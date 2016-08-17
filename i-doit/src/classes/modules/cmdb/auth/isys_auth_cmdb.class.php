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
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_cmdb extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var isys_auth_cmdb
     */
    private static $m_instance = null;
    /**
     * This is a helper-array for the "categories in locations" path.
     *
     * @var  array
     */
    protected $m_categories_in_locations = [];
    /**
     * This is a helper-array for the "categories in objects" path.
     *
     * @var  array
     */
    protected $m_categories_in_objects = [];
    /**
     * @var array
     */
    private $m_object_cache = [];

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_cmdb
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
    } // function

    /**
     * Protected method for combining "category" paths.
     *
     * @static
     *
     * @param   array $p_category_paths
     *
     * @return  array
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    protected static function combine_simple_values(array &$p_category_paths)
    {
        // Prepare some variables.
        $l_return          = [];
        $l_keys            = [];
        $l_last_rights_num = 0;

        // Sort the parameters, so that the foreach will do its job correctly.
        isys_auth::sort_paths_by_rights($p_category_paths);

        foreach ($p_category_paths as $l_key => $l_rights)
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
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB-explorer.
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function explorer()
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('explorer', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_CMDB_EXPLORER')));
    } // function

    /**
     * Method for checking, if the user has the right to view/edit/delete the CMDB-explorer profiles.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function explorer_profiles($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('explorer_profiles', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_CMDB_EXPLORER_PROFILES')), $p_right);
    } // function

    /**
     * Method for checking, if the user has the right to view the location view on the left menu tree.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function location_view($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('location_view', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LOCATION_VIEW')), $p_right);
    } // function

    /**
     * Method for checking, if a certain OBJ-ID inherits the given rights. If these could not be found, it will be
     * checked for the OBJ-IN-TYPE.
     *
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function obj_id($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        if (isset($this->m_paths['obj_id']))
        {
            // Check for wildchars.
            if (isset($this->m_paths['obj_id'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['obj_id'][isys_auth::WILDCHAR]))
            {
                return true;
            } // if

            // Check for actual rights on the given OBJ-ID.
            if (isset($this->m_paths['obj_id'][$p_id]) && in_array($p_right, $this->m_paths['obj_id'][$p_id]))
            {
                return true;
            } // if
        } // if

        // Retrieve object.
        $l_obj = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_object($p_id, false, 1)
            ->get_row();

        // If we could find no rights for this object, we look if the object-type is allowed in general.
        try
        {
            return $this->obj_in_type($p_right, strtolower($l_obj['isys_obj_type__const']));
        }
        catch (isys_exception_auth $e)
        {
            ; // Do nothing here. The method will throw his own exception.
        } // try

        // If we could find no rights for this object, we look if the location is allowed in general.
        try
        {
            return $this->location($p_right, $p_id);
        }
        catch (isys_exception_auth $e)
        {
            ; // Do nothing here. The method will throw his own exception.
        } // try

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_ID',
                [
                    $l_right_name,
                    $l_obj['isys_obj__title']
                ]
            )
        );
    } // function

    /**
     * This method checks, if you are allowed to process an action for objects of a given type.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function obj_in_type($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $l_constant = strtoupper(trim($p_id));

        if (!defined($l_constant))
        {
            throw new isys_exception_general(_L('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_constant));
        } // if

        if (isset($this->m_paths['obj_in_type']))
        {
            // Check for wildchars.
            if (isset($this->m_paths['obj_in_type'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::WILDCHAR]))
            {
                return true;
            } // if

            // Check for actual rights on the given OBJ-TYPE.
            if (isset($this->m_paths['obj_in_type'][$p_id]) && in_array($p_right, $this->m_paths['obj_in_type'][$p_id]))
            {
                return true;
            } // if
        } // if

        // Get some translations for the exception.
        $l_type_id       = constant($l_constant);
        $l_right_name    = isys_auth::get_right_name($p_right);
        $l_obj_type_name = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_objtype_name_by_id_as_string($l_type_id);

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_IN_TYPE',
                [
                    $l_right_name,
                    _L($l_obj_type_name)
                ]
            )
        );
    } // function

    /**
     * This method checks, if you are allowed to process an action for a certain OBJ-TYPE (used for object-type configuration).
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function obj_type($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $l_constant = strtoupper(trim($p_id));

        if (isset($this->m_paths['obj_type']))
        {
            // This checks for paths like "CMDB/OBJ_TYPE" without IDs (will be used to check, if the "new" button shall be displayed in the list-view).
            if (empty($l_constant) && isset($this->m_paths['obj_type'][isys_auth::EMPTY_ID_PARAM]) && in_array(
                    $p_right,
                    $this->m_paths['obj_type'][isys_auth::EMPTY_ID_PARAM]
                )
            )
            {
                return true;
            } // if

            if (!defined($l_constant))
            {
                throw new isys_exception_general(_L('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_constant));
            } // if

            // Check for wildchars.
            if (isset($this->m_paths['obj_type'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['obj_type'][isys_auth::WILDCHAR]))
            {
                return true;
            } // if

            // Check for actual rights on the given OBJ-TYPE.
            if (isset($this->m_paths['obj_type'][$p_id]) && in_array($p_right, $this->m_paths['obj_type'][$p_id]))
            {
                return true;
            } // if
        } // if

        // Get some translations for the exception.
        $l_right_name = isys_auth::get_right_name($p_right);

        if (empty($l_constant))
        {
            $l_obj_type_name = isys_tenantsettings::get('gui.empty_value', '-');
        }
        else
        {
            $l_obj_type_name = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
                ->get_objtype_name_by_id_as_string(constant($l_constant));
        } // if

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_TYPE',
                [
                    $l_right_name,
                    _L($l_obj_type_name)
                ]
            )
        );
    } // function

    /**
     * This method checks, if the user has right to see a certain category in a certain object type.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function category_in_obj_type($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $p_id = strtolower(trim($p_id));
        list (, $l_obj_type) = explode('+', $p_id);

        if (isset($this->m_paths['category_in_obj_type']))
        {
            if (isset($this->m_paths['category_in_obj_type'][isys_auth::WILDCHAR . '+' . $l_obj_type]) && in_array(
                    $p_right,
                    $this->m_paths['category_in_obj_type'][isys_auth::WILDCHAR . '+' . $l_obj_type]
                )
            )
            {
                return true;
            } // if

            if (isset($this->m_paths['category_in_obj_type'][$p_id]) && in_array($p_right, $this->m_paths['category_in_obj_type'][$p_id]))
            {
                return true;
            } // if
        } // if

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $p_id;
        $l_categories     = isys_cmdb_dao::factory(self::$m_dao->get_database_component())->get_cat_by_const(strstr($p_id, '+', true), ['title']);

        if ($l_categories && is_array($l_categories))
        {
            list($l_category_title) = array_values($l_categories);
        } // if

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY',
                [
                    $l_right_name,
                    _L($l_category_title)
                ]
            )
        );
    } // function

    /**
     * This method checks, if the user has right to see a certain category in a certain object.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function category_in_object($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        list($l_category, $l_id) = explode('+', strtolower($p_id));

        if (isset($this->m_categories_in_objects[$l_category]) || isset($this->m_categories_in_objects[isys_auth::WILDCHAR]))
        {
            if (isset($this->m_categories_in_objects[isys_auth::WILDCHAR][$l_id]) && in_array($p_right, $this->m_categories_in_objects[isys_auth::WILDCHAR][$l_id]))
            {
                return true;
            } // if

            if (isset($this->m_categories_in_objects[$l_category][$l_id]) && in_array($p_right, $this->m_categories_in_objects[$l_category][$l_id]))
            {
                return true;
            } // if
        } // if

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_category;
        $l_category       = isys_cmdb_dao::factory(self::$m_dao->get_database_component())->get_cat_by_const($l_category, ['title']);

        if ($l_category && is_array($l_category))
        {
            list($l_category_title) = array_values($l_category);
        } // if

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY',
                [
                    $l_right_name,
                    _L($l_category_title)
                ]
            )
        );
    } // function

    /**
     * This method checks, if the user has right to see a certain category underneath certain location.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function category_in_location($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        list($l_category, $l_object_id) = explode('+', strtolower($p_id));

        if (isset($this->m_categories_in_locations[isys_auth::WILDCHAR]) || isset($this->m_categories_in_locations[$l_category]))
        {
            if (isset($this->m_categories_in_locations[isys_auth::WILDCHAR][$l_object_id]) && in_array(
                    $p_right,
                    $this->m_categories_in_locations[isys_auth::WILDCHAR][$l_object_id]
                )
            )
            {
                return true;
            } // if

            if (isset($this->m_categories_in_locations[$l_category][$l_object_id]) && in_array($p_right, $this->m_categories_in_locations[$l_category][$l_object_id]))
            {
                return true;
            } // if

            // The given ID could not be found directly, now we check the location path.

            /** @var isys_cmdb_dao_location $l_dao_location */
            $l_dao_location = isys_factory::get_instance('isys_cmdb_dao_location', self::$m_dao->get_database_component());
            $l_mptt         = $l_dao_location->get_mptt();

            if (is_array($this->m_categories_in_locations[isys_auth::WILDCHAR]))
            {

                foreach ($this->m_categories_in_locations[isys_auth::WILDCHAR] as $l_location_id => $l_rights)
                {
                    if ($l_mptt->has_children($l_location_id, $l_object_id))
                    {
                        if (in_array($p_right, $l_rights))
                        {
                            $this->m_object_cache[$l_object_id][$p_right] = true;

                            return true;
                        }
                    }
                } // foreach
            } // if

            if (is_array($this->m_categories_in_locations[$l_category]))
            {
                foreach ($this->m_categories_in_locations[$l_category] as $l_location_id => $l_rights)
                {
                    if (in_array($p_right, $l_rights))
                    {
                        return true;
                    }
                } // foreach
            } // if
        } // if

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_category;
        $l_category       = isys_cmdb_dao::factory(self::$m_dao->get_database_component())->get_cat_by_const($l_category, ['title']);

        if ($l_category && is_array($l_category))
        {
            list($l_category_title) = array_values($l_category);
        } // if

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY',
                [
                    $l_right_name,
                    _L($l_category_title)
                ]
            )
        );
    } // function

    /**
     * This method checks wheather you are allowed to process an action for a certain location.
     *
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function location($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        /**
         * Return cached result
         */
        if (isset($this->m_object_cache[$p_id][$p_right]))
        {
            return $this->m_object_cache[$p_id][$p_right];
        }

        if (isset($this->m_paths['location']))
        {
            if (isset($this->m_paths['location'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['location'][isys_auth::WILDCHAR]))
            {
                return true;
            }
            else
            {
                if (isset($this->m_paths['location'][$p_id]) && in_array($p_right, $this->m_paths['location'][$p_id]))
                {
                    return true;
                }
                else
                {
                    // The given ID could not be found directly, now we check the location path.

                    /** @var isys_cmdb_dao_location $l_dao_location */
                    $l_dao_location = isys_cmdb_dao_location::factory(isys_application::instance()->database);
                    $l_mptt         = $l_dao_location->get_mptt();

                    foreach ($this->m_paths['location'] as $l_location_id => $l_rights)
                    {
                        // Get child locations of the location auth-paths.
                        if ($l_mptt->has_children($l_location_id, $p_id))
                        {
                            if (in_array($p_right, $this->m_paths['location'][$l_location_id]))
                            {
                                $this->m_object_cache[$p_id][$p_right] = true;

                                return true;
                            }
                        }
                    } // foreach

                    $this->m_object_cache[$p_id][$p_right] = false;
                } // if
            } // if
        } // if

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);
        $l_obj        = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_object($p_id, false, 1)
            ->get_row();

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_LOCATION',
                [
                    $l_right_name,
                    $l_obj['isys_obj__title']
                ]
            )
        );
    } // function

    /**
     * This method checks, if you are allowed to process an action for a category.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function category($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $l_constant = strtoupper(trim($p_id));

        if (!defined($l_constant))
        {
            throw new isys_exception_general(_L('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_constant));
        } // if

        if (isset($this->m_paths['category']))
        {
            // Check for wildchars.
            if (isset($this->m_paths['category'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['category'][isys_auth::WILDCHAR]))
            {
                return true;
            } // if

            // Check for actual rights on the given OBJ-TYPE.
            if (isset($this->m_paths['category'][$p_id]) && in_array($p_right, $this->m_paths['category'][$p_id]))
            {
                return true;
            } // if
        } // if

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_constant;
        $l_category       = isys_cmdb_dao::factory(self::$m_dao->get_database_component())->get_cat_by_const($l_constant, ['title']);

        if ($l_category && is_array($l_category))
        {
            list($l_category_title) = array_values($l_category);
        } // if

        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY',
                [
                    $l_right_name,
                    _L($l_category_title)
                ]
            )
        );
    } // function

    /**
     * Determines the rights for multiedit in extras menu.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function multiedit($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('multiedit', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_MULTIEDIT')), $p_right);
    } // function

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function overwrite_user_list_config($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('overwrite_user_list_config', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LIST_CONFIG')), $p_right);
    } // function

    /**
     * @param   integer  $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function list_config ($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('list_config', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LIST_CONFIG')), $p_right);
    } // function

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function define_standard_list_config($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if
        return $this->generic_boolean('define_standard_list_config', new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LIST_CONFIG')), $p_right);
    } // function

    /**
     * This method checks the rights for categories and checks the rights from the object.
     *
     * @param   integer $p_right
     * @param   integer $p_object_id
     * @param   string  $p_category_const
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function check_rights_obj_and_category($p_right, $p_object_id, $p_category_const)  // If errors occur, change back to "$p_category_const = null".
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        if ($p_category_const !== null && isset($this->m_paths['category']))
        {
            if ($this->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $p_object_id))
            {
                if (strtoupper($p_category_const) == 'C__CATG__OVERVIEW')
                {
                    if ($this->is_allowed_to($p_right, 'OBJ_ID/' . $p_object_id) && $this->is_allowed_to($p_right, 'CATEGORY/' . strtoupper($p_category_const)))
                    {
                        return true;
                    } // if
                }
                else
                {
                    if ($this->is_allowed_to($p_right, 'CATEGORY/' . strtoupper($p_category_const)))
                    {
                        return true;
                    } // if
                } // if
            } // if
        } // if

        if ($p_category_const !== null)
        {
            if ($this->is_allowed_to($p_right, 'OBJ_ID/' . $p_object_id) && (strtoupper($p_category_const) == 'C__CATG__OVERVIEW'))
            {
                return true;
            } // if

            // We still got a few last chances to permit the user...
            if ($this->m_paths['category_in_obj_type'])
            {
                $l_obj_type = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
                    ->get_type_by_object_id($p_object_id)
                    ->get_row();

                try
                {
                    return $this->category_in_obj_type($p_right, $p_category_const . '+' . $l_obj_type['isys_obj_type__const']);
                }
                catch (isys_exception_auth $e)
                {
                    $e->write_log(); // We don't want to throw an error here.
                } // try
            } // if

            if (count($this->m_categories_in_objects) > 0)
            {
                try
                {
                    return $this->category_in_object($p_right, $p_category_const . '+' . $p_object_id);
                }
                catch (isys_exception_auth $e)
                {
                    $e->write_log(); // We don't want to throw an error here.
                } // try
            } // if

            if (count($this->m_categories_in_locations) > 0)
            {
                // No need to catch the exception, because this is the last check (for now).
                return $this->category_in_location($p_right, $p_category_const . '+' . $p_object_id);
            } // if

            $l_category_title = $p_category_const;
            $l_category       = isys_cmdb_dao::factory(self::$m_dao->get_database_component())->get_cat_by_const($p_category_const, ['title']);

            if ($l_category && is_array($l_category))
            {
                list($l_category_title) = array_values($l_category);
            } // if

            throw new isys_exception_auth(
                _L(
                    'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY',
                    [
                        $this->get_right_name($p_right),
                        _L($l_category_title)
                    ]
                )
            );
        }
        else
        {
            // check rights in object
            if ($this->is_allowed_to($p_right, 'OBJ_ID/' . $p_object_id))
            {
                return true;
            } // if
        } // if

        $l_obj_title = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_obj_name_by_id_as_string($p_object_id);
        throw new isys_exception_auth(
            _L(
                'LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_ID',
                [
                    $this->get_right_name($p_right),
                    $l_obj_title
                ]
            )
        );
    } // function

    /**
     * This method checks the rights for categories and checks the rights from the object. Without exception.
     *
     * @param   integer $p_right
     * @param   integer $p_object_id
     * @param   string  $p_category_const
     *
     * @return  bool
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function has_rights_in_obj_and_category($p_right, $p_object_id, $p_category_const) // If errors occur, change back to "$p_category_const = null".
    {
        try
        {
            return $this->check_rights_obj_and_category($p_right, $p_object_id, $p_category_const);
        }
        catch (isys_exception_auth $e)
        {
            return false;
        } // try
    } // function

    /**
     * Optional method for combining auth paths.
     *
     * @param   array &$p_paths
     *
     * @return  isys_auth_cmdb
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    public function combine_paths(array &$p_paths)
    {
        foreach ($p_paths as $l_method => $l_params)
        {
            switch ($l_method)
            {
                case 'obj_in_type':
                case 'obj_type':
                    $p_paths[$l_method] = isys_auth_cmdb_object_types::combine_object_types($l_params);
                    break;

                case 'obj_id':
                case 'category':
                    $p_paths[$l_method] = self::combine_simple_values($l_params);
                    break;

                case 'category_in_location':
                case 'category_in_object':
                case 'category_in_obj_type':
                    $p_paths[$l_method] = isys_auth_cmdb_categories::combine_category_with_parameter($l_params);
            } // switch
        } // foreach

        return $this;
    } // function

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'obj_id'                      => [
                'title' => _L('LC__AUTH_GUI__OBJ_ID_CONDITION'),
                'type'  => 'object'
            ],
            'obj_in_type'                 => [
                'title' => _L('LC__AUTH_GUI__OBJ_IN_TYPE_CONDITION'),
                'type'  => 'object_type'
            ],
            'obj_type'                    => [
                'title' => _L('LC__AUTH_GUI__OBJ_TYPE_CONDITION'),
                'type'  => 'object_type'
            ],
            'location'                    => [
                'title' => _L('LC__AUTH_GUI__LOCATION_CONDITION'),
                'type'  => 'location'
            ],
            'category'                    => [
                'title' => _L('LC__AUTH_GUI__CATEGORY_CONDITION'),
                'type'  => 'category'
            ],
            'category_in_obj_type'        => [
                'title' => _L('LC__AUTH_GUI__CATEGORY_IN_OBJ_TYPE_CONDITION'),
                'type'  => 'category_in_obj_type'
            ],
            'category_in_object'          => [
                'title' => _L('LC__AUTH_GUI__CATEGORY_IN_OBJECT_CONDITION'),
                'type'  => 'category_in_object'
            ],
            'category_in_location'        => [
                'title' => _L('LC__AUTH_GUI__CATEGORY_IN_LOCATION_CONDITION'),
                'type'  => 'category_in_location'
            ],
            'multiedit'                   => [
                'title'  => _L('LC__AUTH_GUI__MULTIEDIT_CONDITION'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'list_config' => [
                'title'  => _L('LC__AUTH_GUI__LIST_CONFIG'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'overwrite_user_list_config'  => [
                'title'  => _L('LC__AUTH_GUI__OVERWRITE_USER_LIST_CONFIG'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'define_standard_list_config' => [
                'title'  => _L('LC__AUTH_GUI__DEFINE_STANDARD_LIST_CONFIG'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'explorer'                    => [
                'title'  => _L('LC__AUTH_GUI__EXPLORER_CONDITION'),
                'type'   => 'boolean',
                'rights' => [isys_auth::VIEW]
            ],
            'explorer_profiles'           => [
                'title'  => _L('LC__AUTH_GUI__EXPLORER_PROFILE_CONDITION'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::EDIT,
                    isys_auth::DELETE
                ]
            ],
            'location_view'               => [
                'title'  => _L('LC__CMDB__MENU_TREE_VIEW'),
                'type'   => 'boolean',
                'rights' => [isys_auth::VIEW]
            ]
        ];
    } // function

    /**
     * Get ID of related module.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return C__MODULE__CMDB;
    } // function

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return "CMDB";
    } // function

    /**
     * Constructor, will load all necessary paths.
     *
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    protected function __construct()
    {
        parent::__construct();

        // After the constructor is called, we can prepare some of the loaded paths.
        if (isset($this->m_paths['category_in_object']))
        {
            foreach ($this->m_paths['category_in_object'] as $l_param => $l_rights)
            {
                list($l_category, $l_ids) = explode('+', strtolower($l_param));

                if (is_numeric($l_ids))
                {
                    $l_ids = [$l_ids];
                }
                else
                {
                    if (isys_format_json::is_json_array($l_ids))
                    {
                        $l_ids = isys_format_json::decode($l_ids);
                    }
                    else
                    {
                        // How about nope?
                        continue;
                    } // if
                } // if

                if (!isset($this->m_categories_in_objects[$l_category]))
                {
                    $this->m_categories_in_objects[$l_category] = [];
                } // if

                if (count($l_ids) > 0)
                {
                    foreach ($l_ids as $l_id)
                    {
                        $this->m_categories_in_objects[$l_category][$l_id] = array_merge((array) $this->m_categories_in_objects[$l_category][$l_id], $l_rights);
                    } // foreach
                } // if
            } // foreach
        } // if

        if (isset($this->m_paths['category_in_location']))
        {
            foreach ($this->m_paths['category_in_location'] as $l_param => $l_rights)
            {
                list($l_category, $l_ids) = explode('+', strtolower($l_param));

                if (is_numeric($l_ids))
                {
                    $l_ids = [$l_ids];
                }
                else
                {
                    if (isys_format_json::is_json_array($l_ids))
                    {
                        $l_ids = isys_format_json::decode($l_ids);
                    }
                    else
                    {
                        // How about nope?
                        continue;
                    } // if
                } // if

                if (!isset($this->m_categories_in_locations[$l_category]))
                {
                    $this->m_categories_in_locations[$l_category] = [];
                } // if

                if (count($l_ids) > 0)
                {
                    foreach ($l_ids as $l_id)
                    {
                        $this->m_categories_in_locations[$l_category][$l_id] = array_merge((array) $this->m_categories_in_locations[$l_category][$l_id], $l_rights);
                    } // foreach
                } // if
            } // foreach
        } // if
    } // function
} // class