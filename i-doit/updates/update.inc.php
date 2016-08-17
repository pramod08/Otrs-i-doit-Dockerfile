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
 * i-doit - Updates
 *
 * @package     i-doit
 * @subpackage  Update
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

// ------------- Configuration -------------
define("C__XML__SYSTEM", "update_sys.xml");
define("C__XML__DATA", "update_data.xml");
define("C__CHANGELOG", "CHANGELOG");
define("C__DIR__FILES", "files/");
define("C__DIR__MIGRATION", "migration/");
define("C__DIR__MODULES", "modules/");
define("C__URL__PRO", "https://login.i-doit.com");
define("C__URL__OPEN", "http://www.i-doit.org");
define("C__IDOIT_UPDATES", "https://www.i-doit.com/updates.xml");

/* Defining minimum php version for this update */
if (!defined('PHP_VERSION_MINIMUM')) define('PHP_VERSION_MINIMUM', '5.4.0');
if (!defined('PHP_VERSION_MINIMUM_RECOMMENDED')) define('PHP_VERSION_MINIMUM_RECOMMENDED', '5.5.0');
if (!defined('MARIADB_VERSION_MINIMUM')) define('MARIADB_VERSION_MINIMUM', '10.0');
if (!defined('MYSQL_VERSION_MINIMUM')) define('MYSQL_VERSION_MINIMUM', '5.6');
if (!defined('MYSQL_VERSION_MINIMUM_RECOMMENDED')) define('MYSQL_VERSION_MINIMUM_RECOMMENDED', '5.7');

global $g_config, $g_absdir, $g_product_info, $g_comp_template, $g_comp_database, $g_comp_database_system;

// i-doit Temp Directory.
$g_temp_dir = $g_absdir . DIRECTORY_SEPARATOR . "temp" . DIRECTORY_SEPARATOR;
$g_log_dir = $g_absdir . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR;

// Log File.
$l_debug_file         = date("Y-m-d") . '-' . $g_product_info['version'] . '_idoit_update.log';
$g_debug_log          = $g_log_dir . $l_debug_file;
$g_debug_log_www      = $g_config['www_dir'] . 'log/' . $l_debug_file;
$l_migration_log_file = date("Y-m-d") . '-' . $g_product_info['version'] . '_idoit_migration.log';

// Update Temp file.
$g_tempfile = $g_temp_dir . "tmp_update.zip";

// Place where the i-doit update information are stored.
if (defined('C__IDOIT_UPDATES_PRO'))
{
    $g_updatexml = C__IDOIT_UPDATES_PRO;
}
else
{
    $g_updatexml = C__IDOIT_UPDATES;
}

// Your Apache user (Currently unused).
$g_apache_user = "www-data";

$g_updatedir = str_replace("\\", "/", dirname(__FILE__) . "/");

$g_versiondir = $g_updatedir . "versions/";
$g_upd_dir    = $g_versiondir . $_SESSION["update_directory"];
$g_file_dir   = $g_upd_dir . "/" . C__DIR__FILES;

$g_post = $_POST;
$g_get  = $_GET;

$_SESSION["error"] = 0;

/* Increase session time while updating */
if (method_exists('isys_component_session', 'instance'))
{
    isys_component_session::instance()
        ->set_session_time(999999999);
}

if (intval(ini_get('display_errors')) != 1)
{
    ini_set("display_errors", "1");
} // if

if (intval(ini_get('memory_limit')) < 512)
{
    ini_set("memory_limit", "512M");
} // if

set_time_limit(0);

$g_windows = false;
$g_unix    = false;
if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
{
    $g_windows = true;
}
else
{
    $g_unix = true;
} // if

/**
 * Terminates the execution and shows an error.
 *
 * @param  string  $p_text
 * @param  string  $p_file
 * @param  integer $p_line
 */
function __die($p_text, $p_file, $p_line)
{
    die("An error occured in <b>" . $p_file . "</b>: <b>" . $p_line . "</b>:<br />" . $p_text);
} // function

/**
 * Get required classes
 *
 * @return  boolean
 */
function get_includes()
{
    global $g_updatedir;

    include_once($g_updatedir . "classes/isys_update.class.php");

    $l_fh = opendir($g_updatedir . "classes");

    while ($l_file = readdir($l_fh))
    {
        if (strpos($l_file, ".") !== 0 && !include_once($g_updatedir . "classes/" . $l_file))
        {
            __die("Could not load " . $g_updatedir . $l_file, __FILE__, __LINE__);
        } // if
    } // while

    return true;
} // function

