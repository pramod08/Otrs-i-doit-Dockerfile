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
 * Smarty plugin for Selection lists.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_dialog_list extends isys_smarty_plugin_f implements isys_smarty_plugin
{

    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return [
            "p_strSelectedID",
            "p_arData",
        ];
    } // function

    /**
     * If the parameter 'p_bLinklist' is set to '1' a list with optional given links will be shown in the view mode. Only the list with the selected values will be used!
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_arParams
     *
     * @return  string
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_arParams = null)
    {
        if ($p_arParams === null)
        {
            $p_arParams = $this->m_parameter;
        } // if

        $l_strInfoIcon = $this->getInfoIcon($p_arParams);

        if (!is_array($p_arParams["p_arData"]))
        {
            $l_arParams = unserialize($p_arParams["p_arData"]);
        }
        else
        {
            $l_arParams = $p_arParams["p_arData"];
        } // if

        if (is_array($l_arParams))
        {
            $l_strOut = $l_strInfoIcon . '<div class="chosen-container chosen-container-multi"><ul class="chosen-choices" style="border:none;background:transparent">';

            // Divide selected and not selected into 2 arrays.
            foreach ($l_arParams as $l_val_ar)
            {
                $l_bURL = false;

                $l_value = $l_val_ar["val"];
                $l_sel   = $l_val_ar["sel"];
                $l_url   = $l_val_ar["url"];

                if ($l_sel)
                {
                    $l_strOut .= "<li class='search-choice' style='float:none; margin: 3px 0 3px 0'>";

                    $l_strClass = "";

                    if (strlen($l_url) >= 1)
                    {
                        $l_bURL = true;
                        $l_strOut .= "<a href=\"" . $l_url . "\">";
                        $l_strClass = "class=\"inputLinkLink\"";
                    } // if

                    $l_strOut .= "<span $l_strClass>" . $l_value . "</span>";

                    if ($l_bURL)
                    {
                        $l_strOut .= "</a>";
                    } // if

                    $l_strOut .= "</li>";
                } // if
            } // foreach

            $l_strOut .= "</ul></div>";
        }
        else
        {
            $l_strOut = '<span class="ml20">-</span>';
        } // if

        return $l_strOut;
    } // function

    /**
     * Returns html/javascript dialogue list
     *       - when you have more than 1 call of this function at once,
     *         set $p_arParams["name"] for every call
     *       - to get a comma-seperated list of all IDs you have to query
     *         name + "__available_values" and name +
     *         "__selected_values"
     *       - the selectboxes will get a name with the postfixes
     *         "_available_box" and "_selected_box"
     *
     *       Parameters are given in an array $p_param[]
     *       -----------------------------------------------------------------
     *       //basic parameters
     *       name                -> name
     *       type                -> smarty plug in type
     *       p_strPopupType      -> pop up type
     *       p_strPopupLink      -> link for the pop up image
     *       p_strValue          -> value
     *       p_nTabIndex         -> tabindex
     *       p_nTabOffset        -> taboffset
     *       p_strTitle          -> title (and tooltip)
     *       p_strAlt            -> alt tag for the pop up image
     *
     *       //InfoIcon parameters
     *       p_strInfoIconError  -> errortext for the title attribute of the InfoIcon, the InfoIcon is shown as an error icon
     *       p_strInfoIconInfo   -> infotext for the title attribute of the InfoIcon, the InfoIcon is shown as an info icon
     *       p_strInfoIconHelp   -> helptext for the title attribute of the InfoIcon, the InfoIcon is shown as a help icon
     *       p_bInfoIcon         -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *       p_bInfoIconSpacer   -> if set to 0 no image is shown at all
     *
     *       //Style parameters
     *       p_strID             -> id
     *       p_strClass          -> class
     *       p_strStyle          -> style
     *       p_bSelected         -> preselected, looks like onMouseOver style
     *       p_bEditMode         -> if set to 1 the plug in is always shown in edit style
     *       p_bInvisible        -> don't show anything at all
     *       p_bDisabled         -> disabled
     *       p_bReadonly         -> readonly
     *
     *       //JavaScript parameters
     *       p_onClick           -> onClick
     *       p_onChange          -> onChange
     *       p_onMouseOver       -> onMouseOver
     *       p_onMouseOut        -> onMouseOut
     *       p_onMouseMove       -> onMouseMove
     *       p_onKeyDown         -> onKeyDown
     *       p_onKeyPress        -> onKeyPress
     *
     *       //Special parameters
     *       p_bSort             -> Sort the options by title
     *       p_nSize             -> size
     *       p_nRows             -> rows
     *       p_nCols             -> cols
     *       p_nMaxLen           -> maxlen
     *       p_arData            -> array with data to fill the plug in list
     *       p_bDbFieldNN        -> field is NaN (not a number):
     *       p_strSelectedID     -> pre selected value in the list
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_arParams
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_arParams = null)
    {
        if ($p_arParams === null)
        {
            $p_arParams = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_button";
        $this->m_strPluginName  = $p_arParams["name"];

        $l_arrSelectedValues = [];

        // If disabled is set the value will not be send with the formdata.
        $l_extra = (($p_arParams["p_bDisabled"] == "1") ? "disabled=\"disabled\" " : "") . (($p_arParams["p_bReadonly"] == "1") ? "readonly=\"readonly\" " : "");

        // CallBack Preparation.
        if (isset($p_arParams["add_callback"]))
        {
            $l_add_callback = $p_arParams["add_callback"];
        } // if
        else $l_add_callback = '';

        if (isset($p_arParams["remove_callback"]))
        {
            $l_remove_callback = $p_arParams["remove_callback"];
        } // if
        else $l_remove_callback = '';

        // Name-Handling.
        if ($p_arParams["name"] != "")
        {
            $l_strOptionsName    = $p_arParams["name"] . "__selected_box";
            $l_strSelectedValues = $p_arParams["name"] . "__selected_values";
        }
        else
        {
            $l_strOptionsName    = "SelectBox__selected_box";
            $l_strSelectedValues = "SelectBox__selected_values";
        } // if

        // Unserialize data if needed.
        if (is_string($p_arParams["p_arData"]))
        {
            $p_arParams["p_arData"] = unserialize($p_arParams["p_arData"]);
        } // if

        // Extract data to selected / unselected.
        if (is_array($p_arParams["p_arData"]))
        {
            if (count($p_arParams["p_arData"]) > 0)
            {
                $l_options = [];

                foreach ($p_arParams["p_arData"] as $l_val_ar)
                {
                    if (is_array($l_val_ar))
                    {
                        $l_id       = isset($l_val_ar['id']) ? $l_val_ar['id'] : '';
                        $l_value    = isset($l_val_ar['val']) ? $l_val_ar['val'] : '';
                        $l_selected = isset($l_val_ar['sel']) && $l_val_ar['sel'] ? ' selected="selected"' : '';
                        $l_sticky   = isset($l_val_ar['sticky']) ? $l_val_ar['sticky'] : '';

                        if ($l_sticky)
                        {
                            $l_arSticky[$l_id] = $l_sticky;
                        } // if

                        if ($l_selected)
                        {
                            $l_arrSelectedValues[$l_id] = $l_value;
                        } // if

                        $l_options[$l_id] = '<option value="' . $l_id . '" ' . $l_selected . '>' . $l_value . '</option>';
                    } // if
                    else
                    {
                        return '<div class="error p5 ml20">Error: dialog_list structure incompatible: ' . nl2br(
                            var_export($p_arParams["p_arData"], true)
                        ) . '<br />Use: array(array(id => int, val = string, sel = 1/0, sticky = 1/0))</div>';
                    } // if
                } // foreach

                if (!isset($p_arParams['p_bSort']) || $p_arParams['p_bSort'])
                {
                    asort($l_options);
                } // if

                $p_arParams['chosen-btn-all']         = _L('LC__UNIVERSAL__CHOOSE_ALL');
                $p_arParams['chosen-btn-inverted']    = _L('LC__UNIVERSAL__CHOOSE_INVERTED');
                $p_arParams['chosen-btn-none']        = _L('LC__UNIVERSAL__CHOOSE_NONE');
                $p_arParams['additional_value_field'] = $l_strSelectedValues;

                $l_out = $this->getInfoIcon($p_arParams) . '<input type="hidden" name="' . $l_strSelectedValues . '" id="' . $l_strSelectedValues . '" value="' . implode(
                        ',',
                        array_keys($l_arrSelectedValues)
                    ) . '" />' . "<select name='{$l_strOptionsName}[]' id='{$l_strOptionsName}' multiple class=\"input " . $p_arParams['p_strClass'] . "\"
						data-placeholder=\"" . _L(isset($p_arParams['placeholder']) ? $p_arParams['placeholder'] : 'LC__SMARTY__PLUGIN__DIALOGLIST__CHOSEN') . "\"
	                    onChange=\"updateDialogList(this.id, '{$l_strSelectedValues}');{$l_add_callback};{$l_remove_callback}\" {$l_extra}>" . implode(
                        '',
                        $l_options
                    ) . "</select>";

                $l_js = '<script type="text/javascript">(function() {
				    var $field = $("' . $l_strOptionsName . '");
				    new Chosen($field, {"no_results_text": "' . _L('LC__SMARTY__PLUGIN__DIALOGLIST__NO_RESULTS') . '", search_contains: true});
				    new ChosenExtension($field, ' . isys_format_json::encode($p_arParams) . ');
				    })();</script>';

                return $l_out . $l_js;
            }
            else if (isset($p_arParams['emptyMessage']))
            {
                return $this->getInfoIcon($p_arParams) . '<span class="emptyMessage">' . _L($p_arParams['emptyMessage']) . '</span>';
            } // if
        }
        else if (isset($p_arParams['emptyMessage']))
        {
            return $this->getInfoIcon($p_arParams) . '<span class="emptyMessage">' . _L($p_arParams['emptyMessage']) . '</span>';
        } // if

        return '';
    } // function
} // class