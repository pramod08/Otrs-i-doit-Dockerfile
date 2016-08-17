<?php
/**
 * i-doit - Documentation and CMDB solution for IT environments
 *
 * This file is part of the i-doit framework. Modify at your own risk.
 *
 * Please visit http://www.i-doit.com/license for a full copyright and license information.
 *
 * @version     1.7.3
 * @package     i-doit
 * @author      synetics GmbH
 * @copyright   synetics GmbH
 * @url         http://www.i-doit.com
 * @license     http://www.i-doit.com/license
 */

/**
 * Class isys_handler
 */
abstract class isys_handler implements isys_handler_interface
{
    /* The current date */
    /**
     * @var
     */
    protected $m_day;

    /**
     * @var
     */
    protected $m_month;

    /**
     * @var
     */
    protected $m_year;

    /**
     * @return bool
     */
    public function needs_login()
    {
        return true;
    } // function

    /**
     * Logout current session
     */
    public function logout()
    {
        global $g_comp_session;

        if (is_object($g_comp_session))
        {
            if ($g_comp_session->is_logged_in())
            {
                if (function_exists("verbose"))
                {
                    verbose("Logging out\n");
                } // if

                $g_comp_session->logout();
            } // if
        } // if
    } // function

    /**
     * @param string $headline
     * @param int    $done
     * @param int    $total
     * @param int    $size
     */
    public function progress($name, $done, $total, $size = 42)
    {
        static $start_time;

        if ($done === 0) return;

        // if we go over our bound, just ignore it
        if ($done > $total) return;

        if (empty($start_time)) $start_time = time();
        $now = time();

        $perc = (double) ($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\r".$name.": [";
        $status_bar .= str_repeat("=", $bar);
        if ($bar < $size)
        {
            $status_bar .= ">";
            $status_bar .= str_repeat(" ", $size - $bar);
        }
        else
        {
            $status_bar .= "=";
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] $disp%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta  = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= " remaining: ~" . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if ($done == $total)
        {
            echo "\n";
        }

    } // function

    /**
     * Sends a mail.
     *
     * @param string $p_email
     * @param string $p_subject
     * @param string $p_message
     *
     * @return boolean
     */
    public function _mail($p_email, $p_subject, $p_message)
    {
        try
        {
            $l_mailer = new isys_library_mail();

            if ($l_mailer->check_address($p_email))
            {
                // Configure mail.
                $l_mailer->AddAddress($p_email);
                $l_mailer->Subject = isys_settings::get('system.email.subject-prefix', '') . $p_subject;
                $l_mailer->Body    = nl2br($p_message);
                $l_mailer->isHTML(true);

                // Use SMTP and send.
                $l_mailer->IsSMTP();

                if ($l_mailer->Send())
                {
                    verbose(".. successfull.", false);

                    return true;
                }
                else
                {
                    verbose(".. error: " . $l_mailer->ErrorInfo . "", false);

                    return false;
                } // if
            }
            else
            {
                verbose("E-mail: " . $p_email . " is not a valid address.");

                return false;
            }
        }
        catch (Exception $e)
        {
            verbose(" ### Error: " . $e->getMessage());
        } // try
    }

    /**
     * Displays a message, which shows which config file the user has to edit.
     */
    public function display_config_hint()
    {
        global $g_handler_config, $g_absdir;

        if (C__WINDOWS)
        {
            $l_mandator_cmd = "php.exe controller.php -v -m mandator ls";
        }
        else
        {
            $l_mandator_cmd = "./mandator ls";
        } // if

        error(
            "Login configuration error: You should setup \$g_userconf " . "in {$g_handler_config} to do an automated (script-based) login.\n\n" .

            "Check the example in \n" . str_replace("config/", "config/examples/", $g_handler_config) . " and copy it to \n" . $g_absdir . "/src/handler/config/.\n\n" .

            "Or use -u user -p pass -i mandator-id instead. (e.g. -u admin -p admin -i 1)\n\n" . "Get a list of your mandator ids with \"" . $l_mandator_cmd . "\"\n\n"
        );
    } // function

    /**
     *
     */
    public function __destruct()
    {
        //$this->logout();
    } // function

    /**
     * @return mixed
     */
    protected function get_title()
    {
        return str_replace('isys_handler_', '', get_class($this));
    } // function

    /**
     * Creates a task with title $p_title and $p_description
     * and notifies the contacts, which are assigned to $p_object_id
     *
     * @param string $p_title
     * @param string $p_description
     * @param int    $p_object_id
     *
     * @return int workflow id
     */
    protected function create_task($p_title, $p_description, $p_object_id)
    {
        global $g_comp_database, $g_comp_session;

        if (empty($p_object_id)) return false;

        $l_object_id   = $p_object_id;
        $l_description = $p_description;

        $l_wf_action_dao = new isys_workflow_dao_action($g_comp_database);
        $l_dao_dynamic   = new isys_workflow_dao_dynamic($g_comp_database);
        $l_contact_to    = new isys_contact_dao_reference($g_comp_database);
        $l_contact_from  = new isys_contact_dao_reference($g_comp_database);
        $l_catg_contact  = new isys_cmdb_dao_category_g_contact($g_comp_database);

        /* Get all contacts assigned to $l_object_id */
        $l_contacts = $l_catg_contact->get_contacts_by_obj_id($l_object_id);
        if ($l_contacts->num_rows() > 0)
        {
            while ($l_row = $l_contacts->get_row())
            {
                $l_persons[] = $l_row["isys_cats_person_list__isys_obj__id"];
            }
        }
        else
        {
            verbose("No contacts assigned. ", false);

            return false;
        }

        if (isset($l_persons))
        {
            $l_contact_to->ref_contact($l_persons);
            if ($l_contact_to->save()) $l_contact_to_id = $l_contact_to->get_id();

        }
        else $l_contact_to_id = null;

        /* Reference myself */
        $l_contact_from->ref_contact([$g_comp_session->get_user_id()]);
        $l_contact_from->save();

        if (!($l_workflow_id = $l_dao_dynamic->create_workflow(
            $p_title,
            $l_contact_from->get_id(),
            C__WORKFLOW_TYPE__TASK,
            null,
            $l_object_id
        ))
        )
        {
            return false;
        }

        $l_action_new_id = $l_wf_action_dao->create_action(C__WORKFLOW__ACTION__TYPE__NEW);

        if ($l_action_new_id > 0)
        {
            $l_wf_action_dao->bind($l_workflow_id, $l_action_new_id);

            $l_wf_action_dao->add_parameter($l_action_new_id, C__WF__PARAMETER_TYPE__DATETIME, "task__start_date", date("Y-m-d"), 1);

            $l_wf_action_dao->add_parameter($l_action_new_id, C__WF__PARAMETER_TYPE__TEXT, "task__description", $l_description, 3);
        } // if

        if (isset($l_contact_to_id) && is_numeric($l_contact_to_id))
        {
            $l_assign = new isys_workflow_action_assign();
            $l_assign->save($l_workflow_id, $l_contact_to_id);
        } // if

        return $l_workflow_id;
    } // function
}

/**
 * i-doit
 *
 * Workflow handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
interface isys_handler_interface
{
    /**
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public function needs_login();
} // class