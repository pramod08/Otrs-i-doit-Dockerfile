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

define('C__CATS__VIRTUAL', false);

/**
 * i-doit
 * DAO: specific category for view only categories.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_virtual extends isys_cmdb_dao_category_specific
{
    /**
     * Category name.
     *
     * @var  string
     */
    protected $m_category = 'virtual';

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
     * @param  array  $p_data
     * @param  mixed  $p_prepend_table_field
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