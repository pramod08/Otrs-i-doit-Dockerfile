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
 * CMDB UI: Port category for Network
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_network_port extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        return "Port";
    } // function

    /**
     * Show the detail-template for port as a subcategory of network.
     *
     * @param   isys_cmdb_dao_category_g_network_port &$p_cat
     *
     * @return  null|void
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_dirs;

        if (isset($_GET['loadLayer2Vlans']))
        {
            isys_core::send_header('Content-Type', 'application/json');
            if (isset($_POST['ids']) && isys_format_json::is_json($_POST['ids']))
            {
                $l_dao_layer2 = isys_cmdb_dao_category_s_layer2_net::instance($p_cat->get_database_component());
                echo isys_format_json::encode($l_dao_layer2->get_layer2_vlans(isys_format_json::decode($_POST['ids'])));
            }
            else
            {
                echo '[]';
            } // if

            die;
        } // if

        $l_rules    = [];
        $l_gets     = isys_module_request::get_instance()
            ->get_gets();
        $l_posts    = isys_module_request::get_instance()
            ->get_posts();
        $l_bNewPort = false;
        $l_bSave    = ($l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE) ? true : false;

        $l_id                           = @$l_gets[C__CMDB__GET__CATLEVEL] | @$_POST[C__GET__ID][0];
        $l_gets[C__CMDB__GET__CATLEVEL] = $l_id;

        // Retrieve port data.
        $l_catdata = $p_cat->get_data($l_id)
            ->__to_array();

        // Check if this is a new port.
        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__NEW || $l_id == "-1")
        {
            global $g_port_id;
            $l_id = $g_port_id;
            $this->get_template_component()
                ->assign("nNewPort", "1");
            $l_bNewPort = true;
        } // if

        // Assign Port-ID to template.
        $this->get_template_component()
            ->assign("port_id", $l_id);

        // Go to list view after saving ports.
        if ($l_bSave && (!($_GET[C__CMDB__GET__CATLEVEL] > 0)) && $p_cat->validate_user_data())
        {
            return $this->process_list($p_cat);
        } // if

        // Get ip-addresses for linklist.
        $l_rules["C__CATG__PORT__IP_ADDRESS"] = $this->get_linklist($_GET[C__CMDB__GET__OBJECT], $l_id);

        // Assign some rules.
        $l_arYesNo = serialize(get_smarty_arr_YES_NO());

        $l_rules["C__CATG__PORT__TYPE"]["p_strTable"]        = "isys_port_type";
        $l_rules["C__CATG__PORT__MODE"]["p_strTable"]        = "isys_port_mode";
        $l_rules["C__CATG__PORT__PLUG"]["p_strTable"]        = "isys_plug_type";
        $l_rules["C__CATG__PORT__NEGOTIATION"]["p_strTable"] = "isys_port_negotiation";
        $l_rules["C__CATG__PORT__DUPLEX"]["p_strTable"]      = "isys_port_duplex";
        $l_rules["C__CATG__PORT__SPEED"]["p_strTable"]       = "isys_port_speed";

        $l_rules["C__CATG__PORT__STANDARD"]["p_strTable"] = "isys_port_standard";
        $l_rules["C__CATG__PORT__ACTIVE"]["p_arData"]     = $l_arYesNo;
        $l_rules["C__CATG__PORT__COUNT"]["p_strValue"]    = 1;
        $l_rules["C__CATG__PORT__NET"]["p_arTypeFilter"]  = C__OBJTYPE__LAYER3_NET;

        // Get interfaces and assign them.
        $l_dao_iface    = isys_cmdb_dao_category_g_network_interface::instance($this->m_database_component);
        $l_resInterface = $l_dao_iface->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

        $l_arInterface = [];

        if ($l_resInterface->num_rows() > 0)
        {
            while ($l_row = $l_resInterface->get_row())
            {
                if (is_numeric($l_row["isys_catg_netp_list__slotnumber"]) && $l_row["isys_catg_netp_list__slotnumber"] > 0)
                {
                    //$l_arInterface[$l_row["isys_catg_netp_list__id"]] = $l_row["isys_catg_netp_list__title"]." (Slot: ".$l_row["isys_catg_netp_list__slotnumber"].")";
                    $l_arInterface[_L(
                        'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE'
                    )][$l_row["isys_catg_netp_list__id"] . '_C__CMDB__SUBCAT__NETWORK_INTERFACE_P'] = $l_row["isys_catg_netp_list__title"] . " (Slot: " . $l_row["isys_catg_netp_list__slotnumber"] . ")";
                }
                else
                {
                    $l_arInterface[_L(
                        'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE'
                    )][$l_row["isys_catg_netp_list__id"] . '_C__CMDB__SUBCAT__NETWORK_INTERFACE_P'] = $l_row["isys_catg_netp_list__title"];
                } // if
            } // while

            $l_rules["C__CATG__PORT__INTERFACE"]["p_arData"] = serialize($l_arInterface);
        } // if

        //$l_dao_hba = isys_cmdb_dao_category_g_hba::instance($this->m_database_component);
        $l_dao_hba = new isys_cmdb_dao_category_g_hba($this->m_database_component);
        $l_resHBA  = $l_dao_hba->get_data(
            null,
            $_GET[C__CMDB__GET__OBJECT],
            "AND isys_hba_type__const = " . $l_dao_hba->convert_sql_text('C__STOR_TYPE_ISCSI_CONTROLLER'),
            null,
            C__RECORD_STATUS__NORMAL
        );

        if ($l_resHBA->num_rows() > 0)
        {
            while ($l_row = $l_resHBA->get_row())
            {
                $l_arInterface[_L('LC__CMDB__CATG__HBA')][$l_row["isys_catg_hba_list__id"] . '_C__CATG__HBA'] = $l_row["isys_catg_hba_list__title"];
            } // while

            $l_rules["C__CATG__PORT__INTERFACE"]["p_arData"] = serialize($l_arInterface);
        }

        // Assign port data.
        if (!$l_bNewPort)
        {
            $l_speed = isys_convert::speed(
                $l_catdata["isys_catg_port_list__port_speed_value"],
                (int) $l_catdata["isys_catg_port_list__isys_port_speed__id"],
                C__CONVERT_DIRECTION__BACKWARD
            );

            $l_request = isys_request::factory()
                ->set_row($l_catdata);

            $l_vlans        = $p_cat->get_attached_layer2_net($l_id);
            $l_vlanData     = $l_selected_vlans = [];
            $l_default_vlan = null;
            while ($l_vrow = $l_vlans->get_row())
            {
                $l_vlanData[$l_vrow['object_id']] = $l_vrow['title'];
                if ($l_vrow['default_vlan'] > 0)
                {
                    $l_default_vlan = $l_vrow['object_id'];
                } // if
            }
            $l_selected_vlans = array_keys($l_vlanData);

            // Get connection dao.
            $l_rules["C__CATG__PORT__DEST"]["p_strValue"]   = $p_cat->callback_property_assigned_connector($l_request);
            $l_rules["C__CATG__PORT__CABLE"]["p_strValue"]  = $p_cat->callback_property_cable($l_request);
            $l_rules["C__CATG__LAYER2__DEST"]["p_strValue"] = isys_format_json::encode($l_selected_vlans);

            $l_rules["C__CATG__PORT__DEFAULT_VLAN"]["p_arData"]        = $l_vlanData;
            $l_rules["C__CATG__PORT__DEFAULT_VLAN"]["p_strSelectedID"] = $l_default_vlan;

            $l_rules["C__CATG__PORT__TITLE"]["p_strValue"]                                                                = $l_catdata["isys_catg_port_list__title"];
            $l_rules["C__CATG__PORT__TYPE"]["p_strSelectedID"]                                                            = $l_catdata["isys_catg_port_list__isys_port_type__id"];
            $l_rules["C__CATG__PORT__MODE"]["p_strSelectedID"]                                                            = $l_catdata["isys_catg_port_list__isys_port_mode__id"];
            $l_rules["C__CATG__PORT__PLUG"]["p_strSelectedID"]                                                            = $l_catdata["isys_catg_port_list__isys_plug_type__id"];
            $l_rules["C__CATG__PORT__NEGOTIATION"]["p_strSelectedID"]                                                     = $l_catdata["isys_catg_port_list__isys_port_negotiation__id"];
            $l_rules["C__CATG__PORT__DUPLEX"]["p_strSelectedID"]                                                          = $l_catdata["isys_catg_port_list__isys_port_duplex__id"];
            $l_rules["C__CATG__PORT__SPEED_VALUE"]["p_strValue"]                                                          = $l_speed;
            $l_rules["C__CATG__PORT__SPEED"]["p_strSelectedID"]                                                           = $l_catdata["isys_catg_port_list__isys_port_speed__id"];
            $l_rules["C__CATG__PORT__STANDARD"]["p_strSelectedID"]                                                        = $l_catdata["isys_catg_port_list__isys_port_standard__id"];
            $l_rules["C__CATG__PORT__MAC"]["p_strValue"]                                                                  = $l_catdata["isys_catg_port_list__mac"];
            $l_rules["C__CATG__PORT__MTU"]["p_strValue"]                                                                  = $l_catdata["isys_catg_port_list__mtu"];
            $l_rules["C__CATG__PORT__ACTIVE"]["p_strSelectedID"]                                                          = $l_catdata["isys_catg_port_list__state_enabled"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_port_list__description"];
            $l_rules["C__CATG__PORT__INTERFACE"]["p_strSelectedID"]                                                       = (($l_catdata["isys_catg_port_list__isys_catg_netp_list__id"] > 0) ? $l_catdata["isys_catg_port_list__isys_catg_netp_list__id"] . '_C__CMDB__SUBCAT__NETWORK_INTERFACE_P' : $l_catdata["isys_catg_port_list__isys_catg_hba_list__id"] . '_C__CATG__HBA');
        }
        else
        {
            $l_rules["C__CATG__PORT__ACTIVE"]["p_strSelectedID"] = 1;
            $l_rules["C__CATG__PORT__TYPE"]["p_strSelectedID"]   = 3;
            //$l_rules["C__CATG__PORT__TITLE"]["p_strValue"] = "Port";
            $l_rules["C__CATG__PORT__SPEED_VALUE"]["p_strValue"]    = 1;
            $l_rules["C__CATG__PORT__SPEED"]["p_strSelectedID"]     = C__PORT_SPEED__GBIT_S;
            $l_rules['C__CATG__PORT__DUPLEX']['p_strSelectedID']    = C__PORT_DUPLEX__FULL;
            $l_rules["C__CATG__PORT__MODE"]["p_strSelectedID"]      = C__PORT_MODE__STANDARD;
            $l_rules["C__CATG__PORT__INTERFACE"]["p_strSelectedID"] = $_GET["ifaceID"] . '_C__CMDB__SUBCAT__NETWORK_INTERFACE_P';
            $this->get_template_component()
                ->assign("nNewPort", "1");
        } // if

        if (!$p_cat->validate_user_data() && $l_bSave)
        {
            $l_rules["C__CATG__PORT__TITLE"]["p_strValue"]                                                                = $l_posts["C__CATG__PORT__TITLE"];
            $l_rules["C__CATG__PORT__TYPE"]["p_strSelectedID"]                                                            = $l_posts["C__CATG__PORT__TYPE"];
            $l_rules["C__CATG__PORT__PLUG"]["p_strSelectedID"]                                                            = $l_posts["C__CATG__PORT__PLUG"];
            $l_rules["C__CATG__PORT__NEGOTIATION"]["p_strSelectedID"]                                                     = $l_posts["C__CATG__PORT__NEGOTIATION"];
            $l_rules["C__CATG__PORT__DUPLEX"]["p_strSelectedID"]                                                          = $l_posts["C__CATG__PORT__DUPLEX"];
            $l_rules["C__CATG__PORT__SPEED_VALUE"]["p_strValue"]                                                          = $l_posts["C__CATG__PORT__SPEED_VALUE"];
            $l_rules["C__CATG__PORT__SPEED"]["p_strSelectedID"]                                                           = $l_posts["C__CATG__PORT__SPEED"];
            $l_rules["C__CATG__PORT__STANDARD"]["p_strSelectedID"]                                                        = $l_posts["C__CATG__PORT__STANDARD"];
            $l_rules["C__CATG__PORT__MAC"]["p_strValue"]                                                                  = $l_posts["C__CATG__PORT__MAC"];
            $l_rules["C__CATG__PORT__MTU"]["p_strValue"]                                                                  = $l_posts["C__CATG__PORT__MTU"];
            $l_rules["C__CATG__PORT__ACTIVE"]["p_strSelectedID"]                                                          = $l_posts["C__CATG__PORT__ACTIVE"];
            $l_rules["C__CATG__PORT__DEST"]["p_strValue"]                                                                 = $l_posts["C__CATG__PORT__DEST"];
            $l_rules["C__CATG__PORT__COUNT"]["p_strValue"]                                                                = $l_posts["C__CATG__PORT__COUNT"];
            $l_rules["C__CATG__PORT__INTERFACE"]["p_strSelectedID"]                                                       = $l_posts["C__CATG__PORT__INTERFACE"];
            $l_rules["C__CATG__PORT__START_WITH"]["p_strValue"]                                                           = $l_posts["C__CATG__PORT__START_WITH"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_posts["C__CMDB__CAT__COMMENTARY"];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        } // if

        // Assign the images-path and apply rules.
        $this->get_template_component()
            ->assign(
                "port_ajax_url",
                "?" . http_build_query($_GET, null, "&") . "&call=category&" . C__CMDB__GET__CATLEVEL . "=" . $l_catdata["isys_catg_port_list__id"] . '&loadLayer2Vlans=1'
            )
            ->assign('dir_images', $g_dirs['images'])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->get_template();

        return null;
    } // function

    /**
     * Show the list-template for port as a subcategory of network.
     *
     * @param isys_cmdb_dao_category & $p_cat
     *
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        global $index_includes;
        $this->get_template_component()
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        $l_arGetVariables = isys_module_request::get_instance()
            ->get_gets();
        $l_nObjID         = $_GET[C__CMDB__GET__OBJECT];

        $l_listdao = new isys_cmdb_dao_list_catg_network_port($p_cat->get_database_component());
        $l_listdao->set_rec_status($_SESSION["cRecStatusListView"]);
        $l_listres       = $l_listdao->get_result(null, $l_nObjID, null);
        $l_arTableHeader = $l_listdao->get_fields();

        $l_arTemp                         = $l_arGetVariables;
        $l_arTemp[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
        $l_arTemp[C__CMDB__GET__CATG]     = $_GET[C__CMDB__GET__CATG] ? $_GET[C__CMDB__GET__CATG] : C__CMDB__SUBCAT__NETWORK_PORT;
        $l_arTemp[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
        $l_arTemp[C__GET__NAVMODE]        = null;

        $l_arTemp[C__CMDB__GET__CATLEVEL] = "[{isys_catg_port_list__id}]";

        $l_strRowLink = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_arTemp)));

        isys_component_template_navbar::getInstance()
            ->set_visible(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
            ->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        $l_objList = isys_component_list::factory(
            null,
            $l_listres,
            $l_listdao,
            $l_listdao->get_rec_status(),
            ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV ? 'csv' : 'html')
        );

        $l_objList->config($l_arTableHeader, $l_strRowLink, "[{isys_catg_port_list__id}]", true);

        if ($l_objList->createTempTable())
        {
            $l_strTempHtml = $l_objList->getTempTableHtml();
            $this->get_template_component()
                ->assign("objectTableList", $l_strTempHtml);
        } // if

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";

        return null;
    } // function

    /**
     * @param   integer $p_object_id
     * @param   integer $p_port_id
     *
     * @return  mixed
     */
    public function get_linklist($p_object_id, $p_port_id)
    {
        global $g_comp_template_language_manager;

        // Assign ip addresses.
        $l_ip_dao = new isys_cmdb_dao_category_g_ip($this->m_database_component);
        $l_ips    = $l_ip_dao->get_ips_by_obj_id($p_object_id, false, true);

        while ($l_row = $l_ips->get_row())
        {
            $l_address = $l_row["isys_cats_net_ip_addresses_list__title"] ? $l_row["isys_cats_net_ip_addresses_list__title"] : $l_row["isys_catg_ip_list__hostname"];

            if ($l_row['isys_catg_ip_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV4)
            {
                $l_ip_array[] = [
                    "id"   => $l_row["isys_catg_ip_list__id"],
                    "val"  => $l_address ? $l_address : $g_comp_template_language_manager->get("LC__IP__EMPTY_ADDRESS"),
                    "sel"  => (($p_port_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && !is_null($p_port_id)) ? true : false),
                    "link" => "?objID=" . C__CMDB__GET__OBJECT . "&catgID=" . C__CATG__IP . "&cateID=" . $l_row["isys_catg_ip_list__id"]
                ];
            }
            else
            {
                $l_ip_array[] = [
                    "id"   => $l_row["isys_catg_ip_list__id"],
                    "val"  => $l_address ? Ip::validate_ipv6($l_address, true) : $g_comp_template_language_manager->get("LC__IP__EMPTY_ADDRESS"),
                    "sel"  => (($p_port_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && !is_null($p_port_id)) ? true : false),
                    "link" => "?objID=" . C__CMDB__GET__OBJECT . "&catgID=" . C__CATG__IP . "&cateID=" . $l_row["isys_catg_ip_list__id"]
                ];
            } // if
        } // while

        $l_rules                                             = [];
        $l_rules["C__CATG__PORT__IP_ADDRESS"]["p_bLinklist"] = true;
        $l_rules["C__CATG__PORT__IP_ADDRESS"]["p_arData"]    = serialize($l_ip_array);

        return $l_rules["C__CATG__PORT__IP_ADDRESS"];
    } // function
} // class