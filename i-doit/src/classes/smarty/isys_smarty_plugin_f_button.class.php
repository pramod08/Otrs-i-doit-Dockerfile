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
 * Smarty plugin for buttons
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_button extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            "p_strValue"
        ];
    } // function

    /**
     * Parameters are given in an array $p_param[].
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_button";
        $this->m_strPluginName  = $p_param["name"];

        if ($p_param["p_bInvisible"] == "1")
        {
            return '';
        } // if

        if ($p_param["p_bEditMode"] == "1")
        {
            $this->m_bEditMode = true;
        } // if

        $p_param["p_strClass"] = $p_param["p_strClass"] . ' btn';

        if (empty($p_param["p_strStyle"]))
        {
            $p_param["p_strStyle"] = "margin-right:5px;";
        } // if

        // If button is disabled empty javascript and change color
        if ($p_param["p_bDisabled"] == "0")
        {
            $l_disabled = "";
        }
        else if ($p_param["p_bDisabled"] == "1" || $this->m_bEditMode == false)
        {
            // @todo  Check if the CSS class "disabled" is still necessary - the button styling should still work (via the attribute).
            $p_param["p_strClass"] .= " disabled";
            $l_disabled = "disabled=\"disabled\"";
        }
        else
        {
            $l_disabled = '';
        } // if

        if ($p_param["type"] == "f_submit")
        {
            $p_param["type"] = "submit";
        } // if

        if ($p_param["type"] == "f_button")
        {
            $p_param["type"] = "button";
        } // if

        $l_value = _L($p_param["p_strValue"]);

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        //show InfoIcon
        $p_param["p_bInfoIconSpacer"] = "0";

        $l_icon = '';

        if (isset($p_param['icon']))
        {
            $l_icon = '<img src="' . $p_param['icon'] . '" />';
        } // if

        return $this->getInfoIcon($p_param) .
            "<button " . $p_param["name"] . " " .
                $p_param["type"] . " " . $p_param["p_strAccessKey"] . " " . $p_param["p_strID"] . " " .
                $p_param["p_strTitle"] . " " . $p_param["p_strClass"] . " " . $p_param["p_strStyle"] . " " .
                $p_param["p_onClick"] . " " . $p_param["p_onMouseOver"] . " " . $p_param["p_onMouseOut"] . " " .
                $p_param["p_onMouseMove"] . " " . $p_param['p_strValue'] . " " . $l_disabled . '>' . $l_icon .
                (!empty($l_value) ? '<span' . (empty($l_icon) ? '' : ' class="ml5"') . '>' . $l_value . '</span>' : '') .
            '</button>';
    } // function

    /**
     * Wrapper for the navigation_view.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        $this->m_bEditMode = true;

        return $this->navigation_view($p_tplclass, $p_param);
    } // function
} // class