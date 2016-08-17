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
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for layer2-net assigned ports
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_layer2_net_assigned_ports extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'layer2_net_assigned_ports';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'isys_obj__id';
    /**
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;
    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'isys_catg_port_list__id';

    /**
     * @param null $p_obj_id
     *
     * @return int
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0)
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        if ($this->m_table && $l_obj_id > 0)
        {
            $l_sql = "SELECT COUNT(" . $this->m_table . "__isys_obj__id) as count
				FROM " . $this->m_table . "
				WHERE (" . $this->m_table . "__status = " . $this->convert_sql_int(
                    C__RECORD_STATUS__NORMAL
                ) . " OR " . $this->m_table . "__status = " . $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE) . ")
				AND " . $this->m_table . "__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ";";

            $l_amount = $this->retrieve($l_sql)
                ->get_row();

            return (int) $l_amount["count"];
        } // if

        return false;
    }

    /**
     * Get-data method. Note that this category has no PRIMARY __id field. The primary key consists of the two fields port_id and obj_id.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM ' . $this->m_table . '
			LEFT JOIN isys_catg_port_list ON isys_catg_port_list.isys_catg_port_list__id = ' . $this->m_table . '.isys_catg_port_list__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_cats_layer2_net_assigned_ports_list__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= 'AND isys_obj__status = ' . $this->convert_sql_int($p_status) . ' ';
        } // if

        return $this->retrieve($l_sql . ' ORDER BY isys_cats_layer2_net_assigned_ports_list__default DESC;');
    } // function

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (' . $this->m_table . '__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'isys_obj__id'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__OBJECT_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Object title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_layer2_net_assigned_ports_list__isys_obj__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS__ISYS_OBJ__ID'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'isys_catg_port_list__id' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_port_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS__ISYS_CATG_PORT_LIST__ID',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                            isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                            isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__NETWORK',
                            isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                            isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                            isys_popup_browser_object_ng::C__SECOND_LIST      => [
                                'isys_cmdb_dao_category_s_layer2_net_assigned_ports::object_browser',
                                [C__CMDB__GET__OBJECT => (isset($_GET[C__CMDB__GET__OBJECT]) ? $_GET[C__CMDB__GET__OBJECT] : 0)]
                            ],
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'port'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            )
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                if (($l_id = $this->create(
                    $p_category_data['properties']['isys_obj__id']['value'],
                    $p_category_data['properties']['isys_catg_port_list__id']['ref_id']
                ))
                )
                {
                    $l_indicator = true;
                }
            } // if
        }

        return ($l_indicator === true) ? $l_id : false;
    }

    /**
     * @param     $p_obj_id
     * @param int $p_status
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function find_assigned_ports($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT * FROM ' . $this->m_table . ' AS l2 ' .

            // Here we connect to the table which holds all the ports.
            'INNER JOIN isys_catg_port_list AS port ' . 'ON port.isys_catg_port_list__id = l2.isys_catg_port_list__id ' .

            // Now we connect to the isys_obj table to get the data of the connected object.
            'INNER JOIN isys_obj AS obj ' . 'ON obj.isys_obj__id = l2.isys_cats_layer2_net_assigned_ports_list__isys_obj__id ' .

            // Connection to specific category
            'INNER JOIN isys_cats_layer2_net_list AS cats ' . 'ON obj.isys_obj__id = cats.isys_cats_layer2_net_list__isys_obj__id ' .

            // And finally we connect to the isys_obj_type table to get the object type.
            'INNER JOIN isys_obj_type AS type ' . 'ON obj.isys_obj__isys_obj_type__id = type.isys_obj_type__id ' .

            'WHERE isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_int($p_obj_id) . ' ' . 'AND ' . $this->m_table . '__status = ' . $this->convert_sql_int(
                $p_status
            ) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_port_id
     * @param   integer $p_status
     *
     * @return  mixed  Integer with last inserted ID on success, boolean false on failure.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_obj_id, $p_port_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'INSERT INTO ' . $this->m_table . ' SET ' . $this->m_table . '__status = ' . $this->convert_sql_int(
                $p_status
            ) . ', ' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_int($p_obj_id) . ', ' . 'isys_catg_port_list__id = ' . $this->convert_sql_int(
                $p_port_id
            ) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            $this->m_strLogbookSQL .= $l_sql;

            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Create element method, gets called from the object browser after confirming the selection.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_new_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $this->delete_entries_by_obj_id($p_object_id);

        if (count($p_objects) > 0)
        {
            foreach ($p_objects as $l_entry)
            {
                $this->create($p_object_id, $l_entry);
            } // foreach
        } // if
    } // function

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param   integer $p_context
     * @param   array   $p_parameters
     *
     * @return  string  A JSON Encoded array with all the contents of the second list.
     * @return  array   A PHP Array with the preselections for category, first- and second list.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        switch ($p_context)
        {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                $l_dao_port = new isys_cmdb_dao_category_g_network_port($g_comp_database);
                $l_res_port = $l_dao_port->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);

                if ($l_res_port->num_rows() > 0)
                {
                    while ($l_row_port = $l_res_port->get_row())
                    {
                        $l_return[] = [
                            '__checkbox__'                                                                             => $l_row_port["isys_catg_port_list__id"],
                            isys_glob_utf8_encode('Port')                                                              => isys_glob_utf8_encode(
                                $l_row_port["isys_catg_port_list__title"]
                            ),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__PORT__MAC')) => isys_glob_utf8_encode(
                                $l_row_port["isys_catg_port_list__mac"]
                            )
                        ];
                    } // while
                } // if

                return isys_format_json::encode($l_return);
                break;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                // Create this class, because we can't just use "this" or we'll get an exception "Database component not loaded!".
                $l_dao = new isys_cmdb_dao_category_s_layer2_net_assigned_ports($g_comp_database);
                $l_res = $l_dao->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);

                while ($l_row = $l_res->get_row())
                {
                    $l_return['second'][] = [
                        $l_row['isys_catg_port_list__id'],
                        $l_row['isys_catg_port_list__title'],
                        $l_row['isys_catg_port_list__mac'],
                    ];
                } // while

                return $l_return;
                break;
        } // switch
    } // function
} // class
?>
