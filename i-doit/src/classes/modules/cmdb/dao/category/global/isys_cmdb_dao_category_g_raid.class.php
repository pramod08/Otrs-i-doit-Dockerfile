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
 * DAO: global category for raid arrays
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_raid extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'raid';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the drive and device dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_storages(isys_request $p_request)
    {

        $l_obj_id      = $p_request->get_object_id();
        $l_category_id = $p_request->get_category_data_id();

        $l_dao_raid = new isys_cmdb_dao_category_g_raid($this->get_database_component());
        $l_data     = $l_dao_raid->get_data($l_category_id, $l_obj_id)
            ->__to_array();

        $l_hardware_raid = $l_dao_raid->get_raid_type_by_const('C__CMDB__RAID_TYPE__HARDWARE');
        $l_software_raid = $l_dao_raid->get_raid_type_by_const('C__CMDB__RAID_TYPE__SOFTWARE');

        switch ($l_data['isys_catg_raid_list__isys_raid_type__id'])
        {
            case $l_hardware_raid:
                $l_dao_stor = new isys_cmdb_dao_category_g_stor($this->get_database_component());
                $l_res      = $l_dao_stor->get_devices(null, $l_obj_id);
                $l_table    = 'isys_catg_stor_list';
                break;
            case $l_software_raid:
                $l_dao_drive = new isys_cmdb_dao_category_g_drive($this->get_database_component());
                $l_res       = $l_dao_drive->get_drives(null, $l_obj_id);
                $l_table     = 'isys_catg_drive_list';
                break;
        }

        if (is_object($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row[$l_table . '__id']] = $l_row[$l_table . '__title'];
            }
        }

        return $l_return;
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_raid_list " . "LEFT OUTER JOIN isys_obj " . "ON " . "isys_catg_raid_list__isys_obj__id = " . "isys_obj__id " . "LEFT JOIN isys_catg_controller_list " . "ON " . "isys_catg_raid_list__isys_catg_controller_list__id = isys_catg_controller_list__id " . "LEFT JOIN isys_stor_raid_level " . "ON " . "isys_catg_raid_list__isys_stor_raid_level__id = isys_stor_raid_level__id " . "LEFT JOIN isys_raid_type " . "ON " . "isys_catg_raid_list__isys_raid_type__id = isys_raid_type__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }
        if (!empty($p_catg_list_id))
        {
            $l_sql .= " AND isys_catg_raid_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND isys_catg_raid_list__status = " . $this->convert_sql_id($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'raid_type'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__RAID_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_raid_list__isys_raid_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_raid_type',
                            'isys_raid_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__RAID_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_raid_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog'
                        ]
                    ]
                ]
            ),
            'title'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_raid_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__RAID_TITLE'
                    ]
                ]
            ),
            'raid_level'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__RAIDLEVEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'RAID Level'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_raid_list__isys_stor_raid_level__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_stor_raid_level',
                            'isys_stor_raid_level__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__RAID_LEVEL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_stor_raid_level'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog'
                        ]
                    ]
                ]
            ),
            'controller'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONTROLLER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Controller'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_raid_list__isys_catg_controller_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_controller_list',
                            'isys_catg_controller_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__RAID_CONTROLLER',
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
            'storages'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__STORAGE__CONNECTED_DEVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Raid-Array'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_raid_list__id',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__RAID__STORAGES',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_raid',
                                    'callback_property_storages'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'raid'
                        ]
                    ]
                ]
            ),
            'full_capacity' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMDB_MEMORY_TOTALCAPACITY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Total capacity'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_raid_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__RAID__FULL_CAPACITY'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__VIRTUAL   => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'raid_capacity'
                        ]
                    ]
                ]
            ),
            'description'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_raid_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__RAID
                    ]
                ]
            )
        ];
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|int|mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $this->m_sync_catg_data = $p_category_data;
            $l_drive_arr            = null;
            $l_device_arr           = null;
            switch ($p_category_data['properties']['raid_type']['const'])
            {
                case 'C__CMDB__RAID_TYPE__HARDWARE':
                    $l_device_arr = $this->get_property('storages');
                    $l_drive_arr  = null;
                    break;
                case 'C__CMDB__RAID_TYPE__SOFTWARE':
                    $l_drive_arr  = $this->get_property('storages');
                    $l_device_arr = null;
                    break;
            }
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('controller'),
                        $this->get_property('raid_level'),
                        $l_device_arr,
                        $this->get_property('raid_type'),
                        $this->get_property('description'),
                        $l_drive_arr
                    ))
                    )
                    {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('controller'),
                        $this->get_property('raid_level'),
                        null,
                        $l_device_arr,
                        $this->get_property('raid_type'),
                        $this->get_property('description'),
                        null,
                        $l_drive_arr
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    public function create($p_objID, $p_newRecStatus, $p_title, $p_controllerID, $p_raidLevelID, $p_connectedDevices = null, $p_raid_typeID, $p_description, $p_connectedDrives = null)
    {
        $l_update = "INSERT INTO isys_catg_raid_list SET " . "isys_catg_raid_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . "," . "isys_catg_raid_list__title = " . $this->convert_sql_text($p_title) . "," . "isys_catg_raid_list__description = " . $this->convert_sql_text(
                $p_description
            ) . "," . "isys_catg_raid_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . "," . "isys_catg_raid_list__isys_stor_raid_level__id = " . $this->convert_sql_id(
                $p_raidLevelID
            ) . "," . "isys_catg_raid_list__isys_catg_controller_list__id = " . $this->convert_sql_id(
                $p_controllerID
            ) . "," . "isys_catg_raid_list__isys_raid_type__id = " . $this->convert_sql_id($p_raid_typeID);

        if ($this->update($l_update) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            $l_dao_stor  = new isys_cmdb_dao_category_g_stor($this->get_database_component());
            $l_dao_drive = new isys_cmdb_dao_category_g_drive($this->get_database_component());

            if ($p_raid_typeID == 1)
            {
                if (!empty($p_connectedDevices))
                {
                    if (!is_array($p_connectedDevices))
                    {
                        $l_dev_array = explode(",", $p_connectedDevices);
                    }
                    else
                    {
                        $l_dev_array = $p_connectedDevices;
                    }

                    foreach ($l_dev_array AS $l_device__id)
                    {
                        $l_dao_stor->add_raid_to_item($l_device__id, $l_last_id);
                    }
                }
            }
            elseif ($p_raid_typeID == 2)
            {
                if (!empty($p_connectedDrives))
                {
                    if (!is_array($p_connectedDrives))
                    {
                        $l_drive_array = explode(",", $p_connectedDrives);
                    }
                    else
                    {
                        $l_drive_array = $p_connectedDrives;
                    }

                    foreach ($l_drive_array AS $l_drive__id)
                    {
                        $l_dao_drive->add_raid_to_item($l_drive__id, $l_last_id);
                        //$l_dao_drive->add_to_device($l_drive__id, null);
                    }
                }
            }

            return $l_last_id;
        }
        else
            return false;
    }

    public function save($p_catLevel, $p_newRecStatus, $p_title, $p_controllerID, $p_raidLevelID, $p_deconnectedDevices = null, $p_connectedDevices = null, $p_raid_typeID, $p_description, $p_deconnectedDrives = null, $p_connectedDrives = null)
    {
        $l_update = "UPDATE isys_catg_raid_list SET " . "isys_catg_raid_list__title = " . $this->convert_sql_text(
                $p_title
            ) . "," . "isys_catg_raid_list__description = " . $this->convert_sql_text($p_description) . "," . "isys_catg_raid_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . "," . "isys_catg_raid_list__isys_stor_raid_level__id = " . $this->convert_sql_id(
                $p_raidLevelID
            ) . "," . "isys_catg_raid_list__isys_catg_controller_list__id = " . $this->convert_sql_id(
                $p_controllerID
            ) . "," . "isys_catg_raid_list__isys_raid_type__id = " . $this->convert_sql_id($p_raid_typeID) . " " . "WHERE isys_catg_raid_list__id = " . $this->convert_sql_id(
                $p_catLevel
            );

        if ($this->update($l_update) && $this->apply_update())
        {

            $l_dao_stor  = new isys_cmdb_dao_category_g_stor($this->get_database_component());
            $l_dao_drive = new isys_cmdb_dao_category_g_drive($this->get_database_component());

            /* Dettach all stores and drives */
            $l_dao_stor->detach_raid($p_catLevel);
            $l_dao_drive->detach_raid($p_catLevel);

            $l_main = ($p_raid_typeID == 1) ? $l_dao_stor : $l_dao_drive;

            if (!empty($p_connectedDevices))
            {
                if (!is_array($p_connectedDevices))
                {
                    $l_connected_arr = explode(",", $p_connectedDevices);
                }
                else
                {
                    $l_connected_arr = $p_connectedDevices;
                }

                foreach ($l_connected_arr AS $l_device__id)
                {
                    $l_main->add_raid_to_item($l_device__id, $p_catLevel);
                }
            }
            elseif (!empty($p_connectedDrives))
            {
                if (!empty($p_connectedDrives))
                {
                    if (!is_array($p_connectedDrives))
                    {
                        $l_drive_array = explode(",", $p_connectedDrives);
                    }
                    else
                    {
                        $l_drive_array = $p_connectedDrives;
                    }

                    foreach ($l_drive_array AS $l_drive__id)
                    {
                        $l_dao_drive->add_raid_to_item($l_drive__id, $p_catLevel);
                    }
                }
            }

            return true;
        }
        else
            return false;
    }

    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_result()
            ->__to_array();

        if ($p_create)
        {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__RAID_TITLE'],
                $_POST['C__CATG__RAID_CONTROLLER'],
                $_POST['C__CATG__RAID_LEVEL'],
                $_POST['C__CATG__RAID_CONNECTION__selected_box'],
                $_POST['C__CMDB__RAID_TYPE'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__RAID_DRIVE_CONNECTION__selected_box']
            );

            if ($l_id)
            {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level           = -1;

                return $l_id;
            }
        }
        else
        {
            $l_bRet = $this->save(
                $l_catdata["isys_catg_raid_list__id"],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__RAID_TITLE'],
                $_POST['C__CATG__RAID_CONTROLLER'],
                $_POST['C__CATG__RAID_LEVEL'],
                $_POST['C__CATG__RAID_CONNECTION__available_values'],
                $_POST['C__CATG__RAID_CONNECTION__selected_box'],
                $_POST['C__CMDB__RAID_TYPE'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__RAID_DRIVE_CONNECTION__available_values'],
                $_POST['C__CATG__RAID_DRIVE_CONNECTION__selected_box']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet;
    }

    public function get_raids($p_raid_id = null, $p_raid_type = null, $p_obj_id = null)
    {

        $l_sql = "SELECT * FROM isys_catg_raid_list LEFT JOIN isys_raid_type ON isys_catg_raid_list__isys_raid_type__id = isys_raid_type__id
				  WHERE TRUE ";

        if ($p_raid_id != null)
        {
            $l_sql .= " AND isys_catg_raid_list__id = " . $this->convert_sql_id($p_raid_id);
        }

        if ($p_obj_id != null)
        {
            $l_sql .= " AND isys_catg_raid_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        }

        if ($p_raid_type != null)
        {
            $l_sql .= " AND isys_catg_raid_list__isys_raid_type__id = " . $this->convert_sql_id($p_raid_type);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for retrieving raid subsets.
     *
     * @param   array $p_subset
     *
     * @return  isys_component_dao_result
     */
    public function get_raid_subset($p_subset = [])
    {
        $l_query = "SELECT * FROM isys_catg_raid_list WHERE FALSE";

        if (is_array($p_subset) && count($p_subset) > 0)
        {
            foreach ($p_subset as $l_item)
            {
                $l_query .= " OR isys_catg_raid_list__id = " . $this->convert_sql_id($l_item);
            } // foreach
        } // if

        return $this->retrieve($l_query);
    } // function

    public function get_device_name($p_id)
    {
        $l_query = "SELECT isys_catg_raid_list__title FROM isys_catg_raid_list WHERE isys_catg_raid_list__id = " . $this->convert_sql_id($p_id);

        return $this->retrieve($l_query)
            ->get_row_value('isys_catg_raid_list__title');
    }

    public function get_items_from_hardware_raid($p_list_id)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_g_stor($g_comp_database);

        $l_res = $l_dao->get_devices(null, null, $p_list_id);

        return $l_res;
    }

    public function get_items_from_software_raid($p_list_id)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_g_drive($g_comp_database);

        $l_res = $l_dao->get_drives($p_list_id);

        return $l_res;
    }

    public function get_raid_type_by_const($p_const)
    {

        $l_sql = "SELECT isys_raid_type__id FROM isys_raid_type WHERE isys_raid_type__const = " . $this->convert_sql_text($p_const);

        $l_res = $this->retrieve(($l_sql));

        if ($l_res->num_rows() > 0)
        {
            return $l_res->get_row_value('isys_raid_type__id');
        }
        else
        {
            return false;
        }

    }

} // class

?>