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
 * i-doit Report View for showing all network connections
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_view_network_connections extends isys_report_view
{

    /**
     * Method for ajax-requests.
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function ajax_request()
    {
        global $g_comp_database;

        $l_dao_connections = new isys_cmdb_dao_category_g_net_listener($g_comp_database);

        $l_condition = '';
        if (isset($_POST['dialog_protocol']) && $_POST['dialog_protocol'] > 0)
        {
            $l_condition .= ' AND (isys_net_protocol__id = ' . $l_dao_connections->convert_sql_id($_POST['dialog_protocol']) . ')';
        }
        if (isset($_POST['dialog_protocol_5']) && $_POST['dialog_protocol_5'] > 0)
        {
            $l_condition .= ' AND (isys_net_protocol_layer_5__id = ' . $l_dao_connections->convert_sql_id($_POST['dialog_protocol_5']) . ')';
        }

        if (isset($_POST['text_port']) && $_POST['text_port'] > 0)
        {
            $l_condition .= ' AND (isys_catg_net_listener_list__port_from >= ' . $l_dao_connections->convert_sql_id(
                    $_POST['text_port']
                ) . ' AND isys_catg_net_listener_list__port_to <= ' . $l_dao_connections->convert_sql_id($_POST['text_port']) . ')';
        }

        if (isset($_POST['dialog_net']) && $_POST['dialog_net'] > 0)
        {
            $l_condition .= ' AND (network.isys_obj__id = ' . $l_dao_connections->convert_sql_id($_POST['dialog_net']) . ')';
        }

        $l_connections = $l_dao_connections->get_connections($l_condition);

        $l_headers = [
            _L('LC__CMDB__OBJTYPE__LAYER3_NET'),
            _L('LC__CMDB__CATG__NET_CONNECTOR__SOURCE_DEVICE'),
            _L('LC__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS'),
            _L('LC__CATD__PROTOCOL') . '/Port',
            _L('LC__CMDB__CATG__NET_LISTENER__BIND_DEVICE'),
            _L('LC__CMDB__CATG__NET_LISTENER__DESTINATION_IP_ADDRESS'),
            _L('LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION'),
            _L('LC__CATG__NET_CONNECTIONS__GATEWAY') . '-Source',
            _L('LC__CATG__NET_CONNECTIONS__GATEWAY') . '-Destination',
        ];

        $l_return = [];
        while ($l_row = $l_connections->get_row())
        {

            if (isset($l_row['protocol_layer_5']) && $l_row['protocol_layer_5'])
            {
                $l_layer5 = ': ' . $l_row['protocol_layer_5'];
            }
            else $l_layer5 = '';

            $l_return[] = [
                $l_headers[0] => $l_row['network'] . ' (' . $l_row['net_address'] . ')',
                $l_headers[1] => $l_row['source_object'],
                $l_headers[2] => $l_row['source_ip'],
                $l_headers[3] => '<= ' . $l_row['protocol'] . $l_layer5 . '/' . ($l_row['source_port_from'] == $l_row['source_port_to'] ? $l_row['source_port_from'] : $l_row['source_port_from'] . '-' . $l_row['source_port_to']) . ' =>',
                $l_headers[4] => $l_row['bind_object'],
                $l_headers[5] => $l_row['bind_ip'],
                $l_headers[6] => $l_row['bind_application'] ? $l_row['bind_application'] : '-',
                $l_headers[7] => $l_row['source_gateway'] ? $l_row['source_gateway'] : '-',
                $l_headers[8] => $l_row['bind_gateway'] ? $l_row['bind_gateway'] : '-',
            ];
        }

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);

        die;
    } // function

    /**
     * Method for retrieving the language constant of the report-description.
     *
     * @return  string
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__DESCRIPTION__NETWORK_CONNECTIONS';
    } // function

    /**
     * Initialize method.
     *
     * @return  boolean
     */
    public function init()
    {
        return true;
    } // function

    /**
     * Method for retrieving the language constant of the report-name.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function name()
    {
        return 'LC__CATG__NET_CONNECTIONS';
    } // function

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @global  isys_component_template $g_comp_template
     * @global  isys_component_database $g_comp_database
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function start()
    {
        global $g_comp_template, $g_comp_database;

        $l_dao_connections                        = new isys_cmdb_dao_category_g_net_listener($g_comp_database);
        $l_rules['dialog_protocol']['p_arData']   = serialize($l_dao_connections->get_dialog_as_array('isys_net_protocol'));
        $l_rules['dialog_protocol_5']['p_arData'] = serialize($l_dao_connections->get_dialog_as_array('isys_net_protocol_layer_5'));

        $l_dao_net    = new isys_cmdb_dao_category_s_net($g_comp_database);
        $l_arNetworks = [];
        $l_networks   = $l_dao_net->get_data();
        while ($l_row = $l_networks->get_row())
        {
            $l_arNetworks[$l_row['isys_obj__id']] = $l_row['isys_obj__title'] . ' (' . $l_row['isys_cats_net_list__address'] . ')';
        }
        $l_rules['dialog_net']['p_arData'] = serialize($l_arNetworks);

        $g_comp_template->activate_editmode();

        $g_comp_template->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Method for retrieving the template-name of this report.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public function template()
    {
        return 'view_network_connections.tpl';
    } // function

    /**
     * Method for declaring the type of this report.
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public static function type()
    {
        return self::c_php_view;
    }

    /**
     * Method for declaring the view-type.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }
}

?>