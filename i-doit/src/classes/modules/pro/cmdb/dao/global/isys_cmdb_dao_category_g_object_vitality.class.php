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
 * DAO: global category for object vitalities
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_object_vitality extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'object_vitality';

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
        return true;
    } // function

    /**
     * Gets member resources and for the current object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_members_and_main_object($p_obj_id)
    {
        $l_quick_info   = new isys_ajax_handler_quick_info();
        $l_dao_resource = new isys_cmdb_dao_category_g_computing_resources($this->get_database_component());

        $l_apps_vms = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);

        $l_main_obj[$p_obj_id] = $this->get_object_resources($p_obj_id);

        while ($l_row = $l_apps_vms->get_row())
        {
            if ($l_row["isys_obj_type__isys_obj_type_group__id"] == C__OBJTYPE_GROUP__SOFTWARE)
            {
                // Objects from group software.
                $l_resources_res = $l_dao_resource->get_data(null, $l_row["isys_obj__id"], "", null, C__RECORD_STATUS__NORMAL);

                $l_row2 = $l_resources_res->get_row();

                if ($l_resources_res->num_rows() > 0)
                {
                    if (strlen($l_row2["isys_obj__title"]) > 17)
                    {
                        $l_obj_title = substr($l_row2["isys_obj__title"], 0, 14);
                        $l_obj_title .= "...";
                    }
                    else
                    {
                        $l_obj_title = $l_row2["isys_obj__title"];
                    } // if

                    $l_member[$l_row2["isys_obj__id"]]["link"] = $l_quick_info->get_quick_info($l_row2["isys_obj__id"], $l_obj_title, C__LINK__OBJECT);

                    $l_member[$l_row2["isys_obj__id"]]["type"] = _L($this->get_objtype_name_by_id_as_string($this->get_objTypeID($l_row2["isys_obj__id"])));

                    $l_member[$l_row["isys_obj__id"]]["memory"]      = round(
                        isys_convert::memory($l_row2["isys_catg_computing_resources_list__ram"], "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD),
                        2
                    );
                    $l_member[$l_row["isys_obj__id"]]["memory_rest"] = $l_member[$l_row["isys_obj__id"]]["memory"];
                    $l_member[$l_row["isys_obj__id"]]["memory_unit"] = "MB";

                    $l_member[$l_row["isys_obj__id"]]["cpu"]      = round(
                        isys_convert::frequency($l_row2["isys_catg_computing_resources_list__cpu"], "C__FREQUENCY_UNIT__GHZ", C__CONVERT_DIRECTION__BACKWARD),
                        2
                    );
                    $l_member[$l_row["isys_obj__id"]]["cpu_rest"] = $l_member[$l_row["isys_obj__id"]]["cpu"];
                    $l_member[$l_row["isys_obj__id"]]["cpu_unit"] = "GHz";

                    $l_member[$l_row["isys_obj__id"]]["bandwidth"]      = round(
                        isys_convert::speed($l_row2["isys_catg_computing_resources_list__network_bandwidth"], "C__PORT_SPEED__MBIT_S", C__CONVERT_DIRECTION__BACKWARD),
                        2
                    );
                    $l_member[$l_row["isys_obj__id"]]["bandwidth_rest"] = $l_member[$l_row["isys_obj__id"]]["bandwidth"];
                    $l_member[$l_row["isys_obj__id"]]["bandwidth_unit"] = $this->retrieve(
                        "SELECT isys_port_speed__title FROM isys_port_speed WHERE isys_port_speed__const LIKE 'C__PORT_SPEED__MBIT_S'"
                    )
                        ->get_row_value('isys_port_speed__title');

                    $l_member[$l_row["isys_obj__id"]]["disc_space"]      = round(
                        isys_convert::memory($l_row2["isys_catg_computing_resources_list__disc_space"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD),
                        2
                    );
                    $l_member[$l_row["isys_obj__id"]]["disc_space_rest"] = $l_member[$l_row["isys_obj__id"]]["disc_space"];
                    $l_member[$l_row["isys_obj__id"]]["disc_space_unit"] = "GB";
                } // if
            }
            else
            {
                /**
                 * Virtual machines
                 */
                $l_member[$l_row["isys_obj__id"]] = $this->get_object_resources($l_row["isys_obj__id"]);
            } // if
        } // while

        $this->calculate_consumption_and_resources($l_member, $l_main_obj);

        $l_arr = [
            "members"  => $l_member,
            "main_obj" => $l_main_obj,
        ];

        return $l_arr;
    } // function

    /**
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        return count($this->get_data(null, ($p_obj_id ?: $this->m_object_id)));
    } // function

    /**
     * Return Category Data.
     *
     * @param  integer $p_catg_list_id
     * @param  integer $p_obj_id
     * @param  string  $p_condition
     * @param  mixed   $p_filter
     * @param  integer $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT appobj.*, isys_obj_type.*
			FROM isys_catg_application_list AS app
			INNER JOIN isys_connection ON app.isys_catg_application_list__isys_connection__id = isys_connection__id
			INNER JOIN isys_obj AS appobj ON appobj.isys_obj__id = isys_connection__isys_obj__id
			INNER JOIN isys_obj_type ON appobj.isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= ' AND app.isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND appobj.isys_obj__status = ' . $this->convert_sql_int($p_status) . '
				AND app.isys_catg_application_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        $l_sql .= " UNION SELECT vmobj.*, isys_obj_type.* FROM isys_catg_virtual_machine_list AS vm
			INNER JOIN isys_connection ON isys_connection__id = vm.isys_catg_virtual_machine_list__isys_connection__id
			INNER JOIN isys_obj AS vmobj ON vm.isys_catg_virtual_machine_list__isys_obj__id = vmobj.isys_obj__id
			INNER JOIN isys_obj_type ON vmobj.isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE ";

        if ($p_obj_id !== null)
        {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND vmobj.isys_obj__status = ' . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Gets resources from the referrenced object id
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    private function get_object_resources($p_obj_id)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_dao_memory = new isys_cmdb_dao_category_g_memory($this->get_database_component());
        $l_dao_cpu    = new isys_cmdb_dao_category_g_cpu($this->get_database_component());
        $l_dao_port   = new isys_cmdb_dao_category_g_network_port($this->get_database_component());
        $l_dao_drive  = new isys_cmdb_dao_category_g_drive($this->get_database_component());

        $l_memory_res = $l_dao_memory->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);
        $l_cpu_res    = $l_dao_cpu->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);

        $l_memory     = 0;
        $l_disc_space = 0;
        $l_cpu        = 0;

        $l_obj_title = $this->get_obj_name_by_id_as_string($p_obj_id);

        if (strlen($l_obj_title) >= 20)
        {
            $l_obj_title = substr($l_obj_title, 0, 20);
            $l_obj_title .= "...";
        } // if

        $l_member["link"] = $l_quick_info->get_quick_info($p_obj_id, $l_obj_title, C__LINK__OBJECT);

        if ($p_obj_id == $_GET[C__CMDB__GET__OBJECT])
        {
            $l_member["type"] = _L($this->get_objtype_name_by_id_as_string($this->get_objTypeID($p_obj_id)));
        }
        else
        {
            $l_member["type"] = _L("LC__CMDB__CATG__VIRTUAL_MACHINE");
        } // if

        /**
         * MEMORY
         */
        while ($l_memory_row = $l_memory_res->get_row())
        {
            // @todo  Check if "round()" does work correctly... Because some of the convert methods use "number_format()".
            $l_memory += round(isys_convert::memory($l_memory_row["isys_catg_memory_list__capacity"], "C__MEMORY_UNIT__MB", C__CONVERT_DIRECTION__BACKWARD), 2);
        } // while

        $l_member["memory"]      = $l_memory + 0;
        $l_member["memory_rest"] = $l_memory + 0;
        $l_member["memory_unit"] = "MB";

        /**
         * CPU
         */
        while ($l_cpu_row = $l_cpu_res->get_row())
        {
            // @todo  Check if "round()" does work correctly... Because some of the convert methods use "number_format()".
            $l_cpu += round(isys_convert::frequency($l_cpu_row["isys_catg_cpu_list__frequency"], C__FREQUENCY_UNIT__GHZ, C__CONVERT_DIRECTION__BACKWARD), 2);
        } // while

        $l_member["cpu"]      = $l_cpu + 0;
        $l_member["cpu_rest"] = $l_cpu + 0;
        $l_member["cpu_unit"] = "GHz";

        /**
         * BANDWIDTH
         */
        $l_max_speed = round($l_dao_port->get_max_speed($p_obj_id, "C__PORT_SPEED__MBIT_S"), 2);

        $l_member["bandwidth"]      = $l_max_speed + 0;
        $l_member["bandwidth_rest"] = $l_max_speed + 0;
        $l_member["bandwidth_unit"] = $this->retrieve('SELECT isys_port_speed__title FROM isys_port_speed WHERE isys_port_speed__const = "C__PORT_SPEED__MBIT_S";')
            ->get_row_value('isys_port_speed__title');

        /**
         * DRIVES
         */
        $l_system_drive_res = $l_dao_drive->get_system_drives($p_obj_id);

        while ($l_system_drive_row = $l_system_drive_res->get_row())
        {
            // @todo  Check if "round()" does work correctly... Because some of the convert methods use "number_format()".
            $l_disc_space += round(isys_convert::memory($l_system_drive_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD), 2);
        } // while

        $l_member["disc_space"]      = $l_disc_space + 0;
        $l_member["disc_space_rest"] = $l_disc_space + 0;
        $l_member["disc_space_unit"] = "GB";

        return $l_member;
    } // function

    /**
     * Recalculates the over consumption and determines the overall resources for every member.
     *
     * @param  array &$p_members
     * @param  array &$p_members
     */
    private function calculate_consumption_and_resources(&$p_members, &$p_main_obj)
    {
        foreach ($p_main_obj as $l_main_obj_id => $l_main_data)
        {
            if (!is_array($p_members))
            {
                $p_main_obj[$l_main_obj_id]["memory_consumption"]     = 0;
                $p_main_obj[$l_main_obj_id]["cpu_consumption"]        = 0;
                $p_main_obj[$l_main_obj_id]["bandwidth_consumption"]  = 0;
                $p_main_obj[$l_main_obj_id]["disc_space_consumption"] = 0;

                return;
            }
            else
            {
                foreach ($p_members as $l_obj_id => $l_data)
                {
                    $p_main_obj[$l_main_obj_id]["memory_rest"]        = $p_main_obj[$l_main_obj_id]["memory_rest"] - $l_data["memory"];
                    $p_main_obj[$l_main_obj_id]["memory_consumption"] = $p_main_obj[$l_main_obj_id]["memory_consumption"] + $l_data["memory"] + 0;

                    $p_main_obj[$l_main_obj_id]["cpu_rest"]        = $p_main_obj[$l_main_obj_id]["cpu_rest"] - $l_data["cpu"];
                    $p_main_obj[$l_main_obj_id]["cpu_consumption"] = $p_main_obj[$l_main_obj_id]["cpu_consumption"] + $l_data["cpu"] + 0;

                    $p_main_obj[$l_main_obj_id]["bandwidth_rest"]        = $p_main_obj[$l_main_obj_id]["bandwidth_rest"] - $l_data["bandwidth"];
                    $p_main_obj[$l_main_obj_id]["bandwidth_consumption"] = $p_main_obj[$l_main_obj_id]["bandwidth_consumption"] + $l_data["bandwidth"] + 0;

                    $p_main_obj[$l_main_obj_id]["disc_space_rest"]        = $p_main_obj[$l_main_obj_id]["disc_space_rest"] - $l_data["disc_space"];
                    $p_main_obj[$l_main_obj_id]["disc_space_consumption"] = $p_main_obj[$l_main_obj_id]["disc_space_consumption"] + $l_data["disc_space"] + 0;
                } // foreach
            } // if
        } // foreach
    } // function
} // class