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
 * Dialog Admin
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Dennis Stuecken <dstuecken@synetics.de
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_dialog_admin extends isys_cmdb_dao
{
    /**
     * Cache which contains the table fields of dialog tables
     *
     * @var array
     */
    private static $m_table_fields = [];

    /**
     * Relation addons.
     *
     * @param   integer $p_id
     * @param   string  $p_master
     * @param   string  $p_slave
     *
     * @return  boolean
     */
    public function mod_relation_type($p_id, $p_master, $p_slave)
    {
        $l_sql = 'UPDATE isys_relation_type SET ' . 'isys_relation_type__master = ' . $this->convert_sql_text(
                $p_master
            ) . ', ' . 'isys_relation_type__slave = ' . $this->convert_sql_text($p_slave) . ' ' . 'WHERE isys_relation_type__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Creates a new dialog entry.
     *
     * @param   string  $p_table
     * @param   string  $p_title
     * @param   integer $p_sort
     * @param   integer $p_const
     * @param   integer $p_status
     * @param   integer $p_parent_id
     * @param   string  $p_identifier
     *
     * @return  mixed
     */
    public function create($p_table, $p_title, $p_sort, $p_const, $p_status, $p_parent_id = null, $p_identifier = null, $p_description = '')
    {
        if (!empty($p_table))
        {
            $l_fields = $this->get_table_fields($p_table);

            $l_sql = 'INSERT INTO ' . $p_table . ' SET ' . $p_table . '__title = ' . $this->convert_sql_text(trim($p_title)) . ' ';

            if (!empty($p_const) && in_array($p_table . '__const', $l_fields))
            {
                $l_sql .= ',' . $p_table . '__const = ' . $this->convert_sql_text($p_const) . ' ';
            } // if

            if (!empty($p_sort) && in_array($p_table . '__sort', $l_fields))
            {
                $l_sql .= ',' . $p_table . '__sort = ' . $this->convert_sql_int($p_sort) . ' ';
            } // if

            if (!empty($p_description) && in_array($p_table . '__description', $l_fields))
            {
                $l_sql .= ',' . $p_table . '__description = ' . $this->convert_sql_text($p_description) . ' ';
            } // if

            if (in_array($p_table . '__status', $l_fields))
            {
                $l_sql .= ',' . $p_table . '__status = ' . $this->convert_sql_int($p_status) . ' ';
            } // if

            if (!empty($p_parent_id))
            {
                $l_parent_table = $this->get_parent_table($p_table);
                $l_sql .= ',' . $p_table . '__' . $l_parent_table . '__id = ' . $this->convert_sql_id($p_parent_id) . ' ';
            } // if

            if (!empty($p_identifier))
            {
                $l_sql .= ',' . $p_table . '__identifier = ' . $this->convert_sql_text($p_identifier);
            } // if

            if ($this->update($l_sql) && $this->apply_update())
            {
                return $this->get_last_insert_id();
            } // if
        } // if

        return false;
    } // function

    /**
     * Saves an existing dialog entry.
     *
     * @param   integer $p_id
     * @param   string  $p_table
     * @param   string  $p_title
     * @param   integer $p_sort
     * @param   integer $p_const
     * @param   integer $p_status
     * @param   integer $p_parent_id
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_id, $p_table, $p_title, $p_sort, $p_const, $p_status, $p_parent_id = null, $p_description = '')
    {
        if (!empty($p_table))
        {
            $l_fields = $this->get_table_fields($p_table);

            $l_sql = 'UPDATE ' . $p_table . ' SET ' . $p_table . '__title = ' . $this->convert_sql_text($p_title) . ', ' . $p_table . '__sort = ' . $this->convert_sql_int(
                    $p_sort
                ) . ', ';

            if (!empty($p_const))
            {
                $l_sql .= $p_table . '__const = ' . $this->convert_sql_text($p_const) . ', ';
            } // if

            if (!empty($p_description) && in_array($p_table . '__description', $l_fields))
            {
                $l_sql .= $p_table . '__description = ' . $this->convert_sql_text($p_description) . ', ';
            } // if

            $l_sql .= $p_table . '__status = ' . $this->convert_sql_int($p_status) . ' ';

            if (!empty($p_parent_id))
            {
                $l_parent_table = $this->get_parent_table($p_table);
                $l_sql .= ',' . $p_table . '__' . $l_parent_table . '__id = ' . $this->convert_sql_id($p_parent_id) . ' ';
            } // if

            $l_sql .= 'WHERE (' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . ');';

            return $this->update($l_sql) && $this->apply_update();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Deletes a dialog entry.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @throws  Exception
     * @return  boolean
     */
    public function delete($p_table, $p_id)
    {
        if ($this->check_delete($p_table, $p_id))
        {
            $l_sql = 'DELETE FROM ' . $p_table . ' WHERE ' . '(' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . ');';

            return ($this->update($l_sql) && $this->apply_update());
        }
        else
        {
            throw new Exception('Could not delete, because the constant is used for internal calculation.');
        } // if
    } // function

    /**
     * Get DialogEntry by title
     *
     * @param $p_table
     * @param $p_title
     *
     * @return isys_component_dao_result
     */
    public function get_by_title($p_table, $p_title)
    {
        $l_sql = 'SELECT * FROM ' . $p_table . ' ';

        if (($l_parent_table = $this->get_parent_table($p_table)))
        {
            $l_sql .= 'LEFT JOIN ' . $l_parent_table . ' ON ' . $p_table . '__' . $l_parent_table . '__id = ' . $l_parent_table . '__id ';
        }

        $l_sql .= ' WHERE TRUE';

        if (!empty($p_title))
        {
            $l_sql .= ' AND (' . $p_table . '__title = ' . $this->convert_sql_text($p_title) . ')';
        }

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Retrieve data from given table.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_table, $p_id = null, $p_condition = null)
    {
        $l_sql = 'SELECT *, IF(' . $p_table . '__const LIKE \'C__%\', \'' . _L('LC__UNIVERSAL__NO') . '\', \'' . _L(
                'LC__UNIVERSAL__YES'
            ) . '\') AS deleteable FROM ' . $p_table . ' ';

        if (($l_parent_table = $this->get_parent_table($p_table)))
        {
            $l_sql .= 'LEFT JOIN ' . $l_parent_table . ' ON ' . $p_table . '__' . $l_parent_table . '__id = ' . $l_parent_table . '__id ';
        } // if

        $l_sql .= ' WHERE TRUE';

        if (!empty($p_id))
        {
            $l_sql .= ' AND (' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . ')';
        } // if

        if ($p_condition)
        {
            $l_sql .= ' AND (' . $p_condition . ')';
        } // if

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Retrieve fields from table.
     *
     * @param   string $p_table
     *
     * @return  array
     */
    public function get_table_fields($p_table)
    {
        if (isset(self::$m_table_fields[$p_table])) return self::$m_table_fields[$p_table];

        $l_fields = [];
        $l_res    = $this->retrieve('SHOW FIELDS FROM ' . $p_table . ';');

        while ($l_row = $l_res->get_row())
        {
            $l_fields[] = $l_row['Field'];
        } // while
        self::$m_table_fields[$p_table] = $l_fields;

        return $l_fields;
    }

    /**
     * Get parent table if exists.
     *
     * @param   string $p_table
     *
     * @return  mixed
     */
    public function get_parent_table($p_table)
    {
        $l_table_fields = $this->get_table_fields($p_table);

        foreach ($l_table_fields as $l_field)
        {
            $l_field_arr = explode('__isys_', $l_field);

            if (count($l_field_arr) > 1)
            {
                $l_parent_table = 'isys_' . substr($l_field_arr[1], 0, strpos($l_field_arr[1], '__id'));

                return $l_parent_table;
            } // if
        } // foreach

        return false;
    } // function

    /**
     * Get all custom fields of type `dialog`.
     */
    public function get_custom_dialogs()
    {
        $l_res = $this->retrieve('SELECT isysgui_catg_custom__config FROM isysgui_catg_custom WHERE TRUE;');

        $l_custom_catg = [];

        if (count($l_res) > 0)
        {
            while (($l_row = $l_res->get_row()))
            {
                $l_config = unserialize($l_row['isysgui_catg_custom__config']);
                if (is_array($l_config) && count($l_config) > 0)
                {
                    foreach ($l_config as $l_field)
                    {
                        if ($l_field['type'] == 'f_popup' && $l_field['popup'] == 'dialog_plus')
                        {
                            $l_custom_catg[] = [
                                'title'      => $l_field['title'],
                                'identifier' => $l_field['identifier'],
                            ];
                        } // if
                    } // foreach
                } // if
            } // while
        } // if

        return $l_custom_catg;
    } // function

    /**
     *
     * @param   string $p_identifier
     *
     * @return  isys_component_dao_result
     */
    public function get_custom_dialog_data($p_identifier = null)
    {
        if (!empty($p_identifier))
        {
            return $this->retrieve('SELECT * FROM isys_dialog_plus_custom WHERE isys_dialog_plus_custom__identifier = ' . $this->convert_sql_text($p_identifier) . ';');
        } // if

        return false;
    } // function

    /**
     * Cache dialog table content
     *
     * @var array
     */
    private static $m_table_content = [];

    /**
     * @param string $p_table
     * @param string $p_value
     * @param string $p_identifier
     * @param bool   $p_partial_search
     *
     * @return int|mixed|string
     */
    public function get_id($p_table, $p_value, $p_identifier = null, $p_partial_search = true)
    {
        if (isset($p_table))
        {
            if(!isset(self::$m_table_content[$p_table]))
            {
                /* Retrieve dialog data */
                if (empty($p_identifier)) {
                    $l_res = $this->get_data($p_table);
                } else {
                    $l_res = $this->get_custom_dialog_data($p_identifier);
                } // if

                if ($l_res->num_rows()) {
                    // Get dialog table data
                    while ($l_row = $l_res->get_row()) {
                        self::$m_table_content[$p_table][$l_row[$p_table . "__id"]] = _L($l_row[$p_table . "__title"]);
                    } // while
                } // if
            } // if

            if(!($l_id = array_search($p_value, self::$m_table_content[$p_table] ?: [])) && isset(self::$m_table_content[$p_table][$p_value]))
            {
                $l_id = $p_value;
            } // if

            // Start partial search
            if (!$l_id && $p_partial_search)
            {
                foreach (self::$m_table_content[$p_table] AS $l_table_id => $l_table_title)
                {
                    if (is_numeric($p_value) && $p_value == $l_table_id)
                    {
                        $l_id = $l_table_id;
                        break;
                    }
                    elseif ($p_value && stristr($l_table_title, $p_value))
                    {
                        $l_id = $l_table_id;
                        break;
                    } // if
                } // foreach
            } // if

            if ($l_id)
            {
                return $l_id;
            } // if

            $l_id = $this->create(
                $p_table,
                $p_value,
                50,
                null,
                C__RECORD_STATUS__NORMAL,
                null,
                $p_identifier
            );
            self::$m_table_content[$p_table][$l_id] = $p_value;

            return $l_id;
        }
        else
        {
            return $p_value;
        } // if
    } // function

    /**
     * Check if a entry may be deleted.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  Exception
     */
    private function check_delete($p_table, $p_id)
    {
        $l_strConst = "";

        // Check if entry is allowed to be deleted.
        $l_sql = 'SELECT ' . $p_table . '__const ' . 'FROM ' . $p_table . ' ' . 'WHERE ' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . '; ';

        $l_ret = $this->retrieve($l_sql);

        if ($l_ret->num_rows() > 0)
        {
            $l_row      = $l_ret->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW);
            $l_strConst = $l_row[0];
        } // if

        if (strpos($l_strConst, 'C__') === 0)
        {
            throw new Exception('Could not delete dialog entry: Entries containing constants are mandatory for i-doit.');
        } // if

        return true;
    } // function
} // class