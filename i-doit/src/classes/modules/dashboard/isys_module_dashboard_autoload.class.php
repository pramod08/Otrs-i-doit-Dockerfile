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
 * Class autoloader.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.2.0
 */
class isys_module_dashboard_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $p_classname
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function init($p_classname)
    {
        if (strpos($p_classname, 'isys_ajax_handler_dashboard') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'dashboard' . DS . 'handler' . DS . 'ajax' . DS . $p_classname . '.class.php';
        } // if

        if (strpos($p_classname, 'isys_ajax_handler_dashboard_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'dashboard' . DS . 'handler' . DS . 'ajax' . DS . str_replace(
                    '_',
                    DS,
                    substr($p_classname, 28)
                ) . DS . $p_classname . '.class.php';
        } // if

        if (strpos($p_classname, 'isys_dashboard_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'dashboard' . DS . str_replace('_', DS, substr($p_classname, 15)) . DS . $p_classname . '.class.php';
        } // if

        if ($p_classname === 'isys_auth_dashboard')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'dashboard' . DS . 'auth' . DS . $p_classname . '.class.php';
        } // if

        if (!empty($l_path))
        {
            if (parent::include_file($l_path))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            } // if
        } // if

        return false;
    } // function
} // class