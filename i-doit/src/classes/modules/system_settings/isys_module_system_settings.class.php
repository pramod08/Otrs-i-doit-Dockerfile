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
 * System settings.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_system_settings extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    const TENANT_WIDE            = 'Tenant-wide';
    const SYSTEM_WIDE            = 'System-wide';
    const USER                   = 'User';

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {

    } // function

    /**
     * @return void
     */
    public function start()
    {
        global $index_includes, $g_comp_database;

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_gets   = isys_module_request::get_instance()
            ->get_gets();
        $l_posts  = isys_module_request::get_instance()
            ->get_posts();

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);

        if (isys_glob_get_param('navMode') == C__NAVMODE__EDIT)
        {
            $l_navbar->set_selected(true, C__NAVBAR_BUTTON__EDIT);
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }

        /**
         * @desc handle navmode actions
         */
        if (isset($l_posts['navMode']))
        {
            switch ($l_posts['navMode'])
            {
                case C__NAVMODE__SAVE:

                    switch ($_GET[C__GET__SETTINGS_PAGE])
                    {
                        case C__SETTINGS_PAGE__CMDB_STATUS:

                            isys_tenantsettings::set('import.hinventory.default_status', $l_posts['C__SETTING__STATUS__IMPORT']);
                            isys_tenantsettings::set('system.mydoit.show_filter', $l_posts['C__SETTING__STATUS__SHOW_FILTER']);

                            /* Save status */
                            $l_status_dao = new isys_cmdb_dao_status($g_comp_database);
                            {

                                if ($_POST["delStatus"] != '')
                                {
                                    foreach (explode(",", $_POST["delStatus"]) as $l_delStatus)
                                    {
                                        $l_status_dao->delete_status($l_delStatus);
                                    }
                                }

                                if (is_array($_POST["status_title"]))
                                {
                                    foreach ($_POST["status_title"] as $l_id => $l_title)
                                    {
                                        $l_const = $_POST["status_const"][$l_id];
                                        $l_color = $_POST["status_color"][$l_id];

                                        $l_status_dao->save($l_id, $l_const, $l_title, $l_color);
                                    }
                                }

                                if (is_array($_POST["new_status_title"]))
                                {
                                    foreach ($_POST["new_status_title"] as $l_id => $l_title)
                                    {
                                        $l_const = $_POST["new_status_const"][$l_id];
                                        $l_color = $_POST["new_status_color"][$l_id];

                                        $l_status_dao->create($l_const, $l_title, $l_color);
                                    }
                                }
                            }

                            isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));

                            break;
                        case C__SETTINGS_PAGE__SYSTEM:

                            // Non-expert settings
                            if (isset($_POST['settings'][self::SYSTEM_WIDE]) && is_array($_POST['settings'][self::SYSTEM_WIDE]))
                            {
                                foreach ($_POST['settings'][self::SYSTEM_WIDE] as $l_key => $l_value)
                                {
                                    if ($_POST['remove_settings'][self::SYSTEM_WIDE][$l_key] === '1')
                                    {
                                        isys_settings::remove($l_key);
                                    }
                                    else isys_settings::set($l_key, $l_value);
                                } // foreach
                            } // if

                            if (isset($_POST['settings'][self::TENANT_WIDE]) && is_array($_POST['settings'][self::TENANT_WIDE]))
                            {
                                foreach ($_POST['settings'][self::TENANT_WIDE] as $l_key => $l_value)
                                {
                                    if ($_POST['remove_settings'][self::TENANT_WIDE][$l_key] === '1')
                                    {
                                        isys_tenantsettings::remove($l_key);
                                    }
                                    else isys_tenantsettings::set($l_key, $l_value);
                                } // foreach
                            } // if

                            if (isset($_POST['settings'][self::USER]) && is_array($_POST['settings'][self::USER]))
                            {
                                foreach ($_POST['settings'][self::USER] as $l_key => $l_value)
                                {
                                    if ($_POST['remove_settings'][self::USER][$l_key] === '1')
                                    {
                                        isys_usersettings::remove($l_key);
                                    }
                                    else isys_usersettings::set($l_key, $l_value, false);
                                } // foreach
                            } // if

                            // Expert Settings
                            if (isset($_POST['custom_settings']))
                            {
                                $l_callmap = [
                                    self::TENANT_WIDE => 'isys_tenantsettings',
                                    self::USER        => 'isys_usersettings',
                                    self::SYSTEM_WIDE => 'isys_settings'
                                ];

                                // Custom
                                foreach ($_POST['custom_settings']['key'] as $l_index => $l_key)
                                {
                                    if ($l_key && $l_key != '')
                                    {
                                        $l_value = isset($_POST['custom_settings']['value'][$l_index]) ? $_POST['custom_settings']['value'][$l_index] : null;
                                        $l_type  = $_POST['custom_settings']['type'][$l_index] ?: null;

                                        if ($l_type !== null)
                                        {
                                            if (isset($l_callmap[$l_type]))
                                            {
                                                if ($l_value !== null)
                                                {
                                                    call_user_func_array(
                                                        [
                                                            $l_callmap[$l_type],
                                                            'set'
                                                        ],
                                                        [
                                                            $l_key,
                                                            $l_value
                                                        ]
                                                    );
                                                }
                                            }
                                        }

                                    } // if
                                } // foreach
                            } // if

                            isys_notify::success(_L('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                            break;
                    } // switch

                    break;

                case C__NAVMODE__DELETE:
                    break;

                case C__NAVMODE__NEW:
                    break;
            }
        }

        //all nodes for the system settings
        switch ($l_gets[C__GET__SETTINGS_PAGE])
        {
            case C__SETTINGS_PAGE__CMDB_STATUS:
                $this->process_status_settings();

                $index_includes['contentbottomcontent'] = 'content/bottom/content/module__settings__status.tpl';

                break;
            case C__SETTINGS_PAGE__SYSTEM:
                $this->process_settings();
                break;
        }
    } // function

    /**
     * @param isys_module_request $p_req
     *
     * @return Boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    } // function

    /**
     * CMDB Status
     */
    private function process_status_settings()
    {
        global $g_comp_database, $g_comp_template, $g_comp_template_language_manager;

        $l_status = $l_status_complete = [];

        // Check rights
        isys_auth_system_globals::instance()
            ->cmdbstatus(isys_auth::VIEW);

        $l_dao_cmdb   = new isys_cmdb_dao_status($g_comp_database);
        $l_status_dao = $l_dao_cmdb->get_cmdb_status();

        while ($l_row = $l_status_dao->get_row())
        {
            $l_status[$l_row["isys_cmdb_status__id"]] = $g_comp_template_language_manager->get($l_row["isys_cmdb_status__title"]);

            if ($l_row["isys_cmdb_status__editable"] && $l_row["isys_cmdb_status__id"] != C__CMDB_STATUS__IN_OPERATION &&
                $l_row["isys_cmdb_status__id"] != C__CMDB_STATUS__INOPERATIVE
            ) $l_status_complete[] = $l_row;
        }

        $l_rules['C__SETTING__STATUS__IMPORT']['p_arData']        = serialize($l_status);
        $l_rules['C__SETTING__STATUS__IMPORT']['p_strSelectedID'] = isys_tenantsettings::get('import.hinventory.default_status', C__CMDB_STATUS__IN_OPERATION);

        $l_rules['C__SETTING__STATUS__SHOW_FILTER']['p_arData']        = serialize(get_smarty_arr_YES_NO());
        $l_rules['C__SETTING__STATUS__SHOW_FILTER']['p_strSelectedID'] = isys_tenantsettings::get('system.mydoit.show_filter', 1);

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_navbar->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE);

        if (isys_glob_get_param(C__GET__NAVMODE) != C__NAVMODE__EDIT)
        {
            $l_navbar->set_active(
                isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/CMDBSTATUS'),
                C__NAVBAR_BUTTON__EDIT
            )
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }
        else
        {
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
        }

        $g_comp_template->assign("cmdb_status", $l_status_complete);
        $g_comp_template->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }

    /**
     * Process generic settings (config.inc.php)
     */
    private function process_settings()
    {
        global $g_comp_session, $g_comp_database_system, $index_includes;

        // Check rights
        isys_auth_system::instance()
            ->check(isys_auth::SUPERVISOR, 'SYSTEM');

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_save_mode('quick');

        $l_settings   = isys_settings::get();
        $l_definition = isys_settings::get_definition();
        ksort($l_definition);

        $l_tenant_settings   = isys_tenantsettings::get();
        $l_tenant_definition = isys_tenantsettings::get_definition();
        ksort($l_tenant_settings);

        $l_dao_mandator = new isys_component_dao_mandator($g_comp_database_system);
        $l_mandators    = $l_dao_mandator->get_mandator();
        while ($l_row = $l_mandators->get_row())
        {
            $l_definition['Single Sign On']['session.sso.mandator-id']['options'][$l_row['isys_mandator__id']] = $l_row['isys_mandator__title'];
        }

        isys_component_template::instance()
            ->assign("bShowCommentary", false)
            ->assign('tenantTab', _L('LC__SYSTEM_SETTINGS__TENANT', $g_comp_session->get_mandator_name()))
            ->assign('content_title', _L('LC__MODULE__SYSTEM_SETTINGS__TITLE'));

        if (isset($_GET['expert']))
        {
            $l_user_settings = isys_usersettings::get();

            $l_settingsCombined = [];
            foreach ($l_settings as $l_key => $l_value)
            {
                if (is_scalar($l_value))
                {
                    $l_settingsCombined[self::SYSTEM_WIDE][$l_key] = $l_value;
                }
            }
            foreach ($l_tenant_settings as $l_key => $l_value)
            {
                if (is_scalar($l_value))
                {
                    $l_settingsCombined[self::TENANT_WIDE][$l_key] = $l_value;
                }
            }
            foreach ($l_user_settings as $l_key => $l_value)
            {
                if (is_scalar($l_value))
                {
                    $l_settingsCombined[self::USER][$l_key] = $l_value;
                }
            }

            isys_component_template::instance()
                ->assign('expertSettings', true)
                ->assign('content_title', _L('LC__SYSTEM_SETTINGS__EXPERT_SETTINGS'))
                ->assign('settings', $l_settingsCombined);

            $index_includes['contentbottomcontent'] = 'modules/system_settings/expert.tpl';
        }
        else
        {
            isys_component_template::instance()
                ->assign('systemWideKey', self::SYSTEM_WIDE)
                ->assign('tenantWideKey', self::TENANT_WIDE)
                ->assign('definition', $l_definition)
                ->assign('tenant_definition', $l_tenant_definition)
                ->assign('settings', $l_settings)
                ->assign('tenant_settings', $l_tenant_settings);

            $index_includes['contentbottomcontent'] = 'modules/system_settings/index.tpl';
        }

        return true;
    } // function

    /**
     * LF: This method MAY be called magically...?
     * If this is still around by i-doit 1.8: Remove it!
     *
     * @deprecated
     * @throws      isys_exception_missing_function
     */
    private function save()
    {
        throw new isys_exception_missing_function('Registry is no longer supported, please use isys_settings, isys_tenantsettings or isys_usersettings instead.');
    } // function
}