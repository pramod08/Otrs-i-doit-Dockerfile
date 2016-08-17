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
 * Auth: Class for Report module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_report extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_report
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_report
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
            'editor'              => [
                'title'  => _L('LC__AUTH_GUI__CREATE_NEW_REPORTS'),
                'type'   => 'boolean',
                'rights' => [isys_auth::EXECUTE]
            ],
            'online_reports'      => [
                'title'    => _L('LC__AUTH_GUI__ONLINE_REPOSITORY'),
                'type'     => 'boolean',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ],
                'defaults' => [isys_auth::VIEW]
            ],
            'views'               => [
                'title'    => _L('LC__AUTH_GUI__VIEWS'),
                'type'     => 'views',
                'rights'   => [isys_auth::VIEW],
                'defaults' => [isys_auth::VIEW]
            ],
            'report_category'     => [
                'title'    => _L('LC__AUTH_GUI__REPORT_CATEGORIES'),
                'type'     => 'boolean',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::SUPERVISOR
                ],
                'defaults' => [isys_auth::VIEW]
            ],
            'reports_in_category' => [
                'title' => _L('LC__AUTH_GUI__REPORTS_IN_CATEGORY'),
                'type'  => 'reports_in_category',
            ],
            'custom_report'       => [
                'title' => _L('LC__AUTH_GUI__REPORTS'),
                'type'  => 'custom_report'
            ],
        ];
    } // function

    /**
     * Get ID of related module.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return C__MODULE__REPORT;
    } // function

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return "LC__MODULE__REPORT";
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
        global $g_comp_template, $g_absdir, $g_comp_database_system;

        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        switch ($p_method)
        {
            case 'views':
                $l_data     = [];
                $l_view_dir = $g_absdir . "/src/classes/modules/report/views/";
                $l_dialog   = new isys_smarty_plugin_f_dialog();

                $l_dao    = new isys_module_report_pro($g_comp_database_system);
                $l_result = $l_dao->getViews($l_view_dir, true);

                if (count($l_result) > 0)
                {
                    foreach ($l_result AS $l_key => $l_content)
                    {
                        $l_data[strtoupper($l_key)] = $l_content;
                    } // foreach
                } // if

                if (is_string($p_param))
                {
                    $p_param = strtoupper($p_param);
                }

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

            case 'reports_in_category':
                $l_data   = [];
                $l_dao    = new isys_report_dao($g_comp_database_system);
                $l_res    = $l_dao->get_report_categories(null, false);
                $l_dialog = new isys_smarty_plugin_f_dialog();

                if ($l_res->num_rows() > 0)
                {
                    while ($l_row = $l_res->get_row())
                    {
                        $l_data[$l_row['isys_report_category__id']] = $l_row['isys_report_category__title'];
                    } // while
                } // if

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

            case 'custom_report':
                $l_data       = [];
                $l_dao        = new isys_report_dao($g_comp_database_system);
                $l_reportsRes = $l_dao->get_reports();
                $l_dialog     = new isys_smarty_plugin_f_dialog();

                while ($l_row = $l_reportsRes->get_row())
                {
                    $l_data[$l_row['isys_report__id']] = _L($l_row['isys_report__title']);
                }

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

            default:
                return false;
        } // switch

        return $l_return;
    } // function

    /**
     * This methid checks if the user is allowed to use the report categories
     *
     * @param $p_right
     *
     * @return bool
     */
    public function report_category($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('report_category', new isys_exception_auth(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT_CATEGORIES')), $p_right);
    } // function

    /**
     * This method checks, if you are allowed to use the report editor.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @throws  isys_exception_auth
     */
    public function editor($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('editor', new isys_exception_auth(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_CREATING_REPORTS')), $p_right);
    } // function

    /**
     * This method checks, if you are allowed to access the online reports.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @throws  isys_exception_auth
     */
    public function online_reports($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('online_reports', new isys_exception_auth(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_ONLINE_REPOSITORY')), $p_right);
    } // function

    /**
     * This method checks, if you are allowed to access the online reports.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @throws  isys_exception_auth
     */
    public function views($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        if (!empty($p_id))
        {
            if (is_array($this->m_paths['views']))
            {
                if (isset($this->m_paths['views'][isys_auth::WILDCHAR]))
                {
                    return true;
                } // if
                if (isset($this->m_paths['views'][$p_id]))
                {
                    if (in_array($p_right, $this->m_paths['views'][$p_id]))
                    {
                        return true;
                    } // if
                } // if
            } // if
            $l_views = isys_module_auth::get_module_rights_parameters('views');

            throw new isys_exception_auth(
                _L(
                    'LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT',
                    [
                        $this->get_right_name($p_right),
                        $l_views[strtoupper($p_id)]
                    ]
                )
            );
        }
        else
        {
            return $this->check_module_rights($p_right, 'views', $p_id, new isys_exception_auth(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_VIEWS')));
        } // if
    } // function

    /**
     * This method checks, if you are allowed to access a custom/online report.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function report($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_database_system;

        // This checks for paths like "CMDB/OBJ_TYPE" without IDs (will be used to check, if the "new" button shall be displayed in the list-view).
        if (empty($p_id) && is_array($this->m_paths['report']) && isset($this->m_paths['report'][isys_auth::EMPTY_ID_PARAM]) && in_array(
                $p_right,
                $this->m_paths['report'][isys_auth::EMPTY_ID_PARAM]
            )
        )
        {
            return true;
        } // if

        $l_report_dao = new isys_report_dao($g_comp_database_system);
        $l_row        = $l_report_dao->get_report($p_id);
        $l_right_name = isys_auth::get_right_name($p_right);

        return $this->generic_right(
            $p_right,
            'report',
            $p_id,
            new isys_exception_auth(
                _L(
                    'LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT',
                    [
                        $l_right_name,
                        $l_row['isys_report__title']
                    ]
                )
            )
        );
    } // function

    /**
     * This method checks, if you are allowed to access a custom/online report.
     *
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function custom_report($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_database_system;

        // This checks for paths like "CMDB/OBJ_TYPE" without IDs (will be used to check, if the "new" button shall be displayed in the list-view).
        if (empty($p_id))
        {
            if (is_array($this->m_paths['custom_report']) && isset($this->m_paths['custom_report'][isys_auth::EMPTY_ID_PARAM]))
            {
                if (in_array($p_right, $this->m_paths['custom_report'][isys_auth::EMPTY_ID_PARAM]))
                {
                    return true;
                } // if
            } // if

            // Check if has any rights in the report list
            if (is_array($this->m_paths['custom_report']) || is_array($this->m_paths['reports_in_category']))
            {
                return true;
            } // if

            throw new isys_exception_auth(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_ALL_REPORTS', [$this->get_right_name($p_right)]));
        }
        else
        {
            $l_report_dao = new isys_report_dao($g_comp_database_system);
            $l_row        = $l_report_dao->get_report($p_id);
            $l_right_name = isys_auth::get_right_name($p_right);

            if (!isset($this->m_paths['custom_report'][$p_id]))
            {
                if (is_array($this->m_paths['reports_in_category']))
                {
                    if (isset($this->m_paths['reports_in_category'][$l_row['isys_report__isys_report_category__id']]))
                    {
                        // Check by report category
                        return $this->generic_right(
                            $p_right,
                            'reports_in_category',
                            $l_row['isys_report__isys_report_category__id'],
                            new isys_exception_auth(
                                _L(
                                    'LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT',
                                    [
                                        $l_right_name,
                                        $l_row['isys_report__title']
                                    ]
                                )
                            )
                        );
                    } // if
                } // if
            } // if

            // Check by report id
            return $this->generic_right(
                $p_right,
                'custom_report',
                $p_id,
                new isys_exception_auth(
                    _L(
                        'LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT',
                        [
                            $l_right_name,
                            $l_row['isys_report__title']
                        ]
                    )
                )
            );
        } // if
    } // function

    /**
     * Gets all allowed reports for the specified type.
     *
     * @return  mixed
     */
    public function get_allowed_reports()
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_database_system;

        $l_paths = [];

        if (isset($this->m_paths['custom_report']))
        {
            $l_paths = $this->m_paths['custom_report'];
        } // if

        $l_return = null;

        if (isset($l_paths[isys_auth::WILDCHAR]))
        {
            return true;
        } // if

        if (is_array($l_paths) && !isset($l_paths[isys_auth::EMPTY_ID_PARAM]) && !isset($l_paths[isys_auth::WILDCHAR]))
        {
            $l_return = array_keys($l_paths);
        } // if

        if (isset($this->m_paths['reports_in_category']))
        {
            $l_report_dao        = new isys_report_dao($g_comp_database_system);
            $l_report_categories = null;

            if (!isset($this->m_paths['reports_in_category'][isys_auth::WILDCHAR]))
            {
                $l_report_categories = array_keys($this->m_paths['reports_in_category']);
            } // if

            $l_res    = $l_report_dao->get_reports_by_category($l_report_categories);
            $l_return = (array) $l_return;

            while ($l_report_row = $l_res->get_row())
            {
                if (!in_array($l_report_row['isys_report__id'], $l_return))
                {
                    $l_return[] = $l_report_row['isys_report__id'];
                } // if
            } // while
        } // if

        return (is_array($l_return)) ? $l_return : false;
    } // function

    /**
     * Gets all allowed report categories.
     *
     * @return  mixed
     */
    public function get_allowed_report_categories()
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $l_paths  = $this->m_paths['reports_in_category'];
        $l_return = false;

        if (is_array($l_paths) && !isset($l_paths[isys_auth::EMPTY_ID_PARAM]) && !isset($l_paths[isys_auth::WILDCHAR]))
        {
            $l_return = array_keys($l_paths);
        }
        else if (isset($l_paths[isys_auth::WILDCHAR]))
        {
            $l_return = true;
        } // if

        return $l_return;
    } // function

    /**
     * This method checks, if you are allowed to access a reports in category.
     *
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function reports_in_category($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_database_system;

        $l_report_dao      = new isys_report_dao($g_comp_database_system);
        $l_report_category = current($l_report_dao->get_report_categories($p_id));

        return $this->generic_right(
            $p_right,
            'reports_in_category',
            $p_id,
            new isys_exception_auth(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_SPECIFIED_REPORT_CATEGORY', [$l_report_category['isys_report_category__title']]))
        );
    } // function

    /**
     * Check rights if the user has any rights for the report and in categories
     *
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_auth
     */
    public function check_report_right($p_right, $p_id)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        global $g_comp_database_system;

        $l_paths            = $this->m_paths['custom_report'];
        $l_in_custom_report = false;
        $l_right_name       = isys_auth::get_right_name($p_right);
        $l_report_dao       = new isys_report_dao($g_comp_database_system);
        $l_row              = $l_report_dao->get_report($p_id);
        $l_arr              = [];

        if (isset($l_paths[isys_auth::WILDCHAR]))
        {
            $l_in_custom_report = true;
            $l_arr              = $l_paths[isys_auth::WILDCHAR];
        }
        else if (isset($l_paths[$p_id]))
        {
            $l_in_custom_report = true;
            $l_arr              = $l_paths[$p_id];
        } // if

        if ($l_in_custom_report)
        {
            // Check in custom_report
            if (!in_array($p_right, $l_arr))
            {
                throw new isys_exception_auth(
                    _L(
                        'LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT',
                        [
                            $l_right_name,
                            $l_row['isys_report__title']
                        ]
                    )
                );
            } // if
        }
        else
        {
            // Check in reports in categories
            try
            {
                $this->reports_in_category($p_right, $l_row['isys_report__isys_report_category__id']);
            }
            catch (isys_exception_auth $e)
            {
                throw new isys_exception_auth(
                    _L(
                        'LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT',
                        [
                            $l_right_name,
                            $l_row['isys_report__title']
                        ]
                    )
                );
            } // try
        } // if

        return true;
    } // function
} // class