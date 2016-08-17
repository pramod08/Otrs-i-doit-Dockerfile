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
 * Event class
 *
 * @package    i-doit
 * @subpackage Events
 * @author     Dennis Stücken <dstuecken@i-doit.org> 2006-07-24
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_event_task_new extends isys_event_task
{

    /**
     * @desc handle notification
     * @return boolean
     */
    public function handle_event()
    {

    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
}