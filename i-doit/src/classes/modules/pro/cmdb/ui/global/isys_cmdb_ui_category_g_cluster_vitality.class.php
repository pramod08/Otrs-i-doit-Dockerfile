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
class isys_cmdb_ui_category_g_cluster_vitality extends isys_cmdb_ui_category_global
{

    /**
     * @param  isys_cmdb_dao_category_g_cluster_vitality $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__PRINT);

        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_dao             = new isys_cmdb_dao($p_cat->get_database_component());
        $l_dao_resource    = new isys_cmdb_dao_category_g_computing_resources($p_cat->get_database_component());
        $l_cluster_service = new isys_cmdb_dao_category_g_cluster_service($p_cat->get_database_component());
        $l_dao_memory      = new isys_cmdb_dao_category_g_memory($p_cat->get_database_component());
        $l_dao_cpu         = new isys_cmdb_dao_category_g_cpu($p_cat->get_database_component());
        $l_dao_port        = new isys_cmdb_dao_category_g_network_port($p_cat->get_database_component());
        $l_dao_drive       = new isys_cmdb_dao_category_g_drive($p_cat->get_database_component());

        $l_arr = $p_cat->get_members_and_coordinates($_GET[C__CMDB__GET__OBJECT]);

        $l_members     = $l_arr["members"];
        $l_coords      = $l_arr["coordinates"];
        $l_coords2     = $l_arr["coordinates2"];
        $l_consumption = $l_arr["consumption"];

        $l_cluster_service_all = $l_cluster_service->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_cluster_service_all->get_row())
        {

            if ($l_row["isys_connection__isys_obj__id"] != null)
            {
                $l_service_info = $l_cluster_service->get_object_by_id($l_row["isys_connection__isys_obj__id"])
                    ->__to_array();

                if ($l_service_info["isys_obj__status"] == C__RECORD_STATUS__NORMAL)
                {

                    if (strlen($l_service_info["isys_obj__title"]) > 15)
                    {
                        $l_service_title = substr($l_service_info["isys_obj__title"], 0, 12) . "...";
                    }
                    else
                    {
                        $l_service_title = $l_service_info["isys_obj__title"];
                    }

                    $l_resource_arr    = $l_dao_resource->get_data(null, $l_row["isys_connection__isys_obj__id"], '', null, C__RECORD_STATUS__NORMAL)
                        ->get_row();
                    $l_disc_space_unit = $l_dao->retrieve(
                        "SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = " . $l_dao->convert_sql_id(
                            $l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]
                        )
                    )
                        ->get_row();
                    $l_memory_unit     = $l_dao->retrieve(
                        "SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = " . $l_dao->convert_sql_id(
                            $l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]
                        )
                    )
                        ->get_row();
                    $l_cpu_unit        = $l_dao->retrieve(
                        "SELECT isys_frequency_unit__title FROM isys_frequency_unit WHERE isys_frequency_unit__id = " . $l_dao->convert_sql_id(
                            $l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]
                        )
                    )
                        ->get_row();
                    $l_bandwidth_unit  = $l_dao->retrieve(
                        "SELECT isys_port_speed__title FROM isys_port_speed WHERE isys_port_speed__id = " . $l_dao->convert_sql_id(
                            $l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]
                        )
                    )
                        ->get_row();

                    $l_ram        = isys_convert::memory(
                        $l_resource_arr["isys_catg_computing_resources_list__ram"],
                        intval($l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]),
                        C__CONVERT_DIRECTION__BACKWARD
                    );
                    $l_cpu        = isys_convert::frequency(
                        $l_resource_arr["isys_catg_computing_resources_list__cpu"],
                        intval($l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]),
                        C__CONVERT_DIRECTION__BACKWARD
                    );
                    $l_disc_space = isys_convert::memory(
                        $l_resource_arr["isys_catg_computing_resources_list__disc_space"],
                        intval($l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]),
                        C__CONVERT_DIRECTION__BACKWARD
                    );
                    $l_bandwidth  = isys_convert::speed(
                        $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"],
                        intval($l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]),
                        C__CONVERT_DIRECTION__BACKWARD
                    );

                    $l_ram        = (strstr($l_ram, ".")) ? round($l_ram, 2) : $l_ram;
                    $l_cpu        = (strstr($l_cpu, ".")) ? round($l_cpu, 2) : $l_cpu;
                    $l_disc_space = (strstr($l_disc_space, ".")) ? round($l_disc_space, 2) : $l_disc_space;
                    $l_bandwidth  = (strstr($l_bandwidth, ".")) ? round($l_bandwidth, 2) : $l_bandwidth;

                    $l_cluster_service_list[$l_row["isys_catg_cluster_service_list__id"]] = [
                        "object"                => $l_service_title,
                        "object_id"             => $l_service_info["isys_obj__id"],
                        "object_link"           => $l_quick_info->get_quick_info($l_service_info["isys_obj__id"], $l_service_title, C__LINK__OBJECT),
                        "object_type"           => $l_service_info["isys_obj__isys_obj_type__id"],
                        "cluster_service_title" => $l_row["isys_obj__title"],
                        "memory"                => $l_ram,
                        "memory_unit"           => $l_memory_unit["isys_memory_unit__title"],
                        "cpu"                   => $l_cpu,
                        "cpu_unit"              => $l_cpu_unit["isys_frequency_unit__title"],
                        "disc_space"            => $l_disc_space,
                        "disc_space_unit"       => $l_disc_space_unit["isys_memory_unit__title"],
                        "bandwidth"             => $l_bandwidth,
                        "bandwidth_unit"        => $l_bandwidth_unit["isys_port_speed__title"],
                        "cluster_type"          => $l_row["isys_cluster_type__title"]
                    ];
                }
            }
            else
            {
                $p_cat->delete_cluster_service($l_row["isys_catg_cluster_service_list__id"]);
            }
        }

        $l_cluster_vms_res = $p_cat->get_vms($_GET[C__CMDB__GET__OBJECT]);

        while ($l_row = $l_cluster_vms_res->get_row())
        {

            $l_memory     = 0;
            $l_disc_space = 0;
            $l_cpu        = 0;

            if ($l_row["isys_obj__status"] == C__RECORD_STATUS__NORMAL)
            {

                if (strlen($l_row["isys_obj__title"]) > 15)
                {
                    $l_service_title = substr($l_row["isys_obj__title"], 0, 12);
                    $l_service_title .= "...";
                }
                else
                {
                    $l_service_title = $l_row["isys_obj__title"];
                }

                $l_memory_res = $l_dao_memory->get_memory($l_row["isys_obj__id"]);

                while ($l_memory_row = $l_memory_res->get_row())
                {
                    $l_memory += $l_memory_row["isys_catg_memory_list__capacity"];
                } // while

                if (isys_convert::memory($l_memory, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $l_memory      = isys_convert::memory($l_memory, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    $l_memory_unit = _L("LC__CMDB__MEMORY_UNIT__GB");
                }
                else
                {
                    $l_memory      = isys_convert::memory($l_memory, "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD);
                    $l_memory_unit = _L("LC__CMDB__MEMORY_UNIT__MB");
                } // if

                $l_cpu_res = $l_dao_cpu->get_data(null, $l_row["isys_obj__id"], '', null, C__RECORD_STATUS__NORMAL);

                while ($l_cpu_row = $l_cpu_res->get_row())
                {
                    $l_cpu += $l_cpu_row["isys_catg_cpu_list__frequency"];
                } // while

                if (isys_convert::frequency($l_cpu, "C__FREQUENCY_UNIT__GHZ", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $l_cpu      = isys_convert::frequency($l_cpu, "C__FREQUENCY_UNIT__GHZ", C__CONVERT_DIRECTION__BACKWARD);
                    $l_cpu_unit = _L("LC__CMDB__FREQUENCY_UNIT__GHZ");
                }
                else
                {
                    $l_cpu      = isys_convert::frequency($l_cpu, "C__FREQUENCY_UNIT__MHZ", C__CONVERT_DIRECTION__BACKWARD);
                    $l_cpu_unit = _L("LC__CMDB__FREQUENCY_UNIT__MHZ");
                } // if

                $l_max_speed = $l_dao_port->get_max_speed($l_row["isys_obj__id"], "C__PORT_SPEED__MBIT_S");

                $l_port_speed = $p_cat->retrieve(
                    "SELECT isys_port_speed__title FROM isys_port_speed WHERE isys_port_speed__const = " . $p_cat->convert_sql_text("C__PORT_SPEED__MBIT_S")
                )
                    ->get_row();

                $l_system_drive_res = $l_dao_drive->get_system_drives($l_row["isys_obj__id"]);

                while ($l_system_drive_row = $l_system_drive_res->get_row())
                {
                    $l_disc_space += $l_system_drive_row["isys_catg_drive_list__capacity"];
                } // while

                if (isys_convert::memory($l_disc_space, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $l_disc_space      = isys_convert::memory($l_disc_space, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    $l_disc_space_unit = _L("LC__CMDB__MEMORY_UNIT__GB");
                }
                else
                {
                    $l_disc_space      = isys_convert::memory($l_disc_space, "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD);
                    $l_disc_space_unit = _L("LC__CMDB__MEMORY_UNIT__MB");
                } // if

                $l_vm_list[$l_row["isys_catg_virtual_machine_list__id"]] = [
                    "object"                => $l_service_title,
                    "object_id"             => $l_row["isys_obj__id"],
                    "object_link"           => $l_quick_info->get_quick_info($l_row["isys_obj__id"], $l_service_title, C__LINK__OBJECT),
                    "object_type"           => $l_row["isys_obj__isys_obj_type__id"],
                    "cluster_service_title" => "",
                    "memory"                => $l_memory,
                    "memory_unit"           => $l_memory_unit,
                    "cpu"                   => $l_cpu,
                    "cpu_unit"              => $l_cpu_unit,
                    "disc_space"            => $l_disc_space,
                    "disc_space_unit"       => $l_disc_space_unit,
                    "bandwidth"             => $l_max_speed,
                    "bandwidth_unit"        => $l_port_speed["isys_port_speed__title"],
                    "vm_title"              => _L('LC__CMDB__CATG__VIRTUAL_MACHINE'),
                    "primary"               => $l_row["isys_catg_virtual_machine_list__primary"]
                ];
            }
        }

        $this->get_template_component()
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->assign("bShowCommentary", "0")
            ->assignByRef("c_members", $l_members)
            ->assignByRef("coords", $l_coords)
            ->assignByRef("coords_two", $l_coords2)
            ->assignByRef("consumption", $l_consumption)
            ->assignByRef("cluster_service_list", $l_cluster_service_list)
            ->assignByRef("virtual_machine_list", $l_vm_list);
    } // function
} // class