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
 * DAO: specific category list for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_audit extends isys_cmdb_dao_list
{

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    } // function

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    } // function

    /**
     * Modifies single rows for displaying links or getting translations
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_loc;

        $l_table      = $this->m_cat_dao->get_table();
        $l_type_table = 'isys_catg_audit_type';

        if ($p_row[$l_table . '__type'] > 0)
        {
            $l_sql = 'SELECT ' . $l_type_table . '__title FROM ' . $l_type_table . ' WHERE ' . $l_type_table . '__id = ' . $p_row[$l_table . '__type'] . ' LIMIT 1;';

            $l_query = $this->retrieve($l_sql);

            if ($l_row = $l_query->get_row())
            {
                $p_row[$l_table . '__type'] = $l_row[$l_type_table . '__title'];
            } // if
        } // if

        if ($p_row[$l_table . '__apply'])
        {
            $p_row[$l_table . '__apply'] = $g_loc->fmt_date($p_row[$l_table . '__apply']);
        }
        else
        {
            $p_row[$l_table . '__apply'] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function

    /**
     * Gets fields to display in the list view.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     * @return  array
     */
    public function get_fields()
    {
        $l_table      = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            $l_table . '__id'    => 'ID',
            $l_table . '__title' => $l_properties['title'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__type'  => $l_properties['type'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__apply' => $l_properties['apply'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    } // function
} // class