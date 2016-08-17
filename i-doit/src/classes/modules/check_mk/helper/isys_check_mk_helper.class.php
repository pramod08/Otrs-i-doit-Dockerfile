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
 * Check_MK helper.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_check_mk_helper
{
    /**
     * Static instance of the modules DAO.
     *
     * @var  isys_check_mk_dao
     */
    protected static $m_dao = null;
    /**
     * Static instance of the database component.
     *
     * @var  isys_component_database
     */
    protected static $m_db = null;
    /**
     * Static array of the dynamic Check_MK tags.
     *
     * @var  array
     */
    protected static $m_dynamic_tags = [];
    /**
     * Variable which tells us if the dynamic tags have been loaded.
     *
     * @var  boolean
     */
    protected static $m_dynamic_tags_loaded = false;
    /**
     * This array holds various information about the different host states.
     *
     * @var  array
     */
    protected static $m_host_states = [
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__UP          => [
            'state'    => 'UP',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__UP,
            'color'    => 'green',
            'icon'     => 'icons/silk/tick.png'
        ],
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__DOWN        => [
            'state'    => 'DOWN',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__DOWN,
            'color'    => 'red',
            'icon'     => 'icons/silk/delete.png'
        ],
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNREACHABLE => [
            'state'    => 'UNREACHABLE',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNREACHABLE,
            'color'    => 'blue',
            'icon'     => 'icons/silk/information.png'
        ]
    ];
    /**
     * Has the helper been initialized?
     *
     * @var  boolean
     */
    protected static $m_initialized = false;
    /**
     * This array holds various information about the different states.
     *
     * @var  array
     */
    protected static $m_states = [
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__OK       => [
            'state'    => 'OK',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__OK,
            'color'    => 'green',
            'icon'     => 'icons/silk/tick.png'
        ],
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__WARNING  => [
            'state'    => 'WARNING',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__WARNING,
            'color'    => 'yellow',
            'icon'     => 'icons/silk/error.png'
        ],
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__CRITICAL => [
            'state'    => 'CRITICAL',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__CRITICAL,
            'color'    => 'red',
            'icon'     => 'icons/silk/delete.png'
        ],
        C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNKNOWN  => [
            'state'    => 'UNKNOWN',
            'state_id' => C__MODULE__CHECK_MK__LIVESTATUS_STATE__UNKNOWN,
            'color'    => 'blue',
            'icon'     => 'icons/silk/information.png'
        ]
    ];
    /**
     * Static array for saving various information for several methods.
     *
     * @var  array
     */
    protected static $m_tmp = [
        'hostnames' => [],
    ];

    /**
     * Initialize method for setting some initial stuff.
     *
     * @static
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function init()
    {
        if (self::$m_db === null)
        {
            global $g_comp_database;

            self::$m_db = $g_comp_database;
        } // if

        if (self::$m_dao === null)
        {
            self::$m_dao = isys_factory::get_instance('isys_check_mk_dao', self::$m_db);
        } // if

        self::$m_initialized = true;
    } // function

    /**
     * Static method to retrieve informations about the different states.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_state_info()
    {
        return self::$m_states;
    } // function

    /**
     * Static method to retrieve informations about the different host states.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_host_state_info()
    {
        return self::$m_host_states;
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
        if (!self::$m_initialized)
        {
            self::init();
        } // if

        if (array_key_exists($p_obj_id, self::$m_tmp['hostnames']))
        {
            return trim(self::$m_tmp['hostnames'][$p_obj_id]);
        } // if

        $l_row = isys_cmdb_dao_category_g_cmk::instance(self::$m_db)
            ->get_data(null, $p_obj_id)
            ->get_row();

        if ($l_row !== false)
        {
            switch ($l_row['isys_catg_cmk_list__host_name_selection'])
            {
                case C__CATG_CHECK_MK__NAME_SELECTION__INPUT:
                    return self::$m_tmp['hostnames'][$p_obj_id] = trim($l_row['isys_catg_cmk_list__host_name']);

                case C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME_FQDN:
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

                case C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME:
                    return self::$m_tmp['hostnames'][$p_obj_id] = trim(
                        isys_cmdb_dao_category_g_ip::instance(self::$m_db)
                            ->get_primary_ip($p_obj_id)
                            ->get_row_value('isys_catg_ip_list__hostname')
                    );

                case C__CATG_CHECK_MK__NAME_SELECTION__OBJ_ID:
                    return self::$m_tmp['hostnames'][$p_obj_id] = self::prepare_valid_name($l_row['isys_obj__title']);
            } // switch
        } // if

        return '';
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
     * Loads / Retrieves all dynamic tags.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function load_dynamic_tags()
    {
        if (!self::$m_initialized)
        {
            self::init();
        } // if

        if (!self::$m_dynamic_tags_loaded)
        {
            $l_res = self::$m_dao->get_dynamic_tag_data();

            if (count($l_res) > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    self::$m_dynamic_tags[] = [
                        'id'        => $l_row['isys_check_mk_dynamic_tags__id'],
                        'condition' => $l_row['isys_check_mk_dynamic_tags__condition'],
                        'param'     => $l_row['isys_check_mk_dynamic_tags__param'],
                        'tags'      => isys_format_json::decode($l_row['isys_check_mk_dynamic_tags__tags'], true),
                        'status'    => $l_row['isys_check_mk_dynamic_tags__status']
                    ];
                } // while
            } // if

            self::$m_dynamic_tags_loaded = true;
        } // if

        return self::$m_dynamic_tags;
    } // function

    /**
     * Retrieve all dynamic tags with a certain condition.
     *
     * @param   integer $p_condition
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_dynamic_tag_by_condition($p_condition)
    {
        $l_return = [];

        foreach (self::load_dynamic_tags() as $l_tag)
        {
            if ($l_tag['condition'] == $p_condition)
            {
                $l_return[] = $l_tag;
            } // if
        } // foreach

        return $l_return;
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