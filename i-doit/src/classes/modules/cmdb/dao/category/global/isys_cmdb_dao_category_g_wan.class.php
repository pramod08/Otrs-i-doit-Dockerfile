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
 * CMDB DAO class for the WAN category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_wan extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'wan';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function properties()
    {
        return [
            'title'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__TITLE'
                    ]
                ]
            ),
            'role'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__ROLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Role'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__isys_wan_role__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_wan_role',
                            'isys_wan_role__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__ROLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_wan_role',
                            'p_strClass' => 'input-small'
                        ]
                    ]
                ]
            ),
            'type'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__isys_wan_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_wan_type',
                            'isys_wan_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_wan_type',
                            'p_strClass' => 'input-small'
                        ]
                    ]
                ]
            ),
            'channels'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CHANNELS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Channels'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__channels'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__CHANNELS'
                    ]
                ]
            ),
            'call_numbers'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CALL_NUMBERS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Call numbers'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__call_numbers'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__CALL_NUMBERS'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__SANITIZATION => null
                        // This is necessary to keep linebreaks
                    ]
                ]
            ),
            'connection_location'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CONNECTION_LOCATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connection location'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__connection_location',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj',
                            'isys_obj__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__CONNECTION_LOCATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'browser_location'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'capacity_up'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_UP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Capacity up'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__capacity_up'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_UP',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['speed_wan']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'capacity_up_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'capacity_up_unit'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_UP_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Capacity up unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__capacity_up_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_wan_capacity_unit',
                            'isys_wan_capacity_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_UP_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                            'p_strTable'        => 'isys_wan_capacity_unit',
                            'p_bSort'           => false
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'capacity_down'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_DOWN',
                        C__PROPERTY__INFO__DESCRIPTION => 'Capacity down'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__capacity_down'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_DOWN',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['speed_wan']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'capacity_down_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'capacity_down_unit'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_DOWN_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Capacity down unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__capacity_down_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_wan_capacity_unit',
                            'isys_wan_capacity_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_DOWN_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                            'p_strTable'        => 'isys_wan_capacity_unit',
                            'p_bSort'           => false
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'max_capacity_up'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_UP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Max capacity up'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__max_capacity_up'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_UP',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['speed_wan']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'max_capacity_up_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'max_capacity_up_unit'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_UP_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Max capacity up unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__max_capacity_up_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_wan_capacity_unit',
                            'isys_wan_capacity_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_UP_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                            'p_strTable'        => 'isys_wan_capacity_unit',
                            'p_bSort'           => false
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'max_capacity_down'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_DOWN',
                        C__PROPERTY__INFO__DESCRIPTION => 'Max capacity down'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__max_capacity_down'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_DOWN',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['speed_wan']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'max_capacity_down_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'max_capacity_down_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_DOWN_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Max capacity down unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__max_capacity_down_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_wan_capacity_unit',
                            'isys_wan_capacity_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_DOWN_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0,
                            'p_strTable'        => 'isys_wan_capacity_unit',
                            'p_bSort'           => false
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'project_no'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__PROJECT_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Project number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__project_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__PROJECT_NO'
                    ]
                ]
            ),
            'vlan_id'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__VLAN_ID',
                        C__PROPERTY__INFO__DESCRIPTION => 'VLAN-ID'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__vlan',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj',
                            'isys_obj__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__VLAN_ID',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__LAYER2_NET;C__CATS__LAYER2_NET_ASSIGNED_PORTS;C__CATS__LAYER2_NET_ASSIGNED_LOGICAL_PORTS',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'shopping_cart_no'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__SHOPPING_CART_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Shopping cart number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__shopping_cart_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__SHOPPING_CART_NO'
                    ]
                ]
            ),
            'ticket_no'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__TICKET_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Ticket number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__ticket_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__TICKET_NO'
                    ]
                ]
            ),
            'customer_no'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CUSTOMER_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Customer number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__customer_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__WAN__CUSTOMER_NO'
                    ]
                ]
            ),
            'router'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__ROUTER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connected routers'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_wan_list_2_router',
                            'isys_catg_wan_list_2_router__isys_catg_wan_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__ROUTER',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__ROUTER',
                            isys_popup_browser_object_ng::C__MULTISELECTION => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'wan_connected_router'
                        ]
                    ]
                ]
            ),
            'net'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__NET',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connected nets'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_wan_list_2_net',
                            'isys_catg_wan_list_2_net__isys_catg_wan_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__WAN__NET',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__NET',
                            isys_popup_browser_object_ng::C__MULTISELECTION => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'wan_connected_net'
                        ]
                    ]
                ]
            ),
            'description'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__WAN
                    ]
                ]
            )
        ];
    } // function

    /**
     * Abstract method for retrieving the dynamic properties.
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        $l_return = [
            '_capacity_up'       => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_UP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity up'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_capacity_up'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_max_capacity_up'   => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_UP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity up'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_max_capacity_up'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_capacity_down'     => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_DOWN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity up'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_capacity_down'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_max_capacity_down' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_DOWN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity up'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_wan_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_max_capacity_down'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]
        ];

        return $l_return;
    } // function

    /**
     * Helper method which formats the value to the specified unit.
     *
     * @param integer $p_list_id
     * @param integer $p_obj_id
     * @param string $p_field
     * @param string $p_table
     *
     * @return string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_capacity_helper($p_list_id = null, $p_obj_id = null, $p_field = '', $p_table = 'isys_wan_capacity_unit')
    {
        if ($p_list_id !== null || $p_obj_id !== null)
        {
            $l_db   = isys_application::instance()->database;
            $l_dao  = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', $l_db);
            $l_data = $l_dao->get_data($p_list_id, $p_obj_id)
                ->get_row();

            $l_value = $l_data[$p_field];
            if ($l_value > 0)
            {
                $l_unit_field  = $p_field . '_unit';
                $l_dao_dialog  = isys_factory_cmdb_dialog_dao::get_instance($p_table, $l_db);
                $l_unit_id     = ($l_data[$l_unit_field] > 0) ? $l_data[$l_unit_field] : C__WAN_CAPACITY_UNIT__KBITS;
                $l_data_dialog = $l_dao_dialog->get_data($l_unit_id);
                $l_unit        = $l_data_dialog['isys_wan_capacity_unit__title'];

                return isys_convert::speed_wan($l_value, $l_unit_id, C__CONVERT_DIRECTION__BACKWARD) . ' ' . $l_unit;
            } // if
        } // if
        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Dynamic callback function for property capacity_up.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_capacity_up($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao    = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->database);

        return $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__capacity_up'
        );
    } // function

    /**
     * Dynamic callback function for property max_capacity_up.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_max_capacity_up($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao    = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->database);
        $l_return = $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__max_capacity_up'
        );

        return $l_return;
    } // function

    /**
     * Dynamic callback function for property capacity_down.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_capacity_down($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao    = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->database);
        $l_return = $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__capacity_down'
        );

        return $l_return;
    } // function

    /**
     * Dynamic callback function for property max_capacity_down.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_max_capacity_down($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao    = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->database);
        $l_return = $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__max_capacity_down'
        );

        return $l_return;
    } // function

    /**
     * Create a new entity.
     *
     * @param   array $p_data
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create_data($p_data)
    {
        $p_data['capacity_up']       = isys_convert::speed_wan($p_data['capacity_up'], $p_data['capacity_up_unit']);
        $p_data['capacity_down']     = isys_convert::speed_wan($p_data['capacity_down'], $p_data['capacity_down_unit']);
        $p_data['max_capacity_up']   = isys_convert::speed_wan($p_data['max_capacity_up'], $p_data['max_capacity_up_unit']);
        $p_data['max_capacity_down'] = isys_convert::speed_wan($p_data['max_capacity_down'], $p_data['max_capacity_down_unit']);

        $l_result = parent::create_data($p_data);

        $l_router = null;
        $l_net    = null;

        // If the result is not false, we connect the routers and nets.
        if ($l_result && is_numeric($l_result))
        {
            if (isset($p_data['router']))
            {
                $l_router = $p_data['router'];
            } // if

            if (isset($p_data['net']))
            {
                $l_net = $p_data['net'];
            } // if

            $this->assign_router_net($l_result, $l_router, $l_net);
        } // if

        return $l_result;
    } // function

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id
     * @param   array   $p_data
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['capacity_up']       = isys_convert::speed_wan($p_data['capacity_up'], $p_data['capacity_up_unit']);
        $p_data['capacity_down']     = isys_convert::speed_wan($p_data['capacity_down'], $p_data['capacity_down_unit']);
        $p_data['max_capacity_up']   = isys_convert::speed_wan($p_data['max_capacity_up'], $p_data['max_capacity_up_unit']);
        $p_data['max_capacity_down'] = isys_convert::speed_wan($p_data['max_capacity_down'], $p_data['max_capacity_down_unit']);

        $l_result = parent::save_data($p_category_data_id, $p_data);
        $l_router = null;
        $l_net    = null;

        // If the result is not false, we connect the routers and nets.
        if ($l_result)
        {
            if (isset($p_data['router']))
            {
                $l_router = $p_data['router'];
            } // if

            if (isset($p_data['net']))
            {
                $l_net = $p_data['net'];
            } // if

            $this->assign_router_net($p_category_data_id, $l_router, $l_net);
        } // if

        return $l_result;
    } // function

    /**
     * Method for retrieving all connected routers.
     *
     * @param   integer $p_cat_entry_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_connected_routers($p_cat_entry_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_wan_list_2_router
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_wan_list_2_router__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_wan_list_2_router__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for setting certain routers to a given WAN-category entry.
     *
     * @param   integer $p_cat_entry_id
     * @param   mixed   $p_routers
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_connect_routers($p_cat_entry_id, $p_routers)
    {
        if (!is_numeric($p_cat_entry_id) || !$p_cat_entry_id)
        {
            return true;
        } // if

        if (!is_array($p_routers))
        {
            $p_routers = [$p_routers];
        } // if

        // Remove all assignments first
        $this->update('DELETE FROM isys_catg_wan_list_2_router WHERE isys_catg_wan_list_2_router__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . ';');

        if (count($p_routers))
        {
            // Assign all selected routers to the wan
            $l_items = [];
            $l_sql   = 'INSERT INTO isys_catg_wan_list_2_router (isys_catg_wan_list_2_router__isys_catg_wan_list__id, isys_catg_wan_list_2_router__isys_obj__id) VALUES ';
            foreach ($p_routers as $l_router)
            {
                $l_items[] = '(' . $this->convert_sql_id($p_cat_entry_id) . ', ' . $this->convert_sql_id($l_router) . ')';
            } // foreach
            return $this->update($l_sql . implode(', ', $l_items) . ';') && $this->apply_update();
        } // if

        return true;
    } // function

    /**
     * Method for retrieving all connected nets.
     *
     * @param   integer $p_cat_entry_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_connected_nets($p_cat_entry_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_wan_list_2_net
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_wan_list_2_net__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_wan_list_2_net__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for setting certain nets to a given WAN-category entry.
     *
     * @param   integer $p_cat_entry_id
     * @param   mixed   $p_nets
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_connect_nets($p_cat_entry_id, $p_nets)
    {
        if (!is_numeric($p_cat_entry_id) || !$p_cat_entry_id)
        {
            return true;
        } // if

        if (!is_array($p_nets))
        {
            $p_nets = [$p_nets];
        } // if

        // Remove all entries first
        $this->update('DELETE FROM isys_catg_wan_list_2_net WHERE isys_catg_wan_list_2_net__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . ';');

        if (count($p_nets))
        {
            // Assign all selected nets to the wan
            $l_items = [];
            $l_sql   = 'INSERT INTO isys_catg_wan_list_2_net (isys_catg_wan_list_2_net__isys_catg_wan_list__id, isys_catg_wan_list_2_net__isys_obj__id) VALUES ';

            foreach ($p_nets as $l_net)
            {
                $l_items[] = '(' . $this->convert_sql_id($p_cat_entry_id) . ', ' . $this->convert_sql_id($l_net) . ')';
            } // foreach
            return $this->update($l_sql . implode(', ', $l_items) . ';') && $this->apply_update();
        } // if

        return true;
    } // function

    /**
     * Helper method which assigns routers and nets to the WAN.
     *
     * @param integer $p_id
     * @param mixed   $p_router
     * @param mixed   $p_net
     *
     * @return $this
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function assign_router_net($p_id, $p_router = null, $p_net = null)
    {
        if ($p_router !== null)
        {
            $l_router = [];
            if (isys_format_json::is_json_array($p_router))
            {
                $l_router = array_filter(isys_format_json::decode($p_router));
            }
            elseif (is_array($p_router))
            {
                $l_router = $p_router;
            } // if

            $this->set_connect_routers($p_id, $l_router);
        } // if

        if ($p_net !== null)
        {
            $l_net = [];
            if (isys_format_json::is_json_array($p_net))
            {
                $l_net = array_filter(isys_format_json::decode($p_net));
            }
            elseif (is_array($p_net))
            {
                $l_net = $p_net;
            } // if

            $this->set_connect_nets($p_id, $l_net);
        } // if

        return $this;
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing has to be done, otherwise false.
     * @throws  isys_exception_validation
     */
    public function sync($p_category_data, $p_object_id, $p_status)
    {
        // If we are in "create" mode (or have no "data_id") simply try to retrieve it or create a new entry.
        if ($p_status == isys_import_handler_cmdb::C__CREATE || !isset($p_category_data['data_id']) || !$p_category_data['data_id'])
        {
            $l_result = $this->retrieve('SELECT isys_catg_wan_list__id FROM isys_catg_wan_list WHERE isys_catg_wan_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';');

            if (!count($l_result))
            {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_wan_list', $p_object_id);
            }
            else
            {
                $p_category_data['data_id'] = $l_result->get_row_value('isys_catg_wan_list__id');
            } // if

            $p_status = isys_import_handler_cmdb::C__UPDATE;
        } // if

        // Process assigned "router" and "net" objects.
        if ($p_category_data['data_id'] > 0)
        {
            if (isset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router']) && !empty($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router'][C__DATA__VALUE]))
            {
                $this->set_connect_routers($p_category_data['data_id'], $p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router'][C__DATA__VALUE]);

                // Unset the data so that our connection does not get messed up by the parent sync.
                unset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router']);
            } // if

            if (isset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net']) && !empty($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net'][C__DATA__VALUE]))
            {
                $this->set_connect_nets($p_category_data['data_id'], $p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net'][C__DATA__VALUE]);

                // Unset the data so that our connection does not get messed up by the parent sync.
                unset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net']);
            } // if
        } // if

        // Leave the rest to the generic sync method.
        return parent::sync($p_category_data, $p_object_id, $p_status);
    } // function
} // class