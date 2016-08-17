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

define('C__CATG__VIRTUAL', false);

/**
 * i-doit
 * DAO: global category for guest systems.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_virtual extends isys_cmdb_dao_category_global
{
    /**
     * The category's name.
     *
     * @var  string
     */
    protected $m_category = 'virtual';

    /**
     * @var  string
     */
    protected $m_source_table = 'isys_catg_virtual';

    /**
     * Save method.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    } // function

    /**
     * Entry-Counter.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        // Let's return 1 here to display the Tree-Entry in bold.
        return 1;
    } // function

    /**
     * Fetches category data from database.
     *
     * @param   integer $p_category_data_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_data($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->retrieve('SELECT TRUE;');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    } // function

    /**
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
     * Validates property data.
     *
     * @param array $p_data
     * @param mixed $p_prepend_table_field
     *
     * @return boolean
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        return true;
    } // function

    /**
     * Validation method.
     *
     * @return  boolean
     */
    public function validate_user_data()
    {
        return true;
    } // function
} // class