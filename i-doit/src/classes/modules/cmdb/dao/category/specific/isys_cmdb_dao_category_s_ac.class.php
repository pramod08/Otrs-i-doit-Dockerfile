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
 * DAO: specific category for air conditioners.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_ac extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'ac';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

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
        $p_data['height']   = isys_convert::measure($p_data['height'], $p_data['dimension_unit']) ?: 0;
        $p_data['width']    = isys_convert::measure($p_data['width'], $p_data['dimension_unit']) ?: 0;
        $p_data['depth']    = isys_convert::measure($p_data['depth'], $p_data['dimension_unit']) ?: 0;
        $p_data['capacity'] = isys_convert::watt($p_data['capacity'], $p_data['capacity_unit']);

        return parent::create_data($p_data);
    } // function

    /**
     * Method for returning the properties.
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => 'LC__CATS__AC_TYPE'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_ac_type'
                        ]
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ac_list__isys_ac_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ac_type',
                            'isys_ac_type__id'
                        ]
                    ]
                ]
            ),
            'threshold'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => 'LC__CATS__AC_THRESHOLD'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large',
                            'p_strTable' => 'isys_ac_type',
                        ]
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__threshold',
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__UNIT => 'threshold_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'threshold_unit'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => 'LC__CMDB_CATG__MEMORY_UNIT'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_THRESHOLD_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_temp_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                        ]
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ac_list__isys_temp_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_temp_unit',
                            'isys_temp_unit__id'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'capacity_unit'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'isys_capacity_unit',
                        C__PROPERTY__INFO__DESCRIPTION => 'Capacity unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ac_list__isys_ac_refrigerating_capacity_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ac_refrigerating_capacity_unit',
                            'isys_ac_refrigerating_capacity_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_REFRIGERATING_CAPACITY_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_ac_refrigerating_capacity_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'capacity'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => 'LC__CATS__AC_REFRIGERATING_CAPACITY'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_REFRIGERATING_CAPACITY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large',
                        ]
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__capacity'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['watt']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'capacity_unit'
                    ]
                ]
            ),
            'air_quantity'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATS__AC_AIR_QUANTITY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Air quantity'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__air_quantity'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_AIR_QUANTITY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__UNIT => 'air_quantity_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'air_quantity_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'isys_volume_unit',
                        C__PROPERTY__INFO__DESCRIPTION => 'Volume unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ac_list__isys_ac_air_quantity_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ac_air_quantity_unit',
                            'isys_ac_air_quantity_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_AIR_QUANTITY_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_ac_air_quantity_unit',
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
            'width'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__RACK_WIDTH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Width'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__width'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_DIMENSIONS_WIDTH',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
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
                            ['measure']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'dimension_unit'
                    ]
                ]
            ),
            'height'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__RACK_HEIGHT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Height'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__height'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_DIMENSIONS_HEIGHT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-mini',
                            'p_bInfoIconSpacer' => 0
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
                            ['measure']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'dimension_unit'
                    ]
                ]
            ),
            'depth'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__RACK_DEPTH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Depth'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__depth'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__AC_DIMENSIONS_DEPTH',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-mini',
                            'p_bInfoIconSpacer' => 0,
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
                            ['measure']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'dimension_unit'
                    ]
                ]
            ),
            'dimension_unit'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'isys_depth_unit',
                        C__PROPERTY__INFO__DESCRIPTION => 'Dimension unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_ac_list__isys_depth_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_depth_unit',
                            'isys_depth_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATS__AC_DIMENSIONS_UNIT',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable'   => 'isys_depth_unit',
                            'p_strStyle'   => 'width:87px;',
                            'p_bDbFieldNN' => 1
                        ],
                        C__PROPERTY__UI__DEFAULT => C__DEPTH_UNIT__INCH
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'description'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__AC
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
        $p_data['height']   = isys_convert::measure($p_data['height'], $p_data['dimension_unit']);
        $p_data['width']    = isys_convert::measure($p_data['width'], $p_data['dimension_unit']);
        $p_data['depth']    = isys_convert::measure($p_data['depth'], $p_data['dimension_unit']);
        $p_data['capacity'] = isys_convert::watt($p_data['capacity'], $p_data['capacity_unit']);

        return parent::save_data($p_category_data_id, $p_data);
    } // function
} // class