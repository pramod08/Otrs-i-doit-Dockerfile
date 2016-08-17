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
 * CMDB Virtual Switches
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_virtual_switch extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_virtual_switch $p_cat
     *
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs;

        $l_id = $_GET[C__CMDB__GET__CATLEVEL] ?: $_POST[C__GET__ID][0];

        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_daoIP     = new isys_cmdb_dao_category_g_ip($p_cat->get_database_component());
        $l_f_text    = new isys_smarty_plugin_f_text();

        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_ips = $l_daoIP->get_ips_by_obj_id($_GET[C__CMDB__GET__OBJECT]);

        $l_arIPs = [];
        while ($l_row = $l_ips->get_row())
        {
            $l_arIPs[$l_row["isys_catg_ip_list__id"]] = $l_row["isys_cats_net_ip_addresses_list__title"];
        } // while

        // Get Port groups for this Virtual Switch.
        $l_pgs = $p_cat->get_port_groups($l_id);

        $l_pgData  = "";
        $l_pgCount = 0;
        while ($l_row = $l_pgs->get_row())
        {
            $l_pgData .= "<tr name=\"pg_" . $l_pgCount . "\" id=\"pg_" . $l_pgCount . "\">";
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_pgData .= '<td>' . $l_f_text->navigation_edit(
                        $this->get_template_component(),
                        [
                            'name'              => 'C__CATG__VSWITCH_PG_NAME_' . $l_pgCount,
                            'p_strStyle'        => 'width:100%;',
                            'p_strValue'        => $l_row['isys_virtual_port_group__title'],
                            'p_bInfoIconSpacer' => 0
                        ]
                    ) . '</td>';
            }
            else
            {
                $l_pgData .= "<td>" . $l_row["isys_virtual_port_group__title"] . "</td>";
            } // if

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_pgData .= '<td>' . $l_f_text->navigation_edit(
                        $this->get_template_component(),
                        [
                            'name'              => 'C__CATG__VSWITCH_PG_VLANID_' . $l_pgCount,
                            'p_strStyle'        => 'width:100%;',
                            'p_strValue'        => $l_row['isys_virtual_port_group__vlanid'],
                            'p_bInfoIconSpacer' => 0
                        ]
                    ) . '</td>';
            }
            else
            {
                $l_pgData .= "<td>" . $l_row["isys_virtual_port_group__vlanid"] . "</td>";
            } // if

            $l_pgData .= '<td><ul style="margin:0 0 0 15px;">';

            $l_clients = $p_cat->get_connected_clients($_GET[C__CMDB__GET__OBJECT], $l_row["isys_virtual_port_group__title"]);
            while ($l_rowClient = $l_clients->get_row())
            {
                $l_title = $l_quickinfo->get_quick_info(
                    $l_rowClient["isys_obj__id"],
                    _L($p_cat->get_objtype_name_by_id_as_string($l_rowClient["isys_obj__isys_obj_type__id"])) . " >> " . $l_rowClient["isys_obj__title"],
                    C__LINK__OBJECT
                );
                $l_pgData .= "<li>" . $l_title . "</li>\n";
            } // while

            $l_pgData .= "</ul></td>"; // TODO: List hosted guest systems

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_pgData .= '<td style="text-align: center"><img src="' . $g_dirs["images"] . 'icons/silk/cross.png" onclick="remove_port_group(' . $l_pgCount . ');" class="mouse-pointer" /></td>';
            } // if

            $l_pgCount++;
        } // while

        // Get Service Console Ports for this Virtual Switch.
        $l_scps     = $p_cat->get_service_console_ports($l_id);
        $l_scpData  = "";
        $l_scpCount = 0;
        while ($l_row = $l_scps->get_row())
        {
            $l_scpData .= "<tr name=\"scp_" . $l_scpCount . "\" id=\"scp_" . $l_scpCount . "\">";
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_scpData .= '<td>' . $l_f_text->navigation_edit(
                        $this->get_template_component(),
                        [
                            'name'              => 'C__CATG__VSWITCH_SCP_NAME_' . $l_scpCount,
                            'p_strStyle'        => 'width:100%;',
                            'p_strValue'        => $l_row['isys_service_console_port__title'],
                            'p_bInfoIconSpacer' => 0
                        ]
                    ) . '</td>';
            }
            else
            {
                $l_scpData .= "<td>" . $l_row["isys_service_console_port__title"] . "</td>";
            } // if

            $l_scpData .= "<td>";

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_scpData .= '<select name="C__CATG__VSWITCH_SCP_ADDRESS_' . $l_scpCount . '" class="input input-small">';
                $l_scpData .= "<option value=\"-1\">-</option>";
                foreach ($l_arIPs as $l_key => $l_ip)
                {
                    $l_scpData .= "<option value=\"" . $l_key . "\"";
                    if ($l_row["isys_service_console_port__isys_catg_ip_list__id"] == $l_key)
                    {
                        $l_scpData .= "selected=\"selected\"";
                    }

                    $l_scpData .= ">";
                    $l_scpData .= $l_ip;
                    $l_scpData .= "</option>";
                } // foreach
                $l_scpData .= "</select>";
            }
            else
            {
                $l_scpData .= $l_arIPs[intval($l_row["isys_service_console_port__isys_catg_ip_list__id"])];
            } // if

            $l_scpData .= "</td>";

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_scpData .= '<td style="text-align: center"><img src="' . $g_dirs['images'] . 'icons/silk/cross.png" onclick="remove_service_console_port(' . $l_scpCount . ');" class="mouse-pointer" /></td>';
            } // if

            $l_scpCount++;
        } // while

        // Get VMKernel Ports for this Virtual Switch.
        $l_vmks = $p_cat->get_vmkernel_ports($l_id);

        $l_vmkData  = "";
        $l_vmkCount = 0;
        while ($l_row = $l_vmks->get_row())
        {
            $l_vmkData .= "<tr name=\"vmk_" . $l_vmkCount . "\" id=\"vmk_" . $l_vmkCount . "\">";
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_vmkData .= '<td>' . $l_f_text->navigation_edit(
                        $this->get_template_component(),
                        [
                            'name'              => 'C__CATG__VSWITCH_VMK_NAME_' . $l_vmkCount,
                            'p_strStyle'        => 'width:100%;',
                            'p_strValue'        => $l_row['isys_vmkernel_port__title'],
                            'p_bInfoIconSpacer' => 0
                        ]
                    ) . '</td>';
            }
            else
            {
                $l_vmkData .= "<td>" . $l_row["isys_vmkernel_port__title"] . "</td>";
            } // if

            $l_vmkData .= "<td>";

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_vmkData .= '<select name="C__CATG__VSWITCH_VMK_ADDRESS_' . $l_vmkCount . '" class="input input-small">';
                $l_vmkData .= "<option value=\"-1\">-</option>";
                foreach ($l_arIPs as $l_key => $l_ip)
                {
                    $l_vmkData .= "<option value=\"" . $l_key . "\"";
                    if ($l_row["isys_vmkernel_port__isys_catg_ip_list__id"] == $l_key)
                    {
                        $l_vmkData .= "selected=\"selected\"";
                    } // if

                    $l_vmkData .= ">";
                    $l_vmkData .= $l_ip;
                    $l_vmkData .= "</option>";
                } // foreach
                $l_vmkData .= "</select>";
            }
            else
            {
                $l_vmkData .= $l_arIPs[intval($l_row["isys_vmkernel_port__isys_catg_ip_list__id"])];
            } // if

            $l_vmkData .= "</td>";

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_vmkData .= '<td style="text-align: center"><img src="' . $g_dirs['images'] . 'icons/silk/cross.png" onclick="remove_vmkernel_port(' . $l_vmkCount . ');" class="mouse-pointer"/></td>';
            } // if

            $l_vmkCount++;
        } // while

        /**
         * Get Network Interfaces
         */
        $l_ports = $p_cat->get_ports($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL);
        if (!empty($l_id))
        {
            $l_connectedPorts = $p_cat->get_connected_ports($l_id);
        }
        else
        {
            $l_connectedPorts = [];
        } // class

        while ($l_row = $l_ports->get_row())
        {
            $l_port_array[] = [
                "id"  => $l_row["isys_catg_port_list__id"],
                "val" => $l_row["isys_catg_port_list__title"],
                "sel" => in_array($l_row["isys_catg_port_list__id"], $l_connectedPorts) ? true : false,
                //"link"	=> "?objID=".C__CMDB__GET__OBJECT."&catgID=".C__CATG__IP."&cateID=".$l_row["isys_catg_ip_list__id"]
            ];
        } // while

        $l_rules = [];

        $l_rules["C__CATG__VSWITCH_PORTS"]["p_arData"]                                                                = serialize($l_port_array);
        $l_rules["C__CATG__VSWITCH_TITLE"]["p_strValue"]                                                              = $l_catdata["isys_catg_virtual_switch_list__title"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
        )]["p_strValue"]                                                                                              = $l_catdata["isys_catg_virtual_switch_list__description"];

        if (!$p_cat->get_validation())
        {
            $l_rules["C__CATG__VSWITCH_TITLE"]["p_strValue"]                                                              = $_POST["LC__UNIVERSAL__TITLE"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        } // if

        // Apply rules
        $this->get_template_component()
            ->assign("ip_list", $l_arIPs)
            ->assign("pg_count", $l_pgs->num_rows())
            ->assign("pg_data", $l_pgData)
            ->assign("scp_count", $l_scps->num_rows())
            ->assign("scp_data", $l_scpData)
            ->assign("vmk_count", $l_vmks->num_rows())
            ->assign("vmk_data", $l_vmkData)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return null
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, "isys_catg_virtual_switch_list__id");
    } // function
} // class