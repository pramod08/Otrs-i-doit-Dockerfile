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
 * CMDB UI: global category for the ticketing connector
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Steven Bohm <sbohm@synetics.de>
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @author     Benjamin Heisig <bheisig@synetics.de>
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_virtual_tickets extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Gets tickets by object identifier.
     *
     * @param   isys_connector_ticketing $p_connector
     * @param   integer                  $p_objectID
     *
     * @return  array
     */
    private static function get_tickets($p_connector, $p_objectID)
    {
        $l_return  = [];
        $l_tickets = $p_connector->get_tickets_by_cmdb_object($p_objectID);

        foreach ($l_tickets as $l_ticket_value)
        {
            $l_return[$l_ticket_value["id"]] = [
                'subject'        => $l_ticket_value['subject'],
                'created'        => $l_ticket_value["created"],
                'owner'          => $l_ticket_value['owner'],
                'requestor'      => $l_ticket_value['requestors'],
                'starts'         => $l_ticket_value['start_time'],
                'started'        => $l_ticket_value['started'],
                'lastupdated'    => $l_ticket_value['last_updated'],
                'priority'       => $l_ticket_value["priority"],
                'queue'          => $l_ticket_value["queue"],
                'status'         => $l_ticket_value["status"],
                'customcategory' => $l_ticket_value['custom_fields']['kategorie'],
                'customobjects'  => substr($l_ticket_value['custom_fields']['i-doit objects'], 1, -1),
                'custompriority' => $l_ticket_value['custom_fields']['priority'],
                'link'           => $p_connector->get_ticket_url($l_ticket_value["id"])
            ];
        } // foreach

        return $l_return;
    }

    /**
     * Processes view/edit mode.
     *
     * @global array                 $index_includes
     *
     * @param isys_cmdb_dao_category $p_cat Category's DAO
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_database, $index_includes;

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__NEW);

        try
        {
            $l_tickets = [];
            $l_dao_tts = new isys_tts_dao($g_comp_database);
            $l_dao     = new isys_cmdb_dao_category_g_assigned_logical_unit($g_comp_database);
            $l_rt      = $l_dao_tts->get_connector();

            /* Get ticket over AJAX */
            if (isset($_POST['get_ticket']))
            {
                $l_ticket         = $l_rt->get_ticket($_POST['get_ticket']);
                $l_ticket['link'] = $l_rt->get_ticket_url($l_ticket['id']);
                echo isys_format_json::encode($l_ticket);
            }

            $l_workstation = null;
            $l_object_id   = intval($_GET[C__CMDB__GET__OBJECT]);

            if ($l_object_id > 0)
            {

                //Check Objecttype of our object
                $l_object = $l_dao->get_object_by_id($l_object_id)
                    ->get_row();

                if ($l_object['isys_obj__isys_obj_type__id'] != C__OBJTYPE__WORKSTATION)
                {
                    $l_tickets = self::get_tickets($l_rt, $l_object_id);
                }
                else
                {
                    $l_workstation = [
                        'object_id'    => $l_object['isys_obj__id'],
                        'object_title' => $l_object['isys_obj__title'],
                        'object_type'  => _L($l_object['isys_obj_type__title']),
                        'tickets'      => self::get_tickets($l_rt, $l_object_id)
                    ];

                    /* Retrieve workstation components */
                    $l_res = $l_dao->get_selected_objects($l_object_id);

                    if ($l_res->num_rows())
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            $l_workstation['components'][] = [
                                'object_id'    => $l_row['isys_obj__id'],
                                'object_title' => $l_row['isys_obj__title'],
                                'object_type'  => _L($l_dao->get_objtype_name_by_id_as_string($l_row['isys_obj__isys_obj_type__id'])),
                                'tickets'      => self::get_tickets($l_rt, intval($l_row['isys_obj__id']))
                            ];
                        }
                    }
                }
            }

            if (is_array($l_tickets))
            {
                foreach ($l_tickets as $l_index => $l_log_unit)
                {
                    if (isset($l_log_unit['tickets'][0]))
                    {
                        unset($l_tickets[$l_index]);
                    }
                }
            }

            // Assign smarty parameters.
            $this->get_template_component()
                ->assign('tickets', $l_tickets)
                ->assign('workstation', $l_workstation)
                ->assign('ticket_new_url', $l_rt->create_new_ticket_url($l_object_id))
                ->assign('ajax_url', "?" . http_build_query($_GET, null, "&") . "&call=category");

            $index_includes['contentbottomcontent'] = $this->deactivate_commentary()
                ->get_template();
        }
        catch (isys_exception_general $e)
        {
            $this->get_template_component()
                ->assign("tts_processing_error", $e->getMessage());
        }
    } // function
} // class