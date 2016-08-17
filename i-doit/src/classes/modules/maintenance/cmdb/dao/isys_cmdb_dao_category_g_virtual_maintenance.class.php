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
 * Maintenance category DAO.
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */
class isys_cmdb_dao_category_g_virtual_maintenance extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_maintenance';

    /**
     * Method for retrieving all maintenances of a given object.
     *
     * @param   integer $p_object_id
     * @param   mixed   $p_order_start_date_asc
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_plannings_by_object($p_object_id, $p_order_start_date_asc = null)
    {
        $l_sql = 'SELECT * FROM isys_maintenance
			INNER JOIN isys_maintenance_2_object ON isys_maintenance_2_object__isys_maintenance__id = isys_maintenance__id
			WHERE isys_maintenance_2_object__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        if ($p_order_start_date_asc !== null)
        {
            $l_sql .= ' ORDER BY isys_maintenance__date_from ' . ($p_order_start_date_asc ? 'ASC' : 'DESC');
        } // if

        return $this->retrieve($l_sql . ';');
    } // function
} // class