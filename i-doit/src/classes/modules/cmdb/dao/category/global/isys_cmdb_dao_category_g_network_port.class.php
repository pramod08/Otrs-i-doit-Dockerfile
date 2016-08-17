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
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * DAO: global category for physical network ports
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_network_port extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'network_port';
    /**
     * Category's constant
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__NETWORK_PORT';
    /**
     * Category's identifier
     *
     * @var int
     *
     * @fixme No standard behavior!
     */
    protected $m_category_id = C__CMDB__SUBCAT__NETWORK_PORT;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;
    /**
     * Main table where properties are stored persistently
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_table = 'isys_catg_port_list';
    /**
     * Category's template
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_tpl = 'catg__port.tpl';

    /**
     * @var int
     */
    private $m_default_vlan_id = 0;

    /**
     * Adds needed functions for the order by.
     *
     * @param   isys_component_database $p_db
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function add_sql_functions_for_order(isys_component_database &$p_db)
    {
        //$p_db->query("DROP FUNCTION IF EXISTS alphas;");
        if (!$p_db->num_rows($p_db->query('SHOW FUNCTION STATUS WHERE NAME = \'alphas\' AND Db = \'' . $p_db->get_db_name() . '\';')))
        {
            // This function strips all numeric characters
            $p_db->query(
                "CREATE FUNCTION alphas(str CHAR(100)) RETURNS CHAR(100) DETERMINISTIC READS SQL DATA
					BEGIN
					  DECLARE i, len SMALLINT DEFAULT 1;
					  DECLARE ret CHAR(100) DEFAULT '';
					  DECLARE c CHAR(1);
					  SET len = CHAR_LENGTH( str );
					  REPEAT
						BEGIN
						  SET c = MID( str, i, 1 );
						  IF c REGEXP '[[:alpha:]]' THEN
							SET ret=CONCAT(ret,c);
						  END IF;
						  SET i = i + 1;
						END;
					  UNTIL i > len END REPEAT;
					  RETURN ret;
					 END"
            );
        } // if

        if (!$p_db->num_rows($p_db->query('SHOW FUNCTION STATUS WHERE NAME = \'digits\' AND Db = \'' . $p_db->get_db_name() . '\';')))
        {
            // This function strips all non numeric characters
            $p_db->query(
                "CREATE FUNCTION digits( str CHAR(100) ) RETURNS CHAR(100) DETERMINISTIC READS SQL DATA
					BEGIN
					  DECLARE i, len SMALLINT DEFAULT 1;
					  DECLARE ret CHAR(100) DEFAULT '';
					  DECLARE c CHAR(1);
					  SET len = CHAR_LENGTH( str );
					  REPEAT
						BEGIN
						  SET c = MID( str, i, 1 );
						  IF c BETWEEN '0' AND '9' THEN
							SET ret=CONCAT(ret,c);
						  END IF;
						  SET i = i + 1;
						END;
					  UNTIL i > len END REPEAT;
					  RETURN ret;
					END"
            );
        } // if

        if (!$p_db->num_rows($p_db->query('SHOW FUNCTION STATUS WHERE NAME = \'substr_order\' AND Db = \'' . $p_db->get_db_name() . '\';')))
        {
            // This function cuts the string from 0 to last index of the specified delimiter
            $p_db->query(
                "CREATE FUNCTION substr_order( str CHAR(100), delim CHAR(5) ) RETURNS CHAR(100) DETERMINISTIC READS SQL DATA
					BEGIN
					  DECLARE i, len, posi SMALLINT DEFAULT 1;
					  DECLARE c CHAR(1);
					  DECLARE ret CHAR(100);
					  SET len = CHAR_LENGTH(str);
					  REPEAT
						BEGIN
						SET c = MID( str, i, 1 );
						IF c = delim THEN
							SET posi = i;
						END IF;
						SET i = i + 1;
						END;
					  UNTIL i > len END REPEAT;

					  IF posi BETWEEN '2' AND len THEN
						SET ret = SUBSTR(str, 1, posi);
					  ELSE
						SET ret = alphas(str);
					  END IF;

					  RETURN ret;
					END"
            );
        } // if

        return true;
    } // function

    /**
     * Callback method for the interface dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_interface(isys_request $p_request)
    {

        $l_iface_res = isys_cmdb_dao_category_g_network_interface::instance($this->get_database_component())
            ->get_data(null, $p_request->get_object_id());
        $l_hba_res   = isys_cmdb_dao_category_g_hba::instance($this->get_database_component())
            ->get_data(null, $p_request->get_object_id());
        $l_return    = [];

        while ($l_row = $l_iface_res->get_row())
        {
            $l_return[$l_row['isys_catg_netp_list__id'] . '_C__CMDB__SUBCAT__NETWORK_INTERFACE_P'] = $l_row['isys_catg_netp_list__title'];
        } // while

        while ($l_row = $l_hba_res->get_row())
        {
            $l_return[$l_row['isys_catg_hba_list__id'] . '_C__CATG__HBA'] = $l_row['isys_catg_hba_list__title'];
        } // while

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
    public function callback_property_addresses(isys_request $p_request)
    {

        $l_obj_id = $p_request->get_object_id();
        $l_cat_id = $p_request->get_category_data_id();

        $l_res    = isys_cmdb_dao_category_g_ip::instance($this->get_database_component())
            ->get_data(null, $l_obj_id);
        $l_return = [];

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_address = $l_row["isys_cats_net_ip_addresses_list__title"] ?: $l_row["isys_catg_ip_list__hostname"];

                if ($l_row['isys_catg_ip_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV4)
                {
                    $l_return[] = [
                        "id"   => $l_row["isys_catg_ip_list__id"],
                        "val"  => $l_address ? $l_address : _L("LC__IP__EMPTY_ADDRESS"),
                        "sel"  => (($l_cat_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && !is_null($l_cat_id)) ? true : false),
                        "link" => isys_helper_link::create_catg_item_url(
                            [
                                C__CMDB__GET__OBJECT   => $l_obj_id,
                                C__CMDB__GET__CATG     => C__CATG__IP,
                                C__CMDB__GET__CATLEVEL => $l_row["isys_catg_ip_list__id"]
                            ]
                        )
                    ];
                }
                else
                {
                    $l_return[] = [
                        "id"   => $l_row["isys_catg_ip_list__id"],
                        "val"  => $l_address ? Ip::validate_ipv6($l_address, true) : _L("LC__IP__EMPTY_ADDRESS"),
                        "sel"  => (($l_cat_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && !is_null($l_cat_id)) ? true : false),
                        "link" => isys_helper_link::create_catg_item_url(
                            [
                                C__CMDB__GET__OBJECT   => $l_obj_id,
                                C__CMDB__GET__CATG     => C__CATG__IP,
                                C__CMDB__GET__CATLEVEL => $l_row["isys_catg_ip_list__id"]
                            ]
                        )
                    ];
                } // if
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assigned_connector(isys_request $p_request)
    {
        return isys_cmdb_dao_cable_connection::instance($this->get_database_component())
            ->get_assigned_connector_id($p_request->get_row("isys_catg_port_list__isys_catg_connector_list__id"));
    } // function

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_cable(isys_request $p_request)
    {
        return isys_cmdb_dao_cable_connection::instance($this->get_database_component())
            ->get_assigned_cable($p_request->get_row("isys_catg_port_list__isys_catg_connector_list__id"));
    } // function

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_layer2_assignment(isys_request $p_request)
    {
        return $this->get_attached_layer2_net_as_array($p_request->get_row("isys_catg_port_list__id"));
    } // function

    /**
     * Method for retrieving data from this DAO.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_ports($p_obj_id, null, $p_status, $p_catg_list_id, $p_filter, $p_condition);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__PORT__TITLE'
                    ]
                ]
            ),
            'interface'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__CON_INTERFACE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connected interface'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_catg_netp_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_netp_list',
                            'isys_catg_netp_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__INTERFACE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_port',
                                    'callback_property_interface'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'interface_p'
                        ]
                    ]
                ]
            ),
            'port_type'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_port_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_type',
                            'isys_port_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_port_type',
                            'p_bDbFieldNN' => '1'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'port_mode'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MODE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Mode'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_port_mode__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_mode',
                            'isys_port_mode__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__MODE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_port_mode',
                            'p_bDbFieldNN' => '1'
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
            'plug_type'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__PLUG',
                        C__PROPERTY__INFO__DESCRIPTION => 'Plug'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_plug_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_plug_type',
                            'isys_plug_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__PLUG',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_plug_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'negotiation'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__NEGOTIATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Negotiation'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_port_negotiation__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_negotiation',
                            'isys_port_negotiation__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__NEGOTIATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_port_negotiation'
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
            'duplex'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__DUPLEX',
                        C__PROPERTY__INFO__DESCRIPTION => 'Duplex'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_port_duplex__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_duplex',
                            'isys_port_duplex__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__DUPLEX',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_port_duplex'
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
            'speed'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__SPEED',
                        C__PROPERTY__INFO__DESCRIPTION => 'Speed'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__port_speed_value'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__SPEED_VALUE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'       => 'input-dual-small',
                            'p_strPlaceholder' => '0.00'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['speed']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'speed_type'
                    ]
                ]
            ),
            'speed_type'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_port_speed__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_speed',
                            'isys_port_speed__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__SPEED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_port_speed',
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'standard'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__STANDARD',
                        C__PROPERTY__INFO__DESCRIPTION => 'Standard'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_port_standard__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_standard',
                            'isys_port_standard__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__STANDARD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_port_standard'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'mac'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MAC',
                        C__PROPERTY__INFO__DESCRIPTION => 'MAC-address'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__mac'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__PORT__MAC'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_mac_address'
                                ]
                            ]
                        ]
                    ]
                ]
            ),
            'mtu'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MTU',
                        C__PROPERTY__INFO__DESCRIPTION => 'MTU',
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__mtu',

                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__PORT__MTU'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 0,
                                    'max_range' => 16436
                                ]
                            ]
                        ]
                    ]
                ]
            ),
            'assigned_connector' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_port_list__isys_catg_connector_list__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'connected_connector',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__DEST',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strValue'      => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_port',
                                    'callback_property_assigned_connector'
                                ]
                            ),
                            'p_strPopupType'  => 'browser_cable_connection_ng',
                            'secondSelection' => true,
                            'catFilter'       => 'C__CATG__CABLING;C__CATG__NETWORK',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'assigned_connector'
                        ]
                    ]
                ]
            ),
            'connector_sibling'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__SIBLING_IN_OR_OUT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned Input/Output'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_connector_list__id'
                    ],
                    // @todo This property has no field ID and has to be renamed.
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connector'
                        ]
                    ]
                ]
            ),
            'cable'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__CABLE_NAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cable ID'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_cable_connection__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__CABLE',
                        C__PROPERTY__UI__PARAMS => [
                            'catFilter'  => 'C__CATG__CABLE',
                            'p_strValue' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_port',
                                    'callback_property_cable'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'cable_connection'
                        ]
                    ]
                ]
            ),
            'active'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ACTIVE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Active'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__state_enabled'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__ACTIVE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'addresses'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Host address'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_ip_list_2_isys_catg_port_list',
                            'isys_catg_ip_list_2_isys_catg_port_list__isys_catg_port_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__IP_ADDRESS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist' => '1',
                            'p_arData'    => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_cluster_service',
                                    'callback_property_addresses'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__EXPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'hostaddress'
                        ]
                    ]
                ]
            ),
            'layer2_assignment'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_NET',
                        C__PROPERTY__INFO__DESCRIPTION => 'Layer 2 net',
                        C__PROPERTY__INFO__TYPE        => C__PROPERTY__INFO__TYPE__N2M
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LAYER2__DEST',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => 'true',
                            'p_strPopupType' => 'browser_object_ng',
                            'typeFilter'     => 'C__OBJTYPE__LAYER2_NET',
                            'p_strValue'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_port',
                                    'callback_property_layer2_assignment'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'port_assigned_layer2_nets'
                        ]
                    ]
                ]
            ),
            'hba'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__CON_INTERFACE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connected interface'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_catg_hba_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_hba_list',
                            'isys_catg_hba_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__INTERFACE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_port',
                                    'callback_property_interface'
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
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]
            ),
            'default_vlan'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__DEFAULT_VLAN',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connected interface'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_layer2_net_assigned_ports_list',
                            'isys_catg_port_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__DEFAULT_VLAN',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_port',
                                    'callback_property_default_vlan'
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
                            'isys_export_helper',
                            'network_port_property_default_vlan'
                        ]
                    ]
                ]
            ),
            'description'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CMDB__SUBCAT__NETWORK_PORT
                    ]
                ]
            ),
            'relation_direction' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'Relation direction',
                        C__PROPERTY__INFO__DESCRIPTION => 'Relation direction'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_relation_list__id'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__IMPORT     => true,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'relation_direction'
                        ]
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $l_is_master_obj = (isset($p_category_data['properties']['relation_direction'][C__DATA__VALUE])) ? (($p_category_data['properties']['relation_direction'][C__DATA__VALUE] == $p_object_id) ? true : false) : false;
            $l_cable_title   = (isset($p_category_data['properties']['cable_title'])) ? $p_category_data['properties']['cable_title'][C__DATA__VALUE] : null;

            /**
             * Checking if layer 2 net exists; Nulling if not.
             */
            if (isset($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE]))
            {
                if (!$this->obj_exists($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE]))
                {
                    $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE] = null;
                }
            }

            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:

                    if (!isset($p_category_data['properties']['active'][C__DATA__VALUE]) || $p_category_data['properties']['active'][C__DATA__VALUE] === null)
                    {
                        $p_category_data['properties']['active'][C__DATA__VALUE] = 1;
                    }

                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['interface'][C__DATA__VALUE],
                        $p_category_data['properties']['plug_type'][C__DATA__VALUE],
                        $p_category_data['properties']['port_type'][C__DATA__VALUE],
                        $p_category_data['properties']['port_mode'][C__DATA__VALUE],
                        $p_category_data['properties']['speed'][C__DATA__VALUE],
                        $p_category_data['properties']['speed_type'][C__DATA__VALUE],
                        $p_category_data['properties']['duplex'][C__DATA__VALUE],
                        $p_category_data['properties']['negotiation'][C__DATA__VALUE],
                        $p_category_data['properties']['standard'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['mac'][C__DATA__VALUE],
                        $p_category_data['properties']['active'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                        $p_category_data['properties']['cable'][C__DATA__VALUE],
                        $l_cable_title,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE],
                        $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                        $p_category_data['properties']['hba'][C__DATA__VALUE],
                        $l_is_master_obj,
                        $p_category_data['properties']['default_vlan'][C__DATA__VALUE],
                        $p_category_data['properties']['mtu'][C__DATA__VALUE]
                    );

                    break;
                case isys_import_handler_cmdb::C__UPDATE:

                    if ($p_category_data['data_id'] > 0)
                    {
                        // Create connector if it does not exist
                        $l_connector_id = $this->get_connector($p_category_data['data_id']);

                        if (!is_numeric($l_connector_id))
                        {
                            /**
                             * @var $l_daoConnection isys_cmdb_dao_category_g_connector
                             */
                            $l_daoConnection = isys_cmdb_dao_category_g_connector::instance($this->m_db);
                            $l_connector_id  = $l_daoConnection->create(
                                $p_object_id,
                                C__CONNECTOR__OUTPUT,
                                null,
                                null,
                                $p_category_data['properties']['title'][C__DATA__VALUE],
                                null,
                                $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                                null,
                                "C__CMDB__SUBCAT__NETWORK_PORT"
                            );

                            $l_strSQL = "UPDATE isys_catg_port_list SET	" . "isys_catg_port_list__isys_catg_connector_list__id = " . $this->convert_sql_id(
                                    $l_connector_id
                                ) . " " . "WHERE isys_catg_port_list__id = " . $this->convert_sql_id($p_category_data['data_id']);
                            $this->update($l_strSQL);
                        } // if

                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['interface'][C__DATA__VALUE],
                            $p_category_data['properties']['plug_type'][C__DATA__VALUE],
                            $p_category_data['properties']['port_type'][C__DATA__VALUE],
                            $p_category_data['properties']['port_mode'][C__DATA__VALUE],
                            $p_category_data['properties']['speed'][C__DATA__VALUE],
                            $p_category_data['properties']['speed_type'][C__DATA__VALUE],
                            $p_category_data['properties']['duplex'][C__DATA__VALUE],
                            $p_category_data['properties']['negotiation'][C__DATA__VALUE],
                            $p_category_data['properties']['standard'][C__DATA__VALUE],
                            null,
                            $p_category_data['properties']['mac'][C__DATA__VALUE],
                            $p_category_data['properties']['active'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                            $p_category_data['properties']['cable'][C__DATA__VALUE],
                            $l_cable_title,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE],
                            $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                            $p_category_data['properties']['hba'][C__DATA__VALUE],
                            $l_is_master_obj,
                            $p_category_data['properties']['default_vlan'][C__DATA__VALUE],
                            $p_category_data['properties']['mtu'][C__DATA__VALUE]
                        );
                    } // if
                    break;
            } // switch

            if (isset($p_category_data['data_id']) && $p_category_data['data_id'] > 0)
            {
                /**
                 * @var $l_dao_ip isys_cmdb_dao_category_g_ip
                 */
                $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);

                if (is_array($p_category_data['properties']['addresses'][C__DATA__VALUE]))
                {
                    foreach ($p_category_data['properties']['addresses'][C__DATA__VALUE] as $l_ip_id)
                    {
                        if (is_numeric($l_ip_id))
                        {
                            $this->attach_ip($p_category_data['data_id'], $l_ip_id);
                        }
                        else if (strstr($l_ip_id, '.'))
                        {
                            $l_ip = $l_dao_ip->get_ip_by_address($l_ip_id)
                                ->get_row();

                            if (is_array($l_ip) && isset($l_ip['isys_catg_ip_list__id']))
                            {
                                $this->attach_ip($p_category_data['data_id'], $l_ip['isys_catg_ip_list__id']);
                            }
                        }
                    } // foreach
                } // if
                else if (is_scalar($p_category_data['properties']['addresses'][C__DATA__VALUE]))
                {
                    $l_ip = $l_dao_ip->get_ip_by_address($p_category_data['properties']['addresses'][C__DATA__VALUE])
                        ->get_row();

                    if (is_array($l_ip) && isset($l_ip['isys_catg_ip_list__id']))
                    {
                        $this->attach_ip($p_category_data['data_id'], $l_ip['isys_catg_ip_list__id']);
                    }
                }
            } // if
        }

        return isset($p_category_data['data_id']) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Clears all ip attachments for $p_netp_port_id.
     *
     * @param   integer $p_port_id
     * @param   integer $p_ip_id
     *
     * @return  boolean
     */
    public function clear_ip_attachments($p_port_id = null, $p_ip_id = null)
    {
        if (isset($p_port_id) && $p_port_id > 0)
        {
            $l_delete = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL,
				isys_catg_ip_list__isys_catg_port_list__id = NULL
				WHERE isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id);

            $this->update($l_delete);
        } // if

        if (isset($p_ip_id) && $p_ip_id > 0)
        {
            $l_delete = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL,
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL
				WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_ip_id);

            $this->update($l_delete);
        } // if

        return $this->apply_update();
    } // function

    /**
     * Attaches an ip address to a port.
     *
     * @param   integer $p_netp_port_id
     * @param   integer $p_catg_ip_id
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function attach_ip($p_netp_port_id, $p_catg_ip_id)
    {
        if (is_numeric($p_netp_port_id) && is_numeric($p_catg_ip_id) && $p_catg_ip_id > 0 && $p_netp_port_id > 0)
        {
            $l_sql = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_id($p_netp_port_id) . '
				WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_catg_ip_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Saves a cable connection (port)
     *
     * @param integer $p_sourcePortID
     * @param integer $p_destPortID
     *
     * @return boolean
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function connection_save($p_sourcePortID, $p_destPortID = null, $p_cableID, $p_master_connector_id = null)
    {
        $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

        if (!is_numeric($p_destPortID))
        {
            if (!is_numeric($p_sourcePortID))
            {
                return true;
            } // if

            $l_conID = $l_dao->get_cable_connection_id_by_connector_id($p_sourcePortID);

            if ($l_conID != null)
            {
                $l_dao->delete_cable_connection($l_conID);
            } // if

            return true;
        } // if

        try
        {
            $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_sourcePortID));
            $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_destPortID));

            $l_conID = $l_dao->add_cable_connection($p_cableID);

            if (!$l_dao->save_connection($p_sourcePortID, $p_destPortID, $l_conID, $p_master_connector_id))
            {
                return false;
            } // if
        }
        catch (Exception $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        } // try

        return true;
    }

    /**
     * Create method.
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     * @param   integer $p_interface_id
     * @param   integer $p_plugtype_id
     * @param   integer $p_porttype_id
     * @param   integer $p_portmode_id
     * @param   mixed   $p_portspeed  Might be an integer or an float.
     * @param   integer $p_portspeedID
     * @param   integer $p_duplex_id
     * @param   integer $p_negotiation_id
     * @param   integer $p_standard_id
     * @param   unknown $p_net_object Seems unused.
     * @param   string  $p_mac
     * @param   integer $p_active
     * @param   string  $p_description
     * @param   integer $p_connectorID
     * @param   integer $p_cableID
     * @param   string  $p_cable_name
     * @param   integer $p_status
     * @param   integer $p_count
     * @param   integer $p_start_with
     * @param   unknown $p_with_null  Seems unused.
     * @param   integer $p_zero_calc
     * @param   integer $p_zero_points
     * @param   string  $p_suffix
     * @param   integer $p_suffix_type
     * @param    array  $p_layer2_objects
     *
     * @return  mixed  Integer of the last inserted ID, Boolean (false) On failure
     */
    public function create($p_object_id, $p_title, $p_interface_id, $p_plugtype_id, $p_porttype_id, $p_portmode_id, $p_portspeed, $p_portspeedID, $p_duplex_id, $p_negotiation_id, $p_standard_id, $p_net_object, $p_mac, $p_active, $p_description, $p_connectorID, $p_cableID = null, $p_cable_name, $p_status = C__RECORD_STATUS__NORMAL, $p_layer2_objects = null, $p_connector_sibling = null, $p_hba_id = null, $p_is_master_obj = null, $p_default_layer2_id = null, $p_mtu = null)
    {
        if ($p_portspeed > 0)
        {
            $p_portspeed = isys_convert::speed($p_portspeed, intval($p_portspeedID));
        }

        $l_connectortype_data = null;
        $l_port_id            = 0;

        /**
         * @var $l_daoConnection isys_cmdb_dao_category_g_connector
         */
        $l_daoConnection = isys_cmdb_dao_category_g_connector::instance($this->m_db);

        if (empty($p_portmode_id))
        {
            $l_port_mode_arr = $this->get_port_modes('C__PORT_MODE__STANDARD')
                ->get_row();
            $p_portmode_id   = $l_port_mode_arr['isys_port_mode__id'];
        }

        // Get Connector type for the connector
        if ($p_plugtype_id > 0)
        {
            $l_plugtype_data      = isys_factory_cmdb_dialog_dao::get_instance('isys_plug_type', $this->m_db)
                ->get_data($p_plugtype_id, null);
            $l_connectortype_data = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_db)
                ->get_data(null, $l_plugtype_data['title']);
        } // if

        $l_strTitle = $p_title;

        $l_connectorID = $l_daoConnection->create(
            $p_object_id,
            C__CONNECTOR__OUTPUT,
            null,
            ($l_connectortype_data) ? $l_connectortype_data['isys_connection_type__id'] : C__CONNECTION_TYPE__RJ45,
            $l_strTitle,
            null,
            $p_connector_sibling,
            null,
            "C__CMDB__SUBCAT__NETWORK_PORT"
        );

        $l_q = "INSERT INTO isys_catg_port_list (" . "isys_catg_port_list__isys_catg_netp_list__id, " . "isys_catg_port_list__isys_obj__id, " . "isys_catg_port_list__isys_port_negotiation__id, " . "isys_catg_port_list__isys_port_standard__id, " . "isys_catg_port_list__isys_port_duplex__id, " . "isys_catg_port_list__isys_plug_type__id, " . "isys_catg_port_list__isys_port_type__id, " . "isys_catg_port_list__isys_port_mode__id, " . "isys_catg_port_list__port_speed_value, " . "isys_catg_port_list__isys_port_speed__id, " . "isys_catg_port_list__title, " . "isys_catg_port_list__description, " . "isys_catg_port_list__mac, " . "isys_catg_port_list__state_enabled, " . "isys_catg_port_list__isys_catg_connector_list__id, " . "isys_catg_port_list__status, " . "isys_catg_port_list__isys_catg_hba_list__id, " . "isys_catg_port_list__mtu " . ") VALUES (" . $this->convert_sql_id(
                $p_interface_id
            ) . ", " . $this->convert_sql_id($p_object_id) . ", " . $this->convert_sql_id($p_negotiation_id) . ", " . $this->convert_sql_id(
                $p_standard_id
            ) . ", " . $this->convert_sql_id($p_duplex_id) . ", " . $this->convert_sql_id($p_plugtype_id) . ", " . $this->convert_sql_id(
                $p_porttype_id
            ) . ", " . $this->convert_sql_id($p_portmode_id) . ", " . "'" . $p_portspeed . "', " . $this->convert_sql_id($p_portspeedID) . ", " . $this->convert_sql_text(
                $l_strTitle
            ) . ", " . $this->convert_sql_text($p_description) . ", " . $this->convert_sql_text($p_mac) . ", " . "'" . $p_active . "', " . $this->convert_sql_id(
                $l_connectorID
            ) . ", " . "'" . $p_status . "', " . $this->convert_sql_id($p_hba_id) . ", " . $this->convert_sql_int($p_mtu) . ");";

        $l_bRet = $this->update($l_q);

        if ($l_bRet)
        {
            if ($this->apply_update())
            {
                $l_port_id = $this->get_last_insert_id();
                $this->attach_layer2_net($l_port_id, $p_layer2_objects, $p_default_layer2_id);

            } // if
        } // if

        $l_connectorRearID = $l_connectorID;

        if ($p_is_master_obj)
        {
            $l_master_connector = $l_connectorRearID;
        }
        else
        {
            $l_master_connector = $p_connectorID;
        }

        if ($p_connectorID != "")
        {
            if ($p_cableID == "")
            {
                $l_cableID = isys_cmdb_dao_cable_connection::recycle_cable(null);
            }
            else
            {
                $l_cableID = $p_cableID;
            } // if

            $this->connection_save($l_connectorRearID, $p_connectorID, $l_cableID, $l_master_connector);
        } // if

        if ($l_port_id > 0)
        {
            return $l_port_id;
        } // if

        return false;
    } // function

    /**
     * Delete all connections to any layer2 net
     *
     * @param int $p_cat_id PortID
     *
     * @return boolean
     */
    public function clear_layer2_attachments($p_cat_id)
    {
        $l_sql = "DELETE FROM isys_cats_layer2_net_assigned_ports_list " . "WHERE isys_catg_port_list__id = " . $this->convert_sql_id($p_cat_id) . ";";

        if ($this->update($l_sql)) return $this->apply_update();
        else
            return false;
    } // function

    /**
     * This method fetches the assigned host-addresses by a given port-id.
     *
     * @param   integer $p_cat_id
     * @param   boolean $p_only_primary
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_attached_ips($p_cat_id, $p_only_primary = false)
    {
        $l_sql = 'SELECT * FROM isys_catg_ip_list
			INNER JOIN isys_obj ON isys_catg_ip_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			LEFT JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
			WHERE isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_int($p_cat_id);

        if ($p_only_primary)
        {
            $l_sql .= ' AND isys_catg_ip_list__primary = 1';
        } // if

        // We need this for the "layer2 and layer3" report! Please don't remove!
        return $this->retrieve($l_sql . ' ORDER BY isys_catg_ip_list__primary DESC;');
    } // function

    /**
     * Gets the default vlan
     *
     * @return int|null
     */
    public function get_default_vlan_id($p_id)
    {
        if ($p_id > 0)
        {
            $l_sql = 'SELECT isys_cats_layer2_net_assigned_ports_list__isys_obj__id FROM isys_cats_layer2_net_assigned_ports_list
				WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_id) . ' AND isys_cats_layer2_net_assigned_ports_list__default = 1;';
            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows() === 1)
            {
                return $l_res->get_row_value('isys_cats_layer2_net_assigned_ports_list__isys_obj__id');
            } // if
        } // if
        return null;
    } // function

    /**
     *
     * @param   integer $p_cat_id
     * @param   boolean $p_json
     *
     * @return  mixed  PHP or JSON array.
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function get_attached_layer2_net_as_array($p_cat_id, $p_json = false)
    {
        $l_res_arr = [];
        $l_res     = $this->get_attached_layer2_net($p_cat_id);

        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                $l_res_arr[] = $l_row['object_id'];
                if ($l_row['default_vlan'])
                {
                    $this->m_default_vlan_id = $l_row['object_id'];
                }
            } // while
        } // if

        return ($p_json) ? isys_format_json::decode($l_res_arr) : $l_res_arr;
    }

    /**
     * Method for retrieving all attached layer2 nets.
     *
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_attached_layer2_net($p_cat_id)
    {
        $l_sql = 'SELECT isys_cats_layer2_net_assigned_ports_list__isys_obj__id AS object_id, isys_cats_layer2_net_list__ident AS vlan, isys_obj__title AS title, isys_cats_layer2_net_assigned_ports_list__default AS default_vlan
                FROM isys_cats_layer2_net_assigned_ports_list
                INNER JOIN isys_obj ON isys_cats_layer2_net_assigned_ports_list__isys_obj__id = isys_obj__id
                LEFT JOIN isys_cats_layer2_net_list ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id
                WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_cat_id) . '
                AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Attach a layer2 net to the Port.
     * You can deliver an array for $p_object_id
     *
     * @param (int) $p_cat_id PortID
     * @param (array|int) $p_object_id
     */
    public function attach_layer2_net($p_cat_id, $p_object_id, $p_default = 0)
    {
        $l_sql = "INSERT INTO isys_cats_layer2_net_assigned_ports_list SET " . "isys_catg_port_list__id = " . $this->convert_sql_id(
                $p_cat_id
            ) . " ," . "isys_cats_layer2_net_assigned_ports_list__status = " . C__RECORD_STATUS__NORMAL . " ," . "isys_cats_layer2_net_assigned_ports_list__default = %d," . "isys_cats_layer2_net_assigned_ports_list__isys_obj__id = '%s' ;";

        if (is_array($p_object_id))
        {
            foreach ($p_object_id AS $l_obj_id)
            {
                if ($l_obj_id > 0)
                {
                    $this->update(sprintf($l_sql, ($l_obj_id == $p_default ? 1 : 0), $this->convert_sql_id($l_obj_id)));
                }
            }

            return $this->apply_update();
        }

        if (!empty($p_object_id)) return (($this->update(sprintf($l_sql, ($p_object_id == $p_default ? 1 : 0), $p_object_id))) ? $this->apply_update() : false);

        return false;
    } // function

    /**
     * @param $p_port_id
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_connector($p_port_id)
    {
        return $this->retrieve(
            'SELECT isys_catg_port_list__isys_catg_connector_list__id AS con FROM isys_catg_port_list WHERE isys_catg_port_list__id = ' . $this->convert_sql_id(
                $p_port_id
            ) . ';'
        )
            ->get_row_value('con');
    } // function

    /**
     * Method for retrieving the maximum speed of a port from a given object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_unit_id
     *
     * @return  string
     */
    public function get_max_speed($p_obj_id, $p_unit_id = null)
    {

        $l_sql = "SELECT isys_catg_port_list__isys_port_speed__id, isys_catg_port_list__port_speed_value " . "FROM isys_catg_port_list " . "WHERE isys_catg_port_list__state_enabled = 1 " . "AND isys_catg_port_list__isys_obj__id = " . $this->convert_sql_id(
                $p_obj_id
            ) . ";";

        $l_res = $this->retrieve($l_sql);

        $l_max_speed = 0;

        while ($l_row = $l_res->get_row())
        {
            if (!is_null($p_unit_id))
            {
                if ($l_max_speed <= isys_convert::speed($l_row["isys_catg_port_list__port_speed_value"], $p_unit_id, C__CONVERT_DIRECTION__BACKWARD))
                {
                    $l_max_speed = isys_convert::speed($l_row["isys_catg_port_list__port_speed_value"], $p_unit_id, C__CONVERT_DIRECTION__BACKWARD);
                } // if
            }
            else
            {
                if ($l_max_speed <= $l_row["isys_catg_port_list__port_speed_value"])
                {
                    $l_max_speed = $l_row["isys_catg_port_list__port_speed_value"];
                } // if
            } // if
        } // while

        return $l_max_speed;
    } // function

    /**
     * Method for retrieving the port modes.
     *
     * @param int|string $p_value
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_port_modes($p_value = null)
    {
        $l_sql = 'SELECT * FROM isys_port_mode';

        if (is_numeric($p_value))
        {
            $l_sql .= ' AND isys_port_mode__id = ' . $this->convert_sql_id($p_value);
        }
        elseif (is_string($p_value) && strpos($p_value, 'C__'))
        {
            $l_sql .= ' AND isys_port_mode__const = ' . $this->convert_sql_text($p_value);
        }

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving the port types.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_port_types()
    {
        return $this->retrieve('SELECT * FROM isys_port_type;');
    }

    /**
     * Get ports by object and/or interface id.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_interface_id
     * @param   integer $p_status
     * @param   null    $p_netp_port_id
     * @param   array   $p_filter
     * @param   null    $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_ports($p_obj_id = null, $p_interface_id = null, $p_status = null, $p_netp_port_id = null, $p_filter = [], $p_condition = null, $p_order_by = false)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT
isys_catg_ip_list.*,
isys_port_duplex__id,
isys_port_duplex__title,
isys_port_duplex__const,
isys_port_mode__id,
isys_port_mode__title,
isys_port_mode__const,
isys_port_speed.*,
isys_port_negotiation__id,
isys_port_negotiation__title,
isys_port_negotiation__const,
isys_catg_netp_list.*,
isys_port_type.*,
isys_obj.*,
isys_catg_connector_list.*,
isys_cable_connection.*,
isys_catg_port_list.*,
connected_connector.isys_catg_connector_list__id AS con_connector,
isys_cats_net_ip_addresses_list__id,
isys_cats_net_ip_addresses_list__title,
isys_catg_hba_list.*,
GROUP_CONCAT(isys_cats_net_ip_addresses_list__title) AS assigned_ips
			FROM isys_catg_port_list
			LEFT JOIN isys_obj ON isys_catg_port_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_catg_port_list__id = isys_catg_port_list__id
			LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
			LEFT JOIN isys_catg_netp_list ON isys_catg_port_list__isys_catg_netp_list__id = isys_catg_netp_list__id
			LEFT JOIN isys_catg_hba_list ON isys_catg_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id
			LEFT JOIN isys_port_type ON isys_catg_port_list__isys_port_type__id = isys_port_type__id
			LEFT JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
			LEFT JOIN isys_cable_connection ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
			LEFT JOIN isys_catg_connector_list AS connected_connector ON connected_connector.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id AND (connected_connector.isys_catg_connector_list__id != isys_catg_connector_list.isys_catg_connector_list__id OR connected_connector.isys_catg_connector_list__id IS NULL)
			LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id
            LEFT JOIN isys_port_duplex ON isys_port_duplex__id = isys_catg_port_list__isys_port_duplex__id
            LEFT JOIN isys_port_mode ON isys_port_mode__id = isys_catg_port_list__isys_port_mode__id
            LEFT JOIN isys_port_negotiation ON isys_port_negotiation__id = isys_catg_port_list__isys_port_negotiation__id
			WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_interface_id !== null)
        {
            $l_sql .= " AND isys_catg_netp_list__id = " . $p_interface_id . " ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_port_list__status = '" . $p_status . "' ";
        } // if

        if ($p_netp_port_id !== null)
        {
            $l_sql .= " AND isys_catg_port_list__id = '" . $p_netp_port_id . "' ";
        } // if

        $l_sql .= "GROUP BY isys_catg_port_list__id ";

        if ($p_order_by)
        {
            $l_sql .= " ORDER BY ";

            if (is_array($p_obj_id))
            {
                $l_sql .= " isys_obj__id ASC, ";
            }
            $l_sql .= "LENGTH(isys_catg_port_list__title), isys_catg_port_list__title ASC, isys_catg_ip_list__primary DESC;";
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Delete port
     *
     * @param int $p_port_id
     *
     * @return bool
     */
    public function delete($p_port_id)
    {
        if (is_numeric($p_port_id))
        {
            $this->begin_update();

            $l_strSQL = "DELETE FROM isys_catg_port_list " . "WHERE isys_catg_port_list__id = " . $this->convert_sql_id($p_port_id);

            if ($this->update($l_strSQL))
            {
                return $this->apply_update();
            }
        }

        return false;
    } // function

    /**
     * Import-Handler for physical port including netport, port categories.
     *
     * @param   array $p_data Data with entries for port category 'ip'.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_object_id)
    {
        $l_status   = -1;
        $l_cat      = -1;
        $l_arPortID = [];

        // Handle ip and subnetmask
        if (is_array($p_data['ip']) && isset($p_data['ip'][1]))
        {
            $p_data['ip'] = strstr($p_data['ip'][1], '.') ? $p_data['ip'][1] : $p_data['ip'][0];
        } // if

        if (is_array($p_data['subnetmask']) && isset($p_data['subnetmask'][1]))
        {
            $p_data['subnetmask'] = strstr($p_data['subnetmask'][1], '.') ? $p_data['subnetmask'][1] : $p_data['subnetmask'][0];
        } // if

        if (is_array($p_data))
        {
            $l_sql = "DELETE FROM isys_catg_port_list WHERE " . "isys_catg_port_list__isys_obj__id = " . $this->convert_sql_id(
                    $p_object_id
                ) . " AND " . "isys_catg_port_list__mac = '" . $p_data['mac'] . "';";
            if ($this->update($l_sql) && $this->apply_update())
            {

                $l_objData = $this->get_object_by_id($p_object_id)
                    ->__to_array();
                $l_dao_ip  = new isys_cmdb_dao_category_g_ip($this->m_db);
                $l_dao_net = new isys_cmdb_dao_category_s_net($this->m_db);

                $l_arPort = $p_data;

                $_POST['C__CATG__PORT__TITLE']        = $l_arPort["name"];
                $_POST['C__CATG__PORT__MAC']          = $l_arPort["mac"];
                $_POST['C__CATG__PORT__ACTIVE']       = 1;
                $_POST['C__CATG__PORT__SUFFIX_COUNT'] = 1;

                unset($_GET[C__CMDB__GET__CATLEVEL_1]);

                isys_module_request::get_instance()
                    ->_internal_set_private("m_post", $_POST)
                    ->_internal_set_private("m_get", $_GET);

                // Save element and create netport and port categories along with it
                $l_port_id = $this->save_element($l_cat, $l_status);

                $l_arPortID[] = $l_port_id;

                if (isset($p_data["ip"]))
                {
                    if (is_array($p_data["ip"]))
                    {
                        foreach ($p_data["ip"] AS $l_key => $l_ip)
                        {
                            $l_subnetmask = $p_data["subnetmask"][$l_key];

                            /* Parse net type */
                            if (strstr($l_ip, ":"))
                            {
                                $l_net_type = C__CATS_NET_TYPE__IPV6;
                                $l_net      = C__OBJ__NET_GLOBAL_IPV6;

                                $l_net_ip      = Ip::validate_net_ipv6($l_ip, $l_subnetmask);
                                $l_cidr_suffix = $l_subnetmask;
                                $l_range       = Ip::calc_ip_range_ipv6($l_net_ip, $l_cidr_suffix);
                            }
                            else
                            {
                                $l_net_type = C__CATS_NET_TYPE__IPV4;
                                $l_net      = C__OBJ__NET_GLOBAL_IPV4;

                                $l_net_ip      = Ip::validate_net_ip($l_ip, $l_subnetmask, null, true);
                                $l_cidr_suffix = Ip::calc_cidr_suffix($l_subnetmask);
                                $l_range       = Ip::calc_ip_range($l_net_ip, $l_subnetmask);
                            } // if

                            if ($l_net_ip)
                            {
                                $l_condition = 'AND (isys_obj__title = ' . $l_dao_net->convert_sql_text(
                                        $l_net_ip
                                    ) . ' OR isys_cats_net_list__address = ' . $l_dao_net->convert_sql_text($l_net_ip) . ')';
                                $l_net_res   = $l_dao_net->get_data(null, null, $l_condition);
                                if ($l_net_res->num_rows() > 0)
                                {
                                    $l_net = $l_net_res->get_row_value('isys_obj__id');
                                }
                                elseif (isset($p_data["subnetmask"][$l_key]))
                                {

                                    // net does not exist
                                    // Create Layer-3 Net
                                    $l_net = $l_dao_net->insert_new_obj(C__OBJTYPE__LAYER3_NET, false, $l_net_ip, null, C__RECORD_STATUS__NORMAL);
                                    $l_dao_net->create(
                                        $l_net,
                                        C__RECORD_STATUS__NORMAL,
                                        $l_net_ip,
                                        $l_net_type,
                                        $l_net_ip,
                                        $l_subnetmask,
                                        '',
                                        false,
                                        $l_range['from'],
                                        $l_range['to'],
                                        null,
                                        null,
                                        '',
                                        $l_cidr_suffix
                                    );
                                } // if
                            } // if

                            /**
                             * Create ip for assignment
                             */
                            $l_ipdata = $l_dao_ip->get_ip_by_address($l_ip);

                            $l_prim_ip = $l_dao_ip->get_primary_ip($p_object_id)
                                ->get_row_value('isys_cats_net_ip_addresses_list__title');

                            if ($l_prim_ip)
                            {
                                if ($l_prim_ip != $l_ip)
                                {
                                    $l_primary = 0;
                                }
                                else
                                {
                                    $l_primary = 1;
                                } // if
                            }
                            else
                            {
                                $l_primary = 1;
                            } // if

                            /**
                             * These information is now stored in cats_net
                             * $p_data["subnetmask"]
                             * $p_data["gateway"]
                             */

                            if ($l_ipdata->num_rows() <= 0)
                            {
                                $l_ip_id = $l_dao_ip->create(
                                    $p_object_id,
                                    $l_objData["isys_obj__hostname"],
                                    C__CATP__IP__ASSIGN__STATIC,
                                    $l_ip,
                                    $l_primary,
                                    0,
                                    [],
                                    [],
                                    1,
                                    $l_net_type,
                                    $l_net,
                                    ''
                                );
                            }
                            else
                            {

                                $l_iprow = $l_ipdata->get_row();
                                $l_ip_id = $l_iprow['isys_catg_ip_list__id'];

                                if ($l_ip_id > 0)
                                {
                                    $l_dao_ip->save(
                                        $l_ip_id,
                                        $l_objData["isys_obj__hostname"],
                                        C__CATP__IP__ASSIGN__STATIC,
                                        $l_ip,
                                        $l_primary,
                                        0,
                                        [],
                                        [],
                                        1,
                                        $l_net_type,
                                        $l_net,
                                        ''
                                    );
                                } // if
                            } // if

                            $this->attach_ip($l_port_id, $l_ip_id);
                        } // foreach
                    }
                    else
                    {
                        /* Parse net type */
                        if (strstr($p_data["ip"], ":"))
                        {
                            $l_net_type = C__CATS_NET_TYPE__IPV6;
                            $l_net      = C__OBJ__NET_GLOBAL_IPV6;

                            $l_net_ip = Ip::validate_net_ipv6($p_data["ip"], $p_data["subnetmask"]);
                        }
                        else
                        {
                            $l_net_type = C__CATS_NET_TYPE__IPV4;
                            $l_net      = C__OBJ__NET_GLOBAL_IPV4;

                            $l_net_ip = Ip::validate_net_ip($p_data["ip"], $p_data["subnetmask"], null, true);
                        } // if

                        if ($l_net_ip)
                        {
                            $l_condition = 'AND (isys_obj__title = ' . $l_dao_net->convert_sql_text(
                                    $l_net_ip
                                ) . ' OR isys_cats_net_list__address = ' . $l_dao_net->convert_sql_text($l_net_ip) . ')';
                            $l_net_res   = $l_dao_net->get_data(null, null, $l_condition);
                            if ($l_net_res->num_rows() > 0)
                            {
                                $l_net = $l_net_res->get_row_value('isys_obj__id');
                            }
                            elseif (isset($p_data["subnetmask"]))
                            {
                                $l_range = Ip::calc_ip_range($l_net_ip, $p_data["subnetmask"]);
                                // net does not exist
                                // Create Layer-3 Net
                                $l_net = $l_dao_net->insert_new_obj(C__OBJTYPE__LAYER3_NET, false, $l_net_ip, null, C__RECORD_STATUS__NORMAL);
                                $l_dao_net->create(
                                    $l_net,
                                    C__RECORD_STATUS__NORMAL,
                                    $l_net_ip,
                                    $l_net_type,
                                    $l_net_ip,
                                    $p_data["subnetmask"],
                                    '',
                                    false,
                                    $l_range['from'],
                                    $l_range['to'],
                                    null,
                                    null,
                                    '',
                                    Ip::calc_cidr_suffix($p_data["subnetmask"])
                                );
                            } // if
                        } // if

                        /**
                         * Create ip for assignment
                         */
                        $l_ipdata = $l_dao_ip->get_ip_by_address($p_data["ip"]);

                        $l_prim_ip = $l_dao_ip->get_primary_ip($p_object_id)
                            ->get_row_value('isys_cats_net_ip_addresses_list__title');

                        if ($l_prim_ip)
                        {
                            if ($l_prim_ip != $p_data["ip"])
                            {
                                $l_primary = 0;
                            }
                            else
                            {
                                $l_primary = 1;
                            } // if
                        }
                        else
                        {
                            $l_primary = 1;
                        } // if

                        /**
                         * These information is now stored in cats_net
                         * $p_data["subnetmask"]
                         * $p_data["gateway"]
                         */

                        if ($l_ipdata->num_rows() <= 0)
                        {
                            $l_ip_id = $l_dao_ip->create(
                                $p_object_id,
                                $l_objData["isys_obj__hostname"],
                                C__CATP__IP__ASSIGN__STATIC,
                                $p_data["ip"],
                                $l_primary,
                                0,
                                [],
                                [],
                                1,
                                $l_net_type,
                                $l_net,
                                ''
                            );
                        }
                        else
                        {
                            $l_iprow = $l_ipdata->get_row();
                            $l_ip_id = $l_iprow['isys_catg_ip_list__id'];

                            if ($l_ip_id > 0)
                            {
                                $l_dao_ip->save(
                                    $l_ip_id,
                                    $l_objData["isys_obj__hostname"],
                                    C__CATP__IP__ASSIGN__STATIC,
                                    $p_data["ip"],
                                    $l_primary,
                                    0,
                                    [],
                                    [],
                                    1,
                                    $l_net_type,
                                    $l_net,
                                    ''
                                );
                            } // if
                        } // if

                        $this->attach_ip($l_port_id, $l_ip_id);
                    } // if
                } // if
            }
            else
            {
                throw new Exception("Error while deleting existing ports.");
            }
        }

        return $l_arPortID;
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

        switch ($p_context)
        {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_obj     = new $this($g_comp_database);
                $l_objects = $l_obj->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

                if ($l_objects->num_rows() > 0)
                {
                    while ($l_row = $l_objects->get_row())
                    {
                        $l_return[] = [
                            '__checkbox__'                => $l_row["isys_catg_port_list__id"],
                            isys_glob_utf8_encode('Port') => isys_glob_utf8_encode($l_row["isys_catg_port_list__title"])
                        ];
                    } // while
                } // if

                return json_encode($l_return);
                break;

            // @todo preselection stuff!
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $p_preselection = $p_parameters['preselection'];

                if ($p_preselection > 0)
                {
                    // Save a bit memory: Only select needed fields!
                    $l_sql = "SELECT * " . "FROM isys_catg_ip_list " . "INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id " . "LEFT JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id " . "WHERE isys_catg_ip_list__id IN (" . ($p_preselection) . ") " . "AND isys_obj__status = " . C__RECORD_STATUS__NORMAL;

                    $l_dao = new isys_component_dao($g_comp_database);

                    $l_res = $l_dao->retrieve($l_sql);

                    if ($l_res->num_rows() > 1)
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            // Prepare return data.
                            $l_return['category'][] = $l_row['isys_obj__isys_obj_type__id'];
                            $l_return['second'][]   = [
                                isys_glob_utf8_encode($l_row['isys_catg_ip_list__id']),
                                isys_glob_utf8_encode($l_row['isys_cats_net_ip_addresses_list__title']),
                            ]; // $l_line;
                        } // while
                    } // if
                } // if

                return $l_return;
                break;
        } // switch
    } // function

    /**
     *
     * @param   integer $p_port_id
     * @param   string  $p_title
     * @param   integer $p_interface_id
     * @param   integer $p_plugtype_id
     * @param   integer $p_porttype_id
     * @param   integer $p_portmode_id
     * @param   type    $p_portspeed
     * @param   integer $p_portspeedID
     * @param   integer $p_duplex_id
     * @param   integer $p_negotiation_id
     * @param   integer $p_standard_id
     * @param   type    $p_net_object
     * @param   string  $p_mac
     * @param   integer $p_active
     * @param   string  $p_description
     * @param   integer $p_connectorID
     * @param   integer $p_cableID
     * @param   string  $p_cable_name
     * @param   integer $p_status
     * @param    array  $p_layer2_objects
     *
     * @return  type
     */
    public function save($p_port_id, $p_title, $p_interface_id, $p_plugtype_id, $p_porttype_id, $p_portmode_id, $p_portspeed, $p_portspeedID, $p_duplex_id, $p_negotiation_id, $p_standard_id, $p_net_object, $p_mac, $p_active, $p_description, $p_connectorID, $p_cableID, $p_cable_name, $p_status = C__RECORD_STATUS__NORMAL, $p_layer2_objects = null, $p_connector_sibling = null, $p_hba_id = null, $p_is_master_obj = null, $p_default_layer2_id = null, $p_mtu = null)
    {
        if ($p_portspeed > 0)
        {
            $p_portspeed = isys_convert::speed($p_portspeed, intval($p_portspeedID));
        }

        $l_dao_cable_con = new isys_cmdb_dao_cable_connection($this->m_db);

        $l_nRetCode           = false;
        $l_connectortype_data = null;

        // Get Connector type for the connector
        if ($p_plugtype_id > 0)
        {
            $l_plugtype_data      = isys_factory_cmdb_dialog_dao::get_instance('isys_plug_type', $this->m_db)
                ->get_data($p_plugtype_id, null);
            $l_connectortype_data = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_db)
                ->get_data(null, $l_plugtype_data['title']);
        } // if

        $l_strSQL = "UPDATE " . "isys_catg_port_list " . "SET " . "isys_catg_port_list__isys_catg_netp_list__id = " . $this->convert_sql_id(
                $p_interface_id
            ) . ", " . "isys_catg_port_list__isys_plug_type__id = " . $this->convert_sql_id(
                $p_plugtype_id
            ) . ", " . "isys_catg_port_list__isys_port_negotiation__id = " . $this->convert_sql_id(
                $p_negotiation_id
            ) . ", " . "isys_catg_port_list__isys_port_standard__id = " . $this->convert_sql_id(
                $p_standard_id
            ) . ", " . "isys_catg_port_list__isys_port_duplex__id = " . $this->convert_sql_id(
                $p_duplex_id
            ) . ", " . "isys_catg_port_list__isys_port_type__id = " . $this->convert_sql_id(
                $p_porttype_id
            ) . ", " . "isys_catg_port_list__isys_port_mode__id = " . $this->convert_sql_id(
                $p_portmode_id
            ) . ", " . "isys_catg_port_list__port_speed_value = " . "'" . $p_portspeed . "', " . "isys_catg_port_list__isys_port_speed__id = " . $this->convert_sql_id(
                $p_portspeedID
            ) . ", " . "isys_catg_port_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_catg_port_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_port_list__mac = " . $this->convert_sql_text($p_mac) . ", " . "isys_catg_port_list__state_enabled = " . $this->convert_sql_int(
                $p_active
            ) . ", " . "isys_catg_port_list__status = " . $this->convert_sql_int($p_status) . ", " . "isys_catg_port_list__isys_catg_hba_list__id = " . $this->convert_sql_id(
                $p_hba_id
            ) . ", " . "isys_catg_port_list__mtu = " . $this->convert_sql_int($p_mtu) . " " . "WHERE " . "isys_catg_port_list__id = '" . $p_port_id . "';";

        if ($this->update($l_strSQL) && $this->apply_update())
        {

            /* Handle Layer2 Attachments */
            $this->clear_layer2_attachments($p_port_id);
            $this->attach_layer2_net($p_port_id, $p_layer2_objects, $p_default_layer2_id);

            $l_catg__id = $this->get_connector($p_port_id);

            if (is_numeric($l_catg__id) && $l_catg__id > 0)
            {
                $l_strSQL_connector = "UPDATE isys_catg_connector_list SET ";

                if ($p_connector_sibling > 0)
                {
                    $l_strSQL_connector .= "isys_catg_connector_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector_sibling) . ", ";
                } // if

                if ($l_connectortype_data)
                {
                    $l_strSQL_connector .= "isys_catg_connector_list__isys_connection_type__id = " . $this->convert_sql_id(
                            $l_connectortype_data['isys_connection_type__id']
                        ) . ", ";
                } // if
                $l_strSQL_connector .= "isys_catg_connector_list__title = " . $this->convert_sql_text(
                        $p_title
                    ) . " " . "WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($l_catg__id);

                $this->update($l_strSQL_connector);
                if (!$this->apply_update()) throw new isys_exception_cmdb("Error: Could not update Connector.");

                $l_connectorRearID = $l_catg__id;

                /**
                 * connectorReadID is the same as $p_connectorID in API calls!?
                 *
                 * @fixes ID-2128
                 */
                if ($l_connectorRearID != $p_connectorID)
                {
                    if (!empty($p_connectorID))
                    {

                        if ($p_is_master_obj)
                        {
                            $l_master_connector = $l_connectorRearID;
                        }
                        else
                        {
                            $l_master_connector = $p_connectorID;
                        }

                        if ($l_master_connector && $l_connectorRearID && $p_connectorID)
                        {
                            if (empty($p_cableID))
                            {
                                $l_cableID = $l_dao_cable_con->get_assigned_cable($l_catg__id);

                                if ($l_cableID === null)
                                {
                                    $l_cableID = isys_cmdb_dao_cable_connection::recycle_cable(null);
                                } // if
                            }
                            else
                            {
                                $l_cableID = $p_cableID;
                            } // if

                            $l_nRetCode = $this->connection_save($l_connectorRearID, $p_connectorID, $l_cableID, $l_master_connector);
                        }
                    }
                    else
                    {
                        if ($l_cable_connection_id = $l_dao_cable_con->get_cable_connection_id_by_connector_id($l_catg__id))
                        {
                            $l_nRetCode = $l_dao_cable_con->delete_cable_connection($l_cable_connection_id);
                        }
                        else
                        {
                            // No cable connection found
                            $l_nRetCode = true;
                        } // if
                    } // if
                }
            }
            else
            {
                throw new isys_exception_cmdb(
                    "Error: Your Port has lost its connector reference and is therefore inconsistent. " . "You should remove and recreate it in order to reference any other port."
                );
            } // if
        } // if
        return $l_nRetCode;
    } // function

    /**
     * Save global category port element.
     *
     * @param   integer &$p_cat_level       Level to save.
     * @param   integer &$p_intOldRecStatus __status of record before update.
     *
     * @return  integer
     * @throws  isys_exception_dao_cmdb
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        global $g_port_id;

        $l_nPortCount = 1;

        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_nPortID         = $l_posts["port_id"];
        $l_nPlugtypeID     = $l_posts["C__CATG__PORT__PLUG"];
        $l_PorttypeID      = $l_posts["C__CATG__PORT__TYPE"];
        $l_portmode_id     = $l_posts["C__CATG__PORT__MODE"];
        $l_nPortspeedID    = $l_posts["C__CATG__PORT__SPEED"];
        $l_nPortSpeedValue = $l_posts["C__CATG__PORT__SPEED_VALUE"];
        $l_nDuplexID       = $l_posts["C__CATG__PORT__DUPLEX"];
        $l_nNegotiationID  = $l_posts["C__CATG__PORT__NEGOTIATION"];
        $l_nStandardID     = $l_posts["C__CATG__PORT__STANDARD"];
        $l_cable_name      = $l_posts["C__CATG__PORT__DEST__CABLE_NAME"];
        $l_cableID         = $l_posts["C__CATG__PORT__CABLE__HIDDEN"];
        $l_layer2_objects  = (isys_format_json::is_json_array($l_posts['C__CATG__LAYER2__DEST__HIDDEN'])) ? json_decode(
            $l_posts['C__CATG__LAYER2__DEST__HIDDEN']
        ) : $l_posts['C__CATG__LAYER2__DEST__HIDDEN'];
        $l_nIfaceID        = null;
        $l_hba_id          = null;
        $l_mac             = null;

        if (!empty($l_posts["C__CATG__PORT__INTERFACE"]))
        {
            $l_interface_type     = substr(
                $l_posts["C__CATG__PORT__INTERFACE"],
                strpos($l_posts["C__CATG__PORT__INTERFACE"], '_') + 1,
                strlen($l_posts["C__CATG__PORT__INTERFACE"])
            );
            $l_interface_field_id = substr($l_posts["C__CATG__PORT__INTERFACE"], 0, strpos($l_posts["C__CATG__PORT__INTERFACE"], '_'));

            if (defined($l_interface_type))
            {
                switch ($l_interface_type)
                {
                    case 'C__CATG__HBA':
                        $l_nIfaceID = null;
                        $l_hba_id   = $l_interface_field_id;
                        break;
                    case 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P':
                        $l_nIfaceID = $l_interface_field_id;
                        $l_hba_id   = null;
                        break;
                }
            }
        } // if

        // New port or existing?
        if (!is_numeric($l_nPortID) || $l_nPortID <= 0)
        {
            $l_NewPort = true;

            // Determine how many ports are to be created.
            if (is_numeric($l_posts["C__CATG__PORT__SUFFIX_COUNT"]))
            {
                if ($l_posts["C__CATG__PORT__SUFFIX_COUNT"] > 1)
                {
                    $l_nPortCount = $l_posts["C__CATG__PORT__SUFFIX_COUNT"];
                } // if
            } // if
        }
        else
        {
            $l_NewPort = false;
        } // if

        // We convert all sorts of mac addresses to one "default" form.
        if (!empty($l_posts["C__CATG__PORT__MAC"]))
        {
            $l_mac_raw = preg_replace('/[\s\.\-\:]+/i', '', $l_posts["C__CATG__PORT__MAC"]);
            $l_mac     = [];

            if (strlen($l_mac_raw) == 48)
            {
                // We've got a binary!
                for ($i = 0;$i < 6;$i++)
                {
                    $l_mac[] = substr($l_mac_raw, ($i * 8), 8);
                } // for

                $l_mac = implode(':', $l_mac);
            }
            else
            {
                // We've got a HEX!
                for ($i = 0;$i < 6;$i++)
                {
                    $l_mac[] = substr($l_mac_raw, ($i * 2), 2);
                } // for

                $l_mac = implode(':', $l_mac);
            } // if
        } // if

        if ($l_NewPort)
        {

            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CATG__PORT', 'C__CATG__PORT__TITLE');

            for ($i = 0;$l_nPortCount > $i;$i++)
            {
                $l_title   = $l_title_arr[$i];
                $l_nPortID = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    $l_title,
                    $l_nIfaceID,
                    $l_nPlugtypeID,
                    $l_PorttypeID,
                    $l_portmode_id,
                    $l_nPortSpeedValue,
                    $l_nPortspeedID,
                    $l_nDuplexID,
                    $l_nNegotiationID,
                    $l_nStandardID,
                    $l_posts["C__CATG__PORT__NET__HIDDEN"],
                    $l_mac,
                    $l_posts["C__CATG__PORT__ACTIVE"],
                    $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $l_posts["C__CATG__PORT__DEST__HIDDEN"],
                    $l_cableID,
                    $l_cable_name,
                    C__RECORD_STATUS__NORMAL,
                    $l_layer2_objects,
                    null,
                    $l_hba_id,
                    null,
                    $l_posts['C__CATG__PORT__DEFAULT_VLAN'],
                    $l_posts['C__CATG__PORT__MTU']
                );
            }
            if ($l_nPortID > 0)
            {
                $l_nRetCode = null;
            } // if

            $p_cat_level = -1;
        }
        else
        {
            try
            {
                $this->save(
                    $l_nPortID,
                    $l_posts["C__CATG__PORT__TITLE"],
                    $l_nIfaceID,
                    $l_nPlugtypeID,
                    $l_PorttypeID,
                    $l_portmode_id,
                    $l_nPortSpeedValue,
                    $l_nPortspeedID,
                    $l_nDuplexID,
                    $l_nNegotiationID,
                    $l_nStandardID,
                    $l_posts["C__CATG__PORT__NET__HIDDEN"],
                    $l_mac,
                    $l_posts["C__CATG__PORT__ACTIVE"],
                    $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $l_posts["C__CATG__PORT__DEST__HIDDEN"],
                    $l_cableID,
                    $l_cable_name,
                    C__RECORD_STATUS__NORMAL,
                    $l_layer2_objects,
                    null,
                    $l_hba_id,
                    null,
                    $l_posts['C__CATG__PORT__DEFAULT_VLAN'],
                    $l_posts['C__CATG__PORT__MTU']
                );

                $l_nRetCode = null;
            }
            catch (isys_exception_dao_cmdb $e)
            {
                throw $e;
            } // try

        } // if

        // IP-Addresses.
        $l_ip_connection = explode(",", $l_posts["C__CATG__PORT__IP_ADDRESS__selected_values"]);

        $this->clear_ip_attachments($l_nPortID);
        if (count($l_ip_connection) > 0)
        {
            foreach ($l_ip_connection as $l_ip_id)
            {
                if ($l_ip_id > 0)
                {
                    $this->clear_ip_attachments(null, $l_ip_id);
                    $this->attach_ip($l_nPortID, $l_ip_id);
                } // if
            } // foreach
        } // if

        $g_port_id = $l_nPortID;

        return $l_nPortID;
    } // function

    /**
     * Compares category data for import.
     *
     * @todo Currently, every transformation (using helper methods) are skipped.
     * If your unique properties needs them, implement it!
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     */
    public function compare_category_data(&$p_category_data_values, &$p_object_category_dataset, &$p_used_properties, &$p_comparison, &$p_badness, &$p_mode, &$p_category_id, &$p_unit_key, &$p_category_data_ids, &$p_local_export, &$p_dataset_id_changed, &$p_dataset_id, &$p_logger, &$p_category_name = null, &$p_table = null, &$p_cat_multi = null, &$p_category_type_id = null, &$p_category_ids = null, &$p_object_ids = null, &$p_already_used_data_ids = null)
    {
        $l_unique_properties = [
            'isys_catg_port_list__mac'   => true,
            'isys_catg_port_list__title' => true
        ];

        $l_title = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'];
        $l_mac   = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['mac']['value'];

        unset($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['layer2_assignment']['value']);

        $l_mapping = [
            'isys_catg_port_list__title' => $l_title,
            'isys_catg_port_list__mac'   => $l_mac
        ];

        /*
        if (!$p_object_category_dataset['isys_catg_port_list__mac'] != $l_mac)
        {

        }

        if (!$p_object_category_dataset['isys_catg_port_list__title'] != $l_title)
        {

        }
        */
        $l_candidate = [];

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
        {
            $p_dataset_id_changed = false;
            $p_dataset_id         = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id]))
            {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id)
            {
                //$p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS)
                {
                    continue;
                } // if
            } // if

            if ($l_dataset['isys_catg_port_list__title'] == $l_title || (!empty($l_dataset['isys_catg_port_list__mac']) && $l_dataset['isys_catg_port_list__mac'] == $l_mac))
            {
                // Check properties
                $p_badness[$p_dataset_id] = 0;
                foreach ($l_mapping AS $l_table_key => $l_value)
                {
                    if ($l_dataset[$l_table_key] != $l_value)
                    {
                        $p_badness[$p_dataset_id]++;
                        if (isset($l_unique_properties[$l_table_key]))
                        {
                            $p_badness[$p_dataset_id] += 1000;
                            $l_candidate[$l_dataset_key] = $p_dataset_id;
                        } // if
                    } // if
                } // foreach

                if ($p_badness[$p_dataset_id] > isys_import_handler_cmdb::C__COMPARISON__THRESHOLD && $p_badness[$p_dataset_id] > 1000)
                {
                    //$p_logger->debug('Dataset differs completly from category data.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                }
                else if ($p_badness[$p_dataset_id] == 0)
                {
                    // We found our dataset
                    //$p_logger->debug('Dataset and category data are the same.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                }
                else
                {
                    //$p_logger->debug('Dataset differs partly from category data.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
                } // if
            }
            else
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            } // if
            // @todo check badness again
        } // foreach

        // In case we did not find any matching ports
        if (!isset($p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY]) && !empty($l_candidate))
        {
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY] = $l_candidate;
        } // if
    } // function

    /**
     *
     * @param   integer $p_port_id
     * @param   integer $p_interface_id
     *
     * @return  boolean
     */
    public function attach_interface($p_port_id, $p_interface_id)
    {
        if ($p_port_id > 0 && $p_interface_id > 0)
        {
            $l_update = 'UPDATE isys_catg_port_list
				SET isys_catg_port_list__isys_catg_netp_list__id = ' . $this->convert_sql_id($p_interface_id) . '
				WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id);

            return ($this->update($l_update) && $this->apply_update());
        } // if

        return false;
    } // function

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {

        if (!empty($p_data['port_type'])) $l_port_type = isys_import_handler::check_dialog('isys_port_type', $p_data['port_type']);
        else $l_port_type = null;

        if (!empty($p_data['plug_type'])) $l_plug_type = isys_import_handler::check_dialog('isys_plug_type', $p_data['plug_type']);
        else $l_plug_type = null;

        if (!empty($p_data['duplex'])) $l_duplex = isys_import_handler::check_dialog('isys_port_duplex', $p_data['duplex']);
        else $l_duplex = null;

        if (!empty($p_data['negotiation'])) $l_negotiation = isys_import_handler::check_dialog('isys_port_negotiation', $p_data['negotiation']);
        else $l_negotiation = null;

        if (!empty($p_data['standard'])) $l_standard = isys_import_handler::check_dialog('isys_port_standard', $p_data['standard']);
        else $l_standard = null;

        if (!is_numeric($p_data['speed_type'])) $l_speed_type = isys_import_handler::check_dialog('isys_port_speed', $p_data['speed_type']);
        else $l_speed_type = $p_data['speed_type'];

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'       => [
                    'value' => $p_data['title']
                ],
                'interface'   => [
                    'value' => $p_data['interface']
                ],
                'plug_type'   => [
                    'value' => $l_plug_type
                ],
                'port_type'   => [
                    'value' => $l_port_type
                ],
                'speed'       => [
                    'value' => $p_data['speed']
                ],
                'speed_type'  => [
                    'value' => $l_speed_type
                ],
                'duplex'      => [
                    'value' => $l_duplex
                ],
                'negotiation' => [
                    'value' => $l_negotiation
                ],
                'standard'    => [
                    'value' => $l_standard
                ],
                'mac'         => [
                    'value' => $p_data['mac']
                ],
                'active'      => [
                    'value' => $p_data['active']
                ],
                'addresses'   => [
                    'value' => $p_data['addresses']
                ],
                'description' => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    } // function
} // class