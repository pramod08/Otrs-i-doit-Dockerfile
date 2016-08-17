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
 * CMDB UI: Global category (category type is global)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_cluster_adm_service extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    } // function

    /**
     * Show the list-template for subcategories of contact.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   null                   $p_get_param_override
     * @param   null                   $p_strVarName
     * @param   null                   $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   null                   $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [
                        get_class($p_cat),
                        "get_assigned_objects"
                    ],
                    $_GET[C__CMDB__GET__OBJECT],
                    [
                        "isys_obj__id",
                        "isys_obj__title",
                        "isys_obj__isys_obj_type__id",
                        "isys_obj__sysid"
                    ]
                ],
            ],
            "LC__CATG__OBJECT__ADD",
            "LC__CATG__CONTACT_LIST__NAVBAR_ADD"
        );

        $l_new_rights = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_active($l_new_rights, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_new_rights, C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    } // function
} // class