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
 * IT-Service module class.
 *
 * @package     modules
 * @subpackage  itservice
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4
 */
class isys_module_itservice extends isys_module implements isys_module_interface, isys_module_authable
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = true;
    const DISPLAY_IN_SYSTEM_MENU = false;

    // These "PAGE__*" constants represent the different navigation points.
    const PAGE__TYPE_CONFIG   = 'type-config';
    const PAGE__TYPE_LIST     = 'type-list';
    const PAGE__FILTER_CONFIG = 'filter-config';
    /**
     * Variable which holds the itservice DAO class.
     *
     * @var  isys_itservice_dao_filter_config
     */
    protected $m_dao = null;
    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * Module request.
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
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_itservice::instance();
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_itservice
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;
        $this->m_db     = $p_req->get_database();
        $this->m_tpl    = $p_req->get_template()
            ->assign('www_dir', self::get_tpl_www_dir());
        $this->m_dao    = new isys_itservice_dao_filter_config($this->m_db);

        return $this;
    } // function

    /**
     * Callback method for the signal-slot logic.
     *
     * @param  isys_popup_browser_object_ng $p_browser
     * @param  array                        $p_params
     * @param  array                        $p_object_types
     */
    public function before_object_browser_type_assignment(isys_popup_browser_object_ng $p_browser, array $p_params = [], array $p_object_types = [])
    {
        if (!$p_browser->has_object_type_filter(C__OBJTYPE__IT_SERVICE))
        {
            $p_browser->add_object_type_filter(C__OBJTYPE__IT_SERVICE, _L('LC__OBJTYPE__IT_SERVICE'));
        } // if
    } // function

    /**
     * Callback function for construction of breadcrumb navigation.
     *
     * @param   array $p_gets
     *
     * @return  boolean
     */
    public function breadcrumb_get(&$p_gets)
    {
        $l_return = [];

        $l_gets = $this->m_modreq->get_gets();
        $l_id   = isset($l_gets[C__GET__ID]) ? (int) $l_gets[C__GET__ID] : false;

        switch ($l_gets[C__GET__SETTINGS_PAGE])
        {
            case self::PAGE__FILTER_CONFIG:
                $l_return[] = [
                    _L('LC__ITSERVICE__CONFIG') => [
                        C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];

                if ($l_id)
                {
                    $l_config = $this->m_dao->get_data($l_id);

                    $l_return[] = [
                        $l_config['isys_itservice_filter_config__title'] => [
                            C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                            C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE],
                            C__GET__ID            => $l_id
                        ]
                    ];
                } // if
                break;

            case self::PAGE__TYPE_LIST:
                $l_return[] = [
                    _L('LC__ITSERVICE__TYPES') => [
                        C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];

                if ($l_id)
                {
                    $l_config = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
                        ->get_data($l_id);

                    $l_return[] = [
                        $l_config['isys_its_type__title'] => [
                            C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                            C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE],
                            C__GET__ID            => $l_id
                        ]
                    ];
                } // if
                break;

            case self::PAGE__TYPE_CONFIG:
                $l_return[] = [
                    _L('LC__ITSERVICE__TYPE_CONFIGURATION') => [
                        C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                        C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE]
                    ]
                ];

                if ($l_id)
                {
                    $l_config = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
                        ->get_data($l_id);

                    $l_return[] = [
                        $l_config['isys_its_type__title'] => [
                            C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                            C__GET__SETTINGS_PAGE => $l_gets[C__GET__SETTINGS_PAGE],
                            C__GET__ID            => $l_id
                        ]
                    ];
                } // if
                break;
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

        $l_cnt = 0;

        $l_auth           = isys_auth_itservice::instance();
        $l_obj_type_right = isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__IT_SERVICE');

        if ($p_system_module === false)
        {
            $l_page = $_GET[C__GET__SETTINGS_PAGE] ?: self::PAGE__FILTER_CONFIG;

            $l_root = $p_tree->add_node(
                C__MODULE__ITSERVICE . $l_cnt,
                $p_parent,
                _L('LC__MODULE__ITSERVICE')
            );
            $l_cnt++;

            $p_tree->add_node(
                C__MODULE__ITSERVICE . $l_cnt,
                $l_root,
                _L('LC__ITSERVICE__CONFIG'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                        C__GET__TREE_NODE     => C__MODULE__ITSERVICE . $l_cnt,
                        C__GET__SETTINGS_PAGE => self::PAGE__FILTER_CONFIG
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/cog.png',
                (int) ($l_page == self::PAGE__FILTER_CONFIG),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM)
            );
            $l_cnt++;

            // Directory for all the IT-Service types (works just like the relations in extras > cmdb > relations)
            $l_type_list = $p_tree->add_node(
                C__MODULE__ITSERVICE . $l_cnt,
                $l_root,
                _L('LC__ITSERVICE__TYPES'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID        => C__MODULE__ITSERVICE,
                        C__GET__TREE_NODE        => C__MODULE__ITSERVICE . $l_cnt,
                        C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__IT_SERVICE,
                        C__GET__SETTINGS_PAGE    => self::PAGE__TYPE_LIST
                    ]
                ),
                '',
                '',
                (int) (self::PAGE__TYPE_LIST == $_GET[C__GET__SETTINGS_PAGE] && empty($_GET[C__GET__ID])),
                '',
                '',
                $l_obj_type_right
            );
            $l_cnt++;

            $l_itservice_types = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
                ->get_data();

            if (is_array($l_itservice_types) && count($l_itservice_types))
            {
                foreach ($l_itservice_types as $l_type_id => $l_type_data)
                {
                    $p_tree->add_node(
                        C__MODULE__ITSERVICE . $l_cnt,
                        $l_type_list,
                        _L($l_type_data['isys_its_type__title']),
                        isys_helper_link::create_url(
                            [
                                C__GET__MODULE_ID        => C__MODULE__ITSERVICE,
                                C__GET__TREE_NODE        => C__MODULE__ITSERVICE . $l_cnt,
                                C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__IT_SERVICE,
                                C__GET__SETTINGS_PAGE    => self::PAGE__TYPE_LIST,
                                C__GET__ID               => $l_type_id
                            ]
                        ),
                        '',
                        $g_dirs['images'] . 'icons/silk/page_white_stack.png',
                        (int) ($l_type_id == $_GET[C__GET__ID]),
                        '',
                        '',
                        $l_obj_type_right
                    );
                    $l_cnt++;
                } // foreach
            } // if

            $p_tree->add_node(
                C__MODULE__ITSERVICE . $l_cnt,
                $l_root,
                _L('LC__ITSERVICE__TYPE_CONFIGURATION'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                        C__GET__TREE_NODE     => C__MODULE__ITSERVICE . $l_cnt,
                        C__GET__SETTINGS_PAGE => self::PAGE__TYPE_CONFIG
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/tag_blue.png',
                (int) (self::PAGE__TYPE_CONFIG == $_GET[C__GET__SETTINGS_PAGE]),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM)
            );
        } // if
    } // function

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @global  array $g_config
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tpl_www_dir()
    {
        global $g_config;

        return $g_config['www_dir'] . 'src/classes/modules/itservice/templates/';
    } // function

    /**
     * Signal Slot initialization.
     */
    public function initslots()
    {
        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.cmdb.beforeObjectBrowserTypeAssignment',
                [
                    $this,
                    'before_object_browser_type_assignment'
                ]
            );
    } // function

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @return  bool    true
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_url_exploded        = explode('?', $_SERVER['HTTP_REFERER']);
        $l_url_parameters      = $l_url_exploded[1];
        $l_parameters_exploded = explode('&', $l_url_parameters);

        $l_params = array_pop(
            array_map(
                function ($p_arg)
                {
                    $l_return = [];
                    foreach ($p_arg AS $l_content)
                    {
                        list($l_key, $l_value) = explode('=', $l_content);
                        $l_return[$l_key] = $l_value;
                    }

                    return $l_return;
                },
                [$l_parameters_exploded]
            )
        );

        $p_text[] = _L('LC__UNIVERSAL__IT_SERVICES') . ' ' . _L('LC__UNIVERSAL__MODULE');

        if (isset($l_params[C__GET__SETTINGS_PAGE]))
        {
            switch ($l_params[C__GET__SETTINGS_PAGE])
            {
                case self::PAGE__TYPE_CONFIG:
                    $p_text[] = _L('LC__ITSERVICE__TYPE_CONFIGURATION');
                    break;
                case self::PAGE__FILTER_CONFIG:
                    $p_text[] = _L('LC__ITSERVICE__CONFIG');
                    if (isset($l_params['id']))
                    {
                        $l_config = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_itservice_filter_config')
                            ->get_data($l_params['id']);
                        $p_text[] = $l_config['isys_itservice_filter_config__title'];
                    }
                    break;
                case self::PAGE__TYPE_LIST:
                default:
                    $p_text[] = _L('LC__ITSERVICE__TYPES');
                    if (isset($l_params['id']))
                    {
                        $l_config = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
                            ->get_data($l_params['id']);
                        $p_text[] = $l_config['isys_its_type__title'];
                    }
                    break;
            } // switch
        } // if

        $p_link = $l_url_parameters;

        return true;
    } // function

    /**
     * Start method.
     *
     * @throws  isys_exception_general
     * @return  isys_module_itservice
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function start()
    {
        $l_page = $_GET[C__GET__SETTINGS_PAGE];
        $l_auth = isys_auth_itservice::instance();

        // Build the module tree, but only if we are not in the system-module.
        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();

            $this->build_tree($l_tree, false, -1);

            $this->m_tpl->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE] ?: C__MODULE__ITSERVICE . '2'));
        } // if

        $this->m_tpl->assign('tpl_www_dir', self::get_tpl_www_dir());

        switch ($l_page)
        {
            default:
            case self::PAGE__TYPE_LIST:
                isys_auth_cmdb::instance()
                    ->check(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__IT_SERVICE');
                $this->process_type_list();
                break;

            case self::PAGE__TYPE_CONFIG:
                $l_auth->check(isys_auth::VIEW, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->process_type_config();
                break;

            case self::PAGE__FILTER_CONFIG:
                $l_auth->check(isys_auth::VIEW, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->process_filter_config();
                break;
        } // switch

        return $this;
    } // function

    /**
     * Process the "filter config" page.
     */
    private function process_filter_config()
    {
        $l_auth    = isys_auth_itservice::instance();
        $l_id      = $_POST[C__GET__ID][0] ?: $_GET[C__GET__ID];
        $l_navmode = $_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE];

        switch ($l_navmode)
        {
            case C__NAVMODE__DELETE:
                $l_auth->check(isys_auth::DELETE, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->filter_config__delete();
                break;

            case C__NAVMODE__SAVE:
                $l_auth->check(isys_auth::EDIT, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->filter_config__save($l_id);
                break;

            case C__NAVMODE__EDIT:
                $l_auth->check(isys_auth::EDIT, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->filter_config__edit($l_id);
                break;

            case C__NAVMODE__NEW:
                $l_auth->check(isys_auth::EDIT, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->filter_config__edit();
                break;

            default:
                $l_auth->check(isys_auth::VIEW, 'FILTER_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->filter_config__list();
        } // switch
    } // function

    /**
     * This method will display the it-service filter configuration list.
     */
    private function filter_config__list()
    {
        global $index_includes;

        $l_auth                        = isys_auth_itservice::instance();
        $l_url_params                  = $_GET;
        $l_url_params[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        unset($l_url_params[C__GET__MAIN_MENU__NAVIGATION_ID], $l_url_params[C__GET__ID]);

        $l_list_headers = [
            'isys_itservice_filter_config__id'    => 'ID',
            'isys_itservice_filter_config__title' => 'LC__ITSERVICE__CONFIG__TITLE'
        ];

        $l_filter_result = $this->m_dao->get_data_raw();
        $l_filter_count  = count($l_filter_result);

        $l_list = new isys_component_list(null, $l_filter_result);

        $l_list->config(
            $l_list_headers,
            isys_helper_link::create_url($l_url_params) . '&' . C__GET__ID . '=[{isys_itservice_filter_config__id}]',
            '[{isys_itservice_filter_config__id}]'
        );

        if ($l_list->createTempTable())
        {
            $this->m_tpl->assign('list', $l_list->getTempTableHtml());
        } // if

        $l_write  = $l_auth->is_allowed_to(isys_auth::EDIT, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
        $l_delete = $l_auth->is_allowed_to(isys_auth::DELETE, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);

        isys_component_template_navbar::getInstance()
            ->set_active($l_write, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_filter_count > 0 && $l_write), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_filter_count > 0 && $l_delete), C__NAVBAR_BUTTON__DELETE)
            ->set_visible($l_write, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_write, C__NAVBAR_BUTTON__EDIT)
            ->set_visible($l_delete, C__NAVBAR_BUTTON__DELETE);

        $this->m_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'list.tpl';
    } // function

    /**
     * This method will be used to display the "new" and "edit" mode of the it-service filter configuration.
     *
     * @param  integer $p_id
     */
    private function filter_config__edit($p_id = null)
    {
        global $index_includes;

        $l_rules = $l_config = [];

        if ($p_id > 0)
        {
            $l_config = $this->m_dao->get_data($p_id);

            $l_rules['C__ITSERVICE__CONFIG__TITLE']['p_strValue']         = $l_config['isys_itservice_filter_config__title'];
            $l_rules['C__ITSERVICE__CONFIG__TITLE']['p_strClass']         = 'large';
            $l_rules['C__ITSERVICE__CONFIG__PRIORITY']['p_strSelectedID'] = $l_config['formatted__data']['priority'];
            $l_rules['C__ITSERVICE__CONFIG__LEVEL']['p_strSelectedID']    = $l_config['formatted__data']['level'];
            $l_rules['id']['p_strValue']                                  = $p_id;
        } // if

        $l_weighting      = [];
        $l_weighting_data = isys_factory_cmdb_dialog_dao::get_instance('isys_weighting', $this->m_db)
            ->get_data();

        foreach ($l_weighting_data as $l_item)
        {
            $l_weighting[$l_item['isys_weighting__id']] = _L($l_item['isys_weighting__title']);
        } // foreach

        $l_rules['C__ITSERVICE__CONFIG__RELATIONTYPE']['p_arData']   = $this->m_dao->get_relationtype_filter_data($p_id);
        $l_rules['C__ITSERVICE__CONFIG__RELATIONTYPE']['p_strClass'] = 'input';
        $l_rules['C__ITSERVICE__CONFIG__PRIORITY']['p_arData']       = $l_weighting;
        $l_rules['C__ITSERVICE__CONFIG__PRIORITY']['p_strClass']     = 'input';
        $l_rules['C__ITSERVICE__CONFIG__PRIORITY']['p_bSort']        = false;
        $l_rules['C__ITSERVICE__CONFIG__OBJECT_TYPE']['p_arData']    = $this->m_dao->get_objecttype_filter_data($p_id);
        $l_rules['C__ITSERVICE__CONFIG__OBJECT_TYPE']['p_strClass']  = 'input';
        $l_rules['C__ITSERVICE__CONFIG__LEVEL']['p_arData']          = $this->m_dao->get_level_filter_data();
        $l_rules['C__ITSERVICE__CONFIG__LEVEL']['p_strClass']        = 'input';
        $l_rules['C__ITSERVICE__CONFIG__CMDB_STATUS']['p_arData']    = $this->m_dao->get_cmdb_status_filter_data($p_id);
        $l_rules['C__ITSERVICE__CONFIG__CMDB_STATUS']['p_strClass']  = 'input';

        $this->m_tpl->assign('filter_config', $l_config)
            ->activate_editmode()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'filter-config.tpl';
    } // function

    /**
     * This method will save the given data and internally call the edit method, with the newly created (or given) ID.
     *
     * @param  integer $p_id
     */
    private function filter_config__save($p_id)
    {
        $l_config = [
            'priority' => $_POST['config_priority'] ? $_POST['C__ITSERVICE__CONFIG__PRIORITY'] : null,
            'level'    => $_POST['config_level'] ? $_POST['C__ITSERVICE__CONFIG__LEVEL'] : null
        ];

        if (isset($_POST['config_object-type']))
        {
            $l_config['object-type'] = $_POST['C__ITSERVICE__CONFIG__OBJECT_TYPE__selected_box'];
        } // if

        if (isset($_POST['config_relation-type']))
        {
            $l_config['relation-type'] = $_POST['C__ITSERVICE__CONFIG__RELATIONTYPE__selected_box'];
        } // if

        if (isset($_POST['config_cmdb-status']))
        {
            $l_config['cmdb-status'] = $_POST['C__ITSERVICE__CONFIG__CMDB_STATUS__selected_box'];
        } // if

        $l_data = [
            'isys_itservice_filter_config__title' => $_POST['C__ITSERVICE__CONFIG__TITLE'],
            'isys_itservice_filter_config__data'  => isys_format_json::encode($l_config)
        ];

        $l_id = $this->m_dao->save_data($p_id, $l_data);

        $this->filter_config__edit($l_id);
    } // function

    /**
     * This method will delete the selected configurations or display a "notify" warning, if none were selected.
     */
    private function filter_config__delete()
    {
        $l_ids = $_POST['id'];

        if (is_array($l_ids) && count($l_ids) > 0)
        {
            $this->m_dao->delete_data($l_ids);
        }
        else
        {
            isys_notify::warning(_L('LC__ITSERVICE__CONFIG__WARNING__NO_CONFIGURATION_SELECTED'));
        } // if

        $this->filter_config__list();
    } // function

    /**
     * This method will display the it-service CMDB objects and filter them by the given GET parameter "id".
     */
    private function process_type_list()
    {
        global $index_includes;

        $l_request = isys_module_request::get_instance();

        $l_request_get = $l_request->get_gets();

        // This is necessary for "isys_cmdb_view_list_object" internal logic.
        if (!isset($l_request_get[C__CMDB__GET__OBJECTTYPE]))
        {
            $l_request_get[C__CMDB__GET__OBJECTTYPE] = C__OBJTYPE__IT_SERVICE;
            $l_request->_internal_set_private('m_get', $l_request_get);
        } // if

        $this->m_tpl->assign(
            'list',
            isys_factory::get_instance('isys_cmdb_view_list_object', $l_request)
                ->list_process()
        );

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'list.tpl';
    } // function

    /**
     * Process the "type config" page.
     */
    private function process_type_config()
    {
        $l_auth    = isys_auth_itservice::instance();
        $l_navmode = $_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE];
        $l_id      = $_POST[C__GET__ID][0] ?: $_GET[C__GET__ID];

        if (!$l_id && isset($_POST['itservice-id']))
        {
            $l_id = $_POST['itservice-id'];
        } // if

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        switch ($l_navmode)
        {
            case C__NAVMODE__DELETE:
                $l_auth->check(isys_auth::DELETE, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->type_config__delete();
                break;

            case C__NAVMODE__SAVE:
                $l_auth->check(isys_auth::EDIT, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->type_config__save($l_id);
                break;

            case C__NAVMODE__EDIT:
                $l_auth->check(isys_auth::EDIT, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->type_config__edit($l_id);
                break;

            case C__NAVMODE__NEW:
                $l_auth->check(isys_auth::EDIT, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->type_config__edit();
                break;

            default:
                $l_auth->check(isys_auth::VIEW, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
                $this->type_config__list();
        } // switch
    } // function

    /**
     * This method will display the it-service type configuration list.
     */
    private function type_config__list()
    {
        global $index_includes;

        $l_auth                        = isys_auth_itservice::instance();
        $l_url_params                  = $_GET;
        $l_url_params[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        unset($l_url_params[C__GET__MAIN_MENU__NAVIGATION_ID], $l_url_params[C__GET__ID]);

        $l_list_headers = [
            'isys_its_type__id'          => 'ID',
            'isys_its_type__title'       => 'LC__ITSERVICE__TYPE_CONFIGURATION__TITLE',
            'isys_its_type__description' => 'LC__ITSERVICE__TYPE_CONFIGURATION__DESCRIPTION'
        ];

        $l_filter_result = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
            ->get_data_raw();
        $l_filter_count  = count($l_filter_result);

        $l_list = new isys_component_list(null, $l_filter_result);

        $l_list->config($l_list_headers, isys_helper_link::create_url($l_url_params) . '&' . C__GET__ID . '=[{isys_its_type__id}]', '[{isys_its_type__id}]');

        if ($l_list->createTempTable())
        {
            $this->m_tpl->assign('list', $l_list->getTempTableHtml());
        } // if

        $l_write  = $l_auth->is_allowed_to(isys_auth::EDIT, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);
        $l_delete = $l_auth->is_allowed_to(isys_auth::DELETE, 'TYPE_CONFIG/' . isys_auth::EMPTY_ID_PARAM);

        isys_component_template_navbar::getInstance()
            ->set_active($l_write, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_filter_count > 0 && $l_write), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_filter_count > 0 && $l_delete), C__NAVBAR_BUTTON__DELETE)
            ->set_visible($l_write, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_write, C__NAVBAR_BUTTON__EDIT)
            ->set_visible($l_delete, C__NAVBAR_BUTTON__DELETE);

        $this->m_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'list.tpl';
    } // function

    /**
     * This method will be used to display the "new" and "edit" mode of the it-service type configuration.
     *
     * @param  integer $p_id
     */
    private function type_config__edit($p_id = null)
    {
        global $index_includes;

        $l_rules = $l_config = [];

        if ($p_id > 0)
        {
            $l_config = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
                ->get_data($p_id);

            $l_rules['itservice-id']['p_strValue']                                  = $p_id;
            $l_rules['C__ITSERVICE__TYPE_CONFIGURATION__TITLE']['p_strValue']       = $l_config['isys_its_type__title'];
            $l_rules['C__ITSERVICE__TYPE_CONFIGURATION__DESCRIPTION']['p_strValue'] = $l_config['isys_its_type__description'];
        } // if

        $this->m_tpl->activate_editmode()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'type-config.tpl';
    } // function

    /**
     * This method will save the given data and internally call the edit method, with the newly created (or given) ID.
     *
     * @param   integer $p_id
     */
    private function type_config__save($p_id)
    {
        $l_data = [
            'isys_its_type__title = ' . $this->m_dao->convert_sql_text($_POST['C__ITSERVICE__TYPE_CONFIGURATION__TITLE']),
            'isys_its_type__description = ' . $this->m_dao->convert_sql_text($_POST['C__ITSERVICE__TYPE_CONFIGURATION__DESCRIPTION'])
        ];

        $l_sql = 'INSERT INTO isys_its_type SET %s;';

        if ($p_id > 0)
        {
            $l_sql = 'UPDATE isys_its_type SET %s WHERE isys_its_type__id = ' . $this->m_dao->convert_sql_id($p_id) . ';';
        } // if

        $l_sql = sprintf($l_sql, implode(', ', $l_data));

        if ($this->m_dao->update($l_sql) && $this->m_dao->apply_update())
        {
            isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));

            isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_its_type')
                ->reset();
            $this->type_config__edit($p_id ?: $this->m_dao->get_last_insert_id());
        }
        else
        {
            isys_notify::error(_L('LC__INFOBOX__DATA_WAS_NOT_SAVED: ' . $this->m_db->get_last_error_as_string()), ['sticky' => true]);
        } // if
    } //function

    /**
     * This method will delete the selected type configurations or display a "notify" warning, if none were selected.
     */
    private function type_config__delete()
    {
        $l_ids = $_POST['id'];

        if (is_array($l_ids) && count($l_ids) > 0)
        {
            $this->m_dao->update('DELETE FROM isys_its_type WHERE isys_its_type__id IN (' . implode(', ', array_map('intval', $l_ids)) . ');');
        }
        else
        {
            isys_notify::warning(_L('LC__ITSERVICE__CONFIG__WARNING__NO_CONFIGURATION_SELECTED'));
        } // if

        $this->type_config__list();
    } // function
} // class