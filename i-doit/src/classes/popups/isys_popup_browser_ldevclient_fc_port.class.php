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
 * Popup browser for FC-Ports
 *
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_popup_browser_ldevclient_fc_port extends isys_popup_browser
{

    /**
     * Comma separated list of pools
     *
     * @var string
     */
    private $m_format_pools = '';
    /**
     * Id of primary port
     *
     * @var string
     */
    private $m_format_primary_port = '';

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_primary_port($json_string = '[]')
    {
        $this->m_format_primary_port = $json_string;

        return $this;
    }

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_pools($json_string = '[]')
    {
        $this->m_format_pools = $json_string;

        return $this;
    }

    /**
     * @return string
     * @global                        $g_dirs
     * @global                        $g_config
     *
     * @param isys_component_template & $p_tplclass
     * @param                         $p_table
     *
     * @author Andre Woesten <awoesten@i-doit.org>
     * @desc   Handles SMARTY request for SAN-Pool browser
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs;
        global $g_config;

        $l_url            = "";
        $l_strOut         = "";
        $l_strHiddenField = "";

        $l_url = $g_config["startpage"] . "?" . C__GET__MODULE_ID . "=" . C__MODULE__CMDB . "&popup=browser_fc_port" . "&currentObjID=" . $_GET["objID"] . "&resultField=" . $p_params["name"] . "&selectedDevices=" . urlencode(
                $p_params["p_strValue"]
            ) . "&catlevel=" . $_GET[C__CMDB__GET__CATLEVEL];

        /* Set dimensions of browser */
        $this->set_config("width", 500);
        $this->set_config("height", 520);

        /* Hidden field, in which the selected value is put */
        $l_strHiddenField .= "<input " . "name=\"" . $p_params["name"] . "__HIDDEN\" " . "type=\"hidden\" " . "value=\"" . $p_params["p_strValue"] . "\" " . "/>";

        $l_strPrimField = "<input " . "name=\"" . $p_params["name"] . "__PRIM\" " . "type=\"hidden\" " . "value=\"" . $p_params["p_strPrim"] . "\" " . "/>";

        /* Set parameters for the f_text plug-in */
        $p_params["name"]        = $p_params["name"] . "__VIEW";
        $p_params["p_bReadonly"] = "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_get_param("editMode") == C__EDITMODE__ON)
        {
            $p_params["p_strValue"] = $this->set_format_pools($p_params["p_strValue"])
                ->set_format_primary_port($p_params["p_strPrim"])
                ->format_selection($_GET["objID"], false);

            $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $p_params);

            $l_strOut .= '<a href="javascript:" title="' . _L('LC_SANPOOL_POPUP__SELECT_SAN') . '" class="ml5" onClick="' . $this->process(
                    $l_url,
                    true
                ) . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt="" />' . '</a>';

            $l_strOut .= $l_strHiddenField . $l_strPrimField;
        }
        else
        {

            $p_params["p_strValue"] = $this->set_format_pools($p_params["p_strValue"])
                ->set_format_primary_port($p_params["p_strPrim"])
                ->format_selection($_GET["objID"], true);
            $l_strOut .= $l_objPlugin->navigation_view($p_tplclass, $p_params);
            $l_strOut .= $l_strHiddenField;
        }

        return $l_strOut;
    }

    /**
     * Returns a formatted string for the selected SAN-Pool
     *
     * @param            $p_id
     * @param bool|false $p_plain
     */
    public function format_selection($p_id, $p_plain = false)
    {
        $l_pools = explode(",", $this->m_format_pools);
        if (is_array($l_pools) && count($l_pools) > 0)
        {
            if (!isset($p_objid))
            {
                $p_objid = $_GET[C__CMDB__GET__OBJECT];
            }

            $l_daoFC = new isys_cmdb_dao_category_g_controller_fcport(isys_application::instance()->database);

            /*
            $l_catlevel = $_GET[C__CMDB__GET__CATLEVEL];
            $l_res = $l_daoFC->retrieve("SELECT isys_catg_ldevclient_list__primary_path FROM isys_catg_ldevclient_list WHERE isys_catg_ldevclient_list__id = ".$l_daoFC->convert_sql_id($l_catlevel));
            $l_row = $l_res->get_row();
            $l_prim = $l_row["isys_catg_ldevclient_list__primary_path"];
            */

            $l_res = $l_daoFC->get_data(null, $p_objid, "", null, C__RECORD_STATUS__NORMAL);

            if ($l_res->num_rows() > 0)
            {
                $l_str_out = "";

                if (!$p_plain)
                {
                    while ($l_row = $l_res->get_row())
                    {
                        if (in_array($l_row["isys_catg_fc_port_list__id"], $l_pools))
                        {
                            $l_str_out .= isys_glob_str_stop($l_row["isys_catg_fc_port_list__title"], 50);

                            if ($this->m_format_primary_port == $l_row["isys_catg_fc_port_list__id"]) $l_str_out .= " (primary)";

                            $l_str_out .= ", \n";
                        }
                    }

                    $l_str_out = rtrim($l_str_out, ",\n ");
                }
                else
                {
                    //$l_str_out .= "<ul>";
                    while ($l_row = $l_res->get_row())
                    {
                        if (in_array($l_row["isys_catg_fc_port_list__id"], $l_pools))
                        {

                            //$l_str_out .= "<li>";
                            $l_str_out .= isys_glob_str_stop($l_row["isys_catg_fc_port_list__title"], 50);

                            if ($this->m_format_primary_port == $l_row["isys_catg_fc_port_list__id"]) $l_str_out .= " (primary)";

                            $l_str_out .= ", \n";

                            //$l_str_out .= "</li>";
                        }
                    }
                    //$l_str_out .= "</ul>";

                    $l_str_out = rtrim($l_str_out, ",\n ");
                }

                return $l_str_out;
            }
            else
            {
                return _L('LC_SANPOOL_POPUP__NO_DEVICES_CONNECTED') . ".";
            }
        }
        else
        {
            return _L('LC_UNIVERSAL__NONE_SELECTED') . ".";
        }
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

        /* Unpack module request */
        $l_gets     = $p_modreq->get_gets();
        $l_tplpopup = $p_modreq->get_template();

        /* Prepare tree */
        $l_tree = isys_component_tree::factory('g_browser');

        /* Needing CMDB DAO */
        $l_daoFC = new isys_cmdb_dao_category_g_controller_fcport(isys_application::instance()->database);

        /* Create root node */
        $l_node_root = $l_tree->add_node(0, -1, $l_daoFC->get_obj_name_by_id_as_string($l_gets["currentObjID"]));

        if ($l_tplpopup && $l_tree)
        {
            $l_n = 1;

            $l_res = $l_daoFC->get_data(null, $l_gets["currentObjID"], "", null, C__RECORD_STATUS__NORMAL);

            if ($l_res->num_rows() > 0)
            {
                $l_deviceList = [];

                while ($l_row = $l_res->get_row())
                {
                    $l_tree->add_node(
                        $l_n++,
                        $l_node_root,
                        "<label>" . $this->build_checkbox($l_gets, $l_row["isys_catg_fc_port_list__id"]) . " " . isys_glob_str_stop(
                            $l_row["isys_catg_fc_port_list__title"],
                            50
                        ) . "</label>"
                    );

                    $l_deviceList[$l_row["isys_catg_fc_port_list__id"]] = $l_row["isys_catg_fc_port_list__title"];
                }

                $l_tplpopup->assign("deviceList", $l_deviceList);

            }
            else
            {
                $l_tree->add_node($l_n++, $l_node_root, _L('LC_FC_PORT_POPUP__NO_PORTS'));
            }

            /* Write primary path */
            $l_tplpopup->assign("primPort", $l_daoFC->get_primary_path($l_gets["catlevel"]));

            /* Write the JS tree */
            $l_tplpopup->assign("browser", $l_tree->process(0));

            /* Create location browser popup */
            $l_tplpopup->assign("file_body", "popup/fc_port.tpl");

            /* Yes we return the template. */

            return $l_tplpopup;
        }

        return null;
    }

    /**LC_UNIVERSAL__NONE_SELECTED
     *
     * @param $p_gets
     * @param $p_id
     *
     * @return string
     */
    private function build_checkbox($p_gets, $p_id)
    {
        $l_show = false;
        $l_devs = $p_gets["selectedDevices"];

        if (is_string($l_devs) && !empty($l_devs))
        {
            $l_devs = explode(",", $l_devs);
            if (is_array($l_devs) && count($l_devs) > 0)
            {
                $l_show = in_array($p_id, $l_devs, false);
            }
        }

        return "<input " . "class=\"vam\" " . "type=\"checkbox\" " . "name=\"devicesInPool[]\" " . "value=\"{$p_id}\" " . "onChange=\"refresh_selected()\" " . (($l_show) ? "checked=\"checked\"" : "") . " />";
    }
}