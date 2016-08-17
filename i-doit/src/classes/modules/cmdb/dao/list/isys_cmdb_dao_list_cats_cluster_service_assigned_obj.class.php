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
 * DAO: list for cluster members
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_cluster_service_assigned_obj extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__CLUSTER_SERVICE_ASSIGNED_OBJ;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_cluster_service::instance($this->m_db)
            ->get_assigned_objects_and_relations(null, $p_objID, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    } // function

    /**
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $l_quick_info = isys_factory::get_instance('isys_ajax_handler_quick_info');

        $p_row["main_obj_title"] = $l_quick_info->get_quick_info($p_row["main_obj_id"], $p_row["main_obj_title"], C__LINK__OBJECT, 80);
        $p_row["rel_obj_title"]  = $l_quick_info->get_quick_info($p_row["rel_obj_id"], $p_row["rel_obj_title"], C__LINK__OBJECT, 80);
    } // function

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "rel_obj_title"  => "LC__CATS__APPLICATION_ASSIGNMENT__INSTALLATION_INSTANCE",
            "main_obj_title" => "LC__UNIVERSAL__INSTALLED_ON",
        ];
    } // function

    /**
     *
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    } // function
} // class