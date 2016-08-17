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
 * DAO: global category for cables.
 *
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @license       http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_cable extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cable';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the connected objects.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_connection(array $p_row)
    {
        global $g_comp_database;

        $l_conn_data = [];
        $l_dao       = isys_cmdb_dao_cable_connection::instance($g_comp_database);

        $l_cable_connection = $l_dao->get_cable_connection_id_by_cable_id($p_row['isys_obj__id']);
        $l_connection       = $l_dao->get_connection_info($l_cable_connection);

        while ($l_row = $l_connection->get_row())
        {
            $l_conn_data[] = $l_row['isys_obj__title'] . ' (' . $l_row['isys_catg_connector_list__title'] . ')';
        } // while

        return html_entity_decode(implode(' &lsaquo;&mdash;&rsaquo; ', $l_conn_data));
    } // function

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  integer
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_cable_list__status"];

        $l_list_id = $l_catdata["isys_catg_cable_list__id"];

        if (empty($l_list_id))
        {
            $l_list_id = $this->create_connector("isys_catg_cable_list", $_GET[C__CMDB__GET__OBJECT]);
        } // if

        if ($l_list_id)
        {
            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__CABLE_TYPE'],
                $_POST['C__CATG__CABLE_LENGTH'],
                $_POST['C__CATG__CABLE_COLOUR'],
                $_POST['C__CATG__CABLE_OCCUPANCY'],
                $_POST['C__CATG__CABLE_MAX_AMOUNT_OF_FIBERS_LEADS'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet == true ? $l_list_id : -1;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_cable_type_id
     * @param   integer $p_cable_length ,
     * @param   integer $p_cable_colour_id
     * @param   integer $p_cable_occupancy_id
     * @param   integer $p_max_amount_of_fibers_leads
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_cat_level, $p_newRecStatus, $p_cable_type_id, $p_cable_length, $p_cable_colour_id, $p_cable_occupancy_id, $p_max_amount_of_fibers_leads, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_cable_list SET " . "isys_catg_cable_list__isys_cable_type__id = " . $this->convert_sql_id(
                $p_cable_type_id
            ) . ", " . "isys_catg_cable_list__isys_cable_colour__id = " . $this->convert_sql_id(
                $p_cable_colour_id
            ) . ", " . "isys_catg_cable_list__isys_cable_occupancy__id = " . $this->convert_sql_id(
                $p_cable_occupancy_id
            ) . ", " . "isys_catg_cable_list__length = " . $this->convert_sql_text(
                $p_cable_length
            ) . ", " . "isys_catg_cable_list__max_amount_of_fibers_leads = " . $this->convert_sql_id(
                $p_max_amount_of_fibers_leads
            ) . ", " . "isys_catg_cable_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " . "isys_catg_cable_list__description = " . $this->convert_sql_text(
                $p_description
            ) . " " . "WHERE isys_catg_cable_list__id = " . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_strSql) && $this->apply_update());
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_connection' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__CABLE__CONNECTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Kabelverbindung'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_connection'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]
        ];
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cable_list
			INNER JOIN isys_obj ON isys_catg_cable_list__isys_obj__id = isys_obj__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_cable_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_catg_cable_list__status = " . $this->convert_sql_int($p_status) . ")";
        } // if

        return $this->retrieve($l_sql . ";");
    } // function

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id The ID may be an integer or an array of integers.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (isys_catg_cable_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_catg_cable_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            } // if
        } // if

        return $l_sql;
    } // function

    /**
     * Method for returning the properties.
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'cable_type'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CABLE__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cable type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cable_list__isys_cable_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cable_type',
                            'isys_cable_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CABLE_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cable_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'cable_colour'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CABLE__COLOUR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Colour'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cable_list__isys_cable_colour__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cable_colour',
                            'isys_cable_colour__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CABLE_COLOUR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cable_colour'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'cable_occupancy'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CABLE__OCCUPANCY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Occupancy'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cable_list__isys_cable_occupancy__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cable_occupancy',
                            'isys_cable_occupancy__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CABLE_OCCUPANCY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cable_occupancy'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'length'                     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CABLE__LENGTH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Length in CM'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cable_list__length'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CABLE_LENGTH'
                    ]
                ]
            ),
            'max_amount_of_fibers_leads' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => 'LC__CMDB__CATS__CABLE__MAX_AMOUNT_OF_FIBERS_LEADS'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cable_list__max_amount_of_fibers_leads'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CABLE_MAX_AMOUNT_OF_FIBERS_LEADS'
                    ]
                ]
            ),
            'description'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cable_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATG__CABLE
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create_connector(
                    'isys_catg_cable_list',
                    $p_object_id
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data:
                if ($p_category_data['data_id'] > 0)
                {
                    $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['cable_type'][C__DATA__VALUE],
                        $p_category_data['properties']['length'][C__DATA__VALUE],
                        $p_category_data['properties']['cable_colour'][C__DATA__VALUE],
                        $p_category_data['properties']['cable_occupancy'][C__DATA__VALUE],
                        $p_category_data['properties']['max_amount_of_fibers_leads'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );

                    return $p_category_data['data_id'];
                }
            } // if
        } // if
        return false;
    } // function
} // class

?>