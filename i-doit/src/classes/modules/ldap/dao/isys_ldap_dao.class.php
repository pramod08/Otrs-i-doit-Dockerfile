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
 * LDAP Module Dao
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_ldap_dao extends isys_module_dao
{
    /**
     * Deletes a ldap server.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete_server($p_id)
    {
        return $this->m_db->query('DELETE FROM isys_ldap WHERE isys_ldap__id = ' . $this->convert_sql_id($p_id) . ';');
    } // function

    /**
     * Validates given arguments, returns false if one argument is empty.
     *
     * @return  boolean
     */
    public function validate()
    {
        $l_argv = func_get_args();

        foreach ($l_argv as $l_arg)
        {
            if ($l_arg == "-1" || $l_arg == "" || is_null($l_arg))
            {
                return false;
            } // if
        } // foreach

        return true;
    } // function

    /**
     * Saves a registered ldap server.
     *
     * @param   integer $p_directory_type
     * @param   string  $p_host
     * @param   integer $p_port
     * @param   string  $p_user_dn
     * @param   string  $p_pass
     * @param   string  $p_user_search
     * @param   string  $p_group_search
     * @param   string  $p_filter
     * @param   integer $p_active
     * @param   integer $p_timelimit
     * @param   integer $p_recursive
     * @param   integer $p_tls
     * @param   integer $p_version
     * @param   integer $p_id
     * @param   array   $p_filter_arr
     *
     * @throws  Exception
     * @return  boolean
     */
    public function save_server($p_directory_type, $p_host, $p_port, $p_user_dn, $p_pass, $p_user_search, $p_group_search, $p_filter, $p_active, $p_timelimit = 30, $p_recursive = 0, $p_tls = 0, $p_version = 3, $p_id, $p_filter_arr = null)
    {
        if ($this->validate($p_directory_type, $p_host, $p_port, $p_user_dn, $p_pass, $p_user_search))
        {
            $l_sql = "UPDATE isys_ldap SET
				isys_ldap__isys_ldap_directory__id = '{$this->m_db->escape_string($p_directory_type)}',
				isys_ldap__hostname = '{$this->m_db->escape_string($p_host)}',
				isys_ldap__port = '{$this->m_db->escape_string($p_port)}',
				isys_ldap__dn = '{$this->m_db->escape_string($p_user_dn)}',
				isys_ldap__password = '{$this->m_db->escape_string(isys_helper_crypt::encrypt($p_pass))}',
				isys_ldap__user_search = '{$this->m_db->escape_string($p_user_search)}',
				isys_ldap__group_search = '{$this->m_db->escape_string($p_group_search)}',
				isys_ldap__filter = '{$this->m_db->escape_string($p_filter)}',
				isys_ldap__active = '{$this->m_db->escape_string($p_active)}',
				isys_ldap__recursive = '{$this->m_db->escape_string($p_recursive)}',
				isys_ldap__timelimit = '{$this->m_db->escape_string($p_timelimit)}',
				isys_ldap__tls = '" . intval($p_tls) . "',
				isys_ldap__version = '" . intval($p_version) . "' ";

            if (is_array($p_filter_arr))
            {
                $l_sql .= ", isys_ldap__filter_array = '" . $this->m_db->escape_string(serialize($p_filter_arr)) . "'";
            } // if

            $l_sql .= "WHERE (isys_ldap__id = '{$p_id}');";

            if ($this->update($l_sql) && $this->apply_update())
            {
                return $this->get_last_insert_id();
            }
            else
            {
                return false;
            } // if
        }
        else
        {
            throw new Exception("Not all required fields are filled.");
        } // if
    } // function

    /**
     * Creates a new ldap server
     *
     * @param int    $p_directory_type
     * @param string $p_host
     * @param int    $p_port
     * @param string $p_user_dn
     * @param string $p_pass
     * @param string $p_user_search
     * @param string $p_filter
     * @param int    $p_active
     * @param int    $p_timelimit
     * @param int    $p_recursive
     * @param int    $p_tls
     * @param int    $p_version
     *
     * @return int|bool
     */
    public function create_server($p_directory_type, $p_host, $p_port, $p_user_dn, $p_pass, $p_user_search, $p_group_search, $p_filter, $p_active, $p_timelimit = 30, $p_recursive = 0, $p_tls = 0, $p_version = 3, $p_filter_arr = null)
    {
        if ($this->validate($p_directory_type, $p_host, $p_port, $p_user_dn, $p_pass, $p_user_search))
        {
            if (!$this->exists($p_host, $p_port, $p_user_dn, $p_pass, $p_user_search, $p_filter))
            {
                $l_sql = "INSERT INTO isys_ldap SET " . "isys_ldap__isys_ldap_directory__id = '{$this->m_db->escape_string($p_directory_type)}', " . "isys_ldap__hostname = '{$this->m_db->escape_string($p_host)}', " . "isys_ldap__port = '{$this->m_db->escape_string($p_port)}', " . "isys_ldap__dn = '{$this->m_db->escape_string($p_user_dn)}', " . "isys_ldap__password = '{$this->m_db->escape_string(isys_helper_crypt::encrypt($p_pass))}', " . "isys_ldap__user_search = '{$this->m_db->escape_string($p_user_search)}', " . "isys_ldap__group_search = '{$this->m_db->escape_string($p_group_search)}', " . "isys_ldap__filter = '{$this->m_db->escape_string($p_filter)}', " . "isys_ldap__active = '{$this->m_db->escape_string($p_active)}', " . "isys_ldap__timelimit = '{$this->m_db->escape_string($p_timelimit)}', " . "isys_ldap__recursive = '{$this->m_db->escape_string($p_recursive)}', " . "isys_ldap__tls = '" . intval(
                        $p_tls
                    ) . "', " . "isys_ldap__version = '" . intval($p_version) . "' ";

                if (is_array($p_filter_arr))
                {
                    $l_sql .= ",isys_ldap__filter_array = '" . $this->m_db->escape_string(serialize($p_filter_arr)) . "'";
                }

                if ($this->update($l_sql) && $this->apply_update())
                {
                    return $this->get_last_insert_id();
                }
                else return false;
            }
            else throw new Exception ("Server already exists.");
        }
        else throw new Exception("Not all required fields are filled.");
    }

    /**
     * Checks if a ldap server is already registered
     *
     * @param string $p_host
     * @param int    $p_port
     * @param string $p_user_dn
     * @param string $p_pass
     * @param        $p_user_search
     * @param        $p_filter
     *
     * @return boolean
     */
    public function exists($p_host, $p_port, $p_user_dn, $p_pass, $p_user_search, $p_filter)
    {
        $l_sql = "SELECT * FROM isys_ldap
			WHERE isys_ldap__hostname = '{$p_host}'
			AND isys_ldap__port = '{$p_port}'
			AND isys_ldap__dn = '{$p_user_dn}'
			AND isys_ldap__password = '" . isys_helper_crypt::encrypt($p_pass) . "'
			AND isys_ldap__user_search = '{$p_user_search}'
			AND isys_ldap__filter = '{$p_filter}';";

        return (count($this->retrieve($l_sql)) > 0);
    } // function

    /**
     * Saves an existing ldap type
     *
     * @param string $p_title
     * @param string $p_const
     * @param array  $p_mapping
     */
    public function save_ldap_directory($p_title, $p_const, $p_mapping, $p_id)
    {

        if ($p_id > 0)
        {
            $l_mapping = $this->format_mapping($p_mapping);

            $l_sql = "UPDATE isys_ldap_directory SET " . "isys_ldap_directory__title = '{$this->m_db->escape_string($p_title)}', ";

            if (!empty($p_const))
            {
                $l_sql .= "isys_ldap_directory__const = '{$this->m_db->escape_string($p_const)}', ";
            }

            $l_sql .= "isys_ldap_directory__mapping = '{$l_mapping}', " . "isys_ldap_directory__status = '" . C__RECORD_STATUS__NORMAL . "' " . "WHERE (isys_ldap_directory__id = '{$p_id}');";

            return ($this->update($l_sql) && $this->apply_update());
        }

        return false;
    } // function

    /**
     * Creates a new ldap type.
     *
     * @param   string $p_title
     * @param   string $p_const
     * @param   array  $p_mapping
     *
     * @return  boolean
     */
    public function create_ldap_directory($p_title, $p_const, $p_mapping)
    {
        $l_sql = 'INSERT INTO isys_ldap_directory SET isys_ldap_directory__title = ' . $this->convert_sql_text($p_title) . ', ';

        if (!empty($p_const))
        {
            $l_sql .= 'isys_ldap_directory__const = ' . $this->convert_sql_text($p_const) . ', ';
        } // if

        $l_sql .= 'isys_ldap_directory__mapping = ' . $this->convert_sql_text($this->format_mapping($p_mapping)) . ',
			isys_ldap_directory__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    }

    /**
     * Returns mapping as array.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_mapping($p_id)
    {
        return unserialize(
            $this->get_ldap_types($p_id)
                ->get_row_value('isys_ldap_directory__mapping')
        );
    } // function

    /**
     * Returns all registered ldap types.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_ldap_types($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_ldap_directory WHERE TRUE';

        if ($p_id !== null)
        {
            $l_sql .= ' AND isys_ldap_directory__id = ' . $this->convert_sql_id($p_id);
        } // if

        return $this->retrieve($l_sql . '  ORDER BY isys_ldap_directory__title ASC;');
    } // function

    /**
     * Returns only active and registered servers.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_active_servers($p_id = null)
    {
        return $this->get_data($p_id, 1);
    } // function

    /**
     * Returns ldap configurations.
     *
     * @param   integer $p_id
     * @param   integer $p_active
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_id = null, $p_active = null)
    {
        $l_sql = 'SELECT * FROM isys_ldap
			 LEFT JOIN isys_ldap_directory ON isys_ldap__isys_ldap_directory__id = isys_ldap_directory__id
			 WHERE TRUE';

        if ($p_id !== null)
        {
            $l_sql .= ' AND isys_ldap__id = ' . $this->convert_sql_id($p_id);
        } // if

        if ($p_active !== null)
        {
            $l_sql .= ' AND isys_ldap__active = ' . $this->convert_sql_id($p_active);
        } // if

        return $this->retrieve($l_sql . ' ORDER BY isys_ldap_directory__title, isys_ldap__hostname ASC;');
    } // function

    /**
     * Formats a mappign
     *
     * @param   mixed $p_mapping
     *
     * @return  string
     */
    private function format_mapping($p_mapping)
    {
        return serialize($p_mapping);
    } // function
} // class