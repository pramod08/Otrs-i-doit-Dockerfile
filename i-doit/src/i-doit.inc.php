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
 * i-doit Starter
 *
 * @package     i-doit
 * @subpackage  General
 * @author      i-doit-team
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
try
{
    global $g_comp_template, $g_ajax_calls, $g_dirs, $g_comp_database, $g_config, $g_comp_session;

    // Set default form-action.
    $_SERVER['QUERY_STRING'] = isys_glob_url_remove($_SERVER['QUERY_STRING'], C__GET__AJAX_CALL);

    /**
     * Show default tooltips in the navigation bar.
     *
     * @var $g_bDefaultTooltips bool
     */
    $g_bDefaultTooltips = true;

    // Initialize navbar.
    $g_comp_template_navbar = isys_component_template_navbar::getInstance()
        ->set_save_mode(isys_tenantsettings::get('cmdb.registry.quicksave', 1) ? 'quick' : 'log');

    // Global array for URL parameters which mustn't get deleted.
    $g_arSaveURLParameters = [
        "mNavID",
        "SMARTY_DEBUG",
        "bvMode"
    ];

    /**
     * Include main navigation.
     *
     * @todo  isys_mainnavi need a place in the i-doit structure.
     * @todo  isys_mainnavi is plain and not dynamic.
     */
    if (!$g_ajax)
    {
        include_once $g_dirs['utils'] . 'isys_mainnavi.inc.php';
    } // if

    // Store status array.
    if ($_POST['cmdb_status'])
    {
        $_SESSION['cmdb_status'] = $_POST['cmdb_status'];
    } // if

    if (!isset($_SESSION['cmdb_status']) || !is_array($_SESSION['cmdb_status']))
    {
        // Set default status.
        $_SESSION['cmdb_status'] = [
            0,
            0,
            0
        ];
    } // if

    // Write cRecStatusListView to session.
    if (isys_glob_get_param("cRecStatus"))
    {
        $_SESSION['cRecStatusListView'] = isys_glob_get_param("cRecStatus");
    }
    else
    {
        // Is there a value in the session?
        if (!isset($_SESSION['cRecStatusListView']))
        {
            // Set default value
            $_SESSION['cRecStatusListView'] = C__RECORD_STATUS__NORMAL;
        }
        else if ($_SESSION['cRecStatusListView'] > C__RECORD_STATUS__DELETED)
        {
            if ($_GET[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_CATEGORY)
            {
                $_SESSION['cRecStatusListView'] = C__RECORD_STATUS__NORMAL;
            }
            else if ($_GET[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_OBJECT && $_SESSION['cRecStatusListView'] != C__RECORD_STATUS__TEMPLATE)
            {
                $_SESSION['cRecStatusListView'] = C__RECORD_STATUS__NORMAL;
            } // if
        } // if
    } // if

    // CMDB-SPECIFIC - Set object-type id, if not existent in _GET parameters.
    if (empty($_GET[C__CMDB__GET__OBJECTTYPE]) && isset($_GET[C__CMDB__GET__OBJECT]))
    {
        if (class_exists("isys_cmdb_dao"))
        {
            $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

            $_GET[C__CMDB__GET__OBJECTTYPE] = $l_dao_cmdb->get_objTypeID($_GET[C__CMDB__GET__OBJECT]);
            unset($l_dao_cmdb);
        } // if
    } // if

    /**
     * Include Applicaiton controller
     */
    include_once('application.inc.php');
}
catch (isys_exception_general $e)
{
    isys_glob_display_error($e->getMessage());
    die();
} // try