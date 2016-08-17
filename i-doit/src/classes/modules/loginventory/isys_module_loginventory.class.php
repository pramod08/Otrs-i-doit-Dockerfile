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
 * Loginventory module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_module_loginventory extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines whether this module will be displayed in the named menus:
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * Node for import.
     */
    const C__IMPORT = 'import';
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * Instance of database component.
     *
     * @var  isys_component_database
     */
    protected $m_db;
    /**
     * Instance of import module.
     *
     * @var  isys_module_import
     */
    protected $m_import_module;
    /**
     * Import mode.
     *
     * @var  integer
     */
    protected $m_mode;
    /**
     * Module identifier.
     *
     * @var  integer
     */
    protected $m_module_id;
    /**
     * @var isys_component_template_navbar
     */
    protected $m_navbar;
    /**
     * Current node.
     *
     * @var  integer
     */
    protected $m_node;
    /**
     * Nodes.
     *
     * @var  array
     */
    protected $m_nodes;
    /**
     * User request.
     *
     * @var  isys_module_request
     */
    protected $m_userrequest;

    /**
     * Static factory method.
     *
     * @static
     * @return  isys_module_loginventory
     */
    public static function factory()
    {
        return new self();
    } // function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_system::instance();
    } // function

    /**
     * Enhances the breadcrumb navigation.
     */
    public function breadcrumb_get(&$p_gets)
    {
        ; // Not implemented yet.
    } // function

    /**
     * Builds menu tree.
     *
     * @param  isys_component_tree $p_tree
     * @param  boolean             $p_system_module Is it a system module? Defaults to true.
     * @param  integer             $p_parent        Parent identifier. Defaults to null.
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_dirs;

        if (defined('C__MODULE__LOGINVENTORY'))
        {
            if ($p_system_module)
            {
                $l_root = $p_tree->add_node(C__MODULE__LOGINVENTORY . '0', $p_parent, 'LOGINventory');

                $p_tree->add_node(
                    C__MODULE__LOGINVENTORY . 9,
                    $l_root,
                    _L('LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_CONFIGURATION'),
                    '?moduleID=' . C__MODULE__SYSTEM . '&what=loginventory_configuration' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LOGINVENTORY . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LOGINVENTORY . 9,
                    null,
                    $g_dirs['images'] . '/icons/loginventory.png',
                    ($_GET['what'] == 'loginventory_configuration') ? 1 : 0,
                    '',
                    '',
                    isys_auth_system::instance()
                        ->is_allowed_to(
                            isys_auth::SUPERVISOR,
                            'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '9'
                        )
                );

                $p_tree->add_node(
                    C__MODULE__LOGINVENTORY . 10,
                    $l_root,
                    _L('LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_DATABASES'),
                    '?moduleID=' . C__MODULE__SYSTEM . '&what=loginventory_databases' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LOGINVENTORY . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LOGINVENTORY . '10',
                    null,
                    $g_dirs['images'] . '/icons/loginventory.png',
                    ($_GET['what'] == 'loginventory_databases') ? 1 : 0,
                    '',
                    '',
                    isys_auth_system::instance()
                        ->is_allowed_to(
                            isys_auth::SUPERVISOR,
                            'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '10'
                        )
                );
            }
            else
            {
                $this->m_import_module->build_tree($p_tree, $p_system_module, $p_parent);
            }
        }
    } // function

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @return  null
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        ; // Not implemented yet.
    } // function

    /**
     * Starts module. Acts as a dispatcher for nodes and actions.
     */
    public function start()
    {
        global $index_includes;

        $l_gets  = $this->m_userrequest->get_gets();
        $l_posts = $this->m_userrequest->get_posts();

        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call"))
        {
            $this->processAjaxRequest($l_posts);
            die;
        } // if

        if (array_key_exists('what', $l_gets))
        {
            $this->m_node = str_replace('loginventory_', '', $l_gets['what']);
        }
        else
        {
            $this->m_node = self::C__IMPORT;
        } //if

        try
        {
            $l_method = 'handle_' . $this->m_node;
            $this->$l_method($l_gets, $l_posts);
        }
        catch (isys_exception_general $e)
        {
            throw $e;
        }
        catch (isys_exception_auth $e)
        {
            $this->m_userrequest->get_template()
                ->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    } // function

    /**
     * Initiates module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_loginventory
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = $p_req;

        return $this;
    }

    public function import($p_li_ids, $p_db_id, $p_default_obj_type = null)
    {
        $this->m_dao->set_dialog_cache();
        $l_config = $this->m_dao->get_configuration();

        $this->m_dao->import($p_db_id, $l_config, $p_li_ids, $p_default_obj_type);

        return true;
    }

    private function processAjaxRequest($p_posts)
    {
        global $g_comp_session;
        $g_comp_session->write_close();

        switch (isys_glob_get_param("request"))
        {
            case "import":
                $this->m_log->set_log_level(isys_log::C__INFO);
                $this->import($p_posts['id'], $p_posts['selected_loginventory_db'], $p_posts['C__LOGINVENTORY__OBJTYPE']);

                echo $this->m_log->flush_log(false, false);
                break;
            default:
                break;
        }
    }

    /**
     * Handles the Loginventory databases
     *
     * @param $p_gets
     * @param $p_posts
     */
    private function handle_databases($p_gets, $p_posts)
    {
        $l_dbID = $p_gets['dbID'];
        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '10');

        switch ($p_posts[C__GET__NAVMODE])
        {
            case C__NAVMODE__NEW:
                $this->m_userrequest->get_template()
                    ->activate_editmode();
                $this->loginventory_db(null, $p_posts);
                break;

            case C__NAVMODE__SAVE:
                $this->m_dao->save_loginventory_db($l_dbID, $p_posts);
                break;

            case C__NAVMODE__PURGE:
                $this->m_dao->delete_loginventory_db($p_posts["id"]);
            case C__NAVMODE__CANCEL:
                $this->loginventory_db_list();
                break;
            default:
                if ($l_dbID != null)
                {
                    $this->loginventory_db($l_dbID, $p_posts);
                } // if
                else if ($p_posts["id"] != null)
                {
                    $this->loginventory_db($p_posts["id"][0], $p_posts);
                }
                else
                {
                    $this->loginventory_db_list();
                } // if
                break;
        } // switch
    } // function

    /**
     * Shows the selected database config
     *
     * @param null $p_db_id
     * @param      $p_posts
     */
    private function loginventory_db($p_db_id = null, $p_posts)
    {
        global $index_includes;

        $l_template   = $this->m_userrequest->get_template();
        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '10');

        $this->m_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL)
            ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);

        $l_rules = [];
        if ($p_db_id > 0)
        {
            $l_data = $this->m_dao->get_loginventory_databases($p_db_id);

            foreach ($l_data AS $l_key => $l_value)
            {
                $l_rules['C__MODULE__IMPORT__LOGINVENTORY_' . strtoupper(str_replace('isys_loginventory_db__', '', $l_key))]['p_strValue'] = $l_value;
            }
            $l_template->assign('dbID', $p_db_id);
        }
        else
        {
            $l_rules['C__MODULE__IMPORT__LOGINVENTORY_PORT']['p_strValue']   = 3108;
            $l_rules['C__MODULE__IMPORT__LOGINVENTORY_SCHEMA']['p_strValue'] = 'loginventory';
        }

        $l_template->activate_editmode();
        $l_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes['contentbottomcontent'] = 'modules/loginventory/database.tpl';
    } // function

    /**
     * Shows the database list
     */
    private function loginventory_db_list()
    {
        global $index_includes;

        $l_list = new isys_component_list();

        $l_template = $this->m_userrequest->get_template();

        $l_edit_right   = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '10');
        $l_delete_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::DELETE, 'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '10');

        $l_list_headers = [
            "isys_loginventory_db__id"     => "ID",
            "isys_loginventory_db__host"   => "Host",
            "isys_loginventory_db__schema" => "Schema"
        ];

        $l_list_data = $this->m_dao->get_loginventory_databases();
        $this->m_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active((count($l_list_data) > 0) ? $l_edit_right : false, C__NAVBAR_BUTTON__EDIT)
            ->set_active((count($l_list_data) > 0) ? $l_delete_right : false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

        if (count($l_list_data) > 0)
        {
            $l_list->set_data($l_list_data);
            $l_list->config(
                $l_list_headers,
                '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LOGINVENTORY . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . "&what=loginventory_databases&dbID=[{isys_loginventory_db__id}]",
                "[{isys_loginventory_db__id}]"
            );

            if ($l_list->createTempTable())
            {
                $l_template->assign("objectTableList", $l_list->getTempTableHtml());
            } // if
        }
        else
        {
            $l_template->assign("objectTableList", '<div class="p10">' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>');
        } // if

        $l_template->assign('content_title', _L('LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_DATABASES'))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    } // function

    /**
     * Handles the Loginventory configuration
     *
     * @param $p_gets
     * @param $p_posts
     */
    private function handle_configuration($p_gets, $p_posts)
    {
        global $index_includes;

        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '9');

        $l_template   = $this->m_userrequest->get_template();
        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'LOGINVENTORY/' . C__MODULE__LOGINVENTORY . '9');
        switch ($p_posts[C__GET__NAVMODE])
        {
            case C__NAVMODE__SAVE:
                $this->m_dao->save_loginventory_config($p_posts);
                break;
            case C__NAVMODE__EDIT:
                $this->m_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                    ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL)
                    ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);
                break;
            default:
                $this->m_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        $l_config = $this->m_dao->get_configuration();

        if (count($l_config) == 0)
        {
            $this->m_dao->save_loginventory_config();
        }

        $l_databases = $this->m_dao->get_loginventory_databases();

        $l_dao = isys_cmdb_dao::instance($this->m_db);
        $l_res = $l_dao->get_objtype();

        $l_objTypes = [];
        while ($l_row = $l_res->get_row())
        {
            $l_objTypes[$l_row["isys_obj_type__id"]] = _L($l_row["isys_obj_type__title"]);
        } // while

        $l_dbs = [];
        foreach ($l_databases AS $l_data)
        {
            $l_dbs[$l_data['isys_loginventory_db__id']] = $l_data['isys_loginventory_db__host'] . ':' . $l_data['isys_loginventory_db__schema'];
        }

        $l_rules = [
            'C__LOGINVENTORY__OBJTYPE'     => [
                'p_arData'        => serialize($l_objTypes),
                'p_strSelectedID' => $l_config['isys_loginventory_config__isys_obj_type__id'],
                'p_strClass'      => 'input input-small'
            ],
            'C__LOGINVENTORY__DEFAULT_DB'  => [
                'p_arData'        => serialize($l_dbs),
                'p_strSelectedID' => $l_config['isys_loginventory_config__isys_loginventory_db__id'],
                'p_strClass'      => 'input input-small'
            ],
            'C__LOGINVENTORY__APPLICATION' => [
                'p_arData'        => serialize(get_smarty_arr_YES_NO()),
                'p_strSelectedID' => $l_config['isys_loginventory_config__applications'],
                'p_strClass'      => 'input input-mini'
            ],
            'C__LOGINVENTORY__LOGBOOK'     => [
                'p_arData'        => serialize(get_smarty_arr_YES_NO()),
                'p_strSelectedID' => $l_config['isys_loginventory_config__logbook_active'],
                'p_strClass'      => 'input input-mini'
            ]
        ];

        $l_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = 'modules/loginventory/configuration.tpl';
    } // function

    /**
     * Handles the Loginventory import
     *
     * @param $p_gets
     * @param $p_posts
     */
    private function handle_import($p_gets, $p_posts)
    {
        global $index_includes;
        // Inside the import module.
        $l_template = $this->m_userrequest->get_template();
        $l_tree     = $this->m_userrequest->get_menutree();

        $this->build_tree($l_tree, false);

        $l_template->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]))
            ->assign('content_title', 'LOGINventory');

        isys_auth_import::instance()
            ->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LOGINVENTORY);

        $l_pdo_drivers = PDO::getAvailableDrivers();

        if (!in_array('dblib', $l_pdo_drivers))
        {
            throw new isys_exception_general(
                'Please install the PDO driver "dblib".' . "\n\n<a href=\"http://php.net/manual/ref.pdo-dblib.php\">http://php.net/manual/ref.pdo-dblib.php</a>"
            );
        }

        $l_loginventory_dbs_raw = $this->m_dao->get_loginventory_databases();
        $l_loginventory_dbs     = [];
        $l_pdo                  = null;
        $l_default_selection    = null;
        foreach ($l_loginventory_dbs_raw AS $l_data)
        {
            $l_loginventory_dbs[$l_data['isys_loginventory_db__id']] = $l_data['isys_loginventory_db__host'] . ':' . $l_data['isys_loginventory_db__schema'];
            if (empty($l_pdo))
            {
                $l_default_selection = $l_data['isys_loginventory_db__id'];
            }
        }

        $l_config = $this->m_dao->get_configuration();

        $l_dao = isys_cmdb_dao::instance($this->m_db);
        $l_res = $l_dao->get_objtype();

        $l_objTypes = [];
        while ($l_row = $l_res->get_row())
        {
            $l_objTypes[$l_row["isys_obj_type__id"]] = _L($l_row["isys_obj_type__title"]);
        } // while

        $l_rules['selected_loginventory_db']['p_arData']        = serialize($l_loginventory_dbs);
        $l_rules['C__LOGINVENTORY__OBJTYPE']['p_arData']        = serialize($l_objTypes);
        $l_rules['C__LOGINVENTORY__OBJTYPE']['p_strSelectedID'] = $l_config['isys_loginventory_config__isys_obj_type__id'];

        $l_template->assign('js_script', 'show_loginventory_objects(' . $l_default_selection . ')');

        if (substr(php_uname(), 0, 7) == "Windows")
        {
            $l_template->assign('is_win', true);
        }
        else
        {
            $l_template->assign('is_win', false);
        } // if

        $l_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes['contentbottomcontent'] = 'modules/loginventory/import.tpl';
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $g_comp_database;

        $this->m_module_id = C__MODULE__LOGINVENTORY;

        $this->m_db            = $g_comp_database;
        $this->m_log           = isys_factory_log::get_instance('import_loginventory')
            ->set_destruct_flush(false);
        $this->m_navbar        = isys_component_template_navbar::getInstance();
        $this->m_dao           = new isys_loginventory_dao($this->m_db, $this->m_log);
        $this->m_import_module = new isys_module_import();
    } // function

} // class