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
 * DAO: specific category for applications with assigned objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_application_assigned_obj extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'application_assigned_obj';
    /**
     * Field which holds the connected object id field if defined.
     *
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_catg_application_list__isys_obj__id';
    /**
     * Name of property which should be used as identifier.
     *
     * @var string
     */
    protected $m_entry_identifier = 'object';
    /**
     * Should we generically handle a relation creation via property C__PROPERTY__DATA__RELATION_TYPE.
     *
     * @var  boolean
     */
    protected $m_has_relation = true;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;
    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';
    /**
     * Category's title.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_application_list';
    /**
     * Template name, because it re-uses another one.
     *
     * @var  string
     */
    protected $m_tpl = 'catg__application.tpl';

    /**
     * Create connector (for multivalue).
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    } // function

    /**
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $this->m_object_id;

        if ($p_obj_id !== null)
        {
            $l_obj_id = $p_obj_id;
        } // if

        $l_sql = 'SELECT COUNT(isys_catg_application_list__id) AS count
			FROM isys_catg_application_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_application_list__isys_obj__id
			INNER JOIN isys_connection ON isys_catg_application_list__isys_connection__id = isys_connection__id
			WHERE TRUE';

        if ($l_obj_id > 0)
        {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        } // if

        $l_sql .= ' AND isys_catg_application_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return (int) $this->retrieve($l_sql)
            ->get_row_value('count');
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao = new isys_cmdb_dao_category_g_application($this->m_db);

        if ($p_obj_id > 0) $l_condition = ' AND isys_connection__isys_obj__id = ' . $l_dao->convert_sql_id($p_obj_id);
        else $l_condition = '';

        return $l_dao->get_data($p_cats_list_id, null, $l_condition, $p_filter, $p_status);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        $l_return = $l_application_properties = [
            'object'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__INSTALLED_ON',
                        C__PROPERTY__INFO__DESCRIPTION => 'Installed on',
                        C__PROPERTY__INFO__BACKWARD    => true
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_application_list__isys_obj__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => new isys_callback(
                            [
                                'isys_cmdb_dao_category_s_application_assigned_obj',
                                'callback_property_relation_type_handler'
                            ]
                        ),
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_s_application_assigned_obj',
                                'callback_property_relation_handler'
                            ], [
                                'isys_cmdb_dao_category_s_application_assigned_obj',
                                true
                            ]
                        )
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__APPLICATION_OBJ_APPLICATION',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER => "C__CATG__APPLICATION"
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ]
                ]
            ),
            'application_type'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__APPLICATION_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Application type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_catg_application_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_application_type',
                            'isys_catg_application_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_catg_application_type',
                            'p_bDbFieldNN' => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => true,
                        C__PROPERTY__PROVIDES__IMPORT     => true,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__REPORT     => true,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true
                    ]
                ]
            ),
            'application_priority'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__APPLICATION_PRIORITY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Application priority'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_catg_application_priority__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_application_priority',
                            'isys_catg_application_priority__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_PRIORITY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_catg_application_priority',
                            'p_bDbFieldNN' => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => true,
                        C__PROPERTY__PROVIDES__IMPORT     => true,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__REPORT     => true,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true
                    ]
                ]
            ),
            'assigned_license'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE',
                        C__PROPERTY__INFO__DESCRIPTION => 'The assigned licence for the application'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_cats_lic_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_lic_list',
                            'isys_cats_lic_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__LIC_ASSIGN__LICENSE',
                        C__PROPERTY__UI__PARAMS => [
                            'catFilter'        => 'C__CATS__LICENCE',
                            'secondSelection'  => true,
                            'secondList'       => 'isys_cmdb_dao_category_s_lic::object_browser',
                            'secondListFormat' => 'isys_cmdb_dao_category_s_lic::format_selection',
                            'readOnly'         => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'application_license'
                        ]
                    ]
                ]
            ),
            'assigned_database_schema' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_SCHEMA',
                        C__PROPERTY__INFO__DESCRIPTION => 'The assigned database schema for the application'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_catg_relation_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_database_access_list',
                            'isys_cats_database_access_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_DATABASE_SCHEMATA',
                        C__PROPERTY__UI__PARAMS => [
                            'typeFilter' => 'C__OBJTYPE__DATABASE_SCHEMA',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'application_database_schema'
                        ]
                    ]
                ]
            ),
            'assigned_it_service'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IT_SERVICE',
                        C__PROPERTY__INFO__DESCRIPTION => 'The assigned it service for the application'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_catg_relation_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_its_components_list',
                            'isys_catg_its_components_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_IT_SERVICE',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__SERVICE',
                            'p_strSelectedID'                           => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_application',
                                    'callback_property_assigned_it_service'
                                ]
                            ),
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'application_it_service'
                        ]
                    ]
                ]
            ),
            'assigned_variant'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
                        C__PROPERTY__INFO__DESCRIPTION => 'The assigned variant for the application assignment'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_cats_app_variant_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_app_variant_list',
                            'isys_cats_app_variant_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_VARIANT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_application_assigned_obj',
                                    'callback_property_assigned_variant'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'application_assigned_obj_property_assigned_variant'
                        ]
                    ]
                ]
            ),
            'bequest_nagios_services'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Bequest nagios services'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_application_list__bequest_nagios_services'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => 1
                        ],
                        C__PROPERTY__UI__DEFAULT => 1
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'description'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_application_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__APPLICATION
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            )
        ];

        return $l_return;
    } // function

    /**
     * @param   $p_objects
     * @param   $p_direction
     * @param   $p_table
     *
     * @return  boolean
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        $l_dao = new isys_cmdb_dao_category_g_application($this->m_db);

        switch ($_POST[C__GET__NAVMODE])
        {
            case C__NAVMODE__ARCHIVE:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;

            case C__NAVMODE__DELETE:
                $l_status = C__RECORD_STATUS__DELETED;
                break;

            case C__NAVMODE__RECYCLE:
                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED)
                {
                    $l_status = C__RECORD_STATUS__NORMAL;
                }
                else if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED)
                {
                    $l_status = C__RECORD_STATUS__ARCHIVED;
                } // if
                break;

            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($p_objects))
                {
                    foreach ($p_objects AS $l_cat_id)
                    {
                        $l_dao->delete($l_cat_id);
                    } // foreach
                } // if
                return true;
                break;
        } // switch

        foreach ($p_objects AS $l_cat_id)
        {
            $l_dao->set_status($l_cat_id, $l_status);
        } // foreach

        return true;
    } // function

    /**
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['object'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_license'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_it_service'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_variant'][C__DATA__VALUE],
                        $p_category_data['properties']['bequest_nagios_services'][C__DATA__VALUE],
                        $p_category_data['properties']['application_type'][C__DATA__VALUE],
                        $p_category_data['properties']['application_priority'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_version'][C__DATA__VALUE]
                    );

                    if ($p_category_data['data_id'] > 0)
                    {
                        $l_indicator = true;
                    } // if
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['object'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_license'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_it_service'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_variant'][C__DATA__VALUE],
                        $p_category_data['properties']['bequest_nagios_services'][C__DATA__VALUE],
                        $p_category_data['properties']['application_type'][C__DATA__VALUE],
                        $p_category_data['properties']['application_priority'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_version'][C__DATA__VALUE]
                    );
                    break;
            } // switch
        } // if

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Callback method which returns the relation type because application assignment has two relation types:
     * - C__RELATION_TYPE__OPERATION_SYSTEM
     * - C__RELATION_TYPE__SOFTWARE
     *
     * @param   isys_request $p_request
     *
     * @return  integer
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_type_handler(isys_request $p_request)
    {
        $l_dao  = isys_cmdb_dao_category_s_application_assigned_obj::instance($this->m_db);
        $l_data = $l_dao->get_data_by_id($p_request->get_category_data_id())
            ->get_row();

        switch ($l_data['isys_catg_application_list__isys_catg_application_type__id'])
        {
            default:
            case C__CATG__APPLICATION_TYPE__SOFTWARE:
                return C__RELATION_TYPE__SOFTWARE;

            case C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM:
                return C__RELATION_TYPE__OPERATION_SYSTEM;
        } // switch
    } // function

    /**
     * Callback method for property assigned_variant.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_assigned_variant(isys_request $p_request)
    {
        global $g_comp_database;

        return serialize(
            isys_cmdb_dao_category_s_application_assigned_obj::instance($g_comp_database)
                ->get_variants($p_request->get_object_id())
        );
    } // function

    /**
     * Callback method for property versions.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_assigned_version(isys_request $p_request)
    {
        global $g_comp_database;

        return serialize(
            isys_cmdb_dao_category_s_application_assigned_obj::instance($g_comp_database)
                ->get_versions($p_request->get_object_id())
        );
    } // function

    /**
     * Gets all entries from category variant from the given object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_variants($p_obj_id)
    {
        if (is_null($p_obj_id))
        {
            return [];
        } // if

        global $g_comp_database;

        $l_dao  = isys_cmdb_dao_category_s_application_variant::instance($g_comp_database);
        $l_res  = $l_dao->get_data(null, $p_obj_id);
        $l_data = [];

        while ($l_row = $l_res->get_row())
        {
            $l_data[$l_row['isys_cats_app_variant_list__id']] = $l_row['isys_cats_app_variant_list__variant'] . ($l_row['isys_cats_app_variant_list__title'] != '' ? ' (' . $l_row['isys_cats_app_variant_list__title'] . ')' : '');
        } // while

        return $l_data;
    } // function

    /**
     * Gets all entries from category version from the given object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_versions($p_obj_id)
    {
        if (is_null($p_obj_id))
        {
            return [];
        } // if

        global $g_comp_database;

        $l_data = [];
        $l_res  = isys_cmdb_dao_category_g_version::instance($g_comp_database)
            ->get_data(null, $p_obj_id);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_data[$l_row['isys_catg_version_list__id']] = $l_row['isys_catg_version_list__title'];
            } // while
        } // if

        return $l_data;
    } // function

    /**
     * Save global category application element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     * @return  int|null
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == C__CATG__OVERVIEW && isys_glob_get_param(
                C__GET__NAVMODE
            ) == C__NAVMODE__SAVE
        )
        {
            $p_create = true;
        } // if

        if ($_POST['C__CATG__APPLICATION_TYPE'] != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
        {
            $_POST['C__CATG__APPLICATION_PRIORITY'] = null;
        } // if

        if ($p_create)
        {
            // Overview page and no input was given
            if (isys_glob_get_param(C__CMDB__GET__CATG) == C__CATG__OVERVIEW && empty($_POST['C__CATS__APPLICATION_OBJ_APPLICATION__HIDDEN']))
            {
                return null;
            } // if

            $l_applications = $_POST['C__CATS__APPLICATION_OBJ_APPLICATION__HIDDEN'];

            if (isys_format_json::is_json_array($l_applications))
            {
                $l_applications = isys_format_json::decode($l_applications);
            } // if

            if (!is_array($l_applications))
            {
                $l_applications = [$l_applications];
            } // if

            foreach ($l_applications as $l_application)
            {
                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $l_application,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $_POST["C__CATG__LIC_ASSIGN__LICENSE__HIDDEN"],
                    $_POST["C__CATG__APPLICATION_DATABASE_SCHEMATA__HIDDEN"],
                    $_POST["C__CATG__APPLICATION_IT_SERVICE__HIDDEN"],
                    $_POST["C__CATG__APPLICATION_VARIANT__VARIANT"],
                    $_POST['C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES'],
                    $_POST['C__CATG__APPLICATION_TYPE'],
                    $_POST['C__CATG__APPLICATION_PRIORITY'],
                    $_POST['C__CATG__APPLICATION_VERSION']
                );

                $this->m_strLogbookSQL = $this->get_last_query();

                if ($l_id)
                {
                    $l_catdata['isys_catg_application_list__id'] = $l_id;
                    $l_bRet                                      = true;
                    $p_cat_level                                 = null;
                }
                else
                {
                    throw new isys_exception_dao("Could not create category element application");
                } // if
            } // foreach
        }
        else
        {
            $l_catdata         = $this->get_result()
                ->get_row();
            $p_intOldRecStatus = $l_catdata["isys_catg_application_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_application_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATS__APPLICATION_OBJ_APPLICATION__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__LIC_ASSIGN__LICENSE__HIDDEN"],
                $_POST["C__CATG__APPLICATION_DATABASE_SCHEMATA__HIDDEN"],
                $_POST["C__CATG__APPLICATION_IT_SERVICE__HIDDEN"],
                $_POST["C__CATG__APPLICATION_VARIANT__VARIANT"],
                $_POST['C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES'],
                $_POST['C__CATG__APPLICATION_TYPE'],
                $_POST['C__CATG__APPLICATION_PRIORITY'],
                $_POST['C__CATG__APPLICATION_VERSION']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        if ($p_create) return $l_catdata["isys_catg_application_list__id"];

        return $l_bRet == true ? null : $l_intErrorCode;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     * @param   integer $p_licence
     * @param   integer $p_database_schemata_obj
     * @param   mixed   $p_it_service_obj
     * @param   integer $p_variant
     * @param   integer $p_bequest_nagios_services
     * @param   integer $p_type
     * @param   integer $p_priority
     *
     * @return  null
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID, $p_description, $p_licence, $p_database_schemata_obj, $p_it_service_obj, $p_variant = null, $p_bequest_nagios_services = null, $p_type = null, $p_priority = null, $p_version = null)
    {
        if ($p_type === null)
        {
            $p_type = C__CATG__APPLICATION_TYPE__SOFTWARE;
        } // if

        if ($p_type != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
        {
            $p_priority = null;
        } // if

        $p_it_service_obj = (is_array($p_it_service_obj)) ? $p_it_service_obj : trim($p_it_service_obj);

        if (isys_format_json::is_json_array($p_it_service_obj))
        {
            $p_it_service_obj = isys_format_json::decode($p_it_service_obj);
        } // if

        $l_old_data   = $this->get_data($p_cat_level)
            ->__to_array();
        $l_app_obj_id = $l_old_data['isys_connection__isys_obj__id'];

        // Update software assignment
        $l_strSql = "UPDATE isys_catg_application_list SET " . "isys_catg_application_list__isys_obj__id = " . $this->convert_sql_id(
                $p_connectedObjID
            ) . ", " . "isys_catg_application_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_application_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . ", " . "isys_catg_application_list__isys_cats_app_variant_list__id = " . $this->convert_sql_id(
                $p_variant
            ) . ", " . "isys_catg_application_list__isys_cats_lic_list__id = " . $this->convert_sql_id(
                $p_licence
            ) . ", " . "isys_catg_application_list__bequest_nagios_services = " . $this->convert_sql_boolean(
                $p_bequest_nagios_services
            ) . ", " . "isys_catg_application_list__isys_catg_application_type__id = " . $this->convert_sql_id(
                $p_type
            ) . ", " . "isys_catg_application_list__isys_catg_application_priority__id = " . $this->convert_sql_id(
                $p_priority
            ) . ", " . "isys_catg_application_list__isys_catg_version_list__id = " . $this->convert_sql_id(
                $p_version
            ) . " " . "WHERE isys_catg_application_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update())
        {
            // Handle relation
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_data         = $this->get_data($p_cat_level)
                ->__to_array();

            $l_relation_dao->handle_relation(
                $p_cat_level,
                "isys_catg_application_list",
                ($p_type == C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM ? C__RELATION_TYPE__OPERATION_SYSTEM : C__RELATION_TYPE__SOFTWARE),
                $l_data["isys_catg_application_list__isys_catg_relation_list__id"],
                $p_connectedObjID,
                $l_app_obj_id
            );

            if ($p_connectedObjID > 0)
            {
                $l_data = $this->get_data($l_data["isys_catg_application_list__id"])
                    ->__to_array();

                if ($l_data["isys_catg_application_list__isys_catg_relation_list__id"] != "")
                {
                    $l_rel_data        = $l_relation_dao->get_data($l_data["isys_catg_application_list__isys_catg_relation_list__id"])
                        ->__to_array();
                    $l_dao_dbms_access = isys_cmdb_dao_category_s_database_access::instance($this->get_database_component());
                    $l_dao_its_comp    = isys_cmdb_dao_category_g_it_service_components::instance($this->get_database_component());

                    if (is_numeric($p_database_schemata_obj) && $p_database_schemata_obj > 0)
                    {
                        $l_dbms_res = $l_dao_dbms_access->get_data(
                            null,
                            null,
                            "AND isys_connection__isys_obj__id = " . $l_dao_dbms_access->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                            null,
                            C__RECORD_STATUS__NORMAL
                        );
                        if ($l_dbms_res->num_rows() < 1)
                        {
                            $l_dao_dbms_access->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                        }
                        else
                        {
                            if ($l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"]))
                            {
                                $l_dao_dbms_access->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                            } // if
                        } // if
                    }
                    else
                    {
                        $l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                    } // if

                    $l_assigned_it_services = array_flip(
                        isys_cmdb_dao_category_g_application::instance($this->m_db)
                            ->get_assigned_it_services($l_data["isys_catg_application_list__isys_catg_relation_list__id"])
                    );

                    if (is_array($p_it_service_obj) && count($p_it_service_obj))
                    {
                        foreach ($p_it_service_obj as $l_it_service)
                        {
                            $l_it_service_res = $l_dao_its_comp->get_data(
                                null,
                                $l_it_service,
                                "AND isys_connection__isys_obj__id = " . $l_dao_its_comp->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                                null,
                                C__RECORD_STATUS__NORMAL
                            );

                            if ($l_it_service_res->num_rows() < 1)
                            {
                                $l_dao_its_comp->create($l_it_service, C__RECORD_STATUS__NORMAL, $l_rel_data["isys_catg_relation_list__isys_obj__id"], "");
                            }
                            else
                            {
                                unset($l_assigned_it_services[$l_it_service]);
                            } // if
                        } // foreach
                    } // if

                    if (count($l_assigned_it_services) > 0)
                    {
                        foreach ($l_assigned_it_services AS $l_it_serv_obj_id => $l_dummy)
                        {
                            $l_dao_its_comp->remove_component($l_it_serv_obj_id, $l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                        } // foreach
                    } // if
                } // if
            } // if

            return true;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Create method.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     * @param   integer $p_licence
     * @param   integer $p_database_schemata_obj
     * @param   integer $p_it_service_obj
     * @param   integer $p_variant
     * @param   integer $p_bequest_nagios_services
     * @param   integer $p_type
     * @param   integer $p_priority
     *
     * @return  mixed
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description, $p_licence = null, $p_database_schemata_obj = null, $p_it_service_obj = null, $p_variant = null, $p_bequest_nagios_services = 1, $p_type = null, $p_priority = null, $p_version = null)
    {
        if ($p_type === null)
        {
            $p_type = C__CATG__APPLICATION_TYPE__SOFTWARE;
        } // if

        if ($p_type != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
        {
            $p_priority = null;
        } // if

        $l_connection = isys_cmdb_dao_connection::instance($this->m_db);

        $l_sql = "INSERT INTO isys_catg_application_list SET
			isys_catg_application_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_objID)) . ",
			isys_catg_application_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID) . ",
			isys_catg_application_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_application_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_application_list__isys_cats_app_variant_list__id = " . $this->convert_sql_id($p_variant) . ",
			isys_catg_application_list__isys_cats_lic_list__id = " . $this->convert_sql_id($p_licence) . ",
			isys_catg_application_list__bequest_nagios_services = " . $this->convert_sql_boolean($p_bequest_nagios_services) . ",
			isys_catg_application_list__isys_catg_application_type__id = " . $this->convert_sql_id($p_type) . ",
			isys_catg_application_list__isys_catg_version_list__id = " . $this->convert_sql_id($p_version) . ",
			isys_catg_application_list__isys_catg_application_priority__id = " . $this->convert_sql_id($p_priority) . ";";

        if ($this->update($l_sql) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            // Handle software relation.
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_relation_dao->handle_relation(
                $l_last_id,
                "isys_catg_application_list",
                ($p_type == C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM ? C__RELATION_TYPE__OPERATION_SYSTEM : C__RELATION_TYPE__SOFTWARE),
                null,
                $p_connectedObjID,
                $p_objID
            );

            if ($p_connectedObjID > 0)
            {
                $l_data = $this->get_data($l_last_id)
                    ->get_row();

                if ($l_data["isys_catg_application_list__isys_catg_relation_list__id"] != "")
                {
                    $l_rel_data = $l_relation_dao->get_data($l_data["isys_catg_application_list__isys_catg_relation_list__id"])
                        ->get_row();

                    if (is_numeric($p_database_schemata_obj) && $p_database_schemata_obj > 0)
                    {
                        isys_cmdb_dao_category_s_database_access::instance($this->get_database_component())
                            ->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                    } // if

                    // Handle IT-Service
                    if (isys_format_json::is_json_array($p_it_service_obj))
                    {
                        $p_it_service_obj = isys_format_json::decode($p_it_service_obj);
                    } // if

                    if (is_array($p_it_service_obj) && count($p_it_service_obj) > 0)
                    {
                        $l_dao_its_comp = isys_cmdb_dao_category_g_it_service_components::instance($this->get_database_component());

                        foreach ($p_it_service_obj AS $l_it_serv_obj_id)
                        {
                            $l_dao_its_comp->create($l_it_serv_obj_id, C__RECORD_STATUS__NORMAL, $l_rel_data["isys_catg_relation_list__isys_obj__id"], "");
                        } // foreach
                    } // if
                } // if
            } // if

            return $l_last_id;
        }
        else
        {
            return false;
        } // if
    } // function
} // class