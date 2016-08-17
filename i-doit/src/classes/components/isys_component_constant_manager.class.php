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
 * Constant manager.
 *
 * @package     i-doit
 * @subpackage  Components
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

/**
 * Constant Manager for managing the dynamic constant caches. Read out several db-tables and assign the __id  to the specified __const
 *
 * globals.inc includes the "dynamic constants" - cachefile
 *  -> include_once($g_dirs["temp"] . "const_cache.inc.php")
 */
class isys_component_constant_manager extends isys_component
{
    // Filename of the constant cache.
    const c_cache_file = "const_cache.inc.php";
    /**
     * @var isys_component_constant_manager
     */
    private static $m_instance = null;
    /**
     * Constant array.
     *
     * @var  array
     */
    private $m_constants = [];
    /**
     * Subdir for fullname.
     *
     * @var   string
     */
    private $m_subdir = "";
    /**
     * Array with cachable system tables
     *
     * @var array
     */
    private $m_system_caches = [
        "isys_const_system" => [
            "ORDER BY isys_const_system__const ASC",
            "System constants"
        ],
        "isys_language"     => [
            "",
            "Language cache"
        ],
    ];
    /**
     * Array with cachable tenant tables
     *
     * @var array
     */
    private $m_tenant_caches = [
        'isys_right'                          => [
            'ORDER BY isys_right__sort',
            'Rights'
        ],
        'isys_module'                         => [
            'ORDER BY isys_module__id',
            'Modules'
        ],
        'isysgui_catg'                        => [
            'ORDER BY isysgui_catg__sort',
            'Global categories'
        ],
        'isysgui_cats'                        => [
            'ORDER BY isysgui_cats__sort',
            'Specific categories'
        ],
        'isys_obj_type_group'                 => [
            'ORDER BY isys_obj_type_group__sort ASC',
            'Group of ObjTypes'
        ],
        'isys_obj_type'                       => [
            'ORDER BY isys_obj_type__sort ASC',
            'Object types'
        ],
        'isys_catg_global_category'           => [
            'ORDER BY isys_catg_global_category__sort ASC',
            'Category for global categories'
        ],
        'isys_guarantee_period_unit'          => [
            'ORDER BY isys_guarantee_period_unit__sort ASC',
            'isys_guarantee_period_unit (for global category)'
        ],
        'isys_logbook_level'                  => [
            '',
            'Logbook Level'
        ],
        'isys_pobj_type'                      => [
            'ORDER BY isys_pobj_type__sort ASC',
            'PowerObject type'
        ],
        'isys_net_type'                       => [
            'ORDER BY isys_net_type__sort ASC',
            'net type '
        ],
        'isys_wlan_standard'                  => [
            'ORDER BY isys_wlan_standard__sort ASC',
            'wlan / access point standard'
        ],
        'isys_wlan_function'                  => [
            'ORDER BY isys_wlan_function__sort ASC',
            'wlan / access point function'
        ],
        'isys_wlan_channel'                   => [
            'ORDER BY isys_wlan_channel__sort ASC',
            'wlan / access point channel'
        ],
        'isys_cats_prt_emulation'             => [
            'ORDER BY isys_cats_prt_emulation__sort ASC',
            'prt emulation'
        ],
        'isys_cats_prt_type'                  => [
            'ORDER BY isys_cats_prt_type__sort ASC',
            'prt type'
        ],
        'isys_client_type'                    => [
            'ORDER BY isys_client_type__sort ASC',
            'client type'
        ],
        'isys_power_fuse_type'                => [
            'ORDER BY isys_power_fuse_type__sort ASC',
            'power fuse type'
        ],
        'isys_stor_type'                      => [
            'ORDER BY isys_stor_type__sort ASC',
            'Storage device types'
        ],
        'isys_workflow_action_type'           => [
            'ORDER BY isys_workflow_action_type__id ASC',
            'Workflow Actiontypes'
        ],
        'isys_workflow_type'                  => [
            'ORDER BY isys_workflow_type__id ASC',
            'Workflow Types'
        ],
        'isys_workflow_status'                => [
            'ORDER BY isys_workflow_status__id ASC',
            'Workflow Status'
        ],
        'isys_logbook_source'                 => [
            'ORDER BY isys_logbook_source__id ASC',
            'Logbook sources'
        ],
        'isys_port_speed'                     => [
            'ORDER BY isys_port_speed__id ASC',
            'Port Speeds'
        ],
        'isys_controller_type'                => [
            'ORDER BY isys_controller_type__id ASC',
            'Controller types'
        ],
        'isys_weighting'                      => [
            'ORDER BY isys_weighting__id ASC',
            'Relation weighting'
        ],
        'isys_relation_type'                  => [
            'ORDER BY isys_relation_type__id ASC',
            'Relation types'
        ],
        'isys_contact_tag'                    => [
            'ORDER BY isys_contact_tag__id ASC',
            'Contact Tags'
        ],
        'isys_cmdb_status'                    => [
            'ORDER BY isys_cmdb_status__id ASC',
            'CMDB Status'
        ],
        'isys_ip_assignment'                  => [
            'ORDER BY isys_ip_assignment__sort ASC',
            'IP Assignment types'
        ],
        'isys_net_dhcp_type'                  => [
            'ORDER BY isys_net_dhcp_type__sort ASC',
            'DHCP Types'
        ],
        'isys_net_dhcpv6_type'                => [
            'ORDER BY isys_net_dhcpv6_type__sort ASC',
            'DHCPv6 Types'
        ],
        'isys_memory_unit'                    => [
            '',
            'Memory units'
        ],
        'isys_layer2_net_subtype'             => [
            'ORDER BY isys_layer2_net_subtype__sort ASC',
            'Network subtypes'
        ],
        'isys_vlan_management_protocol'       => [
            'ORDER BY isys_vlan_management_protocol__sort ASC',
            'VLAN Management'
        ],
        'isys_switch_role'                    => [
            'ORDER BY isys_switch_role__sort ASC',
            'Switch roles'
        ],
        'isys_switch_spanning_tree'           => [
            'ORDER BY isys_switch_spanning_tree__sort ASC',
            'Spanning tree'
        ],
        'isys_tts_type'                       => [
            'ORDER BY isys_tts_type__id ASC',
            'TTS Types'
        ],
        'isys_ipv6_assignment'                => [
            'ORDER BY isys_ipv6_assignment__id ASC',
            'IPv6 Assignments'
        ],
        'isys_ipv6_scope'                     => [
            'ORDER BY isys_ipv6_scope__id ASC',
            'IPv6 scopes'
        ],
        'isys_obj'                            => [
            'ORDER BY isys_obj__id ASC',
            'Objects'
        ],
        'isys_contract_notice_period_type'    => [
            'ORDER BY isys_contract_notice_period_type__id ASC',
            'Contract notice period type'
        ],
        'isys_backup_cycle'                   => [
            'ORDER BY isys_backup_cycle__id ASC',
            'Backup cycle'
        ],
        'isys_backup_type'                    => [
            'ORDER BY isys_backup_type__id ASC',
            'Backup type'
        ],
        'isys_frequency_unit'                 => [
            'ORDER BY isys_frequency_unit__id ASC',
            'Frequency units'
        ],
        'isys_depth_unit'                     => [
            'ORDER BY isys_depth_unit__id ASC',
            'Depth units'
        ],
        'isys_weight_unit'                    => [
            'ORDER BY isys_weight_unit__id ASC',
            'Weight units'
        ],
        'isys_ac_air_quantity_unit'           => [
            'ORDER BY isys_ac_air_quantity_unit__id ASC',
            'AC units'
        ],
        'isys_ac_refrigerating_capacity_unit' => [
            'ORDER BY isys_ac_refrigerating_capacity_unit__id ASC',
            'AC refrigerating units'
        ],
        'isys_unit_of_time'                   => [
            'ORDER BY isys_unit_of_time__id ASC',
            'Time units'
        ],
        'isys_volume_unit'                    => [
            'ORDER BY isys_volume_unit__id ASC',
            'Volume units'
        ],
        'isys_wan_capacity_unit'              => [
            'ORDER BY isys_wan_capacity_unit__id ASC',
            'WAN Speed units'
        ],
        'isys_raid_type'                      => [
            'ORDER BY isys_raid_type__id ASC',
            'Raid types'
        ],
        'isys_virtual_network_type'           => [
            'ORDER BY isys_virtual_network_type__id ASC',
            'Virtual network types'
        ],
        'isys_virtual_storage_type'           => [
            'ORDER BY isys_virtual_storage_type__id ASC',
            'Virtual storage types'
        ],
        'isys_port_mode'                      => [
            'ORDER BY isys_port_mode__id ASC',
            'Port modes'
        ],
        'isys_port_duplex'                    => [
            'ORDER BY isys_port_duplex__id ASC',
            'Port duplex'
        ],
        'isys_setting_key'                    => [
            'ORDER BY isys_setting_key__id ASC',
            'Mandator-specific setting keys'
        ],
        'isys_snmp_community'                 => [
            'ORDER BY isys_snmp_community__id ASC',
            'SNMP communities'
        ],
        'isys_cluster_type'                   => [
            'ORDER BY isys_cluster_type__id ASC',
            'Cluster types'
        ],
        'isys_layer2_net_type'                => [
            'ORDER BY isys_layer2_net_type__id ASC',
            'Layer-2 net types'
        ],
        'isys_currency'                       => [
            'ORDER BY isys_currency__id ASC',
            'Currency'
        ],
        'isysgui_catg_custom'                 => [
            'ORDER BY isysgui_catg_custom__sort',
            'Custom categories'
        ],
        'isys_model_manufacturer'             => [
            'ORDER BY isys_model_manufacturer__sort',
            'Model manufacturer'
        ],
        'isys_interval'                       => [
            'ORDER BY isys_interval__sort',
            'Time intervals'
        ],
        'isys_contract_payment_period'        => [
            'ORDER BY isys_contract_payment_period__sort',
            'Time intervals'
        ],
        'isys_catg_application_type'          => [
            'ORDER BY isys_catg_application_type__sort',
            'Application types'
        ],
        'isys_catg_application_priority'      => [
            'ORDER BY isys_catg_application_priority__sort',
            'Application types'
        ],
        'isys_catg_identifier_type'           => [
            'ORDER BY isys_catg_identifier_type__sort',
            'Custom identifier types'
        ],
        'isys_connection_type'                => [
            'ORDER BY isys_connection_type__sort',
            'Connector types'
        ],
        'isys_interface'                      => [
            'ORDER BY isys_interface__sort',
            'Connector interface'
        ]
    ];

