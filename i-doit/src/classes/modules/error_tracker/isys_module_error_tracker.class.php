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
 * Error Tracker Module
 *
 * @package     modules
 * @subpackage  error_tracker
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */
class isys_module_error_tracker extends isys_module implements isys_module_interface
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var \error_trackers\Trackable
     */
    protected static $instance = null;
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * @param array $config
     *
     * @return \error_trackers\Trackable
     */
    public static function tracker($config = [])
    {
        if (!self::$instance)
        {
            $tracker = isys_settings::get('error-tracker.type', 'rollbar');
            $class   = 'error_trackers\\' . $tracker . '\\Tracker';

            if (class_exists($class) && is_a($class, 'error_trackers\Trackable', true))
            {
                self::$instance = new $class();
                self::$instance->initialize($config);
            }
            else throw new \isys_exception_general('Fail: Error Tracker "' . $class . '" does not exist! This error is not going to be tracked ;-)');
        }

        return self::$instance;
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_analytics
     */
    public function init(isys_module_request $p_req)
    {
        return $this;
    }

    /**
     * Build breadcrumb navifation
     *
     * @param &$p_gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        return [];
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_module_cmdb->build_tree();
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {

    } // function

    /**
     * Start method.
     *
     * @throws  isys_exception_licence
     * @throws  isys_exception_general
     * @return  isys_module_analytics
     */
    public function start()
    {
        return $this;
    } // function

} // class