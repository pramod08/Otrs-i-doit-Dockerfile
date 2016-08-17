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
 * Cable connection DAO (UI, Port and FC-Port connections).
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_cable_connection extends isys_cmdb_dao
{
    /**
     * @var  array
     */
    protected static $m_last_counter = null;

    /**
     * Adds a new cable object and returns its ID.
     *
     * @param   string  $p_title
     * @param   integer $p_id
     *
     * @return  integer
     */
    public static function add_cable($p_title = null, $p_id = null)
    {
        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

        if ($p_title === null || $p_title === '')
        {
            $l_counter = (self::$m_last_counter === null) ? ($l_dao->retrieve('SELECT MAX(isys_cable_connection__id) AS cnt FROM isys_cable_connection')
                    ->get_row_value('cnt') + 1) : self::$m_last_counter++;

            $p_title = isys_settings::get('cmdb.object.title.cable-prefix') . $l_counter;
        } // if

        $l_cable_id = $l_dao->insert_new_obj(C__OBJTYPE__CABLE, false, $p_title, null, C__RECORD_STATUS__NORMAL);

        $l_sql = 'INSERT INTO isys_catg_cable_list SET
            isys_catg_cable_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_cable_id) . ',
            isys_catg_cable_list__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_dao->update($l_sql) && $l_dao->apply_update();

        return $l_cable_id;
    } // function

    /**
     * Finds a cable which is not assigned to any connector (for recycling).
     *
     * @param   string $p_title
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function recycle_cable($p_title = null)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        if ($p_title === null || $p_title === '')
        {
            $l_counter = (self::$m_last_counter === null) ? ($l_dao->retrieve('SELECT MAX(isys_cable_connection__id) AS cnt FROM isys_cable_connection')
                    ->get_row_value('cnt') + 1) : self::$m_last_counter++;

            $p_title = isys_settings::get('cmdb.object.title.cable-prefix') . $l_counter;
        } // if

        $l_sql = 'SELECT isys_obj__id, isys_obj__title
			FROM isys_obj
			LEFT JOIN isys_cable_connection AS cc ON cc.isys_cable_connection__isys_obj__id = isys_obj__id
			WHERE NOT EXISTS (
				SELECT isys_catg_connector_list__isys_cable_connection__id
				FROM isys_catg_connector_list
				WHERE isys_catg_connector_list__isys_cable_connection__id = cc.isys_cable_connection__id
			) AND isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id(C__OBJTYPE__CABLE) . ' LIMIT 1';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res))
        {
            $l_row      = $l_res->get_row();
            $l_cable_id = $l_row['isys_obj__id'];
            if ($p_title === null)
            {
                $p_title = $l_row['isys_obj__title'];
            } // if

            $l_dao->update(
                'UPDATE isys_obj SET isys_obj__title = ' . $l_dao->convert_sql_text($p_title) . ' WHERE isys_obj__id = ' . $l_dao->convert_sql_id($l_cable_id) . ';'
            );
            $l_dao->apply_update();
        }
        else
        {
            // Fallback if no cable object is free
            $l_cable_id = isys_cmdb_dao_cable_connection::add_cable($p_title);
        } // if

        return $l_cable_id;
    } // function

    /**
     * Retrieves a cable connection by its ID.
     *
     * @param   integer $p_connection_id
     *
     * @return  isys_component_dao_result
     */
    public function get_cable_connection($p_connection_id)
    {
        return $this->retrieve("SELECT * FROM isys_cable_connection WHERE isys_cable_connection__id = " . $this->convert_sql_id($p_connection_id) . ";");
    }

    /**
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_connection_types()
    {
        return $this->retrieve('SELECT * FROM isys_connection_type;');
    } // function

    /**
     * Retrieves the object id by connection id.
     *
     * @param   integer $p_connection_id
     *
     * @return  integer
     */
    public function get_cable_object_id_by_connection_id($p_connection_id)
    {
        return $this->get_cable_connection($p_connection_id)
            ->get_row_value('isys_cable_connection__isys_obj__id');
    } // function

    /**
     * Adds a new cable connection.
     *
     * @param   integer $p_cableID
     *
     * @return  mixed
     */
    public function add_cable_connection($p_cableID)
    {
        if (($l_cable_connection_id = $this->get_cable_connection_id_by_cable_id($p_cableID)))
        {
            $this->delete_cable_connection($l_cable_connection_id);
        } // if

        $l_sql = "INSERT INTO isys_cable_connection (isys_cable_connection__isys_obj__id) VALUES(" . $this->convert_sql_id($p_cableID) . ")";

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Deletes a cable connection (and all connected endpoints).
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete_cable_connection($p_id)
    {
        if ($p_id !== null && $p_id > 0)
        {
            $l_id = $this->convert_sql_id($p_id);

            $l_sql = "SELECT isys_catg_relation_list__isys_obj__id
				FROM isys_catg_relation_list
				INNER JOIN isys_catg_connector_list
				ON isys_catg_connector_list__isys_catg_relation_list__id = isys_catg_relation_list__id
				WHERE isys_catg_connector_list__isys_cable_connection__id = " . $l_id . ";";

            $l_data = $this->retrieve($l_sql)
                ->get_row_value('isys_catg_relation_list__isys_obj__id');

            if ($l_data !== null)
            {
                // Detach relation
                $l_sql = "UPDATE isys_catg_connector_list SET isys_catg_connector_list__isys_catg_relation_list__id = NULL " . "WHERE isys_catg_connector_list__isys_cable_connection__id = " . $l_id;

                if ($this->update($l_sql))
                {
                    $this->delete_object($l_data);
                } // if
            } // if

            // Delete cable connection
            $l_update = "DELETE FROM isys_cable_connection WHERE isys_cable_connection__id = " . $l_id;

            if ($this->update($l_update))
            {
                return $this->apply_update();
            } // if
        } // if
        return null;
    } // function

    /**
     *
     * @param   integer $p_cable_object_id
     *
     * @return  mixed
     */
    public function get_cable_connection_id_by_cable_id($p_cable_object_id)
    {
        $l_cable_connection = $this->get_cable_connection_by_cable_id($p_cable_object_id);

        if (count($l_cable_connection))
        {
            return $l_cable_connection->get_row_value('isys_cable_connection__id');
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     *
     * @param   integer $p_cable_object_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_cable_connection_by_cable_id($p_cable_object_id)
    {
        $l_sql = "SELECT * FROM isys_cable_connection
            WHERE isys_cable_connection__isys_obj__id = " . $this->convert_sql_id($p_cable_object_id) . "
            LIMIT 1;";

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_conID
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_cable_connection_id_by_connector_id($p_conID)
    {
        $l_query = "SELECT isys_cable_connection__id FROM isys_cable_connection
            INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($p_conID) . "
            LIMIT 1;";

        return $this->retrieve($l_query)
            ->get_row_value('isys_cable_connection__id');
    } // function

    /**
     *
     * @param   integer $p_cableConID
     * @param   integer $p_connectorID
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_object($p_cableConID, $p_connectorID)
    {
        $l_query = "SELECT isys_obj__id FROM isys_obj
            INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__isys_obj__id = isys_obj__id
            WHERE isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($p_cableConID) . "
            AND isys_catg_connector_list__id != " . $this->convert_sql_id($p_connectorID) . "
            LIMIT 1;";

        $l_obj_id = $this->retrieve($l_query)
            ->get_row_value('isys_obj__id');

        if ($l_obj_id !== null)
        {
            return $l_obj_id;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     *
     * @param   integer $p_uiID
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_ui($p_uiID)
    {
        $l_query = "SELECT there.isys_catg_ui_list__id FROM isys_catg_ui_list there
            INNER JOIN isys_catg_connector_list con_there ON con_there.isys_catg_connector_list__id = there.isys_catg_ui_list__isys_catg_connector_list__id
            INNER JOIN isys_catg_connector_list con_here ON con_here.isys_catg_connector_list__isys_cable_connection__id = con_there.isys_catg_connector_list__isys_cable_connection__id
                AND con_here.isys_catg_connector_list__id != con_there.isys_catg_connector_list__id
            INNER JOIN isys_catg_ui_list here ON here.isys_catg_ui_list__isys_catg_connector_list__id = con_here.isys_catg_connector_list__id
            WHERE here.isys_catg_ui_list__id = " . $this->convert_sql_id($p_uiID) . "
            LIMIT 1;";

        return $this->retrieve($l_query)
            ->get_row_value("isys_catg_ui_list__id");
    } // function

    /**
     *
     * @param   integer $p_connectorID
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_cable($p_connectorID)
    {
        $l_query = "SELECT isys_cable_connection__isys_obj__id FROM isys_cable_connection
            INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($p_connectorID) . "
            LIMIT 1;";

        return $this->retrieve($l_query)
            ->get_row_value("isys_cable_connection__isys_obj__id");
    } // function

    /**
     *
     * @param   integer $p_portID
     *
     * @return  mixed
     */
    public function get_assigned_port_id($p_portID)
    {
        return $this->get_assigned_port_info($p_portID)["isys_catg_port_list__id"];
    } // function

    /**
     *
     * @param   integer $p_portID
     *
     * @return  mixed
     */
    public function get_assigned_port_name($p_portID)
    {
        return $this->get_assigned_port_info($p_portID)["isys_catg_port_list__title"];
    } // function

    /**
     *
     * @param   integer $p_portID
     *
     * @return  mixed
     */
    public function get_assigned_fc_port_id($p_portID)
    {
        return $this->get_assigned_fc_port_info($p_portID)["isys_catg_fc_port_list__id"];
    } // function

    /**
     *
     * @param   integer $p_portID
     *
     * @return  mixed
     */
    public function get_assigned_fc_port_name($p_portID)
    {
        return $this->get_assigned_fc_port_info($p_portID)['isys_catg_fc_port_list__title'];
    } // function

    /**
     *
     * @param   integer $p_portID
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_port_info($p_portID)
    {
        $l_query = "SELECT there.isys_catg_port_list__id, there.isys_catg_port_list__title FROM isys_catg_port_list there
            INNER JOIN isys_catg_connector_list con_there ON con_there.isys_catg_connector_list__id = there.isys_catg_port_list__isys_catg_connector_list__id
            INNER JOIN isys_catg_connector_list con_here ON con_here.isys_catg_connector_list__isys_cable_connection__id = con_there.isys_catg_connector_list__isys_cable_connection__id
                AND con_here.isys_catg_connector_list__id != con_there.isys_catg_connector_list__id
            INNER JOIN isys_catg_port_list here ON here.isys_catg_port_list__isys_catg_connector_list__id = con_here.isys_catg_connector_list__id
            WHERE here.isys_catg_port_list__id = " . $this->convert_sql_id($p_portID);

        return $this->retrieve($l_query)
            ->get_row();
    } // function

    /**
     *
     * @param   integer $p_connector_id
     * @param   integer $p_otherType
     * @param   integer $p_myType
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_connector($p_connector_id, $p_otherType = null, $p_myType = null)
    {
        $l_query = "SELECT isys_obj.*,there.*, isys_cable_connection.*, isys_connection_type__title, isys_obj_type__title AS object_type
            FROM isys_catg_connector_list here
            INNER JOIN isys_catg_connector_list there ON there.isys_catg_connector_list__isys_cable_connection__id = here.isys_catg_connector_list__isys_cable_connection__id
                AND there.isys_catg_connector_list__id != here.isys_catg_connector_list__id
            LEFT JOIN isys_connection_type ON here.isys_catg_connector_list__isys_connection_type__id = isys_connection_type__id
            LEFT JOIN isys_cable_connection ON there.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            LEFT JOIN isys_obj ON isys_obj__id = there.isys_catg_connector_list__isys_obj__id
            LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE here.isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector_id);

        if ($p_otherType !== null)
        {
            $l_query .= " AND there.isys_catg_connector_list__type = " . $this->convert_sql_id($p_otherType);
        } // if

        if ($p_myType !== null)
        {
            $l_query .= " AND here.isys_catg_connector_list__type = " . $this->convert_sql_id($p_myType);
        } // if

        return $this->retrieve($l_query . ";");
    } // function

    /**
     *
     * @param   integer $p_cableConID
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_connection_info($p_cableConID)
    {
        $l_query = "SELECT * FROM isys_catg_connector_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
            WHERE isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($p_cableConID);

        return $this->retrieve($l_query);
    } // function

    /**
     *
     * @param   integer $p_conID
     * @param   integer $p_cableConID
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_connector_name($p_conID, $p_cableConID)
    {
        $l_query = "SELECT isys_catg_connector_list__title FROM isys_catg_connector_list
            WHERE isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($p_cableConID) . "
            AND isys_catg_connector_list__id != " . $this->convert_sql_id($p_conID) . ";";

        return $this->retrieve($l_query)
            ->get_row_value('isys_catg_connector_list__title');
    } // function

    /**
     *
     * @param   integer $p_conID
     *
     * @return  mixed
     */
    public function get_assigned_connector_id($p_conID)
    {
        $l_res = $this->get_assigned_connector($p_conID);

        if (count($l_res))
        {
            return $l_res->get_row_value('isys_catg_connector_list__id');
        } // if

        return null;
    } // function

    /**
     * Get assigned fc ports over fc_port_id.
     *
     * @param   integer $p_portID
     *
     * @return  array  isys_catg_fc_port_list__id and isys_catg_fc_port_list__title
     */
    public function get_assigned_fc_port_info($p_portID)
    {
        $l_query = "SELECT there.isys_catg_fc_port_list__id, there.isys_catg_fc_port_list__title FROM isys_catg_fc_port_list there
            INNER JOIN isys_catg_connector_list con_there ON con_there.isys_catg_connector_list__id = there.isys_catg_fc_port_list__isys_catg_connector_list__id
            INNER JOIN isys_catg_connector_list con_here ON con_here.isys_catg_connector_list__isys_cable_connection__id = con_there.isys_catg_connector_list__isys_cable_connection__id AND con_here.isys_catg_connector_list__id != con_there.isys_catg_connector_list__id
            INNER JOIN isys_catg_fc_port_list here ON here.isys_catg_fc_port_list__isys_catg_connector_list__id = con_here.isys_catg_connector_list__id
            WHERE here.isys_catg_fc_port_list__id = " . $this->convert_sql_id($p_portID);

        return $this->retrieve($l_query)
            ->get_row();
    } // function

    /**
     * Saves a connector with the given endpoint.
     *
     * @param   integer $p_connector1ID
     * @param   integer $p_connector2ID
     * @param   integer $p_connectionID
     * @param   integer $p_master_connector_id
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     */
    public function save_connection($p_connector1ID, $p_connector2ID, $p_connectionID, $p_master_connector_id = null)
    {
        $l_dao_conncetor = new isys_cmdb_dao_category_g_connector($this->get_database_component());
        $l_dao_relation  = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        // Get connector data.
        $l_connector1 = $l_dao_conncetor->get_data($p_connector1ID)
            ->__to_array();
        $l_connector2 = $l_dao_conncetor->get_data($p_connector2ID)
            ->__to_array();

        $l_rel_id = null;

        // Create implicit relation.
        try
        {
            if ($l_connector1["isys_catg_connector_list__isys_obj__id"] > 0 && $l_connector2["isys_catg_connector_list__isys_obj__id"] > 0)
            {
                if (is_numeric($l_connector1["isys_catg_connector_list__assigned_category"]))
                {
                    switch ($l_connector1["isys_catg_connector_list__assigned_category"])
                    {
                        case C__CATG__CONNECTOR:
                            $l_connector1["isys_catg_connector_list__assigned_category"] = 'C__CATG__CONNECTOR';
                            break;
                        case C__CMDB__SUBCAT__NETWORK_PORT:
                            $l_connector1["isys_catg_connector_list__assigned_category"] = 'C__CMDB__SUBCAT__NETWORK_PORT';
                            break;
                        case C__CATG__POWER_CONSUMER:
                            $l_connector1["isys_catg_connector_list__assigned_category"] = 'C__CATG__POWER_CONSUMER';
                            break;
                        case C__CATG__UNIVERSAL_INTERFACE:
                            $l_connector1["isys_catg_connector_list__assigned_category"] = 'C__CATG__UNIVERSAL_INTERFACE';
                            break;
                        case C__CATG__CONTROLLER_FC_PORT:
                            $l_connector1["isys_catg_connector_list__assigned_category"] = 'C__CATG__CONTROLLER_FC_PORT';
                            break;
                    } // switch
                } // if

                if (is_numeric($l_connector2["isys_catg_connector_list__assigned_category"]))
                {
                    switch ($l_connector2["isys_catg_connector_list__assigned_category"])
                    {
                        case C__CATG__CONNECTOR:
                            $l_connector2["isys_catg_connector_list__assigned_category"] = 'C__CATG__CONNECTOR';
                            break;
                        case C__CMDB__SUBCAT__NETWORK_PORT:
                            $l_connector2["isys_catg_connector_list__assigned_category"] = 'C__CMDB__SUBCAT__NETWORK_PORT';
                            break;
                        case C__CATG__POWER_CONSUMER:
                            $l_connector2["isys_catg_connector_list__assigned_category"] = 'C__CATG__POWER_CONSUMER';
                            break;
                        case C__CATG__UNIVERSAL_INTERFACE:
                            $l_connector2["isys_catg_connector_list__assigned_category"] = 'C__CATG__UNIVERSAL_INTERFACE';
                            break;
                        case C__CATG__CONTROLLER_FC_PORT:
                            $l_connector2["isys_catg_connector_list__assigned_category"] = 'C__CATG__CONTROLLER_FC_PORT';
                            break;
                    } // switch
                } // if

                $l_relation_type = $l_dao_relation->get_relation_type_by_category($l_connector1["isys_catg_connector_list__assigned_category"]);
                if (!$l_relation_type)
                {
                    $l_relation_type = $l_dao_relation->get_relation_type_by_category($l_connector2["isys_catg_connector_list__assigned_category"]);
                    if ($l_relation_type)
                    {
                        $l_connector_puffer = $l_connector1;
                        $l_connector1       = $l_connector2;
                        $l_connector2       = $l_connector_puffer;
                    } // if
                }
                else if ($l_relation_type == C__RELATION_TYPE__CONNECTORS)
                {
                    $l_relation_type = $l_dao_relation->get_relation_type_by_category($l_connector2["isys_catg_connector_list__assigned_category"]);

                    if ($l_relation_type != C__RELATION_TYPE__CONNECTORS)
                    {
                        $l_puffer     = $l_connector1;
                        $l_connector1 = $l_connector2;
                        $l_connector2 = $l_puffer;
                    } // if
                } // if

                if (!empty($p_master_connector_id))
                {
                    if ($p_master_connector_id == $p_connector1ID)
                    {
                        // switch places
                        $l_puffer                                               = $l_connector2["isys_catg_connector_list__isys_obj__id"];
                        $l_connector2["isys_catg_connector_list__isys_obj__id"] = $l_connector1["isys_catg_connector_list__isys_obj__id"];
                        $l_connector1["isys_catg_connector_list__isys_obj__id"] = $l_puffer;
                    } // if
                } // if

                if (!$l_relation_type)
                {
                    $l_relation_type = C__RELATION_TYPE__CONNECTORS;
                } // if

                if ($l_connector1["isys_catg_connector_list__isys_catg_relation_list__id"])
                {
                    $l_call = "save_relation";
                }
                else
                {
                    $l_call = "create_relation";
                } // if

                $l_rel_id = $l_dao_relation->$l_call(
                    "isys_catg_connector_list",
                    $l_connector1["isys_catg_connector_list__id"],
                    $l_connector2["isys_catg_connector_list__isys_obj__id"],
                    $l_connector1["isys_catg_connector_list__isys_obj__id"],
                    $l_relation_type
                );

                if ($l_call == 'save_relation')
                {
                    $l_rel_id = $l_connector1["isys_catg_connector_list__isys_catg_relation_list__id"];
                } // if
            } // if
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        } // try

        $l_update = "UPDATE isys_catg_connector_list SET
            isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($p_connectionID) . ",
            isys_catg_connector_list__isys_catg_relation_list__id = " . $this->convert_sql_id($l_rel_id) . "
            WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector1ID) . "
            OR isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector2ID);

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function delete_connector($p_id)
    {
        return ($this->update("DELETE FROM isys_catg_connector_list WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($p_id) . ";") && $this->apply_update());
    } // function

    /**
     * Constructor
     *
     * @param  isys_component_database $p_database
     */
    public function __construct(isys_component_database $p_database)
    {
        parent::__construct($p_database);
    } // function
} // class