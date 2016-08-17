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
 * List DAO: Person group.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_person_group extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"email_address","isys_cats_person_group_list__email_address","LC__CONTACT__GROUP_EMAIL_ADDRESS","isys_cmdb_dao_category_s_person_group_master::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"phone","isys_cats_person_group_list__phone","LC__CONTACT__GROUP_PHONE","isys_cmdb_dao_category_s_person_group_master::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"ldap_group","isys_cats_person_group_list__ldap_group","LC__CONTACT__GROUP_LDAP_GROUP","isys_cmdb_dao_category_s_person_group_master::get_properties_ng",false]]';
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
			isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address,
			j3.isys_cats_person_group_list__phone,
			j3.isys_cats_person_group_list__ldap_group

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_cats_person_group_list AS j3 ON j3.isys_cats_person_group_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON obj_main.isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__PERSON_GROUP) . ") ";
    } // function
} // class