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
 * CMDB custom fields category.
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_custom_fields extends isys_cmdb_ui_category_global
{
    /**
     * Gets custom category title
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        // Adding the language manager, for custom translations: ID-1649.
        return _L(
            isys_cmdb_dao_category_g_custom_fields::instance($p_cat->get_database_component())
                ->get_category_title($_GET[C__CMDB__GET__CATG_CUSTOM])
        );
    } // function

    /**
     * Processes the user interface.
     *
     * @param   isys_cmdb_dao_category_g_custom_fields $p_cat
     *
     * @return   array|void
     * @version Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_description    = "";
        $l_catg_custom_id = ($p_cat->get_catg_custom_id()) ? $p_cat->get_catg_custom_id() : $_GET[C__CMDB__GET__CATG_CUSTOM];

        $l_config = $p_cat->get_config($l_catg_custom_id);

        $l_cat_info    = $p_cat->get_category_info($l_catg_custom_id);
        $l_multivalued = (bool) $l_cat_info['isysgui_catg_custom__list_multi_value'];

        if (!$l_multivalued)
        {
            $_GET[C__CMDB__GET__CATLEVEL] = null;
        }
        elseif ($l_multivalued && !isset($_GET[C__CMDB__GET__CATLEVEL]))
        {
            $_GET[C__CMDB__GET__CATLEVEL] = $p_cat->get_data_id($l_catg_custom_id);
        } // if

        $l_data = $p_cat->get_data(
            $_GET[C__CMDB__GET__CATLEVEL],
            $_GET[C__CMDB__GET__OBJECT],
            " AND isys_catg_custom_fields_list__isysgui_catg_custom__id = '" . $p_cat->convert_sql_id($l_catg_custom_id) . "'"
        );

        $l_rules = [];

        if (count($l_data))
        {
            if ((int) $l_cat_info['isysgui_catg_custom__list_multi_value'] === 0)
            {
                $p_cat->set_category_entries_purgable(true);
            } // if

            $l_used_keys = [];

            while ($l_row = $l_data->get_row())
            {
                $l_key = $l_row["isys_catg_custom_fields_list__field_key"];
                $l_tmp = $l_config[$l_key];

                if (isset($l_used_keys[$l_key])) continue;

                $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strValue"] = $l_row["isys_catg_custom_fields_list__field_content"];

                if (isset($l_tmp["popup"]))
                {
                    switch ($l_tmp["popup"])
                    {
                        case "dialog":
                        case "dialog_plus":
                            // @fixes ID-1193
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strValue"]      = null;
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strSelectedID"] = $l_row["isys_catg_custom_fields_list__field_content"];
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strTable"]      = "isys_dialog_plus_custom";
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_identifier"]    = $l_tmp["identifier"];
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["condition"]       = "isys_dialog_plus_custom__identifier = '" . $l_tmp["identifier"] . "'";
                            if (isset($l_tmp['multiselection']))
                            {
                                $l_row["isys_catg_custom_fields_list__field_content"]     = implode(
                                    ',',
                                    $p_cat->get_assigned_entries($l_row['isys_catg_custom_fields_list__field_key'], $l_row['isys_catg_custom_fields_list__data__id'])
                                );
                                $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strValue"]      = $l_row["isys_catg_custom_fields_list__field_content"];
                                $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strSelectedID"] = $l_row["isys_catg_custom_fields_list__field_content"];
                                $l_rules["C__CATG__CUSTOM__" . $l_key]["multiselect"]     = 1;
                                $l_rules["C__CATG__CUSTOM__" . $l_key]["p_onComplete"]    = "$('C__CATG__CUSTOM__" . $l_key . "').fire('C__CATG__CUSTOM__" . $l_key . ":updated');";
                            }
                            break;
                        case "browser_object":
                            if (isset($l_tmp['multiselection']))
                            {
                                $l_row["isys_catg_custom_fields_list__field_content"]     = isys_format_json::encode(
                                    $p_cat->get_assigned_entries($l_row['isys_catg_custom_fields_list__field_key'], $l_row['isys_catg_custom_fields_list__data__id'])
                                );
                                $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strValue"]      = $l_row["isys_catg_custom_fields_list__field_content"];
                                $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strSelectedID"] = $l_row["isys_catg_custom_fields_list__field_content"];
                                $l_rules["C__CATG__CUSTOM__" . $l_key][isys_popup_browser_object_ng::C__MULTISELECTION] = true;
                            } // if
                    } // switch
                } // if

                if (isset($l_tmp["type"]))
                {
                    switch ($l_tmp["type"])
                    {
                        case "f_link":
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strTarget"] = "__blank";
                            break;

                        case 'f_dialog':
                            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strSelectedID"] = $l_row['isys_catg_custom_fields_list__field_content'];
                            break;
                    } // switch
                } // if

                if (is_numeric($l_row["isys_catg_custom_fields_list__field_content"]))
                {
                    $l_rules["C__CATG__CUSTOM__" . $l_key]["p_strSelectedID"] = $l_row["isys_catg_custom_fields_list__field_content"];
                } // if

                if (empty($l_description))
                {
                    $l_description = $l_row["isys_catg_custom_fields_list__description"];
                } // if

                $l_used_keys[$l_key] = true;
            } // while
        } // if

        $l_commentary = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_catg_custom_id;

        $l_data                               = $p_cat->get_data_by_key($_GET[C__CMDB__GET__OBJECT], $l_catg_custom_id, $l_commentary, $_GET[C__CMDB__GET__CATLEVEL])
            ->get_row();
        $l_rules[$l_commentary]["p_strValue"] = $l_data["isys_catg_custom_fields_list__field_content"];

        // Before assigning the field configuration, we iterate through and add some fields
        foreach ($l_config as $l_key => $l_field)
        {
            if ($l_field['extra'] == 'yes-no')
            {
                $l_rules["C__CATG__CUSTOM__" . $l_key]['p_bDbFieldNN'] = true;
                $l_rules["C__CATG__CUSTOM__" . $l_key]['p_arData']     = [
                    'LC__UNIVERSAL__YES' => _L('LC__UNIVERSAL__YES'),
                    'LC__UNIVERSAL__NO'  => _L('LC__UNIVERSAL__NO')
                ];
            } // if

            $l_rules["C__CATG__CUSTOM__" . $l_key]["p_dataIdentifier"] = 'isys_cmdb_dao_category_g_custom_fields::' . $l_field['type'] . '_' . $l_key;

            if (!isset($l_used_keys[$l_key]))
            {
                if ($l_field['multiselection'] > 0 && $l_field['type'] == 'f_popup' && $l_field['popup'] == 'dialog_plus')
                {
                    $l_rules["C__CATG__CUSTOM__" . $l_key]["multiselect"]     = true;
                    $l_rules["C__CATG__CUSTOM__" . $l_key]["callback_accept"] = "$('C__CATG__CUSTOM__" . $l_key . "').fire('C__CATG__CUSTOM__" . $l_key . ":updated');";
                } // if

                if ($l_field['multiselection'] > 0 && $l_field['type'] == 'f_popup' && $l_field['popup'] == 'browser_object')
                {
                    $l_rules["C__CATG__CUSTOM__" . $l_key][isys_popup_browser_object_ng::C__MULTISELECTION] = true;
                } // if
            } // if
        } // foreach

        $this->get_template_component()
            ->assign("fields", $l_config)
            ->assign("catg_custom_id", $l_catg_custom_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW);
    } // function

    /**
     * Process list
     *
     * @param   isys_cmdb_dao_category_g_custom_fields $p_cat
     *
     * @return  null|void
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        global $index_includes;

        $l_get  = $_GET;
        $l_data = [];
        unset($l_get["ajax"]);
        unset($l_get["call"]);
        $p_cat->set_catg_custom_id($l_get[C__CMDB__GET__CATG_CUSTOM]);
        $l_category_info  = $p_cat->get_category_info($l_get[C__CMDB__GET__CATG_CUSTOM]);
        $l_category_const = $l_category_info['isysgui_catg_custom__const'];
        $l_config         = unserialize($l_category_info['isysgui_catg_custom__config']);

        $l_dao_list       = new isys_cmdb_dao_list_catg_custom_fields($p_cat->get_database_component());
        $l_current_status = $l_dao_list->get_rec_status();
        $l_navbar         = isys_component_template_navbar::getInstance();
        $l_result         = $l_dao_list->get_result(null, $l_get[C__CMDB__GET__OBJECT], $l_current_status, $l_get[C__CMDB__GET__CATG_CUSTOM]);
        $l_amount         = $l_result->num_rows();

        if ($l_amount > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_data[$l_row['isys_catg_custom_fields_list__data__id']][] = $l_row;
            } // while
        } // if

        $l_dao_list->set_properties($p_cat->get_properties())
            ->set_rows($l_data)
            ->set_config($l_config);

        $l_header_fields   = $l_dao_list->get_fields();
        $l_reformated_rows = $l_dao_list->reformat_rows($l_get[C__CMDB__GET__OBJECT]);
        $l_objList         = isys_component_list::factory($l_reformated_rows, null, null, null, ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV ? 'csv' : 'html'));
        $l_objList->config($l_header_fields, $l_dao_list->make_row_link(), "id", false);
        $l_result = $l_objList->createTempTable();

        if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS === 1)
        {
            $l_arData[C__RECORD_STATUS__TEMPLATE] = "Template";
        } // if

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV)
        {
            $l_objList->createTempTable();
        } // if

        $l_dao_list->get_rec_array();
        $l_arData = $l_dao_list->get_rec_array();

        $this->get_template_component()
            ->assign("conn_link", isys_helper_link::create_url($l_get))
            ->assign("dao_connector", $p_cat)
            ->assign("list_display", true)
            ->assign("bNavbarFilter", "1")
            ->assign("objectTableList", ($l_result) ? $l_objList->getTempTableHtml() : '<div class="p10">' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>')
            ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=0")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $l_current_status)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($l_arData));

        $l_supervisor_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $l_category_const);
        $l_delete_right     = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::DELETE, $_GET[C__CMDB__GET__OBJECT], $l_category_const);
        $l_archive_right    = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $_GET[C__CMDB__GET__OBJECT], $l_category_const);
        $l_edit_right       = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $l_category_const);

        $l_quickpurge = (isys_settings::get('cmdb.quickpurge') == '1') ? true : false;

        $l_navbar->hide_all_buttons([C__NAVBAR_BUTTON__NEW])
            ->set_active(($l_amount > 0), C__NAVBAR_BUTTON__PRINT)
            ->set_active(($l_edit_right && $l_amount > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_supervisor_right && $l_quickpurge && $l_amount > 0), C__NAVBAR_BUTTON__QUICK_PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(($l_amount > 0), C__NAVBAR_BUTTON__PRINT)
            ->set_visible(($l_supervisor_right && $l_quickpurge), C__NAVBAR_BUTTON__QUICK_PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
            ->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        switch ($l_current_status)
        {
            case C__RECORD_STATUS__ARCHIVED:
                $l_navbar->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active($l_delete_right && $l_amount > 0, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_active(($l_archive_right || $l_delete_right) && $l_amount > 0, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible($l_delete_right, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible($l_archive_right || $l_delete_right, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                break;
            case C__RECORD_STATUS__DELETED:
                $l_navbar->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_active($l_supervisor_right && $l_amount > 0, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_edit_right && $l_amount > 0, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_visible($l_delete_right, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible($l_supervisor_right, C__NAVBAR_BUTTON__PURGE);
                break;
            case C__RECORD_STATUS__NORMAL:
            default:
                $l_navbar->set_active(($l_archive_right || $l_delete_right || $l_supervisor_right) && $l_amount > 0, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                break;
        }

        if (count($l_data) == 0)
        {
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__PURGE);
        } // if

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    } // function

    /**
     * UI constructor. Which is needed for the overview otherwise the overview category won´t work
     * with custom categories.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__custom_fields.tpl");
        parent::__construct($p_template);
    } // function
} // class