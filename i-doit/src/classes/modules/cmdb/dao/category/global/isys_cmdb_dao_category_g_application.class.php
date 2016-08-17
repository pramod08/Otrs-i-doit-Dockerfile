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
 * DAO: global category for applications.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_application extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'application';
    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'application';

    /**
     * @var bool
     */
    protected $m_has_relation = true;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;
    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_application_list__isys_obj__id';

    /**
     * Callback method which returns the relation type because application assignment has two relation types:
     * - C__RELATION_TYPE__OPERATION_SYSTEM
     * - C__RELATION_TYPE__SOFTWARE
     *
     * @param isys_request $p_request
     *
     * @return int
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_type_handler(isys_request $p_request)
    {

        $l_dao  = isys_cmdb_dao_category_g_application::instance($this->m_db);
        $l_data = $l_dao->get_data_by_id($p_request->get_category_data_id())
            ->get_row();

        switch ($l_data['isys_catg_application_list__isys_catg_application_type__id'])
        {
            case C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM:
                return C__RELATION_TYPE__OPERATION_SYSTEM;
                break;
            case C__CATG__APPLICATION_TYPE__SOFTWARE:
            default:
                return C__RELATION_TYPE__SOFTWARE;
                break;
        } // switch
    } // function

    /**
     * Used for displaying all variants
     * in our object lists
     *
     * @author Selcuk Kekec <skekec@i-doit.de>
     * @global type $g_comp_database
     *
     * @param type  $p_row
     *
     * @return string HTML LIST
     */
    public function dynamic_property_collected_variants($p_row)
    {
        global $g_comp_database;

        $l_strOut             = "<ul><li>";
        $l_dao                = isys_cmdb_dao_category_g_application::instance($g_comp_database);
        $l_collected_variants = $l_dao->get_collected_variants($p_row['isys_obj__id']);

        if (is_array($l_collected_variants) && count($l_collected_variants))
        {
            $l_strOut .= implode('</li><li>', $l_collected_variants);
        } // if

        $l_strOut .= "</li></ul>";

        return $l_strOut;
    } // function

    /**
     * Dynamic property for displaying the database schema inside a report.
     *
     * @param   array $p_row
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    public function dynamic_property_assigned_database_schema($p_row)
    {
        global $g_comp_database;

        $l_dbs_dao = isys_cmdb_dao_category_s_database_access::instance($g_comp_database);
        $l_sql     = 'SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list WHERE isys_catg_relation_list__id = ' . $l_dbs_dao->convert_sql_id(
                $p_row['isys_catg_application_list__isys_catg_relation_list__id']
            );
        $l_rel_res = $l_dbs_dao->retrieve($l_sql);

        if (count($l_rel_res))
        {
            return $l_dbs_dao->get_data(
                null,
                null,
                "AND isys_connection__isys_obj__id = " . $l_dbs_dao->convert_sql_id($l_rel_res->get_row_value('isys_catg_relation_list__isys_obj__id')),
                null,
                C__RECORD_STATUS__NORMAL
            )
                ->get_row_value('isys_obj__title');
        } // if

        return '';
    } // function

    /**
     * Callback method for property assigned_it_service
     *
     * @param isys_request $p_request
     *
     * @return null|int
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_assigned_it_service(isys_request $p_request)
    {
        $l_data   = $p_request->get_row();
        $l_return = $this->get_assigned_it_services($l_data['isys_catg_application_list__isys_catg_relation_list__id']);

        return count($l_return) > 0 ? $l_return : null;
    } // function

    /**
     * Callback method for property assigned_database_schema
     *
     * @param isys_request $p_request
     *
     * @return null|int
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_assigned_database_schema(isys_request $p_request)
    {
        global $g_comp_database;

        $l_return  = null;
        $l_dbs_dao = isys_cmdb_dao_category_s_database_access::instance($g_comp_database);
        $l_data    = $p_request->get_row();
        $l_sql     = 'SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list WHERE isys_catg_relation_list__id = ' . $l_dbs_dao->convert_sql_id(
                $l_data['isys_catg_application_list__isys_catg_relation_list__id']
            );
        $l_rel_res = $l_dbs_dao->retrieve($l_sql);

        if ($l_rel_res->num_rows() > 0)
        {
            $l_rel_data = $l_rel_res->get_row_value('isys_catg_relation_list__isys_obj__id');
            $l_dbs_data = $l_dbs_dao->get_data(null, null, "AND isys_connection__isys_obj__id = " . $l_dbs_dao->convert_sql_id($l_rel_data), null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            $l_return = $l_dbs_data['isys_obj__id'];
        } // if

        return $l_return;
    } // function

    /**
     * Method for finding out the correct application type of the given object.
     *
     * @param   isys_request $p_request
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_application_type(isys_request $p_request)
    {
        $l_application_object      = $p_request->get_row('isys_obj__id');
        $l_application_object_type = $p_request->get_row('isys_obj_type__id');

        if (empty($l_application_object) || !($l_application_object > 0))
        {
            return -1;
        } // if

        if ($l_application_object_type == C__OBJTYPE__OPERATING_SYSTEM)
        {
            $l_constant = 'C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM';
        }
        else
        {
            $l_constant = 'C__CATG__APPLICATION_TYPE__SOFTWARE';
        } // if

        $l_type = isys_factory_cmdb_dialog_dao::get_instance('isys_catg_application_type', $this->m_db)
            ->get_data($l_constant);

        return $l_type['isys_catg_application_type__id'];
    } // function

    /**
     * Get all available variants
     * used by a specific object
     *
     * @author Selcuk Kekec <skekec@i-doit.de>
     *
     * @param type $p_object_id
     *
     * @return array array(variant_id => variant_title, ...)
     */
    public function get_collected_variants($p_object_id)
    {
        $l_return = [];
        $l_dao    = isys_cmdb_dao_category_g_application::instance($this->m_db);

        $l_sql = 'SELECT * FROM isys_catg_application_list ' . "INNER JOIN isys_cats_app_variant_list ON isys_catg_application_list__isys_cats_app_variant_list__id = isys_cats_app_variant_list__id " . "WHERE isys_catg_application_list__isys_obj__id  = " . $l_dao->convert_sql_id(
                $p_object_id
            ) . ";";
        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while (($l_row = $l_res->get_row()))
            {
                $l_return[$l_row['isys_cats_app_variant_list__id']] = $l_row['isys_cats_app_variant_list__variant'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Callback method for property assigned_variant.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_assigned_variant(isys_request $p_request)
    {
        global $g_comp_database;

        return isys_cmdb_dao_category_g_application::instance($g_comp_database)
            ->get_assigned_variant($p_request->get_category_data_id());
    } // function

    /**
     * Callback method for property assigned_version.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_assigned_version(isys_request $p_request)
    {
        global $g_comp_database;

        return isys_cmdb_dao_category_g_application::instance($g_comp_database)
            ->get_assigned_version($p_request->get_category_data_id());
    } // function

    /**
     * Gets all variants from the assigned application object.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_variant($p_cat_id)
    {
        $l_return = [];
        $l_dao    = isys_cmdb_dao_category_g_application::instance($this->m_db);

        $l_sql = 'SELECT *
			FROM isys_cats_app_variant_list
			WHERE isys_cats_app_variant_list__isys_obj__id = (
				SELECT isys_connection__isys_obj__id FROM isys_catg_application_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
				WHERE isys_catg_application_list__id = ' . $l_dao->convert_sql_id($p_cat_id) . '
			);';
        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while (($l_row = $l_res->get_row()))
            {
                $l_return[$l_row['isys_cats_app_variant_list__id']] = $l_row['isys_cats_app_variant_list__variant'] . ($l_row['isys_cats_app_variant_list__title'] != '' ? ' (' . $l_row['isys_cats_app_variant_list__title'] . ')' : '');
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Gets all variants from the assigned application object.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_assigned_version($p_cat_id)
    {
        $l_return = [];
        $l_dao    = isys_cmdb_dao_category_g_application::instance($this->m_db);

        $l_sql = 'SELECT *
			FROM isys_catg_version_list
			WHERE isys_catg_version_list__isys_obj__id = (
				SELECT isys_connection__isys_obj__id FROM isys_catg_application_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
				WHERE isys_catg_application_list__id = ' . $l_dao->convert_sql_id($p_cat_id) . '
			);';
        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while (($l_row = $l_res->get_row()))
            {
                $l_return[$l_row['isys_catg_version_list__id']] = $l_row['isys_catg_version_list__title'] . (!empty($l_row['isys_catg_version_list__hotfix']) ? ' (' . $l_row['isys_catg_version_list__hotfix'] . ')' : '');
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Get assigned it services by relation id
     *
     * @param $p_relation_id
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_assigned_it_services($p_relation_id)
    {
        global $g_comp_database;

        $l_its_dao = isys_cmdb_dao_category_g_it_service_components::instance($g_comp_database);
        $l_return  = [];

        $l_sql     = 'SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list WHERE isys_catg_relation_list__id = ' . $l_its_dao->convert_sql_id(
                $p_relation_id
            );
        $l_rel_res = $l_its_dao->retrieve($l_sql);

        if ($l_rel_res->num_rows() > 0)
        {
            $l_rel_data = $l_rel_res->get_row_value('isys_catg_relation_list__isys_obj__id');
            $l_its_res  = $l_its_dao->get_data(null, null, "AND isys_connection__isys_obj__id = " . $l_its_dao->convert_sql_id($l_rel_data), null, C__RECORD_STATUS__NORMAL);

            while ($l_its_data = $l_its_res->get_row())
            {
                $l_return[] = $l_its_data['isys_obj__id'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Return Category Data - Note: Cannot use generic method because of the "reference" join
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data_ng($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT
              isys_catg_application_list.*, main.*, isys_connection.*, isys_cats_lic_list.*
            FROM isys_catg_application_list
			INNER JOIN isys_obj main ON isys_catg_application_list__isys_obj__id = main.isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			LEFT JOIN isys_obj reference ON isys_connection__isys_obj__id = reference.isys_obj__id
			LEFT JOIN isys_cats_lic_list ON isys_catg_application_list__isys_cats_lic_list__id = isys_cats_lic_list__id
			WHERE TRUE ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null)
        {
            $l_sql .= ' ' . $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= "AND isys_catg_application_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= "AND isys_catg_application_list__status = " . $this->convert_sql_int($p_status) . " ";
        } // if

        return $this->retrieve($l_sql . $p_condition);
    } // function

    /**
     * @param   string $p_string
     *
     * @return  mixed
     */
    public function parse_manufacturer($p_string)
    {
        if (stristr($p_string, "windows"))
        {
            return "Microsoft";
        } // if

        if (stristr($p_string, ".net"))
        {
            return "Microsoft";
        } // if

        $l_manufacturer = [
            "microsoft",
            "suse",
            "google",
            "novel",
            "novell",
            "vmware",
            "apple",
            "redhat",
            "mcaffee",
            "norton",
            "nullsoft",
            "synetics",
            "sap",
            "adobe",
            "Conexant",
            "ibm",
            "intel",
            "ati",
            "ericsson",
            "nokia",
            "blackberry"
        ];

        if (preg_match("/^(" . implode("|", $l_manufacturer) . ").*?$/si", $p_string, $l_reg))
        {
            return $l_reg[1];
        } // if

        return false;
    } // function

    /**
     * Import-Handler for this category.
     *
     * @param   array   $p_data
     * @param   integer $p_obj_id
     * @param   boolean $p_operating_system
     *
     * @return  array
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_obj_id = null, $p_operating_system = false)
    {
        $l_ids     = [];
        $l_list_id = 0;

        if (count($p_data) > 0)
        {
            // Iterate through Applications.
            foreach ($p_data as $l_application)
            {
                verbose(".", false);

                // Create and check for application or operating system.
                if (!isset($l_application["type"]) && empty($l_application["type"]))
                {
                    $l_application["type"] = C__OBJTYPE__APPLICATION;
                } // if

                $l_objid = $this->get_obj_id_by_title($l_application["name"], $l_application["type"], C__RECORD_STATUS__NORMAL);

                if (!$l_objid)
                {
                    $l_objid = $this->insert_new_obj(
                        $l_application["type"],
                        false,
                        $l_application["name"],
                        ISYS_NULL,
                        C__RECORD_STATUS__NORMAL
                    );
                } // if

                $l_description  = "";
                $l_install_path = null;
                $l_manufacturer = null;
                $l_release      = null;
                $l_version      = null;

                // Get application version.
                if (isset($l_application["version"]) && $l_application["version"] != '')
                {
                    $l_version = $l_application["version"];
                } // if

                // Get service pack (for microsoft operating systems).
                if (isset($l_application["servicepack"]) && $l_application["servicepack"] != '')
                {
                    $l_description .= "Service Pack: " . $l_application["servicepack"] . "\n";
                } // if

                // Get manufacturer.
                if (($l_manufacturer = $this->parse_manufacturer($l_application["name"])))
                {
                    $l_manufacturer = isys_import::check_dialog("isys_application_manufacturer", $l_manufacturer);
                } // if

                if (isset($l_application['installlocation']) && $l_application['installlocation'] != '')
                {
                    $l_install_path = $l_application['installlocation'];
                } // if

                // Save those information into specific category application.
                /* @var  isys_cmdb_dao_category_s_application $l_dao */
                $l_dao         = isys_cmdb_dao_category_s_application::instance($this->m_db);
                $l_version_dao = isys_cmdb_dao_category_g_version::instance($this->m_db);
                $l_data        = $l_dao->get_data(null, $l_objid)
                    ->__to_array();
                $l_version_id  = null;

                if ($l_objid)
                {
                    if (!empty($l_version))
                    {
                        // Check, if the found software version has already been created.
                        $l_version_res = $l_version_dao->get_data(null, $l_objid, ' AND isys_catg_version_list__title LIKE ' . $l_version_dao->convert_sql_text($l_version));

                        if (count($l_version_res))
                        {
                            $l_version_id = $l_version_res->get_row_value('isys_catg_version_list__id');
                        }
                        else
                        {
                            $l_version_id = $l_version_dao->create($l_objid, C__RECORD_STATUS__NORMAL, $l_version);
                        } // if
                    } // if

                    if ($l_data['isys_cats_application_list__id'])
                    {
                        $l_dao->save(
                            $l_data["isys_cats_application_list__id"],
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $l_manufacturer,
                            null,
                            $l_description,
                            null,
                            null,
                            $l_install_path
                        );
                    }
                    else
                    {
                        $l_dao->create($l_objid, C__RECORD_STATUS__NORMAL, null, $l_manufacturer, null, $l_description, null, null, $l_install_path);
                    } // if

                } // if

                // Get client's install location.
                if (isset($l_application["installlocation"]) && !empty($l_application["installlocation"]))
                {
                    $l_description .= "Path: " . $l_application["installlocation"] . "\n";
                } // if

                // Create application connection.
                $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $l_objid,
                    $l_description,
                    null,
                    null,
                    null,
                    null,
                    1,
                    ($p_operating_system ? C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM : C__CATG__APPLICATION_TYPE__SOFTWARE),
                    ($p_operating_system ? C__CATG__APPLICATION_PRIORITY__PRIMARY : null),
                    $l_version_id
                );

                $l_ids[] = $l_list_id;
            } // foreach
        } // if

        return $l_ids;
    } // function

    /**
     * Return associated objects to given application object.
     *
     * @param   integer $p_application_obj__id
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_objects($p_application_obj__id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT * FROM isys_catg_application_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			INNER JOIN isys_obj ON isys_catg_application_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_application_list__isys_catg_relation_list__id
			LEFT OUTER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_obj__id
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_application_obj__id) . '
			' . $this->get_status_condition($p_status) . '
			GROUP BY isys_obj__id ORDER BY isys_obj__title;';

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_cat_id
     * @param   integer $p_application_obj__id
     * @param   integer $p_status
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_assigned_objects_and_relations($p_cat_id = null, $p_application_obj__id, $p_status = C__RECORD_STATUS__NORMAL, $p_condition = null)
    {
        $l_sql = "SELECT isys_catg_application_list__id,
			main.isys_obj__id AS main_obj_id,
			main.isys_obj__title AS main_obj_title,
			main.isys_obj__status AS main_obj_status,
			rel_object.isys_obj__id AS rel_obj_id,
			rel_object.isys_obj__title AS rel_obj_title,
			master.isys_obj__title AS master_title,
			slave.isys_obj__title AS slave_title,
			isys_catg_relation_list__isys_relation_type__id,
			isys_cats_app_variant_list__variant,
			isys_cats_app_variant_list__title,
			isys_catg_application_list__isys_cats_lic_list__id
			FROM isys_catg_application_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			INNER JOIN isys_obj AS main ON isys_catg_application_list__isys_obj__id = main.isys_obj__id
			INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_application_list__isys_catg_relation_list__id
			INNER JOIN isys_obj AS rel_object ON isys_catg_relation_list__isys_obj__id = rel_object.isys_obj__id
			INNER JOIN isys_obj AS master ON isys_catg_relation_list__isys_obj__id__master = master.isys_obj__id
			INNER JOIN isys_obj AS slave ON isys_catg_relation_list__isys_obj__id__slave = slave.isys_obj__id
			LEFT JOIN isys_cats_app_variant_list ON isys_catg_application_list__isys_cats_app_variant_list__id = isys_cats_app_variant_list__id
			WHERE isys_connection__isys_obj__id = " . $this->convert_sql_id($p_application_obj__id) . "
			AND isys_catg_application_list__status = " . $this->convert_sql_int($p_status);

        if (!empty($p_cat_id))
        {
            $l_sql .= " AND isys_catg_application_list__id = " . $this->convert_sql_id($p_cat_id);
        } // if

        if (!empty($p_condition))
        {
            $l_sql .= " " . $p_condition;
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_applications($p_objID)
    {
        $l_sql = 'SELECT * FROM isys_catg_application_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
			ORDER BY isys_obj__isys_obj_type__id;';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for making the given application assignment "primary". Should only be used for operating systems.
     *
     * @param   integer $p_category_entry_id
     * @param   integer $p_obj_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function make_primary_os($p_category_entry_id, $p_obj_id = null)
    {
        if ($p_obj_id === null)
        {
            $p_obj_id = $this->get_data($p_category_entry_id)
                ->get_row_value('isys_catg_application_list__isys_obj__id');
        } // if

        $l_sql = 'UPDATE isys_catg_application_list' . ' SET isys_catg_application_list__isys_catg_application_priority__id = ' . $this->convert_sql_id(
                C__CATG__APPLICATION_PRIORITY__SECONDARY
            ) . ' WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_obj_id
            ) . ' AND isys_catg_application_list__isys_catg_application_type__id = ' . $this->convert_sql_id(
                C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM
            ) . ' AND isys_catg_application_list__id != ' . $this->convert_sql_id($p_category_entry_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
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
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
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

        // This might happen, because the "operating system" option is disabled.
        if (isset($_POST['C__CATG__APPLICATION_TYPE']) && empty($_POST['C__CATG__APPLICATION_TYPE']))
        {
            $_POST['C__CATG__APPLICATION_TYPE'] = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM;
        } // if

        // Only save the priority, when the type = operating system.
        if ($_POST['C__CATG__APPLICATION_TYPE'] != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
        {
            $_POST['C__CATG__APPLICATION_PRIORITY'] = null;
        } // if

        if ($p_create)
        {
            // Overview page and no input was given
            if (isys_glob_get_param(C__CMDB__GET__CATG) == C__CATG__OVERVIEW && empty($_POST['C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN']))
            {
                return null;
            } // if

            $l_applications = $_POST['C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN'];

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
                    $_POST["C__CATG__APPLICATION_VARIANT__VARIANT"] ?: -1,
                    $_POST["C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES"],
                    $_POST['C__CATG__APPLICATION_TYPE'],
                    $_POST['C__CATG__APPLICATION_PRIORITY'],
                    $_POST['C__CATG__APPLICATION_VERSION'] ?: -1
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
            $l_catdata         = $this->get_general_data();
            $p_intOldRecStatus = $l_catdata["isys_catg_application_list__status"];

            if (isys_format_json::is_json_array($_POST['C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN']))
            {
                // Get last element of the array
                $_POST['C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN'] = array_pop(isys_format_json::decode($_POST['C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN']));
            } // if

            $l_bRet = $this->save(
                $l_catdata['isys_catg_application_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__APPLICATION_OBJ_APPLICATION__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__LIC_ASSIGN__LICENSE__HIDDEN"],
                $_POST["C__CATG__APPLICATION_DATABASE_SCHEMATA__HIDDEN"],
                $_POST["C__CATG__APPLICATION_IT_SERVICE__HIDDEN"],
                $_POST["C__CATG__APPLICATION_VARIANT__VARIANT"],
                $_POST["C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES"],
                $_POST['C__CATG__APPLICATION_TYPE'],
                $_POST['C__CATG__APPLICATION_PRIORITY'],
                $_POST['C__CATG__APPLICATION_VERSION']
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
     * Executes the query to save the category entry given by its ID $p_cat_level.
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
     * @throws  Exception
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID, $p_description, $p_licence, $p_database_schemata_obj, $p_it_service_obj, $p_variant = null, $p_bequest_nagios_services = null, $p_type = null, $p_priority = null, $p_version = null)
    {
        $l_old_data = $this->get_data($p_cat_level)
            ->get_row();

        // Update isys_connection
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection->update_connection($l_old_data["isys_catg_application_list__isys_connection__id"], $p_connectedObjID);
        $p_it_service_obj = (is_array($p_it_service_obj)) ? $p_it_service_obj : trim($p_it_service_obj);

        if (isys_format_json::is_json_array($p_it_service_obj))
        {
            $p_it_service_obj = isys_format_json::decode($p_it_service_obj);
        } // if

        if ($p_type === null)
        {
            $p_type = C__CATG__APPLICATION_TYPE__SOFTWARE;
        } // if

        if ($p_type != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
        {
            $p_priority = null;
        } // if

        // Update software assignment
        $l_strSql = "UPDATE isys_catg_application_list SET " . "isys_catg_application_list__description = " . $this->convert_sql_text(
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
            if ($p_priority == C__CATG__APPLICATION_PRIORITY__PRIMARY)
            {
                $this->make_primary_os($p_cat_level);
            } // if

            // Handle relation
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_data         = $this->get_data($p_cat_level)
                ->__to_array();

            $l_relation_dao->handle_relation(
                $p_cat_level,
                "isys_catg_application_list",
                ($p_type == C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM ? C__RELATION_TYPE__OPERATION_SYSTEM : C__RELATION_TYPE__SOFTWARE),
                $l_data["isys_catg_application_list__isys_catg_relation_list__id"],
                $l_data["isys_catg_application_list__isys_obj__id"],
                $p_connectedObjID
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

                    if ($p_database_schemata_obj > 0)
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
                            }
                        }
                    }
                    else
                    {
                        $l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                    }

                    $l_assigned_it_services = array_flip($this->get_assigned_it_services($l_data["isys_catg_application_list__isys_catg_relation_list__id"]));

                    if (is_array($p_it_service_obj) && count($p_it_service_obj))
                    {
                        foreach ($p_it_service_obj AS $l_it_serv_obj_id)
                        {
                            $l_it_service_res = $l_dao_its_comp->get_data(
                                null,
                                $l_it_serv_obj_id,
                                "AND isys_connection__isys_obj__id = " . $l_dao_its_comp->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                                null,
                                C__RECORD_STATUS__NORMAL
                            );

                            if ($l_it_service_res->num_rows() < 1)
                            {
                                $l_dao_its_comp->create($l_it_serv_obj_id, C__RECORD_STATUS__NORMAL, $l_rel_data["isys_catg_relation_list__isys_obj__id"], "");
                            }
                            else
                            {
                                unset($l_assigned_it_services[$l_it_serv_obj_id]);
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
                }
            }

            return true;
        }
        else
        {
            return false;
        }
    } // function

    /**
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
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_variant'                  => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
                    C__PROPERTY__INFO__DESCRIPTION => 'The assigned variant for the application assignment'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_collected_variants'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => false
                ]
            ],
            '_assigned_database_schema' => [
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
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_assigned_database_schema'
                    ]
                ]
            ]
        ];
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
        $l_sql = 'SELECT * FROM isys_catg_application_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_application_manufacturer ON isys_cats_application_list__isys_application_manufacturer__id = isys_application_manufacturer__id
			LEFT JOIN isys_cats_app_variant_list ON isys_cats_app_variant_list__id = isys_catg_application_list__isys_cats_app_variant_list__id
			LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
			WHERE TRUE ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= ' ' . $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND isys_catg_application_list__id = " . $this->convert_sql_id($p_catg_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_application_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ' ' . $p_condition . ';');
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
        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                return ' AND (isys_catg_application_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                return ' AND (isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            } // if
        } // if
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        return [
            'application'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'The application object'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD            => 'isys_catg_application_list__isys_connection__id',
                        //                        C__PROPERTY__DATA__FIELD_ALIAS => 'software_id',
                        C__PROPERTY__DATA__RELATION_TYPE    => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_application',
                                'callback_property_relation_type_handler'
                            ]
                        ),
                        C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                            [
                                'isys_cmdb_dao_category_g_application',
                                'callback_property_relation_handler'
                            ], ['isys_cmdb_dao_category_g_application']
                        ),
                        C__PROPERTY__DATA__REFERENCES       => [
                            'isys_connection',
                            'isys_connection__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_OBJ_APPLICATION',
                        C__PROPERTY__UI__PARAMS => [
                            'catFilter' => 'C__CATS__SERVICE;C__CATS__APPLICATION;C__CATS__LICENCE;C__CATS__DATABASE_SCHEMA;C__CATS__CLUSTER_SERVICE;C__CATS__DBMS;C__CATS__DATABASE_INSTANCE;C__CATS__MIDDLEWARE'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
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
                        C__PROPERTY__PROVIDES__SEARCH     => false,
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
                        C__PROPERTY__PROVIDES__SEARCH     => false,
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
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__LICENCE',
                            'secondSelection'                           => true,
                            'secondList'                                => 'isys_cmdb_dao_category_s_lic::object_browser',
                            'secondListFormat'                          => 'isys_cmdb_dao_category_s_lic::format_selection',
                            'readOnly'                                  => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
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
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__DATABASE_SCHEMA',
                            'p_strPopupType'                            => 'browser_object_ng',
                            'p_strValue'                                => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_application',
                                    'callback_property_assigned_database_schema'
                                ]
                            ),
                            'p_strSelectedID'                           => ''
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
                            'multiselection'                            => true
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
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_VARIANT__VARIANT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_application',
                                    'callback_property_assigned_variant'
                                ]
                            ),
                            'p_strClass' => 'input-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'application_property_assigned_variant'
                        ]
                    ]
                ]
            ),
            'assigned_version'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'The assigned version for the application assignment'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_catg_version_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_version_list',
                            'isys_catg_version_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__APPLICATION_VERSION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_application',
                                    'callback_property_assigned_version'
                                ]
                            ),
                            'p_strClass' => 'input-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'application_property_assigned_version'
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
    }

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
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['application'][C__DATA__VALUE],
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
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['application'][C__DATA__VALUE],
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

                        return $p_category_data['data_id'];
                    } // if
            } // switch
        } // if

        return false;
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
     * @throws  Exception
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description, $p_licence = null, $p_database_schemata_obj = null, $p_it_service_obj = null, $p_variant = null, $p_bequest_nagios_services = 1, $p_type = null, $p_priority = null, $p_version = null)
    {
        $l_connection = isys_cmdb_dao_connection::instance($this->m_db);

        if ($p_type === null)
        {
            $p_type = C__CATG__APPLICATION_TYPE__SOFTWARE;
        } // if

        if ($p_type != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
        {
            $p_priority = null;
        } // if

        $l_sql = "INSERT INTO isys_catg_application_list SET
			isys_catg_application_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_connectedObjID)) . ",
			isys_catg_application_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
			isys_catg_application_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_application_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_application_list__isys_cats_app_variant_list__id = " . $this->convert_sql_id($p_variant) . ",
			isys_catg_application_list__isys_cats_lic_list__id = " . $this->convert_sql_id($p_licence) . ",
			isys_catg_application_list__bequest_nagios_services = " . $this->convert_sql_boolean($p_bequest_nagios_services) . ",
			isys_catg_application_list__isys_catg_application_type__id = " . $this->convert_sql_id($p_type) . ",
			isys_catg_application_list__isys_catg_application_priority__id = " . $this->convert_sql_id($p_priority) . ",
			isys_catg_application_list__isys_catg_version_list__id = " . $this->convert_sql_id($p_version) . ";";

        if ($this->update($l_sql) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            if ($p_priority == C__CATG__APPLICATION_PRIORITY__PRIMARY)
            {
                $this->make_primary_os($l_last_id, $p_objID);
            } // if

            // Handle software relation.
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_relation_dao->handle_relation(
                $l_last_id,
                "isys_catg_application_list",
                ($p_type == C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM ? C__RELATION_TYPE__OPERATION_SYSTEM : C__RELATION_TYPE__SOFTWARE),
                null,
                $p_objID,
                $p_connectedObjID
            );

            if ($p_connectedObjID > 0)
            {
                $l_data = $this->get_data($l_last_id)
                    ->get_row();

                if ($l_data["isys_catg_application_list__isys_catg_relation_list__id"] != "")
                {
                    if (isys_format_json::is_json_array($p_it_service_obj))
                    {
                        $p_it_service_obj = isys_format_json::decode($p_it_service_obj);
                    } // if

                    if ($p_database_schemata_obj > 0 || count($p_it_service_obj) > 0)
                    {
                        $l_rel_data = $l_relation_dao->get_data($l_data["isys_catg_application_list__isys_catg_relation_list__id"])
                            ->get_row();
                    } // if

                    if ($p_database_schemata_obj > 0)
                    {
                        $l_dao_dbms_access = isys_cmdb_dao_category_s_database_access::instance($this->get_database_component());
                        $l_dao_dbms_access->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
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

    /**
     * Set Status for category entry.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_status($p_cat_id, $p_status)
    {
        $l_sql = 'UPDATE isys_catg_application_list SET isys_catg_application_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_catg_application_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Deletes connection between application and object.
     *
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function delete($p_cat_level)
    {

        $l_catdata = $this->get_data($p_cat_level)
            ->get_row();

        isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
            ->delete_relation($l_catdata["isys_catg_application_list__isys_catg_relation_list__id"]);

        $this->update('DELETE FROM isys_catg_application_list WHERE isys_catg_application_list__id = ' . $this->convert_sql_id($p_cat_level) . ';');

        if ($this->apply_update())
        {
            return true;
        }
        else
        {
            throw new isys_exception_cmdb("Could not delete id '" . $p_cat_level . "' in table isys_catg_application_list.");
        } // if
    } // function

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'application'      => [
                    'value' => $p_data['application']
                ],
                'assigned_license' => [
                    'value' => $p_data['assigned_license']
                ],
                'assigned_version' => [
                    'value' => $p_data['assigned_version']
                ],
                'description'      => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    } // function

    /**
     * Compares category data for import.
     *
     * @todo Currently, every transformation (using helper methods) are skipped.
     * If your unique properties needs them, implement it!
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     */
    public function compare_category_data(&$p_category_data_values, &$p_object_category_dataset, &$p_used_properties, &$p_comparison, &$p_badness, &$p_mode, &$p_category_id, &$p_unit_key, &$p_category_data_ids, &$p_local_export, &$p_dataset_id_changed, &$p_dataset_id, &$p_logger, &$p_category_name = null, &$p_table = null, &$p_cat_multi = null, &$p_category_type_id = null, &$p_category_ids = null, &$p_object_ids = null, &$p_already_used_data_ids = null)
    {
        $l_title = (!is_numeric(
            $p_category_data_values['properties']['application'][C__DATA__VALUE]
        )) ? $p_category_data_values['properties']['application'][C__DATA__VALUE] : $p_category_data_values['properties']['application']['title'];

        $l_version = null;

        if (is_numeric($p_category_data_values['properties']['application']['id']))
        {
            $p_category_data_values['properties']['application'][C__DATA__VALUE] = $p_category_data_values['properties']['application']['id'];
        } // if

        if (isset($p_category_data_values['properties']['assigned_version']))
        {
            $l_version = $p_category_data_values['properties']['assigned_version']['ref_title'];
        } // if

        $l_candidate  = $l_candidate2 = new isys_array();
        $l_dataset_id = null;

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
        {
            $p_dataset_id_changed = false;
            $p_dataset_id         = $l_dataset[$p_table . '__id'];
            $l_dataset_id         = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id]))
            {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id)
            {
                //$p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS)
                {
                    continue;
                } // if
            } // if

            if ($l_dataset['isys_obj__title'] === $l_title && $l_dataset['isys_catg_version_list__title'] === $l_version)
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;
                $p_category_data_values['properties']['assigned_version'][C__DATA__VALUE]    = $l_dataset['isys_catg_version_list__id'];

                return;
            }
            if ($l_dataset['isys_obj__title'] === $l_title && $l_dataset['isys_catg_version_list__title'] !== $l_version)
            {
                // We found our dataset
                //$p_logger->debug('Dataset and category data are the same.');
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $l_candidate[$l_dataset_key]                                                      = $l_dataset[$p_table . '__id'];
                $l_candidate2[$l_dataset_key]                                                     = $l_dataset_id;
                //return;
            }
            else
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            } // if
        } // foreach

        if (count($l_candidate) === 1)
        {
            $l_key = key($l_candidate);
            unset($p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_key]);
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_key] = current($l_candidate);
        } // if
    } // function
} // class