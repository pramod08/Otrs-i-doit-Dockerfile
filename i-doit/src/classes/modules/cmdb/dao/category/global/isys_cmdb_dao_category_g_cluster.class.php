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
 * DAO: global category for clusters
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_cluster extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'cluster';

    /**
     * Category entry is purgable.
     *
     * @var boolean
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the primary IP of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_administration_service($p_row)
    {
        global $g_comp_database;

        $l_return    = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao       = isys_cmdb_dao_category_g_cluster::instance($g_comp_database);
        $l_res       = $l_dao->get_administration_services($p_row["isys_obj__id"]);

        while ($l_row = $l_res->get_row())
        {
            if ($l_row["isys_catg_cluster_adm_service_list__status"] == C__RECORD_STATUS__NORMAL)
            {
                $l_return[] = $l_quickinfo->get_quick_info(
                    $l_row["isys_obj__id"],
                    _L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &rarr; " . $l_row["isys_obj__title"],
                    C__LINK__OBJECT
                );
            } // if
        } // while

        if (count($l_return) == 0)
        {
            return '';
        } // if

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    } // function

    /**
     * Dynamic property handling for getting the cluster members of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_cluster_members($p_row)
    {
        global $g_comp_database;

        $l_return    = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao       = isys_cmdb_dao_category_g_cluster::instance($g_comp_database);
        $l_res       = $l_dao->get_cluster_members($p_row["isys_obj__id"]);

        while ($l_row = $l_res->get_row())
        {
            if ($l_row["isys_catg_cluster_members_list__status"] == C__RECORD_STATUS__NORMAL)
            {
                $l_return[] = $l_quickinfo->get_quick_info(
                    $l_row["isys_obj__id"],
                    _L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &rarr; " . $l_row["isys_obj__title"],
                    C__LINK__OBJECT
                );
            } // if
        } // while

        if (count($l_return) == 0)
        {
            return '';
        } // if

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    } // function

    /**
     * Dynamic property handling for getting the cluster services of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_cluster_service($p_row)
    {
        global $g_comp_database;

        $l_return    = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao       = isys_cmdb_dao_category_g_cluster::instance($g_comp_database);
        $l_res       = $l_dao->get_cluster_services($p_row["isys_obj__id"]);

        while ($l_row = $l_res->get_row())
        {
            $l_return[] = $l_quickinfo->get_quick_info(
                $l_row["isys_obj__id"],
                _L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &rarr; " . $l_row["isys_obj__title"],
                C__LINK__OBJECT
            );
        } // while

        if (count($l_return) == 0)
        {
            return '';
        } // if

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    } // function

    /**
     * Trigger save process of global category cluster.
     *
     * @param   integer $p_cat_level        level to save, default 0
     * @param   integer &$p_intOldRecStatus __status of record before update
     *
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode = -1;
        $l_bRet         = false;
        $l_catdata      = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $p_intOldRecStatus = $l_catdata["isys_catg_cluster_list__status"];

        if ($l_catdata["isys_catg_cluster_list__id"] != "")
        {
            $l_bRet = $this->save(
                $l_catdata["isys_catg_cluster_list__id"],
                $_POST['C__CATG__CLUSTER__QUORUM'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet == true ? null : $l_intErrorCode;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_quorum
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_quorum, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "UPDATE isys_catg_cluster_list SET
			isys_catg_cluster_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_cluster_list__quorum = " . $this->convert_sql_id($p_quorum) . ",
			isys_catg_cluster_list__status = " . $this->convert_sql_int($p_status) . "
			WHERE isys_catg_cluster_list__id = " . $this->convert_sql_id($p_cat_level);

        return $this->update($l_strSql) && $this->apply_update();
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catg_cluster__id $p_fk_id.
     *
     * @param   integer $p_objID
     * @param   boolean $p_quorum
     * @param   string  $p_description
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_quorum = false, $p_description = "")
    {
        $l_id = $this->create_connector('isys_catg_cluster_list', $p_objID);
        if ($this->save($l_id, $p_quorum, $p_description))
        {
            return $l_id;
        }
        return false;
    } // function

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_cluster_services($p_obj_id = null, $p_cat_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cluster_service_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_service_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_catg_cluster_service_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_obj_id))
        {
            $l_sql .= " AND isys_catg_cluster_service_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        } // if

        if (!empty($p_cat_id))
        {
            $l_sql .= " AND isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cat_id);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_cluster_members($p_obj_id = null, $p_cat_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cluster_members_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_catg_cluster_members_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_obj_id))
        {
            $l_sql .= " AND isys_catg_cluster_members_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        } // if

        if (!empty($p_cat_id))
        {
            $l_sql .= " AND isys_catg_cluster_members_list__id = " . $this->convert_sql_id($p_cat_id);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_administration_services($p_obj_id = null, $p_cat_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cluster_adm_service_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_catg_cluster_adm_service_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_obj_id))
        {
            $l_sql .= " AND isys_catg_cluster_adm_service_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        } // if

        if (!empty($p_cat_id))
        {
            $l_sql .= " AND isys_catg_cluster_adm_service_list__id = " . $this->convert_sql_id($p_cat_id);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_administration_service' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Administration service'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_administration_service'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_cluster_members'        => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_MEMBERS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster members'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_cluster_members'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_cluster_service'        => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster services'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_cluster_service'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Returns how many entries exists. The folder always returns 1.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == C__CATG__CLUSTER_ROOT)
        {
            return 1;
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
    public function properties()
    {
        return [
            'quorum'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER__QUORUM',
                        C__PROPERTY__INFO__DESCRIPTION => 'Quorum'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_list__quorum'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__CLUSTER__QUORUM',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData' => get_smarty_arr_YES_NO()
                        ],
                        C__PROPERTY__UI__DEFAULT => 0
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
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
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CLUSTER
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
            $this->m_sync_catg_data = $p_category_data;
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                if ($p_object_id > 0)
                {
                    return $this->create(
                        $p_object_id,
                        $p_category_data['properties']['quorum'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );
                }
            }
            elseif ($p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                if ($p_category_data['data_id'] > 0)
                {
                    $this->save(
                        $p_category_data['data_id'],
                        $p_category_data['properties']['quorum'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL
                    );

                    return $p_category_data['data_id'];
                }
            } // if
        } // if
        return false;
    } // function
} // class