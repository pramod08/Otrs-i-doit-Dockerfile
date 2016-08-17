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
 * UI: Specific cellphone category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_sim_card extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_sim_card $p_cat
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        $l_catdata["isys_catg_sim_card_list__twincard"] = ($l_catdata["isys_catg_sim_card_list__twincard"] ?: 0);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE"]["p_strValue"] = $l_catdata["isys_catg_assigned_cards_list__isys_obj__id"];

        $this->get_template_component()
            ->assign("g_twincard", $l_catdata["isys_catg_sim_card_list__twincard"])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class