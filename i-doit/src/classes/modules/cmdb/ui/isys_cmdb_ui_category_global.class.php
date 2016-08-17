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
 * CMDB UI: global category abstraction layer.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_cmdb_ui_category_global extends isys_cmdb_ui_category
{
    /**
     * Fetches category's title from database.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        $l_cat_id = $p_cat->get_category_id();

        $l_title = $p_cat->retrieve('SELECT isysgui_catg__title FROM isysgui_catg WHERE isysgui_catg__id = ' . $p_cat->convert_sql_id($l_cat_id) . ';')
            ->get_row_value('isysgui_catg__title');

        if (!empty($l_title))
        {
            return _L($l_title);
        } // if

        return 'ERROR: isysgui_catg, title for selected catg not found (ID: ' . $l_cat_id . ').';
    } // function
} // class