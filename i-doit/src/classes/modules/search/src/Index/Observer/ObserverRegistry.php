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
namespace idoit\Module\Search\Index\Observer;

use idoit\Module\Cmdb\Model\Ci;
use idoit\Module\Search\Index\Protocol\Observer;

/**
 * i-doit
 *
 * Example/Demo Observer
 *
 * Retrieves all indexed documents and adds a debug log entry
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class ObserverRegistry
{
    /**
     * @var callable[]
     */
    private static $observers = [];

    /**
     * @var Observer[]
     */
    private static $instances;

    /**
     * $observer should be a function that returns an Observer instance.
     *
     * @param string   $name
     * @param callable $observer
     */
    public static function register($name, callable $observer)
    {
        self::$observers[$name] = $observer;
    }

    /**
     * Deregisters an observer
     *
     * @param string $name
     */
    public static function unregister($name)
    {
        unset(self::$observers[$name]);
    }

    /**
     * Return list of registered observers
     *
     * @return Observer
     */
    public static function get()
    {
        if (!self::$instances)
        {
            foreach (self::$observers as $name => $observerService)
            {
                /**
                 * @todo get only active observers via configuration
                 */
                self::$instances[$name] = $observerService();
            }

        }

        return self::$instances;
    }

    /**
     * ObserverRegistry constructor.
     */
    private function __construct() { }
}