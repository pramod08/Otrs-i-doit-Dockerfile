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
 * @package     i-doit
 * @subpackage  Workflow
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_workflow_dao_dynamic extends isys_workflow_dao
{
    /**
     * This variable contains isys_workflow_type__id.
     *
     * @var  integer
     */
    private $m_workflow_type = 1;

    /**
     * @param  integer $p_workflow_type
     */
    public function set_workflow_type($p_workflow_type)
    {
        $this->m_workflow_type = $p_workflow_type;
    } // function

    /**
     * @return  integer
     */
    public function get_workflow_type()
    {
        return $this->m_workflow_type;
    }

    /**
     * @param   integer $p_workflow__id
     * @param   string  $p_start_date
     *
     * @return  boolean
     * @throws  isys_exception_database
     */
    public function check_existence($p_workflow__id, $p_start_date = null)
    {
        $l_sql = "SELECT * FROM isys_workflow
			INNER JOIN isys_workflow_action ON isys_workflow_action__isys_workflow_action_type__id = " . $this->convert_sql_id(C__WORKFLOW__ACTION__TYPE__NEW) . "
			INNER JOIN isys_workflow_2_isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow__id = isys_workflow__id
			INNER JOIN isys_workflow_action_parameter ON isys_workflow_action_parameter__isys_workflow_action__id = isys_workflow_action__id
			WHERE isys_workflow_2_isys_workflow_action__isys_workflow_action__id = isys_workflow_action__id
			AND isys_workflow__isys_workflow__id = " . $this->convert_sql_id($p_workflow__id) . "
			AND isys_workflow_action_parameter__key LIKE '%start_date%'";

        if ($p_start_date !== null)
        {
            $l_sql .= " AND isys_workflow_action_parameter__datetime = " . $this->convert_sql_datetime($p_start_date);
        }
        else
        {
            $l_sql .= " AND (date_format(isys_workflow_action_parameter__datetime,'%Y-%m-%d') = " . $this->convert_sql_text(date('Y-m-d', time()));
        } // if

        $l_dao = $this->retrieve($l_sql);

        return ($l_dao->num_rows() > 0);
    } // function

    /**
     * Creates a dynamic task from checklists.
     *
     * @param   integer            $p_workflow__id
     * @param   isys_workflow      $p_workflow
     * @param   isys_workflow_data $p_workflow_data
     * @param   string             $p_current_startdate
     *
     * @return  integer
     * @author  dennis stuecken <dstuecken@i-doit.org>
     */
    public function create_task($p_workflow__id, isys_workflow $p_workflow, isys_workflow_data $p_workflow_data, $p_current_startdate)
    {
        global $g_comp_database;

        $l_wf_action_dao = new isys_workflow_dao_action($g_comp_database);

        // Get workflow actions.
        $l_workflow_actions = $p_workflow_data->get_actions();

        if (is_object($l_workflow_actions[0]))
        {

            if (!$this->check_existence($p_workflow__id, $p_current_startdate))
            {
                // Create Workflow (Task).
                $l_workflow_id = $this->create_workflow(
                    $p_workflow->get_title(),
                    $p_workflow->get_initiator(),
                    C__WORKFLOW_TYPE__TASK,
                    $p_workflow->get_category(),
                    $p_workflow->get_object_id(),
                    0,
                    0,
                    $p_workflow->get_id()
                );

                // Create action: New.
                $l_action_new_id = $l_wf_action_dao->create_action(C__WORKFLOW__ACTION__TYPE__NEW, $p_workflow->get_initiator());

                $l_wf_action_dao->bind($l_workflow_id, $l_action_new_id);

                // Add start parameter and description to the created action.
                $l_wf_action_dao->add_parameter($l_action_new_id, C__WF__PARAMETER_TYPE__DATETIME, "task__start_date", $p_current_startdate, 1);

                $l_task_description = $p_workflow_data->get_parameter_by_key($l_workflow_actions[0], "checklist__description");

                if (method_exists($l_task_description, "get_value"))
                {
                    $l_wf_action_dao->add_parameter($l_action_new_id, C__WF__PARAMETER_TYPE__TEXT, "task__description", $l_task_description->get_value(), 3);
                }

                // Create action: assign
                $l_assigned = $l_workflow_actions[1]->getAssigned();

                $l_action_assign_id = $l_wf_action_dao->create_action(C__WORKFLOW__ACTION__TYPE__ASSIGN, $l_assigned);
                $l_wf_action_dao->bind($l_workflow_id, $l_action_assign_id);

                // Send notification, but don't create that action, just bind to the checklist notification action.
                if (is_object($l_workflow_actions[2]))
                {
                    if ($l_workflow_actions[2]->get_actiontype() == C__WORKFLOW__ACTION__TYPE__NOTIFICATION)
                    {
                        $l_assignments = $l_workflow_actions[2]->get_to();
                        $l_contact_id  = $l_assignments->get_id();

                        $l_notification = new isys_workflow_action_notification();
                        $l_notification->save($p_workflow__id, $l_workflow_actions[2], C__EMAIL_TEMPLATE__TASK__NOTIFICATION, $l_contact_id, $l_workflow_actions[2]->get_id());
                    } // if
                } // if
            } // if
        }
        else
        {
            return -1;
        } // if

        return $l_workflow_id;
    } // function

    /**
     * isys_workflow_dao_dynamic constructor.
     *
     * @param  isys_component_database $p_database
     */
    public function __construct(isys_component_database &$p_database)
    {
        parent::__construct($p_database);
    } // function
} // class