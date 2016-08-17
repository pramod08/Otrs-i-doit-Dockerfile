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
 * AJAX handler for tree levels.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_tree_level extends isys_ajax_handler
{
    /**
     * Initialize tree level handler
     *
     * @global  isys_component_database $g_comp_database
     * @return  array|void
     */
    public function init()
    {
        global $g_comp_database;

        // Retrieve id parameter. Convert -1 request to null (root node).
        if ($this->m_get['id'] == -1)
        {
            $l_id = null;
        }
        else
        {
            $l_id = $this->m_get['id'];
        } // if

        if ($this->m_get['get_obj_name'])
        {
            $l_location_popup = new isys_popup_browser_location();
            echo $l_location_popup->format_selection($l_id);

            $this->_die();
        } // if

        header('Content-Type: application/json');

        // ID-2898 - Only append the auth-condition, if this feature is enabled.
        $l_consider_rights = !!isys_tenantsettings::get('auth.use-in-location-tree', false);

        $l_dao = isys_component_dao_user::instance($g_comp_database);
        $l_dao->save_settings(C__SETTINGS_PAGE__SYSTEM, ['C__CATG__OVERVIEW__DEFAULT_TREETYPE' => $this->m_post['tree_type']]);

        // Check for "$l_id != C__OBJ__ROOT_LOCATION" because we can't authorize the root location itself ;)
        if ($l_consider_rights && $l_id > 0 && $l_id != C__OBJ__ROOT_LOCATION && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $l_id))
        {
            if ($this->m_get['return_value'])
            {
                return [];
            } // if

            echo '[]';

            $this->_die();
        } // if

        // The function "isys_glob_get_param()" allows us to get different types by GET parameter (used by "relocate-ci" module).
        switch (isys_glob_get_param('tree_type'))
        {
            case C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS:
                $l_return = $this->logical($l_id, $l_consider_rights);
                break;
            case C__CMDB__VIEW__TREE_LOCATION__COMBINED:
                $l_return = $this->combined($l_id, $l_consider_rights);
                break;
            default:
            case C__CMDB__VIEW__TREE_LOCATION__LOCATION:
                $l_return = $this->location($l_id, true, $l_consider_rights);
                break;
        } // switch

        if ($this->m_get['return_value'])
        {
            return $l_return;
        } // if

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * Filters logical devices from the physical tree in the combined view
     *
     * @param $p_tree_array
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function filter_logical_devices_from_physical(&$p_tree_array)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao::instance($g_comp_database);

        foreach ($p_tree_array AS $l_key => $l_value)
        {
            if ($l_dao->get_objTypeID($l_value['id']) != C__OBJTYPE__WORKSTATION && $l_dao->get_objTypeID($l_value['parentId']) != C__OBJTYPE__WORKSTATION)
            {

                $l_logical_unit_id = $l_dao->retrieve(
                    'SELECT isys_catg_logical_unit_list__isys_obj__id__parent FROM isys_catg_logical_unit_list
						WHERE isys_catg_logical_unit_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_value['id'])
                )
                    ->get_row_value('isys_catg_logical_unit_list__isys_obj__id__parent');

                if ($l_logical_unit_id !== null)
                {
                    if ($l_dao->get_objTypeID($l_logical_unit_id) === C__OBJTYPE__WORKSTATION)
                    {
                        unset($p_tree_array[$l_key]);
                    } // if
                } // if
            } // if
        } // foreach
    } // function

    /**
     * Return only logical locations.
     *
     * @param   integer  $p_id
     * @param   boolean  $p_consider_rights
     *
     * @return  array
     */
    private function logical($p_id = -1, $p_consider_rights = false)
    {
        global $g_comp_database, $g_dirs, $g_config;

        $l_result = [];

        $l_dao  = new isys_cmdb_dao_category_g_logical_unit($g_comp_database);
        $l_data = $l_dao->get_data_by_parent($p_id, $p_consider_rights);

        while ($l_row = $l_data->get_row())
        {
            $l_otype = $l_dao->get_type_by_id($l_row['isys_obj__isys_obj_type__id']);

            if (!empty($l_otype["isys_obj_type__icon"]))
            {
                if (strstr($l_otype["isys_obj_type__icon"], '/'))
                {
                    $l_icon = $g_config['www_dir'] . $l_otype["isys_obj_type__icon"];
                }
                else
                {
                    $l_icon = $g_dirs["images"] . "tree/" . $l_otype["isys_obj_type__icon"];
                } // if
            }
            else
            {
                $l_icon = $g_dirs['images'] . 'icons/silk/page_white.png';
            } // if

            if ($p_id == -1 || $p_id == C__OBJ__ROOT_LOCATION || empty($l_row['isys_catg_logical_unit_list__isys_obj__id__parent']))
            {
                $l_node_root = -1;
            }
            else
            {
                $l_node_root = $l_row['isys_catg_logical_unit_list__isys_obj__id__parent'];
            } // if

            $l_leaf       = !(bool) $l_dao->get_data_by_parent($l_row['isys_catg_logical_unit_list__isys_obj__id'])
                ->num_rows();
            $l_hyperlinks = !(isset($this->m_get['no-hyperlinks']) && $this->m_get['no-hyperlinks'] > 0);
            $l_url        = $l_hyperlinks ? 'javascript:ObjectSelected(' . $l_row['isys_catg_logical_unit_list__isys_obj__id'] . ', ' . $l_row['isys_obj__isys_obj_type__id'] . ', \'' . $l_row ['isys_obj__title'] . '\', \'' . _L(
                    $l_row ['isys_obj_type__title']
                ) . '\', \'g_browser_Link_' . $l_row ['isys_catg_logical_unit_list__isys_obj__id'] . '\', this);' : 'javascript:Prototype.emptyFunction;';

            $l_result[] = [
                'id'                     => $l_row['isys_catg_logical_unit_list__isys_obj__id'],
                'text'                   => $l_row['isys_obj__title'],
                'icon'                   => $l_icon,
                'url'                    => $l_url,
                'parentId'               => $l_node_root,
                'is_leaf'                => $l_leaf,
                'is_logically_assigned'  => true,
                'is_physically_assigned' => false
            ];
        } // while

        return $l_result;
    }

    /**
     * Return both, logical and physical locations in a merged view.
     *
     * @param   integer  $p_id
     * @param   boolean  $p_consider_rights
     *
     * @return  array
     */
    private function combined($p_id = -1, $p_consider_rights = false)
    {
        global $g_comp_database;

        $l_dao     = new isys_cmdb_dao_category_g_logical_unit($g_comp_database);
        $l_dao_loc = new isys_cmdb_dao_location($g_comp_database);

        $l_return = [];

        if (!$p_id)
        {
            $l_merged_arr = $this->location($p_id, false, $p_consider_rights);
        }
        else
        {
            $l_merged_arr = array_merge($this->location($p_id, false, $p_consider_rights), $this->logical($p_id, $p_consider_rights));
        } // if

        if (count($l_merged_arr) > 0)
        {
            $this->filter_logical_devices_from_physical($l_merged_arr);
        } // if

        foreach ($l_merged_arr as $l_value)
        {
            $l_value['is_leaf'] = (!$l_dao->get_data_by_parent($l_value['id'])->num_rows() && !$l_dao_loc->get_child_locations($l_value['id'], true, isset($this->m_get['containersOnly']))->num_rows());

            $l_return[] = $l_value;
        } // foreach

        return $l_return;
    } // function

    /**
     * Return logical locations by its parent.
     *
     * @param   integer  $p_id
     * @param   boolean  $p_leaf_checking
     * @param   boolean  $p_consider_rights
     *
     * @return  array
     */
    private function location($p_id = -1, $p_leaf_checking = true, $p_consider_rights = false)
    {
        global $g_dirs, $g_comp_database, $g_config;

        // Determine, whether to show the root location.
        $l_hide_root = isset($this->m_get['hide_root']);

        $l_result = [];

        $l_dao = new isys_cmdb_dao_location($g_comp_database);

        $l_rows = $l_dao->get_child_locations($p_id, $l_hide_root, $this->m_get['containersOnly'], $p_consider_rights);

        while ($l_row = $l_rows->get_row())
        {
            // Decide whether to show node.
            // 1. Condition: If the object ID is equal to the current, the node is not added. So we avoid loopbacks in the location tree.
            if ($l_row['isys_catg_location_list__isys_obj__id'] != $this->m_get['currentObjID'])
            {
                if (!empty($l_row["isys_obj_type__icon"]))
                {
                    if (strstr($l_row["isys_obj_type__icon"], '/'))
                    {
                        $l_icon = $g_config['www_dir'] . $l_row["isys_obj_type__icon"];
                    }
                    else
                    {
                        $l_icon = $g_dirs["images"] . "tree/" . $l_row["isys_obj_type__icon"];
                    } // if
                }
                else
                {
                    $l_icon = $g_dirs['images'] . 'icons/silk/page_white.png';
                } // if

                if ($l_hide_root && $l_row['isys_catg_location_list__parentid'] == C__OBJ__ROOT_LOCATION)
                {
                    $l_node_root = -1;
                }
                else
                {
                    $l_node_root = $l_row['isys_catg_location_list__parentid'];
                } // if

                // Set the default callback action.
                $l_selectCallback = 'ObjectSelected';

                if ($this->m_get['selectCallback'])
                {
                    $l_selectCallback = $this->m_get['selectCallback'];
                } // if

                $l_hyperlinks = !(isset($this->m_get['no-hyperlinks']) && $this->m_get['no-hyperlinks'] > 0);
                $l_url        = $l_hyperlinks ? 'javascript:' . $l_selectCallback . '(' . $l_row['isys_catg_location_list__isys_obj__id'] . ', ' . $l_row['isys_obj__isys_obj_type__id'] . ', \'' . addslashes(
                        $l_row['isys_obj__title']
                    ) . '\', \'' . _L(
                        $l_row['isys_obj_type__title']
                    ) . '\', \'g_browser_Link_' . $l_row['isys_catg_location_list__isys_obj__id'] . '\', this);' : 'javascript:Prototype.emptyFunction;';

                $l_result[] = [
                    'id'                     => $l_row ['isys_catg_location_list__isys_obj__id'],
                    'text'                   => $l_row['isys_obj__title'],
                    'icon'                   => $l_icon,
                    'url'                    => $l_url,
                    'parentId'               => $l_node_root,
                    'is_leaf'                => ($p_leaf_checking ? ($l_row['ChildrenCount'] == 0) : false),
                    'is_logically_assigned'  => false,
                    'is_physically_assigned' => true
                ];
            } // if
        } // while

        return $l_result;
    } // function
} // class