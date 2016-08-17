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
 * Missing function/class exception.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.3
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_missing_function extends isys_exception
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'Missing function';

    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_error_code
     */
    public function __construct($p_function = '', $p_message = null)
    {
        if (!$p_message)
        {
            $p_message = _L('LC__EXCEPTION__MISSING_FUNCTION', $p_function);
        }

        parent::__construct($p_message, '');
    }
}