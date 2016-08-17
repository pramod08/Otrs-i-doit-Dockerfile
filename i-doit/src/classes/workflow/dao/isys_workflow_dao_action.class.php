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
class isys_workflow_dao_action extends isys_workflow_dao
{

    /**
     * @desc returns all actions, actions which happened in a workflow or a single action
     *            with reference to its workflow
     *
     * @param int $p_workflow_id
     * @param int $p_action_id
     *
     * @return isys_component_dao_result
     */
    public function get_actions($p_workflow_id = null, $p_action_id = null, $p_action_type = null, $p_user_id = null, $p_limit = null, $p_workflow_type = null, $p_filter = null, $p_group_by = null, $p_owner_mode = null, $p_date_from = null, $p_date_to = null, $p_order_by = null)
    {

        $l_sql = "SELECT *" . " FROM isys_workflow_action as action_new ";

        if (isset($p_action_type))
        {
            $l_sql .= "INNER JOIN isys_workflow_action as assign " . "ON " . "assign.isys_workflow_action__isys_workflow_action_type__id = " . "'" . C__WORKFLOW__ACTION__TYPE__ASSIGN . "' ";
        }

        $l_sql .= "INNER JOIN isys_workflow_2_isys_workflow_action " . "ON " . "isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . "action_new.isys_workflow_action__id " .

            "INNER JOIN isys_workflow " . "ON " . "isys_workflow__id = " . "isys_workflow_2_isys_workflow_action__isys_workflow__id " .

            "INNER JOIN isys_workflow_action_type " . "ON " . "isys_workflow_action_type__id = " . "action_new.isys_workflow_action__isys_workflow_action_type__id " .

            "INNER JOIN isys_workflow_type " . "ON " . "isys_workflow__isys_workflow_type__id = " . "isys_workflow_type__id ";

        if (isset($p_group_by))
        {
            $l_sql .= "LEFT OUTER JOIN isys_wf_type_2_wf_tp " . "ON " . "isys_wf_type_2_wf_tp__isys_workflow_type__id = isys_workflow_type__id " . "LEFT OUTER JOIN isys_workflow_action_parameter " . "ON " . "isys_workflow_action_parameter__isys_workflow_action__id = " . "action_new.isys_workflow_action__id ";
        }

        if (!empty($p_user_id))
        {
            $l_sql .= "INNER JOIN isys_contact " . "ON ";

            switch ($p_action_type)
            {
                case C__WORKFLOW__ACTION__TYPE__NEW:
                    $l_sql .= "isys_contact__id = isys_workflow__isys_contact__id ";
                    break;
                default:
                    $l_sql .= "isys_contact__id = action_new.isys_workflow_action__isys_contact__id ";
                    break;
            }

            $l_sql .= "INNER JOIN isys_contact_2_isys_obj " . "ON " . "isys_contact_2_isys_obj__isys_contact__id = isys_contact__id ";
            $l_sql .= "INNER JOIN isys_obj " . "ON " . "isys_contact_2_isys_obj__obj__id = " . "isys_obj__id ";
        }

        $l_sql .= "WHERE TRUE ";

        /* -------------------------------------------------------------------------- */
        if (!empty($p_workflow_id))
        {
            $l_sql .= " AND " . "(isys_workflow__id = '" . $p_workflow_id . "')";
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (!empty($p_action_id))
        {
            $l_sql .= " AND " . "(action_new.isys_workflow_action__id = '" . $p_action_id . "')";
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (!empty($p_action_type))
        {
            $l_sql .= " AND " . "(isys_workflow_action_type__id = '" . $p_action_type . "')";
        }
        /* -------------------------------------------------------------------------- */
        if (!empty($p_user_id))
        {
            $l_sql .= " AND " . "(isys_contact_2_isys_obj__isys_obj__id " . $this->get_database_component()
                    ->escape_string($p_owner_mode) . "= '" . $p_user_id . "')";
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (!empty($p_workflow_type))
        {
            $l_sql .= " AND " . "(isys_workflow__isys_workflow_type__id = '" . $p_workflow_type . "')";
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (!empty($p_date_from))
        {
            $l_sql .= " AND " . "((isys_workflow_action_parameter__key LIKE '%start_date%') " . " AND " . "(isys_workflow_action_parameter__datetime >= '" . $p_date_from . "'))";

        }
        /* -------------------------------------------------------------------------- */
        if (!empty($p_date_to))
        {
            $l_sql .= " AND " . "((isys_workflow_action_parameter__key LIKE '%end_date%') " . " AND " . "(isys_workflow_action_parameter__datetime <= '" . $p_date_from . "'))";

        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (isset($p_filter))
        {
            $l_sql .= " " . $p_filter;
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (isset($p_group_by))
        {
            $p_group_by = "isys_workflow__id";
            $l_sql .= " GROUP BY " . $p_group_by . " ";
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (isset($p_order_by))
        {
            if (!isset($p_order_type))
            {
                $p_order_type = "DESC";
            }

            $l_sql .= " ORDER BY " . $p_order_by . " " . $p_order_type;
        }
        else
        {
            $l_sql .= " ORDER BY isys_workflow_action_type__id ASC ";
        }
        /* -------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------- */
        if (!is_null($p_limit))
        {
            $l_sql .= " LIMIT " . $p_limit;
        }
        /* -------------------------------------------------------------------------- */
        $l_sql .= ";";

        /* -------------------------------------------------------------------------- */

        return $this->retrieve($l_sql);
    }

    /**
     * @desc get actions by specified person_intern__id
     * @desc (wrapper for get_actions)
     *
     * @param int $p_user__id
     */
    public function get_actions_by_user_id($p_user__id, $p_action_type = null, $p_limit = null, $p_workflow_type = null, $p_condition = null)
    {
        return $this->get_actions(
            null,
            null,
            $p_action_type,
            $p_user__id,
            $p_limit,
            $p_workflow_type,
            $p_condition,
            "isys_workflow__id"
        );
    }

    /**
     * @desc get action parameters for a given action_id or searches inside of them
     *
     * @param int    $p_action_id
     * @param string $p_search_string
     */
    public function get_action_parameters($p_action_id = null, $p_search_string = null, $p_key = null, $p_check = true)
    {
        $l_sql = "SELECT * FROM isys_workflow_action_parameter ";

        if ($p_check)
        {
            $l_sql .= "INNER JOIN isys_workflow_template_parameter " . "ON " . "isys_workflow_action_parameter__isys_wf_template_parameter__id = " . "isys_workflow_template_parameter__id ";
        }

        $l_sql .= "WHERE TRUE ";

        if (!empty($p_search_string))
        {
            $l_sql .= "AND (isys_workflow_action_parameter__string LIKE '%" . $p_search_string . "%') OR " . "(isys_workflow_action_parameter__int = '" . $p_search_string . "') ";
        }

        if (!empty($p_action_id))
        {
            $l_sql .= "AND (isys_workflow_action_parameter__isys_workflow_action__id = '" . $p_action_id . "') ";
        }
        if (!empty($p_key))
        {
            $l_sql .= "AND (isys_workflow_action_parameter__key LIKE '%" . $p_key . "%') ";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * @desc add a parameter
     *
     * @param int    $p_action
     * @param string $p_key
     * @param string $p_value
     *
     * @return int
     */
    public function add_parameter($p_action_id, $p_type, $p_key, $p_value, $p_template_parameter = null)
    {

        $l_sql = "INSERT INTO isys_workflow_action_parameter SET " . "isys_workflow_action_parameter__key = '" . $p_key . "'," . "isys_workflow_action_parameter__isys_wf_template_parameter__id = " . $this->convert_sql_id(
                $p_template_parameter
            ) . ", " . "isys_workflow_action_parameter__isys_workflow_action__id = " . $this->convert_sql_id($p_action_id) . ", " . "isys_workflow_action_parameter__";

        switch ($p_type)
        {
            case C__WF__PARAMETER_TYPE__INT:
            case C__WF__PARAMETER_TYPE__YES_NO:
                $l_sql .= "int";
                break;
            case C__WF__PARAMETER_TYPE__DATETIME:
                $l_sql .= "datetime";
                break;
            case C__WF__PARAMETER_TYPE__TEXT:
                $l_sql .= "text";
                $p_value = str_replace("\\r", "", $p_value); //we don't want windows formatting here
                break;
            default:
            case C__WF__PARAMETER_TYPE__STRING:
                $l_sql .= "string";
                break;
        }

        $l_sql .= " = '" . $this->m_db->escape_string($p_value) . "';";

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
     * @desc add a parameter
     *
     * @param int    $p_action
     * @param string $p_key
     * @param string $p_value
     *
     * @return int
     */
    public function save_parameter($p_param_id, $p_type, $p_key, $p_value, $p_template_parameter = null)
    {

        if (is_null($p_template_parameter))
        {
            $p_template_parameter = 0;
        }

        $l_sql = "UPDATE isys_workflow_action_parameter SET " . "isys_workflow_action_parameter__key = '" . $p_key . "'," . "isys_workflow_action_parameter__isys_wf_template_parameter__id = '" . $p_template_parameter . "', " . "isys_workflow_action_parameter__";

        switch ($p_type)
        {
            case C__WF__PARAMETER_TYPE__INT:
            case C__WF__PARAMETER_TYPE__YES_NO:
                $l_sql .= "int";
                break;
            case C__WF__PARAMETER_TYPE__DATETIME:
                $l_sql .= "datetime";
                break;
            case C__WF__PARAMETER_TYPE__TEXT:
                $l_sql .= "text";
                $p_value = str_replace("\\r", "", $p_value); //we don't want windows formatting here
                break;
            default:
            case C__WF__PARAMETER_TYPE__STRING:
                $l_sql .= "string";
                break;
        }

        $l_sql .= " = '" . $this->m_db->escape_string($p_value) . "' ";

        $l_sql .= "WHERE (isys_workflow_action_parameter__id = '" . $p_param_id . "');";

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
     * @desc binds an action to a workflow
     *
     * @param int $p_workflow_id
     * @param int $p_action_id
     *
     * @return boolean
     */
    public function bind($p_workflow_id, $p_action_id)
    {
        $l_sql = "INSERT INTO isys_workflow_2_isys_workflow_action SET " . "isys_workflow_2_isys_workflow_action__isys_workflow__id = " . $this->convert_sql_id(
                $p_workflow_id
            ) . ", " . "isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . $this->convert_sql_id($p_action_id) . ";";
        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc unbind an action from a workflow (used for deleting a workflow)
     *
     * @param int $p_workflow_id
     * @param int $p_action_id
     *
     * @return boolean
     */
    public function unbind($p_workflow_id, $p_action_id)
    {
        $l_sql = "DELETE FROM isys_workflow_2_isys_workflow_action " . "WHERE " . "(isys_workflow_2_isys_workflow_action__isys_workflow__id = " . $this->convert_sql_id(
                $p_workflow_id
            ) . " AND " . "isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . $this->convert_sql_id($p_action_id) . ");";
        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @desc create an action
     *
     * @param int $p_contact_id
     * @param int $p_workflow_action_type_id
     *
     * @return int
     */
    public function create_action($p_workflow_action_type_id, $p_contact_id = 0)
    {
        $l_sql = "INSERT INTO isys_workflow_action SET " . "isys_workflow_action__isys_contact__id = " . $this->convert_sql_id(
                $p_contact_id
            ) . "," . "isys_workflow_action__isys_workflow_action_type__id	= " . $this->convert_sql_id(
                $p_workflow_action_type_id
            ) . "," . "isys_workflow_action__datetime = " . $this->convert_sql_datetime(time()) . ";";

        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                return $this->get_last_insert_id();
            }
        }

        return null;
    }

    /**
     * @desc save an action
     *
     * @param int $p_action_id
     * @param int $p_contact_id
     * @param int $p_workflow_action_type_id
     *
     * @return boolean
     */
    public function save_action($p_action_id, $p_contact_id, $p_workflow_action_type_id = ISYS_NULL)
    {
        $l_sql = "UPDATE isys_workflow_action SET " . "isys_workflow_action__isys_contact__id = " . $this->convert_sql_id($p_contact_id) . "";

        if ($p_workflow_action_type_id)
        {
            $l_sql .= ", isys_workflow_action__isys_workflow_action_type__id	= " . $this->convert_sql_id($p_workflow_action_type_id) . " ";
        }

        $l_sql .= "WHERE " . "(isys_workflow_action__id = " . $this->convert_sql_id($p_action_id) . ");";

        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }

        return false;
    }

    /* ---------------------------------------------------------------------------- */
    /**
     * @desc KILLS ALL ACTIONS, ACTION PARAMETERS AND THE WORKFLOW ITSELF !
     */
    /* ---------------------------------------------------------------------------- */
    public function kill($p_workflow__id)
    {
        $l_id = $p_workflow__id;

        $l_workflow_data = $this->get_actions($l_id);
        while ($l_row = $l_workflow_data->get_row())
        {
            $l_action_id = $l_row["isys_workflow_action__id"];

            $l_sql = "DELETE FROM isys_workflow_2_isys_workflow_action " . "WHERE " . "(isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . $this->convert_sql_id(
                    $l_action_id
                ) . ");";
            $this->update($l_sql);

            $l_sql = "DELETE FROM isys_workflow_action_parameter " . "WHERE " . "(isys_workflow_action_parameter__isys_workflow_action__id = " . $this->convert_sql_id(
                    $l_action_id
                ) . ");";
            $this->update($l_sql);

            $this->unbind($l_id, $l_action_id);

            $l_sql = "DELETE FROM isys_workflow_action " . "WHERE " . "(isys_workflow_action__id = " . $this->convert_sql_id($l_action_id) . ");";
            $this->update($l_sql);

            $this->apply_update();
        }

        $this->delete($l_id);

        return true;

    }
    /* ---------------------------------------------------------------------------- */
    /* ---------------------------------------------------------------------------- */

    public function __construct(isys_component_database &$p_database)
    {
        parent::__construct($p_database);
    }
}

?>