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
 * File browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_file extends isys_popup_browser
{
    /**
     * @desc Minimum right to create an object
     * Example: isys_auth::EDIT
     */
    const C__CHECK_RIGHT = "checkRight";
    /**
     * This variable will hold all file infos. Used for the ajax request.
     *
     * @var  array
     */
    protected $m_file_infos = [];
    /**
     * This variable will hold the parameters. Used for the ajax request.
     *
     * @var  array
     */
    protected $m_params = [];

    /**
     * Handles SMARTY request for location browser.
     *
     * @global  array                   $g_dirs
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template $p_tplclass, $p_params)
    {
        global $g_dirs;

        if (is_numeric($p_params['p_strSelectedID']))
        {
            $l_id = $p_params['p_strSelectedID'];
        }
        else
        {
            $l_id = $p_params['p_strValue'];
        } // if

        $l_view_name   = $p_params["name"] . '__VIEW';
        $l_hidden_name = $p_params["name"] . '__HIDDEN';

        $l_plugin         = new isys_smarty_plugin_f_text;
        $l_strHiddenField = '<input id="' . $l_hidden_name . '" name="' . $p_params["name"] . '__HIDDEN" type="hidden" value="' . $l_id . '" />';

        if (!isset($this->m_params[self::C__CHECK_RIGHT]))
        {
            $p_params[self::C__CHECK_RIGHT] = 'isys_auth::EDIT';
        }

        $l_hide_controls = (isset($p_params['p_bReadonly']) && $p_params['p_bReadonly']);

        // Set parameters for the f_text plug-in.
        $p_params['return_name']     = $p_params['name'];
        $p_params['name']            = $l_view_name;
        $p_params['p_bReadonly']     = true;
        $p_params['p_strSelectedID'] = $l_id;
        $p_params['p_strValue']      = $this->format_selection($l_id);

        if (isys_glob_is_edit_mode() || $p_params[isys_popup_browser_object_ng::C__EDIT_MODE])
        {
            $l_return = $l_plugin->navigation_edit($p_tplclass, $p_params);

            if (!$l_hide_controls)
            {
                $l_return .= '<a href="javascript:" title="' . _L("LC__UNIVERSAL__CHOOSE") . '" class="ml5" onClick="' . $this->process_overlay(
                        '',
                        950,
                        555,
                        $p_params,
                        'popup_commentary'
                    ) . ';" >
					<img src="' . $g_dirs['images'] . 'icons/silk/zoom.png" alt="' . _L("LC__UNIVERSAL__CHOOSE") . '" class="vam" />
					</a><a href="javascript:" title="' . _L("LC__UNIVERSAL__DETACH") . '" class="ml5" onClick="$(\'' . $l_view_name . '\').setValue(\'' . _L(
                        'LC__UNIVERSAL__CONNECTION_DETACHED'
                    ) . '\');$(\'' . $l_hidden_name . '\').setValue(0);" >
					<img src="' . $g_dirs['images'] . 'icons/silk/detach.png" alt="' . _L("LC__UNIVERSAL__DETACH") . '" class="vam" />
					</a>';
            } // if

            return $l_return . $l_strHiddenField;
        } // if

        return $l_plugin->navigation_view($p_tplclass, $p_params) . $l_strHiddenField;
    } // function

    /**
     * Displays the formatted filename-string.
     *
     * @param    integer $p_objid
     *
     * @return   string
     * @author   Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_objid, $p_unused = false)
    {
        global $g_comp_database;

        $l_quick_info = new isys_ajax_handler_quick_info();

        // We need a DAO for the object name.
        $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

        if ($p_objid > 0)
        {
            $l_obj = $l_dao_cmdb->get_object_by_id($p_objid)
                ->get_row();

            if (isys_glob_is_edit_mode())
            {
                return _L($l_obj['isys_obj_type__title']) . ' >> ' . $l_obj['isys_obj__title'];
            }
            else
            {
                return _L($l_obj['isys_obj_type__title']) . ' >> ' . $l_quick_info->get_quick_info($p_objid, $l_obj['isys_obj__title'], C__LINK__OBJECT);
            } // if
        } // if

        return _L('LC__CMDB__BROWSER_OBJECT__NONE_SELECTED');
    } // function

    /**
     * Method for loading the popup template and assigning stuff.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_selected_treenode = $this->m_params['p_strSelectedID'] + 10000;

        $this->m_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        if ($l_template = $p_modreq->get_template())
        {
            $l_tree = $this->build_tree(isys_component_tree::factory('file_browser_filetree'));

            $l_allowed_filesize = isys_convert::memory(isys_helper_upload::get_max_upload_size(), 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD);

            if (isset($this->m_params['callback_accept']))
            {
                $l_template->assign('callback_accept', $this->m_params['callback_accept']);
            }
            else
            {
                $l_template->assign('callback_accept', '');
            } // if

            $l_template->activate_editmode()
                ->assign('return_name', $this->m_params['return_name'])
                ->assign('browser', $l_tree->process($l_selected_treenode))
                ->assign('selected_file', $l_selected_treenode)
                ->assign('file_infos', isys_format_json::encode($this->m_file_infos))
                ->assign('new_file_description', _L('LC_FILEBROWSER__NEW_FILE_DESCRIPTION', [$l_allowed_filesize]))
                ->assign(
                    'upload_rights',
                    isys_auth_cmdb::instance()
                        ->is_allowed_to(isys_auth::EDIT, 'OBJ_IN_TYPE/C__OBJTYPE__FILE')
                )
                ->display('popup/filebrowser.tpl');
            die;
        } // if

        return null;
    } // function

    /**
     * Returns the file infos, collected by the "build_tree" method.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_file_infos()
    {
        return $this->m_file_infos;
    } // function

    /**
     * Outsourced method for building the file tree. Will be used from the "handle_module_request" method, but also by the ajax handler "isys_ajax_handler_file.
     *
     * @param   isys_component_tree $p_tree
     *
     * @return  isys_component_tree
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function build_tree($p_tree)
    {
        global $g_comp_database, $g_dirs;

        $l_cmdb_dao = isys_cmdb_dao::instance($g_comp_database);
        $l_file_dao = isys_cmdb_dao_file::instance($g_comp_database);

        $l_file_categories = isys_factory_cmdb_dialog_dao::get_instance($g_comp_database, 'isys_file_category')
            ->get_data();

        // Create root node.
        $l_root_node = $p_tree->add_node(0, -1, '<span class="ml5">' . _L('LC_UNIVERSAL__FILE_BROWSER') . '</span>', '', '', $g_dirs['images'] . 'icons/silk/database.png');

        if (is_array($l_file_categories) && count($l_file_categories) > 0)
        {
            // Here we create the "category" folders.
            foreach ($l_file_categories as $l_id => $l_row)
            {
                $p_tree->add_node($l_id, $l_root_node, '<span>' . $l_row['isys_file_category__title'] . '</span>', '', '', $g_dirs['images'] . 'dtree/folder.gif');
            } // foreach
        } // if

        $l_files       = $l_file_dao->get_active_file_versions();
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        while ($l_row = $l_files->get_row())
        {
            // At first we calculate the filesize.
            $l_filesize  = 0;
            $l_file_path = $g_dirs['fileman']['target_dir'] . DS . $l_row['isys_file_physical__filename'];

            if (file_exists($l_file_path))
            {
                $l_filesize = filesize($g_dirs['fileman']['target_dir'] . DS . $l_row['isys_file_physical__filename']);
            } // if

            if ($l_filesize <= 0)
            {
                $l_filesize = $l_empty_value;
            }
            else
            {
                $l_filesize = isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD);
            } // if

            // Secondly, we get the user who uploaded the file.
            $l_person = $l_cmdb_dao->get_obj_name_by_id_as_string($l_row['isys_file_physical__user_id_uploaded']);

            // Assign intern variables.
            $this->m_file_infos[$l_row['isys_file_version__id']] = [
                'id'           => $l_row['isys_file_version__id'],
                'obj_id'       => $l_row['isys_file_version__isys_obj__id'],
                'filename'     => isys_glob_htmlentities($l_row['isys_file_physical__filename_original']),
                'fileobj_name' => isys_glob_htmlentities($l_cmdb_dao->get_obj_name_by_id_as_string($l_row['isys_file_version__isys_obj__id'])),
                'filesize'     => $l_filesize . ' MB',
                'uploaded_by'  => $l_person,
                'created_at'   => $l_row['isys_date_up'],
                'fileversion'  => isys_glob_htmlentities($l_row['isys_file_version__title']),
                'filerevision' => $l_row['isys_file_version__revision'],
                'category'     => $l_row['isys_file_category__title']
            ];

            $l_filename      = $l_row['isys_file_physical__filename_original'];
            $l_strObjectName = $l_row['isys_obj__title'];

            $l_parent_node_id = $l_row['isys_file_category__id'];

            if ($l_parent_node_id === null)
            {
                $l_parent_node_id = $l_root_node;
            } // if

            // Change file name
            if (strlen($l_strObjectName) > 0)
            {
                $l_filename = $l_strObjectName . ' - "' . $l_filename . '"';
            }
            else
            {
                $l_filename = '"' . $l_filename . '"';
            } // if

            $l_selected_file = 0;

            if ($l_row['isys_file_version__isys_obj__id'] == $this->m_params['p_strSelectedID'])
            {
                $l_selected_file = 1;
            } // if

            // Add the next file to tree.
            $p_tree->add_node(
                10000 + $l_row['isys_file_version__id'],
                $l_parent_node_id,
                '<span class="file-object mouse-pointer ' . ($l_selected_file ? 'bold' : '') . '" data-file-version-id="' . $l_row['isys_file_version__id'] . '">' . $l_filename . '</span>',
                '',
                '',
                '',
                $l_selected_file
            );
        } // while

        return $p_tree;
    } // function
} // class