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
 * DAO: global category for Nagios
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_group extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_group';

    /**
     * This should be created generically, but somehow... It does not.
     *
     * @var  integer
     */
    protected $m_category_const = 'C__CATG__NAGIOS_GROUP';

    /**
     * This should be created generically, but somehow... It does not.
     *
     * @var  integer
     */
    protected $m_category_id = C__CATG__NAGIOS_GROUP;

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * General Callback method for dialog fields in the nagios category.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_type(isys_request $p_request)
    {
        return [
            C__CATG_NAGIOS_GROUP__TYPE_HOST    => 'LC__CATG__NAGIOS_GROUP_HOSTGROUP',
            C__CATG_NAGIOS_GROUP__TYPE_SERVICE => 'LC__CATG__NAGIOS_GROUP_SERVICEGROUP',
        ];
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
            'is_exportable'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_CONFIG_EXPORT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export this configuration'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__is_exportable'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_GROUP_IS_EXPORTABLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'type'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_GROUP_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Group type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__type'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_GROUP_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_group',
                                    'callback_property_type'
                                ]
                            )
                        ]
                    ]
                ]
            ),
            'name'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'display_name',
                        C__PROPERTY__INFO__DESCRIPTION => 'display_name'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__name'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_GROUP_NAME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-radio'
                        ]
                    ]
                ]
            ),
            'name_selection' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'display_name_selection',
                        C__PROPERTY__INFO__DESCRIPTION => 'display_name selection'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__name_selection'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_GROUP_NAME_SELECTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ]
                ]
            ),
            'alias'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'Alias',
                        C__PROPERTY__INFO__DESCRIPTION => 'Alias'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__alias'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_GROUP_ALIAS'
                    ]
                ]
            ),
            'notes'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'Notes',
                        C__PROPERTY__INFO__DESCRIPTION => 'notes'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__notes'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_GROUP_NOTES'
                    ]
                ]
            ),
            'notes_url'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'Notes URL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Notes URL'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__notes_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_GROUP_NOTES_URL'
                    ]
                ]
            ),
            'action_url'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'Action URL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Action URL'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__action_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_GROUP_ACTION_URL'
                    ]
                ]
            ),
            'description'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_group_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__NAGIOS_GROUP
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            )
        ];
    } // function
} // class
?>