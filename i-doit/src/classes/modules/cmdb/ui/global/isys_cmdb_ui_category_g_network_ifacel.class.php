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
 * CMDB UI: Interface category for Network
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_network_ifacel extends isys_cmdb_ui_category_global
{
    /**
     * @param   integer $p_object_id
     * @param   integer $p_ifacel_id
     *
     * @return  array
     */
    public function get_linklist($p_object_id, $p_ifacel_id)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        if ($p_ifacel_id == null)
        {
            $p_ifacel_id = -1;
        } // if

        // Assign ip addresses.
        $l_ip_dao = new isys_cmdb_dao_category_g_network_ifacel($g_comp_database);
        $l_ips    = $l_ip_dao->get_ips_by_obj_id($p_object_id, false);

        while ($l_row = $l_ips->get_row())
        {
            $l_address = $l_row["isys_cats_net_ip_addresses_list__title"] ? $l_row["isys_cats_net_ip_addresses_list__title"] : $l_row["isys_catg_ip_list__hostname"];

            $l_ip_array[] = [
                "id"   => $l_row["isys_catg_ip_list__id"],
                "val"  => $l_address ? $l_address : $g_comp_template_language_manager->{"LC__IP__EMPTY_ADDRESS"},
                "sel"  => (($l_row['isys_catg_ip_list__isys_catg_log_port_list__id'] == $p_ifacel_id) ? true : false),
                "link" => "?objID=" . C__CMDB__GET__OBJECT . "&catgID=" . C__CATG__IP . "&cateID=" . $l_row["isys_catg_ip_list__id"]
            ];

        } // while

        $l_rules["C__CATG__PORT__IP_ADDRESS"]["p_bLinklist"] = true;
        $l_rules["C__CATG__PORT__IP_ADDRESS"]["p_arData"]    = serialize($l_ip_array);

        return $l_rules["C__CATG__PORT__IP_ADDRESS"];
    } // function

    /**
     * Show the detail-template for interfaces.
     *
     * @param   isys_cmdb_dao_category_g_network_ifacel $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function process(isys_cmdb_dao_category_g_network_ifacel $p_cat)
    {
        $l_id     = null;
        $l_rules  = $l_arPorts = $l_arPortAlloc = [];
        $l_gets   = isys_module_request::get_instance()
            ->get_gets();
        $l_posts  = isys_module_request::get_instance()
            ->get_posts();
        $l_obj_id = $l_gets[C__CMDB__GET__OBJECT];

        $l_catdata = $p_cat->get_general_data();

        if (isset($l_catdata['isys_catg_log_port_list__id']) && $l_catdata['isys_catg_log_port_list__id'] > 0)
        {
            $l_id = $l_catdata['isys_catg_log_port_list__id'];
        } // if

        // Get ip addresses for link list.
        $l_rules["C__CATG__PORT__IP_ADDRESS"] = $this->get_linklist($l_obj_id, $l_id);

        // Assign rules.
        $l_rules["C__CATG__INTERFACE_L__ACTIVE"]["p_arData"]                = get_smarty_arr_YES_NO();
        $l_rules["C__CATG__INTERFACE_L__SETTING_ALLOCATION"]["p_bLinklist"] = true;
        $l_rules["C__CATG__INTERFACE_L__PORT_ALLOCATION"]["p_bLinklist"]    = true;
        $l_rules["C__CATG__INTERFACE_L__TYPE"]["p_strTable"]                = "isys_netx_ifacel_type";
        $l_rules["C__CATG__INTERFACE_L__STANDARD"]["p_strTable"]            = "isys_netp_ifacel_standard";
        $l_rules['C__CATG__INTERFACE_L__PARENT']['p_arData']                = $p_cat->callback_property_parent(
            isys_request::factory()
                ->set_object_id($l_obj_id)
                ->set_category_data_id($l_id)
        );

        // If the id is known, assign correct interface data.
        if ($l_id > 0)
        {
            // Retrieve interface data.
            $l_arPorts = $p_cat->get_ports_for_ifacel($l_id);

            $l_rules["C__CATG__INTERFACE_L__DEST"]["p_strValue"]        = $l_catdata['isys_catg_log_port_list__isys_catg_log_port_list__id'];
            $l_rules["C__CATG__INTERFACE_L__TITLE"]["p_strValue"]       = $l_catdata["isys_catg_log_port_list__title"];
            $l_rules["C__CATG__INTERFACE_L__NET"]["p_strValue"]         = $l_catdata["isys_connection__isys_obj__id"];
            $l_rules["C__CATG__INTERFACE_L__MAC"]["p_strValue"]         = $l_catdata["isys_catg_log_port_list__mac"];
            $l_rules["C__CATG__INTERFACE_L__PARENT"]["p_strSelectedID"] = $l_catdata["isys_catg_log_port_list__parent"];

            // DS: Filter Net Objects.
            $l_rules["C__CATG__INTERFACE_L__TYPE"]["p_strSelectedID"]                                                     = $l_catdata["isys_catg_log_port_list__isys_netx_ifacel_type__id"];
            $l_rules["C__CATG__INTERFACE_L__STANDARD"]["p_strSelectedID"]                                                 = $l_catdata["isys_catg_log_port_list__isys_netp_ifacel_standard__id"];
            $l_rules["C__CATG__INTERFACE_L__ACTIVE"]["p_strSelectedID"]                                                   = $l_catdata["isys_catg_log_port_list__active"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_log_port_list__description"];
            $l_rules["C__CATG__INTERFACE_L__NET"]["p_strSelectedID"]                                                      = "[" . $p_cat->get_attached_layer_2_net(
                    $l_catdata['isys_catg_log_port_list__id'],
                    null,
                    true
                ) . "]";
        }
        else
        {
            $l_rules["C__CATG__INTERFACE_L__ACTIVE"]["p_strSelectedID"] = 1;
        } // if

        if (!$p_cat->get_validation())
        {
            $l_rules["C__CATG__INTERFACE_L__TITLE"]["p_strValue"]                                                         = $l_posts["C__CATG__INTERFACE_L__TITLE"];
            $l_rules["C__CATG__INTERFACE_L__NET"]["p_strSelectedID"]                                                      = $l_posts["C__CATG__INTERFACE_L__NET"];
            $l_rules["C__CATG__INTERFACE_L__TYPE"]["p_strSelectedID"]                                                     = $l_posts["C__CATG__INTERFACE_L__TYPE"];
            $l_rules["C__CATG__INTERFACE_L__STANDARD"]["p_strSelectedID"]                                                 = $l_posts["C__CATG__INTERFACE_L__STANDARD"];
            $l_rules["C__CATG__INTERFACE_L__ACTIVE"]["p_strSelectedID"]                                                   = $l_posts["C__CATG__INTERFACE_L__ACTIVE"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_posts["C__CMDB__CAT__COMMENTARY"];
            $l_rules["C__CATG__INTERFACE_L__MAC"]["p_strValue"]                                                           = $l_posts["C__CATG__INTERFACE_L__MAC"];
            $l_rules["C__CATG__INTERFACE_L__PARENT"]["p_strSelectedID"]                                                   = $l_posts["C__CATG__INTERFACE_L__PARENT"];

            $l_strSelVal_Ports = $l_posts["C__CATG__INTERFACE_L__PORT_ALLOCATION__selected_values"];
            $l_arSelPorts      = explode(",", $l_strSelVal_Ports);
            $l_arPorts         = array_flip($l_arSelPorts);

            $this->get_template_component()
                ->assign("navMode", C__NAVMODE__EDIT);

            $l_gets["editMode"] = "1";
            isys_module_request::get_instance()
                ->_internal_set_private("m_get", $l_gets);

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        } // if

        // Get all ports.
        $l_arPortsAll = [];
        $l_dao_port   = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component());
        $l_ports      = $l_dao_port->get_data(null, $l_obj_id);

        while ($l_port = $l_ports->get_row())
        {
            $l_arPortsAll[$l_port["isys_catg_port_list__id"]] = $l_port["isys_catg_port_list__title"];
        } // while

        // Merge the 2 arrays for the dialogue list.
        if (count($l_arPortsAll) > 0)
        {
            $l_check_selection = (count($l_arPorts) > 0);

            foreach ($l_arPortsAll as $key => $val)
            {
                $l_arPortAlloc[] = [
                    "id"  => $key,
                    "val" => $val,
                    "sel" => ($l_check_selection ? array_key_exists($key, $l_arPorts) : false)
                ];
            } // foreach

            $l_rules['C__CATG__INTERFACE_L__PORT_ALLOCATION']['p_arData'] = serialize($l_arPortAlloc);
        } // if

        $this->get_template_component()
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=0")
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', $this->get_template());
    } // function

    /**
     * Processes category data list for multi-valued categories.
     *
     * @param   isys_cmdb_dao_category $p_cat Category's DAO
     * @param   array                  $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_stack_ports = [];
        $l_gets        = isys_module_request::get_instance()
            ->get_gets();
        $l_obj_id      = $l_gets[C__CMDB__GET__OBJECT];
        $l_return      = parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
        $l_list_dao    = $p_cat->get_category_list();

        if (!class_exists($l_list_dao))
        {
            if (empty($l_list_dao))
            {
                throw new isys_exception_general('List class empty for "' . get_class($this) . '".');
            }
            else
            {
                throw new isys_exception_general('List class "' . $l_list_dao . '" does not exist.');
            } // if
        } // if

        /* @var  $l_list_dao  isys_cmdb_dao_list_catg_network_ifacel */
        $l_list_dao = new $l_list_dao($p_cat);

        $l_stacking_dao = isys_cmdb_dao_category_g_stack_member::instance($this->m_database_component);

        $l_stack_res = $l_stacking_dao->get_stacking_meta($l_obj_id);

        if (count($l_stack_res))
        {
            while ($l_stack_row = $l_stack_res->get_row())
            {
                // Here we retrieve the meta "stacking" object.
                $l_stack_object = $l_stack_row['isys_obj__id'];

                // Now we fetch all stack members to then iterate over all logical ports.
                $l_members_res = $l_stacking_dao->get_connected_objects($l_stack_object);
                $l_key         = _L($l_stack_row['isys_obj_type__title']) . ' &raquo; ' . $l_stack_row['isys_obj__title'] . ' (#' . $l_stack_object . ')';

                if (!isset($l_stack_ports[$l_key]))
                {
                    $l_stack_ports[$l_key] = [];
                } // if

                while ($l_member_row = $l_members_res->get_row())
                {
                    if ($l_member_row['isys_catg_stack_member_list__stack_member'] == $l_obj_id)
                    {
                        // Skip, if we found the current object itself.
                        continue;
                    } // if

                    $l_log_port_res = $p_cat->get_data(null, $l_member_row['isys_catg_stack_member_list__stack_member'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_log_port_row = $l_log_port_res->get_row())
                    {
                        $l_list_dao->modify_row($l_log_port_row);

                        $l_stack_ports[$l_key][] = [
                            'title'                 => _L(
                                    $l_log_port_row['isys_obj_type__title']
                                ) . ' &raquo; ' . $l_log_port_row['isys_obj__title'] . ' &raquo; ' . $l_log_port_row['isys_catg_log_port_list__title'],
                            'type'                  => $l_log_port_row['isys_netx_ifacel_type__title'],
                            'ip_address'            => $l_log_port_row['isys_cats_net_ip_addresses_list__title'],
                            'layer2_net_assignment' => $l_log_port_row['object_connection'],
                            'destination'           => $l_log_port_row['attached_log_port']
                        ];
                    } // while
                } // while
            } // while

            $l_table_content = $this->get_template_component()
                ->assign('stack_ports', $l_stack_ports)
                ->fetch('content/bottom/content/catg__interface_l_list.tpl');

            $this->get_template_component()
                ->assign('additional_object_table_data', $l_table_content);
        } // if

        return $l_return;
    } // function
} // class