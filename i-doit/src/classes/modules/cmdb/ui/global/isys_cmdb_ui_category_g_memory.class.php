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
 * CMDB Memory
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_memory extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                           $index_includes
     *
     * @param   isys_cmdb_dao_category_g_memory $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_rules = [];

        $l_catdata = $p_cat->get_general_data();

        $l_catdata["isys_catg_memory_list__capacity"] = isys_convert::memory(
            $l_catdata["isys_catg_memory_list__capacity"],
            $l_catdata["isys_catg_memory_list__isys_memory_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Apply rules.
        $this->get_template_component()
            ->assign(
                "new_catg_memory",
                (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW || isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT && isys_glob_get_param(
                        C__CMDB__GET__CATG
                    ) == C__CATG__OVERVIEW)
            )
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->get_template();
    } // function
} // class