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
 * Popup class for browsers with general functions
 *
 *
 * @package    i-doit
 * @subpackage Popups
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
abstract class isys_popup_browser extends isys_component_popup
{

    private $m_object_count = 0;

    public function build_location_tree(isys_component_template $p_tpl, isys_component_tree $p_tree, $p_selected)
    {
        global $g_comp_database, $g_dirs, $g_comp_template_language_manager;

        $l_selected = null;

        /* Root node */
        $l_node_root = $p_tree->add_node(1, -1, $g_comp_template_language_manager->{"LC__CMDB__TREE__LOCATION"});

        /* Create special location DAO */
        $l_dao_loc = new isys_cmdb_dao_location($g_comp_database);

        /* Get location tree */
        $l_locdata = $l_dao_loc->get_locations_by_obj_id(
            $l_dao_loc->get_root_location_as_integer()
        );

        // Check if type is in type filter (if specified)
        if (!empty($_GET["typeFilter"]))
        {
            $l_arTypes = explode(";", $_GET["typeFilter"]);
            if (is_array($l_arTypes))
            {
                foreach ($l_arTypes as $l_t)
                {
                    $l_types[constant($l_t)] = $l_t;
                }
            }
            else
            {
                $l_types[constant($_GET["typeFilter"])] = $_GET["typeFilter"];
            }
        }
        else
        {
            $l_types = null;
        }

        if (!empty($_GET["catFilter"]))
        {
            $l_isysgui_arr = $l_dao_loc->get_isysgui("isysgui_cat" . $_GET["catFilterType"], null, null, $_GET["catFilter"])
                ->get_row();

            if (class_exists($l_isysgui_arr["isysgui_cat" . $_GET["catFilterType"] . "__class_name"]))
            {
                $l_object_dao = new $l_isysgui_arr["isysgui_cat" . $_GET["catFilterType"] . "__class_name"]($g_comp_database);

                $l_res     = $l_object_dao->get_data(null, null, null, null, C__RECORD_STATUS__NORMAL);
                $l_counter = 0;
                while ($l_row = $l_res->get_row())
                {
                    $l_arr[$l_counter] = $l_row[$l_isysgui_arr["isysgui_cat" . $_GET["catFilterType"] . "__source_table"] . "_list__isys_obj__id"];
                    if (isset($l_arr[$l_counter]) && $l_arr[$l_counter] > 0)
                    {
                        $l_object_info                                          = $l_dao_loc->get_object_by_id($l_arr[$l_counter])
                            ->get_row();
                        $l_types[$l_object_info["isys_obj__isys_obj_type__id"]] = $l_object_info["isys_obj__isys_obj_type__id"];
                    }
                }
            }
        }

        /* Iterate through location tree and build up tree */
        foreach ($l_locdata as $l_location)
        {
            static $l_isroot = true;

            if (isset($l_types) && is_array($l_types))
            {
                if (isset($l_types[$l_location[0]]))
                {
                    $l_showType = true;
                }
                else $l_showType = false;
            }
            else
            {
                $l_showType = true;
            }

            /* Handle root object */
            if ($l_isroot)
            {
                /* First location object is root */
                $l_location[1] = $l_node_root;
                $l_isroot      = false;
                continue;
            }

            $l_title = $l_location[4];

            if ($p_selected == $l_location[0])
            {
                $l_selected = $l_location[0];
            }

            $l_obj_type = $l_dao_loc->get_objTypeID($l_location[0]);

            $l_type_row = $l_dao_loc->get_type_by_id($l_obj_type);

            if ($l_type_row["isys_obj_type__icon"])
            {
                $l_icon = $l_type_row["isys_obj_type__icon"];
            }
            else
            {
                $l_icon = "";
            }

            if ($l_icon)
            {
                $l_icon = $g_dirs["images"] . "tree/" . $l_icon;
            }

            $l_locgets                         = $_GET;
            $l_locgets[C__CMDB__GET__OBJECT]   = $l_location[0];
            $l_locgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_LOCATION;

            if ($_GET[C__CMDB__GET__OBJECT] == $l_location[0]) $l_selected = $l_location[0];

            if ($l_showType)
            {

                // Add tree node
                $p_tree->add_node(
                    $l_location[0],
                    $l_location[1],
                    $l_title,
                    "javascript:select_object('" . $l_location[0] . "', '" . str_replace("'", "", $l_title) . "');",
                    "",
                    $l_icon
                );
            }
        }

        return $p_tree->process($l_selected);
    }

