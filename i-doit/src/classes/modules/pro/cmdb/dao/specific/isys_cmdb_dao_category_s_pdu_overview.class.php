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
 * DAO: specific category PDU overviews.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_pdu_overview extends isys_cmdb_dao_category_s_pdu
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'pdu_overview';

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
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [];
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [];
    } // function

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  null
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        return null;
    } // function

    /**
     * Validate post data method.
     *
     * @return null
     */
    public function validate_user_data()
    {
        return null;
    } // function
} // class
?>