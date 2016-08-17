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
 * UI: class for global category "auth".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 */
class isys_cmdb_ui_category_g_virtual_auth extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_virtual_auth $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
            ->set_visible(false, C__NAVBAR_BUTTON__SAVE);

        $l_object_paths = $p_cat->get_object_paths($_GET[C__CMDB__GET__OBJECT]);

        $l_methods  = isys_auth_cmdb::instance()
            ->get_auth_methods();
        $l_rights   = isys_auth::get_rights();
        $l_auth_dao = isys_auth_dao::instance($this->m_database_component);

        foreach ($l_methods as &$l_method)
        {
            // Encode all method titles, because we are going to convert this to JSON.
            $l_method['title'] = isys_glob_utf8_encode($l_method['title']);
        } // foreach

        foreach ($l_rights as &$l_right)
        {
            // Encode all method titles, because we are going to convert this to JSON.
            $l_right['title'] = isys_glob_utf8_encode($l_right['title']);
        } // foreach

        $l_rules['C__CATG__VIRTUAL_AUTH__CONDITION']['p_arData'] = serialize(
            [
                'obj_id'   => _L('LC__CMDB__CATG__AUTH_OBJ_ID_METHOD'),
                'location' => _L('LC__CMDB__CATG__AUTH_LOCATION_METHOD')
            ]
        );

        // Preparing the path-syntax for every person / persongroup.
        $l_person_object_paths = [];

        foreach ($l_object_paths as $l_person_id => $l_person_paths)
        {
            $l_person_object_paths[$l_person_id] = [
                'person' => $p_cat->get_object_by_id($l_person_id)
                    ->get_row(),
                'paths'  => isys_format_json::encode($l_auth_dao->build_paths_by_array($l_person_paths))
            ];
        } // foreach

        $l_cleaned_get = $_GET;
        unset($l_cleaned_get['call'], $l_cleaned_get['ajax']);

        $this->deactivate_commentary()
            ->get_template_component()// Used to serve as "default" right.
            ->assign('view', isys_auth::VIEW)
            ->assign('paths', $l_person_object_paths)
            ->assign('rights', isys_auth::get_rights())
            ->assign('auth_rights', isys_format_json::encode($l_rights))
            ->assign('auth_methods', isys_format_json::encode($l_methods))
            ->assign(
                'ajax_url',
                '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__AUTH . '&' . C__GET__SETTINGS_PAGE . '=C__MODULE__CMDB&ajax=1'
            )
            ->assign('editmode', isys_glob_is_edit_mode())
            ->assign('auth_wildchar', isys_auth::WILDCHAR)
            ->assign('auth_empty_id', isys_auth::EMPTY_ID_PARAM)
            ->assign('save_ajax_url', '?call=auth&ajax=1&func=create_new_path_by_category')
            ->assign('obj_id', $_GET[C__CMDB__GET__OBJECT])
            ->assign('reload_url', isys_glob_build_url(isys_glob_http_build_query($l_cleaned_get)))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        // Only supervisors may see and edit this category.
        isys_auth_auth::instance()
            ->check(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__CMDB');
    } // function
} // class
?>