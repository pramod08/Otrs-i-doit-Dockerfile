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
 * DAO: specific category for cluster services.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_cluster_service extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_service';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';
    /**
     * @var bool
     */
    protected $m_has_relation = true;
    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;
    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_cluster_service_list__isys_obj__id';
    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_cluster_service_list';

    /**
     * Dynamic property handling for retrieving the assigned clusters.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_cluster(array $p_row)
    {
        global $g_comp_database;

        $l_return    = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();

        $l_dao = isys_cmdb_dao_category_s_cluster_service::instance($g_comp_database);
        $l_res = $l_dao->get_data(null, $p_row['isys_obj__id']);

        if ($l_res->num_rows() > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_row !== false && $l_row['isys_obj__id'] > 0)
                {
                    $l_return[] = $l_quickinfo->get_quick_info(
                        $l_row['isys_obj__id'],
                        _L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &raquo; " . $l_row["isys_obj__title"],
                        C__LINK__OBJECT
                    );
                } // if
            } // while
        } // if

        return implode('<br /> ', $l_return);
    }

    /**
     * Save specific category room.
     *
     * @param  $p_cat_level          Level to save, default 0.
     * @param  & $p_intOldRecStatus  Status of record before update.
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_ret = null;

        $l_dao = new isys_cmdb_dao_category_g_cluster_service($this->get_database_component());

        if (empty($p_cat_level))
        {
            $p_cat_level = $_GET[C__CMDB__GET__CATLEVEL];
        } // if

        $l_catdata         = $l_dao->get_data($p_cat_level)
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_catg_cluster_service_list__status"];
        $l_id              = $l_catdata['isys_catg_cluster_service_list__id'];

        if ($p_cat_level > 0)
        {
            $l_ret = $this->save(
                $l_id,
                $_POST["C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER__HIDDEN"],
                $_POST['C__CATS__CLUSTER_SERVICE__TYPE'],
                $_POST['C__CATS__CLUSTER_SERVICE__RUNS_ON__selected_values'],
                $_POST['C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $p_intOldRecStatus,
                $_POST['C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA__HIDDEN']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
            $p_cat_level           = 1;
            $l_ret                 = null;
        }
        else
        {
            $l_id = $this->create(
                $_POST["C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER__HIDDEN"],
                C__RECORD_STATUS__NORMAL,
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CATS__CLUSTER_SERVICE__TYPE'],
                $_POST['C__CATS__CLUSTER_SERVICE__RUNS_ON__selected_values'],
                $_POST['C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA__HIDDEN']
            );

            if ($l_id != false)
            {
                $this->m_strLogbookSQL = $this->get_last_query();
            } // if

            $p_cat_level = 1;
            $l_ret       = $l_id;
        } // if

        // Clearance.
        $l_dao->detach_shares($l_id);
        $l_dao->detach_drives($l_id);
        $l_dao->detach_addresses($l_id);

        $l_drive_arr = isys_format_json::decode($_POST["C__CATS__CLUSTER_SERVICE__VOLUMES__HIDDEN"], true);

        if (is_array($l_drive_arr))
        {
            foreach ($l_drive_arr as $l_drive)
            {
                if ($l_drive > 0) $l_dao->bind_drive($l_id, $l_drive);
            }
        }

        $l_hostaddr_arr = isys_format_json::decode($_POST["C__CATS__CLUSTER_SERVICE__HOST_ADDRESSES__HIDDEN"], true);

        if (is_array($l_hostaddr_arr))
        {
            foreach ($l_hostaddr_arr as $l_hostaddr)
            {
                if ($l_hostaddr > 0) $l_dao->bind_hostaddress($l_id, $l_hostaddr);
            }
        }

        $l_share_arr = isys_format_json::decode($_POST["C__CATS__CLUSTER_SERVICE__SHARES__HIDDEN"], true);

        if (is_array($l_share_arr))
        {
            foreach ($l_share_arr AS $l_share)
            {
                if ($l_share > 0) $l_dao->bind_share($l_id, $l_share);
            }
        }

        return $l_ret;
    } // function

    /**
     * Save
     *
     * @param int    $p_id
     * @param array  $p_cluster_id
     * @param int    $p_type
     * @param string $p_runs_on Comma separated IDs
     * @param int    $p_default_server
     * @param string $p_description
     * @param int    $p_status
     * @param int    $p_database_schemata_obj
     *
     * @return bool
     * @throws \Exception
     * @throws \isys_exception_cmdb
     * @throws \isys_exception_dao
     */
    public function save($p_id, $p_cluster_id, $p_type, $p_runs_on, $p_default_server, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_database_schemata_obj = null)
    {
        $l_dao_catg_cluster_service = new isys_cmdb_dao_category_g_cluster_service($this->get_database_component());

        if (!is_null($p_runs_on))
        {
            $l_dao_catg_cluster_service->detach_members($p_id);

            if (is_array($p_runs_on))
            {
                foreach ($p_runs_on as $l_member_id)
                {
                    $l_dao_catg_cluster_service->cluster_runs_on($p_id, $l_member_id);
                } // foreach
            }
            else
            {
                foreach (explode(",", $p_runs_on) as $l_member_id)
                {
                    $l_dao_catg_cluster_service->cluster_runs_on($p_id, $l_member_id);
                } // foreach
            } // if
        } // if

        $l_strSql = "UPDATE isys_catg_cluster_service_list " . "SET " . "isys_catg_cluster_service_list__isys_obj__id = " . $this->convert_sql_id(
                $p_cluster_id
            ) . ", " . "isys_catg_cluster_service_list__isys_cluster_type__id = " . $this->convert_sql_id(
                $p_type
            ) . ", " . "isys_catg_cluster_service_list__cluster_members_list__id = " . $this->convert_sql_id(
                $p_default_server
            ) . ", " . "isys_catg_cluster_service_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_cluster_service_list__status = " . $this->convert_sql_id(
                $p_status
            ) . " " . "WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql))
        {
            if ($this->apply_update())
            {
                $l_data = $this->get_data($p_id)
                    ->__to_array();

                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                $l_relation_dao->handle_relation(
                    $p_id,
                    "isys_catg_cluster_service_list",
                    C__RELATION_TYPE__CLUSTER_SERVICE,
                    $l_data["isys_catg_cluster_service_list__isys_catg_relation_list__id"],
                    $l_data["isys_catg_cluster_service_list__isys_obj__id"],
                    $p_cluster_id
                );

                if ($p_cluster_id > 0)
                {
                    // Link DBMS
                    isys_cmdb_dao_category_g_cluster_service::handle_dbms(
                        $l_data['isys_catg_cluster_service_list__isys_catg_relation_list__id'],
                        $p_database_schemata_obj
                    );
                } // if

                return true;
            }
            else
            {
                return false;
            } // if
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_connected_objID
     * @param   integer $p_cluster_type_id
     * @param   string  $p_selected_members
     * @param   integer $p_default_member
     * @param   string  $p_description
     *
     * @return  mixed  The newly created ID as integer or boolean false.
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_connected_objID, $p_cluster_type_id, $p_selected_members, $p_default_member, $p_description, $p_database_schemata_obj = null)
    {
        $l_dao_con                  = new isys_cmdb_dao_connection($this->get_database_component());
        $l_dao_catg_cluster_service = new isys_cmdb_dao_category_g_cluster_service($this->get_database_component());

        $l_con_id = $l_dao_con->add_connection($p_connected_objID);

        $l_sql = "INSERT INTO isys_catg_cluster_service_list (
				isys_catg_cluster_service_list__isys_obj__id,
				isys_catg_cluster_service_list__status,
				isys_catg_cluster_service_list__isys_cluster_type__id,
				isys_catg_cluster_service_list__cluster_members_list__id,
				isys_catg_cluster_service_list__isys_connection__id,
				isys_catg_cluster_service_list__description
			) VALUES (
				" . $this->convert_sql_id($p_objID) . ",
				" . $p_newRecStatus . ",
				" . $this->convert_sql_id($p_cluster_type_id) . ",
				" . $this->convert_sql_id($p_default_member) . ",
				" . $this->convert_sql_id($l_con_id) . ",
				" . $this->convert_sql_text($p_description) . "
			)";

        $this->update($l_sql);

        if ($this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            if (!is_null($p_selected_members))
            {
                if (is_array($p_selected_members))
                {
                    foreach ($p_selected_members as $l_member_id)
                    {
                        $l_dao_catg_cluster_service->cluster_runs_on($l_last_id, $l_member_id);
                    } // foreach
                }
                else
                {
                    foreach (explode(",", $p_selected_members) as $l_member_id)
                    {
                        $l_dao_catg_cluster_service->cluster_runs_on($l_last_id, $l_member_id);
                    } // foreach
                } // if
            } // if

            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->handle_relation(
                $l_last_id,
                "isys_catg_cluster_service_list",
                C__RELATION_TYPE__CLUSTER_SERVICE,
                null,
                $p_objID,
                $p_connected_objID
            );

            if ($p_connected_objID > 0)
            {
                // Link DBMS
                isys_cmdb_dao_category_g_cluster_service::handle_dbms(
                    $this->get_data($l_last_id)
                        ->get_row_value('isys_catg_cluster_service_list__isys_catg_relation_list__id'),
                    $p_database_schemata_obj
                );
            } // if

            return $l_last_id;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_cluster' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned clusters'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_cluster'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    }

    /**
     * Method for retrieving the number of objects, assigned to an object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {

        if ($p_obj_id !== null)
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        $l_sql = "SELECT count(isys_obj__id) AS count FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_catg_cluster_service_list ON isys_catg_cluster_service_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " . "AND (isys_catg_cluster_service_list__id IS NOT NULL) ";

        if ($l_obj_id !== null)
        {
            $l_sql .= "AND (isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        } // if

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return (int) $l_data["count"];
    } // function

    /**
     * Method for retrieving category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_cluster_service_list " . "INNER JOIN isys_obj " . "ON isys_catg_cluster_service_list__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_cluster_type " . "ON isys_cluster_type__id = isys_catg_cluster_service_list__isys_cluster_type__id " . "LEFT JOIN isys_connection " . "ON isys_connection__id = isys_catg_cluster_service_list__isys_connection__id " . "LEFT JOIN isys_catg_cluster_members_list " . "ON isys_catg_cluster_members_list__id = isys_catg_cluster_service_list__cluster_members_list__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_cats_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cats_list_id) . ")";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_catg_cluster_service_list__status = " . $this->convert_sql_int($p_status) . ")";
        } // if

        $l_sql .= " AND (isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        return $this->retrieve($l_sql . ";");
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
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

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
            'cluster'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned clusters'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_cluster_service_list__isys_obj__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__CLUSTER_SERVICE,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_s_cluster_service',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_s_cluster_service']
                        ),
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ]
                ]
            ),
            'type'                     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cluster_service_list__isys_cluster_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cluster_type',
                            'isys_cluster_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATS__CLUSTER_SERVICE__TYPE',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable' => 'isys_cluster_type'
                        ],
                        C__PROPERTY__UI__DEFAULT => C__CLUSTER_TYPE__ACTIVE_ACTIVE
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'runs_on'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
                        C__PROPERTY__INFO__DESCRIPTION => 'Runs on'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_service_list__id',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CLUSTER_SERVICE__RUNS_ON'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'cluster_runs_on'
                        ]
                    ]
                ]
            ),
            'default_server'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Default server'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_cluster_members_list__isys_connection__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_cluster_members_list'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
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
            'hostaddresses'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Host addresses'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cluster_service_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_ip_list_2_isys_catg_cluster_service_list',
                            'isys_catg_cluster_service_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CLUSTER_SERVICE__HOST_ADDRESSES',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => 'true',
                            'p_strPopupType' => 'browser_cat_data',
                            'dataretrieval'  => 'isys_cmdb_dao_category_g_ip::catdata_browser'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'hostaddress'
                        ]
                    ]
                ]
            ),
            'drives'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__VOLUMES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Volumes'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_service_list__property'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CLUSTER_SERVICE__VOLUMES',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => 'true',
                            'p_strPopupType' => 'browser_cat_data',
                            'dataretrieval'  => 'isys_cmdb_dao_category_g_drive::catdata_browser'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'cluster_drives'
                        ]
                    ]
                ]
            ),
            'shares'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__SHARES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Shares'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_service_list__isys_obj__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CLUSTER_SERVICE__SHARES',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => 'true',
                            'p_strPopupType' => 'browser_cat_data',
                            'dataretrieval'  => 'isys_cmdb_dao_category_g_shares::catdata_browser'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'cluster_shares'
                        ]
                    ]
                ]
            ),
            'assigned_database_schema' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_SCHEMA',
                        C__PROPERTY__INFO__DESCRIPTION => 'The assigned database schema for the cluster service'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_cluster_service_list__isys_catg_relation_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_database_access_list',
                            'isys_cats_database_access_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__DATABASE_SCHEMA',
                            'p_strPopupType'                            => 'browser_object_ng',
                            'p_strValue'                                => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_cluster_service',
                                    'callback_property_assigned_database_schema'
                                ]
                            ),
                            'p_strSelectedID'                           => ''
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'cluster_service_database_schema'
                        ]
                    ]
                ]
            ),
            'description'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_service_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__CLUSTER_SERVICE
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            )
        ];
    } // function

    /**
     * Rank records.
     *
     * @param   array  $p_objects
     * @param   string $p_direction
     * @param   string $p_table
     *
     * @return  boolean
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        $l_dao_cluster_service = new isys_cmdb_dao_category_g_cluster_service($this->get_database_component());

        switch ($_POST[C__GET__NAVMODE])
        {
            case C__NAVMODE__ARCHIVE:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;

            case C__NAVMODE__DELETE:
                $l_status = C__RECORD_STATUS__DELETED;
                break;

            case C__NAVMODE__RECYCLE:
                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED)
                {
                    $l_status = C__RECORD_STATUS__NORMAL;
                }
                else if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED)
                {
                    $l_status = C__RECORD_STATUS__ARCHIVED;
                } // if
                break;

            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($_POST["id"]))
                {
                    foreach ($_POST["id"] AS $l_val)
                    {
                        $l_dao_cluster_service->delete($l_val);
                    } // foreach

                    unset($_POST["id"]);
                } // if

                return true;
        } // switch

        foreach ($p_objects AS $l_cat_id)
        {
            $l_dao_cluster_service->set_status($l_cat_id, $l_status);
        } // foreach

        return true;
    } // function

    /**
     * Don't need to syncronize because the global category cluster service will handle it.
     *
     * @return  boolean
     */
    public function sync($p_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        return true;
    } // function

} // class

?>