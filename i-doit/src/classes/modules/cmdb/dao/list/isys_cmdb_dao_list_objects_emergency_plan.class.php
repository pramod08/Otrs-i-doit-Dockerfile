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
 * List DAO: Emergency plan.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_emergency_plan extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_time_needed",false,"LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED","isys_cmdb_dao_category_s_emergency_plan::get_dynamic_properties",["isys_cmdb_dao_category_s_emergency_plan","dynamic_property_callback_time_needed"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"practice_date","isys_cats_emergency_plan_list__practice_actual_date","LC__CMDB__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE","isys_cmdb_dao_category_s_emergency_plan::get_properties",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_created",false,"LC__TASK__DETAIL__WORKORDER__CREATION_DATE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_created"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_changed",false,"LC__CMDB__LAST_CHANGE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_changed"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"purpose","isys_purpose__title","LC__CMDB__CATG__GLOBAL_PURPOSE","isys_cmdb_dao_category_g_global::get_properties",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_cmdb_status",false,"LC__UNIVERSAL__CMDB_STATUS","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_cmdb_status"]]]';
    } // function

    /**
     * Method for retrieving the default list query if not user defined.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_query()
    {
        return "SELECT
			obj_main.*,
			obj_main.isys_obj__id AS '__id__',
			j3.isys_cats_emergency_plan_list__practice_actual_date,
			jn1.isys_purpose__title

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_catg_global_list AS j2 ON j2.isys_catg_global_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_purpose AS jn1 ON jn1.isys_purpose__id = j2.isys_catg_global_list__isys_purpose__id
			LEFT JOIN isys_cats_emergency_plan_list AS j3 ON j3.isys_cats_emergency_plan_list__isys_obj__id = obj_main.isys_obj__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__EMERGENCY_PLAN) . ") ";
    } // function
} // class