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
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_connector extends isys_ajax_handler
{

    /**
     * Init
     *
     * @throws \Exception if method is unkown
     */
    public function init()
    {
        $l_method = $_GET['method'];

        switch ($l_method)
        {
            case 'get_fiber_lead':
                $l_return = $this->get_fiber_lead((int) $_POST['cable_object_id'], (int) $_POST['connector_id']);
                break;
            case 'load_listeners':
                $l_return = $this->load_listeners((int) $_POST['id']);
                break;
            default:
                throw new \Exception(sprintf('unknown method "%s"', $l_method));
        } // switch

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);
        $this->_die();
    } //function

    /**
     * @param   integer $p_cable_object_id
     * @param   integer $p_connector_id
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    protected function get_fiber_lead($p_cable_object_id, $p_connector_id)
    {
        $l_dao = isys_cmdb_dao_category_g_fiber_lead::instance($this->m_database_component);

        $l_fibers_leads = $l_dao->get_data_by_object($p_cable_object_id)
            ->__as_array();

        $l_sql = 'SELECT isys_catg_connector_list__id, isys_catg_connector_list__used_fiber_lead_rx, isys_catg_connector_list__used_fiber_lead_tx' . ' FROM isys_catg_connector_list' . ' INNER JOIN isys_catg_fiber_lead_list AS rx ON rx.isys_catg_fiber_lead_list__id = isys_catg_connector_list__used_fiber_lead_rx' . ' INNER JOIN isys_catg_fiber_lead_list AS tx ON tx.isys_catg_fiber_lead_list__id = isys_catg_connector_list__used_fiber_lead_tx' . ' WHERE isys_catg_connector_list__id = ' . $l_dao->convert_sql_id(
                $p_connector_id
            ) . ' AND (rx.isys_catg_fiber_lead_list__isys_obj__id = ' . $l_dao->convert_sql_id(
                $p_cable_object_id
            ) . ' OR ' . 'tx.isys_catg_fiber_lead_list__isys_obj__id = ' . $l_dao->convert_sql_id($p_cable_object_id) . ');';

        $l_used_fibers_leads = $l_dao->retrieve($l_sql)
            ->__as_array();

        $l_options = [];

        foreach ($l_fibers_leads as $l_fiber_lead)
        {
            $l_option = [
                'isys_catg_fiber_lead_list__id'    => $l_fiber_lead['isys_catg_fiber_lead_list__id'],
                'isys_catg_fiber_lead_list__label' => $l_fiber_lead['isys_catg_fiber_lead_list__label'],
                'isys_fiber_category__title'       => $l_fiber_lead['isys_fiber_category__title'],
                'isys_cable_colour__title'         => $l_fiber_lead['isys_cable_colour__title'],
                'disabled'                         => false
            ];

            foreach ($l_used_fibers_leads as $l_used_fiber_lead)
            {
                if ($l_fiber_lead['isys_catg_fiber_lead__id'] === $l_used_fiber_lead['isys_catg_connector_list__used_fiber_lead_rx'] || $l_fiber_lead['isys_catg_fiber_lead__id'] === $l_used_fiber_lead['isys_catg_connector_list__used_fiber_lead_rx'])
                {
                    $l_option['disabled'] = true;
                    break;
                } //if
            } //foreach

            $l_options[] = $l_option;
        } //foreach

        return $l_options;
    } // function

    /**
     * Method which retrieves all listeners of the selected object
     *
     * @param $p_obj_id
     *
     * @return array
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function load_listeners($p_obj_id)
    {
        /**
         * @var $l_dao isys_cmdb_dao_category_g_net_listener
         */
        $l_dao    = isys_cmdb_dao_category_g_net_listener::instance(isys_application::instance()->database);
        $l_res    = $l_dao->get_data(null, $p_obj_id);
        $l_return = [];

        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row['isys_catg_net_listener_list__id']] = $l_row['isys_net_protocol__title'] . '/' . $l_row['isys_cats_net_ip_addresses_list__title'] . ':' . $l_row['isys_catg_net_listener_list__port_from'] . ' | ' . $l_row['isys_obj__title'];
            } // while
        } // if
        return $l_return;
    } //function
} //class
