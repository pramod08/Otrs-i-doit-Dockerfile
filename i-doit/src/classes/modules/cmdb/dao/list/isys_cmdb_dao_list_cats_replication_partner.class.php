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

class isys_cmdb_dao_list_cats_replication_partner extends isys_cmdb_dao_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__REPLICATION_PARTNER;
    } // function

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $l_replication_partner = isys_cmdb_dao_category_s_replication_partner::instance($this->m_db)
            ->get_obj_by_connection($p_row["isys_cats_replication_partner_list__isys_connection__id"]);

        $p_row["replication_partner"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($l_replication_partner["isys_obj__id"], $l_replication_partner["isys_obj__title"], C__LINK__OBJECT);
    } // function

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_replication_partner_list__id" => "ID",
            "isys_replication_type__title"           => "Replikationstyp",
            "replication_partner"                    => "Replikationspartner"
        ];
    } // function
} // class