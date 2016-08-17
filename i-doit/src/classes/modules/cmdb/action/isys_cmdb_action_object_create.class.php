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
 * Action: Object creation
 *
 * @package     i-doit
 * @subpackage  CMDB_Actions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_action_object_create implements isys_cmdb_action
{
    /**
     * Create object handler.
     *
     * @param  isys_cmdb_dao $p_dao
     * @param  array         &$p_data
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_gets              = isys_module_request::get_instance()
            ->get_gets();

        list($p_objtype_id) = $p_data;

        /**
         * @var isys_cmdb_action_processor
         */
        $l_actionproc = $p_data['__ACTIONPROC'];

        $l_obj_type = $p_dao->get_object_type($p_objtype_id);

        isys_auth_cmdb::instance()
            ->check(isys_auth::EDIT, 'OBJ_IN_TYPE/' . $l_obj_type['isys_obj_type__const']);

        try
        {
            $l_default_template = $p_dao->get_default_template_by_obj_type($p_objtype_id);

            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.beforeInsertObject", $p_dao, $p_objtype_id, $l_default_template);

            // Create the object.
            $l_new_objid = $p_dao->insert_new_obj($p_objtype_id, false);

            if ($l_default_template)
            {
                $l_obj_title = $p_dao->obj_get_title_by_id_as_string($l_default_template);

                $l_obj_data   = $p_dao->get_type_by_object_id($l_new_objid)
                    ->get_row();
                $l_obj_status = $l_obj_data['isys_obj__status'];
                $l_obj_sysid  = $l_obj_data['isys_obj__sysid'];
                $l_obj_type   = $l_obj_data['isys_obj__isys_obj_type__id'];

                // Update created object with the object title with placeholders if there are any
                if (isys_cmdb_dao_category_g_accounting::has_placeholders($l_obj_title) && $l_obj_status !== C__RECORD_STATUS__MASS_CHANGES_TEMPLATE)
                {
                    $l_obj_title = isys_cmdb_dao_category_g_accounting::instance($p_dao->get_database_component())
                        ->replace_placeholders(
                            $l_obj_title,
                            $l_new_objid,
                            (($p_objtype_id === null) ? $l_obj_type : $p_objtype_id),
                            null,
                            $l_obj_sysid,
                            'isys_catg_global_list'
                        );
                    $p_dao->update_object($l_new_objid, null, $l_obj_title);
                } // if
            } // if

            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.afterInsertObject", $p_dao, $p_objtype_id, $l_new_objid);
        }
        catch (Exception $e)
        {
            isys_glob_display_error($e->getMessage());
            die;
        } // try

        if ($l_new_objid != -1)
        {
            $l_mod_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CREATED', '-object initialized-', $l_new_objid, $p_objtype_id);
        }
        else
        {
            $l_mod_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CREATED__NOT', '', null, $p_objtype_id);
        } // if

        $l_gets[C__CMDB__GET__OBJECT] = $l_new_objid;
        isys_module_request::get_instance()
            ->_internal_set_private('m_get', $l_gets);

        if (method_exists($l_actionproc, 'result_push')) $l_actionproc->result_push($l_new_objid);
    } // function
} // class