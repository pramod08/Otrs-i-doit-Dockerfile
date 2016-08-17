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
class isys_workflow_action_assign extends isys_workflow_action
{
    /**
     * @desc return the current status of the workflow, when this action was processed
     * @return int
     */
    public function get_status()
    {
        return C__TASK__STATUS__ASSIGNMENT;
    }

    public function get_template()
    {
        return "workflow/detail/actions/assign.tpl";
    }

    public function handle()
    {
        global $g_comp_template;
        global $g_comp_session;
        global $g_comp_database;

        /* get current user id */
        $l_session_data    = $g_comp_session->get_session_data($g_comp_session->get_session_id());
        $l_current_user_id = $l_session_data["isys_user_session__isys_obj__id"];

        $g_comp_template->assign("assign_id", $this->get_id());

        /**
         * @var isys_contact_dao_reference
         */
        $l_dao_reference      = $this->get_to();
        $l_dao                = new isys_cmdb_dao($g_comp_database);
        $l_contact_person_dao = new isys_contact_dao_person($g_comp_database);
        $l_contact_id         = $l_dao_reference->get_id();
        /* ----------------------------------------------------------------------------------- */
        $l_data_items = $l_dao_reference->get_data_item_array();
        $l_assigned   = "";
        $l_ar_ids     = [];

        if (is_array($l_data_items))
        {
            foreach ($l_data_items as $l_key => $l_value)
            {
                /* Special-Handling for assigned groups */
                $l_object = $l_dao->get_object_by_id($l_key)
                    ->get_row();
                if ($l_object['isys_obj_type__const'] == 'C__OBJTYPE__PERSON_GROUP')
                {
                    /* Check whether we cached users groups already */
                    if (empty($l_user_groups))
                    {
                        $l_groups_res = $l_contact_person_dao->get_groups_by_id($l_current_user_id);

                        if ($l_groups_res->num_rows())
                        {
                            while ($l_row = $l_groups_res->get_row())
                            {
                                $l_user_groups[] = $l_row['isys_person_2_group__isys_obj__id__group'];
                            }
                        }
                        else
                        {
                            $l_user_groups = [];
                        }
                    }

                    if (in_array($l_key, $l_user_groups)) $l_key = $l_current_user_id;
                }

                if ($l_key == $l_current_user_id)
                {
                    $g_comp_template->assign("g_assign", ["me" => $l_key]);;
                }

                $l_ar_ids[] = $l_key;
            }
        }

        /* ----------------------------------------------------------------------------------- */
        $l_person_ids    = [];
        $l_dao_reference = $this->get_from();

        $l_data_items = $l_dao_reference->get_data_item_array();
        if (is_array($l_data_items))
        {
            foreach ($l_data_items as $l_key => $l_value)
            {
                $l_person_ids[] = $l_key;
            }
        }

        $g_comp_template->assign("g_assigned_contact_id", $l_contact_id);
        $g_comp_template->assign("g_assigned_contact", isys_format_json::encode($l_person_ids));

        if (count($l_ar_ids) > 0) $g_comp_template->assign("g_assigned_users", implode(",", $l_ar_ids));

        /* Assigneable Handling */
        $l_parentID = $this->get_parent_workflow();

        global $g_comp_database;
        $l_workflow_dao = new isys_workflow_dao($g_comp_database);
        if ($l_parentID > 0 && $l_workflow_dao->get_workflow_status($l_parentID) && $l_workflow_dao->get_workflow_type_by_id($l_parentID) != C__WORKFLOW_TYPE__CHECKLIST)
        {
            /* Get status of parent workflow */
            $g_comp_template->assign('closeable', ($l_workflow_dao->get_workflow_status($l_parentID) == C__WORKFLOW__ACTION__TYPE__COMPLETE) ? true : false);
        }
        else
        {
            $g_comp_template->assign('closeable', true);
        }
    }

    /**
     * @desc
     *
     * @param int $p_id
     */
    public function save($p_workflow_id, $p_to = 0)
    {
        global $g_comp_database;

        $l_dao_workflow = new isys_workflow_dao_action($g_comp_database);

        $l_action_id = $l_dao_workflow->create_action(C__WORKFLOW__ACTION__TYPE__ASSIGN, $p_to);

        /* bind action to the newly created workflow */
        if ($l_action_id && $p_workflow_id)
        {
            $l_dao_workflow->bind($p_workflow_id, $l_action_id);

            /**
             * Set the current status
             */
            $l_dao_workflow->set_status($p_workflow_id, $this->get_status());

            return true;

        }
        else return false;
    }

    public function __construct()
    {

    }
}

?>