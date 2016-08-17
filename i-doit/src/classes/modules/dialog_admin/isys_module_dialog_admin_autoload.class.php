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
class isys_module_dialog_admin_autoload extends isys_module_manager_autoload
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
        if (strpos($p_classname, 'isys_dialog_admin_') === 0)
        {
            $l_path = __DIR__ . '/dao/';
            //$l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'dialog_admin' . DS . str_replace('_', DS, substr($p_classname, 18)) . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_auth_dao_dialog_admin') === 0)
        {
            $l_path = __DIR__ . '/auth/dao/';
        }
        else if (strpos($p_classname, 'isys_auth_dialog_admin') === 0)
        {
            $l_path = __DIR__ . '/auth/';
        } // if

        if (isset($l_path))
        {
            $l_path = $l_path . $p_classname . '.class.php';

            if (file_exists($l_path) && include_once($l_path))
            {
                isys_caching::factory('autoload')
                    ->set($p_classname, $l_path);

                return true;
            } // if
        } // if

        return false;
    } // function
} // class