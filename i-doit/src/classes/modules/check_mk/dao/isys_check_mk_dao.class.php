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
 * Check_MK DAO.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_check_mk_dao extends isys_module_dao
{
    /**
     * This variable will hold all the user-configured tags.
     *
     * @var  array
     */
    protected static $m_config_tags = null;

    /**
     * Method for returning the dynamic tag conditions.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_dynamic_tag_conditions()
    {
        return [
            C__MODULE__CMK__DYNAMIC_TAG__OBJECT_TYPE => _L('LC__MODULE__CHECK_MK__TAGS__CONDITION__NEW_OBJECTS_OF_TYPE'),
            C__MODULE__CMK__DYNAMIC_TAG__LOCATION    => _L('LC__MODULE__CHECK_MK__TAGS__CONDITION__LOCATION'),
            C__MODULE__CMK__DYNAMIC_TAG__PURPOSE     => _L('LC__MODULE__CHECK_MK__TAGS__CONDITION__PURPOSE')
        ];
    } // function

    /**
     * Method for returning the dynamic tag parameters.
     *
     * @static
     *
     * @param   integer $p_condition
     * @param   integer $p_count
     * @param   mixed   $p_selection
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_dynamic_tag_parameters($p_condition, $p_count, $p_selection = null)
    {
        global $g_comp_template, $g_comp_database;

        $g_comp_template->activate_editmode();

        switch ($p_condition)
        {
            default:
            case C__MODULE__CMK__DYNAMIC_TAG__OBJECT_TYPE:
                $l_smarty = new isys_smarty_plugin_f_dialog();

                $l_obj_type_data = [];
                $l_obj_type_res  = isys_cmdb_dao::instance($g_comp_database)
                    ->get_obj_type_by_catg(
                        [
                            C__CATG__CMK,
                            C__CATG__CMK_TAG
                        ]
                    );

                if (count($l_obj_type_res) > 0)
                {
                    while ($l_obj_type_row = $l_obj_type_res->get_row())
                    {
                        $l_obj_type_data[$l_obj_type_row['isys_obj_type__id']] = _L($l_obj_type_row['isys_obj_type__title']);
                    } // while
                } // if

                $l_params = [
                    'name'              => 'dynamic-tag-parameter-' . $p_count,
                    'p_arData'          => serialize($l_obj_type_data),
                    'p_strClass'        => 'normal',
                    'p_bInfoIconSpacer' => 0,
                    'p_strSelectedID'   => $p_selection,
                    'p_bDbFieldNN'      => true
                ];
                break;

            case C__MODULE__CMK__DYNAMIC_TAG__LOCATION:
                $l_smarty = new isys_smarty_plugin_f_popup();
                $l_params = [
                    'name'              => 'dynamic-tag-parameter-' . $p_count,
                    'p_strPopupType'    => 'browser_location',
                    'containers_only'   => true,
                    'p_strClass'        => 'normal',
                    'p_strSelectedID'   => $p_selection,
                    'p_bInfoIconSpacer' => 0,
                ];
                break;

            case C__MODULE__CMK__DYNAMIC_TAG__PURPOSE:
                $l_smarty = new isys_smarty_plugin_f_dialog();
                $l_params = [
                    'name'              => 'dynamic-tag-parameter-' . $p_count,
                    'p_strTable'        => 'isys_purpose',
                    'p_strClass'        => 'normal',
                    'p_strSelectedID'   => $p_selection,
                    'p_bInfoIconSpacer' => 0,
                    'p_bDbFieldNN'      => true
                ];
                break;
        } // switch

        return $l_smarty->navigation_edit($g_comp_template, $l_params);
    } // function

    /**
     * Method for retrieving the complete Check_MK configuration.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data()
    {
        return $this->retrieve('SELECT 1+1;');
    } // function

    /**
     * Method for retrieving the defined dynamic tags.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_dynamic_tag_data()
    {
        return $this->retrieve('SELECT * FROM isys_check_mk_dynamic_tags');
    } // function

    /**
     * Save the dynamic tag definition!
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_dynamic_tags()
    {
        $l_count   = $_POST['max_counter'];
        $l_queries = [];

        for ($i = 1;$i <= $l_count;$i++)
        {
            if (isset($_POST['dynamic-tag-condition-' . $i]))
            {
                $l_condition = $_POST['dynamic-tag-condition-' . $i];
                $l_parameter = $_POST['dynamic-tag-parameter-' . $i];
                $l_tags      = $_POST['dynamic-tag-taglist-' . $i . '__selected_values'];

                if (empty($l_condition) || empty($l_parameter) || empty($l_tags))
                {
                    continue;
                } // if

                if ($l_condition == C__MODULE__CMK__DYNAMIC_TAG__LOCATION)
                {
                    $l_parameter = $_POST['dynamic-tag-parameter-' . $i . '__HIDDEN'];
                } // if

                // We'd like to use constants wherever possible.
                if ($l_condition == C__MODULE__CMK__DYNAMIC_TAG__OBJECT_TYPE)
                {
                    $l_obj_type  = isys_cmdb_dao::instance($this->m_db)
                        ->get_object_type($l_parameter);
                    $l_parameter = $l_obj_type['isys_obj_type__const'];
                } // if

                // Converting the comma separated string to a JSON array.
                $l_tags = isys_format_json::encode(explode(',', $l_tags));

                $l_queries[] = 'INSERT INTO isys_check_mk_dynamic_tags SET
					isys_check_mk_dynamic_tags__condition = ' . $this->convert_sql_int($l_condition) . ',
					isys_check_mk_dynamic_tags__param = ' . $this->convert_sql_text($l_parameter) . ',
					isys_check_mk_dynamic_tags__tags = ' . $this->convert_sql_text($l_tags) . ';';
            } // if
        } // for

        if (count($l_queries) > 0)
        {
            // First we remove all old dynamic tags.
            $this->update('TRUNCATE TABLE isys_check_mk_dynamic_tags;');

            foreach ($l_queries as $l_query)
            {
                $this->update($l_query);
            } // foreach

            return $this->apply_update();
        } // if

        return true;
    } // function

    /**
     * Method for retrieving the user-configured tags as database result.
     *
     * @return  isys_component_dao_result
     */
    public function get_configured_tags_raw()
    {
        return $this->retrieve(
            'SELECT * FROM isys_check_mk_tags
			LEFT JOIN isys_check_mk_tag_groups ON isys_check_mk_tag_groups__id = isys_check_mk_tags__isys_check_mk_tag_groups__id
			ORDER BY isys_check_mk_tags__isys_check_mk_tag_groups__id ASC;'
        );
    } // function

    /**
     * Method for retrieving the user-configured tags as array.
     *
     * @param   integer $p_config_id
     *
     * @return  array
     */
    public function get_configured_tags($p_config_id = null)
    {
        if (self::$m_config_tags === null)
        {
            $l_res = $this->get_configured_tags_raw();

            if (count($l_res))
            {
                while ($l_row = $l_res->get_row())
                {
                    self::$m_config_tags[$l_row['isys_check_mk_tags__id']] = $l_row;
                } // while
            } // if
        } // if

        if ($p_config_id === null)
        {
            return self::$m_config_tags;
        } // if

        return self::$m_config_tags[$p_config_id] ?: [];
    } // function

    /**
     * Method for saving a user-configured tag.
     *
     * @param   integer $p_id
     * @param   array   $p_data
     *
     * @return  integer
     */
    public function save_data($p_id = null, $p_data = [])
    {
        $l_data   = [];
        $l_return = null;

        foreach ($p_data as $l_field => $l_value)
        {
            $l_data[] = $l_field . ' = ' . (is_numeric($l_value) ? $this->convert_sql_id($l_value) : $this->convert_sql_text($l_value));
        } // foreach

        if ($p_id > 0)
        {
            $l_sql = 'UPDATE isys_check_mk_tags SET ' . implode(', ', $l_data) . ' WHERE isys_check_mk_tags__id = ' . $this->convert_sql_id($p_id) . ';';
        }
        else
        {
            $l_sql = 'INSERT INTO isys_check_mk_tags SET ' . implode(', ', $l_data) . ';';
        } // if

        if ($this->update($l_sql))
        {
            isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));

            if ($p_id > 0)
            {
                $l_return = $p_id;
            }
            else
            {
                $l_return = $this->get_last_insert_id();
            } // if
        }
        else
        {
            isys_notify::error(_L('LC__INFOBOX__DATA_WAS_NOT_SAVED: ' . $this->m_db->get_last_error_as_string()), ['sticky' => true]);
        } // if

        return $l_return;
    } // function

    /**
     * Method for deleting one or more tag configurations.
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

        if ($this->update('DELETE FROM isys_check_mk_tags WHERE isys_check_mk_tags__id IN (' . implode(', ', array_map('intval', $p_id)) . ');') && $this->apply_update())
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
} // class