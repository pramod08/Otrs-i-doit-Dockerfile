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
 * DAO: Specific Layer2 assigned ports list.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_layer2_net_assigned_ports extends isys_cmdb_dao_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category()
    {
        return C__CATS__LAYER2_NET_ASSIGNED_PORTS;
    } // function

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     * Get result method for retrieving data to display in the table.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;
        $l_sql        = 'SELECT cats.isys_cats_layer2_net_assigned_ports_list__isys_obj__id, port.isys_catg_port_list__id, isys_catg_port_list__title, isys_catg_port_list__mac, isys_catg_port_list__isys_obj__id, isys_obj__title, isys_obj_type__title ' . 'FROM isys_cats_layer2_net_assigned_ports_list AS cats ' . // Joining isys_catg_port_list for the port-names
            'LEFT JOIN isys_catg_port_list AS port ' . 'ON cats.isys_catg_port_list__id = port.isys_catg_port_list__id ' . // Joining isys_obj table for the connected object
            'LEFT JOIN isys_obj AS obj ' . 'ON port.isys_catg_port_list__isys_obj__id = obj.isys_obj__id ' . // Joining isys_obj_type table for the object type
            'LEFT JOIN isys_obj_type AS obj_type ' . 'ON obj.isys_obj__isys_obj_type__id = obj_type.isys_obj_type__id ' . // Joining the isys_obj_
            'WHERE cats.isys_cats_layer2_net_assigned_ports_list__isys_obj__id = ' . $this->convert_sql_id(
                $p_objID
            ) . ' ' . 'AND cats.isys_cats_layer2_net_assigned_ports_list__status = ' . $l_cRecStatus;

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for modifying the single rows for displaying links or getting translations.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     *
     * @param   array                                    & $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_template_language_manager;

        // Here we'll have to find out which subtype the layer2 net is.
        $l_l2_net_dao = new isys_cmdb_dao_category_s_layer2_net($this->m_db);
        $l_l2_net_row = $l_l2_net_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->get_row();

        if ($l_l2_net_row['isys_cats_layer2_net_list__isys_layer2_net_subtype__id'] == C__CATS__LAYER2_NET__SUBTYPE__DYNAMIC_VLAN)
        {
            // We have a dynamic layer2 VLAN. Display mac-addresses only!
            $p_row['port_title'] = $p_row['isys_catg_port_list__mac'];

            if (empty($p_row['isys_catg_port_list__mac']))
            {
                $p_row['port_title'] = '-';
            } // if
        }
        else
        {
            // We don't have a dynamic layer2 VLAN. Display the ports.
            $p_row['port_title'] = $p_row['isys_catg_port_list__title'];

            if (empty($p_row['isys_catg_port_list__title']))
            {
                $p_row['port_title'] = '-';
            } // if
        } // if

        $p_row['id']        = $p_row['isys_catg_port_list__id'];
        $p_row['obj_title'] = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $p_row['isys_catg_port_list__isys_obj__id'] . '">' . $p_row['isys_obj__title'] . ' (' . $g_comp_template_language_manager->get(
                $p_row['isys_obj_type__title']
            ) . ')</a>';
    } // function

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Method for retrieving the fields to display in the list-view.
     *
     * @global  isys_component_template_language_manager $g_comp_template_language_manager
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        global $g_comp_template_language_manager;

        // We prepare the return array, but add the other values later for a certain position.
        $l_return = ['id' => 'ID'];

        // Here we'll have to find out which subtype the layer2 net is.
        $l_l2_net_dao = new isys_cmdb_dao_category_s_layer2_net($this->m_db);
        $l_l2_net_row = $l_l2_net_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->get_row();

        if ($l_l2_net_row['isys_cats_layer2_net_list__isys_layer2_net_subtype__id'] == C__CATS__LAYER2_NET__SUBTYPE__DYNAMIC_VLAN)
        {
            $l_return['port_title'] = $g_comp_template_language_manager->get('LC__CMDB__CATG__PORT__MAC');
        }
        else
        {
            $l_return['port_title'] = 'Port';
        } // if

        $l_return['obj_title'] = 'LC_UNIVERSAL__OBJECT';

        return $l_return;
    } // function

    /**
     * Returns the link the browser shall follow if clicked on a row.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function make_row_link()
    {
        return '#';
    } // function
} // class
?>