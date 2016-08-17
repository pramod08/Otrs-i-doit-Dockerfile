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
 * i-doit core classes.
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_usersettings extends isys_settings
{
    /**
     * This will cache the cache-directory string.
     *
     * @var  string
     */
    protected static $m_cache_dir = null;
    /**
     * Cache file.
     *
     * @var  string
     */
    protected static $m_cachefile = 'settings.user.cache';
    /**
     * Database component.
     *
     * @var  isys_component_dao_settings
     */
    protected static $m_dao;
    /**
     * Settings register.
     *
     * @var  array
     */
    protected static $m_definition = [
        'Quickinfo (Link mouseover)' => [
            'gui.quickinfo.active' => [
                'title'   => 'LC__USER_SETTINGS__QUICKINFO_ACTIVE',
                'type'    => 'select',
                'default' => '1',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.quickinfo.delay'  => [
                'title'       => 'LC__UNIVERSAL__DELAY',
                'type'        => 'text',
                'default'     => '0.5',
                'placeholder' => '0.5'
            ]
        ],
        'Workflows'                  => [
            'workflows.max-checklist-entries' => [
                'title'       => 'LC__USER_SETTINGS__CHECKLIST_LIMIT',
                'type'        => 'text',
                'placeholder' => '7'
            ]
        ],
        'Object lists'               => [
            'gui.objectlist.remember-filter' => [
                'title'       => 'LC__CMDB__TREE__SYSTEM__OBJECT_LIST__FILTER_MEMORIZE',
                'type'        => 'text',
                'default'     => '300',
                'placeholder' => '0',
                'description' => 'LC__CMDB__TREE__SYSTEM__OBJECT_LIST__FILTER_MEMORIZE_DESCRIPTION'
            ],
            'gui.objectlist.rows-per-page'   => [
                'title'       => 'LC__SYSTEM__REGISTRY__PAGELIMIT',
                'type'        => 'text',
                'default'     => '50',
                'placeholder' => '50'
            ]
        ]
    ];
    /**
     * Settings initialized?
     *
     * @var  boolean
     */
    protected static $m_initialized = false;
    /**
     * Settings storage.
     *
     * @var  array
     */
    protected static $m_settings = [];
    /**
     * To identify changes.
     *
     * @var  boolean
     */
    private static $m_changed = false;

    /**
     * @param $p_settings
     */
    public static function extend($p_settings)
    {
        self::$m_definition += $p_settings;
    }

    /**
     * Override all settings.
     */
    public static function force_save($p_usersettings = true)
    {
        parent::force_save(true);
    } // function

    /**
     * Return a system setting
     *
     * @static
     *
     * @param   string $p_key     Setting identifier
     * @param   mixed  $p_default Default value
     *
     * @return  mixed
     */
    public static function get($p_key = null, $p_default = '')
    {
        if ($p_key === null)
        {
            return static::$m_settings;
        } // if

        if (isset(static::$m_settings[$p_key]) && static::$m_settings[$p_key] != '')
        {
            return static::$m_settings[$p_key];
        }
        else
        {
            return isys_tenantsettings::get($p_key, $p_default);
        }
    } // function

    /**
     * Method for retrieving the cache directory.
     *
     * @static
     * @throws  Exception
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected static function get_cache_dir()
    {
        if (static::$m_cache_dir !== null)
        {
            return static::$m_cache_dir;
        } // if

        global $g_absdir, $g_mandator_info, $g_comp_session;

        if ($g_comp_session->is_logged_in())
        {
            if (!isset($g_mandator_info["isys_mandator__dir_cache"]) && !$g_mandator_info["isys_mandator__dir_cache"])
            {
                throw new Exception('Error: Cache directory in $g_mandator_info not set.');
            } // if

            return static::$m_cache_dir = $g_absdir . DS . 'temp' . DS . $g_mandator_info["isys_mandator__dir_cache"] . DS;
        }
        else
        {
            throw new Exception('Usersettings are only available after logging in.');
        } // if
    } // function

    /**
     * Load cache.
     *
     * @static
     *
     * @param   isys_component_database $p_database
     *
     * @return  void
     */
    public static function initialize(isys_component_database $p_database)
    {
        isys_component_signalcollection::get_instance()
            ->connect(
                'system.shutdown',
                [
                    'isys_usersettings',
                    'shutdown'
                ]
            );

        self::$m_cachefile = 'settings.' . isys_application::instance()->session->get_user_id() . '.cache';

        if (!is_object(static::$m_dao))
        {
            static::$m_dao = new isys_component_dao_settings($p_database);
        } // if

        $l_cache_dir = static::get_cache_dir();

        // Generate cache and load settings.
        if ($l_cache_dir)
        {
            try
            {
                if (!file_exists($l_cache_dir . static::$m_cachefile))
                {
                    self::regenerate();
                }
                else
                {
                    self::load_cache($l_cache_dir);
                } // if
            }
            catch (Exception $e)
            {
                // @todo log cache exceptions to system log

                // Load settings from database instead of cache.
                if (!static::$m_settings)
                {
                    static::$m_settings = static::$m_dao->get_settings(true);
                } // if
            } // try
        } // if

        static::$m_initialized = true;
    } // function

    /**
     * Override all settings.
     *
     * @param  array $p_settings
     */
    public static function override(array $p_settings)
    {
        // Overwrite settings array.
        static::$m_settings = $p_settings;

        // Write cache.
        self::cache();

        // Save to database.
        static::$m_dao->save($p_settings, true);
    } // function

    /**
     * (Re)generates cache.
     *
     * @throws  Exception
     * @return  array
     */
    public static function regenerate($p_usersettings = true)
    {
        return parent::regenerate(true);
    } // function

    /**
     * Set a setting value.
     *
     * @param  string $p_key
     * @param  mixed  $p_value
     */
    public static function set($p_key, $p_value, $p_usersettings = true)
    {
        self::$m_changed = true;

        parent::set($p_key, $p_value, true);
    } // function

    /**
     * Before destructing the usersettings we want to save the data.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function shutdown()
    {
        if (self::$m_changed)
        {
            parent::force_save(true);
        } // if
    } // function
} // class