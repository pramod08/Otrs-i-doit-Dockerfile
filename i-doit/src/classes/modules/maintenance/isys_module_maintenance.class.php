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
 * Maintenance module class.
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_module_maintenance extends isys_module implements isys_module_interface, isys_module_authable
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = true;
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * Represents the name, of the corresponding auth class - "false" means, there is none.
     *
     * @var  string
     */
    const AUTH_CLASS_NAME = 'isys_maintenance_auth';
    /**
     * Variable which holds the maintenance DAO class.
     *
     * @var  isys_maintenance_dao
     */
    protected $m_dao = null;
    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * This number will be used to render "x" clients in the planning (and overview) list.
     *
     * @var  integer
     */
    protected $m_list_display_clients = 6;
    /**
     * Variable which the module request class.
     *
     * @var  isys_module_request
     */
    protected $m_modreq = null;
    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Static method for retrieving the (filesystem) path, to the modules templates.
     *
     * @static
     * @global  array $g_dirs
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tpl_dir()
    {
        return __DIR__ . DS . 'templates' . DS;
    } // function

    /**
     * Attach live status of object to contenttop header.
     *
     * @param   array $p_catdata
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function process_content_top($p_catdata)
    {
        if (isys_maintenance_dao::instance(isys_application::instance()->database)->is_in_maintenance($p_catdata['isys_obj__id']))
        {
            global $index_includes;

            $index_includes['contenttopobjectdetail'][] = __DIR__ . '/templates/contenttop/main_objectdetail.tpl';
        } // if
    } // function

    /**
     * Get related auth class for module.
     *
     * @return  isys_maintenance_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_auth()
    {
        return isys_maintenance_auth::instance();
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_maintenance
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;
        $this->m_db     = $p_req->get_database();
        $this->m_tpl    = $p_req->get_template();
        $this->m_dao    = new isys_maintenance_dao($this->m_db);

        return $this;
    } // function

    /**
     * Method for building the breadcrumb navigation.
     *
     * @param   array &$p_gets
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function breadcrumb_get(&$p_gets)
    {
        $l_return = [];
        $l_gets   = $this->m_modreq->get_gets();

        switch ($l_gets[C__GET__SETTINGS_PAGE])
        {
            default:
            case C__MAINTENANCE__OVERVIEW:
                $l_return[] = [
                    _L('LC__MAINTENANCE__OVERVIEW') => [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => $l_gets[C__GET__TREE_NODE],
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];
                break;

            case C__MAINTENANCE__PLANNING:
                $l_return[] = [
                    _L('LC__MAINTENANCE__PLANNING') => [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => $l_gets[C__GET__TREE_NODE],
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];
                break;

            case C__MAINTENANCE__PLANNING_ARCHIVE:
                $l_return[] = [
                    _L('LC__MAINTENANCE__PLANNING_ARCHIVE') => [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => $l_gets[C__GET__TREE_NODE],
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];
                break;

            case C__MAINTENANCE__MAILTEMPLATE:
                $l_return[] = [
                    _L('LC__MAINTENANCE__MAILTEMPLATES') => [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => $l_gets[C__GET__TREE_NODE],
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];
        } // switch

        return $l_return;
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_module_cmdb->build_tree();
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_dirs;

        if ($p_system_module === false)
        {
            $l_auth = $this->get_auth();
            $l_root = $p_tree->add_node(
                C__MODULE__MAINTENANCE . 0,
                $p_parent,
                _L('LC__MODULE__MAINTENANCE'),
                null,
                null,
                $g_dirs['images'] . 'icons/silk/wrench.png'
            );

            $p_tree->add_node(
                C__MODULE__MAINTENANCE . 1,
                $l_root,
                _L('LC__MAINTENANCE__OVERVIEW'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 1,
                        C__GET__SETTINGS_PAGE => C__MAINTENANCE__OVERVIEW,
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/table.png',
                (int) (!$_GET[C__GET__TREE_NODE] || $_GET[C__GET__TREE_NODE] == C__MODULE__MAINTENANCE . 1),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'overview')
            );

            $p_tree->add_node(
                C__MODULE__MAINTENANCE . 2,
                $l_root,
                _L('LC__MAINTENANCE__PLANNING'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 2,
                        C__GET__SETTINGS_PAGE => C__MAINTENANCE__PLANNING,
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/calendar_add.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__MAINTENANCE . 2),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'planning')
            );

            $p_tree->add_node(
                C__MODULE__MAINTENANCE . 3,
                $l_root,
                _L('LC__MAINTENANCE__PLANNING_ARCHIVE'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 3,
                        C__GET__SETTINGS_PAGE => C__MAINTENANCE__PLANNING_ARCHIVE,
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/calendar_delete.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__MAINTENANCE . 3),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'planning_archive')
            );

            $p_tree->add_node(
                C__MODULE__MAINTENANCE . 4,
                $l_root,
                _L('LC__MAINTENANCE__MAILTEMPLATES'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 4,
                        C__GET__SETTINGS_PAGE => C__MAINTENANCE__MAILTEMPLATE,
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/email.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__MAINTENANCE . 4),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'mailtemplate')
            );

            $p_tree->add_node(
                C__MODULE__MAINTENANCE . 5,
                $l_root,
                _L('LC__REPORT__VIEW__MAINTENANCE_EXPORT'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID        => C__MODULE__REPORT,
                        C__GET__TREE_NODE        => C__MODULE__REPORT . '5',
                        C__GET__REPORT_PAGE      => C__REPORT_PAGE__VIEWS,
                        C__GET__REPORT_REPORT_ID => 'isys_maintenance_reportview_maintenance_export'
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/report_magnify.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__MAINTENANCE . 5),
                '',
                '',
                isys_auth_report::instance()->has("views")
            );
        } // if
    } // function

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @global  array $g_dirs
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tpl_www_dir()
    {
        global $g_config;

        return $g_config['www_dir'] . 'src/classes/modules/maintenance/templates/';
    } // function

    /**
     * Start method.
     *
     * @throws  isys_exception_licence
     * @throws  isys_exception_general
     * @return  isys_module_maintenance
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function start()
    {
        // Build the module tree, but only if we are not in the system-module.
        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = isys_module_request::get_instance()->get_menutree();

            $this->build_tree($l_tree, false, -1);
            $this->m_tpl->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));


            switch ($_GET[C__GET__SETTINGS_PAGE])
            {
                default:
                case C__MAINTENANCE__OVERVIEW:
                    $this->process_overview();
                    break;

                case C__MAINTENANCE__PLANNING:
                    $this->process_planning();
                    break;

                case C__MAINTENANCE__PLANNING_ARCHIVE:
                    $this->process_planning(C__MAINTENANCE__PLANNING_ARCHIVE);
                    break;

                case C__MAINTENANCE__MAILTEMPLATE:
                    $this->process_mailtemplate();
                    break;
            } // switch
        } // if

        return $this;
    } // function

    /**
     * Modify row method for the planning list.
     *
     * @param   & $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process_planning__list_modify_row(&$p_row)
    {
        global $g_loc, $g_dirs;

        $p_row['isys_maintenance__date_from'] = $g_loc->fmt_date($p_row['isys_maintenance__date_from']);
        $p_row['isys_maintenance__date_to']   = $g_loc->fmt_date($p_row['isys_maintenance__date_to']);

        $l_object_res = $this->m_dao->get_planning_objects($p_row['isys_maintenance__id']);

        if (count($l_object_res))
        {
            $l_objects   = [];
            $l_obj_count = count($l_object_res);
            $l_cnt       = 1;

            while ($l_row = $l_object_res->get_row())
            {
                if ($l_cnt > $this->m_list_display_clients)
                {
                    $l_objects[] = '... (' . $l_obj_count . ')';
                    break;
                } // if

                $l_objects[] = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_row['isys_obj__id'] . '">' . _L(
                        $l_row['isys_obj_type__title']
                    ) . ' >> ' . $l_row['isys_obj__title'] . '</a>';
                $l_cnt++;
            } // while

            $p_row['clients'] = implode(', ', $l_objects);
        } // if

        if ($p_row['isys_maintenance__finished'] !== null)
        {
            $p_row['isys_maintenance__finished'] = '<img src="' . $g_dirs['images'] . 'icons/silk/tick.png" class="vam mr5" /><span class="green" data-finished="1">' . _L(
                    'LC__UNIVERSAL__YES'
                ) . '</span>';
        }
        else
        {
            $p_row['isys_maintenance__finished'] = '<img src="' . $g_dirs['images'] . 'icons/silk/cross.png" class="vam mr5" /><span class="red" data-finished="0">' . _L(
                    'LC__UNIVERSAL__NO'
                ) . '</span>';
        } // if

        if ($p_row['isys_maintenance__mail_dispatched'] !== null)
        {
            $p_row['isys_maintenance__mail_dispatched'] = '<img src="' . $g_dirs['images'] . 'icons/silk/tick.png" class="vam mr5" /><span class="green" data-mail-dispatched="1">' . _L(
                    'LC__UNIVERSAL__YES'
                ) . '</span>';
        }
        else
        {
            $p_row['isys_maintenance__mail_dispatched'] = '<img src="' . $g_dirs['images'] . 'icons/silk/cross.png" class="vam mr5" /><span class="red" data-mail-dispatched="0">' . _L(
                    'LC__UNIVERSAL__NO'
                ) . '</span>';
        } // if
    } // function

    /**
     * Process method for the "overview" page.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_overview()
    {
        global $index_includes;

        $l_rules = [
            'C__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM' => [
                'p_strPopupType'    => 'calendar',
                'p_strClass'        => 'input-mini',
                'p_bInfoIconSpacer' => 0,
                'p_strValue'        => date('Y-m-d'),
            ],
            'C__MAINTENANCE__OVERVIEW__FILTER__DATE_TO'   => [
                'p_strPopupType'    => 'calendar',
                'p_strClass'        => 'input-mini',
                'p_bInfoIconSpacer' => 0,
            ]
        ];

        $l_day_of_week    = date('N') - 1;
        $l_this_week_time = time() - ($l_day_of_week * isys_convert::DAY);

        $l_this_week = mktime(0, 0, 0, date('m', $l_this_week_time), date('d', $l_this_week_time), date('Y', $l_this_week_time));

        $this->m_tpl->activate_editmode()
            ->assign(
                'ajax_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX      => 1,
                        C__GET__AJAX_CALL => 'maintenance'
                    ]
                )
            )
            ->assign('this_week', $l_this_week)
            ->assign('this_month', mktime(0, 0, 0, date('m'), 1, date('Y')))
            ->assign('next_week', $l_this_week + isys_convert::WEEK)
            ->assign('next_month', mktime(0, 0, 0, (date('m') + 1), 1, date('Y')))
            ->assign('next_next_month', mktime(0, 0, 0, (date('m') + 2), 1, date('Y')))
            ->assign(
                'planning_url',
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__SETTINGS_PAGE => C__MAINTENANCE__PLANNING
                    ]
                )
            )
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'overview.tpl';
    } // function

    /**
     * Process method for the "planning" page.
     *
     * @param   string $p_type
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_planning($p_type = C__MAINTENANCE__PLANNING)
    {
        $l_gets  = $this->m_modreq->get_gets();
        $l_posts = $this->m_modreq->get_posts();

        $l_id      = isys_glob_which_isset($l_gets[C__GET__ID], $l_posts[C__GET__ID]);
        $l_navmode = isys_glob_which_isset($l_gets[C__GET__NAVMODE], $l_posts[C__GET__NAVMODE]);

        // This will happen, if a user uses the checkboxes and the "edit" buttno.
        if (is_array($l_id))
        {
            $l_id = $l_id[0];
        } // if

        if (!$l_navmode && $l_id > 0)
        {
            $l_navmode = C__NAVMODE__EDIT;
        } // if

        switch ($l_navmode)
        {
            default:
                $this->process_planning__list($p_type);
                break;

            case C__NAVMODE__EDIT:
                $this->process_planning__edit($p_type, $l_id);
                break;

            case C__NAVMODE__NEW:
                $this->process_planning__edit($p_type);
                break;

            case C__NAVMODE__SAVE:
                $this->process_planning__edit($p_type, $this->process_planning__save($p_type, $l_posts));
                break;

            case C__NAVMODE__DELETE:
                $this->process_planning__delete($p_type, isys_glob_which_isset($l_gets[C__GET__ID], $l_posts[C__GET__ID]));
                $this->process_planning__list($p_type);
                break;
        } // switch

        $this->m_tpl->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1');
    } // function

    /**
     * Processes the planning list.
     *
     * @param   string $p_type
     *
     * @throws  Exception
     * @throws  isys_exception_general
     */
    protected function process_planning__list($p_type = C__MAINTENANCE__PLANNING)
    {
        global $index_includes;

        $l_gets = $this->m_modreq->get_gets();

        if ($p_type === C__MAINTENANCE__PLANNING)
        {
            $l_rights = [
                isys_auth::EDIT   => $this->get_auth()
                    ->is_allowed_to(isys_auth::EDIT, 'planning'),
                isys_auth::DELETE => $this->get_auth()
                    ->is_allowed_to(isys_auth::DELETE, 'planning')
            ];
        }
        else
        {
            $l_rights = [
                isys_auth::EDIT   => $this->get_auth()
                    ->is_allowed_to(isys_auth::EDIT, 'planning_archive'),
                isys_auth::DELETE => $this->get_auth()
                    ->is_allowed_to(isys_auth::DELETE, 'planning_archive')
            ];
        }

        $l_url_params = [
            C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
            C__GET__TREE_NODE     => $l_gets[C__GET__TREE_NODE],
            C__GET__SETTINGS_PAGE => $p_type,
            C__GET__ID            => '[{isys_maintenance__id}]'
        ];

        $l_list_headers = [
            'isys_maintenance__id'                 => 'ID',
            'isys_maintenance_type__title'         => 'LC__MAINTENANCE__PLANNING__TYPE',
            'clients'                              => 'LC__MAINTENANCE__PLANNING__OBJECT_SELECTION',
            'isys_maintenance__date_from'          => 'LC__MAINTENANCE__PLANNING__DATE_FROM',
            'isys_maintenance__date_to'            => 'LC__MAINTENANCE__PLANNING__DATE_TO',
            'isys_maintenance_mailtemplate__title' => 'LC__MAINTENANCE__PLANNING__MAILTEMPLATE',
            'isys_maintenance__finished'           => 'LC__MAINTENANCE__PLANNING__FINISHED',
            'isys_maintenance__mail_dispatched'    => 'LC__MAINTENANCE__PLANNING__MAIL_DISPATCHED'
        ];

        $l_result       = $this->m_dao->get_data(null, '', ($p_type === C__MAINTENANCE__PLANNING));
        $l_result_count = count($l_result);

        /** @var  isys_component_list $l_list */
        $l_list = new isys_component_list(null, $l_result);

        $l_list->set_row_modifier($this, 'process_planning__list_modify_row')
            ->config($l_list_headers, isys_helper_link::create_url($l_url_params), '[{isys_maintenance__id}]');

        if ($l_list->createTempTable())
        {
            $this->m_tpl->assign('list', $l_list->getTempTableHtml());
        } // if

        isys_component_template_navbar::getInstance()
            ->set_active($l_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_rights[isys_auth::EDIT] && $l_result_count > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_rights[isys_auth::DELETE] && $l_result_count > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        if ($l_rights[isys_auth::EDIT] && $p_type === C__MAINTENANCE__PLANNING)
        {
            isys_component_template_navbar::getInstance()
                ->append_button(
                    'LC__MAINTENANCE__PLANNING__FINISH_MAINTENANCE',
                    'maintenance-finish',
                    [
                        'tooltip'       => _L('LC__MAINTENANCE__POPUP__FINISH_MAINTENANCE'),
                        'icon'          => 'icons/silk/tick.png',
                        'icon_inactive' => 'icons/silk/tick.png',
                        'js_onclick'    => isys_factory::get_instance('isys_popup_maintenance_finish')
                            ->process_overlay('', 700, 318),
                        'accesskey'     => 'f',
                        'navmode'       => 'maintenance_finish'
                    ]
                );
        } // if

        if ($this->get_auth()
            ->is_allowed_to(isys_auth::EXECUTE, 'send_mails')
        )
        {
            isys_component_template_navbar::getInstance()
                ->append_button(
                    'LC__MAINTENANCE__SEND_MAIL',
                    'maintenance-send-mail',
                    [
                        'tooltip'       => _L('LC__MAINTENANCE__SEND_MAIL'),
                        'icon'          => 'icons/silk/email.png',
                        'icon_inactive' => 'icons/silk/email.png',
                        'js_onclick'    => ';',
                        'accesskey'     => 'm',
                        'navmode'       => 'maintenance_send_mail'
                    ]
                );
        } // if

        $this->m_tpl->assign(
            'ajax_url',
            isys_helper_link::create_url(
                [
                    C__GET__AJAX      => 1,
                    C__GET__AJAX_CALL => 'maintenance'
                ]
            )
        )
            ->assign('content_title', _L('LC__MAINTENANCE__PLANNING'));

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'planning_list.tpl';
    } // function

    /**
     * Method for loading the "planning" form.
     *
     * @param   string  $p_type
     * @param   integer $p_id
     *
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_planning__edit($p_type = C__MAINTENANCE__PLANNING, $p_id = null)
    {
        global $index_includes, $g_loc;

        if ($p_type === C__MAINTENANCE__PLANNING)
        {
            $l_is_allowed_to_edit = $this->get_auth()
                ->is_allowed_to(isys_auth::EDIT, 'planning');
        }
        else
        {
            $l_is_allowed_to_edit = $this->get_auth()
                ->is_allowed_to(isys_auth::EDIT, 'planning_archive');
        } // if

        $l_contacts = $l_objects = [];

        isys_component_template_navbar::getInstance()
            ->set_active($l_is_allowed_to_edit, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
            ->set_save_mode('formsubmit');

        if ($p_id !== null)
        {
            $l_planning_data = $this->m_dao->get_data($p_id)
                ->get_row();

            $l_object_res  = $this->m_dao->get_planning_objects($p_id);
            $l_contact_res = $this->m_dao->get_planning_contacts($p_id);

            if (count($l_object_res))
            {
                while ($l_object_row = $l_object_res->get_row())
                {
                    $l_objects[] = (int) $l_object_row['isys_obj__id'];
                } // while
            } // if

            if (count($l_contact_res))
            {
                while ($l_contact_row = $l_contact_res->get_row())
                {
                    $l_contacts[] = (int) $l_contact_row['isys_obj__id'];
                } // while
            } // if
        }
        else
        {
            // Default selection for the maintenance type.
            $l_planning_data = [
                'isys_maintenance__finished'                  => null,
                'isys_maintenance__mail_dispatched'           => null,
                'isys_maintenance__isys_maintenance_type__id' => $this->m_dao->retrieve(
                    'SELECT isys_maintenance_type__id FROM isys_maintenance_type WHERE isys_maintenance_type__const = "C__MAINTENANCE__TYPE__CLIENT_MAINTENANCE";'
                )
                    ->get_row_value('isys_maintenance_type__id')
            ];
        } // if

        $l_mailtemplates    = [];
        $l_mailtemplate_res = $this->m_dao->get_mailtemplates();

        if (count($l_mailtemplate_res))
        {
            while ($l_row = $l_mailtemplate_res->get_row())
            {
                $l_mailtemplates[$l_row['isys_maintenance_mailtemplate__id']] = $l_row['isys_maintenance_mailtemplate__title'];
            } // while
        } // if

        $l_rules = [
            'C__MAINTENANCE__PLANNING__ID'               => [
                'p_strValue'        => (int) $p_id,
                'p_bInvisible'      => true,
                'p_bInfoIconSpacer' => 0
            ],
            'C__MAINTENANCE__PLANNING__OBJECT_SELECTION' => [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                'p_strValue'                                    => isys_format_json::encode($l_objects)
            ],
            'C__MAINTENANCE__PLANNING__TYPE'             => [
                'p_strPopupType'  => 'dialog_plus',
                'p_strTable'      => 'isys_maintenance_type',
                'p_strSelectedID' => $l_planning_data['isys_maintenance__isys_maintenance_type__id'],
                'p_strClass'      => 'input-small'
            ],
            'C__MAINTENANCE__PLANNING__DATE_FROM'        => [
                'p_strPopupType' => 'calendar',
                'p_strClass'     => 'input-mini',
                'p_strValue'     => $l_planning_data['isys_maintenance__date_from']
            ],
            'C__MAINTENANCE__PLANNING__DATE_TO'          => [
                'p_strPopupType'    => 'calendar',
                'p_strClass'        => 'input-mini',
                'p_strValue'        => $l_planning_data['isys_maintenance__date_to'],
                'p_bInfoIconSpacer' => 0
            ],
            'C__MAINTENANCE__PLANNING__COMMENT'          => [
                'p_strValue' => $l_planning_data['isys_maintenance__comment']
            ],
            'C__MAINTENANCE__PLANNING__CONTACT_ROLES'    => [
                'p_strTable'      => 'isys_contact_tag',
                'p_strSelectedID' => $l_planning_data['isys_maintenance__isys_contact_tag__id'],
                'p_strClass'      => 'input-small'
            ],
            'C__MAINTENANCE__PLANNING__CONTACTS'         => [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__PERSON_MASTER;C__CATS__PERSON_GROUP_MASTER;C__CATS__ORGANIZATION_MASTER_DATA',
                'p_strValue'                                    => isys_format_json::encode($l_contacts)
            ],
            'C__MAINTENANCE__PLANNING__MAILTEMPLATE'     => [
                'p_arData'        => $l_mailtemplates,
                'p_strSelectedID' => $l_planning_data['isys_maintenance__isys_maintenance_mailtemplate__id']
            ]
        ];

        if ($l_is_allowed_to_edit)
        {
            $this->m_tpl->activate_editmode();
        } // if

        if ($l_is_allowed_to_edit && $p_type === C__MAINTENANCE__PLANNING)
        {
            isys_component_template_navbar::getInstance()
                ->append_button(
                    'LC__MAINTENANCE__PLANNING__FINISH_MAINTENANCE',
                    'maintenance-finish',
                    [
                        'tooltip'       => _L('LC__MAINTENANCE__POPUP__FINISH_MAINTENANCE'),
                        'icon'          => 'icons/silk/tick.png',
                        'icon_inactive' => 'icons/silk/tick.png',
                        'js_onclick'    => isys_factory::get_instance('isys_popup_maintenance_finish')
                            ->process_overlay('', 700, 318),
                        'accesskey'     => 'f',
                        'navmode'       => 'maintenance_finish',
                        'active'        => ($l_planning_data['isys_maintenance__finished'] === null)
                    ]
                );
        } // if

        if ($this->get_auth()
            ->is_allowed_to(isys_auth::EXECUTE, 'send_mails')
        )
        {
            isys_component_template_navbar::getInstance()
                ->append_button(
                    'LC__MAINTENANCE__SEND_MAIL',
                    'maintenance-send-mail',
                    [
                        'tooltip'       => _L('LC__MAINTENANCE__SEND_MAIL'),
                        'icon'          => 'icons/silk/email.png',
                        'icon_inactive' => 'icons/silk/email.png',
                        'js_onclick'    => ';',
                        'accesskey'     => 'm',
                        'navmode'       => 'maintenance_send_mail',
                        'active'        => ($l_planning_data['isys_maintenance__id'] > 0)
                    ]
                );
        } // if

        $this->m_tpl->assign(
            'ajax_url',
            isys_helper_link::create_url(
                [
                    C__GET__AJAX      => 1,
                    C__GET__AJAX_CALL => 'maintenance'
                ]
            )
        )
            ->assign(
                'mail_dispatched',
                ($l_planning_data['isys_maintenance__mail_dispatched'] !== null ? $g_loc->fmt_datetime($l_planning_data['isys_maintenance__mail_dispatched']) : false)
            )
            ->assign('finished', ($l_planning_data['isys_maintenance__finished'] !== null ? $g_loc->fmt_datetime($l_planning_data['isys_maintenance__finished']) : false))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'planning.tpl';
    } // function

    /**
     * Method for saving the "planning" data.
     *
     * @param   string $p_type
     * @param   array  $p_post
     *
     * @return  integer
     * @throws  isys_exception_database
     */
    protected function process_planning__save($p_type = C__MAINTENANCE__PLANNING, $p_post)
    {
        if ($p_type === C__MAINTENANCE__PLANNING)
        {
            $this->get_auth()
                ->check(isys_auth::EDIT, 'planning');
        }
        else
        {
            $this->get_auth()
                ->check(isys_auth::EDIT, 'planning_archive');
        } // if

        $l_id = ($p_post['C__MAINTENANCE__PLANNING__ID'] > 0) ? $p_post['C__MAINTENANCE__PLANNING__ID'] : null;

        $l_objects  = $p_post['C__MAINTENANCE__PLANNING__OBJECT_SELECTION__HIDDEN'];
        $l_contacts = $p_post['C__MAINTENANCE__PLANNING__CONTACTS__HIDDEN'];

        if (isys_format_json::is_json_array($l_objects))
        {
            $l_objects = isys_format_json::decode($l_objects);
        }
        else
        {
            $l_objects = null;
        } // if

        if (isys_format_json::is_json_array($l_contacts))
        {
            $l_contacts = isys_format_json::decode($l_contacts);
        }
        else
        {
            $l_contacts = null;
        } // if

        // We need to go sure the "date from < date to".
        if (!empty($p_post['C__MAINTENANCE__PLANNING__DATE_FROM__HIDDEN']) && !empty($p_post['C__MAINTENANCE__PLANNING__DATE_TO__HIDDEN']))
        {
            if ($p_post['C__MAINTENANCE__PLANNING__DATE_FROM__HIDDEN'] > $p_post['C__MAINTENANCE__PLANNING__DATE_TO__HIDDEN'])
            {
                $l_date_tmp = $p_post['C__MAINTENANCE__PLANNING__DATE_FROM__HIDDEN'];

                $p_post['C__MAINTENANCE__PLANNING__DATE_FROM__HIDDEN'] = $p_post['C__MAINTENANCE__PLANNING__DATE_TO__HIDDEN'];

                $p_post['C__MAINTENANCE__PLANNING__DATE_TO__HIDDEN'] = $l_date_tmp;
            } // if
        } // if

        try
        {
            $l_id = $this->m_dao->save_planning(
                $l_id,
                [
                    'isys_maintenance__isys_maintenance_type__id'         => $p_post['C__MAINTENANCE__PLANNING__TYPE'],
                    'isys_maintenance__date_from'                         => $p_post['C__MAINTENANCE__PLANNING__DATE_FROM__HIDDEN'],
                    'isys_maintenance__date_to'                           => $p_post['C__MAINTENANCE__PLANNING__DATE_TO__HIDDEN'],
                    'isys_maintenance__comment'                           => $p_post['C__MAINTENANCE__PLANNING__COMMENT'],
                    'isys_maintenance__isys_maintenance_mailtemplate__id' => $p_post['C__MAINTENANCE__PLANNING__MAILTEMPLATE'],
                    'isys_maintenance__isys_contact_tag__id'              => $p_post['C__MAINTENANCE__PLANNING__CONTACT_ROLES'],
                    'isys_maintenance__status'                            => C__RECORD_STATUS__NORMAL
                ],
                $l_objects,
                $l_contacts
            );

            isys_notify::success(_L('LC__MAINTENANCE__NOTIFY__SAVE_SUCCESS'));
        }
        catch (Exception $e)
        {
            isys_notify::error(_L('LC__MAINTENANCE__NOTIFY__SAVE_FAILURE') . $e->getMessage(), ['sticky' => true]);
        } // try

        return $l_id;
    } // function

    /**
     * Method for deleting one or more plannings.
     *
     * @param   string $p_type
     * @param   mixed  $p_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_planning__delete($p_type = C__MAINTENANCE__PLANNING, $p_id)
    {
        if ($p_type === C__MAINTENANCE__PLANNING)
        {
            $this->get_auth()
                ->check(isys_auth::DELETE, 'planning');
        }
        else
        {
            $this->get_auth()
                ->check(isys_auth::DELETE, 'planning_archive');
        } // if

        if (!is_array($p_id))
        {
            $p_id = [$p_id];
        } // if

        $this->m_dao->delete_planning(array_filter($p_id, 'is_numeric'));
    } // function

    /**
     * Process method for the "mailtemplate" page.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_mailtemplate()
    {
        $l_gets  = $this->m_modreq->get_gets();
        $l_posts = $this->m_modreq->get_posts();

        $l_id      = isys_glob_which_isset($l_gets[C__GET__ID], $l_posts[C__GET__ID]);
        $l_navmode = isys_glob_which_isset($l_gets[C__GET__NAVMODE], $l_posts[C__GET__NAVMODE]);

        // This will happen, if a user uses the checkboxes and the "edit" buttno.
        if (is_array($l_id))
        {
            $l_id = $l_id[0];
        } // if

        if (!$l_navmode && $l_id > 0)
        {
            $l_navmode = C__NAVMODE__EDIT;
        } // if

        switch ($l_navmode)
        {
            default:
                $this->process_mailtemplate__list();
                break;

            case C__NAVMODE__EDIT:
                $this->process_mailtemplate__edit($l_id);
                break;

            case C__NAVMODE__NEW:
                $this->process_mailtemplate__edit();
                break;

            case C__NAVMODE__SAVE:
                $this->process_mailtemplate__edit($this->process_mailtemplate__save($l_posts));
                break;

            case C__NAVMODE__DELETE:
                $this->process_mailtemplate__delete(isys_glob_which_isset($l_gets[C__GET__ID], $l_posts[C__GET__ID]));
                $this->process_mailtemplate__list();
                break;
        } // switch

        $this->m_tpl->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1');
    } // function

    /**
     * Method for processing the mailtemplate list.
     *
     * @throws  Exception
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_mailtemplate__list()
    {
        global $index_includes;

        $l_gets = $this->m_modreq->get_gets();

        $l_rights = [
            isys_auth::EDIT   => $this->get_auth()
                ->is_allowed_to(isys_auth::EDIT, 'mailtemplate'),
            isys_auth::DELETE => $this->get_auth()
                ->is_allowed_to(isys_auth::DELETE, 'mailtemplate')
        ];

        $l_url_params = [
            C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
            C__GET__TREE_NODE     => $l_gets[C__GET__TREE_NODE],
            C__GET__SETTINGS_PAGE => C__MAINTENANCE__MAILTEMPLATE,
            C__GET__ID            => '[{isys_maintenance_mailtemplate__id}]'
        ];

        $l_list_headers = [
            'isys_maintenance_mailtemplate__id'    => 'ID',
            'isys_maintenance_mailtemplate__title' => 'LC__MAINTENANCE__MAILTEMPLATE__TITLE',
        ];

        $l_result       = $this->m_dao->get_mailtemplates();
        $l_result_count = count($l_result);

        /** @var  isys_component_list $l_list */
        $l_list = new isys_component_list(null, $l_result);

        $l_list->config($l_list_headers, isys_helper_link::create_url($l_url_params), '[{isys_maintenance_mailtemplate__id}]');

        if ($l_list->createTempTable())
        {
            $this->m_tpl->assign('list', $l_list->getTempTableHtml());
        } // if

        isys_component_template_navbar::getInstance()
            ->set_active($l_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_rights[isys_auth::EDIT] && $l_result_count > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_rights[isys_auth::DELETE] && $l_result_count > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        $this->m_tpl->assign('content_title', _L('LC__MAINTENANCE__MAILTEMPLATE'));

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'mailtemplate_list.tpl';
    } // function

    /**
     * Method for loading the "planning" form.
     *
     * @param   integer $p_id
     *
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_mailtemplate__edit($p_id = null)
    {
        global $index_includes;

        $l_is_allowed_to_edit = $this->get_auth()
            ->is_allowed_to(isys_auth::EDIT, 'mailtemplate');
        $l_empty_value        = isys_tenantsettings::get('gui.empty_value', '-');

        isys_component_template_navbar::getInstance()
            ->set_active($l_is_allowed_to_edit, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
            ->set_save_mode('formsubmit');

        $l_mailtemplate_data = [];

        if ($p_id !== null)
        {
            $l_mailtemplate_data = $this->m_dao->get_mailtemplates($p_id)
                ->get_row();
        } // if

        $l_rules = [
            'C__MAINTENANCE__MAILTEMPLATE__ID'    => [
                'p_strValue'        => (int) $p_id,
                'p_bInvisible'      => true,
                'p_bInfoIconSpacer' => 0
            ],
            'C__MAINTENANCE__MAILTEMPLATE__TITLE' => [
                'p_strValue' => $l_mailtemplate_data['isys_maintenance_mailtemplate__title']
            ],
            'C__MAINTENANCE__MAILTEMPLATE__TEXT'  => [
                'p_strValue' => $l_mailtemplate_data['isys_maintenance_mailtemplate__text']
            ]
        ];

        // SQL Query for selecting the first found client, server or switch.
        $l_sql = 'SELECT isys_obj__id FROM isys_obj ' . 'WHERE isys_obj__isys_obj_type__id IN (' . implode(
                ',',
                [
                    C__OBJTYPE__CLIENT,
                    C__OBJTYPE__SERVER,
                    C__OBJTYPE__SWITCH
                ]
            ) . ') ' . 'AND isys_obj__status = ' . $this->m_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ' . 'LIMIT 1;';

        $l_variables = isys_helper_link::get_url_variables(
            $this->m_dao->retrieve($l_sql)
                ->get_row_value('isys_obj__id')
        );

        $l_variables['%recipients%']       = _L('LC__UNIVERSAL__MISSES') . ' Maria Mustermann, John Doe';
        $l_variables['%maintenance_from%'] = date('d.m.Y');
        $l_variables['%maintenance_to%']   = date('d.m.Y', (time() + isys_convert::WEEK));

        foreach ($l_variables as $l_key => &$l_value)
        {
            $l_value = '<code>' . $l_key . '</code> = ' . (empty($l_value) ? $l_empty_value : $l_value);
        } // foreach

        if ($l_is_allowed_to_edit)
        {
            $this->m_tpl->activate_editmode();
        } // if

        $this->m_tpl->assign('variables', implode('<br />', $l_variables))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'mailtemplate.tpl';
    } // function

    /**
     * Method for saving the "planning" data.
     *
     * @param   array $p_post
     *
     * @return  integer
     * @throws  isys_exception_database
     */
    protected function process_mailtemplate__save($p_post)
    {
        $l_id = ($p_post['C__MAINTENANCE__MAILTEMPLATE__ID'] > 0) ? $p_post['C__MAINTENANCE__MAILTEMPLATE__ID'] : null;

        try
        {
            $l_id = $this->m_dao->save_mailtemplate(
                $l_id,
                [
                    'isys_maintenance_mailtemplate__title'  => $p_post['C__MAINTENANCE__MAILTEMPLATE__TITLE'],
                    'isys_maintenance_mailtemplate__text'   => $p_post['C__MAINTENANCE__MAILTEMPLATE__TEXT'],
                    'isys_maintenance_mailtemplate__status' => C__RECORD_STATUS__NORMAL
                ]
            );

            isys_notify::success(_L('LC__MAINTENANCE__NOTIFY__SAVE_SUCCESS'));
        }
        catch (Exception $e)
        {
            isys_notify::error(_L('LC__MAINTENANCE__NOTIFY__SAVE_FAILURE') . $e->getMessage(), ['sticky' => true]);
        } // try

        return $l_id;
    } // function

    /**
     * Method for deleting one or more mail templates.
     *
     * @param   mixed $p_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_mailtemplate__delete($p_id)
    {
        if (!is_array($p_id))
        {
            $p_id = [$p_id];
        } // if

        $this->m_dao->delete_mailtemplate(array_filter($p_id, 'is_numeric'));
    } // function
} // class