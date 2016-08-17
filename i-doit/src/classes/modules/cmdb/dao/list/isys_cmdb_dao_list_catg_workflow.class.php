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
 * DAO: List for Workflows
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_workflow extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Get fields method.
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
            'isys_workflow__id'                        => 'ID',
            'isys_workflow__title'                     => 'LC__TASK__TITLE',
            'isys_workflow_type__title'                => 'LC_WORKFLOW__TYPE',
            'isys_workflow_action_parameter__datetime' => 'LC__TASK__DETAIL__WORKORDER__START_DATE',
            'isys_workflow_category__title'            => 'LC__TASK__DETAIL__WORKORDER__CATEGORY'
        ];
    } // function

    /**
     * @return string
     */
    public function make_row_link()
    {
        return $this->get_row_link();
    } // function

    /**
     * @return string
     */
    public function get_row_link()
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        return urldecode(
            isys_helper_link::create_url(
                [
                    C__GET__MAIN_MENU__NAVIGATION_ID => $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID],
                    C__CMDB__GET__TREEMODE           => C__WF__VIEW__TREE,
                    C__CMDB__GET__VIEWMODE           => C__WF__VIEW__DETAIL__GENERIC,
                    C__WF__GET__TYPE                 => $l_gets[C__WF__GET__TYPE],
                    C__WF__GET__ID                   => '[{isys_workflow__id}]'
                ]
            )
        );
    } // function

    /**
     * @param array $p_arrRow
     */
    public function format_row(&$p_arrRow)
    {
        global $g_loc;

        $p_arrRow["isys_workflow_action_parameter__datetime"] = $g_loc->fmt_datetime($p_arrRow["isys_workflow_action_parameter__datetime"]);
    }

    /**
     * Return constant of category.
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__WORKFLOW;
    } // function

    /**
     * Return constant of category type
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function
} // class