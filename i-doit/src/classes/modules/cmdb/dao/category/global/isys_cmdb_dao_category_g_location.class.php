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
 * DAO: global category for locations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_location extends isys_cmdb_dao_category_global
{
    /**
     * Location cache (including parent objects).
     *
     * @var  array
     */
    protected static $m_location_cache = [];
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'location';
    /**
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_catg_location_list__parentid';

    /**
     * @var  boolean
     */
    protected $m_has_relation = true;
    /**
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_location_list__isys_obj__id';

    /**
     * Static method for checking if a given slot is free.
     *
     * @static
     *
     * @param   array   $p_used_slots
     * @param   integer $p_slot
     * @param   integer $p_insertion
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected static function slot_available($p_used_slots, $p_slot, $p_insertion)
    {
        if ($p_insertion == C__RACK_INSERTION__BOTH && (in_array($p_slot . '-' . C__RACK_INSERTION__FRONT, $p_used_slots) || in_array(
                    $p_slot . '-' . C__RACK_INSERTION__BACK,
                    $p_used_slots
                ))
        )
        {
            return false;
        } // if

        if (in_array($p_slot . '-' . $p_insertion, $p_used_slots) || in_array($p_slot . '-' . C__RACK_INSERTION__BOTH, $p_used_slots))
        {
            return false;
        } // if

        return true;
    } // function

    /**
     * Export Helper for property longitude for global category location.
     *
     * @param  mixed $p_value
     * @param  array $p_row
     *
     * @return string
     */
    public function property_callback_longitude($p_value, $p_row)
    {
        return $p_row['longitude'] ?: '';
    } // function

    /**
     * Export Helper for property latitude for global category location.
     *
     * @param  mixed $p_value
     * @param  array $p_row
     *
     * @return string
     */
    public function property_callback_latitude($p_value, $p_row = [])
    {
        return $p_row['latitude'] ?: '';
    } // function

    /**
     * Return complete location path.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_location_path($p_row)
    {
        global $g_dirs;

        if (!isset($p_row['isys_catg_location_list__parentid']))
        {
            $l_parentid = $this->get_data(null, $p_row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        }
        else
        {
            $l_parentid = $p_row['isys_catg_location_list__parentid'];
        } // if

        if ($l_parentid > 0)
        {
            if (!isset(self::$m_location_cache[C__OBJ__ROOT_LOCATION]))
            {
                self::$m_location_cache[C__OBJ__ROOT_LOCATION] = [
                    'title' => '<img src="' . $g_dirs['images'] . 'icons/silk/house.png" class="vam" title="' . _L('LC__OBJ__ROOT_LOCATION') . '" />',
                    'parent' => null
                ];

                // If the direct parent is not the root location, we display an arrow (just like between the other locations).
                if ($l_parentid != C__OBJ__ROOT_LOCATION)
                {
                    self::$m_location_cache[C__OBJ__ROOT_LOCATION]['title'] .= isys_tenantsettings::get('gui.separator.location', ' > ');
                } // if
            }

            return isys_popup_browser_location::instance()
                ->set_format_exclude_self(true)
                ->set_format_prefix(self::$m_location_cache[C__OBJ__ROOT_LOCATION]['title']) // Fixing ID-2937
                ->format_selection($p_row["__id__"]);
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Return the single location parent of the given object.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_location($p_row)
    {
        if (!isset($p_row['isys_catg_location_list__parentid']))
        {
            $l_parentid = $this->get_data(null, $p_row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        }
        else
        {
            $l_parentid = $p_row['isys_catg_location_list__parentid'];
        } // if

        if ($l_parentid > 0)
        {
            $l_location_row = $this->get_object_by_id($l_parentid)
                ->get_row();

            return isys_factory::get_instance('isys_ajax_handler_quick_info')
                ->get_quick_info(
                    $l_location_row['isys_obj__id'],
                    $l_location_row['isys_obj__title'],
                    C__LINK__OBJECT
                );
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Callback method for the assembly option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assembly_options(isys_request $p_request)
    {
        // Preparing the assembly-options (horizontal/vertical).
        $l_options = [
            C__RACK_INSERTION__HORIZONTAL => _L('LC__CMDB__CATS__ENCLOSURE__HORIZONTAL')
        ];

        if (class_exists('isys_cmdb_dao_category_s_enclosure'))
        {
            $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $p_request->get_row('isys_catg_location_list__parentid'))
                ->get_row();

            if ($l_rack['isys_cats_enclosure_list__vertical_slots_rear'] > 0 || $l_rack['isys_cats_enclosure_list__vertical_slots_front'] > 0)
            {
                $l_options[C__RACK_INSERTION__VERTICAL] = _L('LC__CMDB__CATS__ENCLOSURE__VERTICAL');
            } // if
        } // if

        return $l_options;
    } // function

    /**
     * Callback method for the position dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_pos(isys_request $p_request)
    {
        $l_return = [];

        $l_dao_ff = new isys_cmdb_dao_category_g_formfactor($this->get_database_component());

        $l_by_ajax = $p_request->get_data('ajax', false);

        if ($l_by_ajax)
        {
            $l_rack_obj_id = $p_request->get_object_id();
        }
        else
        {
            $l_catdata     = $this->get_data($p_request->get_category_data_id())
                ->get_row();
            $l_rack_obj_id = $l_catdata['isys_catg_location_list__parentid'];
        } // if

        $l_max_rack_unit = $l_rack_units = $l_dao_ff->get_rack_hu($l_rack_obj_id);

        $l_dao_loc = new isys_cmdb_dao_category_g_location($this->get_database_component());
        $l_res     = $l_dao_loc->get_data(
            null,
            null,
            'AND isys_catg_location_list__parentid = ' . $l_dao_loc->convert_sql_id(
                $l_rack_obj_id
            ) . ' AND isys_catg_location_list__pos > 0 ORDER BY isys_catg_location_list__pos ASC'
        );

        $l_rack = [];

        while ($l_rack_units > 0)
        {
            if (empty($l_row))
            {
                $l_row = $l_res->get_row();
            } // if

            if ($l_rack_units == $l_max_rack_unit - $l_row['isys_catg_location_list__pos'])
            {
                $l_rack_length = $l_dao_ff->get_rack_hu($l_row['isys_catg_location_list__isys_obj__id']);

                switch ($l_row['isys_catg_location_list__insertion'])
                {
                    case C__RACK_INSERTION__BACK:
                        // assigned to back
                        $l_string    = '(' . _L('LC__CATG__LOCATION__BACKSIDE_OCCUPIED') . ')';
                        $l_insertion = C__RACK_INSERTION__BACK;
                        break;

                    case C__RACK_INSERTION__FRONT:
                        // assigned to front
                        $l_string    = '(' . _L('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED') . ')';
                        $l_insertion = C__RACK_INSERTION__FRONT;
                        break;

                    case C__RACK_INSERTION__BOTH:
                        // On both sides
                        $l_string    = '(' . _L('LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED') . ')';
                        $l_insertion = C__RACK_INSERTION__BOTH;
                        break;
                } // switch

                $l_string = isys_glob_utf8_encode($l_string);

                $l_start_from  = $l_rack_units - 1;
                $l_index_start = $l_row['isys_catg_location_list__pos'] + 1;
                while ($l_rack_length > 1)
                {
                    if ($l_by_ajax)
                    {
                        $l_rack[] = [
                            'rack_index' => $l_index_start,
                            'rack_pos'   => $l_start_from + 1,
                            'value'      => $l_string,
                            'insertion'  => $l_insertion
                        ];
                    }
                    else
                    {
                        $l_rack[$l_start_from + 1] = $l_insertion;
                    } // if

                    $l_rack_length--;
                    $l_index_start++;
                    $l_start_from--;
                } // while

                if ($l_by_ajax)
                {
                    $l_rack[] = [
                        'rack_index' => $l_row['isys_catg_location_list__pos'],
                        'rack_pos'   => $l_rack_units + 1,
                        'value'      => $l_string,
                        'insertion'  => $l_insertion
                    ];
                }
                else
                {
                    $l_rack[$l_rack_units + 1] = $l_insertion;
                } // if

                $l_row = $l_res->get_row();
            } // if

            $l_rack_units--;
        } // while

        if ($l_by_ajax)
        {
            return [
                'units'          => $l_max_rack_unit,
                'assigned_units' => $l_rack
            ];
        }

        $l_objDAO       = new isys_cmdb_dao_category_g_formfactor($this->get_database_component());
        $l_nParentObjID = $l_catdata["isys_catg_location_list__parentid"];

        // Get all possible hu positions from rack.
        $l_nHU = $l_objDAO->get_rack_hu($l_nParentObjID);

        for ($i = $l_nHU;$i >= 1;$i--)
        {
            if (array_key_exists($i, $l_rack))
            {
                switch ($l_rack[$i])
                {
                    case C__RACK_INSERTION__FRONT:
                        $l_string = '(' . _L('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED') . ')';
                        break;
                    case C__RACK_INSERTION__BACK:
                        $l_string = '(' . _L('LC__CATG__LOCATION__BACKSIDE_OCCUPIED') . ')';
                        break;
                    case C__RACK_INSERTION__BOTH:
                        $l_string = '(' . _L('LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED') . ')';
                        break;
                } // switch

                $l_return[$l_nHU - $i + 1] = $i . ' ' . $l_string;
            }
            else
            {
                $l_return[$l_nHU - $i + 1] = $i;
            } // if
        } // for

        return $l_return;
    } // function

    /**
     * Callback method for the insertion dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_insertion(isys_request $p_request)
    {
        return [
            C__RACK_INSERTION__FRONT => _L("LC__CMDB__CATG__LOCATION_FRONT"),
            C__RACK_INSERTION__BACK  => _L("LC__CMDB__CATG__LOCATION_BACK"),
            C__RACK_INSERTION__BOTH  => _L("LC__CMDB__CATG__LOCATION_BOTH")
        ];
    } // function

    /**
     * Method for finding a free slot for a given object in a given rack.
     *
     * @param   integer $p_rack_id
     * @param   integer $p_insertion
     * @param   integer $p_object_to_assign
     * @param   integer $p_option
     *
     * @return  array
     */
    public function get_free_rackslots($p_rack_id, $p_insertion, $p_object_to_assign, $p_option)
    {
        $l_return = $l_used_slots = [];

        if (class_exists('isys_cmdb_dao_category_s_enclosure'))
        {
            $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $p_rack_id)
                ->get_row();
        } // if

        $l_obj = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
            ->get_data(null, $p_object_to_assign)
            ->get_row();

        $l_positions = $this->get_positions_in_rack($p_rack_id);

        if ($p_option == C__RACK_INSERTION__HORIZONTAL)
        {
            $l_obj_height = $l_obj['isys_catg_formfactor_list__rackunits'] ?: 1;

            $l_units            = $l_positions['units'];
            $l_assigned_objects = $l_positions['assigned_units'];

            if (is_array($l_assigned_objects))
            {
                // Here we write the used slots in an array, so we can check it later with "in_array()".
                foreach ($l_assigned_objects as $l_slot)
                {
                    if ($l_slot['insertion'] === null || $l_slot['obj_id'] == $p_object_to_assign || $l_slot['option'] == C__RACK_INSERTION__VERTICAL || $l_slot['option'] == null)
                    {
                        continue;
                    } // if

                    if ($l_slot['height'] > 1)
                    {
                        for ($i = 0;$i < $l_slot['height'];$i++)
                        {
                            $l_used_slots[] = ($l_slot['pos'] + $i) . '-' . $l_slot['insertion'];
                        } // for
                    }
                    else
                    {
                        $l_used_slots[] = $l_slot['pos'] . '-' . $l_slot['insertion'];
                    } // if
                } // foreach
            } // if

            for ($i = 1;$i <= $l_units;$i++)
            {
                $l_used_slot = false;

                if ($l_rack['isys_cats_enclosure_list__slot_sorting'] == 'desc')
                {
                    $l_num    = ($l_units - $i) + 1;
                    $l_num_to = $l_num - $l_obj_height + 1;

                    if ($l_num_to < 1)
                    {
                        continue;
                    } // if
                }
                else
                {
                    $l_num    = $i;
                    $l_num_to = $i + $l_obj_height - 1;

                    if ($l_num_to > $l_units)
                    {
                        continue;
                    } // if
                } // if

                // If the current row is in use, we don't need process the next lines.
                if (!self::slot_available($l_used_slots, $i, $p_insertion))
                {
                    continue;
                } // if

                $l_tmp_to = $i + $l_obj_height - 1;

                for ($l_tmp = $i;$l_tmp <= $l_tmp_to;$l_tmp++)
                {
                    if (!self::slot_available($l_used_slots, $l_tmp, $p_insertion))
                    {
                        $l_used_slot = true;
                    } // if
                } // for

                if ($l_used_slot === false)
                {
                    if ($l_obj_height == 1)
                    {
                        $l_return[$l_num . ';' . $l_num] = $l_num . ' ' . _L('LC__CMDB__CATG__RACKUNITS_ABBR');
                    }
                    else
                    {
                        $l_return[$l_num . ';' . $l_num_to] = _L('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $l_num . ' &rarr; ' . $l_num_to;
                    } // if
                } // if
            } // for
        }
        else
        {
            foreach ($l_positions['assigned_units'] as $l_slot)
            {
                if ($l_slot['insertion'] === null || $l_slot['obj_id'] == $p_object_to_assign || $l_slot['option'] == C__RACK_INSERTION__HORIZONTAL || $l_slot['option'] == null)
                {
                    continue;
                } // if

                $l_used_slots[] = $l_slot['pos'] . '-' . $l_slot['insertion'];
            } // foreach

            $l_insertion_pos = ($p_insertion == C__RACK_INSERTION__BACK) ? '_rear' : '_front';

            for ($i = 1;$i <= $l_rack['isys_cats_enclosure_list__vertical_slots' . $l_insertion_pos];$i++)
            {
                $l_num = $i;

                if (self::slot_available($l_used_slots, $l_num, $p_insertion))
                {
                    $l_return[$l_num . ';' . $l_num] = 'Slot #' . $l_num;
                } // if
            } // for
        } // if

        return $l_return;
    } // function

    /**
     * Returns the options for the "position in rack" dialog.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_positions_in_rack($p_obj_id)
    {
        $l_return = [];

        $l_formfactor = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
            ->get_data(null, $p_obj_id)
            ->get_row();

        $l_return['units'] = $l_formfactor['isys_catg_formfactor_list__rackunits'];

        $l_res = isys_factory::get_instance('isys_cmdb_dao_location', $this->get_database_component())
            ->get_location($p_obj_id, null);

        while ($l_row = $l_res->get_row())
        {
            $l_return['assigned_units'][] = [
                'obj_id'    => $l_row['isys_obj__id'],
                'height'    => $l_row['isys_catg_formfactor_list__rackunits'],
                'option'    => $l_row['isys_catg_location_list__option'],
                'pos'       => $l_row['isys_catg_location_list__pos'],
                'insertion' => $l_row['isys_catg_location_list__insertion']
            ];
        } // while

        return $l_return;
    } // function

    /**
     * Method for retrieving the location parent of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @throws  isys_exception_database
     */
    public function get_parent_id_by_object($p_obj_id)
    {
        if ($p_obj_id > 0)
        {
            if (isset(self::$m_location_cache[$p_obj_id]))
            {
                return self::$m_location_cache[$p_obj_id]['parent'];
            } // if

            $l_sql = 'SELECT isys_catg_location_list__parentid AS parent,
				isys_obj__title AS title
				FROM isys_obj
				LEFT JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = isys_obj__id
				WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            $l_parent = $this->retrieve($l_sql)
                ->get_row();

            if (is_array($l_parent))
            {
                self::$m_location_cache[$p_obj_id] = [
                    'title'  => $l_parent['title'],
                    'parent' => $l_parent['parent'] ?: false
                ];
            }
            else
            {
                self::$m_location_cache[$p_obj_id] = [
                    'title'  => null,
                    'parent' => false
                ];
            } // if

            return self::$m_location_cache[$p_obj_id]['parent'];
        } // if

        return false;
    } // function

    /**
     * @param null $p_filter
     * @param int  $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_container_objects($p_filter = null, $p_status = C__RECORD_STATUS__NORMAL, $p_consider_rights = false)
    {
        $l_filter = '';

        if ($p_consider_rights)
        {
            $l_filter = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();
        } // if

        $l_filter .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);

        if ($p_filter !== null)
        {
            $l_filter .= ' AND isys_obj__title LIKE ' . $this->convert_sql_text('%' . $p_filter . '%');
        } // if

        return $this->get_data(null, null, 'AND isys_obj_type__container = 1' . $l_filter, null, $p_status);
    } // function

    /**
     * @param   integer $p_rack_obj_id
     * @param   boolean $p_front
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_rack_positions($p_rack_obj_id, $p_front = true)
    {
        $l_sql = 'SELECT * FROM isys_catg_location_list
			INNER JOIN isys_catg_global_list ON isys_catg_location_list__isys_obj__id = isys_catg_global_list__isys_obj__id
			WHERE isys_catg_location_list__pos > 0
			AND isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_rack_obj_id);

        if ($p_front)
        {
            $l_sql .= ' AND (isys_catg_location_list__insertion = 1);';
        }
        else
        {
            $l_sql .= ' AND (isys_catg_location_list__insertion = 0);';
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Save global category location element
     *
     * @param   integer $p_cat_level        Level to save, default 0.
     * @param   integer &$p_intOldRecStatus __status of record before update.
     * @param   boolean $p_create           Decides whether to create or to save.
     *
     * @return  null
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_location_list__status"];
        $l_oldParent       = $l_catdata["isys_catg_location_list__parentid"];

        if (!empty($l_catdata["isys_catg_location_list__id"]))
        {
            $coord = null;
            if ($_POST["C__CATG__LOCATION_LATITUDE"] || $_POST["C__CATG__LOCATION_LONGITUDE"])
            {
                $coord = new \League\Geotools\Coordinate\Coordinate(
                    [
                        $_POST["C__CATG__LOCATION_LATITUDE"] ?: 0,
                        $_POST["C__CATG__LOCATION_LONGITUDE"] ?: 0
                    ]
                );
            }

            $l_return = $this->save(
                $l_catdata["isys_catg_location_list__id"],
                $l_catdata["isys_catg_location_list__isys_obj__id"],
                $_POST['C__CATG__LOCATION_PARENT__HIDDEN'],
                $l_oldParent,
                $_POST["C__CATG__LOCATION_POS"],
                $_POST["C__CATG__LOCATION_INSERTION"],
                $_POST["C__CATG__LOCATION_IMAGE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__LOCATION_OPTION'],
                $coord
            );

            if ($l_return)
            {
                // Clear all found "auth-*" cache-files. So that it is not necessary to trigger it manually in Cache/Database
                try
                {
                    $l_cache_files = isys_caching::find('auth-*');
                    array_map(
                        function ($l_cache)
                        {
                            $l_cache->clear();
                        },
                        $l_cache_files
                    );
                }
                catch (Exception $e)
                {
                    isys_notify::warning('Could not clear cache files for /temp/auth-* with message: ' . $e->getMessage());
                }
            }

            return $l_return;
        } // if

        return null;
    } // function

    /**
     * Creates the category entry.
     *
     * @param   integer                                $p_list_id
     * @param   integer                                $p_parent_object_id
     * @param   integer                                $p_posID
     * @param   integer                                $p_insertion
     * @param   null                                   $p_unused
     * @param   string                                 $p_description
     * @param   integer                                $p_status
     * @param   integer                                $p_option
     * @param   \League\Geotools\Coordinate\Coordinate $p_coord
     *
     * @throws  Exception
     * @throws  isys_exception_dao
     * @return  integer
     */
    public function save_category($p_list_id, $p_parent_object_id, $p_posID, $p_insertion, $p_unused = null, $p_description = '', $p_status = C__RECORD_STATUS__NORMAL, $p_option = null, $p_coord = null)
    {
        if ($p_insertion >= 0 && $p_insertion !== null)
        {
            $p_insertion = "'" . $p_insertion . "'";
        }
        else
        {
            $p_insertion = "NULL";
        } // if

        if (!$p_coord)
        {
            $p_coord = null;
        }

        $l_strSQL = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__parentid = " . $this->convert_sql_id($p_parent_object_id) . ",
			isys_catg_location_list__pos = " . $this->convert_sql_int($p_posID) . ",
			isys_catg_location_list__insertion = " . $p_insertion . ",
			isys_catg_location_list__gps = " . $this->convert_sql_point($p_coord) . ",
			isys_catg_location_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_location_list__status = " . $this->convert_sql_id($p_status) . ",
			isys_catg_location_list__option = " . $this->convert_sql_id($p_option) . "
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_list_id) . ";";

        $this->m_strLogbookSQL = $l_strSQL;

        $l_bRet = $this->update($l_strSQL) && $this->apply_update();

        if ($l_bRet)
        {
            // Create implicit relation.
            try
            {
                $l_data = $this->get_data($p_list_id)
                    ->__to_array();

                if ($p_parent_object_id > 0)
                {
                    isys_cmdb_dao_category_g_relation::instance($this->m_db)
                        ->handle_relation(
                            $p_list_id,
                            "isys_catg_location_list",
                            C__RELATION_TYPE__LOCATION,
                            $l_data["isys_catg_location_list__isys_catg_relation_list__id"],
                            $p_parent_object_id,
                            $l_data["isys_catg_location_list__isys_obj__id"]
                        );
                }
            }
            catch (Exception $e)
            {
                throw $e;
            } // try
        } // if

        return $l_bRet;
    } // function

    /**
     * Creates the category entry.
     *
     * @param   integer $p_list_id
     * @param   integer $p_object_id
     * @param   integer $p_parent_object_id
     * @param   integer $p_posID
     * @param   integer $p_frontsideID
     * @param   null    $p_unused
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @throws  Exception
     * @throws  isys_exception_dao
     * @return  integer
     */
    public function create_category($p_list_id = null, $p_object_id, $p_parent_object_id, $p_posID, $p_frontsideID, $p_unused = null, $p_description = '', $p_status = C__RECORD_STATUS__NORMAL, $p_coord = null)
    {
        if ($p_frontsideID >= 0 && $p_frontsideID !== null)
        {
            $p_frontside = "'" . $p_frontsideID . "'";
        }
        else
        {
            $p_frontside = "NULL";
        } // if

        if (is_null($p_coord))
        {
            $p_coord = new \League\Geotools\Coordinate\Coordinate([0, 0]);
        } // if

        $l_sql = 'INSERT IGNORE INTO isys_catg_location_list SET
			isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
			isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_parent_object_id) . ',
			isys_catg_location_list__gps = '.$this->convert_sql_point($p_coord).',
			isys_catg_location_list__pos = ' . $this->convert_sql_int($p_posID) . ',
			isys_catg_location_list__insertion = ' . $p_frontside . ',
			isys_catg_location_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_location_list__status = ' . $this->convert_sql_int($p_status) . ';';

        $this->update($l_sql) && $this->apply_update();
        $this->m_strLogbookSQL .= $l_sql;
        $l_last_id = $this->get_last_insert_id();

        // Create implicit relation.
        try
        {
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

            if (!empty($p_parent_object_id))
            {
                $l_dao_relation->handle_relation($l_last_id, 'isys_catg_location_list', C__RELATION_TYPE__LOCATION, null, $p_parent_object_id, $p_object_id);
            } // if
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return $l_last_id;
    } // function

    /**
     * More simple method for resetting a location.
     *
     * @param   integer $p_obj_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function reset_location($p_obj_id)
    {
        if ($p_obj_id > 0)
        {
            $l_row = $this->get_data(null, $p_obj_id)
                ->get_row();

            $this->save($l_row['isys_catg_location_list__id'], $l_row['isys_catg_location_list__isys_obj__id'], null);
        } // if
    } // function

    /**
     * Executes the operations to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer                                $p_list_id
     * @param   integer                                $p_objID
     * @param   integer                                $p_parent_id
     * @param   integer                                $p_oldParentID
     * @param   integer                                $p_posID
     * @param   integer                                $p_insertion
     * @param   null                                   $p_unused
     * @param   string                                 $p_description
     * @param   integer                                $p_option
     * @param   \League\Geotools\Coordinate\Coordinate $p_coord
     *
     * @return  boolean
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_list_id, $p_objID, $p_parent_id, $p_oldParentID = null, $p_posID = null, $p_insertion = null, $p_unused = null, $p_description = '', $p_option = null, $p_coord = null)
    {
        if ($p_list_id > 0)
        {
            if ($p_parent_id != 'NULL' && $p_parent_id > 0)
            {
                if ($this->obj_exists($p_parent_id))
                {
                    if ($p_objID != $p_parent_id)
                    {
                        if (empty($p_oldParentID))
                        {
                            $this->insert_node($p_list_id, $p_parent_id);
                        }
                        else
                        {
                            $this->move_node($p_list_id, $p_parent_id);
                        } // if
                    }
                    else
                    {
                        throw new isys_exception_dao_cmdb('Attaching own object is prohibitted.');
                    } // if
                }
                else
                {
                    throw new isys_exception_dao_cmdb(sprintf('Parent location with id %s does not exist.', $p_parent_id));
                }
            }
            else
            {
                if ($p_oldParentID > 0)
                {
                    $this->delete_node($p_list_id);
                }
            } // if

            return $this->save_category($p_list_id, $p_parent_id, $p_posID, $p_insertion, null, $p_description, C__RECORD_STATUS__NORMAL, $p_option, $p_coord);
        } // if
        return false;
    } // function

    /**
     * Executes the operations to create the category entry referenced by isys_obj__id $p_objID
     *
     * @param   integer $p_objID
     * @param   integer $p_parentID
     * @param   integer $p_posID
     * @param   integer $p_frontsideID
     * @param   null    $p_unused
     * @param   string  $p_description
     *
     * @return  integer  The newly created ID or false
     * @throws  Exception
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_parentID, $p_posID = null, $p_frontsideID = null, $p_unused = null, $p_description = '')
    {
        if ($p_parentID > 0)
        {
            if (!$this->obj_exists($p_parentID))
            {
                throw new isys_exception_dao_cmdb(sprintf('Parent location with id %s does not exist.', $p_parentID));
            } // if
        } // if

        $l_insID = $this->create_category(null, $p_objID, $p_parentID, $p_posID, $p_frontsideID, null, $p_description, C__RECORD_STATUS__NORMAL);

        if ($p_parentID != null)
        {
            $this->insert_node($l_insID, $p_parentID);
        } // if

        return $l_insID;
    } // function

    /**
     * Get whole location tree with one query.
     *
     * @return  isys_component_dao_result
     */
    public function get_location_tree()
    {
        $l_sql = 'SELECT isys_obj__id AS id, a.isys_catg_location_list__parentid AS parentid, isys_obj__title AS title, isys_obj_type__id AS object_type_id, isys_obj_type__title AS object_type, a.isys_catg_location_list__isys_catg_relation_list__id AS relation_id
			FROM isys_catg_location_list a
			INNER JOIN isys_catg_location_list b ON a.isys_catg_location_list__parentid = b.isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj ON isys_obj__id = a.isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			ORDER BY a.isys_catg_location_list__parentid ASC';

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_cached_locations($p_obj_id = null)
    {
        if ($p_obj_id !== null)
        {
            return self::$m_location_cache[$p_obj_id];
        } // if

        return self::$m_location_cache;
    } // function

    /**
     * Returns the location path of the given object. Will throw an RuntimeException on recursion!
     *
     * @param   integer $p_obj
     *
     * @return  array
     * @throws  RuntimeException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_location_path($p_obj)
    {
        $l_return   = [];
        $l_parentid = $p_obj;

        while (($l_parentid = $this->get_parent_id_by_object($l_parentid)) !== false)
        {
            if (in_array($l_parentid, $l_return))
            {
                throw new RuntimeException(_L('LC__CATG__LOCATION__RECURSION_IN_OBJECT') . ' #' . $l_parentid . ' "' . $this->get_obj_name_by_id_as_string($l_parentid) . '"');
            } // if

            if ($l_parentid != C__OBJ__ROOT_LOCATION)
            {
                $l_return[] = $l_parentid;
            } // if
        } // while

        return $l_return;
    } // function

    /**
     * Default Event triggered on updating the location nodes.
     *
     * @param   int    $p_nodeID
     * @param   int    $p_parentNodeID
     * @param   string $p_updatetype
     *
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function update_location_node($p_nodeID, $p_parentNodeID, $p_updatetype)
    {
        // Invalidate cache. This is needed for cached location rights:
        isys_cache::keyvalue()->flush();
    } // function

    /**
     * Creates a new node in the lft-rgt-tree.
     *
     * @param   integer $p_nodeID
     * @param   integer $p_parentNodeID
     *
     * @throws  Exception
     */
    public function insert_node($p_nodeID, $p_parentNodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, $p_parentNodeID, 'insert');

        $l_query = "SELECT isys_catg_location_list__rgt
	        FROM isys_catg_location_list
	        WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($p_parentNodeID) . ";";

        $l_row   = $this->retrieve($l_query)->get_row();

        $l_rgt = (empty($l_row["isys_catg_location_list__rgt"])) ? 0 : ((int) $l_row["isys_catg_location_list__rgt"]) - 1;

        $l_update = "UPDATE isys_catg_location_list SET
	        isys_catg_location_list__rgt = isys_catg_location_list__rgt + 2
	        WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";

        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + 2
			WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = " . $this->convert_sql_id($l_rgt + 1) . ",
			isys_catg_location_list__rgt = " . $this->convert_sql_id($l_rgt + 2) . "
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if
    } // function

    /**
     * Delete a node from the tree.
     *
     * @param   integer $p_nodeID
     *
     * @throws  Exception
     */
    public function delete_node($p_nodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, null, 'delete');

        $l_query = "SELECT isys_catg_location_list__lft, isys_catg_location_list__rgt
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        $l_row   = $this->retrieve($l_query)
            ->get_row();

        $l_lft  = (int) $l_row["isys_catg_location_list__lft"];
        $l_rgt  = (int) $l_row["isys_catg_location_list__rgt"];
        $l_diff = $l_rgt - $l_lft + 1;

        if ($l_lft > 0 && $l_rgt > 0 && $l_rgt > $l_lft)
        {
            // Delete relations
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
            $l_sql          = "SELECT isys_catg_location_list__isys_catg_relation_list__id
				FROM isys_catg_location_list
				WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";

            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_dao_relation->delete_relation($l_row["isys_catg_location_list__isys_catg_relation_list__id"]);
                } // while
            } // if

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__lft = NULL,
				isys_catg_location_list__rgt = NULL
				WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";
            if (!$this->update($l_update))
            {
                throw new Exception(
                    $this->get_database_component()
                        ->get_last_error_as_string()
                );
            } // if

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__rgt = isys_catg_location_list__rgt - " . $l_diff . "
				WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";
            if (!$this->update($l_update))
            {
                throw new Exception(
                    $this->get_database_component()
                        ->get_last_error_as_string()
                );
            } // if

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__lft = isys_catg_location_list__lft - " . $l_diff . "
				WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
            if (!$this->update($l_update))
            {
                throw new Exception(
                    $this->get_database_component()
                        ->get_last_error_as_string()
                );
            } // if
        } // if
    } // function

    /**
     * @param   integer $p_nodeID
     * @param   integer $p_parentNodeID
     *
     * @return  boolean
     * @throws  Exception
     */
    public function move_node($p_nodeID, $p_parentNodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, $p_parentNodeID, 'move');

        $l_query = "SELECT isys_catg_location_list__lft, isys_catg_location_list__rgt, isys_catg_location_list__parentid
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        $l_row   = $this->retrieve($l_query)
            ->get_row();

        $l_lft  = (int) $l_row["isys_catg_location_list__lft"];
        $l_rgt  = (int) $l_row["isys_catg_location_list__rgt"];
        $l_diff = $l_rgt - $l_lft + 1;

        $l_subElements = [];
        $l_query       = "SELECT isys_catg_location_list__id
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";
        $l_res         = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row())
        {
            $l_subElements[] = $l_row['isys_catg_location_list__id'];
        } // while

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__rgt = isys_catg_location_list__rgt - " . $l_diff . "
			WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";
        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft - " . $l_diff . "
			WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        $l_query = "SELECT isys_catg_location_list__rgt, isys_catg_location_list__lft
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($p_parentNodeID) . ";";
        $l_row   = $this->retrieve($l_query)
            ->get_row();

        $l_rgtNew = ((int) $l_row["isys_catg_location_list__rgt"]) - 1;
        $l_lftNew = ((int) $l_row["isys_catg_location_list__lft"]) + 1;

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__rgt = isys_catg_location_list__rgt + " . $l_diff . "
			WHERE isys_catg_location_list__rgt > " . $l_rgtNew;

        if (count($l_subElements) > 0)
        {
            $l_in_query = implode(',', $l_subElements);
            $l_in_query = rtrim($l_in_query, ',');
            $l_update .= ' AND isys_catg_location_list__id NOT IN (' . $l_in_query . ')';
        } // if

        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + " . $l_diff . "
			WHERE isys_catg_location_list__lft > " . $l_rgtNew;

        if (count($l_subElements) > 0)
        {
            $l_update .= ' AND isys_catg_location_list__id NOT IN (' . $l_in_query . ')';
        } // if

        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        // TODO: Insert subtree at the new position
        $l_rgtNew += $l_diff;
        $l_diff = $l_rgtNew - $l_rgt;

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + (" . $l_diff . "),
			isys_catg_location_list__rgt = isys_catg_location_list__rgt + (" . $l_diff . ")
			WHERE FALSE";

        if (count($l_subElements) > 0)
        {
            $l_update .= ' OR isys_catg_location_list__id IN (' . $l_in_query . ')';
        } // if

        if (!$this->update($l_update))
        {
            throw new Exception(
                $this->get_database_component()
                    ->get_last_error_as_string()
            );
        } // if

        return $this->apply_update();
    } // function

    /**
     * Checks whether location is well-defined. This helps to avoid self-
     * referencing, non-location objects, and referencing loops.
     *
     * @param int $p_obj_id      Object identifier
     * @param int $p_location_id Location identifier
     *
     * @return bool|string Returns true on success, otherwise error message.
     */
    public function validate_parent($p_obj_id, $p_location_id)
    {
        assert('is_int($p_obj_id) && $p_obj_id > 0');
        assert('is_int($p_location_id) && $p_location_id > 0');

        // Avoid self-referencing:
        if ($p_obj_id === $p_location_id)
        {
            return _L('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
        } //if

        $l_sql = 'SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__container = 1';
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row())
        {
            $l_valid_location_object_types[] = $l_row['isys_obj_type__id'];
        }

        // Location must be a location object:
        $l_obj_type = intval($this->get_objTypeID($p_location_id));
        if (!in_array($l_obj_type, $l_valid_location_object_types))
        {
            return _L('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
        } //if

        $l_parent_id        = $p_location_id;
        $l_location_objects = [];

        // Walk through the location tree:
        while ($l_parent_id !== false)
        {

            // Object itself isn't part of the tree:
            if (in_array($p_obj_id, $l_location_objects))
            {
                return _L('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            } //if

            // Root location has no parent.
            if ($l_parent_id == C__OBJ__ROOT_LOCATION)
            {
                break;
            } //if

            // Location isn't part of the tree:
            if (in_array($l_parent_id, $l_location_objects))
            {
                return _L('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            } //if

            // Parent must be a location object:
            $l_obj_type = intval($this->get_objTypeID($l_parent_id));
            if (!in_array($l_obj_type, $l_valid_location_object_types))
            {
                return _L('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            } //if

            // Keep parent in mind:
            $l_location_objects[] = $l_parent_id;

            // Next one...
            $l_parent_id = intval($this->get_parent_id_by_object($l_parent_id));
        } // while

        return true;
    } // function

    /**
     * This method gets called, after "category save" by signal-slot-module.
     *
     * @throws  RuntimeException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function validate_after_save()
    {
        global $g_comp_database;

        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_dao    = isys_cmdb_dao_category_g_location::instance($g_comp_database);

        try
        {
            // Using "$l_dao" instead of "$this", because this gets called by "call_user_func" (which works statically).
            $l_dao->get_location_path($l_obj_id);
        }
        catch (RuntimeException $e)
        {
            // The saved parent location produces a recursion - We set the parent to NULL.
            $l_dao->reset_location($l_obj_id);

            // And now we throw the exception further towards the action handler.
            throw $e;
        } // try
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_location'      => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'The current Location'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__parentid'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_location'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_location_path' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_PATH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Location path'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__parentid'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_location_path'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_latitude'      => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_LATITUDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'GPS Coordinates: Latitude'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__gps'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__LOCATION_LATITUDE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'property_callback_latitude'
                    ]
                ]
            ],
            '_longitude'     => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_LONGITUDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'GPS Coordinates: Longitude'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__gps'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__LOCATION_LONGITUDE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'property_callback_longitude'
                    ]
                ]
            ]
        ];
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *, ST_AsText(isys_catg_location_list__gps) AS isys_catg_location_list__gps, ST_X(isys_catg_location_list__gps) AS latitude, ST_Y(isys_catg_location_list__gps) AS longitude FROM isys_catg_location_list
			INNER JOIN isys_obj
			ON isys_catg_location_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type
			ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND isys_catg_location_list__id = " . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_location_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ";");
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'parent'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Location'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_location_list__parentid',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__LOCATION,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_location',
                                'callback_property_relation_handler'
                            ], [
                                'isys_cmdb_dao_category_g_location',
                                true
                            ]
                        )
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__LOCATION_PARENT',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strPopupType'  => 'browser_location',
                            'callback_accept' => 'idoit.callbackManager.triggerCallback(\'location__parent_location_change\');',
                            'p_onChange'      => 'idoit.callbackManager.triggerCallback(\'location__parent_location_change\');',
                            'containers_only' => true
                        ],
                        C__PROPERTY__UI__DEFAULT => '0'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'location'
                        ]
                    ]
                ]
            ),
            'option'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_OPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assembly option'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__option'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_OPTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_location',
                                    'callback_property_assembly_options'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'insertion'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_FRONTSIDE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Insertion'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__insertion'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_INSERTION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'        => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_location',
                                    'callback_property_insertion'
                                ]
                            ),
                            'p_strSelectedID' => 1
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'pos'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_POS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Position in the rack'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__pos'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_POS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_location',
                                    'callback_property_pos'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'location_property_pos'
                        ]
                    ]
                ]
            ),
            'gps'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'GPS',
                        C__PROPERTY__INFO__DESCRIPTION => 'GPS Coordinate'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__gps'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__VIRTUAL    => true,
                        C__PROPERTY__PROVIDES__IMPORT     => true,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'property_callback_gps'
                        ]
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__LOCATION
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => true
                    ]
                ]
            )
        ];
    } // function

    /**
     * Sync method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create($p_object_id, null, null, null, null, null);
            }
            elseif ($p_status == isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] === null)
            {
                $l_res = $this->retrieve(
                    'SELECT isys_catg_location_list__id FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id(
                        $p_object_id
                    ) . ';'
                );

                if (count($l_res))
                {
                    $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_location_list__id');
                }
                else
                {
                    $p_category_data['data_id'] = $this->create($p_object_id, null, null, null, null, null);
                } // if
            } // if

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                $l_coord = null;

                // Save category data:
                if ($p_category_data['data_id'] > 0)
                {
                    if (isset($p_category_data['properties']['gps'][C__DATA__VALUE]) && is_array($p_category_data['properties']['gps'][C__DATA__VALUE]) && count(
                            $p_category_data['properties']['gps'][C__DATA__VALUE]
                        ) === 2
                    )
                    {
                        $l_gps = $p_category_data['properties']['gps'][C__DATA__VALUE];

                        if (is_numeric($l_gps[0]) && is_numeric($l_gps[1]))
                        {
                            $l_coord = new \League\Geotools\Coordinate\Coordinate($l_gps);
                        } // if
                    }
                    else
                    {
                        if ($p_category_data['properties']['latitude'][C__DATA__VALUE] || $p_category_data['properties']['longitude'][C__DATA__VALUE])
                        {
                            $l_coord = new \League\Geotools\Coordinate\Coordinate(
                                [
                                    $p_category_data['properties']['latitude'][C__DATA__VALUE] ?: 0,
                                    $p_category_data['properties']['longitude'][C__DATA__VALUE] ?: 0
                                ]
                            );
                        } // if
                    } // if

                    $this->save(
                        $p_category_data['data_id'],
                        $p_object_id,
                        $p_category_data['properties']['parent'][C__DATA__VALUE],
                        1,
                        $p_category_data['properties']['pos'][C__DATA__VALUE],
                        $p_category_data['properties']['insertion'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['option'][C__DATA__VALUE],
                        $l_coord
                    );

                    return $p_category_data['data_id'];
                }
            } // if
        }

        return false;
    } //function

    /**
     * Constructor.
     *
     * @param   isys_component_database &$p_db
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __construct(isys_component_database $p_db)
    {
        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.cmdb.afterCategoryEntrySave',
                [
                    'isys_cmdb_dao_category_g_location',
                    'validate_after_save'
                ]
            )
            ->connect(
                'mod.cmdb.afterCreateCategoryEntry',
                [
                    'isys_cmdb_dao_category_g_location',
                    'validate_after_save'
                ]
            )
            ->connect(
                'mod.cmdb.beforeUpdateLocationNode',
                [
                    $this,
                    'update_location_node'
                ]
            );

        return parent::__construct($p_db);
    } // function

} // class