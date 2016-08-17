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
class isys_auth_cmdb_objects extends isys_auth_cmdb
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_cmdb_objects
     */
    private static $m_instance = null;

    /**
     * @var string
     */
    private $m_allowed_object_types_condition = null;

    /**
     * @var string
     */
    private $m_allowed_objects_condition = null;

    /**
     * @param       $p_person_id
     * @param null  $p_module_id
     * @param array $p_paths
     *
     * @return isys_cache_keyvalue
     */
    public static function invalidate_cache($p_person_id, $p_module_id = null, $p_paths = [])
    {
        return isys_cache::keyvalue()
            ->ns($p_person_id)
            ->delete('auth.condition.allowed_objects')
            ->delete('auth.condition.allowed_object_types');
    }

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_cmdb_objects
     * @author  Selcuk Kekec <skekec@i-doit.com>
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
    }

    /**
     * Return SQL condition to identify all object types where the user has access rights to
     *
     * @return null|string
     */
    public function get_allowed_object_types_condition()
    {
        if ($this->m_allowed_object_types_condition === null) $this->parse();

        return $this->m_allowed_object_types_condition;
    }

    /**
     * Return SQL condition to isolate objects the user is allowed to see
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return string
     */
    public function get_allowed_objects_condition()
    {
        if ($this->m_allowed_objects_condition === null) $this->parse();

        return $this->m_allowed_objects_condition;
    } // function

    /**
     * Return all allowed objects as an array. Note that this function does not include objects.
     *
     * @deprecated  DON'T USE SINCE THIS FUNCTION COULD BE VERY SLOW AND EXCEEDS THE MAX_ALLOWED_PACKETS RESTRICTION
     *
     * @param   integer $p_type_filter
     *
     * @return  array
     * @throws  isys_exception_database
     * @author      Dennis Stücken <dstuecken@i-doit.de>
     */
    public function get_allowed_objects($p_type_filter = null)
    {
        // Check for inactive auth .
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $l_return = [];

        $l_cache_obj = isys_caching::factory('auth-' . isys_application::instance()->session->get_user_id());
        $l_cache     = $l_cache_obj->get('allowed_objects');

        if ($l_cache === false)
        {
            if ($p_type_filter)
            {
                $l_type_condition = ' AND (isys_obj__isys_obj_type__id = ' . $p_type_filter . ')';
            }
            else
            {
                $l_type_condition = '';
            } // if

            $l_objects = isys_cmdb_dao::factory(isys_application::instance()->database)
                ->retrieve('SELECT isys_obj__id AS id FROM isys_obj WHERE TRUE ' . $this->get_allowed_objects_condition() . $l_type_condition . ' ORDER BY isys_obj__id ASC;');

            while ($l_row = $l_objects->get_row())
            {
                $l_return[$l_row['id']] = $l_row['id'];
            } // while

            try
            {
                $l_cache_obj->set('allowed_objects', $l_return)
                    ->save();
            }
            catch (isys_exception_filesystem $e)
            {
                isys_notify::warning($e->getMessage());
            } // try
        } // if

        return $l_return;
    } // function

    /**
     * Prepare SQL conditions
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return boolean
     */
    private function parse()
    {
        $l_allowed_object_types = $l_allowed_objects = $l_conditions = [];

        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        /**
         * Get Caching instance in a user namespace
         */
        $l_cache_obj = isys_cache::keyvalue()
            ->ns(isys_application::instance()->session->get_user_id());

        $this->m_allowed_objects_condition      = $l_cache_obj->get('auth.condition.allowed_objects');
        $this->m_allowed_object_types_condition = $l_cache_obj->get('auth.condition.allowed_object_types');

        $l_object_type_wildcard = false;

        // Start evaluating if cache is not set
        if (!$this->m_allowed_objects_condition && !$this->m_allowed_object_types_condition)
        {
            if (isset($this->m_paths['obj_in_type']))
            {
                // Wildcard for all objects is greater than any objtype condition, so we don't need to handle any objtype conditions when the user has access to all objects anyway.
                if (!isset($this->m_paths['obj_in_type'][isys_auth::WILDCHAR]))
                {
                    if (!isset($this->m_paths['obj_in_type'][isys_auth::EMPTY_ID_PARAM]))
                    {
                        foreach ($this->m_paths['obj_in_type'] as $l_key => $l_rights)
                        {
                            $l_key = strtoupper($l_key);

                            if (defined($l_key))
                            {
                                $l_allowed_object_types[constant($l_key)] = constant($l_key);
                            } // if
                        } // foreach

                        if (count($l_allowed_object_types) > 0)
                        {
                            $l_conditions['objtype'] = '(isys_obj__id IN (SELECT isys_obj__id FROM isys_obj WHERE isys_obj__isys_obj_type__id IN (' . implode(
                                    ',',
                                    $l_allowed_object_types
                                ) . ')))';
                        } // if
                    } // if
                }
                else
                {
                    $this->m_allowed_object_types_condition = ' AND TRUE';
                    $l_object_type_wildcard                 = true;
                } // if
            } // if

            // Get object types from object id rights.
            if (isset($this->m_paths['obj_id']))
            {
                if (!isset($this->m_paths['obj_id'][isys_auth::WILDCHAR]))
                {
                    if (is_array($this->m_paths['obj_id']) && count($this->m_paths['obj_id']) > 0)
                    {
                        $l_tmp = $this->m_paths['obj_id'];
                        unset($l_tmp[isys_auth::EMPTY_ID_PARAM], $l_tmp[isys_auth::WILDCHAR]);
                        $l_allowed_objects = array_keys($l_tmp);
                        unset($l_tmp);
                    }
                } // if
                else
                {
                    $this->m_allowed_objects_condition = ' AND TRUE';
                } // if
            } // if

            // Search for objects based on a location path.
            if (isset($this->m_paths['location']) && !isset($this->m_paths['location'][isys_auth::WILDCHAR]) && !isset($this->m_paths['location'][isys_auth::EMPTY_ID_PARAM]))
            {
                // The given ID could not be found directly, now we check the location path.

                $l_dao_location = isys_cmdb_dao_location::factory(isys_application::instance()->database);

                foreach ($this->m_paths['location'] as $l_location_id => $l_rights)
                {
                    // Get child locations of the location auth-paths.
                    $l_child_locations = $l_dao_location->get_mptt()
                        ->get_children($l_location_id);

                    if (is_object($l_child_locations))
                    {
                        while ($l_row = $l_child_locations->get_row())
                        {
                            // Add right for this specific object.
                            $l_allowed_objects[] = $l_row['isys_catg_location_list__isys_obj__id'];

                            // Add right for object type, which was gained in consequence of the specific object right.
                            if (!isset($l_allowed_object_types[$l_row['isys_obj_type__id']]))
                            {
                                $l_allowed_object_types[$l_row['isys_obj_type__id']] = $l_row['isys_obj_type__id'];
                            } // if
                        } // while
                    } // if
                } // foreach
            } // if

            // Addup all collected object ids to l_conditions:objids
            if (count($l_allowed_objects) > 0 && !$l_object_type_wildcard)
            {
                $l_conditions['objids'] = 'isys_obj__id IN (' . implode(',', $l_allowed_objects) . ')';
            }
            // Clear the objects condition if all "object types allowed" right was set
            else if ($l_object_type_wildcard)
            {
                $this->m_allowed_objects_condition = '';
            }

            // Build condition.
            if (count($l_conditions) > 0)
            {
                $this->m_allowed_objects_condition = ' AND (' . implode(' OR ', $l_conditions) . ')';
            } // if

            if (count($l_allowed_object_types) > 0)
            {
                $this->m_allowed_object_types_condition = ' AND (isys_obj_type__id IN (' . implode(',', $l_allowed_object_types) . ')';

                if (isset($l_conditions['objids']))
                {
                    $this->m_allowed_object_types_condition .= ' OR isys_obj_type__id IN (SELECT isys_obj__isys_obj_type__id FROM isys_obj WHERE ' . $l_conditions['objids'] . ')';
                } // if

                $this->m_allowed_object_types_condition .= ')';
            }
            else if($this->m_allowed_objects_condition != '')
            {
                $this->m_allowed_object_types_condition = ' AND (isys_obj_type__id IN (SELECT DISTINCT(isys_obj__isys_obj_type__id) FROM isys_obj WHERE TRUE ' . $this->m_allowed_objects_condition . '))';
            } // if

            try
            {
                $l_cache_obj->set('auth.condition.allowed_objects', $this->m_allowed_objects_condition)
                    ->set('auth.condition.allowed_object_types', $this->m_allowed_object_types_condition);
            }
            catch (isys_exception_filesystem $e)
            {
                isys_notify::warning($e->getMessage());
            } // try
        } // if

        return true;
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    } // function
} // class