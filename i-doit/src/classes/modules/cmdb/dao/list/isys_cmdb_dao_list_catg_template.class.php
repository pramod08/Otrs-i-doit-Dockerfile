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
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_template extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__TEMPLATE;
    } // function

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_fk_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_result($p_str = '', $p_fk_id, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql = "SELECT isys_catg_template_list__id, isys_catg_template_list__isys_catg_template__id, isys_catg_template_list__title
			FROM isys_catg_template_list
			WHERE isys_catg_template_list__isys_catg_template__id = " . $this->convert_sql_id($p_fk_id) . " ";

        if (!empty($l_cRecStatus))
        {
            $l_sql .= " AND isys_catg_template_list__status = " . $this->convert_sql_int($l_cRecStatus);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_template_list__title" => "LC__CMDB__CATG__TITLE"
        ];
    } // function
} // class