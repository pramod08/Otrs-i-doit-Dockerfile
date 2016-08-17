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
 * Nagios helper for misc. export functions.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_nagios_helper
{
    /**
     * Static instance of the database component.
     *
     * @var  isys_component_database
     */
    protected static $m_db = null;
    /**
     * This variable defines, if this helper has been initialized.
     *
     * @var  boolean
     */
    protected static $m_initialized = false;
    /**
     * Static array for saving various information for several methods.
     *
     * @var  array
     */
    protected static $m_tmp = [
        'hostnames' => [],
        'address'   => []
    ];

    /**
     * Initialize method for setting some initial stuff.
     *
     * @static
     *
     * @param   array  An optional set of options. Javascript style!
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function init(array $p_options = [])
    {
        global $g_comp_database;

        $l_default_options = [
            'database_component' => $g_comp_database
        ];

        $l_options = array_merge($l_default_options, $p_options);

        self::$m_db = $l_options['database_component'];

        self::$m_initialized = true;
    } // function

    /**
     * Method for retrieving a timeperiod via Dialog or new secondary Dialog+.
     *
     * @static
     *
     * @param   integer $p_timeperiod
     * @param   integer $p_timeperiod_plus
     *
     * @return  mixed  String with the timeperiod string, or false if none could be found.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_timeperiod($p_timeperiod = null, $p_timeperiod_plus = null)
    {
        if (!self::$m_initialized)
        {
            self::init();
        } // if

        if ($p_timeperiod > 0)
        {
            $l_timeperiod = isys_factory::get_instance('isys_component_dao_nagios', self::$m_db)
                ->getTimeperiod($p_timeperiod);

            if (is_array($l_timeperiod) && !empty($l_timeperiod['name']))
            {
                return $l_timeperiod['name'];
            } // if
        }
        else if ($p_timeperiod_plus > 0)
        {
            $l_timeperiod = isys_factory_cmdb_dialog_dao::get_instance(self::$m_db, 'isys_nagios_timeperiods_plus')
                ->get_data($p_timeperiod_plus);

            if (is_array($l_timeperiod) && !empty($l_timeperiod['isys_nagios_timeperiods_plus__title']))
            {
                return $l_timeperiod['isys_nagios_timeperiods_plus__title'];
            } // if
        } // if

        return false;
    } // function

    /**
     * Method for retrieving a command via Dialog or new secondary Dialog+.
     *
     * @static
     *
     * @param   integer $p_command
     * @param   integer $p_command_plus
     * @param   string  $p_parameter
     *
     * @return  mixed  String with the timeperiod string, or false if none could be found.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_command($p_command = null, $p_command_plus = null, $p_parameter = '')
    {
        if (!self::$m_initialized)
        {
            self::init();
        } // if

        if ($p_command > 0)
        {
            $l_command = isys_factory::get_instance('isys_component_dao_nagios', self::$m_db)
                ->getCommand($p_command);

            if (is_array($l_command) && !empty($l_command['name']))
            {
                return $l_command['name'] . (empty($p_parameter) ? '' : '!' . $p_parameter);
            } // if
        }
        else if ($p_command_plus > 0)
        {
            $l_command = isys_factory_cmdb_dialog_dao::get_instance(self::$m_db, 'isys_nagios_commands_plus')
                ->get_data($p_command_plus);

            if (is_array($l_command) && !empty($l_command['isys_nagios_commands_plus__title']))
            {
                return $l_command['isys_nagios_commands_plus__title'] . (empty($p_parameter) ? '' : '!' . $p_parameter);
            } // if
        } // if

        return false;
    } // function

    /**
     * Static method for retrieving the correct contact name, by the given option.
     *
     * @static
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_correct_contact_name($p_obj_id)
    {
        if (!self::$m_initialized)
        {
            self::init();
        } // if

        try
        {
            $l_person = isys_cmdb_dao_category_s_person_nagios::instance(self::$m_db)
                ->get_data(null, $p_obj_id)
                ->get_row();

            // Check, which name shall be exported.
            switch ($l_person['isys_cats_person_nagios_list__contact_name_selection'])
            {
                case C__NAGIOS__PERSON_OPTION__INPUT:
                    return $l_person['isys_cats_person_nagios_list__contact_name'];

                case C__NAGIOS__PERSON_OPTION__USERNAME:
                    return isys_cmdb_dao_category_s_person_login::instance(self::$m_db)
                        ->get_data(null, $p_obj_id)
                        ->get_row_value('isys_cats_person_list__title');

                default:
                case C__NAGIOS__PERSON_OPTION__OBJECT_TITLE:
                    return isys_cmdb_dao::instance(self::$m_db)
                        ->get_obj_name_by_id_as_string($p_obj_id);
            } // switch
        }
        catch (Exception $e)
        {
            return 'ERROR: ' . $e->getMessage();
        } // try
    } // function

    /**
     * Method for converting invalid names like "Peter Griffin " to valid "peter_griffin".
     *
     * @param   string $p_value
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function prepare_valid_name($p_value)
    {
        return preg_replace('~[\s]+~', '_', isys_glob_strip_accent(trim($p_value)));
    } // function

    /**
     * Method for retrieving the hostname of the given object.
     *
     * @static
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function render_export_hostname($p_obj_id)
    {
        if (array_key_exists($p_obj_id, self::$m_tmp['hostnames']))
        {
            return trim(self::$m_tmp['hostnames'][$p_obj_id]);
        } // if

        if (!self::$m_initialized)
        {
            self::init();
        } // if

        $l_nagios_row = isys_cmdb_dao_category_g_nagios::instance(self::$m_db)
            ->get_data(null, $p_obj_id)
            ->get_row();

        if ($l_nagios_row !== false)
        {
            switch ($l_nagios_row['isys_catg_nagios_list__host_name_selection'])
            {
                case C__CATG_NAGIOS__NAME_SELECTION__INPUT:
                    return self::$m_tmp['hostnames'][$p_obj_id] = trim($l_nagios_row['isys_catg_nagios_list__host_name']);

                case C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN:
                    $l_dns_domain = trim(
                        isys_cmdb_dao_category_g_ip::instance(self::$m_db)
                            ->get_assigned_dns_domain($p_obj_id)
                            ->get_row_value('isys_net_dns_domain__title')
                    );

                    if ($l_dns_domain !== null && !empty($l_dns_domain))
                    {
                        $l_dns_domain = '.' . $l_dns_domain;
                    } // if

                    return self::$m_tmp['hostnames'][$p_obj_id] = trim(
                            isys_cmdb_dao_category_g_ip::instance(self::$m_db)
                                ->get_primary_ip($p_obj_id)
                                ->get_row_value('isys_catg_ip_list__hostname')
                        ) . $l_dns_domain;

                case C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME:
                    return self::$m_tmp['hostnames'][$p_obj_id] = trim(
                        isys_cmdb_dao_category_g_ip::instance(self::$m_db)
                            ->get_primary_ip($p_obj_id)
                            ->get_row_value('isys_catg_ip_list__hostname')
                    );

                case C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID:
                    return self::$m_tmp['hostnames'][$p_obj_id] = self::prepare_valid_name($l_nagios_row['isys_obj__title']);
            } // switch
        } // if

        return '';
    } // function

    /**
     * Method for retrieving the address of the given object.
     *
     * @static
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function render_export_address($p_obj_id)
    {
        if (array_key_exists($p_obj_id, self::$m_tmp['address']))
        {
            return trim(self::$m_tmp['address'][$p_obj_id]);
        } // if

        if (!self::$m_initialized)
        {
            self::init();
        } // if

        $l_dao_ip     = isys_cmdb_dao_category_g_ip::instance(self::$m_db);
        $l_nagios_row = isys_cmdb_dao_category_g_nagios::instance(self::$m_db)
            ->get_data(null, $p_obj_id)
            ->get_row();

        $l_address_selection = $l_nagios_row['isys_catg_nagios_list__isys_catg_ip_list__id'];

        if (empty($l_address_selection))
        {
            $l_address_selection = $l_dao_ip->get_primary_ip($p_obj_id)
                ->get_row_value('isys_catg_ip_list__id');
        } // if

        $l_ip = $l_dao_ip->get_ip_by_id($l_address_selection);

        if ($l_ip !== false)
        {
            switch ($l_nagios_row['isys_catg_nagios_list__address_selection'])
            {

                default:
                case C__CATG_NAGIOS__NAME_SELECTION__IP:
                    if (!empty($l_ip['isys_cats_net_ip_addresses_list__title']))
                    {
                        return self::$m_tmp['address'][$p_obj_id] = $l_ip['isys_cats_net_ip_addresses_list__title'];
                    } // if

                // If there is no IP-address, use the hostname as fallback (no break statement here!!).

                case C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME:
                    return self::$m_tmp['address'][$p_obj_id] = trim($l_ip['isys_catg_ip_list__hostname']);

                case C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN:
                    $l_dns_domain = trim(
                        $l_dao_ip->get_assigned_dns_domain($p_obj_id, $l_address_selection)
                            ->get_row_value('isys_net_dns_domain__title')
                    );

                    if ($l_dns_domain !== null && !empty($l_dns_domain))
                    {
                        $l_dns_domain = '.' . $l_dns_domain;
                    } // if

                    return self::$m_tmp['address'][$p_obj_id] = trim($l_ip['isys_catg_ip_list__hostname']) . $l_dns_domain;
            } // switch
        } // if

        return '';
    } // function

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
        ;
    } // function

    /**
     * Private constructor - Singleton!
     */
    private function __construct()
    {
        ;
    } // function
} // class