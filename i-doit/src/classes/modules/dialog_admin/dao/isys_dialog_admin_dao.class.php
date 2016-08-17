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
 * Dialog admin module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 */
class isys_dialog_admin_dao extends isys_module_dao
{
    /**
     * Standard set of tables, which will not be found by $this->get_tables().
     *
     * @var  array
     */
    private $m_dialog_tables = [
        'isys_catg_global_category',
        'isys_cats_prt_emulation',
        'isys_purpose',
        'isys_p_mode',
        'isys_wlan_auth',
        'isys_wlan_channel',
        'isys_wlan_encryption',
        'isys_wlan_function',
        'isys_wlan_standard',
        'isys_cats_prt_paper',
        'isys_file_category',
        'isys_ip_assignment',
        'isys_model_title',
        'isys_ui_plugtype',
        'isys_catg_cpu_frequency',
        'isys_account',
        'isys_contract_status',
        'isys_workflow_category',
        'isys_maintenance_status',
        'isys_contract_reaction_rate',
        'isys_logbook_reason',
        'isys_nagios_timeperiods_plus',
        'isys_dbms',
        'isys_ldev_multipath',
        'isys_unit_of_time',
        'isys_wan_role',
        'isys_maintenance_reaction_rate',
        'isys_replication_mechanism',
        'isys_monitor_resolution',
        'isys_nagios_commands_plus',
        'isys_network_provider',
        'isys_telephone_rate',
        'isys_ldap_directory',
        'isys_net_dns_domain',
        'isys_database_objects',
        'isys_backup_cycle',
        'isys_voip_phone_button_template',
        'isys_voip_phone_softkey_template',
        'isys_interval',
        'isys_layer2_net_subtype',
        'isys_snmp_community',
        'isys_chassis_role',
        'isys_obj_type_group',
        'isys_vlan_management_protocol',
        'isys_switch_role',
        'isys_switch_spanning_tree',
        'isys_routing_protocol',
        'isys_port_speed',
        'isys_role',
        'isys_currency',
        'isys_fc_port_medium',
        'isys_net_dns_server',
        'isys_port_duplex',
        'isys_port_negotiation',
        'isys_port_mode',
        'isys_stor_raid_level',
        'isys_contract_payment_period',
        'isys_net_protocol',
        'isys_net_protocol_layer_5',
        'isys_sla_service_level',
        'isys_memory_title',
        'isys_tierclass',
        'isys_calendar',
        'isys_interface'
    ];
    /**
     * Dialog tables to be skipped, they do not work properly or shall not be displayed as "Dialog+" table.
     *
     * @var  array
     */
    private $m_skip = [
        'isys_obj_type',
        'isys_wan_capacity_unit',
        // Primary key there has wrong name!
        'isys_workflow_type',
        'isys_monitor_unit',
        // No longer used.
        'isys_pobj_type',
        // No longer used.
        'isys_catg_model',
        'isys_workflow_action_type',
        'isys_source_type',
        'isys_port_standard',
        'isys_unit',
        'isys_jdisc_ca_type',
        'isys_notification_type',
        'isys_organisation_intern_iop',
        'isys_file_group',
        // No longer used.
        'viva_application_information_type',
        // Should not be editable
        'viva_information_type',
        // Should not be editable
        'isys_contact_tag',
        // Use administration tool
        'isys_relation_type',
        // Use administration tool
        'isys_catg_application_type',
        // This is NO dialog+ field
        'isys_jdisc_device_type',
        // This is NO dialog+ field
    ];

    /**
     * Unused method.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data()
    {
        ;
    } // function

    /**
     * Retrieves all common dialog tables.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_dialog_tables()
    {
        return $this->load_tables('%_unit')
            ->load_tables('%_type')
            ->load_tables('%_manufacturer')
            ->load_tables('%_model')
            ->load_additional_tables()->m_dialog_tables;
    } // function

    /**
     * Retrieves all dialog titles + identifier of custom category Dialog+ fields.
     *
     * @return  array
     */
    public function get_custom_dialogs()
    {
        $l_return = [];

        $l_res = $this->retrieve('SELECT isysgui_catg_custom__config FROM isysgui_catg_custom;');

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_config = unserialize($l_row['isysgui_catg_custom__config']);

                if (is_array($l_config) && count($l_config) > 0)
                {
                    foreach ($l_config as $l_field)
                    {
                        if ($l_field['type'] == 'f_popup' && $l_field['popup'] == 'dialog_plus')
                        {
                            $l_return[] = [
                                'title'      => $l_field['title'],
                                'identifier' => $l_field['identifier']
                            ];
                        } // if
                    } // foreach
                } // if
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Returns the found table-names by a given "filter"-string.
     *
     * @param   string $p_filter
     *
     * @return  isys_dialog_admin_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_tables($p_filter)
    {
        $l_res = $this->retrieve('SHOW TABLES LIKE ' . $this->convert_sql_text($p_filter) . ';');

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_table_name = current($l_row);

                if (!in_array($l_table_name, $this->m_skip) && !in_array($l_table_name, $this->m_dialog_tables))
                {
                    $this->m_dialog_tables[] = $l_table_name;
                } // if
            } // while
        } // if

        return $this;
    } // function

    /**
     * This method can be used to add "non-core" (module) tables to the dialog admin.
     *
     * @return  isys_dialog_admin_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_additional_tables()
    {
        $this->m_dialog_tables = array_merge(
            $this->m_dialog_tables,
            array_keys(
                isys_register::factory('additional-dialog-admin-tables')
                    ->get()
            )
        );

        return $this;
    } // function
} // class