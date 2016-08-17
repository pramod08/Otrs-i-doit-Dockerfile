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
 * Popup browser for SAN-Pools.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_sanpool extends isys_popup_browser
{

    /**
     * Json Array of pools to use in format_selection
     *
     * @var string
     */
    private $m_format_pools = '[]';
    /**
     * Json array of raids to use in format_selection
     *
     * @var string
     */
    private $m_format_raids = '[]';

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_raids($json_string = '[]')
    {
        $this->m_format_raids = $json_string;

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
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs, $g_comp_template_language_manager;

        $p_params['currentObjID'] = $_GET["objID"];

        // Hidden field, in which the selected value is put.
        $l_hidden_devices = '<input id="' . $p_params["name"] . '__HIDDEN" name="' . $p_params["name"] . '__HIDDEN" type="hidden" value="' . $p_params["p_selectedDevices"] . '" />';
        $l_hidden_raids   = '<input id="' . $p_params["name"] . '__HIDDEN2" name="' . $p_params["name"] . '__HIDDEN2" type="hidden" value="' . $p_params["p_selectedRaids"] . '" />';

        if ($p_params["id"])
        {
            $l_id = $p_params["id"];
        }
        else
        {
            $l_id           = $p_params["name"];
            $p_params["id"] = $l_id;
        } // if

        // Set parameters for the f_text plug-in.
        $p_params["p_strValue"] = $this->set_format_pools($p_params["p_selectedDevices"])
            ->set_format_raids($p_params["p_selectedRaids"])
            ->format_selection($_GET["objID"]);

        $p_params["name"]        = $p_params["name"] . "__VIEW";
        $p_params["p_bReadonly"] = "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_is_edit_mode())
        {
            $l_detach_callback = isset($p_params["p_strDetachCallback"]) ? $p_params["p_strDetachCallback"] : "";
            $l_onclick_detach  = "var e_view = $('" . $l_id . "'), " . "e_hidden = $('" . $l_id . "__HIDDEN')," . "e_hidden2 = $('" . $l_id . "__HIDDEN2');" .

                "if(e_view && e_hidden && e_hidden2) {" . "e_view.value = '" . $g_comp_template_language_manager->{"LC__UNIVERSAL__CONNECTION_DETACHED"} . "!'; " . "e_hidden.value = '';" . "e_hidden2.value = '';" . "}" . $l_detach_callback . ';';

            return $l_objPlugin->navigation_edit($p_tplclass, $p_params) . '<a href="javascript:" title="' . _L(
                'LC_SANPOOL_POPUP__SELECT_SAN'
            ) . '" class="ml5 vam" onClick="' . $this->process_overlay('', 800, 360, $p_params) . '">' . '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt="' . _L(
                'LC__UNIVERSAL__ATTACH'
            ) . '" class="vam" />' . '</a>' . '<a href="javascript:" title="' . _L(
                'LC__UNIVERSAL__DETACH'
            ) . '" class="ml5 vam" onClick="' . $l_onclick_detach . '" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/detach.png" alt="' . _L(
                'LC__UNIVERSAL__DETACH'
            ) . '" class="vam" />' . '</a>' . $l_hidden_devices . $l_hidden_raids;
        }
        else
        {
            return $l_objPlugin->navigation_view($p_tplclass, $p_params);
        } // if
    } // function

    /**
     * Returns a formatted string for the selected SAN-Pool.
     *
     * @param   string  $p_pools JSON array of integers.
     * @param   string  $p_raids JSON array of integers.
     * @param   integer $p_objid
     *
     * @return  string
     */
    public function format_selection($p_objid, $p_unused = false)
    {
        global $g_comp_database;

        $l_pools = isys_format_json::decode($this->m_format_pools);
        $l_raids = isys_format_json::decode($this->m_format_raids);

        $l_dao_ctrl = new isys_cmdb_dao_category_g_stor($g_comp_database);
        $l_dao_raid = new isys_cmdb_dao_category_g_raid($g_comp_database);

        if (count($l_pools) > 0 || count($l_raids) > 0)
        {
            if (!isset($p_objid))
            {
                $p_objid = $_GET[C__CMDB__GET__OBJECT];
            }

            if ($l_dao_ctrl->obj_exists($p_objid))
            {
                $l_res_dev  = $l_dao_ctrl->get_device_subset($l_pools);
                $l_res_raid = $l_dao_raid->get_raid_subset($l_raids);

                if ($l_res_dev->num_rows() > 0 || $l_res_raid->num_rows() > 0)
                {
                    $l_return = [];

                    while ($l_row = $l_res_dev->get_row())
                    {
                        $l_memory      = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], $l_row["isys_memory_unit__const"], C__CONVERT_DIRECTION__BACKWARD);
                        $l_memory_type = $l_row["isys_memory_unit__title"];

                        $l_return[] = isys_glob_str_stop($l_row["isys_catg_stor_list__title"], 50) . " (" . $l_memory . " " . $l_memory_type . ")";
                    } // while

                    while ($l_row = $l_res_raid->get_row())
                    {
                        list($l_memory, $l_memory_type_title, $l_num_disks) = $l_dao_ctrl->get_raid_memory_info($l_row["isys_catg_raid_list__id"]);

                        $l_memory_real = $l_dao_ctrl->raidcalc($l_num_disks, $l_memory, $l_dao_ctrl->get_raid_level($l_row["isys_catg_raid_list__isys_stor_raid_level__id"]));

                        $l_return[] = isys_glob_str_stop($l_row["isys_catg_raid_list__title"], 50) . " (" . $l_memory_real . " " . $l_memory_type_title . ")";
                    } // while

                    return implode(",\n ", $l_return);
                }
                else
                {
                    return _L("LC_SANPOOL_POPUP__NO_DEVICES_CONNECTED") . ".";
                } // if
            }
            else
            {
                return _L("LC_SANPOOL_POPUP__NO_OBJECT") . ".";
            } // if
        }
        else
        {
            return _L("LC_UNIVERSAL__NONE_SELECTED") . ".";
        } // if
    } // function

    /**
     * This method gets called by the Ajax request to display the browser.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template&
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;

        $l_deviceList = [];
        $l_raidList   = [];

        // Unpack module request.
        $l_tplpopup = $p_modreq->get_template();
        $l_params   = isys_format_json::decode(base64_decode($_POST['params']), true);
        $l_devs     = isys_format_json::decode($l_params["p_selectedDevices"], true);
        $l_raids    = isys_format_json::decode($l_params["p_selectedRaids"], true);

        // Prepare tree component.
        $l_tree = isys_component_tree::factory('ldev_browser');

        // Needing CMDB DAO.
        $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

        // Create root node.
        $l_node_root = $l_tree->add_node(0, -1, $l_dao_cmdb->get_obj_name_by_id_as_string($l_params["currentObjID"]));

        if ($l_tplpopup)
        {
            $l_dao_ctrl      = new isys_cmdb_dao_category_g_stor($g_comp_database);
            $l_shown_devices = [];

            $l_node_count = 2;
            $l_res_ctrl   = $l_dao_ctrl->get_controller_by_object_id($l_params["currentObjID"]);

            if ($l_res_ctrl && $l_res_ctrl->num_rows() > 0)
            {
                $l_node_controller = $l_tree->add_node(1, 0, _L("LC__CATG__STORAGE_CONTROLLER"));

                // Add controllers.
                while ($l_row_ctrl = $l_res_ctrl->get_row())
                {
                    $l_node_ctrl = $l_tree->add_node(
                        ($l_node_count++),
                        $l_node_controller,
                        $l_row_ctrl["isys_catg_controller_list__title"]
                    );

                    $l_res_dev  = $l_dao_ctrl->get_devices($l_row_ctrl["isys_catg_controller_list__id"]);
                    $l_res_raid = $l_dao_ctrl->get_raids($l_row_ctrl["isys_catg_controller_list__id"]);

                    if ($l_res_dev->num_rows() > 0 || $l_res_raid->num_rows() > 0)
                    {
                        // Add devices.
                        while ($l_row_dev = $l_res_dev->get_row())
                        {
                            $l_tree->add_node(
                                ($l_node_count++),
                                $l_node_ctrl,
                                "<label>" . $this->build_checkbox(
                                    $l_row_dev["isys_catg_stor_list__id"],
                                    "devicesInPool",
                                    in_array($l_row_dev["isys_catg_stor_list__id"], $l_devs)
                                ) . " " . isys_glob_str_stop($l_row_dev["isys_catg_stor_list__title"], 50) . "</label>"
                            );

                            $l_shown_devices[] = $l_row_dev["isys_catg_stor_list__id"];
                        } // while

                        // Add raids.
                        while ($l_row_raid = $l_res_raid->get_row())
                        {
                            $l_tree->add_node(
                                ($l_node_count++),
                                $l_node_ctrl,
                                "<label>" . $this->build_checkbox(
                                    $l_row_raid["isys_catg_raid_list__id"],
                                    "raidsInPool",
                                    in_array($l_row_raid["isys_catg_raid_list__id"], $l_raids)
                                ) . " " . isys_glob_str_stop($l_row_raid["isys_catg_raid_list__title"], 50) . "</label>"
                            );

                            $l_raidList[$l_row_raid["isys_catg_raid_list__id"]] = $l_row_raid["isys_catg_raid_list__title"];
                        } // while
                    }
                    else
                    {
                        $l_tree->add_node($l_node_count++, $l_node_ctrl, _L('LC_SANPOOL_POPUP__NO_DEVICES_FOUND') . "!");
                    } // if
                } // while
            } // if

            // We want to display a message, if no devices are connected.
            // We need this node to show devices which are not assigned to any controller
            $l_node_unassigned = $l_tree->add_node(($l_node_count++), $l_node_root, _L('LC_SANPOOL_POPUP__NO_DEVICES_CONNECTED'));

            $l_res_dev  = $l_dao_ctrl->get_devices(null, $l_params["currentObjID"]);
            $l_res_raid = $l_dao_ctrl->get_unassigned_raids($l_params["currentObjID"]);

            if ($l_res_dev->num_rows() > 0 || $l_res_raid->num_rows() > 0)
            {
                while ($l_row_dev = $l_res_dev->get_row())
                {
                    if (!empty($l_shown_devices))
                    {
                        if (array_search($l_row_dev["isys_catg_stor_list__id"], $l_shown_devices) === false)
                        {
                            $l_tree->add_node(
                                ($l_node_count++),
                                $l_node_unassigned,
                                "<label>" . $this->build_checkbox(
                                    $l_row_dev["isys_catg_stor_list__id"],
                                    "devicesInPool",
                                    in_array($l_row_dev["isys_catg_stor_list__id"], $l_devs)
                                ) . " " . isys_glob_str_stop($l_row_dev["isys_catg_stor_list__title"], 50) . "</label>"
                            );
                        } // if
                    }
                    else
                    {
                        $l_tree->add_node(
                            ($l_node_count++),
                            $l_node_unassigned,
                            "<label>" . $this->build_checkbox(
                                $l_row_dev["isys_catg_stor_list__id"],
                                "devicesInPool",
                                in_array($l_row_dev["isys_catg_stor_list__id"], $l_devs)
                            ) . " " . isys_glob_str_stop($l_row_dev["isys_catg_stor_list__title"], 50) . "</label>"
                        );
                    } // if

                    $l_deviceList[$l_row_dev["isys_catg_stor_list__id"]] = $l_row_dev["isys_catg_stor_list__title"];
                } // while

                while ($l_row_raid = $l_res_raid->get_row())
                {
                    $l_tree->add_node(
                        ($l_node_count++),
                        $l_node_unassigned,
                        "<label>" . $this->build_checkbox(
                            $l_row_raid["isys_catg_raid_list__id"],
                            "raidsInPool",
                            in_array($l_row_raid["isys_catg_raid_list__id"], $l_raids)
                        ) . " " . isys_glob_str_stop($l_row_raid["isys_catg_raid_list__title"], 50) . "</label>"
                    );

                    $l_raidList[$l_row_raid["isys_catg_raid_list__id"]] = $l_row_raid["isys_catg_raid_list__title"];
                } // while
            }
            else
            {
                $l_tree->add_node($l_node_count, $l_node_root, _L('LC_SANPOOL_POPUP__NO_DEVICES_FOUND'));
            } // if

            $l_tplpopup->assign("name", $l_params['id'])
                ->assign("deviceList", isys_format_json::encode($l_deviceList))
                ->assign("raidList", isys_format_json::encode($l_raidList))
                ->assign("browser", $l_tree->process(0))
                ->display("popup/sanzone.tpl");
            die();
        } // if

        return null;
    } // function

    /**
     * Method for displaying a checkbox inside the object browser.
     *
     * @param   integer $p_id
     * @param   string  $p_name
     * @param   boolean $p_checked
     *
     * @return  string
     */
    protected function build_checkbox($p_id, $p_name, $p_checked)
    {
        return '<input class="vam" type="checkbox" name="' . $p_name . '[]" value="' . $p_id . '"  ' . (($p_checked) ? 'checked="checked"' : '') . ' />';
    } // function
} // class