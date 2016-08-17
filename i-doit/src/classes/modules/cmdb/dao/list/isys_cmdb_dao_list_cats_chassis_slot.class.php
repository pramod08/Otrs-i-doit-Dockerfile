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
 * DAO: list for chassis
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_list_cats_chassis_slot extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__CHASSIS_SLOT;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param   string $p_column
     * @param   string $p_direction
     *
     * @return  string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        switch ($p_column)
        {
            case "isys_cmdb_dao_list_cats_chassis_slot":
                return "LENGTH(" . $p_column . ") " . $p_direction . ", " . $p_column . " " . $p_direction;

            default:
                return parent::get_order_condition($p_column, $p_direction);
        } // switch
    } // function

    /**
     * Method for receiving the category data.
     *
     * @param   string  $p_table
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT cs.*, ct.isys_chassis_connector_type__title
			FROM isys_cats_chassis_slot_list cs
			LEFT JOIN isys_chassis_connector_type AS ct ON ct.isys_chassis_connector_type__id = isys_cats_chassis_slot_list__isys_chassis_connector_type__id
			WHERE isys_cats_chassis_slot_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_cats_chassis_slot_list__status = '" . $this->convert_sql_id(empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus) . "';";

        return $this->retrieve($l_sql);
    } // function

    /**
     * Exchange column to create individual links in columns.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow['isys_cats_chassis_slot_list__insertion'] == C__INSERTION__FRONT)
        {
            $p_arrRow['isys_cats_chassis_slot_list__insertion'] = _L('LC__UNIVERSAL__FRONT');
        }
        else
        {
            $p_arrRow['isys_cats_chassis_slot_list__insertion'] = _L('LC__UNIVERSAL__REAR');
        } // if

        $l_assigned_items = $this->m_cat_dao->get_assigned_chassis_items_by_cat_id($p_arrRow['isys_cats_chassis_slot_list__id']);

        if (is_array($l_assigned_items) && count($l_assigned_items) > 0)
        {
            $p_arrRow['assigned_items'] = [];
            $l_chassis_dao              = isys_cmdb_dao_category_s_chassis::instance($this->m_db);

            foreach ($l_assigned_items as $l_item)
            {
                $p_arrRow['assigned_items'][] = $l_chassis_dao->get_assigned_device_title_by_cat_id($l_item['isys_cats_chassis_list__id'], false);
            } // foreach
        } // if
    } // function

    /**
     * Method for returning the column-names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_chassis_slot_list__title"     => "LC__CMDB__LOGBOOK__TITLE",
            "isys_chassis_connector_type__title"     => "LC__CMDB__CATS__CHASSIS__CONNECTOR_TYPE",
            "isys_cats_chassis_slot_list__insertion" => "LC__CMDB__CATS__CHASSIS__INSERTION",
            "assigned_items"                         => "LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES"
        ];
    } // function
} // class