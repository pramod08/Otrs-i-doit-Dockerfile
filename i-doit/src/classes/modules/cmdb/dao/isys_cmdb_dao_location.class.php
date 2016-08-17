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
 * DAO: Location-object access class.
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

// Fake autoloading of MPTT classes :-)
(class_exists("isys_component_dao_mptt") && interface_exists("isys_mptt_callback")) or die("Failed loading MPTT framework in " . __FILE__);

/**
 * Class isys_cmdb_dao_location
 */
class isys_cmdb_dao_location extends isys_cmdb_dao implements isys_mptt_callback
{

    /**
     * MPTT DAO object.
     *
     * @var  isys_component_dao_mptt
     */
    private $m_mptt;

    /**
     * Temporal array for MPTT results.
     *
     * @var  array
     */
    private $m_tree = [];

    /**
     * Retrieves a DAO result with all location types.
     *
     * @return  isys_component_dao_result
     */
    public function get_location_types()
    {
        return $this->retrieve('SELECT * FROM isys_obj_type WHERE isys_obj_type__container <> 0;');
    } // function

    /**
     * Updates the position of a rack element (child).
     *
     * @param   integer $p_object_id
     * @param   integer $p_option
     * @param   integer $p_insertion
     * @param   integer $p_pos
     *
     * @return  boolean
     */
    public function update_position($p_object_id, $p_option = null, $p_insertion = null, $p_pos = null)
    {
        $l_data = $this->m_mptt->get_by_node_id($p_object_id);

        if (is_object($l_data))
        {
            $l_array = $l_data->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY);

            $l_sql = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__pos = " . $this->convert_sql_int($p_pos) . ",
				isys_catg_location_list__insertion = " . $this->convert_sql_int($p_insertion) . ",
				isys_catg_location_list__option = " . $this->convert_sql_id($p_option) . "
				WHERE isys_catg_location_list__id = " . $this->convert_sql_id($l_array["isys_catg_location_list__id"]) . ";";

            if ($this->update($l_sql))
            {
                return $this->apply_update();
            } // if
        } // if

