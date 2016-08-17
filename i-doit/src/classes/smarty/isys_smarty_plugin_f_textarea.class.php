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
 * Smarty plugin for textarea input fields
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_textarea extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
    } // function

    /**
     * View mode.
     *
     * @return string $p_param["p_strValue"]
     *
     * @param isys_component_template &$p_tplclass
     * @param                         $p_param
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        global $g_dirs;

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $l_strSource = $g_dirs["images"] . "empty.gif";

        $l_spacer_img = '<img class="infoIcon vam" src="' . $l_strSource . '" alt="" height="15px" title="" ' . 'width="15px" style="margin-right:5px;" />';

        if ($p_param["p_bEditMode"] == 1)
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        $l_content_textarea = "";

        $p_param["p_strValue"] = isys_helper_textformat::strip_scripts_tags($p_param["p_strValue"], $p_param["htmlEnabled"]);
        $p_param["p_strValue"] = str_replace("\\n", "\n", $p_param["p_strValue"]);
        $p_param["p_strValue"] = str_replace("\\r", "\r", $p_param["p_strValue"]);

        $l_arValue = explode("\n", $p_param["p_strValue"]);
        $l_first   = true;
        foreach ($l_arValue as $value)
        {
            $l_content_textarea .= ((!$l_first) ? $l_spacer_img : '') . str_replace("\\r", "", $value) . "<br />";
            $l_first = false;
        } // foreach

        $l_content_textarea = rtrim($l_content_textarea);

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName  = $p_param["name"];

        $l_content_textarea = $this->getInfoIcon($p_param) . "<span>" . $l_content_textarea . "</span>";

        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]))
        {
            $l_content_textarea = str_ireplace(
                $_GET[C__SEARCH__GET__HIGHLIGHT],
                "<span class=\"searchHighlight\">" . $_GET[C__SEARCH__GET__HIGHLIGHT] . "</span>",
                $l_content_textarea
            );
        } // if

        return $l_content_textarea;
    } // function

    /**
     * Edit mode - Parameters are given in an array $p_param:
     *     Basic parameters
     *         name                    -> name
     *         type                    -> smarty plug in type
     *         p_strPopupType          -> pop up type
     *         p_strPopupLink          -> link for the pop up image
     *         p_strValue              -> value
     *         p_nTabIndex             -> tabindex
     *         p_nTabOffset            -> taboffset
     *         p_strTitle              -> title (and tooltip)
     *         p_strAlt                -> alt tag for the pop up image
     *
     *     InfoIcon parameters
     *         p_strInfoIconError      -> errortext for the title attribute of the InfoIcon, the InfoIcon is shown as an error icon
     *         p_strInfoIconInfo       -> infotext for the title attribute of the InfoIcon, the InfoIcon is shown as an info icon
     *         p_strInfoIconHelp       -> helptext for the title attribute of the InfoIcon, the InfoIcon is shown as a help icon
     *         p_bInfoIcon             -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *         p_bInfoIconSpacer       -> if set to 0 no image is shown at all
     *
     *     Style parameters
     *         p_strID                 -> id
     *         p_strClass              -> class
     *         p_strStyle              -> style
     *         p_bSelected             -> preselected, looks like onMouseOver style
     *         p_bEditMode             -> if set to 1 the plug in is always shown in edit style
     *         p_bInvisible            -> don't show anything at all
     *         p_bDisabled             -> disabled
     *         p_bReadonly             -> readonly
     *
     *     JavaScript parameters
     *         p_onClick               -> onClick
     *         p_onChange              -> onChange
     *         p_onMouseOver           -> onMouseOver
     *         p_onMouseOut            -> onMouseOut
     *         p_onMouseMove           -> onMouseMove
     *         p_onKeyDown             -> onKeyDown
     *         p_onKeyPress            -> onKeyPress
     *
     *     Special parameters
     *         p_nSize                 -> size
     *         p_nRows                 -> rows
     *         p_nCols                 -> cols
     *         p_nMaxLen               -> maxlen
     *         p_strTable              -> name of the database table to use for filling the plug in list
     *         p_arData                -> array with data to fill the plug in list
     *         p_bDbFieldNN            -> field is NaN (not a number):
     *         p_strSelectedID         -> pre selected value in the list
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName  = $p_param["name"];

        $p_param["p_strValue"] = isys_helper_textformat::strip_scripts_tags($p_param["p_strValue"], $p_param["htmlEnabled"]);

        $l_name = null;

        if ($p_param["name"] !== null)
        {
            $l_name          = $p_param["name"];
            $p_param["name"] = 'name="' . $p_param["name"] . '"';
        } // if

        if ($p_param["p_strID"] !== null)
        {
            $p_param["p_strID"] = 'id="' . $p_param["p_strID"] . '"';
        }
        else if ($p_param["id"] !== null)
        {
            $p_param["p_strID"] = 'id="' . $p_param["id"] . '"';
        }
        else if (isset($l_name))
        {
            $p_param["p_strID"] = 'id="' . $l_name . '"';
        } // if

        $l_extra            = "";
        $l_content_textarea = "";
        $l_strTitle         = "";
        $l_strClass         = "input " . $p_param['p_strClass'];
        $l_nRows            = 10;

        // Is the error flag set?
        if (!empty($p_param["p_strError"]))
        {
            $l_strClass = $l_strClass . "Error";
        } // if

        $l_extra .= ($p_param['p_bDisabled'] == 1) ? 'disabled="disabled" ' : '';
        $l_extra .= ($p_param['p_bReadonly'] == 1) ? 'readonly="readonly" ' : '';

        $this->getJavascriptAttributes($p_param);

        // Rows and columns.
        if (!empty($p_param["p_nRows"]))
        {
            $l_nRows = $p_param["p_nRows"];
        } // if

        if (!empty($p_param["p_nCols"]))
        {
            $l_nCols = $p_param["p_nCols"];
        }
        else
        {
            $l_nCols = 32;
        } // if

        $p_param["p_strValue"] = str_replace("\\n", "\n", $p_param["p_strValue"]);
        $p_param["p_strValue"] = str_replace("\\r", "\r", $p_param["p_strValue"]);

        $l_arValue = explode("\n", $p_param["p_strValue"]);

        foreach ($l_arValue as $value)
        {
            $l_content_textarea .= str_replace("\\r", "", $value) . "\n";
        } // foreach

        $l_content_textarea = rtrim($l_content_textarea, "\n");

        return $this->getInfoIcon(
            $p_param
        ) . "<textarea " . $l_extra . $p_param["p_strID"] . $p_param["name"] . $p_param["p_onChange"] . $p_param["p_onKeyUp"] . $p_param["p_onKeyDown"] . $p_param["p_onKeyPress"] . $p_param["p_onClick"] . "data-identifier=\"" . $p_param['p_dataIdentifier'] . "\" " . "class=\"" . $l_strClass . "\" " . "style=\"" . $p_param["p_strStyle"] . "\" " . "title=\"" . $l_strTitle . "\" " . "tabindex=\"" . $p_param["p_strTab"] . "\" " . "rows=\"" . $l_nRows . "\" " . "cols=\"" . $l_nCols . "\">" . $l_content_textarea . "</textarea>";
    } // function
} // class