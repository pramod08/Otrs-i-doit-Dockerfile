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
 * DAO: specific category for file objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_file_object extends isys_cmdb_dao_category_s_file
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'file_object';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__FILE_OBJECTS';
    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_cats_file_list';
    /**
     * Category's template.
     *
     * @var  string
     */
    protected $m_tpl = 'object_table_list.tpl';

    /**
     * Method for retrieving the number of objects, assigned to an object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null)
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        $l_sql = "SELECT count(isys_obj__id) AS count FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_catg_manual_list ON isys_catg_manual_list__isys_connection__id = isys_connection__id " . "LEFT JOIN isys_catg_file_list ON isys_catg_file_list__isys_connection__id = isys_connection__id " . "LEFT JOIN isys_catg_emergency_plan_list ON isys_catg_emergency_plan_list__isys_connection__id = isys_connection__id " . "WHERE (isys_catg_manual_list__id IS NOT NULL OR isys_catg_file_list__id IS NOT NULL OR isys_catg_emergency_plan_list__id IS NOT NULL) ";

        if ($l_obj_id !== null)
        {
            $l_sql .= "AND (isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        } // if

        return (int) $this->retrieve($l_sql)
            ->get_row_value('count');
    } // function

    /**
     * Get data method.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id " . "LEFT JOIN isys_catg_manual_list ON isys_catg_manual_list__isys_connection__id = isys_connection__id " . "LEFT JOIN isys_catg_file_list ON isys_catg_file_list__isys_connection__id = isys_connection__id " . "LEFT JOIN isys_catg_emergency_plan_list ON isys_catg_emergency_plan_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " . $p_condition . " ";

        $l_sql .= "AND (isys_catg_manual_list__id IS NOT NULL OR isys_catg_file_list__id IS NOT NULL OR isys_catg_emergency_plan_list__id IS NOT NULL) ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_obj__status = " . $this->convert_sql_int($p_status) . ") ";
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @param array $p_data
     * @param mixed $p_prepend_table_field
     *
     * @return  boolean
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        return true;
    } // function

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @return  boolean
     */
    public function validate_user_data()
    {
        return true;
    } // function
} // class