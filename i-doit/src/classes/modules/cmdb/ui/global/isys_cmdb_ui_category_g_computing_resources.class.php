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
 * CMDB computing resources category.
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_computing_resources extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global   array                                        $index_includes
     *
     * @param    isys_cmdb_dao_category_g_computing_resources &$p_cat
     *
     * @version  Niclas Potthast <npotthast@i-doit.org>
     * @version  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__COMPUTING_RESOURCES__RAM"]["p_strValue"]               = isys_convert::memory(
            $l_catdata["isys_catg_computing_resources_list__ram"],
            intval($l_catdata["isys_catg_computing_resources_list__ram__isys_memory_unit__id"]),
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATG__COMPUTING_RESOURCES__CPU"]["p_strValue"]               = isys_convert::frequency(
            $l_catdata["isys_catg_computing_resources_list__cpu"],
            intval($l_catdata["isys_catg_computing_resources_list__cpu__isys_frequency_unit__id"]),
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATG__COMPUTING_RESOURCES__DISC_SPACE"]["p_strValue"]        = isys_convert::memory(
            $l_catdata["isys_catg_computing_resources_list__disc_space"],
            intval($l_catdata["isys_catg_computing_resources_list__ds__isys_memory_unit__id"]),
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules["C__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH"]["p_strValue"] = isys_convert::speed(
            $l_catdata["isys_catg_computing_resources_list__network_bandwidth"],
            intval($l_catdata["isys_catg_computing_resources_list__nb__isys_port_speed__id"]),
            C__CONVERT_DIRECTION__BACKWARD
        );

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class