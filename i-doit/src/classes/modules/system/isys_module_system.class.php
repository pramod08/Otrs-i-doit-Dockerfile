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
 * System settings
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_system extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines whether this module will be displayed in the extras-menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    // Defines, if this module shall be displayed in the systme-menu.
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Stores "additional options"...
     *
     * @var  array
     */
    private $m_additional_options = [];

    /**
     * The current module request instance.
     *
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Installs a licences, if no mandator id is given, the id is retrieved from isys_application::instance()->session.
     *
     * @param   Integer $p_mandator_id
     *
     * @throws isys_exception_licence
     * @throws Exception|isys_exception_licence
     */
    public static function handle_licence_installation($p_mandator_id = null)
    {
        global $index_includes;

        if (!class_exists('isys_module_licence')) return;

        // Check only in i-doit not in admin-center
        if (defined('C__MODULE__SYSTEM') && class_exists('isys_auth_system_licence'))
        {
            isys_auth_system_licence::instance()
                ->installation(isys_auth::EXECUTE);
        } // if

        $l_mandator_id = $p_mandator_id;

        if (is_null($p_mandator_id))
        {
            if (is_object(isys_application::instance()->session))
            {
                $l_mandator_id = isys_application::instance()->session->get_mandator_id();
            } // if
        } // if

        if (is_object(isys_application::instance()->database))
        {
            $tenant_database = isys_application::instance()->database->get_db_name();
        }
        else
        {
            $tenant_database = '';
        }

        isys_application::instance()->template->assign('tenant_database', $tenant_database)
            ->assign("save_buttons", "off")
            ->assign("encType", "multipart/form-data");

        try
        {
            if (is_array($_POST) && count($_POST) > 0)
            {
                if (!empty($_FILES["licence_file"]["tmp_name"]))
                {
                    if (class_exists('isys_module_licence'))
                    {
                        // Validate uploaded licence.
                        $l_licence = new isys_module_licence();

                        // We define an option for the checking-process (Don't check the start-time, so that customers can update before their old licence expires).
                        $l_licence_check_options = ['check_start_date' => false];

                        // We try to catch a certain exception.
                        try
                        {
                            $l_lic_parse = $l_licence->check_licence_file(
                                $_FILES["licence_file"]["tmp_name"],
                                ($l_mandator_id > 0 ? isys_application::instance()->database : null)
                            );
                        }
                        catch (isys_exception_licence $e)
                        {
                            // If the licence starting-date is in the future, we try to still install it correctly and give the user feedback.
                            if ($e->get_errorcode() == LICENCE_ERROR_REG_DATE)
                            {
                                // We switch the check for the licence start-time off (third parameter array).
                                $l_lic_parse = $l_licence->check_licence_file(
                                    $_FILES["licence_file"]["tmp_name"],
                                    ($l_mandator_id > 0 ? isys_application::instance()->database : null),
                                    $l_licence_check_options
                                );
                            }
                            else
                            {
                                // If we get any other exception, we still throw it.
                                throw $e;
                            } // if
                        } // try

                        if (isset($_POST["licence_type"]) && $l_lic_parse[C__LICENCE__TYPE] != $_POST["licence_type"])
                        {
                            throw new isys_exception_licence("Wrong licence type selected", LICENCE_ERROR_TYPE);
                        } // if

                        if (is_array($l_lic_parse))
                        {
                            isys_application::instance()->template->assign("licence_info", $l_lic_parse);

                            // Delete old licence, if there is one available.
                            if ($l_mandator_id > 0)
                            {
                                $l_deleted_licences = $l_licence->delete_licence_by_mandator(isys_application::instance()->database_system, $l_mandator_id);
                            } // if
                            else $l_deleted_licences = 0;

                            // Finally install the licence.
                            if ($l_licence->install(isys_application::instance()->database_system, $l_lic_parse, $l_mandator_id))
                            {
                                if ($l_deleted_licences > 0)
                                {
                                    // Upgrade successfull.
                                    if (time() < $l_lic_parse[4])
                                    {
                                        // But the licence was upgraded to early - So we tell the user.
                                        $l_message = sprintf(_L('LC__LICENCE__UPGRADE__SUCCESSFULL__TO_EARLY'), date('d.m.Y', $l_lic_parse[4]));
                                    }
                                    else
                                    {
                                        $l_message = _L('LC__LICENCE__UPGRADE__SUCCESSFULL');
                                        unset($_SESSION["licenced"]);
                                    } // if
                                }
                                else
                                {
                                    // Installation successfull.
                                    if (time() < $l_lic_parse[4])
                                    {
                                        // But the licence was installed to early - So we tell the user.
                                        $l_message = sprintf(_L('LC__LICENCE__INSTALL__SUCCESSFULL__TO_EARLY'), date('d.m.Y', $l_lic_parse[4]));
                                    }
                                    else
                                    {
                                        isys_module_licence::session_licenced(true);

                                        $l_message = _L('LC__LICENCE__INSTALL__SUCCESSFULL');
                                    } // if
                                } // if
                                // Display the message to the user.
                                isys_application::instance()->template->assign("note", $l_message);
                            }
                            else
                            {
                                // Failed to install licence.
                                isys_application::instance()->template->assign("error", _L('LC__LICENCE__INSTALL__FAIL'));
                            } // if
                        }
                    } // if
                }
                else
                {
                    // No licence file uploaded.
                    isys_application::instance()->template->assign("error", _L('LC__LICENCE__NO_UPLOAD'));
                } // if
            } // if
        }
        catch (isys_exception_licence $e)
        {
            isys_application::instance()->template->assign("error", $e->getMessage())
                ->assign("errorcode", $e->get_errorcode());
        } // try

        $index_includes['contentbottomcontent'] = "modules/system/licence_installation.tpl";
    } // function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_system::instance();
    } // function

    /**
     * Method for generating a string.
     *
     * @param   string $p_what
     * @param   string $p_treenode
     *
     * @return  string
     */
    private static function generate_link($p_what = null, $p_treenode = null)
    {
        return isys_helper_link::create_url(
            [
                C__GET__MODULE_ID => C__MODULE__SYSTEM,
                'what'            => $p_what,
                C__GET__TREE_NODE => $p_treenode
            ]
        );
    } // function

    /**
     * Initializes the CMDB module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  Boolean
     */
    public function init(isys_module_request $p_req)
    {
        global $g_dirs, $g_config;

        $this->m_additional_options = [
            'updates'              => [
                'text' => 'i-doit Updates',
                'icon' => $g_dirs['images'] . '/icons/updates.gif',
                'link' => $g_config['www_dir'] . 'updates'
            ],
            'sysoverview'          => [
                'func' => 'handle_sysoverview',
                'text' => _L('LC__CMDB__TREE__SYSTEM__TOOLS__OVERVIEW'),
                'icon' => $g_dirs['images'] . '/icons/silk/cog.png'
            ],
            'lock'                 => [
                'func' => 'handle_lock',
                'text' => _L('LC__LOCKED__OBJECTS'),
                'icon' => null
            ],
            'logbook'              => [
                'text' => _L('LC__UNIVERSAL__TITLE_LOGBOOK'),
                'icon' => null,
                'link' => '?' . C__GET__MODULE_ID . '=' . C__MODULE__LOGBOOK
            ],
            'cache'                => [
                'func' => 'handle_cache',
                'text' => 'Cache / ' . _L('LC__UNIVERSAL__DATABASE'),
                'icon' => $g_dirs['images'] . '/icons/silk/database.png'
            ],
            'manager'              => [
                'text' => _L('LC__MODULE__MANAGER__TITLE'),
                'link' => '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . "&" . C__GET__MODULE_SUB_ID . "=" . C__MODULE__MANAGER
            ],
            'api'                  => [
                'func' => 'handle_api',
                'text' => 'JSON-RPC API',
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png'
            ],
            'system_settings'      => [
                'func' => 'handle_settings',
                'text' => _L('LC__UNIVERSAL__GENERAL_CONFIGURATION'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png'
            ],
            'idoit_update'         => [
                'text' => 'i-doit Update',
                'icon' => $g_dirs['images'] . '/icons/silk/arrow_refresh.png',
                'link' => '?load=update'
            ],
            'relation_types'       => [
                'func' => 'handle_relation_types',
                'text' => _L('LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png',
            ],
            'roles_administration' => [
                'func' => 'handle_roles_administration',
                'text' => _L('LC__MODULE__SYSTEM__ROLES_ADMINISTRATION'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png',
            ],
            'custom_properties'    => [
                'func' => 'handle_custom_properties',
                'text' => _L('LC__UNIVERSAL__CATEGORY_EXTENSION'),
                'icon' => $g_dirs['images'] . '/icons/silk/database.png'
            ],
        ];

        if (is_object($p_req))
        {
            $this->m_userrequest = &$p_req;

            return true;
        } // if

        return false;
    } // function

    /**
     * Method for handling the licence overview.
     */
    public function handle_licence_overview()
    {
        global $index_includes;

        if (!class_exists('isys_module_licence')) return;

        if (class_exists('isys_auth_system_licence'))
        {
            isys_auth_system_licence::instance()
                ->overview(isys_auth::EXECUTE);
        }

        isys_application::instance()->template->assign("save_buttons", "off");

        // Get licence module.
        $l_licence = new isys_module_licence();

        // Check actiosns.
        if (isset($_GET["delete"]) && $_GET["id"] > 0)
        {
            if ($l_licence->delete_licence(isys_application::instance()->database_system, $_GET["id"]))
            {
                isys_application::instance()->template->assign("note", _L("LC__LICENCE__DELETED"));
            } // if
        } // if

        $l_free_objects = 0;

        try
        {
            // Installed licences.
            $l_licences = $l_licence->get_installed_licences(isys_application::instance()->database_system, isys_application::instance()->session->get_mandator_id());
            isys_application::instance()->template->assign("licences", $l_licences);

            if (count($l_licences) > 0)
            {
                foreach ($l_licences as $l_lic)
                {
                    $l_free_objects += $l_lic["objcount"];
                    $l_licence->check_licence($l_lic["licence_data"], isys_application::instance()->database);
                } // foreach

                /*
                 * If interpretation reaches this code, no licence exceeding was found,
                 * i-doit is licenced and everything is fine!
                 */
                $l_exceeding["objects"] = _L("LC__UNIVERSAL__NO");
                if ($l_free_objects == 0)
                {
                    $l_free_objects = "Unlimited";
                }
            }
            else
            {
                // No licence installed.
                $l_exceeding["objects"]       = _L("LC__UNIVERSAL__YES");
                $l_exceeding["objects_class"] = "red bold";
                isys_application::instance()->template->assign("error", _L("LC__LICENCE__NO_LICENCE"));
            } // if
        }
        catch (isys_exception_licence $e)
        {

            // An Exception was thrown. Theres a licence exceeding.
            isys_application::instance()->template->assign("error", $e->getMessage());

            // Change status to unlicenced.
            $l_exceeding["objects"]       = _L("LC__UNIVERSAL__YES");
            $l_exceeding["objects_class"] = "red bold";

            // Object count specific handling.
            switch ($e->get_errorcode())
            {
                case LICENCE_ERROR_OBJECT_COUNT:
                    ;
                    break;
            } // switch
        } // try

        // Statistics.
        $l_mod_stat = new isys_module_statistics();
        $l_mod_stat->init_statistics();
        $l_mod_stat_counts = $l_mod_stat->get_counts();
        $l_mod_stat_stats  = $l_mod_stat->get_stats();

        if (is_numeric($l_free_objects))
        {
            $l_counts                          = $l_mod_stat->get_counts();
            $l_mod_stat_counts["free_objects"] = $l_free_objects - $l_counts["objects"];
            if ($l_mod_stat_counts["free_objects"] < 0)
            {
                $l_mod_stat_counts["free_objects"] = 0;
            } // if
        }
        else
        {
            $l_mod_stat_counts["free_objects"] = $l_free_objects;
        } // if

        isys_application::instance()->template->assign("stats", $l_mod_stat)
            ->assign("stat_counts", $l_mod_stat_counts)
            ->assign("stat_stats", $l_mod_stat_stats)
            ->assign("exceeding", $l_exceeding);

        $index_includes['contentbottomcontent'] = "modules/system/licence_overview.tpl";
    } // function

    public function handle_settings()
    {
        global $index_includes;

        if (class_exists('isys_auth_system'))
        {
            isys_auth_system::instance()
                ->check(isys_auth::VIEW, 'GLOBALSETTINGS/SYSTEMSETTING');
        } // if

        $l_yes_no        = serialize(get_smarty_arr_YES_NO());
        $l_comp_settings = new isys_component_dao_setting(isys_application::instance()->database);
        $l_list          = '';

        $l_registry_keys = [
            [
                'title'        => _L("LC__SYSTEM__REGISTRY__MYTASK"),
                'post'         => 'reg_count_myTask_entries',
                'settings_key' => 'cmdb.registry.mytask_entries',
                'type'         => 'f_text',
                'params'       => [
                    'type' => 'f_text'
                ]
            ],
            [
                'title'        => _L("LC__CMDB__SYSTEM_SETTING__ALWAYS_DISPLAY_FULL_LISTS"),
                'post'         => 'reg_always_display_full_lists',
                'settings_key' => 'cmdb.registry.show_full_lists',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => _L("LC__CMDB__SYSTEM_SETTING__QUICK_SAVE_BUTTON"),
                'post'         => 'reg_quick_save_button',
                'settings_key' => 'cmdb.registry.quicksave',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => 'SYS-ID readonly',
                'post'         => 'reg_sysid_readonly',
                'settings_key' => 'cmdb.registry.sysid_readonly',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => _L('LC__SYSTEM__REGISTRY__DD_OBJECTS'),
                'post'         => 'reg_object_drag_n_drop',
                'settings_key' => 'cmdb.registry.object_dragndrop',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => _L('LC__CMDB__SETTINGS__CMDB__OBJECTTYPE_SORTING'),
                'post'         => 'reg_objtype_sorting',
                'settings_key' => 'cmdb.registry.object_type_sorting',
                'type'         => 'f_dialog',
                'default'      => C__CMDB__VIEW__OBJECTTYPE_SORTING__MANUAL,
                'params'       => [
                    'p_arData'     => serialize(
                        [
                            C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC => _L('LC__CMDB__TREE_VIEW__OBJECTTYPE_SORTING__ALPHABETICALLY'),
                            C__CMDB__VIEW__OBJECTTYPE_SORTING__MANUAL    => _L('LC__CMDB__TREE_VIEW__OBJECTTYPE_SORTING__MANUAL')
                        ]
                    ),
                    'p_bDbFieldNN' => 1,
                    'type'         => 'f_dialog'
                ]
            ],
            [
                'title'        => _L('LC__CMDB__SYSTEM_SETTING__SANITIZE_INPUT_DATA'),
                'post'         => 'data_sanitizing',
                'settings_key' => 'cmdb.registry.sanitize_input_data',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'      => $l_yes_no,
                    'type'          => 'f_dialog',
                    'p_bDbFieldNN'  => 1,
                    'p_strFootnote' => '*'
                ]
            ]
        ];

        $l_edit_right = class_exists('isys_auth_system') ? isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/SYSTEMSETTING') : true;

        $l_navbar = isys_component_template_navbar::getInstance();

        try
        {
            if (isset($_GET['delete_locks']) && isset($_POST['id']))
            {
                if (is_array($_POST['id']) && count($_POST['id']))
                {
                    $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);

                    foreach ($_POST['id'] AS $l_lockID)
                    {
                        $l_dao_lock->delete($l_lockID);
                    }
                }
            }

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            }
            else
            {
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                {
                    // Save Registry-Values.
                    foreach ($l_registry_keys AS $l_data)
                    {
                        if (isset($_POST[$l_data['post']]))
                        {
                            isys_tenantsettings::set($l_data['settings_key'], (int) $_POST[$l_data['post']]);
                        } // if
                    } // foreach

                    $l_comp_settings->set(null, C__MANDATORY_SETTING__CURRENCY, $_POST['C__CATG__OVERVIEW__MONETARY_FORMAT']);
                    $l_comp_settings->set(null, C__MANDATORY_SETTING__IP_HANDLING, (($_POST['C__MANDATOR_SETTINGS__IP_HANDLING'] <= 0) ? 0 : 1));

                    // Lock Handling.
                    isys_tenantsettings::set('cmdb.registry.lock_dataset', $_POST['lock']);
                    isys_tenantsettings::set('cmdb.registry.lock_timeout', $_POST['lock_timeout']);
                    isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));

                    isys_tenantsettings::override(isys_tenantsettings::get());
                }
            } // try
        }
        catch (isys_exception $e)
        {
            isys_notify::error($e->getMessage(), ['sticky' => true]);
        } // try

        foreach ($l_registry_keys AS $l_data)
        {
            $l_rules[$l_data['post']] = $l_data['params'];

            $l_rules[$l_data['post']][(($l_data['type'] == "f_text") ? "p_strValue" : "p_strSelectedID")] = isys_tenantsettings::get($l_data['settings_key'], 0);
        } // foreach

        $l_rules['C__CATG__OVERVIEW__MONETARY_FORMAT']['p_strSelectedID'] = $l_comp_settings->get(null, C__MANDATORY_SETTING__CURRENCY);
        $l_rules['C__MANDATOR_SETTINGS__IP_HANDLING'] = [
            'p_strSelectedID' => $l_comp_settings->get(null, C__MANDATORY_SETTING__IP_HANDLING),
            'p_arData'        => $l_yes_no,
            'p_bDbFieldNN'    => true
        ];

        $this->handle_templates();

        // Lock Vars.
        isys_application::instance()->template->assign("C__LOCK__TIMEOUT", isys_tenantsettings::get('cmdb.registry.lock_timeout', 120))
            ->assign("C__LOCK__DATASETS", isys_tenantsettings::get('cmdb.registry.lock_dataset', 0));

        $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);
        $l_dao_lock->delete_expired_locks();
        $l_locks = $l_dao_lock->get_lock_information(null, null);

        if ($l_locks->num_rows() > 0)
        {
            $l_objList = new isys_component_list(null, $l_locks);

            $l_objList
                ->set_row_modifier($this, "modify_lock_row")
                ->config(
                    [
                        "isys_cats_person_list__title" => _L("LC__CMDB__LOGBOOK__USER"),
                        "isys_user_session__ip"        => _L("LC__CATP__IP__ADDRESS"),
                        "isys_obj_type__title"         => _L("LC__CMDB__OBJTYPE"),
                        "isys_obj__title"              => _L("LC__UNIVERSAL__TITLE"),
                        "isys_lock__datetime"          => _L("LC__LOCKED__AT"),
                        "expires"                      => _L("LC__EXPIRES_IN"),
                        "progress"                     => ""
                    ],
                    null,
                    "[{isys_lock__id}]"
                );

            $l_objList->createTempTable();
            $l_list = $l_objList->getTempTableHtml();
        }

        if (isys_glob_get_param(C__GET__NAVMODE) != C__NAVMODE__EDIT)
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        isys_application::instance()->template->assign("g_list", $l_list)
            ->assign("registry_keys", $l_registry_keys)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/system/settings.tpl";
    } // function

    // Handles GlobalSettings -> System settings

    /**
     * Handle api configuration
     */
    public function handle_api()
    {
        global $index_includes;

        if (class_exists('isys_auth_system'))
        {
            isys_auth_system::instance()
                ->check(isys_auth::VIEW, 'JSONRPCAPI/CONFIG');
            $l_edit_right = isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EDIT, 'JSONRPCAPI');
        }
        else
        {
            $l_edit_right = true;
        } // if

        $l_is_editmode = 0;

        try
        {
            $l_navbar = isys_component_template_navbar::getInstance();

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

                $l_is_editmode = 1;
            }
            else
            {
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                {
                    // Save data.
                    isys_tenantsettings::set('api.status', (int) isset($_POST["C__SYSTEM_SETTINGS__API_STATUS"]));

                    if (isset($_POST['C__SYSTEM_SETTINGS__APIKEY']))
                    {
                        isys_application::instance()->database_system->query(
                            'UPDATE isys_mandator SET isys_mandator__apikey = \'' . $_POST['C__SYSTEM_SETTINGS__APIKEY'] . '\' WHERE isys_mandator__id = \'' .
                            (int) isys_application::instance()->session->get_mandator_id() . '\''
                        );
                    } // if

                    if (isset($_POST['C__SYSTEM_SETTINGS__LOGGING_ENABLED']))
                    {
                        isys_settings::set('logging.system.api', 1);
                    }
                    else
                    {
                        isys_settings::set('logging.system.api', 0);
                    } // if

                    if (isset($_POST['C__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY']))
                    {
                        isys_settings::set('api.authenticated-users-only', 1);
                    }
                    else
                    {
                        isys_settings::set('api.authenticated-users-only', 0);
                    } // if

                    $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                }
                else
                {
                    $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                }
            } // if
        }
        catch (isys_exception $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        } // try

        // Read data.
        $l_apikey_data = isys_application::instance()->database_system->fetch_row_assoc(
            isys_application::instance()->database_system->query(
                'SELECT isys_mandator__apikey FROM isys_mandator WHERE isys_mandator__id = "' . (int) isys_application::instance()->session->get_mandator_id() . '";'
            )
        );

        isys_application::instance()->template->assign('isEditMode', $l_is_editmode)
            ->assign('status', isys_tenantsettings::get('api.status', 0))
            ->assign('force_user_login', isys_settings::get('api.authenticated-users-only', 0))
            ->assign('logging', isys_settings::get('logging.system.api', 0))
            ->assign('apikey', (isset($l_apikey_data['isys_mandator__apikey']) ? $l_apikey_data['isys_mandator__apikey'] : ''));

        $index_includes['contentbottomcontent'] = "modules/system/api.tpl";
    } // function

    /**
     * Method for handling templates.
     *
     */
    public function handle_templates()
    {
        isys_application::instance()->template->assign("save_buttons", "off");

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
        {
            isys_tenantsettings::set('cmdb.template.colors', (int) isset($_POST["colors"]));
            isys_tenantsettings::set('cmdb.template.color_value', '#' . trim($_POST["color_value"], "# \t\n\r\0\x0B"));
            isys_tenantsettings::set('cmdb.template.status', (int) isset($_POST["status"]));
            isys_tenantsettings::set('cmdb.template.show_assignments', (int) isset($_POST["assignments"]));

            isys_application::instance()->template->assign("C__TEMPLATE__STATUS", $_POST["status"])
                ->assign("C__TEMPLATE__SHOW_ASSIGNMENTS", $_POST["assignments"])
                ->assign("C__TEMPLATE__COLORS", $_POST["colors"])
                ->assign("C__TEMPLATE__COLOR", $_POST["color_value"]);
        }
        else
        {
            isys_application::instance()->template->assign("C__TEMPLATE__STATUS", C__TEMPLATE__STATUS)
                ->assign("C__TEMPLATE__SHOW_ASSIGNMENTS", C__TEMPLATE__SHOW_ASSIGNMENTS)
                ->assign("C__TEMPLATE__COLORS", C__TEMPLATE__COLORS)
                ->assign("C__TEMPLATE__COLOR", C__TEMPLATE__COLOR_VALUE);
        } // if

        if ($_GET['handle'] == 'templates')
        {
            isys_application::instance()->template->include_template('contentbottomcontent', 'modules/templates/settings__templates.tpl');
        } // if
    } // function

    /**
     * Method for handling the system overview.
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function handle_sysoverview()
    {
        global $index_includes, $g_absdir, $g_product_info, $g_dirs;

        isys_auth_system::instance()
            ->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW');

        // Find the operating system.
        if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
        {
            isys_application::instance()->template->assign("os", "Microsoft Windows, " . PHP_OS)
                ->assign("os_msg", "UNIX/Linux Recommended");
        }
        else
        {
            isys_application::instance()->template->assign("os_msg", "OK")
                ->assign("os", "UNIX/Linux, " . PHP_OS);
        } // if

        isys_application::instance()->template// PHP Version.
        ->assign("php_version", PHP_VERSION)
            ->assign("php_version_recommended", PHP_VERSION_MINIMUM_RECOMMENDED)// i-doit Version.
            ->assign("idoit_version", $g_product_info);

        if (file_exists($g_absdir . "/updates/classes/isys_update.class.php"))
        {
            include_once($g_absdir . "/updates/classes/isys_update.class.php");

            $l_upd        = new isys_update();
            $l_info       = $l_upd->get_isys_info();
            $l_new_update = $l_update_msg = '';

            try
            {
                try
                {
                    // Switch between pro and open update url
                    $l_update_url = defined('C__IDOIT_UPDATES_PRO') ? C__IDOIT_UPDATES_PRO : C__IDOIT_UPDATES;
                    $l_content    = $l_upd->fetch_file($l_update_url);
                }
                catch (Exception $e)
                {
                    throw new Exception($e->getMessage());
                }

                $l_version = $l_upd->get_new_versions($l_content);

                if (is_array($l_version) && count($l_version) > 0)
                {
                    foreach ($l_version as $l_v)
                    {
                        if ($l_info["revision"] < $l_v["revision"])
                        {
                            $l_new_update = $l_v;
                        } // if
                    } // foreach

                    if (!isset($l_new_update))
                    {
                        $l_update_msg = "You have got the latest version.";
                    } // if
                }
                else
                {
                    $l_update_msg = "Update check failed. Is the i-doit server not connected to the internet?";
                } // if

                isys_application::instance()->template->assign("update_msg", $l_update_msg)
                    ->assign("update", $l_new_update)
                    ->assign("idoit_info", $l_info);
            }
            catch (Exception $e)
            {
                isys_application::instance()->template->assign("update_error_msg", $e->getMessage())
                    ->assign("idoit_info", $l_info);
            } // try
        } // if

        // Directory Rights.
        $l_rights = [
            'source directory'             => [
                'chk'  => is_writable($g_absdir . DS . 'src'),
                'dir'  => $g_absdir . DS . 'src',
                'msg'  => 'WRITEABLE',
                'note' => 'Must be writeable for i-doit updates!'
            ],
            'idoit directory'              => [
                'chk'  => is_writable($g_absdir),
                'dir'  => $g_absdir,
                'msg'  => 'WRITEABLE',
                'note' => 'Must be writeable for i-doit updates!'
            ],
            'temp'                         => [
                'chk' => is_writable($g_absdir . DS . 'temp'),
                'dir' => $g_absdir . DS . 'temp',
                'msg' => 'WRITEABLE'
            ],
            'css'                          => [
                'chk' => is_dir($g_dirs['css_abs']),
                'dir' => $g_dirs['css_abs'],
                'msg' => 'VALID'
            ],
            'javascript'                   => [
                'chk' => is_dir($g_dirs['js_abs']),
                'dir' => $g_dirs['js_abs'],
                'msg' => 'VALID'
            ],
            'file upload'                  => [
                'chk' => is_dir($g_dirs['fileman']['target_dir']),
                'dir' => $g_dirs['fileman']['target_dir'],
                'msg' => 'WRITEABLE'
            ],
            'image upload'                 => [
                'chk' => is_dir($g_dirs['fileman']['image_dir']),
                'dir' => $g_dirs['fileman']['image_dir'],
                'msg' => 'WRITEABLE'
            ],
            'default theme template cache' => [
                'chk' => is_writable($g_absdir . DS . 'src' . DS . 'themes' . DS . 'default' . DS . 'smarty' . DS . 'templates_c'),
                'dir' => $g_absdir . DS . 'src' . DS . 'themes' . DS . 'default' . DS . 'smarty' . DS . 'templates_c',
                'msg' => 'WRITEABLE'
            ],
            'default theme smarty cache'   => [
                'chk' => is_writable($g_absdir . DS . 'src' . DS . 'themes' . DS . 'default' . DS . 'smarty' . DS . 'cache'),
                'dir' => $g_absdir . DS . 'src' . DS . 'themes' . DS . 'default' . DS . 'smarty' . DS . 'cache',
                'msg' => 'WRITEABLE'
            ]
        ];

        $l_user_agent = $this->extract_user_agent($_SERVER['HTTP_USER_AGENT']);

        if (strstr($l_user_agent["os"], "MSIE 6"))
        {
            $l_user_agent["chk"] = false;
            $l_user_agent["msg"] = "IE >7 or Firefox, Chrome or Safari recommended!";
        }
        else
        {
            $l_user_agent["chk"] = true;
        } // if

        // PHP Checks.
        $l_php = [
            "max_execution_time"  => ini_get("max_execution_time"),
            "upload_max_filesize" => ini_get("upload_max_filesize"),
            "allow_url_fopen"     => ini_get("allow_url_fopen"),
            "max_input_vars"      => ini_get("max_input_vars"),
            "post_max_size"       => ini_get("post_max_size"),
            "file_uploads"        => ini_get("file_uploads"),
            "memory_limit"        => ini_get("memory_limit")
        ];

        $l_db    = isys_application::instance()->database;
        $l_mysql = [
            'innodb_log_file_size'    => $l_db->get_config_value('innodb_log_file_size'),
            'tmp_table_size'          => $l_db->get_config_value('tmp_table_size'),
            'innodb_sort_buffer_size' => $l_db->get_config_value('innodb_sort_buffer_size'),
            'max_allowed_packet'      => $l_db->get_config_value('max_allowed_packet'),
            'join_buffer_size'        => $l_db->get_config_value('join_buffer_size'),
            'sort_buffer_size'        => $l_db->get_config_value('sort_buffer_size'),
            'max_heap_table_size'     => $l_db->get_config_value('max_heap_table_size'),
            'query_cache_limit'       => $l_db->get_config_value('query_cache_limit'),
            'query_cache_size'        => $l_db->get_config_value('query_cache_size'),
            'innodb_buffer_pool_size' => $l_db->get_config_value('innodb_buffer_pool_size'),
            'datadir'                 => $l_db->get_config_value('datadir')
        ];

        isys_application::instance()->template->assign("rights", $l_rights)
            ->assign("browser", $l_user_agent)
            ->assign("php", $l_php)
            ->assign("mysql", $l_mysql)
            ->assign("db_size", $this->get_database_size())
            ->assign(
                'php_dependencies',
                isys_module_manager::instance()
                    ->get_module_dependencies()
            )
            ->assign(
                'apache_dependencies',
                isys_module_manager::instance()->get_module_dependencies(null, 'apache')
            );

        $index_includes['contentbottomcontent'] = "content/bottom/content/module__settings__overview.tpl";
    } // function

    /**
     * Retrieves the current database size
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_database_size()
    {
        $l_sql    = 'SHOW TABLE STATUS';
        $l_result = isys_application::instance()->database->query($l_sql);
        $l_bytes  = 0;
        while ($l_row = isys_application::instance()->database->fetch_row_assoc($l_result))
        {
            $l_bytes += $l_row['Data_length'] + $l_row['Index_length'];
        } // while

        $l_kb = 1024;
        $l_gb = (int) pow($l_kb, 3);
        $l_mb = (int) pow($l_kb, 2);

        if ($l_bytes >= $l_gb)
        {
            return round(($l_bytes / $l_gb), 2) . " GB";
        }
        elseif ($l_bytes >= $l_mb)
        {

            return round(($l_bytes / $l_mb), 2) . " MB";
        }
        elseif ($l_bytes >= $l_kb)
        {
            return round(($l_bytes / $l_kb), 2) . " KB";
        }
        else
        {
            return $l_bytes . " Byte";
        } // if
    } // function

    /**
     * Method for extracting the user-agent.
     *
     * @param String $p_agent
     *
     * @return array
     */
    public function extract_user_agent($p_agent)
    {
        // We prepare the regex pattern for finding several parts of the user-agent.
        $l_pattern = "~([^/\s]*)(/([^\s]*))?";
        $l_pattern .= "([\s]*\[[a-zA-Z][a-zA-Z]\])?\s*";
        $l_pattern .= "(\((([^()]|(\([^()]*\)))*)\))?\s*~";

        // We match the pattern against the given agent.
        preg_match($l_pattern, $p_agent, $l_reg);
        $p_agent_2 = substr($p_agent, strlen($l_reg[0]));

        preg_match($l_pattern, $p_agent_2, $l_reg_2);
        $p_agent_3 = substr($p_agent_2, strlen($l_reg_2[0]));

        $l_tmp = explode(" ", $p_agent_3);

        $l_data = [
            "user_agent"       => $p_agent,
            "engine"           => $l_reg_2[1],
            "engine_complete"  => $l_reg_2[0],
            "engine_version"   => $l_reg_2[3],
            "engine_parent"    => $l_reg_2[6],
            "browser"          => $l_reg[1],
            "browser_complete" => $l_reg[1] . $l_reg[2],
            "browser_title"    => $l_tmp[count($l_tmp) - 1],
            "version"          => $l_reg[3],
            "os"               => $l_reg[5],
        ];

        return $l_data;
    } // function

    /**
     * Method for handling the lock.
     */
    public function handle_lock()
    {
        global $g_comp_template_language_manager, $index_includes;

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_list   = null;

        $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);

        isys_application::instance()->template->assign("save_buttons", "off");

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__DELETE)
        {
            foreach ($_POST["id"] as $l_id)
            {
                $l_dao_lock->delete($l_id);
            }
        }

        if (count($_POST) > 0 && $_POST[C__GET__NAVMODE] == "")
        {
            if ($_POST["timeout"] > 0)
            {
                $l_timeout = intval($_POST["timeout"]);
            }
            else
            {
                $l_timeout = 120;
            } // if

            isys_tenantsettings::set('cmdb.registry.lock_dataset', isset($_POST["lock"]) ? 1 : 0);
            isys_tenantsettings::set('cmdb.registry.lock_timeout', $l_timeout);

            isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));

            die();
        } // if

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__DELETE);

        $l_locks = $l_dao_lock->get_lock_information(null, null);

        if ($l_locks->num_rows() > 0)
        {
            $l_objList = new isys_component_list(null, $l_locks);

            $l_objList->set_row_modifier($this, "modify_lock_row");

            $l_objList->config(
                [
                    "isys_cats_person_list__title" => $g_comp_template_language_manager->{"LC__CMDB__LOGBOOK__USER"},
                    "isys_user_session__ip"        => $g_comp_template_language_manager->{"LC__CATP__IP__ADDRESS"},
                    "isys_obj_type__title"         => $g_comp_template_language_manager->{"LC__CMDB__OBJTYPE"},
                    "isys_obj__title"              => $g_comp_template_language_manager->{"LC__UNIVERSAL__TITLE"},
                    "isys_lock__datetime"          => $g_comp_template_language_manager->{"LC__LOCKED__AT"},
                    "expires"                      => $g_comp_template_language_manager->{"LC__EXPIRES_IN"},
                    "progress"                     => ""
                ],
                null,
                "[{isys_lock__id}]"
            );

            $l_objList->createTempTable();
            $l_list = $l_objList->getTempTableHtml();
        }

        $l_dao_lock->delete_expired_locks();

        isys_application::instance()->template->assign("g_list", $l_list);

        $index_includes['contentbottomcontent'] = "content/bottom/content/module__settings__lock.tpl";
    } // function

    public function modify_lock_row(&$p_row)
    {

        $l_exp     = time() - (strtotime($p_row["isys_lock__datetime"]) + 20);
        $l_seconds = (C__LOCK__TIMEOUT - $l_exp);

        $l_progress_max = 18;

        $l_max      = intval($l_seconds * $l_progress_max / 100);
        $l_progress = "";
        if ($l_max >= $l_progress_max) $l_max = $l_progress_max;

        for ($i = $l_max;$i <= $l_progress_max;$i++)
        {
            $l_progress .= "*";
        }

        for ($i = 0;$i < $l_max;$i++)
        {
            $l_progress .= "-";
        }

        if ($l_seconds < 0)
        {
            $p_row["expires"] = "already expired";
        }
        else
        {
            $p_row["expires"] = $l_seconds . "s";
        }

        $p_row["progress"] = "<span style=\"font-family:Courier New,Courier,Lucida Sans Typewriter,Lucida Typewriter,monospace;\">[" . $l_progress . "]</span>";
    } // function

    /**
     * Callback function for construction of breadcrumb navigation.
     *
     * @param   array &$p_gets
     *
     * @return  null
     */
    public function breadcrumb_get(&$p_gets)
    {
        return null;
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Selcuk Kekec <skekec@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_dirs, $g_comp_template_language_manager;

        $i = 0;

        // Build tree.
        $l_root_node = $p_tree->add_node($i, $p_parent, _L('LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION'), '', '', 'images/icons/silk/application_view_icons.png');

        // Display: System settings
        $p_tree->add_node(
            ++$i,
            $l_root_node,
            _L('LC__SETTINGS__SYSTEM__TITLE'),
            "?" . isys_glob_http_build_query(
                [
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                    C__GET__SETTINGS_PAGE => C__SETTINGS_PAGE__SYSTEM,
                    C__GET__TREE_NODE     => $i
                ]
            ),
            '',
            'images/icons/silk/cog.png'
        );

        // Display: User settings.
        $l_usersettings_node   = $p_tree->add_node(++$i, $l_root_node, _L('LC__CMDB__TREE__SYSTEM__SETTINGS__USER'));
        $l_module_usersettings = new isys_module_user_settings();
        $l_module_usersettings->init(isys_module_request::get_instance());
        $l_module_usersettings->build_tree($p_tree, true, $l_usersettings_node);
        $l_system_auth = isys_auth_system::instance();

        // Display: Authorization module.
        if (defined('C__MODULE__AUTH'))
        {
            isys_module_auth::factory()
                ->init(isys_module_request::get_instance())
                ->build_tree($p_tree, true, $l_root_node);
        } // if

        // Display: CMDB Settings.
        $l_systemsettings_node = $p_tree->add_node(++$i, $l_root_node, $g_comp_template_language_manager->get('LC__CMDB__TREE__SYSTEM__SETTINGS__CMDB'));

        // Global settings -> QCW
        if (class_exists('isys_module_quick_configuration_wizard') && defined('C__MODULE__QCW') && isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/QCW')
        )
        {
            $l_qcw_module = new isys_module_quick_configuration_wizard();
            $l_qcw_module->init(isys_module_request::get_instance());
            $l_qcw_module->build_tree($p_tree, true, $l_systemsettings_node);
        } // if

        // Global settings -> Groups (Software, Infrastructe, Others)
        $l_cmdb_dao                       = new isys_cmdb_dao(isys_application::instance()->database);
        $l_groups                         = $l_cmdb_dao->get_object_group_by_id();
        $l_tmpget[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECTTYPE;
        $l_allowed_objtype_groups         = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttype_group_configs();

        if ($l_allowed_objtype_groups || is_array($l_allowed_objtype_groups))
        {
            $l_systemsettings_groups = $p_tree->add_node(++$i, $l_systemsettings_node, $g_comp_template_language_manager->get('LC__CMDB__OBJTYPE__CONFIGURATION_MODUS'));
            while ($l_grp = $l_groups->get_row())
            {
                if (is_array($l_allowed_objtype_groups) && !in_array($l_grp['isys_obj_type_group__id'], $l_allowed_objtype_groups)) continue;

                if (defined($l_grp['isys_obj_type_group__const']))
                {
                    $l_tmpget[C__CMDB__GET__OBJECTGROUP] = constant($l_grp['isys_obj_type_group__const']);
                    $p_tree->add_node(
                        ++$i,
                        $l_systemsettings_groups,
                        _L($l_grp['isys_obj_type_group__title']),
                        isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP, $l_tmpget),
                        null,
                        'images/icons/silk/box.png'
                    );
                }
                else
                {
                    isys_notify::error('The constant "' . $l_grp['isys_obj_type_group__const'] . '" could not be found! Please clear the cache!', ['sticky' => true]);
                }
            } // foreach
        } // if

        // Global settings -> CMDB-Status
        if (defined('C__MODULE__PRO') && C__MODULE__PRO && $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/CMDBSTATUS'))
        {
            $p_tree->add_node(
                ++$i,
                $l_systemsettings_node,
                _L('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS'),
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                        C__GET__SETTINGS_PAGE => C__SETTINGS_PAGE__CMDB_STATUS,
                        C__GET__TREE_NODE     => $i
                    ]
                ),
                '',
                'images/icons/silk/color_swatch.png'
            );
        } // if

        if ($l_system_auth->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMPROPERTIES'))
        {
            $p_tree->add_node(
                ++$i,
                $l_systemsettings_node,
                $this->m_additional_options['custom_properties']['text'],
                self::generate_link('custom_properties', $i),
                '',
                'images/icons/silk/database_gear.png'
            );
        } // if

        // Global settings -> Custom Categories
        if (defined('C__MODULE__CUSTOM_FIELDS') && class_exists('isys_module_custom_fields'))
        {
            if (class_exists('isys_module_custom_fields'))
            {
                if ($l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/CUSTOMFIELDS'))
                {
                    $l_module_custom_cat = new isys_module_custom_fields();
                    $l_module_custom_cat->build_tree($p_tree, true, $l_systemsettings_node, $i);
                } // if
            }

            if ($l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/RELATIONSHIPTYPES'))
            {
                $p_tree->add_node(
                    ++$i,
                    $l_systemsettings_node,
                    $this->m_additional_options['relation_types']['text'],
                    self::generate_link('relation_types', $i),
                    '',
                    'images/icons/silk/database_gear.png'
                );
            } // if

            if ($l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/ROLESADMINISTRATION'))
            {
                $p_tree->add_node(
                    ++$i,
                    $l_systemsettings_node,
                    $this->m_additional_options['roles_administration']['text'],
                    self::generate_link('roles_administration', $i),
                    '',
                    'images/icons/silk/database_gear.png'
                );
            } // if
        } // if

        // Global settings -> Logbook
        if (isys_auth_logbook::instance()
            ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK')
        )
        {
            $l_systemsettings_logbook_node = $p_tree->add_node(++$i, $l_systemsettings_node, _L('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__LOGBOOK'));

            isys_factory::get_instance('isys_module_logbook')
                ->build_tree($p_tree, true, $l_systemsettings_logbook_node);
        } // if

        // Global settings -> Dialog-Admin
        if (isys_auth_dialog_admin::instance()
                ->is_allowed_to(isys_auth::VIEW, 'TABLE') || isys_auth_dialog_admin::instance()
                ->is_allowed_to(isys_auth::VIEW, 'CUSTOM')
        )
        {
            $l_dialogadmin_node = $p_tree->add_node(++$i, $l_systemsettings_node, _L('LC__CMDB__TREE__SYSTEM__TOOLS__DIALOG_ADMIN'));

            isys_factory::get_instance('isys_module_dialog_admin')
                ->init(isys_module_request::get_instance())
                ->build_tree($p_tree, true, $l_dialogadmin_node);
        } // if

        // Global settings -> System settings
        if ($l_system_auth->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/SYSTEMSETTING'))
        {
            $p_tree->add_node(
                ++$i,
                $l_systemsettings_node,
                $this->m_additional_options['system_settings']['text'],
                self::generate_link('system_settings', $i),
                '',
                'images/icons/silk/cog_edit.png'
            );
        } // if

        if (defined('C__MODULE__QRCODE') && class_exists('isys_module_qrcode') && (isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'qr_config/global') || isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'qr_config/objtype'))
        )
        {
            isys_factory::get_instance('isys_module_qrcode')
                ->init(isys_module_request::get_instance())
                ->build_tree($p_tree, true, $l_systemsettings_node);
        } // if

        // Initialize rights
        $l_jdisc_rights        = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'JDISC');
        $l_jsonrpcapi_rights   = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'JSONRPCAPI/CONFIG');
        $l_loginventory_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LOGINVENTORY');
        $l_ocs_rights          = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'OCS');
        $l_ldap_rights         = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP');
        $l_tts_rights          = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'TTS');
        $l_systemtools_rights  = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS');
        $l_licence_rights      = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LICENCESETTINGS');
        $l_swapci_rights       = false;
        $l_nagios_rights       = false;
        $l_check_mk_rights     = false;
        $l_events_rights       = false;

        if (defined('C__MODULE__SWAPCI') && isys_module_manager::instance()
                ->is_active('swapci')
        )
        {
            if (class_exists('isys_auth_swapci'))
            {
                $l_swapci_rights = isys_auth_swapci::instance()
                    ->has_any_rights_in_module();
            } // if
        } // if

        // Rights: Nagios
        if (defined('C__MODULE__NAGIOS'))
        {
            if (class_exists('isys_auth_nagios'))
            {
                $l_nagios_rights = isys_auth_nagios::instance()
                    ->has_any_rights_in_module();
            }
            else
            {
                $l_nagios_rights = true;
            } // if
        } // if

        // Rights: CheckMK
        if (defined('C__MODULE__CHECK_MK') && isys_module_manager::instance()
                ->is_active('check_mk')
        )
        {
            if (class_exists('isys_auth_check_mk'))
            {
                $l_check_mk_rights = isys_auth_check_mk::instance()
                    ->has_any_rights_in_module();
            }
            else
            {
                $l_check_mk_rights = true;
            } // if
        } // if

        // Rights: Events
        if (defined('C__MODULE__EVENTS') && isys_module_manager::instance()
                ->is_active('events')
        )
        {
            if (class_exists('isys_auth_events'))
            {
                $l_events_rights = isys_auth_events::instance()
                    ->has_any_rights_in_module();
            }
            else
            {
                $l_events_rights = true;
            } // if
        } // if

        // Check for rights
        if ($l_jdisc_rights || $l_jsonrpcapi_rights || $l_loginventory_rights || $l_ocs_rights || $l_ldap_rights || $l_tts_rights || $l_systemtools_rights ||
            $l_licence_rights || $l_swapci_rights || $l_nagios_rights || $l_check_mk_rights || $l_events_rights
        )
        {
            // Display: Interfaces / Externals.
            $l_iext_node   = $p_tree->add_node(++$i, 0, _L('LC__CMDB__TREE__SYSTEM__INTERFACE'));
            $l_import_node = $p_tree->add_node(++$i, $l_iext_node, _L('LC__MODULE__IMPORT'), '', '', $g_dirs['images'] . 'icons/silk/database_copy.png');

            if (class_exists('isys_module_swapci'))
            {
                // Swap-CI module.
                if ($l_swapci_rights && isys_module_swapci::DISPLAY_IN_SYSTEM_MENU === true)
                {
                    $p_tree->add_node(++$i, $l_systemsettings_node, _L('LC__MODULE__SWAPCI'));

                    $l_swapci = isys_factory::get_instance('isys_module_swapci');
                    $l_swapci->init(isys_module_request::get_instance());
                    $l_swapci->build_tree($p_tree, true, $i);
                } // if
            }

            $l_monitoring = null;
            // Monitoring module.
            if (defined('C__MODULE__MONITORING') && class_exists('isys_module_monitoring') && isys_module_monitoring::DISPLAY_IN_SYSTEM_MENU === true)
            {
                $l_monitoring = $p_tree->add_node(++$i, $l_iext_node, _L('LC__MONITORING'), '', null, $g_dirs['images'] . 'icons/silk/monitor.png');

                isys_factory::get_instance('isys_module_monitoring')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            } // if

            // Check MK module.
            if ($l_check_mk_rights && class_exists('isys_module_check_mk') && isys_module_check_mk::DISPLAY_IN_SYSTEM_MENU === true)
            {
                // The Check_MK tree node is no longer used - The export is now located underneath the "Monitoring" module.
                isys_factory::get_instance('isys_module_check_mk')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            } // if

            // Interfaces / Externals -> JDISC
            if (defined('C__MODULE__JDISC') && class_exists('isys_module_jdisc') && isys_module_jdisc::DISPLAY_IN_SYSTEM_MENU === true && $l_jdisc_rights)
            {
                $p_tree->add_node(++$i, $l_import_node, _L('LC__CMDB__TREE__SYSTEM__INTERFACE__JDISC'));

                isys_factory::get_instance('isys_module_jdisc')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            } // if

            // Interfaces / Externals -> JSON-RPC API
            if ($l_jsonrpcapi_rights)
            {
                $p_tree->add_node(
                    ++$i,
                    $l_iext_node,
                    $this->m_additional_options['api']['text'],
                    self::generate_link('api', $i),
                    null,
                    $this->m_additional_options['api']['icon']
                );
            } // if

            // Events
            if (class_exists('isys_module_events') && isys_module_events::DISPLAY_IN_SYSTEM_MENU === true && $l_events_rights)
            {
                isys_factory::get_instance('isys_module_events')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_systemsettings_node);
            } // if

            // Interfaces / Externals -> LOGINventory
            if (class_exists('isys_module_loginventory') && isys_module_loginventory::DISPLAY_IN_SYSTEM_MENU === true && $l_loginventory_rights)
            {
                if (defined('C__MODULE__LOGINVENTORY'))
                {
                    isys_factory::get_instance('isys_module_loginventory')
                        ->init(isys_module_request::get_instance())
                        ->build_tree($p_tree, true, $l_import_node);
                }
            } // if

            // Interfaces / Externals -> OCS NG
            if ($l_ocs_rights)
            {
                $l_iext_ocs_node = $p_tree->add_node(++$i, $l_import_node, _L('LC__CMDB__TREE__SYSTEM__INTERFACE__OCS'));

                // Interfaces / Externals -> OCS NG -> Configuration
                $l_qparams = [
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__IMPORT,
                    'what'                => "ocsconfig",
                    C__GET__TREE_NODE     => $i + 1
                ];

                $p_tree->add_node(
                    ++$i,
                    $l_iext_ocs_node,
                    _L('LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__CONFIGURATION'),
                    isys_helper_link::create_url($l_qparams),
                    '',
                    $g_dirs['images'] . 'icons/ocs-inventory.png',
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'OCS/OCSCONFIG')
                );

                // Interfaces / Externals -> OCS NG -> Databases
                $l_qparams['what']            = 'ocsdb';
                $l_qparams[C__GET__TREE_NODE] = $i + 1;

                $p_tree->add_node(
                    ++$i,
                    $l_iext_ocs_node,
                    _L('LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__DATABASE'),
                    isys_helper_link::create_url($l_qparams),
                    '',
                    $g_dirs['images'] . 'icons/ocs-inventory.png',
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'OCS/OCSDB')
                );
            } // if

            // Interfaces / Externals -> LDAP
            if (defined('C__MODULE__LDAP') && class_exists('isys_module_ldap') && isys_module_ldap::DISPLAY_IN_SYSTEM_MENU === true && $l_ldap_rights)
            {
                $l_iext_ldap = $p_tree->add_node(++$i, $l_iext_node, _L('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP'));

                isys_factory::get_instance('isys_module_ldap')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_iext_ldap);
            } // if

            // Interfaces / Externals -> Nagios
            if (defined('C__MODULE__NAGIOS') && class_exists('isys_module_nagios') && isys_module_nagios::DISPLAY_IN_SYSTEM_MENU === true && $l_nagios_rights)
            {
                isys_factory::get_instance('isys_module_nagios')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_monitoring ?: $l_iext_node);
            } // if

            // Interfaces / Externals -> RFC
            if (defined('C__MODULE__RFC') && class_exists('isys_module_rfc'))
            {
                if (isys_module_rfc::DISPLAY_IN_SYSTEM_MENU === true)
                {
                    $l_iext_rfc = $p_tree->add_node(
                        ++$i,
                        $l_iext_node,
                        _L('LC__CMDB__TREE__SYSTEM__INTERFACE__RFC'),
                        '',
                        null,
                        null,
                        0,
                        '',
                        ''
                    );

                    $l_rfc_module = new isys_module_rfc();
                    $l_rfc_module->init(isys_module_request::get_instance());
                    $l_rfc_module->build_tree($p_tree, true, $l_iext_rfc);
                }
            } // if

            // Interfaces / Externals -> TTS
            if (defined('C__MODULE__TTS') && class_exists('isys_module_tts') && isys_module_tts::DISPLAY_IN_SYSTEM_MENU === true && $l_tts_rights)
            {
                $l_iext_tts_node = $p_tree->add_node(++$i, $l_iext_node, _L('LC__CMDB__TREE__SYSTEM__INTERFACE__TTS'));

                isys_factory::get_instance('isys_module_tts')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_iext_tts_node);
            } // if

            // Display: System-Tools.
            if ($l_systemtools_rights)
            {
                $l_systemtools_node = $p_tree->add_node(++$i, $l_root_node, _L('LC__CMDB__TREE__SYSTEM__TOOLS'));

                // System-Tools -> Cache
                $p_tree->add_node(
                    ++$i,
                    $l_systemtools_node,
                    $this->m_additional_options['cache']['text'],
                    self::generate_link('cache', $i),
                    null,
                    $this->m_additional_options['cache']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE')
                );

                // System-Tools -> Validation
                if (defined('C__MODULE__PRO'))
                {
                    $p_tree->add_node(
                        ++$i,
                        $l_systemsettings_node,
                        _L('LC__CMDB__TREE__SYSTEM__TOOLS__VALIDATION'),
                        isys_helper_link::create_url(
                            [
                                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                                C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                                C__GET__SETTINGS_PAGE => 'validation',
                                C__GET__TREE_NODE     => $i
                            ]
                        ),
                        '',
                        'images/icons/silk/page_red.png',
                        0,
                        '',
                        '',
                        $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/VALIDATION')
                    );
                } // if

                // System-Tools -> System-Overview
                $p_tree->add_node(
                    ++$i,
                    $l_systemtools_node,
                    $this->m_additional_options['sysoverview']['text'],
                    self::generate_link('sysoverview', $i),
                    null,
                    $this->m_additional_options['sysoverview']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW')
                );

                // System-Tools -> i-doit Update
                $p_tree->add_node(
                    ++$i,
                    $l_systemtools_node,
                    $this->m_additional_options['idoit_update']['text'],
                    $this->m_additional_options['idoit_update']['link'],
                    null,
                    $this->m_additional_options['idoit_update']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/IDOITUPDATE')
                );
            }

            if (defined('C__ENABLE__LICENCE') && C__ENABLE__LICENCE)
            {
                // Display: Licence.
                if ($l_licence_rights)
                {
                    $l_lic = $p_tree->add_node(++$i, 0, $g_comp_template_language_manager->get('LC__UNIVERSAL__LICENCEADMINISTRATION'));

                    // Licence -> Installation
                    $p_tree->add_node(
                        ++$i,
                        $l_lic,
                        $g_comp_template_language_manager->get('LC__UNIVERSAL__LICENE_INSTALLATION'),
                        isys_helper_link::create_url(
                            [
                                C__GET__MODULE_ID => C__MODULE__SYSTEM,
                                C__GET__TREE_NODE => $i,
                                'handle'          => 'licence_installation'
                            ]
                        ),
                        null,
                        'images/icons/key.png',
                        ($_GET['handle'] == 'licence_installation') ? 1 : 0,
                        '',
                        '',
                        $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LICENCESETTINGS/INSTALLATION')
                    );

                    // Licence -> Overview
                    $p_tree->add_node(
                        ++$i,
                        $l_lic,
                        $g_comp_template_language_manager->get('LC__UNIVERSAL__LICENE_OVERVIEW'),
                        isys_helper_link::create_url(
                            [
                                C__GET__MODULE_ID => C__MODULE__SYSTEM,
                                C__GET__TREE_NODE => $i,
                                'handle'          => 'licence_overview'
                            ]
                        ),
                        null,
                        'images/icons/silk/page_white_stack.png',
                        ($_GET['handle'] == 'licence_overview') ? 1 : 0,
                        '',
                        '',
                        $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LICENCESETTINGS/OVERVIEW')
                    );
                } // if
            } // if
        } // if
    } // function

    /**
     * @param $p_request
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        global $index_includes;

        // Unpack request package.
        $l_gets     = $this->m_userrequest->get_gets();
        $l_tplclass = $this->m_userrequest->get_template();
        $l_tree     = $this->m_userrequest->get_menutree();

        $this->build_tree($l_tree, false, -1);
        $l_tplclass->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]));

        try
        {
            if (isset($l_gets[C__GET__MODULE_SUB_ID]) && is_numeric($l_gets[C__GET__MODULE_SUB_ID]))
            {
                isys_module_request::get_instance()
                    ->get_module_manager()
                    ->get_by_id($l_gets[C__GET__MODULE_SUB_ID])
                    ->get_object()
                    ->start();
            }
            else
            {
                // If option is not set, set 'overview' as default.
                if (!isset($l_gets['what']) && !isset($l_gets['handle']))
                {
                    $l_gets['what'] = 'overview';
                } // if

                // Call option-specific method.
                if (isset($l_gets['what']) && is_array($this->m_additional_options))
                {
                    if (array_key_exists($l_gets['what'], $this->m_additional_options))
                    {
                        $l_dat = $this->m_additional_options[$l_gets['what']];
                        if (is_array($l_dat))
                        {
                            if (method_exists($this, $l_dat['func']))
                            {
                                call_user_func(
                                    [
                                        $this,
                                        $l_dat['func']
                                    ]
                                );
                            } // if
                        } // if
                    } // if
                }
                else
                {
                    if (isset($l_gets['handle']))
                    {
                        if (method_exists($this, 'handle_' . $l_gets['handle']))
                        {
                            call_user_func(
                                [
                                    $this,
                                    'handle_' . $l_gets['handle']
                                ]
                            );
                        } // if
                    }
                } // if
            } // if
        }
        catch (isys_exception_general $e)
        {
            throw $e;
        }
        catch (isys_exception_auth $e)
        {
            $l_tplclass->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        } // try
    } // function

    /**
     * Deletes all objects with the given status.
     *
     * @param   integer $p_type
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_objects($p_type = C__RECORD_STATUS__BIRTH)
    {
        if ($p_type == C__RECORD_STATUS__NORMAL)
        {
            die('Erm... No. I won\'t do that.');
        } // if

        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);

        $l_res   = $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int($p_type) . ' AND isys_obj__undeletable = 0;');
        $l_count = $l_res->count();

        if ($l_count > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_dao->delete_object_and_relations($l_row['isys_obj__id']);
            } // while

            return $l_count;
        } // if

        return 0;
    } // function

    /**
     * Lists all objects with the given status (for system module -> cache).
     *
     * @global  array   $g_dirs
     *
     * @param   integer $p_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function list_objects($p_type = C__RECORD_STATUS__BIRTH)
    {
        global $g_dirs;

        $l_return    = [];
        $l_dao       = new isys_cmdb_dao(isys_application::instance()->database);
        $l_quickinfo = new isys_ajax_handler_quick_info();

        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj__sysid, isys_obj_type__title
			FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__status = ' . $l_dao->convert_sql_int($p_type) . ' AND isys_obj__undeletable = 0;';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[] = $l_quickinfo->get_quick_info(
                    $l_row['isys_obj__id'],
                    '<img src="' . $g_dirs['images'] . 'icons/silk/information.png" class="vam" /> <span class="vam">' . _L(
                        $l_row['isys_obj_type__title']
                    ) . ' > ' . $l_row['isys_obj__title'] . '</span>',
                    C__LINK__OBJECT
                );
            } // while
        } // if

        return $l_return;
    } // function

    public function cleanup_other($p_type)
    {
        switch ($p_type)
        {
            case 'check_mk_exported_tags':
                if (isys_module_manager::instance()
                    ->is_active('check_mk')
                )
                {
                    $l_check_mk_dao = new isys_check_mk_dao_generic_tag(isys_application::instance()->database);

                    if ($l_check_mk_dao->delete_exported_tags_from_database())
                    {
                        echo 'Successfully removed all exported tags!';
                    }
                    else
                    {
                        echo 'An error occured while removing the exported tags: ' . $l_check_mk_dao->get_database_component()
                                ->get_last_error_as_string();
                    } // if
                } // if

                break;
        } // switch
    } // function

    /**
     * Deletes all categorie entries with the given status.
     *
     * @param   integer $p_type
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_categories($p_type = C__RECORD_STATUS__BIRTH)
    {
        if ($p_type == C__RECORD_STATUS__NORMAL)
        {
            die('i-doit will not remove all category data with status "normal"!');
        } // if

        $l_log = isys_factory_log::get_instance('category_cleanup');

        $l_count_all = 0;
        $l_dao       = new isys_cmdb_dao(isys_application::instance()->database);
        $l_catg_row  = [];

        $l_arr_skip = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [
                C__CATG__LOGICAL_UNIT,
                C__CATG__ASSIGNED_LOGICAL_UNIT,
                C__CATG__OBJECT,
                C__CATG__WORKFLOW,
                C__CATG__NAGIOS_SERVICE_REFS_TPL_BACKWARDS,
                C__CATG__NAGIOS_HOST_TPL_ASSIGNED_OBJECTS,
                C__CATG__NAGIOS_REFS_SERVICES_BACKWARDS,
                C__CATG__NAGIOS_REFS_SERVICES,
                C__CATG__NAGIOS_APPLICATION_FOLDER,
                C__CATG__NAGIOS_APPLICATION_REFS_NAGIOS_SERVICE,
                C__CATG__VIRTUAL_AUTH,
                C__CATG__VIRTUAL_SUPERNET,
                C__CATG__NET_CONNECTIONS_FOLDER
            ],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => [
                C__CATS__CONTRACT_ALLOCATION,
                C__CMDB__SUBCAT__WS_ASSIGNMENT,
                C__CMDB__SUBCAT__FILE_VERSIONS,
                C__CMDB__SUBCAT__FILE_OBJECTS,
                C__CMDB__SUBCAT__FILE_ACTUAL
            ]
        ];

        // Remove Custom category entries
        $l_res = $l_dao->get_all_catg_custom(null, ' AND isysgui_catg_custom__list_multi_value > 0');
        while($l_row = $l_res->get_row())
        {
            // Remove custom fields
            $l_res2 = $l_dao->retrieve('SELECT * FROM isys_catg_custom_fields_list WHERE
              isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $l_dao->convert_sql_id($l_row['isysgui_catg_custom__id']) . '
              AND (isys_catg_custom_fields_list__status = ' . $l_dao->convert_sql_int($p_type) . ' OR isys_catg_custom_fields_list__status IS NULL)');
            $l_data_ids = [];
            while($l_row2 = $l_res2->get_row())
            {
                if(!isset($l_data_ids[$l_row2['isys_catg_custom_fields_list__data__id']]))
                {
                    $l_log->info('Deleting custom fields data entry #' . $l_row2['isys_catg_custom_fields_list__data__id'] . ' in "isys_catg_custom_fields_list" ...');
                    $l_data_ids[$l_row2['isys_catg_custom_fields_list__data__id']] = true;
                } // if

                $l_dao->delete_entry($l_row2['isys_catg_custom_fields_list__id'], 'isys_catg_custom_fields_list');
            } // while
            $l_count_all += count($l_data_ids);
            unset($l_data_ids);
        } // while

        $l_res = $l_dao->get_all_catg(
            null,
            ' AND isysgui_catg__list_multi_value > 0 AND isysgui_catg__id NOT IN (' . implode(',', $l_arr_skip[C__CMDB__CATEGORY__TYPE_GLOBAL]) . ')'
        );

        while ($l_row = $l_res->get_row())
        {
            $l_class = $l_row['isysgui_catg__class_name'];
            $l_table = (substr($l_row['isysgui_catg__source_table'], -5) == '_list') ? $l_row['isysgui_catg__source_table'] : $l_row['isysgui_catg__source_table'] . '_list';

            if (class_exists($l_class) && strpos($l_table, '_2_') == false)
            {
                $l_catg_res = call_user_func(
                    [
                        $l_class,
                        'instance'
                    ],
                    isys_application::instance()->database
                )->get_data(
                    null,
                    null,
                    ' AND (' . $l_table . '.' . $l_table . '__status = ' . $l_dao->convert_sql_int($p_type) . ' OR ' . $l_table . '.' . $l_table . '__status IS NULL)',
                    null,
                    $p_type
                );

                $l_count = $l_catg_res->num_rows();
                if ($l_count > 0)
                {
                    while ($l_catg_row = $l_catg_res->get_row())
                    {
                        $l_log->info('Deleting entry #' . $l_catg_row[$l_table . '__id'] . ' in "' . $l_class . '" ...');
                        $l_dao->delete_entry($l_catg_row[$l_table . '__id'], $l_table);
                    } // while

                    $l_count_all += $l_count;
                } // if
            } // if
        } // while

        $l_res = $l_dao->get_all_cats(
            null,
            ' AND isysgui_cats__list_multi_value > 0 AND isysgui_cats__id NOT IN (' . implode(',', $l_arr_skip[C__CMDB__CATEGORY__TYPE_SPECIFIC]) . ')'
        );

        while ($l_row = $l_res->get_row())
        {
            $l_table = $l_row['isysgui_cats__source_table'];
            $l_class = $l_row['isysgui_cats__class_name'];

            if (class_exists($l_class) && strpos($l_table, '_2_') == false)
            {
                $l_cats_res = call_user_func(
                    [
                        $l_class,
                        'instance'
                    ],
                    isys_application::instance()->database
                )->get_data(
                    null,
                    null,
                    ' AND (' . $l_table . '.' . $l_table . '__status = ' . $l_dao->convert_sql_int($p_type) . ' OR ' . $l_table . '.' . $l_table . '__status IS NULL)',
                    null,
                    $p_type
                );

                $l_count = $l_cats_res->num_rows();

                if ($l_count > 0)
                {
                    while ($l_cats_row = $l_cats_res->get_row())
                    {
                        $l_log->info('Deleting entry #' . $l_catg_row[$l_table . '__id'] . ' in "' . $l_class . '" ...');
                        $l_dao->delete_entry($l_cats_row[$l_table . '__id'], $l_table);
                    } // while

                    $l_count_all += $l_count;
                } // if
            } // if
        } // while

        $l_log->flush_log();

        return $l_count_all;
    } // function

    /**
     * Method which removes duplicate "obj-type to category" assignments. This sometimes happens by accident.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_category_assignments()
    {
        echo 'Removing duplicate category assignments...<br />';

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_res = $l_dao->get_object_types();

        while ($l_row = $l_res->get_row())
        {
            $l_sql = 'SELECT isys_obj_type_2_isysgui_catg__id, isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id, count(isys_obj_type_2_isysgui_catg__isysgui_catg__id) AS cnt
				FROM isys_obj_type_2_isysgui_catg
				WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_row['isys_obj_type__id']) . '
				GROUP BY isys_obj_type_2_isysgui_catg__isysgui_catg__id
				HAVING cnt > 1
				ORDER BY cnt;';

            $l_duplicate_res = $l_dao->retrieve($l_sql);

            if ($l_duplicate_res->num_rows() > 0)
            {
                echo 'Removing duplicates from obj-type "' . _L($l_row['isys_obj_type__title']) . '" (' . $l_row['isys_obj_type__id'] . ')<br />';

                while ($l_duplicate_row = $l_duplicate_res->get_row())
                {
                    // With this SQL we remove all duplicates but one.
                    $l_remove_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg
						WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__isysgui_catg__id']) . '
						AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__isys_obj_type__id']) . '
						AND isys_obj_type_2_isysgui_catg__id != ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__id']) . ';';

                    $l_dao->update($l_remove_sql);
                } // while

                $l_dao->apply_update();
            } // if
        } // while

        echo 'Done!<hr class="mb5 mt5" />';
    } // function

    /**
     * Method which removes duplicate single-value categorie entries. This sometimes happens by accident.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_duplicate_single_value_entries()
    {
        echo 'Deleting duplicate single-value categorie entries...<br />';

        // Here we define some categories, which are not allowed to be modified.
        $l_blacklist_catg  = [
            C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW => true,
            C__CATG__STORAGE                       => true,
            C__CATG__CUSTOM_FIELDS                 => true,
            C__CATG__OPERATING_SYSTEM              => true,
        ];
        $l_deleted_entries = 0;

        /**
         * @var isys_cmdb_dao $l_dao
         */
        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);
        $l_res = $l_dao->get_all_catg(null, ' AND isysgui_catg__list_multi_value = 0 AND isysgui_catg__type = \'' . isys_cmdb_dao_category::TYPE_EDIT . '\'');

        // Global categories
        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                // We use numeric values for better performance.
                if (isset($l_blacklist_catg[$l_row['isysgui_catg__id']]))
                {
                    continue;
                } // if
                $l_src = $l_row['isysgui_catg__source_table'];
                // Check if table really exists
                if ($this->does_table_exists($l_src . '_list'))
                {
                    $l_cat_sql         = 'SELECT ' . $l_src . '_list__id as id, ' . $l_src . '_list__isys_obj__id as obj_id, count(' . $l_src . '_list__isys_obj__id) as cnt
						FROM ' . $l_src . '_list
						GROUP BY ' . $l_src . '_list__isys_obj__id
						HAVING cnt > 1';
                    $l_cat_res         = $l_dao->retrieve($l_cat_sql);
                    $l_amount_deletion = $l_cat_res->num_rows();
                    if ($l_amount_deletion)
                    {
                        echo 'Deleting duplicate entries in global category "' . _L($l_row['isysgui_catg__title']) . '"... ';
                        while ($l_cat_row = $l_cat_res->get_row())
                        {
                            $l_remove_sql = 'DELETE FROM ' . $l_src . '_list
								WHERE ' . $l_src . '_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_cat_row['obj_id']) . '
								AND ' . $l_src . '_list__id != ' . $l_dao->convert_sql_id($l_cat_row['id']) . ';';
                            $l_dao->update($l_remove_sql);
                        } // while
                        if ($l_dao->apply_update())
                        {
                            echo 'Done!<br />';
                        }
                        else
                        {
                            echo 'Error!<br />';
                        } // if
                        $l_deleted_entries += $l_amount_deletion;
                    } // if
                } // if
            } // while
        } // if

        $l_blacklist_cats = [
            C__CATS__NET_IP_ADDRESSES => true
        ];

        // Specific categories
        $l_res = $l_dao->get_all_cats(null, ' AND isysgui_cats__list_multi_value = 0 AND isysgui_cats__type = \'' . isys_cmdb_dao_category::TYPE_EDIT . '\'');
        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                // We use numeric values for better performance.
                if (isset($l_blacklist_cats[$l_row['isysgui_cats__id']]))
                {
                    continue;
                } // if
                $l_src = $l_row['isysgui_cats__source_table'];
                // Check if table really exists
                if ($this->does_table_exists($l_src))
                {
                    $l_cat_sql = 'SELECT ' . $l_src . '__id as id, ' . $l_src . '__isys_obj__id as obj_id, count(' . $l_src . '__isys_obj__id) as cnt
						FROM ' . $l_src . '
						GROUP BY ' . $l_src . '__isys_obj__id
						HAVING cnt > 1';

                    $l_cat_res         = $l_dao->retrieve($l_cat_sql);
                    $l_amount_deletion = $l_cat_res->num_rows();
                    if ($l_amount_deletion)
                    {
                        echo 'Deleting duplicate entries in specific category "' . _L($l_row['isysgui_cats__title']) . '"... ';
                        while ($l_cat_row = $l_cat_res->get_row())
                        {
                            $l_remove_sql = 'DELETE FROM ' . $l_src . '
								WHERE ' . $l_src . '__isys_obj__id = ' . $l_dao->convert_sql_id($l_cat_row['obj_id']) . '
								AND ' . $l_src . '__id != ' . $l_dao->convert_sql_id($l_cat_row['id']) . ';';
                            $l_dao->update($l_remove_sql);
                        } // while
                        if ($l_dao->apply_update())
                        {
                            echo 'Done!<br />';
                        }
                        else
                        {
                            echo 'Error!<br />';
                        } // if
                        $l_deleted_entries += $l_amount_deletion;
                    } // if
                } // if
            } // while
        } // if

        // Custom categories
        $l_res = $l_dao->get_all_catg_custom(null, ' AND isysgui_catg_custom__list_multi_value = 0');
        if ($l_res->num_rows() > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_custom_fields_id = $l_row['isysgui_catg_custom__id'];
                $l_sql              = 'SELECT *, COUNT(isys_catg_custom_fields_list__isys_obj__id) AS cnt, GROUP_CONCAT(isys_catg_custom_fields_list__data__id) AS ids
                    FROM (SELECT *  FROM `isys_catg_custom_fields_list` WHERE `isys_catg_custom_fields_list__isysgui_catg_custom__id` = ' . $l_dao->convert_sql_id(
                        $l_custom_fields_id
                    ) . '
                    GROUP BY isys_catg_custom_fields_list__data__id ORDER BY isys_catg_custom_fields_list__data__id ASC) AS customf GROUP BY isys_catg_custom_fields_list__isys_obj__id HAVING cnt > 1';

                $l_res2 = $l_dao->retrieve($l_sql);
                if ($l_res2->num_rows() > 0)
                {
                    echo 'Deleting duplicate entries in custom category "' . _L($l_row['isysgui_catg_custom__title']) . '"... <br />';
                    while ($l_row2 = $l_res2->get_row())
                    {
                        $l_data_ids = explode(',', $l_row2['ids']);
                        // remove last entry
                        array_pop($l_data_ids);
                        $l_amount_deletion = count($l_data_ids);

                        if ($l_amount_deletion > 0)
                        {
                            // Delete all duplicate entries
                            $l_delete = 'DELETE FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__data__id IN (' . implode(
                                    ',',
                                    $l_data_ids
                                ) . ')' . ' AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $l_dao->convert_sql_id($l_custom_fields_id);
                            $l_dao->update($l_delete);
                        } // if
                        $l_deleted_entries += $l_amount_deletion;
                    } // while
                } // if
            } // while
            $l_dao->apply_update();
        } // if

        if ($l_deleted_entries)
        {
            echo '(' . $l_deleted_entries . ') Duplicate single value entries found and deleted. <br />';
        }
        else
        {
            echo 'No duplicate single value entries found.<br />';
        } // if

        echo '<hr class="mt5 mb5" />';
    } // function

    /**
     * Helper function which checks if a table really exists
     *
     * @param $p_table
     *
     * @return bool
     */
    public function does_table_exists($p_table)
    {
        $l_sql = 'SHOW TABLES LIKE \'' . $p_table . '\';';
        $l_res = isys_application::instance()->database->query($l_sql);

        return (isys_application::instance()->database->num_rows($l_res) > 0) ? true : false;
    } // function

    /**
     * This function deletes all unassigned relation entries and objects
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function cleanup_unassigned_relations()
    {
        $l_dao_rel               = isys_cmdb_dao_relation::instance(isys_application::instance()->database);
        $l_stats                 = $l_dao_rel->delete_dead_relations();
        $l_dead_relation_objects = $l_stats[isys_cmdb_dao_relation::C__DEAD_RELATION_OBJECTS];

        if ($l_dead_relation_objects > 0)
        {
            //echo '(' . $l_amount . ') unassigned relation objects deleted.<br>';
            echo _L('LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS__OBJECTS_DELETED', [$l_dead_relation_objects]) . '<br>';
        }
        else
        {
            echo _L('LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS__NO_OBJECTS_DELETED');
        } // if

        echo '<hr class="mt5 mb5" />';
    } // function

    /**
     * This function renews the relation titles of all relation objects.
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function renew_relation_titles()
    {

        $l_sql     = 'SELECT * FROM isys_catg_relation_list INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id';
        $l_dao     = isys_factory::get_instance('isys_cmdb_dao_category_g_relation', isys_application::instance()->database);
        $l_changes = false;

        $l_res = $l_dao->retrieve($l_sql);
        while ($l_row = $l_res->get_row())
        {
            $l_obj           = $l_row['isys_catg_relation_list__isys_obj__id'];
            $l_master        = $l_row['isys_catg_relation_list__isys_obj__id__master'];
            $l_slave         = $l_row['isys_catg_relation_list__isys_obj__id__slave'];
            $l_relation_type = $l_row['isys_catg_relation_list__isys_relation_type__id'];
            $l_old_name      = $l_row['isys_obj__title'];

            $l_dao->update_relation_object($l_obj, $l_master, $l_slave, $l_relation_type);

            $l_new_name = $l_dao->get_obj_name_by_id_as_string($l_row['isys_obj__id']);

            if ($l_old_name != $l_new_name)
            {
                $l_changes = true;
                echo "Relation title <strong>'" . $l_old_name . "'</strong> changed to <strong>'" . $l_new_name . "'</strong> (" . $l_obj . ").<br />";
            } // if
        } // while

        if (!$l_changes)
        {
            echo "No broken relation title found.";
        } // if
    } // function

    /**
     * Method for refilling empty SYS-IDs
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function refill_empty_sysids()
    {
        $l_sql = 'SELECT * FROM isys_obj
		    LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__sysid = ""
			OR isys_obj__sysid IS NULL;';

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_new_sysid = $l_dao->generate_sysid($l_row['isys_obj_type__id'], $l_row['isys_obj__id']);

                echo 'The ' . _L(
                        $l_row['isys_obj_type__title']
                    ) . ' "' . $l_row['isys_obj__title'] . '" (#' . $l_row['isys_obj__id'] . ') has no SYS-ID. Filling it with: ' . $l_new_sysid . '<br />';

                $l_sql = 'UPDATE isys_obj SET isys_obj__sysid = ' . $l_dao->convert_sql_text($l_new_sysid) . ' WHERE isys_obj__id = ' . $l_dao->convert_sql_id(
                        $l_row['isys_obj__id']
                    ) . ';';

                if (!$l_dao->update($l_sql))
                {
                    throw new isys_exception_cmdb("Updating the object with a new SYS-ID failed!");
                } // if
            } // while
        }
        else
        {
            echo 'No empty SYS-IDs found.';
        } // if

        echo '<hr class="mb5 mt5" />';
    } // function

    /**
     * Temporary solution in cleaning the auth table
     *
     * @return mixed
     */
    public function cleanup_auth()
    {
        $l_modules = isys_module_manager::instance()
            ->get_modules();
        $l_dao     = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_system_module = false;

        while ($l_row = $l_modules->get_row())
        {
            $l_auth_instance = isys_module_manager::instance()
                ->get_module_auth($l_row['isys_module__id']);

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

            // Get auth methods
            $l_auth_module_methods = $l_auth_module_obj->get_auth_methods();

            foreach ($l_auth_module_methods AS $l_method => $l_content)
            {
                // Check if cleanup exists
                if (!array_key_exists('cleanup', $l_content))
                {
                    continue;
                } // if
                $l_found = false;
                foreach ($l_content['cleanup'] AS $l_table => $l_search_field)
                {
                    // Prepare search query
                    $l_query = 'SELECT * FROM ' . $l_table . ' WHERE ' . $l_search_field . ' = ';
                    // Prepare delete query
                    $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id = ';
                    // Get paths
                    $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' . $l_dao->convert_sql_id(
                            $l_row['isys_module__id']
                        ) . ' ' . 'AND isys_auth__path LIKE ' . $l_dao->convert_sql_text(strtoupper($l_method) . '/%');

                    $l_res = $l_dao->retrieve($l_auth_query);
                    if (!$l_found && $l_res->num_rows() > 0)
                    {
                        while ($l_row2 = $l_res->get_row())
                        {
                            $l_search_query  = $l_query;
                            $l_delete_query2 = $l_delete_query;
                            $l_path_arr      = explode('/', $l_row2['isys_auth__path']);
                            $l_field_value   = $l_path_arr[1];

                            if ($l_field_value == isys_auth::WILDCHAR)
                            {
                                continue;
                            } // if

                            $l_search_query .= (is_numeric($l_field_value)) ? $l_dao->convert_sql_id($l_field_value) : $l_dao->convert_sql_text($l_field_value);
                            $l_search_res = $l_dao->retrieve($l_search_query);
                            if ($l_search_res->num_rows() == 0)
                            {
                                // Field value does not exist complete the delete query
                                $l_delete_query2 .= $l_dao->convert_sql_id($l_row2['isys_auth__id']);
                            }
                            else
                            {
                                // Field value found
                                unset($l_delete_query2);
                                $l_found = true;
                            } // if
                        } // while
                    } // if
                } // foreach
                if (isset($l_delete_query2))
                {
                    $l_dao->update($l_delete_query2);
                } // if
            } // foreach
        } // while
        return $l_dao->apply_update();
    } // function

    /**
     * Handles relationship types
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function handle_relation_types()
    {
        isys_auth_system_globals::instance()
            ->relationshiptypes(isys_auth::VIEW);

        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);
        $l_navbar       = isys_component_template_navbar::getInstance();

        $l_posts   = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        switch ($l_navmode)
        {
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            case C__NAVMODE__SAVE:
                try
                {
                    // At first we update all the "weighting" data.
                    $l_res = $l_dao_relation->retrieve('SELECT isys_relation_type__id FROM isys_relation_type;');

                    while ($l_row = $l_res->get_row())
                    {
                        $l_dao_relation->update_relation_type_weighting($l_row['isys_relation_type__id'], $l_posts['relation_weighting'][$l_row['isys_relation_type__id']]);
                    } // while

                    if ($l_posts['delRelTypes'] != '')
                    {
                        $l_dao_contact                   = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->database);
                        $l_del_rel_types                 = explode(',', $l_posts['delRelTypes']);
                        $l_contacts_res                  = $l_dao_contact->get_contact_objects_by_tag(
                            null,
                            null,
                            'AND isys_contact_tag__isys_relation_type__id IN (' . $l_posts['delRelTypes'] . ')'
                        );
                        $l_contacts_role_already_updated = [];

                        while ($l_row = $l_contacts_res->get_row())
                        {
                            $l_relation_type       = C__RELATION_TYPE__USER;
                            $l_master              = $l_row['isys_catg_contact_list__isys_obj__id'];
                            $l_slave               = $l_row['isys_obj__id'];
                            $l_catg_contact_id     = $l_row['isys_catg_contact_list__id'];
                            $l_catg_contact_rel_id = $l_row['isys_catg_contact_list__isys_catg_relation_list__id'];
                            $l_contact_role        = $l_row['isys_contact_tag__id'];

                            if (!in_array($l_contact_role, $l_contacts_role_already_updated))
                            {
                                // Set contact role with relation type contact user
                                $l_dao_contact->update_contact_tag($l_contact_role, null, $l_relation_type);
                                $l_contacts_role_already_updated[] = $l_contact_role;
                            } // if

                            // Update all contacts with the default relation type for contacts
                            $l_dao_relation->handle_relation($l_catg_contact_id, "isys_catg_contact_list", $l_relation_type, $l_catg_contact_rel_id, $l_master, $l_slave);
                        } // if

                        $l_dao_relation->remove_relation_type($l_del_rel_types);
                    } // if

                    $l_relation_types_res = $l_dao_relation->get_relation_type();
                    $l_rel_types          = [];

                    while ($l_row_rel_type = $l_relation_types_res->get_row())
                    {
                        $l_rel_types[$l_row_rel_type['isys_relation_type__id']] = $l_row_rel_type['isys_relation_type__title'];
                    } // while

                    if (isset($l_posts['relation_title']))
                    {
                        foreach ($l_posts['relation_title'] AS $l_key => $l_val)
                        {
                            $l_title        = $l_val;
                            $l_master_title = $l_posts['relation_title_master'][$l_key];
                            $l_slave_title  = $l_posts['relation_title_slave'][$l_key];
                            $l_direction    = $l_posts['relation_direction'][$l_key];
                            $l_type         = $l_posts['relation_type'][$l_key];

                            $l_dao_relation->update_relation_type($l_key, $l_title, $l_master_title, $l_slave_title, $l_direction, $l_type);
                        } // foreach
                    } // if

                    if (isset($l_posts['new_relation_title']))
                    {
                        foreach ($l_posts['new_relation_title'] as $l_key => $l_val)
                        {
                            $l_title        = $l_val;
                            $l_master_title = $l_posts['new_relation_title_master'][$l_key];
                            $l_slave_title  = $l_posts['new_relation_title_slave'][$l_key];
                            $l_direction    = $l_posts['new_relation_direction'][$l_key];
                            $l_weighting    = $l_posts['new_relation_weighting'][$l_key];
                            $l_type         = $l_posts['new_relation_type'][$l_key];

                            if (!empty($l_title) && !in_array($l_title, $l_rel_types))
                            {
                                $l_dao_relation->add_new_relation_type(
                                    $l_title,
                                    $l_master_title,
                                    $l_slave_title,
                                    $l_direction,
                                    null,
                                    1,
                                    C__RECORD_STATUS__NORMAL,
                                    $l_weighting,
                                    $l_type
                                );
                            }
                            else
                            {
                                if (($l_rel_id = array_search($l_title, $l_rel_types)))
                                {
                                    $l_dao_relation->update_relation_type($l_rel_id, $l_title, $l_master_title, $l_slave_title, $l_direction, $l_type);
                                }
                            } // if
                        } // foreach
                    } // if

                    isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                }
                catch (isys_exception_general $e)
                {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        } // switch

        $l_weighting_dao    = isys_factory_cmdb_dialog_dao::get_instance('isys_weighting', isys_application::instance()->database);
        $l_weighting_dialog = isys_factory::get_instance('isys_smarty_plugin_f_dialog');
        $l_relation_types   = $l_dao_relation->get_relation_types_as_array();
        $l_relation_type    = [];

        isys_glob_sort_array_by_column($l_relation_types, 'title_lang');

        foreach ($l_relation_types as &$l_relation_type)
        {
            $l_weighting = $l_weighting_dao->get_data($l_relation_type['weighting']);

            $l_dialog_params = [
                'name'            => 'relation_weighting[' . $l_relation_type['id'] . ']',
                'p_strTable'      => 'isys_weighting',
                'p_strClass'      => 'input-mini',
                'p_strSelectedID' => $l_relation_type['weighting'],
                'order'           => 'isys_weighting__id',
                'p_bSort'         => false
            ];

            $l_relation_type['weighting']      = $l_weighting_dialog->navigation_edit(
                isys_module_request::get_instance()
                    ->get_template(),
                $l_dialog_params
            );
            $l_relation_type['weighting_text'] = _L($l_weighting['isys_weighting__title']);
            $l_relation_type['type']           = $l_relation_type["type"];
        } // foreach

        $l_dialog_params = [
            'name'            => 'new_relation_weighting[]',
            'p_strTable'      => 'isys_weighting',
            'p_strClass'      => 'input-mini',
            'p_strSelectedID' => $l_relation_type['weighting'],
            'order'           => 'isys_weighting__id',
            'p_bSort'         => false
        ];

        isys_module_request::get_instance()
            ->get_template()
            ->assign(
                'weighting_tpl',
                $l_weighting_dialog->navigation_edit(
                    isys_module_request::get_instance()
                        ->get_template(),
                    $l_dialog_params
                )
            )
            ->assign('relation_types', $l_relation_types)
            ->include_template('contentbottomcontent', 'modules/system/relation_types.tpl');
    } // function

    /**
     *
     * @throws  isys_exception_cmdb
     */
    public function handle_roles_administration()
    {
        isys_auth_system_globals::instance()
            ->rolesadministration(isys_auth::VIEW);

        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);
        $l_dao_contact  = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->database);
        $l_navbar       = isys_component_template_navbar::getInstance();

        $l_posts   = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        $l_condition      = " AND isys_relation_type__const IS NULL OR isys_relation_type__category = " . $l_dao_relation->convert_sql_text('C__CATG__CONTACT');
        $l_relation_types = $l_dao_relation->get_relation_types_as_array(null, C__RELATION__IMPLICIT, $l_condition);

        switch ($l_navmode)
        {
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
            case C__NAVMODE__SAVE:

                $l_new_contact_role_titles    = $l_posts['new_role_title'];
                $l_new_contact_role_relations = $l_posts['new_role_relation_type'];
                $l_delete_contact_roles       = $l_posts['delRoles'];
                $l_update_contact_roles       = $l_posts['updRoles'];
                try
                {
                    $l_contact_tag_res = $l_dao_contact->get_contact_tag_data();
                    $l_contact_tag_arr = [];
                    while ($l_contact_tag_row = $l_contact_tag_res->get_row())
                    {
                        $l_contact_tag_arr[$l_contact_tag_row['isys_contact_tag__id']] = $l_contact_tag_row['isys_contact_tag__title'];
                    } // while

                    if (count($l_new_contact_role_titles) > 0)
                    {
                        foreach ($l_new_contact_role_titles AS $l_key => $l_role_title)
                        {
                            if (!in_array($l_role_title, $l_contact_tag_arr))
                            {
                                $l_dao_contact->add_contact_tag($l_role_title, $l_new_contact_role_relations[$l_key]);
                            }
                            elseif (($l_contact_tag_id = array_search($l_role_title, $l_contact_tag_arr)))
                            {
                                $l_update_contact_roles .= ',' . $l_contact_tag_id;
                                $l_posts['role_relation_type'][$l_contact_tag_id] = $l_new_contact_role_relations[$l_key];
                                $l_posts['role_title'][$l_contact_tag_id]         = $l_role_title;
                            }
                        } // foreach
                    } // if

                    if ($l_delete_contact_roles != '')
                    {
                        $l_delete_contact_roles_as_array = explode(',', $l_delete_contact_roles);
                        $l_contacts_res                  = $l_dao_contact->get_contact_objects_by_tag(null, $l_delete_contact_roles_as_array);
                        while ($l_row = $l_contacts_res->get_row())
                        {
                            $l_relation_type       = C__RELATION_TYPE__USER;
                            $l_master              = $l_row['isys_catg_contact_list__isys_obj__id'];
                            $l_slave               = $l_row['isys_obj__id'];
                            $l_catg_contact_id     = $l_row['isys_catg_contact_list__id'];
                            $l_catg_contact_rel_id = $l_row['isys_catg_contact_list__isys_catg_relation_list__id'];
                            $l_dao_relation->handle_relation($l_catg_contact_id, "isys_catg_contact_list", $l_relation_type, $l_catg_contact_rel_id, $l_master, $l_slave);
                        } // if
                        $l_dao_contact->delete_contact_tag($l_delete_contact_roles_as_array);
                    } // if

                    if ($l_update_contact_roles != '')
                    {
                        $l_update_contact_roles          = explode(',', $l_update_contact_roles);
                        $l_contact_update_relation_types = $l_posts['role_relation_type'];
                        $l_contact_update_title          = $l_posts['role_title'];
                        foreach ($l_update_contact_roles AS $l_contact_tag_id)
                        {
                            $l_contact_tag_relation_type_id = $l_contact_update_relation_types[$l_contact_tag_id];
                            $l_contact_tag_title            = (isset($l_contact_update_title[$l_contact_tag_id])) ? $l_contact_update_title[$l_contact_tag_id] : null;

                            $l_dao_contact->update_contact_tag($l_contact_tag_id, $l_contact_tag_title, $l_contact_tag_relation_type_id);
                        } // foreach
                    } // if
                    isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                }
                catch (isys_exception_general $e)
                {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        } // switch

        isys_module_request::get_instance()
            ->get_template()
            ->assign('contact_roles', $l_dao_contact->get_contact_tag_data())
            ->assign('relation_types', $l_relation_types)
            ->include_template('contentbottomcontent', 'modules/system/roles_administration.tpl');
    } // function

    /**
     * Handle custom properties
     *
     * @throws \isys_exception_auth
     * @throws \isys_exception_general
     */
    public function handle_custom_properties()
    {
        global $index_includes;

        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMPROPERTIES');

        /**
         * @var $l_dao_relation isys_cmdb_dao_category_g_relation
         * @var $l_dao_contact  isys_cmdb_dao_category_g_contact
         */
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts   = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        // Navmode-Handling
        switch ($l_navmode)
        {
            // EDIT
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
            // SAVE
            case C__NAVMODE__SAVE:
                if (isset($l_posts['data']))
                {
                    $l_dao = new isys_cmdb_dao_custom_property(isys_application::instance()->database);

                    foreach ($l_posts['data'] AS $l_category_const => $l_properties)
                    {
                        foreach ($l_properties AS $l_property_identifier => $l_property_value)
                        {
                            $l_data_section = "";

                            if ($l_property_value || $l_property_value === '')
                            {
                                $l_data_section = [
                                    C__PROPERTY__INFO => [
                                        C__PROPERTY__INFO__TITLE => $l_property_value
                                    ]
                                ];
                            } // if

                            $l_dao->create(
                                [
                                    'cats'     => constant($l_category_const),
                                    'property' => $l_property_identifier,
                                    'data'     => $l_data_section
                                ]
                            );
                        } // foreach
                    } // foreach
                } // if
            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        } // switch

        // Collect data
        $l_category_store = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => [
                'C__CATS__PERSON'
            ],
        ];

        $l_data = [];

        foreach ($l_category_store AS $l_category_type => $l_categories)
        {
            foreach ($l_categories AS $l_category_const)
            {
                $l_category_dao = isys_factory_cmdb_category_dao::get_instance_by_id(
                    $l_category_type,
                    constant($l_category_const),
                    isys_application::instance()->database
                );
                // Check for method
                if (method_exists($l_category_dao, 'custom_properties'))
                {
                    $l_custom_properties = $l_category_dao->get_custom_properties();

                    // Are there any properties
                    if (is_array($l_custom_properties) && count($l_custom_properties))
                    {

                        $l_data[$l_category_const] = [
                            'title' => $l_category_dao->get_category_by_const_as_string($l_category_const),
                            'data'  => $l_custom_properties,
                        ];
                    }
                } // if
            } // foreach
        } // foreach

        // Assign data
        isys_module_request::get_instance()
            ->get_template()
            ->assign('data', $l_data);

        // Set template
        $index_includes['contentbottomcontent'] = "modules/system/custom_properties.tpl";
    } // function

    /**
     * Method for setting the relation weightings of all relations to the default setting.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function set_default_relation_priorities()
    {
        $l_dao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);

        $l_relation_types = $l_dao->get_relation_types_as_array();

        foreach ($l_relation_types as $l_relation_type_id => $l_relation_type)
        {
            echo 'Setting the priority of all relations (of type "' . _L($l_relation_type['title']) . '") to ' . $l_relation_type['weighting'] . '<br />';

            $l_sql = 'UPDATE isys_catg_relation_list
				SET isys_catg_relation_list__isys_weighting__id = ' . $l_dao->convert_sql_id($l_relation_type['weighting']) . '
				WHERE isys_catg_relation_list__isys_relation_type__id = ' . $l_dao->convert_sql_id($l_relation_type_id) . ';';

            if (!($l_dao->update($l_sql) && $l_dao->apply_update()))
            {
                isys_notify::error(isys_application::instance()->database->get_last_error_as_string(), ['sticky' => true]);
            } // if
        } // foreach
    } // function

    /**
     * Method for handline the cache actions.
     *
     * @global  array  $index_includes
     * @global  array  $g_db_system
     * @global  string $g_absdir
     */
    private function handle_cache()
    {
        global $index_includes, $g_db_system, $g_absdir;

        isys_auth_system_tools::instance()
            ->cache(isys_auth::EXECUTE);

        if ($_GET["ajax"])
        {
            switch ($_GET["do"])
            {
                case "db_optimize":

                    echo "<table class=\"listing\">" . "<thead>" . "<tr>" . "<th>Table</th><th>Operation</th><th>Status</th>" . "</tr>" . "</thead>" . "<tbody>";

                    $l_dao = new isys_component_dao(isys_application::instance()->database);
                    $l_ret = $l_dao->retrieve("SHOW TABLES;");
                    while ($l_row = $l_ret->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW))
                    {

                        $l_table      = $l_row[0];
                        $l_optimize   = $l_dao->retrieve("OPTIMIZE TABLE " . $l_table);
                        $l_opt_result = $l_optimize->get_row();

                        echo "<tr>" . "<td>" . $l_opt_result["Table"] . "</td>" . "<td>" . $l_opt_result["Op"] . "</td>" . "<td>" . $l_opt_result["Msg_text"] . "</td>" .
                            "</tr>";
                    }

                    echo '</tbody></table>';
                    break;
                case "db_defrag":

                    echo "<table class=\"listing\">" . "<thead>" . "<tr>" . "<th>Table</th><th>Operation</th><th>Status</th>" . "</tr>" . "</thead>" . "<tbody>";

                    $l_dao = new isys_component_dao(isys_application::instance()->database);
                    $l_ret = $l_dao->retrieve("SHOW FULL TABLES;");
                    while ($l_row = $l_ret->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW))
                    {

                        $l_table = $l_row[0];
                        $l_type  = $l_row[1];

                        if ($l_type == 'VIEW') continue;

                        if ($l_dao->update("ALTER TABLE " . $l_table . " ENGINE = INNODB") && $l_dao->apply_update())
                        {
                            $l_status = "OK";
                        }
                        else
                        {
                            $l_status = "DEFRAG NOT POSSIBLE";
                        }

                        echo "<tr>" . "<td>" . $l_table . "</td>" . "<td>defrag</td>" . "<td>" . $l_status . "</td>" . "</tr>";
                    }

                    echo "</tbody></table>";

                    break;
                case "export":

                    if (!isys_application::instance()->session->is_logged_in()) die("Youre not logged in! Please login first!");

                    $l_mysqldump = $_POST["mysqldump"];

                    if (file_exists($l_mysqldump))
                    {
                        $l_system   = $_POST["system"];
                        $l_mandator = $_POST["mandator"];

                        if ($l_system == "1")
                        {

                            $l_sql = $g_db_system["name"] . "-" . time() . ".sql";

                            $l_args = "--add-drop-table -q --dump-date -c " . "-h" . $g_db_system["host"] . " " . "-u" . $g_db_system["user"] . " " . "-p" .
                                $g_db_system["pass"] . " " . "-P" . $g_db_system["port"] . " " . " --databases  " . $g_db_system["name"] . " > " . $g_absdir . "/temp/" .
                                $l_sql;

                            exec($l_mysqldump . " " . $l_args);

                            echo "System dump saved as <a href=\"temp/" . $l_sql . "\">" . $g_absdir . "/temp/" . $l_sql . "</a><br />";
                        }

                        if ($l_mandator == "1")
                        {

                            $l_sql = isys_application::instance()->database->get_db_name() . "-" . time() . ".sql";

                            $l_args = "--add-drop-table -q --dump-date --comments=false -c " . "-h" . isys_application::instance()->database->get_host() . " " . "-u" .
                                isys_application::instance()->database->get_user() . " " . "-p" . isys_application::instance()->database->get_pass() . " " . "-P" .
                                isys_application::instance()->database->get_port() . " " . " --databases " . isys_application::instance()->database->get_db_name() . " > " .
                                $g_absdir . "/temp/" . isys_application::instance()->database->get_db_name() . "-" . time() . ".sql";

                            exec($l_mysqldump . " " . $l_args);

                            echo "Mandator dump saved as <a href=\"temp/" . $l_sql . "\">" . $g_absdir . "/temp/" . $l_sql . "</a><br />";
                        }

                        echo "<p>Right click -> Save as to store it anywhere.</p>";
                    }
                    else
                    {
                        echo "Mysqldump binary not found under: " . $l_mysqldump . ".<br /> " .
                            "This executable can normally be found inside the bin directory of your MySQL installation.";
                    }

                    break;

                case "db_location":
                    $l_dao = new isys_cmdb_dao_location(isys_application::instance()->database);
                    $l_dao->_location_fix();

                    echo _L("LC__SYSTEM__CALCULATE_LOCATIONS_DONE");
                    echo '<hr class="mb5 mt5" />';
                    break;
                case "db_relation":
                    $l_dao = new isys_cmdb_dao_relation(isys_application::instance()->database);
                    try
                    {
                        // Delete dead relation objects
                        $l_dao->delete_dead_relations();
                        // Regenerate relation objects
                        $l_dao->regenerate_relations();
                        echo _L("LC__SYSTEM__REGENERATE_RELATIONS_SUCCESS");
                    }
                    catch (Exception $e)
                    {
                        echo _L("LC__SYSTEM__REGENERATE_RELATIONS_ERROR");
                    } // try

                    echo '<hr class="mb5 mt5" />';
                    break;
                case "db_properties":
                    $l_dao          = new isys_cmdb_dao_category_property(isys_application::instance()->database);
                    $l_result_array = $l_dao->rebuild_properties();

                    foreach ($l_result_array AS $l_key => $l_value)
                    {

                        if ($l_key == 'missing_classes') continue;

                        echo "<table class=\"listing\">" . "<thead>" . "<tr>" . "<th colspan='2'>" . strtoupper(
                                $l_key
                            ) . "</th>" . "</tr>" . "<tr>" . "<th width='50%'>Class</th><th>Property</th>" . "</tr>" . "</thead>" . "<tbody>";

                        foreach ($l_value AS $l_class => $l_prop_values)
                        {

                            foreach ($l_prop_values AS $l_property)
                            {
                                echo "<tr>" . "<td>" . $l_class . "</td>" . "<td>" . $l_property . "</td>" . "</tr>";
                            }
                        }

                        echo "</tbody></table>";
                    }

                    if (count($l_result_array['missing_classes']) > 0)
                    {
                        echo '<table class=\"listing\"><thead><tr><th>Missing classes</th></tr></thead><tbody>';
                        foreach ($l_result_array['missing_classes'] as $l_value)
                        {
                            echo '<tr><td>' . $l_value . '</td></tr>';
                        } // foreach
                        echo '</tbody></table>';
                    } // if

                    echo '<hr class="mb5 mt5" />';
                    break;

                case 'db_cleanup_objects':
                    $l_count = 0;

                    try
                    {
                        switch ($_GET['param'])
                        {
                            default:
                            case C__RECORD_STATUS__BIRTH:
                            case C__RECORD_STATUS__ARCHIVED:
                            case C__RECORD_STATUS__DELETED:
                                // Method for cleaning up the objects.
                                $l_count = $this->cleanup_objects($_GET['param']);
                                break;

                            case 'all':
                                $l_count = $this->cleanup_objects(C__RECORD_STATUS__BIRTH) + $this->cleanup_objects(C__RECORD_STATUS__ARCHIVED) + $this->cleanup_objects(
                                        C__RECORD_STATUS__DELETED
                                    );
                        } // switch
                    }
                    catch (Exception $e)
                    {
                        echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                    } // try

                    echo sprintf(_L("LC__SYSTEM__REMOVE_OBJECTS_DONE"), $l_count);

                    echo '<hr class="mb5 mt5" />';
                    break;

                case 'db_list_objects':
                    $l_objects = [];

                    try
                    {
                        switch ($_GET['param'])
                        {
                            default:
                            case C__RECORD_STATUS__BIRTH:
                            case C__RECORD_STATUS__ARCHIVED:
                            case C__RECORD_STATUS__DELETED:
                                // Method for cleaning up the objects.
                                $l_objects = $this->list_objects($_GET['param']);
                                break;

                            case 'all':
                                $l_objects = $this->list_objects(C__RECORD_STATUS__BIRTH) + $this->list_objects(C__RECORD_STATUS__ARCHIVED) + $this->list_objects(
                                        C__RECORD_STATUS__DELETED
                                    );
                        } // switch
                    }
                    catch (Exception $e)
                    {
                        echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                    } // try

                    if (count($l_objects) > 0)
                    {
                        echo '<ul><li>' . implode('</li><li>', $l_objects) . '</li></ul>';
                    }
                    else
                    {
                        echo isys_tenantsettings::get('gui.empty_value', '-');
                    }

                    break;

                case 'db_cleanup_categories':
                    $l_count = 0;

                    try
                    {
                        switch ($_GET['param'])
                        {
                            default:
                            case C__RECORD_STATUS__BIRTH:
                            case C__RECORD_STATUS__ARCHIVED:
                            case C__RECORD_STATUS__DELETED:
                                // Method for cleaning up the category content.
                                $l_count = $this->cleanup_categories($_GET['param']);
                                break;

                            case 'all':
                                $l_count = $this->cleanup_categories(C__RECORD_STATUS__BIRTH) + $this->cleanup_categories(
                                        C__RECORD_STATUS__ARCHIVED
                                    ) + $this->cleanup_categories(C__RECORD_STATUS__DELETED);
                        } // switch
                    }
                    catch (Exception $e)
                    {
                        echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                    } // try

                    echo sprintf(_L("LC__SYSTEM__CLEANUP_OBJECTS_DONE_CATEGORIES"), $l_count);

                    break;

                case 'cleanup_other':
                    $this->cleanup_other($_GET['param']);
                    break;

                case 'db_cleanup_cat_assignments':
                    $this->cleanup_category_assignments();
                    break;

                case 'db_cleanup_duplicate_sv_entries':
                    $this->cleanup_duplicate_single_value_entries();
                    break;

                case 'db_cleanup_unassigned_relations':
                    $this->cleanup_unassigned_relations();
                    break;

                case 'db_renew_relation_titles':
                    $this->renew_relation_titles();
                    break;

                case 'db_refill_empty_sysids':
                    $this->refill_empty_sysids();
                    break;

                case 'db_set_default_relation_priorities':
                    $this->set_default_relation_priorities();
                    break;

                case 'cache_system':
                    global $g_dirs;
                    $l_deleted = $l_undeleted = '';
                    isys_glob_delete_recursive($g_dirs["temp"], $l_deleted, $l_undeleted);
                    echo _L('LC__SETTINGS__SYSTEM__FLUSH_CACHE_MESSAGE', count($l_deleted) . ' System');

                    /**
                     * Removing isys_cache values
                     */
                    isys_cache::keyvalue()
                        ->flush();

                    isys_component_signalcollection::get_instance()
                        ->emit('system.afterFlushSystemCache');

                    break;
                case 'cache_auth':
                    // Clear all found "auth-*" cache-files.
                    $l_cache_files = isys_caching::find('auth-*');

                    array_map(
                        function ($l_cache)
                        {
                            if (is_object($l_cache) && method_exists($l_cache, 'clear')) $l_cache->clear();
                        },
                        $l_cache_files
                    );

                    // Invalidate auth cache
                    isys_cache_keyvalue::keyvalue()
                        ->ns_invalidate('auth');

                    echo _L('LC__SETTINGS__SYSTEM__FLUSH_CACHE_MESSAGE', count($l_cache_files));
                    break;
            } // switch

            die;
        } // if

        $g_windows = false;

        if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
        {
            $g_windows = true;
        } // if

        if ($g_windows)
        {
            if (file_exists("c:\\programme\\mysql5\\bin\\mysqldump.exe"))
            {
                $l_mysqldump = "c:\\programme\\mysql5\\bin\\mysqldump.exe";
            }
            else
            {
                if (file_exists("c:\\programme\\mysql\\bin\\mysqldump.exe"))
                {
                    $l_mysqldump = "c:\\programme\\mysql\\bin\\mysqldump.exe";
                }
                else
                {
                    if (file_exists("c:\\programme\\mysql5.0\\bin\\mysqldump.exe"))
                    {
                        $l_mysqldump = "c:\\programme\\mysql5.0\\bin\\mysqldump.exe";
                    }
                    else
                    {
                        if (file_exists("c:\\windows\\system32\\mysqldump.exe"))
                        {
                            $l_mysqldump = "c:\\windows\\system32\\mysqldump.exe";
                        }
                        else
                        {
                            $l_mysqldump = "mysqldump.exe";
                        }
                    }
                }
            } // if
        }
        else
        {
            $l_which = shell_exec("which mysqldump");
            if (strstr($l_which, "mysqldump"))
            {
                $l_mysqldump = $l_which;
            }
            else
            {
                if (file_exists("/usr/bin/mysqldump"))
                {
                    $l_mysqldump = "/usr/bin/mysqldump";
                }
                else
                {
                    if (file_exists("/usr/local/mysql/bin/mysqldump"))
                    {
                        $l_mysqldump = "/usr/local/mysql/bin/mysqldump";
                    }
                    else
                    {
                        $l_mysqldump = "mysqldump";
                    }
                } // if
            } // if
        } // if

        // Cache buttons.
        $l_cache_buttons = [
            'LC__SETTINGS__SYSTEM__FLUSH_ALL_CACHE'  => [
                'onclick' => 'window.flush_cache(true);',
                'style'   => 'background-color:#eee;',
                'css'     => 'mb15'
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_SYS_CACHE'  => [
                'onclick' => "window.flush_database('cache_system');"
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_TPL_CACHE'  => [
                'onclick' => "window.flush_cache('IDOIT_DELETE_TEMPLATES_C');"
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_AUTH_CACHE' => [
                'onclick' => "window.flush_database('cache_auth');"
            ]
        ];

        if (defined('C__MODULE__PRO'))
        {
            $l_cache_buttons['LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH'] = [
                'onclick' => "window.flush_validation_cache();"
            ];
        } // if

        // Database buttons.
        $l_database_buttons = [
            'LC__SYSTEM__TABLE_EVERY_ACTION'                     => [
                'onclick' => 'window.flush_database(true);',
                'style'   => 'background-color:#eee;',
                'css'     => 'mb15'
            ],
            'LC__SYSTEM__TABLE_OPTIMIZATION'                     => [
                'onclick' => "window.flush_database('db_optimize');"
            ],
            'LC__SYSTEM__TABLE_DEFRAG'                           => [
                'onclick' => "window.flush_database('db_defrag');"
            ],
            'LC__SYSTEM__CALCULATE_LOCATIONS'                    => [
                'onclick' => "window.flush_database('db_location');"
            ],
            'LC__SYSTEM__CLEANUP_CATEGORY_ASSIGNMENTS'           => [
                'onclick' => "window.flush_database('db_cleanup_cat_assignments');"
            ],
            'LC__SYSTEM__RENEW_PROPERTIES'                       => [
                'onclick' => "window.flush_database('db_properties');"
            ],
            'LC__SYSTEM__CLEANUP_DUPLICATE_SINGLE_VALUE_ENTRIES' => [
                'onclick' => "window.flush_database('db_cleanup_duplicate_sv_entries');"
            ],
            'LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS'    => [
                'onclick' => "window.flush_database('db_cleanup_unassigned_relations');"
            ],
            'LC__SYSTEM__RENEW_RELATION_TITLES'                  => [
                'onclick' => "window.flush_database('db_renew_relation_titles')"
            ],
            'LC__SYSTEM__REFILL_EMPTY_SYSIDS'                    => [
                'onclick' => "window.flush_database('db_refill_empty_sysids')"
            ],
            'LC__SYSTEM__RESET_RELATION_PRIORITIES'              => [
                'onclick' => "window.flush_database('db_set_default_relation_priorities')"
            ],
            'LC__SYSTEM__REGENERATE_RELATIONS'                   => [
                'onclick' => "if(confirm('" . _L("LC__SYSTEM__REGENERATE_RELATIONS_CONFIRMATION") . "')){window.flush_database('db_relation');}"
            ]
        ];

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_born     = $l_dao->retrieve(
            'SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ' AND isys_obj__undeletable = 0;'
        )
            ->get_row();
        $l_archived = $l_dao->retrieve(
            'SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ' AND isys_obj__undeletable = 0;'
        )
            ->get_row();
        $l_deleted  = $l_dao->retrieve(
            'SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__DELETED) . ' AND isys_obj__undeletable = 0;'
        )
            ->get_row();
        $l_all      = $l_born['count'] + $l_archived['count'] + $l_deleted['count'];

        // The aliases are used to display translated headings.
        $l_query = 'SELECT isys_obj__id AS \'ID\', isys_obj_type__title AS \'LC__REPORT__FORM__OBJECT_TYPE###1\', isys_obj__title AS \'LC__UNIVERSAL__TITLE###1\', ' .
            'isys_obj__created AS \'isys_cmdb_dao_category_g_global::dynamic_property_callback_created::isys_obj__created::LC__TASK__DETAIL__WORKORDER__CREATION_DATE\', ' .
            'isys_obj__updated AS \'isys_cmdb_dao_category_g_global::dynamic_property_callback_changed::isys_obj__updated::LC__CMDB__LAST_CHANGE\' ' . 'FROM isys_obj ' .
            'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE isys_obj__undeletable = 0 AND isys_obj__status = ';

        $l_object_buttons = [
            'LC__SYSTEM__TABLE_EVERY_ACTION'      => [
                'onclick' => "window.flush_objects('all', '" . isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_OBJECTS__BIRTH_ARCHIVED_DELETED', $l_all)) . "');",
                'style'   => 'background-color:#eee;',
                'css'     => 'btn-block mb15'
            ],
            'LC__SYSTEM__REMOVE_BIRTH_OBJECTS'    => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_OBJECTS__BIRTH', $l_born['count'])) .
                    "');",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:90%;'
            ],
            'LC__SYSTEM__REMOVE_ARCHIVED_OBJECTS' => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__ARCHIVED . ", '" .
                    isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_OBJECTS__ARCHIVED', $l_archived['count'])) . "');",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:90%;'
            ],
            'LC__SYSTEM__REMOVE_DELETED_OBJECTS'  => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__DELETED . ", '" .
                    isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_OBJECTS__DELETED', $l_deleted['count'])) . "');",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__DELETED) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:90%;'
            ]
        ];

        if (!defined('C__MODULE__PRO'))
        {
            unset($l_object_buttons['LC__SYSTEM__REMOVE_BIRTH_OBJECTS']['query'], $l_object_buttons['LC__SYSTEM__REMOVE_ARCHIVED_OBJECTS']['query'], $l_object_buttons['LC__SYSTEM__REMOVE_DELETED_OBJECTS']['query'], $l_object_buttons['LC__SYSTEM__REMOVE_BIRTH_OBJECTS']['style'], $l_object_buttons['LC__SYSTEM__REMOVE_ARCHIVED_OBJECTS']['style'], $l_object_buttons['LC__SYSTEM__REMOVE_DELETED_OBJECTS']['style']);

            $l_object_buttons['LC__SYSTEM__REMOVE_BIRTH_OBJECTS']['css']    = 'btn-block';
            $l_object_buttons['LC__SYSTEM__REMOVE_ARCHIVED_OBJECTS']['css'] = 'btn-block';
            $l_object_buttons['LC__SYSTEM__REMOVE_DELETED_OBJECTS']['css']  = 'btn-block';
        }

        $l_category_buttons = [
            'LC__SYSTEM__TABLE_EVERY_ACTION'         => [
                'onclick' => "window.flush_categories('all', '" . isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_CATEGORIES__BIRTH_ARCHIVED_DELETED')) . "');",
                'style'   => 'background-color:#eee;',
                'css'     => 'mb15'
            ],
            'LC__SYSTEM__REMOVE_BIRTH_CATEGORIES'    => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_CATEGORIES__BIRTH')) . "');",
            ],
            'LC__SYSTEM__REMOVE_ARCHIVED_CATEGORIES' => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__ARCHIVED . ", '" . isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_CATEGORIES__ARCHIVED')) . "');",
            ],
            'LC__SYSTEM__REMOVE_DELETED_CATEGORIES'  => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__DELETED . ", '" . isys_glob_htmlentities(_L('LC__SYSTEM__REMOVE_CATEGORIES__DELETED')) . "');",
            ]
        ];

        $l_other_buttons = [];

        if (isys_module_manager::instance()
            ->is_active('check_mk')
        )
        {
            $l_other_buttons['LC__SYSTEM__TRUNCATE_EXPORTED_CHECK_MK_TAGS'] = [
                'onclick' => "window.flush_other('check_mk_exported_tags', '" . isys_glob_htmlentities(_L('LC__SYSTEM__TRUNCATE_EXPORTED_CHECK_MK_TAGS_CONFIRM')) . "');"
            ];
        } // if

        $this->m_userrequest->get_template()
            ->assign('report_sql_path', isys_application::instance()->app_path . '/src/classes/modules/report/templates/report.js')
            ->assign('mysqldump', $l_mysqldump)
            ->assign('cache_buttons', $l_cache_buttons)
            ->assign('database_buttons', $l_database_buttons)
            ->assign('object_buttons', $l_object_buttons)
            ->assign('category_buttons', $l_category_buttons)
            ->assign('other_buttons', $l_other_buttons);

        $index_includes['contentbottomheader']  = '';
        $index_includes['contentbottomcontent'] = 'content/sys_cache.tpl';
    } // function
} // class
