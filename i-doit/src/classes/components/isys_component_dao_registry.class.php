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
 * DAO Registry
 * The registry DAO is a wrapper to a hierachical configuration table,
 * which is user-definable.
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @author     Dennis Stücken <dstuecken@i-doit.org> - 2007-09-12
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/**
 * Registry DAO
 */
class isys_component_dao_registry extends isys_component_dao
{
    /* Definition for the required root object */
    const c_root_parentid = 0;
    const c_root_key      = "[Root]";
    const c_cache_file    = "cache_registry.inc.php";

    private $m_cache_l1;
    private $m_reg_field_deletable;
    private $m_reg_field_editable;
    private $m_reg_field_id;
    private $m_reg_field_key;
    private $m_reg_field_parentid;
    private $m_reg_field_val;
    private $m_reg_table;
    private $m_source_configured;

    public function get_root()
    {
        return $this->get_by_parent_and_key(self::c_root_parentid, self::c_root_key);
    }

    /**
     * @return boolean
     * @desc Returns if the datasource configured
     */
    public function is_datasource_configured()
    {
        return $this->m_source_configured;
    }

    /**
     * @return boolean
     * @desc Configures the data source
     */
    public function configure_datasource($p_table, $p_field_id, $p_field_parentid, $p_field_key, $p_field_val, $p_field_deletable, $p_field_editable)
    {
        $this->m_reg_table           = $p_table;
        $this->m_reg_field_id        = $p_field_id;
        $this->m_reg_field_parentid  = $p_field_parentid;
        $this->m_reg_field_key       = $p_field_key;
        $this->m_reg_field_val       = $p_field_val;
        $this->m_reg_field_deletable = $p_field_deletable;
        $this->m_reg_field_editable  = $p_field_editable;

        return true;
    }

    /**
     * @return isys_component_dao_result
     *
     * @param integer $p_parentid
     *
     * @desc Returns a DAO result where all records have a parent ID of
     *       $p_parentid
     */
    public function get_by_parentid($p_parentid)
    {
        if (!$this->m_source_configured) return ISYS_NULL;

        $l_q = "SELECT * FROM " . $this->m_reg_table . " " . "WHERE " . $this->m_reg_field_parentid . "=" . $p_parentid . ";";

        return $this->retrieve($l_q);
    }

    /**
     * @param integer $p_parentid
     * @param string  $p_key
     * @param string  $p_val
     * @param integer $p_nDeletable
     * @param integer $p_nEditable
     *
     * @desc Adds an entry to the registry
     * @return isys_component_dao_result|null
     */
    public function add_node($p_parentid, $p_key, $p_val, $p_nDeletable, $p_nEditable)
    {
        if (!$this->m_source_configured) return null;

        /* Is node not existent? */
        $l_noderes = $this->get_by_parent_and_key($p_parentid, $p_key);
        if ($l_noderes)
        {
            if ($l_noderes->num_rows() == 0 && $p_parentid != 0)
            {
                //set empty integer values to DEFAULT
                if (is_null($p_nDeletable) || $p_nDeletable < 0)
                {
                    $p_nDeletable = "DEFAULT";
                }
                else
                {
                    $p_nDeletable = "'$p_nDeletable'";
                }
                if (is_null($p_nEditable) || $p_nEditable < 0)
                {
                    $p_nEditable = "DEFAULT";
                }
                else
                {
                    $p_nEditable = "'$p_nEditable'";
                }

                /* Create transaction query */
                $l_q = "INSERT INTO " . $this->m_reg_table . " (" . $this->m_reg_field_parentid . ", " . $this->m_reg_field_key . ", " . $this->m_reg_field_val . ", " . $this->m_reg_field_deletable . ", " . $this->m_reg_field_editable . ") VALUES (" . $p_parentid . ", " . "'" . $p_key . "', " . "'" . $this->m_db->escape_string(
                        $p_val
                    ) . "', " . $p_nDeletable . ", " . $p_nEditable . ");";

                /* Start transaction for update */
                if ($this->update($l_q))
                {
                    $this->apply_update();

                    $l_q = "SELECT * FROM " . $this->m_reg_table . " " . "WHERE " . $this->m_reg_field_id . "=" . $this->get_last_insert_id() . ";";

                    $l_res = $this->retrieve($l_q);

                    return $l_res;
                }
                else
                {
                    $this->cancel_update();

                    return null;
                }
            }
        }

        throw new isys_exception_general("Node already exists");
    }

