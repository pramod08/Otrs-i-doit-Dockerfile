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
 * Notification: Generic report.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_notification_generic_report extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     *
     * @return bool|int
     */
    protected function handle_notification($p_notification)
    {
        global $g_comp_database_system;

        $this->m_log->set_log_level(isys_log::C__ALL);
        $this->m_log->set_verbose_level(isys_log::C__ALL);

        // Number of reports
        $l_count_objects = 0;

        // Array of report results
        $l_objects_of_report = [];

        // Get all available domains first
        $l_domains = $this->m_dao->get_domains($p_notification['id']);

        // Check for selected reports
        if (is_array($l_domains) && isset($l_domains['reports']) && count($l_domains['reports']))
        {
            $l_report_ids = $l_domains['reports'];
            $l_report_dao = new isys_report_dao($g_comp_database_system);
            $l_cmdb_dao   = new isys_cmdb_dao($this->m_dao->get_database_component());

            foreach ($l_report_ids AS $l_report_id)
            {
                // Get report specific data
                $l_report_data = $l_report_dao->get_report($l_report_id);

                // Get results of report
                $l_report_result = $l_report_dao->query($l_report_data['isys_report__query']);

                // Check for existing results
                if (is_array($l_report_result) && isset($l_report_result['num']) && $l_report_result['num'] > 0)
                {
                    // Get object ids
                    foreach ($l_report_result['content'] AS $l_report_resultset)
                    {
                        // Get object data
                        $l_objects_of_report[$l_report_data['isys_report__id']][] = $l_cmdb_dao->get_object($l_report_resultset['__id__'])
                            ->get_row();

                        // Increase object count
                        $l_count_objects++;
                    } // foreach
                } // if
            } // foreach
        } // if

        // Do we have any results
        if (is_array($l_objects_of_report) && count($l_objects_of_report) && $l_count_objects > (int) $p_notification['threshold'])
        {
            // Send for each report
            foreach ($l_objects_of_report AS $l_report_id => $l_objects)
            {
                // Write messages:
                if ($this->write_messages($p_notification, $l_objects) > 0)
                {
                    return $this->increase_counter($p_notification);
                } // if
            } // foreach
        }
        else
        {
            return $this->reset_counter($p_notification);
        } // if
    } // function

} // class
