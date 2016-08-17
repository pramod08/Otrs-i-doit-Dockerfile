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
 * Notification: Informs about objects which have been changed since the last
 * run.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_notification_changed_objects extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     */
    protected function handle_notification($p_notification)
    {
        // Fetch objects selected by notification:
        $l_notification_objects = $this->m_dao->get_objects($p_notification['id']);

        // Check whether last run is set (affects new notifications):
        if (!isset($p_notification['last_run']))
        {
            $this->m_log->debug(
                'Date of last run has not been set yet. Skip notification.'
            );

            return $this->reset_counter($p_notification);
        } //if

        // Get objects which have been updated since the last run:

        $l_last_run = strtotime($p_notification['last_run']);

        $l_objects = [];

        foreach ($l_notification_objects as $l_object)
        {
            $l_updated = strtotime($l_object['isys_obj__updated']);

            if ($l_last_run <= $l_updated)
            {
                $l_objects[] = $l_object;
            } //if
        } //foreach

        unset ($l_notification_objects);

        $l_num = count($l_objects);

        if ($l_num === 0)
        {
            $this->m_log->debug(
                'There are no objects left to report. Skip notification.'
            );

            return $this->reset_counter($p_notification);
        }
        else
        {
            $this->m_log->debug(
                sprintf(
                    'Amount of objects which have been updated since the last run: %s',
                    $l_num
                )
            );
        } //if

        // Write messages:

        if ($this->write_messages($p_notification, $l_objects) > 0)
        {
            return $this->increase_counter($p_notification);
        } //if

        // Do not increase or reset counter...
    } //function

} //class

?>