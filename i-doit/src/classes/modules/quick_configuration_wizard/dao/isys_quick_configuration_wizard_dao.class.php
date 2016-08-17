<?php

/**
 * i-doit
 *
 * Quick configuration wizard module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_quick_configuration_wizard_dao extends isys_module_dao
{
    /**
     * Variable for storing categories, which shall not be displayed.
     *
     * @var     array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private static $m_skipped_categories = [
        'C__CATG__OVERVIEW',
        'C__CATG__CUSTOM_FIELDS'
    ];
    /**
     * Variable for storing objecttypes, which shall not be displayed.
     *
     * @var     array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private static $m_skipped_objecttypes = [
        'C__OBJTYPE__GENERIC_TEMPLATE',
        'C__OBJTYPE__LOCATION_GENERIC',
        'C__OBJTYPE__RELATION',
        'C__OBJTYPE__CONTAINER',
        'C__OBJTYPE__PARALLEL_RELATION',
        'C__OBJTYPE__SOA_STACK'
    ];
    /**
     * Cache custom categories assignment
     *
     * @var array
     */
    public $m_assigned_custom_categories = [];
    /**
     * Cache all custom categories
     *
     * @var array
     */
    public $m_custom_categories = [];
    /**
     * Data cache.
     *
     * @var  array
     */
    protected $m_cache;

    /**
     * Static getter for a member variable.
     *
     * @static
     * @return  array
     */
    public static function get_skipped_objecttypes()
    {
        return self::$m_skipped_objecttypes;
    }

    /**
     * This method adds a new objecttype to the system.
     *
     * @param   $p_objtype_title
     * @param   $p_objtype_group
     * @param   $p_is_container
     * @param   $p_is_insertion
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function add_selfdefined_objtype($p_objtype_title, $p_objtype_group, $p_is_container, $p_is_insertion)
    {

        $l_objtype_group_query = 'SELECT isys_obj_type_group__id FROM isys_obj_type_group ' . 'WHERE isys_obj_type_group__const = \'' . $p_objtype_group . '\'';

        $l_result = $this->m_db->query($l_objtype_group_query);

        if ($l_result)
        {
            $l_objtype_group_id = array_shift($this->m_db->fetch_row($l_result));

            $l_objtype_const = "C__OBJECT_TYPE__" . time();

            $l_strSQL = "INSERT INTO isys_obj_type " . "(" . "isys_obj_type__isys_obj_type_group__id, " . "isys_obj_type__title, " . "isys_obj_type__selfdefined, " . "isys_obj_type__const, " . "isys_obj_type__status, " . "isys_obj_type__container, " . "isys_obj_type__show_in_rack, " . "isys_obj_type__show_in_tree " . ")" . " VALUES " . "(" . "'" . $l_objtype_group_id . "', " . "'" . $p_objtype_title . "', " . "1, " . "'" . $l_objtype_const . "', " . ((defined(
                    'C__RECORD_STATUS__NORMAL'
                )) ? C__RECORD_STATUS__NORMAL : "2") . ", " . $p_is_container . "," . $p_is_insertion . "," . "1" . ")";

            $l_result = $this->m_db->query($l_strSQL);
            if ($l_result)
            {
                return $l_objtype_const;
            } // if
        } // if
        return false;
    } // function

    /**
     * This method saves the assigned objecttypes for the selected objecttype group
     *
     * @param                         $p_objtype_group
     * @param                         $p_objecttypes
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_objecttype_group($p_objtype_group, $p_objecttypes)
    {

        $l_objtype_update = '(';

        $l_update = 'UPDATE isys_obj_type SET ' . 'isys_obj_type__isys_obj_type_group__id = ' . '(SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__const = \'' . $p_objtype_group . '\'), ' . 'isys_obj_type__show_in_tree = 1 ' . 'WHERE isys_obj_type__const IN ';

        if (!is_array($p_objecttypes)) $l_objecttypes = explode(',', $p_objecttypes);
        else $l_objecttypes = $p_objecttypes;

        if (count($l_objecttypes) > 0)
        {
            foreach ($l_objecttypes AS $l_objtype_const)
            {
                $l_objtype_update .= "'" . $l_objtype_const . "',";
            }
            $l_objtype_update = rtrim($l_objtype_update, ',') . ')';
            $l_update .= $l_objtype_update;

            return $this->m_db->query($l_update);
        }
        else
        {
            return false;
        }
    }

    /**
     * This method deactivates the objecttype
     *
     * @param                         $p_objecttypes
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function disable_objecttypes($p_objecttypes)
    {

        $l_objtype_update = '(';

        $l_update = 'UPDATE isys_obj_type SET ' . 'isys_obj_type__show_in_tree = 0, ' . 'isys_obj_type__isys_obj_type_group__id = NULL ' . 'WHERE isys_obj_type__const IN ';

        if (!is_array($p_objecttypes)) $l_objecttypes = explode(',', $p_objecttypes);
        else $l_objecttypes = $p_objecttypes;

        if (count($l_objecttypes) > 0)
        {
            foreach ($l_objecttypes AS $l_objtype_const)
            {
                $l_objtype_update .= "'" . $l_objtype_const . "',";
            }
            $l_objtype_update = rtrim($l_objtype_update, ',') . ')';
            $l_update .= $l_objtype_update;

            return $this->m_db->query($l_update);
        }
        else
        {
            return false;
        }
    }

    /**
     * This method saves the assigned categories of the selected objecttype
     *
     * @param                         $p_objtype_id
     * @param                         $p_categories
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_objecttype_categories($p_objtype_id, $p_categories)
    {

        $l_objtype_id_query = 'SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = \'' . $p_objtype_id . '\'';
        $l_result           = $this->m_db->query($l_objtype_id_query);
        if ($l_result)
        {
            $l_objtype_id = array_shift($this->m_db->fetch_row($l_result));

            $l_delete_assignment = 'DELETE FROM isys_obj_type_2_isysgui_catg WHERE ' . 'isys_obj_type_2_isysgui_catg__isys_obj_type__id = \'' . $l_objtype_id . '\'';
            $l_result            = $this->m_db->query($l_delete_assignment);

            if ($l_result)
            {

                if (!is_array($p_categories)) $l_categories = explode(',', $p_categories);
                else $l_categories = $p_categories;

                $l_insert = 'INSERT INTO isys_obj_type_2_isysgui_catg ' . '(isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id) VALUES ';

                foreach ($l_categories AS $l_category_const)
                {
                    $l_insert .= "('" . $l_objtype_id . "', (SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__const = '" . $l_category_const . "')),";
                }

                $l_insert = rtrim($l_insert, ',');
                //$l_insert .= "('".$l_objtype_id."', (SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__const = 'C__CATG__GLOBAL')),";
                //$l_insert .= "('".$l_objtype_id."', (SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__const = 'C__CATG__LOGBOOK'))";

                $l_result = $this->m_db->query($l_insert);

                return $l_result;
            } // if
        } // if
        return false;
    } // function

    /**
     * This method gets all category-constants for the specified objecttype.
     *
     * @param   string $p_objtype
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function load_assigned_categories($p_objtype)
    {
        $l_sql = 'SELECT isysgui_catg__const FROM isys_obj_type_2_isysgui_catg
			INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj_type_2_isysgui_catg__isys_obj_type__id
			WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_objtype) . ';';

        $l_return = [];

        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row())
        {
            $l_return[] = $l_row['isysgui_catg__const'];
        } // while

        $l_sql = 'SELECT isysgui_catg_custom.* FROM isys_obj_type_2_isysgui_catg_custom ' . 'INNER JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id ' . 'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id ' . 'WHERE isys_obj_type__const = ' . $this->convert_sql_text(
                $p_objtype
            ) . ';';

        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row())
        {
            if (!isset($this->m_custom_categories[$l_row['isysgui_catg_custom__const']]))
            {
                // Cache custom category
                $this->m_custom_categories[$l_row['isysgui_catg_custom__const']] = [
                    $l_row['isysgui_catg_custom__const'],
                    $l_row['isysgui_catg_custom__title'],
                    $l_row['isysgui_catg_custom__list_multi_value'],
                    $l_row['isysgui_catg_custom__config']
                ];
            }

            // Cache assignment.
            $this->m_assigned_custom_categories[$p_objtype][] = $l_row['isysgui_catg_custom__const'];

            // ID-2835 Bugfix
            $l_return[] = $l_row['isysgui_catg_custom__const'];
        } // while

        return $l_return;
    } // function

    /**
     * Caches the categories of the specified objecttype
     *
     * @param                         $p_objtype_id
     * @param                         $p_objtype_const
     * @param                         $p_caching
     *
     * @return array|bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function cache_objtype($p_objtype_id, $p_objtype_const, &$p_caching)
    {
        $l_return = [];
        $l_sql    = 'SELECT isysgui_catg__const
			FROM isys_obj_type_2_isysgui_catg
			INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
			WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . ';';

        $l_res = $this->retrieve($l_sql);
        while ($l_cat_const_arr = $l_res->get_row())
        {
            if (is_array($l_cat_const_arr))
            {
                $l_return[] = array_shift($l_cat_const_arr);
            } // if
        } // while

        if (count($l_return) > 0)
        {
            $p_caching->add($p_objtype_const, $l_return);

            return $l_return;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Disables all objecttypes from the menu tree. Is used from the import.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function disable_all_objecttypes()
    {
        $l_sql = 'UPDATE isys_obj_type SET isys_obj_type__show_in_tree = 0 WHERE isys_obj_type__const NOT IN ("' . implode('", "', self::$m_skipped_objecttypes) . '")';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Assigns the specified categories to the specified objecttype.
     *
     * @param   integer $p_objtype_id
     * @param   array   $p_categories
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function assign_categories_to_objecttype($p_objtype_id, array $p_categories)
    {
        $l_values = [];
        foreach ($p_categories AS $l_category)
        {
            if ($p_objtype_id > 0)
            {
                if (!is_numeric($l_category))
                {
                    if (!defined($l_category)) continue;

                    $l_category = constant($l_category);
                } // if
                $l_values[] .= '(' . $this->convert_sql_id($p_objtype_id) . ', ' . $this->convert_sql_id($l_category) . ')';
            } // if
        } // foreach

        if (count($l_values) > 0)
        {
            $l_sql = 'INSERT INTO isys_obj_type_2_isysgui_catg (isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id) VALUES ' . implode(
                    ', ',
                    $l_values
                ) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        } // if
        return true;
    } // function

    /**
     * Gets the category id by constant name.
     *
     * @param   string $p_catg_const
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_catg_id($p_catg_const)
    {
        $l_res = $this->retrieve('SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__const = ' . $this->convert_sql_text($p_catg_const) . ';');
        if ($l_res->num_rows() > 0)
        {
            $l_data = $l_res->get_row();

            return $l_data['isysgui_catg__id'];
        }

        return false;
    } // function

    /**
     * Gets object type id by constant name.
     *
     * @param   string $p_objtype_const
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_objtype_id($p_objtype_const)
    {
        $l_res = $this->retrieve('SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_objtype_const) . ';');
        if ($l_res->num_rows() > 0)
        {
            $l_data = $l_res->get_row();

            return $l_data['isys_obj_type__id'];
        }

        return false;
    } // function

    /**
     * Gets object type group id by constant name
     *
     * @param   string $p_objtype_group_const
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_objtype_group_id($p_objtype_group_const)
    {
        $l_res = $this->retrieve(
            'SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__const = ' . $this->convert_sql_text($p_objtype_group_const) . ';'
        );
        if ($l_res->num_rows() > 0)
        {
            $l_data = $l_res->get_row();

            return $l_data['isys_obj_type_group__id'];
        }

        return false;
    } // function

    /**
     * Adds a new selfdefined object type.
     *
     * @param   array   $p_data
     * @param   boolean $p_is_verinice_installed
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function add_new_objtype(array $p_data, $p_is_verinice_installed)
    {
        $l_insert = 'INSERT INTO isys_obj_type SET ';

        foreach ($p_data AS $l_key => $l_value)
        {
            if (($l_key == 'isys_obj_type__isys_verinice_types__id' && !$p_is_verinice_installed) || $l_key == 'isys_obj_type__id')
            {
                continue;
            } // if

            if ($l_key == 'isys_obj_type__isysgui_cats__id')
            {
                $l_insert .= $l_key . ' = (SELECT isysgui_cats__id FROM isysgui_cats WHERE isysgui_cats__const = \'' . $l_value . '\'),';
            }
            elseif ($l_key == 'isys_obj_type__icon' && empty($l_value))
            {
                $l_insert .= $l_key . ' = \'\',';
            }
            else
            {
                $l_insert .= $l_key . ' = ' . ((empty($l_value) && $l_value != '0') ? 'NULL' : '\'' . $l_value . '\'') . ',';
            } // if
        } // foreach

        $l_insert = rtrim($l_insert, ',');

        if ($this->update($l_insert) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return null;
    } // function

    /**
     * Adds a new selfdefined object type group.
     *
     * @param   array $p_data
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function add_new_objtype_group(array $p_data)
    {
        $l_insert = 'INSERT INTO isys_obj_type_group SET ';

        foreach ($p_data AS $l_key => $l_value)
        {
            $l_insert .= $l_key . ' = ' . ((empty($l_value) && $l_value != '0') ? 'NULL' : '\'' . $l_value . '\'') . ',';
        } // foreach

        $l_insert = rtrim($l_insert, ',');

        if ($this->update($l_insert) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return null;
    } // function

    /**
     * Checks if object type exists by constant name.
     *
     * @param   string $p_objtype_const
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function check_objecttype_by_const($p_objtype_const)
    {
        return (bool) $this->retrieve('SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_objtype_const) . ';')
            ->num_rows();
    } // function

    /**
     * Checks if the verinice module is installed.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function is_verinice_installed()
    {
        return (bool) $this->retrieve('SELECT isys_module__id FROM isys_module WHERE isys_module__const = "C__MODULE__VERINICE";')
            ->num_rows();
    } // function

    /**
     * Deletes object types
     *
     * @param                         $p_objtypes
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function delete_objtypes($p_objtypes)
    {

        $l_delete = 'DELETE FROM isys_obj_type WHERE isys_obj_type__const IN (';

        foreach ($p_objtypes AS $l_objtype_const)
        {
            $l_query = 'SELECT count(isys_obj__id) AS count FROM isys_obj INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE isys_obj_type__const = \'' . $l_objtype_const . '\'';
            $l_res   = $this->m_db->query($l_query);
            $l_count = array_shift($this->m_db->fetch_row($l_res));
            if ($l_count > 0)
            {
                return false;
            }
            $l_delete .= "'" . $l_objtype_const . "',";
        }
        $l_delete = rtrim($l_delete, ',') . ')';
        $this->m_db->query($l_delete);

        return true;
    }

    /**
     * Unused method.
     *
     * @return  array
     */
    public function get_data()
    {
        ;
    } // function

    /**
     * Method for saving the object-type group status.
     *
     * @param   string  $p_group_const
     * @param   boolean $p_active
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtypegroup_change_status($p_group_const, $p_active)
    {
        $l_sql = 'UPDATE isys_obj_type_group
			SET isys_obj_type_group__status = ' . $this->convert_sql_int(($p_active === true) ? C__RECORD_STATUS__NORMAL : C__RECORD_STATUS__BIRTH) . '
			WHERE isys_obj_type_group__const = ' . $this->convert_sql_text($p_group_const) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    public function category_change_status($p_obj_type_id, $p_cat_id, $p_active, $p_glob_catg = true)
    {
        if ($p_glob_catg)
        {
            if ($p_active === true)
            {
                $l_sql = 'INSERT INTO isys_obj_type_2_isysgui_catg SET
					isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id(constant($p_obj_type_id)) . ',
					isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_id(constant($p_cat_id)) . ';';
            }
            else
            {
                $l_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg
					WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id(constant($p_obj_type_id)) . '
					AND isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_id(constant($p_cat_id)) . ';';
            } // if
        }
        else
        {
            if ($p_active === true)
            {
                $l_sql = 'INSERT INTO isys_obj_type_2_isysgui_catg_custom SET
					isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = ' . $this->convert_sql_id(constant($p_obj_type_id)) . ',
					isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = ' . '(SELECT isysgui_catg_custom__id FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' . $this->convert_sql_text(
                        $p_cat_id
                    ) . ');';
            }
            else
            {
                $l_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg_custom
					WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = ' . $this->convert_sql_id(constant($p_obj_type_id)) . '
					AND isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = ' . '(SELECT isysgui_catg_custom__id FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' . $this->convert_sql_text(
                        $p_cat_id
                    ) . ');';
            } // if
        }

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for saving the new menu positions.
     *
     * @param   string $p_sorting_string
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtypegroup_change_sorting($p_sorting_string)
    {
        global $g_comp_session;

        $l_sorting = explode(',', $p_sorting_string);

        foreach ($l_sorting as $l_pos => $l_const)
        {
            $l_sql = 'UPDATE isys_obj_type_group
				SET isys_obj_type_group__sort = ' . $this->convert_sql_int($l_pos + 1) . '
				WHERE isys_obj_type_group__const = ' . $this->convert_sql_text($l_const) . ';';

            $this->update($l_sql);
        } // foreach

        // Remove the auth-cache, because the top navigation partially depends on it.
        isys_caching::factory('auth-' . $g_comp_session->get_user_id())
            ->clear();

        return $this->apply_update();
    } // function

    /**
     * Method for saving the group-name.
     *
     * @param  integer $p_id
     * @param  string  $p_title
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtypegroup_save($p_id, $p_title)
    {
        $l_sql = 'UPDATE isys_obj_type_group
			SET isys_obj_type_group__title = ' . $this->convert_sql_text($p_title) . '
			WHERE isys_obj_type_group__const = ' . $this->convert_sql_text($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for saving the edited object-type.
     *
     * @param   string  $p_id
     * @param   string  $p_title
     * @param   boolean $p_container
     * @param   boolean $p_insertion
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtype_save($p_id, $p_title, $p_container = false, $p_insertion = false)
    {
        $l_sql = 'UPDATE isys_obj_type
			SET isys_obj_type__title = ' . $this->convert_sql_text($p_title) . ',
			isys_obj_type__container = ' . (int) $p_container . ',
			isys_obj_type__show_in_rack = ' . (int) $p_insertion . '
			WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for deleting an object-type group.
     *
     * @param   string $p_group_const
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtypegroup_delete($p_group_const)
    {
        // Because of "foreign key constraints" we first remove all assigned object types.
        $l_sql = 'UPDATE isys_obj_type
				SET isys_obj_type__isys_obj_type_group__id = NULL,
				isys_obj_type__status = ' . $this->convert_sql_int(C__RECORD_STATUS__BIRTH) . ',
				isys_obj_type__show_in_tree = 0
				WHERE isys_obj_type__isys_obj_type_group__id = ' . $this->convert_sql_id(constant($p_group_const));
        $this->update($l_sql);

        // Now we remove the group itself.
        $l_sql = 'DELETE FROM isys_obj_type_group
			WHERE isys_obj_type_group__const = ' . $this->convert_sql_text($p_group_const) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for deleting an objet-type.
     *
     * @param  string $p_obj_type_const
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtype_delete($p_obj_type_const)
    {
        // Because of "foreign key constraints" we first remove all assigned categories.
        $l_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg
				WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id(constant($p_obj_type_const)) . ';';
        $this->update($l_sql);

        // Now we remove the object-type itself.
        $l_sql = 'DELETE FROM isys_obj_type
			WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_obj_type_const) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for creating an object-type group.
     *
     * @param   string $p_title
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtypegroup_new($p_title)
    {
        $l_objtype_group_constant = 'C__OBJTYPE_GROUP__SD_' . strtoupper(trim(isys_glob_replace_accent(isys_helper_textformat::clean_string($p_title))));

        $l_query = 'INSERT INTO isys_obj_type_group SET
			isys_obj_type_group__title = ' . $this->convert_sql_text($p_title) . ',
			isys_obj_type_group__const = ' . $this->convert_sql_text($l_objtype_group_constant) . ',
			isys_obj_type_group__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
			isys_obj_type_group__sort = 99;';

        $this->update($l_query);

        define($l_objtype_group_constant, $this->get_last_insert_id());

        /* Clear constant cache */
        isys_component_constant_manager::instance()
            ->clear_dcm_cache();

        return $l_objtype_group_constant;
    } // function

    /**
     * Method for saving the object-type status.
     *
     * @param   string  $p_group_constant
     * @param   string  $p_obj_type_constant
     * @param   boolean $p_active
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtype_change_status($p_group_constant, $p_obj_type_constant, $p_active)
    {
        if ($p_active === true)
        {
            $l_sql = 'UPDATE isys_obj_type
				SET isys_obj_type__isys_obj_type_group__id = ' . $this->convert_sql_id(constant($p_group_constant)) . ',
				isys_obj_type__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
				isys_obj_type__show_in_tree = 1
				WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_obj_type_constant) . ';';
        }
        else
        {
            $l_sql = 'UPDATE isys_obj_type
				SET isys_obj_type__isys_obj_type_group__id = NULL,
				isys_obj_type__status = ' . $this->convert_sql_int(C__RECORD_STATUS__BIRTH) . ',
				isys_obj_type__show_in_tree = 0
				WHERE isys_obj_type__const = ' . $this->convert_sql_text($p_obj_type_constant) . ';';
        } // if

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method for sorting the object-types by a given string ("C__OBJTYPE__LICENCE,C__OBJTYPE__PERSON, ...").
     *
     * @param   string $p_sorting_string
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function objtype_change_sorting($p_sorting_string)
    {
        $l_sorting = explode(',', $p_sorting_string);

        foreach ($l_sorting as $l_pos => $l_const)
        {
            $l_sql = 'UPDATE isys_obj_type
				SET isys_obj_type__sort = ' . $this->convert_sql_int($l_pos + 1) . '
				WHERE isys_obj_type__const = ' . $this->convert_sql_text($l_const) . ';';

            $this->update($l_sql);
        } // foreach

        return $this->apply_update();
    } // function

    public function objtype_new($p_title, $p_container, $p_insertion)
    {
        $l_objtype_constant = 'C__OBJECT_TYPE__' . time();

        $l_query = 'INSERT INTO isys_obj_type (
			isys_obj_type__isys_obj_type_group__id,
			isys_obj_type__title,
			isys_obj_type__selfdefined,
			isys_obj_type__const,
			isys_obj_type__status,
			isys_obj_type__sort,
			isys_obj_type__container,
			isys_obj_type__show_in_rack,
			isys_obj_type__show_in_tree
			) VALUES (
			NULL,
			' . $this->convert_sql_text($p_title) . ',
			1,
			' . $this->convert_sql_text($l_objtype_constant) . ',
			' . $this->convert_sql_int(C__RECORD_STATUS__BIRTH) . ',
			65535,
			' . (int) $p_container . ',
			' . (int) $p_insertion . ',
			0);';

        $this->update($l_query);

        // Now for assigning the standard categories ("global", "relations", "logbook", ...),
        $l_objtype = $this->get_last_insert_id();

        if ($l_objtype > 0)
        {
            $l_sql = 'INSERT INTO isys_obj_type_2_isysgui_catg (isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id)
				VALUES (' . $this->convert_sql_id($l_objtype) . ', ' . $this->convert_sql_id(C__CATG__GLOBAL) . '),
				(' . $this->convert_sql_id($l_objtype) . ', ' . $this->convert_sql_id(C__CATG__RELATION) . '),
				(' . $this->convert_sql_id($l_objtype) . ', ' . $this->convert_sql_id(C__CATG__LOGBOOK) . ');';
            $this->update($l_sql);
        }
        else
        {
            isys_notify::error(_L('LC__MODULE__QCW__OBJTYPES_MSG_NO_ID', [$p_title]), ['sticky' => true]);
        } // if

        define($l_objtype_constant, $l_objtype);

        /* Clear constant cache */
        isys_component_constant_manager::instance()
            ->clear_dcm_cache();

        return $l_objtype_constant;
    } // function

