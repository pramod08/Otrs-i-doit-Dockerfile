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
 * Auth: Class for Nagios module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_nagios extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_nagios
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_nagios
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
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'nagios_commands'            => [
                'title' => 'Commands',
                'type'  => 'boolean'
            ],
            'nagios_config'              => [
                'title' => 'Config',
                'type'  => 'boolean'
            ],
            'nagios_export'              => [
                'title'  => 'Export',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'nagios_host_escalations'    => [
                'title' => 'Host escalations',
                'type'  => 'boolean'
            ],
            'nagios_nagios_hosts'        => [
                'title' => 'Nagios Hosts',
                'type'  => 'boolean'
            ],
            'nagios_service_escalations' => [
                'title' => 'Service escalations',
                'type'  => 'boolean'
            ],
            'nagios_timeperiods'         => [
                'title' => 'Timeperiods',
                'type'  => 'boolean'
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
        return C__MODULE__NAGIOS;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__NAGIOS";
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_commands($p_right)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean(
            'nagios_commands',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Commands',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_config($p_right)
    {
        return $this->generic_boolean(
            'nagios_config',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Config',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_export($p_right)
    {
        return $this->generic_boolean(
            'nagios_export',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Export',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_host_escalations($p_right)
    {
        return $this->generic_boolean(
            'nagios_host_escalations',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Host escalations',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_nagios_hosts($p_right)
    {
        return $this->generic_boolean(
            'nagios_nagios_hosts',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Nagios Hosts',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_service_escalations($p_right)
    {
        return $this->generic_boolean(
            'nagios_service_escalations',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Service escalations',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function

    /**
     * Method for checking, if the user has the right to view the CMDB explorer.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function nagios_timeperiods($p_right)
    {
        return $this->generic_boolean(
            'nagios_timeperiods',
            new isys_exception_auth(
                _L(
                    'LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE',
                    [
                        'Timeperiods',
                        'Nagios'
                    ]
                )
            ),
            $p_right
        );
    } // function
} // class