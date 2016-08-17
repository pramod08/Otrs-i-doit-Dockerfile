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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_nagios_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @param   string $p_classname
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function init($p_classname)
    {
        if (strpos($p_classname, 'isys_ajax_handler_nagios') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'nagios' . DS . 'handler' . DS . 'ajax' . DS . $p_classname . '.class.php';
        } // if

        if (strpos($p_classname, 'isys_handler_nagios') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'nagios' . DS . 'handler' . DS . 'controller' . DS . $p_classname . '.class.php';
        } // if

        if (strpos($p_classname, 'isys_nagios_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'nagios' . DS . str_replace('_', DS, substr($p_classname, 12)) . DS . $p_classname . '.class.php';
        } // if

        if ($p_classname === 'isys_auth_nagios')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'nagios' . DS . 'auth' . DS . 'isys_auth_nagios.class.php';
        } // if

        if (!empty($l_path) && parent::include_file($l_path))
        {
            isys_caching::factory('autoload')
                ->set($p_classname, $l_path);

            return true;
        } // if

        return false;
    } // function
} // class