<?php

/**
 * Action: Object type configuration
 *
 * @package    i-doit
 * @subpackage CMDB_Actions
 * @author     i-doit-team
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_action_config_objecttype implements isys_cmdb_action
{
    /**
     * Handle method.
     *
     * @param  isys_cmdb_dao $p_dao
     * @param  array         $p_data
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_arr2 = [];

        /**
         * @var isys_cmdb_action_processor
         */
        $l_actionproc = $p_data["__ACTIONPROC"];

        list($l_navmode, $l_type_id, $l_posts) = $p_data;

        if ($l_posts['assigned_categories'])
        {
            $l_arr   = $l_posts['assigned_categories'];
            $l_arr[] = 'C__CATG__GLOBAL';
        }
        else
        {
            $l_arr = ['C__CATG__GLOBAL'];
        }

        if ($l_type_id != C__OBJTYPE__PERSON && $l_type_id != C__OBJTYPE__PERSON_GROUP && $l_type_id != C__OBJTYPE__ORGANIZATION)
        {
            $l_arr2[] = 'C__CATG__GLOBAL';
        }
        if ($l_posts['assigned_cat_overview'])
        {
            $l_arr2 = array_merge($l_arr2, $l_posts['assigned_cat_overview']);
        }

        switch ($l_navmode)
        {
            case C__NAVMODE__NEW:
                isys_auth_cmdb::instance()
                    ->check(isys_auth::EDIT, 'OBJ_TYPE');

                try
                {
                    $l_objtypeid = $p_dao->insert_new_objtype($l_type_id);

                    if ($l_objtypeid !== null)
                    {
                        $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECTTYPE_CREATED", isys_glob_get_param("LogbookCommentary"), null, $l_objtypeid);

                        $l_actionproc->result_push($l_objtypeid);
                    } // if
                }
                catch (isys_exception_dao_cmdb $l_e)
                {
                    echo $l_e->getMessage();
                    die;
                } // try

                break;

            case C__NAVMODE__SAVE:
                $l_obj_type = $p_dao->get_object_type($l_type_id);
                isys_auth_cmdb::instance()
                    ->check(isys_auth::EDIT, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']);

                try
                {
                    isys_component_signalcollection::get_instance()
                        ->emit("mod.cmdb.beforeObjectTypeSave", $l_type_id, $l_posts);

                    $l_bRet = $p_dao->update_objtype_by_id($l_type_id, $l_arr, $l_arr2, $l_posts);

                    isys_component_signalcollection::get_instance()
                        ->emit("mod.cmdb.afterObjectTypeSave", $l_type_id, $l_posts, $l_bRet);

                    if ($l_bRet)
                    {
                        $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED", isys_glob_get_param("LogbookCommentary"), null, $l_type_id);
                    }
                    else
                    {
                        $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECTTYPE_CHANGED__NOT", isys_glob_get_param("LogbookCommentary"), null, $l_type_id);
                    } // if
                }
                catch (isys_exception_dao_cmdb $l_e)
                {
                    die($l_e->getMessage());
                } // try
                break;

            case C__NAVMODE__ARCHIVE:
            case C__NAVMODE__DELETE:
            case C__NAVMODE__PURGE:
                // Delete object type: You can only delete self-defined object types which have currently no objects associated!
                $l_obj_type = $p_dao->get_object_type($l_type_id);

                if ($l_navmode == C__NAVMODE__ARCHIVE)
                {
                    if (!isys_auth_cmdb::instance()
                            ->is_allowed_to(isys_auth::ARCHIVE, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']) && !isys_auth_cmdb::instance()
                            ->is_allowed_to(isys_auth::DELETE, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const'])
                    )
                    {
                        throw new isys_exception_auth(
                            _L(
                                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_TYPE',
                                [
                                    isys_auth::get_right_name(isys_auth::ARCHIVE),
                                    _L($l_obj_type['isys_obj_type__title'])
                                ]
                            )
                        );
                    } // if
                }
                else
                {
                    isys_auth_cmdb::instance()
                        ->check(isys_auth::DELETE, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']);
                } // if

                if (is_array($l_posts["id"]))
                {
                    foreach ($l_posts["id"] as $l_val)
                    {
                        $l_objTypeData     = $p_dao->get_objtype($l_val)
                            ->get_row();
                        $l_strObjTypeTitle = $l_objTypeData['isys_obj_type__title'];

                        try
                        {
                            isys_component_signalcollection::get_instance()
                                ->emit("mod.cmdb.beforeObjectTypePurge", $l_type_id, $l_strObjTypeTitle, $l_objTypeData);

                            $l_result = $p_dao->delete_object_type($l_val);

                            $l_mod_event_manager->triggerCMDBEvent(
                                "C__LOGBOOK_EVENT__OBJECTTYPE_PURGED",
                                isys_glob_get_param("LogbookCommentary"),
                                null,
                                $l_type_id,
                                $l_strObjTypeTitle
                            );

                            isys_component_signalcollection::get_instance()
                                ->emit("mod.cmdb.afterObjectTypePurge", $l_type_id, $l_strObjTypeTitle, $l_result, $l_objTypeData);
                        }
                        catch (Exception $e)
                        {
                            isys_application::instance()->container['notify']->error("Delete failed: " . $e->getMessage());

                            $l_mod_event_manager->triggerCMDBEvent(
                                "C__LOGBOOK_EVENT__OBJECTTYPE_PURGED__NOT",
                                isys_glob_get_param("LogbookCommentary"),
                                null,
                                $l_type_id,
                                $l_strObjTypeTitle
                            );
                        } // try
                    } // foreach
                } // if
                break;
        } // switch
    } // function
} // class