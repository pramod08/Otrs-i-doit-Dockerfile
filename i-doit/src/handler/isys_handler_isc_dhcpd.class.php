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
 * This handler exports hosts with static dhcp (dhcp reserved) ips in isc-dhcpd style
 *
 * @package        i-doit
 * @subpackage     Handler
 * @author         Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright      synetics GmbH
 * @version        1.1
 * @license        http://www.i-doit.com/license
 */
class isys_handler_isc_dhcpd extends isys_handler
{

    /**
     * @var isys_cmdb_dao
     */
    private $m_dao = null;
    /**
     * Current isc-dhcpd types
     *
     * @var array
     */
    private $m_types = [
        'host-block'
    ];

    /**
     * Init method
     *
     * @return bool
     */
    public function init()
    {
        verbose("Setting up system environment");

        try
        {
            $this->export_isc_dhcpd();
        }
        catch (Exception $e)
        {
            verbose($e->getMessage());
        } // try

        return true;
    } // function

    /**
     * Exports isc-dhcpd info
     */
    private function export_isc_dhcpd()
    {
        global $argv, $g_comp_database;;

        // Net object id
        $l_net_obj_id = null;

        // Net address
        $l_net_ip = null;

        // default type
        $l_type = 'host-block';

        $l_output = '';

        // Help
        if (in_array('-h', $argv))
        {
            $this->usage();
        } // if

        // Layer3 Net ip
        if (in_array('-net', $argv))
        {
            $l_net_obj_id = $argv[array_search('-net', $argv) + 1];
        } // if

        // Layer3 Net address
        if (in_array('-netaddr', $argv))
        {
            $l_net_ip = $argv[array_search('-netaddr', $argv) + 1];
        } // if

        // Type
        if (in_array('-type', $argv))
        {
            $l_type = $argv[array_search('-type', $argv) + 1];
        } // if

        // output usage if type is unknown
        if (!in_array($l_type, $this->m_types))
        {
            verbose("Type unknown please use the default type 'host-block'.\n");
            $this->usage();
        } // if

        // dao
        $this->m_dao = new isys_cmdb_dao($g_comp_database);

        if (!is_null($l_net_ip))
        {
            $l_net_obj_id = $this->get_net_obj_id_by_net_address($l_net_ip);
        } // if

        switch ($l_type)
        {
            case 'host-block':
                $l_output = $this->host_block($l_net_obj_id);
                break;

        } // switch

        echo $l_output;
    } // function

    /**
     * Retrieves object id of the given net address
     *
     * @param $p_net_ip
     *
     * @return null|int
     */
    private function get_net_obj_id_by_net_address($p_net_ip)
    {
        $l_sql = 'SELECT isys_cats_net_list__isys_obj__id FROM isys_cats_net_list ' . 'WHERE isys_cats_net_list__address = ' . $this->m_dao->convert_sql_text($p_net_ip);

        $l_res = $this->m_dao->retrieve($l_sql);
        if ($l_res->num_rows() > 0)
        {
            $l_ip_arr = $l_res->get_row();

            return $l_ip_arr['isys_cats_net_list__isys_obj__id'];
        } // if
        return null;
    } // function

    /**
     * Retrieves isc-dhcpd host blocks
     *
     * @param null $p_net_obj_id
     *
     * @return string
     */
    private function host_block($p_net_obj_id = null)
    {
        // ipv4 assignment type
        $l_res                = $this->m_dao->retrieve(
            'SELECT isys_ip_assignment__id FROM isys_ip_assignment WHERE isys_ip_assignment__const = ' . $this->m_dao->convert_sql_text('C__CATP__IP__ASSIGN__DHCP_RESERVED')
        );
        $l_ipv4_assignment    = $l_res->get_row();
        $l_ipv4_assignment_id = $l_ipv4_assignment['isys_ip_assignment__id'];

        // ipv6 assignment type
        $l_res                = $this->m_dao->retrieve(
            'SELECT isys_ipv6_assignment__id FROM isys_ipv6_assignment WHERE isys_ipv6_assignment__const = ' . $this->m_dao->convert_sql_text(
                'C__CMDB__CATG__IP__DHCPV6_RESERVED'
            )
        );
        $l_ipv6_assignment    = $l_res->get_row();
        $l_ipv6_assignment_id = $l_ipv6_assignment['isys_ipv6_assignment__id'];

        $l_sql = "SELECT isys_catg_ip_list__hostname AS hostname, isys_cats_net_ip_addresses_list__title AS ip, isys_catg_port_list__mac AS mac FROM isys_catg_port_list " . "INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_catg_port_list__id = isys_catg_port_list__id " . "INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " . "WHERE " . "(isys_catg_ip_list__isys_ip_assignment__id = " . $this->m_dao->convert_sql_id(
                $l_ipv4_assignment_id
            ) . " OR isys_catg_ip_list__isys_ipv6_assignment__id = " . $this->m_dao->convert_sql_id(
                $l_ipv6_assignment_id
            ) . ") " . "AND isys_cats_net_ip_addresses_list__title IS NOT NULL AND isys_catg_ip_list__hostname != '' ";
        //"AND isys_catg_ip_list__primary = 1 ".

        if (!is_null($p_net_obj_id))
        {
            $l_sql .= "AND isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->m_dao->convert_sql_id($p_net_obj_id);
        } // if

        $l_sql .= ';';

        $l_res    = $this->m_dao->retrieve($l_sql);
        $l_output = "\n";
        while ($l_row = $l_res->get_row())
        {
            $l_output .= "host " . $l_row['hostname'] . " {\n";

            // hardware ethernet
            if ($l_row['mac'] != '')
            {
                $l_output .= "\thardware ethernet " . $l_row['mac'] . ";\n";
            } // if

            if ($l_row['ip'] != '')
            {
                $l_output .= "\tfixed-address " . $l_row['ip'] . ";\n";
            } // if

            $l_output .= "\toption host-name " . $l_row['hostname'] . ";\n";
            $l_output .= "}\n";
        }

        return $l_output;
    } // function

    /**
     * Prints how to use the controller
     */
    private function usage()
    {
        error(
            "Usage: ./controller -m iscdhcpd \n\n" . "Optional Parameter: \n" . "-net [layer3-Object-ID]\n" . "-netaddr [layer3-net-address]\n" . "-type [type]\n\n" . "Current types are:\n" . implode(
                ',',
                $this->m_types
            ) . "\n\n" . "Example: \n" . "./controller -m isc_dhcpd -net 939 -type host-block\n" . "./controller -m isc_dhcpd -netaddr 192.168.10.0 -type host-block\n"
        );
        die;
    } // function
} // class