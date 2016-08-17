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
 * CMDB Tree view for object types
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_objecttype extends isys_cmdb_view_tree
{
    /**
     * Returns the view mode ID
     *
     * @return integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_OBJECTTYPE;
    } // function

    /**
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    } // function

    /**
     * @return string
     */
    public function get_name()
    {
        return "Objekttypbaum";
    } // function

    /**
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    } // function

    /**
     * Method for building the object type tree.
     */
    public function tree_build()
    {
        global $g_config, $g_dirs;

        $l_gets = $this->get_module_request()
            ->get_gets();
        $l_dao  = $this->get_dao_cmdb();
        $l_tpl  = $this->get_module_request()
            ->get_template();

        $this->remove_ajax_parameters($l_gets);

        // Set default object group, if unset.
        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE]))
        {
            $l_gets[C__CMDB__GET__OBJECTGROUP] = $l_dao->retrieve(
                'SELECT isys_obj_type__isys_obj_type_group__id FROM isys_obj_type WHERE isys_obj_type__id = ' . $l_dao->convert_sql_id(
                    $l_gets[C__CMDB__GET__OBJECTTYPE]
                ) . ';'
            )
                ->get_row_value('isys_obj_type__isys_obj_type_group__id');
        }
        elseif (!isset($l_gets[C__CMDB__GET__OBJECTGROUP]))
        {
            $l_gets[C__CMDB__GET__OBJECTGROUP] = $l_dao->retrieve(
                'SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__status = ' . $l_dao->convert_sql_int(
                    C__RECORD_STATUS__NORMAL
                ) . ' ORDER BY isys_obj_type_group__sort ASC LIMIT 0,1;'
            )
                ->get_row_value('isys_obj_type_group__id');
        } // if

        // Determines types for the specified object type group.
        $l_typeres = $l_dao->objtype_get_by_objgroup_id($l_gets[C__CMDB__GET__OBJECTGROUP], true);

        // Add root node.
        $l_rootgets                         = $l_gets;
        $l_rootgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECTTYPE;

        if ($l_typeres && $l_typeres->num_rows())
        {
            $l_groupres = $l_dao->objgroup_get_by_id($l_gets[C__CMDB__GET__OBJECTGROUP]);

            if ($l_groupres && $l_groupres->num_rows() > 0)
            {
                $l_groupdata = $l_groupres->get_row();
                $l_roottitle = _L($l_groupdata["isys_obj_type_group__title"]);
            }
            else
            {
                $l_roottitle = _L('LC__CMDB__OBJTYPE');
            } // if

            $l_root_link = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP, $l_rootgets);

            $l_root = $this->m_tree->add_node(0, C__CMDB__TREE_NODE__PARENT, $l_roottitle, $l_root_link, '', $g_dirs['images'] . 'icons/silk/application_view_icons.png');

            $l_objtypeid = $l_gets[C__CMDB__GET__OBJECTTYPE];

            // We want an object list and a type tree.
            $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECT;
            $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;

            $l_type_data_arr = [];

            while ($l_typedata = $l_typeres->get_row())
            {
                if (empty($l_typedata["isys_obj_type__show_in_tree"]))
                {
                    continue;
                } // if

                $l_type_data_arr[_L($l_typedata['isys_obj_type__title']) . $l_typedata['isys_obj_type__id']] = $l_typedata;
            } // while

            if (isys_tenantsettings::get(
                    'cmdb.registry.object_type_sorting',
                    C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC
                ) == C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC && count($l_type_data_arr)
            )
            {
                ksort($l_type_data_arr);
            } // if

            foreach ($l_type_data_arr as $l_typedata)
            {
                $l_icon = "";

                if (empty($l_typedata["isys_obj_type__show_in_tree"]))
                {
                    continue;
                } // if

                $l_issel = ($l_typedata["isys_obj_type__id"] == $l_objtypeid) ? 1 : 0;

                $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_typedata["isys_obj_type__id"];

                if (!empty($l_typedata["isys_obj_type__icon"]))
                {
                    if (strstr($l_typedata["isys_obj_type__icon"], '/'))
                    {
                        $l_icon = $g_config['www_dir'] . $l_typedata["isys_obj_type__icon"];
                    }
                    else
                    {
                        $l_icon = $g_dirs["images"] . "tree/" . $l_typedata["isys_obj_type__icon"];
                    } // if
                } // if

                // Ajax-Tree active?
                global $g_ajax_calls;

                if ($g_ajax_calls)
                {
                    $l_link = "javascript:tree_obj_type_click('" . $l_typedata["isys_obj_type__id"] . "');";
                }
                else
                {
                    $l_link = $g_config["startpage"] . isys_helper_link::create_url($l_gets);
                } // if

                $l_title = isys_glob_escape_string(isys_helper::sanitize_text(_L($l_dao->get_objtype_name_by_id_as_string($l_typedata["isys_obj_type__id"]))));

                $this->m_tree->add_node(
                    $l_typedata["isys_obj_type__id"],
                    $l_root,
                    '<span' . ($l_typedata["objcount"] > 0 ? '' : ' class="obj_noentries"') . '>' . $l_title . '</span> <span>(' . $l_typedata["objcount"] . ')</span>',
                    $l_link,
                    '',
                    $l_icon,
                    $l_issel,
                    '',
                    '',
                    true,
                    $l_typedata["isys_obj_type__const"]
                );
            } // foreach

            $l_settings = isys_component_dao_user::instance($l_dao->get_database_component())
                ->get_user_settings();

            if (!($l_settings['isys_user_ui__tree_visible'] & 1))
            {
                $l_tpl->assign('treeHide', 1);
            }
            else
            {
                $l_tpl->assign('treeHide', 0);
            } // if

            $this->m_tree->set_tree_sort(false);
        } // if

        // Sets the eye for hiding empty nodes
        $this->m_tree->set_tree_visibility(true);

        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.extendObjectTypeTree", $this->m_tree);
    } // function

    /**
     *
     * @return  string
     */
    public function tree_process()
    {
        $l_proc = '';

        if (defined("C__OBJECT_DRAGNDROP") && C__OBJECT_DRAGNDROP)
        {
            $l_proc = "init_drops();";
        } // if

        return $this->m_tree->process(null, $l_proc);
    } // function
} // class