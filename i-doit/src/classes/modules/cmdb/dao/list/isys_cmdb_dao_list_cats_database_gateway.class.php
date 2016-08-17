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
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_database_gateway extends isys_cmdb_dao_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__DATABASE_GATEWAY;
    } // function

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["target"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info(
                $p_row["isys_connection__isys_obj__id"],
                isys_cmdb_dao::instance($this->m_db)
                    ->get_obj_name_by_id_as_string($p_row["isys_connection__isys_obj__id"]),
                C__LINK__OBJECT
            );
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_database_gateway_list__type" => "LC__CMDB__CATS__DATABASE_GATEWAY__GATEWAY_TYPE",
            "isys_cats_database_gateway_list__host" => "LC__CMDB__CATS__DATABASE_GATEWAY__HOST",
            "isys_cats_database_gateway_list__port" => "LC__CATD__PORT",
            "isys_cats_database_gateway_list__user" => "LC__CMDB__CATS__DATABASE_GATEWAY__USER",
            "target"                                => "LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA"
        ];
    } // function
} // class