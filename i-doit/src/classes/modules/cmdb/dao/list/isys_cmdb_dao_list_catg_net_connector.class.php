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
 * DAO: specific category list for network connector
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_net_connector extends isys_cmdb_dao_list
{

    /**
     * Gets fields to display in the list view.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     * @return  array
     */
    public function get_fields()
    {
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            @$l_properties['ip_address'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title' => $l_properties['ip_address'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            'port_range'                                                                                  => _L('Port') . ' / ' . _L('LC__UNIVERSAL__PORT_RANGE'),
            'connected_to_listener'                                                                       => 'LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO',
            'protocol'                                                                                    => 'LC__CMDB__CATG__NET_LISTENER__PROTOCOL',
            'opened_by'                                                                                   => 'LC__UNIVERSAL__APPLICATION',
            'gateway_title'                                                                               => 'LC__CATG__NET_CONNECTIONS__GATEWAY'
        ];
    } // function

    /**
     * Modifies single rows for displaying links or getting translations
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row['isys_id'] = $p_row['isys_catg_net_connector_list__id'];

        $p_row['port_range'] = $p_row['isys_catg_net_connector_list__port_from'];

        if ($p_row['isys_catg_net_connector_list__port_from'] != $p_row['isys_catg_net_connector_list__port_to'])
        {
            $p_row['port_range'] .= '-' . $p_row['isys_catg_net_connector_list__port_to'];
        }

        if ($p_row['isys_catg_net_connector_list__isys_catg_net_listener_list__id'] > 0)
        {
            $l_listener_dao  = isys_cmdb_dao_category_g_net_listener::instance($this->m_db);
            $l_listener_data = $l_listener_dao->get_data_by_id($p_row['isys_catg_net_connector_list__isys_catg_net_listener_list__id'])
                ->get_row();

            if (isset($l_listener_data['isys_obj__id']))
            {
                $p_row['connected_to_listener'] = '<a href="?objID=' . $p_row['isys_obj__id'] . '">' . $l_listener_data['isys_obj__title'] . ' (' . _L(
                        $p_row['isys_obj_type__title']
                    ) . ')' . '</a>';
            }

            $l_protocol        = $l_listener_dao->get_dialog('isys_net_protocol', $p_row['isys_catg_net_listener_list__isys_net_protocol__id'])
                ->__to_array();
            $p_row['protocol'] = $l_protocol['isys_net_protocol__title'];
        }
        else
        {
            $p_row['connected_to_listener'] = '-';
        } // if

        if ($p_row['isys_catg_net_listener_list__opened_by'])
        {
            $p_row['opened_by'] = '<a href="?objID=' . $p_row['isys_catg_net_listener_list__opened_by'] . '">' . $this->m_cat_dao->obj_get_title_by_id_as_string(
                    $p_row['isys_catg_net_listener_list__opened_by']
                ) . '</a>';
        }
        else
        {
            $p_row['opened_by'] = '-';
        } // if
    } // function
} // class