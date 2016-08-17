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
 * Custom Fields Module Dao
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_custom_fields_dao extends isys_component_dao
{
    /**
     * Deletes a custom field and its content.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete($p_id)
    {

        $l_config = $this->get_config($p_id);

        $l_sql = 'DELETE FROM isysgui_catg_custom WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id($p_id) . ';';

        /** @var $l_relation_dao isys_cmdb_dao_category_g_relation */
        $l_relation_dao       = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
        $l_has_relation_field = $l_relation_dao->has_relation_field('isys_catg_custom_fields_list');

        if ($l_has_relation_field)
        {
            // Delete Relations
            $l_sql_delete = 'SELECT isys_catg_relation_list__isys_obj__id
				FROM `isys_catg_custom_fields_list`
				INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = `isys_catg_custom_fields_list__isys_catg_relation_list__id`
				WHERE `isys_catg_custom_fields_list__isysgui_catg_custom__id` = ' . $this->convert_sql_id($p_id);
            $l_res        = $this->retrieve($l_sql_delete);

            while ($l_row = $l_res->get_row())
            {
                $l_relation_dao->delete_object_and_relations($l_row['isys_catg_relation_list__isys_obj__id']);
            } // while
        } // if

        if ($this->update($l_sql) && $this->apply_update())
        {
            /* Clear dialog content */
            if (is_array($l_config))
            {
                foreach ($l_config as $l_c)
                {
                    if ($l_c["type"] == "f_popup" && ($l_c["popup"] == "dialog" || $l_c["popup"] == "dialog_plus"))
                    {
                        $l_identifier = $l_c["identifier"];
                        $this->delete_dialog_content($l_identifier);
                    } // if
                } // foreach
            } // if

            $l_upd_prop = isys_factory::get_instance('isys_update_property_migration');

            $l_upd_prop->set_database($this->get_database_component())
                ->reset_property_table(C__CMDB__CATEGORY__TYPE_CUSTOM)
                ->collect_category_data(C__CMDB__CATEGORY__TYPE_CUSTOM)
                ->prepare_sql_queries('g_custom', false)
                ->execute_sql();

            return true;
        } // if

        return false;
    } // function

    /**
     * @param      $p_custom_category_id
     * @param null $p_object_id
     *
     * @return int
     */
    public function count_values($p_custom_category_id, $p_object_id = null)
    {
        $l_condition = '';
        if ($p_object_id)
        {
            $l_condition = ' AND isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);
        }

        return $this->retrieve(
            'SELECT COUNT(*) AS `c_count` FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id(
                $p_custom_category_id
            ) . $l_condition
        )
            ->get_row_value('c_count');
    }

    /**
     * @param      $p_custom_category_id
     * @param null $p_object_id
     *
     * @return int
     */
    public function count($p_custom_category_id, $p_object_id = null)
    {
        $l_condition = '';
        if ($p_object_id)
        {
            $l_condition = ' AND isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);
        }

        return $this->retrieve(
            'SELECT COUNT(DISTINCT isys_catg_custom_fields_list__isys_obj__id) AS `c_count` FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id(
                $p_custom_category_id
            ) . $l_condition
        )
            ->get_row_value('c_count');
    }

    /**
     * Delete contents of corresponding dialog identifier.
     *
     * @param   string $p_identifier
     *
     * @return  boolean
     */
    public function delete_dialog_content($p_identifier)
    {
        return $this->update(
            'DELETE FROM isys_dialog_plus_custom WHERE isys_dialog_plus_custom__identifier = ' . $this->convert_sql_text($p_identifier) . ';'
        ) && $this->apply_update();
    } // function

    /**
     * Get all custom categories
     *
     * @param   integer $p_id
     * @param   string  $p_title
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_id = null, $p_title = null)
    {
        $l_sql = 'SELECT * FROM isysgui_catg_custom WHERE TRUE';

        if ($p_id !== null)
        {
            if (is_numeric($p_id))
            {
                $l_sql .= ' AND isysgui_catg_custom__id = ' . $this->convert_sql_id($p_id);
            }
            else
            {
                $l_sql .= ' AND isysgui_catg_custom__const = ' . $this->convert_sql_text($p_id);
            } // if
        } // if

        if ($p_title)
        {
            $l_sql .= ' AND isysgui_catg_custom__title = ' . $this->convert_sql_text($p_title);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Validates given arguments, returns false if one argument is empty
     *
     * @return bool
     */
    public function validate()
    {
        $l_argv = func_get_args();

        foreach ($l_argv as $l_arg)
        {
            if ($l_arg == "-1" || $l_arg == "" || is_null($l_arg))
            {
                return false;
            } // if
        } // foreach
        return true;
    } // function

    /**
     * Validate category constant
     *
     * @param $p_category_constant
     *
     * @return bool
     */
    public function validate_category_constant($p_category_constant)
    {
        return $this->validate($p_category_constant) && (strlen($p_category_constant) > 1) && !preg_match('/[^A-Za-z_\w]/', $p_category_constant) && !defined(
            $p_category_constant
        );
    } // function

    /**
     * Extracts config and returns it
     *
     * @param int $p_id
     *
     * @return array
     */
    public function get_config($p_id)
    {
        $l_data = $this->get_data($p_id)
            ->get_row();

        return ($l_data ? unserialize($l_data['isysgui_catg_custom__config']) : null);
    } // function

    /**
     * Clears all custom categoriy to object type assignments.
     *
     * @param   integer $p_custom_id
     *
     * @return  bool
     */
    public function clear_assignments($p_custom_id)
    {
        $l_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg_custom
			WHERE isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_custom_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Get all custom category to object type assignments.
     *
     * @param   integer $p_isysgui_id
     * @param   integer $p_object_type
     *
     * @return  isys_component_dao_result
     */
    public function get_assignments($p_isysgui_id = null, $p_object_type = null, $p_overview_only = false)
    {
        $l_sql = 'SELECT * FROM isys_obj_type AS main ';

        if ($p_overview_only === true)
        {
            $l_sql .= ' INNER JOIN isys_obj_type_2_isysgui_catg_custom_overview AS oc ON oc.isys_obj_type__id = main.isys_obj_type__id ' . 'INNER JOIN isysgui_catg_custom AS cc ON oc.isysgui_catg_custom__id = cc.isysgui_catg_custom__id';
            $l_order_by = ' ORDER BY isys_obj_type_2_isysgui_catg_custom_overview__sort;';
        }
        else
        {
            $l_sql .= 'INNER JOIN isys_obj_type_2_isysgui_catg_custom AS oc ON oc.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = main.isys_obj_type__id ' . 'INNER JOIN isysgui_catg_custom AS cc ON oc.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = cc.isysgui_catg_custom__id';
            $l_order_by = ' ORDER BY cc.isysgui_catg_custom__sort;';
        } // if

        $l_sql .= ' WHERE TRUE ';

        if ($p_isysgui_id !== null)
        {
            $l_sql .= ' AND cc.isysgui_catg_custom__id = ' . $this->convert_sql_id($p_isysgui_id);
        } // if

        if ($p_object_type !== null)
        {
            $l_sql .= ' AND main.isys_obj_type__id = ' . $this->convert_sql_id($p_object_type);
        } // if

        return $this->retrieve($l_sql . $l_order_by);
    } // function

    /**
     * Assigns a custom category to an object type.
     *
     * @param   integer $p_isysgui_id
     * @param   integer $p_obj_type_id
     *
     * @return  boolean
     */
    public function assign($p_isysgui_id, $p_obj_type_id)
    {
        $l_sql = "DELETE FROM isys_obj_type_2_isysgui_catg_custom
			WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = " . $this->convert_sql_id($p_obj_type_id) . "
			AND isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = " . $this->convert_sql_id($p_isysgui_id) . ";";

        if (($l_ret = $this->update($l_sql)))
        {
            $l_sql = "INSERT INTO isys_obj_type_2_isysgui_catg_custom SET
   			    isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = " . $this->convert_sql_id($p_obj_type_id) . ",
   			    isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = " . $this->convert_sql_id($p_isysgui_id) . ";";

            $l_ret = ($this->update($l_sql) && $this->apply_update());
        } // if
        return $l_ret;
    } // function

    /**
     * Saves configuration of a custom category.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   array   $p_config
     * @param   integer $p_parent
     * @param   integer $p_sort
     * @param   integer $p_multivalued
     * @param   string  $p_constant
     *
     * @throws Exception
     * @return  boolean
     */
    public function save($p_id, $p_title, $p_config, $p_parent, $p_sort, $p_multivalued, $p_constant)
    {
        $l_valid_constant = true;

        // Needed by the auth system
        $p_constant = strtoupper($p_constant);

        // Get old constant of category
        $l_old_const = $this->get_data($p_id)
            ->get_row_value('isysgui_catg_custom__const');

        // Constant check for undefined and changed category constants only
        if (!(isset($p_constant) && defined($p_constant) && $l_old_const == $p_constant))
        {
            $l_valid_constant = $this->validate_category_constant($p_constant);
        } // if

        if ($this->validate($p_title) && $l_valid_constant)
        {
            $l_sql = 'UPDATE isysgui_catg_custom SET
	   		    isysgui_catg_custom__title = ' . $this->convert_sql_text($p_title) . ',
	   		    isysgui_catg_custom__type = ' . $this->convert_sql_int(isys_cmdb_dao_category::TYPE_EDIT) . ',
	   		    isysgui_catg_custom__parent = ' . $this->convert_sql_int($p_parent) . ',
	   		    isysgui_catg_custom__config = ' . $this->convert_sql_text(serialize($p_config)) . ',
	   		    isysgui_catg_custom__sort = ' . $this->convert_sql_int($p_sort) . ',
	   		    isysgui_catg_custom__const = ' . $this->convert_sql_text($p_constant) . ',
	   		    isysgui_catg_custom__list_multi_value = ' . $this->convert_sql_boolean($p_multivalued) . '
	   		    WHERE (isysgui_catg_custom__id = ' . $this->convert_sql_id($p_id) . ');';

            if ($this->update($l_sql) && $this->apply_update())
            {
                $l_config_keys = array_keys($p_config);

                /** @var $l_dao_relation isys_cmdb_dao_category_g_relation */
                $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);

                if ($l_dao_relation->has_relation_field('isys_catg_custom_fields_list'))
                {
                    $l_sql = "SELECT isys_catg_custom_fields_list__id, isys_catg_custom_fields_list__isys_catg_relation_list__id
						FROM isys_catg_custom_fields_list
						WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($p_id) . "
						AND isys_catg_custom_fields_list__field_type != 'commentary'
						AND isys_catg_custom_fields_list__field_key NOT IN ('" . rtrim(implode("','", $l_config_keys), "','") . "')";

                    $l_res = $this->retrieve($l_sql);

                    if ($l_res->num_rows() > 0)
                    {
                        $l_delete = $l_delete_rel = [];

                        while ($l_row = $l_res->get_row())
                        {
                            $l_delete[] = $l_row['isys_catg_custom_fields_list__id'];
                            if (!empty($l_row['isys_catg_custom_fields_list__isys_catg_relation_list__id']))
                            {
                                $l_delete_rel[] = $l_row['isys_catg_custom_fields_list__isys_catg_relation_list__id'];
                            } // if
                        } // while
                        $l_delete_query = 'DELETE FROM isys_catg_custom_fields_list ' . 'WHERE isys_catg_custom_fields_list__id IN (' . rtrim(
                                implode(',', $l_delete),
                                ','
                            ) . ')';
                        $this->update($l_delete_query) && $this->apply_update();

                        if (count($l_delete_rel) > 0)
                        {
                            // delete relation
                            foreach ($l_delete_rel AS $l_rel_id)
                            {
                                $l_dao_relation->delete_relation($l_rel_id);
                            } // foreach
                        } // if
                    } // if
                } // if

                /**
                 * @var $l_upd_prop isys_update_property_migration
                 */
                $l_upd_prop = isys_factory::get_instance('isys_update_property_migration');

                $l_upd_prop->set_database($this->get_database_component())
                    ->reset_property_table(C__CMDB__CATEGORY__TYPE_CUSTOM)
                    ->collect_category_data(C__CMDB__CATEGORY__TYPE_CUSTOM)
                    ->prepare_sql_queries('g_custom', false)
                    ->execute_sql();

                return true;
            }
            else
            {
                return false;
            } // if
        }
        else
        {
            throw new Exception(
                "Please make sure that category title is set " . "and category constant contains at least two characters and " . "includes only alphabetic character (a-z and A-Z) or numbers (0-9) and underscores (_)."
            );
        } // if
    } // function

    /**
     * Creates a custom category
     *
     * @param string $p_title
     * @param array  $p_config
     * @param int    $p_parent
     * @param int    $p_sort
     * @param        $p_multivalued
     * @param param string $p_constant
     *
     * @throws Exception
     *
     * @return mixed[int|false]
     */
    public function create($p_title, $p_config, $p_parent, $p_sort, $p_multivalued, $p_constant = null)
    {
        if ($p_constant === null || !$this->validate_category_constant($p_constant))
        {
            $p_constant = 'C__CATG__CUSTOM_FIELDS_' . time();
        } // if

        if ($this->validate($p_title))
        {
            $l_sql = 'INSERT INTO isysgui_catg_custom SET
	   		    isysgui_catg_custom__title = ' . $this->convert_sql_text($p_title) . ',
	   		    isysgui_catg_custom__type = ' . $this->convert_sql_int(isys_cmdb_dao_category::TYPE_EDIT) . ',
	   		    isysgui_catg_custom__parent = ' . $this->convert_sql_int($p_parent) . ',
	   		    isysgui_catg_custom__config = ' . $this->convert_sql_text(serialize($p_config)) . ',
	   		    isysgui_catg_custom__const = ' . $this->convert_sql_text($p_constant) . ',
	   		    isysgui_catg_custom__sort = ' . $this->convert_sql_int($p_sort) . ',
	   		    isysgui_catg_custom__status = ' . C__RECORD_STATUS__NORMAL . ',
	   		    isysgui_catg_custom__list_multi_value = ' . $this->convert_sql_boolean($p_multivalued) . ';';

            if ($this->update($l_sql) && $this->apply_update())
            {
                $l_last_id = $this->get_last_insert_id();

                $l_upd_prop = isys_factory::get_instance('isys_update_property_migration');

                $l_upd_prop->set_database($this->get_database_component())
                    ->reset_property_table(C__CMDB__CATEGORY__TYPE_CUSTOM)
                    ->collect_category_data(C__CMDB__CATEGORY__TYPE_CUSTOM)
                    ->prepare_sql_queries('g_custom', false)
                    ->execute_sql();

                return $l_last_id;
            }
            else
            {
                return false;
            } // if
        }
        else
        {
            throw new Exception("Not all required fields are filled.");
        } // if
    } // function
} // class
