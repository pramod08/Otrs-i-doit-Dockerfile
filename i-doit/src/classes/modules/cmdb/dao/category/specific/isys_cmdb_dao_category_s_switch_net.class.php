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
 * DAO: specific category for network switches
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_switch_net extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'switch_net';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   integer $p_vlan
     * @param   integer $p_role
     * @param   integer $p_spanning_tree
     * @param   string  $p_description
     *
     * @return  mixed  Integer of the newly created ID or boolean false on failure.
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_vlan = null, $p_role = null, $p_spanning_tree = null, $p_description = '')
    {
        $l_sql = 'INSERT IGNORE INTO ' . $this->m_table . ' SET ' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ', ' . $this->m_table . '__description = ' . $this->convert_sql_text(
                $p_description
            ) . ', ' . $this->m_table . '__isys_vlan_management_protocol__id  = ' . $this->convert_sql_id(
                $p_vlan
            ) . ', ' . $this->m_table . '__isys_switch_role__id  = ' . $this->convert_sql_id(
                $p_role
            ) . ', ' . $this->m_table . '__isys_switch_spanning_tree__id  = ' . $this->convert_sql_id(
                $p_spanning_tree
            ) . ', ' . $this->m_table . '__status = ' . $this->convert_sql_id($p_status) . ';';

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
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = 'SELECT * FROM ' . $this->m_table . ' ' . 'LEFT JOIN isys_vlan_management_protocol ON isys_vlan_management_protocol__id = ' . $this->m_table . '__isys_vlan_management_protocol__id ' . 'LEFT JOIN isys_switch_role ON isys_switch_role__id = ' . $this->m_table . '__isys_switch_role__id ' . 'LEFT JOIN isys_switch_spanning_tree ON isys_switch_spanning_tree__id = ' . $this->m_table . '__isys_switch_spanning_tree__id ' . 'WHERE TRUE ' . $p_condition . ' ';

        if ($p_cats_list_id !== null)
        {
            $l_sql .= 'AND ' . $this->m_table . '__id = ' . $this->convert_sql_id($p_cats_list_id) . ' ';
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= 'AND ' . $this->m_table . '__status = ' . $this->convert_sql_int($p_status) . ' ';
        } // if

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
                $l_sql = ' AND (' . $this->m_table . '__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'vlan'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__SWITCH__VLAN_MANAGEMENT_PROTOCOL',
                        C__PROPERTY__INFO__DESCRIPTION => 'VLAN management protocol'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_switch_net_list__isys_vlan_management_protocol__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_vlan_management_protocol',
                            'isys_vlan_management_protocol__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__SWITCH_NET__VLAN',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_vlan_management_protocol'
                        ]
                    ]
                ]
            ),
            'role'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__SWITCH__ROLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Role'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_switch_net_list__isys_switch_role__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_switch_role',
                            'isys_switch_role__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__SWITCH_NET__ROLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_switch_role'
                        ]
                    ]
                ]
            ),
            'spanning_tree' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__SWITCH__SPANNING_TREE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Spanning Tree'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_switch_net_list__isys_switch_spanning_tree__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_switch_spanning_tree',
                            'isys_switch_spanning_tree__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__SWITCH_NET__SPANNING_TREE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_switch_spanning_tree'
                        ]
                    ]
                ]
            ),
            'description'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_switch_net_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__SWITCH_NET
                    ]
                ]
            )
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   int     $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            if ($p_status == isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create(
                    $p_object_id,
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['vlan'][C__DATA__VALUE],
                    $p_category_data['properties']['role'][C__DATA__VALUE],
                    $p_category_data['properties']['spanning_tree'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
                if ($p_category_data['data_id'])
                {
                    $l_indicator = true;
                } // if
            } // if
            if ($p_status == isys_import_handler_cmdb::C__UPDATE)
            {
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_object_id,
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['vlan'][C__DATA__VALUE],
                    $p_category_data['properties']['role'][C__DATA__VALUE],
                    $p_category_data['properties']['spanning_tree'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Performance optimized query for retrieving connected vlans of a switch
     *
     * @param int    $p_switch_object_id
     * @param string $p_order_by
     * @param int    $p_status
     *
     * @return isys_component_dao_result
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     *
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_assigned_vlans($p_switch_object_id, $p_order_by = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT DISTINCT isys_cats_layer2_net_list__ident, isys_obj__id, isys_obj__title, isys_cats_layer2_net_assigned_ports_list__default FROM isys_catg_port_list port
                    INNER JOIN isys_cats_layer2_net_assigned_ports_list l2 ON port.isys_catg_port_list__id = l2.isys_catg_port_list__id
                    INNER JOIN isys_cats_layer2_net_list ON l2.isys_cats_layer2_net_assigned_ports_list__isys_obj__id = isys_cats_layer2_net_list__isys_obj__id
                    INNER JOIN isys_obj ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id

                    WHERE isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_switch_object_id
            ) . ' AND isys_cats_layer2_net_assigned_ports_list__status = ' . $this->convert_sql_id($p_status) . '';

        if ($p_order_by)
        {
            $l_sql .= ' ORDER BY ' . $p_order_by;
        }

        return $this->retrieve($l_sql);
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   integer $p_vlan
     * @param   integer $p_role
     * @param   integer $p_spanning_tree
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_cat_id, $p_obj_id, $p_status, $p_vlan, $p_role, $p_spanning_tree, $p_description)
    {
        $l_sql = 'UPDATE ' . $this->m_table . ' SET ' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ', ' . $this->m_table . '__description = ' . $this->convert_sql_text(
                $p_description
            ) . ', ' . $this->m_table . '__isys_vlan_management_protocol__id  = ' . $this->convert_sql_id(
                $p_vlan
            ) . ', ' . $this->m_table . '__isys_switch_role__id  = ' . $this->convert_sql_id(
                $p_role
            ) . ', ' . $this->m_table . '__isys_switch_spanning_tree__id  = ' . $this->convert_sql_id(
                $p_spanning_tree
            ) . ', ' . $this->m_table . '__status = ' . $this->convert_sql_id($p_status) . ' ' . 'WHERE ' . $this->m_table . '__id = ' . $this->convert_sql_id($p_cat_id);

        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Save specific category switch_net
     *
     * @param   integer $p_cat_level         Level to save, default 0.
     * @param   integer & $p_intOldRecStatus Status of record before update.
     *
     * @return  integer
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_switch_net_list__status"];
        $l_list_id         = $l_catdata['isys_cats_switch_net_list__id'];

        if (empty($l_list_id))
        {
            // We use this method for creating an empty value inside our database.
            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT]);
        } // if

        $l_bRet = $this->save(
            $l_list_id,
            $_GET[C__CMDB__GET__OBJECT],
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CMDB__CATS__SWITCH_NET__VLAN'],
            $_POST['C__CMDB__CATS__SWITCH_NET__ROLE'],
            $_POST['C__CMDB__CATS__SWITCH_NET__SPANNING_TREE'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );

        return ($l_bRet == true) ? $l_list_id : -1;
    } // function
} // class
?>