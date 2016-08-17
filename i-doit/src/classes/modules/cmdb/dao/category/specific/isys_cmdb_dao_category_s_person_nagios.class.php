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
 * DAO: specific category for Nagios related persons.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_person_nagios extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_nagios';

    /**
     * Get data method.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *
			FROM isys_cats_person_nagios_list
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_cats_list_id !== null)
        {
            $l_sql .= ' AND isys_cats_person_nagios_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_cats_person_nagios_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id May be an integer or an array of integers.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                return ' AND isys_cats_person_nagios_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id);
            }
            else
            {
                return ' AND isys_cats_person_nagios_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
            } // if
        } // if

        return '';
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        $l_dialog_yes_no = serialize(get_smarty_arr_YES_NO());

        return [
            'alias'                            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NAGIOS_LIST_ALIAS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Alias'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__alias'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'CONTACT_ALIAS'
                    ]
                ]
            ),
            'contact_name'                     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'contact_name',
                        C__PROPERTY__INFO__DESCRIPTION => 'contact_name'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__contact_name'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'CONTACT_NAME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ]
                ]
            ),
            'contact_name_selection'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'contact_name selection',
                        C__PROPERTY__INFO__DESCRIPTION => 'contact_name selection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__contact_name_selection'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'CONTACT_NAME_SELECTION'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'host_notification_enabled'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'host_notification_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_notification_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__host_notification_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'CONTACT_HOST_NOTIFICATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $l_dialog_yes_no
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
            'service_notification_enabled'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'service_notification_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'service_notification_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__service_notification_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'CONTACT_SERVICE_NOTIFICATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $l_dialog_yes_no
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
            'host_notification_period'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'host_notification_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_notification_period'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__host_notification_period'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'CONTACT_HOST_NOTIFICATION_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getTimeperiodsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'notification_period'
                        ]
                    ]
                ]
            ),
            'host_notification_period_plus'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'host_notification_period+',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_notification_period+'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_person_nagios_list__host_notification_period_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'CONTACT_HOST_NOTIFICATION_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ]
                ]
            ),
            'service_notification_period'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'service_notification_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'service_notification_period'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__service_notification_period'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'CONTACT_SERVICE_NOTIFICATION_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getTimeperiodsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'notification_period'
                        ]
                    ]
                ]
            ),
            'service_notification_period_plus' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'service_notification_period+',
                        C__PROPERTY__INFO__DESCRIPTION => 'service_notification_period+'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_person_nagios_list__service_notification_period_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'CONTACT_SERVICE_NOTIFICATION_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ]
                ]
            ),
            'host_notification_options'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'host_notification_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_notification_options'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT,
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__host_notification_options'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'HOST_NOTIFICATION_OPTIONS'
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_notification_options'
                        ]
                    ]
                ]
            ),
            'service_notification_options'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'service_notification_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'service_notification_options'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT,
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__service_notification_options'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'SERVICE_NOTIFICATION_OPTIONS'
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'service_notification_options'
                        ]
                    ]
                ]
            ),
            'host_notification_commands'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'host_notification_commands',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_notification_commands'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT,
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__host_notification_commands'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'CONTACT_HOST_NOTIFICATION_COMMANDS'
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_notification_commands'
                        ]
                    ]
                ]
            ),
            'service_notification_commands'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'service_notification_commands',
                        C__PROPERTY__INFO__DESCRIPTION => 'service_notification_commands'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT,
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__service_notification_commands'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'CONTACT_SERVICE_NOTIFICATION_COMMANDS'
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'service_notification_commands'
                        ]
                    ]
                ]
            ),
            'can_submit_commands'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'can_submit_commands',
                        C__PROPERTY__INFO__DESCRIPTION => 'can_submit_commands'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__can_submit_commands'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'CONTACT_CAN_SUBMIT_COMMANDS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $l_dialog_yes_no
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
            'retain_status_information'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'retain_status_information',
                        C__PROPERTY__INFO__DESCRIPTION => 'retain_status_information'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__retain_status_information'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'CONTACT_RETAIN_STATUS_INFORMATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $l_dialog_yes_no
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
            'is_exportable'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_CONFIG_EXPORT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export this configuration'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__is_exportable',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'CONTACT_NAGIOS_IS_EXPORTABLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $l_dialog_yes_no
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'retain_nonstatus_information'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'retain_nonstatus_information',
                        C__PROPERTY__INFO__DESCRIPTION => 'retain_nonstatus_information'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__retain_nonstatus_information'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'CONTACT_RETAIN_NONSTATUS_INFORMATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => $l_dialog_yes_no
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
            'custom_object_vars'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'custom_object_vars',
                        C__PROPERTY__INFO__DESCRIPTION => 'custom_object_vars'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__custom_obj_vars'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATS__PERSON_NAGIOS__CUSTOM_OBJ_VARS'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__SANITIZATION => null
                    ]
                ]
            ),
            'description'                      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_nagios_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__PERSON_NAGIOS
                    ]
                ]
            )
        ];
    } // function
} // class
?>