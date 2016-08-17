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
 * DAO: specific category for chassis slots.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_category_s_chassis_view extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'chassis_view';

    /**
     * Method for retrieving the possible matrix-sizes. I believe there's no translation required here.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_chassis_matrix_sizes()
    {
        return [
            0 => 'XS',
            1 => 'S',
            2 => 'M',
            3 => 'L',
            4 => 'XL',
            5 => 'XXL',
        ];
    } // function

    /**
     * Method for preparing the chassis-matrix (to be given to the Javascript Chassis class).
     *
     * @static
     *
     * @param   integer $p_x
     * @param   integer $p_y
     * @param   array   $p_slots
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    static public function process_matrix($p_x, $p_y, array $p_slots)
    {
        $l_return = [];

        // At first we create the matrix nice and clean.
        for ($l_y = 0;$l_y < $p_y;$l_y++)
        {
            $l_return[$l_y] = [];

            for ($l_x = 0;$l_x < $p_x;$l_x++)
            {
                $l_return[$l_y][$l_x] = null;
            } // for
        } // for

        // Now we set the slots to "false" that are inside a col- or rowspan.
        foreach ($p_slots as $l_slot)
        {
            $l_x_from = $l_slot['x_from'];
            $l_x_to   = $l_slot['x_to'];

            $l_y_from = $l_slot['y_from'];
            $l_y_to   = $l_slot['y_to'];

            for ($l_y = $l_y_from;$l_y <= $l_y_to;$l_y++)
            {
                for ($l_x = $l_x_from;$l_x <= $l_x_to;$l_x++)
                {
                    $l_return[$l_y][$l_x] = false;
                } // for
            } // for

            // And finally we set the col- and rowspan to the first slot-TD.
            $l_return[$l_y_from][$l_x_from] = [
                'colSpan'         => ($l_x_to - $l_x_from) + 1,
                'rowSpan'         => ($l_y_to - $l_y_from) + 1,
                'className'       => 'slot m' . $l_y_from . '-' . $l_x_from,
                'data-slot-id'    => $l_slot['id'],
                'data-slot-title' => $l_slot['title'],
                'data-slot-color' => $l_slot['object_color']
            ];
        } // foreach

        return $l_return;
    } // function

    /**
     * Returns the possible matrix sizes.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_size(isys_request $p_req)
    {
        return isys_cmdb_dao_category_s_chassis_view::get_chassis_matrix_sizes();
    } // function

    /**
     * Method for returning the chassis matrix (for the javascript "Chassis" class).
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_chassis_matrix($p_obj_id)
    {
        $l_slot_positions = [
            C__INSERTION__FRONT => [],
            C__INSERTION__REAR  => []
        ];

        $l_slots_res = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db)
            ->get_data(null, $p_obj_id);

        if ($l_slots_res->num_rows() > 0)
        {
            while ($l_slot_row = $l_slots_res->get_row())
            {
                if ($l_slot_row['isys_cats_chassis_slot_list__x_from'] === null && $l_slot_row['isys_cats_chassis_slot_list__y_from'] === null)
                {
                    continue;
                } // if

                $l_slots[]                                                                 = $l_slot_row;
                $l_slot_positions[$l_slot_row['isys_cats_chassis_slot_list__insertion']][] = [
                    'id'     => $l_slot_row['isys_cats_chassis_slot_list__id'],
                    'title'  => $l_slot_row['isys_cats_chassis_slot_list__title'],
                    'x_from' => $l_slot_row['isys_cats_chassis_slot_list__x_from'],
                    'x_to'   => $l_slot_row['isys_cats_chassis_slot_list__x_to'],
                    'y_from' => $l_slot_row['isys_cats_chassis_slot_list__y_from'],
                    'y_to'   => $l_slot_row['isys_cats_chassis_slot_list__y_to']
                ];
            } // while
        } // if

        $l_row = $this->get_data(null, $p_obj_id)
            ->get_row();

        return [
            'front' => self::process_matrix(
                $l_row['isys_cats_chassis_view_list__front_width'],
                $l_row['isys_cats_chassis_view_list__front_height'],
                $l_slot_positions[C__INSERTION__FRONT]
            ),
            'rear'  => self::process_matrix(
                $l_row['isys_cats_chassis_view_list__rear_width'],
                $l_row['isys_cats_chassis_view_list__rear_height'],
                $l_slot_positions[C__INSERTION__REAR]
            )
        ];
    } // function

    /**
     * @param   integer $p_obj_id
     * @param   integer $p_insertion
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process_matrix_devices($p_obj_id, $p_insertion)
    {
        $l_return = [];

        /**
         * IDE typehinting.
         *
         * @var  $l_device_dao  isys_cmdb_dao_category_s_chassis
         */
        $l_device_dao = isys_cmdb_dao_category_s_chassis::instance($this->m_db);

        $l_device_res = $l_device_dao->get_data(null, $p_obj_id);

        if ($l_device_res->num_rows() > 0)
        {
            while ($l_device_row = $l_device_res->get_row())
            {
                $l_slots = $l_device_dao->get_assigned_slots_by_cat_id($l_device_row['isys_cats_chassis_list__id']);

                if (count($l_slots) > 0)
                {
                    foreach ($l_slots as $l_slot)
                    {
                        if ($l_slot['isys_cats_chassis_slot_list__insertion'] == $p_insertion && $l_slot['isys_cats_chassis_slot_list__id'] > 0)
                        {
                            $l_color = $l_obj_type_title = null;

                            if ($l_device_row['isys_connection__isys_obj__id'] > 0)
                            {
                                $l_objtype = $l_device_dao->get_type_by_object_id($l_device_row['isys_connection__isys_obj__id'])
                                    ->get_row();

                                $l_color          = '#' . $l_objtype['isys_obj_type__color'];
                                $l_obj_type_title = _L($l_objtype['isys_obj_type__title']);
                            } // if

                            $l_key = $l_slot['isys_cats_chassis_slot_list__x_from'] . '-' . $l_slot['isys_cats_chassis_slot_list__y_from'];

                            $l_x                = $l_slot['isys_cats_chassis_slot_list__x_to'] - $l_slot['isys_cats_chassis_slot_list__x_from'] + 1;
                            $l_y                = $l_slot['isys_cats_chassis_slot_list__y_to'] - $l_slot['isys_cats_chassis_slot_list__y_from'] + 1;
                            $l_return[$l_key][] = [
                                'title'        => isys_glob_htmlentities(
                                    isys_glob_utf8_encode($l_device_dao->get_assigned_device_title_by_cat_id($l_device_row['isys_cats_chassis_list__id'], 'short'))
                                ),
                                'width'        => $l_x,
                                'height'       => $l_y,
                                'slotid'       => $l_slot['isys_cats_chassis_slot_list__id'],
                                'object_id'    => $l_device_row['isys_connection__isys_obj__id'] ?: 0,
                                'object_color' => $l_color,
                                'object_type'  => isys_glob_utf8_encode($l_obj_type_title)
                            ];
                        } // if
                    } // foreach
                } // if
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Updates existing entity given by user via HTTP GET and POST.
     *
     * @param   boolean   $p_create
     *
     * @return  mixed    Category data's identifier (int) or false (bool)
     */
    public function save_user_data($p_create)
    {
        // At this point we need to detach all positioned slots, which lie outside the grid.
        isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db)
            ->detach_slots_outside_of_bounds(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATS__CHASSIS_VIEW__X_FRONT'],
                $_POST['C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT'],
                C__INSERTION__FRONT
            )
            ->detach_slots_outside_of_bounds(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATS__CHASSIS_VIEW__X_REAR'],
                $_POST['C__CMDB__CATS__CHASSIS_VIEW__Y_REAR'],
                C__INSERTION__REAR
            );

        return parent::save_user_data($p_create);
    } // function

    /**
     * Returns how many entries exists. The folder only needs to know if there are any entries in its subcategories.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == C__CATS__CHASSIS)
        {
            $l_sql = 'SELECT
				(
				IFNULL((SELECT isys_cats_chassis_view_list__id AS cnt FROM isys_cats_chassis_view_list
					WHERE isys_cats_chassis_view_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_cats_chassis_slot_list__id AS cnt FROM  isys_cats_chassis_slot_list
					WHERE isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_cats_chassis_list__id AS cnt FROM  isys_cats_chassis_list
					WHERE isys_cats_chassis_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				)
				AS cnt';

            return ($this->retrieve($l_sql)
                    ->get_row_value('cnt') > 0) ? 1 : 0;
        }
        else
        {
            return parent::get_count($p_obj_id);
        } // if
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
            'front_x'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_VIEW__X_FRONT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Horizontal units'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__front_width'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_VIEW__X_FRONT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'front_y'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_VIEW__Y_FRONT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Vertical units'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__front_height'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_VIEW__Y_FRONT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'front_size'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Front gridsize'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__front_size'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_VIEW__SIZE_FRONT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_chassis_view',
                                    'callback_property_size'
                                ]
                            ),
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'rear_x'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_VIEW__X_REAR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Horizontal units'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__rear_width'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_VIEW__X_REAR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'rear_y'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_VIEW__Y_REAR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Vertical units'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__rear_height'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_VIEW__Y_REAR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'rear_size'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Rear gridsize'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__rear_size'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_VIEW__SIZE_REAR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_chassis_view',
                                    'callback_property_size'
                                ]
                            ),
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_view_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__CHASSIS_VIEW
                    ]
                ]
            )
        ];
    } // function
} // class
