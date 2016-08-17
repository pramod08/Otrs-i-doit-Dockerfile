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

if (include_once('isys_module_tts_autoload.class.php'))
{
    spl_autoload_register('isys_module_tts_autoload::init');
}

isys_component_signalcollection::get_instance()
    ->connect(
        'mod.cmdb.processMenuTreeLinks',
        [
            'isys_module_tts',
            'process_menu_tree_links'
        ]
    );