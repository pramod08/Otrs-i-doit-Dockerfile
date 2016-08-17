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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_fullscreen extends isys_component_popup
{
    /**
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This will be called directly via JS / URL.
    } // function

    /**
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template|void
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Unpack module request.
        switch ($_POST['tpl'])
        {
            default:
            case 'license-warning':
                $l_template_file = (new isys_module_licence)->get_template_dir() . 'nagscreen.tpl';
                break;
        } // switch

        isys_application::instance()->template->activate_editmode()
            ->assign('params', isys_format_json::decode($_POST['parameters']))
            ->display($l_template_file);

        die;
    } // function
} // class