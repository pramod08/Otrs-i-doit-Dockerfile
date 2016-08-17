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
 * Popup class for object browser
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_popup_browser_object extends isys_popup_browser
{

    /**
     * Display quickinfo in format_selection
     *
     * @var bool
     */
    private $m_format_quick_info = true;

    /**
     * @param bool|true $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_quick_info($bool = true)
    {
        $this->m_format_quick_info = $bool;

        return $this;
    }

    /*
    * @return string
     * @global $g_dirs
     * @global $g_config
     * @param isys_component_template& $p_tplclass
    * @param $p_table
    * @author Andre Woesten <awoesten@i-doit.org> - 2006-04-04
    * @desc Handles SMARTY request for object browser
    */
    /**
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     * @param bool                    $p_onclick_only
     *
     * @return string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params, $p_onclick_only = false)
    {
        global $g_dirs;
        global $g_config;
        global $g_comp_database;
        global $g_comp_template_language_manager;

        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_url            = "";
        $l_strOut         = "";
        $l_strHiddenField = "";

        if ($p_params["p_strValue"])
        {
            $l_object_id = $p_params["p_strValue"];
        }
        else if ($p_params["p_strSelectedID"])
        {
            $l_object_id = $p_params["p_strSelectedID"];
        }

        $l_url = $g_config["startpage"] . "?mod=cmdb" . "&popup=browser_object" . "&form_submit=" . $p_params["p_strFormSubmit"] . "&resultField=" . $p_params["name"] . "&multiSelection=" . $p_params["p_multiSelection"] . "&relation=" . $p_params["p_relation"] . "&groupFilter=" . $p_params["groupFilter"];

        if (isset($p_params["js_callback"]))
        {
            $l_url .= "&js_callback=" . base64_encode($p_params["js_callback"]);
        }

        if (!empty($l_object_id))
        {
            $l_url .= "&objID=" . $l_object_id;
        }

        if (!empty($p_params["p_arTypeFilter"]))
        {
            $l_url .= "&typeFilter=" . $p_params["p_arTypeFilter"];
        }

        // Category Filter
        if (!empty($p_params["catFilter"]))
        {
            $l_url .= "&catFilter=" . $p_params["catFilter"];
        }

        if (!empty($p_params["catFilterType"]))
        {
            $l_url .= "&catFilterType=" . $p_params["catFilterType"];
        }

        if (!empty($p_params["catCondition"]))
        {
            $l_url .= "&catCondition=" . $p_params["catCondition"];
        }

        if (!empty($p_params["catConditionColumn"]))
        {
            $l_url .= "&catConditionColumn=" . $p_params["catConditionColumn"];
        }

        if (!empty($p_params["catConditionValue"]))
        {
            $l_url .= "&catConditionValue=" . $p_params["catConditionValue"];
        }

        if (!empty($p_params["p_relation_only"]))
        {
            $l_url .= "&relation_only=" . $p_params["p_relation_only"];
        }

        if (!empty($p_params["p_relation_type"]))
        {
            $l_url .= "&relation_type=" . $p_params["p_relation_type"];
        }

        $l_view   = $p_params["name"] . "__VIEW";
        $l_hidden = $p_params["name"] . "__HIDDEN";

        /* Set dimensions of browser */
        $this->set_config("width", 800);
        $this->set_config("height", 625);

        if (!isset($p_params["nohidden"]))
        {
            $l_strHiddenField .= "<input " . "name=\"" . $l_hidden . "\" " . "id=\"" . $l_hidden . "\" " . "type=\"hidden\" " . "value=\"" . $l_object_id . "\" " . "/>";
        }

        /* Set parameters for the f_text plug-in */
        //$p_params["p_bReadonly"] 	= "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON || isset($p_params["edit"])) && !isset($p_params["plain"]))
        {

            $p_params["name"]       = $l_view;
            $p_params["p_strValue"] = $this->set_format_quick_info(false)
                ->format_selection($l_object_id);

            /**
             * Auto Suggesstion
             */
            if (!isset($p_params["p_multiSelection"]) || !$p_params["p_multiSelection"])
            {
                $p_params["p_onClick"]              = "if (this.value == '" . $p_params["p_strValue"] . "') this.value = '';";
                $p_params["p_strSuggest"]           = "object";
                $p_params["p_strSuggestView"]       = $l_view;
                $p_params["p_strSuggestHidden"]     = $l_hidden;
                $p_params["p_strSuggestParameters"] = "parameters: { " . "typeFilter: '" . $p_params["p_arTypeFilter"] . "', " . "groupFilter: '" . $p_params["groupFilter"] . "' " . "}";
            }
            else $p_params["p_bReadonly"] = 1;

            if ($p_params["id"]) $l_id = $p_params["id"];
            else
            {
                $l_id           = $l_view;
                $p_params["id"] = $l_id;
            }

            $l_detach_callback = isset($p_params["p_strDetachCallback"]) ? $p_params["p_strDetachCallback"] : "";
            $l_onclick_detach  = "var e_view = $('" . $l_id . "'), " . "e_hidden = $('" . $l_hidden . "');" .

                "if(e_view && e_hidden) {" . "e_view.value = '" . $g_comp_template_language_manager->{"LC__UNIVERSAL__CONNECTION_DETACHED"} . "!'; " . "e_hidden.value = '';" . "}" . $l_detach_callback;

            $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $p_params);

            if (!isset($p_params["p_bDisabled"]) && !$p_params["p_bDisabled"])
            {

                $l_strOut .= '<a href="javascript:" title="' . _L("LC__UNIVERSAL__ATTACH") . '" class="ml5" onClick="' . $this->process(
                        $l_url,
                        true
                    ) . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt="Open browser" />' . '</a>';

                if (!isset($p_params["p_bDisableDetach"]))
                {
                    $l_strOut .= '<a href="javascript:" title="' . _L(
                            "LC__UNIVERSAL__DETACH"
                        ) . '" class="ml5" onClick="' . $l_onclick_detach . ';" >' . "<img src=\"" . $g_dirs["images"] . "icons/silk/detach.png\" alt=\"Detach\" />" . "</a>";
                }

            }

            $l_strOut .= $l_strHiddenField;

        }
        else
        {

            $p_params["p_strValue"] = $this->set_format_quick_info(true)
                ->format_selection($l_object_id);

            $l_dao = new isys_cmdb_dao($g_comp_database);

            if ($l_object_id > 0)
            {

                //$l_strOut.= $l_objPlugin->getInfoIcon($p_params) . $p_params["p_strValue"];
                $l_strOut .= $p_params["p_strValue"];
            }
            else
            {

                $l_strOut .= $l_objPlugin->navigation_view($p_tplclass, $p_params);
            }

            $l_strOut .= $l_strHiddenField;
        }

        /*return onclick, or full html*/
        if ($p_onclick_only == true)
        {
            return $this->process($l_url, true);
        }
        else
        {
            return $l_strOut;
        }
    }

    /**
     * @param null $p_objid
     * @param bool $p_quick_info
     * @param bool $p_editmode
     *
     * @return string
     */
    public function format_selection($p_id, $p_unused = false)
    {
        global $g_comp_database;
        global $g_comp_template_language_manager;

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (empty($p_objid)) return $g_comp_template_language_manager->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");

        if (strstr($p_objid, ","))
        {
            $l_obj_ids = explode(",", $p_objid);
        }
        else
        {
            $l_obj_ids = [$p_objid];

        }

        /* We need a DAO for the object name */
        $l_dao_cmdb   = new isys_cmdb_dao($g_comp_database);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_return = '';
        foreach ($l_obj_ids as $l_obj_id)
        {

            if ($this->m_format_quick_info)
            {
                $l_return .= $l_objPlugin->getInfoIcon([]);
                if (count($l_obj_ids) > 1)
                {
                    $l_return .= $l_quick_info->get_quick_info(
                            $l_obj_id,
                            $l_dao_cmdb->get_obj_name_by_id_as_string($l_obj_id),
                            C__LINK__OBJECT
                        ) . "<br>  ";
                }
                else
                {
                    $l_return .= $l_quick_info->get_quick_info(
                            $l_obj_id,
                            $l_dao_cmdb->get_obj_name_by_id_as_string($l_obj_id),
                            C__LINK__OBJECT
                        ) . ", ";
                }
            }
            else
            {
                $l_return .= _L($l_dao_cmdb->get_objtype_name_by_id_as_string($l_dao_cmdb->get_objTypeID($l_obj_id))) . " >> " . $l_dao_cmdb->get_obj_name_by_id_as_string(
                        $l_obj_id
                    ) . ", ";
            }
        }

        return substr($l_return, 0, -2);
    }

    /**
     * @return isys_component_template&
     *
     * @param isys_module_request $p_modreq
     *
     * @desc ...
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;
        global $g_dirs;
        global $g_comp_template;
        global $g_comp_template_language_manager;

        if ((isset($_GET["ajax"]) && $_GET["ajax"] == "1") && isset($_POST["filter"]))
        {
            die($this->process_ajax_request($_POST));
        }

        /* Unpack module request */
        $l_gets     = $p_modreq->get_gets();
        $l_tplclass = $p_modreq->get_template();

        /* We need a DAO for the object name */
        $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

        /* Prepare new template for popup */
        $l_tplpopup = $g_comp_template;

        /* Prepare trees */
        $l_tree_loc = isys_component_tree::factory('g_browser_loc');
        $l_tree_obj = isys_component_tree::factory('g_browser_obj');

        if ($l_tplpopup && $l_tree_loc && $l_tree_obj)
        {

            $l_tplpopup->assign("viewMode", $_GET[C__CMDB__GET__VIEWMODE]);
            $l_tplpopup->assign("objID", $_GET[C__CMDB__GET__OBJECT]);
            $l_tplpopup->assign("js_callback", base64_decode($_GET["js_callback"]));
            $l_tplpopup->assign("typeFilter", $_GET["typeFilter"]);
            $l_tplpopup->assign("groupFilter", $_GET["groupFilter"]);
            $l_tplpopup->assign("multiSelection", $_GET["multiSelection"]);
            $l_tplpopup->assign("relation", $_GET["relation"]);
            $l_tplpopup->assign("relation_only", $_GET["relation_only"]);

            /* If object ID isset, set object details and object name for selection */
            if (isset($_GET[C__CMDB__GET__OBJECT]))
            {
                /* Prepare module request in order to forward request for
                   object details to CMDB view */
                $p_modreq->_internal_set_private("m_template", $l_tplpopup);

                if (($l_object_type = $l_dao_cmdb->get_objTypeID($_GET[C__CMDB__GET__OBJECT])) != C__OBJTYPE__CONTAINER)
                {
                    $l_catobj = new isys_cmdb_view_category($p_modreq);
                    $l_catobj->overview_process("tom.details");

                    $l_tplpopup->assign("showdetails", true);
                }

                $l_tplpopup->assign(
                    "selFull",
                    $this->format_selection(
                        $_GET[C__CMDB__GET__OBJECT],
                        $l_object_type
                    )
                );

                if ($_GET["show"] == "details")
                {
                    $l_tplpopup->display("popup/object_detail.tpl");
                    die;
                }
            }

            /* Build & assign trees */
            if (!$_GET["multiSelection"])
            {
                $l_tplpopup->assign("treeLocation", $this->build_location_tree($l_tplclass, $l_tree_loc, false));
            }

            if (!isset($_GET["status"]) || is_null($_GET["status"]))
            {
                $_GET["status"] = C__RECORD_STATUS__NORMAL;
            }

            $l_tree_obj = $this->build_object_tree(
                $l_tplclass,
                $l_tree_obj,
                null,
                null,
                $_GET["status"],
                null,
                null,
                $_GET[C__CMDB__GET__OBJECT],
                $_GET["multiSelection"],
                $_GET["relation"],
                $_GET["relation_only"],
                $_GET["relation_type"]
            );

            $l_object_count = $this->get_object_count();

            if ($l_object_count < C__TREE_MAX_OBJECTS)
            {
                $l_tplpopup->assign("treeObject", $l_tree_obj);
                $l_tplpopup->assign("refresh_selection", true);
            }
            else
            {
                $l_tplpopup->assign(
                    "message",
                    "<p>" . str_replace(
                        [
                            "\\n",
                            "\\"
                        ],
                        [
                            "<br />",
                            ""
                        ],
                        sprintf($g_comp_template_language_manager->{"LC__OBJECT_BROWSER__TOO_MANY_OBJECTS"}, C__TREE_MAX_OBJECTS)
                    ) . "</p>" . "<script type=\"text/javascript\">" . "var activate_filter = 1;" . "</script>"
                );

                $l_tplpopup->assign("refresh_selection", false);
            }

            /* Assign form submit */
            $l_tplpopup->assign("g_form_submit", $_GET["form_submit"]);
            $l_tplpopup->assign("object_count", $l_object_count);

            /* Status combo box */
            $l_arData = [
                0                          => $g_comp_template_language_manager->{LC__CMDB__RECORD_STATUS__ALL},
                C__RECORD_STATUS__NORMAL   => $g_comp_template_language_manager->{LC__CMDB__RECORD_STATUS__NORMAL},
                C__RECORD_STATUS__ARCHIVED => $g_comp_template_language_manager->{LC__CMDB__RECORD_STATUS__ARCHIVED},
                C__RECORD_STATUS__DELETED  => $g_comp_template_language_manager->{LC__CMDB__RECORD_STATUS__DELETED}
            ];

            if (!isset($l_gets["status"]) || is_null($l_gets["status"]))
            {
                $l_gets["status"] = C__RECORD_STATUS__NORMAL;
            }

            $l_tplpopup->smarty_tom_add_rule("tom.status.cRecStatus.p_strSelectedID=" . $l_gets["status"]);
            $l_tplpopup->smarty_tom_add_rule("tom.status.cRecStatus.p_arData=" . serialize($l_arData));

            /* Build URL for status combo box */
            $l_statgets                         = $l_gets;
            $l_statgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_OBJECT;
            unset($l_statgets["status"], $l_statgets["js_callback"]);

            /* No Viewmode, use object view */
            if (!isset($_GET[C__CMDB__GET__VIEWMODE]))
            {
                $_GET[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_OBJECT;
            }

            $l_tplpopup->assign(
                "statusURL",
                html_entity_decode(
                    isys_glob_build_url(
                        isys_glob_http_build_query($l_statgets)
                    )
                )
            );

            /* Create location browser popup */
            $l_tplpopup->assign("file_body", "popup/object.tpl");

            /* Yes we return the template. */

            return $l_tplpopup;
        }

        return ISYS_NULL;
    }

    /**
     * @param $p_params
     */
    public function process_ajax_request($p_params)
    {
        global $g_comp_database, $g_comp_template_language_manager, $g_comp_session;

        $l_show_relation = false;
        $l_relation_only = false;

        if (!isset($p_params["status"]) || is_null($p_params["status"]))
        {
            $p_params["status"] = C__RECORD_STATUS__NORMAL;
        }

        $l_dao               = new isys_cmdb_dao($g_comp_database);
        $l_additional_filter = '';

        if ($p_params["multiSelection"] && !empty($p_params["selID"]) && $p_params["selID"] != "") $l_additional_filter .= " OR isys_obj__id IN (" . $p_params["selID"] . ") ";
        else
            $l_additional_filter .= "";

        if ($p_params["relation"] == 1) $l_show_relation = true;

        if ($p_params["relation_only"] == 1) $l_relation_only = true;

        $l_objects = $l_dao->search_objects($p_params["filter"], $p_params["typeFilter"], $p_params["groupFilter"], $l_additional_filter, $l_show_relation, $l_relation_only);

        if ($l_objects->num_rows() > 0)
        {

            $l_html_selected = $l_html = "<table class=\"listing\">" . "<colgroup>" . "<col width=\"450\" />" . "<col width=\"250\" />" . "</colgroup>" . "<thead>" . "<tr>" . "<th>" . $g_comp_template_language_manager->{"LC__CATG__ODEP_OBJ"} . "</th>" . "<th>" . $g_comp_template_language_manager->{"LC__CMDB__OBJTYPE"} . "</th>" . "<th>Status</th>" . "</tr>" . "</thead>" . "<tbody>";
            $l_counter       = 0;
            while ($l_row = $l_objects->get_row())
            {
                if (!in_array($l_row["isys_obj__id"], explode(",", $p_params["selID"])))
                {
                    $l_counter++;
                    $l_html .= "<tr style=\"background:#fff;\">";

                    if ($p_params["multiSelection"] == 1)
                    {
                        // IE has problems with onchange so we use onclick
                        $l_html .= "<td><input type=\"checkbox\" " . ((in_array(
                                $l_row["isys_obj__id"],
                                explode(",", $p_params["selID"])
                            )) ? "checked=\"checked\"" : "") . " name=\"object[]\" class=\"objectCheck vam\" value=\"" . $l_row["isys_obj__id"] . "\" onclick=\"refresh_selected()\">" . $l_row["isys_obj__title"] . "</td>";
                    }
                    else
                    {
                        $l_html .= "<td>" . "<a href=\"javascript:select_object('" . $l_row["isys_obj__id"] . "', '" . str_replace(
                                "'",
                                "",
                                $l_row["isys_obj__title"]
                            ) . "');\">" . $l_row["isys_obj__title"] . "</a>" . "</td>";
                    }

                    $l_html .= "<td>" . $g_comp_template_language_manager->{$l_dao->get_objtype_name_by_id_as_string(
                            $l_dao->get_objTypeID($l_row["isys_obj__id"])
                        )} . "</td>" . "<td>" . $l_dao->get_object_status_by_id_as_string($l_row["isys_obj__id"]) . "</td>" . "</tr>";
                }
                else
                {
                    $l_html_selected .= "<tr style=\"background:#fff;\">";
                    if ($p_params["multiSelection"] == 1)
                    {
                        // IE has problems with onchange so we use onclick
                        $l_html_selected .= "<td><input type=\"checkbox\" " . ((in_array(
                                $l_row["isys_obj__id"],
                                explode(",", $p_params["selID"])
                            )) ? "checked=\"checked\"" : "") . " name=\"object[]\" class=\"objectCheck vam\" value=\"" . $l_row["isys_obj__id"] . "\" onclick=\"refresh_selected()\">" . $l_row["isys_obj__title"] . "</td>";
                    }
                    else
                    {
                        $l_html_selected .= "<td>" . "<a href=\"javascript:select_object('" . $l_row["isys_obj__id"] . "', '" . str_replace(
                                "'",
                                "",
                                $l_row["isys_obj__title"]
                            ) . "');\">" . $l_row["isys_obj__title"] . "</a>" . "</td>";
                    }

                    $l_html_selected .= "<td>" . $g_comp_template_language_manager->{$l_dao->get_objtype_name_by_id_as_string(
                            $l_dao->get_objTypeID($l_row["isys_obj__id"])
                        )} . "</td>" . "<td>" . $l_dao->get_object_status_by_id_as_string($l_row["isys_obj__id"]) . "</td>" . "</tr>";
                }
            }

            if ($l_counter == 0) $l_html .= "<tr><td colspan=3><strong>" . $g_comp_template_language_manager->{LC__UNIVERSAL__NO_OBJECTS_FOUND} . "</strong></td></tr>";

            $l_html .= "</tbody></table>";
            $l_html_selected .= "</tbody></table>";

            $l_html_selected_head = "<div><strong>" . $g_comp_template_language_manager->{LC__UNIVERSAL__SELECTED} . " " . $g_comp_template_language_manager->{LC__CMDB__CATG__OBJECT} . "</strong></div>";

            $l_html .= $l_html_selected_head . $l_html_selected;

            echo $l_html;
        }
        else
        {
            echo "<p>No objects found for \"" . $p_params["filter"] . "\".</p>";
        }
    }

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
}

?>