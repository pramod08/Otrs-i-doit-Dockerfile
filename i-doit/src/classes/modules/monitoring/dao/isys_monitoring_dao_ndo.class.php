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
 * Monitoring NDO DAO.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_monitoring_dao_ndo extends isys_module_dao
{
    /**
     * This variable contains the NDO table prefix.
     *
     * @var  string
     */
    protected $m_db_prefix = null;

    /**
     * Method for retrieving the monitoring configuration.
     *
     * @param   integer $p_id
     * @param   string  $p_type
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data($p_id = null, $p_type = null)
    {
        return $this->retrieve('SELECT 1+1;');
    } // function

    /**
     * Method for setting the DB prefix.
     *
     * @param   string $p_db_prefix
     *
     * @return  isys_monitoring_dao_ndo
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_db_prefix($p_db_prefix)
    {
        $this->m_db_prefix = $p_db_prefix;

        return $this;
    } // function

    /**
     * Method for retrieving the current host data.
     *
     * @param   integer $p_obj_id
     * @param   mixed   $p_state May be an integer or an array of integers.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_host_data($p_obj_id = null, $p_state = null)
    {
        $l_query = 'SELECT current_state AS state, name1 AS hostname
			FROM ' . $this->m_db_prefix . 'hoststatus
			NATURAL JOIN ' . $this->m_db_prefix . 'hosts
			INNER JOIN ' . $this->m_db_prefix . 'objects ON host_object_id = object_id
			WHERE TRUE';

        if ($p_obj_id !== null)
        {
            $l_query .= ' AND name1 = ' . $this->convert_sql_text(isys_monitoring_helper::render_export_hostname($p_obj_id));
        } // if

        if ($p_state !== null)
        {
            if (!is_array($p_state))
            {
                $p_state = [$p_state];
            } // if

            $l_query .= ' AND current_state IN (' . implode(', ', array_map('intval', $p_state)) . ')';
        } // if

        return $this->retrieve($l_query . ';');
    } // function

    /**
     * Method for retrieving hosts, which status is not OK.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_not_ok_hosts()
    {
        $l_query = 'SELECT current_state AS state, output AS state_info, name1 AS hostname
			FROM ' . $this->m_db_prefix . 'hoststatus
			NATURAL JOIN ' . $this->m_db_prefix . 'hosts
			INNER JOIN ' . $this->m_db_prefix . 'objects ON host_object_id = object_id
			WHERE current_state != ' . $this->convert_sql_int(C__MONITORING__STATE__OK) . ';';

        return $this->retrieve($l_query);
    } // function

    /**
     * Method for retrieving the current service data.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_service_data($p_obj_id)
    {
        $l_query = 'SELECT name2 AS name, check_command, current_state AS state
			FROM ' . $this->m_db_prefix . 'servicestatus
			NATURAL JOIN ' . $this->m_db_prefix . 'services
			INNER JOIN ' . $this->m_db_prefix . 'objects ON service_object_id = object_id
			WHERE name1 = ' . $this->convert_sql_text(isys_monitoring_helper::render_export_hostname($p_obj_id)) . ';';

        return $this->retrieve($l_query);
    } // function
} // class