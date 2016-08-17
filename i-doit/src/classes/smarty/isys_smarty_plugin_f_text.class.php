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
 * Smarty plugin for text input fields
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_text extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            'p_strValue',
            'p_bDisabled'
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

        if ($p_param['p_bInvisible'] == true)
        {
            return '';
        } // if

        if ((!isset($p_param['p_strValue']) || is_null($p_param['p_strValue'])) && isset($p_param['default']))
        {
            $p_param['p_strValue'] = $p_param['default'];
        } // if

        if ($p_param['p_bEditMode'] == '1')
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        $l_strRet = $this->getInfoIcon($p_param);

        if ($p_param['p_bPassword'])
        {
            return $l_strRet . '***';
        } // if

        if ($p_param['p_strStyle'])
        {
            $p_param['p_strStyle'] = ' style=\'' . $p_param['p_strStyle'] . '\'';
        } // if

        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]))
        {
            $p_param['p_strValue'] = str_ireplace(
                $_GET[C__SEARCH__GET__HIGHLIGHT],
                '<span class=\'searchHighlight\'>' . $_GET[C__SEARCH__GET__HIGHLIGHT] . '</span>',
                $p_param['p_strValue']
            );
        } // if

        if (isset($p_param['p_bStripSlashes']) && $p_param['p_bStripSlashes'])
        {
            $p_param['p_strValue'] = stripslashes($p_param['p_strValue']);
        } // if

        $l_encoded = is_scalar($p_param['p_strValue']) ? html_entity_decode($p_param['p_strValue'], null, $GLOBALS['g_config']['html-encoding']) : $p_param['p_strValue'];

        return $l_strRet . '<span' . $p_param['p_strStyle'] . '>' . $l_encoded . '</span>';
    } // function

    /**
     * Parameters are given in an array $p_param
     *       -----------------------------------------------------------------
     *       // Basic parameters
     *       name                -> name
     *       type                -> smarty plug in type
     *       p_strPopupType      -> pop up type
     *       p_strPopupLink      -> link for the pop up image
     *       p_strValue          -> value
     *       p_nTabIndex         -> tabindex
     *       p_nTabOffset        -> taboffset
     *       p_strTitle          -> title (and tooltip)
     *       p_strAlt            -> alt tag for the pop up image
     *       p_strPlaceholder    -> HTML5 Placeholder attribute
     *         p_bPassword         -> Type password
     *         p_bPasswordHideValue -> Show *** in Field or nothing
     *
     *       // InfoIcon parameters
     *       p_strInfoIconError  -> errortext for the title attribute of the InfoIcon, the InfoIcon is shown as an error icon
     *       p_strInfoIconInfo   -> infotext for the title attribute of the InfoIcon, the InfoIcon is shown as an info icon
     *       p_strInfoIconHelp   -> helptext for the title attribute of the InfoIcon, the InfoIcon is shown as a help icon
     *       p_bInfoIcon         -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *       p_bInfoIconSpacer   -> if set to 0 no image is shown at all
     *
     *       // Style parameters
     *       p_strID             -> id
     *       id                  -> id
     *       p_strClass          -> class
     *       p_strStyle          -> style
     *       p_bSelected         -> preselected, looks like onMouseOver style
     *       p_bEditMode         -> if set to 1 the plug in is always shown in edit style
     *       p_bInvisible        -> don't show anything at all
     *       p_bDisabled         -> disabled
     *       p_bReadonly         -> readonly
     *
     *       // JavaScript parameters
     *       p_onClick           -> onClick
     *       p_onChange          -> onChange
     *       p_onMouseOver       -> onMouseOver
     *       p_onMouseOut        -> onMouseOut
     *       p_onMouseMove       -> onMouseMove
     *       p_onKeyDown         -> onKeyDown
     *       p_onKeyPress        -> onKeyPress
     *       p_onKeyUp           -> onKeyUp
     *
     *       // Special parameters
     *       p_nSize             -> size
     *       p_nRows             -> rows
     *       p_nCols             -> cols
     *       p_nMaxLen           -> maxlen
     *       p_strTable          -> name of the database table to use for filling the plug in list
     *       p_arData            -> array with data to fill the plug in list
     *       p_bDbFieldNN        -> field is NaN (not a number):
     *       p_strSelectedID     -> pre selected value in the list
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        // This can be defined in the validation config.
        if (isset($p_param['force_dialog']))
        {
            if ($p_param['force_dialog'] === true)
            {
                $l_dialog = new isys_smarty_plugin_f_dialog();

                $p_param['p_bSort']         = false;
                $p_param['p_bDbFieldNN']    = true;
                $p_param['p_arData']        = ['' => ''];
                $p_param['p_strSelectedID'] = $p_param['p_strValue'];

                if (is_array($p_param['force_dialog_data']))
                {
                    foreach ($p_param['force_dialog_data'] as $l_data)
                    {
                        if (is_scalar($l_data))
                        {
                            $p_param['p_arData'][addslashes(strip_tags($l_data))] = addslashes(strip_tags($l_data));
                        } // if
                    } // foreach
                } // if

                if (!isset($p_param['p_arData'][isys_glob_htmlentities($p_param['p_strValue'])]))
                {
                    $p_param['p_arData'][isys_glob_htmlentities($p_param['p_strValue'])] = isys_glob_htmlentities($p_param['p_strValue']);
                    $p_param["p_arDisabled"]                                             = serialize([isys_glob_htmlentities($p_param['p_strValue']) => true]);
                } // if

                return $l_dialog->navigation_edit($p_tplclass, $p_param);
            } // if
        }

        $this->m_strPluginClass = 'f_text';
        $this->m_strPluginName  = $p_param['name'];

        // Default css class.
        $p_param['p_strClass'] = 'input ' . (isset($p_param['p_strClass']) ? $p_param['p_strClass'] : '');

        if ((!isset($p_param['p_strValue']) || is_null($p_param['p_strValue'])) && isset($p_param['default']))
        {
            $p_param['p_strValue'] = $p_param['default'];
        } // if

        // Is the error flag set?
        if (!empty($p_param['p_strError']))
        {
            $p_param['p_strError'] = $p_param['p_strError'] . 'Error';
        } // if

        if (isset($p_param['p_bStripSlashes']) && $p_param['p_bStripSlashes'])
        {
            $p_param['p_strValue'] = stripslashes($p_param['p_strValue']);
        } // if

        if (isset($p_param['p_strValue']) && is_scalar($p_param['p_strValue']))
        {
            $p_param['p_strValue'] = htmlentities($p_param['p_strValue'], ENT_QUOTES, $GLOBALS['g_config']['html-encoding']);
        }

        if (!isset($p_param['p_nSize']) || is_null($p_param['p_nSize']))
        {
            $p_param['p_nSize'] = '65';
        } // if

        if (isset($p_param['p_strPlaceholder']))
        {
            $p_param['p_strPlaceholder'] = ' placeholder="' . _L($p_param['p_strPlaceholder']) . '" ';
        } // if

        $l_description_tag = '';

        if (isset($p_param['p_description']) && !empty($p_param['p_description']))
        {
            $l_description_tag = '<p class="mt5" style="font-size: smaller;">' . $this->getInfoIcon($p_param) . _L($p_param['p_description']) . '</p>';
        }

        if (isset($p_param['p_strSuggest']) && empty($p_param['p_strSuggestView']))
        {
            $p_param['p_strSuggestView'] = $p_param['name'];
        }

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        if (isset($p_param['p_bPassword']) && $p_param['p_bPassword'])
        {
            $l_input_type = 'password';
            if (isset($p_param['p_bPasswordHideValue']))
            {
                unset($p_param['p_strValue']);
            } // if
        }
        else
        {
            $l_input_type = 'text';
        } // if

        if (isset($p_param['p_bInvisible']) && $p_param['p_bInvisible'])
        {
            $l_input_type = 'hidden';
        } // if

        $l_strOut = $this->getInfoIcon($p_param) .
            '<input ' . $p_param['name'] . ' ' . 'type=\'' . $l_input_type . '\' ' . $p_param['p_strID'] . ' ' . $p_param['p_strTitle'] . ' ' . $p_param['p_strClass'] . ' ' . $p_param['p_strPlaceholder'] . ' ' . $p_param['p_bDisabled'] . ' ' . $p_param['p_bReadonly'] . ' ' . $p_param['p_strStyle'] . ' ' . $p_param['p_strValue'] . ' ' . $p_param['p_nTabIndex'] . ' ' . $p_param['p_nSize'] . ' ' . $p_param['p_nMaxLen'] . ' ' . $p_param['p_onMouseOver'] . ' ' . $p_param['p_onMouseOut'] . ' ' . $p_param['p_onChange'] . ' ' . $p_param['p_onClick'] . ' ' . $p_param['p_onKeyPress'] . ' ' . $p_param['p_onKeyUp'] . ' ' . $p_param['p_onFocus'] . ' ' . $p_param['p_onBlur'] . ' ' . $p_param['p_dataIdentifier'] . ' ' . $p_param['p_onKeyDown'] . ' ' . $p_param['p_additional'] . ' />';

        // Attach WIKI Link.
        $l_strOut .= $this->attach_wiki($p_param);

        if (isset($p_param['p_strSuggest']))
        {
            $l_suggestField = $p_param['p_strSuggestView'] ?: $p_param['name'];
            $l_parameters   = $p_param['p_strSuggestParameters'] ?: '';

            $l_strOut .= '<script type=\'text/javascript\'>' . 'new idoit.Suggest(\'' . $p_param['p_strSuggest'] . '\', \'' . $l_suggestField . '\', \'' . $p_param['p_strSuggestHidden'] . '\', {' . $l_parameters . '});' . '</script>';
        } // if

        return $l_strOut . $l_description_tag;
    } // function
} // class