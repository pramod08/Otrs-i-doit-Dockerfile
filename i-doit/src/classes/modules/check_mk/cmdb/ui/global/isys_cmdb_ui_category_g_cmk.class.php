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
 * UI: global category for Check_MK.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_ui_category_g_cmk extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the category check_mk.
     *
     * @param   isys_cmdb_dao_category_g_cmk $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  void
     */
    public function process(isys_cmdb_dao_category_g_cmk $p_cat)
    {
        $l_rules   = [];
        $l_obj_id  = $_GET[C__CMDB__GET__OBJECT];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Loading some special rules.
        $l_export_configuration                             = $p_cat->callback_property_export_config(isys_request::factory());
        $l_rules['C__CATG__CMK__EXPORT_CONFIG']['p_arData'] = serialize($l_export_configuration);

        if (count($l_export_configuration) === 1)
        {
            $l_rules['C__CATG__CMK__EXPORT_CONFIG']['p_strSelectedID'] = current(array_keys($l_export_configuration));
        } // if

        // Preparing the host-name selection
        $l_prim_ip = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_primary_ip($l_obj_id)
            ->get_row();

        $l_dns_domain = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_assigned_dns_domain($l_obj_id)
            ->get_row_value('isys_net_dns_domain__title');

        if ($l_dns_domain !== null && !empty($l_dns_domain))
        {
            $l_dns_domain = '.' . $l_dns_domain;
        } // if

        $l_hostname_selection = ($l_catdata['isys_catg_cmk_list__host_name_selection'] === null) ? C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME : $l_catdata['isys_catg_cmk_list__host_name_selection'];

        $this->get_template_component()
            ->assign('hostname_obj_title', isys_check_mk_helper::prepare_valid_name($p_cat->get_obj_name_by_id_as_string($l_obj_id)))
            ->assign('hostname_hostname', trim($l_prim_ip['isys_catg_ip_list__hostname']))
            ->assign('hostname_hostname_fqdn', trim($l_prim_ip['isys_catg_ip_list__hostname']) . $l_dns_domain)
            ->assign('host_name_view', isys_check_mk_helper::render_export_hostname($l_obj_id))
            ->assign('host_name_selection', $l_hostname_selection)
            ->assign('export_ip', !!$l_catdata['isys_catg_cmk_list__export_ip'])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', isys_module_check_mk::get_tpl_dir() . 'modules/cmdb/catg__cmk.tpl');
    } // function
} // class