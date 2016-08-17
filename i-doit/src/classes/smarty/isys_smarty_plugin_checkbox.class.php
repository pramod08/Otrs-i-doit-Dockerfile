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
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_checkbox extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Navigation mode.
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

        if ($p_param['p_bEditMode'] != '1' || !isset($p_param['p_bEditMode']))
        {
            $p_param["p_bDisabled"] = true;
        } // if

        return $this->navigation_edit($p_tplclass, $p_param);
    } // function

    /**
     * Edit mode.
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

        if ($p_param["p_strID"])
        {
            $p_param["id"] = $p_param["p_strID"];
        } // if

        if (empty($p_param["id"]))
        {
            $p_param["id"] = $p_param["name"];
        } // if

        $l_attributes = [
            'id'   => $p_param["id"],
            'name' => $p_param["name"]
        ];

        if ($p_param["p_strClass"])
        {
            $l_attributes['class'] = $p_param["p_strClass"];
        } // if

        if ($p_param["p_strStyle"])
        {
            $l_attributes['style'] = $p_param["p_strStyle"];
        } // if

        if ($p_param["p_bDisabled"])
        {
            $l_attributes['disabled'] = "disabled";
        } // if

        if ($p_param["p_strOnClick"])
        {
            $l_attributes['onclick'] = $p_param["p_strOnClick"];
        } // if

        if ($p_param["p_bChecked"])
        {
            $l_attributes['checked'] = "checked";
        } // if

        if (isset($p_param["p_strValue"]))
        {
            $l_attributes['value'] = $p_param["p_strValue"];
        } // if

        if (!empty($p_param['p_dataIdentifier']))
        {
            $l_attributes['data-identifier'] = $p_param['p_dataIdentifier'];
        } // if

        $l_attribut_string = '';

        foreach ($l_attributes as $l_key => $l_value)
        {
            $l_attribut_string .= ' ' . $l_key . '="' . $l_value . '"';
        } // foreach

        if (empty($p_param["p_strTitle"]))
        {
            return isys_smarty_plugin_f::getInfoIcon($p_param) . '<input type="checkbox" ' . $l_attribut_string . ' />';
        } // if

        return isys_smarty_plugin_f::getInfoIcon($p_param) . '<label><input type="checkbox" ' . $l_attribut_string . " /> " . _L($p_param["p_strTitle"]) . "</label>";
    } // function
} // class