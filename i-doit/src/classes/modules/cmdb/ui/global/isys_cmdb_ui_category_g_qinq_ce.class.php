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
 * UI: QinQ CE-VLAN
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_qinq_ce extends isys_cmdb_ui_category_global
{

    /**
     * @param   isys_cmdb_dao_category_global $p_cat
     *
     * @return  null
     */
    public function process($p_cat)
    {
        return $this->process_list($p_cat);
    } // function

    /**
     * Processes category data list for multi-valued categories.
     *
     * @param   isys_cmdb_dao_category $p_cat Category's DAO
     * @param   array                  $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     */
    public function process_list($p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        // Parameters for object browser
        $l_params = [
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATS__LAYER2_NET_ASSIGNED_PORTS",
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
        ];

        // Get preselection
        $l_selected_objects = [];
        $l_res              = $p_cat->get_selected_objects($_GET[C__CMDB__GET__OBJECT]);

        if ($l_res->count())
        {
            while ($l_row = $l_res->get_row())
            {
                $l_selected_objects[] = $l_row['isys_obj__id'];
            }
        } // if

        $l_params[isys_popup_browser_object_ng::C__SELECTION] = $l_selected_objects;

        // Build object broweser instance
        $l_instance = new isys_popup_browser_object_ng();

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_js_onclick($l_instance->get_js_handler($l_params), C__NAVBAR_BUTTON__NEW)
            ->set_title(_L("LC__CATG__QINQ_CE__ASSIGN_BUTTON"), C__NAVBAR_BUTTON__NEW)
            ->set_active(
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const()),
                C__NAVBAR_BUTTON__NEW
            )
            ->set_visible(true, C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    } // function

} //class
