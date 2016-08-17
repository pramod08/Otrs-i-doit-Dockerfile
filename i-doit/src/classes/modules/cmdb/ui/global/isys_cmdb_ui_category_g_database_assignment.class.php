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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_database_assignment extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for specific category monitor.
     *
     * @param   isys_cmdb_dao_category_g_database_assignment $p_cat
     *
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_rules = [
            'C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT'                                             => [
                'p_strValue' => $l_catdata['assigned_obj_id']
            ],
            'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__DATABASE_ASSIGNMENT => [
                'p_strValue' => $l_catdata['isys_cats_database_access_list__description']
            ]
        ];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data())
            ->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    } // function
} // class