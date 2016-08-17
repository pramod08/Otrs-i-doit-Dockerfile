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
 * i-doit APi
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_idoit_version implements isys_api_model_interface
{

    /**
     * Documentation missing
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        global $g_comp_session;

        $l_userdata = $g_comp_session->get_userdata();

        return array_merge(
            [
                'login' => [
                    'userid'   => $g_comp_session->get_user_id(),
                    'name'     => $l_userdata['name'],
                    'mail'     => $l_userdata['email'],
                    'username' => $g_comp_session->get_current_username(),
                    'mandator' => $g_comp_session->get_mandator_name(),
                    'language' => $g_comp_session->get_language()
                ]
            ],
            isys_application::instance()->info->toArray()
        );
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {

    } // function

} // class