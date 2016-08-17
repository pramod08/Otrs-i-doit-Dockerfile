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
 * CMDB UI: Global category (category type is global)
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_cluster_shared_virtual_switch extends isys_cmdb_ui_category_global
{

    /**
     * @global                       $index_includes
     * @global                       $g_comp_template
     *
     * @param isys_cmdb_dao_category & $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_template;

        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_tpl_navbar = isys_module_request::get_instance()
            ->get_navbar();
        $l_tpl_navbar->set_active(false, C__NAVBAR_BUTTON__PRINT);

        /**
         * Cluster members and coordinates
         */
        $l_cluster_members = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);
        while ($l_row = $l_cluster_members->get_row())
        {

            if (!is_null($l_row["isys_catg_virtual_switch_list__id"]) && $l_row["isys_catg_virtual_switch_list__status"] == C__RECORD_STATUS__NORMAL)
            {
                //$l_coords[$l_row["isys_obj__id"]][str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"])] = $l_row;

                $l_port_groups_res = $p_cat->get_port_groups($l_row["isys_catg_virtual_switch_list__id"]);

                while ($l_port_group_row = $l_port_groups_res->get_row())
                {
                    $l_coords[$l_row["isys_obj__id"]][str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"])]["port_group"][str_replace(
                        " ",
                        "",
                        $l_port_group_row["isys_virtual_port_group__title"]
                    )] = $l_port_group_row;
                }
            }

            if (!isset($l_members[$l_row["isys_obj__id"]]))
            {
                $l_members[$l_row["isys_obj__id"]]         = $l_row;
                $l_members[$l_row["isys_obj__id"]]["link"] = $l_quick_info->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT);
            }

            $l_check_arr[str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"])] = str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"]);

        }

        $g_comp_template->assignByRef("c_members", $l_members);
        $g_comp_template->assignByRef("coords", $l_coords);

        /* ------------------------------------------------------------------------------------ */

        /**
         * San
         */
        $l_vswitch     = new isys_cmdb_dao_category_g_virtual_switch($p_cat->get_database_component());
        $l_vswitch_all = $l_vswitch->get_data(null, null, null, null, C__RECORD_STATUS__NORMAL);
        $l_vswitch_all = $p_cat->get_all_virtual_switches();

        $l_port_group_arr = [];
        while ($l_row = $l_vswitch_all->get_row())
        {

            if (is_array($l_check_arr))
            {
                if (in_array(str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"]), $l_check_arr))
                {
                    if (isset($l_last_vswitch) && $l_last_vswitch != $l_row["isys_catg_virtual_switch_list__title"]) unset($l_port_group_arr);

                    $l_port_groups_res = $p_cat->get_port_groups($l_row["isys_catg_virtual_switch_list__id"]);

                    while ($l_port_group_row = $l_port_groups_res->get_row())
                    {
                        $l_port_group_arr[str_replace(" ", "", $l_port_group_row["isys_virtual_port_group__title"])] = $l_port_group_row["isys_virtual_port_group__title"];
                    }
                    $l_vswitchlist[str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"])]["title"] = $l_row["isys_catg_virtual_switch_list__title"];
                    $l_vswitchlist[str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"])][0]       = [
                        "object"      => $l_row["isys_catg_virtual_switch_list__title"],
                        "object_id"   => $l_row["isys_catg_virtual_switch_list__id"],
                        "object_link" => $l_quick_info->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT),
                        "object_type" => $l_row["isys_obj__isys_obj_type__id"],
                        "port_group"  => $l_port_group_arr,
                    ];

                    $l_last_vswitch = $l_row["isys_catg_virtual_switch_list__title"];
                }
            }

        }

        $g_comp_template->assign("bShowCommentary", "0");

        $g_comp_template->assignByRef("vswitchlist", $l_vswitchlist);

        /* ------------------------------------------------------------------------------------ */

        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__cluster_shared_virtual_switch.tpl");
    }
}

?>