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
 * DAO: Port category list 'IP'
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_ip extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__IP;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Order condition
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'isys_cats_net_ip_addresses_list__title')
        {
            $p_column = 'isys_cats_net_ip_addresses_list__ip_address_long';
        } // if

        return $p_column . " " . $p_direction;
    } // function

    /**
     * Modifies content of each line.
     *
     * @param   array &$p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        // Assigned net.
        if ($p_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'] > 0)
        {
            /** @var isys_cmdb_dao_category_s_net_ip_addresses $l_cats_net_ip_addresses_dao */
            $l_cats_net_ip_addresses_dao = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->get_database_component());

            $l_row = $l_cats_net_ip_addresses_dao->get_data($p_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'])
                ->get_row();

            if ($l_row['isys_cats_net_ip_addresses_list__isys_obj__id'] > 0)
            {
                $l_row2 = $l_cats_net_ip_addresses_dao->get_data_by_object($l_row['isys_cats_net_ip_addresses_list__isys_obj__id'])
                    ->get_row();

                $p_row['isys_catg_ip_list__assigned_net'] = '<a href="' . isys_helper_link::create_url(
                        [C__CMDB__GET__OBJECT => $l_row['isys_cats_net_ip_addresses_list__isys_obj__id']]
                    ) . '" >' . $l_row2['isys_obj__title'] . '</a>';
            } // if
        } // if

        $p_row['isys_catg_ip_list__hostname'] = $p_row['isys_catg_ip_list__hostname'] ?: $l_empty_value;

        // "Yes" / "No" for the primary field.
        $p_row['isys_catg_ip_list__primary'] = ($p_row['isys_catg_ip_list__primary'] == 0) ? '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" class="vam mr5" /><span class="vam red">' . _L(
                'LC__UNIVERSAL__NO'
            ) . '</span>' : '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_green.png" class="vam mr5" /><span class="vam green">' . _L(
                'LC__UNIVERSAL__YES'
            ) . '</span>';

        // If we display an IPv6 address, we shorten the output.
        if ($p_row['isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
        {
            $p_row['ip_assignment']                          = _L($p_row['isys_ipv6_assignment__title']);
            $p_row['isys_cats_net_ip_addresses_list__title'] = Ip::validate_ipv6($p_row['isys_cats_net_ip_addresses_list__title'], true);
            if (empty($p_row['isys_cats_net_ip_addresses_list__title']))
            {
                $p_row['isys_cats_net_ip_addresses_list__title'] = $l_empty_value;
            } // if
        }
        else
        {
            $p_row['ip_assignment'] = _L($p_row['isys_ip_assignment__title']);
            if (empty($p_row['isys_cats_net_ip_addresses_list__title']))
            {
                $p_row['isys_cats_net_ip_addresses_list__title'] = $l_empty_value;
            } // if
        } // if

        /** @var  isys_cmdb_dao_category_g_ip $l_dao_ip */
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->get_database_component());

        // Retrieve domains.
        $l_res_domains = $l_dao_ip->get_assigned_dns_domain(null, $p_row['isys_catg_ip_list__id']);

        if (count($l_res_domains))
        {
            $l_domain_titles = [];

            while ($l_row_domain = $l_res_domains->get_row())
            {
                // Add title
                $l_domain_titles[] = $l_row_domain['isys_net_dns_domain__title'];
            } // while

            // Build list of dns domains
            $p_row['dns_domains'] = $l_domain_titles;
        }
        else
        {
            $p_row['dns_domains'] = $l_empty_value;
        } // if
    } // function

    /**
     * Sets header of the list.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_catg_ip_list__id'                            => 'ID',
            'isys_cats_net_ip_addresses_list__title'           => 'LC__CMDB__CATG__NETWORK__ADDRESS',
            'ip_assignment'                                    => 'LC__CATP__IP__ASSIGN',
            // This is no real field inside the database.
            'isys_catg_ip_list__hostname'                      => 'LC__CMDB__CATG__NETWORK__HOSTNAME',
            'isys_catg_ip_list__assigned_net'                  => 'LC__CATG__IP__ASSIGNED_NET',
            // This is no real field inside the database.
            'isys_catg_ip_list__primary'                       => 'LC__CMDB__CATG__NETWORK__PRIM_IP_BOOL',
            'isys_cats_net_ip_addresses_list__ip_address_long' => false,
            'dns_domains'                                      => 'LC__CATP__IP__DNSDOMAIN',
            // This is no real field inside the database.
        ];
    } // function
} // class