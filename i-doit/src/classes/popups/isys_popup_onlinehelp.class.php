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
 * Online help for categories.
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    Niclas Potthast <npotthast@i-doit.org> - 2005-11-03
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_popup_onlinehelp extends isys_component_popup
{
    /**
     * Handles SMARTY request for online-help browser.
     *
     * @global  array                   $g_dirs
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Andre Woesten <awoesten@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs;

        // Set dimensions of browser.
        $this->set_config("width", 600);
        $this->set_config("height", 450);

        return '<a href="javascript:" class="ml5" onClick="' . $this->process(
            "?mod=cmdb&popup=onlinehelp&helpFile=" . $p_params["p_strHelpFile"],
            true
        ) . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/infoicon/help.png" alt="" />' . '</a>';
    } // function

    /**
     * Handles module request for online-help browser.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template&
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_session;

        $l_tplpopup = $p_modreq->get_template();

        if (isset($_GET["helpFile"]))
        {
            $l_tplpopup->assign("file_body", "popup/help.tpl")
                ->assign("help_data", "popup/help/" . $_GET["helpFile"] . "_" . $g_comp_session->get_language() . ".tpl");
        } // if

        return $l_tplpopup;
    } // function
} // class
?>