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
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-8
 */
class isys_report_view_layer3_nets extends isys_report_view
{
    /**
     * Method for ajax-requests. Must be implemented.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_request()
    {
        ;
    } // function

    /**
     * Method for retrieving the language constant of the report-description.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__LAYER3_NETS__DESCRIPTION';
    } // function

    /**
     * Initialize method.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        return true;
    } // function

    /**
     * Method for retrieving the language constant of the report-name.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__LAYER3_NETS__TITLE';
    } // function

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @global  isys_component_template $g_comp_template
     * @global  isys_component_database $g_comp_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function start()
    {
        global $g_comp_template, $g_comp_database;

        // Preparing some variables.
        $l_data   = [];
        $l_return = [];

        // Initializing the DAO's.
        $l_obj_dao  = new isys_cmdb_dao($g_comp_database);
        $l_l3_dao   = new isys_cmdb_dao_category_s_net($g_comp_database);
        $l_port_dao = new isys_cmdb_dao_category_g_network_port($g_comp_database);

        // At first we search all objects of the type "layer2 net".
        $l_obj_res = $l_obj_dao->get_objects_by_type(C__OBJTYPE__LAYER3_NET);

        // And now the fun begins...
        while ($l_obj_row = $l_obj_res->get_row())
        {
            if (empty($l_obj_row['isys_obj__title']))
            {
                $l_obj_row['isys_obj__title'] = '(' . _L('LC__UNIVERSAL__NO_TITLE') . ')';
            } // if

            $l_l3_link = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_obj_row['isys_obj__id'] . '">' . $l_obj_row['isys_obj__title'] . '</a>';

            // We need this for L3 nets without assigned objects.
            if (!isset($l_data[$l_l3_link]))
            {
                $l_data[$l_l3_link] = [];
            } // if

            $l_server_res = $l_l3_dao->get_assigned_hosts($l_obj_row['isys_obj__id']);

            // Here we retrieve all server, which have assigned the layer3 net of this iteration.
            while ($l_server_row = $l_server_res->get_row())
            {
                if (empty($l_server_row['isys_obj__title']))
                {
                    $l_server_row['isys_obj__title'] = '(' . _L('LC__UNIVERSAL__NO_TITLE') . ')';
                } // if

                $l_server_link = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_server_row['isys_obj__id'] . '">' . $l_server_row['isys_obj__title'] . '</a>';

                // We need this for L3 nets without ports.
                if (!isset($l_data[$l_l3_link][$l_server_link]))
                {
                    $l_data[$l_l3_link][$l_server_link] = [];
                } // if

                // For this server, we fetch all ports.
                $l_port_res = $l_port_dao->get_data(null, $l_server_row['isys_obj__id']);

                while ($l_port_row = $l_port_res->get_row())
                {
                    if ($l_port_row['isys_catg_ip_list__id'] === null)
                    {
                        continue;
                    } // if

                    $l_ip_address = $l_port_row['isys_cats_net_ip_addresses_list__title'];
                    $l_ip_port    = $l_port_row['isys_catg_port_list__title'];

                    // For each port we can now select the assigned layer2 nets.
                    $l_l2_nets = $l_port_dao->get_attached_layer2_net_as_array($l_port_row['isys_catg_port_list__id']);

                    if (count($l_l2_nets) > 0)
                    {
                        foreach ($l_l2_nets as $l_l2_net_id)
                        {
                            $l_l2_link                                                     = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_l2_net_id . '">' . $l_obj_dao->get_obj_name_by_id_as_string(
                                    $l_l2_net_id
                                ) . '</a>';
                            $l_data[$l_l3_link][$l_server_link][$l_ip_address][$l_ip_port] = $l_l2_link;
                        } // foreach
                    }
                    else
                    {
                        $l_data[$l_l3_link][$l_server_link][$l_ip_address][$l_ip_port] = '';
                    } // if
                } // while
            } // while
        } // while

        // To easily display the data we have to alter the array structure.
        foreach ($l_data as $l_key => $l_item)
        {
            if (is_array($l_item) && !empty($l_item))
            {
                foreach ($l_item as $l_key2 => $l_item2)
                {
                    if (is_array($l_item2) && !empty($l_item2))
                    {
                        foreach ($l_item2 as $l_key3 => $l_item3)
                        {
                            if (is_array($l_item3) && !empty($l_item3))
                            {
                                foreach ($l_item3 as $l_key4 => $l_item4)
                                {
                                    $l_return[] = [
                                        isys_glob_utf8_encode($l_key),
                                        isys_glob_utf8_encode($l_key2),
                                        isys_glob_utf8_encode($l_key3),
                                        isys_glob_utf8_encode($l_key4),
                                        isys_glob_utf8_encode($l_item4)
                                    ];
                                }
                            }
                            else
                            {
                                $l_return[] = [
                                    isys_glob_utf8_encode($l_key),
                                    isys_glob_utf8_encode($l_key2),
                                    isys_glob_utf8_encode($l_key3)
                                ];
                            }
                        } // foreach
                    }
                    else
                    {
                        $l_return[] = [
                            isys_glob_utf8_encode($l_key),
                            isys_glob_utf8_encode($l_key2)
                        ];
                    } // if
                } // foreach
            }
            else
            {
                $l_return[] = [isys_glob_utf8_encode($l_key)];
            } // if
        } // foreach

        // Finally assign the data to the template.
        $g_comp_template->assign('data', isys_format_json::encode($l_return));
    } // function

    /**
     * Method for retrieving the template-name of this report.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public function template()
    {
        return 'view_layer3_nets.tpl';
    } // function

    /**
     * Method for declaring the type of this report.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function type()
    {
        return self::c_php_view;
    } // function

    /**
     * Method for declaring the view-type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__RELATION';
    } // function
} // class
?>