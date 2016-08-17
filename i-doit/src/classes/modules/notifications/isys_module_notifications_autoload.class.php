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
class isys_module_notifications_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @return boolean
     *
     * @param string $p_classname
     */
    public static function init($p_classname)
    {
        try
        {
            if ($p_classname === 'isys_notifications_dao')
            {
                $l_path = __DIR__ . '/dao/';
            }
            else if ($p_classname === 'isys_auth_notifications')
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
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return false;
    } // function

} // class