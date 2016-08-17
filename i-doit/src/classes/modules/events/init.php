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
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */

if (isys_module_manager::instance()
    ->is_active('events')
)
{
    if (class_exists('\idoit\Psr4AutoloaderClass'))
    {
        \idoit\Psr4AutoloaderClass::factory()
            ->addNamespace('idoit\Module\Events', __DIR__ . '/src/');

        // Handle module specific language files.
        global $g_comp_session;

        if (include_once('isys_module_events_autoload.class.php'))
        {
            spl_autoload_register('isys_module_events_autoload::init');
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

        /* Synchronize all module hooks to database table isys_event */
        isys_component_signalcollection::get_instance()
            ->connect(
                'system.afterChange',
                function ()
                {
                    if (isys_application::instance()->database)
                    {
                        \idoit\Module\Events\Model\Events::instance(
                            isys_application::instance()->database
                        )
                            ->synchronize();
                    }
                }
            );

        if (isys_application::instance()->session->is_logged_in() && isys_application::instance()->database)
        {
            $signalSlots   = isys_component_signalcollection::get_instance();
            $subscriptions = \idoit\Module\Events\Model\Dao::instance(isys_application::instance()->database)
                ->getEventSubscriptions();

            while ($row = $subscriptions->get_row())
            {
                if (!isset($alreadyConnected[$row['identifier']]) && $row['queued'] == 0)
                {
                    // instant handler
                    $signalSlots->connect($row['identifier'], $row['handler']);

                    $alreadyConnected[$row['identifier']] = true;
                }
                else
                {
                    // queuing handler
                    $signalSlots->connect($row['identifier'], 'isys_module_events::queue');
                }
            }
        }

        isys_tenantsettings::extend(
            [
                'Events' => [
                    'events.decodeArgs' => [
                        'title'       => 'Base64 Decode Event Parameters',
                        'type'        => 'select',
                        'options'     => [
                            '1' => 'LC__UNIVERSAL__YES',
                            '0' => 'LC__UNIVERSAL__NO'
                        ],
                        'description' => 'Decodes json parameters passed to script in base64.',
                        'default'     => '1'
                    ]
                ]
            ]
        );
    }

} // if
