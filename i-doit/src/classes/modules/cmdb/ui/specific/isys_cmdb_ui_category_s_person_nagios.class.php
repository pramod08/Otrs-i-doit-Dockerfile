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
 * CMDB Person: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_person_nagios extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_person_nagios $p_cat
     *
     * @global  array                                  $index_includes
     * @global  isys_component_template                $g_comp_template
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW);

        isys_nagios_helper::init();

        $l_catdata = $p_cat->get_general_data();

        $l_comp_daoNagios = isys_factory::get_instance('isys_component_dao_nagios', $p_cat->get_database_component());

        $l_hostOptArr = $l_serviceOptArr = $l_hostNotOptArr = $l_serviceNotOptArr = [];

        $l_opt = $l_comp_daoNagios->getHostNotificationOptionsAssoc();

        foreach ($l_opt as $key => $val)
        {
            $l_hostOptArr[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => (int) in_array($key, explode(",", $l_catdata["isys_cats_person_nagios_list__host_notification_options"])),
                "url" => ""
            ];
        } // foreach

        $l_opt = $l_comp_daoNagios->getServiceNotificationOptionsAssoc();

        foreach ($l_opt as $key => $val)
        {
            $l_serviceOptArr[] = [
                "id"  => $key,
                "val" => $val,
                "sel" => (int) in_array($key, explode(",", $l_catdata["isys_cats_person_nagios_list__service_notification_options"])),
                "url" => ""
            ];
        } // foreach

        $l_commands = $l_comp_daoNagios->getCommandsAssoc();

        if ($l_catdata["isys_cats_person_nagios_list__host_notification_commands"] != null)
        {
            $l_assCommands = explode(",", $l_catdata["isys_cats_person_nagios_list__host_notification_commands"]);

            foreach ($l_assCommands as $val)
            {
                $l_command = $l_comp_daoNagios->getCommand($val);

                $l_hostNotOptArr[] = [
                    "id"  => $val,
                    "val" => $l_command["name"],
                    "sel" => 1,
                    "url" => ""
                ];
            } // foreach
        }
        else
        {
            $l_assCommands = [];
        } // if

        foreach ($l_commands as $key => $val)
        {
            if (array_search($key, $l_assCommands) === false)
            {
                $l_hostNotOptArr[] = [
                    "id"  => $key,
                    "val" => $val,
                    "sel" => 0,
                    "url" => ""
                ];
            } // if
        } // foreach

        if ($l_catdata["isys_cats_person_nagios_list__service_notification_commands"] != null)
        {
            $l_assCommands = explode(",", $l_catdata["isys_cats_person_nagios_list__service_notification_commands"]);

            foreach ($l_assCommands as $val)
            {
                $l_command = $l_comp_daoNagios->getCommand($val);

                $l_serviceNotOptArr[] = [
                    "id"  => $val,
                    "val" => $l_command["name"],
                    "sel" => 1,
                    "url" => ""
                ];
            } // foreach
        }
        else
        {
            $l_assCommands = [];
        } // if

        foreach ($l_commands as $key => $val)
        {
            if (in_array($key, $l_assCommands) === false)
            {
                $l_serviceNotOptArr[] = [
                    "id"  => $key,
                    "val" => $val,
                    "sel" => 0,
                    "url" => ""
                ];
            } // if
        } // foreach

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_dialog_yes_no                                              = serialize(get_smarty_arr_YES_NO());
        $l_rules["CONTACT_NAGIOS_IS_EXPORTABLE"]["p_arData"]          = $l_dialog_yes_no;
        $l_rules["CONTACT_HOST_NOTIFICATION"]["p_arData"]             = $l_dialog_yes_no;
        $l_rules["CONTACT_SERVICE_NOTIFICATION"]["p_arData"]          = $l_dialog_yes_no;
        $l_rules["CONTACT_CAN_SUBMIT_COMMANDS"]["p_arData"]           = $l_dialog_yes_no;
        $l_rules["CONTACT_RETAIN_STATUS_INFORMATION"]["p_arData"]     = $l_dialog_yes_no;
        $l_rules["CONTACT_RETAIN_NONSTATUS_INFORMATION"]["p_arData"]  = $l_dialog_yes_no;
        $l_rules["CONTACT_HOST_NOTIFICATION_COMMANDS"]["p_arData"]    = serialize($l_hostNotOptArr);
        $l_rules["CONTACT_SERVICE_NOTIFICATION_COMMANDS"]["p_arData"] = serialize($l_serviceNotOptArr);
        $l_rules["HOST_NOTIFICATION_OPTIONS"]["p_arData"]             = serialize($l_hostOptArr);
        $l_rules["SERVICE_NOTIFICATION_OPTIONS"]["p_arData"]          = serialize($l_serviceOptArr);
        $l_rules["CONTACT_HOST_NOTIFICATION_PERIOD"]["p_arData"]      = serialize($l_comp_daoNagios->getTimeperiodsAssoc());
        $l_rules["CONTACT_SERVICE_NOTIFICATION_PERIOD"]["p_arData"]   = serialize($l_comp_daoNagios->getTimeperiodsAssoc());

        if ($l_catdata == null)
        {
            $l_rules["CONTACT_NAGIOS_IS_EXPORTABLE"]["p_strSelectedID"] = 1;
        } // if

        // Apply rules.
        $this->get_template_component()
            ->assign('contact_name_selection', $l_catdata['isys_cats_person_nagios_list__contact_name_selection'] ?: C__NAGIOS__PERSON_OPTION__OBJECT_TITLE)
            ->assign(
                'obj_title',
                isys_cmdb_dao::instance($this->m_database_component)
                    ->get_obj_name_by_id_as_string($l_catdata['isys_cats_person_nagios_list__isys_obj__id'])
            )
            ->assign(
                'user_name',
                isys_cmdb_dao_category_s_person_login::instance($this->m_database_component)
                    ->get_data(null, $l_catdata['isys_cats_person_nagios_list__isys_obj__id'])
                    ->get_row_value('isys_cats_person_list__title')
            )
            ->assign(
                'contact_name',
                isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_catdata['isys_cats_person_nagios_list__isys_obj__id']))
            )
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class