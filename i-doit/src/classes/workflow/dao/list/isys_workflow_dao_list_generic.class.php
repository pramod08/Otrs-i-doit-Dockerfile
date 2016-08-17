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
class isys_workflow_dao_list_generic extends isys_workflow_dao_list
{

    private $m_obj__id;
    private $m_order_dir;
    private $m_order_field;

    /**
     * @desc get sql
     *
     */
    public function get_sql($p_workflow_type__id = null, $p_action_type__id = C__WORKFLOW__ACTION__TYPE__NEW)
    {
        $l_sql = "SELECT * FROM isys_workflow_action " . "INNER JOIN isys_workflow_2_isys_workflow_action " . "ON " . "isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . "isys_workflow_action__id " . "INNER JOIN isys_workflow_action_type " . "ON " . "isys_workflow_action_type__id = " . "isys_workflow_action__isys_workflow_action_type__id " . "INNER JOIN isys_workflow_action_parameter " . "ON " . "isys_workflow_action_parameter__isys_workflow_action__id = " . "isys_workflow_action__id " . "INNER JOIN isys_workflow " . "ON " . "isys_workflow__id = " . "isys_workflow_2_isys_workflow_action__isys_workflow__id " . "INNER JOIN isys_workflow_type " . "ON " . "isys_workflow_type__id = " . "isys_workflow__isys_workflow_type__id " . "LEFT OUTER JOIN isys_workflow_category " . "ON " . "isys_workflow_category__id = " . "isys_workflow__isys_workflow_category__id " . "WHERE TRUE ";

        if (!is_null($this->m_obj__id))
        {
            $l_sql .= " AND (isys_workflow__isys_obj__id = '" . $this->m_obj__id . "') ";
        }

        if (!is_null($p_workflow_type__id))
        {
            $l_sql .= " AND (isys_workflow_type__id = '" . $p_workflow_type__id . "')";
        }

        switch ($this->get_rec_status())
        {
            case C__TASK__STATUS__OPEN:
                $l_sql .= " AND (isys_workflow__status >= " . C__TASK__STATUS__ASSIGNMENT . ") " . " AND (isys_workflow_action_parameter__datetime >= NOW()) " . " AND (isys_workflow_action_type__id = '" . $p_action_type__id . "') ";
                break;
            case C__TASK__STATUS__END:
                $l_sql .= " AND (isys_workflow__status >= " . C__TASK__STATUS__OPEN . " AND isys_workflow__status <= " . C__TASK__STATUS__END . ") " . " AND (isys_workflow_action_parameter__datetime <= NOW()) ";
                break;
            default:
                $l_sql .= " AND (isys_workflow__status = '" . $this->get_rec_status() . "') " . " AND (isys_workflow_action_type__id = '" . $p_action_type__id . "') ";
                break;
        }

        $l_sql .= " AND (isys_workflow_action_parameter__key LIKE '%start_date')";

        $l_sql .= " GROUP BY isys_workflow__id ";

        if (!empty($this->m_order_field))
        {
            $l_sql .= " ORDER BY " . $this->m_order_field . " " . $this->m_order_dir;
        }

        return $l_sql . ";";
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

    /**
     * Format values in the list
     *
     * @param array $p_arrRow
     *
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2007-08-01
     * @version Van Quyen Hoang <qhoang@i-doit.org> - 2013-07-10
     */
    public function format_row(&$p_arrRow)
    {
        global $g_loc;

        $p_arrRow["isys_workflow_action_parameter__datetime"] = $g_loc->fmt_datetime($p_arrRow["isys_workflow_action_parameter__datetime"]);
    }

    public function __construct(isys_component_database &$p_db, $p_obj__id = null, $p_order_field = null, $p_order_dir = null)
    {
        parent::__construct($p_db);

        if (!is_null($p_obj__id))
        {
            $this->m_obj__id = $p_obj__id;
        }
        if (!is_null($p_order_field))
        {
            $this->m_order_field = $p_order_field;
        }
        if (!is_null($p_order_dir))
        {
            $this->m_order_dir = $p_order_dir;
        }
    }
}

?>