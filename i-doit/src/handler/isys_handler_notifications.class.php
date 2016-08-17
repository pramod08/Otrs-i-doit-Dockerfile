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
 * Handler: Notifications
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_notifications extends isys_handler
{

    /**
     * @var null
     */
    public $m_log = null;

    /**
     * Initiates the handler.
     */
    public function init()
    {
        // Start logging:
        $this->m_log = isys_factory_log::get_instance('notifications');
        $this->m_log->set_verbose_level(isys_log::C__ALL & ~isys_log::C__DEBUG);
        $this->m_log->set_auto_flush(true);
        $this->m_log->info('Begin to notify...');

        // Get database component:
        global $g_comp_database;

        try
        {
            $this->m_log->debug('Iterating through each notification type...');

            $l_dao = new isys_notifications_dao(
                $g_comp_database, $this->m_log
            );

            // Fetch all notification types:
            $l_types = $l_dao->get_type();

            // Iterate through each notification type:
            foreach ($l_types as $l_type)
            {
                $this->m_log->info(
                    sprintf(
                        'Handling notification type "%s" [%s].',
                        _L($l_type['title']),
                        $l_type['id']
                    )
                );

                /**
                 * Use callback to notify:
                 *
                 * @var $l_callback isys_notification
                 */
                $l_callback = new $l_type['callback'](
                    $l_dao, $g_comp_database, $this->m_log
                );

                $l_callback->set_channels(isys_notification::C__CHANNEL__EMAIL);
                $l_callback->init($l_type);
                $l_callback->notify();

                unset($l_callback);
            } //foreach

            $l_dao->apply_update();

            $this->m_log->info('Everything done.');
        }
        catch (Exception $e)
        {
            $this->m_log->error($e->getMessage());
            $this->m_log->fatal('Aborted.');
        } //try/catch
    } //function
} //class