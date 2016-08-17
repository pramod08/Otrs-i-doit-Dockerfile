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
 * Module initializer
 *
 * @package     modules
 * @subpackage  nostalgia
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.7
 */

// Autoloader.
if (include_once('isys_module_nostalgia_autoload.class.php'))
{
    spl_autoload_register('isys_module_nostalgia_autoload::init');
} // if