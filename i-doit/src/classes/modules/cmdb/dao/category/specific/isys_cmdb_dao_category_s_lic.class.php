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

define('C__CATS__LICENCE_TYPE__SINGLE_LICENCE', 1);
define('C__CATS__LICENCE_TYPE__VOLUME_LICENCE', 2);

/**
 * i-doit
 *
 * DAO: specific category for license lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_lic extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'lic';
    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__LICENCE_LIST';
    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CMDB__SUBCAT__LICENCE_LIST;
    /**
     * @var string
     */
    protected $m_entry_identifier = 'key';
    /**
     * Is the category multi-valued?
     *
     * @var  bool
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the amount of used licenses.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_used_licenses($p_row)
    {
        global $g_comp_database;

        $l_dao_licence = new isys_cmdb_dao_licences($g_comp_database, $p_row['isys_obj__id']);

        return count($l_dao_licence->get_licences_in_use(C__RECORD_STATUS__NORMAL)) . ' / ' . $l_dao_licence->calculate_sum();
    } // function

    /**
     * Dynamic property handling for getting the amount of free licenses.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_free_licenses($p_row)
    {
        global $g_comp_database;

        $l_dao_licence = new isys_cmdb_dao_licences($g_comp_database, $p_row['isys_obj__id']);

        return $l_dao_licence->calculate_sum() - count($l_dao_licence->get_licences_in_use(C__RECORD_STATUS__NORMAL));
    } // function

    /**
     * Callback method for the licence type.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_type(isys_request $p_request)
    {
        return [
            C__CATS__LICENCE_TYPE__SINGLE_LICENCE => 'LC__CMDB__CATS__LICENCE_TYPE__SINGLE',
            C__CATS__LICENCE_TYPE__VOLUME_LICENCE => 'LC__CMDB__CATS__LICENCE_TYPE__VOLUME'
        ];
    } // function

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param   integer $p_context
     * @param   array   $p_parameters
     *
     * @return  string  A JSON Encoded array with all the contents of the second list.
     * @return  array   A PHP Array with the preselections for category, first- and second list.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        global $g_comp_database;

        $l_lic_dao = new isys_cmdb_dao_licences($g_comp_database, (int) $_GET[C__CMDB__GET__OBJECT]);

        switch ($p_context)
        {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                try
                {
                    $l_licences = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);
                }
                catch (isys_exception_dao_cmdb $l_e)
                {
                    die($l_e->getMessage());
                } // try

                if ($l_licences->num_rows() > 0)
                {
                    while ($l_line = $l_licences->get_row())
                    {
                        $l_title = $l_line['isys_obj__title'];

                        if (!empty($l_line['isys_cats_lic_list__key']))
                        {
                            $l_title = $l_line['isys_cats_lic_list__key'];
                        } // if

                        $l_free_licences = $l_line['isys_cats_lic_list__amount'] - $l_lic_dao->get_licences_in_use(C__RECORD_STATUS__NORMAL, $l_line['isys_cats_lic_list__id'])
                                ->num_rows();

                        if ($l_free_licences < 0)
                        {
                            $l_free_licences = 0;
                        } // if

                        // Prepare return array.
                        $l_return[] = [
                            '__checkbox__'                                             => $l_line['isys_cats_lic_list__id'],
                            isys_glob_utf8_encode(_L('LC__CMDB__CATS__LICENCE_TITLE')) => isys_glob_utf8_encode($l_title),
                            isys_glob_utf8_encode(_L('LC__UNIVERSAL__AVAILABLE'))      => $l_free_licences . ' / ' . $l_line['isys_cats_lic_list__amount'],
                        ]; // $l_line;
                    } // while
                } // if

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => C__OBJTYPE__LICENCE,
                    'first'    => [],
                    'second'   => []
                ];

                if ($p_parameters['preselection'] > 0)
                {
                    // Save a bit memory: Only select needed fields!
                    $l_sql = "SELECT item.isys_cats_lic_list__amount, obj.isys_obj__id, obj.isys_obj__isys_obj_type__id, obj.isys_obj__title, obj.isys_obj__sysid " . "FROM isys_cats_lic_list AS item " . "LEFT JOIN isys_obj AS obj " . "ON isys_cats_lic_list__isys_obj__id = obj.isys_obj__id " . "WHERE item.isys_cats_lic_list__status = " . C__RECORD_STATUS__NORMAL . " AND item.isys_cats_lic_list__id = " . $this->convert_sql_id(
                            $p_parameters['preselection']
                        ) . " LIMIT 1;";

                    $l_dao_result = new isys_component_dao($g_comp_database);
                    $l_res        = $l_dao_result->retrieve($l_sql);

                    if ($l_res->num_rows() == 1)
                    {
                        // Lizenzinfo.
                        $l_row = $l_res->get_row();

                        $l_type = 0;

                        $l_sql2 = "SELECT isys_obj_type__title FROM isys_obj_type WHERE isys_obj_type__id = " . $this->convert_sql_id(
                                $l_row['isys_obj__isys_obj_type__id']
                            ) . " LIMIT 1";
                        $l_res2 = $l_dao_result->retrieve($l_sql2);
                        if ($l_res2->num_rows() == 1)
                        {
                            $l_type = _L($l_res2->get_row_value('isys_obj_type__title'));
                        } // if

                        $l_free_licences = $l_row['isys_cats_lic_list__amount'] - $l_lic_dao->get_licences_in_use(C__RECORD_STATUS__NORMAL, $p_parameters['preselection'])
                                ->num_rows();

                        if ($l_free_licences < 0)
                        {
                            $l_free_licences = 0;
                        } // if

                        // Prepare return data.
                        $l_return['first'] = [
                            $l_row['isys_obj__id'],
                            isys_glob_utf8_encode($l_row['isys_obj__title']),
                            isys_glob_utf8_encode($l_type),
                            isys_glob_utf8_encode($l_row['isys_obj__sysid'])
                        ];

                        $l_return['second'] = [
                            $p_parameters['preselection'],
                            isys_glob_utf8_encode($l_row['isys_cats_lic_list__title']),
                            $l_free_licences . ' / ' . $l_row['isys_cats_lic_list__amount'],
                        ];
                    } // if
                } // if

                return $l_return;
        } // switch
    } // function

    /**
     * This method returns the formatted text for the selection.
     *
     * @param   integer $p_license_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_license_id)
    {
        $p_license_id = (int) $p_license_id;

        if ($p_license_id > 0)
        {
            $l_sql = "SELECT isys_obj__title, isys_cats_lic_list__key
				FROM isys_cats_lic_list
				LEFT JOIN isys_obj ON isys_obj__id = isys_cats_lic_list__isys_obj__id
				WHERE isys_cats_lic_list__id = " . $this->convert_sql_id($p_license_id) . ";";

            $l_license = $this->retrieve($l_sql);

            if ($l_license->num_rows() == 1)
            {
                // Lizenzinfo.
                $l_licenseData = $l_license->get_row();
                $l_licTitle    = $l_licenseData["isys_obj__title"];

                if (!empty($l_licenseData["isys_cats_lic_list__key"]))
                {
                    $l_licTitle .= ' >> ' . $l_licenseData["isys_cats_lic_list__key"];
                }

                return $l_licTitle;
            } // if
        } // if

        return _L('LC_UNIVERSAL__NONE_SELECTED') . '!';
    } // function

    /**
     * Method for retrieving the dynamic properties of every category dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_used_licenses' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_IN_USE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Licenses in Use'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_used_licenses'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_free_licenses' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_FREE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Free licenses'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_free_licenses'
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
            'key'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_KEY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Key'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__key'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__LICENCE_KEY'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'serial'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_SERIAL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Serial'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__serial'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__LICENCE_SERIAL'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'type'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Licence type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__type'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__LICENCE_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_lic',
                                    'callback_property_type'
                                ]
                            ),
                            'p_bDbFieldNN' => 1
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'amount'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_AMOUNT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Amount'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__amount'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__LICENCE_AMOUNT'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'lic_not_in_use' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_FREE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Free licences'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__id'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'licence_property_lic_not_in_use'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ]
                ]
            ),
            'start'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_START',
                        C__PROPERTY__INFO__DESCRIPTION => 'Start Date'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__start'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__LICENCE_START',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'calendar'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'expire'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_EXPIRE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Expiration Date'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__expire'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__LICENCE_EXPIRE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'calendar'
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'cost'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::money(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__COSTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Costs'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__cost'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__LICENCE_COST'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'overall_costs'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::money(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_COST',
                        C__PROPERTY__INFO__DESCRIPTION => 'Overall costs'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__id'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'licence_property_overall_costs'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CMDB__SUBCAT__LICENCE_LIST
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            )
        ];
    } // function
} // class