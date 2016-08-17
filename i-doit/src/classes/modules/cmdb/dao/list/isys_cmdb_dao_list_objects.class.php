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
 * List DAO parent - Will be used for all object types without an own list DAO.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects extends isys_cmdb_dao_list
{
    /**
     * Flag if the current object list is allowed to use group by
     *
     * @var bool
     */
    protected $m_allow_group_by = true;

    /**
     * Variable for the DAO result.
     *
     * @var  isys_component_dao_result
     */
    protected $m_dao_result;
    /**
     * Variable which holds the user-defined object type list (if defined).
     *
     * @var  array
     */
    protected $m_list_row = null;
    /**
     * Variable that defines if all rows shall be retrieved.
     *
     * @var  boolean
     */
    protected $m_load_all = false;
    /**
     * Variable which holds the current object-type row from isys_obj_type.
     *
     * @var  array
     */
    protected $m_object_type = [];
    /**
     * This variable defines how many pages of the list component shall be loaded with one request.
     *
     * @var  integer
     */
    protected $m_preload_pages = 30;
    /**
     * Variable which holds the current user ID.
     *
     * @var  integer
     */
    protected $m_user = 0;

    /**
     * Deactivates "GROUP BY obj_main.isys_obj__id"
     */
    public function deactivate_group_by()
    {
        $this->m_allow_group_by = false;
    } // functio

    /**
     * Find out, if the current list shall receive the "row-click" feature.
     *
     * @return  boolean
     */
    public function activate_row_click()
    {
        $l_user_config = $this->load_user_config();

        if ($l_user_config === null || !is_array($l_user_config))
        {
            return true;
        } // if

        return ($l_user_config['isys_obj_type_list__row_clickable'] == 1) ? true : false;
    } // function

    /**
     * Method for formating the results from the database and the dynamic callbacks.
     *
     * @param   isys_component_dao_result $p_result
     * @param                             array [$p_list_config array]
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function format_result(isys_component_dao_result $p_result, $p_list_config = [])
    {
        $l_array    = [];
        $i          = 0;
        $l_rowarray = [];

        // Get default object list config
        if (count($p_list_config) == 0 || !$p_list_config)
        {
            $p_list_config = isys_format_json::decode($this->get_default_list_config());
        }

        // We use this little trick to apply the ID as the first array element.
        $p_list_config   = array_reverse($p_list_config);
        $p_list_config[] = [
            C__PROPERTY_TYPE__STATIC,
            false,
            'isys_obj__id',
            '__id__',
            false,
            false
        ];
        $p_list_config   = array_reverse($p_list_config);

        while ($l_row = $p_result->get_row())
        {
            foreach ($p_list_config as $l_config)
            {
                list($l_property_type, $l_propkey, $l_rowfield, $l_title, $l_get_properties_method, $l_dynamic_callback, $l_cat_name, $l_custom_cat_const) = $l_config;
                $l_value = '';

                // Check which type of property we got.
                if ($l_property_type == C__PROPERTY_TYPE__STATIC)
                {
                    $l_property_callback = [
                        explode('::', $l_get_properties_method)[0],
                        'instance'
                    ];

                    if (is_callable($l_property_callback))
                    {
                        /**
                         * @var isys_cmdb_dao_category $l_instance
                         */
                        $l_instance = call_user_func($l_property_callback, $this->m_db);

                        if (defined($l_custom_cat_const) && method_exists($l_instance, 'set_catg_custom_id'))
                        {
                            $l_instance->set_catg_custom_id(constant($l_custom_cat_const));
                        }

                        if ($l_property = $l_instance->get_property_by_key($l_propkey))
                        {
                            if ($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DATE || $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DATETIME)
                            {
                                $l_row[$l_rowfield] = isys_locale::get_instance()
                                    ->fmt_date($l_row[$l_rowfield]);
                            } // if
                        } // if
                    } // if

                    // We look for fields with "__isys_obj_id" in the name, because we want to display objects instead of ID's.
                    if (strpos($l_rowfield, '__isys_obj__id') && is_numeric($l_row[$l_rowfield]))
                    {
                        $l_value = isys_cmdb_dao_category::dynamic_property_callback_object($l_row[$l_rowfield]);
                    }
                    else
                    {
                        $l_value = _L($l_row[$l_rowfield]);
                    } // if
                }
                else
                {
                    if (is_string($l_dynamic_callback[0]) && method_exists($l_dynamic_callback[0], 'instance'))
                    {
                        $l_dynamic_callback[0] = $l_dynamic_callback[0]::instance($this->get_database_component());
                    }

                    if (is_object($l_dynamic_callback[0]) && method_exists($l_dynamic_callback[0], $l_dynamic_callback[1]))
                    {
                        $l_value = call_user_func($l_dynamic_callback, $l_row);
                    } // if
                } // if

                if (empty($l_value))
                {
                    // Check again with other key
                    if (array_key_exists($l_title . '###' . $l_property_type, $l_row))
                    {
                        $l_value = _L($l_row[$l_title . '###' . $l_property_type]);
                    } // if
                } // if

                $l_rowarray[_L($l_title)] = $l_value;

                unset($l_value, $l_key);
            } // foreach

            $l_array[$i] = $l_rowarray;
            unset($l_rowarray);
            $i++;
        } // while

        return $l_array;
    } // function

    /**
     * Method for retrieving additional conditions to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_conditions()
    {
        return '';
    } // function

    /**
     * Method for retrieving additional joins to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_joins()
    {
        return '';
    } // function

    /**
     * Will return a isys_component_dao_result or null.
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_dao_result()
    {
        return $this->m_dao_result;
    } // function

    /**
     * Method for retrieving the default JSON encoded configuration array for all object types.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__DYNAMIC . ',"_id",false,"LC__CMDB__OBJTYPE__ID","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_id"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_title",false,"LC__UNIVERSAL__TITLE_LINK","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_title"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_location_path",false,"LC__CMDB__CATG__LOCATION_PATH","isys_cmdb_dao_category_g_location::get_dynamic_properties",["isys_cmdb_dao_category_g_location","dynamic_property_callback_location_path"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_created",false,"LC__TASK__DETAIL__WORKORDER__CREATION_DATE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_created"]],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_changed",false,"LC__CMDB__LAST_CHANGE","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_changed"]],' . '[' . C__PROPERTY_TYPE__STATIC . ',"purpose","isys_purpose__title","LC__CMDB__CATG__GLOBAL_PURPOSE","isys_cmdb_dao_category_g_global::get_properties",false],' . '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_cmdb_status",false,"LC__UNIVERSAL__CMDB_STATUS","isys_cmdb_dao_category_g_global::get_dynamic_properties",["isys_cmdb_dao_category_g_global","dynamic_property_callback_cmdb_status"]]]';
    } // function

    /**
     * Method for retrieving the default list query for all objects.
     *
     * @note    DS: isys_purpose Subquery needed for performance optimization
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function get_default_list_query()
    {
        return "SELECT
			obj_main.isys_obj__id,
			obj_main.isys_obj__title,
			obj_main.isys_obj__status,
			obj_main.isys_obj__created_by,
			obj_main.isys_obj__created,
			obj_main.isys_obj__updated_by,
			obj_main.isys_obj__updated,
			obj_main.isys_obj__isys_cmdb_status__id,
			obj_main.isys_obj__id AS '__id__',
			jn1.isys_purpose__title

			FROM isys_obj AS obj_main
			INNER JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id
			INNER JOIN isys_catg_global_list AS j2 ON j2.isys_catg_global_list__isys_obj__id = obj_main.isys_obj__id
			LEFT JOIN isys_purpose AS jn1 ON jn1.isys_purpose__id = j2.isys_catg_global_list__isys_purpose__id

			WHERE (obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']) . ")";
    } // function

    /**
     * Method for retrieving the JSON encoded configuration array for the current object type.
     *
     * @param   boolean $p_default
     *
     * @return  array
     */
    public function get_list_config($p_default = false)
    {
        $l_user_config = $this->load_user_config();

        if (!is_array($l_user_config) || $p_default)
        {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            $l_config = isys_tenantsettings::get('cmdb.default-object-list.config.' . $this->m_object_type['isys_obj_type__const'], $this->get_default_list_config());
        } // if
        else
        {
            $l_config = $l_user_config['isys_obj_type_list__config'];
        }

        // Fixing problematic config contents in cmdb.default-object-list.config because this could result in an empty list
        if (!is_string($l_config) || !isys_format_json::is_json($l_config))
        {
            $l_config = $this->get_default_list_config();
        }

        return isys_format_json::decode($l_config);
    } // function

    /**
     * This method will return the JSON encoded array, ready to be assigned to the TableOrderer component.
     *
     * @param   integer $p_offset May be an integer or false for no offset.
     *
     * @return  string JSON Array
     */
    public function get_list_data($p_offset = 0)
    {
        // Retrieve the SQL and JSON.
        $this->m_dao_result = $this->retrieve($this->get_list_query($p_offset));

        return isys_format_json::encode($this->format_result($this->m_dao_result, $this->get_list_config()));
    } // function

    /**
     * This method is used by an AJAX call to get more rows, once the defined limit (default 30 pages) is reached.
     *
     * @param   mixed $p_offset May be an integer or false to disable the offset.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_list_offset($p_offset = 0)
    {
        if ($this->m_load_all || $p_offset === false)
        {
            return '';
        } // if

        return 'LIMIT ' . (isys_glob_get_pagelimit() * $this->m_preload_pages * $p_offset) . ', ' . (isys_glob_get_pagelimit() * $this->m_preload_pages);
    } // function

    /**
     * This method will return a SQL query to select the desired data for the object type lists.
     *
     * @param   mixed   $p_offset May be an integer or false for no offset.
     * @param   integer $p_cRecStatus
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_list_query($p_offset = 0, $p_cRecStatus = null)
    {
        $l_obj_status = ((!empty($p_cRecStatus))) ? $p_cRecStatus : ($this->get_rec_status() ? $this->get_rec_status() : C__RECORD_STATUS__NORMAL);

        if (is_array($l_cmdb_status = $this->get_cmdb_status()))
        {
            if (is_numeric(array_search(C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE, $l_cmdb_status)))
            {
                $l_obj_status = C__RECORD_STATUS__TEMPLATE;
            } // if
        } // if

        $l_user_config = $this->load_user_config();

        if ($l_user_config === null || !is_array($l_user_config))
        {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            $l_return = isys_tenantsettings::get('cmdb.default-object-list.sql.' . $this->m_object_type['isys_obj_type__const'], $this->get_default_list_query());
        }
        else
        {
            $l_return = $l_user_config['isys_obj_type_list__query'];
        } // if

        // We need to squish our additional joins before the WHERE statement.
        $l_return = str_replace('WHERE', ' ' . $this->get_additional_joins() . ' WHERE', $l_return);

        $l_status_filter = $this->prepare_status_filter();

        if(strpos($l_status_filter, ' isys_obj__') !== false || strpos($l_status_filter, '(isys_obj__') !== false)
        {
            $l_status_filter = str_replace([' isys_obj__', '(isys_obj__'], [' obj_main.isys_obj__', ' obj_main.isys_obj__'], $l_status_filter);
        } // if

        $l_allowed_objects_condition = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();

        if($l_allowed_objects_condition != '' && strpos($l_allowed_objects_condition, 'obj_main') === false)
        {
            $l_allowed_objects_condition = substr_replace($l_allowed_objects_condition, 'obj_main.isys_obj__id', strpos($l_allowed_objects_condition, 'isys_obj__id'), strlen('isys_obj__id'));
        } // if

        // We apply some conditions (for selected status, my-doit and an offset).
        $l_query = $l_return . ' AND obj_main.isys_obj__status = ' . $this->convert_sql_int($l_obj_status) . ' ' . $l_allowed_objects_condition . ' ' . $this->get_additional_conditions() . ' ' . $l_status_filter  . ' ' . // GROUP BY is causing very slow response times and was substituted by "DISTINCT obj_main.isys_obj__id"
            ($this->m_allow_group_by ? ' GROUP BY obj_main.isys_obj__id ' : '') . $this->get_list_sorting() . $this->get_list_offset($p_offset) . ';';

        return $l_query;
    } // function

    /**
     * Method for counting, how many objects this list instance is holding.
     *
     * @todo    Check, if the "new methode" works for everything.
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_object_count()
    {
        $l_obj_status = $this->get_rec_status();
        if (!$l_obj_status) $l_obj_status = C__RECORD_STATUS__NORMAL;

        if (is_array($l_cmdb_status = $this->get_cmdb_status()))
        {
            if (is_numeric(array_search(C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE, $l_cmdb_status)))
            {
                $l_obj_status = C__RECORD_STATUS__TEMPLATE;
            } // if
        } // if

        $l_status_filter = $this->prepare_status_filter();

        if(strpos($l_status_filter, ' isys_obj__') !== false || strpos($l_status_filter, '(isys_obj__') !== false)
        {
            $l_status_filter = str_replace([' isys_obj__', '(isys_obj__'], [' obj_main.isys_obj__', ' obj_main.isys_obj__'], $l_status_filter);
        } // if

        // New method for retrieving the "object count".
        $l_sql = 'SELECT COUNT(*) AS count
			FROM isys_obj AS obj_main
			' . $this->get_additional_joins() . '
			WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']) . '
			AND obj_main.isys_obj__status = ' . $this->convert_sql_int($l_obj_status) . '
			' . isys_auth_cmdb_objects::instance()
                ->get_allowed_objects_condition() . '
			' . $this->get_additional_conditions() . '
			' . $l_status_filter . ';';

        return (int) $this->retrieve($l_sql)
            ->get_row_value('count');
    } // function

    /**
     * Method for retrieving the number of pages to preload.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_preload_pages()
    {
        return $this->m_preload_pages;
    } // function

    /**
     * This method sets the object type of this instance.
     *
     * @param   mixed $p_object_type May be an ID (integer) or the row from isys_obj_type (array).
     *
     * @return  isys_cmdb_dao_list_objects
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_object_type($p_object_type)
    {
        if (is_array($p_object_type))
        {
            $this->m_object_type = $p_object_type;
        }
        else if (is_numeric($p_object_type))
        {
            $l_dao               = new isys_cmdb_dao($this->get_database_component());
            $this->m_object_type = $l_dao->get_objtype($p_object_type)
                ->get_row();
        } // if

        return $this;
    } // function

    /**
     * This method gets the default sorting id.
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_default_sorting()
    {
        $l_user_config = $this->load_user_config();

        if ($l_user_config === null || !is_array($l_user_config))
        {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            return false;
        } // if

        return (empty($l_user_config['isys_obj_type_list__isys_property_2_cat__id'])) ? false : $l_user_config['isys_obj_type_list__isys_property_2_cat__id'];
    } // function

    /**
     * This method gets the property title by the given id
     *
     * @return null|string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_default_sorting_title()
    {
        $l_user_config = $this->load_user_config();

        if ($l_user_config === null || !is_array($l_user_config))
        {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            return null;
        } // if

        return (empty($l_user_config['isys_property_2_cat__prop_title'])) ? null : $l_user_config['isys_property_2_cat__prop_title'];
    } // function

    /**
     * This method gets the sorting direction
     *
     * @return null|string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_sorting_direction()
    {
        $l_user_config = $this->load_user_config();

        if ($l_user_config === null || !is_array($l_user_config))
        {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            return null;
        } // if

        return (empty($l_user_config['isys_obj_type_list__sorting_direction'])) ? null : $l_user_config['isys_obj_type_list__sorting_direction'];
    }

    /**
     * Method which sets the order by part of the sql statement for the object list
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_list_sorting()
    {
        $l_return = '';

        if (($l_prop_id = $this->get_default_sorting()) && !empty($this->m_list_row))
        {
            $l_cat_dao    = $this->get_dao_category()
                ->get_dao_instance($this->m_list_row['class'], ($this->m_list_row['catg_custom'] ?: null));
            $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
            $l_property   = $l_properties[$this->m_list_row['isys_property_2_cat__prop_key']];

            if ($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] && ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE || $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__TEXT || (($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus')) && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]))))
            {
                if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG || ((isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus')))
                {
                    $l_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                }
                elseif (($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATE && $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST]))
                {
                    $l_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                }
                else
                {
                    if (!isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType']) && !isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && !isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                    {
                        $l_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                    }
                }
            }
            elseif ($this->m_list_row['isys_property_2_cat__cat_const'] == 'C__CATG__GLOBAL')
            {
                // Extra for the global category of an object
                if ($this->m_list_row['isys_property_2_cat__prop_key'] == '_id')
                {
                    $l_field = 'isys_obj__id';
                }
                elseif ($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] && isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && is_object(
                        $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                    )
                )
                {
                    $l_prop_obj     = $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                    $l_props        = $l_prop_obj->get_properties();
                    $l_current_prop = null;
                    foreach ($l_props AS $l_key => $l_value)
                    {
                        if (strstr($this->m_list_row['isys_property_2_cat__prop_key'], $l_key))
                        {
                            $l_current_prop = $l_value;
                            continue;
                        }
                    }
                    if (!empty($l_current_prop))
                    {
                        if (isset($l_current_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                        {
                            $l_field = $l_current_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                        }
                        else
                        {
                            $l_field = $l_current_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                        }
                    }
                }
            }
            if (isset($l_field))
            {
                if ($this->m_list_row['isys_obj_type_list__sorting_direction'] == 'desc')
                {
                    $l_return = ' ORDER BY ' . $l_field . ' DESC ';
                }
                else
                {
                    $l_return = ' ORDER BY ' . $l_field . ' ASC ';
                }
            }
        }
        else
        {
            $l_return = ' ORDER BY obj_main.isys_obj__title ASC ';
        }

        return $l_return;
    }

    /**
     * Count objects of a specific type in several statuses
     *
     * @desc Overwrite this for special count Handling
     * @return array Counts of several Status
     */
    public function get_rec_counts()
    {
        // Build SQL-Statement
        $l_sql = 'SELECT  SUM(isys_obj__status = ' . C__RECORD_STATUS__NORMAL . ') AS COUNT_NORMAL,
                          SUM(isys_obj__status = ' . C__RECORD_STATUS__ARCHIVED . ') AS COUNT_ARCHIVED,
                          SUM(isys_obj__status = ' . C__RECORD_STATUS__DELETED . ') AS COUNT_DELETED ';

        // Add status C__TEMPLATE__STATUS if defined
        if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1)
        {
            $l_sql .= ', SUM(isys_obj__status = ' . C__RECORD_STATUS__TEMPLATE . ') AS COUNT_TEMPLATE ';
        } // if

        $l_sql .= 'FROM `isys_obj` WHERE `isys_obj__isys_obj_type__id` = ' . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']);

        // Retrieve results
        $l_row = $this->retrieve($l_sql)
            ->get_row();

        // Build array
        $l_array = [
            C__RECORD_STATUS__NORMAL   => ($l_row['COUNT_NORMAL'] > 0) ? $l_row['COUNT_NORMAL'] : 0,
            C__RECORD_STATUS__ARCHIVED => ($l_row['COUNT_ARCHIVED'] > 0) ? $l_row['COUNT_ARCHIVED'] : 0,
            C__RECORD_STATUS__DELETED  => ($l_row['COUNT_DELETED'] > 0) ? $l_row['COUNT_DELETED'] : 0,
        ];

        // Add C__STATUS__TEMPLATE
        if (isset($l_row['COUNT_TEMPLATE']))
        {
            $l_array[C__RECORD_STATUS__TEMPLATE] = $l_row['COUNT_TEMPLATE'];
        } // if

        return $l_array;
    }

    /**
     *
     * @param   boolean $p_load_all
     *
     * @return  isys_cmdb_dao_list_objects
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_load_all($p_load_all = false)
    {
        $this->m_load_all = $p_load_all;

        return $this;
    }

    /**
     * Method for finding a user defined list config.
     *
     * @return  mixed  Might be an array, if the user has defined an own list. If not: null.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_user_config()
    {
        if ($this->m_list_row === null)
        {
            // SQL for finding out, if a user has created an own list configuration.
            $l_sql = "SELECT isys_obj_type_list.*, " . "isys_property_2_cat__isysgui_catg_custom__id AS 'catg_custom', " . "isys_property_2_cat__prop_title, " . "isys_property_2_cat__prop_key, " . "isys_property_2_cat__cat_const, " . "(CASE WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__class_name " . "WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__class_name " . "WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN 'isysgui_catg_custom__class_name' END) AS 'class' " . "FROM isys_obj_type_list
				LEFT JOIN isys_property_2_cat ON isys_obj_type_list__isys_property_2_cat__id = isys_property_2_cat__id
				LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_property_2_cat__isysgui_catg__id
				LEFT JOIN isysgui_cats ON isysgui_cats__id = isys_property_2_cat__isysgui_cats__id
				LEFT JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_property_2_cat__isysgui_catg_custom__id
				WHERE isys_obj_type_list__isys_obj__id = " . $this->convert_sql_id($this->m_user) . "
				AND isys_obj_type_list__isys_obj_type__id = " . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']) . ";";

            $l_res = $this->retrieve($l_sql);

            if ($l_res->num_rows() > 0)
            {
                $this->m_list_row = $l_res->get_row();
            } // if
        } // if

        return $this->m_list_row;
    } // function

    /**
     * Constructor method to retrieve and save the current user ID.
     *
     * @global  isys_component_session  $g_comp_session
     *
     * @param   isys_component_database $p_db
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_db)
    {
        global $g_comp_session;

        // Set memory limit.
        if (($l_memlimit = isys_settings::get('system.memory-limit.object-lists', '768M')))
        {
            ini_set('memory_limit', $l_memlimit);
        } // if

        // Set the preload pages.
        $this->m_preload_pages = (int) isys_usersettings::get('gui.lists.preload-pages', 30);

        $this->m_user = (int) $g_comp_session->get_user_id();

        parent::__construct($p_db);
    } // function
} // class