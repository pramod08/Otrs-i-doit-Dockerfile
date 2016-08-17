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
class isys_workflow_dao_type extends isys_workflow_dao
{

    /**
     * @desc returns true, if the given workflow type is circular.
     *            otherwise - false.
     *
     * @param int $p_workflow_type__i
     *
     * @return boolean
     */
    public function is_circular($p_workflow_type__id)
    {

        $l_sql = "SELECT isys_workflow_type__occurrence FROM isys_workflow_type WHERE " . "(isys_workflow_type__id = '" . $p_workflow_type__id . "')";

        $l_res = $this->retrieve($l_sql);
        $l_db  = $l_res->get_row();
        if ($l_db["isys_workflow_type__occurrence"] > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * @desc get workflow types
     *
     * @return isys_component_dao_result
     */
    public function get_workflow_types($l_type_id = null)
    {
        $l_sql = "SELECT * FROM isys_workflow_type WHERE TRUE";

        if (!is_null($l_type_id))
        {
            $l_sql .= " AND (isys_workflow_type__id = '" . $l_type_id . "')";
        }
        else
        {
            $l_sql .= " AND isys_workflow_type__status = '" . C__RECORD_STATUS__NORMAL . "'";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * @desc return the title of a workflow type
     *
     * @param int $p_id
     *
     * @return string
     */
    public function get_title_by_id($p_id)
    {
        $l_data = $this->get_workflow_types($p_id);
        $l_row  = $l_data->get_row();

        return $l_row["isys_workflow_type__title"];
    }

    /**
     * @desc change the status of a workflow type
     *
     * @param int $p_id
     * @param int $p_type
     *
     * @return boolean
     */
    public function set_status($p_id, $p_type)
    {

        if ($p_type == C__RECORD_STATUS__PURGE)
        {
            $this->remove_parameters($p_id);
            $l_sql = "DELETE FROM isys_workflow_type WHERE isys_workflow_type__id = " . $this->convert_sql_id($p_id);
        }
        else
        {
            $l_sql = "UPDATE isys_workflow_type SET " . "isys_workflow_type__status = '" . $p_type . "' " . "WHERE " . "(isys_workflow_type__id = " . $this->convert_sql_id(
                    $p_id
                ) . ");";
        }
        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc get workflow action types
     *
     * @return isys_component_dao_result
     */
    public function get_action_types($l_type_id = null)
    {
        $l_sql = "SELECT * FROM isys_workflow_action_type WHERE TRUE";

        if (!is_null($l_type_id))
        {
            $l_sql .= " AND (isys_workflow_action_type__id = '" . $l_type_id . "')";
        }
        $l_sql .= ";";

        return $this->retrieve($l_sql);
    }

    /**
     * @desc creates a workflow type (Task,..)
     *
     * @param string $p_title
     * @param string $p_const
     *
     * @return int
     */
    public function create_workflow_type($p_title, $p_const, $p_occurrence)
    {
        $l_sql = "INSERT INTO isys_workflow_type SET " . "isys_workflow_type__title = " . $this->convert_sql_text(
                $p_title
            ) . "," . "isys_workflow_type__const = " . $this->convert_sql_text(
                $p_const
            ) . "," . "isys_workflow_type__occurrence = '" . $p_occurrence . "'," . "isys_workflow_type__datetime = " . $this->convert_sql_datetime(
                time()
            ) . "," . "isys_workflow_type__status = " . C__RECORD_STATUS__NORMAL;
        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                $l_type_id = $this->get_last_insert_id();

                $l_title = preg_replace("/\w\w+\s\s+/", "_", $p_title);

                $l_dao_params = new isys_workflow_dao_template($this->m_db);
                $l_dao_params->create_template_parameter(
                    'LC__TASK__DETAIL__WORKORDER__START_DATE',
                    $l_type_id,
                    C__WF__PARAMETER_TYPE__DATETIME,
                    strtolower(str_replace(" ", "", $l_title)) . "__start_date",
                    0,
                    1
                );

                $l_dao_params->create_template_parameter(
                    'LC__TASK__DETAIL__WORKORDER__END_DATE',
                    $l_type_id,
                    C__WF__PARAMETER_TYPE__DATETIME,
                    strtolower(str_replace(" ", "", $l_title)) . "__end_date",
                    0,
                    1
                );

                return $l_type_id;
            }
        }

        return false;
    }

    /**
     * @desc saves a workflow type
     *
     * @param string $p_title
     * @param string $p_const
     *
     * @return int
     */
    public function save_workflow_type($p_id, $p_title, $p_const, $p_occurrence)
    {
        $l_sql = "UPDATE isys_workflow_type SET " . "isys_workflow_type__title = " . $this->convert_sql_text(
                $p_title
            ) . "," . "isys_workflow_type__const = " . $this->convert_sql_text(
                $p_const
            ) . ", " . "isys_workflow_type__occurrence = '" . $p_occurrence . "' " . "WHERE " . "(isys_workflow_type__id = '" . $p_id . "');";
        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc creates a workflow action type (New,Assign,Accept,..)
     *
     * @param string $p_title
     * @param string $p_const
     *
     * @return int
     */
    public function create_action_type($p_title, $p_const, $p_class)
    {
        $l_sql = "INSERT INTO isys_workflow_action_type SET " . "isys_workflow_action_type__title = " . $this->convert_sql_text(
                $p_title
            ) . "," . "isys_workflow_action_type__const = " . $this->convert_sql_text($p_const) . "," . "isys_workflow_action_type__class = " . $this->convert_sql_text(
                $p_class
            ) . "," . "isys_workflow_action_type__datetime = " . $this->convert_sql_datetime(time()) . "," . "isys_workflow_action_type__status = " . C__RECORD_STATUS__NORMAL;
        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * @desc creates a workflow action type (New,Assign,Accept,..)
     *
     * @param string $p_title
     * @param string $p_const
     *
     * @return int
     */
    public function save_action_type($p_id, $p_title, $p_const, $p_class)
    {
        $l_sql = "INSERT INTO isys_workflow_action_type SET " . "isys_workflow_action_type__title = " . $this->convert_sql_text(
                $p_title
            ) . "," . "isys_workflow_action_type__const = " . $this->convert_sql_text($p_const) . "," . "isys_workflow_action_type__class = " . $this->convert_sql_text(
                $p_class
            ) . " " . "WHERE " . "(isys_workflow_action_type__id = '" . $p_id . "');";
        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Removes references template parameters from workflow type
     *
     * @param $p_id
     *
     * @return bool
     */
    public function remove_parameters($p_id)
    {
        $l_sql = "DELETE FROM isys_wf_type_2_wf_tp WHERE isys_wf_type_2_wf_tp__isys_workflow_type__id = " . $this->convert_sql_id($p_id);
        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    public function __construct(isys_component_database &$p_database)
    {
        parent::__construct($p_database);
    }
}

?>