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
 * i-doit.
 *
 * DAO: Category list for contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_object extends isys_cmdb_dao_list
{
    /**
     * Method for returning the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__OBJECT;
    } // function

    /**
     * Method for returning the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();

        $p_arrRow["isys_obj__title"] = $l_quick_info->get_quick_info($p_arrRow["isys_obj__id"], $p_arrRow["isys_obj__title"], C__LINK__OBJECT, 80);
    } // function

    /**
     * Gets flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Method for returning the fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__id"         => "LC__UNIVERSAL__ID",
            "isys_obj__title"      => "LC_UNIVERSAL__OBJECT",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE"
        ];
    } // function

    /**
     * Method for retrieving the row-link.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    } // function
} // class