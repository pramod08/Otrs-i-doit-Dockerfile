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
 * Popup class for LDEV-Client browser
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Dennis Blümer <dbluemer@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_popup_browser_storage_device extends isys_popup_browser
{
    /*
    * @return string
     * @global $g_dirs
     * @global $g_config
     * @param isys_component_template& $p_tplclass
    * @param $p_table
    * @author Andre Woesten <awoesten@i-doit.org> - 2006-04-04
    * @desc Handles SMARTY request for object browser
    */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params, $p_onclick_only = false)
    {
        global $g_dirs;
        global $g_config;
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

        $l_url = $g_config["startpage"] . "?mod=cmdb" . "&popup=browser_storage_device" . "&form_submit=" . $p_params["p_strFormSubmit"] . "&resultField=" . $p_params["name"] . "&groupFilter=" . $p_params["groupFilter"] . "&catlevel=" . $_GET[C__CMDB__GET__CATLEVEL];

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

        /* Set dimensions of browser */
        $this->set_config("width", 800);
        $this->set_config("height", 625);

        if (!isset($p_params["nohidden"]))
        {
            $l_strHiddenField .= "<input name=\"" . $p_params["name"] . "__HIDDEN\" id=\"" . $p_params["name"] . "__HIDDEN\" type=\"hidden\" value=\"" . $p_params["p_strValue"] . "\" />";
        }

        /* Set parameters for the f_text plug-in */
        $l_name = $p_params["name"];

        $p_params["name"]        = $l_name . "__VIEW";
        $p_params["p_bReadonly"] = "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON || isset($p_params["edit"])) && !isset($p_params["plain"]))
        {

            if ($p_params["id"]) $l_id = $p_params["id"];
            else
            {
                $l_id           = $p_params["name"];
                $p_params["id"] = $l_id;
            }

            $p_params["p_strValue"] = strip_tags($this->format_selection($p_params["p_strValue"]));

            $l_detach_callback = isset($p_params["p_strDetachCallback"]) ? $p_params["p_strDetachCallback"] : "";
            $l_onclick_detach  = "var e_view = $('" . $l_id . "'), " . "e_hidden = $('" . $l_name . "__HIDDEN');" .

                "if(e_view && e_hidden) {" . "e_view.value = '" . $g_comp_template_language_manager->{"LC__UNIVERSAL__CONNECTION_DETACHED"} . "!'; " . "e_hidden.value = '';" . "}" . $l_detach_callback;

            $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $p_params);
            $l_strOut .= '<a href="javascript:" title="' . _L("LC__UNIVERSAL__CHOOSE") . '" class="ml5" onClick="' . $this->process(
                    $l_url,
                    true
                ) . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt="Open browser" />' . '</a>';

            $l_strOut .= '<a href="javascript:" title="Detach object" class="ml5" onClick="' . $l_onclick_detach . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/detach.png" alt="Detach" />' . '</a>';

            $l_strOut .= $l_strHiddenField;
        }
        else
        {
            $p_params["p_strValue"] = nl2br($this->format_selection($p_params["p_strValue"]));
            if ($l_object_id > 0)
            {
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
     * @param            $p_clients
     * @param bool|false $p_unused
     *
     * @return string
     */
    public function format_selection($p_clients, $p_unused = false)
    {
        $l_dao_cmdb  = new isys_cmdb_dao_category_g_ldevclient(isys_application::instance()->database);
        $l_objPlugin = new isys_smarty_plugin_f_text();

        $l_clients    = explode(",", $p_clients);
        $l_clients    = $l_dao_cmdb->get_client_info($l_clients);
        $l_quick_info = new isys_ajax_handler_quick_info();

        if ($l_clients->num_rows() < 1) return _L("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");

        $l_out = "";
        while ($l_row = $l_clients->get_row())
        {
            $l_out .= $l_objPlugin->getInfoIcon([]);

            $l_ldevclient_link = $l_quick_info->get_link(
                $l_row["isys_catg_ldevclient_list__id"],
                $l_dao_cmdb->get_obj_name_by_id_as_string($l_row["isys_obj__id"]) . " >> " . $l_row["isys_catg_ldevclient_list__title"],
                "index.php?viewMode=1100&objTypeID=" . $l_dao_cmdb->get_objTypeID(
                    $l_row["isys_obj__id"]
                ) . "&objID=" . $l_row["isys_obj__id"] . "&tvMode=1006&editMode=0&mNavID=2&cateID=" . $l_row["isys_catg_ldevclient_list__id"] . "&catgID=" . $l_dao_cmdb->get_category_id(
                )
            );

            $l_out .= $l_ldevclient_link;
            $l_out .= ", <br>";
        }
        $l_out = substr($l_out, 0, -6);
        $l_out = rtrim($l_out, ",\n ");

        return $l_out;
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

        $l_selectedClients = [];
        $l_res             = $l_dao_cmdb->retrieve(
            "SELECT isys_catg_ldevclient_list__id FROM isys_catg_ldevclient_list WHERE isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $l_dao_cmdb->convert_sql_id(
                $_GET["catlevel"]
            )
        );
        while ($l_row = $l_res->get_row())
        {
            $l_selectedClients[] = $l_row["isys_catg_ldevclient_list__id"];
        }

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

            if (!isset($_GET["status"]) || is_null($_GET["status"]))
            {
                $_GET["status"] = C__RECORD_STATUS__NORMAL;
            }

            /* Build & assign trees */
            $l_tplpopup->assign("treeLocation", $this->build_client_location_tree($l_tplclass, $l_tree_loc, $l_selectedClients));
            $l_tplpopup->assign("treeObject", $this->build_client_tree($l_tplclass, $l_tree_obj, null, null, $_GET["status"], $l_selectedClients));

            /* Assign form submit */
            $l_tplpopup->assign("g_form_submit", $_GET["form_submit"]);

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
            unset($l_statgets["status"]);

            /* No Viewmode, use object view */
            if (!isset($_GET[C__CMDB__GET__VIEWMODE]))
            {
                $_GET[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__TREE_OBJECT;
            }

            $l_tplpopup->assign("statusURL", html_entity_decode(isys_glob_build_url(isys_glob_http_build_query($l_statgets))));

            /* Create location browser popup */
            $l_tplpopup->assign("file_body", "popup/storage_device.tpl");

            /* Assign client list */
            $l_resClients = $l_dao_cmdb->retrieve("SELECT * FROM isys_catg_ldevclient_list");
            $l_clientList = [];
            while ($l_row = $l_resClients->get_row())
            {
                $l_clientList[$l_row["isys_catg_ldevclient_list__id"]] = $l_row["isys_catg_ldevclient_list__title"];
            }

            $l_tplpopup->assign("clientList", $l_clientList);

            /* Yes we return the template. */

            return $l_tplpopup;
        }

        return null;
    }

    public function __construct()
    {
        parent::__construct();
    }
}

?>