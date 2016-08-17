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
 * DAO: Global category CPU.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_cpu extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cpu';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the formatted CPU data.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_frequency($p_row)
    {
        global $g_comp_database;

        $l_cpu_res = [];
        $l_object_id = isys_glob_which_isset($p_row['isys_obj__id'], $p_row['__obj_id__'], $p_row['__id__']);

        if (isset($p_row['isys_catg_cpu_list__id']) && $p_row['isys_catg_cpu_list__id'] > 0)
        {
            $l_cpu_res = isys_cmdb_dao_category_g_cpu::instance($g_comp_database)
                ->get_data($p_row['isys_catg_cpu_list__id']);
        }
        elseif ($l_object_id > 0)
        {
            $l_cpu_res = isys_cmdb_dao_category_g_cpu::instance($g_comp_database)
                ->get_data(null, $l_object_id);
        } // if

        if (count($l_cpu_res) > 0)
        {
            $l_return = [];

            while ($l_cpu_row = $l_cpu_res->get_row())
            {
                $l_core_prefix = '';

                if ($l_cpu_row['isys_catg_cpu_list__cores'] > 1 && !isset($p_row['isys_catg_cpu_list__id']))
                {
                    $l_core_prefix = $l_cpu_row['isys_catg_cpu_list__cores'] . '@ ';
                } // if

                $l_return[] = $l_core_prefix . isys_convert::frequency(
                        $l_cpu_row['isys_catg_cpu_list__frequency'],
                        $l_cpu_row['isys_frequency_unit__const'],
                        C__CONVERT_DIRECTION__BACKWARD
                    ) . ' ' . $l_cpu_row['isys_frequency_unit__title'];
            } // while

            // The "PHP_EOL" is necessary for reports (CSV).
            return '<ul><li>' . implode('</li>' . PHP_EOL . '<li>', $l_return) . '</li></ul>';
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Import-Handler for this category.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        $l_ids = [];

        // Prepare _POST variable(s).
        if (is_numeric($p_data["NumberOfProcessors"]) && $p_data["NumberOfProcessors"] > 0)
        {
            $_POST["C__CATG__CPU_NUMBER2CREATE"] = $p_data["NumberOfProcessors"];
        }
        else
        {
            $_POST["C__CATG__CPU_NUMBER2CREATE"] = 1;
        } // if

        if (count($p_data) > 0)
        {
            // Iterate through CPUs.
            foreach ($p_data as $l_cpu)
            {
                // Save / Create.
                $l_status = -1;

                // Cat-New: 0, Cat-Save: ?.
                $l_cat = -1;

                // Prepare additional _POST variables.
                if ($l_cpu["speed"] > 100)
                {
                    $l_cpu["speed"] = number_format($l_cpu["speed"] / 1000, 2);
                    $l_cpu_unit     = 'GHz';
                }
                else
                {
                    $l_cpu_unit = 'MHz';
                }

                /* !empty() checks are done inside isys_import::check_dialog, so they are not needed here */
                $_POST["C__CATG__CPU_MANUFACTURER"] = isys_import::check_dialog("isys_catg_cpu_manufacturer", $l_cpu["manufacturer"]);
                $_POST["C__CATG__CPU_FREQUENCY"]    = $l_cpu["speed"];
                $_POST["C__CATG__CPU_TYPE"]         = isys_import::check_dialog("isys_catg_cpu_type", $l_cpu["description"]);
                $_POST['C__CATG__CPU_TITLE']        = $l_cpu["name"];

                /* Core detection */
                if (stristr($l_cpu["name"], "dual"))
                {
                    $_POST['C__CATG__CPU_CORES'] = 2;
                }
                else if (stristr($l_cpu["name"], "quad"))
                {
                    $_POST['C__CATG__CPU_CORES'] = 4;
                }
                else
                {
                    $_POST['C__CATG__CPU_CORES'] = 1;
                }

                $l_ids[] = $this->save_element($l_cat, $l_status, true);
            }
        }

        return $l_ids;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param    integer $p_cat_level
     * @param    integer $p_newRecStatus
     * @param    string  $p_title
     * @param    string  $p_manufacturerID
     * @param    integer $p_frequency
     * @param    integer $p_typeID
     * @param    string  $p_description
     * @param    integer $p_cores
     * @param    integer $p_frequency_unit
     *
     * @return   mixed
     * @author   Dennis Bluemer <dbluemer@i-doit.org>
     * @version  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_manufacturerID, $p_frequency, $p_typeID, $p_description, $p_cores = null, $p_frequency_unit = null)
    {
        if (!isset($p_frequency_unit))
        {
            $p_frequency_unit = C__FREQUENCY_UNIT__GHZ;
        } // if

        $l_strSql = "UPDATE isys_catg_cpu_list SET
			isys_catg_cpu_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_cpu_list__isys_catg_cpu_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_catg_cpu_list__frequency  = " . $this->convert_sql_text(isys_convert::frequency($p_frequency, $p_frequency_unit)) . ",
			isys_catg_cpu_list__isys_catg_cpu_type__id = " . $this->convert_sql_id($p_typeID) . ",
			isys_catg_cpu_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_cpu_list__cores = " . $this->convert_sql_int($p_cores) . ",
			isys_catg_cpu_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_cpu_list__isys_frequency_unit__id = " . $this->convert_sql_id($p_frequency_unit) . "
			WHERE isys_catg_cpu_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        if ($this->update($l_strSql) && $this->apply_update())
        {
            return true;
        }

        return false;
    } // function

    /**
     * Save element method.
     *
     * @param    integer $p_cat_level
     * @param    integer $p_intOldRecStatus
     * @param    boolean $p_create
     *
     * @return   mixed  Integer with last inserted ID or boolean false.
     * @version  Niclas Potthast <npotthast@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == C__CATG__OVERVIEW && isys_glob_get_param(
                C__GET__NAVMODE
            ) == C__NAVMODE__SAVE
        )
        {
            $p_create                            = true;
            $_POST["C__CATG__CPU_NUMBER2CREATE"] = 1;
        } // if

        $l_bRet                          = $l_id = null;
        $_POST['C__CATG__CPU_FREQUENCY'] = isys_helper::filter_number($_POST['C__CATG__CPU_FREQUENCY']);

        $l_catdata         = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_catg_cpu_list__status"];

        if ($p_create)
        {
            // Overview page and no input was given.
            if (isys_glob_get_param(
                    C__CMDB__GET__CATG
                ) == C__CATG__OVERVIEW && $_POST['C__CATG__CPU_TITLE'] == "" && $_POST['C__CATG__CPU_MANUFACTURER'] == -1 && $_POST['C__CATG__CPU_FREQUENCY'] == "0" && $_POST['C__CATG__CPU_TYPE'] == -1
            )
            {
                return null;
            } // if

            $l_nQuantity = $_POST["C__CATG__CPU_NUMBER2CREATE"];

            if ($l_nQuantity > 0)
            {
                for ($i = 1;$i <= $l_nQuantity;$i++)
                {
                    $l_id = $this->create(
                        $_GET[C__CMDB__GET__OBJECT],
                        C__RECORD_STATUS__NORMAL,
                        $_POST['C__CATG__CPU_TITLE'],
                        $_POST['C__CATG__CPU_MANUFACTURER'],
                        $_POST['C__CATG__CPU_FREQUENCY'],
                        $_POST['C__CATG__CPU_TYPE'],
                        $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                        $_POST['C__CATG__CPU_CORES'],
                        $_POST['C__CATG__CPU_FREQUENCY_UNIT']
                    );

                    if ($l_id != false)
                    {
                        $this->m_strLogbookSQL = $this->get_last_query();
                    } // if
                } // for

                $p_cat_level = null;

                return $l_id;
            } // if
        }
        else
        {
            $l_bRet = $this->save(
                $l_catdata['isys_catg_cpu_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__CPU_TITLE'],
                $_POST['C__CATG__CPU_MANUFACTURER'],
                $_POST['C__CATG__CPU_FREQUENCY'],
                $_POST['C__CATG__CPU_TYPE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__CPU_CORES'],
                $_POST['C__CATG__CPU_FREQUENCY_UNIT']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet;
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_obj__id $p_objID.
     *
     * @param    integer $p_objID
     * @param    integer $p_newRecStatus
     * @param    string  $p_title
     * @param    integer $p_manufacturerID
     * @param    integer $p_frequency
     * @param    integer $p_typeID
     * @param    string  $p_description
     * @param    integer $p_cores
     * @param    integer $p_frequency_unit
     *
     * @return   mixed    The newly created ID as integer or boolean false.
     * @author   Dennis Bluemer <dbluemer@i-doit.org>
     * @version  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_manufacturerID, $p_frequency, $p_typeID, $p_description, $p_cores = null, $p_frequency_unit = null)
    {
        if (!isset($p_frequency_unit))
        {
            $p_frequency_unit = C__FREQUENCY_UNIT__GHZ;
        } // if

        $l_strSql = "INSERT INTO isys_catg_cpu_list SET
			isys_catg_cpu_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_cpu_list__isys_catg_cpu_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_catg_cpu_list__frequency  = " . $this->convert_sql_text(isys_convert::frequency($p_frequency, $p_frequency_unit)) . ",
			isys_catg_cpu_list__isys_catg_cpu_type__id = " . $this->convert_sql_id($p_typeID) . ",
			isys_catg_cpu_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_cpu_list__cores = " . $this->convert_sql_int($p_cores) . ",
			isys_catg_cpu_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_cpu_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
			isys_catg_cpu_list__isys_frequency_unit__id = " . $this->convert_sql_id($p_frequency_unit) . ";";

        if ($this->update($l_strSql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['manufacturer']))
        {
            $l_manufacturer = isys_import_handler::check_dialog('isys_catg_cpu_manufacturer', $p_data['manufacturer']);
        }
        else
        {
            $l_manufacturer = null;
        } // if

        if (!empty($p_data['type']))
        {
            $l_type = isys_import_handler::check_dialog('isys_catg_cpu_type', $p_data['type']);
        }
        else
        {
            $l_type = null;
        } // if

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'          => [
                    'value' => $p_data['title']
                ],
                'manufacturer'   => [
                    'value' => $l_manufacturer
                ],
                'type'           => [
                    'value' => $l_type
                ],
                'frequency'      => [
                    'value' => $p_data['frequency']
                ],
                'frequency_unit' => [
                    'value' => $p_data['frequency_unit']
                ],
                'cores'          => [
                    'value' => $p_data['cores']
                ],
                'description'    => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    } // function

    /**
     * Compares category data for import.
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     */
    public function compare_category_data(&$p_category_data_values, &$p_object_category_dataset, &$p_used_properties, &$p_comparison, &$p_badness, &$p_mode, &$p_category_id, &$p_unit_key, &$p_category_data_ids, &$p_local_export, &$p_dataset_id_changed, &$p_dataset_id, &$p_logger, &$p_category_name = null, &$p_table = null, &$p_cat_multi = null, &$p_category_type_id = null, &$p_category_ids = null, &$p_object_ids = null, &$p_already_used_data_ids = null)
    {

        $l_title     = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'] ? $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'] : $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['type']['title_lang'];
        $l_type      = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['type']['value'];
        $l_frequency = strval($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['frequency']['value_converted']);
        if ($l_frequency === '')
        {
            $l_frequency = '0';
        } // if

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
        {
            $p_dataset_id_changed = false;
            $p_dataset_id         = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id]))
            {
                // ID has already been used skip entry.
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                continue;
            } // if

            // Test the category data identifier:
            if ($p_category_data_values['data_id'] !== null)
            {
                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id)
                {
                    //$p_logger->debug('Category data identifier is different.');
                    $p_badness[$p_dataset_id]++;
                    $p_dataset_id_changed = true;
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS)
                    {
                        continue;
                    } // if
                } // if
            }

            // converted frequency of the local and imported frequency
            $l_frequency_local  = ceil(
                isys_convert::frequency(
                    $l_dataset['isys_catg_cpu_list__frequency'],
                    $l_dataset['isys_catg_cpu_list__isys_frequency_unit__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) * 10
            );
            $l_frequency_import = ceil(isys_convert::frequency($l_frequency, $l_dataset['isys_catg_cpu_list__isys_frequency_unit__id'], C__CONVERT_DIRECTION__BACKWARD) * 10);

            if ($l_dataset['isys_catg_cpu_list__title'] === $l_title && $l_dataset['isys_catg_cpu_list__isys_catg_cpu_type__id'] === $l_type && $l_frequency_local === $l_frequency_import)
            {
                // Check properties
                // We found our dataset
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            }
            elseif (strtolower($l_dataset['isys_catg_cpu_list__title']) === $l_title && $l_dataset['isys_catg_cpu_list__frequency'] !== $l_frequency)
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            }
            else
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            } // if
        } // foreach
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_frequency' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU',
                    C__PROPERTY__INFO__DESCRIPTION => 'CPU'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_frequency'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]
        ];
    } // function

    /**
     * Get data method for retrieving data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cpu_list
			INNER JOIN isys_obj ON isys_catg_cpu_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_catg_cpu_manufacturer ON isys_catg_cpu_manufacturer__id = isys_catg_cpu_list__isys_catg_cpu_manufacturer__id
			LEFT JOIN isys_catg_cpu_frequency ON isys_catg_cpu_frequency__id = isys_catg_cpu_list__isys_catg_cpu_frequency__id
			LEFT JOIN isys_catg_cpu_type ON isys_catg_cpu_type__id = isys_catg_cpu_list__isys_catg_cpu_type__id
			LEFT JOIN isys_frequency_unit ON isys_catg_cpu_list__isys_frequency_unit__id = isys_frequency_unit__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND isys_catg_cpu_list__id = " . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_cpu_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND isys_catg_cpu_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id);
            }
            else
            {
                $l_sql = ' AND isys_catg_cpu_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
            } // if
        } // if

        return $l_sql;
    } // function

    /**
     * Create logbook entry for each cpu.
     *
     * @param  string $p_strConst
     * @param  string $p_lc_category
     * @param  array  $p_changes
     */
    public function logbook_update($p_strConst, $p_lc_category, $p_changes)
    {
        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_count             = (int) $_POST['C__CATG__CPU_NUMBER2CREATE'];

        if ($l_count > 1)
        {
            for ($i = 1;$i <= $l_count;$i++)
            {
                $l_mod_event_manager->triggerCMDBEvent(
                    $p_strConst,
                    $this->get_strLogbookSQL(),
                    $_GET[C__CMDB__GET__OBJECT],
                    $_GET[C__CMDB__GET__OBJECTTYPE],
                    $p_lc_category,
                    $p_changes,
                    $_POST["LogbookCommentary"]
                );
            } // for
        }
        else
        {
            $l_mod_event_manager->triggerCMDBEvent(
                $p_strConst,
                $this->get_strLogbookSQL(),
                $_GET[C__CMDB__GET__OBJECT],
                $_GET[C__CMDB__GET__OBJECTTYPE],
                $p_lc_category,
                $p_changes,
                $_POST["LogbookCommentary"]
            );
        } // if
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'title'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CPU_TITLE'
                    ]
                ]
            ),
            'manufacturer'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONTROLLER_MANUFACTURER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cpu_list__isys_catg_cpu_manufacturer__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_cpu_manufacturer',
                            'isys_catg_cpu_manufacturer__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CPU_MANUFACTURER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_cpu_manufacturer'
                        ]
                    ]
                ]
            ),
            'type'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cpu_list__isys_catg_cpu_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_cpu_type',
                            'isys_catg_cpu_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CPU_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_cpu_type'
                        ]
                    ]
                ]
            ),
            'frequency'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FREQUENCY',
                        C__PROPERTY__INFO__DESCRIPTION => 'CPU frequency'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__frequency'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CPU_FREQUENCY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_cpu_frequency',
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['frequency']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'frequency_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'frequency_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_FREQUENCY_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'frequency unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cpu_list__isys_frequency_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_frequency_unit',
                            'isys_frequency_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CPU_FREQUENCY_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_frequency_unit',
                            'p_bInfoIconSpacer' => 0,
                            'p_bDbFieldNN'      => 0,
                            'p_strClass'        => 'input-dual-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'cores'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_CORES',
                        C__PROPERTY__INFO__DESCRIPTION => 'CPU cores'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__cores',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__CPU_CORES',
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'description'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CPU
                    ]
                ]
            )
        ];
    } // function

    /**
     * Sync method for import, export and duplicating.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['frequency'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['cores'][C__DATA__VALUE],
                            $p_category_data['properties']['frequency_unit'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['frequency'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['cores'][C__DATA__VALUE],
                            $p_category_data['properties']['frequency_unit'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        } // if
        return false;
    } // function

} // class
