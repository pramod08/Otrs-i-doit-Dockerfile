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
 * DAO: global category for physical network interfaces
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Niclas Potthast <npotthast@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_network_interface extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'network_interface';
    /**
     * Category's constant
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P';
    /**
     * Category's identifier
     *
     * @var int
     *
     * @fixme No standard behavior!
     */
    protected $m_category_id = C__CMDB__SUBCAT__NETWORK_INTERFACE_P;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;
    /**
     * Main table where properties are stored persistently
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_table = 'isys_catg_netp_list';
    /**
     * Category Template
     */
    protected $m_tpl = 'catg__interface_p.tpl';
    /**
     * Category's user interface
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_ui = 'isys_cmdb_ui_category_g_network';

    public function get_interface_by_title($p_title, $p_obj_id)
    {
        return $this->get_data(null, $p_obj_id, "AND (isys_catg_netp_list__title = '{$p_title}')");
    }

    /**
     * @param int  $p_cat_level
     * @param int  $p_intOldRecStatus
     * @param null $p_id
     *
     * @return int|null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) > 0)
        {
            $l_catdata = $this->get_data($_GET[C__CMDB__GET__CATLEVEL])
                ->__to_array();
        }
        else
        {
            $l_catdata = $this->get_result()
                ->__to_array();
        }

        if (!empty($p_id) && $p_id > 0)
        {
            $l_catdata['isys_catg_netp_list__id'] = $p_id;
        }

        if ($p_create || (!isset($l_catdata['isys_catg_netp_list__id']) || !$l_catdata['isys_catg_netp_list__id']))
        {
            $l_catdata['isys_catg_netp_list__id'] = $this->create_connector($this->m_table, $this->m_object_id);
        }

        if ($l_catdata['isys_catg_netp_list__id'])
        {

            if ($this->save(
                $l_catdata['isys_catg_netp_list__id'],
                $_POST['C__CATG__INTERFACE_P_TITLE'],
                $_POST['C__CATG__INTERFACE_P_MANUFACTURER'],
                $_POST['C__CATG__INTERFACE_P_MODEL'],
                $_POST['C__CATG__INTERFACE_P_SERIAL'],
                $_POST['C__CATG__INTERFACE_P_SLOTNUMBER'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            )
            )
            {

                $this->m_strLogbookSQL = $this->get_last_query();

                return null;
            }

        }

        return -1;
    }

    /**
     * Create method
     *
     * @param int   $p_obj_id
     * @param       $p_title
     * @param       $p_manufacturer
     * @param       $p_model
     * @param       $p_serial
     * @param       $p_slot
     * @param       $p_description
     * @param int   $p_status
     *
     * @return bool|int|mixed
     * @throws isys_exception_dao
     */
    public function create($p_obj_id, $p_title, $p_manufacturer, $p_model, $p_serial, $p_slot, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "INSERT INTO isys_catg_netp_list (
			isys_catg_netp_list__isys_obj__id,
			isys_catg_netp_list__title,
			isys_catg_netp_list__description,
			isys_catg_netp_list__isys_iface_manufacturer__id,
			isys_catg_netp_list__isys_iface_model__id,
			isys_catg_netp_list__serial,
			isys_catg_netp_list__slotnumber,
			isys_catg_netp_list__status
			) VALUES (" . $this->convert_sql_id($p_obj_id) . ", " . $this->convert_sql_text($p_title) . ", " . $this->convert_sql_text(
                $p_description
            ) . ", " . $this->convert_sql_id($p_manufacturer) . ", " . $this->convert_sql_id($p_model) . ", " . $this->convert_sql_text(
                $p_serial
            ) . ", " . $this->convert_sql_text($p_slot) . ", " . $this->convert_sql_int($p_status) . ");";

        if ($this->update($l_strSql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Save interface
     *
     * @param int    $p_id
     * @param string $p_title
     * @param int    $p_manufacturer
     * @param int    $p_model
     * @param int    $p_serial
     * @param int    $p_slot
     * @param string $p_description
     * @param int    $p_status
     *
     * @return boolean
     */
    public function save($p_id, $p_title, $p_manufacturer, $p_model, $p_serial, $p_slot, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {

        $l_strSql = "UPDATE " . "isys_catg_netp_list " . "SET " . "isys_catg_netp_list__title = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_netp_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_netp_list__isys_iface_manufacturer__id = " . $this->convert_sql_id(
                $p_manufacturer
            ) . ", " . "isys_catg_netp_list__isys_iface_model__id = " . $this->convert_sql_id(
                $p_model
            ) . ", " . "isys_catg_netp_list__serial = " . $this->convert_sql_text(
                $p_serial
            ) . ", " . "isys_catg_netp_list__slotnumber = " . $this->convert_sql_text(
                $p_slot
            ) . ", " . "isys_catg_netp_list__status = " . "" . $this->convert_sql_id($p_status) . " " . "WHERE " . "isys_catg_netp_list__id = '" . $p_id . "'" . ";";

        return $this->update($l_strSql) && $this->apply_update();
    }

    /**
     * @return integer
     *
     * @param $p_cat_level level to save, standard 0
     *                     (usage by reason of universality)
     * @param &$p_new_id   returns the __id of the new record
     *
     * @version Niclas Potthast <npotthjast@i-doit.org> - 2006-03-03
     * @desc    save global category netp element, return NULL
     */
    public function attachObjects(array $p_post)
    {
        // no success = -1
        $p_new_id     = -1;
        $l_intRetCode = 3;

        $l_object_id = (!empty($p_object_id)) ? $p_object_id : $_GET[C__CMDB__GET__OBJECT];

        $l_strSql = "INSERT INTO " . "isys_catg_netp_list " . "SET " . "isys_catg_netp_list__isys_obj__id = '" . $l_object_id . "', " . "isys_catg_netp_list__title = '', " . "isys_catg_netp_list__status = '" . C__RECORD_STATUS__BIRTH . "' " . ";";

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $l_intRetCode = ISYS_NULL;
            $p_new_id     = $this->get_last_insert_id();
        }

        return (!empty($p_object_id)) ? $p_new_id : $l_intRetCode;
    }

    /**
     * Import-Handler
     *
     * @author Dennis Stuecken <dstuecken@syneics.de>
     */
    public function import($p_data, $p_object_id)
    {
        $l_status  = -1;
        $l_cat     = -1;
        $l_list_id = null;

        if (is_array($p_data))
        {
            foreach ($p_data as $l_key => $l_data)
            {

                $l_list_id = $this->create_connector($this->get_table(), $p_object_id);

                if ($l_list_id > 0)
                {
                    $_POST['C__CATG__INTERFACE_P_TITLE']        = $l_data["name"];
                    $_POST['C__CATG__INTERFACE_P_SLOTNUMBER']   = $l_key;
                    $_POST['C__CATG__INTERFACE_P_MANUFACTURER'] = isys_import::check_dialog("isys_iface_manufacturer", $l_data["manufacturer"]);

                    $this->save_element($l_cat, $l_status, $l_list_id);
                }

                // Create port and port categories with information for ips
                $l_catg_dao = new isys_cmdb_dao_category_g_network_port($this->m_db);

                $l_catg_dao->init($this->get_result());
                $_POST["C__CATG__PORT__INTERFACE"] = $l_list_id;

                $l_catg_dao->import($l_data, $p_object_id);
            }
        }

        return $l_list_id;
    }

    /**
     * Builds an array with minimal requirement for the sync function
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {

        if (!empty($p_data['manufacturer'])) $l_manufacturer = isys_import_handler::check_dialog('isys_iface_manufacturer', $p_data['manufacturer']);
        else $l_manufacturer = null;

        if (!empty($p_data['model'])) $l_model = isys_import_handler::check_dialog('isys_iface_model', $p_data['model']);
        else $l_model = null;

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title']
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'type'         => [
                    'value' => $l_model
                ],
                'serial'       => [
                    'value' => $p_data['serial']
                ],
                'slot'         => [
                    'value' => $p_data['slot']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ]
            ]
        ];

    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param   integer $p_context
     * @param   array   $p_parameters
     *
     * @return  string  A JSON Encoded array with all the contents of the second list.
     * @return  array   A PHP Array with the preselections for category, first- and second list.
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function object_browser($p_context, array $p_parameters)
    {

        switch ($p_context)
        {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_obj     = isys_cmdb_dao_category_g_network_interface::instance($this->m_db);
                $l_objects = $l_obj->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

                if ($l_objects->num_rows() > 0)
                {
                    while ($l_row = $l_objects->get_row())
                    {
                        $l_return[] = [
                            '__checkbox__'                                                             => $l_row["isys_catg_netp_list__id"],
                            isys_glob_utf8_encode(_L('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE')) => isys_glob_utf8_encode($l_row["isys_catg_netp_list__title"])
                        ];
                    } // while
                } // if

                return json_encode($l_return);
                break;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $p_preselection = $p_parameters['preselection'];

                // When we get a JSON string, we modify it to an comma separated list.
                if (isys_format_json::is_json($p_preselection))
                {
                    $p_preselection = implode(',', isys_format_json::decode($p_preselection, true));
                }

                if (!empty($p_preselection) && is_string($p_preselection))
                {
                    $l_sql = "SELECT isys_obj__isys_obj_type__id, isys_catg_netp_list__id, isys_catg_netp_list__title, isys_obj_type__title " . "FROM isys_catg_netp_list " . "LEFT JOIN isys_obj ON isys_obj__id = isys_catg_netp_list__isys_obj__id " . "LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id " . "WHERE isys_catg_netp_list__id IN (" . $p_preselection . ")";

                    $l_res = $this->retrieve($l_sql);

                    if ($l_res->num_rows() > 1)
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            // Prepare return data.
                            $l_return['category'][] = $l_row['isys_obj__isys_obj_type__id'];
                            $l_return['second'][]   = [
                                isys_glob_utf8_encode($l_row['isys_catg_netp_list__id']),
                                isys_glob_utf8_encode($l_row['isys_catg_netp_list__title']),
                                isys_glob_utf8_encode(_L($l_row['isys_obj_type__title'])),
                            ]; // $l_line;
                        }
                    } // if
                } // if

                return $l_return;
                break;
        } // switch
    }

    /**
     * Formats the title of the object for the object browser.
     *
     * @param   integer $p_id
     * @param   boolean $p_plain
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function format_selection($p_id, $p_plain = false)
    {
        // We need a DAO for the object name.
        $l_dao        = isys_cmdb_dao_category_g_network_interface::instance($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_row = $l_dao->get_data($p_id)
            ->__to_array();

        $l_object_type = $l_dao->get_objTypeID($l_row["isys_catg_netp_list__isys_obj__id"]);

        if (!empty($p_id))
        {
            $l_editmode = ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON || isys_glob_get_param(
                        "edit"
                    ) == C__EDITMODE__ON || isset($this->m_params["edit"])) && !isset($this->m_params["plain"]);

            $l_title = _L($l_dao->get_objtype_name_by_id_as_string($l_object_type)) . " >> " . $l_dao->get_obj_name_by_id_as_string(
                    $l_row["isys_catg_netp_list__isys_obj__id"]
                ) . " >> " . $l_row["isys_catg_netp_list__title"];

            if (!$l_editmode && !$p_plain)
            {
                return $l_quick_info->get_quick_info(
                    $l_row["isys_catg_netp_list__isys_obj__id"],
                    $l_title,
                    C__LINK__OBJECT
                );
            }
            else
            {
                return $l_title;
            } // if
        } // if

        return _L("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
    }

    /**
     * Compares category data for import.
     *
     * If your unique properties needs them, implement it!
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
        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
        {
            $p_dataset_id_changed     = false;
            $p_badness[$p_dataset_id] = 0;
            $p_dataset_id             = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id]))
            {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
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

            if ($l_dataset['isys_catg_netp_list__title'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'])
            {
                $p_badness[$p_dataset_id]++;
            } // if
            if ($l_dataset['isys_catg_netp_list__serial'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['serial']['value'])
            {
                $p_badness[$p_dataset_id]++;
            } // if
            if ($l_dataset['isys_catg_netp_list__isys_iface_manufacturer__id'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['manufacturer']['value'])
            {
                $p_badness[$p_dataset_id]++;
            } // if
            if ($l_dataset['isys_catg_netp_list__isys_iface_model__id'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['model']['value'])
            {
                $p_badness[$p_dataset_id]++;
            } // if

            if ($p_badness[$p_dataset_id] == 0)
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            }
            elseif ($p_badness[$p_dataset_id] > 2)
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $l_candidate[$l_dataset_key]                                                      = $p_dataset_id;
            }
            elseif ($p_badness[$p_dataset_id] < 3)
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            }
        } // foreach

        // In case we did not find any matching ports
        if (!isset($p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY]) && !empty($l_candidate))
        {
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY] = $l_candidate;
        } // if
    }

    /**
     * Returns how many entries exists. The folder always returns 1.
     *
     * @param int|null $p_obj_id
     *
     * @return bool|int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == C__CATG__NETWORK)
        {
            // ID-2721  Count the sub-categories to determine if the folder shall be displayed black or grey.
            return parent::get_count($p_obj_id) + isys_cmdb_dao_category_g_network_port_overview::instance($this->m_db)->get_count($p_obj_id);
        }
        else
        {
            return parent::get_count($p_obj_id);
        } // if
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__TITLE'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_netp_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_P_TITLE'
                    ]
                ]
            ),
            'manufacturer' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_P_MANUFACTURER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_netp_list__isys_iface_manufacturer__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_iface_manufacturer',
                            'isys_iface_manufacturer__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_P_MANUFACTURER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_iface_manufacturer'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'model'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_P_MODEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__INTERFACE_P_MODEL'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_netp_list__isys_iface_model__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_iface_model',
                            'isys_iface_model__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_P_MODEL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_iface_model'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'serial'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERIAL',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SERIAL'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_netp_list__serial'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_P_SERIAL'
                    ]
                ]
            ),
            'slot'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__SWITCH_COUNT_SLOT',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CATG__SWITCH_COUNT_SLOT'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_netp_list__slotnumber'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_P_SLOTNUMBER'
                    ]
                ]
            ),
            'description'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_netp_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CMDB__SUBCAT__NETWORK_INTERFACE_P
                    ]
                ]
            )
        ];
    } // function

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
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['model'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['slot'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['model'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['slot'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    } // function
} // class

?>