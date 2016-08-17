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
 * @author      Dennis Stücken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_database_access extends isys_cmdb_ui_category_specific
{
    /**
     * Process the list-view.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   null                   $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Stücken <dsteucken@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                // Enable the multiselection. Default: false.
                isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                // Should isys_form gets submitted after accepting? Default: false.
                isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                // This is the html element where the selected objects are transfered into (as JSON)
                isys_popup_browser_object_ng::C__DATARETRIEVAL    => [
                    [
                        'isys_cmdb_dao_category_s_database_access',
                        'get_data_by_object'
                    ],
                    $_GET[C__CMDB__GET__OBJECT],
                    [
                        "isys_connection__id",
                        "assignment_title",
                        "assignment_type",
                        "assignment_sysid"
                    ]
                    // this is where the browser tries to get a preselection from
                ],
                isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                isys_popup_browser_object_ng::C__GROUP_FILTER     => "C__OBJTYPE_GROUP__SOFTWARE",
                isys_popup_browser_object_ng::C__SECOND_LIST      => [
                    'isys_cmdb_dao_category_s_database_access::object_browser',
                    ['typefilter' => C__RELATION_TYPE__SOFTWARE]
                ],
            ],
            "LC__CATG__OBJECT__ADD",
            "LC__CATG__OBJECT__ADD_TT"
        );

        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(true, C__NAVBAR_BUTTON__PRINT)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, true, true, "isys_cats_database_access");
    } // function
} // class