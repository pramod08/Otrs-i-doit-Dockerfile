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
class isys_tenantsettings extends isys_settings
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
    protected static $m_cachefile = 'settings.cache';

    /**
     * Database component.
     *
     * @var  isys_component_dao_tenant_settings
     */
    protected static $m_dao;

    /**
     * Settings register.
     * Constant C__TREE__TITLE__MAXLEN is not used
     *
     * @var array
     */
    protected static $m_definition = [
        /* These settings do exist, but shall not be displayed in "administration > system settings".
        'Registry' => [
            'cmdb.registry.show_full_lists' => [
                'title' => ' ### show full list',
                'type' => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'cmdb.registry.quicksave' => [
                'title' => ' ### quicksave',
                'type' => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'cmdb.registry.sysid_readonly' => [
                'title' => ' ### SYS-ID readonly',
                'type' => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'cmdb.registry.object_dragndrop' => [
                'title' => ' ### Object drag\'n\'drop',
                'type' => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
        ],
        */

        'Display Limits'                         => [
            'cmdb.limits.obj-browser.objects-in-viewmode'     => [
                'title'       => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECTS_IN_VIEWMODE',
                'default'     => 8,
                'type'        => 'text',
                'description' => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECTS_IN_VIEWMODE_DESCRIPTION'
            ],
            'gui.lists.preload-pages'                         => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__PRELOAD_OBJECT_PAGES_TITLE',
                'type'        => 'text',
                'placeholder' => '30',
                'default'     => '30'
            ],
            'cmdb.object-browser.max-objects'                 => [
                'title'       => 'LC__SYSTEM_SETTINGS__OBJECT_BROWSER_RESULT_LIMIT',
                'type'        => 'text',
                'placeholder' => '1500'
            ],
            'cmdb.limits.port-lists-vlans'                    => [
                'title'   => 'LC__SETTINGS__CMDB__VLAN_LIMIT_IN_PORT_LISTS',
                'default' => 5,
                'type'    => 'text'
            ],
            'cmdb.limits.port-lists-layer2'                   => [
                'title'   => 'LC__SETTINGS__CMDB__LAYER2_LIMIT_IN_LOGICAL_PORT_LISTS',
                'default' => 5,
                'type'    => 'text'
            ],
            'cmdb.limits.port-overview-default-vlan-only'     => [
                'title'   => 'LC__SETTINGS__CMDB__PORT_OVERVIEW_DEFAULT_VLAN_ONLY',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'cmdb.limits.connector-lists-assigned_connectors' => [
                'title'   => 'LC__SETTINGS__CMDB__ASSIGNED_CONNECTOR_LIMIT_IN_CONNECTOR_LISTS',
                'default' => 5,
                'type'    => 'text'
            ],
            'cmdb.limits.ip-lists'                            => [
                'title'   => 'LC__SETTINGS__CMDB__IP_LISTS_LIMIT',
                'default' => 5,
                'type'    => 'text'
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__IP_LIST'   => [
            'cmdb.ip-list.cache-lifetime' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__CACHE_LIFETIME',
                'default'     => 86400,
                'placeholder' => 86400,
                'type'        => 'text'
            ],
            'cmdb.ip-list.ping-method'    => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__PING_METHOD',
                'type'    => 'select',
                'options' => [
                    'nmap'  => 'Ping via NMAP',
                    'fping' => 'Ping via FPING'
                ]
            ],
            'cmdb.ip-list.nmap-parameter' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__NMAP_PARAMETER',
                'type'    => 'select',
                'options' => [
                    'PE' => 'PE/PP/PM: ICMP echo, timestamp, and netmask request discovery probes',
                    'sP' => 'sP: Ping Scan - go no further than determining if host is online'
                ]
            ]
        ],
        'Unique checks'                          => [
            'cmdb.unique.object-title' => [
                'title'   => 'LC__UNIVERSAL__OBJECT_TITLE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.layer-2-net'  => [
                'title'   => 'LC__REPORT__VIEW__LAYER2_NETS__TITLE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.ip-address'   => [
                'title'   => 'LC__REPORT__VIEW__LAYER2_NETS__IP_ADDRESSES',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.hostname'     => [
                'title'   => 'Hostname',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ]
        ],
        'Barcodes'                               => [
            // C__BARCODE_TYPE
            'barcode.enabled' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__BARCODE_ENABLED',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'barcode.type'    => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__BARCODE_FORM',
                'type'    => 'select',
                'options' => [
                    'qr'     => 'QR-Code',
                    'code39' => 'Code39'
                ],
                'default' => 'qr'
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__GUI'       => [
            // C__GUI_VALUE__NA
            'gui.empty_value'         => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__EMPTY_VALUES',
                'type'        => 'text',
                'placeholder' => '-',
                'default'     => '-'
            ],
            // C_CMDB_LOCATION_SEPARATOR
            'gui.separator.location'  => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_SEPARATOR',
                'type'        => 'text',
                'placeholder' => ' > ',
                'default'     => ' > '
            ],
            // C_CMDB_CONNECTOR_SEPARATOR
            'gui.separator.connector' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__CONNECTOR_SEPARATOR',
                'type'        => 'text',
                'placeholder' => ' > ',
                'default'     => ' > '
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__MAXLENGTH' => [
            // C__DIALOG_PLUS__MAXLENGTH
            'maxlength.dialog_plus'      => [
                'title'       => 'Dialog-Plus',
                'type'        => 'text',
                'placeholder' => '110',
                'default'     => '110'
            ],
            // C__LIST__TITLE__MAXLEN
            'maxlength.object.lists'     => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_OBJECT_TITLE',
                'type'        => 'text',
                'placeholder' => '55',
                'default'     => '55'
            ],
            // C__LIST__LOCATION__OBJLEN
            'maxlength.location.objects' => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_OBJECTS_IN_TREE',
                'type'        => 'text',
                'placeholder' => '16',
                'default'     => '16'
            ],
            // C__LIST__LOCATION__MAXLEN
            'maxlength.location.path'    => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_LOCATION_PATH',
                'type'        => 'text',
                'placeholder' => '40',
                'default'     => '40'
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK'   => [
            // C__SAVE_DETAILED_CMDB_CHANGES
            'logbook.changes' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK__LOGGING',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default' => '1'
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__SECURITY'  => [
            'minlength.login.password' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_MINLENGTH',
                'type'        => 'text',
                'placeholder' => '4',
                'default'     => 4
            ]
        ],
        'Logging'                                => [
            'logging.system.exceptions' => [
                'title'       => 'Exception Log',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__SYSTEM__LOGGING_ENABLED'
            ],
        ],
        'Quickinfo (Link mouseover)'             => [
            'cache.quickinfo.expiration' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_EXPIRATION',
                'type'        => 'select',
                'default'     => isys_convert::DAY,
                'options'     => [
                    isys_convert::MINUTE => 'LC__UNIVERSAL__MINUTE',
                    isys_convert::HOUR   => 'LC__UNIVERSAL__HOUR',
                    isys_convert::DAY    => 'LC__UNIVERSAL__DAY'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_EXPIRATION__DESCRIPTION'
            ]
        ],
        'CMDB'             => [
            'cmdb.sysid.prefix' => [
                'title'       => 'LC__SYSTEM_SETTINGS__SYSID_PREFIX',
                'type'        => 'text',
                'default'     => 'SYSID_',
            ]
        ]
    ];

    /**
     * Settings initialized?
     *
     * @var bool
     */
    protected static $m_initialized = false;

    /**
     * Settings storage
     *
     * @var  array
     */
    protected static $m_settings = [];

    /**
     * To identify changes.
     *
     * @var  boolean
     */
    protected static $m_changed = false;

    /**
     * @param $p_settings
     */
    public static function extend($p_settings)
    {
        self::$m_definition += $p_settings;
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
            return  static::$m_settings[$p_key];
        }

        return isys_settings::get($p_key, $p_default);
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

        global $g_absdir, $g_mandator_info;

        if (is_object(isys_application::instance()->session))
        {
            if (isys_application::instance()->session->is_logged_in())
            {
                if (!isset($g_mandator_info["isys_mandator__dir_cache"]) && !$g_mandator_info["isys_mandator__dir_cache"])
                {
                    throw new Exception('Error: Cache directory in $g_mandator_info not set.');
                } // if

                return static::$m_cache_dir = $g_absdir . DS . 'temp' . DS . $g_mandator_info["isys_mandator__dir_cache"] . DS;
            }
            else
            {
                throw new Exception('Tenantsettings are only available after logging in.');
            } // if
        } // if

        return false;
    } // function

    /**
     * Load cache.
     *
     * @static
     *
     * @param   isys_component_database $p_database
     * @param   integer                 $p_tenant
     * @param   boolean                 $p_reset_dao
     *
     * @return  void
     */
    public static function initialize(isys_component_database $p_database, $p_tenant = null, $p_reset_dao = false)
    {
        isys_component_signalcollection::get_instance()
            ->connect(
                'system.shutdown',
                [
                    'isys_tenantsettings',
                    'shutdown'
                ]
            );

        if (!is_object(self::$m_dao) || $p_reset_dao)
        {
            self::$m_dao = new isys_component_dao_tenant_settings($p_database, $p_tenant);
        } // if

        $l_cache_dir = static::get_cache_dir();

        // Generate cache and load settings.
        if ($l_cache_dir)
        {
            try
            {
                if (!file_exists($l_cache_dir . static::$m_cachefile))
                {
                    static::regenerate();
                }
                else
                {
                    static::load_cache($l_cache_dir);
                } // if
            }
            catch (Exception $e)
            {
                if (isys_application::instance()->container['logger'])
                {
                    isys_application::instance()->container['logger']->addError($e->getMessage());
                }

                // Load settings from database instead of cache.
                if (!static::$m_settings)
                {
                    static::$m_settings = static::$m_dao->get_settings();
                } // if
            } // try
        } // if

        static::$m_initialized = true;
    }

    /**
     * Set a setting value.
     *
     * @param  string $p_key
     * @param  mixed  $p_value
     */
    public static function set($p_key, $p_value)
    {
        static::$m_changed = true;

        parent::set($p_key, $p_value);
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
} // class