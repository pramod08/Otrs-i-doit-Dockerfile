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
 * DAO: specific category for Nagios person groups.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_person_group_nagios extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_group_nagios';

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_person_group_nagios_list WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_cats_list_id !== null)
        {
            $l_sql .= ' AND (isys_cats_person_group_nagios_list__id = ' . $this->convert_sql_id($p_cats_list_id) . ') ';
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND (isys_cats_person_group_nagios_list__status = ' . $this->convert_sql_int($p_status) . ') ';
        } // if

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id May be an integer or an array of integers.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (isys_cats_person_group_nagios_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_cats_person_group_nagios_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            } // if
        } // if

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
            'is_exportable' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__NAGIOS_CONFIG_EXPORT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Export this configuration'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_nagios_list__is_exportable',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__NAGIOS_IS_EXPORTABLE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__IMPORT    => true,
                        C__PROPERTY__PROVIDES__EXPORT    => true,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'alias'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'alias',
                        C__PROPERTY__INFO__DESCRIPTION => 'alias'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_nagios_list__alias'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__MODULE__NAGIOS__CONTACT_GROUP_ALIAS'
                    ]
                ]
            ),
            'description'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_nagios_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__PERSON_GROUP_NAGIOS
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
                    'isys_cats_person_group_nagios_list',
                    $p_object_id
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data.
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['alias'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['is_exportable'][C__DATA__VALUE]
                );
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_person_group_nagios_list__status"];

        $l_list_id = $l_catdata["isys_cats_person_group_nagios_list__id"];

        if (empty($l_list_id))
        {
            $l_list_id = $this->create_connector("isys_cats_person_group_nagios_list", $_GET[C__CMDB__GET__OBJECT]);
        } // if

        if ($l_list_id)
        {
            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $_POST["C__MODULE__NAGIOS__CONTACT_GROUP_ALIAS"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__MODULE__NAGIOS__CONTACT_GROUP_IS_EXPORTABLE']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet == true ? $l_list_id : -1;
    } // function

    /**
     * Save method.
     *
     * @param   integer $p_catlevel
     * @param   mixed   $p_status
     * @param   string  $p_alias
     * @param   string  $p_description
     * @param   integer $p_is_exportable
     *
     * @return  boolean
     */
    public function save($p_catlevel, $p_status = C__RECORD_STATUS__NORMAL, $p_alias, $p_description, $p_is_exportable)
    {
        $l_sql = 'UPDATE isys_cats_person_group_nagios_list SET
			isys_cats_person_group_nagios_list__alias = ' . $this->convert_sql_text($p_alias) . ',
			isys_cats_person_group_nagios_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_person_group_nagios_list__status = ' . $this->convert_sql_id($p_status) . ',
			isys_cats_person_group_nagios_list__is_exportable = ' . $this->convert_sql_boolean($p_is_exportable) . '
			WHERE isys_cats_person_group_nagios_list__id = ' . $this->convert_sql_id($p_catlevel) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function
} // class
?>