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

if (include_once('isys_module_jdisc_autoload.class.php'))
{
    spl_autoload_register('isys_module_jdisc_autoload::init');
}

/* Register jdisc controller */
$GLOBALS['g_controller']['handler']['jdisc'] = [
    'class' => 'isys_handler_jdisc'
];
/* Register jdisc controller */
$GLOBALS['g_controller']['handler']['jdisc_discovery'] = [
    'class' => 'isys_handler_jdisc_discovery'
];

// Handle module specific language files.
global $g_idoit_language_short;

if (file_exists(__DIR__ . DS . 'lang' . DS . $g_idoit_language_short . '.inc.php'))
{
    $l_language = include_once 'lang' . DS . $g_idoit_language_short . '.inc.php';

    if (is_array($l_language))
    {
        global $g_comp_template_language_manager;
        $g_comp_template_language_manager->append_lang($l_language);
    } // if
} // if