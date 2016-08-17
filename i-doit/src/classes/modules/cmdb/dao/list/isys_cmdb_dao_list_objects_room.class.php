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
 * List DAO: Room.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_room extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_location_path",false,"LC__CMDB__CATG__LOCATION_PATH","isys_cmdb_dao_category_g_location::get_dynamic_properties",["isys_cmdb_dao_category_g_location","dynamic_property_callback_location_path"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"floor","isys_cats_room_list__floor","LC__CMDB__CATS__ROOM_FLOOR","isys_cmdb_dao_category_s_room::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"number","isys_cats_room_list__number","LC__CMDB__CATS__ROOM_NUMBER","isys_cmdb_dao_category_s_room::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"type","isys_room_type__title","LC__CMDB__CATG__TYPE","isys_cmdb_dao_category_s_room::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"purpose","isys_purpose__title","LC__CMDB__CATG__GLOBAL_PURPOSE","isys_cmdb_dao_category_g_global::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_cmdb_status",false,"LC__UNIVERSAL__CMDB_STATUS","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_cmdb_status"]]]';
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
			j6.isys_cats_room_list__floor,
			j6.isys_cats_room_list__number,
			jn3.isys_room_type__title,
			jn1.isys_purpose__title

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_catg_global_list AS j2 ON j2.isys_catg_global_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_purpose AS jn1 ON jn1.isys_purpose__id = j2.isys_catg_global_list__isys_purpose__id
			LEFT JOIN isys_cats_room_list AS j6 ON j6.isys_cats_room_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_room_type AS jn3 ON jn3.isys_room_type__id = j6.isys_cats_room_list__isys_room_type__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__ROOM) . ") ";
    } // function
} // class