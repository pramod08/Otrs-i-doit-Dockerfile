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
class isys_ajax_handler_get_category_data extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        switch ($_GET['func'])
        {
            case 'get_data':
                $l_return = $this->get_data();
                break;

            case 'get_properties_by_database':
                $l_return = $this->get_properties_by_database();
                break;

            case 'get_filtered_properties_by_database':
                $l_return = $this->get_filtered_properties_by_database();
                break;
            case 'get_property_keys_and_names':
                $l_return = $this->get_property_keys_and_names();
                break;

            case 'is_property_sortable':
                $l_return = $this->is_property_sortable();
                break;

            case 'get_categories':
                $l_return = $this->get_categories();
                break;

            case 'format_preselection':
                $l_return = $this->format_preselection();
                break;
        } // switch

        echo isys_format_json::encode($l_return);
        $this->_die();
    } // function

    /**
     * Rebuilds selected properties to a readable format for the property selector
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function format_preselection()
    {
        global $g_comp_database;
        $l_dao = new isys_smarty_plugin_f_property_selector($g_comp_database);

        return $l_dao->handle_preselection(isys_format_json::decode($_POST['data']));
    } // function

    /**
     * Get global / specific categories for the property selector
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function get_categories()
    {
        global $g_comp_database;
        $l_dao    = new isys_smarty_plugin_f_property_selector($g_comp_database);
        $l_return = [
            'catg'        => $l_dao->get_catg($_POST['provides'], $_POST['dynamic_properties'], $_POST['consider_rights']),
            'cats'        => $l_dao->get_cats($_POST['provides'], $_POST['dynamic_properties'], $_POST['consider_rights']),
            'catg_custom' => $l_dao->get_catg_custom($_POST['provides'], $_POST['dynamic_properties'], $_POST['consider_rights'])
        ];

        return $l_return;
    } // function

    /**
     * Get-data method.
     *
     * It is possible to pass the following parameters per post:
     *    catsID (int)
     *    catgID (int)
     *    objID (int)
     *    condition (string)
     *
     * @global  isys_component_database $g_comp_database
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_data()
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        $l_return = [];

        // We look, if we are selecting a specific or global category.
        if (isset($_POST[C__CMDB__GET__CATS]))
        {
            $l_get_param  = C__CMDB__GET__CATS;
            $l_cat_suffix = 's';
        }
        else
        {
            $l_get_param  = C__CMDB__GET__CATG;
            $l_cat_suffix = 'g';
        } // if

        $l_cat_id    = $_POST[$l_get_param];
        $l_object_id = (int) $_POST[C__CMDB__GET__OBJECT];
        $l_condition = $_POST['condition'];

        // Get category info.
        $l_isysgui = $l_dao->get_isysgui('isysgui_cat' . $l_cat_suffix, $l_cat_id)
            ->__to_array();

        // Check class and instantiate it.
        if (class_exists($l_isysgui['isysgui_cat' . $l_cat_suffix . '__class_name']))
        {
            /**
             * IDE typehinting.
             *
             * @var  $l_cat  isys_cmdb_dao_category
             */
            if (($l_cat = new $l_isysgui['isysgui_cat' . $l_cat_suffix . '__class_name']($g_comp_database)))
            {
                // Check if the get_data method exists.
                if (method_exists($l_cat, 'get_data'))
                {
                    if (isset($l_condition))
                    {
                        $l_catdata = $l_cat->get_data(null, null, $l_condition);
                    }
                    else
                    {
                        $l_catdata = $l_cat->get_data(null, $l_object_id);
                    } // if

                    if ($l_catdata->num_rows() > 0)
                    {
                        while ($l_row = $l_catdata->get_row())
                        {
                            $l_return[] = $l_row;
                        } // while
                    } // if
                } // if
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Retrieve the properties by the isys_property_2_cat table.
     *
     * @global  isys_component_database $g_comp_database
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_properties_by_database()
    {
        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);

        $l_return                     = [];
        $l_dynamic_properties         = $_POST['dynamic_properties'];
        $l_allowed_prop_types         = explode(',', $_POST['allowed_prop_types']);
        $l_consider_rights            = ($_POST['consider_rights'] == 'true') ? true : false;
        $l_replace_dynamic_properties = $_POST['replace_dynamic_properties'];

        $l_res = $l_dao->retrieve_properties(
            null,
            null,
            null,
            $_POST['provide'],
            'AND isys_property_2_cat__cat_const = ' . $l_dao->convert_sql_text($_POST['cat_const']),
            $l_dynamic_properties
        );

        $l_keys = [];

        while ($l_row = $l_res->get_row())
        {
            $l_cat_dao       = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
            $l_properties    = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
            $l_property      = $l_properties[$l_row['key']];
            $l_property_type = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];

            // This can be used to display only types like "text" or "dialog", ...
            if (count($l_allowed_prop_types) > 0 && !empty($l_allowed_prop_types[0]) && $l_consider_rights && !in_array($l_property_type, $l_allowed_prop_types))
            {
                continue;
            } // if

            // Also skip the "HR" and "HTML" fields of custom categories.
            if ($_POST['cat_type'] == 'g_custom' && (strpos($l_row['key'], 'hr_c_') === 0 || strpos($l_row['key'], 'html_c_') === 0))
            {
                continue;
            } // if

            if ($l_replace_dynamic_properties && $l_row['type'] == C__PROPERTY_TYPE__DYNAMIC)
            {
                $l_search_key = substr($l_row['key'], 1);
                if (array_key_exists($l_search_key, $l_keys))
                {
                    unset($l_return[$l_keys[$l_search_key]]);
                } // if
            } // if

            $l_return[$l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type] = _L($l_row['title']);
            $l_keys[$l_row['key']]                                                 = $l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type;
        } // while

        // Sort result
        asort($l_return);

        return $l_return;
    } // function

    /**
     * Retrieve and filter the properties by the isys_property_2_cat table.
     *
     * @global  isys_component_database $g_comp_database
     * @return  array
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    protected function get_filtered_properties_by_database()
    {
        // Init
        $l_dao                        = new isys_cmdb_dao_category_property($this->m_database_component);
        $l_return                     = [];
        $l_filter                     = strtolower($_POST['filter']);
        $l_dynamic_properties         = $_POST['dynamic_properties'];
        $l_allowed_prop_types         = explode(',', $_POST['allowed_prop_types']);
        $l_consider_rights            = ($_POST['consider_rights'] == 'true') ? true : false;
        $l_replace_dynamic_properties = $_POST['replace_dynamic_properties'];
        $l_obj_type_id                = $_POST['obj_type_id'];
        $l_custom_fields              = $_POST['custom_fields'];
        $l_condition                  = '';

        // Handling custom fields
        if ($l_custom_fields != true)
        {
            $l_condition = ' AND isys_property_2_cat__isysgui_catg_custom__id IS NULL';
        } // if

        // Create dao res
        $l_res = $l_dao->retrieve_properties(
            null,
            null,
            null,
            $_POST['provide'],
            $l_condition,
            $l_dynamic_properties
        );

        $l_keys = [];

        while ($l_row = $l_res->get_row())
        {
            $l_cat_dao       = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
            $l_properties    = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
            $l_property      = $l_properties[$l_row['key']];
            $l_property_type = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];

            if ($l_obj_type_id > 0)
            {
                // Check if the found category is assigned to the given object-type.
                if ($l_row['catg'] > 0 && !$l_dao->objtype_is_catg_assigned($l_obj_type_id, $l_row['catg']))
                {
                    continue;
                } // if

                if ($l_row['cats'] > 0 && !$l_dao->objtype_is_cats_assigned($l_obj_type_id, $l_row['cats']))
                {
                    continue;
                } // if

                // Find something for custom categories.
                if ($l_custom_fields && $l_row['catg_custom'] > 0 && !$l_dao->objtype_is_catg_custom_assigned($l_obj_type_id, $l_row['catg_custom']))
                {
                    continue;
                } // if
            } // if

            // This can be used to display only types like "text" or "dialog", ...
            if (count($l_allowed_prop_types) > 0 && !empty($l_allowed_prop_types[0]) && $l_consider_rights && !in_array($l_property_type, $l_allowed_prop_types))
            {
                continue;
            } // if

            // Also skip the "HR" and "HTML" fields of custom categories.
            if ($_POST['cat_type'] == 'g_custom' && (strpos($l_row['key'], 'hr_c_') === 0 || strpos($l_row['key'], 'html_c_') === 0))
            {
                continue;
            } // if

            if ($l_replace_dynamic_properties && $l_row['type'] == C__PROPERTY_TYPE__DYNAMIC)
            {
                $l_search_key = substr($l_row['key'], 1);
                if (array_key_exists($l_search_key, $l_keys))
                {
                    unset($l_return[$l_keys[$l_search_key]]);
                } // if
            } // if

            $l_prop_title = _L($l_row['title']);

            // Filter property
            if (strpos(strtolower($l_prop_title), $l_filter) !== false)
            {
                $l_cat_type  = null;
                $l_cat_title = $l_dao->get_category_by_const_as_string($l_row['const']);

                // Detect category type
                if (isset($l_row['catg']))
                {
                    $l_cat_type = 'g';
                }
                else if (isset($l_row['cats']))
                {
                    $l_cat_type = 's';
                }
                else if (isset($l_row['catg_custom']))
                {
                    $l_cat_type = 'g_custom';
                } // if

                // Add property to results
                $l_return[$l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type] = [
                    'title'     => $l_prop_title . ' <span class="removeable-addon">(' . $l_cat_title . ')</span>',
                    'cat_type'  => $l_cat_type,
                    'cat_const' => $l_row['const'],
                    'cat_title' => $l_cat_title,
                ];

                $l_keys[$l_row['key']] = $l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type;
            } // if

        } // while

        asort($l_return);

        return $l_return;
    } // function

    /**
     * Method for loading all property keys and their translated names by a given category-constant.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_property_keys_and_names()
    {
        $l_return = [];
        $l_props  = $this->get_properties_by_database();

        foreach ($l_props as $l_prop_key => $l_prop_name)
        {
            if (!empty($l_prop_name))
            {
                $l_return[] = $l_prop_name . ': "' . current(explode('#', $l_prop_key)) . '"';
            } // if
        } // foreach

        return $l_return;
    } // function

    /**
     * This method checks if a property is sortable or not
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function is_property_sortable()
    {
        $l_prop_id    = $_POST['prop_id'];
        $l_dao        = new isys_cmdb_dao_category_property($this->m_database_component);
        $l_prop_arr   = $l_dao->retrieve_properties($l_prop_id, null, null, null, '', true)
            ->__to_array();
        $l_cat_dao    = $l_dao->get_dao_instance($l_prop_arr['class'], ($l_prop_arr['catg_custom'] ?: null));
        $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
        $l_property   = $l_properties[$l_prop_arr['key']];
        $l_return     = false;

        if ($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] && ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE || $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__TEXT || (($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus')) && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]))))
        {
            if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus') || ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE && $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST]))
            {
                $l_return = true;
            }
            else
            {
                if (!isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType']) && !isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && !isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                {
                    $l_return = true;
                }
            }
        }
        elseif ($l_prop_arr['const'] == 'C__CATG__GLOBAL')
        {
            $l_return = true;
        }

        return $l_return;
    }

} // class
?>