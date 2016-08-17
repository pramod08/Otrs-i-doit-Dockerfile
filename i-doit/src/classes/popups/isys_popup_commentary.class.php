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
 * Popup class for commentaries for saving changes into the LogBook
 *
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Niclas Potthast <npotthast@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_commentary extends isys_component_popup
{
    /**
     * @return string
     * @global                        $g_dirs
     * @global                        $g_config
     *
     * @param isys_component_template & $p_tplclass
     * @param                         $p_params
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2005-11-03
     * @desc    Handles SMARTY request for commentary popup
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        $l_url = isys_helper_link::create_url(
            [
                'mod'      => 'cmdb',
                'popup'    => 'commentary',
                'editMode' => C__EDITMODE__ON
            ]
        );

        $this->set_config("width", 490);
        $this->set_config("height", 260);
        $this->set_config("scrollbars", "no");

        $p_params["p_onClick"] = $this->process($l_url, true);
        $p_params["type"]      = "f_button";

        //use smarty method for button
        $l_objButton = new isys_smarty_plugin_f_button();

        if (isys_glob_get_param("editMode") == C__EDITMODE__ON)
        {
            return $l_objButton->navigation_edit($p_tplclass, $p_params);
        } // if

        return $l_objButton->navigation_view($p_tplclass, $p_params);
    } // function

    /**
     * @global                    $g_comp_database
     *
     * @param isys_module_request $p_modreq
     *
     * @return isys_component_template&
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Create location browser popup.
        $p_modreq->get_template()
            ->display("popup/commentary.tpl");

        die();
    } // function
} // class