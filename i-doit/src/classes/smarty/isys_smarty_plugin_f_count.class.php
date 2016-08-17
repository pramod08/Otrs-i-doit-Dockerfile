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
 * Smarty plugin for numerical input fields with arrows to change the value.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_count extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     * @author  André Wösten <awoesten@i-doit.org>
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
    } // function

    /**
     * Returns the content value.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if ($p_param["p_bEditMode"] == "1")
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        return $this->getInfoIcon($p_param) . html_entity_decode(stripslashes($p_param["p_strValue"]));
    } // function

    /**
     *
     * @global  array                   $g_dirs
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        global $g_dirs;

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $l_strID      = "";
        $l_input_type = 'text';

        $this->m_strPluginClass = "f_count";
        $this->m_strPluginName  = $p_param["name"];

        // Set a default width, this can be overwritten with the CSS classes "small", "verysmall", etc.
        $p_param["p_strStyle"] = 'width:100px; ' . $p_param["p_strStyle"];

        // Default css class.
        $p_param['p_strClass'] = 'input ' . $p_param['p_strClass'];

        // Standard ID
        if (empty($p_param["p_strID"]))
        {
            $p_param["p_strID"] = $p_param["name"];
            $l_strID            = $p_param["p_strID"];
        } // if

        // Standard value is 1
        if (empty($p_param["p_strValue"]))
        {
            $p_param["p_strValue"] = 1;
        } // if

        // Standard size
        if (empty($p_param["p_nSize"]))
        {
            $p_param["p_nSize"] = "3";
        } // if

        // Is the error flag set?
        if (!empty($p_param["p_strError"]))
        {
            $p_param["p_strError"] = $p_param["p_strError"] . "Error";
        } // if

        $l_onchange            = $p_param["p_onChange"];
        $p_param["p_onChange"] = "checkNaN($('" . $l_strID . "')); " . ($p_param["p_bNeg"] != "1" ? "checkNeg($('" . $l_strID . "')); " : " ") . $p_param["p_onChange"];

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        if ($p_param["p_bInvisible"] == true)
        {
            $l_input_type = "hidden";
        } // if

        if ($p_param["p_bNeg"] == "1")
        {
            $l_bNeg = "true";
        }
        else
        {
            $l_bNeg = "false";
        } // if

        return $this->getInfoIcon($p_param) . //decrease counter
        '<span class="mouse-pointer bold" onClick="$(\'' . $l_strID . '\').setValue(changeCount($F(\'' . $l_strID . '\'), \'down\', ' . $l_bNeg . ')).simulate(\'change\');' . $l_onchange . '" ' . $p_param["p_onAlter"] . '><img src="' . $g_dirs["images"] . 'icons/dec_arr.png" />&nbsp;</span>' . // The input-field itselt.
        '<input ' . $p_param["name"] . ' type="' . $l_input_type . '" ' . $p_param["p_strID"] . ' ' . $p_param["p_strTitle"] . ' ' . $p_param["p_strClass"] . ' ' . $p_param["p_bDisabled"] . ' ' . $p_param["p_bReadonly"] . ' ' . $p_param["p_strStyle"] . ' ' . $p_param["p_strValue"] . ' ' . $p_param["p_strTab"] . ' ' . $p_param["p_nSize"] . " " . $p_param["p_nMaxLen"] . " " . $p_param["p_onMouseOver"] . " " . $p_param["p_onMouseOut"] . " " . $p_param["p_onChange"] . " " . $p_param["p_onClick"] . " " . $p_param["p_onKeyPress"] . " " . $p_param["p_onKeyUp"] . " " . $p_param["p_onKeyDown"] . " " . $p_param["p_additional"] . " " . $p_param['p_dataIdentifier'] . " />" . //increase counter
        '<span class="mouse-pointer bold" onClick="$(\'' . $l_strID . '\').setValue(changeCount($F(\'' . $l_strID . '\'), \'up\')).simulate(\'change\'); ' . $l_onchange . '" ' . $p_param["p_onAlter"] . '>&nbsp;<img src="' . $g_dirs["images"] . 'icons/inc_arr.png" /></span>';
    } // function
} // class