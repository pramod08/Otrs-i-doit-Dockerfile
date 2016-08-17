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
 * Connection DAO
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Dennis Stuecken <dstuecken@synetics.de
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_connection extends isys_cmdb_dao
{
    /**
     * Retrieves a connection by its id
     *
     * @param   integer $p_connection_id
     *
     * @return  isys_component_dao_result
     */
    public function get_connection($p_connection_id)
    {
        return $this->retrieve('SELECT * FROM isys_connection WHERE isys_connection__id = ' . $this->convert_sql_id($p_connection_id) . ';');
    } // function

    /**
     * Retrieves the object id by connection id.
     *
     * @param   integer $p_connection_id
     *
     * @return  integer
     */
    public function get_object_id_by_connection($p_connection_id)
    {
        $l_row = $this->get_connection($p_connection_id)
            ->get_row();

        return $l_row["isys_connection__isys_obj__id"];
    } // function

    /**
     * Adds a new connection to isys_obj__id
     *
     * @param   integer $p_object_id
     *
     * @return  mixed  Integer with last inserted ID on success, null on failure.
     */
    public function add_connection($p_object_id)
    {
        $l_sql = "INSERT INTO isys_connection SET isys_connection__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return null;
    } // function

    /**
     * Updates an existing connection.
     *
     * @param   integer $p_connection_id
     * @param   integer $p_object_id
     *
     * @return  bool
     */
    public function update_connection($p_connection_id, $p_object_id)
    {
        if (empty($p_connection_id))
        {
            return $this->add_connection($p_object_id);
        } // if

        $l_sql = "UPDATE isys_connection
			SET isys_connection__isys_obj__id = " . $this->convert_sql_id($p_object_id) . "
			WHERE isys_connection__id = " . $this->convert_sql_id($p_connection_id);

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $p_connection_id;
        } // if
        return false;
    } // function

    /**
     * Attaches a connection.
     *
     * @param   string  $p_list_table
     * @param   integer $p_list_id
     * @param   integer $p_object_id
     *
     * @throws  isys_exception_cmdb
     * @return  mixed
     */
    public function attach_connection($p_list_table, $p_list_id, $p_object_id, $p_field = null)
    {
        if ($p_list_table)
        {
            $l_id = $this->add_connection($p_object_id);

            if ($p_field === null)
            {
                $l_set = "SET " . $p_list_table . "__isys_connection__id = " . $this->convert_sql_id($l_id);
            }
            else
            {
                $l_set = "SET " . $p_field . " = " . $this->convert_sql_id($l_id);
            } // if

            $l_sql = "UPDATE " . $p_list_table . " " . $l_set . "
				WHERE " . $p_list_table . "__id = " . $this->convert_sql_id($p_list_id) . ";";

            if ($this->update($l_sql) && $this->apply_update())
            {
                return $l_id;
            } // if
            return false;
        }
        else
        {
            throw new isys_exception_cmdb("Coult not attach connection. List table is empty.");
        } // if
    } // function

    /**
     * Method for retrieving the connection id from the specified table. Creates a new connection if not existing.
     *
     * @param      $p_list_table
     * @param      $p_list_id
     * @param null $p_connection_field
     *
     * @return bool|mixed
     * @throws isys_exception_cmdb
     */
    public function retrieve_connection($p_list_table, $p_list_id, $p_connection_field = null)
    {
        if ($p_list_table)
        {
            if ($p_connection_field)
            {
                $l_connection_field = $p_connection_field;
            }
            else
            {
                $l_connection_field = $p_list_table . '__isys_connection__id';
            } // if

            $l_sql    = "SELECT " . $l_connection_field . " FROM " . $p_list_table . " WHERE " . $p_list_table . "__id = " . $this->convert_sql_id($p_list_id);
            $l_return = $this->retrieve($l_sql)
                ->get_row_value($l_connection_field);

            if ($l_return)
            {
                return $l_return;
            }
            else
            {
                return $this->attach_connection($p_list_table, $p_list_id, null, $p_connection_field);
            } // if
        }
        else
        {
            throw new isys_exception_cmdb("Coult not retrieve connection id. List table is empty.");
        } // if
    } // function

    /**
     * Deletes a connection.
     *
     * @param   integer $p_connectionID
     *
     * @return  boolean
     */
    public function delete($p_connectionID)
    {
        return ($this->update("DELETE FROM isys_connection WHERE isys_connection__id = " . $this->convert_sql_id($p_connectionID) . ";") && $this->apply_update());
    } // function
} // class