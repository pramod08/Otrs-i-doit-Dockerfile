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
 * Monitoring NDO connector class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_monitoring_ndo
{
    /**
     * Configuration array.
     *
     * @var  array
     */
    protected static $m_config = [];
    /**
     * Singleton instances.
     *
     * @var  array
     */
    protected static $m_instances = [];
    /**
     * Singleton instances of the NDO DAOs.
     *
     * @var  array
     */
    protected static $m_ndo_daos = [];
    /**
     * The socket connection.
     *
     * @var  isys_component_database
     */
    protected $m_connection = null;

    /**
     * This variable indicates the currently used host.
     *
     * @var  integer
     */
    protected $m_host = null;

    /**
     * Static factory method.
     *
     * @static
     *
     * @param   integer $p_host
     *
     * @throws  isys_exception_general
     * @return  isys_monitoring_ndo
     */
    public static function factory($p_host)
    {
        if (empty($p_host))
        {
            throw new isys_exception_general(_L('LC__MONITORING__NDO_EXCEPTION__NO_CONFIG'), 0, false);
        } // if

        if (isset(self::$m_instances[$p_host]))
        {
            return self::$m_instances[$p_host];
        } // if

        return self::$m_instances[$p_host] = new self($p_host);
    } // function

    /**
     * @return  isys_monitoring_dao_ndo
     */
    public function get_ndo_dao()
    {
        if (isset(self::$m_ndo_daos[$this->m_host]))
        {
            return self::$m_ndo_daos[$this->m_host];
        } // if

        // We can not use the isys_factory here, because it may be possible that we connect to several databases in one request.
        self::$m_ndo_daos[$this->m_host] = new isys_monitoring_dao_ndo($this->get_db_connection());

        return self::$m_ndo_daos[$this->m_host]->set_db_prefix($this->get_db_prefix());
    } // function

    /**
     * Destructor for disconnecting the socket.
     */
    public function __destruct()
    {
        $this->disconnect();
    } // function

    /**
     * Public method for retrieving the currently connected database.
     *
     * @return  isys_component_database
     */
    public function get_db_connection()
    {
        return $this->m_connection;
    } // function

    /**
     * Public method for retrieving the currently connected database.
     *
     * @return  isys_component_database
     */
    public function get_db_prefix()
    {
        return self::$m_config[$this->m_host]["isys_monitoring_hosts__dbprefix"];
    } // function

    /**
     * Disconnect method.
     */
    public function disconnect()
    {
        $this->m_connection->close();
        $this->m_connection = null;
    } // function

    /**
     * Method for connecting to the configured socket.
     *
     * @throws  isys_exception_general
     */
    protected function connect()
    {
        $l_bIP = preg_match("/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]/", self::$m_config[$this->m_host]["isys_monitoring_hosts__address"]);

        if (($l_bIP && !gethostbyaddr(self::$m_config[$this->m_host]["isys_monitoring_hosts__address"])) || (!$l_bIP && !gethostbyname(
                    self::$m_config[$this->m_host]["isys_monitoring_hosts__address"]
                ))
        )
        {
            throw new isys_exception_general("Connection failed.", 0, false);
        } // if

        try
        {
            $this->m_connection = isys_component_database::get_database(
                "mysqli",
                self::$m_config[$this->m_host]["isys_monitoring_hosts__address"],
                self::$m_config[$this->m_host]["isys_monitoring_hosts__port"],
                self::$m_config[$this->m_host]["isys_monitoring_hosts__username"],
                isys_helper_crypt::decrypt(self::$m_config[$this->m_host]["isys_monitoring_hosts__password"]),
                self::$m_config[$this->m_host]["isys_monitoring_hosts__dbname"]
            );
        }
        catch (isys_exception $e)
        {
            throw new isys_exception_database("Could not connect to NDO-Database");
        } // try

        return true;
    } // function

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
        ;
    } // function

    /**
     * Private constructor method - Singleton!
     *
     * @param  integer $p_host
     */
    private function __construct($p_host)
    {
        global $g_comp_database;
        $this->m_host = $p_host;

        self::$m_config[$this->m_host] = isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_data($this->m_host, C__MONITORING__TYPE_NDO)
            ->get_row();

        $this->connect();
    } // function
} // class