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
 * Smarty plugin for some data ONLY for view mode!
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_onlinehelp extends isys_smarty_plugin_f implements isys_smarty_plugin
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
     * @return  array
     */
    public static function get_meta_map()
    {
        return [];
    } // function

    /**
     * Method for processing the viewmode.
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

        if (isset($p_param["p_strHelp"]))
        {
            $l_url = $this->generate_url($p_param["p_strHelp"]);

            return '<a href="' . $l_url . '">' . $p_param["p_strValue"] . '</a>';
        } // if

        return 'No help file specified!';
    } // function

    /**
     * Method for preparing an URL.
     *
     * @param   string $p_help
     *
     * @return  string
     */
    public function generate_url($p_help)
    {
        return isys_helper_link::create_url(
            [
                C__GET__MODULE      => 'cmdb',
                C__CMDB__GET__POPUP => 'onlinehelp',
                'helpFile'          => $p_help
            ]
        );
    } // function

    /**
     * Method for processing the editmode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        return $this->navigation_view($p_tplclass, $p_param);
    } // function
} // class