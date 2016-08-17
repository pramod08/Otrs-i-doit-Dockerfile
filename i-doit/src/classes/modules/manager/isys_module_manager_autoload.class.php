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
class isys_module_manager_autoload
{

    /**
     * Autoloader.
     *
     * @return boolean
     *
     * @param string $p_classname
     */
    public static function init($p_classname)
    {
        ;
    } // function

    /**
     * Method for including the given file.
     *
     * @param   string $p_file
     *
     * @return  boolean
     */
    public static function include_file($p_file)
    {
        global $g_absdir;

        if (!is_null($p_file) && file_exists($g_absdir . $p_file))
        {
            if (include_once($g_absdir . $p_file))
            {
                return true;
            } // if
        } // if

        return false;
    } // function
} // class