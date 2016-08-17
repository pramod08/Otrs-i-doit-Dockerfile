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
 * @package     Modules
 * @subpackage  Wiring
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.6
 */
class isys_auth_wiring extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_check_mk
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_wiring
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null)
        {
            global $g_comp_database;

            self::$m_dao = isys_auth_dao::instance($g_comp_database);
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
     */
    public function get_auth_methods()
    {
        return [
            'wiring'        => [
                'title'  => 'Wiring',
                'type'   => 'boolean',
                'rights' => [isys_auth::VIEW]
            ],
            'wiring_object' => [
                'title'  => _L('LC__MODULE__WIRING__OBJECT'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT
                ]
            ]
            /*,
                        'wiring_house' => array(
                            'title' => _L('LC__MODULE__WIRING__HOUSE'),
                            'type' => 'boolean',
                            'rights' => array(isys_auth::VIEW, isys_auth::EDIT)
                        )*/
        ];
    } // function

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return C__MODULE__WIRING;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__WIRING";
    } // function

    /**
     * Method for checking, if the user is allowed to manipulute hooks
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Dennis Stuecken <dstuecken@i-doit.com>
     */
    public function wiring($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right($p_right, __FUNCTION__, self::EMPTY_ID_PARAM, new isys_exception_auth(_L('LC__AUTH__EVENTS_EXCEPTION__WIRING')));
    } // function

    /**
     * Method for checking, if the user is allowed to view the history
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Dennis Stuecken <dstuecken@i-doit.com>
     */
    public function wiring_object($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right($p_right, __FUNCTION__, self::EMPTY_ID_PARAM, new isys_exception_auth(_L('LC__AUTH__EVENTS_EXCEPTION__WIRING')));
    } // function

    /**
     * Method for checking, if the user is allowed to view the history
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Dennis Stuecken <dstuecken@i-doit.com>
     */
    public function wiring_house($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right($p_right, __FUNCTION__, self::EMPTY_ID_PARAM, new isys_exception_auth(_L('LC__AUTH__EVENTS_EXCEPTION__WIRING')));
    } // function
} // class