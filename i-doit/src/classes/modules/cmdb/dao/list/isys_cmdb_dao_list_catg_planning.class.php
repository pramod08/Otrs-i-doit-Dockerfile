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
 * DAO: Category list for planning
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_planning extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Format row will be called for each row to format certain fields.
     *
     * @param  array &$p_row
     */
    public function format_row(&$p_row)
    {
        global $g_loc;

        $p_row["isys_catg_planning_list__start"] = $g_loc->fmt_date($p_row["isys_catg_planning_list__start"]);
        $p_row["isys_catg_planning_list__end"]   = $g_loc->fmt_date($p_row["isys_catg_planning_list__end"]);
    } // function

    /**
     * Every list class must have this method to return its category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__PLANNING;
    } // function

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Get result method.
     *
     * @param   string  $p_str
     * @param   integer $p_fk_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_result($p_str = null, $p_unused, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        return $this->m_cat_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, $l_cRecStatus, "isys_catg_planning_list__end ASC");
    } // function

    /**
     * Modify row method will be called for each row to alter its content.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["isys_cmdb_status__title"] = '<div class="cmdb-marker" style="background-color:#' . $p_row['isys_cmdb_status__color'] . ';"></div>' . _L(
                $p_row["isys_cmdb_status__title"]
            );
    } // function

    /**
     * Method for getting the table column names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cmdb_status__title"        => "LC__UNIVERSAL__CMDB_STATUS",
            "isys_catg_planning_list__start" => "LC__UNIVERSAL__FROM",
            "isys_catg_planning_list__end"   => "LC__UNIVERSAL__TO"
        ];
    } // function
} // class