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
 * @package     Modules
 * @subpackage  nostalgia
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.7
 */
class isys_module_nostalgia_autoload extends isys_module_manager_autoload
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
        $l_classlist = [
            'isys_helper_ip' => '/src/classes/modules/nostalgia/src/classes/helper/isys_helper_ip.class.php',
        ];

        if (isset($l_classlist[$p_classname]))
        {
            if (parent::include_file($l_classlist[$p_classname]))
            {
                isys_caching::factory('autoload')
                    ->set($p_classname, $l_classlist[$p_classname]);

                return true;
            } // if
        } // if

        return false;
    } // function
} // class