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
 * Notification: Check for i-doit updates.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_notification_update extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     */
    protected function handle_notification($p_notification)
    {
        $l_update = new isys_update();

        $l_xml               = $l_update->fetch_file((defined('C__IDOIT_UPDATES_PRO') ? C__IDOIT_UPDATES_PRO : C__IDOIT_UPDATES));
        $l_available_updates = $l_update->get_new_versions($l_xml);
        $l_info              = $l_update->get_isys_info();

        $l_updates = [];

        foreach ($l_available_updates as $l_available_update)
        {
            if ($l_available_update['revision'] > $l_info['revision'])
            {
                $l_updates[] = $l_available_update;
                $this->m_log->notice(
                    sprintf(
                        'i-doit update %s found',
                        $l_available_update['version']
                    )
                );
            } //if
        } //foreach

        $l_num = count($l_updates);

        if ($l_num == 0)
        {
            $this->m_log->debug(
                'There are no updates available. Skip notification.'
            );

            return $this->reset_counter($p_notification);
        }
        else
        {
            $this->m_log->debug(
                sprintf(
                    'Amount of updates: %s',
                    $l_num
                )
            );
        } //if

        // Write messages:
        if ($this->write_messages($p_notification) > 0)
        {
            return $this->increase_counter($p_notification);
        } //if

        // Do not increase or reset counter...
        return null;
    } //function

} //class