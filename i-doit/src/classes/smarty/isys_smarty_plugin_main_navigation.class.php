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
 * Smarty plugin for the main navigation.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_smarty_plugin_main_navigation extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not
     *
     * @return bool
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return [];
    } // function

    /**
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_arParam
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_arParam = null)
    {
        global $g_comp_session, $g_menu, $g_bDefaultTooltips;

        if ($p_arParam === null)
        {
            $p_arParam = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName  = $p_arParam["name"];

        if (!$g_comp_session->is_logged_in())
        {
            return "";
        }

        $l_strRet      = "";
        $l_ii          = 0;
        $l_active_menu = $g_menu->get_active_menuobj();
        // Iterate through menuobjects
        while ($l_ii < $g_menu->count_new_menuobj())
        {
            $l_mi      = $g_menu->get_menuobj_by_nr($l_ii++); // act menuItem
            $l_strLink = $l_mi->get_member('m_link');

            // Tabindex - add the tabindex_offset, given by template to the tab-value of each menuItem
            $p_arParam["p_nTabIndex"] = $p_arParam["p_nTabOffset"] + $l_mi->get_member('m_tab');

            // Choose class for correct display
            if ($l_mi->__get('name') == $l_active_menu)
            {
                $l_strClass = $p_arParam["p_strClassSelected"];
            }
            else
            {
                $l_strClass = $p_arParam["p_strClass"];
            } // if

            if ($g_bDefaultTooltips)
            {
                if (strlen($l_mi->get_member('m_rn_tooltip')) > 0)
                {
                    $p_arParam["p_strTitle"] = $l_mi->get_member('m_rn_tooltip');
                } // if
            } // if

            $l_strRet .= "<a id=\"mainnavi_" . $l_mi->get_member('m_name') . "\" href=\"" . $l_strLink . "\" ";

            if ($p_arParam["p_strTarget"])
            {
                $l_strRet .= "target=\"" . $p_arParam["p_strTarget"] . "\" ";
            } // if

            if ($l_strClass)
            {
                $l_strRet .= "class=\"" . $l_strClass . "\" ";
            } // if

            if ($p_arParam["p_strStyle"])
            {
                $l_strRet .= "style=\"" . $p_arParam["p_strStyle"] . "\" ";
            } // if

            $l_strRet .= "onclick=\"" . $l_mi->get_member(
                    'm_onclick'
                ) . ";" . "$$('#mainNavi a." . $p_arParam["p_strClassSelected"] . "').each(function(i){i.className='mainNaviLink';});" . "this.className='" . $p_arParam["p_strClassSelected"] . "'\" ";

            if (strlen($p_arParam["p_strTitle"]) > 0)
            {
                $l_strRet .= "title=\"" . $p_arParam["p_strTitle"] . "\" ";
            } // if

            $l_strRet .= "tabindex=\"" . $p_arParam["p_nTabIndex"] . "\" >" . "&nbsp;&nbsp;" . $l_mi->get_member('m_rn_title') . "&nbsp;&nbsp;" . "</a>\n";
        } // while

        return $l_strRet;
    } // function

    /**
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_arParam
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_arParam = null)
    {
        return $this->navigation_view($p_tplclass, $p_arParam);
    } // function
} // class