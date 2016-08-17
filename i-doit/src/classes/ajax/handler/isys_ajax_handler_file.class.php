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
 * AJAX for file actions.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_file extends isys_ajax_handler
{
    /**
     * Initialization.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // For proper internal error handling, we need to disable the PHP errors.
        error_reporting(0);

        $l_return = [];

        switch ($_GET['func'])
        {
            case 'create_new_file_version':
                $l_return = $this->create_new_file_version();
                break;

            case 'get_file_tree_data':
                $l_return = $this->get_file_tree_data();
                break;

            case 'qq_fileupload':
                $l_return = $this->qqfile_uploader();
                break;

            case 'upload':
                $l_return = $this->upload();
                break;

            case 'upload_by_ckeditor':
                $l_return = $this->upload_by_ckeditor($_GET['upload_handler']);
                break;

            case 'browse_by_ckeditor':
                $l_return = $this->browse_by_ckeditor($_GET['upload_handler']);
                break;

            case 'delete_by_ckeditor':
                $l_return = $this->delete_by_ckeditor($_POST['file'], $_GET['upload_handler']);
                break;
        } // switch

        // The IE has problems to handle any other content type but "text/html".
        if ($_GET['is_ie'] == 'true')
        {
            header('Content-Type: text/plain; charset=UTF-8');
        }
        else
        {
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * Method for creating a new file version for an object.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function create_new_file_version()
    {
        return $this->qqfile_uploader();
    } // function

    /**
     * This method will retrieve the data, to display the file-tree inside the file-browser.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function get_file_tree_data()
    {
        $l_file_browser = new isys_popup_browser_file();

        return [
            'tree'       => $l_file_browser->build_tree(
                isys_component_tree::factory('file_browser_filetree')
            )
                ->process(),
            'file_infos' => $l_file_browser->get_file_infos()
        ];
    } // function

    /**
     * This method uses the qqFileUploader plugin for ajax uploading.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function qqfile_uploader()
    {
        global $g_comp_session, $g_dirs;

        $l_file_prefix = $_GET['obj_id'] . '__' . time() . '__';
        $l_upload_dir  = realpath($g_dirs["fileman"]["target_dir"]) . DS;
        $l_uploader    = new isys_library_fileupload;

        $l_filename     = $l_uploader->getName();
        $l_new_filename = $l_file_prefix . $l_filename;

        $l_result = $l_uploader->set_prefix($l_file_prefix)
            ->handleUpload($l_upload_dir);

        if ($l_result['success'] === true)
        {
            /**
             * @var  $l_file_dao  isys_cmdb_dao_category_s_file
             */
            $l_file_dao = isys_cmdb_dao_category_s_file::instance($this->m_database_component);

            // Retrieve the md5 checksum.
            $p_md5_hash = md5_file($l_upload_dir . $l_new_filename);

            $l_version_id  = null;
            $l_physical_id = $l_file_dao->create_physical_file(
                $l_new_filename,
                $l_filename,
                $p_md5_hash,
                $g_comp_session->get_user_id()
            );

            if ($l_physical_id > 0)
            {
                $l_version_id = $l_file_dao->create_version(
                    $_GET['obj_id'],
                    $l_physical_id,
                    '', // @todo Version title wird entfernt
                    ''
                );
            } // if

            $l_cats_id = $l_file_dao->create_connector("isys_cats_file_list", $_GET['obj_id']);

            $l_retVal = $l_file_dao->update_cats_file_list(
                $l_cats_id,
                $l_version_id,
                $_GET['category'],
                ''
            );

            return ['success' => $l_retVal];
        }
        else
        {
            // If the upload was no success, we just return the array with the error-message. qqFileUploader will alert it.
            return $l_result;
        } // if
    } // function

    /**
     * Simple upload method, which returns the file-path or an error message.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function upload()
    {
        global $g_dirs;

        // We store the name of our upload-field in this GET parameter.
        $l_field = $_GET['uploadfield'];

        try
        {
            return [
                'success' => true,
                'path'    => isys_helper_upload::save($_FILES[$l_field], isys_helper_upload::append_hash_prefix($_FILES[$l_field]['name']), realpath($g_dirs["fileman"]["target_dir"]))
            ];
        }
        catch (isys_exception_filesystem $e)
        {
            return [
                'success'           => false,
                'message'           => $e->getMessage(),
                'secondary_message' => isys_helper_upload::get_error($l_field)
            ];
        } // try
    } // function

    /**
     * This method will be used by the CKEditor, when uploading files.
     *
     * @param   string $p_upload_handler Module classname. Needs the static "get_upload_dir" method.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function upload_by_ckeditor($p_upload_handler = 'isys_module_cmdb')
    {
        global $g_dirs;

        $message       = '';
        $l_destination = realpath($g_dirs["fileman"]["target_dir"]);
        $l_filename    = isys_helper_upload::append_hash_prefix($_FILES['upload']['name']);

        if (class_exists($p_upload_handler) && method_exists($p_upload_handler, 'get_upload_dir'))
        {
            $l_destination = $p_upload_handler::get_upload_dir();
        } // if

        try
        {
            isys_helper_upload::save($_FILES['upload'], $l_filename, $l_destination);
        }
        catch (isys_exception_filesystem $e)
        {
            $message = $e->getMessage() . ' - ' . isys_helper_upload::get_error('upload');
        } // try

        $l_www_dir = str_replace('\\', '/', $g_dirs['www_dir'] . str_replace(BASE_DIR, '', $l_destination) . DS . $l_filename);

        echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $_GET['CKEditorFuncNum'] . ', "' . $l_www_dir . '", "' . isys_glob_htmlentities(
                $message
            ) . '")</script>';

        // We let the script die, before the content-type is set.
        die;
    } // function

    /**
     * This method will be used by the CKEditor, when uploading files.
     *
     * @param   string $p_upload_handler Module classname. Needs the static "get_upload_dir" method.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function browse_by_ckeditor($p_upload_handler = 'isys_module_cmdb')
    {
        global $g_dirs, $g_comp_template;

        $l_files   = [];
        $l_message = '';

        if (class_exists($p_upload_handler) && method_exists($p_upload_handler, 'get_upload_dir_files'))
        {
            $l_files = $p_upload_handler::get_upload_dir_files();
        }
        else
        {
            $l_message = _L(
                'LC__UNIVERSAL__FILE__NO_UPLOAD_HANDLER',
                [
                    $p_upload_handler,
                    'get_upload_dir_files'
                ]
            );
        } // if

        if (count($l_files))
        {
            // This is used change the absolute path to a "www" path.
            $l_files = array_map(
                function ($p_file_path) use ($g_dirs)
                {
                    return str_replace('\\', '/', $g_dirs['www_dir'] . str_replace(BASE_DIR, '', $p_file_path));
                },
                $l_files
            );
        }
        else
        {
            $l_message = _L('LC__UNIVERSAL__FILE__NOT_FOUND');
        } // if

        $g_comp_template->assign('title', _L('LC_UNIVERSAL__FILE_BROWSER'))
            ->assign('files', $l_files)
            ->assign('file_body', 'popup/ckeditor_filebrowser.tpl')
            ->assign('ckeditor_func_num', $_GET['CKEditorFuncNum'])
            ->assign('message', $l_message)
            ->assign(
                'delete_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX      => 1,
                        C__GET__AJAX_CALL => 'file',
                        'func'            => 'delete_by_ckeditor',
                        'upload_handler'  => $p_upload_handler
                    ]
                )
            )
            ->display('popup/main.tpl');

        die;
    } // function

    /**
     * This method will delete a selected image from the server.
     *
     * @param   string $p_file
     * @param   string $p_upload_handler
     *
     * @return  array
     */
    private function delete_by_ckeditor($p_file, $p_upload_handler = 'isys_module_cmdb')
    {
        $l_files = [];

        $p_file = str_replace(
            [
                '\\',
                '/'
            ],
            DS,
            BASE_DIR . $p_file
        );

        if (class_exists($p_upload_handler) && method_exists($p_upload_handler, 'get_upload_dir_files'))
        {
            $l_files = $p_upload_handler::get_upload_dir_files();

            // This is used change the absolute path to a "www" path.
            $l_files = array_map(
                function ($p_file_path)
                {
                    return str_replace(
                        [
                            '\\',
                            '/'
                        ],
                        DS,
                        $p_file_path
                    );
                },
                $l_files
            );
        } // if

        if (count($l_files) === 0)
        {
            return [
                'success' => false,
                'message' => _L('LC__UNIVERSAL__FILE__NOT_FOUND'),
                'data'    => null
            ];
        } // if

        if (!in_array($p_file, $l_files))
        {
            return [
                'success' => false,
                'message' => _L('LC_FILEBROWSER__NO_FILE_FOUND'),
                'data'    => null
            ];
        } // if

        try
        {
            $l_filemanager = new isys_component_filemanager();

            return [
                'success' => $l_filemanager->delete($p_file, ''),
                'message' => null,
                'data'    => null
            ];
        }
        catch (Exception $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        } // try
    } // function
} // class