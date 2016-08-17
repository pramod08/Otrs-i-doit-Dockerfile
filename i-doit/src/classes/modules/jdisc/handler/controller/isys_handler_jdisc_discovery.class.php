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
 * Handler: JDisc discovery
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_jdisc_discovery extends isys_handler
{
    /**
     * Constant for the selected "JDisc Server" parameter.
     *
     * @var string
     */
    const C__SERVER_PARAMETER = '-s';

    /**
     * Constant for the selected "Discovery Job" parameter.
     *
     * @var    string
     */
    const C__DISCOVERY_JOB_PARAMETER = '-j';

    /**
     * Constant for the usage of this handler
     *
     * @var string
     */
    const C__HELP_PARAMETER = '-h';

    /**
     * Constant for the selected device by "hostname" parameter.
     */
    const C__DEVICE_PARAMETER_NAME = '-d';

    /**
     * Constant for the selected device by "hostaddress" parameter
     */
    const C__DEVICE_PARAMETER_HOSTADDRESS = '-a';

    /**
     * Constant for showing the log while discovery
     */
    const C__DISCOVERY_SHOW_LOG = '-l';

    /**
     * Log instance for this handler.
     *
     * @var  isys_log
     */
    protected $m_log = null;

    private $m_show_log = false;

    /**
     * Desctructor
     *
     * @todo  Move it to parent class!?
     */
    public function __destruct()
    {
        $this->logout();
    } // function

    /**
     * Initiates the handler.
     */
    public function init()
    {
        global $argv, $g_comp_template_language_manager, $g_idoit_language_short;

        if (!is_object($g_comp_template_language_manager))
        {
            if (empty($g_idoit_language_short))
            {
                $g_idoit_language_short = 'en';
            } // if

            $g_comp_template_language_manager = new isys_component_template_language_manager($g_idoit_language_short);
        } // if

        // Start logging.
        $this->m_log = isys_factory_log::get_instance('jdisc_discovery');
        $this->m_log->set_verbose_level(isys_log::C__WARNING | isys_log::C__ERROR | isys_log::C__FATAL);

        $l_jdisc_server        = null;
        $l_device_hostname     = null;
        $l_device_hostaddress  = null;
        $l_jdisc_discovery_job = "Discover all";

        if (array_search(self::C__HELP_PARAMETER, $argv) !== false)
        {
            $this->usage();
        } // if
        // Retrieving the jdisc server.
        $l_slice = (array_search(
                self::C__SERVER_PARAMETER,
                $argv
            ) !== false) ? array_search(self::C__SERVER_PARAMETER, $argv) + 1 : false;

        if ($l_slice !== false)
        {
            $l_cmd          = array_slice($argv, $l_slice);
            $l_jdisc_server = $l_cmd[0];
        } // if
        // Retrieving the discovery job.
        $l_slice = (array_search(
                self::C__DISCOVERY_JOB_PARAMETER,
                $argv
            ) !== false) ? array_search(
                self::C__DISCOVERY_JOB_PARAMETER,
                $argv
            ) + 1 : false;
        if ($l_slice !== false)
        {
            $l_cmd = array_slice($argv, $l_slice);
            if (count($l_cmd) > 0)
            {
                $l_jdisc_discovery_job = trim(implode(' ', $l_cmd));
            }
            else
            {
                $l_jdisc_discovery_job = $l_cmd[0];
            }
        } // if
        // Retrieving the device hostname.
        $l_slice = (array_search(
                self::C__DEVICE_PARAMETER_NAME,
                $argv
            ) !== false) ? array_search(self::C__DEVICE_PARAMETER_NAME, $argv) + 1 : false;
        if ($l_slice !== false)
        {
            $l_cmd             = array_slice($argv, $l_slice);
            $l_device_hostname = $l_cmd[0];
        } // if
        // Retrieving the device hostname.
        $l_slice = (array_search(
                self::C__DEVICE_PARAMETER_HOSTADDRESS,
                $argv
            ) !== false) ? array_search(
                self::C__DEVICE_PARAMETER_HOSTADDRESS,
                $argv
            ) + 1 : false;
        if ($l_slice !== false)
        {
            $l_cmd                = array_slice($argv, $l_slice);
            $l_device_hostaddress = $l_cmd[0];
        } // if
        // Retrieving the device hostname.
        $this->m_show_log = (array_search(self::C__DISCOVERY_SHOW_LOG, $argv) !== false) ? true : false;

        try
        {
            // JDisc module
            $l_module = isys_module_jdisc::factory();

            $l_jdisc_server = $l_module->get_jdisc_discovery_data($l_jdisc_server, true)
                ->get_row();

            $l_host     = $l_jdisc_server['isys_jdisc_db__host'];
            $l_username = $l_jdisc_server['isys_jdisc_db__discovery_username'];
            $l_password = isys_helper_crypt::decrypt($l_jdisc_server['isys_jdisc_db__discovery_password'], 'C__MODULE__JDISC');
            $l_port     = $l_jdisc_server['isys_jdisc_db__discovery_port'];
            $l_protocol = $l_jdisc_server['isys_jdisc_db__discovery_protocol'];

            // JDisc Discovery object
            $l_discovery_obj = isys_jdisc_dao_discovery::get_instance();

            $l_discovery_obj->connect($l_host, $l_username, $l_password, $l_port, $l_protocol);

            if ($l_device_hostname === null && $l_device_hostaddress === null)
            {
                $l_discovery_jobs = $l_discovery_obj->get_discovery_jobs();
                foreach ($l_discovery_jobs AS $l_job)
                {
                    if (strtolower($l_job['name']) == strtolower($l_jdisc_discovery_job))
                    {
                        $l_discovery_obj->set_discovery_job($l_job);
                        break;
                    }
                }
                if ($l_discovery_obj->get_discovery_job() === null)
                {
                    verbose('Discovery Job "' . $l_jdisc_discovery_job . '" not found.');
                    die;
                }
                if ($l_discovery_obj->start_discovery_job())
                {
                    verbose('Discovery Job "' . $l_jdisc_discovery_job . '" has been triggered.');
                    $this->handle_output($l_discovery_obj);
                }
                else
                {
                    verbose('Failed to trigger the Discovery Job "' . $l_jdisc_discovery_job . '".');
                } // if
            }
            else
            {
                if ($l_device_hostaddress)
                {
                    $l_discovery_obj->set_target($l_device_hostaddress);
                }
                elseif ($l_device_hostname)
                {
                    $l_discovery_obj->set_target($l_device_hostname);
                } // if
                if ($l_discovery_obj->discover_device())
                {
                    verbose('Discovery of device "' . $l_discovery_obj->get_target() . '" started.', true);
                    $this->handle_output($l_discovery_obj);
                } // if
            } // if
        }
        catch (Exception $e)
        {
            verbose($e->getMessage());
        } // try
    }

    /**
     * Prints out the usage of he import handler.
     */
    public function usage()
    {
        echo "Missing parameters!\n" . "You have to use the following parameters in order for the JDisc import to work:\n\n" . "  " . self::C__DISCOVERY_JOB_PARAMETER . " Discovery Job (optional, default: \"Discover all\")\n" . "  " . self::C__SERVER_PARAMETER . " jdisc-server-ID (optional)\n" . "  " . self::C__DEVICE_PARAMETER_HOSTADDRESS . " Hostaddress (optional)\n" . "  " . self::C__DEVICE_PARAMETER_NAME . " Hostname (optional)\n" . "  " . self::C__DISCOVERY_SHOW_LOG . " show discovery log (optional)\n\n" . "Example:\n" . "./controller -v -m jdisc_discovery -s 1 -j \"Discover all\"\n" . "./controller -v -m jdisc_discovery -s 1 -a 127.0.0.1 -l" . PHP_EOL;
        die;
    }

    /**
     * Output the currently running discovery log
     *
     * @param $p_discovery_obj
     */
    private function handle_output($p_discovery_obj)
    {
        if (defined('ISYS_VERBOSE') && $this->m_show_log === true)
        {
            // Get status logs only if verbose mode is set
            if (constant('ISYS_VERBOSE') === true)
            {
                $l_running_data = $p_discovery_obj->get_running_discover_status();
                $l_status       = $l_running_data['status'];
                $l_last_log     = $l_running_data['log'];
                verbose($l_last_log, true);

                while ($l_status === 'Running')
                {
                    $l_running_data = $p_discovery_obj->get_running_discover_status();
                    $l_status       = $l_running_data['status'];
                    if ($l_last_log !== $l_running_data['log'] && $l_running_data['log'] !== '')
                    {
                        $l_last_log = $l_running_data['log'];
                        verbose($l_last_log, true);
                    } // if
                } // while
                verbose('Finished scanning device.', true);
            }
        }
    } // function
}