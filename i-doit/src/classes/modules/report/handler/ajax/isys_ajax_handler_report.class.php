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
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-8
 */
class isys_ajax_handler_report extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        if (isset($_GET['func']))
        {
            $l_method = $_GET['func'];
            if (method_exists($this, $l_method))
            {
                $l_return = $this->$l_method();
            }
        }

        echo isys_format_json::encode($l_return);
        $this->_die();
    } // function

    /**
     * This method is used for the ajax pagination of the reports.
     *
     * @global  isys_component_database $g_comp_database_system
     * @global  isys_component_database $g_comp_database
     * @global  integer                 $g_page_limit
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_pager()
    {
        global $g_comp_database_system, $g_comp_database;

        $l_row = isys_report_dao::instance($g_comp_database_system)
            ->get_report($_GET['report_id']);

        $l_query = stripslashes($l_row["isys_report__query"]);

        // We use this DAO because here we defined how many pages we want to preload.
        $l_dao = isys_cmdb_dao_list_objects::instance($g_comp_database);

        // First we modify the SQL to find out, with how many rows we are dealing...
        $l_preloadable_rows = isys_glob_get_pagelimit() * $l_dao->get_preload_pages();
        $l_offset           = $l_preloadable_rows * $_POST['offset_block'];

        if (strpos($l_query, 'LIMIT'))
        {
            return [];
        } // if

        $l_query = rtrim($l_query, ';') . ' LIMIT ' . $l_offset . ', ' . $l_preloadable_rows . ';';

        return isys_module_report::get_instance()
            ->process_show_report($l_query, null, true);
    } // function

    /**
     * Method which deletes report categories
     *
     * @return array
     */
    protected function delete_report_category()
    {
        global $g_comp_database_system;

        $l_return = [
            'error'   => false,
            'message' => null
        ];

        /**
         * @var isys_report_dao
         */
        $l_report_dao = new isys_report_dao($g_comp_database_system);
        if (count($l_report_dao->get_reports_by_category($_POST['id'])) === 0)
        {
            $l_report_dao->delete_report_category($_POST['id']);
            $l_return['message'] = _L('LC__REPORT__POPUP__REPORT_CATEGORIES__CONFIRMATION_SUCCESS');
        }
        else
        {
            $l_return['error']   = true;
            $l_return['message'] = _L('LC__REPORT__POPUP__REPORT_CATEGORIES__CONFIRMATION_ERROR');
        } // if
        return $l_return;
    } // function

    protected function get_report_category()
    {
        global $g_comp_database_system;

        /**
         * @var isys_report_dao
         */
        $l_report_dao         = new isys_report_dao($g_comp_database_system);
        $l_report_category_id = $_POST['id'];

        return current($l_report_dao->get_report_categories($l_report_category_id));
    } // function

    /**
     * Method to retrieve all the categories.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function add_division()
    {
        $l_return = [
            'error'   => false,
            'message' => null
        ];

        $l_dao = new isys_cmdb_dao($this->m_database_component);

        $l_blacklist_categories = [
            'C__CATS__NET_IP_ADDRESSES'
        ];

        try
        {
            $l_sql = "SELECT child.isysgui_catg__title, child.isysgui_catg__id, child.isysgui_catg__const, parent.isysgui_catg__title AS parent " . "FROM isys_property_2_cat " . "INNER JOIN isysgui_catg AS child ON isys_property_2_cat__isysgui_catg__id = child.isysgui_catg__id " . "LEFT JOIN isysgui_catg AS parent ON parent.isysgui_catg__id = child.isysgui_catg__parent " . "WHERE isys_property_2_cat__prop_provides & " . C__PROPERTY__PROVIDES__REPORT . " " . "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " " . "GROUP BY isysgui_catg__id;";

            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row())
            {
                if (!in_array($l_row['isysgui_catg__const'], $l_blacklist_categories))
                {
                    $l_title = _L($l_row['isysgui_catg__title']);
                    if ($l_row['parent'] !== null)
                    {
                        $l_title .= ' (' . _L($l_row['parent']) . ')';
                    }
                    $l_return['data']['catg'][$l_row['isysgui_catg__const']] = $l_title;
                } // if
            } // while

            $l_sql = "SELECT isysgui_cats__id, isysgui_cats__title, isysgui_cats__const FROM isys_property_2_cat " . "INNER JOIN isysgui_cats ON isys_property_2_cat__isysgui_cats__id = isysgui_cats__id " . "WHERE isys_property_2_cat__prop_provides & " . C__PROPERTY__PROVIDES__REPORT . " " . "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " " . "GROUP BY isysgui_cats__id;";
            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row())
            {
                if (!in_array($l_row['isysgui_cats__const'], $l_blacklist_categories))
                {
                    $l_parent     = '';
                    $l_parent_arr = [];
                    // Check parent
                    $l_check_parent_sql = 'SELECT isysgui_cats__title, isysgui_cats__id FROM isysgui_cats ' . 'INNER JOIN isysgui_cats_2_subcategory ON isysgui_cats_2_subcategory__isysgui_cats__id__parent = isysgui_cats__id ' . 'WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child = ' . $l_dao->convert_sql_id(
                            $l_row['isysgui_cats__id']
                        );

                    $l_res2 = $l_dao->retrieve($l_check_parent_sql);
                    if (count($l_res2) > 0)
                    {
                        $l_parent_arr = [];

                        while ($l_row2 = $l_res2->get_row())
                        {
                            $l_check_objtypes = 'SELECT isys_obj_type__title FROM isys_obj_type WHERE isys_obj_type__isysgui_cats__id = ' . $l_dao->convert_sql_id(
                                    $l_row2['isysgui_cats__id']
                                );
                            $l_res3           = $l_dao->retrieve($l_check_objtypes);

                            while ($l_row3 = $l_res3->get_row())
                            {
                                $l_title                = _L($l_row3['isys_obj_type__title']);
                                $l_parent_arr[$l_title] = $l_title;
                            } // while
                        } // while
                    }
                    else
                    {
                        $l_check_objtypes = 'SELECT isys_obj_type__title FROM isys_obj_type WHERE isys_obj_type__isysgui_cats__id = ' . $l_dao->convert_sql_id(
                                $l_row['isysgui_cats__id']
                            );
                        $l_res3           = $l_dao->retrieve($l_check_objtypes);

                        while ($l_row3 = $l_res3->get_row())
                        {
                            $l_title                = _L($l_row3['isys_obj_type__title']);
                            $l_parent_arr[$l_title] = $l_title;
                        } // while
                    } // if
                    if (count($l_parent_arr) > 0)
                    {
                        $l_parent = ' (' . implode(', ', $l_parent_arr) . ')';
                    } // if

                    $l_return['data']['cats'][$l_row['isysgui_cats__const']] = _L($l_row['isysgui_cats__title']) . $l_parent;
                } // if
            } // while

            $l_sql = "SELECT isysgui_catg_custom__id, isysgui_catg_custom__title, isysgui_catg_custom__const FROM isys_property_2_cat " . "INNER JOIN isysgui_catg_custom ON isys_property_2_cat__isysgui_catg_custom__id = isysgui_catg_custom__id " . "WHERE isys_property_2_cat__prop_provides & " . C__PROPERTY__PROVIDES__REPORT . " " . "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " " . "GROUP BY isysgui_catg_custom__id;";
            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row())
            {
                $l_return['data']['catg_custom'][$l_row['isysgui_catg_custom__const']] = _L($l_row['isysgui_catg_custom__title']);
            } // while

            if (is_array($l_return['data']['catg']))
            {
                asort($l_return['data']['catg']);
                $l_return['data']['catg'] = array_flip($l_return['data']['catg']);
            } // if

            if (is_array($l_return['data']['cats']))
            {
                asort($l_return['data']['cats']);
                $l_return['data']['cats'] = array_flip($l_return['data']['cats']);
            } // if

            if (is_array($l_return['data']['catg_custom']))
            {
                asort($l_return['data']['catg_custom']);
                $l_return['data']['catg_custom'] = array_flip($l_return['data']['catg_custom']);
            }
        }
        catch (Exception $e)
        {
            $l_return['error']   = true;
            $l_return['message'] = $e->getMessage();
        }

        return $l_return;
    } // function

    /**
     * Method to retrieve the properties of a given category.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function add_property_selection_to_division()
    {
        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);

        $l_return = [
            'error'   => false,
            'data'    => null,
            'message' => null
        ];

        if (defined($_POST['cat_id']))
        {
            $l_category_info = $l_dao->get_cat_by_const($_POST['cat_id']);
            $l_catg          = null;
            $l_cats          = null;
            $l_catg_custom   = null;

            switch ($l_category_info['type'])
            {
                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    $l_catg = constant($_POST['cat_id']);
                    break;
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $l_cats = constant($_POST['cat_id']);
                    break;
                case C__CMDB__CATEGORY__TYPE_CUSTOM:
                    $l_catg_custom = constant($_POST['cat_id']);
                    break;
            }
            $l_res = $l_dao->retrieve_properties(null, $l_catg, $l_cats, C__PROPERTY__PROVIDES__REPORT, "", false, $l_catg_custom);
        }
        else
        {
            $l_return['error']   = true;
            $l_return['message'] = "Constant '" . $_POST['cat_id'] . "' is not defined.";

            return $l_return;
        }

        try
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return['data'][$l_row['const'] . '-' . $l_row['key']] = _L($l_row['title']);
            } // while
            if (is_array($l_return['data']))
            {
                asort($l_return['data']);
            } // if
        }
        catch (Exception $e)
        {
            $l_return['error']   = true;
            $l_return['message'] = $e->getMessage();
        } // try

        return $l_return;
    } // function

    /**
     * Method for retrieving the options to a given property.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function add_contraint_to_property()
    {
        global $g_comp_template;
        $l_return        = $l_ui_params = [];
        $l_load_field    = true;
        $l_special_field = false;
        $l_condition     = '';

        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);

        $l_prop_id = null;

        if (is_numeric($_POST['prop_id']))
        {
            $l_prop_id   = $_POST['prop_id'];
            $l_condition = '';
        }
        elseif (strpos($_POST['prop_id'], '-'))
        {
            $l_prop_info = explode('-', $_POST['prop_id']);
            $l_condition = ' AND isys_property_2_cat__cat_const = ' . $l_dao->convert_sql_text(
                    $l_prop_info[0]
                ) . ' AND isys_property_2_cat__prop_key = ' . $l_dao->convert_sql_text($l_prop_info[1]);
        }

        $l_row = $l_dao->retrieve_properties($l_prop_id, null, null, null, $l_condition)
            ->get_row();

        $l_return['special_field'] = null;

        $l_cat_dao    = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
        $l_properties = $l_cat_dao->get_properties();
        $l_props      = $l_properties[$l_row['key']];

        $l_popup_types = [
            'browser_object_ng',
            'browser_location',
            'browser_object_relation',
            'browser_cable_connection_ng',
            'browser_file',
            'browser_sanpool'
        ];

        $l_identifier = $l_row['class'] . '::' . $l_row['key'];

        if (isset($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] != C__PROPERTY__INFO__TYPE__OBJECT_BROWSER)
        {
            if ((strpos($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false || strpos(
                        $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                        'cats'
                    ) !== false) && strpos($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], '_list') !== false
            )
            {
                $l_special_field = true;
            } // if
        } // if

        $_POST['division'] = str_replace('__HIDDEN', '', $_POST['division']);
        // We check for special formats to
        if ($l_props[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE || $l_props[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATETIME)
        {
            $l_cal         = new isys_popup_calendar();
            $l_cat_options = [
                'name'              => $_POST['division'],
                'p_bEditMode'       => true,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'reportInput ' . $_POST['prop_class'],
                'p_strStyle'        => 'width:140px;',
                'p_strValue'        => $_POST['value'],
                'p_dataIdentifier'  => $l_identifier
            ];

            $l_return['special_field'] = $l_cal->handle_smarty_include($g_comp_template, $l_cat_options);
            $l_load_field              = false;
        }
        else if (($l_props[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP && in_array(
                    $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'],
                    $l_popup_types
                )) && !$l_special_field
        )
        {
            // Get the ui params.
            $l_ui_params = $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];

            if (isset($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection']))
            {
                $l_multiselection = (bool) $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'];
            }
            else
            {
                $l_multiselection = false;
            }

            if (isset($l_ui_params['secondSelection']) || $l_ui_params['p_strPopupType'] == 'browser_sanpool')
            {
                $l_ui_params['p_strPopupType'] = 'browser_object_ng';
                unset($l_ui_params['secondSelection']);
                $l_multiselection = false;
            }

            $l_ui_params['name']                                     = $_POST['division'];
            $l_ui_params['p_strSelectedID']                          = $_POST['value'];
            $l_ui_params['p_strValue']                               = $_POST['value'];
            $l_ui_params['p_bInfoIconSpacer']                        = 0;
            $l_ui_params['p_bEditMode']                              = true;
            $l_ui_params['edit']                                     = true;
            $l_ui_params['p_strStyle']                               = 'width:140px;';
            $l_ui_params['p_strClass']                               = 'reportInput ' . $_POST['prop_class'];
            $l_ui_params[isys_popup_browser_object_ng::C__EDIT_MODE] = true;
            if ($l_ui_params['p_strPopupType'] != 'browser_object_relation')
            {
                $l_ui_params[isys_popup_browser_object_ng::C__MULTISELECTION] = $l_multiselection;
            }
            $l_ui_params[isys_popup_browser_object_ng::C__DISABLE_DETACH] = false;
            $l_ui_params['p_dataIdentifier']                              = $l_identifier;
            //$l_ui_params['p_dataIdentifier'] = '';

            unset($l_ui_params[isys_popup_browser_object_ng::C__DATARETRIEVAL]);
            unset($l_ui_params[isys_popup_browser_object_ng::C__FORM_SUBMIT]);
            unset($l_ui_params[isys_popup_browser_object_ng::C__RETURN_ELEMENT]);

            $l_popup_class = "isys_popup_" . $l_ui_params['p_strPopupType'];
            if (class_exists($l_popup_class))
            {
                $l_popup = new $l_popup_class();

                $l_return['special_field'] = $l_popup->handle_smarty_include($g_comp_template, $l_ui_params);
                $l_load_field              = false;
            } // if
        } // if

        if ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__COMMENTARY)
        {
            $l_return['equation'] = [
                'LIKE %...%',
                'NOT LIKE %...%'
            ];
            $l_return['field']    = null;
        }
        else if ((($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == null || substr(
                        $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                        0,
                        5
                    ) != 'isys_') && empty($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) && !in_array(
                    $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'],
                    $l_popup_types
                )) || $l_special_field || in_array(
                $l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1],
                isys_cmdb_dao_category_property::$m_ignored_format_callbacks
            )
        )
        {
            $l_return['equation'] = [
                '=',
                '&lt;',
                '&gt;',
                '!=',
                '&lt;=',
                '&gt;=',
                'LIKE',
                'LIKE %...%',
                'NOT LIKE',
                'NOT LIKE %...%'
            ];
            $l_return['field']    = null;
        }
        else if ($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection' && empty($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
        {
            $l_return['equation'] = [
                '=',
                '!=',
                'subcnd'
            ];
        }
        else
        {
            $l_return['equation'] = [
                '=',
                '!='
            ];
            $l_data               = null;

            if (!empty($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
            {
                if (is_array($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                {
                    // If we simply get an array.
                    $l_data = $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                }
                else if (is_object($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) && get_class(
                        $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']
                    ) == 'isys_callback'
                )
                {
                    // If we get an instance of "isys_callback"
                    $l_data = $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
                }
                else if (is_string($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                {
                    // Or if we get a string (we assume it's serialized).
                    $l_data = unserialize($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']);
                } // if
            } // if

            // @todo Special treatment for the stupid IP addresses... We need to fix this generically!
            if ($l_load_field)
            {
                if((!isset($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bDbFieldNN']) ||
                    $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bDbFieldNN'] == 0) &&
                    ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG ||
                        $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_PLUS))
                {
                    $l_return['field'] = [' ' => isys_settings::get('gui.empty_values')];
                }
                else
                {
                    $l_return['field'] = [];
                } // if

                if ($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null && $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] != 'isys_cats_net_ip_addresses_list' && $l_data === null)
                {
                    // Prepare array, so we can check this in the GUI.
                    $l_sql             = "SELECT " . $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id AS 'id', " . $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__title AS 'title' FROM " . $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . ";";
                    $l_field_res       = $l_dao->retrieve($l_sql);

                    while ($l_field_row = $l_field_res->get_row())
                    {
                        $l_return['field'][$l_field_row['id'] . ' '] = _L($l_field_row['title']);
                    } // while
                }
                else if (is_array($l_data))
                {
                    if (count($l_data) > 0)
                    {
                        foreach ($l_data AS $l_key => $l_val)
                        {
                            if (is_array($l_val))
                            {
                                foreach ($l_val AS $l_key2 => $l_val2)
                                {
                                    $l_return['field'][$l_key2 . ' '] = _L($l_val2);
                                }
                            }
                            else
                            {
                                $l_return['field'][$l_key . ' '] = _L($l_val);
                            }
                        } // foreach
                    } // if
                } // if

                if (!empty($l_return['field']) && is_array($l_return['field']))
                {
                    asort($l_return['field']);
                } // if
            } // if

            if ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER)
            {
                // Special equation for category location
                if ($l_ui_params['p_strPopupType'] == 'browser_location')
                {
                    $l_return['equation'][] = 'under_location';
                } // if
                $l_return['equation'][] = 'subcnd';
            } // if
        } // if

        // Check if we got a convert method to apply.
        if ($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert' || isset($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]))
        {
            // We need to get the unit information.
            if ($l_row['catg'] != null)
            {
                // We have to select from CATG.
                $l_unit_row = $l_dao->retrieve_properties(
                    null,
                    $l_row['catg'],
                    null,
                    null,
                    "AND isys_property_2_cat__prop_key = " . $l_dao->convert_sql_text($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])
                )
                    ->get_row();
            }
            else
            {
                // We have to select from CATS.
                $l_unit_row = $l_dao->retrieve_properties(
                    null,
                    null,
                    $l_row['cats'],
                    null,
                    "AND isys_property_2_cat__prop_key = " . $l_dao->convert_sql_text($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])
                )
                    ->get_row();
            } // if

            $l_cat_dao    = $l_dao->get_dao_instance($l_unit_row['class'], ($l_unit_row['catg_custom'] ?: null));
            $l_properties = $l_cat_dao->get_properties();
            $l_unit_props = $l_properties[$l_unit_row['key']];

            $l_table         = $l_unit_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0];
            $l_unit_property = $l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

            if (!empty($l_table))
            {
                $l_sql      = "SELECT " . $l_table . "__id AS id, " . $l_table . "__title AS title FROM " . $l_table . " ORDER BY " . $l_table . "__sort ASC;";
                $l_unit_res = $l_dao->retrieve($l_sql);

                while ($l_unit_row = $l_unit_res->get_row())
                {
                    $l_return['unit'][$l_unit_row['id'] . '-' . $l_unit_property] = _L($l_unit_row['title']);
                } // while
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Method for "checking" if a report will work and find objects.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function check_report()
    {
        global $g_comp_database;

        $l_conditions = isys_format_json::decode($_POST['condition']);

        // We have to "simulate" the data comes from $_POST to use the "create_property_query_for_report()" method.
        if (is_array($l_conditions))
        {
            foreach ($l_conditions as $l_field => $l_value)
            {
                $_POST[$l_field] = $l_value;
            } // foreach
        }

        try
        {
            $l_dao = new isys_cmdb_dao_category_property($g_comp_database);
            $l_sql = $l_dao->create_property_query_for_report(5);

            $l_return = [
                'error'   => false,
                'message' => _L(
                    'LC__REPORT__FORM__CHECK_NOTE',
                    [
                        $l_dao->retrieve($l_sql)
                            ->num_rows()
                    ]
                )
            ];
        }
        catch (Exception $e)
        {
            $l_return = [
                'error'   => true,
                'message' => _L('LC__REPORT__FORM__CHECK_ERROR')
            ];
        } // try

        return $l_return;
    } // function
} // class