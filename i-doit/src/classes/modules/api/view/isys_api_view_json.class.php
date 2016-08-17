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
 * JSON API view
 *
 * @package     i-doit
 * @subpackage  API
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_api_view_json extends isys_api_view
{
    /**
     * Header to send.
     *
     * @var  string
     */
    protected $m_header = "Content-Type: application/json";

    /**
     * Sets and formats raw response.
     *
     * @param  array $p_response_data Raw response data
     */
    public function set_response($p_response_data)
    {
        assert('is_array($p_response_data)');
        $this->m_response = $p_response_data;

        // Unset the error-key on success to prevent misinterpretation.
        if (empty($p_response_data["error"]))
        {
            unset($p_response_data["error"]);
        }
        else
        {
            unset($p_response_data["result"]);
        } // if

        $this->m_formatted_response = isys_format_json::encode($p_response_data);
    } // function
} //class