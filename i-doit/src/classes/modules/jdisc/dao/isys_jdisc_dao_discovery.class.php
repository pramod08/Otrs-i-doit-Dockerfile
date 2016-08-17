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
 * JDisc data module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.5
 */
class isys_jdisc_dao_discovery
{
    /**
     * singleton holder
     */
    protected static $m_instance = null;
    /**
     * JDisc current discovery job
     *
     * @var string
     */
    protected $m_discovery_job = null;
    /**
     * JDisc Connection Session ID
     *
     * @var array
     */
    protected $m_sessionID = null;
    /**
     * Connector
     *
     * @var isys_protocol_soap
     */
    protected $m_soap_connector = null;
    /**
     * Request options
     *
     * @var array
     */
    protected $m_soap_request_options = [];
    /**
     * JDisc hostaddress or hostname of the device which shall be discovered
     *
     * @var string
     */
    protected $m_target = null;

    /**
     * Singelton
     */
    public static function get_instance()
    {
        if (empty(self::$m_instance)) self::$m_instance = new self();

        return self::$m_instance;
    } // function

    /**
     * Getter for the discovery job
     *
     * @return string
     */
    public function get_discovery_job()
    {
        return $this->m_discovery_job;
    } // function

    /**
     * Setter for the discovery job
     *
     * @param $p_job
     *
     * @return $this
     */
    public function set_discovery_job($p_job)
    {
        $this->m_discovery_job = $p_job;

        return $this;
    } // function

    /**
     * Getter for the JDisc session id
     *
     * @return array
     */
    public function get_sessionID()
    {
        return $this->m_sessionID;
    } // function

    /**
     * Setter for the JDisc session id
     *
     * @param $p_session
     *
     * @return $this
     */
    public function set_sessionID($p_session)
    {
        $this->m_sessionID = $p_session;

        return $this;
    } // function

