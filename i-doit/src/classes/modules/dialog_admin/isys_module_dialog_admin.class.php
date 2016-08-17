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
 * Administration for Dialog and Dialog+ boxes.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     2008-01-29 - Dennis Stücken
 * @version     2010-10-26 - Dennis Stücken < relation addons
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_dialog_admin extends isys_module implements isys_module_interface, isys_module_authable
{

    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * Tree component.
     *
     * @var  isys_component_tree
     */
    protected $m_tree = null;
    /**
     * @var  integer
     */
    protected $m_tree_count = 0;
    /**
     * @var  integer
     */
    protected $m_tree_root = 0;
    /**
     * Use this array, if a dialog+ table shall be able to receive descriptions!
     *
     * @var  array
     */
    private $m_description_whitelist = [
        'isys_sla_service_level'
    ];
    /**
     * @var  array
     */
    private $m_skip = [];
    /**
     * @var  array
     */
    private $m_tables = [];
    /**
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_dialog_admin::instance();
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_dialog_admin
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;

        return $this;
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
        $l_parent = -1;

        $this->m_tree = $p_tree;
        $this->m_tree->set_tree_sort(true);

        $l_auth = isys_auth_dialog_admin::instance();

        if ($p_system_module)
        {
            $l_parent = $p_parent;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $this->m_tree_root = $p_parent;
        }
        else
        {
            $this->m_tree_root = $this->m_tree->add_node(
                C__MODULE__DIALOG_ADMIN . $this->m_tree_count,
                $l_parent,
                _L('LC__DIALOG_ADMIN')
            );
        } // if

        // Get dialog+ and dialogs of the custom categories.
        $l_tables         = isys_factory::get_instance('isys_dialog_admin_dao', isys_application::instance()->database)
            ->get_dialog_tables();
        $l_custom_dialogs = isys_factory::get_instance('isys_dialog_admin_dao', isys_application::instance()->database)
            ->get_custom_dialogs();

        foreach ($l_tables as $l_table)
        {
            $this->m_tree_count++;

            $l_strRowLink = '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&table=' . $l_table . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__DIALOG_ADMIN . '&' . C__GET__TREE_NODE . '=' . C__MODULE__DIALOG_ADMIN . $this->m_tree_count;

            $this->m_tree->add_node(
                C__MODULE__DIALOG_ADMIN . $this->m_tree_count,
                $this->m_tree_root,
                _L($l_table),
                $l_strRowLink,
                null,
                null,
                ($l_table == $_GET["table"]) ? 1 : 0,
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'TABLE/' . strtoupper($l_table))
            );
        } // foreach

        if (count($l_custom_dialogs))
        {
            // Create rootfolder in tree first.
            $this->m_tree_count++;

            $l_custom_menu = $this->m_tree->add_node(
                C__MODULE__DIALOG_ADMIN . $this->m_tree_count,
                $this->m_tree_root,
                _L("LC__UNIVERSAL__CUSTOM_DIALOG_PLUS"),
                "",
                "",
                "",
                1
            );

            foreach ($l_custom_dialogs as $l_custom_dialog)
            {
                $l_selected = ("isys_dialog_plus_custom" == $_GET['table'] && $_GET['identifier'] == $l_custom_dialog) ? 1 : 0;

                $this->m_tree_count++;

                $l_link = '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__DIALOG_ADMIN . '&' . C__GET__TREE_NODE . '=' . C__MODULE__DIALOG_ADMIN . $this->m_tree_count . '&table=isys_dialog_plus_custom&identifier=' . $l_custom_dialog['identifier'];

                $this->m_tree->add_node(
                    C__MODULE__DIALOG_ADMIN . $this->m_tree_count,
                    $l_custom_menu,
                    $l_custom_dialog['title'],
                    $l_link,
                    null,
                    null,
                    $l_selected,
                    '',
                    '',
                    $l_auth->is_allowed_to(isys_auth::VIEW, 'CUSTOM/' . strtoupper($l_custom_dialog['identifier']))
                );
            } // foreach
        } // if
    } // function

    /**
     * Starts module process.
     *
     * @throws  isys_exception_general
     */
    public function start()
    {
        global $index_includes;

        $l_navbar = isys_component_template_navbar::getInstance();

        // Unpack request package.
        $l_template = isys_application::instance()->template;
        $l_tree     = $this->m_userrequest->get_menutree();
        $l_gets     = $this->m_userrequest->get_gets();
        $l_posts    = $this->m_userrequest->get_posts();
        $l_addons   = [];

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $this->build_tree($l_tree, false);
            $l_template->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        $l_dao = new isys_cmdb_dao_dialog_admin(isys_application::instance()->database);

        // Custom-Normal Dialog+ Handling for the check method.
        $l_auth_identifier = null;
        $l_auth_path       = null;

        if (($l_gets['table'] != 'isys_dialog_plus_custom'))
        {
            $l_auth_identifier = strtoupper($l_gets['table']);
            $l_auth_path       = 'TABLE';
        }
        else
        {
            $l_auth_identifier = strtoupper($l_gets["identifier"]);
            $l_auth_path       = 'CUSTOM';
        } // if

        $l_edit_right   = isys_auth_dialog_admin::instance()
            ->is_allowed_to(isys_auth::EDIT, $l_auth_path . '/' . $l_auth_identifier);
        $l_delete_right = isys_auth_dialog_admin::instance()
            ->is_allowed_to(isys_auth::DELETE, $l_auth_path . '/' . $l_auth_identifier);

        if (isset($l_gets['id']))
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL)
                ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
                ->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
                ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                ->set_visible(true, C__NAVBAR_BUTTON__PURGE);
        } // if

        switch ($l_posts[C__GET__NAVMODE])
        {
            case C__NAVMODE__PURGE:
                if (is_array($l_posts['id']) && count($l_posts['id']) > 0)
                {
                    try
                    {
                        foreach ($l_posts['id'] as $l_id)
                        {
                            $l_dao->delete($l_gets['table'], $l_id);
                        } // foreach
                    }
                    catch (Exception $e)
                    {
                        isys_component_template_infobox::instance()
                            ->set_message($e->getMessage(), null, null, null, 4);
                    } // try

                }
                elseif ($l_posts["dialog_id"] > 0)
                {
                    $l_dao->delete($l_gets['table'], $l_posts["dialog_id"]);
                }

                // Clear constant cache because of the constant
                isys_component_constant_manager::instance()
                    ->clear_dcm_cache();
                $l_gets["id"] = 0;
                break;
            case C__NAVMODE__SAVE:
                try
                {
                    if (isset($l_posts['dialog_id']) && $l_posts['dialog_id'] > 0)
                    {
                        if (!empty($l_posts['C__DIALOG__PARENTS']))
                        {
                            $l_dao->save(
                                $l_posts['dialog_id'],
                                $l_gets['table'],
                                $l_posts['title'],
                                $l_posts['sort'],
                                $l_posts['const'],
                                $l_posts['status'],
                                $l_posts['C__DIALOG__PARENTS'],
                                $l_posts['description']
                            );
                        }
                        else
                        {
                            $l_dao->save(
                                $l_posts['dialog_id'],
                                $l_gets['table'],
                                $l_posts['title'],
                                $l_posts['sort'],
                                $l_posts['const'],
                                $l_posts['status'],
                                null,
                                $l_posts['description']
                            );
                        } // if

                        $l_id = $l_posts['dialog_id'];
                        unset($l_gets['id']);
                    }
                    else
                    {
                        if (!empty($l_posts['C__DIALOG__PARENTS']))
                        {
                            $l_id = $l_dao->create(
                                $l_gets['table'],
                                $l_posts['title'],
                                $l_posts['sort'],
                                $l_posts['const'],
                                $l_posts['status'],
                                $l_posts['C__DIALOG__PARENTS'],
                                null,
                                $l_posts['description']
                            );
                        }
                        else
                        {
                            $l_id = $l_dao->create(
                                $l_gets['table'],
                                $l_posts['title'],
                                $l_posts['sort'],
                                $l_posts['const'],
                                $l_posts['status'],
                                null,
                                $l_gets['identifier'],
                                $l_posts['description']
                            );
                        }
                    } // if

                    // Clear constant cache because of the constant
                    isys_component_constant_manager::instance()
                        ->clear_dcm_cache();
                    isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));
                }
                catch (isys_exception_general $e)
                {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                } // try
                break;
            case C__NAVMODE__EDIT:
            case C__NAVMODE__NEW:
                $l_navmode = $l_posts[C__GET__NAVMODE];
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                    ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL)
                    ->set_visible(false, C__NAVBAR_BUTTON__NEW)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                break;
        } // switch

        if ($l_gets['table'])
        {
            // Addons.
            switch ($l_gets['table'])
            {
                case 'isys_relation_type':
                    $l_addons['relation'] = true;

                    if ($l_posts[C__GET__NAVMODE])
                    {
                        if (empty($l_posts['C__UNIVERSAL__BUTTON_CANCEL']))
                        {
                            if ($l_id > 0)
                            {
                                $l_dao->mod_relation_type($l_id, $l_posts['relation_master'], $l_posts['relation_slave']);
                            } // if
                        } // if
                    } // if
                    break;
            } // switch

            if (!empty($l_gets['table']))
            {
                $l_parent_table = $l_dao->get_parent_table($l_gets['table']);
            }
            else
            {
                $l_parent_table = '';
            } // if

            $l_data = [
                'status' => C__RECORD_STATUS__NORMAL,
                'sort'   => 99
            ];

            if ($l_gets['id'] > 0)
            {
                // Am i allowed to create a new dialog+ entry.
                if (isys_auth_dialog_admin::instance()
                    ->check(isys_auth::EDIT, $l_auth_path . '/' . $l_auth_identifier)
                )
                {
                    $l_daodata = $l_dao->get_data($l_gets['table'], $l_gets['id']);

                    if (count($l_daodata) > 0)
                    {
                        $l_row = $l_daodata->get_row();
                        foreach ($l_row as $l_key => $l_value)
                        {
                            if (strpos($l_key, $l_parent_table) === 0)
                            {
                                $l_data[str_replace($l_parent_table, 'parent', str_replace($l_gets['table'] . '__', '', $l_key))] = $l_value;
                            }
                            else
                            {
                                $l_data[str_replace($l_gets['table'] . '__', '', $l_key)] = $l_value;
                            } // if
                        } // foreach
                    } // if
                } // if
            }
            elseif ($l_posts[C__GET__NAVMODE] != C__NAVMODE__NEW)
            {
                // Am i allowed to view dialogs content.
                if (isys_auth_dialog_admin::instance()
                    ->check(isys_auth::VIEW, $l_auth_path . '/' . $l_auth_identifier)
                )
                {
                    if (($l_content = $this->get_content($l_gets['table'])))
                    {
                        $l_template->assign('g_list', $l_content);
                    }
                    else
                    {
                        $l_template->template->assign('g_message', "Table {$l_gets['table']} does not exist.");
                    } // if
                } // if
            } // if

            if ($l_parent_table)
            {
                $l_arr_res = $l_dao->get_dialog($l_parent_table);
                while ($l_row = $l_arr_res->get_row())
                {
                    $l_ar_data[$l_row[$l_parent_table . '__id']] = $l_row[$l_parent_table . '__title'];
                } // while

                $l_data['has_parent']                             = 1;
                $l_rules['C__DIALOG__PARENTS']['p_arData']        = serialize($l_ar_data);
                $l_rules['C__DIALOG__PARENTS']['p_strSelectedID'] = $l_data['parent__id'];
                $l_rules['C__DIALOG__PARENTS']['p_bEditMode']     = 1;

                $l_template->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
            } // if

            $l_template->assign(
                'recordStatus',
                [
                    C__RECORD_STATUS__NORMAL   => _L('LC__CMDB__RECORD_STATUS__NORMAL'),
                    C__RECORD_STATUS__ARCHIVED => _L('LC__CMDB__RECORD_STATUS__ARCHIVED'),
                    C__RECORD_STATUS__DELETED  => _L('LC__CMDB__RECORD_STATUS__DELETED')
                ]
            );

            $l_template->assign(
                'display_wysiwyg',
                (in_array($l_gets['table'], $this->m_description_whitelist) && in_array($l_gets['table'] . '__description', $l_dao->get_table_fields($l_gets['table'])))
            )
                ->assign('g_data', $l_data);
        } // if

        $l_template->activate_editmode()
            ->assign('content_title', _L('LC__CMDB__TREE__SYSTEM__TOOLS__DIALOG_ADMIN'))
            ->assign('addons', $l_addons);

        // This is necessary, because "$l_template->activate_editmode()" will set the navmode to EDIT.
        if (isset($l_navmode) && $l_navmode == C__NAVMODE__NEW)
        {
            global $g_navmode;

            $g_navmode              = C__NAVMODE__NEW;
            $_POST[C__GET__NAVMODE] = C__NAVMODE__NEW;
        } // if

        $index_includes['contentbottomcontent'] = 'content/bottom/content/module_dialog_admin.tpl';
    } // function

    /**
     * Fill the list from the specified 'Dialog' table.
     *
     * @param   string $p_table
     *
     * @return  string
     */
    public function get_content($p_table)
    {
        $p_table = isys_application::instance()->database->escape_string($p_table);

        $l_dao   = new isys_cmdb_dao_dialog_admin(isys_application::instance()->database);
        $l_sql   = 'SHOW TABLES LIKE \'' . $p_table . '\';';
        $l_query = isys_application::instance()->database->query($l_sql);

        if (isys_application::instance()->database->num_rows($l_query) > 0)
        {
            $l_listres = (!empty($_GET['identifier'])) ? $l_dao->get_custom_dialog_data($_GET['identifier']) : $l_dao->get_data($p_table);

            $l_strRowLink = '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&table=' . $p_table . '&identifier=' . $_GET['identifier'] . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__DIALOG_ADMIN . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . '&id=[{' . $p_table . '__id}]';

            // Array with table header titles.
            $l_arTableHeader = [
                $p_table . '__id'     => _L('LC__UNIVERSAL__ID'),
                $p_table . '__title'  => _L('LC__CMDB__CATP__TITLE'),
                $p_table . '__const'  => _L('LC__CMDB__OBJTYPE__CONST'),
                $p_table . '__status' => _L('LC__UNIVERSAL__STATUS'),
                'deleteable'          => _L('LC__REGEDIT__DELETEABLE')
            ];

            if ($l_parent_table = $l_dao->get_parent_table($p_table)) $l_arTableHeader[$l_parent_table . '__title'] = _L($l_parent_table);

            $l_objList = new isys_component_list(
                null, $l_listres
            );

            $l_objList->config(
                $l_arTableHeader,
                $l_strRowLink,
                '[{' . $p_table . '__id}]',
                true
            );

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        } // if
    } // function

    /**
     * Fills the left tree.
     *
     * @deprecated
     *
     * @param   string                  $p_filter
     * @param   isys_component_database $p_database
     *
     * @return  isys_module_dialog_admin
     */
    public function fill_tree($p_filter, $p_database)
    {
        $l_query = $p_database->query('SHOW TABLES LIKE "' . $p_filter . '";');

        while ($l_row = $p_database->fetch_array($l_query))
        {
            if (!in_array($l_row[0], $this->m_skip) && !isset($this->m_tables[$l_row[0]]))
            {
                $this->m_tree_count++;

                $l_strRowLink = '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&table=' . $l_row[0] . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__DIALOG_ADMIN . '&' . C__GET__TREE_NODE . '=' . C__MODULE__DIALOG_ADMIN . $this->m_tree_count;

                $this->m_tables[$l_row[0]] = true;

                $this->m_tree->add_node(
                    C__MODULE__DIALOG_ADMIN . $this->m_tree_count,
                    $this->m_tree_root,
                    _L($l_row[0]),
                    $l_strRowLink,
                    null,
                    null,
                    ($l_row[0] == $_GET["table"]) ? 1 : 0
                );
            } // if
        } // while

        return $this;
    } // function
} // class