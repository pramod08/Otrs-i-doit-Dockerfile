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
 * Smarty plugin for multiselection
 *
 * @deprecated  Do not use this anymore!
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_multiselect extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return ['p_strTable'];
    } // function

    /**
     * Viewmode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @throws  Exception
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_params = null)
    {
        if ($p_params === null)
        {
            $p_params = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_dialog";
        $this->m_strPluginName  = $p_params["name"];

        $p_params['p_strStyle'] = 'border:0;';

        if (empty($p_params['ajaxURL']) && empty($p_params['data']))
        {
            if (isset($p_params['emptyMessage']) && !empty($p_params['emptyMessage']))
            {
                return '<span class="ml20">' . _L($p_params['emptyMessage']) . '</span>';
            } // if

            return '<span class="ml20">' . _L('LC__UNIVERSAL__EMPTY') . '</span>';
        } // if

        return $this->prepare_html($p_params) . $this->prepare_script($p_params, 0);
    } // function

    /**
     * Editmode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_params = null)
    {
        if ($p_params === null)
        {
            $p_params = $this->m_parameter;
        } // if

        return $this->prepare_html($p_params) . $this->prepare_script($p_params, 1);
    } // function

    /**
     * Method for preparing the HTML to render the form-fields.
     *
     * @param   array $p_params
     *
     * @return  string
     */
    private function prepare_html($p_params)
    {
        $l_value = $l_style = '';

        $l_data = $p_params['p_arData'];

        if (is_string($p_params['p_arData']))
        {
            $l_data = unserialize($p_params['p_arData']);
        } // if

        if ($p_params['p_strStyle'])
        {
            $l_style = 'style="' . $p_params['p_strStyle'] . '"';
        } // if

        if (is_array($l_data))
        {
            $l_value = implode(',', $l_data);
        } // if

        return '<div class="multisuggest"' . $l_style . '>' . '<input type="hidden" id="' . $p_params['name'] . '__HIDDEN" name="' . $p_params['name'] . '__HIDDEN" value="' . $l_value . '" onChange="' . $p_params['p_onChange'] . '" class="' . $p_params['p_strClass'] . '__HIDDEN" />' . '<div id="container_' . $p_params['name'] . '" class="auto">' . '<div class="default" style="display:none;"></div>' . '<ul class="feed"></ul>' . '</div>' . '</div>';
    } // function

    /**
     * Method for preparing the javascript, to initialize the multiselect field.
     *
     * @param   array   $p_params
     * @param   integer $p_editmode
     *
     * @return  string
     */
    private function prepare_script($p_params, $p_editmode = 0)
    {
        $l_main_data = '[]';
        if (!isset($p_params['data']) || $p_params['data'] == 'null')
        {
            $p_params['data'] = [];
            $l_main_data      = '';
        }
        else if (is_string($p_params['data']))
        {
            $l_main_data = $p_params['data'];
        } // if

        // Remove unwanted chars. This is a workaround for multiedit.
        $p_params['jsname'] = str_replace(
            [
                '[',
                ']',
                '-'
            ],
            '',
            $p_params['name']
        );

        $l_data = $p_params['p_arData'];

        if (is_string($p_params['p_arData']))
        {
            $l_data = unserialize($p_params['p_arData']);
        } // if

        // We have to use addslashes for the data, because we assign JSON strings which have " and ' inside.
        return '<script type="text/javascript">' . 'window.mselect_' . $p_params['jsname'] . ' = new idoit.multiSelect("' . $p_params['name'] . '__HIDDEN", ' . '"container_' . $p_params['name'] . '", {' . 'ajaxURL:"' . $p_params['ajaxURL'] . '", ' . 'data:\'' . $l_main_data . '\', ' . 'table:"' . $p_params['p_strTable'] . '", ' . 'editmode:' . intval(
            $p_editmode
        ) . ', ' . 'mainData:' . isys_format_json::encode(
            $l_data
        ) . ', ' . 'inputFields:"' . $p_params['name'] . '", ' . 'inputId:"' . $p_params['name'] . '"}' . ');' . '</script>';
    } // function
} // class