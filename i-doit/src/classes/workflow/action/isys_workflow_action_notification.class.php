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
class isys_workflow_action_notification extends isys_workflow_action
{

    /**
     * @desc
     *
     * @param int $p_id
     */
    public function save($p_workflow_id, $p_req, $p_template = C__EMAIL_TEMPLATE__TASK__NOTIFICATION, $p_contact_id = null, $p_action_id = null)
    {

        global $g_comp_database, $g_comp_template;

        /**
         * @var isys_contact_dao_reference
         */
        $l_dao_reference = new isys_contact_dao_reference($g_comp_database);

        if (is_null($p_contact_id))
        {
            $l_contact_id = $p_req->get_to();
        }
        else
        {
            $l_contact_id = $p_contact_id;
        }

        if (!empty($l_contact_id))
        {
            $l_dao_reference->load($l_contact_id);
            //$l_contact_person = new isys_contact_dao_person($l_dao_reference->get_database_component());
            $l_data_items = $l_dao_reference->get_data_item_array();
            $l_assigned   = "";

            if (is_array($l_data_items))
            {
                $l_send_status = [];
                foreach ($l_data_items as $l_key => $l_value)
                {

                    if ($l_value)
                    {
                        $l_userdata = $l_dao_reference->get_data_item_info($l_key)
                            ->get_row();
                        if ($l_userdata['isys_obj__isys_obj_type__id'] != C__OBJTYPE__PERSON_GROUP)
                        {
                            $l_id           = $l_userdata["isys_obj__id"];
                            $l_assign["me"] = $l_id;

                            $l_assigned .= $l_userdata["isys_obj__title"];
                            $l_send_status[$l_id] = true;
                            if (isys_settings::get('system.email.smtp-host', ''))
                            {
                                $l_mail_event = new isys_event_task_notification(
                                    $p_template, $p_workflow_id, null, $l_assigned, null, $l_userdata["isys_cats_person_list__mail_address"]
                                );
                            }
                        }
                        else
                        {
                            $l_contact_group = new isys_contact_dao_group($l_dao_reference->get_database_component());
                            $l_persons_res   = $l_contact_group->get_persons_by_id($l_key);

                            if ($l_persons_res->num_rows())
                            {
                                while ($l_row = $l_persons_res->get_row())
                                {
                                    if (!isset($l_send_status[$l_row['isys_obj__id']]))
                                    {
                                        $l_userdata                            = $l_dao_reference->get_data_item_info($l_row['isys_obj__id'])
                                            ->get_row();
                                        $l_send_status[$l_row['isys_obj__id']] = true;
                                        if (isys_settings::get('system.email.smtp-host', ''))
                                        {
                                            new isys_event_task_notification(
                                                $p_template, $p_workflow_id, null, $l_assigned, null, $l_userdata["isys_cats_person_list__mail_address"]
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function __construct()
    {

    }
}

?>