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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define("C__SUGGEST__MINIMUM_SEARCH__LENGTH", 3);
define("C__SUGGEST__POST_PARAMETER", "search");

class isys_ajax_handler_suggest extends isys_ajax_handler
{
    /**
     * Initialize the suggestion request.
     *
     * @global  isys_component_database $g_comp_database
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_database;

        $_POST    = $this->m_post;
        $_GET     = $this->m_get;
        $l_return = [];

        // Filter.
        $l_allowed_object_types = isys_popup_browser_object_ng::get_objecttype_filter(
            ((!empty($_POST[isys_popup_browser_object_ng::C__GROUP_FILTER])) ? explode(";", $_POST[isys_popup_browser_object_ng::C__GROUP_FILTER]) : []),
            ((!empty($_POST[isys_popup_browser_object_ng::C__TYPE_FILTER])) ? explode(";", $_POST[isys_popup_browser_object_ng::C__TYPE_FILTER]) : []),
            ((!empty($_POST[isys_popup_browser_object_ng::C__CAT_FILTER])) ? explode(";", $_POST[isys_popup_browser_object_ng::C__CAT_FILTER]) : []),
            ((!empty($_POST[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST])) ? explode(";", $_POST[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST]) : [])
        );

        $l_condition = '';

        if (isys_tenantsettings::get('auth.use-in-object-browser', false))
        {
            $l_condition = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();
        } // if

        if (isset($_POST[isys_popup_browser_object_ng::C__CMDB_FILTER]))
        {
            $l_status       = explode(';', $_POST[isys_popup_browser_object_ng::C__CMDB_FILTER]);
            $l_status_array = [];

            foreach ($l_status as $l_cmdb_status)
            {
                if (defined($l_cmdb_status))
                {
                    $l_status_array[] = (int) constant($l_cmdb_status);
                } // if
            } // foreach

            if (count($l_status_array) > 0)
            {
                $l_condition = ' AND isys_obj__isys_cmdb_status__id IN (' . implode(',', $l_status_array) . ') ';
            } // if
        } // if

        $l_allowed_object_types = array_flip($l_allowed_object_types);

        switch ($_GET["method"])
        {
            case "physical-logical-location":
                if (!empty($_POST["search"]) && isys_strlen($_POST[C__SUGGEST__POST_PARAMETER]) >= C__SUGGEST__MINIMUM_SEARCH__LENGTH)
                {

                    $l_dao     = new isys_cmdb_dao_category_g_logical_unit($g_comp_database);
                    $l_browser = new isys_popup_browser_location();

                    // SQL for retrieving objects by name which have the logical location category and are assigned.
                    $l_containers                         = $l_dao->search_located_objects_by_title($_POST[C__SUGGEST__POST_PARAMETER], true);
                    $l_object_types_workstations          = $l_dao->get_object_types_by_category(C__CATG__LOGICAL_UNIT, 'g', false);
                    $l_object_types_assigned_logical_unit = $l_dao->get_object_types_by_category(C__CATG__ASSIGNED_LOGICAL_UNIT, 'g', false);
                    $l_person_assigned_logical_unit       = $l_dao->get_object_types_by_category(C__CATG__PERSON_ASSIGNED_WORKSTATION, 'g', false);

                    $l_browser->set_format_str_cut(false)
                        ->set_format_object_name_cut(true)
                        ->set_format_exclude_self(false)
                        ->set_format_as_text(true);

                    while ($l_row = $l_containers->get_row())
                    {
                        if ($l_row["isys_obj__id"] > 0)
                        {
                            $l_object_id = $l_row["isys_obj__id"];

                            //($p_obj_id, $p_str_cut = false, $p_object_name_cut = 100, $p_exclude_self = false, $p_as_string = false)
                            $l_title = strip_tags($l_browser->format_selection($l_object_id));

                            // If we found no location path we'll just receive the object title.
                            if ($l_title == $l_row['isys_obj__title'])
                            {
                                // So we try to find the physical location of the objects parent.
                                $l_title = strip_tags($l_browser->format_selection($l_row["parent"]));

                                if (in_array($l_row['isys_obj__isys_obj_type__id'], $l_object_types_workstations) && in_array(
                                        $l_row['parent_objtype'],
                                        $l_person_assigned_logical_unit
                                    )
                                )
                                {
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['isys_obj__title'];
                                    $l_object_id = $l_row['isys_obj__id'];
                                }
                                elseif ($l_row['parent_parent'] > 0 && in_array($l_row['parent_parent_objtype'], $l_person_assigned_logical_unit) && in_array(
                                        $l_row['parent_objtype'],
                                        $l_object_types_assigned_logical_unit
                                    )
                                )
                                {
                                    // Retrieve person
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['parent_parent_title'];
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['parent_title'];
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['isys_obj__title'];
                                    $l_object_id = $l_row['isys_obj__id'];
                                }
                                else
                                {
                                    $l_object_id = $l_row["parent"];
                                } // if
                            } // if

                            if (empty($l_title))
                            {
                                $l_title = $l_row["isys_obj__title"];
                            } // if

                            if ($l_object_id > 0)
                            {
                                $l_return[] = '<li id="' . $l_object_id . '">' . $l_title . '</li>';
                            } // if
                        } // if
                    } // while
                } // if

            // NO break here!

            case "location":
                if (!empty($_POST["search"]) && isys_strlen($_POST[C__SUGGEST__POST_PARAMETER]) >= C__SUGGEST__MINIMUM_SEARCH__LENGTH)
                {
                    $l_dao        = new isys_cmdb_dao_category_g_location($g_comp_database);
                    $l_browser    = new isys_popup_browser_location();
                    $l_containers = $l_dao->get_container_objects(
                        $_POST[C__SUGGEST__POST_PARAMETER],
                        C__RECORD_STATUS__NORMAL,
                        !!isys_tenantsettings::get('auth.use-in-location-tree', false)
                    );

                    $l_browser->set_format_str_cut(false)
                        ->set_format_object_name_cut(true)
                        ->set_format_exclude_self(false)
                        ->set_format_as_text(true);

                    while ($l_row = $l_containers->get_row())
                    {
                        if ($l_row["isys_obj__id"] > 0)
                        {
                            $l_title = strip_tags($l_browser->format_selection($l_row["isys_obj__id"]));

                            if (empty($l_title))
                            {
                                $l_title = $l_row["isys_obj__title"];
                            } // if

                            $l_return[] = '<li id="' . $l_row["isys_obj__id"] . '">' . $l_title . '</li>';
                        } // if
                    } // while
                } // if
                $l_return = array_unique($l_return);
                break;

            case "object_with_no_type":
            case "object":
                if (!empty($_POST[C__SUGGEST__POST_PARAMETER]) && isys_strlen($_POST[C__SUGGEST__POST_PARAMETER]) >= C__SUGGEST__MINIMUM_SEARCH__LENGTH)
                {
                    $l_dao  = new isys_cmdb_dao_category_g_global($g_comp_database);
                    $l_data = $l_dao->search_objects($_POST[C__SUGGEST__POST_PARAMETER], $_POST["typeFilter"], $_POST["groupFilter"], $l_condition);

                    while ($l_row = $l_data->get_row())
                    {
                        if ($l_row["isys_obj__id"] > 0 && isset($l_allowed_object_types[$l_row['isys_obj_type__id']]))
                        {
                            $l_return_string = '<li id="' . $l_row["isys_obj__id"] . '" title="' . $l_row["isys_obj__title"] . ' (' . isys_glob_str_stop(
                                    _L($l_row["isys_obj_type__title"]),
                                    15
                                ) . ')">' . '<strong>' . isys_glob_str_stop($l_row["isys_obj__title"], 50) . '</strong> ';
                            if ($_GET["method"] != "object_with_no_type")
                            {
                                $l_return_string .= '(' . isys_glob_str_stop(_L($l_row["isys_obj_type__title"]), 15) . ')</li>';
                            }
                            else
                            {
                                $l_return_string = rtrim($l_return_string) . '</li>';
                            } // if
                            $l_return[] = $l_return_string;
                        } // if
                    } // while
                } // if
                break;

            case 'autotext':
                if (isset($_POST[C__SUGGEST__POST_PARAMETER]) && isys_strlen(
                        $_POST[C__SUGGEST__POST_PARAMETER]
                    ) >= C__SUGGEST__MINIMUM_SEARCH__LENGTH && isset($_POST[0]) && strlen($_POST[0]) > 0 && isset($_POST[1]) && strlen($_POST[1]) > 0
                )
                {
                    $l_query    = trim($_POST[C__SUGGEST__POST_PARAMETER]);
                    $l_source   = trim($_POST[0]);
                    $l_property = trim($_POST[1]);

                    $l_dao  = new isys_cmdb_dao($g_comp_database);
                    $l_data = $l_dao->get_autotext(
                        $l_query,
                        $l_source,
                        $l_property
                    );

                    if ($l_data->num_rows() > 0)
                    {
                        while ($l_row = $l_data->get_row())
                        {
                            $l_id       = $l_row[$l_source . '__id'];
                            $l_title    = $l_row[$l_source . '__' . $l_property];
                            $l_return[] = '<li id="' . $l_id . '" title="' . $l_title . '">' . $l_title . '</li>';
                        } // while
                    } // if
                } // if
                break;
        } // switch

        echo "<ul>" . implode('', $l_return) . "</ul>";
        $this->_die();
    } // function
} // class