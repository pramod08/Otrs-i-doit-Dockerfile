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
 * @author      Dennis Stücken
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

// Set error reporting.
$l_errorReporting = E_ALL & ~E_NOTICE;

if (defined('E_DEPRECATED'))
{
    $l_errorReporting &= ~E_DEPRECATED;
} // if

if (defined('E_STRICT'))
{
    $l_errorReporting &= ~E_STRICT;
} // if

error_reporting($l_errorReporting);

// Start session.
session_start();

/**
 * @param int $value
 *
 * @return int
 */
function compute_bytes($value)
{
    $value = trim($value);
    $last  = strtolower($value[strlen($value) - 1]);

    switch ($last)
    {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    } //switch/case

    return (int) $value;
} //function

// Set maximal execution time.
if (ini_get("max_execution_time") < 600)
{
    set_time_limit(600);
} // if

$memory_limit = compute_bytes(ini_get('memory_limit'));

if ($memory_limit < (128 * 1024 * 1024))
{
    ini_set('memory_limit', '128M');
} // if

$upload_max_filesize = compute_bytes(ini_get('upload_max_filesize'));

if ($upload_max_filesize < (8 * 1024 * 1024))
{
    ini_set('upload_max_filesize', '8M');
} // if

// Disable asserts.
assert_options(ASSERT_ACTIVE, 0);

// Publish admin center.
define("C__ADMIN_CENTER", true);

// Determine our directory.
global $g_config;
$g_config['base_dir'] = $g_absdir = dirname(__DIR__) . '/';

// Set default timezone.
date_default_timezone_set('Europe/Berlin');
//setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

if (!@include_once($g_absdir . "/src/config.inc.php"))
{
    header("Location: ..");
} // if

if (!@include_once($g_absdir . "/src/constants.inc.php"))
{
    die("Error loading file: " . $g_absdir . "/src/constants.inc.php");
} // if

if (!@include_once($g_absdir . "/src/convert.inc.php"))
{
    die("Error loading file: " . $g_absdir . "/src/convert.inc.php");
} // if

if (!@include_once($g_absdir . "/src/autoload.inc.php"))
{
    die("Could not load " . $g_absdir . "src/autoload.inc.php");
} // if

if (!@include_once($g_absdir . "/src/functions.inc.php"))
{
    die("Could not load " . $g_absdir . "src/functions.inc.php");
} // if

$g_dirs["temp"] = $g_absdir . "/temp/";

// Include english language file
@include_once($g_absdir . "/src/lang/en.inc.php");

// Logout.
if (isset($_GET["logout"]))
{
    unset($_SESSION);
    session_destroy();
} // if

// Globalization.
global $g_db_system;

try
{
    // Set custom warnings handler.
    set_error_handler(
        [
            'isys_core',
            'warning_handler'
        ],
        E_WARNING
    );

    // Connect system database.
    $g_comp_database_system = isys_component_database::get_database(
        $g_db_system["type"],
        $g_db_system["host"],
        $g_db_system["port"],
        $g_db_system["user"],
        $g_db_system["pass"],
        $g_db_system["name"]
    );

    // Include Global constant cache.
    $g_dcs      = isys_component_constant_manager::instance();
    $l_dcs_file = $g_dcs->get_dcs_path();

    if (!file_exists($l_dcs_file))
    {
        $g_dcs->create_dcs_cache();
    } // if

    if (!@include_once($l_dcs_file))
    {
        die("Could not load " . $l_dcs_file);
    } // if

// Get template engine.
    $g_dirs["smarty"] = $g_absdir . "/src/themes/default/smarty/";

    $g_comp_template                                = isys_component_template::instance();
    $g_comp_template->default_template_handler_func = null;

// Register plugins.

    /*
    $g_comp_template->registerPlugin(
        'function', "isys", array(
            $g_comp_template,
            "smarty_function_isys"
        )
    );
*/
    $g_comp_template->setConfigDir($g_dirs["smarty"] . "configs/")
        ->setCompileDir($g_dirs["smarty"] . "templates_c/")
        ->setCacheDir($g_dirs["smarty"] . "cache/");

    $g_comp_template->left_delimiter  = "[{";
    $g_comp_template->right_delimiter = "}]";

    if (!defined("C__RECORD_STATUS__NORMAL"))
    {
        $g_comp_template->assign(
            "system_error",
            'Constant cache not available. Delete the content of your temp/ directory and login to <a href="../">i-doit</a> in order to re-create the cache.'
        );
    } // if

    if (isset($_POST["username"]) && isset($_POST["password"]))
    {

        if (is_null($g_admin_auth) || (isset($g_admin_auth[$_POST["username"]]) && $g_admin_auth[$_POST["username"]] == ""))
        {

            $g_comp_template->assign("error", "Admin login is not configured, yet. <br />Specify an admin password in your config.inc.php (Section: \$g_admin_auth).");
        }
        else
        {
            if (isset($g_admin_auth[$_POST["username"]]) && $g_admin_auth[$_POST["username"]] == $_POST["password"])
            {
                $_SESSION["logged_in"] = true;
                $_SESSION["username"]  = $_POST["username"];
            }
            else
            {
                $g_comp_template->assign('error', 'Error logging in: <strong>Username or password incorrect!</strong><br />Login credentials are defined in config.inc.php.');
            } // if
        } // if
    } // if
}
catch (Exception $e)
{
    echo $e->getMessage();
    die;
}

try
{
    if (file_exists("src/functions.inc.php"))
    {
        include_once("src/functions.inc.php");
    } // if

    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"])
    {
        if (!@include_once($g_absdir . "/src/bootstrap.inc.php")) die("Error loading file: " . $g_absdir . "/src/bootstrap.inc.php");

        $g_comp_template->assign('version', $g_product_info);

        if (isset($_GET["req"]))
        {
            $_GET["req"] = str_replace(chr(0), '', addslashes($_GET["req"]));
            if (file_exists("src/" . $_GET["req"] . ".inc.php"))
            {
                // Process requests
                include_once("src/" . $_GET["req"] . ".inc.php");
            }

            if (file_exists("templates/pages/" . $_GET["req"] . ".tpl"))
            {
                // Include template.
                $g_comp_template->assign("request", "pages/" . $_GET["req"] . ".tpl");
            } // if
        } // if
    }
    else
    {
        if (php_sapi_name() == 'cli' && $argc > 1)
        {
            if (!@include_once($g_absdir . "/src/bootstrap.inc.php")) die("Error loading file: " . $g_absdir . "/src/bootstrap.inc.php");

            include_once('../src/version.inc.php');
            include_once('cli.inc.php');
            die;
        }
        else
        {
            $g_comp_template->assign('loginAction', '?' . str_replace('logout', '', $_SERVER['QUERY_STRING']));

            /* Logout i-doit session for not getting in any session conflicts */
            $l_session = isys_component_session::instance();
            $l_session->logout();

            $g_comp_template->assign("request", "pages/login.tpl");
        }
    } // if
}
catch (InvalidArgumentException $e)
{
    ;
}
catch (Exception $e)
{
    $g_comp_template->assign("system_error", $e->getMessage());
} // try

try
{
    // Display content.
    $g_comp_template->setTemplateDir(__DIR__ . '/templates/')
        ->display('index.tpl');
}
catch (Exception $e)
{
    echo $e->getMessage();
} // try