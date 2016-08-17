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
 * Smarty plugin for file upload
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stückn <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_file extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            "p_strAccept"
        ];
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
     * General Params:
     *   $p_params["p_strAccept"]     => comma seperated list of accepted mime types
     *   $p_params["p_bDisabled"]     => disable
     *   $p_params["p_strName"]       => name
     *   $p_params["p_strSize"]       => size
     *   $p_params["p_strStyle"]      => set the style
     *   $p_params["p_strClass"]      => set the class
     *   $p_params["p_strTitle"]      => title for e.g. tooltip
     *   $p_params["p_Tab"]           => tabindex
     *   $p_params["p_strOnFocus"]    => onfocus handler
     *   $p_params["p_strOnClick"]    => onclick handler
     *   $p_params["p_strMouseOver"]  => onmouseover handler
     *   $p_params["p_strMouseDown"]  => onmousedown handler
     *   $p_params["p_strOnKeyPress"] => onkeypress handler
     *
     * Input specific params:
     *   $p_params["p_strInfoIconError"]  => error message
     *   $p_params["p_strInfoIconInfo"]   => info message
     *   $p_params["p_strInfoIconHelp"]   => help message
     *   $p_params["p_strError"]          => error flag (1 or 0)
     *   $p_params["p_bInfoIconDisabled"] => disable the InfoIcon
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Dennis Stücken <dstuecke@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_file";
        $this->m_strPluginName  = $p_param["name"];

        $p_param["p_strClass"] = "input input-file " . $p_param["p_strClass"];

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        return $this->getInfoIcon(
            $p_param
        ) . "<input " . $p_param["name"] . " " . "type=\"file\" " . $p_param["p_strAccept"] . " " . $p_param["p_bDisabled"] . " " . $p_param["p_strName"] . " " . $p_param["p_strSize"] . " " . $p_param["p_strStyle"] . " " . $p_param["p_strClass"] . " " . $p_param["p_nSize"] . " " . $p_param["p_strTitle"] . " " . $p_param["p_Tab"] . " " . $p_param["p_strOnFocus"] . " " . $p_param["p_strOnClick"] . " " . $p_param["p_strMouseDown"] . " " . $p_param["p_onKeyDown"] . " />";
    } // function
} // class