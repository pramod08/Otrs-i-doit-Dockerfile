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
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_application_assigned_obj extends isys_cmdb_dao_list
{
    /**
     * @var  isys_cmdb_dao_category_g_relation
     */
    protected $m_dao_relation;

    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__APPLICATION_ASSIGNED_OBJ;
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
        return isys_cmdb_dao_category_g_application::instance($this->m_db)
            ->get_assigned_objects_and_relations(
                null,
                $p_objID,
                empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus,
                " AND main.isys_obj__status = " . C__RECORD_STATUS__NORMAL
            );
    } // function

    /**
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $l_quick_info = new isys_ajax_handler_quick_info;

        $l_relation_type = $this->m_dao_relation->get_relation_type($p_row['isys_catg_relation_list__isys_relation_type__id'])
            ->get_row();

        $p_row["main_obj_title"] = $l_quick_info->get_quick_info($p_row["main_obj_id"], $p_row["main_obj_title"], C__LINK__OBJECT);
        $p_row["rel_obj_title"]  = $l_quick_info->get_quick_info(
            $p_row["rel_obj_id"],
            $p_row['slave_title'] . ' ' . _L($l_relation_type['isys_relation_type__slave']) . ' ' . $p_row['master_title'],
            C__LINK__OBJECT
        );

        if ($p_row['isys_cats_app_variant_list__title'] != '' && $p_row['isys_cats_app_variant_list__variant'] != '')
        {
            $p_row['isys_cats_app_variant_list__variant'] .= ' (' . $p_row['isys_cats_app_variant_list__title'] . ')';
        } // if

        // Find the assigned license.
        if ($p_row['isys_catg_application_list__isys_cats_lic_list__id'] > 0)
        {
            $l_row = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_s_lic', $this->m_db)
                ->get_data($p_row['isys_catg_application_list__isys_cats_lic_list__id'])
                ->get_row();

            $p_row["assigned_license"] = $l_quick_info->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT);
        } // if
    } // function

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'rel_obj_title'                       => 'LC__CATS__APPLICATION_ASSIGNMENT__INSTALLATION_INSTANCE',
            'main_obj_title'                      => 'LC__UNIVERSAL__INSTALLED_ON',
            'isys_cats_app_variant_list__variant' => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
            'assigned_license'                    => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE'
        ];
    } // function

    /**
     * Construct the DAO object.
     *
     * @param  isys_cmdb_dao_category $p_cat
     */
    public function __construct(isys_cmdb_dao_category $p_cat)
    {
        $this->m_dao_relation = new isys_cmdb_dao_category_g_relation($p_cat->get_database_component());

        parent::__construct($p_cat);
    } // function
} // class