        return false;
    } // function

    /**
     * Retrieve a location by a given object.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_component_dao_result
     */
    public function get_location_by_object_id($p_object_id)
    {
        return $this->get_location(null, null, C__RECORD_STATUS__NORMAL, $p_object_id);
    } // function

    /**
     * Retrieve location objects.
     *
     * @param  integer $p_parent_id
     * @param  boolean $p_front
     * @param  integer $p_record_status
     * @param  integer $p_object_id
     *
     * @return isys_component_dao_result
     */
    public function get_location($p_parent_id = null, $p_front = true, $p_record_status = C__RECORD_STATUS__NORMAL, $p_object_id = null, $p_show_in_tree = true, $p_condition = '')
    {
        $l_strSQL = "SELECT * FROM isys_catg_location_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_catg_formfactor_list ON isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__isys_obj__id
			WHERE (
				(isys_obj__status = " . $this->convert_sql_int($p_record_status) . ") AND
				(isys_obj__status != " . $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE) . ") ";

        if ($p_parent_id !== null)
        {
            $l_strSQL .= "AND (isys_catg_location_list__parentid = " . $this->convert_sql_id($p_parent_id) . ") ";
        } // if

        if ($p_object_id !== null)
        {
            $l_strSQL .= "AND (isys_obj__id = '" . $p_object_id . "') ";
        } // if

        if (!is_null($p_front))
        {
            $l_strSQL .= " AND (";

            if ($p_front)
            {
                $l_strSQL .= "((isys_catg_location_list__insertion = 1) OR (isys_catg_location_list__insertion = 2))";
            }
            else
            {
                $l_strSQL .= "((isys_catg_location_list__insertion = 0) OR (isys_catg_location_list__insertion = 2) " . " OR (isys_catg_location_list__insertion is null))";
            } // if

            $l_strSQL .= ")";
        } // if

        if ($p_parent_id !== null && $p_show_in_tree)
        {
            $l_strSQL .= " AND (isys_obj_type__show_in_tree = 1)";
        } // if

        if ($p_condition != '')
        {
            $l_strSQL .= $p_condition;
        } // if

        $l_strSQL .= ") ";

        return $this->retrieve($l_strSQL . ";");
    } // function

    /**
     * Return objects which are containers or which can be shown in a rack.
     *
     * @param   boolean $p_bContainer
     * @param   boolean $p_bInRack
     * @param   integer $p_record_status
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function get_location_objects($p_bContainer = true, $p_bInRack = true, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSQL = "SELECT * FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id
			WHERE ((isys_obj_type__show_in_tree = 1) AND (isys_obj_type__status = " . $this->convert_sql_int($p_record_status) . ")) ";

        if ($p_bContainer)
        {
            $l_strSQL .= "AND isys_obj_type__container = 1 ";
        } // if

        if ($p_bInRack)
        {
            $l_strSQL .= "AND isys_obj_type__show_in_rack = 1 ";
        } // if

        return $this->retrieve($l_strSQL . ';');
    } // function

    /**
     * Retrieves a location type specified by $p_typeid.
     *
     * @param   integer $p_typeid
     *
     * @return  isys_component_dao_result
     */
    public function get_location_type_by_id($p_typeid)
    {
        if (is_numeric($p_typeid) && $p_typeid > 0)
        {
            return $this->retrieve('SELECT * FROM isys_obj_type WHERE isys_obj_type__id = ' . $this->convert_sql_id($p_typeid) . ' AND isys_obj_type__container <> 0;');
        } // if

        return null;
    } // function

    /**
     * Attaches object specified by $p_objid to location object specified by $p_parent_objid. If the object is already attached
     * to a location object, the attachment is overwritten. Returns integer to identify ID of created record.
     *
     * @param   integer $p_objid
     * @param   integer $p_parent_objid
     * @param   array   $p_extradata
     *
     * @return  integer
     * @author  dennis stuecken <dstuecken@i-doit.org>
     */
    public function attach($p_objid, $p_parent_objid, $p_extradata = null)
    {
        if ($this->obj_exists($p_objid))
        {
            $l_loc_obj_type = $this->get_location_type_by_id($this->get_objTypeID($p_parent_objid));

            /* The object can only be attached, if one of these two conditions
               is met:

               a) Destination type is a container type
               b) Destination object is the root location object
            */
            if (($l_loc_obj_type && $l_loc_obj_type->num_rows() > 0) || ($p_parent_objid == $this->get_root_location_as_integer()))
            {
                /* Is location entry existent for location category? */
                $l_q = "SELECT * FROM isys_catg_location_list " . "WHERE isys_catg_location_list__isys_obj__id = '" . $p_objid . "';";

                $l_dbres = $this->retrieve($l_q);

                if ($l_dbres)
                {

                    /* If not, add one, otherwise rebind entry to new parent */
                    $this->m_mptt->action_stack_add(
                        ($l_dbres->num_rows() == 0) ? C__MPTT__ACTION_ADD : C__MPTT__ACTION_MOVE,
                        [
                            "node_id"   => $p_objid,
                            "parent_id" => $p_parent_objid
                        ]
                    );

                    /* Query for location entry again */
                    $l_dbres = $this->retrieve($l_q);

                    /* Fetch record data */
                    $l_entrydata = $l_dbres->get_row();

                    /* Check for nullentries before updating */
                    $l_property = (empty($l_entrydata["isys_catg_location_list__property"])) ? 0 : $l_entrydata["isys_catg_location_list__property"];
                    $l_status   = (empty($l_entrydata["isys_catg_location_list__status"])) ? 0 : $l_entrydata["isys_catg_location_list__status"];

                    /* Update location entry */
                    $this->m_mptt->action_stack_add(
                        C__MPTT__ACTION_UPDATE,
                        [
                            "node_id"                              => $p_objid,
                            "isys_catg_location_list__title"       => $l_entrydata["isys_catg_location_list__title"],
                            "isys_catg_location_list__description" => $l_entrydata["isys_catg_location_list__description"],
                            "isys_catg_location_list__lft"         => intval($l_entrydata["isys_catg_location_list__lft"]),
                            "isys_catg_location_list__rgt"         => intval($l_entrydata["isys_catg_location_list__rgt"]),
                            "isys_catg_location_list__property"    => intval($l_property),
                            "isys_catg_location_list__status"      => intval($l_status)
                        ]
                    );

                    /* Extra data? */
                    if ($p_extradata != null && is_array($p_extradata))
                    {
                        $l_arrupdate = ["node_id" => $p_objid];

                        foreach ($p_extradata as $l_field => $l_data)
                        {
                            $l_arrupdate[$l_field] = $l_data;
                        }

                        $this->m_mptt->action_stack_add(
                            C__MPTT__ACTION_UPDATE,
                            $l_arrupdate
                        );
                    }

                    /* Return ID of created / updated record */

                    return $l_entrydata["isys_catg_location_list__id"];
                }
            }
            else
            {

                /* Location not a container*/
                if ($l_loc_obj_type && $l_loc_obj_type->num_rows() <= 0)
                {

                    $l_row = $this->get_type_by_id($this->get_objTypeID($p_parent_objid));

                    isys_component_template_infobox::instance()
                        ->set_message(
                            "Your destination object is not a container. " . "Change your object-type config in order to add objects into \"" . _L(
                                $l_row["isys_obj_type__title"]
                            ) . "\".",
                            null,
                            null,
                            null,
                            C__LOGBOOK__ALERT_LEVEL__3
                        );
                }
            }
        }

        return null;
    }

    /**
     * Detaches object specified by $p_objid from its location.
     *
     * @param   integer $p_objid
     *
     * @return  boolean
     */
    public function detach($p_objid)
    {
        if ($this->obj_exists($p_objid))
        {
            // This is a real detach - so delete.
            $this->m_mptt->action_stack_add(C__MPTT__ACTION_DELETE, ["node_id" => $p_objid]);

            return true;
        } // if

        return false;
    } // function

    /**
     * Returns the DAO result containing the record with the object, which is mapping the root location.
     *
     * @return  isys_component_dao_result
     */
    public function get_root_location()
    {
        return $this->m_mptt->get_by_node_id(C__OBJ__ROOT_LOCATION);
    } // function

    /**
     * Returns the ID of the root location object.
     *
     * @return  mixed
     */
    public function get_root_location_as_integer()
    {
        $l_locres = $this->get_root_location();

        if (count($l_locres) > 0)
        {
            $l_rootdata = $l_locres->get_row();

            return $l_rootdata["isys_catg_location_list__isys_obj__id"];
        } // if

        return null;
    } // function

    /**
     * Return tree object with all locations
     *
     * @param   integer $p_objid
     *
     * @return  mixed
     */
    public function &get_locations_by_obj_id($p_objid)
    {
        // Create new result tree.
        $this->m_tree = [];

        // Perform read operation via MPTT.
        if ($this->m_mptt->read($p_objid, $this))
        {
            // Return resulting tree.
            return $this->m_tree;
        } // if

        return null;
    } // function

    /**
     * @param $p_objid
     * @param $p_origindata
     *
     * @return isys_component_dao_result|null
     */
    public function get_path_by_obj_id($p_objid, &$p_origindata)
    {
        if ($this->obj_exists($p_objid))
        {
            $l_locres = $this->m_mptt->get_by_node_id($p_objid);

            if (is_object($l_locres))
            {
                if ($l_locres->num_rows())
                {
                    $l_locdata = $l_locres->get_row();

                    if ($l_locdata["isys_catg_location_list__parentid"] == null) return null;

                    $l_left  = $l_locdata["isys_catg_location_list__lft"];
                    $l_right = $l_locdata["isys_catg_location_list__rgt"];

                    $p_origindata = $l_locdata;

                    /* Try to find path */

                    return $this->m_mptt->get_outer_by_left_right($l_left, $l_right);
                }
            }
        }

        return null;
    }

    /**
     * @return bool
     * @throws Exception
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function _location_fix()
    {
        $l_data = $this->retrieve(
            "SELECT isys_catg_location_list__id, COUNT(isys_catg_location_list__isys_obj__id) AS n FROM isys_catg_location_list GROUP BY isys_catg_location_list__isys_obj__id HAVING n > 1"
        );
        while ($l_row = $l_data->get_row())
        {
            $this->update("DELETE FROM isys_catg_location_list WHERE isys_catg_location_list__id = '" . $l_row["isys_catg_location_list__id"] . "';");
        }
        $this->apply_update();

        $this->begin_update();

        $this->initialize_lft_rgt();
        $this->save();

        return $this->apply_update() ? $this->regenerate_missing_relation_objects() : false;
    }

    /**
     *
     */
    public function save()
    {
        $this->m_mptt->write($this);
    }

    /**
     * @return  isys_component_dao_mptt
     */
    public function get_mptt()
    {
        return $this->m_mptt;
    } // function

    /**
     * Callback handler for read operations.
     *
     * @param  string  $p_table
     * @param  integer $p_level
     * @param  integer $p_id
     * @param  integer $p_node_id
     * @param  integer $p_parent_id
     * @param  string  $p_const
     * @param  integer $p_left
     * @param  integer $p_right
     * @param  mixed   $p_userdata
     * @param  string  $p_title
     */
    public function mptt_read($p_table, $p_level, $p_id, $p_node_id, $p_parent_id, $p_const, $p_left, $p_right, $p_userdata, $p_title = "")
    {
        $this->m_tree[] = [
            $p_node_id,
            $p_parent_id,
            $p_const,
            $p_level,
            $p_title
        ];
    } // function

    /**
     * Callback handler for write operations
     *
     * @param   integer $p_node_id
     * @param   integer $p_parent_id
     * @param   string  $p_const
     * @param   integer $p_left
     * @param   integer $p_right
     *
     * @return  boolean
     */
    public function mptt_write(&$p_node_id, &$p_parent_id, &$p_const, &$p_left, &$p_right)
    {
        return true;
    } // function

    /**
     * Retrieve an object-ID by it's parent-ID.
     *
     * @param   integer $p_parentID
     *
     * @return  integer
     */
    public function get_by_parent_id($p_parentID)
    {
        return $this->retrieve(
            "SELECT isys_catg_location_list__isys_obj__id FROM isys_catg_location_list WHERE isys_catg_location_list__parentid = " . $this->convert_sql_id(
                $p_parentID
            ) . ";"
        )
            ->get_row_value('isys_catg_location_list__isys_obj__id');
    } // function

    /**
     * Retrieve the child locations of a given parent location.
     * If the parentID is null, the root location is retrieved.
     * Retrieves only NORMAL records.
     *
     * @param   integer  $p_parentID
     * @param   boolean  $p_hiderootlocation
     * @param   boolean  $p_container_only
     * @param   boolean  $p_consider_rights
     *
     * @return  isys_component_dao_result
     */
    public function get_child_locations($p_parentID = null, $p_hiderootlocation = false, $p_container_only = false, $p_consider_rights = false)
    {
        $l_query = "SELECT
			(SELECT COUNT(child.isys_catg_location_list__id) FROM isys_catg_location_list child ";

        if ($p_container_only)
        {
            $l_query .= "INNER JOIN isys_obj childObject ON child.isys_catg_location_list__isys_obj__id = childObject.isys_obj__id " . "INNER JOIN isys_obj_type childType ON childObject.isys_obj__isys_obj_type__id = childType.isys_obj_type__id " . "INNER JOIN isys_cmdb_status ON childObject.isys_obj__isys_cmdb_status__id = isys_cmdb_status__id " . "WHERE child.isys_catg_location_list__parentid = parentObject.isys_obj__id AND childType.isys_obj_type__container = 1";
        }
        else
        {
            $l_query .= "WHERE child.isys_catg_location_list__parentid = parentObject.isys_obj__id";
        } // if

        $l_query .= ") AS ChildrenCount, parent.*, parentObject.*, parentType.*
			FROM isys_catg_location_list parent
			INNER JOIN isys_obj parentObject ON parent.isys_catg_location_list__isys_obj__id = parentObject.isys_obj__id
			INNER JOIN isys_obj_type parentType ON parentObject.isys_obj__isys_obj_type__id = parentType.isys_obj_type__id
			WHERE TRUE ";

        if ($p_consider_rights && $p_parentID != null)
        {
            $l_query .= isys_auth_cmdb_objects::instance()->get_allowed_objects_condition() . ' ';
        } // if

        if ($p_parentID == null)
        {
            if ($p_hiderootlocation)
            {
                $l_query .= " AND parent.isys_catg_location_list__parentid = " . $this->convert_sql_id(C__OBJ__ROOT_LOCATION);
            }
            else
            {
                $l_query .= " AND parent.isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id(C__OBJ__ROOT_LOCATION);
            } // if
        }
        else
        {
            $l_query .= " AND parent.isys_catg_location_list__parentid = " . $this->convert_sql_id($p_parentID);
        } // if

        if ($p_container_only)
        {
            $l_query .= " AND parentType.isys_obj_type__container = 1";
        } // if

        $l_query .= " AND parentObject.isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			GROUP BY parent.isys_catg_location_list__id
			ORDER BY parentObject.isys_obj__title;";

        return $this->retrieve($l_query);
    } // function

    /**
     * Method for retrieving all objects which are located underneath the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_child_locations_recursive($p_obj_id)
    {
        $l_return = [];

        if ($p_obj_id > 0)
        {
            $l_sql = 'SELECT obj.*, objtype.*, loc.isys_catg_location_list__parentid AS parent
				FROM isys_catg_location_list loc
				LEFT JOIN isys_obj obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
				LEFT JOIN isys_obj_type objtype ON isys_obj__isys_obj_type__id = isys_obj_type__id
				WHERE isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_obj_id);

            $l_res = $this->retrieve($l_sql);

            if ($l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_row['isys_obj_type__title'] = _L($l_row['isys_obj_type__title']);

                    $l_return[$l_row['isys_obj__id']] = $l_row;

                    $l_return = $l_return + $this->get_child_locations_recursive($l_row['isys_obj__id']);
                } // while
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Retrieve the hierarchy of nodes above the given node up to the root.
     *
     * @param   integer  $p_nodeid
     * @param   boolean  $p_hiderootlocation
     *
     * @return  string
     */
    public function get_node_hierarchy($p_nodeid, $p_hiderootlocation = false)
    {
        $l_hierachy = [];

        $l_iteration_id = $p_nodeid;

        while ($l_iteration_id != C__OBJ__ROOT_LOCATION && $l_iteration_id != null)
        {
            $l_query        = "SELECT * FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($l_iteration_id);
            $l_res          = $this->retrieve($l_query)->get_row();
            $l_objid        = $l_res ["isys_catg_location_list__isys_obj__id"];
            $l_iteration_id = $l_res["isys_catg_location_list__parentid"];
            $l_hierachy[]   = $l_objid;
        }

        if ($l_iteration_id == null)
        {
            $p_hiderootlocation = false;
        } // if

        if (!$p_hiderootlocation)
        {
            $l_hierachy[] = C__OBJ__ROOT_LOCATION;
        } // if

        return implode(",", $l_hierachy);
    } // function

    /**
     * Method which resets the lft nad rgt where no parents are set.
     *
     * @return  boolean
     */
    public function initialize_lft_rgt()
    {
        return $this->update(
            "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = NULL,
			isys_catg_location_list__rgt = NULL
			WHERE ISNULL(isys_catg_location_list__parentid)"
        );
    } // function

    /**
     * Method which rebuilds all locations which have no relation object
     */
    public function regenerate_missing_relation_objects()
    {
        $l_dao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);

        try
        {
            $l_root_location    = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\';';
            $l_root_location_id = $l_dao->retrieve($l_root_location)
                ->get_row_value('isys_obj__id');
            $l_sql              = 'SELECT * FROM isys_catg_location_list
				WHERE isys_catg_location_list__isys_catg_relation_list__id IS NULL AND
				(isys_catg_location_list__isys_obj__id != \'' . $l_root_location_id . '\' AND isys_catg_location_list__isys_obj__id > 0);';
            $l_res              = $l_dao->retrieve($l_sql);

            if ($l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    // rebuild missing relation for location entries
                    $l_dao->handle_relation(
                        $l_row['isys_catg_location_list__id'],
                        'isys_catg_location_list',
                        C__RELATION_TYPE__LOCATION,
                        null,
                        $l_row['isys_catg_location_list__parentid'],
                        $l_row['isys_catg_location_list__isys_obj__id']
                    );
                } // while
            } // if
            return true;
        }
        catch (Exception $e)
        {
            isys_notify::error('Error with following message: ' . $e->getMessage());
        } // try
        return false;
    } // function

    /**
     * Constructor
     *
     * @param  isys_component_database $p_db
     */
    public function __construct(isys_component_database &$p_db)
    {
        parent::__construct($p_db);

        $this->m_mptt = new isys_component_dao_mptt(
            $p_db,
            "isys_catg_location_list",
            "isys_catg_location_list__id",
            "isys_catg_location_list__isys_obj__id",
            "isys_catg_location_list__parentid",
            "isys_catg_location_list__const",
            "isys_catg_location_list__lft",
            "isys_catg_location_list__rgt"
        );
    } // function
} // class
