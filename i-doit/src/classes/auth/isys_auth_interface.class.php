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
 * Auth: Interface
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface isys_auth_interface
{
    /**
     * Main check() method for auth classes
     *
     * @param   integer $p_right
     * @param   string  $p_path
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function check($p_right, $p_path);

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods();

    /**
     * Get ID of related module.
     *
     * @return  integer
     */
    public function get_module_id();

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title();

    /**
     * Check, if user has a baseright.
     *
     * @param   string $p_master_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function has($p_master_right);

    /**
     * Checks if there exists any path for the current module.
     *
     * @return  boolean
     * @authro  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function has_any_rights_in_module();

    /**
     * This method will process the exact same code as "check()" but will return a boolean value without any exceptions.
     *
     * @param   integer $p_right
     * @param   string  $p_path
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function is_allowed_to($p_right, $p_path);

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return self
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance();
} // interface