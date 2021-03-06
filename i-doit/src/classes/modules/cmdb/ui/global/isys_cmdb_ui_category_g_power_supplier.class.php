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
 * CMDB power_supplier
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_power_supplier extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                                   $index_includes
     *
     * @param   isys_cmdb_dao_category_g_power_supplier $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_daoCon = new isys_cmdb_dao_cable_connection($this->get_database_component());

        $l_rules = [];

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->get_template();

        $l_rules["C__CATG__POWER_SUPPLIER__DEST"]["p_strValue"]  = $l_daoCon->get_assigned_connector_id(
            $l_catdata["isys_catg_power_supplier_list__isys_catg_connector_list__id"]
        );
        $l_rules["C__CATG__POWER_SUPPLIER__CABLE"]["p_strValue"] = $l_daoCon->get_assigned_cable($l_catdata["isys_catg_power_supplier_list__isys_catg_connector_list__id"]);

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class
?>