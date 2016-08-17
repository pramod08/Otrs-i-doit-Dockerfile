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
 * CMDB DAO exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_exception_dao_cmdb extends isys_exception_dao
{
    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  string  $p_strDAO This is really really unnecessary.
     * @param  integer $p_errorcode
     * @param  object  $p_dao
     *
     * @todo   Refactor and kick "$p_strDAO" out!
     */
    public function __construct($p_message, $p_strDAO = "", $p_errorcode = 0, $p_dao = null)
    {
        if (!empty($p_dao))
        {
            $l_dao = "CMDB (" . get_class($p_dao) . "): ";
        } // if
        else $l_dao = '';

        parent::__construct($l_dao . $p_message, $p_errorcode);
    } // function
} // class