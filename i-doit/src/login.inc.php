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
 * Login
 *
 * This file is included when $_POST['login_username'] is set.
 *
 *    Happends when the user clicked on the "Login" button
 *     AND
 *    when he/she selected a tenant afterwards.
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
global $g_comp_session, $g_comp_database, $g_comp_template, $g_comp_template_language_manager, $g_template;

try
{
    /**
     * Initialize CMDB Module, because it is needed for retrieving login users
     */
    include_once(__DIR__ . '/classes/modules/cmdb/init.php');

    // Load mandants template, because this is an ajax request.
    if (isset($_POST['login_submit']))
    {
        $g_template['start_page'] = 'content/mandants.tpl';
    } // if

    // Check if username and password was entered.
    if (empty($_POST['login_username']))
    {
        $l_error = 'No username specified!<br />';
    }
    else if (empty($_POST['login_password']))
    {
        $l_error = 'No password specified!<br />';
    }
    else
    {
        // Check if mandator ID is set.
        if (isset($_POST['login_mandant_id']))
        {

            // Instantiate $g_comp_database.
            if ($g_comp_session->connect_mandator($_POST['login_mandant_id']))
            {
                // Insert Session Entry to database.
                if ($g_comp_session->start_dbsession() != null)
                {
                    $g_comp_session->delete_expired_sessions();

                    // Do the real login.
                    $l_loginres = $g_comp_session->login(
                        $g_comp_database,
                        $_POST['login_username'],
                        $_POST['login_password'],
                        false // Write new userID to session
                    );

                    if ($l_loginres)
                    {
                        unset($_GET["logout"]);

                        // Prepare module request, because a module dao is needed in ->checkLicense method and $g_modman->init

                        // Initialize module manager.
                        isys_module_manager::instance()
                            ->init(isys_module_request::get_instance());

                        /* Check if licence check exists */
                        if (class_exists('isys_ajax_handler_licence_check'))
                        {
                            $l_lic = new isys_ajax_handler_licence_check($_GET, $_POST);
                            $l_lic->checkLicense();
                        } // if

                        // Delete temp tables.
                        try
                        {
                            $l_dao_tables = new isys_component_dao_table($g_comp_database);
                            $l_dao_tables->clean_temp_tables();
                        }
                        catch (isys_exception_dao $l_e)
                        {
                            ; // Ignore it...
                        } // try
                    }
                    else
                    {
                        $l_error = "Login attempt failed. Please try again.";
                    } // if
                }
                else
                {
                    $l_error = "Could not add session to database.";
                } // if
            }
            else
            {
                $l_error = "Could not connect to mandator database.";
            } // if
        }
        else
        {
            // PREPARE MANDATOR LIST FOR LOGIN

            // This block is executed after the initial login. User entered username password and we fetch the available mandantors for him now.
            $l_mandator_data = $g_comp_session->fetch_mandators($_POST["login_username"], $_POST["login_password"]);

            if (count($l_mandator_data) > 0)
            {
                $l_mandants           = [];
                $l_preferred_language = null;

                if (count($l_mandator_data) === 1)
                {
                    $g_comp_template->assign('directlogin', true);
                } // if

                foreach ($l_mandator_data as $l_mandator)
                {
                    $l_mandants[$l_mandator['id']] = $l_mandator['title'];
                    $l_user_id                     = $l_mandator['user_id'];
                    if ($l_preferred_language === null)
                    {
                        $l_preferred_language = $l_mandator['preferred_language'];
                    } // if
                } // foreach

                // Show available mandators in SELECT and disable text fields.
                $g_comp_template->assign("mandant_options", $l_mandants)
                    ->assign("languages", $g_comp_template_language_manager->fetch_available_languages())
                    ->assign('preferred_language', $l_preferred_language);
            } // if

            $l_session_errors = $g_comp_session->get_errors();

            if (count($l_session_errors) > 0 && count($l_mandator_data) <= 0)
            {
                // Removed: Check for rights -> isys_rs_system
            }
            else
            {
                if (is_null($l_mandator_data))
                {
                    $l_error = "No mandators found in system database!";
                }
                else if (count($l_mandator_data) == 0)
                {
                    $l_error = "Invalid username or password!";

                    // Clear all sessions, because this login failed!
                    $g_comp_session->logout();
                } // if
            } // if

            if (!isset($l_error))
            {
                // If no error occurred - load clients.
                $g_comp_template->fetch($g_template["start_page"], null, null, null, true);
                exit;
            } // if
        } // if
    } // if
}
catch (ErrorException $e)
{
    if (strlen($e->getMessage()) > 100)
    {
        $l_error = 'Login failed: ' . isys_glob_cut_string($e->getMessage(), 100) . '...' . substr($e->getMessage(), -100);
    }
    else
    {
        $l_error = 'Login failed: ' . $e->getMessage();
    }

    isys_application::instance()->logger->addError($e->getMessage());
}