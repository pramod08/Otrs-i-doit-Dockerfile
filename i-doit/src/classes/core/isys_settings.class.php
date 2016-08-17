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
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_settings
{
    /**
     * Cache file.
     *
     * @var  string
     */
    protected static $m_cachefile = 'settings.cache';
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
        'User interface'    => [
            'gui.wiki-url'             => [
                'title'       => 'Wiki URL',
                'type'        => 'text',
                'placeholder' => 'https://wikipedia.org/wiki/'
            ],
            'gui.wysiwyg'              => [
                'title'   => 'LC__SYSTEM_SETTINGS__WYSIWYG_EDITOR',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.wysiwyg-all-controls' => [
                'title'   => 'LC__SYSTEM_SETTINGS__WYSIWYG_EDITOR_FULL_CONTROL',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'login.tenantlist.sortby'  => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT_SORT_FUNCTION',
                'type'    => 'select',
                'options' => [
                    'isys_mandator__title' => 'LC__UNIVERSAL__TITLE',
                    'isys_mandator__sort'  => 'LC__SYSTEM_SETTINGS__TENANT_SORT_FUNCTION__CUSTOM'
                ]
            ]
        ],
        'Session'           => [
            'session.time' => [
                'title'       => 'Session timeout',
                'type'        => 'text',
                'description' => 'LC__CMDB__UNIT_OF_TIME__SECOND',
                'placeholder' => '300',
                'default'     => '300'
            ]
        ],
        'Single Sign On'    => [
            'session.sso.active'      => [
                'title'   => 'LC__UNIVERSAL__ACTIVE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'session.sso.mandator-id' => [
                'title' => 'LC__SYSTEM_SETTINGS__DEFAULT_MANDATOR',
                'type'  => 'select'
            ]
        ],
        'System Parameters' => [
            'reports.browser-url'            => [
                'title'       => 'Report-Browser URL',
                'type'        => 'text',
                'hidden'      => true,
                'placeholder' => 'http://reports-ng.i-doit.org/s'
            ],
            'ldap.default-group'             => [
                'title'       => 'LC__SYSTEM_SETTINGS__DEFAULT_LDAP_GROUP',
                'type'        => 'text',
                'description' => 'LC__SYSTEM_SETTINGS__LDAP_GROUP_DESCRIPTION',
                'placeholder' => '14'
            ],
            'cmdb.connector.suffix-schema'   => [
                'title'  => '',
                'type'   => 'select',
                'hidden' => true
            ],
            'system.timezone'                => [
                'title'       => 'LC__SYSTEM_SETTINGS__PHP_TIMEZONE',
                'type'        => 'text',
                'placeholder' => 'Europe/Berlin',
                'description' => '<a href="https://php.net/manual/timezones.php">https://php.net/manual/timezones.php</a>'
            ],
            'auth.active'                    => [
                'title'   => 'LC__MODULE__AUTH',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__MODULE__QCW__INACTIVE',
                    '1' => 'LC__NOTIFICATIONS__NOTIFICATION_STATUS'
                ]
            ],
            'system.devmode'                 => [
                'title'   => 'Developer mode',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'hidden'  => true
            ],
            'system.dir.file-upload'         => [
                'title'       => 'LC__SYSTEM_SETTINGS__FILE_UPLOAD_DIRECTORY',
                'placeholder' => '/path/to/i-doit/upload/files/',
                'type'        => 'text'
            ],
            'system.dir.image-upload'        => [
                'title'       => 'LC__SYSTEM_SETTINGS__IMAGE_UPLOAD_DIRECTORY',
                'placeholder' => '/path/to/i-doit/upload/images/',
                'type'        => 'text'
            ],
            'tts.rt.queues'                  => [
                'title'       => 'Request Tracker queues',
                'type'        => 'text',
                'placeholder' => 'General'
            ],
            'cmdb.quickpurge'                => [
                'title'       => 'LC__SYSTEM_SETTINGS__QUICKPURGE',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__QUICKPURGE_DESCRIPTION'
            ],
            'cmdb.object.title.cable-prefix' => [
                'title' => 'LC__SYSTEM_SETTINGS__OBJECT_CABLE_PREFIX',
                //'Object cable prefix',
                'type'  => 'text',
            ],
            'import.object.keep-status'      => [
                'title'       => 'LC__SYSTEM_SETTINGS__IMPORT_OBJECT_KEEP',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__IMPORT_OBJECT_KEEP_STATUS_DESCRIPTION'
            ]
        ],
        'Logging'           => [
            'logging.system.api'  => [
                'title'       => 'Api',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__API__LOGGING_ENABLED'
            ],
            'logging.cmdb.import' => [
                'title'   => 'CMDB Import',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'ldap.debug'          => [
                'title'       => 'LDAP Debug',
                'type'        => 'select',
                'description' => 'ldap_debug in log/',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ]
        ],
        'E-Mail'            => [
            'system.email.smtp-host'          => [
                'title'       => 'SMTP Host',
                'type'        => 'text',
                'placeholder' => 'mail.i-doit.com'
            ],
            'system.email.port'               => [
                'title'       => 'SMTP Port',
                'type'        => 'text',
                'placeholder' => '25'
            ],
            'system.email.username'           => [
                'title'       => 'SMTP Username',
                'type'        => 'text',
                'placeholder' => 'username'
            ],
            'system.email.password'           => [
                'title'       => 'SMTP Password',
                'type'        => 'password',
                'placeholder' => 'password'
            ],
            'system.email.from'               => [
                'title'       => 'LC__SYSTEM_SETTINGS__SENDER',
                'type'        => 'text',
                'placeholder' => 'i-doit@i-doit.com'
            ],
            'system.email.name'               => [
                'title'       => 'Name',
                'type'        => 'text',
                'placeholder' => 'i-doit'
            ],
            'system.email.connection-timeout' => [
                'title'       => 'Timeout',
                'type'        => 'text',
                'placeholder' => '60'
            ],
            'system.email.smtpdebug'          => [
                'title'   => 'SMTP Debug',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'system.email.subject-prefix'     => [
                'title'       => 'LC__SYSTEM_SETTINGS__SUBJET_PREFIX',
                'type'        => 'text',
                'placeholder' => '[i-doit] '
            ],
            'email.template.maintenance'      => [
                'title' => 'LC__SYSTEM_SETTINGS__MAINTENANCE_CONTRACT_TEMPLATE',
                'type'  => 'textarea'
            ]
        ],
        'Proxy'             => [
            'proxy.active'   => [
                'title'   => 'LC__UNIVERSAL__ACTIVE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'proxy.host'     => [
                'title'       => 'LC__SYSTEM_SETTINGS__HOST_IP_ADDRESS',
                'type'        => 'text',
                'placeholder' => 'proxy.i-doit.com'
            ],
            'proxy.port'     => [
                'title'       => 'Port',
                'type'        => 'text',
                'placeholder' => '3128'
            ],
            'proxy.username' => [
                'title' => 'LC__LOGIN__USERNAME',
                'type'  => 'text'
            ],
            'proxy.password' => [
                'title' => 'LC__LOGIN__PASSWORD',
                'type'  => 'password'
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
     * @static
     * @return  array
     */
    public static function get_definition()
    {
        return static::$m_definition;
    } // function

    /**
     * @static
     *
     * @param  array $p_settings
     */
    public static function extend(array $p_settings = [])
    {
        self::$m_definition += $p_settings;
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
                    'isys_settings',
                    'shutdown'
                ]
            );

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
                    static::$m_settings = static::$m_dao->get_settings();
                } // if
            } // try
        } // if

        static::$m_initialized = true;
    } // function

    /**
     * Check wheather settings were initialized or not
     *
     * @static
     * @return  boolean
     */
    public static function is_initialized()
    {
        return static::$m_initialized;
    } // function

    /**
     * Load cached settings.
     *
     * @static
     *
     * @param   string $p_cachedir
     *
     * @throws  Exception
     */
    public static function load_cache($p_cachedir)
    {
        if (file_exists($p_cachedir . static::$m_cachefile))
        {
            if (is_readable($p_cachedir . static::$m_cachefile))
            {
                static::$m_settings = self::decode(file_get_contents($p_cachedir . static::$m_cachefile));
            }
            else
            {
                throw new isys_exception_filesystem($p_cachedir . static::$m_cachefile . ' not readable');
            } // if
        }
        else
        {
            throw new isys_exception_filesystem('Error: Cache file ' . $p_cachedir . static::$m_cachefile . ' does not exist');
        } // if
    } // function

    /**
     * Set a setting value.
     *
     * @static
     *
     * @param  string  $p_key
     * @param  mixed   $p_value
     * @param  boolean $p_usersettings
     */
    public static function set($p_key, $p_value, $p_usersettings = false)
    {
        self::$m_changed = true;

        if (!isset(static::$m_settings[$p_key]))
        {
            static::$m_dao->set($p_key, $p_value, $p_usersettings)
                ->apply_update();
        } // if

        static::$m_settings[$p_key] = $p_value;
    } // function

    /**
     * Remove setting.
     *
     * @static
     *
     * @param  string $p_key
     */
    public static function remove($p_key)
    {
        self::$m_changed = true;

        unset(static::$m_settings[$p_key]);
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

        return isset(static::$m_settings[$p_key]) && static::$m_settings[$p_key] !== '' ? static::$m_settings[$p_key] : $p_default;
    } // function

    /**
     * Check if the given key exists.
     *
     * @static
     *
     * @param   string $p_key
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function has($p_key)
    {
        return isset(static::$m_settings[$p_key]);
    } // function

    /**
     * (Re)generates cache. Loads the cache into static::$m_settings.
     *
     * @param   boolean $p_usersettings
     *
     * @throws  Exception
     * @return  array
     */
    public static function regenerate($p_usersettings = false)
    {
        try
        {
            static::$m_settings = static::$m_dao->get_settings($p_usersettings);

            // Write settings cache.
            self::write(
                static::get_cache_dir() . static::$m_cachefile,
                static::$m_settings
            );
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return static::$m_settings;
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
        static::$m_dao->save($p_settings, false);
    } // function

    /**
     * Override all settings.
     *
     * @param  boolean $p_usersettings
     */
    public static function force_save($p_usersettings = false)
    {
        // Write cache.
        self::cache();

        // Save to database.
        static::$m_dao->save(static::$m_settings, $p_usersettings);
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
            self::force_save();
        } // if
    } // function

    /**
     * Method for retrieving the cache directory.
     *
     * @static
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected static function get_cache_dir()
    {
        global $g_absdir;

        return $g_absdir . DS . 'temp' . DS;
    } // function

    /**
     * Writes the cache.
     *
     * @throws  Exception
     */
    protected static function cache()
    {
        try
        {
            // Write settings cache.
            self::write(static::get_cache_dir() . static::$m_cachefile, static::$m_settings);
        }
        catch (Exception $e)
        {
            throw $e;
        } // try
    } // function

    /**
     *
     * @param   string $p_file
     * @param   mixed  $p_settings
     *
     * @throws  Exception
     */
    protected static function write($p_file, $p_settings)
    {
        if (!file_exists($p_file))
        {
            if (!is_dir(dirname($p_file)))
            {
                if (!mkdir(dirname($p_file), 0777, true))
                {
                    throw new isys_exception_cache('Error writing settings cache directory: ' . dirname($p_file) . ' could not be written.', 'Settings');
                } // if
            } // if

            if (is_writable(dirname($p_file)))
            {
                touch($p_file);
                chmod($p_file, 0777);
            }
            else
            {
                throw new isys_exception_cache('Error writing settings cache: ' . $p_file . ' is not writeable.', 'Settings');
            } // if
        } // if

        if (is_writeable($p_file))
        {
            file_put_contents($p_file, self::encode($p_settings));
        }
        else
        {
            throw new isys_exception_filesystem('Error writing settings cache: ' . $p_file . ' is not writeable.');
        } // if
    } // function

    /**
     * Encode settings.
     *
     * @param   mixed $p_data
     *
     * @return  string
     */
    protected static function encode($p_data)
    {
        return isys_format_json::encode($p_data);
    } // function

    /**
     * Decode settings.
     *
     * @param   string $p_data
     *
     * @return  mixed
     */
    protected static function decode($p_data)
    {
        return isys_format_json::decode($p_data, true);
    } // function
} // class