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
class isys_event_task_notification extends isys_event_task
{

    /**
     * @desc handle notification
     *
     * @return boolean
     */
    public function handle_event()
    {
        return $this->_mail();
    }

    /**
     * @param      $p_template
     * @param      $p_workflow_id
     * @param      $p_contact_id
     * @param null $p_name
     * @param null $p_cc
     * @param null $p_email
     */
    public function __construct($p_template, $p_workflow_id, $p_contact_id, $p_name = null, $p_cc = null, $p_email = null)
    {
        try
        {
            parent::__construct();

            // Check registry wheather mailing is allowed or not.
            $l_notify = (int) isys_tenantsettings::get('workflow.notify', 15);

            if (C__WORKFLOW__MAIL__NOTIFICATION & $l_notify)
            {
                if (!is_null($p_cc))
                {
                    $this->set_cc($p_cc);
                } // if

                $this->init($p_template, $p_workflow_id, $p_contact_id, $p_name, $p_email);
            } // if
        }
        catch (Exception $e)
        {
            ; // Ignore it...
        } // try
    } // function
} // class