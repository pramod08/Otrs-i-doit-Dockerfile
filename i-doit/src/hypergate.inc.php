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
 * Hypergate
 * Responsible for login, logout and general tasks
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
global $g_template;

// Login procedure.
if (!isys_application::instance()->session->is_logged_in() && isset($_POST['login_username']))
{
    include_once('login.inc.php');
}
else if (!isys_application::instance()->session->is_logged_in() && isys_settings::get('session.sso.active', false) && isys_settings::get(
        'session.sso.mandator-id',
        '1'
    ) > 0 && ((isset($_SERVER['REDIRECT_REMOTE_USER']) && $_SERVER['REDIRECT_REMOTE_USER'] != '') || (isset($_SERVER['REMOTE_USER']) && $_SERVER['REMOTE_USER'] != ''))
)
{
    include_once('sso.inc.php');
} // if

// Logout.
if (isset($_GET["logout"]))
{
    if (isys_application::instance()->session->is_logged_in())
    {
        isys_application::instance()->session->logout();
        header('Location: ?logout');
    } // if
} // if

/**
 * --------------------------------------------------------------------------------------------------------------------------
 * SHOW LOGIN PAGE IF NOT LOGGED IN
 * --------------------------------------------------------------------------------------------------------------------------
 */

// If not logged in, show login dialog, otherwise forward to main include (i-doit.inc.php).
if (!isys_application::instance()->session->is_logged_in())
{
    if (!isys_settings::get('system.devmode', false))
    {
        global $g_product_info;

        // Check for i-doit code / database version conflicts.
        $g_idoit      = new isys_component_dao_idoit(isys_application::instance()->database_system);
        $l_db_version = $g_idoit->get_version();

        if ($l_db_version != "" && $l_db_version != $g_product_info["version"] && $_GET["load"] != "update" && !isset($_POST["login_submit"]))
        {
            isys_glob_display_error(
                "The version of your i-doit database does not match the version of your program code. Please update your databases to <strong>i-doit " . $g_product_info["version"] . "</strong> using the <a href=\"" . isys_application::instance(
                )->www_dir . "updates\">updater</a> or revert/update your i-doit source code to version " . $l_db_version . ".<br /><br />System Database Version: " . $l_db_version . "<br />Source Code Version: " . $g_product_info["version"]
            );
            die;
        } // if
    } // if

    // Check for session timeouts.
    if ($_SESSION["session_data"]["isys_user_session__isys_obj__id"] > 0 && empty($l_error))
    {
        $l_login_header = "i-doit session manager";
        $l_error        = "Your session timed out! (<strong>" . isys_component_session::instance()
                ->get_session_time() . "</strong> seconds) <br />Login again, please.";
    } // if

    // User is not logged in.
    isys_application::instance()->template->assign("bloggedIn", "false")
        ->assign('showAdminCenterLink', $GLOBALS["g_admin_auth"]["admin"]);

    // Destroy session, because the login attempt failed, or session timed out.
    isys_application::instance()->session->destroy();

    if (isset($l_error))
    {
        if ($l_login_header)
        {
            isys_application::instance()->template->assign("login_header", $l_login_header);
        } // if

        isys_application::instance()->template->assign("login_error", str_replace("'", "\'", $l_error))
            ->display($g_template["start_page"]); // Display error.

        die;
    }
    else
    {
        $g_template["start_page"] = "main.tpl";
    } // if

    $index_includes = ["contentarea" => "content/login.tpl"];
}
else
{
    /**
     * --------------------------------------------------------------------------------------------------------------------------
     * USER IS LOGGED IN
     * --------------------------------------------------------------------------------------------------------------------------
     */

    /* Restore mandator id on failure */
    if (!isset($_SESSION["user_mandator"]))
    {
        /* If there is no user mandator saved in users session, it could be
           possible that it was unsetted. The
           mandator-ID is restored here. :-) */
        $l_mandator = isys_application::instance()->session->get_current_mandator_as_id();

        if ($l_mandator != null)
        {
            $_SESSION["user_mandator"] = $l_mandator;
        } // if
    }
    else
    {
        global $g_dirs, $g_absdir;

        /* User is not logged in. Do some directory checks: */
        $g_cache_dirs = [
            "temp"           => $g_absdir . "/temp",
            "file upload"    => $g_dirs["fileman"]["target_dir"],
            "image upload"   => $g_dirs["fileman"]["image_dir"],
            "template cache" => $g_absdir . "/src/themes/default/smarty/templates_c"
        ];

        $g_not_writable = [];
        foreach ($g_cache_dirs as $l_dir)
        {
            if (!is_writeable($l_dir))
            {
                $g_not_writable[] = $l_dir;
            }
        }

        if (count($g_not_writable) > 0)
        {
            isys_glob_display_error(
                "Temp/Cache Problem: The apache process is not able to write inside the following " . "temporary i-doit directories: <br /><br />" . implode(
                    ",<br />",
                    $g_not_writable
                ) . "<br /><br />" . "Please provide the appropriate permissions (e.g. \"chmod 777 path\").<br /><br />" . "<button onclick=\"location.reload(true);\">Refresh</button>"
            );
            die;
        }
    }

    /**
     * --------------------------------------------------------------------------------------------------------------------------
     * HANDLE SESSION BASED STUFF
     *  - Checks if a user is logged in and a mandator id is set in session
     * --------------------------------------------------------------------------------------------------------------------------
     */
    if (isys_application::instance()->session->is_logged_in() && isset($_SESSION["user_mandator"]))
    {
        // Assign current mandant name.
        $g_mandator_name = isys_glob_get_mandant_name_as_string($_SESSION["user_mandator"]);

        isys_application::instance()->session->start_dbsession();

        // Load update engine.
        if (isset($_GET["load"]) && $_GET["load"] == "update")
        {
            global $g_absdir;
            include_once('template.inc.php');
            include_once($g_absdir . "/updates/update.inc.php");
            die;
        } // if
    } // if

    // Handle nag screen.
    if (class_exists('isys_module_licence'))
    {
        isys_module_licence::show_nag_screen();
    } // if

    if (!isys_application::instance()->session->get_session_id())
    {
        $g_sessionid = isys_application::instance()->session->get_session_id();
    } // if

    // Read session data.
    $_SESSION["session_data"] = isys_application::instance()->session->get_session_data();

    if (is_array($_SESSION["session_data"]))
    {
        foreach ($_SESSION["session_data"] as $l_key => $l_val)
        {
            if (is_numeric($l_key))
            {
                unset($_SESSION["session_data"][$l_key]);
            } // if
        } // foreach
    } // if

    // Load Event manager.
    $g_mod_event_manager = isys_event_manager::getInstance();

    // Assign navbar template.
    $index_includes['navbar'] = 'content/navbar/main.tpl';

    // User is logged in.
    include_once("i-doit.inc.php");

    // Show navbar.
    isys_component_template_navbar::getInstance()
        ->show_navbar();

    // Assign the collected data.
    global $g_mandator_name;

    isys_application::instance()->template->assign("g_mandant_name", $g_mandator_name)
        ->assignByRef("infobox", isys_component_template_infobox::instance())
        ->assign('menu_width', isys_usersettings::get('gui.leftcontent.width', isys_component_dao_user::C__CMDB__TREE_MENU_WIDTH));
} // if

