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
 * Import handler
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_import_handler extends isys_import_xml
{
    protected $m_current_data;
    protected $m_data;
    protected $m_date_format = 'Y-m-d H:i:s';
    protected $m_db;
    /**
     * New array to save "flushed" tables (to only flush them once).
     *
     * @var  array
     */
    protected $m_flushed_tables = [];
    protected $m_hostname;
    /**
     * Log
     *
     * @var  isys_log
     */
    protected $m_log;
    protected $m_mandator;
    /**
     * This array can be used for several options.
     *
     * @var  array
     */
    protected $m_options = [
        'update-object-changed' => true
    ];
    protected $m_scantime;
    protected $m_type;
    protected $m_update_required = [
        'isys_import_handler' => true
    ];
    protected $m_version;

    /**
     * This method will overwrite the complete "options" array.
     *
     * @param   array $p_options
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_options(array $p_options)
    {
        $this->m_options = $p_options;

        return $this;
    } // function

    /**
     * Import Post processing
     */
    public function post($p_write_import_log = true)
    {
        $database = isys_application::instance()->database;

        if (is_object($database) && $p_write_import_log)
        {
            $l_logbook = new isys_component_dao_logbook($database);
            $l_logbook->set_entry(
                'Import-Log',
                isys_import_log::get(),
                null,
                isys_import_log::get_alarmlevel(),
                null,
                null,
                null,
                null,
                C__LOGBOOK_SOURCE__INTERNAL
            );

        } // if

        // Emit postImpoert Signal
        isys_component_signalcollection::get_instance()
            ->emit(
                'mod.cmdb.afterLegacyImport',
                $this->m_import_start_time,
                null,
                null,
                $this->m_type,
                $this->m_data
            );
    }

    /**
     * This method will set a specific option to a given value.
     *
     * @param   string $p_key
     * @param   mixed  $p_value
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_option($p_key, $p_value = null)
    {
        $this->m_options[$p_key] = $p_value;

        return $this;
    } // function

    /**
     * This method will get the value of a specific option - if the given key is not set, the "default" will be returned.
     * If no key is given, all options will be returned.
     *
     * @param   string $p_key
     * @param   mixed  $p_default
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_option($p_key = null, $p_default = null)
    {
        if ($p_key === null)
        {
            return $this->m_options;
        } // if

        if (isset($this->m_options[$p_key]))
        {
            return $this->m_options[$p_key];
        } // if

        return $p_default;
    } // function

    /**
     * Prepare dummy
     */
    public function prepare()
    {
        // Nothing to do here.
    } // function

    /**
     * Gets table of given _global_ category.
     *
     * @param   string $p_cat_const
     *
     * @return  string
     */
    public function get_category_table($p_cat_const)
    {
        $l_overview = isys_cmdb_dao_category_g_overview::instance($this->m_db);
        $l_catinfo  = $l_overview->gui_get_info_by_catg_id($p_cat_const);

        $l_row = $l_catinfo->get_row();

        return $l_row["isysgui_catg__source_table"];
    } // function

    /**
     * Deletes all category entries associated to the given object id.
     *   cat_table: name of mysql table
     *   obj_id:    id of corresponding object
     *
     * @param   string  $p_cat_table
     * @param   integer $p_obj_id
     *
     * @return  boolean
     */
    public function clear_category($p_cat_table, $p_obj_id)
    {
        $l_dao = new isys_cmdb_dao($this->m_db);

        return $l_dao->update(
            "DELETE FROM " . $p_cat_table . "_list WHERE " . $p_cat_table . "_list__isys_obj__id = " . $l_dao->convert_sql_id($p_obj_id) . ';'
        ) && $l_dao->apply_update();
    } // function

    /**
     * Checks if an import is already existing.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_scantime
     *
     * @return  boolean
     */
    public function check_scantime($p_obj_id, $p_scantime)
    {
        if (!$p_obj_id) return false;

        if ($p_scantime != null)
        {
            $l_scanstamp = strtotime($p_scantime);
            $l_scantime  = date($this->m_date_format, $l_scanstamp);
        }
        else
        {
            return false;
        } // if

        $l_dao = new isys_cmdb_dao($this->m_db);

        $l_sql = "SELECT * FROM isys_obj " . "WHERE " . "(isys_obj__id = '" . $p_obj_id . "') AND " . "(isys_obj__scantime >= '" . $l_scantime . "');";

        $l_data     = $l_dao->retrieve($l_sql);
        $l_num_rows = $l_data->num_rows();

        return ($l_num_rows > 0);
    } // function

    /**
     * Edits scan time of existing dataset.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_scantime
     * @param   string  $p_hostname
     *
     * @return  boolean
     */
    public function edit_scantime($p_obj_id, $p_scantime, $p_hostname = null)
    {
        if ($p_scantime != null)
        {
            $l_scanstamp = strtotime($p_scantime);
            $l_scantime  = date($this->m_date_format, $l_scanstamp);
        }
        else
        {
            return false;
        } // if

        $l_dao = new isys_cmdb_dao($this->m_db);

        $l_sql = "UPDATE isys_obj SET " . "isys_obj__scantime = '" . $l_scantime . "', " . "isys_obj__imported = NOW()";

        if (!empty($p_hostname))
        {
            $l_sql .= ", isys_obj__hostname = '" . $p_hostname . "'";
        } // if

        $l_sql .= " WHERE isys_obj__id = " . $l_dao->convert_sql_id($p_obj_id);

        if ($l_dao->update($l_sql))
        {
            return $l_dao->apply_update();
        } // if

        return false;
    } // function

    /**
     * Does the actual work of importing from the xml file.
     *
     * @deprecated  Delete me after finalizing import/export.
     *
     * @param       integer $p_objid
     * @param       integer $p_method
     * @param       integer $p_category_type
     */
    protected function categorize($p_objid, $p_method, $p_category_type = C__CMDB__CATEGORY__TYPE_GLOBAL)
    {
        // Retrieve import data.
        $p_data = $this->m_current_data[$p_category_type];

        $l_object_info = $this->m_current_data[C__HEAD];

        if (!is_array($p_data))
        {
            $p_data        = $this->m_data[$p_category_type];
            $l_object_info = $this->m_data[C__HEAD];
        } // if

        // Unset unused head data.
        unset($p_data[C__HEAD]);

        // Get distributor.
        $l_dist = new isys_cmdb_dao_distributor($this->m_db, $p_objid, $p_category_type);

        // Get Categories.
        $l_overview = isys_cmdb_dao_category_g_overview::instance($this->m_db);

        $_GET[C__CMDB__GET__OBJECT] = $p_objid;
        isys_module_request::get_instance()
            ->_internal_set_private("m_get", $_GET);

        if ($l_dist && $l_dist->count() > 0)
        {
            // Category Processing.
            if (is_array($p_data))
            {
                foreach ($p_data as $l_cat => $l_data)
                {
                    if (!is_numeric($l_cat))
                    {
                        verbose("(*) Import problem with: " . $l_cat);
                        continue;
                    } // if

                    // Get instance of current category dao and ui.
                    switch ($p_category_type)
                    {
                        case C__CMDB__CATEGORY__TYPE_GLOBAL:
                            $l_catg_dao = $l_overview->get_dao_by_catg_id($l_cat);
                            break;
                        case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                            $l_catg_dao = $l_overview->get_dao_by_cats_id($l_cat);
                            break;
                    } // switch

                    /**
                     * @var $l_catg_dao isys_cmdb_dao_category
                     */
                    if (!isset($l_catg_dao))
                    {
                        continue;
                    }

                    if (isset($l_data[C__HEAD]["title"]))
                    {
                        $l_cat_title = $l_data[C__HEAD]["title"];
                    }
                    else
                    {
                        $l_cat_title = str_replace("isys_cmdb_dao_category_", "", get_class($l_catg_dao));
                    } // if

                    verbose("|- " . $l_cat_title . "..", true, "+");

                    $l_current_cat = $l_dist->get_category($l_cat);
                    $l_cat_table   = $this->get_category_table($l_cat);

                    if ($p_method == "import" && !in_array($l_cat_table . $p_objid, $this->m_flushed_tables))
                    {
                        $this->m_flushed_tables[] = $l_cat_table . $p_objid;

                        isys_import_log::add("Flushing category table \"" . $l_cat_table . "\"");
                        $this->clear_category($l_cat_table, $p_objid);
                    } // if

                    if (is_object($l_current_cat))
                    {
                        // Initialize category (needed for get_general_data).
                        $l_catg_dao->init($l_current_cat->get_result());

                        // Special handling for category global to apply the object title and sysid.
                        if ($l_cat == C__CATG__GLOBAL)
                        {
                            $l_data           = (array) $l_data[0];
                            $l_data["object"] = $l_object_info;
                        } // if

                        // Import dataset.
                        if (method_exists($l_catg_dao, $p_method))
                        {
                            isys_import_log::add("# " . $l_cat_title . ", " . strtoupper($p_method));

                            if (isset($l_data[C__HEAD]))
                            {
                                unset($l_data[C__HEAD]);
                            } // if

                            // Call method.
                            if ($l_catg_dao->$p_method($l_data, $p_objid))
                            {
                                verbose(C__COLOR__LIGHT_GREEN . " done" . C__COLOR__NO_COLOR, false);
                                isys_import_log::add("# DONE");
                            }
                            else
                            {
                                verbose(C__COLOR__LIGHT_RED . " error" . C__COLOR__NO_COLOR, false);
                                isys_import_log::add("Error while importing " . $l_cat_title);
                            } // if
                        }
                        else
                        {
                            isys_import_log::add("ERROR: " . $l_cat_title . " {$p_method} not implemented");
                            verbose(C__COLOR__LIGHT_RED . " " . $p_method . "() not implemented" . C__COLOR__NO_COLOR, false);
                        } // if
                    }
                    else
                    {
                        isys_import_log::add("Category with ID \"" . $l_cat . "\" not found.");
                    } // if
                } // foreach
            }
            else
            {
                verbose("Could not read export. Wrong type!");
            } // if
        }
        else
        {
            isys_import_log::add("Distributor error, no categories found.");
        } // if
    } // function

    /**
     * Adds a new value to $this->m_data.
     *
     * @param  string $p_key
     * @param  string $p_value
     */
    private function add($p_key, $p_value)
    {
        $this->m_data[$p_key] = $p_value;
    } // function

    /**
     * Constructor.
     *
     * @param  isys_log                $p_log
     * @param  isys_component_database $p_db
     */
    public function __construct($p_log, $p_db)
    {
        parent::__construct();

        assert('$p_log instanceof isys_log');
        $this->m_log = $p_log;

        assert('$p_db instanceof isys_component_database');
        $this->m_db = $p_db;
    } // function
} // class