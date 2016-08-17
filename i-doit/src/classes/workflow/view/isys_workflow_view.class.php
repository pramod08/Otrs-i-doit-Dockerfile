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
 * @package     i-doit
 * @subpackage  Workflow
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_workflow_view extends isys_cmdb_view
{
    /**
     * Constructor method.
     *
     * @param   isys_module_request $p_modreq
     *
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class
?>