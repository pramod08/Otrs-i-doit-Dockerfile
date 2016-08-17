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
 * QR-Code module class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_module_qrcode extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * Variable which holds the dashboard DAO class.
     *
     * @var  isys_cmdb_dao
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
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_qrcode
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_db  = $p_req->get_database();
        $this->m_tpl = $p_req->get_template();
        $this->m_dao = isys_cmdb_dao::instance($this->m_db);

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

        if ($p_system_module)
        {
            $p_tree->add_node(
                C__MODULE__QRCODE . 0,
                $p_parent,
                _L('LC__MODULE__QRCODE'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__QRCODE,
                        C__GET__TREE_NODE     => C__MODULE__QRCODE . 0
                    ]
                ),
                '',
                $g_dirs['images'] . 'icons/tree/qr_code.png',
                (int) ($_GET[C__GET__TREE_NODE] == C__MODULE__QRCODE . 0),
                '',
                '',
                isys_auth_system::instance()
                    ->has('qr_config')
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

        return $g_config['www_dir'] . 'src/classes/modules/qrcode/templates/';
    } // function

    /**
     * Start method.
     */
    public function start()
    {
        if (!isys_auth_system::instance()
            ->has('qr_config')
        )
        {
            throw new isys_exception_auth(_L('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_SYSTEM'));
        } // if

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
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                {
                    try
                    {
                        $l_auth = isys_auth_system::instance();

                        if (!$l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/global') && $l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/objtype'))
                        {
                            throw new isys_exception_auth(_L('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_SYSTEM'));
                        } // if

                        $l_config = [
                            'global'   => [
                                'type'        => $_POST['C__MODULE__QRCODE__GLOBAL_TYPE'] ?: C__QRCODE__TYPE__ACCESS_URL,
                                'link'        => $_POST['C__MODULE__QRCODE__GLOBAL_LINK_TYPE'] ?: C__QRCODE__LINK__PRINT,
                                'description' => $_POST['C__MODULE__QRCODE__CONFIGURATION__GLOBAL_WYSIWYG'] ?: '',
                                'logo'        => $_POST['C__MODULE__QRCODE__LOGO_OBJ__HIDDEN'],
                                'url'         => ($_POST['C__MODULE__QRCODE__GLOBAL_TYPE'] == C__QRCODE__TYPE__SELFDEFINED ? trim($_POST['C__MODULE__QRCODE__GLOBAL_URL']) : '')
                            ],
                            'obj-type' => []
                        ];

                        if (!empty($l_config['global']['description']))
                        {
                            $l_additional_tags = [
                                'img',
                                'caption',
                                'table',
                                'thead',
                                'th',
                                'tbody',
                                'tr',
                                'td',
                                'a',
                                's'
                            ];

                            isys_smarty_plugin_f_wysiwyg::add_tags_to_whitelist($l_additional_tags);

                            // Necessary action to throw out all tags and attributes, that are not allowed.
                            $l_config['global']['description'] = isys_helper_textformat::strip_scripts_tags(
                                strip_tags($l_config['global']['description'], '<' . implode('><', isys_smarty_plugin_f_wysiwyg::get_tags_whitelist()) . '>'),
                                isys_smarty_plugin_f_wysiwyg::get_tags_whitelist()
                            );
                        } // if

                        foreach ($_POST as $l_key => $l_value)
                        {
                            if (strpos($l_key, 'C__MODULE__QRCODE__WYSIWYG__') === 0)
                            {
                                $l_obj_type = substr($l_key, 28);

                                $l_config['obj-type'][$l_obj_type] = [
                                    'type'        => $_POST['C__MODULE__QRCODE__' . $l_obj_type . '_TYPE'] ?: C__QRCODE__TYPE__ACCESS_URL,
                                    'description' => $_POST['C__MODULE__QRCODE__WYSIWYG__' . $l_obj_type] ?: '',
                                    'url'         => ($_POST['C__MODULE__QRCODE__' . $l_obj_type . '_TYPE'] == C__QRCODE__TYPE__SELFDEFINED ? trim(
                                        $_POST['C__MODULE__QRCODE__' . $l_obj_type . '_URL']
                                    ) : ''),
                                    'enabled'     => ($_POST['C__MODULE__QRCODE__ENABLE__' . $l_obj_type] == 1)
                                ];

                                if (!empty($l_config['obj-type'][$l_obj_type]['description']))
                                {
                                    // Necessary action to throw out all tags and attributes, that are not allowed.
                                    $l_config['obj-type'][$l_obj_type]['description'] = isys_helper_textformat::strip_html_attributes(
                                        strip_tags(
                                            $l_config['obj-type'][$l_obj_type]['description'],
                                            '<' . implode('><', isys_smarty_plugin_f_wysiwyg::get_tags_whitelist()) . '>'
                                        )
                                    );
                                } // if
                            } // if
                        } // foreach

                        isys_tenantsettings::set('qrcode.config', $l_config);
                        isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                    }
                    catch (isys_exception_general $e)
                    {
                        isys_notify::error($e->getMessage(), ['sticky' => true]);
                    } // try
                } // if

                $this->process_config();
                break;
        } // switch
    } // function

    /**
     * Method for loading a QR code - That is: it's link, description and logo.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_qr_code($p_obj_id)
    {
        $l_object = $this->m_dao->get_object_by_id($p_obj_id)->get_row();
        $l_config = isys_tenantsettings::get(
            'qrcode.config',
            [
                'obj-type' => [],
                'global'   => []
            ]
        );

        // If we find a object-type specific configuration, we use it.
        if (isset($l_config['obj-type'][$l_object['isys_obj_type__const']]))
        {
            // @see ID-1424  If the user deactivated the QR-Code for the given object-type, we simply return an empty array.
            if (!$l_config['obj-type'][$l_object['isys_obj_type__const']]['enabled'])
            {
                return [];
            } // if

            $l_return = [
                'link'        => $l_config['global']['link'] ?: C__QRCODE__LINK__PRINT,
                'type'        => $l_config['obj-type'][$l_object['isys_obj_type__const']]['type'] ?: C__QRCODE__TYPE__ACCESS_URL,
                'url'         => $l_config['obj-type'][$l_object['isys_obj_type__const']]['url'] ?: false,
                'description' => $l_config['obj-type'][$l_object['isys_obj_type__const']]['description'] ?: false,
                'logo'        => false,
                'sysid'       => $l_object['isys_obj__sysid']
            ];
        }
        else
        {
            $l_return = [
                'link'        => $l_config['global']['link'] ?: C__QRCODE__LINK__PRINT,
                'type'        => $l_config['global']['type'] ?: C__QRCODE__TYPE__ACCESS_URL,
                'url'         => $l_config['global']['url'] ?: false,
                'description' => $l_config['global']['description'] ?: false,
                'logo'        => false,
                'sysid'       => $l_object['isys_obj__sysid']
            ];
        } // if

        if ($l_return['type'] == C__QRCODE__TYPE__ACCESS_URL)
        {
            $l_return['url'] = isys_cmdb_dao_category_g_access::instance($this->m_db)
                ->get_url($p_obj_id);
        } // if

        if ($l_config['global']['logo'] > 0)
        {
            $l_return['logo'] = isys_helper_link::create_image_url($l_config['global']['logo']);
        } // if

        // This will be our "default" QR Code url.
        if (empty($l_return['url']))
        {
            $l_return['url'] = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_obj_id], true);
        } // if

        // Finally handle the URL variables (also for the description field).
        $l_return['url']         = urlencode(isys_helper_link::handle_url_variables($l_return['url'], $p_obj_id));
        $l_return['description'] = isys_helper_link::handle_url_variables($l_return['description'], $p_obj_id);

        if ($l_return['link'] == C__QRCODE__LINK__IQR)
        {
            $l_return['iqr'] = 'iqr://' . $p_obj_id;
        } // if

        return $l_return;
    } // function

    /**
     * Method for processing the QR Code configuration page.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_config()
    {
        global $index_includes;

        $l_config = isys_tenantsettings::get('qrcode.config');
        $l_auth   = isys_auth_system::instance();

        if (!is_array($l_config))
        {
            $l_config = [
                'global'   => [],
                'obj-type' => []
            ];
        } // if

        $l_navbar          = isys_component_template_navbar::getInstance();
        $l_url_description = '';

        if (isys_glob_is_edit_mode())
        {
            if ($l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/global') || $l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/objtype'))
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
            } // if

            $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);

            // For demonstration purpose we load the first server, switch or client object-ID.
            $l_sql = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__isys_obj_type__id IN (' . implode(
                    ',',
                    [
                        C__OBJTYPE__SERVER,
                        C__OBJTYPE__SWITCH,
                        C__OBJTYPE__CLIENT
                    ]
                ) . ') LIMIT 1;';

            $l_object_id = (int) $this->m_dao->retrieve($l_sql)->get_row_value('isys_obj__id');

            if (!$l_object_id)
            {
                $l_object_id = (int) $this->m_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__isys_obj_type__id != ' . $this->m_dao->convert_sql_id(C__OBJTYPE__RELATION) . ' ORDER BY RAND() LIMIT 1;')->get_row_value('isys_obj__id');
            } // if

            $l_variables   = isys_helper_link::get_url_variables($l_object_id);
            $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

            foreach ($l_variables as $l_key => &$l_value)
            {
                $l_value = '<code>' . $l_key . '</code> = ' . (empty($l_value) ? $l_empty_value : $l_value);
            } // foreach

            $l_url_description = _L('LC__CMDB__LINK_VARIABLE__DESCRIPTION') . implode(', ', $l_variables);
        }
        else
        {
            if ($l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/global') || $l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/objtype'))
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
            } // if
        } // if

        $l_rules         = $l_object_types = $l_obj_type_config = [];
        $l_obj_type_data = $this->m_dao->get_object_type();

        foreach ($l_obj_type_data as $l_object_type)
        {
            $l_object_types[$l_object_type['isys_obj_type__const']] = $l_object_type['LC_isys_obj_type__title'];

            if (isset($l_config['obj-type'][$l_object_type['isys_obj_type__const']]))
            {
                $l_obj_type_config[$l_object_type['isys_obj_type__const']] = [
                    'type_selection'   => C__QRCODE__TYPE__ACCESS_URL,
                    'type_selfdefined' => '',
                    'type_accessurl'   => '',
                    'obj_type_name'    => _L($l_object_type['isys_obj_type__title']),
                    'url'              => 'C__MODULE__QRCODE__' . $l_object_type['isys_obj_type__const'] . '_URL',
                    'description'      => 'C__MODULE__QRCODE__WYSIWYG__' . $l_object_type['isys_obj_type__const']
                ];

                $l_rules['C__MODULE__QRCODE__' . $l_object_type['isys_obj_type__const'] . '_URL']['p_strValue']     = $l_config['obj-type'][$l_object_type['isys_obj_type__const']]['url'] ?: '%idoit_host%/?objID=%objid%';
                $l_rules['C__MODULE__QRCODE__WYSIWYG__' . $l_object_type['isys_obj_type__const']]['p_strValue']     = $l_config['obj-type'][$l_object_type['isys_obj_type__const']]['description'] ?: '';
                $l_rules['C__MODULE__QRCODE__ENABLE__' . $l_object_type['isys_obj_type__const']]['p_strSelectedID'] = (int) $l_config['obj-type'][$l_object_type['isys_obj_type__const']]['enabled'];

                if (($l_config['obj-type'][$l_object_type['isys_obj_type__const']]['type'] ?: C__QRCODE__TYPE__ACCESS_URL) == C__QRCODE__TYPE__ACCESS_URL)
                {
                    $l_obj_type_config[$l_object_type['isys_obj_type__const']]['type_accessurl'] = ' checked="checked"';
                }
                else
                {
                    $l_obj_type_config[$l_object_type['isys_obj_type__const']]['type_selfdefined'] = ' checked="checked"';
                    $l_obj_type_config[$l_object_type['isys_obj_type__const']]['type_selection']   = C__QRCODE__TYPE__SELFDEFINED;
                } // if
            } // if
        } // foreach

        $l_rules['C__MODULE__QRCODE__OBJ_TYPES']['p_arData']                       = $l_object_types;
        $l_rules['C__MODULE__QRCODE__GLOBAL_URL']['p_strValue']                    = $l_config['global']['url'] ?: '%idoit_host%/?objID=%objid%';
        $l_rules['C__MODULE__QRCODE__CONFIGURATION__GLOBAL_WYSIWYG']['p_strValue'] = $l_config['global']['description'] ?: '';
        $l_rules['C__MODULE__QRCODE__LOGO_OBJ']['p_strSelectedID']                 = $l_config['global']['logo'] ?: 0;

        $this->m_tpl->assign('variable_description', $l_url_description)
            ->assign('global_type', $l_config['global']['type'] ?: C__QRCODE__TYPE__ACCESS_URL)
            ->assign('link_type', $l_config['global']['link'] ?: C__QRCODE__LINK__PRINT)
            ->assign('obj_type_config', $l_obj_type_config)
            ->assign('auth_edit_global', $l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/global'))
            ->assign('auth_edit_objtype', $l_auth->is_allowed_to(isys_auth::EDIT, 'qr_config/objtype'))
            ->assign('auth_delete_objtype', $l_auth->is_allowed_to(isys_auth::DELETE, 'qr_config/objtype'))
            ->assign('smarty_yes_no', get_smarty_arr_YES_NO())
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = self::get_tpl_dir() . 'config.tpl';
    } // function
} // class