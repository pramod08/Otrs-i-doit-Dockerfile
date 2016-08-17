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
 * @subpackage  Maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_maintenance_auth extends isys_auth implements isys_auth_interface
{

    /**
     * Container for singleton instance.
     *
     * @var  isys_maintenance_auth
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_events
     * @author  Leonard Fischer <lfischer@i-doit.com>
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
            'planning'         => [
                'title'  => _L('LC__MAINTENANCE__AUTH__PLANNING'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE
                ]
            ],
            'planning_archive' => [
                'title'  => _L('LC__MAINTENANCE__AUTH__PLANNING_ARCHIVE'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE
                ]
            ],
            'mailtemplate'     => [
                'title'  => _L('LC__MAINTENANCE__AUTH__MAILTEMPLATE'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE
                ]
            ],
            'overview'         => [
                'title'  => _L('LC__MAINTENANCE__AUTH__OVERVIEW'),
                'type'   => 'boolean',
                'rights' => [isys_auth::VIEW]
            ],
            'send_mails'       => [
                'title'  => _L('LC__MAINTENANCE__AUTH__SEND_MAILS'),
                'type'   => 'boolean',
                'rights' => [isys_auth::EXECUTE]
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
        return C__MODULE__MAINTENANCE;
    } // function
} // class