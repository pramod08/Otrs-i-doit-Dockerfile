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
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
if (include_once('isys_module_cmdb_autoload.class.php'))
{
    spl_autoload_register('isys_module_cmdb_autoload::init');
} // if

if (class_exists('\idoit\Psr4AutoloaderClass'))
{
    \idoit\Psr4AutoloaderClass::factory()
        ->addNamespace('idoit\Module\Cmdb', __DIR__ . '/src/');
}

// Connect search signals
idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
    ->connect();


/**
 * Register cmdb index extension. Used for creating a cmdb index.
 */
\idoit\Module\Search\Index\Registry::register(
    'CMDB',
    function (array $observers, array $categoryBlacklist, array $objectTypeBlacklist)
    {
        // Create index manager for cis
        return new \idoit\Module\Cmdb\Search\IndexExtension\Manager\CmdbIndexManager(
            new \idoit\Module\Cmdb\Search\IndexExtension\Config(
                $objectTypeBlacklist, $categoryBlacklist, [], [
                    C__PROPERTY__PROVIDES__SEARCH
                ]
            )
        );
    }
);