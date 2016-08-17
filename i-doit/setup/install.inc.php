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
 * Installer
 *
 * @package    i-doit
 * @subpackage General
 * @version    Dennis Stücken    <dstuecken@i-doit.org> - 2008-06-19, 01-2009
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/* Some initialization */
error_reporting(E_ALL & !E_NOTICE);

/* some constants and settings */
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
ini_set("max_execution_time", 360);
ini_set("memory_limit", "128M");

define("C_EDITION", "web");

define("ISYS_NULL", null);

/* where are the setup modules located? */
$g_module_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR;
define("C__DIR__MODULES", "modules/");
define("C__DIR__FILES", "files/");

/* include required sources */
include_once($g_absdir . "/src/functions.inc.php");
include_once($g_absdir . "/src/constants.inc.php");

/* Include autoloader */
$g_dirs["class"] = $g_absdir . "/src/classes/";
include_once($g_absdir . "/src/autoload.inc.php");

/**
 * -------------------------------------------------------------------------------------------
 * Cache checker
 * -------------------------------------------------------------------------------------------
 */
function nowrite($p_dir)
{
    global $g_nowrite;
    $g_nowrite = true;
    isys_glob_display_error(
        "<strong>" . $p_dir . "</strong> is <strong>not writable</strong> or does not exist!<br />" . "Make sure the apache process is allowed to write into it.<br />" . "On Unix systems, do \"chmod 777 " . $p_dir . "\"."
    );
}

foreach ([
             $g_absdir . "/temp",
             $g_absdir . "/src/"
         ] as $l_dir)
{
    if (!is_writable($l_dir))
    {
        nowrite($l_dir);
    }
}

$l_dir = opendir($g_absdir . "/src/themes/");
while ($l_f = readdir($l_dir))
{
    $l_thedir = $g_absdir . "/src/themes/" . $l_f . "/";
    if (is_dir($l_thedir) && strpos($l_f, ".") !== 0)
    {
        if (!is_writable($l_thedir . "smarty/templates_c")) nowrite($l_thedir . "smarty/templates_c");
        if (!is_writable($l_thedir . "smarty/cache")) nowrite($l_thedir . "smarty/cache");
    }
}

if (isset($g_nowrite) && $g_nowrite) die;

/* ------------------------------------------------------------------------------------------- */

/**
 * Check for PHP Version and if it is compatible
 */
$l_php_version = phpversion();
if ($l_php_version != false)
{
    if (function_exists("version_compare"))
    {
        if (version_compare($l_php_version, PHP_VERSION_MINIMUM, "<") == -1)
        {
            startup_die(
                "You have PHP " . $l_php_version . ". You need at least PHP " . PHP_VERSION_MINIMUM . "."
            );
        }
    }
    else
    {
        startup_die(
            "Function 'version_compare' missing.\n" . "It seems you have an old PHP version. You need at least " . PHP_VERSION_MINIMUM
        );
    }
}
else
{
    startup_die("phpversion() failed. Your system isn't supported!");
}

/**
 * Check installed extensions
 */
$l_ext_needed = [
    "mysqli",
    "xml",
    "standard",
    "pcre",
    "session"
];

$l_ext_have = array_intersect($l_ext_needed, get_loaded_extensions());
if (count($l_ext_have) < count($l_ext_needed))
{
    startup_die(
        "Not all needed extensions are installed.\n" . "I need: " . implode(" ", $l_ext_needed) . "\n" . "I have: " . implode(" ", $l_ext_have)
    );
}

/* start session */
session_start();

/* installation code starts here */
$g_osUNIX = false;
$g_osWin  = false;
switch (strtoupper(substr(PHP_OS, 0, 3)))
{
    case "WIN":
        $g_osWin = true;
        break;
    default:
        $g_osUNIX = true;
}

/**
 * Terminates the execution and show an error
 *
 * @param string  $p_text
 * @param string  $p_file
 * @param integer $p_line
 */
