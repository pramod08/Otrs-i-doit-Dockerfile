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
 * i-doit
 *
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0 Wed Jun 21 13:48:38 CEST 2006 13:48:38
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_workflow_view_list_filter extends isys_workflow_view_list
{

    private $m_current;
    private $m_max_entries;

    public function get_id()
    {
        return C__WF__VIEW__LIST_FILTER;
    }

    public function get_mandatory_parameters(&$l_gets)
    {
    }

    public function get_name()
    {
        return "";
    }

    public function get_optional_parameters(&$l_gets)
    {
        $l_gets[C__WF__GET__TYPE]   = true;
        $l_gets[C__WF__GET__FITLER] = true;
    }

    public function get_template_bottom()
    {
        return "workflow/filter_list.tpl";
    }

    public function get_template_top()
    {
        return "";
    }

    public function list_init()
    {
        return true;
    }

    /**
     * @desc process the list
     * @return boolean
     */
    public function list_process()
    {

        global $g_comp_database;
        global $g_comp_template;
        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_FILTER);
        $l_filter = "";

        /** @desc get dao objects */
        $l_dao_actions       = new isys_workflow_dao_action($g_comp_database);
        $l_workflow_dao_list = new isys_workflow_dao_list_filter(
            $this->get_module_request()
                ->get_database()
        );

        /*get singleton instance of isys_workflow_list*/
        $l_workflow_list = isys_workflow_list::get();

        $l_workflow = new isys_workflow();

        /** @desc get current filter */
        $l_filter .= $this->process_filter_operations();

        /*filter for action new, to get some info parameters*/
        $l_workflow_action_type = C__WORKFLOW__ACTION__TYPE__NEW;

        /* --  Default Settings -- */

        /**
         * Use this predefined settings, if nothing is selected
         */
        if (count($_POST) <= 1)
        {
            if ($_GET["fltr"] != "m") $_POST["today"] = "";

            // Show my workflows as default
            $_POST["today"] = "today";

            /* Show my workflows */
            $_POST["my"]      = true;
            $_POST["wf_type"] = C__WORKFLOW_TYPE__TASK;
            $_POST["status"]  = C__TASK__STATUS__ASSIGNMENT;
        }

        /* -------------------------------------------------------------------- */
        /* Process post variables --------------------------------------------- */
        /* -------------------------------------------------------------------- */

        $l_workflow_type = $_POST["wf_type"];

        /* Creator */
        $_POST["f_uid"] = isys_glob_get_param("f_uid");
        if (isset($_POST["f_uid"]))
        {
            $l_user_id = $_POST["f_uid"];
            if ($l_user_id <= 0) unset($l_user_id);
        }

        /* -------------------------------------------------------------------- */
        /* - Extended: -------------------------------------------------------- */
        /* -------------------------------------------------------------------- */

        if (isset($_POST["my"]))
        {
            $l_user_id              = $_SESSION["session_data"]["isys_user_session__isys_obj__id"];
            $l_workflow_action_type = C__WORKFLOW__ACTION__TYPE__ASSIGN;
        }

        /* -------------------------------------------------------------------- */

        /* -------------------------------------------------------------------- */
        /* - Date from / to: -------------------------------------------------- */
        /* -------------------------------------------------------------------- */
        if (strlen($_POST["f_date_from__HIDDEN"]) > 0)
        {
            $l_date_from = $_POST["f_date_from__HIDDEN"];
        }
        else $l_date_from = null;
        /* -------------------------------------------------------------------- */
        if (strlen($_POST["f_date_to__HIDDEN"]) > 0)
        {
            $l_date_to = $_POST["f_date_to__HIDDEN"];
        }
        else $l_date_to = null;
        /* -------------------------------------------------------------------- */

        if (isset($_POST["today"]))
        {
            if (isset($_GET[C__WORKFLOW__GET__FILTER]) && $_GET[C__WORKFLOW__GET__FILTER] == 'm')
            {
                $l_date_from = date("Y-m") . '-01';
                $l_month     = date("n");
                $l_year      = date("Y");
                $l_date_to   = $l_year . '-' . $l_month . '-' . date('t', strtotime($l_year . '-' . $l_month . '-01'));
            }
            else
            {
                $l_date_from = date("Y-m-d");
                $l_date_to   = date("Y-m-d");
            }
        }

        /* -------------------------------------------------------------------- */
        /* -------------------------------------------------------------------- */

        /* -------------------------------------------------------------------- */

        /* -------------------------------------------------------------------- */
        /* - ORDER!!: --------------------------------------------------------- */
        /* -------------------------------------------------------------------- */
        $l_order_by = null;
        if (!empty($_POST['order_field']))
        {
            switch ($_POST['order_field'])
            {
                case 'startdate':
                    $l_order_by = "start_date.isys_workflow_action_parameter__datetime " . $_POST['order_dir'];
                    break;
                default:
                    $l_order_by = $_POST['order_field'] . " " . $_POST['order_dir'];
                    break;
            }
        }
        /* -------------------------------------------------------------------- */

        /*get actions as dao result*/
        $l_dao = $l_dao_actions->get_workflows(
            null,
            null,
            $l_workflow_type,
            $l_workflow_action_type,
            $l_filter,
            null,
            $l_date_from,
            $l_date_to,
            $l_order_by,
            $l_user_id,
            $_POST["f_owner_mode"],
            $_POST["status"]
        );

        $i = 0;
        while ($l_row = $l_dao->get_row())
        {
            /*the column index*/
            $i++;
            /*check and process a checklist and dynamically show the tasks for it*/
            if (!empty($l_row["isys_workflow__occurrence"]))
            {
                #$this->process_checklists($l_row, $i, $l_date_from, $l_date_to);
                $this->create_columns($i, $l_row);
            }
            else
            {

                /* Send entry to hell if today is filtered and task doesn't start today */
                $l_date = preg_replace("/(.*?) [\d][\d]:[\d][\d]:[\d][\d]/", "\\1", $l_row["startdate"]);
                if (isset($_POST["today"]) && $_GET[C__WORKFLOW__GET__FILTER] == 'd' && date("Y-m-d") != $l_date) continue;

                /**
                 * @desc create and send the columns to isys_workflow_list
                 */
                $this->create_columns($i, $l_row);
            }

            /**
             * @desc create a link
             */
            $l_workflow_list->set_link($i, $l_workflow->create_link($l_row["isys_workflow__id"]));
        }

        /**
         * @desc sort the list
         */
        //$l_workflow_list->sort();

        /**
         * @desc assign the list to smarty
         */
        $l_workflow_list->assign();

        /**
         * @desc SMARTY HTML STATUS ASSIGNMENTS
         */
        $this->assign_type_selection();

        return true;
    }

    public function process()
    {
        $this->list_process();

        return true;
    }

    public function &get_detail_view()
    {
        return new isys_workflow_view_detail_generic($this->m_modreq);
    }

    /**
     * @desc process_checklists:
     *            method to create dynamic tasks, which are orianted on the given checklist
     *
     * @return boolean
     */
    public function process_checklists($p_data, &$p_index, $p_date_from = null, $p_date_to = null)
    {
        global $g_comp_database;

        $l_data          = $p_data;
        $l_workflow_list = isys_workflow_list::get();

        $this->m_current = 0;

        /**
         * @desc strtotime mapping
         */
        $l_occurrence = [
            C__TASK__OCCURRENCE__ONCE            => "+1 day",
            C__TASK__OCCURRENCE__HOURLY          => "+1 hour",
            C__TASK__OCCURRENCE__DAILY           => "+1 day",
            C__TASK__OCCURRENCE__WEEKLY          => "+1 week",
            C__TASK__OCCURRENCE__EVERY_TWO_WEEKS => "+2 weeks",
            C__TASK__OCCURRENCE__MONTHLY         => "+1 month",
            C__TASK__OCCURRENCE__YEARLY          => "+1 week",
        ];

        /*action dao*/
        $l_dao_actions = new isys_workflow_dao_action($g_comp_database);
        $l_dao_dynamic = new isys_workflow_dao_dynamic($g_comp_database);
        $l_workflow    = new isys_workflow();

        $l_action__id = $p_data["isys_workflow_action__id"];
        $l_end_date   = $p_data["enddate"];

        /* If this isnt an endless check, build the dates dynamically */
        if ($l_end_date != "0000-00-00" && $l_end_date != "0000-00-00 00:00:00")
        {
            /* Prepare enddate */
            $l_end_date_formatted = date("Ymd", strtotime($l_end_date));

            if (!is_null($p_date_to))
            {
                $l_end_date     = $p_date_to;
                $l_end_date_tmp = date("Ymd", strtotime($l_end_date));

                if ($l_end_date_tmp <= $l_end_date_formatted)
                {
                    $l_end_date_formatted = $l_end_date_tmp;
                }
            }

            /* Prepare start-date */
            if (isset($_POST["today"]) || isset($_POST["from_now"]))
            {
                $l_current_date_formatted = date("Ymd");
            }
            else if (!is_null($p_date_from))
            {
                $l_current_date_formatted = date("Ymd", strtotime($p_date_from));
            }
            else
            {
                $l_current_date_formatted = date("Ymd", strtotime($p_data["startdate"]));
            }

            while ($l_end_date_formatted >= $l_current_date_formatted)
            {
                $l_today             = date("Y-m-d", strtotime($l_current_date_formatted));
                $p_data["startdate"] = $l_today;

                /*	- dow = 0-6 Sunday-Saturday
                    - check if the current dynamic date is not an exception
                    - isys_workflow__exception : bit compare */

                $l_dow = date("w", strtotime($l_current_date_formatted));
                if (!(1 << $l_dow & $p_data["isys_workflow__exception"]))
                {

                    /*  &&
                        !$l_dao_dynamic->check_existence(	$p_data["isys_workflow__id"],
                                                            $p_data["startdate"])*/

                    if ($p_data["isys_workflow__occurrence"] > 0)
                    {
                        /*create the columns*/
                        $this->create_columns($p_index++, $p_data);

                        /*create link for current dynamic checklist*/
                        $l_workflow_list->set_link(
                            $p_index - 1,
                            $l_workflow->create_link(
                                $p_data["isys_workflow__id"]
                            ) . "&" . C__WF__GET__TYPE . "=" . $p_data["isys_workflow_type__id"] . "&" . "date=" . $l_current_date_formatted
                        );

                    }
                }

                $l_current_date_formatted = date(
                    "Ymd",
                    strtotime(
                        $l_occurrence[$p_data["isys_workflow__occurrence"]],
                        strtotime($l_current_date_formatted)
                    )
                );

                if (isset($_POST["today"]) || ++$this->m_current >= $this->m_max_entries)
                {
                    return true;
                }
            }
        }

        return true;
    }

    /**
     * @desc create current filter operations as sql condition
     * @return string
     */
    private function process_filter_operations()
    {

        return null;
    }

    /*not used*/

    /**
     * @desc assign an array with all workflow types of the system
     *
     */
    private function assign_type_selection()
    {
        global $g_comp_database;
        global $g_comp_template;

        $l_dao       = new isys_workflow_dao_type($g_comp_database);
        $l_type_data = $l_dao->get_workflow_types();

        while ($l_row = $l_type_data->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY))
        {
            $l_workflow_types[] = $l_row;

            if ($l_row["isys_workflow_type__id"] == $_POST["wf_type"])
            {
                $l_workflow_types[count($l_workflow_types) - 1]["selected"] = true;
            }
        }
        $g_comp_template->assign("g_workflow_types", $l_workflow_types);

        return true;
    }

    /**
     * @desc prepare columns for isys_workflow_list
     *
     * @param int   $p_index
     * @param array $p_row
     *
     * @return true
     */
    private function create_columns($p_index, $p_row)
    {
        global $g_comp_template_language_manager;
        $l_lm = $g_comp_template_language_manager;
        global $g_loc;

        /*get singleton instance of isys_workflow_list*/
        $l_workflow_list = isys_workflow_list::get();

        /**
         * @desc table definitions:
         */
        $l_workflow_list->add_column(
            $p_index,
            "isys_workflow__title",
            $l_lm->get("LC__TASK__DETAIL__WORKORDER__TITLE"),
            $p_row["isys_workflow__title"]
        );

        $l_workflow_list->add_column(
            $p_index,
            "isys_workflow_type__title",
            $l_lm->get("LC__CMDB__CATG__TYPE"),
            $p_row["isys_workflow_type__title"]
        );

        $l_workflow_list->add_column(
            $p_index,
            "startdate",
            $l_lm->get("LC__TASK__DETAIL__WORKORDER__START_DATE"),
            $g_loc->fmt_datetime($p_row["startdate"])
        );

        $l_workflow_list->add_column(
            $p_index,
            "isys_obj__title",
            (isset($_POST["my"])) ? $l_lm->get("LC__TASK__DETAIL__WORKORDER__ASSIGNED_PERSONS") : $l_lm->get("LC__TASK__INITIATOR"),
            $p_row["isys_obj__title"]
        );

        $l_workflow_list->add_column(
            $p_index,
            "isys_workflow__datetime",
            $l_lm->get("LC__TASK__DETAIL__WORKORDER__CREATION_DATE"),
            $p_row["isys_workflow__datetime"]
        );

        return true;
    }

    public function __construct(isys_module_request $p_modreq)
    {
        global $g_comp_template;

        if ($_POST["max"] > 0 && $_POST["max"] < 100)
        {
            $this->m_max_entries = $_POST["max"];
        }
        else
        {
            $this->m_max_entries = isys_usersettings::get('workflows.max-checklist-entries', 7); //	How many tasks for one checklist
        }

        $g_comp_template->assign("g_max_workflows", $this->m_max_entries);

        parent::__construct($p_modreq);
    }
}

?>