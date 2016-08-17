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
 * DAO: specific category for person group members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_person_group_members extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_group_members';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';
    /**
     * Flag which defines if the category is only a list with an object browser.
     *
     * @var  boolean
     */
    protected $m_object_browser_category = true;
    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'connected_object';
    /**
     * Category's table.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_cats_person_list';

    public function get_count($p_obj_id = null)
    {

        if (!empty($p_obj_id)) $l_obj_id = $p_obj_id;
        else $l_obj_id = $this->m_object_id;

        $l_sql = "SELECT COUNT(isys_cats_person_list__id) AS count FROM isys_person_2_group " . "INNER JOIN isys_cats_person_list " . "ON " . "isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id " . "INNER JOIN isys_cats_person_group_list " . "ON " . "isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($this->m_object_id))
        {
            $l_sql .= " AND (isys_person_2_group__isys_obj__id__group = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT isys_cats_person_list.*, isys_person_2_group.*, isys_cats_person_group_list.*, persongroup.*, " .

            /**
             * Containing person
             */
            "person.isys_obj__id as `person_id`, person.isys_obj__title as `person_title`, " . "person.isys_obj__sysid as `person_sysid`, person.isys_obj__isys_obj_type__id as `person_type`, " .

            /**
             * Containing the email
             */
            "mail_person.isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, " . "mail_pgroup.isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address " .

            "FROM isys_person_2_group " . "INNER JOIN isys_cats_person_list " . "ON " . "isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id " . "INNER JOIN isys_cats_person_group_list " . "ON " . "isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id " . "INNER JOIN isys_obj persongroup " . "ON " . "persongroup.isys_obj__id = isys_cats_person_group_list__isys_obj__id " . "INNER JOIN isys_obj person " . "ON " . "person.isys_obj__id = isys_cats_person_list__isys_obj__id " . "LEFT JOIN isys_catg_mail_addresses_list AS mail_person " . "ON " . "mail_person.isys_catg_mail_addresses_list__isys_obj__id = person.isys_obj__id AND mail_person.isys_catg_mail_addresses_list__primary = 1 " . "LEFT JOIN isys_catg_mail_addresses_list AS mail_pgroup " . "ON " . "mail_pgroup.isys_catg_mail_addresses_list__isys_obj__id = persongroup.isys_obj__id AND mail_pgroup.isys_catg_mail_addresses_list__primary = 1 " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id))
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_id))
        {
            $l_sql .= " AND (isys_person_2_group__id = '{$p_id}')";
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND (isys_cats_person_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

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
                $l_sql = ' AND (isys_person_2_group__isys_obj__id__group ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'first_name'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_FIRST_NAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'First name'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__first_name'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__FIRST_NAME'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'last_name'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_LAST_NAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Last name'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__last_name'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__LAST_NAME'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'department'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_DEPARTMENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Department'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__department'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__DEPARTMENT'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'phone_company'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_TELEPHONE_COMPANY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Telephone company'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__phone_company'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__PHONE_COMPANY'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'email_address'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_EMAIL_ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => 'EMail'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__mail_address'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__EMAIL_ADDRESS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'organization'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_ASSIGNED_ORGANISATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Organisation'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__isys_connection__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__ORGANIZATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => ''
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
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
            'title'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__title'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__TITLE'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'connected_object' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__MEMBERS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Person group memberships'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__isys_obj__id'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ]
                ]
            )
        ];
    } // function

    /**
     * @param array  $p_objects
     * @param int    $p_direction
     * @param string $p_table
     *
     * @return bool
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE])
        {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($_POST["id"]))
                {
                    foreach ($_POST["id"] AS $l_val)
                    {
                        $this->detach_person(null, null, $l_val);
                    } // foreach

                    unset($_POST["id"]);
                } // if

                return true;
        } // switch

        return true;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                if (($p_category_data['data_id'] = $this->attach_person(
                    $p_object_id,
                    $p_category_data['properties']['connected_object'][C__DATA__VALUE]
                ))
                )
                {
                    $l_indicator = true;
                }
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save specific category monitor
     *
     * @param int $p_cat_level        level to save, default 0
     * @param int &$p_intOldRecStatus __status of record before update
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    }

    /**
     * Einfuegen einer Personen/Gruppen Zuordnung
     *
     * @param integer $p_group_id
     * @param integer $p_person_id
     *
     * @return bool
     */
    public function attach_person($p_group_id, $p_person_id)
    {
        $l_sql = "INSERT INTO isys_person_2_group SET " . "isys_person_2_group__isys_obj__id__person = '" . $p_person_id . "', " . "isys_person_2_group__isys_obj__id__group = '" . $p_group_id . "';";

        if ($this->update($l_sql))
        {
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->handle_relation($this->get_last_insert_id(), "isys_person_2_group", C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS, null, $p_group_id, $p_person_id);

            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.beforeUserGroupChanged', $p_person_id, $p_group_id, 'attach-person');
        }
    }

    /**
     * Detaches a person and removes its relation
     *
     * @param integer $p_group_id
     * @param integer $p_person_id
     *
     * @return bool
     */
    public function detach_person($p_group_id, $p_person_id, $p_cat_list_id = null)
    {

        if ($p_cat_list_id > 0)
        {
            $l_data = $this->get_data($p_cat_list_id)
                ->__to_array();
        }
        else
        {
            $l_data = $this->get_data(null, $p_group_id, " AND isys_person_2_group__isys_obj__id__person = '" . $p_person_id . "'")
                ->__to_array();
        }

        if ($l_data["isys_person_2_group__isys_catg_relation_list__id"] > 0)
        {
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->delete_relation($l_data["isys_person_2_group__isys_catg_relation_list__id"]);
        }

        $l_sql = "DELETE FROM isys_person_2_group WHERE TRUE ";

        if ($p_person_id)
        {
            $l_sql .= " AND isys_person_2_group__isys_obj__id__person = '" . $p_person_id . "' ";
        }

        if ($p_group_id)
        {
            $l_sql .= " AND isys_person_2_group__isys_obj__id__group = '" . $p_group_id . "' ";
        }

        if (!empty($p_cat_list_id))
        {
            $l_sql .= " AND isys_person_2_group__id = " . $this->convert_sql_id($p_cat_list_id);
        }

        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUserGroupChanged', $p_person_id, $p_group_id, 'detach-person');

        return $this->update($l_sql . ";") && $this->apply_update();
    } // function

    /**
     * Updates category data.
     *
     * @param   integer $p_objID   Object identifier
     * @param   array   $p_persons Arrays of integers with person's object identifiers
     *
     * @return  boolean  Success?
     */
    public function save($p_objID, $p_persons)
    {
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_objID, $this->get_category_const());

        if (!$l_edit_right)
        {
            return false;
        } // if

        $l_data = $this->get_data(null, $p_objID);

        while ($l_row = $l_data->get_row())
        {
            $l_current_persons[$l_row["isys_person_2_group__isys_obj__id__person"]] = $l_row["isys_person_2_group__id"];
        } // while

        if (is_array($p_persons))
        {
            foreach ($p_persons as $l_person)
            {
                if (!isset($l_current_persons[$l_person]) || !$l_current_persons[$l_person])
                {
                    $this->attach_person($p_objID, $l_person);
                } // if
            } // foreach
        }
        else if (is_scalar($p_persons))
        {
            if (!isset($l_current_persons[$p_persons]) || !$l_current_persons[$p_persons])
            {
                $this->attach_person($p_objID, $l_current_persons[$p_persons]);
            } // if
        } // if

        return $this->apply_update();
    } // function

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return bool|null
     * @throws Exception
     */
    public function attachObjects($p_object_id, array $p_persons)
    {
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_object_id, $this->get_category_const());

        if (!$l_edit_right)
        {
            return null;
        } // if

        $l_existing = [];
        $l_save     = [];

        // Select all items from the database-table for deleting them.
        $l_res = $this->get_selected_persons($p_object_id);

        // Get the array of ID's from our json-string.
        while ($l_row = $l_res->get_row())
        {
            $l_existing[] = $l_row['isys_obj__id'];

            // Delete entries from tables.
            if (!in_array($l_row['isys_obj__id'], $p_persons))
            {
                $this->detach_person($p_object_id, $l_row['isys_obj__id']);
            } // if
        } // while

        foreach ($p_persons as $l_person)
        {
            // But don't insert any items, that already exist!
            if (!in_array($l_person, $l_existing))
            {
                if ($l_person > 0)
                {
                    // Collect persons for one single method call.
                    $l_save[] = $l_person;
                } // if
            } // if
        } // foreach

        if (count($l_save) > 0)
        {
            return $this->save($p_object_id, $l_save);
        } // if

        return null;
    } // function

    /**
     * Get the preselection for the object-browser.
     *
     * @param   integer $p_group_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_selected_persons($p_group_id)
    {
        $l_sql = 'SELECT * FROM isys_person_2_group p2g ' . 'LEFT JOIN isys_obj AS obj ' . 'ON p2g.isys_person_2_group__isys_obj__id__person = obj.isys_obj__id ' . 'WHERE p2g.isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id(
                $p_group_id
            ) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.cmdb.beforeUserGroupChanged',
                [
                    'isys_cmdb_dao_category_s_person',
                    'slotBeforeUserGroupChanged'
                ]
            );
    }
} // class