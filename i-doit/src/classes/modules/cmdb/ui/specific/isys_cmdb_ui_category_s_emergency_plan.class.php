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
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_emergency_plan extends isys_cmdb_ui_category_specific
{

    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_emergency_plan $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_row = isys_factory_cmdb_dialog_dao::get_instance($this->get_database_component(), 'isys_unit_of_time')
            ->get_data($l_catdata["isys_cats_emergency_plan_list__isys_unit_of_time__id"]);

        $l_rules["C__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED"]["p_strValue"] = isys_convert::time(
            $l_catdata["isys_cats_emergency_plan_list__calc_time_need"],
            $l_row["isys_unit_of_time__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class