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
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_ws_assignment extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CMDB__SUBCAT__WS_ASSIGNMENT;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param   null    $p_strTable
     * @param   integer $p_objID
     * @param   null    $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_strTable = null, $p_objID, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_s_ws_assignment::instance($this->get_database_component())
            ->get_data(null, $p_objID, "", null, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    } // function

    /**
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $p_arrRow["isys_obj__title"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_arrRow["isys_obj__id"], $p_arrRow['isys_obj__title'], C__LINK__OBJECT);
    } // function

    /**
     * Gets flag for the rec status dialog
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
     * @return  string
     */
    public function make_row_link()
    {
        return '#';
    } // function

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title" => "LC__CMDB__CATG__ASSIGNED_OBJECTS"
        ];
    } // function
} // class