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
 * CMDB UI: Nagios Service Definition.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_nagios_service_def extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories of application.
     *
     * @param   isys_cmdb_dao_category_g_nagios_service_def $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        // This is necessary to load the class once, because only there we've got the "C__CATG_NAGIOS__NAME_SELECTION..." constants
        isys_cmdb_dao_category_g_nagios::instance($this->get_database_component());
        $l_comp_daoNagios = new isys_component_dao_nagios($this->m_database_component);

        $l_dialog_yes_no                                                                  = serialize(get_smarty_arr_YES_NO());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__IS_EXPORTABLE']['p_arData']                = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__ACTIVE_CHECKS_ENABLED']['p_arData']        = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__PASSIVE_CHECKS_ENABLED']['p_arData']       = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__IS_ACTIVE']['p_arData']                    = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATIONS_ENABLED']['p_arData']        = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__FLAP_DETECTION_ENABLED']['p_arData']       = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__IS_VOLATILE']['p_arData']                  = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__OBSESS_OVER_SERVICE']['p_arData']          = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__CHECK_FRESHNESS']['p_arData']              = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER_ENABLED']['p_arData']        = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__PROCESS_PERF_DATA']['p_arData']            = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__RETAIN_STATUS_INFORMATION']['p_arData']    = $l_dialog_yes_no;
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__RETAIN_NONSTATUS_INFORMATION']['p_arData'] = $l_dialog_yes_no;

        // Creating an request-instance for the callback methods.
        $l_request = isys_request::factory()
            ->set_category_data_id($l_catdata['isys_catg_nagios_service_def_list__id'])
            ->set_object_id($l_catdata['isys_catg_nagios_service_def_list__isys_obj__id']);

        // Fill 'p_arData' with content...
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__ESCALATIONS']['p_arData']            = serialize($p_cat->callback_property_escalations($l_request));
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_OPTIONS']['p_arData']   = serialize($p_cat->callback_property_notification_option($l_request));
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__FLAP_DETECTION_OPTIONS']['p_arData'] = serialize($p_cat->callback_property_flap_detection_options($l_request));
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__STALKING_OPTIONS']['p_arData']       = serialize($p_cat->callback_property_stalking_options($l_request));
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__INITIAL_STATE']['p_arData']          = serialize($l_comp_daoNagios->getServiceFlapDetectionOptionsAssoc());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND']['p_arData']          = serialize($l_comp_daoNagios->getCommandsAssoc());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER']['p_arData']          = serialize($l_comp_daoNagios->getCommandsAssoc());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD']['p_arData']           = serialize($l_comp_daoNagios->getTimeperiodsAssoc());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_PERIOD']['p_arData']    = $l_rules['C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD']['p_arData'];

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        if ($l_catdata === null)
        {
            $l_rules["C__CATG__NAGIOS_SERVICE_DEF__NOTES_URL"]["p_strValue"]          = isys_helper_link::create_url(
                [C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT]],
                true
            );
            $l_rules['C__CATG__NAGIOS_SERVICE_DEF__IS_EXPORTABLE']['p_strSelectedID'] = 1;
        } // if

        if ($l_catdata['isys_catg_nagios_service_def_list__is_exportable'] == null)
        {
            $l_rules['C__CATG__NAGIOS_SERVICE_DEF__IS_EXPORTABLE']['p_strSelectedID'] = 1;
        } // if

        $l_display_name_view = $l_catdata['isys_catg_nagios_service_def_list__display_name'];

        if ($l_catdata['isys_catg_nagios_service_def_list__display_name_selection'] == C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID)
        {
            $l_display_name_view = _L('LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION') . ' ("' . $l_catdata['isys_obj__title'] . '")';
        } // switch

        $this->get_template_component()// Assigning the data for the "comment popup".
        ->assign('check_command_value', $l_rules["C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND"]['p_strSelectedID'])
            ->assign('event_handler_value', $l_rules["C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER"]['p_strSelectedID'])// Assigning other data...
            ->assign('display_name_view', $l_display_name_view)
            ->assign('display_name_selection', $l_catdata['isys_catg_nagios_service_def_list__display_name_selection'])
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    } // function
} // class
?>