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
 * Workflow handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_handler_workflow extends isys_handler
{
    /**
     * Workflow-DAO
     *
     * @var  isys_workflow_dao
     */
    private $m_dao = null;

    /**
     * Workflow-Action-DAO
     *
     * @var  isys_workflow_dao_action
     */
    private $m_dao_action = null;

    /**
     * Contact dao
     *
     * @var  isys_contact_dao_reference
     */
    private $m_dao_contact_ref = null;

    /**
     * Contact dao
     *
     * @var  isys_cmdb_dao_category_s_person_group_members
     */
    private $m_dao_person_group = null;

    /**
     * Workflow instance
     *
     * @var  isys_workflow
     */
    private $m_workflow = null;

    /**
     * @var  mixed
     */
    private $m_workflow_type = null;

    /**
     * Match given date.
     *
     * @param   string  $p_start_date
     * @param   string  $p_today
     * @param   integer $p_occurrence
     *
     * @return  boolean
     */
    public function check($p_start_date, $p_today, $p_occurrence)
    {
        // Get a date difference.
        $l_date_diff = abs(strtotime($p_today) - strtotime($p_start_date)) / isys_convert::DAY;

        $l_month = date("m", strtotime($p_start_date));
        $l_day   = date("d", strtotime($p_start_date));

        switch ($p_occurrence)
        {
            case C__TASK__OCCURRENCE__WEEKLY:
                if (($l_date_diff % 7) == 0)
                {
                    return true;
                } // if
                break;

            case C__TASK__OCCURRENCE__EVERY_TWO_WEEKS:
                if (($l_date_diff % 14) == 0)
                {
                    return true;
                } // if
                break;

            case C__TASK__OCCURRENCE__MONTHLY:

                $l_days_in_month = date('t', strtotime($this->m_year . '-' . $this->m_month . '-01'));
                if (checkdate($this->m_month, $l_day, $this->m_year))
                {
                    if ($this->m_day == $l_day)
                    {
                        return true;
                    } // if
                }
                else
                {
                    if ($l_days_in_month <= $l_day && $this->m_day == $l_days_in_month)
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    } // if
                } // if
                break;

            case C__TASK__OCCURRENCE__YEARLY:

                if (checkdate($this->m_month, $l_day, $this->m_year))
                {
                    if ($l_month == $this->m_month && $l_day == $this->m_day)
                    {
                        return true;
                    } // if
                }
                else
                {
                    if ($this->m_day < $l_day)
                    {
                        return true;
                    }
                } // if
                break;
        } // switch

        return false;
    } // function

    /**
     * @return bool
     * @throws isys_exception_contact
     * @throws isys_exception_database
     */
    public function parse_workflows()
    {
        global $g_comp_template_language_manager;

        $l_dao = &$this->m_dao;

        verbose("Retrieving active workflows");

        $l_filter = "AND ((" . "date_format(start_date.isys_workflow_action_parameter__datetime, '%Y%m%d') <= date_format(NOW(), '%Y%m%d')" . ") " . "OR (" .
            "date_format(end_date.isys_workflow_action_parameter__datetime, '%Y%m%d') >= date_format(NOW(), '%Y%m%d')" . ")) " . "AND (isys_workflow__occurrence > 0)";

        $l_actions = $l_dao->get_workflows(
            null,
            null,
            C__WORKFLOW_TYPE__CHECKLIST,
            C__WORKFLOW__ACTION__TYPE__NEW,
            $l_filter,
            null,
            null,
            null,
            "isys_workflow__datetime DESC"
        );

        $l_workflow_task      = false;
        $l_workflow_checklist = false;

        if (is_array($this->m_workflow_type))
        {
            if (in_array('task', $this->m_workflow_type))
            {
                $l_workflow_task = true;
            } // if
            if (in_array('checklist', $this->m_workflow_type))
            {
                $l_workflow_checklist = true;
            } // if
        }
        else
        {
            if ($this->m_workflow_type == 'task')
            {
                $l_workflow_task = true;
            }
            elseif ($this->m_workflow_type == 'checklist')
            {
                $l_workflow_checklist = true;
            } // if
        } // if

        // Check Checklists
        if ($l_workflow_checklist)
        {
            if ($l_actions->num_rows() > 0)
            {
                verbose($l_actions->num_rows() . " Checklists found.");
                while ($l_row = $l_actions->get_row())
                {
                    verbose("Parsing workflow: " . $l_row["isys_workflow__title"] . " ");

                    $l_id         = 0;
                    $l_start_date = date("Ymd", strtotime($l_row["startdate"]));

                    /*
                    if ($l_row["enddate"] != "0000-00-00 00:00:00") {
                        $l_end_date 	= date("Ymd", strtotime($l_row["enddate"]));
                    } else $l_end_date = date("Ymd", strtotime("+1 year"));
                    */

                    $l_today = date("Ymd");

                    switch ($l_row["isys_workflow__occurrence"])
                    {
                        case C__TASK__OCCURRENCE__HOURLY:
                            echo "- Hourly tasks are not implemented yet.";
                            break;
                        case C__TASK__OCCURRENCE__DAILY:

                            /* Checking exception */
                            if (!(1 << date("w") & $l_row["isys_workflow__exception"]))
                            {
                                $l_id = $this->add_workflow($l_row["isys_workflow__id"]);
                            }
                            else
                            {
                                loading(false);
                                verbose("- Task omitted because of an exception rule. (" . date("D") . ")");
                            }

                            break;
                        case C__TASK__OCCURRENCE__WEEKLY:
                        case C__TASK__OCCURRENCE__EVERY_TWO_WEEKS:
                        case C__TASK__OCCURRENCE__MONTHLY:
                        case C__TASK__OCCURRENCE__YEARLY:

                            loading();
                            if ($this->check($l_start_date, $l_today, $l_row["isys_workflow__occurrence"]))
                            {
                                $l_id = $this->add_workflow($l_row["isys_workflow__id"]);
                            }
                            else
                            {
                                loading();
                                verbose("- Task omitted because occurrence setting did not match the current date.");
                            } // if

                            break;
                    } // switch

                    if ($l_id > 0)
                    {
                        verbose("Task Added. ({$l_id})");
                    } // if
                } // while
            }
            else
            {
                verbose("No circular workflows found");
            } // if
        }
        else
        {
            verbose("Checklists ignored.");
        } // if

        // Check Tasks
        if ($l_workflow_task)
        {
            $l_sql = 'SELECT w0.*, w1.*, w2.*, w3.*,
				a1.isys_workflow_action_parameter__datetime AS end_datetime,
				a2.isys_workflow_action_parameter__int AS notice_days,
				a3.isys_workflow_action_parameter__datetime AS start_datetime,
				a4.isys_workflow_action_parameter__text AS description ' .

                'FROM isys_workflow_action AS w0 ' .

                'INNER JOIN isys_workflow_action_parameter a3 ON a3.isys_workflow_action_parameter__isys_workflow_action__id = w0.isys_workflow_action__id ' .
                'AND isys_workflow_action_parameter__key LIKE \'%start_date\' ' .
                'INNER JOIN isys_workflow_action_parameter a4 ON a4.isys_workflow_action_parameter__isys_workflow_action__id = w0.isys_workflow_action__id ' .
                'AND a4.isys_workflow_action_parameter__key LIKE \'%description\' ' .
                'INNER JOIN isys_workflow_action_parameter a1 ON a1.isys_workflow_action_parameter__isys_workflow_action__id = w0.isys_workflow_action__id ' .
                'AND a1.isys_workflow_action_parameter__key LIKE \'%end_date\' ' .
                'INNER JOIN isys_workflow_action_parameter a2 ON a2.isys_workflow_action_parameter__isys_workflow_action__id = w0.isys_workflow_action__id ' .
                'AND a2.isys_workflow_action_parameter__int > 0 ' .

                'INNER JOIN isys_workflow_2_isys_workflow_action w1 ON w1.isys_workflow_2_isys_workflow_action__isys_workflow_action__id = w0.isys_workflow_action__id ' .
                'INNER JOIN isys_workflow_action_type w2 ON w2.isys_workflow_action_type__id = w0.isys_workflow_action__isys_workflow_action_type__id ' .
                'INNER JOIN isys_workflow w3 ON w3.isys_workflow__id = w1.isys_workflow_2_isys_workflow_action__isys_workflow__id ' .
                'LEFT OUTER JOIN isys_workflow_category ON isys_workflow_category__id = w3.isys_workflow__isys_workflow_category__id ' . 'WHERE TRUE  ' .
                'AND (w3.isys_workflow__isys_workflow_type__id = ' . $l_dao->convert_sql_id(
                    C__WORKFLOW_TYPE__TASK
                ) . ') ' . 'AND (w3.isys_workflow__status = ' . $l_dao->convert_sql_int(
                    C__RECORD_STATUS__NORMAL
                ) . ') ' . 'AND (w2.isys_workflow_action_type__id = ' . $l_dao->convert_sql_id(C__WORKFLOW__ACTION__TYPE__NEW) . ') ' . 'GROUP BY w3.isys_workflow__id';

            $l_res   = $l_dao->retrieve($l_sql);
            $l_count = $l_res->num_rows();
            if ($l_count > 0)
            {

                $l_obj_type_id = array_pop(
                    $l_dao->retrieve('SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' . $l_dao->convert_sql_text('C__OBJTYPE__PERSON'))
                        ->get_row()
                );

                verbose($l_count . " Tasks found.");
                $l_expired = 0;
                $l_active  = 0;
                $l_send    = 0;
                while ($l_row = $l_res->get_row())
                {
                    $this->m_dao_contact_ref->load($l_row['isys_workflow_action__isys_contact__id']);

                    $l_contact_initiator_id       = array_pop(array_keys($this->m_dao_contact_ref->get_data_item_array()));
                    $l_contact_initiator_data_arr = $this->m_dao_contact_ref->get_data_item_data($l_contact_initiator_id);
                    $l_contact_initiator_data     = method_exists($l_contact_initiator_data_arr['dao'], 'get_row') ? $l_contact_initiator_data_arr['dao']->get_row() : [];
                    $l_initiator                  = $l_contact_initiator_data['isys_cats_person_list__first_name'] . ' ' .
                        $l_contact_initiator_data['isys_cats_person_list__last_name'];

                    $l_end_date     = strtotime($l_row['end_datetime']);
                    $l_notice_days  = $l_row['notice_days'] * 86400;
                    $l_current_date = strtotime(date('Y-m-d', time()));

                    if (($l_current_date + $l_notice_days) == $l_end_date)
                    {
                        $l_sql = 'SELECT action_new.isys_workflow_action__isys_contact__id AS contact_id FROM isys_workflow_action AS action_new
							INNER JOIN isys_workflow_2_isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow_action__id = action_new.isys_workflow_action__id
							INNER JOIN isys_workflow ON isys_workflow__id = isys_workflow_2_isys_workflow_action__isys_workflow__id
							INNER JOIN isys_workflow_action_type ON isys_workflow_action_type__id = action_new.isys_workflow_action__isys_workflow_action_type__id
							WHERE TRUE  AND (isys_workflow__id = ' . $l_dao->convert_sql_id($l_row['isys_workflow__id']) . ')
							AND isys_workflow_action_type__id = ' . C__WORKFLOW__ACTION__TYPE__ASSIGN;

                        $l_res2 = $l_dao->retrieve($l_sql);
                        if ($l_res2->num_rows() > 0)
                        {
                            $l_data       = $l_res2->get_row();
                            $l_contact_id = $l_data['contact_id'];

                            $this->m_dao_contact_ref->load($l_contact_id);
                            $l_contacts = $this->m_dao_contact_ref->get_data_item_array();
                            foreach (array_keys($l_contacts) AS $l_obj_id)
                            {
                                $l_cont_obj_type_id = array_pop(
                                    $l_dao->retrieve('SELECT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id = ' . $l_dao->convert_sql_id($l_obj_id))
                                        ->get_row()
                                );

                                if ($l_cont_obj_type_id == $l_obj_type_id)
                                {
                                    // Object type person
                                    $l_res_cont = $this->m_dao_contact_ref->get_data_item_info($l_obj_id, $l_cont_obj_type_id);
                                    while ($l_row_cont = $l_res_cont->get_row())
                                    {
                                        // Send task information to persons
                                        new isys_event_task_information(
                                            $l_row['isys_workflow__id'],
                                            $l_row_cont['isys_cats_person_list__mail_address'],
                                            null,
                                            $l_initiator,
                                            $l_row['notice_days'] . ' ' . $g_comp_template_language_manager->get('LC__TASK__DAY'),
                                            $l_row['start_datetime'],
                                            $l_row['end_datetime'],
                                            $l_row['description']
                                        );
                                    } // while
                                }
                                else
                                {
                                    // Object type person group
                                    $l_res_cont = $this->m_dao_contact_ref->get_data_item_info($l_obj_id, $l_cont_obj_type_id);
                                    while ($l_row_cont = $l_res_cont->get_row())
                                    {
                                        $l_res_cont_members = $this->m_dao_person_group->get_data(null, $l_row_cont['isys_obj__id']);
                                        while ($l_row_cont_members = $l_res_cont_members->get_row())
                                        {
                                            // Send task information to persons
                                            new isys_event_task_information(
                                                $l_row['isys_workflow__id'],
                                                $l_row_cont_members['isys_cats_person_list__mail_address'],
                                                null,
                                                $l_initiator,
                                                $l_row['notice_days'] . ' ' . $g_comp_template_language_manager->get('LC__TASK__DAY'),
                                                $l_row['start_datetime'],
                                                $l_row['end_datetime'],
                                                $l_row['description']
                                            );
                                        } // while
                                    } // while
                                } // if
                            } // foreach
                        } // if

                        $l_active++;
                        $l_send++;
                    }
                    else
                    {
                        if (($l_current_date) > $l_end_date)
                        {
                            $l_expired++;
                        }
                        elseif (($l_current_date) <= $l_end_date)
                        {
                            $l_active++;
                        } // if
                    } // if
                } // while

                verbose($l_expired . " Task(s) are expired.");
                verbose($l_active . " Task(s) are still active.");
                verbose($l_send . " Notice(s) were send.");
            }
            else
            {
                verbose("No Tasks found");
            } // if
        }
        else
        {
            verbose("Tasks ignored");
        } // if

        return true;
    } // function

    /**
     * @return bool
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database, $argv;

        if (in_array('-h', $argv))
        {
            $this->usage();
            die;
        } // if
        verbose("Workflow-Handler initialized (" . date("Y-m-d H:i:s") . ")");

        if ($g_comp_session->is_logged_in())
        {
            if (in_array('-t', $argv))
            {
                $l_type_key = array_search('-t', $argv) + 1;
                if (isset($argv[$l_type_key]))
                {
                    $l_type = $argv[$l_type_key];
                    if (strpos($l_type, ',') > 0)
                    {
                        $this->m_workflow_type = explode(',', $l_type);
                    }
                    else
                    {
                        $this->m_workflow_type = $l_type;
                    } // if
                }
                else
                {
                    $this->m_workflow_type = 'checklist';
                } // if
            }
            else
            {
                $this->m_workflow_type = 'checklist';
            } // if

            verbose("Setting up system environment");

            // Get daos, because now we are logged in.
            $this->m_dao              = new isys_workflow_dao($g_comp_database);
            $this->m_dao_action       = new isys_workflow_dao_action($g_comp_database);
            $this->m_dao_contact_ref  = new isys_contact_dao_reference($g_comp_database);
            $this->m_dao_person_group = new isys_cmdb_dao_category_s_person_group_members($g_comp_database);

            // Get Workflow instance.
            $this->m_workflow = new isys_workflow();

            // Parse wokflows.
            return $this->parse_workflows();
        } // if
        return false;
    } // function

    /**
     * @param   integer $p_workflow__id
     *
     * @return  integer
     */
    private function add_workflow($p_workflow__id)
    {
        global $g_comp_database;

        $l_workflow    = &$this->m_workflow;
        $l_dao_dynamic = new isys_workflow_dao_dynamic($g_comp_database);

        loading();

        $l_workflow->load($p_workflow__id);
        $l_workflow_data = $l_workflow->get_data();

        loading();

        // Get a DB-Conform date.
        $l_current_startdate = date("Y-m-d");

        $l_workflow__id = $l_dao_dynamic->create_task($p_workflow__id, $l_workflow, $l_workflow_data, $l_current_startdate);
        loading();

        if ($l_workflow__id == -1)
        {
            verbose("- No workflow data found.");
        } // if

        if (empty($l_workflow__id))
        {
            verbose("- Task for {$l_current_startdate} already existing.");
        } // if

        return $l_workflow__id;
    } // function

    /**
     *
     */
    private function usage()
    {
        error(
            "Usage: ./controller -m workflow \n" . "Optional Parameter: -t \n" . "Possible Parameters for -t:\n" . "task			- checks only workflow tasks\n" .
            "checklist		- checks only workflow checklists (Default)\n" . "task,checklist		- checks workflow checklists and tasks\n\n" .
            "Example: ./controller -m workflow -t task\n" . ""
        );
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->m_month = date("m");
        $this->m_day   = date("d");
        $this->m_year  = date("Y");
    } // function
} // class