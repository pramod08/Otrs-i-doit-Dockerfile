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
 * SOAP protocol.
 *
 * This is a client for SOAP calls.
 *
 * @package     i-doit
 * @subpackage  Protocol
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @author      Steven Bohm <sbohm@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_protocol_soap extends isys_protocol
{
    const C__HTTP  = 'http';
    const C__HTTPS = 'https';
    protected $m_options = [];
    protected $m_protocol = null;
    protected $m_wsdl;
    private $m_base_url = '/';
    private $m_host = null;
    private $m_pass = null;
    private $m_port = null;

    /* HTTP types */
    private $m_requests = [];
    private $m_user = null;

    /**
     * Return instance of isys_protocol_soap.
     *
     * @param   string  $p_host
     * @param   integer $p_port
     * @param   string  $p_protocol
     *
     * @return  isys_protocol_soap
     */
    public static function get_instance($p_host = 'localhost', $p_port = 80, $p_protocol = 'http')
    {
        return new isys_protocol_soap($p_host, $p_port, $p_protocol);
    } // function

    /**
     * Returns the Host without any information.
     *
     * @return  string
     */
    public function get_host()
    {
        return $this->m_protocol . "://" . $this->m_host;
    } // function

    /**
     * Sets user.
     *
     * @param   string $p_user
     *
     * @return  isys_protocol_soap
     */
    public function set_user($p_user)
    {
        $this->m_user = $p_user;

        return $this;
    } // function

    /**
     * Sets password.
     *
     * @param   string $p_pass
     *
     * @return  isys_protocol_soap
     */
    public function set_pass($p_pass)
    {
        $this->m_pass = $p_pass;

        return $this;
    } // function

    /**
     * Sets the base url. This url is added in front of every request.
     *
     * @param   string $p_base_url
     *
     * @return  isys_protocol_soap
     */
    public function set_base_url($p_base_url)
    {
        $this->m_base_url = $p_base_url;

        return $this;
    } // function

    /**
     * Retrieve base url.
     *
     * @return  string
     */
    public function get_base_url()
    {
        return $this->m_base_url;
    } // function

    /**
     * Attach a string to the base URL.
     *
     * @param   string $p_base_url
     *
     * @return  isys_protocol_soap
     */
    public function attach_base_url($p_base_url)
    {
        $this->m_base_url .= $p_base_url;

        return $this;
    } // function

    /**
     * Retrieve all options.
     *
     * @return  array
     */
    public function get_options()
    {
        return $this->m_options;
    } // function

    /**
     * Sets the options and overrides all old ones.
     *
     * @param   array $p_options
     *
     * @return  isys_protocol_soap
     */
    public function set_options($p_options)
    {
        assert('is_array($p_options)');
        $this->m_options = $p_options;

        return $this;
    } // function

    /**
     * Adds a single option.
     *
     * @param   string $p_key
     * @param   mixed  $p_value
     *
     * @return  isys_protocol_soap
     */
    public function add_option($p_key, $p_value)
    {
        assert('is_string($p_key) && !empty($p_key)');
        assert('!empty($p_value)');
        $this->m_options[$p_key] = $p_value;

        return $this;
    } // function

    /**
     * Set options.
     *
     * @param   array $p_options
     *
     * @return  isys_protocol_soap
     */
    public function add_options(array $p_options)
    {
        assert('is_array($p_options)');
        $this->m_options = array_merge($this->m_options, $p_options);

        return $this;
    } // function

    /**
     * Retrieve the WDSL.
     *
     * @return  string
     */
    public function get_wsdl()
    {
        return $this->m_wsdl;
    } // function

    /**
     * Set the WSDL.
     *
     * @param   string $p_wsdl
     *
     * @return  isys_protocol_soap
     */
    public function set_wsdl($p_wsdl)
    {
        assert('is_string($p_wsdl)');
        $this->m_wsdl = $p_wsdl;

        return $this;
    } // function

    /**
     * Opens a standard get connection to the base url.
     *
     * @todo    Absolutely not useful!
     * @return  string
     */
    public function open()
    {
        return $this->get('');
    } // function

    /**
     * Gets a request.
     *
     * @param   string $p_path
     * @param   array  $p_params
     *
     * @return  string
     */
    public function get($p_path, $p_params = [])
    {
        $this->m_requests[] = $l_request = $this->request($p_path, $p_params);

        return $l_request;
    } // function

    /**
     * Get request array
     *
     * @return array
     */
    public function get_requests()
    {
        return $this->m_requests;
    } // function

    /**
     * Starts the HTTP request.
     *
     * @param   string $p_method
     * @param   array  $p_params
     *
     * @throws  isys_exception_general
     * @return  string
     */
    public function request($p_method, $p_params = [])
    {
        assert('is_string($p_method)');
        assert('is_array($p_params)');

        // WSDL:
        $l_wsdl = null;

        if (isset($this->m_wsdl))
        {
            $l_wsdl = $this->m_wsdl;
        } // if

        // Options:
        $l_options             = [];
        $l_options['location'] = $this->url($p_params['location']);

        if (isset($this->m_user))
        {
            $l_options['login'] = $this->m_user;
        } // if

        if (isset($this->m_pass))
        {
            $l_options['password'] = $this->m_pass;
        } // if

        if (isset($p_params['uri']))
        {
            $l_options['uri'] = $p_params['uri'];
        } // if

        if (isset($p_params['use']))
        {
            $l_options['use'] = $p_params['use'];
        } // if

        if (isset($p_params['style']))
        {
            $l_options['style'] = $p_params['style'];
        } // if

        $l_options['trace'] = true;

        try
        {
            // Initiate client library:
            $this->m_connection = new SoapClient($l_wsdl, $l_options);

            $l_args = [];

            if (isset($p_params['args']))
            {
                assert('is_array($p_params["args"])');
                $l_args = $p_params['args'];
            } // if

            $l_result = $this->m_connection->__soapCall($p_method, $l_args, $l_options);

            return $l_result;
        }
        catch (Exception $l_soap_fault)
        {
            throw new isys_exception_general(
                'SOAP call failed: ' . $l_soap_fault->getMessage(
                ) . '---Please check your configuration settings, especially credentials and protocol (e. g. http) in service url.'
            );
        } // try
    } // function

    /**
     * Wrapper method
     *
     * @return array
     */
    public function __getFunctions()
    {
        $l_return = [];
        if (isset($this->m_connection) && is_object($this->m_connection))
        {
            $l_return = $this->m_connection->__getFunctions();
        }
        else
        {
            if (isset($this->m_wsdl))
            {
                // Initiate client library:
                $this->m_connection = new SoapClient($this->m_wsdl);

                $l_return = $this->m_connection->__getFunctions();
            } // if
        } // if
        return $l_return;
    } // function

    /**
     * @return  string
     */
    public function get_protocol()
    {
        return $this->m_protocol;
    } // function

    /**
     * Set port.
     *
     * @param   string $p_port
     *
     * @return  isys_protocol_soap
     */
    public function set_port($p_port)
    {
        $this->m_port = $p_port;

        return $this;
    } // function

    /**
     * Singleton clone method.
     */
    protected function __clone()
    {
        ;
    } // function

    /**
     * Get a http url.
     *
     * @param   string $p_path
     *
     * @return  string
     */
    private function url($p_path = null)
    {
        return $this->m_protocol . "://" . $this->m_host . ":" . $this->m_port . $this->m_base_url . $p_path;
    } // function

    /**
     * Singleton constructor.
     *
     * @param   string  $p_host
     * @param   integer $p_port
     * @param   string  $p_protocol
     *
     * @throws  isys_exception_general
     */
    public function __construct($p_host, $p_port, $p_protocol)
    {
        global $g_product_info;

        if (!class_exists('SoapClient'))
        {
            throw new isys_exception_general('PHP extension "SOAP" is missing.');
        } // if

        assert('is_string($p_host)');
        assert('is_int($p_port)');
        assert('is_string($p_protocol)');

        $this->m_host     = $p_host;
        $this->m_port     = $p_port;
        $this->m_protocol = $p_protocol;

        $this->m_options['user_agent'] = 'i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['type'];
    } // function
} // class