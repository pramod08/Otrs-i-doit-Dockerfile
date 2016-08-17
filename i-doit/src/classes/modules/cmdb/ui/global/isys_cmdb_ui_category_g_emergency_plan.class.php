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
 * CMDB UI: Global category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_emergency_plan extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                                   $index_includes
     *
     * @param   isys_cmdb_dao_category_g_emergency_plan &$p_cat
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Adding specific rules.
        $l_rules["C__CATG__EMERGENCY_PLAN_OBJ_EMERGENCY_PLAN"]["p_strSelectedID"] = $l_catdata["isys_connection__isys_obj__id"];

        // Apply rules
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class