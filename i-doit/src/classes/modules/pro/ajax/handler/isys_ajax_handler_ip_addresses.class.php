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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-8
 */
class isys_ajax_handler_ip_addresses extends isys_ajax_handler
{
    /**
     * @var $m_event_manager isys_event_manager
     */
    private $m_event_manager;

    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        if (!empty($_GET['method']))
        {
            $this->m_event_manager = isys_event_manager::getInstance();

            switch ($_GET['method'])
            {
                case 'ping':
                    echo isys_format_json::encode($this->ping_ip(isys_format_json::decode($_POST['ip']), $_POST['net_obj']));
                    break;

                case 'r_nslookup':
                    echo isys_format_json::encode($this->reverse_nslookup(isys_format_json::decode($_POST['catg_ip_id']), $_POST['net_obj']));
                    break;

                case 'nslookup':
                    echo isys_format_json::encode($this->nslookup(isys_format_json::decode($_POST['catg_ip_id']), $_POST['net_obj']));
                    break;

                case 'update-ip-address':
                    echo isys_format_json::encode($this->update_ip_address($_POST['catg_ip_id'], $_POST['new_ip'], $_POST['net_obj_id']));
                    break;

                case 'update-hostname':
                    echo isys_format_json::encode($this->update_hostname($_POST['catg_ip_id'], $_POST['new_hostname'], $_POST['net_obj_id']));
                    break;

                case 'c':
                    echo isys_format_json::encode($this->connect_v4());
                    break;

                case 'd':
                    echo isys_format_json::encode($this->disconnect_v4());
                    break;

                case 'dv6':
                    echo isys_format_json::encode($this->disconnect_v6());
                    break;

                case 'show_ip_list':
                    global $g_comp_template;

                    $l_net_info = $this->get_all_net_information();

                    if (count($l_net_info['data']['hosts']) > 0)
                    {
                        $l_net_info['data']['hosts'] = isys_format_json::encode($l_net_info['data']['hosts']);
                    }
                    else
                    {
                        // This will do the trick!
                        $l_net_info['data']['hosts'] = '{}';
                    } // if

                    if (count($l_net_info['data']['non_addressed_hosts']) > 0)
                    {
                        $l_net_info['data']['non_addressed_hosts'] = isys_format_json::encode($l_net_info['data']['non_addressed_hosts']);
                    }
                    else
                    {
                        // This will do the trick!
                        $l_net_info['data']['non_addressed_hosts'] = '{}';
                    } // if

                    $l_cache = isys_caching::factory(
                        'catg_net_ip_addresses__' . $_POST['net_object'],
                        isys_tenantsettings::get('cmdb.ip-list.cache-lifetime', isys_convert::DAY)
                    );

                    $g_comp_template->assign(
                        'cache_data',
                        isys_format_json::encode(
                            $l_cache->get() ?: [
                                'pings'              => [],
                                'nslookup_hostnames' => [],
                                'nslookup_ips'       => []
                            ]
                        )
                    )
                        ->assign('has_edit_right', false)
                        ->assign(
                            'has_execute_right',
                            isys_auth_cmdb::instance()
                                ->has_rights_in_obj_and_category(isys_auth::EXECUTE, $_POST['net_object'], 'C__CATS__NET_IP_ADDRESSES')
                        )
                        ->assign('legend_scroller', (($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) ? 694 : 514))
                        ->assign('address_range_from', $l_net_info['data']['address_range_from'])
                        ->assign('address_range_to', $l_net_info['data']['address_range_to'])
                        ->assign('address_default_gateway', $l_net_info['data']['address_default_gateway'])
                        ->assign('hosts', $l_net_info['data']['hosts'])
                        ->assign('non_addressed_hosts', $l_net_info['data']['non_addressed_hosts'])
                        ->assign('obj_id', $_POST['net_object']);

                    switch ($_POST['net_type'])
                    {
                        case C__CATS_NET_TYPE__IPV4:
                            $l_ping_method = isys_tenantsettings::get('cmdb.ip-list.ping-method', 'nmap');

                            $g_comp_template->assign('ping_available', !!system_which($l_ping_method))
                                ->assign('ping_method', strtoupper($l_ping_method))
                                ->assign('nslookup_available', !!system_which('nslookup'))
                                ->assign('net_cidr_suffix', $l_net_info['data']['net_cidr_suffix'])
                                ->assign('dhcp_ranges', isys_format_json::encode($l_net_info['data']['dhcp_ranges']))
                                ->display("file:" . $this->m_smarty_dir . "templates/content/bottom/content/cats__net_ipv4_addresses.tpl");
                            break;
                        case C__CATS_NET_TYPE__IPV6:
                            $g_comp_template->display("file:" . $this->m_smarty_dir . "templates/content/bottom/content/cats__net_ipv6_addresses.tpl");
                            break;
                    } // switch

                    break;
            } // switch

            $this->_die();
        } // if
    } // function

    /**
     * Method for connecting a new object to a layer3 net.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function connect_v4()
    {
        $l_ip_dao  = new isys_cmdb_dao_category_g_ip($this->m_database_component);
        $l_net_dao = new isys_cmdb_dao_category_s_net($this->m_database_component);

        // For the "primary" field. We set this to 1 if it's the first entry for this connected object.
        $l_primary = 1;
        if ($l_ip_dao->get_data(null, $_POST[C__CMDB__GET__OBJECT . '2'])
                ->num_rows() > 0
        )
        {
            $l_primary = 0;
        } // if

        // For retrieving the DNS server and domain, we first need the net ID.
        $l_cats_net_id = $l_net_dao->get_data(null, $_POST[C__CMDB__GET__OBJECT])
            ->get_row();
        $l_cats_net_id = $l_cats_net_id['isys_cats_net_list__id'];

        // Here we fetch the assigned DNS server from the layer3 net.
        $l_dns_server = $l_net_dao->get_assigned_dns_server($l_cats_net_id);

        // And here the assigned DNS domain.
        $l_dns_domain     = [];
        $l_dns_domain_res = $l_net_dao->get_assigned_dns_domain(null, $l_cats_net_id);
        while ($l_dns_domain_row = $l_dns_domain_res->get_row())
        {
            $l_dns_domain[] = $l_dns_domain_row['isys_net_dns_domain__id'];
        } // while

        // ID of the values from "isys_ip_assignment" - static, dhcp, dhcp-reserved.
        $l_assign           = null;
        $l_ip_addresses_dao = new isys_cmdb_dao_category_s_net_ip_addresses($this->m_database_component);
        $l_ip_assignment    = $l_ip_addresses_dao->get_ip_assignment_by_ip($_POST['ip']);

        // If our IP-address is empty, we can assume we've got a unnumbered entry.
        if (empty($_POST['ip']))
        {
            $_POST['ip']     = '';
            $l_ip_assignment = C__CATP__IP__ASSIGN__UNNUMBERED;
        } // if

        $l_ip_dao->create(
            $_POST[C__CMDB__GET__OBJECT . '2'],    // The layer3 object ID.
            '',
            $l_ip_assignment,
            $_POST['ip'],
            $l_primary,
            null,
            $l_dns_server,                        // DNS server
            $l_dns_domain,                        // DNS domain
            1,                                    // We set the new connection "active".
            C__CATS_NET_TYPE__IPV4,                // IPv4 from isys_net_type, currently we don't .
            $_POST[C__CMDB__GET__OBJECT],        // The ID of the object, we want to connect to.
            '',
            C__RECORD_STATUS__NORMAL
        );

        $this->m_event_manager->triggerCMDBEvent(
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
            $l_ip_dao->get_last_query(),
            $_POST[C__CMDB__GET__OBJECT],
            $l_ip_dao->get_objTypeID($_POST[C__CMDB__GET__OBJECT]),
            _L('LC__CMDB__CATS__NET_IP_ADDRESSES'),
            null,
            sprintf(
                _L('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_ASSIGNED'),
                $_POST['ip'],
                $l_ip_dao->get_obj_name_by_id_as_string($_POST['objID2'])
            )
        );

        $l_new_res = $l_net_dao->get_assigned_hosts($_POST[C__CMDB__GET__OBJECT]);

        while ($l_low = $l_new_res->get_row())
        {
            $l_dns_domains = [];
            $l_dns_res     = $l_ip_dao->get_assigned_dns_domain(null, $l_low['isys_catg_ip_list__id']);

            if (count($l_dns_res))
            {
                while ($l_dns_row = $l_dns_res->get_row())
                {
                    $l_dns_domains[] = $l_dns_row['isys_net_dns_domain__title'];
                } // while
            } // if

            if ($l_low['isys_cats_net_ip_addresses_list__title'] == '0.0.0.0' || $l_low['isys_cats_net_ip_addresses_list__title'] == '' || $l_low['isys_cats_net_ip_addresses_list__title'] == 'D.H.C.P')
            {
                // As in the DAO - We only set the ID-key because we need a JSON Object and no array.
                $l_return['not_addressed_hosts']['id-' . $l_low['isys_catg_ip_list__id']] = [
                    'catg_ip_id'      => $l_low['isys_catg_ip_list__id'],
                    'list_id'         => $l_low['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_low['isys_obj__id'],
                    'isys_obj__title' => $l_low['isys_obj__title'] . ' (' . _L($l_low['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_low['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    'assignment__id'  => $l_low['isys_catg_ip_list__isys_ip_assignment__id'],
                    'hostname'        => $l_low['isys_catg_ip_list__hostname'],
                    'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                    'domains'         => $l_dns_domains
                ];
            }
            else
            {
                $l_return['hosts'][$l_low['isys_cats_net_ip_addresses_list__title']][] = [
                    'catg_ip_id'      => $l_low['isys_catg_ip_list__id'],
                    'list_id'         => $l_low['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_low['isys_obj__id'],
                    'isys_obj__title' => $l_low['isys_obj__title'] . ' (' . _L($l_low['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_low['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    'assignment__id'  => $l_low['isys_catg_ip_list__isys_ip_assignment__id'],
                    'hostname'        => $l_low['isys_catg_ip_list__hostname'],
                    'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                    'domains'         => $l_dns_domains
                ];
            } // if
        } // while

        $l_return['result'] = 'success';

        return $l_return;
    } // function

    /**
     * Method for disconnecting an object.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function disconnect_v4()
    {
        $l_return = [];

        /**
         * Typehinting
         *
         * @var $l_ip_list_dao isys_cmdb_dao_category_s_net_ip_addresses
         * @var $l_rel_dao     isys_cmdb_dao_category_g_relation
         * @var $l_ip_dao      isys_cmdb_dao_category_g_ip
         */
        $l_ip_list_dao = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_database_component);
        $l_ip_dao      = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_database_component);
        $l_rel_dao     = isys_cmdb_dao_category_g_relation::instance($this->m_database_component);

        $l_ip_list_row = $l_ip_list_dao->get_data($_POST[C__CMDB__GET__OBJECT])
            ->get_row();

        // We don't really "disconnect" the object, we just assign it to our GLOBAL layer3-net.
        $l_ip_list_dao->save(
            $l_ip_list_row['isys_cats_net_ip_addresses_list__id'],
            $l_ip_list_row['isys_cats_net_ip_addresses_list__title'],
            C__OBJ__NET_GLOBAL_IPV4,
            $l_ip_list_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
            C__RECORD_STATUS__NORMAL
        );

        // Update relation between client and net
        $l_rel_dao->handle_relation(
            $l_ip_list_row['isys_catg_ip_list__id'],
            "isys_catg_ip_list",
            C__RELATION_TYPE__IP_ADDRESS,
            $l_ip_list_row["isys_catg_ip_list__isys_catg_relation_list__id"],
            C__OBJ__NET_GLOBAL_IPV4,
            $l_ip_list_row['isys_catg_ip_list__isys_obj__id']
        );

        $this->m_event_manager->triggerCMDBEvent(
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
            $l_ip_list_dao->get_last_query(),
            $_POST['objID2'],
            $l_ip_list_dao->get_objTypeID($_POST['objID2']),
            _L('LC__CMDB__CATS__NET_IP_ADDRESSES'),
            null,
            sprintf(
                _L('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_RELEASED'),
                $l_ip_list_row['isys_cats_net_ip_addresses_list__title'],
                $l_ip_list_dao->get_obj_name_by_id_as_string($l_ip_list_row['isys_catg_ip_list__isys_obj__id'])
            )
        );

        $l_new_dao = new isys_cmdb_dao_category_s_net($this->m_database_component);
        $l_new_res = $l_new_dao->get_assigned_hosts($_POST[C__CMDB__GET__OBJECT . '2']);

        while ($l_low = $l_new_res->get_row())
        {
            $l_dns_domains = [];
            $l_dns_res     = $l_ip_dao->get_assigned_dns_domain(null, $l_low['isys_catg_ip_list__id']);

            if (count($l_dns_res))
            {
                while ($l_dns_row = $l_dns_res->get_row())
                {
                    $l_dns_domains[] = $l_dns_row['isys_net_dns_domain__title'];
                } // while
            } // if

            if ($l_low['isys_cats_net_ip_addresses_list__title'] == '0.0.0.0' || $l_low['isys_cats_net_ip_addresses_list__title'] == '' || $l_low['isys_cats_net_ip_addresses_list__title'] == 'D.H.C.P')
            {
                // As in the DAO - We only set the ID-key because we need a JSON Object and no array.
                $l_return['not_addressed_hosts']['id-' . $l_low['isys_catg_ip_list__id']] = [
                    'catg_ip_id'      => $l_low['isys_catg_ip_list__id'],
                    'list_id'         => $l_low['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_low['isys_obj__id'],
                    'isys_obj__title' => $l_low['isys_obj__title'] . ' (' . _L($l_low['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_low['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    'assignment__id'  => $l_low['isys_catg_ip_list__isys_ip_assignment__id'],
                    'hostname'        => $l_low['isys_catg_ip_list__hostname'],
                    'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                    'domains'         => $l_dns_domains
                ];
            }
            else
            {
                $l_return['hosts'][$l_low['isys_cats_net_ip_addresses_list__title']][] = [
                    'catg_ip_id'      => $l_low['isys_catg_ip_list__id'],
                    'list_id'         => $l_low['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_low['isys_obj__id'],
                    'isys_obj__title' => $l_low['isys_obj__title'] . ' (' . _L($l_low['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_low['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    'assignment__id'  => $l_low['isys_catg_ip_list__isys_ip_assignment__id'],
                    'hostname'        => $l_low['isys_catg_ip_list__hostname'],
                    'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                    'domains'         => $l_dns_domains
                ];
            } // if
        } // while

        $l_return['result'] = 'success';

        return $l_return;
    } // function

    /**
     * Method for disconnecting an object from a IPv6 list.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function disconnect_v6()
    {
        $l_return = [];

        /**
         * Typehinting
         *
         * @var $l_ip_list_dao isys_cmdb_dao_category_s_net_ip_addresses
         * @var $l_rel_dao     isys_cmdb_dao_category_g_relation
         */
        $l_ip_list_dao = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_database_component);
        $l_rel_dao     = isys_cmdb_dao_category_g_relation::instance($this->m_database_component);
        $l_ip_list_row = $l_ip_list_dao->get_data($_POST[C__CMDB__GET__OBJECT])
            ->get_row();

        // We don't really "disconnect" the object, we just assign it to our GLOBAL layer3-net.
        $l_ip_list_dao->save(
            $l_ip_list_row['isys_cats_net_ip_addresses_list__id'],
            $l_ip_list_row['isys_cats_net_ip_addresses_list__title'],
            C__OBJ__NET_GLOBAL_IPV6,
            $l_ip_list_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
            C__RECORD_STATUS__NORMAL
        );

        // Update relation between client and net
        $l_rel_dao->handle_relation(
            $l_ip_list_row['isys_catg_ip_list__id'],
            "isys_catg_ip_list",
            C__RELATION_TYPE__IP_ADDRESS,
            $l_ip_list_row["isys_catg_ip_list__isys_catg_relation_list__id"],
            C__OBJ__NET_GLOBAL_IPV6,
            $l_ip_list_row['isys_catg_ip_list__isys_obj__id']
        );

        $this->m_event_manager->triggerCMDBEvent(
            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
            $l_ip_list_dao->get_last_query(),
            $_POST['objID2'],
            $l_ip_list_dao->get_objTypeID($_POST['objID2']),
            _L('LC__CMDB__CATS__NET_IP_ADDRESSES'),
            null,
            sprintf(
                _L('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_RELEASED'),
                $l_ip_list_row['isys_cats_net_ip_addresses_list__title'],
                $l_ip_list_dao->get_obj_name_by_id_as_string($l_ip_list_row['isys_catg_ip_list__isys_obj__id'])
            )
        );

        $l_new_dao = new isys_cmdb_dao_category_s_net($this->m_database_component);
        $l_new_res = $l_new_dao->get_assigned_hosts($_POST[C__CMDB__GET__OBJECT . '2']);

        while ($l_low = $l_new_res->get_row())
        {
            if (empty($l_low['isys_cats_net_ip_addresses_list__title']))
            {
                // The key is just used, so that we get an JSON object, and no array.
                $l_return['not_addressed_hosts']['id-' . $l_low['isys_catg_ip_list__id']] = [
                    'list_id'         => $l_low['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_low['isys_obj__id'],
                    'isys_obj__title' => $l_low['isys_obj__title'] . ' (' . _L($l_low['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_low['isys_catg_ip_list__isys_ipv6_assignment__id']
                ];
            }
            else
            {
                $l_return['hosts'][Ip::validate_ipv6($l_low['isys_cats_net_ip_addresses_list__title'], true)][] = [
                    'list_id'         => $l_low['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    'isys_obj__id'    => $l_low['isys_obj__id'],
                    'isys_obj__title' => $l_low['isys_obj__title'] . ' (' . _L($l_low['isys_obj_type__title']) . ')',
                    'isys_obj__type'  => $l_low['isys_catg_ip_list__isys_ipv6_assignment__id']
                ];
            } // if
        } // while

        $l_return['result'] = 'success';

        return $l_return;
    } // function

    /**
     * Method for pinging a single IP or a IP range.
     *
     * @param   mixed   $p_ip
     * @param   integer $p_net_obj
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function ping_ip($p_ip, $p_net_obj = null)
    {
        $l_return = [
            'success' => true,
            'data'    => [],
            'message' => null
        ];

        try
        {
            if (!is_array($p_ip))
            {
                $l_first_ip = $p_ip;
                $l_last_ip  = null;
                $l_ip_range = [$l_first_ip => false];
            }
            else
            {
                $l_first_ip = reset($p_ip);
                $l_last_ip  = end($p_ip);

                for ($i = Ip::ip2long($l_first_ip);$i <= Ip::ip2long($l_last_ip);$i++)
                {
                    $l_ip_range[Ip::long2ip($i)] = false;
                } // for
            } // if

            switch ($l_ping_method = isys_tenantsettings::get('cmdb.ip-list.ping-method', 'nmap'))
            {
                default:
                case 'nmap':
                    $l_return['data'] = Ip::nmap_ping($l_first_ip, $l_last_ip) + $l_ip_range;
                    break;
                case 'fping':
                    $l_return['data'] = Ip::fping_ping($l_first_ip, $l_last_ip) + $l_ip_range;
                    break;
            } // switch

            if ($p_net_obj !== null)
            {
                $l_cache = isys_caching::factory('catg_net_ip_addresses__' . $p_net_obj, isys_tenantsettings::get('cmdb.ip-list.cache-lifetime', isys_convert::DAY));

                // Refresh the "ping" cache.
                $l_cache->set('pings', array_merge($l_cache->get('pings', []), $l_return['data']))
                    ->save(true);
            } // if
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        return $l_return;
    } // function

    /**
     * Method for retrieving the correct IP address for the given host(s).
     *
     * @param   mixed   $p_catg_ip_id
     * @param   integer $p_net_obj
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function nslookup($p_catg_ip_id, $p_net_obj = null)
    {
        $l_return = [
            'success' => true,
            'data'    => [],
            'message' => null
        ];

        $l_dao             = isys_cmdb_dao_category_g_ip::instance($this->m_database_component);
        $l_caching_ips     = [];
        $l_net_dns_servers = [];

        if (!is_array($p_catg_ip_id))
        {
            $p_catg_ip_id = [$p_catg_ip_id];
        } // if

        $p_catg_ip_id = array_unique(array_filter($p_catg_ip_id));

        if ($p_net_obj !== null)
        {
            $l_dns_res = $l_dao->get_ip_addresses_by_ids($l_dao->get_assigned_dns_server($p_net_obj));

            while ($l_dns_row = $l_dns_res->get_row())
            {
                $l_net_dns_servers[] = $l_dns_row['isys_cats_net_ip_addresses_list__title'];
            } // while
        } // if

        foreach ($p_catg_ip_id as $l_catg_ip_id)
        {
            $l_dns_servers = $l_net_dns_servers;

            $l_iteration = [
                'success' => true,
                'data'    => null,
                'message' => null
            ];

            $l_ip_row = $l_dao->get_data($l_catg_ip_id)
                ->get_row();

            $l_hostname   = $l_ip_row['isys_catg_ip_list__hostname'];
            $l_dns_domain = $l_dao->get_assigned_dns_domain(null, $l_catg_ip_id)
                ->get_row_value('isys_net_dns_domain__title');

            if (!empty($l_dns_domain))
            {
                $l_hostname .= '.' . $l_dns_domain;
            } // if

            $l_ip_res = $l_dao->get_ip_addresses_by_ids($l_dao->get_assigned_dns_server($l_catg_ip_id));

            while ($l_ip_address_row = $l_ip_res->get_row())
            {
                $l_dns_servers[] = $l_ip_address_row['isys_cats_net_ip_addresses_list__title'];
            } // while

            try
            {
                $l_iteration['data'] = Ip::nslookup($l_hostname, $l_dns_servers);
            }
            catch (Exception $e)
            {
                $l_iteration['success'] = false;
                $l_iteration['message'] = $e->getMessage();
            } // try

            $l_caching_ips[$l_ip_row['isys_cats_net_ip_addresses_list__title']]    = $l_iteration['data'];
            $l_return['data'][$l_ip_row['isys_cats_net_ip_addresses_list__title']] = $l_iteration;
        } // foreach

        if ($p_net_obj !== null)
        {
            $l_cache = isys_caching::factory('catg_net_ip_addresses__' . $p_net_obj, isys_tenantsettings::get('cmdb.ip-list.cache-lifetime', isys_convert::DAY));

            // Refresh the "ping" cache.
            $l_cache->set('nslookup_ips', array_merge($l_cache->get('nslookup_ips', []), $l_caching_ips))
                ->save(true);
        } // if

        return $l_return;
    } // function

    /**
     * Method for retrieving the correct hostname for the given host(s).
     *
     * @param   mixed   $p_catg_ip_id
     * @param   integer $p_net_obj
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function reverse_nslookup($p_catg_ip_id, $p_net_obj = null)
    {
        $l_return = [
            'success' => true,
            'data'    => [],
            'message' => null
        ];

        /* @var  isys_cmdb_dao_category_g_ip $l_dao */
        $l_dao            = isys_cmdb_dao_category_g_ip::instance($this->m_database_component);
        $l_hostname_cache = [];

        if (!is_array($p_catg_ip_id))
        {
            $p_catg_ip_id = [$p_catg_ip_id];
        } // if

        $p_catg_ip_id = array_unique(array_filter($p_catg_ip_id));

        foreach ($p_catg_ip_id as $l_catg_ip_id)
        {
            $l_iteration = [
                'success' => true,
                'data'    => null,
                'message' => null
            ];

            if (is_numeric($l_catg_ip_id))
            {
                $l_ip_row = $l_dao->get_data($l_catg_ip_id)
                    ->get_row();
            }
            else if (Ip::validate_ip($l_catg_ip_id))
            {
                $l_ip_row['isys_cats_net_ip_addresses_list__title'] = $l_catg_ip_id;
            }
            else
            {
                continue;
            } // if

            try
            {
                $l_iteration['data'] = Ip::reverse_nslookup($l_ip_row['isys_cats_net_ip_addresses_list__title']);
            }
            catch (Exception $e)
            {
                $l_iteration['success'] = false;
                $l_iteration['message'] = $e->getMessage();
            } // try

            $l_hostname_cache[$l_ip_row['isys_cats_net_ip_addresses_list__title']] = $l_iteration['data'];

            $l_return['data'][$l_ip_row['isys_cats_net_ip_addresses_list__title']] = $l_iteration;
        } // foreach

        if ($p_net_obj !== null)
        {
            $l_cache = isys_caching::factory('catg_net_ip_addresses__' . $p_net_obj, isys_tenantsettings::get('cmdb.ip-list.cache-lifetime', isys_convert::DAY));

            // Refresh the "ping" cache.
            $l_cache->set('nslookup_hostnames', array_merge($l_cache->get('nslookup_hostnames', []), $l_hostname_cache))
                ->save(true);
        } // if

        return $l_return;
    } // function

    /**
     * Method for updating the given host address with a new IP.
     *
     * @param   integer $p_catg_ip_id
     * @param   string  $p_new_hostname
     * @param   integer $p_net_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function update_hostname($p_catg_ip_id, $p_new_hostname, $p_net_obj_id = null)
    {
        $l_return = [
            'success' => true,
            'data'    => [],
            'message' => null
        ];

        $l_dao = new isys_cmdb_dao_category_g_ip($this->m_database_component);

        try
        {
            // Save the DNS separately.
            $l_dns = substr(strstr($p_new_hostname, '.'), 1);

            if (!empty($l_dns))
            {
                $l_dao->attach_dns_domain_by_string($p_catg_ip_id, $l_dns);
            } // if

            $l_hostname = strstr($p_new_hostname, '.', true);

            $l_sql = 'UPDATE isys_catg_ip_list
				SET isys_catg_ip_list__hostname = ' . $l_dao->convert_sql_text($l_hostname) . '
				WHERE isys_catg_ip_list__id = ' . $l_dao->convert_sql_id($p_catg_ip_id) . ';';

            if (!empty($l_hostname) && $l_dao->update($l_sql) && $l_dao->apply_update())
            {
                // Reload the ip list data.
                $l_data = $this->get_all_net_information($p_net_obj_id);

                $l_return['data']['hosts'] = $l_data['data']['hosts'];
            } // if
        }
        catch (Exception $e)
        {
            $l_return = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        } // try

        return $l_return;
    } // function

    /**
     * Method for updating the given host address with a new IP.
     *
     * @param   integer $p_catg_ip_id
     * @param   string  $p_new_ip
     * @param   integer $p_net_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function update_ip_address($p_catg_ip_id, $p_new_ip, $p_net_obj_id = null)
    {
        $l_return = [
            'success' => true,
            'data'    => [],
            'message' => null
        ];

        $l_dao = new isys_cmdb_dao_category_g_ip($this->m_database_component);

        try
        {
            if ($l_dao->update_ip_address($p_catg_ip_id, $p_new_ip))
            {
                // Reload the ip list data.
                $l_data = $this->get_all_net_information($p_net_obj_id);

                $l_return['data']['hosts'] = $l_data['data']['hosts'];
            } // if
        }
        catch (Exception $e)
        {
            $l_return = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        } // try

        return $l_return;
    } // function

    /**
     * @param null $p_net_obj_id
     *
     * @return array
     */
    private function get_all_net_information($p_net_obj_id = null)
    {
        $l_net_obj_id = $p_net_obj_id ?: $_POST['net_object'];

        $l_return          = [];
        $l_dao_net         = new isys_cmdb_dao_category_s_net($this->m_database_component);
        $l_hostaddress_dao = new isys_cmdb_dao_category_g_ip($this->m_database_component);
        $l_net_row         = $l_dao_net->get_all_net_information_by_obj_id($l_net_obj_id);

        $l_address_default_gateway = 0;
        $l_address_ranges          = [];

        // We also select all DHCP address-ranges, so that we can display them.
        $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($this->m_database_component);
        $l_dhcp_res = $l_dhcp_dao->get_data(null, $l_net_obj_id, '', null, C__RECORD_STATUS__NORMAL);

        while ($l_dhcp_row = $l_dhcp_res->get_row())
        {
            $l_address_ranges[] = [
                'from' => $l_dhcp_row['isys_cats_net_dhcp_list__range_from'],
                'to'   => $l_dhcp_row['isys_cats_net_dhcp_list__range_to'],
                'type' => $l_dhcp_row['isys_cats_net_dhcp_list__isys_net_dhcp_type__id']
            ];
        } // while

        $l_dhcp_ranges = $l_address_ranges;

        if (!empty($l_net_row['isys_cats_net_list__isys_catg_ip_list__id']))
        {
            $l_address_default_gateway = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
                ->get_data($l_net_row['isys_cats_net_list__isys_catg_ip_list__id'])
                ->get_row_value('isys_cats_net_ip_addresses_list__title');
        }

        if ($l_net_row['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV4)
        {
            // ipv4
            $l_net_range = [
                'from'         => $l_net_row["isys_cats_net_list__address_range_from_long"],
                'from_address' => $l_net_row["isys_cats_net_list__address_range_from"],
                'to'           => $l_net_row["isys_cats_net_list__address_range_to_long"],
                'to_address'   => $l_net_row["isys_cats_net_list__address_range_to"],
            ];

            // Get the assigned hosts of our net.
            $l_hosts               = [];
            $l_non_addressed_hosts = [];
            $l_hosts_res           = $l_dao_net->get_assigned_hosts(
                $l_net_row['isys_obj__id'],
                '',
                C__RECORD_STATUS__NORMAL,
                'ORDER BY isys_cats_net_ip_addresses_list__ip_address_long ASC;'
            );
            $l_address_conflict    = false;

            while ($l_hosts_row = $l_hosts_res->get_row())
            {
                $l_dns_domains = [];
                $l_dns_res     = $l_hostaddress_dao->get_assigned_dns_domain(null, $l_hosts_row['isys_catg_ip_list__id']);

                if (count($l_dns_res))
                {
                    while ($l_dns_row = $l_dns_res->get_row())
                    {
                        $l_dns_domains[] = $l_dns_row['isys_net_dns_domain__title'];
                    } // while
                } // if

                if (in_array(
                    $l_hosts_row['isys_cats_net_ip_addresses_list__title'],
                    [
                        '',
                        '0.0.0.0',
                        'D.H.C.P'
                    ]
                ))
                {
                    // The key is just used, so that we get an JSON object, and no array.
                    $l_non_addressed_hosts['id-' . $l_hosts_row['isys_catg_ip_list__id']] = [
                        'catg_ip_id'      => $l_hosts_row['isys_catg_ip_list__id'],
                        'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                        'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                        'isys_obj__title' => isys_glob_utf8_encode($l_hosts_row['isys_obj__title'] . ' (' . _L($l_hosts_row['isys_obj_type__title']) . ')'),
                        'isys_obj__type'  => isys_glob_utf8_encode($l_hosts_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id']),
                        'assignment__id'  => $l_hosts_row['isys_catg_ip_list__isys_ip_assignment__id'],
                        'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                        'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                        'domains'         => $l_dns_domains
                    ];
                }
                else
                {
                    $l_hosts[$l_hosts_row['isys_cats_net_ip_addresses_list__title']][] = [
                        'catg_ip_id'      => $l_hosts_row['isys_catg_ip_list__id'],
                        'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                        'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                        'isys_obj__title' => isys_glob_utf8_encode($l_hosts_row['isys_obj__title'] . ' (' . _L($l_hosts_row['isys_obj_type__title']) . ')'),
                        'isys_obj__type'  => isys_glob_utf8_encode($l_hosts_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id']),
                        'assignment__id'  => $l_hosts_row['isys_catg_ip_list__isys_ip_assignment__id'],
                        'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                        'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                        'domains'         => $l_dns_domains
                    ];

                    // Display a message, that there are IP-address conflicts
                    if (count($l_hosts[$l_hosts_row['isys_cats_net_ip_addresses_list__title']]) > 1)
                    {
                        $l_address_conflict = true;
                    } // if
                } // if
            } // while

            // When the array is empty, we can't give an empty JSON array to the template because that will break the $H() object.
            if (count($l_hosts) <= 0)
            {
                // This will do the trick!
                $l_hosts = [];
            } // if

            // Same thing as above!
            if (count($l_non_addressed_hosts) <= 0)
            {
                // This will do the trick!
                $l_non_addressed_hosts = [];
            } // if
        }
        else
        {
            // ipv6

            $l_net_range = [
                'from_address' => $l_net_row["isys_cats_net_list__address_range_from"],
                'to_address'   => $l_net_row["isys_cats_net_list__address_range_to"]
            ];

            // Get the assigned hosts of our net.
            $l_hosts               = [];
            $l_non_addressed_hosts = [];
            $l_hosts_res           = $l_dao_net->get_assigned_hosts($l_net_row['isys_obj__id']);
            $l_address_conflict    = false;

            if (is_object($l_hosts_res))
            {
                while ($l_hosts_row = $l_hosts_res->get_row())
                {
                    $l_dns_domains = [];
                    $l_dns_res     = $l_hostaddress_dao->get_assigned_dns_domain(null, $l_hosts_row['isys_catg_ip_list__id']);

                    if (count($l_dns_res))
                    {
                        while ($l_dns_row = $l_dns_res->get_row())
                        {
                            $l_dns_domains[] = $l_dns_row['isys_net_dns_domain__title'];
                        } // while
                    } // if

                    // Maybe we should check for more than just "empty".
                    if (empty($l_hosts_row['isys_cats_net_ip_addresses_list__title']))
                    {
                        // The key is just used, so that we get an JSON object, and no array.
                        $l_non_addressed_hosts['id-' . $l_hosts_row['isys_catg_ip_list__id']] = [
                            'catg_ip_id'      => $l_hosts_row['isys_catg_ip_list__id'],
                            'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                            'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                            'isys_obj__title' => isys_glob_utf8_encode($l_hosts_row['isys_obj__title'] . ' (' . _L($l_hosts_row['isys_obj_type__title']) . ')'),
                            'isys_obj__type'  => $l_hosts_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                            'assignment__id'  => isys_glob_utf8_encode($l_hosts_row['isys_catg_ip_list__isys_ipv6_assignment__id']),
                            'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                            'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                            'domains'         => $l_dns_domains
                        ];
                    }
                    else
                    {
                        $l_hosts[Ip::validate_ipv6($l_hosts_row['isys_cats_net_ip_addresses_list__title'], true)][] = [
                            'catg_ip_id'      => $l_hosts_row['isys_catg_ip_list__id'],
                            'list_id'         => $l_hosts_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                            'isys_obj__id'    => $l_hosts_row['isys_obj__id'],
                            'isys_obj__title' => isys_glob_utf8_encode($l_hosts_row['isys_obj__title'] . ' (' . _L($l_hosts_row['isys_obj_type__title']) . ')'),
                            'isys_obj__type'  => $l_hosts_row['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                            'assignment__id'  => isys_glob_utf8_encode($l_hosts_row['isys_catg_ip_list__isys_ipv6_assignment__id']),
                            'hostname'        => $l_hosts_row['isys_catg_ip_list__hostname'],
                            'domain'          => (isset($l_dns_domains[0]) ? $l_dns_domains[0] : false),
                            'domains'         => $l_dns_domains
                        ];

                        // Display a message, that there are IP-address conflicts
                        if (count($l_hosts[Ip::validate_ipv6($l_hosts_row['isys_cats_net_ip_addresses_list__title'], true)]) > 1)
                        {
                            $l_address_conflict = true;
                        } // if
                    } // if
                } // while
            } // if

            // When the array is empty, we can't give an empty JSON array to the template because that will break the $H() object.
            if (count($l_hosts) <= 0)
            {
                // This will do the trick!
                $l_hosts = [];
            } // if

            // Same thing as above!
            if (count($l_non_addressed_hosts) <= 0)
            {
                // This will do the trick!
                $l_non_addressed_hosts = [];
            } // if
        } // if

        $l_return['data'] = [
            'address_default_gateway' => $l_address_default_gateway,
            'dhcp_ranges'             => $l_dhcp_ranges,
            'net_address'             => $l_net_row['isys_cats_net_list__address'],
            'net_subnet_mask'         => $l_net_row['isys_cats_net_list__mask'],
            'net_cidr_suffix'         => $l_net_row['isys_cats_net_list__cidr_suffix'],
            'hosts'                   => $l_hosts,
            'non_addressed_hosts'     => $l_non_addressed_hosts,
            'address_conflict'        => $l_address_conflict,
            'address_range_from'      => $l_net_range['from_address'],
            'address_range_to'        => $l_net_range['to_address']
        ];

        return $l_return;
    } // function
} // class