    /**
     * @param integer $p_id
     * @param string  $p_key
     * @param string  $p_val
     * @param integer $p_nDeletable (1 or 0)
     * @param integer $p_nEditable  (1 or 0)
     *
     * @desc Updates a node with the specified data
     * @return boolean|null
     */
    public function set($p_id, $p_key, $p_val, $p_nDeletable, $p_nEditable)
    {
        if (!$this->m_source_configured) return null;

        $l_q = "UPDATE " . $this->m_reg_table . " SET " . "" . $this->m_reg_field_key . "='" . $p_key . "', " . "" . $this->m_db->escape_string(
                $this->m_reg_field_val
            ) . "='" . $p_val . "', " . "" . $this->m_reg_field_deletable . "='" . $p_nDeletable . "', " . "" . $this->m_reg_field_editable . "='" . $p_nEditable . "'" . "WHERE " . $this->m_reg_field_id . "=" . $p_id . ";";

        if ($this->update($l_q))
        {

            /* Rewrite cache */
            $this->rewrite_cache();

            $this->apply_update();

            return true;
        }
        else
        {
            $this->cancel_update();

            return false;
        }
    }

    /**
     * Checks if a key exists
     *
     * @param string $p_key
     *
     * @author ds, 2009
     * @return boolean
     */
    public function key_exists($p_key)
    {

        $l_sql = "SELECT " . $this->m_reg_field_id . " FROM " . $this->m_reg_table . " " . "WHERE " . "" . $this->m_reg_field_key . " = " . "'" . $p_key . "' " . ";";

        return ($this->retrieve($l_sql)
                ->num_rows() > 0) ? true : false;
    }

    /**
     * Save a key by name
     *
     * @param string $p_key
     * @param string $p_val
     */
    public function set_value($p_key, $p_val, $p_parent_path = null, $p_deletable = 0, $p_editable = 1)
    {
        $l_bRet = false;

        if ($this->key_exists($p_key))
        {

            $l_strSQL = "UPDATE " . "" . $this->m_reg_table . " " . "SET " . "" . $this->m_db->escape_string(
                    $this->m_reg_field_val
                ) . " = " . "'" . $p_val . "' " . "WHERE " . "" . $this->m_reg_field_key . " = " . "'" . $p_key . "' " . ";";

            if ($this->update($l_strSQL))
            {

                /* Rewrite cache */
                $this->rewrite_cache();

                $l_bRet = $this->apply_update();
            }
        }
        else
        {
            if (!is_null($p_parent_path))
            {

                $l_parent = $this->get_by_path($p_parent_path);

                if ($l_parent->num_rows() > 0)
                {

                    $l_parent_data = $l_parent->get_row();
                    $l_parent_id   = $l_parent_data[$this->m_reg_field_id];

                    $this->add_node($l_parent_id, $p_key, $p_val, $p_deletable, $p_editable);
                }
            }
        }

        return $l_bRet;
    }

    /**
     * Returns a registry entry as DAO result.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get($p_id)
    {
        return $this->get_by_id($p_id);
    }

    /**
     * Returns a registry entry as DAO result which is resolved by the node ID.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_by_id($p_id)
    {
        return $this->retrieve("SELECT * FROM " . $this->m_reg_table . " WHERE " . $this->m_reg_field_id . "=" . $this->convert_sql_id($p_id) . ";");
    }

    public function get_by_key($p_key)
    {
        return $this->retrieve("SELECT * FROM " . $this->m_reg_table . " WHERE " . $this->m_reg_field_key . "=" . $this->convert_sql_text($p_key) . ";");
    } // function

    /**
     * @return mixed
     *
     * @param array $p_res
     *
     * @desc Returns the ID from a result row
     */
    public function &get_id_by_array(&$p_res)
    {
        return $p_res[$this->m_reg_field_id];
    } // function

    /**
     * @return integer
     *
     * @param array $p_res
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2005-09-22
     * @desc   Returns the integer deletable from a result row
     */
    public function &get_deletable_by_array(&$p_res)
    {
        return $p_res[$this->m_reg_field_deletable];
    }

