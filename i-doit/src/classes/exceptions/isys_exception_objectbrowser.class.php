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
 * CMDB exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_objectbrowser extends isys_exception
{
    /**
     * Variable for detailed error message.
     *
     * @var  string
     */
    private $m_detailed_error = '';

    /**
     * Method for retrieving the detail message.
     *
     * @return  string
     */
    public function getDetailMessage()
    {
        return $this->m_detailed_error;
    } // function

    /**
     * Exception constructors.
     *
     * @param  string  $p_message
     * @param  integer $p_detailed_error
     */
    public function __construct($p_message, $p_detailed_error)
    {
        $this->m_detailed_error = $p_detailed_error;
        parent::__construct($p_message, $p_detailed_error);
    } // function
} // class