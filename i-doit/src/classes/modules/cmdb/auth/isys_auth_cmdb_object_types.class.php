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
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_cmdb_object_types extends isys_auth_cmdb
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_cmdb_object_types
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_cmdb_object_types
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null)
        {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        } // if

        if (self::$m_instance === null)
        {
            self::$m_instance = new self;
        } // if

        return self::$m_instance;
    } // function

    /**
     * Protected method for combining "category" paths.
     *
     * @static
     *
     * @param   array $p_objtype_paths
     *
     * @return  array
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    protected static function combine_object_types(array &$p_objtype_paths)
    {
        // Prepare some variables.
        $l_return          = [];
        $l_keys            = [];
        $l_last_rights_num = 0;

        // Sort the parameters, so that the foreach will do its job correctly.
        isys_auth::sort_paths_by_rights($p_objtype_paths);

        foreach ($p_objtype_paths as $l_key => $l_rights)
        {
            if ($l_key == self::WILDCHAR || $l_key == self::EMPTY_ID_PARAM)
            {
                $l_return[$l_key] = $l_rights;
                continue;
            } // if

            $l_rights_num = array_sum($l_rights);

            if ($l_last_rights_num == $l_rights_num)
            {
                $l_keys[] = $l_key;
            }
            else
            {
                if (count($l_keys))
                {
                    $l_return[implode(',', $l_keys)] = isys_helper::split_bitwise($l_last_rights_num);
                } // if

                $l_keys = [$l_key];
            } // if

            $l_last_rights_num = $l_rights_num;
        } // foreach

        if (count($l_keys))
        {
            $l_return[implode(',', $l_keys)] = isys_helper::split_bitwise($l_last_rights_num);
        } // if

        return $l_return;
    }

    /**
     * Gets all allowed object types
     *
     * @return array|bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_allowed_objecttypes()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return [];
        } // if

        $l_allowed_object_types_condition = isys_auth_cmdb_objects::instance()
            ->get_allowed_object_types_condition();

        if ($l_allowed_object_types_condition)
        {
            return $this->get_object_types_by_condition($l_allowed_object_types_condition);
        }

        return false;
    } // function

    /**
     * @param $p_condition
     *
     * @return array
     * @throws isys_exception_database
     */
    public function get_object_types_by_condition($p_condition)
    {
        $l_return = [];

        if ($p_condition)
        {
            $l_dao = new isys_cmdb_dao_object_type(isys_application::instance()->database);

            $l_otypes = $l_dao->retrieve(
                "SELECT isys_obj_type__id FROM isys_obj_type WHERE (TRUE $p_condition);"
            );
            while ($l_row = $l_otypes->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW))
            {
                $l_return[$l_row[0]] = $l_row[0];
            }
        }

        return $l_return;
    } // function

    /**
     * This method gets all allowed object type groups
     *
     * @global isys_component_database $g_comp_database
     * @return array|bool|mixed
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_allowed_objtype_groups()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        /** @var isys_component_database $g_comp_database */
        global $g_comp_session, $g_comp_database;

        $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());
        $l_cache     = $l_cache_obj->get('allowed_objtype_groups');

        $l_return = false;

        if ($l_cache === false || (is_array($l_cache) && count($l_cache) == 0))
        {
            $l_allowed_objtypes = $this->get_allowed_objecttypes();
            $l_sql              = 'SELECT DISTINCT(isys_obj_type_group__const) FROM isys_obj_type_group INNER JOIN isys_obj_type ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id ';

            if (is_array($l_allowed_objtypes) && count($l_allowed_objtypes) > 0)
            {
                $l_sql .= 'WHERE isys_obj_type__id IN (' . implode(',', $l_allowed_objtypes) . ')';
            } // if

            $l_res = $g_comp_database->query($l_sql);

            if (count($g_comp_database->num_rows($l_res)) > 0)
            {
                while ($l_row = $g_comp_database->fetch_array($l_res))
                {
                    $l_return[] = $l_row['isys_obj_type_group__const'];
                } // while
            } // if

            try
            {
                $l_cache_obj->set('allowed_objtype_groups', $l_return)
                    ->save();
            }
            catch (isys_exception_cache $e)
            {
                isys_notify::warning($e->getMessage());
            }
        }
        else
        {
            $l_return = $l_cache;
        } // if
        return $l_return;
    } // function

    /**
     * Gets all object types for object type configuration list.
     *
     * @return  array|bool|mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.con>
     */
    public function get_allowed_objecttype_configs()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_session;

        $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());
        $l_cache     = $l_cache_obj->get('allowed_objtype_configs');

        $l_return = false;

        if ($l_cache === false || (is_array($l_cache) && count($l_cache) == 0))
        {

            $l_wildcard = false;
            // Get object types from object in type rights
            if (isset($this->m_paths['obj_type']))
            {
                if (isset($this->m_paths['obj_type'][isys_auth::WILDCHAR]))
                {
                    $l_wildcard = true;
                    $l_return   = true;
                }
                else
                {
                    if (!isset($this->m_paths['obj_type'][isys_auth::EMPTY_ID_PARAM]))
                    {
                        $l_return = [];
                        foreach ($this->m_paths['obj_type'] AS $l_key => $l_rights)
                        {
                            $l_key = strtoupper($l_key);
                            if (defined($l_key))
                            {
                                $l_key_constant            = constant($l_key);
                                $l_return[$l_key_constant] = $l_key_constant;
                            } // if
                        } // foreach
                    } // if
                } // if
            } // if

            if (!$l_wildcard)
            {
                $l_return = (count($l_return) > 0) ? $l_return : false;
            } // if

            try
            {
                $l_cache_obj->set('allowed_objtype_configs', $l_return)
                    ->save();
            }
            catch (isys_exception_cache $e)
            {
                isys_notify::warning($e->getMessage());
            }
        }
        else
        {
            $l_return = $l_cache;
        } // if
        return $l_return;
    } // function

    /**
     * Gets all object type groups for the object type configuration.
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.con>
     */
    public function get_allowed_objecttype_group_configs()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_database;

        $l_objecttypes = $this->get_allowed_objecttype_configs();

        if (is_array($l_objecttypes))
        {
            return isys_cmdb_dao::instance($g_comp_database)
                ->get_objtype_group_const_by_type_id($l_objecttypes);
        }
        else
        {
            if (is_bool($l_objecttypes))
            {
                return $l_objecttypes;
            } // if
        } // if

        return false;
    } // function

    /**
     * Checks if object type is allowed.
     *
     * @param   mixed $p_obj_type
     *
     * @return  boolean
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_allowed_in_objecttype($p_obj_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        if (!is_numeric($p_obj_type))
        {
            if (defined($p_obj_type))
            {
                $p_obj_type = constant($p_obj_type);
            }
            else
            {
                throw new isys_exception_general('Object type constant does not exist.');
            } // if
        } // if

        $l_objtypes = $this->get_allowed_objecttypes();

        if (is_array($l_objtypes))
        {
            if (count($l_objtypes) > 0)
            {
                return isset($l_objtypes[$p_obj_type]);
            }
            else
            {
                // WILDCARD
                return true;
            } // if
        }
        else
        {
            if ($l_objtypes === true)
            {
                return true;
            } // if
        } // if

        return false;
    } // function

    /**
     * Checks permission to see the object type
     *
     * @param $p_objecttype
     *
     * @throws isys_exception_auth
     */
    public function check_in_allowed_objecttypes($p_objecttype)
    {
        if (!$this->is_allowed_in_objecttype($p_objecttype))
        {
            throw new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_RIGHTS_TO_VIEW_OBJECT_LIST',
                    _L(
                        isys_factory_cmdb_dao::get_instance(
                            'isys_cmdb_dao',
                            self::$m_dao->get_database_component()
                        )
                            ->get_objtype_name_by_id_as_string($p_objecttype)
                    )
                )
            );
        } // if
    } // function
} // class