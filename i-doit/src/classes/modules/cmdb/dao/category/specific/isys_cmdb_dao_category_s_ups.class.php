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
 * DAO: specific category for uninterruptible power suppliers (UPS)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_ups extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'ups';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for retrieving the autonomy time + unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_autonomy_time(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';
        $l_dao    = isys_cmdb_dao_category_s_ups::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== null)
        {
            $l_unit_row = $l_dao->get_dialog('isys_unit_of_time', $l_row['isys_cats_ups_list__autonomy_time__isys_unit_of_time__id'])
                ->get_row();

            $l_return = isys_convert::time(
                    $l_row['isys_cats_ups_list__autonomy_time'],
                    $l_row['isys_cats_ups_list__autonomy_time__isys_unit_of_time__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . _L($l_unit_row['isys_unit_of_time__title']);
        }

        return $l_return;
    } // function

    /**
     * Dynamic property handling for retrieving the charge time + unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_charge_time(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';
        $l_dao    = isys_cmdb_dao_category_s_ups::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== null)
        {
            $l_unit_row = $l_dao->get_dialog('isys_unit_of_time', $l_row['isys_cats_ups_list__charge_time__isys_unit_of_time__id'])
                ->get_row();

            $l_return = isys_convert::time(
                    $l_row['isys_cats_ups_list__charge_time'],
                    $l_row['isys_cats_ups_list__charge_time__isys_unit_of_time__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . _L($l_unit_row['isys_unit_of_time__title']);
        }

        return $l_return;
    } // function

    /**
     * Save specific category ups.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  integer
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_ups_list__status"];

        $l_list_id = $l_catdata["isys_cats_ups_list__id"];

        if (empty($l_list_id))
        {
            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL);
        }

        if ($l_list_id)
        {
            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CMDB__CATS__UPS__TYPE'],
                $_POST['C__CMDB__CATS__UPS__BATTERY_TYPE'],
                $_POST['C__CMDB__CATS__UPS__AMOUNT_BATTERIES'],
                $_POST['C__CMDB__CATS__UPS__CHARGE_TIME'],
                $_POST['C__CMDB__CATS__UPS__AUTONOMY_TIME'],
                $_POST['C__CMDB__CATS__UPS__CHARGE_TIME_UNIT_OF_TIME'],
                $_POST['C__CMDB__CATS__UPS__AUTONOMY_TIME_UNIT_OF_TIME'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet == true ? $l_list_id : -1;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_type_id
     * @param   integer $p_battery_type_id
     * @param   integer $p_battery_amount
     * @param   integer $p_charge_time
     * @param   integer $p_autonomy_time
     * @param   integer $p_charge_unit
     * @param   integer $p_autonomy_unit
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_type_id, $p_battery_type_id, $p_battery_amount, $p_charge_time, $p_autonomy_time, $p_charge_unit, $p_autonomy_unit, $p_description)
    {
        $l_charge_time = isys_convert::time(
            $p_charge_time,
            $this->get_dialog("isys_unit_of_time", $p_charge_unit)
                ->get_row_value('isys_unit_of_time__const')
        );

        $l_autonomy_time = isys_convert::time(
            $p_autonomy_time,
            $this->get_dialog("isys_unit_of_time", $p_autonomy_unit)
                ->get_row_value('isys_unit_of_time__const')
        );

        $l_strSql = "UPDATE isys_cats_ups_list SET
			isys_cats_ups_list__isys_ups_type__id = " . $this->convert_sql_id($p_type_id) . ",
			isys_cats_ups_list__isys_ups_battery_type__id = " . $this->convert_sql_id($p_battery_type_id) . ",
			isys_cats_ups_list__battery_amount = " . $this->convert_sql_int($p_battery_amount) . ",
			isys_cats_ups_list__charge_time = " . $this->convert_sql_int($l_charge_time) . ",
			isys_cats_ups_list__autonomy_time = " . $this->convert_sql_int($l_autonomy_time) . ",
			isys_cats_ups_list__charge_time__isys_unit_of_time__id = " . $this->convert_sql_text($p_charge_unit) . ",
			isys_cats_ups_list__autonomy_time__isys_unit_of_time__id = " . $this->convert_sql_text($p_autonomy_unit) . ",
			isys_cats_ups_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_ups_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_cats_ups_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    } // function

    /**
     * Executes the query to create the category entry
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   string  $p_description
     *
     * @return  mixed  The newly created ID or false.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title = null, $p_description = null)
    {
        $l_strSql = "INSERT IGNORE INTO isys_cats_ups_list SET
			isys_cats_ups_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_cats_ups_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_ups_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_cats_ups_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

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
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_autonomy_time' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__AUTONOMY_TIME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Autonomy time under full load'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_autonomy_time'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_charge_time'   => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__CHARGE_TIME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Charge time'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_charge_time'
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
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ups_list__isys_ups_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ups_type',
                            'isys_ups_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__UPS__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_ups_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'battery_type'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__BATTERY_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Battery type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ups_list__isys_ups_battery_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ups_battery_type',
                            'isys_ups_battery_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__UPS__BATTERY_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_ups_battery_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'amount'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__AMOUNT_BATTERIES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Quantity'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ups_list__battery_amount'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__UPS__AMOUNT_BATTERIES'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'charge_time'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__CHARGE_TIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Charge time'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ups_list__charge_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__UPS__CHARGE_TIME',
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
                            ['time']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'charge_time_unit'
                    ]
                ]
            ),
            'charge_time_unit'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_cats_ups_list__charge_time__isys_unit_of_time__id',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'charge_time_unit',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'charge_time',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_unit_of_time',
                            'isys_unit_of_time__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__UPS__CHARGE_TIME_UNIT_OF_TIME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_unit_of_time',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'autonomy_time'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__UPS__AUTONOMY_TIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Autonomy time under full load'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ups_list__autonomy_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__UPS__AUTONOMY_TIME',
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
                            ['time']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'autonomy_time_unit'
                    ]
                ]
            ),
            'autonomy_time_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_cats_ups_list__autonomy_time__isys_unit_of_time__id',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'autonomy_time_unit',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'autonomy_time',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_unit_of_time',
                            'isys_unit_of_time__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__UPS__AUTONOMY_TIME_UNIT_OF_TIME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_unit_of_time',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'description'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ups_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__UPS
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create_connector(
                    'isys_cats_ups_list',
                    $p_object_id
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['battery_type'][C__DATA__VALUE],
                    $p_category_data['properties']['amount'][C__DATA__VALUE],
                    $p_category_data['properties']['charge_time'][C__DATA__VALUE],
                    $p_category_data['properties']['autonomy_time'][C__DATA__VALUE],
                    $p_category_data['properties']['charge_time_unit'][C__DATA__VALUE],
                    $p_category_data['properties']['autonomy_time_unit'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function
} // class