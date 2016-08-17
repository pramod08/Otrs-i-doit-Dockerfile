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
 * DAO: global category for Nagios.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.<com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_refs_services_backwards extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_refs_services_backwards';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_nagios_refs_services_list__isys_obj__id__host';
    /**
     * @var bool
     */
    protected $m_has_relation = true;
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
    protected $m_object_id_field = 'isys_catg_nagios_refs_services_list__isys_obj__id__service';

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return mixed|null
     * @throws Exception
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id          = null;
        $l_delete_data = $l_data = $this->get_selected_objects($p_object_id, true);

        foreach ($l_data as $l_data_key => $l_obj_id)
        {
            if (($l_key = array_search($l_obj_id, $p_objects)) !== false)
            {
                unset($p_objects[$l_key], $l_delete_data[$l_data_key]);
            } // if
        } // foreach

        if (count($l_delete_data) > 0)
        {
            $this->delete_entry(array_keys($l_delete_data), 'isys_catg_nagios_refs_services_list');
        }

        if (count($p_objects) > 0)
        {
            foreach ($p_objects as $l_obj_id)
            {
                $l_id = $this->create($p_object_id, $l_obj_id);
            } // foreach
        } // if

        return $l_id;
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
    public function create($p_object_id, $p_connected_obj)
    {
        $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_sql = 'INSERT INTO isys_catg_nagios_refs_services_list
			SET isys_catg_nagios_refs_services_list__isys_obj__id__host = ' . $this->convert_sql_id($p_connected_obj) . ',
			isys_catg_nagios_refs_services_list__isys_obj__id__service = ' . $this->convert_sql_id($p_object_id) . ';';

        $l_last_id = false;

        if ($this->update($l_sql) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            $l_dao_rel->handle_relation(
                $l_last_id,
                'isys_catg_nagios_refs_services_list',
                C__RELATION_TYPE__NAGIOS_REFS_SERVICE,
                null,
                $p_connected_obj,
                $p_object_id
            );
        } // if

        return $l_last_id;
    } // function

    /**
     * Count method, for the navigation tree.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $this->m_object_id;

        if ($p_obj_id !== null)
        {
            $l_obj_id = $p_obj_id;
        } // if

        $l_sql = 'SELECT count(isys_catg_nagios_refs_services_list__isys_obj__id__service) AS count
			FROM isys_catg_nagios_refs_services_list
			WHERE TRUE ';

        if ($l_obj_id > 0)
        {
            $l_sql .= 'AND isys_catg_nagios_refs_services_list__isys_obj__id__service = ' . $this->convert_sql_id($p_obj_id);
        } // if

        return (int) current(
            $this->retrieve($l_sql . ';')
                ->get_row()
        );
    } // function

    /**
     * get_data method.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_nagios_refs_services_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__host
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_catg_list_id !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_refs_services_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_refs_services_list__isys_obj__id__service = ' . $this->convert_sql_id($p_obj_id);
        } // if

        return $this->retrieve($l_sql . ';');
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
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_HOST_TPL_ASSIGNED_OBJECTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_nagios_refs_services_list__isys_obj__id__host',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__NAGIOS_REFS_SERVICE,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_nagios_refs_services_backwards',
                                'callback_property_relation_handler'
                            ], [
                                'isys_cmdb_dao_category_g_nagios_refs_services_backwards',
                                true
                            ]
                        ),
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ASSIGNED_SERVICES'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__EXPORT => false
                    ]
                ]
            )
        ];
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
        $l_sql = 'SELECT isys_obj.*, isys_catg_nagios_refs_services_list__id
			FROM isys_catg_nagios_refs_services_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_refs_services_list__isys_obj__id__host
			WHERE isys_catg_nagios_refs_services_list__isys_obj__id__service = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

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