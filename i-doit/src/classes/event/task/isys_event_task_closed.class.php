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
class isys_event_task_closed extends isys_event_task
{

    /**
     * @desc handle notification
     *
     * @return bool
     */
    public function handle_event()
    {
        global $g_comp_template;
        global $g_comp_database;

        $this->set_initiator();
        $this->set_email($this->get_initiator_email());

        $g_comp_template->assign("g_description", $this->m_description);

        if ($this->m_status == C__TASK__STATUS__CANCEL) $g_comp_template->assign("g_task_state", "abgebrochen");

        return $this->_mail();
    }

    /**
     * @param      $p_template
     * @param      $p_workflow_id
     * @param      $p_contact_id
     * @param      $p_description
     * @param      $p_method_desc
     * @param null $p_email
     * @param null $p_cc
     * @param null $p_status
     */
    public function __construct($p_template, $p_workflow_id, $p_contact_id, $p_description, $p_method_desc, $p_email = null, $p_cc = null, $p_status = null)
    {
        try
        {
            parent::__construct();
            global $g_comp_session;

            $l_notify = (int) isys_tenantsettings::get('workflow.notify');

            if (C__WORKFLOW__MAIL__COMPLETED & $l_notify)
            {

                $this->m_description = $p_description;
                $this->m_status      = $p_status;

                isys_application::instance()->template->assign("g_measure", $p_method_desc);

                $l_session = $g_comp_session->get_session_data();

                $l_dao_user = isys_component_dao_user::instance(isys_application::instance()->database);

                $this->init($p_template, $p_workflow_id, $p_contact_id, $l_dao_user->get_user_title($l_session["isys_user_session__isys_obj__id"]), $p_email, $p_cc);
            } // if
        }
        catch (Exception $e)
        {
            ; // Ignore it...
        } // Try
    } // function
} // class