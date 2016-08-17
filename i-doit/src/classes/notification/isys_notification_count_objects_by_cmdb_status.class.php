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
 * Notification: Count objects by their CMDB status
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_notification_count_objects_by_cmdb_status extends isys_notification
{

    protected $m_cmdb_status;

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

        // Get objects which match destinated CMDB status:

        $l_objects = [];

        foreach ($l_notification_objects as $l_object)
        {
            if ($l_object['isys_obj__isys_cmdb_status__id'] == $this->m_cmdb_status)
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
                    'Amount of objects which match the criterias: %s',
                    $l_num
                )
            );
        } //if

        // Check whether threshold is exceeded:

        if ($p_notification['threshold'] >= 0 && $l_num <= $p_notification['threshold'])
        {
            $this->m_log->debug(
                sprintf(
                    'Threshold not exceeded (%s). Skip notification.',
                    $p_notification['threshold']
                )
            );

            return $this->reset_counter($p_notification);
        } //if

        if ($p_notification['threshold'] <= 0 && $l_num >= ($p_notification['threshold'] * -1))
        {
            $this->m_log->debug(
                sprintf(
                    'Threshold not exceeded (%s). Skip notification.',
                    $p_notification['threshold']
                )
            );

            return $this->reset_counter($p_notification);
        } //if

        $this->m_log->debug(
            sprintf(
                'Threshold exceeded (%s)!',
                $p_notification['threshold']
            )
        );

        // Write messages:

        if ($this->write_messages($p_notification, $l_objects) > 0)
        {
            return $this->increase_counter($p_notification);
        } //if

        // Do not increase or reset counter...
    } //function

} //class

?>