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
 * DAO: specific category for chassis enclosure.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_cmdb_dao_category_s_chassis extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'chassis';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'assigned_device';

    /**
     * @var bool
     */
    protected $m_has_relation = true;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;
    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_chassis_list__isys_obj__id';

    /**
     * Returns the possible device types.
     *
     * @static
     * @return  array
     */
    public static function get_assigned_device_types()
    {
        return [
            0 => _L('LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS__ASSIGN_BUTTON'),
            1 => _L('LC__CMDB__CATG__POWER_CONSUMER'),
            2 => _L('LC__CMDB__CATG__HBA'),
            3 => _L('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE')
        ];
    } // function

    /**
     * Returns the possible chassis-insertions.
     *
     * @static
     * @return  array
     */
    public static function get_insertion()
    {
        return [
            C__INSERTION__FRONT => _L('LC__UNIVERSAL__FRONT'),
            C__INSERTION__REAR  => _L('LC__UNIVERSAL__REAR')
        ];
    } // function

    /**
     * Returns the possible device type.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_assigned_device_type(isys_request $p_req)
    {
        return isys_cmdb_dao_category_s_chassis::get_assigned_device_types();
    } // function

    /**
     * Returns the assigned slots to a device.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assigned_slots(isys_request $p_req)
    {
        // Retrieve the available slots and prepare the dialog-list data.
        $l_return = [];

        $l_assigned_slots = array_keys($this->get_assigned_slots_by_cat_id($p_req->get_category_data_id()));
        $l_slot_res       = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db)
            ->get_data(null, $p_req->get_object_id(), '', null, C__RECORD_STATUS__NORMAL);

        while ($l_slot_row = $l_slot_res->get_row())
        {
            $l_return[] = [
                'id'  => $l_slot_row['isys_cats_chassis_slot_list__id'],
                'val' => $l_slot_row['isys_cats_chassis_slot_list__title'],
                'sel' => in_array($l_slot_row['isys_cats_chassis_slot_list__id'], $l_assigned_slots)
            ];
        } // while

        return $l_return;
    } // function

    /**
     * Returns the possible chassis-insertions.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_insertion(isys_request $p_req)
    {
        return isys_cmdb_dao_category_s_chassis::get_insertion();
    } // function

    /**
     * Callback method for the local devices dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_ports(isys_request $p_request)
    {
        return $this->get_local_devices_as_array($p_request->get_object_id());
    } // function

    /**
     * Assign a chassis item to a chassis slot.
     *
     * @param   integer $p_chassis_cat_id
     * @param   integer $p_slot
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_slot_to_chassis_item($p_chassis_cat_id, $p_slot)
    {
        if (empty($p_chassis_cat_id) || empty($p_slot))
        {
            return null;
        } // if

        $l_sql = 'INSERT INTO  isys_cats_chassis_list_2_isys_cats_chassis_slot_list (
			isys_cats_chassis_slot_list__id,
			isys_cats_chassis_list__id
			) VALUES (
			' . $this->convert_sql_id($p_slot) . ',
			' . $this->convert_sql_id($p_chassis_cat_id) . '
			);';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Assign multiple chassis slots to a chassis item.
     *
     * @param   integer $p_cat_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_slots_from_post($p_cat_id)
    {
        // First delete all assignments.
        $this->remove_slot_assignments($p_cat_id);

        $l_selected = explode(',', $_POST['C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT__selected_values']);

        if (is_array($l_selected))
        {
            foreach ($l_selected as $l_slot)
            {
                $this->assign_slot_to_chassis_item($p_cat_id, $l_slot);
            } // foreach
        } // if
    } // function

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   integer $p_role
     * @param   string  $p_local_assignment format example: "3_C__CATG__HBA"
     * @param   integer $p_assigned_device
     * @param   string  $p_description
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_role, $p_local_assignment = null, $p_assigned_device = null, $p_description = null)
    {
        $l_netp_id = null;
        $l_pc_id   = null;
        $l_hba_id  = null;
        $l_con_id  = null;

        /**
         * @var  $l_dao_connection  isys_cmdb_dao_connection
         */
        $l_dao_connection = isys_cmdb_dao_connection::instance($this->m_db);

        if ($p_assigned_device > 0)
        {
            $l_con_id = $l_dao_connection->add_connection($p_assigned_device);
        }
        else if ($p_local_assignment)
        {
            $l_con_id = $l_dao_connection->add_connection(0);
            $l_id     = substr($p_local_assignment, 0, strpos($p_local_assignment, '_'));
            $l_type   = substr($p_local_assignment, strpos($p_local_assignment, '_') + 1);

            switch ($l_type)
            {
                case 'C__CATG__HBA':
                    $l_hba_id = $l_id;
                    break;
                case 'C__CATG__POWER_CONSUMER':
                    $l_pc_id = $l_id;
                    break;
                case 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P':
                    $l_netp_id = $l_id;
                    break;
            } // switch
        } // if

        $l_update = 'INSERT INTO isys_cats_chassis_list (
			isys_cats_chassis_list__isys_obj__id,
			isys_cats_chassis_list__status,
			isys_cats_chassis_list__isys_chassis_role__id,
			isys_cats_chassis_list__isys_connection__id,
			isys_cats_chassis_list__isys_catg_netp_list__id,
			isys_cats_chassis_list__isys_catg_pc_list__id,
			isys_cats_chassis_list__isys_catg_hba_list__id,
			isys_cats_chassis_list__description
			) VALUES (' . $this->convert_sql_id($p_obj_id) . ',' . $this->convert_sql_int($p_status) . ',' . $this->convert_sql_id($p_role) . ',' . $this->convert_sql_id(
                $l_con_id
            ) . ',' . $this->convert_sql_id($l_netp_id) . ',' . $this->convert_sql_id($l_pc_id) . ',' . $this->convert_sql_id($l_hba_id) . ',' . $this->convert_sql_text(
                $p_description
            ) . ');';

        if ($this->update($l_update) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            // We only need to create relations if we got ourself an object.
            if ($p_assigned_device > 0)
            {
                $this->relations_update($l_last_id, $p_obj_id, $p_assigned_device);
            } // if

            return $l_last_id;
        } // if

        return false;
    } // function

    /**
     * Retrieves the string of the assigned device (for lists and dialog-fields etc.).
     *
     * @param   integer $p_cat_id
     * @param   string  $p_type
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_device_title_by_cat_id($p_cat_id, $p_type = 'quickinfo')
    {
        $l_return = '-';

        $l_sql = "SELECT rl.isys_chassis_role__title, ob.isys_obj__id, ob.isys_obj__title, netp.isys_catg_netp_list__id, netp.isys_catg_netp_list__title, pc.isys_catg_pc_list__id, pc.isys_catg_pc_list__title, hba.isys_catg_hba_list__id, hba.isys_catg_hba_list__title
			FROM isys_cats_chassis_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_chassis_list__isys_connection__id
			LEFT JOIN isys_chassis_role AS rl ON rl.isys_chassis_role__id = isys_cats_chassis_list__isys_chassis_role__id
			LEFT JOIN isys_obj ob ON ob.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_catg_netp_list AS netp ON netp.isys_catg_netp_list__id = isys_cats_chassis_list__isys_catg_netp_list__id
			LEFT JOIN isys_catg_pc_list AS pc ON pc.isys_catg_pc_list__id = isys_cats_chassis_list__isys_catg_pc_list__id
			LEFT JOIN isys_catg_hba_list AS hba ON hba.isys_catg_hba_list__id = isys_cats_chassis_list__isys_catg_hba_list__id
			WHERE isys_cats_chassis_list__id = " . $this->convert_sql_id($p_cat_id) . ";";

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        if ($l_row['isys_obj__id'] > 0)
        {
            if ($p_type == 'quickinfo')
            {
                $l_quickinfo = new isys_ajax_handler_quick_info();
                $l_return    = _L('LC_UNIVERSAL__OBJECT') . ': ' . $l_quickinfo->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT);
            }
            else if ($p_type == 'short')
            {
                $l_return = $l_row['isys_obj__title'];
            }
            else
            {
                $l_return = _L('LC_UNIVERSAL__OBJECT') . ': ' . $l_row['isys_obj__title'];
            } // if
        }
        else if ($l_row['isys_catg_pc_list__id'] > 0)
        {
            $l_return = _L('LC__CMDB__CATG__POWER_CONSUMER') . ': ' . $l_row['isys_catg_pc_list__title'];
        }
        else if ($l_row['isys_catg_netp_list__id'] > 0)
        {
            $l_return = _L('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE') . ': ' . $l_row['isys_catg_netp_list__title'];
        }
        else if ($l_row['isys_catg_hba_list__id'] > 0)
        {
            $l_return = _L('LC__CMDB__CATG__HBA') . ': ' . $l_row['isys_catg_hba_list__title'];
        } // if

        return $l_return;
    } // function

    /**
     * Retrieve all assigned objects of a chassis.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   bool    $p_only_object_ids
     *
     * @return  array
     */
    public function get_assigned_objects($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_only_object_ids = false)
    {
        $l_return = [];
        $l_sql    = 'SELECT obj.*, type.*
			FROM isys_cats_chassis_list
			LEFT JOIN isys_connection ON isys_cats_chassis_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_obj obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_cats_chassis_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_cats_chassis_list__status = ' . $this->convert_sql_id($p_status) . '
			AND obj.isys_obj__status = ' . $this->convert_sql_id($p_status) . '
			GROUP BY isys_obj__id;';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {
            if ($l_row['isys_obj__id'] > 0)
            {
                if ($p_only_object_ids)
                {
                    $l_return[] = $l_row['isys_obj__id'];
                }
                else
                {
                    $l_return[] = $l_row;
                }
            } // if
        } // while

        return $l_return;
    } // function

    /**
     * Selects the assigned slots to an chassis item.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_slots_by_cat_id($p_cat_id)
    {
        $l_return = [];
        $l_sql    = 'SELECT cs.*
			FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list c2cs
			LEFT JOIN isys_cats_chassis_slot_list cs ON cs.isys_cats_chassis_slot_list__id = c2cs.isys_cats_chassis_slot_list__id
			LEFT JOIN isys_cats_chassis_list c ON c.isys_cats_chassis_list__id = c2cs.isys_cats_chassis_list__id
			WHERE c.isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cat_id) . '
			AND c.isys_cats_chassis_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND cs.isys_cats_chassis_slot_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {
            $l_return[$l_row['isys_cats_chassis_slot_list__id']] = $l_row;
        } // while

        return $l_return;
    } // function

    /**
     * Return Category Data
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_chassis_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_chassis_list__isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_cats_chassis_list__isys_connection__id
			LEFT JOIN isys_chassis_connector_type ON isys_cats_chassis_list__isys_chassis_connector_type__id = isys_chassis_connector_type__id
			LEFT JOIN isys_chassis_role ON isys_cats_chassis_list__isys_chassis_role__id = isys_chassis_role__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null)
        {
            $l_sql .= ' AND (isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cats_list_id) . ')';
        }

        if ($p_status !== null)
        {
            $l_sql .= ' AND (isys_cats_chassis_list__status = ' . $this->convert_sql_int($p_status) . ')';
        }

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function properties()
    {
        return [
            'role'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ROLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Role'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_chassis_role__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_chassis_role',
                            'isys_chassis_role__id'
                        ]
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ROLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_chassis_role',
                            'p_strClass' => 'input-small'
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'assigned_device'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned device'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_cats_chassis_list__isys_connection__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__CHASSIS,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_s_chassis',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_s_chassis']
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_connection',
                            'isys_connection__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID           => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__UI__PLACEHOLDER  => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__PLACEHOLDER',
                        C__PROPERTY__UI__EMPTYMESSAGE => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS__EMPTY'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connection'
                        ]
                    ]
                ]
            ),
            'assigned_hba'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned hostadapter (HBA)'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_catg_hba_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_hba_list',
                            'isys_catg_hba_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_ip',
                                    'callback_property_assigned_categories'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_referenced_object_and_category',
                            [
                                'C__CATG__HBA',
                                'C__CMDB__CATEGORY__TYPE_GLOBAL'
                            ]
                        ]
                    ]
                ]
            ),
            'assigned_interface'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned interface'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_catg_netp_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_netp_list',
                            'isys_catg_netp_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_ip',
                                    'callback_property_assigned_categories'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_referenced_object_and_category',
                            [
                                'C__CMDB__SUBCAT__NETWORK_INTERFACE_P',
                                'C__CMDB__CATEGORY__TYPE_GLOBAL'
                            ]
                        ]
                    ]
                ]
            ),
            'assigned_power_consumer' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned power consumer'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_catg_pc_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_pc_list',
                            'isys_catg_pc_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_ip',
                                    'callback_property_assigned_categories'
                                ]
                            ),
                            'p_strClass' => 'input-small ml20'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_referenced_object_and_category',
                            [
                                'C__CATG__POWER_CONSUMER',
                                'C__CMDB__CATEGORY__TYPE_GLOBAL'
                            ]
                        ]
                    ]
                ]
            ),
            'assigned_slots'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned to'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID          => 'C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT',
                        C__PROPERTY__UI__PARAMS      => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_chassis',
                                    'callback_property_assigned_slots'
                                ]
                            )
                        ],
                        C__PROPERTY__UI__PLACEHOLDER => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS__PLACEHOLDER'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__REPORT    => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'chassis_slots'
                        ]
                    ]
                ]
            ),
            'description'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__CHASSIS_DEVICES
                    ]
                ]
            )
        ];
    } // function

    /**
     * Rank method for handling "archive", "delete" and "recycle".
     *
     * @param   integer $p_cat_id
     * @param   integer $p_direction
     * @param   string  $p_table
     * @param   array   $p_checkMethod Callback like array('Class', 'Method').
     *
     * @return  boolean
     */
    public function rank_record($p_cat_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        $l_row = $this->get_data($p_cat_id)
            ->get_row();

        if ($l_row['isys_connection__isys_obj__id'] > 0)
        {
            // Retrieve the location- and chassis relation from the given object.
            $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

            // Now we prepare the condition to get us the relation objects.
            $l_cond = 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($l_row['isys_connection__isys_obj__id']) . '
				AND (isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(C__RELATION_TYPE__CHASSIS) . '
					OR isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(C__RELATION_TYPE__LOCATION) . ')';
            $l_res  = $l_rel_dao->get_data(null, null, $l_cond);

            while ($l_rel_row = $l_res->get_row())
            {
                parent::rank_record($l_rel_row['isys_catg_relation_list__id'], $p_direction, 'isys_catg_relation_list', $p_checkMethod);
            } // while
        } // if

        return parent::rank_record($p_cat_id, $p_direction, $p_table, $p_checkMethod);
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $l_role            = $p_category_data['properties']['role'][C__DATA__VALUE];
            $l_assigned_device = $p_category_data['properties']['assigned_device'][C__DATA__VALUE];
            $l_description     = $p_category_data['properties']['description'][C__DATA__VALUE];
            if ($p_category_data['properties']['assigned_hba'][C__DATA__VALUE] > 0)
            {
                $l_local_device = $p_category_data['properties']['assigned_hba'][C__DATA__VALUE] . '_' . 'C__CATG__HBA';
            }
            else if ($p_category_data['properties']['assigned_interface'][C__DATA__VALUE] > 0)
            {
                $l_local_device = $p_category_data['properties']['assigned_hba'][C__DATA__VALUE] . '_' . 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P';
            }
            else if ($p_category_data['properties']['assigned_power_consumer'][C__DATA__VALUE] > 0)
            {
                $l_local_device = $p_category_data['properties']['assigned_hba'][C__DATA__VALUE] . '_' . 'C__CATG__POWER_CONSUMER';
            }
            else
            {
                $l_local_device = null;
            } // if
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create(
                    $p_object_id,
                    C__RECORD_STATUS__NORMAL,
                    $l_role,
                    $l_local_device,
                    $l_assigned_device,
                    $l_description
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $l_role,
                    $l_local_device,
                    $l_assigned_device,
                    $l_description
                );
                // Assign slots.
                if (is_array($p_category_data['properties']['assigned_slots'][C__DATA__VALUE]))
                {
                    foreach ($p_category_data['properties']['assigned_slots'][C__DATA__VALUE] as $l_slot)
                    {
                        $this->assign_slot_to_chassis_item($p_category_data['data_id'], $l_slot['id']);
                    } // foreach
                } // if
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Gets local hostadapters.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_hba($p_obj_id, $p_cat_id = null)
    {
        $l_query = 'SELECT isys_catg_hba_list__id AS id, isys_catg_hba_list__title AS title
			FROM isys_catg_hba_list
			WHERE isys_catg_hba_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_hba_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_cat_id))
        {
            $l_query .= ' AND isys_catg_hba_list__id = ' . $this->convert_sql_id($p_cat_id);
        } // if

        $l_res = $this->retrieve($l_query);
        if ($l_res->num_rows() > 0)
        {
            return $l_res;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Gets local power consumers.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_power_consumer($p_obj_id, $p_cat_id = null)
    {
        $l_query = 'SELECT isys_catg_pc_list__id AS id, isys_catg_pc_list__title AS title
			FROM isys_catg_pc_list
			WHERE isys_catg_pc_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_pc_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_cat_id))
        {
            $l_query .= ' AND isys_catg_pc_list__id = ' . $this->convert_sql_id($p_cat_id);
        } // if

        $l_res = $this->retrieve($l_query);
        if ($l_res->num_rows() > 0)
        {
            return $l_res;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Gets local interfaces.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_interface($p_obj_id, $p_cat_id = null)
    {
        $l_query = 'SELECT isys_catg_netp_list__id AS id, isys_catg_netp_list__title AS title
			FROM isys_catg_netp_list
			WHERE isys_catg_netp_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_netp_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_cat_id))
        {
            $l_query .= ' AND isys_catg_netp_list__id = ' . $this->convert_sql_id($p_cat_id);
        } // if

        $l_res = $this->retrieve($l_query);
        if ($l_res->num_rows() > 0)
        {
            return $l_res;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Gets local devices (hba, interfaces, power consumer) as array.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_devices_as_array($p_obj_id)
    {
        $l_arr     = [];
        $l_devices = [
            _L('LC__CMDB__CATG__HBA')                           => [
                $this->get_local_hba($p_obj_id),
                'C__CATG__HBA'
            ],
            _L('LC__CMDB__CATG__POWER_CONSUMER')                => [
                $this->get_local_power_consumer($p_obj_id),
                'C__CATG__POWER_CONSUMER'
            ],
            _L('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE') => [
                $this->get_local_interface($p_obj_id),
                'C__CMDB__SUBCAT__NETWORK_INTERFACE_P'
            ]
        ];

        foreach ($l_devices AS $l_lc => $l_result)
        {
            if ($l_result[0])
            {
                while ($l_row = $l_result[0]->get_row())
                {
                    $l_arr[$l_lc][$l_row['id'] . '_' . $l_result[1]] = $l_row['title'];
                } // while
            } // if
        } // foreach

        return $l_arr;
    } // function

    /**
     * This method helps to create the necessary "chassis" and "location" relations, when assigning a new device (object).
     *
     * @param   integer $p_cat_id
     * @param   integer $p_chassis_obj
     * @param   integer $p_assigned_obj
     *
     * @return  isys_cmdb_dao_category_s_chassis
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function relations_create($p_cat_id, $p_chassis_obj, $p_assigned_obj)
    {
        // If we got no object to assign, we can skip this method.
        if (empty($p_assigned_obj))
        {
            return $this;
        } // if

        $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        // Now we can create the new relation
        $l_rel_dao->handle_relation(
            $p_cat_id,
            'isys_cats_chassis_list',
            C__RELATION_TYPE__CHASSIS,
            null,
            $p_chassis_obj,
            $p_assigned_obj
        );

        // Now we handle the location relation.
        $l_loc_dao  = new isys_cmdb_dao_category_g_location($this->m_db);
        $l_location = $l_loc_dao->get_data(null, $p_assigned_obj)
            ->get_row();

        // Object to Chassis location.
        if (!$l_location)
        {
            $l_loc_dao->create($p_assigned_obj, $p_chassis_obj, null, null, null, null);
        } // if
        else if (isset($l_location['isys_catg_location_list__isys_obj__id']) && $l_location['isys_catg_location_list__isys_obj__id'] > 0)
        {
            $l_loc_dao->save(
                $l_location['isys_catg_location_list__id'],
                $l_location['isys_catg_location_list__isys_obj__id'],
                $p_chassis_obj,
                $l_location['isys_catg_location_list__parentid'],
                $l_location['isys_catg_location_list__pos'],
                $l_location['isys_catg_location_list__insertion'],
                null,
                $l_location['isys_catg_location_list__description'],
                $l_location['isys_catg_location_list__option']
            );
        }

        return $this;
    } // function

    /**
     * Method which calls relations_remove and relations_create.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_chassis_obj
     * @param   integer $p_assigned_obj
     *
     * @return  isys_cmdb_dao_category_s_chassis
     * @uses    $this->relations_remove()
     * @uses    $this->relations_create()
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function relations_update($p_cat_id, $p_chassis_obj, $p_assigned_obj)
    {
        return $this->relations_remove($p_cat_id, $p_chassis_obj, $p_assigned_obj)
            ->relations_create($p_cat_id, $p_chassis_obj, $p_assigned_obj);
    } // function

    /**
     * Method for removing the "chassis" and "location" relations of the chassis category entry and the given object.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_chassis_obj
     * @param   integer $p_assigned_obj
     *
     * @return  isys_cmdb_dao_category_s_chassis
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function relations_remove($p_cat_id, $p_chassis_obj, $p_assigned_obj)
    {
        $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
        $l_loc_dao = isys_cmdb_dao_category_g_location::instance($this->m_db);
        $l_catdata = $this->get_data($p_cat_id)
            ->get_row();

        if ($p_assigned_obj > 0)
        {
            $l_loc_dao->reset_location($p_assigned_obj);
        } // if

        // First remove the already saved relation (if existing).
        if ($l_catdata['isys_cats_chassis_list__isys_catg_relation_list__id'] > 0)
        {
            $l_rel_dao->delete_relation($l_catdata['isys_cats_chassis_list__isys_catg_relation_list__id']);
        } // if

        // We check if the chassis entry already has an assigned object and delete it's relations.
        if ($l_catdata['isys_connection__isys_obj__id'] > 0)
        {
            $l_cond    = 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($l_catdata['isys_connection__isys_obj__id']) . '
				AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(C__RELATION_TYPE__CHASSIS);
            $l_rel_row = $l_rel_dao->get_data(null, null, $l_cond)
                ->get_row();

            if ($l_rel_row !== null && is_array($l_rel_row))
            {
                // We found an old Chassis relation to the given object - We delete it.
                $l_rel_dao->delete_relation($l_rel_row['isys_catg_relation_list__id']);
            } // if

            // Also set the location of the old object to null.
            $l_loc_dao->reset_location($l_catdata['isys_connection__isys_obj__id']);
        } // if

        // We want to delete the old Chassis relation of the given object and therefore have to check if a relation exists.
        $l_cond    = 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($p_assigned_obj) . '
			AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(C__RELATION_TYPE__CHASSIS);
        $l_rel_row = $l_rel_dao->get_data(null, null, $l_cond)
            ->get_row();

        if ($l_rel_row !== null && is_array($l_rel_row))
        {
            // We found an old Chassis relation to the given object - We delete it.
            $l_rel_dao->delete_relation($l_rel_row['isys_catg_relation_list__id']);
        } // if

        if ($p_assigned_obj > 0)
        {
            $l_location = $l_loc_dao->get_data(null, $p_assigned_obj)
                ->get_row();
            $l_loc_dao->save(
                $l_location['isys_catg_location_list__id'],
                $l_location['isys_catg_location_list__isys_obj__id'],
                null
            );
        } // if

        return $this;
    } // function

    /**
     * Method for removing all chassis-slot assignments from a certain chassis item.
     *
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function remove_slot_assignments($p_cat_id)
    {
        $l_sql = 'DELETE FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list
			WHERE isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Save method.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     * @param   integer $p_role
     * @param   string  $p_local_assignment format example: "3_C__CATG__HBA"
     * @param   integer $p_assigned_device
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_cat_id, $p_status, $p_role, $p_local_assignment = null, $p_assigned_device = null, $p_description = null)
    {
        $l_netp_id = null;
        $l_pc_id   = null;
        $l_hba_id  = null;
        $l_con_id  = null;

        $l_row = $this->get_data($p_cat_id)
            ->get_row();

        $this->relations_remove($p_cat_id, $l_row['isys_cats_chassis_list__isys_obj__id'], $p_assigned_device);

        if (!$p_assigned_device && $p_local_assignment)
        {
            $l_id   = substr($p_local_assignment, 0, strpos($p_local_assignment, '_'));
            $l_type = substr($p_local_assignment, strpos($p_local_assignment, '_') + 1);

            switch ($l_type)
            {
                case 'C__CATG__HBA':
                    $l_hba_id = $l_id;
                    break;
                case 'C__CATG__POWER_CONSUMER':
                    $l_pc_id = $l_id;
                    break;
                case 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P':
                    $l_netp_id = $l_id;
                    break;
            } // switch
        } // if

        $this->relations_create($p_cat_id, $l_row['isys_cats_chassis_list__isys_obj__id'], $p_assigned_device);

        $l_update = 'UPDATE isys_cats_chassis_list
			SET
			isys_cats_chassis_list__isys_connection__id = ' . $this->convert_sql_id($this->handle_connection($p_cat_id, $p_assigned_device)) . ',
			isys_cats_chassis_list__isys_chassis_role__id = ' . $this->convert_sql_id($p_role) . ',
			isys_cats_chassis_list__isys_catg_netp_list__id = ' . $this->convert_sql_id($l_netp_id) . ',
			isys_cats_chassis_list__isys_catg_pc_list__id = ' . $this->convert_sql_id($l_pc_id) . ',
			isys_cats_chassis_list__isys_catg_hba_list__id = ' . $this->convert_sql_id($l_hba_id) . ',
			isys_cats_chassis_list__description = ' . $this->convert_sql_text($p_description) . '
			WHERE isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Method for saving the element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  integer  The error code or null on success.
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create)
    {
        $l_intErrorCode = -1;

        if ($p_create)
        {
            $p_cat_level = 1;
            $l_id        = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CMDB__CATS__CHASSIS__ROLE'],
                $_POST['C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT'],
                $_POST['C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            $this->assign_slots_from_post($l_id);

            return $l_id;
        } // if

        $l_catdata         = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_cats_chassis_list__status"];

        $l_bRet = $this->save(
            $l_catdata["isys_cats_chassis_list__id"],
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CMDB__CATS__CHASSIS__ROLE'],
            $_POST['C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT'],
            $_POST['C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__HIDDEN'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        $this->assign_slots_from_post($l_catdata["isys_cats_chassis_list__id"]);

        return ($l_bRet == true) ? null : $l_intErrorCode;
    } // function
} // class