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
 * UI: specific category for the basic auth-system implementation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 * @author      Leonard Fischer <lfischer@i-doit.com>
 */
class isys_cmdb_ui_category_s_basic_auth extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_basic_auth $p_cat
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id   = $_GET[C__CMDB__GET__OBJECT];
        $l_obj_type = $p_cat->get_objTypeID($l_obj_id);

        isys_auth_auth::instance()
            ->check(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH');

        // Collect the available modules.
        $l_modules    = [];
        $l_module_res = isys_module_manager::instance()
            ->get_modules();

        if (count($l_module_res) > 0)
        {
            while ($l_row = $l_module_res->get_row())
            {
                $l_auth_instance = isys_module_manager::instance()
                    ->get_module_auth($l_row['isys_module__id']);

                // We only want to select modules, which have their own auth-classes.
                if ($l_auth_instance)
                {
                    $l_modules[$l_row['isys_module__const']] = isys_glob_utf8_encode(_L($l_row['isys_module__title']));
                } // if
            } // while
        } // if

        // Collect the available rights.
        $l_rights = isys_auth::get_rights();

        foreach ($l_rights as &$l_right)
        {
            $l_right['title'] = isys_glob_utf8_encode($l_right['title']);
        } // foreach

        // Collect the paths.
        $l_paths = [];
        $l_res   = $p_cat->get_data(null, $l_obj_id);

        while ($l_row = $l_res->get_row())
        {
            if (!in_array($l_row['isys_auth__type'], (array) $l_paths[$l_row['isys_module__const']]))
            {
                if (is_array($l_paths[$l_row['isys_module__const']]))
                {
                    $l_paths[$l_row['isys_module__const']] = array_merge($l_paths[$l_row['isys_module__const']], isys_helper::split_bitwise($l_row['isys_auth__type']));
                }
                else
                {
                    $l_paths[$l_row['isys_module__const']] = isys_helper::split_bitwise($l_row['isys_auth__type']);
                } // if

                // The "array_unique" is not necessary, but it shrinks the array immensely!
                $l_paths[$l_row['isys_module__const']] = array_unique($l_paths[$l_row['isys_module__const']]);
            } // if
        } // while

        // Now collect the inherited paths of persongroups (if the current object is a person).
        $l_inherited_paths = [];

        // @todo Maybe this "check" should look for the specific category instead of the object-type.
        if ($l_obj_type == C__OBJTYPE__PERSON)
        {
            $l_pg_dao = new isys_cmdb_dao_category_s_person_assigned_groups($this->get_database_component());

            $l_pg_res = $l_pg_dao->get_data(null, $l_obj_id);

            while ($l_row = $l_pg_res->get_row())
            {
                $l_res = $p_cat->get_data(null, $l_row['isys_person_2_group__isys_obj__id__group']);

                while ($l_row2 = $l_res->get_row())
                {
                    if (is_array($l_inherited_paths[$l_row2['isys_module__const']]))
                    {
                        $l_inherited_paths[$l_row2['isys_module__const']] = array_merge(
                            $l_inherited_paths[$l_row2['isys_module__const']],
                            isys_helper::split_bitwise($l_row2['isys_auth__type'])
                        );
                    }
                    else
                    {
                        $l_inherited_paths[$l_row2['isys_module__const']] = isys_helper::split_bitwise($l_row2['isys_auth__type']);
                    } // if

                    // The "array_unique" is not necessary, but it shrinks the array immensely!
                    $l_inherited_paths[$l_row2['isys_module__const']] = array_unique($l_inherited_paths[$l_row2['isys_module__const']]);
                } // while
            } // while
        } // if

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('rights', isys_format_json::encode($l_rights))
            ->assign('modules', isys_format_json::encode($l_modules))
            ->assign('paths', isys_format_json::encode($l_paths))
            ->assign('inherited_paths', isys_format_json::encode($l_inherited_paths))
            ->assign('edit_mode', (int) isys_glob_is_edit_mode());
    } // function
} // class