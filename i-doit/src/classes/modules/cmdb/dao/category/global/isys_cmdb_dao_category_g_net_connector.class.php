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
 * DAO: global category for network connector
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_net_connector extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_connector';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_net_listener_list__isys_obj__id';
    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'connected_to';
    /**
     * Dynamically manage the connected_to relation
     *
     * @var bool
     */
    protected $m_has_relation = true;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;
    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_net_connector_list__isys_obj__id';

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_relation_handler(isys_request $p_request, $p_parameters = [])
    {
        $l_return = [];

        if (($l_data_id = $p_request->get_category_data_id()))
        {
            $l_dao_net_con = isys_cmdb_dao_category_g_net_connector::instance(isys_application::instance()->database);
            $l_data        = $l_dao_net_con->get_data_by_id($l_data_id)
                ->get_row();

            if (isset($l_data[$l_dao_net_con->m_object_id_field]))
            {
                $l_return[C__RELATION_OBJECT__MASTER] = $l_data[$l_dao_net_con->m_connected_object_id_field];
                $l_return[C__RELATION_OBJECT__SLAVE]  = $l_data[$l_dao_net_con->m_object_id_field];
            } // if
        }
        else
        {
            $l_dao_listener = isys_cmdb_dao_category_g_net_listener::instance(isys_application::instance()->database);
            $l_listener     = $l_dao_listener->get_data_by_id(intval($p_request->get_data('connected_to')))
                ->get_row();
            if (isset($l_listener['isys_catg_net_listener_list__isys_obj__id']))
            {
                $l_return[C__RELATION_OBJECT__MASTER] = $l_listener['isys_catg_net_listener_list__isys_obj__id'];
                $l_return[C__RELATION_OBJECT__MASTER] = $p_request->get_object_id();
            } // if
        } // if
        return $l_return;
    } // function

    /**
     * Creates the distrubtion connector entry and returns its id.
     * If obj_id is null, the method takes it from $_GET parameter.
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    } // function

    /**
     * Return database field to be used as breadcrumb title
     *
     * @return string
     */
    public function get_breadcrumb_field($p_data = null)
    {
        return 'isys_cats_net_ip_addresses_list__title';
    } // function

    /**
     * Get entry identifier
     *
     * @param array $p_entry_data
     *
     * @return string
     */
    public function get_entry_identifier($p_entry_data)
    {
        $l_identifier = null;

        if (isset($p_entry_data['isys_catg_net_connector_list__isys_catg_net_listener_list__id']))
        {
            $l_dao = new isys_cmdb_dao_category_g_net_listener($this->get_database_component());

            $l_res = $l_dao->get_data($p_entry_data['isys_catg_net_connector_list__isys_catg_net_listener_list__id']);

            if ($l_res->num_rows())
            {
                $l_row = $l_res->get_row();

                $l_identifier = $l_row['isys_net_protocol__title'] . '/' . $l_row['isys_cats_net_ip_addresses_list__title'] . ':' . $l_row['isys_catg_net_listener_list__port_from'] . ($l_row['isys_obj__title'] ? ' | ' . $l_row['isys_obj__title'] : '');
            } // if
        } // if

        return $l_identifier;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    protected function properties()
    {
        return [
            'ip_address'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => '(Source) ip address'
                    ],
                    C__PROPERTY__DATA => [
                        /* isys_catg_net_connector_list__isys_cats_net_ip_addresses_list__id was too long, so field is shortened: */
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_net_connector_list__ip_addresses_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_net_ip_addresses_list',
                            'isys_cats_net_ip_addresses_list__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cats_net_ip_addresses_list',
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_net_connector',
                                    'callback_property_ip_addresses'
                                ]
                            )
                        ]
                    ]
                ]
            ),
            'port_from'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__FROM',
                        C__PROPERTY__INFO__DESCRIPTION => 'Port from'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_net_connector_list__port_from'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_CONNECTOR__PORT_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'       => 'input-mini',
                            'p_strPlaceholder' => '1',
                            'default'          => '1024'
                        ]
                    ]
                ]
            ),
            'port_to'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__TO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Port to'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_net_connector_list__port_to'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_CONNECTOR__PORT_TO',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-mini',
                            'p_bInfoIconSpacer' => '0',
                            'p_strPlaceholder'  => '65535',
                            'default'           => '65535'
                        ]
                    ]
                ]
            ),
            'connected_to'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CONNECTED_WITH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connection to specific network listener'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_net_connector_list__isys_catg_net_listener_list__id',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID           => 'C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO',
                        C__PROPERTY__UI__PLACEHOLDER  => 'LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_PLACEHOLDER',
                        C__PROPERTY__UI__EMPTYMESSAGE => 'LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_EMPTY',
                        C__PROPERTY__UI__PARAMS       => [
                            isys_popup_browser_object_ng::C__MULTISELECTION => false,
                            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__NET_CONNECTIONS_FOLDER',
                            'p_strSelectedID'                               => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_net_connector',
                                    'callback_property_connected_to'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__VIRTUAL    => true,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ]
                ]
            ),
            'connected_listener' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NET_LISTENER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connection to specific network listener'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_net_connector_list__isys_catg_net_listener_list__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__NET_CONNECTIONS,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_net_connector',
                                'callback_property_relation_handler'
                            ]
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_catg_net_listener_list',
                            'isys_catg_net_listener_list__id',
                            'isys_catg_net_listener_list__port_from'
                        ]
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID           => 'C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_LISTENER',
                        C__PROPERTY__UI__PLACEHOLDER  => 'LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_PLACEHOLDER',
                        C__PROPERTY__UI__EMPTYMESSAGE => 'LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_EMPTY',
                        C__PROPERTY__UI__PARAMS       => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_net_connector',
                                    'callback_property_connected_to_listener'
                                ]
                            ),
                            'p_bDbFieldNN' => 1,
                            'chosen'       => false
                        ]
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ]
                ]
            ),
            'gateway'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NET_CONNECTIONS__GATEWAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Firewall gateway'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_net_connector_list__gateway',
                        //C__PROPERTY__DATA__FIELD_ALIAS => 'opened_by',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'gateway',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_obj',
                            'isys_obj__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_CONNECTOR__GATEWAY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'browser_object_ng'
                        ]
                    ]
                ]
            ),
            'description'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_net_connector_list__description',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__NET_CONNECTOR
                    ]
                ]
            )
        ];
    }

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_connected_to(isys_request $p_request)
    {
        /**
         * @var isys_cmdb_dao_category_g_net_listener
         */
        $l_dao_listener = isys_cmdb_dao_category_g_net_connector::instance($this->m_db);
        $l_listener     = $l_dao_listener->get_data($p_request->get_category_data_id());

        if ($l_listener->num_rows())
        {
            return $l_listener->get_row_value('isys_catg_net_listener_list__isys_obj__id');
        } // if
        return null;
    } // function

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_connected_to_listener(isys_request $p_request)
    {
        /**
         * @var isys_cmdb_dao_category_g_net_listener
         */
        $l_dao_connector = isys_cmdb_dao_category_g_net_connector::instance($this->m_db);
        $l_res           = $l_dao_connector->get_data($p_request->get_category_data_id());

        if ($l_res->num_rows())
        {
            $l_obj_id       = $l_res->get_row_value('isys_catg_net_listener_list__isys_obj__id');
            $l_dao_listener = isys_cmdb_dao_category_g_net_listener::instance($this->m_db);
            $l_res          = $l_dao_listener->get_data(null, $l_obj_id);
            $l_return       = [];

            if ($l_res->num_rows())
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_return[$l_row['isys_catg_net_listener_list__id']] = $l_row['isys_net_protocol__title'] . '/' . $l_row['isys_cats_net_ip_addresses_list__title'] . ':' . $l_row['isys_catg_net_listener_list__port_from'] . ($l_row['isys_obj__title'] ? ' | ' . $l_row['isys_obj__title'] : '');
                } // while
            } // if
            return $l_return;
        } // if
        return null;
    }

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_ip_addresses(isys_request $p_request)
    {
        /**
         * @var isys_cmdb_dao_category_g_ip
         */
        $l_dao_ip    = isys_cmdb_dao_category_g_ip::instance($this->m_db);
        $l_object_id = $p_request->get_object_id();

        if ($this->get_type_by_object_id($l_object_id) == C__OBJTYPE__RELATION)
        {
            $l_dao_relation  = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_relation_data = $l_dao_relation->get_relation_members_by_obj_id($l_object_id);
            if (isset($l_relation_data[0]))
            {
                $l_object_id = $l_relation_data[0];
            } // if
        } // if

        $l_data = $l_dao_ip->get_ips_by_obj_id($l_object_id);

        $l_return = [];
        if ($l_data->num_rows())
        {
            while ($l_row = $l_data->get_row())
            {
                $l_return[$l_row['isys_cats_net_ip_addresses_list__id']] = $l_row['isys_cats_net_ip_addresses_list__title'];
            }
        } // if

        return $l_return;
    }

} // class