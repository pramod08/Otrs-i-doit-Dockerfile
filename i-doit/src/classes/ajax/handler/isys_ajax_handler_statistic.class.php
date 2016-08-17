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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_ajax_handler_statistic extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        $l_return = [];

        switch ($_GET['func'])
        {
            case 'get_rack_statistics':
                $l_return = $this->get_rack_statistics($_POST['obj_id'], $_POST['as_json']);
                break;
        } // switch

        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Method for assigning an object to a rack (position and insertion).
     *
     * @param   integer $p_rack_id
     * @param   boolean $p_as_json
     *
     * @return  array
     * @uses    isys_ajax_handler_statistic::get_port_statistics
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_rack_statistics($p_rack_id, $p_as_json = true)
    {
        if (class_exists('isys_cmdb_dao_category_s_enclosure'))
        {
            $l_enc = isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
                ->get_data(null, $p_rack_id)
                ->get_row();
        } // if

        $l_formfactor = isys_cmdb_dao_category_g_formfactor::instance($this->m_database_component)
            ->get_data(null, $p_rack_id)
            ->get_row();

        $l_res = isys_cmdb_dao_location::instance($this->m_database_component)
            ->get_location($p_rack_id, null);

        // Prepare statistics for horizontal slots for preventing PHP-notices.
        $l_stats['free_h_slots_f']       = $l_formfactor['isys_catg_formfactor_list__rackunits'];
        $l_stats['used_h_slots_f']       = 0;
        $l_stats['free_h_slots_r']       = $l_formfactor['isys_catg_formfactor_list__rackunits'];
        $l_stats['used_h_slots_r']       = 0;
        $l_stats['free_h_slots_percent'] = $l_formfactor['isys_catg_formfactor_list__rackunits'] * 2;

        // Prepare statistics for vertical slots for preventing PHP-notices.
        $l_stats['free_v_slots_f']       = $l_enc['isys_cats_enclosure_list__vertical_slots_front'];
        $l_stats['used_v_slots_f']       = 0;
        $l_stats['free_v_slots_r']       = $l_enc['isys_cats_enclosure_list__vertical_slots_rear'];
        $l_stats['used_v_slots_r']       = 0;
        $l_stats['free_v_slots_percent'] = $l_enc['isys_cats_enclosure_list__vertical_slots_front'] + $l_enc['isys_cats_enclosure_list__vertical_slots_rear'];

        // Prepare the variables for preventing PHP-notices.
        $l_stats['pdu_connectors']   = [];
        $l_stats['switch_ports']     = [];
        $l_stats['fc_switch_ports']  = [];
        $l_stats['patch_connectors'] = [];

        // We prepare all objects in one array, so we can just assign them later on.
        while ($l_row = $l_res->get_row())
        {
            $l_rack_units = (int) ($l_row['isys_catg_formfactor_list__rackunits'] ?: 1);

            if ($l_row['isys_catg_location_list__option'] == C__RACK_INSERTION__HORIZONTAL && $l_row['isys_catg_location_list__insertion'] != null && $l_row['isys_catg_location_list__pos'] > 0)
            {
                if ($l_row['isys_catg_location_list__insertion'] == C__RACK_INSERTION__FRONT)
                {
                    $l_stats['used_h_slots_f'] += $l_rack_units;
                }
                else if ($l_row['isys_catg_location_list__insertion'] == C__RACK_INSERTION__BACK)
                {
                    $l_stats['used_h_slots_r'] += $l_rack_units;
                }
                else
                {
                    $l_stats['used_h_slots_f'] += $l_rack_units;
                    $l_stats['used_h_slots_r'] += $l_rack_units;
                } // if
            }
            else if ($l_row['isys_catg_location_list__option'] == C__RACK_INSERTION__VERTICAL && $l_row['isys_catg_location_list__insertion'] != null && $l_row['isys_catg_location_list__pos'] > 0)
            {
                if ($l_row['isys_catg_location_list__insertion'] == C__RACK_INSERTION__FRONT)
                {
                    $l_stats['used_v_slots_f']++;
                }
                else
                {
                    $l_stats['used_v_slots_r']++;
                } // if
            } // if

            switch ($l_row['isys_obj_type__const'])
            {
                case 'C__OBJTYPE__PDU':
                    $l_statistics = $this->get_connector_statistics($l_row['isys_obj__id']);

                    if ($l_statistics['conns_num'] > 0)
                    {
                        foreach ($l_statistics['conns'] as $l_type => $l_conn_stats)
                        {
                            $l_stats['pdu_connectors']['in'][$l_type]['free'] += $l_conn_stats['free_in'];
                            $l_stats['pdu_connectors']['in'][$l_type]['used'] += $l_conn_stats['used_in'];
                            $l_stats['pdu_connectors']['out'][$l_type]['free'] += $l_conn_stats['free_out'];
                            $l_stats['pdu_connectors']['out'][$l_type]['used'] += $l_conn_stats['used_out'];
                        } // foreach
                    } // if
                    break;

                case 'C__OBJTYPE__SWITCH':
                    $l_statistics = $this->get_port_statistics($l_row['isys_obj__id']);

                    if ($l_statistics['ports_num'] > 0)
                    {
                        foreach ($l_statistics['ports'] as $l_type => $l_port_stats)
                        {
                            $l_stats['switch_ports'][$l_type]['free'] += $l_port_stats['free'];
                            $l_stats['switch_ports'][$l_type]['used'] += $l_port_stats['used'];
                        } // foreach
                    } // if
                    break;

                case 'C__OBJTYPE__FC_SWITCH':
                    $l_statistics = $this->get_port_statistics($l_row['isys_obj__id']);

                    if ($l_statistics['ports_num'] > 0)
                    {
                        foreach ($l_statistics['ports'] as $l_type => $l_port_stats)
                        {
                            $l_stats['fc_switch_ports'][$l_type]['free'] += $l_port_stats['free'];
                            $l_stats['fc_switch_ports'][$l_type]['used'] += $l_port_stats['used'];
                        } // foreach
                    } // if
                    break;

                case 'C__OBJTYPE__PATCH_PANEL':
                    $l_statistics = $this->get_connector_statistics($l_row['isys_obj__id']);

                    if ($l_statistics['conns_num'] > 0)
                    {
                        foreach ($l_statistics['conns'] as $l_type => $l_port_stats)
                        {
                            $l_stats['patch_connectors']['in'][$l_type]['free'] += $l_port_stats['free_in'];
                            $l_stats['patch_connectors']['in'][$l_type]['used'] += $l_port_stats['used_in'];
                            $l_stats['patch_connectors']['out'][$l_type]['free'] += $l_port_stats['free_out'];
                            $l_stats['patch_connectors']['out'][$l_type]['used'] += $l_port_stats['used_out'];
                        } // foreach
                    } // if
                    break;
            } // switch
        } // while

        // We calculate the remaining free slots.
        $l_stats['free_h_slots_f']    = $l_stats['free_h_slots_f'] - $l_stats['used_h_slots_f'];
        $l_stats['free_h_slots_r']    = $l_stats['free_h_slots_r'] - $l_stats['used_h_slots_r'];
        $l_stats['free_h_slots_comb'] = $l_stats['free_h_slots_f'] + $l_stats['free_h_slots_r'];
        $l_stats['used_h_slots_comb'] = $l_stats['used_h_slots_f'] + $l_stats['used_h_slots_r'];
        if ($l_stats['free_h_slots_percent'] > 0)
        {
            $l_stats['free_h_slots_percent']       = round(($l_stats['free_h_slots_f'] + $l_stats['free_h_slots_r']) / $l_stats['free_h_slots_percent'] * 100, 2);
            $l_stats['free_h_slots_percent_color'] = isys_helper_color::retrieve_color_by_percent($l_stats['free_h_slots_percent']);
        } // if

        $l_stats['free_v_slots_f']    = $l_stats['free_v_slots_f'] - $l_stats['used_v_slots_f'];
        $l_stats['free_v_slots_r']    = $l_stats['free_v_slots_r'] - $l_stats['used_v_slots_r'];
        $l_stats['free_v_slots_comb'] = $l_stats['free_v_slots_f'] + $l_stats['free_v_slots_r'];
        $l_stats['used_v_slots_comb'] = $l_stats['used_v_slots_f'] + $l_stats['used_v_slots_r'];
        if ($l_stats['free_v_slots_percent'] > 0)
        {
            $l_stats['free_v_slots_percent']       = round(($l_stats['free_v_slots_f'] + $l_stats['free_v_slots_r']) / $l_stats['free_v_slots_percent'] * 100, 2);
            $l_stats['free_v_slots_percent_color'] = isys_helper_color::retrieve_color_by_percent($l_stats['free_v_slots_percent']);
        } // if

        // Calculate the consumption of electricity.
        $l_electricity                  = $this->get_consumption_of_electricity($p_rack_id);
        $l_stats['consumption_of_watt'] = number_format($l_electricity['watt'], 2, '.', ' ') . ' Watt';
        $l_stats['consumption_of_btu']  = number_format($l_electricity['btu'], 2, '.', ' ') . ' BTU';

        // If we don't want JSON we render the statistics-template.
        if ($p_as_json == false || $p_as_json == 'false')
        {
            global $g_comp_template;

            $g_comp_template->assign('stats', $l_stats)
                ->display("file:" . $this->m_smarty_dir . "templates/ajax/rack_statistics.tpl");

            $this->_die();
        } // if

        return $l_stats;
    } // function

    /**
     * Method which returns an array with connector statistics of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_connector_statistics($p_obj_id)
    {
        $l_return = [];

        $l_res  = isys_cmdb_dao_category_g_connector::instance($this->m_database_component)
            ->get_data(null, $p_obj_id);
        $l_plug = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_database_component);

        $l_return['conns_num'] = $l_res->num_rows();

        while ($l_row = $l_res->get_row())
        {
            $l_plug_title = $l_plug->get_data($l_row['isys_catg_connector_list__isys_connection_type__id']);
            $l_plug_title = _L($l_plug_title['isys_connection_type__title']);

            $l_inout = '_in';
            if ($l_row['isys_catg_connector_list__type'] == C__CONNECTOR__OUTPUT)
            {
                $l_inout = '_out';
            } // if

            if (empty($l_plug_title))
            {
                $l_plug_title = '<em>' . _L('LC_UNIVERSAL__NOT_SPECIFIED') . '</em>';
            } // if

            if ($l_row['con_connector'] > 0)
            {
                $l_return['conns'][$l_plug_title]['used' . $l_inout]++;
            }
            else
            {
                $l_return['conns'][$l_plug_title]['free' . $l_inout]++;
            } // if
        } // while

        return $l_return;
    } // function

    /**
     * Method for calculating the energy consumption inside an object and it's children (recursive).
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function get_consumption_of_electricity($p_obj_id)
    {
        $l_return = [
            'watt' => 0,
            'btu'  => 0
        ];

        $l_dao = isys_cmdb_dao::instance($this->m_database_component);
        $l_sql = "SELECT
			isys_catg_pc_list__watt, isys_catg_pc_list__btu, content_obj.isys_obj__id, isys_catg_pc_list__active, content_type.isys_obj_type__container
			FROM isys_catg_location_list rack_loc
			LEFT JOIN isys_obj rack_obj ON rack_loc.isys_catg_location_list__isys_obj__id = rack_obj.isys_obj__id
			INNER JOIN isys_catg_location_list content_loc ON content_loc.isys_catg_location_list__parentid = rack_obj.isys_obj__id
			INNER JOIN isys_obj content_obj ON content_loc.isys_catg_location_list__isys_obj__id = content_obj.isys_obj__id
			INNER JOIN isys_obj_type content_type ON content_obj.isys_obj__isys_obj_type__id = content_type.isys_obj_type__id
			LEFT JOIN isys_catg_pc_list ON isys_catg_pc_list__isys_obj__id = content_loc.isys_catg_location_list__isys_obj__id
			WHERE (rack_loc.isys_catg_location_list__status = 2)
			AND rack_obj.isys_obj__id = " . $l_dao->convert_sql_id($p_obj_id) . ";";

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_row['isys_catg_pc_list__active'])
                {
                    $l_return['watt'] += $l_row['isys_catg_pc_list__watt'];
                    $l_return['btu'] += $l_row['isys_catg_pc_list__btu'];
                } // if

                // We found a container object, so we check inside for more objects.
                if ($l_row['isys_obj_type__container'] == 1)
                {
                    $l_recursive_call = $this->get_consumption_of_electricity($l_row['isys_obj__id']);

                    $l_return['watt'] += $l_recursive_call['watt'];
                    $l_return['btu'] += $l_recursive_call['btu'];
                } // if
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Method which returns an array with port statistics of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_port_statistics($p_obj_id)
    {
        $l_return = [];

        $l_res  = isys_cmdb_dao_category_g_network_port::instance($this->m_database_component)
            ->get_data(null, $p_obj_id);
        $l_plug = isys_factory_cmdb_dialog_dao::get_instance('isys_plug_type', $this->m_database_component);

        $l_return['ports_num'] = $l_res->num_rows();

        while ($l_row = $l_res->get_row())
        {
            $l_plug_title = $l_plug->get_data($l_row['isys_catg_port_list__isys_plug_type__id']);
            $l_plug_title = _L($l_plug_title['isys_plug_type__title']);

            if (empty($l_plug_title))
            {
                $l_plug_title = '<em>' . _L('LC_UNIVERSAL__NOT_SPECIFIED') . '</em>';
            } // if

            if ($l_row['con_connector'] > 0)
            {
                $l_return['ports'][$l_plug_title]['used']++;
            }
            else
            {
                $l_return['ports'][$l_plug_title]['free']++;
            } // if
        } // while

        return $l_return;
    } // function
} // class