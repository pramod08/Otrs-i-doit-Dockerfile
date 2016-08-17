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
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_system extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_system
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_system
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null)
        {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        } // if

        if (self::$m_instance === null)
        {
            self::$m_instance = new self;
        } // if

        return self::$m_instance;
    } // function

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        $l_return = [
            'system'            => [ // This is used for the "?modules" page. Needs supervisor right.
                                     'title' => 'System',
                                     'type'  => 'boolean'
            ],
            'workflow'          => [
                'title' => _L('LC__AUTH_GUI__WORKFLOWS_CONDITION'),
                'type'  => 'workflow'
            ],
            'ocs'               => [
                'title' => _L('LC__AUTH_GUI__OCS_CONDITION'),
                'type'  => 'ocs'
            ],
            'jsonrpcapi'        => [
                'title' => _L('LC__AUTH_GUI__JSONRPCAPI_CONDITION'),
                'type'  => 'jsonrpcapi'
            ],
            'systemtools'       => [
                'title' => _L('LC__AUTH_GUI__SYSTEMTOOLS_CONDITION'),
                'type'  => 'systemtools'
            ],
            'globalsettings'    => [
                'title' => _L('LC__AUTH_GUI__GLOBALSETTINGS_CONDITION'),
                'type'  => 'globalsettings'
            ],
            'licencesettings'   => [
                'title' => _L('LC__AUTH_GUI__LICENCESETTINGS_CONDITION'),
                'type'  => 'licencesettings'
            ],
            'controllerhandler' => [
                'title'    => _L('LC__AUTH_GUI__CONTROLLER_HANDLER'),
                'type'     => 'controllerhandler',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ],
                'defaults' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'qr_config'         => [
                'title'    => _L('LC__AUTH_GUI__QR_CODE_CONFIGURATION'),
                'type'     => 'qr_config',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ],
                'defaults' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ]
        ];

        if (defined('C__MODULE__LOGINVENTORY'))
        {
            $l_return['loginventory'] = [
                'title' => _L('LC__AUTH_GUI__LOGINVENTORY_CONDITION'),
                'type'  => 'loginventory'
            ];
        } // if

        if (defined('C__MODULE__JDISC'))
        {
            $l_return['jdisc'] = [
                'title' => _L('LC__AUTH_GUI__JDISC_CONDITION'),
                'type'  => 'jdisc'
            ];
        } // if

        if (defined('C__MODULE__LDAP'))
        {
            $l_return['ldap'] = [
                'title' => _L('LC__AUTH_GUI__LDAP_CONDITION'),
                'type'  => 'ldap'
            ];
        } // if

        if (defined('C__MODULE__TTS'))
        {
            $l_return['tts'] = [
                'title' => _L('LC__AUTH_GUI__TTS_CONDITION'),
                'type'  => 'tts'
            ];
        } // if

        return $l_return;
    } // function

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return C__MODULE__SYSTEM;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__SYSTEM__TITLE";
    } // function

    /**
     *
     * @param   integer $p_right
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function system($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        // Check for wildchars.
        if (isset($this->m_paths['system']))
        {
            if (array_key_exists(isys_auth::EMPTY_ID_PARAM, $this->m_paths['system']) && in_array(
                    $p_right,
                    $this->m_paths['system'][isys_auth::EMPTY_ID_PARAM]
                )
            )
            {
                return true;
            } // if
        } // if
        throw new isys_exception_auth(_L('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_SYSTEM'));
    } // function

    /**
     * Determines the rights for the workflows.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function workflow($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights($p_right, 'workflow', $p_type, new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_CMDB_EXPLORER')));
    } // function

    /**
     * Determines the rights for loginventory.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function loginventory($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights($p_right, 'loginventory', $p_type, new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LOGINVENTORY')));
    } // function

    /**
     * Determines the rights for jdisc.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function jdisc($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights($p_right, 'jdisc', $p_type, new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_JDISC')));
    } // function

    /**
     * Determines the rights for ocs.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function ocs($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights(
            $p_right,
            'ocs',
            $p_type,
            new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_OCS'))
        );
    } // function

    /**
     * Determines the rights for ldap.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function ldap($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights(
            $p_right,
            'ldap',
            $p_type,
            new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LDAP'))
        );
    } // function

    /**
     * Determines the rights for TroubleTicket-System (tts).
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function tts($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights(
            $p_right,
            'tts',
            $p_type,
            new isys_exception_auth(_L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_TTS'))
        );
    } // function

    /**
     * Determines the rights for JSON-RPC Api.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function jsonrpcapi($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        switch ($p_type)
        {
            case 'api':
                $l_exception = _L(
                    'LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_JSONRPCAPI_API',
                    $this->get_right_name(isys_auth::EXECUTE)
                );
                break;
            default:
                $l_exception = _L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_JSONRPCAPI');
                break;
        } // switch

        return $this->check_module_rights($p_right, 'jsonrpcapi', $p_type, new isys_exception_auth($l_exception));
    } // function

    /**
     * Determines the rights for Systemtools.
     *
     * @param   integer $p_right
     * @param   mixed   $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function systemtools($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        switch ($p_param)
        {
            case 'cache':
                $l_exception = _L('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [_L('LC__SYSTEM__CACHE')]);
                break;
            case 'modulemanager':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__TOOLS__MODULE_MANAGER')]
                );
                break;
            case 'validation':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__TOOLS__VALIDATION')]
                );
                break;
            case 'idoitupdate':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE')]
                );
                break;
            default:
                $l_exception = _L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_SYSTEMTOOLS');
                break;
        } // switch

        return $this->check_module_rights($p_right, 'systemtools', $p_param, new isys_exception_auth($l_exception));
    } // function

    /**
     * Determines the rights for the global settings
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function globalsettings($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        switch ($p_param)
        {
            case 'systemsetting':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__SETTINGS__SYSTEM')]
                );
                break;
            case 'customfields':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES')]
                );
                break;
            case 'qcw':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__CMDB_CONFIGURATION__QOC')]
                );
                break;
            case 'cmdbstatus':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS')]
                );
                break;
            case 'relationshiptypes':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES')]
                );
                break;
            case 'rolesadministration':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__MODULE__SYSTEM__ROLES_ADMINISTRATION')]
                );
                break;
            default:
                $l_exception = _L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_GLOBALSETTINGS');
                break;
        } // switch

        return $this->check_module_rights($p_right, 'globalsettings', $p_param, new isys_exception_auth($l_exception));
    } // function

    /**
     * Determines the rights for the licence administration.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function licencesettings($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        switch ($p_param)
        {
            case 'installation':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__UNIVERSAL__LICENE_INSTALLATION')]
                );
                break;
            case 'overview':
                $l_exception = _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT',
                    [_L('LC__UNIVERSAL__LICENE_OVERVIEW')]
                );
                break;
            default:
                $l_exception = _L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LICENCESETTINGS');
                break;
        } // switch

        return $this->check_module_rights($p_right, 'licencesettings', $p_param, new isys_exception_auth($l_exception));
    } // function

    /**
     * Determines the rights for all controller handlers.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function controllerhandler($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights(
            $p_right,
            'controllerhandler',
            $p_param,
            new isys_exception_auth('No rights to execute controller handler ' . $p_param . '.')
        );
    } // function

    /**
     * Determines the rights for the QR code configuration.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function qr_config($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        switch ($p_param)
        {
            default:
            case 'global':
                $l_exception = _L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_GLOBAL_QRCODE_CONFIG');
                break;
            case 'objtype':
                $l_exception = _L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_OBJTYPE_QRCODE_CONFIG');
                break;
        } // switch

        return $this->generic_right($p_right, 'qr_config', $p_param, new isys_exception_auth($l_exception));
    } // function

    /**
     * Method for retrieving the "parameter" in the configuration GUI. Gets called generically by "ajax()" method.
     *
     * @see     isys_module_auth->ajax_retrieve_parameter();
     *
     * @param   string  $p_method
     * @param   string  $p_param
     * @param   integer $p_counter
     * @param   boolean $p_editmode
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function retrieve_parameter($p_method, $p_param, $p_counter, $p_editmode = false)
    {
        global $g_comp_template;

        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        $l_dialog_data = null;

        switch ($p_method)
        {
            case 'controllerhandler':
                global $g_dirs;

                $l_dir         = opendir($g_dirs["handler"]);
                $l_dialog_data = [];

                if (is_resource($l_dir))
                {
                    while ($l_file = readdir($l_dir))
                    {
                        if (is_file($g_dirs["handler"] . DIRECTORY_SEPARATOR . $l_file) && preg_match("/^(isys_handler_(.*))\.class\.php$/i", $l_file, $l_register))
                        {
                            $l_dialog_data[strtoupper($l_register[1])] = str_replace('isys_handler_', '', $l_register[1]);
                        } // if
                    } // while
                    closedir($l_dir);
                } // if

                // Module controllers
                $l_module_dir = $g_dirs["class"] . 'modules' . DS;
                $l_module_res = opendir($l_module_dir);

                if (is_resource($l_module_res))
                {
                    while ($l_dir = readdir($l_module_res))
                    {
                        if (strpos($l_dir, '.') === false)
                        {
                            if (is_dir($l_module_dir . $l_dir . DS . 'handler' . DS . 'controller'))
                            {
                                $l_controller_dir = opendir($l_module_dir . $l_dir . DS . 'handler' . DS . 'controller');

                                while ($l_file = readdir($l_controller_dir))
                                {
                                    if (is_file($l_module_dir . $l_dir . DS . 'handler' . DS . 'controller' . DIRECTORY_SEPARATOR . $l_file) && preg_match(
                                            "/^(isys_handler_(.*))\.class\.php$/i",
                                            $l_file,
                                            $l_register
                                        )
                                    )
                                    {
                                        $l_dialog_data[strtoupper($l_register[1])] = str_replace('isys_handler_', '', $l_register[1]);
                                    } // if
                                } // while

                                closedir($l_controller_dir);
                            } // if
                        }
                        else
                        {
                            continue;
                        } // if
                    } // while

                    closedir($l_module_res);
                } // if
                break;

            case 'globalsettings':
                $l_dialog_data = isys_auth_system_globals::get_globalsettings_parameter();
                break;

            case 'jdisc':
                $l_dialog_data = [
                    C__MODULE__JDISC . 9  => 'LC__MODULE__JDISC__CONFIGURATION',
                    C__MODULE__JDISC . 10 => 'LC__MODULE__JDISC__PROFILES',
                ];
                break;

            case 'jsonrpcapi':
                $l_dialog_data = [
                    'global'  => 'LC__AUTH_GUI__QR_CODE_GLOBAL_CONFIGURATION',
                    'objtype' => 'LC__AUTH_GUI__QR_CODE_GLOBAL_OBJECT_TYPE'
                ];
                break;

            case 'ldap':
                $l_dialog_data = [
                    C__MODULE__LDAP . C__LDAPPAGE__CONFIG      => 'LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__SERVER',
                    C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES => 'LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__DIRECTORIES'
                ];
                break;

            case 'licencesettings':
                $l_dialog_data = isys_auth_system_licence::get_licencesettings_parameter();
                break;

            case 'loginventory':
                $l_dialog_data = [
                    C__MODULE__LOGINVENTORY . 9  => 'LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_CONFIGURATION',
                    C__MODULE__LOGINVENTORY . 10 => 'LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_DATABASES'
                ];
                break;

            case 'ocs':
                $l_dialog_data = [
                    'OCSCONFIG' => 'LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__CONFIGURATION',
                    'OCSDB'     => 'LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__DATABASE'
                ];
                break;

            case 'qr_config':
                $l_dialog_data = isys_auth_system_qr::get_qr_config_parameter();
                break;

            case 'systemtools':
                $l_dialog_data = [
                    'CACHE'          => 'LC__SYSTEM__CACHE',
                    'MODULEMANAGER'  => 'LC__CMDB__TREE__SYSTEM__TOOLS__MODULE_MANAGER',
                    'SYSTEMOVERVIEW' => 'LC__CMDB__TREE__SYSTEM__TOOLS__OVERVIEW',
                    'IDOITUPDATE'    => 'LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE'
                ];
                break;

            case 'tts':
                $l_dialog_data = [
                    'CONFIG' => 'LC__TTS__CONFIGURATION'
                ];
                break;

            case 'workflow':
                $l_dialog_data = [
                    C__WF__VIEW__DETAIL__EMAIL_GUI => 'LC_WORKFLOW_TREE__EMAIL',
                    C__WF__VIEW__LIST_FILTER       => 'LC__WORKFLOWS__MY',
                    C__WF__VIEW__LIST_TEMPLATE     => 'LC__WORKFLOW__TEMPLATES',
                    C__WF__VIEW__LIST              => 'LC__CMDB__CATG__WORKFLOW'
                ];
                break;
        } // switch

        if ($l_dialog_data !== null && is_array($l_dialog_data))
        {
            $l_dialog = new isys_smarty_plugin_f_dialog();

            if (is_string($p_param))
            {
                $p_param = strtolower($p_param);
            } // if

            $l_params = [
                'name'              => 'auth_param_form_' . $p_counter,
                'p_arData'          => $l_dialog_data,
                'p_editMode'        => $p_editmode,
                'p_bDbFieldNN'      => 1,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'input-small',
                'p_strSelectedID'   => $p_param
            ];

            $l_return['html'] = $l_dialog->navigation_edit($g_comp_template, $l_params);

            return $l_return;
        } // if

        return false;
    } // function
} // class