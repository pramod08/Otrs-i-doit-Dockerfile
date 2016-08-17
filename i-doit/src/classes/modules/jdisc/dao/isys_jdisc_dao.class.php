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
 * JDisc module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_jdisc_dao extends isys_module_dao
{

    const C__CONFIGURATION = 'configuration';

    const C__PROFILES = 'profiles';

    const C__COMMON_SETTINGS = 'common_settings';

    const C__OBJECT_TYPE_ASSIGNMENTS = 'object_type_assignments';

    const C__ADDITIONAL_OPTIONS = 'additional_options';

    /**
     * Data cache
     *
     * @var array
     */
    protected $m_cache;
    /**
     * Defines the current installed JDisc version
     *
     */
    protected $m_current_version;
    /**
     * Logger
     *
     * @var isys_log
     */
    protected $m_log;
    /**
     * PDO driver
     *
     * @var isys_component_database_pdo
     */
    protected $m_pdo;
    /**
     * Current jdisc server
     *
     * @var
     */
    protected $m_server_id;
    /**
     * List of categories supported by JDisc
     *
     * @var array Array of category identifiers (integers)
     */
    protected $m_supported_categories = [
        'C__CATG__GRAPHIC'                     => true,
        'C__CATG__CPU'                         => true,
        'C__CATG__MEMORY'                      => true,
        'C__CATG__STORAGE'                     => true,
        'C__CATG__DRIVE'                       => true,
        'C__CATG__IP'                          => true,
        'C__CATG__OPERATING_SYSTEM'            => true,
        'C__CATG__APPLICATION'                 => true,
        'C__CMDB__SUBCAT__NETWORK_PORT'        => true,
        'C__CATG__MODEL'                       => true,
        'C__CATG__VIRTUAL_MACHINE'             => true,
        'C__CATG__CLUSTER_MEMBERSHIPS'         => true,
        'C__CMDB__SUBCAT__NETWORK_INTERFACE_P' => true,
        'C__CATG__GUEST_SYSTEMS'               => true,
        'C__CATG__RM_CONTROLLER'               => true,
        'C__CATG__NET_LISTENER'                => true,
        'C__CATG__UNIVERSAL_INTERFACE'         => true,
        'C__CATG__CONTROLLER_FC_PORT'          => true,
        'C__CATG__LAST_LOGIN_USER'             => true,
        'C__CATG__STACK_MEMBER'                => true
    ];
    /**
     * Cache for supported categories
     *
     * @var array
     * @see get_supported_categories()
     */
    protected $m_supported_categories_cached;

    /**
     * Gets information about property groups.
     *
     * @return array Associative array
     */
    public function get_property_groups()
    {
        if (!isset($this->m_groups))
        {
            $this->m_groups = [
                self::C__COMMON_SETTINGS         => [
                    'title' => _L('LC__MODULE__JDISC__PROFILES__COMMON_SETTINGS')
                ],
                self::C__OBJECT_TYPE_ASSIGNMENTS => [
                    'title' => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS')
                ],
                self::C__ADDITIONAL_OPTIONS      => [
                    'title' => _L('LC__MODULE__JDISC__ADDITIONAL_OPTIONS')
                ]
            ];
        } //if

        return $this->m_groups;
    } //function

    /**
     * Gets information about property tables.
     *
     * @return array Associative array
     */
    public function get_tables()
    {
        if (!isset($this->m_tables))
        {
            $this->m_tables = [
                self::C__CONFIGURATION           => 'isys_jdisc_db',
                self::C__PROFILES                => 'isys_jdisc_profile',
                self::C__OBJECT_TYPE_ASSIGNMENTS => 'isys_jdisc_object_type_assignment'
            ];
        } //if

        return $this->m_tables;
    } //function

    /**
     * Gets information about property types.
     *
     * @return array Associative array
     */
    public function get_property_types()
    {
        if (!isset($this->m_types))
        {
            $this->m_types = [
                self::C__CONFIGURATION           => [
                    'title' => _L('LC__MODULE__JDISC__CONFIGURATION')
                ],
                self::C__PROFILES                => [
                    'title' => _L('LC__MODULE__JDISC__PROFILES')
                ],
                self::C__OBJECT_TYPE_ASSIGNMENTS => [
                    'title' => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS')
                ]
            ];
        } //if

        return $this->m_types;
    } //function

    /**
     * Gets list of categories supported by JDisc suitable for dialog lists.
     *
     * @return array Index array of arrays
     */
    public function get_supported_categories()
    {
        if (!isset($this->m_supported_categories_cached))
        {
            $l_cmdb_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
            /** @var $l_cmdb_dao isys_cmdb_dao */

            $l_all_categories = $l_cmdb_dao->get_all_categories();
            // Only global categories are needed:
            $l_categories = [];
            foreach ($l_all_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $l_category)
            {
                assert('defined($l_category["const"])');
                $l_constant = $l_category['const'];

                if (!isset($this->m_supported_categories[$l_constant]))
                {
                    continue;
                } //if

                $l_categories[$l_constant] = [
                    'id'  => $l_category['id'],
                    'val' => _L($l_category['title']),
                    'sel' => true,
                    'url' => ''
                ];
            } //foreach

            $l_sort = function ($p_arr1, $p_arr2)
            {
                return strcmp($p_arr1['val'], $p_arr2['val']);
            };
            usort($l_categories, $l_sort);

            $this->m_supported_categories_cached = $l_categories;
        } //if

        return $this->m_supported_categories_cached;
    } //function

    /**
     * Fetches database configuration data from database.
     *
     * @param array $p_selections    (optional) Select only these properties. If
     *                               not set (default), all properties will be selected.
     * @param array $p_conditions    (optional) Make some conditions. Associative
     *                               array of properties as keys and the destinated values as values. Defaults
     *                               to no condition.
     * @param bool  $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                               false.
     * @param bool  $p_as_result_set (optional) Returns fetched data as result
     *                               set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     */
    public function get_configuration($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities(self::C__CONFIGURATION, $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches profiles from database.
     *
     * @param array $p_selections    (optional) Select only these properties. If
     *                               not set (default), all properties will be selected.
     * @param array $p_conditions    (optional) Make some conditions. Associative
     *                               array of properties as keys and the destinated values as values. Defaults
     *                               to no condition.
     * @param bool  $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                               false.
     * @param bool  $p_as_result_set (optional) Returns fetched data as result
     *                               set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     */
    public function get_profiles($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities(self::C__PROFILES, $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches profile by its identifier from database.
     *
     * @param array $p_selections    (optional) Select only these properties. If
     *                               not set (default), all properties will be selected.
     * @param bool  $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                               false.
     * @param bool  $p_as_result_set (optional) Returns fetched data as result
     *                               set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     */
    public function get_profile($p_id, $p_selections = null, $p_raw = false, $p_as_result_set = false)
    {
        $l_conditions = ['id' => $p_id];

        return $this->get_entities(self::C__PROFILES, $p_selections, $l_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * @param null $p_selections
     * @param null $p_conditions
     * @param bool $p_raw
     * @param bool $p_as_result_set
     *
     * @return array|isys_component_dao_result
     */
    public function get_object_type_assignments($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities(self::C__OBJECT_TYPE_ASSIGNMENTS, $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * @param      $p_profile
     * @param null $p_selections
     * @param null $p_conditions
     * @param bool $p_raw
     * @param bool $p_as_result_set
     *
     * @return array|isys_component_dao_result
     */
    public function get_object_type_assignments_by_profile($p_profile, $p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        assert('is_int($p_profile)');
        $l_conditions = [];
        if (is_array($p_conditions))
        {
            $l_conditions = $p_conditions;
        }
        $l_conditions['profile'] = $p_profile;

        return $this->get_entities(self::C__OBJECT_TYPE_ASSIGNMENTS, $p_selections, $l_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches all data from database.
     *
     * @return  array
     */
    public function get_data()
    {
        return $this->get_configuration();
    } //function

    /**
     * Provides access to JDisc's database.
     *
     * @throws  isys_exception_general
     * @return  isys_component_database_pdo
     */
    public function get_connection($p_config_id = null)
    {
        if (isset($this->m_pdo))
        {
            return $this->m_pdo;
        } // if

        $this->m_log->debug('Providing access to JDisc\'s database...');

        // Fetch database configuration:
        if ($p_config_id !== null)
        {
            $l_config = end($this->get_configuration(null, ['id' => $p_config_id]));
        }
        else
        {
            $l_config = end($this->get_configuration(null, ['default_server' => '1']));
        } // if

        if ($l_config === false)
        {
            throw new isys_exception_general('JDisc configuration is missing.');
        } // if

        try
        {
            if ($this->m_server_id === null)
            {
                $this->m_server_id = $l_config['id'];
            } // if
            $this->m_pdo = new isys_component_database_pdo(
                'pgsql',
                $l_config['host'],
                $l_config['port'],
                $l_config['username'],
                isys_helper_crypt::decrypt($l_config['password'], 'C__MODULE__JDISC'),
                $l_config['database']
            );
        }
        catch (isys_exception_database $e)
        {
            throw new Exception(_L('LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_WITH_MESSAGE', $e->getMessage()));
        } // try

        return $this->m_pdo;
    } // function

    /**
     * Fetches all JDisc device types from database. This data depends on the JDisc release.
     *
     * @return  array
     */
    public function get_jdisc_device_types()
    {
        $l_data = $this->fetch_array('SELECT * FROM devicetypelookup WHERE id > 0 ORDER BY singular;');
        if (!$l_data)
        {
            // Get jdisc types from local database instead from the JDisc server.
            $l_data = $this->get_local_jdisc_device_types();
        } // if
        return $l_data;
    } // function

    /**
     * Fetches all JDisc device types from local database. In case we don't have any connection to a JDisc Server.
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_local_jdisc_device_types()
    {
        $l_res  = $this->retrieve('SELECT isys_jdisc_device_type__description AS id, isys_jdisc_device_type__title AS singular FROM isys_jdisc_device_type;');
        $l_data = [];
        while ($l_row = $l_res->get_row())
        {
            $l_data[] = $l_row;
        } // while
        return $l_data;
    } // function

    /**
     * Fetches all JDisc groups from the JDisc database.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_jdisc_groups()
    {
        return $this->fetch_array('SELECT * FROM devicegroup ORDER BY id;');
    } // function

    /**
     * Fetches all JDisc operating systems from database. This data depends on the inventory data.
     *
     * @param    $p_field     string
     * @param    $p_id        int
     *
     * @return  array
     */
    public function get_jdisc_operating_systems($p_field = null, $p_id = null)
    {
        $l_query = 'SELECT ';
        if ($p_field !== null)
        {
            $l_query .= $p_field . ' ';
        }
        else
        {
            $l_query .= ' * ';
        }
        $l_query .= 'FROM operatingsystem ';
        if ($p_id !== null)
        {
            $l_query .= 'WHERE id = ' . $this->convert_sql_id($p_id) . ' ';
        }
        $l_query .= 'ORDER BY osversion;';

        return $this->fetch_array($l_query);
    } // function

    /**
     * Gets the current JDisc version
     *
     * @return float
     */
    public function get_version($p_jdisc_servier_id)
    {
        if ($this->m_current_version != '')
        {
            return $this->m_current_version;
        }

        $l_dao        = $this->get_connection($p_jdisc_servier_id);
        $l_result_set = $l_dao->query('SELECT * FROM installationinfo');
        $l_arr        = $l_dao->fetch_row_assoc($l_result_set);

        return (float) ($l_arr['majorversion'] . '.' . $l_arr['minorversion']);
    } // function

    /**
     * Checks if specified profile id exists
     *
     * @param $p_id
     *
     * @return bool
     */
    public function profile_exists($p_id)
    {
        return (($this->retrieve('SELECT isys_jdisc_profile__id FROM isys_jdisc_profile WHERE isys_jdisc_profile__id = ' . $this->convert_sql_id($p_id))
                ->num_rows() > 0) ? true : false);
    } // function

    /**
     * Resets the default server
     *
     * @param $p_exclude int
     *
     * @return bool
     */
    public function reset_default_server($p_exclude)
    {
        $l_update = 'UPDATE isys_jdisc_db SET isys_jdisc_db__default_server = 0 WHERE isys_jdisc_db__id != ' . $this->convert_sql_id($p_exclude);

        return $this->update($l_update) && $this->apply_update();
    } // function

    /**
     * Switches the database
     *
     * @param $p_id
     *
     * @throws isys_exception_general
     */
    public function switch_database($p_id)
    {
        $l_config = end($this->get_configuration(null, ['id' => $p_id]));

        if ($l_config)
        {
            try
            {
                $this->m_server_id = $p_id;
                $this->m_pdo       = new isys_component_database_pdo(
                    'pgsql',
                    $l_config['host'],
                    $l_config['port'],
                    $l_config['username'],
                    isys_helper_crypt::decrypt($l_config['password'], 'C__MODULE__JDISC'),
                    $l_config['database']
                );

                return true;
            }
            catch (isys_exception_database $e)
            {
                throw new Exception(_L('LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_WITH_MESSAGE', $e->getMessage()));
            }
            catch (Exception $e)
            {
                return false;
            } // try
        }

        return false;
    } // function

    /**
     * Gets all or one specific jdisc server
     *
     * @param int $p_id
     *
     * @return isys_component_dao_result
     */
    public function get_jdisc_servers($p_id = null, $p_default_server = false)
    {
        $l_sql = 'SELECT isys_jdisc_db__id, isys_jdisc_db__host, isys_jdisc_db__port, isys_jdisc_db__database,
			isys_jdisc_db__username, isys_jdisc_db__version_check, isys_jdisc_db__default_server FROM isys_jdisc_db';

        if ($p_id !== null)
        {
            $l_sql .= ' WHERE isys_jdisc_db__id = ' . $this->convert_sql_id($p_id);
        } // if
        if ($p_id === null && $p_default_server)
        {
            $l_sql .= ' WHERE isys_jdisc_db__default_server = 1';
        } // if
        return $this->retrieve($l_sql);
    } // function

    /**
     * Get JDisc Server list resultset for the list view
     *
     * @return mixed
     */
    public function get_jdisc_server_list()
    {
        $l_sql = 'SELECT isys_jdisc_db__id, isys_jdisc_db__host, isys_jdisc_db__port, isys_jdisc_db__database,
			isys_jdisc_db__username, isys_jdisc_db__version_check, isys_jdisc_db__host,
			isys_jdisc_db__discovery_username, isys_jdisc_db__discovery_password, isys_jdisc_db__discovery_port,
			isys_jdisc_db__discovery_protocol, isys_jdisc_db__default_server FROM isys_jdisc_db WHERE TRUE';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Get relevant information for the jdisc discovery
     *
     * @param $p_id
     *
     * @return mixed
     */
    public function get_jdisc_discovery_data($p_id = null, $p_default = false)
    {
        $l_sql = 'SELECT isys_jdisc_db__host, isys_jdisc_db__discovery_username, isys_jdisc_db__discovery_password,
			isys_jdisc_db__discovery_port, isys_jdisc_db__discovery_protocol FROM isys_jdisc_db';

        if ($p_id !== null)
        {
            $l_sql .= ' WHERE isys_jdisc_db__id = ' . $this->convert_sql_id($p_id);
        }
        elseif ($p_id === null && $p_default)
        {
            $l_sql .= ' WHERE isys_jdisc_db__default_server = 1';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Checks if the configured JDisc Server is a JEDI Version or not.
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_jedi_version()
    {
        try
        {
            $l_dao        = $this->get_connection();
            $l_result_set = $l_dao->query('SELECT * FROM installationinfo');
            $l_arr        = $l_dao->fetch_row_assoc($l_result_set);
            if (isset($l_arr['edition']))
            {
                return (strtolower($l_arr['edition']) == 'essential') ? true : false;
            }
            else
            {
                // old version
                return false;
            } // if
        }
        catch (Exception $e)
        {
            return false;
        } // try
    } // function

    /**
     * Checks if connection can be established
     *
     * @param null $p_id
     *
     * @return bool
     */
    public function is_connected($p_id = null)
    {
        try
        {
            $this->get_connection($p_id);

            return true;
        }
        catch (isys_exception_general $e)
        {
            return false;
        }
        catch (Exception $e)
        {
            return false;
        } // try
    } // function

    /**
     * Wrapper for deleting whole category entries for the specified object id
     *
     * @param $p_table
     * @param $p_object_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function clear_category($p_table, $p_object_id, $p_has_relation = true)
    {
        $this->m_log->debug('Deleting category entries from table ' . $p_table . ' with object ID "' . $p_object_id . '".');
        $l_cmdb_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        if ($l_cmdb_dao->clear_data($p_object_id, $p_table, $p_has_relation))
        {
            $this->m_log->debug('Category entries successfully deleted from table ' . $p_table . ' for object ID "' . $p_object_id . '".');

            return true;
        }
        else
        {
            $this->m_log->debug('Could not delete category entries from table ' . $p_table . ' for object ID "' . $p_object_id . '".');

            return false;
        }
    } // function

    /**
     * Save jdisc default profile to isys_obj_type
     *
     * @param int $p_obj_type_id
     * @param int $p_verinice_type
     *
     * @return boolean
     */
    public function set_jdisc_default_profile($p_obj_type_id, $p_jdisc_profile_id)
    {

        $l_sql = 'UPDATE isys_obj_type SET ' . 'isys_obj_type__isys_jdisc_profile__id = ' . $this->convert_sql_id(
                $p_jdisc_profile_id
            ) . ' ' . 'WHERE isys_obj_type__id = ' . $this->convert_sql_int($p_obj_type_id);

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Get jdisc profile id by
     *
     * @param $p_obj_type_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_jdisc_default_profile($p_obj_type_id)
    {
        $l_sql = 'SELECT * FROM isys_obj_type
          INNER JOIN isys_jdisc_profile ON isys_jdisc_profile__id = isys_obj_type__isys_jdisc_profile__id
          WHERE isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type_id);

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Get jdisc server by jdisc profile
     *
     * @param $p_jdisc_profile_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_jdisc_server_by_profile($p_jdisc_profile_id)
    {
        $l_sql = 'SELECT isys_jdisc_db.* FROM isys_jdisc_profile
          INNER JOIN isys_jdisc_db ON isys_jdisc_db__id = isys_jdisc_profile__jdisc_server
          WHERE isys_jdisc_profile__id = ' . $this->convert_sql_id($p_jdisc_profile_id);

        return $this->retrieve($l_sql . ';');
    } // function

    public function get_server_id()
    {
        return $this->m_server_id;
    } // function

    /**
     * Method which determines if the object id is the one for the imported object
     *
     * @param       $p_object_id
     * @param array $p_checks
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_object_id($p_object_id, array $p_checks)
    {
        if (count($p_checks) > 0)
        {
            $l_check_query = 'SELECT (';
            foreach ($p_checks AS $l_check_method => $l_check_value)
            {
                $l_query = $this->$l_check_method($p_object_id, $l_check_value, true);
                $l_check_query .= '(' . $l_query . ') +';
            } // foreach
            $l_check_query = rtrim($l_check_query, '+') . ') AS obj_check';
            $l_check_count = $this->retrieve($l_check_query)
                ->get_row_value('obj_check');

            if ((count($p_checks) > 1 && $l_check_count < 2) || (count($p_checks) === 1 && $l_check_count < 1))
            {
                // Check not successful
                return false;
            } // if
            return true;
        } // if
        return false;
    } // function

    /**
     * Method which checks if the hostname belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_hostname($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_sql = 'SELECT COUNT(isys_catg_ip_list__id) FROM isys_catg_ip_list WHERE isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ' AND isys_catg_ip_list__hostname = ' . $this->convert_sql_text($p_value);
        if ($p_as_query)
        {
            return $l_sql;
        }
        else
        {
            return (bool) $this->retrieve($l_sql)
                ->num_rows();
        } // if
    } // function

    /**
     * Method which checks if the serial belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_serial($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_sql = 'SELECT COUNT(isys_catg_model_list__id) FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $this->convert_sql_text(
                $p_obj_id
            ) . ' AND isys_catg_model_list__serial = ' . $this->convert_sql_text($p_value);
        if ($p_as_query)
        {
            return $l_sql;
        }
        else
        {
            return (bool) $this->retrieve($l_sql)
                ->num_rows();
        } // if
    } // function

    /**
     * Method which checks if the fqdn belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_fqdn($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_hostname = $p_value[0];
        $l_fqdn     = $p_value[1];
        $l_dns      = trim(str_replace($l_hostname, '', $l_fqdn), '.');

        $l_sql = 'SELECT COUNT(main.isys_catg_ip_list__id) FROM isys_catg_ip_list AS main
                INNER JOIN isys_catg_ip_list_2_isys_net_dns_domain AS con ON main.isys_catg_ip_list__id = con.isys_catg_ip_list__id
                INNER JOIN isys_net_dns_domain AS dns ON dns.isys_net_dns_domain__id = con.isys_net_dns_domain__id
                WHERE main.isys_catg_ip_list__hostname = ' . $this->convert_sql_text($l_hostname) . ' AND dns.isys_net_dns_domain__title = ' . $this->convert_sql_text(
                $l_dns
            ) . ' AND main.isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        if ($p_as_query)
        {
            return $l_sql;
        }
        else
        {
            return (bool) $this->retrieve($l_sql)
                ->num_rows();
        } // if
    } // function

    /**
     * Method which checks if the mac belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_mac($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_mac_condition = "('" . implode("','", $p_value) . "')";

        $l_sql = 'SELECT COUNT(isys_catg_port_list__id) FROM isys_catg_port_list WHERE isys_catg_port_list__mac IN ' . $l_mac_condition . ' AND isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            );
        if ($p_as_query)
        {
            return $l_sql;
        }
        else
        {
            return (bool) $this->retrieve($l_sql)
                ->num_rows();
        } // if
    } // if

    /**
     * Method which checks if the object title belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_name($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_sql = 'SELECT COUNT(isys_obj__id) FROM isys_obj WHERE isys_obj__title = ' . $this->convert_sql_text($p_value) . ' AND isys_obj__id = ' . $this->convert_sql_text(
                $p_obj_id
            );
        if ($p_as_query)
        {
            return $l_sql;
        }
        else
        {
            return (bool) $this->retrieve($l_sql)
                ->num_rows();
        } // if
    } // function

    /**
     * Provides information about properties.
     */
    protected function build_properties()
    {
        $l_provides_all = self::C__PROPERTY__PROVIDES__VIEW + self::C__PROPERTY__PROVIDES__CREATE + self::C__PROPERTY__PROVIDES__SAVE + self::C__PROPERTY__PROVIDES__DELETE;

        $this->m_properties = [
            self::C__CONFIGURATION           => [
                'id'                 => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__ID',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'host'               => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__HOST')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__host',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'localhost'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__HOST',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'port'               => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__PORT')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__port',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => 25321
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__PORT',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 1,
                                    'max_range' => 65535
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'database'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__DATABASE')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__database',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'inventory'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DATABASE',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'username'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__CONFIGURATION__USERNAME'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__CONFIGURATION__USERNAME__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__username',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'postgresro'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__USERNAME',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'password'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__PASSWORD')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__password',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'crypt'                  => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__PASSWORD',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bPassword' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'version_check'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__VERSION_CHECK')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__version_check',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__VERSION_CHECK',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_username' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_username',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => ''
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_password' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_password',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'crypt'                  => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bPassword' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_port'     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_port',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => 9000
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 1,
                                    'max_range' => 65535
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_protocol' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_protocol',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'http'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'default_server'     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__default_server',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 1
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
            ],
            self::C__PROFILES                => [
                'id'                           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__PROFILES__ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__ID',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'jdisc_server'                 => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__IMPORT__JDISC_SERVERS'),
                        'group'                  => self::C__COMMON_SETTINGS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__jdisc_server',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__IMPORT__JDISC_SERVERS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_module_jdisc',
                                    'callback_get_jdisc_servers_as_array'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'title'                        => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__PROFILES__TITLE'),
                        'group'                  => self::C__COMMON_SETTINGS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__title',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__PROFILES__TITLE',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'description'                  => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__PROFILES__DESCRIPTION'),
                        'group'                  => self::C__COMMON_SETTINGS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__description',
                        C__PROPERTY__DATA__TYPE  => 'text',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__DESCRIPTION',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXTAREA,
                        C__PROPERTY__UI__PARAMS => [
                            'p_nRows' => '3',
                            'p_nCols' => '55'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_textarea'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'categories'                   => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__PROFILES__CATEGORIES'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__PROFILES__CATEGORIES__DESCRIPTION'),
                        'group'                        => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__categories',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => serialize($this->get_supported_categories())
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__CATEGORIES',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist' => '1'
                        ],
                        'post'                  => 'C__MODULE__JDISC__PROFILES__CATEGORIES__selected_box',
                        'default'               => _L('LC__MODULE__SEARCH__ALL_CATEGORIES')
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_list_of_ids'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_software'          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_software',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__ONLY_CREATE_SOFTWARE_RELATIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_networks'          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_networks',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__ONLY_CREATE_NETWORK_RELATIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_clusters'          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_clusters',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CREATE_CLUSTER',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_blade_connections' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_blade_connections',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CREATE_BLADE_CONNECTIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_custom_attributes'     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__ADD_CUSTOM_ATTRIBUTES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_custom_attributes',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__IMPORT_CUSTOM_ATTRIBUTES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_vlans'             => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__VLAN_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_vlans',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CREATE_VLAN_RELATIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_type_interfaces'       => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('Import type interfaces'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_type_interfaces',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 2,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CHASSIS_INTERFACE_OPTION',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_arData'     => serialize(
                                [
                                    0 => _L('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__CATEGORY_INTERFACE'),
                                    1 => _L('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__CATEGORY_CHASSIS'),
                                    2 => _L('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__BOTH_CATEGORIES')
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'cmdb_status'                  => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__cmdb_status',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => C__CMDB_STATUS__IN_OPERATION,
                        'params'                 => [
                            'unsigned'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CMDB_STATUS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_bSort'      => false,
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_module_jdisc',
                                    'callback_get_cmdb_status_as_array'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'use_default_templates'        => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__USE_DEFAULT_TEMPLATES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__use_default_templates',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__USE_DEFAULT_TEMPLATES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'software_filter'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER__DESCRIPTION'),
                        'group'                        => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__software_filter',
                        C__PROPERTY__DATA__TYPE  => 'text',
                        'default'                => null,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__SOFTWARE_FILTER',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXTAREA,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'software_filter_type'         => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__software_filter_type',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__SOFTWARE_FILTER_TYPE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_bSort'      => false,
                            'p_arData'     => [
                                0 => 'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE__WHITELIST',
                                1 => 'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE__BLACKLIST'
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ]
            ],
            self::C__OBJECT_TYPE_ASSIGNMENTS => [
                'id'                    => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__ID',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'profile'               => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PROFILE')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__' . $this->m_tables[self::C__PROFILES] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PROFILE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_type'            => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_type',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_type_customized' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE_CUSTOMIZED'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_type_customized',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE_CUSTOMIZED',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_os'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_os',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_os_customized'   => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS_CUSTOMIZED'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_os_customized',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS_CUSTOMIZED',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'object_type'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__isys_obj_type__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj_type',
                            'isys_obj_type__id'
                        ],
                        'default'                     => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'port_filter'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('Port Filter'),
                        //                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'),
                        'group'                  => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__port_filter',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER',
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'port_filter_type'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('Port Filter type'),
                        //                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'),
                        'group'                  => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__port_filter_type',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER_TYPE'
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'location'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('Location'),
                        //						C__PROPERTY__INFO__DESCRIPTION => _L('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'),
                        'group'                  => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__object_location__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj',
                            'isys_obj__id'
                        ],
                        'default'                     => '0'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_LOCATION',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__POPUP,
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ]
            ]
        ];

        return $this;
    } // function

    /**
     * Fetches data from JDisc database.
     *
     * @param   string $p_query SQL query
     *
     * @return  array
     */
    protected function fetch_array($p_query)
    {
        try
        {
            $l_dao        = $this->get_connection();
            $l_result_set = $l_dao->query($p_query);
            $l_result     = [];
            while ($l_row = $l_dao->fetch_row_assoc($l_result_set))
            {
                $l_result[] = $l_row;
            } // while
            return $l_result;
        }
        catch (Exception $e)
        {
            return false;
        } // try
    } // function

    /**
     * Constructor.
     *
     * @param  isys_component_database $p_db  Database component
     * @param  isys_log                $p_log Logger
     */
    public function __construct(isys_component_database $p_db, $p_log)
    {
        parent::__construct($p_db);

        $this->m_log = $p_log;
        $this->get_tables();
    } // function
} // class