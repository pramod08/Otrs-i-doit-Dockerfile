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
class isys_notification_stored_objects extends isys_notification_count_objects_by_cmdb_status
{

    /**
     * Initiates notification.
     *
     * @param array $p_type Information about this notification type
     */
    public function init($p_type)
    {
        $this->m_cmdb_status = C__CMDB_STATUS__STORED;
        parent::init($p_type);
    } //function

} //class

?>