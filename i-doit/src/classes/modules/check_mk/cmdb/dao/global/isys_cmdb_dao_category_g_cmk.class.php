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
 * DAO: global folder category for Check_MK.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_dao_category_g_cmk extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cmk';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for property host.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_export_config(isys_request $p_request)
    {
        global $g_comp_database;

        $l_export_config     = [];
        $l_export_config_res = isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_export_data();

        if (count($l_export_config_res))
        {
            while ($l_row = $l_export_config_res->get_row())
            {
                $l_export_config[$l_row['isys_monitoring_export_config__id']] = $l_row['isys_monitoring_export_config__title'];
            } // while
        } // if

        return $l_export_config;
    } // function

    /**
     * Callback method for property host.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_hostaddress(isys_request $p_request)
    {
        global $g_comp_database;

        $l_return = [
            '-1' => _L('LC__CATG__IP__PRIMARY_IP_ADDRESS')
        ];

        $l_hostaddresses = isys_cmdb_dao_category_g_ip::instance($g_comp_database)
            ->get_data(null, $p_request->get_object_id());

        if (count($l_hostaddresses))
        {
            while ($l_row = $l_hostaddresses->get_row())
            {
                $l_return[$l_row['isys_catg_ip_list__id']] = $l_row['isys_cats_net_ip_addresses_list__title'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Method for looking up an object, by giving a (check_mk-) host-ID and hostname.
     *
     * @deprecated  Use isys_monitoring_helper::get_objects_by_hostname() instead - This method is used by analytics!
     *
     * @param   integer $p_host_id
     * @param   string  $p_hostname
     *
     * @return  array
     * @author      Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_object_by_cmk_host_name($p_host_id, $p_hostname)
    {
        $l_host_res = isys_cmdb_dao_category_g_monitoring::instance($this->get_database_component())
            ->get_data(null, null, ' AND isys_catg_monitoring_list__isys_monitoring_hosts__id = ' . $this->convert_sql_id($p_host_id) . ' ', null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_host_res->get_row())
        {
            if (isys_monitoring_helper::render_export_hostname($l_row['isys_catg_monitoring_list__isys_obj__id']) == $p_hostname)
            {
                return $l_row;
            } // if
        } // while

        return [];
    } // function

    /**
     * This method selects all export configurations, which are used at least once (Doesn't matter if NORMAL, ARCHIVED or DELETED).
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_used_export_paths()
    {
        return $this->retrieve(
            'SELECT isys_monitoring_export_config.*
			FROM isys_monitoring_export_config
			INNER JOIN isys_catg_cmk_list ON isys_catg_cmk_list__isys_monitoring_export_config__id = isys_monitoring_export_config__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_cmk_list__isys_obj__id;'
        );
    } // function

    /**
     * Simply retrieve all export configurations.
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_export_configurations()
    {
        $mapping = [
            'isys_monitoring_export_config__id AS id',
            'isys_monitoring_export_config__title AS title',
            'isys_monitoring_export_config__path AS path',
            'isys_monitoring_export_config__address AS address',
            'isys_monitoring_export_config__type AS type',
            'isys_monitoring_export_config__options AS options'
        ];

        return $this->retrieve('SELECT ' . implode(',', $mapping) . ' FROM isys_monitoring_export_config WHERE isys_monitoring_export_config__type = "check_mk";');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function properties()
    {
        return [
            'active'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK__ACTIVE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Active'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__exportable'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK__ACTIVE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => true
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
            'export_config'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__MONITORING__EXPORT__CONFIGURATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export configuration'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__isys_monitoring_export_config__id'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK__EXPORT_CONFIG',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_cmk',
                                    'callback_property_export_config'
                                ]
                            )
                        ]
                    ]
                ]
            ),
            'title'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK__ALIAS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Alias'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CMK__ALIAS'
                    ]
                ]
            ),
            'export_ip'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK__EXPORT_IP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export hostaddress'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__export_ip'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK__EXPORT_IP',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => get_smarty_arr_YES_NO(),
                            'p_strClass'   => 'input-mini',
                            'p_bDbFieldNN' => 1
                        ]
                    ]
                ]
            ),
            'hostaddress'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hostaddress'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__isys_catg_ip_list__id'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK__HOSTADDRESS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_cmk',
                                    'callback_property_hostaddress'
                                ]
                            ),
                            'p_bSort'      => false,
                            'p_bDbFieldNN' => 1
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_check_mk',
                            'hostaddress'
                        ]
                    ]
                ]
            ),
            'host_name'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK__HOSTNAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__host_name'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK_HOST_NAME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'host_name_selection' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK__HOSTNAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hostname selection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__host_name_selection'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__CMK__HOSTNAME'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'description'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CMK
                    ]
                ]
            )
        ];
    } // function
} // class