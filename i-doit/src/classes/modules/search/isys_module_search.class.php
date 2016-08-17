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
 * Search module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_search extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    const MAIN_MENU_REWRITE_LINK = true;
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Get related auth class for module
     *
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_search::instance();
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {

    } // function

    /**
     * Module start method
     */
    public function start()
    {
        return;

    } // function

    /**
     * @param isys_module_request $p_req
     *
     * @return bool
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req))
        {
            return true;
        } // if
        return false;
    } // function

}