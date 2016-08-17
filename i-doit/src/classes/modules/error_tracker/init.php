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
 * @subpackage  error_tracker
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */

if (isys_module_manager::instance()
    ->is_active('error_tracker')
)
{
    // Handle module specific language files.
    global $g_comp_session;

    if (include_once('isys_module_error_tracker_autoload.class.php'))
    {
        spl_autoload_register('isys_module_error_tracker_autoload::init');
    } // if

    isys_settings::extend(
        [
            'Error Tracking' => [
                'error-tracker.enabled'     => [
                    'title'       => 'Activate Error Reporting to Synetics',
                    'type'        => 'select',
                    'options'     => [
                        '1' => 'LC__UNIVERSAL__YES',
                        '0' => 'LC__UNIVERSAL__NO'
                    ],
                    'description' => 'Help improving i-doit and automatically report Bugs to our online bug tracker. Your report is submitted securely via HTTPS. Please keep in mind that your i-doit host has to be internet enabled in order to automatically report errors.',
                    'default'     => '1',
                ],
                'error-tracker.type'        => [
                    'title'       => 'Error Tracker',
                    'type'        => 'select',
                    'options'     => [
                        'rollbar' => 'Rollbar (www.rollbar.com)',
                    ],
                    'description' => 'The error tracking instance to use. Feel free to inform yourself about the third party service.',
                    'default'     => 'rollbar'
                ],
                'error-tracker.environment' => [
                    'title'       => 'Environment',
                    'type'        => 'select',
                    'description' => 'The type or stage of your environment.',
                    'options'     => [
                        'Development' => 'Development',
                        'Pre-Test'    => 'Pre-Test',
                        'Test'        => 'Test',
                        'Production'  => 'Production',
                    ],
                    'default'     => 'Production'
                ],
                'error-tracker.anonymize'   => [
                    'title'       => 'Anonymize?',
                    'type'        => 'select',
                    'options'     => [
                        '1' => 'LC__UNIVERSAL__YES',
                        '0' => 'LC__UNIVERSAL__NO'
                    ],
                    'description' => 'This option anonymizes all the reported data. The report contains the following information: error message, stack trace, i-doit, os, browser versions, and the request parameters to reproduce the error. (Setting this option to no also reports the current username, e-mail address, hostname and ip address)',
                    'default'     => '1',
                ],
            ]
        ]
    );

    /**
     * Initialize current error tracker
     */
    if (isys_settings::get('error-tracker.enabled', true))
    {
        isys_module_error_tracker::tracker();
    }

    isys_component_signalcollection::get_instance()
        ->connect(
            'exceptionTriggered',
            function ($exc)
            {
                if (isys_settings::get('error-tracker.enabled', true))
                {
                    isys_module_error_tracker::tracker()
                        ->exception($exc);
                }
            }
        );

} // if