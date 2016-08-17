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
 * my-doit Area
 *
 * @package    i-doit
 * @subpackage General
 * @author     Andre Woesten <awoesten@i-doit.org> - 2006-05-06
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

$l_db = isys_application::instance()->database;

$l_dao       = new isys_component_dao($l_db);
$l_settingID = isys_component_dao_user::instance($l_db)
    ->get_user_setting_id();

if ($l_settingID != null)
{
    if (isset($_POST["mydoitAction"]))
    {
        try
        {
            $l_action = $_POST["mydoitAction"];
            if ($l_action == "add")
            {

                // Add an entry.
                $l_modman = isys_module_manager::instance();
                $l_actmod = $l_modman->get_active_module();

                if ($l_actmod)
                {
                    // Return active module register entry.
                    $l_modreg = $l_modman->get_by_id($l_actmod);

                    if ($l_modreg->is_initialized())
                    {
                        // The module data includes the module title.
                        $l_moddata = $l_modreg->get_data();

                        // Asking module for its breadcrumb navigation.
                        $l_modobj = $l_modreg->get_object();

                        if (method_exists($l_modobj, "mydoit_get"))
                        {
                            $l_out  = null;
                            $l_link = null;

                            if ($l_modobj->mydoit_get($l_out, $l_link))
                            {
                                // Finish link.
                                $l_link = isys_glob_build_url($l_link);

                                // Build text.
                                $l_out = '[' . _L($l_moddata['isys_module__title']) . '] ' . implode('&nbsp;&gt;&gt;&nbsp;', $l_out);

                                // Good - insert into database now.
                                $l_q = "INSERT INTO isys_user_mydoit (isys_user_mydoit__id, isys_user_mydoit__isys_user_setting__id, isys_user_mydoit__title, isys_user_mydoit__link, isys_user_mydoit__date_added)
									VALUES(DEFAULT, '" . $l_settingID . "', '" . $l_db->escape_string($l_out) . "', '" . $l_db->escape_string(
                                        urlencode($l_link)
                                    ) . "', NOW());";

                                if ($l_dao->update($l_q) && $l_dao->apply_update())
                                {
                                    // Good. now show mydoIT-Area automatically.
                                    isys_application::instance()->template->assign("mydoitShow", 1);
                                } // if
                            }
                            else
                            {
                                throw new Exception(_L('LC__MYDOIT__ERROR_NO_SUPPORT'));
                            } // if
                        } // if
                    } // if
                } // if
            }
            elseif ($l_action == "delete")
            {
                // Delete selected entries.
                if (isset($_POST["mydoitSelection"]))
                {
                    foreach ($_POST["mydoitSelection"] as $l_selID => $l_selStatus)
                    {
                        $l_q = "DELETE FROM isys_user_mydoit WHERE isys_user_mydoit__id='" . $l_selID . "' AND isys_user_mydoit__isys_user_setting__id='" . $l_settingID . "';";

                        if ($l_dao->update($l_q) && $l_dao->apply_update())
                        {
                            isys_application::instance()->template->assign("mydoitShow", 1);
                        } // if
                    } // foreach
                } // if
            } // if
        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage());
        } // try
    } // if

    /* Query database for bookmarks */
    $l_q          = "SELECT * FROM isys_user_mydoit " . "WHERE isys_user_mydoit__isys_user_setting__id='" . $l_settingID . "';";
    $l_res        = $l_dao->retrieve($l_q);
    $l_bookmarks  = [];
    $l_nbookmarks = $l_res->num_rows();
    if ($l_res && ($l_nbookmarks > 0))
    {
        /* Build up SMARTY Array with bookmarks */
        while ($l_row = $l_res->get_row())
        {
            $l_bookmarks[$l_row["isys_user_mydoit__id"]] = [
                "text" => $l_row["isys_user_mydoit__title"],
                "link" => urldecode($l_row["isys_user_mydoit__link"])
            ];
        }
    }

    isys_application::instance()->template->assign(
        "mydoit",
        [
            "bookmarkList"  => $l_bookmarks,
            "bookmarkCount" => $l_nbookmarks
        ]
    );
}

/* my-tasks area */
require_once("my-tasks.inc.php");
require_once("my-search.inc.php");

/**
 * CMDB Status
 */
if (isys_tenantsettings::get('system.mydoit.show_filter', 1))
{
    include_once("cmdb-status.inc.php");
} // if