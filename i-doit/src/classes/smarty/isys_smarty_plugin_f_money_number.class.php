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
 * Smarty plugin for formating money numbers
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_money_number extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
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

        if ($p_param["p_bEditMode"] == "1")
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // function

        // Format number.
        $this->format($p_param);

        return $this->getInfoIcon($p_param) . $p_param["p_strValueFormatted"] . ' <span class="bold">' . $p_param['p_strMonetary'] . '</span>';
    } // function

    /**
     * Format numbers
     *
     * @param array &$p_param
     */
    public function format(&$p_param)
    {
        global $g_comp_database;

        $l_arSessData = $_SESSION["session_data"];

        $l_objLoc = isys_locale::get($g_comp_database, $l_arSessData['isys_user_session__isys_obj__id']);

        if (is_null($p_param["p_strValue"]) && isset($p_param["default"]))
        {
            $p_param["p_strValue"] = $p_param["default"];
        } // if

        // Decimal seperator from the user configuration.
        $l_monetary     = $l_objLoc->fmt_monetary($p_param["p_strValue"]);
        $l_monetary_tmp = explode(" ", $l_monetary);

        $p_param["p_strValueFormatted"] = $l_monetary_tmp[0];
        $p_param["p_strMonetary"]       = $l_monetary_tmp[1];
    } // function

    /**
     *
     *
     * @see     isys_smarty_plugin_f_text  For all parameters.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.de>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if ($p_param["p_bEditMode"] == "0")
        {
            return $this->navigation_view($p_tplclass, $p_param);
        } // if

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName  = $p_param["name"];

        // default value should only be on view
        unset($p_param['default']);

        // Format number.
        $this->format($p_param);

        $l_info_icon                  = $this->getInfoIcon($p_param);
        $p_param['p_bInfoIconSpacer'] = 0;

        return $l_info_icon . '<span class="money-appendix">' . isys_factory::get_instance('isys_smarty_plugin_f_text')
            ->navigation_edit($p_tplclass, $p_param) . '<strong class="money-sign">' . $p_param['p_strMonetary'] . '</strong></span>';
    } // function
} // class