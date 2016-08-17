<?php

/**
 * i-doit
 *
 * Quick configuration wizard module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_module_quick_configuration_wizard extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines whether this module will be displayed in the named menus:
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var  boolean
     */
    protected static $m_licenced = true;
    /**
     * Blacklist array for some categories, which shall not appear in the QCW.
     *
     * @var  array
     */
    protected $m_category_blacklist = [
        'C__CATG__GLOBAL',
        'C__CATG__RELATION',
        'C__CATG__LOGBOOK',
        'C__CATG__VIRTUAL_AUTH'
    ];
    /**
     * Instance of module DAO.
     *
     * @var  isys_quick_configuration_wizard_dao
     */
    protected $m_dao;
    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * Standard encoding.
     *
     * @var  string
     */
    protected $m_encoding = 'utf-8';
    /**
     * User request.
     *
     * @var  isys_module_request
     */
    protected $m_userrequest;

    /**
     * Static factory method for instant method chaining.
     *
     * @static
     * @return  isys_module_jdisc
     */
    public static function factory()
    {
        return new self;
    } //function

    /**
     * Gets all profile files in an array
     *
     * @static
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_config_files()
    {
        global $g_absdir;

        $l_dir_handle = opendir($g_absdir . DS . 'imports/qcw');

        $l_file_arr = [];
        while ($l_file = readdir($l_dir_handle))
        {
            if ($l_file == '.' || $l_file == '..' || substr($l_file, strlen($l_file) - 4, strlen($l_file)) != '.xml') continue;

            $l_file_arr[] = $l_file;
        }

        return $l_file_arr;
    } // function

    /**
     * Deletes the specified profile file
     *
     * @static
     *
     * @param   string $p_file_name
     *
     * @return  bool
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function delete_config_file($p_file_name)
    {
        if ($p_file_name != '' && substr($p_file_name, strlen($p_file_name) - 4, strlen($p_file_name)) == '.xml')
        {
            global $g_absdir;

            $l_file_name = $g_absdir . DS . 'imports' . DS . 'qcw' . DS . $p_file_name;

            if (file_exists($l_file_name))
            {
                return unlink($l_file_name);
            }
            else
            {
                return false;
            } // if
        } // if

        return true;
    } // function

    //////////////////////////////////
    //			START EXPORT		//
    //////////////////////////////////

    /**
     * Initiates module.
     *
     * @param isys_module_request $p_req
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = $p_req;
    } // function

    /**
     * Builds menu tree.
     *
     * @param  isys_component_tree $p_tree
     * @param  boolean             $p_system_module (optional) Is it a system module? Defaults to true.
     * @param  integer             $p_parent        (optional) Parent identifier. Defaults to null.
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_comp_template_language_manager, $g_config;

        $l_tmpget[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECTTYPE;

        $i = 0;
        if ($p_system_module) $l_root = $p_parent;
        else
            $l_root = $p_tree->add_node(
                C__MODULE__QCW . ++$i,
                $p_parent,
                $g_comp_template_language_manager->get('LC__CMDB__OBJTYPE__CONFIGURATION_MODUS')
            );

        $p_tree->set_tree_sort(false);

        if (isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EXECUTE, 'GLOBALSETTINGS/QCW')
        )
        {
            $p_tree->add_node(
                C__MODULE__QCW . ++$i,
                $l_root,
                $g_comp_template_language_manager->get('LC__CMDB__TREE__SYSTEM__CMDB_CONFIGURATION__QOC'),
                $g_config['www_dir'] . 'index.php?' . (($p_system_module) ? C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . "&" . C__GET__MODULE_SUB_ID . "=" . C__MODULE__QCW : C__GET__MODULE_ID . "=" . C__MODULE__QCW) . '&' . C__GET__TREE_NODE . '=' . C__MODULE__QCW . $i,
                null,
                'images/icons/silk/application_form_edit.png',
                0,
                '',
                ''
            );
        }

    }

    /**
     * Start method.
     *
     * @global  array $index_includes
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  isys_module_events|void
     */
    public function start()
    {
        global $index_includes, $g_absdir;

        if (isset($_GET['dl']) && !empty($_GET['dl']))
        {
            // Remove slashes for a little bit more security...
            $_GET['dl'] = str_replace(
                [
                    '/',
                    '\\'
                ],
                '',
                $_GET['dl']
            );

            if (!file_exists($g_absdir . DS . 'imports' . DS . 'qcw' . DS . $_GET['dl']))
            {
                unset($_GET['dl']);

                header('Location: ' . isys_helper_link::create_url($_GET));
            } // if

            isys_factory::get_instance('isys_component_filemanager')
                ->set_upload_path($g_absdir . DS . 'imports' . DS . 'qcw' . DS)
                ->send($_GET['dl']);
        } // if

        $l_tree = $this->m_userrequest->get_menutree();

        if ($_GET[C__GET__MODULE_SUB_ID] != C__MODULE__QCW) $this->build_tree($l_tree, false, -1);

        isys_auth_system_globals::instance()
            ->qcw(isys_auth::EXECUTE);
        if (isset($_FILES['import_file']))
        {
            try
            {
                $l_destination = BASE_DIR . 'imports' . DS . 'qcw' . DS;
                isys_helper_upload::save($_FILES['import_file'], null, $l_destination);
            }
            catch (isys_exception_filesystem $e)
            {
                $l_error = isys_helper_upload::get_error('import_file');

                if ($l_error === false)
                {
                    $l_error = $e->getMessage();
                } // if

                $this->m_userrequest->get_template()
                    ->assign('upload_error', $l_error);
            } // try
        } // if

        if (isset($_POST['C__MODULE__QCW__CONFIG_TITLE']) && !empty($_POST['C__MODULE__QCW__CONFIG_TITLE']))
        {
            $l_config_title = trim($_POST['C__MODULE__QCW__CONFIG_TITLE']);
            $this->generate_export($l_config_title);
        } // if

        $l_groups = $l_types = $l_cats = [];

        $l_obj_type_groups = $this->m_dao->load_objecttypes_group(true);
        $l_obj_types       = $this->m_dao->load_objecttypes(null, true);
        $l_categories      = $this->m_dao->load_categories();

        foreach ($l_obj_type_groups as $l_group)
        {
            $l_groups[$l_group['isys_obj_type_group__const']] = [
                'name'        => _L($l_group['isys_obj_type_group__title']),
                'selfdefined' => (strpos($l_group['isys_obj_type_group__const'], 'C__OBJTYPE_GROUP__SD_') === false) ? false : true,
                'active'      => ($l_group['isys_obj_type_group__status'] == C__RECORD_STATUS__NORMAL) ? true : false
            ];
        } // foreach

        $l_cnt = 0;

        foreach ($l_obj_types as $l_obj_type)
        {
            $l_types[str_pad($l_obj_type['isys_obj_type__sort'], 3, '0', STR_PAD_LEFT) . '-' . $l_cnt] = [
                'title'       => _L($l_obj_type['isys_obj_type__title']),
                'const'       => _L($l_obj_type['isys_obj_type__const']),
                'selfdefined' => (bool) $l_obj_type['isys_obj_type__selfdefined'],
                'active'      => ($l_obj_type['isys_obj_type__show_in_tree'] == 1 && $l_obj_type['isys_obj_type__isys_obj_type_group__id'] > 0) ? true : false,
                'used_in'     => ($l_obj_type['isys_obj_type__show_in_tree'] == 1 && $l_obj_type['isys_obj_type_group__title'] !== null) ? _L(
                    $l_obj_type['isys_obj_type_group__title']
                ) : false,
                'container'   => $l_obj_type['isys_obj_type__container'],
                'insertion'   => $l_obj_type['isys_obj_type__show_in_rack']
            ];

            $l_cnt++;
        } // foreach

        $l_alphabetical_sort = (isys_tenantsettings::get(
                'cmdb.registry.object_type_sorting',
                C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC
            ) == C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC);

        // We check, if the object-types should be sorted by name (alphabetically) or manually.
        if ($l_alphabetical_sort)
        {
            // Sort array by title.
            if (is_array($l_types))
            {
                usort($l_types, 'isys_glob_array_compare_title');
            } // if
        }
        else
        {
            ksort($l_types);
        } // if

        foreach ($l_categories as $l_category)
        {
            if (!in_array($l_category['const'], $this->m_category_blacklist))
            {
                $l_cats[$l_category['const']] = $l_category;
            } // if
        } // foreach

        // Prepare a link to the custom-field module.
        $l_module_link = '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__CUSTOM_FIELDS . '&' . C__GET__TREE_NODE . '=' . C__MODULE__CUSTOM_FIELDS . '1' . '&' . C__GET__SETTINGS_PAGE . '=1';

        $l_download_params = $_GET;
        $l_download_link   = isys_helper_link::create_url($l_download_params);

        $this->m_userrequest->get_template()
            ->activate_editmode()
            ->assign("encType", "multipart/form-data")
            ->assign('obj_type_sorting_alphabetical', $l_alphabetical_sort)
            ->assign('obj_type_groups', $l_groups)
            ->assign('obj_types', array_values($l_types))
            ->assign('categories', $l_cats)
            ->assign('user_category_module', $l_module_link)
            ->assign('config_files', self::get_config_files())
            ->assign('download_link', $l_download_link . '&dl=')
            ->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]));

        $index_includes['contentbottomcontent'] = $this->get_template_dir() . 'quick_configuration_wizard.tpl';
    }

    /**
     * This function generates a xml file with the current objecttype configuration.
     *
     * @param   string $p_config_title
     *
     * @return  string
     * @throws  Exception
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function generate_export($p_config_title)
    {
        global $g_absdir;

        $l_pattern = "/[\*\!\"\§\$\%\&\/\\\(\)\=\']/";

        if ($p_config_title == '')
        {
            $p_config_title = 'default';
        } // if

        $l_config_title = preg_replace($l_pattern, '', $p_config_title);

        $l_qcw_folder    = DS . 'imports' . DS . 'qcw' . DS;
        $l_dir           = $g_absdir . $l_qcw_folder;
        $l_filename      = $l_config_title . '.xml';
        $l_full_filename = $l_dir . $l_filename;

        // Check if folder is writable
        if (is_writeable($l_dir))
        {
            $l_export = $this->export_current_config($l_full_filename);
            if (!$l_export)
            {
                throw new isys_exception_general('An error occurred while exporting the config.');
            } // if
        }
        else
        {
            throw new isys_exception_general('Please check the rights of the folder "' . $l_dir . '".');
        }

        return $l_full_filename;
    }

    /**
     * This function creates the xml file
     *
     * @param                         $p_file
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function export_current_config($p_file)
    {

        $l_xml = "<?xml version=\"1.0\" encoding=\"" . $this->m_encoding . "\" standalone=\"yes\"?>\n" . "<isys_export>\n" . $this->export_objtype_groups() . "</isys_export>";

        return file_put_contents($p_file, $l_xml);
    }

    /**
     * This function appends the objecttype groups to the xml file
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function export_objtype_groups()
    {
        $l_objecttype_groups = $this->m_dao->load_objecttypes_group(true);
        $l_root              = '';
        foreach ($l_objecttype_groups AS $l_objtype_group)
        {
            $l_root .= "\t<objecttypegroup>\n";
            $l_root .= $this->format_objtype_group_head($l_objtype_group);
            $l_root .= $this->export_objtypes($l_objtype_group['isys_obj_type_group__const']);
            $l_root .= "\t</objecttypegroup>\n";
        }

        return $l_root;
    }

    /**
     * This function adds the objecttype group header information to the xml file
     *
     * @param             $p_data
     * @param DOMDocument $p_doc
     * @param             $p_parent
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function format_objtype_group_head($p_data)
    {
        $l_root = "\t\t<head>\n";

        foreach ($p_data AS $l_key => $l_data)
        {
            $l_tag = str_replace('isys_obj_type_group__', '', $l_key);
            $l_root .= "\t\t\t<" . $l_tag . ">" . isys_glob_utf8_encode($l_data) . "</" . $l_tag . ">\n";
        }
        $l_root .= "\t\t</head>\n";

        return $l_root;
    }

    //////////////////////////////////
    //			END EXPORT			//
    //////////////////////////////////

    //////////////////////////////////
    //			START IMPORT		//
    //////////////////////////////////

    /**
     * This function appends the objecttypes to the xml file
     *
     * @param                         $p_objtype_group_const
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function export_objtypes($p_objtype_group_const)
    {
        $l_objtypes = $this->m_dao->load_objecttypes($p_objtype_group_const, true);
        $l_root     = "\t\t<objecttypes>\n";
        foreach ($l_objtypes AS $l_row)
        {
            $l_root .= "\t\t\t<objecttype>\n";
            $l_root .= $this->format_objtype_head($l_row);
            $l_root .= $this->export_assigned_categories($l_row['isys_obj_type__const']);
            $l_root .= "\t\t\t</objecttype>\n";
        }
        $l_root .= "\t\t</objecttypes>\n";

        return $l_root;
    } // function

    //////////////////////////////////
    //			END IMPORT			//
    //////////////////////////////////

    /**
     * This function adds the objecttype header information to the xml file
     *
     * @param             $p_data
     * @param DOMDocument $p_doc
     * @param             $p_parent
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function format_objtype_head($p_data)
    {
        $l_root = "\t\t\t\t<head>\n";

        $l_show_in_tree = '0';
        $l_status       = '1';

        foreach ($p_data AS $l_key => $l_data)
        {
            if (strpos($l_key, 'isys_obj_type__') !== false)
            {
                $l_tag = str_replace('isys_obj_type__', '', $l_key);
                if ($l_tag == 'isysgui_cats__id')
                {
                    $l_data = $p_data['isysgui_cats__const'];
                }
                if ($l_tag == 'show_in_tree')
                {
                    $l_show_in_tree = $l_data;
                }
                if ($l_tag == 'status')
                {
                    $l_status = $l_data;
                }
                $l_root .= "\t\t\t\t\t<" . $l_tag . ">" . isys_glob_utf8_encode($l_data) . "</" . $l_tag . ">\n";
            }
        }

        $l_query = 'UPDATE isys_obj_type SET isys_obj_type__show_in_tree = \'' . $l_show_in_tree . '\', isys_obj_type__status = \'' . $l_status . '\', isys_obj_type__isys_obj_type_group__id = ' . '(SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__const = \'' . $p_data['isys_obj_type_group__const'] . '\') ' . 'WHERE isys_obj_type__const = \'' . $p_data['isys_obj_type__const'] . '\'';

        $l_root .= "\t\t\t\t\t<query><![CDATA[" . $l_query . "]]></query>\n";

        $l_root .= "\t\t\t\t</head>\n";

        return $l_root;
    }

    /**
     * This function appends the categories to the xml file
     *
     * @param                         $p_objtype_const
     * @param DOMDocument             $p_doc
     * @param                         $p_parent
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function export_assigned_categories($p_objtype_const)
    {
        $l_categories = $this->m_dao->load_assigned_categories($p_objtype_const);
        $l_root       = "\t\t\t\t<assigned_catgories>";

        foreach ($l_categories AS $l_category)
        {
            $l_root .= $l_category . ",";
        }
        $l_root = rtrim($l_root, ',');
        $l_root .= "</assigned_catgories>\n";

        if (isset($this->m_dao->m_assigned_custom_categories[$p_objtype_const]))
        {
            $l_root .= "\t\t\t\t<assigned_custom_catgories>";
            $l_root .= "<![CDATA[";
            foreach ($this->m_dao->m_assigned_custom_categories[$p_objtype_const] AS $l_category)
            {
                $l_root .= serialize($this->m_dao->m_custom_categories[$l_category]) . "||";
            } // foreach
            $l_root = rtrim($l_root, '||') . "]]>";
            $l_root .= "</assigned_custom_catgories>\n";
        } // if

        return $l_root;
    } // function

    /**
     * Loads the config files and reorganizes the objecttype structure
     *
     * @param                         $p_filename_selection
     * @param isys_caching            $p_caching
     * @param bool                    $p_clear_category_assignments
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function load_config($p_filename_selection, isys_caching $p_caching)
    {
        global $g_absdir;

        try
        {
            $l_dir = $g_absdir . DS . 'imports/qcw/';

            if (!is_array($p_filename_selection)) $l_files_arr = [$p_filename_selection];
            else $l_files_arr = $p_filename_selection;

            $this->m_dao->disable_all_objecttypes();

            $l_libxml     = new isys_import_xml();
            $l_dao_custom = isys_custom_fields_dao::instance(isys_application::instance()->database);

            $l_res               = $l_dao_custom->get_data();
            $l_custom_categories = [];
            if ($l_res->num_rows())
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_custom_categories[$l_row['isysgui_catg_custom__const']] = $l_row['isysgui_catg_custom__id'];
                } // while
            } // if

            foreach ($l_files_arr AS $l_filename_item)
            {
                $l_filename      = $l_filename_item;
                $l_full_filename = $l_dir . $l_filename;

                $l_libxml->load_xml_file($l_full_filename);

                $l_xml_data = array_shift($l_libxml->get_xml_data());

                $l_verinice_installed = $this->m_dao->is_verinice_installed();

                foreach ($l_xml_data AS $l_arr)
                {
                    foreach ($l_arr AS $l_objtype_group)
                    {
                        $l_objtype_group_const = '';
                        $l_objtype_group_id    = '';
                        $l_objtype_group_data  = [];

                        $l_head = $l_objtype_group[0]['head'];
                        foreach ($l_head AS $l_key => $l_value)
                        {
                            if ($l_key == 'const')
                            {
                                $l_objtype_group_const = $l_value;
                                $l_objtype_group_id    = $this->m_dao->get_objtype_group_id($l_objtype_group_const);
                            } // if
                            $l_objtype_group_data['isys_obj_type_group__' . $l_key] = $l_value;
                        } // foreach
                        if (!$l_objtype_group_id)
                        {
                            $l_objtype_group_id = $this->m_dao->add_new_objtype_group($l_objtype_group_data);
                        } // if

                        /* Set status of object group */
                        $this->m_dao->objtypegroup_change_status($l_objtype_group_const, (bool) ($l_head['status'] - 1));

                        $l_objtypes = $l_objtype_group[1]['objecttypes'];

                        if (is_array($l_objtypes))
                        {
                            foreach ($l_objtypes AS $l_objtype)
                            {
                                $l_head                              = $l_objtype['objecttype'][0]['head'];
                                $l_string_assigned_categories        = trim($l_objtype['objecttype']['assigned_catgories']);
                                $l_string_assigned_custom_categories = '';
                                $l_assigned_custom_categories        = [];
                                $l_objtype_const                     = '';
                                $l_assignment_query                  = '';

                                foreach ($l_head AS $l_key => $l_value)
                                {
                                    if ($l_key == 'const')
                                    {
                                        $l_objtype_const                            = $l_value;
                                        $l_objtype_data['isys_obj_type__' . $l_key] = $l_value;
                                    }
                                    elseif ($l_key == 'query')
                                    {
                                        $l_assignment_query = $l_value;
                                    }
                                    else
                                    {
                                        $l_objtype_data['isys_obj_type__' . $l_key] = $l_value;
                                    } // if
                                } // foreach
                                $l_objtype_data['isys_obj_type__isys_obj_type_group__id'] = $l_objtype_group_id;

                                $l_check = $this->m_dao->check_objecttype_by_const($l_objtype_const);
                                if (!$l_check)
                                {
                                    $l_objtype_id = $this->m_dao->add_new_objtype($l_objtype_data, $l_verinice_installed);
                                }
                                else
                                {
                                    $l_objtype_id = $this->m_dao->get_objtype_id($l_objtype_const);
                                } // if

                                // Custom categories
                                if (isset($l_objtype['objecttype']['assigned_custom_catgories']))
                                {
                                    $l_string_assigned_custom_categories = trim($l_objtype['objecttype']['assigned_custom_catgories']);
                                    $l_assigned_custom_categories_res    = $l_dao_custom->get_assignments(null, $l_objtype_id);
                                    while ($l_row_cc = $l_assigned_custom_categories_res->get_row())
                                    {
                                        $l_assigned_custom_categories[$l_row_cc['isysgui_catg_custom__const']] = true;
                                    } // while
                                } // if

                                // Update objtype group assignment
                                $this->m_db->query($l_assignment_query);

                                $l_assigned_categories_cached = $p_caching->get($l_objtype_const);
                                if (!$l_assigned_categories_cached)
                                {
                                    $l_assigned_categories_cached = $this->m_dao->cache_objtype($l_objtype_id, $l_objtype_const, $p_caching);
                                } // if
                                $l_new_assigned_categories        = [];
                                $l_new_assigned_custom_categories = [];

                                if ($l_string_assigned_categories != '')
                                {
                                    $l_assigned_categories     = [];
                                    $l_raw_assigned_categories = explode(',', $l_string_assigned_categories);
                                    foreach ($l_raw_assigned_categories AS $l_category_const)
                                    {

                                        $l_cat_id = $p_caching->get($l_category_const);
                                        if (empty($l_cat_id))
                                        {
                                            $p_caching->add($l_category_const, ($l_cat_id = $this->m_dao->get_catg_id($l_category_const)))
                                                ->save();
                                        } // if

                                        $l_assigned_categories[$l_category_const] = $l_cat_id;
                                    } // foreach

                                    if (!$l_assigned_categories_cached)
                                    {
                                        $l_new_assigned_categories    = $l_assigned_categories;
                                        $l_assigned_categories_cached = [];
                                    }
                                    else
                                    {
                                        foreach ($l_assigned_categories AS $l_cat_const => $l_cat_id)
                                        {
                                            if (!in_array($l_cat_const, $l_assigned_categories_cached))
                                            {
                                                $l_new_assigned_categories[] = $l_cat_const;
                                            } // if
                                        } // foreach
                                    } // if
                                } // if

                                // Handle custom categories
                                if ($l_string_assigned_custom_categories != '')
                                {
                                    $l_raw_assigned_categories = explode('||', $l_string_assigned_custom_categories);

                                    foreach ($l_raw_assigned_categories AS $l_category_info)
                                    {
                                        $l_category_data       = unserialize($l_category_info);
                                        $l_category_const      = $l_category_data[0];
                                        $l_category_title      = $l_category_data[1];
                                        $l_category_multivalue = $l_category_data[2];
                                        $l_category_config     = unserialize($l_category_data[3]);

                                        if (!isset($l_custom_categories[$l_category_const]))
                                        {
                                            // Custom category does not exist create it
                                            $l_custom_id                            = $l_dao_custom->create(
                                                $l_category_title,
                                                $l_category_config,
                                                0,
                                                0,
                                                $l_category_multivalue,
                                                $l_category_const
                                            );
                                            $l_custom_categories[$l_category_const] = $l_custom_id;

                                            // Assign custom category to object type
                                            $l_dao_custom->assign($l_custom_id, $l_objtype_id);
                                        }
                                        else
                                        {
                                            // Custom category exists
                                            $l_custom_id = $l_custom_categories[$l_category_const];

                                            // Check if its assigned to the object type
                                            if (!$l_assigned_custom_categories[$l_category_const])
                                            {
                                                // Assign custom category to object type
                                                $l_dao_custom->assign($l_custom_categories[$l_category_const], $l_objtype_id);
                                            } // if
                                        } // if

                                        $l_cat_id = $p_caching->get($l_category_const);
                                        if (empty($l_cat_id))
                                        {
                                            $p_caching->add($l_category_const, $l_custom_id)
                                                ->save();
                                        } // if

                                        if (!in_array($l_category_const, $l_assigned_categories_cached))
                                        {
                                            $l_new_assigned_custom_categories[] = $l_category_const;
                                        } // if
                                    } // foreach
                                } // if

                                // Assign categories only if it is needed
                                if (count($l_new_assigned_categories) || count($l_new_assigned_custom_categories))
                                {
                                    // Merge categories from selected profile and add them to the cache
                                    $l_assigned_categories_cached = array_merge($l_assigned_categories_cached, $l_new_assigned_categories, $l_new_assigned_custom_categories);
                                    $p_caching->add($l_objtype_const, $l_assigned_categories_cached)
                                        ->save();

                                    // Assign only global categories
                                    $this->m_dao->assign_categories_to_objecttype($l_objtype_id, $l_new_assigned_categories);
                                } // if
                            } // foreach
                        } // if
                    } // foreach
                } // foreach
            } // foreach
        }
        catch (Exception $e)
        {
            isys_notify::warning($e->getMessage());
        }
    } // function

    /**
     * This methods calls the specified method from the dao class.
     *
     * @param   string $p_method
     * @param   array  $p_params
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function call_dao_method($p_method, $p_params)
    {
        return call_user_func_array(
            [
                $this->m_dao,
                $p_method
            ],
            $p_params
        );
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->m_module_id = C__MODULE__QCW;
        $this->m_db        = isys_application::instance()->database;
        $this->m_dao       = new isys_quick_configuration_wizard_dao($this->m_db);
        parent::__construct();
    } // function
} // class