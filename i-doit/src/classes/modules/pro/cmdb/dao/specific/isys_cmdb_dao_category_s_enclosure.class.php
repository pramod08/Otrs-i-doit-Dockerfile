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
 * DAO: specific category for enclosures.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_enclosure extends isys_cmdb_dao_category_specific
{
    /**
     * Number of maximum vertical slots.
     *
     * @var  integer
     */
    const C__RACK__VERTICAL_SLOTS = 12;

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'enclosure';

    /**
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null)
        {
            $l_obj_id = (int) $p_obj_id;
        }
        else
        {
            $l_obj_id = (int) $this->m_object_id;
        } // if

        $l_sql = "SELECT COUNT(isys_catg_location_list__id) AS 'count' FROM isys_catg_location_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__parentid
			WHERE TRUE ";

        if ($l_obj_id > 0)
        {
            $l_sql .= "AND (isys_catg_location_list__parentid = " . $this->convert_sql_id($l_obj_id) . ") ";
        }

        $l_sql .= "AND (isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ");";

        $l_data = $this->retrieve($l_sql)
            ->get_row();

        return $l_data["count"];
    } // function

    /**
     * Get data method, will retrieve category, object and object-type information while sorting by "position_in_room" field.
     *
     * @param   integer $p_category_data_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *
			FROM isys_cats_enclosure_list
			LEFT JOIN isys_obj ON isys_obj__id = isys_cats_enclosure_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_category_data_id !== null)
        {
            $l_sql .= ' AND isys_cats_enclosure_list__id = ' . $this->convert_sql_id($p_category_data_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_cats_enclosure_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        $l_sql .= ' ORDER BY isys_cats_enclosure_list__position_in_room ASC;';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'vertical_slots_front' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONTSIDE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Vertical slots'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_enclosure_list__vertical_slots_front'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-block',
                            'p_bDbFieldNN'      => true,
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'vertical_slots_rear'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ENCLOSURE__VERTICAL_SLOTS_BACKSIDE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Vertical slots'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_enclosure_list__vertical_slots_rear'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass'        => 'input-block',
                            'p_bDbFieldNN'      => true,
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'slot_sorting'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ENCLOSURE__SLOT_SORTING',
                        C__PROPERTY__INFO__DESCRIPTION => 'Height unit sorting'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_enclosure_list__slot_sorting'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATS__ENCLOSURE__UNIT_SORTING',
                        C__PROPERTY__UI__DEFAULT => 'asc',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strClass'        => 'input-block',
                            'p_bDbFieldNN'      => true,
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'description'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_enclosure_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__ENCLOSURE
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            )
        ];
    } // function

    /**
     * This save method is used, because we may have to detach objects before saving.
     *
     * @param   integer $p_cat_id
     * @param   array   $p_data
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_data($p_cat_id, $p_data)
    {
        /*
         * The following lines are used for the case that we save a rack with for example 8 (front/rear) v-slots,
         * although there are still objects assigned between slot 9 and 12.
         */
        $l_positions = isys_cmdb_dao_category_g_location::instance($this->get_database_component())
            ->get_positions_in_rack($p_data['isys_obj__id']);

        if (count($l_positions['assigned_units']) > 0)
        {
            foreach ($l_positions['assigned_units'] as $l_object)
            {
                if ($l_object['option'] == C__RACK_INSERTION__VERTICAL)
                {
                    if ($l_object['insertion'] == C__RACK_INSERTION__FRONT)
                    {
                        if ($l_object['pos'] > $p_data['vertical_slots_front'])
                        {
                            // This object is assigned to a not existing slot - Remove it.
                            isys_cmdb_dao_location::instance($this->get_database_component())
                                ->update_position($l_object['obj_id']);
                        } // if
                    }
                    else if ($l_object['insertion'] == C__RACK_INSERTION__BACK)
                    {
                        if ($l_object['pos'] > $p_data['vertical_slots_rear'])
                        {
                            // This object is assigned to a not existing slot - Remove it.
                            isys_cmdb_dao_location::instance($this->get_database_component())
                                ->update_position($l_object['obj_id']);
                        } // if
                    } // if
                } // if
            } // foreach
        } // if

        return parent::save_data($p_cat_id, $p_data);
    } // function

    /**
     * Method for preparing the necessary information of a rack, by receiving it's object ID and title.
     *
     * @param   integer $p_rack_obj_id
     * @param   string  $p_rack_obj_title
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function prepare_rack_data($p_rack_obj_id, $p_rack_obj_title)
    {
        global $g_dirs;

        $l_rackobj_res = isys_factory::get_instance('isys_cmdb_dao_location', $this->get_database_component())
            ->get_location($p_rack_obj_id, null);

        $l_rack_formfactor = isys_factory::get_instance('isys_cmdb_dao_category_g_formfactor', $this->get_database_component())
            ->get_data(null, $p_rack_obj_id)
            ->get_row();

        $l_rack_enclosure = isys_factory::get_instance('isys_cmdb_dao_category_s_enclosure', $this->get_database_component())
            ->get_data(null, $p_rack_obj_id)
            ->get_row();

        $l_cmdb_res = isys_factory::get_instance('isys_cmdb_dao_status', $this->get_database_component())
            ->get_cmdb_status();

        while ($l_row = $l_cmdb_res->get_row())
        {
            $l_cmdb_status[$l_row['isys_cmdb_status__id']] = [
                'color' => '#' . $l_row['isys_cmdb_status__color'],
                'title' => isys_glob_htmlentities(_L($l_row['isys_cmdb_status__title']))
            ];
        } // while

        $l_return = [
            'id'               => (int) $p_rack_obj_id,
            'title'            => $p_rack_obj_title,
            'slots'            => (int) $l_rack_formfactor['isys_catg_formfactor_list__rackunits'],
            'sorting'          => $l_rack_enclosure['isys_cats_enclosure_list__slot_sorting'] ?: 'asc',
            'vslots_front'     => (int) $l_rack_enclosure['isys_cats_enclosure_list__vertical_slots_front'],
            'vslots_rear'      => (int) $l_rack_enclosure['isys_cats_enclosure_list__vertical_slots_rear'],
            'position_in_room' => (int) $l_rack_enclosure['isys_cats_enclosure_list__position_in_room']
        ];

        while ($l_rackobj_row = $l_rackobj_res->get_row())
        {
            $l_icon = $l_rackobj_row['isys_obj_type__icon'];

            if (strpos($l_icon, '/') === false)
            {
                $l_icon = $g_dirs['images'] . 'tree/' . $l_icon;
            } // if

            $l_return['objects'][$l_rackobj_row['isys_obj__id']] = [
                'id'          => $l_rackobj_row['isys_obj__id'],
                'title'       => isys_glob_htmlentities(isys_glob_utf8_encode($l_rackobj_row['isys_obj__title'])),
                'type'        => isys_glob_utf8_encode(_L($l_rackobj_row['isys_obj_type__title'])),
                'icon'        => $l_icon,
                'color'       => '#' . $l_rackobj_row['isys_obj_type__color'],
                'cmdb_color'  => $l_cmdb_status[$l_rackobj_row['isys_obj__isys_cmdb_status__id']]['color'],
                'cmdb_status' => $l_cmdb_status[$l_rackobj_row['isys_obj__isys_cmdb_status__id']]['title'],
                'height'      => $l_rackobj_row['isys_catg_formfactor_list__rackunits'] ?: 1,
                'option'      => $l_rackobj_row['isys_catg_location_list__option'],
                'insertion'   => $l_rackobj_row['isys_catg_location_list__insertion'],
                'pos'         => $l_rackobj_row['isys_catg_location_list__pos']
            ];
        } // while

        // Encode the objects, before returning the variable.
        $l_return['objects'] = isys_format_json::encode($l_return['objects']);

        return $l_return;
    } // function

    /**
     * Method for saving the position of one or more racks inside a locataion.
     * We only need the key (position) and value (rack obj-id).
     *
     * @param   array $p_position
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_position_in_location(array $p_position = [])
    {
        if (count($p_position) > 0)
        {
            foreach ($p_position as $l_pos => $l_rack_obj_id)
            {
                $l_sql = 'UPDATE isys_cats_enclosure_list
					SET isys_cats_enclosure_list__position_in_room = ' . $this->convert_sql_int($l_pos) . '
					WHERE  isys_cats_enclosure_list__isys_obj__id = ' . $this->convert_sql_id($l_rack_obj_id) . ';';

                if (!($this->update($l_sql) && $this->apply_update()))
                {
                    return false;
                } // if
            } // foreach
        } // if

        return true;
    } // function
} // class