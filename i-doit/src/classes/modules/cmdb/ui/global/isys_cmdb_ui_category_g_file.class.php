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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_file extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global   array                         $index_includes
     *
     * @param    isys_cmdb_dao_category_g_file &$p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_comp_filemanager = new isys_component_filemanager();

        if ($l_catdata["isys_connection__isys_obj__id"] > 0)
        {
            $l_file_dao = new isys_cmdb_dao_category_s_file($this->get_database_component());

            $l_file_obj_id = $l_catdata["isys_connection__isys_obj__id"];

            $l_active_file = $l_file_dao->get_file_by_obj_id($l_file_obj_id)
                ->get_row();

            $l_rules["C__CATG__FILE_OBJ_FILE"]["p_strValue"] = $l_file_obj_id;

            if (is_array($l_active_file) && count($l_active_file))
            {
                $this->get_template_component()
                    ->assign("file_uploaded", true);

                // Store upload path in a hidden field -> and activate the download link.
                $l_rules["C__CATG__FILE_NAME"]["p_strValue"]    = $l_active_file["isys_file_physical__filename_original"];
                $l_rules["C__CATG__PATH__HIDDEN"]["p_strValue"] = addslashes($l_comp_filemanager->get_upload_path());
                $l_rules["C__CATG__FILE_DOWNLOAD"]["p_strLink"] = "?" . $_SERVER["QUERY_STRING"] . "&mod=cmdb&file_manager=get&f_id=" . $l_active_file["isys_file_physical__id"];
            }
            else
            {
                $l_rules["C__CATG__FILE_NAME"]["p_strValue"] = "No file version uploaded.";
            } // if
        }
        else
        {
            $l_rules["C__CATG__FILE_NAME"]["p_strValue"] = "No file selected.";
        } // if

        // Make rules.
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_file_list__description"];
        $l_rules["C__CATG__FILE_LINK"]["p_strValue"]                                                                  = $l_catdata["isys_catg_file_list__link"];

        // Apply rules.
        $this->get_template_component()
            ->assign('encType', 'multipart/form-data')
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class