    /**
     * @return integer
     *
     * @param array $p_res
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2005-09-22
     * @desc   Returns the integer editable from a result row
     */
    public function &get_editable_by_array(&$p_res)
    {
        return $p_res[$this->m_reg_field_editable];
    }

    /**
     * @return mixed
     *
     * @param array $p_res
     *
     * @desc Returns the key from a result row
     */
    public function &get_key_by_array(&$p_res)
    {
        return $p_res[$this->m_reg_field_key];
    }

    /**
     * @return mixed
     *
     * @param array $p_res
     *
     * @desc Returns the parent ID from a result row
     */
    public function &get_parentid_by_array(&$p_res)
    {
        return $p_res[$this->m_reg_field_parentid];
    }

    /**
     * @return mixed
     *
     * @param array $p_res
     *
     * @desc Returns the value from a result row
     */
    public function &get_value_by_array(&$p_res)
    {
        return $p_res[$this->m_reg_field_val];
    }

    /**
     * @return isys_component_dao_result
     *
     * @param string $p_path
     * @param string $p_delim
     *
     * @desc Remembering HKEY_LOCAL_MACHINE\Software\Micro$oft\... ? This is the
     *       same way of using the registry :-) This function returns a DAO result
     *       or ISYS_NULL on failure.
     */
    public function get_by_path($p_path, $p_delim = '/')
    {

        /**
         * Imagine you have a path such:
         *  isys_root/config/global/db/hostname
         *
         * then this method will explode this function into
         * an array. After that we will iterate through that
         * array and search exactly one result that fits
         * on one entry. When we reached 'hostname' respective
         * the last entry, we'll return the appropriate data.
         */

        if (is_string($p_path))
        {
            $l_pdata    = explode($p_delim, $p_path);
            $l_parentid = self::c_root_parentid;

            if (count($l_pdata))
            {
                $l_path = 0;

                /**
                 * Resolve path iteratively by going through
                 * each path component
                 */
                foreach ($l_pdata as $l_pkey)
                {
                    if ($l_path < count($l_pdata) - 1)
                    {
                        $l_dbres = $this->get_by_parent_and_key($l_parentid, $l_pkey);

                        // Only ONE result allowed
                        if ($l_dbres->num_rows() == 1)
                        {
                            $l_dbdata   = $l_dbres->get_row();
                            $l_parentid = $l_dbdata[$this->m_reg_field_id];
                        } // ansonsten ungültig, da sowas nur einmal vorhanden sein darf ...
                    }
                    else
                    {
                        $l_return = $this->get_by_parent_and_key($l_parentid, $l_pkey);
                        if ($l_return) return $l_return;
                        else return null;
                    }

                    $l_path++;
                }
            }
        }

        return null;
    }

    /**
     * @return mixed
     *
     * @param string $p_path
     * @param [mixed default]
     *
     * @desc  Determines the value for the specified path
     */
    public function __get($p_path)
    {
        if (func_num_args() > 1)
        {
            $l_default = func_get_arg(1);
        }
        else $l_default = "";

        if (is_string($p_path))
        {

            if (is_array($this->m_cache_l1) && array_key_exists($p_path, $this->m_cache_l1))
            {
                if (!isset($this->m_cache_l1[$p_path]) && $l_default) return $l_default;

                return $this->m_cache_l1[$p_path];

            }
            else
            {
                $l_res = $this->get_by_path($p_path);
                if ($l_res->num_rows() > 0)
                {
                    $l_row  = $l_res->get_row();
                    $l_data = $this->get_value_by_array($l_row);

                    if ($l_data != ISYS_NULL)
                    {
                        isys_glob_reset_type($l_data);

                        $this->m_cache_l1[$p_path] = $l_data;

                        if (empty($l_data) && $l_default) return $l_default;
                        else return $l_data;
                    }
                }
                else return $l_default;
            }
        }

        return ISYS_NULL;

    }