if (get_includes())
{
    try
    {
        //isys_auth_system_tools::instance()->idoitupdate(isys_auth::EXECUTE);

        // Prepare Steps.
        $g_steps = [
            0 => "error.tpl",
            1 => "steps/1.tpl",
            2 => "steps/2.tpl",
            3 => "steps/4.tpl",
            4 => "steps/5.tpl",
            5 => "steps/6.tpl",
            6 => "steps/7.tpl",
            7 => "steps/8.tpl",
            8 => "steps/9.tpl"
        ];

        $l_steps = count($g_steps);

        $g_current_step = 1;
        if (empty($g_post["step"]))
        {
            $g_current_step = 1;
        }
        else
        {
            if ($g_post["step"] > $l_steps)
            {
                $g_current_step = $l_steps;
            }
            elseif ($g_post["step"] <= $l_steps)
            {
                $g_current_step = $g_post["step"];
            } // if
        } // if

        // Debug log.
        if (isset($_POST['debug_log']) && is_string($_POST['debug_log']) && !empty($_POST['debug_log']))
        {
            $g_debug_log = $_POST['debug_log'];
        } // if

        // Debug log path.
        if (isset($_POST['debug_log_www']) && is_string($_POST['debug_log_www']) && !empty($_POST['debug_log_www']))
        {
            $g_debug_log_www = $_POST['debug_log_www'];
        } // if

        // Migration log.
        if (isset($_POST['migration_log_file']) && is_string($_POST['migration_log_file']) && !empty($_POST['migration_log_file']))
        {
            $l_migration_log_file = $_POST['migration_log_file'];
        } // if

        // Smarty assignments.
        $g_comp_template->assign("g_steps", $g_steps)
            ->assign("g_config", $g_config)
            ->assign("debug_log", $g_debug_log)
            ->assign("debug_log_www", $g_debug_log_www)
            ->assign("migration_log_file", $l_migration_log_file);

        // Get isys_update.
        $l_update = new isys_update();

        // Get log component.
        $l_log = isys_update_log::get_instance();

        // Get and assign system information.
        $l_info = $l_update->get_isys_info();

        $g_comp_template->assign("g_info", $l_info);

        if ($g_current_step == 1)
        {
            // ------------- Do a Version check -------------
            $l_php_version = phpversion();

            if (version_compare($l_php_version, PHP_VERSION_MINIMUM, "<") == -1)
            {
                $g_comp_template->assign(
                    "php_version_error",
                    "You have PHP " . $l_php_version . ". For updating i-doit to the next version you need at least PHP " . PHP_VERSION_MINIMUM . "!"
                )
                    ->assign("g_stop", true);
            } // if

            $l_db_version = $g_comp_database->fetch_row_assoc($g_comp_database->query('SELECT VERSION() AS v;'))['v'];
            $l_is_mariadb = stripos($l_db_version, 'maria') !== false;

            if ($l_is_mariadb)
            {
                if (version_compare($l_db_version, MARIADB_VERSION_MINIMUM, "<") == -1)
                {
                    $g_comp_template->assign(
                        "sql_version_error",
                        "You have MariaDB " . $l_db_version . ". For updating i-doit to the next version you need at least MariaDB " . MARIADB_VERSION_MINIMUM .
                        "!<br /><a href=\"https://i-doit.atlassian.net/wiki/x/IQANAQ\" target=\"_blank\">See our Knowledge Base article for help!</a>"
                    )
                        ->assign("g_stop", true);
                } // if
            }
            else
            {
                if (version_compare($l_db_version, MYSQL_VERSION_MINIMUM, "<") == -1)
                {
                    $g_comp_template->assign(
                        "sql_version_error",
                        "You have MySQL " . $l_db_version . ". For updating i-doit to the next version you need at least MySQL " . MYSQL_VERSION_MINIMUM .
                        "!<br /><a href=\"https://i-doit.atlassian.net/wiki/x/IQANAQ\" target=\"_blank\">See our Knowledge Base article for help!</a>"
                    )
                        ->assign("g_stop", true);
                } // if
            } // if

            $l_php_settings = [
                'magic_quotes_gpc' => [
                    'check'   => !ini_get('magic_quotes_gpc'),
                    'value'   => (ini_get('magic_quotes_gpc') ? 'ON' : 'OFF'),
                    'message' => 'You should turn magic_quotes_gpc <b>off</b> in order to update i-doit.'
                ],
                'max_input_vars'   => [
                    'check'   => (ini_get('max_input_vars') == 0 || ini_get('max_input_vars') >= 10000),
                    'value'   => ini_get('max_input_vars'),
                    'message' => 'You should set max_input_vars to at least <b>10000</b> in order to update i-doit.'
                ],
                'post_max_size'    => [
                    'check'   => (ini_get('post_max_size') == 0 || isys_convert::to_bytes(ini_get('post_max_size')) >= isys_convert::to_bytes('128M')),
                    'value'   => ini_get('post_max_size'),
                    'message' => 'You should set post_max_size to at least <b>128M</b> in order to update i-doit.'
                ]
            ];

            $l_failed_php_settings = array_filter(
                $l_php_settings,
                function ($p_setting)
                {
                    return !$p_setting['check'];
                }
            );

            // Disable the updater, if one or more PHP settings do not match.
            if (count($l_failed_php_settings))
            {
                $g_comp_template->assign("g_stop", true);
            } // if

            $g_comp_template->assign("php_settings", $l_php_settings)
                ->assign("dependencies", isys_update::get_module_dependencies())
                ->assign("apache_dependencies", isys_update::get_module_dependencies(null, 'apache'));
        } // if

        if ($g_current_step == 2 || $g_current_step == 4)
        {
            // Databases (prev|next)
            if (isset($g_post["system_database"]))
            {
                $_SESSION["system_database"] = $g_post["system_database"];
            }
            else
            {
                $_SESSION["system_database"] = -1;
            } // if

            if (isset($g_post["databases"]))
            {
                $_SESSION["mandant_databases"] = $g_post["databases"];
            }
            else
            {
                $_SESSION["mandant_databases"] = -1;
            } // if
        } // if

        if ($g_current_step == 3 || $g_current_step == 5)
        {
            if (isset($g_post["no_file_update"]))
            {
                $_SESSION["no_file_update"] = $g_post["no_file_update"];
            } // if

            if (isset($g_post["no_temp"]))
            {
                $_SESSION["no_temp"] = $g_post["no_temp"];
            } // if

            if (isset($g_post["no_config"]))
            {
                $_SESSION["no_config"] = $g_post["no_config"];
            } // if
        } // if

        // Switch GUI-Steps.
        switch ($g_current_step)
        {
            case 2:
                /**
                 * #########################################################
                 *  Step 2 - Available Updates
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Check for available updates
                 *     - Download an update if available
                 *     - Unzip the downloaded update to $g_updatedir
                 *   - Show current version, revision and changelog
                 *   - Show a selectable list with current downloaded
                 *     updates
                 * ---------------------------------------------------------
                 */

                /**
                 * Get file-component
                 */
                $l_files = new isys_update_files();

                /**
                 * Check for updates on i-doit.org
                 */
                if (isset($g_post["check_update"]) && $g_post["check_update"] == "true")
                {
                    $l_avail_updates = $l_update->get_available_updates($g_versiondir);
                    try
                    {
                        /* Get update */
                        $l_updates = array_reverse($l_update->get_new_versions($l_update->fetch_file($g_updatexml)));

                        $i = 0;

                        $l_new_update = [];
                        while ($l_info["revision"] < $l_updates[$i]["revision"])
                        {
                            $l_new_update = $l_updates[$i++];
                        }

                        if (count($l_avail_updates) > 0)
                        {
                            foreach ($l_avail_updates as $l_up)
                            {
                                if (isset($l_new_update["version"]) && ($l_new_update["version"] != @$l_up["version"]))
                                {
                                    $g_comp_template->assign("g_update", $l_new_update);
                                }
                                else
                                {
                                    $g_comp_template->assign(
                                        "g_update_message",
                                        "No updates available (for your version) " . "or you have already downloaded " . "the most recent one."
                                    );
                                }
                            }
                        }
                        elseif (count($l_new_update) > 0)
                        {
                            $g_comp_template->assign("g_update", $l_new_update);
                        }
                        else
                        {
                            $g_comp_template->assign(
                                "g_update_message",
                                "No updates available (for your version)."
                            );
                        }
                    }
                    catch (Exception $e)
                    {
                        $g_comp_template->assign("g_update_message_class", "red")
                            ->assign("g_update_message", $e->getMessage());
                    }
                }

                /* Assign curresponding url for update notices */
                if (defined('C__ENABLE__LICENCE'))
                {
                    $g_comp_template->assign('site_url', C__URL__PRO);

                    if (isset($_SESSION['licenced']) && $_SESSION['licenced'] === false)
                    {
                        $g_comp_template->assign('licence_error', 'Error. Your licence does not allow any updates');
                    }
                }
                else
                {
                    $g_comp_template->assign('site_url', C__URL__OPEN);
                }

                /* Download the new version */
                if (isset($g_post["dl_file"]) && strlen($g_post["dl_file"]) > 0)
                {

                    $l_filename = $g_post["dl_file"];

                    if (preg_match('/^http[s]?\:\/\/(.*?[\.]?i-doit\.(com|de)|dev\.synetics\.de)\/.*?idoit.*?[\-\.0-9]+-update\.zip$/i', $l_filename))
                    {

                        /* Dowload the update and store it in $g_tempfile */
                        if (file_put_contents($g_tempfile, $l_update->fetch_file($l_filename)) > 0)
                        {

                            /* Read and extract the zipfile to $g_updatedir */
                            if (($l_extracted = $l_files->read_zip($g_tempfile, $g_absdir, false, true)))
                            {

                                /* The template should also know, whether the download was successfull or not */
                                $g_comp_template->assign("g_downloaded", true);

                                /* Delete the temp file */
                                unlink($g_tempfile);
                            }
                            else
                            {
                                $g_comp_template->assign("g_update_message", "Extracting failed.");
                            }
                        }
                        else
                        {
                            $g_comp_template->assign("g_update_message_class", "red")
                                ->assign(
                                    "g_update_message",
                                    "Download failed. " . "Check your internet connection."
                                );
                        }

                    }
                    else
                    {
                        $g_comp_template->assign("g_update_message", "Download failed. File seems not to be an i-doit update!");
                    }
                }

                /**
                 * Get Update-Directories
                 */
                $l_avail_updates = $l_update->get_available_updates($g_versiondir);
                $g_comp_template->assign("g_dirs", $l_avail_updates);

                /**
                 * Disable Button: Next, if no update available
                 */
                if (count($l_avail_updates) == 0)
                {
                    $g_comp_template->assign("g_stop", true);
                }

                break;
            case 3:
                /**
                 * #########################################################
                 *  Step 3 - Databases
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Database selection to apply the update to
                 *   - Show a list with updatable system and
                 *     mandator databases
                 *   - Preselect ALL databases (recommendation)
                 * ---------------------------------------------------------
                 */

                /**
                 * Prepare database list for GUI
                 */
                $g_comp_template->assign("g_databases", $l_update->get_databases());

                /**
                 * Assign name of the system database
                 */
                $g_comp_template->assign("g_system_database", $g_comp_database_system->get_db_name());
                $g_comp_template->assign("sql_mode", $g_comp_database_system->get_strictmode());

                /**
                 * Store selected update directory to session
                 */
                /**
                 * Save the selected directory in a Session varible
                 */
                if (strlen($g_post["dir"]) > 0)
                {
                    $_SESSION["update_directory"] = $g_post["dir"];
                }

                if (!isset($_SESSION["update_directory"]))
                {
                    $g_current_step = 0;
                    $g_comp_template->assign(
                        "g_message",
                        "<p>No Directory selected.<p/>" . "<p>Update aborted..</p>"
                    );
                }

                break;
            case 4:
                /**
                 * #########################################################
                 *  Step 4 - File Update
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Show files which will be updated (Overview)
                 *   - Last step before the _real_ update begins
                 * ---------------------------------------------------------
                 */

                if (!is_writeable($g_absdir) || !is_writeable($g_absdir . '/src') || !is_writeable($g_absdir . '/src/config.inc.php') || !is_writeable(
                        $g_absdir . '/admin'
                    ) || (file_exists($g_absdir . '.htaccess') && !is_writeable($g_absdir . '.htaccess'))
                )
                {
                    $g_comp_template->assign("g_not_writeable", true);
                }

                if (strlen($_SESSION["update_directory"]) > 0)
                {

                    if (is_dir($g_file_dir))
                    {
                        $l_files       = new isys_update_files($g_file_dir);
                        $l_files_array = $l_files->getdir();

                        $l_files_html = "";
                        if (count($l_files_array) > 0)
                        {
                            foreach ($l_files_array as $l_current_file)
                            {
                                $l_files_html .= "[+] " . $l_current_file . "\n";
                            }
                        }
                        else
                        {
                            $l_files_html = "No Files to update available";
                        }

                        $g_comp_template->assign("g_files", $l_files_html);
                        $g_comp_template->assign("g_filecount", count($l_files_array));
                    }
                    else
                    {
                        $g_comp_template->assign("g_filecount", 0);
                        $g_comp_template->assign("g_files", "No files will be updated.");
                    }

                }
                else
                {
                    $g_comp_template->assign("g_stop", true);
                }

                /**
                 * Assign bool value to verify the os
                 */
                $g_comp_template->assign("g_unix", $g_unix);

                break;
            case 5:
                /**
                 * #########################################################
                 *  Step 5 - The allmighty i-doit Update
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Apply the selected update
                 *   - Update the previously selected databases
                 *   - Copy new files into the i-doit directory
                 *   - Show a log with notice/error messages
                 *   - write a debug log to i-doit/log/idoit-update-time.log
                 * ---------------------------------------------------------
                 *
                 * @todo backup i-doit (files and/or database)
                 */

                $l_system_database   = $_SESSION["system_database"];
                $l_mandant_databases = $_SESSION["mandant_databases"];

                /* break, if session is clear unstead of skipping the databases */
                if (is_null($l_system_database) || is_null($l_mandant_databases))
                {
                    $g_current_step = 0;
                    $g_comp_template->assign("g_message", "<p>Your browser session was cleared somehow.<p/>" . "<p>Update aborted..</p>");
                    $g_comp_template->assign("g_debug_info", "<p>Concrete debug information can be found at " . $g_debug_log . "</p>");
                }

                // Okay, let's go!
                if ($l_update->update($l_system_database, $l_mandant_databases))
                {
                    // If the main update process has worked, we try to "install" (or "update") the PRO module (Just in case we update from i-doit OPEN).
                    if (is_array($l_mandant_databases) && count($l_mandant_databases))
                    {
                        $l_db_update = new isys_update_xml();

                        if (file_exists($g_absdir . '/src/classes/modules/pro/install/update_sys.xml') && is_readable(
                                $g_absdir . '/src/classes/modules/pro/install/update_sys.xml'
                            )
                        )
                        {
                            // Update the SYSTEM database.
                            $l_db_update->update_database($g_absdir . '/src/classes/modules/pro/install/update_sys.xml', $g_comp_database_system);
                        } // if

                        if (file_exists($g_absdir . '/src/classes/modules/pro/install/update_data.xml') && is_readable(
                                $g_absdir . '/src/classes/modules/pro/install/update_data.xml'
                            )
                        )
                        {
                            // Now update all (selected) TENANT databases.
                            foreach ($l_mandant_databases as $l_mandant_db_name)
                            {
                                $l_db_update->update_database($g_absdir . '/src/classes/modules/pro/install/update_data.xml', $l_update->get_database($l_mandant_db_name));
                            } // foreach
                        } // if
                    } // if
                } // if

                /**
                 * Write debug log
                 */
                register_shutdown_function(
                    function () use ($l_log, $g_debug_log)
                    {
                        $l_log->write_debug(basename($g_debug_log));
                    }
                );

                /* Assign debug file */
                //$g_comp_template->assign("debug_log", $g_debug_log);

                break;
            case 6:
                /**
                 * #########################################################
                 *  Step 6 - Migration
                 * #########################################################
                 * ---------------------------------------------------------
                 */
                $l_migration     = new isys_update_migration();
                $l_migration_log = [];
                $l_update->get_databases();

                try
                {
                    if (is_array($_SESSION["mandant_databases"]))
                    {
                        $l_mig_log = isys_log_migration::get_instance();
                        $l_mig_log->set_log_file($g_log_dir . $l_migration_log_file);
                        $l_mig_log->set_log_level(isys_log::C__ALL);

                        foreach ($_SESSION["mandant_databases"] as $l_db)
                        {
                            $g_comp_database        = $l_update->get_database($l_db);
                            $l_migration_log[$l_db] = $l_migration->migrate($g_upd_dir . "/" . C__DIR__MIGRATION);
                        }
                        unset($l_mig_log);
                    }
                }
                catch (Exception $e)
                {
                    $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                }

                if (count($l_migration_log) <= 0) $l_migration_log[$g_product_info["version"]][] = "No migration code needed this time.";
                $g_comp_template->assignByRef("migration_log", $l_migration_log);
                break;

            case 7:
                $l_update->get_databases();
                $l_migration = new isys_update_property_migration();

                try
                {
                    if (is_array($_SESSION["mandant_databases"]))
                    {
                        $l_mig_log = isys_log_migration::get_instance();
                        $l_mig_log->set_log_file($g_log_dir . 'prop_' . $l_migration_log_file);
                        $l_mig_log->set_log_level(isys_log::C__ALL);

                        foreach ($_SESSION["mandant_databases"] as $l_db)
                        {
                            $l_result[$l_db] = $l_migration->set_database($l_update->get_database($l_db))
                                ->reset_property_table()
                                ->collect_category_data()
                                ->prepare_sql_queries('g')
                                ->prepare_sql_queries('s')
                                ->prepare_sql_queries('g_custom')
                                ->execute_sql()
                                ->get_results();

                            // We only want to display the successfully migrated classes.
                            $l_result[$l_db] = array_keys($l_result[$l_db]['migrated']);
                            sort($l_result[$l_db]);

                            // ID-2797 Refreshing the configured lists to use the latest property data :)
                            isys_cmdb_dao_object_type::instance($l_update->get_database($l_db))
                                ->refresh_objtype_list_config(null, true);

                            try
                            {
                                // Set default categories for all object types.
                                \idoit\Module\Cmdb\Model\CiTypeCategoryAssigner::factory($l_update->get_database($l_db))
                                    ->setAllCiTypes()
                                    ->setGlobalCategories(
                                        [
                                            'C__CATG__GLOBAL',
                                            'C__CATG__LOGBOOK',
                                            'C__CATG__RELATION',
                                            'C__CATG__VIRTUAL_AUTH',
                                            'C__CATG__PLANNING'
                                        ]
                                    )
                                    ->assign();
                            }
                            catch (Exception $e)
                            {
                                $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                            } // try
                        } // foreach

                        unset($l_mig_log);
                    } // if

                    $g_comp_template->assign('result', $l_result);
                }
                catch (Exception $e)
                {
                    $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                } // try
                break;

            case 8:
                if ($_SESSION["error"] >= 1)
                {

                    $l_message = "There are <strong>" . $_SESSION["error"] . "</strong> errors occurred. Your i-doit could run unstable now. <br />" .
                        "Visit our support forum at http://www.i-doit.org/ for any help.<br /><br />" . "Detailed debug information can be found at <br /><u>{$g_debug_log}</u> on your i-doit web-server.";

                    $g_comp_template->assign("g_message", "<strong>Error!</strong><br /><br />" . $l_message);
                }
                else
                {
                    $l_message = "Your i-doit installation has been successfully updated to a newer version.<br /><br />";

                    if (file_exists($g_debug_log))
                    {
                        $l_message .= "Detailed debug information can be found at <u>{$g_debug_log}</u> on your i-doit web-server.<br /></br />";
                    } // if

                    if (C__UPDATE_MIGRATION && file_exists($g_log_dir . $l_migration_log_file))
                    {
                        $l_message .= "Detailed migration information can be found at <u>" . $g_log_dir . $l_migration_log_file . "</u> on your i-doit web-server.";
                    } // if

                    if (isset($_POST["config_backup"]) && file_exists($_POST["config_backup"]))
                    {
                        $l_message .= "<br /><br />A backup of your old config file can be found at: <u>" . $_POST["config_backup"] . "</u>";
                    } // if

                    $g_comp_template->assign("g_message", "<strong>Congratulations!</strong><br /><br />" . $l_message);
                } // if

                // Call system has changed post notification.
                isys_component_signalcollection::get_instance()
                    ->emit('system.afterChange');

                break;
            case 1:
            default:
                /**
                 * #########################################################
                 *  Step 1 - "Welcome Step"
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Welcome message
                 *   - Show i-doit and system information
                 * ---------------------------------------------------------
                 */

                /**
                 * Get OS information
                 */
                $l_os = [
                    "name"    => php_uname("s"),
                    "version" => php_uname("r") . " " . php_uname("v")
                ];

                $g_comp_template->assign("g_os", $l_os);

                break;
        }

        /**
         * HTTPS Url
         */
        $g_comp_template->assign("g_https", "https://" . $_SERVER["HTTP_HOST"] . $g_config["www_dir"] . "?load=update");

        /**
         * Load Smarty and display index file: update.tpl
         */
        $g_comp_template->assign("g_current_step", $g_current_step);
        $g_comp_template->template_dir = $g_updatedir . "tpl/";
        $g_comp_template->display("update.tpl");

    }
    catch (isys_exception_auth $e)
    {
        isys_glob_display_error($e->getMessage() . '<br /><a href="index.php">' . _L('LC__UNIVERSAL__BACK') . '</a></p>');
        die;
    }
    catch (Exception $e)
    {
        isys_glob_display_error($e->getMessage());
    }
    // try
}