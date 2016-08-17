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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define('C__NAGIOS__PERSON_OPTION__OBJECT_TITLE', 0);
define('C__NAGIOS__PERSON_OPTION__USERNAME', 1);
define('C__NAGIOS__PERSON_OPTION__INPUT', 2);

define('C__CATG_NAGIOS_GROUP__TYPE_HOST', 0);
define('C__CATG_NAGIOS_GROUP__TYPE_SERVICE', 1);

define('C__CATG_NAGIOS__NAME_SELECTION__INPUT', 0);
define('C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN', 1);
define('C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID', 2);
define('C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME', 3);
define('C__CATG_NAGIOS__NAME_SELECTION__IP', 4);

if (include_once('isys_module_nagios_autoload.class.php'))
{
    spl_autoload_register('isys_module_nagios_autoload::init');
} // if

// Register the nagios handler.
$GLOBALS['g_controller']['handler']['nagios']        = ['class' => 'isys_handler_nagios'];
$GLOBALS['g_controller']['handler']['nagios_export'] = ['class' => 'isys_handler_nagios_export'];

isys_component_signalcollection::get_instance()
    ->connect(
        'mod.cmdb.processContentTop',
        [
            'isys_module_nagios',
            'process_content_top'
        ]
    );