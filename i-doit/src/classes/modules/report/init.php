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

if (include_once('isys_module_report_autoload.class.php'))
{
    spl_autoload_register('isys_module_report_autoload::init');
} // if

/* Register jdisc controller */
$GLOBALS['g_controller']['handler']['report'] = [
    'class' => 'isys_handler_report'
];

// Defining some constants.
define("C__REPORT__STANDARD", 0);
define("C__REPORT__CUSTOM", 1);

define("C__GET__REPORT_PAGE", "rpID");
define("C__GET__REPORT_REPORT_ID", "reportID");

define("C__REPORT_PAGE__REPORT_BROWSER", 1);
define("C__REPORT_PAGE__STANDARD_REPORTS", 2);
define("C__REPORT_PAGE__CUSTOM_REPORTS", 3);
define("C__REPORT_PAGE__QUERY_BUILDER", 4);
define("C__REPORT_PAGE__VIEWS", 5);

// Add a few widgets to the dashboard.
isys_register::factory('widget-register')
    ->set('reports', 'isys_dashboard_widgets_reports');

\idoit\Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\Report', __DIR__ . '/src/');