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
namespace idoit\Component\Provider;

/**
 * i-doit Factory Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
trait Singleton
{
    /**
     * @var Singleton
     */
    private static $instance;

    /**
     * Return instance of current class
     *
     * @return static
     */
    final public static function instance()
    {
        if (self::$instance === null)
        {
            $args = func_get_args();
            if (count($args) > 0)
            {
                $class          = get_called_class();
                self::$instance = (new \ReflectionClass($class))->newInstanceArgs($args);
            }
            else
            {
                self::$instance = new static;
            }
        }

        return self::$instance;
    }
}