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
 * Smarty plugin for popups.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Wösten <awoesten@i-doit.org>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_popup extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Gets the map for Smarty Meta Map (SM²).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        // LF: "multiselect" is necessary for isys_cmdb_action_category_change.
        return [
            "p_strPopupType",
            "p_strSelectedID",
            "p_arData",
            "p_strTable",
            "p_strValue",
            "multiselect"
        ];
    } // function

    /**
     * Provides HTML code for viewing.
     *
     * @param   isys_component_template $p_tplclass Template
     * @param   array                   $p_params   Parameters
     *
     * @return  string  Returns null on error.
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_params = null)
    {
        return $this->navigation_edit($p_tplclass, $p_params);
    } // function

    /**
     * Provides HTML code for editing.
     *
     * @param   isys_component_template $p_tplclass Template
     * @param   array                   $p_params   Parameters
     *
     * @return  string  Returns null on error.
     */
    public function navigation_edit(isys_component_template &$p_tplclass, &$p_params = null)
    {
        if ($p_params === null)
        {
            $p_params = $this->m_parameter;
        } // if

        if (isset($p_params["p_strPopupType"]))
        {
            $l_popuptype = $p_params["p_strPopupType"];
            $l_classname = "isys_popup_" . $l_popuptype;

            if (class_exists($l_classname))
            {
                $l_instance = new $l_classname;
                if (@is_object($l_instance))
                {
                    if (isset($p_params['p_bEnableMetaMap']))
                    {
                        if ($p_params['p_bEnableMetaMap'])
                        {
                            $this->m_enableMetaMap = true;
                        }
                        else
                        {
                            $this->m_enableMetaMap = false;
                        }
                    }

                    $l_params = $p_params;
                    $l_return = $l_instance->handle_smarty_include($p_tplclass, $l_params);

                    $p_params["p_strValue"] = $l_params["p_strValue"];

                    return $l_return;
                }
            }
        }

        return null;
    } // function
} // class