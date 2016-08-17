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
 * @author      Dennis Stücken
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

try
{
    $l_opt = getopt("mf:i:a:");

    if (isset($l_opt['a']))
    {
        switch ($l_opt['a'])
        {
            case 'installModule':
                if (isset($l_opt['f']))
                {
                    if (file_exists($l_opt['f']))
                    {
                        global $g_comp_database_system, $l_dao_mandator;
                        $l_dao_mandator = new isys_component_dao_mandator($g_comp_database_system);

                        if (install_module_by_zip($l_opt['f'], isset($l_opt['i']) ? $l_opt['i'] : '0'))
                        {
                            echo "Module installed/updated.\n";
                            die;
                        }
                        else
                        {
                            throw new Exception('Could not install module. File "' . $l_opt['f'] . '" was not found');
                        } // if
                    } // if
                } // if
                break;
        } // switch
    } // if

    throw new Exception("Usage: php index.php -a installModule -f modulefile.zip [-i mandatorID]");
}
catch (Exception $e)
{
    echo $e->getMessage() . "\n";
} // try