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
 * UI: global category for monitoring.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_cmdb_ui_category_g_monitoring extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the monitoring category.
     *
     * @param   isys_cmdb_dao_category_g_monitoring $p_cat The corresponding category DAO
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_obj_id  = $_GET[C__CMDB__GET__OBJECT];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_dns_domain = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_assigned_dns_domain($l_obj_id)
            ->get_row_value('isys_net_dns_domain__title');

        if ($l_dns_domain !== null && !empty($l_dns_domain))
        {
            $l_dns_domain = '.' . $l_dns_domain;
        } // if

        // Loading some special rules.
        $l_monitoring_hosts                               = $p_cat->callback_property_monitoring_host(isys_request::factory());
        $l_rules['C__CATG__MONITORING__HOST']['p_arData'] = serialize($l_monitoring_hosts);

        if (count($l_monitoring_hosts) === 1)
        {
            $l_rules['C__CATG__MONITORING__HOST']['p_strSelectedID'] = current(array_keys($l_monitoring_hosts));
        } // if

        // Preparing the host-name selection
        $l_prim_ip = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_primary_ip($l_obj_id)
            ->get_row();

        $l_hostname_selection = ($l_catdata['isys_catg_monitoring_list__host_name_selection'] === null) ? C__MONITORING__NAME_SELECTION__HOSTNAME : $l_catdata['isys_catg_monitoring_list__host_name_selection'];

        $this->get_template_component()
            ->assign('hostname_obj_title', isys_monitoring_helper::prepare_valid_name($p_cat->get_obj_name_by_id_as_string($l_obj_id)))
            ->assign('hostname_hostname', $l_prim_ip['isys_catg_ip_list__hostname'])
            ->assign('hostname_hostname_fqdn', $l_prim_ip['isys_catg_ip_list__hostname'] . $l_dns_domain)
            ->assign('host_name_view', isys_monitoring_helper::render_export_hostname($l_obj_id))
            ->assign('host_name_selection', $l_hostname_selection)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class