function install_die($p_text, $p_file, $p_line)
{
    die("An error occured in <b>" . $p_file . "</b>, in line <b>" . $p_line . "</b>:<br />" . $p_text);
}

/**
 * Returns the current install step
 *
 * @return integer
 */
function install_get_current_step()
{
    if (isset($_POST["install_step"]))
    {
        return intval($_POST["install_step"]);
    }

    return 1;
}

/**
 * Loads a template - returns NULL on failure
 *
 * @param string $p_filename
 *
 * @return string
 */
function tpl_load($p_filename)
{
    if (file_exists($p_filename))
    {
        return file_get_contents($p_filename);
    }

    return null;
}

/**
 * Sets variables in the template, returns false on failure
 *
 * @param string $p_template
 * @param array  $p_array
 *
 * @return boolean
 */
function tpl_set(&$p_template, $p_array)
{
    if (is_string($p_template))
    {
        foreach ($p_array as $l_key => $l_value)
        {
            if (!is_array($l_value))
            {
                $p_template = str_replace(
                    "[" . strtoupper($l_key) . "]",
                    $l_value,
                    $p_template
                );
            }
        }

        return true;
    }

    return false;
}

/**
 * Processes a template - here you can put things like language-specific
 * replacements or other template options.
 *
 * @param string $p_template
 *
 * @return boolean
 */
function tpl_process(&$p_template)
{
    return $p_template;
}

/* Install steps */
$g_install_steps = [
    1 => [
        "file"     => "1_system_check.inc.php",
        "template" => "1_system_check.tpl"
    ],
    2 => [
        "file"     => "2_directory_config.inc.php",
        "template" => "2_directory_config.tpl"
    ],
    3 => [
        "file"     => "3_database_config.inc.php",
        "template" => "3_database_config.tpl"
    ],
    4 => [
        "file"     => "4_framework_config.inc.php",
        "template" => "4_framework_config.tpl"
    ],
    5 => [
        "file"     => "5_config_check.inc.php",
        "template" => "5_config_check.tpl"
    ],
    6 => [
        "file"     => "6_installation.inc.php",
        "template" => "6_installation.tpl"
    ],
    7 => [
        "file"     => "7_finish.inc.php",
        "template" => "7_finish.tpl"
    ]
];

/* Internal installation settings */
$g_settings = [
    "mysqlDumpSystem"     => dirname(__FILE__) . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "idoit_system.sql",
    "mysqlDumpMandator"   => dirname(__FILE__) . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "idoit_data.sql",
    "configTemplate"      => dirname(__FILE__) . DIRECTORY_SEPARATOR . "config_template.inc.php",
    "configReportManager" => dirname(__FILE__) . DIRECTORY_SEPARATOR . "config_template_irm.inc.php",
    "configDestination"   => "config.inc.php",
    "mysqlPrivileges"     => "ALL",
    "cronConfig"          => dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "cron/crontab"
];

/* Retrieve idoit www-dir */
$l_idoit_www = rtrim(str_replace("\\", "/", dirname($_SERVER["PHP_SELF"])), "/") . "/";

