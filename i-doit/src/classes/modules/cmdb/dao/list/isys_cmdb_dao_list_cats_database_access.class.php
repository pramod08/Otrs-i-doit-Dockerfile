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
class isys_cmdb_dao_list_cats_database_access extends isys_cmdb_dao_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__DATABASE_ACCESS;
    } // function

    /**
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
        $p_row["assignment_title"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_row["isys_connection__isys_obj__id"], $p_row["assignment_title"], C__LINK__OBJECT);
    } // function

    /**
     * Gets flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "assignment_title" => _L("LC__CMDB__OBJTYPE__APPLICATION") . " / " . _L("LC__CMDB__OBJTYPE__SERVICE")
        ];
    } // function

    /**
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    } // function
} // class