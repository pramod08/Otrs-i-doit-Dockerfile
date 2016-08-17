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
class isys_ajax_handler_tree_object extends isys_ajax_handler_tree
{
    /**
     * Initialization for this AJAX request.
     */
    public function init()
    {
        parent::init();
    } // function

    /**
     * Method which defines, if the hypergate needs to be run.
     *
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function
} // class