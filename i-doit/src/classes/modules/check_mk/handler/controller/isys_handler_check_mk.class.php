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
 * Controller for writing status changes to the Logbook.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_handler_check_mk extends isys_handler
{
    private $m_cmk_instances = [];
    /**
     * Check_MK category DAO.
     *
     * @var  isys_cmdb_dao_category_g_cmk
     */
    private $m_dao_cmk;

    /**
     * Logbook DAO.
     *
     * @var  isys_component_dao_logbook
     */
    private $m_dao_logbook;
    /**
     * This variable will hold the database component.
     *
     * @var  isys_component_database
     */
    private $m_db;
    /**
     * This will hold all hosts with "state > 0".
     *
     * @var  array
     */
    private $m_hosts = [];

    /**
     * Initialization method.
     *
     * @global  isys_component_session  $g_comp_session
     * @global  isys_component_database $g_comp_database
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database, $g_comp_database_system;

        if ($g_comp_session->is_logged_in())
        {
            verbose("Setting up system environment");

            $this->m_db          = &$g_comp_database;
            $this->m_dao_logbook = new isys_component_dao_logbook($this->m_db);
            $this->m_dao_cmk     = new isys_cmdb_dao_category_g_cmk($this->m_db);

            verbose("Check_MK Handler initialized (" . date("Y-m-d H:i:s") . ")");

            // Check status and add to logbook.
            try
            {
                isys_tenantsettings::initialize($g_comp_database_system);

                $this->prepare_connection()
                    ->read_log()
                    ->write_logbook();
            }
            catch (Exception $e)
            {
                return false;
            } // try
        } // if

        return false;
    } // function

    /**
     * This method will prepare the "m_cmk_instances" variable.
     *
     * @return  isys_handler_check_mk
     */
    private function prepare_connection()
    {
        // Load all hosts.
        verbose("Load all defined Check_MK instances... ");
        $l_host_res = isys_monitoring_dao_hosts::instance($this->m_db)
            ->get_data(null, C__MONITORING__TYPE_LIVESTATUS);

        if (count($l_host_res))
        {
            while ($l_host_row = $l_host_res->get_row())
            {
                if (!$l_host_row['isys_monitoring_hosts__active'])
                {
                    verbose('Found ' . $l_host_row['isys_monitoring_hosts__title'] . ', but its deactivated...');
                    continue;
                } // if

                try
                {
                    $this->m_cmk_instances[$l_host_row['isys_monitoring_hosts__id']] = isys_monitoring_livestatus::factory((int) $l_host_row['isys_monitoring_hosts__id']);
                    verbose('Found ' . $l_host_row['isys_monitoring_hosts__title'] . ', and connected!');
                }
                catch (Exception $e)
                {
                    verbose($l_host_row['isys_monitoring_hosts__title'] . ': ' . $e->getMessage());
                } // try
            } // while
        } // if

        return $this;
    } // function

    /**
     * This method will read the Check_MK log and write data in the i-doit logbook.
     *
     * @return  isys_handler_check_mk
     */
    private function read_log()
    {
        $l_last_check = isys_tenantsettings::get('check_mk.controller.last_log_check', '0');
        isys_tenantsettings::set('check_mk.controller.last_log_check', time());

        foreach ($this->m_cmk_instances as $l_cmk_host_id => $l_cmk_host)
        {
            verbose('Last check was... ' . ($l_last_check > 0 ? date('d.m.Y H:i:s', $l_last_check) : 'Never!'));

            // Retrieve all log-entries since the last check and which are "not OK".
            $l_entries = $l_cmk_host->query(
                [
                    "GET log",
                    "Filter: time > " . $l_last_check,
                    "Filter: state != " . C__MODULE__CHECK_MK__LIVESTATUS_STATE__UP,
                    "Columns: host_name time state plugin_output"
                ]
            );

            foreach ($l_entries as $l_entry)
            {
                list ($l_hostname, $l_time, $l_state, $l_state_message) = $l_entry;

                $l_host = isys_monitoring_helper::get_objects_by_hostname($l_cmk_host_id, $l_hostname);

                if (empty($l_host))
                {
                    verbose('  Could not find the host "' . $l_hostname . '" in your CMDB.');
                    continue;
                } // if

                verbose('Got data for object "' . $l_host['isys_obj__title'] . '" (#' . $l_host['isys_obj__id'] . '), with the hostname "' . $l_hostname . '".');

                if ($l_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL || $l_host['isys_catg_monitoring_list__status'] != C__RECORD_STATUS__NORMAL)
                {
                    verbose('> But the host is archived or deleted.');
                    continue;
                }

                // Format the host data and save it to an array.
                $this->m_hosts[$l_host['isys_obj__id']][$l_time][] = [
                    'hostname'      => $l_hostname,
                    'state'         => $l_state,
                    'state_message' => $l_state_message,
                    'cmdb_data'     => $l_host
                ];

                ksort($this->m_hosts[$l_host['isys_obj__id']]);
            } // foreach
        } // foreach

        return $this;
    } // function

    /**
     * This method will write the logbook entries to all the found hosts.
     *
     * @throws  Exception
     * @return  isys_handler_check_mk
     */
    private function write_logbook()
    {
        if (count($this->m_hosts))
        {
            verbose('Writing logbook entries...');

            foreach ($this->m_hosts as $l_obj_id => $l_dates)
            {
                foreach ($l_dates as $l_date => $l_rows)
                {
                    foreach ($l_rows as $l_data)
                    {
                        verbose('  Writing logbook for "' . $l_data['cmdb_data']['isys_obj__title'] . '" (#' . $l_obj_id . '), ' . date('d.m.Y H:i:s', $l_date));

                        switch ($l_data['state'])
                        {
                            case C__MODULE__CHECK_MK__LIVESTATUS_STATE__UP:
                                verbose('  > OK');
                                $this->m_dao_logbook->set_entry(
                                    $l_data['hostname'] . ": UP",
                                    "State time: " . date('d.m.Y h:i:s', $l_date) . "<br />Change to: UP<br />" . $l_data['state_message'],
                                    date('Y-m-d h:i:s', $l_date),
                                    C__LOGBOOK__ALERT_LEVEL__1,
                                    $l_obj_id,
                                    null,
                                    null,
                                    null,
                                    C__LOGBOOK_SOURCE__CMK
                                );
                                break;

                            case C__MODULE__CHECK_MK__LIVESTATUS_STATE__DOWN:
                                verbose('  > DOWN');
                                $this->m_dao_logbook->set_entry(
                                    $l_data['hostname'] . ": DOWN",
                                    "State time: " . date('d.m.Y h:i:s', $l_date) . "<br />Change to: DOWN<br />" . $l_data['state_message'],
                                    date('Y-m-d h:i:s', $l_date),
                                    C__LOGBOOK__ALERT_LEVEL__3,
                                    $l_obj_id,
                                    null,
                                    null,
                                    null,
                                    C__LOGBOOK_SOURCE__CMK
                                );
                                break;

                                break;
                            case C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNREACHABLE:
                                verbose('  > UNREACHABLE');
                                $this->m_dao_logbook->set_entry(
                                    $l_data['hostname'] . ": DOWN",
                                    "State time: " . date('d.m.Y h:i:s', $l_date) . "<br />Change to: UNREACHABLE<br />" . $l_data['state_message'],
                                    date('Y-m-d h:i:s', $l_date),
                                    C__LOGBOOK__ALERT_LEVEL__3,
                                    $l_obj_id,
                                    null,
                                    null,
                                    null,
                                    C__LOGBOOK_SOURCE__CMK
                                );
                                break;

                            default:
                            case C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNKNOWN:
                                verbose('  > UNKNOWN "' . $l_data['state_message'] . '"...');
                        } // switch
                    } // foreach
                } // foreach
            } // foreach
        } // if

        return $this;
    } // function
} // class