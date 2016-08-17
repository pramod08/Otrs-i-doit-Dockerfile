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
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_service_dep extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_service_dep';

    protected $m_multivalued = true;

    /**
     * Callback method for the notification criteria dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_notification_fail_criteria(isys_request $p_request)
    {
        $l_return = [];

        $l_catdata = isys_cmdb_dao_category_g_nagios_service_dep::instance($this->m_db)
            ->get_data($p_request->get_category_data_id())
            ->get_row();

        $l_sn = isys_component_dao_nagios::instance($this->m_db)
            ->getServiceFailureCriteriaAssoc();

        $l_assSn = explode(",", $l_catdata["isys_catg_nagios_service_dep_list__notif_fail_criteria"]);

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
     * Callback method for the notification criteria dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_execution_fail_criteria(isys_request $p_request)
    {
        $l_return = [];

        $l_catdata = isys_cmdb_dao_category_g_nagios_service_dep::instance($this->m_db)
            ->get_data($p_request->get_category_data_id())
            ->get_row();

        $l_sn = isys_component_dao_nagios::instance($this->m_db)
            ->getServiceFailureCriteriaAssoc();

        $l_assSn = explode(",", $l_catdata["isys_catg_nagios_service_dep_list__exec_fail_criteria"]);

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

    public function get_collected_data($p_objectID, $p_record_status = null)
    {
        $l_sql = "SELECT *,
			chost.isys_catg_nagios_refs_services_list__isys_obj__id__host AS host,
			isys_catg_nagios_service_dep_list__service_dep_connection AS servicedep,
			chostdep.isys_catg_nagios_refs_services_list__isys_obj__id__host AS hostdep
			FROM isys_catg_nagios_service_dep_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_service_dep_list__isys_obj__id
			LEFT JOIN isys_catg_nagios_refs_services_list chost ON isys_catg_nagios_service_dep_list__host_connection = chost.isys_catg_nagios_refs_services_list__id
			LEFT JOIN isys_catg_nagios_refs_services_list chostdep ON isys_catg_nagios_service_dep_list__host_dep_connection = chostdep.isys_catg_nagios_refs_services_list__id
			WHERE (isys_catg_nagios_service_dep_list__service_dep_connection = " . $this->convert_sql_id($p_objectID) . "
			    OR isys_obj.isys_obj__id = " . $this->convert_sql_id($p_objectID) . ")";

        if ($p_record_status !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_service_dep_list__status = ' . $this->convert_sql_int($p_record_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Get data method.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $this->get_assigned_hosts($p_obj_id);
        $l_sql = "SELECT *,
	        chost.isys_catg_nagios_refs_services_list__isys_obj__id__host AS host,
	        isys_catg_nagios_service_dep_list__service_dep_connection AS servicedep,
	        chostdep.isys_catg_nagios_refs_services_list__isys_obj__id__host AS hostdep
	        FROM isys_catg_nagios_service_dep_list
	        INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_service_dep_list__isys_obj__id
	        LEFT JOIN isys_catg_nagios_refs_services_list chost ON isys_catg_nagios_service_dep_list__host_connection = chost.isys_catg_nagios_refs_services_list__id
	        LEFT JOIN isys_catg_nagios_refs_services_list chostdep ON isys_catg_nagios_service_dep_list__host_dep_connection = chostdep.isys_catg_nagios_refs_services_list__id
	        WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND isys_catg_nagios_service_dep_list__id = " . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_nagios_service_dep_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'host'                       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_SERVICE_DEP__HOST',
                        C__PROPERTY__INFO__DESCRIPTION => 'Host'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_nagios_service_dep_list__host_dep_connection',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_catg_nagios_refs_services_list',
                            'isys_catg_nagios_refs_services_list__id'
                        ],
                        C__PROPERTY__DATA__FIELD_ALIAS => 'hostdep_id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'chostdep',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_DEP__HOST'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__EXPORT => false
                    ]
                ]
            ),
            'service_dependency'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_nagios_service_dep_list__service_dep_connection',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_connection',
                            'isys_connection__id'
                        ],
                        C__PROPERTY__DATA__FIELD_ALIAS => 'servicedep_id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'cservicedep',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__EXPORT => false
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ]
                ]
            ),
            'host_dependency'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_nagios_service_dep_list__host_connection',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_catg_nagios_refs_services_list',
                            'isys_catg_nagios_refs_services_list__id'
                        ],
                        C__PROPERTY__DATA__FIELD_ALIAS => 'host_id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'chost',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__EXPORT => false
                    ]
                ]
            ),
            'inherits_parent'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'inherits_parent',
                        C__PROPERTY__INFO__DESCRIPTION => 'inherits_parent'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_dep_list__inherits_parent'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_DEP__INHERITS_PARENT',
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
                    ]
                ]
            ),
            'execution_fail_criteria'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'execution_failure_criteria',
                        C__PROPERTY__INFO__DESCRIPTION => 'execution_failure_criteria'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_dep_list__exec_fail_criteria',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_dep_list',
                            'isys_catg_nagios_service_dep_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_DEP__EXEC_FAIL_CRITERIA',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_service_dep',
                                    'callback_property_execution_fail_criteria'
                                ]
                            )
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
            'notification_fail_criteria' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_failure_criteria',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_failure_criteria'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_dep_list__notif_fail_criteria',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_nagios_service_dep_list',
                            'isys_catg_nagios_service_dep_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_DEP__NOTIF_FAIL_CRITERIA',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios_service_dep',
                                    'callback_property_notification_fail_criteria'
                                ]
                            )
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
            'dependency_period'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'dependency_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period dependency_period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_service_dep_list__dep_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods',
                            'isys_nagios_timeperiods__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD',
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
            'dependency_period_plus'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'dependency_period+',
                        C__PROPERTY__INFO__DESCRIPTION => 'dependency_period+'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_nagios_service_dep_list__dep_period_plus',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ],
                        C__PROPERTY__DATA__TABLE_ALIAS => 'timeperiod_plus_b'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
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
            'description'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_dep_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__NAGIOS_SERVICE_DEP
                    ]
                ]
            )
        ];
    } // function

    /**
     * Method for retrieving all hosts, by a given nagios service object ID.
     *
     * @param   integer $p_objectID
     *
     * @return  array
     */
    public function get_assigned_hosts($p_objectID)
    {
        $l_return  = [];
        $l_dataRes = isys_cmdb_dao_category_g_nagios_refs_services_backwards::instance($this->get_database_component())
            ->get_data(null, $p_objectID);

        if (count($l_dataRes) > 0)
        {
            while ($l_row = $l_dataRes->get_row())
            {
                $l_tmp = $this->get_object_by_id($l_row['isys_catg_nagios_refs_services_list__isys_obj__id__host'], true)
                    ->get_row();

                /**
                 * @var $l_dao isys_cmdb_dao_object_type
                 */
                $l_dao = isys_cmdb_dao_object_type::factory($this->get_database_component());
                if ($l_dao->has_cat($l_tmp['isys_obj__isys_obj_type__id'], ['C__CATG__NAGIOS_HOST_FOLDER']))
                {
                    $l_return[$l_row['isys_catg_nagios_refs_services_list__id']] = $l_tmp['isys_obj__title'];
                } // if
            } // while
        } // if

        return $l_return;
    } // function
} // class