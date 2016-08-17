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
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_connector extends isys_cmdb_dao_list
{
    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_connector_list__title'             => _L('LC__CMDB__CATG__TITLE'),
            'isys_connection_type__title'                 => _L('LC__CATG__CONNECTOR__CONNECTION_TYPE'),
            'connector_name'                              => _L('LC__CMDB__CONNECTED_WITH'),
            'isys_catg_connector_list__assigned_category' => _L('LC__CMDB__CATG__CATEGORY'),
            'isys_interface__title'                       => _L('LC__CATG__CONNECTOR__INTERFACE'),
            'cable_object'                                => _L('LC__CMDB__OBJTYPE__CABLE'),
            'fiber_wave_lengths'                          => _L('LC__CATG__CONNECTOR__FIBER_WAVE_LENGTHS')
        ];
    } // function

    /**
     * Modify each row with certain contents.
     *
     * @param   array & $p_row
     *
     * @return array
     */
    public function modify_row(&$p_row)
    {
        $l_dao_connection = new isys_cmdb_dao_cable_connection($this->m_db);

        $l_quick_info                           = new isys_ajax_handler_quick_info();
        $p_row["isys_connection__isys_obj__id"] = $l_quick_info->get_quick_info(
            $p_row["isys_connection__isys_obj__id"],
            $l_dao_connection->get_obj_name_by_id_as_string($p_row["isys_connection__isys_obj__id"]),
            C__LINK__OBJECT
        );

        $l_connector_data = $l_dao_connection->get_assigned_connector($p_row["isys_catg_connector_list__id"]);

        if ($l_connector_data->num_rows() > 0)
        {
            $l_connector_data              = $l_connector_data->__to_array();
            $l_get[C__CMDB__GET__CATLEVEL] = $l_connector_data["isys_catg_connector_list__id"];

            $p_row["connector_name"]  = $l_quick_info->get_quick_info(
                $l_connector_data["isys_catg_connector_list__isys_obj__id"],
                $l_connector_data["isys_obj__title"] . " &raquo; " . $l_connector_data["isys_catg_connector_list__title"],
                C__LINK__OBJECT,
                false,
                $l_get
            );
            $p_row["target_objectID"] = $l_connector_data["isys_obj__id"];
        }
        else
        {
            $p_row["connector_name"] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        // Check for the assigned categories, before they get their real title.
        if ($p_row["isys_catg_connector_list__assigned_category"] != 'C__CATG__UNIVERSAL_INTERFACE' || $p_row["isys_catg_connector_list__assigned_category"] != C__CATG__UNIVERSAL_INTERFACE)
        {
            $p_row["isys_connection_type__title"] = ($p_row["isys_connection_type__id"] > 0) ? _L($p_row["isys_connection_type__title"]) : '-';
        }
        else
        {
            // Because this category has an own dialog-field for it's "connection type" we need to get it seperately.
            $l_ui_dao = new isys_cmdb_dao_category_g_ui($this->m_db);
            $l_ui_row = $l_ui_dao->get_data(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                'AND isys_catg_ui_list__isys_catg_connector_list__id = ' . $l_ui_dao->convert_sql_id($p_row['isys_catg_connector_list__id'])
            )
                ->get_row();

            $p_row["isys_connection_type__title"] = $l_ui_row['isys_ui_plugtype__title'];
        }

        /** @var isys_cmdb_dao_category_g_connector $l_dao */
        $l_dao                                                = isys_cmdb_dao_category_g_connector::instance($this->m_db);
        $p_row["isys_catg_connector_list__assigned_category"] = $l_dao->get_assigned_category_title($p_row["isys_catg_connector_list__assigned_category"]);

        // Column for interface.
        if (!isset($p_row['isys_interface__title']))
        {
            $p_row['isys_interface__title'] = isys_tenantsettings::get('gui.empty_value', '-');
        } //if

        // Column for cable and its fibers/leads.
        if (isset($p_row['isys_catg_connector_list__isys_cable_connection__id']))
        {
            $p_row['cable'] = $l_quick_info->get_quick_info($p_row['cable_id'], $p_row["cable_title"], C__LINK__OBJECT);
        }
        else
        {
            $p_row['cable'] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        // Column for fiber wave lengths.
        $l_assigned_fiber_wave_lengths = $l_dao->get_assigned_fiber_wave_lengths($p_row['isys_obj__id'], $p_row['isys_catg_connector_list__id']);

        $l_fiber_color = [];
        /** @var isys_cmdb_dao_category_g_fiber_lead $l_fiber_lead_dao */
        $l_fiber_lead_dao     = isys_cmdb_dao_category_g_fiber_lead::instance($this->m_db);
        $l_fiber_wave_lengths = '';

        if (isset($p_row['isys_catg_connector_list__used_fiber_lead_rx']) && isset($p_row['isys_catg_connector_list__used_fiber_lead_rx']) > 0)
        {
            $l_fiber_color[] = $l_fiber_lead_dao->get_data($p_row['isys_catg_connector_list__used_fiber_lead_rx'])
                ->get_row_value('isys_cable_colour__title');
        } // if

        if (isset($p_row['isys_catg_connector_list__used_fiber_lead_tx']) && isset($p_row['isys_catg_connector_list__used_fiber_lead_tx']) > 0)
        {
            $l_fiber_color[] = $l_fiber_lead_dao->get_data($p_row['isys_catg_connector_list__used_fiber_lead_tx'])
                ->get_row_value('isys_cable_colour__title');
        } // if

        if (count($l_fiber_color))
        {
            $l_fiber_wave_lengths = implode(', ', $l_fiber_color);
        } // if

        if ($l_assigned_fiber_wave_lengths->count() === 0)
        {
            $p_row['fiber_wave_lengths'] = isys_tenantsettings::get('gui.empty_value', '-');
        }
        else
        {
            $l_assigned_fiber_wave_length_list = [];

            while ($l_assigned_fiber_wave_length = $l_assigned_fiber_wave_lengths->get_row())
            {
                $l_assigned_fiber_wave_length_list[] = $l_assigned_fiber_wave_length['isys_fiber_wave_length__title'];
            } //while

            $l_fiber_wave_lengths .= (strlen($l_fiber_wave_lengths) > 0 ? ' / ' : '') . implode(', ', $l_assigned_fiber_wave_length_list);
        } // if

        $p_row['fiber_wave_lengths'] = $l_fiber_wave_lengths;

        return $p_row;
    } // function
} // class
