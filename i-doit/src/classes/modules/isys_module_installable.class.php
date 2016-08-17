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
 * Interface isys_module_installable
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface isys_module_installable
{
    /**
     * Checks wheather a module is installed or not, should return the module id.
     *
     * @param string $p_identifier
     * @param bool   $p_and_active
     *
     * @return int]false
     */
    public function is_installed($p_identifier = null, $p_and_active = false);
} // interface