/* Config variables used in config_template.inc.php */
$l_irmdir = rtrim(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "irm" . DIRECTORY_SEPARATOR;

/* Get browser */
$l_browser = _get_browser();
if ($l_browser["type"] == "MSIE" && $l_browser["version"] == "6.0")
{
    $l_ajax_calls = "false";
}
else
{
    $l_ajax_calls = "true";
}

/* Predefined settings: */
$g_config = [
    "idoit.dir"                 => [
        "content" => rtrim(str_replace("\\", "/", dirname(dirname(__FILE__))), "/") . "/",
        "name"    => "i-doit Directory"
    ],
    "config.adminauth.username" => [
        "content" => "admin",
        "name"    => "Username"
    ],
    "config.adminauth.password" => [
        "content" => "",
        "name"    => "Password"
    ],
    "config.dir.fileman.file"   => [
        "content" => dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR,
        "name"    => "Save path for file manager"
    ],
    "config.dir.fileman.image"  => [
        "content" => dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR,
        "name"    => "Image path"
    ],
    "config.db.host"            => [
        "content" => "127.0.0.1",
        "name"    => "Database host"
    ],
    "config.db.port"            => [
        "content" => "3306",
        "name"    => "Database port"
    ],
    "config.db.username"        => [
        "content" => "idoit",
        "name"    => "Database username"
    ],
    "config.db.password"        => [
        "content" => "",
        "name"    => "Database password"
    ],
    "config.db.password2"       => [
        "content" => "",
        "name"    => "Database password (retype)"
    ],
    "config.db.root.username"   => [
        "content" => "root",
        "name"    => "Database root username"
    ],
    "config.db.root.password"   => [
        "content" => "",
        "name"    => "Database root password"
    ],
    /*"config.db.root.password2"		=>
        array("content" => "", "name" => "Database root password (retype)"),*/
    "config.db.name"            => [
        "content" => "idoit_system",
        "name"    => "System Database Name"
    ],
    "config.db.config"          => [
        "content" => "",
        "name"    => "Database Config"
    ],
    "config.db.mode"            => [
        "content" => "",
        "name"    => "Database mode"
    ],
    "config.mandant.name"       => [
        "content" => "idoit_data",
        "name"    => "Mandator database name"
    ],
    "config.mandant.title"      => [
        "content" => "Your companyname",
        "name"    => "Mandator Title"
    ],
    "config.mandant.autoinc"    => [
        "content" => "1",
        "name"    => "Auto-Increment start value"
    ],
    "config.dir.src"            => [
        "content" => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src"),
        "name"    => "Configuration path"
    ]
];

/* Taken from update procedure (update.inc.php) */
$g_updatedir = $g_absdir . DIRECTORY_SEPARATOR . "updates" . DIRECTORY_SEPARATOR;

define("C__XML__SYSTEM", "update_sys.xml");
define("C__XML__DATA", "update_data.xml");

include_once($g_updatedir . "classes/isys_update.class.php");

$l_fh = opendir($g_updatedir . "classes");
while ($l_file = readdir($l_fh))
{
    if (strpos($l_file, ".") !== 0 && !include_once($g_updatedir . "classes/" . $l_file)) __die("Could not load " . $g_updatedir . $l_file, __FILE__, __LINE__);
}
/* -- */

/* Okay - now we start - at first load the install template */
$g_tpl_main = tpl_load("setup/install.tpl");
if ($g_tpl_main !== null)
{
    $g_current_step = install_get_current_step();

    $l_previous_disabled = false;
    $l_previous_step     = $g_current_step - 1;
    if ($l_previous_step < 1)
    {
        $l_previous_step     = 1;
        $l_previous_disabled = true;
    }

    $l_next_disabled = false;
    $l_next_step     = $g_current_step + 1;
    if ($l_next_step >= (count($g_install_steps) - 1))
    {
        $l_next_step     = count($g_install_steps) - 1;
        $l_next_disabled = true;
    }

    if (isset($g_install_steps[$g_current_step]))
    {
        $l_stepFile = $g_install_steps[$g_current_step]["file"];
        $l_stepTpl  = $g_install_steps[$g_current_step]["template"];

        if ($g_current_step == 6 && $_POST["install_now"] == "1")
        {
            $l_stepTpl = "6_finishinstall.tpl";
        }

        /* Get output buffer into $g_tpl_step */
        ob_start();
        include_once("setup/" . $l_stepTpl);
        $g_tpl_step = ob_get_contents();
        ob_end_clean();

        if ($g_tpl_step !== null)
        {

            /* Overwrite config parameters with data from POST array */
            foreach ($_POST as $l_key => $l_value)
            {
                $l_key = str_replace("_", ".", $l_key);
                $l_key = str_replace(".field", "", $l_key);
                if (isset($g_config[$l_key]))
                {
                    $g_config["$l_key"]["content"] = $l_value;
                }
            }

            foreach ([
                         "config.dir.fileman.file",
                         "config.dir.fileman.image",
                         "config.dir.src"
                     ] as $l_key)
            {
                $g_config[$l_key]["content"] = preg_replace("/[\\\\]+/", "\\", $g_config[$l_key]["content"]);

                $g_config[$l_key]["content"] = preg_replace("/[\/]+/", "/", $g_config[$l_key]["content"]);
            }

            if (isset($_POST["module"]))
            {
                $g_config["config.modules"]["content"] = $_POST["module"];
            }

            /**
             * Variables you can use in your install step:
             * + $g_tpl_main : Main Template
             * + $g_tpl_step : Template for the selected step
             */
            if (require_once("setup/" . $l_stepFile))
            {
                tpl_set(
                    $g_tpl_main,
                    [
                        "MAIN_CONTENT" => tpl_process($g_tpl_step)
                    ]
                );

                /* Callback function to give the step handler the chance to handle
                   POST parameters directly */
                if (function_exists("process_after_posttransfer"))
                {
                    process_after_posttransfer();
                }

                /* Build HTML with all config parameters as HIDDEN INPUT's
                    (and build template array) */
                $l_parameters = "";
                $l_pararray   = [];

                foreach ($g_config as $l_key => $l_value)
                {
                    $l_parameters .= "  <!-- " . $l_value["name"] . " //--><input " . "type=\"hidden\" " . "name=\"" . strtolower(
                            $l_key
                        ) . "\" " . "value=\"" . $l_value["content"] . "\" " . "/>\n";

                    $l_pararray[strtoupper($l_key)] = $l_value["content"];
                }

                /* Show only the things related to the used operating system */
                tpl_set(
                    $g_tpl_main,
                    [
                        "OS_VISIBILITY" => ".visibilityWin {\n" . ($g_osWin ? "" : "display: none\n") . "}\n\n" . ".visibilityUnix {\n" . ($g_osUNIX ? "" : "display: none\n") . "}\n\n"
                    ]
                );

                /* Write HTML with parameters */
                tpl_set(
                    $g_tpl_main,
                    [
                        "MAIN_CONFIG_PARAMETERS" => $l_parameters
                    ]
                );

                /* Set parameters into template */
                tpl_set($g_tpl_main, $l_pararray);

                /* Set dialog buttons for 'previous' and 'next' */
                tpl_set(
                    $g_tpl_main,
                    [
                        "MAIN_PREV_DISABLED" => (($l_previous_disabled) ? "disabled=\"disabled\"" : ""),
                        "MAIN_NEXT_DISABLED" => (($l_next_disabled) ? "disabled=\"disabled\"" : ""),
                        "MAIN_PREV_STEP"     => $l_previous_step,
                        "MAIN_NEXT_STEP"     => $l_next_step,
                        "MAIN_CURRENT_STEP"  => $g_current_step,
                        "FORM_ACTION"        => '',
                    ]
                );

                foreach ($g_install_steps as $l_step => $l_data)
                {
                    if ($l_step < $g_current_step)
                    {
                        tpl_set(
                            $g_tpl_main,
                            [
                                ("MAIN_ACTION_STEP" . $l_step) => "onClick=\"document.forms.install_form.install_step.value={$l_step}; document.forms.install_form.submit();\""
                            ]
                        );
                    }
                    else
                    {
                        tpl_set(
                            $g_tpl_main,
                            [
                                ("MAIN_ACTION_STEP" . $l_step) => ""
                            ]
                        );

                    }
                }
            }
            else install_die(
                "Could not include setup/" . $l_stepFile,
                __FILE__,
                __LINE__
            );
        }
        else install_die(
            "Could not find associated template setup/" . $l_stepTpl,
            __FILE__,
            __LINE__
        );
    }
    else install_die(
        "Could not find current installer step $g_current_step",
        __FILE__,
        __LINE__
    );

    echo tpl_process($g_tpl_main);

}