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
 * CMDB Air condition: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_ac extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_ac $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Make rules.
        $l_rules["C__CATS__AC_REFRIGERATING_CAPACITY"]["p_strValue"] = isys_convert::watt(
            $l_catdata["isys_cats_ac_list__capacity"],
            $l_catdata["isys_cats_ac_list__isys_ac_refrigerating_capacity_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATS__AC_DIMENSIONS_WIDTH"]["p_strValue"]       = isys_convert::measure(
            $l_catdata["isys_cats_ac_list__width"],
            $l_catdata["isys_cats_ac_list__isys_depth_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATS__AC_DIMENSIONS_HEIGHT"]["p_strValue"]      = isys_convert::measure(
            $l_catdata["isys_cats_ac_list__height"],
            $l_catdata["isys_cats_ac_list__isys_depth_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATS__AC_DIMENSIONS_DEPTH"]["p_strValue"]       = isys_convert::measure(
            $l_catdata["isys_cats_ac_list__depth"],
            $l_catdata["isys_cats_ac_list__isys_depth_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class