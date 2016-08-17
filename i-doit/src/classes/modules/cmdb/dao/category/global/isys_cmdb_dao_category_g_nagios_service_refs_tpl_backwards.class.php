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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_nagios_service_refs_tpl_backwards extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_service_refs_tpl_backwards';

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
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return null
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id  = null;
        $l_dao = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_db);

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
            foreach ($l_delete_data as $l_obj_id)
            {
                $l_row = $l_dao->get_data(null, $l_obj_id)
                    ->get_row();

                // Remove the template reference.
                $l_id = $l_dao->save_data(
                    ($l_row == false) ? null : $l_row['isys_catg_nagios_service_def_list__id'],
                    [
                        'template' => ''
                    ]
                );
            }
        }

        if (count($p_objects) > 0)
        {
            foreach ($p_objects as $l_obj_id)
            {
                $l_row = $l_dao->get_data(null, $l_obj_id)
                    ->get_row();

                $l_id = $l_dao->save_data(
                    ($l_row == false) ? null : $l_row['isys_catg_nagios_service_def_list__id'],
                    [
                        'template'     => $p_object_id,
                        'isys_obj__id' => $l_obj_id
                    ]
                );
            } // foreach
        } // if

        return $l_id;
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catg_memory__id $p_fk_id.
     *
     * @param   integer $p_object_id
     * @param   array   $p_arData
     *
     * @return  mixed  Integer with the newly created ID or boolean false on failure.
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function create($p_object_id, $p_arData)
    {
        $l_fields = [];

        foreach ($p_arData as $key => $value)
        {
            $l_fields[] = $key . ' = ' . $value;
        } // foreach

        $l_fields[] = 'isys_catg_nagios_host_tpl_def_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        $l_sql = 'INSERT INTO isys_catg_nagios_host_tpl_def_list SET ' . implode(', ', $l_fields) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Method for retrieving the number of category-rows.
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

        $l_sql = 'SELECT count(isys_obj__id) AS count
			FROM isys_catg_nagios_service_def_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_service_def_list__isys_obj__id
			WHERE TRUE
			AND isys_catg_nagios_service_def_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($l_obj_id))
        {
            $l_sql .= ' AND LOCATE("' . $this->convert_sql_id($l_obj_id) . '", isys_catg_nagios_service_def_list__service_template)';
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
        $l_sql = 'SELECT * FROM isys_catg_nagios_service_def_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_service_def_list__isys_obj__id
			WHERE TRUE
			' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_catg_list_id !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_service_def_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= ' AND LOCATE("' . $this->convert_sql_id($p_obj_id) . '", isys_catg_nagios_service_def_list__service_template)';
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_catg_nagios_service_def_list__status = ' . $this->convert_sql_int($p_status);
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
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_HOST_TPL_ASSIGNED_OBJECTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_service_def_list__service_template'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__NAGIOS_ASSIGNED_OBJECTS'
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
     * Method for retrieving the referenced objects.
     *
     * @param   integer $p_obj_id
     * @param   boolean $p_as_array
     *
     * @return  mixed  May return an instance of isys_component_dao_result or an array.
     */
    public function get_selected_objects($p_obj_id, $p_as_array = false)
    {
        $l_sql = 'SELECT isys_obj.*
			FROM isys_catg_nagios_service_def_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_service_def_list__isys_obj__id
			WHERE LOCATE("' . $p_obj_id . '", isys_catg_nagios_service_def_list__service_template)
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($p_as_array)
        {
            $l_data = [];
            $l_res  = $this->retrieve($l_sql);

            while ($l_row = $l_res->get_row())
            {
                $l_data[] = $l_row['isys_obj__id'];
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