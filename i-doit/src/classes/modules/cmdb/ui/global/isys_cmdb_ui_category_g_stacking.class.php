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
 * CMDB Global category stacking.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_stacking extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_stacking $p_cat
     *
     * @return  boolean
     */
    public function process(isys_cmdb_dao_category_g_stacking $p_cat)
    {
        return $this->process_list($p_cat);
    } // function

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_g_stacking $p_cat
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category_g_stacking $p_cat)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                // multiselection: false is default
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                // should isys_form gets submitted after accepting? default is no.
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                // this is the html element where the selected objects are transfered into (as JSON)
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [
                        get_class($p_cat),
                        "get_connected_objects"
                    ],
                    $_GET[C__CMDB__GET__OBJECT]
                    // this is where the browser tries to get a preselection from
                ]
            ],
            "LC__CATG__OBJECT__ADD",
            "LC__CATG__OBJECT__ADD_TT"
        );

        $l_new_rights = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_active($l_new_rights, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_new_rights, C__NAVBAR_BUTTON__NEW);

        $this->list_view("isys_catg_stacking", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_catg_stacking($p_cat->get_database_component()));

        return true;
    } // function
} // class