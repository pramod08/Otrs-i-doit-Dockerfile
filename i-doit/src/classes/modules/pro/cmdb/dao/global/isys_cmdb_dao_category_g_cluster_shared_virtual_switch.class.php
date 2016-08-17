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
class isys_cmdb_dao_category_g_cluster_shared_virtual_switch extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'cluster_shared_virtual_switch';

    public function get_all_virtual_switches()
    {
        $l_sql = "SELECT * FROM isys_virtual_port_group " . " INNER JOIN isys_catg_virtual_switch_list ON isys_catg_virtual_switch_list__id = isys_virtual_port_group__isys_catg_virtual_switch_list__id " . " WHERE isys_catg_virtual_switch_list__status = " . C__RECORD_STATUS__NORMAL . " " . " GROUP BY isys_catg_virtual_switch_list__title, isys_virtual_port_group__title ORDER BY isys_catg_virtual_switch_list__title DESC";

        return $this->retrieve($l_sql);
    }

    public function get_port_groups($p_virtual_switch_id)
    {

        $l_sql = "SELECT * FROM isys_virtual_port_group " . " WHERE isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id(
                $p_virtual_switch_id
            );

        return $this->retrieve($l_sql);
    }

    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $p_intOldRecStatus = $l_catdata["isys_catg_virtual_switch_list__status"];

        $l_dao = new isys_cmdb_dao_category_g_virtual_switch($this->m_db);

        if (is_array($_POST["port_group_hidden"]))
        {
            foreach ($_POST["port_group_hidden"] as $l_virtual_switch_id => $l_objects)
            {

                if (is_array($l_objects))
                {
                    foreach ($l_objects as $l_obj_id => $l_port_group)
                    {

                        foreach ($l_port_group AS $l_port_group_id => $l_state)
                        {
                            $l_create_new = false;

                            if ($l_state == "1")
                            {
                                $l_row = $this->get_virtual_switch_by_string($l_virtual_switch_id);

                                $l_sql = "SELECT * FROM isys_catg_virtual_switch_list WHERE isys_catg_virtual_switch_list__isys_obj__id = " . $this->convert_sql_id(
                                        $l_obj_id
                                    ) . " AND isys_catg_virtual_switch_list__title = " . $this->convert_sql_text($l_row["isys_catg_virtual_switch_list__title"]);

                                $l_res = $this->retrieve($l_sql);

                                if ($l_res->num_rows() == 0)
                                {
                                    // INSERT NEW VIRTUAL SWITCH
                                    $l_vswitch_id = $l_dao->create($l_obj_id, C__RECORD_STATUS__NORMAL, $l_row["isys_catg_virtual_switch_list__title"], null);
                                }
                                else
                                {
                                    $l_row        = $l_res->get_row();
                                    $l_vswitch_id = $l_row["isys_catg_virtual_switch_list__id"];
                                }

                                $l_port_row = $this->get_port_group_by_string($l_port_group_id);

                                $l_sql = "SELECT * FROM isys_virtual_port_group WHERE isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id(
                                        $l_vswitch_id
                                    ) . " AND isys_virtual_port_group__title = " . $this->convert_sql_text($l_port_row["isys_virtual_port_group__title"]);

                                $l_port_res = $this->retrieve($l_sql);

                                if ($l_port_res->num_rows() == 0)
                                {
                                    // ADD PORT GROUP TO VIRTUAL SWITCH
                                    $l_dao->add_port_group($l_vswitch_id, $l_port_row["isys_virtual_port_group__title"], $l_port_row["isys_virtual_port_group__vlanid"]);
                                }
                            }
                            elseif ($l_state == "0")
                            {

                                $l_row = $this->get_virtual_switch_by_string($l_virtual_switch_id);

                                $l_sql = "SELECT * FROM isys_catg_virtual_switch_list WHERE isys_catg_virtual_switch_list__isys_obj__id = " . $this->convert_sql_id(
                                        $l_obj_id
                                    ) . " AND isys_catg_virtual_switch_list__title = " . $this->convert_sql_text($l_row["isys_catg_virtual_switch_list__title"]);

                                $l_res = $this->retrieve($l_sql);

                                if ($l_res->num_rows() > 0)
                                {
                                    $l_row        = $l_res->get_row();
                                    $l_vswitch_id = $l_row["isys_catg_virtual_switch_list__id"];

                                    $l_port_row = $this->get_port_group_by_string($l_port_group_id);

                                    $l_sql = "SELECT * FROM isys_virtual_port_group WHERE isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id(
                                            $l_vswitch_id
                                        ) . " AND isys_virtual_port_group__title = " . $this->convert_sql_text($l_port_row["isys_virtual_port_group__title"]);

                                    $l_port_res = $this->retrieve($l_sql);

                                    if ($l_port_res->num_rows() > 0)
                                    {
                                        // PREPARE ARRAY FOR DELETING
                                        $l_port_row     = $l_port_res->get_row();
                                        $l_remove_arr[] = $l_port_row["isys_virtual_port_group__id"];
                                    }
                                }
                            }
                        }
                    }
                }

            }

            if (is_array($l_remove_arr) && count($l_remove_arr) > 0)
            {
                foreach ($l_remove_arr AS $l_port_group_id)
                {
                    // DELETE ASSIGNED PORT GROUPS
                    $this->remove_port_group($l_port_group_id);
                }
            }

        }

        return null;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param boolean   $p_virtualHost
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_cat_level, $p_virtualHost, $p_connectedObjID, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        return null;
    }

    /**
     * Get count method.
     *
     * @param   integer $p_objID
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        return 1;
    }

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

        $l_sql = "SELECT * FROM isys_catg_cluster_members_list " .

            "INNER JOIN isys_connection " . "ON " . "isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " . "INNER JOIN isys_obj " . "ON " . "isys_obj__id = isys_connection__isys_obj__id " . "LEFT JOIN isys_catg_virtual_switch_list " . "ON " . "isys_catg_virtual_switch_list__isys_obj__id = isys_obj__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id))
        {
            $l_sql .= " AND (isys_catg_cluster_members_list__id = '{$p_catg_list_id}') ";
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND (isys_obj__status = '{$p_status}') AND (isys_catg_cluster_members_list__status = '{$p_status}') ";
        }

        $l_sql .= "ORDER BY isys_obj__title, isys_catg_virtual_switch_list__title";

        return $this->retrieve($l_sql);
    }

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
                $l_sql = ' AND (isys_catg_cluster_members_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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

    private function get_port_group_by_string($p_trim_port_group_title)
    {

        $l_sql = "SELECT * FROM isys_virtual_port_group ";

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {
            if (str_replace(" ", "", $l_row["isys_virtual_port_group__title"]) == $p_trim_port_group_title)
            {
                return $l_row;
            }
        }

        return false;
    } // function

    private function get_virtual_switch_by_string($p_trim_vswitch_title)
    {
        $l_sql = "SELECT * FROM isys_catg_virtual_switch_list ";

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {
            if (str_replace(" ", "", $l_row["isys_catg_virtual_switch_list__title"]) == $p_trim_vswitch_title)
            {
                return $l_row;
            }
        }
    } // function

    private function remove_port_group($p_catlevel = null, $p_vswitch_id = null)
    {
        $l_delete = "DELETE FROM isys_virtual_port_group WHERE ";

        $l_addAnd = false;

        if (!empty($p_catlevel))
        {
            $l_delete .= " isys_virtual_port_group__id = " . $this->convert_sql_id($p_catlevel);
            $l_addAnd = true;
        }

        if (!empty($p_vswitch_id))
        {
            if ($l_addAnd)
            {
                $l_delete .= " AND isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitch_id);
            }
            else
            {
                $l_delete .= " isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitch_id);
            }
        }

        if ($this->update($l_delete) && $this->apply_update())
        {
            return true;
        }
        else
        {
            return false;
        }
    } // function
} // class
?>