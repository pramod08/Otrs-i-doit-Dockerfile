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
 * DAO: global category for service
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_service extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'service';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = false;

    /**
     * Callback method for the service alias field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_service_alias(isys_request $p_request)
    {
        $l_cat_list = [];
        $l_res      = $this->get_service_alias();

        while ($l_row = $l_res->get_row())
        {
            $l_cat_list[] = [
                "caption" => $l_row['isys_service_alias__title'],
                "value"   => $l_row['isys_service_alias__id']
            ];
        } // while

        return $l_cat_list;
    } // function

    /**
     * Gets all existing service aliase with a normal status.
     *
     * @param   string $p_filter
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_service_alias($p_filter = '')
    {
        return $this->retrieve(
            'SELECT * FROM isys_service_alias WHERE isys_service_alias__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ' . $p_filter . ';'
        );
    } // function

    /**
     * Gets all assigned service aliase
     *
     * @param integer $p_obj_id
     * @param integer $p_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_assigned_service_aliase($p_obj_id = null, $p_id = null)
    {
        if (empty($p_obj_id) && empty($p_id))
        {
            return false;
        } // if

        $l_sql = 'SELECT srv_alias.isys_service_alias__id, srv_alias.isys_service_alias__title
			FROM isys_catg_service_list_2_isys_service_alias AS main
			INNER JOIN isys_service_alias srv_alias ON main.isys_service_alias__id = srv_alias.isys_service_alias__id ';

        $l_condition = '';

        if ($p_obj_id > 0)
        {
            $l_condition = ' WHERE main.isys_catg_service_list__id = (SELECT isys_catg_service_list__id FROM isys_catg_service_list WHERE isys_catg_service_list__isys_obj__id = ' . $this->convert_sql_id(
                    $p_obj_id
                ) . ')';
        } // if

        if ($p_id > 0)
        {
            $l_condition = ' WHERE main.isys_catg_service_list__id = ' . $this->convert_sql_id($p_id);
        } // if

        return $this->retrieve($l_sql . $l_condition . ';');
    } // function

    /**
     * Remove assigned service alias connections
     *
     * @param null $p_obj_id
     * @param null $p_id
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function clear_assigned_service_aliase($p_obj_id = null, $p_id = null)
    {
        if (empty($p_obj_id) && empty($p_id))
        {
            return false;
        } // if

        $l_condition = '';

        if ($p_obj_id > 0)
        {
            $l_condition = ' isys_catg_service_list__id = (SELECT isys_catg_service_list__id FROM isys_catg_service_list WHERE isys_catg_service_list__isys_obj__id = ' . $this->convert_sql_id(
                    $p_obj_id
                ) . ')';
        } // if

        if ($p_id > 0)
        {
            $l_condition = ' isys_catg_service_list__id = ' . $this->convert_sql_id($p_id);
        } // if

        $l_sql = 'DELETE FROM isys_catg_service_list_2_isys_service_alias WHERE ' . $l_condition;

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Save category entry.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_service_list__status"];

        $l_list_id = $l_catdata["isys_catg_service_list__id"];

        if (empty($l_list_id))
        {
            $l_list_id = $this->create_connector("isys_catg_service_list");
        } // if

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST["C__CMDB__CATG__SERVICE__TYPE"],
            $_POST["C__CMDB__CATG__SERVICE__CATEGORY"],
            $_POST["C__CMDB__CATG__SERVICE__ACTIVE"],
            $_POST["C__CMDB__CATG__SERVICE__BUSINESS_UNIT"],
            $_POST['C__CMDB__CATG__SERVICE__ALIAS'],
            $_POST["C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_INTERN"],
            $_POST["C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_EXTERN"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            $_POST['C__CMDB__CATG__SERVICE__SERVICE_NUMBER']
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    } // function

    /**
     * Save action.
     *
     * @param  integer $p_id
     * @param  integer $p_status
     * @param  integer $p_service_type
     * @param  integer $p_service_category
     * @param  integer $p_active
     * @param  integer $p_business_unit
     * @param  array   $p_service_aliase
     * @param  string  $p_srv_descr_intern
     * @param  string  $p_srv_descr_extern
     * @param  string  $p_description
     * @param  string  $p_service_number
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function save($p_id, $p_status = C__RECORD_STATUS__NORMAL, $p_service_type = null, $p_service_category = null, $p_active = null, $p_business_unit = null, $p_service_aliase = null, $p_srv_descr_intern = null, $p_srv_descr_extern = null, $p_description = null, $p_service_number = null)
    {
        $l_update = 'UPDATE isys_catg_service_list SET ' . 'isys_catg_service_list__service_number = ' . $this->convert_sql_text(
                $p_service_number
            ) . ', ' . 'isys_catg_service_list__active = ' . $this->convert_sql_int(
                $p_active
            ) . ', ' . 'isys_catg_service_list__isys_service_type__id = ' . $this->convert_sql_id(
                $p_service_type
            ) . ', ' . 'isys_catg_service_list__isys_service_category__id = ' . $this->convert_sql_id(
                $p_service_category
            ) . ', ' . 'isys_catg_service_list__isys_business_unit__id = ' . $this->convert_sql_id(
                $p_business_unit
            ) . ', ' . 'isys_catg_service_list__service_description_intern = ' . $this->convert_sql_text(
                $p_srv_descr_intern
            ) . ', ' . 'isys_catg_service_list__service_description_extern = ' . $this->convert_sql_text(
                $p_srv_descr_extern
            ) . ', ' . 'isys_catg_service_list__status = ' . $this->convert_sql_int($p_status) . ', ' . 'isys_catg_service_list__description = ' . $this->convert_sql_text(
                $p_description
            ) . ' WHERE isys_catg_service_list__id = ' . $this->convert_sql_id($p_id);

        $l_assigned_aliase_res = $this->get_assigned_service_aliase(null, $p_id);

        while ($l_row = $l_assigned_aliase_res->get_row())
        {
            $l_assigned_aliase[$l_row['isys_service_alias__id']] = true;
        } // while

        $this->update($l_update);

        $this->clear_assigned_service_aliase(null, $p_id);

        if (is_array($p_service_aliase))
        {
            if (!empty($p_service_aliase[0]))
            {
                $l_values = '';
                foreach ($p_service_aliase AS $l_alias_id)
                {
                    $l_values .= ' (' . $this->convert_sql_id($p_id) . ', ' . $this->convert_sql_id($l_alias_id) . '),';
                } // foreach

                $l_insert = 'INSERT INTO isys_catg_service_list_2_isys_service_alias (isys_catg_service_list__id, isys_service_alias__id) VALUES ' . rtrim($l_values, ',');
                $this->update($l_insert);
            } // if
        } // if

        return $this->apply_update();
    } // function

    /**
     * Returns how many entries exists. The folder always returns 1.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == C__CATG__SERVICE)
        {
            return 1;
        }
        else
        {
            return parent::get_count($p_obj_id);
        } // if
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'service_number'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__SERVICE_NUMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Service number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__service_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__SERVICE__SERVICE_NUMBER'
                    ]
                ]
            ),
            'type'                       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Service type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_service_list__isys_service_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_service_type',
                            'isys_service_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__SERVICE__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_service_type'
                        ]
                    ]
                ]
            ),
            'category'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__CATEGORY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Service category'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_service_list__isys_service_category__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_service_category',
                            'isys_service_category__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__SERVICE__CATEGORY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_service_category'
                        ]
                    ]
                ]
            ),
            'business_unit'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__BUSINESS_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Business unit'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_service_list__isys_business_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_business_unit',
                            'isys_business_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__SERVICE__BUSINESS_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_business_unit'
                        ]
                    ]
                ]
            ),
            'service_description_intern' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__DESCRIPTION_INTERN',
                        C__PROPERTY__INFO__DESCRIPTION => 'Internal service description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__service_description_intern',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_INTERN'
                    ]
                ]
            ),
            'service_description_extern' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__DESCRIPTION_EXTERN',
                        C__PROPERTY__INFO__DESCRIPTION => 'External service description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__service_description_extern',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_EXTERN'
                    ]
                ]
            ),
            'service_alias'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::multiselect(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__ALIASE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Aliase'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_service_list__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'srv_alias',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_catg_service_list_2_isys_service_alias',
                            'isys_catg_service_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__SERVICE__ALIAS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_service_alias',
                            'placeholder'  => _L('LC__CMDB__CATG__SERVICE__ALIASE'),
                            'p_onComplete' => "idoit.callbackManager.triggerCallback('cmdb-catg-service-alias-update', selected);",
                            'multiselect'  => true
                            //'p_arData' => new isys_callback(array('isys_cmdb_dao_category_g_service', 'callback_property_service_alias'))
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_multiselect'
                        ]
                    ]
                ]
            ),
            'active'                     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__ACTIVE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Active'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__active'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CMDB__CATG__SERVICE__ACTIVE',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => 1
                        ],
                        // refs #4904
                        C__PROPERTY__UI__DEFAULT => 1
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'description'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__description',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__SERVICE
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE || empty($p_category_data['data_id']))
            {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_service_list', $p_object_id);
            } // if

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                $l_success = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['category'][C__DATA__VALUE],
                    $p_category_data['properties']['active'][C__DATA__VALUE],
                    $p_category_data['properties']['business_unit'][C__DATA__VALUE],
                    $p_category_data['properties']['service_alias'][C__DATA__VALUE],
                    $p_category_data['properties']['service_description_intern'][C__DATA__VALUE],
                    $p_category_data['properties']['service_description_intern'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['service_number'][C__DATA__VALUE]
                );

                if ($l_success)
                {
                    return $p_category_data['data_id'];
                } // if
            } // if
        } // if

        return false;
    } // function
} // class