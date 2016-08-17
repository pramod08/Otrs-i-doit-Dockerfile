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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_custom_fields extends isys_cmdb_dao_category_global
{
    /**
     * Custom configuration.
     *
     * @var  array  Defaults to an empty array.
     */
    protected static $m_config = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'custom_fields';

    /**
     * Custom category info
     *
     * @var array
     */
    protected $m_category_config = [];

    /**
     * Custom category identifier.
     *
     * @var  integer  Defaults to null.
     */
    protected $m_catg_custom_id = null;

    /**
     * Contains the relation type data
     *
     * @var array
     */
    private $m_relation_type_data = [];

    /**
     * Callback function for dialog properties
     *
     * @param isys_request $p_request
     * @param              $p_arr
     *
     * @return array
     * @internal param $p_identifier
     */
    public function callback_property_dialog_field(isys_request $p_request, $p_arr)
    {
        global $g_comp_database;
        $l_data = [];

        $l_identifier = substr($p_arr[0], strpos($p_arr[0], 'c'), strlen($p_arr[0]));
        $this->set_catg_custom_id($p_arr[1]);

        $l_catg   = isys_factory::get_instance(
            'isys_custom_fields_dao',
            $g_comp_database
        )
            ->get_data($this->get_catg_custom_id())
            ->get_row();
        $l_config = unserialize($l_catg['isysgui_catg_custom__config']);

        $l_sql = 'SELECT * FROM isys_dialog_plus_custom ' . 'WHERE isys_dialog_plus_custom__identifier = ' . $this->convert_sql_text($l_config[$l_identifier]['identifier']);
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row())
        {
            $l_data[$l_row['isys_dialog_plus_custom__id']] = $l_row['isys_dialog_plus_custom__title'];
        } // while

        return $l_data;
    }

    /**
     * Creates new custom field.
     *
     * @param   integer $p_obj_id      Object identifier
     * @param   integer $p_custom_id   Custom category identifier
     * @param   string  $p_key         Field key
     * @param   string  $p_value       Field value
     * @param   string  $p_type        Field type
     * @param   int     $p_status      Record status
     * @param   string  $p_description Description
     * @param   int     $p_data_id
     *
     * @return  mixed  Last inserted identifier (integer) or false (boolean).
     * @version    Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function create($p_obj_id, $p_custom_id, $p_key, $p_value, $p_type, $p_status = C__RECORD_STATUS__NORMAL, $p_description = '', $p_data_id = 1)
    {
        if (!isset(self::$m_config[$p_custom_id]))
        {
            $this->set_config($p_custom_id);
        }

        $l_sql = "INSERT INTO isys_catg_custom_fields_list SET
            isys_catg_custom_fields_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ",
            isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($p_custom_id) . ",
            isys_catg_custom_fields_list__field_key = " . $this->convert_sql_text($p_key) . ",
            isys_catg_custom_fields_list__field_content = " . $this->convert_sql_text($p_value) . ",
            isys_catg_custom_fields_list__field_type = " . $this->convert_sql_text($p_type) . ",
            isys_catg_custom_fields_list__status = " . $this->convert_sql_int($p_status) . ",
            isys_catg_custom_fields_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_custom_fields_list__data__id = " . $this->convert_sql_id($p_data_id) . ";";

        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                $this->m_strLogbookSQL = $l_sql;

                return $this->get_last_insert_id();
            } // if
        } // if

        return false;
    } // function

    /**
     * Gets custom category identifier.
     *
     * @return  integer
     */
    public function get_catg_custom_id()
    {
        return $this->m_catg_custom_id;
    } // function

    /**
     * Sets custom category identifier.
     *
     * @param  int $p_custom_id Custom category identifier
     *
     * @return $this
     */
    public function set_catg_custom_id($p_custom_id)
    {
        $this->m_catg_custom_id = $p_custom_id;

        if (isset($this->m_cached_properties))
        {
            $this->unset_properties();
        } // if
        return $this;
    } // function

    /**
     * Fetches custom category configuration from database.
     *
     * @param int $p_custom_id Custom category identifier
     *
     * @return array
     */
    public function get_config($p_custom_id)
    {
        if ($p_custom_id > 0)
        {
            if (!isset(self::$m_config[$p_custom_id]))
            {
                $this->set_config($p_custom_id);
            } // if

            return self::$m_config[$p_custom_id];
        }
        else
        {
            return [];
        } // if
    } // function

    /**
     * Sets custom category configuration into member variable
     *
     * @param $p_custom_id
     *
     * @return mixed
     */
    public function set_config($p_custom_id)
    {
        $l_module = isys_custom_fields_dao::instance($this->m_db);

        return self::$m_config[$p_custom_id] = $l_module->get_config($p_custom_id);
    }

    /**
     * Fetches custom category info from db
     *
     * @param $p_custom_id
     *
     * @return mixed
     */
    public function get_category_info($p_custom_id)
    {
        return $this->m_category_config = (count($this->m_category_config) == 0) ? isys_custom_fields_dao::instance($this->m_db)
            ->get_data($p_custom_id)
            ->get_row() : $this->m_category_config;
    } // function

    /**
     * Fetches category data by key from database.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_custom_id
     * @param   string  $p_key
     * @param   integer $p_data_id
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_key($p_obj_id, $p_custom_id, $p_key, $p_data_id)
    {
        return $this->get_data(
            $p_data_id,
            $p_obj_id,
            'AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($p_key) . '
			AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_custom_id)
        );
    }

    /**
     * Updates an existing custom field.
     *
     * @param    integer $p_id          Category data identifier
     * @param    integer $p_custom_id   Custom category identifier
     * @param    string  $p_key         Field key
     * @param    string  $p_value       Field value
     * @param    string  $p_type        Field type
     * @param    integer $p_status      Record status
     * @param    string  $p_description Description. Default to ''.
     * @param    integer $p_data_id
     *
     * @return   boolean
     * @version  Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function save($p_id = null, $p_custom_id, $p_key, $p_value, $p_type, $p_status, $p_description = '', $p_data_id = null)
    {
        $l_return = false;

        try
        {
            if (!isset(self::$m_config[$p_custom_id]))
            {
                $this->set_config($p_custom_id);
            } // if

            if (self::$m_config[$p_custom_id][$p_key]['multiselection'] > 0)
            {
                $l_assigned_objects = array_flip($this->get_assigned_entries($p_key, $p_data_id, true));
                $l_value            = [];

                if (isys_format_json::is_json_array($p_value))
                {
                    $l_value = isys_format_json::decode($p_value);
                }
                elseif (is_array($p_value))
                {
                    $l_value = $p_value;
                } // if

                if (count($l_value))
                {
                    $l_main_obj_id = null;
                    foreach ($l_value AS $l_id)
                    {
                        if (isset($l_assigned_objects[$l_id]))
                        {
                            unset($l_assigned_objects[$l_id]);
                            $l_return = true;
                            continue;
                        } // if

                        $p_id     = $this->create($this->get_object_id(), $p_custom_id, null, null, null, $p_status, null, $p_data_id);
                        $l_return = $this->save_helper($p_id, $p_custom_id, $p_key, $l_id, $p_type, $p_status, $p_description, $p_data_id);
                        $p_id     = null;
                    } // foreach
                }
                else
                {
                    // Nothing to do
                    $l_return = true;
                } // if

                if (count($l_assigned_objects))
                {
                    foreach ($l_assigned_objects AS $l_obj_id => $l_entry_id)
                    {
                        $this->delete_entry($l_entry_id, 'isys_catg_custom_fields_list');
                    } // foreach
                } // if
            }
            else
            {
                $l_return = $this->save_helper($p_id, $p_custom_id, $p_key, $p_value, $p_type, $p_status, $p_description, $p_data_id);
            } // if
        }
        catch (ErrorException $e)
        {
            throw new Exception($e->getMessage());
        }

        return $l_return;
    } // function

    /**
     * Updates existing data in database given by HTTP POST.
     *
     * @param   integer $p_cat_level Category data identifier
     * @param   integer $p_status    Record status
     * @param   boolean $p_create    UNUSED
     *
     * @return  boolean Success?
     * @version    Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_list_id   = null;
        $l_config_id = ($this->get_catg_custom_id() ? $this->get_catg_custom_id() : $_GET[C__CMDB__GET__CATG_CUSTOM]);

        $this->set_config($l_config_id);
        $l_category_info = $this->get_category_info($l_config_id);

        if (isset($_GET[C__CMDB__GET__CATLEVEL]))
        {
            $l_list_id = $_GET[C__CMDB__GET__CATLEVEL];
        } // if

        if ($p_status === null)
        {
            $p_status = C__RECORD_STATUS__NORMAL;

            if ($l_list_id > 0)
            {
                $l_catdata = $this->get_data($l_list_id, $_GET[C__CMDB__GET__OBJECT])
                    ->get_row();
                if (is_array($l_catdata))
                {
                    $p_status = $l_catdata["isys_catg_custom_fields_list__status"];
                }
            } // if
        } // if

        $_POST["C__CATG__CUSTOM__C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_config_id]               = $_POST["C__CMDB__CAT__COMMENTARY_" .
        C__CMDB__CATEGORY__TYPE_CUSTOM . $l_config_id];
        self::$m_config[$l_config_id]["C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_config_id]["type"] = "commentary";

        foreach ($_POST as $l_key => $l_value)
        {
            if (($l_pos = strpos($l_key, "C__CATG__CUSTOM__")) === 0 && !empty($l_key))
            {
                // See ID-2713: Always reset the current ID, so that entries will not get overwritten.
                $l_id = 0;

                if (!empty($_POST[$l_key . '__HIDDEN']))
                {
                    continue;
                } // if

                $l_new_key = substr($l_key, 17, (strlen($l_key) - 17));

                if (strstr($l_new_key, "__VIEW"))
                {
                    continue;
                } // if

                if (strstr($l_new_key, "__HIDDEN"))
                {
                    $l_new_key = str_replace("__HIDDEN", "", $l_new_key);
                } // if

                if (!isset(self::$m_config[$l_config_id][$l_new_key]))
                {
                    continue;
                } // if

                if (self::$m_config[$l_config_id][$l_new_key]["type"] == 'html' || self::$m_config[$l_config_id][$l_new_key]["type"] == 'hr')
                {
                    continue;
                } // if

                if ($l_list_id === null && (int) $l_category_info['isysgui_catg_custom__list_multi_value'] === 1)
                {
                    $l_list_id = $this->get_data_id($l_config_id);
                } // if

                $l_data        = $this->get_data_by_key($_GET[C__CMDB__GET__OBJECT], $l_config_id, $l_new_key, $l_list_id);
                $l_entry_count = $l_data->num_rows();
                if ($l_entry_count > 0)
                {
                    $l_row     = $l_data->get_row();
                    $l_id      = ($l_entry_count >
                        1) ? null : $l_row["isys_catg_custom_fields_list__id"]; // ($l_entry_count > 1) Set null only if there are several entries (Multi Object Browser)
                    $l_list_id = $l_row['isys_catg_custom_fields_list__data__id'];
                }
                else
                {
                    if ($l_list_id === null && (int) $l_category_info['isysgui_catg_custom__list_multi_value'] === 0)
                    {
                        $l_list_id = $this->get_data_id_by_object_id($l_config_id, $_GET[C__CMDB__GET__OBJECT]);
                    } // if

                    if (!isset(self::$m_config[$l_config_id][$l_new_key]['multiselection']) && $l_value != '' && $l_new_key != '' || strpos(
                            $l_key,
                            'C__CATG__CUSTOM__C__CMDB__CAT__COMMENTARY_'
                        ) === 0
                    )
                    {
                        $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], $l_config_id, $l_new_key, null, null, $p_status, null, $l_list_id);
                    } // if
                } // if

                $this->save($l_id, $l_config_id, $l_new_key, $l_value, self::$m_config[$l_config_id][$l_new_key]["type"], $p_status, '', $l_list_id);
            } // if
        } // foreach

        return $l_list_id;
    } // function

    /**
     * Gets the next data id for the associated custom category.
     *
     * @param   integer $p_catg_custom_id
     * @param   integer $p_cat_id
     *
     * @return  integer
     * @author  Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function get_data_id($p_catg_custom_id = null, $p_cat_id = null)
    {
        if ($p_catg_custom_id === null)
        {
            $p_catg_custom_id = $this->get_catg_custom_id();
        } // if

        $l_return = 0;
        $l_sql    = 'SELECT MAX(DISTINCT(isys_catg_custom_fields_list__data__id)) AS last_id
			FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_catg_custom_id);

        if ($p_cat_id !== null)
        {
            $l_sql .= ' AND isys_catg_custom_fields_list__id = ' . $this->convert_sql_id($p_cat_id);
        } // if

        $l_res = $this->retrieve($l_sql);

        if (count($l_res))
        {
            $l_last_id = $l_res->get_row_value('last_id');
            $l_return  = ((is_null($p_cat_id)) ? $l_last_id + 1 : $l_last_id);
        } // if

        return (int) $l_return;
    } // function

    /**
     * Retrieve data id by category id and object id
     *
     * @param $p_catg_custom_id
     * @param $p_object_id
     *
     * @return int|mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_data_id_by_object_id($p_catg_custom_id, $p_object_id)
    {
        if ($p_catg_custom_id === null)
        {
            $p_catg_custom_id = $this->get_catg_custom_id();
        } // if

        $l_sql    = 'SELECT isys_catg_custom_fields_list__data__id
			FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_catg_custom_id) .
            ' AND isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        $l_data_id = $this->retrieve($l_sql)->get_row_value('isys_catg_custom_fields_list__data__id');

        if ($l_data_id)
        {
            return $l_data_id;
        }
        else
        {
            return $this->get_data_id($p_catg_custom_id);
        } // if
    } // function

    /**
     * Gets custom category title.
     *
     * @param   integer $p_custom_category_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_title($p_custom_category_id)
    {
        return $this->retrieve(
            'SELECT isysgui_catg_custom__title FROM isysgui_catg_custom WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id($p_custom_category_id) . ';'
        )
            ->get_row_value('isysgui_catg_custom__title');
    } // function

    /**
     * Compares category data which will be used in the import module
     *
     * @param      $p_category_data_values
     * @param      $p_object_category_dataset
     * @param      $p_used_properties
     * @param      $p_comparison
     * @param      $p_badness
     * @param      $p_mode
     * @param      $p_category_id
     * @param      $p_unit_key
     * @param      $p_category_data_ids
     * @param      $p_local_export
     * @param      $p_dataset_id_changed
     * @param      $p_dataset_id
     * @param      $p_logger
     * @param null $p_category_name
     * @param null $p_table
     * @param null $p_cat_multi
     * @param null $p_category_type_id
     * @param null $p_category_ids
     * @param null $p_object_ids
     */
    public function compare_category_data(&$p_category_data_values, &$p_object_category_dataset, &$p_used_properties, &$p_comparison, &$p_badness, &$p_mode, &$p_category_id, &$p_unit_key, &$p_category_data_ids, &$p_local_export, &$p_dataset_id_changed, &$p_dataset_id, &$p_logger, &$p_category_name = null, &$p_table = null, &$p_cat_multi = null, &$p_category_type_id = null, &$p_category_ids = null, &$p_object_ids = null, &$p_already_used_data_ids = null)
    {
        foreach ($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES] AS $l_key => $l_value)
        {

            if ($l_key != 'description')
            {
                $l_field_key = strchr($l_key, 'c_');
                $l_type      = substr($l_key, 0, strpos($l_key, '_c'));
            }
            else
            {
                $l_field_key = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CUSTOM_FIELDS;
                $l_type      = 'description';
            } // if

            if ($l_type == 'hr')
            {
                continue;
            }

            foreach ($p_object_category_dataset AS $l_dataset)
            {
                if ($l_dataset['isys_catg_custom_fields_list__field_key'] !== $l_field_key)
                {
                    continue;
                }

                if ($l_dataset['isys_catg_custom_fields_list__data__id'] !== $p_category_data_values['data_id'])
                {
                    continue;
                }

                $p_dataset_id = $l_dataset['isys_catg_custom_fields_list__data__id'];
                $l_local      = $l_dataset['isys_catg_custom_fields_list__field_content'];
                $l_import     = null;

                switch ($l_type)
                {
                    case 'f_popup':
                        if (isset($l_value['id']))
                        {
                            // Object-Browser
                            $l_import = $l_value['id'];
                        }
                        break;
                    default:
                        $l_import = $l_value[C__DATA__VALUE];
                        break;
                } // switch

                if ($l_local != $l_import)
                {
                    $p_badness[$p_dataset_id] += 1;
                } // if
            } // forach
        } // foreach

        if ($p_badness[$p_dataset_id] > isys_import_handler_cmdb::C__COMPARISON__THRESHOLD)
        {
            $p_logger->debug(
                'Dataset differs completly from category data.'
            );
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$p_dataset_id] = $p_dataset_id;
        } // @todo check badness again
        else
        {
            if ($p_badness[$p_dataset_id] == 0 /*|| ($p_dataset_id_changed && $p_badness[$p_dataset_id] == 1)*/)
            {
                $p_logger->debug(
                    'Dataset and category data are the same.'
                );
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$p_dataset_id] = $p_dataset_id;
            }
            else
            {
                $p_logger->debug(
                    'Dataset differs partly from category data.'
                );
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$p_dataset_id] = $p_dataset_id;
            }
        } //if
    } // function

    /**
     * Gets additional query condition to fetch data from database.
     *
     * @return  string
     */
    public function get_export_condition()
    {
        return " AND isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($this->m_catg_custom_id) . " ";
    } // function

    /**
     * Checks if an entry exists
     *
     * @param $p_obj_id
     * @param $p_key
     * @param $p_data_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function entry_exists($p_obj_id, $p_key, $p_data_id)
    {
        $l_sql = 'SELECT isys_catg_custom_fields_list__id
			FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($p_key) . '
			AND isys_catg_custom_fields_list__data__id = ' . $this->convert_sql_id($p_data_id);

        return ($this->retrieve($l_sql)
                ->num_rows() > 0);
    } // function

    /**
     *
     * @param   isys_array $p_array_reference
     * @param   integer    $p_record_status
     *
     * @return  isys_array
     */
    public function category_data(&$p_array_reference, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        // Retrieve category data.
        $l_catdata = $this->get_data_by_object($this->get_object_id(), null, $p_record_status);

        $l_rows = [];

        // Format category result.
        while ($l_row = $l_catdata->get_row())
        {
            if(!isset(self::$m_config[$l_row['isysgui_catg_custom__id']]))
            {
                self::$m_config[$l_row['isysgui_catg_custom__id']] = $this->get_config($l_row['isysgui_catg_custom__id']);
            } // if

            $l_value = $l_row['isys_catg_custom_fields_list__field_content'];

            if ($l_row['isys_catg_custom_fields_list__field_type'] == 'commentary')
            {
                $l_key = 'description';
            }
            else
            {
                $l_key = $l_row['isys_catg_custom_fields_list__field_type'] . '_' . $l_row['isys_catg_custom_fields_list__field_key'];
            } // if

            if ($l_row['isys_catg_custom_fields_list__field_type'] == 'f_popup' &&
                self::$m_config[$l_row['isysgui_catg_custom__id']][$l_row['isys_catg_custom_fields_list__field_key']]['popup'] == 'dialog_plus'
            )
            {
                $l_value = $l_row['isys_dialog_plus_custom__title'];
            } // if

            $l_rows[$l_row['isys_catg_custom_fields_list__data__id']][$l_key] = new isys_cmdb_dao_category_data_value(_L($l_value));
        } // while

        if (count($l_rows))
        {
            foreach ($l_rows as $l_id => $l_row)
            {
                $p_array_reference[$l_id] = $l_row;
            } // foreach
        } // if

        unset($l_rows);

        return $p_array_reference;
    } // function

    /**
     * @param string $p_table
     * @param null   $p_obj_id
     *
     * @return bool
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Return database field to be used as breadcrumb title. This is set to an empty string, so we receive the "#<id>" catlevel.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_breadcrumb_field()
    {
        return '';
    } // function

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  isys_exception_database
     * @author  Lenard Fischer <lfischer@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        $l_sql = 'SELECT COUNT(*) AS count
            FROM isys_catg_custom_fields_list
            WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($this->m_catg_custom_id);

        if ($p_obj_id !== null)
        {
            $l_sql .= ' AND isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } // if

        return (int) $this->retrieve($l_sql . ';')
            ->get_row_value('count');
    } // function

    /**
     * Fetches category data from database.
     *
     * @param   integer $p_catg_list_id Category data identifier. Defaults to null.
     * @param   integer $p_obj_id       Object identifier. Defaults to null.
     * @param   string  $p_condition    Query condition. Defaults to ''.
     * @param   mixed   $p_filter       Filter (string or array). Defaults to null.
     * @param   integer $p_status       Record status. Defaults to null.
     *
     * @return  isys_component_dao_result
     * @version    Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function get_data($p_catg_list_id = 1, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_custom_fields_list ' .
            'INNER JOIN isysgui_catg_custom ON isys_catg_custom_fields_list__isysgui_catg_custom__id = isysgui_catg_custom__id ' .
            'INNER JOIN isys_obj ON isys_catg_custom_fields_list__isys_obj__id = isys_obj__id ' .
            'LEFT JOIN isys_dialog_plus_custom ON isys_dialog_plus_custom__id = isys_catg_custom_fields_list__field_content ' . 'WHERE TRUE ' . $p_condition . ' ' .
            $this->prepare_filter(
                $p_filter
            );

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null && $p_catg_list_id > 0)
        {
            $l_sql .= " AND (isys_catg_custom_fields_list__data__id = '" . (int) $p_catg_list_id . "') ";
        } // if

        if ($p_catg_list_id === null && $p_obj_id === null)
        {
            $l_sql .= " AND FALSE ";
        } // if

        if (isset($this->m_catg_custom_id))
        {
            $l_sql .= " AND (isys_catg_custom_fields_list__isysgui_catg_custom__id = '" . (int) $this->m_catg_custom_id . "') ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_catg_custom_fields_list__status = '" . (int) $p_status . "') ";
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Simple wrapper of get_data()
     *
     * @param null   $p_category_data_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return array Category result as array
     */
    public function get_data_as_array($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        // Get result
        $l_res        = $this->get_data($p_category_data_id, $p_obj_id, $p_condition, $p_filter, $p_status);
        $l_catentries = [];

        if ($l_res->num_rows())
        {
            $i = $l_last_id = -1;

            while ($l_row = $l_res->get_row())
            {
                // Do we have a new entry
                if ($l_row['isys_catg_custom_fields_list__data__id'] != $l_last_id)
                {
                    $l_last_id = $l_row['isys_catg_custom_fields_list__data__id'];
                    $i++;
                } // if

                // Build field key
                $l_field_key = $l_row['isys_catg_custom_fields_list__field_type'] . '_' . $l_row['isys_catg_custom_fields_list__field_key'];

                $l_catentries[$i]
                [$l_field_key] = $l_row['isys_catg_custom_fields_list__field_content'];

                // Special-Handling for description field: Alias is not conform with other fields. It does not contain the type as alias.
                if (strpos(
                        $l_row['isys_catg_custom_fields_list__field_key'],
                        'C__CMDB__CAT__COMMENTARY_'
                    ) !== false
                )
                {
                    $l_catentries[$i][$l_row['isys_catg_custom_fields_list__field_key']] = $l_row['isys_catg_custom_field_list__fields_content'];
                } // if

                // Add object and category entry id
                $l_catentries[$i]['isys_catg_custom_fields_list__id']           = $l_row['isys_catg_custom_fields_list__data__id'];
                $l_catentries[$i]['isys_catg_custom_fields_list__isys_obj__id'] = $l_row['isys_catg_custom_fields_list__isys_obj__id'];
            } // while
        } // if

        return $l_catentries;
    } // function

    /**
     * Get entry identifier
     *
     * @author  Selcuk Kekec <skekec@i-doit.com>
     *
     * @param   array $p_entry_data
     *
     * @return  string
     */
    public function get_entry_identifier($p_entry_data)
    {
        try
        {
            if (isset($p_entry_data[0]) && isset($p_entry_data[0]['isys_catg_custom_fields_list__field_content']))
            {
                return $p_entry_data[0]['isys_catg_custom_fields_list__field_content'];
            }
            else
            {
                if (isset($p_entry_data['isys_catg_custom_fields_list__field_content']))
                {
                    return $p_entry_data[0]['isys_catg_custom_fields_list__field_content'];
                }
            }
        }
        catch (isys_exception_cmdb $e)
        {
        }

        return '';
    } // function

    public function get_list_id()
    {
        return (int) $this->m_list_id;
    } // function

    /**
     * @return isys_cmdb_ui_category_g_custom_fields
     */
    public function &get_ui()
    {
        global $g_comp_template;

        return new isys_cmdb_ui_category_g_custom_fields($g_comp_template);
    } // function

    /**
     * Checks if the custom category is a multivalued category
     *
     * @param null $p_custom_category_id
     *
     * @return bool
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function is_multivalued($p_custom_category_id = null)
    {
        $l_sql = 'SELECT isysgui_catg_custom__list_multi_value
			FROM isysgui_catg_custom
			WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id((!is_null($p_custom_category_id) ? $p_custom_category_id : $this->m_catg_custom_id));

        return (bool) $this->retrieve($l_sql)
            ->get_row_value('isysgui_catg_custom__list_multi_value');
    } // function

    /**
     * Method for returning the properties.
     *
     * @version  Van Quyen Hoang    <qhoang@i-doit.org>
     * @throws   isys_exception_dao
     * @return   array
     */
    protected function properties()
    {
        $l_return = [];

        if ($this->m_catg_custom_id > 0 && !is_null($this->m_catg_custom_id))
        {
            try
            {
                $l_config = $this->get_config($this->m_catg_custom_id);

                // Check category has fields
                if (is_array($l_config) && count($l_config) > 0)
                {
                    foreach ($l_config as $l_field_key => $l_field)
                    {
                        $l_tag = $l_field['type'] . '_' . $l_field_key;

                        $l_return[$l_tag] = array_replace_recursive(
                            isys_cmdb_dao_category_pattern::text(),
                            [
                                C__PROPERTY__INFO     => [
                                    C__PROPERTY__INFO__TITLE       => $l_field['title'],
                                    C__PROPERTY__INFO__DESCRIPTION => $l_field['type']
                                ],
                                C__PROPERTY__DATA     => [
                                    C__PROPERTY__DATA__TYPE        => C__TYPE__TEXT,
                                    C__PROPERTY__DATA__FIELD       => 'isys_catg_custom_fields_list__field_content',
                                    C__PROPERTY__DATA__FIELD_ALIAS => $l_tag
                                ],
                                C__PROPERTY__UI       => [
                                    C__PROPERTY__UI__ID     => $l_field_key,
                                    /** Validation bricks if type is not set to text or textarea, so we need to replace the f_ See ID-3016 */
                                    C__PROPERTY__UI__TYPE   => str_replace('f_', '', $l_field['type']),
                                    C__PROPERTY__UI__PARAMS => $l_field
                                ],
                                C__PROPERTY__PROVIDES => [
                                    C__PROPERTY__PROVIDES__REPORT => true,
                                    C__PROPERTY__PROVIDES__SEARCH => true,
                                ]
                            ]
                        );

                        if ($l_field['type'] == 'f_textarea')
                        {
                            $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]                          = C__PROPERTY__INFO__TYPE__TEXTAREA;
                            $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]                          = C__TYPE__TEXT_AREA;
                            $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]                              = C__PROPERTY__UI__TYPE__TEXTAREA;
                            $l_return[$l_tag][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['options'][1] = 'filter_textarea';
                            unset($l_return[$l_tag][C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION]);
                        } // if

                        if ($l_field['type'] == 'f_popup')
                        {
                            $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]           = C__TYPE__INT;
                            $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'isys_export_helper';
                            switch ($l_field['popup'])
                            {
                                case 'calendar':
                                    $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]           = C__TYPE__DATE;
                                    $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]           = C__PROPERTY__INFO__TYPE__DATETIME;
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]               = C__PROPERTY__UI__TYPE__DATETIME;
                                    $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'custom_category_property_calendar';
                                    break;

                                case 'browser_object':
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]                                                    = C__PROPERTY__UI__TYPE__POPUP;
                                    $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]                                                = C__PROPERTY__INFO__TYPE__OBJECT_BROWSER;
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType']                                = 'browser_object_ng';
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION] = !!$l_field['multiselection'];
                                    $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][]                                      = 'custom_category_property_object';
                                    if (isset($l_field['identifier']))
                                    {
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_identifier'] = $l_field['identifier'];
                                    }
                                    break;

                                default:
                                    $l_ui_params = [
                                        'p_arData'       => new isys_callback(
                                            [
                                                'isys_cmdb_dao_category_g_custom_fields',
                                                'callback_property_dialog_field'
                                            ], [
                                                $l_tag,
                                                $this->m_catg_custom_id
                                            ]
                                        ),
                                        'p_strPopupType' => 'dialog_plus',
                                        'p_identifier'   => $l_field['identifier']
                                    ];

                                    if ($l_field['multiselection'] > 0)
                                    {
                                        $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__MULTISELECT;
                                        $l_ui_params['multiselect']                                   = true;
                                        $l_ui_params['multiselection']                                = 1;
                                    }
                                    else
                                    {
                                        $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__DIALOG_PLUS;
                                    } // if

                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] = array_merge(
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS],
                                        $l_ui_params
                                    );

                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]    = C__PROPERTY__UI__TYPE__POPUP;
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT] = null;

                                    $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'custom_category_property_dialog_plus';

                                    $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] = [
                                        'isys_dialog_plus_custom',
                                        'isys_dialog_plus_custom__id'
                                    ];
                                    break;
                            } // switch
                        } // if

                        if ($l_field['type'] == 'html' || $l_field['type'] == 'script' || $l_field['type'] == 'hr')
                        {
                            $l_return[$l_tag][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] = true;
                        } // if
                    } // foreach
                } // if

                $l_return['description'] = array_replace_recursive(
                    isys_cmdb_dao_category_pattern::commentary(),
                    [
                        C__PROPERTY__INFO => [
                            C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                            C__PROPERTY__INFO__DESCRIPTION => 'Description'
                        ],
                        C__PROPERTY__DATA => [
                            C__PROPERTY__DATA__TYPE        => C__TYPE__TEXT_AREA,
                            C__PROPERTY__DATA__FIELD       => 'isys_catg_custom_fields_list__field_content',
                            C__PROPERTY__DATA__FIELD_ALIAS => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id
                        ],
                        C__PROPERTY__UI   => [
                            C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id
                        ]
                    ]
                );
            }
            catch (Exception $e)
            {
                throw new isys_exception_dao($e->getMessage());
            } // try
        } // if

        return $l_return;
    } // function

    /**
     * Rank records method
     *
     * @param int    $p_object
     * @param int    $p_direction
     * @param string $p_table
     * @param null   $p_checkMethod
     * @param bool   $p_purge
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rank_record($p_category_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        $l_custom_id = ($this->get_catg_custom_id()) ?: $this->get_category_id();

        $l_table         = 'isys_catg_custom_fields_list';
        $l_sql_condition = ' WHERE ' . $l_table . '__isysgui_catg_custom__id = ' . $l_custom_id . ' AND ' . $l_table . '__data__id = ' . $this->convert_sql_id($p_category_id);
        $l_sql_update    = '';
        $l_targetStatus  = null;

        /**
         * Get entry data
         */
        $l_strSQL = "SELECT * FROM " . $p_table . $l_sql_condition . ";";
        $l_row    = $this->retrieve($l_strSQL)
            ->__as_array();

        if ($this->m_multivalued === false && $p_purge === true && $p_direction === C__CMDB__RANK__DIRECTION_DELETE)
        {
            // For non multivalued custom categories
            $l_current_status = C__RECORD_STATUS__DELETED;
        }
        else
        {
            $l_sql            = 'SELECT isys_catg_custom_fields_list__status FROM isys_catg_custom_fields_list ' . $l_sql_condition . ' LIMIT 0,1';
            $l_current_status = ($p_purge === true && $p_direction === C__CMDB__RANK__DIRECTION_DELETE) ? C__RECORD_STATUS__DELETED : $this->retrieve($l_sql)
                ->get_row_value('isys_catg_custom_fields_list__status');
            $l_sql_update     = 'UPDATE isys_catg_custom_fields_list SET isys_catg_custom_fields_list__status = ';
        } // if

        /* Is there a corresponding entry for the given ID? */
        if ($l_current_status)
        {
            if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE)
            {
                switch ($l_current_status)
                {
                    case C__NAVMODE__QUICK_PURGE:
                    case C__RECORD_STATUS__DELETED:
                        /**
                         * @var $l_rel_dao isys_cmdb_dao_category_g_relation
                         */
                        $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

                        if ($l_rel_dao->has_relation_field('isys_catg_custom_fields_list'))
                        {
                            // get relation objects
                            $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id FROM ' . $l_table . ' ' .
                                'LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = ' . $l_table . '__isys_catg_relation_list__id ' . $l_sql_condition;
                            $l_res = $this->retrieve($l_sql);
                            while ($l_row = $l_res->get_row())
                            {
                                if (!empty($l_row['isys_catg_relation_list__isys_obj__id']))
                                {
                                    $l_rel_dao->delete_object_and_relations($l_row['isys_catg_relation_list__isys_obj__id']);
                                } // if
                            } // while
                        }
                        $l_sql_update = 'DELETE FROM ' . $l_table . $l_sql_condition;

                        $l_targetStatus = C__RECORD_STATUS__PURGE;
                        break;
                    case C__RECORD_STATUS__ARCHIVED:
                        $l_targetStatus = C__RECORD_STATUS__DELETED;
                        break;
                    case C__RECORD_STATUS__NORMAL:
                        $l_targetStatus = C__RECORD_STATUS__ARCHIVED;
                        break;
                } // switch
            }
            elseif ($p_direction == C__CMDB__RANK__DIRECTION_RECYCLE)
            {
                switch ($l_current_status)
                {
                    case C__RECORD_STATUS__ARCHIVED:
                        $l_targetStatus = C__RECORD_STATUS__NORMAL;
                        break;
                    case C__RECORD_STATUS__DELETED:
                        $l_targetStatus = C__RECORD_STATUS__ARCHIVED;
                        break;
                } // switch
            } // if

            if ($l_targetStatus != C__RECORD_STATUS__PURGE)
            {
                $l_sql_update .= $this->convert_sql_int($l_targetStatus) . ' ' . $l_sql_condition;
            }

            $l_entryTitle = null;
            if (method_exists($this, 'get_entry_identifier'))
            {
                $l_entryTitle = $this->get_entry_identifier($l_row);
            } // if

            /**
             * identify object id
             */
            if (isset($l_row[0][$this->m_table . '__isys_obj__id']))
            {
                $l_object_id = $l_row[0][$this->m_table . '__isys_obj__id'];
            }
            else
            {
                if (isset($l_row[$this->m_table . '__isys_obj__id']))
                {
                    $l_object_id = $l_row[$this->m_table . '__isys_obj__id'];
                }
                else
                {
                    $l_object_id = $this->get_object_id();
                }
            }

            /**
             * Emit mod.cmdb.beforeRankRecord before ranking the object/category entry
             */
            isys_component_signalcollection::get_instance()
                ->emit(
                    "mod.cmdb.beforeRankRecord",
                    $this,
                    $l_object_id,
                    $p_category_id,
                    $l_entryTitle,
                    $l_row,
                    $p_table,
                    $l_current_status,
                    $l_targetStatus,
                    $this->m_category_type_const,
                    $p_direction
                );

            return ($this->update($l_sql_update) && $this->apply_update());
        }

        /* $p_object is empty or category entry ID not found */

        return false;
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator   = true;
        $l_new_data_id = null;

        if (is_array($p_category_data) && count($p_category_data) > 0 && is_numeric($p_object_id) && $p_object_id >= 0 && is_numeric($this->m_catg_custom_id))
        {
            $this->set_config($this->m_catg_custom_id);
            $l_cache_obj_id = null;
            if ($this->get_object_id() != $p_object_id)
            {
                $l_cache_obj_id = $this->get_object_id();
                $this->set_object_id($p_object_id);
            } // if

            if ($p_status == isys_import_handler_cmdb::C__CREATE)
            {
                $l_new_data_id = $this->get_data_id($this->m_catg_custom_id);
            }
            else
            {
                $l_new_data_id = $p_category_data['data_id'];
            } // if

            /**
             * Handle description field separately
             *
             * Description field should always be created because reports always joins the description field at first for the data__id.
             *
             * @see ID-2504, ID-3117
             */
            $l_key = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id;
            if (!$this->entry_exists($p_object_id, $l_key, $l_new_data_id))
            {
                // Create the description entry
                $this->create(
                    $p_object_id,
                    $this->m_catg_custom_id,
                    $l_key,
                    isset($p_category_data['properties']['description'][C__DATA__VALUE]) ? $p_category_data['properties']['description'][C__DATA__VALUE] : '',
                    'commentary',
                    C__RECORD_STATUS__NORMAL,
                    null, // description
                    $l_new_data_id
                );
            }
            else
            {
                // Update description only if there is one given
                if (isset($p_category_data['properties']['description'][C__DATA__VALUE]))
                {
                    $this->save(
                        null,
                        $this->m_catg_custom_id,
                        $l_key,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        'commentary',
                        C__RECORD_STATUS__NORMAL,
                        null, // description
                        $l_new_data_id
                    );
                }
            } // if

            // Description was handled above, so remove it from properties that will be iterated next
            if (isset($p_category_data['properties']['description'])) unset($p_category_data['properties']['description']);

            // Get properties.
            $l_properties = $this->get_properties();

            // Iterate through the rest
            foreach ($p_category_data['properties'] as $l_tag => $l_property)
            {
                if (!isset($l_properties[$l_tag]))
                {
                    // This check prevents the creation of non category property entries.
                    continue;
                } // if

                $l_type           = null;
                $l_key            = null;
                $l_multiselection = (bool) $l_properties[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION];

                $l_key  = strrchr($l_tag, 'c_');
                $l_type = substr($l_tag, 0, strrpos($l_tag, '_c'));

                if (!$l_multiselection && !$this->entry_exists($p_object_id, $l_key, $l_new_data_id))
                {
                    $this->create(
                        $p_object_id,
                        $this->m_catg_custom_id,
                        $l_key,
                        $l_property[C__DATA__VALUE],
                        $l_type,
                        C__RECORD_STATUS__NORMAL,
                        null, // description
                        $l_new_data_id
                    );
                } // if

                // ID-2993 simply removing the "else" method, so that every freshly created row gets processed correctly.
                $l_status = $this->save(
                    null,
                    $this->m_catg_custom_id,
                    $l_key,
                    $l_property[C__DATA__VALUE],
                    $l_type,
                    C__RECORD_STATUS__NORMAL,
                    null, // description
                    $l_new_data_id
                );

                if ($l_status === false)
                {
                    return false;
                } // if
            } // foreach

            if ($l_cache_obj_id !== null)
            {
                $this->set_object_id($l_cache_obj_id);
            } // if
        } // if
        return ($l_indicator === true) ? $l_new_data_id : false;
    } // function

    /**
     * Retrieve all assigne objects for the multi object browser
     *
     * @param $p_field_key
     * @param $p_data_id
     *
     * @return array
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_entries($p_field_key, $p_data_id, $p_with_keys = false)
    {
        $l_sql = 'SELECT isys_catg_custom_fields_list__field_content, isys_catg_custom_fields_list__id FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' .
            $this->convert_sql_id(
                $this->get_catg_custom_id()
            ) . ' AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text(
                $p_field_key
            ) . ' AND isys_catg_custom_fields_list__data__id = ' . $this->convert_sql_id($p_data_id);

        $l_res    = $this->retrieve($l_sql);
        $l_return = [];
        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                if ($p_with_keys)
                {
                    $l_return[$l_row['isys_catg_custom_fields_list__id']] = $l_row['isys_catg_custom_fields_list__field_content'];
                }
                else
                {
                    $l_return[] = $l_row['isys_catg_custom_fields_list__field_content'];
                } // if
            } // while
        } // if
        return $l_return;
    } // function

    /**
     * Save helper method
     *
     * @param null   $p_id
     * @param        $p_custom_id
     * @param        $p_key
     * @param        $p_value
     * @param        $p_type
     * @param        $p_status
     * @param string $p_description
     * @param null   $p_data_id
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function save_helper($p_id = null, $p_custom_id, $p_key, $p_value, $p_type, $p_status, $p_description = '', $p_data_id = null)
    {
        /**
         * @var $l_dao_relation isys_cmdb_dao_category_g_relation
         */
        $l_dao_relation       = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);
        $l_relation_type_data = [];
        $l_return             = false;

        // Relation Object browser
        if (self::$m_config[$p_custom_id][$p_key]['popup'] == 'browser_object' && isset(self::$m_config[$p_custom_id][$p_key]['identifier']))
        {
            $l_sql_relation = "SELECT isys_catg_custom_fields_list__isys_obj__id, isys_catg_custom_fields_list__id, isys_catg_custom_fields_list__isys_catg_relation_list__id FROM isys_catg_custom_fields_list ";
            if (!isset($this->m_relation_type_data[self::$m_config[$p_custom_id][$p_key]['identifier']]))
            {
                $l_relation_type_identifier = self::$m_config[$p_custom_id][$p_key]['identifier'];
                $l_relation_type_data       = $l_dao_relation->get_relation_type($l_relation_type_identifier, null, true);
            }

            if (count($l_relation_type_data) == 0)
            {
                unset($l_sql_relation);
            } //
        } // if

        if (!empty($p_id))
        {
            $l_sql_condition = "WHERE isys_catg_custom_fields_list__id = " . $this->convert_sql_id($p_id) . ";";
        }
        else
        {
            $l_sql_condition = "WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id(
                    $p_custom_id
                ) . " " . "AND isys_catg_custom_fields_list__field_key = " . $this->convert_sql_text(
                    $p_key
                ) . " " . "AND isys_catg_custom_fields_list__data__id = " . $this->convert_sql_id($p_data_id);
        } // if

        $l_obj_id = $l_cat_id = $l_relation_id = null;
        if (isset($l_sql_relation))
        {
            $l_sql_relation .= $l_sql_condition;
            $l_row         = $this->retrieve($l_sql_relation)
                ->get_row();
            $l_relation_id = $l_row['isys_catg_custom_fields_list__isys_catg_relation_list__id'];
            $l_cat_id      = $l_row['isys_catg_custom_fields_list__id'];
            $l_obj_id      = $l_row['isys_catg_custom_fields_list__isys_obj__id'];
        } // if

        $l_sql = "UPDATE isys_catg_custom_fields_list
			SET isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($p_custom_id) . ",
			isys_catg_custom_fields_list__field_key = " . $this->convert_sql_text($p_key) . ",
			isys_catg_custom_fields_list__field_content = " . $this->convert_sql_text($p_value) . ",
			isys_catg_custom_fields_list__field_type = " . $this->convert_sql_text($p_type) . ",
			isys_catg_custom_fields_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_custom_fields_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_custom_fields_list__data__id = " . $this->convert_sql_id($p_data_id) . "
			";

        $l_sql .= $l_sql_condition;

        if ($this->update($l_sql))
        {
            $this->m_strLogbookSQL = $l_sql;
            if ($l_return = $this->apply_update())
            {
                if (isset($l_sql_relation) && count($l_relation_type_data))
                {
                    if ($l_relation_type_data['isys_relation_type__default'] == C__RELATION_DIRECTION__I_DEPEND_ON)
                    {
                        $l_master = $p_value;
                        $l_slave  = $l_obj_id;
                    }
                    else
                    {
                        $l_master = $l_obj_id;
                        $l_slave  = $p_value;
                    } // if

                    $l_dao_relation->handle_relation(
                        $l_cat_id,
                        'isys_catg_custom_fields_list',
                        $l_relation_type_data['isys_relation_type__id'],
                        $l_relation_id,
                        $l_master,
                        $l_slave
                    );
                } // if
            } // if
        } // if
        return $l_return;
    } // function
} // class