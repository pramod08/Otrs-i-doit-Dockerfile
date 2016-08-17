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
 * Auth: Class for Auth module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_auth extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_auth
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_auth
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
            'overview' => [
                'title'    => _L('LC__AUTH_GUI__AUTH_OVERVIEW'),
                'type'     => 'boolean',
                'rights'   => [isys_auth::VIEW],
                'defaults' => [isys_auth::VIEW]
            ],
            'module'   => [
                'title' => _L('LC__AUTH_GUI__AUTH_MODULES'),
                'type'  => 'modules'
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
        return C__MODULE__AUTH;
    } // function

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__AUTH";
    } // function

    /**
     * Checks, if the current user is allowed to see the auth-overview.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function overview()
    {
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        return $this->generic_boolean('overview', new isys_exception_auth(_L('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_OVERVIEW')));
    } // function

    /**
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function module($p_right, $p_id)
    {
        // Check for inactive auth system
        if (!$this->is_auth_active())
        {
            return true;
        } // if

        $l_module_const = strtoupper($p_id);

        if (!defined($l_module_const))
        {
            throw new isys_exception_general(_L('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_module_const));
        } // if

        if (is_array($this->m_paths) && isset($this->m_paths['module']) && is_array($this->m_paths['module']))
        {
            if (isset($this->m_paths['module'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['module'][isys_auth::WILDCHAR]))
            {
                return true;
            } // if

            if (isset($this->m_paths['module'][$p_id]) && in_array($p_right, $this->m_paths['module'][$p_id]))
            {
                return true;
            } // if
        } // if

        // Retrieve the module row, to display a nice exception message.
        $l_module = isys_module_manager::instance()
            ->get_modules(constant($l_module_const))
            ->get_row();

        throw new isys_exception_auth(_L('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_MODULE', _L($l_module['isys_module__title'])));
    } // function
} // class