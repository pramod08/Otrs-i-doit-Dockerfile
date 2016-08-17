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
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_calc_ip_address extends isys_ajax_handler
{
    /**
     * @var  isys_cmdb_dao_category_g_ip
     */
    private $m_ip_address_dao;

    /**
     * Init method for this AJAX request.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        $this->m_ip_address_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database_component);

        switch ($_GET['func'])
        {
            case 'find_free_v4':
                $l_return = $this->find_free_v4((int) $_POST['net_obj_id'], (int) $_POST['ip_assignment']);
                break;

            case 'is_free_v4':
                $l_return = $this->is_free_v4((int) $_POST['net_obj_id'], $_POST['ip'], $_POST['objID']);
                break;
        } // switch

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * Method for finding a free IPv4 address.
     *
     * @param   integer $p_net_obj_id
     * @param   integer $p_ip_assignment
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function find_free_v4($p_net_obj_id, $p_ip_assignment)
    {
        if ($p_net_obj_id > 0 && $p_ip_assignment > 0)
        {
            return [
                'success' => true,
                'data'    => $this->m_ip_address_dao->get_free_ip($p_net_obj_id, $p_ip_assignment)
            ];
        } // if

        return [
            'success' => false
        ];
    } // function

    /**
     * Method returns true/false if a given IP-address is free or not.
     *
     * @param   integer $p_net_obj_id
     * @param   string  $p_ip
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function is_free_v4($p_net_obj_id, $p_ip, $p_excludeObjID)
    {
        return [
            'success' => !$this->m_ip_address_dao->ip_already_in_use($p_net_obj_id, $p_ip, $p_excludeObjID),
            'net'     => (($p_net_obj_id == C__OBJ__NET_GLOBAL_IPV4) ? isys_cmdb_dao_category_s_net::instance($this->m_database_component)
                ->get_matching_net_by_ipv4_address($p_ip) : null)
        ];
    } // function
} // class