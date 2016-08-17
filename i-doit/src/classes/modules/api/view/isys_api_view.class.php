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
 * API view
 *
 * @package    i-doit
 * @subpackage API
 * @author     Benjamin Heisig <bheisig@synetics.de>, Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/**
 * API view base class
 */
abstract class isys_api_view extends isys_api
{
    /**
     * Formatted response.
     *
     * @var  string
     */
    protected $m_formatted_response = '';
    /**
     * Header.
     *
     * @var  string
     */
    protected $m_header = "Content-Type: text/html";
    /**
     * Raw response.
     *
     * @var  array
     */
    protected $m_response = [];
    /**
     * Should we send header.
     *
     * @var  boolean
     */
    protected $m_send_header = true;

    /**
     * Sets and formats raw response.
     *
     * @param  array $p_response_data Raw response data
     */
    abstract public function set_response($p_response_data); // function

    /**
     * Gets raw response.
     *
     * @return  array  Returns empty array if raw reponse hasn't been set yet.
     */
    public function get_response()
    {
        return $this->m_response;
    } // function

    /**
     * Gets formatted response.
     *
     * @return  string  Returns an empty string if raw response hasn't been set yet.
     */
    public function get_formatted_response()
    {
        return $this->m_formatted_response;
    } // function

    /**
     * Prints the response.
     */
    public function output()
    {
        // Send the required heders.
        if ($this->m_send_header && !headers_sent())
        {
            header($this->get_header());
        } // if

        echo $this->m_formatted_response;
    } // function

    /**
     * Header setter.
     *
     * @param   string $p_header
     *
     * @return  isys_api_view
     */
    protected function set_header($p_header = null)
    {
        $this->m_header = $p_header;

        return $this;
    } // function

    /**
     * Get the Header.
     *
     * @return  string
     */
    protected function get_header()
    {
        return $this->m_header;
    }
} // class