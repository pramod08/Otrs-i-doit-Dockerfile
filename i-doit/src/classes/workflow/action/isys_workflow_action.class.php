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
 *
 * @package    i-doit
 * @subpackage Workflow
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_workflow_action
{

    /**
     * @var int
     */
    protected $m_actiontype;
    /**
     * @var isys_contact_dao_reference
     */
    protected $m_from;
    /**
     * @var int
     */
    protected $m_id;
    protected $m_title;
    /**
     * @var isys_contact_dao_reference
     */
    protected $m_to;
    /**
     * @desc assigned workflow id
     * @var int
     */
    protected $m_workflow_id;
    private $m_assigned;
    /**
     * @var string
     */
    private $m_datetime;
    /**
     * @var array of isys_workflow_action_parameter
     */
    private $m_parameters;

    /**
     * Get parent workflow
     *
     * @global type $g_comp_database
     * @return null
     */
    public function get_parent_workflow()
    {
        global $g_comp_database;
        $l_dao = new isys_workflow_dao($g_comp_database);

        $l_res = $l_dao->get_workflows_clean($this->get_workflow());

        if ($l_res->num_rows())
        {
            $l_row = $l_res->get_row();

            return $l_row['isys_workflow__isys_workflow__id'];
        }

        return null;
    }

    public function getAssigned()
    {
        return $this->m_assigned;
    }

    public function setAssigned($p_assigned)
    {
        $this->m_assigned = $p_assigned;
    }

    /**
     * @desc return a given parameter
     *
     */
    public function get_parameter($p_id)
    {
        return $this->m_parameters[$p_id];
    }

    public function get_parameters()
    {
        return $this->m_parameters;
    }

    public function add_parameter($p_key, $p_value, $p_type, $p_template_parameter, $p_id)
    {
        $this->m_parameters[] = new isys_workflow_action_parameter(
            $p_key, $p_value, $p_type, $p_template_parameter, $p_id
        );
    }

    /**
     * @desc method prototype
     *
     */
    public function get_template()
    {
        return "";
    }

    public function set_from(&$p_contact_reference)
    {
        $this->m_from = $p_contact_reference;
    }

    public function set_to(&$p_contact_reference)
    {
        $this->m_to = $p_contact_reference;
    }

    /**
     * @return int
     */
    public function get_from()
    {
        return $this->m_from;
    }

    /**
     * @return int
     */
    public function get_to()
    {
        return $this->m_to;
    }

    /**
     * @desc return the error codes
     * @return array
     */
    public function get_error_code()
    {

    }

    public function set_workflow_id($p_id)
    {
        $this->m_workflow_id = $p_id;
    }

    public function get_workflow()
    {
        return $this->m_workflow_id;
    }

    public function set_actiontype($p_action_type)
    {
        $this->m_actiontype = $p_action_type;
    }

    public function get_actiontype()
    {
        return $this->m_actiontype;
    }

    public function set_id($p_id)
    {
        $this->m_id = $p_id;
    }

    public function get_id()
    {
        return $this->m_id;
    }

    public function set_datetime($p_datetime)
    {
        $this->m_datetime = $p_datetime;
    }

    public function get_datetime()
    {
        return date('d.m.Y, H:i:s', strtotime($this->m_datetime));
    }

    public function set_title($p_title)
    {
        $this->m_title = $p_title;
    }

    public function get_title()
    {
        return $this->m_title;
    }

    /**
     * @desc get current action type
     * @return int
     */
    public function get_type()
    {
        return $this->m_actiontype;
    }

    /**
     * @desc overwrite this one, please
     */
    public function save()
    {

    }

    /**
     * @desc overwrite this one, please
     */
    public function handle()
    {

    }

    /**
     * @desc load data to the intern structure
     *
     * @param isys_workflow_data $p_data
     */
    public function load(isys_workflow_data &$p_data, isys_component_dao_result $p_workflow_res = null)
    {
        global $g_comp_database;
        global $g_comp_template_language_manager;

        /* get the language manager */
        $l_lm = $g_comp_template_language_manager;

        /* prepare daos */
        $l_dao_reference = new isys_contact_dao_reference($g_comp_database);
        $l_dao_workflow  = new isys_workflow_dao_action($g_comp_database);

        /* get action data */
        if (!is_null($p_workflow_res))
        {
            $l_data = $p_workflow_res;
        }
        else
        {
            $l_data = $l_dao_workflow->get_actions($this->m_workflow_id);
        }

        /* iterate through action data */
        while ($l_row = $l_data->get_row())
        {

            /* --------------------------------------------------------------------------------------------- */
            /* - ADD SPECIFIC ACTION WITH INFO TO DATA OBJECT ---------------------------------------------- */
            /* --------------------------------------------------------------------------------------------- */

            $l_action_id = $l_row["isys_workflow_action__id"];
            $l_to_id     = $l_row["isys_workflow_action__isys_contact__id"];
            $l_from_id   = $l_row["isys_workflow__isys_contact__id"];

            $l_action_type  = $l_row["isys_workflow_action_type__id"];
            $l_action_class = $l_row["isys_workflow_action_type__class"];
            $l_action_title = $l_row["isys_workflow_action_type__title"];

            /**
             * @var isys_workflow_action
             */
            if (class_exists($l_action_class))
            {
                $l_action = new $l_action_class();

                /* load contact: from */
                if (!empty($l_from_id))
                {
                    $l_dao_reference->load($l_from_id);
                }

                $l_action->set_from($l_dao_reference);

                $l_dao_reference->clear();

                /* load contact: to */
                if (!empty($l_to_id))
                {
                    $l_dao_reference->load($l_to_id);
                }
                $l_action->set_to($l_dao_reference);
                $l_action->setAssigned($l_to_id);

                $l_action->set_actiontype($l_action_type);
                $l_action->set_workflow_id($this->m_workflow_id);

                $l_action->set_datetime($l_row["isys_workflow_action__datetime"]);
                $l_action->set_title($l_action_title);
                $l_action->set_id($l_action_id);

                /* --------------------------------------------------------------------------------------------- */
                /* - SWITCH PARAMETER TYPE --------------------------------------------------------------------- */
                /* --------------------------------------------------------------------------------------------- */
                $l_p_data = $l_dao_workflow->get_action_parameters($l_row["isys_workflow_action__id"]);

                if ($l_p_data->num_rows() > 0)
                {
                    while ($l_p_row = $l_p_data->get_row())
                    {
                        $l_parameter_type = $l_p_row["isys_workflow_template_parameter__type"];

                        switch ($l_parameter_type)
                        {
                            case C__WF__PARAMETER_TYPE__INT:
                                $l_parameter = $l_p_row["isys_workflow_action_parameter__int"];
                                break;
                            case C__WF__PARAMETER_TYPE__YES_NO:
                                $l_parameter = str_replace(
                                    [
                                        0,
                                        1
                                    ],
                                    [
                                        $l_lm->get("LC__UNIVERSAL__NO"),
                                        $l_lm->get("LC__UNIVERSAL__YES")
                                    ],
                                    $l_p_row["isys_workflow_action_parameter__int"]
                                );
                                break;
                            case C__WF__PARAMETER_TYPE__DATETIME:
                                $l_parameter = $l_p_row["isys_workflow_action_parameter__datetime"];
                                break;
                            case C__WF__PARAMETER_TYPE__TEXT:
                                $l_parameter = $l_p_row["isys_workflow_action_parameter__text"];
                                break;
                            default:
                            case C__WF__PARAMETER_TYPE__STRING:
                                $l_parameter = $l_p_row["isys_workflow_action_parameter__string"];
                                break;
                        }

                        $l_action->add_parameter(
                            $l_p_row["isys_workflow_action_parameter__key"],
                            $l_parameter,
                            $l_parameter_type,
                            $l_p_row["isys_workflow_template_parameter__id"],
                            $l_p_row["isys_workflow_action_parameter__id"]
                        );
                    }
                }
                /* --------------------------------------------------------------------------------------------- */
                /* --------------------------------------------------------------------------------------------- */
                $p_data->add_action($l_action);
                /* --------------------------------------------------------------------------------------------- */
                $l_action->handle();
                /* --------------------------------------------------------------------------------------------- */
                $l_dao_reference->clear();
                /* --------------------------------------------------------------------------------------------- */

            }
            else throw new isys_exception_general("Class " . $l_class . ": defined in isys_workflow_action_types is not existing.");
        }

        return true;
    }

    /**
     * @desc
     *
     */
    public function verify()
    {

    }

    /**
     * @desc return the current status of the workflow, when this action was processed
     * @return int
     */
    public function get_status()
    {
        return null;
    }

    /**
     * Sends an email to the given email(s)
     *
     * @param string /array $p_email
     */
    protected function send_report($p_email, $p_description, $p_method_desc, $p_status, $p_template = C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED)
    {
        $l_mail_event = new isys_event_task_closed(
            $p_template, $this->m_workflow_id, 0, $p_description, $p_method_desc, $p_email[0], array_slice($p_email, 1), $p_status
        );

        return $l_mail_event;
    }

    /**
     * @desc creates a contact data item by a given user id
     *
     * @param int $p_user_id
     *
     * @return int
     */
    protected function create_contact_by_person_intern($p_user_id)
    {
        global $g_comp_database;

        $l_dao_reference = new isys_contact_dao_reference($g_comp_database);
        $l_dao_reference->insert_data_item($p_user_id, C__CONTACT__DATA_ITEM__PERSON_INTERN);

        if ($l_dao_reference->save())
        {
            $l_contact_id = $l_dao_reference->get_id();
        }

        return $l_contact_id;
    }

    /**
     * @desc sets m_id
     *
     * @param int $p_id
     */
    public function __construct($p_workflow__id)
    {
        $this->m_workflow_id = $p_workflow__id;
    }
}

?>