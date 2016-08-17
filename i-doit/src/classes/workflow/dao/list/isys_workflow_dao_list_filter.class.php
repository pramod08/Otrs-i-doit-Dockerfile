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
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0 Wed Jun 21 11:49:57 CEST 2006 11:49:57
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_workflow_dao_list_filter extends isys_workflow_dao_list
{

    private $m_obj__id;

    /**
     * @desc get sql
     */
    public function get_sql()
    {
        global $g_comp_template_language_manager;
        global $g_comp_database;

        switch ($_GET[C__WORKFLOW__GET__FILTER])
        {
            case "d":
                $l_filter = "AND (isys_workflow_action_parameter__key LIKE '%start_date%') " . "AND (date_format(isys_workflow_action_parameter__datetime,'%Y-%m-%d') = " . date(
                        'Y-m-d',
                        time()
                    ) . ")";
                break;
            case "m":
                $l_filter = "AND (isys_workflow_action_parameter__key LIKE '%start_date%') " . "AND (date_format(isys_workflow_action_parameter__datetime,'%Y-%m') = " . date(
                        'Y-m',
                        time()
                    ) . ")";
                break;
            default:
                $l_filter = null;
                break;
        }

        $l_dao_actions = new isys_workflow_dao_action($g_comp_database);
        $l_dao         = $l_dao_actions->get_actions(
            null,
            null,
            C__WORKFLOW__ACTION__TYPE__NEW,
            $_GET["uid"],
            null,
            null,
            $l_filter
        );

        return $l_dao->get_query();
    }

    public function get_category()
    {
        return C__CATG__WORKFLOW;
    }

    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    public function make_row_link()
    {
        return $this->get_row_link();
    }

    /**
     * Method for retrieving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_workflow__id"                        => "ID",
            "isys_workflow__title"                     => "LC__TASK__DETAIL__WORKORDER__TITLE",
            "isys_workflow_type__title"                => "LC_WORKFLOW__TYPE",
            "isys_obj__title"                          => "LC__TASK__DETAIL__WORKORDER__ASSIGNED_PERSONS",
            "isys_workflow_action_parameter__datetime" => "LC__TASK__DETAIL__WORKORDER__START_DATE",
            "isys_workflow__datetime"                  => "LC__TASK__DETAIL__WORKORDER__CREATION_DATE"
        ];
    }

    /**
     * @desc throw the result ! ;)
     *
     * @param unknown_type $p_workflow_type__id
     *
     * @return unknown
     */
    public function get_result($p_workflow_type__id)
    {

        if ($p_workflow_type__id == 0) $p_workflow_type__id = null;
        $l_sql = $this->get_sql($p_workflow_type__id);

        return $this->retrieve($l_sql);
    } // function

    public function get_row_link($p_get_params = null)
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_link[C__GET__MAIN_MENU__NAVIGATION_ID] = $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID];

        $l_link[C__CMDB__GET__TREEMODE] = C__WF__VIEW__TREE;
        $l_link[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__DETAIL__GENERIC;
        $l_link[C__WF__GET__TYPE]       = $l_gets[C__WF__GET__TYPE];
        $l_link[C__WF__GET__ID]         = "[{isys_workflow__id}]";

        return "?" . urldecode(isys_glob_http_build_query($l_link));
    }

    public function __construct(isys_component_database &$p_db, $p_obj__id = null)
    {
        parent::__construct($p_db);

        if (!is_null($p_obj__id))
        {
            $this->m_obj__id = $p_obj__id;
        }
    }
}

?>