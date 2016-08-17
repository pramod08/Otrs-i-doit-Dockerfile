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
class isys_module_import_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader.
     *
     * @param   string $p_classname
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function init($p_classname)
    {
        $l_classes = [
            'isys_module_dao_import_log'   => '/src/classes/modules/import/dao/isys_module_dao_import_log.class.php',
            'isys_ajax_handler_csv_import' => '/src/classes/modules/import/handler/ajax/isys_ajax_handler_csv_import.class.php',
        ];

        if (isset($l_classes[$p_classname]) && parent::include_file($l_classes[$p_classname]))
        {
            isys_caching::factory('autoload')
                ->set($p_classname, $l_classes[$p_classname]);

            return true;
        } // if

        return false;
    } // function
} // class