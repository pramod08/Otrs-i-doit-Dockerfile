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
 * Notizen:
 * --------------------------------------------------------------------------
 * - isys_component_dao -> isys_component
 * - isys_component_dao_result
 * - isys_component_dao_user -> isys_component_dao
 */

define("IDOIT_C__DAO_RESULT_TYPE_ARRAY", 1);
define("IDOIT_C__DAO_RESULT_TYPE_ROW", 2);
define("IDOIT_C__DAO_RESULT_TYPE_ALL", 3);

/**
 * i-doit
 *
 * DAO Base classes.
 *
 * @package     i-doit›
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_dao_result implements Countable
{
    /**
     * Current database component.
     *
     * @var  isys_component_database
     */
    protected $m_db;

    /**
     * Current databse resource.
     *
     * @var  resource
     */
    protected $m_dbres;
    /**
     * The last occured error.
     *
     * @var  string
     */
    protected $m_last_error;
    /**
     * Current SQL query.
     *
     * @var  string
     */
    protected $m_query;
    /**
     * Row data
     *
     * @var array
     */
    protected $m_row_data = [];

    /**
     * Is m_dbres still actively bound or already freed?
     *
     * @var bool
     */
    private $m_resource_active = true;

    /**
     * Returns a row from a DAO result. The result type is specified by $p_result_type and defaults to a assoc+numeric array as result.
     *
     * @param   integer $p_result_type
     *
     * @return  array
     */
    public function get_row($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        /*
         if ($this->m_dbres)
        {*/
        switch ($p_result_type)
        {
            case IDOIT_C__DAO_RESULT_TYPE_ROW:
                return $this->m_db->fetch_row($this->m_dbres);
                break;
            case IDOIT_C__DAO_RESULT_TYPE_ALL:
                return $this->m_db->fetch_array($this->m_dbres);
                break;
            default:
            case IDOIT_C__DAO_RESULT_TYPE_ARRAY:
                return $this->m_db->fetch_row_assoc($this->m_dbres);
                break;
        } // switch
        /*}
        else throw new Exception('Error while retrieving dataset. $this->m_dbres is empty.');
        */
    } // function

    /**
     * Returns the specified key value from the fetched row.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get_row_value($p_key)
    {
        $this->m_row_data = $this->m_db->fetch_row_assoc($this->m_dbres);
        $this->m_db->free_result($this->m_dbres);

        return (isset($this->m_row_data[$p_key])) ? $this->m_row_data[$p_key] : null;
    } // function

    /**
     * Converts current dao result into a single array.
     *
     * @param   integer $p_result_type
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function __to_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        if ($this->count() > 0)
        {
            $this->m_row_data = $this->get_row($p_result_type);
            $this->free_result();

        }

        return $this->m_row_data;
    } // function

    /**
     * Converts current dao result into a multidimensional array.
     *
     * @param   integer $p_result_type
     *
     * @return  array
     */
    public function __as_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        $l_ret = [];

        if ($this->count() > 0)
        {
            while ($l_row = $this->get_row($p_result_type))
            {
                $l_ret[] = $l_row;
            } // while
        } // if
        $this->free_result();

        return $l_ret;
    } // function

    /**
     * @return bool|mixed
     */
    public function reset_pointer()
    {
        if ($this->count()) return $this->m_db->data_seek($this->m_dbres);

        return true;
    }

    /**
     * @return $this
     */
    public function free_result()
    {
        if ($this->m_resource_active)
        {
            $this->m_resource_active = false;

            return $this->m_db->free_result($this->m_dbres);
        }

        return false;
    } // function

    /**
     * Returns the number of rows - A wrapper method for "count()".
     *
     * @return  integer
     */
    public function num_rows()
    {
        return $this->count();
    } // function

    /**
     * Retrieves the number of fields from a query.
     *
     * @return  integer
     */
    public function num_fields()
    {
        return $this->m_db->num_fields($this->m_dbres);
    } // function

    /**
     * Get the type of the specified field in a result
     *
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_type($p_i)
    {
        return $this->m_db->field_type($this->m_dbres, $p_i);
    } // function

    /**
     *  Get the name of the specified field in a result
     *
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_name($p_i)
    {
        return $this->m_db->field_name($this->m_dbres, $p_i);
    } // function

    /**
     * Returns the length of the specified field
     *
     * @param   integer $p_i
     *
     * @return  integer
     */
    public function field_len($p_i)
    {
        return $this->m_db->field_len($this->m_dbres, $p_i);
    } // function

    /**
     * Get the flags associated with the specified field in a result.
     *
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_flags($p_i)
    {
        return $this->m_db->field_flags($this->m_dbres, $p_i);
    } // function

    /**
     * Returns the current query.
     *
     * @return  mixed  Might be an SQL query or null.
     */
    public function get_query()
    {
        return $this->m_query;
    } // function

    /**
     * Requery the last query.
     *
     * @return  isys_component_dao_result
     * @todo    Is this really used? Only found one single occurence.
     */
    public function requery()
    {
        $this->m_dbres = $this->m_db->query($this->get_query());

        return $this;
    } // function

    /**
     * Free memory on destruction
     */
    public function __destruct()
    {
        //if ($this->m_dbres) $this->free_result();
    } // function

    /**
     * Count method, called by Countable interface.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @link    http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        if ($this->m_db->is_resource($this->m_dbres))
        {
            return $this->m_db->num_rows($this->m_dbres);
        }
        else return 0;
    }

    /**
     * Constructor. Needs the database component and a database resource.
     */
    public function __construct(isys_component_database &$p_db, $p_dbres, $p_query = null)
    {
        $this->m_db    = $p_db;
        $this->m_dbres = $p_dbres;

        if ($p_query !== null)
        {
            $this->m_query = $p_query;
        } // if
    } // function
} // class

