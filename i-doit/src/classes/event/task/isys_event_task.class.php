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
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
abstract class isys_event_task extends isys_library_mail implements isys_event
{

    /**
     * @var
     */
    protected $m_cc;

    /**
     * @var
     */
    protected $m_contact_id;

    /**
     * @var
     */
    protected $m_current_template;

    /**
     * @var
     */
    protected $m_description;

    /**
     * @var
     */
    protected $m_email;

    /**
     * @var
     */
    protected $m_initiator;

    /**
     * @var
     */
    protected $m_initiator_email;

    /**
     * @var
     */
    protected $m_message;

    /**
     * @var
     */
    protected $m_object_id;

    /**
     * @var
     */
    protected $m_status;

    /**
     * @var
     */
    protected $m_subject;

    /**
     * @var
     */
    protected $m_template_map;

    /**
     * @var
     */
    protected $m_workflow_id;

    /**
     * @var string
     */
    private $m_smarty_dir;

    /**
     * @param $p_email
     */
    public function set_email($p_email)
    {
        $this->m_email = $p_email;
    }

    /**
     * @param $p_cc
     */
    public function set_cc($p_cc)
    {
        $this->m_cc = $p_cc;
    }

    /**
     * @param $p_message
     */
    public function set_message($p_message)
    {
        $this->m_message = $p_message;
    }

    /**
     * @param $p_template
     */
    public function set_current_template($p_template)
    {
        $this->m_current_template = $p_template;
    }

    /**
     * @param $p_email
     */
    public function set_initiator_email($p_email)
    {
        $this->m_initiator_email = $p_email;
    }

    /**
     * @return mixed
     */
    public function get_initiator_email()
    {
        return $this->m_initiator_email;
    }

    /**
     * Get compiled subject template.
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @global isys_component_template $g_comp_template
     * @return string
     */
    public function get_subject()
    {
        global $g_comp_template;

        /* Write actual template in file */
        $l_templateData = $this->get_current_template();
        $l_template = 'string:' . $l_templateData['subject'];

        if (!empty($_SERVER["SERVER_ADDR"]))
        {
            $g_comp_template->assign("g_ip", isys_settings::get('workflows.mail.ip-address', $_SERVER['SERVER_ADDR']));
            $g_comp_template->assign("g_hostname", isys_settings::get('workflows.mail.hostname', gethostbyaddr($_SERVER['SERVER_ADDR'])));
            $g_comp_template->assign("g_http", 'http' . ($_SERVER['HTTPS'] ? 's' : ''));
        }
        else
        {
            $g_comp_template->assign("g_ip", isys_settings::get('workflows.mail.ip-address', C__SERVER_ADDR));
            $g_comp_template->assign("g_hostname", isys_settings::get('workflows.mail.hostname', C__SERVER_NAME));
            $g_comp_template->assign("g_http", 'http' . (C__HTTPS_ENABLED ? 's' : ''));
        }

        /**
         * @desc fetch template
         */
        $l_contents = $g_comp_template->fetch($l_template);

        return $l_contents;
    }

    /**
     * Sets subject.
     *
     * @param string $p_subject
     */
    public function set_subject($p_subject)
    {
        $this->m_subject = $p_subject;
    }

    /**
     * @return mixed
     */
    public function get_task_id()
    {
        return $this->m_workflow_id;
    }

    /**
     * @return mixed
     */
    public function get_contact_id()
    {
        return $this->m_contact_id;
    }

    /**
     * @return mixed
     */
    public function get_email()
    {
        return $this->m_email;
    }

    /**
     * @return mixed
     */
    public function get_message()
    {
        return $this->m_message;
    }

    /**
     * @return mixed
     */
    public function get_cc()
    {
        return $this->m_cc;
    }

    /**
     * @return mixed
     */
    public function get_current_template()
    {
        return $this->m_template_map[$this->m_current_template];
    }

    /**
     * @return string
     */
    public function build_link()
    {
        global $g_config;

        return $g_config['www_dir'] . isys_helper_link::create_url(
            [
                C__CMDB__GET__TREEMODE => C__WF__VIEW__TREE,
                C__CMDB__GET__VIEWMODE => C__WF__VIEW__DETAIL__GENERIC,
                C__WF__GET__ID         => $this->m_workflow_id
            ]
        );
    }

