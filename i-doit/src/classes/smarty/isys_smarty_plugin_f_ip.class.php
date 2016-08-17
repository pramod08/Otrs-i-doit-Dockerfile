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
 * Smarty plugin for text input fields.
 *
 * @deprecated  Use a normal isys_smarty_plugin_f_text instead!
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_ip extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     * @author André Wösten <awoesten@i-doit.org>
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
    } // function

    /**
     * Returns the content value.
     *
     * @param   isys_component_template & $p_tplclass
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

        if ($p_param['p_bInvisible'] == true)
        {
            return '';
        } // if

        if (is_null($p_param['p_strValue']) && isset($p_param['default']))
        {
            $p_param['p_strValue'] = $p_param['default'];
        } // if

        if ($p_param['p_bEditMode'] == '1')
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        return $this->getInfoIcon($p_param) . '<span>' . stripslashes(html_entity_decode(stripslashes($p_param['p_strValue']))) . '</span>';
    } // function

    /**
     * Display in edit mode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        $l_strOut = '';

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $p_param['p_nMaxLen'] = '3';

        $this->m_strPluginClass = "f_ip";
        $this->m_strPluginName  = $p_param["name"];

        $l_class_iterator = null;

        // This is necessary for multi edit
        if (isset($p_param["classIterator"]))
        {
            $l_class_iterator = $p_param["p_strClass"];
        } // if

        // Default css class.
        $l_strClasses = $p_param["p_strClass"] = 'input ' . $p_param["p_strClass"];

        // Is the error flag set?
        if (!empty($p_param["p_strError"]))
        {
            $p_param["p_strError"] = $p_param["p_strError"] . "Error";
        } // if

        // Unescape and strip the value.
        $p_param["p_strValue"] = stripslashes($p_param["p_strValue"]);
        $p_param["p_strValue"] = htmlentities(isys_glob_unescape($p_param["p_strValue"]), null, $GLOBALS['g_config']['html-encoding']);

        // IP Type (ipv4, ipv6).
        $l_type  = $p_param["p_strType"];
        $l_value = $p_param["p_strValue"];

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        // Show InfoIcon.
        $l_strOut .= $this->getInfoIcon($p_param);

        switch ($l_type)
        {
            case "ipv6":
                $l_strOut .= "<input " . "name=\"" . $this->m_strPluginName . "\" " . "type=\"text\" " . "value=\"" . $l_value . "\" " . "id=\"" . $this->m_strPluginName . "\" " . $p_param["p_strTitle"] . " " . $p_param["p_strClass"] . " " . $p_param["p_bDisabled"] . " " . $p_param["p_bReadonly"] . " " . $p_param["p_strTab"] . " " . $p_param["p_onMouseOver"] . " " . $p_param["p_onMouseOut"] . " " . $p_param["p_onChange"] . " " . $p_param["p_onClick"] . " " . $p_param["p_additional"] . " />";
                break;

            case "ipv4":
            default:
                // Explode address mask.
                $l_value = explode(".", $l_value);

                for ($i = 0;$i <= 3;$i++)
                {
                    $l_keyup = "this.value = this.value.replace(/\D/,''); " . "if (this.value > 255) { this.value = '255'; } " . "if (this.value < 0) { this.value = '1'; } ";

                    // This is a more "user-friendly" way of navigating through the IP-inputs.
                    $l_keydown = (($i < 3) ? "if (event.keyCode == Event.KEY_RIGHT) { $('" . $this->m_strPluginName . "[" . ($i + 1) . "]').select(); event.stop(); }" : '') . (($i > 0) ? "if (event.keyCode == Event.KEY_LEFT) { $('" . $this->m_strPluginName . "[" . ($i - 1) . "]').select(); event.stop(); }" : '');

                    if ($l_class_iterator !== null)
                    {
                        $p_param["p_strClass"] = 'class="' . $l_strClasses . ' ' . $l_class_iterator . '_' . $i . '"';
                    } // if

                    $l_border_style = (($i < 3) ? 'border-right:none;' : '');

                    $l_strOut .= '<input name="' . $this->m_strPluginName . '[]" type="text" value="' . $l_value[$i] . '" ' . 'style="width:35px; text-align:center; ' . $l_border_style . '" id="' . $this->m_strPluginName . '[' . $i . ']" ' . 'onkeyup="' . $l_keyup . '" onkeydown="' . $l_keydown . '" maxlength="3" ' . $p_param["p_strTitle"] . ' ' . $p_param["p_strClass"] . ' ' . $p_param["p_bDisabled"] . ' ' . $p_param["p_bReadonly"] . ' ' . $p_param["p_strTab"] . ' ' . $p_param["p_onMouseOver"] . ' ' . $p_param["p_onMouseOut"] . ' ' . $p_param["p_onChange"] . ' ' . $p_param['p_dataIdentifier'] . ' ' . $p_param["p_onClick"] . ' ' . $p_param["p_additional"] . ' />';
                } // for
        } // switch

        return $l_strOut;
    } // function
} // class