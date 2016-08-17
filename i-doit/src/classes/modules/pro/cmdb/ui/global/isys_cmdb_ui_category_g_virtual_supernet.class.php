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
 * CMDB Global category for objecttype supernet.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_cmdb_ui_category_g_virtual_supernet extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_virtual_supernet $p_cat
     *
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT);

        // The contact DAO is needed for retrieving the primary contact of each net.
        $l_contact_dao = isys_cmdb_dao_category_g_contact::instance($this->get_database_component());

        $l_quickinfo = new isys_ajax_handler_quick_info();

        $l_subnets = [];

        $l_net_data = isys_cmdb_dao_category_s_net::instance($p_cat->get_database_component())
            ->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->get_row();

        $l_subnets_calculated = $p_cat->get_subnets(
            $_GET[C__CMDB__GET__OBJECT],
            $l_net_data['isys_cats_net_list__address_range_from_long'],
            $l_net_data['isys_cats_net_list__address_range_to_long']
        );

        if ($l_net_data['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV4)
        {
            Ip::virtual_supernet_range_instance($l_net_data['isys_cats_net_list__address_range_from_long'], $l_net_data['isys_cats_net_list__address_range_to_long']);
        } // if

        if (count($l_subnets_calculated) > 0)
        {
            while ($l_row = $l_subnets_calculated->get_row())
            {
                $l_primary_contact        = $l_contact_dao->get_assigned_contacts($l_row['isys_cats_net_list__isys_obj__id'], null, true)
                    ->get_row();
                $l_row['primary_contact'] = false;

                if ($l_primary_contact)
                {
                    $l_row['primary_contact'] = $l_quickinfo->get_quick_info(
                        $l_primary_contact['isys_obj__id'],
                        $l_primary_contact['isys_obj__title'],
                        C__LINK__OBJECT
                    );
                } // if

                $l_row['title'] = $l_quickinfo->get_quick_info(
                    $l_row['isys_obj__id'],
                    $l_row['isys_obj__title'] . ' &raquo; ' . $l_row['isys_cats_net_list__address'] . ' / ' . $l_row['isys_cats_net_list__cidr_suffix'],
                    isys_helper_link::create_url(
                        [
                            C__CMDB__GET__OBJECT => $l_row['isys_obj__id'],
                            C__CMDB__GET__CATS   => C__CATS__NET_IP_ADDRESSES
                        ]
                    )
                );

                $l_subnets[] = $l_row;

                // IPv6 does not work yet.
                if ($l_row['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV4)
                {
                    $l_range = Ip::calc_ip_range($l_row['isys_cats_net_list__address'], $l_row['isys_cats_net_list__mask'], true);
                    Ip::add_subnet($l_range['from'], $l_range['to']);
                } // if
            } // while
        } // if

        $this->deactivate_commentary()// Assign all the data to the template.
        ->get_template_component()
            ->assign('subnets', $l_subnets)
            ->assign('net', $l_net_data)
            ->assign('free_ranges', isys_format_json::encode(Ip::get_free_ranges_in_virtual_supernet()));
    } // function
} // class
?>