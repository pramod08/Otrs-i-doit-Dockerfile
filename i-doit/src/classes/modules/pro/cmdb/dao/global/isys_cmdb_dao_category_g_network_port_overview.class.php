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
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_network_port_overview extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'network_port_overview';
    /**
     * Category's constant
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW';
    /**
     * Category's identifier
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = false;
    /**
     * Main table where properties are stored persistently
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_catg_port_list';
    /**
     * Category's template.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_tpl = 'catg__port_overview.tpl';

    /**
     * Helper function so that we don´t have to change the parameter list of the get_data
     *
     * @param null   $p_catg_list_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     * @param string $p_order_by
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_port_overview_result($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null, $p_order_by = '')
    {
        $l_sql = "SELECT isys_catg_ip_list.*, isys_catg_netp_list__title, isys_catg_hba_list__title, isys_obj.*, isys_catg_connector_list.*, isys_catg_port_list.*
			FROM isys_catg_port_list
			LEFT JOIN isys_obj ON isys_catg_port_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_catg_port_list__id = isys_catg_port_list__id
			LEFT JOIN isys_catg_netp_list ON isys_catg_port_list__isys_catg_netp_list__id = isys_catg_netp_list__id
			LEFT JOIN isys_catg_hba_list ON isys_catg_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id
			LEFT JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_port_list__status = " . $this->convert_sql_int($p_status) . " ";
        } // if

        $l_sql .= " GROUP BY isys_catg_port_list__id ";

        if ($p_order_by)
        {
            $l_order = ' ORDER BY ' . $p_order_by;
        }
        else $l_order = '';

        return $this->retrieve($l_sql . $l_order);
    } // function

    /**
     * Loads the data for the port overview (will be called by ajax, through the "isys_ajax_handler_ports" class).
     *
     * @param   integer $p_obj_id
     * @param   integer $p_limit
     * @param   integer $p_offset
     *
     * @return  array
     */
    public function get_port_overview($p_obj_id, $p_limit = null, $p_offset = null)
    {
        $l_data                      = [];
        $l_ip_addresses_dao          = new isys_cmdb_dao_category_s_net_ip_addresses($this->get_database_component());
        $l_layer2_dao                = new isys_cmdb_dao_category_s_layer2_net($this->get_database_component());
        $l_layer2_assigned_ports_dao = new isys_cmdb_dao_category_s_layer2_net_assigned_ports($this->get_database_component());
        $l_layer3_dao                = new isys_cmdb_dao_category_s_net($this->get_database_component());
        $l_connector_dao             = new isys_cmdb_dao_category_g_connector($this->get_database_component());

        $l_port_res = $this->get_port_overview_result(null, $p_obj_id, '', null, C__RECORD_STATUS__NORMAL);

        $l_port_mode_res = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component())
            ->get_port_modes();

        while ($l_port_mode_row = $l_port_mode_res->get_row())
        {
            $l_port_modes[$l_port_mode_row['isys_port_mode__id']] = isys_glob_utf8_encode($l_port_mode_row['isys_port_mode__title']);
        } // while

        $l_counter = 1;
        while ($l_port_row = $l_port_res->get_row())
        {
            // This "should" not happen, but we'll never know...
            if ($l_port_row['isys_catg_connector_list__id'] === null)
            {
                continue;
            } // if

            // Here we assign the variable, which will be passed to the template later on.
            $l_first = $l_last = [
                'interface'  => '-',
                'layer2'     => '-',
                'layer3'     => '-',
                'ip_address' => '-',
                'mode'       => '-',
                'link'       => '-'
            ];

            $l_last_conn = null;

            $l_connector_res = $l_connector_dao->get_first_and_last_cable_run_object($l_port_row['isys_catg_connector_list__id']);

            $l_link = isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT   => $p_obj_id,
                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY,
                    C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                    C__CMDB__GET__CATG     => C__CMDB__SUBCAT__NETWORK_PORT,
                    C__CMDB__GET__CATLEVEL => $l_port_row['isys_catg_port_list__id']
                ]
            );

            preg_match('~.*?(\d+).*?~', $l_port_row['isys_catg_port_list__title'], $l_match);

            if (isset($l_match[1]))
            {
                $l_key = str_pad($l_match[1], 5, 0, STR_PAD_LEFT);
            }
            else
            {
                $l_key = $l_port_row['isys_catg_port_list__title'];
            } // if

            $l_key .= $l_counter++;

            $l_first['sort'] = $l_port_row['isys_catg_port_list__title'];

            $l_first['link'] = '<a href="' . $l_link . '">' . ($l_port_row['isys_catg_port_list__title'] !== '' ? $l_port_row['isys_catg_port_list__title'] : isys_tenantsettings::get(
                    'gui.empty_value',
                    '-'
                )) . '</a>';

            if (!empty($l_port_row['isys_catg_netp_list__title']))
            {
                $l_first['interface'] = ($l_port_row['isys_catg_netp_list__title']);
            }
            elseif (!empty($l_port_row['isys_catg_hba_list__title']))
            {
                $l_first['interface'] = ($l_port_row['isys_catg_hba_list__title']);
            } // if

            if (array_key_exists($l_port_row['isys_catg_port_list__isys_port_mode__id'], $l_port_modes))
            {
                $l_first['mode'] = ($l_port_modes[$l_port_row['isys_catg_port_list__isys_port_mode__id']]);
            } // if

            if ($l_connector_res['first']['CONNECTOR_ID'] != $l_connector_res['last']['CONNECTOR_ID'])
            {
                $l_last_conn = $l_connector_dao->get_data($l_connector_res['last']['CONNECTOR_ID'])
                    ->get_row();

                $l_last_port = $this->get_data(
                    null,
                    null,
                    'AND isys_catg_port_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($l_last_conn['isys_catg_connector_list__id'])
                )
                    ->get_row();

                $l_link = isys_helper_link::create_url(
                    [
                        C__CMDB__GET__OBJECT   => $l_last_conn['isys_obj__id'],
                        C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY,
                        C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                        C__CMDB__GET__CATG     => C__CMDB__SUBCAT__NETWORK_PORT,
                        C__CMDB__GET__CATLEVEL => $l_last_port['isys_catg_port_list__id']
                    ]
                );

                $l_last['link'] = '<a href="' . $l_link . '">' . (isset($l_connector_res['last']['CONNECTOR_TITLE']) && $l_connector_res['last']['CONNECTOR_TITLE'] !== '' ? $l_connector_res['last']['CONNECTOR_TITLE'] : isys_tenantsettings::get(
                        'gui.empty_value',
                        '-'
                    )) . '</a>';

                $l_last['obj_link'] = '<a href="' . isys_helper_link::create_url(
                        [C__CMDB__GET__OBJECT => $l_last_conn['isys_obj__id']]
                    ) . '">' . (isset($l_last_conn['isys_obj__title']) && $l_last_conn['isys_obj__title'] !== '' ? $l_last_conn['isys_obj__title'] : isys_tenantsettings::get(
                        'gui.empty_value',
                        '-'
                    )) . '</a>';
            } // if

            // Get the assigned layer2 nets.
            $l_layer2     = [];
            $l_layer2_res = $l_layer2_assigned_ports_dao->get_data(
                null,
                null,
                'AND isys_catg_port_list.isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
					AND isys_catg_port_list.isys_catg_port_list__id = ' . $this->convert_sql_id($l_port_row['isys_catg_port_list__id']) . (isys_tenantsettings::get(
                    'cmdb.limits.port-overview-default-vlan-only',
                    0
                ) ? ' AND isys_cats_layer2_net_assigned_ports_list__default = 1' : ''),
                null,
                C__RECORD_STATUS__NORMAL
            );

            $i     = 0;
            $l_max = isys_tenantsettings::get('cmdb.limits.port-lists-vlans', 5);
            while ($l_layer2_row = $l_layer2_res->get_row())
            {
                if ($i++ === $l_max)
                {
                    $l_layer2[] = '...';
                    break;
                }

                $l_layer2_obj_row = $l_layer2_dao->get_data(
                    null,
                    $l_layer2_row['isys_cats_layer2_net_assigned_ports_list__isys_obj__id'],
                    '',
                    null,
                    C__RECORD_STATUS__NORMAL
                )
                    ->get_row();

                // We prepare a user-friendly link.
                $l_layer2[] = '<a href="' . isys_helper_link::create_url(
                        [C__CMDB__GET__OBJECT => $l_layer2_row['isys_cats_layer2_net_assigned_ports_list__isys_obj__id']]
                    ) . '"' . ($l_layer2_row['isys_cats_layer2_net_assigned_ports_list__default'] ? ' class="bold"' : '') . '>' . $l_layer2_assigned_ports_dao->get_obj_name_by_id_as_string(
                        $l_layer2_row['isys_cats_layer2_net_assigned_ports_list__isys_obj__id']
                    ) . ' (' . $l_layer2_obj_row['isys_cats_layer2_net_list__ident'] . ')' . '</a>';
            } // while

            $l_first['layer2'] = implode(', ', array_unique($l_layer2));

            $l_first['layer2'] = (empty($l_first['layer2']) ? '-' : $l_first['layer2']);

            // Get the host-address for this port.
            $l_first['ip_address'] = '-';
            if ($l_port_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'] > 0)
            {
                $l_tmp             = $l_ip_addresses_dao->get_data($l_port_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'])
                    ->get_row();
                $l_first['layer3'] = $l_tmp['isys_obj__title'];
                $l_layer3          = $l_layer3_dao->get_data(null, $l_tmp['isys_cats_net_ip_addresses_list__isys_obj__id'])
                    ->get_row();

                if ($l_layer3['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
                {
                    $l_layer3['isys_cats_net_list__address'] = Ip::validate_ipv6($l_layer3['isys_cats_net_list__address'], true);
                } // if

                $l_first['layer3'] .= " ( " . $l_layer3['isys_cats_net_list__address'] . ' /' . $l_layer3['isys_cats_net_list__cidr_suffix'] . ")";
                $l_first['ip_address'] = $l_tmp['isys_cats_net_ip_addresses_list__title'];
            } // if

            $l_first['layer3'] = (empty($l_first['layer3']) ? '-' : $l_first['layer3']);

            if (!empty($l_last_conn))
            {
                $l_port_row = $this->get_data(
                    null,
                    $l_last_conn['isys_obj__id'],
                    'AND isys_catg_port_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($l_last_conn['isys_catg_connector_list__id'])
                )
                    ->get_row();

                if (!empty($l_port_row['isys_catg_netp_list__title']))
                {
                    $l_last['interface'] = $l_port_row['isys_catg_netp_list__title'];
                }
                elseif (!empty($l_port_row['isys_catg_hba_list__title']))
                {
                    $l_last['interface'] = $l_port_row['isys_catg_hba_list__title'];
                }
                $l_last['mode'] = (array_key_exists(
                    $l_port_row['isys_catg_port_list__isys_port_mode__id'],
                    $l_port_modes
                ) ? $l_port_modes[$l_port_row['isys_catg_port_list__isys_port_mode__id']] : '-');

                if ($l_port_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'] > 0)
                {
                    // The Host address.
                    $l_tmp            = $l_ip_addresses_dao->get_data($l_port_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'])
                        ->get_row();
                    $l_last['layer3'] = $l_tmp['isys_obj__title'];

                    if ($l_tmp['isys_cats_net_ip_addresses_list__isys_obj__id'] > 0)
                    {
                        // And layer3 net.
                        $l_last['ip_address'] = $l_layer3_dao->get_data(null, $l_tmp['isys_cats_net_ip_addresses_list__isys_obj__id'])
                            ->get_row();

                        if ($l_last['ip_address']['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
                        {
                            $l_last['ip_address']['isys_cats_net_list__address'] = Ip::validate_ipv6($l_last['ip_address']['isys_cats_net_list__address'], true);
                        } // if
                        $l_last['layer3'] .= " ( " . $l_last['ip_address']['isys_cats_net_list__address'] . ' /' . $l_last['ip_address']['isys_cats_net_list__cidr_suffix'] . " )";
                        $l_last['ip_address'] = $l_tmp['isys_cats_net_ip_addresses_list__title'];
                    } // if
                } // if

                // Get the assigned layer2 nets.
                $l_layer2     = [];
                $l_layer2_res = $l_layer2_assigned_ports_dao->get_data(
                    null,
                    null,
                    'AND isys_catg_port_list.isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_id(
                        $l_port_row['isys_obj__id']
                    ) . ' ' . 'AND isys_catg_port_list.isys_catg_port_list__id = ' . $this->convert_sql_id($l_port_row['isys_catg_port_list__id']),
                    null,
                    C__RECORD_STATUS__NORMAL
                );

                while ($l_layer2_row = $l_layer2_res->get_row())
                {
                    $l_layer2_obj_row = $l_layer2_dao->get_data(
                        null,
                        $l_layer2_row['isys_cats_layer2_net_assigned_ports_list__isys_obj__id'],
                        '',
                        null,
                        C__RECORD_STATUS__NORMAL
                    )
                        ->get_row();

                    // We prepare a user-friendly link.
                    $l_layer2[] = '<a href="' . isys_helper_link::create_url(
                            [C__CMDB__GET__OBJECT => $l_layer2_row['isys_cats_layer2_net_assigned_ports_list__isys_obj__id']]
                        ) . '"' . ($l_layer2_row['isys_cats_layer2_net_assigned_ports_list__default'] ? ' class="bold"' : '') . '>' . $l_layer2_assigned_ports_dao->get_obj_name_by_id_as_string(
                            $l_layer2_row['isys_cats_layer2_net_assigned_ports_list__isys_obj__id']
                        ) . ' ' . '(' . $l_layer2_obj_row['isys_cats_layer2_net_list__ident'] . ')' . '</a>';
                } // while

                $l_last['layer2'] = implode(', ', array_unique($l_layer2));

                $l_last['layer2'] = (empty($l_last['layer2']) ? '-' : $l_last['layer2']);
            } // if

            $l_data[$l_key] = [
                '_first' => $l_first,
                '_last'  => $l_last
            ];
        } // while

        if (isys_tenantsettings::get('gui.nat-sort.port-list', 1))
        {
            usort(
                $l_data,
                function ($a, $b)
                {
                    return strnatcasecmp($a['_first']['sort'], $b['_first']['sort']);
                }
            );
        }

        return array_values($l_data);
    } // function

    /**
     * Get-Count method for highlighting the category.
     *
     * @param   integer $p_obj_id
     *
     * @see     ID-2721
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        return count($this->get_port_overview_result(null, $p_obj_id, '', null, C__RECORD_STATUS__NORMAL));
    } // function

    /**
     * Method for retrieving data - Strongly modified SQL taken from isys_cmdb_dao_category_g_network_port->get_ports().
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_port_overview_result($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status);
    } // function

    /**
     * Get UI method.
     *
     * @global  isys_component_template $g_comp_template
     * @return  isys_cmdb_ui_category_g_network_port_overview
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function &get_ui()
    {
        global $g_comp_template;

        return new isys_cmdb_ui_category_g_network_port_overview($g_comp_template);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    } // function
} // class