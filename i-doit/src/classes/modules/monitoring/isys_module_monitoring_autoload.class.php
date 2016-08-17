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
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_module_monitoring_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $p_classname
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function init($p_classname)
    {
        $l_classlist = [
            'isys_monitoring_dao_hosts'               => '/src/classes/modules/monitoring/dao/isys_monitoring_dao_hosts.class.php',
            'isys_monitoring_dao_ndo'                 => '/src/classes/modules/monitoring/dao/isys_monitoring_dao_ndo.class.php',
            'isys_ajax_handler_monitoring_livestatus' => '/src/classes/modules/monitoring/handler/ajax/isys_ajax_handler_monitoring_livestatus.class.php',
            'isys_ajax_handler_monitoring_ndo'        => '/src/classes/modules/monitoring/handler/ajax/isys_ajax_handler_monitoring_ndo.class.php',
            'isys_monitoring_helper'                  => '/src/classes/modules/monitoring/helper/isys_monitoring_helper.class.php',
            'isys_monitoring_livestatus'              => '/src/classes/modules/monitoring/livestatus/isys_monitoring_livestatus.class.php',
            'isys_monitoring_ndo'                     => '/src/classes/modules/monitoring/ndo/isys_monitoring_ndo.class.php',
            'isys_monitoring_widgets_not_ok_hosts'    => '/src/classes/modules/monitoring/widgets/not_ok_hosts/isys_monitoring_widgets_not_ok_hosts.class.php'
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