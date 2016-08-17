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
 * @package     modules
 * @subpackage  itservice
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4
 */
class isys_module_itservice_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $p_classname
     *
     * @return  boolean
     */
    public static function init($p_classname)
    {
        if (strpos($p_classname, 'isys_ajax_handler_itservice') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'itservice' . DS . 'handler' . DS . 'ajax' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_itservice_dao_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'itservice' . DS . 'dao' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_itservice_reports') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'itservice' . DS . 'reports' . DS . $p_classname . '.class.php';
        }
        else if ($p_classname === 'isys_auth_itservice')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'itservice' . DS . 'auth' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_itservice_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'itservice' . DS . str_replace('_', DS, substr($p_classname, 15)) . DS . $p_classname . '.class.php';
        } // if

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