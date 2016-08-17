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
 * UI: specific category for group type
 *
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 * @license       http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_group_type extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category monitor.
     *
     * @global  array                               $index_includes
     * @global  isys_component_template             $g_comp_template
     *
     * @param   isys_cmdb_dao_category_s_group_type $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_type = (isset($l_catdata['isys_cats_group_type_list__type'])) ? $l_catdata['isys_cats_group_type_list__type'] : 0;
        // Make rules.
        $l_rules["C__CATS__OBJECT_GROUP__TYPE"]["p_arData"]          = serialize($p_cat->callback_property_type());
        $l_rules["C__CATS__OBJECT_GROUP__TYPE"]["p_strSelectedID"]   = $l_type;
        $l_rules["C__CATS__OBJECT_GROUP__REPORT"]["p_arData"]        = serialize($p_cat->callback_property_report());
        $l_rules["C__CATS__OBJECT_GROUP__REPORT"]["p_strSelectedID"] = $l_catdata['isys_cats_group_type_list__isys_report__id'];

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_group_type_list__description"];

        if ($l_type == 1)
        {
            $this->get_template_component()
                ->assign('js_show_reportList', "$('reportList').show();");
        } // if

        if (!$p_cat->get_validation())
        {
            $l_rules["C__CATS__OBJECT_GROUP__TYPE"]["p_strSelectedID"]                                                    = $_POST["C__CATS__OBJECT_GROUP__TYPE"];
            $l_rules["C__CATS__OBJECT_GROUP__REPORT"]["p_strSelectedID"]                                                  = $_POST["C__CATS__OBJECT_GROUP__REPORT"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        } // if

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->get_template();
    } // function
} // class
?>