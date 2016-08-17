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
 * @package     i-doit
 * @subpackage  Export
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_export_data
{
    /**
     * Variable for holding our "data".
     *
     * @var  mixed
     */
    protected $m_data;

    /**
     * Method for retrieving previously saved data.
     *
     * @return  mixed
     */
    public function get_data()
    {
        return $this->m_data;
    } // function

    /**
     * Method for setting data.
     *
     * @param  mixed $p_data
     */
    public function set_data($p_data)
    {
        $this->m_data = $p_data;
    } // function

    /**
     * @desc fixing roolbar item error: E_RECOVERABLE_ERROR: Object of class isys_export_data could not be converted to string (https://rollbar.com/Synetics/i-doit/items/864/)
     */
    public function __toString()
    {
        return json_encode($this->m_data);
    }

    /**
     * Constructor
     *
     * @param  mixed $p_data
     */
    public function __construct($p_data = null)
    {
        $this->m_data = $p_data;
    } // function
} // class