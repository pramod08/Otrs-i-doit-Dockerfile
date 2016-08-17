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
 * CMDB UI: Global category (category type is accounting)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_cmdb_ui_category_g_accounting extends isys_cmdb_ui_category_global
{
    /**
     * Process method for displaying the template.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_g_accounting &$p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Initializing some variables.
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Creating some further, more specific, rules.
        $l_person_ids = $p_cat->callback_property_contact(
            isys_request::factory()
                ->set_object_id($l_catdata['isys_obj__id'])
        );

        $l_rules["C__CATG__PURCHASE_CONTACT"]["p_strSelectedID"] = (count($l_person_ids) > 0) ? isys_format_json::encode($l_person_ids) : null;

        $l_row = $p_cat->get_dialog("isys_guarantee_period_unit", $l_catdata["isys_catg_accounting_list__isys_guarantee_period_unit__id"])
            ->get_row();
        $l_now = time();

        if ($l_now > strtotime($l_catdata["isys_catg_accounting_list__acquirementdate"]))
        {
            $l_rules["C__CATG__ACCOUNTING_GUARANTEE_STATUS"]["p_strValue"] = $p_cat->calculate_guarantee_status(
                strtotime($l_catdata["isys_catg_accounting_list__acquirementdate"]),
                $l_catdata["isys_catg_accounting_list__guarantee_period"],
                $l_row["isys_guarantee_period_unit__const"]
            );
        }
        else
        {
            $l_rules["C__CATG__ACCOUNTING_GUARANTEE_STATUS"]["p_strValue"] = $p_cat->calculate_guarantee_status(
                $l_now,
                $l_catdata["isys_catg_accounting_list__guarantee_period"],
                $l_row["isys_guarantee_period_unit__const"]
            );
        } // if

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class