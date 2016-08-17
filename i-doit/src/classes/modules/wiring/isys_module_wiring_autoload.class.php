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
 * @subpackage  wiring
 * @author      Dennis Stücken <dstuecken@synetic.de>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.6
 */
class isys_module_wiring_autoload extends isys_module_manager_autoload
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
        if (strpos($p_classname, 'isys_wiring_report_view') === 0)
        {
            $l_path = __DIR__ . 'report/views/' . $p_classname . '.class.php';
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