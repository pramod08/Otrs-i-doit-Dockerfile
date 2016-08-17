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
 * Export module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_export extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    const FILTER_OBJECT          = 1;
    const FILTER_OBJECT_TYPE     = 2;
    const FILTER_LOCATION        = 3;
    const FILTER_ALL             = 100;
    const SAVE_SHOW              = 0;
    const SAVE_DOWNLOAD          = 1;
    const SAVE_AS                = 2;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    private $m_userrequest;

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_export::instance();
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
        global $g_dirs, $g_comp_template_language_manager;

        $l_parent    = -1;
        $l_submodule = '';
        $l_template  = $this->m_userrequest->get_template();

        if ($p_system_module)
        {
            $l_parent    = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__EXPORT;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(
                C__MODULE__EXPORT . '0',
                $l_parent,
                'CMDB Export'
            );
        } // if

        $p_tree->add_node(
            C__MODULE__EXPORT . '1',
            $l_root,
            $g_comp_template_language_manager->get('LC__MODULE__EXPORT__EXPORT_WIZARD'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__EXPORT . '1' . '&' . C__GET__SETTINGS_PAGE . '=1',
            '',
            $g_dirs['images'] . '/tree/monitor.gif',
            (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
            '',
            '',
            isys_auth_export::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'EXPORT/' . C__MODULE__EXPORT . '1')
        );

        $p_tree->add_node(
            C__MODULE__EXPORT . '2',
            $l_root,
            $g_comp_template_language_manager->get('LC__MODULE__EXPORT__EXPORT_DRAFT'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__EXPORT . '2' . '&' . C__GET__SETTINGS_PAGE . '=2',
            '',
            $g_dirs['images'] . '/tree/enclosure.gif',
            (($_GET[C__GET__SETTINGS_PAGE] == '2') ? 1 : 0),
            '',
            '',
            isys_auth_export::instance()
                ->is_allowed_to(isys_auth::VIEW, 'EXPORT/' . C__MODULE__EXPORT . '2')
        );

        // Emit signal for letting extensions extend the tree.
        isys_component_signalcollection::get_instance()
            ->emit('mod.export.extendTree', $p_tree);
    }

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @return  bool    true
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_url_exploded        = explode('?', $_SERVER['HTTP_REFERER']);
        $l_url_parameters      = $l_url_exploded[1];
        $l_parameters_exploded = explode('&', $l_url_parameters);

        $l_params = array_pop(
            array_map(
                function ($p_arg)
                {
                    $l_return = [];
                    foreach ($p_arg AS $l_content)
                    {
                        list($l_key, $l_value) = explode('=', $l_content);
                        $l_return[$l_key] = $l_value;
                    }

                    return $l_return;
                },
                [$l_parameters_exploded]
            )
        );

        $p_text[] = _L('LC__MODULE__EXPORT') . ' ' . _L('LC__UNIVERSAL__MODULE');

        if (isset($l_params[C__GET__SETTINGS_PAGE]))
        {
            if ($l_params[C__GET__SETTINGS_PAGE] == 1)
            {
                $p_text[] = _L('LC__MODULE__EXPORT__EXPORT_WIZARD');
            }
            else
            {
                $p_text[] = _L('LC__MODULE__EXPORT__EXPORT_DRAFT');
            } // if
        }
        else
        {
            $p_text[] = _L('LC__MODULE__EXPORT__EXPORT_WIZARD');
        } // if

        $p_link = $l_url_parameters;

        return true;
    }

    /**
     * Starts module processing.
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        global $index_includes;

        // Handle AJAX requests.
        if ($_GET['ajax'] && !isys_glob_get_param('mydoitAction'))
        {
            switch ($_GET['request'])
            {
                case 'list':
                    $this->show_object_list();
                    break;
                case 'cmdb':
                    $this->show_cmdb_view();
                    break;
            } // switch

            // Delete export templates:
            if (is_array($_POST['id']))
            {
                foreach ($_POST['id'] as $l_id)
                {
                    $this->delete_options(intval($l_id));
                }
            }
            else
            {
                die();
            }
        } // if
        // Set default page-id to export-wizard.
        if (!isset($_GET[C__GET__SETTINGS_PAGE]))
        {
            if (isys_auth_export::instance()
                ->is_allowed_to(isys_auth::EDIT, 'EXPORT/' . C__MODULE__EXPORT . '1')
            )
            {
                $_GET[C__GET__SETTINGS_PAGE] = "1";
            }
            elseif (isys_auth_export::instance()
                ->is_allowed_to(isys_auth::VIEW, 'EXPORT/' . C__MODULE__EXPORT . '2')
            )
            {
                $_GET[C__GET__SETTINGS_PAGE] = "2";
            }
        } // if
        $l_template = $this->m_userrequest->get_template();

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = $this->m_userrequest->get_menutree();
            $this->build_tree($l_tree, false);
            $l_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        try
        {
            // Switch export pages.
            switch ($_GET[C__GET__SETTINGS_PAGE])
            {
                case '2':
                    isys_auth_export::instance()
                        ->check(isys_auth::VIEW, 'EXPORT/' . C__MODULE__EXPORT . '2');
                    $this->templates();
                    break;
                case '1':
                default:
                    isys_auth_export::instance()
                        ->check(isys_auth::EXECUTE, 'EXPORT/' . C__MODULE__EXPORT . '1');
                    $this->export();
                    break;
            } // switch

            $l_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
            $index_includes['contentbottomcontent'] = "modules/export/main.tpl";
        }
        catch (isys_exception_general $e)
        {
            throw $e;
        }
        catch (isys_exception_auth $e)
        {
            $l_template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    }

    /**
     * @param isys_module_request & $p_req
     *
     * @desc Initializes the module
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req))
        {
            $this->m_userrequest = &$p_req;

            return true;
        }

        return false;
    }

    /**
     * Deletes existing export template.
     *
     * @param int $p_id Export template's identifier
     *
     * @return bool Success?
     */
    public function delete_options($p_id)
    {
        global $g_comp_database;

        return $g_comp_database->query("DELETE FROM isys_export WHERE (isys_export__id = '" . $g_comp_database->escape_string($p_id) . "');");
    }

    /**
     * Saves an options array for later exports
     *
     * @param string $p_title
     * @param array  $p_params
     *
     * @return bool
     */
    public function save_options($p_title, $p_params)
    {
        global $g_comp_database;
        if (is_array($p_params) && count($p_params) > 0)
        {

            $l_params = serialize($p_params);

            $l_sql = "INSERT INTO isys_export " . "SET " . "isys_export__title = '" . $g_comp_database->escape_string(
                    $p_title
                ) . "', " . "isys_export__params = '" . $g_comp_database->escape_string($l_params) . "', " . "isys_export__exported = 1, " . "isys_export__datetime = NOW();";

            if ($g_comp_database->query($l_sql))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Starts the export.
     *
     * @param array $p_objects
     * @param mixed $p_categories (optional) List of categories as array or false (bool) to ignore it. Defaults to false.
     */
    public function start_export($p_objects, $p_categories = false)
    {
        global $g_comp_template, $g_comp_template_language_manager, $index_includes, $g_comp_database;

        $l_option_save = '';

        if (isset($_POST['options_save']) && $_POST['options_save'] == '1')
        {

            unset($_POST['options_save']);
            if ($this->save_options($_POST['options_save_filename'], $_POST))
            {
                $l_option_save = '<br />* ' . sprintf(
                        $g_comp_template_language_manager->get('LC__CMDB__EXPORT__TEMPLATE_CREATION_SUCCEEDED'),
                        $_POST["options_save_filename"]
                    );
            }
            else
            {
                $l_option_save = '<br />* ' . $g_comp_template_language_manager->get('LC__CMDB__EXPORT__TEMPLATE_CREATION_FAILED');
            }
        }

        $l_type        = isset($_POST['type']) ? $_POST['type'] : 'xml';
        $l_export_type = 'isys_export_type_' . $l_type;

        if (class_exists($l_export_type))
        {
            try
            {

                switch ($l_type)
                {
                    case 'csv':
                        $l_csv_export_class = $_POST['csv_type'];
                        $l_export           = new isys_export_cmdb_csv_object($l_export_type, $g_comp_database);

                        if (class_exists($l_csv_export_class))
                        {
                            $l_parser = $l_export->export($p_objects, $l_csv_export_class)
                                ->parse();
                        }
                        else
                        {
                            throw new isys_exception_cmdb(sprintf('Class "isys_export_csv_%s" does not exist.', $l_csv_export_class));
                        }
                        break;
                    default:
                        $l_export = new isys_export_cmdb_object($l_export_type, $g_comp_database);
                        $l_parser = $l_export->export($p_objects, $p_categories)
                            ->parse();
                        break;
                }

                switch ($_POST['export_save'])
                {
                    case self::SAVE_DOWNLOAD:
                        if ($l_type == "csv")
                        {
                            header("Content-Type: text/csv; charset=utf-8;");
                        }
                        else
                        {
                            if (defined('C__EXPORT__CONTENT_TYPE'))
                            {
                                header('Content-Type: ' . C__EXPORT__CONTENT_TYPE . '; charset=utf-8;');
                            }
                            else
                            {
                                header('Content-Type: text/plain; charset=utf-8;');
                            }
                        }
                        header('Content-Disposition: attachment; filename="idoit-export-' . date('Ymdhis') . '.' . $l_type . '"');

                        if ($l_type == 'csv') echo $l_parser->get_export();
                        else
                            echo isys_glob_utf8_encode($l_parser->get_export());

                        die();

                        break;
                    case self::SAVE_AS:

                        if ($_POST['export_save_filename'])
                        {
                            if (file_put_contents($_POST['export_save_filename'], isys_glob_utf8_encode($l_parser->get_export())))
                            {
                                $g_comp_template->assign(
                                    'note',
                                    "* " . sprintf(
                                        $g_comp_template_language_manager->get('LC__CMDB__EXPORT__SAVING_EXPORT_SUCCEEDED'),
                                        $_POST['export_save_filename']
                                    ) . $l_option_save
                                );
                            }
                            else
                            {
                                $g_comp_template->assign(
                                    'note',
                                    "* " . sprintf(
                                        $g_comp_template_language_manager->get('LC__CMDB__EXPORT__SAVING_EXPORT_FAILED'),
                                        $_POST['export_save_filename']
                                    ) . $l_option_save
                                );
                            }
                        }
                        else
                        {
                            $g_comp_template->assign('note', $g_comp_template_language_manager->get('LC__CMDB__EXPORT__INVALID_EXPORT_FILE'));
                        }

                        break;
                    default:
                    case self::SAVE_SHOW:
                        if (defined('C__EXPORT__CONTENT_TYPE'))
                        {
                            header('Content-Type: ' . C__EXPORT__CONTENT_TYPE . '; charset=utf-8;');
                        }

                        echo isys_glob_utf8_encode($l_parser->get_export());
                        die();

                        break;
                }
            }
            catch (Exception $e)
            {
                die($e->getMessage());
            }
        }

        $index_includes['contenttop'] = 'content/top/main.tpl';
    } // function

    /**
     * Get a list of available export types
     *
     * @return array
     */
    public function get_available_export_types($p_directory = "src/classes/export/type/")
    {
        global $g_absdir;
        $l_files = [];

        $l_has_custom_csv_exports = $this->has_customer_exports();

        $l_typedir = $g_absdir . DIRECTORY_SEPARATOR . $p_directory;
        foreach (glob($l_typedir . "isys_export_type_*.class.php") as $l_file)
        {
            $l_file = str_replace($l_typedir . "isys_export_type_", "", $l_file);
            $l_file = str_replace(".class.php", "", $l_file);
            if ($l_file != 'csv' || $l_has_custom_csv_exports) $l_files[$l_file] = $l_file;
        }
        arsort($l_files);

        return $l_files;
    }

    /**
     * Get a list of available export types
     *
     * @param   string $p_directory
     * @param   string $p_additional
     *
     * @return  array
     */
    public function get_available_csv_export_types($p_directory = "src/classes/export/csv/", $p_additional = "")
    {
        global $g_absdir;

        $l_files = [];

        if (is_dir($p_directory))
        {
            $l_typedir = $g_absdir . DIRECTORY_SEPARATOR . $p_directory;
            if (is_dir($l_typedir))
            {
                $l_files_arr = [];
                if (function_exists('glob'))
                {
                    $l_files_arr = glob($l_typedir . "isys_export_csv_" . $p_additional . "*.class.php");

                    if ($l_files_arr === false)
                    {
                        isys_notify::error('An error occured while retrieving files like "' . $l_typedir . 'isys_export_csv_' . $p_additional . '*.class.php".');
                    } // try
                }
                else
                {
                    $l_dir_handler = opendir($l_typedir);

                    while ($l_file = readdir($l_dir_handler))
                    {
                        if ($l_file == '.' || $l_file == '..')
                        {
                            continue;
                        } // if

                        if ($l_file != 'isys_export_csv.class.php')
                        {
                            $l_files_arr[] = $l_file;
                        } // if
                    } // while
                } // if

                if (is_array($l_files_arr) && count($l_files_arr))
                {
                    foreach ($l_files_arr as $l_file)
                    {
                        $l_file  = str_replace($l_typedir, "", $l_file);
                        $l_class = str_replace(".class.php", "", $l_file);

                        if (class_exists($l_class))
                        {
                            $l_class_obj = new $l_class();

                            $l_title = $l_class_obj->get_title();
                        } // if

                        $l_files[$l_class] = $l_title;
                    } // foreach
                } // if
            } // if
        } // if

        if (count($l_files) > 0)
        {
            return $l_files;
        }
        else
        {
            return false;
        } // if
    }

    /**
     * Checks if there exists any customer csv exports
     *
     * @return bool
     */
    public function has_customer_exports()
    {
        global $g_absdir;

        $l_directory = "src/classes/export/csv/";
        $l_files     = null;

        if (is_dir($l_directory))
        {
            $l_typedir = $g_absdir . DIRECTORY_SEPARATOR . $l_directory;
            $l_files   = glob($l_typedir . 'isys_export_csv_' . '*.class.php');
        }

        if (!empty($l_files) && count($l_files) > 0) return true;
        else return false;
    }

    /**
     * Handles export functions.
     *
     * @todo    Let the user choose specific categories like the global ones in the GUI.
     *
     * @param   array $p_options (optional) Options. Defaults to null.
     */
    public function export($p_options = null)
    {
        global $g_comp_database, $g_comp_template;

        if (is_array($p_options))
        {
            $l_posts = $p_options;
            $_POST   = $p_options;
        }
        else
        {
            $l_posts = $this->m_userrequest->get_posts();
        } // if

        // Get available export types:
        if ($l_posts['step'] == '2')
        {
            $g_comp_template->assign('export_types', $this->get_available_export_types())
                ->assign('csv_export_types', $this->get_available_csv_export_types());
        } // if

        $l_cmdb_dao              = new isys_cmdb_dao($g_comp_database);
        $l_exportable_categories = isys_export_cmdb_object::fetch_exportable_categories();

        // Assign categories:
        $l_categories = $l_export_cats = $l_export_custom = [];

        // Allowed categories
        $l_auth_cmdb          = isys_auth_cmdb_categories::instance();
        $l_allowed_categories = $l_auth_cmdb->get_allowed_categories();

        // Assign durable global categories:
        $l_cat = $l_cmdb_dao->get_durable_catg();
        while ($l_row = $l_cat->get_row())
        {
            if (class_exists($l_row['isysgui_catg__class_name']))
            {
                if (is_array($l_allowed_categories) && !in_array($l_row['isysgui_catg__const'], $l_allowed_categories))
                {
                    continue;
                } // if

                $l_check_cat = new $l_row['isysgui_catg__class_name']($g_comp_database);

                if (count(($l_properties = $l_check_cat->get_properties())) > 0)
                {
                    if (isys_export_cmdb_object::is_catg_exportable($l_properties))
                    {
                        $l_categories[_L($l_row['isysgui_catg__title'])] = [
                            'id'    => $l_row['isysgui_catg__id'],
                            'title' => $l_row['isysgui_catg__title']
                        ];
                    } // if
                } // if
            } // if
        } // while

        // Assign global categories:
        $l_cat = $l_cmdb_dao->get_all_catg();
        while ($l_row = $l_cat->get_row())
        {
            if ($l_row['isysgui_catg__const'] == 'C__CATG__GLOBAL' || (is_array($l_allowed_categories) && !in_array($l_row['isysgui_catg__const'], $l_allowed_categories)))
            {
                continue;
            } // if

            if (class_exists($l_row['isysgui_catg__class_name']))
            {
                $l_check_cat = new $l_row['isysgui_catg__class_name']($g_comp_database);
                if (count(($l_properties = $l_check_cat->get_properties())) > 0)
                {
                    if (isys_export_cmdb_object::is_catg_exportable($l_properties))
                    {
                        $l_categories[_L($l_row['isysgui_catg__title'])] = [
                            'id'    => $l_row['isysgui_catg__id'],
                            'title' => $l_row['isysgui_catg__title']
                        ];
                    } // if
                } // if
            } // if
        } // while

        // Specific Categories.
        $l_cats = $l_cmdb_dao->get_isysgui('isysgui_cats');

        while ($l_row = $l_cats->get_row())
        {
            if (is_array($l_allowed_categories) && !in_array($l_row['isysgui_cats__const'], $l_allowed_categories))
            {
                continue;
            } // if

            if (isset($l_exportable_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']]) && $l_exportable_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']] === true)
            {
                $l_export_cats[] = $l_row['isysgui_cats__id'];
            } // if
        } // while

        unset($l_cats);

        // Custom Categories.
        if (count($l_exportable_categories[C__CMDB__CATEGORY__TYPE_CUSTOM]))
        {
            $l_catg_custom = $l_cmdb_dao->get_all_catg_custom();

            while ($l_row = $l_catg_custom->get_row())
            {
                if (is_array($l_allowed_categories) && !in_array($l_row['isysgui_catg_custom__const'], $l_allowed_categories))
                {
                    continue;
                } // if

                if ($l_exportable_categories[C__CMDB__CATEGORY__TYPE_CUSTOM][$l_row['isysgui_catg_custom__id']])
                {
                    $l_export_custom[_L($l_row['isysgui_catg_custom__title'])] = $l_row;
                } // if
            } // while

            unset($l_catg_custom);
        } // if

        ksort($l_categories);
        ksort($l_export_custom);

        $g_comp_template->assign('categories', array_values($l_categories))
            ->assign('custom_categories', array_values($l_export_custom));

        /**
         * Switch Step 1 Selection (Which kind of export)
         */
        switch ($l_posts['export_filter'])
        {
            /* Export objects only (object based) */
            case self::FILTER_OBJECT:
                if ($l_posts["step"] == "3")
                {
                    $l_objects = isys_format_json::decode($_POST["object_ids__HIDDEN"]);
                }

                break;
            /* Export objects by location */
            case self::FILTER_LOCATION:

                $l_dao_location = new isys_cmdb_dao_location($g_comp_database);

                if ($l_posts["step"] == '3')
                {
                    $l_objects_array = $l_dao_location->get_locations_by_obj_id($l_posts["C__CATG__LOCATION_PARENT__HIDDEN"]);
                    if (count($l_objects_array) > 0)
                    {
                        foreach ($l_objects_array as $l_loc)
                        {
                            $l_objects[] = $l_loc[0];
                        }
                    }
                    else
                    {
                        $GLOBALS['g_error'] = 'There were no childs found for your selection.';
                    }
                }

                break;
            /* Export complete object types (object type based) */
            case self::FILTER_OBJECT_TYPE :
                if ($l_posts["step"] == "2")
                {
                    // Assign object types.
                    $l_ot                   = $l_cmdb_dao->get_objtype();
                    $l_allowed_object_types = isys_auth_cmdb_object_types::instance()
                        ->get_allowed_objecttypes();
                    $l_otypes               = [];
                    if ($l_allowed_object_types === true || is_array($l_allowed_object_types))
                    {
                        while ($l_row = $l_ot->get_row())
                        {
                            if ((is_array($l_allowed_object_types) && !in_array($l_row['isys_obj_type__id'], $l_allowed_object_types))) continue;

                            // We don't want relations and parallel relations to be exported.
                            if ($l_row['isys_obj_type__const'] != 'C__OBJTYPE__PARALLEL_RELATION' && $l_row['isys_obj_type__const'] != 'C__OBJTYPE__RELATION')
                            {
                                $l_otypes[_L($l_row['isys_obj_type__title'])] = $l_row;
                            } // if
                        } // while
                    } // if

                    ksort($l_otypes);

                    $g_comp_template->assign("objecttypes", array_values($l_otypes));
                }
                else if ($l_posts["step"] == "3")
                {
                    if (isset($l_posts["objecttype"]))
                    {
                        foreach ($l_posts["objecttype"] as $l_ot)
                        {
                            $l_objects_dao = $l_cmdb_dao->get_objects_by_type_id($l_ot);

                            if ($l_objects_dao->num_rows() > 0)
                            {
                                while ($l_row = $l_objects_dao->get_row())
                                {
                                    if ($l_row["isys_obj__status"] == C__RECORD_STATUS__NORMAL)
                                    {
                                        $l_objects[] = $l_row["isys_obj__id"];
                                    } // if
                                } // while
                            }
                            else
                            {
                                ; // No objects found in object type $l_ot.
                            } // if
                        } // foreach
                    } // if
                } // if

                break;
            /* Export complete CMDB */
            case self::FILTER_ALL:

                if ($l_posts["step"] == "3")
                {
                    $l_cmdb_dao     = new isys_cmdb_dao($g_comp_database);
                    $l_dao_location = new isys_cmdb_dao_location($g_comp_database);

                    /* First get all hierarchical location objects starting from ROOT-NODE */
                    $l_objects_array = $l_dao_location->get_locations_by_obj_id(C__OBJ__ROOT_LOCATION);
                    foreach ($l_objects_array as $l_loc_obj)
                    {
                        $l_objects[]              = $l_loc_obj[0];
                        $l_objects_in[$l_loc_obj] = true;
                    }

                    /* Then get all other objects */
                    $l_object_dao = $l_cmdb_dao->get_object();
                    while ($l_row = $l_object_dao->get_row())
                    {

                        if ($l_row["isys_obj__status"] == C__RECORD_STATUS__NORMAL && !$l_objects_in[$l_row["isys_obj__id"]])
                        {
                            $l_objects[] = $l_row["isys_obj__id"];
                        }
                    }
                }

                break;
        }
        // Which categories does the user want to export?

        /* Global Categories */
        $l_wanted_categories = [];
        if ($l_posts['all_categories'] == 'all')
        {
            foreach ($l_categories as $l_category)
            {
                if ($l_category['id'] != C__CATG__ITS_LOGBOOK && $l_category['id'] != C__CATG__COMPUTING_RESOURCES)
                {
                    $l_wanted_categories[] = $l_category['id'];
                }
            }
        }
        else
        {
            $l_wanted_categories = $l_posts['category'];
        }

        /* Custom categories */
        $l_custom_wanted_categories = [];
        if (isset($l_posts['custom_category']) && !empty($l_posts['custom_category']))
        {
            $l_custom_wanted_categories = $l_posts['custom_category'];
        }

        // Add all specific categories to the export:
        $l_export_categories = [
            C__CMDB__CATEGORY__TYPE_GLOBAL => $l_wanted_categories,
            C__CMDB__CATEGORY__TYPE_CUSTOM => $l_custom_wanted_categories
        ];

        if (isset($l_posts['export_specific_categories']))
        {
            $l_export_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $l_export_cats;
        }

        // Start exporting:
        if (is_array($l_objects))
        {
            $this->start_export($l_objects, $l_export_categories);
        }
    } // function

    /**
     * Shows export templates and handles them.
     */
    public function templates()
    {
        global $g_comp_database, $g_comp_template, $g_comp_template_language_manager;

        $l_navbar = $this->m_userrequest->get_navbar();

        $l_navbar->set_active(
            isys_auth_export::instance()
                ->is_allowed_to(isys_auth::DELETE, 'EXPORT/' . C__MODULE__EXPORT . '2'),
            C__NAVBAR_BUTTON__DELETE
        )
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        $l_dao     = new isys_export_dao($g_comp_database);
        $l_exports = $l_dao->get_data();

        /**
         * Display template list
         */
        $l_arTableHeader = [
            "isys_export__title"    => $g_comp_template_language_manager->{"LC__WORKFLOW__TEMPLATES"},
            "save_as"               => "Export " . $g_comp_template_language_manager->{"LC__CMDB__CATG__TYPE"},
            "isys_export__datetime" => $g_comp_template_language_manager->{"LC__TASK__DETAIL__WORKORDER__CREATION_DATE"},
            "exported_count"        => $g_comp_template_language_manager->{"LC__EXPORT__EXECUTED"},
        ];

        $l_objList = new isys_component_list(ISYS_NULL, $l_exports);
        $l_objList->set_row_modifier($this, "row_mod_tpl");

        $l_objList->config(
            $l_arTableHeader,
            "?" . $_SERVER["QUERY_STRING"] . "&id=[{isys_export__id}]",
            "[{isys_export__id}]",
            true
        );

        $l_objList->createTempTable();

        $g_comp_template->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1');
        $g_comp_template->assign("g_list", $l_objList->getTempTableHtml());

        if ($_GET["id"])
        {
            /**
             * Start export
             */
            $l_exports = $l_dao->get_data($_GET["id"]);

            if ($l_exports->num_rows() > 0)
            {
                /* Get export options (_POST parameters) */
                $l_data   = $l_exports->__to_array();
                $l_params = unserialize($l_data["isys_export__params"]);

                /* Increment export counter */
                $l_dao->count($_GET["id"]);

                /* Do the export with parameters: $l_params */
                $this->export($l_params);
            }
        }
    } // function

    /**
     * @param  array $p_row
     */
    public function row_mod_tpl(&$p_row)
    {
        $l_params = unserialize($p_row["isys_export__params"]);

        switch ($l_params["export_save"])
        {
            case self::SAVE_SHOW:
                $p_row["save_as"] = _L("LC__CMDB__CATS__LICENCE_SHOW");
                break;

            case self::SAVE_DOWNLOAD :
                $p_row["save_as"] = "Download";
                break;

            case self::SAVE_AS:
                $p_row["save_as"] = "Save as " . $l_params["export_save_filename"];
                break;
        } // switch
    }

    /**
     * Creates CMDB printing views.
     *
     * @global array $g_config
     * @global <type> $g_comp_database
     * @global <type> $g_comp_template_language_manager
     */
    public function show_cmdb_view()
    {
        global $g_config, $g_absdir;

        $l_objType = isys_glob_get_param(C__CMDB__GET__OBJECTTYPE);
        $l_objID   = isys_glob_get_param(C__CMDB__GET__OBJECT);
        $l_cat     = isys_glob_get_param(C__CMDB__GET__CATG);
        $l_scat    = isys_glob_get_param(C__CMDB__GET__CATS);
        $l_start   = isys_glob_get_param("navPageStart");
        $l_filter  = isys_glob_get_param("filter");

        if ($l_objType != null)
        {
            global $g_comp_database;
            global $g_comp_template_language_manager;

            $l_export = new isys_export_cmdb_object('isys_export_type_xml', $g_comp_database);

            $l_categories = [];

            if ($l_objID != null)
            {
                // Object overview's printing view:

                if ($l_cat == C__CATG__OVERVIEW || ($l_cat == null && $l_scat == null))
                {
                    // Overview
                    $l_objects   = [];
                    $l_objects[] = $l_objID;

                    $l_dao = isys_cmdb_dao_category_g_overview::instance($g_comp_database);

                    // Get category types:
                    $l_category_types = $l_dao->get_category_types();

                    foreach ($l_category_types as $l_category_type_id => $l_category_type_const)
                    {
                        // Get visible categories in the right order:
                        $l_cat_res = $l_dao->get_categories(
                            $l_objType,
                            $l_category_type_id,
                            C__RECORD_STATUS__NORMAL,
                            true,
                            true
                        );

                        while ($l_row = $l_cat_res->get_row())
                        {
                            switch ($l_category_type_id)
                            {
                                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                                    $l_categories[$l_category_type_id][] = $l_row['isysgui_catg__id'];
                                    break;
                                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                                    $l_categories[$l_category_type_id][] = $l_row['isysgui_cats__id'];
                                    break;
                            } // switch
                        } // while
                    } // foreach

                    unset($l_dao);
                }
                else
                {
                    // Category's printing view:

                    $l_objects   = [];
                    $l_objects[] = $l_objID;

                    $l_cats = [];

                    $l_dao = new isys_cmdb_dao($g_comp_database);

                    if ($l_cat)
                    {
                        $l_res = $l_dao->catg_get_subcats($_GET[C__CMDB__GET__CATG]);

                        if ($l_res->num_rows() > 0)
                        {
                            $l_cats[] = $_GET[C__CMDB__GET__CATG];
                            while ($l_row = $l_res->get_row())
                            {
                                $l_cats[] = $l_row['isysgui_catg__id'];
                            }
                            $l_categories = [
                                $l_cats,
                                [],
                                [],
                                [],
                                []
                            ];
                        }
                        else if ($l_cat == C__CATG__CUSTOM_FIELDS)
                        {
                            $l_categories = [
                                [],
                                [],
                                [],
                                [],
                                [$_GET[C__CMDB__GET__CATG_CUSTOM]]
                            ];
                        }
                        else
                        {
                            $l_cats[]     = $_GET[C__CMDB__GET__CATG];
                            $l_categories = [
                                $l_cats,
                                [],
                                [],
                                [],
                                []
                            ];
                        }
                    }
                    elseif ($l_scat)
                    {

                        $l_res = $l_dao->cats_get_subcats($_GET[C__CMDB__GET__CATS]);

                        if ($l_res->num_rows() > 0)
                        {
                            $l_cats[] = $_GET[C__CMDB__GET__CATS];
                            while ($l_row = $l_res->get_row())
                            {
                                $l_cats[] = $l_row['isysgui_cats_2_subcategory__isysgui_cats__id__child'];
                            }
                        }
                        else
                        {
                            $l_cats[] = $_GET[C__CMDB__GET__CATS];
                        }
                        $l_categories = [
                            [],
                            $l_cats,
                            [],
                            [],
                            []
                        ];
                    }

                }

                $l_xsl = file_get_contents('xsl/trans.xsl');

                while (preg_match("/\{\*(.*?)\*\}/", $l_xsl, $l_match))
                {
                    $l_xsl = str_replace("{*" . $l_match[1] . "*}", $g_comp_template_language_manager->get($l_match[1]), $l_xsl);
                }

                $l_xsl = preg_replace('/%%BASE_URL%%/', $g_config['www_dir'], $l_xsl);

                if (file_put_contents($g_absdir . '/temp/trans.xsl', isys_glob_utf8_encode($l_xsl)))
                {
                    $l_parser = $l_export->export($l_objects, $l_categories)
                        ->parse(null, $g_config['www_dir'] . 'temp/trans.xsl', true);

                    $l_xml = $l_parser->get_export();

                    header('Content-Type: text/xml; charset=utf-8');

                    echo isys_glob_utf8_encode($l_xml);
                }
            }
            else
            {
                // Object lists' printing view:

                $l_objects = [];

                $l_dao = isys_cmdb_dao_category_g_overview::instance($g_comp_database);

                $l_start = (((!is_numeric($l_start)) ? 1 : (int) $l_start) - 1) * isys_glob_get_pagelimit();

                $l_limit = $l_start . ', ' . isys_glob_get_pagelimit();

                /* Data contains the ids as comma-separated list of the visible objects in the frontend list */
                if (isys_glob_get_param("data"))
                {
                    $l_data = " AND isys_obj__id IN (" . isys_glob_get_param("data") . ") ";
                }
                $l_res = $l_dao->get_objects_by_type_id($l_objType, $_SESSION['cRecStatusListView'], $l_limit, $l_filter, null, $l_data);

                while ($l_row = $l_res->get_row())
                {
                    $l_objects[] = $l_row['isys_obj__id'];
                }

                // Get category types:
                $l_category_types = $l_dao->get_category_types();

                foreach ($l_category_types as $l_category_type_id => $l_category_type_const)
                {
                    if ($l_category_type_id === C__CMDB__CATEGORY__TYPE_CUSTOM && !defined('C__MODULE__PRO'))
                    {
                        continue;
                    }

                    // Get visible categories in the right order:
                    $l_cat_res = $l_dao->get_categories(
                        $l_objType,
                        $l_category_type_id,
                        C__RECORD_STATUS__NORMAL,
                        true,
                        true
                    );

                    if (is_object($l_cat_res))
                    {
                        while ($l_row = $l_cat_res->get_row())
                        {
                            switch ($l_category_type_id)
                            {
                                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                                    $l_categories[$l_category_type_id][] = $l_row['isysgui_catg__id'];
                                    break;
                                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                                    $l_categories[$l_category_type_id][] = $l_row['isysgui_cats__id'];
                                    break;
                            } // switch
                        } // while
                    }
                } // foreach

                unset($l_dao);

                $l_xsl = file_get_contents('xsl/trans.xsl');

                while (preg_match("/\{\*(.*?)\*\}/", $l_xsl, $l_match))
                {
                    $l_xsl = str_replace("{*" . $l_match[1] . "*}", $g_comp_template_language_manager->get($l_match[1]), $l_xsl);
                }

                $l_xsl = preg_replace('/%%BASE_URL%%/', $g_config['www_dir'], $l_xsl);

                file_put_contents($g_absdir . '/temp/trans.xsl', isys_glob_utf8_encode($l_xsl));
                $l_parser = $l_export->export($l_objects, $l_categories, C__RECORD_STATUS__NORMAL, false)
                    ->parse(null, $g_config['www_dir'] . 'temp/trans.xsl', true);

                $l_xml = $l_parser->get_export();

                header('Content-Type: text/xml; charset=utf-8');

                echo isys_glob_utf8_encode($l_xml);
            }

            die;
        }
    } //function

    /**
     * Gets the module identifier.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return C__MODULE__EXPORT;
    } // function

    /**
     * Shows a list of objects. This is for AJAX calls and prints HTML code.
     */
    private function show_object_list()
    {
        global $g_comp_database, $g_comp_template_language_manager, $g_comp_session;
        $l_global = isys_cmdb_dao_category_g_global::instance($g_comp_database);

        $l_objects = $l_global->get_data(
            null,
            null,
            "AND ((isys_obj__status = '" . C__RECORD_STATUS__NORMAL . "') OR (isys_obj__status = '" . C__RECORD_STATUS__TEMPLATE . "')) " . "AND (isys_obj__isys_obj_type__id NOT IN ('" . C__OBJTYPE__RELATION . "', '" . C__OBJTYPE__PARALLEL_RELATION . "'))",
            $_GET['search']
        );

        if ($l_objects->num_rows() > 0)
        {

            $l_html = "<table class=\"listing\">" . "<colgroup><col width=\"10\" /><col width=\"10\" /></colgroup>" . "<thead>" . "<tr>" . "<th><label><input type=\"checkbox\" onclick=\"CheckAllBoxes(this);\" /></label></th>" . "<th></th>" . "<th>" . $g_comp_template_language_manager->{'LC__CATG__ODEP_OBJ'} . "-ID</th>" . "<th>" . $g_comp_template_language_manager->{'LC__CATG__ODEP_OBJ'} . "</th>" . "<th>" . $g_comp_template_language_manager->{'LC__CMDB__OBJTYPE'} . "</th>" . "<th>Status</th>" . "</tr>" . "</thead>" . "<tbody>";

            while ($l_row = $l_objects->get_row())
            {

                $l_html .= "<tr>" . "<td>" . "<input type=\"checkbox\" class=\"checkbox\" name=\"object[]\" value=\"" . $l_row['isys_obj__id'] . "\" title=\"" . $l_row['isys_obj__title'] . "\" />" . "</td>" . "<td>" . "<a href=\"javascript:\" class=\"bold\" onclick=\"add_object_to_export('" . $l_row['isys_obj__id'] . "', '" . addslashes(
                        $l_row['isys_obj__title']
                    ) . "')\">" . "+" . "</a>" . "</td>" . "<td>" . $l_row["isys_obj__id"] . "</td>" . "<td>" . htmlentities(
                        $l_row["isys_obj__title"]
                    ) . "</td>" . "<td>" . $g_comp_template_language_manager->{$l_global->get_objtype_name_by_id_as_string(
                        $l_global->get_objTypeID($l_row['isys_obj__id'])
                    )} . "</td>" . "<td>" . $l_global->get_object_status_by_id_as_string($l_row["isys_obj__id"]) . "</td>" . "</tr>";
            }
            $l_html .= "</body></table>";
            $l_html .= '<br /><input type="button" onclick="add_selected_objects_to_export();" value="' . $g_comp_template_language_manager->get(
                    'LC__CMDB__EXPORT__ADD'
                ) . '" />';

            echo $l_html;
        }
        else
        {
            printf($g_comp_template_language_manager->{'LC__CMDB__EXPORT__NO_OBJECTS_FOUND'}, $_GET['search']);
        }
    } // function
} // class