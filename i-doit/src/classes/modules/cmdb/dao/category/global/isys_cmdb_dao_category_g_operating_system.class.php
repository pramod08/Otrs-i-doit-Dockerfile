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
 * DAO: global category for operation systems.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @since       1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_operating_system extends isys_cmdb_dao_category_g_application
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'operating_system';
    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = false;
    /**
     * This variable holds the table name.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_application_list';

    /**
     * Dynamic property handling for displaying the operating system of the object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_application($p_row)
    {
        global $g_comp_database;

        $l_os = isys_cmdb_dao_category_g_operating_system::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_os && is_array($l_os))
        {
            $l_quick_info = new isys_ajax_handler_quick_info();

            return $l_quick_info->get_quick_info($l_os["isys_catg_application_list__isys_obj__id"], $l_os['isys_obj__title'], C__LINK__OBJECT);
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Executes the query to create the category entry for object referenced by $p_objID.
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
     * @param   integer $p_version
     *
     * @return  mixed  Integer with the newly created ID on success, otherwise boolean false.
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description, $p_licence = null, $p_database_schemata_obj = null, $p_it_service_obj = null, $p_variant = null, $p_bequest_nagios_services = 1, $p_type = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM, $p_priority = C__CATG__APPLICATION_PRIORITY__PRIMARY, $p_version = null)
    {
        $l_return = parent::create(
            $p_objID,
            $p_newRecStatus,
            $p_connectedObjID,
            $p_description,
            $p_licence,
            $p_database_schemata_obj,
            $p_it_service_obj,
            $p_variant,
            $p_bequest_nagios_services,
            C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM,
            C__CATG__APPLICATION_PRIORITY__PRIMARY,
            $p_version
        );

        if (!$l_return)
        {
            return false;
        } // if

        // After saving, we go sure the current record is the only "primary" one.
        return ($this->make_primary_os($l_return, $p_objID) ? $l_return : false);
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_application' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__OPERATING_SYSTEM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Operating system'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_application'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param  integer $p_obj_id
     *
     * @return integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $p_obj_id ?: $this->m_object_id;

        if ($l_obj_id > 0)
        {
            return count($this->get_data(null, $l_obj_id));
        } // if

        return 0;
    } // function

    /**
     * Return Category Data - Note: Cannot use generic method because of the second left join.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= ' AND isys_catg_application_list__isys_catg_application_type__id = ' . $this->convert_sql_id(
                C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM
            ) . ' AND isys_catg_application_list__isys_catg_application_priority__id = ' . $this->convert_sql_id(C__CATG__APPLICATION_PRIORITY__PRIMARY) . ' ';

        return parent::get_data($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status);
    } // function

    /**
     * This method needs to be overwritten to open the category in edit/view mode correctly.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_general_data($p_obj_id = null)
    {
        return $this->get_data(($p_obj_id ? null : ($_GET[C__CMDB__GET__OBJECT] ? null : false)), $p_obj_id ?: $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL)
            ->get_row();
    } // function

    /**
     * Import-Handler for this category.
     *
     * @param   array $p_data
     *
     * @return  array
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function import($p_data, $p_obj_id = null, $p_operating_system = false)
    {
        return parent::import($p_data, null, true);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        $l_properties = parent::properties();

        $l_properties['application'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]       = _L('LC__CATG__OPERATING_SYSTEM');
        $l_properties['application'][C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION] = 'The connected operating system';

        foreach ($l_properties as &$l_property)
        {
            $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]                  = str_replace(
                'C__CATG__APPLICATION',
                'C__CATG__OPERATING_SYSTEM',
                $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]
            );
            $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] = true;
        } // foreach

        $l_properties['description'][C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__OPERATING_SYSTEM;

        return $l_properties;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param   integer $p_cat_level
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
     * @param   integer $p_version
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID, $p_description, $p_licence, $p_database_schemata_obj, $p_it_service_obj, $p_variant = null, $p_bequest_nagios_services = null, $p_type = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM, $p_priority = C__CATG__APPLICATION_PRIORITY__PRIMARY, $p_version = null)
    {
        $l_return = parent::save(
            $p_cat_level,
            $p_newRecStatus,
            $p_connectedObjID,
            $p_description,
            $p_licence,
            $p_database_schemata_obj,
            $p_it_service_obj,
            $p_variant,
            $p_bequest_nagios_services,
            C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM,
            C__CATG__APPLICATION_PRIORITY__PRIMARY,
            $p_version
        );

        if (!$l_return)
        {
            return false;
        } // if

        // After saving, we go sure the current record is the only "primary" one.
        return $this->make_primary_os($p_cat_level);
    } // function

    /**
     * Save global category application element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @throws  isys_exception_dao
     * @return  int|null
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

        $p_create = (count($l_catdata) === 0);

        if ($p_create)
        {
            // Overview page and no input was given
            if (isys_glob_get_param(C__CMDB__GET__CATG) == C__CATG__OVERVIEW && empty($_POST['C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN']))
            {
                return null;
            } // if

            $l_applications = $_POST['C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN'];

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
                    $_POST["C__CATG__OPERATING_SYSTEM_DATABASE_SCHEMATA__HIDDEN"],
                    $_POST["C__CATG__OPERATING_SYSTEM_IT_SERVICE__HIDDEN"],
                    $_POST["C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT"] ?: -1,
                    $_POST["C__CATG__OPERATING_SYSTEM_BEQUEST_NAGIOS_SERVICES"],
                    C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM,
                    C__CATG__APPLICATION_PRIORITY__PRIMARY,
                    $_POST['C__CATG__OPERATING_SYSTEM_VERSION'] ?: -1
                );

                $this->m_strLogbookSQL = $this->get_last_query();

                if ($l_id)
                {
                    $l_catdata   = ['isys_catg_application_list__id' => $l_id];
                    $l_bRet      = true;
                    $p_cat_level = null;
                }
                else
                {
                    throw new isys_exception_dao("Could not create category element application");
                } // if
            } // foreach
        }
        else
        {
            $l_catdata         = $l_catdata->get_row();
            $p_intOldRecStatus = $l_catdata["isys_catg_application_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_application_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__LIC_ASSIGN__LICENSE__HIDDEN"],
                $_POST["C__CATG__OPERATING_SYSTEM_DATABASE_SCHEMATA__HIDDEN"],
                $_POST["C__CATG__OPERATING_SYSTEM_IT_SERVICE__HIDDEN"],
                $_POST["C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT"],
                $_POST["C__CATG__OPERATING_SYSTEM_BEQUEST_NAGIOS_SERVICES"],
                C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM,
                C__CATG__APPLICATION_PRIORITY__PRIMARY,
                $_POST['C__CATG__OPERATING_SYSTEM_VERSION']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        if ($p_create)
        {
            return $l_catdata["isys_catg_application_list__id"];
        } // if

        return ($l_bRet == true) ? null : $l_intErrorCode;
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $p_category_data['properties']['application_type']     = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM;
        $p_category_data['properties']['application_priority'] = C__CATG__APPLICATION_PRIORITY__PRIMARY;

        if($p_category_data['data_id'] === null)
        {
            $p_category_data['data_id'] = $this->get_data(null, $p_object_id)->get_row_value('isys_catg_application_list__id');
        } // if

        return parent::sync($p_category_data, $p_object_id, $p_status);
    } // function
} // class