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
 * DAO: global category for power suppliers
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_power_supplier extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'power_supplier';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     */
    public function callback_property_assigned_connector(isys_request $p_request)
    {
        $l_dao = new isys_cmdb_dao_cable_connection($this->get_database_component());

        return $l_dao->get_assigned_connector_id($p_request->get_row("isys_catg_power_supplier_list__isys_catg_connector_list__id"));
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = 'SELECT isys_obj.*, isys_catg_power_supplier_list.*, connected.isys_catg_connector_list__title AS connector_name, ' . 'connected.isys_catg_connector_list__id AS con_connector, mine.* ' . 'FROM isys_catg_power_supplier_list ' . 'INNER JOIN isys_obj ' . 'ON ' . 'isys_catg_power_supplier_list__isys_obj__id = isys_obj__id ' . 'LEFT JOIN isys_catg_connector_list AS mine ' . 'ON ' . 'mine.isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id ' . 'LEFT JOIN isys_cable_connection ' . 'ON ' . 'mine.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id ' . 'LEFT JOIN isys_catg_connector_list AS connected ' . 'ON ' . 'connected.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id ' . 'AND (connected.isys_catg_connector_list__id != mine.isys_catg_connector_list__id OR connected.isys_catg_connector_list__id IS NULL) ' . 'WHERE TRUE ';

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if (!empty($p_catg_list_id))
        {
            $l_sql .= ' AND (isys_catg_power_supplier_list__id = ' . $this->convert_sql_id($p_catg_list_id) . ') ';
        } // if

        if (!empty($p_status))
        {
            $l_sql .= ' AND (isys_catg_power_supplier_list__status = ' . $this->convert_sql_int($p_status) . ')';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_connector_list__title',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'mine'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),

            'volt'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__POWER_SUPPLIER__VOLT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Volt'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__volt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__POWER_SUPPLIER__VOLT'
                    ]
                ]
            ),
            'watt'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__POWER_SUPPLIER__WATT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Watt'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__watt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__POWER_SUPPLIER__WATT'
                    ]
                ]
            ),
            'ampere'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__POWER_SUPPLIER__AMPERE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Ampere'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__ampere'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__POWER_SUPPLIER__AMPERE'
                    ]
                ]
            ),
            'assigned_connector' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_power_supplier_list__isys_catg_connector_list__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'connected_connector',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType'  => 'browser_cable_connection_ng',
                            'secondSelection' => true,
                            'groupFilter'     => 'C__OBJTYPE_GROUP__INFRASTRUCTURE',
                            'p_strValue'      => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_power_supplier',
                                    'callback_property_assigned_connector'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'assigned_connector'
                        ]
                    ]
                ]
            ),
            'connector_sibling'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__SIBLING_IN_OR_OUT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned Input/Output'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_connector_list__id'
                    ],
                    // @todo This property has no field ID and has to be renamed.
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connector'
                        ]
                    ]
                ]
            ),
            'assigned_category'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    // This property has no UI field.
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__CATEGORY_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CATG__CONNECTOR__CATEGORY_TYPE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__assigned_category'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__VIRTUAL   => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_connector_assigned_category'
                        ]
                    ]
                ]
            ),
            'description'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__POWER_SUPPLIER
                    ]
                ]
            )
        ];
    }

    /**
     * Synchronizes data
     *
     * @param $p_category_data
     * @param $p_object_id
     * @param $p_status
     *
     * @return bool
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        $this->get_property('volt'),
                        $this->get_property('watt'),
                        $this->get_property('ampere'),
                        $this->get_property('title'),
                        $this->get_property('description'),
                        $this->get_property('connector_sibling')
                    );
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('connector'),
                        $this->get_property('assigned_connector'),
                        null,
                        $this->get_property('volt'),
                        $this->get_property('watt'),
                        $this->get_property('ampere'),
                        $this->get_property('title'),
                        $this->get_property('description'),
                        $this->get_property('connector_sibling')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Save global category power_supplier.
     *
     * @param $p_cat_level
     * @param &$p_intOldRecStatus
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode    = -1; // ErrorCode
        $l_catdata         = $this->get_general_data();
        $p_intOldRecStatus = $l_catdata['isys_catg_power_supplier_list__status'];
        $l_intNewRecStatus = C__RECORD_STATUS__NORMAL;

        $l_milli_volt   = $_POST['C__CMDB__CATG__POWER_SUPPLIER__VOLT'];
        $l_milli_watt   = $_POST['C__CMDB__CATG__POWER_SUPPLIER__WATT'];
        $l_milli_ampere = $_POST['C__CMDB__CATG__POWER_SUPPLIER__AMPERE'];

        $l_daoCableConnection = new isys_cmdb_dao_cable_connection($this->m_db);
        $l_dao_connector      = new isys_cmdb_dao_category_g_connector($this->m_db);

        $l_conID          = $l_daoCableConnection->get_cable_connection_id_by_connector_id($l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id']);
        $l_connection_res = $l_daoCableConnection->get_connection_info($l_conID);

        $l_daoCableConnection->delete_cable_connection(
            $l_daoCableConnection->get_cable_connection_id_by_connector_id($l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id'])
        );

        if (isset($_POST['C__CATG__POWER_SUPPLIER__DEST__HIDDEN']) && $_POST['C__CATG__POWER_SUPPLIER__DEST__HIDDEN'] != '')
        {
            $l_cable_name = $_POST['C__CATG__POWER_SUPPLIER__DEST__CABLE_NAME'];
            $l_cableID    = $_POST['C__CATG__POWER_SUPPLIER__CABLE__HIDDEN'];

            if (empty($l_cableID))
            {
                $l_cableID = isys_cmdb_dao_cable_connection::add_cable($l_cable_name);
            }

            $l_conID = $l_daoCableConnection->add_cable_connection($l_cableID);

            $l_daoCableConnection->save_connection(
                $l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id'],
                $_POST['C__CATG__POWER_SUPPLIER__DEST__HIDDEN'],
                $l_conID
            );
        } // if

        if ($l_catdata['isys_catg_power_supplier_list__id'] != '')
        {
            $l_strSql = 'UPDATE isys_catg_power_supplier_list ' . 'SET ' . 'isys_catg_power_supplier_list__description = ' . $this->convert_sql_text(
                    $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
                ) . ', ' . 'isys_catg_power_supplier_list__volt = ' . $this->convert_sql_text(
                    $l_milli_volt
                ) . ', ' . 'isys_catg_power_supplier_list__watt = ' . $this->convert_sql_text(
                    $l_milli_watt
                ) . ', ' . 'isys_catg_power_supplier_list__ampere = ' . $this->convert_sql_text(
                    $l_milli_ampere
                ) . ', ' . 'isys_catg_power_supplier_list__status = ' . C__RECORD_STATUS__NORMAL . ', ' . 'isys_catg_power_supplier_list__isys_catg_connector_list__id = ' . $this->convert_sql_id(
                    $l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id']
                ) . ' ' . 'WHERE ' . 'isys_catg_power_supplier_list__id = ' . $this->convert_sql_id($l_catdata['isys_catg_power_supplier_list__id']) . ';';

            $this->m_strLogbookSQL = $l_strSql;

            $l_bRet = $this->update($l_strSql);
            if ($l_bRet)
            {

                $l_bRet = $this->apply_update();
            }
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    public function save($p_catlevel, $p_status, $p_connectorRearID, $p_connectorAheadID, $p_cableID, $p_volt = null, $p_watt = null, $p_ampere = null, $p_title = '', $p_description = '', $p_connector_sibling = null)
    {

        $l_daoCableConnection = new isys_cmdb_dao_cable_connection($this->m_db);

        $l_bRet = false;

        if (empty($p_connectorRearID) && $p_catlevel > 0)
        {
            // Get connector id from db
            $p_connectorRearID = $this->get_connector($p_catlevel);
        }
        else
        {
            // Check if ids are the same
            $l_connectorRearID = $this->get_connector($p_catlevel);
            if ($l_connectorRearID != $p_connectorRearID)
            {
                $p_connectorRearID = $l_connectorRearID;
            }
        }

        $l_daoCableConnection->delete_cable_connection($l_daoCableConnection->get_cable_connection_id_by_connector_id($p_connectorRearID));

        if (isset($p_connectorAheadID) && $p_connectorAheadID != '' && $p_connectorAheadID != null)
        {
            $l_daoCableConnection = new isys_cmdb_dao_cable_connection($this->m_db);

            $l_daoCableConnection->delete_cable_connection($l_daoCableConnection->get_cable_connection_id_by_connector_id($p_connectorRearID));
            $l_cableID = $p_cableID;

            if ($l_cableID == '') $l_cableID = isys_cmdb_dao_cable_connection::add_cable();

            $l_daoCableConnection->delete_cable_connection($l_daoCableConnection->get_cable_connection_id_by_connector_id($p_connectorAheadID));

            $l_conID = $l_daoCableConnection->add_cable_connection($l_cableID);

            $l_daoCableConnection->save_connection($p_connectorRearID, $p_connectorAheadID, $l_conID);
        }

        if ($p_catlevel != '')
        {

            $l_strSql = 'UPDATE isys_catg_power_supplier_list ' . 'INNER JOIN isys_catg_connector_list ' . 'ON ' . 'isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id ' . 'SET ';

            if ($p_connector_sibling > 0) $l_strSql .= 'isys_catg_connector_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($p_connector_sibling) . ', ';

            $l_strSql .= 'isys_catg_connector_list__title = ' . $this->convert_sql_text(
                    $p_title
                ) . ', ' . 'isys_catg_power_supplier_list__description = ' . $this->convert_sql_text(
                    $p_description
                ) . ', ' . 'isys_catg_power_supplier_list__volt = ' . $this->convert_sql_text(
                    $p_volt
                ) . ', ' . 'isys_catg_power_supplier_list__watt = ' . $this->convert_sql_text(
                    $p_watt
                ) . ', ' . 'isys_catg_power_supplier_list__status = ' . C__RECORD_STATUS__NORMAL . ', ' . 'isys_catg_power_supplier_list__ampere = ' . $this->convert_sql_text(
                    $p_ampere
                ) . ' ' . 'WHERE ' . 'isys_catg_power_supplier_list__id = ' . $this->convert_sql_id($p_catlevel) . ';';

            $this->m_strLogbookSQL = $l_strSql;

            $l_bRet = $this->update($l_strSql);

            if ($l_bRet)
            {
                $l_bRet = $this->apply_update();
            }
        }

        return $l_bRet;
    }

    /**
     * @param $p_cat_level
     * @param &$p_new_id
     *
     * @desc Save global category power_supplier element
     */
    public function attachObjects(array $p_post)
    {
        global $g_comp_database;
        $p_new_id = -1;

        // CREATE CONNECTOR
        $l_strTitle = $this->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);

        $l_dao_connector = new isys_cmdb_dao_category_g_connector($g_comp_database);
        $l_list_id       = $l_dao_connector->create($_GET[C__CMDB__GET__OBJECT], C__CONNECTOR__OUTPUT, null, null, null, null, null, null, "C__CATG__POWER_SUPPLIER", null);

        $l_strSql = 'INSERT IGNORE INTO ' . 'isys_catg_power_supplier_list ' . 'SET ' . 'isys_catg_power_supplier_list__isys_catg_connector_list__id = ' . $this->convert_sql_id(
                $l_list_id
            ) . ', ' . 'isys_catg_power_supplier_list__isys_obj__id	= ' . $this->convert_sql_id(
                $_GET[C__CMDB__GET__OBJECT]
            ) . ', ' . 'isys_catg_power_supplier_list__status 		= ' . C__RECORD_STATUS__NORMAL . ' ' . ';';

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $p_new_id = $this->get_last_insert_id();
        }

        return $p_new_id;
    }

    public function create($p_cat_level, $p_status, $p_connection_id, $p_volt = null, $p_watt = null, $p_ampere = null, $p_title = '', $p_description = '', $p_connector_sibling)
    {
        global $g_comp_database;
        $p_new_id = -1;

        // CREATE CONNECTOR
        $l_strTitle = $this->get_obj_name_by_id_as_string($p_cat_level);

        $l_dao_connector = new isys_cmdb_dao_category_g_connector($g_comp_database);

        if ($p_connection_id == null)
        {
            $p_connection_id = $l_dao_connector->create(
                $p_cat_level,
                C__CONNECTOR__OUTPUT,
                null,
                null,
                $p_title,
                null,
                $p_connector_sibling,
                null,
                'C__CATG__POWER_SUPPLIER',
                null
            );
        }

        $l_strSql = 'INSERT INTO ' . 'isys_catg_power_supplier_list ' . 'SET ' . 'isys_catg_power_supplier_list__isys_catg_connector_list__id = ' . $this->convert_sql_id(
                $p_connection_id
            ) . ', ' . 'isys_catg_power_supplier_list__isys_obj__id	= ' . $this->convert_sql_id(
                $p_cat_level
            ) . ', ' . 'isys_catg_power_supplier_list__status 		= ' . $this->convert_sql_int(
                $p_status
            ) . ', ' . 'isys_catg_power_supplier_list__description = ' . $this->convert_sql_text(
                $p_description
            ) . ', ' . 'isys_catg_power_supplier_list__volt = ' . $this->convert_sql_text($p_volt) . ', ' . 'isys_catg_power_supplier_list__watt = ' . $this->convert_sql_text(
                $p_watt
            ) . ', ' . 'isys_catg_power_supplier_list__ampere = ' . $this->convert_sql_text($p_ampere) . ' ' . ';';

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $p_new_id = $this->get_last_insert_id();
        }

        return $p_new_id;
    }

    public function get_connector($p_list_id)
    {
        $l_query = 'SELECT isys_catg_power_supplier_list__isys_catg_connector_list__id AS id FROM isys_catg_power_supplier_list WHERE isys_catg_power_supplier_list__id = ' . $this->convert_sql_id(
                $p_list_id
            );

        return $this->retrieve($l_query)
            ->get_row_value('id');
    }

    public function get_connector_mod($p_list_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_power_supplier_list ' . 'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id ' . 'WHERE isys_catg_power_supplier_list__id = ' . $this->convert_sql_id(
                $p_list_id
            ) . ';';
        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows())
        {
            $l_row           = $l_res->get_row();
            $l_tmp_connector = $l_row['isys_catg_connector_list__id'];
            $l_cable         = $l_row['isys_catg_connector_list__isys_cable_connection__id'];
            $l_sql           = 'SELECT * FROM isys_catg_connector_list WHERE isys_catg_connector_list__id != ' . $this->convert_sql_id(
                    $l_tmp_connector
                ) . ' ' . 'AND isys_catg_connector_list__isys_cable_connection__id = ' . $this->convert_sql_id($l_cable) . ';';

            return $this->retrieve($l_sql);
        }
    }

} // class

?>