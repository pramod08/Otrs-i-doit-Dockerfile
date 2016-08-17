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
 * Factory for CMDB DAOs
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_factory_cmdb_dao extends isys_factory
{
    /**
     * Gets an instance of a CMDB DAO.
     *
     * @param   string                  $p_class
     * @param   isys_component_database $p_db
     *
     * @return  isys_cmdb_dao
     */
    public static function get_instance($p_class, isys_component_database $p_db)
    {
        if (!isset(self::$m_instances[$p_class]))
        {
            self::$m_instances[$p_class] = new $p_class($p_db);
        } //if

        return self::$m_instances[$p_class];
    } // function
} // class