    /**
     * Function for disconnecting from the Host
     *
     * @return $this
     * @throws Exception
     * @throws isys_exception_general
     */
    public function disconnect()
    {
        global $g_dirs;
        $l_wsdl_dir = $g_dirs['class'] . 'modules' . DS . 'jdisc' . DS . 'soap' . DS;

        try
        {
            if (file_exists($l_wsdl_dir . 'LogonService.wsdl'))
            {
                $this->m_soap_connector->set_wsdl($l_wsdl_dir . 'LogonService.wsdl')
                    ->set_base_url('/InventoryLogon');
                $l_options         = $this->m_soap_request_options;
                $l_options['args'] = [
                    'parameters' => [
                        'session' => $this->get_sessionID()
                    ]
                ];
                $this->m_soap_connector->request('logoff', $l_options);
            }
            else
            {
                throw new Exception('WSDL file soap/LogonService.wsdl does not exist in module folder.');
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }

        return $this;
    } // function

    /**
     * Method to connect to the Host via SOAP
     *
     * @param        $p_host
     * @param        $p_username
     * @param        $p_password
     * @param string $p_port
     * @param string $p_protocol
     *
     * @return $this
     * @throws Exception
     * @throws isys_exception_general
     */
    public function connect($p_host, $p_username, $p_password, $p_port = '9000', $p_protocol = 'http')
    {
        global $g_dirs;
        $l_wsdl_dir = $g_dirs['class'] . 'modules' . DS . 'jdisc' . DS . 'soap' . DS;

        try
        {
            if (file_exists($l_wsdl_dir . 'LogonService.wsdl'))
            {
                if (!$this->m_soap_connector)
                {
                    $this->m_soap_connector       = isys_protocol_soap::get_instance($p_host, $p_port, $p_protocol);
                    $this->m_soap_request_options = [
                        'soap_version' => SOAP_1_2,
                        'uri'          => $p_protocol . '//' . $p_host
                    ];
                } // if
                $this->m_soap_connector->set_wsdl($l_wsdl_dir . 'LogonService.wsdl')
                    ->set_base_url('/InventoryLogon')
                    ->set_user($p_username)
                    ->set_pass($p_password);
                $l_options         = $this->m_soap_request_options;
                $l_options['args'] = [
                    'parameters' => [
                        'username' => $p_username,
                        'password' => $p_password
                    ]
                ];
                $l_session_data    = $this->m_soap_connector->request('logon', $l_options);
                if($l_session_data->logonResult->status === "Success")
                {
                    $this->set_sessionID((array) $l_session_data->logonResult->sessionId);
                }
                else
                {
                    throw new Exception('Credentials for the discovery webservice are not valid.');
                } // if
            }
            else
            {
                throw new Exception('WSDL file soap/LogonService.wsdl does not exist in module folder.');
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try
        return $this;
    } // function

    /**
     * Gets all discovery jobs as html options
     *
     * @throws Exception
     */
    public function get_discovery_jobs()
    {
        global $g_dirs;
        $l_return = [];

        try
        {
            $l_wsdl_dir = $g_dirs['class'] . 'modules' . DS . 'jdisc' . DS . 'soap' . DS;

            if (file_exists($l_wsdl_dir . 'DiscoveryService.wsdl'))
            {
                if ($this->m_soap_connector)
                {
                    $this->m_soap_connector->set_wsdl($l_wsdl_dir . 'DiscoveryService.wsdl')
                        ->set_base_url('/Discovery');
                    $l_options         = $this->m_soap_request_options;
                    $l_options['args'] = [
                        'parameters' => [
                            'sessionId' => $this->get_sessionID()
                        ]
                    ];
                    $l_jobs_data       = $this->m_soap_connector->request('getDiscoveryJobs', $l_options);
                    $l_jobs_arr        = (array) $l_jobs_data->discoveryJobsResult->jobs;
                    if (isset($l_jobs_arr[1]))
                    {
                        foreach ($l_jobs_arr AS $l_obj)
                        {
                            $l_return[] = (array) $l_obj;
                        } // foreach
                    }
                    else
                    {
                        $l_return[] = $l_jobs_arr;
                    } // if
                } // if
            }
            else
            {
                throw new Exception('WSDL file soap/DiscoveryService.wsdl does not exist in module folder.');
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try
        return $l_return;
    } // function

    /**
     * Method for starting a discovery job
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_general
     */
    public function start_discovery_job()
    {
        global $g_dirs;
        $l_status = 'Failure';

        try
        {
            $l_wsdl_dir = $g_dirs['class'] . 'modules' . DS . 'jdisc' . DS . 'soap' . DS;

            if (file_exists($l_wsdl_dir . 'DiscoveryService.wsdl'))
            {
                if ($this->m_soap_connector)
                {
                    $this->m_soap_connector->set_wsdl($l_wsdl_dir . 'DiscoveryService.wsdl')
                        ->set_base_url('/Discovery');
                    $l_options         = $this->m_soap_request_options;
                    $l_options['args'] = [
                        'parameters' => [
                            'sessionId'    => $this->get_sessionID(),
                            'discoveryJob' => $this->get_discovery_job()
                        ]
                    ];
                    $l_status_data     = $this->m_soap_connector->request('startDiscoveryJob', $l_options);
                    $l_status          = $l_status_data->status;
                }
            }
            else
            {
                throw new Exception('WSDL file soap/DiscoveryService.wsdl does not exist in module folder.');
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try

        switch ($l_status)
        {
            case 'Success':
                return true;
                break;
            case 'Failure':
            default:
                return false;
                break;
        } // switch
    } // function

    /**
     * Setter for the device discover target
     *
     * @param $p_value
     *
     * @return $this
     */
    public function set_target($p_value)
    {
        $this->m_target = $p_value;

        return $this;
    } // function

    /**
     * Getter for the device discovery target
     *
     * @return string
     */
    public function get_target()
    {
        return $this->m_target;
    } // function

    /**
     * Method to trigger a discovery to the specified target device
     *
     * @return bool
     * @throws Exception
     */
    public function discover_device()
    {
        if ($this->get_target() === null) return false;

        global $g_dirs;
        $l_status = 'Failure';

        try
        {
            $l_wsdl_dir = $g_dirs['class'] . 'modules' . DS . 'jdisc' . DS . 'soap' . DS;

            if (file_exists($l_wsdl_dir . 'DiscoveryService.wsdl'))
            {
                if ($this->m_soap_connector)
                {
                    $this->m_soap_connector->set_wsdl($l_wsdl_dir . 'DiscoveryService.wsdl')
                        ->set_base_url('/Discovery');
                    $l_options         = $this->m_soap_request_options;
                    $l_options['args'] = [
                        'parameters' => [
                            'sessionId' => $this->get_sessionID(),
                            'target'    => $this->get_target()
                        ]
                    ];
                    $l_status_data     = $this->m_soap_connector->request('discoverDevice', $l_options);
                    $l_status          = $l_status_data->status;
                }
            }
            else
            {
                throw new Exception('WSDL file soap/DiscoveryService.wsdl does not exist in module folder.');
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try

        switch ($l_status)
        {
            case 'Success':
                return true;
                break;
            case 'Failure':
            default:
                return false;
                break;
        } // switch
    } // function

    /**
     * Get current running discovery log
     *
     * @return array|bool
     * @throws Exception
     */
    public function get_running_discover_status()
    {
        global $g_dirs;

        try
        {
            $l_wsdl_dir = $g_dirs['class'] . 'modules' . DS . 'jdisc' . DS . 'soap' . DS;

            if (file_exists($l_wsdl_dir . 'DiscoveryService.wsdl'))
            {
                if ($this->m_soap_connector)
                {
                    $this->m_soap_connector->set_wsdl($l_wsdl_dir . 'DiscoveryService.wsdl')
                        ->set_base_url('/Discovery');
                    $l_options     = $this->m_soap_request_options;
                    $l_status_data = $this->m_soap_connector->request('getStatus', $l_options);
                    $l_status      = $l_status_data->discoveryStatus->discoveryStatus;
                    $l_log         = $l_status_data->discoveryStatus->discoveryStatusMessage;

                    return [
                        'status' => $l_status,
                        'log'    => $l_log
                    ];
                }
            }
            else
            {
                throw new Exception('WSDL file soap/DiscoveryService.wsdl does not exist in module folder.');
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try
        return false;
    } // function

    public function __destruct()
    {
        if ($this->m_sessionID)
        {
            $this->disconnect();
        } // if
    } // function
}