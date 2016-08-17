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
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_report_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader.
     *
     * @param   string $p_classname
     *
     * @return  boolean
     */
    public static function init($p_classname)
    {
        if ($p_classname === 'isys_report_dao')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'dao' . DS . $p_classname . '.class.php';
        }
        else if ($p_classname === 'isys_module_report_open' || $p_classname === 'isys_module_report_pro')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'controller' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_ajax_handler_report') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'handler' . DS . 'ajax' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_handler_report') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'handler' . DS . 'controller' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_popup_report') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'popups' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_report_export') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'export' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_report_view') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'views' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_report_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . str_replace('_', DS, substr($p_classname, 12)) . DS . $p_classname . '.class.php';
        }
        else if ($p_classname === 'isys_auth_report')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'auth' . DS . $p_classname . '.class.php';
        }
        else if ($p_classname === 'isys_auth_dao_report')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'auth' . DS . 'dao' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_dashboard_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'report' . DS . 'dashboard' . DS . 'widgets' . DS . 'reports' . DS . $p_classname . '.class.php';
        }

        if (!empty($l_path))
        {
            if (parent::include_file($l_path))
            {
                isys_caching::factory('autoload')
                    ->set($p_classname, $l_path);

                return true;
            } // if
        } // if

        return false;
    } // function
} // class