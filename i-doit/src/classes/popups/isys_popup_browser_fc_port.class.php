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
class isys_popup_browser_fc_port extends isys_popup_browser
{
    /**
     * Json Array of pools to use in format_selection
     *
     * @var string
     */
    private $m_format_pools = '[]';
    /**
     * Id of primary port to use in format_selection
     *
     * @var string
     */
    private $m_format_primary_port = '';

    /**
     * @param int $int
     *
     * @inherit
     * @return $this
     */
    public function set_format_primary_port($int)
    {
        $this->m_format_primary_port = $int;

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
     * Handles SMARTY request for SAN-Pool browser.
     *
     * @global  array                   $g_dirs
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs;

        // Hidden field, in which the selected value is stored.
        $l_strHiddenField = '<input name="' . $p_params["name"] . '__HIDDEN" id="' . $p_params["name"] . '__HIDDEN" type="hidden" value="' . $p_params["p_strValue"] . '" />';

        // Set parameters for the f_text plug-in.
        $p_params["p_bReadonly"] = "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_get_param("editMode") == C__EDITMODE__ON)
        {
            // Here we pass some data, that we'll need later on.
            $p_params[C__CMDB__GET__OBJECT]   = $_GET[C__CMDB__GET__OBJECT];
            $p_params[C__CMDB__GET__CATLEVEL] = $_GET[C__CMDB__GET__CATLEVEL];
            $p_params['selected_ports']       = $p_params['p_strValue'];

            $l_url = $this->process_overlay('', 400, 460, $p_params);

            $p_params["p_strValue"] = $this->set_format_pools($p_params["p_strValue"])
                ->set_format_primary_port($p_params["p_strPrim"])
                ->format_selection($_GET["objID"], false);

            $l_name           = $p_params["name"];
            $p_params["name"] = $p_params["name"] . "__VIEW";

            if ($p_params["id"])
            {
                $l_id = $p_params["id"];
            }
            else
            {
                $l_id           = $p_params["name"];
                $p_params["id"] = $l_id;
            } // if

            $l_onclick_detach = "var e_view = $('" . $l_id . "'), e_hidden = $('" . $l_name . "__HIDDEN');
				if(e_view && e_hidden) {
					e_view.value = '" . _L("LC__UNIVERSAL__CONNECTION_DETACHED") . "!';
					e_hidden.value = '';
				}" . (isset($p_params["p_strDetachCallback"]) ? $p_params["p_strDetachCallback"] : "");

            return $l_objPlugin->navigation_edit($p_tplclass, $p_params) . '<a href="javascript:" title="' . _L(
                "LC__UNIVERSAL__ATTACH"
            ) . '" class="ml5 vam" onClick="' . $l_url . ';">' . '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt="' . _L(
                'LC__UNIVERSAL__ATTACH'
            ) . '" class="vam" />' . '</a>' . '<a href="javascript:" title="' . _L(
                "LC__UNIVERSAL__DETACH"
            ) . '" class="ml5 vam" onClick="' . $l_onclick_detach . ';">' . '<img src="' . $g_dirs["images"] . 'icons/silk/detach.png" alt="' . _L(
                'LC__UNIVERSAL__DETACH'
            ) . '" class="vam" />' . '</a>' . $l_strHiddenField . '<input name="' . $p_params["name"] . '__PRIM" id="' . $p_params["name"] . '__PRIM" type="hidden" value="' . $p_params["p_strPrim"] . '" />';
        } // if

        $p_params["p_strValue"] = $this->set_format_pools($p_params["p_strValue"])
            ->set_format_primary_port($p_params["p_strPrim"])
            ->format_selection($_GET["objID"], true);

        return $l_objPlugin->navigation_view($p_tplclass, $p_params) . $l_strHiddenField;
    } // function

    /**
     * Returns a formatted string for the selected SAN-Pool.
     *
     * @param            $p_objid
     * @param bool|false $p_unused
     *
     * @return string
     */
    public function format_selection($p_objid, $p_unused = false)
    {
        if ($this->m_format_pools != '')
        {
            $l_pools = explode(',', $this->m_format_pools);

            if (!$p_objid)
            {
                $p_objid = $_GET[C__CMDB__GET__OBJECT];
            } // if

            $l_daoFC   = new isys_cmdb_dao_category_g_controller_fcport(isys_application::instance()->database);
            $l_res     = $l_daoFC->get_data(null, $p_objid, "", null, C__RECORD_STATUS__NORMAL);
            $l_str_out = [];
            if ($l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    if (in_array($l_row["isys_catg_fc_port_list__id"], $l_pools))
                    {
                        $l_path = isys_glob_str_stop($l_row["isys_catg_fc_port_list__title"], 50);
                        if ($this->m_format_primary_port == $l_row["isys_catg_fc_port_list__id"])
                        {
                            $l_path .= ' (' . _L('LC__UNIVERSAL__PRIMARY') . ')';
                        } // if
                        $l_str_out[] = $l_path;
                    } // if
                } // while

                return implode(', ', $l_str_out);
            }
            else
            {
                return _L('LC_SANPOOL_POPUP__NO_PATHS_CONNECTED') . ".";
            } // if
        }
        else
        {
            return _L('LC_UNIVERSAL__NONE_SELECTED') . ".";
        } // if
    } // function

    /**
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  mixed  An instance of isys_component_template or null on failure.
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_fc_ports          = [];
        $l_fc_ports_selected = [];

        // Unpack module request.
        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        if ($l_tplpopup = $p_modreq->get_template())
        {
            /**
             * Creating an instance of "isys_cmdb_dao_category_g_controller_fcport".
             *
             * @var  isys_cmdb_dao_category_g_controller_fcport $l_dao
             */
            $l_dao = isys_cmdb_dao_category_g_controller_fcport::instance(isys_application::instance()->database);

            $l_res = $l_dao->get_data(null, $l_params[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

            if ($l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_row["isys_catg_fc_port_list__id"] = (int) $l_row["isys_catg_fc_port_list__id"];

                    $l_fc_ports[$l_row["isys_catg_fc_port_list__id"]] = [
                        'id'    => $l_row["isys_catg_fc_port_list__id"],
                        'title' => $l_row["isys_catg_fc_port_list__title"],
                    ];
                } // while

                $l_fc_ports_selected = explode(',', $l_params['selected_ports']);
                $l_fc_ports_selected = array_map('intval', $l_fc_ports_selected);
            } // if

            if (empty($l_params['selected_ports']))
            {
                $l_fc_ports_selected = [];
            } // if

            // Write primary path.
            $l_tplpopup->assign('returnfield', $l_params['name'])
                ->assign('fc_ports', isys_format_json::encode($l_fc_ports))
                ->assign('fc_ports_selection', isys_format_json::encode($l_fc_ports_selected))
                ->assign('primary', $l_params['p_strPrim'])
                ->display('popup/fc_port.tpl');
            die;
        } // if

        return null;
    } // function
} // class