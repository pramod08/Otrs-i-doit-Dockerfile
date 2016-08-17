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
 * i-doit Report Manager PRO Version.
 *
 * @package       i-doit
 * @subpackage    Modules
 * @author        Dennis Bluemer <dbluemer@synetics.de>
 * @author        Van Quyen Hoang    <qhoang@synetics.de>
 * @copyright     synetics GmbH
 * @license       http://www.i-doit.com/license
 */
class isys_module_report_pro extends isys_module_report implements isys_module_authable
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = true;
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * URL of the report-repository.
     *
     * @var         string
     * @deprecated  ??
     */
    private $m_report_browser_www = "http://reports-ng.i-doit.org/";

    /**
     * Header of the current report
     *
     * @var array
     */
    private $m_report_headers = [];

    public static function add_external_view($p_report_class_path)
    {
        isys_register::factory('additional-report-views')
            ->set($p_report_class_path);
    } // function

    /**
     * Method for assigning the object types to the dropdown.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @global  isys_component_database $g_comp_database
     */
    public function build_object_types()
    {
        global $g_comp_database;

        $l_result = $g_comp_database->query(
            'SELECT isys_obj_type__id AS id, isys_obj_type__title AS type
            FROM isys_obj_type
            WHERE isys_obj_type__show_in_tree = 1
            OR isys_obj_type__const = "C__OBJTYPE__RELATION";'
        );

        while ($l_row = $g_comp_database->fetch_array($l_result))
        {
            $l_objects[$l_row['id']] = _L($l_row['type']);
        } // while

        asort($l_objects);

        isys_application::instance()->template->assign('object_types', $l_objects);
    } // function

    /**
     * Method for preparing the report-data to view it properly with the TabOrder.
     * This is used by the ajax handler for the online reports and the normal reports.
     *
     * @param   string  $l_query
     * @param   null    $deprecated
     * @param   boolean $p_ajax_request
     * @param   boolean $p_limit
     * @param   boolean $p_raw_data
     * @param   boolean $p_title_chaining
     *
     * @return  mixed  If this method is called by an ajax request, it returns an array. If not, null.
     * @throws  Exception
     * @since   0.9.9-9
     * @author  Leonard Fischer <lfischer@synetic.de>
     */
    public function process_show_report($l_query, $deprecated = null, $p_ajax_request = false, $p_limit = false, $p_raw_data = false, $p_title_chaining = true)
    {
        global $g_comp_database;

        $l_dao     = new isys_report_dao($g_comp_database);
        $l_result  = $l_dao->query($l_query, null, $p_raw_data, $p_title_chaining);
        $l_json    = $l_return = [];
        $l_counter = 0;

        // This is necessary because of UTF8 and JSON complications.
        if ($l_result['grouped'])
        {
            foreach ($l_result['content'] as $l_groupname => $l_group)
            {
                $l_tmp = [];

                foreach ($l_group as $l_data)
                {
                    $l_tmp2 = [];

                    // With this code, we can set the ID at the first place of the table.
                    if (isset($l_data['__id__']))
                    {
                        $l_tmp2['__id__'] = $l_data['__id__'];
                    }

                    foreach ($l_data as $l_key => $l_value)
                    {
                        if (in_array($l_key, $l_result['headers']))
                        {
                            $l_value = strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#', '', $l_value), '<a></a><img/>');

                            // The whitespace at the end fixes #3667.
                            $l_tmp2[$l_key] = _L($l_value) . '&nbsp;';
                        } // if
                    } // foreach

                    $l_tmp[] = $l_tmp2;
                } // foreach

                $l_return[_L($l_groupname)] = isys_format_json::encode($l_tmp);
            } // foreach
        }
        else
        {
            if (is_array($l_result['content']) && count($l_result['content']))
            {
                foreach ($l_result['content'] as $l_data)
                {
                    $l_tmp = [];

                    if ($l_counter == 25 && $p_limit) break;

                    // With this code, we can set the ID at the first place of the table.
                    if (isset($l_data['__id__']))
                    {
                        $l_tmp['__id__'] = $l_data['__id__'];
                    }

                    foreach ($l_data as $l_key => $l_value)
                    {
                        if (in_array($l_key, $l_result['headers']))
                        {
                            $l_value = strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#', '', $l_value), '<span><a></a><img/>');

                            // The whitespace at the end fixes #3667.
                            $l_tmp[$l_key] = _L($l_value) . '&nbsp;';
                        } // if
                    } // foreach

                    $l_return[] = $l_tmp;
                    $l_counter++;
                } // foreach
            } // if

            $l_json = isys_format_json::encode($l_return);
        } // if

        if (isset($l_result['headers']))
        {
            $this->set_report_headers($l_result['headers']);
        } // if

        if ($p_ajax_request)
        {
            return $l_return;
        } // if

        isys_application::instance()->template
            ->assign("listing", $l_result)
            ->assign("result", $l_json);
    } // function

    /**
     * @param      $p_viewdir
     * @param bool $p_as_dialog
     * @param bool $p_check_right
     *
     * @return array
     */
    public function getViews($p_viewdir, $p_as_dialog = false, $p_check_right = false)
    {
        $l_views = [];

        try
        {
            if (is_readable($p_viewdir))
            {
                $l_dirhndl = dir($p_viewdir);

                while ($l_f = $l_dirhndl->read())
                {
                    if (strpos($l_f, ".") !== 0 && $l_f != "isys_report_view.class.php" && $l_f != "isys_report_view_sla.class.php")
                    {
                        $l_class = str_replace(".class.php", "", $l_f);

                        /**
                         * @var isys_report_view
                         */
                        $l_class_object = new $l_class();

                        if (method_exists($l_class_object, 'name'))
                        {
                            if ($p_check_right)
                            {
                                if (!isys_auth_report::instance()
                                    ->is_allowed_to(isys_auth::VIEW, 'VIEWS/' . strtoupper($l_class))
                                )
                                {
                                    continue;
                                }
                            }

                            if (!$p_as_dialog)
                            {
                                $l_views[] = [
                                    "filename"    => $l_f,
                                    "view"        => str_replace(
                                        [
                                            "isys_report_view_",
                                            ".class.php"
                                        ],
                                        "",
                                        $l_f
                                    ),
                                    "class"       => $l_class,
                                    "name"        => _L($l_class_object->name()),
                                    "description" => _L($l_class_object->description()),
                                    "type"        => $l_class_object->type(),
                                    "viewtype"    => $l_class_object->viewtype()
                                ];
                            }
                            else
                            {
                                $l_views[$l_class] = _L($l_class_object->name());
                            } // if
                        }
                    } // if
                } // while
            } // if
            else
            {
                throw new Exception('Report view directory ' . $p_viewdir . ' is not readable.');
            }
            $l_dirhndl->close();

            $l_external_views = isys_register::factory('additional-report-views')
                ->get();

            foreach ($l_external_views as $l_external_view_class => $l_tmp)
            {
                if (is_file($l_external_view_class))
                {
                    include $l_external_view_class;

                    $l_classname = strstr(basename($l_external_view_class), '.class.php', true);

                    $l_class = new $l_classname;

                    if (!$p_as_dialog)
                    {
                        $l_views[] = [
                            "filename"    => basename($l_external_view_class),
                            "view"        => $l_classname . '.tpl',
                            "class"       => $l_classname,
                            "name"        => _L($l_class->name()),
                            "description" => _L($l_class->description()),
                            "type"        => $l_class->type(),
                            "viewtype"    => $l_class->viewtype()
                        ];
                    }
                    else
                    {
                        $l_views[$l_classname] = _L($l_class->name());
                    } // if
                } // if
            } // roreach
        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage());
        }

        // Adding some further report-views, which got added by modules.
        return $l_views;
    } // function

    /**
     * Checks if the report can only be edited via sql editor or not
     *
     * @param $p_id
     *
     * @return bool
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function is_sql_only_report($p_id)
    {
        $l_report = $this->m_dao->get_report($p_id);

        return (empty($l_report['isys_report__querybuilder_data']) ? true : false);
    } // function

    /**
     * Enhances the breadcrumb navigation.
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function breadcrumb_get(&$p_gets)
    {

        $l_result = [];

        switch ($p_gets[C__GET__REPORT_PAGE])
        {
            case C__REPORT_PAGE__CUSTOM_REPORTS:
                $l_title = _L('LC__REPORT__MAINNAV__STANDARD_QUERIES');

                if (isset($p_gets['report_category']))
                {
                    $l_report_category       = current($this->m_dao->get_report_categories($p_gets['report_category']));
                    $l_report_category_title = $l_report_category['isys_report_category__title'];
                }

                if (isset($p_gets[C__GET__REPORT_REPORT_ID]))
                {
                    $l_report_title = $this->m_dao->get_report_title_by_id($p_gets[C__GET__REPORT_REPORT_ID]);
                } // if

                break;
            case C__REPORT_PAGE__REPORT_BROWSER:
                $l_title = _L('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            case C__REPORT_PAGE__VIEWS:
                $l_title = 'Views';

                if (isset($p_gets[C__GET__REPORT_REPORT_ID]))
                {
                    if (class_exists($p_gets[C__GET__REPORT_REPORT_ID]))
                    {
                        $l_report_title = _L($p_gets[C__GET__REPORT_REPORT_ID]::name());
                    } // if
                }
                break;
            default:
                return null;
                break;
        }

        if (isset($l_report_category_title))
        {
            $l_result[] = [
                $l_report_category_title => [
                    C__GET__MODULE_ID   => C__MODULE__REPORT,
                    C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                    C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
                    'report_category'   => $p_gets['report_category']
                ]
            ];
        }
        else
        {
            $l_result[] = [
                $l_title => [
                    C__GET__MODULE_ID   => C__MODULE__REPORT,
                    C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                    C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
                ]
            ];
        } // if

        if (isset($l_report_title))
        {
            $l_result[] = [
                $l_report_title => [
                ]
            ];
        } // if
        return $l_result;
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author    Leonard Fischer <lfischer@i-doit.org>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     * @since     0.9.9-7
     * @see       isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        $l_parent    = -1;
        $l_submodule = '';

        $p_tree->set_tree_sort(false);

        if ($p_system_module)
        {
            $l_parent    = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__REPORT;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(C__MODULE__REPORT . '0', $l_parent, 'Report Manager');
        } // if

        $l_report_root = $p_tree->add_node(
            C__MODULE__REPORT . '2',
            $l_root,
            _L('LC__REPORT__MAINNAV__STANDARD_QUERIES'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__CUSTOM_REPORTS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            '',
            ((($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__CUSTOM_REPORTS || !isset($_GET[C__GET__REPORT_PAGE])) && !isset($_GET['report_category'])) ? 1 : 0),
            '',
            '',
            (isys_auth_report::instance()
                    ->has('custom_report') || isys_auth_report::instance()
                    ->has('reports_in_category'))
        );

        $l_res = $this->m_dao->get_report_categories(null, false);
        while ($l_row = $l_res->get_row())
        {
            $p_tree->add_node(
                C__MODULE__REPORT . '2' . $l_row['isys_report_category__id'],
                $l_report_root,
                $l_row['isys_report_category__title'],
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' .
                $l_row['isys_report_category__id'] . '&' . C__GET__REPORT_PAGE . '=' . C__REPORT_PAGE__CUSTOM_REPORTS . '&report_category=' .
                $l_row['isys_report_category__id'] . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
                '',
                'images/icons/silk/page_portrait.png',
                ((isset($_GET['report_category']) && $_GET['report_category'] == $l_row['isys_report_category__id']) ? 1 : 0),
                '',
                '',
                (isys_auth_report::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'REPORTS_IN_CATEGORY/' . $l_row['isys_report_category__id']))
            );
        } // while

        $p_tree->add_node(
            C__MODULE__REPORT . '3',
            $l_root,
            _L('LC__REPORT__MAINNAV__QUERY_BROWSER'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '3' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__REPORT_BROWSER . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            'images/icons/silk/report_picture.png',
            (($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__REPORT_BROWSER) ? 1 : 0),
            '',
            '',
            isys_auth_report::instance()
                ->has("online_reports")
        );

        $p_tree->add_node(
            C__MODULE__REPORT . '5',
            $l_root,
            'Views',
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '5' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__VIEWS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            'images/icons/silk/report_magnify.png',
            (($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__VIEWS) ? 1 : 0),
            '',
            '',
            isys_auth_report::instance()
                ->has("views")
        );
    } // function

    /**
     * Start module Report Manager.
     *
     * @author    Dennis Blümer <dbluemer@synetics.de>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function start()
    {
        $l_id             = 0;

        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call"))
        {
            $this->processAjaxRequest();
            die;
        } // if

        if (isset($_GET["export"]))
        {
            $this->exportReport($_GET["report_id"], $_GET["type"]);
            die;
        } // if

        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        isys_application::instance()->template
            ->assign('allowedObjectGroup', isys_auth_cmdb::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/C__OBJECT_TYPE__GROUP'));

        try
        {
            switch ($l_gets[C__GET__REPORT_PAGE])
            {
                case C__REPORT_PAGE__REPORT_BROWSER:
                    isys_auth_report::instance()
                        ->check(isys_auth::VIEW, 'ONLINE_REPORTS');
                    $this->processReportBrowser();
                    break;
                case C__REPORT_PAGE__VIEWS:
                    isys_auth_report::instance()
                        ->check(isys_auth::VIEW, 'VIEWS');
                    $this->processViews();
                    break;
                case C__REPORT_PAGE__CUSTOM_REPORTS:
                default:
//					isys_auth_report::instance()->check(isys_auth::VIEW, 'CUSTOM_REPORT');
                    switch ($_POST[C__GET__NAVMODE])
                    {
                        case C__NAVMODE__DUPLICATE:
                            try
                            {
                                $this->duplicate_report($_POST['report_id']);
                                unset($_POST['savedCheckboxes']);
                                $this->processReportList();
                                isys_notify::success(_L('LC__REPORT__POPUP__REPORT_DUPLICATE__SUCCESS'));
                            }
                            catch (Exception $e)
                            {
                                isys_notify::error(_L('LC__REPORT__POPUP__REPORT_DUPLICATE__ERROR'));
                            }
                            break;
                        case C__NAVMODE__EDIT:
                            if (is_array($_POST["id"]))
                            {
                                isys_auth_report::instance()
                                    ->check_report_right(isys_auth::EDIT, $_POST["id"][0]);
                                if (isset($_POST["querybuilder"]) && $_POST["querybuilder"] != '')
                                {
                                    if ((bool) $_POST["querybuilder"])
                                    {
                                        if ($this->is_sql_only_report($_POST["id"][0]))
                                        {
                                            $this->processReportList();
                                            isys_notify::error(_L('LC__REPORT__LIST__EDIT__ERROR'));
                                        }
                                        else
                                        {
                                            $this->processQueryBuilder($_POST["id"][0], $_GET['report_category']);
                                        } // if
                                    }
                                    else
                                    {
                                        $this->editReport($_POST["id"][0]);
                                    } // if
                                }
                                else
                                {
                                    if ($this->is_sql_only_report($_POST["id"][0]))
                                    {
                                        $this->editReport($_POST["id"][0]);
                                    }
                                    else
                                    {
                                        $this->processQueryBuilder($_POST["id"][0], $_GET['report_category']);
                                    } // if
                                } // if
                            }
                            else
                            {
                                $this->processReportList();
                            } // if

                            break;

                        case C__NAVMODE__SAVE:
                            try
                            {
                                if (!empty($_POST['report_mode']))
                                {
                                    isys_auth_report::instance()
                                        ->check(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');
                                    switch ($_POST['report_mode'])
                                    {
                                        case 'category':
                                            if ($_POST['category_selection'] > 0)
                                            {
                                                // update
                                                $this->update_category(
                                                    $_POST['category_selection'],
                                                    $_POST['category_title'],
                                                    $_POST['category_description'],
                                                    $_POST['category_sort']
                                                );
                                            }
                                            else
                                            {
                                                // create
                                                $this->create_category($_POST['category_title'], $_POST['category_description'], $_POST['category_sort']);
                                            } // if
                                            break;
                                        default:
                                            break;
                                    }
                                    $this->processReportList();
                                }
                                else
                                {
                                    try
                                    {
//										isys_auth_report::instance()->check(isys_auth::EXECUTE, 'EDITOR');

                                        if (!empty($_POST['report_id']))
                                        {
                                            // Update
                                            if (!empty($_POST["queryBuilder"]))
                                            {
                                                $this->saveReport($_POST['report_id'], true);
                                            }
                                            else
                                            {
                                                // SQL-Editor
                                                $this->saveReport($_POST['report_id']);
                                            } // if
                                        }
                                        else
                                        {
                                            // Create
                                            if (isset($_POST["queryBuilder"]) && $_POST["queryBuilder"] == 1)
                                            {
                                                // Query Builder
                                                $l_id = $this->createReport(true);
                                            }
                                            else
                                            {
                                                // SQL Editor
                                                $l_id = $this->createReport(false);
                                            }
                                        }
                                        isys_notify::success(_L('LC__REPORT__FORM__SUCCESS'));

                                        if ($l_id > 0)
                                        {
                                            header('Content-Type: application/json');
                                            die(isys_format_json::encode(
                                                [
                                                    'success' => true,
                                                    'id'      => $l_id
                                                ]
                                            ));
                                        }
                                    }
                                    catch (Exception $e)
                                    {
                                        isys_notify::error(_L('LC__REPORT__FORM__ERROR'));
                                    } // try
                                } // if
                            }
                            catch (Exception $e)
                            {
                                isys_notify::error($e->getMessage());
                            } // try
                            break;

                        case C__NAVMODE__NEW:

                            isys_auth_report::instance()
                                ->check(isys_auth::EXECUTE, 'EDITOR');
                            if (isset($_POST['querybuilder']) && $_POST['querybuilder'] != '')
                            {
                                switch ($_POST['querybuilder'])
                                {
                                    case '0':
                                        $this->editReport();
                                        break;
                                    case '1':
                                    default:
                                        $this->processQueryBuilder(null, $_GET['report_category']);
                                        break;
                                }
                            }
                            else
                            {
                                $this->processQueryBuilder(null, $_GET['report_category']);
                            } // if
                            break;

                        case C__NAVMODE__PURGE:
                            if (is_array($_POST["id"]))
                            {
                                foreach ($_POST["id"] as $l_id)
                                {
                                    $this->deleteReport($l_id);
                                } // foreach
                            } // if

                            $this->processReportList();
                            break;

                        default:
                            if (isset($_GET[C__GET__REPORT_REPORT_ID]))
                            {
                                isys_auth_report::instance()
                                    ->check_report_right(isys_auth::EXECUTE, $_GET[C__GET__REPORT_REPORT_ID]);
                                $this->showReport($_GET[C__GET__REPORT_REPORT_ID]);
                            }
                            else
                            {
                                $this->processReportList();
                            } // if
                            break;
                    } // switch
                    break;
            } // switch
        }
        catch (isys_exception_auth $e)
        {
            isys_application::instance()->template
                ->assign("exception", $e->write_log())
                ->include_template('contentbottomcontent', 'exception-auth.tpl');
        }

        // Is the tree part of the system menu?
        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            // Handle the tree.
            $l_tree = isys_module_request::get_instance()->get_menutree();

            $this->build_tree($l_tree, false);

            isys_application::instance()->template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

    } // function

    /**
     * Set header of the current report
     *
     * @param $p_headers
     */
    public function set_report_headers($p_headers)
    {
        $this->m_report_headers = $p_headers;
    } // function

    /**
     * Gets the header of the current report
     *
     * @return array
     */
    public function get_report_headers()
    {
        return $this->m_report_headers;
    } // function

    /**
     * Method for building the report SQL.
     *
     * @return  array
     * @author    Leonard Fischer <lfischer@i-doit.org>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function buildReport()
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

        // Returning the SQL-query and the other dara (title, description, ...).
        $l_return = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'type'              => 'c',
            'userspecific'      => $_POST['chk_user_specific'],
            'query'             => $l_dao->create_property_query_for_report(),
            'report_category'   => $_POST['report_category'],
            'empty_values'      => $_POST['empty_values'],
            'display_relations' => $_POST['display_relations']
        ];
        if (!is_array($_POST['lvls_raw']))
        {
            $l_lvls = isys_format_json::decode($_POST['lvls_raw']);
        }
        else
        {
            $l_lvls = $_POST['lvls_raw'];
        } // if

        if ($l_lvls !== null)
        {
            foreach ($l_lvls AS $l_key => $l_lvl_content)
            {
                foreach ($l_lvl_content AS $l_key2 => $l_content)
                {
                    $l_lvls[$l_key][$l_key2] = isys_format_json::decode($l_content);
                } // foreach
            } // foreach
        } // if

        if (!is_array($_POST['querycondition']))
        {
            $l_condition = isys_format_json::decode($_POST['querycondition']);
        }
        else
        {
            $l_condition = $_POST['querycondition'];
        } // if

        $l_arr = [
            'main_object' => isys_format_json::decode($_POST['report__HIDDEN']),
            'lvls'        => $l_lvls,
            'conditions'  => $l_condition
        ];

        $l_return['querybuilder_data'] = isys_format_json::encode($l_arr);

        if (!empty($_GET[C__GET__REPORT_REPORT_ID]) || !empty($_POST['report_id']))
        {
            $l_return['report_id'] = (!empty($_POST['report_id']) ? $_POST['report_id'] : $_GET[C__GET__REPORT_REPORT_ID]);
        } // if

        return $l_return;
    } // function

    /**
     * Updates reports
     *
     * @param      $p_id
     * @param bool $p_querybuilder
     *
     * @return bool
     */
    private function saveReport($p_id, $p_querybuilder = false)
    {
        if ($p_querybuilder)
        {
            $l_report = new isys_report($this->buildReport());
            $l_report->update();
        }
        else
        {
            return $this->m_dao->saveReport(
                $p_id,
                $_POST["title"],
                $_POST["description"],
                $_POST["query"],
                null,
                (($_POST['chk_user_specific'] == 'on' ? true : false)),
                $_POST['report_category']
            );
        }

        return false;
    } // function

    /**
     * Duplicates Report
     *
     * @param $p_report_id
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function duplicate_report($p_report_id)
    {
        $l_report          = $this->m_dao->get_report($p_report_id);
        $l_param           = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'query'             => $l_report['isys_report__query'],
            'userspecific'      => ($_POST['chk_user_specific'] == 'on') ? 1 : 0,
            'querybuilder_data' => $l_report['isys_report__querybuilder_data'],
            'report_category'   => $_POST['category_selection'],
            'type'              => $l_report['isys_report__type'],
            'empty_values'      => $l_report['isys_report__empty_values'],
            'display_relations' => $l_report['isys_report__display_relations']
        ];
        $l_report_instance = new isys_report($l_param);
        $l_report_id       = $l_report_instance->store();

        // Add right
        $this->add_right($l_report_id, 'custom_report');
    } // function

    /**
     * This Method is a helper method which adds the rights for custom_report or reports_in_category
     *
     * @param $p_id
     * @param $p_method
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function add_right($p_id, $p_method)
    {
        global $g_comp_session, $g_comp_database;

        // Check if user has wildcard rights for CUSTOM_REPORTS and REPORTS
        switch ($p_method)
        {
            case 'custom_report':
                if (isys_auth_report::instance()
                        ->get_allowed_reports() === true
                )
                {
                    return;
                }
                break;
            case 'reports_in_category':
                if (isys_auth_report::instance()
                        ->get_allowed_report_categories() === true
                )
                {
                    return;
                }
                break;
        } // switch

        $l_user_id   = (int) $g_comp_session->get_user_id();
        $l_path_data = [$p_method => [$p_id => [isys_auth::SUPERVISOR]]];
        // Add right for the report
        isys_auth_dao::instance($g_comp_database)
            ->create_paths($l_user_id, C__MODULE__REPORT, $l_path_data);
        isys_caching::factory('auth-' . $l_user_id)
            ->clear();
    } // function

    /**
     * @param $p_id
     *
     * @return bool
     */
    private function deleteReport($p_id)
    {
        global $g_comp_session;

        $l_report = $this->m_dao->get_report($p_id);

        if ($g_comp_session->get_user_id() != $l_report['isys_report__user'])
        {
            isys_auth_report::instance()
                ->check(isys_auth::DELETE, 'CUSTOM_REPORT/' . $p_id);
        } // if

        return $this->m_dao->deleteReport($p_id);
    } // function

    /**
     * @param $p_id
     */
    private function editReport($p_id = null)
    {
        global $g_comp_session;

        switch ($_POST[C__GET__NAVMODE])
        {
            case C__NAVMODE__EDIT:
            case C__NAVMODE__NEW:
                $l_navbar = isys_component_template_navbar::getInstance();
                $l_navbar->set_save_mode('ajax')
                    ->set_ajax_return('ajaxReturnNote')
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
        } //switch

        $l_title              = null;
        $l_selected_category  = null;
        $l_report_description = null;
        $l_query              = null;
        $l_user_specific      = null;
        $l_my_report          = false;

        if ($p_id !== null)
        {
            $l_report = $this->m_dao->get_report($p_id);

            $l_title              = $l_report["isys_report__title"];
            $l_selected_category  = $l_report['isys_report__isys_report_category__id'];
            $l_report_description = $l_report["isys_report__description"];
            $l_query              = isys_glob_htmlentities($l_report["isys_report__query"]);
            $l_user_specific      = $l_report["isys_report__user_specific"];

            if ($g_comp_session->get_mandator_id() == $l_report['isys_report__mandator'] && $g_comp_session->get_user_id() == $l_report['isys_report__user'])
            {
                $l_my_report = true;
            }
            else
            {
                isys_auth_report::instance()
                    ->check(isys_auth::EDIT, 'CUSTOM_REPORT/' . $p_id);
            }
        }

        $l_report_category_data = $this->m_dao->get_report_categories();
        $l_data                 = [];
        foreach ($l_report_category_data AS $l_report_category)
        {
            $l_data[$l_report_category['isys_report_category__id']] = $l_report_category['isys_report_category__title'];
        } // foreach

        $l_rules = [
            'title'             => ['p_strValue' => $l_title],
            'report_category'   => [
                'p_strSelectedID' => $l_selected_category,
                'p_arData'        => $l_data
            ],
            'description'       => ['p_strValue' => $l_report_description],
            'query'             => ['p_strValue' => $l_query],
            'chk_user_specific' => [
                'p_bChecked'    => $l_user_specific,
                'p_strDisabled' => (!$l_my_report)
            ],
        ];

        if (!empty($l_report["isys_report__querybuilder_data"]))
        {
            isys_application::instance()->template->assign("querybuilder_warning", _L('LC__REPORT__EDIT__WARNING_TEXT'));
        } // if

        isys_application::instance()->template
            ->assign('content_title', _L('LC__REPORT__LIST__SQL_EDITOR'))
            ->assign("report_id", $l_report["isys_report__id"])
            ->activate_editmode()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_edit.tpl');
    } // function

    /**
     * @param $p_id
     * @param $p_type
     *
     * @throws Exception
     */
    private function exportReport($p_id, $p_type)
    {
        $l_row = $this->m_dao->get_report($p_id);

        $l_report = [
            "report_id"   => $l_row["isys_report__id"],
            "type"        => $l_row["isys_report__type"],
            "title"       => $l_row["isys_report__title"],
            "description" => $l_row["isys_report__description"],
            "query"       => $l_row["isys_report__query"],
            "mandator"    => $l_row["isys_report__mandator"],
            "datetime"    => $l_row["isys_report__datetime"],
            "last_edited" => $l_row["isys_report__last_edited"]
        ];

        try
        {
            isys_application::instance()->session->write_close();

            $report = new \idoit\Module\Report\Report(
                new isys_component_dao(isys_application::instance()->database),
                $l_row["isys_report__query"],
                $l_row["isys_report__title"],
                $l_row["isys_report__id"],
                $l_row["isys_report__type"]
            );

            switch ($p_type)
            {
                case "xml":
                    $l_report = new isys_report_xml($l_report);
                    break;

                case "csv":
                    \idoit\Module\Report\Export\CsvExport::factory($report)
                        ->export()
                        ->output();
                    die;

                    break;
                case "txt":
                    \idoit\Module\Report\Export\TxtExport::factory($report)
                        ->export()
                        ->output();
                    die;

                    break;

                case "pdf":
                    $l_report['title'] = utf8_decode($l_report['title']); // Bugfix for ID-2182
                    $l_report          = new isys_report_pdf($l_report);
                    break;

                default:
                    throw new Exception("Missing or unknown export type");
            } // switch

            $l_report->export();
        }
        catch (\idoit\Exception\OutOfMemoryException $e)
        {
            isys_application::instance()->container["notify"]->error($e->getMessage());

            throw $e;
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try
    } // function

    /**
     * Method for processing ajax requests.
     */
    private function processAjaxRequest()
    {
        global $g_comp_session;

        $g_comp_session->write_close();

        switch (isys_glob_get_param("request"))
        {
            case "executeReport":

                $l_query = stripslashes($_POST["query"]);

                try
                {
                    $this->process_show_report($l_query);

                    isys_application::instance()->template
                        ->assign("report_id", $_POST["reportID"])
                        ->assign("reportTitle", $_POST["title"])
                        ->assign("reportDescription", $_POST["desc"])
                        ->display($this->get_tpl_dir() . "report_result.tpl");
                }
                catch (Exception $e)
                {
                    isys_glob_display_error($e->getMessage());
                } // try

                break;

            case "downloadReport":
                global $g_comp_database_system;

                if (isys_auth_report::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'ONLINE_REPORTS')
                )
                {
                    $l_dao = new isys_report_dao($g_comp_database_system);

                    if ($l_dao->reportExists($_POST["title"], $_POST["query"]))
                    {
                        isys_notify::warning(_L("LC__REPORT__EXISTS"));
                    }
                    else
                    {
                        $l_global_id = $l_dao->get_report_categories('Global', false)
                            ->get_row_value('isys_report_category__id');
                        if ($l_dao->createReport($_POST["title"], $_POST["desc"], $_POST["query"], null, false, false, $l_global_id) !== false)
                        {
                            isys_notify::success(_L("LC__REPORT__DOWNLOAD_SUCCESSFUL"));
                        }
                        else
                        {
                            isys_notify::error(_L("LC__REPORT__ERROR_SAVING"));
                        } // if
                    } // if
                }
                else
                {
                    isys_notify::error(_L("LC__UNIVERSAL__NO_ACCESS_RIGHTS"));
                }
                break;

            default:
                if (isset($_GET["reportID"]))
                {
                    $l_class = $_GET["reportID"];

                    if (class_exists($l_class))
                    {
                        $l_obj = new $l_class();

                        if (method_exists($l_obj, "ajax_request"))
                        {
                            $l_obj->ajax_request();
                        } // if

                        unset($l_obj);
                    } // if
                } // if
                break;
        } // switch
    } // function

    /**
     * Method for displaying a report.
     *
     * @global  isys_component_database $g_comp_database_system
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_id
     */
    private function showReport($p_id)
    {
        global $g_comp_database_system, $g_comp_database;

        $l_dao    = new isys_report_dao($g_comp_database_system);
        $l_report = $l_dao->get_report($p_id);

        try
        {
            $l_ajax_pager = 'false';

            // We use this DAO because here we defined how many pages we want to preload.
            $l_dao = new isys_cmdb_dao_list_objects($g_comp_database);

            /**
             * Count rows based on the complete statement (as a fallback)
             *
             * @param $l_q
             *
             * @return array
             * @throws isys_exception_database
             */
            $l_checkfallback = function($l_q) use($l_dao) {
                // If our first try fails because we broke the SQL, we use this here...
                return [
                    'count' => $l_dao->retrieve($l_q)->num_rows()
                ];
            };

            /**
             * Check for inner selects
             *
             * @see https://i-doit.atlassian.net/browse/ID-3099
             */
            if (preg_match('/SELECT.*?^[SELECT].*?FROM/is', $l_report["isys_report__query"]))
            {
                // First we modify the SQL to find out, with how many rows we are dealing...
                $l_rowcount_sql = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(*) as count FROM', $l_report["isys_report__query"], 1);

                try
                {
                    $l_num_rows = $l_dao->retrieve($l_rowcount_sql)
                        ->num_rows();

                    if ($l_num_rows == 1)
                    {
                        $l_rowcount = $l_dao->retrieve($l_rowcount_sql)
                            ->get_row();
                    }
                    else
                    {
                        $l_rowcount['count'] = $l_num_rows;
                    } // if
                }
                catch (Exception $e)
                {
                    // If our first try fails because we broke the SQL, we use this here...
                    $l_rowcount = $l_checkfallback($l_report["isys_report__query"]);
                } // try
            }
            else
            {
                $l_rowcount = $l_checkfallback($l_report["isys_report__query"]);
            }

            $l_preloadable_rows = isys_glob_get_pagelimit() * $l_dao->get_preload_pages();

            // If we get more rows than our defined preloading allowes, we need the ajax pager.
            if ($l_preloadable_rows < $l_rowcount['count'] && !strpos($l_report["isys_report__query"], 'LIMIT'))
            {
                // First we append an offset to the report-query.
                $l_report["isys_report__query"] = rtrim(trim($l_report["isys_report__query"]), ';') . ' LIMIT 0, ' . $l_preloadable_rows . ';';

                // Here we prepare the URL for the ajax pagination.
                $l_ajax_url   = '?ajax=1&call=report&func=ajax_pager&report_id=' . $p_id;
                $l_ajax_pager = 'true';

                isys_application::instance()->template
                    ->assign('ajax_url', $l_ajax_url)
                    ->assign('preload_pages', $l_dao->get_preload_pages())
                    ->assign('max_pages', ceil($l_rowcount['count'] / isys_glob_get_pagelimit()));
            } // if

            $this->process_show_report($l_report["isys_report__query"]);

            isys_application::instance()->template
                ->assign("rowcount", $l_rowcount['count'])
                ->assign("ajax_pager", $l_ajax_pager)
                ->assign("report_id", $p_id)
                ->assign("reportTitle", $l_report["isys_report__title"])
                ->assign("reportDescription", $l_report["isys_report__description"])
                ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_execute.tpl');
        }
        catch (Exception $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());

            $this->processReportList();
        } // try
    } // function

    private function processViews()
    {
        global $g_absdir;

        if (!isset($_GET["reportID"]) || !class_exists($_GET["reportID"]))
        {
            $l_header = [
                "isys_viewtype"    => _L("LC__CMDB__CATG__TYPE"),
                "isys_name"        => _L("LC__UNIVERSAL__TITLE"),
                "isys_description" => _L("LC__LANGUAGEEDIT__TABLEHEADER_DESCRIPTION")
            ];

            $l_viewdir = $g_absdir . "/src/classes/modules/report/views/";
            if (is_dir($l_viewdir) && is_readable($l_viewdir))
            {
                $l_views = $this->getViews($l_viewdir);

                $l_list = new isys_component_list($l_views, null, null, null);
                $l_list->disable_dragndrop();

                $l_rowLink = isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__REPORT_REPORT_ID . "=[{class}]");
                $l_list->config($l_header, $l_rowLink, "[{isys_report__id}]");

                if ($l_list->createTempTable())
                {
                    isys_application::instance()->template->assign("objectTableList", $l_list->getTempTableHtml());
                }

                isys_application::instance()->template->assign("content_title",_L("LC__REPORT__MAINNAV__STANDARD_QUERIES"));

            }
            else
            {
                isys_application::instance()->container['notify']->error("Could not read dir: " . $l_viewdir);
            }

            isys_application::instance()->template
                ->assign("content_title", "Report-Views")
                ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');
        }
        else
        {
            $l_class = strtoupper($_GET["reportID"]);

            isys_auth_report::instance()
                ->check(isys_auth::VIEW, 'VIEWS/' . $l_class);

            if (class_exists($l_class))
            {
                $l_view = new $l_class();

                isys_application::instance()->template->assign("content_title", _L($l_view->name()));
                $l_tpl = '';

                if ($l_view->external() === false)
                {
                    $l_tpl = $this->get_tpl_dir();
                } // if

                isys_application::instance()->template->assign("viewTemplate", $l_tpl . $l_view->template());

                if ($l_view->init())
                {
                    $l_view->start();
                }

                isys_application::instance()->template
                    ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'view.tpl');

            } // if
            else throw new Exception("Error: Report does not exist.");
        } // if
    } // function

    /**
     * Shows the report list
     */
    private function processReportList()
    {
        global $g_comp_database_system;

        $l_allowed_reports = isys_auth_report::instance()
            ->get_allowed_reports();

        $l_dao             = new isys_report_dao($g_comp_database_system);
        $l_report_category = (isset($_GET['report_category'])) ? $_GET['report_category'] : null;

        if ($l_report_category !== null && !$l_dao->check_report_category($l_report_category))
        {
            $l_report_category = null;
        } // if

        $l_reports = $l_dao->get_reports(null, $l_allowed_reports, $l_report_category);

        $l_header = [
            "isys_report__id"          => "ID",
            "isys_report__title"       => "LC__UNIVERSAL__TITLE",
            "category_title"           => "LC_UNIVERSAL__CATEGORY",
            "with_qb"                  => "LC__REPORT__LIST__VIA_QUERY_BUILDER_CREATED",
            "isys_report__description" => "LC__UNIVERSAL__DESCRIPTION"
        ];

        if (isset($_GET['call']) || isset($_GET['ajax']))
        {
            $l_gets = $_GET;
            unset($l_gets['call']);
            unset($l_gets['ajax']);
        }
        else
        {
            $l_gets = $_GET;
        } // if

        $l_rowLink = isys_glob_build_url(http_build_query($l_gets) . "&" . C__GET__REPORT_REPORT_ID . "=[{isys_report__id}]");

        $l_list = (new isys_component_list(null, $l_reports, null, null))->config($l_header, $l_rowLink, "[{isys_report__id}]")
            ->set_row_modifier($l_dao, 'modify_row');

        if ($l_list->createTempTable())
        {
            isys_application::instance()->template->assign("objectTableList", $l_list->getTempTableHtml());
        } // if

        isys_application::instance()->template->assign("content_title", _L("LC__REPORT__MAINNAV__STANDARD_QUERIES"));

        $l_new_overlay = [
            [
                "title"   => _L('LC__REPORT__MAINNAV__QUERY_BUILDER'),
                "icon"    => "icons/silk/brick_add.png",
                "href"    => "javascript:;",
                "onclick" => "document.isys_form.navMode.value='" . C__NAVMODE__NEW . "'; document.isys_form.submit();",
            ],
            [
                "title"   => _L("LC__REPORT__LIST__SQL_EDITOR"),
                "icon"    => "icons/silk/application_form_add.png",
                "href"    => "javascript:;",
                "onclick" => "$('querybuilder').value=0;document.isys_form.navMode.value='" . C__NAVMODE__NEW . "'; document.isys_form.submit();",
            ]
        ];

        $l_edit_overlay = [
            [
                "title"   => _L('LC__REPORT__MAINNAV__QUERY_BUILDER'),
                "icon"    => "icons/silk/brick_edit.png",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__EDIT,
                "onclick" => "$('querybuilder').value=1;document.isys_form.sort.value='';document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; form_submit();"
            ],
            [
                "title"   => _L("LC__REPORT__LIST__SQL_EDITOR"),
                "icon"    => "icons/silk/application_form_edit.png",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__EDIT,
                "onclick" => "$('querybuilder').value=0;document.isys_form.sort.value='';document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; form_submit();"
            ]
        ];

        $l_rights_create          = isys_auth_report::instance()
            ->is_allowed_to(isys_auth::EXECUTE, 'EDITOR');
        $l_rights_report_category = isys_auth_report::instance()
            ->is_allowed_to(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');

        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote')
            ->set_overlay($l_new_overlay, C__NAVBAR_BUTTON__NEW)
            ->set_overlay($l_edit_overlay, 'edit')
            ->set_active($l_rights_create, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_rights_create, C__NAVBAR_BUTTON__NEW);

        if ($l_allowed_reports)
        {
            $l_navbar->append_button(
                _L('LC__AUTH__RIGHT_EDIT'),
                'edit',
                [
                    "icon"                => "icons/silk/page_edit.png",
                    "navmode"             => C__NAVMODE__EDIT,
                    "add_onclick_prepend" => "$('querybuilder').value='';",
                    "accesskey"           => "e"
                ]
            );
        } // if

        if ($l_rights_report_category)
        {
            $l_navbar->append_button(
                _L('LC_UNIVERSAL__CATEGORIES'),
                'report_category',
                [
                    "icon"       => "icons/silk/application_form_add.png",
                    "navmode"    => C__NAVMODE__DUPLICATE,
                    "js_onclick" => " onclick=\"get_popup('report', null, '480', '270', {'func':'show_category'});\""
                ]
            );
        } // if

        if ($l_allowed_reports)
        {
            $l_navbar->append_button(
                _L('LC__NAVIGATION__NAVBAR__DUPLICATE'),
                'duplicate_report',
                [
                    "icon"       => "icons/silk/page_copy.png",
                    "navmode"    => C__NAVMODE__DUPLICATE,
                    "js_onclick" => " onclick=\"get_popup('report', null, '480', '260', {'func':'show_duplicate'});\""
                ]
            )
                ->append_button(
                    _L('LC__NAVIGATION__NAVBAR__PURGE'),
                    'purge',
                    [
                        "navmode"   => C__NAVMODE__PURGE,
                        "icon"      => "icons/silk/page_delete.png",
                        "accesskey" => "d",
                        'js_onclick' => "if (confirm('" . _L('LC__REPORT__CONFIRM_PURGE') . "')) {\$('navMode').setValue(6); form_submit(null, null, null, null, null, get_listSelection4Submit());}"
                    ]
                );
        } // if

        isys_application::instance()->template
            ->assign("querybuilder", 1)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_list.tpl');
    } // function

    private function processReportBrowser()
    {
        isys_application::instance()->template
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_browser.tpl');
    } // function

    /**
     * Shows the querybuilder
     *
     * @param int $p_reportID
     * @param int $p_report_category_id
     */
    private function processQueryBuilder($p_reportID = null, $p_report_category_id = null)
    {
        isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote')
            ->set_active(true, C__NAVBAR_BUTTON__SAVE);

        $l_rules = [];

        // Add the object types to the select-box.
        $this->build_object_types();

        // States array
        $l_arr_states = [
            _L('LC__CMDB__RECORD_STATUS__ALL') . ' (0)',
            _L('LC__CMDB__RECORD_STATUS__BIRTH') . ' (1)',
            _L('LC__CMDB__RECORD_STATUS__NORMAL') . ' (2)',
            _L('LC__CMDB__RECORD_STATUS__ARCHIVED') . ' (3)',
            _L('LC__CMDB__RECORD_STATUS__DELETED') . ' (4)'
        ];

        $l_report_category_data      = $this->m_dao->get_report_categories();
        $l_allowed_report_categories = isys_auth_report::instance()
            ->get_allowed_report_categories();
        $l_global_report_category_id = null;

        $l_data = [];
        if ((count($l_allowed_report_categories) > 0 || $l_allowed_report_categories === true) && $l_allowed_report_categories !== false)
        {
            foreach ($l_report_category_data AS $l_report_category)
            {
                if (strtolower($l_report_category['isys_report_category__title']) == 'global')
                {
                    $l_global_report_category_id = $l_report_category['isys_report_category__id'];
                }
                if ($l_allowed_report_categories === true)
                {
                    // Wildcard
                    $l_data[$l_report_category['isys_report_category__id']] = $l_report_category['isys_report_category__title'];
                }
                elseif (is_array($l_allowed_report_categories) && in_array($l_report_category['isys_report_category__id'], $l_allowed_report_categories))
                {
                    $l_data[$l_report_category['isys_report_category__id']] = $l_report_category['isys_report_category__title'];
                } // if
            } // foreach
        }
        else
        {
            $l_report_category_data                                      = $this->m_dao->get_report_categories('Global', false)
                ->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        } // if

        if ($p_report_category_id !== null)
        {
            isys_application::instance()->template->assign('category_selected', $p_report_category_id);
        }
        else
        {
            isys_application::instance()->template->assign('category_selected', $l_global_report_category_id);
        } // if

        $l_display_relation = 0;

        if ($p_reportID !== null)
        {
            $l_report            = $this->m_dao->get_report($p_reportID);
            $l_querybuilder_data = isys_format_json::decode($l_report['isys_report__querybuilder_data']);
            $l_conditions        = array_slice($l_querybuilder_data, 2, 1);

            $l_display_relation = $l_report['isys_report__display_relations'] ?: 0;

            isys_application::instance()->template
                ->assign('category_selected', $l_report['isys_report__isys_report_category__id'])
                ->assign('empty_values_selected', $l_report['isys_report__empty_values'])
                ->assign('report_id', $p_reportID)
                ->assign('chk_user_specific', $l_report["isys_report__user_specific"])
                ->assign('preselection_data', isys_format_json::encode($l_querybuilder_data['main_object']))
                ->assign('preselection_lvls', isys_format_json::encode($l_querybuilder_data['lvls']))
                ->assign('report_title', $l_report['isys_report__title'])
                ->assign('report_description', $l_report['isys_report__description'])
                ->assign('querybuilder_conditions', ((count($l_conditions['conditions']) > 0) ? isys_format_json::encode($l_conditions) : null));
        }
        else
        {
            $l_rules['report']['preselection'] = '[{"g":{"C__CATG__GLOBAL":["title"]}}]';
        } // if

        // Assign the title and make the save/cancel buttons invisible.
        isys_application::instance()->template
            ->assign('category_data', serialize($l_data))
            ->assign("content_title", _L("LC__REPORT__MAINNAV__QUERY_BUILDER"))
            ->assign("yes_or_no", get_smarty_arr_YES_NO())
            ->assign('display_relations_selected', $l_display_relation)
            ->assign("states_arr", $l_arr_states)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'querybuilder.tpl');
    } // function

    /**
     * Method for inserting an report into the database.
     *
     * @param   boolean $p_querybuilder
     *
     * @return  mixed  boolean, null or integer
     */
    private function createReport($p_querybuilder)
    {
        $l_id = null;

        try
        {
            if ($p_querybuilder)
            {
                $l_report = new isys_report($this->buildReport());
                $l_id     = $l_report->store();
            }
            else
            {
                $l_id = $this->m_dao->createReport(
                    $_POST["title"],
                    $_POST["description"],
                    $_POST["query"],
                    null,
                    false,
                    $_POST['chk_user_specific'],
                    $_POST['report_category']
                );
            } // if

            // Add right
            $this->add_right($l_id, 'custom_report');
        }
        catch (Exception $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        } // try

        return $l_id;
    } // function

    /**
     * Method to create a new report category
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function create_category($p_title, $p_description = null, $p_sorting = 99)
    {
        if (strlen(trim($p_title)))
        {
            $l_last_id = $this->m_dao->create_category(trim($p_title), $p_description, $p_sorting);

            if ($l_last_id)
            {
                // Add auth right.
                $this->add_right($l_last_id, 'reports_in_category');
            } // if

            return $l_last_id;
        } // if
        return false;
    } // function

    /**
     * Method to update an existing report category
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function update_category($p_id, $p_title, $p_description = null, $p_sorting = 99)
    {
        if (strlen(trim($p_title)))
        {
            return $this->m_dao->update_category($p_id, trim($p_title), $p_description, $p_sorting);
        } // if

        return false;
    } // function

    /**
     * Constructor method to be sure, there's a DAO instance.
     */
    public function __construct()
    {
        global $g_comp_database_system;

        if ($this->m_dao === null)
        {
            $this->m_dao = new isys_report_dao($g_comp_database_system);
        } // if
    } // function
} // class