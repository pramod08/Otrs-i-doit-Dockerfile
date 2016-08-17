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
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_quicklaunch extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Init method.
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . DS . 'templates' . DS . 'quicklaunch.tpl';

        return parent::init();
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        $l_function_list = $l_configuration_list = [];

        if (isys_auth_system::instance()
            ->is_allowed_to(isys_auth::VIEW, 'USERSETTINGS')
        )
        {
            $l_configuration_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__USER_SETTINGS . '&' . C__GET__SETTINGS_PAGE . '=1'] = _L('LC__WIDGET__QUICKLAUNCH_USER_SETTINGS');
        } // if

        // Check for module-constants, to display these in the frontend.
        if (defined('C__MODULE__IMPORT') && C__MODULE__IMPORT > 0 && isys_auth_import::instance()
                ->is_allowed_to(isys_auth::VIEW, 'IMPORT')
        )
        {
            $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__IMPORT] = _L('LC__MODULE__IMPORT');
        } // if

        if (defined('C__MODULE__EXPORT') && C__MODULE__EXPORT > 0 && isys_auth_export::instance()
                ->is_allowed_to(isys_auth::VIEW, 'EXPORT')
        )
        {
            $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__EXPORT] = _L('LC__MODULE__EXPORT');
        } // if

        if (defined('C__CMDB__VIEW__MULTIEDIT') && C__CMDB__VIEW__MULTIEDIT > 0 && isys_auth_cmdb::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'MULTIEDIT')
        )
        {
            $l_function_list['?' . C__CMDB__GET__VIEWMODE . '=' . C__CMDB__VIEW__MULTIEDIT] = _L('LC__MULTIEDIT__MULTIEDIT');
        } // if

        if (defined('C__MODULE__TEMPLATES') && C__MODULE__TEMPLATES > 0 && isys_auth_templates::instance()
                ->is_allowed_to(isys_auth::VIEW, 'TEMPLATES')
        )
        {
            $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__TEMPLATES] = _L('LC__MODULE__TEMPLATES');
        } // if

        if (defined('C__MODULE__LOGBOOK') && C__MODULE__LOGBOOK > 0 && isys_auth_logbook::instance()
                ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK')
        )
        {
            $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__LOGBOOK] = _L('LC__MODULE__LOGBOOK__TITLE');
        } // if

        if (defined('C__MODULE__DIALOG_ADMIN') && C__MODULE__DIALOG_ADMIN > 0)
        {
            if (isys_auth_dialog_admin::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'TABLE') || isys_auth_dialog_admin::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'CUSTOM')
            )
            {
                $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__DIALOG_ADMIN] = _L('LC__DIALOG_ADMIN');
            } // if
        } // if

        if (defined('C__MODULE__SYSTEM') && C__MODULE__SYSTEM > 0 && isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS')
        )
        {
            if (isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW')
            )
            {
                $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&what=sysoverview'] = _L('LC__SYSTEM__OVERVIEW');
            } // if

            if (isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE')
            )
            {
                $l_function_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&what=cache'] = _L('LC__SETTINGS__SYSTEM__FLUSH_SYS_CACHE');
            } // if

            $l_configuration_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM] = _L('LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION');
        } // if

        if (defined('C__MODULE__LDAP') && C__MODULE__LDAP > 0)
        {
            $l_ldap_auth = isys_auth_system::instance();

            if ($l_ldap_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG) || $l_ldap_auth->is_allowed_to(
                    isys_auth::SUPERVISOR,
                    'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES
                )
            )
            {
                $l_configuration_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__LDAP] = _L('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP');
            } // if
        } // if

        if (defined('C__MODULE__NAGIOS') && C__MODULE__NAGIOS > 0 && isys_auth_nagios::instance()
                ->has_any_rights_in_module()
        )
        {
            $l_configuration_list['?' . C__GET__MODULE_ID . '=' . C__MODULE__NAGIOS] = _L('LC__CMDB__TREE__SYSTEM__INTERFACE__NAGIOS');
        } // if

        return $this->m_tpl->assign('function_list', $l_function_list)
            ->assign('configuration_list', $l_configuration_list)
            ->assign(
                'allow_update',
                isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'SYSTEMTOOLS/IDOITUPDATE')
            )
            ->fetch($this->m_tpl_file);
    } // function
} // class