// LOAD METHODEN #######################################################################################################################################################################################

    /**
     * This method gets all global categories.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function load_categories()
    {
        $l_return = [];

        $l_res = $this->retrieve('SELECT * FROM isysgui_catg WHERE isysgui_catg__parent IS NULL');
        while ($l_row = $l_res->get_row())
        {
            if (in_array($l_row['isysgui_catg__const'], self::$m_skipped_categories) || !class_exists($l_row['isysgui_catg__class_name']))
            {
                continue;
            } // if

            $l_return[] = [
                'title'       => _L($l_row['isysgui_catg__title']),
                'const'       => _L($l_row['isysgui_catg__const']),
                'selfdefined' => false,
                'active'      => ($l_row['isysgui_catg__status'] == C__RECORD_STATUS__NORMAL) ? true : false
            ];
        } // while

        $l_res = $this->retrieve('SELECT * FROM isysgui_catg_custom');
        while ($l_row = $l_res->get_row())
        {
            $l_return[] = [
                'title'       => _L($l_row['isysgui_catg_custom__title']),
                'const'       => _L($l_row['isysgui_catg_custom__const']),
                'selfdefined' => true,
                'active'      => ($l_row['isysgui_catg_cutsom__status'] == C__RECORD_STATUS__NORMAL) ? true : false
            ];
        } // while

        /**
         * Sort array by title
         */
        usort(
            $l_return,
            'isys_glob_array_compare_title'
        );

        return $l_return;
    } // function

    /**
     * This method gets all objecttypes.
     *
     * @param   string  $p_obj_type_group
     * @param   boolean $p_full_row
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function load_objecttypes($p_obj_type_group = null, $p_full_row = false)
    {
        $l_return = [];

        $l_query = 'SELECT * FROM isys_obj_type
			LEFT JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id
			LEFT JOIN isysgui_cats ON isys_obj_type__isysgui_cats__id = isysgui_cats__id ';

        if ($p_obj_type_group !== null)
        {
            $l_query .= 'WHERE isys_obj_type_group__const = ' . $this->convert_sql_text($p_obj_type_group) . '
				AND isys_obj_type__show_in_tree = 1 ';
        } // if

        $l_query .= 'ORDER BY isys_obj_type__sort ASC;';

        $l_res = $this->m_db->query($l_query);
        while ($l_row = $this->m_db->fetch_row_assoc($l_res))
        {
            if (in_array($l_row['isys_obj_type__const'], self::$m_skipped_objecttypes))
            {
                continue;
            } // if

            if (!$p_full_row)
            {
                $l_return[] = [
                    'id'          => $l_row['isys_obj_type__const'],
                    'val'         => isys_glob_utf8_encode(_L($l_row['isys_obj_type__title'])),
                    'title'       => "'" . isys_glob_utf8_encode(_L($l_row['isys_obj_type__title'])) . "'",
                    'sel'         => (!empty($p_obj_type_group)) ? '1' : '0',
                    'group_title' => isys_glob_utf8_encode(_L($l_row['isys_obj_type_group__title']))
                ];
            }
            else
            {
                $l_return[] = $l_row;
            } // if
        } // while

        return $l_return;
    } // function

    /**
     * This method gets all objecttype groups.
     *
     * @param   boolean $p_full_row
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function load_objecttypes_group($p_full_row = false)
    {
        $l_res             = $this->retrieve('SELECT * FROM isys_obj_type_group ORDER BY isys_obj_type_group__sort');
        $l_obj_type_groups = [];

        while ($l_row = $l_res->get_row())
        {
            if (!$p_full_row)
            {
                $l_obj_type_groups[$l_row['isys_obj_type_group__const']] = _L($l_row['isys_obj_type_group__title']);
            }
            else
            {
                $l_obj_type_groups[] = $l_row;
            } // if
        } // while

        return $l_obj_type_groups;
    } // function
} // class
?>