    /**
     * @desc returns the template
     *
     * @return string
     */
    public function get_template()
    {
        /* @var $g_comp_template Smarty */
        global $g_comp_template;

        /* Write actual template in file */
        $l_templateData = $this->get_current_template();
        $l_template = 'string:' . $l_templateData['body'];

        if (!empty($_SERVER["SERVER_ADDR"]))
        {
            $g_comp_template->assign("g_ip", isys_settings::get('workflows.mail.ip-address', $_SERVER['SERVER_ADDR']));
            $g_comp_template->assign("g_hostname", isys_settings::get('workflows.mail.hostname', gethostbyaddr($_SERVER['SERVER_ADDR'])));
            $g_comp_template->assign("g_http", 'http' . ($_SERVER['HTTPS'] ? 's' : ''));
        }
        else
        {
            $g_comp_template->assign("g_ip", isys_settings::get('workflows.mail.ip-address', C__SERVER_ADDR));
            $g_comp_template->assign("g_hostname", isys_settings::get('workflows.mail.hostname', C__SERVER_NAME));
            $g_comp_template->assign("g_http", 'http' . (C__HTTPS_ENABLED ? 's' : ''));
        }

        $g_comp_template->assign("g_email_template", $l_template);

        /**
         * @desc fetch template
         */
        $l_contents = $g_comp_template->fetch($l_template);

        return $l_contents;
    }

    /**
     * @desc returns the title of the task
     *
     * @return string
     */
    public function get_task_title()
    {
        global $g_comp_database;

        $l_dao_workflow = new isys_workflow_dao($g_comp_database);

        return $l_dao_workflow->get_title_by_id($this->get_task_id());
    }

    /**
     * @desc   returns an array with all available
     * task information for further processing
     * in templates
     *
     * @authro Selcuk Kekec <skekec@i-doit.com>
     * @return string
     */
    public function get_task()
    {
        global $g_comp_database;
        $l_return = [];

        $l_dao          = new isys_cmdb_dao($g_comp_database);
        $l_dao_workflow = new isys_workflow_dao($g_comp_database);
        $l_workflowRes  = $l_dao_workflow->get_workflows($this->get_task_id());

        if ($l_workflowRes->num_rows())
        {
            $l_row    = $l_workflowRes->get_row();
            $l_return = [
                'id'        => $l_row['isys_workflow__id'],
                'link'      => 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['SERVER_ADDR'] . $this->build_link(),
                'query'     => $this->build_link(),
                'category'  => $l_dao_workflow->get_category($this->get_task_id()),
                'type'      => '',
                'title'     => $l_row['isys_workflow__title'],
                'status'    => _L($l_row['isys_workflow_action_type__title']),
                'message'   => $l_dao_workflow->get_message($this->get_task_id()),
                'contactID' => $l_row['isys_workflow__isys_contact__id'],
            ];

            $l_objects = $l_dao_workflow->get_assgined_objects($l_row['isys_workflow__id']);

            foreach ($l_objects as $l_assigned_object_id)
            {
                $l_assObj = $l_dao->get_object_by_id($l_assigned_object_id, true)
                    ->get_row();

                $l_return['objects'][] = [
                    'id'     => $l_assObj['isys_obj__id'],
                    'title'  => $l_assObj['isys_obj__title'],
                    'link'   => self::object_link($l_assObj['isys_obj__id']),
                    'status' => $l_assObj['isys_obj__status'],
                    'type'   => _L($l_assObj['isys_obj_type__title']),
                ];
            }

            $l_contacts = $l_dao_workflow->get_assigned_contacts($l_row['isys_workflow__id']);

            foreach ($l_contacts as $l_assigned_contact_id)
            {
                $l_sql = "SELECT * FROM isys_obj " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " .
                    "LEFT JOIN isys_cats_person_group_list ON isys_cats_person_group_list__isys_obj__id = isys_obj__id " .
                    "LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id " . "WHERE isys_obj__id = " . $l_dao_workflow->convert_sql_id(
                        $l_assigned_contact_id
                    ) . ";";

                $l_res = $l_dao_workflow->retrieve($l_sql);

                if ($l_res->num_rows())
                {
                    $l_contactData = $l_res->get_row();

                    $l_return['contacts'][] = [
                        'id'    => $l_contactData['isys_obj__id'],
                        'title' => $l_contactData['isys_obj__title'],
                        'link'  => self::object_link($l_contactData['isys_obj__id']),
                        'type'  => _L($l_contactData['isys_obj_type__title'])
                    ];
                }
            }

            $l_return['initiator'] = $this->m_initiator;
            $l_return['actor']     = $this->get_actor($_SESSION['session_data']['isys_user_session__isys_obj__id']);
        }

        return $l_return;
    }

