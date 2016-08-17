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
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_ajax_handler_monitoring_ndo extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'load_ndo_state':
                    $l_return['data'] = current($this->load_ndo_states([$_POST[C__CMDB__GET__OBJECT]], ($_POST['force'] == 1 ? true : false)));
                    break;

                case 'load_ndo_states':
                    $l_return['data'] = $this->load_ndo_states(isys_format_json::decode($_POST['obj_ids']) ?: [], ($_POST['force'] == 1 ? true : false));
                    break;

                case 'load_ndo_service':
                    $l_return['data'] = current($this->load_ndo_services([$_POST[C__CMDB__GET__OBJECT]], ($_POST['force'] == 1 ? true : false)));
                    break;

                case 'load_ndo_services':
                    $l_return['data'] = $this->load_ndo_services(isys_format_json::decode($_POST['obj_ids']) ?: [], ($_POST['force'] == 1 ? true : false));
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

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
     * This method will retrieve the "NDO" data of a given host in realtime.
     *
     * @param   array   $p_obj_ids
     * @param   boolean $p_force
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_ndo_states(array $p_obj_ids, $p_force = false)
    {
        if (!$p_force)
        {
            // Enable cache lifetime of 5 minutes.
            isys_core::expire(isys_convert::MINUTE * 5);
        } // if

        $l_display_error = false;
        $p_obj_ids       = array_filter($p_obj_ids);
        $l_states        = isys_monitoring_helper::get_state_info();
        $l_host_states   = isys_monitoring_helper::get_host_state_info();
        $l_return        = [];

        if (count($p_obj_ids) > 0)
        {
            foreach ($p_obj_ids as $l_obj_id)
            {
                try
                {
                    $l_row = isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
                        ->get_data(null, $l_obj_id)
                        ->get_row();

                    if (empty($l_row['isys_catg_monitoring_list__isys_monitoring_hosts__id']) || $l_row['isys_monitoring_hosts__type'] != C__MONITORING__TYPE_NDO || $l_row['isys_monitoring_hosts__active'] != 1)
                    {
                        continue;
                    } // if

                    $l_host_data = isys_monitoring_ndo::factory($l_row["isys_catg_monitoring_list__isys_monitoring_hosts__id"])
                        ->get_ndo_dao()
                        ->get_host_data($l_obj_id)
                        ->get_row();

                    $l_return[] = [
                        'obj_id'     => $l_obj_id,
                        'hostname'   => $l_host_data['hostname'],
                        'state'      => $l_states[$l_host_data['state']],
                        'host_state' => $l_host_states[$l_host_data['state']]
                    ];
                }
                catch (Exception $e)
                {
                    $l_display_error = $e;
                } // try
            } // foreach

            // If a error occurs, we do not display it for each iteration.
            if ($l_display_error instanceof Exception)
            {
                isys_notify::error($l_display_error->getMessage());
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * @param   array   $p_obj_ids
     * @param   boolean $p_force
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_ndo_services(array $p_obj_ids, $p_force = false)
    {
        if (!$p_force)
        {
            // Enable cache lifetime of 5 minutes.
            isys_core::expire(isys_convert::MINUTE * 5);
        } // if

        $p_obj_ids = array_filter($p_obj_ids);
        $l_states  = isys_monitoring_helper::get_state_info();
        $l_return  = [];

        if (count($p_obj_ids) > 0)
        {
            foreach ($p_obj_ids as $l_obj_id)
            {
                $l_services = [];
                $l_row      = isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
                    ->get_data(null, $l_obj_id)
                    ->get_row();

                if ($l_row['isys_monitoring_hosts__type'] != C__MONITORING__TYPE_NDO || $l_row['isys_monitoring_hosts__active'] != 1)
                {
                    continue;
                } // if

                $l_service_res = isys_monitoring_ndo::factory($l_row["isys_catg_monitoring_list__isys_monitoring_hosts__id"])
                    ->get_ndo_dao()
                    ->get_service_data($l_obj_id);

                if (count($l_service_res))
                {
                    while ($l_service_row = $l_service_res->get_row())
                    {
                        $l_services[] = [
                            'name'          => $l_service_row['name'],
                            'check_command' => $l_service_row['check_command'],
                            'state'         => $l_states[$l_service_row['state']]
                        ];
                    } // while
                } // if

                $l_return[] = [
                    'obj_id'   => $l_obj_id,
                    'hostname' => isys_monitoring_helper::render_export_hostname($l_obj_id),
                    'services' => $l_services
                ];
            } // foreach
        } // if

        return $l_return;
    } // function
} // class