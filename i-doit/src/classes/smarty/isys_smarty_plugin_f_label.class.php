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
 * Smarty plugin for label fields.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_label extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Method for retrieving the output on editmode.
     *    'name': name (string);
     *    'ident': translation (string);
     *    'description': add optional description (string);
     *    'default': add optional default value (mixed);
     *    'mandatory': mark optional mandatory field (bool)
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

        $this->m_strPluginClass = "f_label";
        $this->m_strPluginName  = $p_param['name'];

        $l_description = null;

        if (isset($p_param['description']) && !empty($p_param['description']))
        {
            assert('is_string($p_param["description"]) && !empty($p_param["description"])');

            $l_description = PHP_EOL . '<p style="font-size: smaller;">' . _L($p_param['description']) . '</p>';
        } // if

        $l_mandatory = null;

        if (array_key_exists('mandatory', $p_param) && filter_var($p_param['mandatory'], FILTER_VALIDATE_BOOLEAN))
        {
            $l_mandatory = '<span class="red bold">*</span>';
        } // if

        return sprintf('<label for="%s" style="%s">%s</label>%s%s', $p_param['name'], $p_param['p_strStyle'], _L($p_param['ident']), $l_mandatory, $l_description);
    } // function

    /**
     * Method for retrieving the output on viewmode.
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

        $this->m_strPluginClass = "f_label";
        $this->m_strPluginName  = $p_param['name'];

        return _L($p_param['ident']);
    } // function
} // class