    /**
     * @param $p_objID
     *
     * @return array
     */
    public function get_actor($p_objID)
    {
        global $g_comp_database;
        $l_dao        = new isys_cmdb_dao_category_s_person_master($g_comp_database);
        $l_personData = $l_dao->get_data(null, $_SESSION['session_data']['isys_user_session__isys_obj__id'])
            ->get_row();

        return [
            'id'    => $l_personData['isys_obj__id'],
            'title' => $l_personData['isys_obj__title'],
            'type'  => _L($l_personData['isys_obj_type__title']),
            'link'  => self::object_link($l_personData['isys_obj__id']),
        ];
    }

    /**
     * @desc get email of current contact
     *
     * @return string
     */
    public function get_contact_email()
    {
        global $g_comp_database;
        $l_contact_dao = new isys_cmdb_dao_category_s_person_master($g_comp_database);

        $l_data = $l_contact_dao->get_data($this->m_contact_id);
        if (is_object($l_data))
        {
            $l_row   = $l_data->get_row();
            $l_email = $l_row["isys_cats_person_list__mail_address"];
        }
        else $l_email = '';

        return $l_email;
    }

    /**
     * @desc handles the event
     *
     * @return boolean
     */
    public function handle_event()
    {

    }

    /**
     * Intitialize the task
     *
     * @param      $p_template
     * @param      $p_task_id
     * @param      $p_contact_id
     * @param null $p_name
     * @param null $p_email
     * @param null $p_cc
     *
     * @return bool
     */
    public function init($p_template, $p_task_id, $p_contact_id, $p_name = null, $p_email = null, $p_cc = null)
    {
        global $g_comp_template;

        $this->set_charset('UTF-8');
        $this->m_workflow_id = $p_task_id;
        $this->m_contact_id  = $p_contact_id;
        $this->set_current_template($p_template);

        $this->set_subject("[i-doit] Workflow: " . $this->get_object_name() . " " . $this->get_task_title());

        $this->set_initiator();
        $l_taskData = $this->get_task();
        $g_comp_template->assign("g_message", $this->get_message());
        $g_comp_template->assign("g_task", $l_taskData);
        $g_comp_template->assign("g_link", $this->build_link());
        $g_comp_template->assign("g_processors", $p_name);

        if (is_numeric($p_contact_id) && $p_contact_id > 0)
        {
            $this->set_email($this->get_contact_email());
        }
        else
        {
            $this->set_email($p_email);
        }

        if (is_array($p_cc))
        {
            foreach ($p_cc as $l_cc)
            {
                if ($this->check_address($l_cc))
                {
                    $this->AddCC($l_cc);
                }
            }
        }
        else
        {
            $this->set_cc($this->get_parameter_cc());
        }

        if (empty($this->m_description))
        {
            $this->m_description = $p_template;
        }

        $this->handle_event();

        return true;
    }

    /**
     * @desc return object name
     *
     * @return string
     */
    protected function get_object_name()
    {
        global $g_comp_database;
        global $g_comp_template_language_manager;

        $l_obj_id = $this->get_object_id();

        if ($l_obj_id > 0)
        {
            $l_cmdb_dao = new isys_cmdb_dao($g_comp_database);
            $l_object   = $l_cmdb_dao->get_obj_name_by_id_as_string($l_obj_id);

            $l_obj_type_id = $l_cmdb_dao->get_objTypeID($l_obj_id);
            $l_obj_type    = $l_cmdb_dao->get_objtype_name_by_id_as_string($l_obj_type_id);

            $l_obj_type = _L($l_obj_type);
        }
        else $l_obj_type = '';

        return (empty($l_object)) ? null : $l_obj_type . "/" . $l_object . ":";
    }

    /**
     * @return int
     */
    protected function get_object_id()
    {
        global $g_comp_database;

        if ($this->m_workflow_id)
        {
            $l_dao_workflow = new isys_workflow_dao($g_comp_database);

            $this->m_object_id = $l_dao_workflow->get_object($this->m_workflow_id);
        }

        return $this->m_object_id;
    }

    /**
     * Build link to i-doit object
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @global array     $g_config
     *
     * @param string|int $p_objID
     *
     * @return string
     */
    protected function object_link($p_objID)
    {
        global $g_config;

        return 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['SERVER_ADDR'] . $g_config['www_dir'] . 'index.php' . isys_helper_link::create_url(
            [C__CMDB__GET__OBJECT => $p_objID]
        );
    }

