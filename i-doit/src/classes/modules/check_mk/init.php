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
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */

define('C__CATG_CHECK_MK__NAME_SELECTION__INPUT', 0);
define('C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME_FQDN', 1);
define('C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME', 2);
define('C__CATG_CHECK_MK__NAME_SELECTION__OBJ_ID', 3);

define('C__MODULE__CMK__DYNAMIC_TAG__OBJECT_TYPE', 1);
define('C__MODULE__CMK__DYNAMIC_TAG__LOCATION', 2);
define('C__MODULE__CMK__DYNAMIC_TAG__PURPOSE', 3);

define('C__MODULE__CHECK_MK__LIVESTATUS_TYPE__TCP', 'tcp');
define('C__MODULE__CHECK_MK__LIVESTATUS_TYPE__UNIX', 'unix');

define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__OK', 0);
define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__WARNING', 1);
define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__CRITICAL', 2);
define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNKNOWN', 3);

define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__UP', 0);
define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__DOWN', 1);
define('C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNREACHABLE', 2);

if (isys_module_manager::instance()
    ->is_active('check_mk')
)
{
    if (include_once('isys_module_check_mk_autoload.class.php'))
    {
        spl_autoload_register('isys_module_check_mk_autoload::init');
    } // if

    if (class_exists('\idoit\Psr4AutoloaderClass'))
    {
        \idoit\Psr4AutoloaderClass::factory()
            ->addNamespace('idoit\Module\Check_mk', __DIR__ . '/src/');
    } // if

    // Handle module specific language files.
    global $g_comp_session;

    if (file_exists(__DIR__ . DS . 'lang' . DS . $g_comp_session->get_language() . '.inc.php'))
    {
        $l_language = include_once(__DIR__ . DS . 'lang' . DS . $g_comp_session->get_language() . '.inc.php');

        if (is_array($l_language))
        {
            global $g_comp_template_language_manager;
            $g_comp_template_language_manager->append_lang($l_language);
        } // if
    } // if

    // Making the Check_MK controller class available
    $GLOBALS['g_controller']['handler']['check_mk']        = ['class' => 'isys_handler_check_mk'];
    $GLOBALS['g_controller']['handler']['check_mk_export'] = ['class' => 'isys_handler_check_mk_export'];
} // if
