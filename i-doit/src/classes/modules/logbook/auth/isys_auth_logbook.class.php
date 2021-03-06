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
 * Auth: Class for Notifications module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_logbook extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_logbook
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_logbook
     * @author  Selcuk Kekec <skekec@i-doit.com>
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
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'logbook' => [
                'title' => _L('LC__AUTH_GUI__LOGBOOK_CONDITION'),
                'type'  => 'logbook'
            ]
        ];
    } // function

    /**
     * Get ID of related module.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return C__MODULE__LOGBOOK;
    } // function

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return "LC__MODULE__LOGBOOK";
    } // function

    /**
     * Determines the rights for the logbook module.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function logbook($p_right, $p_type)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        switch ($p_type)
        {
            case C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW:
                $l_exception = _L('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [_L('LC__CMDB__LOGBOOK__LIST_CONTENT_TITLE')]);
                break;
            case C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION:
                $l_exception = _L('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [_L('LC__MODULE__CMDB__LOGBOOK_CONFIGURATION')]);
                break;
            case C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE:
                $l_exception = _L('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [_L('LC__NAVIGATION__NAVBAR__ARCHIVE')]);
                break;
            case C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE:
                $l_exception = _L('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [_L('LC__UNIVERSAL__RESTORE')]);
                break;
            default:
                $l_exception = _L('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LOGBOOK');
                break;
        } // switch

        return $this->check_module_rights($p_right, 'logbook', $p_type, new isys_exception_auth($l_exception));
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
            case 'logbook':
                $l_dialog_data = [
                    C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW          => 'LC__CMDB__LOGBOOK__LIST_CONTENT_TITLE',
                    C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION => 'LC__MODULE__CMDB__LOGBOOK_CONFIGURATION',
                    C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE       => 'LC__NAVIGATION__NAVBAR__ARCHIVE',
                    C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE       => 'LC__UNIVERSAL__RESTORE'
                ];
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