/**
 * This component is the client to the database component.
 */
class isys_component_dao extends isys_component
{
    /**
     * @var isys_component_dao[]
     */
    protected static $instances = [];
    /**
     * Database component.
     *
     * @var isys_component_database
     */
    protected $m_db;
    /**
     * Last known error
     *
     * @var string
     */
    protected $m_last_error;
    /**
     * The last executed query.
     *
     * @var  string
     */
    protected $m_last_query;

    /**
     * Return instance of current class
     *
     * @return static
     */
    public static function factory(isys_component_database $p_database)
    {
        return new static($p_database);
    }

    /**
     * Return singleton instance of current class
     *
     * @return static
     */
    public static function instance(isys_component_database $p_database)
    {
        $db_name = $p_database->get_db_name();
        $class   = get_called_class();

        if (!isset(self::$instances[$class . ':' . $db_name]))
        {
            self::$instances[$class . ':' . $db_name] = new $class($p_database);
        }

        return self::$instances[$class . ':' . $db_name];
    }

    /**
     * Return last insert id.
     *
     * @return  integer
     */
    public function get_last_insert_id()
    {
        // Retrieving last insert id from MySQL instead of the database driver
        // since we are experiencing several issues with insert id being "0".
        return $this->retrieve('SELECT LAST_INSERT_ID() as id;')
            ->get_row_value('id');
        //return $this->m_db->get_last_insert_id();
    } // function

    /**
     * Executes $p_query and returns DAO result or NULL on failure.
     * This is only for read access! For write access use self::update().
     *
     * @param   string $p_query
     *
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     */
    public function retrieve($p_query)
    {
        try
        {
            $this->m_last_query = $p_query;

            if ($this->m_db)
            {
                return new isys_component_dao_result($this->m_db, $this->m_db->query($p_query, false), $p_query);
            }
            else
            {
                throw new isys_exception_database("Retrieve failed. Database component not loaded!");
            } // if
        }
        catch (isys_exception_database $e)
        {
            throw $e;
        } // try
    } // function

    /**
     * Executes $p_query and returns a boolean result. This is for write access (UPDATE, INSERT etc.) only.
     * All write queries have to be executed in a transaction, so we need to start one if there is noone running.
     *
     * @param   string $p_query
     *
     * @throws  isys_exception_dao
     * @return  boolean
     */
    public function update($p_query)
    {
        if ($this->m_db->is_connected())
        {
            $this->m_last_query = $p_query;
            $l_ret = $this->m_db->query($p_query) or $this->m_last_error = $this->m_db->get_last_error_as_string();

            if ($l_ret)
            {
                unset($p_query);

                return $l_ret;
            }
            else
            {
                $l_mailto_support = isys_helper_link::create_mailto('support@i-doit.org', ['subject' => 'i-doit Exception: ' . $this->m_last_error]);

                throw new isys_exception_dao(
                    nl2br(
                        "<strong>MySQL-Error</strong>: " . $this->m_last_error . "\n\n" . "<strong>Query</strong>: " . $this->m_last_query . "\n\n" . "Try <a href=\"./updates\">updating</a> your database. If this error occurs permanently, contact the i-doit team, please. " . "(<a href=\"http://i-doit.org/forum\" target=\"_new\">http://i-doit.org/forum</a>, " . "<a href=\"" . $l_mailto_support . "\">support@i-doit.org</a>)"
                    )
                );
            } // if
        } // if

        return false;
    } // function

    /**
     * Change transaction behaviour
     *
     * @param $bool
     *
     * @return $this
     */
    public function set_autocommit($bool)
    {
        $this->m_db->set_autocommit($bool);

        return $this;
    }

    /**
     * Begins a transaction.
     */
    public function begin_update()
    {
        return $this->m_db->begin();
    } // function

    /**
     * After you made some update()-queries, this function will commit the transaction.
     *
     * @return  boolean
     */
    public function apply_update()
    {
        return $this->m_db->commit();
    } // function

