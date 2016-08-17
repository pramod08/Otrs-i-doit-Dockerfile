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
 * Notification module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_notifications_dao extends isys_module_dao
{
    /**
     * Notification status: deactivated
     */
    const C__STATUS__DEACTIVATED = 0;

    /**
     * Notification status: activated
     */
    const C__STATUS__ACTIVATED = 1;

    /**
     * Notification status: limit reached; no more notifications
     */
    const C__STATUS__LIMIT_REACHED = 2;

    /**
     * Notification status: incomplete notification; skip it
     */
    const C__STATUS__INCOMPLETE = 4;

    /**
     * Notification status: all together (only used for internal bitwise operations)
     */
    const C__STATUS__ALL = 7;

    /**
     * Notification domain: none
     */
    const C__DOMAIN__NONE = 0;

    /**
     * Notification domain: objects
     */
    const C__DOMAIN__OBJECTS = 1;

    /**
     * Notification domain: object types
     */
    const C__DOMAIN__OBJECT_TYPES = 2;

    /**
     * Notification domain: reports
     */
    const C__DOMAIN__REPORTS = 4;

    /**
     * Notification domain: all
     */
    const C__DOMAIN__ALL = 7;
    /**
     * Object mapping for contacts which are assigned by roles.
     *
     * @var  array
     */
    public static $m_receivers_roles_object_mapping = [];
    /**
     * Data cache.
     *
     * @var  array
     */
    protected $m_cache;
    /**
     * Domain types.
     *
     * @var  array
     */
    protected $m_domain_types;
    /**
     * Logger.
     *
     * @var  isys_log
     */
    protected $m_log;
    /**
     * Data tables for properties.
     *
     * @var  array  Associative array of strings
     */
    protected $m_tables = [
        'notifications'          => 'isys_notification',
        'notification_domains'   => 'isys_notification_domain',
        'notification_types'     => 'isys_notification_type',
        'notification_roles'     => 'isys_notification_role',
        'notification_templates' => 'isys_notification_template',

        'units'        => 'isys_unit',
        'objects'      => 'isys_obj',
        'object_types' => 'isys_obj_type',
        'reports'      => 'isys_report',
        'contacts'     => 'isys_contact',
        'roles'        => 'isys_contact_tag'
    ];

    /**
     * Gets information about property types.
     *
     * @param   string $p_type (optional) Property type (e. g. 'notifications'). Defaults to null (all property types will be fetched).
     *
     * @return  array  Associative array
     */
    public function get_property_types($p_type = null)
    {
        if (!isset($this->m_types))
        {
            $this->m_types = [
                'notifications'          => [
                    'title' => _L('LC__NOTIFICATIONS__NOTIFICATIONS')
                ],
                'notification_domains'   => [
                    'title' => _L('LC__NOTIFICATIONS__DOMAINS')
                ],
                'notification_roles'     => [
                    'title' => _L('LC__NOTIFICATIONS__NOTIFICATION_ROLES')
                ],
                'notification_types'     => [
                    'title' => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPES')
                ],
                'notification_templates' => [
                    'title' => _L('LC__NOTIFICATIONS__NOTIFICATION_TEMPLATES')
                ]
            ];
        } //if

        if (isset($p_type))
        {
            assert('array_key_exists($p_type, $this->m_groups)');

            return $this->m_types[$p_type];
        } //if

        return $this->m_types;
    } // function

    /**
     * Gets information about property groups.
     *
     * @return  array
     */
    public function get_property_groups()
    {
        if (!isset($this->m_groups))
        {
            $this->m_groups = [
                'common_settings' => [
                    'title' => _L('LC__NOTIFICATIONS__COMMON_SETTINGS')
                ],
                'domains'         => [
                    'title' => _L('LC__NOTIFICATIONS__DOMAINS')
                ],
                'receivers'       => [
                    'title' => _L('LC__NOTIFICATIONS__RECEIVERS')
                ]
            ];
        } //if

        return $this->m_groups;
    } //function

    /**
     * Gets information about domain types.
     *
     * @param   string $p_type (optional) Domain type (e. g. 'objects'). Defaults to null (all domain types will be fetched).
     *
     * @return  array  Associative array
     */
    public function get_domain_types($p_type = null)
    {
        if (!isset($this->m_domain_types))
        {
            $this->m_domain_types = [
                'objects'      => [
                    'title' => _L('LC__NOTIFICATIONS__DOMAIN_OBJECTS')
                ],
                'object_types' => [
                    'title' => _L('LC__NOTIFICATIONS__DOMAIN_OBJECT_TYPES')
                ],
                'reports'      => [
                    'title' => _L('LC__NOTIFICATIONS__DOMAIN_REPORTS')
                ]
            ];
        } // if

        if (isset($p_type))
        {
            assert('array_key_exists($p_type, $this->m_domain_types)');

            return $this->m_domain_types[$p_type];
        } // if

        return $this->m_domain_types;
    } //function

    /**
     * Gets notifications status messages.
     *
     * @return  array
     */
    public function get_status()
    {
        return [
            self::C__STATUS__DEACTIVATED   => _L('LC__NOTIFICATIONS__STATUS__DEACTIVATED'),
            self::C__STATUS__ACTIVATED     => _L('LC__NOTIFICATIONS__STATUS__ACTIVATED'),
            self::C__STATUS__LIMIT_REACHED => _L('LC__NOTIFICATIONS__STATUS__LIMIT_REACHED'),
            self::C__STATUS__INCOMPLETE    => _L('LC__NOTIFICATIONS__STATUS__INCOMPLETE')
        ];
    } // function

    /**
     * Fetches available languages from system database.
     *
     * @return array Associative array with language abbreviations as keys and
     * their titles as values.
     */
    public function get_locales()
    {
        global $g_comp_database_system;

        $l_languages = [];
        $l_dao       = new isys_component_dao($g_comp_database_system);

        $l_query = "SELECT isys_language__title, isys_language__short FROM isys_language WHERE isys_language__const != 'ISYS_LANGUAGE_ALL';";

        $l_result = $l_dao->retrieve($l_query);

        if ($l_result->num_rows() > 1)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_languages[$l_row['isys_language__short']] = _L($l_row['isys_language__title']);
            } //while
        } //if

        return $l_languages;
    } // function

    /**
     * Gets available placeholders.
     *
     * @return array Indexed array of associative arrays which contains 'value',
     * 'title', and (optional) 'description'
     */
    public function get_placeholders()
    {
        $l_all_properties = $this->get_properties();

        $l_result = [];

        // Use properties:
        foreach ($l_all_properties as $l_property_type => $l_properties)
        {
            foreach ($l_properties as $l_property_tag => $l_property_info)
            {
                $l_entry                           = [];
                $l_entry['value']                  = '%' . $l_property_type . '__' . $l_property_tag . '%';
                $l_entry[C__PROPERTY__INFO__TITLE] = $l_property_info[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
                if (array_key_exists(C__PROPERTY__INFO__DESCRIPTION, $l_property_info[C__PROPERTY__INFO]))
                {
                    $l_entry[C__PROPERTY__INFO__DESCRIPTION] = $l_property_info[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION];
                }
                $l_result[] = $l_entry;
            } //foreach
        } //foreach

        // Add receivers:
        $l_receivers = [
            [
                'value' => '%receivers__title%',
                'title' => _L('LC__CMDB__CATG__CONTACT_TITLE')
            ],
            [
                'value' => '%receivers__email%',
                'title' => _L('LC__CATG__CONTACT_EMAIL')
            ]
        ];

        $l_result = array_merge($l_result, $l_receivers);

        return $l_result;
    } // function

    /**
     * Fetches notification data from database.
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
     * @return mixed Associative array or result set (isys_component_dao_result)
     */
    public function get_notifications($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities('notifications', $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches notification template data from database.
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
     * @return mixed Associative array or result set (isys_component_dao_result)
     */
    public function get_templates($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities('notification_templates', $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches notification data from database.
     *
     * @param int  $p_id            (optional) Select notification identifier. If not set
     *                              (default), all notifications will be selected.
     * @param bool $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                              false.
     * @param bool $p_as_result_set (optional) Returns fetched data as result
     *                              set. Defaults to false.
     *
     * @return mixed Associative array or result set (isys_component_dao_result)
     */
    public function get_notification($p_id = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->fetch_properties('notifications', $p_id, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches notification types from database.
     *
     * @param int  $p_id            (optional) Select notification type identifier. If
     *                              not set (default), all notification types will be selected.
     * @param bool $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                              false.
     * @param bool $p_as_result_set (optional) Returns fetched data as result
     *                              set. Defaults to false.
     *
     * @return mixed Associative array or result set (isys_component_dao_result)
     */
    public function get_type($p_id = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->fetch_properties('notification_types', $p_id, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches affected objects from database. All domains will be asked for
     * data: assigned objects, object types and reports.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array Associative array
     */
    public function get_objects($p_notification_id)
    {
        assert('is_int($p_notification_id) && $p_notification_id >= 0');

        if (isset($this->m_cache['objects'][$p_notification_id]))
        {
            $this->m_cache['objects'][$p_notification_id];
        } //if

        $this->m_cache['objects'][$p_notification_id] = [];

        $l_query = 'SELECT * FROM ' . $this->m_tables['notification_domains'] . ' WHERE ' . $this->m_tables['notification_domains'] . '__' . $this->m_tables['notifications'] . '__id = ' . $this->convert_sql_id(
                $p_notification_id
            ) . ';';

        $l_notification_domains = $this->retrieve($l_query)
            ->__as_array();

        $l_object_ids = [];

        $l_cmdb_dao = new isys_cmdb_dao($this->m_db);

        foreach ($l_notification_domains as $l_notification_domain)
        {
            if (isset($l_notification_domain[$this->m_tables['notification_domains'] . '__' . $this->m_tables['objects'] . '__id']))
            {
                $l_object_ids[] = $l_notification_domain[$this->m_tables['notification_domains'] . '__' . $this->m_tables['objects'] . '__id'];
            } //if

            if (isset($l_notification_domain[$this->m_tables['notification_domains'] . '__' . $this->m_tables['object_types'] . '__id']))
            {
                $l_objects_by_type = $l_cmdb_dao->get_objects_by_type(
                    intval($l_notification_domain[$this->m_tables['notification_domains'] . '__' . $this->m_tables['object_types'] . '__id'])
                )
                    ->__as_array();
                foreach ($l_objects_by_type as $l_object)
                {
                    $l_object_ids[] = $l_object[$this->m_tables['objects'] . '__id'];
                } //foreach
            } //if

            if (isset($l_notification_domain[$this->m_tables['notification_domains'] . '__isys_report__id']))
            {
                $l_reports = $this->get_all_reports();

                foreach ($l_reports as $l_report)
                {
                    if ($l_report['isys_report__id'] == $l_notification_domain[$this->m_tables['notification_domains'] . '__isys_report__id'])
                    {
                        $l_query  = $l_report['isys_report__query'];
                        $l_result = $this->retrieve($l_query)
                            ->__as_array();
                        // 'Smart' check:
                        if (count($l_result) == 0 || !array_key_exists('__id__', $l_result[0]))
                        {
                            $this->m_log->notice('You\'ve selected a report that is not compatible with this notification. Object identifers are required as "__id__"');
                        }
                        else
                        {
                            foreach ($l_result as $l_entry)
                            {
                                $l_object_ids[] = intval($l_entry['__id__']);
                            } //foreach
                        } //if
                        break;
                    } //if
                } //foreach
            } //if
        } //foreach

        if (count($l_object_ids) > 0)
        {
            $this->m_cache['objects'][$p_notification_id] = $l_cmdb_dao->get_objects(['ids' => $l_object_ids])
                ->__as_array();
        } //if

        return $this->m_cache['objects'][$p_notification_id];
    } //function

    /**
     * Fetches notification domains from database.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array Associative array with all domain types as keys
     */
    public function get_domains($p_notification_id)
    {
        assert('is_int($p_notification_id) && $p_notification_id >= 0');

        $p_property_type = 'notification_domains';

        if (is_array($this->m_cache[$p_property_type]) && isset($this->m_cache[$p_property_type][$p_notification_id]))
        {
            return $this->m_cache[$p_property_type][$p_notification_id];
        } //if

        $l_domain_types = $this->get_domain_types();

        $this->m_cache[$p_property_type][$p_notification_id] = [];
        foreach ($l_domain_types as $l_domain => $l_null)
        {
            $this->m_cache[$p_property_type][$p_notification_id][$l_domain] = null;
        } //foreach

        $l_query = 'SELECT * FROM ' . $this->m_tables[$p_property_type] . ' WHERE `' . $this->m_tables[$p_property_type] . '__' . $this->m_tables['notifications'] . '__id` = ' . $this->convert_sql_id(
                $p_notification_id
            ) . ';';

        $l_raw_data = $this->retrieve($l_query)
            ->__as_array();
        $l_data     = [];

        foreach ($l_raw_data as $l_raw)
        {
            $l_data[] = $this->map_properties(
                $p_property_type,
                $l_raw
            );
        } //foreach

        if (count($l_data) == 0)
        {
            return $this->m_cache[$p_property_type][$p_notification_id];
        } //if

        foreach ($l_data as $l_entity)
        {
            foreach ($l_domain_types as $l_domain => $l_null)
            {
                if (isset($l_entity[$l_domain]))
                {
                    $this->m_cache[$p_property_type][$p_notification_id][$l_domain][] = $l_entity[$l_domain];
                } //if
            } //foreach
        } //foreach

        return $this->m_cache[$p_property_type][$p_notification_id];
    } //function

    /**
     * Fetches receivers from database based on assigned roles and contacts.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array Array of receivers' object identifiers ('id'), their full
     * names ('title'), email addresses ('email'), and locale ('locale') as
     * values.
     *
     * @todo Also fetch locale settings
     */
    public function get_receivers($p_notification_id)
    {
        assert('is_int($p_notification_id) && $p_notification_id >= 0');

        $this->m_log->debug('Fetching receivers from database based on assigned roles and contacts...');

        if (is_array($this->m_cache['receivers']) && array_key_exists($p_notification_id, $this->m_cache['receivers']))
        {
            $this->m_log->debug('Cache found.');

            return $this->m_cache['receivers'][$p_notification_id];
        } //if

        $this->m_cache['receivers'][$p_notification_id]['contacts']          = [];
        $this->m_cache['receivers'][$p_notification_id]['contacts_by_roles'] = [];

        // Contacts from notification:
        $l_receivers_arr['contacts'] = $this->resolve_receivers_from_contacts($this->get_receivers_by_contacts($p_notification_id));

        // Contacts by roles from notification:
        $l_receivers_arr['contacts_by_roles'] = $this->get_receivers_by_roles($p_notification_id);

        if (count($l_receivers_arr['contacts_by_roles']) > 0 || count($l_receivers_arr['contacts']))
        {
            $l_receivers_already_used = [];

            foreach ($l_receivers_arr AS $l_type => $l_rec_arr)
            {
                $l_condition = [];

                foreach ($l_rec_arr as $l_receiver)
                {
                    $l_condition[] = 'isys_obj__id = ' . $this->convert_sql_id($l_receiver);

                } //foreach
                if (count($l_condition) === 0) continue;

                // Fetch identifiers (isys_obj__id), full names (isys_obj__title) and
                // email addresses (isys_cats_person_list__mail_address) from database:
                $l_query = 'SELECT isys_obj__id AS id, isys_obj__title AS title, isys_catg_mail_addresses_list__title AS email ' . 'FROM isys_obj ' . 'INNER JOIN isys_cats_person_list ' . 'ON isys_cats_person_list__isys_obj__id = isys_obj__id ' . 'LEFT JOIN isys_catg_mail_addresses_list ' . 'ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1 ' . 'WHERE (' . implode(
                        ' OR ',
                        $l_condition
                    ) . ') AND isys_catg_mail_addresses_list__title <> \'\' AND isys_obj__title <> \'\';';

                $l_receivers = $this->retrieve($l_query)
                    ->__as_array();
                if (count($l_receivers) === 0)
                {
                    //return $this->m_cache['receivers'][$p_notification_id];
                    continue;
                } //if

                foreach ($l_receivers as $l_receiver)
                {
                    if (isset($l_receivers_already_used[$l_receiver['id']])) continue;

                    $l_new_receiver = $l_receiver;
                    // Language:
                    $l_locale            = isys_locale::get($this->m_db, $l_receiver['id']);
                    $l_language_constant = $l_locale->get_setting(LC_LANG);
                    $l_language          = $l_locale->resolve_language_by_constant($l_language_constant);
                    if (isset($l_language))
                    {
                        $l_new_receiver['locale'] = $l_language;
                    }
                    else
                    {
                        $this->m_log->warning(
                            sprintf(
                                'Language not set for user "%s" [%s]. Skip receiver.',
                                $l_receiver['title'],
                                $l_receiver['id']
                            )
                        );
                        continue;
                    } //if

                    if ($l_type === 'contacts_by_roles')
                    {
                        // Assigned objects
                        $l_new_receiver['assigned_objects'] = self::$m_receivers_roles_object_mapping[$l_receiver['id']];
                    } // if

                    $this->m_cache['receivers'][$p_notification_id][$l_type][] = $l_new_receiver;
                    $l_receivers_already_used[$l_new_receiver['id']]           = true;
                } //foreach
            } // foreach
        } //if

        $this->m_log->debug(
            sprintf(
                'Amount of receivers: %s',
                count($this->m_cache['receivers'][$p_notification_id]['contacts']) + count($this->m_cache['receivers'][$p_notification_id]['contacts_by_roles'])
            )
        );

        return $this->m_cache['receivers'][$p_notification_id];
    } //function

    /**
     * Fetches receivers from database based on assigned contacts.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array Object identifiers (integers)
     */
    public function get_receivers_by_contacts($p_notification_id)
    {
        $this->m_log->debug('Fetching receivers from database based on assigned contacts...');

        $l_notification = $this->get_notification($p_notification_id);

        if (count($l_notification) === 0 || !isset($l_notification['contacts']))
        {
            return [];
        } //if

        return $this->get_contacts($l_notification['contacts']);
    } //function

    /**
     * Fetches receivers from database based on assigned contacts.
     *
     * @param int $p_contact_id Assigned contact identifier
     *
     * @return array Object identifiers (integers)
     */
    public function get_contacts($p_contact_id)
    {
        assert('is_int($p_contact_id)');

        $l_query = 'SELECT * FROM isys_contact_2_isys_obj ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_contact_2_isys_obj__isys_obj__id ' . 'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE isys_contact_2_isys_obj__isys_contact__id = ' . $this->convert_sql_id(
                $p_contact_id
            ) . ';';

        return $this->retrieve($l_query)
            ->__as_array();
    } //if

    /**
     * Fetches unit data from database.
     *
     * @param int  $p_id            (optional) Select unit identifier. If not set (default),
     *                              all units will be selected.
     * @param bool $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                              false.
     * @param bool $p_as_result_set (optional) Returns fetched data as result
     *                              set. Defaults to false.
     *
     * @return mixed Associative array or result set (isys_component_dao_result)
     */
    public function get_unit($p_id = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->fetch_properties('units', $p_id, $p_raw, $p_as_result_set);
    } //function

    /**
     * Fetches unit parameters data from database.
     *
     * @param string $p_table Table name
     *
     * @return array
     */
    public function get_unit_parameters($p_table)
    {
        assert('is_string($p_table)');

        if (isset($this->m_cache['unit_parameters']) && array_key_exists($p_table, $this->m_cache['unit_parameters']))
        {
            return $this->m_cache['unit_parameters'][$p_table];
        } //if

        $l_query = 'SELECT * ' . 'FROM ' . $p_table . ' WHERE ' . $p_table . '__status = \'' . C__RECORD_STATUS__NORMAL . '\';';

        $this->m_cache['unit_parameters'][$p_table] = $this->retrieve($l_query)
            ->__as_array();

        return $this->m_cache['unit_parameters'][$p_table];
    } //function

    /**
     * Fetches reports from system database.
     *
     * @global isys_component_database $g_comp_database_system
     *
     * @return array
     */
    public function get_all_reports()
    {
        global $g_comp_database_system;

        if (isset($this->m_cache['reports']))
        {
            return $this->m_cache['reports'];
        } //if

        $l_module_report_dao = new isys_report_dao($g_comp_database_system);

        $this->m_cache['reports'] = $l_module_report_dao->get_reports()
            ->__as_array();

        return $this->m_cache['reports'];
    } //function

    /**
     * Fetches notification roles from database.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array
     */
    public function get_roles($p_notification_id)
    {
        assert('is_int($p_notification_id) && $p_notification_id >= 0');

        if (is_array($this->m_cache['notification_roles']) && isset($this->m_cache['notification_roles'][$p_notification_id]))
        {
            return $this->m_cache['notification_roles'][$p_notification_id];
        } //if

        $l_query = 'SELECT * FROM ' . $this->m_tables['notification_roles'] . ' INNER JOIN ' . $this->m_tables['roles'] . ' ON ' . $this->m_tables['roles'] . '__id = ' . $this->m_tables['notification_roles'] . '__' . $this->m_tables['roles'] . '__id WHERE ' . $this->m_tables['notification_roles'] . '__' . $this->m_tables['notifications'] . '__id = ' . $this->convert_sql_id(
                $p_notification_id
            ) . ';';

        $this->m_cache['notification_roles'][$p_notification_id] = $this->retrieve($l_query)
            ->__as_array();

        return $this->m_cache['notification_roles'][$p_notification_id];
    } //function

    /**
     * Deletes an existing entity from database.
     *
     * @param string $p_property_type       Property type (e. g. 'notifications')
     * @param int    $p_entity              Entity identifier
     * @param bool   $p_use_notification_id (optional) Use notification identifier
     *                                      instead of entity identifier. Note, there is not always a notification
     *                                      identifier given! Defaults to false.
     */
    public function delete($p_property_type, $p_entity, $p_use_notification_id = false)
    {
        assert('is_string($p_property_type)');
        assert('is_int($p_entity)');
        assert('is_bool($p_use_notification_id)');

        $l_types = $this->get_property_types();

        if (!array_key_exists($p_property_type, $l_types))
        {
            throw new isys_exception_general(
                sprintf(
                    'Failed to delete entity "%s" because of unknown property type "%s"',
                    $p_entity,
                    $p_property_type
                )
            );
        } //if

        $l_notification = $this->m_tables[$p_property_type];
        if ($p_use_notification_id && $p_property_type !== 'notifications')
        {
            $l_notification .= '__' . $this->m_tables['notifications'];
        } //if

        $l_query = 'DELETE FROM ' . $this->m_tables[$p_property_type] . ' WHERE ' . $l_notification . '__id = ' . $this->convert_sql_id($p_entity) . ';';

        if (!$this->update($l_query))
        {
            throw new isys_exception_general(
                sprintf(
                    'Failed to delete entity "%s" for property type "%s" because an unexpected database error occured.',
                    $p_entity,
                    $p_property_type
                )
            );
        } //if
    } //function

    /**
     * Fetches all notification data from database.
     *
     * @return array
     */
    public function get_data()
    {
        return $this->get_notification();
    } //function

    /**
     * Adds or updates one or more roles to a notification.
     *
     * @param int   $p_notification_id Notification identifier
     * @param mixed $p_roles           One or more user roles formatted as a comma
     *                                 separated string of numerics, a JSON array or numerics, or an array of
     *                                 numerics
     */
    public function add_roles($p_notification_id, $p_roles)
    {
        $l_roles = [];

        if (isys_format_json::is_json_array($p_roles))
        {
            $l_roles = isys_format_json::decode($p_roles, true);
        }
        else if (is_string($p_roles))
        {
            $l_roles = explode(',', $p_roles);
        }
        else if (is_array($p_roles))
        {
            $l_roles = $p_roles;
        }
        else
        {
            throw new isys_exception_general(
                'Failed to add roles because format is invalid.'
            );
        } //if

        foreach ($l_roles as $l_role)
        {
            assert('is_numeric($l_role) && $l_role >= 0');
        } //foreach

        // Delete existing notification roles:

        $l_query = 'DELETE FROM ' . $this->m_tables['notification_roles'] . ' WHERE ' . $this->m_tables['notification_roles'] . '__' . $this->m_tables['notifications'] . '__id = ' . $this->convert_sql_id(
                $p_notification_id
            ) . ';';

        if (!$this->update($l_query))
        {
            throw new isys_exception_general(
                'Failed to clean up notification roles because an unexpected database error occured.'
            );
        } //if

        // No "new" roles? We've finished.
        if (count($l_roles) === 0)
        {
            return;
        } //if

        // Add "new" roles:

        $l_inserts = [];

        foreach ($l_roles as $l_role)
        {
            $l_inserts[] = '(' . $this->convert_sql_id($p_notification_id) . ', ' . $this->convert_sql_id($l_role) . ')';
        } //foreach

        $l_query = 'INSERT INTO ' . $this->m_tables['notification_roles'] . ' (`' . $this->m_tables['notification_roles'] . '__' . $this->m_tables['notifications'] . '__id`, `' . $this->m_tables['notification_roles'] . '__' . $this->m_tables['roles'] . '__id`) VALUES ' . implode(
                ', ',
                $l_inserts
            ) . ';';

        if (!$this->update($l_query))
        {
            throw new isys_exception_general(
                'Failed to add notification roles because an unexpected database error occured.'
            );
        } //if
    } //function

    /**
     * Fetches roles from database.
     *
     * @return array
     */
    public function get_all_roles()
    {
        if (isset($this->m_cache['roles']))
        {
            return $this->m_cache['roles'];
        } //if

        $l_query = 'SELECT ' . $this->m_tables['roles'] . '__id, ' . $this->m_tables['roles'] . '__title FROM ' . $this->m_tables['roles'] . ' WHERE ' . $this->m_tables['roles'] . '__status = \'' . C__RECORD_STATUS__NORMAL . '\';';

        $this->m_cache['roles'] = $this->retrieve($l_query)
            ->__as_array();

        return $this->m_cache['roles'];
    } //function

    /**
     * Adds or updates one or more contacts assigned to a notification.
     *
     * Notice: This is just a helper method for creating/updating a
     * notification. You have to insert/update manually this notification with
     * the return value of this method.
     *
     * @param int   $p_notification_id (optional) Notification identifier. If not
     *                                 set a new reference will be created. Defaults to null
     * @param mixed $p_contacts        One or more user contact identifiers formatted
     *                                 as a comma separated string or numerics, a JSON array or numerics, or an
     *                                 array of numerics
     */
    public function add_contacts($p_notification_id, $p_contacts)
    {
        assert('is_int($p_notification_id)');

        $l_property_type = 'notifications';
        $l_properties    = $this->get_properties($l_property_type);

        $l_notification_data   = $this->get_notification($p_notification_id);
        $l_existing_contact_id = $l_notification_data['contacts'];

        $l_dao_ref = new isys_contact_dao_reference($this->m_db);

        $l_contact_id = null;

        if (is_array($p_contacts) && count($p_contacts) === 0)
        {
            $p_contacts = null;
        } //if

        if ($p_contacts === null && $l_existing_contact_id === null)
        {
            // Everthing is fine.
            return;
        }
        else if ($p_contacts === null && $l_existing_contact_id !== null)
        {
            // Remove contacts:
            if ($l_dao_ref->delete($l_existing_contact_id) === false)
            {
                throw new isys_exception_general('Failed to remove old contacts.');
            } //if
        }
        else
        {
            $l_contact_id = $l_dao_ref->ref_contact(
                $p_contacts,
                $l_existing_contact_id
            );

            if ($l_contact_id === false)
            {
                throw new isys_exception_general('Failed to add contacts.');
            } //if
        } //if

        // Update contact field for this notification:

        $l_query = 'UPDATE ' . $this->m_tables[$l_property_type] . ' SET `' . $this->m_tables[$l_property_type] . '__' . $this->m_tables['contacts'] . '__id` = ' . $this->convert_sql_id(
                $l_contact_id
            ) . ' WHERE `' . $l_properties['id'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . '` = ' . $this->convert_sql_id($p_notification_id) . ';';

        if (!$this->update($l_query))
        {
            throw new isys_exception_general(
                'Failed to add contacts because an unexpected database error occured.'
            );
        } //if
    } //function

    /**
     * Adds domains for a notification. Existing ones will be replaced.
     *
     * @param int   $p_notification_id Notification identifier
     * @param array $p_domains         (optional) Associative array with domain types as
     *                                 keys and the selected entities (indexed array of identifiers) as values.
     *                                 Defaults to null (all existing domains will be deleted).
     */
    public function add_domains($p_notification_id, $p_domains = null)
    {
        assert('is_int($p_notification_id)');

        $l_property_type = 'notification_domains';

        // Truncate existing domain entities:
        $this->delete($l_property_type, $p_notification_id, true);

        $l_inserts = [];

        if (isset($p_domains))
        {
            assert('is_array($p_domains)');

            foreach ($p_domains as $l_domain => $l_entities)
            {
                if (is_array($l_entities))
                {
                    assert('is_array($l_entities)');

                    $l_insert = [
                        'notifications' => $p_notification_id,
                        'objects'       => 'NULL',
                        'object_types'  => 'NULL',
                        'reports'       => 'NULL'
                    ];

                    foreach ($l_entities as $l_entity)
                    {
                        assert('is_int($l_entity)');
                        $l_insert[$l_domain] = $this->convert_sql_id($l_entity);
                        $l_inserts[]         = '(' . implode(', ', $l_insert) . ')';
                        $l_insert[$l_domain] = 'NULL';
                    } //foreach
                }
            } //foreach
        } //if

        if (count($l_inserts) == 0)
        {
            return;
        } //if

        $l_query = 'INSERT INTO ' . $this->m_tables[$l_property_type] . ' (`' . $this->m_tables[$l_property_type] . '__' . $this->m_tables['notifications'] . '__id`, `' . $this->m_tables[$l_property_type] . '__' . $this->m_tables['objects'] . '__id`, `' . $this->m_tables[$l_property_type] . '__' . $this->m_tables['object_types'] . '__id`, `' . $this->m_tables[$l_property_type] . '__' . $this->m_tables['reports'] . '__id`' . ') VALUES ' . implode(
                ', ',
                $l_inserts
            ) . ';';

        if (!$this->update($l_query))
        {
            throw new isys_exception_general(
                'Failed to add notification domains because an unexpected database error occured.'
            );
        } //if
    } //function

    /**
     * Fetches notification data from database.
     *
     * @param   integer $p_id            (optional) Select notification template identifier. If not set (default), all notification templates will be selected.
     * @param   boolean $p_raw           (optional) Returns unformatted ouput. Defaults to false.
     * @param   boolean $p_as_result_set (optional) Returns fetched data as result set. Defaults to false.
     *
     * @return  mixed  Associative array or result set (isys_component_dao_result)
     */
    public function get_template($p_id = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->fetch_properties('notification_templates', $p_id, $p_raw, $p_as_result_set);
    } //function

    /**
     * Provides information about properties.
     */
    protected function build_properties()
    {
        $l_provides_all = self::C__PROPERTY__PROVIDES__VIEW + self::C__PROPERTY__PROVIDES__CREATE + self::C__PROPERTY__PROVIDES__SAVE + self::C__PROPERTY__PROVIDES__DELETE;

        $this->m_properties = [
            'notifications'          => [
                'id'             => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_ID',
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
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'title'          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TITLE'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__title',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__NOTIFICATIONS__NOTIFICATION_TITLE',
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
                'status'         => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_STATUS'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__status',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => 1
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__NOTIFICATIONS__NOTIFICATION_STATUS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'min_range' => 0
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'threshold'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_THRESHOLD'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__threshold',
                        C__PROPERTY__DATA__TYPE  => 'float',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__NOTIFICATIONS__NOTIFICATION_THRESHOLD',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_FLOAT
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'threshold_unit' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_THRESHOLD_UNIT'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__threshold_unit',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_THRESHOLD_UNIT',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all & ~self::C__PROPERTY__PROVIDES__DELETE
                ],
                'type'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables['notifications'] . '__' . $this->m_tables['notification_types'] . '__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            $this->m_tables['notification_types'],
                            $this->m_tables['notification_types'] . '__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_TYPE_ID',
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
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all & ~self::C__PROPERTY__PROVIDES__DELETE
                ],
                'contacts'       => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__CONTACTS'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__CONTACTS__DESCRIPTION'),
                        'group'                        => 'LC__NOTIFICATIONS__RECEIVERS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables['notifications'] . '__' . $this->m_tables['contacts'] . '__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            $this->m_tables['contacts'],
                            $this->m_tables['contacts'] . '__id'
                        ],
                        'default'                     => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__CONTACTS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__POPUP,
                        C__PROPERTY__UI__PARAMS => [
                            'title'                  => 'LC__BROWSER__TITLE__CONTACT',
                            'p_strPopupType'         => 'browser_object_ng',
                            'catFilter'              => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                            'multiselection'         => 'true',
                            'p_bReadonly'            => '1',
                            'p_image'                => 'true',
                            'p_strFormSubmit'        => '0',
                            'p_iSelectedTab'         => '1',
                            'p_iEnabledPreselection' => '1'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                        // @todo It's an array of unsigned integers
                        //                        C__PROPERTY__CHECK__VALIDATION => array(
                        //                            FILTER_CALLBACK,
                        //                            array('isys_helper', 'filter_json_array_of_ids')
                        //                        )
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'limit'          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__NOTIFICATION_LIMIT'),
                        'group'                        => 'common_settings',
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__NOTIFICATION_LIMIT__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__limit',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [],
                        'default'                => 1
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__NOTIFICATIONS__NOTIFICATION_LIMIT',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            []
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'count'          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__NOTIFICATION_COUNT'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__NOTIFICATION_COUNT__DESCRIPTION'),
                        'group'                        => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__count',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            // @todo Unsigned includes 0, doesn't it? Why is convert_sql_id turning it into NULL?!?!
                            //'unsigned'
                        ],
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_COUNT',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bReadonly' => 'true'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'last_run'       => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_LAST_RUN'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__last_run',
                        C__PROPERTY__DATA__TYPE  => 'datetime',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_LAST_RUN',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__POPUP,
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'calendar',
                            'p_bTime'        => '1',
                            'p_bReadonly'    => 'true'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_date'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'description'    => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_DESCRIPTION'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notifications'] . '__description',
                        C__PROPERTY__DATA__TYPE  => 'text',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_DESCRIPTION',
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
                ]
            ],
            'notification_types'     => [
                'id'           => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_ID')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ],
                'title'        => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_TITLE')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__title',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ]
                ],
                'description'  => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_DESCRIPTION')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__description',
                        C__PROPERTY__DATA__TYPE  => 'text'
                    ]
                ],
                'status'       => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_STATUS')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__status',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ],
                'callback'     => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_CALLBACK')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__callback',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ]
                ],
                'domains'      => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_DOMAINS')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__domains',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ],
                'unit'         => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_UNIT')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__' . $this->m_tables['units'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ],
                'default_unit' => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_DEFAULT_UNIT')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_types'] . '__default_unit',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ]
            ],
            'notification_domains'   => [
                'id'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__DOMAIN_ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_domains'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__DOMAIN_ID',
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
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'notification' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_domains'] . '__' . $this->m_tables['notifications'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'objects'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__DOMAIN_OBJECTS'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__DOMAIN_OBJECTS__DESCRIPTION'),
                        'group'                        => 'LC__NOTIFICATIONS__NOTIFICATION_DOMAINS'

                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_domains'] . '__' . $this->m_tables['objects'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__DOMAIN_OBJECTS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__POPUP,
                        C__PROPERTY__UI__PARAMS => [
                            'title'                  => 'LC__BROWSER__TITLE__CONTACT',
                            'p_strPopupType'         => 'browser_object_ng',
                            'multiselection'         => 'true',
                            'p_bReadonly'            => '1',
                            'p_image'                => 'true',
                            'p_strFormSubmit'        => '0',
                            'p_iSelectedTab'         => '1',
                            'p_iEnabledPreselection' => '1',
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                        // @todo It's an array of unsigned integers
                        //                        C__PROPERTY__CHECK__VALIDATION => array(
                        //                            FILTER_CALLBACK,
                        //                            array(
                        //                                'options' => array('isys_helper', 'filter_list_of_ids')
                        //                            )
                        //                        )
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'object_types' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__DOMAIN_OBJECT_TYPES'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__DOMAIN_OBJECT_TYPES__DESCRIPTION'),
                        'group'                        => 'LC__NOTIFICATIONS__NOTIFICATION_DOMAINS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_domains'] . '__' . $this->m_tables['object_types'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__DOMAIN_OBJECT_TYPES',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist' => '1'
                        ],
                        'post'                  => 'C__NOTIFICATIONS__DOMAIN_OBJECT_TYPES__selected_values'
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
                'reports'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__DOMAIN_REPORTS'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__DOMAIN_REPORTS__DESCRIPTION'),
                        'group'                        => 'LC__NOTIFICATIONS__NOTIFICATION_DOMAINS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_domains'] . '__' . $this->m_tables['reports'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__DOMAIN_REPORTS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist' => '1'
                        ],
                        'post'                  => 'C__NOTIFICATIONS__DOMAIN_REPORTS__selected_values'
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
                ]
            ],
            'notification_roles'     => [
                'id'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__ROLE_ID'),
                        'group'                  => 'LC__NOTIFICATIONS__RECEIVERS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_roles'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'notification' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_ID'),
                        'group'                  => 'LC__NOTIFICATIONS__RECEIVERS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_roles'] . '__' . $this->m_tables['notifications'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'roles'        => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__ASSIGNED_ROLES'),
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__ASSIGNED_ROLES__DESCRIPTION'),
                        'group'                        => 'LC__NOTIFICATIONS__RECEIVERS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_roles'] . '__' . $this->m_tables['roles'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__ASSIGNED_ROLES',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist'  => '1',
                            'p_bDbFieldNN' => '1'
                        ],
                        'post'                  => 'C__NOTIFICATIONS__ASSIGNED_ROLES__selected_values'
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
                ]
            ],
            'notification_templates' => [
                'id'                => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__TEMPLATE_ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_templates'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__TEMPLATE_ID',
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
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'notification_type' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TYPE_ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables['notification_templates'] . '__' . $this->m_tables['notification_types'] . '__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            $this->m_tables['notification_types'],
                            $this->m_tables['notification_types'] . '__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_TYPE_ID',
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
                                'options' => ['min_range' => 0]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all & ~self::C__PROPERTY__PROVIDES__DELETE
                ],
                'locale'            => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_templates'] . '__locale',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_arData'     => serialize($this->get_locales())
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
                'subject'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_templates'] . '__subject',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => '%notifications__title%'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => []
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
                'text'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => _L('LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT'),
                        'group'                  => 'common_settings'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_templates'] . '__text',
                        C__PROPERTY__DATA__TYPE  => 'text'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXTAREA,
                        C__PROPERTY__UI__PARAMS => [
                            'p_nRows' => '10',
                            'p_nCols' => '55'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
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
                'report'            => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT'),
                        'group'                        => 'common_settings',
                        C__PROPERTY__INFO__DESCRIPTION => _L('LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['notification_templates'] . '__report',
                        C__PROPERTY__DATA__TYPE  => 'text'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__PROPERTY_SELECTOR,
                        C__PROPERTY__UI__PARAMS => [
                            'provide'  => C__PROPERTY__PROVIDES__REPORT,
                            'sortable' => true,
                            'grouping' => 0
                        ],
                        'post'                  => 'C__NOTIFICATIONS__DOMAIN_REPORTS__HIDDEN'
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_property_selector'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ]
            ],
            'units'                  => [
                'id'          => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__UNITS__ID')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['units'] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ],
                'title'       => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__UNITS__TITLE')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['units'] . '__title',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ]
                ],
                'description' => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__UNITS__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['units'] . '__description',
                        C__PROPERTY__DATA__TYPE  => 'text'
                    ]
                ],
                'table'       => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__UNITS__TABLE')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['units'] . '__table',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ]
                ],
                'default'     => [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE => _L('LC__UNITS__DEFAULT')
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables['units'] . '__default',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ]
                ]
            ]
        ];
    } //function

    /**
     * Helps getter methods to fetch data from database.
     *
     * @param string $p_property_type Property type (e. g. 'notifications')
     * @param int    $p_id            (optional) Select special identifier. If not set
     *                                (default), all identifiers will be selected.
     * @param bool   $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                                false.
     * @param bool   $p_as_result_set (optional) Returns fetched data as result
     *                                set. Defaults to false.
     *
     * @return mixed Associative array or result set (isys_component_dao_result)
     */
    protected function fetch_properties($p_property_type, $p_id = null, $p_raw = false, $p_as_result_set = false)
    {
        assert('is_bool($p_raw)');

        if (isset($p_id))
        {
            assert('is_int($p_id) && $p_id >= 0');
        } //if

        if (!isset($this->m_cache[$p_property_type]) || (isset($p_id) && !isset($this->m_cache[$p_property_type][$p_id])))
        {
            $l_table = $this->m_tables[$p_property_type];

            $l_query = 'SELECT * FROM ' . $l_table;

            if (isset($p_id))
            {
                $l_query .= ' WHERE ' . $l_table . '__id = ' . $this->convert_sql_id($p_id);
            } //if

            $l_query .= ';';

            if ($p_as_result_set)
            {
                return $this->retrieve($l_query);
            }

            if (isset($p_id))
            {
                $this->m_cache[$p_property_type][$p_id] = $this->retrieve($l_query)
                    ->__to_array();
            }
            else
            {
                $l_result = $this->retrieve($l_query)
                    ->__as_array();

                foreach ($l_result as $l_entity)
                {
                    $this->m_cache[$p_property_type][$l_entity[$l_table . '__id']] = $l_entity;
                } //foreach
            } //if
        } //if

        if ($p_raw)
        {
            if (isset($p_id))
            {
                return $this->m_cache[$p_property_type][$p_id];
            } //if

            return $this->m_cache[$p_property_type];
        } //if

        if (isset($p_id))
        {
            return $this->map_properties($p_property_type, $this->m_cache[$p_property_type][$p_id]);
        } //if

        $l_result = [];

        foreach ($this->m_cache[$p_property_type] as $l_id => $l_values)
        {
            $l_result[$l_id] = $this->map_properties($p_property_type, $l_values);
        } //foreach

        return $l_result;
    } //function

    /**
     * Fetches receivers from database based on assigned roles.
     *
     * @param int $p_notification_id Notification identifier
     *
     * @return array Object identifiers (integers)
     */
    protected function get_receivers_by_roles($p_notification_id)
    {
        $this->m_log->debug('Fetching receivers from database based on assigned roles...');

        $l_roles   = $this->get_roles($p_notification_id);
        $l_objects = $this->get_objects($p_notification_id);

        if (count($l_roles) == 0 || count($l_objects) == 0)
        {
            $this->m_log->debug('There are no receivers.');

            return [];
        } //if

        $l_catg_contact_dao = new isys_cmdb_dao_category_g_contact($this->m_db);
        $l_contact_list     = $l_catg_contact_dao->get_table();

        $l_query_objects = [];
        foreach ($l_objects as $l_object)
        {
            $l_object_id       = intval($l_object[$this->m_tables['objects'] . '__id']);
            $l_query_objects[] = $l_contact_list . '__' . $this->m_tables['objects'] . '__id = ' . $this->convert_sql_id($l_object_id);
        } //foreach

        $l_query_roles = [];
        foreach ($l_roles as $l_role)
        {
            $l_query_roles[] = $l_contact_list . '__' . $this->m_tables['roles'] . '__id = ' . $this->convert_sql_id($l_role[$this->m_tables['roles'] . '__id']);
        } //foreach

        $l_query = 'SELECT ' . $this->m_tables['objects'] . '__id, ' . $this->m_tables['objects'] . '__' . $this->m_tables['object_types'] . '__id, GROUP_CONCAT(' . $l_contact_list . '__isys_obj__id) AS assigned_objects FROM ' . $l_contact_list . ' INNER JOIN isys_connection ON isys_connection__id = ' . $l_contact_list . '__isys_connection__id INNER JOIN ' . $this->m_tables['objects'] . ' ON ' . $this->m_tables['objects'] . '__id = isys_connection__' . $this->m_tables['objects'] . '__id WHERE ' . $l_contact_list . '__status = ' . $this->convert_sql_id(
                C__RECORD_STATUS__NORMAL
            ) . ' AND (' . implode(' OR ', $l_query_objects) . ') AND (' . implode(' OR ', $l_query_roles) . ') GROUP BY ' . $this->m_tables['objects'] . '__id';

        $l_contacts = $this->retrieve($l_query)
            ->__as_array();

        return $this->resolve_receivers_from_contacts($l_contacts);
    } // function

    /**
     * Resolves receivers from contacts.
     *
     * @param array $p_contacts Contacts
     *
     * @return array Object identifiers
     */
    protected function resolve_receivers_from_contacts($p_contacts)
    {
        $l_receivers = [];

        $l_num = count($p_contacts);
        if ($l_num == 0)
        {
            $this->m_log->debug('There are no contacts.');

            return $l_receivers;
        }
        else if ($l_num == 1)
        {
            $this->m_log->debug('There is 1 contact.');
        }
        else
        {
            $this->m_log->debug(sprintf('There are %s contacts', $l_num));
        } //if

        $l_organizations = [];
        $l_groups        = [];

        foreach ($p_contacts as $l_contact)
        {
            // Switch between organizations, persons, and person groups:
            switch ($l_contact[$this->m_tables['objects'] . '__' . $this->m_tables['object_types'] . '__id'])
            {
                case C__OBJTYPE__ORGANIZATION:
                    $l_organizations[] = $l_contact[$this->m_tables['objects'] . '__id'];
                    break;
                case C__OBJTYPE__PERSON:
                    // Add person directly to receivers:
                    $l_receivers[] = $l_contact[$this->m_tables['objects'] . '__id'];
                    if (isset($l_contact['assigned_objects']))
                    {
                        self::$m_receivers_roles_object_mapping[$l_contact[$this->m_tables['objects'] . '__id']] = explode(',', $l_contact['assigned_objects']);
                    } // if
                    break;
                case C__OBJTYPE__PERSON_GROUP:
                    $l_groups[] = $l_contact[$this->m_tables['objects'] . '__id'];
                    break;
            } //switch
        } //foreach

        // Fetch organization assigned persons:
        if (count($l_organizations) > 0)
        {
            $l_assignments = [];

            foreach ($l_organizations as $l_organization)
            {
                $l_assignments[] = 'isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($l_organization);
            } //foreach

            $l_query = 'SELECT isys_cats_person_list__isys_obj__id ' . 'FROM isys_cats_person_list ' . 'INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id ' . 'INNER JOIN isys_cats_organization_list ON isys_connection__isys_obj__id = isys_cats_organization_list__isys_obj__id ' . 'WHERE ' . implode(
                    ' OR ',
                    $l_assignments
                ) . ';';

            $l_persons = $this->retrieve($l_query)
                ->__as_array();

            foreach ($l_persons as $l_person)
            {
                $l_receivers[] = intval(
                    $l_person['isys_cats_person_list__isys_obj__id']
                );
            } //foreach
        } //if

        // Fetch group assigned persons:
        if (count($l_groups) > 0)
        {
            $l_assignments = [];

            foreach ($l_groups as $l_group)
            {
                $l_assignments[] = 'isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($l_group);
            } //foreach

            $l_query = 'SELECT isys_person_2_group__isys_obj__id__person FROM ' . 'isys_person_2_group WHERE ' . implode(' OR ', $l_assignments) . ';';

            $l_persons = $this->retrieve($l_query)
                ->__as_array();

            foreach ($l_persons as $l_person)
            {
                $l_receivers[] = intval(
                    $l_person['isys_person_2_group__isys_obj__id__person']
                );
            } //foreach
        } //if

        return array_unique($l_receivers);
    } //function

    /**
     * Constructor.
     *
     * @param  isys_component_database $p_db  Database component
     * @param  isys_log                $p_log Logger
     */
    public function __construct(isys_component_database $p_db, isys_log $p_log)
    {
        parent::__construct($p_db);
        $this->m_log = $p_log;
    } // function
} // class