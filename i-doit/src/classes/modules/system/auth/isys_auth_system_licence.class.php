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
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_system_licence extends isys_auth_system
{
    /**
     * Container for singleton instance
     *
     * @var  isys_auth_system_licence
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_system_licence
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null)
        {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        } // if

        if (self::$m_instance === null)
        {
            self::$m_instance = new self;
        } // if

        return self::$m_instance;
    } // function

    /**
     * Method for retrieving the "parameter" in the configuration GUI.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public static function get_licencesettings_parameter()
    {
        return [
            'INSTALLATION' => 'LC__UNIVERSAL__LICENE_INSTALLATION',
            'OVERVIEW'     => 'LC__UNIVERSAL__LICENE_OVERVIEW'
        ];
    } // function

    /**
     * Licence installation rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function installation($p_right)
    {
        return $this->licencesettings($p_right, 'installation');
    } // function

    /**
     * Licence overview rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function overview($p_right)
    {
        return $this->licencesettings($p_right, 'overview');
    } // function
} // class