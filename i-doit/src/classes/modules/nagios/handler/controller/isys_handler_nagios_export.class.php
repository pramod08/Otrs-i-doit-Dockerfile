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
 * Nagios Export handler
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Blümer <dbluemer@i.doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_nagios_export extends isys_handler
{
    /**
     * Nagios DAO
     *
     * @var  isys_component_dao_nagios
     */
    private $m_daoNagios;

    /**
     * Nagios Module
     *
     * @var  isys_module_nagios
     */
    private $m_moduleNagios;

    /**
     * Method for returning the nagios host ID.
     *
     * @return  integer
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function get_nagios_host()
    {
        global $argv, $g_comp_database;

        $l_key = array_search("-n", $argv);

        if (!is_numeric($l_key) || !$argv[++$l_key])
        {
            $l_res = isys_monitoring_dao_hosts::instance($g_comp_database)
                ->get_export_data();

            if ($l_res->num_rows() > 0)
            {
                $l_host = [];

                while ($l_row = $l_res->get_row())
                {
                    $l_host[] = ' - ' . $l_row['isys_monitoring_export_config__id'] . ': ' . $l_row['isys_monitoring_export_config__title'];
                } // while

                throw new InvalidArgumentException(
                    "Please specify a Nagios Host using the -n option! Try -n <nagios host id>\n\nPossible ID's are:\n" . implode("\n", $l_host)
                );
            }
            else
            {
                throw new UnexpectedValueException('There are no defined Nagios Hosts!');
            } // if
        } // if

        return (int) $argv[$l_key];
    } // function

    /**
     * Initialization method.
     *
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database;

        if ($g_comp_session->is_logged_in())
        {
            verbose("Setting up system environment");

            // Get daos, because now we are logged in.
            $this->m_moduleNagios = new isys_module_nagios();
            $this->m_daoNagios    = new isys_component_dao_nagios($g_comp_database);

            verbose("Nagios-Handler initialized (" . date("Y-m-d H:i:s") . ")");
            verbose("Type -h for help!");

            global $argv;

            if (array_search("-h", $argv))
            {
                $this->display_help();

                return true;
            } // if

            // Check status and add to logbook.
            try
            {
                $l_nagios_host_id = $this->get_nagios_host();
                $this->export($l_nagios_host_id);
            }
            catch (InvalidArgumentException $e)
            {
                verbose('Invalid argument: ' . $e->getMessage());
            }
            catch (UnexpectedValueException $e)
            {
                verbose('Unexpected value: ' . $e->getMessage());
            } // try
        } // if

        return true;
    } // function

    /**
     * Displays the help.
     */
    protected function display_help()
    {
        verbose('The nagios_export controller offers three options:');
        verbose(' -n <nagios host ID>  This will define, which nagios host shall be used for the export.');
        verbose(' -validate            This will activate the nagios export validation... Meaning, only valid objects will be exported');
        verbose(' -h                   This will display this help.');
    } // function

    /**
     * Export method.
     *
     * @param   integer $p_host_id
     *
     * @throws  InvalidArgumentException
     */
    private function export($p_host_id)
    {
        global $argv, $g_comp_database;

        $l_host         = isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_export_data($p_host_id);
        $l_hosts_number = $l_host->num_rows();

        $validate = false;

        if (array_search("-validate", $argv))
        {
            $validate = true;
        } // if

        if ($l_hosts_number != 1)
        {
            throw new InvalidArgumentException("Host #" . $p_host_id . " does not exist");
        } // if

        $l_row = $l_host->get_row();

        verbose('Starting export for "' . $l_row['isys_monitoring_export_config__title'] . '"');

        if (empty($l_row["isys_monitoring_export_config__path"]))
        {
            $l_row["isys_monitoring_export_config__path"] = "nagiosexport";
        } // if

        verbose('Exporting to directory "' . $l_row["isys_monitoring_export_config__path"] . '"');

        try
        {
            $this->m_moduleNagios->exportNagiosConfig($l_row, $validate);
        }
        catch (isys_exception_filesystem $e)
        {
            verbose($e->getMessage());
        } // try

        verbose("Done");
    } // function
} // class