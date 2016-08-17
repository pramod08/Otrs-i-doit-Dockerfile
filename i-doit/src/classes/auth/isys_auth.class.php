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
 * Auth: abstract class for module authorization.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_auth
{
    /**
     * The almighty wildchar.
     *
     * @var  string
     */
    const WILDCHAR = '*';

    /**
     * This constant will be used for paths like "CMDB/OBJ_TYPE" (without ID).
     *
     * @var  string
     */
    const EMPTY_ID_PARAM = 'empty-id';

    /**
     * Holds the value for viewing-right.
     *
     * @var  integer
     */
    const VIEW = 1;

    /**
     * Holds the value for edit-right (includes "view").
     *
     * @var  integer
     */
    const EDIT = 2;

    /**
     * Holds the value for delete-right (includes "view" and "archive").
     *
     * @var  integer
     */
    const DELETE = 4;

    /**
     * Holds the value for execute-right (includes "view").
     *
     * @var  integer
     */
    const EXECUTE = 8;

    /**
     * Holds the value for execute-right (includes "view").
     *
     * @var  integer
     */
    const ARCHIVE = 16;

    /**
     * Holds the value for edit-right (includes "view", "edit", "archive", "delete", "execute" and every right to come...).
     *
     * @var  integer
     */
    const SUPERVISOR = 2048;
    /**
     * Holds an instance of "isys_auth_dao" for all database queries.
     *
     * @var  isys_auth_dao
     */
    protected static $m_dao = null;
    /**
     * Holds all module path-instances in an array.
     *
     * @var  array
     */
    protected $m_paths = [];

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    abstract public function get_auth_methods();

    /**
     * Method for retrieving the "human-readable" right name.
     *
     * @param   integer $p_right
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_right_name($p_right = null)
    {
        $l_rights = [
            self::VIEW       => _L('LC__AUTH__RIGHT_VIEW'),
            self::EDIT       => _L('LC__AUTH__RIGHT_EDIT'),
            self::DELETE     => _L('LC__AUTH__RIGHT_DELETE'),
            self::EXECUTE    => _L('LC__AUTH__RIGHT_EXECUTE'),
            self::ARCHIVE    => _L('LC__AUTH__RIGHT_ARCHIVE'),
            self::SUPERVISOR => _L('LC__AUTH__RIGHT_SUPERVISOR')
        ];

        if ($p_right !== null)
        {
            return $l_rights[$p_right];
        } // if

        return $l_rights;
    } // function

    /**
     * Method for retrieving the "rights" including some additional data.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_rights()
    {
        return [
            [
                'title' => _L('LC__AUTH__RIGHT_VIEW'),
                'icon'  => 'icons/eye.png',
                'value' => self::VIEW
            ],
            [
                'title' => _L('LC__AUTH__RIGHT_EDIT'),
                'icon'  => 'icons/silk/pencil.png',
                'value' => self::EDIT
            ],
            [
                'title' => _L('LC__AUTH__RIGHT_ARCHIVE'),
                'icon'  => 'icons/silk/folder_page.png',
                'value' => self::ARCHIVE
            ],
            [
                'title' => _L('LC__AUTH__RIGHT_DELETE'),
                'icon'  => 'icons/silk/delete.png',
                'value' => self::DELETE
            ],
            [
                'title' => _L('LC__AUTH__RIGHT_EXECUTE'),
                'icon'  => 'icons/silk/cog.png',
                'value' => self::EXECUTE
            ],
            [
                'title' => _L('LC__AUTH__RIGHT_SUPERVISOR'),
                'icon'  => 'icons/silk/user_gray.png',
                'value' => self::SUPERVISOR
            ]
        ];
    } // function

    /**
     * Fallback.
     *
     * @throws      isys_exception_general
     * @deprecated  This should be removed in the future.
     */
    public static function factory()
    {
        global $g_product_info;

        throw new isys_exception_general(_L('LC__COMPATIBILTY_ERROR__MODULE_IDOIT', $g_product_info['version']));
    } // function

    /**
     * Sort the parameters, so that the combine functions will do their job correctly.
     *
     * @param   array &$p_paths
     *
     * @return  void
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_auth::combine_paths()
     */
    protected static function sort_paths_by_rights(array &$p_paths)
    {
        uasort(
            $p_paths,
            function ($p_a, $p_b)
            {
                $p_a = array_sum($p_a);
                $p_b = array_sum($p_b);

                if ($p_a == $p_b)
                {
                    return 0;
                } // if

                return ($p_a < $p_b) ? -1 : 1;
            }
        );
    } // function

    /**
     * Generic "check()" method, may be overwritten by "isys_auth_module_*" classes.
     *
     * @param   integer $p_right
     * @param   string  $p_path
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function check($p_right, $p_path)
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        list($l_method, $l_id) = explode('/', strtolower($p_path));

        if (!method_exists($this, $l_method))
        {
            // Retrieve method title.
            $l_methods = $this->get_auth_methods();

            if (isset($l_methods[$l_method]))
            {
                $l_action_title = _L($l_methods[$l_method]['title']);

                // Check via "generic_boolean" if the type fits.
                if ($l_methods[$l_method]['type'] == 'boolean')
                {
                    return $this->generic_boolean(
                        $l_method,
                        new isys_exception_auth(
                            _L(
                                'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                                [
                                    $l_action_title,
                                    _L($this->get_module_title())
                                ]
                            )
                        ),
                        $p_right
                    );
                } // if
            }
            else
            {
                $l_action_title = $l_method;
            } // if

            // Check via generic_right.
            return $this->generic_right(
                $p_right,
                $l_method,
                $l_id,
                new isys_exception_auth(
                    _L(
                        'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                        [
                            $l_action_title,
                            _L($this->get_module_title())
                        ]
                    )
                )
            );
        } // if

        return call_user_func(
            [
                $this,
                $l_method
            ],
            $p_right,
            $l_id
        );
    } // function

    /**
     * This method will process the exact same code as "check()" but will return a boolean value without any exceptions.
     *
     * @param   integer $p_right
     * @param   string  $p_path
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function is_allowed_to($p_right, $p_path)
    {
        // Check for inactive auth system.
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        try
        {
            return $this->check($p_right, $p_path);
        }
        catch (Exception $e)
        {
            return false;
        } // try
    } // function

    /**
     * Check, if user has a baseright.
     *
     * @param   string $p_master_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function has($p_master_right)
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return (is_array($this->m_paths) && isset($this->m_paths[$p_master_right]) && is_array($this->m_paths[$p_master_right]));
    } // function

    /**
     * Checks if there exists any path for the current module.
     *
     * @return  boolean
     * @authro  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function has_any_rights_in_module()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        if (!is_array($this->m_paths))
        {
            return false;
        } // if

        return !!count($this->m_paths);
    } // function

    /**
     * Optional method for combining auth paths.
     *
     * @static
     *
     * @param   array &$p_paths
     *
     * @return  isys_auth
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    public function combine_paths(array &$p_paths)
    {
        return $this;
    } // function

    /**
     * Get ID of related module.
     *
     * @todo    Should be part of the interfaces as of version 1.6
     * @return  integer
     */
    public function get_module_id()
    {
        isys_application::instance()->container['notify']->error(
            'Module ' . str_replace('isys_auth_', '', get_class($this)) . ' is not compatible with version ' . isys_application::instance()->info->get(
                'version'
            ) . '. Please update.'
        );

        return 0;
    } // function

    /**
     * Get title of related module.
     *
     * @todo    Should be part of the interfaces as of version 1.6
     * @return  string
     */
    public function get_module_title()
    {
        return str_replace('isys_auth_', '', get_class($this));
    } // function

    /**
     * Check whether the authorization system is active or not.
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    protected function is_auth_active()
    {
        global $g_config;

        return $g_config['use_auth'];
    } // function

    /**
     * Method for preparing the single "path" objects.
     *
     * @param   integer $p_person_id
     * @param   integer $p_module_id
     *
     * @return  isys_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function load_auth_paths($p_person_id = null, $p_module_id = null)
    {
        // If "$p_person_id" equals "null", we load the currently logged in user via session.
        if ($p_person_id === null)
        {
            global $g_comp_session;

            $p_person_id = $g_comp_session->get_user_id();
        } // if

        // If "$p_module_id" equals "null", we use the current module.
        if ($p_module_id === null)
        {
            $p_module_id = $this->get_module_id();
        } // if

        $l_cache_key   = $p_person_id . '-' . $p_module_id;
        $l_cache       = isys_cache_keyvalue::keyvalue()->ns('auth');
        $this->m_paths = $l_cache->get($l_cache_key);

        if (!$this->m_paths)
        {
            $l_person_paths = self::$m_dao->get_paths($p_person_id, $p_module_id);
            $l_group_paths  = self::$m_dao->get_group_paths_by_person($p_person_id, $p_module_id);

            if ($l_group_paths !== false && count($l_group_paths) > 0)
            {
                $this->m_paths = self::$m_dao->build_paths_by_result($l_group_paths);
            } // if

            if ($l_person_paths !== false && count($l_person_paths) > 0)
            {
                $l_paths_person = self::$m_dao->build_paths_by_result($l_person_paths);

                if (count($l_paths_person) > 0)
                {
                    // We tried to merge the two arrays, but that didn't work out - So we need a foreach.
                    foreach ($l_paths_person as $l_method => $l_params)
                    {
                        if (!isset($this->m_paths[$l_method]))
                        {
                            $this->m_paths[$l_method] = [];
                        } // if

                        foreach ($l_params as $l_param => $l_rights)
                        {
                            if (!isset($this->m_paths[$l_method][$l_param]))
                            {
                                $this->m_paths[$l_method][$l_param] = [];
                            } // if

                            // Even at this level the merging does not work properly...
                            foreach ($l_rights as $l_right)
                            {
                                if (!in_array($l_right, $this->m_paths[$l_method][$l_param]))
                                {
                                    $this->m_paths[$l_method][$l_param][] = $l_right;
                                } // if
                            } // foreach
                        } // foreach
                    } // foreach
                } // if
            } // if

            try
            {
                $l_cache->set($l_cache_key, $this->m_paths);
            }
            catch (Exception $e)
            {
                isys_notify::error($e->getMessage());
            }
        }

        return $this;
    } // function

    /**
     * Generic boolean checker. We can use this for yes/no rights.
     *
     * @param   mixed               $p_method
     * @param   isys_exception_auth $p_exception
     * @param   integer             $p_right
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @todo    Wrong order of parameters. Please use "1: right, 2: method, 3: exception".
     */
    protected function generic_boolean($p_method, isys_exception_auth $p_exception, $p_right = null)
    {
        if (is_array($this->m_paths[$p_method]))
        {
            if (!empty($p_right))
            {
                if (in_array($p_right, $this->m_paths[$p_method][self::EMPTY_ID_PARAM]))
                {
                    return true;
                } // if
            }
            else
            {
                return true;
            } // if
        } // if

        throw $p_exception;
    } // function

    /**
     * Generic right checker.
     *
     * @param   integer             $p_right     Right to check.
     * @param   string              $p_method    Usally the method name.
     * @param   mixed               $p_param     Identifier (CONSTANT|ID|ETC).
     * @param   isys_exception_auth $p_exception The exception which shall be thrown.
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    protected function generic_right($p_right, $p_method, $p_param, isys_exception_auth $p_exception)
    {
        if (isset($this->m_paths[$p_method]) && is_array($this->m_paths[$p_method]))
        {
            // Check for wildchars.
            if (isset($this->m_paths[$p_method][self::WILDCHAR]) && in_array($p_right, $this->m_paths[$p_method][self::WILDCHAR]))
            {
                return true;
            } // if

            if (isset($this->m_paths[$p_method][$p_param]) && in_array($p_right, $this->m_paths[$p_method][$p_param]))
            {
                return true;
            } // if
        } // if

        throw $p_exception;
    }

    /**
     * Checks if any rights for the specified path exist.
     *
     * @param   integer             $p_right
     * @param   string              $p_method
     * @param   string              $p_param
     * @param   isys_exception_auth $p_exception
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function check_module_rights($p_right, $p_method, $p_param, isys_exception_auth $p_exception)
    {
        if (isset($this->m_paths[$p_method]) && is_array($this->m_paths[$p_method]) && empty($p_param))
        {
            return true;
        }
        else
        {
            return $this->generic_right($p_right, $p_method, $p_param, $p_exception);
        } // if
    }

    /**
     * Constructor, will load all necessary paths.
     *
     * @author  Leonard Fischer <lficsher@i-doit.com>
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    protected function __construct()
    {
        // Load the CMDB specific paths.
        $this->load_auth_paths();
    } // function
} // class