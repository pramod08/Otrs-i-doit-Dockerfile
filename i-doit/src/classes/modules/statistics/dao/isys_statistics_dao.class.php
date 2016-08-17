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
 * Export DAO
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_statistics_dao extends isys_module_dao
{

    private $m_cmdb_dao = null;

    /**
     * @param null $p_obj_type
     *
     * @return int
     */
    public function count_objects($p_obj_type = null)
    {
        if (defined("C__OBJTYPE__RELATION") && defined("C__OBJTYPE__PARALLEL_RELATION"))
        {
            $l_condition = "isys_obj__isys_obj_type__id NOT IN ('" . C__OBJTYPE__RELATION . "', '" . C__OBJTYPE__PARALLEL_RELATION . "'";

            if (defined("C__OBJTYPE__NAGIOS_SERVICE") && defined("C__OBJTYPE__NAGIOS_SERVICE_TPL") && defined("C__OBJTYPE__NAGIOS_HOST_TPL"))
            {
                $l_condition .= ", '" . C__OBJTYPE__NAGIOS_SERVICE . "', '" . C__OBJTYPE__NAGIOS_SERVICE_TPL . "', '" . C__OBJTYPE__NAGIOS_HOST_TPL . "'";
            }

            $l_condition .= ") ";
        }
        else
        {
            $l_condition = "isys_obj__isys_obj_type__id NOT IN (" . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__RELATION'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__PARALLEL_RELATION'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__NAGIOS_SERVICE'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__NAGIOS_HOST_TPL'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__NAGIOS_SERVICE_TPL') " . ")";
        }

        if ($this->m_cmdb_dao)
        {
            return $this->m_cmdb_dao->count_objects(null, $p_obj_type, true, " AND " . $l_condition);
        }
        else throw new Exception('Could not count objects: isys_cmdb_dao was not found.');
    }

    /**
     * @return mixed
     */
    public function count_cmdb_references()
    {

        $l_sql = "SELECT COUNT(*) AS counter FROM isys_connection";
        $l_dao = $this->retrieve($l_sql);
        $l_row = $l_dao->get_row();

        return $l_row["counter"];
    }

    /**
     * @return array
     */
    public function get_db_version()
    {
        global $g_comp_database_system;

        if (empty($this->m_isys_info))
        {
            $l_mret = $g_comp_database_system->query("SELECT * FROM isys_db_init;");

            while ($l_mrow = $g_comp_database_system->fetch_row_assoc($l_mret))
            {
                if ($l_mrow["isys_db_init__key"] == "version") $l_version = $l_mrow["isys_db_init__value"];

                if ($l_mrow["isys_db_init__key"] == "revision") $l_revision = (int) $l_mrow["isys_db_init__value"];

                if ($l_mrow["isys_db_init__key"] == "title") $l_title = $l_mrow["isys_db_init__value"];
            }

            $this->m_isys_info = [
                "name"     => @$l_title,
                "version"  => @$l_version,
                "revision" => @$l_revision,
                "type"     => "System"
            ];

            unset($l_version, $l_revision);
        }

        return $this->m_isys_info;
    }

    /**
     * @param [int $p_id]
     *
     * @return isys_component_dao_result
     */
    public function get_data()
    {
        return false;
    }

    /**
     * @param isys_component_database $p_db
     * @param isys_cmdb_dao           $p_cmdb_dao
     */
    public function __construct(isys_component_database $p_db, isys_cmdb_dao $p_cmdb_dao)
    {
        parent::__construct($p_db);
        $this->m_cmdb_dao = $p_cmdb_dao;
    }

}