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
 * List DAO: Service.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_service extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . //'[' . C__PROPERTY_TYPE__STATIC . ',"application_manufacturer","isys_application_manufacturer__title","LC__CMDB__CATG__MANUFACTURE","isys_cmdb_dao_category_s_service::get_properties_ng",false],' .
        //'[' . C__PROPERTY_TYPE__STATIC . ',"release","isys_cats_application_list__release","LC__CMDB__CATS__SERVICE_RELEASE","isys_cmdb_dao_category_s_service::get_properties_ng",false],' .
        //'[' . C__PROPERTY_TYPE__STATIC . ',"specification","isys_cats_application_list__specification","LC__CMDB__CATS__SERVICE_SPECIFICATION","isys_cmdb_dao_category_s_service::get_properties_ng",false],' .
        '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_created",false,"LC__TASK__DETAIL__WORKORDER__CREATION_DATE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_created"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_changed",false,"LC__CMDB__LAST_CHANGE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_changed"]],' . //'[' . C__PROPERTY_TYPE__STATIC . ',"purpose","isys_purpose__title","LC__CMDB__CATG__GLOBAL_PURPOSE","isys_cmdb_dao_category_g_global::get_properties_ng",false],' .
        '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_cmdb_status",false,"LC__UNIVERSAL__CMDB_STATUS","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_cmdb_status"]]]';
    } // function

    /**
     * Method for retrieving the default list query if not user defined.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function get_default_list_query()
    {
        return "SELECT
			obj_main.isys_obj__id,
			obj_main.isys_obj__id AS '__id__',
			obj_main.isys_obj__title,
			obj_main.isys_obj__updated,
			obj_main.isys_obj__created,
			obj_main.isys_obj__isys_cmdb_status__id,
			isys_cmdb_status__title

			FROM isys_obj obj_main
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__SERVICE) . ") ";
    } // function
} // class