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
 * CMDB Misc view.
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_misc extends isys_cmdb_view
{
    /**
     * @return  mixed
     */
    abstract public function misc_process();

    /**
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * @param  array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * @return  mixed
     */
    public function process()
    {
        return $this->misc_process();
    } // function

    /**
     * Public constructor, for protected parent.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class
?>