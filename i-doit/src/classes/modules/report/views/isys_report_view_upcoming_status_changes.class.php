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
 * i-doit Report Manager View for upcoming changes.
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_view_upcoming_status_changes extends isys_report_view
{
    /**
     * Empty abstract method.
     */
    public function ajax_request()
    {
        ;
    } // function

    /**
     * LC string of this reports view's description.
     *
     * @return  string
     */
    public static function description()
    {
        return "LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__DESCRIPTION";
    } // function

    /**
     * Initialize method.
     *
     * @return  boolean
     */
    public function init()
    {
        return true;
    } // function

    /**
     * LC string of this report view's title.
     *
     * @return  string
     */
    public static function name()
    {
        return "LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__TITLE";
    } // function

    /**
     * Method for preparing the data.
     */
    public function start()
    {
        global $g_comp_template, $g_comp_database;

        $l_changedata   = $l_titles = [];
        $l_changegroups = [
            3,
            7,
            10,
            14,
            30
        ];

        $l_dao_planning = new isys_cmdb_dao_category_g_planning($g_comp_database);

        foreach ($l_changegroups as $l_changedays)
        {
            $l_data = [];

            $l_tmp = $l_dao_planning->get_data(null, null, " AND (isys_catg_planning_list__start BETWEEN " . time() . " AND " . strtotime("+$l_changedays days") . ")");

            while ($l_row = $l_tmp->get_row())
            {
                $l_data[] = [
                    "id"     => $l_row["isys_obj__id"],
                    "title"  => $l_row["isys_obj__title"],
                    "status" => $l_row["isys_cmdb_status__title"],
                    "start"  => $l_row["isys_catg_planning_list__start"],
                    "end"    => $l_row["isys_catg_planning_list__end"]
                ];
            } // while

            $l_changedata[$l_changedays] = $l_data;
            $l_titles[$l_changedays]     = _L('LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__NEXT_DAYS', $l_changedays);
        } // foreach

        $g_comp_template->assign("changeData", $l_changedata)
            ->assign('titles', $l_titles);
    } // function

    /**
     * Template file of this report view.
     *
     * @return  string
     */
    public function template()
    {
        return "view_upcoming_status_change.tpl";
    } // function

    /**
     * Returns the report view's type.
     *
     * @return  integer
     */
    public static function type()
    {
        return self::c_php_view;
    } // function

    /**
     * Report view's view type.
     *
     * @return string
     */
    public static function viewtype()
    {
        return "CMDB-Status";
    }  // function
} // class
?>