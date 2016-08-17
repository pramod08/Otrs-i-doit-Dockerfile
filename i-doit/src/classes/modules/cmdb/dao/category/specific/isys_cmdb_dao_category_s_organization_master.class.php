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
 * DAO: specific category for master organizations
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_organization_master extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'organization_master';

    /**
     * Category's constant.
     *
     * @var    string
     */
    protected $m_category_const = 'C__CATS__ORGANIZATION_MASTER_DATA';

    /**
     * Category's identifier.
     *
     * @var    integer
     */
    protected $m_category_id = C__CATS__ORGANIZATION_MASTER_DATA;

    /**
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * Field for the object ID.
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_cats_organization_list__isys_obj__id';

    /**
     * Main table where properties are stored persistently.
     *
     * @var  string
     */
    protected $m_table = 'isys_cats_organization_list';

    /**
     * Method for retrieving the dynamic properties of this dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_properties()
    {
        return [
            '_website' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_WEBSITE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Website'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__website'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            $this,
                            'dynamic_property_callback_website'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => true,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            )
        ];
    } // function

    /**
     * Return Category Data.
     *
     * @param   mixed    $p_cats_list_id
     * @param   integer  $p_obj_id
     * @param   string   $p_condition
     * @param   mixed    $p_filter
     * @param   integer  $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_cats_organization_list
            INNER JOIN isys_obj ON isys_cats_organization_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_connection ON isys_cats_organization_list__isys_connection__id = isys_connection__id
            WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_cats_list_id !== null)
        {
            $l_sql .= " AND isys_cats_organization_list__id = " . $this->convert_sql_id($p_cats_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_cats_organization_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__title'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_TITLE'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ]
                ]
            ),
            'telephone'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_PHONE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Telephone'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__telephone'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_PHONE'
                    ]
                ]
            ),
            'fax'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_FAX',
                        C__PROPERTY__INFO__DESCRIPTION => 'Fax'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__fax'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_FAX'
                    ]
                ]
            ),
            'website'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_WEBSITE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Website'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__website'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_WEBSITE',
                        C__PROPERTY__UI__TYPE   => 'f_link',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTarget' => '_blank',
                        ],
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'headquarter' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_ASSIGNMENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Headquarter'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_cats_organization_list__isys_connection__id',
                        C__PROPERTY__DATA__RELATION_TYPE    => C__RELATION_TYPE__ORGANIZATION_HEADQUARTER,
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_s_organization_master',
                                'callback_property_relation_handler'
                            ], [
                                'isys_cmdb_dao_category_s_organization_master',
                                true
                            ]
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_connection',
                            'isys_connection__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_ASSIGNMENT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_organization_master',
                                    'callback_property_headquarter_selection'
                                ]
                            ),
                            'chosen' => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connection'
                        ]
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__ORGANIZATION
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create_connector(
                    'isys_cats_organization_list',
                    $p_object_id
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['telephone'][C__DATA__VALUE],
                    $p_category_data['properties']['fax'][C__DATA__VALUE],
                    $p_category_data['properties']['website'][C__DATA__VALUE],
                    $p_category_data['properties']['headquarter'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Dynamic property handling for getting the formatted website attribute.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_website($p_row)
    {
        if (empty($p_row['isys_cats_organization_list__website']))
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        return isys_factory::get_instance('isys_smarty_plugin_f_link')
            ->navigation_view(
                isys_application::instance()->template,
                [
                    'name'              => 'dynamic-property-organization-website',
                    'p_strValue'        => $p_row['isys_cats_organization_list__website'],
                    'p_strTarget'       => '_blank',
                    'p_bInfoIconSpacer' => 0
                ]
            );
    } // function

    public function callback_property_headquarter_selection(isys_request $p_request)
    {
        global $g_comp_database;
        /**
         * @var $l_dao isys_cmdb_dao_category_s_organization
         */
        $l_dao    = isys_cmdb_dao_category_s_organization::instance($g_comp_database);
        $l_obj_id = $p_request->get_object_id();

        $l_obj_types = $l_dao->get_object_types_by_category(C__CATS__ORGANIZATION_MASTER_DATA, 's', false);

        $l_condition = '';
        if (count($l_obj_types) > 0)
        {
            $l_condition = ' isys_obj__isys_obj_type__id IN (' . implode(',', $l_obj_types) . ') ';
        } // if

        if ($l_obj_id !== null)
        {
            if ($l_condition != '')
            {
                $l_condition .= 'AND ';
            } // if
            $l_condition .= ' isys_obj__id = ' . $l_dao->convert_sql_id($l_obj_id);
        } // if

        if ($l_condition != '')
        {
            $l_condition = 'WHERE ' . $l_condition;
        } // if

        $l_sql = 'SELECT isys_obj__id, isys_obj__title FROM isys_obj ' . $l_condition;

        $l_res = $l_dao->retrieve($l_sql);
        $l_arr = [];

        if ($l_res->num_rows() > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_arr[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            }
        }

        return $l_arr;
    }

    /**
     * @param            $p_title
     * @param bool|false $p_create
     *
     * @return int
     */
    public function get_object_id_by_title($p_title, $p_create = false)
    {
        $l_res = $this->get_objtype_by_cats_id(C__CATS__ORGANIZATION);
        $l_arr = [];
        while($l_row = $l_res->get_row())
        {
            $l_arr[] = $l_row['isys_obj_type__id'];
        } // while
        $l_obj_id = $this->get_obj_id_by_title($p_title, $l_arr);

        if (!$l_obj_id)
        {
            if ($p_create)
            {
                $l_obj_id = $this->insert_new_obj(C__OBJTYPE__ORGANIZATION, false, $p_title, null, C__RECORD_STATUS__NORMAL);

                $this->sync(
                    [
                        'properties' => [
                            'title' => [
                                C__DATA__VALUE => $p_title
                            ]
                        ]
                    ],
                    $l_obj_id,
                    isys_import_handler_cmdb::C__CREATE
                );
            }
        }
        else
        {
            /* Workaround for organizations without isys_cats_organization_list entry */
            if (!$this->get_data_by_object($l_obj_id)
                ->num_rows()
            )
            {
                $this->sync(
                    [
                        'properties' => [
                            'title' => [
                                C__DATA__VALUE => $p_title
                            ]
                        ]
                    ],
                    $l_obj_id,
                    isys_import_handler_cmdb::C__CREATE
                );
            }
        }

        return $l_obj_id;
    } // function

    /**
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao_cmdb
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_organization_list__status"];

        $l_list_id = $l_catdata["isys_cats_organization_list__id"];

        if (empty($l_list_id))
        {
            $l_list_id = $this->create_connector("isys_cats_organization_list", $_GET[C__CMDB__GET__OBJECT]);
        } // if

        if ($l_list_id)
        {
            if (empty($_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]))
            {
                $l_description = $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . C__CATS__ORGANIZATION];
            }
            else
            {
                $l_description = $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()];
            } // if

            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CONTACT__ORGANISATION_TITLE"],
                $_POST["C__CONTACT__ORGANISATION_PHONE"],
                $_POST["C__CONTACT__ORGANISATION_FAX"],
                $_POST["C__CONTACT__ORGANISATION_WEBSITE"],
                $_POST["C__CONTACT__ORGANISATION_ASSIGNMENT"],
                $l_description
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_bRet)
            {
                if (!$this->update_orga_object($_GET[C__CMDB__GET__OBJECT], $_POST["C__CONTACT__ORGANISATION_TITLE"]))
                {
                    throw new isys_exception_dao_cmdb("Error while updating organization object");
                } // if
            } // if
        } // if

        return $l_bRet == true ? $l_list_id : -1;
    } // function

    public function save($p_catlevel, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_telephone = null, $p_fax = null, $p_website = null, $p_headquarter = null, $p_description = null)
    {
        $l_old_data = $this->get_data($p_catlevel)->get_row();

        if (!empty($l_old_data["isys_cats_organization_list__isys_connection__id"]))
        {
            $l_id = isys_cmdb_dao_connection::instance($this->m_db)
                ->update_connection($l_old_data["isys_cats_organization_list__isys_connection__id"], $p_headquarter);
        }
        else
        {
            $l_id = isys_cmdb_dao_connection::instance($this->m_db)
                ->add_connection($p_headquarter);
        } // if

        $l_sql = "UPDATE isys_cats_organization_list
            INNER JOIN isys_obj ON isys_obj__id = isys_cats_organization_list__isys_obj__id
            SET isys_obj__status = " . $this->convert_sql_id($p_status) . ",
            isys_obj__title = " . $this->convert_sql_text($p_title) . ",
            isys_cats_organization_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_cats_organization_list__status = " . $this->convert_sql_id($p_status) . ",
            isys_cats_organization_list__telephone = " . $this->convert_sql_text($p_telephone) . ",
            isys_cats_organization_list__fax =" . $this->convert_sql_text($p_fax) . ",
            isys_cats_organization_list__website =" . $this->convert_sql_text($p_website) . ",
            isys_cats_organization_list__isys_connection__id =" . $this->convert_sql_id($l_id) . ",
            isys_cats_organization_list__description =" . $this->convert_sql_text($p_description) . "
            WHERE isys_cats_organization_list__id = " . $this->convert_sql_id($p_catlevel);

        if ($this->update($l_sql))
        {
            isys_cmdb_dao_category_g_global::instance($this->m_db)->handle_template_status($l_old_data["isys_obj__status"], $l_old_data["isys_obj__id"]);

            // Create implicit relation.
            try
            {
                $l_data = $this->get_data($p_catlevel)->get_row();

                if ($l_data && $l_data["isys_cats_organization_list__isys_obj__id"] > 0)
                {
                    $l_relation_dao = isys_factory::get_instance('isys_cmdb_dao_category_g_relation', $this->get_database_component());

                    if ($p_headquarter > 0)
                    {
                        // Update relation.
                        $l_relation_dao->handle_relation(
                            $p_catlevel,
                            $this->m_table,
                            C__RELATION_TYPE__ORGANIZATION_HEADQUARTER,
                            $l_data[$this->m_table . "__isys_catg_relation_list__id"],
                            $p_headquarter,
                            $l_data[$this->m_table . "__isys_obj__id"]
                        );
                    }
                    else
                    {
                        // Remove relation.
                        $l_relation_dao->delete_relation($l_data['isys_cats_organization_list__isys_catg_relation_list__id']);
                    } // if
                } // if
            }
            catch (Exception $e)
            {
                throw $e;
            } // try

            return $this->apply_update();
        } // if

        return false;
    } // function

    /**
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function update_orga_object($p_object_id, $p_title)
    {
        $l_sql = 'UPDATE isys_obj SET isys_obj__title = ' . $this->convert_sql_text($p_title) . ' WHERE isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Executes the query to create new oragization
     *
     * @return int the newly created ID or false
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_street, $p_zip_code, $p_city, $p_country, $p_telephone, $p_fax, $p_website, $p_connection_id, $p_headquarter_id, $p_description)
    {

        if (is_null($p_connection_id))
        {
            $l_dao_connection = new isys_cmdb_dao_connection($this->m_db);
            $p_connection_id  = $l_dao_connection->add_connection(null);
        }

        $l_sql = "INSERT IGNORE INTO isys_cats_organization_list (
					isys_cats_organization_list__isys_obj__id,
					isys_cats_organization_list__title,
					isys_cats_organization_list__status,
					isys_cats_organization_list__telephone,
					isys_cats_organization_list__fax,
					isys_cats_organization_list__website,
					isys_cats_organization_list__isys_connection__id,
					isys_cats_organization_list__headquarter,
					isys_cats_organization_list__description)
					VALUES ";

        $l_sql .= "(" . $this->convert_sql_id($p_objID) . ",
					" . $this->convert_sql_text($p_title) . ",
					" . $this->convert_sql_id($p_newRecStatus) . ",
					" . $this->convert_sql_text($p_telephone) . ",
					" . $this->convert_sql_text($p_fax) . ",
					" . $this->convert_sql_text($p_website) . ",
					" . $this->convert_sql_id($p_connection_id) . ",
					" . $this->convert_sql_id($p_headquarter_id) . ",
					" . $this->convert_sql_text($p_description) . "
					)";

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        }
    }

    /**
     * Get PersonData by $p_orga_id (location).
     *
     * @param   integer $p_orga_id
     *
     * @return  isys_component_dao_result
     */
    public function get_persons_by_id($p_orga_id)
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
            FROM isys_cats_person_list
            INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
            LEFT JOIN isys_catg_mail_addresses_list ON isys_connection__isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_orga_id) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_orga_id
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_organisation_title_by_id($p_orga_id)
    {
        $l_sql = 'SELECT isys_cats_organization_list__title
            FROM isys_cats_organization_list
            WHERE isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($p_orga_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_cats_organization_list__title');
    } // function

    /**
     * Method for simply updating the organization title.
     *
     * @param   integer $p_organization_object_id
     * @param   string  $p_title
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_organization_title($p_organization_object_id, $p_title)
    {
        $l_sql = 'UPDATE isys_cats_organization_list
			SET isys_cats_organization_list__title = ' . $this->convert_sql_text($p_title) . '
			WHERE isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($p_organization_object_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function
} // class