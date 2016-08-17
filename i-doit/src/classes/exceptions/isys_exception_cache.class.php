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
 * Cache exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.4
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_cache extends isys_exception
{

    /**
     * Variable which holds the current cache handler
     *
     * @var  string
     */
    private $m_cache_handler = '';

    /**
     * Method for returning the cache handler
     *
     * @return  string
     */
    public function get_cache_handler()
    {
        return $this->m_cache_handler;
    }

    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_error_code
     */
    public function __construct($p_message, $p_cache_handler)
    {
        $this->m_cache_handler = $p_cache_handler;

        isys_application::instance()->logger->addWarning($p_cache_handler . ': ' . $p_message);
        parent::__construct($p_cache_handler . ': ' . $p_message, '', 0, 'exception', false);
    }
}