    /**
     * @return array
     *
     * @param integer $p_id
     * @param         ref -array $p_array
     * @desc $p_id is defining the starting node from which this function should
     *                    fetch the data. It stores the following structure in $p_array:
     *                    --------------------------------------------------------------------
     *                    struct element {
     *                    id        of integer,
     *                    parent_id of integer,
     *                    key       of string,
     *                    value     of string,
     *                    next      of struct element
     *                    }
     */
    public function &get_all_by_id($p_id, &$p_array)
    {
        if (!$this->m_source_configured) return ISYS_NULL;

        /* Arguments valid? */
        if (is_integer($p_id) && is_array($p_array))
        {
            /* Fetch all children of $p_id */
            $l_daores = $this->get_by_parentid($p_id);

            /* Results found? */
            if ($l_daores->num_rows())
            {
                $l_records = 0;

                /* Iterate through result set */
                while ($l_line = $l_daores->get_row())
                {
                    $p_array[$l_records]         = $l_line;
                    $p_array[$l_records]["next"] = [];

                    /* Recurse here ... */
                    $this->get_all_by_id(
                        intval($this->get_id_by_array($l_line)),
                        $p_array[$l_records]["next"]
                    );

                    $l_records++;
                }
            }

            return $p_array;
        }

        return ISYS_NULL;
    }

    /**
     * Get the cache path and filename
     *
     * @return string
     */
    public function get_cache_file()
    {
        return isys_glob_get_temp_dir() . self::c_cache_file;
    }

    /**
     * Creates a cache of the registry
     *
     * @param string &$p_cache
     *
     * @return NULL
     */
    public function &cache($p_id, &$p_array, $p_parent_path = "", $p_parent_id = 0)
    {
        if (!$this->m_source_configured) return ISYS_NULL;

        /* Arguments valid? */
        if (is_integer($p_id) && is_array($p_array))
        {
            /* Fetch all children of $p_id */
            $l_daores = $this->get_by_parentid($p_id);

            /* Results found? */
            if ($l_daores->num_rows())
            {
                $l_parent_path = '';
                /* Iterate through result set */
                while ($l_line = $l_daores->get_row())
                {
                    $l_tmp_array = [];

                    $l_id = $l_line[$this->m_reg_field_id];

                    $l_this = $l_line[$this->m_reg_field_key];
                    if ($p_parent_id != "")
                    {
                        $l_parent_path = $p_parent_path . "/";
                    }
                    $l_parent_path .= $l_this;

                    if ($l_line[$this->m_reg_field_parentid] == $p_parent_id && !is_null($l_line[$this->m_reg_field_val]))
                    {

                        if (!array_key_exists($l_parent_path, $this->m_cache_l1))
                        {
                            $this->m_cache_l1[$l_parent_path] = $l_line[$this->m_reg_field_val];
                        }

                    }

                    /* Recurse here ... */
                    $this->cache(
                        intval($this->get_id_by_array($l_line)),
                        $l_tmp_array,
                        $l_parent_path,
                        $l_id
                    );

                }
            }

            return $p_array;
        }

        return ISYS_NULL;
    }

    public function write_cache($p_cachefile)
    {

        if (file_exists($p_cachefile))
        {
            isys_glob_delete_recursive($p_cachefile, $x, $y);
        }

        /* Evaluate filename and open it */
        $l_cachefile = fopen($p_cachefile, "w");

        if ($l_cachefile)
        {
            /* File header */
            fputs($l_cachefile, "<?php\n");
            fputs($l_cachefile, "/* i-doit registry cache */ \n");

            /* File content */
            $l_cache = '$g_registry_cache = array(';
            $l_cache .= "\n";

            $l_c = count($this->m_cache_l1);
            $i   = 0;
            foreach ($this->m_cache_l1 as $l_key => $l_value)
            {

                $l_cache .= "\t\t\t\t";
                $l_cache .= '"' . $l_key . '" => "' . $l_value . '"';
                if (++$i < $l_c) $l_cache .= ",";
                $l_cache .= "\n";

            }
            $l_cache .= "\n\t\t);";

            fputs($l_cachefile, $l_cache);

            /* File footer */
            fputs($l_cachefile, "\n");
            fputs($l_cachefile, "?>");

            fclose($l_cachefile);
            chmod($p_cachefile, 0777);
        }
    }

