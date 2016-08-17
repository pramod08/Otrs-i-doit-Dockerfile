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
 * Helper methods for IP-handling.
 *
 * @deprecated
 * @see         idoit\Component\Helper\Ip
 * @package     i-doit
 * @subpackage  Helper
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_helper_ip
{
    /**
     * Calculates cidr suffix from subnetmask.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     * @static
     *
     * @param   string $p_subnet_mask
     *
     * @return  integer
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_cidr_suffix($p_subnet_mask)
    {
        return idoit\Component\Helper\Ip::calc_cidr_suffix($p_subnet_mask);
    } // function

    /**
     * Calculates cidr suffix for ipv6 from the specified subnetmask.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     * @static
     *
     * @param   string $p_net_mask
     *
     * @return  integer
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_cidr_suffix_ipv6($p_net_mask)
    {
        return idoit\Component\Helper\Ip::calc_cidr_suffix_ipv6($p_net_mask);
    } // function

    /**
     * Calculates ip range.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string  $p_net_address
     * @param   string  $p_subnet_mask
     * @param   boolean $p_fullrange True: Returns the "full" IP-range, False: Returns the "available" IP-range (without net-address and broadcast).
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function calc_ip_range($p_net_address, $p_subnet_mask, $p_fullrange = false)
    {
        return idoit\Component\Helper\Ip::calc_ip_range($p_net_address, $p_subnet_mask, $p_fullrange);
    } // function

    /**
     * Calculates IPv6 Range with the specified net address and cidr suffix.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string  $p_net_address
     * @param   integer $p_cidr_suffix
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function calc_ip_range_ipv6($p_net_address, $p_cidr_suffix)
    {
        return idoit\Component\Helper\Ip::calc_ip_range_ipv6($p_net_address, $p_cidr_suffix);
    } // function

    /**
     * Method for calculating the given IPv6 address +1.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_address
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calculate_next_ipv6($p_address)
    {
        return idoit\Component\Helper\Ip::calculate_next_ipv6($p_address);
    } // function

    /**
     * Method for calculating the given IPv6 address -1.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_address
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calculate_prev_ipv6($p_address)
    {
        return idoit\Component\Helper\Ip::calculate_prev_ipv6($p_address);
    } // function

    /**
     * Calculates subnetmask by cidr suffix.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   integer $p_cidr_suffix
     *
     * @return  string
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_subnet_by_cidr_suffix($p_cidr_suffix)
    {
        return idoit\Component\Helper\Ip::calc_subnet_by_cidr_suffix($p_cidr_suffix);
    } // function

    /**
     * Calculate subnetmask for ipv6 from the specified cidr suffix.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   integer $p_cidr_suffix
     * @param   boolean $p_full_mask
     *
     * @return  string
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_subnet_by_cidr_suffix_ipv6($p_cidr_suffix, $p_full_mask = false)
    {
        return idoit\Component\Helper\Ip::calc_subnet_by_cidr_suffix_ipv6($p_cidr_suffix, $p_full_mask);
    } // function

    /**
     * Converts ip address to numeric value.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_ip_address
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function ip2long($p_ip_address)
    {
        return idoit\Component\Helper\Ip::ip2long($p_ip_address);
    } // function

    /**
     * Checks if an ip address in between the specified range.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   mixed $p_ip
     * @param   mixed $p_range_from
     * @param   mixed $p_range_to
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function is_ip_in_range($p_ip, $p_range_from, $p_range_to)
    {
        return idoit\Component\Helper\Ip::is_ip_in_range($p_ip, $p_range_from, $p_range_to);
    } // function

    /**
     * Checks if a ipv6 Address is inside the specified ipv6 range.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_address
     * @param   string $p_from
     * @param   string $p_to
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_ipv6_in_range($p_address, $p_from, $p_to)
    {
        return idoit\Component\Helper\Ip::is_ipv6_in_range($p_address, $p_from, $p_to);
    } // function

    /**
     * Converts numeric value to ip address.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     * @static
     *
     * @param   integer $p_iplong
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function long2ip($p_iplong)
    {
        return idoit\Component\Helper\Ip::long2ip($p_iplong);
    } // function

    /**
     * Sets an outer ranged ip inside the specified ip range.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_ip
     * @param   string $p_range_from
     * @param   string $p_range_to
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function set_ip_in_range($p_ip, $p_range_from, $p_range_to)
    {
        return idoit\Component\Helper\Ip::set_ip_in_range($p_ip, $p_range_from, $p_range_to);
    } // function

    /**
     * Checks if the specified ip is valid.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_ip
     *
     * @return  boolean
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function validate_ip($p_ip)
    {
        return idoit\Component\Helper\Ip::validate_ip($p_ip);
    } // function

    /**
     * Validates ipv6 address.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string  $p_address
     * @param   boolean $p_short_view
     *
     * @return  string
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function validate_ipv6($p_address, $p_short_view = false)
    {
        return idoit\Component\Helper\Ip::validate_ipv6($p_address, $p_short_view);
    } // function

    /**
     * Validates net ip.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string  $p_net_ip
     * @param   string  $p_subnetmask
     * @param   integer $p_cidr_suffix
     * @param   boolean $p_next_net_ip
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_net_ip($p_net_ip, $p_subnetmask = null, $p_cidr_suffix = null, $p_next_net_ip = false)
    {
        return idoit\Component\Helper\Ip::validate_net_ip($p_net_ip, $p_subnetmask, $p_cidr_suffix, $p_next_net_ip);
    } // function

    /**
     * Validates ipv6 net address and returns correct ipv6 net address.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string  $p_address
     * @param   integer $p_cidr_suffix
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_net_ipv6($p_address, $p_cidr_suffix)
    {
        return idoit\Component\Helper\Ip::validate_net_ipv6($p_address, $p_cidr_suffix);
    } // function

    /**
     * Validates subnetmask and returns the next appropiate mask.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string  $p_net_mask
     * @param   boolean $p_get_next_mask
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_subnetmask($p_net_mask, $p_get_next_mask = false)
    {
        return idoit\Component\Helper\Ip::validate_subnetmask($p_net_mask, $p_get_next_mask);
    } // function

    /**
     * Validates subnetmask of ipv6 ip and returns correct ip.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     * @static
     *
     * @param   string $p_ipv6_address
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_subnetmask_ipv6($p_ipv6_address)
    {
        return idoit\Component\Helper\Ip::validate_subnetmask_ipv6($p_ipv6_address);
    } // function

    /**
     * Instance method for resetting the virtual supernet range calculator.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   integer $p_from
     * @param   integer $p_to
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function virtual_supernet_range_instance($p_from, $p_to)
    {
        return idoit\Component\Helper\Ip::virtual_supernet_range_instance($p_from, $p_to);
    } // function

    /**
     * Method for adding a new subnet to the virtual supernet range calculator.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_from
     * @param   string $p_to
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function add_subnet($p_from, $p_to)
    {
        idoit\Component\Helper\Ip::add_subnet($p_from, $p_to);
    } // function

    /**
     * Retrieves an array of available ranges, between the given nets.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_free_ranges_in_virtual_supernet()
    {
        return idoit\Component\Helper\Ip::get_free_ranges_in_virtual_supernet();
    } // function

    /**
     * Method for doing a nslookup via console. It will return a ip address or false.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_hostname
     * @param   array  $p_dns_server
     *
     * @return  mixed
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function nslookup($p_hostname, array $p_dns_server = [])
    {
        return idoit\Component\Helper\Ip::nslookup($p_hostname, $p_dns_server);
    } // function

    /**
     * Method for doing a reverse nslookup via console. It will return a hostname or false.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_ip
     *
     * @return  mixed
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function reverse_nslookup($p_ip)
    {
        return idoit\Component\Helper\Ip::reverse_nslookup($p_ip);
    } // function

    /**
     * Method for pinging multiple hosts via nmap.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_ip_from
     * @param   string $p_ip_to
     *
     * @return  array
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function nmap_ping($p_ip_from, $p_ip_to = null)
    {
        return idoit\Component\Helper\Ip::nmap_ping($p_ip_from, $p_ip_to);
    } // function

    /**
     * Method for pinging multiple hosts via fping.
     *
     * @deprecated
     * @see     idoit\Component\Helper\Ip
     *
     * @param   string $p_ip_from
     * @param   string $p_ip_to
     *
     * @return  array
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function fping_ping($p_ip_from, $p_ip_to = null)
    {
        return idoit\Component\Helper\Ip::fping_ping($p_ip_from, $p_ip_to);
    } // function
} // class