    /**
     * Singleton instance getter
     *
     * @return isys_component_constant_manager
     */
    public static function instance($p_subdir = null)
    {
        if (!self::$m_instance)
        {
            self::$m_instance = new self($p_subdir);
        }

        return self::$m_instance;
    }

    /**
     * Build the filename and full path for the const cache and create the sub dir (if nessesary).
     *
     * @return  string
     */
    public function get_fullpath_name()
    {
        return $this->get_fullpath() . self::c_cache_file;
    }

    /**
     * @return string
     */
    public function get_dcs_path()
    {
        return isys_glob_get_temp_dir() . self::c_cache_file;
    } // function

    /**
     * Method for returning the full path to the temp dir (including mandator sub-dir, if set).
     *
     * @return  string
     */
    public function get_fullpath()
    {
        $l_full_path = isys_glob_get_temp_dir();

        if (!empty($this->m_subdir))
        {
            $l_full_path .= $this->m_subdir . "/";
        } // if

        return $l_full_path;
    }

    /**
     * Build the filename and full path for the const cache.
     *
     * @param   string $p_subdir
     *
     * @return  string
     */
    public function set_subdir($p_subdir)
    {
        $this->m_subdir = $p_subdir;

        return ($this->get_fullpath_name());
    } // function

    /**
     * Clear constant cache
     */
    public function clear_dcm_cache()
    {
        if (file_exists($this->get_fullpath_name()) && is_writable($this->get_fullpath_name()))
        {
            unlink($this->get_fullpath_name());
        }
    } // function

