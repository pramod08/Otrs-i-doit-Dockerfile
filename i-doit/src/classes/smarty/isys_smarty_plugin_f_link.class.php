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
 * smarty plugin: link
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stückn <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_smarty_plugin_f_link extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return [];
    } // function

    /**
     * View mode returns the content value.
     *
     * @global  array                   $g_dirs
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        global $g_dirs;

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if (isys_glob_is_edit_mode() || (isset($p_param["p_editMode"]) && $p_param["p_editMode"]))
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        return $this->getInfoIcon($p_param) . isys_helper_link::create_anker(
            $p_param["p_strValue"],
            $p_param["p_strTarget"],
            '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" alt="Link" class="vam" /> <span class="vam">',
            '</span>'
        );
    } // function

    /**
     * Parameters are given in an array $p_param:
     *     Basic parameters
     *         p_strAccept          -> comma seperated list of accepted mime types
     *         p_bDisabled          -> disable
     *         p_strName            -> name
     *         p_strSize            -> size
     *         p_strPlaceholder     -> HTML5 Placeholder attribute
     *         p_strStyle           -> set the style
     *         p_strClass           -> set the class
     *         p_strTitle           -> title for e.g. tooltip
     *         p_Tab                -> tabindex
     *         p_strOnFocus         -> onfocus handler
     *         p_strOnClick         -> onclick handler
     *         p_strMouseOver       -> onmouseover handler
     *         p_strMouseDown       -> onmousedown handler
     *         p_strOnKeyPress      -> onkeypress handler
     *
     *     Input specific params
     *         p_strInfoIconError   -> error message
     *         p_strInfoIconInfo    -> info message
     *         p_strInfoIconHelp    -> help message
     *         p_strError           -> error flag (1 or 0)
     *         p_bInfoIconDisabled  -> disable the InfoIcon
     *
     * @param   isys_component_template $p_tplclass
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

        $this->m_strPluginClass = "f_link";
        $this->m_strPluginName  = $p_param["name"];

        if (empty($p_param["p_strClass"]))
        {
            $p_param["p_strClass"] = "input";
        } // if

        $p_param["p_strValue"] = str_replace("\\\\", "\\", $p_param["p_strValue"]);

        if (is_null($p_param['p_nSize']))
        {
            $p_param['p_nSize'] = '65';
        } // if

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        if ($p_param["p_bInvisible"] == true)
        {
            $l_input_type = "hidden";
        }
        else
        {
            $l_input_type = "text";
        } // if

        $l_description_tag = '';

        if (!empty($p_param['p_description']))
        {
            $l_description_tag = '<p class="mt5 ml20" style="font-size: smaller;">' . _L($p_param['p_description']) . '</p>';
        } // if

        if (isset($p_param['p_strPlaceholder']))
        {
            $p_param['p_strPlaceholder'] = ' placeholder="' . _L($p_param['p_strPlaceholder']) . '" ';
        }
        else
        {
            $p_param['p_strPlaceholder'] = ' placeholder="http://" ';
        } // if

        return $this->getInfoIcon(
            $p_param
        ) . '<input ' . $p_param['name'] . ' type="' . $l_input_type . '" ' . $p_param['p_strID'] . ' ' . $p_param['p_strTitle'] . ' ' . $p_param['p_strClass'] . ' ' . $p_param['p_bDisabled'] . ' ' . $p_param['p_bReadonly'] . ' ' . $p_param['p_strStyle'] . ' ' . $p_param['p_strValue'] . ' ' . $p_param['p_strPlaceholder'] . ' ' . $p_param['p_strTab'] . ' ' . $p_param['p_nSize'] . ' ' . $p_param['p_nMaxLen'] . ' ' . $p_param['p_onMouseOver'] . ' ' . $p_param['p_onMouseOut'] . ' ' . $p_param['p_onClick'] . ' ' . $p_param['p_onKeyPress'] . ' ' . $p_param['p_onKeyDown'] . '/>' . $l_description_tag;
    } // function
} // class