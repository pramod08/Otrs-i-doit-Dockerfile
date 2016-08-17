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
 * PHP-SNMP
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

if (!function_exists("snmpget"))
{
    throw new Exception("PHP-SNMP Module not installed or activated (http://php.net/snmp). SNMP queries are currently not possible.");
} // if

class isys_library_snmp
{
    /**
     * Community.
     *
     * @var string
     */
    private $m_community = "";
    /**
     * Hostname.
     *
     * @var string
     */
    private $m_hostname = "";

    /**
     * Set Hostname.
     *
     * @param   string $p_hostname
     *
     * @return  isys_library_snmp
     */
    public function set_hostname($p_hostname)
    {
        $this->m_hostname = $p_hostname;

        return $this;
    } // function

    /**
     * Set community.
     *
     * @param   string $p_community
     *
     * @return  isys_library_snmp
     */
    public function set_community($p_community)
    {
        $this->m_community = $p_community;

        return $this;
    } // function

    /**
     * SNMPGET.
     *
     * @param   string $p_object_id
     *
     * @return  string
     */
    public function __get($p_object_id)
    {
        return $this->get_new($this->m_hostname, $this->m_community, $p_object_id);
    } // function

    /**
     * SNMPGET wrapper
     *
     * @param   string  $p_hostname
     * @param   string  $p_community
     * @param   string  $p_object_id
     * @param   integer $p_timeout
     * @param   integer $p_retries
     *
     * @return  string
     */
    public function get_new($p_hostname, $p_community, $p_object_id, $p_timeout = 1000000, $p_retries = 5)
    {
        try
        {
            if (!empty($p_hostname))
            {
                return snmpget($p_hostname, $p_community, $p_object_id, $p_timeout, $p_retries);
            } // if

        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage());
        }

        return false;
    } // function

    /**
     * SNMPGET.
     *
     * @param   string $p_object_id
     *
     * @return  string
     */
    public function get($p_object_id)
    {
        return $this->get_new($this->m_hostname, $this->m_community, $p_object_id);
    } // function

    /**
     * SNMP Walk mapper.
     *
     * @param   string  $p_hostname
     * @param   string  $p_community
     * @param   string  $p_object_id
     * @param   integer $p_timeout
     * @param   integer $p_retries
     *
     * @return  string
     */
    public function walk_new($p_hostname, $p_community, $p_object_id, $p_timeout = null, $p_retries = null)
    {
        try
        {
            return snmpwalk($p_hostname, $p_community, $p_object_id, $p_timeout, $p_retries);
        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage());
        }

        return '';
    } // function

    /**
     * Generic toString() method.
     *
     * @return  string
     */
    public function __toString()
    {
        if ($this->m_hostname && $this->m_community)
        {
            return implode(', ', snmpwalk($this->m_hostname, $this->m_community, null));
        } // if

        return "";
    } // function

    /**
     * SNMP Walk.
     *
     * @param   string $p_object_id
     *
     * @return  string
     */
    public function walk($p_object_id = null)
    {
        return snmpwalk($this->m_hostname, $this->m_community, $p_object_id);
    } // function

    /**
     * Clean the given string.
     *
     * @param   string $p_string
     *
     * @return  string
     */
    public function cleanup($p_string)
    {
        return str_replace(
            [
                "Gauge32: ",
                "\"",
                "STRING: "
            ],
            "",
            $p_string
        );
    } // function

    /**
     * Constructor.
     *
     * @param  string $p_hostname
     * @param  string $p_community
     */
    public function __construct($p_hostname = null, $p_community = null)
    {
        $this->m_hostname  = $p_hostname;
        $this->m_community = $p_community;
    } // function
} // class