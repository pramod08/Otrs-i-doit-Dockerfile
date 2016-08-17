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
 * DAO: specific category list for contract assignment.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_contract_assignment extends isys_cmdb_dao_list
{

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    } //function

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    } //function

    /**
     * Modify elements in array for output.
     *
     * @param   array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_loc;

        $l_table     = $this->m_cat_dao->get_table();
        $l_dao       = new isys_cmdb_dao($this->get_database_component());
        $l_quickinfo = new isys_ajax_handler_quick_info();

        $l_obj_title              = $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_connection__isys_obj__id"]);
        $p_arrRow["object_title"] = $l_quickinfo->get_quick_info($p_arrRow["isys_connection__isys_obj__id"], $l_obj_title, C__LINK__OBJECT);

        if (empty($p_arrRow[$l_table . '__contract_start']) || empty($p_arrRow[$l_table . '__contract_end']))
        {
            $l_contract_dao  = new isys_cmdb_dao_category_s_contract($this->get_database_component());
            $l_contract_data = $l_contract_dao->get_data(null, $p_arrRow["isys_connection__isys_obj__id"])
                ->get_row();

            if (empty($p_arrRow[$l_table . '__contract_start']))
            {
                $p_arrRow[$l_table . '__contract_start'] = $g_loc->fmt_date($l_contract_data['isys_cats_contract_list__start_date']);
            }
            else
            {
                $p_arrRow[$l_table . '__contract_start'] = $g_loc->fmt_date(str_replace("00:00:00", "", $p_arrRow[$l_table . '__contract_start']));
            } // if

            if (empty($p_arrRow[$l_table . '__contract_end']))
            {
                $p_arrRow[$l_table . '__contract_end'] = $g_loc->fmt_date($l_contract_data['isys_cats_contract_list__end_date']);
            }
            else
            {
                $p_arrRow[$l_table . "__contract_end"] = $g_loc->fmt_date(str_replace("00:00:00", "", $p_arrRow[$l_table . '__contract_end']));
            } // if
        }
        else
        {
            $p_arrRow[$l_table . '__contract_start'] = $g_loc->fmt_date(str_replace("00:00:00", "", $p_arrRow[$l_table . '__contract_start']));
            $p_arrRow[$l_table . "__contract_end"]   = $g_loc->fmt_date(str_replace("00:00:00", "", $p_arrRow[$l_table . '__contract_end']));
        } // if
    } //function

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_table      = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            'object_title'                => $l_properties['connected_contract'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__contract_start' => $l_properties['contract_start'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__contract_end'   => $l_properties['contract_end'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    } // function
} //class