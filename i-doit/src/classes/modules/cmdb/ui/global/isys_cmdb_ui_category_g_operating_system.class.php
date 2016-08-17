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
 * CMDB UI: Operating system category (category type is global):
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @since       1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_operating_system extends isys_cmdb_ui_category_g_application
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_operating_system $p_cat
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        $l_catdata = $p_cat->get_general_data() ?: ['isys_catg_application_list__id' => 0];

        $l_request = isys_request::factory()
            ->set_category_data_id($l_catdata['isys_catg_application_list__id']);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION"]["p_strSelectedID"] = $l_catdata['isys_connection__isys_obj__id'] ?: null;
        //$l_rules["C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION"][isys_popup_browser_object_ng::C__CAT_FILTER] = 'C__CATS__APPLICATION;C__CATS__APPLICATION_ASSIGNED_OBJ;C__CATS__APPLICATION_VARIANT';
        $l_rules["C__CATG__OPERATING_SYSTEM_OBJ_APPLICATION"][isys_popup_browser_object_ng::C__TYPE_FILTER] = 'C__OBJTYPE__OPERATING_SYSTEM';
        $l_rules['C__CATG__OPERATING_SYSTEM_TYPE']['p_bDisabled']                                           = true;
        $l_rules['C__CATG__OPERATING_SYSTEM_TYPE']['p_strSelectedID']                                       = C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM;
        // This is used for the dialog+ popup to be able to create category entries.
        $l_rules["C__CATG__OPERATING_SYSTEM_VERSION"]["p_arData"]         = null;
        $l_rules["C__CATG__OPERATING_SYSTEM_VERSION"]["p_strTable"]       = 'isys_catg_version_list';
        $l_rules["C__CATG__OPERATING_SYSTEM_VERSION"]["condition"]        = 'isys_catg_version_list__isys_obj__id = ' . $p_cat->convert_sql_id(
                $l_catdata['isys_connection__isys_obj__id']
            );
        $l_rules["C__CATG__OPERATING_SYSTEM_VERSION"]["p_strCatTableObj"] = $p_cat->convert_sql_id($l_catdata['isys_connection__isys_obj__id']);

        $l_rules['C__CATG__OPERATING_SYSTEM_VARIANT__VARIANT']['p_arData'] = $p_cat->callback_property_assigned_variant($l_request);

        $l_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'software',
            'func'            => 'get_variants'
        ];

        $l_smarty_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'smartyplugin',
            'mode'            => 'edit'
        ];

        isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        $this->get_template_component()
            ->assign("application_ajax_url", isys_helper_link::create_url($l_ajax_param))
            ->assign("smarty_ajax_url", isys_helper_link::create_url($l_smarty_ajax_param))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class