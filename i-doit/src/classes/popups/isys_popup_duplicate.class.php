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
 * Popup for object duplication
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_duplicate extends isys_component_popup
{
    /**
     * This array will hold the IDs of all newly created objects.
     *
     * @var  array
     */
    protected $m_imported_objects = [];

    /**
     * Handles Smarty inclusion.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template $p_tplclass (unused)
     * @param   mixed                   $p_params   (unused)
     *
     * @return  string
     */
    public function handle_smarty_include(&$p_tplclass, $p_params)
    {
        global $g_config;

        $l_url = $g_config['startpage'] . '? ' . 'mod=cmdb&' . C__CMDB__GET__POPUP . '=duplicate&' . C__CMDB__GET__EDITMODE . '=' . C__EDITMODE__ON . '&' . C__CMDB__GET__OBJECTTYPE . '=' . $_GET[C__CMDB__GET__OBJECTTYPE];

        $this->set_config('width', 1000);
        $this->set_config('height', 1000);
        $this->set_config('scrollbars', 'no');

        return $this->process($l_url, true);
    } // function

    /**
     * Handles module request.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_module_request     $p_modreq (unused)
     *
     * @return  isys_component_template
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;

        $l_cmdb_dao = new isys_cmdb_dao($g_comp_database);

        // Prepare template for popup.
        $l_tplpopup = isys_component_template::instance();
        $l_tplpopup->assign('file_body', 'popup/duplicate.tpl');

        switch ($_GET[C__CMDB__GET__OBJECTTYPE])
        {
            case C__OBJTYPE__PERSON:
            case C__OBJTYPE__PERSON_GROUP:
            case C__OBJTYPE__ORGANIZATION:
                $l_custom_name = false;
                break;
            default:
                $l_custom_name = true;
                break;
        } // switch

        if (isset($_POST["id"]) && is_array($_POST["id"]) && count($_POST["id"]) == 1)
        {
            $l_tplpopup->assign("object_title", html_entity_decode($l_cmdb_dao->get_obj_name_by_id_as_string($_POST["id"][0]), null, ""));
        }
        else
        {
            $l_tplpopup->assign("object_title", "");
        }
        $l_categories = [];

        // Assign durable global categories:
        $l_cat                = $l_cmdb_dao->get_durable_catg();
        $l_skipped_categories = isys_export_cmdb_object::get_skipped_categories(C__CMDB__CATEGORY__TYPE_GLOBAL);
        while ($l_row = $l_cat->get_row())
        {
            if (class_exists($l_row['isysgui_catg__class_name']))
            {
                $l_check_cat = new $l_row['isysgui_catg__class_name']($g_comp_database);
                if (count($l_check_cat->get_properties()) > 0)
                {
                    if (isys_export_cmdb_object::is_catg_exportable($l_check_cat->get_properties()))
                    {
                        $l_categories[] = [
                            C__GET__ID => $l_row['isysgui_catg__id'],
                            'title'    => $l_row['isysgui_catg__title']
                        ];
                    } // if
                } // if
            } // if
        } // while

        // Assign global categories:
        $l_cat = $l_cmdb_dao->get_catg_by_obj_type($_GET[C__CMDB__GET__OBJECTTYPE]);
        while ($l_row = $l_cat->get_row())
        {
            // Don´t show skipped categories in gui
            if (array_key_exists(
                    $l_row['isysgui_catg__id'],
                    $l_skipped_categories
                ) || $l_row['isysgui_catg__const'] == 'C__CATG__VIRTUAL' || $l_row['isysgui_catg__const'] == 'C__CATG__GLOBAL'
            )
            {
                // @todo C__CATG__GLOBAL is already set in durable catg above.
                continue;
            } // if

            if (class_exists($l_row['isysgui_catg__class_name']))
            {
                $l_check_cat = new $l_row['isysgui_catg__class_name']($g_comp_database);
                if (count($l_check_cat->get_properties()) > 0)
                {
                    if (isys_export_cmdb_object::is_catg_exportable($l_check_cat->get_properties()))
                    {
                        $l_categories[] = [
                            C__GET__ID => $l_row['isysgui_catg__id'],
                            'title'    => $l_row['isysgui_catg__title']
                        ];
                    } // if
                } // if
            } // if
        } // while

        // Assign custom categories:
        $l_cat_custom  = $l_cmdb_dao->get_catg_custom_by_obj_type($_GET[C__CMDB__GET__OBJECTTYPE]);
        $l_catg_custom = [];

        while ($l_row = $l_cat_custom->get_row())
        {
            $l_catg_custom[] = $l_row;
        } // while

        return $l_tplpopup->assign('custom_categories', $l_catg_custom)
            ->assign('categories', $l_categories)
            ->assign('customName', $l_custom_name);
    } // function

    /**
     * Duplicates object.
     *
     * @todo Use a shorter way to duplicate: Instead of making a complete import
     * after making a complete export, just transform data to the new data
     * structure.
     *
     * @global  isys_component_session  $g_comp_session
     * @global  isys_component_database $g_comp_database
     * @return  boolean
     */
    public function duplicate()
    {
        global $g_comp_session, $g_comp_database;

        $l_return = [
            'success' => true,
            'data' => [],
            'message' => null,
        ];

        try
        {

            $g_comp_session->write_close();

            // Start logging:
            $l_log = isys_factory_log::get_instance('duplicate');
            $l_log->set_verbose_level(isys_log::C__NONE);

            // Retrieve objects:
            $l_objects = [];
            if (isset($_POST[C__GET__ID]) && $_POST[C__GET__ID] > 0)
            {
                $l_objects = $_POST[C__GET__ID];
            }
            elseif (isset($_POST['objects']))
            {
                $l_objects = explode(',', $_POST['objects']);
            } //if

            /**
             * @var $l_cmdb_dao isys_cmdb_dao_object_type
             */
            $l_cmdb_dao = isys_cmdb_dao_object_type::instance($g_comp_database);

            // Iterate though objects:
            $l_object_type = null;
            foreach ($l_objects as $l_object_id)
            {
                // Determine object type identifier:
                if (!isset($l_object_type))
                {
                    $l_object_type = $l_cmdb_dao->get_objTypeID($l_object_id);
                } //if
            } //foreach object

            // Retrieve categories:
            $l_categories = [];
            // Global categories:
            if (isset($_POST['category']))
            {
                $l_catg                                       = $_POST['category'];
                $l_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = $l_catg;
            }
            // Specific categories:
            if (isset($_POST['export_specific_catg']))
            {
                $l_cats         = [];
                $l_cats_dataset = $l_cmdb_dao->get_specific_category(
                    $l_object_type,
                    C__RECORD_STATUS__NORMAL,
                    null,
                    true
                )
                    ->__as_array();
                foreach ($l_cats_dataset as $l_cats_data)
                {
                    $l_cats[] = $l_cats_data['isysgui_cats__id'];
                }
                $l_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $l_cats;
            } //if
            // Custom categories:
            if (isset($_POST['custom_category']))
            {
                $l_catc                                       = $_POST['custom_category'];
                $l_categories[C__CMDB__CATEGORY__TYPE_CUSTOM] = $l_catc;
            } //if

            // Export data...
            $l_export = new isys_export_cmdb_object(
                'isys_export_type_xml', $g_comp_database
            );

            $l_parser = $l_export->export(
                $l_objects,
                $l_categories,
                C__RECORD_STATUS__NORMAL,
                true
            )
                ->parse();

            $l_data = isys_glob_utf8_encode($l_parser->get_export());

            unset($l_export, $l_parser);

            // ...and import it:
            $l_import = new isys_import_handler_cmdb($l_log, $g_comp_database);
            $l_import->set_option('update-object-changed', false)
                ->set_mode(isys_import_handler_cmdb::C__APPEND)
                ->set_multivalue_categories_mode(isys_import_handler_cmdb::C__APPEND);

            if (isset($_POST['update_globals']))
            {
                $l_import->set_update_globals();
            }
            $l_import->load_xml_data($l_data);

            if ($l_import->parse() === false)
            {
                return false;
            } // if

            $l_import->prepare();

            // Set title inside import method
            foreach ($l_objects AS $l_object_id)
            {
                $l_title = null;
                if (count($l_objects) > 1)
                {
                    // There are more than one objects to be duplicated, so the need their names:
                    if (isset($_POST['object_title']) && $_POST['object_title'] != '')
                    {
                        $l_title = $_POST['object_title'];
                    }
                    else
                    {
                        $l_title = $l_cmdb_dao->get_obj_name_by_id_as_string($l_object_id);
                    } // if
                }
                else if (isset($_POST['object_title']))
                {
                    // Only one object:
                    assert('isset($_POST["object_title"]) && is_string($_POST["object_title"])');
                    $l_title = $_POST['object_title'];
                } // if

                // Set title:
                if ($l_title !== null)
                {
                    $l_import->set_replaced_title($l_title, $l_object_id);
                } // if
            }

            if ($l_import->import() === false)
            {
                return false;
            } // if

            $l_object_ids = $l_import->get_object_ids();
            unset($l_import);

            $this->m_imported_objects = [];

            $l_auth_dao = new isys_auth_dao($g_comp_database);
            foreach ($l_objects as $l_object_id)
            {
                // Skip objects that could not be duplicated:
                if (!isset($l_object_ids[$l_object_id]))
                {
                    continue;
                } // if

                // Call custom duplication methods:
                if ($l_cmdb_dao->has_cat($l_object_type,
                    [
                        'C__CATS__PERSON_GROUP',
                        'C__CATS__PERSON'
                    ]
                )
                )
                {
                    $l_auth_dao->duplicate($l_object_id, $l_object_ids[$l_object_id]);
                } // if

                // Handle options:
                $this->handle_options($l_object_ids[$l_object_id]);

                $this->m_imported_objects[] = $l_object_ids[$l_object_id];
            } // foreach

            $l_return['data']['imported'] = array_unique($this->m_imported_objects);

            unset($l_cmdb_dao);
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        if (isset($_POST['open_new_created_object']) && $_POST['open_new_created_object'] == 'on')
        {
            // Redirect directly inside the newly created object. See Jira ticket: ID-1429.
            $l_return['data']['redirect'] = isys_helper_link::create_url([C__CMDB__GET__OBJECT => end($this->get_imported_objects())]);
            $l_return['data']['url'] = isys_helper_link::get_base() . 'cmdb/object/' . end($this->get_imported_objects());
        } // if

        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);

        // End the request.
        die;
    } // function

    /**
     * Method for retrieving the newly created object IDs.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_imported_objects()
    {
        return $this->m_imported_objects;
    } // function

    /**
     * Handles options given by the duplicate dialog.
     *
     * @global  isys_component_database $g_comp_database Database component
     *
     * @param   integer                 $p_object_id
     */
    private function handle_options($p_object_id)
    {
        global $g_comp_database;

        switch ($_POST['duplicate_options'])
        {
            case 'virtualize':
                $l_dao = new isys_cmdb_dao_category_g_virtual_machine($g_comp_database);
                $l_dao->set_vm_status($p_object_id, C__VM__GUEST);
                break;

            case 'devirtualize':
                $l_dao = new isys_cmdb_dao_category_g_virtual_machine($g_comp_database);
                $l_dao->set_vm_status($p_object_id, C__VM__NO);
                break;
        } // switch
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (!defined('C__MODULE__EXPORT') || !class_exists('isys_module_export'))
        {
            throw new isys_exception_general('Export module is not installed.');
        } // if

        set_time_limit(60 * 60 * 24);
    } // function
} // class
?>