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
 * DAO: global category for Nagios
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_refs_services extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_refs_services';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_nagios_refs_services_list__isys_obj__id__service';
    /**
     * @var bool
     */
    protected $m_has_relation = true;
    protected $m_multivalued = true;
    /**
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;
    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'assigned_objects';
    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_nagios_refs_services_list__isys_obj__id__host';

    /**
     * Save global category Nagios element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_new_id
     *
     * @return  integer
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_delete_data = $l_data = $this->get_selected_objects($p_object_id, true);

        foreach ($l_data AS $l_data_key => $l_obj_id)
        {
            if (($l_key = array_search($l_obj_id, $p_objects)) !== false)
            {
                unset($p_objects[$l_key]);
                unset($l_delete_data[$l_data_key]);
            } // if
        } // foreach

        if (count($l_delete_data) > 0)
        {
            $this->delete_entry(array_keys($l_delete_data), 'isys_catg_nagios_refs_services_list');
        }

        if (count($p_objects) > 0)
        {
            foreach ($p_objects AS $l_obj_id)
            {
                $this->create($p_object_id, $l_obj_id);
            } // foreach
        } // if
    } // function

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_object_id
     * @param   integer $p_connected_obj
     *
     * @return  mixed  Integer with the newly created ID or boolean false on failure.
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function create($p_object_id, $p_connected_obj = null)
    {
        /* We are called from generic sync context */
        if (is_array($p_object_id))
        {
            $l_tmp           = $p_object_id;
            $p_object_id     = $l_tmp['isys_obj__id'];
            $p_connected_obj = $l_tmp['assigned_objects'];
        }

        $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_sql = 'INSERT INTO isys_catg_nagios_refs_services_list SET ' . 'isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id(
                $p_object_id
            ) . ', ' . 'isys_catg_nagios_refs_services_list__isys_obj__id__service = ' . $this->convert_sql_id($p_connected_obj);

        $l_last_id = false;

        if ($this->update($l_sql) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            $l_dao_rel->handle_relation(
                $l_last_id,
                'isys_catg_nagios_refs_services_list',
                C__RELATION_TYPE__NAGIOS_REFS_SERVICE,
                null,
                $p_object_id,
                $p_connected_obj
            );
        } // if

        return $l_last_id;
    } // function

    /**
     * Count method
     *
     * @param null $p_obj_id
     *
     * @return int
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) $l_obj_id = $p_obj_id;
        else $l_obj_id = $this->m_object_id;

        $l_sql = 'SELECT count(isys_catg_nagios_refs_services_list__isys_obj__id__service) AS count FROM isys_catg_nagios_refs_services_list ' . 'WHERE TRUE ';

        if (!empty($l_obj_id))
        {
            $l_sql .= ' AND isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id($p_obj_id);
        } // if

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    } // function

    /**
     * get_data method.
     *
     * @param null   $p_catg_list_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_nagios_refs_services_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__host ' . 'WHERE TRUE ' . $p_condition . ' ';

        if ($p_obj_id !== null)
        {
            $l_sql .= 'AND isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id($p_obj_id) . ' ';
        }

        if ($p_catg_list_id !== null)
        {
            $l_sql .= 'AND isys_catg_nagios_refs_services_list__id = ' . $this->convert_sql_id($p_catg_list_id) . ' ';
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
            'assigned_objects' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__OBJTYPE__NAGIOS_SERVICE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_nagios_refs_services_list__isys_obj__id__service',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__NAGIOS_REFS_SERVICE,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_nagios_refs_services',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_g_nagios_refs_services']
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_obj',
                            'isys_obj__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ASSIGNED_SERVICES'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => false
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
     * Sync method used for import mechanism.
     *
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     */
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
                            $p_category_data['properties']['assigned_objects'][C__DATA__VALUE]
                        );
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_selected_objects = $this->get_selected_objects($p_object_id, true);
                    if (!in_array($p_category_data['properties']['assigned_objects'][C__DATA__VALUE], $l_selected_objects) || count($l_selected_objects) == 0)
                    {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['assigned_objects'][C__DATA__VALUE]
                        );
                    }

                    return $p_category_data['data_id'];
                    break;
            } // switch
        }

        return false;
    } // function

    /**
     * Gets assigned nagios services for object browser
     *
     * @param      $p_obj_id
     * @param bool $p_as_array
     *
     * @return array|isys_component_dao_result
     */
    public function get_selected_objects($p_obj_id, $p_as_array = false)
    {
        $l_sql = 'SELECT isys_obj.*, isys_catg_nagios_refs_services_list__id FROM isys_catg_nagios_refs_services_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__service ' . 'WHERE isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ' ';

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " ";

        if ($p_as_array)
        {
            $l_res  = $this->retrieve($l_sql);
            $l_data = [];

            while ($l_row = $l_res->get_row())
            {
                $l_data[$l_row['isys_catg_nagios_refs_services_list__id']] = $l_row['isys_obj__id'];
            } // while
            return $l_data;
        }
        else
        {
            return $this->retrieve($l_sql);
        } // if
    } // function

} // class
?>