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
 * DAO: ObjectType list for manuals
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org> - 2007-08-21
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_file_object extends isys_cmdb_dao_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CMDB__SUBCAT__FILE_OBJECTS;
    } // function

    /**
     * Return category type constant.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_general
     */
    public function get_result($p_table = null, $p_obj_id = null, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_condition = ' AND (isys_catg_file_list__status = ' . $this->convert_sql_int($l_cRecStatus) . '
			OR isys_catg_manual_list__status = ' . $this->convert_sql_int($l_cRecStatus) . '
			OR isys_catg_emergency_plan_list__status = ' . $this->convert_sql_int($l_cRecStatus) . ') ';

        return isys_cmdb_dao_category_s_file_object::instance($this->m_db)
            ->get_data(null, $p_obj_id, $l_condition, null, C__RECORD_STATUS__NORMAL);
    } // function

    /**
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_dao = new isys_cmdb_dao($this->m_db);

        if (!empty($p_arrRow["isys_catg_manual_list__id"]))
        {
            $p_arrRow["title"]      = $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_catg_manual_list__isys_obj__id"]);
            $p_arrRow["type_title"] = $l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($p_arrRow["isys_catg_manual_list__isys_obj__id"]));
            $p_arrRow["cat_title"]  = _L($l_dao->get_catg_name_by_id_as_string(C__CATG__MANUAL));
        }
        else if (!empty($p_arrRow["isys_catg_file_list__id"]))
        {
            $p_arrRow["title"]      = $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_catg_file_list__isys_obj__id"]);
            $p_arrRow["type_title"] = $l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($p_arrRow["isys_catg_file_list__isys_obj__id"]));
            $p_arrRow["cat_title"]  = _L($l_dao->get_catg_name_by_id_as_string(C__CATG__FILE));
        }
        else if (!empty($p_arrRow["isys_catg_emergency_plan_list__id"]))
        {
            $p_arrRow["title"]      = $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_catg_emergency_plan_list__isys_obj__id"]);
            $p_arrRow["type_title"] = $l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($p_arrRow["isys_catg_emergency_plan_list__isys_obj__id"]));
            $p_arrRow["cat_title"]  = _L($l_dao->get_catg_name_by_id_as_string(C__CATG__EMERGENCY_PLAN));
        } // if
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "title"      => "LC__CMDB__CATG__GLOBAL_TITLE",
            "type_title" => "LC__CMDB__OBJTYPE",
            "cat_title"  => "LC__CMDB__CATG__CATEGORY"
        ];
    } // function
} // class