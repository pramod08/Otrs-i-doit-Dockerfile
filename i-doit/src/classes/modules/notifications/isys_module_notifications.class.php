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
 * Notification module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_module_notifications extends isys_module implements isys_module_interface, isys_module_authable
{

    const DISPLAY_IN_MAIN_MENU = true;

    // Defines whether this module will be displayed in the named menus:
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * Root node
     */
    const C__ROOT = 0;
    /**
     * Node for managing notifications
     */
    const C__MANAGE_NOTIFICATIONS = 1;
    /**
     * Node for managing templates
     */
    const C__MANAGE_TEMPLATES = 2;
    /**
     * Parameter name to handle entities
     */
    const C__ENTITY = 'entity';
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * Current action (based on navigation mode)
     *
     * @var int
     */
    protected $m_action;
    /**
     * Module DAO instance
     *
     * @var isys_notifications_dao
     */
    protected $m_dao;
    /**
     * Current entity
     *
     * @var int
     */
    protected $m_entity;
    /**
     * Logger instance
     *
     * @var isys_log
     */
    protected $m_log;
    /**
     * Module identifier
     *
     * @var int
     */
    protected $m_module_id;

    /**
     * Current node
     *
     * @var int
     */
    protected $m_node;
    /**
     * Nodes
     *
     * @var array
     */
    protected $m_nodes;
    /**
     * Current type
     *
     * @var int
     */
    protected $m_type;
    /**
     * User request
     *
     * @var isys_module_request
     */
    protected $m_userrequest;
    /**
     * @var
     */
    private $m_notification_type_const;

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_notifications::instance();
    } //function

    /**
     * Enhances the breadcrumb navigation.
     *
     * @return array
     */
    public function breadcrumb_get(&$p_gets)
    {

        $l_result = [];

        $l_result[] = [
            $this->m_nodes[self::C__ROOT]['nodes'][$this->m_node]['title'] => [
                C__GET__MODULE_ID => $this->m_module_id,
                C__GET__TREE_NODE => $this->m_node
            ]
        ];

        $l_types = $this->m_dao->get_type();

        $l_result[] = [
            _L($l_types[$this->m_type]['title']) => [
                C__GET__MODULE_ID     => $this->m_module_id,
                C__GET__TREE_NODE     => $this->m_node,
                C__GET__SETTINGS_PAGE => $this->m_type
            ]
        ];

        if (isset($this->m_entity))
        {
            $l_entity = null;

            switch ($this->m_node)
            {
                case self::C__MANAGE_NOTIFICATIONS:
                    $l_entity = $this->m_dao->get_notification($this->m_entity);
                    break;
                case self::C__MANAGE_TEMPLATES:
                    $l_entity = $this->m_dao->get_template($this->m_entity);
                    break;
            } //switch

            $l_title = null;
            if (count($l_entity) > 0)
            {
                $l_title = $l_entity['title'];
            }
            else
            {
                $l_title = _L('LC__NAVIGATION__NAVBAR__NEW');
            }

            $l_result[] = [
                $l_title => [
                    C__GET__MODULE_ID     => $this->m_module_id,
                    C__GET__TREE_NODE     => $this->m_node,
                    C__GET__SETTINGS_PAGE => $this->m_type,
                    self::C__ENTITY       => $this->m_entity
                ]
            ];
        }

        return $l_result;
    } //function

    /**
     * Builds menu tree.
     *
     * @param isys_component_tree $p_tree          Tree component
     * @param bool                $p_system_module (optional) Is it a system module? Defaults
     *                                             to true.
     * @param int                 $p_parent        (optional) Parent identifier. Defaults to null.
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        assert('$p_tree instanceof isys_component_tree');
        assert('is_bool($p_system_module)');

        $l_parent = -1;

        $l_nodes = $this->get_nodes();

        $l_id = $this->m_module_id;

        $l_root = null;
        if (isset($p_parent))
        {
            assert('is_int($p_parent)');
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(
                $l_id,
                $l_parent,
                $l_nodes[self::C__ROOT]['title']
            );
        } //if

        // Iterate through each node (except root node):
        foreach ($l_nodes[self::C__ROOT]['nodes'] as $l_key => $l_value)
        {
            $l_node_id = intval($l_id . $l_key);

            $p_tree->add_node(
                $l_node_id,
                $l_root,
                $l_value['title'],
                '?' . C__GET__MODULE_ID . '=' . $this->m_module_id . '&' . C__GET__TREE_NODE . '=' . $l_key,
                '',
                '',
                intval($this->m_node === $l_key),
                '',
                '',
                isys_auth_notifications::instance()
                    ->is_allowed_to(isys_auth::VIEW, "NOTIFICATIONS/" . $l_value['right_const'])
            );

            // Iterate through sub nodes:
            if (isset($l_value['nodes']))
            {
                foreach ($l_value['nodes'] as $l_sub_key => $l_sub_value)
                {
                    $l_sub_node_id = intval($l_id . $l_key . $l_sub_key);

                    $p_tree->add_node(
                        $l_sub_node_id,
                        $l_node_id,
                        $l_sub_value['title'],
                        '?' . C__GET__MODULE_ID . '=' . $this->m_module_id . '&' . C__GET__TREE_NODE . '=' . $l_key . '&' . C__GET__SETTINGS_PAGE . '=' . $l_sub_key,
                        '',
                        '',
                        intval($this->m_type === $l_sub_key),
                        '',
                        '',
                        isys_auth_notifications::instance()
                            ->is_allowed_to(isys_auth::VIEW, "NOTIFICATIONS/" . $l_value['right_const'])
                    );
                } //foreach
            } //if
        } //foreach
    }

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @return  bool    true
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_link_options = [
            C__GET__MODULE_ID     => $this->m_module_id,
            C__GET__TREE_NODE     => $this->m_node,
            C__GET__SETTINGS_PAGE => $this->m_type
        ];

        if ($this->m_action !== 0)
        {
            $l_link_options[self::C__ENTITY] = $this->m_entity['id'];
            $p_text[]                        = $this->m_entity['title'];
        }
        else
        {
            $p_text[] = $this->m_nodes[self::C__ROOT]['nodes'][$this->m_node]['title'];
        } //if

        $p_link = isys_glob_http_build_query($l_link_options);

        return true;
    } //function

    /**
     * Starts module.
     */
    public function start()
    {
        global $index_includes;

        $l_gets   = $this->m_userrequest->get_gets();
        $l_posts  = $this->m_userrequest->get_posts();
        $l_navbar = $this->m_userrequest->get_navbar();
        $l_nodes  = $this->get_nodes();

        // Set node:

        if (array_key_exists(C__GET__TREE_NODE, $l_gets))
        {
            $this->m_node = intval($l_gets[C__GET__TREE_NODE]);
        }
        else
        {
            $this->m_node = self::C__MANAGE_NOTIFICATIONS;
        } //if

        // Set type:

        if (array_key_exists(C__GET__SETTINGS_PAGE, $l_gets))
        {
            $l_candidate = intval($l_gets[C__GET__SETTINGS_PAGE]);
            if (is_array($l_nodes[self::C__ROOT]['nodes'][$this->m_node]['nodes']))
            {
                if (array_key_exists($l_candidate, $l_nodes[self::C__ROOT]['nodes'][$this->m_node]['nodes']))
                {
                    $this->m_type = $l_candidate;
                } //if
            }
        } //if

        if (!isset($this->m_type))
        {
            // Fetch first type (in alphabetical order):
            $l_candidate = null;

            if (is_array($l_nodes[self::C__ROOT]['nodes'][$this->m_node]['nodes']))
            {
                foreach ($l_nodes[self::C__ROOT]['nodes'][$this->m_node]['nodes'] as $l_key => $l_value)
                {
                    if (!isset($l_candidate) || strcmp($l_candidate, $l_value['title']) > 0)
                    {
                        $l_candidate  = $l_value['title'];
                        $this->m_type = $l_key;
                    } //if
                } //foreach
            }
        } //if

        // Set action:

        // Default is to show list:
        if (array_key_exists(C__GET__NAVMODE, $l_posts))
        {
            $this->m_action = intval($l_posts[C__GET__NAVMODE]);
        }
        else
        {
            $this->m_action = 0;
        } //if

        // It's a click on a list to edit an entity:
        if ($this->m_action === 0 && isset($l_gets[self::C__ENTITY]))
        {
            $this->m_action = C__NAVMODE__EDIT;
        } //if

        // Set entity:

        if (isset($l_gets[self::C__ENTITY]))
        {
            $this->m_entity = intval($l_gets[self::C__ENTITY]);
        }
        else if (!empty($l_posts['id']))
        {
            if (is_numeric($l_posts['id']))
            {
                $this->m_entity = intval($l_posts['id']);
            }
            else if (is_array($l_posts['id']))
            {
                $this->m_entity = intval($l_posts['id'][0]);
            } //if
        }
        else
        {
            // Last chance to set entity:
            if ($this->m_node === self::C__MANAGE_NOTIFICATIONS && isset($l_posts['SM2__C__NOTIFICATIONS__NOTIFICATION_ID']['p_strValue']))
            {
                $this->m_entity = $l_posts['SM2__C__NOTIFICATIONS__NOTIFICATION_ID']['p_strValue'];
            }
            else if ($this->m_node === self::C__MANAGE_TEMPLATES && isset($l_posts['SM2__C__NOTIFICATIONS__TEMPLATE_ID']['p_strValue']))
            {
                $this->m_entity = $l_posts['SM2__C__NOTIFICATIONS__TEMPLATE_ID']['p_strValue'];
            } //if
        } //if

        $l_template = $this->m_userrequest->get_template();

        $l_tree = $this->m_userrequest->get_menutree();
        $this->build_tree($l_tree, false, null);

        $l_select_node = $this->m_module_id . $this->m_node . $this->m_type;
        $l_tree->select_node_by_id($l_select_node);
        $l_processed = $l_tree->process($l_select_node);

        $l_template->assign('menu_tree', $l_processed);
        try
        {
            switch ($this->m_node)
            {
                case self::C__MANAGE_TEMPLATES:
                    $index_includes['contentbottomcontent'] = 'modules/notifications/templates.tpl';
                    $this->m_notification_type_const        = 'MANAGE_TEMPLATES';
                    switch ($this->m_action)
                    {
                        case C__NAVMODE__NEW:
                            $this->show_template($this->m_action);
                            break;
                        case C__NAVMODE__EDIT:
                            if ($this->m_entity > 0)
                            {
                                $l_data = $this->load_template($this->m_entity);
                                $this->show_template($this->m_action, $l_data);
                            }
                            else
                            {
                                $this->show_templates();
                                throw new isys_exception_general(
                                    _L('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST')
                                );
                            } // if
                            break;
                        case C__NAVMODE__SAVE:
                            $l_template_id = null;
                            if (isset($l_posts['C__NOTIFICATIONS__TEMPLATE_ID']) && !empty($l_posts['C__NOTIFICATIONS__TEMPLATE_ID']))
                            {
                                $l_template_id = intval($l_posts['C__NOTIFICATIONS__TEMPLATE_ID']);
                            } //if
                            $this->save_template($l_template_id);
                            break;
                        case C__NAVMODE__CANCEL:
                            if (is_numeric($l_posts['C__NOTIFICATIONS__TEMPLATE_ID']))
                            {
                                $l_data = $this->load_template(intval($l_posts['C__NOTIFICATIONS__TEMPLATE_ID']));
                                $this->show_template(C__NAVMODE__SAVE, $l_data);
                            }
                            else
                            {
                                $this->show_templates();
                            } //if
                            break;
                        case C__NAVMODE__PURGE:
                            if (is_array($l_posts['id']))
                            {
                                // User marked one or more templates in list
                                // mode:
                                foreach ($l_posts['id'] as $l_id)
                                {
                                    $this->delete_template(intval($l_id));
                                } //foreach
                            }
                            else if (isset($this->m_entity))
                            {
                                // User purged template within its view mode:
                                $this->delete_template($this->m_entity);
                            }
                            else
                            {
                                $this->show_templates();
                                throw new isys_exception_general(
                                    _L('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST')
                                );
                            } //if

                            $this->show_templates();
                            break;
                        // View:
                        default:
                        case 0:
                            $this->show_templates();
                            break;
                    } //switch
                    break;
                case self::C__MANAGE_NOTIFICATIONS:
                default:
                    $index_includes['contentbottomcontent'] = 'modules/notifications/notifications.tpl';
                    $this->m_notification_type_const        = 'MANAGE_NOTIFICATIONS';
                    switch ($this->m_action)
                    {
                        case C__NAVMODE__NEW:
                            $this->show_notification($this->m_action);
                            break;
                        case C__NAVMODE__EDIT:
                            if($this->m_entity > 0)
                            {
                                $l_data = $this->load_notification($this->m_entity);
                                $this->show_notification($this->m_action, $l_data);
                            }
                            else
                            {
                                $this->show_notifications();
                                throw new isys_exception_general(
                                    _L('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST')
                                );

                            } // if
                            break;
                        case C__NAVMODE__SAVE:
                            $l_notification_id = null;
                            if (isset($l_posts['C__NOTIFICATIONS__NOTIFICATION_ID']) && !empty($l_posts['C__NOTIFICATIONS__NOTIFICATION_ID']))
                            {
                                $l_notification_id = intval($l_posts['C__NOTIFICATIONS__NOTIFICATION_ID']);
                            } //if
                            $this->save_notification($l_notification_id);
                            break;
                        case C__NAVMODE__CANCEL:
                            if (is_numeric($l_posts['C__NOTIFICATIONS__NOTIFICATION_ID']))
                            {
                                $l_data = $this->load_notification(intval($l_posts['C__NOTIFICATIONS__NOTIFICATION_ID']));
                                $this->show_notification(C__NAVMODE__SAVE, $l_data);
                            }
                            else
                            {
                                $this->show_notifications();
                            } //if
                            break;
                        case C__NAVMODE__PURGE:
                            if (is_array($l_posts['id']))
                            {
                                // User marked one or more notifications in list
                                // mode:
                                foreach ($l_posts['id'] as $l_id)
                                {
                                    $this->delete_notification(intval($l_id));
                                } //foreach
                            }
                            else if (isset($this->m_entity))
                            {
                                // User purged notification within its view mode:
                                $this->delete_notification($this->m_entity);
                            }
                            else
                            {
                                $this->show_notifications();
                                throw new isys_exception_general(
                                    _L('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST')
                                );

                            } //if

                            $this->show_notifications();
                            break;
                        // View:
                        default:
                        case 0:
                            $this->show_notifications();
                            break;
                    } //switch
                    break;
            } //switch
        }
        catch(isys_exception_general $e)
        {
            isys_notify::error($e->getMessage());
        }

        $this->m_dao->apply_update();
    } //function

    /**
     * Initiates module.
     *
     * @param isys_module_request $p_req
     */
    public function init(isys_module_request $p_req)
    {
        // Set request information:

        assert('$p_req instanceof isys_module_request');
        $this->m_userrequest = $p_req;
    } // function

    /**
     * Modifies row when showing notifications. This is a callback method for
     * isys_component_list::set_row_modifier()
     *
     * @param array $p_ar_data
     */
    public function modify_notification_rows(&$p_ar_data)
    {
        if (isset($p_ar_data['isys_notification__status']))
        {
            $l_status                               = $this->m_dao->get_status();
            $p_ar_data['isys_notification__status'] = $l_status[$p_ar_data['isys_notification__status']];
        } //if
    } //function

    /**
     * Modifies row when showing templates. This is a callback method for
     * isys_component_list::set_row_modifier()
     *
     * @param array $p_ar_data
     */
    public function modify_template_rows(&$p_ar_data)
    {
        if (isset($p_ar_data['isys_notification_template__text']))
        {
            $p_ar_data['isys_notification_template__text'] = nl2br($p_ar_data['isys_notification_template__text']);
        } //if

        if (isset($p_ar_data['isys_notification_template__locale']))
        {
            $l_languages = $this->m_dao->get_locales();

            if (array_key_exists($p_ar_data['isys_notification_template__locale'], $l_languages))
            {
                $p_ar_data['isys_notification_template__locale'] = $l_languages[$p_ar_data['isys_notification_template__locale']];
            } //if
        } //if
    } //function

    /**
     * Gets module nodes.
     *
     * @return array Multi-dimensional indexed array with translated titles.
     */
    protected function get_nodes()
    {
        if (isset($this->m_nodes))
        {
            return $this->m_nodes;
        } //if

        // Root node:
        $this->m_nodes = [
            self::C__ROOT => [
                'title' => _L('LC__MODULE__NOTIFICATIONS'),
                'nodes' => [
                    self::C__MANAGE_NOTIFICATIONS => [
                        'title'       => _L('LC__NOTIFICATIONS__MANAGE_NOTIFICATIONS'),
                        'right_const' => 'MANAGE_NOTIFICATIONS'
                    ],
                    self::C__MANAGE_TEMPLATES     => [
                        'title'       => _L('LC__NOTIFICATIONS__MANAGE_TEMPLATES'),
                        'right_const' => 'MANAGE_TEMPLATES'
                    ]
                ]
            ]
        ];

        $l_types = $this->m_dao->get_type();

        // Assign notification types to all sub nodes:
        foreach ($l_types as $l_type)
        {
            $l_title = _L($l_type['title']);

            $this->m_nodes[self::C__ROOT]['nodes'][self::C__MANAGE_NOTIFICATIONS]['nodes'][$l_type['id']] = ['title' => $l_title];
            $this->m_nodes[self::C__ROOT]['nodes'][self::C__MANAGE_TEMPLATES]['nodes'][$l_type['id']]     = ['title' => $l_title];
        } //foreach

        return $this->m_nodes;
    } //function

    /**
     * Shows list of notifications.
     */
    protected function show_notifications()
    {
        global $index_includes;
        $l_template = $this->m_userrequest->get_template();

        try
        {
            isys_auth_notifications::instance()
                ->check(isys_auth::VIEW, "NOTIFICATIONS/MANAGE_NOTIFICATIONS");

            $l_result_set = $this->m_dao->get_notifications(null, ['type' => $this->m_type], true, true);
            $l_properties = $this->m_dao->get_properties('notifications');

            $l_columns = [
                'id',
                'title',
                'status',
                'count',
                'last_run'
            ];

            $l_entity_id_field = $l_properties['id'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

            $l_header = [];
            foreach ($l_columns as $l_column)
            {
                $l_header[$l_properties[$l_column][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $l_properties[$l_column][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
            } //foreach

            $l_list = $this->create_list($l_result_set, $l_entity_id_field, $l_header, 'modify_notification_rows');

            $l_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");
            $l_template->assign('g_list', $l_list);
        }
        catch (isys_exception_auth $e)
        {
            $l_template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
        catch (Exception $e)
        {
            $l_template->assign("exception", $e);
            $index_includes['contentbottomcontent'] = "exception.tpl";
        }
    } //function

    /**
     * Loads notification from database.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array Associative array
     */
    protected function load_notification($p_notification_id)
    {
        $l_data = [];

        // Notification roles:

        $l_roles_data = $this->m_dao->get_roles($p_notification_id);
        $l_roles      = [];

        foreach ($l_roles_data as $l_role)
        {
            $l_roles[] = intval($l_role['isys_contact_tag__id']);
        } //foreach
        $l_data['notification_roles']['roles'] = array_unique($l_roles);

        // Notification domains:

        $l_domain_data                  = $this->m_dao->get_domains($p_notification_id);
        $l_data['notification_domains'] = $l_domain_data;

        // Notifications:

        $l_data['notifications'] = $this->m_dao->get_notification($p_notification_id);

        // Contacts:

        $l_contacts = [];

        if (isset($l_data['notifications']['contacts']))
        {
            $l_contact_data = $this->m_dao->get_contacts(
                $l_data['notifications']['contacts']
            );

            foreach ($l_contact_data as $l_contact)
            {
                $l_contacts[] = $l_contact['isys_contact_2_isys_obj__isys_obj__id'];
            } //foreach
        } //if

        $l_data['notifications']['contacts'] = $l_contacts;

        return $l_data;
    } //function

    /**
     * Show notification in view or edit mode.
     *
     * @param int    $p_mode   Show or edit/new mode?
     * @param array  $p_data   (optional) Data for all property types. Defaults to
     *                         null.
     * @param array  $p_result (optional) Validation results for all property
     *                         types. Defaults to null.
     *
     * @global array $g_config Global configuration
     */
    protected function show_notification($p_mode, $p_data = null, $p_result = null)
    {
        global $g_config;
        isys_auth_notifications::instance()
            ->check(isys_auth::VIEW, "NOTIFICATIONS/MANAGE_NOTIFICATIONS");
        $l_template = $this->m_userrequest->get_template();
        $l_navbar   = isys_component_template_navbar::getInstance();

        // Rights
        $l_auth_edit   = isys_auth_notifications::instance()
            ->is_allowed_to(isys_auth::EDIT, "NOTIFICATIONS/MANAGE_NOTIFICATIONS");
        $l_auth_delete = isys_auth_notifications::instance()
            ->is_allowed_to(isys_auth::DELETE, "NOTIFICATIONS/MANAGE_NOTIFICATIONS");

        // Mode:

        if ($p_mode == C__NAVMODE__NEW || $p_mode == C__NAVMODE__EDIT)
        {

            if ($l_auth_edit)
            {
                $l_template->activate_editmode();
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            }

            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW)
                ->set_active(false, C__NAVBAR_BUTTON__PURGE);
        }
        else
        {
            $l_navbar->set_active($l_auth_edit, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_auth_edit, C__NAVBAR_BUTTON__NEW)
                ->set_active($l_auth_delete, C__NAVBAR_BUTTON__PURGE)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                ->set_visible(true, C__NAVBAR_BUTTON__PURGE);
        } //if

        // Assign notification identifier:
        $l_template->assign(
            'id',
            $p_data['notifications']['id']
        );

        // Current notification status:

        if ($p_mode === C__NAVMODE__EDIT)
        {
            $l_status = $this->m_dao->get_status();

            $l_current_notification_status = sprintf(
                _L('LC__NOTIFICATIONS__CURRENT_NOTIFICATION_STATUS'),
                $l_status[$p_data['notifications']['status']]
            );

            $l_template->assign(
                'current_notification_status',
                $l_current_notification_status
            );
        } //if

        // Type:

        $l_type = $this->m_dao->get_type($this->m_type);

        $l_template->assign('is_report_based', ($l_type['callback'] == 'isys_notification_generic_report'));
        $l_template->assign('type_title', _L($l_type['title']));
        $l_template->assign('type_description', nl2br(_L($l_type['description'])));
        $l_template->assign(
            'type_templates',
            $g_config['www_dir'] . '?' . C__GET__MODULE_ID . '=' . $this->m_module_id . '&' . C__GET__TREE_NODE . '=' . self::C__MANAGE_TEMPLATES . '&' . C__GET__SETTINGS_PAGE . '=' . $this->m_type
        );

        // Domains:

        $l_domains = intval($l_type['domains']);

        if (isys_notifications_dao::C__DOMAIN__NONE != $l_domains)
        {
            $l_template->assign('domain', true);
        } //if

        if (isys_notifications_dao::C__DOMAIN__OBJECTS & $l_domains)
        {
            $l_template->assign('objects_domain', true);
        } //if

        if (isys_notifications_dao::C__DOMAIN__OBJECT_TYPES & $l_domains)
        {
            $l_template->assign('object_types_domain', true);
        } //if

        if (isys_notifications_dao::C__DOMAIN__REPORTS & $l_domains)
        {
            $l_template->assign('reports_domain', true);
        } //if

        $l_properties = $this->m_dao->get_properties();

        // Notification type:

        if ($p_mode == C__NAVMODE__NEW || $p_mode == C__NAVMODE__EDIT)
        {
            $l_properties['notifications']['type'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'] = $l_type['id'];
        } // if

        // Threshold unit:

        $l_show_unit = true;

        if ($l_type['unit'] === null)
        {
            $l_show_unit = false;
        }
        else
        {
            $l_unit = $this->m_dao->get_unit($l_type['unit']);

            $l_properties['notifications']['threshold_unit'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable'] = $l_unit['table'];
            $l_properties['notifications']['threshold_unit'][C__PROPERTY__DATA]['default']                           = $l_type['default_unit'];
        } //if

        $l_template->assign('show_unit', $l_show_unit);

        // Domain object types:

        $l_object_types = [];
        $l_cmdb_dao     = new isys_cmdb_dao($this->m_db);
        $l_result_set   = $l_cmdb_dao->get_object_types();

        while ($l_row = $l_result_set->get_row())
        {
            $l_is_selected = false;
            if (is_array($p_data['notification_domains']['object_types']) && in_array(intval($l_row['isys_obj_type__id']), $p_data['notification_domains']['object_types']))
            {
                $l_is_selected = true;
            } //if

            $l_object_types[] = [
                'id'  => $l_row['isys_obj_type__id'],
                'val' => _L($l_row['isys_obj_type__title']),
                'sel' => $l_is_selected,
                'url' => ''
            ];
        } //while
        unset($l_cmdb_dao, $l_result_set);
        usort(
            $l_object_types,
            [
                $this,
                'sort_dialog_list'
            ]
        );

        $l_properties['notification_domains']['object_types'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] = serialize($l_object_types);

        // Domain reports:

        $l_reports     = [];
        $l_all_reports = $this->m_dao->get_all_reports();

        foreach ($l_all_reports as $l_report)
        {
            $l_is_selected = false;
            if (is_array($p_data['notification_domains']['reports']) && in_array(intval($l_report['isys_report__id']), $p_data['notification_domains']['reports']))
            {
                $l_is_selected = true;
            } //if

            $l_reports[] = [
                'id'  => $l_report['isys_report__id'],
                'val' => $l_report['isys_report__title'],
                'sel' => $l_is_selected,
                'url' => ''
            ];
        } //foreach
        usort(
            $l_reports,
            [
                $this,
                'sort_dialog_list'
            ]
        );

        $l_properties['notification_domains']['reports'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] = serialize($l_reports);

        // All available roles:
        $l_roles     = [];
        $l_all_roles = $this->m_dao->get_all_roles();

        foreach ($l_all_roles as $l_role)
        {
            $l_is_selected = false;
            if (is_array($p_data['notification_roles']['roles']) && in_array(intval($l_role['isys_contact_tag__id']), $p_data['notification_roles']['roles']))
            {
                $l_is_selected = true;
            } //if

            $l_roles[] = [
                'id'  => $l_role['isys_contact_tag__id'],
                'val' => _L($l_role['isys_contact_tag__title']),
                'sel' => $l_is_selected,
                'url' => ''
            ];
        } //foreach
        usort(
            $l_roles,
            [
                $this,
                'sort_dialog_list'
            ]
        );

        $l_properties['notification_roles']['roles'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] = serialize($l_roles);

        // Assign rules and optionally data and validation results for all used
        // property types:

        $l_property_types = [
            'notifications',
            'notification_domains',
            'notification_roles'
        ];

        foreach ($l_property_types as $l_property_type)
        {
            $l_data = null;
            if (is_array($p_data) && isset($p_data[$l_property_type]))
            {
                $l_data = $p_data[$l_property_type];
            } //if

            $l_result = null;
            if (is_array($p_result) && isset($p_result[$l_property_type]))
            {
                $l_result = $p_result[$l_property_type];
            } //if

            // Assign rules:
            $l_template->smarty_tom_add_rules(
                'tom.content.bottom.content',
                $this->prepare_user_data_assignment(
                    $l_properties[$l_property_type],
                    $l_data,
                    $l_result
                )
            );
        } //foreach
    } //function

    /**
     * Saves a notification.
     *
     * @param int $p_notification_id (optional) Notification identifier. If set,
     *                               an existing notification will be updated. Otherwise a new one will be
     *                               created. Defaults to null.
     */
    protected function save_notification($p_notification_id = null)
    {
        global $g_config;

        $l_type = $this->m_dao->get_type($this->m_type);

        $l_data              = [];
        $l_result            = [];
        $l_validation_failed = false;
        $l_notification_id   = null;

        // Notification:

        $l_property_type            = 'notifications';
        $l_properties               = $this->m_dao->get_properties($l_property_type);
        $l_data[$l_property_type]   = $this->parse_user_data($l_properties);
        $l_result[$l_property_type] = $this->validate_property_data($l_properties, $l_data[$l_property_type]);

        $l_save_data = [];

        foreach ($l_properties as $l_property_id => $l_property_info)
        {
            // If identifier is not valid, just ignore it. A new entity will be
            // created.
            if ($l_property_id == 'id' && ((isset($l_data[$l_property_type]['id']) && $l_data[$l_property_type]['id'] < 1) || !isset($l_data[$l_property_id]['id'])))
            {
                $l_result[$l_property_type]['id'] = isys_notifications_dao::C__VALIDATION_RESULT__IGNORED;
                continue;
            } //if

            // Ignore referenced contacts. They will be treated later.
            if ($l_property_id == 'contacts' && isset($l_data['contacts']))
            {
                continue;
            } //if

            if (array_key_exists($l_property_id, $l_data[$l_property_type]) && array_key_exists(
                    $l_property_id,
                    $l_result[$l_property_type]
                ) && $l_result[$l_property_type][$l_property_id] > isys_notifications_dao::C__VALIDATION_RESULT__IGNORED
            )
            {
                $l_validation_failed = true;
                break;
            } // if

            // Save property only if create and save are provided:
            if (array_key_exists(C__PROPERTY__PROVIDES, $l_property_info))
            {
                if ((isys_notifications_dao::C__PROPERTY__PROVIDES__CREATE & $l_property_info[C__PROPERTY__PROVIDES]) || (isys_notifications_dao::C__PROPERTY__PROVIDES__SAVE & $l_property_info[C__PROPERTY__PROVIDES]))
                {
                    if ($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == 'varchar')
                    {
                        $l_data[$l_property_type][$l_property_id] = $l_data[$l_property_type][$l_property_id];
                    }
                    $l_save_data[$l_property_id] = $l_data[$l_property_type][$l_property_id];
                } //if
            } //if
        } //foreach

        if (isset($p_notification_id))
        {
            // Update notification identifier:
            $l_save_data['id'] = $p_notification_id;
        } //if

        if ($l_validation_failed === false)
        {
            $l_notification_id = $this->m_dao->save('notifications', $l_save_data);

            // Update notification identifier:
            $l_data[$l_property_type]['id']   = $l_notification_id;
            $l_result[$l_property_type]['id'] = isys_notifications_dao::C__VALIDATION_RESULT__NOTHING;
        } //if

        // Notification domains:

        $l_property_type            = 'notification_domains';
        $l_properties               = $this->m_dao->get_properties($l_property_type);
        $l_data[$l_property_type]   = $this->parse_user_data($l_properties);
        $l_result[$l_property_type] = $this->validate_property_data($l_properties, $l_data[$l_property_type]);

        if ($l_validation_failed === false)
        {
            // Update notification identifier:
            $l_data[$l_property_type]['notification']   = $l_notification_id;
            $l_result[$l_property_type]['notification'] = isys_notifications_dao::C__VALIDATION_RESULT__NOTHING;
        } //if

        foreach ($l_result[$l_property_type] as $l_property_id => $l_property_result)
        {
            // Ignore some properties which are unnecessary here:
            if (in_array(
                $l_property_id,
                [
                    'id',
                    'notification'
                ]
            ))
            {
                continue;
            } //if

            if ($l_property_result > isys_notifications_dao::C__VALIDATION_RESULT__IGNORED)
            {
                $l_validation_failed = true;
                break;
            } // if
        } //foreach

        $l_domains = [];

        $l_domain_types = array_keys($this->m_dao->get_domain_types());

        foreach ($l_domain_types as $l_domain_type)
        {
            if (isset($l_data[$l_property_type][$l_domain_type]) && (isys_notifications_dao::C__DOMAIN__OBJECTS & $l_type['domains']) && $l_result[$l_property_type][$l_domain_type] == isys_notifications_dao::C__VALIDATION_RESULT__NOTHING)
            {
                if (isys_format_json::is_json_array($l_data[$l_property_type][$l_domain_type]))
                {
                    $l_domains[$l_domain_type] = isys_format_json::decode($l_data[$l_property_type][$l_domain_type], true);
                }
                else if (is_string($l_data[$l_property_type][$l_domain_type]))
                {
                    $l_domains[$l_domain_type] = explode(',', $l_data[$l_property_type][$l_domain_type]);
                }
                else if (is_array($l_data[$l_property_type][$l_domain_type]))
                {
                    $l_domains[$l_domain_type] = $l_data[$l_property_type][$l_domain_type];
                }
                else
                {
                    throw new isys_exception_general(
                        sprintf(
                            'Failed to save notification because data format of domain type "%s" is invalid.',
                            $l_domain_type
                        )
                    );
                } //if
            }
            else
            {
                $l_domains[$l_domain_type] = $l_data[$l_property_type][$l_domain_type];
            } //if
        } //foreach

        if ($l_validation_failed === false)
        {
            $this->m_dao->add_domains($l_notification_id, $l_domains);
        } // if

        // Notification roles:
        $l_property_type            = 'notification_roles';
        $l_properties               = $this->m_dao->get_properties($l_property_type);
        $l_data[$l_property_type]   = $this->parse_user_data($l_properties);
        $l_result[$l_property_type] = $this->validate_property_data($l_properties, $l_data[$l_property_type]);

        // Update notification identifier:
        $l_data[$l_property_type]['notification']   = $l_notification_id;
        $l_result[$l_property_type]['notification'] = isys_notifications_dao::C__VALIDATION_RESULT__NOTHING;

        foreach ($l_result[$l_property_type] as $l_property_id => $l_property_result)
        {
            // Ignore some properties which are unnecessary here:
            if (in_array(
                $l_property_id,
                [
                    'id',
                    'notification'
                ]
            ))
            {
                continue;
            } //if

            if ($l_property_result > isys_notifications_dao::C__VALIDATION_RESULT__IGNORED)
            {
                $l_validation_failed = true;
                break;
            } // if
        } //foreach

        if (!isset($l_data[$l_property_type]['roles']))
        {
            $l_data[$l_property_type]['roles'] = [];
        } //if

        if ($l_validation_failed === false && $l_result[$l_property_type]['roles'] == isys_notifications_dao::C__VALIDATION_RESULT__NOTHING)
        {
            $this->m_dao->add_roles($l_notification_id, $l_data[$l_property_type]['roles']);
        } //if

        // Referenced contacts:

        if ($l_validation_failed === false && $l_result[$l_property_type]['contacts'] == isys_notifications_dao::C__VALIDATION_RESULT__NOTHING)
        {
            $this->m_dao->add_contacts($l_notification_id, $l_data['notifications']['contacts']);
        } //if

        if ($l_validation_failed)
        {
            $this->show_notification(C__NAVMODE__NEW, $l_data, $l_result);
        }
        else
        {
            $this->show_notification(C__NAVMODE__SAVE, $l_data, $l_result);
        } //if
    } //function

    /**
     * Deletes an existing notification with all dependencies and relations.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @todo Delete contact references as well.
     */
    protected function delete_notification($p_notification_id)
    {
        assert('is_int($p_notification_id)');

        $l_property_types = [
            'notifications',
            'notification_domains',
            'notification_roles'
        ];

        foreach ($l_property_types as $l_property_type)
        {
            $this->m_dao->delete($l_property_type, $p_notification_id, true);
        } // foreach
    } //function

    /**
     * Shows list of templates.
     */
    protected function show_templates()
    {
        global $index_includes;
        $l_template = $this->m_userrequest->get_template();
        try
        {
            isys_auth_notifications::instance()
                ->check(isys_auth::VIEW, "NOTIFICATIONS/MANAGE_TEMPLATES");

            $l_result_set = $this->m_dao->get_templates(null, ['notification_type' => $this->m_type], true, true);
            $l_properties = $this->m_dao->get_properties('notification_templates');

            $l_columns = [
                'id',
                'locale',
                'subject',
                'text'
            ];

            $l_entity_id_field = $l_properties['id'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

            $l_header = [];
            foreach ($l_columns as $l_column)
            {
                $l_header[$l_properties[$l_column][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $l_properties[$l_column][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
            } //foreach

            $l_list = $this->create_list($l_result_set, $l_entity_id_field, $l_header, 'modify_template_rows');

            $l_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");
            $l_template->assign('g_list', $l_list);
        }
        catch (isys_exception_general $e)
        {
            throw $e;
        }
        catch (isys_exception_auth $e)
        {
            $l_template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    } //function

    /**
     * Show notification template in view or edit mode.
     *
     * @param int    $p_mode   Show or edit/new mode?
     * @param array  $p_data   (optional) Data for all property types. Defaults to
     *                         null.
     * @param array  $p_result (optional) Validation results for all property
     *                         types. Defaults to null.
     *
     * @global array $g_config Global configuration
     */
    protected function show_template($p_mode, $p_data = null, $p_result = null)
    {
        global $g_config;
        isys_auth_notifications::instance()
            ->check(isys_auth::VIEW, "NOTIFICATIONS/MANAGE_TEMPLATES");
        $l_template = $this->m_userrequest->get_template();
        $l_navbar   = isys_component_template_navbar::getInstance();

        // Rights
        $l_auth_edit   = isys_auth_notifications::instance()
            ->is_allowed_to(isys_auth::EDIT, "NOTIFICATIONS/MANAGE_TEMPLATES");
        $l_auth_delete = isys_auth_notifications::instance()
            ->is_allowed_to(isys_auth::DELETE, "NOTIFICATIONS/MANAGE_TEMPLATES");

        // Mode:
        if ($p_mode == C__NAVMODE__NEW || $p_mode == C__NAVMODE__EDIT)
        {
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW)
                ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                ->set_active(false, C__NAVBAR_BUTTON__SAVE);

            if ($l_auth_edit)
            {
                $l_template->activate_editmode();
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
            }

        }
        else
        {
            if ($l_auth_edit)
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__NEW)
                    ->set_active(true, C__NAVBAR_BUTTON__EDIT);
            }
            if ($l_auth_delete)
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__PURGE);
            }
        } //if

        // Assign notification template identifier:
        $l_template->assign(
            'id',
            $p_data['notification_templates']['id']
        );

        // Type:

        $l_type = $this->m_dao->get_type($this->m_type);

        $l_template->assign('type_title', _L($l_type['title']))
            ->assign('type_description', nl2br(_L($l_type['description'])))
            ->assign(
                'type_templates',
                $g_config['www_dir'] . '?' . C__GET__MODULE_ID . '=' . $this->m_module_id . '&' . C__GET__TREE_NODE . '=' . self::C__MANAGE_NOTIFICATIONS . '&' . C__GET__SETTINGS_PAGE . '=' . $this->m_type
            );

        $l_properties = $this->m_dao->get_properties();

        // Locales:

        $l_locales = $this->m_dao->get_locales();

        if (isset($p_data['notification_templates']['locale']) && array_key_exists($p_data['notification_templates']['locale'], $l_locales))
        {
            $l_properties['notification_templates']['locale'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID'] = $p_data['notification_templates']['locale'];
        } //if

        // Notification type:

        if ($p_mode == C__NAVMODE__NEW || $p_mode == C__NAVMODE__EDIT)
        {
            $l_properties['notification_templates']['notification_type'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'] = $l_type['id'];
        } // if

        // Placeholders:
        $l_template->assign('placeholders', $this->m_dao->get_placeholders());

        // Assign rules and optionally data and validation results for all used
        // property types:

        $l_property_types = [
            'notification_templates'
        ];

        foreach ($l_property_types as $l_property_type)
        {
            $l_data = null;
            if (is_array($p_data) && isset($p_data[$l_property_type]))
            {
                $l_data = $p_data[$l_property_type];
            } //if

            $l_result = null;
            if (is_array($p_result) && isset($p_result[$l_property_type]))
            {
                $l_result = $p_result[$l_property_type];
            } //if

            // Assign rules:
            $l_template->smarty_tom_add_rules(
                'tom.content.bottom.content',
                $this->prepare_user_data_assignment(
                    $l_properties[$l_property_type],
                    $l_data,
                    $l_result
                )
            );
        } //foreach
    } //function

    /**
     * Loads notification template from database.
     *
     * @param int $p_template_id Notification template identifier
     *
     * @return array Associative array
     */
    protected function load_template($p_template_id)
    {
        $l_data = [];

        $l_data['notification_templates'] = $this->m_dao->get_template($p_template_id);

        return $l_data;
    } //function

    /**
     * Saves a template.
     *
     * @param int $p_template_id (optional) Notification template identifier. If
     *                           set, an existing notification template will be updated. Otherwise a new
     *                           one will be created. Defaults to null.
     */
    protected function save_template($p_template_id = null)
    {
        global $g_config;

        $l_data              = [];
        $l_result            = [];
        $l_validation_failed = false;
        $l_template_id       = null;

        // Notification template:

        $l_property_type            = 'notification_templates';
        $l_properties               = $this->m_dao->get_properties($l_property_type);
        $l_data[$l_property_type]   = $this->parse_user_data($l_properties);
        $l_result[$l_property_type] = $this->validate_property_data($l_properties, $l_data[$l_property_type]);

        $l_save_data = [];

        foreach ($l_properties as $l_property_id => $l_property_info)
        {
            // If identifier is not valid, just ignore it. A new entity will be
            // created.
            if ($l_property_id == 'id' && ((isset($l_data[$l_property_type]['id']) && $l_data[$l_property_type]['id'] < 1) || !isset($l_data[$l_property_id]['id'])))
            {
                $l_result[$l_property_type]['id'] = isys_notifications_dao::C__VALIDATION_RESULT__IGNORED;
                continue;
            } //if

            if (array_key_exists($l_property_id, $l_data[$l_property_type]) && array_key_exists(
                    $l_property_id,
                    $l_result[$l_property_type]
                ) && $l_result[$l_property_type][$l_property_id] > isys_notifications_dao::C__VALIDATION_RESULT__IGNORED
            )
            {
                $l_validation_failed = true;
                break;
            } // if

            // Save property only if create and save are provided:
            if (array_key_exists(C__PROPERTY__PROVIDES, $l_property_info))
            {
                if ((isys_notifications_dao::C__PROPERTY__PROVIDES__CREATE & $l_property_info[C__PROPERTY__PROVIDES]) || (isys_notifications_dao::C__PROPERTY__PROVIDES__SAVE & $l_property_info[C__PROPERTY__PROVIDES]))
                {
                    if ($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == 'varchar' || $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == 'text')
                    {
                        $l_data[$l_property_type][$l_property_id] = $l_data[$l_property_type][$l_property_id];
                    }
                    $l_save_data[$l_property_id] = $l_data[$l_property_type][$l_property_id];
                } //if
            } //if
        } //foreach

        if (isset($p_template_id))
        {
            // Update template identifier:
            $l_save_data['id'] = $p_template_id;
        } //if

        if ($l_validation_failed === false)
        {
            $l_template_id = $this->m_dao->save('notification_templates', $l_save_data);

            // Update notification identifier:
            $l_data[$l_property_type]['id']   = $l_template_id;
            $l_result[$l_property_type]['id'] = isys_notifications_dao::C__VALIDATION_RESULT__NOTHING;
        } //if

        if ($l_validation_failed)
        {
            $this->show_template(C__NAVMODE__NEW, $l_data, $l_result);
        }
        else
        {
            $this->show_template(C__NAVMODE__SAVE, $l_data, $l_result);
        } //if
    } //function

    /**
     * Deletes an existing notification template with all dependencies and
     * relations.
     *
     * @param int $p_template_id Notification template identifier
     */
    protected function delete_template($p_template_id)
    {
        assert('is_int($p_template_id)');

        $this->m_dao->delete('notification_templates', $p_template_id);
    } //function

    /**
     * Sorts dialog list. Used by usort().
     *
     * @param array $p_arr1
     * @param array $p_arr2
     *
     * @return int
     */
    protected function sort_dialog_list($p_arr1, $p_arr2)
    {
        return strcmp($p_arr1['val'], $p_arr2['val']);
    } //function

    /**
     * Creates an HTML table list of entities.
     *
     * @param isys_resultset $p_result_set   Result set
     * @param array          $p_properties   Properties
     * @param string         $p_row_modifier (optional) Callback to modify rows.
     *                                       Defaults to null.
     *
     * @return string
     */
    protected function create_list($p_result_set, $p_entity_id_field, $p_columns, $p_row_modifier = null)
    {
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_edit_right   = isys_auth_notifications::instance()
            ->is_allowed_to(isys_auth::EDIT, "NOTIFICATIONS/" . $this->m_notification_type_const);
        $l_delete_right = isys_auth_notifications::instance()
            ->is_allowed_to(isys_auth::DELETE, "NOTIFICATIONS/" . $this->m_notification_type_const);

        $l_amount = $p_result_set->num_rows();

        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_edit_right && $l_amount > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_delete_right && $l_amount > 0), C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        $l_objList = new isys_component_list();

        $l_objList->config(
            $p_columns,
            '?' . C__GET__MODULE_ID . '=' . $this->m_module_id . '&' . C__GET__TREE_NODE . '=' . $this->m_node . '&' . C__GET__SETTINGS_PAGE . '=' . $this->m_type . '&' . self::C__ENTITY . '=[{' . $p_entity_id_field . '}]',
            '[{' . $p_entity_id_field . '}]',
            true,
            true
        );

        if (isset($p_row_modifier))
        {
            $l_objList->set_row_modifier($this, $p_row_modifier);
        } //if

        return $l_objList->getTempTableHtml($p_result_set);
    } //function

    /**
     * Constructor
     */
    public function __construct()
    {
        global $g_comp_database;

        $this->m_module_id = C__MODULE__NOTIFICATIONS;

        $this->m_db  = $g_comp_database;
        $this->m_log = isys_factory_log::get_instance('notifications');
        $this->m_dao = new isys_notifications_dao($this->m_db, $this->m_log);
    } // function
} //class