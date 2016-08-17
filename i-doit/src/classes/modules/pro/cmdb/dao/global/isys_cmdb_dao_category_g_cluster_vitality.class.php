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
 * DAO: Global category for shared storage
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_cluster_vitality extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'cluster_vitality';

    /**
     * @param $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_vms($p_obj_id)
    {
        $l_dao_vm = new isys_cmdb_dao_category_g_virtual_machine($this->m_db);

        return $l_dao_vm->get_data(
            null,
            null,
            " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id),
            null,
            C__RECORD_STATUS__NORMAL
        );
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   boolean $p_virtualHost
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_virtualHost, $p_connectedObjID, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_old_data = $this->get_data($p_cat_level)
            ->__to_array();

        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection->update_connection($l_old_data["isys_catg_cluster_list__isys_connection__id"], $p_connectedObjID);

        $l_strSql = "UPDATE isys_catg_cluster_list SET " . "isys_catg_cluster_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_cluster_list__virtual_host = " . $this->convert_sql_id(
                $p_virtualHost
            ) . ", " . "isys_catg_cluster_list__status = " . $p_status . " " . "WHERE isys_catg_cluster_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql))
        {
            return $this->apply_update();
        }
        else
        {
            return false;
        } // if
    }

    /**
     * Gets Cluster type as String.
     *
     * @param   integer $p_list_id
     *
     * @return  string
     */
    public function get_cluster_type_as_string($p_list_id)
    {
        $l_sql = "SELECT isys_cluster_type__title FROM isys_cluster_type WHERE isys_cluster_type__id = " . $this->convert_sql_id($p_list_id);

        return $this->retrieve($l_sql)
            ->get_row_value('isys_cluster_type__title');
    }

    /**
     * Creates members and coordinates
     *
     * @param int $p_obj_id
     *
     * @return array
     */
    public function get_members_and_coordinates($p_obj_id)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_dao_memory = new isys_cmdb_dao_category_g_memory($this->get_database_component());
        $l_dao_cpu    = new isys_cmdb_dao_category_g_cpu($this->get_database_component());
        $l_dao_port   = new isys_cmdb_dao_category_g_network_port($this->get_database_component());
        $l_dao_drive  = new isys_cmdb_dao_category_g_drive($this->get_database_component());
        $l_dao_vm     = new isys_cmdb_dao_category_g_virtual_machine($this->get_database_component());

        $l_cluster_members = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);
        $l_cluster_vms     = $this->get_vms($p_obj_id);

        while ($l_vm_row = $l_cluster_vms->get_row())
        {
            $l_cluster_vm_arr[$l_vm_row["isys_catg_virtual_machine_list__id"]] = $l_vm_row["isys_catg_virtual_machine_list__primary"];
        } // while

        while ($l_row = $l_cluster_members->get_row())
        {

            $l_memory     = 0;
            $l_max_speed  = 0;
            $l_disc_space = 0;
            $l_cpu        = 0;

            // Coordinates
            if (!is_null($l_row["isys_catg_cluster_service_list__id"]) && $l_row["isys_catg_cluster_service_list__status"] == C__RECORD_STATUS__NORMAL)
            {
                $l_coords[$l_row["isys_obj__id"]]["service_" . $l_row["isys_catg_cluster_service_list__id"]]  = $l_row;
                $l_coords2["service_" . $l_row["isys_catg_cluster_service_list__id"]][$l_row["isys_obj__id"]] = $l_row;

                $l_default_member = $this->get_data($l_row["isys_catg_cluster_service_list__cluster_members_list__id"])
                    ->get_row();

                if ($l_default_member["isys_catg_cluster_members_list__id"] != "")
                {
                    $l_coords[$l_row["isys_obj__id"]]["service_" . $l_row["isys_catg_cluster_service_list__id"]]["default_member"]  = $l_default_member["isys_obj__id"];
                    $l_coords2["service_" . $l_row["isys_catg_cluster_service_list__id"]][$l_row["isys_obj__id"]]["default_member"] = $l_default_member["isys_obj__id"];
                }

                $l_cluster_type = $this->get_cluster_type_as_string($l_row["isys_cluster_type__id"]);

                switch ($l_cluster_type)
                {
                    case "LC__CLUSTER_TYPE__ACTIVE_PASSIVE":
                        if ($l_row["isys_obj__id"] == $l_default_member["isys_obj__id"])
                        {
                            $l_coords[$l_row["isys_obj__id"]]["service_" . $l_row["isys_catg_cluster_service_list__id"]]["cluster_type"]  = "LC__CLUSTER_TYPE__ACTIVE";
                            $l_coords2["service_" . $l_row["isys_catg_cluster_service_list__id"]][$l_row["isys_obj__id"]]["cluster_type"] = "LC__CLUSTER_TYPE__ACTIVE";
                        }
                        else
                        {
                            $l_coords[$l_row["isys_obj__id"]]["service_" . $l_row["isys_catg_cluster_service_list__id"]]["cluster_type"]  = "LC__CLUSTER_TYPE__PASSIVE";
                            $l_coords2["service_" . $l_row["isys_catg_cluster_service_list__id"]][$l_row["isys_obj__id"]]["cluster_type"] = "LC__CLUSTER_TYPE__PASSIVE";
                        }
                        break;
                    case "LC__CLUSTER_TYPE__ACTIVE_ACTIVE":
                        $l_coords[$l_row["isys_obj__id"]]["service_" . $l_row["isys_catg_cluster_service_list__id"]]["cluster_type"]  = "LC__CLUSTER_TYPE__ACTIVE";
                        $l_coords2["service_" . $l_row["isys_catg_cluster_service_list__id"]][$l_row["isys_obj__id"]]["cluster_type"] = "LC__CLUSTER_TYPE__ACTIVE";
                        break;
                    case "LC__CLUSTER_TYPE__HPC":
                        $l_coords[$l_row["isys_obj__id"]]["service_" . $l_row["isys_catg_cluster_service_list__id"]]["cluster_type"]  = "LC__CLUSTER_TYPE__HPC";
                        $l_coords2["service_" . $l_row["isys_catg_cluster_service_list__id"]][$l_row["isys_obj__id"]]["cluster_type"] = "LC__CLUSTER_TYPE__HPC";
                        break;
                    default:
                        break;
                }
            }

            // Members
            if (!isset($l_members[$l_row["isys_obj__id"]]))
            {
                $l_memory                          = 0;
                $l_max_speed                       = 0;
                $l_disc_space                      = 0;
                $l_cpu                             = 0;
                $l_members[$l_row["isys_obj__id"]] = $l_row;

                $l_res_vm = $l_dao_vm->get_data(
                    null,
                    null,
                    " AND isys_catg_virtual_machine_list__primary = " . $this->convert_sql_id($l_row["isys_obj__id"]),
                    null,
                    C__RECORD_STATUS__NORMAL
                );

                if ($l_res_vm->num_rows() > 0)
                {
                    $l_row_vm                                             = $l_res_vm->get_row();
                    $l_members[$l_row["isys_obj__id"]]["virtual_machine"] = $l_row_vm["isys_catg_virtual_machine_list__isys_obj__id"];
                }

                if (strlen($l_row["isys_obj__title"]) > 17)
                {
                    $l_obj_title = substr($l_row["isys_obj__title"], 0, 13);
                    $l_obj_title .= "...";
                }
                else
                {
                    $l_obj_title = $l_row["isys_obj__title"];
                }

                $l_members[$l_row["isys_obj__id"]]["link"] = $l_quick_info->get_quick_info($l_row["isys_obj__id"], $l_obj_title, C__LINK__OBJECT);

                $l_memory_res = $l_dao_memory->get_memory($l_row["isys_obj__id"]);

                while ($l_memory_row = $l_memory_res->get_row())
                {
                    $l_memory += isys_convert::memory($l_memory_row["isys_catg_memory_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                }

                $l_members[$l_row["isys_obj__id"]]["memory"]      = $l_memory;
                $l_members[$l_row["isys_obj__id"]]["memory_rest"] = $l_memory;
                $l_members[$l_row["isys_obj__id"]]["memory_unit"] = "GB";

                $l_cpu_res = $l_dao_cpu->get_data(null, $l_row["isys_obj__id"]);

                while ($l_cpu_row = $l_cpu_res->get_row())
                {
                    $l_cpu += isys_convert::frequency($l_cpu_row["isys_catg_cpu_list__frequency"], C__FREQUENCY_UNIT__GHZ, C__CONVERT_DIRECTION__BACKWARD);
                }

                $l_members[$l_row["isys_obj__id"]]["cpu"]      = $l_cpu;
                $l_members[$l_row["isys_obj__id"]]["cpu_rest"] = $l_cpu;
                $l_members[$l_row["isys_obj__id"]]["cpu_unit"] = "GHz";

                $l_max_speed = $l_dao_port->get_max_speed($l_row["isys_obj__id"], "C__PORT_SPEED__MBIT_S");

                $l_members[$l_row["isys_obj__id"]]["bandwidth"]      = $l_max_speed;
                $l_members[$l_row["isys_obj__id"]]["bandwidth_rest"] = $l_max_speed;
                $l_port_speed                                        = $this->retrieve(
                    "SELECT isys_port_speed__title FROM isys_port_speed WHERE isys_port_speed__const = " . $this->convert_sql_text("C__PORT_SPEED__MBIT_S")
                )
                    ->get_row_value("isys_port_speed__title");
                $l_members[$l_row["isys_obj__id"]]["bandwidth_unit"] = $l_port_speed;

                $l_system_drive_res = $l_dao_drive->get_system_drives($l_row["isys_obj__id"]);

                while ($l_system_drive_row = $l_system_drive_res->get_row())
                {
                    $l_disc_space += isys_convert::memory($l_system_drive_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                }

                $l_members[$l_row["isys_obj__id"]]["disc_space"]      = $l_disc_space;
                $l_members[$l_row["isys_obj__id"]]["disc_space_rest"] = $l_disc_space;
                $l_members[$l_row["isys_obj__id"]]["disc_space_unit"] = "GB";

            }

            if (isset($l_row["isys_catg_cluster_service_list__id"]))
            {
                $l_members[$l_row["isys_obj__id"]]["services"] .= $l_row["isys_catg_cluster_service_list__id"] . ",";
            }

            if (is_array($l_cluster_vm_arr) && in_array($l_row["isys_obj__id"], $l_cluster_vm_arr))
            {
                // Koordinaten fuer virtuelle maschinen
                foreach ($l_cluster_vm_arr AS $l_key => $l_val)
                {

                    if ($l_val == $l_row["isys_obj__id"])
                    {
                        $l_vm_arr = $l_dao_vm->get_data($l_key, null, "", null, C__RECORD_STATUS__NORMAL)
                            ->get_row();

                        $l_coords[$l_row["isys_obj__id"]]["virtual_" . $l_key]  = $l_vm_arr;
                        $l_coords2["virtual_" . $l_key][$l_row["isys_obj__id"]] = $l_vm_arr;

                        if (!in_array($l_key, (array) $l_members[$l_row["isys_obj__id"]]["vms_arr"]))
                        {
                            $l_members[$l_row["isys_obj__id"]]["vms"] .= $l_key . ',';
                            $l_members[$l_row["isys_obj__id"]]["vms_arr"][] = $l_key;
                        } // if
                    } // if
                } // foreach
                $l_members[$l_row["isys_obj__id"]]["vms"] = rtrim($l_members[$l_row["isys_obj__id"]]["vms"], ',');
            } // if
        } // while

        $l_consumption_arr = $this->get_consumption_and_resources($l_coords, $l_coords2);

        $this->calculate_consumption_and_resources($l_consumption_arr, $l_members);

        $l_arr = [
            "members"      => $l_members,
            "coordinates"  => $l_coords,
            "coordinates2" => $l_coords2,
            "consumption"  => $l_consumption_arr,
        ];

        return $l_arr;
    } // function

    public function delete_cluster_service($p_catg_id = null, $p_obj_id = null)
    {

        $l_sql = "DELETE FROM isys_catg_cluster_service_list WHERE TRUE ";

        if ($p_catg_id != null)
        {
            $l_sql .= " AND isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_catg_id);
        }

        if ($p_obj_id != null)
        {
            $l_sql .= " AND isys_catg_cluster_service_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        }

        return ($this->update($l_sql) && $this->apply_update());
    }

    public function attachObjects(array $p_post)
    {
        return null;
    } // function

    /**
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        return 1;
    } // function

    /**
     * Return Category Data
     *
     * @param [int $p_id]
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM `isys_catg_cluster_members_list` " . "INNER JOIN isys_connection " . "ON " . "isys_catg_cluster_members_list__isys_connection__id = isys_connection__id " . "INNER JOIN isys_obj " . "ON " . "isys_connection__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_catg_cluster_members_list_2_isys_catg_cluster_service_list " . "ON " . "isys_catg_cluster_members_list.isys_catg_cluster_members_list__id = isys_catg_cluster_members_list_2_isys_catg_cluster_service_list.isys_catg_cluster_members_list__id " . "LEFT OUTER JOIN isys_catg_cluster_service_list " . "ON " . "isys_catg_cluster_service_list.isys_catg_cluster_service_list__id = isys_catg_cluster_members_list_2_isys_catg_cluster_service_list.isys_catg_cluster_service_list__id " . "LEFT OUTER JOIN isys_cluster_type " . "ON " . "isys_catg_cluster_service_list.isys_catg_cluster_service_list__isys_cluster_type__id = isys_cluster_type__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id))
        {
            $l_sql .= " AND (isys_catg_cluster_members_list.isys_catg_cluster_members_list__id = '{$p_catg_list_id}') ";
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND (isys_obj__status = '{$p_status}') AND (isys_catg_cluster_members_list__status = '{$p_status}') ";
        }

        $l_sql .= "ORDER BY isys_obj__title";

        return $this->retrieve($l_sql);
    } // function

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (isys_catg_cluster_members_list.isys_catg_cluster_members_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_catg_cluster_members_list.isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    }

    /**
     * @param bool $p_create
     *
     * @return bool
     */
    public function save_user_data($p_create)
    {
        return true;
    }

    /**
     * Calculates the overall consumption for each member and resources for each coordinate
     *
     * @param array $p_coords
     * @param array $p_coords2
     *
     * @return array
     */
    private function get_consumption_and_resources(&$p_coords, &$p_coords2)
    {
        $l_dao_con      = new isys_cmdb_dao_connection($this->get_database_component());
        $l_dao_resource = new isys_cmdb_dao_category_g_computing_resources($this->get_database_component());
        $l_dao_memory   = new isys_cmdb_dao_category_g_memory($this->get_database_component());
        $l_dao_cpu      = new isys_cmdb_dao_category_g_cpu($this->get_database_component());
        $l_dao_port     = new isys_cmdb_dao_category_g_network_port($this->get_database_component());
        $l_dao_drive    = new isys_cmdb_dao_category_g_drive($this->get_database_component());

        $l_consumption = [];

        if (is_array($p_coords2))
        {
            foreach ($p_coords2 as $l_service_id => $l_service_val)
            {

                if (strstr($l_service_id, "service"))
                {

                    foreach ($l_service_val as $l_object_id => $l_service)
                    {

                        $l_resource_arr = $l_dao_resource->get_data(
                            null,
                            $l_dao_con->get_object_id_by_connection($l_service["isys_catg_cluster_service_list__isys_connection__id"]),
                            "",
                            null,
                            C__RECORD_STATUS__NORMAL
                        )
                            ->get_row();

                        $l_disc_space_unit = $this->retrieve(
                            "SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = " . $this->convert_sql_id(
                                $l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]
                            )
                        )
                            ->get_row_value('isys_memory_unit__title');
                        $l_memory_unit     = $this->retrieve(
                            "SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = " . $this->convert_sql_id(
                                $l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]
                            )
                        )
                            ->get_row_value('isys_memory_unit__title');
                        $l_cpu_unit        = $this->retrieve(
                            "SELECT isys_frequency_unit__title FROM isys_frequency_unit WHERE isys_frequency_unit__id = " . $this->convert_sql_id(
                                $l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]
                            )
                        )
                            ->get_row_value('isys_frequency_unit__title');
                        $l_bandwidth_unit  = $this->retrieve(
                            "SELECT isys_port_speed__title FROM isys_port_speed WHERE isys_port_speed__id = " . $this->convert_sql_id(
                                $l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]
                            )
                        )
                            ->get_row_value('isys_port_speed__title');

                        switch ($l_service["cluster_type"])
                        {
                            case "LC__CLUSTER_TYPE__PASSIVE":
                                $p_coords2[$l_service_id][$l_object_id]["memory"]     = 0;
                                $p_coords2[$l_service_id][$l_object_id]["cpu"]        = 0;
                                $p_coords2[$l_service_id][$l_object_id]["disc_space"] = 0;
                                $p_coords2[$l_service_id][$l_object_id]["bandwidth"]  = 0;

                                $p_coords[$l_object_id][$l_service_id]["memory"]     = 0;
                                $p_coords[$l_object_id][$l_service_id]["cpu"]        = 0;
                                $p_coords[$l_object_id][$l_service_id]["disc_space"] = 0;
                                $p_coords[$l_object_id][$l_service_id]["bandwidth"]  = 0;

                                $l_consumption[$l_object_id]["memory"] += 0;
                                $l_consumption[$l_object_id]["cpu"] += 0;
                                $l_consumption[$l_object_id]["disc_space"] += 0;
                                $l_consumption[$l_object_id]["bandwidth"] += 0;

                                break;
                            case "LC__CLUSTER_TYPE__ACTIVE":
                                $p_coords2[$l_service_id][$l_object_id]["memory"]     = isys_convert::memory(
                                    $l_resource_arr["isys_catg_computing_resources_list__ram"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords2[$l_service_id][$l_object_id]["cpu"]        = isys_convert::frequency(
                                    $l_resource_arr["isys_catg_computing_resources_list__cpu"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords2[$l_service_id][$l_object_id]["disc_space"] = isys_convert::memory(
                                    $l_resource_arr["isys_catg_computing_resources_list__disc_space"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords2[$l_service_id][$l_object_id]["bandwidth"]  = isys_convert::speed(
                                    $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );

                                $p_coords[$l_object_id][$l_service_id]["memory"]     = isys_convert::memory(
                                    $l_resource_arr["isys_catg_computing_resources_list__ram"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords[$l_object_id][$l_service_id]["cpu"]        = isys_convert::frequency(
                                    $l_resource_arr["isys_catg_computing_resources_list__cpu"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords[$l_object_id][$l_service_id]["disc_space"] = isys_convert::memory(
                                    $l_resource_arr["isys_catg_computing_resources_list__disc_space"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords[$l_object_id][$l_service_id]["bandwidth"]  = isys_convert::speed(
                                    $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"],
                                    intval($l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]),
                                    C__CONVERT_DIRECTION__BACKWARD
                                );

                                $l_consumption[$l_object_id]["memory"] += $l_resource_arr["isys_catg_computing_resources_list__ram"];
                                $l_consumption[$l_object_id]["cpu"] += $l_resource_arr["isys_catg_computing_resources_list__cpu"];
                                $l_consumption[$l_object_id]["disc_space"] += $l_resource_arr["isys_catg_computing_resources_list__disc_space"];
                                $l_consumption[$l_object_id]["bandwidth"] += $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"];

                                break;
                            case "LC__CLUSTER_TYPE__HPC":
                                $l_divisor = count($p_coords2[$l_service_id]);

                                $p_coords2[$l_service_id][$l_object_id]["memory"]     = isys_convert::memory(
                                        $l_resource_arr["isys_catg_computing_resources_list__ram"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;
                                $p_coords2[$l_service_id][$l_object_id]["cpu"]        = isys_convert::frequency(
                                        $l_resource_arr["isys_catg_computing_resources_list__cpu"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;
                                $p_coords2[$l_service_id][$l_object_id]["disc_space"] = isys_convert::memory(
                                        $l_resource_arr["isys_catg_computing_resources_list__disc_space"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;
                                $p_coords2[$l_service_id][$l_object_id]["bandwidth"]  = isys_convert::speed(
                                        $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;

                                $p_coords[$l_object_id][$l_service_id]["memory"]     = isys_convert::memory(
                                        $l_resource_arr["isys_catg_computing_resources_list__ram"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;
                                $p_coords[$l_object_id][$l_service_id]["cpu"]        = isys_convert::frequency(
                                        $l_resource_arr["isys_catg_computing_resources_list__cpu"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;
                                $p_coords[$l_object_id][$l_service_id]["disc_space"] = isys_convert::memory(
                                        $l_resource_arr["isys_catg_computing_resources_list__disc_space"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;
                                $p_coords[$l_object_id][$l_service_id]["bandwidth"]  = isys_convert::speed(
                                        $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"],
                                        intval($l_resource_arr["isys_catg_computing_resources_list__nb__isys_port_speed__id"]),
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) / $l_divisor;

                                $l_consumption[$l_object_id]["memory"] += $l_resource_arr["isys_catg_computing_resources_list__ram"] / $l_divisor;
                                $l_consumption[$l_object_id]["cpu"] += $l_resource_arr["isys_catg_computing_resources_list__cpu"] / $l_divisor;
                                $l_consumption[$l_object_id]["disc_space"] += $l_resource_arr["isys_catg_computing_resources_list__disc_space"] / $l_divisor;
                                $l_consumption[$l_object_id]["bandwidth"] += $l_resource_arr["isys_catg_computing_resources_list__network_bandwidth"] / $l_divisor;
                                break;
                            default:
                                break;
                        }

                        $p_coords2[$l_service_id][$l_object_id]["memory"]     = (strstr($p_coords2[$l_service_id][$l_object_id]["memory"], ".")) ? round(
                            $p_coords2[$l_service_id][$l_object_id]["memory"],
                            2
                        ) : $p_coords2[$l_service_id][$l_object_id]["memory"];
                        $p_coords2[$l_service_id][$l_object_id]["cpu"]        = (strstr($p_coords2[$l_service_id][$l_object_id]["cpu"], ".")) ? round(
                            $p_coords2[$l_service_id][$l_object_id]["cpu"],
                            2
                        ) : $p_coords2[$l_service_id][$l_object_id]["cpu"];
                        $p_coords2[$l_service_id][$l_object_id]["disc_space"] = (strstr($p_coords2[$l_service_id][$l_object_id]["disc_space"], ".")) ? round(
                            $p_coords2[$l_service_id][$l_object_id]["disc_space"],
                            2
                        ) : $p_coords2[$l_service_id][$l_object_id]["disc_space"];
                        $p_coords2[$l_service_id][$l_object_id]["bandwidth"]  = (strstr($p_coords2[$l_service_id][$l_object_id]["bandwidth"], ".")) ? round(
                            $p_coords2[$l_service_id][$l_object_id]["bandwidth"],
                            2
                        ) : $p_coords2[$l_service_id][$l_object_id]["bandwidth"];

                        $p_coords[$l_object_id][$l_service_id]["memory"]     = (strstr($p_coords[$l_object_id][$l_service_id]["memory"], ".")) ? round(
                            $p_coords[$l_object_id][$l_service_id]["memory"],
                            2
                        ) : $p_coords[$l_object_id][$l_service_id]["memory"];
                        $p_coords[$l_object_id][$l_service_id]["cpu"]        = (strstr($p_coords[$l_object_id][$l_service_id]["cpu"], ".")) ? round(
                            $p_coords[$l_object_id][$l_service_id]["cpu"],
                            2
                        ) : $p_coords[$l_object_id][$l_service_id]["cpu"];
                        $p_coords[$l_object_id][$l_service_id]["disc_space"] = (strstr($p_coords[$l_object_id][$l_service_id]["disc_space"], ".")) ? round(
                            $p_coords[$l_object_id][$l_service_id]["disc_space"],
                            2
                        ) : $p_coords[$l_object_id][$l_service_id]["disc_space"];
                        $p_coords[$l_object_id][$l_service_id]["bandwidth"]  = (strstr($p_coords[$l_object_id][$l_service_id]["bandwidth"], ".")) ? round(
                            $p_coords[$l_object_id][$l_service_id]["bandwidth"],
                            2
                        ) : $p_coords[$l_object_id][$l_service_id]["bandwidth"];

                        $p_coords2[$l_service_id][$l_object_id]["memory_unit"]     = $l_memory_unit;
                        $p_coords2[$l_service_id][$l_object_id]["cpu_unit"]        = $l_cpu_unit;
                        $p_coords2[$l_service_id][$l_object_id]["disc_space_unit"] = $l_disc_space_unit;
                        $p_coords2[$l_service_id][$l_object_id]["bandwidth_unit"]  = $l_bandwidth_unit;

                        $p_coords[$l_object_id][$l_service_id]["memory_unit"]     = $l_memory_unit;
                        $p_coords[$l_object_id][$l_service_id]["cpu_unit"]        = $l_cpu_unit;
                        $p_coords[$l_object_id][$l_service_id]["disc_space_unit"] = $l_disc_space_unit;
                        $p_coords[$l_object_id][$l_service_id]["bandwidth_unit"]  = $l_bandwidth_unit;
                    }
                }
                else
                {
                    if (is_array($l_service_val))
                    {
                        foreach ($l_service_val AS $l_object_id => $l_vm)
                        {

                            $l_memory                 = 0;
                            $l_memory_consumption     = 0;
                            $l_max_speed              = 0;
                            $l_max_speed_consumption  = 0;
                            $l_disc_space             = 0;
                            $l_disc_space_consumption = 0;
                            $l_cpu                    = 0;
                            $l_cpu_consumption        = 0;

                            $l_memory_res = $l_dao_memory->get_memory($l_vm["isys_obj__id"]);

                            while ($l_memory_row = $l_memory_res->get_row())
                            {
                                $l_memory += $l_memory_row["isys_catg_memory_list__capacity"];
                                $l_memory_consumption += $l_memory_row["isys_catg_memory_list__capacity"];
                            }

                            if (isys_convert::memory($l_memory, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                            {
                                $l_memory                                              = isys_convert::memory($l_memory, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                                $p_coords2[$l_service_id][$l_object_id]["memory_unit"] = "GB";
                                $p_coords[$l_object_id][$l_service_id]["memory_unit"]  = "GB";
                            }
                            else
                            {
                                $l_memory                                              = isys_convert::memory($l_memory, "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD);
                                $p_coords2[$l_service_id][$l_object_id]["memory_unit"] = "MB";
                                $p_coords[$l_object_id][$l_service_id]["memory_unit"]  = "MB";
                            }

                            $l_cpu_res = $l_dao_cpu->get_data(null, $l_vm["isys_obj__id"]);

                            while ($l_cpu_row = $l_cpu_res->get_row())
                            {
                                $l_cpu += isys_convert::frequency($l_cpu_row["isys_catg_cpu_list__frequency"], C__FREQUENCY_UNIT__GHZ, C__CONVERT_DIRECTION__BACKWARD);
                                $l_cpu_consumption += isys_convert::frequency($l_cpu, "C__FREQUENCY_UNIT__GHZ");
                            }

                            $l_max_speed             = $l_dao_port->get_max_speed($l_vm["isys_obj__id"], "C__PORT_SPEED__MBIT_S");
                            $l_max_speed_consumption = $l_dao_port->get_max_speed($l_vm["isys_obj__id"]);

                            $l_system_drive_res = $l_dao_drive->get_system_drives($l_vm["isys_obj__id"]);

                            while ($l_system_drive_row = $l_system_drive_res->get_row())
                            {
                                $l_disc_space += $l_system_drive_row["isys_catg_drive_list__capacity"];
                                $l_disc_space_consumption += $l_system_drive_row["isys_catg_drive_list__capacity"];
                            }

                            if (isys_convert::memory($l_disc_space, "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                            {
                                $l_disc_space                                              = isys_convert::memory(
                                    $l_disc_space,
                                    "C__MEMORY_UNIT__GB",
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords2[$l_service_id][$l_object_id]["disc_space_unit"] = "GB";
                                $p_coords[$l_object_id][$l_service_id]["disc_space_unit"]  = "GB";
                            }
                            else
                            {
                                $l_disc_space                                              = isys_convert::memory(
                                    $l_disc_space,
                                    "C__MEMORY_UNIT__MB",
                                    C__CONVERT_DIRECTION__BACKWARD
                                );
                                $p_coords2[$l_service_id][$l_object_id]["disc_space_unit"] = "MB";
                                $p_coords[$l_object_id][$l_service_id]["disc_space_unit"]  = "MB";
                            }

                            $p_coords2[$l_service_id][$l_object_id]["memory"]     = (strstr($l_memory, ".")) ? round($l_memory, 2) : $l_memory;
                            $p_coords2[$l_service_id][$l_object_id]["cpu"]        = (strstr($l_cpu, ".")) ? round($l_cpu, 2) : $l_cpu;
                            $p_coords2[$l_service_id][$l_object_id]["disc_space"] = (strstr($l_disc_space, ".")) ? round($l_disc_space, 2) : $l_disc_space;
                            $p_coords2[$l_service_id][$l_object_id]["bandwidth"]  = (strstr($l_max_speed, ".")) ? round($l_max_speed, 2) : $l_max_speed;

                            $p_coords[$l_object_id][$l_service_id]["memory"]     = (strstr($l_memory, ".")) ? round($l_memory, 2) : $l_memory;
                            $p_coords[$l_object_id][$l_service_id]["cpu"]        = (strstr($l_cpu, ".")) ? round($l_cpu, 2) : $l_cpu;
                            $p_coords[$l_object_id][$l_service_id]["disc_space"] = (strstr($l_disc_space, ".")) ? round($l_disc_space, 2) : $l_disc_space;
                            $p_coords[$l_object_id][$l_service_id]["bandwidth"]  = (strstr($l_max_speed, ".")) ? round($l_max_speed, 2) : $l_max_speed;

                            $l_consumption[$l_object_id]["memory"] += $l_memory_consumption;
                            $l_consumption[$l_object_id]["cpu"] += $l_cpu_consumption;
                            $l_consumption[$l_object_id]["disc_space"] += $l_disc_space_consumption;
                            $l_consumption[$l_object_id]["bandwidth"] += $l_max_speed_consumption;

                            //$p_coords2[$l_service_id][$l_object_id]["memory_unit"] = "GB";
                            $p_coords2[$l_service_id][$l_object_id]["cpu_unit"] = "GHz";
                            //$p_coords2[$l_service_id][$l_object_id]["disc_space_unit"] = "GB";
                            $p_coords2[$l_service_id][$l_object_id]["bandwidth_unit"] = "MBit/s";

                            //$p_coords[$l_object_id][$l_service_id]["memory_unit"] = "GB";
                            $p_coords[$l_object_id][$l_service_id]["cpu_unit"] = "GHz";
                            //$p_coords[$l_object_id][$l_service_id]["disc_space_unit"] = "GB";
                            $p_coords[$l_object_id][$l_service_id]["bandwidth_unit"] = "MBit/s";

                        }
                    }
                }
            }
        }

        return $l_consumption;
    }

    /**
     * Recalculates the over consumption and determines the overall resources for every member
     *
     * @param array $p_consumption
     * @param array $p_members
     */
    private function calculate_consumption_and_resources(&$p_consumption, &$p_members)
    {
        if (is_array($p_consumption))
        {
            foreach ($p_consumption AS $l_obj_id => $l_consume)
            {

                if (isys_convert::memory($l_consume["memory"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $p_consumption[$l_obj_id]["memory"]      = isys_convert::memory($l_consume["memory"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["memory_unit"] = 'LC__CMDB__MEMORY_UNIT__GB';
                    $p_members[$l_obj_id]["memory_rest"]     = $p_members[$l_obj_id]["memory"] - $p_consumption[$l_obj_id]["memory"];
                }
                else
                {
                    $p_members[$l_obj_id]["memory_rest"]     = $p_members[$l_obj_id]["memory"] - isys_convert::memory(
                            $l_consume["memory"],
                            "C__MEMORY_UNIT__GB",
                            C__CONVERT_DIRECTION__BACKWARD
                        );
                    $p_consumption[$l_obj_id]["memory"]      = isys_convert::memory($l_consume["memory"], "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["memory_unit"] = 'LC__CMDB__MEMORY_UNIT__MB';
                }

                $p_members[$l_obj_id]["memory_rest"] = ((strstr($p_members[$l_obj_id]["memory_rest"], ".")) ? round(
                    $p_members[$l_obj_id]["memory_rest"],
                    2
                ) : $p_members[$l_obj_id]["memory_rest"]);

                if (isys_convert::frequency($l_consume["cpu"], "C__FREQUENCY_UNIT__GHZ", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $p_consumption[$l_obj_id]["cpu"]      = isys_convert::frequency($l_consume["cpu"], "C__FREQUENCY_UNIT__GHZ", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["cpu_unit"] = 'LC__CMDB__FREQUENCY_UNIT__GHZ';
                    $p_members[$l_obj_id]["cpu_rest"]     = $p_members[$l_obj_id]["cpu"] - $p_consumption[$l_obj_id]["cpu"];
                }
                else
                {
                    $p_members[$l_obj_id]["cpu_rest"]     = $p_members[$l_obj_id]["cpu"] - isys_convert::frequency(
                            $l_consume["cpu"],
                            "C__FREQUENCY_UNIT__GHZ",
                            C__CONVERT_DIRECTION__BACKWARD
                        );
                    $p_consumption[$l_obj_id]["cpu"]      = isys_convert::frequency($l_consume["cpu"], "C__FREQUENCY_UNIT__MHZ", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["cpu_unit"] = 'LC__CMDB__FREQUENCY_UNIT__MHZ';
                }

                $p_members[$l_obj_id]["cpu_rest"] = ((strstr($p_members[$l_obj_id]["cpu_rest"], ".")) ? round(
                    $p_members[$l_obj_id]["cpu_rest"],
                    2
                ) : $p_members[$l_obj_id]["cpu_rest"]);

                if (isys_convert::memory($l_consume["disc_space"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $p_consumption[$l_obj_id]["disc_space"]      = isys_convert::memory($l_consume["disc_space"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["disc_space_unit"] = 'LC__CMDB__MEMORY_UNIT__GB';
                    $p_members[$l_obj_id]["disc_space_rest"]     = isys_convert::memory(
                        (isys_convert::memory($p_members[$l_obj_id]["disc_space"], "C__MEMORY_UNIT__GB") - $l_consume["disc_space"]),
                        "C__MEMORY_UNIT__GB",
                        C__CONVERT_DIRECTION__BACKWARD
                    );

                }
                else
                {
                    $p_members[$l_obj_id]["disc_space_rest"]     = $p_members[$l_obj_id]["disc_space"] - isys_convert::memory(
                            $l_consume["disc_space"],
                            "C__MEMORY_UNIT__GB",
                            C__CONVERT_DIRECTION__BACKWARD
                        );
                    $p_consumption[$l_obj_id]["disc_space"]      = isys_convert::memory($l_consume["disc_space"], "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["disc_space_unit"] = 'LC__CMDB__MEMORY_UNIT__MB';
                }

                $p_members[$l_obj_id]["disc_space_rest"] = ((strstr($p_members[$l_obj_id]["disc_space_rest"], ".")) ? round(
                    $p_members[$l_obj_id]["disc_space_rest"],
                    2
                ) : $p_members[$l_obj_id]["disc_space_rest"]);

                if (isys_convert::speed($l_consume["bandwidth"], "C__PORT_SPEED__MBIT_S", C__CONVERT_DIRECTION__BACKWARD) >= 1)
                {
                    $p_consumption[$l_obj_id]["bandwidth"]      = isys_convert::speed($l_consume["bandwidth"], "C__PORT_SPEED__MBIT_S", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["bandwidth_unit"] = 'LC__CMDB__PORT_SPEED__MBITS';
                    $p_members[$l_obj_id]["bandwidth_rest"]     = $p_members[$l_obj_id]["bandwidth"] - $p_consumption[$l_obj_id]["bandwidth"];
                }
                else
                {
                    $p_members[$l_obj_id]["bandwidth_rest"]     = $p_members[$l_obj_id]["bandwidth"] - isys_convert::speed(
                            $l_consume["bandwidth"],
                            "C__PORT_SPEED__MBIT_S",
                            C__CONVERT_DIRECTION__BACKWARD
                        );
                    $p_consumption[$l_obj_id]["bandwidth"]      = isys_convert::speed($l_consume["bandwidth"], "C__PORT_SPEED__KBIT_S", C__CONVERT_DIRECTION__BACKWARD);
                    $p_consumption[$l_obj_id]["bandwidth_unit"] = 'LC__CMDB__PORT_SPEED__KBITS';
                }

                $p_members[$l_obj_id]["bandwidth_rest"] = ((strstr($p_members[$l_obj_id]["bandwidth_rest"], ".")) ? round(
                    $p_members[$l_obj_id]["bandwidth_rest"],
                    2
                ) : $p_members[$l_obj_id]["bandwidth_rest"]);

                $p_consumption[$l_obj_id]["memory"]     = ((strstr($p_consumption[$l_obj_id]["memory"], ".")) ? round(
                    $p_consumption[$l_obj_id]["memory"],
                    2
                ) : $p_consumption[$l_obj_id]["memory"]);
                $p_consumption[$l_obj_id]["cpu"]        = ((strstr($p_consumption[$l_obj_id]["cpu"], ".")) ? round(
                    $p_consumption[$l_obj_id]["cpu"],
                    2
                ) : $p_consumption[$l_obj_id]["cpu"]);
                $p_consumption[$l_obj_id]["disc_space"] = ((strstr($p_consumption[$l_obj_id]["disc_space"], ".")) ? round(
                    $p_consumption[$l_obj_id]["disc_space"],
                    2
                ) : $p_consumption[$l_obj_id]["disc_space"]);
                $p_consumption[$l_obj_id]["bandwidth"]  = ((strstr($p_consumption[$l_obj_id]["bandwidth"], ".")) ? round(
                    $p_consumption[$l_obj_id]["bandwidth"],
                    2
                ) : $p_consumption[$l_obj_id]["bandwidth"]);

            }
        }
    } // function
} // class
?>