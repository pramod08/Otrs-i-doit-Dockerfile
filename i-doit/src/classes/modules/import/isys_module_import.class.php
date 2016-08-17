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
 * Import module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define('C__IMPORT__GET__IMPORT', 1);
define('C__IMPORT__GET__FINISHED_IMPORTS', 2);
define('C__IMPORT__GET__SCRIPTS', 3);
define('C__IMPORT__GET__OCS_OBJECTS', 4);
define("C__IMPORT__GET__CSV", 5);
define('C__IMPORT__GET__JDISC', 6);
define('C__IMPORT__GET__LDAP', 7);
define('C__IMPORT__GET__SHAREPOINT', 8);
define('C__IMPORT__GET__CABLING', 9);
define('C__IMPORT__GET__LOGINVENTORY', 10);
define('C__IMPORT__GET__DOWNLOAD', 11);
define("C__CMDB__GET__CSV_AJAX", 'call_csv_handler_action');

// Path to import files.
define('C__IMPORT__DIRECTORY', isys_application::instance()->app_path . "/imports/");

// Path to import files in CSV format.
define('C__IMPORT__CSV_DIRECTORY', isys_application::instance()->app_path . "/imports/");

// Path to log files.
define('C__IMPORT__LOG_DIRECTORY', isys_application::instance()->app_path . "/log/");