    /**
     *
     * @param integer $p_id
     *
     * @return ref-array
     * @desc Deletes a subtree from a node by the specified $p_id.
     *       Returns ISYS_NULL on failure, the array with
     *       the deleted values on success.
     */
    public function &delete_by_id($p_id)
    {
        if (!$this->m_source_configured) return ISYS_NULL;

        $l_thisentry = $this->get_by_id($p_id);
        if ($l_thisentry->num_rows() == 1)
        {
            /* Create deletion list and append the current one as first entry */
            $l_dellist = [$l_thisentry->get_row()];

            /* Recurse deletion list */
            $this->create_entry_list($p_id, $l_dellist);

            if (count($l_dellist))
            {
                /* Iterate through deletion list and delete entries */
                foreach ($l_dellist as $l_delentry)
                {
                    $this->update(
                        "DELETE FROM " . $this->m_reg_table . " " . "WHERE " . $this->m_reg_field_id . "=" . $l_delentry[$this->m_reg_field_id] . ";"
                    );
                }

                /* Commit the changes */
                $this->apply_update();

                return $l_dellist;
            }
        }
    }

    /**
     * @return boolean
     *
     * @param string $p_key
     *
     * @desc Checks the syntax of a key and returns a boolean result
     */
    public function check_key($p_key)
    {
        /* 1. Must be a string */
        if (is_string($p_key))
        {
            /* 2. Only a-z, A-Z, 0-9, ., _ and - are permitted in the key */
            if (preg_match(
                "/^[a-zA-Z0-9._-]+$/i",
                $p_key
            ))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Rewrites the registry cache
     *
     */
    public function rewrite_cache()
    {
        $this->m_cache_l1 = [];

        $this->cache(0, $this->m_cache_l1);

        return $this->write_cache($this->get_cache_file());
    }

    /**
     * @return boolean
     * @desc Returns if root is existent
     */
    private function is_root_existent()
    {
        $l_rootres = $this->get_root();

        return ($l_rootres ? ($l_rootres->num_rows() ? true : false) : false);
    }

    /**
     * @return isys_component_dao_result
     *
     * @param integer $p_parentid
     * @param string  $p_key
     *
     * @desc Returns record specified by the parentID and Key. Useful
     *       in situations when you need to determine if a special
     *       key is existent in a "container" (parentID).
     */
    private function get_by_parent_and_key($p_parentid, $p_key)
    {
        if (!$this->m_source_configured) return ISYS_NULL;

        $l_q = "SELECT * FROM " . $this->m_reg_table . " " . "WHERE " . $this->m_reg_field_parentid . "=" . $p_parentid . " " . "AND " . $this->m_reg_field_key . "='" . $p_key . "';";

        return $this->retrieve($l_q);
    }

    /**
     * @param integer $p_id
     * @param array   & $p_entry_list
     *
     * @desc Recurse a list of entries from $p_id on
     */
    private function create_entry_list($p_id, &$p_entry_list)
    {
        if (is_array($p_entry_list))
        {
            $l_res = $this->get_by_parentid($p_id);
            if ($l_res->num_rows())
            {
                while ($l_record = $l_res->get_row())
                {
                    $p_entry_list[] = $l_record;
                    $this->create_entry_list(
                        $this->get_id_by_array($l_record),
                        $p_entry_list
                    );
                }
            }
        }
    }

    /**
     * @param isys_component_database $p_db
     * @param string                  $p_table
     * @param integer                 $p_field_id
     * @param integer                 $p_field_parentid
     * @param string                  $p_field_key
     * @param string                  $p_field_val
     * @param integer                 $p_field_deletable
     * @param integer                 $p_field_editable
     */
    public function __construct(isys_component_database &$p_db, $p_table, $p_field_id, $p_field_parentid, $p_field_key, $p_field_val, $p_field_deletable, $p_field_editable)
    {
        global $g_registry_cache;

        parent::__construct($p_db);

        $this->m_source_configured = false;

        if ($this->configure_datasource(
            $p_table,
            $p_field_id,
            $p_field_parentid,
            $p_field_key,
            $p_field_val,
            $p_field_deletable,
            $p_field_editable
        )
        )
        {
            $this->m_source_configured = true;
            $this->m_cache_l1          = [];

            if (!file_exists($this->get_cache_file()))
            {
                $this->rewrite_cache();
            }
            else
            {

                include_once($this->get_cache_file());
                $this->m_cache_l1 = $g_registry_cache;

            }

            if (!$this->is_root_existent())
            {
                throw new isys_exception_general
                (
                    "No Root Object for Registry DAO in '" . $p_table . "' existent!"
                );
            }
        }
    }
}

?>