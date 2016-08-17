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
 * i-doit Report View which shows all connections from objects assigned to racks
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   Copyright 2012 - synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_view_rack_connections extends isys_report_view
{

    /**
     * CSV Content
     *
     * @var array
     */
    private $m_csv_arr = [];
    /**
     * Encoding type
     *
     * @var string
     */
    private $m_encoding = 'UTF-8';

    /**
     * Mapping for the table header
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private static function get_header_fields($p_category = null)
    {
        $l_return = [
            C__CATG__CONNECTOR            => [
                _L('LC__CMDB__OBJTYPE__ENCLOSURE'),
                _L('LC__CMDB__CATG__RACKUNITS_ABBR'),
                _L('LC_UNIVERSAL__OBJECT'),
                _L('LC__CATG__STORAGE_CONNECTION_TYPE'),
                _L('LC__CMDB__CATG__NETWORK__TARGET_OBJECT') . ' (' . _L('LC__CMDB__CATG__UI_ASSIGNED_UI') . ')',
                _L('LC__REPORT__VIEW__RACKS_CONNECTIONS__TARGET_CONNECTOR'),
                isys_glob_utf8_encode(_L('LC__CATG__CONNECTOR__CATEGORY_TYPE')),
                isys_glob_utf8_encode(_L('LC__REPORT__VIEW__RACKS_CONNECTIONS__SIBLING_CONNECTOR')),
                _L('LC__CATG__CONNECTOR__CONNECTED_NET'),
                _L('LC__CATG__CONNECTOR__CONNECTION_TYPE'),
                _L('LC__CATG__CONNECTOR__INOUT')
            ],
            C__CATG__CONTROLLER_FC_PORT   => [
                _L('LC__CMDB__OBJTYPE__ENCLOSURE'),
                _L('LC__CMDB__CATG__RACKUNITS_ABBR'),
                _L('LC_UNIVERSAL__OBJECT'),
                _L('LC__CATG__STORAGE_CONNECTION_TYPE'),
                _L('LC__CMDB__CATG__NETWORK__TARGET_OBJECT') . ' (' . _L('LC__CMDB__CATG__UI_ASSIGNED_UI') . ')',
                _L('LC__REPORT__VIEW__RACKS_CONNECTIONS__TARGET_CONNECTOR'),
                _L('LC__CATG__CONTROLLER_FC_PORT_TYPE'),
                _L('LC__CATG__CONTROLLER_FC_CONTROLLER'),
                _L('LC__CATG__CONTROLLER_FC_PORT_MEDIUM'),
                _L('LC__CMDB__CATG__PORT__SPEED'),
                _L('LC__CMDB__CATG__PORT__SPEED_UNIT'),
                _L('LC__CATG__CONTROLLER_FC_PORT_NODE_WWN'),
                _L('LC__CATG__CONTROLLER_FC_PORT_PORT_WWN'),
                _L('LC__CMDB__CATS__SAN_ZONE')
            ],
            C__CATG__UNIVERSAL_INTERFACE  => [
                _L('LC__CMDB__OBJTYPE__ENCLOSURE'),
                _L('LC__CMDB__CATG__RACKUNITS_ABBR'),
                _L('LC_UNIVERSAL__OBJECT'),
                _L('LC__CATG__STORAGE_CONNECTION_TYPE'),
                _L('LC__CMDB__CATG__NETWORK__TARGET_OBJECT') . ' (' . _L('LC__CMDB__CATG__UI_ASSIGNED_UI') . ')',
                _L('LC__REPORT__VIEW__RACKS_CONNECTIONS__TARGET_CONNECTOR'),
                _L('LC__CMDB__CATG__UI_CONNECTION_TYPE'),
                _L('LC__CMDB__CATG__UI_PLUG_TYPE')
            ],
            C__CMDB__SUBCAT__NETWORK_PORT => [
                _L('LC__CMDB__OBJTYPE__ENCLOSURE'),
                _L('LC__CMDB__CATG__RACKUNITS_ABBR'),
                _L('LC_UNIVERSAL__OBJECT'),
                _L('LC__CATG__STORAGE_CONNECTION_TYPE'),
                _L('LC__CMDB__CATG__NETWORK__TARGET_OBJECT') . ' (' . _L('LC__CMDB__CATG__UI_ASSIGNED_UI') . ')',
                _L('LC__REPORT__VIEW__RACKS_CONNECTIONS__TARGET_CONNECTOR'),
                _L('LC__CMDB__CATG__TYPE'),
                _L('LC__CMDB__CATS__SWITCH_FC_ACTIVE'),
                _L('LC__CMDB__LAYER2_NET'),
                _L('LC__CMDB__CATG__PORT__STANDARD'),
                _L('LC__CMDB__CATG__PORT__MODE'),
                _L('LC__CMDB__CATG__PORT__NEGOTIATION'),
                _L('LC__CMDB__CATG__PORT__DUPLEX'),
                _L('LC__CMDB__CATG__PORT__SPEED'),
                _L('LC__CMDB__CATG__PORT__SPEED_UNIT'),
                _L('LC__CMDB__CATG__PORT__MAC')
            ]
        ];
        if (!empty($p_category))
        {
            return $l_return[$p_category];
        }

        return $l_return;
    }

    /**
     * Gets the categories for the dialog field
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private static function get_categories_for_view()
    {
        return [
            C__CATG__CONNECTOR            => _L('LC__CMDB__CATG__CONNECTORS'),
            C__CATG__CONTROLLER_FC_PORT   => _L('LC__STORAGE_FCPORT'),
            C__CATG__UNIVERSAL_INTERFACE  => _L('LC__CMDB__CATG__UNIVERSAL_INTERFACE'),
            C__CMDB__SUBCAT__NETWORK_PORT => _L('LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT')
        ];
    }

    /**
     * Method for ajax-requests.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ajax_request()
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao::instance($g_comp_database);

        $l_selectedcategory = isys_glob_get_param('selectedCategory');
        $l_objectfilter     = isys_glob_get_param('objectFilter');
        $l_query_obj_filter = '';

        if (!empty($l_objectfilter))
        {
            if (isys_format_json::is_json($l_objectfilter))
            {
                $l_objectfilter = isys_format_json::decode($l_objectfilter);
            }
            if (is_array($l_objectfilter) && count($l_objectfilter) > 0)
            {
                $l_query_obj_filter = ' AND main.isys_obj__id IN (' . implode(',', $l_objectfilter) . ')';
            }
        }

        $l_query = $this->get_view_query($l_selectedcategory) . $l_query_obj_filter;

        $l_res = $l_dao->retrieve($l_query);
        if ($l_res->num_rows() > 0)
        {
            $l_counter = 0;
            while ($l_row = $l_res->get_row())
            {
                $this->modify_row($l_row, $l_selectedcategory);
                $l_result[] = $l_row;
                foreach ($l_row AS $l_val)
                {
                    $this->m_csv_arr[$l_counter][] = strip_tags($l_val);
                }
                $l_counter++;
            }
        }
        else
        {
            $l_result = null;
        }

        $this->generate_csv_file($l_selectedcategory);

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_result);
        die;
    } // function

    /**
     * Method for retrieving the language constant of the report-description.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__RACKS_CONNECTIONS_DESCRIPTION';
    } // function

    /**
     * Initialize method.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        return true;
    } // function

    /**
     * Method for retrieving the language constant of the report-name.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__RACKS_CONNECTIONS';
    } // function

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @global  isys_component_template $g_comp_template
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function start()
    {
        global $g_comp_template;

        $l_rules['C__VIEW_RACK_CONNECTIONS__SELECTED_CATEGORY']['p_arData']        = serialize($this->get_categories_for_view());
        $l_rules['C__VIEW_RACK_CONNECTIONS__SELECTED_CATEGORY']['p_strSelectedID'] = C__CMDB__SUBCAT__NETWORK_PORT;

        $g_comp_template->assign('download_link', 'temp/csv_export_report_view_rack_connections.csv')
            ->assign('no_entries_found', _L('LC__CMDB__FILTER__NOTHING_FOUND_STD'))
            ->assign('header_json', isys_format_json::encode($this->get_header_fields()))
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->assign('categories', $this->get_categories_for_view())
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Method for retrieving the template-name of this report.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public function template()
    {
        return 'view_rack_connections.tpl';
    } // function

    /**
     * Method for declaring the type of this report.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function type()
    {
        return self::c_php_view;
    } // function

    /**
     * Method for declaring the view-type.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }

    /**
     * Gets the needed query for the report view.
     *
     * @param   integer $p_category
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function get_view_query($p_category)
    {
        switch ($p_category)
        {
            default:
            case C__CATG__CONNECTOR:
                $l_query = "SELECT main.isys_obj__title AS '0', child.isys_obj__title AS '2', connector.isys_catg_connector_list__title AS '3', child2.isys_obj__title AS '4',
					connected.isys_catg_connector_list__title AS '5', connector.isys_catg_connector_list__assigned_category AS '6', sibling.isys_catg_connector_list__title AS '7',
					lnet.isys_obj__title AS '8', isys_connection_type__title AS '9', connector.isys_catg_connector_list__type AS '10',

					main_ff.isys_catg_formfactor_list__rackunits AS rack_ru, child_ff.isys_catg_formfactor_list__rackunits AS obj_ru,
					child_loc.isys_catg_location_list__pos AS obj_pos, main.isys_obj__id AS main_obj_id, child.isys_obj__id AS child_obj_id, child2.isys_obj__id AS child2_obj_id,
					lnet.isys_obj__id AS lnet_obj_id
					FROM isys_obj AS main

					INNER JOIN isys_catg_formfactor_list main_ff ON main_ff.isys_catg_formfactor_list__isys_obj__id = main.isys_obj__id
					INNER JOIN isys_catg_location_list AS main_loc ON main_loc.isys_catg_location_list__parentid = main.isys_obj__id
					INNER JOIN isys_obj AS child ON child.isys_obj__id = main_loc.isys_catg_location_list__isys_obj__id
					INNER JOIN isys_catg_location_list AS child_loc ON child.isys_obj__id = child_loc.isys_catg_location_list__isys_obj__id
					INNER JOIN isys_catg_connector_list AS connector ON connector.isys_catg_connector_list__isys_obj__id = child.isys_obj__id
					INNER JOIN isys_connection ON isys_connection__id = connector.isys_catg_connector_list__isys_connection__id

					LEFT JOIN isys_catg_connector_list AS sibling ON sibling.isys_catg_connector_list__isys_catg_connector_list__id = connector.isys_catg_connector_list__id OR sibling.isys_catg_connector_list__id = connector.isys_catg_connector_list__isys_catg_connector_list__id
					LEFT JOIN isys_catg_connector_list AS connected ON connected.isys_catg_connector_list__isys_cable_connection__id = connector.isys_catg_connector_list__isys_cable_connection__id AND connector.isys_catg_connector_list__id != connected.isys_catg_connector_list__id
					LEFT JOIN isys_obj AS child2 ON child2.isys_obj__id = connected.isys_catg_connector_list__isys_obj__id
					LEFT JOIN isys_catg_formfactor_list AS child_ff ON child_ff.isys_catg_formfactor_list__isys_obj__id = child.isys_obj__id
					LEFT JOIN isys_obj AS lnet ON lnet.isys_obj__id = isys_connection__isys_obj__id

					LEFT JOIN isys_connection_type ON isys_connection_type__id = connector.isys_catg_connector_list__isys_connection_type__id";
                break;

            case C__CATG__UNIVERSAL_INTERFACE:
                $l_query = "SELECT main.isys_obj__title AS '0', child.isys_obj__title AS '2', connector.isys_catg_connector_list__title AS '3', child2.isys_obj__title AS '4',
					connected.isys_catg_connector_list__title AS '5', isys_ui_con_type__title AS '6', isys_ui_plugtype__title AS '7',
					main_ff.isys_catg_formfactor_list__rackunits AS rack_ru, child_ff.isys_catg_formfactor_list__rackunits AS obj_ru,
					child_loc.isys_catg_location_list__pos AS obj_pos, main.isys_obj__id AS main_obj_id, child.isys_obj__id AS child_obj_id, child2.isys_obj__id AS child2_obj_id
					FROM isys_obj AS main

					INNER JOIN isys_catg_formfactor_list main_ff ON main_ff.isys_catg_formfactor_list__isys_obj__id = main.isys_obj__id
					INNER JOIN isys_catg_location_list AS main_loc ON main_loc.isys_catg_location_list__parentid = main.isys_obj__id
					INNER JOIN isys_obj AS child ON child.isys_obj__id = main_loc.isys_catg_location_list__isys_obj__id
					INNER JOIN isys_catg_location_list AS child_loc ON child.isys_obj__id = child_loc.isys_catg_location_list__isys_obj__id

					INNER JOIN isys_catg_ui_list ON child.isys_obj__id = isys_catg_ui_list__isys_obj__id
					INNER JOIN isys_catg_connector_list AS connector ON connector.isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id
					LEFT JOIN isys_catg_connector_list AS connected ON connected.isys_catg_connector_list__isys_cable_connection__id = connector.isys_catg_connector_list__isys_cable_connection__id AND connector.isys_catg_connector_list__id != connected.isys_catg_connector_list__id
					LEFT JOIN isys_obj AS child2 ON child2.isys_obj__id = connected.isys_catg_connector_list__isys_obj__id

					LEFT JOIN isys_catg_formfactor_list AS child_ff ON child_ff.isys_catg_formfactor_list__isys_obj__id = child.isys_obj__id

					LEFT JOIN isys_ui_con_type ON isys_ui_con_type__id = isys_catg_ui_list__isys_ui_con_type__id
					LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id";
                break;

            case C__CATG__CONTROLLER_FC_PORT:
                $l_query = "SELECT main.isys_obj__title AS '0', child.isys_obj__title AS '2', connector.isys_catg_connector_list__title AS '3', child2.isys_obj__title AS '4',
					connected.isys_catg_connector_list__title AS '5', isys_fc_port_type__title AS '6', isys_fc_port_medium__title AS '8', isys_catg_fc_port_list__port_speed AS '9',
					isys_port_speed__title AS '10', isys_catg_fc_port_list__wwn AS '11', isys_catg_fc_port_list__wwpn AS '12', san_obj.isys_obj__title AS '13',

					isys_catg_controller_list__title AS controller1, isys_catg_hba_list__title AS controller2,
					main_ff.isys_catg_formfactor_list__rackunits AS rack_ru, child_ff.isys_catg_formfactor_list__rackunits AS obj_ru,
					child_loc.isys_catg_location_list__pos AS obj_pos, main.isys_obj__id AS main_obj_id, child.isys_obj__id AS child_obj_id, child2.isys_obj__id AS child2_obj_id,
					san_obj.isys_obj__id AS san_obj_id, isys_port_speed__id

					FROM isys_obj AS main

					INNER JOIN isys_catg_formfactor_list main_ff ON main_ff.isys_catg_formfactor_list__isys_obj__id = main.isys_obj__id
					INNER JOIN isys_catg_location_list AS main_loc ON main_loc.isys_catg_location_list__parentid = main.isys_obj__id
					INNER JOIN isys_obj AS child ON child.isys_obj__id = main_loc.isys_catg_location_list__isys_obj__id
					INNER JOIN isys_catg_location_list AS child_loc ON child.isys_obj__id = child_loc.isys_catg_location_list__isys_obj__id

					INNER JOIN isys_catg_fc_port_list ON child.isys_obj__id = isys_catg_fc_port_list__isys_obj__id
					INNER JOIN isys_catg_connector_list AS connector ON connector.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
					LEFT JOIN isys_catg_connector_list AS connected ON connected.isys_catg_connector_list__isys_cable_connection__id = connector.isys_catg_connector_list__isys_cable_connection__id AND connector.isys_catg_connector_list__id != connected.isys_catg_connector_list__id
					LEFT JOIN isys_obj AS child2 ON child2.isys_obj__id = connected.isys_catg_connector_list__isys_obj__id

					LEFT JOIN isys_catg_formfactor_list AS child_ff ON child_ff.isys_catg_formfactor_list__isys_obj__id = child.isys_obj__id
					LEFT JOIN isys_san_zoning_fc_port ON isys_san_zoning_fc_port__isys_catg_fc_port_list__id = isys_catg_fc_port_list__id
					LEFT JOIN isys_cats_san_zoning_list ON isys_cats_san_zoning_list__id = isys_san_zoning_fc_port__isys_cats_san_zoning_list__id
					LEFT JOIN isys_obj AS san_obj ON san_obj.isys_obj__id = isys_cats_san_zoning_list__isys_obj__id

					LEFT JOIN isys_fc_port_type ON isys_fc_port_type__id = isys_catg_fc_port_list__isys_fc_port_type__id
					LEFT JOIN isys_fc_port_medium ON isys_fc_port_medium__id = isys_catg_fc_port_list__isys_fc_port_medium__id
					LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_fc_port_list__isys_port_speed__id
					LEFT JOIN isys_catg_controller_list ON isys_catg_controller_list__id = isys_catg_fc_port_list__isys_catg_controller_list__id
					LEFT JOIN isys_catg_hba_list ON isys_catg_hba_list__id = isys_catg_fc_port_list__isys_catg_hba_list__id";
                break;

            case C__CMDB__SUBCAT__NETWORK_PORT:
                $l_query = "SELECT main.isys_obj__title AS '0', child.isys_obj__title AS '2', connector.isys_catg_connector_list__title AS '3', child2.isys_obj__title AS '4',
					connected.isys_catg_connector_list__title AS '5', isys_port_type__title AS '6', isys_catg_port_list__state_enabled AS '7', layer2_obj.isys_obj__title AS '8',
					isys_port_standard__title AS '9', isys_port_mode__title AS '10', isys_port_negotiation__title AS '11', isys_port_duplex__title AS '12',
					isys_catg_port_list__port_speed_value AS '13', isys_port_speed__title AS '14', isys_catg_port_list__mac AS '15',
					isys_port_speed__id, main_ff.isys_catg_formfactor_list__rackunits AS rack_ru, child_ff.isys_catg_formfactor_list__rackunits AS obj_ru,
					child_loc.isys_catg_location_list__pos AS obj_pos, main.isys_obj__id AS main_obj_id, child.isys_obj__id AS child_obj_id, child2.isys_obj__id AS child2_obj_id,
					layer2_obj.isys_obj__id AS layer2_obj_id
					FROM isys_obj AS main

					INNER JOIN isys_catg_formfactor_list main_ff ON main_ff.isys_catg_formfactor_list__isys_obj__id = main.isys_obj__id
					INNER JOIN isys_catg_location_list AS main_loc ON main_loc.isys_catg_location_list__parentid = main.isys_obj__id
					INNER JOIN isys_obj AS child ON child.isys_obj__id = main_loc.isys_catg_location_list__isys_obj__id
					INNER JOIN isys_catg_location_list AS child_loc ON child.isys_obj__id = child_loc.isys_catg_location_list__isys_obj__id
					INNER JOIN isys_catg_port_list ON child.isys_obj__id = isys_catg_port_list__isys_obj__id
					INNER JOIN isys_catg_connector_list AS connector ON connector.isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
					LEFT JOIN isys_catg_connector_list AS connected ON connected.isys_catg_connector_list__isys_cable_connection__id = connector.isys_catg_connector_list__isys_cable_connection__id AND connector.isys_catg_connector_list__id != connected.isys_catg_connector_list__id
					LEFT JOIN isys_obj AS child2 ON child2.isys_obj__id = connected.isys_catg_connector_list__isys_obj__id

					LEFT JOIN isys_catg_formfactor_list AS child_ff ON child_ff.isys_catg_formfactor_list__isys_obj__id = child.isys_obj__id
					LEFT JOIN isys_cats_layer2_net_assigned_ports_list AS layer2_assign ON layer2_assign.isys_catg_port_list__id = isys_catg_port_list.isys_catg_port_list__id
					LEFT JOIN isys_obj AS layer2_obj ON layer2_obj.isys_obj__id = layer2_assign.isys_cats_layer2_net_assigned_ports_list__isys_obj__id

					LEFT JOIN isys_port_type ON isys_port_type__id = isys_catg_port_list__isys_port_type__id
					LEFT JOIN isys_port_standard ON isys_port_standard__id = isys_catg_port_list__isys_port_standard__id
					LEFT JOIN isys_port_mode ON isys_port_mode__id = isys_catg_port_list__isys_port_mode__id
					LEFT JOIN isys_port_negotiation ON isys_port_negotiation__id = isys_catg_port_list__isys_port_negotiation__id
					LEFT JOIN isys_port_duplex ON isys_port_duplex__id = isys_catg_port_list__isys_port_duplex__id
					LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id";
                break;
        } // switch

        return $l_query . " WHERE main.isys_obj__isys_obj_type__id = " . C__OBJTYPE__ENCLOSURE;
    }

    /**
     * Modifies the result for each selected category
     *
     * @param $p_row
     * @param $p_category
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function modify_row(&$p_row, $p_category)
    {
        $p_row['0'] = '<a href="' . isys_glob_build_url('objID=' . $p_row['main_obj_id']) . '">' . htmlentities($p_row['0']) . '</a>';
        $p_row['2'] = '<a href="' . isys_glob_build_url('objID=' . $p_row['child_obj_id']) . '">' . htmlentities($p_row['2']) . '</a>';
        $p_row['3'] = htmlentities($p_row['3']);
        $p_row['4'] = '<a href="' . isys_glob_build_url('objID=' . $p_row['child2_obj_id']) . '">' . htmlentities($p_row['4']) . '</a>';
        $p_row['5'] = htmlentities($p_row['5']);

        $l_start = $p_row['rack_ru'];
        if ($p_row['obj_ru'] > 1)
        {
            for ($i = $p_row['obj_pos'];$i > 1;$i--)
            {
                $l_start--;
            }
            $l_end = $l_start;
            for ($i = $p_row['obj_ru'];$i > 1;$i--)
            {
                $l_end--;
            }
            $p_row['1'] = $l_start . ' - ' . $l_end;
        }
        else
        {
            $p_row['1'] = $l_start;
        }

        switch ($p_category)
        {
            case C__CATG__CONNECTOR:

                switch ($p_row['6'])
                {
                    case C__CATG__CONTROLLER_FC_PORT:
                    case 'C__CATG__CONTROLLER_FC_PORT':
                        $p_row['6'] = htmlentities(_L('LC__STORAGE_FCPORT'));
                        break;
                    case C__CATG__POWER_CONSUMER:
                    case 'C__CATG__POWER_CONSUMER':
                        $p_row['6'] = htmlentities(_L('LC__CMDB__CATG__POWER_CONSUMER'));
                        break;
                    case C__CMDB__SUBCAT__NETWORK_PORT:
                    case 'C__CMDB__SUBCAT__NETWORK_PORT':
                        $p_row['6'] = htmlentities(_L('LC__CATD__PORT'));
                        break;
                    case C__CATG__CONNECTOR:
                    case 'C__CATG__CONNECTOR':
                        $p_row['6'] = htmlentities(_L('LC__CMDB__CATG__CONNECTORS'));
                        break;
                    case C__CATG__UNIVERSAL_INTERFACE:
                    case 'C__CATG__UNIVERSAL_INTERFACE':
                        $p_row['6'] = htmlentities(_L('LC__CMDB__CATG__UNIVERSAL_INTERFACE'));
                        break;
                }

                $p_row['7']  = htmlentities($p_row['7']);
                $p_row['9']  = htmlentities($p_row['9']);
                $p_row['10'] = htmlentities(($p_row['10'] == C__CONNECTOR__INPUT) ? _L('LC__CATG__CONNECTOR__INPUT') : _L('LC__CATG__CONNECTOR__OUTPUT'));

                if ($p_row['lnet_obj_id'] > 0)
                {
                    $p_row['8'] = '<a href="' . isys_glob_build_url('objID=' . $p_row['lnet_obj_id']) . '">' . htmlentities($p_row['8']) . '</a>';
                }
                unset($p_row['lnet_obj_id']);
                break;
            case C__CATG__CONTROLLER_FC_PORT:

                $p_row['6'] = htmlentities(_L($p_row['6']));
                $p_row['7'] = htmlentities(($p_row['controller1'] != '') ? $p_row['controller1'] : $p_row['controller2']);
                $p_row['8'] = htmlentities(_L($p_row['8']));
                $p_row['9'] = isys_convert::speed($p_row['9'], $p_row['isys_port_speed__id'], C__CONVERT_DIRECTION__BACKWARD);
                if ($p_row['san_obj_id'] > 0)
                {
                    $p_row['13'] = '<a href="' . isys_glob_build_url('objID=' . $p_row['san_obj_id']) . '">' . htmlentities($p_row['13']) . '</a>';
                }

                unset($p_row['controller1']);
                unset($p_row['controller2']);
                unset($p_row['isys_port_speed__id']);
                unset($p_row['san_obj_id']);
                break;
            case C__CATG__UNIVERSAL_INTERFACE:
                $p_row['6'] = htmlentities(_L($p_row['6']));
                $p_row['7'] = htmlentities(_L($p_row['7']));
                break;
            case C__CMDB__SUBCAT__NETWORK_PORT:

                if ($p_row['layer2_obj_id'] > 0)
                {
                    $p_row['8'] = '<a href="' . isys_glob_build_url('objID=' . $p_row['layer2_obj_id']) . '">' . htmlentities($p_row['8']) . '</a>';
                }

                $p_row['6']  = htmlentities(_L($p_row['6']));
                $p_row['9']  = htmlentities(_L($p_row['9']));
                $p_row['10'] = htmlentities(_L($p_row['10']));
                $p_row['11'] = htmlentities(_L($p_row['11']));
                $p_row['12'] = htmlentities(_L($p_row['12']));
                $p_row['7']  = ($p_row['7'] == 1) ? _L('LC__UNIVERSAL__YES') : _L('LC__UNIVERSAL__NO');

                $p_row['13'] = isys_convert::speed($p_row['13'], $p_row['isys_port_speed__id'], C__CONVERT_DIRECTION__BACKWARD);
                unset($p_row['isys_port_speed__id']);
                unset($p_row['layer2_obj_id']);

                break;
        }

        unset($p_row['rack_ru']);
        unset($p_row['obj_ru']);
        unset($p_row['obj_pos']);
        unset($p_row['main_obj_id']);
        unset($p_row['child_obj_id']);
        unset($p_row['child2_obj_id']);

        ksort($p_row);
    }

    /**
     * Generates CSV File for this report view
     *
     * @param $p_selected_category
     *
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function generate_csv_file($p_selected_category)
    {
        global $g_dirs;

        $l_encoding = array_shift(
            isys_component_signalcollection::get_instance()
                ->emit(
                    "mod.report.views.view_rack_connections.set_encoding_type"
                )
        );

        if ($l_encoding != '' && $l_encoding != $this->m_encoding)
        {
            $this->m_encoding = $l_encoding;
        }

        $l_header = self::get_header_fields($p_selected_category);

        $l_filename = $g_dirs['temp'] . "csv_export_report_view_rack_connections.csv";
        $l_csv      = implode(';', $l_header) . "\n";
        try
        {
            $l_handler = fopen($l_filename, 'w');

            foreach ($this->m_csv_arr AS $l_row)
            {
                $l_csv_row = '';
                foreach ($l_row AS $l_value)
                {
                    $l_csv_row .= trim($l_value) . ';';
                }
                $l_csv .= rtrim($l_csv_row, ';') . "\n";
            }

            if ($this->m_encoding != 'UTF-8')
            {
                $l_csv = iconv('UTF-8', $this->m_encoding, $l_csv);
            }

            fwrite($l_handler, $l_csv);
            fclose($l_handler);
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }
}

?>