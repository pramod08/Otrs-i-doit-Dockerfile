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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_rt_synchronize_custom_fields extends isys_ajax_handler
{
    /**
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_template_language_manager;

        try
        {
            isys_factory::get_instance('isys_module_request_tracker')
                ->synchronize();
            echo "<img src='images/icons/infobox/green.png' class='m5 vam' /><span>" . $g_comp_template_language_manager->get(
                    'LC__REQUEST_TRACKER__SYNCHRONISATION__SUCCESSFUL'
                ) . "</span>";
        }
        catch (Exception $e)
        {
            echo "<img src='images/icons/infobox/red.png' class='m5 vam' /><span>" . $g_comp_template_language_manager->get(
                    'LC__REQUEST_TRACKER__SYNCHRONISATION__ERROR'
                ) . "</span>";
        } // if

        $this->_die();

        return true;
    } // function
} // class