// Show loaded and initialized modules.
if (isset($_GET['modules']) && defined('C__MODULE__SYSTEM') && isys_auth_system::instance()
        ->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEM')
)
{
    $g_modman->enum();
    $l_modules = $g_modman->modules();
    ksort($l_modules);

    isys_application::instance()->template->assign('init_modules', $g_modman->get_initialized_modules())
        ->assign('modules', $l_modules);

    $index_includes = ["contentbottomcontent" => "content/modules.tpl"];
} // if

/**
 * --------------------------------------------------------------------------------------------------------------------------
 * INITIALIZE SOME TEMPLATE VARIABLES
 * --------------------------------------------------------------------------------------------------------------------------
 */
include_once('template.inc.php');

/**
 * --------------------------------------------------------------------------------------------------------------------------
 * PRINT OUT THE I-DOIT SITE
 * --------------------------------------------------------------------------------------------------------------------------
 */
if (!$g_output_done)
{
    /**
     * @TODO  Consider following structure (instead of deep nesting):
     *
     * if (<bad thing>) {
     *   throw exception
     * }
     *
     * if (<bad thing>) {
     *   throw exception
     * }
     *
     * [...]
     *
     * <do logic at the end>
     */

    if (!empty($g_dirs["smarty"]))
    {
        if (!empty($g_template["start_page"]))
        {
            if (file_exists($g_dirs["smarty"] . "templates/" . $g_template["start_page"]))
            {
                isys_application::instance()->template->display("file:" . $g_dirs["smarty"] . "templates/" . $g_template["start_page"]);

                // Emit signal afterRender.
                isys_component_signalcollection::get_instance()
                    ->emit('system.gui.afterRender');
            }
            else
            {
                isys_glob_display_error("Error: Template " . $g_dirs["smarty"] . "templates/" . $g_template["start_page"] . ' does not exist.');
            } // if
        }
        else
        {
            isys_glob_display_error("Error while displaying template: g_template[start_page] is not set!");
        } // if
    }
    else
    {
        isys_glob_display_error("Error while displaying template: g_dirs[smarty] is empty. This could be a settings or cache problem");
    } // if
} // if