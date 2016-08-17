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
 * Auth: dao class for module report
 *
 * @package     i-doit
 * @subpackage  dao
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_dao_report extends isys_auth_module_dao
{
    /**
     * Determines which cleanup method should be called
     *
     * @param null $p_method
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function cleanup($p_method = null)
    {
        switch ($p_method)
        {
            case 'views':
                $this->cleanup_views();
                break;
            case 'report':
            case 'custom_report':
                $this->cleanup_report();
                break;
            case 'reports_in_category':
                $this->cleanup_reports_in_category();
                break;
            default:
                $this->cleanup_views()
                    ->cleanup_report()
                    ->cleanup_reports_in_category();
                break;
        } // switch
        return $this;
    } // function

    /**
     * Method to clean up auth paths for reports in category
     *
     * @return $this
     * @throws isys_exception_general
     */
    private function cleanup_reports_in_category()
    {
        global $g_comp_database_system;

        $l_report_query = 'SELECT isys_report_category__id FROM isys_report_category WHERE TRUE';
        $l_search       = 'isys_auth__path LIKE \'REPORTS_IN_CATEGORY/%\'';

        $l_res = $g_comp_database_system->query($l_report_query);
        try
        {
            if ($g_comp_database_system->num_rows($l_res) > 0)
            {

                $l_report_ids = [];
                while ($l_row = $g_comp_database_system->fetch_row_assoc($l_res))
                {
                    $l_report_ids[] = $l_row['isys_report_category__id'];
                } // while

                // Prepare delete query
                $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';
                $l_delete_arr   = [];

                $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id(
                        $this->m_module_id
                    ) . ' ' . 'AND ' . $l_search;

                $l_res_auth = $this->retrieve($l_auth_query);
                while ($l_row = $l_res_auth->get_row())
                {
                    $l_path_arr = explode('/', $l_row['isys_auth__path']);
                    if ($l_path_arr[1] == isys_auth::WILDCHAR) continue;

                    $l_indicator = $l_path_arr[1];
                    $l_auth_id   = $l_row['isys_auth__id'];

                    if (!in_array($l_indicator, $l_report_ids))
                    {
                        $l_delete_arr[] = $l_auth_id;
                    } // if
                } // while
                if (count($l_delete_arr) > 0)
                {
                    $l_delete_query .= '(' . implode(',', $l_delete_arr) . ')';
                    $this->update($l_delete_query);
                    $this->apply_update();
                } // if
            }
            else
            {
                $l_delete_query = 'DELETE FROM isys_auth WHERE ' . $l_search;
                $this->update($l_delete_query);
                $this->apply_update();
            } // if
        }
        catch (isys_exception_general $e)
        {
            throw new isys_exception_general($e->getMessage());
        } // try

        return $this;
    } // function

    /**
     * Method to clean up auth paths for reports or custom reports
     *
     * @return $this
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function cleanup_report()
    {
        global $g_comp_database_system;

        $l_report_query = 'SELECT isys_report__id FROM isys_report WHERE TRUE';
        $l_search       = 'isys_auth__path LIKE \'CUSTOM_REPORT/%\'';

        $l_res = $g_comp_database_system->query($l_report_query);
        try
        {
            if ($g_comp_database_system->num_rows($l_res) > 0)
            {

                $l_report_ids = [];
                while ($l_row = $g_comp_database_system->fetch_row_assoc($l_res))
                {
                    $l_report_ids[] = $l_row['isys_report__id'];
                } // while

                // Prepare delete query
                $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';
                $l_delete_arr   = [];

                $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id(
                        $this->m_module_id
                    ) . ' ' . 'AND ' . $l_search;

                $l_res_auth = $this->retrieve($l_auth_query);
                while ($l_row = $l_res_auth->get_row())
                {
                    $l_path_arr = explode('/', $l_row['isys_auth__path']);
                    if ($l_path_arr[1] == isys_auth::WILDCHAR) continue;

                    $l_indicator = $l_path_arr[1];
                    $l_auth_id   = $l_row['isys_auth__id'];

                    if (!in_array($l_indicator, $l_report_ids))
                    {
                        $l_delete_arr[] = $l_auth_id;
                    } // if
                } // while
                if (count($l_delete_arr) > 0)
                {
                    $l_delete_query .= '(' . implode(',', $l_delete_arr) . ')';
                    $this->update($l_delete_query);
                    $this->apply_update();
                } // if
            } // if
        }
        catch (isys_exception_general $e)
        {
            throw new isys_exception_general($e->getMessage());
        } // try

        return $this;
    } // function

    /**
     * Method to clean up auth paths for report views
     *
     * @return $this
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function cleanup_views()
    {
        global $g_absdir;

        $l_report_obj = new isys_module_report_pro();

        $l_viewdir = $g_absdir . "/src/classes/report/views/";

        try
        {
            if (is_dir($l_viewdir) && is_readable($l_viewdir))
            {

                $l_views      = $l_report_obj->getViews($l_viewdir, true);
                $l_delete_arr = [];

                // Prepare delete query
                $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';

                $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id(
                        $this->m_module_id
                    ) . ' ' . 'AND isys_auth__path LIKE \'VIEWS/%\'';

                $l_res = $this->retrieve($l_auth_query);
                if ($l_res->num_rows() > 0)
                {
                    while ($l_row = $l_res->get_row())
                    {
                        $l_path_arr = explode('/', $l_row['isys_auth__path']);
                        if ($l_path_arr[1] == isys_auth::WILDCHAR) continue;

                        $l_indicator = strtolower($l_path_arr[1]);
                        $l_auth_id   = $l_row['isys_auth__id'];

                        if (!array_key_exists($l_indicator, $l_views))
                        {
                            $l_delete_arr[] = $l_auth_id;
                        } // if
                    } // while
                    if (count($l_delete_arr) > 0)
                    {
                        $l_delete_query .= '(' . implode(',', $l_delete_arr) . ')';
                        $this->update($l_delete_query);
                        $this->apply_update();
                    } // if
                } // if
            } // if
        }
        catch (isys_exception_general $e)
        {
            throw new isys_exception_general($e->getMessage());
        } // try

        return $this;
    } // function
}

?>