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
 * @package    i-doit
 * @subpackage Smarty_Plugins
 * @author     Van Quyen Hoang <qhoang@synetics.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_autotext extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Parameters are given in an array $p_param[]
     *     Basic parameters
     *         name                -> name
     *         type                -> smarty plug in type
     *         p_strPopupType      -> pop up type
     *         p_strPopupLink      -> link for the pop up image
     *         p_strValue          -> value
     *         p_nTabIndex         -> tabindex
     *         p_nTabOffset        -> taboffset
     *         p_strTitle          -> title (and tooltip)
     *         p_strAlt            -> alt tag for the pop up image
     *
     *     InfoIcon parameters
     *         p_strInfoIconError  -> errortext for the title attribute of the InfoIcon, the InfoIcon is shown as an error icon
     *         p_strInfoIconInfo   -> infotext for the title attribute of the InfoIcon, the InfoIcon is shown as an info icon
     *         p_strInfoIconHelp   -> helptext for the title attribute of the InfoIcon, the InfoIcon is shown as a help icon
     *         p_bInfoIcon         -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *         p_bInfoIconSpacer   -> if set to 0 no image is shown at all
     *
     *     Style parameters
     *         p_strID             -> id
     *         id                   -> id
     *         p_strClass          -> class
     *         p_strStyle          -> style
     *         p_bSelected         -> preselected, looks like onMouseOver style
     *         p_bEditMode         -> if set to 1 the plug in is always shown in edit style
     *         p_bInvisible        -> don't show anything at all
     *         p_bDisabled         -> disabled
     *         p_bReadonly         -> readonly
     *
     *     JavaScript parameters
     *         p_onClick           -> onClick
     *         p_onChange          -> onChange
     *         p_onMouseOver       -> onMouseOver
     *         p_onMouseOut        -> onMouseOut
     *         p_onMouseMove       -> onMouseMove
     *         p_onKeyDown         -> onKeyDown
     *         p_onKeyPress        -> onKeyPress
     *         p_onKeyUp           -> onKeyUp
     *
     *     Special parameters
     *         p_nSize             -> size
     *         p_nRows             -> rows
     *         p_nCols             -> cols
     *         p_nMaxLen           -> maxlen
     *         p_strTable          -> name of the database table to use for filling the plug in list
     *         p_arData            -> array with data to fill the plug in list
     *         p_bDbFieldNN        -> field is NaN (not a number):
     *         p_strSelectedID     -> pre selected value in the list
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = 'f_autotext';
        $this->m_strPluginName  = $p_param['name'];

        // Default css class.
        if (empty($p_param['p_strClass']))
        {
            // @todo  ID-1365 before removing "inputTextarea" check if any modules use this class to identify this element.
            $p_param['p_strClass'] = 'inputText';
        }
        else
        {
            // @todo  ID-1365 before removing "inputTextarea" check if any modules use this class to identify this element.
            $p_param['p_strClass'] .= ' inputText';
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

        $p_param['p_strValue'] = htmlentities($p_param['p_strValue'], null, $GLOBALS['g_config']['html-encoding']);

        if (is_null($p_param['p_nSize']))
        {
            $p_param['p_nSize'] = '65';
        } // if

        if ($p_param['p_strSuggest'] && $p_param["p_strSuggestParameters"] && $p_param['p_strValue'] > 0)
        {
            $l_condition                = '';
            $p_param['p_strSelectedID'] = $p_param['p_strValue'];

            preg_match("/\".*.\"/", $p_param["p_strSuggestParameters"], $l_matches);
            $l_condition_info = explode(',', trim(str_replace('"', '', $l_matches[0])));
            $l_table          = $l_condition_info[0];

            if ($p_param['p_strValue'] > 0)
            {
                $l_condition = $l_table . '__id = ' . $p_param['p_strValue'];
            } // if

            $l_value               = $this->get_array_data($l_table, $l_table . '__id', $l_condition);
            $p_param['p_strValue'] = $l_value;
        } // if

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        // Show InfoIcon
        $l_strOut = $this->getInfoIcon(
                $p_param
            ) . '<input ' . $p_param['name'] . ' ' . 'type=\'text\' ' . $p_param['p_strID'] . ' ' . $p_param['p_strTitle'] . ' ' . $p_param['p_strClass'] . ' ' . $p_param['p_bDisabled'] . ' ' . $p_param['p_bReadonly'] . ' ' . $p_param['p_strStyle'] . ' ' . $p_param['p_strValue'] . ' ' . $p_param['p_nTabIndex'] . ' ' . $p_param['p_nSize'] . ' ' . $p_param['p_nMaxLen'] . ' ' . $p_param['p_onMouseOver'] . ' ' . $p_param['p_onMouseOut'] . ' ' . $p_param['p_onChange'] . ' ' . $p_param['p_onClick'] . ' ' . $p_param['p_onKeyPress'] . ' ' . $p_param['p_onKeyUp'] . ' ' . $p_param['p_dataIdentifier'] . " " . $p_param['p_onKeyDown'] . ' ' . $p_param['p_additional'] . ' ' . '/>';

        /* Attach WIKI Link */
        $l_strOut .= $this->attach_wiki($p_param);

        if (isset($p_param['p_strSuggest']))
        {
            if (isset($p_param['p_strSuggestView']))
            {
                $l_suggestField = $p_param['p_strSuggestView'];
            }
            else
            {
                $l_suggestField = $p_param['name'];
            }

            if (isset($p_param['p_strSuggestParameters']))
            {
                $l_parameters = $p_param['p_strSuggestParameters'];
            }
            else $l_parameters = '';

            $l_strOut .= '<input type=\'hidden\' value=\'' . $p_param['p_strSelectedID'] . '\' name=\'' . $p_param['p_strSuggestHidden'] . '\' id=\'' . $p_param['p_strSuggestHidden'] . '\'>';

            $l_strOut .= '<script type=\'text/javascript\'>' . 'new idoit.Suggest(\'' . $p_param['p_strSuggest'] . '\', \'' . $l_suggestField . '\', \'' . $p_param['p_strSuggestHidden'] . '\', {' . $l_parameters . '});' . '</script>';
        }

        return $l_strOut;
    }

    /**
     * Returns the data from a table in an array.
     *
     * @param   string $p_strTablename
     * @param   string $p_order
     * @param   string $p_condition
     *
     * @return  array
     */
    public function get_array_data($p_strTablename, $p_order = null, $p_condition = null)
    {
        $l_tblres = isys_glob_get_data_by_table($p_strTablename, null, null, $p_order, $p_condition);

        if ($l_tblres !== null && count($l_tblres) > 0)
        {
            $l_tblrow = $l_tblres->get_row();

            return _L($l_tblrow[$p_strTablename . "__title"]);
        } // if

        return null;
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

        if (is_null($p_param['p_strValue']) && isset($p_param['default']))
        {
            $p_param['p_strValue'] = $p_param['default'];
        } // if

        if ($p_param['p_strSuggest'] && $p_param["p_strSuggestParameters"] && $p_param['p_strValue'] > 0)
        {
            $p_param['p_strSelectedID'] = $p_param['p_strValue'];

            preg_match("/\".*.\"/", $p_param["p_strSuggestParameters"], $l_matches);
            $l_condition_info = explode(',', trim(str_replace('"', '', $l_matches[0])));
            $l_table          = $l_condition_info[0];

            $l_value               = $this->get_array_data($l_table, $l_table . '__id', $l_table . '__id = ' . $p_param['p_strValue']);
            $p_param['p_strValue'] = $l_value;
        } // if

        if ($p_param['p_bEditMode'] == '1')
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        if ($p_param['p_bInvisible'] == true)
        {
            return '';
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

        return $this->getInfoIcon($p_param) . '<span' . $p_param['p_strStyle'] . '>' . html_entity_decode(
            $p_param['p_strValue'],
            null,
            $GLOBALS['g_config']['html-encoding']
        ) . '</span>';
    } // function
} // class