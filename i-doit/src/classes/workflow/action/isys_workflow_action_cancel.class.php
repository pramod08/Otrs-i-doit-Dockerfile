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
class isys_workflow_action_cancel extends isys_workflow_action
{

    /**
     * @desc return the current status of the workflow, when this action was processed
     * @return int
     */
    public function get_status()
    {
        return C__TASK__STATUS__CANCEL;
    }

    public function get_template()
    {
        return "workflow/detail/actions/cancel.tpl";
    }

    public function handle()
    {
        global $g_comp_template;
        global $g_comp_database;

        $g_comp_template->assign("g_cancelled", true);

        $l_id = $this->get_id();

        $l_workflow_dao = new isys_workflow_dao_action($g_comp_database);
        $l_parameter    = $l_workflow_dao->get_action_parameters($l_id, null, null, false);
        while ($l_row = $l_parameter->get_row())
        {
            if ($l_row["isys_workflow_action_parameter__key"] == "description")
            {
                $g_comp_template->assign("g_description", $l_row["isys_workflow_action_parameter__text"]);
            }
            if ($l_row["isys_workflow_action_parameter__key"] == "measure")
            {
                $g_comp_template->assign("g_measures", $l_row["isys_workflow_action_parameter__text"]);
            }
        }

        return true;

    }

    /**
     * @desc
     *
     * @param int $p_id
     */
    public function save($p_workflow__id)
    {
        global $g_comp_database, $g_comp_session;

        $l_workflow_id = $p_workflow__id;
        $g_post        = $_POST;

        /* get current user id */
        $l_session_data    = $g_comp_session->get_session_data($g_comp_session->get_session_id());
        $l_current_user_id = $l_session_data["isys_user_session__isys_obj__id"];

        /* ----------------------------------------------------------------------------------- */
        $l_dao               = isys_cmdb_dao::instance($g_comp_database);
        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_dao_person_group  = isys_cmdb_dao_category_s_person_group_members::instance($g_comp_database);
        $l_dao_reference     = new isys_contact_dao_reference($g_comp_database);
        $l_dao_workflow      = new isys_workflow_dao_action($g_comp_database);
        $l_dao_logbook       = isys_component_dao_logbook::instance($g_comp_database);
        $l_dao_mail          = isys_cmdb_dao_category_g_mail_addresses::instance($g_comp_database);
        /* ----------------------------------------------------------------------------------- */
        $l_action_id = $l_dao_workflow->create_action(C__WORKFLOW__ACTION__TYPE__CANCEL);
        $l_workflow  = $l_dao_workflow->get_workflows($l_workflow_id, null, null, C__WORKFLOW__ACTION__TYPE__ACCEPT)
            ->get_row();
        /* ----------------------------------------------------------------------------------- */
        /* bind action to workflow */
        if ($l_action_id && $l_workflow_id)
        {
            $l_dao_workflow->bind($l_workflow_id, $l_action_id);
        }
        else return false;
        /* ----------------------------------------------------------------------------------- */

        $this->set_workflow_id($l_workflow_id);

        /* ----------------------------------------------------------------------------------- */
        $l_description = $g_post["C__WF__COMPLETE_DESCRIPTION"];
        $l_measure     = $g_post["C__WF__METHOD_DESCRIPTION"];
        /* ----------------------------------------------------------------------------------- */
        $l_dao_workflow->add_parameter(
            $l_action_id,
            C__WF__PARAMETER_TYPE__TEXT,
            "description",
            $l_description
        );
        $l_dao_workflow->add_parameter(
            $l_action_id,
            C__WF__PARAMETER_TYPE__TEXT,
            "measure",
            $l_measure
        );
        /* ----------------------------------------------------------------------------------- */

        /* ----------------------------------------------------------------------------------- */
        $l_contact_id = $l_workflow["isys_workflow__isys_contact__id"];

        if (isys_settings::get('system.email.smtp-host', ''))
        {
            new isys_event_task_closed(C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED, $l_workflow_id, $l_contact_id, $l_description, $l_measure);
        }

        /* ----------------------------------------------------------------------------------- */

        $l_affected_objects = $l_dao_workflow->get_linked_objects($l_workflow_id);

        /**
         * Set the current status
         */
        $l_dao_workflow->set_status($l_workflow_id, $this->get_status());

        $l_dao_reference->load($l_workflow['isys_contact__id']);
        $l_assigned_contact = $l_dao_reference->get_data_item_array();

        /**
         * Add logbook entry
         */
        if (is_array($l_assigned_contact))
        {
            foreach ($l_assigned_contact AS $l_obj_id => $l_dummy)
            {
                if ($l_obj_id == $l_current_user_id)
                {
                    $l_object = $l_dao->get_object_by_id($l_obj_id)
                        ->get_row();
                    continue;
                }
                elseif ($l_dao->get_objTypeID($l_obj_id) == C__OBJTYPE__PERSON_GROUP)
                {
                    $l_res = $l_dao_person_group->get_selected_persons($l_obj_id);
                    while ($l_row = $l_res->get_row())
                    {
                        if ($l_row['isys_obj__id'] == $l_current_user_id)
                        {
                            $l_object = $l_dao->get_object_by_id($l_row['isys_obj__id'])
                                ->get_row();
                            continue 2;
                        }
                    }
                }
            }
            $l_description = 'Cancelled by ' . $l_object['isys_obj__title'];
            if (is_array($l_affected_objects))
            {
                foreach ($l_affected_objects AS $l_object_id)
                {
                    $l_dao_logbook->set_entry(
                        'C__LOGBOOK_EVENT__WORKFLOW_CANCELLED',
                        null,
                        null,
                        C__LOGBOOK__ALERT_LEVEL__0,
                        $l_object_id,
                        $l_workflow['isys_workflow__title'],
                        $l_workflow['isys_workflow_type__title'],
                        null,
                        null,
                        null,
                        $l_description
                    );
                }
            }
            else
            {
                $l_mod_event_manager->triggerWorkflowEvent("C__LOGBOOK_EVENT__WORKFLOW_CANCELLED", $l_description, $l_workflow_id, $l_workflow['isys_workflow_type__id']);
            }
        }

        /* Switch back to list */
        header("Location: " . isys_glob_add_to_query(C__WF__GET__TYPE, 1, isys_glob_add_to_query(C__CMDB__GET__VIEWMODE, C__WF__VIEW__LIST)));

        return true;
    }

    public function __construct()
    {

    }
}

?>