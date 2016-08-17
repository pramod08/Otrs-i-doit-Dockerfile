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
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_cmdb_ui_category_g_rack_view extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for specific category room.
     *
     * @param   isys_cmdb_dao_category_g_rack_view $p_cat
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $i        = 0;
        $l_obj_id = (int) $_GET[C__CMDB__GET__OBJECT];
        $l_racks  = [];

        $l_rack_dao  = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component());
        $l_child_res = isys_factory::get_instance('isys_cmdb_dao_category_g_location', $this->get_database_component())
            ->get_data(
                null,
                null,
                'AND isys_catg_location_list__parentid = ' . $p_cat->convert_sql_id(
                    $l_obj_id
                ) . ' AND isys_obj_type__const = "C__OBJTYPE__ENCLOSURE" AND isys_obj__status = ' . $p_cat->convert_sql_int(C__RECORD_STATUS__NORMAL)
            );

        while ($l_child_row = $l_child_res->get_row())
        {
            $l_object                                                                         = $l_rack_dao->prepare_rack_data(
                $l_child_row['isys_obj__id'],
                $l_child_row['isys_obj__title']
            );
            $l_racks[str_pad($l_object['position_in_room'], 3, '0', STR_PAD_LEFT) . '-' . $i] = $l_object;
            $i++;
        } // while

        ksort($l_racks);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT);

        // Apply rules.
        $this->get_template_component()
            ->assign(
                'has_edit_right',
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $l_obj_id, 'C__CATG__RACK_VIEW')
            )
            ->assign('obj_id', $l_obj_id)
            ->assign('object_cnt', count($l_racks))
            ->assign('racks', array_values($l_racks))
            ->assign('this_page', '?' . str_replace('ajax', '', $_SERVER['QUERY_STRING']))
            ->assign("bShowCommentary", 0);
    } // function
} // class