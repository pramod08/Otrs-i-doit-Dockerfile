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
 * Nagios handler.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Bluemer <dbluemer@i.doit.org>
 * @version     0.9.3
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_nagios extends isys_handler
{
    /**
     * NDO component DAO.
     *
     * @var  isys_component_dao_ndo
     */
    private $m_comp_dao_ndo;
    /**
     * Logbook DAO.
     *
     * @var  isys_component_dao_logbook
     */
    private $m_dao_logbook;
    /**
     * Nagios category DAO.
     *
     * @var  isys_cmdb_dao_category_g_nagios
     */
    private $m_dao_nagios;

    /**
     * @global  isys_component_session  $g_comp_session
     * @global  isys_component_database $g_comp_database
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database;

        if ($g_comp_session->is_logged_in())
        {
            verbose("Setting up system environment");

            // Get daos, because now we are logged in.
            $this->m_dao_nagios  = new isys_cmdb_dao_category_g_nagios($g_comp_database);
            $this->m_dao_logbook = new isys_component_dao_logbook($g_comp_database);

            verbose("Nagios-Handler initialized (" . date("Y-m-d H:i:s") . ")");

            // Check status and add to logbook.
            try
            {
                $this->processStateHistory();
            }
            catch (Exception $e)
            {
                return false;
            } // try
        } // if

        return true;
    } // function

    /**
     * Process state history method.
     *
     * @throws  Exception
     */
    private function processStateHistory()
    {
        loading();
        $l_monitoring_dao = isys_factory::get_instance('isys_cmdb_dao_category_g_monitoring', isys_application::instance()->database);

        $l_hosts_res = $l_monitoring_dao->get_data(
            null,
            null,
            'AND isys_monitoring_hosts__active = 1 AND isys_monitoring_hosts__type = ' . $l_monitoring_dao->convert_sql_text(C__MONITORING__TYPE_NDO),
            null,
            C__RECORD_STATUS__NORMAL
        );

        $l_hosts_num = count($l_hosts_res);

        // We need to initialize the nagios helper.
        isys_nagios_helper::init();

        verbose('Found ' . $l_hosts_num . ($l_hosts_num == 1 ? ' host' : ' hosts'));

        if ($l_hosts_num > 0)
        {
            while ($l_row = $l_hosts_res->get_row())
            {
                $l_objID    = $l_row["isys_obj__id"];
                $l_hostname = isys_monitoring_helper::render_export_hostname($l_row['isys_obj__id']);

                verbose('Processing "' . $l_hostname . '"');

                try
                {
                    try
                    {
                        if ($l_row["isys_catg_monitoring_list__isys_monitoring_hosts__id"] > 0)
                        {
                            verbose('..', false);
                            $l_ndo_instance = isys_monitoring_ndo::factory($l_row["isys_catg_monitoring_list__isys_monitoring_hosts__id"]);
                        }
                        else
                        {
                            verbose(' ..this object has no assigned monitoring host [SKIP]');
                            continue;
                        } // if
                    }
                    catch (Exception $e)
                    {
                        verbose('  ' . $l_hostname . ': ' . $e->getMessage()) . ' [SKIP]';
                        continue;
                    } // try

                    $this->m_comp_dao_ndo = new isys_component_dao_ndo($l_ndo_instance->get_db_connection(), $l_ndo_instance->get_db_prefix());

                    if (!$this->m_comp_dao_ndo->hostExists($l_hostname))
                    {
                        throw new Exception("  Host does not exist in your NDO database [SKIP]");
                    } // if

                    $l_hist = $this->m_comp_dao_ndo->getHostStateHistory($l_hostname);
                    $l_date = $this->m_dao_logbook->getDateOfLastNDOEntry($l_objID);

                    foreach ($l_hist as $val)
                    {
                        if ($val["state_time"] <= $l_date)
                        {
                            break;
                        } // if

                        switch ($val["state"])
                        {
                            case "0":
                                verbose("  is UP");
                                $this->m_dao_logbook->set_entry(
                                    $l_hostname . ": UP",
                                    "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                    $val["state_time"],
                                    C__LOGBOOK__ALERT_LEVEL__1,
                                    $l_objID,
                                    null,
                                    null,
                                    null,
                                    C__LOGBOOK_SOURCE__NDO
                                );
                                break;

                            case "1":
                                verbose("  is DOWN");
                                $this->m_dao_logbook->set_entry(
                                    $l_hostname . ": DOWN",
                                    "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                    $val["state_time"],
                                    C__LOGBOOK__ALERT_LEVEL__3,
                                    $l_objID,
                                    null,
                                    null,
                                    null,
                                    C__LOGBOOK_SOURCE__NDO
                                );
                                break;

                            case "2":
                                verbose("  is UNREACHABLE");
                                $this->m_dao_logbook->set_entry(
                                    $l_hostname . ": UNREACHABLE",
                                    "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                    $val["state_time"],
                                    C__LOGBOOK__ALERT_LEVEL__2,
                                    $l_objID,
                                    null,
                                    null,
                                    null,
                                    C__LOGBOOK_SOURCE__NDO
                                );
                                break;

                            default:
                                throw new Exception("Error");
                        } // switch
                    } // foreach

                    loading();

                    $l_service_res = $this->m_dao_nagios->getActiveServices($l_objID);

                    if (count($l_service_res) > 0)
                    {
                        while ($l_service_row = $l_service_res->get_row())
                        {
                            if (!$this->m_comp_dao_ndo->serviceExists($l_hostname, $l_service_row["service_description"]))
                            {
                                continue;
                            } // if

                            $l_hist = $this->m_comp_dao_ndo->getServiceStateHistory($l_hostname, $l_service_row["service_description"]);
                            $l_date = $this->m_dao_logbook->getDateOfLastNDOEntry($l_service_row["service_obj_id"]);

                            foreach ($l_hist as $val)
                            {
                                if ($val["state_time"] <= $l_date)
                                {
                                    break;
                                } // if

                                switch ($val["state"])
                                {
                                    case "0":
                                        $this->m_dao_logbook->set_entry(
                                            $l_service_row["service_description"] . "(" . $l_hostname . "): OK",
                                            "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                            $val["state_time"],
                                            C__LOGBOOK__ALERT_LEVEL__1,
                                            $l_service_row["service_obj_id"],
                                            null,
                                            null,
                                            null,
                                            C__LOGBOOK_SOURCE__NDO
                                        );
                                        break;

                                    case "1":
                                        $this->m_dao_logbook->set_entry(
                                            $l_service_row["service_description"] . "(" . $l_hostname . "): WARNING",
                                            "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                            $val["state_time"],
                                            C__LOGBOOK__ALERT_LEVEL__2,
                                            $l_service_row["service_obj_id"],
                                            null,
                                            null,
                                            null,
                                            C__LOGBOOK_SOURCE__NDO
                                        );

                                        break;

                                    case "2":
                                        $this->m_dao_logbook->set_entry(
                                            $l_service_row["service_description"] . "(" . $l_hostname . "): CRITICAL",
                                            "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                            $val["state_time"],
                                            C__LOGBOOK__ALERT_LEVEL__3,
                                            $l_service_row["service_obj_id"],
                                            null,
                                            null,
                                            null,
                                            C__LOGBOOK_SOURCE__NDO
                                        );

                                        break;

                                    case "3":
                                        $this->m_dao_logbook->set_entry(
                                            $l_service_row["service_description"] . "(" . $l_hostname . "): UNKNOWN",
                                            "State time: " . $val["state_time"] . "<br/>" . $val["output"],
                                            $val["state_time"],
                                            C__LOGBOOK__ALERT_LEVEL__2,
                                            $l_service_row["service_obj_id"],
                                            null,
                                            null,
                                            null,
                                            C__LOGBOOK_SOURCE__NDO
                                        );

                                        break;

                                    default:
                                        verbose("Processing of " . $val["isys_catg_application_list__service_description"] . " on " . $l_hostname . " failed");
                                } // switch
                            } // foreach
                        } // while
                    } // if
                }
                catch (Exception $e)
                {
                    verbose('  ' . $l_hostname . ': ' . $e->getMessage());
                } // try
            } // while
        } // if
    } // function
} // class