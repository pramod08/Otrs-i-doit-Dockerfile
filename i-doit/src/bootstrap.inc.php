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
 * Global definitions.
 *
 * This file provides basic functionalities needed by all source files.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
include_once('version.inc.php');

// Get localization class
include_once __DIR__ . '/locales.inc.php';

/* ---------------------------------------------- FUNCTIONS & CONSTANTS ---- */
include_once("functions.inc.php");
include_once("constants.inc.php");
/* ------------------------------------------------------------------------- */

global $g_absdir;
$g_config = [
    "base_dir"          => $g_absdir . DIRECTORY_SEPARATOR,
    "www_dir"           => str_replace('src/jsonrpc.php', '', str_replace('index.php', '', @$_SERVER['SCRIPT_NAME'])),
    "override_host"     => false,
    "theme"             => "default",
    "theme_selectable"  => true,
    "startpage"         => "index.php",
    "smarty_debug_host" => 'localhost',
    //	"show_barcodes"     => true, // This can now be set in the tenant-settings. @see ID-1424
    "html-encoding"     => "utf-8",
    "ajax_calls"        => true
];

/**
 * @desc Directory configuration
 * -------------------------------------------------------------------------
 *       Array of required global directory structure, the rest is read
 *       and set by the system registry. NOTE: You should NOT modify this!
 *
 *       FILE MANAGER SETTINGS
 *
 *       Modify them in order to control the file manager, downloads and
 *       uploads. target_dir must be absolute and tailed by /, furthermore,
 *       your apache-user (normally www-data) needs full access rights (RWX)
 *       to this directory. temp_dir is /tmp/ on UNIX systems, otherwise
 *       configure it here manually for Win.
 *       The image_dir is used for the uploaded object images www-data needs also
 *       full access here
 */
$g_dirs = [
    "css_abs"      => $g_config["base_dir"] . "src/themes/default/css/",
    "js_abs"       => $g_config["base_dir"] . "src/tools/js/",
    "temp"         => $g_config["base_dir"] . "temp/",
    "class"        => $g_config["base_dir"] . "src/classes/",
    "import"       => $g_config["base_dir"] . "src/classes/import/",
    "temp_www"     => $g_config["www_dir"] . "temp/",
    "images"       => $g_config["www_dir"] . "images/",
    "theme_images" => $g_config["www_dir"] . "src/themes/default/images/",
    "handler"      => $g_config["base_dir"] . "src/handler/"
];

// Global error/exception message.
$g_error = "";

// Active/Deactivate ajax calls.
$g_ajax_calls = true;

// Internal smarty/template config.
$g_template = [
    "start_page" => "main.tpl",
    "ajax"       => "ajax.tpl"
];

/* ------------------------------------------------------------------------- */
/* -------------------------------------------- INCLUDE CLASS AUTOLOADER --- */
include_once("autoload.inc.php");
/* ------------------------------------------------------------------------- */
/* -------------------------------------------- INCLUDE CONVERTER CLASS ---- */
include_once("convert.inc.php");

// Get global converter.
$g_convert = new isys_convert();

/* ------------------------------------------------------------------------- */
/* -------------------------------------------------- GENERAL COMPONENTS --- */

// Available PDO drivers.
if (class_exists("PDO"))
{
    $g_pdo_drivers = PDO::getAvailableDrivers();
}
else
{
    $g_pdo_drivers = [];
} // if

// If this is set to true, there is no template display at all
$g_output_done = false;

/**
 * Including isys_application
 */
include_once(__DIR__ . '/classes/core/isys_application.class.php');

/**
 * Call bootstrapping and load all required components
 */
isys_application::instance()
    ->language(isset($_GET["lang"]) ? $_GET["lang"] : null)
    ->bootstrap();
