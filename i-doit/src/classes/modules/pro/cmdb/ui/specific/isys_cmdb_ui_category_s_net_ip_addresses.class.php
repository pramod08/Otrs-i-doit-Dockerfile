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
 * CMDB specific category for IP addresses.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-8
 */
class isys_cmdb_ui_category_s_net_ip_addresses extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category net ip-addresses.
     *
     * @param   isys_cmdb_dao_category_s_net_ip_addresses $p_cat
     *
     * @return  array|void
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id = (int) $_GET[C__CMDB__GET__OBJECT];

        // We select the net address-range, to display the list.
        $l_net_dao = new isys_cmdb_dao_category_s_net($this->get_database_component());
        $l_net_res = $l_net_dao->get_data(null, $l_obj_id, '', C__RECORD_STATUS__NORMAL);

        // Here we prepare all variables, which get displayed in the template.
        $l_address_range_from      = 0;
        $l_address_range_to        = 0;
        $l_address_default_gateway = 0;
        $l_dhcp_ranges             = isys_format_json::encode([]);
        $l_net_address             = 0;
        $l_net_subnet_mask         = 0;
        $l_net_cidr_suffix         = 0;
        $l_catg_row                = [];

        if ($l_net_row = $l_net_res->get_row())
        {
            // We are processing some things specially for IPv4 and IPv6.
            if ($l_net_row['isys_net_type__id'] == C__CATS_NET_TYPE__IPV4)
            {
                $this->process_ipv4($p_cat);
            }
            else if ($l_net_row['isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
            {
                $this->process_ipv6($p_cat);
            } // if

            // We create a new instance for selecting the default gateway.
            $l_catg_ip = new isys_cmdb_dao_category_g_ip($this->get_database_component());

            if (!empty($l_net_row['isys_cats_net_list__isys_catg_ip_list__id']))
            {
                $l_catg_row = $l_catg_ip->get_data($l_net_row['isys_cats_net_list__isys_catg_ip_list__id'])
                    ->get_row();
            } // if

            // We also select all DHCP address-ranges, so that we can display them.
            $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($this->get_database_component());
            $l_dhcp_res = $l_dhcp_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

            $l_address_ranges = [];

            while ($l_dhcp_row = $l_dhcp_res->get_row())
            {
                $l_address_ranges[] = [
                    'from' => $l_dhcp_row['isys_cats_net_dhcp_list__range_from'],
                    'to'   => $l_dhcp_row['isys_cats_net_dhcp_list__range_to'],
                    'type' => $l_dhcp_row['isys_cats_net_dhcp_list__isys_net_dhcp_type__id']
                ];
            } // while

            $l_address_range_from      = $l_net_row['isys_cats_net_list__address_range_from'];
            $l_address_range_to        = $l_net_row['isys_cats_net_list__address_range_to'];
            $l_address_default_gateway = (!empty($l_catg_row['isys_cats_net_ip_addresses_list__title'])) ? $l_catg_row['isys_cats_net_ip_addresses_list__title'] : isys_tenantsettings::get(
                'gui.empty_value',
                '-'
            );
            $l_dhcp_ranges             = isys_format_json::encode($l_address_ranges);
            $l_net_address             = $l_net_row['isys_cats_net_list__address'];
            $l_net_subnet_mask         = $l_net_row['isys_cats_net_list__mask'];
            $l_net_cidr_suffix         = $l_net_row['isys_cats_net_list__cidr_suffix'];

            // We shorten the IPv6 addresses.
            if ($l_net_row['isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
            {
                $l_net_address        = Ip::validate_ipv6($l_net_address, true);
                $l_address_range_from = Ip::validate_ipv6($l_address_range_from, true);
                $l_address_range_to   = Ip::validate_ipv6($l_address_range_to, true);
                $l_net_subnet_mask    = Ip::validate_ipv6(Ip::calc_subnet_by_cidr_suffix_ipv6($l_net_cidr_suffix));
            } // if
        } // if

        $this->get_template_component()
            ->activate_editmode()
            ->assign(
                'has_edit_right',
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $l_obj_id, 'C__CATS__NET_IP_ADDRESSES')
            )
            ->assign(
                'has_execute_right',
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EXECUTE, $l_obj_id, 'C__CATS__NET_IP_ADDRESSES')
            )
            ->assign('address_range_from', $l_address_range_from)
            ->assign('address_range_to', $l_address_range_to)
            ->assign('address_default_gateway', $l_address_default_gateway)
            ->assign('dhcp_ranges', $l_dhcp_ranges)
            ->assign('net_address', $l_net_address)
            ->assign('net_subnet_mask', $l_net_subnet_mask)
            ->assign('net_cidr_suffix', $l_net_cidr_suffix)
            ->assign('obj_id', $l_obj_id)
            ->assign('is_global_net', ($_GET[C__CMDB__GET__OBJECT] == C__OBJ__NET_GLOBAL_IPV4 || $_GET[C__CMDB__GET__OBJECT] == C__OBJ__NET_GLOBAL_IPV6))
            ->assign('bShowCommentary', 0);

        // Setting the edit-button inactive.
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT);
    } // function

    /**
     * Special process method for IPv4 list.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     *
     * @param   isys_cmdb_dao_category                   $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function process_ipv4(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_template_language_manager;

        $l_address_conflict    = false;
        $l_duplicate_addresses = [];
        $l_obj_id              = (int) $_GET[C__CMDB__GET__OBJECT];
        $l_quickinfo           = new isys_ajax_handler_quick_info;

        $l_cache = isys_caching::factory('catg_net_ip_addresses__' . $l_obj_id, isys_tenantsettings::get('cmdb.ip-list.cache-lifetime', isys_convert::DAY));

        /**
         * @var  isys_cmdb_dao_category_s_net $l_net_dao
         * @var  isys_cmdb_dao_category_g_ip  $l_ip_dao
         */
        $l_net_dao = isys_cmdb_dao_category_s_net::instance($this->m_database_component);

        // Get the assigned hosts of our net.
        $l_hosts               = [];
        $l_non_addressed_hosts = [];
        $l_hosts_res           = $l_net_dao->get_assigned_hosts_with_dns($l_obj_id, C__RECORD_STATUS__NORMAL);

        while ($l_hosts_row = $l_hosts_res->get_row())
        {
            if (in_array(
                $l_hosts_row['isys_cats_net_ip_addresses_list__title'],
                [
                    '',
                    '0.0.0.0',
                    'D.H.C.P'
                ]
            ))
            {
                if (isset($l_non_addressed_hosts['id-' . $l_hosts_row['isys_catg_ip_list__id']]) && $l_hosts_row['isys_net_dns_domain__title'] !== null)
                {
                    // Add the DNS.
                    $l_non_addressed_hosts['id-' . $l_hosts_row['isys_catg_ip_list__id']]['domains'][] = $l_hosts_row['isys_net_dns_domain__title'];
                    continue;
                } // if

                // The key is just used, so that we get an JSON object, and no array.
                $l_non_addressed_hosts['id-' . $l_hosts_row['isys_catg_ip_list__id']] = [
                    'catg_ip_id'      => $l_hosts_row['isys_catg_ip_list__id'],
                    'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                    'isys_obj__title' => $l_hosts_row['isys_obj__title'] . ' (' . _L($l_hosts_row['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_hosts_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    'assignment__id'  => $l_hosts_row['isys_catg_ip_list__isys_ip_assignment__id'],
                    'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                    'domains'         => ($l_hosts_row['isys_net_dns_domain__title'] !== null ? [$l_hosts_row['isys_net_dns_domain__title']] : null),
                    'domain'          => ($l_hosts_row['isys_net_dns_domain__title'] !== null ? $l_hosts_row['isys_net_dns_domain__title'] : false)
                ];
            }
            else
            {
                if (isset($l_hosts[$l_hosts_row['isys_cats_net_ip_addresses_list__title']][$l_hosts_row['isys_catg_ip_list__id']]) && $l_hosts_row['isys_net_dns_domain__title'] !== null)
                {
                    // Add the DNS.
                    $l_hosts[$l_hosts_row['isys_cats_net_ip_addresses_list__title']][$l_hosts_row['isys_catg_ip_list__id']]['domains'][] = $l_hosts_row['isys_net_dns_domain__title'];
                    continue;
                } // if

                $l_hosts[$l_hosts_row['isys_cats_net_ip_addresses_list__title']][$l_hosts_row['isys_catg_ip_list__id']] = [
                    'catg_ip_id'      => $l_hosts_row['isys_catg_ip_list__id'],
                    'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                    'isys_obj__title' => $l_hosts_row['isys_obj__title'] . ' (' . _L($l_hosts_row['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_hosts_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    'assignment__id'  => $l_hosts_row['isys_catg_ip_list__isys_ip_assignment__id'],
                    'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                    'domains'         => ($l_hosts_row['isys_net_dns_domain__title'] !== null ? [$l_hosts_row['isys_net_dns_domain__title']] : null),
                    'domain'          => ($l_hosts_row['isys_net_dns_domain__title'] !== null ? $l_hosts_row['isys_net_dns_domain__title'] : false)
                ];

                // Display a message, that there are IP-address conflicts
                if (count($l_hosts[$l_hosts_row['isys_cats_net_ip_addresses_list__title']]) > 1)
                {
                    $l_duplicate_addresses[] = $l_hosts_row['isys_cats_net_ip_addresses_list__title'];
                    $l_address_conflict      = true;
                } // if
            } // if
        } // while

        // When the array is empty, we can't give an empty JSON array to the template because that will break the $H() object.
        if (count($l_hosts) > 0)
        {
            $l_hosts = isys_format_json::encode($l_hosts);
        }
        else
        {
            // This will do the trick!
            $l_hosts = '{}';
        } // if

        // Same thing as above!
        if (count($l_non_addressed_hosts) > 0)
        {
            $l_non_addressed_hosts = isys_format_json::encode($l_non_addressed_hosts);
        }
        else
        {
            // This will do the trick!
            $l_non_addressed_hosts = '{}';
        } // if

        $l_ping_method = isys_tenantsettings::get('cmdb.ip-list.ping-method', 'nmap');

        $l_layer2_net = $l_net_dao->get_assigned_layer_2_ids($l_obj_id, true);

        if (is_array($l_layer2_net) && count($l_layer2_net))
        {
            foreach ($l_layer2_net as &$l_layer2_obj)
            {
                $l_layer2_obj = $l_quickinfo->get_quick_info($l_layer2_obj, $p_cat->get_obj_name_by_id_as_string($l_layer2_obj), C__LINK__OBJECT);
            } // foreach
        } // if

        $l_supernet = isys_tenantsettings::get('gui.empty_value', '-');

        $l_supernet_res = $l_net_dao->find_responsible_supernet($l_obj_id);

        if (count($l_supernet_res))
        {
            $l_supernet = [];

            while ($l_supernet_row = $l_supernet_res->get_row())
            {
                $l_supernet[] = $l_quickinfo->get_quick_info(
                    $l_supernet_row['isys_obj__id'],
                    $l_supernet_row['isys_obj__title'],
                    isys_helper_link::create_url(
                        [
                            C__CMDB__GET__OBJECT   => $l_supernet_row['isys_obj__id'],
                            C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                            C__CMDB__GET__CATG     => C__CATG__VIRTUAL_SUPERNET
                        ]
                    )
                );
            } // while

            $l_supernet = implode(', ', $l_supernet);
        } // if

        $this->get_template_component()
            ->assign(
                'cache_data',
                isys_format_json::encode(
                    $l_cache->get() ?: [
                        'pings'              => [],
                        'nslookup_hostnames' => [],
                        'nslookup_ips'       => []
                    ]
                )
            )
            ->assign('ping_available', !!system_which($l_ping_method))
            ->assign('ping_method', strtoupper($l_ping_method))
            ->assign('nslookup_available', !!system_which('nslookup'))
            ->assign('hosts', $l_hosts)
            ->assign('non_addressed_hosts', $l_non_addressed_hosts)
            ->assign('address_conflict', $l_address_conflict)
            ->assign('address_conflict_ips', $l_duplicate_addresses)
            ->assign('supernet', $l_supernet)
            ->assign('layer2_net', $l_layer2_net)
            ->assign('ipv4', true);

        $l_dao = new isys_cmdb_dao($this->get_database_component());

        // Prepare some rules for the object browser.
        $l_rules['C__CATS__IP_ADDRESSES']['typeFilter'] = implode(';', $l_dao->get_object_types_by_category(C__CATG__IP));
        $l_rules                                        = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    } // function

    /**
     * Special process method for IPv6 list.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     * @global  array                                    $g_dirs
     *
     * @param   isys_cmdb_dao_category                   $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function process_ipv6(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_template_language_manager, $g_dirs;

        $l_obj_id              = (int) $_GET[C__CMDB__GET__OBJECT];
        $l_address_conflict    = false;
        $l_duplicate_addresses = $l_hosts = $l_non_addressed_hosts = [];

        $l_net_dao = new isys_cmdb_dao_category_s_net($this->get_database_component());

        // Get the assigned hosts of our net.
        $l_hosts_res = $l_net_dao->get_assigned_hosts($l_obj_id);

        while ($l_hosts_row = $l_hosts_res->get_row())
        {
            $l_dns_domain = $l_net_dao->get_assigned_dns_domain(null, $l_hosts_row['isys_catg_ip_list__id'])
                ->get_row('isys_net_dns_domain__title');

            // Maybe we should check for more than just "empty".
            if (empty($l_hosts_row['isys_cats_net_ip_addresses_list__title']))
            {
                // The key is just used, so that we get an JSON object, and no array.
                $l_non_addressed_hosts['id-' . $l_hosts_row['isys_catg_ip_list__id']] = [
                    'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                    'isys_obj__title' => isys_glob_utf8_encode(
                        $l_hosts_row['isys_obj__title'] . ' (' . $g_comp_template_language_manager->get($l_hosts_row['isys_obj_type__title']) . ')'
                    ),
                    'assignment__id'  => isys_glob_utf8_encode($l_hosts_row['isys_catg_ip_list__isys_ipv6_assignment__id']),
                    'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                    'domain'          => $l_dns_domain ?: false
                ];
            }
            else
            {
                $l_hosts[Ip::validate_ipv6($l_hosts_row['isys_cats_net_ip_addresses_list__title'], true)][] = [
                    'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                    'isys_obj__title' => isys_glob_utf8_encode(
                        $l_hosts_row['isys_obj__title'] . ' (' . $g_comp_template_language_manager->get($l_hosts_row['isys_obj_type__title']) . ')'
                    ),
                    'assignment__id'  => isys_glob_utf8_encode($l_hosts_row['isys_catg_ip_list__isys_ipv6_assignment__id']),
                    'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                    'domain'          => $l_dns_domain ?: false
                ];

                // Display a message, that there are IP-address conflicts
                if (count($l_hosts[Ip::validate_ipv6($l_hosts_row['isys_cats_net_ip_addresses_list__title'], true)]) > 1)
                {
                    $l_duplicate_addresses[] = Ip::validate_ipv6($l_hosts_row['isys_cats_net_ip_addresses_list__title']);
                    $l_address_conflict      = true;
                } // if
            } // if
        } // while

        // When the array is empty, we can't give an empty JSON array to the template because that will break the $H() object.
        if (count($l_hosts) > 0)
        {
            $l_hosts = isys_format_json::encode($l_hosts);
        }
        else
        {
            // This will do the trick!
            $l_hosts = '{}';
        } // if

        // Same thing as above!
        if (count($l_non_addressed_hosts) > 0)
        {
            $l_non_addressed_hosts = isys_format_json::encode($l_non_addressed_hosts);
        }
        else
        {
            // This will do the trick!
            $l_non_addressed_hosts = '{}';
        } // if

        $this->get_template_component()
            ->assign('hosts', $l_hosts)
            ->assign('image_path', $g_dirs['images'])
            ->assign('non_addressed_hosts', $l_non_addressed_hosts)
            ->assign('address_conflict', $l_address_conflict)
            ->assign('address_conflict_ips', $l_duplicate_addresses)
            ->assign('ipv6', true);
    } // function
} // class