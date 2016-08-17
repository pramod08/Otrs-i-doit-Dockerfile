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
class isys_workflow_action_accept extends isys_workflow_action
{
    /**
     * @desc return the current status of the workflow, when this action was processed
     * @return int
     */
    public function get_status()
    {
        return C__TASK__STATUS__OPEN;
    }

    public function get_template()
    {
        return "workflow/detail/actions/accept.tpl";
    }

    public function handle()
    {
        global $g_comp_template;
        global $g_comp_session;

        $g_comp_template->assign("g_accepted", true);

        /* get current user id */
        $l_session_data    = $g_comp_session->get_session_data($g_comp_session->get_session_id());
        $l_current_user_id = $l_session_data["isys_user_session__isys_obj__id"];

        /**
         * @var isys_contact_dao_reference
         */
        $l_dao_reference = $this->get_to();

        /* ----------------------------------------------------------------------------------- */
        $l_data_items = $l_dao_reference->get_data_item_array();
        $l_assigned   = "";

        if (is_array($l_data_items))
        {
            foreach ($l_data_items as $l_key => $l_value)
            {

                if ($l_value)
                {
                    $l_userdata = $l_dao_reference->get_data_item_info($l_key, C__CONTACT__DATA_ITEM__PERSON_INTERN)
                        ->get_row();

                    $l_id           = $l_userdata["isys_obj__id"];
                    $l_assign["me"] = $l_id;

                    if ($l_id == $l_current_user_id)
                    {
                        $g_comp_template->assign("g_assign", $l_assign);;
                    }

                    $l_assigned .= $l_userdata["isys_obj__title"];
                }
            }
        }
        /* ----------------------------------------------------------------------------------- */
        $g_comp_template->assign("g_accepted_users", $l_assigned);

    }

    /**
     * @desc
     *
     * @param int $p_id
     */
    public function save($p_workflow_id, $p_to)
    {
        global $g_comp_database;
        global $g_comp_session;

        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_dao               = isys_cmdb_dao::instance($g_comp_database);
        $l_dao_workflow      = new isys_workflow_dao_action($g_comp_database);
        $l_dao_reference     = new isys_contact_dao_reference($g_comp_database);
        $l_dao_person_group  = isys_cmdb_dao_category_s_person_group_members::instance($g_comp_database);
        $l_dao_logbook       = isys_component_dao_logbook::instance($g_comp_database);

        /* get current user id */
        $l_session_data    = $g_comp_session->get_session_data($g_comp_session->get_session_id());
        $l_current_user_id = $l_session_data["isys_user_session__isys_obj__id"];

        $l_action_id = $l_dao_workflow->create_action(C__WORKFLOW__ACTION__TYPE__ACCEPT, $this->create_contact_by_person_intern($l_current_user_id));

        /**
         * @desc send notification email
         */
        $l_workflow   = $l_dao_workflow->get_workflows($p_workflow_id, null, null, C__WORKFLOW__ACTION__TYPE__ASSIGN)
            ->get_row();
        $l_contact_id = $l_workflow["isys_workflow__isys_contact__id"];
        /**
         * @desc load contact
         */
        $l_dao_reference->load($l_contact_id);
        /**
         * @desc get contact info
         */
        $l_person = $l_dao_reference->get_data_item_array();

        $l_dao_person_intern = new isys_contact_dao_person($g_comp_database);
        if (isys_settings::get('system.email.smtp-host', '') && is_array($l_person))
        {
            $l_data     = $l_dao_person_intern->get_data_by_id($l_current_user_id)
                ->get_row();
            $l_fullname = trim($l_data["isys_cats_person_list__first_name"] . ' ' . $l_data["isys_cats_person_list__last_name"]);

            new isys_event_task_accept(C__EMAIL_TEMPLATE__TASK__ACCEPT, $p_workflow_id, $l_contact_id, $l_fullname);
        }

        /* bind action to the newly created workflow */
        if ($l_action_id && $p_workflow_id)
        {
            $l_dao_workflow->bind($p_workflow_id, $l_action_id);

            $l_affected_objects = $l_dao_workflow->get_linked_objects($p_workflow_id);

            /**
             * Set the current status
             */
            $l_dao_workflow->set_status($p_workflow_id, $this->get_status());

            // Add logbook entry.
            // Workflow has been accepted
            $l_dao_reference->load($l_workflow['isys_contact__id']);
            $l_assigned_contact = $l_dao_reference->get_data_item_array();
            $l_dao              = new isys_cmdb_dao($g_comp_database);
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

                if (isset($l_object['isys_obj__title']))
                {
                    $l_description = 'Accepted by ' . $l_object['isys_obj__title'];
                    if (is_array($l_affected_objects))
                    {
                        foreach ($l_affected_objects AS $l_object_id)
                        {
                            $l_dao_logbook->set_entry(
                                'C__LOGBOOK_EVENT__WORKFLOW_ACCEPTED',
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
                        $l_mod_event_manager->triggerWorkflowEvent(
                            "C__LOGBOOK_EVENT__WORKFLOW_ACCEPTED",
                            $l_description,
                            $p_workflow_id,
                            $l_workflow['isys_workflow_type__id']
                        );
                    }
                }

            }
        }
        else return false;
    }

    public function __construct()
    {

    }
}

?>