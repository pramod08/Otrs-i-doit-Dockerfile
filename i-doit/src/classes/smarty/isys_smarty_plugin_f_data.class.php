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
 * Smarty plugin for some data ONLY for view mode!!!
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_data extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Method for view-mode. Following parameters are being used:
     *     p_strValue       -> Value to diplay.
     *     default          -> Value to display, if p_strValue is empty.
     *     p_plain          -> If set to true, will return a plain string of the value.
     *     len              -> Limits the value to a given amount of characters.
     *     append           -> The string, that will be appended, if cut of (default "..").
     *     p_strStyle       -> String, which will be loaded into the "style" attribute.
     *
     * + all parameters, which are relevant for the infoicon.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        $l_style = $l_id = $l_class = '';

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if (empty($p_param["p_strValue"]) && !empty($p_param["default"]))
        {
            $p_param["p_strValue"] = $p_param["default"];
        } // if

        $p_param["p_strValue"] = html_entity_decode(stripslashes($p_param["p_strValue"]), null, $GLOBALS['g_config']['html-encoding']);

        if ($p_param["p_plain"])
        {
            return $p_param["p_strValue"];
        } // if

        $this->m_strPluginClass = "f_data";
        $this->m_strPluginName  = $p_param["name"];

        if (isset($p_param["len"]) && $p_param["len"] > 0)
        {
            $l_append = "..";

            if (!empty($p_param["append"]))
            {
                $l_append = $p_param["append"];
            } // if

            $p_param["p_strValue"] = isys_glob_cut_string($p_param["p_strValue"], $p_param["len"], $l_append);
        } // if

        if (!empty($p_param["p_strStyle"]))
        {
            $l_style = ' style="' . $p_param["p_strStyle"] . '"';
        } // if

        if (!empty($p_param["p_strID"]))
        {
            $l_id = ' id="' . $p_param["p_strID"] . '"';
        }
        else if (!empty($p_param["id"]))
        {
            $l_id = ' id="' . $p_param["id"] . '"';
        } // if

        if (!empty($p_param["p_strClass"]))
        {
            $l_class = ' class="' . $p_param["p_strClass"] . '"';
        } // if

        if (!$p_param["p_strValue"] && isset($p_param["default"]))
        {
            $p_param["p_strValue"] = $p_param["default"];
        }

        if ($l_style . $l_id . $l_class)
        {
            return $this->getInfoIcon($p_param) . "<span" . $l_style . $l_id . $l_class . ">" . $p_param["p_strValue"] . "</span>";
        }
        else
        {
            return $this->getInfoIcon($p_param) . $p_param["p_strValue"];
        }
    } // function

    /**
     * Method for edit-mode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        return $this->navigation_view($p_tplclass, $p_param);
    } // function
} // class