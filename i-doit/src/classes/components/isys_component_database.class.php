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
 * Database wrapper class for DB-Connections
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
abstract class isys_component_database extends isys_component
{

    /**
     * MySQL Protocol driver alias
     *
     * @var string
     */
    const C__MYSQL = "mysql";
    /**
     * MSSQL Protocol driver alias
     *
     * @var string
     */
    const C__MSSQL = "mssql";
    /**
     * PGSQL Protocol driver alias
     *
     * @var string
     */
    const C__PGSQL = "pgsql";
    /**
     * @var resource|object
     */
    protected $m_db_link;
    /**
     * @var string
     */
    protected $m_db_name;
    /**
     * @var string
     */
    protected $m_host;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $m_logger;
    /**
     * @var string
     */
    protected $m_pass;
    /**
     * @var int
     */
    protected $m_port;
    /**
     * @var isys_cache
     */
    protected $m_querycache = null;
    /**
     * @var bool
     */
    protected $m_strictmode = false;
    /**
     * @var string
     */
    protected $m_user;

    /**
     * @return mixed
     */
    abstract public function affected_rows();

    /**
     * @return mixed
     */
    abstract public function begin();

    /**
     * @return mixed
     */
    abstract public function close();

    /**
     * @return mixed
     */
    abstract public function commit();

    /**
     * @param     $p_res
     * @param int $p_row_number
     *
     * @return mixed
     */
    abstract public function data_seek($p_res, $p_row_number = 0);

    /**
     * @param $p_datepart
     * @param $p_number
     * @param $p_date
     *
     * @return mixed
     */
    abstract public function date_add($p_datepart, $p_number, $p_date);

    /**
     * @param $p_datepart
     * @param $p_number
     * @param $p_date
     *
     * @return mixed
     */
    abstract public function date_sub($p_datepart, $p_number, $p_date);

    /**
     * @param $p_res
     *
     * @return mixed
     */
    abstract public function fetch_array($p_res);

    /**
     * @param $p_res
     *
     * @return mixed
     */
    abstract public function fetch_row($p_res);

    /**
     * @param $p_res
     *
     * @return mixed
     */
    abstract public function fetch_row_assoc($p_res);

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    abstract public function field_flags($p_res, $p_i);

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    abstract public function field_len($p_res, $p_i);

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    abstract public function field_name($p_res, $p_i);

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    abstract public function field_type($p_res, $p_i);

    /**
     * @param $p_res
     *
     * @return mixed
     */
    abstract public function free_result($p_res);

    /**
     * @param $p_key
     *
     * @return mixed
     */
    abstract public function get_config_value($p_key);

    /**
     * @return mixed
     */
    abstract public function get_last_error_as_id();

    /**
     * @return mixed
     */
    abstract public function get_last_error_as_string();

    /**
     * @return mixed
     */
    abstract public function get_last_insert_id();

    /**
     * @param $p_like
     *
     * @return mixed
     */
    abstract public function get_table_names($p_like);

    /**
     * @return mixed
     */
    abstract public function get_version();

    /**
     * @param $p_table
     * @param $p_field
     *
     * @return mixed
     */
    abstract public function is_field_existent($p_table, $p_field);

    /**
     * @param $p_table
     *
     * @return mixed
     */
    abstract public function is_table_existent($p_table);

    /**
     * @param $p_res
     *
     * @return mixed
     */
    abstract public function num_fields($p_res);

    /**
     * @param $p_res
     *
     * @return mixed
     */
    abstract public function num_rows($p_res);

    /**
     * @param      $p_query
     * @param bool $p_unbuffered
     *
     * @return mixed
     */
    abstract public function query($p_query, $p_unbuffered = false);

    /**
     * @return mixed
     */
    abstract public function reconnect();

    /**
     * @return mixed
     */
    abstract public function rollback();

    /**
     * @param $p_databasename
     *
     * @return mixed
     */
    abstract public function select_database($p_databasename);

    /**
     * @param $p_value
     *
     * @return mixed
     */
    abstract public function set_autocommit($p_value);

    /**
     * Database manufacturer
     *
     * @param string $p_dbType
     * @param string $p_host
     * @param int    $p_port
     * @param string $p_user
     * @param string $p_password
     * @param string $p_databasename
     *
     * @return isys_component_database
     */
    public static function get_database($p_dbType, $p_host, $p_port, $p_user, $p_password, $p_databasename)
    {
        global $g_pdo_drivers;

        try
        {
            switch ($p_dbType)
            {
                case self::C__PGSQL:
                    if (array_search($p_dbType, $g_pdo_drivers) === false) throw new Exception("No PDO driver avaiable for " . $p_dbType);

                    return new isys_component_database_pdo($p_dbType, $p_host, $p_port, $p_user, $p_password, $p_databasename);
                    break;
                default:
                    return new isys_component_database_mysqli($p_host, $p_port, $p_user, $p_password, $p_databasename);
                    break;
            }

            /*
            if ((array_search($p_dbType, $g_pdo_drivers === false) && $p_dbType != null) || $p_dbType == "mysql") {

            }
            else {
                return new isys_component_database_pdo($p_dbType, $p_host, $p_port, $p_user, $p_password, $p_databasename);
            }
            */
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * @param $p_dbType
     * @param $p_host
     * @param $p_port
     * @param $p_user
     * @param $p_password
     * @param $p_databasename
     *
     * @return isys_component_database
     * @throws Exception
     */
    public static function factory($p_dbType, $p_host, $p_port, $p_user, $p_password, $p_databasename)
    {
        return self::get_database($p_dbType, $p_host, $p_port, $p_user, $p_password, $p_databasename);
    }

    /**
     * @param isys_cache $p_cache
     *
     * @return $this
     */
    public function set_querycache(isys_cache $p_cache)
    {
        $this->m_querycache = $p_cache;

        return $this;
    }

    /**
     * @param \Psr\Log\LoggerInterface $p_logger
     *
     * @return $this
     */
    public function set_logger(\Psr\Log\LoggerInterface $p_logger)
    {
        $this->m_logger = $p_logger;

        return $this;
    }

    /**
     * @return string
     */
    public function get_db_name()
    {
        return $this->m_db_name;
    }

    /**
     * @return string
     */
    public function get_user()
    {
        return $this->m_user;
    }

    /**
     * @return string
     */
    public function get_pass()
    {
        return $this->m_pass;
    }

    /**
     * @return string
     */
    public function get_host()
    {
        return $this->m_host;
    }

    /**
     * @return string
     */
    public function get_port()
    {
        return $this->m_port;
    }

    /**
     * @return resource
     */
    public function get_link()
    {
        return $this->m_db_link;
    }

    /**
     * @return bool
     */
    public function is_connected()
    {
        return (!!($this->m_db_link));
    }

    /**
     * @param $p_res
     *
     * @return bool
     */
    public function is_resource($p_res)
    {
        return is_resource($p_res);
    }

    /**
     * @param $p_query
     * @param $p_len
     * @param $p_offset
     */
    public function limit_query($p_query, $p_len, $p_offset)
    {
        return '';
    }

    /**
     * @param $p_str
     *
     * @return string
     */
    public function escape_string($p_str)
    {
        return addslashes(strval($p_str));
    }

    /**
     * Returns status of the
     * SQL-Strictmode
     */
    public function get_strictmode()
    {
        return $this->m_strictmode;
    }

    /**
     * Method for setting the transaction isolation level.
     *
     * @param  string $p_level
     */
    public function set_isolation_level($p_level)
    {
        return true;
    } // function

}