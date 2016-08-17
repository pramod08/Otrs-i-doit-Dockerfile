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
 * Template Module Dao
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_templates_dao extends isys_module_dao
{

    /**
     * Retrieves all templates
     *
     * @param int $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_templates($p_obj_id = null)
    {
        return $this->get_data($p_obj_id);
    }

    /**
     * Retrieve all mass change templates
     *
     */
    public function get_mass_change_templates($p_obj_id = null)
    {
        return $this->get_data($p_obj_id, "isys_obj_type__id, isys_obj__title", C__RECORD_STATUS__MASS_CHANGES_TEMPLATE);
    }

    /**
     * Retrieves all templates
     *
     * @param int $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_obj_id = null, $p_order_by = "isys_obj_type__id,isys_obj__title", $p_record_status = C__RECORD_STATUS__TEMPLATE)
    {

        $l_sql = "SELECT *, isys_obj__id AS isys_id FROM isys_obj " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " . "WHERE TRUE ";

        if (!empty($p_obj_id))
        {
            $l_sql .= " AND (isys_obj__id = '{$p_obj_id}')";
        }

        $l_sql .= " AND (isys_obj__status = '" . $p_record_status . "')";
        $l_sql .= " GROUP BY isys_obj__id";
        $l_sql .= " ORDER BY " . $p_order_by;

        return $this->retrieve($l_sql);
    }

    /**
     * Deletes a template
     *
     * @param int $p_obj_id
     */
    public function delete_template($p_obj_id)
    {
        $l_dao = new isys_cmdb_dao($this->get_database_component());
        $l_dao->delete_object($p_obj_id);
    }

}

?>