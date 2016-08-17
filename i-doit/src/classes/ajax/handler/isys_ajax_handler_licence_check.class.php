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
 * AJAX
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_ajax_handler_licence_check extends isys_ajax_handler
{

    public function init()
    {
        global $g_comp_template;
        global $g_comp_database_system;
        global $g_comp_database;
        global $g_config;

        if (class_exists("isys_module_licence"))
        {
            $l_licence = new isys_module_licence();

            try
            {
                $l_licences = $l_licence->get_installed_licences($g_comp_database_system);

                if (!is_null($l_licences))
                {
                    if (count($l_licences) > 0)
                    {
                        foreach ($l_licences as $l_lic)
                        {
                            $l_licence->check_licence($l_lic["licence_data"], $g_comp_database);
                        }
                    }
                }
                else
                {
                    throw new isys_exception_licence(_L("LC__LICENCE__NO_LICENCE"), 1);
                }

            }
            catch (isys_exception_licence $e)
            {
                // Try: isys_application::instance()->www_path
                $l_html = $e->getMessage() . " (" . $e->get_errorcode(
                    ) . ")<br />" . "<a href=\"" . $g_config["www_dir"] . "index.php?moduleID=" . C__MODULE__SYSTEM . "&handle=licence_overview\">Zur Lizenzverwaltung</a>";

                $g_comp_template->assign("error_topic", "Lizenzen")
                    ->assign("g_error", $l_html)
                    ->display("exception.tpl");
            } // try
        } // if

        return true;
    }

    public function checkLicense()
    {
        if (class_exists("isys_module_licence"))
        {
            $l_licence = new isys_module_licence();
            $l_licence->verify();
        }
    }
}