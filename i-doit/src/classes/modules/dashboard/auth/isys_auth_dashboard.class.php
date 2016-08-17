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
 * Auth: Class for i-doit authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.2.0
 */
class isys_auth_dashboard extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_dashboard
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_dashboard
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
        return [
            'configure_dashboard'        => [
                'title'  => _L('LC__AUTH_GUI__CONFIGURE_DASHBOARD'),
                'type'   => 'boolean',
                'rights' => [isys_auth::EXECUTE]
            ],
            'configure_other_dashboards' => [
                'title'  => _L('LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS'),
                'type'   => 'boolean',
                'rights' => [isys_auth::SUPERVISOR]
            ],
            'configure_widgets'          => [
                'title'  => _L('LC__AUTH_GUI__CONFIGURE_WIDGETS'),
                'type'   => 'boolean',
                'rights' => [isys_auth::EXECUTE]
            ]
        ];
    } // function

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return C__MODULE__DASHBOARD;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__DASHBOARD";
    } // function

    /**
     * Method for checking, if the user is allowed to open and execute the dashboard configuration.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function configure_dashboard($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right(
            $p_right,
            'configure_dashboard',
            self::EMPTY_ID_PARAM,
            new isys_exception_auth(_L('LC__AUTH__DASHBOARD_EXCEPTION__MISSING_RIGHT_FOR_DASHBOARD_CONFIG'))
        );
    } // function

    /**
     * Method for checking, if the user is allowed to configure the dashboard of other users.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function configure_other_dashboards($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right(
            $p_right,
            'configure_other_dashboards',
            self::EMPTY_ID_PARAM,
            new isys_exception_auth(_L('LC__AUTH__DASHBOARD_EXCEPTION__MISSING_RIGHT_FOR_OTHERS_DASHBOARD_CONFIG'))
        );
    } // function

    /**
     * Method for checking, if the user is allowed to configure widgets.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function configure_widgets($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right(
            $p_right,
            'configure_widgets',
            self::EMPTY_ID_PARAM,
            new isys_exception_auth(_L('LC__AUTH__DASHBOARD_EXCEPTION__MISSING_RIGHT_FOR_WIDGET_CONFIG'))
        );
    } // function
} // class