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
 * New authorization module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_module_auth extends isys_module implements isys_module_interface, isys_module_authable
{
    // Defines whether this module will be displayed in the extras-menu.
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines, if this module shall be displayed in the systme-menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * Settings page for resetting the right system
     *
     * @var  string
     */
    const RESET_RIGHT_SYSTEM = 'reset_right_system';
    /**
     * Variable which defines, if this module is licenced.
     *
     * @var  boolean
     */
    protected static $m_licenced = true;
    /**
     * Instance of module DAO.
     *
     * @var  isys_auth_dao
     */
    protected $m_dao;
    /**
     * User request.
     *
     * @var  isys_module_request
     */
    protected $m_userrequest;

    /**
     * Static factory method for instant method chaining.
     *
     * @static
     * @return  isys_module_auth
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public static function factory()
    {
        return new self;
    } //function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_auth::instance();
    } // function

    /**
     * Initiates module.
     *
     * @param   isys_module_request $p_req
     *
     * @return  isys_module_auth
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = $p_req;

        return $this;
    } // function

    /**
     * Builds menu tree.
     *
     * @param   isys_component_tree &$p_tree
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        $i = 0;

        if (defined('C__MODULE__PRO'))
        {
            // Get only active modules
            $l_modules_res = $this->m_userrequest->get_module_manager()
                ->get_modules(null, null, true);
            $l_get         = $this->m_userrequest->get_gets();

            $l_auth_root = $p_tree->add_node(
                C__MODULE__AUTH . ++$i,
                $p_parent,
                _L('LC__MODULE__AUTH'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                        C__GET__TREE_NODE     => C__MODULE__AUTH . $i
                    ]
                ),
                '',
                '',
                0,
                '',
                '',
                isys_auth_auth::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'OVERVIEW')
            );

            $l_rights_node = $p_tree->add_node(
                C__MODULE__AUTH . ++$i,
                $l_auth_root,
                '<i class="hide">A</i><span>' . _L('LC__MODULE__AUTH__TREE__RIGHTS') . '</span>',
                '',
                '',
                '',
                0
            );

            $p_tree->add_node(
                C__MODULE__AUTH . ++$i,
                $l_auth_root,
                '<i class="hide">Z</i>' . _L('LC__MODULE__AUTH__TREE__RESET_RIGHT_SYSTEM'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                        C__GET__TREE_NODE     => C__MODULE__AUTH . $i,
                        C__GET__SETTINGS_PAGE => self::RESET_RIGHT_SYSTEM
                    ]
                ),
                '',
                '',
                (int) ($l_get[C__GET__SETTINGS_PAGE] == self::RESET_RIGHT_SYSTEM)
            );

            if (count($l_modules_res) > 0)
            {
                while ($l_row = $l_modules_res->get_row())
                {
                    $l_auth_instance = isys_module_manager::instance()
                        ->get_module_auth($l_row['isys_module__id']);

                    if ($l_auth_instance && $l_row['isys_module__status'] == C__RECORD_STATUS__NORMAL)
                    {
                        // If auth class name is isys_auth_system but the class itself is not the system module then skip it in the tree
                        if (get_class($l_auth_instance) == 'isys_auth_system' && defined($l_row['isys_module__const']) && constant(
                                $l_row['isys_module__const']
                            ) != C__MODULE__SYSTEM
                        )
                        {
                            continue;
                        } // if

                        switch ($l_row['isys_module__id'])
                        {
                            case C__MODULE__TEMPLATES:
                                $l_module_title = _L('LC__AUTH_GUI__TEMPLATES_CONDITION') . ' / ' . _L('LC__AUTH_GUI__MASS_CHANGES_CONDITION');
                                break;

                            default:
                                $l_module_title = _L($l_row['isys_module__title']);
                                break;
                        }
                        if (isys_auth_auth::instance()
                            ->is_allowed_to(isys_auth::VIEW, 'MODULE/' . $l_row['isys_module__const'])
                        )
                        {
                            $p_tree->add_node(
                                C__MODULE__AUTH . ++$i,
                                $l_rights_node,
                                $l_module_title,
                                isys_helper_link::create_url(
                                    [
                                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                                        C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                                        C__GET__TREE_NODE     => C__MODULE__AUTH . $i,
                                        C__GET__SETTINGS_PAGE => $l_row['isys_module__const'],
                                    ]
                                ),
                                '',
                                '',
                                (int) ($l_get[C__GET__SETTINGS_PAGE] == $l_row['isys_module__const']),
                                '',
                                ''
                            );
                        } // if
                    } // if
                } // while
            } // if
        } // if
    } // function

    /**
     * Start method.
     *
     * @throws  isys_exception_auth
     * @global  array $index_includes
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function start()
    {
        global $index_includes;

        $l_save = null;
        $l_get  = $this->m_userrequest->get_gets();

        isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote');

        if (array_key_exists(C__GET__AJAX, $l_get))
        {
            if (array_key_exists('func', $l_get))
            {
                // Call the internal "ajax" function, with the given method as parameter.
                $this->ajax($l_get['func']);
            }
            else if (array_key_exists('navMode', $_POST) && $_POST['navMode'] == C__NAVMODE__SAVE)
            {
                // Save action.
                try
                {
                    $l_module_constant = $l_get[C__GET__SETTINGS_PAGE];
                    $l_auth            = isys_auth_auth::instance();

                    // Check if the user is allowed to see this page.
                    $l_auth->check(isys_auth::EDIT, 'MODULE/' . $l_module_constant);

                    if ($this->save($_POST['C__AUTH__PERSON_SELECTION__HIDDEN'], constant($l_module_constant)))
                    {
                        isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                    }
                }
                catch (Exception $e)
                {
                    isys_notify::error($e->getMessage());
                } // try
            } // if
        } // if

        if (array_key_exists(C__GET__SETTINGS_PAGE, $l_get) && $l_get[C__GET__SETTINGS_PAGE] != self::RESET_RIGHT_SYSTEM)
        {
            $l_module_constant = $l_get[C__GET__SETTINGS_PAGE];
            $l_auth            = isys_auth_auth::instance();

            // Check if the user is allowed to see this page.
            $l_auth->check(isys_auth::VIEW, 'MODULE/' . $l_module_constant);

            isys_component_template_navbar::getInstance()
                ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_auth->is_allowed_to(isys_auth::EDIT, 'MODULE/' . $l_module_constant), C__NAVBAR_BUTTON__SAVE);

            // Retrieve auth-instance of the given module.
            $l_methods = [];

            if ($l_auth_instance = isys_module_manager::instance()
                ->get_module_auth($l_module_constant)
            )
            {
                $l_methods = $l_auth_instance->get_auth_methods();
            } // if

            $l_module_data = isys_module_manager::instance()
                ->get_modules(null, $l_module_constant)
                ->get_row();

            // Retrieve the rights and make sure, the titles are UTF8.
            $l_rights = isys_auth::get_rights();

            // Remove the "edit mode" parameter and add "ajax".
            $l_url = isys_glob_url_remove(isys_glob_add_to_query(C__GET__AJAX, '1'), C__CMDB__GET__EDITMODE);

            $this->m_userrequest->get_template()
                ->activate_editmode()
                ->assign('module_id', constant($l_module_constant))
                ->assign('ajax_url', $l_url)
                ->assign('auth_rights', isys_format_json::encode($l_rights))
                ->assign('auth_methods', isys_format_json::encode($l_methods))
                ->assign('auth_wildchar', isys_auth::WILDCHAR)
                ->assign('auth_empty_id', isys_auth::EMPTY_ID_PARAM)
                ->assign('auth_title', _L('LC__UNIVERSAL__MODULE') . ': "' . _L($l_module_data['isys_module__title']) . '"')
                ->assign('content_title', _L('LC__MODULE__AUTH'));

            $index_includes['contentbottomcontent'] = 'modules/auth/configuration.tpl';
        }
        else if ($l_get[C__GET__SETTINGS_PAGE] == self::RESET_RIGHT_SYSTEM)
        {
            global $g_admin_auth;

            $l_admin_auth  = $g_admin_auth;
            $l_admin_key   = array_pop(array_keys($l_admin_auth));
            $l_admin_value = array_pop(array_values($l_admin_auth));

            if (empty($l_admin_key) || empty($l_admin_value))
            {
                throw new isys_exception_auth('Credentials are not setted.');
            }
            else
            {
                $l_rules['C__AUTH__RESET_RIGHT_SYSTEM__PASSWORD']['p_bPassword'] = 1;

                $l_gets = [
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                    C__GET__AJAX          => 1
                ];

                $this->m_userrequest->get_template()
                    ->activate_editmode()
                    ->assign('ajax_handler_url', '?call=auth&ajax=1')
                    ->assign('ajax_url', isys_helper_link::create_url($l_gets))
                    ->assign('content_title', _L('LC__MODULE__AUTH'))
                    ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
                $index_includes['contentbottomcontent'] = 'modules/auth/reset_right_system.tpl';
            }
        }
        else
        {
            $l_modules = [];

            $l_module_res = $this->m_userrequest->get_module_manager()
                ->get_modules();

            if (count($l_module_res) > 0)
            {
                while ($l_row = $l_module_res->get_row())
                {
                    $l_auth_instance = isys_module_manager::instance()
                        ->get_module_auth($l_row['isys_module__id']);

                    if ($l_auth_instance && $l_row['isys_module__status'] == C__RECORD_STATUS__NORMAL)
                    {
                        // If auth class name is isys_auth_system but the class itself is not the system module then skip it in the tree
                        if (get_class($l_auth_instance) == 'isys_auth_system' && constant($l_row['isys_module__const']) != C__MODULE__SYSTEM)
                        {
                            continue;
                        } // if

                        $l_modules[$l_row['isys_module__id']] = _L($l_row['isys_module__title']);
                    } // if
                } // while
            } // if

            $l_rules = [
                'condition_filter_object' => [
                    'p_strClass'        => 'input-small',
                    'p_bInfoIconSpacer' => 0
                ],
                'condition_filter_module' => [
                    'p_arData'          => serialize($l_modules),
                    'p_strClass'        => 'input-small',
                    'p_bInfoIconSpacer' => 0,
                    'p_bDbFieldNN'      => true,
                    'p_strSelectedID'   => C__MODULE__CMDB
                ]
            ];

            $this->m_userrequest->get_template()
                ->activate_editmode()
                ->assign('ajax_handler_url', '?call=auth&ajax=1')
                ->assign('ajax_url', isys_glob_add_to_query(C__GET__AJAX, '1'))
                ->assign('auth_wildchar', isys_auth::WILDCHAR)
                ->assign('auth_empty_id', isys_auth::EMPTY_ID_PARAM)
                ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

            $index_includes['contentbottomcontent'] = 'modules/auth/indexpage.tpl';
        } // if
    } // function

    /**
     * Method for adding links to the "sticky" category bar.
     *
     * @param  isys_component_template $p_tpl
     * @param  string                  $p_tpl_var
     * @param  integer                 $p_obj_id
     * @param  integer                 $p_obj_type_id
     */
    public function process_menu_tree_links($p_tpl, $p_tpl_var, $p_obj_id, $p_obj_type_id)
    {
        global $g_config, $g_dirs;

        if (defined('C__MODULE__PRO'))
        {
            // Check if the user is allowed to see the "auth"-category.
            if ($g_config['use_auth'] && isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::VIEW, $p_obj_id, 'C__CATG__VIRTUAL_AUTH')
            )
            {
                $l_link_data = [
                    'title' => _L('LC__CMDB__CATG__AUTH'),
                    'link'  => "javascript:get_content_by_object('" . $p_obj_id . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . C__CATG__VIRTUAL_AUTH . "', '" . C__CMDB__GET__CATG . "');",
                    'icon'  => $g_dirs['images'] . 'icons/silk/lock.png'
                ];

                $p_tpl->append($p_tpl_var, ['auth' => $l_link_data], true);
            } // if
        }
    } // function

    /**
     * Ajax dispatcher for this module.
     *
     * @param   string $p_method
     *
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function ajax($p_method)
    {
        try
        {
            $p_method = 'ajax_' . $p_method;
            $l_data   = null;

            if (!method_exists($this, $p_method))
            {
                throw new isys_exception_general(
                    _L(
                        'LC__AUTH__EXCEPTION__MISSING_METHOD',
                        [
                            $p_method,
                            get_class($this)
                        ]
                    )
                );
            } // if

            switch ($p_method)
            {
                case 'ajax_retrieve_paths':
                    $l_data = $this->ajax_retrieve_paths((int) $_POST['obj_id'], (int) $_POST['module_id']);
                    break;

                case 'ajax_reset_right_system':
                    $l_data = $this->ajax_reset_right_system($_POST['username'], $_POST['password']);
                    break;

                case 'ajax_retrieve_parameter':
                    // First we check if the auth class brings a "retrieve_parameter" method of its own.
                    if (defined($_GET[C__GET__SETTINGS_PAGE]) || is_numeric($_GET[C__GET__SETTINGS_PAGE]))
                    {
                        $l_auth_instance = isys_module_manager::instance()
                            ->get_module_auth($_GET[C__GET__SETTINGS_PAGE]);

                        if ($l_auth_instance && method_exists($l_auth_instance, 'retrieve_parameter'))
                        {
                            $l_data = $l_auth_instance->retrieve_parameter($_POST['method'], $_POST['param'], $_POST['counter'], (bool) $_POST['edit_mode']);

                            if ($l_data && is_array($l_data))
                            {
                                break;
                            } // if
                        } // if
                    } // if

                    $l_data = $this->ajax_retrieve_parameter($_POST['method'], $_POST['param'], $_POST['counter'], (bool) $_POST['edit_mode']);
                    break;

                default:
                    $l_data = call_user_func(
                        [
                            $this,
                            $p_method
                        ],
                        $_POST['method'],
                        $_POST['param'],
                        $_POST['counter'],
                        (bool) $_POST['edit_mode']
                    );
                    break;
            }

            $l_return = [
                'success' => true,
                'message' => null,
                'data'    => $l_data
            ];
        }
        catch (Exception $e)
        {
            $l_return = [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        } // try

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);
        die;
    } // function

    /**
     * Method for saving the configuration.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_module_id
     *
     * @throws  isys_exception_general
     * @return  string
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function save($p_obj_id, $p_module_id)
    {
        if (!($p_obj_id > 0 && $p_module_id > 0))
        {
            throw new isys_exception_general(_L('LC__AUTH_GUI__EXCEPTION__MISSING_PARAM'));
        } // if

        /**
         * Invalidate person's auth cache after updating rights
         */
        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.auth.afterRemoveAllRights',
                [
                    'isys_auth_cmdb_objects',
                    'invalidate_cache'
                ]
            );

        if (!$this->m_dao->remove_all_paths($p_obj_id, $p_module_id))
        {
            // This should not happen... But you'll never know.
            throw new isys_exception_general(_L('LC__AUTH_GUI__EXCEPTION__REMOVING_OLD_PATHS'));
        } // if

        $l_path_data = [];

        // This is necessary for finding all paths and bring them in the right syntax... Maybe we can clean this up.
        foreach ($_POST as $l_key => $l_value)
        {
            if (strpos($l_key, 'method_') === 0)
            {
                $i = (int) substr($l_key, 7);

                $l_param = $this->get_gui_param($i);
                $l_right = (array_key_exists('right_' . $i, $_POST)) ? $_POST['right_' . $i] : isys_auth::VIEW;

                // Because of the current "syntax" every path and every right needs an own row in the DB. So lets begin!
                if (is_array($l_param))
                {
                    foreach ($l_param as $l_param_item)
                    {
                        if (is_array($l_right))
                        {
                            foreach ($l_right as $l_right_item)
                            {
                                $l_path_data[$l_value][$l_param_item][] = $l_right_item;
                            } // foreach
                        }
                        else
                        {
                            $l_path_data[$l_value][$l_param_item][] = $l_right;
                        } // if
                    } // foreach
                }
                else
                {
                    if (is_array($l_right))
                    {
                        foreach ($l_right as $l_right_item)
                        {
                            $l_path_data[$l_value][$l_param][] = $l_right_item;
                        } // foreach
                    }
                    else
                    {
                        $l_path_data[$l_value][$l_param][] = $l_right;
                    } // if
                } // if
            } // if
        } // foreach

        return $this->m_dao->create_paths($p_obj_id, $p_module_id, $l_path_data);
    } // function

    /**
     * Retrieve the "param" content from the GUI's POST-data.
     *
     * @param   integer $p_count
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function get_gui_param($p_count)
    {
        $l_plus = null;

        // Checks for the "All"-Button and sets the wildchar.
        if (array_key_exists('auth_param_button_val_' . $p_count, $_POST) && $_POST['auth_param_button_val_' . $p_count] == '1')
        {
            // This is a special route, like for example "All categories in object type server" > "*+C__OBJTYPE__SERVER"
            if (in_array(
                $_POST['method_' . $p_count],
                [
                    'category_in_obj_type',
                    'category_in_object',
                    'category_in_location'
                ]
            ))
            {
                return isys_auth::WILDCHAR . '+' . ($_POST['auth_param_form_' . $p_count . 'plus__HIDDEN'] ?: $_POST['auth_param_form_' . $p_count . 'plus']);
            } // if

            return isys_auth::WILDCHAR;
        } // if

        // Will occur for object, location and some other browsers.
        if (isset($_POST['auth_param_form_' . $p_count . '__HIDDEN']) && !empty($_POST['auth_param_form_' . $p_count . '__HIDDEN']))
        {
            return isys_format_json::decode($_POST['auth_param_form_' . $p_count . '__HIDDEN'], true);
        } // if

        // We check for additional parameters.
        if (!empty($_POST['auth_param_form_' . $p_count . 'plus']))
        {
            $l_plus = '+' . $_POST['auth_param_form_' . $p_count . 'plus'];
        } // if

        // We check for additional object- / location-browser.
        if (!empty($_POST['auth_param_form_' . $p_count . 'plus__HIDDEN']))
        {
            $l_plus = '+' . $_POST['auth_param_form_' . $p_count . 'plus__HIDDEN'];
        } // if

        // It may happen, that the parameter is an array, when selecting multiple values.
        if (is_array($_POST['auth_param_form_' . $p_count]))
        {
            if ($l_plus)
            {
                foreach ($_POST['auth_param_form_' . $p_count] as &$l_param)
                {
                    $l_param .= $l_plus;
                } // foreach
            } // if

            return $_POST['auth_param_form_' . $p_count];
        } // if

        return $_POST['auth_param_form_' . $p_count] . $l_plus;
    } // function

    /**
     * Method for retrieving the "parameter" in the configuration GUI. Gets called generically by "ajax()" method.
     *
     * @see     $this->ajax();
     *
     * @param   string  $p_method
     * @param   string  $p_param
     * @param   integer $p_counter
     * @param   boolean $p_editmode
     * @param   boolean $p_combo_param This parameter is used, when more than one box is displayed at once (category in object, ...).
     *
     * @return  array
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function ajax_retrieve_parameter($p_method, $p_param, $p_counter, $p_editmode = false, $p_combo_param = false)
    {
        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        // The "empty-id" parameter will only show up
        if ($p_param != isys_auth::EMPTY_ID_PARAM)
        {
            switch ($p_method)
            {
                case 'object':
                    $l_popup  = new isys_smarty_plugin_f_popup();
                    $l_params = [
                        'name'                                          => 'auth_param_form_' . $p_counter . ($p_combo_param ? 'plus' : ''),
                        'p_strPopupType'                                => 'browser_object_ng',
                        isys_popup_browser_object_ng::C__EDIT_MODE      => $p_editmode,
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        'p_bInfoIconSpacer'                             => 0,
                        'p_strClass'                                    => 'input-' . ($p_combo_param ? 'mini' : 'small'),
                        'p_strSelectedID'                               => $p_param
                    ];

                    $l_return['html'] = $l_popup->navigation_edit($this->m_userrequest->get_template(), $l_params);
                    break;

                case 'location':
                    $l_popup  = new isys_smarty_plugin_f_popup();
                    $l_params = [
                        'name'              => 'auth_param_form_' . $p_counter . ($p_combo_param ? 'plus' : ''),
                        'p_strPopupType'    => 'browser_location',
                        'edit'              => $p_editmode,
                        'p_bInfoIconSpacer' => 0,
                        'p_strClass'        => 'input-' . ($p_combo_param ? 'mini' : 'small'),
                        'p_strSelectedID'   => $p_param,
                        'only_container'    => true
                    ];

                    if ($p_editmode === false)
                    {
                        $l_params['plain'] = 1;
                    } // if

                    $l_return['html'] = $l_popup->navigation_edit($this->m_userrequest->get_template(), $l_params);
                    break;

                case 'object_type':
                    // Convert the parameter (a constant) back to upper-case.
                    $p_param = strtoupper($p_param);

                    $l_object_types = [];
                    $l_data         = isys_cmdb_dao::instance($this->m_db)
                        ->get_object_type();

                    foreach ($l_data as $l_object_type)
                    {
                        $l_object_types[$l_object_type['isys_obj_type__const']] = $l_object_type['LC_isys_obj_type__title'];
                    } // foreach

                    if (strpos($p_param, ',') !== false && !$p_combo_param)
                    {
                        // Remove all selections, that do not (or "no longer") exist.
                        $p_param = implode(
                            ',',
                            array_filter(
                                explode(',', $p_param),
                                function ($p_const)
                                {
                                    return defined($p_const);
                                }
                            )
                        );
                    }
                    else
                    {
                        if (!defined($p_param))
                        {
                            $p_param = null;
                        } // if
                    } // if

                    $l_dialog = new isys_smarty_plugin_f_dialog();
                    $l_params = [
                        'name'              => 'auth_param_form_' . $p_counter . ($p_combo_param ? 'plus' : '[]'),
                        'p_arData'          => serialize($l_object_types),
                        'p_multiple'        => !$p_combo_param,
                        'chosen'            => !$p_combo_param,
                        'p_editMode'        => $p_editmode,
                        'p_bDbFieldNN'      => 1,
                        'p_bInfoIconSpacer' => 0,
                        'p_strClass'        => 'input-' . ($p_combo_param ? 'mini' : 'small'),
                        'p_strSelectedID'   => $p_param
                    ];

                    $l_return['html'] = $l_dialog->navigation_edit($this->m_userrequest->get_template(), $l_params);
                    break;

                case 'category':
                    $l_cmdb_dao   = new isys_cmdb_dao($this->m_db);
                    $l_cat_data   = $l_cmdb_dao->get_all_categories();
                    $l_cat_custom = $l_cmdb_dao->get_all_catg_custom();

                    // Category type strings
                    $l_global     = _L('LC__UNIVERSAL__GLOBAL');
                    $l_specific   = _L('LC__UNIVERSAL__SPECIFIC');
                    $l_custom     = _L('LC__CMDB__CUSTOM_CATEGORIES');
                    $l_categories = [];

                    /*
                     * Global categories
                     */
                    if (isset($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL]) && count($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL]) > 0)
                    {
                        foreach ($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL] as $l_catg)
                        {
                            if ($l_catg['id'] == C__CATG__CUSTOM_FIELDS) continue;

                            if ($l_catg['parent'] !== null && isset($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_catg['parent']]))
                            {
                                $l_title = _L($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_catg['parent']]['title']) . ' > ' . _L($l_catg['title']);
                            }
                            else
                            {
                                $l_title = _L($l_catg['title']);
                            }

                            $l_categories[$l_global][$l_catg['const']] = $l_title;
                        } // foreach
                        asort($l_categories[$l_global]);
                    }

                    /*
                     * Specific categories
                     */
                    if (isset($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC]) && count($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC]) > 0)
                    {
                        foreach ($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC] as $l_cats)
                        {
                            if ($l_cats['parent'] !== null && isset($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_cats['parent']]))
                            {
                                $l_title = _L($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_cats['parent']]['title']) . ' > ' . _L($l_cats['title']);
                            }
                            else
                            {
                                $l_title = _L($l_cats['title']);
                            }

                            $l_categories[$l_specific][$l_cats['const']] = $l_title;
                        } // foreach
                        asort($l_categories[$l_specific]);
                    }

                    /*
                     * Custom categories
                     */
                    if ($l_cat_custom->num_rows() > 0)
                    {
                        while ($l_category_data = $l_cat_custom->get_row())
                        {
                            $l_categories[$l_custom][$l_category_data['isysgui_catg_custom__const']] = _L($l_category_data['isysgui_catg_custom__title']);
                        } // while
                        asort($l_categories[$l_custom]);;
                    } // if

                    // Initialize dialog
                    $l_dialog = new isys_smarty_plugin_f_dialog();

                    $l_params = [
                        'name'              => 'auth_param_form_' . $p_counter . '[]',
                        'p_arData'          => $l_categories,
                        'p_multiple'        => true,
                        'chosen'            => true,
                        'p_editMode'        => $p_editmode,
                        'p_bDbFieldNN'      => 1,
                        'p_bInfoIconSpacer' => 0,
                        'p_strClass'        => 'input-' . ($p_combo_param ? 'mini' : 'small'),
                        'p_strSelectedID'   => strtoupper($p_param),
                        'p_bSort'           => false
                    ];

                    $l_return['html'] = $l_dialog->navigation_edit($this->m_userrequest->get_template(), $l_params);
                    break;

                case 'category_in_obj_type':
                    list($l_category, $l_obj_type) = explode('+', $p_param);

                    // Call the same method for "object types" and "categories".
                    $l_category = $this->ajax_retrieve_parameter('category', $l_category, $p_counter, $p_editmode, true);
                    $l_obj_type = $this->ajax_retrieve_parameter('object_type', $l_obj_type, $p_counter, $p_editmode, true);

                    $l_return['html'] = $l_category['html'] . ' in ' . $l_obj_type['html'];
                    break;

                case 'category_in_object':
                    list($l_category, $l_objects) = explode('+', $p_param);

                    // Call the same method for "objects" and "categories".
                    $l_category = $this->ajax_retrieve_parameter('category', $l_category, $p_counter, $p_editmode, true);
                    $l_object   = $this->ajax_retrieve_parameter('object', $l_objects, $p_counter, $p_editmode, true);

                    $l_return['html'] = $l_category['html'] . ' in ' . $l_object['html'];
                    break;

                case 'category_in_location':
                    list($l_category, $l_objects) = explode('+', $p_param);

                    // Call the same method for "objects" and "categories".
                    $l_category = $this->ajax_retrieve_parameter('category', $l_category, $p_counter, $p_editmode, true);
                    $l_location = $this->ajax_retrieve_parameter('location', $l_objects, $p_counter, $p_editmode, true);

                    $l_return['html'] = $l_category['html'] . ' in ' . $l_location['html'];
                    break;

                case 'modules':
                    // Init the dialog admin.
                    $l_data    = [];
                    $l_modules = isys_module_manager::instance()
                        ->get_modules();

                    if (count($l_modules) > 0)
                    {
                        while ($l_row = $l_modules->get_row())
                        {
                            $l_auth_instance = isys_module_manager::instance()
                                ->get_module_auth($l_row['isys_module__id']);

                            // We only want to select modules, which have their own auth-classes.
                            if ($l_auth_instance)
                            {
                                $l_data[$l_row['isys_module__const']] = _L($l_row['isys_module__title']);
                            } // if
                        } // while
                    } // if

                    $l_dialog = new isys_smarty_plugin_f_dialog();
                    $l_params = [
                        'name'              => 'auth_param_form_' . $p_counter,
                        'p_arData'          => serialize($l_data),
                        'p_editMode'        => $p_editmode,
                        'p_bDbFieldNN'      => 1,
                        'p_bInfoIconSpacer' => 0,
                        'p_strClass'        => 'input-small',
                        'p_strSelectedID'   => strtoupper($p_param)
                    ];

                    $l_return['html'] = $l_dialog->navigation_edit($this->m_userrequest->get_template(), $l_params);
                    break;

                case 'boolean':
                    break;

                default:
                    throw new isys_exception_general(
                        'Please provide a function for auth-method "' . $p_method . '" with parameter "' . isys_format_json::encode($p_param) . '".'
                    );
            } // switch
        } // if

        return $l_return;
    } // function

    /**
     * Method for retrieving the paths, defined for a person and a module.
     *
     * @see     $this->ajax();
     *
     * @param   integer $p_obj_id
     * @param   integer $p_module_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function ajax_retrieve_paths($p_obj_id, $p_module_id)
    {
        $l_auth_dao = new isys_auth_dao($this->m_db);

        $l_paths       = $l_auth_dao->get_paths($p_obj_id, $p_module_id);
        $l_group_paths = $l_auth_dao->get_group_paths_by_person($p_obj_id, $p_module_id);

        $l_return = [
            'paths'       => $l_paths ? $l_auth_dao->build_paths_by_result($l_paths) : [],
            'group_paths' => $l_group_paths ? $l_auth_dao->build_paths_by_result($l_group_paths) : []
        ];

        try
        {
            /* @var  isys_auth $l_module_auth */
            $l_module_auth = isys_module_manager::instance()
                ->get_module_auth($p_module_id);

            if ($l_module_auth)
            {
                $l_module_auth->combine_paths($l_return['paths'])
                    ->combine_paths($l_return['group_paths']);
            } // if
        }
        catch (Exception $e)
        {
            ; // Nothing to see here citizen, move along.
        } // try

        return $l_return;
    } // if

    /**
     * Method for resetting the right system for the current mandator
     *
     * @param $p_username
     * @param $p_password
     *
     * @return array
     */
    private function ajax_reset_right_system($p_username, $p_password)
    {
        global $g_admin_auth;

        if (isset($g_admin_auth[$p_username]) && $g_admin_auth[$p_username] == $p_password)
        {
            if ($this->reset_right_system())
            {
                return [
                    'success' => true,
                    'message' => 'Right system has been resetted'
                ];
            } // if
        }
        else
        {
            return [
                'success' => false,
                'message' => 'Credentials are wrong or are not setted.'
            ];
        } // if
    } // function

    /**
     * Method where the actual reset of the right system happens
     *
     * @return bool
     */
    private function reset_right_system()
    {
        global $g_comp_session;

        $l_ignore_methods = [
            'category_in_obj_type',
            'category_in_object',
            'category_in_location'
        ];

        $l_modules = isys_module_manager::instance()
            ->get_modules();

        $l_current_user = $g_comp_session->get_user_id();

        // Remove all rights
        $this->m_dao->remove_all_paths($l_current_user);

        // Set right system for the current user
        $l_system_module = false;
        while ($l_row = $l_modules->get_row())
        {
            $l_auth_instance = isys_module_manager::instance()
                ->get_module_auth($l_row['isys_module__id']);
            $l_auth_paths    = [];

            if ($l_auth_instance)
            {
                if (get_class($l_auth_instance) == 'isys_auth_system')
                {
                    if (!$l_system_module)
                    {
                        $l_system_module          = true;
                        $l_row['isys_module__id'] = C__MODULE__SYSTEM;
                    }
                    else
                    {
                        continue;
                    } // if

                } // if

                $l_auth_module_obj = isys_module_manager::instance()
                    ->get_module_auth($l_row['isys_module__id']);
            }
            else
            {
                continue;
            } // if

            $l_auth_methods = [];

            if ($l_auth_module_obj)
            {
                $l_auth_methods = $l_auth_module_obj->get_auth_methods();
            } // if

            $l_rights_supervisor = [isys_auth::SUPERVISOR];
            // Set path array
            foreach ($l_auth_methods AS $l_method => $l_content)
            {
                if (in_array($l_method, $l_ignore_methods)) continue;

                if (isset($l_content['rights']))
                {
                    // get only the rights which are defined in $l_content['rights']
                    if (in_array(isys_auth::VIEW, $l_content['rights']) && count($l_content['rights']) > 1)
                    {
                        $l_key = array_search(isys_auth::VIEW, $l_content['rights']);
                        unset($l_content['rights'][$l_key]);
                    }
                    $l_rights = $l_content['rights'];
                }
                else
                {
                    $l_rights = $l_rights_supervisor;
                }

                if ($l_content['type'] == 'boolean')
                {
                    $l_auth_paths[$l_method][null] = $l_rights;
                }
                else
                {
                    $l_auth_paths[$l_method][isys_auth::WILDCHAR] = $l_rights;
                } // if
            } // foreach
            $this->m_dao->create_paths($l_current_user, $l_row['isys_module__id'], $l_auth_paths);
        } // while
        isys_caching::factory('auth-' . $l_current_user)
            ->clear();

        return true;
    } // function

    /**
     * Module constructor.
     *
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_module_id = C__MODULE__AUTH;
        $this->m_db        = isys_application::instance()->database;
        $this->m_dao       = new isys_auth_dao($this->m_db);
    } // function
} // class