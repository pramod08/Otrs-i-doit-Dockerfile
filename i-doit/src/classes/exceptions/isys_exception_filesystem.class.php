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
 * Filesystem exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_filesystem extends isys_exception
{
    /**
     * Exception constructor.
     *
     * @param   string  $p_message
     * @param   string  $p_extinfo
     * @param   integer $p_code
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_message, $p_extinfo = '', $p_code = 0)
    {
        isys_application::instance()->logger->addWarning('Filesystem error: ' . $p_message);
        parent::__construct('Filesystem error: ' . $p_message, $p_extinfo, $p_code);
    } // function
} // class