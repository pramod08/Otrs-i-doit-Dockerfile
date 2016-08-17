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
 * i-doit.
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    1.0 Fri Jun 23 15:53:59 CEST 2006
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_workflow_dao_list extends isys_component_dao_object_table_list
{
    /**
     *
     * @param   mixed $p_workflow_type__id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_result($p_workflow_type__id)
    {
        if ($p_workflow_type__id == 0)
        {
            $p_workflow_type__id = null;
        } // if

        return $this->retrieve($this->get_sql($p_workflow_type__id));
    } // function

    /**
     * Method for retrieving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        if (empty($_POST["sort"]))
        {
            $_POST["sort"] = "isys_workflow__datetime";
            $_POST["dir"]  = "DESC";
        } // if

        return [
            "isys_workflow__id"                        => "ID",
            "isys_workflow__title"                     => "LC__TASK__DETAIL__WORKORDER__TITLE",
            "isys_workflow_type__title"                => "Workflow Type",
            "isys_workflow_action_parameter__datetime" => "LC__TASK__DETAIL__WORKORDER__START_DATE",
            "isys_workflow_category__title"            => "LC__TASK__DETAIL__WORKORDER__CATEGORY",
        ];
    } // function
} // class