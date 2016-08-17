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
class isys_auth_system_globals extends isys_auth_system
{
    /**
     * Container for singleton instance
     *
     * @var  isys_auth_system_globals
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_system_globals
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
    public static function get_globalsettings_parameter()
    {
        return [
            'SYSTEMSETTING'       => 'LC__CMDB__TREE__SYSTEM__SETTINGS__SYSTEM',
            'CUSTOMFIELDS'        => 'LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES',
            'QCW'                 => 'LC__CMDB__TREE__SYSTEM__CMDB_CONFIGURATION__QOC',
            'CMDBSTATUS'          => 'LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS',
            'VALIDATION'          => 'LC__CMDB__TREE__SYSTEM__TOOLS__VALIDATION',
            'RELATIONSHIPTYPES'   => 'LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES',
            'ROLESADMINISTRATION' => 'LC__MODULE__SYSTEM__ROLES_ADMINISTRATION',
            'CUSTOMPROPERTIES'    => 'LC__UNIVERSAL__CATEGORY_EXTENSION',
        ];
    } // function

    /**
     * Systemsetting rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function systemsetting($p_right)
    {
        return $this->globalsettings($p_right, 'systemsetting');
    } // function

    /**
     * Customfields rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function customfields($p_right)
    {
        return $this->globalsettings($p_right, 'customfields');
    } // function

    /**
     * QCW rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function qcw($p_right)
    {
        return $this->globalsettings($p_right, 'qcw');
    } // function

    /**
     * CMDB-Status rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function cmdbstatus($p_right)
    {
        return $this->globalsettings($p_right, 'cmdbstatus');
    } // function

    /**
     * CMDB-Status rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function relationshiptypes($p_right)
    {
        return $this->globalsettings($p_right, 'relationshiptypes');
    } // function

    /**
     * Roles administration rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function rolesadministration($p_right)
    {
        return $this->globalsettings($p_right, 'rolesadministration');
    } // function
} // class