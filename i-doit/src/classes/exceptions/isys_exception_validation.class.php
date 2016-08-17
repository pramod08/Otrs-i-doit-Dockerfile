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
 * Validation exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Leonard Fischer <lfischer@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_validation extends isys_exception_api
{

    /**
     * This variable will hold the failed category entry ID.
     *
     * @var  integer
     */
    protected $m_cat_entry_id = [];
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'Validation exception';
    /**
     * This variable will hold all failed validations.
     *
     * @var  array
     */
    protected $m_validation_errors = [];

    /**
     * Method for retrieving the validation failures.
     *
     * @return array
     */
    public function get_validation_errors()
    {
        return $this->m_validation_errors;
    } // function

    /**
     * Method for retrieving the validation failures.
     *
     * @return array
     */
    public function get_cat_entry_id()
    {
        return $this->m_cat_entry_id;
    } // function

    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  array   $p_validation_errors
     * @param  integer $p_cat_entry_id
     */
    public function __construct($p_message, $p_validation_errors, $p_cat_entry_id = null)
    {
        $this->m_validation_errors = $p_validation_errors;
        $this->m_cat_entry_id      = (int) $p_cat_entry_id;

        parent::__construct($p_message, 0);
    } // function
} // class