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
 * @subpackage  Analytics
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.4
 */
class isys_auth_itservice extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_itservice
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_itservice
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
            'type_config'   => [
                'title'  => _L('LC__ITSERVICE__AUTH__TYPE_CONFIG'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'filter_config' => [
                'title'  => _L('LC__ITSERVICE__AUTH__FILTER_CONFIG'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
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
        return C__MODULE__ITSERVICE;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__ITSERVICE";
    } // function

    /**
     * Method for checking, if the user is allowed to view / execute a simulation.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function type_config($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right(
            $p_right,
            'type_config',
            $p_param,
            new isys_exception_auth(_L('LC__AUTH__EXCEPTION__MISSING_METHOD_RIGHT', isys_auth::get_right_name($p_right)))
        );
    } // function

    /**
     * Method for checking, if the user is allowed to administrate dataquality profiles (add, delete, publish).
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function filter_config($p_right, $p_param)
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_right(
            $p_right,
            'filter_config',
            $p_param,
            new isys_exception_auth(_L('LC__AUTH__EXCEPTION__MISSING_METHOD_RIGHT', isys_auth::get_right_name($p_right)))
        );
    } // function
} // class