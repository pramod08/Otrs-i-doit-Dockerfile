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
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: assigned logical unit.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_assigned_logical_unit extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'assigned_logical_unit';
    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__ASSIGNED_LOGICAL_UNIT';
    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CATG__ASSIGNED_LOGICAL_UNIT;
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id';
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
     * Flag
     *
     * @var bool
     */
    protected $m_object_browser_category = true;
    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'assigned_object';
    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id__parent';
    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = 'isys_cmdb_dao_category_g_logical_unit';
    /**
     * category table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_logical_unit_list';

    /**
     * Method for getting the object-browsers preselection.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_selected_objects($p_obj_id)
    {
        $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        return $l_dao->get_data_by_parent($p_obj_id);
    }

    /**
     * @param array $p_post
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_category_data_id = null;
        $l_relation_dao     = new isys_cmdb_dao_category_g_relation($this->m_db);
        $l_dao              = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        // First get assigned devices
        $l_dao_res        = $l_dao->get_data_by_parent($p_object_id);
        $l_assigned_units = [];

        if ($l_dao_res->num_rows() > 0)
        {
            while ($l_dao_row = $l_dao_res->get_row())
            {
                $l_assigned_units[$l_dao_row['isys_catg_logical_unit_list__id'] . '#' . $l_dao_row['isys_catg_logical_unit_list__isys_catg_relation_list__id']] = $l_dao_row['isys_obj__id'];
            } // while
        } // if

        // Now we create the new entries.
        foreach ($p_objects as $l_id)
        {
            if (!in_array($l_id, $l_assigned_units))
            {
                $l_rows = $l_dao->get_data(null, $l_id)
                    ->num_rows();

                // If there is no entry, we create a new one.
                if ($l_rows == 0)
                {
                    $l_category_data_id = $this->create_connector('isys_catg_logical_unit_list', $l_id);
                    $l_relation_id      = null;
                }
                else
                {
                    $l_row              = $l_dao->get_data(null, $l_id)
                        ->get_row();
                    $l_category_data_id = $l_row['isys_catg_logical_unit_list__id'];
                    $l_relation_id      = $l_row['isys_catg_logical_unit_list__isys_catg_relation_list__id'];
                } // if

                $l_sql = 'UPDATE isys_catg_logical_unit_list ' . 'SET isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id(
                        $p_object_id
                    ) . ' ' . 'WHERE isys_catg_logical_unit_list__id = ' . $this->convert_sql_id($l_category_data_id);

                if ($this->update($l_sql))
                {
                    $l_relation_dao->handle_relation(
                        $l_category_data_id,
                        'isys_catg_logical_unit_list',
                        C__RELATION_TYPE__LOGICAL_UNIT,
                        $l_relation_id,
                        $_GET[C__CMDB__GET__OBJECT],
                        $l_id
                    );
                } // if
            }
            elseif (count($l_assigned_units) > 0)
            {
                $l_key = array_search($l_id, $l_assigned_units);
                unset($l_assigned_units[$l_key]);
            } // if
        } // foreach

        // Now we delete the entries
        if (count($l_assigned_units) > 0)
        {
            foreach ($l_assigned_units AS $l_key => $l_obj_id)
            {
                list($l_id, $l_rel_id) = explode('#', $l_key);
                $l_relation_dao->delete_relation($l_rel_id);
                $l_dao->delete_entry($l_id, 'isys_catg_logical_unit_list');
            } // foreach
        } // if

        return $l_category_data_id;
    } // function

    /**
     * Do nothing
     *
     * @param      $p_cat_level
     * @param      $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return null
     */
    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        return null;
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

        $l_sql = "SELECT COUNT(isys_catg_logical_unit_list__id) AS count FROM isys_catg_logical_unit_list " . "WHERE TRUE ";

        if (!empty($l_obj_id))
        {
            $l_sql .= " AND (isys_catg_logical_unit_list__isys_obj__id__parent = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_logical_unit_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    } // function

    /**
     * Get data method, uses logical unit DAO.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        //return $l_dao->get_data($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status);
        return $l_dao->get_data_by_parent($p_obj_id);
    } // function

    /**
     * Get UI method, because the UI class name breaks the standards.
     *
     * @global  isys_component_template $g_comp_template
     * @return  isys_cmdb_ui_category_g_virtual_cabling
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function &get_ui()
    {
        global $g_comp_template;

        return new isys_cmdb_ui_category_g_assigned_logical_unit($g_comp_template);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__OBJECT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned Object'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_logical_unit_list__isys_obj__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__LOGICAL_UNIT,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_assigned_logical_unit',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_g_assigned_logical_unit']
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_obj',
                            'isys_obj__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ASSIGNED_LOGICAL_UNITS',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => true,
                            'catFilter'      => 'C__CATG__ASSIGNED_WORKSTATION'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__SEARCH    => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ]
                ]
            )
        ];
    } // function

    /**
     * Purge entries.
     *
     * @param   array $p_cat_ids
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  boolean
     */
    public function rank_records($p_cat_ids, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE])
        {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                $l_dao          = new isys_cmdb_dao_category_g_logical_unit($this->m_db);
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);

                if (is_array($p_cat_ids))
                {
                    foreach ($p_cat_ids AS $l_cat_id)
                    {
                        $l_catdata = $l_dao->get_data($l_cat_id)
                            ->get_row();

                        // First delete relation.
                        if ($l_relation_dao->delete_relation($l_catdata['isys_catg_logical_unit_list__isys_catg_relation_list__id']))
                        {
                            // Then delete entry.
                            $l_dao->delete_entry($l_cat_id, 'isys_catg_logical_unit_list');
                        } // if
                    } // foreach
                }

                return true;
        } // switch
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            if (($p_status == isys_import_handler_cmdb::C__CREATE || $p_status == isys_import_handler_cmdb::C__UPDATE))
            {
                $l_val = [];

                if (is_array($p_category_data['properties']['assigned_object'][C__DATA__VALUE]))
                {
                    foreach ($p_category_data['properties']['assigned_object'][C__DATA__VALUE] AS $l_obj_id)
                    {
                        $l_val[] = $l_obj_id;
                    }
                }
                else
                {
                    $l_val[] = $p_category_data['properties']['assigned_object'][C__DATA__VALUE];
                }

                if (count($l_val) > 0)
                {
                    $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                    foreach ($l_val AS $l_obj_id)
                    {
                        $l_category_data_id = $this->create_connector('isys_catg_logical_unit_list', $l_obj_id);
                        $l_relation_id      = null;

                        $l_sql = 'UPDATE isys_catg_logical_unit_list ' . 'SET isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id(
                                $p_object_id
                            ) . ' ' . 'WHERE isys_catg_logical_unit_list__id = ' . $this->convert_sql_id($l_category_data_id);

                        if ($this->update($l_sql))
                        {
                            $l_relation_dao->handle_relation(
                                $l_category_data_id,
                                'isys_catg_logical_unit_list',
                                C__RELATION_TYPE__LOGICAL_UNIT,
                                $l_relation_id,
                                $p_object_id,
                                $l_obj_id
                            );
                        } // if
                    }
                } // if
                return true;
            } // if
        }

        return false;
    } // function

} // class
?>