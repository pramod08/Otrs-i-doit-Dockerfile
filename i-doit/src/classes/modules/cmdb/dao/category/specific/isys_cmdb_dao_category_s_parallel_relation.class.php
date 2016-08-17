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
 * DAO: specific category for parallel relations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_parallel_relation extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'parallel_relation';

    /**
     * Category's table name. Will be used by im- and export.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_cats_relpool_list';

    /**
     * Return Category Data
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_cats_relpool_list " . "INNER JOIN isys_obj " . "ON isys_obj__id = isys_cats_relpool_list__isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null)
        {
            $l_sql .= " AND (isys_cats_relpool_list__id = " . $this->convert_sql_id($p_cats_list_id) . ") ";
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_cats_relpool_list__status = " . $this->convert_sql_int($p_status) . ")";
        } // if

        return $this->retrieve($l_sql);
    }

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
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_relpool_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__RELPL__TITLE'
                    ]
                ]
            ),
            'threshold'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PARALLEL_RELATION__THRESHOLD',
                        C__PROPERTY__INFO__DESCRIPTION => 'Threshold'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_relpool_list__threshold'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__RELPL__THRESHOLD'
                    ]
                ]
            ),
            'rel_pool'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__RELATION__PARALLEL_RELATIONS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Parallel relations'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_relpool_list__isys_obj__id'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'parallel_rel_property_rel_pool'
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
                        C__PROPERTY__DATA__FIELD => 'isys_cats_relpool_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__PARALLEL_RELATION
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
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create_connector(
                    'isys_cats_relpool_list',
                    $p_object_id
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['threshold'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Count relation pool objects
     *
     * @param int $p_relpool_object
     *
     * @return int
     */
    public function count_pool($p_relpool_object)
    {

        $l_sql = "SELECT COUNT(isys_cats_relpool_list.isys_cats_relpool_list__id) AS `count` FROM isys_cats_relpool_list " . "INNER JOIN isys_cats_relpool_list_2_isys_obj " . "ON " . "isys_cats_relpool_list_2_isys_obj.isys_cats_relpool_list__id = isys_cats_relpool_list.isys_cats_relpool_list__id " . "WHERE isys_cats_relpool_list__isys_obj__id = " . $this->convert_sql_id(
                $p_relpool_object
            );

        $l_tmp = $this->retrieve($l_sql)
            ->__to_array();

        return $l_tmp["count"];
    } // function

    /**
     * Attach a new relation
     *
     * @param int $p_relation_pool
     * @param int $p_relation_object
     *
     * @return boolean
     */
    public function attach_relation($p_relation_pool, $p_relation_object)
    {
        if ($p_relation_object > 0 && $p_relation_pool > 0)
        {
            $l_check = "SELECT * FROM isys_cats_relpool_list_2_isys_obj WHERE " . "(" . "isys_cats_relpool_list__id = " . $this->convert_sql_id(
                    $p_relation_pool
                ) . " AND " . "isys_obj__id = " . $this->convert_sql_id($p_relation_object) . ");";

            if ($this->retrieve($l_check)
                    ->num_rows() == 0
            )
            {
                $l_sql = "INSERT INTO isys_cats_relpool_list_2_isys_obj SET " . "isys_cats_relpool_list__id = " . $this->convert_sql_id(
                        $p_relation_pool
                    ) . ", " . "isys_cats_relpool_list_2_isys_obj__status = '" . C__RECORD_STATUS__NORMAL . "', " . "isys_obj__id = " . $this->convert_sql_id(
                        $p_relation_object
                    );

                return $this->update($l_sql) && $this->apply_update();
            }
        }

        return false;
    } // function

    /**
     * Ranks a relation object.
     *
     * @param   integer $p_relation_obj
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function rank($p_relation_obj, $p_status)
    {
        $l_sql = "UPDATE isys_cats_relpool_list_2_isys_obj SET isys_cats_relpool_list_2_isys_obj__status = " . $this->convert_sql_int(
                $p_status
            ) . " WHERE isys_obj__id = " . $this->convert_sql_id($p_relation_obj);

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Clear relation pool.
     *
     * @param   integer $p_relation_pool
     *
     * @return  boolean
     */
    public function clear($p_relation_pool)
    {
        $l_sql = "DELETE FROM isys_cats_relpool_list_2_isys_obj WHERE isys_cats_relpool_list__id = " . $this->convert_sql_id($p_relation_pool) . ";";

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Removes a relation.
     *
     * @param   integer $p_relation_pool
     * @param   integer $p_relation_object
     *
     * @return  boolean
     */
    public function remove_relation($p_relation_pool, $p_relation_object)
    {
        $l_sql = "DELETE FROM isys_cats_relpool_list_2_isys_obj WHERE isys_cats_relpool_list__id = " . $this->convert_sql_id(
                $p_relation_pool
            ) . " AND isys_obj__id = " . $this->convert_sql_id($p_relation_object) . ";";

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Returns relation pool.
     *
     * @param   integer $p_pool_object
     *
     * @return  isys_component_dao_result
     */
    public function get_relation_pool($p_pool_object)
    {
        $l_sql = "SELECT rp.*, relobj.*, isys_catg_relation_list.*, isys_relation_type.*, " . /* Relation aliases */
            "obj1.isys_obj__title as `object1_title`, obj1.isys_obj__isys_obj_type__id as `object1_type` " . "FROM isys_cats_relpool_list_2_isys_obj " . /* Get relation pool*/
            "INNER JOIN isys_cats_relpool_list rp ON isys_cats_relpool_list_2_isys_obj.isys_cats_relpool_list__id = rp.isys_cats_relpool_list__id " . /* Join relation */
            "INNER JOIN isys_obj relobj ON relobj.isys_obj__id = isys_cats_relpool_list_2_isys_obj.isys_obj__id " . "INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = relobj.isys_obj__id " . /* Join relation objects */
            "INNER JOIN isys_obj obj1 ON isys_catg_relation_list__isys_obj__id__master = obj1.isys_obj__id " . /* Join relation type */
            "INNER JOIN isys_relation_type ON isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id " . "WHERE rp.isys_cats_relpool_list__isys_obj__id = " . $this->convert_sql_id(
                $p_pool_object
            ) . " " . "AND isys_cats_relpool_list_2_isys_obj__status = '" . C__RECORD_STATUS__NORMAL . "'";

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_object_id
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_pool_siblings_as_array($p_object_id)
    {
        $l_return = [];

        $l_sql = "SELECT * FROM isys_cats_relpool_list_2_isys_obj " . "WHERE isys_cats_relpool_list_2_isys_obj.isys_obj__id = " . $this->convert_sql_id(
                $p_object_id
            ) . " " . "AND isys_cats_relpool_list_2_isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

        $l_ret = $this->retrieve($l_sql);

        if (count($l_ret))
        {
            while ($l_row = $l_ret->get_row())
            {
                $l_sql = "SELECT * FROM isys_cats_relpool_list_2_isys_obj " . "WHERE isys_cats_relpool_list_2_isys_obj.isys_cats_relpool_list__id = " . $this->convert_sql_id(
                        $l_row["isys_cats_relpool_list__id"]
                    ) . " " . "AND isys_cats_relpool_list_2_isys_obj.isys_obj__id != " . $this->convert_sql_id(
                        $p_object_id
                    ) . " " . "AND isys_cats_relpool_list_2_isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

                $l_siblibgs = $this->retrieve($l_sql);

                if (count($l_siblibgs))
                {
                    while ($l_sibrow = $l_siblibgs->get_row())
                    {
                        $l_return[$l_sibrow["isys_obj__id"]] = $l_sibrow["isys_obj__id"];
                    } // while
                } // if
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Handles the ajax callback of the popup browser (Click on add object(s)).
     *
     * @param  integer $p_relpool_id
     * @param  array   $p_post
     *
     * @return boolean
     */
    public function handle_ajax_request($p_relpool_id, $p_post)
    {
        if ($p_relpool_id > 0)
        {
            if (isset($p_post["remove"]) && is_numeric($p_post["remove"]))
            {
                return $this->remove_relation($p_relpool_id, $p_post["remove"]);
            } // if

            if ($p_post["objectID__HIDDEN"])
            {
                // Due to the new relation-browser we get a JSON string, but we don't want to break the old logic!
                if (isys_format_json::is_json_array($p_post["objectID__HIDDEN"]))
                {
                    $l_objects = isys_format_json::decode($p_post["objectID__HIDDEN"], true);
                }
                else
                {
                    $l_objects = explode(",", $p_post["objectID__HIDDEN"]);
                } // if

                foreach ($l_objects as $l_object)
                {
                    if (is_numeric($l_object))
                    {
                        $this->attach_relation($p_relpool_id, $l_object);
                    } // if
                } // foreach

                // Now, display the relation pool.
                echo isys_cmdb_ui_category_s_parallel_relation::get_relation_pool($this);
            } // if
        } // if
    } // function

    /**
     * Save element method.
     *
     * @param  integer $p_cat_level
     * @param  integer $p_status
     * @param  boolean $p_create
     *
     * @return boolean
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_general_data();
        $p_status  = $l_catdata["isys_cats_relpool_list__status"];
        $l_list_id = $l_catdata["isys_cats_relpool_list__id"];

        if (empty($l_list_id))
        {
            $l_list_id = $this->create_connector("isys_cats_relpool_list", $_GET[C__CMDB__GET__OBJECT]);
        } // if

        // Save is triggerd via ajax request. This occurs on adding or removing objects of the parallel relation list.
        if ($_POST["relpool"] == 1)
        {
            $this->handle_ajax_request($l_list_id, $_POST);
            die();
        } // if

        // Save parallel relation.
        $this->save(
            $l_list_id,
            $_POST["C__CMDB__CATS__RELPL__TITLE"],
            $_POST["C__CMDB__CATS__RELPL__THRESHOLD"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            C__RECORD_STATUS__NORMAL
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return true;
    } // function

    /**
     * Executes the query to save the category entry by its ID $p_id.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_threshold
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_title, $p_threshold, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = 'UPDATE isys_cats_relpool_list SET ' . 'isys_cats_relpool_list__title = ' . $this->convert_sql_text(
                $p_title
            ) . ', ' . 'isys_cats_relpool_list__threshold = ' . $this->convert_sql_id(
                $p_threshold
            ) . ', ' . 'isys_cats_relpool_list__description	 = ' . $this->convert_sql_text(
                $p_description
            ) . ', ' . 'isys_cats_relpool_list__status = ' . $this->convert_sql_id($p_status) . ' WHERE isys_cats_relpool_list__id = ' . $this->convert_sql_id($p_id) . ';';

        if ($this->update($l_strSql))
        {
            // Get global category dao to synchronize object title.
            $l_dao       = new isys_cmdb_dao_category_g_global($this->m_db);
            $l_object_id = $this->get_object_id_by_category_id($p_id, "isys_cats_relpool_list");

            // Create catg global entry or just update object title, because this view ist generally the only view of the overview page.
            if ($l_dao->get_object_status_by_id($l_object_id) == C__RECORD_STATUS__BIRTH)
            {
                $l_dao->set_object_status($l_object_id, C__RECORD_STATUS__NORMAL);
            } // if

            $l_dao->save_title($l_object_id, $p_title);

            return $this->apply_update();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Executes the query to create the category entry.
     *
     * @param   array   $p_object_id
     * @param   string  $p_title
     * @param   string  $p_threshold
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @throws  isys_exception_dao
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_title, $p_threshold, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = 'INSERT IGNORE INTO isys_cats_relpool_list SET ' . 'isys_cats_relpool_list__title = ' . $this->convert_sql_text(
                $p_title
            ) . ', ' . 'isys_cats_relpool_list__threshold = ' . $this->convert_sql_id(
                $p_threshold
            ) . ', ' . 'isys_cats_relpool_list__description	 = ' . $this->convert_sql_text(
                $p_description
            ) . ', ' . 'isys_cats_relpool_list__status = ' . $this->convert_sql_id($p_status) . ', ' . 'isys_cats_relpool_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_object_id
            ) . ';';

        if ($this->update($l_strSql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function
} // class