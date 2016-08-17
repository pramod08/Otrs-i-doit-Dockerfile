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
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    1.0 Wed Jun 21 11:49:57 CEST 2006 11:49:57
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_workflow_dao_list_template extends isys_workflow_dao_list
{
    /**
     * Method for retrieving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_workflow_template_parameter__id"    => "ID",
            "isys_workflow_type__title"               => "LC_WORKFLOW__TYPE",
            "isys_workflow_template_parameter__title" => "LC__TASK__TITLE",
            "isys_workflow_template_parameter__key"   => "Key",
        ];
    } // function

    /**
     * @desc get sql
     *
     */
    public function get_sql($p_workflow_type__id = null)
    {

        global $g_comp_template_language_manager;

        $l_sql = "SELECT * FROM isys_workflow_template_parameter " . "LEFT OUTER JOIN isys_wf_type_2_wf_tp " . "ON " . "isys_wf_type_2_wf_tp__isys_workflow_template_parameter__id = " . "isys_workflow_template_parameter__id " . "LEFT OUTER JOIN isys_workflow_type " . "ON " . "isys_wf_type_2_wf_tp__isys_workflow_type__id = " . "isys_workflow_type__id " . "WHERE TRUE ";

        if (!is_null($p_workflow_type__id))
        {
            $l_sql .= " AND (isys_workflow_type__id = '" . $p_workflow_type__id . "') ";
        }

        return $l_sql;
    }

    public function get_row_link($p_get_params = null)
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_link[C__GET__MAIN_MENU__NAVIGATION_ID] = $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID];

        $l_link[C__CMDB__GET__TREEMODE] = C__WF__VIEW__TREE;
        $l_link[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__DETAIL__TEMPLATE;
        $l_link[C__WF__GET__TYPE]       = $l_gets[C__WF__GET__TYPE];
        $l_link[C__WF__GET__TEMPLATE]   = "[{isys_workflow_template_parameter__id}]";

        return "?" . urldecode(isys_glob_http_build_query($l_link));
    }

    public function __construct(isys_component_database &$p_db)
    {
        parent::__construct($p_db);
    }
}

?>