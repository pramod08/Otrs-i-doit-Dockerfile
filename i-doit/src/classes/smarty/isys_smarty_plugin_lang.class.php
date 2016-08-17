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
 * Smarty plugin for language constants
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_lang extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return false;
    } // function

    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public static function get_meta_map()
    {
        return ['ident'];
    } // function

    /**
     * Method for translating language constants.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_params = null)
    {
        global $g_comp_template_language_manager;

        if ($p_params === null)
        {
            $p_params = $this->m_parameter;
        } // if

        $this->m_strPluginClass = 'lang';
        $this->m_strPluginName  = $p_params['name'];

        if (isset($p_params['values']))
        {
            $l_values = $p_params['values'];
        }
        else $l_values = null;

        $l_strRet = (array_key_exists('ident', $p_params)) ? $g_comp_template_language_manager->get($p_params['ident'], $l_values) : null;

        if (!empty($p_params['truncate']))
        {
            $l_strRet = isys_glob_str_stop($l_strRet, intval($p_params['truncate']), '..');
        } // if

        if (isset($p_params['p_func']))
        {
            /*
             * possible functions: strtoupper, strtolower, ucfirst ...
             */
            $l_func = $p_params['p_func'];
            if (function_exists($l_func))
            {
                $l_strRet = $l_func($l_strRet);
            } // if
        } // if

        if ($p_params['p_bHtmlEncode'] || !isset($p_params['p_bHtmlEncode']))
        {
            $l_strRet = isys_glob_htmlentities($l_strRet);
        } // if

        return $l_strRet;
    } // function

    /**
     * This is an alias function of "navigation_view".
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_params = null)
    {
        if ($p_params === null)
        {
            $p_params = $this->m_parameter;
        } // if

        return $this->navigation_view($p_tplclass, $p_params);
    } // function
} // class