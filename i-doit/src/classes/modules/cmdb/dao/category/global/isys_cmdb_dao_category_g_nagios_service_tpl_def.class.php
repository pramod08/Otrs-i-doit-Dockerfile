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
 * DAO: global category for service definitions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_service_tpl_def extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_service_tpl_def';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for the notification option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_notification_option(isys_request $p_request)
    {
        $l_return = [];

        $l_catdata = isys_cmdb_dao_category_g_nagios_service_tpl_def::instance($this->m_db)
            ->get_data($p_request->get_category_data_id())
            ->get_row();

        $l_sn = isys_component_dao_nagios::instance($this->m_db)
            ->getServiceNotificationOptionsAssoc();

        $l_assSn = explode(",", $l_catdata["isys_catg_nagios_service_tpl_def_list__notification_options"]);

        foreach ($l_sn as $key => $val)
        {
            $l_return[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => in_array($key, $l_assSn),
                "url" => ""
            ];
        } // foreach

        return $l_return;
    } // function

    /**
     * Callback method for the flap detection dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_flap_detection_options(isys_request $p_request)
    {
        $l_return = [];

        $l_catdata = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_db)
            ->get_data($p_request->get_category_data_id())
            ->get_row();

        $l_fd = isys_component_dao_nagios::instance($this->m_db)
            ->getServiceFlapDetectionOptionsAssoc();

        $l_assFd = explode(",", $l_catdata["isys_catg_nagios_service_tpl_def_list__flap_detection_options"]);

        foreach ($l_fd as $key => $val)
        {
            $l_return[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => in_array($key, $l_assFd),
                "url" => ""
            ];
        } // foreach

        return $l_return;
    } // function

    /**
     * Callback method for the stalking options dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_stalking_options(isys_request $p_request)
    {
        $l_return = [];

        $l_catdata = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_db)
            ->get_data($p_request->get_category_data_id())
            ->get_row();

        $l_so = isys_component_dao_nagios::instance($this->m_db)
            ->getServiceFlapDetectionOptionsAssoc();

        $l_assSo = explode(",", $l_catdata["isys_catg_nagios_service_tpl_def_list__stalking_options"]);

        foreach ($l_so as $key => $val)
        {
            $l_return[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => in_array($key, $l_assSo),
                "url" => ""
            ];
        } // foreach

        return $l_return;
    } // function

    /**
     * Callback method for the esclations dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_escalations(isys_request $p_request)
    {
        $l_return = [];

        $l_catdata = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_db)
            ->get_data($p_request->get_category_data_id())
            ->get_row();

        $l_he = isys_component_dao_nagios::instance($this->m_db)
            ->getServiceEscalationsAssoc();

        $l_assHe = explode(",", $l_catdata["isys_catg_nagios_service_tpl_def_list__escalations"]);

        foreach ($l_he as $key => $val)
        {
            $l_return[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => in_array($key, $l_assHe),
                "url" => ""
            ];
        } // foreach

        return $l_return;
    } // function

    /**
     * Checks, if the nagios service exists.
     *
     * @param   integer $p_fk_id
     *
     * @return  boolean
     */
    public function nagiosServiceExists($p_fk_id)
    {
        return (count(
                $this->retrieve(
                    'SELECT * FROM isys_catg_nagios_service_tpl_def_list WHERE isys_catg_nagios_service_tpl_def_list__id = ' . $this->convert_sql_id($p_fk_id) . ';'
                )
            ) > 0);
    } // function

    /**
     * Creates a nagios service.
     *
     * @param   integer $p_fk_id
     *
     * @return  boolean
     */
    public function createNagiosService($p_fk_id)
    {
        return $this->update('INSERT INTO isys_catg_nagios_service_tpl_def_list SET isys_catg_nagios_service_tpl_def_list__id  = ' . $this->convert_sql_id($p_fk_id) . ';');
    } // function

    /**
     * Set Status for category entry.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_status($p_cat_id, $p_status)
    {
        $l_sql = 'UPDATE isys_catg_application_list
			SET isys_catg_application_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_catg_application_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * We need to overwrite this method, because nagios needs special treatment.
     *
     * @param   array $p_data
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        if ($this->save_data(null, $p_data))
        {
            return (int) $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Get data method.
     *
     * @param   integer $p_category_data_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT isys_obj.*, isys_obj_type.*, isys_catg_nagios_service_tpl_def_list.*, check_period_plus.*, notification_period_plus.*, isys_catg_nagios_service_tpl_def_list.*
			FROM isys_obj
			INNER JOIN isys_catg_nagios_service_tpl_def_list ON isys_catg_nagios_service_tpl_def_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_nagios_timeperiods_plus check_period_plus ON isys_catg_nagios_service_tpl_def_list__check_period_plus = check_period_plus.isys_nagios_timeperiods_plus__id
			LEFT JOIN isys_nagios_timeperiods_plus notification_period_plus ON isys_catg_nagios_service_tpl_def_list__notification_period_plus = notification_period_plus.isys_nagios_timeperiods_plus__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_category_data_id !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_service_tpl_def_list__id = ' . $this->convert_sql_id($p_category_data_id);
        } // if

        if ($p_obj_id !== null)
        {
            if (is_array($p_obj_id))
            {
                $l_sql .= ' AND isys_catg_nagios_service_tpl_def_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id);
            }
            else
            {
                $l_sql .= ' AND isys_catg_nagios_service_tpl_def_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
            } // if
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_service_tpl_def_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        return [
            'check_command'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command',
                        C__PROPERTY__INFO__DESCRIPTION => 'Command'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__check_command',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_COMMAND',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_component_dao_nagios',
                                    'getCommandsAssoc'
                                ]
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_onChange'        => "idoit.callbackManager.triggerCallback('nagios_service_tpl__check_command_description', this.id);",
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'check_command'
                        ]
                    ]
                ]
            ),
            'name'                         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'name',
                        C__PROPERTY__INFO__DESCRIPTION => 'name'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__name'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NAME'
                    ]
                ]
            ),
            'check_command_plus'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command_plus',
                        C__PROPERTY__INFO__DESCRIPTION => 'Command +'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__check_command_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands_plus',
                            'isys_nagios_commands_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_COMMAND_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_commands_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'check_command_parameters'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command_parameters',
                        C__PROPERTY__INFO__DESCRIPTION => 'Command parameters'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__check_command_parameters',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_COMMAND_PARAMETERS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'max_check_attempts'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'max_check_attempts',
                        C__PROPERTY__INFO__DESCRIPTION => 'Max attempts'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__max_check_attempts',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__MAX_CHECK_ATTEMPTS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'check_interval'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_interval',
                        C__PROPERTY__INFO__DESCRIPTION => 'Interval'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__check_interval',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'retry_interval'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'retry_interval',
                        C__PROPERTY__INFO__DESCRIPTION => 'Retry interval'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__retry_interval',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__RETRY_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'check_period'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__check_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_component_dao_nagios',
                                    'getTimeperiodsAssoc'
                                ]
                            ),
                            'p_strClass' => 'input-dual-radio'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'check_period',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'check_period_plus'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_period+',
                        C__PROPERTY__INFO__DESCRIPTION => 'Check Period +'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_nagios_service_tpl_def_list__check_period_plus',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ],
                        C__PROPERTY__DATA__TABLE_ALIAS => 'timeperiod_plus_a'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_nagios_timeperiods_plus',
                            'p_strClass' => 'input-dual-radio mt5'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'notification_interval'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_interval',
                        C__PROPERTY__INFO__DESCRIPTION => 'Inertval notification'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__notification_interval',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTIFICATION_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'notification_period'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period notification'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__notification_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTIFICATION_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_component_dao_nagios',
                                    'getTimeperiodsAssoc'
                                ]
                            ),
                            'p_strClass' => 'input-dual-radio'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'notification_period'
                        ]
                    ]
                ]
            ),
            'notification_period_plus'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_period+',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period notification+'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_nagios_service_tpl_def_list__notification_period_plus',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ],
                        C__PROPERTY__DATA__TABLE_ALIAS => 'timeperiod_plus_b'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTIFICATION_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_nagios_timeperiods_plus',
                            'p_strClass' => 'input-dual-radio mt5'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'display_name'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'display_name',
                        C__PROPERTY__INFO__DESCRIPTION => 'Dispaly name'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__display_name',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__DISPLAY_NAME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'display_name_selection'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'display_name_selection',
                        C__PROPERTY__INFO__DESCRIPTION => 'Dispaly name selection'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__display_name_selection',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__DISPLAY_NAME_SELECTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__active_checks_enabled',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__ACTIVE_CHECKS_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
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
            'passive_checks_enabled'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'passive_checks_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'passive_checks_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__passive_checks_enabled',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__PASSIVE_CHECKS_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
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
            'initial_state'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'initial_state',
                        C__PROPERTY__INFO__DESCRIPTION => 'initial_state'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__TYPE       => C__TYPE__TEXT,
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__initial_state',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__INITIAL_STATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_component_dao_nagios',
                                    'getServiceFlapDetectionOptionsAssoc'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'service_initial_state'
                        ]
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__notifications_enabled',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTIFICATIONS_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
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
            'service_notification_options' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__notification_options',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTIFICATION_OPTIONS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_service_tpl_def',
                                    'callback_property_notification_option'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__NOTIFICATION_OPTIONS__EMPTY')
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'service_notification_options'
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__first_notification_delay',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__FIRST_NOTIFICATION_DELAY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__flap_detection_enabled',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__FLAP_DETECTION_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
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
            'flap_detection_options'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'flap_detection_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'flap_detection_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__flap_detection_options',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__FLAP_DETECTION_OPTIONS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_service_tpl_def',
                                    'callback_property_flap_detection_options'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__FLAP_DETECTION__EMPTY')
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => null
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__low_flap_threshold',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__LOW_FLAP_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__high_flap_threshold',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__HIGH_FLAP_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ]
                ]
            ),
            'is_volatile'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'is_volatile',
                        C__PROPERTY__INFO__DESCRIPTION => 'is_volatile'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__is_volatile',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__IS_VOLATILE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'obsess_over_service'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'obsess_over_service',
                        C__PROPERTY__INFO__DESCRIPTION => 'obsess_over_service'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__obsess_over_service',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__OBSESS_OVER_SERVICE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__check_freshness',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CHECK_FRESHNESS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__freshness_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__FRESHNESS_THRESHOLD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__event_handler_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__EVENT_HANDLER_ENABLED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__event_handler'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__EVENT_HANDLER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_component_dao_nagios',
                                    'getCommandsAssoc'
                                ]
                            ),
                            'p_strClass'        => 'input-dual-radio',
                            'p_onChange'        => "idoit.callbackManager.triggerCallback('nagios_service_tpl__check_command_description', this.id);",
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'event_handler'
                        ]
                    ]
                ]
            ),
            'event_handler_plus'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler+',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler+'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__event_handler_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands_plus',
                            'isys_nagios_commands_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__EVENT_HANDLER_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_commands_plus',
                            'p_strClass'        => 'input-dual-radio mt5',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__process_perf_data'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__PROCESS_PERF_DATA',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__retain_status_info',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__RETAIN_STATUS_INFORMATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__retain_nonstatus_info',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__RETAIN_NONSTATUS_INFORMATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__stalking_options',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__STALKING_OPTIONS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_service_tpl_def',
                                    'callback_property_stalking_options'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__STALKING_OPTIONS__EMPTY')
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => null
                ]
            ),
            'is_exportable'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_SERVICE_TPL_DEF__CONFIG_EXPORT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export this configuration'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__is_exportable',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__IS_EXPORTABLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
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
            'escalations'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'escalations',
                        C__PROPERTY__INFO__DESCRIPTION => 'escalations'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_tpl_def_list__escalations',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_tpl_def_list',
                            'isys_catg_nagios_service_tpl_def_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__ESCALATIONS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_service_tpl_def',
                                    'callback_property_escalations'
                                ]
                            ),
                            'emptyMessage' => _L('LC__CATG__NAGIOS__ESCALATIONS__EMPTY')
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true
                    ],
                    C__PROPERTY__FORMAT   => null
                ]
            ),
            'description'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__NAGIOS_SERVICE_TPL_DEF
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__action_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__ACTION_URL'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__icon_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__ICON_IMAGE'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__icon_image_alt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__ICON_IMAGE_ALT'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__notes'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTES'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__notes_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__NOTES_URL'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_tpl_def_list__custom_obj_vars'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_TPL_DEF__CUSTOM_OBJ_VARS'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__SANITIZATION => null
                    ]
                ]
            )
        ];
    } // function

    /**
     * We need to overwrite this method, because nagios needs special treatment.
     *
     * @param   integer $p_category_data_id
     * @param   array   $p_data
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $l_values     = [];
        $l_properties = $this->properties();

        // This array contains properties, which are allowed to hold "0" (instead of being translated to "NULL").
        $l_allowed_zero_value = [
            'is_exportable',
            'max_check_attempts',
            'check_interval',
            'retry_interval',
            'notification_interval',
            'active_checks_enabled',
            // Dialog
            'passive_checks_enabled',
            // Dialog
            'notifications_enabled',
            // Dialog
            'first_notification_delay',
            'flap_detection_enabled',
            // Dialog
            'low_flap_threshold',
            'high_flap_threshold',
            'is_volatile',
            // Dialog
            'obsess_over_service',
            // Dialog
            'check_freshness',
            // Dialog
            'freshness_threshold',
            'event_handler_enabled',
            // Dialog
            'process_perf_data',
            // Dialog
            'retain_status_information',
            // Dialog
            'retain_nonstatus_information',
            // Dialog
            'display_name_selection'
        ];

        foreach ($p_data as $l_key => $l_value)
        {
            $l_db_field = $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

            // If the current property has no database field (happenes for some fields), skip it.
            if (empty($l_db_field))
            {
                continue;
            } // if

            if (in_array($l_key, $l_allowed_zero_value) && ($l_value === 0 || $l_value === '0'))
            {
                $l_value = 0;
            }
            else
            {
                if (empty($l_value) || (is_numeric($l_value) && $l_value < 0))
                {
                    $l_value = 'NULL';
                }
                else
                {
                    if (is_numeric($l_value) || $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__TYPE__INT)
                    {
                        $l_value = $this->convert_sql_id($l_value);
                    }
                    else
                    {
                        $l_value = $this->convert_sql_text($l_value);
                    } // if
                } // if
            } // if

            $l_values[] = $l_db_field . ' = ' . $l_value;
        } // foreach

        if ($p_category_data_id !== null)
        {
            $l_sql = 'UPDATE isys_catg_nagios_service_tpl_def_list
				SET isys_catg_nagios_service_tpl_def_list__status = ' . $this->convert_sql_int($p_data['status'] ? $p_data['status'] : C__RECORD_STATUS__NORMAL) . ',
				' . implode(', ', $l_values) . '
				WHERE isys_catg_nagios_service_tpl_def_list__id = ' . $this->convert_sql_id($p_category_data_id) . ';';
        }
        else
        {
            $l_sql = 'INSERT IGNORE INTO isys_catg_nagios_service_tpl_def_list
				SET isys_catg_nagios_service_tpl_def_list__isys_obj__id = ' . $this->convert_sql_id($p_data['isys_obj__id']) . ',
				isys_catg_nagios_service_tpl_def_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
				' . implode(', ', $l_values) . ';';
        } // if

        return ($this->update($l_sql) && $this->apply_update());
    } // function
} // class
?>