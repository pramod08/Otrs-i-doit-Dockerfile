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
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_module_check_mk_autoload extends isys_module_manager_autoload
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
        if (strpos($p_classname, 'isys_cmdb_dao_category_g_cmk') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'cmdb' . DS . 'dao' . DS . 'global' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_dao_list_catg_cmk') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'cmdb' . DS . 'dao' . DS . 'list' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_ui_category_g_cmk') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'cmdb' . DS . 'ui' . DS . 'global' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_ajax_handler_check_mk') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'handler' . DS . 'ajax' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_handler_check_mk') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'handler' . DS . 'controller' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_check_mk_helper_tag') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'helper' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_check_mk_dao_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'dao' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_check_mk_reportview_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'reportview' . DS . $p_classname . '.class.php';
        }
        else if ($p_classname === 'isys_auth_check_mk')
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . 'auth' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_check_mk_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'check_mk' . DS . str_replace('_', DS, substr($p_classname, 14)) . DS . $p_classname . '.class.php';
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