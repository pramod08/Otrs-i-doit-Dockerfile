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
 * Class autoloader for search module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.5.4, 1.6
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_search_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @return boolean
     *
     * @param string $p_classname
     */
    public static function init($p_classname)
    {
        try
        {
            if (strpos($p_classname, 'isys_search_filter') === 0)
            {
                if (parent::include_file(($l_path = '/src/classes/modules/search/filter/' . $p_classname . '.class.php')))
                {
                    isys_caching::factory('autoload')
                        ->add($p_classname, $l_path);

                    return true;
                }
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }

        return false;
    }

}