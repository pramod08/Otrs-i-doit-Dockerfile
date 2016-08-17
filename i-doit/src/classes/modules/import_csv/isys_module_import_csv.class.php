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
use idoit\Component\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;

/**
 * i-doit
 *
 * CSV Import
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_module_import_csv implements isys_module_interface
{
    /**
     * @see  self::get_csv().
     */
    const CL__GET__HEAD                = 'csv_head';
    const CL__GET__CONTENT             = 'csv_content';
    const CL__GET__HEAD_LENGTH         = 'csv_head_length';
    const CL__GET__CONTENT__FIRST_LINE = 'csv_content_firstline';
    const CL__GET__CONTENT__HEADSIZE   = 'csv_headsize';
    /**
     * @see  self::get_importable_categories().
     */
    const CL__MULTIVALUE_TYPE__COLUMN   = 'multivalue_column';
    const CL__MULTIVALUE_TYPE__ROW      = 'multivalue_row';
    const CL__MULTIVALUE_TYPE__COMMA    = 'multivalue_comma';
    const CL__MULTIVALUE_LINE_SEPARATOR = '#-separator-#';
    /**
     * @see  self::create_category_map().
     */
    const CL__CAT__ID                     = 'cat_id';
    const CL__CAT__TITLE                  = 'cat_title';
    const CL__CAT__PROPERTIES             = 'cat_properties';
    const CL__CAT__MULTIVALUE             = 'cat_multivalue';
    const CL__CAT__PARENT                 = 'cat_parent';
    const CL__CAT__TABLE                  = 'cat_table';
    const CL__CAT__CAT_TYPE               = 'cat_ctype';
    const CL__CAT__DATA_FIELD             = 'cat_data_field'; // GLOBAL, SPECIFIC, CUSTOM
    const CL__CAT__CONSTANT               = 'cat_constant';
    const CL__CAT__CLASS                  = 'cat_classname';
    const CL__CAT__TYPE                   = 'cat_type';
    const CL__CAT__PROPERTY__TITLE        = 'property_title'; // EDIT, READ, ASSIGN
    const CL__CAT__PROPERTY__ROW          = 'property_row';
    const CL__CAT__PROPERTY__MODE         = 'property_mode';
    const CL__CAT__PROPERTY__PARAM        = 'property_param';
    const CL__CAT__PROPERTY__TAG          = 'property_tag';
    const CL__CAT__PROPERTY__VISIBLE      = 'property_visible';
    const CL__CAT__PROPERTY__TYPE         = 'property_type';
    const CL__CAT__PROPERTY__ESSENTIALITY = 'property_essentiality';
    const CL__CAT__PROPERTY__FORMTAG      = 'property_formtag';
    const CL__CAT__PROPERTY__REFERENCES   = 'property_references';
    const CL__CAT__PROPERTY__IDENTIFIER   = 'property_identifier'; // @see ID-3183 This will be used for custom category dialog+ fields
    const CL__CAT__PROPERTY__TABLE        = 'property_table'; // @see ID-3188 Multiselects should be treated like dialog+
    /**
     * @see  self::handle_multivalue_category();
     */
    const CL__MULTIVALUE_MODE__UNTOUCHED = 'm_untouched';
    const CL__MULTIVALUE_MODE__ADD       = 'm_add';
    const CL__MULTIVALUE_MODE__OVERWRITE = 'm_overwrite';
    /**
     * Constants for datastructur
     *
     * @see  $this->create_data_structure();
     */
    const CL__CAT__DATA           = 'category_data';
    const CL__OBJECT_TYPE         = 'object_type';
    const CL__OBJECT_TITLE        = 'object_title';
    const CL__OBJECT_PURPOSE      = 'object_purpose';
    const CL__OBJECT_CATEGORY     = 'object_category';
    const CL__OBJECT_TAGS         = 'object_tags';
    const CL__OBJECT_SYSID        = 'object_sysid';
    const CL__OBJECT_HOSTNAME     = 'object_hostname';
    const CL__OBJECT_CMDBSTATUS   = 'object_cmdbstatus';
    const CL__OBJECT_DESCRIPTION  = 'object_description';
    const CL__SYN_PROPERTY        = 'properties';
    const CL__HELPER__VALUE       = 'helper_value';
    const CL__HELPER__UNIT_ROW    = 'helper_unit_row';
    const CL__HELPER__UNIT_TAG    = 'helper_unit_tag';
    const CL__HELPER__UNIT_ID     = 'helper_unit_id';
    const CL__UNUSED__DATA        = 'unused_data';
    const CL__OBJECT_MODE__CREATE = 'object_create';
    const CL__OBJECT_MODE__UPDATE = 'object_update';
    /**
     * Some "import step" constants.
     */
    const CL__IMPORT_STEP__CONSTRUCT     = 'step_construct';
    const CL__IMPORT_STEP__INITIALIZE    = 'step_initialize';
    const CL__IMPORT_STEP__ARRANGE       = 'step_arrange';
    const CL__IMPORT_STEP__DATASTRUCTURE = 'step_datastructure';
    const CL__IMPORT_STEP__IMPORT        = 'step_import';
    const CL__IMPORT_STEP__FINISHED      = 'step_finished';
    /**
     * @var  boolean
     */
    protected static $m_licenced = true;
    /**
     * @var  bool
     */
    private static $m_activate_caching = true;
    /**
     * List of supported helper.
     *
     * @see  self::create_category_map()
     * @var  array
     */
    private static $m_allowed_properties = [
        'LC__UNIVERSAL__YES_NO'                        => 'get_yes_or_no',
        'LC__UNIVERSAL__DIALOG'                        => 'dialog',
        'Dialog'                                       => 'model_title',
        'LC__UNIVERSAL__DIALOG_PLUS'                   => 'dialog_plus',
        'LC__CMDB__LOGBOOK__DATE'                      => 'date',
        'LC__UNIVERSAL__TIME_PERIOD'                   => 'timeperiod',
        'LC__CMDB__CATG__UNIT'                         => 'convert',
        'LC__CMDB__CATG__POWER_CONSUMER_CONNECTION'    => 'connection',
        'LC__CMDB__CATG__REFERENCED_VALUE'             => 'get_reference_value',
        'LC_UNIVERSAL__OBJECT'                         => 'object',
        'Position'                                     => 'location_property_pos',
        'Location'                                     => 'location',
        'Money'                                        => 'money_format',
        'LC__CMDB__CATG__GLOBAL_CONTACT'               => 'contact',
        'Hostname'                                     => 'hostname_handler',
        'LC__UNIVERSAL__CUSTOM_DIALOG_PLUS'            => 'custom_category_property_dialog_plus',
        'LC__MODULE__CUSTOM_FIELDS__OBJECT_BROWSER'    => 'custom_category_property_object',
        'LC_UNIVERSAL__DATE'                           => 'custom_category_property_calendar',
        'LC__CATG__WAN__ROUTER'                        => 'wan_connected_router',
        'LC__CATG__WAN__NET'                           => 'wan_connected_net',
        'LC__CMDB__CATS__NET__LAYER2_NET'              => 'layer_2_assignments',
        'LC__UNIVERSAL__CSV_MULTISELECT_VIA_SEMICOLON' => 'dialog_multiselect'
    ];

    /**
     * Callback Register.
     *
     * @var  array
     */
    private static $m_callback_register = [
        'C__CATG__IP'                   => 'callback_ip',
        'C__CMDB__SUBCAT__NETWORK_PORT' => 'callback_port',
        'C__CATG__LOCATION'             => 'callback_location',
        'C__CATG__MODEL'                => 'callback_model',
        'C__CATG__CONTACT'              => 'callback_contact'
    ];
    /**
     * Category blacklist.
     *
     * @var  array
     */
    private static $m_category_skip = [
        'C__CATG__GLOBAL',
        'C__CATG__RELATION',
        'C__CATG__CLUSTER_ROOT',
        'C__CATG__CLUSTER',
        'C__CATG__TICKETS',
        'C__CATS__RELATION_DETAILS',
        'C__CATS__PARALLEL_RELATION',
        'C__CATS__CHASSIS',
        // Skip folder and childs
        'C__CATS__DATABASE_SCHEMA',
        // Skip folder and childs
        'C__CATS__FILE',
        // Skip folder and childs
        'C__CATS__PERSON_MASTER',
        // Skip folder and childs
        'C__CATS__PERSON_GROUP_MASTER',
        // Skip folder and childs
        'C__CATS__ORGANIZATION_MASTER_DATA',
        // Skip folder and childs
        'C__CATS__APPLICATION_SERVICE_ASSIGNED_OBJ',
        'C__CATS__APPLICATION_DBMS_ASSIGNED_OBJ',
        'C__CATS__LAYER2_NET_ASSIGNED_PORTS',
        'C__CATS__LAYER2_NET_ASSIGNED_LOGICAL_PORTS',
        'C__CATS__NET_DHCP',
        'C__CATS__NET_IP_ADDRESSES',
        'C__CATS__PERSON_NAGIOS',
        'C__CATS__PERSON_GROUP_NAGIOS',
        'C__CATS__ENCLOSURE'
    ];
    /**
     * @var  integer
     */
    private static $m_current_object_id;
    /**
     * @var  integer
     */
    private static $m_current_object_type;
    /**
     * CSV-Delimiter.
     *
     * @var  string
     */
    private static $m_delimiter;
    /**
     * Path to file.
     *
     * @var  string
     */
    private static $m_file;
    /**
     * @var  integer
     */
    private static $m_global_object_type;
    /**
     * @var  integer
     */
    private static $m_global_step;
    /**
     * @var  boolean
     */
    private static $m_header = true;
    /**
     * Table join string which is needed for the identification query.
     *
     * @var  string
     */
    private static $m_identification_joins = '';
    /**
     * @var  boolean
     */
    private static $m_live_category_skip = false;
    /**
     * @var  string
     */
    private static $m_log_essential;
    /**
     * @var  string
     */
    private static $m_log_objecttype;
    /**
     * @var  isys_module_logbook
     */
    private static $m_logb_dao;
    /**
     * Mulitvalue-Mode.
     *
     * @var  string
     */
    private static $m_multivalue_mode;
    /**
     * untouch | overwrite | add
     *
     * @var  string
     */
    private static $m_multivalue_update_mode = null;
    /**
     * @var  integer
     */
    private static $m_object_category;

    /**
     * @var integer
     */
    private static $m_object_tags;

    /**
     * @var  integer
     */
    private static $m_object_cmdbstatus;
    /**
     * @var  integer
     */
    private static $m_object_description;
    /**
     * @var  integer
     */
    private static $m_object_hostname;
    /**
     * @var  integer
     */
    private static $m_object_live_mode = isys_import_handler_cmdb::C__CREATE;
    /**
     * Object-Mode: create or update.
     *
     * @var  string
     */
    private static $m_object_mode = self::CL__OBJECT_MODE__CREATE;
    /**
     * @var  integer
     */
    private static $m_object_purpose;
    /**
     * @var  integer
     */
    private static $m_object_sysid;
    /**
     * @var  integer
     */
    private static $m_object_title;
    /**
     * @var  integer
     */
    private static $m_object_type;
    /**
     * @var  array
     */
    private static $m_object_type_skip = [];
    /**
     * @var  array
     */
    private static $m_prop_search;
    /**
     * Property rules.
     *
     * @var  array
     */
    private static $m_property_rules = [];
    /**
     * Hide properties by column.
     *
     * @see  self::create_category_map().
     * @var  array
     */
    private static $m_property_visibility = [
        'isys_catg_ip_list__isys_net_type__id',
        'isys_catg_ip_list__isys_ip_assignment__id',
        'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
        'isys_catg_ip_list__isys_ipv6_assignment__id',
        'isys_catg_ip_list__isys_catg_port_list__id',
        'isys_catg_ip_list__isys_catg_log_port_list__id',
        'isys_catg_accounting_list__isys_guarantee_period_unit__id'
    ];
    /**
     * @var  isys_cmdb_dao
     */
    private static $m_s_dao;
    /**
     * Variable for deciding wether to overwrite or ignore empty values.
     *
     * @var  boolean
     */
    private static $m_singlevalue_overwrite_empty_values = true;
    /**
     * @var  boolean
     */
    private static $m_step_construct = false;
    /**
     * Contains the position which fields will be used for the identification of existing objects.
     *
     * @var  array
     */
    private static $m_update_csv_idents = [];
    /**
     * Contains identifiers which will be used to identify existing objects.
     *
     * @var  array
     */
    private static $m_update_identifiers = [];
    /**
     * Array which contains information of the identification keys.
     *
     * @var  array
     */
    private static $m_update_identifiers_map = [
        'LC__CMDB__CATG__GLOBAL_TITLE'            => [
            'id'    => C__CATG__GLOBAL,
            'title' => 'LC__CMDB__CATG__GLOBAL',
            'table' => 'isys_obj',
            'field' => 'main_obj.isys_obj__title'
        ],
        'LC__CMDB__CATG__GLOBAL_SYSID'            => [
            'id'    => C__CATG__GLOBAL,
            'title' => 'LC__CMDB__CATG__GLOBAL',
            'table' => 'isys_obj',
            'field' => 'main_obj.isys_obj__sysid'
        ],
        'LC_UNIVERSAL__OBJECT_TYPE'               => [
            'id'    => C__CATG__GLOBAL,
            'title' => 'LC__CMDB__CATG__GLOBAL',
            'table' => [
                'isys_obj_type' => [
                    'main_obj.isys_obj__isys_obj_type__id',
                    'isys_obj_type__id'
                ],
            ],
            'field' => 'isys_obj_type__const'
        ],
        'LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO' => [
            'id'    => C__CATG__ACCOUNTING,
            'title' => 'LC__CMDB__CATG__ACCOUNTING',
            'table' => 'isys_catg_accounting_list',
            'field' => 'isys_catg_accounting_list__inventory_no'
        ],
        'LC__CMDB__CATS__ROOM_NUMBER'             => [
            'id'    => C__CATS__ROOM,
            'title' => 'LC__CMDB__CATS__ROOM',
            'table' => 'isys_cats_room_list',
            'field' => 'isys_cats_room_list__number'
        ],
        'LC__CMDB__CATG__INTERFACE_P_SERIAL'      => [
            'id'    => C__CATG__MODEL,
            'title' => 'LC__CMDB__CATG__MODEL',
            'table' => 'isys_catg_model_list',
            'field' => 'isys_catg_model_list__serial'
        ],
        'LC__CATG__IP_ADDRESS'                    => [
            'id'    => C__CATG__IP,
            'title' => 'LC__CATG__IP_ADDRESS',
            'table' => [
                'isys_catg_ip_list'               => [
                    'main_obj.isys_obj__id',
                    'isys_catg_ip_list__isys_obj__id'
                ],
                'isys_cats_net_ip_addresses_list' => [
                    'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    'isys_cats_net_ip_addresses_list__id'
                ]
            ],
            'field' => 'isys_cats_net_ip_addresses_list__title'
        ],
        'LC__CATP__IP__HOSTNAME'                  => [
            'id'    => C__CATG__IP,
            'title' => 'LC__CATG__IP_ADDRESS',
            'table' => 'isys_catg_ip_list',
            'field' => 'isys_catg_ip_list__hostname'
        ],
        'LC__LOGIN__USERNAME'                     => [
            'id'    => C__CATS__PERSON,
            'title' => 'LC__CONTACT__TREE__PERSON',
            'table' => 'isys_cats_person_list',
            'field' => 'isys_cats_person_list__title'
        ],
        'LC__CMDB__CATG__LOCATION_PARENT'         => [
            'id'    => C__CATG__LOCATION,
            'title' => 'LC__CMDB__CATG__LOCATION',
            'table' => [
                'isys_catg_location_list' => [
                    'main_obj.isys_obj__id',
                    'isys_catg_location_list__isys_obj__id'
                ],
                'isys_obj AS obj1'        => [
                    'obj1.isys_obj__id',
                    'isys_catg_location_list__parentid'
                ]
            ],
            'field' => 'obj1.isys_obj__title'
        ]
    ];
    /**
     * Supported unit tables.
     *
     * @see  self::csv_helper__convert().
     * @var  array
     */
    private static $m_valid_unit_tables = [
        'isys_ac_air_quantity_unit',
        'isys_ac_refrigerating_capacity_unit',
        'isys_depth_unit',
        'isys_frequency_unit',
        'isys_guarantee_period_unit',
        'isys_memory_unit',
        'isys_monitor_unit',
        'isys_san_capacity_unit',
        'isys_stor_unit',
        'isys_temp_unit',
        'isys_unit_of_time',
        'isys_volume_unit',
        'isys_wan_capacity_unit',
        'isys_weight_unit',
        'isys_port_speed'
    ];
    /**
     * @var  array
     */
    private $m_assignment_map = [];
    /**
     * @var  array
     */
    private $m_category_map;
    /**
     * @var  array
     */
    private $m_created_object_cache = [];
    /**
     * Instance of the dialog admin DAO.
     *
     * @var  isys_cmdb_dao_dialog_admin
     */
    private $m_dao_dialog;
    /**
     * @var  boolean
     */
    private $m_import_status = true;
    /**
     * The i-doit Logger instance
     *
     * @var  \idoit\Component\Logger
     */
    private $m_log = null;
    /**
     * The directory where the LOG file is to be found.
     *
     * @var  string
     */
    private $m_log_dir = '';
    /**
     * The name of the LOG file.
     *
     * @var  string
     */
    private $m_log_file = '';
    /**
     * The Monolog TestHandler.
     *
     * @var  \Monolog\Handler\TestHandler
     */
    private $m_log_handler = null;
    /**
     * @var  array
     */
    private $m_logbook_entries = [];
    /**
     * This array will be used to define object relations (attached object type, create object if it could not be found).
     *
     * @var array
     */
    private $m_object_type_assignment = [];
    /**
     * @var  array
     */
    private $m_raw_data;
    private $m_record_cycle = [
        C__RECORD_STATUS__NORMAL,
        C__RECORD_STATUS__ARCHIVED => 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED',
        C__RECORD_STATUS__DELETED  => 'C__LOGBOOK_EVENT__CATEGORY_DELETED',
        C__RECORD_STATUS__PURGE    => 'C__LOGBOOK_EVENT__CATEGORY_PURGED',
    ];
    /**
     * This will be set to true if the mapping does not contain a "object-title" but instead first- or lanstname (or "title" of organization / persongroup).
     *
     * @var  array
     */
    private $m_special_title = false;
    /**
     * This array will hold the current column index (used for the helper).
     *
     * @var  integer
     */
    private $m_tmp_index = null;
    /**
     * @var  array
     */
    private $m_transformed_data = []; // function

    /**
     * Gets all identificators as an array.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_update_identificators()
    {
        $l_return = [];

        if (is_array(self::$m_update_identifiers_map))
        {
            foreach (self::$m_update_identifiers_map AS $l_key => $l_value)
            {
                $l_return[$l_key] = _L($l_key) . ' (' . _L($l_value['title']) . ')';
            } // foreach
        } // if

        return $l_return;
    } // function

    /**
     * Returns the category map.
     *
     * @param   boolean $p_generate_new
     *
     * @return  array
     */
    public static function get_category_map($p_generate_new = false)
    {
        if (self::$m_activate_caching === false || $p_generate_new == true)
        {
            return self::create_category_map();
        }
        else
        {
            if (!file_exists(BASE_DIR . isys_module_import::$m_path_to_category_map))
            {
                self::create_category_map();
            } // if

            return unserialize(file_get_contents(BASE_DIR . isys_module_import::$m_path_to_category_map));
        } // if
    } // function

    /**
     * Get csv profiles.
     *
     * @param   integer $p_profile_id
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public static function get_profiles($p_profile_id = null)
    {
        $l_result = [];
        $l_dao    = isys_cmdb_dao::instance(isys_application::instance()->database);
        $l_sql    = 'SELECT * FROM isys_csv_profile';

        if ($p_profile_id !== null && $p_profile_id > 0)
        {
            $l_sql .= ' WHERE isys_csv_profile__id = ' . $l_dao->convert_sql_id($p_profile_id) . ';';
        } // if

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_result[] = [
                    'id'              => $l_row['isys_csv_profile__id'],
                    'title'           => $l_row['isys_csv_profile__title'],
                    'fileinformation' => $l_row['isys_csv_profile__fileinfo'],
                    'data'            => $l_row['isys_csv_profile__data'],
                    'description'     => $l_row['isys_csv_profile__description'],
                ];
            } // while
        } // if

        return $l_result;
    } // function

    /**
     * Save/Create a csv profile.
     *
     * @param   string                  $p_title
     * @param   array                   $p_data
     * @param   integer                 $p_id
     *
     * @return  boolean
     */
    public static function save_profile($p_title, $p_data, $p_id = null)
    {
        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

        if (empty($p_title) && $p_id > 0)
        {
            $l_sql = 'UPDATE isys_csv_profile
				SET isys_csv_profile__data = ' . $l_dao->convert_sql_text(isys_format_json::encode($p_data)) . '
				WHERE isys_csv_profile__id = ' . $l_dao->convert_sql_id($p_id) . ';';
        }
        else
        {
            $l_sql = 'INSERT INTO isys_csv_profile
				SET isys_csv_profile__title = ' . $l_dao->convert_sql_text($p_title) . ',
				isys_csv_profile__data = ' . $l_dao->convert_sql_text(isys_format_json::encode($p_data)) . ';';
        } // if

        return ($l_dao->update($l_sql) && $l_dao->apply_update());
    } // function

    /**
     * Delete profile.
     *
     * @param   integer $p_id
     *
     * @return  bool
     * @throws  isys_exception_dao
     */
    public static function delete_profile($p_id)
    {
        if ($p_id > 0)
        {
            /** @var isys_cmdb_dao $l_dao */
            $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

            return ($l_dao->update('DELETE FROM isys_csv_profile WHERE isys_csv_profile__id = ' . $l_dao->convert_sql_id($p_id) . ';') && $l_dao->apply_update());
        } // if

        return true;
    } // function

    /**
     * Creates the category map and saves it serialized in isys_module_import::$m_path_to_category_map.
     *
     * @return array
     */
    public static function create_category_map()
    {
        global $g_comp_template_language_manager;

        /** @var isys_cmdb_dao $l_dao */
        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

        $l_all_categories = $l_dao->get_all_categories(
            [
                isys_cmdb_dao_category::TYPE_EDIT,
                isys_cmdb_dao_category::TYPE_REAR,
                isys_cmdb_dao_category::TYPE_ASSIGN
            ]
        );

        $l_category_map = [];

        foreach ($l_all_categories as $l_category_type => $l_categories)
        {
            foreach ($l_categories as $l_category)
            {
                // Check whether the dao of category exists.
                if (class_exists($l_category['class_name']) && !in_array($l_category['const'], self::$m_category_skip))
                {
                    // Get instance of dao and retrieve the $m_data_information array.
                    /** @var isys_cmdb_dao_category $l_category_dao */
                    $l_category_dao = new $l_category['class_name'](isys_application::instance()->database);

                    // Set id of custom category
                    if ($l_category_type == C__CMDB__CATEGORY__TYPE_CUSTOM)
                    {
                        /** @var isys_cmdb_dao_category_g_custom_fields $l_category_dao */
                        $l_category_dao->set_catg_custom_id($l_category['id']);
                    } // if

                    if ($l_category_type == C__CMDB__CATEGORY__TYPE_SPECIFIC)
                    {
                        $l_parent_res = $l_category_dao->cats_get_parent_cats($l_category['id'], true);
                        if ($l_parent_res->num_rows() > 0)
                        {
                            $l_parent_const = $l_parent_res->get_row_value('isysgui_cats__const');
                            if (in_array($l_parent_const, self::$m_category_skip))
                            {
                                continue;
                            } // if
                        } // if
                    } // if

                    // Get properties of category.
                    $l_category_data_information = $l_category_dao->get_properties();

                    // Check the count of $m_data_information.
                    if (count($l_category_data_information) > 0)
                    {
                        $l_map_key                  = $l_category['const'];
                        $l_category_map[$l_map_key] = [
                            self::CL__CAT__ID         => $l_category['id'],
                            self::CL__CAT__TITLE      => $g_comp_template_language_manager->get($l_category['title']),
                            self::CL__CAT__MULTIVALUE => (bool) $l_category['list_multi_value'],
                            self::CL__CAT__TABLE      => $l_category_dao->get_table(),
                            self::CL__CAT__CLASS      => $l_category['class_name'],
                            self::CL__CAT__CAT_TYPE   => $l_category_type,
                            self::CL__CAT__CONSTANT   => $l_category['const'],
                            self::CL__CAT__TYPE       => $l_category['type']
                        ];

                        if ($l_category_map[$l_map_key][self::CL__CAT__TABLE] == 'isys_person_2_group_list' || $l_category['source_table'] == 'isys_person_2_group')
                        {
                            $l_category_map[$l_map_key][self::CL__CAT__TABLE] = 'isys_person_2_group';
                        } // if

                        // Check for parent and set data field
                        if ($l_category_type == C__CMDB__CATEGORY__TYPE_GLOBAL)
                        {
                            $l_category_map[$l_map_key][self::CL__CAT__PARENT]     = self::get_parent($l_category['id'], 'g', $l_category_dao);
                            $l_category_map[$l_map_key][self::CL__CAT__DATA_FIELD] = $l_category_map[$l_map_key][self::CL__CAT__TABLE] . '__id';
                        }
                        elseif ($l_category_type == C__CMDB__CATEGORY__TYPE_SPECIFIC)
                        {
                            $l_category_map[$l_map_key][self::CL__CAT__PARENT]     = self::get_parent($l_category['id'], 's', $l_category_dao);
                            $l_category_map[$l_map_key][self::CL__CAT__DATA_FIELD] = $l_category_map[$l_map_key][self::CL__CAT__TABLE] . '__id';
                        }
                        else
                        {
                            $l_category_map[$l_map_key][self::CL__CAT__DATA_FIELD] = $l_category_map[$l_map_key][self::CL__CAT__TABLE] . '__data__id';
                        } // if

                        // Walk through $m_data_information array.
                        foreach ($l_category_data_information AS $l_tag => $l_property)
                        {
                            // Our first condition: Property have to be importable.
                            if ($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT] || $l_tag == 'contact_object')
                            {
                                $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag] = [
                                    self::CL__CAT__PROPERTY__TITLE        => $g_comp_template_language_manager->get($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                                    self::CL__CAT__PROPERTY__ROW          => $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                                    self::CL__CAT__PROPERTY__TAG          => $l_tag,
                                    self::CL__CAT__PROPERTY__ESSENTIALITY => self::get_essentiality(
                                        $l_category_map[$l_map_key][self::CL__CAT__TABLE],
                                        $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                                        $l_dao
                                    ),
                                    self::CL__CAT__PROPERTY__FORMTAG      => $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID],
                                ];

                                // @see ID-3183 This will be used for custom category dialog+ fields
                                if ($l_category_type == C__CMDB__CATEGORY__TYPE_CUSTOM)
                                {
                                    if (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_identifier']) && !empty($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_identifier']))
                                    {
                                        $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__IDENTIFIER] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_identifier'];
                                    } // if
                                } // if

                                // Visibility-Handling.
                                if (in_array($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], self::$m_property_visibility) ||
                                    self::check_property_rules($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) ||
                                    self::check_property_unit($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])
                                )
                                {
                                    $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__VISIBLE] = false;
                                }
                                else
                                {
                                    $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__VISIBLE] = true;
                                } // if

                                // Method-Handling.
                                if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]))
                                {
                                    // Our second condition: Do we support this Method.
                                    if (in_array($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1], self::$m_allowed_properties))
                                    {
                                        // We support the mehtod.
                                        $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__MODE] = $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                                        $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__TYPE] = _L(
                                            array_search(
                                                $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1],
                                                self::$m_allowed_properties
                                            )
                                        );

                                        if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2]))
                                        {
                                            $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__PARAM] = $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2];

                                            /* Do we have an unit property */
                                            if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]))
                                            {
                                                $l_unit_property                                                                               = $l_category_data_information[$l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]];
                                                $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__PARAM] = [
                                                    'method'     => $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__PARAM][0],
                                                    'unit_table' => $l_unit_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                                    'unit_row'   => $l_unit_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                                                    'unit_tag'   => $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]
                                                ];
                                            } // if
                                        } // if

                                        if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                                        {
                                            $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__REFERENCES] = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES];
                                        } // if

                                        if (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']))
                                        {
                                            $l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__TABLE] = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable'];
                                        } // if
                                    }
                                    else
                                    {
                                        /* We don't support the method */

                                        /* CASE:
										 * Helper is unsupported
										 * Row is essential
										 * Row is not the entryID row
										 * Row is not the isys_obj__id row
										 *
										 * We have to skip the whole category to prevent any SQL Exceptions while importing
										 */
                                        if ($l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag][self::CL__CAT__PROPERTY__ESSENTIALITY] && !strstr(
                                                $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                                                '_list__id'
                                            ) && !strstr($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], '_list__isys_obj__id')
                                        )
                                        {
                                            unset($l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag]);
                                            self::add_to_category_skip($l_category['const']);
                                            break;
                                        } // if
                                        unset($l_category_map[$l_map_key][self::CL__CAT__PROPERTIES][$l_tag]);
                                    } // if
                                } // if
                            } // if
                        } // foreach
                    } // if
                } // if
            } // foreach
        } // foreach

        // Clean and modify
        self::clean_category_map($l_category_map);
        self::modify_category_map($l_category_map);

        // Save serialized
        file_put_contents(isys_module_import::$m_path_to_category_map, serialize($l_category_map));

        return $l_category_map;
    } // function

    /**
     * Setter for static variable m_update_identifiers
     *
     * @param $p_value
     */
    public static function set_update_identifiers($p_value)
    {
        self::$m_update_identifiers = $p_value;
    } // function

    /**
     * Setter for static variable m_update_csv_idents
     *
     * @param $p_value
     */
    public static function set_update_csv_idents($p_value)
    {
        self::$m_update_csv_idents = $p_value;
    } // function

    /**
     * Reads the specified csv file and returns the delivered $p_mode area
     *
     * @param   string $p_filepath
     * @param   string $p_delimiter
     * @param   string $p_mode See constants
     *
     * @return  mixed
     * @throws  Exception
     */
    public static function get_csv($p_filepath, $p_delimiter, $p_mode)
    {
        if (file_exists($p_filepath))
        {
            // Enable automatic line ending detection
            ini_set('auto_detect_line_endings', true);
            $l_content = [];
            $l_is_utf8 = true;

            $l_file_res = fopen($p_filepath, 'r');

            if ($p_mode == self::CL__GET__HEAD)
            {
                $l_content = fgetcsv($l_file_res, 0, $p_delimiter);
                $l_is_utf8 = !count(
                    array_filter(
                        $l_content,
                        function ($p_str)
                        {
                            return !preg_match('!!u', $p_str);
                        }
                    )
                );
            }
            else if ($p_mode == self::CL__GET__HEAD_LENGTH || $p_mode == self::CL__GET__CONTENT__HEADSIZE)
            {
                $l_row = fgetcsv($l_file_res, 0, $p_delimiter);

                fclose($l_file_res);

                return count($l_row);
            }
            else if ($p_mode == self::CL__GET__CONTENT__FIRST_LINE)
            {
                fgetcsv($l_file_res, 0, $p_delimiter);

                $l_content = fgetcsv($l_file_res, 0, $p_delimiter);
                $l_is_utf8 = !count(
                    array_filter(
                        $l_content,
                        function ($p_str)
                        {
                            return !preg_match('!!u', $p_str);
                        }
                    )
                );
            }
            else if ($p_mode == self::CL__GET__CONTENT)
            {
                while ($l_csv_line = fgetcsv($l_file_res, 0, $p_delimiter))
                {
                    if (count(
                        array_filter(
                            $l_csv_line,
                            function ($p_str)
                            {
                                return !preg_match('!!u', $p_str);
                            }
                        )
                    ))
                    {
                        $l_csv_line = array_map('utf8_encode', $l_csv_line);
                    } // if

                    $l_content[] = $l_csv_line;
                } // while

                fclose($l_file_res);

                if (count($l_content))
                {
                    // Get the csv content without the header.
                    if (self::$m_header)
                    {
                        unset($l_content[0]);
                    } // if

                    return array_values($l_content);
                }
                else
                {
                    throw new Exception("CSV-File empty");
                } // if
            } // if

            if (!$l_is_utf8)
            {
                $l_content = array_map('utf8_encode', $l_content);
            } // if

            if (is_resource($l_file_res))
            {
                fclose($l_file_res);
            } // if

            return $l_content;
        } // if

        return false;
    } // function

    /**
     * Returns all importable categories for smarty template (KEY=>CATGID and VALUE=>TITLE).
     *
     * @param   string $p_multivalue_type See constants
     * @param   mixed  $p_object_type
     *
     * @return  string
     */
    public static function get_importable_categories($p_multivalue_type, $p_object_type = false)
    {
        $l_return = [
            _L('LC__UNIVERSAL__EXTRAS')                 => [
                '-' => '-'
            ],
            _L('LC__UNIVERSAL__OBJECT_SPECIFIC_FIELDS') => [
                'object_title'        => _L('LC__UNIVERSAL__OBJECT_TITLE'),
                'object_type_dynamic' => _L('LC__UNIVERSAL__OBJECT_TYPE'),
                'object_sysid'        => _L('LC__UNIVERSAL__OBJECT_SYSID'),
                'object_cmdbstatus'   => _L('LC__UNIVERSAL__CMDB_STATUS'),
                'object_purpose'      => _L('LC__CMDB__CATG__PURPOSE'),
                'object_category'     => _L('LC__CMDB__CATG__CATEGORY'),
                'object_tags'         => _L('LC__CMDB__CATG__GLOBAL_TAG') . ' (' . _L('LC__UNIVERSAL__CSV_MULTISELECT_VIA_SEMICOLON') . ')',
                'object_description'  => _L('LC__UNIVERSAL__DESCRIPTION')
            ]
        ];

        $l_catg         = $l_cats = $l_catc = [];
        $l_category_map = self::get_category_map();

        if (!is_object(self::$m_s_dao))
        {
            self::$m_s_dao = isys_cmdb_dao::instance(isys_application::instance()->database);
        } // if

        if ($p_object_type !== false)
        {
            $l_catg     = self::$m_s_dao->gui_get_catg_with_subcats_by_objtype_id($p_object_type);
            $l_cats     = self::$m_s_dao->gui_get_cats_with_subcats_by_objtype_id($p_object_type);
            $l_catc_res = self::$m_s_dao->gui_get_catg_custom_by_objtype_id($p_object_type);

            if (count($l_catc_res))
            {
                while ($l_catc_row = $l_catc_res->get_row())
                {
                    $l_catc[$l_catc_row['isysgui_catg_custom__id']] = true;
                } // while
            } // if
        } // if

        foreach ($l_category_map as $l_cat_const => $l_cat_data)
        {
            foreach ($l_cat_data[self::CL__CAT__PROPERTIES] as $l_prop_key => $l_prop_data)
            {
                if (!$l_prop_data[self::CL__CAT__PROPERTY__VISIBLE])
                {
                    continue;
                } // if

                $l_objtypes      = [];
                $l_parent_prefix = '';

                // Check if we need to skip any categories.
                if ($p_object_type !== false)
                {
                    if ($l_cat_data[self::CL__CAT__CAT_TYPE] == C__CMDB__CATEGORY__TYPE_GLOBAL && !isset($l_catg[$l_cat_data[self::CL__CAT__ID]]))
                    {
                        continue;
                    }
                    else if ($l_cat_data[self::CL__CAT__CAT_TYPE] == C__CMDB__CATEGORY__TYPE_SPECIFIC && !isset($l_cats[$l_cat_data[self::CL__CAT__ID]]))
                    {
                        continue;
                    }
                    else if ($l_cat_data[self::CL__CAT__CAT_TYPE] == C__CMDB__CATEGORY__TYPE_CUSTOM && !isset($l_catc[$l_cat_data[self::CL__CAT__ID]]))
                    {
                        continue;
                    } // if
                } // if

                if (is_array($l_cat_data[self::CL__CAT__PARENT]))
                {
                    if (!empty($l_cat_data[self::CL__CAT__PARENT]['isysgui_catg__title']))
                    {
                        $l_parent_prefix = _L($l_cat_data[self::CL__CAT__PARENT]['isysgui_catg__title']) . ': ';
                    }
                    else if (!empty($l_cat_data[self::CL__CAT__PARENT]['isysgui_cats__title']))
                    {
                        $l_parent_prefix = _L($l_cat_data[self::CL__CAT__PARENT]['isysgui_cats__title']) . ': ';
                    } // if
                } // if

                switch ($l_cat_data[self::CL__CAT__CAT_TYPE])
                {
                    default:
                    case C__CMDB__CATEGORY__TYPE_GLOBAL:
                        $l_cat_type = _L('LC__CMDB__GLOBAL_CATEGORIES');
                        break;
                    case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                        $l_cat_type = _L('LC__CMDB__SPECIFIC_CATEGORIES');

                        if ($p_object_type === false)
                        {
                            $l_objtype_res = self::$m_s_dao->get_objtype_by_cats_id($l_cat_data[self::CL__CAT__ID]);

                            while ($l_objtype_row = $l_objtype_res->get_row())
                            {
                                $l_objtypes[] = _L($l_objtype_row['isys_obj_type__title']);
                            } // while
                        } // if
                        break;
                    case C__CMDB__CATEGORY__TYPE_CUSTOM:
                        $l_cat_type = _L('LC__CMDB__CUSTOM_CATEGORIES');
                        break;
                } // switch

                $l_return[$l_cat_type][$l_cat_const . '::' . $l_prop_key] = $l_parent_prefix . $l_cat_data[self::CL__CAT__TITLE] . ' > ' . $l_prop_data[self::CL__CAT__PROPERTY__TITLE] . (!empty($l_prop_data[self::CL__CAT__PROPERTY__TYPE]) ? ' (' . $l_prop_data[self::CL__CAT__PROPERTY__TYPE] . ')' : '') . (count(
                        $l_objtypes
                    ) > 1 ? ' (' . implode(', ', $l_objtypes) . ')' : '');
            } // foreach
        } // foreach

        foreach ($l_return as &$l_data)
        {
            if (is_array($l_data))
            {
                asort($l_data);
            } // if
        } // foreach

        // Add Separator Entry to the extras optgroup if multivalue mode is equal LINE.
        if ('multivalue_' . $p_multivalue_type == self::CL__MULTIVALUE_TYPE__COLUMN)
        {
            $l_return[_L('LC__UNIVERSAL__EXTRAS')]['separator'] = 'Separator';
        } // if

        return $l_return;
    } // function

    /**
     * Returns objects specific categories as array
     *
     * @param int $p_object_type
     *
     * @return array
     */
    public static function get_specific_categories_of_object_type($p_object_type)
    {
        $l_specific_categories = [];

        // Global categories
        /** @var isys_cmdb_dao $l_dao */
        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);
        $l_res = $l_dao->get_all_catg_by_obj_type_id($p_object_type);

        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                $l_specific_categories[] = $l_row['isysgui_catg__const'];
            } // while
        } // if

        // Custom categories
        $l_res = $l_dao->get_catg_custom_by_obj_type($p_object_type);

        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                $l_specific_categories[] = $l_row['isysgui_catg_custom__const'];
            } // while
        } // if

        // Specific categories
        $l_objtypes_specific_categories = $l_dao->gui_get_cats_with_subcats_by_objtype_id($p_object_type);
        if (count($l_objtypes_specific_categories) > 0)
        {
            $l_specific_categories = array_merge(
                $l_specific_categories,
                array_map(
                    function ($l_arr)
                    {
                        return $l_arr['isysgui_cats__const'];
                    },
                    $l_objtypes_specific_categories
                )
            );
        } // if

        return $l_specific_categories;
    } // function

    /**
     * Get all objecttypes and their groups.
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public static function get_objecttypes()
    {
        /** @var isys_cmdb_dao $l_dao */
        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

        $l_objgroups    = [];
        $l_objtypes     = [];
        $l_res_objgroup = $l_dao->retrieve("SELECT * FROM isys_obj_type_group WHERE TRUE;");

        if ($l_res_objgroup->num_rows())
        {
            while ($l_row = $l_res_objgroup->get_row())
            {
                $l_objgroups[$l_row['isys_obj_type_group__id']] = _L($l_row['isys_obj_type_group__title']);
            } // while

            if (count($l_objgroups))
            {
                $l_sql_objtype = 'SELECT isys_obj_type__const, isys_obj_type__title FROM isys_obj_type WHERE isys_obj_type__isys_obj_type_group__id = ';
                foreach ($l_objgroups AS $l_obj_group_id => $l_obj_group_title)
                {
                    $l_res_objtype = $l_dao->retrieve($l_sql_objtype . $l_dao->convert_sql_id($l_obj_group_id));

                    if (count($l_res_objtype))
                    {
                        while ($l_row = $l_res_objtype->get_row())
                        {
                            if (!in_array($l_row['isys_obj_type__const'], self::$m_object_type_skip))
                            {
                                $l_objtypes[$l_obj_group_title][$l_row['isys_obj_type__const']] = _L($l_row['isys_obj_type__title']);
                            } // if
                        } // while
                    } // if

                    if (is_array($l_objtypes[$l_obj_group_title]))
                    {
                        asort($l_objtypes[$l_obj_group_title]);
                    } // if
                } // foreach
            } // if
        } // if

        return $l_objtypes;
    } // function

    /**
     * Ajax-request handler
     *
     * @param string $p_action
     */
    public static function handle_ajax_request($p_action)
    {
        if (!empty($p_action))
        {
            switch ($p_action)
            {
                case 'delete_import':
                    echo json_encode(!!unlink(C__IMPORT__CSV_DIRECTORY . $_POST['filename']));
                    break;

                case 'load_profiles':
                    header('Content-Type: application/json');
                    echo isys_format_json::encode(self::get_profiles());
                    break;
                case 'save_profile':
                    $l_data = json_decode($_POST['profileData'], true);

                    if (self::save_profile($l_data['title'], $l_data['data'], $_POST['profileID']))
                    {
                        isys_notify::success(_L(($_POST['profileID']) ? 'LC__MODULE__IMPORT__CSV__MSG__OVERWRITE' : 'LC__MODULE__IMPORT__CSV__MSG__SAVED'));
                    }
                    else
                    {
                        isys_notify::error(_L('LC__MODULE__IMPORT__CSV__MSG__SAVE_FAIL'));
                    } // if

                    break;

                case 'file_exist':
                    echo json_encode(!!file_exists(C__IMPORT__CSV_DIRECTORY . $_POST['filename']));
                    break;

                case 'import':
                    header('Content-Type: application/json; charset=utf-8');

                    echo isys_format_json::encode(self::process_import());
                    die;
                    break;
            } // switch
        }
        else
        {
            /**
             * @todo
             * If $p_action is empty we have to throw an exception
             */
        } // if
    } // function

    /**
     *
     * @return  array
     */
    public static function process_import()
    {
        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try
        {
            switch ($_POST['csv-log-detail'])
            {
                case 'simple':
                    $l_log_level = Logger::ERROR;
                    break;
                default:
                case 'normal':
                    $l_log_level = Logger::INFO;
                    break;
                case 'all':
                    $l_log_level = Logger::DEBUG;
                    break;
            } // switch

            if (defined($_POST['object_type']))
            {
                $_POST['object_type'] = constant($_POST['object_type']);
            } // if

            $l_importer = new isys_module_import_csv(
                C__IMPORT__CSV_DIRECTORY . $_POST['csv_filename'],
                $_POST['csv_separator'],
                $_POST['multivalue'],
                // 'row', 'column', 'comma'
                $_POST['object_title'],
                $_POST['object_type'],
                $_POST['object_type_dynamic'],
                $_POST['object_purpose'],
                $_POST['object_category'],
                $_POST['object_sysid'],
                $_POST['object_cmdbstatus'],
                $_POST['object_description'],
                !!$_POST['csv_header'],
                $_POST['prop_search'],
                $_POST['multivalue_mode'],
                $l_log_level,
                $_POST['singlevalue_overwrite_empty_values'],
                $_POST['object_tags']
            );

            if (isset($_POST['special-title']))
            {
                $l_importer->set_special_title_index(explode(',', $_POST['special-title']));
            } // if

            $l_importer->initialize($_POST['assignment'], $_POST['obj_type_assignment'] ?: []);
            $l_importer->import();

            $l_import_dao   = new isys_module_dao_import_log(isys_application::instance()->database);
            $l_import_entry = $l_import_dao->add_import_entry(
                $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__CSV'),
                $_POST['csv_filename'],
                null // (((bool) $_POST['profile_loaded']) ? $_POST['profile_sbox'] : null) // What is this field for?
            );

            $l_importer->save_log($l_import_entry);

            $l_return['data'] = [
                'csv_objects'  => $l_importer->get_created_objects(),
                'csv_status'   => $l_importer->get_import_status(),
                'csv_log_path' => $l_importer->get_log_path(),
                'csv_log'      => $l_importer->get_log_records($l_log_level)
            ];
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        return $l_return;
    } // function

    /**
     * Builds the SQL Join string for the query to identify existing objects
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private static function build_joins()
    {
        $l_already_joined = ['isys_obj'];
        $l_joins          = '';

        if (is_array(self::$m_update_identifiers))
        {
            foreach (self::$m_update_identifiers AS $l_ident)
            {
                if ($l_ident == '-1')
                {
                    continue;
                } // if

                $l_table_join = self::$m_update_identifiers_map[$l_ident]['table'];

                if (is_array($l_table_join) && count($l_table_join))
                {
                    foreach ($l_table_join AS $l_table => $l_field)
                    {
                        if (!in_array($l_table_join, $l_already_joined))
                        {
                            $l_joins .= ' LEFT JOIN ' . $l_table . ' ON ' . $l_field[0] . ' = ' . $l_field[1];
                            $l_already_joined[] = $l_table;
                        } // if
                    } // foreach
                }
                else if (!empty($l_table_join))
                {
                    if (!in_array($l_table_join, $l_already_joined))
                    {
                        $l_joins .= ' LEFT JOIN ' . $l_table_join . ' ON ' . $l_table_join . '__isys_obj__id = isys_obj__id ';
                        $l_already_joined[] = $l_table_join;
                    } // if
                } // if
            } // foreach
        } // if

        self::$m_identification_joins = $l_joins;
    } // function

    /**
     * Checks whether a rule of $m_property_rules get matched in the row string.
     *
     * @param   string $p_row
     *
     * @return  boolean
     */
    private static function check_property_rules($p_row)
    {
        if (is_array(self::$m_property_rules))
        {
            foreach (self::$m_property_rules AS $l_substring)
            {
                if (stristr($p_row, $l_substring))
                {
                    return true;
                } // if
            } // foreach
        } // if

        return false;
    } // function

    /**
     * Checks whether a rowstring includes any unit tables. It is used to set the visibility of properties.
     *
     * @param   string $p_row
     *
     * @return  boolean
     */
    private static function check_property_unit($p_row)
    {
        if (is_array(self::$m_valid_unit_tables))
        {
            foreach (self::$m_valid_unit_tables AS $l_substring)
            {
                if (stristr($p_row, $l_substring))
                {
                    return true;
                } // if
            } // foreach
        } // if

        return false;
    } // function

    /**
     * Gets the parent of the given category.
     *
     * @param   integer       $p_category_id
     * @param   string        $p_category_abbr
     * @param   isys_cmdb_dao $p_category_dao
     *
     * @return  mixed
     */
    private static function get_parent($p_category_id, $p_category_abbr, $p_category_dao)
    {
        $l_sql = 'SELECT parent.* FROM isysgui_cat' . $p_category_abbr . ' AS child
			INNER JOIN isysgui_cat' . $p_category_abbr . ' AS parent ON parent.isysgui_cat' . $p_category_abbr . '__id = child.isysgui_cat' . $p_category_abbr . '__parent
			WHERE child.isysgui_cat' . $p_category_abbr . '__id = ' . $p_category_dao->convert_sql_id($p_category_id) . ' LIMIT 1;';

        return $p_category_dao->retrieve($l_sql)
            ->get_row();
    } // function

    /**
     * Handle category blacklist.
     *
     * @param  array $p_category_map
     */
    private static function clean_category_map(&$p_category_map)
    {
        // Walk through $m_category_skip.
        foreach (self::$m_category_skip AS $l_category_const)
        {
            if (defined($l_category_const))
            {
                $l_category_const = constant($l_category_const);

                if (isset($p_category_map[$l_category_const]))
                {
                    unset($p_category_map[$l_category_const]);
                } // if
            } // if
        } // foreach

        // Unset all categories without any properties.
        foreach ($p_category_map AS $l_category_id => $l_category_data)
        {
            if (!count($l_category_data[self::CL__CAT__PROPERTIES]))
            {
                unset($p_category_map[$l_category_id]);
            } // if
        } // foreach
    } // function

    /**
     * Virtual properties.
     *
     * @return  array
     */
    private static function virtual_properties()
    {
        return [
            'C__CATG__IP'                   => [
                'virtual_ip'              => [
                    self::CL__CAT__PROPERTY__TITLE   => _L('LC__CMDB__CATG__NETWORK__PRIM_IP'),
                    self::CL__CAT__PROPERTY__ROW     => 'virtual',
                    self::CL__CAT__PROPERTY__TAG     => 'virtual_ip',
                    self::CL__CAT__PROPERTY__VISIBLE => true,
                    self::CL__CAT__PROPERTY__MODE    => 'virtual'
                ],
                'virtual_ip_assignment'   => [
                    self::CL__CAT__PROPERTY__TITLE   => _L('LC__CATP__IP__ASSIGN'),
                    self::CL__CAT__PROPERTY__ROW     => 'virtual',
                    self::CL__CAT__PROPERTY__TAG     => 'virtual_ip_assignment',
                    self::CL__CAT__PROPERTY__VISIBLE => true,
                    self::CL__CAT__PROPERTY__MODE    => 'virtual',
                    self::CL__CAT__PROPERTY__TYPE    => _L('LC__UNIVERSAL__DIALOG')
                ],
                'virtual_port_assignment' => [
                    self::CL__CAT__PROPERTY__TITLE   => _L('LC__CATG__IP__ASSIGNED_PORT'),
                    self::CL__CAT__PROPERTY__ROW     => 'virtual',
                    self::CL__CAT__PROPERTY__TAG     => 'virtual_port_assignment',
                    self::CL__CAT__PROPERTY__VISIBLE => true,
                    self::CL__CAT__PROPERTY__MODE    => 'virtual',
                    self::CL__CAT__PROPERTY__TYPE    => _L('LC__UNIVERSAL__DIALOG')
                ]
            ],
            'C__CMDB__SUBCAT__NETWORK_PORT' => [
                'virtual_interface' => [
                    self::CL__CAT__PROPERTY__TITLE   => _L('LC__CMDB__CATG__PORT__CON_INTERFACE'),
                    self::CL__CAT__PROPERTY__ROW     => 'virtual',
                    self::CL__CAT__PROPERTY__TAG     => 'virtual_interface',
                    self::CL__CAT__PROPERTY__VISIBLE => true,
                    self::CL__CAT__PROPERTY__MODE    => 'virtual',
                    self::CL__CAT__PROPERTY__TYPE    => _L('LC__CMDB__CATG__REFERENCED_VALUE')
                ]
            ]
        ];
    } // function

    /**
     * Default categorymap manipulating method. Currently adds virtual properties.
     *
     * @param  array &$p_category_map
     */
    private static function modify_category_map(&$p_category_map)
    {
        $l_virtual_properties = self::virtual_properties();

        if (count($l_virtual_properties))
        {
            foreach ($l_virtual_properties as $l_catg_id => $l_vproperties)
            {
                foreach ($l_vproperties as $l_vkey => $l_vprop)
                {
                    $p_category_map[$l_catg_id][self::CL__CAT__PROPERTIES][$l_vkey] = $l_vprop;
                } // foreach
            } // foreach
        } // if
    } // function

    /**
     * Adds an category to $m_category_skip.
     * $p_const have to be the category constant as string
     *
     * @param  string $p_const
     */
    private static function add_to_category_skip($p_const)
    {
        self::$m_category_skip[] = $p_const;
    } // function

    /**
     * Check whether the row is nullable in the mysql table to get its essentiality. Only rows of %int% are important.
     *
     * @param   string        $p_table
     * @param   array         $p_column
     * @param   isys_cmdb_dao $p_dao
     *
     * @return  mixed
     */
    private static function get_essentiality($p_table, $p_column, $p_dao)
    {
        if ($p_table)
        {
            // This will happen very often occur when handling object browser fields.
            if ($p_column === $p_table . '__id')
            {
                return false;
            } // if

            try
            {
                $l_res = $p_dao->retrieve("SHOW COLUMNS FROM " . $p_table . " WHERE Field LIKE " . $p_dao->convert_sql_text($p_column) . " AND Type LIKE '%int%';");

                if (count($l_res))
                {
                    return $l_res->get_row_value('Null') == 'NO';
                } // if
            }
            catch (Exception $e)
            {
                return false;
            } // try
        } // if

        return false;
    } // function

    /**
     * Prepares the given value for the unit handler.
     *
     * @param   string $p_value
     *
     * @return  string
     */
    private static function prepare_value($p_value)
    {
        return str_replace('_', '', preg_replace('/[\W\d]/', '', strtolower(trim($p_value))));
    } // function

    /**
     * Set step
     *
     * @param int $p_step
     */
    private static function set_step($p_step)
    {
        self::$m_global_step = $p_step;
    } // function

    /**
     * Get current step.
     *
     * @return  integer
     */
    private static function get_step()
    {
        return self::$m_global_step;
    } // function

    /**
     * Live category skipper.
     */
    private static function skip_category()
    {
        self::$m_live_category_skip = true;
    } // function

    /**
     * Reset live category skipper
     */
    private static function reset_category_skip()
    {
        self::$m_live_category_skip = false;
    } // function

    /**
     * Get Status of the live category skipper
     *
     * @return mixed
     */
    private static function get_category_skip()
    {
        return self::$m_live_category_skip;
    } // function

    /**
     * Returns the Object mode
     *
     * @return mixed
     */
    private static function get_object_mode()
    {
        return self::$m_object_mode;
    } // function

    /**
     * Returns Index of Hostname Row.
     *
     * @param   array $p_assignment_map
     *
     * @return  integer
     */
    private static function get_hostname_index($p_assignment_map)
    {
        $l_category_map             = self::get_category_map();
        $l_hostname_property_exists = false;
        $l_hostname_csv_index       = null;

        // @todo  Is it ever possible, that the "hostname" property is missing here?
        foreach ($l_category_map['C__CATG__IP'][self::CL__CAT__PROPERTIES] AS $l_index => $l_property)
        {
            if ($l_property[self::CL__CAT__PROPERTY__ROW] == 'isys_catg_ip_list__hostname')
            {
                $l_hostname_property_exists = true;
                continue;
            } // if
        } // foreach

        if ($l_hostname_property_exists)
        {
            if (self::$m_multivalue_mode == self::CL__MULTIVALUE_TYPE__COLUMN && count($p_assignment_map))
            {
                foreach ($p_assignment_map AS $l_index => $l_catg)
                {
                    if ($l_catg['catg'] == 'C__CATG__IP' && $l_catg['property'] == 'hostname')
                    {
                        $l_hostname_csv_index = $l_index;
                        continue;
                    } // if
                } // foreach
            }
            else
            {
                if (isset($p_assignment_map['C__CATG__IP']['hostname']))
                {
                    $l_hostname_csv_index = $p_assignment_map['C__CATG__IP']['hostname'];
                } // if
            } // if
        } // if

        return $l_hostname_csv_index;
    } // function

    /**
     * Checks
     *
     * @param  int $p_obj_type_id
     *
     * @return mixed
     */
    private static function is_container($p_obj_type_id)
    {
        /** @var isys_cmdb_dao $l_dao */
        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);
        $l_sql = "SELECT isys_obj_type__container
            FROM isys_obj_type
            WHERE isys_obj_type__id = " . $l_dao->convert_sql_id($p_obj_type_id) . "
            AND isys_obj_type__container = 1;";

        $l_res = $l_dao->retrieve($l_sql);

        return (bool) $l_res->num_rows();
    } // function

    /**
     * Count non empty arguments
     *
     * @return int
     */
    private static function counter()
    {
        $l_vals    = func_get_args();
        $l_counter = 0;

        foreach ($l_vals AS $l_value)
        {
            if (!empty($l_value))
            {
                $l_counter++;
            } // if
        } // foreach

        return $l_counter;
    } // function

    /**
     * Set live object mode
     */
    private static function live_object_update()
    {
        self::$m_object_live_mode = isys_import_handler_cmdb::C__UPDATE;
    } // function

    /**
     * Set live object mode
     */
    private static function live_object_create()
    {
        self::$m_object_live_mode = isys_import_handler_cmdb::C__CREATE;
    } // function

    /**
     * Checks live object mode is equal UPDATE
     *
     * @return bool
     */
    private static function is_update()
    {
        return (self::$m_object_live_mode == isys_import_handler_cmdb::C__UPDATE);
    } // function

    /**
     * Object live mode GETTER
     *
     * @return mixed object live mode
     */
    private static function get_live()
    {
        return self::$m_object_live_mode;
    } // function

    /**
     * Save the current object id
     *
     * @param int $p_object_id
     */
    private static function set_current_object($p_object_id)
    {
        if (!empty($p_object_id))
        {
            self::$m_current_object_id = $p_object_id;
        } // if
    } // function

    /**
     * Get the current object id
     *
     * @return mixed
     */
    private static function get_current_object()
    {
        return self::$m_current_object_id;
    } // function

    /**
     * Save the current object id
     *
     * @param int $p_object_id
     */
    private static function set_current_object_type($p_obj_type_id = null)
    {
        self::$m_current_object_type = (($p_obj_type_id !== null) ? $p_obj_type_id : (self::$m_global_object_type > 0 ? self::$m_global_object_type : null));
    } // function

    /**
     * Get the current object id
     *
     * @return mixed
     */
    private static function get_current_object_type()
    {
        return self::$m_current_object_type;
    } // function

    /**
     * @param int $p_objecttype_id
     * @param int $p_category_id
     * @param int $p_category_type
     *
     * @return bool
     * @throws \Exception
     * @throws \isys_exception_database
     */
    private static function includes_category($p_objecttype_id = null, $p_category_id = null, $p_category_type)
    {
        $l_suffix = ($p_category_type == C__CMDB__CATEGORY__TYPE_GLOBAL ? 'g' : ($p_category_type == C__CMDB__CATEGORY__TYPE_SPECIFIC ? 's' : 'g_custom'));
        if (!empty($p_objecttype_id) && !empty($p_category_id))
        {
            if ($p_category_type == C__CMDB__CATEGORY__TYPE_SPECIFIC)
            {
                $l_res = self::$m_s_dao->get_objtype_by_cats_id($p_category_id);

                if ($l_res->num_rows() > 0)
                {
                    return true;
                }
                else
                {
                    // child category
                    $l_parent_category_id = self::$m_s_dao->cats_get_parent_cats($p_category_id)
                        ->get_row_value('isysgui_cats_2_subcategory__isysgui_cats__id__parent');
                    $l_res                = self::$m_s_dao->get_objtype_by_cats_id($l_parent_category_id);

                    if ($l_res->num_rows() > 0)
                    {
                        return true;
                    } // if
                } // if
            }
            else
            {
                /* is category directly assigned to the objecttype ?? */
                $l_sql = "SELECT * FROM isys_obj_type_2_isysgui_cat{$l_suffix} " . "WHERE isys_obj_type_2_isysgui_cat{$l_suffix}__isys_obj_type__id = " . $p_objecttype_id . " AND " . "isys_obj_type_2_isysgui_cat{$l_suffix}__isysgui_cat{$l_suffix}__id = " . $p_category_id . ";";
                $l_res = self::$m_s_dao->retrieve($l_sql);
                if ($l_res->num_rows())
                {
                    return (bool) $l_res->num_rows();
                }
                else
                {
                    if ($p_category_type == C__CMDB__CATEGORY__TYPE_GLOBAL)
                    {
                        /* is category a child */
                        $l_sql  = "SELECT isysgui_cat{$l_suffix}__parent FROM isysgui_cat{$l_suffix} WHERE isysgui_cat{$l_suffix}__id = " . self::$m_s_dao->convert_sql_id(
                                $p_category_id
                            ) . ";";
                        $l_res2 = self::$m_s_dao->retrieve($l_sql);
                        $l_row2 = $l_res2->get_row();
                        if ($l_res2->num_rows() && !empty($l_row2["isysgui_cat{$l_suffix}__parent"]))
                        {
                            $l_sql  = "SELECT * FROM isys_obj_type_2_isysgui_cat{$l_suffix} " . "WHERE isys_obj_type_2_isysgui_cat{$l_suffix}__isys_obj_type__id = " . $p_objecttype_id . " AND " . "isys_obj_type_2_isysgui_cat{$l_suffix}__isysgui_cat{$l_suffix}__id = " . $l_row2["isysgui_cat{$l_suffix}__parent"] . ";";
                            $l_res3 = self::$m_s_dao->retrieve($l_sql);
                            if ($l_res3->num_rows())
                            {
                                return (bool) $l_res3->num_rows();
                            } // if
                        } // if
                    } // if
                } // if
            } // if
        } // if

        return false;
    } // function

    /**
     * Method for initializing the module.
     *
     * @param   isys_module_request $p_req
     *
     * @return  void
     */
    public function init(isys_module_request $p_req)
    {
        ;
    } // function

    /**
     * Signal Slot initialization.
     *
     * @return  void
     */
    public function initslots()
    {
        ;
    } // function

    /**
     * Method for starting the process of a module.
     *
     * @return  void
     */
    public function start()
    {
        ;
    } // function

    /**
     * Gets the global import status
     *
     * @return mixed
     */
    public function get_import_status()
    {
        return $this->m_import_status;
    } // function

    /**
     * Returns the global object or the line object
     *
     * @param array $p_csv_line
     *
     * @return int|bool
     */
    public function get_object_type($p_csv_line)
    {
        if (self::$m_global_object_type)
        {
            return self::$m_global_object_type;
        }
        else
        {
            $l_object_type          = trim($p_csv_line[self::$m_object_type]);
            self::$m_log_objecttype = $l_object_type;
            if (!empty($l_object_type))
            {
                return $this->m_dao_dialog->get_objtype_id_by_const_string($l_object_type);
            }

            return false;
        } // if
    } // function

    /**
     * Initialize the Import
     *
     * @param   array $p_assignment_map
     * @param   array $p_obj_type_assignment
     */
    public function initialize($p_assignment_map, array $p_obj_type_assignment = [])
    {
        $logger = $this->m_log;

        // Start location fix after every csv import:
        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.import_csv.afterImport',
                function ($p_module_import_csv, $p_transformed_data, $p_created_objects, $p_category_map) use ($logger)
                {
                    if (is_array($p_transformed_data) && count($p_transformed_data) > 0)
                    {
                        $firstItem = min($p_transformed_data);
                        if (isset($firstItem['category_data']))
                        {
                            if ($firstItem['category_data']['C__CATG__LOCATION'])
                            {
                                $l_dao = new isys_cmdb_dao_location(isys_application::instance()->database);
                                $l_dao->_location_fix();
                                $l_dao->regenerate_missing_relation_objects();

                                /**
                                 * Clear cache as well
                                 */
                                isys_cache::keyvalue()
                                    ->flush();

                                if ($logger)
                                {
                                    $logger->notice('Location fix executed.');
                                }
                            } // if
                        } // if
                    } // if
                }
            );

        $this->m_object_type_assignment = $p_obj_type_assignment;

        if ($this->get_import_status())
        {
            self::set_step(self::CL__IMPORT_STEP__INITIALIZE);
            $this->m_log->info('Initialize ...');
            if (!is_array($p_assignment_map) || count($p_assignment_map) === 0)
            {
                $this->m_log->notice('No assignments has been made.');
            } // if

            $this->m_assignment_map  = $p_assignment_map;
            self::$m_object_hostname = self::get_hostname_index($p_assignment_map);

            // Getting category map, raw data, prepare raw data and create data structure.
            $this->m_category_map = self::get_category_map(true);

            $this->m_raw_data = self::get_csv(
                self::$m_file,
                self::$m_delimiter,
                self::CL__GET__CONTENT
            );

            if (is_array($this->m_raw_data) && count($this->m_raw_data))
            {
                if (empty(self::$m_update_identifiers))
                {
                    self::$m_update_identifiers = $_POST['identificator'];
                } // if
                if (empty(self::$m_update_csv_idents))
                {
                    self::$m_update_csv_idents = $_POST['csv_ident'];
                } // if

                // BUILD JOINS FOR IDENTIFICATION.
                self::build_joins();

                $this->arrange_raw_data();
                $this->create_data_structure();

                if (!is_array($this->m_transformed_data) && !count($this->m_transformed_data))
                {
                    $this->set_import_status(false);
                    $this->m_log->error("Unable to create data structure.");
                    $this->m_log->info('Aborting import !');
                } // if
            }
            else
            {
                $this->set_import_status(false);
                $this->m_log->error('CSV-Content empty.');
                $this->m_log->info('Aborting import !');
            } // if
        } // if
    } // function

    /**
     * Sorting the raw data in consideration of our multivalue mode.
     */
    public function arrange_raw_data()
    {
        self::set_step(self::CL__IMPORT_STEP__ARRANGE);
        $this->m_log->info('Arranging raw data');

        if (self::$m_multivalue_mode != self::CL__MULTIVALUE_TYPE__COLUMN)
        {
            // Use the row method for "row" and "comma" mode.
            $this->arrange_raw_data_for_row();
        }
        else
        {
            $this->arrange_raw_data_for_line();
        } // if
    } // function

    /**
     * Sorting the raw data for Multivalue: ROW
     */
    public function arrange_raw_data_for_row()
    {
        $l_arranged_array = [];

        foreach ($this->m_raw_data as $l_line)
        {
            if (empty($l_line[self::$m_object_title]) && $this->m_special_title === false)
            {
                continue;
            } // if

            // It can be possible that objects with the same title exist but in different object types.
            if (self::$m_object_sysid !== null)
            {
                $l_identifier = $l_line[self::$m_object_sysid];
            }
            elseif (self::$m_object_type)
            {
                $l_identifier = $l_line[self::$m_object_title] . '|&|' . $l_line[self::$m_object_type];
            }
            else
            {
                $l_identifier = $l_line[self::$m_object_title];
            } // if

            if ($this->m_special_title !== false)
            {
                $l_identifier = [];

                foreach ($this->m_special_title as $l_index)
                {
                    $l_identifier[] = $l_line[$l_index];
                } // foreach

                $l_identifier = implode(' ', $l_identifier);
            } // if

            $l_arranged_array[$l_identifier][] = $l_line;
        } // foreach

        $this->m_raw_data = $l_arranged_array;
    } // function

    /**
     * Sorting the raw data for Multivalue: Line
     * and creates the data structure
     */
    public function arrange_raw_data_for_line()
    {
        $l_transformed      = [];
        $l_multivalue_cache = [];
        self::set_step(self::CL__IMPORT_STEP__DATASTRUCTURE);

        if ($this->get_import_status())
        {
            $this->m_log->info('Creating data structure');
            if (is_array($this->m_raw_data))
            {
                foreach ($this->m_raw_data as $l_index_of_line => $l_line)
                {
                    $this->m_log->debug('Index of line: ' . $l_index_of_line);
                    if (($l_object_type = $this->get_object_type($l_line)))
                    {
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_TITLE]       = trim($l_line[self::$m_object_title]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_TYPE]        = trim($l_object_type);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_CATEGORY]    = trim($l_line[self::$m_object_category]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_TAGS]        = trim($l_line[self::$m_object_tags]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_PURPOSE]     = trim($l_line[self::$m_object_purpose]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_SYSID]       = trim($l_line[self::$m_object_sysid]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_CMDBSTATUS]  = trim($l_line[self::$m_object_cmdbstatus]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_DESCRIPTION] = trim($l_line[self::$m_object_description]);
                        $l_transformed[$l_index_of_line][self::CL__OBJECT_HOSTNAME]    = trim($l_line[self::$m_object_hostname]);
                        $l_transformed[$l_index_of_line]['identification_condition']   = $this->build_conditions($l_line);

                        if (is_array($this->m_assignment_map))
                        {
                            foreach ($this->m_assignment_map as $l_header_index => $l_target)
                            {
                                if ($l_target['category'] == 'separator')
                                {
                                    $l_multivalue_cache = [];
                                }
                                else
                                {
                                    if (empty($l_line[$l_header_index]) && strlen($l_line[$l_header_index]) === 0)
                                    {
                                        $this->m_log->notice(
                                            'Empty value for ' . $this->m_category_map[$l_target['category']][self::CL__CAT__TITLE] . " [" . $this->m_category_map[$l_target['category']][self::CL__CAT__PROPERTIES][$l_target['property']][self::CL__CAT__PROPERTY__TITLE] . "]"
                                        );
                                        continue;
                                    } // if

                                    $this->m_log->debug(
                                        'Current source: ' . $this->m_category_map[$l_target['category']][self::CL__CAT__TITLE] . " [" . $this->m_category_map[$l_target['category']][self::CL__CAT__PROPERTIES][$l_target['property']][self::CL__CAT__PROPERTY__TITLE] . "]  " . "Current value: " . $l_line[$l_header_index]
                                    );

                                    $this->m_tmp_index       = $l_header_index;
                                    $l_line[$l_header_index] = $this->universal_helper($l_target['category'], $l_target['property'], $l_line[$l_header_index]);

                                    $this->m_tmp_index = null;

                                    if ($this->m_category_map[$l_target['category']][self::CL__CAT__MULTIVALUE])
                                    {
                                        // @see ID-2529
                                        if (is_string($l_line[$l_header_index]))
                                        {
                                            $l_line[$l_header_index] = trim($l_line[$l_header_index]);
                                        } // if

                                        if ($this->m_category_map[$l_target['category']][self::CL__CAT__TYPE] == isys_cmdb_dao_category::TYPE_ASSIGN && $l_target['category'] !== 'C__CATG__CONTACT')
                                        {
                                            if (isset($l_multivalue_cache[$l_target['category']]))
                                            {
                                                $l_multivalue_cache[$l_target['category']]++;
                                            }
                                            else
                                            {
                                                $l_multivalue_cache[$l_target['category']] = 0;
                                            } // if

                                            $l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']][$l_multivalue_cache[$l_target['category']]][$l_target['property']] = $l_line[$l_header_index];
                                        }
                                        else
                                        {
                                            if (isset($l_multivalue_cache[$l_target['category']]))
                                            {
                                                $l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']][$l_multivalue_cache[$l_target['category']]][$l_target['property']] = $l_line[$l_header_index];
                                            }
                                            else
                                            {
                                                $l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']][][$l_target['property']] = $l_line[$l_header_index];
                                                end($l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']]);
                                                $l_multivalue_cache[$l_target['category']] = key($l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']]);
                                            } // if
                                        } // if
                                    }
                                    else
                                    {
                                        if (is_string($l_line[$l_header_index]))
                                        {
                                            $l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']][$l_target['property']] = trim(
                                                $l_line[$l_header_index]
                                            );
                                        }
                                        else
                                        {
                                            $l_transformed[$l_index_of_line][self::CL__CAT__DATA][$l_target['category']][$l_target['property']] = $l_line[$l_header_index];
                                        } // if
                                    } // if

                                    unset($this->m_raw_data[$l_index_of_line][$l_header_index]);
                                } // if
                            } // foreach
                        } // if

                        $l_transformed[$l_index_of_line][self::CL__UNUSED__DATA] = $this->m_raw_data[$l_index_of_line];
                    }
                    else
                    {
                        $this->m_log->notice('Object is empty or invalid: ' . self::$m_log_objecttype);
                    } // if
                } // foreach
            } // if

            $this->m_transformed_data = $l_transformed;
        } // if
    } // function

    /**
     * Creates the specific array data structure for the Import and assigns it to $this->m_transformed_data.
     */
    public function create_data_structure()
    {
        if (self::$m_multivalue_mode == self::CL__MULTIVALUE_TYPE__COLUMN)
        {
            return;
        } // if

        self::set_step(self::CL__IMPORT_STEP__DATASTRUCTURE);
        $l_multivalue_cache = [];
        $l_data_structure   = [];

        if ($this->get_import_status())
        {
            $this->m_log->info('Creating data structure');

            if (is_array($this->m_raw_data) && count($this->m_raw_data))
            {
                foreach ($this->m_raw_data as $l_identifier => $l_collected_lines)
                {
                    foreach ($l_collected_lines as $l_line)
                    {
                        if (($l_object_type = $this->get_object_type($l_line)))
                        {
                            $l_title = $l_line[self::$m_object_title];

                            if ($this->m_special_title !== false)
                            {
                                $l_title = $l_identifier;
                            } // if

                            $l_data_structure[$l_identifier][self::CL__OBJECT_TITLE]       = $l_title;
                            $l_data_structure[$l_identifier][self::CL__OBJECT_TYPE]        = $l_object_type;
                            $l_data_structure[$l_identifier][self::CL__OBJECT_CATEGORY]    = (!empty($l_line[self::$m_object_category])) ? $l_line[self::$m_object_category] : $l_data_structure[$l_identifier][self::CL__OBJECT_CATEGORY];
                            $l_data_structure[$l_identifier][self::CL__OBJECT_TAGS]        = (!empty($l_line[self::$m_object_tags])) ? $l_line[self::$m_object_tags] : $l_data_structure[$l_identifier][self::CL__OBJECT_TAGS];
                            $l_data_structure[$l_identifier][self::CL__OBJECT_PURPOSE]     = (!empty($l_line[self::$m_object_purpose])) ? $l_line[self::$m_object_purpose] : $l_data_structure[$l_identifier][self::CL__OBJECT_PURPOSE];
                            $l_data_structure[$l_identifier][self::CL__OBJECT_SYSID]       = (!empty($l_line[self::$m_object_sysid])) ? $l_line[self::$m_object_sysid] : null;
                            $l_data_structure[$l_identifier][self::CL__OBJECT_CMDBSTATUS]  = (!empty($l_line[self::$m_object_cmdbstatus])) ? $l_line[self::$m_object_cmdbstatus] : $l_data_structure[$l_identifier][self::CL__OBJECT_CMDBSTATUS];
                            $l_data_structure[$l_identifier][self::CL__OBJECT_DESCRIPTION] = (!empty($l_line[self::$m_object_description])) ? $l_line[self::$m_object_description] : $l_data_structure[$l_identifier][self::CL__OBJECT_DESCRIPTION];
                            $l_data_structure[$l_identifier][self::CL__OBJECT_HOSTNAME]    = (!empty($l_line[self::$m_object_hostname])) ? $l_line[self::$m_object_hostname] : null;
                            $l_data_structure[$l_identifier]['identification_condition']   = $this->build_conditions($l_line);

                            if (is_array($this->m_assignment_map) && count($this->m_assignment_map) > 0)
                            {
                                foreach ($this->m_assignment_map as $l_cat_const => $l_properties)
                                {
                                    foreach ($l_properties as $l_prop_key => $l_index)
                                    {
                                        if (!empty($l_line[$l_index]))
                                        {
                                            if ($this->m_category_map[$l_cat_const][self::CL__CAT__MULTIVALUE] && self::$m_multivalue_mode == self::CL__MULTIVALUE_TYPE__COMMA)
                                            {
                                                $l_line[$l_index] = array_map('trim', explode(',', $l_line[$l_index]));
                                            }
                                            else
                                            {
                                                $l_line[$l_index] = [$l_line[$l_index]];
                                            } // if

                                            foreach ($l_line[$l_index] as $l_value)
                                            {
                                                $this->m_tmp_index = $l_index;
                                                $l_value           = $this->universal_helper($l_cat_const, $l_prop_key, $l_value);
                                                $this->m_tmp_index = null;

                                                if ($this->m_category_map[$l_cat_const][self::CL__CAT__MULTIVALUE])
                                                {
                                                    if (!isset($l_multivalue_cache[$l_cat_const]) || self::$m_multivalue_mode == self::CL__MULTIVALUE_TYPE__COMMA)
                                                    {
                                                        /*
                                                         * ID-3189 Instead of splitting each value into its own "category entry" we try to merge together as good as possible.
                                                         *
                                                         * Before:
                                                         * [
                                                         *   'C__CATG__CONTACT' => [
                                                         *     0 => [
                                                         *       'contact_object' => '12'
                                                         *     ],
                                                         *     1 => [
                                                         *       'contact_object' => '23'
                                                         *     ],
                                                         *     2 => [
                                                         *       'role' => 12
                                                         *     ],
                                                         *     3 => [
                                                         *       'role' => 23
                                                         *     ]
                                                         *   ]
                                                         * ]
                                                         *
                                                         * After:
                                                         * [
                                                         *   'C__CATG__CONTACT' => [
                                                         *     0 => [
                                                         *       'contact_object' => '12',
                                                         *       'role' => 12
                                                         *     ],
                                                         *     1 => [
                                                         *       'contact_object' => '23',
                                                         *       'role' => 23
                                                         *     ]
                                                         *   ]
                                                         * ]
                                                         */
                                                        $l_assigned = false;

                                                        if (! isset($l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const]))
                                                        {
                                                            $l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const] = [];
                                                        } // if

                                                        foreach ($l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const] as &$l_item)
                                                        {
                                                            if (isset($l_item[$l_prop_key]))
                                                            {
                                                                continue;
                                                            } // if

                                                            $l_item[$l_prop_key] = $l_value;
                                                            $l_assigned = true;
                                                            break;
                                                        } // foreach

                                                        if (!$l_assigned)
                                                        {
                                                            $l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const][] = [$l_prop_key => $l_value];
                                                        } // if

                                                        $l_multivalue_cache[$l_cat_const] = true;
                                                    }
                                                    else
                                                    {
                                                        // @todo  Check if there is a better way.
                                                        end($l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const]);
                                                        $l_last_index = key($l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const]);
                                                        $l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const][$l_last_index][$l_prop_key] = $l_value;
                                                    } // if
                                                }
                                                else
                                                {
                                                    // Only add value if not empty.
                                                    if (!empty($l_value))
                                                    {
                                                        $l_data_structure[$l_identifier][self::CL__CAT__DATA][$l_cat_const][$l_prop_key] = $l_value;
                                                    } // if
                                                } // if
                                            } // foreach
                                        } // if
                                    } // foreach
                                } // foreach
                            } // if

                            $l_multivalue_cache = [];
                        } // if
                    } // foreach
                } // foreach
            } // if

            $this->m_transformed_data = $l_data_structure;
        } // if
    } // function

    /**
     * Calls the helper specified in the category map.
     *
     * @param   string  $p_category_const
     * @param   integer $p_property_id
     * @param   string  $p_value
     *
     * @return  string
     */
    public function universal_helper($p_category_const, $p_property_id, $p_value)
    {
        if (isset($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$p_property_id][self::CL__CAT__PROPERTY__MODE]))
        {
            $l_helper_method = 'csv_helper__' . $this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$p_property_id][self::CL__CAT__PROPERTY__MODE];
            if (method_exists($this, $l_helper_method))
            {
                $this->m_log->debug('Calling Helper: ' . $l_helper_method);

                return $this->$l_helper_method($p_value, $this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$p_property_id]);
            } // if

            $this->m_log->error('CSV-Helper does not exist: ' . $l_helper_method . ' (' . $p_category_const . '::' . $p_property_id . ')');
        } // if

        return $p_value;
    } // function

    /**
     * Validate user data.
     *
     * @param   isys_cmdb_dao_category $p_dao
     * @param   array                  $p_sync_array
     * @param   bool                   $p_is_sync
     *
     * @return  mixed  A array of error messages or true
     */
    public function validate(isys_cmdb_dao_category $p_dao, $p_sync_array, $p_is_sync = true)
    {
        $l_validation_array = [];

        if (is_array($p_sync_array) && count($p_sync_array) > 0)
        {
            if ($p_is_sync)
            {
                // Transform $p_sync_array first.
                foreach ($p_sync_array[self::CL__SYN_PROPERTY] AS $l_property_tag => $l_property)
                {
                    $l_validation_array[$l_property_tag] = $l_property[C__DATA__VALUE];
                } // foreach
            } // if

            // Now validate data
            $l_validation_messages = $p_dao->validate($l_validation_array);

            if (is_array($l_validation_messages) && count($l_validation_messages))
            {
                foreach ($l_validation_messages AS $l_property_tag => $l_validation_message)
                {
                    $l_validation_messages[$l_property_tag] = $l_property_tag . ': ' . $l_validation_message;
                } // foreach
            } // if

            return $l_validation_messages;
        } // if

        return false;
    } // function

    /**
     * Handle multivalue category.
     *
     * @param  string                 $p_category_const
     * @param  isys_cmdb_dao_category $p_category_dao
     * @param  array                  $p_properties
     */
    public function handle_multivalue_category($p_category_const, isys_cmdb_dao_category $p_category_dao, $p_properties)
    {
        // Set vars.
        $l_properties = $p_properties;
        $l_object_id  = self::get_current_object();

        // Instantiate category dao.
        $l_category_dao = $p_category_dao;

        // Is live-update?
        if (self::is_update())
        {
            // Multivalue-Update-Mde; Untouched.
            if (self::$m_multivalue_update_mode == self::CL__MULTIVALUE_MODE__UNTOUCHED)
            {
                if ($l_category_dao->get_data(null, $l_object_id, '', null, C__RECORD_STATUS__NORMAL)
                        ->num_rows() > 0
                )
                {
                    // There is nothing to do in this case. Let us simply return.
                    $this->m_log->info('Multivalue-Update-Mode is \'untouch\'. Let us skip creating entries.');

                    return;
                }
                else
                {
                    // There are no entries for this object. So let us create it.
                    $this->m_log->info('Multivalue-Update-Mode is \'untouch\'. No entries found. Continue creating the entries.');
                } // if
            }
            else if (self::$m_multivalue_update_mode == self::CL__MULTIVALUE_MODE__OVERWRITE)
            {
                // Overwrite: Purge all existing entries and create logbook entries.

                // Multivalue-Update-Mde; Overwrite.
                $this->m_log->info('Multivalue-Update-Mode is \'overwrite\'. Let us delete existing category entries.');

                // Retrieve category entries.
                $l_category_data = $this->get_data($l_category_dao->get_data(null, $l_object_id), $p_category_const);
                $this->m_log->info('There are ' . count($l_category_data) . ' entries in category to delete.');

                if (count($l_category_data))
                {
                    // Iterate through category entries.
                    foreach ($l_category_data AS $l_key => $l_res)
                    {
                        // Get current entries status.
                        $l_entry_status = $l_res[$this->m_category_map[$p_category_const][self::CL__CAT__TABLE] . '__status'];

                        if ($l_entry_status)
                        {
                            // Get statuses until purge.
                            $l_status_cycle = array_slice($this->m_record_cycle, $l_entry_status - 1);

                            if (is_array($l_status_cycle))
                            {
                                // Iterate through status until purge.
                                foreach ($l_status_cycle AS $s_status)
                                {
                                    // Generate logbook entries.
                                    $this->m_logbook_entries[] = [
                                        'object_id'      => $l_object_id,
                                        'object_type_id' => self::$m_current_object_type,
                                        'category'       => $this->m_category_map[$p_category_const][self::CL__CAT__TITLE],
                                        'changes'        => null,
                                        'event'          => $s_status,
                                        'count_changes'  => 0
                                    ];
                                } // foreach
                            } // if
                        } // if

                        // Finish: Delete category entry from DB.
                        if (isset($this->m_category_map[$p_category_const][self::CL__CAT__TABLE]))
                        {
                            // @note VQH: use delete_entry because relation object also needs to be removed if exists
                            if ($this->m_category_map[$p_category_const][self::CL__CAT__TABLE] == 'isys_catg_custom_fields_list')
                            {
                                // Simulate purge for custom category using rank method
                                $l_category_dao->rank_record($l_key, C__CMDB__RANK__DIRECTION_DELETE, 'isys_catg_custom_fields_list', null, true);
                            }
                            else
                            {
                                $this->m_dao_dialog->delete_entry(
                                    $l_res[$this->m_category_map[$p_category_const][self::CL__CAT__TABLE] . "__id"],
                                    $this->m_category_map[$p_category_const][self::CL__CAT__TABLE]
                                );
                            }

                            $this->m_log->info('Entry #' . $l_res[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]] . " deleted successfully.");
                        } // if

                        $l_category_entries[] = $l_res[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]];
                    } // foreach
                } // if
            }
            else
            {
                if (self::$m_multivalue_update_mode == self::CL__MULTIVALUE_MODE__ADD)
                {
                    $this->m_log->info('Multivalue-Update-Mode: \'add\'. Let us add the given entries to the existing ones.');
                } // if
            } // if
        } // if

        // Create entries if needed.
        if (is_array($l_properties))
        {
            // Iterate through entries array.
            foreach ($l_properties AS $l_cat_data)
            {
                // Build the sync array.
                $l_sync_array = $this->build_for_sync($p_category_const, $l_cat_data);

                if ($l_sync_array)
                {
                    $this->sync($p_category_const, self::get_current_object(), $p_category_dao, $l_sync_array, isys_import_handler_cmdb::C__CREATE);
                }
                else
                {
                    if (!empty(self::$m_log_essential))
                    {
                        $this->m_log->error('An essential information is not set: ' . self::$m_log_essential);
                        $this->m_log->info('Have to skip data.');
                    }
                    else
                    {
                        $this->m_log->error('SyncArray is empty. There is nothing to import !');
                        $this->m_log->info('Have to skip data.');
                    } // if
                } // if
            } // foreach
        } // if
    } // function

    /**
     * Imports the Data
     */
    public function import()
    {
        // Set the memory limit to 2G (if necessary).
        if (isys_convert::to_bytes(ini_get('memory_limit')) < isys_convert::to_bytes('2G'))
        {
            ini_set('memory_limit', '2G');
        } // if

        // Emit beforeImport signal.
        isys_component_signalcollection::get_instance()
            ->emit('mod.import_csv.beforeImport', $this, $this->m_transformed_data, $this->m_created_object_cache, $this->m_category_map);

        self::set_step(self::CL__IMPORT_STEP__IMPORT);
        $this->m_log->info('Starting import.');

        foreach ($this->m_transformed_data as $l_object)
        {
            // Indicate import success of this object.
            $l_success = false;

            try
            {
                // Emit beforeImportObject.
                isys_component_signalcollection::get_instance()
                    ->emit('mod.import_csv.beforeImportObject', $this, $l_object);

                if ($l_object[self::CL__OBJECT_TYPE])
                {
                    $l_created   = true;
                    $l_object_id = false;
                    // Create/Retrieve object id.
                    if (self::get_object_mode() == self::CL__OBJECT_MODE__CREATE)
                    {
                        self::live_object_create();
                        $l_object_id = $this->_create_object(
                            $l_object[self::CL__OBJECT_TYPE],
                            $l_object[self::CL__OBJECT_TITLE],
                            $l_object[self::CL__OBJECT_SYSID],
                            C__RECORD_STATUS__NORMAL,
                            $l_object[self::CL__OBJECT_HOSTNAME],
                            $l_object[self::CL__OBJECT_CATEGORY],
                            $l_object[self::CL__OBJECT_PURPOSE],
                            $l_object[self::CL__OBJECT_CMDBSTATUS],
                            $l_object[self::CL__OBJECT_DESCRIPTION],
                            $l_object[self::CL__OBJECT_TAGS]
                        );
                    }
                    else if (self::get_object_mode() == self::CL__OBJECT_MODE__UPDATE)
                    {
                        // Try to find object
                        if (self::$m_identification_joins != '' || (isset($l_object['identification_condition']) && $l_object['identification_condition'] != ''))
                        {
                            $this->m_log->debug("Searching for Object by identification keys...");
                            $l_object_id = $this->get_object_id_by_identification($l_object['identification_condition']);
                        }
                        else
                        {
                            // Search for the Object by its SYSID.
                            $this->m_log->debug("Searching for Object with SYSID '" . $l_object[self::CL__OBJECT_SYSID] . "'");
                            $l_object_id = $this->search_object($l_object);
                        } // if

                        // Create if necessary
                        if (!$l_object_id)
                        {
                            // Object not found. Lets create it
                            self::live_object_create();
                            $this->m_log->debug("Object not found. Will create a new one.");

                            $l_object_id = $this->_create_object(
                                $l_object[self::CL__OBJECT_TYPE],
                                $l_object[self::CL__OBJECT_TITLE],
                                $l_object[self::CL__OBJECT_SYSID],
                                C__RECORD_STATUS__NORMAL,
                                $l_object[self::CL__OBJECT_HOSTNAME],
                                $l_object[self::CL__OBJECT_CATEGORY],
                                $l_object[self::CL__OBJECT_PURPOSE],
                                $l_object[self::CL__OBJECT_CMDBSTATUS],
                                $l_object[self::CL__OBJECT_DESCRIPTION],
                                $l_object[self::CL__OBJECT_TAGS]
                            );
                        }
                        else
                        {
                            /* We found an object */
                            self::live_object_update();
                            $l_created = false;
                            $this->_update_object(
                                $l_object_id,
                                $l_object[self::CL__OBJECT_HOSTNAME],
                                $l_object[self::CL__OBJECT_CATEGORY],
                                $l_object[self::CL__OBJECT_PURPOSE],
                                $l_object[self::CL__OBJECT_CMDBSTATUS],
                                $l_object[self::CL__OBJECT_DESCRIPTION],
                                $l_object[self::CL__OBJECT_TITLE],
                                $l_object[self::CL__OBJECT_TAGS]
                            );
                        }
                    }

                    if ($l_object_id)
                    {
                        self::set_current_object($l_object_id);
                        $this->m_log->info(
                            sprintf(
                                'Object "%s" of type %s with ID %s successfully ' . ($l_created ? 'created' : 'found') . '!',
                                $l_object[self::CL__OBJECT_TITLE],
                                _L(self::$m_s_dao->get_obj_type_name_by_obj_id($l_object_id)),
                                $l_object_id
                            )
                        );

                        if (isset($l_object[self::CL__CAT__DATA]) && is_array($l_object[self::CL__CAT__DATA]))
                        {
                            foreach ($l_object[self::CL__CAT__DATA] as $l_category_id => $l_properties)
                            {
                                self::reset_category_skip();

                                $this->m_log->info('Validating data for import in category: ' . $this->m_category_map[$l_category_id][self::CL__CAT__TITLE]);

                                if (defined($l_category_id) && self::includes_category(
                                        $l_object[self::CL__OBJECT_TYPE],
                                        constant($l_category_id),
                                        $this->m_category_map[$l_category_id][self::CL__CAT__CAT_TYPE]
                                    )
                                )
                                {
                                    $this->m_log->debug("Category " . $l_category_id . " found in object type.");

                                    $l_category_dao = $this->get_category_dao($l_category_id)
                                        ->set_object_id($l_object_id)
                                        ->set_object_type_id(self::get_current_object_type());

                                    if ($this->m_category_map[$l_category_id][self::CL__CAT__MULTIVALUE])
                                    {
                                        // Current category is multivalued.
                                        $this->handle_multivalue_category($l_category_id, $l_category_dao, $l_properties);
                                    }
                                    else
                                    {
                                        // Category is singlevalued.
                                        $this->handle_singlevalue_category($l_category_id, $l_category_dao, $l_properties);
                                    } // if
                                }
                                else
                                {
                                    if ($l_category_id !== '-')
                                    {
                                        $this->m_log->error(
                                            'Object type "' . _L(
                                                self::$m_s_dao->get_obj_type_name_by_obj_id($l_object_id)
                                            ) . '" does not include Category "' . $this->m_category_map[$l_category_id][self::CL__CAT__TITLE] . '". We have to skip it !'
                                        );
                                    } // if
                                } // if
                            } // foreach
                        }
                        else
                        {
                            // We have to do this here to insert the created object permanently because isys_cmdb_dao::insert_new_obj not close the mysql transaction.
                            $this->m_dao_dialog->apply_update();
                        } // if

                        $this->m_created_object_cache[$l_object_id] = [
                            'title' => $l_object[self::CL__OBJECT_TITLE],
                            'type'  => _L(self::$m_s_dao->get_objtype_name_by_id_as_string($l_object[self::CL__OBJECT_TYPE])),
                            'id'    => $l_object_id
                        ];
                    }
                    else
                    {
                        $this->m_log->error(
                            sprintf(
                                'Unable to create Object "%s" of type %s with ID %s.',
                                $l_object[self::CL__OBJECT_TITLE],
                                _L(self::$m_s_dao->get_objtype_name_by_id_as_string($l_object[self::CL__OBJECT_TYPE])),
                                $l_object_id
                            )
                        );
                    } // if
                }
                else
                {
                    $this->m_log->error(
                        sprintf(
                            'Unable to create Object "%s" of type %s.',
                            $l_object[self::CL__OBJECT_TITLE],
                            _L(self::$m_s_dao->get_objtype_name_by_id_as_string($l_object[self::CL__OBJECT_TYPE]))
                        )
                    );

                    $this->m_log->info('Please check the object type constant of object.');
                } // if

                $l_success = true;

            }
            catch (Exception $e)
            {
                $this->m_log->error($e->getMessage());
            } // try

            // Emit afterImportObject
            isys_component_signalcollection::get_instance()
                ->emit('mod.import_csv.afterImportObject', $this, $l_object, $l_success);
        } // foreach

        if (count($this->m_created_object_cache) > 0)
        {
            $this->m_dao_dialog->object_changed(array_keys($this->m_created_object_cache));
        } // if

        // Emit afterImport signal
        isys_component_signalcollection::get_instance()
            ->emit('mod.import_csv.afterImport', $this, $this->m_transformed_data, $this->m_created_object_cache, $this->m_category_map);

        self::set_step(self::CL__IMPORT_STEP__FINISHED);
    } // function

    /**
     * Returns all created objects.
     *
     * @return  mixed
     */
    public function get_created_objects()
    {
        return $this->m_created_object_cache;
    } // function

    /**
     * Saves log changes.
     *
     * @param  integer $p_import_entry
     */
    public function save_log($p_import_entry)
    {
        if (count($this->m_logbook_entries) > 0)
        {
            $l_event_man = isys_event_manager::getInstance();
            $l_event_man->set_import_id($p_import_entry);

            foreach ($this->m_logbook_entries as $l_entry)
            {
                $l_event_man->triggerImportEvent(
                    $l_entry['event'],
                    _L('LC__UNIVERSAL__CSV_IMPORT'),
                    $l_entry['object_id'],
                    $l_entry['object_type_id'],
                    $l_entry['category'],
                    $l_entry['changes'],
                    null,
                    null,
                    null,
                    null,
                    $l_entry['count_changes']
                );
            } // foreach
        } // if
    } // function

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // Clear all found "auth-*" cache-files. So that it is not necessary to trigger it manually in Cache/Database
        if (self::get_step() === self::CL__IMPORT_STEP__FINISHED)
        {
            try
            {
                $l_cache_files = isys_caching::find('auth-*');

                array_map(
                    function ($l_cache)
                    {
                        $l_cache->clear();
                    },
                    $l_cache_files
                );
            }
            catch (Exception $e)
            {
                $this->m_log->warning('Could not clear cache files for /temp/auth-* with message: ' . $e->getMessage());
            } // try
        } // if
    } // function

    /**
     * Get the Logger instance.
     *
     * @return  \idoit\Component\Logger
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_log()
    {
        return $this->m_log;
    } // function

    /**
     * Get path to the log file.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_log_path()
    {
        return $this->m_log_dir . $this->m_log_file;
    } // function

    /**
     * This will return the log handler to get all records.
     *
     * @param   integer $p_log_level
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_log_records($p_log_level)
    {
        return array_filter(
            $this->m_log_handler->getRecords(),
            function ($p_record) use ($p_log_level)
            {
                return ($p_record['level'] >= $p_log_level);
            }
        );
    } // function

    /**
     * Method for setting the special title indexes.
     *
     * @param   array $p_indexes
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_special_title_index(array $p_indexes)
    {
        $this->m_special_title = $p_indexes;

        return $this;
    } // function

    /**
     * Builds the SQL condition for the quiery to identify existing objects.
     *
     * @param   array $p_line
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function build_conditions($p_line)
    {
        $l_condition_arr    = [];
        $l_condition_string = '';

        if (is_array(self::$m_update_identifiers) && count(self::$m_update_identifiers))
        {
            foreach (self::$m_update_identifiers AS $l_key => $l_ident)
            {
                if ($l_ident == '-1') continue;

                if (!array_key_exists($l_ident, $l_condition_arr) && !empty(self::$m_update_identifiers_map[$l_ident]['field']))
                {
                    $l_condition_arr[$l_ident] = self::$m_update_identifiers_map[$l_ident]['field'] . " = '" . $p_line[self::$m_update_csv_idents[$l_key]] . "'";
                }
                else if (!empty(self::$m_update_identifiers_map[$l_ident]['field']))
                {
                    $l_condition_arr[$l_ident] .= ' OR ' . self::$m_update_identifiers_map[$l_ident]['field'] . " = '" . $p_line[self::$m_update_csv_idents[$l_key]] . "'";
                } // if
            } // foreach
        } // if

        if (is_array($l_condition_arr) && count($l_condition_arr))
        {
            foreach ($l_condition_arr AS $l_condition)
            {
                if ($l_condition_string != '') $l_condition_string .= ' AND ';

                if (strpos($l_condition, 'OR') !== false)
                {
                    $l_condition_string .= '(' . $l_condition . ')';
                }
                else
                {
                    $l_condition_string .= $l_condition;
                } // if
            } // foreach
        } // if

        return $l_condition_string;
    } // function

    /**
     * Sets the global import status.
     *
     * @param  boolean $p_bool
     */
    private function set_import_status($p_bool)
    {
        $this->m_import_status = !!$p_bool;
    } // function

    /**
     * Dummy "virtual" method to prevent error messages.
     *
     * @param   mixed $p_value
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function csv_helper__virtual($p_value)
    {
        return $p_value;
    } // function

    /**
     * Wrapper method for "csv_helper__object". This is necessary, because the CSV import only handle its own helper...
     *
     * @param   string  $p_value
     * @param   array   $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__wan_connected_router ($p_value, $p_property_data)
    {
        return $this->csv_helper__object($p_value, $p_property_data);
    } // function

    /**
     * Wrapper method for "csv_helper__object". This is necessary, because the CSV import only handle its own helper...
     *
     * @param   string  $p_value
     * @param   array   $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__layer_2_assignments ($p_value, $p_property_data)
    {
        return $this->csv_helper__object($p_value, $p_property_data);
    } // function

    /**
     * Wrapper method for "csv_helper__object". This is necessary, because the CSV import only handle its own helper...
     *
     * @param   string  $p_value
     * @param   array   $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__wan_connected_net ($p_value, $p_property_data)
    {
        return $this->csv_helper__object($p_value, $p_property_data);
    } // function


    /**
     * Retrieve layer3net for ip
     *
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  bool|mixed
     * @throws  \Exception
     * @throws  \isys_exception_database
     */
    private function csv_helper__ip_net($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE)
        {
            return $p_value;
        } // if

        if (isset($p_value) && !empty($p_value))
        {
            $this->m_log->debug('Search for Layer-3-Net "' . $p_value . '"...');

            // Use the specific category to find a "layer 3 net" object instead of the object type.
            $l_sql = "SELECT isys_obj__id FROM isys_obj
                LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
	            WHERE isys_obj__title = " . $this->m_dao_dialog->convert_sql_text($p_value) . "
	            AND isys_obj_type__isysgui_cats__id = " . $this->m_dao_dialog->convert_sql_id(C__CATS__NET) . "
	            LIMIT 1;";

            $l_res = $this->m_dao_dialog->retrieve($l_sql);

            if (count($l_res))
            {
                // We found a Layer-3-Net with the given name.
                $this->m_log->debug("Layer-3-Net found.");

                return $l_res->get_row_value('isys_obj__id');
            } // if
        } // if

        return false;
    } // function

    /**
     * This is a dummy for preventing errors
     *
     * @param string $p_value
     *
     * @return bool|mixed
     */
    private function csv_helper__model_title($p_value)
    {
        return $p_value;
    } // function

    /**
     * Helper: Dialog
     *
     * @param string $p_value
     * @param array  $p_property_data
     * @param bool   $p_force
     *
     * @return mixed
     */
    private function csv_helper__dialog($p_value, $p_property_data, $p_force = false)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE && !$p_force)
        {
            return $p_value;
        } // if

        if (isset($p_property_data[self::CL__CAT__PROPERTY__PARAM]) && !isset($p_property_data[self::CL__CAT__PROPERTY__REFERENCES]))
        {
            $p_property_data[self::CL__CAT__PROPERTY__REFERENCES] = [
                $p_property_data[self::CL__CAT__PROPERTY__PARAM],
                $p_property_data[self::CL__CAT__PROPERTY__PARAM] . "__id",
            ];
        } // if

        $l_table_content = [];
        if (isset($p_property_data[self::CL__CAT__PROPERTY__REFERENCES][0]))
        {
            $l_res = $this->m_dao_dialog->get_data($p_property_data[self::CL__CAT__PROPERTY__REFERENCES][0]);

            if ($l_res->num_rows())
            {
                while ($l_row = $l_res->get_row())
                {
                    // Handling for custom category dialogs
                    if (isset($p_property_data[self::CL__CAT__PROPERTY__IDENTIFIER]))
                    {
                        if ($l_row[$p_property_data[self::CL__CAT__PROPERTY__REFERENCES][0] . "__identifier"] == $p_property_data[self::CL__CAT__PROPERTY__IDENTIFIER])
                        {
                            $l_table_content[$l_row[$p_property_data[self::CL__CAT__PROPERTY__REFERENCES][1]]] = _L(
                                $l_row[$p_property_data[self::CL__CAT__PROPERTY__REFERENCES][0] . "__title"]
                            );
                        } // if
                    }
                    else
                    {
                        $l_table_content[$l_row[$p_property_data[self::CL__CAT__PROPERTY__REFERENCES][1]]] = _L(
                            $l_row[$p_property_data[self::CL__CAT__PROPERTY__REFERENCES][0] . "__title"]
                        );
                    } // if
                } // while

                $l_id = array_search($p_value, $l_table_content);

                if ($l_id)
                {
                    $this->m_log->debug('Value \'' . $p_value . '\' matched in table');
                }
                else
                {
                    $this->m_log->debug('Trying to parse it again');

                    foreach ($l_table_content AS $l_table_id => $l_table_title)
                    {
                        if (stristr($l_table_title, $p_value))
                        {
                            $this->m_log->debug("Value exists only as substring in '" . $l_table_title . "'");
                            $l_id = $l_table_id;
                        } // if
                    } // foreach

                    if (!$l_id)
                    {
                        $this->m_log->debug('Value not matched in table');
                    } // if
                } // if

                return $l_id;
            } // if

            $this->m_log->debug('Value not matched in table');

            return false;
        }
        else
        {
            return $p_value;
        } // if
    } // function

    /**
     * Helper: Dialog Plus.
     *
     * @param   string  $p_value
     * @param   array   $p_property_data
     * @param   boolean $p_force
     *
     * @return  mixed
     */
    private function csv_helper__dialog_plus($p_value, $p_property_data, $p_force = false)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE && !$p_force)
        {
            return $p_value;
        } // if

        if (empty($p_value) && strlen($p_value) === 0)
        {
            return null;
        } // if

        if (($l_id = $this->csv_helper__dialog($p_value, $p_property_data, $p_force)))
        {
            return $l_id;
        } // if

        $this->m_log->info("Creating new entry for '$p_value'");
        $l_custom_identifier = null;

        if (isset($p_property_data[self::CL__CAT__PROPERTY__IDENTIFIER]))
        {
            $l_custom_identifier = $p_property_data[self::CL__CAT__PROPERTY__IDENTIFIER];
        } // if

        return $this->m_dao_dialog->create($p_property_data[self::CL__CAT__PROPERTY__REFERENCES][0], $p_value, 50, null, C__RECORD_STATUS__NORMAL, null, $l_custom_identifier);
    } // function

    /**
     * Helper: Custom dialog+
     *
     * @param      $p_value
     * @param      $p_property_data
     * @param bool $p_force
     *
     * @return mixed
     */
    private function csv_helper__custom_category_property_dialog_plus($p_value, $p_property_data, $p_force = false)
    {
        return $this->csv_helper__dialog_plus($p_value, $p_property_data, $p_force);
    } // function

    /**
     * Helper: Yes or No
     *
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__get_yes_or_no($p_value, $p_property_data)
    {
        if ((int) $p_value)
        {
            $p_value = (bool) $p_value;

            return ($p_value) ? 1 : 0;
        }
        else
        {
            return (stristr(_L('LC__UNIVERSAL__YES'), $p_value) !== false) ? 1 : 0;
        } // if
    } // function

    /**
     * Helper: Date
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return mixed
     */
    private function csv_helper__date($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE)
        {
            return $p_value;
        } // if
        return date('Y-m-d H:i:s', strtotime($p_value));
    } // function

    /**
     * Helper: Date
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return mixed
     */
    private function csv_helper__custom_category_property_calendar($p_value, $p_property_data)
    {
        return $this->csv_helper__date($p_value, $p_property_data);
    } // function

    /**
     * Helper: Timeperiod.
     *
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__timeperiod($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE)
        {
            return $p_value;
        } // if

        $l_convert = new isys_convert;
        $l_value   = (int) $p_value;
        $l_unit    = self::prepare_value($p_value);

        if (!empty($l_value) && !empty($l_unit))
        {
            $this->m_log->debug(sprintf("Delivered value '%s' splitted to unit '%s' and value '%s'.", $p_value, $l_unit, $l_value));

            $l_unit_table = $p_property_data[self::CL__CAT__PROPERTY__PARAM]['unit_table'];
            $l_method     = $p_property_data[self::CL__CAT__PROPERTY__PARAM][0];

            $l_unit_table_content = [];

            if ($l_unit_table)
            {
                $l_res = $this->m_dao_dialog->retrieve("SELECT * FROM $l_unit_table WHERE TRUE;");

                if (count($l_res))
                {
                    while ($l_row = $l_res->get_row())
                    {
                        $l_unit_table_content[$l_row[$l_unit_table . '__const']] = [
                            str_replace(
                                [
                                    '(',
                                    ')'
                                ],
                                '',
                                _L($l_row[$l_unit_table . '__title'])
                            ),
                            $l_row[$l_unit_table . '__id']
                        ];
                    } // while

                    if (is_array($l_unit_table_content))
                    {
                        foreach ($l_unit_table_content AS $l_unit_const => $l_unit_data)
                        {
                            if (stristr($l_unit_data[0], $l_unit) || stristr($l_unit, $l_unit_data[0]))
                            {
                                $p_value = [
                                    self::CL__HELPER__VALUE    => (!empty($l_method)) ? $l_convert->$l_method(0, $l_value, $l_unit_const) : $l_value,
                                    self::CL__HELPER__UNIT_ID  => $l_unit_data[1],
                                    self::CL__HELPER__UNIT_ROW => $p_property_data[self::CL__CAT__PROPERTY__PARAM]['unit_row'],
                                    self::CL__HELPER__UNIT_TAG => $p_property_data[self::CL__CAT__PROPERTY__PARAM]['unit_tag'],
                                ];
                            } // if
                        } // foreach
                    } // if
                } // if
            } // if
        }
        else
        {
            $this->m_log->debug("Unable to split value '" . $p_value . "'.");
        } // if

        return (is_array($p_value)) ? $p_value : null;
    } // function

    /**
     * Helper: Timeperiod.
     *
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__hostname_handler($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE)
        {
            return $p_value;
        } // if

        return $p_value;
    } // function

    /**
     * Helper: Convert
     *
     * @param $p_value
     * @param $p_property_data
     *
     * @return mixed
     */
    private function csv_helper__convert($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE)
        {
            return $p_value;
        } // if

        $l_value = (float) str_replace(',', '.', $p_value);
        $l_unit  = self::prepare_value($p_value);

        if (!empty($l_value) && !empty($l_unit))
        {
            $this->m_log->debug(
                sprintf(
                    "Delivered value '%s' splitted to unit '%s' and value '%s'.",
                    $p_value,
                    $l_unit,
                    $l_value
                )
            );

            $l_unit_table_content = [];

            /* ----------------  Compare unit with tables title row ----------------  */
            $l_unit_table = $p_property_data[self::CL__CAT__PROPERTY__PARAM]['unit_table'];
            /* Retrieve unit table content */
            if ($l_unit_table)
            {
                $l_sql = "SELECT * FROM $l_unit_table WHERE TRUE";
                $l_res = $this->m_dao_dialog->retrieve($l_sql);

                if ($l_res->num_rows())
                {
                    while ($l_row = $l_res->get_row())
                    {
                        $l_unit_table_content[$l_row[$l_unit_table . "__const"]] = [
                            str_replace('/', '', _L($l_row[$l_unit_table . "__title"])),
                            $l_row[$l_unit_table . '__id']
                        ];
                    } // while

                    /* Comparison parsed unit == unit-table-title */
                    if (is_array($l_unit_table_content))
                    {
                        foreach ($l_unit_table_content AS $l_unit_const => $l_unit_title)
                        {
                            if (stristr($l_unit_title[0], $l_unit))
                            {
                                $p_value = [
                                    self::CL__HELPER__VALUE    => $l_value,
                                    self::CL__HELPER__UNIT_ID  => $l_unit_title[1],
                                    self::CL__HELPER__UNIT_ROW => $p_property_data[self::CL__CAT__PROPERTY__PARAM]['unit_row'],
                                    self::CL__HELPER__UNIT_TAG => $p_property_data[self::CL__CAT__PROPERTY__PARAM]['unit_tag'],
                                ];
                            } // if
                        } // foreach
                    } // if
                } // if
            }
        }
        else
        {
            $this->m_log->debug("Unable to split value '" . $p_value . "'.");
        } // if

        return (is_array($p_value)) ? $p_value : null;
    } // function

    /**
     * Helper for Connections.
     *
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  mixed
     */
    private function csv_helper__connection($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__DATASTRUCTURE)
        {
            return $p_value;
        } // if

        if ($this->m_tmp_index === null || !isset($this->m_object_type_assignment[$this->m_tmp_index]))
        {
            $this->m_log->notice('Simple search for object with name "' . $p_value . '"');
            $l_obj_id = $this->search_object_by_title($p_value);
            $this->m_log->debug(' > Found ' . ($l_obj_id ? ' a result!' : ' nothing.'));

            return $l_obj_id;
        } // if

        $l_object_type = null;

        if (defined($this->m_object_type_assignment[$this->m_tmp_index]['object-type']))
        {
            $l_object_type = constant($this->m_object_type_assignment[$this->m_tmp_index]['object-type']);
        } // if

        $this->m_log->notice('Search for object with name "' . $p_value . '" and type "' . _L($this->m_dao_dialog->get_objtype_name_by_id_as_string($l_object_type)) . '"');
        $l_obj_id = $this->search_object_by_title($p_value, $l_object_type) ?: false;
        $this->m_log->debug(' > Found ' . ($l_obj_id ? ' a result!' : ' nothing.'));

        if (!$l_obj_id && $l_object_type > 0 && $this->m_object_type_assignment[$this->m_tmp_index]['create-object'] == 1)
        {
            $this->m_log->info(' > A new object will be created!');
            $l_obj_id = $this->m_dao_dialog->insert_new_obj($l_object_type, null, $p_value, null, C__RECORD_STATUS__NORMAL, null, null, true);
        } // if

        return $l_obj_id;
    } // function

    /**
     * Helper for referenced Values
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return mixed
     */
    private function csv_helper__get_reference_value($p_value, $p_property_data)
    {
        return $p_value;
    } // function

    /**
     * Helper for position
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return mixed
     */
    private function csv_helper__location_property_pos($p_value, $p_property_data)
    {
        return $p_value;
    } // function

    /**
     * Helper for formating money values
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return int
     */
    private function csv_helper__money_format($p_value, $p_property_data)
    {
        return isys_helper::filter_number($p_value);
    } // function

    /**
     * Helper for creating contact bundles
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return boolean
     */
    private function csv_helper__contact($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__IMPORT)
        {
            return $p_value;
        } // if

        /** @var isys_contact_dao_reference $l_dao_contact */
        $l_dao_contact = isys_contact_dao_reference::factory($this->m_dao_dialog->get_database_component());
        $l_dao_contact->clear();

        $l_object_id = $this->csv_helper__object($p_value, $p_property_data);

        if ($l_object_id)
        {
            $l_dao_contact->insert_data_item($l_object_id);
            $l_dao_contact->save();

            return $l_dao_contact->get_id();
        } // if

        return false;
    } // function

    /**
     * Dummy-Helper for location.
     * The real job is done by callback_location.
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return string
     */
    private function csv_helper__location($p_value, $p_property_data)
    {
        return $p_value;
    } // function

    /**
     * Search for object by title.
     *
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  mixed
     * @throws  \Exception
     * @throws  \isys_exception_database
     */
    private function csv_helper__object($p_value, $p_property_data)
    {
        if (self::get_step() != self::CL__IMPORT_STEP__IMPORT)
        {
            return $p_value;
        } // if

        if ($this->m_tmp_index !== null && isset($this->m_object_type_assignment[$this->m_tmp_index]))
        {
            $l_obj_type_id = null;

            if (isset($this->m_object_type_assignment[$this->m_tmp_index]['object-type']) && defined($this->m_object_type_assignment[$this->m_tmp_index]['object-type']))
            {
                $l_obj_type_id = constant($this->m_object_type_assignment[$this->m_tmp_index]['object-type']);
                $this->m_log->notice(
                    'Search for object with name "' . $p_value . '" and type "' . _L($this->m_dao_dialog->get_objtype_name_by_id_as_string($l_obj_type_id)) . '"'
                );
            } // if

            $l_obj_id = $this->search_object_by_title($p_value, $l_obj_type_id) ?: false;
            $this->m_log->debug(' > Found ' . ($l_obj_id ? ' a result!' : ' nothing.'));

            if (!$l_obj_id && $l_obj_type_id > 0 && $this->m_object_type_assignment[$this->m_tmp_index]['create-object'] == 1)
            {
                $this->m_log->info(' > A new object will be created!');
                $l_obj_id = $this->m_dao_dialog->insert_new_obj($l_obj_type_id, null, $p_value, null, C__RECORD_STATUS__NORMAL, null, null, true);
            } // if

            return $l_obj_id;
        } // if

        $l_object_id = false;

        $this->m_log->notice('Simple search for object with name "' . $p_value . '"');
        $l_res = $this->m_dao_dialog->retrieve(
            'SELECT isys_obj__id, isys_obj__isys_obj_type__id FROM isys_obj WHERE TRIM(isys_obj__title) = BINARY ' . $this->m_dao_dialog->convert_sql_text($p_value) . ';'
        );

        if (count($l_res) === 1)
        {
            $l_row = $l_res->get_row();

            $l_object_id = $l_row['isys_obj__id'];
            $this->m_log->debug(' > Found one result, proceding... (#' . $l_object_id . ')');

            // Location ROW.
            if ($p_property_data[self::CL__CAT__PROPERTY__ROW] == 'isys_catg_location_list__parentid')
            {
                $this->m_log->debug('We have to check whether the found object is an Container');

                if (self::is_container($l_row['isys_obj__isys_obj_type__id']) || $l_row['isys_obj__id'] == C__OBJ__ROOT_LOCATION)
                {
                    $this->m_log->debug('Found Object is a container');
                }
                else
                {
                    $this->m_log->warning('Object is not a container');
                    unset($l_object_id);
                } // if
            } // if
        }
        else
        {
            if (count($l_res) > 1)
            {
                $this->m_log->debug(' > Found more than one result, skipping...');
            }
            else
            {
                $this->m_log->debug(' > No results...');
            } // if
        } // if

        return $l_object_id;
    } // function

    /**
     * Wrapper for csv_helper__object
     *
     * @param string $p_value
     * @param array  $p_property_data
     *
     * @return bool
     */
    private function csv_helper__custom_category_property_object($p_value, $p_property_data)
    {
        return $this->csv_helper__object($p_value, $p_property_data);
    } // function

    /**
     * @param   string $p_value
     * @param   array  $p_property_data
     *
     * @return  array
     */
    private function csv_helper__dialog_multiselect($p_value, $p_property_data)
    {
        $l_return = $l_items = array_map('trim', explode(';', $p_value));

        if (isset($p_property_data['property_table']))
        {
            $l_return = [];

            foreach ($l_items as $l_item)
            {
                $p_property_data[self::CL__CAT__PROPERTY__REFERENCES] = [$p_property_data['property_table'], $p_property_data['property_table'] . '__id'];

                $l_item_id = $this->csv_helper__dialog($l_item, $p_property_data, true);

                if (!$l_item_id)
                {
                    $this->m_log->debug("Creating value '" . $l_item . "' in table '" . $p_property_data['property_table'] . "'!");
                    $l_return[] = $this->m_dao_dialog->create($p_property_data['property_table'], $l_item, 50, null, C__RECORD_STATUS__NORMAL);
                }
                else
                {
                    $this->m_log->debug("Found value '" . $l_item . "' in table '" . $p_property_data['property_table'] . "'!");
                    $l_return[] = $l_item_id;
                } // if
            } // foreach
        } // if

        return [self::CL__HELPER__VALUE => $l_return];
    } // function

    /**
     * Search for object by title.
     *
     * @param   string  $p_title
     * @param   integer $p_obj_type
     *
     * @return  mixed
     */
    private function search_object_by_title($p_title, $p_obj_type = null)
    {
        if (!empty($p_title))
        {
            return $this->m_dao_dialog->get_obj_id_by_title($p_title, $p_obj_type);
        } // if

        return false;
    } // function

    /**
     * Builds the specific array structure for SYNC.
     *
     * @param   string $p_category_const
     * @param   array  $p_properties
     *
     * @return  mixed
     */
    private function build_for_sync($p_category_const, $p_properties)
    {
        $l_property_array = [];

        if (is_array($p_properties))
        {
            foreach ($p_properties as $l_property_id => $l_property_value)
            {
                if (!empty($l_property_value))
                {
                    if (is_array($l_property_value))
                    {
                        $l_property_array[self::CL__SYN_PROPERTY][$this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$l_property_id][self::CL__CAT__PROPERTY__TAG]][C__DATA__VALUE] = $l_property_value[self::CL__HELPER__VALUE];
                        $l_property_array[self::CL__SYN_PROPERTY][$this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$l_property_id][self::CL__CAT__PROPERTY__TAG]]['id']           = $l_property_value;

                        if (isset($l_property_value[self::CL__HELPER__UNIT_TAG]) && !empty($l_property_value[self::CL__HELPER__UNIT_TAG]))
                        {
                            $l_unit_tag                                                            = $l_property_value[self::CL__HELPER__UNIT_TAG];
                            $l_property_array[self::CL__SYN_PROPERTY][$l_unit_tag][C__DATA__VALUE] = $l_property_value[self::CL__HELPER__UNIT_ID];
                            $l_property_array[self::CL__SYN_PROPERTY][$l_unit_tag]['id']           = $l_property_value[self::CL__HELPER__UNIT_ID];
                        } // if
                    }
                    else
                    {
                        // Try finding out the original mapping-index.
                        if (isset($this->m_assignment_map[$p_category_const][$l_property_id]))
                        {
                            $this->m_tmp_index = $this->m_assignment_map[$p_category_const][$l_property_id];
                        }
                        else
                        {
                            foreach ($this->m_assignment_map AS $l_index => $l_assignment)
                            {
                                if ($l_assignment['category'] === $p_category_const && $l_assignment['property'] === $l_property_id)
                                {
                                    // Index found
                                    $this->m_tmp_index = $l_index;
                                    break;
                                } // if
                            } // foreach
                        } // if

                        $l_property_value  = $this->universal_helper($p_category_const, $l_property_id, $l_property_value);
                        $this->m_tmp_index = null;

                        $l_property_array[self::CL__SYN_PROPERTY][$this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$l_property_id][self::CL__CAT__PROPERTY__TAG]][C__DATA__VALUE] = $l_property_value;
                        $l_property_array[self::CL__SYN_PROPERTY][$this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES][$l_property_id][self::CL__CAT__PROPERTY__TAG]]['id']           = $l_property_value;
                    } // if
                } // if
            } // foreach
        } // if

        $this->execute_callback($p_category_const, $l_property_array);

        return $this->check_essential_properties($p_category_const, $l_property_array);
    } // function

    /**
     * Execute registered callbacks
     *
     * @param string $p_category_const
     * @param array  $p_property_array
     */
    private function execute_callback($p_category_const, &$p_property_array)
    {
        if (isset(self::$m_callback_register[$p_category_const]))
        {
            $l_func_str = self::$m_callback_register[$p_category_const];
            if (method_exists($this, $l_func_str))
            {
                $this->m_log->debug("Executing callback: '" . $l_func_str . "'");
                $this->$l_func_str($this->m_category_map[$p_category_const], $p_property_array);
            }
            else
            {
                $this->m_log->error("Unable to execute callback '" . $l_func_str . "'. Method does not exist.");
            } // if
        } // if
    } // function

    /**
     * Callback for port
     *
     * @param array $p_category_structure
     * @param array $p_category_data
     */
    private function callback_port($p_category_structure, &$p_category_data)
    {
        if (isset($p_category_data['properties']['virtual_interface']['value']))
        {
            $l_interface_title = $p_category_data['properties']['virtual_interface']['value'];

            /** @var isys_cmdb_dao_category_g_network_interface $l_interface_dao */
            $l_interface_dao = isys_cmdb_dao_category_g_network_interface::instance($this->m_dao_dialog->get_database_component());
            $l_res           = $l_interface_dao->get_data(
                null,
                self::get_current_object(),
                "AND isys_catg_netp_list__title = " . $l_interface_dao->convert_sql_text($l_interface_title)
            );

            if ($l_res->num_rows())
            {
                $l_row                                      = $l_res->get_row();
                $p_category_data['properties']['interface'] = [
                    "value" => $l_row['isys_catg_netp_list__id'],
                    "id"    => $l_row['isys_catg_netp_list__id'],
                ];
            }
            else
            {
                $this->m_log->error("Unable to find the interface named '" . $p_category_data['properties']['virtual_interface']['value'] . "'.");
            } // if
        } // if
    } // function

    /**
     * Callback for model
     *
     * @param array $p_category_structure
     * @param array $p_category_data
     *
     * @throws \Exception
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     */
    private function callback_model($p_category_structure, &$p_category_data)
    {
        if (isset($p_category_data['properties']['manufacturer']['value']) && isset($p_category_data['properties']['title']['value']))
        {
            $l_title           = $p_category_data['properties']['title']['value'];
            $l_manufacturer_id = $p_category_data['properties']['manufacturer']['value'];

            $l_sql = "SELECT isys_model_title__id FROM isys_model_title " . "WHERE isys_model_title__title = " . $this->m_dao_dialog->convert_sql_text(
                    $l_title
                ) . " AND " . "isys_model_title__isys_model_manufacturer__id = " . $this->m_dao_dialog->convert_sql_id($l_manufacturer_id) . ";";

            $l_res = $this->m_dao_dialog->retrieve($l_sql);

            if (!$l_res->num_rows())
            {
                $l_sql = "INSERT INTO isys_model_title VALUES(NULL, " . $this->m_dao_dialog->convert_sql_text(
                        $l_title
                    ) . ", NULL,NULL,NULL,1,NULL, " . $this->m_dao_dialog->convert_sql_id($l_manufacturer_id) . ");";

                $this->m_dao_dialog->update($l_sql) && $this->m_dao_dialog->apply_update();

                $p_category_data['properties']['title']['value'] = $p_category_data['properties']['title']['id'] = $this->m_dao_dialog->get_last_insert_id();
            }
            else
            {
                $l_row                                           = $l_res->get_row();
                $p_category_data['properties']['title']['value'] = $p_category_data['properties']['title']['id'] = $l_row['isys_model_title__id'];
            } // if
        } // if
    } // function

    /**
     * Callback for contact
     *
     * @param array $p_category_structure
     * @param array $p_category_data
     */
    private function callback_contact($p_category_structure, &$p_category_data)
    {
        if ($p_category_data['properties']['contact_object']['value'])
        {
            $l_contact = $p_category_data['properties']['contact_object']['value'];

            // Translate object title to ID.
            if (!is_numeric($l_contact))
            {
                $this->m_log->debug("Given contact has to be translated to an ID ...");
                $l_contact_id = $this->m_dao_dialog->get_obj_id_by_title($l_contact);

                if (!$l_contact_id)
                {
                    $this->m_log->info("Object with title \"$l_contact\" not found!");
                    $p_category_data['properties']['contact_object']['value'] = null;

                    return;
                }
                else
                {
                    $this->m_log->info("Object found: $l_contact ($l_contact_id)");
                    $p_category_data['properties']['contact_object']['value'] = $l_contact_id;
                } // if
            } // if

            $p_category_data['properties']['contact'] = $p_category_data['properties']['contact_object'];
        } // if
    }

    /**
     * Callback for location.
     *
     * @param   array $p_category_structure
     * @param   array $p_category_data
     *
     * @throws  \Exception
     * @throws  \isys_exception_database
     */
    private function callback_location($p_category_structure, &$p_category_data)
    {
        $l_index = null;
        if ($p_category_data['properties']['parent']['value'])
        {
            // Try finding out the original mapping-index.
            if (isset($this->m_assignment_map['C__CATG__LOCATION']['parent']))
            {
                $l_index = $this->m_assignment_map['C__CATG__LOCATION']['parent'];
            }
            else
            {
                foreach ($this->m_assignment_map AS $l_tmp_index => $l_assignment)
                {
                    if ($l_assignment['category'] === 'C__CATG__LOCATION' && $l_assignment['property'] === 'parent')
                    {
                        $l_index = $l_tmp_index;
                        break;
                    } // if
                } // foreach
            } // if

            if ($l_index === null)
            {
                $this->m_log->error('Index for the location for property "parent" could not be determined. Skipping location assignment.');

                return;
            } // if

            $l_location_id = $p_category_data['properties']['parent']['value'];

            // Translate object title to ID.
            $this->m_log->debug('Given Location has to be translated to an ID ...');
            $l_special_search = false;

            if (isset(self::$m_prop_search['C__CATG__LOCATION_' . $l_index]))
            {
                $l_search_information    = json_decode(self::$m_prop_search['C__CATG__LOCATION_' . $l_index], true);
                $l_special_search        = true;
                $l_special_search_table  = $l_search_information['table'];
                $l_special_search_column = $l_search_information['select'];
                $l_special_search_field  = $l_search_information['search'];
            } // if

            if ($l_special_search && isset($l_special_search_column) && isset($l_special_search_field) && isset($l_special_search_table))
            {
                $this->m_log->debug('Searching location object "' . $l_location_id . '" in ' . $l_special_search_field . '.');

                $l_sql = 'SELECT ' . $l_special_search_column . ' FROM ' . $l_special_search_table . ' WHERE ' . $l_special_search_field . ' = ' . $this->m_dao_dialog->convert_sql_text(
                        $l_location_id
                    );

                $l_location_id = self::$m_s_dao->retrieve($l_sql)
                    ->get_row_value('isys_obj__id');
            }
            else
            {
                if (is_numeric($l_location_id))
                {
                    if (!$this->m_dao_dialog->obj_exists($l_location_id))
                    {
                        $this->m_log->debug('Location object with id "' . $l_location_id . '" does not exist.');

                        return;
                    } // if
                }
                else
                {
                    $l_location_id = $this->m_dao_dialog->get_obj_id_by_title($l_location_id);
                } // if
            } // if

            if (!$l_location_id)
            {
                $this->m_log->info('Location object with title "' . $p_category_data['properties']['parent']['value'] . '" not found!');

                $l_object_type = null;

                if (defined($this->m_object_type_assignment[$l_index]['object-type']))
                {
                    $l_object_type = constant($this->m_object_type_assignment[$l_index]['object-type']);
                }
                else
                {
                    // If nothing has been selected (or "automatic") we create rooms.
                    $l_object_type = C__OBJTYPE__ROOM;
                } // if

                if ($this->m_object_type_assignment[$l_index]['create-object'] == 1 && $l_object_type > 0)
                {
                    $this->m_log->info(' > A new object will be created!');
                    $p_category_data['properties']['parent']['value'] = $l_location_id = $this->m_dao_dialog->insert_new_obj(
                        $l_object_type,
                        null,
                        $p_category_data['properties']['parent']['value'],
                        null,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null,
                        true
                    );
                }
                else
                {
                    $p_category_data['properties']['parent']['value'] = null;

                    return;
                } // if
            }
            else
            {
                $this->m_log->info('Location Object found: "' . $p_category_data['properties']['parent']['value'] . '" (' . $l_location_id . ')');
                $p_category_data['properties']['parent']['value'] = $l_location_id;
            } // if

            $l_location_objtype_id = $this->m_dao_dialog->get_objTypeID($l_location_id);

            // Abort operation if objecttype is not a container.
            $this->m_log->debug("Checking whether the objecttype is a container or not");
            if (!self::is_container($l_location_objtype_id))
            {
                $this->m_log->debug('Objecttype is no container, so "' . $p_category_data['properties']['parent']['value'] . '" could not be used as a location parent!');
                $p_category_data['properties']['parent']['value'] = null;

                return;
            } // if

            if ($l_location_objtype_id == C__OBJTYPE__ENCLOSURE)
            {
                /** @var isys_cmdb_dao_category_g_location $l_location_dao */
                $l_location_dao = isys_cmdb_dao_category_g_location::instance($this->m_dao_dialog->get_database_component());

                /** @var isys_cmdb_dao_category_g_formfactor $l_formfactor_dao */
                $l_formfactor_dao = isys_cmdb_dao_category_g_formfactor::instance($this->m_dao_dialog->get_database_component());

                $l_location_rack_height = $l_formfactor_dao->get_rack_hu($l_location_id);

                if ($l_location_rack_height && $p_category_data['properties']['pos']['id'])
                {
                    $p_category_data['properties']['pos']['id'] = $p_category_data['properties']['pos']['value'] = $l_location_rack_height - $p_category_data['properties']['pos']['id'] + 1;
                } // if

                if ($p_category_data['properties']['insertion'])
                {
                    $l_insertions        = $l_location_dao->callback_property_insertion(isys_request::factory());
                    $l_current_insertion = $p_category_data['properties']['insertion']['id'];

                    if (is_array($l_insertions))
                    {
                        foreach ($l_insertions AS $l_insertion_id => $l_insertion_title)
                        {
                            if (strcasecmp($l_current_insertion, $l_insertion_title) == 0)
                            {
                                $p_category_data['properties']['insertion']['value'] = $p_category_data['properties']['insertion']['id'] = $l_insertion_id;
                                continue;
                            } // if
                        } // foreach
                    } // if
                } // if

                if ($p_category_data['properties']['option'])
                {
                    $l_options          = $l_location_dao->callback_property_assembly_options(
                        isys_request::factory()
                            ->set_row(['isys_catg_location_list__parentid' => $p_category_data['properties']['parent']['value']])
                    );
                    $l_current_position = $p_category_data['properties']['option']['id'];

                    if (is_array($l_options))
                    {
                        foreach ($l_options AS $l_option_id => $l_option_title)
                        {
                            if (strcasecmp($l_current_position, $l_option_title) == 0)
                            {
                                $p_category_data['properties']['option']['value'] = $p_category_data['properties']['option']['id'] = $l_option_id;
                                continue;
                            } // if
                        } // foreach
                    } // if
                } // if
            } // if
        } // if
    } // function

    /**
     * Callback for ip
     *
     * @param array $p_category_structure
     * @param array $p_category_data
     *
     * @throws \isys_exception_general
     */
    private function callback_ip($p_category_structure, &$p_category_data)
    {
        if (isset($p_category_data['properties']['virtual_ip']['value']))
        {
            $l_ip   = $p_category_data['properties']['virtual_ip']['value'];
            $l_type = (filter_var($l_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) ? 4 : ((filter_var($l_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) ? 6 : false);

            if ($l_type)
            {
                $l_ip_configuration = [
                    "net_type"                      => [
                        "value" => constant("C__CATS_NET_TYPE__IPV" . $l_type),
                        "id"    => constant("C__CATS_NET_TYPE__IPV" . $l_type),
                    ],
                    "ipv" . $l_type . "_assignment" => [
                        "value" => 1,
                        "id"    => 1
                    ],
                    "ipv" . $l_type . "_address"    => [
                        "value" => $l_ip,
                        "id"    => $l_ip
                    ],
                ];

                // If no net has been found
                if (!isset($p_category_data['properties']['net']) || $p_category_data['properties']['net']['value'] === false)
                {
                    $l_ip_configuration["net"] = [
                        "value" => constant("C__OBJ__NET_GLOBAL_IPV" . $l_type),
                        "id"    => constant("C__OBJ__NET_GLOBAL_IPV" . $l_type)
                    ];
                } // if

                if (isset($p_category_data['properties']['virtual_ip_assignment']['value']))
                {
                    $l_ip_assignment = $p_category_data['properties']['virtual_ip_assignment']['value'];
                    $l_ip_assignment = $this->csv_helper__dialog(
                        $l_ip_assignment,
                        [
                            self::CL__CAT__PROPERTY__REFERENCES => [
                                (($l_type == 4) ? "isys_ip_assignment" : "isys_ipv6_assignment"),
                                (($l_type == 4) ? "isys_ip_assignment__id" : "isys_ipv6_assignment__id")
                            ]
                        ],
                        true
                    );

                    if ($l_ip_assignment)
                    {
                        $l_ip_configuration["ipv" . $l_type . "_assignment"] = [
                            "id"    => $l_ip_assignment,
                            "value" => $l_ip_assignment
                        ];
                    } // if
                } // if

                if (is_array($l_ip_configuration))
                {
                    foreach ($l_ip_configuration AS $l_tag => $l_property)
                    {
                        $p_category_data['properties'][$l_tag] = $l_property;
                    } // foreach
                } // if

                /** @var isys_cmdb_dao_category_g_ip $l_dao_ip */
                $l_dao_ip = isys_factory_cmdb_category_dao::get_instance_by_id(C__CMDB__CATEGORY__TYPE_GLOBAL, C__CATG__IP, $this->m_dao_dialog->get_database_component());

                // 1. cmdb.unique.hostname
                if (isys_tenantsettings::get('cmdb.unique.hostname') && !$l_dao_ip->is_unique_hostname(
                        self::get_current_object(),
                        $p_category_data['properties']['hostname']['value'],
                        $p_category_data['properties']['net']['value']
                    )
                )
                {
                    $this->m_log->error('The given hostname is already used in net. We will set it to empty.');

                    unset($p_category_data['properties']['hostname']);
                } // if

                // 2. cmdb.unique.ip-address
                if (isys_tenantsettings::get('cmdb.unique.ip-address') && !$l_dao_ip->is_unique_ip(
                        self::get_current_object(),
                        $p_category_data['properties']['virtual_ip']['value'],
                        $p_category_data['properties']['net']['value']
                    )
                )
                {
                    $this->m_log->error('The given ip-address is already used in net. We will set it to empty.');
                } // if
            }
            else
            {
                $this->m_log->error("Unable to detect ip type. We have to skip it.");
            } // if
        } // if

        if (isset($p_category_data['properties']['virtual_port_assignment']['value']))
        {
            /** @var isys_cmdb_dao_category_g_network_port $l_port_dao */
            $l_port_dao   = isys_cmdb_dao_category_g_network_port::instance($this->m_dao_dialog->get_database_component());
            $l_port_title = $p_category_data['properties']['virtual_port_assignment']['value'];
            $l_port_res   = $l_port_dao->get_data(
                null,
                self::get_current_object(),
                "AND isys_catg_port_list__title = " . $l_port_dao->convert_sql_text($l_port_title)
            );

            if ($l_port_res->num_rows())
            {
                $l_port_row = $l_port_res->get_row();
                $l_port_id  = $l_port_row['isys_catg_port_list__id'];
            }
            else
            {
                /** @var isys_cmdb_dao_category_g_network_ifacel $l_logport_dao */
                $l_logport_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_dao_dialog->get_database_component());

                $l_logport_res = $l_logport_dao->get_data(
                    null,
                    self::get_current_object(),
                    "AND isys_catg_log_port_list.isys_catg_log_port_list__title = " . $l_logport_dao->convert_sql_text($l_port_title)
                );

                if ($l_logport_res->num_rows())
                {
                    $l_logport_row = $l_logport_res->get_row();
                    $l_log_port_id = $l_logport_row['isys_catg_log_port_list__id'];
                } // if
            } // if

            if (!empty($l_port_id) || !empty($l_log_port_id))
            {

                if (!empty($l_port_id))
                {
                    $l_tag = "assigned_port";
                    $l_id  = $l_port_id . '_' . 'C__CMDB__SUBCAT__NETWORK_PORT';    // id_constant because of the validation
                }
                else
                {
                    $l_tag = "assigned_log_port";
                    $l_id  = $l_log_port_id . '_' . 'C__CMDB__SUBCAT__NETWORK_INTERFACE_L';    // id_constant because of the validation
                } // if

                $p_category_data['properties'][$l_tag] = [
                    "value" => $l_id,
                    "id"    => $l_id
                ];
            } // if
        } // if
    } // function

    /**
     * If category has any reference to isys_connection,
     * we have to set the tags to create empty connections
     *
     * @param string $p_category_const
     * @param string $p_property_array
     */
    private function set_connections($p_category_const, &$p_property_array)
    {
        if (isset($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES]) && is_array($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES]))
        {
            foreach ($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES] AS $l_property)
            {
                if ($l_property[self::CL__CAT__PROPERTY__MODE] == 'connection')
                {
                    $p_property_array[self::CL__SYN_PROPERTY][$l_property[self::CL__CAT__PROPERTY__TAG]] = [
                        C__DATA__VALUE => null,
                        'id'           => null
                    ];
                } // if
            } // foreach
        } // if
    } // function

    /**
     * Checks whether all essential properties are setted
     * to prevent mysql exceptions
     *
     * @param string $p_category_const
     * @param string $p_property
     *
     * @return mixed
     */
    private function check_essential_properties($p_category_const, $p_property)
    {
        if (isset($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES]) && $this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES])
        {
            foreach ($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES] AS $l_property)
            {
                if ($l_property[self::CL__CAT__PROPERTY__ESSENTIALITY])
                {
                    if (!isset($p_property[self::CL__SYN_PROPERTY][$l_property[self::CL__CAT__PROPERTY__TAG]][C__DATA__VALUE]) && $l_property[self::CL__CAT__PROPERTY__MODE] != 'connection')
                    {
                        self::$m_log_essential = $l_property[self::CL__CAT__PROPERTY__TITLE];

                        return false;
                    } // if
                } // if
            } // foreach
        } // if

        self::$m_log_essential = null;

        return $p_property;
    } // function

    /**
     * Returns the tag of the unit property
     *
     * @param string $p_category_const
     * @param array  $p_row
     *
     * @return mixed
     */
    private function get_tag_of_unit_property($p_category_const, $p_row)
    {
        foreach ($this->m_category_map[$p_category_const][self::CL__CAT__PROPERTIES] AS $l_property)
        {
            if ($l_property[self::CL__CAT__PROPERTY__ROW] == $p_row)
            {
                return $l_property[self::CL__CAT__PROPERTY__TAG];
            } // if
        } // foreach

        return false;
    } // function

    /**
     * Searchs an object by its SYSID
     *
     * @param   string $p_object
     *
     * @return  mixed
     */
    private function search_object($p_object)
    {
        $l_result_id = false;

        // First try detect object over hostname, serial, mac combination.
        $l_hostname = $p_object[self::CL__OBJECT_HOSTNAME];
        $l_title    = $p_object[self::CL__OBJECT_TITLE];
        $l_type     = $p_object[self::CL__OBJECT_TYPE];
        $l_serial   = null;
        $l_mac      = null;

        if (isset($p_object[self::CL__CAT__DATA]['C__CATG__MODEL']) && isset($p_object[self::CL__CAT__DATA]['C__CATG__MODEL']['serial']) && !empty($p_object[self::CL__CAT__DATA]['C__CATG__MODEL']['serial']))
        {
            $l_serial = $p_object[self::CL__CAT__DATA]['C__CATG__MODEL']['serial'];
        } // if

        if (isset($p_object[self::CL__CAT__DATA]['C__CMDB__SUBCAT__NETWORK_PORT']) && is_array(
                $p_object[self::CL__CAT__DATA]['C__CMDB__SUBCAT__NETWORK_PORT']
            ) && isset($p_object[self::CL__CAT__DATA]['C__CMDB__SUBCAT__NETWORK_PORT'][0]['mac']) && !empty($p_object[self::CL__CAT__DATA]['C__CMDB__SUBCAT__NETWORK_PORT'][0]['mac'])
        )
        {
            $l_mac = $p_object[self::CL__CAT__DATA]['C__CMDB__SUBCAT__NETWORK_PORT'][0]['mac'];
        } // if

        if (self::counter($l_hostname, $l_serial, $l_mac) >= 2)
        {
            $l_result_id = $this->m_dao_dialog->get_object_by_hostname_serial_mac(
                $l_hostname,
                $l_serial,
                $l_mac
            );
            $this->m_log->debug("Try to detect object over hostname, serial, mac combination.");

            // If we don`t have a ResultID we should try to detect one with the SYS-ID.
            if (!$l_result_id)
            {
                $this->m_log->debug("No matches.");

                if (!empty($p_object[self::CL__OBJECT_SYSID]))
                {
                    $l_result_id = $this->m_dao_dialog->get_obj_id_by_sysid($p_object[self::CL__OBJECT_SYSID]);

                    $this->m_log->debug("Try to detect object over SYSID.");
                    if (!$l_result_id)
                    {
                        $this->m_log->debug("No matches.");
                    } // if
                } // if
            }
            else
            {
                $this->m_log->debug('Existing object found.');
            } // if
        } // if

        if (!$l_result_id)
        {
            // Get object by title.
            $l_result_id = $this->m_dao_dialog->get_obj_id_by_title($l_title, $l_type, C__RECORD_STATUS__NORMAL);

            if ($l_result_id)
            {
                $this->m_log->debug('Found existing object with title ' . $l_title);
            } // if
        } // if

        return $l_result_id;
    } // function

    /**
     * Updates global category info of an object (Hostname, category, purpose, cmdb-status, description).
     *
     * @param   integer $p_obj_id
     * @param   string  $p_hostname
     * @param   string  $p_category
     * @param   string  $p_purpose
     * @param   string  $p_cmdb_status
     * @param   string  $p_description
     * @param   string  $p_title
     * @param   string  $l_tags
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    private function _update_object($p_obj_id, $p_hostname, $p_category, $p_purpose, $p_cmdb_status, $p_description, $p_title, $l_tags)
    {
        $l_changes = [];
        $l_update  = false;

        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			INNER JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_purpose ON isys_purpose__id = isys_catg_global_list__isys_purpose__id
			LEFT JOIN isys_catg_global_category ON isys_catg_global_category__id = isys_catg_global_list__isys_catg_global_category__id
			WHERE isys_obj__id = ' . $this->m_dao_dialog->convert_sql_id($p_obj_id);

        $l_data = $this->m_dao_dialog->retrieve($l_sql)
            ->get_row();

        // Sets current object type
        $this->set_current_object_type($l_data['isys_obj__isys_obj_type__id']);

        $l_update_sql = 'UPDATE isys_obj INNER JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id SET ';

        if (self::$m_object_hostname !== null && $p_hostname != $l_data['isys_obj__hostname'])
        {
            $l_update                                               = true;
            $l_changes['isys_cmdb_dao_category_g_global::hostname'] = [
                'from' => $l_data['isys_obj__hostname'],
                'to'   => $p_hostname
            ];

            $l_update_sql .= 'isys_obj__hostname = ' . $this->m_dao_dialog->convert_sql_text($p_hostname) . ', ';
        } // if

        if (self::$m_object_category !== null && $p_category != $l_data['isys_catg_global_category__title'])
        {
            $l_update                                               = true;
            $l_changes['isys_cmdb_dao_category_g_global::category'] = [
                'from' => $l_data['isys_catg_global_category__title'],
                'to'   => $p_category
            ];
            $l_update_sql .= 'isys_catg_global_list__isys_catg_global_category__id = ' . $this->m_dao_dialog->convert_sql_id(
                    $this->csv_helper__dialog_plus(
                        $p_category,
                        [
                            self::CL__CAT__PROPERTY__PARAM      => 'isys_catg_global_category',
                            self::CL__CAT__PROPERTY__REFERENCES => [
                                'isys_catg_global_category',
                                'isys_catg_global_category__id'
                            ]
                        ],
                        true
                    )
                ) . ', ';
        } // if

        if (self::$m_object_purpose !== null && $p_purpose != $l_data['isys_purpose__title'])
        {
            $l_update                                              = true;
            $l_changes['isys_cmdb_dao_category_g_global::purpose'] = [
                'from' => _L($l_data['isys_purpose__title']),
                'to'   => $p_purpose
            ];
            $l_update_sql .= 'isys_catg_global_list__isys_purpose__id = ' . $this->m_dao_dialog->convert_sql_id(
                    $this->csv_helper__dialog_plus(
                        $p_purpose,
                        [
                            self::CL__CAT__PROPERTY__PARAM      => 'isys_purpose',
                            self::CL__CAT__PROPERTY__REFERENCES => [
                                'isys_purpose',
                                'isys_purpose__id'
                            ]
                        ],
                        true
                    )
                ) . ', ';
        } // if

        if (self::$m_object_cmdbstatus !== null && $p_cmdb_status != $l_data['isys_cmdb_status__title'])
        {
            $l_update                                                  = true;
            $l_changes['isys_cmdb_dao_category_g_global::cmdb_status'] = [
                'from' => _L($l_data['isys_cmdb_status__title']),
                'to'   => $p_cmdb_status
            ];
            $l_update_sql .= 'isys_obj__isys_cmdb_status__id = ' . $this->m_dao_dialog->convert_sql_id(
                    $this->csv_helper__dialog_plus(
                        $p_cmdb_status,
                        [
                            self::CL__CAT__PROPERTY__PARAM      => 'isys_cmdb_status',
                            self::CL__CAT__PROPERTY__REFERENCES => [
                                'isys_cmdb_status',
                                'isys_cmdb_status__id'
                            ]
                        ],
                        true
                    )
                ) . ', ';
        } // if

        if (self::$m_object_description !== null && $p_description != $l_data['isys_obj__description'])
        {
            $l_update                                                  = true;
            $l_changes['isys_cmdb_dao_category_g_global::description'] = [
                'from' => $l_data['isys_obj__description'],
                'to'   => $p_description
            ];
            $l_update_sql .= 'isys_obj__description = ' . $this->m_dao_dialog->convert_sql_text($p_description) . ', ';
        } // if

        if (self::$m_object_title !== null && $p_title != $l_data['isys_obj__title'])
        {
            $l_update                                            = true;
            $l_changes['isys_cmdb_dao_category_g_global::title'] = [
                'from' => $l_data['isys_obj__title'],
                'to'   => $p_title
            ];
            $l_update_sql .= 'isys_obj__title = ' . $this->m_dao_dialog->convert_sql_text($p_title) . ', ';
        } // if

        if (! empty($l_tags))
        {
            $l_tags = $this->csv_helper__dialog_multiselect($l_tags, ['property_table' => 'isys_tag']);

            isys_cmdb_dao_category_g_global::instance(isys_application::instance()->database)->assign_tag($p_obj_id, $l_tags[self::CL__HELPER__VALUE]);
        } // if

        if ($l_update === true)
        {
            $l_update_sql = rtrim($l_update_sql, ', ') . ' WHERE isys_obj__id = ' . $this->m_dao_dialog->convert_sql_id($p_obj_id);
            // Update only the data which are assigned
            $this->m_dao_dialog->update($l_update_sql . ';');

            $this->m_logbook_entries[] = [
                'object_id'      => $p_obj_id,
                'object_type_id' => $l_data['isys_obj__isys_obj_type__id'],
                'category'       => _L('LC__CMDB__CATG__GLOBAL'),
                'changes'        => serialize($l_changes),
                'event'          => 'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                'count_changes'  => count($l_changes)
            ];
        } // if
    } // function

    /**
     * Create object
     *
     * @param   integer $p_type
     * @param   string  $p_title
     * @param   string  $p_sys_id
     * @param   integer $p_record_status
     * @param   string  $p_hostname
     * @param   string  $p_category
     * @param   string  $p_purpose
     * @param   string  $p_cmdb_status
     * @param   string  $p_description
     * @param   string  $p_tags
     *
     * @return  mixed
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     */
    private function _create_object($p_type, $p_title, $p_sys_id, $p_record_status = C__RECORD_STATUS__NORMAL, $p_hostname, $p_category, $p_purpose, $p_cmdb_status, $p_description, $p_tags)
    {
        $l_obj_id       = false;
        $l_unique_title = true;

        // Handle "cmdb.unique.object-title" configuration.
        if (isys_tenantsettings::get('cmdb.unique.object-title') && $this->m_dao_dialog->get_obj_id_by_title($p_title, null, C__RECORD_STATUS__NORMAL))
        {
            $l_unique_title = false;
        } // if

        if ($l_unique_title)
        {
            $p_category    = $this->csv_helper__dialog_plus($p_category, [self::CL__CAT__PROPERTY__PARAM => 'isys_catg_global_category'], true);
            $p_purpose     = $this->csv_helper__dialog_plus($p_purpose, [self::CL__CAT__PROPERTY__PARAM => 'isys_purpose'], true);
            $p_cmdb_status = $this->csv_helper__dialog_plus($p_cmdb_status, [self::CL__CAT__PROPERTY__PARAM => 'isys_cmdb_status'], true);
            $l_tags        = $this->csv_helper__dialog_multiselect($p_tags, ['property_table' => 'isys_tag']);

            // Let us validate data for global category.
            $l_data = [
                'title'       => $p_title,
                'type'        => $p_type,
                'sysid'       => $p_sys_id,
                'status'      => $p_record_status,
                'purpose'     => $p_purpose,
                'category'    => $p_category,
                'tag'         => $l_tags[self::CL__HELPER__VALUE],
                'cmdb_status' => $p_cmdb_status
            ];

            // Validate.
            $l_validation_messages = $this->validate(isys_cmdb_dao_category_g_global::instance($this->m_dao_dialog->get_database_component()), $l_data, false);

            if (is_array($l_validation_messages))
            {
                $this->m_log->error("Unable to create object because of validation errors:");
                $this->m_log->error(implode("\n", $l_validation_messages));

                return false;
            } // if

            // Create object.
            $l_obj_id = $this->m_dao_dialog->insert_new_obj(
                $p_type,
                null,
                $p_title,
                $p_sys_id,
                $p_record_status,
                $p_hostname,
                null,
                null,
                null,
                null,
                null,
                null,
                $p_category,
                $p_purpose,
                $p_cmdb_status,
                $p_description
            );

            // Sets current object type.
            $this->set_current_object_type($p_type);

            isys_cmdb_dao_category_g_global::instance(isys_application::instance()->database)->assign_tag($l_obj_id, $l_tags[self::CL__HELPER__VALUE]);

            // Generate logbook entry.
            $this->m_logbook_entries[] = [
                'object_id'      => $l_obj_id,
                'object_type_id' => $p_type,
                'category'       => null,
                'changes'        => null,
                'event'          => 'C__LOGBOOK_EVENT__OBJECT_CREATED',
                'count_changes'  => 0
            ];
        }
        else
        {
            $this->m_log->error('Unable to create object: An object with the given title \'' . $p_title . '\' already exists.');
        } // if

        return $l_obj_id;
    } // function

    /**
     * Get Object ID by identification keys.
     *
     * @param   string $p_identification_condition
     *
     * @return  mixed
     */
    private function get_object_id_by_identification($p_identification_condition)
    {
        $l_res = self::$m_s_dao->retrieve(
            'SELECT main_obj.isys_obj__id FROM isys_obj AS main_obj ' . self::$m_identification_joins . ' WHERE ' . $p_identification_condition . ' LIMIT 1;'
        );

        if (count($l_res))
        {
            return $l_res->get_row_value('isys_obj__id');
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * @param  string                 $p_category_const
     * @param  isys_cmdb_dao_category $p_category_dao
     * @param  array                  $p_properties
     */
    private function handle_singlevalue_category($p_category_const, isys_cmdb_dao_category $p_category_dao, $p_properties)
    {
        $l_sync_array = $this->build_for_sync($p_category_const, $p_properties);

        if ($l_sync_array)
        {
            if (self::is_update())
            {
                $l_result = $this->get_data($p_category_dao->get_data(null, self::get_current_object()), $p_category_const);

                if (count($l_result) == 1)
                {
                    $l_row = reset($l_result);
                    $this->m_log->debug("There is already an entry for the object. We will update it !");

                    // Set the data_id index for the update.
                    $l_sync_array['data_id'] = $l_row[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]];

                    if (!self::$m_singlevalue_overwrite_empty_values)
                    {
                        foreach ($p_category_dao->get_properties() as $l_prop => $l_prop_info)
                        {
                            if (!isset($l_sync_array['properties'][$l_prop]))
                            {
                                if (isset($l_row[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]]))
                                {
                                    $l_sync_array['properties'][$l_prop] = [
                                        'id'           => $l_row[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]],
                                        C__DATA__VALUE => $l_row[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]]
                                    ];
                                }
                                else if (isset($l_row[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]))
                                {
                                    $l_sync_array['properties'][$l_prop] = [
                                        'id'           => $l_row[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]],
                                        C__DATA__VALUE => $l_row[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]
                                    ];
                                } // if
                            } // if
                        } // foreach
                    } // if
                }
                else
                {
                    if (count($l_result) > 1)
                    {
                        self::skip_category();
                        $this->m_log->error("There is more than one entry in the Singlevalue-Category. Therefore we skip it!");
                    }
                    else
                    {
                        $this->m_log->debug("There is no entry for the Object. We will create it");
                    } // if
                } // if
            } // if

            if (!self::get_category_skip())
            {
                // Sync mode handling
                if (isset($l_sync_array['data_id']) && is_numeric($l_sync_array['data_id']))
                {
                    $l_mode = isys_import_handler_cmdb::C__UPDATE;
                    $this->m_log->debug("Import Mode: UPDATE");
                }
                else
                {
                    $l_mode = isys_import_handler_cmdb::C__CREATE;
                    $this->m_log->debug("Import Mode: CREATE");
                } // if

                $this->sync($p_category_const, self::get_current_object(), $p_category_dao, $l_sync_array, $l_mode);
            } // if
        }
        else
        {
            if (!empty(self::$m_log_essential))
            {
                $this->m_log->error('An essential information is not set: ' . self::$m_log_essential . '! Skipping...');
            }
            else
            {
                $this->m_log->notice('SyncArray is empty. There is nothing to import! Skipping...');
            } // if
        } // if
    } // function

    /**
     * Syncwrapper.
     *
     * @param   string                 $p_category_const
     * @param   integer                $p_object_id
     * @param   isys_cmdb_dao_category $p_category_dao
     * @param   array                  $p_sync_array
     * @param   integer                $p_mode
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_validation
     */
    private function sync($p_category_const, $p_object_id, isys_cmdb_dao_category $p_category_dao, array $p_sync_array, $p_mode)
    {
        // Validate data first.
        $l_validation_messages = $this->validate($p_category_dao, $p_sync_array);
        if (!(is_bool($l_validation_messages) && $l_validation_messages == true))
        {
            $this->m_log->error("Data is invalid: ");

            if (is_array($l_validation_messages) && count($l_validation_messages))
            {
                $this->m_log->error(implode('\n', $l_validation_messages));
            } // if

            // Data is invalid. We have to skip this entry.
            return false;
        } // if

        // Sync data.
        $l_success = $p_category_dao->sync($p_sync_array, $p_object_id, $p_mode);

        if ($l_success)
        {
            $this->m_log->info('Data successfully imported.');
        }
        else
        {
            $this->m_log->error('Unable to import data: category "' . $p_category_dao->get_category() . '", object #' . $p_object_id . '.');
        } // if

        if ($l_success)
        {
            // Get changes and create logbook entry.
            $l_category_changes = self::$m_logb_dao->prepare_changes($p_category_dao, [], $p_sync_array);
            $l_count_changes = count($l_category_changes);
            if ($l_count_changes > 0)
            {
                $this->m_logbook_entries[] = [
                    'object_id'      => $p_object_id,
                    'object_type_id' => self::$m_current_object_type,
                    'category'       => $this->m_category_map[$p_category_const][self::CL__CAT__TITLE],
                    'changes'        => serialize($l_category_changes),
                    'event'          => 'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                    'count_changes'  => $l_count_changes
                ];
            } // if
        } // if

        // Emit category signal (afterCategoryEntrySave).
        isys_component_signalcollection::get_instance()
            ->emit(
                "mod.cmdb.afterCategoryEntrySave",
                $p_category_dao,
                isset($p_sync_array['data_id']) ? $p_sync_array['data_id'] : null,
                $l_success,
                $p_object_id,
                $p_sync_array,
                isset($l_category_changes) ? $l_category_changes : []
            );

        return $l_success;
    } // function

    /**
     * Returns DAO for category.
     *
     * @param   string $p_category_const
     *
     * @return  isys_cmdb_dao_category
     */
    private function get_category_dao($p_category_const)
    {
        if (isset($this->m_category_map[$p_category_const]))
        {
            $l_class        = $this->m_category_map[$p_category_const][self::CL__CAT__CLASS];
            $l_category_dao = new $l_class($this->m_dao_dialog->get_database_component());

            if ($this->m_category_map[$p_category_const][self::CL__CAT__CAT_TYPE] == C__CMDB__CATEGORY__TYPE_CUSTOM)
            {
                /** @var isys_cmdb_dao_category_g_custom_fields $l_category_dao */
                $l_category_dao->set_catg_custom_id($this->m_category_map[$p_category_const][self::CL__CAT__ID]);
            } // if

            return $l_category_dao;
        } // if

        return false;
    } // function

    /**
     * Retrieve data by DAO-Result with handling for custom categories.
     *
     * @param   isys_component_dao_result $p_dao_result
     * @param   string                    $p_category_const
     *
     * @return  array
     */
    private function get_data(isys_component_dao_result $p_dao_result, $p_category_const)
    {
        $l_catentries = [];

        if ($p_dao_result->num_rows())
        {
            // Retrieve data
            if ($this->m_category_map[$p_category_const][self::CL__CAT__CAT_TYPE] == C__CMDB__CATEGORY__TYPE_CUSTOM)
            {
                // Extra-Handling for custom categories: We have to build entry rows because in custom categories we have one resultset per field. Let us merge it.
                while ($l_row = $p_dao_result->get_row())
                {
                    // Build field key.
                    $l_source_table                                                                                          = $this->m_category_map[$p_category_const][self::CL__CAT__TABLE];
                    $l_field_key                                                                                             = $l_row[$l_source_table . '__field_type'] . '_' . $l_row[$l_source_table . '__field_key'];
                    $l_catentries[$l_row[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]]][$l_field_key] = $l_row[$l_source_table . '__field_content'];

                    // Special-Handling for description field: Alias is not conform with other fields. It does not contain the type as alias.
                    if (strpos($l_row[$l_source_table . '__field_key'], 'C__CMDB__CAT__COMMENTARY_') !== false)
                    {
                        $l_catentries[$l_row[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]]]
                        [$l_row[$l_source_table . '__field_key']] = $l_row[$l_source_table . '__field_content'];
                    } // if

                    $l_catentries[$l_row[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]]]
                    [$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]] = $l_row[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]];
                } // while
            }
            else
            {
                while ($l_row = $p_dao_result->get_row())
                {
                    $l_catentries[$l_row[$this->m_category_map[$p_category_const][self::CL__CAT__DATA_FIELD]]] = $l_row;
                } // while
            } // if
        } // if

        return $l_catentries;
    } // function

    /**
     * Constructor.
     *
     * @param  string  $p_file
     * @param  string  $p_delimiter
     * @param  string  $p_multivalue
     * @param  string  $p_object_title
     * @param  integer $p_global_object_type
     * @param  integer $p_object_type
     * @param  integer $p_object_purpose
     * @param  integer $p_object_category
     * @param  integer $p_object_sysid
     * @param  integer $p_object_cmdbstatus
     * @param  integer $p_object_description
     * @param  boolean $p_header
     * @param  array   $p_prop_search
     * @param  string  $p_multivalue_update_mode
     * @param  integer $p_log_level
     * @param  integer $p_overwrite_empty_values
     * @param  integer $p_object_tags
     */
    public function __construct($p_file, $p_delimiter, $p_multivalue, $p_object_title, $p_global_object_type, $p_object_type, $p_object_purpose, $p_object_category, $p_object_sysid, $p_object_cmdbstatus, $p_object_description, $p_header = false, $p_prop_search = null, $p_multivalue_update_mode, $p_log_level = null, $p_overwrite_empty_values = 1, $p_object_tags = null)
    {
        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request. (via mod.import_csv.afterImport)
         */
        \idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        self::set_step(self::CL__IMPORT_STEP__CONSTRUCT);

        $this->m_dao_dialog = isys_cmdb_dao_dialog_admin::instance(isys_application::instance()->database);
        self::$m_s_dao      = isys_cmdb_dao::instance(isys_application::instance()->database);
        self::$m_logb_dao   = new isys_module_logbook(isys_application::instance()->database);

        $this->m_log_dir  = isys_settings::get('system.log.path', BASE_DIR . 'log') . DS;
        $this->m_log_file = 'import_cmdb_csv_' . date('Y-m-d_H-i-s') . '.log';

        // Use the "TestHandler" to retrieve the log records later on.
        $l_test_handler = (new TestHandler())->setFormatter(new LineFormatter("%message% %context%\n", null, false, true));

        // Use the "StreamHandler" to write the log records to a log file.
        $l_stream_handler = (new StreamHandler($this->m_log_dir . $this->m_log_file, $p_log_level))->setFormatter(
            new LineFormatter("[%datetime%] %level_name%: %message% %context%\n")
        );

        $this->m_log = new Logger(
            'i-doit CSV', [
                $l_stream_handler,
                $l_test_handler
            ]
        );

        // Assign the "TestHandler" to a member variable.
        $this->m_log_handler = $l_test_handler;

        // Set information.
        self::$m_file                               = $p_file;
        self::$m_delimiter                          = $p_delimiter;
        self::$m_object_title                       = $p_object_title;
        self::$m_global_object_type                 = ($p_global_object_type > 0) ? $p_global_object_type : null;
        self::$m_object_type                        = $p_object_type;
        self::$m_object_sysid                       = $p_object_sysid;
        self::$m_object_cmdbstatus                  = $p_object_cmdbstatus;
        self::$m_object_description                 = $p_object_description;
        self::$m_multivalue_update_mode             = $p_multivalue_update_mode;
        self::$m_object_category                    = $p_object_category;
        self::$m_object_tags                        = $p_object_tags;
        self::$m_object_purpose                     = $p_object_purpose;
        self::$m_header                             = $p_header;
        self::$m_singlevalue_overwrite_empty_values = !!$p_overwrite_empty_values;

        // Additional property search for example category location property location object.
        self::$m_prop_search = $p_prop_search;

        // Try to update.
        self::$m_object_mode = self::CL__OBJECT_MODE__UPDATE;

        // Set multivalue mode.
        $l_multivalue_mode_str = 'self::CL__MULTIVALUE_TYPE__' . strtoupper($p_multivalue);

        if (constant($l_multivalue_mode_str))
        {
            self::$m_step_construct  = true;
            self::$m_multivalue_mode = constant($l_multivalue_mode_str);
            $this->m_log->info("Multivalue mode: " . self::$m_multivalue_mode);
        }
        else
        {
            $this->set_import_status(false);
            $this->m_log->error("Undefined Multivalue mode: " . $p_multivalue);
            $this->m_log->info('Aborting import !');
        } // if
    } // function
} // class