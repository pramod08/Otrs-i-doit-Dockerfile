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
 * Smarty plugin for Dialog(+)
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     Andre Woesten <awoesten@i-doit.org> - 25.08.05
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_dialog extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            "p_strSelectedID",
            "p_strTable",
            "p_arData"
        ];
    } // function

    /**
     * Navigation view for dialog-fields.
     *
     * @param  isys_component_template $p_tplclass
     * @param  array                   $p_param
     *
     * @return string
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_button";
        $this->m_strPluginName  = $p_param["name"];

        $l_strOut   = "";
        $l_strValue = "";

        if ($p_param["p_bEditMode"] == "1")
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        }

        $l_arData = [];

        if (!empty($p_param["p_arData"]))
        {
            // GET-array with data from $p_param
            if (is_array($p_param["p_arData"]))
            {
                $l_arData = $p_param["p_arData"];
            }
            elseif (is_string($p_param["p_arData"]))
            {
                $l_arData = unserialize($p_param["p_arData"]);
            } // if
        }
        else
        {
            //get array from table
            if (!empty($p_param["p_strTable"]))
            {

                if ($p_param["status"] == 0)
                {
                    $l_status = null;
                }
                else
                {
                    $l_status = C__RECORD_STATUS__NORMAL;
                } // if

                $l_arData = $this->get_array_data(
                    $p_param["p_strTable"],
                    $l_status,
                    $p_param["order"],
                    $p_param["condition"]
                );
            } // if
        } // if

        if (is_array($l_arData) && isset($p_param['p_strDbFieldNN']))
        {
            $l_arData[-1] = _L($p_param['p_strDbFieldNN']);
        } // if

        // Evaluate current value
        if (isset($p_param["p_strSelectedID"]))
        {
            if ($l_arData != null)
            {
                $l_multiple       = (strpos($p_param["p_strSelectedID"], ',') !== false);
                $l_multiple_items = explode(',', $p_param["p_strSelectedID"]);

                foreach ($l_arData as $l_content)
                {
                    if (is_array($l_content))
                    {
                        if (isset($l_content[$p_param["p_strSelectedID"]]))
                        {
                            $l_value    = $l_content[$p_param["p_strSelectedID"]];
                            $l_strValue = isys_glob_htmlentities(isys_glob_str_stop(_L($l_value), isys_tenantsettings::get('maxlength.dialog_plus', 110)));

                            continue;
                        } // if
                    }
                    else
                    {
                        if ($l_multiple)
                        {
                            $l_strValue = [];

                            foreach ($l_multiple_items as $l_item)
                            {
                                $l_strValue[] = isys_glob_htmlentities(isys_glob_str_stop(_L($l_arData[$l_item]), isys_tenantsettings::get('maxlength.dialog_plus', 110)));
                            } // foreach

                            $l_strValue = implode(', ', $l_strValue);
                        }
                        else
                        {
                            if (isset($l_arData[$p_param["p_strSelectedID"]]))
                            {
                                $l_value    = $l_arData[$p_param["p_strSelectedID"]];
                                $l_strValue = isys_glob_htmlentities(isys_glob_str_stop(_L($l_value), isys_tenantsettings::get('maxlength.dialog_plus', 110)));
                            } // if
                        } // if

                        continue;
                    } // if
                } // foreach
            } // if
        }
        else
        {
            $l_strValue = "-";
        } // if

        if (empty($l_strValue))
        {
            if (isset($p_param["p_strValue"]))
            {
                $l_strValue = $p_param["p_strValue"];
            } // if
        } // if

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        //show InfoIcon
        $l_strOut .= $this->getInfoIcon($p_param);

        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]))
        {
            $l_strValue = str_ireplace($_GET[C__SEARCH__GET__HIGHLIGHT], "<span class=\"searchHighlight\">" . $_GET[C__SEARCH__GET__HIGHLIGHT] . "</span>", $l_strValue);
        } // if

        return $l_strOut . $l_strValue;
    } // function

    /**
     * Returns the data from a table in an array.
     *
     * @param   string  $p_strTablename
     * @param   integer $p_status
     * @param   string  $p_order
     * @param   string  $p_condition
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_array_data($p_strTablename, $p_status = C__RECORD_STATUS__NORMAL, $p_order = null, $p_condition = null)
    {
        $l_return = [];

        $l_tblres = isys_glob_get_data_by_table($p_strTablename, null, $p_status, $p_order, $p_condition);
        if ($l_tblres != null)
        {
            if ($l_tblres->num_rows() > 0)
            {
                while ($l_tblrow = $l_tblres->get_row())
                {
                    $l_return[$l_tblrow[$p_strTablename . "__id"]] = _L($l_tblrow[$p_strTablename . "__title"]);
                } // while
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Parameters are given in an array $p_param:
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
     *     InfoIcon parameters
     *         p_strInfoIconError      -> errortext for the title attribute of the InfoIcon, the InfoIcon is shown as an error icon
     *         p_strInfoIconInfo       -> infotext for the title attribute of the InfoIcon, the InfoIcon is shown as an info icon
     *         p_strInfoIconHelp       -> helptext for the title attribute of the InfoIcon, the InfoIcon is shown as a help icon
     *         p_strInfoIconWarning    -> helptext for the title attribute of the InfoIcon, the InfoIcon is shown as a warning icon
     *         p_bInfoIcon             -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *         p_bInfoIconSpacer       -> if set to 0 no image is shown at all
     *     Style parameters
     *         p_strID                 -> id
     *         p_strClass              -> class
     *         p_strStyle              -> style
     *         p_bSelected             -> preselected, looks like onMouseOver style
     *         p_bEditMode             -> if set to 1 the plug in is always shown in edit style
     *         p_bInvisible            -> don't show anything at all
     *         p_bDisabled             -> disabled (and add a hidden field to save the value when sending the form)
     *         p_bReadonly             -> readonly
     *     JavaScript parameters
     *         p_onClick               -> onClick
     *         p_onChange              -> onChange
     *         p_onMouseOver           -> onMouseOver
     *         p_onMouseOut            -> onMouseOut
     *         p_onMouseMove           -> onMouseMove
     *         p_onKeyDown             -> onKeyDown
     *         p_onKeyPress            -> onKeyPress
     *     Special parameters
     *         p_bSort                 -> Sort the given p_arData or not (boolean)
     *         p_nSize                 -> size
     *         p_nRows                 -> rows
     *         p_nCols                 -> cols
     *         p_nMaxLen               -> maxlen
     *         p_strTable              -> name of the database table to use for filling the plug in list
     *         p_arData                -> array with data to fill the plug in list
     *         p_bDbFieldNN            -> field is NaN (not a number):
     *         p_strSelectedID         -> pre selected value in the list
     *         p_bPlus                 -> Show + button to allow non-sysop users to add entries
     *         p_optionsTable          -> name of the database table to use for the options
     *         p_const                 -> constant to get values of the specific constant
     *     Parameters for a combined dialogbox
     *
     *     Parameters needed for the first dialog box
     *         p_ajaxTable             -> Target table where the data lies
     *         p_ajaxIdentifier        -> Identifier of the second dialog box
     *     Parameters needed for the second dialog box
     *         p_strSecTableIdentifier -> Identifier of the parent dialog box
     *
     *       Parameter needed to determine if its a chosen dialog box
     *           chosen                   -> true or false
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        try
        {
            global $g_dirs;

            $p_param["p_additional"] = '';

            $this->m_strPluginClass = 'f_button';
            $this->m_strPluginName  = $p_param["name"];

            // p_editMode
            if (isset($p_param["p_editMode"]) && !$p_param["p_editMode"])
            {
                return $this->navigation_view($p_tplclass, $p_param);
            } // if

            $l_strHidden = '';
            $l_arData    = [];

            if ($p_param["p_bDisabled"])
            {
                $l_strHidden = '<input type="hidden" name="' . $p_param['name'] . '" value="' . $p_param['p_strSelectedID'] . '" />';
            } // if

            $l_arDisabled = unserialize($p_param["p_arDisabled"]);

            // Show select box?
            if ($p_param["p_bInvisible"] == "1")
            {
                return "";
            } // if

            if (!empty($p_param["p_arData"]))
            {
                // GET-array with data from $p_param
                if (is_array($p_param["p_arData"]))
                {
                    $l_arData = $p_param["p_arData"];
                }
                else if (is_string($p_param["p_arData"]))
                {
                    $l_arData = unserialize($p_param["p_arData"]);
                } // if
            }
            else
            {
                // Get array from table
                if (!empty($p_param["p_strTable"]))
                {
                    if ($p_param["status"] == 0)
                    {
                        $l_status = null;
                    }
                    else
                    {
                        $l_status = C__RECORD_STATUS__NORMAL;
                    } // if

                    if ($p_param["p_identifier"])
                    {
                        if ($p_param["p_strTable"] == "isys_dialog_plus_custom")
                        {
                            $p_param["condition"] = "isys_dialog_plus_custom__identifier = '" . $p_param["p_identifier"] . "'";
                        } // if
                    } // if

                    if ($p_param["p_const"])
                    {
                        $p_param["condition"] = $p_param["p_strTable"] . "__const = '" . $p_param["p_const"] . "'";
                    } // if

                    if ($_GET["secTable"] && $_GET["secTableID"])
                    {
                        $p_param["condition"] = $p_param["p_strTable"] . "__" . $_GET["secTable"] . "__id = '" . $_GET["secTableID"] . "'";
                    }
                    else if ($p_param["secTable"] && $p_param["secTableID"])
                    {
                        if (is_object($p_param["secTableID"]))
                        {
                            if (isset($_GET[C__CMDB__GET__OBJECT]))
                            {
                                $l_request             = isys_request::factory()
                                    ->set_object_id($_GET[C__CMDB__GET__OBJECT]);
                                $p_param["secTableID"] = $p_param["secTableID"]->execute($l_request);
                                $p_param["condition"]  = $p_param["p_strTable"] . "__" . $p_param["secTable"] . "__id = '" . $p_param["secTableID"] . "'";
                            } // if
                        }
                        else
                        {
                            $p_param["condition"] = $p_param["p_strTable"] . "__" . $p_param["secTable"] . "__id = '" . $p_param["secTableID"] . "'";
                        } // if
                    }
                    else if ($p_param["secTable"])
                    {
                        $p_param["condition"] = $p_param["p_strTable"] . "__id = FALSE";
                    } // if

                    $l_arData = $this->get_array_data(
                        $p_param["p_strTable"],
                        $l_status,
                        $p_param["order"],
                        $p_param["condition"]
                    );
                } // if
            } // if

            if (isset($p_param["id"]))
            {
                $p_param["p_strID"] = $p_param["id"];
            } // if

            if (!isset($p_param["p_strClass"]))
            {
                $p_param["p_strClass"] = '';
            } // if

            $p_param["p_strClass"] = "input " . $p_param["p_strClass"];

            // Enable chosen?
            if (isset($p_param['chosen']) && $p_param['chosen'])
            {
                $p_param['p_strClass'] .= ' chosen-select';
            } // if

            if ($p_param['placeholder'])
            {
                $p_param["p_additional"] .= 'data-placeholder="' . _L($p_param['placeholder']) . '"';
            } // if

            // Handle secidentifier
            $l_attribute_secidentifier = '';
            if (isset($p_param['p_strSecTableIdentifier']))
            {
                $l_attribute_secidentifier = "data-secidentifier=\"" . $p_param['p_strSecTableIdentifier'] . "\" ";
            } // if

            $this->getStandardAttributes($p_param);
            $this->getJavascriptAttributes($p_param);

            if (empty($l_arData) && isset($p_param['emptyMessage']) && $p_param['emptyMessage'] && !isset($p_param["p_bPlus"]))
            {
                $l_strOut = $this->getInfoIcon($p_param) . '<span class="emptyMessage">' . _L(
                        $p_param['emptyMessage']
                    ) . '</span> <input type="hidden" ' . $p_param["name"] . ' value="" />';
            }
            else
            {
                //show InfoIcon
                $l_strOut = $this->getInfoIcon(
                        $p_param
                    ) . "<select " . $p_param["name"] . " " . $p_param["p_strClass"] . " " . $p_param["p_strStyle"] . " " . $p_param["p_strTitle"] . " " . $p_param["p_strID"] . " " . $p_param["p_onClick"] . " " . $p_param["p_onChange"] . " " . $p_param["p_bDisabled"] . " " . $p_param["p_bReadonly"] . " " . $p_param["p_strTabIndex"] . " " . $p_param["p_nSize"] . " " . $p_param["p_onKeyPress"] . " " . $p_param["p_onKeyDown"] . " " . $p_param["p_onMouseOver"] . " " . $p_param['p_dataIdentifier'] . " " . $p_param["p_onMouseOut"] . " " . $l_attribute_secidentifier . " " . $p_param["p_additional"] . " " . $p_param["p_multiple"] . " " . ">\n";

                if ($p_param["p_bDbFieldNN"] != "1")
                {
                    $l_strOut .= $this->get_option(
                        isset($p_param["p_strDbFieldNN"]) ? _L($p_param["p_strDbFieldNN"]) : ' - ',
                        '-1',
                        ($p_param["p_strSelectedID"] == '-1' || $p_param["p_strSelectedID"] == ''),
                        false
                    );
                } // if

                if ($p_param["exclude"])
                {
                    $l_exc = explode(";", $p_param["exclude"]);
                    if (!$l_exc)
                    {
                        $l_exc = explode(",", $p_param["exclude"]);
                    } // if

                    foreach ($l_exc as $l_exclude)
                    {
                        if (defined($l_exclude))
                        {
                            $l_excludes[constant($l_exclude)] = true;
                        }
                        else
                        {
                            $l_excludes[$l_exclude] = true;
                        } // if
                    } // foreach
                } // if

                if (is_array($l_arData))
                {
                    // Sort the Array.
                    if (is_array($l_arData))
                    {
                        if (!isset($p_param['p_bSort']) || $p_param['p_bSort'])
                        {
                            uasort(
                                $l_arData,
                                function ($a, $b)
                                {
                                    if ($a == $b) return 0;

                                    if (is_array($a) || is_array($b))
                                    {
                                        if (is_array($a))
                                        {
                                            uasort(
                                                $a,
                                                function ($a2, $b2)
                                                {
                                                    if ($a2 == $b2) return 0;

                                                    return strcasecmp(_L($a2), _L($b2));
                                                }
                                            );
                                        } // if

                                        if (is_array($b))
                                        {
                                            uasort(
                                                $b,
                                                function ($a2, $b2)
                                                {
                                                    if ($a2 == $b2) return 0;

                                                    return strcasecmp(_L($a2), _L($b2));
                                                }
                                            );
                                        } // if

                                        return 0;
                                    } // if

                                    return strcasecmp(_L($a), _L($b));
                                }
                            );
                        }
                    } // if

                    // Needs to be converted to string otherwise this case is true (2 == '2_4') = true
                    $p_param["p_strSelectedID"] .= '';

                    $l_multiple        = (strpos($p_param["p_strSelectedID"], ',') !== false);
                    $l_multiple_values = explode(',', $p_param['p_strSelectedID']);

                    // Needs to be converted to string otherwise this case is true (2 == '2_4') = true
                    $p_param["p_strSelectedID"] .= '';

                    foreach ($l_arData as $l_key => $l_content)
                    {
                        if (is_array($l_content))
                        {
                            if (isset($p_param["sort"]) && $p_param["sort"])
                            {
                                asort($l_content);
                            } // if

                            $l_strOut .= "<optgroup label=\"" . $l_key . "\">";

                            foreach ($l_content as $l_contentkey => $l_value)
                            {
                                if (isset($l_excludes[$l_contentkey]))
                                {
                                    continue;
                                } // if

                                $l_contentkey .= '';

                                $l_strOut .= $this->get_option(
                                    $l_value,
                                    $l_contentkey,
                                    (isset($p_param["p_strSelectedID"]) && ($p_param["p_strSelectedID"] == $l_contentkey || $l_multiple && in_array(
                                                $l_contentkey,
                                                $l_multiple_values
                                            ))),
                                    ($l_arDisabled[$l_contentkey] == true)
                                );
                            } // foreach

                            $l_strOut .= "</optgroup>";
                        }
                        else
                        {
                            if (isset($l_excludes[$l_key]))
                            {
                                continue;
                            } // if

                            $l_key .= '';

                            $l_strOut .= $this->get_option(
                                $l_content,
                                $l_key,
                                (isset($p_param["p_strSelectedID"]) && ($p_param["p_strSelectedID"] == $l_key || $l_multiple && in_array($l_key, $l_multiple_values))),
                                ($l_arDisabled[$l_key] == true)
                            );
                        } // if
                    } // foreach
                } // if

                $l_strOut .= "</select>";

                if (isset($p_param['secTable']) && (!empty($p_param['secTable']) && empty($p_param['secTableID'])))
                {
                    // Load data via ajax
                    $l_strOut .= '<script type="text/javascript">';
                    $l_strOut .= "if ($('" . $p_param['p_strSecTableIdentifier'] . "').value != -1) new Ajax.Request('?call=combobox&func=load&ajax=1',
								{
									parameters:{
										'table':'" . $p_param['p_strTable'] . "',
										'parent_table':'" . $p_param['secTable'] . "',
										'parent_table_id':$('" . $p_param['p_strSecTableIdentifier'] . "').value
									},
									method:'post',
									onSuccess:function (transport) {
										var dialog_field = $('" . $this->m_strPluginName . "').update(''),
											json = [];
										if (transport.responseText != '[]') {
											json = new Hash(transport.responseJSON);
										}
										" . (((int) $p_param['p_bDbFieldNN'] == 0) ? "dialog_field.insert(new Element('option', {value: '-1'}).update('-'));" : "") . "
										json.each(function(item) {
											if(item.key == '" . $p_param['p_strSelectedID'] . " '){
												dialog_field.insert(new Element('option', {value: item.key, selected: 'selected'}).update(item.value));
											} else{
												dialog_field.insert(new Element('option', {value: item.key}).update(item.value));
											}
										});
									}
								});";
                    $l_strOut .= '</script>';
                } // if

                if (isset($p_param["p_bPlus"]) && !empty($p_param["p_bPlus"]) && $p_param["p_bPlus"] != 'off')
                {
                    $l_strOut .= '<a href="javascript:" class="' . str_replace('[]', '', $this->m_strPluginName) . ' dialog-plus ml5" title="' . _L(
                            "LC__UNIVERSAL__NEW_VALUE"
                        ) . '" onClick="' . $p_param["p_strLink"] . '";>' . '<img src="' . $g_dirs["images"] . 'icons/silk/page_white_stack.png" class="vam" alt="' . _L(
                            "LC__UNIVERSAL__NEW_VALUE"
                        ) . '" />' . '</a>';
                } // if
            } // if

            return $l_strOut . $this->attach_wiki($p_param) . $l_strHidden;
        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage());
        }

        return '';
    } // function

    /**
     * Method for retrieving the option-field.
     *
     * @param   string  $p_value
     * @param   string  $p_key
     * @param   boolean $p_selected
     * @param   boolean $p_disabled
     *
     * @return  string
     */
    private function get_option($p_value, $p_key, $p_selected = false, $p_disabled = false)
    {
        $l_strSelected = ($p_selected) ? ' selected="selected"' : '';
        $l_disabled    = ($p_disabled) ? ' disabled="disabled"' : '';

        /* @see  ID-2234 */
        // We decode the HTML entities once, so that we don't have to deal with double-encoded values ("&lt;", "&amp;amp;" ...).
        $p_value = html_entity_decode($p_value, null, $GLOBALS['g_config']['html-encoding']);

        $p_value = isys_glob_str_stop(_L(htmlentities($p_value, null, $GLOBALS['g_config']['html-encoding'])), isys_tenantsettings::get('maxlength.dialog_plus', 110));

        return '<option value="' . $p_key . '" ' . $l_strSelected . $l_disabled . '>' . $p_value . "</option>";
    } // function
} // class