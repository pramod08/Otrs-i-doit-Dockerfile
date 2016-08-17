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
 * CMDB Nagios
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@synetics.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_nagios_group extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the category nagios
     *
     * @param  isys_cmdb_dao_category_g_nagios_group $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Apply some "special" rules, which can not be handled by "fill_formfields()".
        $l_rules["C__CATG__NAGIOS_GROUP_IS_EXPORTABLE"]["p_arData"] = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__CATG__NAGIOS_GROUP_TYPE"]["p_arData"]          = serialize($p_cat->callback_property_type(isys_request::factory()));

        // Set some default values, if no data is saved.
        if ($l_catdata === null)
        {
            $l_rules["C__CATG__NAGIOS_GROUP_IS_EXPORTABLE"]["p_strSelectedID"] = 1;
        } // if

        $l_group_name_view = $l_catdata["isys_catg_nagios_group_list__name"];

        if ($l_catdata["isys_catg_nagios_group_list__name_selection"] == C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID)
        {
            $l_group_name_view = _L('LC__UNIVERSAL__OBJECT_TITLE') . ' ("' . $l_catdata['isys_obj__title'] . '")';
        } // if

        $this->get_template_component()
            ->assign('group_name_view', $l_group_name_view)
            ->assign('group_name_selection', $l_catdata["isys_catg_nagios_group_list__name_selection"])
            ->smarty_tom_add_rules("tom.content.bottom.content", isys_glob_array_merge($l_rules, $p_cat->get_additional_rules()));
    } // function
} // class