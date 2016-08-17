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
class isys_module_cmdb_autoload extends isys_module_manager_autoload
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
        try
        {

            // CMDB components.
            if (strpos($p_classname, "isys_cmdb") === 0)
            {
                // CMDB DAO component.
                if (strpos($p_classname, "isys_cmdb_dao") === 0)
                {
                    // CMDB DAO category component.
                    if (strpos($p_classname, "isys_cmdb_dao_category") === 0)
                    {
                        if (strpos($p_classname, "isys_cmdb_dao_category_g_") === 0)
                        {
                            // DAO component for global category.
                            $l_path = __DIR__ . "/dao/category/global/";
                        }
                        else if (strpos($p_classname, "isys_cmdb_dao_category_s_") === 0)
                        {
                            // DAO component for specific category.
                            $l_path = __DIR__ . "/dao/category/specific/";
                        }
                        else
                        {
                            // DAO component for rest.
                            $l_path = __DIR__ . "/dao/category/";
                        } // if
                    }
                    else if (strpos($p_classname, "isys_cmdb_dao_object") === 0)
                    {
                        $l_path = __DIR__ . "/dao/object/";
                    }
                    else if (strpos($p_classname, "isys_cmdb_dao_list_interface") === 0)
                    {
                        $p_classname = "isys_cmdb_dao_list";
                        $l_path      = __DIR__ . "/dao/list/";
                    }
                    else if (strpos($p_classname, "isys_cmdb_dao_list") === 0)
                    {
                        // All CMDB list components.
                        $l_path = __DIR__ . "/dao/list/";
                    }
                    else
                    {
                        // All other CMDB DAO components.
                        $l_path = __DIR__ . "/dao/";
                    } // if
                }
                else if (strpos($p_classname, "isys_cmdb_ui") === 0)
                {
                    // CMDB UI components.

                    if (strpos($p_classname, "isys_cmdb_ui_category_g_") === 0)
                    {
                        // UI component for global category.
                        $l_path = __DIR__ . "/ui/global/";
                    }
                    else if (strpos($p_classname, "isys_cmdb_ui_category_s_") === 0)
                    {
                        // UI component for specific category.
                        $l_path = __DIR__ . "/ui/specific/";
                    }
                    else
                    {
                        // UI component for rest.
                        $l_path = __DIR__ . "/ui/";
                    } // if
                }
                else if (strpos($p_classname, "isys_cmdb_view") === 0)
                {
                    // CMDB view component.
                    $l_path = __DIR__ . "/view/";
                }
                else if (strpos($p_classname, "isys_cmdb_action") === 0)
                {
                    // CMDB action component.
                    $l_path = __DIR__ . "/action/";
                } // if
            }
            else if (strpos($p_classname, "isys_controller_cmdb") === 0)
            {
                // DAO component for global category.
                $l_path = __DIR__ . "/controller/";
            }
            else if (strpos($p_classname, "isys_auth_cmdb") === 0)
            {
                $l_path = __DIR__ . "/auth/";

            }
            else if (strpos($p_classname, "isys_auth_dao_cmdb") === 0)
            {
                $l_path = __DIR__ . "/auth/dao/";

            }
            else if (strpos($p_classname, "isys_auth") === 0)
            {
                if ($p_classname === 'isys_auth_import')
                {
                    $l_path = __DIR__ . '/../import/auth/';
                }
                else if ($p_classname === 'isys_auth_logbook')
                {
                    $l_path = __DIR__ . '/../logbook/auth/';
                }
                else if ($p_classname === 'isys_auth_search')
                {
                    $l_path = __DIR__ . '/../search/auth/';
                }
                else if ($p_classname === 'isys_auth_auth')
                {
                    $l_path = __DIR__ . '/../auth/auth/';
                }
                else if (strpos($p_classname, "isys_auth_system") === 0)
                {
                    $l_path = __DIR__ . '/../system/auth/';
                } // if
            } // if
            else if ($p_classname == 'isys_module_cmdb_eventhandler')
            {
                $l_path = __DIR__ . '/';
            }

            if (isset($l_path))
            {
                $l_path = $l_path . $p_classname . '.class.php';

                if (file_exists($l_path) && include_once($l_path))
                {
                    isys_caching::factory('autoload')
                        ->set($p_classname, $l_path);

                    return true;
                } // if
            } // if
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return false;
    } // function
} // class