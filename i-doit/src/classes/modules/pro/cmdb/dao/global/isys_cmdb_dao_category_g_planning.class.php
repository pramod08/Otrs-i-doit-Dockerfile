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
 * DAO: global category for status plans.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_planning extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'planning';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the "start" date.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_start($p_row)
    {
        if (isset($p_row['isys_catg_planning_list__start']) && $p_row['isys_catg_planning_list__start'] > 0)
        {
            return isys_locale::get_instance()
                ->fmt_date($p_row['isys_catg_planning_list__start']);
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Dynamic property handling for getting the "end" date.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_end($p_row)
    {
        if (isset($p_row['isys_catg_planning_list__end']) && $p_row['isys_catg_planning_list__end'] > 0)
        {
            return isys_locale::get_instance()
                ->fmt_date($p_row['isys_catg_planning_list__end']);
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function create_data($p_data)
    {
        $p_data['start'] = strtotime($p_data['start']);
        $p_data['end']   = strtotime($p_data['end']);

        return parent::create_data($p_data);
    } // function

    /**
     * Method for retrieving the dynamic properties.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_start' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__VALIDITY_FROM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Validity period from'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_planning_list__start'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_start'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_end'   => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__VALIDITY_TO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Validity period to'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_planning_list__end'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_end'
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
            'cmdb_status' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CMDB_STATUS',
                        C__PROPERTY__INFO__DESCRIPTION => 'CMDB status'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_planning_list__isys_cmdb_status__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cmdb_status',
                            'isys_cmdb_status__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PLANNING__STATUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cmdb_status'
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
            // @todo  Convert to "datetime" and add a dynamic property
            'start'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__VALIDITY_FROM',
                        C__PROPERTY__INFO__DESCRIPTION => 'Validity period from'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_planning_list__start'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PLANNING__START',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'timestamp'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            // @todo  Convert to "datetime" and add a dynamic property
            'end'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__VALIDITY_TO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Validity period to'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_planning_list__end'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PLANNING__END',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'timestamp'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_planning_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__PLANNING
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
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['start'] = strtotime($p_data['start']);
        $p_data['end']   = strtotime($p_data['end']);

        return parent::save_data($p_category_data_id, $p_data);
    } // function
} // class