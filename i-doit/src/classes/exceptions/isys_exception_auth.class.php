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
 * Auth exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @since       1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_auth extends isys_exception
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'LC__AUTH__EXCEPTION_TITLE';

    /**
     * Exception constructor.
     *
     * @global          isys_component_template
     *
     * @param   string  $p_message
     * @param   string  $p_extinfo
     * @param   integer $p_code
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_message, $p_extinfo = '', $p_code = 0)
    {
        parent::__construct(_L('LC__AUTH__EXCEPTION') . $p_message, $p_extinfo, $p_code, date('Y-m-d') . '_auth_exception', false);
    } // function
} // class