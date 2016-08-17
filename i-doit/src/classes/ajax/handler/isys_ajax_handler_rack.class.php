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
 * @since       0.9.9-9
 */
class isys_ajax_handler_rack extends isys_ajax_handler
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

        switch ($_GET['func'])
        {
            case 'assign_object_to_rack':
                $l_return = $this->assign_object_to_rack();
                break;

            case 'detach_object_from_rack':
                $l_return = $this->detach_object_from_rack($_POST['obj_id']);
                break;

            case 'get_free_slots':
                $l_return = $this->get_free_slots();
                break;

            case 'get_free_slots_for_location':
                $l_return = $this->get_free_slots_for_location();
                break;

            case 'get_racks_recursive':
                $l_return = $this->get_racks_recursive($_POST['obj_id']);
                break;

            case 'get_rack_options':
                $l_return = $this->get_rack_options();
                break;

            case 'get_rack_insertions':
                $l_return = $this->get_rack_insertions($_POST['option']);
                break;

            case 'pos':
                $l_return = $this->get_positions_in_rack($_POST['obj_id']);
                break;

            case 'remove_object_assignment':
                $l_return = $this->remove_object_assignment();
                break;

            case 'save_object_ru':
                $l_return = $this->save_object_ru($_POST['obj_id'], $_POST['height']);
                break;

            case 'save_position_in_location':
                $l_return = $this->save_position_in_location($_POST['positions']);
                break;
        } // switch

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Method for assigning an object to a rack (position and insertion).
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function assign_object_to_rack()
    {
        $l_pos = $_POST['pos'];

        isys_cmdb_dao_location::instance($this->m_database_component)
            ->update_position($_POST['obj_id'], $_POST['option'], $_POST['insertion'], $l_pos);

        return array_values($this->get_assigned_objects($_POST['rack_obj_id']));
    } // function

    /**
     * Method for detaching an objects location.
     *
     * @param   integer $p_obj
     *
     * @return  array
     */
    protected function detach_object_from_rack($p_obj)
    {
        $l_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);

        $l_row = $l_dao->get_data(null, $p_obj)
            ->get_row();

        try
        {
            // Use save method. Whole location entry has to be updated
            $l_dao->save(
                $l_row['isys_catg_location_list__id'],
                $p_obj,
                null,
                $l_row['isys_catg_location_list__parentid'],
                null,
                null,
                null, // This can be removed
                $l_row['isys_catg_location_list__description'],
                $l_row['isys_catg_location_list__option']
            );

            return ['success' => true];
        }
        catch (Exception $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } // try
    } // function

    /**
     * Receive the assigned objects of a given rack in a certain format.
     *
     * @param   integer $p_rack_id The object-id of the rack.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_assigned_objects($p_rack_id)
    {
        $l_cmdb_res = isys_cmdb_dao_status::instance($this->m_database_component)
            ->get_cmdb_status();
        $l_objects  = $l_cmdb_status = [];

        while ($l_row = $l_cmdb_res->get_row())
        {
            $l_cmdb_status[$l_row['isys_cmdb_status__id']] = [
                'color' => '#' . $l_row['isys_cmdb_status__color'],
                'title' => isys_glob_htmlentities(_L($l_row['isys_cmdb_status__title']))
            ];
        } // while

        $l_res = isys_cmdb_dao_location::instance($this->m_database_component)
            ->get_location($p_rack_id, null, C__RECORD_STATUS__NORMAL, null, false, ' AND isys_obj_type__show_in_rack = 1');

        // We prepare all objects in one array, so we can just assign them later on.
        while ($l_row = $l_res->get_row())
        {
            $l_obj_formfactor = ['isys_catg_formfactor_type__title' => isys_tenantsettings::get('gui.empty_value', '-')];

            if ($l_row['isys_catg_formfactor_list__isys_catg_formfactor_type__id'] > 0)
            {
                $l_obj_formfactor = isys_factory_cmdb_dialog_dao::get_instance('isys_catg_formfactor_type', $this->m_database_component)
                    ->get_data($l_row['isys_catg_formfactor_list__isys_catg_formfactor_type__id']);
            } // if

            $l_rack_units                      = $l_row['isys_catg_formfactor_list__rackunits'] ?: 1;
            $l_objects[$l_row['isys_obj__id']] = [
                'id'          => $l_row['isys_obj__id'],
                'title'       => $l_row['isys_obj__title'],
                'type'        => _L($l_row['isys_obj_type__title']),
                'formfactor'  => isys_glob_htmlentities($l_obj_formfactor['isys_catg_formfactor_type__title']),
                'icon'        => $l_row['isys_obj_type__icon'],
                'color'       => '#' . $l_row['isys_obj_type__color'],
                'cmdb_color'  => $l_cmdb_status[$l_row['isys_obj__isys_cmdb_status__id']]['color'],
                'cmdb_status' => $l_cmdb_status[$l_row['isys_obj__isys_cmdb_status__id']]['title'],
                'height'      => $l_rack_units,
                'option'      => $l_row['isys_catg_location_list__option'],
                'insertion'   => $l_row['isys_catg_location_list__insertion'],
                'pos'         => $l_row['isys_catg_location_list__pos']
            ];
        } // while

        return $l_objects;
    } // function

    /**
     * Returns the available slots inside a rack.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_free_slots()
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_free_rackslots($_POST['rack_obj_id'], $_POST['insertion'], $_POST['assign_obj_id'], $_POST['option']);
    } // function

    /**
     * Returns the available slots inside a rack. Slightly differs to "get_free_slots": This method always returns ascending values.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_free_slots_for_location()
    {
        $l_return = [];

        $l_rack_formfactor = isys_cmdb_dao_category_g_formfactor::instance($this->m_database_component)
            ->get_data(null, $_POST['rack_obj_id'])
            ->get_row();

        $l_rack_sorting = isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
            ->get_data(null, $_POST['rack_obj_id'])
            ->get_row();

        $l_slots = isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_free_rackslots($_POST['rack_obj_id'], $_POST['insertion'], $_POST['assign_obj_id'], $_POST['option']);

        if ($_POST['option'] == C__RACK_INSERTION__HORIZONTAL && $l_rack_sorting['isys_cats_enclosure_list__slot_sorting'] == 'desc')
        {
            foreach ($l_slots as $l_key => $l_slot)
            {
                $l_key = $l_rack_formfactor['isys_catg_formfactor_list__rackunits'] - current(explode(';', $l_key)) + 1;

                $l_return[$l_key] = $l_slot;
            } // foreach

            return $l_return;
        } // if

        return $l_slots;
    } // function

    /**
     * Returns the options for the "position in rack" dialog.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_positions_in_rack($p_obj_id)
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_positions_in_rack($p_obj_id);
    } // function

    /**
     * We use this method to find out if the given rack is capable of vertical-slots.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_rack_options()
    {
        $l_dao         = isys_cmdb_dao::instance($this->m_database_component);
        $l_objecttypes = $l_dao->get_object_types_by_category(C__CATS__ENCLOSURE, 's', false);

        $l_location_objtype_id = $l_dao->get_objTypeID($_POST['obj_id']);

        if (!in_array($l_location_objtype_id, $l_objecttypes)) return false;

        $l_return  = [];
        $l_options = isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->callback_property_assembly_options(
                isys_request::factory()
                    ->set_row(['isys_catg_location_list__parentid' => $_POST['obj_id']])
            );

        foreach ($l_options as $l_option_id => $l_option)
        {
            $l_return[] = [
                'id'    => $l_option_id,
                'title' => $l_option
            ];
        } // foreach

        return $l_return;
    } // function

    /**
     * Method for retrieving the "front", "rear" and "both" data.
     *
     * @param   integer $p_option Defines if you need the insertion-options for horizontal or vertical assignment.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_rack_insertions($p_option)
    {
        $l_insertions = isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->callback_property_insertion(isys_request::factory());

        $l_return = [
            [
                'id'    => C__RACK_INSERTION__FRONT,
                'title' => $l_insertions[C__RACK_INSERTION__FRONT]
            ],
            [
                'id'    => C__RACK_INSERTION__BACK,
                'title' => $l_insertions[C__RACK_INSERTION__BACK]
            ]
        ];

        if ($p_option == C__RACK_INSERTION__HORIZONTAL)
        {
            $l_return[] = [
                'id'    => C__RACK_INSERTION__BOTH,
                'title' => $l_insertions[C__RACK_INSERTION__BOTH]
            ];
        } // if

        return $l_return;
    } // function

    /**
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_racks_recursive($p_obj_id)
    {
        $l_loc_dao  = isys_cmdb_dao_location::instance($this->m_database_component);
        $l_rack_dao = isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component);

        $l_return = [];

        $l_objects = $l_loc_dao->get_child_locations_recursive($p_obj_id);

        foreach ($l_objects as $l_object)
        {
            // We only want to load racks, so we check for the object-type.
            if ($l_object['isys_obj__isys_obj_type__id'] == C__OBJTYPE__ENCLOSURE && $l_object['isys_obj__id'] > 0 && $l_object['parent'] != $p_obj_id)
            {
                $l_return[] = $l_rack_dao->prepare_rack_data($l_object['isys_obj__id'], $l_object['isys_obj__title']);
            } // if
        } // while

        return $l_return;
    } // function

    /**
     * Method for removing an object out of the rack.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function remove_object_assignment()
    {
        isys_cmdb_dao_location::instance($this->m_database_component)
            ->update_position($_POST['obj_id']);

        return array_values($this->get_assigned_objects($_POST['rack_obj_id']));
    } // function

    /**
     * Method for saving the rack-units of an object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_height
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function save_object_ru($p_obj_id, $p_height)
    {
        // Retrieve the formfactor DAO.
        $l_dao = isys_cmdb_dao_category_g_formfactor::instance($this->m_database_component);

        // And get the data.
        $l_row = $l_dao->get_data(null, $p_obj_id)
            ->get_row();

        if (empty($l_row))
        {
            $l_return = ($l_dao->create_data(
                    [
                        'isys_obj__id' => $p_obj_id,
                        'rackunits'    => $p_height
                    ]
                ) !== false);
        }
        else
        {
            $l_return = $l_dao->save_data($l_row['isys_catg_formfactor_list__id'], ['rackunits' => $p_height]);
        } // if

        return [
            'success'    => $l_return,
            'new_height' => $p_height
        ];
    } // function

    protected function save_position_in_location($p_positions)
    {
        return [
            'success' => isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
                ->save_position_in_location(isys_format_json::decode($p_positions))
        ];
    } // function
} // class