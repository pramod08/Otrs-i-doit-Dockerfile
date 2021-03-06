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
 * Smarty plugin for constants.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_image extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return false;
    } // function

    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return [""];
    } // function

    /**
     * View mode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = 'image';
        $this->m_strPluginName  = $p_param['name'];

        $this->getStandardAttributes($p_param);
        $this->getJavascriptAttributes($p_param);

        $l_strRet = '<a href="javascript:">';

        if (!empty($p_param["p_strLink"]))
        {
            $l_strRet = '<a href="' . $p_param['p_strLink'] . '">';
        } // if

        if (empty($p_param['p_bInvisible']))
        {
            $l_strRet .= '<img src="' . $p_param["p_strSrc"] . '" alt="' . $p_param["p_strAlt"] . '" ' . $p_param["p_strID"] . ' ' . $p_param["p_strTitle"] . ' ' . $p_param["p_strClass"] . ' ' . $p_param["p_strStyle"] . ' ' . $p_param["p_strValue"] . ' ' . $p_param["p_strTab"] . ' ' . $p_param["p_onMouseOver"] . ' ' . $p_param["p_onMouseOut"] . ' ' . $p_param["p_onClick"] . '>';
        } // if

        return $this->getInfoIcon($p_param) . $l_strRet . '</a>';
    } // function

    /**
     * Edit mode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    } // function
} // class