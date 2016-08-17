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
 * Event Module initializer
 *
 * @package     modules
 * @subpackage  events
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.6
 */

if (isys_module_manager::instance()
    ->is_active('wiring')
)
{
    // Populate new namespace directory
    \idoit\Psr4AutoloaderClass::factory()
        ->addNamespace('idoit\Module\Wiring', __DIR__ . '/src/');

    // Including wiring auth class
    include_once('auth/isys_auth_wiring.class.php');

    // Including autoloader
    include_once('isys_module_wiring_autoload.class.php');

    // Handle module specific language files.
    global $g_comp_template_language_manager;
    $g_comp_template_language_manager->append_lang_file(__DIR__ . DS . 'lang' . DS . isys_application::instance()->language . '.inc.php');

} // if