    /**
     * Build an object tree
     *
     * @param isys_component_template $p_tpl
     * @param isys_component_tree     $p_tree
     * @param bool                    $p_bSelected
     * @param unknown_type            $p_link
     * @param integer                 $p_status
     *
     * @version NP 2007-10-18
     * @return string
     */
    public function build_object_tree(isys_component_template $p_tpl, isys_component_tree $p_tree, $p_bSelected, $p_link = null, $p_status = C__RECORD_STATUS__NORMAL, $p_onclick = null, $p_groupFilter = null, $p_object_id = null, $p_multiSelection = false, $p_relation = false, $p_relation_only = false, $p_relation_type = null)
    {
        global $g_comp_database, $g_dirs;

        $l_tree     = 0;
        $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

        if (is_null($p_object_id))
        {
            $p_object_id = $_GET[C__CMDB__GET__OBJECT];
        }

        if ($p_multiSelection)
        {
            $p_object_id = explode(",", $p_object_id);
        }
        else
        {
            $p_object_id = [$p_object_id];
        }

        if ($p_groupFilter != null)
        {
            $_GET["groupFilter"] = $p_groupFilter;
        }

        /* Root node "CMDB" */
        $l_node_root = $p_tree->add_node(
            $l_tree++,
            C__CMDB__TREE_NODE__PARENT,
            "CMDB"
        );

        // Check if type is in type filter (if specified)
        if (!empty($_GET["typeFilter"]))
        {
            $l_arTypes = explode(";", $_GET["typeFilter"]);
            if (is_array($l_arTypes))
            {
                foreach ($l_arTypes as $l_t)
                {
                    if (is_numeric($l_t))
                    {
                        $l_types[$l_t] = $l_t;
                    }
                    else
                    {
                        $l_types[constant($l_t)] = $l_t;
                    }
                }
            }
            else
            {
                $l_types[constant($_GET["typeFilter"])] = $_GET["typeFilter"];
            }
        }
        else
        {
            $l_types = null;
        }

        // CHECK Category has entries (if specified)
        if (!empty($_GET["catFilter"]))
        {
            $l_catFilter = explode(";", $_GET["catFilter"]);

            // CHECK category condition
            if (!empty($_GET["catCondition"]))
            {
                $l_catCondition_tables  = explode(";", $_GET["catCondition"]);
                $l_catCondition_columns = explode(";", $_GET["catConditionColumn"]);
                $l_catCondition_value   = explode(";", $_GET["catConditionValue"]);
            }

            foreach ($l_catFilter AS $l_filter)
            {

                if ($_GET["catFilterType"] == "g" || empty($_GET["catFilterType"]))
                {
                    $l_cat_single = $l_dao_cmdb->get_catg_by_const(constant($l_filter))
                        ->get_row();

                    if (is_null($l_cat_single["isysgui_catg__parent"]))
                    {
                        $l_cat_res = $l_dao_cmdb->get_all_catg_by_obj_type_id(null, constant($l_filter));

                        while ($l_cat_row = $l_cat_res->get_row())
                        {
                            $l_types[$l_cat_row["isys_obj_type_2_isysgui_catg__isys_obj_type__id"]]                                         = "";
                            $l_cat_tables[$l_cat_row["isys_obj_type_2_isysgui_catg__isys_obj_type__id"]][$l_cat_row["isysgui_catg__const"]] = $l_cat_row["isysgui_catg__source_table"];
                        }
                    }
                    else
                    {
                        // subcat
                        $l_cat_res = $l_dao_cmdb->get_all_catg_by_obj_type_id(null, $l_cat_single["isysgui_catg__parent"]);

                        while ($l_cat_row = $l_cat_res->get_row())
                        {
                            $l_types[$l_cat_row["isys_obj_type_2_isysgui_catg__isys_obj_type__id"]]                                            = "";
                            $l_cat_tables[$l_cat_row["isys_obj_type_2_isysgui_catg__isys_obj_type__id"]][$l_cat_single["isysgui_catg__const"]] = $l_cat_single["isysgui_catg__source_table"];
                        }
                    }
                }
                elseif ($_GET["catFilterType"] == "s")
                {

                    $l_cat_single                                                                           = $l_dao_cmdb->get_objtype_by_cats_id(constant($l_filter))
                        ->get_row();
                    $l_cat_tables[$l_cat_single["isys_obj_type__id"]][$l_cat_single["isysgui_cats__const"]] = substr(
                        $l_cat_single["isysgui_cats__source_table"],
                        0,
                        strpos($l_cat_single["isysgui_cats__source_table"], "_list")
                    );
                }
            }
        }

        /* Evaluate object groups */
        $l_res_og = $l_dao_cmdb->objgroup_get();

        if ($l_res_og && $l_res_og->num_rows())
        {
            while ($l_row_og = $l_res_og->get_row())
            {

                if (isset($_GET["groupFilter"]) && !empty($_GET["groupFilter"]))
                {
                    $l_groups = explode(";", $_GET["groupFilter"]);

                    $l_stop = false;
                    foreach ($l_groups as $l_group)
                    {
                        if ($l_row_og["isys_obj_type_group__id"] == constant($l_group)) $l_stop = true;
                    }

                    if (!$l_stop) continue;
                }

                $l_res_ot = $l_dao_cmdb->objtype_get_by_objgroup_id($l_row_og["isys_obj_type_group__id"]);

                if ($l_res_ot && $l_res_ot->num_rows() > 0)
                {
                    /* Create node for object group, only if it has types */
                    $l_node_og = $p_tree->add_node(
                        $l_tree++,
                        $l_node_root,
                        _L($l_row_og["isys_obj_type_group__title"])
                    );

                    while ($l_row_ot = $l_res_ot->get_row())
                    {
                        /* Skip relation objects */
                        if ($l_row_ot["isys_obj_type__id"] == C__OBJTYPE__RELATION)
                        {
                            continue;
                        }

                        $l_showType = false;
                        $l_showIt   = true;

                        if (is_array($l_types))
                        {
                            if (array_key_exists($l_row_ot["isys_obj_type__id"], $l_types)) $l_showType = true;
                        }
                        else
                        {
                            $l_showType = true;
                        }

                        /* Check if type has to be shown */
                        $l_showType = (!empty($l_row_ot["isys_obj_type__show_in_tree"]) && $l_showType);

                        if ($l_showType)
                        {
                            $l_res_obj = $this->get_obj_res(
                                $l_dao_cmdb,
                                $l_row_ot["isys_obj_type__id"],
                                $p_status
                            );

                            /* Only if type has objects, add node */
                            if ($l_res_obj && $l_res_obj->num_rows() > 0)
                            {

                                $l_node_ot = $p_tree->add_node(
                                    $l_tree++,
                                    $l_node_og,
                                    _L($l_row_ot["isys_obj_type__title"])
                                );

                                while ($l_row_obj = $l_res_obj->get_row())
                                {

                                    $l_showIt = false;
                                    // Check category condition
                                    if (!empty($_GET["catCondition"]))
                                    {
                                        if (array_key_exists($l_row_obj["isys_obj_type__id"], $l_cat_tables))
                                        {
                                            $l_counter = 0;

                                            foreach ($l_cat_tables[$l_row_obj["isys_obj_type__id"]] AS $l_key => $l_val)
                                            {

                                                if (in_array($l_key, $l_catCondition_tables))
                                                {

                                                    $l_showIt = $this->check_condition(
                                                        $l_dao_cmdb,
                                                        $l_val . "_list",
                                                        $l_catCondition_columns[$l_counter],
                                                        $l_catCondition_value[$l_counter],
                                                        $l_row_obj["isys_obj__id"]
                                                    );
                                                    if ($l_showIt)
                                                    {
                                                        break;
                                                    }
                                                    $l_counter++;
                                                }
                                                else
                                                {
                                                    $l_showIt = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $l_showIt = true;
                                    }

                                    if (($l_row_obj["isys_obj__status"] == $p_status || $l_row_obj["isys_obj__status"] == $_GET["status"] || $_GET["status"] == 0) && $l_showIt)
                                    {

                                        if (in_array($l_row_obj["isys_obj__id"], $p_object_id))
                                        {
                                            $p_bSelected = $l_tree;
                                            $l_selected  = true;
                                        }
                                        else if ($p_multiSelection && !$p_relation_only) $l_selected = false;

                                        if ($p_multiSelection && !$p_relation_only)
                                        {
                                            // IE has problems with onchange thats why we use onclick
                                            $l_title   = "<label>" . "<input " . "class=\"objectCheck vam\" " . "type=\"checkbox\" " . "name=\"object[]\" " . "onClick=\"refresh_selected();\" " . "value=\"{$l_row_obj["isys_obj__id"]}\" " . (($l_selected) ? "checked=\"checked\"" : "") . " /> ";
                                            $p_onclick = null;
                                            $p_link    = "javascript:;";
                                        }
                                        else $l_title = "";

                                        $l_title .= ($l_row_obj["isys_obj__title"] == "") ? "unnamed" : htmlspecialchars(
                                            str_replace(
                                                [
                                                    "'",
                                                    "\r",
                                                    "\n"
                                                ],
                                                [
                                                    "\"",
                                                    "",
                                                    ""
                                                ],
                                                $l_row_obj["isys_obj__title"]
                                            )
                                        );

                                        if ($p_multiSelection && !$p_relation_only) $l_title .= "</label>";

                                        if (!$p_relation_only)
                                        {
                                            if (empty($p_link))
                                            {
                                                $l_url = "javascript:select_object('" . $l_row_obj["isys_obj__id"] . "', '" . str_replace("'", "", $l_title) . "');";
                                            }
                                            else
                                            {
                                                $l_url = $p_link;
                                            }

                                            if (!empty($p_onclick))
                                            {
                                                $l_url = "javascript:" . $p_onclick;
                                            }
                                        }
                                        else
                                        {
                                            $l_url = false;
                                        }

                                        $l_url = str_replace("[{ID}]", $l_row_obj["isys_obj__id"], $l_url);

                                        if (!empty($l_row_obj["isys_obj_type__icon"]))
                                        {
                                            $l_icon = $g_dirs["images"] . "/tree/" . $l_row_obj["isys_obj_type__icon"];
                                        }
                                        else
                                        {
                                            $l_icon = "";
                                        }

                                        $this->m_object_count++;

                                        $l_node_obj = $p_tree->add_node(
                                            $l_tree++,
                                            $l_node_ot,
                                            $l_title,
                                            $l_url,
                                            "",
                                            $l_icon
                                        );

                                        /**
                                         * RELATION BEGIN
                                         */

                                        if ($p_relation)
                                        {
                                            $l_dao_rel   = new isys_cmdb_dao_category_g_relation($g_comp_database);
                                            $l_condition = " AND (isys_catg_relation_list__isys_obj__id__master = '" . $l_row_obj["isys_obj__id"] . "' OR isys_catg_relation_list__isys_obj__id__slave = '" . $l_row_obj["isys_obj__id"] . "') ";

                                            if ($p_relation_type != "")
                                            {
                                                $l_reltypes = explode(";", $p_relation_type);

                                                if (count($l_reltypes) > 0) $l_condition .= " AND (";

                                                foreach ($l_reltypes as $l_reltype)
                                                {
                                                    $l_condition .= "isys_catg_relation_list__isys_relation_type__id = " . $l_dao_cmdb->convert_sql_id(
                                                            constant($l_reltype)
                                                        ) . " OR ";
                                                }

                                                if (count($l_reltypes) > 0)
                                                {
                                                    $l_condition = substr($l_condition, 0, -4);
                                                    $l_condition .= ")";
                                                }

                                            }
                                            $l_res_rel = $l_dao_rel->get_data(null, null, $l_condition, null, $_GET["status"]);

                                            if ($l_res_rel->num_rows() > 0)
                                            {

                                                $l_category = null;

                                                while ($l_row_rel = $l_res_rel->get_row())
                                                {

                                                    $l_selected = false;
                                                    //$l_arr[$l_row_obj["isys_obj__id"]][] = $l_row_rel;

                                                    $l_relation_type = $l_dao_rel->get_relation_type(
                                                        $l_row_rel["isys_catg_relation_list__isys_relation_type__id"],
                                                        null,
                                                        true
                                                    );

                                                    $l_master_obj = $l_row_rel["isys_catg_relation_list__isys_obj__id__master"];
                                                    $l_slave_obj  = $l_row_rel["isys_catg_relation_list__isys_obj__id__slave"];

                                                    if (in_array($l_row_rel["isys_obj__id"], $p_object_id))
                                                    {
                                                        $p_bSelected = $l_tree;
                                                        $l_selected  = true;
                                                    }
                                                    else if ($p_multiSelection) $l_selected = false;

                                                    if ($l_row_obj["isys_obj__id"] == $l_master_obj)
                                                    {
                                                        $l_string_title = $l_dao_rel->get_obj_name_by_id_as_string($l_master_obj) . " " . _L(
                                                                $l_relation_type["isys_relation_type__master"]
                                                            ) . " " . $l_dao_rel->get_obj_name_by_id_as_string($l_slave_obj);
                                                    }
                                                    else
                                                    {
                                                        $l_string_title = $l_dao_rel->get_obj_name_by_id_as_string($l_slave_obj) . " " . _L(
                                                                $l_relation_type["isys_relation_type__slave"]
                                                            ) . " " . $l_dao_rel->get_obj_name_by_id_as_string($l_master_obj);
                                                    }

                                                    if ($p_multiSelection)
                                                    {
                                                        $l_title   = "<label>" . "<input " . "class=\"objectCheck vam\" " . "type=\"checkbox\" " . "name=\"object[]\" " . "onChange=\"refresh_selected();\" " . "value=\"{$l_row_rel["isys_obj__id"]}\" " . (($l_selected) ? "checked=\"checked\"" : "") . " /> ";
                                                        $p_onclick = null;
                                                        $p_link    = "javascript:;";
                                                    }
                                                    else $l_title = "";

                                                    $l_title .= ($l_string_title == "") ? "unnamed" : str_replace(
                                                        [
                                                            "'",
                                                            "\n",
                                                            "\r"
                                                        ],
                                                        [
                                                            "\"",
                                                            "",
                                                            ""
                                                        ],
                                                        htmlspecialchars($l_string_title)
                                                    );

                                                    if ($p_multiSelection) $l_title .= "</label>";

                                                    if (empty($p_link))
                                                    {
                                                        $l_url = "javascript:select_object('" . $l_row_rel["isys_obj__id"] . "', '" . str_replace("'", "", $l_title) . "');";
                                                    }
                                                    else
                                                    {
                                                        $l_url = $p_link;
                                                    }

                                                    if (!empty($p_onclick))
                                                    {
                                                        $l_url = "javascript:" . $p_onclick;
                                                    }

                                                    $l_url = str_replace("[{ID}]", $l_row_rel["isys_obj__id"], $l_url);

                                                    if (!empty($l_row_rel["isys_obj_type__icon"]))
                                                    {
                                                        $l_icon = $g_dirs["images"] . "/tree/" . $l_row_rel["isys_obj_type__icon"];
                                                    }
                                                    else
                                                    {
                                                        $l_icon = "";
                                                    }

                                                    $l_node_rel = $p_tree->add_node(
                                                        $l_tree++,
                                                        $l_node_obj,
                                                        $l_title,
                                                        $l_url,
                                                        "",
                                                        ""
                                                    );

                                                }
                                            }
                                            else
                                            {

                                                if ($p_relation_only) $p_tree->remove_node($l_tree - 1);

                                            }
                                        }
                                        /**
                                         * RELATION END
                                         */

                                    } // if status
                                } // while object
                            } // if objet numrows > 0
                        } // if showType
                    } // while object-types
                } // if object-type numrows > 0
            } // while object-group
        } // if object-group numrows > 0

        if ($p_relation)
        {

            $l_node_con = $p_tree->add_node(
                $l_tree++,
                $l_node_root,
                _L("LC__RELATION__PARALLEL_RELATIONS")
            );

            $l_prel_dao = new isys_cmdb_dao_category_s_parallel_relation($g_comp_database);
            $l_prels    = $l_prel_dao->get_data();

            while ($l_row = $l_prels->get_row())
            {

                if (is_null($p_link))
                {
                    $l_objgets                         = $_GET;
                    $l_objgets[C__CMDB__GET__OBJECT]   = $l_row["isys_obj__id"];
                    $l_objgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_OBJECT;

                    $l_url = isys_glob_build_url(isys_glob_http_build_query($l_objgets));
                }
                else
                {
                    // [[{ID}] is replaced!
                    $l_url = str_replace("[{ID}]", $l_row["isys_obj__id"], $p_link);
                }

                if ($_GET[C__CMDB__GET__OBJECT] == $l_row["isys_obj__id"])
                {
                    $p_bSelected = $l_tree;
                }

                if (!empty($l_row["isys_obj_type__icon"]))
                {
                    $l_icon = $g_dirs["images"] . "/tree/" . $l_row["isys_obj_type__icon"];
                }
                else
                {
                    $l_icon = "";
                }

                if ($p_multiSelection && !$p_relation_only)
                {
                    $l_title   = "<label>" . "<input " . "class=\"objectCheck vam\" " . "type=\"checkbox\" " . "name=\"object[]\" " . "onChange=\"refresh_selected();\" " . "value=\"{$l_row["isys_obj__id"]}\" " . (($l_selected) ? "checked=\"checked\"" : "") . " /> ";
                    $p_onclick = null;
                    $p_link    = "javascript:;";
                }
                else $l_title = "";

                $l_title .= htmlspecialchars(
                    str_replace(
                        "'",
                        "\"",
                        ($l_row["isys_cats_relpool_list__title"] == "") ? "unnamed" : $l_row["isys_cats_relpool_list__title"]
                    )
                );

                if ($p_multiSelection && !$p_relation_only) $l_title .= "</label>";

                $p_tree->add_node(
                    $l_tree++,
                    $l_node_con,
                    $l_title,
                    $l_url,
                    "",
                    $l_icon
                );

            }

        }

        return $p_tree->process($p_bSelected);
    }

    public function build_object_lun_tree(isys_component_template $p_tpl, isys_component_tree $p_tree, $p_bSelected, $p_link = null, $p_status = C__RECORD_STATUS__NORMAL, $p_onclick = null, $p_groupFilter = null, $p_object_id = null)
    {
        global $g_comp_database, $g_dirs;

        $l_tree           = 0;
        $l_dao_cmdb       = new isys_cmdb_dao($g_comp_database);
        $l_dao_ldevserver = new isys_cmdb_dao_category_g_sanpool($g_comp_database);

        if (is_null($p_object_id))
        {
            $p_object_id = $_GET[C__CMDB__GET__OBJECT];
        }

        if ($p_groupFilter != null)
        {
            $_GET["groupFilter"] = $p_groupFilter;
        }

        /* Root node "CMDB" */
        $l_node_root = $p_tree->add_node($l_tree++, C__CMDB__TREE_NODE__PARENT, "CMDB");

        // Check if type is in type filter (if specified)
        if (!empty($_GET["typeFilter"]))
        {
            $l_arTypes = explode(";", $_GET["typeFilter"]);
            if (is_array($l_arTypes))
            {
                foreach ($l_arTypes as $l_t)
                {
                    if (is_numeric($l_t))
                    {
                        $l_types[$l_t] = $l_t;
                    }
                    else
                    {
                        $l_types[constant($l_t)] = $l_t;
                    }
                }
            }
            else
            {
                $l_types[constant($_GET["typeFilter"])] = $_GET["typeFilter"];
            }
        }
        else
        {
            $l_types = null;
        }

        /* Evaluate object groups */
        $l_res_og = $l_dao_cmdb->objgroup_get();

        if ($l_res_og && $l_res_og->num_rows())
        {
            while ($l_row_og = $l_res_og->get_row())
            {

                if (isset($_GET["groupFilter"]) && !empty($_GET["groupFilter"]))
                {
                    $l_groups = explode(";", $_GET["groupFilter"]);

                    $l_stop = false;
                    foreach ($l_groups as $l_group)
                    {
                        if ($l_row_og["isys_obj_type_group__id"] == constant($l_group)) $l_stop = true;
                    }

                    if (!$l_stop) continue;
                }

                $l_res_ot = $l_dao_cmdb->objtype_get_by_objgroup_id($l_row_og["isys_obj_type_group__id"]);

                if ($l_res_ot && $l_res_ot->num_rows() > 0)
                {
                    /* Create node for object group, only if it has types */
                    $l_node_og = $p_tree->add_node($l_tree++, $l_node_root, _L($l_row_og["isys_obj_type_group__title"]));

                    while ($l_row_ot = $l_res_ot->get_row())
                    {
                        $l_showType = false;

                        if (is_array($l_types))
                        {
                            if (array_key_exists($l_row_ot["isys_obj_type__id"], $l_types)) $l_showType = true;
                        }
                        else
                        {
                            $l_showType = true;
                        }

                        /* Check if type has to be shown */
                        $l_showType = (!empty($l_row_ot["isys_obj_type__show_in_tree"]) && $l_showType);

                        if ($l_showType)
                        {
                            $l_res_obj = $this->get_obj_res($l_dao_cmdb, $l_row_ot["isys_obj_type__id"], $p_status, $l_types);

                            /* Only if type has objects, add node */
                            if ($l_res_obj && $l_res_obj->num_rows() > 0)
                            {

                                $l_res_objtypes_san = $l_dao_ldevserver->get_san_objecttypes();
                                $l_objtypes_san_arr = [];
                                $l_obj_san_arr      = [];

                                while ($l_row_objt_san = $l_res_objtypes_san->get_row())
                                {
                                    $l_objtypes_san_arr[] = $l_row_objt_san["isys_obj_type__id"];
                                    $l_obj_san_arr[]      = $l_row_objt_san["isys_obj__id"];
                                }

                                if (in_array($l_row_ot["isys_obj_type__id"], $l_objtypes_san_arr))
                                {

                                    $l_node_ot = $p_tree->add_node($l_tree++, $l_node_og, _L($l_row_ot["isys_obj_type__title"]));

                                    while ($l_row_obj = $l_res_obj->get_row())
                                    {

                                        if (($l_row_obj["isys_obj__status"] == $p_status || $l_row_obj["isys_obj__status"] == $_GET["status"] || $_GET["status"] == 0) && in_array(
                                                $l_row_obj["isys_obj__id"],
                                                $l_obj_san_arr,
                                                false
                                            )
                                        )
                                        {

                                            $l_title = ($l_row_obj["isys_obj__title"] == "") ? "unnamed" : $l_row_obj["isys_obj__title"];
                                            $l_title = str_replace("'", "\"", $l_title);
                                            $l_title = htmlspecialchars($l_title);

                                            if ($p_object_id == $l_row_obj["isys_obj__id"])
                                            {
                                                $p_bSelected = $l_tree;
                                            }

                                            if (!empty($l_row_obj["isys_obj_type__icon"]))
                                            {
                                                $l_icon = $g_dirs["images"] . "/tree/" . $l_row_obj["isys_obj_type__icon"];
                                            }
                                            else
                                            {
                                                $l_icon = "";
                                            }

                                            $this->m_object_count++;

                                            $l_node_obj = $p_tree->add_node($l_tree++, $l_node_ot, $l_title);

                                            $l_ldev_res = $l_dao_ldevserver->get_ldevserver_by_obj_id_or_ldev_id($l_row_obj["isys_obj__id"]);

                                            while ($l_ldev_row = $l_ldev_res->get_row())
                                            {
                                                $l_title = ($l_ldev_row["isys_catg_sanpool_list__title"] == "") ? "unnamed" : $l_ldev_row["isys_catg_sanpool_list__title"] . " (LUN: " . $l_ldev_row["isys_catg_sanpool_list__lun"] . ")";
                                                $l_title = str_replace("'", "\"", $l_title);
                                                $l_title = htmlspecialchars($l_title);

                                                if (empty($p_link))
                                                {
                                                    $l_url = "javascript:select_lun('" . $l_ldev_row["isys_catg_sanpool_list__id"] . "' ,'" . str_replace(
                                                            "'",
                                                            "",
                                                            $l_title
                                                        ) . "');";
                                                }
                                                else
                                                {
                                                    $l_url = $p_link;
                                                }

                                                if (!empty($p_onclick))
                                                {
                                                    $l_url = "javascript:" . $p_onclick;
                                                }

                                                //$l_url = str_replace("[{ID}]", $l_ldev_row["isys_catg_sanpool_list__id"], $l_url);

                                                $p_tree->add_node($l_tree++, $l_node_obj, $l_title, $l_url, "", $l_icon);
                                            }

                                        } // if status
                                    } // while object
                                }
                            } // if objet numrows > 0
                        } // if showType
                    } // while object-types
                } // if object-type numrows > 0
            } // while object-group
        } // if object-group numrows > 0

        return $p_tree->process($p_bSelected);
    }

    public function build_location_lun_tree(isys_component_template $p_tpl, isys_component_tree $p_tree, $p_selectedClients)
    {
        global $g_comp_database, $g_dirs, $g_comp_template_language_manager;

        $l_selected = null;

        /* Root node */
        $l_node_root = $p_tree->add_node(1, -1, $g_comp_template_language_manager->{"LC__CMDB__TREE__LOCATION"});

        /* Create special location DAO */
        $l_dao_loc        = new isys_cmdb_dao_location($g_comp_database);
        $l_dao_ldevserver = new isys_cmdb_dao_category_g_sanpool($g_comp_database);

        /* Get location tree */
        $l_locdata = $l_dao_loc->get_locations_by_obj_id($l_dao_loc->get_root_location_as_integer());

        // Check if type is in type filter (if specified)
        if (!empty($_GET["typeFilter"]))
        {
            $l_arTypes = explode(";", $_GET["typeFilter"]);
            if (is_array($l_arTypes))
            {
                foreach ($l_arTypes as $l_t)
                {
                    $l_types[constant($l_t)] = $l_t;
                }
            }
            else
            {
                $l_types[constant($_GET["typeFilter"])] = $_GET["typeFilter"];
            }
        }
        else
        {
            $l_types = null;
        }

        /* Iterate through location tree and build up tree */
        foreach ($l_locdata as $l_location)
        {
            static $l_isroot = true;

            if (is_array($l_types))
            {
                if (isset($l_types[$l_location[0]]))
                {
                    $l_showType = true;
                }
                else $l_showType = false;
            }
            else
            {
                $l_showType = true;
            }

            /* Handle root object */
            if ($l_isroot)
            {
                /* First location object is root */
                $l_location[1] = $l_node_root;
                $l_isroot      = false;
                continue;
            }

            $l_title = $l_location[4];

            if ($p_selectedClients == $l_location[0])
            {
                $l_selected = $l_location[0];
            }

            $l_obj_type = $l_dao_loc->get_objTypeID($l_location[0]);
            $l_type_row = $l_dao_loc->get_type_by_id($l_obj_type);

            if ($l_type_row["isys_obj_type__icon"])
            {
                $l_icon = $l_type_row["isys_obj_type__icon"];
            }
            else
            {
                $l_icon = "";
            }

            if ($l_icon)
            {
                $l_icon = $g_dirs["images"] . "tree/" . $l_icon;
            }

            $l_locgets                         = $_GET;
            $l_locgets[C__CMDB__GET__OBJECT]   = $l_location[0];
            $l_locgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_LOCATION;

            if ($_GET[C__CMDB__GET__OBJECT] == $l_location[0]) $l_selected = $l_location[0];

            if ($l_showType)
            {
                $l_resServer = $l_dao_ldevserver->get_ldevserver_by_obj_id_or_ldev_id($l_location[0]);

                // Add tree node
                $l_node_obj = $p_tree->add_node($l_location[0], $l_location[1], $l_title, "", "", $l_icon);

                if ($l_resServer->num_rows() > 0)
                {
                    while ($l_row = $l_resServer->get_row())
                    {

                        $l_title = ($l_row["isys_catg_sanpool_list__title"] == "") ? "unnamed" : $l_row["isys_catg_sanpool_list__title"] . " (LUN: " . $l_row["isys_catg_sanpool_list__lun"] . ")";
                        $l_title = str_replace("'", "\"", $l_title);
                        $l_title = htmlspecialchars($l_title);

                        if (empty($p_link))
                        {
                            $l_url = "javascript:select_lun('" . $l_row["isys_catg_sanpool_list__id"] . "' ,'" . str_replace("'", "", $l_title) . "');";
                        }
                        else
                        {
                            $l_url = $p_link;
                        }

                        $p_tree->add_node(
                            99999 + $l_row["isys_catg_sanpool_list__id"],
                            $l_node_obj,
                            $l_title,
                            $l_url,
                            "",
                            $l_icon
                        );
                    }
                }
            }
        }

        return $p_tree->process($l_selected);
    }

    /**
     * Build an object tree
     *
     * @param isys_component_template $p_tpl
     * @param isys_component_tree     $p_tree
     * @param bool                    $p_bSelected
     * @param unknown_type            $p_link
     * @param integer                 $p_status
     *
     * @version NP 2007-10-18
     * @return string
     */
    public function build_client_tree(isys_component_template $p_tpl, isys_component_tree $p_tree, $p_bSelected, $p_link = null, $p_status = C__RECORD_STATUS__NORMAL, $p_selectedClients)
    {
        global $g_comp_database, $g_dirs;

        $l_tree      = 0;
        $l_dao_cmdb  = new isys_cmdb_dao($g_comp_database);
        $l_daoClient = new isys_cmdb_dao_category_g_ldevclient($g_comp_database);

        /* Root node "CMDB" */
        $l_node_root = $p_tree->add_node($l_tree++, C__CMDB__TREE_NODE__PARENT, "CMDB");

        // Check if type is in type filter (if specified)
        if (!empty($_GET["typeFilter"]))
        {
            $l_arTypes = explode(";", $_GET["typeFilter"]);
            if (is_array($l_arTypes))
            {
                foreach ($l_arTypes as $l_t)
                {
                    if (is_numeric($l_t))
                    {
                        $l_types[$l_t] = $l_t;
                    }
                    else
                    {
                        $l_types[constant($l_t)] = $l_t;
                    }
                }
            }
            else
            {
                $l_types[constant($_GET["typeFilter"])] = $_GET["typeFilter"];
            }
        }
        else
        {
            $l_types = null;
        }

        /* Evaluate object groups */
        $l_res_og = $l_dao_cmdb->objgroup_get();

        if ($l_res_og && $l_res_og->num_rows())
        {
            while ($l_row_og = $l_res_og->get_row())
            {

                if (isset($_GET["groupFilter"]) && !empty($_GET["groupFilter"]))
                {
                    $l_groups = explode(";", $_GET["groupFilter"]);

                    $l_stop = false;
                    foreach ($l_groups as $l_group)
                    {
                        if ($l_row_og["isys_obj_type_group__id"] == constant($l_group)) $l_stop = true;
                    }

                    if (!$l_stop) continue;
                }

                $l_res_ot = $l_dao_cmdb->objtype_get_by_objgroup_id($l_row_og["isys_obj_type_group__id"]);

                if ($l_res_ot && $l_res_ot->num_rows() > 0)
                {
                    /* Create node for object group, only if it has types */
                    $l_node_og = $p_tree->add_node($l_tree++, $l_node_root, _L($l_row_og["isys_obj_type_group__title"]));

                    while ($l_row_ot = $l_res_ot->get_row())
                    {
                        $l_showType = false;

                        if (is_array($l_types))
                        {
                            if (array_key_exists($l_row_ot["isys_obj_type__id"], $l_types)) $l_showType = true;
                        }
                        else
                        {
                            $l_showType = true;
                        }

                        /* Check if type has to be shown */
                        $l_showType = (!empty($l_row_ot["isys_obj_type__show_in_tree"]) && $l_showType);

                        if ($l_showType)
                        {
                            $l_res_obj = $l_daoClient->get_clients_by_object_type($l_row_ot["isys_obj_type__id"], $p_status);

                            /* Only if type has objects, add node */
                            if ($l_res_obj && $l_res_obj->num_rows() > 0)
                            {

                                $l_node_ot = $p_tree->add_node($l_tree++, $l_node_og, _L($l_row_ot["isys_obj_type__title"]));

                                while ($l_row_obj = $l_res_obj->get_row())
                                {

                                    if ($l_row_obj["isys_obj__status"] == $p_status || $l_row_obj["isys_obj__status"] == $_GET["status"] || $_GET["status"] == 0)
                                    {
                                        $l_title = ($l_row_obj["isys_obj__title"] == "") ? "unnamed" : $l_row_obj["isys_obj__title"];
                                        $l_title = str_replace("'", "\"", $l_title);
                                        $l_title = htmlspecialchars($l_title);

                                        if (!empty($l_row_obj["isys_obj_type__icon"]))
                                        {
                                            $l_icon = $g_dirs["images"] . "/tree/" . $l_row_obj["isys_obj_type__icon"];
                                        }
                                        else
                                        {
                                            $l_icon = "";
                                        }

                                        $l_resClient = $l_daoClient->get_clients_by_object($l_row_obj["isys_obj__id"]);

                                        if ($l_resClient && $l_resClient->num_rows() > 0)
                                        {
                                            $l_node_object = $p_tree->add_node($l_tree++, $l_node_ot, $l_title, "", "", $l_icon);

                                            while ($l_rowClient = $l_resClient->get_row())
                                            {
                                                $l_title = $l_rowClient["isys_catg_ldevclient_list__title"];
                                                $l_title = str_replace("'", "\"", $l_title);
                                                $l_title = htmlspecialchars($l_title);

                                                $l_title = "<label>" . "<input " . "class=\"vam\" " . "type=\"checkbox\" " . "name=\"clientsSelected[]\" " . "value=\"" . $l_rowClient["isys_catg_ldevclient_list__id"] . "\" " . "onChange=\"refresh_selected('obj')\" " . (in_array(
                                                        $l_rowClient["isys_catg_ldevclient_list__id"],
                                                        $p_selectedClients,
                                                        false
                                                    ) ? "checked=\"checked\"" : "") . " />" . " " . isys_glob_str_stop(
                                                        $l_rowClient["isys_catg_ldevclient_list__title"],
                                                        50
                                                    ) . "</label>";

                                                $p_tree->add_node($l_tree++, $l_node_object, $l_title);

                                                $this->m_object_count++;
                                            }
                                        }
                                    } // if status
                                } // while object
                            } // if objet numrows > 0
                        } // if showType
                    } // while object-types
                } // if object-type numrows > 0
            } // while object-group
        } // if object-group numrows > 0

        return $p_tree->process($p_bSelected);
    }

    public function build_client_location_tree(isys_component_template $p_tpl, isys_component_tree $p_tree, $p_selectedClients)
    {
        global $g_comp_database, $g_dirs, $g_comp_template_language_manager;

        $l_selected = null;

        /* Root node */
        $l_node_root = $p_tree->add_node(1, -1, $g_comp_template_language_manager->{"LC__CMDB__TREE__LOCATION"});

        /* Create special location DAO */
        $l_dao_loc   = new isys_cmdb_dao_location($g_comp_database);
        $l_daoClient = new isys_cmdb_dao_category_g_ldevclient($g_comp_database);

        /* Get location tree */
        $l_locdata = $l_dao_loc->get_locations_by_obj_id($l_dao_loc->get_root_location_as_integer());

        // Check if type is in type filter (if specified)
        if (!empty($_GET["typeFilter"]))
        {
            $l_arTypes = explode(";", $_GET["typeFilter"]);
            if (is_array($l_arTypes))
            {
                foreach ($l_arTypes as $l_t)
                {
                    $l_types[constant($l_t)] = $l_t;
                }
            }
            else
            {
                $l_types[constant($_GET["typeFilter"])] = $_GET["typeFilter"];
            }
        }
        else
        {
            $l_types = null;
        }

        /* Iterate through location tree and build up tree */
        foreach ($l_locdata as $l_location)
        {
            static $l_isroot = true;

            if (is_array($l_types))
            {
                if (isset($l_types[$l_location[0]]))
                {
                    $l_showType = true;
                }
                else $l_showType = false;
            }
            else
            {
                $l_showType = true;
            }

            /* Handle root object */
            if ($l_isroot)
            {
                /* First location object is root */
                $l_location[1] = $l_node_root;
                $l_isroot      = false;
                continue;
            }

            $l_title = $l_location[4];

            if ($p_selectedClients == $l_location[0])
            {
                $l_selected = $l_location[0];
            }

            $l_obj_type = $l_dao_loc->get_objTypeID($l_location[0]);
            $l_type_row = $l_dao_loc->get_type_by_id($l_obj_type);

            if ($l_type_row["isys_obj_type__icon"])
            {
                $l_icon = $l_type_row["isys_obj_type__icon"];
            }
            else
            {
                $l_icon = "";
            }

            if ($l_icon)
            {
                $l_icon = $g_dirs["images"] . "tree/" . $l_icon;
            }

            $l_locgets                         = $_GET;
            $l_locgets[C__CMDB__GET__OBJECT]   = $l_location[0];
            $l_locgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_LOCATION;

            if ($_GET[C__CMDB__GET__OBJECT] == $l_location[0]) $l_selected = $l_location[0];

            if ($l_showType)
            {
                $l_resClient = $l_daoClient->get_clients_by_object($l_location[0]);

                // Add tree node
                $p_tree->add_node($l_location[0], $l_location[1], $l_title, "", "", $l_icon);

                if ($l_resClient->num_rows() > 0)
                {
                    while ($l_row = $l_resClient->get_row())
                    {
                        $l_title = "<label>" . "<input " . "class=\"vam\" " . "type=\"checkbox\" " . "name=\"clientsSelectedLocation[]\" " . "value=\"" . $l_row["isys_catg_ldevclient_list__id"] . "\" " . "onChange=\"refresh_selected('loc')\" " . (in_array(
                                $l_row["isys_catg_ldevclient_list__id"],
                                $p_selectedClients,
                                false
                            ) ? "checked=\"checked\"" : "") . " />" . " " . isys_glob_str_stop($l_row["isys_catg_ldevclient_list__title"], 50) . "</label>";

                        $p_tree->add_node(999999 + $l_row["isys_catg_ldevclient_list__id"], $l_location[0], $l_title);
                    }
                }
            }
        }

        return $p_tree->process($l_selected);
    }

    public function get_object_count()
    {
        return $this->m_object_count;
    }

    protected function xml_create_column($p_id, $p_childnodes)
    {
        $l_xml_column = new isys_component_xml_node("column");
        if ($l_xml_column)
        {
            $l_xml_column->setAttribute(["id" => $p_id]);
            foreach ($p_childnodes as $l_key => $l_data)
            {
                $l_xml_column->addXmlObj(new isys_component_xml_element($l_key, $l_data));
            }

            return $l_xml_column;
        }

        return null;
    }

    /**
     * Get result set of objects
     *
     * @param isys_cmdb_dao $p_dao
     * @param integer       $p_nObjTypeID
     * @param integer       $p_nRecStatus
     */
    protected function get_obj_res(isys_cmdb_dao $p_dao, $p_nObjTypeID, $p_nRecStatus = C__RECORD_STATUS__NORMAL)
    {
        return $p_dao->get_objects_by_type_id($p_nObjTypeID, $p_nRecStatus);
    }

    private function check_condition(isys_cmdb_dao $p_dao, $p_table, $p_column, $p_value, $p_obj_id, $p_connection = false, $p_condition = "AND", $p_operator = "=")
    {
        try
        {

            if ($p_connection)
            {
                $l_query = "SELECT " . $p_table . "__id FROM " . $p_table . " " . "INNER JOIN isys_connection ON isys_connection__id = " . $p_table . "__isys_connection__id " . "WHERE " . $p_table . "__isys_obj__id = " . $p_obj_id . " ";
            }
            else
            {
                $l_query = "SELECT " . $p_table . "__id FROM " . $p_table . " WHERE " . $p_table . "__isys_obj__id = " . $p_obj_id . " ";
            }

            if ($p_value != "")
            {
                $l_query .= $p_condition . " " . $p_column . " " . $p_operator . " '" . $p_value . "'";
            }
            else
            {
                $l_query .= $p_condition . " " . $p_column . " !" . $p_operator . " '" . $p_value . "'";
            }
            //echo $l_query;
            $l_res = $p_dao->retrieve($l_query);

            if ($l_res->num_rows() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch (Exception $e)
        {
            return true;
        }
    }

    public function __construct()
    {
        parent::__construct();
    }
}

?>