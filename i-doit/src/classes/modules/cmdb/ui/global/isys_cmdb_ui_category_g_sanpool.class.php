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
 * CMDB Drive: Dynamic category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      André Wösten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_sanpool extends isys_cmdb_ui_category_global
{
    /**
     * Shows the detail-template for the dynamic category SAN-Pool.
     *
     * @global  isys_component_template          $g_comp_template
     *
     * @param   isys_cmdb_dao_category_g_sanpool $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_template;

        $l_catdata = $p_cat->get_general_data();

        // Prepare the object-browser values.
        $l_rules = $l_selected_devices = $l_selected_raid = $l_selected_paths = $l_selected_clients = [];

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Determine devices binded to SAN-Pool.
        $l_res_devices = $p_cat->get_connected_devices($l_catdata["isys_catg_sanpool_list__id"]);
        if (count($l_res_devices) > 0)
        {
            while ($l_row = $l_res_devices->get_row())
            {
                // The int-casting is important, so we can write "[1,2,3]" inside the hidden fields value.
                $l_selected_devices[] = (int) $l_row["isys_catg_sanpool_list_2_isys_catg_stor_list__stor__id"];
            } // while
        } // if

        // Determine raids binded to this pool.
        $l_res_raid = $p_cat->get_connected_raids($l_catdata["isys_catg_sanpool_list__id"]);
        if (count($l_res_raid) > 0)
        {
            while ($l_row = $l_res_raid->get_row())
            {
                // The int-casting is important, so we can write "[1,2,3]" inside the hidden fields value.
                $l_selected_raid[] = (int) $l_row["isys_catg_sanpool_list_2_isys_catg_raid_list__raid__id"];
            } // while
        } // if

        // Determine path binded to SAN-Pool.
        $l_resFCPorts = $p_cat->get_paths($l_catdata["isys_catg_sanpool_list__id"]);
        if (count($l_resFCPorts) > 0)
        {
            while ($l_rowFCPorts = $l_resFCPorts->get_row())
            {
                $l_selected_paths[] = $l_rowFCPorts["isys_catg_fc_port_list__id"];
            } // while
        } // if

        // Determine LDEV-Clients binded to SAN-Pool.
        $l_resClient = $p_cat->get_clients($l_catdata["isys_catg_sanpool_list__id"]);
        if (count($l_resClient) > 0)
        {
            while ($l_row = $l_resClient->get_row())
            {
                $l_selected_clients[] = $l_row["isys_catg_ldevclient_list__id"];
            } // while
        } // if

        // Make rules
        $l_rules["C__CATD__SANPOOL_CAPACITY"]["p_strValue"]       = isys_convert::memory(
            $l_catdata["isys_catg_sanpool_list__capacity"],
            $l_catdata["isys_catg_sanpool_list__isys_memory_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATD__SANPOOL_DEVICES"]["p_selectedDevices"] = isys_format_json::encode($l_selected_devices);
        $l_rules["C__CATD__SANPOOL_DEVICES"]["p_selectedRaids"]   = isys_format_json::encode($l_selected_raid);
        $l_rules["C__CATD__SANPOOL_PATHS"]["p_strValue"]          = (count($l_selected_paths)) ? implode(',', $l_selected_paths) : null;
        $l_rules["C__CATD__SANPOOL_PATHS"]["p_strPrim"]           = $l_catdata["isys_catg_sanpool_list__primary_path"];

        // There seems to be a bug - This variable gets filled with a value, but should not be used!
        $l_rules["C__CATD__SANPOOL_DEVICES"]["p_strSelectedID"] = '';

        // LDEV-Client Browser.
        $l_rules['C__CATD__SANPOOL_CLIENTS']['p_strValue'] = (count($l_selected_clients)) ? implode(',', $l_selected_clients) : null;
        $l_rules['C__CATD__SANPOOL_CLIENTS']['objID']      = $_GET['objID'];
        // This is necessary, because it gets set to "1" by "fill_formfields()".
        $l_rules['C__CATD__SANPOOL_CLIENTS']['p_strSelectedID'] = null;

        // Apply rules
        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   mixed                  $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  mixed
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        return parent::process_list($p_cat, [C__CMDB__GET__CATD => null], $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, "isys_catg_sanpool_list__id");
    } // function
} // class