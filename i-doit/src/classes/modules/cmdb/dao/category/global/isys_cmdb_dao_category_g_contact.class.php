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
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: Global category for contacts
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_contact extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'contact';
    /**
     * Category constants for filtering.
     *
     * @var  array
     */
    protected $m_cats_filter = [
        'C__CATS__PERSON',
        'C__CATS__PERSON_GROUP',
        'C__CATS__ORGANIZATION'
    ];
    /**
     *
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';
    /**
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
     * Flag which defines if the category is only a list with an object browser.
     *
     * @var  boolean
     */
    protected $m_object_browser_category = true;
    /**
     * Property of the object browser
     *
     * @var  string
     */
    protected $m_object_browser_property = 'contact_object';
    /**
     * Field for the object id.
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_contact_list__isys_obj__id';
    /**
     * All object types which can be assigned as contact.
     *
     * @var  array
     */
    private $m_assignable_object_types = [];

    /**
     * Callback method which returns the relation type because contact assignment can have custom relation types.
     *
     * @param   isys_request $p_request
     *
     * @return  integer
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_type_handler(isys_request $p_request)
    {
        $l_relation_type_id = isys_cmdb_dao_category_g_contact::instance($this->m_db)
            ->get_data_by_id($p_request->get_category_data_id())
            ->get_row_value('isys_contact_tag__isys_relation_type__id');

        return ($l_relation_type_id > 0) ? $l_relation_type_id : C__RELATION_TYPE__USER;
    } // function

    /**
     * Callback method which returns the master and slave object for the relation.
     *
     * @param   isys_request $p_request
     * @param   array        $p_array
     *
     * @return  isys_array
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_handler(isys_request $p_request, $p_array = [])
    {
        $l_return = [];
        $l_dao    = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->database);
        $l_data   = $l_dao->get_data_by_id($p_request->get_category_data_id())
            ->get_row();

        if (isset($l_data[$l_dao->m_object_id_field]))
        {
            $l_master = $l_data[$l_dao->m_object_id_field];
            $l_slave  = $l_data[$l_dao->m_connected_object_id_field];

            if ($l_data['isys_contact_tag__isys_relation_type__id'] !== null)
            {
                $l_relation_default = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database)
                    ->get_relation_type($l_data['isys_contact_tag__isys_relation_type__id'])
                    ->get_row_value('isys_relation_type__default');

                if ((int) $l_relation_default === C__RELATION_DIRECTION__I_DEPEND_ON)
                {
                    $l_cache  = $l_master;
                    $l_master = $l_slave;
                    $l_slave  = $l_cache;
                } // switch
            } // if

            $l_return[C__RELATION_OBJECT__MASTER] = $l_master;
            $l_return[C__RELATION_OBJECT__SLAVE]  = $l_slave;
        } // if

        return $l_return;
    } // function

    /**
     * Method for retrieving the dynamic properties.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_person'        => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__PERSON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Persons'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_person'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_linked_person' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__PERSON_LINKED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Persons (linked)'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_person_linked'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Return Category Data
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
            FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_contact_list__isys_obj__id
            LEFT JOIN isys_contact_tag ON isys_catg_contact_list__isys_contact_tag__id = isys_contact_tag__id
            LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_cats_person_group_list ON isys_cats_person_group_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_cats_organization_list ON isys_cats_organization_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_catg_mail_addresses_list ON isys_connection__isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_contact_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        } // if

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_catg_contact_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Get entry identifier.
     *
     * @param   array $p_entry_data
     *
     * @return  string
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function get_entry_identifier($p_entry_data)
    {
        $l_data = $this->get_data($p_entry_data['isys_catg_contact_list__id'])
            ->get_row();

        if (is_array($l_data))
        {
            if (isset($l_data['isys_cats_person_list__id']))
            {
                return $l_data['isys_cats_person_list__first_name'] . ' ' . $l_data['isys_cats_person_list__last_name'];
            }
            else if ($l_data['isys_cats_person_group_list__id'])
            {
                return $l_data['isys_cats_person_group_list__title'];
            } // if
        } // if

        return parent::get_entry_identifier($p_entry_data); // TODO: Change the autogenerated stub
    } // function

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (isys_catg_contact_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'contact'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CONTACT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Contact'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_contact_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__CONTACT__CONTACT'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'catg_contact'
                        ]
                    ]
                ]
            ),
            'contact_object' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CONTACT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Contact object'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_contact_list__isys_connection__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_contact',
                                'callback_property_relation_type_handler'
                            ]
                        ),
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_contact',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_g_contact']
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_connection',
                            'isys_connection__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CONTACT__CONNECTED_OBJECT',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection' => false,
                            'catFilter'      => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                        C__PROPERTY__PROVIDES__REPORT     => true,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connection'
                        ]
                    ]
                ]
            ),
            'person'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__PERSON',
                        C__PROPERTY__INFO__DESCRIPTION => 'Person'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_cats_person_list__title',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_cats_person_list'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    ]
                ]
            ),
            'person_group'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__PERSON_GROUP',
                        C__PROPERTY__INFO__DESCRIPTION => 'Person group'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_cats_person_group_list__title',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_cats_person_group_list'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    ]
                ]
            ),
            'organization'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__ORGANISATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Organization'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_cats_organization_list__title',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_cats_organization_list'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    ]
                ]
            ),
            'primary'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_LIST__PRIMARY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Primary'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_contact_list__primary_contact'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CONTACT__PRIMARY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'role'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CONTACT_ROLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Role'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_contact_list__isys_contact_tag__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contact_tag',
                            'isys_contact_tag__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONTACT_TAG',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_contact_tag'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    ]
                ]
            ),
            'description'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_contact_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CONTACT
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
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
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            if (isset($p_category_data['properties']['role'][C__DATA__VALUE]) && $p_category_data['properties']['role'][C__DATA__VALUE] > 0)
            {
                // Because of ID-2643 We'll check if the used user role has a relation type. If not: use a default.
                $l_role_data = isys_factory_cmdb_dialog_dao::get_instance('isys_contact_tag', $this->m_db)
                    ->get_data($p_category_data['properties']['role'][C__DATA__VALUE]);

                if ($l_role_data['isys_contact_tag__isys_relation_type__id'] === null)
                {
                    $l_sql = 'UPDATE isys_contact_tag
						SET isys_contact_tag__isys_relation_type__id = ' . $this->convert_sql_id(C__RELATION_TYPE__USER) . '
						WHERE isys_contact_tag__id = ' . $this->convert_sql_id($l_role_data['isys_contact_tag__id']) . ';';

                    $this->update($l_sql) && $this->apply_update();
                } // if
            } // if

            $l_value['contact'] = $p_category_data['properties']['contact'][C__DATA__VALUE];

            if (is_array($l_value['contact']))
            {
                $l_contact = $l_value['contact'][0][C__DATA__VALUE];
            }
            else
            {
                $l_contact = $l_value['contact'];
            } // if

            if ($l_contact === null)
            {
                $l_value['contact_object'] = $p_category_data['properties']['contact_object'][C__DATA__VALUE];

                if ($l_value['contact_object'] !== null)
                {
                    $l_contact = $l_value['contact_object'];
                } // if
            } // if

            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        $p_category_data['data_id'] = $this->create(
                            $p_object_id,
                            $l_contact,
                            $p_category_data['properties']['role'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        $l_indicator = true;
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            $l_contact,
                            $p_category_data['properties']['role'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        $l_indicator = true;
                    } // if
                    break;
            } // switch

            // Primary:
            if (is_numeric($p_category_data['properties']['primary'][C__DATA__VALUE]))
            {
                switch ((int) $p_category_data['properties']['primary'][C__DATA__VALUE])
                {
                    case 0:
                        if ($this->reset_primary($p_object_id, $p_category_data['data_id']) === false)
                        {
                            $l_indicator = false;
                        } // if
                        break;
                    case 1:
                        if ($this->make_primary($p_object_id, $p_category_data['data_id']) === false)
                        {
                            $l_indicator = false;
                        } // if
                        break;
                } // switch
            } // if
        } // if

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Dynamic property handling for getting the assigned contacts.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_person(array $p_row)
    {
        global $g_comp_database;

        $l_return      = [];
        $l_contact_dao = isys_cmdb_dao_category_g_contact::instance($g_comp_database);
        $l_contact_res = $l_contact_dao->get_assigned_contacts($p_row['isys_obj__id'], C__RECORD_STATUS__NORMAL);

        if (count($l_contact_res) > 0)
        {
            while ($l_row = $l_contact_res->get_row())
            {
                $l_return[] = $l_row['isys_obj__title'] . ($l_contact_dao->is_primary($l_row['isys_catg_contact_list__id']) ? ' (' . _L(
                            'LC__CATG__CONTACT_LIST__PRIMARY'
                        ) . ')' : '');
            } // while
        }
        else
        {
            $l_return[] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        return implode(', ', $l_return);
    } // function

    /**
     * Dynamic property handling for getting the assigned contacts (linked).
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_person_linked(array $p_row)
    {
        global $g_comp_database;

        $l_return      = [];
        $l_contact_dao = isys_cmdb_dao_category_g_contact::instance($g_comp_database);
        $l_contact_res = $l_contact_dao->get_assigned_contacts($p_row['isys_obj__id'], C__RECORD_STATUS__NORMAL);

        $l_ajax_quickinfo = new isys_ajax_handler_quick_info();

        if (count($l_contact_res) > 0)
        {
            while ($l_row = $l_contact_res->get_row())
            {
                $l_return[] = $l_ajax_quickinfo->get_quick_info(
                    $l_row['isys_obj__id'],
                    $l_row['isys_obj__title'] . ($l_contact_dao->is_primary($l_row['isys_catg_contact_list__id']) ? ' (' . _L('LC__CATG__CONTACT_LIST__PRIMARY') . ')' : ''),
                    C__LINK__OBJECT
                );
            } // while
        }
        else
        {
            $l_return[] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        return implode(', ', $l_return);
    }

    /**
     * Get the primary contact
     *
     * @param int $p_primType
     * @param int $p_primID
     */
    public function contact_get_primary(&$p_primType, &$p_primID)
    {

        $l_dao_ref = new isys_contact_dao_reference($this->m_db);
        $l_catdata = $this->get_data(null, $this->m_object_id, "AND isys_catg_contact_list__primary_contact = 1");

        if ($l_catdata->num_rows() > 0)
        {
            $l_row = $l_catdata->get_row();

            if ($l_row["isys_connection__isys_obj__id"] > 0)
            {
                $p_primID   = $l_row["isys_connection__isys_obj__id"];
                $p_primType = $l_row["isys_obj__isys_obj_type__id"];

                $l_contact_info = $l_dao_ref->get_data_item_info($l_row["isys_connection__isys_obj__id"]);

                if (is_object($l_contact_info))
                {
                    return $l_contact_info->get_row();
                }
            }
        }

        return false;
    }


    // Removed: save_element() & isys_rs_system

    /**
     * Check if assigned object is assignable as a contact
     *
     * @param $p_obj_id
     *
     * @return bool
     * @author Van Quyen hoang <qhoang@i-doit.org>
     */
    public function is_object_assignable($p_obj_id)
    {
        $l_objtype_id = $this->get_objTypeID($p_obj_id);

        if (count($this->m_assignable_object_types) == 0)
        {
            foreach ($this->m_cats_filter AS $l_constant)
            {
                if (defined($l_constant))
                {
                    $this->m_assignable_object_types = array_merge($this->get_object_types_by_category(constant($l_constant), 's', false), $this->m_assignable_object_types);
                } // if
            } // foreach
        } // if

        return in_array($l_objtype_id, $this->m_assignable_object_types);
    } // function

    /**
     * Creates a contact assignment
     *
     * @param int $p_objID
     * @param int $p_connected_obj_id (Connected User, Group or Organisation)
     * @param int $p_role_id
     * @param int $p_status
     *
     * @return boolean
     */
    public function create($p_objID, $p_connected_obj_id, $p_role_id = null, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_connection    = new isys_cmdb_dao_connection($this->m_db);
        $l_connection_id = $l_connection->add_connection($p_connected_obj_id);

        /* Insert category record */
        $l_q = "INSERT INTO isys_catg_contact_list SET " . "isys_catg_contact_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . ", " . "isys_catg_contact_list__isys_connection__id = " . $this->convert_sql_id(
                $l_connection_id
            ) . ", " . "isys_catg_contact_list__isys_contact_tag__id = " . $this->convert_sql_id(
                $p_role_id
            ) . ", " . "isys_catg_contact_list__isys_contact_data_item_primary__id = " . $this->convert_sql_id(
                null
            ) . ", " . "isys_catg_contact_list__status = " . $this->convert_sql_id($p_status) . ';';

        $this->m_strLogbookSQL .= $l_q;

        $l_res = $this->update($l_q);
        if (!$l_res)
        {
            return -11;
        } // if

        $l_id = $this->get_last_insert_id();

        /* Create implicit relation */
        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        if ($p_role_id > 0)
        {
            $l_contact_tag_data = $this->get_contact_tag_data($p_role_id)
                ->get_row();
        }
        else
        {
            $l_contact_tag_data = null;
        } // if

        if (!empty($l_contact_tag_data) && !empty($l_contact_tag_data['isys_contact_tag__isys_relation_type__id']))
        {
            $l_relation_type_arr = $l_relation_dao->get_relation_type($l_contact_tag_data['isys_contact_tag__isys_relation_type__id'])
                ->__to_array();

            $l_relation_type = $l_contact_tag_data['isys_contact_tag__isys_relation_type__id'];
            switch ($l_relation_type_arr['isys_relation_type__default'])
            {
                case C__RELATION_DIRECTION__DEPENDS_ON_ME:
                    $l_slave  = $p_connected_obj_id;
                    $l_master = $p_objID;
                    break;
                case C__RELATION_DIRECTION__I_DEPEND_ON:
                default:
                    $l_slave  = $p_objID;
                    $l_master = $p_connected_obj_id;
                    break;
            } // switch

            $l_relation_dao->handle_relation($l_id, "isys_catg_contact_list", $l_relation_type, null, $l_master, $l_slave);
        }
        else
        {
            $l_relation_dao->handle_relation($l_id, "isys_catg_contact_list", C__RELATION_TYPE__USER, null, $p_objID, $p_connected_obj_id);
        } // if

        return $l_id;
    } // function

    /**
     * Saves a contact assignment
     *
     * @param   integer $p_cat_level
     * @param   integer $p_connected_obj_id
     * @param   integer $p_tag
     * @param   string  $p_description
     * @param   integer $p_record_status
     *
     * @return  boolean
     */
    public function save($p_cat_level, $p_connected_obj_id, $p_role_id = null, $p_description = null, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        // Contact should not be created without an contact object
        if ($p_connected_obj_id > 0)
        {
            $l_sql = "UPDATE isys_catg_contact_list
				INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
				SET
				isys_connection__isys_obj__id = " . $this->convert_sql_id(
                    $p_connected_obj_id
                ) . ", " . "isys_catg_contact_list__isys_contact_tag__id = " . $this->convert_sql_id(
                    $p_role_id
                ) . ", " . "isys_catg_contact_list__description = " . $this->convert_sql_text(
                    $p_description
                ) . ", " . "isys_catg_contact_list__status = " . $this->convert_sql_id($p_record_status) . "
				WHERE isys_catg_contact_list__id = " . $this->convert_sql_id($p_cat_level);

            $this->update($l_sql);
            if ($this->apply_update())
            {
                /* Create implicit relation */
                $l_data         = $this->get_data($p_cat_level)
                    ->__to_array();
                $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
                if ($p_role_id > 0)
                {
                    $l_contact_tag_data = $this->get_contact_tag_data($p_role_id)
                        ->get_row();
                }
                else
                {
                    $l_contact_tag_data = null;
                } // if
                if (!empty($l_contact_tag_data) && !empty($l_contact_tag_data['isys_contact_tag__isys_relation_type__id']))
                {
                    $l_relation_type_arr = $l_relation_dao->get_relation_type($l_contact_tag_data['isys_contact_tag__isys_relation_type__id'])
                        ->__to_array();
                    $l_relation_type     = $l_contact_tag_data['isys_contact_tag__isys_relation_type__id'];
                    switch ($l_relation_type_arr['isys_relation_type__default'])
                    {
                        case C__RELATION_DIRECTION__DEPENDS_ON_ME:
                            $l_slave  = $p_connected_obj_id;
                            $l_master = $l_data["isys_catg_contact_list__isys_obj__id"];
                            break;
                        case C__RELATION_DIRECTION__I_DEPEND_ON:
                        default:
                            $l_slave  = $l_data["isys_catg_contact_list__isys_obj__id"];
                            $l_master = $p_connected_obj_id;
                            break;
                    } // switch
                    $l_relation_dao->handle_relation(
                        $p_cat_level,
                        "isys_catg_contact_list",
                        $l_relation_type,
                        $l_data["isys_catg_contact_list__isys_catg_relation_list__id"],
                        $l_master,
                        $l_slave
                    );
                }
                else
                {
                    $l_relation_dao->handle_relation(
                        $p_cat_level,
                        "isys_catg_contact_list",
                        C__RELATION_TYPE__USER,
                        $l_data["isys_catg_contact_list__isys_catg_relation_list__id"],
                        $l_data["isys_catg_contact_list__isys_obj__id"],
                        $p_connected_obj_id
                    );
                } // if
                return true;
            }
            else
            {
                return false;
            } // if
        }
        else
        {
            // Remove entry because there is no contact object defined
            return $this->delete($p_cat_level);
        } // if
    } // function

    public function delete($p_id = null, $p_obj_id = null)
    {
        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_res = $this->get_data($p_id, $p_obj_id);

        while ($l_data = $l_res->get_row())
        {
            if ($l_data["isys_catg_contact_list__isys_catg_relation_list__id"] > 0 && !empty($l_data["isys_catg_contact_list__isys_catg_relation_list__id"]))
            {
                $l_relation_dao->delete_relation($l_data["isys_catg_contact_list__isys_catg_relation_list__id"]);
            } // if
        } // while

        $l_sql = "DELETE FROM isys_catg_contact_list WHERE TRUE";

        if ($p_id)
        {
            $l_sql .= " AND isys_catg_contact_list__id = " . $this->convert_sql_id($p_id);
        } // if

        if ($p_obj_id)
        {
            $l_sql .= " AND isys_catg_contact_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        } // if

        $this->m_strLogbookSQL .= $l_sql . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     *
     * @param   string $p_string
     *
     * @return  mixed|string
     * @throws  isys_exception_database
     */
    public function get_tag_id_by_string($p_string)
    {
        $l_res = $this->retrieve('SELECT isys_contact_tag__id FROM isys_contact_tag WHERE isys_contact_tag__title = ' . $this->convert_sql_text($p_string) . ';');

        if (count($l_res))
        {
            return $l_res->get_row_value('isys_contact_tag__id');
        }
        else
        {
            return "null";
        } // if
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_contact_tag_data($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_contact_tag WHERE TRUE';

        if (is_numeric($p_id))
        {
            $l_sql .= ' AND isys_contact_tag__id = ' . $this->convert_sql_id($p_id);
        }
        else if (is_string($p_id))
        {
            $l_sql .= ' AND isys_contact_tag__const = ' . $this->convert_sql_text($p_id);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Convert data item to primary.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_level
     *
     * @return  integer
     * @throws  isys_exception_dao
     */
    public function make_primary($p_obj_id, $p_cat_level)
    {
        $this->reset_primary($p_obj_id);

        $l_q = "UPDATE isys_catg_contact_list
			SET isys_catg_contact_list__primary_contact = '1'
			WHERE isys_catg_contact_list__id = " . $this->convert_sql_id($p_cat_level) . ';';

        $this->m_strLogbookSQL .= $l_q;

        return $this->update($l_q) && $this->apply_update($l_q);
    } // function

    /**
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_database
     */
    public function is_primary($p_cat_level)
    {
        $l_primary_contact = $this->retrieve(
            'SELECT isys_catg_contact_list__primary_contact FROM isys_catg_contact_list WHERE isys_catg_contact_list__id = ' . $this->convert_sql_id($p_cat_level) . ';'
        )
            ->get_row_value('isys_catg_contact_list__primary_contact');

        return ($l_primary_contact != 0);
    } // function

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function reset_primary($p_obj_id, $p_cat_level = null)
    {
        $l_sql = 'UPDATE isys_catg_contact_list SET
			isys_catg_contact_list__primary_contact = 0
			WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        if ($p_cat_level !== null && $p_cat_level > 0)
        {
            $l_sql .= ' AND isys_catg_contact_list__id = ' . $this->convert_sql_id($p_cat_level);
        } // if

        return $this->update($l_sql . ';') && $this->apply_update();
    } // function

    /**
     * @param int $p_cat_level
     * @param int $p_new_id
     *
     * @return NULL
     * @throws Exception
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_last_id      = null;
        $l_unassignable = [];
        $l_set_primary  = false;

        if (!is_null($p_objects))
        {
            // Select all items from the database-table for deleting them.
            $l_res = $this->get_assigned_contacts($p_object_id);

            if ($l_res->num_rows() == 0)
            {
                $l_set_primary = true;
            }

            /**
             * @desc Don't delete any contacts because multi assignments should be possible
             * @see  https://i-doit.atlassian.net/browse/ID-521
             * while ($l_row = $l_res->get_row())
             * {
             * //$l_existing[] = $l_row['isys_connection__isys_obj__id'];
             *
             * // Collect only items, which are not to be saved.
             * if (!in_array($l_row['isys_connection__isys_obj__id'], $l_objects))
             * {
             * // Collect items to delete, so we don't have to execute dozens of queries but only one.
             * $this->delete($l_row['isys_catg_contact_list__id'], $_GET[C__CMDB__GET__OBJECT]);
             * } // if
             * } // while
             */

            // Now insert new items.
            foreach ($p_objects as $l_object)
            {
                $l_assignable = $this->is_object_assignable($l_object);

                // But don't insert any items, that already exist!
                if ($l_assignable)
                {
                    if ($l_object > 0)
                    {
                        // Create the new items.
                        $l_last_id = $this->create($p_object_id, $l_object);
                        if ($l_set_primary)
                        {
                            $this->make_primary($p_object_id, $l_last_id);
                            $l_set_primary = false;
                        }
                    } // if
                }
                else
                {
                    $l_unassignable[] = $this->get_obj_name_by_id_as_string($l_object);
                }
            } // foreach
        } // if

        return $l_last_id;
    } // function

    /**
     * @param $p_list_id
     * @param $p_direction
     * @param $p_table
     */
    public function pre_rank($p_list_id, $p_direction, $p_table)
    {
        if ($this->is_primary($p_list_id))
        {
            $this->reset_primary($_GET[C__CMDB__GET__OBJECT]);
        } // if
    } // function

    /**
     * @param $p_list_id
     * @param $p_direction
     * @param $p_table
     */
    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_primary_element = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], " AND isys_catg_contact_list__primary_contact = 1", C__RECORD_STATUS__NORMAL)
            ->get_row();

        if (!$l_primary_element)
        {
            $l_rows = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, null, C__RECORD_STATUS__NORMAL);
            $l_num  = $l_rows->num_rows();

            if ($l_num)
            {
                $l_row = $l_rows->get_row();
                $this->make_primary($_GET[C__CMDB__GET__OBJECT], $l_row["isys_catg_contact_list__id"]);
            } // if
        } // if
    } // function

    /**
     * @param   integer $p_obj_id
     * @param   integer $p_catg_obj_id
     *
     * @return  boolean
     */
    public function check_contacts($p_obj_id, $p_catg_obj_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_catg_obj_id) . ';';

        return (count($this->retrieve($l_sql)) > 0);
    } // function

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_contacts_as_id_string($p_obj_id)
    {
        $l_sql = 'SELECT isys_connection__isys_obj__id FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
            WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        $l_res = $this->retrieve($l_sql);

        if (count($l_res))
        {
            $l_id_array = [];

            while ($l_row = $l_res->get_row())
            {
                $l_id_array[] = $l_row["isys_connection__isys_obj__id"];
            } // while

            return implode(',', $l_id_array);
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Do nothing otherwise we get an exception.
     *
     * @return  null
     */
    public function save_element()
    {
        return null;
    } // function

    /**
     * Updates the contact tag
     *
     * @param int $p_contact_id
     * @param int $p_contact_tag
     */
    public function save_contact_tag($p_catg_contact_id, $p_contact_tag)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.contact.beforeSaveTag', $this, $p_catg_contact_id, $p_contact_tag);
        $l_old_data = $this->get_data($p_catg_contact_id)
            ->__to_array();

        $l_query = "UPDATE isys_catg_contact_list " . "SET isys_catg_contact_list__isys_contact_tag__id = " . $this->convert_sql_id(
                $p_contact_tag
            ) . " " . "WHERE isys_catg_contact_list__id = " . $this->convert_sql_id($p_catg_contact_id) . ";";

        if ($this->update($l_query))
        {
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

            $l_data = $this->get_data($p_catg_contact_id)
                ->__to_array();

            // In case the relation type is not set
            if ($l_data['isys_contact_tag__isys_relation_type__id'] === null)
            {
                $this->update_contact_tag($p_catg_contact_id, null, C__RELATION_TYPE__USER);
                $l_data['isys_contact_tag__isys_relation_type__id'] = C__RELATION_TYPE__USER;
            } // if

            $l_relation_type_arr = $l_relation_dao->get_relation_type($l_data['isys_contact_tag__isys_relation_type__id'])
                ->__to_array();

            $l_relation_type = $l_data['isys_contact_tag__isys_relation_type__id'];
            switch ($l_relation_type_arr['isys_relation_type__default'])
            {
                case C__RELATION_DIRECTION__DEPENDS_ON_ME:
                    $l_slave  = $l_data["isys_connection__isys_obj__id"];
                    $l_master = $l_data["isys_catg_contact_list__isys_obj__id"];
                    break;
                case C__RELATION_DIRECTION__I_DEPEND_ON:
                default:
                    $l_slave  = $l_data["isys_catg_contact_list__isys_obj__id"];
                    $l_master = $l_data["isys_connection__isys_obj__id"];
                    break;
            }

            $l_relation_dao->handle_relation(
                $p_catg_contact_id,
                "isys_catg_contact_list",
                $l_relation_type,
                $l_data["isys_catg_contact_list__isys_catg_relation_list__id"],
                $l_master,
                $l_slave
            );

            // Get tags
            $l_tags = isys_factory_cmdb_dialog_dao::get_instance('isys_contact_tag', $this->m_db)
                ->get_data();

            // Build changes array
            $l_changes = [
                'isys_cmdb_dao_category_g_contact::role' => [
                    'from' => $l_tags[$l_old_data['isys_catg_contact_list__isys_contact_tag__id']]['title'],
                    'to'   => $l_tags[$p_contact_tag]['title']
                ]
            ];

            // Create logbook entry
            $l_logbook_dao = new isys_component_dao_logbook($this->m_db);

            $l_logbook_dao->set_entry(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                $this->get_last_query(),
                null,
                C__LOGBOOK__ALERT_LEVEL__0,
                $l_data['isys_obj__id'],
                $l_data['isys_obj__title'],
                $this->get_obj_type_name_by_obj_id($l_data['isys_obj__id']),
                'LC__CMDB__CATG__CONTACT',
                null,
                serialize($l_changes),
                _L('LC__CATG__CONTACT_HAS_BEEN_UPDATED'),
                null
            );

            return $this->apply_update();
        } // if

        return false;
    } // function

    /**
     * This method gets the assigned contacts by an object-id for the contact-browser.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   boolean $p_primary
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_contacts($p_obj_id, $p_status = null, $p_primary = false)
    {
        // Prepare SQL statement for retrieving contacts, assigned to a certain object.
        $l_sql = 'SELECT cl.isys_catg_contact_list__id, conn.isys_connection__isys_obj__id, obj.isys_obj__id, obj.isys_obj__title, obj.isys_obj__isys_obj_type__id, obj.isys_obj__sysid
            FROM isys_catg_contact_list AS cl
            LEFT JOIN isys_connection AS conn ON conn.isys_connection__id = cl.isys_catg_contact_list__isys_connection__id
            LEFT JOIN isys_obj AS obj ON obj.isys_obj__id = conn.isys_connection__isys_obj__id
            WHERE cl.isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_catg_contact_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        if ($p_primary)
        {
            $l_sql .= ' AND isys_catg_contact_list__primary_contact = 1';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for retrieving the contacts, assigned to a certain relation-ID defined in the table "isys_contact_2_isys_obj".
     *
     * @param   integer $p_rel_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_contacts_by_relation_id($p_rel_id)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__title FROM isys_contact_2_isys_obj AS c2o
            LEFT JOIN isys_obj AS o ON c2o.isys_contact_2_isys_obj__isys_obj__id = o.isys_obj__id
            WHERE c2o.isys_contact_2_isys_obj__isys_contact__id = ' . ($p_rel_id + 0) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Gets all internal contacts of an object given by its ID.
     *
     * @param   integer $p_objID
     * @param   boolean $p_only_primary
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function get_contacts_by_obj_id($p_objID, $p_only_primary = false)
    {
        $l_sql = 'SELECT isys_cats_person_list__id, isys_cats_person_list__first_name, isys_cats_person_list__last_name, isys_connection__isys_obj__id, isys_cats_person_list__isys_obj__id, isys_contact_tag__title
			FROM isys_cats_person_list
			INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_cats_person_list__isys_obj__id
			INNER JOIN isys_catg_contact_list ON isys_catg_contact_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_contact_tag ON isys_catg_contact_list__isys_contact_tag__id = isys_contact_tag__id
			WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_objID);

        if ($p_only_primary === true)
        {
            $l_sql .= ' AND isys_catg_contact_list__primary_contact = 1';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Retrieves the email address of a person by the data-ID.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_email_by_id($p_id)
    {
        $l_sql = 'SELECT isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_cats_person_list
            LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_cats_person_list__mail_address');
    } // function

    /**
     * Retrieve a person by its ID.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getPersonInternByID($p_id)
    {
        $l_query = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_cats_person_list
	        LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
	        WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_query)
            ->get_row();
    } // function

    /**
     * Retrieve all persons.
     *
     * @return  isys_component_dao_result
     */
    public function getContacts()
    {
        $l_query = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			LEFT JOIN isys_catg_mail_addresses_list
			ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1';

        return $this->retrieve($l_query);
    } // function

    /**
     * Retrieves all contact objects by tag ID.
     *
     * @param null $p_obj_id
     * @param null $p_tag_id
     * @param null $p_condition
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_contact_objects_by_tag($p_obj_id = null, $p_tag_id = null, $p_condition = null)
    {
        $l_tag_condition = '';

        if (is_array($p_tag_id))
        {
            $l_tag_condition = ' AND isys_contact_tag__id IN(' . implode(',', $p_tag_id) . ') ';
        }
        else if ($p_tag_id !== null)
        {
            $l_tag_condition = ' AND isys_contact_tag__id = ' . $this->convert_sql_id($p_tag_id) . ' ';
        } // if

        if ($p_obj_id !== null)
        {
            $l_tag_condition .= ' AND isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } // if

        if ($p_condition !== null)
        {
            $l_tag_condition .= $p_condition;
        } // if

        $l_query = 'SELECT isys_obj.*, isys_catg_contact_list.*, isys_contact_tag.* FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
            INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
            INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id
            WHERE TRUE ' . $l_tag_condition . ';';

        return $this->retrieve($l_query);
    } // function

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['role']))
        {
            $l_role = isys_import_handler::check_dialog('isys_contact_tag', $p_data['role']);
        }
        else
        {
            $l_role = null;
        } // if

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'contact' => [
                    'value' => $p_data['contact']
                ],
                'role'    => [
                    'value' => $l_role
                ]
            ]
        ];
    } // function

    /**
     * Adds a new contact role.
     *
     * @param   integer $p_contact_tag_title
     * @param   integer $p_contact_tag_relation_type
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function add_contact_tag($p_contact_tag_title, $p_contact_tag_relation_type)
    {
        $l_update = 'INSERT INTO isys_contact_tag SET
            isys_contact_tag__title = ' . $this->convert_sql_text($p_contact_tag_title) . ',
			isys_contact_tag__isys_relation_type__id = ' . $this->convert_sql_id($p_contact_tag_relation_type) . ',
			isys_contact_tag__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_update) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Updates an existing contact role.
     *
     * @param   integer $p_contact_tag_id
     * @param   string  $p_contact_tag_title
     * @param   integer $p_contact_tag_relation_type
     *
     * @return  bool
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function update_contact_tag($p_contact_tag_id, $p_contact_tag_title = null, $p_contact_tag_relation_type = null)
    {
        if ($p_contact_tag_id === null || ($p_contact_tag_relation_type === null && $p_contact_tag_relation_type === null))
        {
            return false;
        } // if

        $l_update = 'UPDATE isys_contact_tag SET ';

        if ($p_contact_tag_title !== null)
        {
            $l_update .= 'isys_contact_tag__title = ' . $this->convert_sql_text($p_contact_tag_title) . ' ';
        } // if

        if ($p_contact_tag_relation_type !== null)
        {
            if ($p_contact_tag_title !== null)
            {
                $l_update .= ',';
            } // if

            $l_update .= 'isys_contact_tag__isys_relation_type__id = ' . $this->convert_sql_id($p_contact_tag_relation_type) . ' ';
        } // if

        $l_update .= 'WHERE isys_contact_tag__id = ' . $this->convert_sql_id($p_contact_tag_id);

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Deletes existing contact roles.
     *
     * @param   mixed $p_contact_tag_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function delete_contact_tag($p_contact_tag_id)
    {
        if (is_array($p_contact_tag_id))
        {
            $l_delete = 'WHERE isys_contact_tag__id IN (' . implode(',', $p_contact_tag_id) . ')';
        }
        else if (is_numeric($p_contact_tag_id))
        {
            $l_delete = 'WHERE isys_contact_tag__id = ' . $this->convert_sql_id($p_contact_tag_id);
        }
        else
        {
            return false;
        } // if

        return ($this->update('DELETE FROM isys_contact_tag ' . $l_delete) && $this->apply_update());
    } // function

    /**
     * Gets assigned objects by contact object id or via e-mail
     *
     * @param int    $p_contact_obj_id
     * @param string $p_email
     * @param bool   $p_group_by_obj_id
     *
     * @return bool|isys_component_dao_result
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_objects_by_contact($p_contact_obj_id = null, $p_email = null)
    {
        if (empty($p_contact_obj_id) && empty($p_email)) return false;

        $l_sql = 'SELECT o1.*, ot.*, isys_contact_tag__title, isys_catg_contact_list__primary_contact
			FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
			LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id
			INNER JOIN isys_obj AS o1 ON isys_catg_contact_list__isys_obj__id = o1.isys_obj__id
			INNER JOIN isys_obj_type AS ot ON o1.isys_obj__isys_obj_type__id = ot.isys_obj_type__id
			INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id
			WHERE ';
        if ($p_contact_obj_id !== null)
        {
            $l_sql .= 'o2.isys_obj__id = ' . $this->convert_sql_id($p_contact_obj_id) . ' ';
        }
        else
        {
            $l_sql .= 'o2.isys_obj__id = ' . '(SELECT isys_catg_mail_addresses_list__isys_obj__id FROM isys_catg_mail_addresses_list ' . 'WHERE isys_catg_mail_addresses_list__title = ' . $this->convert_sql_text(
                    $p_email
                ) . ') ';
        } // if

        $l_sql .= ';';

        return $this->retrieve($l_sql);
    } // function

    public function get_contact_objects_by_tags($p_obj_id, $p_tagArray)
    {
        $l_query = 'SELECT isys_obj.* FROM isys_catg_contact_list ' . 'INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id ' . 'INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id ' . 'INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id ' . 'WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ' ' . 'AND isys_contact_tag__id IN( ' . implode(',', $p_tagArray) . ');';

        return $this->retrieve($l_query);
    }

    public function import($p_data, $p_object_id)
    {
        $l_dao_person = new isys_cmdb_dao_category_s_person_master($this->get_database_component());

        if (is_array($p_data))
        {
            $l_contacts_res     = $this->get_contacts_by_obj_id($p_object_id);
            $l_already_assigned = [];
            while ($l_contacts_row = $l_contacts_res->get_row())
            {
                $l_already_assigned[] = $l_contacts_row['isys_connection__isys_obj__id'];
            } // while

            foreach ($p_data as $l_contacts)
            {
                $l_login_username = null;
                if (($l_posi = strrpos($l_contacts["contact"], "\\")) !== false)
                {
                    $l_login_username = substr($l_contacts["contact"], $l_posi + 1, strlen($l_contacts["contact"]));
                }
                else
                {
                    $l_login_username = $l_contacts["contact"];
                } // if

                if ($l_login_username !== null)
                {
                    // Check if user with username exists
                    $l_res = $l_dao_person->get_person_by_username($l_login_username);
                    if ($l_res->num_rows() > 0)
                    {
                        $l_contact_obj_id = $l_res->get_row_value('isys_obj__id');

                        if (count($l_already_assigned) > 0 && in_array($l_contact_obj_id, $l_already_assigned))
                        {
                            continue;
                        }

                        $this->create($p_object_id, $l_contact_obj_id, C__CONTACT_TYPE__USER);
                        $l_already_assigned[] = $l_contact_obj_id;
                    }
                }
            }

            return true;
        }
    }
} // class