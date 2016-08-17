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
 * Abstract protocol class.
 *
 * @package     i-doit
 * @subpackage  Protocol
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_protocol
{
    /**
     * Prototocols
     */
    const C__HTTP  = 'http';
    const C__HTTPS = 'https';
    /**
     * Accepted Protocols.
     *
     * @var  array
     */
    protected static $m_accepted_protocols = [
        self::C__HTTP,
        self::C__HTTPS,
    ];
    /**
     * Status codes.
     *
     * @var  array  Indexed array of mixed types (based on used protocol)
     */
    protected $m_codes;
    /**
     * Current connection handler.
     *
     * @var  resource
     */
    protected $m_connection;

    /**
     * Opens new connection.
     *
     * @return  boolean  Success?
     */
    abstract public function open(); // function

    /**
     * Method for checking the protocol.
     *
     * @param   string $p_protocol
     *
     * @return  boolean
     */
    public static function check_protocol($p_protocol)
    {
        return in_array($p_protocol, self::$m_accepted_protocols);
    }

    /**
     * Desctructor. Closes eventually opened connection.
     */
    public function __destruct()
    {
        if (is_resource($this->m_connection))
        {
            curl_close($this->m_connection);
        } // if
    } // function

    /**
     * Gets current connection.
     *
     * @return  object  Returns null, if no connection has been opened yet.
     */
    public function get_connection()
    {
        return $this->m_connection;
    } // function

    /**
     * Gets status codes.
     *
     * @return  array  Indexed array of mixed types (based on used protocol). Returns null, if no status codes are available.
     */
    public function get_codes()
    {
        return $this->m_codes;
    } // function

    /**
     * Gets last status code.
     *
     * @return  mixed  Return type bases on used protocol. Returns null, if no last status code is available.
     */
    public function get_last_code()
    {
        if (count($this->m_codes) === 0)
        {
            return null;
        } // if

        return end($this->m_codes);
    } // function

    /**
     * Method for setting the protocol.
     *
     * @param   string $p_protocol
     *
     * @return  isys_protocol
     */
    public function set_protocol($p_protocol)
    {
        if (self::check_protocol($p_protocol))
        {
            $this->m_protocol = $p_protocol;
        } // if

        return $this;
    } // function
} // class