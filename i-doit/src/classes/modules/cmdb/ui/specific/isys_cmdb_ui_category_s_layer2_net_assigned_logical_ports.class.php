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
 * DAO: UI class for layer2-net assigned logical ports.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_layer2_net_assigned_logical_ports extends isys_cmdb_ui_category_specific
{
    /**
     * Empty process-list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        ;
    }

    /**
     * Process list method.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     *
     * @param   isys_cmdb_dao_category                   & $p_cat
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                isys_popup_browser_object_ng::C__GROUP_FILTER     => "C__OBJTYPE_GROUP__INFRASTRUCTURE",
                isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                isys_popup_browser_object_ng::C__SECOND_LIST      => [
                    'isys_cmdb_dao_category_s_layer2_net_assigned_logical_ports::object_browser',
                    [C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT]]
                ],
            ],
            "LC__UNIVERSAL__ASSIGNMENT",
            "LC__CATG__OBJECT__ADD"
        );

        // We deactivate the edit, archive and purge functions.
        isys_component_template_navbar::getInstance()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__PURGE);

        // We create our list DAO.
        $l_dao_list = new isys_cmdb_dao_list_cats_layer2_net_assigned_logical_ports($p_cat->get_database_component());

        // We cast the object-id to INT so nobody can do bad bad things to our code.
        $l_obj_id = (int) $_GET[C__CMDB__GET__OBJECT];

        // We call the list_view method, which handles the rest.
        $this->list_view("isys_catg_log_port_list_2_isys_obj", $l_obj_id, $l_dao_list, null, null, null, true, true);

        return true;
    } // function
} // class