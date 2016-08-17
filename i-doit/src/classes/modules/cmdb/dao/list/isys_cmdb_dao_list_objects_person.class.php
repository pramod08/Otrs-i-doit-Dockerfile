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
 * List DAO: Person.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_person extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"first_name","isys_cats_person_list__first_name","LC__CONTACT__PERSON_FIRST_NAME","isys_cmdb_dao_category_s_person_master::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"last_name","isys_cats_person_list__last_name","LC__CONTACT__PERSON_LAST_NAME","isys_cmdb_dao_category_s_person_master::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"department","isys_cats_person_list__department","LC__CONTACT__PERSON_DEPARTMENT","isys_cmdb_dao_category_s_person_master::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__STATIC . ',"phone_company","isys_cats_person_list__phone_company","LC__CONTACT__PERSON_TELEPHONE_COMPANY","isys_cmdb_dao_category_s_person_master::get_properties_ng",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_mail",false,"LC__CONTACT__PERSON_MAIL_ADDRESS","isys_cmdb_dao_category_s_person_master::get_dynamic_properties",["isys_cmdb_dao_category_s_person_master","dynamic_property_callback_mail_address"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_organisation",false,"LC__CONTACT__PERSON_ASSIGNED_ORGANISATION","isys_cmdb_dao_category_s_person_master::get_dynamic_properties",["isys_cmdb_dao_category_s_person_master","dynamic_property_callback_organisation"]]]';
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
			j6.isys_cats_person_list__first_name,
			j6.isys_cats_person_list__last_name,
			j6.isys_cats_person_list__department,
			j6.isys_cats_person_list__phone_company,
			j6.isys_cats_person_list__isys_connection__id,
			mail_person.isys_catg_mail_addresses_list__title

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_cats_person_list AS j6 ON j6.isys_cats_person_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list AS mail_person ON obj_main.isys_obj__id = mail_person.isys_catg_mail_addresses_list__isys_obj__id AND mail_person.isys_catg_mail_addresses_list__primary = 1
			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__PERSON) . ") ";
    } // function
} // class