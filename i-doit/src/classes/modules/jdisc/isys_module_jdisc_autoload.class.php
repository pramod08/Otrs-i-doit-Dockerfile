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
class isys_module_jdisc_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @param   string $p_classname
     *
     * @return  boolean
     */
    public static function init($p_classname)
    {
        if (strpos($p_classname, 'isys_jdisc_dao') === 0)
        {
            if (parent::include_file(($l_path = '/src/classes/modules/jdisc/dao/' . $p_classname . '.class.php')))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        }
        if (strpos($p_classname, 'isys_cmdb_dao_jdisc') === 0)
        {
            if (parent::include_file(($l_path = '/src/classes/modules/jdisc/cmdb/dao/' . $p_classname . '.class.php')))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        }
        if (strpos($p_classname, 'isys_ajax_handler_jdisc') === 0)
        {
            if (parent::include_file(($l_path = '/src/classes/modules/jdisc/handler/ajax/' . $p_classname . '.class.php')))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        }
        if (strpos(' ' . $p_classname, 'isys_handler_jdisc'))
        {
            if (parent::include_file(($l_path = '/src/classes/modules/jdisc/handler/controller/' . $p_classname . '.class.php')))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        }
        if ($p_classname == 'isys_popup_duplicate_jdisc_profile')
        {
            if (parent::include_file(($l_path = '/src/classes/modules/jdisc/popups/' . $p_classname . '.class.php')))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        }

        if (strpos(' ' . $p_classname, 'isys_cmdb_dao_category_g_jdisc_'))
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'jdisc' . DS . 'cmdb' . DS . 'dao' . DS . 'global' . DS . $p_classname . '.class.php';
            if (parent::include_file(($l_path)))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        } // if

        if (strpos(' ' . $p_classname, 'isys_cmdb_dao_list_catg_jdisc_'))
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'jdisc' . DS . 'cmdb' . DS . 'dao' . DS . 'list' . DS . $p_classname . '.class.php';
            if (parent::include_file(($l_path)))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        } // if

        if (strpos(' ' . $p_classname, 'isys_cmdb_ui_category_g_jdisc_'))
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'jdisc' . DS . 'cmdb' . DS . 'ui' . DS . 'global' . DS . $p_classname . '.class.php';
            if (parent::include_file(($l_path)))
            {
                isys_caching::factory('autoload')
                    ->add($p_classname, $l_path);

                return true;
            }
        } // if

        return false;
    } // function
} // class