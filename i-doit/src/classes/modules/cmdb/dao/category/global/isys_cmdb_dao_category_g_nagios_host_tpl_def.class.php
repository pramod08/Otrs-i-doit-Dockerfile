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
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_host_tpl_def extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'nagios_host_tpl_def';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * General Callback method for dialog fields in the nagios category.
     *
     * @param   isys_request $p_request
     * @param   string       $p_method
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_general_dialog_nagios_methods(isys_request $p_request, $p_method)
    {
        $l_comp_dao_nagios = new isys_component_dao_nagios($this->get_database_component());
        $l_return          = null;

        if (is_array($p_method) && count($p_method) == 1)
        {
            $p_method = array_pop($p_method);
        }

        if (method_exists($l_comp_dao_nagios, $p_method))
        {
            $l_return = $l_comp_dao_nagios->$p_method();
        } // function

        return $l_return;
    } // function

    /**
     * General Callback method for dialog list fields in the nagios category.
     *
     * @param   isys_request $p_request
     * @param   string       $p_method
     * @param   string       $p_field
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_general_dialog_list_nagios_methods(isys_request $p_request, $p_method, $p_field = null)
    {
        $l_comp_dao_nagios = new isys_component_dao_nagios($this->get_database_component());
        $l_return          = null;

        if (is_array($p_method) && count($p_method) == 2)
        {
            $p_method = $p_method[0];
            $p_field  = $p_method[1];
        }

        $l_assigned_options = explode(
            ',',
            $this->get_data($p_request->get_category_data_id())
                ->get_row_value($p_field)
        );

        if (method_exists($l_comp_dao_nagios, $p_method))
        {
            $l_arr = $l_comp_dao_nagios->$p_method();

            foreach ($l_arr as $l_key => $l_val)
            {
                $l_return[] = [
                    "id"  => $l_key,
                    "val" => $l_val,
                    "sel" => in_array($l_key, $l_assigned_options),
                ];
            } // foreach
        } // if

        return $l_return;
    } // function

    /**
     * Save global category Nagios.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode    = -1;
        $l_catdata         = $this->get_general_data();
        $p_intOldRecStatus = $l_catdata["isys_catg_nagios_host_tpl_def_list__status"];

        $l_posts = $_POST;

        $l_arData = $this->build_sql_attributes($l_posts);

        $l_arData['isys_catg_nagios_host_tpl_def_list__description'] = $this->convert_sql_text(
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );
        $l_arData['isys_catg_nagios_host_tpl_def_list__status']      = $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($l_catdata['isys_catg_nagios_host_tpl_def_list__id']))
        {
            $l_bRet = $this->save($l_catdata['isys_catg_nagios_host_tpl_def_list__id'], $l_arData);
        }
        else
        {
            $l_bRet = $this->create($_GET[C__CMDB__GET__OBJECT], $l_arData);
        }
        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? null : $l_intErrorCode;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   array   $p_arData
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_cat_level, $p_arData)
    {
        $l_fields = [];

        foreach ($p_arData as $key => $value)
        {
            $l_fields[] = $key . ' = ' . $value;
        } // foreach

        if (count($l_fields) > 0)
        {
            $l_sql = 'UPDATE isys_catg_nagios_host_tpl_def_list SET ' . implode(', ', $l_fields) . ' WHERE isys_catg_nagios_host_tpl_def_list__id = ' . $this->convert_sql_id(
                    $p_cat_level
                ) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        } // if

        return true;
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catg_memory__id $p_fk_id.
     *
     * @param   integer $p_object_id
     * @param   array   $p_arData
     *
     * @return  mixed  Integer with the newly created ID or boolean false on failure.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_object_id, $p_arData)
    {
        $l_fields = [];

        if (count($p_arData) > 0)
        {
            if (!array_key_exists('isys_catg_nagios_host_tpl_def_list__is_exportable', $p_arData))
            {
                $p_arData['isys_catg_nagios_host_tpl_def_list__is_exportable'] = 1;
            }
            foreach ($p_arData as $key => $value)
            {
                $l_fields[] = $key . ' = ' . $value;
            } // foreach
        }

        $l_fields[] = 'isys_catg_nagios_host_tpl_def_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);
        if (!is_array($p_arData) || !array_key_exists('isys_catg_nagios_host_tpl_def_list__status', $p_arData))
        {
            $l_fields[] = 'isys_catg_nagios_host_tpl_def_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);
        }

        $l_sql = 'INSERT IGNORE INTO isys_catg_nagios_host_tpl_def_list SET ' . implode(', ', $l_fields) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getCatDataById($p_id)
    {
        return $this->retrieve(
            'SELECT * FROM isys_catg_nagios_host_tpl_def_list WHERE isys_catg_nagios_host_tpl_def_list__isys_obj__id = ' . $this->convert_sql_id($p_id) . ';'
        )
            ->get_row();
    } // function

    /**
     * Retrieve all nagios hosts.
     *
     * @param   boolean $p_only_exportable
     *
     * @return  isys_component_dao_result
     */
    public function getHosts($p_only_exportable = false)
    {
        $l_sql = 'SELECT * FROM isys_catg_nagios_host_tpl_def_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_host_tpl_def_list__isys_obj__id
			WHERE isys_obj__status = ' . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__isys_cmdb_status__id = ' . $this->convert_sql_int(C__CMDB_STATUS__IN_OPERATION);

        if ($p_only_exportable === true)
        {
            $l_sql .= ' AND isys_catg_nagios_host_tpl_def_list__is_exportable = 1';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) $l_obj_id = $p_obj_id;
        else $l_obj_id = $this->m_object_id;

        $l_sql = "SELECT count(isys_catg_nagios_host_tpl_def_list__id) AS count FROM isys_catg_nagios_host_tpl_def_list " . "WHERE TRUE ";

        if (!empty($l_obj_id))
        {
            $l_sql .= " AND (isys_catg_nagios_host_tpl_def_list__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_nagios_host_tpl_def_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'name'                         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__HOSTNAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__name1'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_NAME1'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'nagios_host'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NAGIOS__NAGIOS_HOST',
                        C__PROPERTY__INFO__DESCRIPTION => 'Nagios Host'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__export_host',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_monitoring_export_config',
                            'isys_monitoring_export_config__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_HOST',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getNagiosHostsAssoc']
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'max_check_attempts'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_HOST_TPL_DEF_MAX_CHECK_ATTEMPTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'max_check_attempts'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__max_check_attempts'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_MAX_CHECK_ATTEMPTS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'check_period'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__check_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods',
                            'isys_nagios_timeperiods__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getTimeperiodsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'check_period',
                            ['isys_nagios_timeperiods']
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'check_period_plus'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'check_period +',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_period +'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__check_period_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                ]
            ),
            'notification_interval'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_INTERVAL',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_interval'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__notification_interval'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'notification_period'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__notification_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods',
                            'isys_nagios_timeperiods__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getTimeperiodsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'notification_period',
                            ['isys_nagios_timeperiods']
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'notification_period_plus'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'notification_period +',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_period +'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__notification_period_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                ]
            ),
            'display_name'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'display_name',
                        C__PROPERTY__INFO__DESCRIPTION => 'display_name'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__display_name'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'display_name_selection'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'display_name_selection',
                        C__PROPERTY__INFO__DESCRIPTION => 'display_name selection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__display_name_selection'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME_SELECTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'check_command'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_command'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__check_command',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands',
                            'isys_nagios_commands__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getCommandsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0,
                            'p_onChange'        => "idoit.callbackManager.triggerCallback('nagios_tpl__check_command_description', this.id);"
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'check_command'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'check_command_plus'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command+',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_command+'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__check_command_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands_plus',
                            'isys_nagios_commands_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_commands_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'check_command_parameters'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command_parameters',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_command_parameters'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__check_command_parameters'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND_PARAMETERS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'initial_state'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'initial_state',
                        C__PROPERTY__INFO__DESCRIPTION => 'initial_state'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__initial_state',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_INITIAL_STATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getHostFlapDetectionOptionsAssoc']
                            )
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_initial_state'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'check_interval'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_interval',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_interval'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__check_interval'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'retry_interval'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'retry_interval',
                        C__PROPERTY__INFO__DESCRIPTION => 'retry_interval'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__retry_interval'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_RETRY_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'active_checks_enabled'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'active_checks_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'active_checks_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__active_checks_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_ACTIVE_CHECKS_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'passive_checks_enabled'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'passive_checks_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'passive_checks_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__passive_checks_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_PASSIVE_CHECKS_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'obsess_over_host'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'obsess_over_host',
                        C__PROPERTY__INFO__DESCRIPTION => 'obsess_over_host'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__obsess_over_host'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_OBSESS_OVER_HOST',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'check_freshness'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_freshness',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_freshness'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__check_freshness'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_FRESHNESS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'freshness_threshold'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'freshness_threshold',
                        C__PROPERTY__INFO__DESCRIPTION => 'freshness_threshold'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__freshness_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_FRESHNESS_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'flap_detection_enabled'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'flap_detection_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'flap_detection_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__flap_detection_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'flap_detection_options'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'flap_detection_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'flap_detection_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__flap_detection_options',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_OPTIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostFlapDetectionOptionsAssoc',
                                    'isys_catg_nagios_host_tpl_def_list__flap_detection_options'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__FLAP_DETECTION__EMPTY')
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_flap_detection_options'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'event_handler'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__event_handler'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getCommandsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0,
                            'p_onChange'        => "idoit.callbackManager.triggerCallback('nagios_tpl__check_command_description', this.id);"
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'event_handler'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'event_handler_plus'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler +',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler +'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_host_tpl_def_list__event_handler_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands_plus',
                            'isys_nagios_commands_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_commands_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'event_handler_parameters'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler_parameters',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler_parameters'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__event_handler_parameters'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_PARAMETERS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'event_handler_enabled'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__event_handler_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'low_flap_threshold'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'low_flap_threshold',
                        C__PROPERTY__INFO__DESCRIPTION => 'low_flap_threshold'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__low_flap_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_LOW_FLAP_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'high_flap_threshold'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'high_flap_threshold',
                        C__PROPERTY__INFO__DESCRIPTION => 'high_flap_threshold'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__high_flap_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_HIGH_FLAP_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'process_perf_data'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'process_perf_data',
                        C__PROPERTY__INFO__DESCRIPTION => 'process_perf_data'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__process_perf_data'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_PROCESS_PERF_DATA',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'retain_status_information'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'retain_status_information',
                        C__PROPERTY__INFO__DESCRIPTION => 'retain_status_information'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__retain_status_information'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_RETAIN_STATUS_INFORMATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'retain_nonstatus_information' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'retain_nonstatus_information',
                        C__PROPERTY__INFO__DESCRIPTION => 'retain_nonstatus_information'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__retain_nonstatus_information'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_RETAIN_NONSTATUS_INFORMATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'first_notification_delay'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'first_notification_delay',
                        C__PROPERTY__INFO__DESCRIPTION => 'first_notification_delay'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__first_notification_delay'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_FIRST_NOTIFICATION_DELAY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'host_notification_options'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__notification_options',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_OPTIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostNotificationOptionsAssoc',
                                    'isys_catg_nagios_host_tpl_def_list__notification_options'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__NOTIFICATION_OPTIONS__EMPTY')
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_notification_options'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'notifications_enabled'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notifications_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'notifications_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__notifications_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATIONS_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'stalking_options'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'stalking_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'stalking_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__stalking_options',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_STALKING_OPTIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostFlapDetectionOptionsAssoc',
                                    'isys_catg_nagios_host_tpl_def_list__stalking_options'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__STALKING_OPTIONS__EMPTY')
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_stalking_options'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'escalations'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'escalations',
                        C__PROPERTY__INFO__DESCRIPTION => 'escalations'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__escalations',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__INT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_TPL_DEF_ESCALATIONS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_host_tpl_def',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostEscalationsAssoc',
                                    'isys_catg_nagios_host_tpl_def_list__escalations'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__ESCALATIONS__EMPTY')
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_escalations'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'name2'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__name2'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_NAME2'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'description'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__NAGIOS
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'action_url'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'action_url',
                        C__PROPERTY__INFO__DESCRIPTION => 'action_url'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__action_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_ACTION_URL'
                    ]
                ]
            ),
            'icon_image'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'icon_image',
                        C__PROPERTY__INFO__DESCRIPTION => 'icon_image'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__icon_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_ICON_IMAGE'
                    ]
                ]
            ),
            'icon_image_alt'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'icon_image_alt',
                        C__PROPERTY__INFO__DESCRIPTION => 'icon_image_alt'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__icon_image_alt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_ICON_IMAGE_ALT'
                    ]
                ]
            ),
            'vrml_image'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'vrml_image',
                        C__PROPERTY__INFO__DESCRIPTION => 'vrml_image'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__vrml_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_VRML_IMAGE'
                    ]
                ]
            ),
            'statusmap_image'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'statusmap_image',
                        C__PROPERTY__INFO__DESCRIPTION => 'statusmap_image'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__statusmap_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_STATUSMAP_IMAGE'
                    ]
                ]
            ),
            'notes'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'notes',
                        C__PROPERTY__INFO__DESCRIPTION => 'notes'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__notes'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTES'
                    ]
                ]
            ),
            'notes_url'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'notes_url',
                        C__PROPERTY__INFO__DESCRIPTION => 'notes_url'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__notes_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_NOTES_URL'
                    ]
                ]
            ),
            'custom_object_vars'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'custom_object_vars',
                        C__PROPERTY__INFO__DESCRIPTION => 'custom_object_vars'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_host_tpl_def_list__custom_obj_vars'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_HOST_TPL_DEF_CUSTOM_OBJ_VARS'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__SANITIZATION => null
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
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create($p_object_id, []);
            } // if
            if (($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) && $p_category_data['data_id'] > 0)
            {
                // Match category data:
                $l_data                                                      = $this->build_sql_attributes($p_category_data['properties'], false);
                $l_arData['isys_catg_nagios_host_tpl_def_list__description'] = $this->convert_sql_text($p_category_data['properties']['description'][C__DATA__VALUE]);
                $l_arData['isys_catg_nagios_host_tpl_def_list__status']      = $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

                // Save category data:
                return ($this->save($p_category_data['data_id'], $l_data)) ? $p_category_data['data_id'] : false;
            } // if
        }

        return false;
    } // function

    /**
     * Helper method which builds an array which can be used for the insert/update statement.
     *
     * @param null $p_data
     * @param bool $p_post
     *
     * @return mixed
     */
    private function build_sql_attributes($p_data = null, $p_post = true)
    {
        $l_arData = [];

        foreach ($this->get_properties() AS $l_key => $l_value)
        {
            if ($p_post)
            {
                if ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG_LIST)
                {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__selected_values';
                }
                else
                {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                } // if
            }
            else
            {
                $l_post_key = $l_key;
            } // if

            if (!array_key_exists($l_post_key, $p_data))
            {
                continue;
            } // if

            if ($p_post)
            {
                $l_data = $p_data[$l_post_key];
            }
            else
            {
                $l_data = $p_data[$l_post_key][C__DATA__VALUE];
            } // if

            if ($l_data == '-1')
            {
                $l_arData[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = 'NULL';
            }
            elseif ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__TYPE__TEXT_AREA)
            {
                $l_arData[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = (!empty($l_data)) ? $this->convert_sql_text($l_data) : 'NULL';
            }
            elseif ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__TYPE__TEXT)
            {
                $l_arData[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $this->convert_sql_text($l_data);
            }
            elseif ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__TYPE__INT)
            {
                $l_arData[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = ($l_data == 0) ? '0' : $l_data;
            }
            else
            {
                $l_arData[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = (!empty($l_data) || is_numeric($l_data)) ? $this->convert_sql_int($l_data) : 'NULL';
            } // if
        } // foreach

        return $l_arData;
    } // function
} // class
?>