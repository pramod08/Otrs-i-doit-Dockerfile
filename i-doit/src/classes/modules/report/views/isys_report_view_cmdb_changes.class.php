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
 * i-doit Report View which shows all CMDB-Status changes of all objects
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.4.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_view_cmdb_changes extends isys_report_view
{

    public function ajax_request()
    {
        global $g_dirs;

        /**
         * @var $l_dao isys_cmdb_dao
         */
        $l_dao      = isys_cmdb_dao::instance(isys_application::instance()->database);
        $l_return   = [
            'data'    => [],
            'success' => false
        ];
        $l_objtypes = [];

        // Get all object types
        $l_res_objtypes = $l_dao->get_object_types();
        while ($l_row_objtypes = $l_res_objtypes->get_row())
        {
            $l_objtypes[$l_row_objtypes['isys_obj_type__title']] = _L($l_row_objtypes['isys_obj_type__title']);
        } // while

        // Fetching some translations that will be used a few times.
        $l_lc_title       = _L('LC__UNIVERSAL__TITLE_LINK');
        $l_lc_object_type = _L('LC__CMDB__OBJTYPE');
        $l_lc_date        = _L('LC_UNIVERSAL__DATE');
        $l_lc_changes     = _L('LC__UNIVERSAL__CHANGES');

        $l_period_from = $_POST['C__CMDB_CHANGES__PERIOD_FROM__HIDDEN'];
        $l_period_to   = $_POST['C__CMDB_CHANGES__PERIOD_TO__HIDDEN'];
        $l_contacts    = $_POST['C__CMDB_CHANGES__PERSONS__HIDDEN'];

        // Query
        $l_sql = "SELECT isys_catg_logb_list__isys_obj__id, isys_obj__title AS '" . $l_lc_title . "',
			isys_logbook__obj_type_static AS '" . $l_lc_object_type . "', isys_logbook__user_name_static AS '" . _L('LC__CMDB__LOGBOOK__SOURCE__USER') . "',
			isys_logbook__date AS '" . $l_lc_date . "', isys_logbook__changes AS '" . $l_lc_changes . "'
			FROM isys_catg_logb_list
			INNER JOIN isys_logbook ON isys_logbook__id = isys_catg_logb_list__isys_logbook__id
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_logb_list__isys_obj__id
			WHERE isys_logbook__event_static = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED'
			AND (isys_logbook__changes LIKE '%cmdb\_status%' OR isys_logbook__changes LIKE '%C\_\_OBJ\_\_CMDB\_STATUS%') ";

        // From date
        if ($l_period_from != '')
        {
            $l_sql .= 'AND isys_logbook__date > ' . $l_dao->convert_sql_text($l_period_from . ' 00:00:00') . ' ';
        } // if

        // To date
        if ($l_period_to != '')
        {
            $l_sql .= 'AND isys_logbook__date <= ' . $l_dao->convert_sql_text($l_period_to . ' 23:59:59') . ' ';
        } // if

        // Contact objects
        if ($l_contacts != '')
        {
            $l_contacts = isys_format_json::decode($l_contacts);

            if (count($l_contacts) > 1)
            {
                $l_sql .= 'AND isys_logbook__isys_obj__id IN (' . implode(',', $l_contacts) . ') ';
            }
            else
            {
                $l_sql .= 'AND isys_logbook__isys_obj__id = ' . $l_dao->convert_sql_id($l_contacts[0]);
            } // if
        } // if

        $l_res = $l_dao->retrieve($l_sql . ';');

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_obj_id = $l_row['isys_catg_logb_list__isys_obj__id'];
                unset($l_row['isys_catg_logb_list__isys_obj__id']);

                // Create object link
                $l_url_link = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_obj_id]);

                $l_row[$l_lc_title] = "<a href='" . $l_url_link . "'>" . $l_row[_L('LC__UNIVERSAL__TITLE_LINK')] . "</a>";

                $l_row[$l_lc_object_type] = $l_objtypes[$l_row[$l_lc_object_type]];
                $l_row[$l_lc_date]        = isys_locale::get_instance()->fmt_datetime($l_row[$l_lc_date]);

                $l_changes = unserialize($l_row[$l_lc_changes]);

                // Build change string
                if (isset($l_changes['C__OBJ__CMDB_STATUS']))
                {
                    $l_key = 'C__OBJ__CMDB_STATUS';
                }
                else
                {
                    $l_key = 'isys_cmdb_dao_category_g_global::cmdb_status';
                } // if

                $l_from               = $l_changes[$l_key]['from'] ?: isys_settings::get('gui.empty_value', '-');
                $l_to                 = $l_changes[$l_key]['to'] ?: isys_settings::get('gui.empty_value', '-');
                $l_row[$l_lc_changes] = $l_from . ' => ' . $l_to;

                $l_return['data'][] = $l_row;
            } // while

            $l_return['success'] = true;
        }
        else
        {
            $l_return['data'] = '<p class="pb10 pl10"><img src="' . $g_dirs['images'] . 'icons/infobox/blue.png" class="vam" /> ' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</p>';
        } // if

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);

        die;
    } // function

    public static function description()
    {
        return "LC__REPORT__VIEW__CMDB_CHANGES__DESCRIPTION";
    } // function

    public function init()
    {
        return true;
    } // function

    public static function name()
    {
        return "LC__REPORT__VIEW__CMDB_CHANGES__TITLE";
    } // function

    public function start()
    {
        isys_application::instance()->template
            ->assign('page_limit', isys_glob_get_pagelimit())
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1));
    } // function

    public function template()
    {
        return "view_cmdb_changes.tpl";
    } // function

    public static function type()
    {
        return self::c_php_view;
    } // function

    public static function viewtype()
    {
        return "LC__REPORT__VIEW_TYPE__CMDB_CHANGES";
    } // function
} // class