    /**
     * Creates the dynamic consts mandator cache-file. Fetches all records matching the "criteria", creates a PHP file with const definition for including.
     */
    public function create_dcm_cache()
    {
        global $g_comp_database;

        if (!file_exists($this->get_fullpath() . "/"))
        {
            // The directory doesn't exist.  Recurse, passing in the parent directory so that it gets created.
            mkdir($this->get_fullpath() . "/", 0777, true);
        } // if

        if (is_writeable($this->get_fullpath()))
        {
            // Evaluate filename and open it.
            $l_cachefile = fopen($this->get_fullpath_name(), "w");

            if ($l_cachefile)
            {
                fputs($l_cachefile, "<?php\n");
                fputs($l_cachefile, "// Dynamic constants for mandator - cache with constants.\n");

                if (is_object($g_comp_database))
                {
                    foreach ($this->m_tenant_caches as $l_tbl => $l_data)
                    {
                        fputs($l_cachefile, $this->read_table_constants($l_tbl, $l_data[1], $l_data[0], $g_comp_database));
                    } // foreach
                } // if

                fclose($l_cachefile);
                chmod($this->get_fullpath_name(), 0777);
            } // if
        }
        else
        {
            throw new isys_exception_filesystem($this->get_fullpath() . ' is not writeable.');
        }

        return $this;
    } // function

