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
 * DAO: specific category for emergency power suppliers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_eps extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'eps';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the formatted autonomy time.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_autonomy_time($p_row)
    {
        global $g_comp_database;

        $l_eps_row       = isys_cmdb_dao_category_s_eps::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row();
        $l_autonomy_time = isys_factory_cmdb_dialog_dao::get_instance('isys_unit_of_time', $g_comp_database)
            ->get_data($l_eps_row['isys_cats_eps_list__autonomy_time__isys_unit_of_time__id']);

        return isys_convert::time(
            $l_eps_row['isys_cats_eps_list__autonomy_time'],
            $l_eps_row['isys_cats_eps_list__autonomy_time__isys_unit_of_time__id'],
            C__CONVERT_DIRECTION__BACKWARD
        ) . ' ' . $l_autonomy_time['title'];
    } // function

    /**
     * Dynamic property handling for getting the formatted warmup time.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_warmup_time($p_row)
    {
        global $g_comp_database;

        $l_eps_row     = isys_cmdb_dao_category_s_eps::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row();
        $l_warmup_time = isys_factory_cmdb_dialog_dao::get_instance('isys_unit_of_time', $g_comp_database)
            ->get_data($l_eps_row['isys_cats_eps_list__warmup_time__isys_unit_of_time__id']);

        return isys_convert::time(
            $l_eps_row['isys_cats_eps_list__warmup_time'],
            $l_eps_row['isys_cats_eps_list__warmup_time__isys_unit_of_time__id'],
            C__CONVERT_DIRECTION__BACKWARD
        ) . ' ' . $l_warmup_time['title'];
    } // function

    /**
     * Dynamic property handling for getting the formatted fuel tank.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_fuel_tank($p_row)
    {
        global $g_comp_database;

        $l_eps_row = isys_cmdb_dao_category_s_eps::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        return isys_convert::volume(
            $l_eps_row['isys_cats_eps_list__fuel_tank'],
            $l_eps_row['isys_cats_eps_list__isys_volume_unit__id'],
            C__CONVERT_DIRECTION__BACKWARD
        ) . ' ' . _L($l_eps_row['isys_volume_unit__title']);
    } // function

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        $p_data['fuel_tank']     = isys_convert::volume($p_data['fuel_tank'], $p_data['volume_unit']);
        $p_data['warmup_time']   = isys_convert::time($p_data['warmup_time'], $p_data['warmup_time_unit']);
        $p_data['autonomy_time'] = isys_convert::time($p_data['autonomy_time'], $p_data['autonomy_time_unit']);

        return parent::create_data($p_data);
    } // function

    /**
     * Method for retrieving the dynamic properties of this dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_autonomy_time' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__AUTONOMY_TIME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Autonomy time'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_autonomy_time'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_warmup_time'   => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__WARMUP_TIME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Warmup time'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_warmup_time'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_fuel_tank'     => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__FUEL_TANK',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fuel tank'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_fuel_tank'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @todo    Dynamic properties for "warmup_time", "fuel_tank" and "autonomy_time".
     */
    protected function properties()
    {
        return [
            'type'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__POBJ_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_eps_list__isys_cats_eps_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_eps_type',
                            'isys_cats_eps_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__POBJ_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cats_eps_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'warmup_time'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__WARMUP_TIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Warmup time'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_eps_list__warmup_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__EPS__WARMUP_TIME'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['time']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'warmup_time_unit'
                    ]
                ]
            ),
            'warmup_time_unit'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_eps_list__warmup_time__isys_unit_of_time__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_unit_of_time',
                            'isys_unit_of_time__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__EPS__WARMUP_TIME_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_unit_of_time'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'fuel_tank'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__FUEL_TANK',
                        C__PROPERTY__INFO__DESCRIPTION => 'Fuel tank'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_eps_list__fuel_tank'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__EPS__FUEL_TANK'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['volume']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'volume_unit'
                    ]
                ]
            ),
            'volume_unit'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_eps_list__isys_volume_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_volume_unit',
                            'isys_volume_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__EPS__FUEL_TANK_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_volume_unit'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'autonomy_time'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__AUTONOMY_TIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Autonomy time'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_eps_list__autonomy_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__EPS__AUTONOMY_TIME'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
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
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_eps_list__autonomy_time__isys_unit_of_time__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_unit_of_time',
                            'isys_unit_of_time__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__EPS__AUTONOMY_TIME_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_unit_of_time'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
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
                        C__PROPERTY__DATA__FIELD => 'isys_cats_eps_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__EPS
                    ]
                ]
            )
        ];
    } // function

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['fuel_tank']     = isys_convert::volume($p_data['fuel_tank'], $p_data['volume_unit']);
        $p_data['warmup_time']   = isys_convert::time($p_data['warmup_time'], $p_data['warmup_time_unit']);
        $p_data['autonomy_time'] = isys_convert::time($p_data['autonomy_time'], $p_data['autonomy_time_unit']);

        return parent::save_data($p_category_data_id, $p_data);
    } // function
} // class