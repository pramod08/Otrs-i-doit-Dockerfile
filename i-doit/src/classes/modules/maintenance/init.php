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
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.3
 */

if (isys_module_manager::instance()
    ->is_active('maintenance')
)
{
    // Handle module specific language files.
    global $g_comp_session;

    // Define some constants.
    define('C__MAINTENANCE__PLANNING', 'planning');
    define('C__MAINTENANCE__PLANNING_ARCHIVE', 'planning_archive');
    define('C__MAINTENANCE__MAILTEMPLATE', 'mailtemplate');
    define('C__MAINTENANCE__OVERVIEW', 'overview');

    define('C__MAINTENANCE__RECIPIENTS__RESOLVE_CONTACT_GROUPS', 1);
    define('C__MAINTENANCE__RECIPIENTS__ONLY_SELECTED_CONTACTS', 2);

    if (include_once('isys_module_maintenance_autoload.class.php'))
    {
        spl_autoload_register('isys_module_maintenance_autoload::init');
    } // if

    if (file_exists(__DIR__ . DS . 'lang' . DS . $g_comp_session->get_language() . '.inc.php'))
    {
        $l_language = include_once __DIR__ . DS . 'lang' . DS . $g_comp_session->get_language() . '.inc.php';

        if (is_array($l_language))
        {
            global $g_comp_template_language_manager;

            $g_comp_template_language_manager->append_lang($l_language);
        } // if
    } // if

    // Append the "maintenance" notification in the object header.
    isys_component_signalcollection::get_instance()
        ->connect(
            'mod.cmdb.processContentTop',
            [
                'isys_module_maintenance',
                'process_content_top'
            ]
        );

    // Add the report view.
    isys_register::factory('additional-report-views')
        ->set(__DIR__ . DS . 'reportview' . DS . 'isys_maintenance_reportview_maintenance_export.class.php');

    // Append some tenant settings for the e-mail notification.
    isys_tenantsettings::extend(
        [
            'LC__MODULE__MAINTENANCE' => [
                'maintenance.email.recipients' => [
                    'title'   => 'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS',
                    'type'    => 'select',
                    'options' => [
                        C__MAINTENANCE__RECIPIENTS__RESOLVE_CONTACT_GROUPS => 'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS__RESOLVE_CONTACT_GROUPS',
                        C__MAINTENANCE__RECIPIENTS__ONLY_SELECTED_CONTACTS => 'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS__ONLY_SELECTED_CONTACTS',
                    ],
                    'default' => C__MAINTENANCE__RECIPIENTS__RESOLVE_CONTACT_GROUPS
                ]
            ]
        ]
    );

    // Add the e-mail notification controller.
    $GLOBALS['g_controller']['handler']['maintenance_notification'] = [
        'class' => 'isys_handler_maintenance_notification'
    ];
} // if