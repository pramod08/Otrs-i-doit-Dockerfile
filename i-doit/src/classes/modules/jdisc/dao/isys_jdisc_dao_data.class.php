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
 * JDisc data module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_jdisc_dao_data extends isys_module_dao
{
    /**
     * Cache for location objects
     *
     * @var array
     */
    public static $m_cached_locations = [];
    /**
     * Contains all tables which are defined in the jdisc database
     *
     * @var array
     */
    protected static $m_active_tables = null;
    /**
     * Cache object
     *
     * @var null
     */
    protected static $m_caching = null;
    /**
     * Flag if in clear mode
     *
     * @var bool
     */
    protected static $m_clear_mode = false;
    /**
     * Object ID of the current device
     *
     * @var null
     */
    protected static $m_current_object_id = null;
    /**
     * Object Type ID of the current device
     *
     * @var null
     */
    protected static $m_current_object_type_id = null;
    /**
     * Collects all objects which has been created in the jdisc dao classes.
     * The key is the device id from the jdisc database the value is the object id from i-doit.
     *
     * @var array
     */
    protected static $m_jdisc_to_idoit_objects = [];
    /**
     * JDisc type id for Blade chassis
     *
     * @var null
     */
    protected static $m_jdisc_type_ids = [];
    /**
     * Collects all new created objects
     *
     * @var array
     */
    protected static $m_object_ids = [];
    /**
     * Cache all object types
     *
     * @var null
     */
    protected static $m_object_types_cache = null;

    /**
     * Map cmdb-status
     *
     * @var null
     */
    protected $m_cmdb_status_map = null;

    /**
     * Default cmdb status if set
     *
     * @var null
     */
    protected $m_default_cmdb_status = null;
    /**
     * Holds an instance of the import log.
     *
     * @var  isys_log
     */
    protected $m_log = null;
    /**
     * Holds all logbook entries from the jdisc import
     *
     * @var array
     */
    protected static $m_logbook_entries = [];
    /**
     * Variable for using the PDO in every child-class without creating a new instance.
     *
     * @var  isys_component_database_pdo
     */
    protected $m_pdo;
    /**
     * Temporary table
     *
     * @var string
     */
    protected $m_temp_table = 'temp_table_jdisc';

    /**
     * Activate clear mode for categories
     */
    public static function activate_clear_mode()
    {
        self::$m_clear_mode = true;
    } // function

    /**
     * Deactivate clear mode for categories
     */
    public static function deactivate_clear_mode()
    {
        self::$m_clear_mode = false;
    } // function

    /**
     * Get clear mode for categories
     *
     * @return bool
     */
    public static function clear_data()
    {
        return self::$m_clear_mode;
    } // function

    public function __destruct()
    {
        unset($this->m_log);
        unset($this->m_pdo);
    } // function

    /**
     * Fetches data from JDisc database.
     *
     * @param   string $p_query
     *
     * @return  PDOStatement
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function fetch($p_query)
    {
        return $this->m_pdo->query($p_query);
    } //function

    /**
     * Fetches all JDisc device types from database. This data depends on the JDisc release.
     *
     * @return  array
     */
    public function get_jdisc_device_types()
    {
        return $this->fetch_array('SELECT * FROM devicetypelookup ORDER BY singular;');
    } //function

    /**
     * Fetches all JDisc operating systems from database. This data depends on the inventory data.
     *
     * @return  array
     */
    public function get_jdisc_operating_systems()
    {
        return $this->fetch_array('SELECT * FROM operatingsystem ORDER BY osversion;');
    } // function

    /**
     * Sets the jdisc type id for the specified jdisc type
     *
     * @param    $p_idoit_type    mixed    identifier for the array
     * @param    $p_jdisc_type    string    JDisc type as string
     */
    public function get_jdisc_type_id($p_idoit_type, $p_jdisc_type)
    {
        if (!isset(self::$m_jdisc_type_ids[$p_idoit_type]))
        {
            self::$m_jdisc_type_ids[$p_idoit_type] = $this->get_jdisc_type_id_by_name($p_jdisc_type);
        } // if
        return self::$m_jdisc_type_ids[$p_idoit_type];
    } // function

    /**
     * Unused "get_data()" method.
     */
    public function get_data()
    {
        // Unused.
    } // function

    /**
     *
     * @param   array $p_data
     *
     * @return  array
     */
    public function prepare_model($p_data)
    {
        $p_data['manufacturer'] = trim($p_data['manufacturer']);
        $p_data['model']        = trim($p_data['model']);

        $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', $p_data['manufacturer']);
        $l_title        = isys_import_handler::check_dialog('isys_model_title', $p_data['model'], null, $l_manufacturer);

        return [
            C__DATA__TITLE      => _L('LC__CMDB__CATG__MODEL'),
            'const'             => 'C__CATG__MODEL',
            'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
            'category_entities' => [
                [
                    'data_id'    => null,
                    'properties' => [
                        'title'        => [
                            'tag'        => 'title',
                            'value'      => $p_data['model'],
                            'id'         => $l_title,
                            'title_lang' => $p_data['model'],
                            'title'      => 'LC__CMDB__CATG__MODEL',
                        ],
                        'manufacturer' => [
                            'tag'        => 'manufacturer',
                            'value'      => $p_data['manufacturer'],
                            'id'         => $l_manufacturer,
                            'title_lang' => $p_data['manufacturer'],
                            'title'      => 'LC__CATG__STORAGE_MANUFACTURER',
                        ],
                        'serial'       => [
                            'tag'        => 'serial',
                            'value'      => $p_data['serialnumber'],
                            'title_lang' => $p_data['serialnumber'],
                            'title'      => 'LC__CMDB__CATG__SERIAL',
                        ],
                        'productid'    => [
                            'tag'        => 'productid',
                            'value'      => $p_data['partnumber'],
                            'title_lang' => $p_data['partnumber'],
                            'title'      => 'LC__CMDB__CATG__MODEL_PRODUCTID',
                        ],
                        'firmware'     => [
                            'tag'        => 'firmware',
                            'value'      => $p_data['firmware'],
                            'title_lang' => $p_data['firmware'],
                            'title'      => 'LC__CMDB__CATG__FIRMWARE',
                        ]
                    ]
                ]
            ]
        ];
    } // function

    /**
     * Prepare Method for category location
     *
     * @param $p_location_id
     *
     * @return array
     * @throws isys_exception_general
     */
    public function prepare_location($p_location_id)
    {

        if (!isset(self::$m_cached_locations[$p_location_id]))
        {
            $l_dao                                       = isys_cmdb_dao_jdisc::instance($this->m_db);
            $l_data                                      = $l_dao->get_object_by_id($p_location_id)
                ->get_row();
            self::$m_cached_locations[$p_location_id]    = new SplFixedArray(5);
            self::$m_cached_locations[$p_location_id][0] = $l_data['isys_obj__id'];
            self::$m_cached_locations[$p_location_id][1] = $l_data['isys_obj__title'];
            self::$m_cached_locations[$p_location_id][2] = $l_data['isys_obj_type__const'];
            self::$m_cached_locations[$p_location_id][3] = $l_data['isys_obj_type__title'];
            self::$m_cached_locations[$p_location_id][4] = $l_data['isys_obj__sysid'];
        } // if

        return [
            C__DATA__TITLE      => _L('LC__CMDB__CATG__LOCATION'),
            'const'             => 'C__CATG__LOCATION',
            'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
            'category_entities' => [
                [
                    'data_id'    => null,
                    'properties' => [
                        'parent' => [
                            'tag'      => 'parent',
                            'value'    => self::$m_cached_locations[$p_location_id][0],
                            'id'       => self::$m_cached_locations[$p_location_id][0],
                            'type'     => self::$m_cached_locations[$p_location_id][2],
                            'sysid'    => self::$m_cached_locations[$p_location_id][4],
                            'lc_title' => self::$m_cached_locations[$p_location_id][3],
                            'title'    => self::$m_cached_locations[$p_location_id][1],
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns all collected object ids so far
     *
     * @return array
     */
    public function get_object_ids()
    {
        return self::$m_object_ids;
    } // function

    /**
     * Adds a newly created object id to the array
     *
     * @param $p_obj_id
     */
    public function set_object_id($p_obj_id)
    {
        self::$m_object_ids[$p_obj_id] = $p_obj_id;
    } // function

    /**
     * Adds a newly created object id to an array with the device id from the jdisc system
     *
     * @param $p_id
     * @param $p_obj_id
     */
    public function set_jdisc_to_idoit_objects($p_id, $p_obj_id)
    {
        self::$m_jdisc_to_idoit_objects[$p_id] = $p_obj_id;
    } // function

    /**
     * Returns all collected object ids with the device id as key so far
     *
     * @return array
     */
    public function get_jdisc_to_idoit_objects()
    {
        return self::$m_jdisc_to_idoit_objects;
    } // function

    /**
     * Setter for $m_current_object_id
     *
     * @param $p_obj_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_current_object_id($p_obj_id)
    {
        self::$m_current_object_id = $p_obj_id;
    } // function

    /**
     * Getter for $m_current_object_id
     *
     * @return null
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_current_object_id()
    {
        return self::$m_current_object_id;
    } // function

    /**
     * Setter for $m_current_object_type_id
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_current_object_type_id($p_value)
    {
        self::$m_current_object_type_id = $p_value;
    } // function

    /**
     * Getter for $m_current_object_type_id
     *
     * @return null
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_current_object_type_id()
    {
        return self::$m_current_object_type_id;
    } // function

    /**
     * Get object type const
     *
     * @param $p_type_id
     *
     * @return mixed
     */
    public function get_object_type_const($p_type_id)
    {
        if (self::$m_object_types_cache === null)
        {
            self::$m_object_types_cache = new isys_array();

            $l_sql = 'SELECT isys_obj_type__id, isys_obj_type__const FROM isys_obj_type;';

            $l_query = $this->m_db->query($l_sql);
            while ($l_row = $this->m_db->fetch_row($l_query))
            {
                self::$m_object_types_cache[$l_row[0]] = $l_row[1];
            } // while
        } // if

        return self::$m_object_types_cache[$p_type_id];
    } // function

    /**
     * Get hostname and ip address by device if exist
     *
     * @param $p_device_id
     *
     * @return string|bool
     */
    public function get_hostname_primary_address_by_device_id($p_device_id)
    {
        $l_sql = 'SELECT fqdn, address FROM ip4transport WHERE isdiscoverytransport = TRUE AND deviceid = ' . $this->convert_sql_id($p_device_id) . ' LIMIT 1;';
        $l_res = $this->fetch($l_sql);

        if ($this->m_pdo->num_rows($l_res) > 0)
        {
            $l_row       = $this->m_pdo->fetch_row($l_res);
            $l_fqdn      = $l_row[0];
            $l_fqdn_arr  = explode('.', $l_fqdn);
            $l_return    = new SplFixedArray(2);
            $l_return[1] = $l_row[1];
            if (count($l_fqdn_arr) >= 3)
            {
                $l_return[0] = $l_fqdn_arr[0];
            }
            else
            {
                $l_return[0] = $l_fqdn;
            } // if
            return $l_return;
        } // if
        return null;
    } // function

    /**
     * Gets all active mac-addresses from the selected device
     *
     * @param $p_device_id
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_mac_addresses($p_device_id, $p_unique = false)
    {
        $l_sql = 'SELECT DISTINCT(m.ifphysaddress) AS macaddr FROM mac AS m WHERE m.ifoperstatus != 2 AND m.deviceid = ' . $this->convert_sql_id(
                $p_device_id
            ) . ' AND  m.ifphysaddress IS NOT NULL  ';

        // This addition is for retrieving mac addresses which are unique
        if ($p_unique)
        {
            $l_sql .= ' AND (SELECT COUNT(*) AS cnt FROM mac WHERE ifphysaddress = m.ifphysaddress GROUP BY ifphysaddress) = 1 ';
        } // if

        $l_res          = $this->fetch($l_sql);
        $l_macaddresses = [];
        if ($this->m_pdo->num_rows($l_res) > 0)
        {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
            {
                $l_macaddresses[] = $l_row['macaddr'];
            } // while
            $this->m_pdo->free_result($l_res);
        } // if
        return $l_macaddresses;
    } // function

    /**
     * Get object id by jdisc data
     * (Hostadress,
     *
     * @param $p_row
     *
     * @return bool|int
     * @throws isys_exception_general
     */
    public function get_object_id_by_jdisc_data($p_row)
    {
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        // Retrieve the hostname and ip address of the device
        $l_ip_data   = $this->get_hostname_primary_address_by_device_id($p_row['deviceid']);
        $l_object_id = false;

        if ($p_row['serialnumber'] != '' || $l_ip_data !== null)
        {
            // Hostname
            $l_hostname = $l_ip_data[0];
            // IP address as long value
            $l_ip_long = $l_ip_data[1];

            $l_check_fields = (($l_hostname) ? 'Hostname: "' . $l_hostname . '" ' : '') . (($p_row['serialnumber']) ? 'Serialnumber: "' . $p_row['serialnumber'] . '" ' : '') . (($l_ip_long) ? 'IP long value: "' . $l_ip_long . '" ' : '');

            // First check with hostname & serialnumber & ip long value
            $this->m_log->debug('Checking device: "' . $p_row['name'] . '"');
            $l_object_id = $l_dao->get_object_by_hostname_serial_mac($l_hostname, $p_row['serialnumber'], null, null, null, $l_ip_long);
            if (!$l_object_id)
            {
                $this->m_log->debug('First check failed with ' . $l_check_fields . '.');
                // Retrieve all mac addresses from the device
                $l_mac_addresses = $this->get_mac_addresses($p_row['deviceid'], true);
                // Check with hostname and serial and mac-addresses and ip long value
                $l_object_id = $l_dao->get_object_by_hostname_serial_mac(
                    $l_hostname,
                    $p_row['serialnumber'],
                    $l_mac_addresses,
                    null,
                    null,
                    $l_ip_long
                );
                $l_name      = $p_row['name'];
                // Use hostname or serialnumber as title if title is empty
                if ($l_name == '' && $l_hostname != '')
                {
                    $l_name = $l_hostname;
                }
                elseif ($l_name == '' && $p_row['serialnumber'] != '')
                {
                    $l_name = $p_row['serialnumber'];
                } // if

                $l_check_fields .= 'Mac-adresses ';

                if (!$l_object_id && $l_name != '')
                {
                    $this->m_log->debug('Second check failed with ' . $l_check_fields . '.');
                    // Last check with hostname and serial and mac-addresses and ip long value and device title
                    $l_object_id = $l_dao->get_object_by_hostname_serial_mac(
                        $l_hostname,
                        $p_row['serialnumber'],
                        $l_mac_addresses,
                        $l_name,
                        null,
                        $l_ip_long
                    );

                    $l_check_fields .= 'Object-Title: ' . $l_name;
                    // If object has been found than set name
                    if ($l_object_id)
                    {
                        $p_row['name'] = $l_name;
                        $this->m_log->debug('Last check successful with ' . $l_check_fields . '.');
                    }
                    else
                    {
                        $this->m_log->debug('Last check failed with ' . $l_check_fields . '.');
                    } // if
                }
                elseif ($l_object_id)
                {
                    $this->m_log->debug('Second check successful with ' . $l_check_fields . '.');
                } // if
            }
            else
            {
                $this->m_log->debug('First check successful with ' . $l_check_fields . '.');
            } // if
        }
        else
        {
            // If object has not been found check with title and object type
            if ($p_row['name'] != '')
            {
                $l_object_id = $l_dao->get_obj_id_by_title($p_row['name'], $p_row['idoit_obj_type']);
            } // if
        } // if
        return $l_object_id;
    } // function

    /**
     * Checks if specified table exists
     *
     * @param $p_table
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_table($p_table)
    {
        if (self::$m_active_tables === null)
        {
            $this->map_tables();
        } // if
        return (bool) self::$m_active_tables[$p_table];
    } // function

    /**
     * Map cmdb status
     */
    public function map_cmdb_status($p_default_cmdb_status = null)
    {
        global $g_comp_database;

        if ($p_default_cmdb_status !== null)
        {
            $this->m_default_cmdb_status = $p_default_cmdb_status;
        } // if

        /**
         * @var $l_dao isys_cmdb_dao_status
         */
        $l_dao = isys_cmdb_dao_status::instance($g_comp_database);
        $l_res = $l_dao->get_cmdb_status();
        while ($l_row = $l_res->get_row())
        {
            $this->m_cmdb_status_map[$l_row['isys_cmdb_status__id']] = [
                'tag'        => 'cmdb_status',
                'value'      => _L($l_row['isys_cmdb_status__title']),
                'id'         => $l_row['isys_cmdb_status__id'],
                'const'      => $l_row['isys_cmdb_status__const'],
                'title_lang' => $l_row['isys_cmdb_status__title'],
                'title'      => 'LC__UNIVERSAL__CMDB_STATUS'
            ];
        } // while
    } // function

    /**
     * Get default cmdb-status
     *
     * @return null
     */
    public function get_default_cmdb_status()
    {
        return $this->m_default_cmdb_status;
    } // if

    /**
     * Setter method which fills m_logbook_entries
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function set_logbook_entries($p_value)
    {
        self::$m_logbook_entries[] = $p_value;
    } // function

    /**
     * Getter method which retrieves the variable m_logbook_entries
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_logbook_entries()
    {
        return self::$m_logbook_entries;
    } // function

    /**
     * Resets logbook entries for the current device
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function reset_logbook_entries()
    {
        self::$m_logbook_entries = [];
    } // function

    /**
     * Create temp table
     *
     * @param $p_data
     *
     * @throws isys_exception_dao
     */
    public function create_cache_table()
    {
        $l_query = 'CREATE TEMPORARY TABLE ' . $this->m_temp_table . ' (id INT(10) UNSIGNED, data LONGTEXT, type INT(10) UNSIGNED) ENGINE=MyISAM;';
        $this->update($l_query);
    } // function

    /**
     * Add entries into the temporary table
     *
     * Types:
     * 1 port
     * 2 logical_port
     * 3 update_port
     * 4 network_interfaces
     * 5 network_interfaces_connection
     * 6 net_listener_connections
     *
     * @param $p_id
     * @param $p_data
     * @param $p_type
     *
     * @return $this
     * @throws isys_exception_dao
     */
    public function cache_data($p_id, $p_data, $p_type)
    {
        $l_insert = 'INSERT INTO ' . $this->m_temp_table . ' (id, data, type) VALUES(' . $this->convert_sql_id($p_id) . ', ' . $this->convert_sql_text(
                isys_format_json::encode($p_data)
            ) . ', ' . $this->convert_sql_int($p_type) . ')';
        $this->update($l_insert);

        return $this;
    } // function

    /**
     * Load relevant data from temporary table
     *
     * @param $p_obj_id
     *
     * @throws Exception
     */
    public function load_cache($p_obj_id, $p_condition = '')
    {
        $l_sql = 'SELECT * FROM ' . $this->m_temp_table . ' WHERE id = ' . $this->convert_sql_id($p_obj_id) . ' ' . $p_condition;

        return $this->m_db->query($l_sql);
    } // function

    /**
     * Drops temporary table
     *
     * @throws isys_exception_dao
     */
    public function drop_cache_table()
    {
        $l_query = 'DROP TEMPORARY TABLE ' . $this->m_temp_table . ';';
        $this->update($l_query);
    } // function

    /**
     * Fetches data from JDisc database.
     *
     * @param   string $p_query
     *
     * @return  array
     * @author  Benjamin Heisig <bheisig@i-doit.org>
     */
    protected function fetch_array($p_query)
    {
        $l_result_set = $this->m_pdo->query($p_query);

        $l_result = [];

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_result_set))
        {
            $l_result[] = $l_row;
        } // while

        return $l_result;
    } // function

    /**
     * Fetches the jdisc device type id by name
     *
     * @param $p_name
     *
     * @return bool|mixed
     */
    private function get_jdisc_type_id_by_name($p_name)
    {
        $l_condition_value = $this->convert_sql_text($p_name);
        $l_row             = $this->fetch_array('SELECT id FROM devicetypelookup WHERE name ILIKE ' . $l_condition_value);

        return (is_array($l_row) && (count($l_row) == 1) ? current(current($l_row)) : false);
    } // function

    /**
     * Helper function which maps all tables which are defined in the jdisc database
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @return void
     */
    private function map_tables()
    {
        self::$m_active_tables = new isys_array();
        $l_sql                 = 'SELECT tablename FROM pg_tables WHERE schemaname = ' . $this->convert_sql_text('public');

        $l_res = $this->fetch($l_sql);
        if ($this->m_pdo->num_rows($l_res) > 0)
        {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res))
            {
                self::$m_active_tables[$l_row['tablename']] = true;
            } // while
        } // if
    } // function

    /**
     * Constructor
     *
     * @param   isys_component_database     $p_db Database component
     * @param   isys_component_database_pdo $p_pdo
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_database $p_db, isys_component_database_pdo $p_pdo)
    {
        parent::__construct($p_db);

        if (static::$m_caching === null)
        {
//			static::$m_caching = isys_cache::keyvalue();
        } // if
        $this->m_log = isys_factory_log::get_instance('import_jdisc');
        $this->m_pdo = $p_pdo;
    } // function

} // class