    /**
     * Use this, if you want to rollback a transaction
     */
    public function cancel_update()
    {
        return $this->m_db->rollback();
    } // function

    /**
     * Returns how many rows were affected after an update.
     *
     * @return  integer
     */
    public function affected_after_update()
    {
        return $this->m_db->affected_rows();
    } // function

    /**
     * Returns the last query string.
     *
     * @return  string
     */
    public function get_last_query()
    {
        return $this->m_last_query;
    } // function

    /**
     * Returns the associated database component.
     *
     * @return  isys_component_database
     */
    public function get_database_component()
    {
        return $this->m_db;
    } // function

    /**
     * Convert id in sql compliant syntax depending on the value of $p_value.
     * It is used almost everywhere in i-doit.
     *
     * @param   mixed $p_value
     *
     * @return  boolean
     */
    public function convert_sql_id($p_value)
    {
        $l_id = (int) $p_value;

        if ($l_id <= 0)
        {
            return "NULL";
        } // if

        return $l_id;
    } // function

    /**
     * Converts a numeric value or a string to a integer.
     *
     * @param   mixed $p_value Can be something numeric or a string.
     *
     * @return  integer
     */
    public function convert_sql_int($p_value)
    {
        if ($p_value === null)
        {
            return "NULL";
        } // if

        return (int) $p_value;
    } // function

    /**
     * @param \League\Geotools\Coordinate\Coordinate $p_coord
     */
    public function convert_sql_point($p_coord)
    {
        if (is_a($p_coord, '\League\Geotools\Coordinate\Coordinate'))
        {
            return "POINT(" . $this->convert_sql_text($p_coord->getLatitude()) . ", " . $this->convert_sql_text($p_coord->getLongitude()) . ")";
        }
        else return "NULL";
    }

    /**
     * Convert text in SQL compliant syntax depending on system settings it is used in the methode save_element.
     *
     * @param   string $p_value
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.info>
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     * @todo    Do something about un-escaped single- and double-quotes.
     */
    public function convert_sql_text($p_value)
    {
        return "'" . $this->m_db->escape_string($p_value) . "'";
    } // function

    /**
     * Method for converting a numeric value to a float-variable as SQL understands it.
     *
     * @param   mixed $p_value Can be a string or anything numeric.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_helper::filter_number()
     */
    public function convert_sql_float($p_value)
    {
        if (is_null($p_value) || !is_numeric($p_value))
        {
            return "NULL";
        } // if

        return "'" . isys_helper::filter_number($p_value) . "'";
    } // function

    /**
     * Method for avoiding SQL to saving an empty date string.
     *
     * @param   string $p_strDate
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function convert_sql_datetime($p_strDate)
    {
        if (!empty($p_strDate) && $p_strDate != '1970-01-01' && $p_strDate != '0000-00-00')
        {
            // ID-1933  Because of the data type DATE the "NOW()" function will not work, so we need "CURDATE()".
            if ($p_strDate == "NOW()" || $p_strDate == "CURDATE()")
            {
                return $p_strDate;
            }
            else
            {
                if (is_numeric($p_strDate))
                {
                    return "'" . date("Y-m-d H:i:s", (int) $p_strDate) . "'";
                }
                else
                {
                    $l_date = strtotime($p_strDate);

                    if ($l_date === false)
                    {
                        return 'NULL';
                    } // if

                    return "'" . date("Y-m-d H:i:s", $l_date) . "'";
                } // if
            } // if
        }
        else
        {
            return "NULL";
        } // if
    } // function

    /**
     * Method for converting a boolean value to something, SQL can understand.
     *
     * @param   mixed $p_value Can be a boolean, (numeric) string or integer - Should be true (bool), 1 or "1" (NOT "false" or "true").
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function convert_sql_boolean($p_value)
    {
        return (int) !!$p_value;
    } // function

    /**
     * Prepares a MySQL conform IN() condition.
     *
     * @param   array   $p_array
     * @param   boolean $p_negate
     *
     * @return  string
     */
    public function prepare_in_condition(array $p_array, $p_negate = false)
    {
        $l_items = [];

        if (count($p_array))
        {
            foreach ($p_array as $l_item)
            {
                if (!is_numeric($l_item))
                {
                    if (defined($l_item))
                    {
                        $l_item = constant($l_item);
                    }
                    else
                    {
                        continue;
                    } // if
                } // if

                $l_items[] = $this->convert_sql_int($l_item);
            } // foreach

            if (count($l_items))
            {
                return (($p_negate) ? "NOT " : "") . "IN(" . implode(',', $l_items) . ")";
            } // if
        } // if

        return "IS NULL";
    } // function

    /**
     * Constructor. Assigns database component.
     *
     * @param   isys_component_database $p_db
     *
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_db         = $p_db;
        $this->m_last_query = "";
    } // function
} // class