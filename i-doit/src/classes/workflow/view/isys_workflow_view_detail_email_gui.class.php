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
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0 Thu Sept 26 14:38:38 CEST 2007
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_workflow_view_detail_email_gui extends isys_workflow_view_detail
{

    /**
     * @var string
     */
    private static $m_include_bottom = '';

    /**
     * @var array
     */
    private $m_routing = [
        'default'                                    => 'handle_settings',
        C__EMAIL_TEMPLATE__TASK__NOTIFICATION        => 'handle_notification',
        C__EMAIL_TEMPLATE__TASK__ACCEPT              => 'handle_accepted',
        C__EMAIL_TEMPLATE__TASK__COMPLETION_ACCEPTED => 'handle_completed',
        C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED       => 'handle_canceled',
    ];

    /**
     * @desc tom rules
     * @var array
     */
    private $m_rules;

    /**
     * @return int
     */
    public function get_id()
    {
        return C__WF__VIEW__DETAIL__EMAIL_GUI;
    }

    /**
     * @param array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return "i-manageIT::email.gui";
    }

    /**
     * @param array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);
    }

    /**
     * @return string
     */
    public function get_template_bottom()
    {
        return self::$m_include_bottom;
    } // function

    /**
     * @return string
     */
    public function get_template_top()
    {
        return "workflow/detail/workflow.tpl";
    }

    /**
     * @param int $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        parent::handle_navmode($p_navmode);
    }

    /**
     * @desc process
     * @return string
     */
    public function process()
    {
        // check rights
        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__DETAIL__EMAIL_GUI);

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_navbar->set_save_mode('formsubmit');

        $l_navbar->set_active(
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW/' . C__WF__VIEW__DETAIL__EMAIL_GUI),
            C__NAVBAR_BUTTON__SAVE
        )
            ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(false, C__NAVBAR_BUTTON__CANCEL)
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__PRINT)
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE);

        parent::process();
        $l_route_param = (!empty($_GET['tplID']) && is_numeric($_GET['tplID']) && method_exists(
                $this,
                $this->m_routing[$_GET['tplID']]
            )) ? $this->m_routing[$_GET['tplID']] : $this->m_routing['default'];

        $this->$l_route_param();

        return true;
    }

    /**
     * @return bool
     */
    public function get_list_id()
    {
        return false;
    }

    /**
     *
     */
    public function get_dao()
    {
    }

    /**
     * @param $p_value
     */
    public function set_value($p_value)
    {
        isys_tenantsettings::set('workflow.notify', $p_value);
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (is_array($_POST["reg_value"]))
        {
            $l_bam = 0;
            foreach ($_POST["reg_value"] as $l_val)
            {
                $l_bam += $l_val;
            }

            $this->set_value($l_bam);
        }

        return true;
    }

    /**
     *
     */
    private function handle_settings()
    {
        self::$m_include_bottom = 'workflow/email_gui.tpl';

        isys_application::instance()->template->assign("g_current_setting", (int) isys_tenantsettings::get('workflow.notify'));
    }

    /**
     *
     */
    private function handle_notification()
    {
        $this->handle_template(C__EMAIL_TEMPLATE__TASK__NOTIFICATION);
    }

    /**
     *
     */
    private function handle_accepted()
    {
        $this->handle_template(C__EMAIL_TEMPLATE__TASK__ACCEPT);
    }

    /**
     *
     */
    private function handle_completed()
    {
        $this->handle_template(C__EMAIL_TEMPLATE__TASK__COMPLETION_ACCEPTED);
    }

    /**
     *
     */
    private function handle_canceled()
    {
        $this->handle_template(C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED);
    }

    /**
     * @param null $p_statusID
     *
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function handle_template($p_statusID = null)
    {
        if (isset($_POST[C__GET__NAVMODE]) && $_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
        {
            $l_sql = "UPDATE isys_task_event SET " . "isys_task_event__email_subject_de = " . $this->m_dao_cmdb->convert_sql_text(
                    $_POST['email_de_subject']
                ) . " ," . "isys_task_event__email_subject_en = " . $this->m_dao_cmdb->convert_sql_text(
                    $_POST['email_en_subject']
                ) . " ," . "isys_task_event__email_body_de = " . $this->m_dao_cmdb->convert_sql_text(
                    $_POST['email_de_body']
                ) . " ," . "isys_task_event__email_body_en = " . $this->m_dao_cmdb->convert_sql_text(
                    $_POST['email_en_body']
                ) . " " . "WHERE isys_task_event__id = " . $this->m_dao_cmdb->convert_sql_id(($p_statusID)) . ";";

            $this->m_dao_cmdb->update($l_sql) && $this->m_dao_cmdb->apply_update();
        }

        global $g_comp_template;
        self::$m_include_bottom = 'workflow/email_tpl.tpl';

        if (!empty($p_statusID))
        {
            $l_sql = "SELECT * FROM isys_task_event WHERE isys_task_event__id = " . $this->m_dao_cmdb->convert_sql_id($p_statusID);

            $l_res = $this->m_dao_cmdb->retrieve($l_sql);

            if ($l_res->num_rows())
            {
                $l_row = $l_res->get_row();

                $g_comp_template->assign("email_de_subject", $l_row['isys_task_event__email_subject_de']);
                $g_comp_template->assign("email_en_subject", $l_row['isys_task_event__email_subject_en']);
                $g_comp_template->assign("email_de_body", $l_row['isys_task_event__email_body_de']);
                $g_comp_template->assign("email_en_body", $l_row['isys_task_event__email_body_en']);
            }
        }
    }
}