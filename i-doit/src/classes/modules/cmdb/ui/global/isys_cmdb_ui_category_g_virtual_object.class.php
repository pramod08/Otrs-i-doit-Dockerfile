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
 * CMDB UI: Global category location.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_virtual_object extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Show the list-template for subcategories of file.
     *
     * @param   isys_cmdb_dao_category & $p_cat
     *
     * @return  null
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_dao_location = new isys_cmdb_dao_category_g_location($p_cat->get_database_component());
        $l_catdata      = $l_dao_location->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        if ($l_catdata["isys_catg_location_list__id"] != 1 && empty($l_catdata["isys_catg_location_list__parentid"]))
        {
            $l_strJs = "get_popup('location_error', '', 320, 140);";

            isys_component_template_navbar::getInstance()
                ->set_js_onclick($l_strJs, C__NAVBAR_BUTTON__NEW);
        }
        else
        {
            $this->object_browser_as_new(
                [
                    isys_popup_browser_object_ng::C__MULTISELECTION => true,
                    isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATG__LOCATION",
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
                    ]
                ],
                "LC__CMDB__CATS__GROUP__ADD_OBJECTS",
                "LC__CATG__OBJECT__ADD_TT"
            );
        }

        isys_component_template_navbar::getInstance()
            ->set_active(
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const()),
                C__NAVBAR_BUTTON__NEW
            )
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__CANCEL);

        return parent::process_list(
            $p_cat,
            $p_get_param_override,
            $p_strVarName,
            $p_strTemplateName,
            $p_bCheckbox,
            $p_bOrderLink,
            "isys_catg_location_list",
            C__RECORD_STATUS__NORMAL
        );
    } // function

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__virtual_object.tpl");
    } // function
} // class
?>