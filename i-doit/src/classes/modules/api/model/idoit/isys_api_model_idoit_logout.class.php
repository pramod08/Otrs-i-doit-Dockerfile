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
class isys_api_model_idoit_logout implements isys_api_model_interface
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

        if ($g_comp_session->logout())
        {
            return [
                'message' => 'Logout successfull',
                'result'  => true
            ];
        }
        else
        {
            return [
                'message' => 'Logout unsuccessfull',
                'result'  => false
            ];
        }
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {

    } // function

} // class