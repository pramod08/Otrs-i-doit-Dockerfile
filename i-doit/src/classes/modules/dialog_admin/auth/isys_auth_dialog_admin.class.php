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
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_dialog_admin extends isys_auth implements isys_auth_interface
{

    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_dialog_admin
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_dialog_admin
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'table'  => [
                'title' => _L('LC__AUTH_GUI__DIALOG_TABLE_CONDITION'),
                'type'  => 'dialog_tables'
            ],
            'custom' => [
                'title' => _L('LC__AUTH_GUI__CUSTOM_DIALOG_TABLE_CONDITION'),
                'type'  => 'custom_dialog_tables'
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
        return C__MODULE__DIALOG_ADMIN;
    } // function

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return "LC__DIALOG_ADMIN";
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
     * @param   boolean $p_combo_param This parameter is used, when more than one box is displayed at once (category in object, ...).
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function retrieve_parameter($p_method, $p_param, $p_counter, $p_editmode = false, $p_combo_param = false)
    {
        global $g_comp_database, $g_comp_template;

        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        switch ($p_method)
        {
            case 'dialog_tables':
                // Init the dialog admin.
                $l_dialog_admin = new isys_dialog_admin_dao($g_comp_database);
                $l_tables       = $l_dialog_admin->get_dialog_tables();
                $l_data         = [];

                // Bring the tables in the needed syntax.
                foreach ($l_tables as $l_table)
                {
                    $l_data[$l_table] = _L($l_table);
                } // foreach

                $l_dialog = new isys_smarty_plugin_f_dialog();
                $l_params = [
                    'name'              => 'auth_param_form_' . $p_counter,
                    'p_arData'          => serialize($l_data),
                    'p_editMode'        => $p_editmode,
                    'p_bDbFieldNN'      => 1,
                    'p_bInfoIconSpacer' => 0,
                    'p_strClass'        => 'input-small',
                    'p_strSelectedID'   => $p_param
                ];

                $l_return['html'] = isys_glob_utf8_encode($l_dialog->navigation_edit($g_comp_template, $l_params));
                break;

            case 'custom_dialog_tables':
                // Init the dialog admin.
                $l_dialog_admin = new isys_dialog_admin_dao($g_comp_database);
                $l_tables       = $l_dialog_admin->get_custom_dialogs();
                $l_data         = [];

                // Bring the tables in the needed syntax.
                foreach ($l_tables as $l_table)
                {
                    $l_data[strtolower($l_table['identifier'])] = $l_table['title'];
                } // foreach

                $l_dialog = new isys_smarty_plugin_f_dialog();
                $l_params = [
                    'name'              => 'auth_param_form_' . $p_counter,
                    'p_arData'          => serialize($l_data),
                    'p_editMode'        => $p_editmode,
                    'p_bDbFieldNN'      => 1,
                    'p_bInfoIconSpacer' => 0,
                    'p_strClass'        => 'input-small',
                    'p_strSelectedID'   => $p_param
                ];

                $l_return['html'] = $l_dialog->navigation_edit($g_comp_template, $l_params);
                break;
        } // switch

        return $l_return;
    } // function

    /**
     * This method checks, if you are allowed to process an action for the given dialog+ field.
     *
     * @param   integer $p_right
     * @param   string  $p_tablename Dialog+ Tablename
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function table($p_right, $p_tablename)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights(
            $p_right,
            'table',
            $p_tablename,
            new isys_exception_auth(
                _L(
                    'LC__AUTH__DIALOG_ADMIN_EXCEPTION__MISSING_RIGHT_FOR_DIALOG_ADMIN',
                    [
                        isys_auth::get_right_name($p_right),
                        $p_tablename
                    ]
                )
            )
        );
    } // function

    /**
     * This method checks, if you are allowed to process an action for the given custom dialog+ field.
     *
     * @param   integer $p_right
     * @param   string  $p_tablename Dialog+ Tablename
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function custom($p_right, $p_tablename)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->check_module_rights(
            $p_right,
            'custom',
            $p_tablename,
            new isys_exception_auth(
                _L(
                    'LC__AUTH__DIALOG_ADMIN_EXCEPTION__MISSING_RIGHT_FOR_DIALOG_ADMIN',
                    [
                        isys_auth::get_right_name($p_right),
                        $p_tablename
                    ]
                )
            )
        );
    } // function
} // class