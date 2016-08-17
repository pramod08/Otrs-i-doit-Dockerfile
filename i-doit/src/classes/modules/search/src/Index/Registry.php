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
namespace idoit\Module\Search\Index;

use idoit\Module\Search\Index\Protocol\ObservableIndexManager;

/**
 * i-doit
 *
 * Search index registry
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Registry
{
    /**
     * @var callable[]
     */
    private static $indexManagers = [];

    /**
     * @var ObservableIndexManager[]
     */
    private static $instances;

    /**
     * $observableIndexManager should be a function returning the ObservableIndexManager, $name should be a unique title to identify where the indexed documents are coming from.
     *
     * This functions retrieves the following variables:
     *
     * - $observers (Array of all registered observers)
     * - $categoryBlacklist (Array of blacklisted cmdb categories)
     * - $objectTypeBlacklist (Array of blacklisted objectTypes)
     *
     * @param string   $name
     * @param callable $observableIndexManager
     */
    public static function register($name, callable $observableIndexManager)
    {
        self::$indexManagers[$name] = $observableIndexManager;
    }

    /**
     * Deregisters an observer
     *
     * @param string $name
     */
    public static function unregister($name)
    {
        unset(self::$indexManagers[$name]);
    }

    /**
     * Return list of IndexManagers
     *
     * @param array $observers
     * @param array $categoryBlacklist
     * @param array $objectTypeBlacklist
     *
     * @return Protocol\ObservableIndexManager[]
     */
    public static function get($observers = [], $categoryBlacklist = [], $objectTypeBlacklist = [])
    {
        if (!self::$instances)
        {
            foreach (self::$indexManagers as $name => $manager)
            {
                self::$instances[$name] = $manager($observers, $categoryBlacklist, $objectTypeBlacklist);
                self::$instances[$name]->setName($name);
            }

        }

        return self::$instances;
    }
}