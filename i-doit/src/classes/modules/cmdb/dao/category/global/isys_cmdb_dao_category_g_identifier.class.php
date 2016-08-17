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
 * DAO: global category for custom identifiers
 *
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @author        Selcuk Kekec <skekec@i-doit.com>
 * @author        Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright     synetics GmbH
 * @license       http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_identifier extends isys_cmdb_dao_category_global
{
    /**
     * Member variable for identifier key
     *
     * @var null
     */
    private static $m_identifier_key = null;
    /**
     * Member variable for identifier type
     *
     * @var null
     */
    private static $m_identifier_type = null;
    /**
     * Cache all objects which have no entry in the identifier category
     *
     * @var null
     */
    private static $m_missing_identifier = [];
    /**
     * @var array
     */
    private static $m_objects_cache = [];
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'identifier';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'type';
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Setter for member variable $m_identifier_type
     *
     * @param $p_value
     */
    public static function set_identifier_type($p_value)
    {
        self::$m_identifier_type = $p_value;
    } // function

    /**
     * Setter for member variable $m_identifier_key
     *
     * @param $p_value
     */
    public static function set_identifier_key($p_value)
    {
        self::$m_identifier_key = $p_value;
    } // function

    public static function get_identifier_key()
    {
        return self::$m_identifier_key;
    } // function

    public static function get_identifier_type()
    {
        return self::$m_identifier_type;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author      Selcuk Kekec <skekec@i-doit.com>
     */
    protected function properties()
    {
        return [
            'key'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__KEY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Key'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_identifier_list__key'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__IDENTIFIER__KEY'
                    ]
                ]
            ),
            'value'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__VALUE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Value'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_identifier_list__value'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__IDENTIFIER__VALUE'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__MANDATORY => false
                    ]
                ]
            ),
            'last_edited'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::datetime(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__LAST_EDITED',
                        C__PROPERTY__INFO__DESCRIPTION => 'Value',
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD    => 'isys_catg_identifier_list__datetime',
                        C__PROPERTY__DATA__READONLY => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__LAST_EDITED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bReadonly' => true,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__CHECK => false
                ]
            ),
            'type'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_identifier_list__isys_catg_identifier_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_identifier_type',
                            'isys_catg_identifier_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_identifier_type'
                        ]
                    ]
                ]
            ),
            'group'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__GROUP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Group'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_identifier_list__group'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__IDENTIFIER__GROUP'
                    ]
                ]
            ),
            'last_scan'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::datetime(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__LAST_SCAN',
                        C__PROPERTY__INFO__DESCRIPTION => 'Last scan',
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD    => 'isys_catg_identifier_list__last_scan',
                        C__PROPERTY__DATA__READONLY => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__LAST_SCAN',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bReadonly' => true,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__CHECK    => false
                ]
            ),
            'last_updated' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__LAST_UPDATED',
                        C__PROPERTY__INFO__DESCRIPTION => 'Last updated',
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD    => 'isys_obj__updated',
                        C__PROPERTY__DATA__READONLY => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__LAST_UPDATED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bReadonly' => true,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__CHECK => false
                ]
            ),
            'description'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_identifier_list__description',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__IDENTIFIER
                    ]
                ]
            )
        ];
    } // function

    /**
     * Get the object id by identifier
     *
     * @param $p_device_id
     *
     * @return bool|mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_object_id_by_identifer($p_key)
    {
        return (isset(self::$m_objects_cache[$p_key]) ? self::$m_objects_cache[$p_key] : false);
    } // function

    /**
     * Add identifier to the object cache
     *
     * @param $p_object_id
     * @param $p_device_id
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function set_object_id_by_identifier($p_object_id, $p_key)
    {
        self::$m_objects_cache[$p_key] = $p_object_id;
    } // function

    /**
     * Get cached objects by identifier
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_cached_objects()
    {
        return (is_array(self::$m_objects_cache)) ? self::$m_objects_cache : [];
    } // function

    /**
     * Get cached objects which have no entry in the identifier category
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_missing_identifiers()
    {
        return self::$m_missing_identifier;
    } // function

    /**
     * Add new entry to the cached objects which have no entry in the identifier category
     *
     * @param $p_obj_id
     * @param $p_id
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function set_missing_identifiers($p_obj_id, $p_id)
    {
        self::$m_missing_identifier[$p_obj_id] = $p_id;
    } // function

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     */
    public function create($p_data)
    {
        // Set last_edited field
        $p_data['last_edited'] = date('Y-m-d H:i:s');

        return parent::create_data($p_data); // TODO: Change the autogenerated stub
    } // function

    /**
     * Get value by object id, type and key
     *
     * @param $p_obj_id
     * @param $p_type
     * @param $p_key
     *
     * @return bool|int
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_value($p_obj_id, $p_type, $p_key)
    {
        if (!($p_type = $this->check_identifier_type($p_type)))
        {
            return false;
        } // if

        $l_sql = 'SELECT isys_catg_identifier_list__value FROM isys_catg_identifier_list USE INDEX (identifier_universal)
          WHERE
          isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
          AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
          AND isys_catg_identifier_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
          AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_identifier_list__value');
    } // function

    /**
     * Get object id by type, key and value
     *
     * @param $p_type
     * @param $p_key
     * @param $p_value
     *
     * @return bool|mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_object_id_by_key_value($p_type, $p_key, $p_value)
    {
        if (!($p_type = $this->check_identifier_type($p_type)))
        {
            return false;
        } // if
        $l_sql = 'SELECT isys_catg_identifier_list__isys_obj__id FROM isys_catg_identifier_list USE INDEX (identifier_universal)
          WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_identifier_list__isys_obj__id');
    } // function

    /**
     * Get Object id and entry id by type, key and value
     *
     * @param $p_type
     * @param $p_key
     * @param $p_value
     *
     * @return bool|mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_identifier_by_key_value($p_type, $p_key, $p_value)
    {
        if (!($p_type = $this->check_identifier_type($p_type)))
        {
            return false;
        } // if
        $l_sql = 'SELECT CONCAT_WS(\'_\', isys_catg_identifier_list__isys_obj__id, isys_catg_identifier_list__id) AS identifier FROM isys_catg_identifier_list
          USE INDEX (identifier_universal)
          WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->retrieve($l_sql)
            ->get_row_value('identifier');
    } // function

    /**
     * Gets id by key value
     *
     * @param $p_type
     * @param $p_key
     * @param $p_value
     *
     * @return bool|mixed
     * @throws isys_exception_database
     */
    public function get_id_by_key_value($p_type, $p_key, $p_value)
    {
        if (!($p_type = $this->check_identifier_type($p_type)))
        {
            return false;
        } // if
        $l_sql = 'SELECT isys_catg_identifier_list__id FROM isys_catg_identifier_list
          USE INDEX (identifier_universal)
          WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_identifier_list__id');
    } // function

    /**
     * Get objects by type and key
     *
     * @param $p_type
     * @param $p_key
     *
     * @return bool|isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_objects_by_type_key($p_type, $p_key)
    {
        if (!($p_type = $this->check_identifier_type($p_type)))
        {
            return false;
        } // if

        // Using Index Key "identifier_unique_index"
        $l_sql = 'SELECT * FROM isys_catg_identifier_list
          USE INDEX (identifier_universal)
          WHERE
          isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
          AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
          AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->retrieve($l_sql);
    } // function

    /**
     * Wrapper function to get objects by type and key as array
     *
     * @param $p_type
     * @param $p_key
     *
     * @return array|bool
     */
    public function get_objects_by_type_key_as_array($p_type, $p_key, $p_obj_id_as_key = true)
    {
        $l_res = $this->get_objects_by_type_key($p_type, $p_key);
        if ($l_res)
        {
            $l_arr = [];
            while ($l_row = $l_res->get_row())
            {
                if ($p_obj_id_as_key)
                {
                    $l_arr[$l_row['isys_catg_identifier_list__isys_obj__id']] = $l_row['isys_catg_identifier_list__value'];
                }
                else
                {
                    $l_arr[$l_row['isys_catg_identifier_list__value']] = $l_row['isys_catg_identifier_list__isys_obj__id'];
                } // if
            } // while
            return $l_arr;
        } // if
        return false;
    } // function

    /**
     * @param        $p_obj_id
     * @param        $p_type
     * @param        $p_key
     * @param        $p_value
     * @param string $p_description
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function set_identifier($p_obj_id, $p_type, $p_key, $p_value, $p_description = '', $p_group = '', $p_scantime = '', $p_check_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT isys_catg_identifier_list__id FROM isys_catg_identifier_list
            WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int($p_check_status);

        if ($p_group != '')
        {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
        } // if

        $l_res = $this->retrieve($l_sql);
        if ($l_res->num_rows())
        {
            $l_id     = $l_res->get_row_value('isys_catg_identifier_list__id');
            $l_update = 'UPDATE isys_catg_identifier_list SET isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . ' ';
            if ($p_scantime != '')
            {
                $l_update .= ', isys_catg_identifier_list__last_scan = ' . $this->convert_sql_text($p_scantime) . ' ';
            } // if

            if ($p_description != '')
            {
                $l_update .= ', isys_catg_identifier_list__description = ' . $this->convert_sql_text($p_description) . ' ';
            } // if

            if ($p_group != '')
            {
                $l_update .= ', isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group) . ' ';
            } // if

            $l_update .= ', isys_catg_identifier_list__status = ' . $this->convert_sql_int(
                    C__RECORD_STATUS__NORMAL
                ) . ' WHERE isys_catg_identifier_list__id = ' . $this->convert_sql_id($l_id);
        }
        else
        {
            $l_update = 'INSERT IGNORE INTO isys_catg_identifier_list SET isys_catg_identifier_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
                isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . ',
                isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . ',
                isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . ',
                isys_catg_identifier_list__datetime = NOW(),
                isys_catg_identifier_list__description = ' . $this->convert_sql_text($p_description) . ',
                isys_catg_identifier_list__status = ' . C__RECORD_STATUS__NORMAL;

            if ($p_scantime != '')
            {
                $l_update .= ', isys_catg_identifier_list__last_scan = ' . $this->convert_sql_text($p_scantime);
            } // if

            if ($p_group != '')
            {
                $l_update .= ', isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
            } // if
        } // if

        return $this->update($l_update) && $this->apply_update();
    } // if

    /**
     * Clears identifiers by type, key or value if set.
     *
     * @param        $p_type
     * @param string $p_key
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function clear_identifiers($p_type, $p_key = null, $p_value = null, $p_group_name = null)
    {
        $l_sql = 'UPDATE isys_catg_identifier_list SET isys_catg_identifier_list__status = ' . $this->convert_sql_int(
                C__RECORD_STATUS__ARCHIVED
            ) . ' WHERE isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . ' ';

        if ($p_key !== null)
        {
            $l_sql .= ' AND isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key);
        } // if

        if ($p_value !== null)
        {
            $l_sql .= ' AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value);
        } // if

        if ($p_group_name !== null)
        {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group_name);
        }
        else
        {
            $l_sql .= ' AND (isys_catg_identifier_list__group IS NULL OR isys_catg_identifier_list__group = \'\')';
        }

        if ($this->update($l_sql) && $this->apply_update())
        {
            if ($p_value !== null)
            {
                if (isset(self::$m_objects_cache[$p_value]))
                {
                    unset(self::$m_objects_cache[$p_value]);
                } // if
            }
            else
            {
                self::$m_objects_cache = [];
            } // if
            return true;
        } // if
        return false;
    } // function

    /**
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function recover_identifiers($p_type, $p_key = null)
    {
        $l_sql = 'UPDATE isys_catg_identifier_list SET isys_catg_identifier_list__status = ' . $this->convert_sql_int(
                C__RECORD_STATUS__NORMAL
            ) . ' WHERE isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . ' ';

        if ($p_key !== null)
        {
            $l_sql .= ' AND isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key);
        } // if
        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Create object map by identifier
     *
     * @param $p_jdisc_server
     *
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function map_existing_objects_by_identifier($p_type, $p_key, $p_obj_id_as_array = false)
    {
        self::$m_objects_cache = $this->get_objects_by_type_key_as_array($p_type, $p_key, $p_obj_id_as_array);
    } // function

    /**
     * Sets mapping for the identifiers
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_mapping($p_value)
    {
        self::$m_objects_cache = $p_value;
    } // function

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding
     *                                      values as values.
     *
     * @return  boolean
     * @author      Selcuk Kekec <skekec@i-doit.com>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        return parent::save_data($p_category_data_id, $p_data);
    } // function

    /**
     * Private method which checks the identifier type
     *
     * @param $p_type
     *
     * @return bool|mixed
     */
    private function check_identifier_type($p_type)
    {
        if (!is_numeric($p_type))
        {
            if (defined($p_type))
            {
                $p_type = constant($p_type);
            }
            else
            {
                // Identifier type is not defined
                $p_type = false;
            } // if
        } // if
        return $p_type;
    } // function

} // class