    /**
     * @desc sets the initiator of the task and assigns it to smarty as g_initiator
     *
     * @return true
     */
    protected function set_initiator()
    {
        global $g_comp_database;
        global $g_comp_template;

        $l_user_id = $this->get_object_id();

        $l_contact_dao = new isys_cmdb_dao_category_s_person_master($g_comp_database);
        $l_data        = $l_contact_dao->get_data(null, $l_user_id);

        $l_row       = $l_data->get_row();
        $l_firstname = $l_row["isys_cats_person_list__first_name"];
        $l_lastname  = $l_row["isys_cats_person_list__last_name"];
        $l_username  = $l_row["isys_cats_person_list__title"];

        $l_email = $l_row["isys_cats_person_list__mail_address"];

        $this->set_initiator_email($l_email);

        $this->m_initiator = [
            "fullname" => $l_firstname . " " . $l_lastname,
            "username" => $l_username,
            "id"       => $l_user_id
        ];

        $g_comp_template->assign("g_initiator", $this->m_initiator);

        return true;
    }

    /**
     * @desc THE mail function
     *
     * @uses isys_library_mail
     *
     */
    protected function _mail()
    {
        if ($this->check_address($this->get_email()))
        {
            /* Configure mail */
            $this->AddAddress($this->get_email());

            $l_ccs = explode(",", $this->get_cc());
            if (is_array($l_ccs))
            {
                foreach ($l_ccs as $l_cc)
                {
                    $l_cc = str_replace(" ", "", $l_cc);

                    if ($this->check_address($l_cc))
                    {
                        $this->AddCC($l_cc);
                    }
                }
            }
            else
            {
                if ($this->check_address($this->get_cc()))
                {
                    $this->AddCC($this->get_cc());
                }
            }

            $this->Subject = isys_settings::get('system.email.subject-prefix', '') . $this->get_subject();
            $this->Body    = $this->get_template();

            /* Use SMTP and send */
            $this->IsSMTP();

            if ($this->Send())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * @desc get cc parameter from template engine
     * @return string
     *
     */
    private function get_parameter_cc()
    {
        global $g_comp_database;

        $l_dao_workflow = new isys_workflow_dao_action($g_comp_database);
        $l_cc_to        = '';

        /* Get CC-TO ------------------------------------------------------------------------- */
        /* ----------------------------------------------------------------------------------- */
        $l_actions = $l_dao_workflow->get_actions(
            $this->m_workflow_id,
            null,
            C__WORKFLOW__ACTION__TYPE__NEW
        );

        if (method_exists($l_actions, 'get_row'))
        {
            $l_action_new = $l_actions->get_row();

            $l_action_parameters = $l_dao_workflow->get_action_parameters(
                $l_action_new["isys_workflow_2_isys_workflow_action__isys_workflow_action__id"],
                null,
                "cc_to"
            );

            $l_action_parameter = $l_action_parameters->get_row();
            $l_cc_to            = $l_action_parameter["isys_workflow_action_parameter__string"];
        }

        return $l_cc_to;
    }

    /**
     * Assigns all needed task templates to an array
     *
     * @return bool
     * @version Niclas Potthast <npotthast@i-doit.org> - 2007-10-01
     */
    private function build_template_map()
    {
        global $g_comp_session, $g_comp_database;

        $l_task_events = [
            "C__EMAIL_TEMPLATE__TASK__BEFORE_ENDDATE",
            "C__EMAIL_TEMPLATE__TASK__NOTIFICATION",
            "C__EMAIL_TEMPLATE__TASK__ACCEPT",
            "C__EMAIL_TEMPLATE__TASK__STATUS_OPEN",
            "C__EMAIL_TEMPLATE__TASK__STATUS_DUE",
            "C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED",
            "C__EMAIL_TEMPLATE__TASK__COMPLETION_ACCEPTED",
        ];

        $l_dao = new isys_cmdb_dao($g_comp_database);

        foreach ($l_task_events as $l_eventConst)
        {
            $l_eventData = [
                'subject' => '',
                'body'    => '',
            ];

            $l_sql = "SELECT * FROM isys_task_event WHERE isys_task_event__const = " . $l_dao->convert_sql_text($l_eventConst) . ";";

            $l_res = $l_dao->retrieve($l_sql);

            if ($l_res->num_rows())
            {
                $l_row = $l_res->get_row();

                $l_eventData = [
                    'subject' => $l_row['isys_task_event__email_subject_' . $g_comp_session->get_language()],
                    'body'    => $l_row['isys_task_event__email_body_' . $g_comp_session->get_language()],
                    //'tpl'     => $l_row['isys_task_event__tpl'],
                ];
            }

            $this->m_template_map[constant($l_eventConst)] = $l_eventData;
        }

        return true;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        global $g_absdir;
        $this->m_smarty_dir = $g_absdir . "/src/themes/default/smarty/templates/email/task/";

        $this->build_template_map();

        parent::__construct();
    }

}