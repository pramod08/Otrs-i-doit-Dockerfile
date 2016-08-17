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
 * Check_MK module class.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_module_check_mk extends isys_module implements isys_module_interface, isys_module_authable
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = true;
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * Variable which holds the check_mk DAO class.
     *
     * @var  isys_check_mk_dao
     */
    protected $m_dao = null;
    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tpl_dir()
    {
        return __DIR__ . DS . 'templates' . DS;
    } // function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_check_mk::instance();
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_check_mk
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_db  = $p_req->get_database();
        $this->m_tpl = $p_req->get_template();
        $this->m_dao = new isys_check_mk_dao($this->m_db);

        return $this;
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

        $l_auth = isys_auth_check_mk::instance();

        if ($p_system_module)
        {
            $l_monitoring_parent = $p_tree->find_id_by_title(_L('LC__MONITORING'));

            if ($l_monitoring_parent > 0)
            {
                $p_parent = $l_monitoring_parent;
            } // if

            $p_tree->add_node(
                C__MODULE__CHECK_MK . 1,
                $p_parent,
                _L('LC__MODULE__CHECK_MK__EXPORT'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__CHECK_MK,
                        C__GET__SETTINGS_PAGE => 'export',
                        C__GET__TREE_NODE     => C__MODULE__CHECK_MK . 1
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/check_mk.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__CHECK_MK . 1),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::EXECUTE, 'EXPORT')
            );
        }
        else
        {
            $l_root = $p_tree->add_node(
                C__MODULE__CHECK_MK . 2,
                $p_parent,
                _L('LC__MODULE__CHECK_MK')
            );

            $p_tree->add_node(
                C__MODULE__CHECK_MK . 3,
                $l_root,
                _L('LC__CATG__CMK_TAG_DYNAMIC'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__CHECK_MK,
                        C__GET__SETTINGS_PAGE => 'tags',
                        C__GET__TREE_NODE     => C__MODULE__CHECK_MK . 3
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/tag_blue_edit.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__CHECK_MK . 3),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::EXECUTE, 'TAGS')
            );

            //$p_tree->add_node(
            //	C__MODULE__CHECK_MK . 4,
            //	$l_root,
            //	_L('Host overview'),
            //	isys_helper_link::create_url(array(
            //		C__GET__MODULE_ID => C__MODULE__CHECK_MK,
            //		C__GET__SETTINGS_PAGE => 'host_overview',
            //		C__GET__TREE_NODE => C__MODULE__CHECK_MK . 4
            //	)),
            //	'',
            //	$g_dirs['images'] . 'icons/silk/server_chart.png',
            //	(int) ($_GET[C__GET__TREE_NODE] == C__MODULE__CHECK_MK . 4),
            //	'',
            //	'',
            //	true /*$l_auth->is_allowed_to(isys_auth::EXECUTE, 'HOST_OVERVIEW')*/);

            $p_tree->add_node(
                C__MODULE__CHECK_MK . 5,
                $l_root,
                _L('LC__MODULE__CHECK_MK__TAGS__STATIC_TAG_CONFIG'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__CHECK_MK,
                        C__GET__SETTINGS_PAGE => 'tag_config',
                        C__GET__TREE_NODE     => C__MODULE__CHECK_MK . 5
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/silk/tag_blue_edit.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__CHECK_MK . 5),
                '',
                '',
                isys_auth_dialog_admin::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'TABLE/ISYS_CHECK_MK_TAGS')
            );

            $p_tree->add_node(
                C__MODULE__CHECK_MK . 1,
                $l_root,
                _L('LC__MODULE__CHECK_MK__EXPORT'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__CHECK_MK,
                        C__GET__SETTINGS_PAGE => 'export',
                        C__GET__TREE_NODE     => C__MODULE__CHECK_MK . 1
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/check_mk.png',
                0,
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::EXECUTE, 'EXPORT')
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

        return $g_config['www_dir'] . 'src/classes/modules/check_mk/templates/';
    } // function

    /**
     * Start method.
     */
    public function start()
    {
        // Build the module tree, but only if we are not in the system-module.
        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();
            $this->build_tree($l_tree, false, -1);

            $this->m_tpl->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        $this->m_tpl->assign('tpl_www_dir', self::get_tpl_www_dir());

        switch ($_GET[C__GET__SETTINGS_PAGE])
        {
            default:
                break;

            case 'export':
                $this->process_export();
                break;

            case 'tags':
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                {
                    $this->m_dao->save_dynamic_tags();

                    $l_generic_dao = isys_check_mk_dao_generic_tag::instance($this->m_db);

                    foreach ($_POST as $l_key => $l_value)
                    {
                        if (strpos($l_key, 'generic_tag_properties_') === 0 && strpos($l_key, '__HIDDEN_IDS') !== false)
                        {
                            if (!empty($l_value))
                            {
                                $l_obj_type = substr($l_key, 23, -12);

                                // This will happen for the "default" (or "global") configuration.
                                if ($l_obj_type === false)
                                {
                                    $l_constant = 0;

                                    $l_config = [
                                        'location'   => [
                                            'export'   => $_POST['generic_location'],
                                            'obj_type' => $_POST['generic_location_obj_type']
                                        ],
                                        'properties' => $l_generic_dao->get_formatted_config_by_property_id(isys_format_json::decode($l_value, true))
                                    ];
                                }
                                else
                                {
                                    $l_constant = constant($l_obj_type);

                                    $l_config = [
                                        'location'         => [
                                            'export'   => $_POST['generic_location_' . $l_obj_type],
                                            'obj_type' => $_POST['generic_location_obj_type_' . $l_obj_type]
                                        ],
                                        'overwrite_global' => $_POST['overwrite_global_' . $l_obj_type],
                                        'properties'       => $l_generic_dao->get_formatted_config_by_property_id(isys_format_json::decode($l_value, true))
                                    ];
                                }

                                $l_generic_dao->save_config($l_constant, $l_config);
                            } // if
                        } // if
                    } // foreach
                } // if

                $this->process_tags();

                break;

            case 'tag_config':
                $l_id = 0;

                if (isset($_POST['id']) && is_numeric($_POST['id']))
                {
                    $l_id = $_POST['id'];
                }
                else if (isset($_POST['id']) && is_array($_POST['id']))
                {
                    $l_id = $_POST['id'][0];
                }
                else if (isset($_GET['id']) && is_numeric($_GET['id']))
                {
                    $l_id = $_GET['id'];
                } // if

                $l_navmode = $_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE];

                if (! is_numeric($l_id))
                {
                    $l_id = 0;
                } // if

                switch ($l_navmode)
                {
                    case C__NAVMODE__DELETE:
                        $this->tag_config__delete();
                        break;

                    case C__NAVMODE__SAVE:
                        $this->tag_config__save($l_id);
                        break;

                    case C__NAVMODE__EDIT:
                    case C__NAVMODE__NEW:
                        $this->tag_config__edit($l_id);
                        break;

                    default:
                        $this->tag_config__list();
                } // switch
                break;

            case 'host_overview':
                $this->process_host_overview();
                break;
        } // switch
    } // function

    /**
     * Modify row method for making the tag list a bit more pretty.
     *
     * @param  array &$p_row
     */
    public function tag_config__modify_row(&$p_row)
    {
        global $g_dirs;

        if ($p_row['isys_check_mk_tags__exportable'])
        {
            $p_row['isys_check_mk_tags__exportable'] = '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_green.png" class="vam mr5" /><span class="vam green">' . _L(
                    'LC__UNIVERSAL__YES'
                ) . '</span>';
        }
        else
        {
            $p_row['isys_check_mk_tags__exportable'] = '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" class="vam mr5" /><span class="vam red">' . _L(
                    'LC__UNIVERSAL__NO'
                ) . '</span>';
        } // if
    } // function

    protected function process_export()
    {
        global $index_includes;

        isys_auth_check_mk::instance()
            ->check(isys_auth::EXECUTE, 'EXPORT');

        $l_languages    = isys_glob_get_language_constants();
        $l_languages[0] = _L('LC__MODULE__CHECK_MK__EXPORT_LANGUAGE_ALL_AVAILABLE');

        $l_rules = [
            'C__MODULE__CHECK_MK__EXPORT_LANGUAGE'  => [
                'p_arData'     => $l_languages,
                'p_strClass'   => 'input-mini',
                'p_bDbFieldNN' => true
            ],
            'C__MODULE__CHECK_MK__EXPORT_STRUCTURE' => [
                'p_arData'     => \idoit\Module\Check_mk\Export::getStructureOptions(),
                'p_strClass'   => 'input-small',
                'p_bSort'      => false,
                'p_bDbFieldNN' => true
            ]
        ];

        $l_export_paths = [];

        // Now load the export paths, which are actually beeing used to prepare the directories.
        $l_hosts_res = isys_cmdb_dao_category_g_cmk::instance($this->m_db)
            ->get_used_export_paths();

        while ($l_row = $l_hosts_res->get_row())
        {
            if (!empty($l_row['isys_monitoring_export_config__path']))
            {
                $l_export_paths[] = '"' . rtrim($l_row['isys_monitoring_export_config__path'], DS) . DS . '"';
            } // if
        } // while

        $this->m_tpl->assign('export_warning', _L('LC__MODULE__CHECK_MK__EXPORT_PATH_WARNING', implode(', ', array_unique($l_export_paths))))
            ->assign(
                'ajax_url_export',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX_CALL => 'check_mk',
                        C__GET__AJAX      => 1,
                        'func'            => 'export'
                    ]
                )
            )
            ->assign(
                'ajax_url_shellscript',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX_CALL => 'check_mk',
                        C__GET__AJAX      => 1,
                        'func'            => 'shellscript'
                    ]
                )
            )
            ->activate_editmode()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = self::get_tpl_dir() . 'export.tpl';
    } // function

    /**
     * Method for processing the Check_MK tags page.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_tags()
    {
        global $index_includes;

        $l_auth           = isys_auth_check_mk::instance();
        $l_generic_dao    = isys_check_mk_dao_generic_tag::instance($this->m_db);
        $l_generic_config = $l_generic_dao->get_config();

        $l_auth->check(isys_auth::EXECUTE, 'TAGS');

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_save_mode('formsubmit');

        $l_rules = [];

        // Some global logic for the tag GUI.
        $l_ajax_url = isys_helper_link::create_url(
            [
                C__GET__AJAX_CALL => 'check_mk',
                C__GET__AJAX      => 1
            ]
        );

        // Some logic for the "generic tag configuration" GUI.
        $l_obj_types = [];
        $l_res       = isys_cmdb_dao::instance($this->m_db)
            ->get_obj_type_by_catg(
                [
                    C__CATG__CMK,
                    C__CATG__CMK_TAG
                ]
            );
        $l_yes_no    = serialize(get_smarty_arr_YES_NO());

        $l_location_obj_types     = [];
        $l_location_obj_types_res = isys_cmdb_dao::instance($this->m_db)
            ->get_object_types_by_properties(['isys_obj_type__container' => 1]);

        if (count($l_location_obj_types_res) > 0)
        {
            while ($l_location_obj_types_row = $l_location_obj_types_res->get_row())
            {
                $l_location_obj_types[$l_location_obj_types_row['isys_obj_type__id']] = _L($l_location_obj_types_row['isys_obj_type__title']);
            } // while
        } // if

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_obj_types[$l_row['isys_obj_type__const']] = [
                    'id'           => $l_row['isys_obj_type__id'],
                    'title'        => _L($l_row['isys_obj_type__title']),
                    'preselection' => isys_format_json::encode($l_generic_dao->get_config_for_property_selector($l_row['isys_obj_type__id']))
                ];

                $l_rules['overwrite_global_' . $l_row['isys_obj_type__const']]['p_arData']        = $l_yes_no;
                $l_rules['overwrite_global_' . $l_row['isys_obj_type__const']]['p_strSelectedID'] = isset($l_generic_config[$l_row['isys_obj_type__id']]['overwrite_global']) ? $l_generic_config[$l_row['isys_obj_type__id']]['overwrite_global'] : 1;

                $l_rules['generic_location_' . $l_row['isys_obj_type__const']]['p_arData']        = $l_yes_no;
                $l_rules['generic_location_' . $l_row['isys_obj_type__const']]['p_strSelectedID'] = $l_generic_config[$l_row['isys_obj_type__id']]['location']['export'] ?: 0;

                $l_rules['generic_location_obj_type_' . $l_row['isys_obj_type__const']]['p_arData']        = $l_location_obj_types;
                $l_rules['generic_location_obj_type_' . $l_row['isys_obj_type__const']]['p_strSelectedID'] = $l_generic_config[$l_row['isys_obj_type__id']]['location']['obj_type'];
            } // while
        } // if

        // Some logic for the "dynamic tag" GUI.
        $l_rules['generic_location']['p_arData']                 = $l_yes_no;
        $l_rules['generic_location']['p_strSelectedID']          = $l_generic_config[0]['location']['export'] ?: 0;
        $l_rules['generic_location_obj_type']['p_arData']        = $l_location_obj_types;
        $l_rules['generic_location_obj_type']['p_strSelectedID'] = $l_generic_config[0]['location']['obj_type'];
        $l_rules['C__CHECK_MK__CONFIG__TAG_REPORT']['p_arData']  = serialize($this->get_report_data_for_dialog());

        // Creating a dialog for the dynamic tag "conditions".
        $l_dialog = new isys_smarty_plugin_f_dialog();

        // Load the dynamic tags.
        $l_cnt             = 0;
        $l_dynamic_tags    = [];
        $l_dynamic_tag_res = $this->m_dao->get_dynamic_tag_data();

        if (count($l_dynamic_tag_res) > 0)
        {
            while ($l_dynamic_tag_row = $l_dynamic_tag_res->get_row())
            {
                $l_cnt++;

                $l_condition_params = [
                    'name'              => 'dynamic-tag-condition-' . $l_cnt,
                    'p_strClass'        => 'normal condition-select',
                    'p_arData'          => serialize($this->m_dao->get_dynamic_tag_conditions()),
                    'p_strSelectedID'   => $l_dynamic_tag_row['isys_check_mk_dynamic_tags__condition'],
                    'p_bInfoIconSpacer' => 0,
                    'p_bDbFieldNN'      => true
                ];

                $l_tag_params = [
                    'name'              => 'dynamic-tag-taglist-' . $l_cnt,
                    'p_strClass'        => 'normal',
                    'p_arData'          => serialize(
                        isys_cmdb_dao_category_g_cmk_tag::instance($this->m_db)
                            ->get_tags_for_dialog_list(null, isys_format_json::decode($l_dynamic_tag_row['isys_check_mk_dynamic_tags__tags'], true))
                    ),
                    'p_bInfoIconSpacer' => 0
                ];

                $l_param_selection = $l_dynamic_tag_row['isys_check_mk_dynamic_tags__param'];

                if (!is_numeric($l_param_selection) && defined($l_param_selection))
                {
                    $l_param_selection = constant($l_param_selection);
                } // if

                $l_dynamic_tags[] = [
                    'cnt'       => $l_cnt,
                    'condition' => $l_dialog->navigation_edit($this->m_tpl, $l_condition_params),
                    'parameter' => $this->m_dao->get_dynamic_tag_parameters($l_dynamic_tag_row['isys_check_mk_dynamic_tags__condition'], $l_cnt, $l_param_selection),
                    'tags'      => isys_factory::get_instance('isys_smarty_plugin_f_dialog_list')
                        ->navigation_edit($this->m_tpl, $l_tag_params)
                ];
            } // while
        } // if

        $l_condition_params = [
            'name'              => 'dynamic-tag-condition-%s',
            'p_strClass'        => 'normal condition-select',
            'p_arData'          => serialize($this->m_dao->get_dynamic_tag_conditions()),
            'p_bInfoIconSpacer' => 0,
            'p_bDbFieldNN'      => true,
            'p_strSelectedID'   => 1,
        ];

        $l_rules['max_counter']['p_strValue'] = $l_cnt;

        $this->m_tpl->activate_editmode()
            ->assign('tpl_cmdb_tags', self::get_tpl_dir() . 'tags_cmdb_config.tpl')
            ->assign('tpl_dynamic_tags', self::get_tpl_dir() . 'tags_dynamic_config.tpl')
            ->assign('dynamic_tag_condition_dialog', $l_dialog->navigation_edit($this->m_tpl, $l_condition_params))
            ->assign('ajax_url', $l_ajax_url)
            ->assign('dynamic_tags', $l_dynamic_tags)
            ->assign('check_mk_obj_types', $l_obj_types)
            ->assign('preselection', isys_format_json::encode($l_generic_dao->get_config_for_property_selector(0)))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = self::get_tpl_dir() . 'tag_gui.tpl';
    } // function

    /**
     * Method for processing the Check_MK host overview page.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_host_overview()
    {
        global $index_includes;

        // isys_auth_templates::instance()->check(isys_auth::EXECUTE, 'HOST_OVERVIEW');

        // Here we load all hosts, which have a Check_MK configuration.
        $l_hosts = isys_cmdb_dao_category_g_cmk::instance($this->m_db)
            ->get_data(null, null);

        $l_cmdb_status = isys_cmdb_dao_status::instance($this->m_db)
            ->get_cmdb_status_as_array();

        $l_ajax_url = isys_helper_link::create_url(
            [
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'monitoring_livestatus',
                'func'            => 'load_livestatus'
            ]
        );

        $this->m_tpl->activate_editmode()
            ->assign('tpl_generic_config', self::get_tpl_dir() . 'tags_generic_config.tpl')
            ->assign('tpl_dynamic_tags', self::get_tpl_dir() . 'tags_dynamic_tags.tpl')
            ->assign('hosts', $l_hosts)
            ->assign('ajax_url', $l_ajax_url)
            ->assign('cmdb_status', $l_cmdb_status)
            ->assign('states', isys_format_json::encode(isys_check_mk_helper::get_state_info()))
            ->assign('host_states', isys_format_json::encode(isys_check_mk_helper::get_host_state_info()))
            ->smarty_tom_add_rules('tom.content.bottom.content', []);

        $index_includes['contentbottomcontent'] = self::get_tpl_dir() . 'host_overview.tpl';
    } // function

    /**
     * This method will display the list of tags.
     */
    private function tag_config__list()
    {
        global $index_includes;

        $l_url_params                  = $_GET;
        $l_url_params[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        unset($l_url_params[C__GET__MAIN_MENU__NAVIGATION_ID], $l_url_params['id']);

        $l_list_headers = [
            'isys_check_mk_tags__id'           => 'ID',
            'isys_check_mk_tags__unique_name'  => 'LC__MODULE__CHECK_MK__STATIC_TAGS__UNIQUE_NAME',
            'isys_check_mk_tags__display_name' => 'LC__MODULE__CHECK_MK__STATIC_TAGS__DISPLAY_NAME',
            'isys_check_mk_tag_groups__title'  => 'LC__MODULE__CHECK_MK__STATIC_TAGS__TAG_GROUP',
            'isys_check_mk_tags__exportable'   => 'LC__MODULE__CHECK_MK__STATIC_TAGS__EXPORTABLE'
        ];

        $l_tag_result = $this->m_dao->get_configured_tags_raw();
        $l_tag_count  = count($l_tag_result);

        /** @var  isys_component_list $l_list */
        $l_list = new isys_component_list(null, $l_tag_result);

        $l_list->set_row_modifier($this, 'tag_config__modify_row')
            ->config($l_list_headers, isys_helper_link::create_url($l_url_params) . '&id=[{isys_check_mk_tags__id}]', '[{isys_check_mk_tags__id}]');

        if ($l_list->createTempTable())
        {
            $this->m_tpl->assign('list', $l_list->getTempTableHtml());
        } // if

        // @todo Check rights?
        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_tag_count > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_tag_count > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        $this->m_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = __DIR__ . DS . 'templates' . DS . 'list.tpl';
    } // function

    /**
     * This method will be used to display the "new" and "edit" mode of the it-service filter configuration.
     *
     * @param  integer $p_id
     */
    private function tag_config__edit($p_id = null)
    {
        $l_rules = $l_config = [];

        $l_rules['C__CHECK_MK__TAGS__TAG_GROUP']['p_strTable']       = 'isys_check_mk_tag_groups';
        $l_rules['C__CHECK_MK__TAGS__EXPORTABLE']['p_arData']        = get_smarty_arr_YES_NO();
        $l_rules['C__CHECK_MK__TAGS__EXPORTABLE']['p_strSelectedID'] = 1;

        if ($p_id > 0)
        {
            $l_config = $this->m_dao->get_configured_tags($p_id);

            $l_rules['C__CHECK_MK__TAGS__UNIQUE_NAME']['p_strValue']     = $l_config['isys_check_mk_tags__unique_name'];
            $l_rules['C__CHECK_MK__TAGS__DISPLAY_NAME']['p_strValue']    = $l_config['isys_check_mk_tags__display_name'];
            $l_rules['C__CHECK_MK__TAGS__TAG_GROUP']['p_strSelectedID']  = $l_config['isys_check_mk_tags__isys_check_mk_tag_groups__id'];
            $l_rules['C__CHECK_MK__TAGS__EXPORTABLE']['p_strSelectedID'] = $l_config['isys_check_mk_tags__exportable'];
            $l_rules['C__CHECK_MK__TAGS__DESCRIPTION']['p_strValue']     = $l_config['isys_check_mk_tags__description'];
        } // if

        $this->m_tpl->activate_editmode()
            ->assign('id', $p_id)
            ->assign('filter_config', $l_config)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', self::get_tpl_dir() . 'tag_config_form.tpl');

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
    } // function

    /**
     * This method will save the given data and internally call the edit method, with the newly created (or given) ID.
     *
     * @param  integer $p_id
     */
    private function tag_config__save($p_id)
    {
        $l_data = [
            'isys_check_mk_tags__unique_name'                  => $_POST['C__CHECK_MK__TAGS__UNIQUE_NAME'],
            'isys_check_mk_tags__display_name'                 => $_POST['C__CHECK_MK__TAGS__DISPLAY_NAME'],
            'isys_check_mk_tags__isys_check_mk_tag_groups__id' => (int) $_POST['C__CHECK_MK__TAGS__TAG_GROUP'],
            'isys_check_mk_tags__exportable'                   => (int) $_POST['C__CHECK_MK__TAGS__EXPORTABLE'],
            'isys_check_mk_tags__description'                  => $_POST['C__CHECK_MK__TAGS__DESCRIPTION'],
            'isys_check_mk_tags__status'                       => C__RECORD_STATUS__NORMAL,
        ];

        $l_id = $this->m_dao->save_data($p_id, $l_data);

        $this->tag_config__edit($l_id);
    } // function

    /**
     * This method will delete the selected configurations or display a "notify" warning, if none were selected.
     */
    private function tag_config__delete()
    {
        $l_ids = $_POST['id'];

        if (is_array($l_ids) && count($l_ids))
        {
            $this->m_dao->delete_data($l_ids);
        }
        else
        {
            isys_notify::warning(_L('LC__MODULE__CHECK_MK__NO_CONFIGURATION_SELECTED'));
        } // if

        $this->tag_config__list();
    } // function

    /**
     * Retrieve the "reports" as array structure for a dialog field.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function get_report_data_for_dialog()
    {
        global $g_comp_database_system;

        $l_return     = [];
        $l_report_dao = new isys_report_dao($g_comp_database_system);

        $l_res = $l_report_dao->get_reports();

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_type = ($l_row['isys_report__type'] == 'c') ? _L('LC__REPORT__BROWSER__USER_REPORTS') : _L('LC__REPORT__BROWSER__STANDARD_REPORTS');

                $l_return[$l_type][$l_row['isys_report__id']] = $l_row['isys_report__title'];
            } // while

            foreach ($l_return as &$l_data)
            {
                asort($l_data);
            } // foreach
        } // if

        return $l_return;
    } // function
} // class