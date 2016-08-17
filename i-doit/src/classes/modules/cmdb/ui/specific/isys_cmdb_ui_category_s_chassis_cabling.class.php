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
 * CMDB Specific category chassis.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_chassis_cabling extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_chassis_cabling $p_cat
     *
     * @global  array                                    $index_includes
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_rules  = $l_dialog_data = [];

        $l_objects = isys_cmdb_dao_category_s_chassis::instance($this->get_database_component())
            ->get_assigned_objects($l_obj_id);

        // Prepend the object-array with the chassis itself!
        array_unshift(
            $l_objects,
            $p_cat->get_object_by_id($l_obj_id)
                ->get_row()
        );

        // At first we prepare an object list for our dialog-fields.
        foreach ($l_objects as $l_object)
        {
            $l_dialog_data[$l_object['isys_obj__id']] = $l_object['isys_obj__title'];
        } // foreach

        // Add the chassis.
        $l_dialog_data[$l_obj_id] = isys_cmdb_dao_category_s_chassis::instance($this->get_database_component())
            ->get_obj_name_by_id_as_string($l_obj_id);

        foreach ($l_objects as $l_object)
        {
            $l_log_ports = $p_cat->get_log_ports_for_ui($l_object['isys_obj__id'], $l_rules);
            $l_ports     = $p_cat->get_ports_for_ui($l_object['isys_obj__id'], $l_rules);
            $l_fc_ports  = $p_cat->get_fc_ports_for_ui($l_object['isys_obj__id'], $l_rules);

            $l_log_port_dialog_name = 'C__CMDB__CATS__CHASSIS_CABLING__NEW_LOG_PORT_' . $l_object['isys_obj__id'];
            $l_log_port_l2net_name  = 'C__CMDB__CATS__CHASSIS_CABLING__NEW_LOG_PORT_L2NET_' . $l_object['isys_obj__id'];

            $l_tpl_objects[] = [
                'id'                   => $l_object['isys_obj__id'],
                'title'                => $l_object['isys_obj__title'],
                'type_id'              => $l_object['isys_obj_type__id'],
                'type_title'           => _L($l_object['isys_obj_type__title']),
                'counter'              => [
                    'ports'     => count($l_ports),
                    'fc_ports'  => count($l_fc_ports),
                    'log_ports' => count($l_log_ports)
                ],
                'ports'                => $l_ports,
                'log_ports'            => $l_log_ports,
                'fc_ports'             => $l_fc_ports,
                'log_port_dialog_name' => $l_log_port_dialog_name,
                'log_port_l2net_name'  => $l_log_port_l2net_name
            ];

            $l_data = $l_dialog_data;

            unset($l_data[$l_object['isys_obj__id']]);

            // Now we add the dialog-data to the form element, excluding the current object.
            $l_rules[$l_log_port_dialog_name]['p_arData'] = $l_data;
        } // foreach

        $this->get_template_component()
            ->assign(
                'editmode',
                $this->get_template_component()
                    ->editmode()
            )
            ->assign('objects', $l_tpl_objects)
            ->assign('bShowCommentary', 0)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function
} // class