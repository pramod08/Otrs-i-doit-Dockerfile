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
class isys_export_dao extends isys_module_dao
{

    /**
     * Increments the export counter for the given template
     *
     * @param int $p_id
     *
     * @return bool
     */
    public function count($p_id)
    {
        $l_sql = "UPDATE isys_export SET isys_export__exported = isys_export__exported + 1 WHERE " . "(isys_export__id = '" . $p_id . "');";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Returns export templates
     *
     * @param [int $p_id]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_id = null)
    {
        $l_sql = "SELECT *, CONCAT(isys_export__exported,'x') AS exported_count FROM isys_export WHERE TRUE";

        if (!is_null($p_id))
        {
            $l_sql .= " AND (isys_export__id = '" . $this->get_database_component()
                    ->escape_string($p_id) . "')";
        }

        return $this->retrieve($l_sql . ";");
    }

}

?>