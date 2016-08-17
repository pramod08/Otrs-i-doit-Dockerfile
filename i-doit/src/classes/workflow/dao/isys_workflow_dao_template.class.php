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
 *
 * @package    i-doit
 * @subpackage Workflow
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_workflow_dao_template extends isys_workflow_dao
{

    /**
     * @desc get template parameters (for specific workflow type)
     *
     * @param int $p_workflow_type__id
     *
     * @return isys_component_dao_result
     */
    public function get_templates($p_workflow_type__id = null, $p_template_parameter__id = null, $p_template_parameter__key = null)
    {
        $l_sql = "SELECT * FROM isys_workflow_template_parameter " . "LEFT OUTER JOIN isys_wf_type_2_wf_tp " . "ON " . "isys_wf_type_2_wf_tp__isys_workflow_template_parameter__id = " . "isys_workflow_template_parameter__id " . "LEFT OUTER JOIN isys_workflow_type " . "ON " . "isys_wf_type_2_wf_tp__isys_workflow_type__id = " . "isys_workflow_type__id " . "WHERE TRUE";

        if (!is_null($p_workflow_type__id))
        {
            $l_sql .= " AND (isys_workflow_type__id = '" . $p_workflow_type__id . "')";
        }
        if (!is_null($p_template_parameter__id))
        {
            $l_sql .= " AND (isys_workflow_template_parameter__id = '" . $p_template_parameter__id . "')";
        }
        if (!is_null($p_template_parameter__key))
        {
            $l_sql .= " AND (isys_workflow_template_parameter__key = '" . $p_template_parameter__key . "')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * @desc return the workflow type by a parameter id
     *
     * @param int $p_id
     *
     * @return int
     */
    public function get_workflow_type_by_parameter_id($p_id)
    {
        $l_templates = $this->get_templates(null, $p_id)
            ->get_row();

        return $l_templates["isys_wf_type_2_wf_tp__isys_workflow_type__id"];
    }

    /**
     * @desc bind a template parameter to a workflow type
     *
     * @param int $p_workflow_type__id
     * @param int $p_template_parameter__id
     *
     * @return boolean
     */
    public function bind($p_workflow_type__id, $p_template_parameter__id)
    {
        $l_sql   = "SELECT * FROM isys_wf_type_2_wf_tp WHERE " . "(isys_wf_type_2_wf_tp__isys_workflow_template_parameter__id = '" . $p_template_parameter__id . "');";
        $l_dao   = $this->retrieve($l_sql);
        $l_where = '';
        if ($l_dao->num_rows() <= 0)
        {
            $l_sql = "INSERT INTO isys_wf_type_2_wf_tp SET ";
        }
        else
        {
            $l_sql   = "UPDATE isys_wf_type_2_wf_tp SET ";
            $l_where = "WHERE " . "(isys_wf_type_2_wf_tp__isys_workflow_template_parameter__id = " . $this->convert_sql_id($p_template_parameter__id) . ");";
        }

        $l_sql .= "isys_wf_type_2_wf_tp__isys_workflow_type__id = " . $this->convert_sql_id(
                $p_workflow_type__id
            ) . ", " . "isys_wf_type_2_wf_tp__isys_workflow_template_parameter__id = " . $this->convert_sql_id($p_template_parameter__id) . " ";

        $l_sql .= $l_where . ";";

        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc unbind the connection
     *
     * @param int $p_workflow_type__id
     * @param int $p_template_parameter__id
     *
     * @return boolean
     */
    public function unbind($p_workflow_type__id, $p_template_parameter__id)
    {
        $l_sql = "DELETE FROM isys_wf_type_2_wf_tp WHERE " . "(isys_wf_type_2_wf_tp__isys_workflow_type__id = " . $this->convert_sql_id(
                $p_workflow_type__id
            ) . " AND " . "isys_wf_type_2_wf_tp__isys_workflow_template_parameter__id = " . $this->convert_sql_id($p_template_parameter__id) . ");";

        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc delete template parameter and unbind it
     *
     * @param int $p_id
     */
    public function delete_template_parameter($p_id, $p_force = false)
    {

        /*get workflow type*/
        $l_param   = $this->get_template_parameter_by_id($p_id);
        $l_row     = $l_param->get_row();
        $l_wf_type = $l_row["isys_workflow_type__id"];

        if ($l_wf_type > 0)
        {
            if (preg_match("/^.*?(start_date|end_date).*?$/i", $l_row["isys_workflow_template_parameter__key"]) && !$p_force)
            {
                return -1;
            }
            /*unbind*/
            $this->unbind($l_wf_type, $p_id);
        }

        /*delete the template*/
        $l_sql = "DELETE FROM isys_workflow_template_parameter WHERE " . "(isys_workflow_template_parameter__id = " . $this->convert_sql_id($p_id) . ");";
        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc creates a template parameter
     *
     * @param string $p_title
     * @param int    $p_workflow_type__id
     * @param int    $p_type
     * @param string $p_key
     * @param int    $p_sort
     *
     * @return int
     */
    public function create_template_parameter($p_title, $p_workflow_type__id, $p_type, $p_key, $p_sort = 0, $p_check = 0)
    {

        $l_sql = "INSERT INTO isys_workflow_template_parameter SET " . "isys_workflow_template_parameter__type = '" . $p_type . "', " . "isys_workflow_template_parameter__title = '" . $this->m_db->escape_string(
                $p_title
            ) . "', " . "isys_workflow_template_parameter__key = '" . $this->m_db->escape_string($p_key) . "', " . "isys_workflow_template_parameter__sort =  '" . intval(
                $p_sort
            ) . "', " . "isys_workflow_template_parameter__property =  '" . intval(
                $p_check
            ) . "', " . "isys_workflow_template_parameter__status = " . C__RECORD_STATUS__NORMAL . ";";

        if ($this->update($l_sql))
        {
            $l_update = $this->apply_update();

            if ($l_update)
            {
                $l_id = $this->get_last_insert_id();

                if ($p_workflow_type__id > 0)
                {
                    $this->bind($p_workflow_type__id, $l_id);
                }
            }

            return $l_update;
        }

        return false;
    }

    /**
     * @desc creates a template parameter
     *
     * @param string $p_title
     * @param int    $p_workflow_type__id
     * @param int    $p_type
     * @param string $p_key
     * @param int    $p_sort
     *
     * @return int
     */
    public function save_template_parameter($p_id, $p_title, $p_workflow_type__id, $p_type, $p_key, $p_sort, $p_check = 0)
    {

        $l_sql = "UPDATE isys_workflow_template_parameter SET " . "isys_workflow_template_parameter__type = '" . $p_type . "', " . "isys_workflow_template_parameter__title = '" . $this->m_db->escape_string(
                $p_title
            ) . "', " . "isys_workflow_template_parameter__key = '" . $this->m_db->escape_string(
                $p_key
            ) . "', " . "isys_workflow_template_parameter__sort =  '" . $p_sort . "', " . "isys_workflow_template_parameter__property =  '" . $p_check . "' " . "WHERE " . "(isys_workflow_template_parameter__id = '" . $p_id . "');";

        if ($this->update($l_sql))
        {
            $l_update = $this->apply_update();

            if ($l_update)
            {
                $this->bind($p_workflow_type__id, $p_id);
            }

            return $l_update;
        }

        return false;
    }

    /**
     * @desc return template parameter data by id
     *
     * @param int $p_template_parameter__id
     *
     * @return isys_component_dao_result
     */
    public function get_template_parameter_by_id($p_template_parameter__id)
    {
        return $this->get_templates(null, $p_template_parameter__id);
    }

    /**
     * @desc return tempplate parameter data by key (isys_workflow_template_parameter__key)
     *
     * @param string $p_key
     *
     * @return isys_component_dao_result
     */
    public function get_template_by_key($p_key)
    {
        $l_sql = "SELECT * " . "FROM isys_workflow_template_parameter " . "WHERE (isys_workflow_template_parameter__key = '" . $p_key . "');";

        return $this->retrieve($l_sql);
    }

    /**
     * @desc get template_parameter by workflow type (isys_workflow_type__id) AS ARRAY
     *                "key" => X
     *                "value => X
     *                "type" => X
     *
     * @param int $l_workflow_type
     *
     * @return array
     */
    public function get_template_parameter($l_workflow_type)
    {
        /**
         * @desc get template parameter for the current workflow type
         */
        $l_templates          = $this->get_templates($l_workflow_type);
        $l_template_parameter = [];
        while ($l_row = $l_templates->get_row())
        {
            $l_template_parameter[$l_row["isys_workflow_template_parameter__id"]] = [
                "key"   => $l_row["isys_workflow_template_parameter__key"],
                "value" => _L($l_row["isys_workflow_template_parameter__title"]),
                "type"  => $l_row["isys_workflow_template_parameter__type"],
                "check" => "check=" . $l_row["isys_workflow_template_parameter__property"]
            ];
        }

        return $l_template_parameter;
    }

    public function __construct(isys_component_database &$p_database)
    {
        parent::__construct($p_database);
    }
}

?>