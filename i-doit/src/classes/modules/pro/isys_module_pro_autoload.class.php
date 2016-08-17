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
 * i-doit PRO
 *
 * Class autoloader.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.3
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_pro_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @param   string $p_classname
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function init($p_classname)
    {
        if (strpos($p_classname, 'isys_pro_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_dao_category_s') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'cmdb' . DS . 'dao' . DS . 'specific' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_ui_category_s') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'cmdb' . DS . 'ui' . DS . 'specific' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_dao_category_g') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'cmdb' . DS . 'dao' . DS . 'global' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_ui_category_g') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'cmdb' . DS . 'ui' . DS . 'global' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_cmdb_view') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'cmdb' . DS . 'view' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_dashboard_widgets_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'dashboard' . DS . 'widgets' . DS . str_replace(
                    '_',
                    DS,
                    substr($p_classname, 23)
                ) . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_ajax_handler') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'ajax' . DS . 'handler' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_visualization_export') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . 'export' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_visualization_graph_visitor') === 0 || strpos($p_classname, 'isys_visualization_tree_visitor') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . 'visitor' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_visualization_graph') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . 'graph' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_visualization_profile') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . 'profile' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_visualization_tree') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . 'tree' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_visualization') === 0 || strpos($p_classname, 'isys_visualization_model') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . $p_classname . '.class.php';
        }
        else if (strpos($p_classname, 'isys_popup_visualization_') === 0)
        {
            $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'pro' . DS . 'visualization' . DS . 'popup' . DS . $p_classname . '.class.php';
        } // if

        if (!empty($l_path) && parent::include_file($l_path))
        {
            isys_caching::factory('autoload')
                ->set($p_classname, $l_path);

            return true;
        } // if

        return false;
    } // function
} // class