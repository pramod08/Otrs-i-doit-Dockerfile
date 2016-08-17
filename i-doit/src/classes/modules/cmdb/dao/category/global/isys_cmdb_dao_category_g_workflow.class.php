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
 * DAO: global category for workflows
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_workflow extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'workflow';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  bool
     */
    protected $m_multivalued = true;

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0)
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        return $this->get_data(null, $l_obj_id)
            ->count();
    } // function

    /**
     * Fetches category's data from database.
     *
     * @param   integer $p_workflow_id Workflow's identifier
     * @param   integer $p_obj_id      Object's identifier
     * @param   string  $p_condition   Condition
     * @param   mixed   $p_filter      Filter string or array
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_workflow_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {

        $l_sql = "SELECT * FROM isys_obj
			INNER JOIN isys_workflow_2_isys_obj ON isys_obj__id = isys_workflow_2_isys_obj__isys_obj__id
			INNER JOIN isys_workflow ON isys_workflow__id = isys_workflow_2_isys_obj__isys_workflow__id
			LEFT JOIN isys_workflow_type ON isys_workflow_type__id = isys_workflow__isys_workflow_type__id
			LEFT JOIN isys_workflow_category ON isys_workflow_category__id = isys_workflow__isys_workflow_category__id
			LEFT JOIN isys_workflow_2_isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow__id = isys_workflow__id
			LEFT JOIN isys_workflow_action ON isys_workflow_action__id = isys_workflow_2_isys_workflow_action__isys_workflow_action__id
			LEFT JOIN isys_workflow_action_parameter ON isys_workflow_action_parameter__isys_workflow_action__id = isys_workflow_action__id
			WHERE isys_workflow_action_parameter__key LIKE '%start_date'
			AND isys_workflow_action__isys_workflow_action_type__id = " . $this->convert_sql_id(C__WORKFLOW__ACTION__TYPE__NEW) . " " . $p_condition . $this->prepare_filter(
                $p_filter
            );

        if ($p_obj_id !== null)
        {
            $l_sql .= " " . $this->get_object_condition($p_obj_id);
        } // if

        if ($p_workflow_id !== null)
        {
            $l_sql .= " AND isys_workflow__id = " . $this->convert_sql_id($p_workflow_id);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    } // function
} // class