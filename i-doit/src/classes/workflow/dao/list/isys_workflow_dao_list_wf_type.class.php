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
class isys_workflow_dao_list_wf_type extends isys_workflow_dao_list
{
    /**
     * Method for retrieving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_workflow_type__id'    => 'ID',
            'isys_workflow_type__title' => 'LC_WORKFLOW__TYPE'
        ];
    } // function

    /**
     * @param int $p_workflow_type__id
     * @param int $p_obj_id
     * @param int $p_rec_status
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_result($p_workflow_type__id, $p_obj_id = null, $p_rec_status = null)
    {
        if ($p_workflow_type__id == 0) $p_workflow_type__id = null;

        $l_sql = $this->get_sql($p_workflow_type__id, $p_rec_status);

        return $this->retrieve($l_sql);
    }

    /**
     * @desc get sql
     *
     */
    public function get_sql($p_workflow_type__id = null, $p_rec_status = null)
    {
        $l_sql = "SELECT * FROM isys_workflow_type WHERE TRUE ";

        if (!is_null($p_workflow_type__id))
        {
            $l_sql .= " AND (isys_workflow_type__id = " . $this->convert_sql_id($p_workflow_type__id) . ") ";
        }

        $l_sql .= "AND (isys_workflow_type__status = " . $this->convert_sql_id(($p_rec_status === null) ? $this->get_rec_status() : $p_rec_status) . ") ";

        $l_sql .= ";";

        return $l_sql;
    }

    /**
     * @param null $p_get_params
     *
     * @return string
     */
    public function get_row_link($p_get_params = null)
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_link[C__GET__MAIN_MENU__NAVIGATION_ID] = $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID];

        $l_link[C__CMDB__GET__TREEMODE] = C__WF__VIEW__TREE;
        $l_link[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__DETAIL__WF_TYPE;
        $l_link[C__WF__GET__TYPE]       = "[{isys_workflow_type__id}]";

        return "?" . urldecode(isys_glob_http_build_query($l_link));
    }

    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database &$p_db)
    {
        parent::__construct($p_db);
    }
}