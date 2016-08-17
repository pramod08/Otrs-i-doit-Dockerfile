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
 * CMDB nagios host assigned objects: global category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang<qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_nagios_refs_services extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category_g_assigned_logical_unit $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules                                                        = [];
        $l_rules['C__CATG__NAGIOS_ASSIGNED_SERVICES']['multiselection'] = true;
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Method for processing the list-view.
     *
     * @param   isys_cmdb_dao_category_g_nagios_refs_services $p_cat
     *
     * @return  boolean
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_params = [
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__NAGIOS_SERVICE_FOLDER;C__CATG__NAGIOS_SERVICE_DEF',
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                [
                    get_class($p_cat),
                    "get_selected_objects"
                ],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ]
        ];

        $l_instance = new isys_popup_browser_object_ng();

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_js_onclick($l_instance->get_js_handler($l_params), C__NAVBAR_BUTTON__NEW)
            ->set_title(_L("LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS__ASSIGN_BUTTON"), C__NAVBAR_BUTTON__NEW)
            ->set_active(
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const()),
                C__NAVBAR_BUTTON__NEW
            )
            ->set_visible(true, C__NAVBAR_BUTTON__NEW);

        $this->list_view("isys_obj__id", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_catg_nagios_refs_services($this->get_database_component()));

        return true;
    } // function

    /**
     * Constructor.
     *
     * @todo    Is this a reversed-category or can the constructor be removed?
     *
     * @param   isys_component_template $p_template
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__nagios_refs_services.tpl");
        parent::__construct($p_template);
    } // function
} // class