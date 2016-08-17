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
 * DAO: global category for virtual cabling
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_virtual_cabling extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_cabling';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__CABLING';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CATG__CABLING;

    /**
     * Create element method.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_new_id
     *
     * @return  null
     */
    public function attachObjects(array $p_post)
    {
        return null;
    }

    /**
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        return isys_cmdb_dao_category_g_connector::instance($this->get_database_component())
            ->get_count();
    } // function

    /**
     * Get data method.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     * @param   string  $p_sort_by
     * @param   string  $p_direction
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null, $p_sort_by = null, $p_direction = null)
    {
        return isys_cmdb_dao_category_g_connector::instance($this->m_db)
            ->get_data($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status, $p_sort_by, $p_direction);
    } // function

    /**
     * Get UI method, because the UI class name breaks the standards.
     *
     * @global  isys_component_template $g_comp_template
     * @return  isys_cmdb_ui_category_g_virtual_cabling
     */
    public function &get_ui()
    {
        global $g_comp_template;

        return new isys_cmdb_ui_category_g_virtual_cabling($g_comp_template);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
        ];
    } // function

    /**
     *
     * @param   array  $p_objects
     * @param   string $p_direction
     * @param   string $p_table
     *
     * @return  boolean
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        return true;
    } // function

    /**
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    } // function

} // class

?>