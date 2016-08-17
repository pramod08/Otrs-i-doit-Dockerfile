<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_ajax_handler_quick_configuration_wizard extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];
        $l_dao    = new isys_module_quick_configuration_wizard();

        try
        {
            switch ($_POST['func'])
            {
                case 'remove_group_assignment':
                    $l_return = [
                        'success' => $l_dao->call_dao_method(
                            'objtype_change_status',
                            [
                                0,
                                $_POST['id'],
                                false
                            ]
                        )
                    ];
                    break;

                case 'objtypegroup_change_status':
                    $l_return = [
                        'success' => $l_dao->call_dao_method(
                            'objtypegroup_change_status',
                            [
                                $_POST['id'],
                                (bool) $_POST['status']
                            ]
                        )
                    ];
                    break;

                case 'objtypegroup_change_sorting':
                    $l_return = ['success' => $l_dao->call_dao_method('objtypegroup_change_sorting', [$_POST['sorting']])];
                    break;

                case 'objtypegroup_new':
                    $l_return = [
                        'success'  => true,
                        'constant' => $l_dao->call_dao_method('objtypegroup_new', [$_POST['title']])
                    ];
                    break;

                case 'objtypegroup_save':
                    $l_return = [
                        'success' => $l_dao->call_dao_method(
                            'objtypegroup_save',
                            [
                                $_POST['id'],
                                $_POST['title']
                            ]
                        )
                    ];
                    break;

                case 'objtypegroup_delete':
                    $l_return = ['success' => $l_dao->call_dao_method('objtypegroup_delete', [$_POST['id']])];
                    break;

                case 'objecttype_list':
                    $l_return = $l_dao->call_dao_method('load_objecttypes', [$_POST['objTypeGroup']]);
                    break;

                // Older methods, may still be used.
                case 'objtype_change_status':
                    $l_return = [
                        'success' => $l_dao->call_dao_method(
                            'objtype_change_status',
                            [
                                $_POST['group_id'],
                                $_POST['id'],
                                (bool) $_POST['status']
                            ]
                        )
                    ];
                    break;

                case 'objtype_change_sorting':
                    $l_return = ['success' => $l_dao->call_dao_method('objtype_change_sorting', [$_POST['sorting']])];
                    break;

                case 'objtype_new':
                    $l_return = [
                        'success'  => true,
                        'constant' => $l_dao->call_dao_method(
                            'objtype_new',
                            [
                                $_POST['title'],
                                $_POST['container'],
                                $_POST['insertion']
                            ]
                        )
                    ];
                    break;

                case 'objtype_save':
                    $l_return = [
                        'success' => $l_dao->call_dao_method(
                            'objtype_save',
                            [
                                $_POST['id'],
                                $_POST['title'],
                                $_POST['container'],
                                $_POST['insertion']
                            ]
                        )
                    ];
                    break;

                case 'objtype_delete':
                    $l_return = ['success' => $l_dao->call_dao_method('objtype_delete', [$_POST['id']])];
                    break;

                case 'category_list':
                    $l_return = $l_dao->call_dao_method('load_assigned_categories', [$_POST['obj_type']]);
                    break;

                case 'category_change_status':
                    $l_return = [
                        'success' => $l_dao->call_dao_method(
                            'category_change_status',
                            [
                                $_POST['obj_type_id'],
                                $_POST['id'],
                                (bool) $_POST['status'],
                                (bool) $_POST['catg']
                            ]
                        )
                    ];
                    break;

                // Deletion and resetting.
                case 'delete_file':
                    $l_return = isys_module_quick_configuration_wizard::delete_config_file($_POST['file']);
                    break;

                case 'reset':
                    $l_return = $this->reset_configuration();
                    break;

                case 'load_profiles':
                    $l_selection = isys_format_json::decode($_POST['profile_files']);

                    if (count($l_selection) > 0)
                    {
                        $l_caching = isys_caching::factory('objecttypeconfig');
                        try
                        {
                            $l_dao->load_config($l_selection, $l_caching);
                            $l_caching->clear();
                            isys_caching::factory()
                                ->delete_all_except(
                                    [
                                        'getdataconditionless',
                                        'validationconfig'
                                    ]
                                );
                            $l_return['success'] = true;
                        }
                        catch (isys_exception_general $e)
                        {
                            $l_return['success'] = false;
                        } // try
                    } // if
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } // try

        // We want to go sure, our changes will appear, so we clear the menu cache.
        isys_caching::factory()
            ->delete_all_except(
                [
                    'getdataconditionless',
                    'validationconfig'
                ]
            );

        echo isys_format_json::encode($l_return);
        $this->_die();
    } // function

    /**
     * Reset the configuration and set the object-type-groups to default visibility and sorting.
     *
     * @return  array
     * @author  Leonard Fischer
     */
    protected function reset_configuration()
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database_component);

        $l_skipped_categories = isys_quick_configuration_wizard_dao::get_skipped_objecttypes();

        $l_skipped_categories_string = '"' . implode('","', $l_skipped_categories) . '"';

        // 1 - Remove all custom categories from all object types.
        if (!$l_dao->update('DELETE FROM isys_obj_type_2_isysgui_catg_custom WHERE TRUE;'))
        {
            return [
                'success' => false,
                'message' => isys_glob_utf8_encode(_L('LC__MODULE__QCW__RESET_ERROR__CUSTOM_CAT_ASSIGNMENT'))
            ];
        } // if

        // 2 - Remove all global categories from all object types. But keep some necessary (global, relation, logbook, ...)
        $l_sql = 'DELETE isys_obj_type_2_isysgui_catg FROM isys_obj_type_2_isysgui_catg
			LEFT JOIN isysgui_catg ON isys_obj_type_2_isysgui_catg__isysgui_catg__id = isysgui_catg__id
			LEFT JOIN isys_obj_type ON isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type__id
			WHERE isysgui_catg__const NOT IN ("C__CATG__GLOBAL", "C__CATG__RELATION", "C__CATG__LOGBOOK")
			AND isys_obj_type__const NOT IN (' . $l_skipped_categories_string . ')';

        if (!$l_dao->update($l_sql))
        {
            return [
                'success' => false,
                'message' => isys_glob_utf8_encode(_L('LC__MODULE__QCW__RESET_ERROR__GLOBAL_CAT_ASSIGNMENT'))
            ];
        } // if

        // 3 - Remove all possible categories (without: global, relation, logbook, ...) from the overview.
        $l_sql = 'DELETE isys_obj_type_2_isysgui_catg_overview FROM isys_obj_type_2_isysgui_catg_overview
			LEFT JOIN isysgui_catg ON isysgui_catg.isysgui_catg__id = isys_obj_type_2_isysgui_catg_overview.isysgui_catg__id
			LEFT JOIN isys_obj_type ON isys_obj_type.isys_obj_type__id = isys_obj_type_2_isysgui_catg_overview.isys_obj_type__id
			WHERE isysgui_catg__const NOT IN ("C__CATG__GLOBAL", "C__CATG__RELATION", "C__CATG__LOGBOOK")
			AND isys_obj_type__const NOT IN (' . $l_skipped_categories_string . ')';

        if (!$l_dao->update($l_sql))
        {
            return [
                'success' => false,
                'message' => isys_glob_utf8_encode(_L('LC__MODULE__QCW__RESET_ERROR__OVERVIEW_CAT_ASSIGNMENT'))
            ];
        } // if

        // 4 - Remove all object types from all object type groups.
        $l_sql = 'UPDATE isys_obj_type
			SET isys_obj_type__isys_obj_type_group__id = NULL,
			isys_obj_type__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ',
			isys_obj_type__show_in_tree = 0
			WHERE TRUE;';

        if (!$l_dao->update($l_sql))
        {
            return [
                'success' => false,
                'message' => isys_glob_utf8_encode(_L('LC__MODULE__QCW__RESET_ERROR__OBJ_TYPE_ASSIGNMENT'))
            ];
        } // if

        // 5 - Display no object type groups in the menu, but the "standards" (software, infrastructure, ...). Also reset the sorting.
        $l_sql = 'UPDATE isys_obj_type_group
			SET isys_obj_type_group__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ',
			isys_obj_type_group__sort = isys_obj_type_group__id
			WHERE TRUE;';

        if (!$l_dao->update($l_sql))
        {
            return [
                'success' => false,
                'message' => isys_glob_utf8_encode(_L('LC__MODULE__QCW__RESET_ERROR__OBJ_TYPE_GROUP_RESET'))
            ];
        } // if

        $l_sql = 'UPDATE isys_obj_type_group
			SET isys_obj_type_group__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			WHERE isys_obj_type_group__const IN ("C__OBJTYPE_GROUP__SOFTWARE", "C__OBJTYPE_GROUP__INFRASTRUCTURE", "C__OBJTYPE_GROUP__OTHER", "C__OBJTYPE_GROUP__CONTACT");';

        if (!$l_dao->update($l_sql))
        {
            return [
                'success' => false,
                'message' => isys_glob_utf8_encode(_L('LC__MODULE__QCW__RESET_ERROR__OBJ_TYPE_GROUP_VISIBILITY'))
            ];
        } // if

        return [
            'success' => true
        ];
    } // function
} // class