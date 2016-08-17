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
 * Database wrapper class for mySQL.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_database_mysql extends isys_component_database
{
    /**
     * Defines the standard MySQL port.
     *
     * @var  integer
     */
    protected $m_port = 3306;

    /**
     * @var bool
     */
    private $m_transaction_running = false;

    /**
     * mysql_unbuffered_query
     *
     * @param   string $p_query
     *
     * @return  resource
     */
    public function unbuffered_query($p_query)
    {
        return $this->query($p_query, true);
    } // function

    /**
     *
     * @param   string  $p_update
     * @param   integer $p_len
     *
     * @return  string
     */
    public function limit_update($p_update, $p_len)
    {
        return $p_update . " LIMIT " . (int) $p_len;
    }

    /**
     * @desc Get the name of the specified field in a result
     */
    public function field_table($p_res, $p_i)
    {
        return mysql_field_table($p_res, $p_i);
    } //function

    /**
     * Returns the number of affected rows by the last query.
     *
     * @param   resource $p_res
     *
     * @return  integer
     */
    public function affected_rows()
    {
        return mysql_affected_rows($this->m_db_link);
    } // function

    /**
     * Method for beginning a new transaction.
     */
    public function begin()
    {
        if (is_object($this->m_db_link) && $this->m_transaction_running)
        {
            $this->m_transaction_running = false;

            return $this->query("BEGIN;");
        }

        return false;
    } // function

    /**
     * Closes the database connection if valid.
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stücken
     * @return  boolean
     */
    public function close()
    {
        if (is_resource($this->m_db_link))
        {
            mysql_close($this->m_db_link);

            return true;
        } // if

        return false;
    } // function

    /**
     * Method for committing a new transaction.
     */
    public function commit()
    {
        if (!$this->m_transaction_running)
        {
            $this->m_transaction_running = true;

            return $this->query("COMMIT;");
        }

        return true;
    } // function

    /**
     * Reset pointer to zero.
     *
     * @param   resource $p_res
     * @param   integer  $p_row_number
     *
     * @return  boolean
     */
    public function data_seek($p_res, $p_row_number = 0)
    {
        return mysql_data_seek($p_res, $p_row_number);
    }

    /**
     * @param   string  $p_datepart
     * @param   integer $p_number
     * @param   string  $p_date
     *
     * @return  string
     */
    public function date_add($p_datepart, $p_number, $p_date)
    {
        return "DATE_ADD(" . $p_date . ", INTERVAL " . ((int) $p_number) . " " . ($p_datepart ?: 'SECONDS') . ")";
    }

    /**
     * @param   string  $p_datepart
     * @param   integer $p_number
     * @param   string  $p_date
     *
     * @return  string
     */
    public function date_sub($p_datepart, $p_number, $p_date)
    {
        return "DATE_SUB(" . $p_date . ", INTERVAL " . ((int) $p_number) . " " . ($p_datepart ?: 'SECONDS') . ")";
    } // function

    public function escape_string($p_str)
    {
        if ($this->is_connected())
        {
            return mysql_real_escape_string(strval($p_str), $this->m_db_link);
        }
        else
        {
            return str_replace(
                [
                    "\\",
                    "\x00",
                    "\n",
                    "\r",
                    "'",
                    '"',
                    "\x1a"
                ],
                [
                    "\\\\",
                    "\\0",
                    "\\n",
                    "\\r",
                    "\\'",
                    '\"',
                    "\\Z"
                ],
                $p_str
            );
        }
    } // function

    /**
     * Fetches a row as numeric+assoc array from the result set.
     *
     * @param resource $p_res
     *
     * @return array
     */
    public function fetch_array($p_res)
    {
        if (is_resource($p_res))
        {
            return mysql_fetch_array($p_res);
        }
        else return [];
    } // function

    /**
     * Fetches a row from the result set.
     *
     * @param   resource $p_res
     *
     * @return  array
     */
    public function fetch_row($p_res)
    {
        return mysql_fetch_row($p_res);
    } // function

    /**
     * Fetches a row as associative array from the result set.
     *
     * @param resource $p_res
     *
     * @return array
     */
    public function fetch_row_assoc($p_res)
    {
        return mysql_fetch_assoc($p_res);
    } // function

    /**
     * Get the flags associated with the specified field in a result
     *
     * @param   resource $p_res
     * @param   integer  $p_i
     *
     * @return  string
     */
    public function field_flags($p_res, $p_i)
    {
        return mysql_field_flags($p_res, $p_i);
    } // function

    /**
     * @desc Returns the length of the specified field
     */
    public function field_len($p_res, $p_i)
    {
        return mysql_field_len($p_res, $p_i);
    } // function

    /**
     * @desc Get the name of the specified field in a result
     */
    public function field_name($p_res, $p_i)
    {
        return mysql_field_name($p_res, $p_i);
    } // function

    /**
     * Get the type of the specified field in a result
     *
     * @param           $p_res
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_type($p_res, $p_i)
    {
        return mysql_field_type($p_res, $p_i);
    } // function

    /**
     *
     * @return  resource
     *
     * @param   resource $p_res
     */
    public function free_result($p_res)
    {
        return mysql_free_result($p_res);
    } // function

    /**
     * Retrieve mysql settings
     *
     * @param $p_key
     */
    public function get_config_value($p_key)
    {
        $l_get = $this->query('SELECT @@global.' . $this->escape_string($p_key) . ';');
        $l_row = $this->fetch_array($l_get);

        return $l_row[0];
    } // function

    /**
     * Returns the ID of the last error.
     *
     * @return  integer
     */
    public function get_last_error_as_id()
    {
        if (is_resource($this->m_db_link))
        {
            return mysql_errno($this->m_db_link);
        }
        else
            return 0;
    } // function

    /**
     * Returns the description of the last error.
     *
     * @return  string
     */
    public function get_last_error_as_string()
    {
        if (is_resource($this->m_db_link))
        {
            return mysql_error($this->m_db_link);
        }
        else
            return 'Not connected to database (Database link is not a resource)';
    } // function

    /**
     * Returns the last ID of an inserted record. Session-scope function.
     *
     * @return  integer
     */
    public function get_last_insert_id()
    {
        return mysql_insert_id($this->m_db_link);
    } // function

    /**
     * Retrieve table names by a given string ("%" wildchard is allowed).
     *
     * @param   string $p_like
     *
     * @return  array
     */
    public function get_table_names($p_like)
    {
        $l_tables = [];

        $l_res = $this->query("SHOW TABLES LIKE '" . $p_like . "'");

        while ($l_row = $this->fetch_row($l_res))
        {
            $l_tables[] = $l_row[0];
        } // while

        return $l_tables;
    }

    /**
     * Returns an array with the version information of the mySQL-DBS. On failure, it'll return null.
     *
     * @return  mixed
     */
    public function get_version()
    {
        if ($this->is_connected())
        {
            return [
                "server" => mysql_get_server_info($this->m_db_link),
                "host"   => mysql_get_host_info($this->m_db_link),
                "client" => mysql_get_client_info(),
                "proto"  => mysql_get_proto_info($this->m_db_link)
            ];
        } // if

        return null;
    }

    /**
     * Has been a connection established yet?
     *
     * @return bool
     */
    public function is_connected()
    {
        return is_resource($this->m_db_link);
    }

    /**
     * Tests if $p_table is existent.
     *
     * @param   string $p_table
     * @param   string $p_field
     *
     * @return  boolean
     * @todo    In some cases this query is totally unlogic
     */
    public function is_field_existent($p_table, $p_field)
    {
        $l_res = $this->query("DESC " . $p_table . " '" . $p_field . "';");

        return !!($this->num_rows($l_res));
    }

    /**
     * Is given parameter a valid resource?
     *
     * @param $p_resource Resource
     *
     * @return bool
     */
    public function is_resource($p_resource)
    {
        return is_resource($p_resource);
    }

    /**
     * Tests if $p_table is existent.
     *
     * @param   string $p_table
     *
     * @return  boolean
     */
    public function is_table_existent($p_table)
    {
        return !!($this->num_rows($this->query("SHOW TABLES LIKE '" . $p_table . "'")));
    }

    /**
     *
     * @param   string  $p_query
     * @param   integer $p_len
     * @param   integer $p_offset
     *
     * @return  string
     */
    public function limit_query($p_query, $p_len, $p_offset)
    {
        return $p_query . " LIMIT " . (int) $p_len . " OFFSET " . (int) $p_offset;
    }

    /**
     * @return integer
     *
     * @param resource $p_res
     *
     * @desc  Retrieves the number of fields from a query
     */
    public function num_fields($p_res)
    {
        return mysql_num_fields($p_res);
    } // function

    /**
     * @return integer
     *
     * @param resource $p_res
     *
     * @desc Returns the count of rows in the result set.
     */
    public function num_rows($p_res)
    {
        return @mysql_num_rows($p_res);
    } // function

    /**
     * Queries the database.
     *
     * @param string $p_query      Query
     * @param bool   $p_unbuffered Is this an unbuffered query? Defaults to false.
     *
     * @return resource
     * @throws isys_exception_database_mysql when something goes wrong
     */
    public function query($p_query, $p_unbuffered = false)
    {
        $l_res = false;

        try
        {
            // Try to reconnect if link is lost
            if (!is_resource($this->m_db_link))
            {
                $this->reconnect();
            }

            if (is_resource($this->m_db_link))
            {
                if ($this->m_logger) $this->m_logger->debug($p_query);

                if (!$p_unbuffered)
                {
                    $l_res = mysql_query($p_query, $this->m_db_link);
                }
                else
                {
                    $l_res = mysql_unbuffered_query($p_query, $this->m_db_link);
                }

                if ($l_res === false)
                {
                    throw new isys_exception_database_mysql(
                        "Query error: '" . $p_query . "':\n" . $this->get_last_error_as_string(), $this->get_version(), $this->get_last_error_as_id()
                    );
                }
            }
            else
            {
                new isys_exception_database_mysql('No connection to database. (Query: ' . $p_query . ')');
            }
        }
        catch (isys_exception_database_mysql $e)
        {
            isys_application::instance()->container['notify']->emergency($e->getMessage());

            throw $e;
        } // try

        unset($p_query);

        return $l_res;
    } // function

    /**
     * Method for reconnecting the database.
     *
     * @throws  Exception
     * @throws  isys_exception_database_mysql
     */
    public function reconnect()
    {
        try
        {
            if (!$this->m_db_link = mysql_connect(isys_glob_create_tcp_address($this->m_host, $this->m_port), $this->m_user, $this->m_pass, true))
            {
                throw new isys_exception_database_mysql($this->get_last_error_as_string(), [], $this->get_last_error_as_id());
            } // if

            if (!$this->select_database($this->m_db_name))
            {
                throw new isys_exception_database_mysql("Could not select database: " . $this->m_db_name, [], $this->get_last_error_as_id());
            } // if
        }
        catch (isys_exception_database_mysql $e)
        {
            throw $e;
        } // try
    } // function

    /**
     * Method for rolling back a transaction.
     */
    public function rollback()
    {
        if ($this->m_transaction_running)
        {
            $this->m_transaction_running = false;

            return $this->query("ROLLBACK;");
        }

        return false;
    } // function

    /**
     * Select a database.
     *
     * @param   string $p_databasename
     *
     * @return  boolean
     */
    public function select_database($p_databasename)
    {
        return mysql_select_db($p_databasename, $this->m_db_link);
    } // function

    /**
     * Method for setting the auto-commit function on/off.
     *
     * @param   boolean $p_value
     *
     * @return  mixed
     */
    public function set_autocommit($p_value)
    {
        return $this->query("SET AUTOCOMMIT=" . ($p_value ? "1" : "0") . ';');
    } // function

    /**
     * Method for setting the auto-commit function on/off.
     *
     * @param  string $p_level
     */
    public function set_isolation_level($p_level)
    {
        return $this->query("SET SESSION TRANSACTION ISOLATION LEVEL " . $p_level . ';');
    } // function

    /**
     * Destructor method, closes the connection.
     */
    public function __destruct()
    {
        //if ($this->m_transaction_manager)  $this->m_transaction_manager->commit();
        $this->close();
    } // function

    /**
     * Connects to a database and returns a resource link.
     *
     * @param   string  $p_host
     * @param   integer $p_port
     * @param   string  $p_user
     * @param   string  $p_password
     *
     * @return  resource
     * @throws  Exception
     * @throws  isys_exception_database_mysql
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    private function connect($p_host, $p_port, $p_user, $p_password)
    {
        try
        {
            if (!$this->m_db_link = @mysql_connect(isys_glob_create_tcp_address($p_host, $p_port), $p_user, $p_password, true))
            {
                if (mysql_error() == 'No such file or directory')
                {
                    throw new isys_exception_database_mysql(
                        'Database connection to ' . isys_glob_create_tcp_address($p_host, $p_port) . ' failed. Your MySQL server seems to be down.'
                    );
                }

                throw new isys_exception_database_mysql(mysql_error() . ' (' . $php_errormsg . ')');
            } // if

            if ($this->m_db_link)
            {
                // Disable SQL-Strict-Mode.
                list($this->m_strictmode) = $this->fetch_row($this->query("SELECT @@SESSION.sql_mode;"));
                $this->m_strictmode = ($this->m_strictmode != '');
                $this->query("SET sql_mode='';");
                $this->query("SET names utf8;");
            } // if

            if ($this->m_db_link == false)
            {
                throw new Exception(
                    "<strong>Database connection to " . $p_user . "@" . isys_glob_create_tcp_address(
                        $p_host,
                        $p_port
                    ) . " failed.</strong><br /><br />" . "Possible errors:<br />" . "* MySQL Server not loaded.<br />" . "* Password or settings for mandator connection in table \"isys_mandator\" (system database) wrong.<br />" . "* Database settings wrong in configuration file: src/config.inc.php<br /><br />" . "MySQL-Reports: [" . mysql_errno(
                    ) . "]: <strong>" . mysql_error() . "</strong>"
                );
            } // if
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return $this->m_db_link;
    } // function

    /**
     * Constructor. Connects to the specified database and selects the requested database.
     * The constructor also needs and assigns the transaction manager.
     *
     * @param   string  $p_host
     * @param   integer $p_port
     * @param   string  $p_user
     * @param   string  $p_password
     * @param   string  $p_databasename
     *
     * @throws  isys_exception_database_mysql
     */
    public function __construct($p_host, $p_port, $p_user, $p_password, $p_databasename)
    {
        try
        {
            $this->m_db_link = $this->connect($p_host, $p_port, $p_user, $p_password);
        }
        catch (isys_exception_database_mysql $e)
        {
            throw $e;
        } // try

        if ($this->is_connected())
        {
            $this->m_user = $p_user;
            $this->m_port = $p_port;
            $this->m_host = $p_host;
            $this->m_pass = $p_password;

            if (!$this->select_database($p_databasename))
            {
                global $g_db_system;
                global $g_comp_database_system;

                $l_message = $this->get_last_error_as_string();
                if ($p_databasename != $g_db_system["name"] && is_object($g_comp_database_system) && $g_comp_database_system->is_connected())
                {
                    $l_dao         = new isys_component_dao_mandator($g_comp_database_system);
                    $l_mandator_id = $l_dao->get_mandator_id_by_db_name($p_databasename);

                    if ($l_mandator_id)
                    {
                        $l_dao->deactivate_mandator($l_mandator_id);
                        $l_message = "This tenant has been deactivated";
                    } // if

                    if (strstr($l_message, $this->m_user))
                    {
                        $l_message .= "\n\n" . 'Check your tenant credentials in Admin-Center';
                    }
                } // if

                throw new isys_exception_database_mysql("Could not select database: " . $p_databasename . ' (' . $l_message . ')', mysql_error());
            } // if

            $this->m_db_name = $p_databasename;

        } // if
    } // function
} // class