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
class isys_cmdb_ui_category_g_nagios_host_tpl_def extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the category nagios
     *
     * @param  isys_cmdb_dao_category_g_nagios_host_tpl_def $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        // This will fetch a lot of UI specific parameters from the DAO.
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__NAGIOS_HOST_TPL_DEF_HOST"]["p_arData"] = serialize(
            $p_cat->callback_property_general_dialog_nagios_methods(isys_request::factory(), 'getNagiosHostsAssoc')
        );

        if ($l_catdata["isys_catg_nagios_host_tpl_def_list__id"] != null)
        {
            $this->fill_formfields($p_cat, $l_rules, $l_catdata);

            // Sadly we need to iterate over the dialog_lists by ourself, because the DAO method is a bit too complicated for "fill_formfields()".
            if (isset($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_OPTIONS']['p_arData']) && is_array(
                    $l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_OPTIONS']['p_arData']
                )
            )
            {
                $l_options = explode(',', $l_catdata['isys_catg_nagios_host_tpl_def_list__notification_options']);
                foreach ($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_OPTIONS']['p_arData'] as &$l_data)
                {
                    $l_data['sel'] = (in_array($l_data['id'], $l_options));
                } // if
            } // if

            if (isset($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_OPTIONS']['p_arData']) && is_array(
                    $l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_OPTIONS']['p_arData']
                )
            )
            {
                $l_options = explode(',', $l_catdata['isys_catg_nagios_host_tpl_def_list__flap_detection_options']);
                foreach ($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_OPTIONS']['p_arData'] as &$l_data)
                {
                    $l_data['sel'] = (in_array($l_data['id'], $l_options));
                } // if
            } // if

            if (isset($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_STALKING_OPTIONS']['p_arData']) && is_array(
                    $l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_STALKING_OPTIONS']['p_arData']
                )
            )
            {
                $l_options = explode(',', $l_catdata['isys_catg_nagios_host_tpl_def_list__stalking_options']);
                foreach ($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_STALKING_OPTIONS']['p_arData'] as &$l_data)
                {
                    $l_data['sel'] = (in_array($l_data['id'], $l_options));
                } // if
            } // if

            if (isset($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_ESCALATIONS']['p_arData']) && is_array($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_ESCALATIONS']['p_arData']))
            {
                $l_options = explode(',', $l_catdata['isys_catg_nagios_host_tpl_def_list__escalations']);
                foreach ($l_rules['C__CATG__NAGIOS_HOST_TPL_DEF_ESCALATIONS']['p_arData'] as &$l_data)
                {
                    $l_data['sel'] = (in_array($l_data['id'], $l_options));
                } // if
            } // if

            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"] = $l_catdata["isys_catg_nagios_host_tpl_def_list__description"];
        } // if

        $l_display_name_view = $l_catdata["isys_catg_nagios_host_tpl_def_list__display_name"];

        switch ($l_catdata["isys_catg_nagios_host_tpl_def_list__display_name_selection"])
        {
            case C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME:
                $l_hostaddress       = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
                    ->get_ips_by_obj_id($_GET[C__CMDB__GET__OBJECT], true)
                    ->get_row();
                $l_display_name_view = _L('LC__CATP__IP__HOSTNAME') . ' ("' . $l_hostaddress['isys_catg_ip_list__hostname'] . '")';
                break;

            case C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID:
                $l_display_name_view = _L('LC__UNIVERSAL__OBJECT_TITLE') . ' ("' . $l_catdata['isys_obj__title'] . '")';
                break;
        } // switch

        $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());

        $this->get_template_component()// Assigning the data for the "comment popup".
        ->assign('check_command_value', $l_rules["C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND"]['p_strSelectedID'])
            ->assign('event_handler_value', $l_rules["C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER"]['p_strSelectedID'])// Assigning other data...
            ->assign('display_name_view', $l_display_name_view)
            ->assign('display_name_selection', $l_catdata["isys_catg_nagios_host_tpl_def_list__display_name_selection"])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class