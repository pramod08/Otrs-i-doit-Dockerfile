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
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * OCS import handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_handler_ocs extends isys_handler
{

    /**
     * @var array
     */
    private $m_categories;
    /**
     * @var isys_component_dao_ocs
     */
    private $m_comp_dao_ocs;
    /**
     * @var array
     */
    private $m_objtype_blacklist = [
        C__OBJTYPE__PERSON,
        C__OBJTYPE__PERSON_GROUP,
        C__OBJTYPE__ORGANIZATION
    ];
    /**
     * @var isys_component_database
     */
    private $m_ocs_db;

    /**
     * Check if pattern prefix exists in TAG or in NAME
     *
     * @param $p_prefix
     * @param $p_tag
     * @param $p_name
     *
     * @return bool
     */
    private static function check_pattern_for_objtype($p_prefix, $p_tag, $p_name)
    {
        if (strpos($p_prefix, '%') !== false)
        {
            $l_search_string = '';
            $l_pattern       = '';
            $p_prefix        = preg_quote($p_prefix);
            if (strpos($p_prefix, '%') > 0)
            {
                $l_search_string = '^';
            } // if

            $l_search_string .= '(' . str_replace('%', '.*', trim($p_prefix, '%')) . ')';
            $l_pattern .= "/" . $l_search_string . "/";
            if (preg_match($l_pattern, $p_tag) !== 0 || preg_match($l_pattern, $p_name) !== 0)
            {
                return true;
            } // if
        }
        elseif (strpos($p_tag, $p_prefix) === 0 || strpos($p_name, $p_prefix) === 0)
        {
            return true;
        } // if
        return false;
    }

    /**
     * destroy this
     *
     */
    public function __destruct()
    {
        if (empty($_SERVER['HTTP_HOST']))
        {
            $this->logout();
        }
        else
        {
            unset($this->m_dao);
            unset($this->m_categories);
            unset($this->m_comp_dao_ocs);
        }
    }

    /**
     * Initialize handler
     *
     * @return bool
     */
    public function init()
    {
        global $g_comp_session;

        verbose("------------------------------------------------");
        verbose(C__CONSOLE_LOGO__IDOIT . " OCS Import");
        verbose("------------------------------------------------");

        verbose(C__COLOR__GREEN . "OCS-Handler initialized " . C__COLOR__NO_COLOR . "(" . date("Y-m-d H:i:s") . ")");

        if ($g_comp_session->is_logged_in())
        {

            /* Process import */
            try
            {

                $this->set_ocs_comp_dao();

                $this->set_ocs_db();

                $this->m_categories = [
                    "operating_system"           => "operating_system",
                    C__CATG__CPU                 => "cpu",
                    C__CATG__MEMORY              => "memory",
                    C__CATG__APPLICATION         => "application",
                    C__CATG__NETWORK             => "net",
                    C__CATG__STORAGE             => "stor",
                    C__CATG__DRIVE               => "drive",
                    C__CATG__GRAPHIC             => "graphic",
                    C__CATG__SOUND               => "sound",
                    C__CATG__MODEL               => "model",
                    C__CATG__UNIVERSAL_INTERFACE => "ui"
                ];

                $this->process();
            }
            catch (Exception $e)
            {
                error($e->getMessage());
            }

            return true;
        }

        return false;
    }

    /**
     * Prints out the usage of the import handler
     *
     */
    public function usage($p_level = null)
    {
        switch ($p_level)
        {
            case 1:
                $l_error = "Wrong usage!\n";
                $l_error .= "Missing database for parameter -db.\n\n";
                $l_error .= "Example:\n";
                $l_error .= "php5 controller.php -m ocs -db dbschema\n";
                $l_error .= "Optional parameters:\n";
                $l_error .= "-x Overwrite categories hostaddress and port\n";
                $l_error .= "-t Default objecttype constant: php5 controller.php -m ocs -db dbschema -t C__OBJTYPE__SERVER\n";
                $l_error .= "-f File with hostnames.\nExample: php5 controller.php -m ocs -db dbschema -f file.txt\n";
                $l_error .= "-h Hostnames which will be imported.\nExample: php5 controller.php -m ocs -db dbschema -h Hostname1,Hostname2\n";
                $l_error .= "-s Switch to import snmp devices.\nExample: php5 controller.php -m ocs -db dbschema -s -h Hostname1,Hostname2\n";
                $l_error .= "-c Categories which will be imported.\nExample: php5 controller.php -m ocs -db dbschema -c drive,ui,sound,application,memory,model,graphic,net,stor,operating_system,cpu\n";
                $l_error .= "-l Activate file logging. 1 = Normal Log; 2 = Debug Log\nExample: php5 controller.php -m ocs -db dbschema -l 1\n";

                break;
            case 2:
                // no hosts selected
                $l_error = "Please select one or more hosts to be imported.\n";
                break;
            default:
                $l_error = '';
                $l_error .= "Example:\n";
                $l_error .= "php5 controller.php -m ocs -db dbschema\n";
                $l_error .= "Optional parameters:\n";
                $l_error .= "-x Overwrite categories hostaddress and port\n";
                $l_error .= "-t Default objecttype constant: php5 controller.php -m ocs -db dbschema -t C__OBJTYPE__SERVER\n";
                $l_error .= "-f File with hostnames.\nExample: php5 controller.php -m ocs -db dbschema -f file.txt\n";
                $l_error .= "-h Hostnames which will be imported.\nExample: php5 controller.php -m ocs -db dbschema -h Hostname1,Hostname2\n";
                $l_error .= "-s Switch to import snmp devices.\nExample: php5 controller.php -m ocs -db dbschema -s -h Hostname1,Hostname2\n";
                $l_error .= "-c Categories which will be imported.\nExample: php5 controller.php -m ocs -db dbschema -c drive,ui,sound,application,memory,model,graphic,net,stor,operating_system,cpu\n";
                $l_error .= "-l Datei logging aktivieren.\n1 = Normal Log\n2 = Debug Log\nExample: php5 controller.php -m ocs -db dbschema -l 1\n";
                break;
        }

        error($l_error);
    }

    /**
     * Start the import process
     *
     * @return bool
     * @version Van Quyen Hoang <qhoang@i-doit.org> 20.06.2012
     */
    public function process()
    {
        global $argv;
        global $g_comp_database;
        global $g_comp_template_language_manager;
        global $g_comp_session;

        /**
         * Import start time, used to identify the updated objects within this import
         */
        $startTime = microtime(true);

        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request, if enabled via checkbox.
         */
        \idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_config_obj_types = [];

        $l_regServer  = isys_tenantsettings::get('ocs.prefix.server');
        $l_regClient  = isys_tenantsettings::get('ocs.prefix.client');
        $l_regSwitch  = isys_tenantsettings::get('ocs.prefix.switch');
        $l_regRouter  = isys_tenantsettings::get('ocs.prefix.router');
        $l_regPrinter = isys_tenantsettings::get('ocs.prefix.printer');

        if ($l_regServer != '')
        {
            $l_config_obj_types[C__OBJTYPE__SERVER] = $l_regServer;
        } // if

        if ($l_regClient != '')
        {
            $l_config_obj_types[C__OBJTYPE__CLIENT] = $l_regClient;
        } // if

        if ($l_regSwitch != '')
        {
            $l_config_obj_types[C__OBJTYPE__SWITCH] = $l_regSwitch;
        } // if

        if ($l_regRouter != '')
        {
            $l_config_obj_types[C__OBJTYPE__ROUTER] = $l_regRouter;
        } // if

        if ($l_regPrinter != '')
        {
            $l_config_obj_types[C__OBJTYPE__PRINTER] = $l_regPrinter;
        } // if

        $l_regApp         = isys_tenantsettings::get('ocs.application');
        $l_regAppAssign   = isys_tenantsettings::get('ocs.application.assignment');
        $l_logb_active    = isys_tenantsettings::get('ocs.logbook.active');
        $l_cmdb_status_id = isys_tenantsettings::get('import.hinventory.default_status', C__CMDB_STATUS__IN_OPERATION);

        $l_daoCMDB = new isys_cmdb_dao($g_comp_database);

        $l_daoOCS   = $this->m_comp_dao_ocs;
        $l_dao_logb = new isys_module_logbook();

        // selected_ocsdb
        $l_settings = $l_daoOCS->getOCSDB($this->m_ocs_db);

        if (empty($l_settings["isys_ocs_db__host"]))
        {
            throw new Exception(" ** " . C__COLOR__RED . "You have to configure the OCS connector first." . C__COLOR__NO_COLOR);
        } // if

        $l_ocsdb = isys_component_database::get_database(
            "mysqli",
            $l_settings["isys_ocs_db__host"],
            $l_settings["isys_ocs_db__port"],
            $l_settings["isys_ocs_db__user"],
            isys_helper_crypt::decrypt($l_settings["isys_ocs_db__pass"]),
            $l_settings["isys_ocs_db__schema"]
        );

        $l_daoOCS = new isys_component_dao_ocs($l_ocsdb);

        $l_gui               = false;
        $l_hosts_key         = null;
        $l_file_key          = null;
        $l_objType_snmp      = null;
        $l_overwrite_ip_port = false;
        $l_log_lvl           = 0;
        $l_objtype_arr       = $l_objtype_snmp_arr = $l_snmpIDs = $l_hardwareIDs = [];

        if (!empty($_SERVER['HTTP_HOST']))
        {
            $l_cached = $this->m_categories;
            unset($this->m_categories);
            foreach ($l_cached AS $l_key => $l_val)
            {
                $this->m_categories[] = $l_val;
            }
            // Import one ocs object
            if (isys_glob_get_param("hardwareID"))
            {
                $l_snmp = isys_glob_get_param('snmp');
                if ($l_snmp && $l_snmp !== 'false')
                {
                    $l_snmpIDs[] = isys_glob_get_param("hardwareID");
                }
                else
                {
                    $l_hardwareIDs[] = isys_glob_get_param("hardwareID");
                }

                $l_objType = $l_objType_snmp = isys_glob_get_param("objTypeID");
            }
            else
            {
                $l_hardwareIDs      = isys_glob_get_param("id");
                $l_snmpIDs          = isys_glob_get_param("id_snmp");
                $l_objtype_arr      = isys_glob_get_param("objtypes");
                $l_objtype_snmp_arr = isys_glob_get_param("objtypes_snmp");

                $l_objType      = isys_tenantsettings::get('ocs.default.objtype');
                $l_objType_snmp = null;
            }
            // Default Object type as string
            $l_default_objtype = _L($l_daoCMDB->get_objtype_name_by_id_as_string($l_objType));
            $l_log_lvl         = isys_glob_get_param("ocs_logging");

            $l_categories = isys_glob_get_param("category");

            if (!is_array($l_objtype_snmp_arr) && isys_format_json::is_json($l_objtype_snmp_arr))
            {
                $l_objtype_snmp_arr = isys_format_json::decode($l_objtype_snmp_arr);
            }

            if (!is_array($l_objtype_arr) && isys_format_json::is_json($l_objtype_arr))
            {
                $l_objtype_arr = isys_format_json::decode($l_objtype_arr);
            }

            if (isys_format_json::is_json($l_categories))
            {
                $l_categories = isys_format_json::decode($l_categories);
            }

            if (!is_array($l_hardwareIDs) && isys_format_json::is_json($l_hardwareIDs))
            {
                $l_hardwareIDs = isys_format_json::decode($l_hardwareIDs);
            }

            if (!is_array($l_snmpIDs) && isys_format_json::is_json($l_snmpIDs))
            {
                $l_snmpIDs = isys_format_json::decode($l_snmpIDs);
            }

            $l_overwrite_ip_port = (bool) isys_glob_get_param("overwrite_ip_port");

            $l_gui = true;
            define("ISYS_VERBOSE", true);
        }
        elseif (is_array($argv))
        {
            if (array_search('--help', $argv) !== false || array_search('--h', $argv) !== false)
            {
                $this->usage();
                die;
            } // if

            $l_file_key    = array_search("-f", $argv);
            $l_hosts_key   = array_search("-h", $argv);
            $l_snmp_switch = array_search("-s", $argv);
            $l_hardware    = true;
            $l_snmp        = ($l_snmp_switch !== false) ? true : false;
            $l_hardwareIDs = [];

            if ($l_file_key !== false && $l_file_key !== null)
            {
                $l_file = $argv[$l_file_key + 1];
                if (!file_exists($l_file)) throw new Exception("Input file " . $l_file . " not found");

                $l_arHosts = explode("\n", file_get_contents($l_file));

                $l_temp = [];

                foreach ($l_arHosts as $l_hostname)
                {
                    $l_hostname = trim($l_hostname);
                    if ($l_hostname == "") continue;
                    $l_temp[] = $l_hostname;
                }

                if ($l_hardware)
                {
                    $l_resHW = $l_daoOCS->getHardwareIDs($l_temp);

                    while ($l_row = $l_resHW->get_row())
                    {
                        $l_hardwareIDs[] = $l_row["ID"];
                    }
                }
                if ($l_snmp)
                {
                    $l_resHW = $l_daoOCS->getHardwareSnmpIDs($l_temp);

                    while ($l_row = $l_resHW->get_row())
                    {
                        $l_snmpIDs[] = $l_row["ID"];
                    }
                }

            }
            elseif ($l_hosts_key !== false && $l_hosts_key !== null)
            {
                $l_hosts = explode(',', $argv[$l_hosts_key + 1]);

                if ($l_hardware)
                {
                    $l_resHW = $l_daoOCS->getHardwareIDs($l_hosts);

                    while ($l_row = $l_resHW->get_row())
                    {
                        $l_hardwareIDs[] = $l_row["ID"];
                    }
                }
                if ($l_snmp)
                {
                    $l_resHW = $l_daoOCS->getHardwareSnmpIDs($l_hosts);

                    while ($l_row = $l_resHW->get_row())
                    {
                        $l_snmpIDs[] = $l_row["ID"];
                    }
                }
            }
            else
            {
                if ($l_hardware)
                {
                    $l_res = $l_daoOCS->getHardware();

                    while ($l_row = $l_res->get_row())
                    {
                        $l_hardwareIDs[] = $l_row["ID"];
                    }
                }
                if ($l_snmp)
                {
                    $l_resHW = $l_daoOCS->getHardwareSnmp();

                    $l_already_set = [];
                    while ($l_row = $l_resHW->get_row())
                    {
                        if (isset($l_already_set[$l_row['NAME']])) continue;

                        $l_already_set[$l_row['NAME']] = true;
                        $l_snmpIDs[]                   = $l_row["ID"];
                    }
                }
            }

            $l_categories_key = array_search("-c", $argv);
            if ($l_categories_key)
            {
                $l_import_categories = explode(',', $argv[$l_categories_key + 1]);
                $l_cached_arr        = array_flip($this->m_categories);
                unset($this->m_categories);
                foreach ($l_cached_arr AS $l_key => $l_val)
                {
                    if (in_array($l_key, $l_import_categories))
                    {
                        $this->m_categories[] = $l_val;
                    }
                }
            }
            else
            {
                foreach ($this->m_categories AS $l_key => $l_val)
                {
                    $this->m_categories[] = $l_val;
                }
            }

            /* Is an standard object type is setted */
            if (is_numeric(($l_standardObjType = array_search('-t', $argv))))
            {
                if (is_string($argv[$l_standardObjType + 1]))
                {

                    /* Retrieve object type by constant */
                    $l_objType = $l_daoCMDB->get_object_type(null, $argv[$l_standardObjType + 1], null);

                    /* Set $l_objType */
                    if (is_array($l_objType))
                    {
                        $l_objType = $l_objType['isys_obj_type__id'];
                    }
                }
            }
            else
            {
                /* Retrieve standard object type by i-doit registry */
                $l_objType = isys_tenantsettings::get('ocs.default.objtype');
            }

            // Default Object type as string
            $l_default_objtype = _L($l_daoCMDB->get_objtype_name_by_id_as_string($l_objType));

            $l_categories = $this->m_categories;

            if (array_search('-x', $argv) !== false)
            {
                $l_overwrite_ip_port = true;
            } // if

            if (array_search('-l', $argv) !== false)
            {
                $l_log_lvl = $argv[array_search('-l', $argv) + 1];
            } // if
        }
        else
            return false;

        $l_logging = true;

        switch ($l_log_lvl)
        {
            case 2:
                $l_loglevel = isys_log::C__ALL;
                break;
            case 1:
                $l_loglevel = isys_log::C__ALL & ~isys_log::C__DEBUG;
                break;
            default:
                $l_logging = false;
                break;
        }

        if (count($l_hardwareIDs) == 0 && count($l_snmpIDs) == 0)
        {
            $this->usage(2);

            return false;
        }

        // initialize objects once
        $l_daoCableCon = new isys_cmdb_dao_cable_connection($g_comp_database);

        if ($l_logging)
        {
            $l_log = isys_factory_log::get_instance('import_ocs')
                ->set_verbose_level($l_loglevel)
                ->set_log_level($l_loglevel);
        }
        else
        {
            $l_log = isys_log_null::get_instance();
        } // if

        $l_log->flush_log();
        /**
         * Typehinting:
         *
         * @var  $l_daoGl              isys_cmdb_dao_category_g_global
         * @var  $l_daoNet_s           isys_cmdb_dao_category_s_net
         * @var  $l_daoApp             isys_cmdb_dao_category_g_application
         * @var  $l_daoOS              isys_cmdb_dao_category_g_operating_system
         * @var  $l_daoApp_s           isys_cmdb_dao_category_s_application
         * @var  $l_daoPort            isys_cmdb_dao_category_g_network_port
         * @var  $l_daoIP              isys_cmdb_dao_category_g_ip
         */
        $l_daoGl    = isys_cmdb_dao_category_g_global::instance($g_comp_database);
        $l_daoNet_s = isys_cmdb_dao_category_s_net::instance($g_comp_database);
        $l_daoApp   = isys_cmdb_dao_category_g_application::instance($g_comp_database);
        $l_daoOS    = isys_cmdb_dao_category_g_operating_system::instance($g_comp_database);
        $l_daoApp_s = isys_cmdb_dao_category_s_application::instance($g_comp_database);
        $l_daoPort  = isys_cmdb_dao_category_g_network_port::instance($g_comp_database);
        $l_daoIP    = isys_cmdb_dao_category_g_ip::instance($g_comp_database);

        $l_add_cpu             = false;
        $l_add_model           = false;
        $l_add_memory          = false;
        $l_add_application     = false;
        $l_add_graphic         = false;
        $l_add_sound           = false;
        $l_add_storage         = false;
        $l_add_drive           = false;
        $l_add_net             = false;
        $l_add_ui              = false;
        $l_add_os              = false;
        $l_add_virtual_machine = false;

        $l_category_selection_as_string = '';
        if (array_search('operating_system', $l_categories) !== false)
        {
            $l_add_os = true;
            $l_category_selection_as_string .= 'Operating System, ';
        }

        if (array_search(C__CATG__CPU, $l_categories) !== false || (array_key_exists(C__CATG__CPU, $l_categories) !== false && !$l_gui))
        {
            $l_add_cpu = true;
            $l_daoCPU  = new isys_cmdb_dao_category_g_cpu($g_comp_database);
            $l_category_selection_as_string .= 'CPU, ';
        }
        if (array_search(C__CATG__MEMORY, $l_categories) !== false || (array_key_exists(C__CATG__MEMORY, $l_categories) !== false && !$l_gui))
        {
            $l_add_memory = true;
            $l_daoMemory  = new isys_cmdb_dao_category_g_memory($g_comp_database);
            $l_category_selection_as_string .= 'Memory, ';
        }

        if (array_search(C__CATG__APPLICATION, $l_categories) !== false || (array_key_exists(C__CATG__APPLICATION, $l_categories) !== false && !$l_gui))
        {
            $l_add_application = true;
            $l_connection      = isys_cmdb_dao_connection::instance($g_comp_database);
            $l_relation_dao    = isys_cmdb_dao_category_g_relation::instance($g_comp_database);
            $l_relation_data   = $l_relation_dao->get_relation_type(C__RELATION_TYPE__SOFTWARE, null, true);
            $l_category_selection_as_string .= 'Software assignment, ';
        }

        if (array_search(C__CATG__NETWORK, $l_categories) !== false || (array_key_exists(C__CATG__NETWORK, $l_categories) !== false && !$l_gui))
        {
            $l_add_net            = true;
            $l_dao_interface      = new isys_cmdb_dao_category_g_network_interface($g_comp_database);
            $l_dao_power_consumer = new isys_cmdb_dao_category_g_power_consumer($g_comp_database);
            $l_category_selection_as_string .= 'Network, ';
        }

        if (array_search(C__CATG__STORAGE, $l_categories) !== false || (array_key_exists(C__CATG__STORAGE, $l_categories) !== false && !$l_gui))
        {
            $l_add_storage = true;
            $l_daoStor     = new isys_cmdb_dao_category_g_stor($g_comp_database);
            $l_category_selection_as_string .= 'Devices, ';
        }

        if (array_search(C__CATG__GRAPHIC, $l_categories) !== false || (array_key_exists(C__CATG__GRAPHIC, $l_categories) !== false && !$l_gui))
        {
            $l_add_graphic = true;
            $l_dao_graphic = new isys_cmdb_dao_category_g_graphic($g_comp_database);
            $l_category_selection_as_string .= 'Graphic card, ';
        }

        if (array_search(C__CATG__SOUND, $l_categories) !== false || (array_key_exists(C__CATG__SOUND, $l_categories) !== false && !$l_gui))
        {
            $l_add_sound = true;
            $l_dao_sound = new isys_cmdb_dao_category_g_sound($g_comp_database);
            $l_category_selection_as_string .= 'Sound card, ';
        }

        if (array_search(C__CATG__MODEL, $l_categories) !== false || (array_key_exists(C__CATG__MODEL, $l_categories) !== false && !$l_gui))
        {
            $l_add_model   = true;
            $l_daoModel    = new isys_cmdb_dao_category_g_model($g_comp_database);
            $l_daoStacking = new isys_cmdb_dao_category_g_stacking($g_comp_database);
            $l_category_selection_as_string .= 'Model, ';
        }

        if (array_search(C__CATG__UNIVERSAL_INTERFACE, $l_categories) !== false || (array_key_exists(C__CATG__UNIVERSAL_INTERFACE, $l_categories) !== false && !$l_gui))
        {
            $l_add_ui = true;
            $l_daoUI  = new isys_cmdb_dao_category_g_ui($g_comp_database);
            $l_category_selection_as_string .= 'Interface, ';
        }

        if (array_search(C__CATG__DRIVE, $l_categories) !== false || (array_key_exists(C__CATG__DRIVE, $l_categories) !== false && !$l_gui))
        {
            $l_add_drive = true;
            $l_dao_drive = new isys_cmdb_dao_category_g_drive($g_comp_database);
            $l_category_selection_as_string .= 'Drives, ';
        }

        if (array_search(C__CATG__VIRTUAL_MACHINE, $l_categories) !== false || (array_key_exists(C__CATG__VIRTUAL_MACHINE, $l_categories) !== false && !$l_gui))
        {
            $l_add_virtual_machine = true;
            $l_dao_vm              = new isys_cmdb_dao_category_g_virtual_machine($g_comp_database);
            $l_dao_gs              = new isys_cmdb_dao_category_g_guest_systems($g_comp_database);
        }

        if ($l_category_selection_as_string != '')
        {
            $l_log->info('Following categories are selected for the import: ' . $l_category_selection_as_string);
        }
        else
        {
            $l_log->warning('No categories selected for the import.');
        } // if

        try
        {
            $l_log->info('Preparing environment data for the import.');
            $l_daoCMDB->begin_update();

            $l_capacityUnitMB         = $l_daoCMDB->retrieve("SELECT isys_memory_unit__id FROM isys_memory_unit WHERE isys_memory_unit__const = 'C__MEMORY_UNIT__MB'")
                ->get_row_value('isys_memory_unit__id');
            $l_frequency_unit         = $l_daoCMDB->retrieve(
                "SELECT isys_frequency_unit__id FROM isys_frequency_unit WHERE isys_frequency_unit__const = 'C__FREQUENCY_UNIT__GHZ'"
            )
                ->get_row_value('isys_frequency_unit__id');
            $l_model_default_manufact = $l_daoCMDB->retrieve(
                "SELECT isys_model_manufacturer__title FROM isys_model_manufacturer WHERE isys_model_manufacturer__const = 'C__MODEL_NOT_SPECIFIED'"
            )
                ->get_row_value('isys_model_manufacturer__title');

            $l_app_manufacturer = [];
            if ($l_add_application)
            {
                $l_res = $l_daoCMDB->retrieve("SELECT isys_application_manufacturer__id, isys_application_manufacturer__title FROM isys_application_manufacturer");
                while ($l_row = $l_res->get_row())
                {
                    $l_app_manufacturer[$l_row['isys_application_manufacturer__id']] = trim(_L($l_row['isys_application_manufacturer__title']));
                } // while
            } // if

            $l_vm_types = [];
            if ($l_add_virtual_machine)
            {
                $l_res = $l_daoCMDB->retrieve("SELECT isys_vm_type__id, isys_vm_type__title FROM isys_vm_type");
                while ($l_row = $l_res->get_row())
                {
                    $l_vm_types[$l_row['isys_vm_type__id']] = trim(_L($l_row['isys_vm_type__title']));
                } // while
            } // if

            if ($l_add_net)
            {
                $l_resNet_s = $l_daoNet_s->get_data();
                while ($l_rowNetS = $l_resNet_s->get_row())
                {
                    $l_net_address = null;
                    if ($l_rowNetS['isys_cats_net_list__address'] === null || $l_rowNetS['isys_cats_net_list__address'] == '')
                    {
                        $l_net_address = substr($l_rowNetS['isys_obj__title'], 0, strpos($l_rowNetS['isys_obj__title'], '/'));
                        if (!Ip::validate_net_ip($l_net_address))
                        {
                            continue;
                        }
                    }
                    else
                    {
                        $l_net_address = $l_rowNetS['isys_cats_net_list__address'];
                    }
                    if ($l_net_address !== null)
                    {
                        $l_net_arr[$l_net_address] = [
                            'row_data' => $l_rowNetS,
                        ];
                    }
                } // while
            } // if

            $l_conTypeTitle = $l_daoCMDB->retrieve("SELECT isys_ui_con_type__title FROM isys_ui_con_type WHERE isys_ui_con_type__const = 'C__UI_CON_TYPE__OTHER'")
                ->get_row_value('isys_ui_con_type__title');

            if ($l_capacityUnitMB == null)
            {
                $l_log->debug("Internal error: ID for capacity unit MB could not be retrieved");
                throw new Exception("Internal error: ID for capacity unit MB could not be retrieved");
            }

            if (count($l_hardwareIDs) > 0)
            {
                $l_log->info('Starting Import.');
                $l_log->info('Device count: ' . count($l_hardwareIDs));
                foreach ($l_hardwareIDs AS $l_position => $l_hardwareID)
                {
                    $l_objID          = false;
                    $l_object_updated = false;

                    $l_hw_data           = $this->get_hardware_info(
                        $l_daoOCS,
                        $l_hardwareID,
                        $l_add_model,
                        $l_add_memory,
                        $l_add_application,
                        $l_add_graphic,
                        $l_add_sound,
                        $l_add_storage,
                        $l_add_net,
                        $l_add_ui,
                        $l_add_drive,
                        $l_add_virtual_machine
                    );
                    $l_inventory         = $l_hw_data['inventory'];
                    $l_inventory["NAME"] = trim($l_inventory["NAME"]);

                    $l_thisObjTypeID = null;

                    if ($l_inventory == null)
                    {
                        $l_log->debug("Object with ID \"" . $l_hardwareID . "\" does not exist. Skipping to next device.");
                        verbose("Object with ID " . $l_hardwareID . " does not exist");
                        continue;
                    }

                    $l_log->info("Processing device: \"" . $l_inventory["NAME"] . "\".");

                    // New object, or update existing?
                    if ($l_inventory['macaddr'] != '')
                    {
                        $l_macaddresses = explode(',', $l_inventory['macaddr']);
                        $l_log->debug("MAC-addresses found for " . $l_inventory["NAME"] . ".");
                        $l_log->debug(
                            "Using Hostname: \"" . $l_inventory["NAME"] . "\", Serial: \"" . $l_inventory["SSN"] . "\" MAC-Addresses: \"" . $l_inventory['macaddr'] . "\" to identify object in i-doit."
                        );
                        $l_objID = $l_daoCMDB->get_object_by_hostname_serial_mac($l_inventory["NAME"], $l_inventory['SSN'], $l_macaddresses);
                        if ($l_objID === false)
                        {
                            $l_log->debug("Check failed.");
                        }
                        else
                        {
                            $l_log->debug("Check successful.");
                        } // if
                    }
                    else
                    {
                        verbose("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                        $l_log->debug("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                    } // if

                    if ($l_objID === false && $l_inventory['SSN'] != '')
                    {
                        $l_log->debug("Serial found for \"" . $l_inventory["NAME"] . "\".");
                        $l_log->debug("Using Hostname: \"" . $l_inventory["NAME"] . "\", Serial: \"" . $l_inventory["SSN"] . "\" to identify object in i-doit.");
                        $l_objID = $l_daoCMDB->get_object_by_hostname_serial_mac($l_inventory["NAME"], $l_inventory['SSN']);
                        if ($l_objID === false)
                        {
                            $l_log->debug("Check failed.");
                        }
                        else
                        {
                            $l_log->debug("Check successful.");
                        } // if
                    }
                    elseif ($l_inventory["SSN"] == '')
                    {
                        verbose("No Serial found for \"" . $l_inventory["NAME"] . "\".");
                        $l_log->debug("No Serial found for \"" . $l_inventory["NAME"] . "\".");
                    } // if

                    $l_log->debug("Checking object type.");
                    if (isset($l_objtype_arr[$l_position]) && $l_objtype_arr[$l_position] > 0)
                    {
                        $l_thisObjTypeID = $l_objtype_arr[$l_position];
                        $l_log->debug(
                            "Object type is set in the dialog. Using selected Object type " . _L(
                                $l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)
                            ) . " for \"" . $l_inventory["NAME"] . "\"."
                        );
                    }
                    else
                    {
                        foreach ($l_config_obj_types AS $l_conf_objtype_id => $l_prefix)
                        {
                            if ($l_thisObjTypeID === null)
                            {
                                $l_prefix_arr = null;
                                if (strpos($l_prefix, ',') !== false)
                                {
                                    $l_prefix_arr = explode(',', $l_prefix);
                                } // if

                                if (is_array($l_prefix_arr))
                                {
                                    foreach ($l_prefix_arr AS $l_sub_prefix)
                                    {
                                        if (self::check_pattern_for_objtype($l_sub_prefix, $l_inventory["TAG"], $l_inventory["NAME"]))
                                        {
                                            $l_thisObjTypeID = $l_conf_objtype_id;
                                            break;
                                        } // if
                                    } // foreach
                                }
                                else
                                {
                                    if (self::check_pattern_for_objtype($l_prefix, $l_inventory["TAG"], $l_inventory["NAME"]))
                                    {
                                        $l_thisObjTypeID = $l_conf_objtype_id;
                                    } // if
                                } // if
                            }
                            else
                            {
                                break;
                            } // if
                        } // foreach

                        // Use Default Object type
                        if ($l_thisObjTypeID === null)
                        {
                            if ($l_objType > 0)
                            {
                                verbose(
                                    "Could not determine object type from configuration. Using default object type: " . $l_default_objtype . "."
                                );
                                $l_log->debug(
                                    "Could not determine object type from configuration. Using default object type \"" . $l_default_objtype . "\" for \"" . $l_inventory["NAME"] . "\"."
                                );
                                $l_thisObjTypeID = $l_objType;
                            }
                            else
                            {
                                verbose("No default object type has been defined.");
                            } // if
                        } // if
                    }

                    // last attempt
                    if ($l_objID === false)
                    {
                        $l_log->debug("Object in i-doit still not found. Checking field isys_obj__hostname with \"" . $l_inventory["NAME"] . "\".");
                        $l_query = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__hostname = ' . $l_daoCMDB->convert_sql_text($l_inventory["NAME"]);
                        if ($l_thisObjTypeID > 0)
                        {
                            $l_query .= ' AND isys_obj__isys_obj_type__id = ' . $l_daoCMDB->convert_sql_id($l_thisObjTypeID);
                        } // if
                        $l_objID_res = $l_daoCMDB->retrieve($l_query);

                        if ($l_objID_res->num_rows() > 0)
                        {
                            $l_objID = $l_objID_res->get_row_value('isys_obj__id');
                        } // if

                        if ($l_objID === false)
                        {
                            $l_log->debug("Last attempt to identify object in i-doit. Checking field isys_obj__title with \"" . $l_inventory["NAME"] . "\".");
                            $l_query = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = ' . $l_daoCMDB->convert_sql_text($l_inventory["NAME"]);
                            if ($l_thisObjTypeID > 0)
                            {
                                $l_query .= ' AND isys_obj__isys_obj_type__id = ' . $l_daoCMDB->convert_sql_id($l_thisObjTypeID);
                            } // if
                            $l_objID_res = $l_daoCMDB->retrieve($l_query);
                            if ($l_objID_res->num_rows() > 0)
                            {
                                $l_objID = $l_objID_res->get_row_value('isys_obj__id');
                            } // if
                        } // if
                    } // if

                    // first check
                    if ($l_objID)
                    {
                        $l_log->info("Object found. Updating object-ID: " . $l_objID);
                        $l_object_updated = true;
                        // Update existing object
                        $l_row           = $l_daoCMDB->get_object_by_id($l_objID)
                            ->get_row();
                        $l_thisObjTypeID = $l_row["isys_obj__isys_obj_type__id"];

                        if ($l_thisObjTypeID > 0 && in_array($l_thisObjTypeID, $this->m_objtype_blacklist))
                        {
                            $l_error_msg = "Error: Import object '" . $l_inventory["NAME"] . "' (#" . $l_objID . ") is of blacklisted object type '" . _L(
                                    $l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_row['isys_obj_type__id'])) . "'"
                                );

                            verbose($l_error_msg);
                            $this->debug($l_error_msg);
                            continue;
                        }
                        $l_update_msg = "Updating existing object " . $l_inventory["NAME"] . " (" . _L(
                                $l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_row['isys_obj_type__id']))
                            ) . ")";
                        verbose($l_update_msg);

                        $l_update_title = '';
                        if ($l_row['isys_obj__title'] !== $l_inventory['NAME'])
                        {
                            $l_log->debug("Object title is differnt: " . $l_row['isys_obj__title'] . " (i-doit) !== " . $l_inventory["NAME"] . " (OCS).");
                            if (is_array($l_daoGl->validate(['title' => $l_inventory['NAME']])) || isys_tenantsettings::get('cmdb.unique.object-title'))
                            {
                                $l_title        = $l_daoCMDB->generate_unique_obj_title($l_inventory['NAME']);
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_title) . ", ";
                            }
                            else
                            {
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_inventory['NAME']) . ", ";
                            } // if
                        } // if

                        $l_update = "UPDATE isys_obj SET " . $l_update_title . "isys_obj__hostname = " . $l_daoCMDB->convert_sql_text(
                                $l_inventory["NAME"]
                            ) . ", " . "isys_obj__updated     =    NOW(), " . "isys_obj__updated_by  =    '" . $g_comp_session->get_current_username(
                            ) . "', " . "isys_obj__imported    =    NOW() ";

                        if (isset($l_objtype_arr[$l_position]) && $l_objtype_arr[$l_position] > 0)
                        {
                            $l_log->debug("Updating object with object type: " . $l_daoCMDB->get_objtype_name_by_id_as_string($l_objtype_arr[$l_position]) . ".");
                            $l_update .= ", isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id($l_objtype_arr[$l_position]) . " ";
                        } // if

                        /**
                         * Main object data
                         */
                        $l_daoCMDB->update(
                            $l_update . "WHERE isys_obj__id = " . $l_daoCMDB->convert_sql_id($l_row["isys_obj__id"]) . ";"
                        );

                    }
                    else
                    {
                        verbose("Creating new object " . $l_inventory["NAME"] . " (" . _L($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")");
                        $l_log->info(
                            "Object not found. Creating new object " . $l_inventory["NAME"] . " (" . _L($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")"
                        );

                        $l_objID = $l_daoCMDB->insert_new_obj(
                            $l_thisObjTypeID,
                            false,
                            $l_inventory["NAME"],
                            null,
                            C__RECORD_STATUS__NORMAL,
                            $l_inventory["NAME"],
                            null,
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $l_cmdb_status_id
                        );

                        $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_objID, $l_thisObjTypeID);
                    }

                    /*
                     * Clear categories hostaddress and port
                     */
                    if ($l_overwrite_ip_port)
                    {
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_ip_list', true);
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_port_list', true);
                        verbose("Categories hostaddress and port cleared.", true);
                        $l_log->debug("Categories hostaddress and port cleared.");
                    } // if

                    /**
                     * Model
                     */
                    if (isset($l_hw_data['model']))
                    {
                        verbose("Processing model");
                        $l_log->info("Processing model");
                        $l_data = [];

                        $l_rowModel = null;
                        $l_row      = $l_hw_data['model'];

                        $l_res = $l_daoModel->get_data(null, $l_objID);
                        if ($l_res->num_rows() < 1)
                        {
                            $l_data['productid']   = null;
                            $l_data['description'] = null;
                        }
                        else
                        {
                            $l_rowModel            = $l_res->get_row();
                            $l_data['productid']   = $l_rowModel["isys_catg_model_list__productid"];
                            $l_data['description'] = $l_rowModel["isys_catg_model_list__description"];
                            $l_data['data_id']     = $l_rowModel['isys_catg_model_list__id'];
                        }

                        $l_data['manufacturer'] = $l_row["SMANUFACTURER"];
                        $l_data['title']        = $l_row['SMODEL'];
                        $l_data['serial']       = $l_row["SSN"];
                        $l_data['firmware']     = $l_row["BVERSION"];

                        // Build import array.
                        $l_object_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL] = $l_daoModel->parse_import_array($l_data);

                        if ($l_logb_active)
                        {
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                'manufacturer' => ['title_lang' => $l_row["SMANUFACTURER"]],
                                'title'        => ['title_lang' => $l_row["SMODEL"]],
                                'serial'       => ['value' => $l_row["SSN"]],
                                'firmware'     => ['value' => $l_row["BVERSION"]]
                            ];

                            $l_changes = $l_dao_logb->prepare_changes($l_daoModel, $l_rowModel, $l_category_values);
                            if (count($l_changes) > 0)
                            {
                                $l_mod_event_manager->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    "-modified from OCS-",
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__MODEL',
                                    serialize($l_changes)
                                );
                            } // if
                        } // if

                        $l_import_data = $l_daoModel->parse_import_array($l_data);
                        $l_entry_id    = $l_daoModel->sync(
                            $l_import_data,
                            $l_objID,
                            ((!empty($l_data['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE)
                        );

                        // Emit category signal (afterCategoryEntrySave).
                        isys_component_signalcollection::get_instance()
                            ->emit(
                                "mod.cmdb.afterCategoryEntrySave",
                                $l_daoModel,
                                $l_entry_id,
                                !!$l_entry_id,
                                $l_objID,
                                $l_import_data,
                                $l_changes
                            );

                        unset($l_data);
                    }

                    /**
                     * Processors
                     */
                    if ($l_add_cpu)
                    {
                        verbose("Processing CPUs");
                        $l_log->info("Processing CPUs");
                        $l_data = [];

                        $n = intval($l_inventory["PROCESSORN"]);

                        $l_res  = $l_daoCPU->get_data(null, $l_objID);
                        $l_cpus = $l_res->num_rows();

                        if ($l_cpus > 0)
                        {
                            // Delete and Create
                            if ($l_cpus > $n)
                            {
                                // Delete cpus limit $l_cpus - $n
                                $l_resDelete = $l_daoCPU->retrieve(
                                    'SELECT isys_catg_cpu_list__id FROM isys_catg_cpu_list WHERE ' . 'isys_catg_cpu_list__isys_obj__id = ' . $l_daoCPU->convert_sql_id(
                                        $l_objID
                                    ) . ' LIMIT 0,' . ($l_cpus - $n)
                                );
                                while ($l_rowDelete = $l_resDelete->get_row())
                                {
                                    $l_daoCPU->delete_entry($l_rowDelete['isys_catg_cpu_list__id'], 'isys_catg_cpu_list');
                                    if ($l_logb_active) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__CPU',
                                        null
                                    );
                                }
                            }
                            elseif ($l_cpus < $n)
                            {
                                // Add cpus $n - $l_cpus
                                for ($i = 0;$i < ($n - $l_cpus);$i++)
                                {
                                    $l_data[] = [
                                        'data_id'        => null,
                                        'title'          => $l_inventory["PROCESSORT"],
                                        'frequency'      => $l_inventory["PROCESSORS"] / 1000,
                                        'frequency_unit' => $l_frequency_unit,
                                        'manufacturer'   => null,
                                        'type'           => null
                                    ];
                                }
                            }

                            while ($l_rowCPU = $l_res->get_row())
                            {
                                $l_data[]                                          = [
                                    'data_id'        => $l_rowCPU['isys_catg_cpu_list__id'],
                                    'title'          => $l_inventory["PROCESSORT"],
                                    'frequency'      => $l_inventory["PROCESSORS"] / 1000,
                                    'frequency_unit' => $l_frequency_unit,
                                    'manufacturer'   => $l_rowCPU['isys_catg_cpu_manufacturer__title'],
                                    'type'           => $l_rowCPU['isys_catg_cpu_type__title']
                                ];
                                $l_rowCPU_arr[$l_rowCPU['isys_catg_cpu_list__id']] = $l_rowCPU;
                            }

                            foreach ($l_data AS $l_val)
                            {
                                $l_import_data = $l_daoCPU->parse_import_array($l_val);

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'          => ['value' => $l_inventory["PROCESSORT"]],
                                        'frequency'      => ['value' => $l_inventory["PROCESSORS"] / 1000],
                                        'frequency_unit' => ['title_lang' => 'GHz']
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoCPU, $l_rowCPU_arr[$l_val['data_id']], $l_category_values);
                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__MODEL',
                                            serialize($l_changes)
                                        );
                                    } // if
                                } // if

                                $l_entry_id = $l_daoCPU->sync(
                                    $l_import_data,
                                    $l_objID,
                                    ((!empty($l_val['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE)
                                );

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoCPU,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        $l_changes
                                    );
                            }
                        }
                        else
                        {
                            // Create
                            for ($i = 0;$i < $n;$i++)
                            {
                                $l_data = [
                                    'data_id'        => null,
                                    'title'          => $l_inventory["PROCESSORT"],
                                    'frequency'      => $l_inventory["PROCESSORS"] / 1000,
                                    'frequency_unit' => $l_frequency_unit,
                                    'manufacturer'   => null,
                                    'type'           => null
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'          => ['value' => $l_inventory["PROCESSORT"]],
                                        'frequency'      => ['value' => $l_inventory["PROCESSORS"] / 1000],
                                        'frequency_unit' => ['title_lang' => 'GHz']
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoCPU, null, $l_category_values);
                                    $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__CPU',
                                        serialize($l_changes)
                                    );
                                } // if

                                $l_import_data = $l_daoCPU->parse_import_array($l_data);
                                $l_entry_id    = $l_daoCPU->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoCPU,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        $l_changes
                                    );
                            }
                        }
                    }

                    /**
                     * Memory
                     */
                    if (isset($l_hw_data['memory']))
                    {
                        verbose("Processing memory");
                        $l_log->info("Processing memory");
                        $l_check_data = [];
                        $l_res        = $l_daoMemory->get_data(null, $l_objID);
                        $l_mem_amount = $l_res->num_rows();
                        if ($l_mem_amount > 0)
                        {

                            // Get data in i-doit
                            while ($l_rowMemory = $l_res->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_rowMemory['isys_catg_memory_list__id'],
                                    'title'        => $l_rowMemory["isys_memory_title__title"],
                                    'manufacturer' => $l_rowMemory['isys_memory_manufacturer__title'],
                                    'type'         => $l_rowMemory['isys_memory_type__title'],
                                    'unit'         => $l_rowMemory['isys_catg_memory_list__isys_memory_unit__id'],
                                    'capacity'     => $l_rowMemory['isys_catg_memory_list__capacity'],
                                    'description'  => $l_rowMemory['isys_catg_memory_list__description']
                                ];
                            }

                            foreach ($l_hw_data['memory'] AS $l_rowMemory)
                            {

                                // Check data from ocs with data from i-doit
                                foreach ($l_check_data AS $l_key => $l_val)
                                {
                                    if ($l_val['title'] == $l_rowMemory['CAPTION'] && $l_val['type'] == $l_rowMemory['TYPE'] && $l_val['capacity'] == isys_convert::memory(
                                            $l_rowMemory["CAPACITY"],
                                            $l_capacityUnitMB
                                        )
                                    )
                                    {

                                        unset($l_check_data[$l_key]);
                                        continue 2;
                                    }
                                }

                                // Raw array for preparing the import array
                                $l_data = [
                                    'title'        => $l_rowMemory["CAPTION"],
                                    'manufacturer' => null,
                                    'type'         => $l_rowMemory['TYPE'],
                                    'unit'         => $l_capacityUnitMB,
                                    'capacity'     => $l_rowMemory['CAPACITY']
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'    => ['title_lang' => $l_rowMemory["CAPTION"]],
                                        'type'     => ['title_lang' => $l_rowMemory["TYPE"]],
                                        'unit'     => ['title_lang' => 'MB'],
                                        'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoMemory, null, $l_category_values);

                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__MEMORY',
                                            serialize($l_changes)
                                        );
                                    } // if
                                } // if

                                $l_import_data = $l_daoMemory->parse_import_array($l_data);
                                $l_entry_id    = $l_daoMemory->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoMemory,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        $l_changes
                                    );
                            }
                            // Delete entries
                            if (count($l_check_data) > 0) $this->delete_entries_from_category(
                                $l_check_data,
                                $l_daoMemory,
                                'isys_catg_memory_list',
                                $l_objID,
                                $l_thisObjTypeID,
                                'LC__CMDB__CATG__MEMORY',
                                $l_logb_active
                            );

                        }
                        else
                        {
                            // Create entries
                            foreach ($l_hw_data['memory'] AS $l_rowMemory)
                            {
                                $l_data = [
                                    'title'        => $l_rowMemory["CAPTION"],
                                    'manufacturer' => null,
                                    'type'         => $l_rowMemory["TYPE"],
                                    'unit'         => $l_capacityUnitMB,
                                    'capacity'     => $l_rowMemory["CAPACITY"]
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'    => ['title_lang' => $l_rowMemory["CAPTION"]],
                                        'type'     => ['title_lang' => $l_rowMemory["TYPE"]],
                                        'unit'     => ['title_lang' => 'MB'],
                                        'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoMemory, null, $l_category_values);
                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__MEMORY',
                                            serialize($l_changes)
                                        );
                                    } // if
                                } // if

                                $l_import_data = $l_daoMemory->parse_import_array($l_data);
                                $l_entry_id    = $l_daoMemory->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoMemory,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        $l_changes
                                    );
                            }
                        }
                    }

                    /**
                     * Operating system
                     */
                    if ($l_add_os)
                    {
                        verbose("Processing Operating system");
                        $l_log->info("Processing Operating system");

                        $l_found    = false;
                        $l_data_id  = null;
                        $l_row_data = null;

                        $l_os_sql = "SELECT isys_obj__id FROM isys_obj" . " WHERE isys_obj__title = " . $l_daoCMDB->convert_sql_text(
                                $l_inventory["OSNAME"]
                            ) . " AND isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id(C__OBJTYPE__OPERATING_SYSTEM) . ";";

                        $l_res = $l_daoCMDB->retrieve($l_os_sql);

                        if (count($l_res))
                        {
                            // OS object exists.
                            $l_row  = $l_res->get_row();
                            $l_osID = $l_row["isys_obj__id"];
                        }
                        else
                        {
                            // Create new OS object.
                            $l_osID = $l_daoCMDB->insert_new_obj(C__OBJTYPE__OPERATING_SYSTEM, false, $l_inventory["OSNAME"], null, C__RECORD_STATUS__NORMAL);
                        } // if

                        $l_version_id = null;

                        if ($l_osID > 0 && !empty($l_inventory["OSVERSION"]))
                        {
                            // Check, if the version has been created.
                            $l_os_version_sql = 'SELECT isys_catg_version_list__id FROM isys_catg_version_list
		                        WHERE isys_catg_version_list__isys_obj__id = ' . $l_daoCMDB->convert_sql_id($l_osID) . '
		                        AND isys_catg_version_list__title LIKE ' . $l_daoCMDB->convert_sql_text($l_inventory["OSVERSION"]) . ' LIMIT 1;';

                            $l_res = $l_daoCMDB->retrieve($l_os_version_sql);

                            if (count($l_res))
                            {
                                $l_version_id = $l_res->get_row_value('isys_catg_version_list__id');
                            }
                            else
                            {
                                $l_version_id = isys_cmdb_dao_category_g_version::instance($g_comp_database)
                                    ->create($l_osID, C__RECORD_STATUS__NORMAL, $l_inventory["OSVERSION"]);
                            } // if
                        } // if

                        $l_res = $l_daoCMDB->retrieve(
                            "SELECT * FROM isys_catg_application_list" . " INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id" . " INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id" . " INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id" . " WHERE isys_catg_application_list__isys_obj__id = " . $l_daoCMDB->convert_sql_id(
                                $l_objID
                            ) . " AND isys_obj_type__id = " . $l_daoCMDB->convert_sql_id(C__OBJTYPE__OPERATING_SYSTEM) . ";"
                        );

                        if (count($l_res) > 1)
                        {
                            while ($l_rowOS = $l_res->get_row())
                            {
                                if ($l_rowOS['isys_obj__title'] != $l_inventory["OSNAME"])
                                {
                                    $l_daoCMDB->delete_entry($l_rowOS['isys_catg_application_list__id'], 'isys_catg_application_list');

                                    if ($l_logb_active)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__APPLICATION',
                                            null
                                        );
                                    } // if
                                }
                                else
                                {
                                    $l_found = true;
                                } // if
                            } // while
                        }
                        else if (count($l_res) == 1)
                        {
                            $l_rowOS    = $l_res->get_row();
                            $l_row_data = [
                                'isys_catg_application_list__id' => $l_rowOS['isys_catg_application_list__id'],
                                'isys_connection__isys_obj__id'  => $l_rowOS['isys_connection__isys_obj__id'],
                            ];

                            $l_data_id = $l_rowOS['isys_catg_application_list__id'];
                        } // if

                        if (!$l_found)
                        {
                            $l_data = [
                                'data_id'          => $l_data_id,
                                'application'      => $l_osID,
                                'assigned_version' => $l_version_id,
                            ];

                            if ($l_logb_active)
                            {
                                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = ['application' => ['value' => $l_osID]];

                                $l_changes = $l_dao_logb->prepare_changes(
                                    $l_daoOS,
                                    $l_row_data,
                                    $l_category_values
                                );

                                if (count($l_changes) > 0)
                                {
                                    $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CATG__OPERATING_SYSTEM',
                                        serialize($l_changes)
                                    );
                                } // if
                            } // if

                            $l_import_data = $l_daoOS->parse_import_array($l_data);
                            $l_entry_id    = $l_daoOS->sync(
                                $l_import_data,
                                $l_objID,
                                (($l_data_id > 0) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE)
                            );

                            // Emit category signal (afterCategoryEntrySave).
                            isys_component_signalcollection::get_instance()
                                ->emit("mod.cmdb.afterCategoryEntrySave", $l_daoOS, $l_entry_id, !!$l_entry_id, $l_objID, $l_import_data, isset($l_changes) ? $l_changes : []);
                        } // if
                    } // if

                    /**
                     * Applications
                     */
                    if (isset($l_hw_data['application']))
                    {
                        verbose("Processing applications");
                        $l_log->info("Processing applications");
                        $l_check_data = $l_double_assigned = [];
                        $l_res_app    = $l_daoCMDB->retrieve(
                            "SELECT isys_obj__title, isys_catg_application_list__id, isys_obj__id,
                            isys_catg_application_list__isys_catg_version_list__id, isys_catg_version_list__title " . "FROM isys_catg_application_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id " . "INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " . "LEFT JOIN isys_catg_version_list ON isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id " . "WHERE isys_catg_application_list__isys_obj__id = " . $l_daoCMDB->convert_sql_id(
                                $l_objID
                            ) . " " . "AND isys_obj_type__id = " . $l_daoCMDB->convert_sql_id(C__OBJTYPE__APPLICATION)
                        );

                        while ($l_rowApp = $l_res_app->get_row())
                        {
                            if (isset($l_check_data[$l_rowApp['isys_obj__id']]))
                            {
                                $l_double_assigned[] = [
                                    'data_id' => $l_rowApp['isys_catg_application_list__id']
                                ];
                            } // if

                            $l_check_data[$l_rowApp['isys_obj__id']] = [
                                'data_id'       => $l_rowApp['isys_catg_application_list__id'],
                                'version'       => $l_rowApp['isys_catg_application_list__isys_catg_version_list__id'],
                                'version_title' => $l_rowApp['isys_catg_version_list__title']
                            ];
                        } // while

                        $l_swIDs = $l_already_updated = [];

                        // Assign Application
                        foreach ($l_hw_data['application'] AS $l_row)
                        {
                            $l_swID = false;

                            $l_row['VERSION']   = trim($l_row['VERSION']);
                            $l_row['PUBLISHER'] = trim($l_row['PUBLISHER']);
                            $l_row['FOLDER']    = trim($l_row['FOLDER']);
                            $l_row['COMMENTS']  = trim($l_row['COMMENTS']);
                            $l_row['NAME']      = trim($l_row['NAME']);

                            $l_resSW = $l_daoCMDB->retrieve(
                                "SELECT isys_obj__id, isys_cats_application_list.* " . "FROM isys_obj " . "LEFT JOIN isys_cats_application_list ON isys_obj__id = isys_cats_application_list__isys_obj__id " . "WHERE isys_obj__title = " . $l_daoCMDB->convert_sql_text(
                                    $l_row["NAME"]
                                ) . " " . "AND isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id(C__OBJTYPE__APPLICATION) . ";"
                            );

                            if ($l_resSW->num_rows() > 0)
                            {
                                // Application object exists
                                $l_app_data = $l_resSW->get_row();
                                $l_swID     = $l_app_data['isys_obj__id'];

                                if ($l_app_data['isys_cats_application_list__id'] > 0 && !in_array($l_app_data['isys_cats_application_list__id'], $l_already_updated))
                                {
                                    $l_changed_data  = [];
                                    $l_specific_data = [
                                        'data_id'          => $l_app_data['isys_cats_application_list__id'],
                                        'specification'    => $l_app_data['isys_cats_application_list__specification'],
                                        'installation'     => $l_app_data['isys_cats_application_list__isys_installation_type__id'],
                                        'registration_key' => $l_app_data['isys_cats_application_list__registration_key'],
                                        'manufacturer'     => $l_app_data['isys_cats_application_list__isys_application_manufacturer__id'],
                                        'install_path'     => $l_app_data['isys_cats_application_list__install_path'],
                                        'release'          => $l_app_data['isys_cats_application_list__release'],
                                        'description'      => $l_app_data['isys_cats_application_list__description']
                                    ];

                                    if (strtolower($l_row['PUBLISHER']) != strtolower($l_app_manufacturer[$l_specific_data['manufacturer']]))
                                    {
                                        $l_changed_data['isys_cmdb_dao_category_s_application::manufacturer'] = [
                                            'from' => $l_app_manufacturer[$l_app_data['isys_cats_application_list__isys_application_manufacturer__id']],
                                            'to'   => $l_row['PUBLISHER']
                                        ];

                                        $l_specific_data['manufacturer'] = isys_import_handler::check_dialog('isys_application_manufacturer', $l_row['PUBLISHER']);
                                    } // if

                                    if ($l_row['FOLDER'] != $l_app_data['isys_cats_application_list__install_path'])
                                    {
                                        $l_changed_data['isys_cmdb_dao_category_s_application::install_path'] = [
                                            'from' => $l_specific_data['install_path'],
                                            'to'   => $l_row['FOLDER'],
                                        ];

                                        $l_specific_data['install_path'] = $l_row['FOLDER'];
                                    } // if

                                    if ($l_row['COMMENTS'] != '' && $l_row['COMMENTS'] != $l_app_data['isys_cats_application_list__description'])
                                    {
                                        $l_changed_data['isys_cmdb_dao_category_s_application::description'] = [
                                            'from' => $l_specific_data['description'],
                                            'to'   => $l_row['COMMENTS']
                                        ];

                                        $l_specific_data['description'] = $l_row['COMMENTS'];
                                    } // if

                                    // Update specific category of application
                                    if (count($l_changed_data) > 0)
                                    {
                                        $l_daoApp_s->save(
                                            $l_specific_data['data_id'],
                                            C__RECORD_STATUS__NORMAL,
                                            $l_specific_data['specification'],
                                            $l_specific_data['manufacturer'],
                                            $l_specific_data['release'],
                                            $l_specific_data['description'],
                                            $l_specific_data['installation'],
                                            $l_specific_data['registration_key'],
                                            $l_specific_data['install_path']
                                        );

                                        if ($l_logb_active)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_swID,
                                                C__OBJTYPE__APPLICATION,
                                                'LC__CMDB__CATS__APPLICATION',
                                                serialize($l_changed_data)
                                            );
                                        } // if
                                    } // if
                                    $l_already_updated[] = $l_app_data['isys_cats_application_list__id'];
                                }
                                elseif (!$l_app_data['isys_cats_application_list__id'])
                                {
                                    if ($l_row['PUBLISHER'] != '')
                                    {
                                        $l_manufacturer = isys_import_handler::check_dialog('isys_application_manufacturer', $l_row['PUBLISHER']);
                                    }
                                    else
                                    {
                                        $l_manufacturer = array_search(_L('LC__UNIVERSAL__NOT_SPECIFIED'), $l_app_manufacturer);
                                    } // if

                                    $l_app_data['isys_cats_application_list__id'] = $l_daoApp_s->create(
                                        $l_swID,
                                        C__RECORD_STATUS__NORMAL,
                                        null,
                                        $l_manufacturer,
                                        null,
                                        $l_row['COMMENTS'],
                                        null,
                                        null,
                                        $l_row['FOLDER']
                                    );
                                    $l_already_updated[]                          = $l_app_data['isys_cats_application_list__id'];
                                }
                            }
                            else if ($l_regApp == "0")
                            {
                                // Creat new application object
                                $l_swID = $l_daoCMDB->insert_new_obj(C__OBJTYPE__APPLICATION, false, $l_row["NAME"], null, C__RECORD_STATUS__NORMAL);
                                if ($l_row['PUBLISHER'] != '' && ($l_app_man_key = array_search($l_row['PUBLISHER'], $l_app_manufacturer)))
                                {
                                    $l_manufacturer = $l_app_man_key;
                                }
                                else
                                {
                                    if ($l_row['PUBLISHER'] != '')
                                    {
                                        $l_manufacturer                      = isys_import_handler::check_dialog('isys_application_manufacturer', $l_row['PUBLISHER']);
                                        $l_app_manufacturer[$l_manufacturer] = $l_row['PUBLISHER'];
                                    }
                                    else
                                    {
                                        $l_manufacturer = array_search(_L('LC__UNIVERSAL__NOT_SPECIFIED'), $l_app_manufacturer);
                                    } // if
                                } // if
                                $l_daoApp_s->create(
                                    $l_swID,
                                    C__RECORD_STATUS__NORMAL,
                                    null,
                                    $l_manufacturer,
                                    $l_row["VERSION"],
                                    $l_row["COMMENTS"],
                                    null,
                                    null,
                                    $l_row["FOLDER"]
                                );
                                $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_swID, C__OBJTYPE__APPLICATION);
                            }

                            $l_version_id = null;

                            // Check, if the found application version has already been created.
                            if ($l_swID && !empty($l_row["VERSION"]))
                            {
                                // Check, if the version has been created.
                                $l_app_version_sql = 'SELECT isys_catg_version_list__id FROM isys_catg_version_list
			                        WHERE isys_catg_version_list__isys_obj__id = ' . $l_daoCMDB->convert_sql_id($l_swID) . '
			                        AND isys_catg_version_list__title LIKE ' . $l_daoCMDB->convert_sql_text($l_row["VERSION"]) . ' LIMIT 1;';

                                $l_res = $l_daoCMDB->retrieve($l_app_version_sql);

                                if (count($l_res))
                                {
                                    $l_version_id = $l_res->get_row_value('isys_catg_version_list__id');
                                }
                                else
                                {
                                    $l_version_id = isys_cmdb_dao_category_g_version::instance($g_comp_database)
                                        ->create($l_swID, C__RECORD_STATUS__NORMAL, $l_row["VERSION"]);
                                } // if
                            } // if

                            if ($l_swID && !in_array($l_swID, $l_swIDs))
                            {
                                $l_swIDs[] = $l_swID;
                                if (count($l_check_data) > 0 && isset($l_check_data[$l_swID]))
                                {
                                    // Application found
                                    if ((int) $l_check_data[$l_swID]['version'] !== (int) $l_version_id)
                                    {
                                        // Update version
                                        $l_update = 'UPDATE isys_catg_application_list SET isys_catg_application_list__isys_catg_version_list__id = ' . $l_daoApp->convert_sql_id(
                                                $l_version_id
                                            ) . ' WHERE isys_catg_application_list__id = ' . $l_daoApp->convert_sql_id($l_check_data[$l_swID]['data_id']);
                                        $l_daoApp->update($l_update);

                                        if ($l_logb_active)
                                        {
                                            $l_changed_data['isys_cmdb_dao_category_g_application::application']      = [
                                                'from' => $l_row["NAME"],
                                                'to'   => $l_row["NAME"]
                                            ];
                                            $l_changed_data['isys_cmdb_dao_category_g_application::assigned_version'] = [
                                                'from' => $l_check_data[$l_sqID]['version_title'],
                                                'to'   => $l_row['VERSION']
                                            ];
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__CMDB__CATG__APPLICATION',
                                                serialize($l_changed_data)
                                            );
                                        } // if
                                    } // if

                                    unset($l_check_data[$l_swID]);
                                    continue;
                                } // if

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = ['application' => ['value' => $l_swID]];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoApp, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__APPLICATION',
                                        serialize($l_changes)
                                    );
                                }

                                //$l_daoApp->sync($l_daoApp->parse_import_array($l_data), $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // First create relation
                                $l_relation_obj = $l_relation_dao->create_object(
                                    $l_relation_dao->format_relation_name(
                                        $l_inventory["NAME"],
                                        $l_row['NAME'],
                                        $l_relation_data["isys_relation_type__master"]
                                    ),
                                    C__OBJTYPE__RELATION,
                                    C__RECORD_STATUS__NORMAL
                                );

                                $l_sql = "INSERT INTO isys_catg_relation_list " . "SET " . "isys_catg_relation_list__isys_obj__id = " . $l_daoCMDB->convert_sql_id(
                                        $l_relation_obj
                                    ) . ", " . "isys_catg_relation_list__isys_obj__id__master = " . $l_daoCMDB->convert_sql_id(
                                        $l_objID
                                    ) . ", " . "isys_catg_relation_list__isys_obj__id__slave = " . $l_daoCMDB->convert_sql_id(
                                        $l_swID
                                    ) . ", " . "isys_catg_relation_list__isys_relation_type__id = '" . C__RELATION_TYPE__SOFTWARE . "', " . "isys_catg_relation_list__isys_weighting__id = '" . C__WEIGHTING__5 . "', " . "isys_catg_relation_list__status = '" . C__RECORD_STATUS__NORMAL . "' " . ";";

                                if ($l_daoApp->update($l_sql))
                                {
                                    $l_relation_id = $l_daoApp->get_last_insert_id();

                                    // Secondly insert new application entry with relation id
                                    $l_sql = "INSERT INTO isys_catg_application_list SET
										isys_catg_application_list__isys_connection__id = " . $l_daoCMDB->convert_sql_id($l_connection->add_connection($l_swID)) . ",
										isys_catg_application_list__status = " . $l_daoApp->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
										isys_catg_application_list__isys_catg_relation_list__id = ' . $l_daoApp->convert_sql_id($l_relation_id) . ',
										isys_catg_application_list__isys_obj__id = ' . $l_daoApp->convert_sql_id($l_objID) . ',
										isys_catg_application_list__isys_catg_version_list__id = ' . $l_daoApp->convert_sql_id($l_version_id) . ';';

                                    $l_daoApp->update($l_sql) && $l_daoApp->apply_update();
                                } // if
                            } // if
                        } // foreach

                        // Detach Applications
                        if ($l_regAppAssign == "1")
                        {
                            if (count($l_check_data) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_check_data,
                                    $l_daoApp,
                                    'isys_catg_application_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__APPLICATION',
                                    $l_logb_active
                                );
                            }
                            if (count($l_double_assigned) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_double_assigned,
                                    $l_daoApp,
                                    'isys_catg_application_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__APPLICATION',
                                    $l_logb_active
                                );
                            } // if
                        } // if
                    } // if

                    /**
                     * Graphics adapter
                     */
                    if (isset($l_hw_data['graphic']))
                    {
                        verbose("Processing graphics adapter");
                        $l_log->info("Processing graphics adapter");
                        $l_check_data   = [];
                        $l_res_graphic  = $l_dao_graphic->get_data(null, $l_objID);
                        $l_graka_amount = $l_res_graphic->num_rows();

                        if ($l_graka_amount > 0)
                        {
                            while ($l_rowGraka = $l_res_graphic->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_rowGraka['isys_catg_graphic_list__id'],
                                    'title'        => $l_rowGraka['isys_catg_graphic_list__title'],
                                    'memory'       => $l_rowGraka['isys_catg_graphic_list__memory'],
                                    'manufacturer' => $l_rowGraka['isys_graphic_manufacturer__title'],
                                    'unit'         => $l_rowGraka['isys_catg_graphic_list__isys_graphic_manufacturer__id'],
                                    'description'  => $l_rowGraka['isys_catg_graphic_list__description']
                                ];
                            }

                            foreach ($l_hw_data['graphic'] AS $l_rowGraka)
                            {
                                foreach ($l_check_data AS $l_key => $l_val)
                                {
                                    if ($l_val['title'] == $l_rowGraka['NAME'] && $l_val['memory'] == isys_convert::memory($l_rowGraka["MEMORY"], $l_capacityUnitMB))
                                    {

                                        unset($l_check_data[$l_key]);
                                        continue 2;
                                    }
                                }

                                $l_data = [
                                    'title'        => $l_rowGraka['NAME'],
                                    'manufacturer' => null,
                                    'memory'       => $l_rowGraka['MEMORY'],
                                    'unit'         => $l_capacityUnitMB
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'  => ['value' => $l_rowGraka['NAME']],
                                        'memory' => ['value' => $l_rowGraka['MEMORY']],
                                        'unit'   => ['title_lang' => 'MB']
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_graphic, null, $l_category_values);

                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__GRAPHIC',
                                            serialize($l_changes)
                                        );
                                    } // if
                                } // if

                                $l_import_data = $l_dao_graphic->parse_import_array($l_data);
                                $l_entry_id    = $l_dao_graphic->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_graphic,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );

                            } // foreach

                            if (count($l_check_data) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_check_data,
                                    $l_dao_graphic,
                                    'isys_catg_graphic_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__GRAPHIC',
                                    $l_logb_active
                                );
                            } // if
                        }
                        else
                        {
                            foreach ($l_hw_data['graphic'] AS $l_rowGraka)
                            {
                                $l_data = [
                                    'title'        => $l_rowGraka['NAME'],
                                    'manufacturer' => null,
                                    'memory'       => $l_rowGraka['MEMORY'],
                                    'unit'         => $l_capacityUnitMB,
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'    => ['value' => $l_rowGraka['NAME']],
                                        'unit'     => ['title_lang' => 'MB'],
                                        'capacity' => ['value' => $l_rowGraka['MEMORY']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_graphic, null, $l_category_values);
                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__MEMORY',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_dao_graphic->parse_import_array($l_data);
                                $l_entry_id    = $l_dao_graphic->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_graphic,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    /**
                     * Sound adapter
                     */
                    if (isset($l_hw_data['sound']))
                    {
                        verbose("Processing sound adapter");
                        $l_log->info("Processing sound adapter");
                        $l_check_data   = [];
                        $l_res_sound    = $l_dao_sound->get_data(null, $l_objID);
                        $l_sound_amount = $l_res_sound->num_rows();

                        if ($l_sound_amount > 0)
                        {
                            while ($l_rowSound = $l_res_sound->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_rowSound['isys_catg_sound_list__id'],
                                    'title'        => $l_rowSound['isys_catg_sound_list__title'],
                                    'manufacturer' => $l_rowSound['isys_sound_manufacturer__title'],
                                    'description'  => $l_rowSound['isys_catg_graphic_list__description']
                                ];
                            }

                            foreach ($l_hw_data['sound'] AS $l_rowSound)
                            {

                                foreach ($l_check_data AS $l_key => $l_val)
                                {
                                    if ($l_val['title'] == $l_rowSound['NAME'] && $l_val['manufacturer'] == $l_rowSound['MANUFACTURER'])
                                    {

                                        unset($l_check_data[$l_key]);
                                        continue 2;
                                    }
                                }

                                $l_data = [
                                    'title'        => $l_rowSound['NAME'],
                                    'manufacturer' => $l_rowSound['MANUFACTURER'],
                                    'description'  => $l_rowSound['DESCRIPTION']
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'        => ['value' => $l_rowSound['NAME']],
                                        'manufacturer' => ['title_lang' => $l_rowSound['MANUFA']],
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_sound, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__SOUND',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_dao_sound->parse_import_array($l_data);
                                $l_entry_id    = $l_dao_sound->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_sound,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );

                            }
                            if (count($l_check_data) > 0) $this->delete_entries_from_category(
                                $l_check_data,
                                $l_dao_sound,
                                'isys_catg_sound_list',
                                $l_objID,
                                $l_thisObjTypeID,
                                'LC__CMDB__CATG__SOUND',
                                $l_logb_active
                            );

                        }
                        else
                        {
                            foreach ($l_hw_data['sound'] AS $l_rowSound)
                            {

                                $l_data = [
                                    'title'        => $l_rowSound['NAME'],
                                    'manufacturer' => $l_rowSound['MANUFACTURER'],
                                    'description'  => $l_rowSound['DESCRIPTION']
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'        => ['value' => $l_rowSound['NAME']],
                                        'manufacturer' => ['title_lang' => $l_rowSound['MANUFACTURER']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_sound, null, $l_category_values);
                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__SOUND',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_dao_sound->parse_import_array($l_data);
                                $l_entry_id    = $l_dao_sound->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_sound,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    /**
                     * Drives
                     */
                    if (isset($l_hw_data['drive']))
                    {
                        verbose("Processing drives");
                        $l_log->info("Processing drives");
                        $l_check_data   = [];
                        $l_res_drive    = $l_dao_drive->get_data(null, $l_objID);
                        $l_drive_amount = $l_res_drive->num_rows();

                        if ($l_drive_amount > 0)
                        {
                            // Create and delete
                            while ($l_rowDrive = $l_res_drive->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_rowDrive['isys_catg_drive_list__id'],
                                    'mount_point'  => $l_rowDrive['isys_catg_drive_list__driveletter'],
                                    'title'        => $l_rowDrive['isys_catg_drive_list__title'],
                                    'system_drive' => $l_rowDrive['isys_catg_drive_list__system_drive'],
                                    'filesystem'   => $l_rowDrive['isys_filesystem_type__title'],
                                    'unit'         => $l_rowDrive['isys_catg_drive_list__isys_memory_unit__id'],
                                    'capacity'     => $l_rowDrive['isys_catg_drive_list__capacity'],
                                    'description'  => $l_rowDrive['isys_catg_drive_list__description'],
                                ];
                            }

                            foreach ($l_hw_data['drive'] AS $l_rowDrive)
                            {

                                if ($l_rowDrive["LETTER"] == null) $l_driveletter = $l_rowDrive["TYPE"];
                                else
                                    $l_driveletter = $l_rowDrive["LETTER"];

                                foreach ($l_check_data AS $l_key => $l_val)
                                {
                                    if ($l_val['mount_point'] == $l_driveletter && $l_val['filesystem'] == $l_rowDrive['FILESYSTEM'])
                                    {

                                        unset($l_check_data[$l_key]);
                                        continue 2;
                                    }
                                }

                                $l_data = [
                                    'mount_point' => $l_driveletter,
                                    'filesystem'  => $l_rowDrive['FILESYSTEM'],
                                    'unit'        => $l_capacityUnitMB,
                                    'capacity'    => $l_rowDrive['TOTAL'],
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'mount_point' => ['value' => $l_driveletter],
                                        'filesystem'  => ['title_lang' => $l_rowDrive['FILESYSTEM']],
                                        'unit'        => ['title_lang' => 'MB'],
                                        'capacity'    => ['value' => $l_rowDrive['TOTAL']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_drive, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__STORAGE_DRIVE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_dao_drive->parse_import_array($l_data);
                                $l_entry_id    = $l_dao_drive->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_drive,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            } // foreach

                            if (count($l_check_data) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_check_data,
                                    $l_dao_drive,
                                    'isys_catg_drive_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__STORAGE_DRIVE',
                                    $l_logb_active
                                );
                            } // if
                        }
                        else
                        {
                            // Create
                            foreach ($l_hw_data['drive'] AS $l_rowDrive)
                            {

                                if ($l_rowDrive["LETTER"] == null) $l_driveletter = $l_rowDrive["TYPE"];
                                else
                                    $l_driveletter = $l_rowDrive["LETTER"];

                                $l_data = [
                                    'mount_point' => $l_driveletter,
                                    'filesystem'  => $l_rowDrive['FILESYSTEM'],
                                    'unit'        => $l_capacityUnitMB,
                                    'capacity'    => $l_rowDrive['TOTAL'],
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'mount_point' => ['value' => $l_driveletter],
                                        'filesystem'  => ['title_lang' => $l_rowDrive['FILESYSTEM']],
                                        'unit'        => ['title_lang' => 'MB'],
                                        'capacity'    => ['value' => $l_rowDrive['TOTAL']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_drive, null, $l_category_values);
                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__STORAGE_DRIVE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_dao_drive->parse_import_array($l_data);
                                $l_entry_id    = $l_dao_drive->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_drive,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            } // foreach
                        } // if
                    } // if

                    /**
                     * Network
                     */
                    if (isset($l_hw_data['net']))
                    {
                        verbose("Processing network");
                        $l_log->info("Processing network");
                        unset($l_check_ip, $l_check_net, $l_check_iface, $l_check_port, $l_already_imported_ips);
                        $l_already_imported_ips = [];
                        // IP info
                        $l_query_ip = 'SELECT t1.isys_catg_ip_list__id, t2.isys_cats_net_ip_addresses_list__title, t3.* ' . 'FROM isys_catg_ip_list  AS t1 ' . 'INNER JOIN isys_cats_net_ip_addresses_list AS t2 ON t2.isys_cats_net_ip_addresses_list__id = t1.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id ' . 'INNER JOIN isys_cats_net_list AS t3 ON t3.isys_cats_net_list__isys_obj__id = t2.isys_cats_net_ip_addresses_list__isys_obj__id ' . 'WHERE t1.isys_catg_ip_list__isys_obj__id = ' . $l_daoIP->convert_sql_id(
                                $l_objID
                            );
                        $l_res_ip   = $l_daoIP->retrieve($l_query_ip);
                        while ($l_row_ip = $l_res_ip->get_row())
                        {
                            $l_check_ip[]  = [
                                'data_id'   => $l_row_ip['isys_catg_ip_list__id'],
                                'title'     => $l_row_ip['isys_cats_net_ip_addresses_list__title'],
                                'net'       => $l_row_ip['isys_cats_net_list__isys_obj__id'],
                                'net_title' => $l_row_ip['isys_cats_net_list__address'],
                                'hostname'  => $l_row_ip['isys_catg_ip_list__hostname']
                            ];
                            $l_check_net[] = [
                                'data_id'    => $l_row_ip['isys_cats_net_list__id'],
                                'title'      => $l_row_ip['isys_cats_net_list__address'],
                                'mask'       => $l_row_ip['isys_cats_net_list__mask'],
                                'range_from' => $l_row_ip['isys_cats_net_list__address_range_from'],
                                'range_to'   => $l_row_ip['isys_cats_net_list__address_range_to'],
                            ];
                        }

                        // Port info
                        $l_query_port = 'SELECT * FROM isys_catg_port_list ' . 'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id ' . 'LEFT JOIN isys_catg_netp_list ON isys_catg_netp_list__id = isys_catg_port_list__isys_catg_netp_list__id ' . 'LEFT JOIN isys_port_type ON isys_port_type__id = isys_catg_port_list__isys_port_type__id ' . 'LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id ' . 'WHERE isys_catg_port_list__isys_obj__id = ' . $l_daoPort->convert_sql_id(
                                $l_objID
                            );
                        $l_res_port   = $l_daoPort->retrieve($l_query_port);
                        while ($l_row_port = $l_res_port->get_row())
                        {
                            $l_check_port[] = [
                                'data_id'            => $l_row_port['isys_catg_port_list__id'],
                                'title'              => $l_row_port['isys_catg_port_list__title'],
                                'mac'                => $l_row_port['isys_catg_port_list__mac'],
                                'speed'              => $l_row_port['isys_catg_port_list__port_speed_value'],
                                'speed_type'         => $l_row_port['isys_port_speed__id'],
                                'port_type'          => $l_row_port['isys_port_type__title'],
                                'active'             => $l_row_port['isys_catg_port_list__state_enabled'],
                                'assigned_connector' => $l_row_port['isys_catg_port_list__isys_catg_connector_list__id']
                            ];
                        }

                        // Interface info
                        $l_query_interface = 'SELECT isys_catg_netp_list__id, isys_catg_netp_list__title FROM isys_catg_netp_list ' . 'WHERE isys_catg_netp_list__isys_obj__id = ' . $l_dao_interface->convert_sql_id(
                                $l_objID
                            );
                        $l_res_iface       = $l_dao_interface->retrieve($l_query_interface);
                        while ($l_row_iface = $l_res_iface->get_row())
                        {
                            $l_check_iface[] = [
                                'data_id' => $l_row_iface['isys_catg_netp_list__id'],
                                'title'   => $l_row_iface['isys_catg_netp_list__title']
                            ];
                        }

                        foreach ($l_hw_data['net'] AS $l_hw_key => $l_row)
                        {

                            preg_match('/[0-9]*/', $l_row['SPEED'], $l_speed_arr);
                            $l_speed = $l_speed_arr[0];
                            preg_match('/[^0-9][^\s]*/', $l_row['SPEED'], $l_speed_type_arr);
                            $l_speed_type = ltrim($l_speed_type_arr[0]);
                            $l_speed_type_lower = strtolower($l_speed_type);

                            if ($l_speed_type_lower == 'mb/s' || $l_speed_type_lower == 'm' || $l_speed_type_lower == 'mb' || $l_speed_type_lower == 'mbps')
                            {
                                $l_speed_type_id = C__PORT_SPEED__MBIT_S;
                            }
                            elseif ($l_speed_type_lower == 'gb/s' || $l_speed_type_lower == 'g' || $l_speed_type_lower == 'gb' || $l_speed_type_lower == 'gbps')
                            {
                                $l_speed_type_id = C__PORT_SPEED__GBIT_S;
                            }

                            $l_address      = null;
                            $l_interface_id = null;
                            $l_port_id      = null;
                            $l_ip_id        = null;
                            $l_sync_ip      = true;
                            $l_sync_port    = true;
                            $l_sync_iface   = true;
                            $l_cidr_suffix  = null;

                            if (count($l_check_iface) > 0 && 1 == 2)
                            {
                                foreach ($l_check_iface AS $l_key => $l_iface)
                                {
                                    if (strcasecmp($l_iface['title'], $l_row['DESCRIPTION']) == 0)
                                    {
                                        $l_interface_id = $l_check_iface[$l_key]['data_id'];
                                        unset($l_check_iface[$l_key]);
                                        $l_sync_iface = false;
                                        break;
                                    }
                                }
                            }

                            if (count($l_check_port) > 0)
                            {
                                foreach ($l_check_port AS $l_key => $l_port)
                                {
                                    if ($l_port['port_type'] == $l_row['TYPE'] && strcmp(
                                            $l_port['mac'],
                                            $l_row['MACADDR']
                                        ) == 0 && $l_port['speed_type'] == $l_speed_type_id && $l_port['speed'] == isys_convert::speed($l_speed, $l_speed_type_id)
                                    )
                                    {
                                        $l_port_id = $l_check_port[$l_key]['data_id'];
                                        unset($l_check_port[$l_key]);
                                        $l_sync_port = false;
                                        break;
                                    }
                                }
                            }

                            if (count($l_check_ip) > 0)
                            {
                                foreach ($l_check_ip AS $l_key => $l_ip)
                                {
                                    if (($l_ip['title'] == $l_row['IPADDRESS'] || $l_ip['title'] == $l_row['IPDHCP']) && $l_ip['net_title'] == $l_row['IPSUBNET'])
                                    {
                                        if ($l_row['STATUS'] == 'UP')
                                        {
                                            if ($l_ip['hostname'] != $l_inventory['NAME'])
                                            {
                                                continue;
                                            }
                                        }
                                        else
                                        {
                                            if ($l_ip['hostname'] == '')
                                            {
                                                continue;
                                            }
                                        }

                                        $l_ip_id = $l_check_ip[$l_key]['data_id'];
                                        unset($l_check_ip[$l_key]);
                                        unset($l_check_net[$l_key]);
                                        $l_already_imported_ips[$l_ip_id] = $l_ip['title'];
                                        $l_sync_ip                        = false;
                                        break;
                                    }
                                }
                            }

                            // Interface sync
                            if ($l_sync_iface && 1 == 2)
                            {
                                $l_data = [
                                    'title' => $l_row['DESCRIPTION']
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title' => ['value' => $l_row['DESCRIPTION']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_dao_interface, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data  = $l_dao_interface->parse_import_array($l_data);
                                $l_interface_id = $l_dao_interface->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_dao_interface,
                                        $l_interface_id,
                                        !!$l_interface_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            } // if

                            if ($l_row['STATUS'] == 'Up')
                            {
                                $l_status = '1';
                            }
                            else
                            {
                                $l_status = '0';
                            } // if

                            // Port sync
                            if ($l_sync_port)
                            {
                                $l_data = [
                                    'title'      => $l_row['DESCRIPTION'],
                                    //'Port ' . $l_hw_key,
                                    'port_type'  => $l_row['TYPE'],
                                    'speed'      => isys_convert::speed($l_speed, $l_speed_type),
                                    'speed_type' => $l_speed_type_id,
                                    'mac'        => $l_row['MACADDR'],
                                    'active'     => $l_status
                                ];

                                // Interface to port.
                                if (isset($l_interface_id))
                                {
                                    $l_data['interface'] = $l_interface_id;
                                } // if

                                // Add hostaddress.
                                if (!$l_sync_ip)
                                {
                                    $l_data['addresses'] = [
                                        $l_ip_id
                                    ];
                                } // if

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'      => ['value' => $l_row['DESCRIPTION']],
                                        // 'Port ' . $l_hw_key),
                                        'port_type'  => ['title_lang' => $l_row['TYPE']],
                                        'speed'      => ['value' => $l_speed],
                                        'speed_type' => ['title_lang' => $l_speed_type],
                                        'mac'        => ['value' => $l_row['MACADDR']],
                                        'active'     => ['value' => ($l_status) ? _L('LC__UNIVERSAL__YES') : _L('LC__UNIVERSAL__NO')],
                                        'interface'  => ['value' => $l_row['DESCRIPTION']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoPort, null, $l_category_values);

                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                            serialize($l_changes)
                                        );
                                    } // if
                                } // if

                                $l_import_data = $l_daoPort->parse_import_array($l_data);
                                $l_port_id     = $l_daoPort->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoPort,
                                        $l_port_id,
                                        !!$l_port_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }

                            // Ip sync
                            if ($l_sync_ip)
                            {

                                // ip type check ipv4, ipv6
                                if (Ip::ip2long($l_row['IPSUBNET']) > 0 && Ip::ip2long($l_row['IPADDRESS']) > 0)
                                {
                                    $l_subnet  = $l_row['IPSUBNET'];
                                    $l_dhcp    = false;
                                    $l_address = $l_row['IPADDRESS'];
                                }
                                elseif (Ip::ip2long($l_row['IPDHCP']) > 0)
                                {
                                    /*if(($l_pos = strpos($l_row['IPDHCP'],'.0')) > 0){
										$l_subnet = substr($l_row['IPDHCP'], 0, ($l_pos));
										$l_amount = substr_count($l_subnet, '.');
										while($l_amount < 3){
											$l_amount++;
											$l_subnet .= '.0';
										}
									} else{
										$l_ip_arr = explode('.', $l_row['IPDHCP']);
										$l_subnet = $l_ip_arr[0].'.'.$l_ip_arr[1].'.'.$l_ip_arr[2].'.0';
									}*/
                                    $l_subnet  = $l_row['IPSUBNET'];
                                    $l_dhcp    = true;
                                    $l_address = $l_row['IPDHCP'];
                                }
                                elseif ($l_row['IPADDRESS'] != '' && $l_row['IPMASK'] != '')
                                {
                                    // Calculate net ip
                                    $l_subnet  = Ip::validate_net_ip($l_row['IPADDRESS'], $l_row['IPMASK'], null, true);
                                    $l_dhcp    = false;
                                    $l_address = $l_row['IPADDRESS'];
                                }

                                // Secondary Check
                                if ($l_address !== null && !in_array($l_address, $l_already_imported_ips))
                                {
                                    if (Ip::validate_ip($l_subnet))
                                    {
                                        $l_net_type    = C__CATS_NET_TYPE__IPV4;
                                        $l_cidr_suffix = Ip::calc_cidr_suffix($l_row['IPMASK']);
                                        if ($l_dhcp) $l_ip_type = C__CATP__IP__ASSIGN__DHCP;
                                        else $l_ip_type = C__CATP__IP__ASSIGN__STATIC;
                                        $l_net_id = C__OBJ__NET_GLOBAL_IPV6;
                                    }
                                    elseif (Ip::validate_ipv6($l_subnet))
                                    {
                                        $l_net_type    = C__CATS_NET_TYPE__IPV6;
                                        $l_cidr_suffix = Ip::calc_cidr_suffix_ipv6($l_row['IPMASK']);
                                        if ($l_dhcp) $l_ip_type = C__CMDB__CATG__IP__DHCPV6;
                                        else $l_ip_type = C__CMDB__CATG__IP__STATIC;
                                        $l_net_id = C__OBJ__NET_GLOBAL_IPV6;
                                    }

                                    // Check Net
                                    if (isset($l_net_arr[$l_subnet]))
                                    {
                                        $l_net_id    = $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__isys_obj__id'];
                                        $l_net_title = $l_net_arr[$l_subnet]['row_data']['isys_obj__title'];

                                        if ($l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__address_range_from'] === '')
                                        {
                                            // Update net because the range is not set
                                            $l_ip_range                                                                 = Ip::calc_ip_range(
                                                $l_subnet,
                                                $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__mask']
                                            );
                                            $l_from_long                                                                = Ip::ip2long($l_ip_range['from']);
                                            $l_to_long                                                                  = Ip::ip2long($l_ip_range['to']);
                                            $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__address_range_from'] = $l_ip_range['from'];
                                            $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__address_range_to']   = $l_ip_range['to'];
                                            $l_update                                                                   = 'UPDATE isys_cats_net_list SET isys_cats_net_list__address_range_from = ' . $l_daoCMDB->convert_sql_text(
                                                    $l_ip_range['from']
                                                ) . ',
												isys_cats_net_list__address_range_to = ' . $l_daoCMDB->convert_sql_text($l_ip_range['to']) . ',
												isys_cats_net_list__address_range_from_long = ' . $l_daoCMDB->convert_sql_text($l_from_long) . ',
												isys_cats_net_list__address_range_to_long = ' . $l_daoCMDB->convert_sql_text($l_to_long) . '
												WHERE isys_cats_net_list__isys_obj__id = ' . $l_daoCMDB->convert_sql_id($l_net_id);
                                            $l_daoCMDB->update($l_update);
                                        } // if
                                    }
                                    else
                                    {
                                        // Create net
                                        $l_gateway_arr = $l_daoCMDB->retrieve(
                                            'SELECT isys_catg_ip_list__id FROM isys_catg_ip_list ' . 'INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' . 'WHERE isys_cats_net_ip_addresses_list__title = ' . $l_daoCMDB->convert_sql_text(
                                                $l_row['IPGATEWAY']
                                            )
                                        )
                                            ->__to_array();
                                        if ($l_gateway_arr)
                                        {
                                            $l_gateway_id = $l_gateway_arr['isys_catg_ip_list__id'];
                                        }
                                        else
                                        {
                                            $l_gateway_id = null;
                                        }
                                        $l_net_id    = $l_daoCMDB->insert_new_obj(
                                            C__OBJTYPE__LAYER3_NET,
                                            false,
                                            $l_subnet . '/' . $l_cidr_suffix,
                                            null,
                                            C__RECORD_STATUS__NORMAL
                                        );
                                        $l_net_title = $l_subnet;
                                        $l_ip_range  = Ip::calc_ip_range($l_subnet, $l_row['IPMASK']);
                                        $l_daoNet_s->create(
                                            $l_net_id,
                                            C__RECORD_STATUS__NORMAL,
                                            $l_subnet,
                                            $l_net_type,
                                            $l_subnet,
                                            $l_row['IPMASK'],
                                            $l_gateway_id,
                                            false,
                                            $l_ip_range['from'],
                                            $l_ip_range['to'],
                                            null,
                                            null,
                                            '',
                                            $l_cidr_suffix
                                        );

                                        $l_net_arr[$l_subnet] = [
                                            'row_data' => [
                                                'isys_cats_net_list__isys_obj__id' => $l_net_id,
                                                'isys_obj__title'                  => $l_subnet,
                                            ]
                                        ];
                                    }

                                    $l_data = [
                                        'net_type' => $l_net_type,
                                        'primary'  => $l_status,
                                        'active'   => '1',
                                        'net'      => $l_net_id,
                                        'hostname' => ($l_status > 0) ? $l_inventory['NAME'] : ''
                                    ];

                                    switch ($l_net_type)
                                    {
                                        case C__CATS_NET_TYPE__IPV4:
                                            $l_data['ipv4_address']    = $l_address;
                                            $l_data['ipv4_assignment'] = $l_ip_type;
                                            break;
                                        case C__CATS_NET_TYPE__IPV6:
                                            $l_data['ipv6_address']    = $l_address;
                                            $l_data['ipv6_assignment'] = $l_ip_type;
                                            break;
                                    }

                                    // add port
                                    if ($l_port_id > 0)
                                    {
                                        $l_data['assigned_port'] = $l_port_id;
                                    }
                                    else
                                    {
                                        $l_data['assigned_port'] = null;
                                    }

                                    if ($l_logb_active)
                                    {
                                        switch ($l_net_type)
                                        {
                                            case C__CATS_NET_TYPE__IPV4:
                                                $l_ip_assignment                                            = $l_daoCMDB->get_dialog('isys_ip_assignment', $l_ip_type)
                                                    ->get_row();
                                                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                    'ipv4_address'    => ['value' => $l_row["IPADDRESS"]],
                                                    'ipv4_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                                    'net_type'        => ['title_lang' => 'IPv4']
                                                ];
                                                break;
                                            case C__CATS_NET_TYPE__IPV6:
                                                $l_ip_assignment                                            = $l_daoCMDB->get_dialog('isys_ipv6_assignment', $l_ip_type)
                                                    ->get_row();
                                                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                    'ipv6_address'    => ['value' => $l_row["IPADDRESS"]],
                                                    'ipv6_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                                    'net_type'        => ['title_lang' => 'IPv6'],
                                                ];
                                                break;
                                        }

                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname'] = ['value' => ($l_status > 0) ? $l_inventory["NAME"] : ''];
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['net']      = ['value' => $l_net_id];
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['active']   = ['value' => $l_status];
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['primary']  = ['value' => $l_status];

                                        $l_changes = $l_dao_logb->prepare_changes($l_daoIP, null, $l_category_values);

                                        if (count($l_changes) > 0)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__CATG__IP_ADDRESS',
                                                serialize($l_changes)
                                            );
                                            unset($l_changes);
                                        }
                                    }

                                    $l_import_data                         = $l_daoIP->parse_import_array($l_data);
                                    $l_ip_data_id                          = $l_daoIP->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);
                                    $l_already_imported_ips[$l_ip_data_id] = $l_address;

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_daoIP,
                                            $l_ip_data_id,
                                            !!$l_ip_data_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                }
                            }
                        }
                        if (count($l_check_iface) > 0 && 1 == 2) $this->delete_entries_from_category(
                            $l_check_iface,
                            $l_dao_interface,
                            'isys_catg_netp_list',
                            $l_objID,
                            $l_thisObjTypeID,
                            'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                            $l_logb_active
                        );

                        if (count($l_check_port) > 0)
                        {
                            foreach ($l_check_port AS $l_val)
                            {
                                $l_cableConID = $l_daoCableCon->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                                $l_daoCableCon->delete_cable_connection($l_cableConID);
                                $l_daoCableCon->delete_connector($l_val["assigned_connector"]);
                                $l_daoPort->delete_entry($l_val['data_id'], 'isys_catg_port_list');

                                if ($l_logb_active) $l_mod_event_manager->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                    "-modified from OCS-",
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CATD__PORT',
                                    null
                                );
                            }
                        }

                        if (count($l_check_ip) > 0) $this->delete_entries_from_category(
                            $l_check_ip,
                            $l_daoIP,
                            'isys_catg_ip_list',
                            $l_objID,
                            $l_thisObjTypeID,
                            'LC__CATG__IP_ADDRESS',
                            $l_logb_active
                        );
                    }

                    /**
                     * Universal interfaces
                     */
                    if (isset($l_hw_data['ui']))
                    {
                        verbose("Processing UI");
                        $l_log->info("Processing UI");
                        $l_check_data = [];

                        $l_res_ui    = $l_daoUI->retrieve(
                            'SELECT isys_catg_ui_list__id, isys_catg_ui_list__title, isys_catg_ui_list__isys_catg_connector_list__id, ' . 'isys_ui_plugtype__title ' . 'FROM isys_catg_ui_list ' . 'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id ' . 'LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id ' . 'WHERE isys_catg_ui_list__isys_obj__id = ' . $l_daoUI->convert_sql_id(
                                $l_objID
                            )
                        );
                        $l_ui_amount = $l_res_ui->num_rows();

                        if ($l_ui_amount > 0)
                        {
                            // data from i-doit
                            while ($l_row_ui = $l_res_ui->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'            => $l_row_ui['isys_catg_ui_list__id'],
                                    'title'              => $l_row_ui['isys_catg_ui_list__title'],
                                    'plug'               => $l_row_ui['isys_ui_plugtype__title'],
                                    'assigned_connector' => $l_row_ui['isys_catg_ui_list__isys_catg_connector_list__id'],
                                    'description'        => $l_row_ui['isys_catg_ui_list__description']
                                ];
                            }

                            foreach ($l_hw_data['ui'] AS $l_row)
                            {
                                // Check if data already exists in i-doit
                                foreach ($l_check_data AS $l_key => $l_value)
                                {
                                    if ($l_value['title'] == $l_row["CAPTION"] && $l_value['plug'] == $l_row['TYPE'])
                                    {
                                        unset($l_check_data[$l_key]);
                                        continue 2;
                                    }
                                }

                                // Create new data
                                $l_data = [
                                    'title'       => $l_row["CAPTION"],
                                    'plug'        => $l_row["TYPE"],
                                    'type'        => $l_conTypeTitle,
                                    'description' => $l_row['DESCRIPTION'],
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title' => ['value' => $l_row["CAPTION"]],
                                        'plug'  => ['title_lang' => $l_row["TYPE"]],
                                        'type'  => ['title_lang' => $l_conTypeTitle],
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoUI, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_daoUI->parse_import_array($l_data);
                                $l_entry_id    = $l_daoUI->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoUI,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                            foreach ($l_check_data AS $l_val)
                            {
                                $l_cableConID = $l_daoCableCon->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                                $l_daoCableCon->delete_cable_connection($l_cableConID);
                                $l_daoCableCon->delete_connector($l_val["assigned_connector"]);
                                $l_daoUI->delete_entry($l_val['data_id'], 'isys_catg_ui_list');

                                if ($l_logb_active) $l_mod_event_manager->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                    "-modified from OCS-",
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                    null
                                );
                            }

                        }
                        else
                        {
                            // create
                            foreach ($l_hw_data['ui'] AS $l_row)
                            {

                                $l_data = [
                                    'title'       => $l_row["CAPTION"],
                                    'plug'        => $l_row["TYPE"],
                                    'type'        => $l_conTypeTitle,
                                    'description' => $l_row['DESCRIPTION'],
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title' => ['value' => $l_row["CAPTION"]],
                                        'plug'  => ['title_lang' => $l_row["TYPE"]],
                                        'type'  => ['title_lang' => $l_conTypeTitle],
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoUI, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_daoUI->parse_import_array($l_data);
                                $l_entry_id    = $l_daoUI->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoUI,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    /**
                     * Storage
                     */
                    if (isset($l_hw_data['stor']))
                    {
                        verbose("Processing storage");
                        $l_log->info("Processing storage");
                        $l_check_data = [];

                        $l_res_stor = $l_daoStor->retrieve(
                            'SELECT isys_catg_stor_list__title, isys_stor_manufacturer__title, isys_stor_model__title, ' . 'isys_catg_stor_list__capacity, isys_catg_stor_list__id ' . 'FROM isys_catg_stor_list ' . 'LEFT JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id ' . 'LEFT JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id ' . 'WHERE isys_catg_stor_list__isys_obj__id = ' . $l_daoStor->convert_sql_id(
                                $l_objID
                            )
                        );

                        $l_stor_amount = $l_res_stor->num_rows();

                        if ($l_stor_amount > 0)
                        {
                            // Check, Delete, Create
                            while ($l_row = $l_res_stor->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_row['isys_catg_stor_list__id'],
                                    'title'        => $l_row['isys_catg_stor_list__title'],
                                    'manufacturer' => $l_row['isys_stor_manufacturer__title'],
                                    'model'        => $l_row['isys_stor_model__title'],
                                    'capacity'     => $l_row['isys_catg_stor_list__capacity']
                                ];
                            }

                            foreach ($l_hw_data['stor'] AS $l_row)
                            {
                                // Check if data already exists in i-doit
                                foreach ($l_check_data AS $l_key => $l_value)
                                {
                                    if ($l_value['title'] == $l_row["NAME"] && $l_value['manufacturer'] == $l_row['MANUFACTURER'] && $l_value['model'] == $l_row['MODEL'] && $l_value['capacity'] == isys_convert::memory(
                                            $l_row['DISKSIZE'],
                                            $l_capacityUnitMB
                                        )
                                    )
                                    {
                                        unset($l_check_data[$l_key]);
                                        continue 2;
                                    }
                                }

                                if ($l_row["TYPE"] == null || $l_row["TYPE"] == "") $l_deviceType = $this->parseStorageType($l_row["DESCRIPTION"]);
                                else
                                    $l_deviceType = $this->parseStorageType($l_row["TYPE"]);

                                // Create new data
                                $l_data = [
                                    'title'        => $l_row['NAME'],
                                    'manufacturer' => $l_row['MANUFACTURER'],
                                    'model'        => $l_row['MODEL'],
                                    'capacity'     => $l_row['DISKSIZE'],
                                    'unit'         => $l_capacityUnitMB,
                                    'description'  => $l_row['DESCRIPTION'],
                                    'type'         => $l_deviceType
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'        => ['value' => $l_row["NAME"]],
                                        'capacity'     => ['value' => $l_row["DISKSIZE"]],
                                        'unit'         => ['title_lang' => 'MB'],
                                        'manufacturer' => ['title_lang' => $l_row["MANUFACTURER"]],
                                        'model'        => ['title_lang' => $l_row["MODEL"]]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoStor, null, $l_category_values);

                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__UNIVERSAL__DEVICES',
                                            serialize($l_changes)
                                        );
                                    }
                                }

                                $l_import_data = $l_daoStor->parse_import_array($l_data);
                                $l_entry_id    = $l_daoStor->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoStor,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }

                            if (count($l_check_data) > 0) $this->delete_entries_from_category(
                                $l_check_data,
                                $l_daoStor,
                                'isys_catg_stor_list',
                                $l_objID,
                                $l_thisObjTypeID,
                                'LC__UNIVERSAL__DEVICES',
                                $l_logb_active
                            );

                        }
                        else
                        {
                            // create
                            foreach ($l_hw_data['stor'] AS $l_row)
                            {

                                if ($l_row["TYPE"] == null || $l_row["TYPE"] == "") $l_deviceType = $this->parseStorageType($l_row["DESCRIPTION"]);
                                else
                                    $l_deviceType = $this->parseStorageType($l_row["TYPE"]);

                                // Create new data
                                $l_data = [
                                    'title'        => $l_row['NAME'],
                                    'manufacturer' => $l_row['MANUFACTURER'],
                                    'model'        => $l_row['MODEL'],
                                    'capacity'     => $l_row['DISKSIZE'],
                                    'unit'         => $l_capacityUnitMB,
                                    'description'  => $l_row['DESCRIPTION'],
                                    'type'         => $l_deviceType
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'        => ['value' => $l_row["NAME"]],
                                        'capacity'     => ['value' => $l_row["DISKSIZE"]],
                                        'unit'         => ['title_lang' => 'MB'],
                                        'manufacturer' => ['title_lang' => $l_row["MANUFACTURER"]],
                                        'model'        => ['title_lang' => $l_row["MODEL"]]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoStor, null, $l_category_values);

                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__UNIVERSAL__DEVICES',
                                            serialize($l_changes)
                                        );
                                    }
                                }

                                $l_import_data = $l_daoStor->parse_import_array($l_data);
                                $l_entry_id    = $l_daoStor->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoStor,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    //				if(isset($l_hw_data['virtual_machine'])){
                    //					verbose("Processing virtual machine");
                    //					$l_check_data = array();
                    //
                    //					$l_res_gs = $l_dao_gs->get_data(null, $l_objID);
                    //
                    //					$l_vm_amount = $l_res_gs->num_rows();
                    //
                    //					if($l_vm_amount > 0){
                    //						// Create and delete
                    //						while($l_rowGS = $l_res_gs->get_row()){
                    //							$l_check_data[] = array(
                    //								'data_id' => $l_rowGS['isys_catg_virtual_machine_list__id'],
                    //								'title' => $l_rowGS['isys_obj__title'],
                    //								'system' => $l_rowGS['isys_catg_virtual_machine_list__isys_vm_type__id']
                    //							);
                    //						}
                    ////						$l_vm_types
                    //
                    //						foreach($l_hw_data['virtual_machine'] AS $l_rowVM){
                    //
                    //							// Check if Virtual machine exists as object
                    //
                    //
                    //							if ($l_rowDrive["LETTER"] == null)
                    //								$l_driveletter = $l_rowDrive["TYPE"];
                    //							else
                    //								$l_driveletter = $l_rowDrive["LETTER"];
                    //
                    //							foreach($l_check_data AS $l_key => $l_val){
                    //								if($l_val['mount_point'] == $l_driveletter &&
                    //									$l_val['filesystem'] == $l_rowDrive['FILESYSTEM']){
                    //
                    //									unset($l_check_data[$l_key]);
                    //									continue 2;
                    //								}
                    //							}
                    //
                    //							$l_data = array(
                    //								'mount_point' => $l_driveletter,
                    //								'filesystem' => $l_rowDrive['FILESYSTEM'],
                    //								'unit' => $l_capacityUnitMB,
                    //								'capacity' => $l_rowDrive['TOTAL'],
                    //							);
                    //
                    //							if($l_logb_active){
                    //								$l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = array(
                    //									'mount_point' => array('value' => $l_driveletter),
                    //									'filesystem' => array('title_lang' => $l_rowDrive['FILESYSTEM']),
                    //									'unit' => array('title_lang' => 'MB'),
                    //									'capacity' => array('value' => $l_rowDrive['TOTAL'])
                    //								);
                    //
                    //								$l_changes = $l_dao_logb->prepare_changes($l_dao_drive, NULL, $l_category_values);
                    //
                    //								if(count($l_changes) > 0)
                    //									$l_mod_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $l_objID, $l_thisObjTypeID, 'LC__STORAGE_DRIVE', serialize($l_changes));
                    //							}
                    //							$l_dao_drive->sync($l_dao_drive->parse_import_array($l_data), $l_objID, isys_import_handler_cmdb::C__CREATE);
                    //
                    //						}
                    //						if(count($l_check_data) > 0)
                    //							$this->delete_entries_from_category($l_check_data, $l_dao_drive, 'isys_catg_drive_list', $l_objID, $l_thisObjTypeID, 'LC__STORAGE_DRIVE', $l_logb_active);
                    //
                    //					} else{
                    //						// Create
                    //						foreach($l_hw_data['drive'] AS $l_rowDrive){
                    //
                    //							if ($l_rowDrive["LETTER"] == null)
                    //								$l_driveletter = $l_rowDrive["TYPE"];
                    //							else
                    //								$l_driveletter = $l_rowDrive["LETTER"];
                    //
                    //							$l_data = array(
                    //								'mount_point' => $l_driveletter,
                    //								'filesystem' => $l_rowDrive['FILESYSTEM'],
                    //								'unit' => $l_capacityUnitMB,
                    //								'capacity' => $l_rowDrive['TOTAL'],
                    //							);
                    //
                    //							if($l_logb_active){
                    //								$l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = array(
                    //									'mount_point' => array('value' => $l_driveletter),
                    //									'filesystem' => array('title_lang' => $l_rowDrive['FILESYSTEM']),
                    //									'unit' => array('title_lang' => 'MB'),
                    //									'capacity' => array('value' => $l_rowDrive['TOTAL'])
                    //								);
                    //
                    //								$l_changes = $l_dao_logb->prepare_changes($l_dao_drive, NULL, $l_category_values);
                    //								if(count($l_changes) > 0)
                    //									$l_mod_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED', "-modified from OCS-", $l_objID, $l_thisObjTypeID, 'LC__STORAGE_DRIVE', serialize($l_changes));
                    //							}
                    //							$l_dao_drive->sync($l_dao_drive->parse_import_array($l_data), $l_objID, isys_import_handler_cmdb::C__CREATE);
                    //						}
                    //					}
                    //
                    //				}

                    if ($l_object_updated === true)
                    {
                        verbose($l_inventory["NAME"] . " succesfully updated\n\n");
                        $l_log->info("\"" . $l_inventory["NAME"] . "\" succesfully updated.");
                    }
                    else
                    {
                        verbose($l_inventory["NAME"] . " succesfully imported\n\n");
                        $l_log->info("\"" . $l_inventory["NAME"] . "\" succesfully imported.");
                    } // if

                    $l_log->flush_log(true, false);
                }
            }

            /**
             * @TODO Memory in ocs database the value is puzzling
             */
            if (count($l_snmpIDs) > 0)
            {
                $l_log->info("Found " . count($l_snmpIDs) . " SNMP devices.");
                $l_log->info("Starting import for SNMP devices.");
                $l_object_ids = [];
                foreach ($l_snmpIDs AS $l_position => $l_snmp_id)
                {
                    if(!($l_hw_data = $this->get_snmp_info(
                        $l_daoOCS,
                        $l_snmp_id,
                        $l_add_memory,
                        $l_add_storage,
                        $l_add_net,
                        $l_add_cpu,
                        $l_add_ui,
                        $l_add_model,
                        $l_add_application,
                        $l_add_graphic,
                        $l_add_sound,
                        $l_add_drive,
                        $l_add_virtual_machine)))
                    {
                        // Device has already been imported
                        continue;
                    } // if

                    $l_port_descriptions = $l_port_connections = [];

                    $l_objID          = false;
                    $l_thisObjTypeID  = null;
                    $l_object_updated = false;

                    $l_inventory         = $l_hw_data['inventory'];
                    $l_inventory["NAME"] = trim($l_inventory["NAME"]);

                    if ($l_inventory == null)
                    {
                        verbose("Object with ID " . $l_snmp_id . " does not exist");
                        $l_log->debug("Object wit ID " . $l_snmp_id . " does not exist. Skipping to next device.");
                        continue;
                    } // if

                    if (isset($l_hw_data['model']) && count($l_hw_data['model']) === 1)
                    {
                        $l_inventory['SSN'] = $l_hw_data['model'][0]['SERIALNUMBER'];
                    } // if

                    if (isset($l_inventory['SSN']))
                    {
                        $l_log->debug("Serialnumber found for " . $l_inventory["NAME"] . ".");
                        $l_log->debug("Using Hostname: \"" . $l_inventory["NAME"] . "\" and \" Serialnumber: \"" . $l_inventory['SSN'] . "\" to identify object in i-doit.");

                        $l_objID = $l_daoCMDB->get_object_by_hostname_serial_mac($l_inventory["NAME"], $l_inventory["SSN"]);

                        if (!$l_objID)
                        {
                            $l_log->debug("Check failed.");
                        }
                        else
                        {
                            $l_log->debug("Check successful.");
                        } // if
                    }
                    else
                    {
                        if (count($l_hw_data['model']) > 1)
                        {
                            verbose("Could not identify Serialnumber for device \"" . $l_inventory["NAME"] . "\".");
                            $l_log->debug("Could not identify Serialnumber for device \"" . $l_inventory["NAME"] . "\".");
                        }
                        else
                        {
                            verbose("No Serialnumber found for \"" . $l_inventory["NAME"] . "\".");
                            $l_log->debug("No Serialnumber found for \"" . $l_inventory["NAME"] . "\".");
                        } // if
                    } // if

                    // New object, or update existing?
                    if ($l_objID === false && $l_inventory['macaddr'] != '')
                    {
                        $l_macaddresses = explode(',', $l_inventory['macaddr']);
                        $l_log->debug("MAC-addresses found for " . $l_inventory["NAME"] . ".");
                        $l_log->debug("Using Hostname: \"" . $l_inventory["NAME"] . "\", \" MAC-Addresses: \"" . $l_inventory['macaddr'] . "\" to identify object in i-doit.");
                        foreach ($l_macaddresses AS $l_macaddress)
                        {
                            $l_objID = $l_daoCMDB->get_object_by_hostname_serial_mac($l_inventory["NAME"], null, $l_macaddress);
                            if ($l_objID) continue;
                        } // foreach

                        if (!$l_objID)
                        {
                            $l_log->debug("Check failed.");
                        }
                        else
                        {
                            $l_log->debug("Check successful.");
                        } // if
                    }
                    else
                    {
                        verbose("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                        $l_log->debug("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                    } // if

                    if (is_numeric($l_objType_snmp))
                    {
                        $l_log->debug("Object type has been defined. Using \"" . $l_daoCMDB->get_objtype_name_by_id_as_string($l_objType_snmp) . "\" as object type.");
                        $l_thisObjTypeID = $l_objType_snmp;
                    }
                    else
                    {
                        if (isset($l_objtype_snmp_arr[$l_position]) && $l_objtype_snmp_arr[$l_position] > 0)
                        {
                            $l_log->debug(
                                "Object type is set in dialog. Using \"" . $l_daoCMDB->get_objtype_name_by_id_as_string(
                                    $l_objtype_snmp_arr[$l_position]
                                ) . "\" as object type."
                            );
                            $l_thisObjTypeID = $l_objtype_snmp_arr[$l_position];
                        }
                        else
                        {
                            foreach ($l_config_obj_types AS $l_conf_objtype_id => $l_prefix)
                            {
                                if ($l_thisObjTypeID === null)
                                {
                                    $l_prefix_arr = null;
                                    if (strpos($l_prefix, ',') !== false)
                                    {
                                        $l_prefix_arr = explode(',', $l_prefix);
                                    } // if

                                    if (is_array($l_prefix_arr))
                                    {
                                        foreach ($l_prefix_arr AS $l_sub_prefix)
                                        {
                                            if (self::check_pattern_for_objtype($l_sub_prefix, $l_inventory["TAG"], $l_inventory["NAME"]))
                                            {
                                                $l_thisObjTypeID = $l_conf_objtype_id;
                                                break;
                                            } // if
                                        } // foreach
                                    }
                                    else
                                    {
                                        if (self::check_pattern_for_objtype($l_prefix, $l_inventory["TAG"], $l_inventory["NAME"]))
                                        {
                                            $l_thisObjTypeID = $l_conf_objtype_id;
                                        } // if
                                    } // if
                                }
                                else
                                {
                                    break;
                                } // if
                            } // foreach

                            if ($l_thisObjTypeID === null)
                            {
                                // Get Object type from ocs
                                switch ($l_inventory['OBJTYPE'])
                                {
                                    case 'blade':
                                        $l_thisObjTypeID = C__OBJTYPE__BLADE_CHASSIS;
                                        break;
                                    case 'printer':
                                        $l_thisObjTypeID = C__OBJTYPE__PRINTER;
                                        break;
                                    case 'switch':
                                        $l_thisObjTypeID = C__OBJTYPE__SWITCH;
                                        break;
                                    case 'router':
                                        $l_thisObjTypeID = C__OBJTYPE__ROUTER;
                                        break;
                                    case 'server':
                                        $l_thisObjTypeID = C__OBJTYPE__SERVER;
                                        break;
                                    default:
                                        $l_thisObjTypeID = $l_objType;
                                        break;
                                }
                                verbose(
                                    "Could not determine object type from configuration. Using object type " . $l_inventory['OBJTYPE'] . " with object type id " . $l_thisObjTypeID . "."
                                );
                                $l_log->debug(
                                    "Could not determine object type from configuration. Using object type " . $l_daoCMDB->get_objtype_name_by_id_as_string(
                                        $l_inventory['OBJTYPE']
                                    ) . " with object type id " . $l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID) . "."
                                );
                            }
                        }
                    }

                    // last attempt
                    if ($l_objID === false)
                    {
                        $l_log->debug("Object in i-doit still not found. Checking field isys_obj__hostname with \"" . $l_inventory["NAME"] . "\".");
                        $l_query = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__hostname = ' . $l_daoCMDB->convert_sql_text($l_inventory["NAME"]);
                        if ($l_thisObjTypeID > 0)
                        {
                            $l_query .= ' AND isys_obj__isys_obj_type__id = ' . $l_daoCMDB->convert_sql_id($l_thisObjTypeID);
                        } // if
                        $l_objID_res = $l_daoCMDB->retrieve($l_query);
                        if ($l_objID_res->num_rows() > 0)
                        {
                            $l_objID = $l_objID_res->get_row_value('isys_obj__id');
                        } // if

                        if ($l_objID === false)
                        {
                            $l_log->debug("Last attempt to identify object in i-doit. Checking field isys_obj__title with \"" . $l_inventory["NAME"] . "\" and object type.");
                            $l_query = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = ' . $l_daoCMDB->convert_sql_text($l_inventory["NAME"]);
                            if ($l_thisObjTypeID > 0)
                            {
                                $l_query .= ' AND isys_obj__isys_obj_type__id = ' . $l_daoCMDB->convert_sql_id($l_thisObjTypeID);
                            } // if
                            $l_objID_res = $l_daoCMDB->retrieve($l_query);
                            if ($l_objID_res->num_rows() > 0)
                            {
                                $l_objID = $l_objID_res->get_row_value('isys_obj__id');
                            } // if
                        } // if
                    } // if

                    if ($l_objID)
                    {
                        $l_log->info("Object found. Updating object-ID: " . $l_objID);
                        $l_object_updated = true;
                        // Update existing object
                        $l_row           = $l_daoCMDB->get_object_by_id($l_objID)
                            ->get_row();
                        $l_thisObjTypeID = $l_row["isys_obj__isys_obj_type__id"];

                        $l_update_msg = "Updating existing object " . $l_inventory["NAME"] . " (" . _L(
                                $l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_row['isys_obj_type__id']))
                            ) . ")";

                        verbose($l_update_msg);
                        $l_log->debug($l_update_msg);
                        $l_update_title = '';

                        /**
                         * Main object data
                         */
                        if ($l_row['isys_obj__title'] !== $l_inventory['NAME'])
                        {
                            $l_log->debug("Object title is differnt: \"" . $l_row['isys_obj__title'] . "\" (i-doit) !== \"" . $l_inventory["NAME"] . "\" (OCS).");
                            if (is_array($l_daoGl->validate(['title' => $l_inventory['NAME']])) || isys_tenantsettings::get('cmdb.unique.object-title'))
                            {
                                $l_title        = $l_daoCMDB->generate_unique_obj_title($l_inventory['NAME']);
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_title) . ", ";
                            }
                            else
                            {
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_inventory['NAME']) . ", ";
                            } // if
                        } // if

                        $l_update = "UPDATE isys_obj SET " . $l_update_title . "isys_obj__hostname = " . $l_daoCMDB->convert_sql_text(
                                $l_inventory["NAME"]
                            ) . ", " . "isys_obj__updated     =    NOW(), " . "isys_obj__updated_by  =    '" . $g_comp_session->get_current_username(
                            ) . "', " . "isys_obj__imported    =    NOW() ";

                        if ($l_row['isys_obj__description'] == '')
                        {
                            $l_update .= ", isys_obj__description = " . $l_daoCMDB->convert_sql_text($l_inventory['DESCRIPTION']) . " ";
                        } // if

                        if (isset($l_objtype_snmp_arr[$l_position]) && $l_objtype_snmp_arr[$l_position] > 0)
                        {
                            $l_log->debug("Updating object with object type: " . $l_daoCMDB->get_objtype_name_by_id_as_string($l_objtype_snmp_arr[$l_position]) . ".");
                            $l_update .= ", isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id($l_objtype_snmp_arr[$l_position]) . " ";
                        } // if

                        $l_daoCMDB->update(
                            $l_update . " WHERE isys_obj__id    =    " . $l_daoCMDB->convert_sql_text($l_row["isys_obj__id"]) . ";"
                        );
                    }
                    else
                    {
                        verbose("Creating new object " . $l_inventory["NAME"] . " (" . _L($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")");
                        $l_log->info(
                            "Object not found. Creating new object " . $l_inventory["NAME"] . " (" . _L($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")"
                        );

                        $l_objID = $l_daoCMDB->insert_new_obj(
                            $l_thisObjTypeID,
                            false,
                            $l_inventory["NAME"],
                            null,
                            C__RECORD_STATUS__NORMAL,
                            $l_inventory["NAME"],
                            null,
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $l_cmdb_status_id
                        );

                        $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_objID, $l_thisObjTypeID);
                    }

                    /*
                     * Clear categories hostaddress and port
                     */
                    if ($l_overwrite_ip_port)
                    {
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_ip_list', true);
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_port_list', true);
                        verbose("Categories hostaddress and port cleared.", true);
                        $l_log->info("Categories hostaddress and port cleared.");
                    } // if

                    $l_object_ids[] = $l_objID;

                    /**
                     * Model
                     */
                    if (isset($l_hw_data['model']))
                    {
                        verbose("Processing model");
                        $l_log->info("Processing model");
                        $l_data = [];

                        $l_rowModel = null;
                        $l_row      = $l_hw_data['model'];

                        if (count($l_row) > 1)
                        {
                            // Its a chassis
                            $l_assigned_switch_stack = [];
                            foreach ($l_row AS $l_switch_stack)
                            {
                                $l_obj_id_switch_stack = $l_daoCMDB->get_object_by_hostname_serial_mac(
                                    null,
                                    $l_switch_stack['SERIALNUMBER'],
                                    null,
                                    $l_inventory["NAME"] . ' - ' . $l_switch_stack['TITLE']
                                );
                                if (!$l_obj_id_switch_stack)
                                {
                                    $l_obj_id_switch_stack = $l_daoCMDB->insert_new_obj(
                                        C__OBJTYPE__SWITCH,
                                        null,
                                        $l_inventory["NAME"] . ' - ' . $l_switch_stack['TITLE'],
                                        null,
                                        C__RECORD_STATUS__NORMAL
                                    );

                                    $l_data['manufacturer'] = $l_switch_stack["MANUFACTURER"] = (trim(
                                        $l_switch_stack["MANUFACTURER"]
                                    ) == '' ? $l_model_default_manufact : trim($l_switch_stack["MANUFACTURER"]));
                                    $l_data['serial']       = $l_switch_stack["SERIALNUMBER"];
                                    $l_data['title']        = $l_switch_stack["DESCRIPTION"];
                                    $l_data['firmware']     = ($l_switch_stack["FIRMVERSION"] != '') ? $l_switch_stack["FIRMVERSION"] : $l_switch_stack["FIRMVERSION_IOS"];
                                    /* Build import array */
                                    $l_object_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL] = $l_daoModel->parse_import_array($l_data);
                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'manufacturer' => ['title_lang' => $l_switch_stack["MANUFACTURER"]],
                                            'title'        => ['title_lang' => $l_switch_stack["DESCRIPTION"]],
                                            'serial'       => ['value' => $l_switch_stack["SERIALNUMBER"]],
                                            'firmware'     => ['value' => (($l_switch_stack["FIRMVERSION"] != '') ? $l_switch_stack["FIRMVERSION"] : $l_switch_stack["FIRMVERSION_IOS"])]
                                        ];
                                        $l_changes                                                  = $l_dao_logb->prepare_changes($l_daoModel, null, $l_category_values);
                                        if (count($l_changes) > 0)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_obj_id_switch_stack,
                                                C__OBJTYPE__SWITCH,
                                                'LC__CMDB__CATG__MODEL',
                                                serialize($l_changes)
                                            );
                                        }
                                    }
                                    $l_import_data = $l_daoModel->parse_import_array($l_data);

                                    $l_daoModel->sync($l_import_data, $l_obj_id_switch_stack, isys_import_handler_cmdb::C__CREATE);
                                } // if
                                $l_assigned_switch_stack[] = $l_obj_id_switch_stack;
                            } // foreach

                            // Create stacking
                            $l_daoStacking->create_stacking($l_objID, $l_assigned_switch_stack);
                        }
                        else
                        {
                            $l_res = $l_daoModel->get_data(null, $l_objID);
                            if ($l_res->num_rows() < 1)
                            {
                                $l_data['description'] = null;
                            }
                            else
                            {
                                $l_rowModel            = $l_res->get_row();
                                $l_data['description'] = $l_rowModel["isys_catg_model_list__description"];
                                $l_data['data_id']     = $l_rowModel['isys_catg_model_list__id'];
                            }
                            $l_data['manufacturer'] = $l_row[0]["MANUFACTURER"] = (trim($l_row[0]["MANUFACTURER"]) == '' ? $l_model_default_manufact : trim(
                                $l_row[0]["MANUFACTURER"]
                            ));
                            $l_data['title']        = $l_row[0]['DESCRIPTION'];
                            $l_data['serial']       = $l_row[0]["SERIALNUMBER"];
                            $l_data['firmware']     = ($l_row[0]["FIRMVERSION"] !== '') ? $l_row[0]["FIRMVERSION"] : $l_row[0]['FIRMVERSION_IOS'];
                            /* Build import array */
                            $l_object_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL] = $l_daoModel->parse_import_array($l_data);
                            if ($l_logb_active)
                            {
                                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                    'manufacturer' => ['title_lang' => $l_row[0]["MANUFACTURER"]],
                                    'title'        => ['title_lang' => $l_row[0]["DESCRIPTION"]],
                                    'serial'       => ['value' => $l_row[0]["SERIALNUMBER"]],
                                    'firmware'     => ['value' => (($l_row[0]["FIRMVERSION"] !== '') ? $l_row[0]["FIRMVERSION"] : $l_row[0]['FIRMVERSION_IOS'])]
                                ];
                                $l_changes                                                  = $l_dao_logb->prepare_changes($l_daoModel, $l_rowModel, $l_category_values);
                                if (count($l_changes) > 0)
                                {
                                    $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__MODEL',
                                        serialize($l_changes)
                                    );
                                }
                            }
                            $l_import_data = $l_daoModel->parse_import_array($l_data);
                            $l_entry_id    = $l_daoModel->sync(
                                $l_import_data,
                                $l_objID,
                                ((!empty($l_data['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE)
                            );

                            // Emit category signal (afterCategoryEntrySave).
                            isys_component_signalcollection::get_instance()
                                ->emit(
                                    "mod.cmdb.afterCategoryEntrySave",
                                    $l_daoModel,
                                    $l_entry_id,
                                    !!$l_entry_id,
                                    $l_objID,
                                    $l_import_data,
                                    isset($l_changes) ? $l_changes : []
                                );
                        }
                        unset($l_data);
                    }

                    if (isset($l_hw_data['cpu']))
                    {
                        verbose("Processing CPUs");
                        $l_log->info("Processing CPUs");
                        $l_data = $l_check_data = [];

                        $l_res  = $l_daoCPU->get_data(null, $l_objID);
                        $l_cpus = $l_res->num_rows();

                        if ($l_cpus > 0)
                        {
                            // Get data in i-doit
                            while ($l_rowCPU = $l_res->get_row())
                            {
                                $l_check_data[]                                    = [
                                    'data_id'        => $l_rowCPU['isys_catg_cpu_list__id'],
                                    'title'          => $l_rowCPU["isys_catg_cpu_list__title"],
                                    'manufacturer'   => $l_rowCPU['isys_catg_cpu_manufacturer__title'],
                                    'type'           => $l_rowCPU['isys_catg_cpu_type__title'],
                                    'frequency'      => $l_rowCPU['isys_catg_cpu_list__frequency'],
                                    'frequency_unit' => $l_rowCPU['isys_catg_cpu_list__isys_frequency_unit__id'],
                                    'cores'          => $l_rowCPU['isys_catg_cpu_list__cores'],
                                    'description'    => $l_rowCPU['isys_catg_cpu_list__description']
                                ];
                                $l_rowCPU_arr[$l_rowCPU['isys_catg_cpu_list__id']] = $l_rowCPU;
                            }

                            if (count($l_hw_data['cpu']) > 0)
                            {
                                foreach ($l_hw_data['cpu'] AS $l_rowCPU)
                                {
                                    // Check data from ocs with data from i-doit
                                    foreach ($l_check_data AS $l_key => $l_val)
                                    {
                                        if ($l_val['type'] == $l_rowCPU['TYPE'] && $l_val['manufacturer'] == $l_rowCPU['MANUFACTURER'] && $l_val['frequency'] == isys_convert::frequency(
                                                $l_rowCPU['SPEED'],
                                                $l_frequency_unit
                                            )
                                        )
                                        {
                                            unset($l_check_data[$l_key]);
                                            continue 2;
                                        }
                                    }

                                    // Raw array for preparing the import array
                                    $l_data = [
                                        'title'          => null,
                                        'manufacturer'   => ['title_lang' => $l_rowCPU['MANUFACTURER']],
                                        'type'           => ['title_lang' => $l_rowCPU['TYPE']],
                                        'frequency_unit' => $l_frequency_unit,
                                        'cores'          => 1,
                                        'frequency'      => $l_rowCPU['SPEED']
                                    ];

                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'manufacturer'   => ['title_lang' => $l_rowCPU['MANUFACTURER']],
                                            'frequency'      => ['value' => $l_rowCPU['SPEED']],
                                            'type'           => ['title_lang' => $l_rowCPU['TYPE']],
                                            'frequency_unit' => ['title_lang' => $l_rowCPU['UNIT']]
                                        ];

                                        $l_changes = $l_dao_logb->prepare_changes($l_daoCPU, null, $l_category_values);
                                        if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__CPU',
                                            serialize($l_changes)
                                        );
                                    }

                                    $l_import_data = $l_daoCPU->parse_import_array($l_data);
                                    $l_entry_id    = $l_daoCPU->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_daoCPU,
                                            $l_entry_id,
                                            !!$l_entry_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                }
                            }
                            // Delete entries
                            if (count($l_check_data) > 0) $this->delete_entries_from_category(
                                $l_check_data,
                                $l_daoCPU,
                                'isys_catg_cpu_list',
                                $l_objID,
                                $l_thisObjTypeID,
                                'LC__CMDB__CATG__CPU',
                                $l_logb_active
                            );
                        }
                        else
                        {
                            // Create
                            foreach ($l_hw_data['cpu'] AS $l_rowCPU)
                            {
                                $l_data = [
                                    'title'          => null,
                                    'manufacturer'   => $l_rowCPU['MANUFACTURER'],
                                    'type'           => $l_rowCPU['TYPE'],
                                    'frequency_unit' => $l_frequency_unit,
                                    'cores'          => 1,
                                    'frequency'      => $l_rowCPU['SPEED']
                                ];
                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'          => null,
                                        'frequency'      => ['value' => $l_rowCPU['SPEED']],
                                        'frequency_unit' => ['title_lang' => 'GHz'],
                                        'type'           => ['title_lang' => $l_rowCPU['TYPE']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoCPU, null, $l_category_values);
                                    $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__CPU',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_daoCPU->parse_import_array($l_data);
                                $l_entry_id    = $l_daoCPU->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoCPU,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    /**
                     * Memory Skip because the value is strange
                     */
                    if (isset($l_hw_data['memory']) && 1 == 2)
                    {
                        verbose("Processing memory");
                        $l_log->info("Processing memory");
                        $l_check_data = [];
                        $l_res        = $l_daoMemory->get_data(null, $l_objID);
                        $l_mem_amount = $l_res->num_rows();
                        if ($l_mem_amount > 0)
                        {

                            // Get data in i-doit
                            while ($l_rowMemory = $l_res->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_rowMemory['isys_catg_memory_list__id'],
                                    'title'        => $l_rowMemory["isys_memory_title__title"],
                                    'manufacturer' => $l_rowMemory['isys_memory_manufacturer__title'],
                                    'type'         => $l_rowMemory['isys_memory_type__title'],
                                    'unit'         => $l_rowMemory['isys_catg_memory_list__isys_memory_unit__id'],
                                    'capacity'     => $l_rowMemory['isys_catg_memory_list__capacity'],
                                    'description'  => $l_rowMemory['isys_catg_memory_list__description']
                                ];
                            }

                            if (count($l_hw_data) > 0)
                            {
                                foreach ($l_hw_data['memory'] AS $l_rowMemory)
                                {

                                    // Check data from ocs with data from i-doit
                                    foreach ($l_check_data AS $l_key => $l_val)
                                    {
                                        if ($l_val['capacity'] == isys_convert::memory($l_rowMemory["CAPACITY"], $l_capacityUnitMB))
                                        {

                                            unset($l_check_data[$l_key]);
                                            continue 2;
                                        }
                                    }

                                    // Raw array for preparing the import array
                                    $l_data = [
                                        'title'        => null,
                                        'manufacturer' => null,
                                        'type'         => null,
                                        'unit'         => $l_capacityUnitMB,
                                        'capacity'     => $l_rowMemory['CAPACITY']
                                    ];

                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'unit'     => ['title_lang' => 'MB'],
                                            'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                                        ];

                                        $l_changes = $l_dao_logb->prepare_changes($l_daoMemory, null, $l_category_values);
                                        if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__MEMORY',
                                            serialize($l_changes)
                                        );
                                    }

                                    $l_import_data = $l_daoMemory->parse_import_array($l_data);
                                    $l_entry_id    = $l_daoMemory->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_daoMemory,
                                            $l_entry_id,
                                            !!$l_entry_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                }
                            }
                            // Delete entries
                            if (count($l_check_data) > 0) $this->delete_entries_from_category(
                                $l_check_data,
                                $l_daoMemory,
                                'isys_catg_memory_list',
                                $l_objID,
                                $l_thisObjTypeID,
                                'LC__CMDB__CATG__MEMORY',
                                $l_logb_active
                            );

                        }
                        else
                        {
                            // Create entries
                            foreach ($l_hw_data['memory'] AS $l_rowMemory)
                            {
                                $l_data = [
                                    'title'        => null,
                                    'manufacturer' => null,
                                    'type'         => null,
                                    'unit'         => $l_capacityUnitMB,
                                    'capacity'     => $l_rowMemory["CAPACITY"]
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'unit'     => ['title_lang' => 'MB'],
                                        'capacity' => ['value' => $l_rowMemory["CAPACITY"]]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoMemory, null, $l_category_values);
                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__MEMORY',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_daoMemory->parse_import_array($l_data);
                                $l_entry_id    = $l_daoMemory->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoMemory,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    /**
                     * Network
                     */
                    if (isset($l_hw_data['net']))
                    {
                        verbose("Processing network");
                        $l_log->info("Processing network");
                        unset($l_check_ip, $l_check_net, $l_check_iface, $l_check_port, $l_already_imported_ips);
                        $l_already_imported_ports = $l_already_imported_ips = [];
                        // IP info
                        $l_query_ip = 'SELECT t1.isys_catg_ip_list__id, t2.isys_cats_net_ip_addresses_list__title, t3.* ' . 'FROM isys_catg_ip_list  AS t1 ' . 'INNER JOIN isys_cats_net_ip_addresses_list AS t2 ON t2.isys_cats_net_ip_addresses_list__id = t1.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id ' . 'INNER JOIN isys_cats_net_list AS t3 ON t3.isys_cats_net_list__isys_obj__id = t2.isys_cats_net_ip_addresses_list__isys_obj__id ' . 'WHERE t1.isys_catg_ip_list__isys_obj__id = ' . $l_daoIP->convert_sql_id(
                                $l_objID
                            );
                        $l_res_ip   = $l_daoIP->retrieve($l_query_ip);
                        while ($l_row_ip = $l_res_ip->get_row())
                        {
                            $l_check_ip[]  = [
                                'data_id'   => $l_row_ip['isys_catg_ip_list__id'],
                                'title'     => $l_row_ip['isys_cats_net_ip_addresses_list__title'],
                                'net'       => $l_row_ip['isys_cats_net_list__isys_obj__id'],
                                'net_title' => $l_row_ip['isys_cats_net_list__address'],
                                'hostnmae'  => $l_row_ip['isys_catg_ip_list__hostname'],
                                'primary'   => (bool) $l_row_ip['isys_catg_ip_list__primary']
                            ];
                            $l_check_net[] = [
                                'data_id'    => $l_row_ip['isys_cats_net_list__id'],
                                'title'      => $l_row_ip['isys_cats_net_list__address'],
                                'mask'       => $l_row_ip['isys_cats_net_list__mask'],
                                'range_from' => $l_row_ip['isys_cats_net_list__address_range_from'],
                                'range_to'   => $l_row_ip['isys_cats_net_list__address_range_to'],
                            ];
                        }

                        // Port info
                        $l_query_port = 'SELECT * FROM isys_catg_port_list ' . 'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id ' . 'LEFT JOIN isys_catg_netp_list ON isys_catg_netp_list__id = isys_catg_port_list__isys_catg_netp_list__id ' . 'LEFT JOIN isys_port_type ON isys_port_type__id = isys_catg_port_list__isys_port_type__id ' . 'LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id ' . 'WHERE isys_catg_port_list__isys_obj__id = ' . $l_daoPort->convert_sql_id(
                                $l_objID
                            );
                        $l_res_port   = $l_daoPort->retrieve($l_query_port);
                        while ($l_row_port = $l_res_port->get_row())
                        {
                            $l_check_port[] = [
                                'data_id'            => $l_row_port['isys_catg_port_list__id'],
                                'title'              => $l_row_port['isys_catg_port_list__title'],
                                'mac'                => $l_row_port['isys_catg_port_list__mac'],
                                'speed'              => $l_row_port['isys_catg_port_list__port_speed_value'],
                                'speed_type'         => $l_row_port['isys_port_speed__id'],
                                'port_type'          => $l_row_port['isys_port_type__title'],
                                'active'             => $l_row_port['isys_catg_port_list__state_enabled'],
                                'assigned_connector' => $l_row_port['isys_catg_port_list__isys_catg_connector_list__id'],
                                'description'        => $l_row_port['isys_catg_port_list__description']
                            ];
                        }

                        // Interface info
                        $l_check_iface     = [];
                        $l_existing_ifaces = [];

                        $l_query_interface = 'SELECT isys_catg_netp_list__id, isys_catg_netp_list__title FROM isys_catg_netp_list ' . 'WHERE isys_catg_netp_list__isys_obj__id = ' . $l_dao_interface->convert_sql_id(
                                $l_objID
                            );
                        $l_res_iface       = $l_dao_interface->retrieve($l_query_interface);
                        while ($l_row_iface = $l_res_iface->get_row())
                        {
                            $l_check_iface[] = [
                                'data_id' => $l_row_iface['isys_catg_netp_list__id'],
                                'title'   => $l_row_iface['isys_catg_netp_list__title']
                            ];
                        } // while

                        $l_check_ps    = [];
                        $l_existing_ps = [];
                        $l_query_ps    = 'SELECT isys_catg_pc_list__id, isys_catg_pc_list__title FROM isys_catg_pc_list
							WHERE isys_catg_pc_list__isys_obj__id = ' . $l_dao_power_consumer->convert_sql_id($l_objID);
                        $l_res_ps      = $l_dao_power_consumer->retrieve($l_query_ps);
                        while ($l_row_ps = $l_res_ps->get_row())
                        {
                            $l_check_ps[] = [
                                'data_id' => $l_row_ps['isys_catg_pc_list__id'],
                                'title'   => $l_row_ps['isys_catg_pc_list__title']
                            ];
                        } // while

                        // Copy list of existing ifaces
                        $l_existing_ifaces = $l_check_iface;
                        $l_existing_ps     = $l_check_ps;

                        $l_counter    = 0;
                        $l_net_amount = count($l_hw_data['net']) - 1;

                        foreach ($l_hw_data['net'] AS $l_hw_key => $l_row)
                        {
                            $l_speed         = null;
                            $l_speed_type_id = null;
                            if (isset($l_row['SPEED']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies')
                            {
                                preg_match('/[0-9]*\.[0-9]*|[0-9]*/', $l_row['SPEED'], $l_speed_arr);
                                $l_speed      = $l_speed_arr[0];
                                $l_speed_type = ltrim(str_replace($l_speed, '', $l_row['SPEED']));
                                $l_speed_type_lower = strtolower($l_speed_type);

                                if ($l_speed_type_lower == 'mb/s' || $l_speed_type_lower == 'm' || $l_speed_type_lower == 'mb' || $l_speed_type_lower == 'mbps')
                                {
                                    $l_speed_type_id = C__PORT_SPEED__MBIT_S;
                                }
                                elseif ($l_speed_type_lower == 'gb/s' || $l_speed_type_lower == 'g' || $l_speed_type_lower == 'gb' || $l_speed_type_lower == 'gbps')
                                {
                                    $l_speed_type_id = C__PORT_SPEED__GBIT_S;
                                }
                            }

                            $l_address      = null;
                            $l_interface_id = null;
                            $l_port_id      = null;
                            $l_ip_id        = null;
                            $l_sync_ip      = true;
                            $l_sync_port    = true;
                            $l_sync_iface   = true;

                            // Trim iface title if needed
                            if (isset($l_row['SLOT']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies')
                            {
                                $l_row['SLOT'] = trim($l_row['SLOT']);
                            } // if

                            if (count($l_existing_ifaces) > 0 && $l_hw_key === 'interfaces')
                            {
                                foreach ($l_row AS $l_interface_key => $l_interface_data)
                                {
                                    foreach ($l_existing_ifaces AS $l_key => $l_iface)
                                    {
                                        if (strcasecmp($l_iface['title'], $l_interface_data['REFERENCE']) == 0 && $l_iface['serial'] === $l_interface_data['SERIALNUMBER'])
                                        {
                                            $l_interface_id          = $l_existing_ifaces[$l_key]['data_id'];
                                            $l_row[$l_interface_key] = null;
                                            // Unset to prevent removing of iface later
                                            if (isset($l_check_iface[$l_key]))
                                            {
                                                unset($l_check_iface[$l_key]);
                                            } // if
                                            continue;
                                        } // if
                                    } // foreach
                                }
                            } // if
                            if (count($l_existing_ps) > 0 && $l_hw_key === 'powersupplies')
                            {
                                foreach ($l_row AS $l_ps_key => $l_ps_data)
                                {
                                    foreach ($l_existing_ps AS $l_key => $l_ps)
                                    {
                                        if (strcasecmp($l_ps['title'], $l_ps_data['TITLE']) == 0)
                                        {
                                            $l_ps_id          = $l_existing_ps[$l_key]['data_id'];
                                            $l_row[$l_ps_key] = null;
                                            // Unset to prevent removing of iface later
                                            if (isset($l_check_ps[$l_key]))
                                            {
                                                unset($l_check_ps[$l_key]);
                                            } // if
                                            continue;
                                        }
                                    }
                                }
                            }

                            if (count($l_check_port) > 0 && isset($l_row['TYPE']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies')
                            {
                                foreach ($l_check_port AS $l_key => $l_port)
                                {
                                    if ($l_port['title'] == $l_row['SLOT'] && strcmp($l_port['mac'], $l_row['MACADDR']) == 0)
                                    {
                                        $l_port_id = $l_check_port[$l_key]['data_id'];
                                        unset($l_check_port[$l_key]);
                                        $l_arr                                                              = new SplFixedArray(2);
                                        $l_arr[0]                                                           = $l_port['data_id'];
                                        $l_arr[1]                                                           = $l_port['description'];
                                        $l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']] = $l_arr;
                                        $l_sync_port                                                        = false;

                                        if (empty($l_port['description']) && $l_port['description'] != $l_row['DESCRIPTION'] && !isset($l_port_descriptions[$l_port_id]) && $l_port['description'] === '')
                                        {
                                            $l_port_descriptions[$l_port_id] = $l_row['DESCRIPTION'];
                                        } // if
                                        continue;
                                    }
                                }
                            }

                            if (count($l_check_ip) > 0 && isset($l_row['IPADDR']) && $l_hw_key !== 'interfaces' && $l_hw_key !== 'powersupplies')
                            {
                                foreach ($l_check_ip AS $l_key => $l_ip)
                                {
                                    if ($l_ip['title'] == $l_row['IPADDR'])
                                    {
                                        $l_ip_id = $l_check_ip[$l_key]['data_id'];
                                        unset($l_check_ip[$l_key]);
                                        unset($l_check_net[$l_key]);
                                        $l_already_imported_ips[$l_ip_id] = $l_ip['title'];
                                        $l_sync_ip                        = false;
                                        continue;
                                    }
                                }
                            }

                            // Sync Power Consumer
                            if ($l_hw_key === 'powersupplies')
                            {
                                foreach ($l_row AS $l_ps_data)
                                {
                                    if ($l_ps_data === null) continue;

                                    $l_data = [
                                        'title'        => $l_ps_data['TITLE'],
                                        'manufacturer' => $l_ps_data['MANUFACTURER']
                                    ];

                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'title'        => ['value' => $l_ps_data['TITLE']],
                                            'manufacturer' => ['title_lang' => $l_ps_data["MANUFACTURER"]]
                                        ];
                                        $l_changes                                                  = $l_dao_logb->prepare_changes(
                                            $l_dao_power_consumer,
                                            null,
                                            $l_category_values
                                        );
                                        if (count($l_changes) > 0)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                                serialize($l_changes)
                                            );
                                        }
                                    }

                                    if (!empty($l_data['manufacturer'])) $l_manufacturer = isys_import_handler::check_dialog('isys_pc_manufacturer', $l_data['manufacturer']);
                                    else $l_manufacturer = null;

                                    $l_import_data = [
                                        'data_id'    => null,
                                        'properties' => [
                                            'title'              => [
                                                'value' => $l_data['title']
                                            ],
                                            'manufacturer'       => [
                                                'value' => $l_manufacturer
                                            ],
                                            'active'             => [
                                                'value' => null
                                            ],
                                            'model'              => [
                                                'value' => null
                                            ],
                                            'volt'               => [
                                                'value' => null
                                            ],
                                            'watt'               => [
                                                'value' => null
                                            ],
                                            'ampere'             => [
                                                'value' => null
                                            ],
                                            'btu'                => [
                                                'value' => null
                                            ],
                                            'assigned_connector' => [
                                                'value' => null
                                            ],
                                            'connector_sibling'  => [
                                                'value' => null
                                            ],
                                            'description'        => [
                                                'value' => null
                                            ]
                                        ]
                                    ];

                                    $l_pc_id = $l_dao_power_consumer->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_dao_power_consumer,
                                            $l_pc_id,
                                            !!$l_pc_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );

                                    // Add it to existing ifaces
                                    if (is_numeric($l_pc_id))
                                    {
                                        $l_existing_ps[] = [
                                            'data_id' => $l_pc_id,
                                            'title'   => $l_ps_data['TITLE']
                                        ];
                                    } // if
                                }
                            }

                            // Interface sync
                            if ($l_hw_key === 'interfaces')
                            {
                                foreach ($l_row AS $l_interface_data)
                                {
                                    if ($l_interface_data === null) continue;

                                    $l_data = [
                                        'title'        => $l_interface_data['REFERENCE'],
                                        'firmware'     => $l_interface_data['FIRMWARE'],
                                        'serial'       => $l_interface_data['SERIALNUMBER'],
                                        'manufacturer' => $l_interface_data['MANUFACTURER'],
                                        'description'  => $l_interface_data['DESCRIPTION']
                                    ];

                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'title' => ['value' => $l_interface_data['REFERENCE']]
                                        ];
                                        $l_changes                                                  = $l_dao_logb->prepare_changes(
                                            $l_dao_interface,
                                            null,
                                            $l_category_values
                                        );
                                        if (count($l_changes) > 0)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                                serialize($l_changes)
                                            );
                                        }
                                    }

                                    $l_import_data = $l_dao_interface->parse_import_array($l_data);

                                    $l_interface_id = $l_dao_interface->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_dao_interface,
                                            $l_interface_id,
                                            !!$l_interface_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                    // Add it to existing ifaces
                                    if (is_numeric($l_interface_id))
                                    {
                                        $l_existing_ifaces[] = [
                                            'data_id' => $l_interface_id,
                                            'title'   => $l_interface_data['REFERENCE']
                                        ];
                                    } // if
                                }
                                continue;
                            }

                            if ($l_row['STATUS'] == 'Up')
                            {
                                $l_status = '1';
                            }
                            else $l_status = '0';

                            // Port sync
                            if ($l_sync_port && isset($l_row['TYPE']) && !isset($l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']]))
                            {
                                $l_data = [
                                    'title'      => $l_row['SLOT'],
                                    // 'Port ' . $l_hw_key,
                                    'port_type'  => $l_row['TYPE'],
                                    'speed'      => $l_speed,
                                    'speed_type' => $l_speed_type_id,
                                    'mac'        => $l_row['MACADDR'],
                                    'active'     => $l_status
                                ];

                                // Interface to port
                                if (isset($l_interface_id))
                                {
                                    $l_data['interface'] = $l_interface_id;
                                }

                                if (!$l_sync_ip)
                                {
                                    // add hostaddress
                                    $l_data['addresses'] = [
                                        $l_ip_id
                                    ];
                                }

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'       => ['value' => $l_row['SLOT']],
                                        'port_type'   => ['title_lang' => $l_row['TYPE']],
                                        'speed'       => ['value' => $l_row['SPEED']],
                                        'speed_type'  => ['title_lang' => $l_speed_type],
                                        'mac'         => ['value' => $l_row['MACADDR']],
                                        'active'      => ['value' => ($l_status) ? _L('LC__UNIVERSAL__YES') : _L('LC__UNIVERSAL__NO')],
                                        'interface'   => ['value' => $l_row['SLOT']],
                                        'description' => ['value' => $l_row['DESCRIPTION']]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoPort, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data                                                      = $l_daoPort->parse_import_array($l_data);
                                $l_port_id                                                          = $l_daoPort->sync(
                                    $l_import_data,
                                    $l_objID,
                                    isys_import_handler_cmdb::C__CREATE
                                );
                                $l_arr                                                              = new SplFixedArray(2);
                                $l_arr[0]                                                           = $l_port_id;
                                $l_arr[1]                                                           = $l_row['DESCRIPTION'];
                                $l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']] = $l_arr;

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoPort,
                                        $l_port_id,
                                        !!$l_port_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            } // if

                            // Cache Port connections this will be handled after the categories has been imported for this device
                            if (isset($l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']]))
                            {
                                if (!isset($l_port_connections[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]]) && !empty($l_row['DEVICEADDRESS']) && !empty($l_row['DEVICENAME']))
                                {
                                    $l_fixed_array                                                                              = new SplFixedArray(3);
                                    $l_fixed_array[0]                                                                           = $l_row['DEVICENAME'];
                                    $l_fixed_array[1]                                                                           = $l_row['DEVICEPORT'];
                                    $l_fixed_array[2]                                                                           = (isset($l_row['connected_to'])) ? $l_row['connected_to'] : null;
                                    $l_port_connections[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]] = $l_fixed_array;
                                } // if

                                if (!isset($l_port_descriptions[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]]) && $l_row['DESCRIPTION'] != $l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][1] && $l_row['DESCRIPTION'] != '')
                                {
                                    $l_port_descriptions[$l_already_imported_ports[$l_row['SLOT'] . '_' . $l_row['MACADDR']][0]] = $l_row['DESCRIPTION'];
                                } // if
                            } // if

                            // Ip sync
                            if ($l_sync_ip && isset($l_row['IPADDR']))
                            {

                                // ip type check ipv4, ipv6
                                if ($l_row['IPADDR'] != '' && $l_row['IPMASK'] != '')
                                {
                                    // Calculate net ip
                                    $l_mask_puffer = explode('.', $l_row['IPMASK']);
                                    if ($l_mask_puffer[3] == '0')
                                    {
                                        $l_address_puffer = explode('.', $l_row['IPADDR']);
                                        $l_subnet         = $l_address_puffer[0] . '.' . $l_address_puffer[1] . '.' . $l_address_puffer[2] . '.0';
                                    }
                                    else
                                    {
                                        $l_subnet = Ip::validate_net_ip($l_row['IPADDR'], $l_row['IPMASK'], null, true);
                                    }
                                    $l_dhcp    = false;
                                    $l_address = $l_row['IPADDR'];
                                }
                                elseif (Ip::ip2long($l_row['IPDHCP']) > 0 && $l_row['IPSUBNET'] != '0.0.0.0')
                                {
                                    /*if(($l_pos = strpos($l_row['IPDHCP'],'.0')) > 0){
										$l_subnet = substr($l_row['IPDHCP'], 0, ($l_pos));
										$l_amount = substr_count($l_subnet, '.');
										while($l_amount < 3){
											$l_amount++;
											$l_subnet .= '.0';
										}
									} else{
										$l_ip_arr = explode('.', $l_row['IPDHCP']);
										$l_subnet = $l_ip_arr[0].'.'.$l_ip_arr[1].'.'.$l_ip_arr[2].'.0';
									}*/
                                    $l_subnet  = $l_row['IPSUBNET'];
                                    $l_dhcp    = true;
                                    $l_address = $l_row['IPDHCP'];
                                } // if

                                $l_primary = (int) $l_row['PRIMARY'];

                                // Secondary Check
                                if ($l_address !== null && !in_array($l_address, $l_already_imported_ips))
                                {
                                    if (Ip::validate_ip($l_subnet))
                                    {
                                        $l_net_type    = C__CATS_NET_TYPE__IPV4;
                                        $l_cidr_suffix = Ip::calc_cidr_suffix($l_row['IPMASK']);
                                        if ($l_dhcp) $l_ip_type = C__CATP__IP__ASSIGN__DHCP;
                                        else $l_ip_type = C__CATP__IP__ASSIGN__STATIC;
                                        $l_net_id = C__OBJ__NET_GLOBAL_IPV6;
                                    }
                                    elseif (Ip::validate_ipv6($l_subnet))
                                    {
                                        $l_net_type    = C__CATS_NET_TYPE__IPV6;
                                        $l_cidr_suffix = Ip::calc_cidr_suffix_ipv6($l_row['IPMASK']);
                                        if ($l_dhcp) $l_ip_type = C__CMDB__CATG__IP__DHCPV6;
                                        else $l_ip_type = C__CMDB__CATG__IP__STATIC;
                                        $l_net_id = C__OBJ__NET_GLOBAL_IPV6;
                                    }

                                    // Check Net
                                    if (isset($l_net_arr[$l_subnet]))
                                    {
                                        $l_net_id    = $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__isys_obj__id'];
                                        $l_net_title = $l_net_arr[$l_subnet]['row_data']['isys_obj__title'];

                                        if ($l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__address_range_from'] === '')
                                        {
                                            // Update net because the range is not set
                                            $l_ip_range                                                                 = Ip::calc_ip_range(
                                                $l_subnet,
                                                $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__mask']
                                            );
                                            $l_from_long                                                                = Ip::ip2long($l_ip_range['from']);
                                            $l_to_long                                                                  = Ip::ip2long($l_ip_range['to']);
                                            $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__address_range_from'] = $l_ip_range['from'];
                                            $l_net_arr[$l_subnet]['row_data']['isys_cats_net_list__address_range_to']   = $l_ip_range['to'];
                                            $l_update                                                                   = 'UPDATE isys_cats_net_list SET isys_cats_net_list__address_range_from = ' . $l_daoCMDB->convert_sql_text(
                                                    $l_ip_range['from']
                                                ) . ',
												isys_cats_net_list__address_range_to = ' . $l_daoCMDB->convert_sql_text($l_ip_range['to']) . ',
												isys_cats_net_list__address_range_from_long = ' . $l_daoCMDB->convert_sql_text($l_from_long) . ',
												isys_cats_net_list__address_range_to_long = ' . $l_daoCMDB->convert_sql_text($l_to_long) . '
												WHERE isys_cats_net_list__isys_obj__id = ' . $l_daoCMDB->convert_sql_id($l_net_id);
                                            $l_daoCMDB->update($l_update);
                                        } // if
                                    }
                                    else
                                    {
                                        // Create net
                                        $l_gateway_arr = $l_daoCMDB->retrieve(
                                            'SELECT isys_catg_ip_list__id FROM isys_catg_ip_list ' . 'INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' . 'WHERE isys_cats_net_ip_addresses_list__title = ' . $l_daoCMDB->convert_sql_text(
                                                $l_row['IPGATEWAY']
                                            )
                                        )
                                            ->__to_array();
                                        if ($l_gateway_arr)
                                        {
                                            $l_gateway_id = $l_gateway_arr['isys_catg_ip_list__id'];
                                        }
                                        else
                                        {
                                            $l_gateway_id = null;
                                        }
                                        $l_net_id    = $l_daoCMDB->insert_new_obj(
                                            C__OBJTYPE__LAYER3_NET,
                                            false,
                                            $l_subnet . '/' . $l_cidr_suffix,
                                            null,
                                            C__RECORD_STATUS__NORMAL
                                        );
                                        $l_net_title = $l_subnet;
                                        $l_ip_range  = Ip::calc_ip_range($l_subnet, $l_row['IPMASK']);
                                        $l_daoNet_s->create(
                                            $l_net_id,
                                            C__RECORD_STATUS__NORMAL,
                                            $l_subnet,
                                            $l_net_type,
                                            $l_subnet,
                                            $l_row['IPMASK'],
                                            $l_gateway_id,
                                            false,
                                            $l_ip_range['from'],
                                            $l_ip_range['to'],
                                            null,
                                            null,
                                            '',
                                            $l_cidr_suffix
                                        );

                                        $l_net_arr[$l_subnet] = [
                                            'row_data' => [
                                                'isys_cats_net_list__isys_obj__id' => $l_net_id,
                                                'isys_obj__title'                  => $l_subnet,
                                            ]
                                        ];
                                    }

                                    $l_data = [
                                        'net_type' => $l_net_type,
                                        'primary'  => $l_primary,
                                        'active'   => '1',
                                        'net'      => $l_net_id,
                                        'hostname' => ($l_primary) ? $l_inventory['NAME'] : null
                                    ];

                                    switch ($l_net_type)
                                    {
                                        case C__CATS_NET_TYPE__IPV4:
                                            $l_data['ipv4_address']    = $l_address;
                                            $l_data['ipv4_assignment'] = $l_ip_type;
                                            break;
                                        case C__CATS_NET_TYPE__IPV6:
                                            $l_data['ipv6_address']    = $l_address;
                                            $l_data['ipv6_assignment'] = $l_ip_type;
                                            break;
                                    }

                                    // add port
                                    if ($l_port_id > 0)
                                    {
                                        $l_data['assigned_port'] = $l_port_id;
                                    }
                                    else
                                    {
                                        $l_data['assigned_port'] = null;
                                    }

                                    if ($l_logb_active)
                                    {
                                        switch ($l_net_type)
                                        {
                                            case C__CATS_NET_TYPE__IPV4:
                                                $l_ip_assignment                                            = $l_daoCMDB->get_dialog('isys_ip_assignment', $l_ip_type)
                                                    ->get_row();
                                                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                    'ipv4_address'    => ['value' => $l_row["IPADDR"]],
                                                    'ipv4_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                                    'net_type'        => ['title_lang' => 'IPv4']
                                                ];
                                                break;
                                            case C__CATS_NET_TYPE__IPV6:
                                                $l_ip_assignment                                            = $l_daoCMDB->get_dialog('isys_ipv6_assignment', $l_ip_type)
                                                    ->get_row();
                                                $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                    'ipv6_address'    => ['value' => $l_row["IPADDR"]],
                                                    'ipv6_assignment' => ['title_lang' => $l_ip_assignment['isys_ip_assignment__title']],
                                                    'net_type'        => ['title_lang' => 'IPv6'],
                                                ];
                                                break;
                                        }

                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname'] = ['value' => (($l_primary) ? $l_inventory['NAME'] : null)];
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['net']      = ['value' => $l_net_id];
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['active']   = ['value' => 1];
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES]['primary']  = ['value' => $l_primary];

                                        $l_changes = $l_dao_logb->prepare_changes($l_daoIP, null, $l_category_values);

                                        if (count($l_changes) > 0)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__CATG__IP_ADDRESS',
                                                serialize($l_changes)
                                            );
                                            unset($l_changes);
                                        }
                                    }

                                    $l_import_data                         = $l_daoIP->parse_import_array($l_data);
                                    $l_ip_data_id                          = $l_daoIP->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);
                                    $l_already_imported_ips[$l_ip_data_id] = $l_address;

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_daoIP,
                                            $l_ip_data_id,
                                            !!$l_ip_data_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                }
                            }
                            $l_counter++;
                        }
                        if (count($l_check_iface) > 0) $this->delete_entries_from_category(
                            $l_check_iface,
                            $l_dao_interface,
                            'isys_catg_netp_list',
                            $l_objID,
                            $l_thisObjTypeID,
                            'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                            $l_logb_active
                        );

                        if (count($l_check_port) > 0)
                        {
                            foreach ($l_check_port AS $l_val)
                            {
                                $l_cableConID = $l_daoCableCon->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                                $l_daoCableCon->delete_cable_connection($l_cableConID);
                                $l_daoCableCon->delete_connector($l_val["assigned_connector"]);
                                $l_daoPort->delete_entry($l_val['data_id'], 'isys_catg_port_list');

                                if ($l_logb_active) $l_mod_event_manager->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                    "-modified from OCS-",
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CATD__PORT',
                                    null
                                );
                            }
                        }

                        if (count($l_check_ip) > 0) $this->delete_entries_from_category(
                            $l_check_ip,
                            $l_daoIP,
                            'isys_catg_ip_list',
                            $l_objID,
                            $l_thisObjTypeID,
                            'LC__CATG__IP_ADDRESS',
                            $l_logb_active
                        );
                    }

                    /**
                     * Universal interfaces
                     */
                    if (isset($l_hw_data['ui']))
                    {
                        verbose("Processing UI");
                        $l_log->info("Processing UI");
                        $l_check_data = [];

                        $l_res_ui    = $l_daoUI->retrieve(
                            'SELECT isys_catg_ui_list__id, isys_catg_ui_list__title, isys_catg_ui_list__isys_catg_connector_list__id, ' . 'isys_ui_plugtype__title ' . 'FROM isys_catg_ui_list ' . 'INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id ' . 'LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id ' . 'WHERE isys_catg_ui_list__isys_obj__id = ' . $l_daoUI->convert_sql_id(
                                $l_objID
                            )
                        );
                        $l_ui_amount = $l_res_ui->num_rows();

                        if ($l_ui_amount > 0)
                        {
                            // data from i-doit
                            while ($l_row_ui = $l_res_ui->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'            => $l_row_ui['isys_catg_ui_list__id'],
                                    'title'              => $l_row_ui['isys_catg_ui_list__title'],
                                    'plug'               => $l_row_ui['isys_ui_plugtype__title'],
                                    'assigned_connector' => $l_row_ui['isys_catg_ui_list__isys_catg_connector_list__id'],
                                    'description'        => $l_row_ui['isys_catg_ui_list__description']
                                ];
                            }

                            if (count($l_hw_data['ui']) > 0)
                            {
                                foreach ($l_hw_data['ui'] AS $l_row)
                                {
                                    // Check if data already exists in i-doit
                                    foreach ($l_check_data AS $l_key => $l_value)
                                    {
                                        if ($l_value['title'] == $l_row["NAME"] && $l_value['plug'] == $l_row['TYPE'])
                                        {
                                            unset($l_check_data[$l_key]);
                                            continue 2;
                                        }
                                    }

                                    // Create new data
                                    $l_data = [
                                        'title'       => $l_row["NAME"],
                                        'plug'        => $l_row["TYPE"],
                                        'type'        => $l_conTypeTitle,
                                        'description' => $l_row['DESCRIPTION'],
                                    ];

                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'title' => ['value' => $l_row["NAME"]],
                                            'plug'  => ['title_lang' => $l_row["TYPE"]],
                                            'type'  => ['title_lang' => $l_conTypeTitle],
                                        ];

                                        $l_changes = $l_dao_logb->prepare_changes($l_daoUI, null, $l_category_values);

                                        if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                            serialize($l_changes)
                                        );
                                    }

                                    $l_import_data = $l_daoUI->parse_import_array($l_data);
                                    $l_entry_id    = $l_daoUI->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_daoUI,
                                            $l_entry_id,
                                            !!$l_entry_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                }
                            }

                            foreach ($l_check_data AS $l_val)
                            {
                                $l_cableConID = $l_daoCableCon->get_cable_connection_id_by_connector_id($l_val["assigned_connector"]);
                                $l_daoCableCon->delete_cable_connection($l_cableConID);
                                $l_daoCableCon->delete_connector($l_val["assigned_connector"]);
                                $l_daoUI->delete_entry($l_val['data_id'], 'isys_catg_ui_list');

                                if ($l_logb_active) $l_mod_event_manager->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                    "-modified from OCS-",
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                    null
                                );
                            }

                        }
                        else
                        {
                            // create
                            foreach ($l_hw_data['ui'] AS $l_row)
                            {

                                $l_data = [
                                    'title'       => $l_row["NAME"],
                                    'plug'        => $l_row["TYPE"],
                                    'type'        => $l_conTypeTitle,
                                    'description' => $l_row['DESCRIPTION'],
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title' => ['value' => $l_row["NAME"]],
                                        'plug'  => ['title_lang' => $l_row["TYPE"]],
                                        'type'  => ['title_lang' => $l_conTypeTitle],
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoUI, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__UNIVERSAL_INTERFACE',
                                        serialize($l_changes)
                                    );
                                }

                                $l_import_data = $l_daoUI->parse_import_array($l_data);
                                $l_entry_id    = $l_daoUI->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoUI->get_category_id(),
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    /**
                     * Storage
                     */
                    if (isset($l_hw_data['stor']))
                    {
                        verbose("Processing storage");
                        $l_log->info("Processing storage");
                        $l_check_data = [];

                        $l_res_stor = $l_daoStor->retrieve(
                            'SELECT isys_catg_stor_list__title, isys_stor_manufacturer__title, isys_stor_model__title, ' . 'isys_catg_stor_list__capacity, isys_catg_stor_list__id ' . 'FROM isys_catg_stor_list ' . 'LEFT JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id ' . 'LEFT JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id ' . 'WHERE isys_catg_stor_list__isys_obj__id = ' . $l_daoStor->convert_sql_id(
                                $l_objID
                            )
                        );

                        $l_stor_amount = $l_res_stor->num_rows();

                        if ($l_stor_amount > 0)
                        {
                            // Check, Delete, Create
                            while ($l_row = $l_res_stor->get_row())
                            {
                                $l_check_data[] = [
                                    'data_id'      => $l_row['isys_catg_stor_list__id'],
                                    'title'        => $l_row['isys_catg_stor_list__title'],
                                    'manufacturer' => $l_row['isys_stor_manufacturer__title'],
                                    'model'        => $l_row['isys_stor_model__title'],
                                    'serial'       => $l_row['isys_catg_stor_list__serial'],
                                    'capacity'     => $l_row['isys_catg_stor_list__capacity']
                                ];
                            }

                            if (count($l_hw_data['stor']) > 0)
                            {
                                foreach ($l_hw_data['stor'] AS $l_row)
                                {
                                    // Check if data already exists in i-doit
                                    foreach ($l_check_data AS $l_key => $l_value)
                                    {
                                        if ($l_value['title'] == $l_row["NAME"] && $l_value['serial'] == $l_row['SERIALNUMBER'] && $l_value['manufacturer'] == $l_row['MANUFACTURER'] && $l_value['model'] == $l_row['MODEL'] && $l_value['capacity'] == isys_convert::memory(
                                                $l_row['DISKSIZE'],
                                                $l_capacityUnitMB
                                            )
                                        )
                                        {
                                            unset($l_check_data[$l_key]);
                                            continue 2;
                                        }
                                    }

                                    if ($l_row["TYPE"] == null || $l_row["TYPE"] == "") $l_deviceType = $this->parseStorageType($l_row["DESCRIPTION"]);
                                    else
                                        $l_deviceType = $this->parseStorageType($l_row["TYPE"]);

                                    // Create new data
                                    $l_data = [
                                        'title'        => $l_row['NAME'],
                                        'manufacturer' => $l_row['MANUFACTURER'],
                                        'model'        => $l_row['MODEL'],
                                        'serial'       => $l_row['SERIALNUMBER'],
                                        'capacity'     => $l_row['DISKSIZE'],
                                        'unit'         => $l_capacityUnitMB,
                                        'description'  => $l_row['DESCRIPTION'],
                                        'type'         => $l_deviceType
                                    ];

                                    if ($l_logb_active)
                                    {
                                        $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                            'title'        => ['value' => $l_row["NAME"]],
                                            'capacity'     => ['value' => $l_row["DISKSIZE"]],
                                            'unit'         => ['title_lang' => 'MB'],
                                            'manufacturer' => ['title_lang' => $l_row["MANUFACTURER"]],
                                            'model'        => ['title_lang' => $l_row["MODEL"]],
                                            'serial'       => ['value' => $l_row['SERIALNUMBER']]
                                        ];

                                        $l_changes = $l_dao_logb->prepare_changes($l_daoStor, null, $l_category_values);

                                        if (count($l_changes) > 0)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__UNIVERSAL__DEVICES',
                                                serialize($l_changes)
                                            );
                                        }
                                    }

                                    $l_import_data = $l_daoStor->parse_import_array($l_data);
                                    $l_entry_id    = $l_daoStor->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                    // Emit category signal (afterCategoryEntrySave).
                                    isys_component_signalcollection::get_instance()
                                        ->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_daoStor,
                                            $l_entry_id,
                                            !!$l_entry_id,
                                            $l_objID,
                                            $l_import_data,
                                            isset($l_changes) ? $l_changes : []
                                        );
                                }
                            }

                            if (count($l_check_data) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_check_data,
                                    $l_daoStor,
                                    'isys_catg_stor_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__UNIVERSAL__DEVICES',
                                    $l_logb_active
                                );
                            } // if
                        }
                        else
                        {
                            // create
                            foreach ($l_hw_data['stor'] AS $l_row)
                            {

                                if ($l_row["TYPE"] == null || $l_row["TYPE"] == "") $l_deviceType = $this->parseStorageType($l_row["DESCRIPTION"]);
                                else
                                    $l_deviceType = $this->parseStorageType($l_row["TYPE"]);

                                // Create new data
                                $l_data = [
                                    'title'        => $l_row['NAME'],
                                    'manufacturer' => $l_row['MANUFACTURER'],
                                    'model'        => $l_row['MODEL'],
                                    'capacity'     => $l_row['DISKSIZE'],
                                    'unit'         => $l_capacityUnitMB,
                                    'description'  => $l_row['DESCRIPTION'],
                                    'type'         => $l_deviceType
                                ];

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                        'title'        => ['value' => $l_row["NAME"]],
                                        'capacity'     => ['value' => $l_row["DISKSIZE"]],
                                        'unit'         => ['title_lang' => 'MB'],
                                        'manufacturer' => ['title_lang' => $l_row["MANUFACTURER"]],
                                        'model'        => ['title_lang' => $l_row["MODEL"]]
                                    ];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoStor, null, $l_category_values);

                                    if (count($l_changes) > 0)
                                    {
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                            "-modified from OCS-",
                                            $l_objID,
                                            $l_thisObjTypeID,
                                            'LC__UNIVERSAL__DEVICES',
                                            serialize($l_changes)
                                        );
                                    } // if
                                } // if

                                $l_import_data = $l_daoStor->parse_import_array($l_data);
                                $l_entry_id    = $l_daoStor->sync($l_import_data, $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_daoStor,
                                        $l_entry_id,
                                        !!$l_entry_id,
                                        $l_objID,
                                        $l_import_data,
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }
                    }

                    if (isset($l_hw_data['application']))
                    {
                        verbose("Processing applications");
                        $l_log->info("Processing applications");
                        $l_check_data = $l_double_assigned = [];
                        $l_res_app    = $l_daoCMDB->retrieve(
                            "SELECT isys_obj__title,
							isys_catg_application_list__id,
							isys_obj__id,
							isys_obj__isys_obj_type__id,
							isys_catg_application_list__isys_catg_application_type__id,
							isys_catg_application_list__isys_catg_application_priority__id,
							isys_catg_application_list__isys_catg_version_list__id,
							isys_catg_version_list__title " . "FROM isys_catg_application_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id " . "INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " . "LEFT JOIN isys_catg_version_list ON isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id " . "WHERE isys_catg_application_list__isys_obj__id = " . $l_daoCMDB->convert_sql_id(
                                $l_objID
                            ) . " " . "AND (isys_obj_type__id = " . $l_daoCMDB->convert_sql_id(C__OBJTYPE__APPLICATION) . " OR
							isys_obj_type__id = " . $l_daoCMDB->convert_sql_id(C__OBJTYPE__OPERATING_SYSTEM) . ");"
                        );

                        while ($l_rowApp = $l_res_app->get_row())
                        {
                            if (isset($l_check_data[$l_rowApp['isys_obj__id']]))
                            {
                                $l_double_assigned[] = [
                                    'data_id' => $l_rowApp['isys_catg_application_list__id']
                                ];
                            } // if

                            $l_check_data[$l_rowApp['isys_obj__id']] = [
                                'data_id'       => $l_rowApp['isys_catg_application_list__id'],
                                'obj_type'      => $l_rowApp['isys_obj__isys_obj_type__id'],
                                'type'          => $l_rowApp['isys_catg_application_list__isys_catg_application_type__id'],
                                'priority'      => $l_rowApp['isys_catg_application_list__isys_catg_application_priority__id'],
                                'version'       => $l_rowApp['isys_catg_application_list__isys_catg_version_list__id'],
                                'version_title' => $l_rowApp['isys_catg_version_list__title']
                            ];
                        }
                        $l_swIDs = $l_already_updated = [];
                        // Assign Application
                        foreach ($l_hw_data['application'] AS $l_row)
                        {
                            $l_swID = false;

                            $l_app_objtype = ($l_row['COMMENTS'] === 'IOS') ? C__OBJTYPE__OPERATING_SYSTEM : C__OBJTYPE__APPLICATION;

                            $l_row['VERSION']  = trim($l_row['VERSION']);
                            $l_row['COMMENTS'] = trim($l_row['COMMENTS']);
                            $l_row['NAME']     = trim($l_row['NAME']);

                            $l_resSW = $l_daoCMDB->retrieve(
                                "SELECT isys_obj__id, isys_cats_application_list.* " . "FROM isys_obj " . "LEFT JOIN isys_cats_application_list ON isys_obj__id = isys_cats_application_list__isys_obj__id " . "WHERE isys_obj__title = " . $l_daoCMDB->convert_sql_text(
                                    $l_row["NAME"]
                                ) . " " . "AND isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id($l_app_objtype) . ";"
                            );

                            if ($l_resSW->num_rows() > 0)
                            {
                                // Application object exists
                                $l_app_data = $l_resSW->get_row();
                                $l_swID     = $l_app_data['isys_obj__id'];
                                if ($l_app_data['isys_cats_application_list__id'] > 0 && !in_array($l_app_data['isys_cats_application_list__id'], $l_already_updated))
                                {
                                    $l_changed_data  = [];
                                    $l_specific_data = [
                                        'data_id'          => $l_app_data['isys_cats_application_list__id'],
                                        'specification'    => $l_app_data['isys_cats_application_list__specification'],
                                        'installation'     => $l_app_data['isys_cats_application_list__isys_installation_type__id'],
                                        'registration_key' => $l_app_data['isys_cats_application_list__registration_key'],
                                        'manufacturer'     => $l_app_data['isys_cats_application_list__isys_application_manufacturer__id'],
                                        'install_path'     => $l_app_data['isys_cats_application_list__install_path'],
                                        'description'      => $l_app_data['isys_cats_application_list__description']
                                    ];

                                    if ($l_row['COMMENTS'] != '' && $l_row['COMMENTS'] != $l_app_data['isys_cats_application_list__description'])
                                    {
                                        $l_changed_data['isys_cmdb_dao_category_s_application::description'] = [
                                            'from' => $l_specific_data['description'],
                                            'to'   => $l_row['COMMENTS']
                                        ];
                                        $l_specific_data['description']                                      = $l_row['COMMENTS'];
                                    } // if

                                    // Update specific category of application
                                    if (count($l_changed_data) > 0)
                                    {
                                        $l_daoApp_s->save(
                                            $l_specific_data['data_id'],
                                            C__RECORD_STATUS__NORMAL,
                                            $l_specific_data['specification'],
                                            $l_specific_data['manufacturer'],
                                            null,
                                            $l_specific_data['description'],
                                            $l_specific_data['installation'],
                                            $l_specific_data['registration_key'],
                                            $l_specific_data['install_path']
                                        );

                                        if ($l_logb_active)
                                        {
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_swID,
                                                C__OBJTYPE__APPLICATION,
                                                'LC__CMDB__CATS__APPLICATION',
                                                serialize($l_changed_data)
                                            );
                                        } // if
                                    } // if
                                    $l_already_updated[] = $l_app_data['isys_cats_application_list__id'];
                                }
                                elseif (!$l_app_data['isys_cats_application_list__id'])
                                {

                                    $l_app_data['isys_cats_application_list__id'] = $l_daoApp_s->create(
                                        $l_swID,
                                        C__RECORD_STATUS__NORMAL,
                                        null,
                                        null,
                                        null,
                                        $l_row['COMMENTS'],
                                        null,
                                        null,
                                        null
                                    );
                                    $l_already_updated[]                          = $l_app_data['isys_cats_application_list__id'];
                                }
                            }
                            else if ($l_regApp == "0")
                            {
                                // Creat new application object
                                $l_swID = $l_daoCMDB->insert_new_obj($l_app_objtype, false, $l_row["NAME"], null, C__RECORD_STATUS__NORMAL);
                                $l_daoApp_s->create($l_swID, C__RECORD_STATUS__NORMAL, null, null, null, $l_row["COMMENTS"], null, null, null);
                                $l_mod_event_manager->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_swID, $l_app_objtype);
                            }

                            $l_version_id = null;

                            // Check, if the found application version has already been created.
                            if ($l_swID && !empty($l_row["VERSION"]))
                            {
                                // Check, if the version has been created.
                                $l_app_version_sql = 'SELECT isys_catg_version_list__id FROM isys_catg_version_list
			                        WHERE isys_catg_version_list__isys_obj__id = ' . $l_daoCMDB->convert_sql_id($l_swID) . '
			                        AND isys_catg_version_list__title LIKE ' . $l_daoCMDB->convert_sql_text($l_row["VERSION"]) . ' LIMIT 1;';

                                $l_res = $l_daoCMDB->retrieve($l_app_version_sql);

                                if (count($l_res))
                                {
                                    $l_version_id = $l_res->get_row_value('isys_catg_version_list__id');
                                }
                                else
                                {
                                    $l_version_id = isys_cmdb_dao_category_g_version::instance($g_comp_database)
                                        ->create($l_swID, C__RECORD_STATUS__NORMAL, $l_row["VERSION"]);
                                } // if
                            } // if

                            if ($l_swID && !in_array($l_swID, $l_swIDs))
                            {
                                $l_swIDs[] = $l_swID;
                                if (count($l_check_data) > 0 && isset($l_check_data[$l_swID]))
                                {
                                    if ($l_check_data[$l_swID]['obj_type'] == C__OBJTYPE__OPERATING_SYSTEM)
                                    {
                                        if ($l_check_data[$l_swID]['priority'] !== C__CATG__APPLICATION_PRIORITY__PRIMARY)
                                        {
                                            // Update operating system
                                            $l_update = 'UPDATE isys_catg_application_list SET isys_catg_application_list__isys_catg_application_priority__id = ' . $l_daoApp->convert_sql_id(
                                                    C__CATG__APPLICATION_PRIORITY__PRIMARY
                                                ) . ' WHERE isys_catg_application_list__id = ' . $l_daoApp->convert_sql_id($l_check_data[$l_swID]['data_id']);
                                            $l_daoApp->update($l_update);

                                            $l_model_data = $l_daoApp->retrieve(
                                                'SELECT isys_catg_model_list__firmware AS firmware, isys_catg_model_list__id AS id FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $l_daoApp->convert_sql_id(
                                                    $l_objID
                                                )
                                            )
                                                ->get_row();
                                            if (empty($l_model_data['firmware']))
                                            {
                                                $l_update = 'UPDATE isys_catg_model_list SET isys_catg_model_list__firmware = ' . $l_daoApp->convert_sql_text(
                                                        $l_row['NAME']
                                                    ) . ' WHERE isys_catg_model_list__id = ' . $l_daoApp->convert_sql_id($l_model_data['id']);
                                                $l_daoApp->update($l_update);
                                            } // if
                                        } // if
                                    }

                                    if ((int) $l_check_data[$l_swID]['version'] !== (int) $l_version_id)
                                    {
                                        // Update version
                                        $l_update = 'UPDATE isys_catg_application_list SET isys_catg_application_list__isys_catg_version_list__id = ' . $l_daoApp->convert_sql_id(
                                                $l_version_id
                                            ) . ' WHERE isys_catg_application_list__id = ' . $l_daoApp->convert_sql_id($l_check_data[$l_swID]['data_id']);
                                        $l_daoApp->update($l_update);

                                        if ($l_logb_active)
                                        {
                                            $l_changed_data['isys_cmdb_dao_category_g_application::application']      = [
                                                'from' => $l_row["NAME"],
                                                'to'   => $l_row["NAME"]
                                            ];
                                            $l_changed_data['isys_cmdb_dao_category_g_application::assigned_version'] = [
                                                'from' => $l_check_data[$l_sqID]['version_title'],
                                                'to'   => $l_row['VERSION']
                                            ];
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from OCS-",
                                                $l_objID,
                                                $l_thisObjTypeID,
                                                'LC__CMDB__CATG__APPLICATION',
                                                serialize($l_changed_data)
                                            );
                                        } // if
                                    } // if

                                    // Application found
                                    unset($l_check_data[$l_swID]);
                                    continue;
                                } /*else{
									$l_data = array(
										'application' => $l_swID
									);
								}*/

                                if ($l_logb_active)
                                {
                                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = ['application' => ['value' => $l_swID]];

                                    $l_changes = $l_dao_logb->prepare_changes($l_daoApp, null, $l_category_values);

                                    if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                        "-modified from OCS-",
                                        $l_objID,
                                        $l_thisObjTypeID,
                                        'LC__CMDB__CATG__APPLICATION',
                                        serialize($l_changes)
                                    );
                                }

                                //$l_daoApp->sync($l_daoApp->parse_import_array($l_data), $l_objID, isys_import_handler_cmdb::C__CREATE);

                                // First create relation
                                $l_relation_obj = $l_relation_dao->create_object(
                                    $l_relation_dao->format_relation_name(
                                        $l_inventory["NAME"],
                                        $l_row['NAME'],
                                        $l_relation_data["isys_relation_type__master"]
                                    ),
                                    C__OBJTYPE__RELATION,
                                    C__RECORD_STATUS__NORMAL
                                );

                                $l_sql = "INSERT INTO isys_catg_relation_list " . "SET " . "isys_catg_relation_list__isys_obj__id = " . $l_daoCMDB->convert_sql_id(
                                        $l_relation_obj
                                    ) . ", " . "isys_catg_relation_list__isys_obj__id__master = " . $l_daoCMDB->convert_sql_id(
                                        $l_objID
                                    ) . ", " . "isys_catg_relation_list__isys_obj__id__slave = " . $l_daoCMDB->convert_sql_id(
                                        $l_swID
                                    ) . ", " . "isys_catg_relation_list__isys_relation_type__id = '" . C__RELATION_TYPE__SOFTWARE . "', " . "isys_catg_relation_list__isys_weighting__id = '" . C__WEIGHTING__5 . "', " . "isys_catg_relation_list__status = '" . C__RECORD_STATUS__NORMAL . "' " . ";";

                                if ($l_daoApp->update($l_sql))
                                {
                                    $l_relation_id = $l_daoApp->get_last_insert_id();

                                    // Secondly insert new application entry with relation id
                                    $l_sql = "INSERT INTO isys_catg_application_list SET
										isys_catg_application_list__isys_connection__id = " . $l_daoCMDB->convert_sql_id($l_connection->add_connection($l_swID)) . ",
										isys_catg_application_list__status = '" . C__RECORD_STATUS__NORMAL . "',
										isys_catg_application_list__isys_catg_relation_list__id = " . $l_daoApp->convert_sql_id($l_relation_id) . ",
										isys_catg_application_list__isys_catg_version_list__id = " . $l_daoApp->convert_sql_id($l_version_id) . ",
										isys_catg_application_list__isys_obj__id = " . $l_daoApp->convert_sql_id($l_objID) . " ";

                                    if ($l_app_objtype == C__OBJTYPE__OPERATING_SYSTEM)
                                    {
                                        $l_sql .= ", isys_catg_application_list__isys_catg_application_type__id = " . $l_daoApp->convert_sql_id(
                                                C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM
                                            ) . ", " . "isys_catg_application_list__isys_catg_application_priority__id = " . $l_daoApp->convert_sql_id(
                                                C__CATG__APPLICATION_PRIORITY__PRIMARY
                                            ) . ";";

                                        $l_model_data = $l_daoApp->retrieve(
                                            'SELECT isys_catg_model_list__firmware AS firmware, isys_catg_model_list__id AS id FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $l_daoApp->convert_sql_id(
                                                $l_objID
                                            )
                                        )
                                            ->get_row();
                                        if (empty($l_model_data['firmware']))
                                        {
                                            $l_update = 'UPDATE isys_catg_model_list SET isys_catg_model_list__firmware = ' . $l_daoApp->convert_sql_text(
                                                    $l_row['NAME']
                                                ) . ' WHERE isys_catg_model_list__id = ' . $l_daoApp->convert_sql_id($l_model_data['id']);
                                            $l_daoApp->update($l_update);
                                        } // if
                                    } // if

                                    $l_daoApp->update($l_sql) && $l_daoApp->apply_update();
                                }
                            }
                        }

                        // Detach Applications
                        if ($l_regAppAssign == "1")
                        {
                            if (count($l_check_data) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_check_data,
                                    $l_daoApp,
                                    'isys_catg_application_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__APPLICATION',
                                    $l_logb_active
                                );
                            }
                            if (count($l_double_assigned) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_double_assigned,
                                    $l_daoApp,
                                    'isys_catg_application_list',
                                    $l_objID,
                                    $l_thisObjTypeID,
                                    'LC__CMDB__CATG__APPLICATION',
                                    $l_logb_active
                                );
                            }
                        }
                    }

                    // Import Connection between ports
                    if (count($l_port_connections) > 0)
                    {
                        verbose("Updating port connections.", true);
                        $l_log->info("Updating port connections.");
                        foreach ($l_port_connections AS $l_port_id => $l_connected_to)
                        {
                            $l_device_name = $l_connected_to[0];
                            $l_port        = $l_connected_to[1];
                            $l_mac         = $l_connected_to[2];
                            //list($l_device_name, $l_port, $l_mac) = explode('|', $l_connected_to);

                            $l_connected_obj_id = $l_daoCMDB->get_object_by_hostname_serial_mac(null, null, $l_mac, $l_device_name);
                            $l_cable_id         = null;

                            if ($l_connected_obj_id)
                            {
                                $l_main_sql = 'SELECT isys_catg_port_list__id AS port_id, isys_catg_port_list__isys_catg_connector_list__id AS con_id, isys_catg_connector_list__isys_cable_connection__id AS cable_con
								FROM isys_catg_port_list
 								INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
								WHERE ';
                                $l_sql      = $l_main_sql . ' isys_catg_port_list__isys_obj__id = ' . $l_daoPort->convert_sql_id($l_connected_obj_id);
                                if ($l_port)
                                {
                                    $l_sql .= ' AND isys_catg_port_list__title = ' . $l_daoPort->convert_sql_text($l_port);
                                } // if

                                if ($l_mac)
                                {
                                    $l_sql .= ' AND isys_catg_port_list__mac = ' . $l_daoPort->convert_sql_text($l_mac);
                                } // if
                                $l_connected_port = $l_daoPort->retrieve($l_sql)
                                    ->get_row();

                                $l_sql       = $l_main_sql . ' isys_catg_port_list__id = ' . $l_daoPort->convert_sql_id($l_port_id);
                                $l_main_port = $l_daoPort->retrieve($l_sql)
                                    ->get_row();

                                // Check if port is not assigned
                                if ($l_connected_port['cable_con'] != $l_main_port['cable_con'] || $l_connected_port['cable_con'] === null || $l_main_port['cable_con'] === null)
                                {
                                    if ($l_main_port['cable_con'] !== null)
                                    {
                                        $l_cable_id = $l_daoCableCon->get_cable_object_id_by_connection_id($l_main_port['cable_con']);
                                        $l_daoCableCon->delete_cable_connection($l_main_port['cable_con']);
                                    } // if
                                    if ($l_connected_port['cable_con'] !== null)
                                    {
                                        if (!$l_cable_id)
                                        {
                                            $l_cable_id = $l_daoCableCon->get_cable_object_id_by_connection_id($l_main_port['cable_con']);
                                        }
                                        $l_daoCableCon->delete_cable_connection($l_connected_port['cable_con']);
                                    } // if

                                    if (!$l_cable_id)
                                    {
                                        $l_cable_id = isys_cmdb_dao_cable_connection::add_cable();
                                    } // if

                                    $l_daoPort->connection_save($l_main_port['con_id'], $l_connected_port['con_id'], $l_cable_id);
                                } // if
                            } // if
                        } // foreach
                    } // if

                    if (count($l_port_descriptions) > 0)
                    {
                        verbose("Updating port descriptions.", true);
                        $l_log->info("Updating port descriptions.");
                        foreach ($l_port_descriptions AS $l_port_id => $l_description)
                        {
                            $l_update = 'UPDATE isys_catg_port_list SET isys_catg_port_list__description = ' . $l_daoCMDB->convert_sql_text(
                                    $l_description
                                ) . ' WHERE isys_catg_port_list__id = ' . $l_daoCMDB->convert_sql_id($l_port_id);
                            $l_daoCMDB->update($l_update);
                        } // foreach
                        $l_daoCMDB->apply_update();
                    } // if

                    if ($l_object_updated === true)
                    {
                        verbose($l_inventory["NAME"] . " succesfully updated\n\n");
                        $l_log->info("\"" . $l_inventory["NAME"] . "\" succesfully updated.");
                    }
                    else
                    {
                        verbose($l_inventory["NAME"] . " succesfully imported\n\n");
                        $l_log->info("\"" . $l_inventory["NAME"] . "\" succesfully imported.");
                    } // if

                    $l_log->flush_log(true, false);
                } // foreach
            } // if

            $l_daoCMDB->apply_update();
            verbose("Import successful");
            $l_log->info("Import successful");

            /**
             * Create index for imported/updated objects, based on the start time of this import
             *
             * @todo prepare a list of imported categories so that not every category is indexed again
             */
            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.afterLegacyImport', $startTime);
        }
        catch (Exception $e)
        {
            $l_daoCMDB->cancel_update();
            verbose("Import failed");
            $l_log->info("Import failed");
            throw $e;
        }

        $l_log->flush_log(true, false);

        return true;
    }

    /**
     * Parse the storage type to determine the corresponding storage type in i-doit
     *
     * @param String $p_strType The String to be parsed
     *
     * @return int The ID of the corresponding storage type, if applicable, else false
     */
    private function parseStorageType($p_strType)
    {
        $l_storageTypes = [
            "disk"                  => C__STOR_TYPE_DEVICE_HD,
            "Fixedxhard disk media" => C__STOR_TYPE_DEVICE_HD,
            "cdrom"                 => C__STOR_TYPE_DEVICE_CD_ROM,
            "CD-ROM"                => C__STOR_TYPE_DEVICE_CD_ROM,
            "CD-ROM-Laufwerk"       => C__STOR_TYPE_DEVICE_CD_ROM,
            "Diskettenlaufwerk"     => C__STOR_TYPE_DEVICE_FLOPPY
        ];

        return isset($l_storageTypes[$p_strType]) ? $l_storageTypes[$p_strType] : null;
    }

    /**
     * Builds array with all information that is neede for the import
     *
     * @param isys_component_dao_ocs $p_dao
     * @param array                  $p_hardwareids
     * @param array                  $p_categories
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function get_hardware_info($p_dao, $p_hardwareid = null, $p_add_model = false, $p_add_memory = false, $p_add_application = false, $p_add_graphic = false, $p_add_sound = false, $p_add_storage = false, $p_add_net = false, $p_add_ui = false, $p_add_drive = false, $p_add_vm = false)
    {

        $l_memory  = [];
        $l_app     = [];
        $l_graphic = [];
        $l_sound   = [];
        $l_drive   = [];
        $l_net     = [];
        $l_ui      = [];
        $l_stor    = [];

        $l_data = [
            'inventory' => $p_dao->getHardwareItem($p_hardwareid)
        ];

        $l_data['inventory']['macaddr'] = $p_dao->get_unique_mac_addresses($p_hardwareid);

        if ($p_add_model) $l_data['model'] = $p_dao->getBios($p_hardwareid)
            ->__to_array();

        if ($p_add_memory)
        {
            $l_res_ocs_memory = $p_dao->getMemory($p_hardwareid);
            while ($l_row = $l_res_ocs_memory->get_row())
            {
                $l_memory[] = $l_row;
            }
            $l_data['memory'] = $l_memory;
        }

        if ($p_add_application)
        {
            $l_res_software = $p_dao->getSoftware($p_hardwareid);
            while ($l_row = $l_res_software->get_row())
            {
                $l_app[] = $l_row;
            }
            $l_data['application'] = $l_app;
        }

        if ($p_add_graphic)
        {
            $l_res_ocs_graphic = $p_dao->getGraphicsAdapter($p_hardwareid);
            while ($l_row = $l_res_ocs_graphic->get_row())
            {
                $l_graphic[] = $l_row;
            }
            $l_data['graphic'] = $l_graphic;
        }

        if ($p_add_sound)
        {
            $l_res_ocs_sound = $p_dao->getSoundAdapter($p_hardwareid);
            while ($l_row = $l_res_ocs_sound->get_row())
            {
                $l_sound[] = $l_row;
            }
            $l_data['sound'] = $l_sound;
        }

        if ($p_add_drive)
        {
            $l_res_ocs_drive = $p_dao->getDrives($p_hardwareid);
            while ($l_row = $l_res_ocs_drive->get_row())
            {
                $l_drive[] = $l_row;
            }
            $l_data['drive'] = $l_drive;
        }

        if ($p_add_net)
        {
            $l_res_net = $p_dao->getNetworkAdapter($p_hardwareid);
            while ($l_row = $l_res_net->get_row())
            {
                $l_net[] = $l_row;
            }
            $l_data['net'] = $l_net;
        }

        if ($p_add_ui)
        {
            $l_res_ui = $p_dao->getPorts($p_hardwareid);
            while ($l_row = $l_res_ui->get_row())
            {
                $l_ui[] = $l_row;
            }
            $l_data['ui'] = $l_ui;
        }

        if ($p_add_storage)
        {
            $l_res_stor = $p_dao->getStorage($p_hardwareid);
            while ($l_row = $l_res_stor->get_row())
            {
                $l_stor[] = $l_row;
            }
            $l_data['stor'] = $l_stor;
        }

        if ($p_add_vm)
        {
            $l_res_vm = $p_dao->getVirtualMachines($p_hardwareid);
            while ($l_row = $l_res_vm->get_row())
            {
                $l_vm[] = $l_row;
            }
            $l_data['virtual_machine'] = $l_vm;
        }

        return $l_data;
    } // function

    /**
     * @var array
     */
    private $m_already_imported_snmp_devices = [];


    /**
     * @param isys_component_dao_ocs $p_dao
     * @param null                   $p_snmp_id
     * @param bool                   $p_add_memory
     * @param bool                   $p_add_storage
     * @param bool                   $p_add_net
     * @param                        $p_add_cpu
     * @param bool                   $p_add_ui
     * @param bool                   $p_add_model
     * @param bool                   $p_add_application
     * @param bool                   $p_add_graphic
     * @param bool                   $p_add_sound
     * @param bool                   $p_add_drive
     * @param bool                   $p_add_vm
     *
     * @return array
     */
    private function get_snmp_info($p_dao, $p_snmp_id = null, $p_add_memory = false, $p_add_storage = false, $p_add_net = false, $p_add_cpu, $p_add_ui = false, $p_add_model = false, $p_add_application = false, $p_add_graphic = false, $p_add_sound = false, $p_add_drive = false, $p_add_vm = false)
    {
        $l_data = [];

        if ($p_dao->does_snmp_exist())
        {
            $l_memory  = [];
            $l_app     = [];
            $l_graphic = [];
            $l_sound   = [];
            $l_drive   = [];
            $l_net     = [];
            $l_ui      = [];
            $l_stor    = [];
            $l_cpu     = [];

            /**
             * @var $p_dao isys_component_dao_ocs
             */
            $l_data = [
                'inventory' => $p_dao->getHardwareItemBySNMP($p_snmp_id)
            ];

            if(isset($this->m_already_imported_snmp_devices[trim($l_data['inventory']['NAME'])]))
            {
                return false;
            } // if

            $this->m_already_imported_snmp_devices[trim($l_data['inventory']['NAME'])] = true;
            $l_data['inventory']['macaddr'] = $p_dao->get_unique_mac_addresses($p_snmp_id, true);

            if ($p_add_cpu)
            {
                $l_res_ocs_cpu = $p_dao->getCPU($p_snmp_id, true);
                while ($l_row = $l_res_ocs_cpu->get_row())
                {
                    $l_speed        = $l_row['SPEED'];
                    $l_row['SPEED'] = preg_replace('/[^0-9.]*/', '', $l_speed);
                    $l_row['UNIT']  = preg_replace('/[0-9.]*/', '', $l_speed);
                    $l_cpu[]        = $l_row;
                }
                $l_data['cpu'] = $l_cpu;
            }

            if ($p_add_memory)
            {
                $l_res_ocs_memory = $p_dao->getMemory($p_snmp_id, true);
                while ($l_row = $l_res_ocs_memory->get_row())
                {
                    $l_memory[] = $l_row;
                }
                $l_data['memory'] = $l_memory;
            }

            if ($p_add_net)
            {
                $l_res_net     = $p_dao->getNetworkAdapter($p_snmp_id, true);
                $l_subnetmasks = [];
                $l_counter     = 0;
                $l_addresses   = [];
                $l_primary_set = false;
                while ($l_row = $l_res_net->get_row())
                {
                    if ($l_row['DEVICENAME'] !== '' && $l_row['DEVICEPORT'] !== '')
                    {
                        $l_connected_to = $p_dao->getNetworkConnectedTo($l_row['DEVICENAME'], $l_row['DEVICEPORT']);
                        if (is_array($l_connected_to))
                        {
                            $l_row += $l_connected_to;
                        } // if
                    } // if

                    if ($l_row['IPADDR'] !== '')
                    {
                        $l_addresses[$l_row['IPADDR']] = true;
                        $l_row['PRIMARY']              = ($l_primary_set === false) ? true : false;
                        $l_primary_set                 = true;
                    }
                    $l_net[$l_counter]                                                      = $l_row;
                    $l_subnetmasks[Ip::validate_net_ip($l_row['IPADDR'], $l_row['IPMASK'])] = $l_row['IPMASK'];
                    $l_counter++;
                }
                if ($l_data['inventory']['IPADDR'] != '' && !isset($l_addresses[$l_data['inventory']['IPADDR']]))
                {
                    $l_subnetmask = '255.255.255.0';
                    if (count($l_subnetmasks) > 0)
                    {
                        $l_cache_net_ip_arr    = explode('.', $l_data['inventory']['IPADDR']);
                        $l_cache_net_ip_first  = $l_cache_net_ip_arr[0] . '.' . $l_cache_net_ip_arr[1] . '.' . $l_cache_net_ip_arr[2] . '.0';
                        $l_cache_net_ip_second = $l_cache_net_ip_arr[0] . '.' . $l_cache_net_ip_arr[1] . '.0.0';
                        $l_cache_net_ip_third  = $l_cache_net_ip_arr[0] . '.0.0.0';
                        if (isset($l_subnetmasks[$l_cache_net_ip_first]))
                        {
                            $l_subnetmask = $l_subnetmasks[$l_cache_net_ip_first];
                        }
                        elseif (isset($l_subnetmasks[$l_cache_net_ip_second]))
                        {
                            $l_subnetmask = $l_subnetmasks[$l_cache_net_ip_second];
                        }
                        elseif (isset($l_subnetmasks[$l_cache_net_ip_third]))
                        {
                            $l_subnetmask = $l_subnetmasks[$l_cache_net_ip_third];
                        }
                    }

                    $l_net[] = [
                        'IPADDR'   => $l_data['inventory']['IPADDR'],
                        'IPMASK'   => $l_subnetmask,
                        'IPSUBNET' => Ip::validate_net_ip($l_data['inventory']['IPADDR'], $l_subnetmask, null, true),
                        'STATUS'   => 'Up',
                        'PRIMARY'  => ($l_primary_set === false) ? true : false
                    ];
                }

                $l_res_interface = $p_dao->getSNMPNetworkInterfaces($p_snmp_id);
                while ($l_row = $l_res_interface->get_row())
                {
                    $l_net['interfaces'][] = $l_row;
                } // while

                $l_res_ps = $p_dao->getSNMPPowerSupplies($p_snmp_id);
                while ($l_row = $l_res_ps->get_row())
                {
                    $l_net['powersupplies'][] = $l_row;
                } // while

                $l_data['net'] = $l_net;
            }

            if ($p_add_storage)
            {
                $l_res_stor = $p_dao->getStorage($p_snmp_id, true);
                while ($l_row = $l_res_stor->get_row())
                {
                    $l_stor[] = $l_row;
                }
                $l_data['stor'] = $l_stor;
            }

            if ($p_add_ui)
            {
                $l_res_ui = $p_dao->getPorts($p_snmp_id, true);
                while ($l_row = $l_res_ui->get_row())
                {
                    $l_ui[] = $l_row;
                }
                $l_data['ui'] = $l_ui;
            }

            if ($p_add_model)
            {
                $l_data['model'] = $p_dao->getBios($p_snmp_id, true)
                    ->__as_array();
            }

            if ($p_add_application)
            {
                $l_res_software = $p_dao->getSoftware($p_snmp_id, true);
                while ($l_row = $l_res_software->get_row())
                {
                    $l_app[] = $l_row;
                }
                $l_data['application'] = $l_app;
            }

            /*if ($p_add_graphic) {
                $l_res_ocs_graphic = $p_dao->getGraphicsAdapter($p_hardwareid);
                while($l_row = $l_res_ocs_graphic->get_row()){
                    $l_graphic[] = $l_row;
                }
                $l_data['graphic'] = $l_graphic;
            }*/

            /*if ($p_add_sound) {
                $l_res_ocs_sound = $p_dao->getSoundAdapter($p_hardwareid);
                while($l_row = $l_res_ocs_sound->get_row()){
                    $l_sound[] = $l_row;
                }
                $l_data['sound'] = $l_sound;
            }*/

            /*if ($p_add_drive) {
                $l_res_ocs_drive = $p_dao->getDrives($p_hardwareid);
                while($l_row = $l_res_ocs_drive->get_row()){
                    $l_drive[] = $l_row;
                }
                $l_data['drive'] = $l_drive;
            }*/

            /*if ($p_add_vm) {
                $l_res_vm = $p_dao->getVirtualMachines($p_hardwareid);
                while($l_row = $l_res_vm->get_row()){
                    $l_vm[] = $l_row;
                }
                $l_data['virtual_machine'] = $l_vm;
            }*/
        }

        return $l_data;
    }

    /**
     * Deletes entries from category
     *
     * @param array $p_arr
     * @param       $p_dao
     * @param       $p_table
     * @param       $p_obj_id
     * @param       $p_obj_type
     * @param       $p_category_title
     * @param       $p_logb_active
     *
     * @return null
     */
    private function delete_entries_from_category(array $p_arr, $p_dao, $p_table, $p_obj_id, $p_obj_type, $p_category_title, $p_logb_active)
    {

        $l_mod_event_manager = isys_event_manager::getInstance();

        if (empty($p_table)) return null;

        foreach ($p_arr AS $l_val)
        {
            $p_dao->delete_entry($l_val['data_id'], $p_table);

            if ($p_logb_active) $l_mod_event_manager->triggerCMDBEvent(
                'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                "-modified from OCS-",
                $p_obj_id,
                $p_obj_type,
                $p_category_title,
                null
            );
        }
    }

    /**
     * Sets ocs db id in member variable
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function set_ocs_db()
    {
        global $argv;

        if (!empty($_SERVER['HTTP_HOST']))
        {
            $this->m_ocs_db = isys_glob_get_param('selected_ocsdb');
        }
        else
        {
            if (($l_key = array_search('-db', $argv)))
            {
                if (isset($argv[$l_key + 1]))
                {
                    if (is_numeric($argv[$l_key + 1]))
                    {
                        $this->m_ocs_db = $argv[$l_key + 1];
                    }
                    elseif (is_string($argv[$l_key + 1]))
                    {
                        $this->m_ocs_db = $this->get_ocs_db_id_by_title($argv[$l_key + 1]);
                    }
                }
                else
                {
                    $this->usage(1);
                }
            }
            else
            {
                $this->m_ocs_db = isys_tenantsettings::get('ocs.default.db', null);
            }
        }
    }

    /**
     * Gets ocs db id by ocs db name
     *
     * @param $p_ocsdb_title
     *
     * @return int|null
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function get_ocs_db_id_by_title($p_ocsdb_title)
    {
        $l_ocs_db_id = $this->m_comp_dao_ocs->get_ocs_db_id_by_schema($p_ocsdb_title);
        if ($l_ocs_db_id)
        {
            return $l_ocs_db_id;
        }
        else
        {
            return null;
        } // if
    } // function

    /**
     * Sets component dao ocs in member variable
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function set_ocs_comp_dao()
    {
        global $g_comp_database;

        $this->m_comp_dao_ocs = new isys_component_dao_ocs($g_comp_database);
    }

    /**
     * Overriding default construct
     */
    public function __construct()
    {

    }

}