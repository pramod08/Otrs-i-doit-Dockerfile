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
 * DAO: ObjectType list for Emergency plans
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Andre Wösten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_emergency_plan extends isys_cmdb_dao_list
{
    /**
     * Method for retrieving the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_emergency_plan_list__title'          => 'LC__CMDB__CATG__EMERGENCY_PLAN_TITLE',
            'isys_obj_type__title'                          => 'LC__CMDB__OBJTYPE',
            'isys_obj__title'                               => 'LC__CMDB__CATG__GLOBAL_TITLE',
            'isys_cats_emergency_plan_list__calc_time_need' => 'LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED',
            'practice_date'                                 => 'LC__CMDB__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE'
        ];
    } // function

    /**
     * Format values in the list
     *
     * @param array $p_arrRow
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org> - 2013-07-10
     */
    public function format_row(&$p_arrRow)
    {
        global $g_loc;

        $p_arrRow["practice_date"] = $g_loc->fmt_datetime($p_arrRow["practice_date"]);
    } // function

    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__EMERGENCY_PLAN;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   mixed   $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_strSQL = 'SELECT isys_catg_emergency_plan_list__id, isys_catg_emergency_plan_list__title, isys_obj__title, isys_cats_emergency_plan_list__calc_time_need, isys_cats_emergency_plan_list__practice_actual_date AS practice_date, isys_unit_of_time__title, isys_obj__id, isys_obj_type__id, isys_obj_type__title, isys_unit_of_time__const
			FROM isys_catg_emergency_plan_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id
			LEFT OUTER JOIN isys_obj  ON isys_connection__isys_obj__id = isys_obj__id
			LEFT OUTER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_cats_emergency_plan_list ON isys_cats_emergency_plan_list__isys_obj__id = isys_obj__id
			LEFT OUTER JOIN isys_unit_of_time ON isys_cats_emergency_plan_list__isys_unit_of_time__id=isys_unit_of_time__id
			WHERE isys_catg_emergency_plan_list__isys_obj__id = ' . $this->convert_sql_id($p_objID);

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (!empty($l_cRecStatus))
        {
            $l_strSQL .= ' AND isys_catg_emergency_plan_list__status = ' . $this->convert_sql_int($l_cRecStatus);
        } // if

        return $this->retrieve($l_strSQL);
    } // function

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        if ($p_arrRow["isys_obj__id"] != null)
        {
            $l_link = isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT     => $p_arrRow["isys_obj__id"],
                    C__CMDB__GET__OBJECTTYPE => $p_arrRow["isys_obj_type__id"],
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY_GLOBAL,
                    C__CMDB__GET__CATG       => C__CATG__GLOBAL,
                    C__CMDB__GET__TREEMODE   => $l_gets[C__CMDB__GET__TREEMODE]
                ]
            );

            $p_arrRow["isys_obj__title"]                               = '<a href="' . $l_link . '"><img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" /> ' . $p_arrRow["isys_obj__title"] . '</a>';
            $p_arrRow["isys_cats_emergency_plan_list__calc_time_need"] = isys_convert::time(
                    $p_arrRow["isys_cats_emergency_plan_list__calc_time_need"],
                    $p_arrRow["isys_unit_of_time__const"],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . _L($p_arrRow["isys_unit_of_time__title"]);
        } // if
    } // function
} // class