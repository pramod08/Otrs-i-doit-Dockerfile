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
 * CMDB UI: Application category (category type is global):
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Wösten <awoesten@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_application extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories of application.
     *
     * @param   isys_cmdb_dao_category_g_application $p_cat
     *
     * @return  array|void
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__APPLICATION_OBJ_APPLICATION"]["p_strSelectedID"] = $l_catdata['isys_connection__isys_obj__id'];
        $l_rules["C__CATG__APPLICATION_OBJ_APPLICATION"]["multiselection"]  = (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW);
        $l_rules["C__CATG__APPLICATION_TYPE"]["p_strSelectedID"]            = (($l_catdata['isys_obj__isys_obj_type__id'] ?: $_GET[C__CMDB__GET__OBJECTTYPE]) == C__OBJTYPE__OPERATING_SYSTEM) ? C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM : C__CATG__APPLICATION_TYPE__SOFTWARE;
        // This is used for the dialog+ popup to be able to create category entries.
        $l_rules["C__CATG__APPLICATION_VERSION"]["p_strTable"]       = 'isys_catg_version_list';
        $l_rules["C__CATG__APPLICATION_VERSION"]["condition"]        = 'isys_catg_version_list__isys_obj__id = ' . $p_cat->convert_sql_id(
                $l_catdata['isys_connection__isys_obj__id']
            );
        $l_rules["C__CATG__APPLICATION_VERSION"]["p_strCatTableObj"] = $p_cat->convert_sql_id($l_catdata['isys_connection__isys_obj__id']);

        $l_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'software',
        ];

        $l_smarty_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'smartyplugin',
            'mode'            => 'edit'
        ];

        $this->get_template_component()
            ->assign("hide_priority", $l_rules['C__CATG__APPLICATION_TYPE']['p_strSelectedID'] != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
            ->assign("application_ajax_url", isys_helper_link::create_url($l_ajax_param))
            ->assign("smarty_ajax_url", isys_helper_link::create_url($l_smarty_ajax_param))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class