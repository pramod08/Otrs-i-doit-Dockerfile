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
 * DAO: Subcategory assigned objects of specific category contract
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_contract_allocation extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'contract_allocation';
    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATS__CONTRACT_ALLOCATION';
    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     */
    protected $m_category_id = C__CATS__CONTRACT_ALLOCATION;
    /**
     * @var string
     */
    protected $m_entry_identifier = 'assigned_object';
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;
    /**
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;
    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'assigned_object';
    /**
     * Table for result
     *
     * @var string
     */
    protected $m_table = 'isys_catg_contract_assignment_list';

    /**
     * Createa new assignment
     *
     * @param int $p_cat_level
     * @param int $p_new_id
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_general
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_dao = isys_cmdb_dao_category_g_contract_assignment::instance($this->get_database_component());
        if (count($p_objects))
        {
            foreach ($p_objects as $l_object)
            {
                if (isset($l_assigned_objects[$l_object])) continue;
                if ($l_object > 0)
                {
                    // Create the new items.
                    $l_dao->create($l_object, C__RECORD_STATUS__NORMAL, null, null, $_GET[C__CMDB__GET__OBJECT]);
                } // if
            } // foreach
        } // if
        return true;
    } // function

    /**
     * Gets assigned objects
     *
     * @param      $p_obj_id
     * @param null $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_assigned_objects($p_obj_id, $p_status = null)
    {
        if ($p_status === null && isset($_POST['cRecStatus']) && is_numeric($_POST['cRecStatus']))
        {
            $p_status = (int) $_POST['cRecStatus'];
        } // if
        return $this->get_data(null, $p_obj_id, '', null, $p_status);
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [];
    } // function

    /**
     * Method for retrieving the number of objects, assigned to an object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {

        if ($p_obj_id !== null)
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        $l_sql = "SELECT count(isys_obj__id) AS count FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " . "LEFT JOIN isys_catg_contract_assignment_list ON isys_catg_contract_assignment_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " . "AND (isys_catg_contract_assignment_list__id IS NOT NULL) ";

        if ($l_obj_id !== null)
        {
            $l_sql .= "AND (isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        } // if

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return (int) $l_data["count"];
    } // function

    /**
     * Get data function
     *
     * @param null   $p_cats_list_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_contract_assignment_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_contract_assignment_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contract_assignment_list__isys_connection__id
			WHERE TRUE ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {

            if (is_array($p_obj_id))
            {
                $l_sql .= ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ')';
            }
            else
            {
                $l_sql .= ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ')';
            } // if
        } // if

        if ($p_cats_list_id !== null)
        {
            $l_sql .= " AND isys_catg_contract_assignment_list__id = " . $this->convert_sql_id($p_cats_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_contract_assignment_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . $p_condition);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MAINTENANCE_LINKED_OBJECT_LIST',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_contract_assignment_list__isys_obj__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATS__CONTRACT_ALLOCATION__ASSIGNED_OBJECT'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => true,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__IMPORT     => true,
                        C__PROPERTY__PROVIDES__EXPORT     => true
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
     * Rank record method of it-service components.
     *
     * @param   array   $p_objects
     * @param   integer $p_direction
     * @param   string  $p_table
     *
     * @return  boolean
     * @throws  isys_exception_general
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        parent::rank_records($p_objects, $p_direction, "isys_catg_contract_assignment_list");

        return true;
    } // function

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
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            /**
             * typehinting
             *
             * @var $l_dao isys_cmdb_dao_category_g_contract_assignment
             */
            $l_dao = isys_cmdb_dao_category_g_contract_assignment::instance($this->m_db);
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $l_dao->create(
                            $p_category_data['properties']['assigned_object'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            null,
                            null,
                            $p_object_id
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $l_cat_data = $l_dao->get_data($p_category_data['data_id'])
                            ->get_row();

                        $l_dao->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $l_cat_data['isys_catg_contract_assignment_list__contract_start'],
                            $l_cat_data['isys_catg_contract_assignment_list__contract_end'],
                            $p_object_id,
                            $l_cat_data['isys_catg_contract_assignment_list__description'],
                            $l_cat_data['isys_catg_contract_assignment_list__reaction_rate__id']
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    } // function

} // class