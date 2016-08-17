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
 * DAO: global category for storage devices.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_stor extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'stor';
    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__STORAGE__DEVICE';
    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CMDB__SUBCAT__STORAGE__DEVICE;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method to get the model manufacturer id.
     *
     * @param   isys_request $p_request
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_title_ui_params_secTableID(isys_request $p_request)
    {
        $l_cat_id = $p_request->get_category_data_id();
        $l_obj_id = $p_request->get_object_id();

        if ($l_cat_id > 0)
        {
            return $this->get_data($l_cat_id)
                ->get_row_value('isys_stor_model__isys_stor_manufacturer__id');
        }
        elseif ($l_obj_id > 0)
        {
            return $this->get_data(null, $l_obj_id)
                ->get_row_value('isys_stor_model__isys_stor_manufacturer__id');
        }
        else
        {
            return -1;
        } // if
    } // function

    /**
     * Callback method for the controller dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_controller(isys_request $p_request)
    {
        $l_return = [];
        $l_res    = $this->get_controller_by_object_id($p_request->get_object_id());

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row['isys_catg_controller_list__id']] = $l_row['isys_catg_controller_list__title'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Callback method for the controller dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_raid_group(isys_request $p_request)
    {
        $l_return = [];

        $l_res = isys_cmdb_dao_category_g_raid::instance($this->get_database_component())
            ->get_raids(null, null, $p_request->get_object_id());

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row['isys_catg_raid_list__id']] = $l_row['isys_catg_raid_list__title'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_obj
			INNER JOIN isys_catg_stor_list ON isys_catg_stor_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT OUTER  JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id
			LEFT OUTER JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id
			LEFT OUTER JOIN isys_stor_raid_level ON isys_stor_raid_level__id = isys_catg_stor_list__isys_stor_raid_level__id
			LEFT OUTER JOIN isys_catg_controller_list ON isys_catg_controller_list__id = isys_catg_stor_list__isys_catg_controller_list__id
			LEFT OUTER JOIN isys_catg_sanpool_list ON isys_catg_sanpool_list__id = isys_catg_stor_list__isys_catg_sanpool_list__id
			LEFT JOIN isys_stor_con_type ON isys_stor_con_type__id = isys_catg_stor_list__isys_stor_con_type__id
			LEFT OUTER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id
			LEFT OUTER JOIN isys_stor_type ON isys_stor_type__id = isys_catg_stor_list__isys_stor_type__id
			LEFT OUTER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_stor_list__isys_catg_raid_list__id
			LEFT OUTER JOIN isys_raid_type ON isys_raid_type__id = isys_catg_raid_list__isys_raid_type__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND isys_catg_stor_list__id = " . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_stor_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ";");
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_stor_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_stor_type',
                            'isys_stor_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_stor_type'
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
            'title'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__STOR__TITLE'
                    ]
                ]
            ),
            'manufacturer' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_stor_manufacturer__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_stor_manufacturer',
                            'isys_stor_manufacturer__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_MANUFACTURER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'       => 'isys_stor_manufacturer',
                            'p_ajaxTable'      => 'isys_stor_model',
                            'p_ajaxIdentifier' => 'C__CATG__STORAGE_MODEL'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus',
                            ['isys_stor_manufacturer']
                        ]
                    ]
                ]
            ),
            'model'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO             => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_MODEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Model'
                    ],
                    C__PROPERTY__DATA             => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_stor_model__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_stor_model',
                            'isys_stor_model__id'
                        ]
                    ],
                    C__PROPERTY__UI               => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_MODEL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'              => 'isys_stor_model',
                            'secTable'                => 'isys_stor_manufacturer',
                            'secTableID'              => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_stor',
                                    'callback_property_title_ui_params_secTableID'
                                ]
                            ),
                            'p_strSecTableIdentifier' => 'C__CATG__STORAGE_MANUFACTURER',
                            'p_strSecDataIdentifier'  => 'isys_cmdb_dao_category_g_stor::manufacturer',
                        ]
                    ],
                    C__PROPERTY__PROVIDES         => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT           => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus',
                            ['isys_stor_model']
                        ]
                    ],
                    C__PROPERTY__FORMAT__REQUIRES => 'manufacturer'
                ]
            ),
            'unit'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_memory_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_memory_unit',
                            'isys_memory_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_memory_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'capacity'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::double(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_CAPACITY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Capacity'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__capacity'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CAPACITY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['memory']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'unit'
                    ]
                ]
            ),
            'hotspare'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_HOTSPARE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hotspare'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__hotspare'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__STORAGE_HOTSPARE',
                        C__PROPERTY__UI__DEFAULT => 0,
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'connected'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONNECTION_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_stor_con_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_stor_con_type',
                            'isys_stor_con_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONNECTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'  => 'isys_stor_con_type',
                            'p_bLinklist' => '1'
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
            'controller'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__STORAGE_CONTROLLER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Controller'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_catg_controller_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_controller_list',
                            'isys_catg_controller_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_stor',
                                    'callback_property_controller'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]
            ),
            'raid_group'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__RAIDGROUP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hardware RAID group'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_catg_raid_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_raid_list',
                            'isys_catg_raid_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_RAIDGROUP',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_stor',
                                    'callback_property_raid_group'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]
            ),
            'serial'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_SERIAL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Serial number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__serial'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__STORAGE_SERIAL'
                    ]
                ]
            ),
            'description'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CMDB__SUBCAT__STORAGE__DEVICE,
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array $p_category_data Values of category data to be saved.
     * @param   int   $p_object_id     Current object identifier (from database)
     * @param   int   $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['capacity'][C__DATA__VALUE],
                        $p_category_data['properties']['unit'][C__DATA__VALUE],
                        $p_category_data['properties']['connected'][C__DATA__VALUE],
                        $p_category_data['properties']['controller'][C__DATA__VALUE],
                        $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                        $p_category_data['properties']['model'][C__DATA__VALUE],
                        $p_category_data['properties']['raid_group'][C__DATA__VALUE],
                        null,
                        null,
                        $p_category_data['properties']['hotspare'][C__DATA__VALUE],
                        $p_category_data['properties']['serial'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );
                    if ($p_category_data['data_id'] > 0)
                    {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['capacity'][C__DATA__VALUE],
                        $p_category_data['properties']['unit'][C__DATA__VALUE],
                        $p_category_data['properties']['connected'][C__DATA__VALUE],
                        $p_category_data['properties']['controller'][C__DATA__VALUE],
                        $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                        $p_category_data['properties']['model'][C__DATA__VALUE],
                        $p_category_data['properties']['raid_group'][C__DATA__VALUE],
                        null,
                        null,
                        $p_category_data['properties']['hotspare'][C__DATA__VALUE],
                        $p_category_data['properties']['serial'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Returns a result set with all devices connected to the specified storage controller.
     *
     * @param   integer $p_controller_id
     * @param   integer $p_object_id
     * @param   integer $p_nRaidPoolID
     * @param   integer $p_cDeviceType
     * @param   string  $p_condition
     * @param   boolean $p_show_raid
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.de>
     * @author  Andre Wösten <awoesten@i-doit.org>
     */
    public function get_devices($p_controller_id = null, $p_object_id = null, $p_nRaidPoolID = null, $p_cDeviceType = null, $p_condition = null, $p_show_raid = false)
    {
        $l_sql = "SELECT * FROM isys_catg_stor_list
			LEFT OUTER JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id
			LEFT OUTER JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id
			LEFT OUTER JOIN isys_stor_raid_level ON isys_stor_raid_level__id = isys_catg_stor_list__isys_stor_raid_level__id
			LEFT OUTER JOIN isys_catg_controller_list ON isys_catg_controller_list__id = isys_catg_stor_list__isys_catg_controller_list__id
			LEFT OUTER JOIN isys_catg_sanpool_list ON isys_catg_sanpool_list__id = isys_catg_stor_list__isys_catg_sanpool_list__id
			LEFT JOIN isys_stor_con_type ON isys_stor_con_type__id = isys_catg_stor_list__isys_stor_con_type__id
			LEFT OUTER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id
			LEFT OUTER JOIN isys_stor_type ON isys_stor_type__id = isys_catg_stor_list__isys_stor_type__id
			LEFT OUTER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_stor_list__isys_catg_raid_list__id
			LEFT OUTER JOIN isys_raid_type ON isys_raid_type__id = isys_catg_raid_list__isys_raid_type__id ";

        if (is_numeric($p_controller_id))
        {
            $l_sql .= "WHERE isys_catg_stor_list__isys_catg_controller_list__id= " . $this->convert_sql_id($p_controller_id);
        }
        else if (is_numeric($p_object_id))
        {
            $l_sql .= "JOIN isys_obj ON isys_catg_stor_list__isys_obj__id=isys_obj__id WHERE isys_obj__id = " . $this->convert_sql_id($p_object_id);
        }
        else
        {
            $l_sql .= "WHERE TRUE ";
        } // if

        if ($p_nRaidPoolID != null || $p_nRaidPoolID != 0)
        {
            $l_sql .= " AND isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_nRaidPoolID) . " ";
        }
        else if (!$p_show_raid)
        {
            $l_sql .= " AND isys_catg_stor_list__isys_catg_raid_list__id IS NULL AND isys_catg_stor_list__status = " . C__RECORD_STATUS__NORMAL;
        } // if

        if ($p_cDeviceType)
        {
            $l_sql .= " AND isys_catg_stor_list__isys_stor_type__id = '$p_cDeviceType' ";
        } // if

        if ($p_condition != null)
        {
            $l_sql .= $p_condition;
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for returning the raids.
     *
     * @param   integer $p_controllerID
     *
     * @return  isys_component_dao_result
     */
    public function get_raids($p_controllerID)
    {
        return $this->retrieve("SELECT * FROM isys_catg_raid_list WHERE isys_catg_raid_list__isys_catg_controller_list__id = " . $this->convert_sql_id($p_controllerID));
    }

    /**
     * Method for returning unassigned raids.
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_unassigned_raids($p_objID)
    {
        return $this->retrieve(
            "SELECT * FROM isys_catg_raid_list WHERE isys_catg_raid_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . " AND ISNULL(isys_catg_raid_list__isys_catg_controller_list__id)"
        );
    } // function

    /**
     * Returns a result set with all controllers assigned to the specified object id.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cType
     *
     * @return  isys_component_dao_result
     * @author  Andre Wösten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_controller_by_object_id($p_obj_id, $p_cType = null)
    {
        $l_bType = ($p_cType == C__STOR_TYPE_FC_CONTROLLER);

        $l_q = "SELECT isys_catg_controller_list__title, isys_catg_controller_list__id, isys_catg_controller_list__status, isys_controller_type__const
			FROM isys_catg_controller_list
			JOIN isys_obj ON isys_catg_controller_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_controller_type ON isys_catg_controller_list__isys_controller_type__id = isys_controller_type__id
			WHERE isys_obj__id = " . $this->convert_sql_id($p_obj_id) . " ";

        if ($l_bType)
        {
            $l_q .= "AND isys_controller_type__const = 'C__STOR_TYPE_FC_CONTROLLER'";
        } // if

        return $this->retrieve($l_q);
    } // function

    /**
     * Return all SAN pools.
     *
     * @return  isys_component_dao_result
     */
    public function get_san_pools()
    {
        return $this->retrieve("SELECT * FROM isys_catg_sanpool_list");
    } // function

    /**
     * Returns Parent Object of SAN Pool
     *
     * @param   integer $p_SANPoolID
     *
     * @return  isys_component_dao_result
     */
    public function get_san_pool_parent($p_SANPoolID)
    {
        if (!$p_SANPoolID || !is_numeric($p_SANPoolID))
        {
            return false;
        } // if

        $l_strSQL = "SELECT *
			FROM isys_catg_sanpool_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_sanpool_list__isys_obj__id
			WHERE isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_SANPoolID) . ";";

        return $this->retrieve($l_strSQL);
    } // function

    /**
     * @return integer (newly created id)
     *
     * @param int $p_cat_level
     * @param int $p_intOldRecStatus __status of record before update
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $p_cat_level = null;

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_gets  = isys_module_request::get_instance()
            ->get_gets();

        $l_id = $l_gets[C__CMDB__GET__CATLEVEL] ?: $l_posts["stor_id"];

        // create new one, or just save it?
        if ($l_id != -1)
        {
            $l_ret = $this->save(
                $l_id,
                $l_posts["C__CATG__STORAGE_TYPE"],
                C__RECORD_STATUS__NORMAL,
                $l_posts["C__CATG__STORAGE_TITLE"],
                $l_posts["C__CATG__STORAGE_CAPACITY"],
                $l_posts["C__CATG__STORAGE_UNIT"],
                $l_posts["C__CATG__STORAGE_CONNECTION_TYPE"],
                $l_posts["C__CATG__STORAGE_CONTROLLER"],
                $l_posts["C__CATG__STORAGE_MANUFACTURER"],
                $l_posts["C__CATG__STORAGE_MODEL"],
                $l_posts["C__CATG__STORAGE_RAIDGROUP"],
                $l_posts["C__CATG__STORAGE_RAIDLEVEL"],
                $l_posts["C__CATG__STORAGE_SANPOOL"],
                $l_posts["C__CATG__STORAGE_HOTSPARE"],
                $l_posts["C__CATG__STORAGE_SERIAL"],
                explode(",", $l_posts["C__CATG__STORAGE_CONNECTION__selected_values"]),
                $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
        }
        else
        {
            $l_storage_count = 1;

            // Determine how many devices are to be created.
            if (is_numeric($l_posts["C__CATG__STORAGE__SUFFIX_COUNT"]))
            {
                if ($l_posts["C__CATG__STORAGE__SUFFIX_COUNT"] > 1)
                {
                    $l_storage_count = $l_posts["C__CATG__STORAGE__SUFFIX_COUNT"];
                } // if
            } // if

            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CATG__STORAGE', 'C__CATG__STORAGE_TITLE');

            for ($i = 0;$l_storage_count > $i;$i++)
            {
                $l_title = $l_title_arr[$i];

                $l_ret = $this->create(
                    $l_gets[C__CMDB__GET__OBJECT],
                    $l_posts["C__CATG__STORAGE_TYPE"],
                    C__RECORD_STATUS__NORMAL,
                    $l_title,
                    $l_posts["C__CATG__STORAGE_CAPACITY"],
                    $l_posts["C__CATG__STORAGE_UNIT"],
                    $l_posts["C__CATG__STORAGE_CONNECTION_TYPE"],
                    $l_posts["C__CATG__STORAGE_CONTROLLER"],
                    $l_posts["C__CATG__STORAGE_MANUFACTURER"],
                    $l_posts["C__CATG__STORAGE_MODEL"],
                    $l_posts["C__CATG__STORAGE_RAIDGROUP"],
                    $l_posts["C__CATG__STORAGE_RAIDLEVEL"],
                    $l_posts["C__CATG__STORAGE_SANPOOL"],
                    $l_posts["C__CATG__STORAGE_HOTSPARE"],
                    $l_posts["C__CATG__STORAGE_SERIAL"],
                    explode(",", $l_posts["C__CATG__STORAGE_CONNECTION__selected_values"]),
                    $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );
            } // for

            $p_cat_level = 1;
        } // if

        return $l_ret;
    } // function

    /**
     * Executes the operations to create the category entry for the object referenced by isys_obj__id $p_objID
     * The device type is specified by its constant C__CATG__STORAGE_TYPE $p_deviceType
     *
     * @param int     $p_objID
     * @param int     $p_deviceType
     * @param int     $p_recStatus
     * @param String  $p_title
     * @param float   $p_capacity
     * @param integer $p_capacityUnitID
     * @param integer $p_conTypeID
     * @param integer $p_controllerID
     * @param integer $p_manufacturerID
     * @param integer $p_modelID
     * @param integer $p_raidGroupID
     * @param integer $p_raidLevelID
     * @param integer $p_sanPoolID
     * @param boolean $p_hotspare
     * @param         $p_serial
     * @param array   $p_connectedHDs
     * @param String  $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_deviceType, $p_recStatus, $p_title, $p_capacity, $p_capacityUnitID, $p_conTypeID, $p_controllerID, $p_manufacturerID, $p_modelID, $p_raidGroupID, $p_raidLevelID, $p_sanPoolID, $p_hotspare, $p_serial, $p_connectedHDs, $p_description)
    {
        // Convert capacity from user's locale to invariant.
        $p_capacity = isys_convert::memory(isys_helper::filter_number($p_capacity), $p_capacityUnitID);

        try
        {
            $this->begin_update();

            $l_update = "INSERT INTO isys_catg_stor_list SET
				isys_catg_stor_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
				isys_catg_stor_list__status = " . $this->convert_sql_id($p_recStatus) . ",
				isys_catg_stor_list__isys_stor_type__id = " . $this->convert_sql_id($p_deviceType) . ",
				isys_catg_stor_list__title = " . $this->convert_sql_text($p_title) . ",
				isys_catg_stor_list__capacity = '" . $p_capacity . "',
				isys_catg_stor_list__isys_memory_unit__id = " . $this->convert_sql_id($p_capacityUnitID) . ",
				isys_catg_stor_list__isys_stor_con_type__id = " . $this->convert_sql_id($p_conTypeID) . ",
				isys_catg_stor_list__isys_catg_controller_list__id = " . $this->convert_sql_id($p_controllerID) . ",
				isys_catg_stor_list__isys_stor_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
				isys_catg_stor_list__isys_stor_model__id = " . $this->convert_sql_id($p_modelID) . ",
				isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raidGroupID) . ",
				isys_catg_stor_list__isys_stor_raid_level__id = " . $this->convert_sql_id($p_raidLevelID) . ",
				isys_catg_stor_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_sanPoolID) . ",
				isys_catg_stor_list__hotspare = " . $this->convert_sql_text($p_hotspare) . ",
				isys_catg_stor_list__serial = " . $this->convert_sql_text($p_serial) . ",
				isys_catg_stor_list__description = " . $this->convert_sql_text($p_description);

            if (!$this->update($l_update))
            {
                throw new Exception();
            } // if

            $l_id = $this->get_last_insert_id();

            if ($p_deviceType == C__STOR_TYPE_DEVICE_RAID_GRP)
            {
                // Set this as raidpool for the given HDDs by $p_connectedHDs.
                foreach ($p_connectedHDs as $l_dev)
                {
                    $l_update = "UPDATE isys_catg_stor_list SET
						isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($l_id) . "
						WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($l_dev);

                    if (!$this->update($l_update))
                    {
                        throw new Exception();
                    } // if
                } // foreach
            } // if

            if (!$this->apply_update())
            {
                throw new Exception();
            } // if
        }
        catch (Exception $e)
        {
            $this->cancel_update();

            return false;
        } // try

        return $l_id;
    } // function

    /**
     * Executes the operations to save the category entry referenced by isys_catg_stor_list__id $p_cat_level
     * The device type is specified by its constant C__CATG__STORAGE_TYPE $p_deviceType.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_deviceType
     * @param   integer $p_recStatus
     * @param   String  $p_title
     * @param   float   $p_capacity
     * @param   integer $p_capacityUnitID
     * @param   integer $p_conTypeID
     * @param   integer $p_controllerID
     * @param   integer $p_manufacturerID
     * @param   integer $p_modelID
     * @param   integer $p_raidGroupID
     * @param   integer $p_raidLevelID
     * @param   integer $p_sanPoolID
     * @param   boolean $p_hotspare
     * @param   string  $p_serial
     * @param   array   $p_connectedHDs
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_deviceType, $p_recStatus, $p_title, $p_capacity, $p_capacityUnitID, $p_conTypeID, $p_controllerID, $p_manufacturerID, $p_modelID, $p_raidGroupID, $p_raidLevelID, $p_sanPoolID, $p_hotspare, $p_serial, $p_connectedHDs, $p_description)
    {
        $p_capacity = isys_convert::memory(isys_helper::filter_number($p_capacity), $p_capacityUnitID);

        try
        {
            $this->begin_update();

            $l_update = "UPDATE isys_catg_stor_list SET
				isys_catg_stor_list__status = " . $this->convert_sql_id($p_recStatus) . ",
				isys_catg_stor_list__isys_stor_type__id = " . $this->convert_sql_id($p_deviceType) . ",
				isys_catg_stor_list__title = " . $this->convert_sql_text($p_title) . ",
				isys_catg_stor_list__capacity = '" . $p_capacity . "',
				isys_catg_stor_list__isys_memory_unit__id = " . $this->convert_sql_id($p_capacityUnitID) . ",
				isys_catg_stor_list__isys_stor_con_type__id = " . $this->convert_sql_id($p_conTypeID) . ",
				isys_catg_stor_list__isys_catg_controller_list__id = " . $this->convert_sql_id($p_controllerID) . ",
				isys_catg_stor_list__isys_stor_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
				isys_catg_stor_list__isys_stor_model__id = " . $this->convert_sql_id($p_modelID) . ",
				isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raidGroupID) . ",
				isys_catg_stor_list__isys_stor_raid_level__id = " . $this->convert_sql_id($p_raidLevelID) . ",
				isys_catg_stor_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_sanPoolID) . ",
				isys_catg_stor_list__hotspare = " . $this->convert_sql_text($p_hotspare) . ",
				isys_catg_stor_list__serial = " . $this->convert_sql_text($p_serial) . ",
				isys_catg_stor_list__description = " . $this->convert_sql_text($p_description) . "
				WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

            if (!$this->update($l_update))
            {
                throw new Exception();
            } // if

            if ($p_deviceType == C__STOR_TYPE_DEVICE_RAID_GRP)
            {
                $l_update = "UPDATE isys_catg_stor_list SET
					isys_catg_stor_list__isys_catg_raid_list__id = NULL
					WHERE isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_cat_level);

                if (!$this->update($l_update))
                {
                    throw new Exception();
                } // if

                // Set this as raidpool for the given HDDs by $p_connectedHDs.
                foreach ($p_connectedHDs as $l_dev)
                {
                    $l_update = "UPDATE isys_catg_stor_list SET
						isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_cat_level) . "
						WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($l_dev);

                    if (!$this->update($l_update))
                    {
                        throw new Exception();
                    } // if
                } // foreach
            } // if

            if (!$this->apply_update())
            {
                throw new Exception();
            } // if
        }
        catch (Exception $e)
        {
            $this->cancel_update();

            return false;
        } // try

        return true;
    } // function

    /**
     * Import-Handler for storage (hard disk)
     *
     * @param array
     *
     * @return array (newly created storage IDs)
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2008-03-27
     * @version Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_object_id)
    {
        $l_status = -1;
        $l_cat    = -1;
        $l_ar_ID  = [];

        if (is_array($p_data))
        {
            foreach ($p_data as $l_key => $l_data)
            {
                foreach ($l_data as $l_arStor)
                {
                    switch ($l_key)
                    {
                        case "cd":
                            $_POST['C__CATG__STORAGE_TYPE'] = C__STOR_TYPE_DEVICE_CD_ROM;
                            break;
                        case "floppy":
                            $_POST['C__CATG__STORAGE_TYPE'] = C__STOR_TYPE_DEVICE_FLOPPY;
                            break;
                        default:
                            $_POST['C__CATG__STORAGE_TYPE'] = C__STOR_TYPE_DEVICE_HD;
                            break;
                    } // switch

                    $_POST['C__CATG__STORAGE_MANUFACTURER'] = isys_import::check_dialog("isys_stor_manufacturer", $l_arStor["manufacturer"]);
                    $_POST['C__CATG__STORAGE_CAPACITY']     = $l_arStor["size"];
                    $_POST['C__CATG__STORAGE_UNIT']         = isys_import::check_dialog("isys_stor_unit", "C__STOR_UNIT__MB", "isys_stor_unit__const");
                    $_POST['C__CATG__STORAGE_TITLE']        = trim($l_arStor["name"]) . " (" . trim($l_arStor["number"]) . ")";

                    if (!$_GET[C__CMDB__GET__OBJECT] && !is_null($p_object_id))
                    {
                        $_GET[C__CMDB__GET__OBJECT] = $p_object_id;
                    } // if

                    $_GET[C__CMDB__GET__CATLEVEL] = -1;

                    isys_module_request::get_instance()
                        ->_internal_set_private("m_post", $_POST)
                        ->_internal_set_private("m_get", $_GET);

                    $l_ar_ID[] = $this->save_element($l_cat, $l_status);
                } // foreach
            } // foreach
        } // if

        return $l_ar_ID;
    } // function

    /**
     * Gets info of the raid system
     *
     * @param int $p_obj__id
     *
     * @return array
     */
    public function get_raid_memory_info($p_obj__id)
    {
        $l_sql = "SELECT isys_catg_stor_list__capacity, isys_catg_stor_list__isys_memory_unit__id, isys_memory_unit__title FROM isys_catg_stor_list " . "LEFT JOIN isys_memory_unit ON isys_catg_stor_list__isys_memory_unit__id = isys_memory_unit__id " . "WHERE isys_catg_stor_list__isys_catg_raid_list__id = '" . $p_obj__id . "'";

        $l_res       = $this->retrieve($l_sql);
        $l_num_disks = $l_res->num_rows();
        $l_capacity  = 0;
        while ($l_row = $l_res->get_row())
        {

            if ($l_capacity == 0 || $l_capacity > isys_convert::memory(
                    $l_row["isys_catg_stor_list__capacity"],
                    $l_row["isys_catg_stor_list__isys_memory_unit__id"],
                    C__CONVERT_DIRECTION__BACKWARD
                )
            ) $l_capacity = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], $l_row["isys_catg_stor_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD);

            $l_capacity_type = $l_row["isys_memory_unit__title"];
        }

        return [
            $l_capacity,
            $l_capacity_type,
            $l_num_disks
        ];
    }

    /**
     * Gets the Raid-Level.
     *
     * @param   integer $p_level__id
     *
     * @return  string
     */
    public function get_raid_level($p_level__id)
    {
        return $this->retrieve('SELECT isys_stor_raid_level__title FROM isys_stor_raid_level WHERE isys_stor_raid_level__id = ' . $this->convert_sql_id($p_level__id) . ';')
            ->get_row_value('isys_stor_raid_level__title');
    } // function

    /**
     * Calculates Capacity for the specific Raid-Level
     *
     * @param int   $p_disks
     * @param float $p_space
     * @param int   $p_raidtype
     *
     * @return float
     */
    public function raidcalc($p_disks, $p_space, $p_raidtype)
    {
        $l_num_disks = $p_disks;
        $l_space     = "-";

        if ($l_num_disks % 2 != 0 && $p_raidtype == "1")
        {
            return false;
        }
        if ($l_num_disks % 2 != 0 && $p_raidtype == "10")
        {
            return false;
        }

        $l_diskspace_each = $p_space;
        $l_diskspace      = ($l_diskspace_each * $l_num_disks);

        switch ($p_raidtype)
        {
            case "0":
                $l_strUtilization = $l_diskspace;
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "1":
                $l_strUtilization = $l_diskspace / 2;
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "10":
                $l_strUtilization = $l_diskspace / 2;
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "2":
                $l_strUtilization = $l_diskspace;
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "3":
                $l_strUtilization = ($l_diskspace * (((($l_num_disks - 1) / $l_num_disks) * 10000 / 100) / 100));
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "4":
                $l_strUtilization = ($l_diskspace * (((($l_num_disks - 1) / $l_num_disks * 10000) / 100) / 100));
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "5":
                $l_strUtilization = ($l_diskspace * (((($l_num_disks - 1) / $l_num_disks) * 10000 / 100) / 100));
                $l_space          = $l_strUtilization * 100 / 100;
                break;

            case "6":
                $l_strUtilization = ($l_diskspace * (((($l_num_disks - 2) / $l_num_disks) * 10000 / 100) / 100));
                $l_space          = $l_strUtilization * 100 / 100;
                break;
        } // switch

        if ($l_space != 0 && is_numeric($l_space))
        {
            return $l_space;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Attach new raid.
     *
     * @param   integer $p_stor__id
     * @param   integer $p_raid__id
     * @param   integer $p_controller__id
     *
     * @return  boolean
     */
    public function add_raid_to_item($p_stor__id, $p_raid__id, $p_controller__id = null)
    {
        $l_update = 'UPDATE isys_catg_stor_list
			SET isys_catg_stor_list__isys_catg_raid_list__id = ' . $this->convert_sql_id($p_raid__id) . '
			WHERE isys_catg_stor_list__id = ' . $this->convert_sql_id($p_stor__id);

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Detach a certain raid.
     *
     * @param   integer $p_raidID
     *
     * @return  boolean
     */
    public function detach_raid($p_raidID)
    {
        $l_sql = 'UPDATE isys_catg_stor_list
			SET isys_catg_stor_list__isys_catg_raid_list__id = NULL
			WHERE isys_catg_stor_list__isys_catg_raid_list__id = ' . $this->convert_sql_id($p_raidID) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for removing a certain raid from something.
     *
     * @param   integer $p_stor__id
     *
     * @return  boolean
     */
    public function remove_raid_from_item($p_stor__id)
    {
        $l_update = 'UPDATE isys_catg_stor_list
			SET isys_catg_stor_list__isys_catg_raid_list__id = NULL
			WHERE isys_catg_stor_list__id = ' . $this->convert_sql_id($p_stor__id) . ';';

        return ($this->update($l_update) && $this->apply_update);
    } // function

    /**
     * Method for retrieving device subsets.
     *
     * @param   array $p_subset
     *
     * @return  isys_component_dao_result
     */
    public function get_device_subset($p_subset = [])
    {
        $l_query = 'SELECT * FROM isys_catg_stor_list
			LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id
			WHERE FALSE';

        if (is_array($p_subset) && count($p_subset) > 0)
        {
            foreach ($p_subset as $l_item)
            {
                $l_query .= " OR isys_catg_stor_list__id = " . $this->convert_sql_id($l_item);
            } // foreach
        } // if

        return $this->retrieve($l_query);
    } // function

    /**
     * Retrieves a device name.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_device_name($p_id)
    {
        return $this->retrieve('SELECT isys_catg_stor_list__title FROM isys_catg_stor_list WHERE isys_catg_stor_list__id = ' . $this->convert_sql_id($p_id) . ';')
            ->get_row_value('isys_catg_stor_list__title');
    } // function

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        $l_model        = null;
        $l_manufacturer = null;
        $l_type         = null;

        if (!empty($p_data['model']))
        {
            $l_model = isys_import_handler::check_dialog('isys_stor_model', $p_data['model']);
        } // if

        if (!empty($p_data['manufacturer']))
        {
            $l_manufacturer = isys_import_handler::check_dialog('isys_stor_manufacturer', $p_data['manufacturer']);
        } // if

        if (!is_numeric($p_data['type']) && !empty($p_data['type']))
        {
            $l_type = isys_import_handler::check_dialog('isys_stor_type', $p_data['type']);
        }
        else if (is_numeric($p_data['type']))
        {
            $l_type = $p_data['type'];
        } // if

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title']
                ],
                'type'         => [
                    'value' => $l_type
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'model'        => [
                    'value' => $l_model
                ],
                'unit'         => [
                    'value' => $p_data['unit']
                ],
                'capacity'     => [
                    'value' => $p_data['capacity']
                ],
                'description'  => [
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
        $l_title = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value']);
        $l_type  = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['type']['value'];

        if ($l_type !== C__STOR_TYPE_DEVICE_TAPE)
        {
            $l_serial = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['serial']['value']);

            // Iterate through local data sets:
            foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
            {
                $p_dataset_id_changed = false;
                $p_dataset_id         = $l_dataset[$p_table . '__id'];

                // Ignore Devices from type Tape or if ID has already been used skip this entry
                if ($l_dataset['isys_catg_stor_list__isys_stor_type__id'] === C__STOR_TYPE_DEVICE_TAPE || isset($p_already_used_data_ids[$p_dataset_id]))
                {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                    continue;
                }

                //$p_logger->debug(sprintf('Handle dataset %s.', $p_dataset_id));
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
                $l_dataset_title  = strtolower($l_dataset['isys_catg_stor_list__title']);
                $l_dataset_serial = strtolower($l_dataset['isys_catg_stor_list__serial']);

                if ($l_dataset_title === $l_title && $l_dataset_serial === $l_serial)
                {
                    // Check properties
                    // We found our dataset
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                }
                elseif ($l_dataset_serial === $l_serial && $l_dataset_title !== $l_title)
                {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
                }
                else
                {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                } // if
            } // foreach
        }
        else
        {
            // Iterate through local data sets:
            foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
            {
                $p_dataset_id_changed = false;
                $p_dataset_id         = $l_dataset[$p_table . '__id'];

                if ($l_dataset['isys_catg_stor_list__isys_stor_type__id'] !== C__STOR_TYPE_DEVICE_TAPE)
                {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                    continue;
                }

                //$p_logger->debug(sprintf('Handle dataset %s.', $p_dataset_id));
                // Test the category data identifier:
                if ($p_category_data_values['data_id'] !== null)
                {
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id && !isset($p_already_used_data_ids[$p_dataset_id]))
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
                if (strtolower($l_dataset['isys_catg_stor_list__title']) === $l_title && !isset($p_already_used_data_ids[$l_dataset['isys_catg_stor_list__id']]))
                {
                    // Check properties
                    // We found our dataset
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                }
                else
                {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                } // if
            } // foreach
        }
    } // function

} // class