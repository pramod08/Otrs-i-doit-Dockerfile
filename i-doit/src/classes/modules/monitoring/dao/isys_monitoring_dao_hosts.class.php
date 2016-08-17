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
 * Monitoring DAO.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_monitoring_dao_hosts extends isys_component_dao
{
    /**
     * Method for retrieving the monitoring configuration.
     *
     * @param   integer $p_id
     * @param   string  $p_type
     * @param   boolean $p_only_active
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data($p_id = null, $p_type = null, $p_only_active = false)
    {
        $l_sql = 'SELECT * FROM isys_monitoring_hosts WHERE TRUE ';

        if ($p_id !== null)
        {
            if (is_array($p_id))
            {
                $l_sql .= ' AND isys_monitoring_hosts__id IN (' . implode(', ', array_map('intval', $p_id)) . ')';
            }
            else
            {
                $l_sql .= ' AND isys_monitoring_hosts__id = ' . $this->convert_sql_id($p_id);
            } // if
        } // if

        if ($p_type !== null)
        {
            $l_sql .= ' AND isys_monitoring_hosts__type = ' . $this->convert_sql_text($p_type);
        } // if

        if ($p_only_active)
        {
            $l_sql .= ' AND isys_monitoring_hosts__active = 1';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for creating/saving a monitoring host definition.
     *
     * @param   integer $p_id
     * @param   array   $p_values
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save($p_id, $p_values)
    {
        $l_data = [];

        if ($p_id === null || empty($p_id))
        {
            $l_sql = 'INSERT INTO isys_monitoring_hosts SET %s;';
        }
        else
        {
            $l_sql = 'UPDATE isys_monitoring_hosts SET %s WHERE isys_monitoring_hosts__id = ' . $this->convert_sql_id($p_id) . ';';
        } // if

        if (count($p_values) > 0)
        {
            foreach ($p_values as $l_key => $l_value)
            {
                $l_data[] = 'isys_monitoring_hosts__' . $l_key . ' = ' . $this->convert_sql_text($l_value);
            } // foreach

            $this->update(str_replace('%s', implode(', ', $l_data), $l_sql)) && $this->apply_update();
        } // if

        return $p_id ?: $this->get_last_insert_id();
    } // function

    /**
     * This method will remove all configurations, whose IDs are explicitly given as parameter.
     *
     * @param   mixed $p_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete($p_id)
    {
        if (!is_array($p_id))
        {
            $p_id = [$p_id];
        } // if

        return ($this->update(
                'DELETE FROM isys_monitoring_hosts WHERE isys_monitoring_hosts__id IN (' . implode(',', array_map('intval', $p_id)) . ');'
            ) && $this->apply_update());
    } // function

    /**
     * Method for retrieving the monitoring export configuration.
     *
     * @param   mixed $p_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_export_data($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_monitoring_export_config WHERE TRUE ';

        if ($p_id !== null)
        {
            if (is_array($p_id))
            {
                $l_sql .= ' AND isys_monitoring_export_config__id IN (' . implode(', ', array_map('intval', $p_id)) . ')';
            }
            else
            {
                $l_sql .= ' AND isys_monitoring_export_config__id = ' . $this->convert_sql_id($p_id);
            } // if
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for finding all children of a given (check_mk) configuration.
     *
     * @param   integer $p_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_child_configurations($p_id)
    {
        $l_return = [];

        $l_res = $this->retrieve(
            'SELECT * FROM isys_monitoring_export_config
			WHERE isys_monitoring_export_config__type = "check_mk"
			AND isys_monitoring_export_config__id != ' . $this->convert_sql_id($p_id) . ';'
        );

        while ($l_row = $l_res->get_row())
        {
            if (isys_format_json::is_json_array($l_row['isys_monitoring_export_config__options']))
            {
                $l_config = isys_format_json::decode($l_row['isys_monitoring_export_config__options']);

                if ($l_config['master'] == $p_id)
                {
                    $l_return[] = $l_row;
                } // if
            } // if
        } // while

        return $l_return;
    } // function

    /**
     * Method for creating/saving a monitoring export definition.
     *
     * @param   integer $p_id
     * @param   array   $p_values
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_export_config($p_id = null, array $p_values = [])
    {
        $l_data = [];

        if ($p_id === null || empty($p_id))
        {
            $l_sql = 'INSERT INTO isys_monitoring_export_config SET %s;';
        }
        else
        {
            $l_sql = 'UPDATE isys_monitoring_export_config SET %s WHERE isys_monitoring_export_config__id = ' . $this->convert_sql_id($p_id) . ';';
        } // if

        if (count($p_values) > 0)
        {
            foreach ($p_values as $l_key => $l_value)
            {
                $l_data[] = 'isys_monitoring_export_config__' . $l_key . ' = ' . $this->convert_sql_text($l_value);
            } // foreach

            $this->update(str_replace('%s', implode(', ', $l_data), $l_sql)) && $this->apply_update();
        } // if

        return $p_id ?: $this->get_last_insert_id();
    } // function

    /**
     * This method will remove all export configurations, whose IDs are explicitly given as parameter.
     *
     * @param   mixed $p_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete_export_config($p_id)
    {
        if (!is_array($p_id))
        {
            $p_id = [$p_id];
        } // if

        return ($this->update(
                'DELETE FROM isys_monitoring_export_config WHERE isys_monitoring_export_config__id IN (' . implode(',', array_map('intval', $p_id)) . ');'
            ) && $this->apply_update());
    } // function
} // class