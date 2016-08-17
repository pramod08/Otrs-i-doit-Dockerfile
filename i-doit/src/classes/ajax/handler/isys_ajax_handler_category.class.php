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
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_category extends isys_ajax_handler
{
    /**
     * Initialization method.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function init()
    {
        isys_application::instance()->template->display("file:" . $this->m_smarty_dir . "templates/content/main_groups.tpl");

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate has to be included for this handler.
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function needs_hypergate()
    {
        return true;
    } // function
} // class