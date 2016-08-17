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
 * CMDB Specific view category for chassis.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_cmdb_ui_category_s_chassis_view extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_chassis_view $p_cat
     *
     * @global  array                                 $index_includes
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules          = $l_slots = [];
        $l_slot_positions = [
            C__INSERTION__FRONT => [],
            C__INSERTION__REAR  => []
        ];
        $l_catdata        = $p_cat->get_general_data();
        $l_template       = $this->get_template_component();

        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];

        // Fill the category forms.
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__X_FRONT']['p_strValue']                                                = $l_catdata['isys_cats_chassis_view_list__front_width'] ?: 0;
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT']['p_strValue']                                                = $l_catdata['isys_cats_chassis_view_list__front_height'] ?: 0;
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT']['p_arData']                                               = serialize($p_cat::get_chassis_matrix_sizes());
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT']['p_strSelectedID']                                        = $l_catdata['isys_cats_chassis_view_list__front_size'];
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__X_REAR']['p_strValue']                                                 = $l_catdata['isys_cats_chassis_view_list__rear_width'] ?: 0;
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__Y_REAR']['p_strValue']                                                 = $l_catdata['isys_cats_chassis_view_list__rear_height'] ?: 0;
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR']['p_arData']                                                = serialize($p_cat::get_chassis_matrix_sizes());
        $l_rules['C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR']['p_strSelectedID']                                         = $l_catdata['isys_cats_chassis_view_list__rear_size'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_cats_chassis_view_list__description'];

        // If this is a new category-entry, we set the default size to "L".
        if ($l_catdata === null)
        {
            $l_rules['C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT']['p_strSelectedID'] = 3;
            $l_rules['C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR']['p_strSelectedID']  = 3;
        } // if

        $l_slots_res = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component)
            ->get_data(null, $l_obj_id);

        if ($l_slots_res->num_rows() > 0)
        {
            while ($l_slot_row = $l_slots_res->get_row())
            {
                $l_key                       = $l_slot_row['isys_cats_chassis_slot_list__title'] . '-' . $l_slot_row['isys_cats_chassis_slot_list__id'];
                $l_slots[$l_key]             = $l_slot_row;
                $l_slots[$l_key]['assigned'] = ($l_slot_row['isys_cats_chassis_slot_list__x_from'] !== null && $l_slot_row['isys_cats_chassis_slot_list__x_to'] !== null);

                $l_slot_positions[$l_slot_row['isys_cats_chassis_slot_list__insertion']][] = [
                    'id'     => $l_slot_row['isys_cats_chassis_slot_list__id'],
                    'title'  => isys_glob_htmlentities($l_slot_row['isys_cats_chassis_slot_list__title']),
                    'x_from' => $l_slot_row['isys_cats_chassis_slot_list__x_from'],
                    'x_to'   => $l_slot_row['isys_cats_chassis_slot_list__x_to'],
                    'y_from' => $l_slot_row['isys_cats_chassis_slot_list__y_from'],
                    'y_to'   => $l_slot_row['isys_cats_chassis_slot_list__y_to']
                ];
            } // while

            ksort($l_slots);

            // We only needed the keys for sorting matters, so now we'll remove them.
            $l_slots = array_values($l_slots);
        } // if

        // Preparing the matrix and devices for front and rear view.
        $l_matrix = [
            C__INSERTION__FRONT => $p_cat::process_matrix(
                $l_catdata['isys_cats_chassis_view_list__front_width'],
                $l_catdata['isys_cats_chassis_view_list__front_height'],
                $l_slot_positions[C__INSERTION__FRONT]
            ),
            C__INSERTION__REAR  => $p_cat::process_matrix(
                $l_catdata['isys_cats_chassis_view_list__rear_width'],
                $l_catdata['isys_cats_chassis_view_list__rear_height'],
                $l_slot_positions[C__INSERTION__REAR]
            )
        ];

        $l_devices = [
            C__INSERTION__FRONT => $p_cat->process_matrix_devices($l_obj_id, C__INSERTION__FRONT),
            C__INSERTION__REAR  => $p_cat->process_matrix_devices($l_obj_id, C__INSERTION__REAR)
        ];

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__PRINT)
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        // Assign all the data to the template.
        $l_template->assign('cat_data', $l_catdata)
            ->assign('slots', $l_slots)
            ->assign('obj_id', $l_obj_id)
            ->assign('edit_mode', (int) isys_glob_is_edit_mode())
            ->assign('matrix_front', isys_format_json::encode($l_matrix[C__INSERTION__FRONT]))
            ->assign('devices_front', isys_format_json::encode($l_devices[C__INSERTION__FRONT]))
            ->assign('matrix_rear', isys_format_json::encode($l_matrix[C__INSERTION__REAR]))
            ->assign('devices_rear', isys_format_json::encode($l_devices[C__INSERTION__REAR]))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class