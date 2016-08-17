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
 * List DAO: Parallel relation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_parallel_relation extends isys_cmdb_dao_list_objects
{
    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"threshold","isys_cats_relpool_list__threshold","LC__CATS__AC_THRESHOLD","isys_cmdb_dao_category_s_parallel_relation::get_properties",false]]';
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
			j2.isys_cats_relpool_list__title,
			j2.isys_cats_relpool_list__threshold

			FROM isys_obj AS obj_main
			LEFT JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			LEFT JOIN isys_cats_relpool_list AS j2 ON j2.isys_cats_relpool_list__isys_obj__id = obj_main.isys_obj__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id(C__OBJTYPE__PARALLEL_RELATION) . ") ";
    } // function
} // class