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
 * List DAO: SIM card.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_sim_card extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"type","isys_cp_contract_type__title","LC__CMDB__CATG__TYPE","isys_cmdb_dao_category_g_sim_card::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_assigned_mobile",false,"LC__CMDB__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE","isys_cmdb_dao_category_g_sim_card::get_dynamic_properties",["isys_cmdb_dao_category_g_sim_card","dynamic_property_callback_assigned_mobile"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"network_provider","isys_network_provider__title","LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER","isys_cmdb_dao_category_g_sim_card::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_created",false,"LC__TASK__DETAIL__WORKORDER__CREATION_DATE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_created"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_changed",false,"LC__CMDB__LAST_CHANGE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_changed"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"purpose","isys_purpose__title","LC__CMDB__CATG__GLOBAL_PURPOSE","isys_cmdb_dao_category_g_global::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_cmdb_status",false,"LC__UNIVERSAL__CMDB_STATUS","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_cmdb_status"]]]';
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
			obj_main.isys_obj__description,
			jn1.isys_cp_contract_type__title,
			jn3.isys_network_provider__title,
			jn5.isys_purpose__title,
			obj_main.isys_obj__title

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_catg_global_list AS j2 ON j2.isys_catg_global_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_purpose AS jn5 ON jn5.isys_purpose__id = j2.isys_catg_global_list__isys_purpose__id
			LEFT JOIN isys_catg_sim_card_list AS j4 ON j4.isys_catg_sim_card_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_cp_contract_type AS jn1 ON jn1.isys_cp_contract_type__id = j4.isys_catg_sim_card_list__isys_cp_contract_type__id
			LEFT JOIN isys_network_provider AS jn3 ON jn3.isys_network_provider__id = j4.isys_catg_sim_card_list__isys_network_provider__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__SIM_CARD) . ") ";
    } // function
} // class