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
 * CMDB Person: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_person_group_members extends isys_cmdb_ui_category_specific
{
    /**
     * Method for processing the list-view.
     *
     * @param   isys_cmdb_dao_category_s_person_group_members $p_cat
     *
     * @return  boolean
     */
    public function process_list(isys_cmdb_dao_category_s_person_group_members $p_cat)
    {
        $l_params = [
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__PERSON',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                [
                    get_class($p_cat),
                    "get_selected_persons"
                ],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ],
        ];

        $l_instance = new isys_popup_browser_object_ng();

        // Special rule for this category, because only administrators shall be able to assign groups.
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons()
            ->hide_all_buttons()
            ->set_js_onclick($l_instance->get_js_handler($l_params), C__NAVBAR_BUTTON__NEW)
            ->set_title(_L("LC__CMDB__CATS__PERSONGROUPS__ATTACH_PERSONS"), C__NAVBAR_BUTTON__NEW)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW);

        $this->list_view("isys_person_2_group__id", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_cats_person_group_members($p_cat), null, null, null, false);

        return true;
    } // function

    /**
     * Constructor
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__person_login.tpl");
    } // function
} // class