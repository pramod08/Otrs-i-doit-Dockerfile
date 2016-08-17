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
 * @package    i-doit
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_share_access extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_g_share_access $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();

        $l_rules["C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS"]["p_strValue"]                                             = $l_catdata['isys_connection__isys_obj__id'];
        $l_rules["C__CATG__SHARE_ACCESS__MOUNTPOINT"]["p_strValue"]                                                   = $l_catdata['isys_catg_share_access_list__mountpoint'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata['isys_catg_share_access_list__description'];

        if ($l_catdata['isys_connection__isys_obj__id'] > 0)
        {
            $l_res = isys_cmdb_dao_category_g_shares::instance($p_cat->get_database_component())
                ->get_data(null, $l_catdata['isys_connection__isys_obj__id']);

            if (count($l_res) > 0)
            {
                $l_data = [];

                while ($l_row = $l_res->get_row())
                {
                    $l_data[$l_row['isys_catg_shares_list__id']] = $l_row['isys_catg_shares_list__title'];
                } // while

                $l_rules['C__CATG__SHARE_ACCESS__SHARE']['p_arData']        = serialize($l_data);
                $l_rules['C__CATG__SHARE_ACCESS__SHARE']['p_strSelectedID'] = $l_catdata['isys_catg_share_access_list__isys_catg_shares_list__id'];
            } // if
        } // if

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    } // function
} // class