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
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_dao_mandator extends isys_component_dao
{
    /**
     * Adds a new mandator entry.
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_dir_cache
     * @param   string  $p_dir_tpl
     * @param   string  $p_db_host
     * @param   integer $p_db_port
     * @param   string  $p_db_user
     * @param   string  $p_db_pass
     * @param   integer $p_sort
     * @param   integer $p_active
     *
     * @return  boolean
     * @author  Dennis Stücken
     */
    public function add($p_title, $p_description, $p_dir_cache = null, $p_dir_tpl = "default", $p_db_host = "localhost", $p_db_port = 3306, $p_db_user = "root", $p_db_pass = "", $p_sort = 0, $p_active = 1)
    {
        if ($p_dir_cache == null)
        {
            $p_dir_cache = "cache_" . str_replace(" ", "_", strtolower($p_title));
        } // if

        if (empty($p_dir_tpl))
        {
            $p_dir_tpl = "default";
        } // if

        if (empty($p_db_host))
        {
            $p_db_host = "localhost";
        } // if

        if (empty($p_db_user))
        {
            $p_db_user = "root";
        } // if

        if (empty($p_db_port))
        {
            $p_db_port = 3306;
        } // if

        if (empty($p_sort))
        {
            $p_sort = 0;
        } // if

        if (empty($p_active))
        {
            $p_active = 1;
        } // if

        $l_sql = "INSERT INTO isys_mandator
			SET isys_mandator__title = '" . $p_title . "',
			isys_mandator__description = '" . $p_description . "',
			isys_mandator__dir_cache = '" . $p_dir_cache . "',
			isys_mandator__dir_tpl = '" . $p_dir_tpl . "',
			isys_mandator__db_host = '" . $p_db_host . "',
			isys_mandator__db_port = '" . $p_db_port . "',
			isys_mandator__db_user = '" . $p_db_user . "',
			isys_mandator__db_pass = '" . $p_db_pass . "',
			isys_mandator__sort = '" . $p_sort . "',
			isys_mandator__active = '" . $p_active . "';";

        return $this->update($l_sql);
    } // function

    /**
     * Edits a mandator entry.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_dir_cache
     * @param   string  $p_dir_tpl
     * @param   string  $p_db_host
     * @param   integer $p_db_port
     * @param   string  $p_db_user
     * @param   string  $p_db_pass
     * @param   integer $p_sort
     * @param   integer $p_active
     *
     * @return  boolean
     * @author  Dennis Stücken
     */
    public function edit($p_id, $p_title, $p_description, $p_dir_cache = null, $p_dir_tpl = "default", $p_db_host = "localhost", $p_db_port = 3306, $p_db_user = "root", $p_db_pass = "", $p_sort = 0, $p_active = 1)
    {
        if ($p_dir_cache == null)
        {
            $p_dir_cache = "cache_" . str_replace(" ", "_", strtolower($p_title));
        } // if

        if (empty($p_dir_tpl))
        {
            $p_dir_tpl = "default";
        } // if

        if (empty($p_db_host))
        {
            $p_db_host = "localhost";
        } // if

        if (empty($p_db_user))
        {
            $p_db_user = "root";
        } // if

        if (empty($p_db_port))
        {
            $p_db_port = 3306;
        } // if

        if (empty($p_sort))
        {
            $p_sort = 0;
        } // if

        if (empty($p_active))
        {
            $p_active = 1;
        } // if

        $l_sql = "UPDATE isys_mandator
			SET isys_mandator__title = '" . $p_title . "',
			isys_mandator__description = '" . $p_description . "',
			isys_mandator__dir_cache = '" . $p_dir_cache . "',
			isys_mandator__dir_tpl = '" . $p_dir_tpl . "',
			isys_mandator__db_host = '" . $p_db_host . "',
			isys_mandator__db_port = '" . $p_db_port . "',
			isys_mandator__db_user = '" . $p_db_user . "',
			isys_mandator__db_pass = '" . $p_db_pass . "',
			isys_mandator__sort = '" . $p_sort . "',
			isys_mandator__active = '" . $p_active . "'
			WHERE (isys_mandator__id = '" . $p_id . "');";

        return $this->update($l_sql);
    } // function

    /**
     * @param   string $p_name
     *
     * @return  boolean
     */
    public function get_mandator_id_by_db_name($p_name)
    {
        $l_res = $this->retrieve("SELECT isys_mandator__id FROM isys_mandator WHERE (isys_mandator__db_name = '" . $p_name . "') LIMIT 1;");

        if (count($l_res))
        {
            return $l_res->get_row_value('isys_mandator__id');
        } // if

        return false;
    } // function

    /**
     * Used in session component.
     *
     * @param   integer $p_mandator_id
     *
     * @return  resource
     */
    public function get_mandator_query($p_mandator_id)
    {
        global $g_comp_database_system;

        $l_sql = "SELECT * FROM isys_mandator
			WHERE isys_mandator__id = '" . $p_mandator_id . "'
			AND isys_mandator__active = 1
			ORDER BY isys_mandator__sort ASC;";

        return $g_comp_database_system->query($l_sql);
    } // function

    /**
     * Mandator-DAO.
     *
     * @param   integer $p_mandator_id
     * @param   integer $p_exclude_inactive
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_mandator($p_mandator_id = null, $p_exclude_inactive = 1, $p_condition = "")
    {
        $l_sql = "SELECT * FROM isys_mandator LEFT JOIN isys_licence ON isys_licence__isys_mandator__id = isys_mandator__id WHERE TRUE";

        if (!empty($p_mandator_id))
        {
            $l_sql .= " AND isys_mandator__id = '" . $p_mandator_id . "'";
        } // if

        if ($p_exclude_inactive == 1)
        {
            $l_sql .= " AND isys_mandator__active = 1";
        } // if

        return $this->retrieve($l_sql . $p_condition . " ORDER BY isys_mandator__sort ASC;");
    } // function

    /**
     * @param   integer $p_id
     * @param   integer $p_active
     *
     * @return  boolean
     */
    public function set_active($p_id, $p_active)
    {
        $l_sql = "UPDATE isys_mandator SET isys_mandator__active = '" . $p_active . "' WHERE isys_mandator__id = '" . $p_id . "';";

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Activates a mandator.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function activate_mandator($p_id)
    {
        return $this->set_active($p_id, 1);
    } // function

    /**
     * Deactivates a mandator.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function deactivate_mandator($p_id)
    {
        return $this->set_active($p_id, 0);
    } // function

    /**
     * Deletes a mandator.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete($p_id)
    {
        // Delete mandator
        $l_sql = "DELETE FROM isys_mandator WHERE isys_mandator__id = '" . $p_id . "';";
        $this->update($l_sql);
        // Delete mandator settings
        $l_sql = "DELETE FROM isys_settings WHERE isys_settings__isys_mandator__id = '" . $p_id . "';";
        $this->update($l_sql);

        return $this->apply_update();
    } // function

    /**
     * Constructor
     *
     * @param  isys_component_database $p_database
     */
    public function __construct($p_database = null)
    {
        if (is_object($p_database))
        {
            parent::__construct($p_database);
        }
        else
        {
            global $g_comp_database_system;

            parent::__construct($g_comp_database_system);
        } // if
    } // function
} // class