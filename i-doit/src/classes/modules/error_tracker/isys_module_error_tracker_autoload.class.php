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
 * @subpackage  error_tracker
 * @author      Dennis Stücken <dstuecken@synetic.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */
class isys_module_error_tracker_autoload extends isys_module_manager_autoload
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
        if (strstr($p_classname, 'Track'))
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'error_tracker' . DS . str_replace('\\', DS, $p_classname) . '.php';
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