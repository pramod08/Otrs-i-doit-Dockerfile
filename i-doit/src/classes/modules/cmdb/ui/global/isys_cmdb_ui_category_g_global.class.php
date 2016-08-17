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
 * CMDB Global category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Wösten <awoesten@i-doit.de>
 * @version     Dennis Blümer <dbluemer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 * Notice: This category is special.
 * After creating an object the object gets the status NORMAL only if the data for catg global is saved.
 * Otherwise the object gets BIRTH status.
 */
class isys_cmdb_ui_category_g_global extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_global &$p_cat
     *
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_loc;

        $l_tag_selection = $l_rules = [];

        $l_gets  = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_object_id = (isset($l_gets[C__CMDB__GET__OBJECT])) ? $l_gets[C__CMDB__GET__OBJECT] : $_GET[C__CMDB__GET__OBJECT];

        // Fetch data.
        $l_catdata = $p_cat->get_general_data();

        if (is_null($l_catdata))
        {
            $p_cat->create($l_object_id);
            $l_catdata = $p_cat->get_data(null, $l_object_id)
                ->get_row();
        } // if

        if (isys_tenantsettings::get('barcode.enabled', 1) == 1)
        {
            $this->get_template_component()
                ->assign("g_sysid", $l_catdata["isys_obj__sysid"]);
        } // if

        $l_rules["C__CATG__GLOBAL_CREATED"]["p_strValue"] = $g_loc->fmt_datetime($l_catdata["isys_obj__created"], true, false);
        $l_rules["C__CATG__GLOBAL_UPDATED"]["p_strValue"] = $g_loc->fmt_datetime($l_catdata["isys_obj__updated"], true, false);

        $l_rules["C__OBJ__ID"]["p_strValue"]                                                                          = $l_catdata['isys_obj__id'];
        $l_rules["C__OBJ__TYPE"]["p_strSelectedID"]                                                                   = $l_catdata["isys_obj_type__id"];
        $l_rules["C__OBJ__STATUS"]["p_strValue"]                                                                      = $p_cat->get_record_status_as_string(
            $l_catdata["isys_obj__status"]
        );
        $l_rules["C__CATG__GLOBAL_TITLE"]["p_strValue"]                                                               = $l_catdata["isys_obj__title"];
        $l_rules["C__CATG__GLOBAL_SYSID"]["p_strValue"]                                                               = $l_catdata["isys_obj__sysid"];
        $l_rules["C__CATG__GLOBAL_PURPOSE"]["p_strSelectedID"]                                                        = $l_catdata["isys_catg_global_list__isys_purpose__id"];
        $l_rules["C__CATG__GLOBAL_CATEGORY"]["p_strSelectedID"]                                                       = $l_catdata["isys_catg_global_list__isys_catg_global_category__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_obj__description"];

        if ($l_catdata["isys_obj__status"] == C__RECORD_STATUS__ARCHIVED && $l_catdata["isys_obj__status"] == C__RECORD_STATUS__DELETED)
        {
            $l_rules["C__OBJ__STATUS"]["p_bDisabled"] = "1";
        } // if

        // Because "Birth" is no option, the user might get confused by an awkward status.
        if ($l_catdata["isys_obj__status"] == 1)
        {
            $l_catdata["isys_obj__status"] = 2;
        } // if

        if (isset($_POST["template"]) && $_POST["template"] != "")
        {
            $_POST["template"]             = (int) $_POST["template"];
            $l_catdata["isys_obj__status"] = ($_POST["template"] === 1) ? C__RECORD_STATUS__TEMPLATE : ($_POST["template"] === C__RECORD_STATUS__MASS_CHANGES_TEMPLATE ? C__RECORD_STATUS__MASS_CHANGES_TEMPLATE : C__RECORD_STATUS__NORMAL);
        } // if

        $l_rules["C__OBJ__STATUS"]["p_strSelectedID"] = $l_catdata["isys_obj__status"];

        $l_rules["C__OBJ__STATUS"]["p_arData"] = serialize(
            [
                C__RECORD_STATUS__NORMAL                => _L("LC__CMDB__RECORD_STATUS__NORMAL"),
                C__RECORD_STATUS__TEMPLATE              => "Template",
                C__RECORD_STATUS__ARCHIVED              => _L("LC__CMDB__RECORD_STATUS__ARCHIVED"),
                C__RECORD_STATUS__DELETED               => _L("LC__CMDB__RECORD_STATUS__DELETED"),
                C__RECORD_STATUS__MASS_CHANGES_TEMPLATE => _L("LC__MASS_CHANGE__CHANGE_TEMPLATE")
            ]
        );

        $l_rules["C__OBJ__STATUS"]["p_arDisabled"] = serialize(
            [
                C__RECORD_STATUS__DELETED  => true,
                C__RECORD_STATUS__BIRTH    => true,
                C__RECORD_STATUS__ARCHIVED => true
            ]
        );

        // CMDB STATUS.
        $l_rules["C__OBJ__CMDB_STATUS"]["p_strTable"]   = "isys_cmdb_status";
        $l_rules["C__OBJ__CMDB_STATUS"]["condition"]    = "isys_cmdb_status__id NOT IN ('" . C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE . "')";
        $l_rules["C__OBJ__CMDB_STATUS"]['p_arDisabled'] = serialize([C__CMDB_STATUS__IDOIT_STATUS => 'LC__CMDB_STATUS__IDOIT_STATUS']);

        $l_cmdb_status_colors = [];
        $l_cmdb_statuses      = isys_factory_cmdb_dialog_dao::get_instance('isys_cmdb_status', $this->get_database_component())
            ->get_data();

        foreach ($l_cmdb_statuses as $l_cmdb_status)
        {
            $l_cmdb_status_colors[$l_cmdb_status['isys_cmdb_status__id']] = '#' . $l_cmdb_status['isys_cmdb_status__color'];
        } // foreach

        if ($l_catdata["isys_obj__isys_cmdb_status__id"] > 0)
        {
            $l_rules["C__OBJ__CMDB_STATUS"]["p_strSelectedID"] = $l_catdata["isys_obj__isys_cmdb_status__id"];
        }
        else
        {
            $l_rules["C__OBJ__CMDB_STATUS"]["p_strSelectedID"] = C__CMDB_STATUS__IN_OPERATION;
        } // if

        $l_rules["C__CATG__GLOBAL_PURPOSE"]["p_strTable"]  = "isys_purpose";
        $l_rules["C__CATG__GLOBAL_CATEGORY"]["p_strTable"] = "isys_catg_global_category";
        $l_rules["C__CATG__GLOBAL_SYSID"]["p_bDisabled"]   = C__SYSID__READONLY;
        $l_show_in_tree                                    = true;

        // See isys_quick_configuration_wizard_dao $m_skipped_objecttypes
        $l_blacklisted_object_types = [
            C__OBJTYPE__GENERIC_TEMPLATE,
            C__OBJTYPE__LOCATION_GENERIC,
            C__OBJTYPE__RELATION,
            C__OBJTYPE__CONTAINER,
            C__OBJTYPE__PARALLEL_RELATION,
            C__OBJTYPE__SOA_STACK
        ];

        // Check if object is a template
        if ($l_posts['template'] !== '' || (int) $l_catdata['isys_obj__status'] === C__RECORD_STATUS__MASS_CHANGES_TEMPLATE || (int) $l_catdata['isys_obj__status'] === C__RECORD_STATUS__TEMPLATE || in_array(
                (int) $l_catdata['isys_obj__isys_obj_type__id'],
                $l_blacklisted_object_types
            )
        )
        {
            $l_show_in_tree = null;
        } // if

        $l_res = $p_cat->get_object_types(null, $l_show_in_tree);

        while ($l_row = $l_res->get_row())
        {
            $l_rules["C__OBJ__TYPE"]["p_arData"][$l_row['isys_obj_type__id']] = _L($l_row['isys_obj_type__title']);
        } // while

        if ($l_catdata['isys_obj__id'])
        {
            $l_tag_selection = $p_cat->get_assigned_tag($l_catdata['isys_obj__id'], true);
        } // if

        $l_rules['C__CATG__GLOBAL_TAG'] = [
            'p_strTable'      => 'isys_tag',
            'emptyMessage'    => _L('LC__CMDB__CATG__GLOBAL__NO_TAGS_FOUND'),
            'p_onComplete'    => "idoit.callbackManager.triggerCallback('cmdb-catg-global-tag-update', selected);",
            'p_strSelectedID' => implode(',', $l_tag_selection),
            'multiselect'     => true
        ];

        // Apply rules.
        $this->get_template_component()
            ->assign(
                "placeholders_g_global",
                (((int) $l_catdata['isys_obj__status'] === C__RECORD_STATUS__MASS_CHANGES_TEMPLATE || isys_application::instance()->template->editmode(
                    ) === false) ? false : isys_cmdb_dao_category_g_accounting::get_placeholders_info_with_data(true, 4124, 5, null, isys_cmdb_dao::get_last_sysid()))
            )
            ->assign("created_by", $l_catdata["isys_obj__created_by"])
            ->assign("changed_by", $l_catdata["isys_obj__updated_by"])
            ->assign("status_color", $l_catdata["isys_cmdb_status__color"])
            ->assign("status_colors", isys_format_json::encode($l_cmdb_status_colors))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class