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
 * CMDB Nagios
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_nagios extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the category nagios
     *
     * @param  isys_cmdb_dao_category_g_nagios $p_cat The corresponding category DAO
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_gets      = isys_module_request::get_instance()
            ->get_gets();
        $l_object_id = $l_gets[C__CMDB__GET__OBJECT];

        $l_catdata        = $p_cat->get_general_data();
        $l_comp_daoNagios = new isys_component_dao_nagios($this->get_database_component());
        $l_daoIP          = new isys_cmdb_dao_category_g_ip($this->get_database_component());

        $l_ips       = $l_daoIP->get_ips_by_obj_id($l_object_id);
        $l_ipArrData = [
            0 => _L('LC__CATG__NAGIOS__PRIMARY_ADDRESS')
        ];

        while ($l_row = $l_ips->get_row())
        {
            $l_prim     = ($l_row['isys_catg_ip_list__primary'] == '1') ? ' (' . _L('LC__CATP__IP__PRIMARY') . ')' : '';
            $l_hostname = trim($l_row["isys_catg_ip_list__hostname"]);
            $l_option   = [];

            if (!empty($l_row["isys_cats_net_ip_addresses_list__title"]))
            {
                $l_option[] = $l_row["isys_cats_net_ip_addresses_list__title"];
            } // if

            if (!empty($l_hostname))
            {
                $l_option[] = $l_hostname;

                $l_dns_domain = trim(
                    $l_daoIP->get_assigned_dns_domain($l_object_id, $l_row["isys_catg_ip_list__id"])
                        ->get_row_value('isys_net_dns_domain__title')
                );

                if (!empty($l_dns_domain))
                {
                    $l_option[] = $l_hostname . $l_dns_domain;
                } // if
            } // if

            $l_ipArrData[$l_row["isys_catg_ip_list__id"]] = implode(' / ', $l_option) . $l_prim;
        } // while

        $l_he    = $l_comp_daoNagios->getHostEscalationsAssoc();
        $l_assHe = explode(",", $l_catdata["isys_catg_nagios_list__escalations"]);
        foreach ($l_he as $key => $val)
        {
            $l_heArr[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => (int) in_array($key, $l_assHe),
                "url" => ""
            ];
        } // foreach

        $l_fd    = $l_comp_daoNagios->getHostFlapDetectionOptionsAssoc();
        $l_assFd = explode(",", $l_catdata["isys_catg_nagios_list__flap_detection_options"]);
        foreach ($l_fd as $key => $val)
        {
            $l_fdArr[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => (int) in_array($key, $l_assFd),
                "url" => ""
            ];
        } // foreach

        $l_sn    = $l_comp_daoNagios->getHostNotificationOptionsAssoc();
        $l_assSn = explode(",", $l_catdata["isys_catg_nagios_list__notification_options"]);
        foreach ($l_sn as $key => $val)
        {
            $l_snArr[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => (int) in_array($key, $l_assSn),
                "url" => ""
            ];
        } // foreach

        $l_so    = $l_comp_daoNagios->getHostFlapDetectionOptionsAssoc();
        $l_assSo = explode(",", $l_catdata["isys_catg_nagios_list__stalking_options"]);
        foreach ($l_so as $key => $val)
        {
            $l_soArr[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => (int) in_array($key, $l_assSo),
                "url" => ""
            ];
        } // foreach

        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Adding specific "p_arData" values...
        $l_dialog_yes_no                                               = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__CATG__NAGIOS_IS_ACTIVE"]["p_arData"]              = $l_dialog_yes_no;
        $l_rules["C__CATG__NAGIOS_IP"]["p_arData"]                     = serialize($l_ipArrData);
        $l_rules["C__CATG__NAGIOS_NOTIFICATION_OPTIONS"]["p_arData"]   = serialize($l_snArr);
        $l_rules["C__CATG__NAGIOS_FLAP_DETECTION_OPTIONS"]["p_arData"] = serialize($l_fdArr);
        $l_rules["C__CATG__NAGIOS_STALKING_OPTIONS"]["p_arData"]       = serialize($l_soArr);
        $l_rules["C__CATG__NAGIOS_ESCALATIONS"]["p_arData"]            = serialize($l_heArr);

        // Newly added stuff.
        $l_2d_coords                                          = explode(',', $l_catdata["isys_catg_nagios_list__2d_coords"]);
        $l_rules["C__CATG__NAGIOS_2D_COORDS_X"]["p_strValue"] = $l_2d_coords[0];
        $l_rules["C__CATG__NAGIOS_2D_COORDS_Y"]["p_strValue"] = $l_2d_coords[1];

        $l_3d_coords                                          = explode(',', $l_catdata["isys_catg_nagios_list__3d_coords"]);
        $l_rules["C__CATG__NAGIOS_3D_COORDS_X"]["p_strValue"] = $l_3d_coords[0];
        $l_rules["C__CATG__NAGIOS_3D_COORDS_Y"]["p_strValue"] = $l_3d_coords[1];
        $l_rules["C__CATG__NAGIOS_3D_COORDS_Z"]["p_strValue"] = $l_3d_coords[2];

        if ($l_catdata == null)
        {
            // Add some "default" values, when no data is available.
            $l_rules["C__CATG__NAGIOS_ALIAS"]["p_strValue"]     = isys_cmdb_dao::factory($this->get_database_component())
                ->get_obj_name_by_id_as_string($l_object_id);
            $l_rules["C__CATG__NAGIOS_NOTES_URL"]["p_strValue"] = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_object_id], true);
        } // if

        $l_display_name_view = $l_catdata["isys_catg_nagios_list__display_name"];

        switch ($l_catdata["isys_catg_nagios_list__display_name_selection"])
        {
            case C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME:
                $l_hostaddress       = isys_cmdb_dao_category_g_ip::instance($this->get_database_component())
                    ->get_ips_by_obj_id($l_object_id, true)
                    ->get_row();
                $l_display_name_view = _L('LC__CATP__IP__HOSTNAME') . ' ("' . $l_hostaddress['isys_catg_ip_list__hostname'] . '")';
                break;

            case C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID:
                $l_display_name_view = _L('LC__UNIVERSAL__OBJECT_TITLE') . ' ("' . $l_catdata['isys_obj__title'] . '")';
                break;
        } // switch

        $l_host_name_view = isys_nagios_helper::render_export_hostname($l_object_id);

        $l_res_contacts = isys_factory::get_instance('isys_cmdb_dao_category_g_contact', $this->get_database_component())
            ->get_contact_objects_by_tag($l_object_id, C__CONTACT_TYPE__NAGIOS);

        if (count($l_res_contacts) > 0)
        {
            while ($l_row = $l_res_contacts->get_row())
            {
                $l_contacts_arr[] = $l_row['isys_obj__id'];
            } // while
        } // if

        $l_rules['C__CATG__NAGIOS_PARENTS']['catFilter']         = 'C__CATG__NAGIOS_HOST_FOLDER;C__CATG__NAGIOS';
        $l_rules['C__CATG__NAGIOS_TEMPLATES']['p_strSelectedID'] = $l_catdata['isys_catg_nagios_list__host_tpl'];
        $l_rules['C__CATG__NAGIOS_TEMPLATES']['catFilter']       = 'C__CATG__NAGIOS_HOST_TPL_FOLDER;C__CATG__NAGIOS_HOST_TPL_DEF';
        $l_rules['C__CATG__NAGIOS_CONTACTS']['p_strSelectedID']  = isys_format_json::encode($l_contacts_arr);

        // Loading some special rules.
        $l_export_configuration                             = $p_cat->callback_property_export_config(isys_request::factory());
        $l_rules['C__CATG__NAGIOS_EXPORT_HOST']['p_arData'] = serialize($l_export_configuration);

        if (count($l_export_configuration) === 1)
        {
            $l_rules['C__CATG__NAGIOS_EXPORT_HOST']['p_strSelectedID'] = current(array_keys($l_export_configuration));
        } // if

        $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());

        // Preparing the host-name selection
        $l_prim_ip = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_primary_ip($l_object_id)
            ->get_row();

        $l_dns_domain = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_assigned_dns_domain($l_object_id)
            ->get_row_value('isys_net_dns_domain__title');

        if ($l_dns_domain !== null && !empty($l_dns_domain))
        {
            $l_dns_domain = '.' . $l_dns_domain;
        } // if

        $l_address_view = isys_nagios_helper::render_export_address($l_object_id);

        $this->get_template_component()
            ->assign('hostname_obj_title', isys_monitoring_helper::prepare_valid_name($p_cat->get_obj_name_by_id_as_string($l_object_id)))
            ->assign('hostname_hostname', trim($l_prim_ip['isys_catg_ip_list__hostname']))
            ->assign('hostname_hostname_fqdn', trim($l_prim_ip['isys_catg_ip_list__hostname']) . $l_dns_domain)// Assigning the data for the "comment popup".
            ->assign('check_command_value', $l_rules["C__CATG__NAGIOS_CHECK_COMMAND"]['p_strSelectedID'])
            ->assign('event_handler_value', $l_rules["C__CATG__NAGIOS_EVENT_HANDLER"]['p_strSelectedID'])// Assigning other data...
            ->assign('parents', $l_comp_daoNagios->getParents($l_catdata["isys_obj__id"]))
            ->assign('host_name_view', $l_host_name_view)
            ->assign(
                'host_name_selection',
                (!is_numeric(
                    $l_catdata["isys_catg_nagios_list__host_name_selection"]
                )) ? C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME : $l_catdata["isys_catg_nagios_list__host_name_selection"]
            )
            ->assign('address_view', $l_address_view)
            ->assign(
                'address_selection',
                (!is_numeric(
                    $l_catdata["isys_catg_nagios_list__address_selection"]
                )) ? C__CATG_NAGIOS__NAME_SELECTION__IP : $l_catdata["isys_catg_nagios_list__address_selection"]
            )
            ->assign('display_name_view', $l_display_name_view)
            ->assign(
                'display_name_selection',
                (!is_numeric(
                    $l_catdata["isys_catg_nagios_list__display_name_selection"]
                )) ? C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID : $l_catdata["isys_catg_nagios_list__display_name_selection"]
            )
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class