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
 * CMDB UI: Global category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @version     0.9.9-2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_power_consumer extends isys_cmdb_ui_category_global
{
    /**
     * @global   array                                   $index_includes
     *
     * @param    isys_cmdb_dao_category_g_power_consumer $p_cat
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

        $l_rules["C__CATG__POWER_CONSUMER__DEST"]["p_strSelectedID"]   = $l_daoCon->get_assigned_connector_id($l_catdata["isys_catg_pc_list__isys_catg_connector_list__id"]);
        $l_rules["C__CATG__POWER_CONSUMER__CABLE"]["p_strValue"]       = $l_daoCon->get_assigned_cable($l_catdata["isys_catg_pc_list__isys_catg_connector_list__id"]);
        $l_rules["C__CATG__POWER_CONSUMER__ACTIVE"]["p_strSelectedID"] = ((empty($l_catdata["isys_catg_pc_list__id"])) ? '1' : (($l_catdata["isys_catg_pc_list__active"] > 0) ? '1' : '0'));
        $l_rules["C__CATG__POWER_CONSUMER__ACTIVE"]["p_arData"]        = serialize(get_smarty_arr_YES_NO());

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class
?>