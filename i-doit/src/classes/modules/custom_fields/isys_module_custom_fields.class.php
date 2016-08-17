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
define("C__CUSTOM_FIELDS__CONFIG", 1);

/**
 * i-doit
 *
 * Custom fields.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_custom_fields extends isys_module implements isys_module_interface
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var  boolean
     */
    protected static $m_licenced = true;

    /**
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Static method for retrieving the path, to the modules templates.
     * The template in ./modules/custom-fields is without the option object browser with relationships.
     *
     * @static
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_tpl_dir()
    {
        $l_dir = __DIR__ . DS . 'templates';

        if (!is_dir($l_dir))
        {
            return false;
        } // if

        return $l_dir . DS;
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_comp_template_language_manager;

        $l_parent = -1;

        if ($p_system_module)
        {
            $l_parent = $p_tree->find_id_by_title('Modules');
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(
                C__MODULE__CUSTOM_FIELDS . '0',
                $l_parent,
                $g_comp_template_language_manager->{'LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES'}
            );
        } // if

        $p_tree->add_node(
            C__MODULE__CUSTOM_FIELDS . '1',
            $l_root,
            _L('LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES'),
            isys_helper_link::create_url(
                [
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__CUSTOM_FIELDS,
                    C__GET__TREE_NODE     => C__MODULE__CUSTOM_FIELDS . '1'
                ]
            ),
            '',
            'images/icons/silk/application_form_add.png'
        );
    } // function

    /**
     * Starts module process.
     */
    public function start()
    {
        isys_auth_system_globals::instance()
            ->customfields(isys_auth::VIEW);

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_template = $this->m_userrequest->get_template();
            $l_tree     = $this->m_userrequest->get_menutree();

            $this->build_tree($l_tree, false);

            // Assign tree.
            $l_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        // Handle requests.
        $this->process();
    } // function

    /**
     * List generation.
     *
     * @return  mixed
     */
    public function get_category_list()
    {
        global $g_comp_database;

        $l_dao = new isys_custom_fields_dao($g_comp_database);

        $l_data = $l_dao->get_data();

        if ($l_data->num_rows() > 0)
        {
            $l_objList = new isys_component_list(ISYS_NULL, $l_data);
            $l_objList->set_row_modifier($this, "row_mod");

            $l_objList->config(
                [
                    "isysgui_catg_custom__title"            => "LC__CMDB__CATG__CATEGORY",
                    "isysgui_catg_custom__list_multi_value" => "LC__CMDB__CUSTOM_CATEGORIES__MULTIVALUE",
                    "field_count"                           => _L("LC__CMDB__CATG__QUANTITY") . " " . _L("LC__CMDB__CATG__CUSTOM_FIELDS"),
                    "assignment"                            => "LC__CMDB__CATG__INTERFACE_L__PORT_ALLOCATION",
                ],
                '?' . C__GET__MODULE_ID . "=" . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__CUSTOM_FIELDS . '&' . C__GET__TREE_NODE . '=' .
                $_GET[C__GET__TREE_NODE] . '&' . C__GET__SETTINGS_PAGE . '=' . C__CUSTOM_FIELDS__CONFIG . '&' . C__CMDB__GET__EDITMODE . '=' . C__EDITMODE__ON .
                "&id=[{isysgui_catg_custom__id}]",
                "[{isysgui_catg_custom__id}]",
                true
            );

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        }
        else
        {
            $l_navbar = isys_component_template_navbar::getInstance();
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__PURGE);

            return '<div class="p10">' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>';
        } // if
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req))
        {
            $this->m_userrequest = &$p_req;

            return true;
        } // if

        return false;
    } // function

    /**
     * Row modifier.
     *
     * @param  array &$p_row
     */
    public function row_mod(&$p_row)
    {
        global $g_comp_database, $g_dirs;

        $l_add_dots           = false;
        $p_row["field_count"] = count(unserialize($p_row["isysgui_catg_custom__config"]));

        $l_assigns   = isys_custom_fields_dao::instance($g_comp_database)
            ->get_assignments($p_row["isysgui_catg_custom__id"]);
        $l_obj_types = [];

        while ($l_a = $l_assigns->get_row())
        {
            if (count($l_obj_types) > 10)
            {
                $l_add_dots = true;
                break;
            } // if

            $l_obj_types[] .= _L($l_a["isys_obj_type__title"]);
        } // while

        sort($l_obj_types);

        if ($l_add_dots)
        {
            $l_obj_types[] = '...';
        } // if

        $p_row["assignment"]                            = implode(', ', $l_obj_types);
        $p_row["isysgui_catg_custom__list_multi_value"] = $p_row["isysgui_catg_custom__list_multi_value"] ? '<img src="' . $g_dirs['images'] .
            'icons/silk/bullet_green.png" class="vam mr5" /><span class="vam green">' . _L(
                'LC__UNIVERSAL__YES'
            ) . '</span>' : '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" class="vam mr5" /><span class="vam red">' . _L('LC__UNIVERSAL__NO') . '</span>';
    } // function

    /**
     * @param $p_config
     * @param $p_constant
     *
     * @return array
     */
    public function prepare_api_example_for_config($p_config, $p_constant)
    {
        $l_api_example = [
            'method'  => 'cmdb.category.create',
            'params'  => [
                'language' => isys_application::instance()->language,
                'apikey'   => 'your-key',
                'category' => $p_constant,
                'objID'    => 'your-object-id',
                'data'     => []
            ],
            'id'      => 1,
            'version' => '2.0'
        ];
        if (is_array($p_config))
        {
            foreach ($p_config as $l_key => $l_value)
            {
                switch ($l_value['type'])
                {
                    case 'f_text':
                    case 'f_textarea':
                    case 'f_wysiwyg':
                        $l_content = 'textual-content';
                        break;
                    case 'f_popup';
                        $l_content = null;

                        if ($l_value['popup'] == 'calendar')
                        {
                            $l_content = date('Y-m-d');
                        }
                        break;
                }

                if (isset($l_content))
                {
                    $l_api_example['params']['data'][$l_value['type'] . '_' . $l_key] = $l_content;
                }
                else
                {
                    $l_api_example['params']['data'][$l_value['type'] . '_' . $l_key] = null;
                }
            }
        }

        $l_return = [
            'create' => $l_api_example
        ];
        unset($l_api_example['params']['data']);
        $l_api_example['method'] = 'cmdb.category.read';

        $l_return['read'] = $l_api_example;
        unset($l_api_example);

        return $l_return;
    }

    /**
     * Process custom field configuration
     */
    public function process()
    {
        global $g_comp_template, $g_comp_database, $index_includes;;

        try
        {
            $l_navbar = isys_component_template_navbar::getInstance();

            $l_dao     = new isys_custom_fields_dao($g_comp_database);
            $l_tpl_dir = $this->get_tpl_dir();

            $l_process_filter = (isset($_POST['filter']) && !empty($_POST['filter']));

            // Delete a custom category.
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__PURGE)
            {
                if (isset($_POST["id"]) && is_array($_POST["id"]))
                {
                    foreach ($_POST["id"] as $l_id)
                    {
                        $l_dao->delete($l_id);
                    } // foreach
                }
            } // if

            $l_id = 0;
            if ($_POST[C__GET__NAVMODE] != C__NAVMODE__NEW)
            {
                $l_id = $_GET[C__GET__ID] ?: $_POST[C__GET__ID];
                if (is_array($l_id))
                {
                    $l_id = $l_id[0];
                } // if
            } // if

            // Switch back to list on cancel.
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__CANCEL || $_POST[C__GET__NAVMODE] == C__NAVMODE__PURGE)
            {
                unset($_GET["id"]);
                $l_id = null;
            } // if

            // Save custom fields and category.
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE && !$l_process_filter)
            {
                $l_id = $_POST[C__GET__ID];

                if (is_array($_POST["field_title"]))
                {
                    foreach ($_POST["field_title"] as $l_field_key => $l_field_title)
                    {
                        // Removing line breaks and tabs to prevent that the config can not be loaded
                        // Key fields should not have any line breaks or tabs
                        $l_field_title = str_replace(
                            [
                                "\r",
                                "\n",
                                "\t"
                            ],
                            "",
                            $l_field_title
                        );
                        /*
                         * This is necessary, for one reason:
                         *
                         * If we don't pre- or append a string the key will be an integer. This is a problem because:
                         * When we serialize integers errors might appear, when we change from a 32 to a 64 bit machine!
                         */
                        $l_key = 'c_' . ltrim($l_field_key, 'c_');

                        // Check if theres a corresponding type.
                        if (isset($_POST["field_type"][$l_field_key]))
                        {
                            $l_type = $_POST["field_type"][$l_field_key];

                            // If type contains a comma, its a popup which needs more infos.
                            if (strstr($l_type, ","))
                            {
                                $l_data = explode(",", $l_type);

                                if ($l_data[1] == 'yes-no')
                                {
                                    $l_config[$l_key] = [
                                        "type"  => $l_data[0],
                                        "extra" => $l_data[1],
                                        "title" => $l_field_title
                                    ];
                                }
                                else
                                {
                                    $l_config[$l_key] = [
                                        "type"  => $l_data[0],
                                        "popup" => $l_data[1],
                                        "title" => $l_field_title
                                    ];
                                } // if

                                // Dialog or Dialog+?
                                if ($l_data[1] == "dialog" || $l_data[1] == "dialog_plus")
                                {
                                    if (isset($_POST["field_dialog_identifier"][$l_field_key]))
                                    {
                                        $l_identifier = $_POST["field_dialog_identifier"][$l_field_key];

                                        if (empty($l_identifier))
                                        {
                                            $l_identifier = isys_glob_strip_accent(isys_glob_replace_accent($l_field_title), "_");
                                        } // if

                                        $l_config[$l_key]["identifier"] = $l_identifier;
                                    } // if

                                    if ($l_data[2] == 1)
                                    {
                                        $l_config[$l_key]['multiselection'] = 1;
                                    } // if
                                } // if

                                // Normal Object browser or with relation?
                                if ($l_data[1] == 'browser_object')
                                {
                                    if ($l_data[2] == 1)
                                    {
                                        $l_identifier                   = $_POST["field_relation"][$l_field_key];
                                        $l_config[$l_key]["identifier"] = $l_identifier;
                                    } // if

                                    if ($l_data[3] == 1)
                                    {
                                        $l_config[$l_key]['multiselection'] = 1;
                                    } // if
                                } // if
                            }
                            else
                            {
                                $l_config[$l_key] = [
                                    "type"  => $l_type,
                                    "title" => isys_helper_textformat::strip_scripts_tags($l_field_title, true)
                                ];
                            } // if
                        } // if

                        // Save show_in_list config.
                        if (isset($_POST["field_show_in_list"][$l_field_key]))
                        {
                            $l_config[$l_key]['show_in_list'] = 1;
                        }
                        else
                        {
                            $l_config[$l_key]['show_in_list'] = 0;
                        } // if
                    } // foreach
                } // if

                if (isset($l_config) && count($l_config) > 0)
                {
                    try
                    {
                        if ($l_id > 0)
                        {
                            $l_dao->save($l_id, $_POST["category_title"], $l_config, 0, 0, $_POST["multivalued"], $_POST['category_constant']);
                        }
                        else
                        {
                            $l_id = $l_dao->create($_POST["category_title"], $l_config, 0, 0, $_POST["multivalued"], $_POST['category_constant']);
                        } // if

                        // Clear all object type assignments.
                        $l_dao->clear_assignments($l_id);

                        // (Re-)Add selected object type assignments.
                        $l_otypes = explode(",", $_POST["object_types__selected_values"]);

                        // Add Generic template.
                        if (defined('C__OBJTYPE__GENERIC_TEMPLATE'))
                        {
                            if (!in_array(C__OBJTYPE__GENERIC_TEMPLATE, $l_otypes))
                            {
                                $l_otypes[] = C__OBJTYPE__GENERIC_TEMPLATE;
                            } // if
                        } // if

                        if (is_array($l_otypes))
                        {
                            foreach ($l_otypes as $l_obj_type)
                            {
                                if ($l_obj_type > 0)
                                {
                                    $l_dao->assign($l_id, $l_obj_type);
                                } // if
                            } // foreach
                        } // if

                        // Update constant cache:
                        isys_component_constant_manager::instance()
                            ->clear_dcm_cache();

                        isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));
                        unset($_GET["id"], $l_id);
                    }
                    catch (Exception $e)
                    {
                        isys_notify::error($e->getMessage(), ['sticky' => true]);

                        // Activate edit mode
                        $g_comp_template->activate_editmode();
                    } // try
                }
                else
                {
                    isys_application::instance()->container['notify']->error("Specify your fields, please.");
                } // if
            } // if

            $l_edit_right   = isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/CUSTOMFIELDS');
            $l_delete_right = isys_auth_system::instance()
                ->is_allowed_to(isys_auth::DELETE, 'GLOBALSETTINGS/CUSTOMFIELDS');

            switch ($_GET[C__GET__SETTINGS_PAGE])
            {
                default:
                case C__CUSTOM_FIELDS__CONFIG:
                    $l_rules = $l_rel_data = [];

                    $l_dao_rel            = new isys_cmdb_dao_category_g_relation($g_comp_database);
                    $l_relation_type_data = $l_dao_rel->get_relation_types_as_array(null, C__RELATION__IMPLICIT);

                    // Necessary logic for displaying a sorted list of relation types.
                    foreach ($l_relation_type_data as $l_relation_type)
                    {
                        $l_rel_data[$l_relation_type['title_lang']] = $l_relation_type;
                    } // foreach

                    ksort($l_rel_data);

                    $g_comp_template->assign("relation_types", isys_format_json::encode(array_values($l_rel_data)))
                        ->assign("id", null);

                    // Init vars
                    $l_title = $l_selected = $l_multivalued = $l_constant = null;

                    // New or Edit.
                    if (isset($l_id) && $l_id > 0 && $_POST[C__GET__NAVMODE] != C__NAVMODE__NEW)
                    {
                        $l_data                               = $l_dao->get_data($l_id);
                        $l_row                                = $l_data->get_row();
                        $l_title                              = $l_row["isysgui_catg_custom__title"];
                        $l_multivalued                        = $l_row["isysgui_catg_custom__list_multi_value"];
                        $l_constant                           = $l_row['isysgui_catg_custom__const'];
                        $l_row["isysgui_catg_custom__config"] = str_replace("'", "\"", $l_row["isysgui_catg_custom__config"]);
                        $l_config                             = unserialize($l_row["isysgui_catg_custom__config"]);

                        $g_comp_template->assign("category_config", $l_config)// Prepare API Example
                        ->assign('apiExample', $this->prepare_api_example_for_config($l_config, $l_constant))
                            ->assign('id', $l_id)
                            ->assign('entryCount', $l_dao->count($l_id))
                            ->assign('valueCount', $l_dao->count_values($l_id));

                        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                            ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                            ->set_visible(true, C__NAVBAR_BUTTON__SAVE);
                    }
                    else
                    {
                        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW)
                        {
                            $_GET[C__CMDB__GET__EDITMODE] = 1;
                            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                                ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                                ->set_visible(true, C__NAVBAR_BUTTON__SAVE);
                        }
                        else
                        {
                            $l_list = $this->get_category_list();
                            $g_comp_template->assign("g_list", $l_list);
                            $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
                            $l_navbar->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
                                ->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                                ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
                                ->set_visible(true, C__NAVBAR_BUTTON__PURGE)
                                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                                ->set_visible(
                                    true,
                                    C__NAVBAR_BUTTON__NEW
                                )// We use this line to prevent the breadcrumb from loading - because this will trigger a "History.pushState()" call.
                                ->set_js_onclick(
                                    "$('sort').setValue(''); $('navMode').setValue('" . C__NAVMODE__EDIT . "'); form_submit(false, false, false, true);",
                                    C__NAVBAR_BUTTON__EDIT
                                );
                        }
                    } // if

                    $l_cmdb_dao     = isys_cmdb_dao::instance($g_comp_database);
                    $l_object_types = $l_cmdb_dao->get_objtype();
                    $l_otypes       = [];

                    while ($l_row = $l_object_types->get_row())
                    {
                        // Skip generic Template.
                        if ($l_row['isys_obj_type__id'] == C__OBJTYPE__GENERIC_TEMPLATE) continue;

                        if (isset($l_id) && $l_id > 0)
                        {
                            $l_sel      = $l_dao->get_assignments($l_id, $l_row["isys_obj_type__id"]);
                            $l_selected = ($l_sel->num_rows() > 0);
                        } // if

                        $l_row["isys_obj_type__title"] = _L($l_row["isys_obj_type__title"]);

                        $l_otypes[$l_row["isys_obj_type__title"]] = [
                            "val" => $l_row["isys_obj_type__title"],
                            "hid" => 0,
                            "sel" => $l_selected,
                            "id"  => $l_row["isys_obj_type__id"]
                        ];
                    } // while

                    ksort($l_otypes);

                    // Set smarty rules
                    $l_rules['multivalued']['p_arData']         = serialize(get_smarty_arr_YES_NO());
                    $l_rules['multivalued']['p_strSelectedID']  = $l_multivalued;
                    $l_rules['multivalued']['p_bDbFieldNN']     = 1;
                    $l_rules['category_title']['p_strValue']    = $l_title;
                    $l_rules['object_types']['p_arData']        = array_values($l_otypes);
                    $l_rules['category_constant']['p_strValue'] = $l_constant;

                    $g_comp_template->assign('content_title', _L('LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES'))
                        ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                        ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

                    $index_includes['contentbottomcontent'] = $l_tpl_dir . "/config.tpl";
                    break;
            } // switch

        } catch (Exception $e)
        {
            throw $e;
        }
    } // function
} // class