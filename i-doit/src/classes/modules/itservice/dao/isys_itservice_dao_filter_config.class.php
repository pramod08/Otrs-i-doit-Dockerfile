<?php

/**
 * i-doit
 *
 * IT-Service DAO.
 *
 * @package     modules
 * @subpackage  itservice
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4
 */
class isys_itservice_dao_filter_config extends isys_module_dao
{
    /**
     * This variable will cache the available filters.
     *
     * @var  array
     */
    private static $m_cache = null;

    /**
     * Get data method for retrieving the configuration data.
     *
     * @param   integer $p_id
     *
     * @return  array    isys_array
     */
    public function get_data($p_id = null)
    {
        if (static::$m_cache === null)
        {
            $l_res = $this->get_data_raw();

            if (count($l_res))
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_row['formatted__data'] = isys_format_json::decode($l_row['isys_itservice_filter_config__data']);

                    static::$m_cache[$l_row['isys_itservice_filter_config__id']] = new isys_array($l_row);
                } // while
            } // if
        } // if

        if ($p_id !== null && isset(static::$m_cache[$p_id]))
        {
            return static::$m_cache[$p_id];
        } // if

        return static::$m_cache;
    } // function

    /**
     * Method for saving a filter configuration.
     *
     * @param   integer $p_id
     * @param   array   $p_data
     *
     * @return  integer
     */
    public function save_data($p_id = null, $p_data = [])
    {
        $l_data = [];

        foreach ($p_data as $l_field => $l_value)
        {
            $l_data[] = $l_field . ' = ' . $this->convert_sql_text($l_value);
        } // foreach

        if ($p_id > 0)
        {
            $l_sql = 'UPDATE isys_itservice_filter_config SET ' . implode(', ', $l_data) . ' WHERE isys_itservice_filter_config__id = ' . $this->convert_sql_id($p_id) . ';';
        }
        else
        {
            $l_sql = 'INSERT INTO isys_itservice_filter_config SET ' . implode(', ', $l_data) . ';';
        } // if

        if ($this->update($l_sql) && $this->apply_update())
        {
            isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));

            return $p_id ?: $this->get_last_insert_id();
        }
        else
        {
            isys_notify::error(_L('LC__INFOBOX__DATA_WAS_NOT_SAVED: ' . $this->m_db->get_last_error_as_string()), ['sticky' => true]);

            return null;
        } // if
    } // function

    /**
     * Method for deleting one or more configurations.
     *
     * @param   mixed $p_id May be a integer or a array.
     *
     * @return  boolean
     */
    public function delete_data($p_id)
    {
        if (!is_array($p_id))
        {
            $p_id = [$p_id];
        } // if

        if ($this->update(
                'DELETE FROM isys_itservice_filter_config WHERE isys_itservice_filter_config__id IN (' . implode(', ', array_map('intval', $p_id)) . ');'
            ) && $this->apply_update()
        )
        {
            isys_notify::success(_L('LC__INFOBOX__DATA_WAS_DELETED'));

            return true;
        }
        else
        {
            isys_notify::error(_L('LC__INFOBOX__DATA_WAS_NOT_DELETED: ' . $this->m_db->get_last_error_as_string()), ['sticky' => true]);

            return false;
        } // if
    } // function

    /**
     * Method for retrieving a RAW database result.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_data_raw($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_itservice_filter_config WHERE TRUE ';

        if ($p_id !== null)
        {
            $l_sql .= 'AND isys_itservice_filter_config__id = ' . $this->convert_sql_id($p_id);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Retrieves the prepared array data for the "relation type" dialog list.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_relationtype_filter_data($p_id = null)
    {
        $l_return = $l_config = [];

        if ($p_id !== null)
        {
            $l_config = $this->get_data($p_id);
            $l_config = $l_config['formatted__data'];
        } // if

        $l_relation_types = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_relation_type')
            ->get_data();

        foreach ($l_relation_types as $l_data)
        {
            if ($l_data['isys_relation_type__status'] == C__RECORD_STATUS__NORMAL)
            {
                $l_title = _L($l_data['isys_relation_type__title']);

                if (isset($l_return[$l_title]))
                {
                    // This is necessary, because Chosen will not display two items with the same value :/
                    $l_title .= ' (#' . $l_data['isys_relation_type__id'] . ')';
                } // if

                $l_return[$l_title] = [
                    'id'  => $l_data['isys_relation_type__id'],
                    'sel' => (in_array($l_data['isys_relation_type__id'], $l_config['relation-type'] ?: [])),
                    'val' => $l_title
                ];
            } // if
        } // foreach

        return array_values($l_return);
    } // function

    /**
     * Retrieves the prepared array data for the "object type" dialog list.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_objecttype_filter_data($p_id = null)
    {
        $l_return = $l_config = [];

        if ($p_id !== null)
        {
            $l_config = $this->get_data($p_id);
            $l_config = $l_config['formatted__data'];
        } // if

        $l_obj_types = isys_cmdb_dao::instance($this->m_db)
            ->get_object_type();

        foreach ($l_obj_types as $l_data)
        {
            if ($l_data['isys_obj_type__status'] == C__RECORD_STATUS__NORMAL)
            {
                $l_return[] = [
                    'id'  => $l_data['isys_obj_type__id'],
                    'sel' => (in_array($l_data['isys_obj_type__id'], $l_config['object-type'] ?: [])),
                    'val' => $l_data['LC_isys_obj_type__title']
                ];
            } // if
        } // foreach

        return $l_return;
    } // function

    /**
     * Retrieves the prepared array data for the "cmdb status" dialog list.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_cmdb_status_filter_data($p_id = null)
    {
        $l_return = $l_config = [];

        if ($p_id !== null)
        {
            $l_config = $this->get_data($p_id);
            $l_config = $l_config['formatted__data'];
        } // if

        $l_cmdb_status_res = isys_cmdb_dao_status::instance($this->m_db)
            ->get_cmdb_status();

        if (count($l_cmdb_status_res))
        {
            while ($l_cmdb_status_row = $l_cmdb_status_res->get_row())
            {
                $l_return[] = [
                    'id'  => $l_cmdb_status_row['isys_cmdb_status__id'],
                    'sel' => (in_array($l_cmdb_status_row['isys_cmdb_status__id'], $l_config['cmdb-status'] ?: [])),
                    'val' => _L($l_cmdb_status_row['isys_cmdb_status__title'])
                ];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Retrieves the prepared array data for the "level" dialog.
     *
     * @return  array
     */
    public function get_level_filter_data()
    {
        $l_return = [];

        foreach (range(1, 10) as $l_level)
        {
            $l_return[$l_level] = $l_level;
        } // foreach

        return $l_return;
    } // function
} // class