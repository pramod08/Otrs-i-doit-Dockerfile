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
 * DAO: global category for shared storage in clusters.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_cluster_shared_storage extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_shared_storage';

    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $p_intOldRecStatus = $l_catdata["isys_catg_cluster_list__status"];
        $l_last_san_obj_id = [];

        $l_dao               = new isys_cmdb_dao_category_g_ldevclient($this->m_db);
        $l_dao_sanpool       = new isys_cmdb_dao_category_g_sanpool($this->m_db);
        $l_mod_event_manager = isys_event_manager::getInstance();

        if (is_array($_POST["ldevclient_hidden"]))
        {
            foreach ($_POST["ldevclient_hidden"] as $l_sanpool_id => $l_objects)
            {
                if (is_array($l_objects))
                {
                    $l_sanpool_object = $l_dao_sanpool->get_ldevserver_by_obj_id_or_ldev_id(null, $l_sanpool_id)
                        ->__to_array();

                    foreach ($l_objects as $l_obj_id => $l_state)
                    {
                        $l_id = false;
                        if ($l_state == "1")
                        {
                            if ($l_dao->get_data(
                                    null,
                                    $l_obj_id,
                                    "AND isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($l_sanpool_id),
                                    null,
                                    C__RECORD_STATUS__NORMAL
                                )
                                    ->num_rows() <= 0
                            )
                            {
                                $l_id = $l_dao->create($l_obj_id, C__RECORD_STATUS__NORMAL, "SAN" . $l_sanpool_id, $l_sanpool_id, null, null, null, "");
                            } // if
                        }
                        else if ($l_state == "0")
                        {

                            $l_res = $l_dao->get_data(
                                null,
                                $l_obj_id,
                                "AND isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($l_sanpool_id),
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                            if ($l_res->num_rows() > 0)
                            {
                                $l_id  = $l_dao->delete_ldevclient(null, $l_sanpool_id, $l_obj_id);
                                $l_res = $l_dao->get_data(
                                    null,
                                    $l_obj_id,
                                    "AND isys_catg_ldevclient_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($l_sanpool_id),
                                    null,
                                    C__RECORD_STATUS__NORMAL
                                );
                                if ($l_res->num_rows() == 0)
                                {
                                    $l_id = true;
                                }
                            }
                            else
                            {
                                $l_id = false;
                            }
                        }

                        /**
                         * Trigger Logbook message
                         */
                        if ($l_id || $l_id > 0)
                        {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_CHANGED";

                            $l_mod_event_manager->triggerCMDBEvent(
                                $l_strConstEvent,
                                $l_dao->get_last_query(),
                                $l_obj_id,
                                $this->get_objTypeID($l_obj_id),
                                _L("LC__CMDB__CATG__LDEV_CLIENT")
                            );

                            if (!in_array($l_sanpool_object["isys_catg_sanpool_list__isys_obj__id"], $l_last_san_obj_id))
                            {
                                $l_last_san_obj_id[] = $l_sanpool_object["isys_catg_sanpool_list__isys_obj__id"];
                                $l_mod_event_manager->triggerCMDBEvent(
                                    $l_strConstEvent,
                                    $l_dao->get_last_query(),
                                    $l_sanpool_object["isys_catg_sanpool_list__isys_obj__id"],
                                    $this->get_objTypeID($l_sanpool_object["isys_catg_sanpool_list__isys_obj__id"]),
                                    _L("LC__CMDB__CATG__LDEV_SERVER")
                                );
                            }
                        }
                    }
                }
            }
        }

        return null;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int     $p_cat_level
     * @param boolean $p_virtualHost
     * @param int     $p_connectedObjID
     * @param String  $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_virtualHost, $p_connectedObjID, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_old_data = $this->get_data($p_cat_level)
            ->__to_array();

        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection->update_connection($l_old_data["isys_catg_cluster_list__isys_connection__id"], $p_connectedObjID);

        $l_strSql = "UPDATE isys_catg_cluster_list SET " . "isys_catg_cluster_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_cluster_list__virtual_host = " . $this->convert_sql_id(
                $p_virtualHost
            ) . ", " . "isys_catg_cluster_list__status = " . $p_status . " " . "WHERE isys_catg_cluster_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql))
        {
            return $this->apply_update();
        }
        else
            return false;
    } // function

    /**
     * Get count method.
     *
     * @param   integer $p_objID
     *
     * @return  integer
     */
    public function get_count($p_objID = null)
    {
        return 1;
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_cluster_members_list " . "INNER JOIN isys_connection " . "ON " . "isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " . "INNER JOIN isys_obj " . "ON " . "isys_obj__id = isys_connection__isys_obj__id " . "LEFT JOIN isys_catg_ldevclient_list " . "ON " . "isys_catg_ldevclient_list__isys_obj__id = isys_obj__id " . "LEFT OUTER JOIN isys_catg_sanpool_list " . "ON " . "isys_catg_ldevclient_list__isys_catg_sanpool_list__id = isys_catg_sanpool_list__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_cluster_members_list__id = " . (int) $p_catg_list_id . ") ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_obj__status = " . (int) $p_status . ") AND (isys_catg_cluster_members_list__status = " . (int) $p_status . ") ";
        } // if

        $l_sql .= "ORDER BY isys_obj__title, isys_catg_ldevclient_list__title";

        return $this->retrieve($l_sql);
    }

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
                $l_sql = ' AND (isys_catg_cluster_members_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
        return [];
    } // function
} // class