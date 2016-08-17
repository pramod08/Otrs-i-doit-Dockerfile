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
 * DAO: global category for shares.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_shares extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'shares';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the drive dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_volume(isys_request $p_request)
    {
        return $this->get_dialog_content_drive($p_request->get_object_id());
    }

    /**
     * Callback method for the "catdata" browser. Maybe we can switch the first parameter to an instance of isys_request?
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function catdata_browser($p_obj_id)
    {
        $l_return = [];
        $l_res    = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_res->get_row())
        {
            $l_val = "<strong>" . isys_glob_str_stop($l_row["isys_catg_shares_list__title"], 30) . "</strong>" . " (UNC: " . isys_glob_str_stop(
                    str_replace("\\", "/", $l_row["isys_catg_shares_list__unc_path"]),
                    30
                ) . ")";

            $l_return[$l_row['isys_catg_shares_list__id']] = $l_val;
        } // while

        return $l_return;
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
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_shares_list " . "LEFT JOIN isys_catg_drive_list " . "ON " . "isys_catg_drive_list__id = " . "isys_catg_shares_list__isys_catg_drive_list__id " . "INNER JOIN isys_obj " . "ON " . "isys_catg_shares_list__isys_obj__id = " . "isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id))
        {
            $l_sql .= " AND (isys_catg_shares_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND (isys_catg_shares_list__status = '{$p_status}')";
        }

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
            'title'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARES__SHARE_NAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SHARES__SHARE_NAME'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SHARES__TITLE'
                    ]
                ]
            ),
            'unc_path'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARES__UNC_PATH',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SHARES__UNC_PATH'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__unc_path'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SHARES__UNC_PATH'
                    ]
                ]
            ),
            'volume'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARES__VOLUME',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SHARES__VOLUME'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_shares_list__isys_catg_drive_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_drive_list',
                            'isys_catg_drive_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SHARES__VOLUME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_shares',
                                    'callback_property_volume'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]
            ),
            'path'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARES__LOCAL_PATH',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SHARES__LOCAL_PATH'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__path'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__SHARES__PATH'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__SHARES
                    ]
                ]
            )
        ];
    } // function

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
                        $this->get_property('title'),
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('unc_path'),
                        $this->get_property('path'),
                        $this->get_property('volume'),
                        $this->get_property('description')
                    );
                    if ($p_category_data['data_id'])
                    {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        $this->get_property('title'),
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('unc_path'),
                        $this->get_property('path'),
                        $this->get_property('volume'),
                        $this->get_property('description')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Add new graphic adapter
     *
     * @param string $p_title
     * @param string $p_manufacturer_id
     * @param string $p_memory
     * @param string $p_memory_unit_id
     */
    public function create($p_object_id, $p_title, $p_status, $p_unc_path, $p_path, $p_volume, $p_description)
    {

        $l_sql = "INSERT INTO isys_catg_shares_list " . "SET " . "isys_catg_shares_list__title = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_shares_list__status = " . $this->convert_sql_id($p_status) . ", " . "isys_catg_shares_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_shares_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ", " . "isys_catg_shares_list__unc_path = " . $this->convert_sql_text(
                $p_unc_path
            ) . ", " . "isys_catg_shares_list__path = " . $this->convert_sql_text(
                $p_path
            ) . ", " . "isys_catg_shares_list__isys_catg_drive_list__id = " . $this->convert_sql_id($p_volume) . "" . ";";

        if ($this->update($l_sql))
        {
            if ($this->apply_update())
            {
                $this->m_strLogbookSQL = $l_sql;

                return $this->get_last_insert_id();
            }
        }

        return false;
    } // function

    /**
     * Updates an existing
     *
     * @param string $p_id
     * @param string $p_title
     * @param string $p_memory
     * @param string $p_memory_unit_id
     */
    public function save($p_id, $p_title, $p_status, $p_unc_path, $p_path, $p_volume, $p_description)
    {

        if (is_numeric($p_id))
        {
            $l_sql = "UPDATE isys_catg_shares_list " . "SET " . "isys_catg_shares_list__title = " . $this->convert_sql_text(
                    $p_title
                ) . ", " . "isys_catg_shares_list__status = '" . $p_status . "', " . "isys_catg_shares_list__description = " . $this->convert_sql_text(
                    $p_description
                ) . ", " . "isys_catg_shares_list__unc_path = " . $this->convert_sql_text($p_unc_path) . ", " . "isys_catg_shares_list__path = " . $this->convert_sql_text(
                    $p_path
                ) . ", " . "isys_catg_shares_list__isys_catg_drive_list__id = " . $this->convert_sql_id(
                    $p_volume
                ) . " " . "WHERE " . "(isys_catg_shares_list__id = '" . $p_id . "')" . ";";

            if ($this->update($l_sql))
            {
                $this->m_strLogbookSQL = $l_sql;

                return $this->apply_update();
            }
        }

        return false;
    } // function

    /**
     * @param   integer $p_cat_level
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status)
    {
        if (empty($_GET[C__CMDB__GET__CATLEVEL]))
        {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST["C__CATG__SHARES__TITLE"],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATG__SHARES__UNC_PATH"],
                $_POST["C__CATG__SHARES__PATH"],
                $_POST["C__CATG__SHARES__VOLUME"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id > 0)
            {
                $p_cat_level = null;

                return $l_id;
            } // if
        }
        else
        {
            $l_catdata = $this->get_result()
                ->__to_array();

            $l_res = $this->save(
                $l_catdata["isys_catg_shares_list__id"],
                $_POST["C__CATG__SHARES__TITLE"],
                $l_catdata["isys_catg_shares_list__status"],
                $_POST["C__CATG__SHARES__UNC_PATH"],
                $_POST["C__CATG__SHARES__PATH"],
                $_POST["C__CATG__SHARES__VOLUME"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_res)
            {
                return null;
            } // if
        } // if

        return false;
    } // function

    public function get_shares_by_obj_id_or_shares_id($p_obj_id, $p_shares_id = null, $p_table = null)
    {
        $l_sql = "SELECT * FROM isys_catg_shares_list WHERE TRUE ";

        if (!empty($p_obj_id))
        {
            $l_sql .= " AND isys_catg_shares_list__isys_obj__id = '" . $p_obj_id . "' ";
        }

        if (!empty($p_shares_id))
        {
            $l_sql .= " AND isys_catg_shares_list__id = '" . $p_shares_id . "' ";
        }

        $l_result = $this->retrieve($l_sql);

        return $l_result;
    } // function

    public function get_objtypes_shares()
    {
        $l_sql = "SELECT * FROM isys_catg_shares_list " . "LEFT JOIN isys_obj ON isys_catg_shares_list__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id";

        return $this->retrieve($l_sql);
    } // function

    public function get_shares_by_cluster_service($p_clusterServiceID)
    {
        $l_sql = "SELECT * FROM isys_catg_shares_list AS t1 " . "LEFT JOIN isys_catg_shares_list_2_isys_catg_cluster_service_list AS t2 " . "ON t2.isys_catg_shares_list__id = t1.isys_catg_shares_list__id " . "WHERE t2.isys_catg_cluster_service_list__id = " . $this->convert_sql_id(
                $p_clusterServiceID
            );

        return $this->retrieve($l_sql);
    } // function

    public function get_dialog_content_drive($p_obj_id)
    {
        $l_dao = new isys_cmdb_dao_category_g_drive($this->get_database_component());
        $l_res = $l_dao->get_drives(null, $p_obj_id, null, null);
        while ($l_row = $l_res->get_row())
        {
            $l_return[$l_row['isys_catg_drive_list__id']] = $l_row['isys_catg_drive_list__title'];
        }

        return $l_return;
    } // function

    /**
     * Builds an array with minimal requirements for the sync function
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'       => [
                    'value' => $p_data['title']
                ],
                'unc_path'    => [
                    'value' => $p_data['unc_path']
                ],
                'path'        => [
                    'value' => $p_data['path']
                ],
                'description' => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

} // class
?>