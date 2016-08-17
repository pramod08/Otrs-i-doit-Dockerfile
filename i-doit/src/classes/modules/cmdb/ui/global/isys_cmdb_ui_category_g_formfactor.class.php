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
 * CMDB Formfactor category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_formfactor extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for global category formfactor.
     *
     * @param   isys_cmdb_dao_category_g_formfactor $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.de>
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__FORMFACTOR_INSTALLATION_WIDTH"]["p_strValue"] = isys_convert::measure(
            $l_catdata["isys_catg_formfactor_list__installation_width"],
            $l_catdata["isys_depth_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $l_rules["C__CATG__FORMFACTOR_INSTALLATION_HEIGHT"]["p_strValue"] = isys_convert::measure(
            $l_catdata["isys_catg_formfactor_list__installation_height"],
            $l_catdata["isys_depth_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $l_rules["C__CATG__FORMFACTOR_INSTALLATION_DEPTH"]["p_strValue"] = isys_convert::measure(
            $l_catdata["isys_catg_formfactor_list__installation_depth"],
            $l_catdata["isys_depth_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $l_rules["C__CATG__FORMFACTOR_INSTALLATION_WEIGHT"]["p_strValue"] = isys_convert::weight(
            $l_catdata["isys_catg_formfactor_list__installation_weight"],
            $l_catdata["isys_catg_formfactor_list__isys_weight_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class