    /**
     * Creates the constcache-file for the idoit_system db.
     * Fetches all records matching the "criteria", creates a PHP file with const definition for including.
     *
     * @return isys_component_constant_manager
     */
    public function create_dcs_cache()
    {
        global $g_comp_database_system, $g_db_system, $g_config;

        $l_tempdir = isys_glob_get_temp_dir();

        if (is_writeable($l_tempdir))
        {
            $l_system_cache = $l_tempdir . self::c_cache_file;

            if (!file_exists($l_system_cache) || is_writeable($l_system_cache))
            {
                // Evaluate filename and open it.
                $l_cachefile = fopen($l_system_cache, "w");

                if ($l_cachefile)
                {
                    fputs($l_cachefile, "<?php\n");
                    fputs($l_cachefile, "// Dynamic constants for the " . $g_db_system["name"] . " db - cache with constants.\n");

                    if (is_object($g_comp_database_system))
                    {
                        foreach ($this->m_system_caches as $l_tbl => $l_data)
                        {
                            fputs($l_cachefile, $this->read_table_constants($l_tbl, $l_data[1], $l_data[0], $g_comp_database_system));
                        } // foreach
                    } // if

                    fputs(
                        $l_cachefile,
                        "\n// Address cache for i-doit handlers, which are started by php-cli mode (there isn't any apache variable available then).\n" . "define('C__HTTP_HOST','" . $_SERVER["HTTP_HOST"] . "');\n" . "define('C__HTTPS_ENABLED'," . ($_SERVER["HTTPS"] ? 'true' : 'false') . ");\n" . "define('C__WWW_DIR','" . $g_config['www_dir'] . "');\n" . "define('C__DOCUMENT_ROOT','" . $_SERVER["DOCUMENT_ROOT"] . "');\n" . "define('C__SERVER_ADDR','" . $_SERVER["SERVER_ADDR"] . "');\n" . "define('C__SERVER_PORT','" . $_SERVER['SERVER_PORT'] . "');\n" . "define('C__SERVER_NAME','" . $_SERVER["SERVER_NAME"] . "');\n"
                    );

                    fclose($l_cachefile);
                    chmod($this->get_fullpath_name(), 0777);
                } // if
            }
            else
            {
                throw new isys_exception_filesystem('Error: ' . $l_system_cache . ' is not writeable.');
            }
        }
        else
        {
            throw new isys_exception_filesystem('Error: ' . $l_tempdir . ' is not writeable.');
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function include_dcs()
    {
        $l_dcs_file = $this->get_dcs_path();

        try
        {
            if (!file_exists($l_dcs_file))
            {
                /* Creates temp/const_cache.inc.php */
                $this->create_dcs_cache();
            } // if

            if (file_exists($l_dcs_file) && is_readable($l_dcs_file))
            {
                include_once($l_dcs_file);
            }
            else throw new Exception('Error: Cachefile exists but is not readable: ' . $l_dcs_file);

        }
        catch (isys_exception_filesystem $e)
        {
            /**
             * DCS Fallback mode
             */
            $this->fallback_dcs();

            /**
             * @todo send message to system log saying "Tenant temp file $l_dcs_file is not accesible due to write permission problems in dirname($l_dcs_file)"
             */
        }

        return $this;
    } // function

    /**
     * @param $p_cache_dir
     *
     * @return bool
     * @throws Exception
     */
    public function include_dcm($p_cache_dir)
    {
        if ($p_cache_dir)
        {
            $l_file = $this->set_subdir($p_cache_dir);

            try
            {
                if (!file_exists($l_file))
                {
                    $this->create_dcm_cache();
                }

                if (is_readable($l_file))
                {
                    if (include_once($l_file))
                    {
                        return true;
                    }
                }
                else throw new Exception('Error: Cachefile exists but is not readable: ' . $l_file);
            }
            catch (isys_exception_filesystem $e)
            {
                $this->fallback_dcm();

                /**
                 * @todo send message to system log saying "Temp directory $p_cache_dir is not writable"
                 */
            }

        }
        else throw new Exception('Cache directory needed for creating the tenant cache.');

        return false;
    } // function

    /**
     * Creates the constcache for the give table. Fetches all records matching the "criteria", creates a PHP file with const definition for including.
     * 20050622 -> extended parameter $p_db_source by separate the db into idoit_system and the mandator db(s).
     *
     * @param   string                  $p_table_name
     * @param   string                  $p_headline
     * @param   string                  $p_sql_order_by
     * @param   isys_component_database $p_db_source
     *
     * @return  string
     */
    private function read_table_constants($p_table_name, $p_headline, $p_sql_order_by, $p_db_source)
    {
        if (is_object($p_db_source))
        {
            $l_check     = "SHOW TABLES LIKE '" . $p_table_name . "'";
            $l_check_res = $p_db_source->query($l_check);

            if ($p_db_source->num_rows($l_check_res) == 0)
            {
                return null;
            } // if

            // Languages.
            $l_sql = "SELECT * FROM $p_table_name WHERE !ISNULL(" . $p_table_name . "__const)";

            if ($p_sql_order_by != "")
            {
                $l_sql .= " $p_sql_order_by;";
            } // if

            try
            {
                $l_dbres = $p_db_source->query($l_sql);

                // File header (placed at this position to remember empty tables by viewing the const_cache.
                $l_retcode = "\n// Constants: $p_headline (from table '" . $p_table_name . "' in database '" . $p_db_source->get_db_name() . "').\n";

                if ($p_db_source->num_rows($l_dbres))
                {
                    while ($l_entry = $p_db_source->fetch_array($l_dbres))
                    {
                        $l_ident = $l_entry[$p_table_name . "__const"];

                        if (!isset($this->m_constants[$l_ident]))
                        {
                            if (isset($l_entry[$p_table_name . "__value"]))
                            {
                                $l_data = $l_entry[$p_table_name . "__value"];
                            } // if
                            else
                            {
                                $l_data = $l_entry[$p_table_name . "__id"];
                            }

                            // Ignore empty.
                            if (!empty($l_ident))
                            {
                                $l_retcode .= "define('$l_ident', $l_data);\n";
                            } // if

                            $this->m_constants[$l_ident] = true;
                        } // if
                    } // while
                } // if
            }
            catch (isys_exception_database $e)
            {
                return null;
            } // try

            return $l_retcode;
        } // if

        return null;
    }

    /**
     * Fallback mode for non accessible tenant temp directories
     *
     *  - Retrieves all constants as PHP define statements
     *  - Evaluates them with eval()
     *
     * @return $this
     */
    private function fallback_dcm()
    {
        global $g_comp_database;

        if (is_object($g_comp_database))
        {
            $l_eval_code = '';
            foreach ($this->m_tenant_caches as $l_tbl => $l_data)
            {
                $l_eval_code .= $this->read_table_constants($l_tbl, $l_data[1], $l_data[0], $g_comp_database);
            } // foreach

            if ($l_eval_code)
            {
                eval($l_eval_code);
            }
        } // if

        return $this;
    }

    /**
     *  Fallback mode for non accessible system temp directory /temp/
     *
     *  - Retrieves all constants as PHP define statements
     *  - Evaluates them with eval()
     *
     * @return $this
     */
    private function fallback_dcs()
    {
        global $g_comp_database_system;

        if (is_object($g_comp_database_system))
        {
            $l_eval_code = '';
            foreach ($this->m_system_caches as $l_tbl => $l_data)
            {
                $l_eval_code .= $this->read_table_constants($l_tbl, $l_data[1], $l_data[0], $g_comp_database_system);
            } // foreach

            if ($l_eval_code)
            {
                eval($l_eval_code);
            }
        } // if

        return $this;
    }

    /**
     * @param $p_subdir
     */
    private function __construct($p_subdir)
    {
        if ($p_subdir)
        {
            $this->set_subdir($p_subdir);
        }
    }
} // class