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
 * CMDB UI: Global category planning
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_planning extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category_g_planning $p_cat
     *
     * @return  array
     * @throws  isys_exception_cmdb
     */
    public function process(isys_cmdb_dao_category_g_planning $p_cat)
    {
        $l_cmdb_status_colors = $l_rules = [];
        $l_catdata            = $p_cat->get_general_data();

        $l_cmdb_statuses = isys_factory_cmdb_dialog_dao::get_instance($this->get_database_component(), 'isys_cmdb_status')
            ->get_data();

        foreach ($l_cmdb_statuses as $l_cmdb_status)
        {
            $l_cmdb_status_colors[$l_cmdb_status['isys_cmdb_status__id']] = '#' . $l_cmdb_status['isys_cmdb_status__color'];
        } // foreach

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Process some "special" rules.
        $l_rules["C__CATG__PLANNING__START"]["p_strValue"] = date("Y-m-d", $l_catdata["isys_catg_planning_list__start"]);
        $l_rules["C__CATG__PLANNING__END"]["p_strValue"]   = date("Y-m-d", $l_catdata["isys_catg_planning_list__end"]);

        $this->get_template_component()
            ->assign('status_colors', isys_format_json::encode($l_cmdb_status_colors))
            ->assign('status_color', $l_catdata["isys_cmdb_status__color"])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        return $l_rules;
    } // function
} // class