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
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_cluster_service extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category room.
     *
     * @param  isys_cmdb_dao_category_s_cluster_service $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_comp_template;

        if (@$_GET["runs_on_ajax_call"])
        {
            $this->process_runs_on($p_cat);
            die();
        } // if

        $l_quickinfo = new isys_ajax_handler_quick_info();

        $l_tpl_navbar = isys_module_request::get_instance()
            ->get_navbar();

        $l_catlevel = $_GET[C__CMDB__GET__CATLEVEL];

        if ($l_catlevel > 0)
        {
            $l_catdata = $p_cat->get_data($l_catlevel, null, "", null, C__RECORD_STATUS__NORMAL)
                ->__to_array();

            // Make rules
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_room_list__description"];

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_bDisabled"] = "0";
            }
            else
            {
                $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_bDisabled"] = "1";
            }

            $l_dao_cs = new isys_cmdb_dao_category_g_cluster_service($p_cat->get_database_component());

            $l_addresses      = [];
            $l_drives         = [];
            $l_cluster_shares = [];

            $l_res_addresses      = $l_dao_cs->get_cluster_addresses($l_catdata["isys_catg_cluster_service_list__id"]);
            $l_res_drives         = $l_dao_cs->get_cluster_drives($l_catdata["isys_catg_cluster_service_list__id"]);
            $l_res_cluster_shares = $l_dao_cs->get_cluster_shares($l_catdata["isys_catg_cluster_service_list__id"]);

            while ($l_row = $l_res_addresses->get_row())
            {
                $l_addresses[] = $l_row["isys_catg_ip_list__id"];
            } // while

            while ($l_row = $l_res_drives->get_row())
            {
                $l_drives[] = $l_row["isys_catg_drive_list__id"];
            } // while

            while ($l_row = $l_res_cluster_shares->get_row())
            {
                $l_cluster_shares[] = $l_row["isys_catg_shares_list__id"];
            } // while

            $l_dao_c_members = new isys_cmdb_dao_category_g_cluster_members($p_cat->get_database_component());

            $l_res_members = $l_dao_c_members->get_data(null, $l_catdata["isys_catg_cluster_service_list__isys_obj__id"]);
            $l_members     = "";

            while ($l_row = $l_res_members->get_row())
            {
                $l_selected = ($l_dao_cs->get_cluster_members($l_catdata["isys_catg_cluster_service_list__id"], $l_row["isys_catg_cluster_members_list__id"])
                        ->num_rows() > 0);

                $l_title = $p_cat->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);

                $l_cat_list[] = [
                    "val" => $l_title,
                    "hid" => 0,
                    "sel" => $l_selected,
                    "id"  => $l_row["isys_catg_cluster_members_list__id"]
                ];

                if ($l_selected) $l_arData[$l_row["isys_catg_cluster_members_list__id"]] = $l_title;
            }

            if ($l_catdata["isys_obj__id"])
            {
                $l_cluster_title = $l_quickinfo->get_quick_info($l_catdata["isys_obj__id"], $p_cat->get_obj_name_by_id_as_string($l_catdata["isys_obj__id"]), C__LINK__OBJECT);

                $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_strValue"]            = $l_members;
                $l_rules["C__CATS__CLUSTER_SERVICE__TYPE"]["p_strSelectedID"]          = $l_catdata["isys_catg_cluster_service_list__isys_cluster_type__id"];
                $l_rules["C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER"]["p_strValue"]   = $l_catdata["isys_obj__id"];
                $l_rules["C__CATS__CLUSTER_SERVICE__HOST_ADDRESSES"]["p_preSelection"] = isys_glob_htmlentities(isys_format_json::encode($l_addresses));
                $l_rules["C__CATS__CLUSTER_SERVICE__VOLUMES"]["p_preSelection"]        = isys_glob_htmlentities(isys_format_json::encode($l_drives));
                $l_rules["C__CATS__CLUSTER_SERVICE__SHARES"]["p_preSelection"]         = isys_glob_htmlentities(isys_format_json::encode($l_cluster_shares));

                $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_arData"] = serialize($l_cat_list);

                // Default server dialog hack.
                $l_rules["C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"]["p_arData"]        = serialize($l_arData);
                $l_rules["C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__cluster_members_list__id"];

                // Get DBMS
                $l_dbms_data                                                              = isys_cmdb_dao_category_g_cluster_service::get_dbms(
                    $l_catdata['isys_catg_cluster_service_list__isys_catg_relation_list__id']
                );
                $l_rules['C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA']['p_strSelectedID'] = $l_dbms_data['isys_obj__id'];

                $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
                )]["p_strValue"] = $l_catdata["isys_catg_cluster_service_list__description"];
            } // if
        } // if

        $g_comp_template->assign(
            "cluster_service_ajax_url",
            "?" . http_build_query($_GET, null, "&") . "&call=category&runs_on_ajax_call=1&" . C__CMDB__GET__CATLEVEL . "=" . $l_catlevel
        );

        $l_tpl_navbar->set_active(false, C__NAVBAR_BUTTON__ARCHIVE);

        // Apply rules
        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    /**
     * Method for processing the list.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  boolean
     */
    public function process_list(isys_cmdb_dao_category $p_cat)
    {
        $this->list_view("isys_catg_cluster_service", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_cats_cluster_service($p_cat));

        return true;
    }

    public function process_runs_on(isys_cmdb_dao_category_s_cluster_service $p_cat)
    {
        global $g_comp_template;

        $l_instance = new isys_smarty_plugin_f_dialog_list();

        if ($_GET[C__CMDB__GET__CATLEVEL] > 0) $l_catdata = $p_cat->get_data($_GET[C__CMDB__GET__CATLEVEL], null, "", null, C__RECORD_STATUS__NORMAL)
            ->__to_array();

        $l_dao_c_members = new isys_cmdb_dao_category_g_cluster_members($p_cat->get_database_component());
        $l_dao_cs        = new isys_cmdb_dao_category_g_cluster_service($p_cat->get_database_component());

        $l_res_members = $l_dao_c_members->get_data(null, $_POST["cluster_id"]);
        $l_members     = "";

        while ($l_row = $l_res_members->get_row())
        {

            if ($l_catdata) $l_selected = ($l_dao_cs->get_cluster_members($l_catdata["isys_catg_cluster_service_list__id"], $l_row["isys_catg_cluster_members_list__id"])
                    ->num_rows() > 0);

            $l_title = $p_cat->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);

            $l_cat_list[] = [
                "val" => $l_title,
                "hid" => 0,
                "sel" => $l_selected,
                "id"  => $l_row["isys_catg_cluster_members_list__id"]
            ];
        }

        $l_params = [
            "name"            => "C__CATS__CLUSTER_SERVICE__RUNS_ON",
            "remove_callback" => "idoit.callbackManager.triggerCallback('clusterservice__runs_on_callback').triggerCallback('clusterservice__set_default_server');",
            "p_arData"        => serialize($l_cat_list),
        ];

        if ($_POST[C__GET__NAVMODE] == "") $_GET["editMode"] = 0;

        die($l_instance->navigation_edit($g_comp_template, $l_params));
    } // function

    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__cluster_service.tpl");
        parent::__construct($p_template);
    } // function
} // class