class isys_module_import extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    public static $m_path_to_category_map = "temp/cache_category_map.cache";

    /* Relative Path to cached and serialized category map */
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    private $m_skip_files = [
        'isys_import_handler.class.php',
        'isys_import_handler_csv.class.php',
        'isys_import_handler_cmdb.class.php',
        'isys_import_handler_inventory.class.php',
        'isys_import_handler_cabling.class.php'
    ];
    private $m_type_map = [
        "cmdb"      => "isys_export_type_xml",
        "csv"       => "isys_export_type_csv",
        "inventory" => "inventory"
    ];

    // Files to be skipped
    private $m_userrequest;

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_import::instance();
    }

    /**
     * Method for retrieving the imported objects.
     *
     * @global  isys_component_database $g_comp_database
     * @return  isys_component_dao_result
     */
    public function get_imports()
    {
        global $g_comp_database;

        $l_sql = "SELECT * FROM isys_obj " . "INNER JOIN isys_obj_type " . "ON isys_obj_type__id = isys_obj__isys_obj_type__id " . "WHERE (isys_obj__scantime != '0000-00-00 00:00:00') " . "AND (isys_obj__hostname != '');";

        $l_dao = new isys_component_dao($g_comp_database);

        return $l_dao->retrieve($l_sql);
    } // function

    public function check_status($p_hostname, $p_obj_title = null)
    {
        global $g_comp_database;

        $l_sql = "SELECT * FROM isys_obj " . "WHERE TRUE";

        if (!is_null($p_hostname))
        {
            $l_sql .= " AND (isys_obj__hostname = '" . $g_comp_database->escape_string($p_hostname) . "')";
        }

        if (!is_null($p_obj_title))
        {
            $l_sql .= " AND (isys_obj__title = '" . $g_comp_database->escape_string($p_obj_title) . "')";
        }

        $l_sql .= " LIMIT 1;";

        $l_query = $g_comp_database->query($l_sql);
        $l_row   = $g_comp_database->fetch_array($l_query);

        return $l_row;
    } // function

    /**
     * @param isys_module_request & $p_req
     *
     * @desc Initializes the module
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;
    } // function

    /**
     * Return array of zipped scripts in imports/scripts/ directory.
     *
     * @return array
     */
    public function get_scripts()
    {
        $l_scripts = [];

        $l_dirh = opendir(C__IMPORT__DIRECTORY . "/scripts/");
        while ($l_file = readdir($l_dirh))
        {
            if (strstr($l_file, ".zip"))
            {
                $l_scripts[] = $l_file;
            } // if
        } // while

        return $l_scripts;
    }

    /**
     * Delete given import
     *
     * @param string $p_filename
     */
    public function delete_import($p_filename)
    {
        if (file_exists(C__IMPORT__DIRECTORY . "/" . $p_filename))
        {
            return unlink(C__IMPORT__DIRECTORY . "/" . $p_filename);
        } // if

        return false;
    }

    /**
     * Handler for ocs db list
     */
    public function handle_ocsdb()
    {
        $l_dbID = isys_glob_get_param("dbID");

        switch ($_POST[C__GET__NAVMODE])
        {
            case C__NAVMODE__NEW:
                $this->ocs_db(null);
                $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;
                break;

            case C__NAVMODE__SAVE:
                $this->ocs_db($l_dbID);
                break;

            case C__NAVMODE__PURGE:
                $this->delete_ocsdb($_POST["id"]);
                $this->ocs_list();
                break;

            default:
                if ($l_dbID != null)
                {
                    $this->ocs_db($l_dbID);
                } // if
                else if ($_POST["id"] != null)
                {
                    $this->ocs_db($_POST["id"][0]);
                }
                else
                {
                    $this->ocs_list();
                } // if
                break;
        } // switch
    }

    /**
     * Deletes an ocs db source from db
     *
     * @param $p_ids
     */
    public function delete_ocsdb($p_ids)
    {
        global $g_comp_database;

        $l_dao = new isys_component_dao_ocs($g_comp_database);

        if (is_array($p_ids))
        {
            foreach ($p_ids as $l_id)
            {
                $l_dao->delete_ocsdb($l_id);
            } // foreach
        }
    }

    // function

    /**
     * Method which sets data for the ocs db page.
     *
     * @param   integer $p_id
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ocs_db($p_id)
    {
        global $index_includes, $g_comp_template, $g_comp_database;

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'OCS/OCSDB');
        $l_navbar     = isys_component_template_navbar::getInstance();
        $l_dao        = new isys_component_dao_ocs($g_comp_database);

        switch (isys_glob_get_param(C__GET__NAVMODE))
        {
            case C__NAVMODE__SAVE:
                $p_id = $l_dao->saveOCSDB($p_id);
                if ($p_id)
                {
                    isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));
                }
                else
                {
                    isys_notify::error(_L('LC__INFOBOX__DATA_WAS_NOT_SAVED'), ['sticky' => true]);
                } // if
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                break;
            case C__NAVMODE__NEW:
            case C__NAVMODE__EDIT:
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                    ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL);
                break;
        }

        if (isys_glob_get_param(C__GET__MAIN_MENU__NAVIGATION_ID) == C__NAVMODE__EDIT && isys_glob_get_param(C__GET__NAVMODE) === false)
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        if ($p_id)
        {
            $l_settings = $l_dao->getOCSDB($p_id);
        } // if

        if (empty($l_settings["isys_ocs_db__port"]))
        {
            $l_settings["isys_ocs_db__port"] = "3306";
        } // if

        $l_rules                                                = [];
        $l_rules["C__MODULE__IMPORT__OCS_HOST"]["p_strValue"]   = $l_settings["isys_ocs_db__host"];
        $l_rules["C__MODULE__IMPORT__OCS_PORT"]["p_strValue"]   = $l_settings["isys_ocs_db__port"];
        $l_rules["C__MODULE__IMPORT__OCS_SCHEMA"]["p_strValue"] = $l_settings["isys_ocs_db__schema"];
        $l_rules["C__MODULE__IMPORT__OCS_USER"]["p_strValue"]   = $l_settings["isys_ocs_db__user"];
        $l_rules["C__MODULE__IMPORT__OCS_PASS"]["p_strValue"]   = isys_helper_crypt::decrypt($l_settings["isys_ocs_db__pass"]);

        $g_comp_template->assign("dbID", $l_settings["isys_ocs_db__id"])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/import/ocs_db.tpl";
    }

    // function

    /**
     * Method for ocs db list
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ocs_list()
    {
        global $g_comp_database, $index_includes;

        $l_template = $this->m_userrequest->get_template();

        $l_navbar       = isys_component_template_navbar::getInstance();
        $l_edit_right   = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'OCS/OCSDB');
        $l_delete_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::DELETE, 'OCS/OCSDB');
        $l_dao          = new isys_component_dao_ocs($g_comp_database);

        $l_dbs = $l_dao->getOCSDBs();

        $l_list = new isys_component_list();

        $l_list_headers = [
            "isys_ocs_db__id"     => "ID",
            "isys_ocs_db__host"   => "Host",
            "isys_ocs_db__schema" => "Schema"
        ];

        $l_list_data = [];

        $l_count = $l_dbs->num_rows();

        if ($l_count > 0)
        {
            while ($l_row = $l_dbs->get_row())
            {
                $l_list_data[] = $l_row;
            } // while

            $l_list->set_data($l_list_data);
            $l_list->config(
                $l_list_headers,
                '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . "&what=ocsdb&dbID=[{isys_ocs_db__id}]",
                "[{isys_ocs_db__id}]"
            );

            if ($l_list->createTempTable())
            {
                $l_template->assign("objectTableList", $l_list->getTempTableHtml());
            } // if
        }
        else
        {
            $l_template->assign("objectTableList", '<div class="p10">' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>');
        }

        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active((($l_count > 0) ? $l_delete_right : false), C__NAVBAR_BUTTON__PURGE)
            ->set_active((($l_count > 0) ? $l_edit_right : false), C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        $l_template->assign("content_title", "OCS " . _L("LC__UNIVERSAL__DATABASE"))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    }

    /**
     * Handle CSV imports.
     *
     * @deprecated
     */
    public function csv_import_handler()
    {

    } // function

    /**
     * Read temp directory for CSV files
     *
     * @return array
     * @author Selcuk Kekec <skekec@synetics.de>
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_csv_files()
    {
        $l_handle = opendir(C__IMPORT__CSV_DIRECTORY);
        $l_result = [];
        while ($l_file = readdir($l_handle))
        {
            if (is_file(C__IMPORT__CSV_DIRECTORY . $l_file))
            {
                if (strrchr($l_file, '.') == '.csv')
                {
                    $l_result[$l_file]['file'] = $l_file;
                    $l_result[$l_file]['size'] = (filesize(C__IMPORT__CSV_DIRECTORY . $l_file) / 1024) . " Kilobyte";
                    $l_result[$l_file]['date'] = date('d.m.Y H:i:s', filemtime(C__IMPORT__CSV_DIRECTORY . $l_file));
                } //if
            } //if
        } //while
        return $l_result;
    } // function

    /**
     * Fetch log files.
     *
     * @global        C__IMPORT__LOG_DIRECTORY
     *
     * @param  string $p_prefix File name prefix (optional)
     * @param  string $p_suffix File extension (optional). Default: '.log'
     *
     * @return array
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_log_files($p_prefix = '', $p_suffix = '.log')
    {
        $l_handle = opendir(C__IMPORT__LOG_DIRECTORY);
        $l_result = [];
        while ($l_file = readdir($l_handle))
        {
            if (is_file(C__IMPORT__LOG_DIRECTORY . $l_file))
            {
                if (preg_match("/^" . $p_prefix . "(.+)" . $p_suffix . "$/", $l_file) === 1)
                {
                    $l_result[$l_file]['name'] = $l_file;
                    $l_result[$l_file]['date'] = date('Y-m-d H:i:s', filectime(C__IMPORT__LOG_DIRECTORY . $l_file));
                } //if
            } //if
        } //while
        closedir($l_handle);

        return $l_result;
    } // function

    /**
     * Method for ocs obects list
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ocsObjectsPage()
    {
        global $index_includes, $g_comp_template, $g_comp_database;

        $g_comp_template->assign("content_title", "OCS Inventory")
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        $l_dao          = new isys_component_dao_ocs($g_comp_database);
        $l_settings_res = $l_dao->getOCSDBs();
        $l_ocs_db_list  = [];

        $l_default_ocs_db = isys_tenantsettings::get('ocs.default.db', null);

        if ($l_settings_res->num_rows() > 0)
        {
            if ($l_dao->getOCSDB($l_default_ocs_db) == null)
            {
                $l_default_ocs_db = null;
                isys_tenantsettings::set('ocs.default.db', '');
            }

            while ($l_settings = $l_settings_res->get_row())
            {
                if ($l_default_ocs_db == null)
                {
                    $l_default_ocs_db = $l_settings['isys_ocs_db__id'];
                }

                $l_ocs_db_list[$l_settings['isys_ocs_db__id']] = $l_settings["isys_ocs_db__host"] . ' - ' . $l_settings["isys_ocs_db__schema"];
            }
        }

        $l_sql             = 'SELECT isys_obj_type__id, isys_obj_type__title, isys_obj_type_group__title FROM isys_obj_type ' . 'INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id ' . 'WHERE isys_obj_type__show_in_tree = 1 AND isys_obj_type_group__const != \'C__OBJTYPE_GROUP__CONTACT\' AND isys_obj_type__status = ' . C__RECORD_STATUS__NORMAL;
        $l_res             = $l_dao->retrieve($l_sql);
        $l_obj_type_groups = [];
        while ($l_row = $l_res->get_row())
        {
            $l_obj_type_groups[_L($l_row['isys_obj_type_group__title'])][$l_row['isys_obj_type__id']] = _L($l_row['isys_obj_type__title']);
        } // while

        foreach ($l_obj_type_groups AS $l_key => $l_obj_types)
        {
            asort($l_obj_type_groups[$l_key]);
        } // foreach

        $l_rules['templaet_objtype_arr']['p_arData']           = serialize($l_obj_type_groups);
        $l_rules['all_objtypes']['p_arData']                   = serialize($l_obj_type_groups);
        $l_rules['selected_ocsdb']['p_arData']                 = serialize($l_ocs_db_list);
        $l_rules['ocs_overwrite_hostaddress_port']['p_arData'] = serialize(get_smarty_arr_YES_NO());
        $l_rules['selected_ocsdb']['p_strSelectedID']          = $l_default_ocs_db;

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $g_comp_template->assign('js_script', 'show_ocs_objects(' . $l_default_ocs_db . ')');

        $index_includes['contentbottomcontent'] = "modules/import/ocs_objects.tpl";
    } // function

    /**
     * Handles ocs configuration
     */
    public function handle_ocsconfig()
    {
        global $g_comp_template_language_manager, $index_includes, $g_comp_database, $g_comp_template;

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'OCS/OCSCONFIG');

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_comp_dao_ocs = new isys_component_dao_ocs($g_comp_database);
        $l_res_ocs_dbs  = $l_comp_dao_ocs->getOCSDBs();

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
        {
            try
            {
                isys_tenantsettings::set('ocs.default.objtype', $_POST["C__OCS__OBJTYPE"]);
                isys_tenantsettings::set('ocs.prefix.server', $_POST["C__OCS__SERVER_PREFIX"]);
                isys_tenantsettings::set('ocs.prefix.client', $_POST["C__OCS__CLIENT_PREFIX"]);
                isys_tenantsettings::set('ocs.application', $_POST["C__OCS__APPLICATION"]);
                isys_tenantsettings::set('ocs.prefix.router', $_POST["C__OCS__ROUTER_PREFIX"]);
                isys_tenantsettings::set('ocs.prefix.switch', $_POST["C__OCS__SWITCH_PREFIX"]);
                isys_tenantsettings::set('ocs.prefix.printer', $_POST["C__OCS__PRINTER_PREFIX"]);
                isys_tenantsettings::set('ocs.application.assignment', $_POST["C__OCS__APPLICATION_ASSIGNMENT"]);
                isys_tenantsettings::set('ocs.logbook.active', $_POST["C__OCS__LOGBOOK"]);
                isys_tenantsettings::set('ocs.default.db', $_POST["C__OCS__DEFAULT_DB"]);

                isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            }
            catch (Exception $e)
            {
                isys_notify::error($e->getMessage(), ['sticky' => true]);
            } // try
        } // if

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW)
        {
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        $l_dao = new isys_cmdb_dao($g_comp_database);
        $l_res = $l_dao->get_objtype();

        $l_objTypes = [];
        while ($l_row = $l_res->get_row())
        {
            $l_objTypes[$l_row["isys_obj_type__id"]] = $g_comp_template_language_manager->get($l_row["isys_obj_type__title"]);
        } // while

        $l_ocs_dbs      = [];
        $l_first_ocs_db = '';
        while ($l_row = $l_res_ocs_dbs->get_row())
        {
            if ($l_first_ocs_db == '')
            {
                $l_first_ocs_db = $l_row['isys_ocs_db__id'];
            }

            $l_ocs_dbs[$l_row['isys_ocs_db__id']] = $l_row['isys_ocs_db__host'] . ' - ' . $l_row['isys_ocs_db__schema'];
        }

        $l_rules = [
            'C__OCS__OBJTYPE'                => [
                'p_arData'        => serialize($l_objTypes),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.default.objtype'),
                'p_strClass'      => 'input input-small'
            ],
            'C__OCS__DEFAULT_DB'             => [
                'p_arData'        => serialize($l_ocs_dbs),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.default.db', $l_first_ocs_db),
                'p_strClass'      => 'input input-small'
            ],
            'C__OCS__SERVER_PREFIX'          => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.server'),
            ],
            'C__OCS__CLIENT_PREFIX'          => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.client'),
            ],
            'C__OCS__ROUTER_PREFIX'          => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.router'),
            ],
            'C__OCS__SWITCH_PREFIX'          => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.switch'),
            ],
            'C__OCS__PRINTER_PREFIX'         => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.printer'),
            ],
            'C__OCS__APPLICATION'            => [
                'p_arData'        => serialize(get_smarty_arr_YES_NO()),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.application'),
                'p_strClass'      => 'input input-small'
            ],
            'C__OCS__APPLICATION_ASSIGNMENT' => [
                'p_arData'        => serialize(get_smarty_arr_YES_NO()),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.application.assignment'),
                'p_strClass'      => 'input input-small'
            ],
            'C__OCS__LOGBOOK'                => [
                'p_arData'        => serialize(get_smarty_arr_YES_NO()),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.logbook.active'),
                'p_strClass'      => 'input input-small'
            ]
        ];

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes['contentbottomcontent'] = "modules/import/ocs_config.tpl";
    } // function

    /**
     * Process AJAX requests.
     */
    public function processAjaxRequest()
    {
        switch ($_GET['request'])
        {
            case 'showOCSObject':
                global $g_comp_template, $g_comp_database, $g_comp_template_language_manager;

                $l_dao = new isys_component_dao_ocs($g_comp_database);

                if (isset($_GET['selected_ocsdb'])) $l_db = $_GET['selected_ocsdb'];
                else $l_db = null;

                $l_settings = $l_dao->getOCSDB($l_db);

                $l_ocsdb = isys_component_database::get_database(
                    'mysqli',
                    $l_settings['isys_ocs_db__host'],
                    $l_settings['isys_ocs_db__port'],
                    $l_settings['isys_ocs_db__user'],
                    isys_helper_crypt::decrypt($l_settings['isys_ocs_db__pass']),
                    $l_settings['isys_ocs_db__schema']
                );

                $l_dao = new isys_component_dao_ocs($l_ocsdb);

                $l_snmp = (bool) $_GET['snmp'];
                if ($l_snmp && $l_dao->does_snmp_exist())
                {
                    // Scanned by SNMP?
                    $l_hw = $l_dao->getHardwareItemBySNMP($_GET['hwID']);

                    $g_comp_template->assign('C__OCS__ID', $l_hw['ID'])
                        ->assign('C__OCS__NAME', $l_hw['NAME'])
                        ->assign('C__OCS__OS_NAME', $l_hw['OSNAME'])
                        ->assign('C__OCS__IP', $l_hw['IPADDR']);

                    $l_dao = new isys_cmdb_dao($g_comp_database);

                    $l_row = $l_dao->retrieve(
                        'SELECT isys_obj__imported, isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__hostname = ' . $l_dao->convert_sql_text($l_hw['NAME']) . ';'
                    )
                        ->get_row();

                    if ($l_row['isys_obj__isys_obj_type__id'] != null)
                    {
                        $g_comp_template->assign('objTypeID', $l_row['isys_obj__isys_obj_type__id']);
                    }
                    else
                    {
                        $g_comp_template->assign('objTypeID', isys_tenantsettings::get('ocs.default.objtype'));
                    } // if

                    $g_comp_template->assign('imported', $l_row['isys_obj__imported']);

                    $l_objTypes = $l_dao->retrieve('SELECT isys_obj_type__id, isys_obj_type__title FROM isys_obj_type');
                    while ($l_row = $l_objTypes->get_row())
                    {
                        $l_object_types[$l_row['isys_obj_type__id']] = $g_comp_template_language_manager->get($l_row['isys_obj_type__title']);
                    } // while
                    asort($l_object_types);
                    $g_comp_template->assign('object_types', $l_object_types)
                        ->assign('yes_no_selection', get_smarty_arr_YES_NO())
                        ->display('modules/import/ocs_snmp_object.tpl');
                }
                else
                {
                    $l_hw = $l_dao->getHardwareItem($_GET['hwID']);

                    $g_comp_template->assign('C__OCS__ID', $l_hw['ID'])
                        ->assign('C__OCS__NAME', $l_hw['NAME'])
                        ->assign('C__OCS__OS_NAME', $l_hw['OSNAME'])
                        ->assign('C__OCS__PROCESSOR', $l_hw['PROCESSORT'])
                        ->assign('C__OCS__CPU_SPEED', $l_hw['PROCESSORS'])
                        ->assign('C__OCS__MEMORY', $l_hw['MEMORY'])
                        ->assign('C__OCS__IP', $l_hw['IPADDR']);

                    $l_dao = new isys_cmdb_dao($g_comp_database);

                    $l_row = $l_dao->retrieve(
                        'SELECT isys_obj__imported, isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__hostname = ' . $l_dao->convert_sql_text($l_hw['NAME']) . ';'
                    )
                        ->get_row();

                    if ($l_row['isys_obj__isys_obj_type__id'] != null)
                    {
                        $g_comp_template->assign('objTypeID', $l_row['isys_obj__isys_obj_type__id']);
                    }
                    else
                    {
                        $g_comp_template->assign('objTypeID', isys_tenantsettings::get('ocs.default.objtype'));
                    } // if

                    $g_comp_template->assign('imported', $l_row['isys_obj__imported']);

                    $l_objTypes = $l_dao->retrieve('SELECT isys_obj_type__id, isys_obj_type__title FROM isys_obj_type');
                    while ($l_row = $l_objTypes->get_row())
                    {
                        $l_object_types[$l_row['isys_obj_type__id']] = $g_comp_template_language_manager->get($l_row['isys_obj_type__title']);
                    } // while

                    $g_comp_template->assign('yes_no_selection', get_smarty_arr_YES_NO())
                        ->assign('object_types', $l_object_types)
                        ->display('modules/import/ocs_object.tpl');
                }
                break;
            case 'call_csv_handler':
                isys_module_import_csv::handle_ajax_request($_GET[C__CMDB__GET__CSV_AJAX]);
                break;

            default:
                if ($_GET[C__GET__PARAM] == C__IMPORT__GET__FINISHED_IMPORTS)
                {
                    $this->imports();
                } // if
                break;
        } // switch
    } // function

    public function get_module_id()
    {
        return $_GET[C__GET__MODULE_ID];
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_dirs;

        $l_parent    = -1;
        $l_submodule = '';
        $l_root      = null;

        if ($p_system_module)
        {
            $l_parent    = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(
                C__MODULE__IMPORT . '0',
                $l_parent,
                'Import ' . _L('LC__UNIVERSAL__MODULE')
            );
        } // if

        if (!$p_system_module)
        {
            $p_tree->add_node(
                C__MODULE__IMPORT . C__IMPORT__GET__IMPORT,
                $l_root,
                _L('LC__MODULE__IMPORT__XML'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__IMPORT,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__IMPORT,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/page_white_code.png',
                $_GET[C__GET__PARAM] == C__IMPORT__GET__IMPORT,
                '',
                '',
                isys_auth_import::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT)
            );

            $p_tree->add_node(
                C__MODULE__IMPORT . C__IMPORT__GET__CSV,
                $l_root,
                _L('LC__MODULE__IMPORT__CSV'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__CSV,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__CSV,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/page_white_excel.png',
                $_GET[C__GET__PARAM] == C__IMPORT__GET__CSV,
                '',
                '',
                isys_auth_import::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT)
            );

            $p_tree->add_node(
                C__MODULE__IMPORT . '2',
                $l_root,
                _L('LC__MODULE__IMPORT__OCS'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__OCS_OBJECTS,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]
                ),
                '',
                $g_dirs['images'] . 'tree/ocs.png',
                $_GET['param'] == C__IMPORT__GET__OCS_OBJECTS,
                '',
                '',
                isys_auth_import::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS)
            );

            if (defined('C__MODULE__JDISC') && class_exists('isys_module_jdisc'))
            {
                $p_tree->add_node(
                    C__MODULE__IMPORT . C__IMPORT__GET__JDISC,
                    $l_root,
                    _L('LC__MODULE__JDISC'),
                    isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID                => C__MODULE__IMPORT,
                            C__GET__PARAM                    => C__IMPORT__GET__JDISC,
                            C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__JDISC,
                            C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                        ]
                    ),
                    '',
                    $g_dirs['images'] . 'icons/jdisc.png',
                    $_GET['param'] == C__IMPORT__GET__JDISC,
                    '',
                    '',
                    isys_auth_import::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__JDISC)
                );
            } // if

            if (defined('C__MODULE__LDAP') && defined('C__MODULE__PRO'))
            {
                $p_tree->add_node(
                    C__IMPORT__GET__LDAP,
                    $l_root,
                    _L('LC__MODULE__IMPORT__LDAP'),
                    isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID                => C__MODULE__IMPORT,
                            C__GET__PARAM                    => C__IMPORT__GET__LDAP,
                            C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__LDAP,
                            C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                        ]
                    ),
                    '',
                    $g_dirs['images'] . 'icons/silk/server_database.png',
                    $_GET['param'] == C__IMPORT__GET__LDAP,
                    '',
                    '',
                    isys_auth_import::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LDAP)
                );
            } // if

            if (defined('C__MODULE__SHAREPOINT') && defined('C__MODULE__PRO'))
            {
                $p_tree->add_node(
                    C__IMPORT__GET__SHAREPOINT,
                    $l_root,
                    _L('LC__MODULE__IMPORT__SHAREPOINT'),
                    isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID                => C__MODULE__SHAREPOINT,
                            C__GET__PARAM                    => C__IMPORT__GET__SHAREPOINT,
                            C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT,
                            C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                        ]
                    ),
                    '',
                    $g_dirs['images'] . 'tree/sharepoint.png',
                    $_GET['param'] == C__IMPORT__GET__SHAREPOINT,
                    '',
                    '',
                    isys_auth_import::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT)
                );
            } // if

            if (defined('C__MODULE__PRO'))
            {
                $p_tree->add_node(
                    C__IMPORT__GET__CABLING,
                    $l_root,
                    _L('LC__MODULE__IMPORT__CABLING'),
                    isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID                => C__MODULE__IMPORT,
                            C__GET__PARAM                    => C__IMPORT__GET__CABLING,
                            C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__CABLING,
                            C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                        ]
                    ),
                    '',
                    $g_dirs['images'] . 'icons/silk/chart_line.png',
                    $_GET['param'] == C__IMPORT__GET__CABLING,
                    '',
                    '',
                    isys_auth_import::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__CABLING)
                );
            } // if

            if (defined('C__MODULE__LOGINVENTORY') && defined('C__MODULE__PRO'))
            {

                $p_tree->add_node(
                    C__IMPORT__GET__LOGINVENTORY,
                    $l_root,
                    'LOGINventory',
                    isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID                => C__MODULE__LOGINVENTORY,
                            C__GET__PARAM                    => C__IMPORT__GET__LOGINVENTORY,
                            C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__LOGINVENTORY,
                            C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                        ]
                    ),
                    null,
                    $g_dirs['images'] . 'icons/loginventory.png',
                    $_GET['param'] == C__IMPORT__GET__LOGINVENTORY,
                    '',
                    '',
                    isys_auth_import::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LOGINVENTORY)
                );
            } // if
        }
        else
        {
            $p_tree->add_node(
                C__MODULE__IMPORT . 7,
                $l_root,
                _L('LC__MODULE__IMPORT__OCS_DBS'),
                '?moduleID=' . $this->get_module_id() . '&what=ocsdb' . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '7',
                null,
                $g_dirs['images'] . '/tree/ocs.png',
                $_GET['what'] == 'ocsdb'
            );

            $p_tree->add_node(
                C__MODULE__IMPORT . 8,
                $l_root,
                _L('LC__MODULE__IMPORT__OCS_CONFIG'),
                '?moduleID=' . $this->get_module_id() . '&what=ocsconfig' . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '8',
                null,
                $g_dirs['images'] . 'tree/ocs.png',
                ($_GET['what'] == 'ocsconfig') ? 1 : 0
            );

            if (defined('C__MODULE__JDISC') && class_exists("isys_module_jdisc"))
            {
                $l_module_jdisc = new isys_module_jdisc();
                $l_module_jdisc->build_tree($p_tree, true, $l_root);
            } //if

            if (defined('C__MODULE__SHAREPOINT'))
            {
                $p_tree->add_node(
                    C__MODULE__IMPORT . 11,
                    $l_root,
                    _L('LC__MODULE__SHAREPOINT__CONFIGURATION'),
                    '?moduleID=' . $this->get_module_id(
                    ) . '&what=sharepoint_configuration' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '11',
                    null,
                    $g_dirs['images'] . 'tree/sharepoint.png',
                    $_GET['what'] == 'sharepoint_configuration'
                );
            } //if

            if (defined('C__MODULE__LOGINVENTORY'))
            {
                $p_tree->add_node(
                    C__MODULE__IMPORT . 12,
                    $l_root,
                    'LOGINventory-Konfiguration',
                    '?moduleID=' . $this->get_module_id(
                    ) . '&what=loginventory_configuration' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '12',
                    null,
                    $g_dirs['images'] . 'icons/loginventory.png',
                    $_GET['what'] == 'loginventory_configuration'
                );

                $p_tree->add_node(
                    C__MODULE__IMPORT . 13,
                    $l_root,
                    'LOGINventory-Datenbanken',
                    '?moduleID=' . $this->get_module_id(
                    ) . '&what=loginventory_databases' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '13',
                    null,
                    $g_dirs['images'] . '/icons/loginventory.png',
                    $_GET['what'] == 'loginventory_databases'
                );
            } // if
        } // if
    }

    //function

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @return  bool    true
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_url_exploded        = explode('?', $_SERVER['HTTP_REFERER']);
        $l_url_parameters      = $l_url_exploded[1];
        $l_parameters_exploded = explode('&', $l_url_parameters);

        $l_params = array_pop(
            array_map(
                function ($p_arg)
                {
                    $l_return = [];
                    foreach ($p_arg AS $l_content)
                    {
                        list($l_key, $l_value) = explode('=', $l_content);
                        $l_return[$l_key] = $l_value;
                    }

                    return $l_return;
                },
                [$l_parameters_exploded]
            )
        );

        $p_text[] = _L('LC__MODULE__IMPORT') . ' ' . _L('LC__UNIVERSAL__MODULE');

        if (isset($l_params[C__GET__PARAM]))
        {
            switch ($l_params[C__GET__PARAM])
            {
                case C__IMPORT__GET__OCS_OBJECTS:
                    $p_text[] = _L('LC__MODULE__IMPORT__OCS');
                    break;
                case C__IMPORT__GET__JDISC:
                    $p_text[] = _L('LC__MODULE__JDISC');
                    break;
                case C__IMPORT__GET__LDAP:
                    $p_text[] = _L('LC__MODULE__IMPORT__LDAP');
                    break;
                case C__IMPORT__GET__CABLING:
                    $p_text[] = _L('LC__CMDB__CATG__CABLING');
                    break;
                case C__IMPORT__GET__LOGINVENTORY:
                    $p_text[] = _L('LC__AUTH_GUI__LOGINVENTORY_CONDITION');
                    break;
                case C__IMPORT__GET__SHAREPOINT:
                    $p_text[] = _L('LC__MODULE__SHAREPOINT');
                    break;
                case C__IMPORT__GET__IMPORT:
                default:
                    $p_text[] = _L('LC__UNIVERSAL__FILE_IMPORT');
                    break;
            } // switch
        } // if

        $p_link = $l_url_parameters;

        return true;
    } //function

    /**
     * Starts module process
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call") && !isys_glob_get_param('mydoitAction'))
        {
            $this->processAjaxRequest();
            die;
        } // if

        $l_gets = $this->m_userrequest->get_gets();

        global $index_includes, $g_comp_template, $g_absdir;

        if (!isset($_GET[C__GET__PARAM]) && !isset($_GET['what']))
        {
            if (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__IMPORT;
            }
            elseif (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__OCS_OBJECTS;
            }
            elseif (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__JDISC)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__JDISC;
            }
            elseif (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LDAP)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__LDAP;
            }
            elseif (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__CABLING)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__CABLING;
            }
            elseif (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__OCS_OBJECTS;
            }
            elseif (isys_auth_import::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT)
            )
            {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__SHAREPOINT;
            } // if
        } // if

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $this->build_system_menu();
        } // if

        try
        {
            if ($_POST["delete_import"])
            {
                header('Content-Type: application/json');

                if ($this->delete_import($_POST["delete_import"]))
                {
                    $l_success = true;
                    $l_message = _L('LC__MODULE__IMPORT__FILE_DELETION_SUCCEEDED', [$_POST['delete_import']]);
                }
                else
                {
                    $l_success = false;
                    $l_message = _L('LC__MODULE__IMPORT__FILE_DELETION_FAILED', [$_POST['delete_import']]);
                } // if

                $l_arr = [
                    'success' => $l_success,
                    'message' => $l_message
                ];

                echo isys_format_json::encode($l_arr);
                die;
            } // if

            if (isset($l_gets[C__GET__FILE_MANAGER]))
            {
                $this->handle_file_manager();
            } // if

            switch ($_GET[C__GET__PARAM])
            {
                case C__IMPORT__GET__IMPORT:
                    isys_auth_import::instance()
                        ->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT);
                    $g_comp_template->assign("encType", "multipart/form-data");
                    $this->import_new();
                    $this->imports();
                    $g_comp_template->assign("inventory_import", class_exists('isys_import_handler_inventory'))
                        ->assign("import_files", $this->get_files());
                    break;

                case C__IMPORT__GET__SCRIPTS:
                    $g_comp_template->assign("import_path", str_replace($g_absdir . "/", "", C__IMPORT__DIRECTORY));
                    $g_comp_template->assign("scripts", $this->get_scripts());
                    break;

                case C__IMPORT__GET__OCS_OBJECTS:
                    isys_auth_import::instance()
                        ->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS);
                    $this->ocsObjectsPage();
                    break;

                case C__IMPORT__GET__CSV:
                    $this->process_csv_import_index();
                    break;

                case C__IMPORT__GET__FINISHED_IMPORTS:
                    $this->imports();
                    break;

                case C__IMPORT__GET__LDAP:
                    isys_auth_import::instance()
                        ->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LDAP);

                    return $this->ldap_import_page();
                    break;
                case C__IMPORT__GET__CABLING:
                    isys_auth_import::instance()
                        ->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__CABLING);
                    $this->cabling_import_page();
                    break;

                case C__IMPORT__GET__DOWNLOAD:

                    if (isset($_GET['file']))
                    {
                        switch ($_GET['file'])
                        {
                            case 'hi':
                                global $g_absdir;
                                $l_filemanager = new isys_component_filemanager();
                                $l_filemanager->send($g_absdir . DS . 'imports' . DS . 'scripts' . DS . 'inventory.zip');
                                break;
                        }
                    }

                    break;
                default:
                    ;
                    break;
            } // switch

            if ($_GET[C__GET__PARAM] == C__IMPORT__GET__OCS_OBJECTS || $_GET[C__GET__PARAM] == C__IMPORT__GET__CSV)
            {
                return null;
            } // if

            if (isset($l_gets['what']))
            {
                if ($l_gets['what'] === 'ocsconfig')
                {
                    isys_auth_system::instance()
                        ->check(isys_auth::VIEW, 'OCS/OCSCONFIG');
                    $this->handle_ocsconfig();
                }
                else if ($l_gets['what'] === 'ocsdb')
                {
                    isys_auth_system::instance()
                        ->check(isys_auth::VIEW, 'OCS/OCSDB');
                    $this->handle_ocsdb();
                }
                else if (defined('C__MODULE__JDISC') && class_exists("isys_module_jdisc"))
                {
                    $l_jdisc = new isys_module_jdisc();
                    $l_jdisc->init($this->m_userrequest);
                    $l_jdisc->start();
                }
                else if (defined('C__MODULE__SHAREPOINT') && $l_gets['what'] === 'sharepoint_configuration')
                {
                    $l_jdisc = new isys_module_sharepoint();
                    $l_jdisc->init($this->m_userrequest);
                    $l_jdisc->start();
                }
                else if (defined('C__MODULE__LOGINVENTORY') && ($l_gets['what'] === 'loginventory_databases' || $l_gets['what'] === 'loginventory_configuration'))
                {
                    $l_loginvent = new isys_module_loginventory();
                    $l_loginvent->init($this->m_userrequest);
                    $l_loginvent->start();
                }
                // if
            } // if
            else
            {
                if (class_exists("isys_module_jdisc"))
                {
                    if ($_GET['param'] == C__IMPORT__GET__JDISC)
                    {
                        $l_jdisc = new isys_module_jdisc();
                        $l_jdisc->init($this->m_userrequest);

                        return $l_jdisc->start();
                    }
                }

                $index_includes['contentbottomcontent'] = "modules/import/import_main.tpl";
                $index_includes['contenttop']           = false;
            } // if
        }
        catch (isys_exception_general $e)
        {
            //$this->build_system_menu();
            throw $e;

        }
        catch (isys_exception_auth $e)
        {

            $g_comp_template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";

        }
    }

    /**
     * Active directry computer import
     *
     * @return null
     */
    public function ldap_import_page()
    {

        global $index_includes, $g_comp_template, $g_dirs, $g_comp_database;

        $l_rules    = [];
        $l_objtypes = [];

        if (defined('C__MODULE__LDAP'))
        {

            $l_ldap = new isys_ldap_dao($g_comp_database);

            $l_res_ldap_serv  = $l_ldap->get_data();
            $l_ldap_ad_exists = false;

            while ($l_row = $l_res_ldap_serv->get_row())
            {
                if ($l_row['isys_ldap_directory__const'] == 'C__LDAP__AD')
                {
                    $l_ldap_ad_exists                     = true;
                    $l_ldap_serv[$l_row['isys_ldap__id']] = $l_row['isys_ldap__hostname'];
                    if ($l_row['isys_ldap__active'] > 0) $l_ldap_serv_selected = $l_row['isys_ldap__id'];
                }
            } // while

            if (!$l_ldap_ad_exists)
            {
                $g_comp_template->assign('error_message', 'No Active directory server defined.');
            }
            else
            {
                // rules
                $l_rules['C__LDAP_IMPORT__LDAP_SERVERS']['p_arData']        = serialize($l_ldap_serv);
                $l_rules['C__LDAP_IMPORT__LDAP_SERVERS']['p_strSelectedID'] = $l_ldap_serv_selected;
                $l_rules['C__LDAP_IMPORT__OBJECTTYPE']['p_arData']          = serialize($l_objtypes);

                $g_comp_template->activate_editmode();
                $g_comp_template->assign("information_text", _L(''));
                $g_comp_template->assign("ldap_is_installed", true);
                $g_comp_template->assign("content_title", "LDAP Objekt Import");
                $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
            }
        }
        else
        {
            $g_comp_template->assign('error_message', 'LDAP Module is not installed.');
        }

        $index_includes['contentbottomcontent'] = $g_dirs['class'] . 'modules' . DS . 'pro' . DS . 'templates' . DS . "modules/import/ldap_import.tpl";

        return null;
    } // function

    public function cabling_import_page()
    {
        global $index_includes, $g_comp_template, $g_comp_database, $g_dirs;

        $l_dao = isys_cmdb_dao::instance($g_comp_database);
        $l_log = isys_factory_log::get_instance('import_cabling')
            ->set_destruct_flush(isys_settings::get('logging.cmdb.import', false));

        $l_typefilter           = $l_dao->get_object_types_by_category(C__CATG__CABLING, 'g', false, false);
        $l_typefilter_as_string = $l_dao->get_object_types_by_category(C__CATG__CABLING, 'g', true, false);
        $l_key                  = array_search('C__OBJTYPE__CABLE', $l_typefilter_as_string);
        unset($l_typefilter_as_string[$l_key]);

        $l_dialog_dao     = isys_factory_cmdb_dialog_dao::get_instance($g_comp_database, 'isys_connection_type');
        $l_dialog_data    = $l_dialog_dao->get_data(null, 'RJ-45');
        $l_dialog_data_id = $l_dialog_data['isys_connection_type__id'];

        $g_comp_template->activate_editmode();
        $g_comp_template->assign("content_title", _L('LC__CMDB__CATG__CABLING'))
            ->assign("encType", "multipart/form-data")
            ->assign("lang_all_connectors", _L('LC__MODULE__IMPORT__CABLING__ALL_CONNECTORS'))
            ->assign('img_dir', $g_dirs['images'])
            ->assign("ajax_link", '?ajax=1&call=cabling_import&func=')
            ->assign("typefilter_as_string", implode(';', $l_typefilter_as_string));

        $l_objtype_group_arr = [];
        $l_objtypes          = $l_dao->get_obj_type_by_catg([C__CATG__CABLING]);

        while ($l_row = $l_objtypes->get_row())
        {
            if (!array_key_exists($l_row['isys_obj_type__isys_obj_type_group__id'], $l_objtype_group_arr))
            {
                $l_objtype_group_arr[$l_row['isys_obj_type__isys_obj_type_group__id']] = $l_dao->objgroup_get_by_id($l_row['isys_obj_type__isys_obj_type_group__id'])
                    ->get_row();
            }

            $l_arr_objtypes[_L(
                $l_objtype_group_arr[$l_row['isys_obj_type__isys_obj_type_group__id']]['isys_obj_type_group__title']
            )][$l_row['isys_obj_type__id']] = $l_row['isys_obj_type__title'];
        }

        $l_rules['C__MODULE__IMPORT__CABLING__OBJTYPE']['p_arData']             = serialize($l_arr_objtypes);
        $l_rules['C__MODULE__IMPORT__CABLING__OBJTYPE']['p_strSelectedID']      = C__OBJTYPE__PATCH_PANEL;
        $l_rules['C__MODULE__IMPORT__CABLING__CABLE_TYPE']['p_strSelectedID']   = $l_dialog_data_id;
        $l_rules['C__MODULE__IMPORT__CABLING__CABLING_TYPE']['p_strSelectedID'] = C__CATG__CONNECTOR;

        $l_rules['C__MODULE__IMPORT__CABLING__CABLING_TYPE']['p_arData'] = serialize(
            [
                C__CATG__CONNECTOR            => _L('LC__CMDB__CATG__CONNECTORS'),
                C__CMDB__SUBCAT__NETWORK_PORT => _L('LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS'),
                C__CATG__CONTROLLER_FC_PORT   => _L('LC__CMDB__CATS__CHASSIS_CABLING__FC_PORTS'),
                C__CATG__UNIVERSAL_INTERFACE  => _L('LC__CMDB__CATG__UNIVERSAL_INTERFACE')
            ]
        );

        $l_default_arr = [
            [
                _L('LC_UNIVERSAL__OBJECT'),
                _L('LC__CATG__CONNECTOR__OUTPUT'),
                _L('LC__CMDB__OBJTYPE__CABLE'),
                _L('LC__CATG__CONNECTOR__INPUT'),
                _L('LC_UNIVERSAL__OBJECT'),
                _L('LC__CATG__CONNECTOR__OUTPUT')
            ]
        ];

        $l_show_default = true;

        if (isset($_FILES['import_file']) || isset($_POST['import_submitter']))
        {

            $l_create_patch_panels = false;

            $l_rules['C__MODULE__IMPORT__CABLING__CABLING_TYPE']['p_strSelectedID']              = $_POST['C__MODULE__IMPORT__CABLING__CABLING_TYPE'];
            $l_rules['C__MODULE__IMPORT__CABLING__CABLE_TYPE']['p_strSelectedID']                = $_POST['C__MODULE__IMPORT__CABLING__CABLE_TYPE'];
            $l_rules['C__MODULE__IMPORT__CABLING__OBJTYPE']['p_strSelectedID']                   = $_POST['C__MODULE__IMPORT__CABLING__OBJTYPE'];
            $l_rules['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM']['p_strSelectedID'] = $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN'];
            $l_rules['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE']['p_strSelectedID']    = $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'];

            if (!empty($_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN']) && !empty($_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE']))
            {
                $g_comp_template->assign('advanced_options', true);
            } // if

            if (isset($_POST['C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST']))
            {
                $l_rules['C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST']['p_bChecked'] = true;
                $l_create_patch_panels                                                   = true;
            } // if

            if ($_POST['import_submitter'] == 'load_csv')
            {
                // Reads file and generates the output
                if (!empty($_FILES['import_file']['name']) && strrchr($_FILES['import_file']['name'], ".") == '.csv')
                {
                    if (move_uploaded_file($_FILES['import_file']['tmp_name'], C__IMPORT__CSV_DIRECTORY . $_FILES['import_file']['name']))
                    {
                        chmod(C__IMPORT__CSV_DIRECTORY . $_FILES['import_file']['name'], 0777);
                        $l_import = new isys_import_handler_cabling($l_log, C__IMPORT__CSV_DIRECTORY . $_FILES['import_file']['name']);
                        $l_list   = $l_import->load_list()
                            ->set_options(
                                $_POST['C__MODULE__IMPORT__CABLING__CABLING_TYPE'],
                                $_POST['C__MODULE__IMPORT__CABLING__CABLE_TYPE'],
                                $l_create_patch_panels,
                                $_POST['C__MODULE__IMPORT__CABLING__OBJTYPE'],
                                $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN'],
                                $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'],
                                $l_typefilter
                            )
                            ->render_list()
                            ->get_output();

                        $g_comp_template->assign('content', $l_list);
                        $l_show_default = false;
                    }
                }
            }
            elseif ($_POST['import_submitter'] == 'import')
            {
                // Imports the data
                $l_import = new isys_import_handler_cabling($l_log, null, $_POST['csv_row']);
                $l_import->set_options(
                    $_POST['C__MODULE__IMPORT__CABLING__CABLING_TYPE'],
                    $_POST['C__MODULE__IMPORT__CABLING__CABLE_TYPE'],
                    $l_create_patch_panels,
                    $_POST['C__MODULE__IMPORT__CABLING__OBJTYPE'],
                    $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN'],
                    $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'],
                    $l_typefilter
                );

                $l_imported = $l_import->prepare()
                    ->import();
                $l_import_log = $l_import->get_import_log();

                $l_list = $l_import->render_list()
                    ->get_output();

                if($l_imported)
                {
                    isys_notify::success(_L('LC__MODULE__IMPORT__CABLING__SUCCEEDED'));
                }
                else
                {
                    isys_notify::error(_L('LC__MODULE__IMPORT__CABLING__FAILED'));
                } // if

                $l_dlgets                       = $this->m_userrequest->get_gets();
                $l_dlgets[C__GET__FILE_MANAGER] = "get";
                $l_dlgets[C__GET__FILE_NAME]    = 'cabling_import.csv';
                $l_dlgets[C__GET__MODULE_ID]    = C__MODULE__IMPORT;
                $l_download_link                = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

                $g_comp_template->assign('content', $l_list)
                    ->assign('img_dir', $g_dirs['images'])
                    ->assign('import_log', ltrim($l_import_log))
                    ->assign('cabling_import_result', $l_imported)
                    ->assign('import_message_success', _L('LC__MODULE__IMPORT__CABLING__SUCCEEDED'))
                    ->assign('import_message_fail', _L('LC__MODULE__IMPORT__CABLING__FAILED'))
                    ->assign('download_link', $l_download_link);
                $l_show_default = false;
            }
        }

        if ($l_show_default)
        {
            $l_import = new isys_import_handler_cabling($l_log, null, $l_default_arr);

            $l_list = $l_import->load_list()
                ->set_options(C__CATG__CONNECTOR, $l_dialog_data_id, false, C__OBJTYPE__PATCH_PANEL, null, null, $l_typefilter)
                ->render_list()
                ->get_output();

            $g_comp_template->assign('content', $l_list);
        }

        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes['contentbottomcontent'] = "modules/import/cabling_import.tpl";
    } // function

    /**
     * This method will process the CSV import specific actions.
     *
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_csv_import_index()
    {
        $l_tpl   = isys_application::instance()->template;
        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_gets  = isys_module_request::get_instance()
            ->get_gets();

        if (isset($_FILES['import_file']) && is_array($_FILES['import_file']) && $_FILES['import_file']['error'] !== UPLOAD_ERR_NO_FILE)
        {
            $this->import_new();
        }
        else if (isset($l_posts['file']) || isset($l_gets['file']))
        {
            $this->process_csv_import_assignment((isset($l_posts['file']) ? $l_posts['file'] : $l_gets['file']), $l_gets['profile']);

            return;
        }
        else if (isset($l_posts['csv_filename']) && isset($l_posts['csv_separator']))
        {
            header('Content-Type: application/json; charset=utf-8');

            echo isys_format_json::encode($this->process_csv_file($l_posts['csv_filename'], $l_posts['csv_separator'], $l_posts['object_type']));
            die;
        } // if

        // Display the list of files.
        $l_tpl->assign("encType", "multipart/form-data")
            ->assign("import_files", $this->get_files())
            ->assign("import_filter", $this->get_import_filter())
            ->assign(
                "form_action_url",
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__CSV,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__CSV,
                        C__GET__MAIN_MENU__NAVIGATION_ID => 2
                    ]
                )
            )
            ->include_template('contentbottomcontent', 'modules/import/import_csv.tpl');
    }

    /**
     * This method will be used to display the assignment page, after you chose a CSV file to import.
     *
     * @param   string  $p_file
     * @param   integer $p_profile
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_csv_import_assignment($p_file, $p_profile = null)
    {
        $l_tpl = isys_application::instance()->template;

        // A file has been selected. Display the matching options!
        if (file_exists(BASE_DIR . self::$m_path_to_category_map))
        {
            // @todo  Check if this is necessary.
            unlink(BASE_DIR . self::$m_path_to_category_map);
        } // if

        $l_rules = [
            'object_type'     => ['p_arData' => isys_module_import_csv::get_objecttypes()],
            'csv_filename'    => ['p_strValue' => $p_file],
            'identificator[]' => ['p_arData' => isys_module_import_csv::get_update_identificators()]
        ];

        $l_tpl->activate_editmode()
            ->assign('csv_filename', $p_file)
            ->assign('selected_profile', $p_profile)
            ->assign('log_icons', isys_format_json::encode(\idoit\Component\Logger::getLevelIcons()))
            ->assign('log_colors', isys_format_json::encode(\idoit\Component\Logger::getLevelColors()))
            ->assign('log_levels', isys_format_json::encode(\idoit\Component\Logger::getLevelNames()))
            ->assign(
                'ajax_url_csvprofiles',
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID      => C__MODULE__IMPORT,
                        C__GET__PARAM          => C__IMPORT__GET__CSV,
                        C__GET__AJAX           => 1,
                        'request'              => 'call_csv_handler',
                        C__CMDB__GET__CSV_AJAX => 'load_profiles'
                    ]
                )
            )
            ->assign(
                'url_ajax_import',
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID      => C__MODULE__IMPORT,
                        C__GET__PARAM          => C__IMPORT__GET__CSV,
                        C__GET__AJAX           => 1,
                        'request'              => 'call_csv_handler',
                        C__CMDB__GET__CSV_AJAX => 'import'
                    ]
                )
            )
            ->assign(
                'csvmapping_ajax_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX      => 1,
                        C__GET__AJAX_CALL => 'csv_import',
                    ]
                )
            )
            ->assign(
                'multivalue_modes',
                [
                    'untouched' => isys_module_import_csv::CL__MULTIVALUE_MODE__UNTOUCHED,
                    'add'       => isys_module_import_csv::CL__MULTIVALUE_MODE__ADD,
                    'overwrite' => isys_module_import_csv::CL__MULTIVALUE_MODE__OVERWRITE,
                ]
            )
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'modules/import/csv_import.tpl');
    } // function

    /**
     * Method for simply processing the CSV file for the frontend to start the matching.
     *
     * @param   string $p_csv_filename
     * @param   string $p_csv_separator
     * @param   mixed  $p_obj_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_csv_file($p_csv_filename, $p_csv_separator, $p_obj_type = false)
    {
        try
        {
            if ($p_obj_type !== null && defined($p_obj_type))
            {
                $p_obj_type = constant($p_obj_type);
            } // if

            if (!($p_obj_type > 0))
            {
                $p_obj_type = false;
            } // if

            return [
                'success' => true,
                'data'    => [
                    'csv_first_line'  => isys_module_import_csv::get_csv(C__IMPORT__CSV_DIRECTORY . $p_csv_filename, $p_csv_separator, isys_module_import_csv::CL__GET__HEAD),
                    'csv_second_line' => isys_module_import_csv::get_csv(
                        C__IMPORT__CSV_DIRECTORY . $p_csv_filename,
                        $p_csv_separator,
                        isys_module_import_csv::CL__GET__CONTENT__FIRST_LINE
                    ),
                    'categories'      => isys_module_import_csv::get_importable_categories($_POST['multivalue'], $p_obj_type)
                ],
                'message' => null
            ];
        }
        catch (Exception $e)
        {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        } // try
    } // function

    private function get_import_filter()
    {
        global $g_dirs;
        $l_handler    = [];
        $l_dir_import = $g_dirs["import"] . "handler/";
        $l_log        = isys_factory_log::get_instance('import')
            ->set_destruct_flush((bool) isys_settings::get('logging.cmdb.import', false));

        if (is_dir($l_dir_import))
        {
            $l_import_fh = opendir($l_dir_import);
            while ($l_file = readdir($l_import_fh))
            {
                if ($l_file != "." && $l_file != ".." && $l_file != ".DS_Store" && !in_array($l_file, $this->m_skip_files) && is_file($l_dir_import . "/" . $l_file))
                {

                    $l_class = preg_replace("/^(.*?).class.php$/", "\\1", $l_file);
                    $l_file  = preg_replace("/^isys_import_handler_(.*?).class.php$/", "\\1", $l_file);

                    if (class_exists($l_class))
                    {
                        $l_class_obj = new $l_class($l_log);
                        if (method_exists($l_class_obj, 'get_name'))
                        {
                            $l_import_name = $l_class_obj->get_name();
                        }
                        else
                        {
                            $l_import_name = str_replace(".php", "", str_replace("isys_import_handler_", "", $l_file));
                        } // if

                        $l_handler[$l_file] = $l_import_name;
                    } // if
                } // if
            } // while
        } // if

        return $l_handler;
    }

    /**
     * Method for a new import.
     *
     * @global  isys_component_template                  $g_comp_template
     * @global  isys_component_database                  $g_comp_database
     * @global  string                                   $g_absdir
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     * @global  array                                    $g_dirs
     */
    private function import_new()
    {
        global $g_comp_template, $g_comp_database, $g_absdir, $g_comp_template_language_manager, $g_dirs;

        $l_fileman = new isys_component_filemanager();
        $l_fileman->set_upload_path(C__IMPORT__DIRECTORY);
        $l_fileman->set_disallowed_filetypes(
            [
                "exe",
                "bin",
                "bat",
                "cmd",
                "php",
                "pl",
                "cgi",
                "py",
                "rb",
                "phtml"
            ]
        );

        // Object types for <select>.
        $l_dao    = new isys_cmdb_dao($g_comp_database);
        $l_otypes = $l_dao->get_types();

        while ($l_row = $l_otypes->get_row())
        {
            if (!in_array(
                $l_row["isys_obj_type__id"],
                [
                    C__OBJTYPE__CONTAINER,
                    C__OBJTYPE__GENERIC_TEMPLATE,
                    C__OBJTYPE__LOCATION_GENERIC
                ]
            )
            )
            {
                $l_object_types[$l_row["isys_obj_type__id"]] = $g_comp_template_language_manager->get($l_row["isys_obj_type__title"]);
            } // if
        } // while

        if (is_array($_FILES) && count($_FILES) > 0)
        {
            $l_mimetypes = [
                'text/comma-separated-values',
                'text/csv',
                'application/csv',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'application/octet-stream'
            ];

            if ($_FILES['import_file']['type'] == 'application/octet-stream')
            {
                isys_application::instance()->container['notify']->notice(_L('LC__MODULE__IMPORT__UPLOAD_MIMETYPE_MISMATCH'), ['life' => 10]);
            } // if

            if (!in_array($_FILES["import_file"]['type'], $l_mimetypes) && !strstr($_FILES["import_file"]['type'], 'text'))
            {
                isys_application::instance()->container['notify']->error(_L('LC__MODULE__IMPORT__UPLOAD_PROHIBITED'));
            }
            else
            {
                $l_fileman->receive($_FILES["import_file"]);
                $l_errors    = $l_fileman->get_errors();

                if (count($l_errors) > 0)
                {
                    $l_error = (!strstr($l_errors[0], "%s")) ? $l_errors[0] : null;
                    $g_comp_template->assign("class", "msgbox_error")
                        ->assign(
                            "message",
                            $g_comp_template_language_manager->get('LC__UNIVERSAL__FILE_UPLOAD__FAILED') . ': ' . $l_error . '(' . $_FILES['import_file']['name'] . ')'
                        );
                }
                else
                {
                    $g_comp_template->assign("class", "msgbox_info")
                        ->assign("message", $g_comp_template_language_manager->get('LC__UNIVERSAL__FILE_UPLOAD__SUCCESS') . ": (" . $_FILES["import_file"]["name"] . ")");
                } // if
            }
        } // if

        $g_comp_template->assign("object_types", $l_object_types)
            ->assign("import_path", str_replace($g_absdir . "/", "", C__IMPORT__DIRECTORY));
    } //function

    /**
     * Method for displaying the inventoried objects.
     *
     * @global  isys_component_template $g_comp_template
     */
    private function imports()
    {
        global $g_comp_template;

        $l_imports = $this->get_imports();

        if ($l_imports->num_rows() > 0)
        {
            // Link for each table-row.
            $l_rowlink = '?' . C__GET__MODULE_ID . '=' . C__MODULE__CMDB . '&' . C__CMDB__GET__VIEWMODE . '=1100' . '&' . C__CMDB__GET__TREEMODE . '=1006' . '&' . C__CMDB__GET__OBJECTTYPE . '=[{isys_obj__isys_obj_type__id}]' . '&' . C__CMDB__GET__OBJECT . '=[{isys_obj__id}]';

            // Array with table header titles.
            $l_tableheader = [
                'isys_obj__id'         => _L('LC__UNIVERSAL__ID'),
                'isys_obj_type__title' => _L('LC__CMDB__OBJTYPE'),
                'isys_obj__title'      => _L('LC__UNIVERSAL__TITLE'),
                'isys_obj__hostname'   => _L('LC__CATP__IP__HOSTNAME'),
                'isys_obj__scantime'   => _L('LC_CALENDAR_POPUP__TIME_OF_DAY') . ' (Scan)'
            ];

            $l_objList = new isys_component_list(null, $l_imports);
            $l_objList->config($l_tableheader, $l_rowlink, '', true);
            $l_objList->createTempTable();

            $l_pagerlink = '?' . C__GET__MODULE_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__PARAM . '=' . $_GET[C__GET__PARAM] . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID];

            $l_navbar = isys_component_template_navbar::getInstance();
            $l_navbar->set_url($l_pagerlink, C__NAVMODE__FORWARD);
            $l_navbar->set_url($l_pagerlink, C__NAVMODE__BACK);

            $g_comp_template->assign("g_list", $l_objList->getTempTableHtml());

            if ($_GET[C__GET__AJAX] == 1)
            {
                $l_navbar->show_navbar();
                echo $g_comp_template->display('modules/import/import_main.tpl');
                die();
            } // if
        } // if
    } // function

    /**
     * Method for retrieving the files.
     *
     * @return  array
     */
    private function get_files()
    {
        global $g_loc;

        $l_files    = [];
        $l_filedata = '';
        try
        {
            if (is_writable(C__IMPORT__DIRECTORY))
            {
                $l_fh          = opendir(C__IMPORT__DIRECTORY);
                $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
                while ($l_file = readdir($l_fh))
                {
                    if (strpos($l_file, ".") !== 0 && !is_dir(C__IMPORT__DIRECTORY . "/" . $l_file))
                    {
                        $l_hostname        = false;
                        $l_data            = null;
                        $l_imported_mktime = 0;
                        $l_scantime_mktime = 0;

                        $l_object_count = "?";
                        $l_type         = $l_empty_value;
                        $l_stripped     = str_replace(".xml", "", $l_file);

                        if (file_exists(C__IMPORT__DIRECTORY . $l_file))
                        {
                            $l_filedata = file_get_contents(C__IMPORT__DIRECTORY . $l_file);
                            try
                            {
                                if (strpos(trim($l_filedata), "<") === 0)
                                {
                                    $l_replace_array = [
                                        '<value></value>' => '',
                                    ];
                                    $l_xmlcontent    = isys_glob_replace_accent(strtr(isys_glob_utf8_encode($l_filedata), $l_replace_array));

                                    if (!empty($l_xmlcontent))
                                    {
                                        try
                                        {
                                            $l_xmllib = new isys_library_xml($l_xmlcontent);
                                            $l_data   = $l_xmllib->simple_xml_string($l_xmlcontent);
                                        }
                                        catch (ErrorException $e)
                                        {
                                            // xml file not readable
                                        }
                                    } // if
                                } // if
                            }
                            catch (Exception $e)
                            {
                                isys_notify::error($e->getMessage(), ['sticky' => true]);
                            }
                        } // if

                        if ($l_data)
                        {
                            $l_hostname = (string) $l_data->hostname;
                            $l_scantime = (string) $l_data->datetime;
                            if ($l_data->hostname)
                            {
                                $l_type         = "inventory";
                                $l_object_count = 1;
                            } // if

                            if (!$l_scantime)
                            {
                                $l_scantime = (string) $l_data->head->datetime;
                            } // if

                            if (!empty($l_hostname)) $l_status = $this->check_status($l_hostname);
                            else
                                $l_status = [];

                            if (strstr($l_scantime, "/"))
                            {
                                $l_scantmp_1 = explode(" ", $l_scantime);
                                $l_date      = $l_scantmp_1[0];
                                $l_time      = $l_scantmp_1[1];

                                $l_scantmp_2 = explode("/", $l_date);

                                $l_scantime = $l_scantmp_2[0] . "." . $l_scantmp_2[1] . "." . $l_scantmp_2[2] . " " . $l_time;
                            }

                            if (!$l_hostname && isset($l_data->objects))
                            {
                                $l_object_count = (int) $l_data->objects->attributes()->count;

                                if (isset($l_data->head))
                                {

                                    if (isset($l_data->head->format))
                                    {
                                        $l_type = (string) $l_data->head->format;
                                    }
                                    else
                                    {
                                        $l_type = (string) $l_data->head->type;
                                    }

                                    $l_hostname = (string) $l_data->objects->object->title;
                                    $l_status   = $this->check_status(null, $l_hostname);
                                }
                            }

                            if ($l_status)
                            {
                                $l_imported_mktime = strtotime($l_status["isys_obj__scantime"]);
                            } // if

                            if ($l_scantime)
                            {
                                $l_scantime_mktime = strtotime($l_scantime);
                            } // if

                            if ($l_scantime_mktime <= $l_imported_mktime)
                            {
                                $l_dupe = true;
                            }
                            else
                                $l_dupe = false;
                        }
                        else
                        {

                            $l_err = null;
                            foreach (libxml_get_errors() as $l_error)
                            {
                                $l_err[] = $l_error->message;
                            } // foreach

                            $l_status = false;
                            $l_dupe   = false;
                            if (strstr($l_file, "csv"))
                            {
                                $l_type         = "csv";
                                $l_object_count = count(explode("\n", $l_filedata)) - 1;
                                if ($l_object_count <= 0) $l_object_count = 1;
                            }
                            else
                            {
                                if (is_array($l_err))
                                {
                                    $l_type = "XML: Parse error " . implode(", ", $l_err);
                                }
                            } // if
                        } // if

                        $l_dlgets                       = $this->m_userrequest->get_gets();
                        $l_dlgets[C__GET__FILE_MANAGER] = "get";
                        $l_dlgets[C__GET__FILE_NAME]    = $l_file;
                        $l_dlgets[C__GET__MODULE_ID]    = C__MODULE__IMPORT;
                        $l_download_link                = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

                        $l_files[$l_stripped] = [
                            "filename"   => $l_file,
                            "stripped"   => $l_stripped,
                            "count"      => $l_object_count,
                            "type"       => $l_type,
                            "scantime"   => $g_loc->fmt_datetime($l_scantime_mktime),
                            "importtime" => $g_loc->fmt_datetime($l_imported_mktime),
                            "dupe"       => $l_dupe,
                            "status"     => $l_status,
                            "download"   => $l_download_link
                        ];
                    } // if
                    sort($l_files);

                    unset($l_scantime, $l_scantime_mktime, $l_imported_mktime, $l_object_count);
                } // while
            }
            else
            {
                throw new isys_exception_general(
                    C__IMPORT__DIRECTORY . " is not writable. Please create " . "it and give Apache writing rights to it on unix systems."
                );
            }
        }
        catch (Exception $e)
        {
            ;
        }

        return $l_files;
    } // function

    /**
     * Build and assign system menu
     */
    private function build_system_menu()
    {
        $l_tree = $this->m_userrequest->get_menutree();

        $this->build_tree($l_tree, false);
        $this->m_userrequest->get_template()
            ->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
    } // function

    /**
     * Handles the download of import files
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     */
    private function handle_file_manager()
    {
        try
        {
            $l_gets         = $this->m_userrequest->get_gets();
            $l_file_manager = new isys_component_filemanager();

            if (isset($l_gets[C__GET__FILE_NAME]))
            {
                $l_filename = $l_gets[C__GET__FILE_NAME];
                $l_files    = null;

                /**
                 * send directly outputs the file to the client
                 */
                if (!$l_file_manager->send(C__IMPORT__DIRECTORY . $l_filename, $l_files, C_FILES__MODE_DOWNLOAD))
                {
                    header("HTTP/1.0 404 Not Found");
                    die;
                } // if
            } // if
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        } // try
    } // function
} // class
