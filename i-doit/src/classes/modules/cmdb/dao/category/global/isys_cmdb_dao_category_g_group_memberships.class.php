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
 * DAO: global category for group memberships.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_group_memberships extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'group_memberships';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_cats_group_list__isys_obj__id';
    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';
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
     * Field for the object id
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';
    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  Breaks with developer guidelines!
     */
    protected $m_table = 'isys_cats_group_list';

    /**
     * Save global category backup element
     *
     * @param   integer &$p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @throws  isys_exception_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        try
        {

            $l_catdata         = $this->get_general_data();
            $p_intOldRecStatus = $l_catdata["isys_cats_group_list__status"];

            if ($p_create || !is_array($l_catdata))
            {
                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATS__GROUP__OBJECT__HIDDEN'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );

                if ($l_id != false)
                {
                    $this->m_strLogbookSQL = $this->get_last_query();
                } // if

                $p_cat_level = null;

                return $l_id;
            }
            else
            {
                if ($l_catdata['isys_cats_group_list__id'] != "")
                {
                    $l_bRet = $this->save(
                        $l_catdata['isys_cats_group_list__id'],
                        C__RECORD_STATUS__NORMAL,
                        $_POST['C__CATS__GROUP__OBJECT__HIDDEN'],
                        $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                    );

                    $this->m_strLogbookSQL = $this->get_last_query();
                } // if

                return $l_bRet == true ? null : $l_intErrorCode;
            } // if
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        } // try
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param int    $p_connectionID
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID = null, $p_description = null)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_strSql = "UPDATE isys_cats_group_list SET " . "isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id(
                $p_connectedObjID
            ) . ", " . "isys_cats_group_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_cats_group_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . " " . "WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql))
        {
            if ($this->apply_update())
            {
                $l_data = $this->get_data($p_cat_level)
                    ->__to_array();
                $l_dao_relation->handle_relation(
                    $p_cat_level,
                    "isys_cats_group_list",
                    C__RELATION_TYPE__GROUP_MEMBERSHIPS,
                    $l_data["isys_cats_group_list__isys_catg_relation_list__id"],
                    $l_data["isys_connection__isys_obj__id"],
                    $p_connectedObjID
                );

                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catg_backup__id $p_fk_id
     *
     * @param int    $p_objID
     * @param int    $p_newRecStatus
     * @param String $p_title
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_connectedObjID = null, $p_description = null)
    {
        $l_connection   = new isys_cmdb_dao_connection($this->get_database_component());
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_strSql = "INSERT INTO isys_cats_group_list SET " . "isys_cats_group_list__isys_connection__id = " . $this->convert_sql_id(
                $l_connection->add_connection($p_objID)
            ) . ", " . "isys_cats_group_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_cats_group_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . ", " . "isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID);

        if ($this->update($l_strSql) && $this->apply_update($p_connectedObjID))
        {

            $l_last_id = $this->get_last_insert_id();
            $l_dao_relation->handle_relation($l_last_id, "isys_cats_group_list", C__RELATION_TYPE__GROUP_MEMBERSHIPS, null, $p_objID, $p_connectedObjID);

            return $l_last_id;
        }
        else
        {
            return false;
        }
    } // function

    /**
     * Set Status for category entry
     *
     * @param int $p_cat_id
     * @param int $p_status
     *
     * @return bool
     */
    public function set_status($p_cat_id, $p_status)
    {
        $l_sql = "UPDATE isys_cats_group_list SET isys_cats_group_list__status = " . $this->convert_sql_id(
                $p_status
            ) . " " . "WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_id);
        if ($this->update($l_sql) && $this->apply_update())
        {
            return true;
        }
        else
        {
            return false;
        }
    } // function

    /**
     * Deletes connection between group and object
     *
     * @param int $p_cat_level
     * @param int $p_objtype_id
     *
     * @return bool
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @version Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function delete($p_cat_level, $p_objtype_id = C__OBJECT_TYPE__GROUP)
    {

        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_catdata      = $this->get_data($p_cat_level)
            ->__to_array();

        if ($l_catdata["isys_cats_group_list__isys_catg_relation_list__id"] > 0)
        {

            $l_dao_relation->delete_relation($l_catdata["isys_cats_group_list__isys_catg_relation_list__id"]);
        }
        else
        {

            $l_sql = "DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_level);
            $this->update($l_sql);
        }

        if ($this->apply_update())
        {
            return true;
        }
        else
        {
            throw new isys_exception_cmdb("Could not delete id '{$p_cat_level}' in table isys_catg_application_list.");
        }
    } // function

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id))
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_cats_group_list__id) AS count FROM isys_cats_group_list " . "LEFT JOIN isys_connection ON isys_cats_group_list__isys_connection__id = isys_connection__id " . "LEFT JOIN isys_obj ON  isys_obj__id = isys_cats_group_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($l_obj_id))
        {
            $l_sql .= " AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_cats_group_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    } // function

    /**
     * Return Category Data
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
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_group_list " . "INNER JOIN isys_obj ON  isys_obj__id = isys_cats_group_list__isys_obj__id " . "LEFT JOIN isys_connection ON isys_cats_group_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_cats_group_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_cats_group_list__status = " . $this->convert_sql_id($p_status) . ") ";
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
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
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            } // if
        } // if

        return $l_sql;
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
            'connected_object' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GROUP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Group'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_cats_group_list__isys_obj__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__GROUP_MEMBERSHIPS,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_group_memberships',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_g_group_memberships']
                        ),
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__GROUP__OBJECT',
                        C__PROPERTY__UI__PARAMS => [
                            'typeFilter' => 'C__OBJECT_TYPE__GROUP'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'description'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_group_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__GROUP_MEMBERSHIPS
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
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
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        }

        return false;
    } // function
} // class
?>