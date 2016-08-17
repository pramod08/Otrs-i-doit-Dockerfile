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
class isys_cmdb_dao_list_cats_database_links extends isys_cmdb_dao_list
{
    /**
     * @return integer
     */
    public function get_category()
    {
        return C__CATS__DATABASE_LINKS;
    } // function

    /**
     * @return integer
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
        $p_row["isys_cats_database_links_list__public"] = ($p_row["isys_cats_database_links_list__public"]) ? _L('LC__UNIVERSAL__YES') : _L('LC__UNIVERSAL__NO');
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_database_links_list__title"       => "LC__UNIVERSAL__TITLE",
            "schema_title"                               => "LC__OBJTYPE__DATABASE_SCHEMA",
            "isys_cats_database_links_list__target_user" => "LC__CMDB__CATS__DATABASE_LINKS__TARGET_USER",
            "isys_cats_database_links_list__owner"       => "LC__CMDB__CATS__DATABASE_LINKS__OWNER",
            "isys_cats_database_links_list__public"      => "LC__UNIVERSAL__PUBLIC"
        ];
    } // function
} // class