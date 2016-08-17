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
 * Wiring module class.
 *
 * @package     modules
 * @subpackage  wiring
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */
class isys_module_wiring extends isys_module implements isys_module_interface, isys_module_authable
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = true;
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var  boolean
     */
    protected static $m_licenced = true;
    /**
     * Variable which the module request class.
     *
     * @var  isys_module_request
     */
    protected $m_modreq = null;
    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Get related auth class for module.
     *
     * @return  isys_auth_wiring
     */
    public static function get_auth()
    {
        return isys_auth_wiring::instance();
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_analytics
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;

        return $this;
    } // function
} // class