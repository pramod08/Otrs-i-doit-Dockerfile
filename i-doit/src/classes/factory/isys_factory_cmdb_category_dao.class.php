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
 * Factory for CMDB category DAOs
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @version     Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_factory_cmdb_category_dao extends isys_factory
{
    /**
     * Contains information about all categories received from database.
     *
     * @var  array  Associative, multidimensional array with category types as keys and categories as values.
     */
    protected static $m_categories = [];

    /**
     * Gets an instance of a category DAO.
     *
     * @param   string                  $p_class
     * @param   isys_component_database $p_db
     *
     * @return  isys_cmdb_dao_category
     */
    public static function get_instance($p_class, isys_component_database $p_db)
    {
        if (!$p_class)
        {
            throw new isys_exception_general('Instance class is not set in ' . __FILE__ . ':' . __LINE__);
        }

        if (!isset(self::$m_instances[$p_class]))
        {
            self::$m_instances[$p_class] = new $p_class($p_db);
        } //if

        return self::$m_instances[$p_class];
    } // function

    /**
     * Gets an instance of a category DAO by the category identifier.
     *
     * @param   integer                 $p_type Category type identifier
     * @param   integer                 $p_id   Category identifier
     * @param   isys_component_database $p_db   Database component
     *
     * @return  isys_cmdb_dao_category
     */
    public static function get_instance_by_id($p_type, $p_id, isys_component_database $p_db)
    {
        if (count(self::$m_categories) == 0)
        {
            self::build_category_list($p_db);
        } // if

        return self::get_instance(self::$m_categories[$p_type][$p_id]['class_name'], $p_db);
    } // function

    /**
     * Builds the category list.
     */
    protected static function build_category_list(isys_component_database &$p_db)
    {
        $l_cmdb_dao = new isys_cmdb_dao($p_db);

        self::$m_categories = $l_cmdb_dao->get_all_categories();
    } // function
} // class