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
class isys_cmdb_dao_category_g_nagios extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'nagios';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for property host.
     *
     * @param   isys_request $p_request
     *
     * @return  string
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

        if (is_array($p_method) && isset($p_method[0]))
        {
            $p_method = $p_method[0];
        }

        if ($p_method)
        {
            if (method_exists($l_comp_dao_nagios, $p_method))
            {
                $l_return = $l_comp_dao_nagios->$p_method();
            } // function
        }

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

        $l_cat_id = $p_request->get_category_data_id();

        $l_catdata          = $this->get_data($l_cat_id)
            ->__to_array();
        $l_assigned_options = explode(',', $l_catdata[$p_field]);

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
     * Callback method for the hostaddress dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_address(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        $l_dao    = new isys_cmdb_dao_category_g_ip($this->get_database_component());
        $l_res    = $l_dao->get_data(null, $l_obj_id);
        $l_return = null;

        while ($l_row = $l_res->get_row())
        {
            $l_return[$l_row['isys_catg_ip_list__id']] = $l_row['isys_cats_net_ip_addresses_list__title'];
        } // while

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
        $p_intOldRecStatus = $l_catdata["isys_catg_nagios_list__status"];

        // At first we used "g_active_modreq", but this led to double encoding (I don't know why).
        $l_posts = $_POST;

        $l_arData = $this->build_sql_attributes($l_posts);

        $l_arData['isys_catg_nagios_list__description'] = $this->convert_sql_text(
            $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );
        $l_arData['isys_catg_nagios_list__status']      = $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($l_catdata['isys_catg_nagios_list__id']))
        {
            $l_bRet = $this->save($l_catdata['isys_catg_nagios_list__id'], $l_arData);
        }
        else
        {
            $l_bRet = $this->create($_GET[C__CMDB__GET__OBJECT], $l_arData);
        } // if

        $this->m_strLogbookSQL = $this->get_last_query();

        return ($l_bRet) ? $l_bRet : $l_intErrorCode;
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
            $l_sql = 'UPDATE isys_catg_nagios_list SET ' . implode(', ', $l_fields) . ' WHERE isys_catg_nagios_list__id = ' . $this->convert_sql_id($p_cat_level) . ';';

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

        if (is_array($p_arData) && count($p_arData) > 0)
        {
            if (!array_key_exists('isys_catg_nagios_list__is_exportable', $p_arData))
            {
                $p_arData['isys_catg_nagios_list__is_exportable'] = 1;
            }
            foreach ($p_arData as $key => $value)
            {
                $l_fields[] = $key . ' = ' . $value;
            } // foreach
        } // if

        $l_fields[] = 'isys_catg_nagios_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);
        if (!is_array($p_arData) || !array_key_exists('isys_catg_nagios_list__status', $p_arData))
        {
            $l_fields[] = 'isys_catg_nagios_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);
        }

        $l_sql = 'INSERT IGNORE INTO isys_catg_nagios_list SET ' . implode(', ', $l_fields) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    }

    /**
     * Method for retrieving the nagios and NDO data by a given object ID.
     *
     * @param   mixed $p_id May be an integer or a array of integers.
     *
     * @return  array
     */
    public function getCatDataById($p_id)
    {
        if (is_array($p_id))
        {
            return $this->retrieve(
                'SELECT * FROM isys_catg_monitoring_list
				INNER JOIN isys_obj ON isys_obj__id = isys_catg_monitoring_list__isys_obj__id
				LEFT JOIN isys_catg_nagios_list ON isys_catg_nagios_list__isys_obj__id = isys_catg_monitoring_list__isys_obj__id
				LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_catg_nagios_list__isys_obj__id AND isys_catg_ip_list__primary = 1
				WHERE isys_catg_monitoring_list__isys_obj__id ' . $this->prepare_in_condition($p_id) . ';'
            )
                ->get_row();
        } // if

        return $this->retrieve(
            'SELECT * FROM isys_catg_monitoring_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_monitoring_list__isys_obj__id
			LEFT JOIN isys_catg_nagios_list ON isys_catg_nagios_list__isys_obj__id = isys_catg_monitoring_list__isys_obj__id
			LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_catg_nagios_list__isys_obj__id AND isys_catg_ip_list__primary = 1
			WHERE isys_catg_monitoring_list__isys_obj__id = ' . $this->convert_sql_id($p_id) . ';'
        )
            ->get_row();
    } // function

    /**
     * Retrieve all nagios hosts.
     *
     * @param   boolean $p_only_exportable
     * @param   integer $p_monitoring_export_id
     *
     * @return  isys_component_dao_result
     */
    public function getHosts($p_only_exportable = false, $p_monitoring_export_id = null)
    {
        if ($p_monitoring_export_id === null)
        {
            $l_sql = 'SELECT * FROM isys_catg_nagios_list
				INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_list__isys_obj__id
				WHERE isys_obj__status = ' . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . '
				AND isys_obj__isys_cmdb_status__id = ' . $this->convert_sql_int(C__CMDB_STATUS__IN_OPERATION);
        }
        else
        {
            $l_sql = 'SELECT * FROM isys_catg_nagios_list
				INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_list__isys_obj__id
				WHERE isys_obj__status = ' . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . '
				AND isys_catg_nagios_list__export_host = ' . $this->convert_sql_id($p_monitoring_export_id) . '
				AND isys_obj__isys_cmdb_status__id = ' . $this->convert_sql_int(C__CMDB_STATUS__IN_OPERATION);
        } // if

        if ($p_only_exportable === true)
        {
            $l_sql .= ' AND isys_catg_nagios_list__is_exportable = 1';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Retrieve all active services.
     *
     * @param   integer $p_hostObjID
     *
     * @return  isys_component_dao_result
     */
    public function getActiveServices($p_hostObjID)
    {
        $l_query = 'SELECT isys_catg_nagios_refs_services_list__isys_obj__id__service AS service_obj_id, isys_catg_nagios_service_def_list__service_description AS service_description
			FROM isys_catg_nagios_refs_services_list
			LEFT JOIN isys_catg_nagios_service_def_list ON isys_catg_nagios_service_def_list__isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__service
			WHERE isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id($p_hostObjID) . ';';

        // Check for "is_active" was removed, because the corresponding field is no longer existent in the frontend.
        return $this->retrieve($l_query);
    } // function

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id))
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        $l_sql = "SELECT count(isys_catg_nagios_list__id) AS count FROM isys_catg_nagios_list WHERE TRUE ";

        if (!empty($l_obj_id))
        {
            $l_sql .= " AND isys_catg_nagios_list__isys_obj__id = " . $this->convert_sql_id($l_obj_id);
        } // if

        $l_sql .= ' AND isys_catg_nagios_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return (int) $this->retrieve($l_sql)
            ->get_row_value('count');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'is_exportable'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_CONFIG_EXPORT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export this configuration'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__is_exportable'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_IS_EXPORTABLE',
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
            'export_host'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__MONITORING__EXPORT__CONFIGURATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export configuration'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__export_host',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_monitoring_export_config',
                            'isys_monitoring_export_config__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_EXPORT_HOST',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_export_config'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'host_template'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__TEMPLATES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Nagios host template'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__host_tpl'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_TEMPLATES'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'host_template'
                        ]
                    ]
                ]
            ),
            'host_name'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'host_name',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_name'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__host_name'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_NAME',
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
            'host_name_selection'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'host_name_selection',
                        C__PROPERTY__INFO__DESCRIPTION => 'host_name_selection selection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__host_name_selection'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HOST_NAME_SELECTION',
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
            'name1'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__HOSTNAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__name1'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_NAME1'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'alias'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NAGIOS_LIST_ALIAS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Alias'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__alias'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ALIAS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
                    ]
                ]
            ),
            'address'                      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => 'IP Adress'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__isys_catg_ip_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_ip_list',
                            'isys_catg_ip_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_IP',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_address'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false,
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'assigned_ip_address'
                        ]
                    ]
                ]
            ),
            'address_selection'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'address_selection',
                        C__PROPERTY__INFO__DESCRIPTION => 'Address selection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__address_selection'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_IP_SELECTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPlaceholder' => '0',
                            'default'          => null
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'parents'                      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_PARENTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Choose further objects as parent'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__parents'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_PARENTS'
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
            'is_parent'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_IS_PARENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Use the following objects as parents?'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__is_parent'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_IS_PARENT',
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
            'check_command'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'check_command',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_command'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__check_command',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands',
                            'isys_nagios_commands__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_CHECK_COMMAND',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getCommandsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio mb5',
                            'p_bInfoIconSpacer' => 0
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__check_command_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands_plus',
                            'isys_nagios_commands_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_CHECK_COMMAND_PLUS',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__check_command_parameters'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_CHECK_COMMAND_PARAMETERS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__check_interval'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_CHECK_INTERVAL',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__retry_interval'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_RETRY_INTERVAL',
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
            'max_check_attempts'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'max_check_attempts',
                        C__PROPERTY__INFO__DESCRIPTION => 'max_check_attempts'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__max_check_attempts'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_MAX_CHECK_ATTEMPTS',
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
                        C__PROPERTY__INFO__TITLE       => 'check_period',
                        C__PROPERTY__INFO__DESCRIPTION => 'check_period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__check_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods',
                            'isys_nagios_timeperiods__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_CHECK_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getTimeperiodsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio mb5',
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__check_period_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_CHECK_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__active_checks_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_ACTIVE_CHECKS_ENABLED',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__passive_checks_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_PASSIVE_CHECKS_ENABLED',
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
            'notifications_enabled'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notifications_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'notifications_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__notifications_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_NOTIFICATIONS_ENABLED',
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
            'host_notification_options'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__notification_options',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_NOTIFICATION_OPTIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostNotificationOptionsAssoc',
                                    'isys_catg_nagios_list__notification_options'
                                ]
                            )
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
            'notification_interval'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'notification_interval',
                        C__PROPERTY__INFO__DESCRIPTION => 'notification_interval'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__notification_interval'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_NOTIFICATION_INTERVAL',
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__notification_period',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods',
                            'isys_nagios_timeperiods__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_NOTIFICATION_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getTimeperiodsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio mb5',
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__notification_period_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_timeperiods_plus',
                            'isys_nagios_timeperiods_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_NOTIFICATION_PERIOD_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_timeperiods_plus',
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__initial_state',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_INITIAL_STATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
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
            'obsess_over_host'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'obsess_over_host',
                        C__PROPERTY__INFO__DESCRIPTION => 'obsess_over_host'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__obsess_over_host'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_OBSESS_OVER_HOST',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__check_freshness'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_CHECK_FRESHNESS',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__freshness_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_FRESHNESS_THRESHOLD',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__flap_detection_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_FLAP_DETECTION_ENABLED',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__flap_detection_options',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_FLAP_DETECTION_OPTIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostFlapDetectionOptionsAssoc',
                                    'isys_catg_nagios_list__flap_detection_options'
                                ]
                            )
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
            'low_flap_threshold'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'low_flap_threshold',
                        C__PROPERTY__INFO__DESCRIPTION => 'low_flap_threshold'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__low_flap_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_LOW_FLAP_THRESHOLD',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__high_flap_threshold'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_HIGH_FLAP_THRESHOLD',
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
            'event_handler_enabled'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler_enabled',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler_enabled'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__event_handler_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_EVENT_HANDLER_ENABLED',
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
            'event_handler'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'event_handler',
                        C__PROPERTY__INFO__DESCRIPTION => 'event_handler'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__event_handler'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_EVENT_HANDLER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'          => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_nagios_methods'
                                ], ['getCommandsAssoc']
                            ),
                            'p_strClass'        => 'input-dual-radio mb5',
                            'p_bInfoIconSpacer' => 0
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
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_nagios_list__event_handler_plus',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_nagios_commands_plus',
                            'isys_nagios_commands_plus__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_EVENT_HANDLER_PLUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_nagios_commands_plus',
                            'p_strClass'        => 'input-dual-radio',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper_nagios',
                            'dialog_plus'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__event_handler_parameters'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_EVENT_HANDLER_PARAMETERS'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__process_perf_data'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_PROCESS_PERF_DATA',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__retain_status_information'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_RETAIN_STATUS_INFORMATION',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__retain_nonstatus_information'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_RETAIN_NONSTATUS_INFORMATION',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__first_notification_delay'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_FIRST_NOTIFICATION_DELAY',
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
            'stalking_options'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'stalking_options',
                        C__PROPERTY__INFO__DESCRIPTION => 'stalking_options'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__stalking_options',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_STALKING_OPTIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostFlapDetectionOptionsAssoc',
                                    'isys_catg_nagios_list__stalking_options'
                                ]
                            )
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__escalations',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__INT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_ESCALATIONS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_nagios',
                                    'callback_property_general_dialog_list_nagios_methods'
                                ], [
                                    'getHostEscalationsAssoc',
                                    'isys_catg_nagios_list__escalations'
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
            'action_url'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'action_url',
                        C__PROPERTY__INFO__DESCRIPTION => 'action_url'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__action_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ACTION_URL'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__icon_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ICON_IMAGE'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__icon_image_alt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ICON_IMAGE_ALT'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__vrml_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_VRML_IMAGE'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__statusmap_image'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_STATUSMAP_IMAGE'
                    ]
                ]
            ),
            'twod_coords'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => '2d_coords',
                        C__PROPERTY__INFO__DESCRIPTION => '2d_coords'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__2d_coords'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_2D_COORDS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ]
                ]
            ),
            'threed_coords'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => '3d_coords',
                        C__PROPERTY__INFO__DESCRIPTION => '3d_coords'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__3d_coords'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_3D_COORDS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__notes'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_NOTES'
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__notes_url'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_NOTES_URL'
                    ]
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__display_name'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_DISPLAY_NAME',
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__display_name_selection'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_DISPLAY_NAME_SELECTION',
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
            'name2'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__name2'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_NAME2'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__custom_obj_vars'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_CUSTOM_OBJ_VARS'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__SANITIZATION => null
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__description'
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
                $l_data                                           = $this->build_sql_attributes($p_category_data['properties'], false);
                $l_arData['isys_catg_nagios_list__description']   = $this->convert_sql_text($p_category_data['properties']['description'][C__DATA__VALUE]);
                $l_arData['isys_catg_nagios_list__status']        = $this->convert_sql_int(C__RECORD_STATUS__NORMAL);
                $l_arData['isys_catg_nagios_list__is_exportable'] = 1;

                // Save category data:
                return ($this->save($p_category_data['data_id'], $l_data)) ? $p_category_data['data_id'] : false;
            } // if
        }

        return false;
    } // function

    /**
     * This method gets all nagios services which are exportable
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_exportable_objects()
    {
        $l_arr = [];
        $l_res = $this->getHosts(true);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_arr[] = $l_row['isys_obj__id'];
            } // while
        } // if

        return $l_arr;
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
                if ($l_key == 'parents' || $l_key == 'host_template')
                {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__HIDDEN';
                }
                elseif ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG_LIST)
                {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__selected_values';
                }
                else
                {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                }
            }
            else
            {
                $l_post_key = $l_key;
            }

            if (!array_key_exists($l_post_key, $p_data)) continue;

            if ($p_post)
            {
                $l_data = $p_data[$l_post_key];
            }
            else
            {
                $l_data = $p_data[$l_post_key][C__DATA__VALUE];
            }

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
            else
            {
                $l_arData[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = (!empty($l_data) || is_numeric($l_data)) ? $this->convert_sql_int($l_data) : 'NULL';
            }
        }

        return $l_arData;
    } // function
} // class