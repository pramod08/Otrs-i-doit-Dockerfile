<?php

/**
 * i-doit
 * Class autoloader.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_quick_configuration_wizard_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @static
     *
     * @param   string $p_classname
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function init($p_classname)
    {
        try
        {
            if ($p_classname === 'isys_quick_configuration_wizard_dao')
            {
                $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'quick_configuration_wizard' . DS . 'dao' . DS . 'isys_quick_configuration_wizard_dao.class.php';
            }
            else if ($p_classname === 'isys_ajax_handler_quick_configuration_wizard')
            {
                $l_path = DS . 'src' . DS . 'classes' . DS . 'modules' . DS . 'quick_configuration_wizard' . DS . 'ajax' . DS . 'isys_ajax_handler_quick_configuration_wizard.class.php';
            } // if

            if (!empty($l_path) && parent::include_file($l_path))
            {
                isys_caching::factory('autoload')
                    ->set($p_classname, $l_path);

                return true;
            } // if
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return false;
    } // function
} // class