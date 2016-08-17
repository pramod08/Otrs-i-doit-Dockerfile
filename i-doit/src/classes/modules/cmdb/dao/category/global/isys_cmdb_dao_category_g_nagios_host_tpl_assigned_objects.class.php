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
class isys_cmdb_dao_category_g_nagios_host_tpl_assigned_objects extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'nagios_host_tpl_assigned_objects';
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
    protected $m_object_browser_property = 'connected_object';
    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = 'isys_cmdb_dao_category_g_nagios';
    /**
     * category table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_nagios_list';

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
        $l_dao         = new isys_cmdb_dao_category_g_nagios($this->m_db);
        $l_delete_data = $l_data = $this->get_selected_objects($_GET[C__CMDB__GET__OBJECT], true);

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
            $l_update = 'UPDATE isys_catg_nagios_list SET isys_catg_nagios_list__host_tpl = NULL WHERE ' . 'isys_catg_nagios_list__isys_obj__id IN (' . implode(
                    ',',
                    $l_delete_data
                ) . ')';
            $this->update($l_update);
        }

        if (count($p_objects) > 0)
        {
            foreach ($p_objects AS $l_obj_id)
            {
                $l_new_data = [];
                $l_res      = $l_dao->get_data(null, $l_obj_id);
                if ($l_res->num_rows() > 0)
                {
                    $l_catdata = $l_res->get_row();

                    if (!empty($l_catdata['isys_catg_nagios_list__host_tpl']))
                    {
                        if (isys_format_json::is_json_array($l_catdata['isys_catg_nagios_list__host_tpl']))
                        {
                            $l_new_data   = isys_format_json::decode($l_catdata['isys_catg_nagios_list__host_tpl']);
                            $l_new_data[] = $p_object_id;
                        }
                        else
                        {
                            $l_new_data[] = $l_catdata['isys_catg_nagios_list__host_tpl'];
                            $l_new_data[] = $p_object_id;
                        } // if
                    }
                    else
                    {
                        $l_new_data = $p_object_id;
                    } // if

                    $l_update = 'UPDATE isys_catg_nagios_list SET isys_catg_nagios_list__host_tpl = ' . $l_dao->convert_sql_text(
                            $l_new_data
                        ) . ' ' . 'WHERE isys_catg_nagios_list__id = ' . $l_dao->convert_sql_id($l_catdata['isys_catg_nagios_list__id']);
                    $l_dao->update($l_update);
                }
                else
                {
                    $l_new_data['isys_catg_nagios_list__host_tpl'] = $_GET[C__CMDB__GET__OBJECT];
                    $l_dao->create($l_obj_id, $l_new_data);
                } // if
            } // foreach
        } // if
        return $l_dao->apply_update();
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

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) $l_obj_id = $p_obj_id;
        else $l_obj_id = $this->m_object_id;

        $l_sql = 'SELECT count(isys_obj__id) AS count FROM isys_catg_nagios_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_list__isys_obj__id ' . 'WHERE TRUE ';

        if (!empty($l_obj_id))
        {
            $l_sql .= ' AND LOCATE(\'' . $l_obj_id . '\', isys_catg_nagios_list__host_tpl) ';
        } // if

        $l_sql .= ' AND (isys_catg_nagios_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

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
        $l_sql = 'SELECT * FROM isys_catg_nagios_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_list__isys_obj__id ' . 'WHERE LOCATE(\'' . $p_obj_id . '\', isys_catg_nagios_list__host_tpl) ';

        if (!empty($p_status))
        {
            $l_sql .= "AND isys_catg_nagios_list__status = " . $this->convert_sql_int($p_status) . " ";
        } // if

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
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_HOST_TPL_ASSIGNED_OBJECTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_nagios_list__host_tpl'
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

    public function get_selected_objects($p_obj_id, $p_as_array = false)
    {
        $l_sql = 'SELECT isys_obj.*, isys_catg_nagios_list__id FROM isys_catg_nagios_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_list__isys_obj__id ' . 'WHERE LOCATE(\'' . $p_obj_id . '\', isys_catg_nagios_list__host_tpl) ';

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " ";

        if ($p_as_array)
        {
            $l_res  = $this->retrieve($l_sql);
            $l_data = [];

            while ($l_row = $l_res->get_row())
            {
                $l_data[$l_row['isys_catg_nagios_list__id']] = $l_row['isys_obj__id'];
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