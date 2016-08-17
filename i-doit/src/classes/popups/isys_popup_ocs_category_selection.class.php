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
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_ocs_category_selection extends isys_component_popup
{
    /**
     *
     * @return string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        return $this->process(
            isys_helper_link::create_url(
                [
                    'mod'   => 'cmdb',
                    'popup' => 'ocs_category_selection'
                ]
            ),
            true
        );
    } // function

    /**
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template&
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_categories = [
            _L("LC__OBJTYPE__OPERATING_SYSTEM")            => "operating_system",
            _L("LC__CMDB__CATG__COMPUTING_RESOURCES__CPU") => C__CATG__CPU,
            _L("LC__CMDB__CATG__MEMORY")                   => C__CATG__MEMORY,
            _L("LC__CMDB__CATG__APPLICATION")              => C__CATG__APPLICATION,
            _L("LC__CMDB__CATG__NETWORK")                  => C__CATG__NETWORK,
            _L("LC__UNIVERSAL__DEVICES")                   => C__CATG__STORAGE,
            _L("LC__UNIVERSAL__DRIVES")                    => C__CATG__DRIVE,
            _L("LC__CMDB__CATG__GRAPHIC")                  => C__CATG__GRAPHIC,
            _L("LC__CMDB__CATG__SOUND")                    => C__CATG__SOUND,
            _L("LC__CMDB__CATG__MODEL")                    => C__CATG__MODEL,
            _L("LC__CMDB__CATG__UNIVERSAL_INTERFACE")      => C__CATG__UNIVERSAL_INTERFACE
        ];

        // Prepare new template for popup.
        $l_tplpopup = isys_component_template::instance();

        return $l_tplpopup->assign("file_body", "popup/ocs_category_selection.tpl")
            ->assign("categories", $l_categories);
    } // function
} // class