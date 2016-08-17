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
 * List DAO: Supernet.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_supernet extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"],"LC__CMDB__CATG__GLOBAL"],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_supernet_link",false,"LC__CMDB__CATG__SUPERNET__OPEN_SUPERNET","isys_cmdb_dao_category_g_virtual_supernet::get_dynamic_properties",["isys_cmdb_dao_category_g_virtual_supernet","dynamic_property_callback_supernet_link"],"LC__CMDB__CATG__SUPERNET"],' . '[' . C__PROPERTY_TYPE__STATIC . ',"type","isys_net_type__title","LC__CMDB__CATS__NET__TYPE","isys_cmdb_dao_category_s_net::get_properties_ng",false,"LC__CMDB__CATG__LOGBOOK"],' . '[2,"_address_with_suffix",false,"LC__CMDB__CATS__NET__ADDRESS_WITH_SUFFIX","isys_cmdb_dao_category_s_net::get_dynamic_properties",["isys_cmdb_dao_category_s_net","dynamic_property_callback_address_with_suffix"],"LC__CMDB__CATG__LOGBOOK"],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_address_with_suffix",false,"LC__CMDB__CATS__NET__ADDRESS_WITH_SUFFIX","isys_cmdb_dao_category_s_net::get_dynamic_properties",["isys_cmdb_dao_category_s_net","dynamic_property_callback_address_with_suffix"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_address_range",false,"LC__CMDB__CATS__NET__ADDRESS_RANGE","isys_cmdb_dao_category_s_net::get_dynamic_properties",["isys_cmdb_dao_category_s_net","dynamic_property_callback_address_range"],"LC__CMDB__CATG__LOGBOOK"],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_netmask",false,"LC__CATP__IP__SUBNETMASK","isys_cmdb_dao_category_s_net::get_dynamic_properties",["isys_cmdb_dao_category_s_net","dynamic_property_callback_netmask"],"LC__CMDB__CATG__LOGBOOK"],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_free_addresses",false,"LC__CMDB__CATG__NETWORK__ASS_IP","isys_cmdb_dao_category_s_net::get_dynamic_properties",["isys_cmdb_dao_category_s_net","dynamic_property_callback_free_addresses"],"LC__CMDB__CATG__LOGBOOK"],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_cmdb_status",false,"LC__UNIVERSAL__CMDB_STATUS","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_cmdb_status"],"LC__CMDB__CATG__GLOBAL"]]';
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
			obj_main.isys_obj__title,
			jn2.isys_net_type__title

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_cats_net_list AS j3 ON j3.isys_cats_net_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_net_type AS jn2 ON jn2.isys_net_type__id = j3.isys_cats_net_list__isys_net_type__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__SUPERNET) . ") ";
    } // function
} // class