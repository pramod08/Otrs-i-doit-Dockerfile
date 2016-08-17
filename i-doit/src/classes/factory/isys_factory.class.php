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
 * Class factory
 *
 * @package     i-doit
 * @subpackage  Factory
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_factory
{
    /**
     * Contains self representations of factorized classes.
     *
     * @var  array  Associative array of instances
     */
    protected static $m_instances = [];

    /**
     * Gets an instance of a class.
     *
     * @param   string $p_class
     * @param   mixed  $p_params
     *
     * @return  Object
     */
    public static function get_instance($p_class, $p_params = null)
    {
        if (isset(self::$m_instances[$p_class]))
        {
            return self::$m_instances[$p_class];
        }
        else
        {
            if (method_exists($p_class, 'get_instance'))
            {
                return (self::$m_instances[$p_class] = call_user_func_array(
                    [
                        $p_class,
                        'get_instance'
                    ],
                    $p_params
                ));
            }
            else
            {
                // @todo Find a way to call the constructor and pass variable params - "call_user_func_array(array($p_class, '__construct') ..." does not work.
                return (self::$m_instances[$p_class] = new $p_class($p_params));
            } // if
        } // if
    } // function
} // class