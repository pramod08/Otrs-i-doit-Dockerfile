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
 * For "Single Sign On" (SSO). This file is included by hypergate.inc.php - if the user is not logged in, yet, and SSO is activated
 *
 * Checked via:
 *  isys_settings::get('session.sso.active', false) &&
 *  isys_settings::get('session.sso.mandator-id', '1') > 0 && (isset($_SERVER['REMOTE_USER']) && $_SERVER['REMOTE_USER'] != '')
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
if (isset($_GET['logout']))
{
    // Display SSO-Message after logout and prevent endless redirections.
    $l_error = "Re-Login with user: <a href='?'>" . ($_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] : $_SERVER['REDIRECT_REMOTE_USER']) . "</a>";
}
else
{
    $l_session = isys_application::instance()->session;

    if ($l_session->connect_mandator(isys_settings::get('session.sso.mandator-id')))
    {
        if (is_object(isys_application::instance()->database))
        {
            $l_sso_user = ($_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] : $_SERVER['REDIRECT_REMOTE_USER']);

            if (strstr($l_sso_user, '\\'))
            {
                $l_sso_user = substr($l_sso_user, strpos($l_sso_user, '\\') + 1);
            }
            elseif (strstr($l_sso_user, '@'))
            {
                $l_sso_user = substr($l_sso_user, 0, strpos($l_sso_user, '@'));
            } // if

            if ($l_sso_user)
            {
                $l_dao_user  = isys_component_dao_user::instance(isys_application::instance()->database);
                $l_userarray = $l_dao_user->get_user(null, $l_sso_user)
                    ->__to_array();

                if ($l_userarray)
                {
                    $l_session->delete_current_session();
                    $l_session->start_dbsession();

                    if (!$l_session->login(isys_application::instance()->database, $l_sso_user, $l_userarray['isys_cats_person_list__user_pass'], true, true))
                    {
                        $l_error = "Single sign on login failed. Either username (" . $l_sso_user . "), password or mandator (" . isys_settings::get(
                                'session.sso.mandator-id'
                            ) . ") information is not correct.";
                    }
                    else
                    {
                        // Check and populate current licence.
                        if (class_exists('isys_module_licence'))
                        {
                            $l_licence = new isys_module_licence();
                            $l_licence->verify();
                        } // if

                        header('Location: index.php');
                    } // if

                }
                else
                {
                    $l_error = "Single-Sign-On User '" . $l_sso_user . "' not found in i-doit! Login manually.";

                    // Clear all sessions, because this login failed!
                    $l_session->logout();
                } // if
            } // if
        } // if
    } // if
} // if