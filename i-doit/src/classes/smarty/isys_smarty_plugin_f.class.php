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
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * @var  boolean
     */
    protected $m_bEditMode = false;
    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @var  boolean
     */
    protected $m_enableMetaMap = true;
    /**
     * Parameter array.
     *
     * @var  array
     */
    protected $m_parameter = [];
    /**
     * @var  string
     */
    protected $m_strPluginClass = "";
    /**
     * @var  string
     */
    protected $m_strPluginName = "";

    /**
     * Method for setting a single or multiple parameters.
     *
     * @param   string|array $p_key
     * @param   mixed|null   $p_value
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_parameter($p_key, $p_value = null)
    {
        if (is_array($p_key) && $p_value === null)
        {
            $this->m_parameter = $p_key;
        }
        else if (is_scalar($p_key))
        {
            $this->m_parameter[$p_key] = $p_value;
        } // if

        return $this;
    } // function

    /**
     * Method for getting a single or all parameters.
     *
     * @param   string|null $p_key
     *
     * @return  mixed|array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_parameter($p_key = null)
    {
        if ($p_key === null)
        {
            return $this->m_parameter;
        } // if

        return $this->m_parameter[$p_key];
    } // function

    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return $this->m_enableMetaMap;
    } // function

    /**
     * Returns map for the Smarty Meta Map (SM2).
     *
     * @static
     * @return  array
     */
    public static function get_meta_map()
    {
        return [];
    } // function

    /**
     * Method for navigation-view.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        return '';
    } // function

    /**
     * Get html string for the InfoIcon.
     *
     * @global  array $g_dirs
     *
     * @param   array $p_param
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function getInfoIcon($p_param)
    {
        global $g_dirs;

        $l_return            = "";
        $l_strInfoIconSource = "";
        $l_strTitle          = "";
        $l_strFootnote       = "";
        $l_bInfoIcon         = false;

        if ($p_param["p_bInfoIconSpacer"] == "0")
        {
            return "";
        } // if

        // If p_strHelp, p_strInfo or p_strError are set, overwrite the title and show the InfoIcon (if p_bInfoIcon is not set to 0).
        if (!empty($p_param["p_strInfoIconError"]))
        {
            $l_strTitle          = $p_param["p_strInfoIconError"];
            $l_strInfoIconSource = $g_dirs["images"] . "icons/alert-icon.png";
            $l_bInfoIcon         = true;
        }
        else if (!empty($p_param["p_strInfoIconInfo"]))
        {
            $l_strTitle          = $p_param["p_strInfoIconInfo"];
            $l_strInfoIconSource = $g_dirs["images"] . "icons/infoicon/info.png";
            $l_bInfoIcon         = true;
        }
        else if (!empty($p_param["p_strInfoIconHelp"]))
        {
            $l_strTitle          = _L($p_param["p_strInfoIconHelp"]);
            $l_strInfoIconSource = $g_dirs["images"] . "icons/infoicon/help.png";
            $l_bInfoIcon         = true;
        }
        else if (!empty($p_param["p_strInfoIconWarning"]))
        {
            $l_strTitle          = _L($p_param["p_strInfoIconWarning"]);
            $l_strInfoIconSource = $g_dirs["images"] . "icons/infoicon/warning.png";
            $l_bInfoIcon         = true;
        } // if

        if (!empty($p_param['p_strSelfdefinedIcon']))
        {
            $l_strTitle          = _L($p_param['p_strSelfdefinedIconTitle']);
            $l_strInfoIconSource = $g_dirs["images"] . $p_param['p_strSelfdefinedIcon'];
            $l_bInfoIcon         = true;
        } // if

        if (!empty($p_param['p_strFootnote']) && ($p_param["p_bInfoIconSpacer"] != "0" || $l_bInfoIcon))
        {
            $l_strFootnote = '<span style="position:absolute;float:left;size:2px;">' . $p_param['p_strFootnote'] . '</span>';
        } // if

        if ($p_param["p_bInfoIcon"] != "0" && $l_bInfoIcon == true)
        {
            // Show InfoIcon.
            $l_return .= $l_strFootnote . '<img class="infoIcon vam ' . $p_param["p_strInfoIconClass"] . ' mr5" src="' . $l_strInfoIconSource . '" alt="' . $l_strTitle . '" title="' . $l_strTitle . '" height="15px" width="15px" />';
        }
        else
        {
            // Show spacer image.
            $l_strSource = $g_dirs["images"] . "empty.gif";

            $l_return .= $l_strFootnote . '<img class="infoIcon vam ' . $p_param["p_strInfoIconClass"] . ' mr5" src="' . $l_strSource . '" alt="' . $l_strTitle . '" title="' . $l_strTitle . '" height="15px" width="15px" />';
        } // if

        return $l_return;
    } // function

    /**
     * Attach wiki if configured.
     *
     * @global  array $g_dirs
     *
     * @param   array $p_param
     *
     * @return  string
     */
    protected function attach_wiki($p_param)
    {
        global $g_dirs;

        $l_wiki_url = trim(isys_settings::get('gui.wiki-url'));

        if (!empty($l_wiki_url) && empty($p_param["nowiki"]) && is_null($p_param["p_bDisabled"]))
        {
            $l_last_char = substr($l_wiki_url, -1);

            if ($l_last_char !== '/' && $l_last_char !== ':')
            {
                $l_wiki_url .= '/';
            } // if

            return ' <a target="_blank" href="' . $l_wiki_url . $this->m_strPluginName . '" class="wiki-link" title="Wiki aufrufen"><img src="' . $g_dirs["images"] . 'icons/silk/world_link.png" class="vam" /></a>';
        } // if

        return '';
    } // function

    /**
     * Set the edit-mode.
     *
     * @param   boolean $p_bEditMode
     *
     * @return  $this
     */
    protected function set_edit_mode($p_bEditMode)
    {
        $this->m_bEditMode = $p_bEditMode;

        return $this;
    } // function

    /**
     * Retrieve the edit-mode.
     *
     * @return  boolean
     */
    protected function get_edit_mode()
    {
        return $this->m_bEditMode;
    } // function

    /**
     * Get HTML string for the standard attributes via the parameter-array, given as reference.
     *
     * @param   array &$p_param
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    protected function getStandardAttributes(&$p_param)
    {
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

        if ($p_param["p_strAccessKey"] !== null)
        {
            $p_param["p_strAccessKey"] = 'accesskey="' . $p_param["p_strAccessKey"] . '"';
        } // if

        if ($p_param["type"] !== null)
        {
            $p_param["type"] = 'type="' . $p_param["type"] . '"';
        } // if

        if ($p_param["p_strValue"] !== null)
        {
            if (isset($p_param["p_bNoTranslation"]) && $p_param["p_bNoTranslation"])
            {
                $p_param["p_strValue"] = 'value="' . $p_param["p_strValue"] . '"';
            }
            else
            {
                $p_param["p_strValue"] = 'value="' . _L($p_param["p_strValue"]) . '"';
            } // if
        }
        else if ($p_param["value"] !== null)
        {
            if ($p_param["p_bNoTranslation"] == "1")
            {
                $p_param["value"] = 'value="' . $p_param["value"] . '"';
            }
            else
            {
                $p_param["value"] = 'value="' . _L($p_param["value"]) . '"';
            } // if
        } // if

        if ($p_param["p_nTabIndex"] !== null)
        {
            $p_param["p_nTabIndex"] = 'tabindex="' . $p_param["p_nTabIndex"] . '"';
        }
        else if ($p_param["tabindex"] !== null)
        {
            $p_param["p_nTabIndex"] = 'tabindex="' . $p_param["tabindex"] . '"';
        } // if

        if ($p_param["p_strTitle"] !== null)
        {
            if ($p_param["p_bNoTranslation"] == "1")
            {
                $p_param["p_strTitle"] = 'title="' . $p_param["p_strTitle"] . '"';
            }
            else
            {
                $p_param["p_strTitle"] = 'title="' . _L($p_param["p_strTitle"]) . '"';
            } // if
        }
        else if ($p_param["title"] !== null)
        {
            if ($p_param["p_bNoTranslation"] == "1")
            {
                $p_param["title"] = 'title="' . $p_param["title"] . '"';
            }
            else
            {
                $p_param["title"] = 'title="' . _L($p_param["title"]) . '"';
            } // if
        } // if

        if (!empty($p_param["p_strInfoIconError"]))
        {
            $p_param["p_strClass"] .= ' error';
        } // if

        if (!empty($p_param["p_strAlt"]))
        {
            $p_param["p_strAlt"] = 'alt="' . $p_param["p_strAlt"] . '"';
        } // if

        if (!empty($p_param["p_strClass"]))
        {
            $p_param["p_strClass"] = 'class="' . $p_param["p_strClass"] . '"';
        } // if

        if (!empty($p_param["width"]))
        {
            $p_param["p_strStyle"] = "width:" . $p_param["width"] . ";";
        } // if

        if (!empty($p_param["p_strStyle"]))
        {
            $p_param["p_strStyle"] = 'style="' . $p_param["p_strStyle"] . '"';
        } // if

        if (!empty($p_param["style"]))
        {
            $p_param["p_strStyle"] = 'style="' . $p_param["style"] . '"';
        } // if

        if (!empty($p_param["p_nSize"]))
        {
            $p_param["p_nSize"] = 'size="' . $p_param["p_nSize"] . '"';
        } // if

        if (!empty($p_param["size"]))
        {
            $p_param["p_nSize"] = 'size="' . $p_param["size"] . '"';
        } // if

        if (!empty($p_param["p_nRows"]))
        {
            $p_param["p_nRows"] = 'rows="' . $p_param["p_nRows"] . '"';
        } // if

        if (!empty($p_param["p_nCols"]))
        {
            $p_param["p_nCols"] = 'cols="' . $p_param["p_nCols"] . '"';
        } // if

        if (!empty($p_param["p_nMaxLen"]))
        {
            $p_param["p_nMaxLen"] = 'maxlength="' . $p_param["p_nMaxLen"] . '"';
        } // if

        if (!empty($p_param["p_bDisabled"]))
        {
            $p_param["p_bDisabled"] = 'disabled="disabled"';
        } // if

        if (!empty($p_param["p_bReadonly"]))
        {
            $p_param["p_bReadonly"] = 'readonly="readonly"';
        } // if

        if (!empty($p_param['p_dataIdentifier']))
        {
            $p_param['p_dataIdentifier'] = 'data-identifier="' . $p_param['p_dataIdentifier'] . '"';
        } // if

        // @see ID-2082 We use these attributes instead of CSS classes to identify form-fields with validation.
        if (!empty($p_param['p_validation_rule']))
        {
            $p_param['p_validation_rule'] = 'data-validation-rule="' . $p_param['p_validation_rule'] . '"';
        } // if

        // @see ID-2082 We use these attributes instead of CSS classes to identify form-fields with validation.
        if (!empty($p_param['p_validation_mandatory']))
        {
            $p_param['p_validation_mandatory'] = 'data-validation-rule="' . $p_param['p_validation_mandatory'] . '"';
        } // if

        if (isset($p_param['p_bEnableMetaMap']))
        {
            $this->m_enableMetaMap = !!$p_param['p_bEnableMetaMap'];
        } // if

        if (isset($p_param['p_multiple']) && $p_param['p_multiple'])
        {
            $p_param['p_multiple'] = 'multiple="multiple"';
        } // if
    } // function

    /**
     * Get HTML string for the javascript attributes.
     *
     * @param   array &$p_param
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    protected function getJavascriptAttributes(&$p_param)
    {
        if (!empty($p_param['p_onClick']))
        {
            $p_param['p_onClick'] = 'onclick="' . $p_param['p_onClick'] . '"';
        }
        else
        {
            $p_param['p_onClick'] = '';
        } // if

        if (!empty($p_param['p_onKeyUp']))
        {
            $p_param['p_onKeyUp'] = 'onkeyup="' . $p_param['p_onKeyUp'] . '"';
        }
        else
        {
            $p_param['p_onKeyUp'] = '';
        } // if

        if (!empty($p_param['p_onChange']))
        {
            $p_param['p_onChange'] = 'onchange="' . $p_param['p_onChange'] . '"';
        }
        else
        {
            $p_param['p_onChange'] = '';
        } // if

        if (!empty($p_param['p_onMouseOver']))
        {
            $p_param['p_onMouseOver'] = 'onmouseover="' . $p_param['p_onMouseOver'] . '"';
        }
        else
        {
            $p_param['p_onMouseOver'] = '';
        } // if

        if (!empty($p_param['p_onMouseOut']))
        {
            $p_param['p_onMouseOut'] = 'onmouseout="' . $p_param['p_onMouseOut'] . '"';
        }
        else
        {
            $p_param['p_onMouseOut'] = '';
        } // if

        if (!empty($p_param['p_onMouseMove']))
        {
            $p_param['p_onMouseMove'] = 'onmousemove="' . $p_param['p_onMouseMove'] . '"';
        }
        else
        {
            $p_param['p_onMouseMove'] = '';
        } // if

        if (!empty($p_param['p_onKeyDown']))
        {
            $p_param['p_onKeyDown'] = 'onkeydown="' . $p_param['p_onKeyDown'] . '"';
        }
        else
        {
            $p_param['p_onKeyDown'] = '';
        } // if

        if (!empty($p_param['p_onKeyPress']))
        {
            $p_param['p_onKeyPress'] = 'onkeypress="' . $p_param['p_onKeyPress'] . '"';
        }
        else
        {
            $p_param['p_onKeyPress'] = '';
        } // if

        if (!empty($p_param['p_onFocus']))
        {
            $p_param['p_onFocus'] = 'onfocus="' . $p_param['p_onFocus'] . '"';
        }
        else
        {
            $p_param['p_onFocus'] = '';
        } // if

        if (!empty($p_param['p_onBlur']))
        {
            $p_param['p_onBlur'] = 'onblur="' . $p_param['p_onBlur'] . '"';
        }
        else
        {
            $p_param['p_onBlur'] = '';
        } // if
    } // function
} // class