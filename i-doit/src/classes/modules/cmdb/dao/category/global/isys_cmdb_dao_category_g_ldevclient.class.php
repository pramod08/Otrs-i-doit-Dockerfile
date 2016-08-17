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
 * DAO: global category for logical device clients
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_ldevclient extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'ldevclient';
    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__LDEV_CLIENT';
    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CATG__LDEV_CLIENT;
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_sanpool_list__isys_obj__id';
    /**
     * @var bool
     */
    protected $m_has_relation = true;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;
    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_ldevclient_list__isys_obj__id';

    /**
     * Callback method for the notification option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assigned_ldevserver(isys_request $p_request)
    {
        return isys_cmdb_dao_category_g_sanpool::instance($this->get_database_component())
            ->object_types();
    } // function

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catd_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_ldevclient_list " . "LEFT OUTER JOIN isys_catg_sanpool_list " . "ON " . "isys_catg_ldevclient_list__isys_catg_sanpool_list__id = isys_catg_sanpool_list__id " . "LEFT OUTER JOIN isys_obj " . "ON " . "isys_catg_ldevclient_list__isys_obj__id = " . "isys_obj__id " . "LEFT OUTER JOIN isys_ldev_multipath " . "ON " . "isys_catg_ldevclient_list__isys_ldev_multipath__id = isys_ldev_multipath__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catd_list_id))
        {
            $l_sql .= " AND isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_catd_list_id);
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND isys_catg_ldevclient_list__status = " . $this->convert_sql_id($p_status);
        }

        return $this->retrieve($l_sql);
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
            'title'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_ldevclient_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__LDEVCLIENT_TITLE'
                    ]
                ]
            ),
            'paths'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__PATH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Path'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_ldevclient_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LDEVCLIENT_PATHS',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => true,
                            // @todo Property Callback for multiedit (in future).
                            'p_strPopupType' => 'browser_fc_port'
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
                            'ldevclient_assigned_paths'
                        ]
                    ]
                ]
            ),
            'assigned_ldevserver' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LDEV_SERVER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Logical devices (LDEV Server)'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_ldevclient_list__isys_catg_sanpool_list__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__LDEV_CLIENT,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_ldevclient',
                                'callback_property_relation_handler'
                            ], [
                                'isys_cmdb_dao_category_g_ldevclient',
                                true
                            ]
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_catg_sanpool_list',
                            'isys_catg_sanpool_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LDEVCLIENT_SANPOOL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType'   => 'browser_object_ng',
                            'secondSelection'  => true,
                            'catFilter'        => 'C__CATG__SANPOOL;C__CATG__LDEV_CLIENT;C__CATG__LDEV_SERVER',
                            'secondList'       => 'isys_cmdb_dao_category_g_sanpool::object_browser',
                            'secondListFormat' => 'isys_cmdb_dao_category_g_sanpool::format_selection',
                            'readOnly'         => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'ldevclient_ldevserver'
                        ]
                    ]
                ]
            ),
            'primary_path'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LDEVCLIENT__PRIMARY_PATH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Primary path'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_ldevclient_list__primary_path'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__LDEVCLIENT__PRIMARY_PATH'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH  => false,
                        C__PROPERTY__PROVIDES__REPORT  => false,
                        C__PROPERTY__PROVIDES__LIST    => false,
                        C__PROPERTY__PROVIDES__VIRTUAL => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]
            ),
            'multipath'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LDEV_MULTI_PATH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Multipath technology'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_ldevclient_list__isys_ldev_multipath__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_ldev_multipath',
                            'isys_ldev_multipath__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LDEVCLIENT_MULTIPATH',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_ldev_multipath'
                        ]
                    ]
                ]
            ),
            'description'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_ldevclient_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__LDEV_CLIENT
                    ]
                ]
            )
        ];
    } // function

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_ldevserver'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_hba'][C__DATA__VALUE],
                            $p_category_data['properties']['paths'][C__DATA__VALUE],
                            $p_category_data['properties']['primary_path'][C__DATA__VALUE],
                            $p_category_data['properties']['multipath'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_ldevserver'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_hba'][C__DATA__VALUE],
                            $p_category_data['properties']['paths'][C__DATA__VALUE],
                            $p_category_data['properties']['primary_path'][C__DATA__VALUE],
                            $p_category_data['properties']['multipath'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        }

        return false;
    }

    public function delete_ldevclient($p_ldevclient_id = null, $p_sanpool_id = null, $p_obj_id = null)
    {

        $l_sql = "DELETE FROM isys_catg_ldevclient_list WHERE TRUE";

        if (!is_null($p_ldevclient_id))
        {
            $l_sql .= " AND isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_ldevclient_id);
        }

        if (!is_null($p_sanpool_id))
        {
            $l_sql .= " AND isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_sanpool_id);
        }

        if (!is_null($p_obj_id))
        {
            $l_sql .= " AND isys_catg_ldevclient_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        }

        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function create($p_objID, $p_newRecStatus, $p_title, $p_sanpool_id, $p_hbaID = null, $p_fc_paths, $p_fc_prim, $p_multipathID, $p_description = '')
    {

        $l_update = "INSERT INTO isys_catg_ldevclient_list SET " . "isys_catg_ldevclient_list__title = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_ldevclient_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_ldevclient_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . ", " . "isys_catg_ldevclient_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . ", " . "isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id(
                $p_sanpool_id
            ) . ", " . "isys_catg_ldevclient_list__isys_ldev_multipath__id = " . $this->convert_sql_id(
                $p_multipathID
            ) . ", " . "isys_catg_ldevclient_list__primary_path = " . $this->convert_sql_id($p_fc_prim);

        if ($this->update($l_update) && $this->apply_update())
        {
            $l_id = $this->get_last_insert_id();

            if (!empty($p_fc_paths)) $this->attach_paths($l_id, $p_fc_paths);
            /* Create implicit relation */
            if (!empty($p_sanpool_id))
            {

                $l_dao_sanpool  = new isys_cmdb_dao_category_g_sanpool($this->get_database_component());
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                $l_data         = $l_dao_sanpool->get_data($p_sanpool_id)
                    ->__to_array();

                $l_relation_dao->handle_relation(
                    $l_id,
                    "isys_catg_ldevclient_list",
                    C__RELATION_TYPE__LDEV_CLIENT,
                    null,
                    $l_data["isys_catg_sanpool_list__isys_obj__id"],
                    $p_objID
                );

            }

            return $l_id;
        }
        else
            return false;
    }

    public function save($p_id, $p_newRecStatus, $p_title, $p_sanpool_id, $p_hbaID = null, $p_fc_paths, $p_fc_prim, $p_multipathID, $p_description)
    {

        $l_update = "UPDATE isys_catg_ldevclient_list SET " . "isys_catg_ldevclient_list__title = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_ldevclient_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_ldevclient_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . ", " . "isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id(
                $p_sanpool_id
            ) . ", " . "isys_catg_ldevclient_list__isys_ldev_multipath__id = " . $this->convert_sql_id(
                $p_multipathID
            ) . ", " . "isys_catg_ldevclient_list__primary_path = " . $this->convert_sql_id($p_fc_prim);

        $l_update .= " WHERE isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_update))
        {

            if ($this->apply_update())
            {

                if (!empty($p_fc_paths))
                {
                    $this->detach_paths($p_id);
                    $this->attach_paths($p_id, $p_fc_paths);
                }
                else
                {
                    $this->detach_paths($p_id);
                }

                /* Create implicit relation */
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                $l_dao_sanpool  = new isys_cmdb_dao_category_g_sanpool($this->get_database_component());

                if (!empty($p_sanpool_id)) $l_sanpool_data = $l_dao_sanpool->get_data($p_sanpool_id)
                    ->__to_array();

                $l_data = $this->get_data($p_id)
                    ->__to_array();

                $l_relation_dao->handle_relation(
                    $p_id,
                    "isys_catg_ldevclient_list",
                    C__RELATION_TYPE__LDEV_CLIENT,
                    $l_data["isys_catg_ldevclient_list__isys_catg_relation_list__id"],
                    $l_sanpool_data["isys_catg_sanpool_list__isys_obj__id"],
                    $l_data["isys_catg_ldevclient_list__isys_obj__id"]
                );

                return true;
            }
        }
        else
        {
            return false;
        }
    }

    public function attach_paths($p_id, $p_path)
    {
        if (is_string($p_path))
        {
            $l_strPath  = rtrim($p_path, ',');
            $l_path_arr = explode(",", $l_strPath);
        }
        else
        {
            $l_path_arr = $p_path;
        }
        if (count($l_path_arr) > 0)
        {
            $l_update = "INSERT INTO isys_ldevclient_fc_port_path (isys_ldevclient_fc_port_path__isys_catg_ldevclient_list__id, isys_ldevclient_fc_port_path__isys_catg_fc_port_list__id) VALUES ";
            $l_exe    = false;
            foreach ($l_path_arr as $l_path)
            {
                if (!empty($l_path) && $l_path > 0)
                {
                    $l_update .= "(" . $p_id . ", " . $l_path . "),";
                    $l_exe = true;
                }
            }
            $l_update = rtrim($l_update, ',');

            if (!$l_exe) return false;

            if ($this->update($l_update) && $this->apply_update())
            {
                return true;
            }
        }

        return false;
    }

    public function detach_paths($p_id)
    {

        $l_update = "DELETE FROM isys_ldevclient_fc_port_path " . "WHERE isys_ldevclient_fc_port_path__isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_update)) return $this->apply_update();
        else
            return false;
    }

    public function get_paths($p_id)
    {
        $l_query = "SELECT * FROM isys_ldevclient_fc_port_path " . "INNER JOIN isys_catg_fc_port_list ON isys_catg_fc_port_list__id = isys_ldevclient_fc_port_path__isys_catg_fc_port_list__id " . "WHERE isys_ldevclient_fc_port_path__isys_catg_ldevclient_list__id = " . $this->convert_sql_id(
                $p_id
            );

        return $this->retrieve($l_query);
    }

    /**
     * Retrieve the primary path of a client.
     *
     * @param   integer $p_listID
     *
     * @return  array
     */
    public function get_primary_path($p_listID)
    {
        $l_sql = 'SELECT isys_catg_ldevclient_list__primary_path
			FROM isys_catg_ldevclient_list
			WHERE isys_catg_ldevclient_list__id = ' . $this->convert_sql_id($p_listID) . ';';

        return $this->retrieve($l_sql)
            ->get_row();
    } // function

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_result()
            ->__to_array();

        if ($p_create)
        {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__LDEVCLIENT_TITLE'],
                $_POST['C__CATG__LDEVCLIENT_SANPOOL__HIDDEN'],
                $_POST['C__CATG__LDEVCLIENT_HBA'],
                $_POST['C__CATG__LDEVCLIENT_PATHS__HIDDEN'],
                $_POST['C__CATG__LDEVCLIENT_PATHS__VIEW__PRIM'],
                $_POST['C__CATG__LDEVCLIENT_MULTIPATH'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id)
            {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level           = -1;

                return $l_id;
            } // if
        }
        else
        {
            $l_bRet = $this->save(
                $l_catdata['isys_catg_ldevclient_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__LDEVCLIENT_TITLE'],
                $_POST['C__CATG__LDEVCLIENT_SANPOOL__HIDDEN'],
                $_POST['C__CATG__LDEVCLIENT_HBA'],
                $_POST['C__CATG__LDEVCLIENT_PATHS__HIDDEN'],
                $_POST['C__CATG__LDEVCLIENT_PATHS__VIEW__PRIM'],
                $_POST['C__CATG__LDEVCLIENT_MULTIPATH'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet;
    } // function

    /**
     * Method for detaching a sanpool by ID.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function detach_sanpool($p_id)
    {
        $l_sql = 'UPDATE isys_catg_ldevclient_list
			SET isys_catg_ldevclient_list__isys_catg_sanpool_list__id = NULL
			WHERE isys_catg_ldevclient_list__isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Retrieve clients by a given sanpool-ID.
     *
     * @param   integer $p_sanpool_id
     *
     * @return  isys_component_dao_result
     */
    public function get_clients($p_sanpool_id)
    {
        return $this->retrieve(
            'SELECT * FROM isys_catg_ldevclient_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id
			WHERE isys_catg_ldevclient_list__isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_sanpool_id) . '
			AND isys_catg_ldevclient_list__status = ' . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . ';'
        );
    } // function

    /**
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_clients_by_object($p_objID)
    {
        $l_sql = "SELECT * FROM isys_catg_ldevclient_list " . "LEFT OUTER JOIN isys_catg_sanpool_list ON isys_catg_ldevclient_list__isys_catg_sanpool_list__id = isys_catg_sanpool_list__id " . "LEFT OUTER JOIN isys_obj ON isys_catg_ldevclient_list__isys_obj__id = isys_obj__id " . "WHERE isys_catg_ldevclient_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . ";";

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_objTypeID
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_clients_by_object_type($p_objTypeID, $p_status)
    {
        $l_query = "SELECT * FROM isys_obj_type " . "INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id " . "WHERE isys_obj__id IN (SELECT isys_catg_ldevclient_list__isys_obj__id FROM isys_catg_ldevclient_list WHERE isys_catg_ldevclient_list__status = " . $this->convert_sql_id(
                $p_status
            ) . ") " . "AND isys_obj_type__id = " . $this->convert_sql_id($p_objTypeID) . ";";

        return $this->retrieve($l_query);
    } // function

    /**
     * Retrieve infos of a certain ldev-client.
     *
     * @param   array $p_clients
     *
     * @return  isys_component_dao_result
     */
    public function get_client_info(array $p_clients = [])
    {
        $l_query = "SELECT isys_catg_ldevclient_list__title, isys_obj__title, isys_obj__id, isys_obj__isys_obj_type__id, isys_catg_ldevclient_list__id " . "FROM isys_catg_ldevclient_list " . "INNER JOIN isys_obj ON isys_obj__id = isys_catg_ldevclient_list__isys_obj__id " . "WHERE FALSE ";

        if (count($p_clients))
        {
            foreach ($p_clients as $l_client)
            {
                if (!empty($l_client))
                {
                    $l_query .= 'OR isys_catg_ldevclient_list__id = ' . $this->convert_sql_id($l_client);
                } // if
            } // foreach
        } // if

        return $this->retrieve($l_query . ";");
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_device_name($p_id)
    {
        $l_query = "SELECT isys_catg_ldevclient_list__title FROM isys_catg_ldevclient_list " . "WHERE isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_id);

        return $this->retrieve($l_query)
            ->get_row_value('isys_catg_ldevclient_list__title');
    } // function
} // class