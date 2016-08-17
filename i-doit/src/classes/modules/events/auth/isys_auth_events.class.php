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
 * @subpackage  Events
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */
class isys_auth_events extends isys_auth implements isys_auth_interface
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
     * @return isys_auth_events
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
     */
    public function get_auth_methods()
    {
        return [
            'hooks'   => [
                'title'  => 'Event-Hooks',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT
                ]
            ],
            'history' => [
                'title'  => _L('LC__MODULE__EVENTS__HISTORY'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT
                ]
            ]
            /*,
			'config' => array(
				'title' => _L('LC__CONFIGURATION'),
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
        return C__MODULE__EVENTS;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__EVENTS";
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
    public function hooks($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right($p_right, 'hooks', self::EMPTY_ID_PARAM, new isys_exception_auth(_L('LC__AUTH__EVENTS_EXCEPTION__MISSING_RIGHT_FOR_HOOKS')));
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
    public function history($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right($p_right, 'history', self::EMPTY_ID_PARAM, new isys_exception_auth(_L('LC__AUTH__EVENTS_EXCEPTION__MISSING_RIGHT_FOR_HISTORY')));
    } // function
} // class