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
 * CMDB UI: Global rearward category for VRRP.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_vrrp_view extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_g_vrrp_view $p_cat
     */
    public function process(isys_cmdb_dao_category_g_vrrp_view $p_cat)
    {
        global $g_dirs;

        parent::process($p_cat);

        $l_rows      = [];
        $l_dao       = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component());
        $l_ip_dao    = isys_cmdb_dao_category_g_ip::instance($this->get_database_component());
        $l_vrrp_dao  = isys_cmdb_dao_category_g_vrrp::instance($this->get_database_component());
        $l_quickinfo = new isys_ajax_handler_quick_info;
        $l_empty     = isys_tenantsettings::get('gui.empty_value', '-');

        $l_res = $l_dao->get_data(null, $p_cat->get_object_id());

        while ($l_row = $l_res->get_row())
        {
            $l_vrrp_res = $p_cat->get_vrrp_by_log_port($l_row['isys_catg_log_port_list__id']);

            while ($l_vrrp_row = $l_vrrp_res->get_row())
            {
                $l_parent = false;

                if (!isset($l_rows[$l_vrrp_row['isys_catg_vrrp_member_list__isys_obj__id']]))
                {
                    $l_vrrp = $l_vrrp_dao->get_data(null, $l_vrrp_row['isys_catg_vrrp_member_list__isys_obj__id'])
                        ->get_row();
                    $l_ip   = $l_ip_dao->get_primary_ip($l_vrrp_row['isys_catg_vrrp_member_list__isys_obj__id'])
                        ->get_row();
                    $l_net  = $l_empty;

                    if (!empty($l_ip) && $l_ip['isys_cats_net_list__isys_obj__id'] > 0)
                    {
                        $l_net = $l_quickinfo->get_quick_info(
                            $l_ip['isys_cats_net_list__isys_obj__id'],
                            '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam mr5" />' . $l_dao->get_obj_name_by_id_as_string(
                                $l_ip['isys_cats_net_list__isys_obj__id']
                            ),
                            C__LINK__CATS,
                            false,
                            [C__CMDB__GET__CATS => C__CATS__NET]
                        );
                    } // if

                    $l_rows[$l_vrrp_row['isys_catg_vrrp_member_list__isys_obj__id']] = [
                        'info'  => [
                            'obj_id'         => $l_vrrp_row['isys_obj__id'],
                            'obj_title'      => $l_vrrp_row['isys_obj__title'],
                            'obj_type_title' => _L($l_vrrp_row['isys_obj_type__title']),
                            // VRRP data.
                            'vrrp_type'      => _L($l_vrrp['isys_vrrp_type__title']) ?: $l_empty,
                            'vrrp_vr_id'     => $l_vrrp['isys_catg_vrrp_list__vr_id'] ?: $l_empty,
                            // Hostadress data.
                            'layer3_net'     => $l_net,
                            'ip_address'     => $l_ip['isys_cats_net_ip_addresses_list__title'] ?: $l_empty,
                            'url'            => $l_quickinfo->get_quick_info(
                                $l_vrrp_row['isys_obj__id'],
                                '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam mr5" />' . _L(
                                    $l_vrrp_row['isys_obj_type__title']
                                ) . ' &raquo; ' . $l_vrrp_row['isys_obj__title'],
                                C__LINK__CATG,
                                false,
                                [C__CMDB__GET__CATG => C__CATG__VRRP]
                            )
                        ],
                        'ports' => []
                    ];
                } // if

                if ($l_row['isys_catg_log_port_list__parent'] > 0)
                {
                    $l_parent_port = $l_dao->get_data($l_row['isys_catg_log_port_list__parent'])
                        ->get_row();

                    $l_parent = [
                        'id'    => $l_parent_port['isys_catg_log_port_list__id'],
                        'title' => $l_parent_port['isys_catg_log_port_list__title'],
                        'mac'   => $l_row['isys_catg_log_port_list__mac'],
                        'url'   => isys_helper_link::create_url(
                            [
                                C__CMDB__GET__OBJECT   => $l_parent_port['isys_catg_log_port_list__isys_obj__id'],
                                C__CMDB__GET__CATG     => C__CMDB__SUBCAT__NETWORK_INTERFACE_L,
                                C__CMDB__GET__CATLEVEL => $l_parent_port['isys_catg_log_port_list__id']
                            ]
                        )
                    ];
                } // if

                $l_rows[$l_vrrp_row['isys_catg_vrrp_member_list__isys_obj__id']]['ports'][] = [
                    'id'     => $l_row['isys_catg_log_port_list__id'],
                    'title'  => $l_row['isys_catg_log_port_list__title'],
                    'mac'    => $l_row['isys_catg_log_port_list__mac'],
                    'parent' => $l_parent,
                    'url'    => isys_helper_link::create_url(
                        [
                            C__CMDB__GET__OBJECT   => $l_row['isys_catg_log_port_list__isys_obj__id'],
                            C__CMDB__GET__CATG     => C__CMDB__SUBCAT__NETWORK_INTERFACE_L,
                            C__CMDB__GET__CATLEVEL => $l_row['isys_catg_log_port_list__id']
                        ]
                    )
                ];
            } // while
        } // while

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        $this->get_template_component()
            ->assign('rows', array_values($l_rows));
    } //function
} // class