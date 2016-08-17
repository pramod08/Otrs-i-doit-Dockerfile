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
 * Application Programming Interface (API).
 *
 * @package     i-doit
 * @subpackage  API
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

/**
 * API base class
 */
abstract class isys_api
{
    /**
     * API logger.
     *
     * @var  isys_log
     */
    protected $m_log;

    /**
     * Initialization method. Initializes the logger.
     */
    protected function init()
    {
        // Logger:
        $this->m_log = isys_factory_log::get_instance('api');
    } // function
} // class