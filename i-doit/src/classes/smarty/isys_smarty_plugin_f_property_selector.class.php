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
 * Smarty plugin for the property selector.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_property_selector extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_meta_map()
    {
        return [];
    } // function

    /**
     * Method for view-mode.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        // If we force the edit mode, display it.
        if (isys_glob_is_edit_mode() || $p_param['p_bEditMode'])
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        if (isset($p_param['preselection']) && isys_format_json::is_json($p_param['preselection']))
        {
            $l_return       = [];
            $l_preselection = $this->handle_preselection(isys_format_json::decode($p_param['preselection'], true));

            foreach ($l_preselection as $l_prop)
            {
                $l_return[] = $l_prop['cat_title'] . ' &raquo; ' . $l_prop['prop_title'];
            } // foreach

            return $this->getInfoIcon($p_param) . implode(', ', $l_return);
        } // if

        return $this->getInfoIcon($p_param) . '-';
    } // function

    /**
     * Method for edit-mode.
     *
     * List of usable parameters for $p_param:
     *     name               -> The name of the plugin, will also be used as ID and for some javascript variables (Default: "default").
     *     preselection       -> The plugin only handles JSON data (default: "").
     *     dynamic_properties -> Define a limit of selectable items (Default: 38).
     *     max_items          -> Define a limit of selectable items (Default: 38).
     *     grouping           -> Define if the selected properties shall be grouped (Default: true).
     *     sortable           -> Define if the selected properties shall be sortable (Default: false).
     *       selector_size      -> Defines the size of the property selector (Default: 'normal').
     *     obj_type_id        -> If given, only categories which are inherited by the object type will be displayed (Default: none, Example: 5).
     *     provide            -> Define which properties shall be displayed via BITwise selection (Default: none, Example: (C__PROPERTY__PROVIDES__LIST^C__PROPERTY__PROVIDES__REPORT)).
     *     p_bEditMode        -> Force the edit mode.
     *     p_bInfoIconSpacer  -> When given 0 or boolean "false" the info icon will not be displayed.
     *     p_strStyle         -> Set a custom style to the surrounding DIV element.
     *     searchable         -> Activate/Deactivate search
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $l_js_params         = [];
        $l_preselection      = null;
        $l_consider_rights   = false;
        $l_preselection_lvls = null;

        $l_dialog_plugin = new isys_smarty_plugin_f_dialog();

        // If edit mode is inactive, we call the navigation view.
        if (!isys_glob_is_edit_mode() && !$p_param['p_bEditMode'])
        {
            return $this->navigation_view($p_tplclass, $p_param);
        } // if

        if ($p_param['obj_type_id'] > 0)
        {
            $l_js_params['obj_type_id'] = $p_param['obj_type_id'];
        } // if

        // Set a default name, when no name was given.
        if (!isset($p_param['name']))
        {
            $p_param['name'] = 'default';
        } // if

        if (isset($p_param['searchable']))
        {
            $l_js_params['searchable'] = (bool) $p_param['searchable'];
        }
        else
        {
            $p_param['searchable'] = $l_js_params['searchable'] = true;
        } // if

        if (isset($p_param['p_consider_rights']))
        {
            $l_consider_rights              = $p_param['p_consider_rights'];
            $l_js_params['consider_rights'] = (bool) $l_consider_rights;
        } // if

        if (isset($p_param['p_strStyle']))
        {
            $p_param['p_strStyle'] = 'width:850px;' . $p_param['p_strStyle'];
        }
        else if (!isset($p_param['selector_size']))
        {
            $p_param['p_strStyle'] = 'width:850px;';
        } // if

        // Check for the "max_items" parameter.
        if (isset($p_param['max_items']))
        {
            $l_js_params['max_items'] = (int) $p_param['max_items'];
        } // if

        // Check for the "grouping" parameter.
        if (isset($p_param['grouping']))
        {
            $l_js_params['group'] = (bool) $p_param['grouping'];
        } // if

        // Check for the "sortable" parameter.
        if (isset($p_param['sortable']))
        {
            $l_js_params['sortable'] = (bool) $p_param['sortable'];
        } // if

        // Set a default name, when no name was given.
        if (isset($p_param['dynamic_properties']))
        {
            $l_js_params['dynamic_properties'] = $p_param['dynamic_properties'];
        } // if

        if (isset($p_param['replace_dynamic_properties']))
        {
            $l_js_params['replace_dynamic_properties'] = $p_param['replace_dynamic_properties'];
        } // if

        if (isset($p_param['selector_size']))
        {
            switch ($p_param['selector_size'])
            {
                case 'small':
                    $l_min_width        = '305px;';
                    $l_select_box_class = 'small ';
                    break;
                case 'normal':
                default:
                    $l_min_width        = '400px;';
                    $l_select_box_class = 'reportDialog ';
                    break;
            }
            $l_js_params['selector_size'] = $p_param['selector_size'];
        }
        else
        {
            $l_min_width        = '400px;';
            $l_select_box_class = 'reportDialog ';
        }

        // Assign the property "provide", if given.
        if (isset($p_param['provide']))
        {
            $l_js_params['provides'] = $p_param['provide'];
        } // if

        if (isset($p_param['report']))
        {
            $l_js_params['report'] = (bool) $p_param['report'];
        } // if

        if (isset($p_param['custom_fields']))
        {
            $l_js_params['custom_fields'] = (bool) $p_param['custom_fields'];
        } // if

        // Check for preselection data. Only JSON (as string) is allowed!
        if (isset($p_param['preselection']) && isys_format_json::is_json($p_param['preselection']))
        {
            $l_preselection = $this->handle_preselection(isys_format_json::decode($p_param['preselection'], true));
        } // if

        if (isset($p_param['preselection_lvls']) && $p_param['preselection_lvls'] !== 'null')
        {
            if (!isys_format_json::is_json($p_param['preselection_lvls']))
            {
                $l_preselection_lvls = isys_format_json::encode($p_param['preselection_lvls']);
            }
            else
            {
                $l_preselection_lvls = $p_param['preselection_lvls'];
            } // if
        } // if

        // Option if properties shall be sortable or not.
        if (isset($p_param['check_sorting']))
        {
            $l_js_params['check_sorting'] = $p_param['check_sorting'];
        } // if

        // Option for chosen dialog width
        if (isset($p_param['dialog_width']))
        {
            $l_js_params['dialog_width'] = $p_param['dialog_width'];
        } // if

        // Category lists
        $l_catg_options = $this->get_catg($p_param['provide'], $p_param['dynamic_properties'], $l_consider_rights, $p_param['obj_type_id']);
        $l_cats_options = $this->get_cats($p_param['provide'], $p_param['dynamic_properties'], $l_consider_rights, $p_param['obj_type_id']);

        $l_dialog_params = [
            'p_bEditMode'       => $p_param['p_bEditMode'],
            'p_arData'          => $l_catg_options,
            'chosen'            => true,
            'p_bSort'           => true,
            'name'              => $p_param['name'] . '_catg_selection',
            'p_strClass'        => $l_select_box_class,
            'p_bInfoIconSpacer' => 0,
            'width'             => $l_min_width
        ];

        $l_dialog_global             = $l_dialog_plugin->navigation_edit($p_tplclass, $l_dialog_params);
        $l_dialog_params['p_arData'] = $l_cats_options;
        $l_dialog_params['name']     = $p_param['name'] . '_cats_selection';
        $l_dialog_specific           = $l_dialog_plugin->navigation_edit($p_tplclass, $l_dialog_params);

        // Custom categories only in report query builder
        if ($l_js_params['custom_fields'])
        {
            $l_catg_custom_options       = $this->get_catg_custom($p_param['provide'], $p_param['dynamic_properties'], $l_consider_rights, $p_param['obj_type_id']);
            $l_dialog_params['p_arData'] = $l_catg_custom_options;
            $l_dialog_params['name']     = $p_param['name'] . '_catg_custom_selection';
            $l_dialog_custom             = $l_dialog_plugin->navigation_edit($p_tplclass, $l_dialog_params);

            $l_catg_custom_div = '<div class="m10" id="' . $p_param['name'] . '_catg_custom_list" style="display:none;">' . $l_dialog_custom . '

					<div id="' . $p_param['name'] . '_catg_custom_properties"></div>
				</div>';
            $l_catg_custom_li  = '<li><a href="#' . $p_param['name'] . '_catg_custom_list">' . _L('LC__CMDB__CATG__CUSTOM') . '</a></li>';
        }
        else
        {
            $l_catg_custom_div = '';
            $l_catg_custom_li  = '';
        } // if

        // We prepare the hidden field.
        $l_hidden = '<input type="hidden" name="' . $p_param['name'] . '__HIDDEN" id="' . $p_param['name'] . '__HIDDEN" value="" />' . '<input type="hidden" name="' . $p_param['name'] . '__HIDDEN_IDS" id="' . $p_param['name'] . '__HIDDEN_IDS" value="" />' . '<input type="hidden" name="' . $p_param['name'] . '__COMPLETE" id="' . $p_param['name'] . '__COMPLETE" value="" />';

        $l_left_box = '<tr><td style="vertical-align:top"><div style="width:' . $l_min_width . '; background:#fff;" class="border fl mr20">
				<ul class="m0 gradient browser-tabs" style="position:relative" id="' . $p_param['name'] . '_tabs">
					<li><a href="#' . $p_param['name'] . '_catg_list">' . _L('LC__UNIVERSAL__GLOBAL') . '</a></li>
					<li><a href="#' . $p_param['name'] . '_cats_list">' . _L('LC__UNIVERSAL__SPECIFIC') . '</a></li>
					' . $l_catg_custom_li . (($p_param['searchable']) ? '<li style="float: right; position:absolute;right:5px;" class="mr5">
                        	<a href="#' . $p_param['name'] . '_search" id="' . $p_param['name'] . '_search_tab">
                                <img src="images/icons/silk/magnifier.png" class="greyscale vam">
                                <div id="' . $p_param['name'] . '_search_controls" style="margin-top:1px;display:none;">
                                    <input type="text" id="' . $p_param['name'] . '_search_input" placeholder="' . _L('LC__PROPERTY_SELECTOR__SEARCH_IN_PROPERTIES') . '" class="input input-mini" style="height:20px;"/>
                                    <button type="button" id="' . $p_param['name'] . '_search_button" class="btn" disabled="disabled" style="margin-left: -4px; height:20px; padding-top:0;padding-bottom:0;">
                                        <img src="images/icons/silk/magnifier.png" class="greyscale">
                                        <img src="images/ajax-loading.gif"         class="greyscale" style="display:none">
                                    </button>
                                </div>
                            </a>
                        </li>' : '') . '
				</ul>
				<div class="m10" id="' . $p_param['name'] . '_catg_list">
					' . $l_dialog_global . '
					<div id="' . $p_param['name'] . '_catg_properties"></div>
				</div>
				<div class="m10" id="' . $p_param['name'] . '_cats_list" style="display:none;">
					' . $l_dialog_specific . '
					<div id="' . $p_param['name'] . '_cats_properties"></div>
				</div>' . $l_catg_custom_div . '<div class="m10" id="' . $p_param['name'] . '_search" style="display:none;">
				    <!--<input type="text" id="' . $p_param['name'] . '_search_input"  class="input input-small mr5" placeholder="' . _L(
                'LC__PROPERTY_SELECTOR__SEARCH_IN_PROPERTIES'
            ) . '"/>
				    <button type="button" id="' . $p_param['name'] . '_search_button" class="btn" disabled="disabled">
				        <img src="images/icons/silk/magnifier.png" >
				        <img src="images/ajax-loading.gif" style="display:none">
                    </button>-->
				    <div id="' . $p_param['name'] . '_search_result_container"></div>
                    <div id="' . $p_param['name'] . '_search_count_container" class="green mt10" style="display:none;">
                        <img src="images/icons/infobox/blue.png" class="vam">
                        <span id="' . $p_param['name'] . '_search_count"></span> ' . _L('LC__PROPERTY_SELECTOR__ATTRIBUTES_FOUND') . '
                    </div>
				</div>
				<div class="error m10 p5" id="' . $p_param['name'] . '_attr_error" style="display:none;"></div>
			</div></td>';

        $l_right_box = '<td style="vertical-align:top"><div style="width:' . $l_min_width . '; background:#fff;" class="border fl">' . '<div class="m0 p10 gradient browser-tabs" style="height:15px; border-bottom:1px solid #888;">' . '<span class="text-shadow"><b style="color:#333333; font-size:10px;">' . _L(
                'LC__REPORT__INFO__CHOSEN_PROPERTIES_TEXT'
            ) . '</b></span>';

        if (isset($p_param['allowed_property_types']))
        {
            if (is_string($p_param['allowed_property_types']))
            {
                $p_param['allowed_property_types'] = isys_format_json::decode($p_param['allowed_property_types']);
            } // if

            $l_js_params['allowed_prop_types'] = $p_param['allowed_property_types'];
        } // if

        if (isset($p_param['default_sorting']))
        {
            $l_js_params['default_sorting'] = $p_param['default_sorting'];

            $l_right_box = '<td style="vertical-align:top;"><div style="width:' . $l_min_width . '; background:#fff;" class="border fl">' . '<div class="m0 p10 gradient browser-tabs" style="height:15px; border-bottom:1px solid #888;">' . '<span class="text-shadow"><b style="color:#333333; font-size:10px;">' . _L(
                    'LC__REPORT__INFO__CHOSEN_PROPERTIES_TEXT'
                ) . '</b></span>' . '<span class="text-shadow" style="float:right;"><b style="color:#333333; font-size:10px;">' . _L(
                    'LC__REPORT__INFO__DEFAULT_SORTING'
                ) . '</b></span>';
        } // if

        $l_right_box .= '</div>' . '<div class="m10" id="' . $p_param['name'] . '_selection_field">' . '<div id="' . $p_param['name'] . '_attribute_remover" class="category-field">' . _L(
                'LC__REPORT__NO_ATTRIBUTES_ADDED'
            ) . '</div>' . '</div>' . '</div></td><tr id="spacer_lvl_1" style="min-width:30px;" class="selector-spacer"></tr>';

        $l_html = $this->getInfoIcon(
                $p_param
            ) . '<div><div class="property-selector" style="position:relative;"><table style="' . $p_param['p_strStyle'] . '">' . $l_left_box . $l_right_box . '</table></div></div>';

        $l_javascript = "<script type=\"text/javascript\">
			idoit.Translate.set('LC__UNIVERSAL__GLOBAL', '" . _L('LC__UNIVERSAL__GLOBAL') . "');
			idoit.Translate.set('LC__UNIVERSAL__SPECIFIC', '" . _L('LC__UNIVERSAL__SPECIFIC') . "');
			idoit.Translate.set('LC__CMDB__CATG__CUSTOM', '" . _L('LC__CMDB__CATG__CUSTOM') . "');
			idoit.Translate.set('LC__REPORT__NO_ATTRIBUTES_FOUND', '" . _L('LC__REPORT__NO_ATTRIBUTES_FOUND') . "');
			idoit.Translate.set('LC__REPORT__FORM__ATTRIBUTE_COUNT_ERROR', '" . _L('LC__REPORT__FORM__ATTRIBUTE_COUNT_ERROR') . "');
			idoit.Translate.set('LC__REPORT__INFO__CHOSEN_PROPERTIES_TEXT', '" . _L('LC__REPORT__INFO__CHOSEN_PROPERTIES_TEXT') . "');
			idoit.Translate.set('LC__REPORT__NO_ATTRIBUTES_ADDED', '" . _L('LC__REPORT__NO_ATTRIBUTES_ADDED') . "');

			var " . $p_param['name'] . " = new PropertySelector('" . $p_param['name'] . "', " . isys_format_json::encode($l_js_params) . ");

			" . (($l_preselection !== null) ? $p_param['name'] . '.handle_preselection(' . isys_format_json::encode($l_preselection) . ');' : '') . "

			" . (($l_preselection_lvls !== null) ? $p_param['name'] . '.handle_preselection_lvls(' . $l_preselection_lvls . ');' : '') . "

			if ($('" . $p_param['name'] . "_tabs')) {
				new Tabs('" . $p_param['name'] . "_tabs', {
					wrapperClass: 'browser-tabs',
					contentClass: 'browser-tab-content',
					tabClass: 'text-shadow',
					onTabSelect: function(el) {
                        //return;
                        if (el.getAttribute('href') == '#" . $p_param['name'] . "_search' && !$('" . $p_param['name'] . "_search_controls').visible()) {
                            el.select('img.greyscale')[0].hide();
                            //el.childElements()[1].show();
                            $('" . $p_param['name'] . "_search_controls').show();
                            $$('#" . $p_param['name'] . "_search_controls input').invoke('focus');
                        } else if (el.getAttribute('href') != '#" . $p_param['name'] . "_search' && $('" . $p_param['name'] . "_search_controls')) {
                            $('" . $p_param['name'] . "_search_controls').hide();
                            $('" . $p_param['name'] . "_search_tab').select('img.greyscale')[0].show();
                        }
					}
				});
			}";

        // Sets Callback for the add button
        if (isset($p_param['callback_add']))
        {
            $l_javascript .= 'PropertySelector.addMethods({
				callback_add: function() {
					' . $p_param['callback_add'] . '();
				}
			}); ';
        } // if

        // Sets Callback for the add button
        if (isset($p_param['callback_remove']))
        {
            $l_javascript .= 'PropertySelector.addMethods({
				callback_remove: function() {
					' . $p_param['callback_remove'] . '();
				}
			}); ';
        } // if

        $l_javascript .= "</script>";

        return $l_html . $l_hidden . $l_javascript . $this->attach_wiki($p_param);
    } // function

    /**
     * Method for returning the "option"-elements with all the global categories.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_provides
     * @param   boolean                 $p_dynamic_properties
     * @param   boolean                 $p_consider_rights
     * @param   integer                 $p_obj_type_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_catg($p_provides, $p_dynamic_properties, $p_consider_rights = true, $p_obj_type_id = null)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

        $l_obj_type_id = false;
        $l_categories  = $l_catg = $l_obj_type_catg = $l_return = [];

        // Retrieve all categories, the current user may see.
        if ($p_consider_rights)
        {
            // This IF-block solves #5139
            if ($p_obj_type_id !== null)
            {
                $l_obj_type_const = $p_obj_type_id;

                if (is_numeric($p_obj_type_id))
                {
                    $l_obj_type = $l_dao->get_object_type($p_obj_type_id);

                    $l_obj_type_const = $l_obj_type['isys_obj_type__const'];
                } // if

                $l_categories = isys_auth_cmdb_categories::instance()
                    ->get_allowed_categories_by_obj_type($l_obj_type_const);

                if (in_array(isys_auth::WILDCHAR, $l_categories))
                {
                    $l_categories = true;
                } // if
            }
            else
            {
                $l_categories = isys_auth_cmdb_categories::instance()
                    ->get_allowed_categories();
            } // if

            // In case of a wildchar, we can just deactivate the upcoming checks.
            if ($l_categories === true)
            {
                $p_consider_rights = false;
            } // if

            // If the user has no categories, we can just return an empty array.
            if ($l_categories === false)
            {
                return $l_return;
            } // if
        } // if

        // Get subcats by object type
        $l_obj_type_id       = (int) $p_obj_type_id;
        $l_obj_type_catg     = $l_dao->gui_get_catg_with_subcats_by_objtype_id($l_obj_type_id);
        $l_obj_type_catg_ids = array_keys($l_obj_type_catg);

        $l_catg_res = $l_dao->retrieve_categories_by_provide($p_provides, 'g', $p_dynamic_properties);

        while ($l_row = $l_catg_res->get_row())
        {
            $l_parent = '';

            if ($p_consider_rights && !in_array($l_row['isys_property_2_cat__cat_const'], $l_categories))
            {
                continue;
            } // if

            if ($l_obj_type_id > 0 && !in_array($l_row['isysgui_catg__id'], $l_obj_type_catg_ids))
            {
                continue;
            } // if

            if ($l_obj_type_catg[$l_row['isysgui_catg__id']]['isysgui_catg__parent'] > 0)
            {
                // Check for empty title
                if (isset($l_obj_type_catg[$l_obj_type_catg[$l_row['isysgui_catg__id']]['isysgui_catg__parent']]) && $l_obj_type_catg[$l_obj_type_catg[$l_row['isysgui_catg__id']]['isysgui_catg__parent']]['isysgui_catg__title'] != '')
                {
                    $l_parent = _L($l_obj_type_catg[$l_obj_type_catg[$l_row['isysgui_catg__id']]['isysgui_catg__parent']]['isysgui_catg__title']) . ' > ';
                } // if
            } // if

            $l_catg[$l_row['isys_property_2_cat__cat_const']] = $l_parent . _L($l_row['isysgui_catg__title']);
        } // while

        asort($l_catg);
        foreach ($l_catg as $l_const => $l_cat)
        {
            $l_return[$l_const] = $l_cat;
        } // foreach

        return $l_return;
    } // function

    /**
     * Method for returning the "option"-elements with all the specific categories.
     *
     * @param   integer $p_provides
     * @param   boolean $p_dynamic_properties
     * @param   boolean $p_consider_rights
     * @param   integer $p_obj_type_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_cats($p_provides, $p_dynamic_properties, $p_consider_rights = true, $p_obj_type_id = null)
    {
        global $g_comp_database;

        $l_obj_type_id = false;
        $l_dao         = new isys_cmdb_dao_category_property($g_comp_database);

        $l_obj_type_cats_ids = $l_categories = $l_cats = $l_obj_type_cats = $l_return = [];

        // Retrieve all categories, the current user may see.
        if ($p_consider_rights)
        {
            // This IF-block solves #5139
            if ($p_obj_type_id !== null)
            {
                $l_obj_type_const = $p_obj_type_id;

                if (is_numeric($p_obj_type_id))
                {
                    $l_obj_type = $l_dao->get_object_type($p_obj_type_id);

                    $l_obj_type_const = $l_obj_type['isys_obj_type__const'];
                } // if

                $l_categories = isys_auth_cmdb_categories::instance()
                    ->get_allowed_categories_by_obj_type($l_obj_type_const);

                if (in_array(isys_auth::WILDCHAR, $l_categories))
                {
                    $l_categories = true;
                } // if
            }
            else
            {
                $l_categories = isys_auth_cmdb_categories::instance()
                    ->get_allowed_categories();
            } // if

            // In case of a wildchar, we can just deactivate the upcoming checks.
            if ($l_categories === true)
            {
                $p_consider_rights = false;
            } // if

            // If the user has no categories, we can just return an empty array.
            if ($l_categories === false)
            {
                return $l_return;
            } // if
        } // if

        if ($p_obj_type_id !== null && is_numeric($p_obj_type_id))
        {
            $l_obj_type_id = (int) $p_obj_type_id;

            $l_obj_type_cats     = $l_dao->gui_get_cats_with_subcats_by_objtype_id($l_obj_type_id);
            $l_obj_type_cats_ids = array_keys($l_obj_type_cats);
        } // if

        $l_cats_res = $l_dao->retrieve_categories_by_provide($p_provides, 's', $p_dynamic_properties);

        while ($l_row = $l_cats_res->get_row())
        {
            $l_parent = '';

            if ($p_consider_rights && !in_array($l_row['isys_property_2_cat__cat_const'], $l_categories))
            {
                continue;
            } // if

            if ($l_obj_type_id > 0 && !in_array($l_row['isysgui_cats__id'], $l_obj_type_cats_ids))
            {
                continue;
            } // if

            if (isset($l_obj_type_cats[$l_row['isysgui_cats__id']]['parent']) && $l_obj_type_cats[$l_row['isysgui_cats__id']]['parent'] > 0)
            {
                $l_parent = _L($l_dao->get_cats_name_by_id_as_string($l_obj_type_cats[$l_row['isysgui_cats__id']]['parent'])) . ' > ';
            } // if

            $l_obj_types    = [];
            $l_obj_type_row = $l_dao->get_object_types_by_category($l_row['isysgui_cats__id'], 's', false, true);

            foreach ($l_obj_type_row as $l_obj_type)
            {
                if (!empty($l_obj_type['isys_obj_type__title']) && _L($l_obj_type['isys_obj_type__title']) != _L($l_row['isysgui_cats__title']))
                {
                    $l_obj_types[] = _L($l_obj_type['isys_obj_type__title']);
                } // if
            } // foreach

            $l_cats[$l_row['isys_property_2_cat__cat_const']] = $l_parent . _L($l_row['isysgui_cats__title']);

            if (count($l_obj_types) > 0)
            {
                if (count($l_obj_types) > 4)
                {
                    $l_obj_types   = array_slice($l_obj_types, 0, 4);
                    $l_obj_types[] = '...';
                } // if

                $l_cats[$l_row['isys_property_2_cat__cat_const']] .= ' (' . implode(', ', $l_obj_types) . ')';
            } // if
        } // while

        asort($l_cats);

        foreach ($l_cats as $l_const => $l_cat)
        {
            $l_return[$l_const] = $l_cat;
        } // foreach
        return $l_return;
    } // function

    public function get_catg_custom($p_provides, $p_dynamic_properties, $p_consider_rights = true, $p_obj_type_id = null)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

        $l_obj_type_id = false;
        $l_categories  = $l_catg_custom = $l_obj_type = $l_obj_type_catg_ids = $l_return = [];

        // Retrieve all categories, the current user may see.
        if ($p_consider_rights)
        {
            // This IF-block solves #5139
            if ($p_obj_type_id !== null)
            {
                $l_obj_type_const = $p_obj_type_id;

                if (is_numeric($p_obj_type_id))
                {
                    $l_obj_type = $l_dao->get_object_type($p_obj_type_id);

                    $l_obj_type_const = $l_obj_type['isys_obj_type__const'];
                } // if

                $l_categories = isys_auth_cmdb_categories::instance()
                    ->get_allowed_categories_by_obj_type($l_obj_type_const);

                if (in_array(isys_auth::WILDCHAR, $l_categories))
                {
                    $l_categories = true;
                } // if
            }
            else
            {
                $l_categories = isys_auth_cmdb_categories::instance()
                    ->get_allowed_categories();
            } // if

            // In case of a wildchar, we can just deactivate the upcoming checks.
            if ($l_categories === true)
            {
                $p_consider_rights = false;
            } // if

            // If the user has no categories, we can just return an empty array.
            if ($l_categories === false)
            {
                return $l_return;
            } // if
        } // if

        if ($p_obj_type_id !== null && is_numeric($p_obj_type_id))
        {
            $l_obj_type_id = (int) $p_obj_type_id;

            $l_obj_type_catg_res = $l_dao->get_catg_custom_by_obj_type($l_obj_type_id);
            while ($l_row = $l_obj_type_catg_res->get_row())
            {
                $l_obj_type_catg_ids[] = $l_row['isysgui_catg_custom__id'];
            } // while
        } // if

        $l_catg_res = $l_dao->retrieve_categories_by_provide($p_provides, 'g_custom', $p_dynamic_properties);

        while ($l_row = $l_catg_res->get_row())
        {
            if ($p_consider_rights && !in_array($l_row['isys_property_2_cat__cat_const'], $l_categories))
            {
                continue;
            } // if

            if ($l_obj_type_id > 0 && !in_array($l_row['isysgui_catg_custom__id'], $l_obj_type_catg_ids))
            {
                continue;
            } // if

            $l_catg_custom[$l_row['isys_property_2_cat__cat_const']] = _L($l_row['isysgui_catg_custom__title']);
        } // while

        asort($l_catg_custom);

        foreach ($l_catg_custom as $l_const => $l_cat)
        {
            $l_return[$l_const] = $l_cat;
        } // foreach
        return $l_return;
    }

    /**
     * Method for loading the preselection data.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_preselection($p_data)
    {
        global $g_comp_database;

        if (!is_array($p_data) || count($p_data) == 0)
        {
            return [];
        } // if

        /**
         * Type-hinting for IDE.
         *
         * @var isys_cmdb_dao $l_dao
         */
        $l_dao = isys_cmdb_dao::instance($g_comp_database);
        $l_cat = $l_cond = $l_return = $l_sorting = [];

        foreach ($p_data as $l_cat_entry)
        {
            if (is_array($l_cat_entry) && count($l_cat_entry)) foreach ($l_cat_entry as $l_cat_type => $l_category)
            {
                if (is_array($l_category) && count($l_category)) foreach ($l_category as $l_category_id => $l_prop)
                {
                    if (is_array($l_prop) && count($l_prop)) foreach ($l_prop as $l_prop_key)
                    {
                        $l_cat[$l_cat_type][$l_category_id][]                              = $l_prop_key;
                        $l_sorting[$l_cat_type . '.' . $l_category_id . '.' . $l_prop_key] = 0;
                    } // foreach
                } // foreach
            } // foreach
        } // foreach

        $l_catg_key        = 'const';
        $l_cats_key        = 'const';
        $l_catg_custom_key = 'const';
        if (count($l_cat['g']) > 0)
        {
            $l_catg_keys = array_keys($l_cat['g']);
            if (is_numeric($l_catg_keys[0]))
            {
                $l_catg_key = 'id';
                $l_cond[]   = " isys_property_2_cat__isysgui_catg__id IN (" . implode(",", $l_catg_keys) . ") ";
            }
            else
            {
                $l_cond[] = " isys_property_2_cat__cat_const IN ('" . implode("','", $l_catg_keys) . "') ";
            } // if
        } // if

        if (count($l_cat['s']) > 0)
        {
            $l_cats_keys = array_keys($l_cat['s']);
            if (is_numeric($l_cats_keys[0]))
            {
                $l_cats_key = 'id';
                $l_cond[]   = " isys_property_2_cat__isysgui_cats__id IN (" . implode(",", $l_cats_keys) . ") ";
            }
            else
            {
                $l_cond[] = " isys_property_2_cat__cat_const IN ('" . implode("','", $l_cats_keys) . "') ";
            } // if
        } // if

        if (count($l_cat['g_custom']) > 0)
        {
            $l_catg_custom_keys = array_keys($l_cat['g_custom']);
            if (is_numeric($l_catg_custom_keys[0]))
            {
                $l_catg_custom_key = 'id';
                $l_cond[]          = " isys_property_2_cat__isysgui_catg_custom__id IN (" . implode(",", $l_catg_custom_keys) . ") ";
            }
            else
            {
                $l_cond[] = " isys_property_2_cat__cat_const IN ('" . implode("','", $l_catg_custom_keys) . "') ";
            } // if
        } // if

        // This may happen when there are problems with the constants.
        if (count($l_cond) == 0)
        {
            return [];
        } // if

        $l_sql = "SELECT
				isys_property_2_cat__id,
				isys_property_2_cat__isysgui_catg__id,
				isys_property_2_cat__isysgui_cats__id,
				isys_property_2_cat__isysgui_catg_custom__id,
				isys_property_2_cat__cat_const,
				isys_property_2_cat__prop_type,
				isys_property_2_cat__prop_key,
				isys_property_2_cat__prop_title,
			(CASE
				WHEN isys_property_2_cat__isysgui_catg__id > 0 THEN isys_property_2_cat.isys_property_2_cat__isysgui_catg__id
				WHEN isys_property_2_cat__isysgui_cats__id > 0 THEN isys_property_2_cat.isys_property_2_cat__isysgui_cats__id
				WHEN isys_property_2_cat__isysgui_catg_custom__id > 0 THEN isys_property_2_cat.isys_property_2_cat__isysgui_catg_custom__id
			END) AS id,
			(CASE
				WHEN isys_property_2_cat__isysgui_catg__id > 0 THEN isysgui_catg.isysgui_catg__title
				WHEN isys_property_2_cat__isysgui_cats__id > 0 THEN isysgui_cats.isysgui_cats__title
				WHEN isys_property_2_cat__isysgui_catg_custom__id > 0 THEN isysgui_catg_custom.isysgui_catg_custom__title
			END) AS title,
			(CASE
				WHEN isys_property_2_cat__isysgui_catg__id > 0 THEN isysgui_catg.isysgui_catg__const
				WHEN isys_property_2_cat__isysgui_cats__id > 0 THEN isysgui_cats.isysgui_cats__const
				WHEN isys_property_2_cat__isysgui_catg_custom__id > 0 THEN isysgui_catg_custom.isysgui_catg_custom__const
			END) AS const,
			(CASE
				WHEN isys_property_2_cat__isysgui_catg__id > 0 THEN isysgui_catg.isysgui_catg__class_name
				WHEN isys_property_2_cat__isysgui_cats__id > 0 THEN isysgui_cats.isysgui_cats__class_name
				WHEN isys_property_2_cat__isysgui_catg_custom__id > 0 THEN 'isys_cmdb_dao_category_g_custom_fields'
			END) AS class

		FROM isys_property_2_cat
		    LEFT JOIN isysgui_catg
		    ON isysgui_catg__id = isys_property_2_cat__isysgui_catg__id

		    LEFT JOIN isysgui_cats
		    ON isysgui_cats__id = isys_property_2_cat__isysgui_cats__id

			LEFT JOIN isysgui_catg_custom
			ON isysgui_catg_custom__id = isys_property_2_cat__isysgui_catg_custom__id

			WHERE TRUE AND (" . implode('OR', $l_cond) . ");";

        $l_res = $l_dao->retrieve($l_sql);

        // Cache for the custom category daos
        $l_custom_daos = [];

        while ($l_row = $l_res->get_row())
        {
            if (!class_exists($l_row['class'])) continue;

            if ($l_row['class'] === 'isys_cmdb_dao_category_g_custom_fields')
            {
                if (isset($l_custom_daos[$l_row['id']]))
                {
                    $l_cat_dao = $l_custom_daos[$l_row['id']];
                }
                else
                {
                    // Have to create a new instances because the properties are in every custom category different
                    $l_cat_dao = new isys_cmdb_dao_category_g_custom_fields($l_dao->get_database_component());
                    $l_cat_dao->set_catg_custom_id($l_row['id']);
                    $l_custom_daos[$l_row['id']] = $l_cat_dao;
                } // if
            }
            else
            {
                $l_cat_dao = $l_row['class']::instance($l_dao->get_database_component());
            } // if

            $l_properties = $l_cat_dao->get_properties();
            $l_property   = $l_properties[$l_row['isys_property_2_cat__prop_key']];

            // Check if property is sortable or not
            $l_sortable = false;

            if ($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] && ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE || $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__TEXT || (($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus')) && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]))))
            {
                if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus') || ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE && $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST]))
                {
                    $l_sortable = true;
                }
                else
                {
                    if (!isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType']) && !isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && !isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                    {
                        $l_sortable = true;
                    } // if
                } // if
            }
            elseif ($l_row['isys_property_2_cat__cat_const'] == 'C__CATG__GLOBAL')
            {
                $l_sortable = true;
            } // if

            if ($l_row['isys_property_2_cat__isysgui_catg__id'] !== null)
            {
                if (isset($l_cat['g'][$l_row[$l_catg_key]]) && is_array($l_cat['g'][$l_row[$l_catg_key]]))
                {
                    if (in_array($l_row['isys_property_2_cat__prop_key'], $l_cat['g'][$l_row[$l_catg_key]]))
                    {
                        $l_return[$l_row['isys_property_2_cat__id']] = [
                            'cat_type'      => 'g',
                            'cat_title'     => isys_glob_utf8_encode(_L($l_row['title'])),
                            'cat_const'     => $l_row['isys_property_2_cat__cat_const'],
                            'prop_id'       => $l_row['isys_property_2_cat__id'],
                            'prop_key'      => $l_row['isys_property_2_cat__prop_key'],
                            'prop_title'    => isys_glob_utf8_encode(_L($l_row['isys_property_2_cat__prop_title'])),
                            'prop_type'     => $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                            'prop_sortable' => $l_sortable
                        ];
                        // We fill the sorting array, to easily get the right sorting.
                        $l_sorting['g.' . $l_row[$l_catg_key] . '.' . $l_row['isys_property_2_cat__prop_key']] = $l_row['isys_property_2_cat__id'];
                    } // if
                }
            }
            elseif ($l_row['isys_property_2_cat__isysgui_cats__id'] !== null)
            {
                if (isset($l_cat['s'][$l_row[$l_cats_key]]) && is_array($l_cat['s'][$l_row[$l_cats_key]]))
                {
                    if (in_array($l_row['isys_property_2_cat__prop_key'], $l_cat['s'][$l_row[$l_cats_key]]))
                    {
                        $l_return[$l_row['isys_property_2_cat__id']] = [
                            'cat_type'      => 's',
                            'cat_title'     => isys_glob_utf8_encode(_L($l_row['title'])),
                            'cat_const'     => $l_row['isys_property_2_cat__cat_const'],
                            'prop_id'       => $l_row['isys_property_2_cat__id'],
                            'prop_key'      => $l_row['isys_property_2_cat__prop_key'],
                            'prop_title'    => isys_glob_utf8_encode(_L($l_row['isys_property_2_cat__prop_title'])),
                            'prop_type'     => $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                            'prop_sortable' => $l_sortable
                        ];
                        // We fill the sorting array, to easily get the right sorting.
                        $l_sorting['s.' . $l_row[$l_cats_key] . '.' . $l_row['isys_property_2_cat__prop_key']] = $l_row['isys_property_2_cat__id'];
                    } // if
                }
            }
            else
            {
                if (isset($l_cat['g_custom'][$l_row[$l_catg_custom_key]]) && is_array($l_cat['g_custom'][$l_row[$l_catg_custom_key]]))
                {
                    if (in_array($l_row['isys_property_2_cat__prop_key'], $l_cat['g_custom'][$l_row[$l_catg_custom_key]]))
                    {
                        $l_return[$l_row['isys_property_2_cat__id']] = [
                            'cat_type'      => 'g_custom',
                            'cat_title'     => isys_glob_utf8_encode(_L($l_row['title'])),
                            'cat_const'     => $l_row['isys_property_2_cat__cat_const'],
                            'prop_id'       => $l_row['isys_property_2_cat__id'],
                            'prop_key'      => $l_row['isys_property_2_cat__prop_key'],
                            'prop_title'    => isys_glob_utf8_encode(_L($l_row['isys_property_2_cat__prop_title'])),
                            'prop_type'     => $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                            'prop_sortable' => $l_sortable
                        ];
                        // We fill the sorting array, to easily get the right sorting.
                        $l_sorting['g_custom.' . $l_row[$l_catg_custom_key] . '.' . $l_row['isys_property_2_cat__prop_key']] = $l_row['isys_property_2_cat__id'];
                    } // if
                }
            } // if
        } // while

        $l_return_sorted = [];

        // Finally we sort the array, to get the same positions as in the preselection.
        foreach ($l_sorting as $l_sort_prop_id)
        {
            if ($l_return[$l_sort_prop_id] !== null)
            {
                $l_return_sorted[] = $l_return[$l_sort_prop_id];
            } // if
        } // foreach

        return $l_return_sorted;
    } // function

    public function handle_preselection_lvls($p_data)
    {
        foreach ($p_data AS $l_lvl => $l_lvl_content)
        {
            foreach ($l_lvl_content AS $l_key => $l_content)
            {
                $p_data[$l_lvl][$l_key] = $this->handle_preselection($l_content);
            }
        }

        return $p_data;
    }

} // class