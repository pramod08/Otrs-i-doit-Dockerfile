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
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.6.0
 */
class isys_module_maintenance_autoload extends isys_module_manager_autoload
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
        $l_classes = [
            'isys_maintenance_auth'                          => '/src/classes/modules/maintenance/auth/isys_maintenance_auth.class.php',
            'isys_cmdb_dao_category_g_virtual_maintenance'   => '/src/classes/modules/maintenance/cmdb/dao/isys_cmdb_dao_category_g_virtual_maintenance.class.php',
            'isys_cmdb_ui_category_g_virtual_maintenance'    => '/src/classes/modules/maintenance/cmdb/ui/isys_cmdb_ui_category_g_virtual_maintenance.class.php',
            'isys_maintenance_dao'                           => '/src/classes/modules/maintenance/dao/isys_maintenance_dao.class.php',
            'isys_ajax_handler_maintenance'                  => '/src/classes/modules/maintenance/handler/ajax/isys_ajax_handler_maintenance.class.php',
            'isys_handler_maintenance_notification'          => '/src/classes/modules/maintenance/handler/controller/isys_handler_maintenance_notification.class.php',
            'isys_module_maintenance_install'                => '/src/classes/modules/maintenance/install/isys_module_maintenance_install.class.php',
            'isys_popup_maintenance_finish'                  => '/src/classes/modules/maintenance/popup/isys_popup_maintenance_finish.class.php',
            'isys_maintenance_reportview_fpdi'               => '/src/classes/modules/maintenance/reportview/isys_maintenance_reportview_fpdi.class.php',
            'isys_maintenance_reportview_maintenance_export' => '/src/classes/modules/maintenance/reportview/isys_maintenance_reportview_maintenance_export.class.php'
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