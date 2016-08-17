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
 * UI: class for global category "images".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.6
 */
class isys_cmdb_ui_category_g_images extends isys_cmdb_ui_category_global
{
    /**
     * Processes view/edit mode.
     *
     * @param   isys_cmdb_dao_category_g_images $p_cat
     *
     * @return  array
     * @global  array                           $index_includes
     * @throws  isys_exception_dao_cmdb
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_dirs;

        $l_object_id = $_GET[C__CMDB__GET__OBJECT];

        if (isset($_GET[C__GET__AJAX]) && $_GET[C__GET__AJAX] && isset($_GET['action']))
        {
            header('Content-Type: application/json; charset=UTF-8');

            $l_return = [
                'success' => true,
                'data'    => null,
                'message' => null
            ];

            try
            {
                switch ($_GET['action'])
                {
                    default:
                    case 'save':
                        $l_return['data'] = $p_cat->handle_upload($l_object_id);
                        break;

                    case 'delete':
                        $l_return['data'] = $p_cat->delete_image($_POST['image_id']);
                } // switch
            }
            catch (Exception $e)
            {
                $l_return['success'] = false;
                $l_return['message'] = $e->getMessage();
            } // try

            echo isys_format_json::encode($l_return);

            die;
        } // if

        if (isset($_GET[C__GET__FILE__ID]) && $_GET[C__GET__FILE__ID])
        {
            // isys_core::expire(isys_convert::DAY);

            echo $p_cat->load_image((int) $_GET[C__GET__FILE__ID]);

            die;
        } // if

        $l_photos = [];

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        $l_res = $p_cat->get_images_by_object($l_object_id);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_photos[] = $l_row['isys_catg_images_list__id'];
            } // while
        } // if

        $l_image_url = $l_ajax_url = [
            C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT],
            C__CMDB__GET__CATG   => $_GET[C__CMDB__GET__CATG],
            C__GET__AJAX         => 1
        ];

        unset($l_image_url[C__GET__AJAX]);

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('images', isys_format_json::encode($l_photos))
            ->assign(
                'is_allowed_to_edit',
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $l_object_id, 'C__CATG__IMAGES')
            )
            ->assign(
                'is_allowed_to_delete',
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::DELETE, $l_object_id, 'C__CATG__IMAGES')
            )
            ->assign('upload_script_path', $g_dirs['js_abs'] . 'ajax_upload/fileuploader.js')
            ->assign('image_url', isys_helper_link::create_url($l_image_url))
            ->assign('ajax_url', isys_helper_link::create_url($l_ajax_url));

        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    /**
     * Fake a single value category.
     *
     * @param   isys_cmdb_dao_category_g_images $p_cat
     *
     * @return  array
     */
    public function process_list(isys_cmdb_dao_category_g_images &$p_cat)
    {
        return $this->process($p_cat);
    } // function
} // class