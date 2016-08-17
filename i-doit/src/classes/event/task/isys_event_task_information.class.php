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
class isys_event_task_information extends isys_event_task
{

    private $m_end_time = null;
    private $m_enddate = null;
    private $m_startdate = null;
    private $m_task_description = null;

    /**
     * @desc handle notification
     *
     * @return bool
     */
    public function handle_event()
    {
        global $g_comp_template;

        $this->set_initiator();
        $this->set_email($this->get_initiator_email());

        $g_comp_template->assign("g_end_time", $this->m_end_time);
        $g_comp_template->assign("g_startdate", $this->m_startdate);
        $g_comp_template->assign("g_enddate", $this->m_enddate);
        $g_comp_template->assign("g_task_description", $this->m_task_description);

        return $this->_mail();
    }

    /**
     * @param      $p_workflow_id
     * @param null $p_email
     * @param null $p_cc
     * @param null $p_processor
     * @param null $p_end_time
     * @param null $p_startdate
     * @param null $p_enddate
     * @param null $p_task_description
     */
    public function __construct($p_workflow_id, $p_email = null, $p_cc = null, $p_processor = null, $p_end_time = null, $p_startdate = null, $p_enddate = null, $p_task_description = null)
    {
        parent::__construct();

        global $g_comp_session;
        global $g_comp_database;

        $l_session = $g_comp_session->get_session_data();

        $this->m_end_time         = $p_end_time;
        $this->m_startdate        = $p_startdate;
        $this->m_enddate          = $p_enddate;
        $this->m_task_description = $p_task_description;

        $this->init(
            C__EMAIL_TEMPLATE__TASK__BEFORE_ENDDATE,
            $p_workflow_id,
            0,
            (empty($p_processor)) ? isys_component_dao_user::instance($g_comp_database)
                ->get_user_title($l_session["isys_user_session__isys_obj__id"]) : $p_processor,
            $p_email,
            $p_cc
        );
    }
}
