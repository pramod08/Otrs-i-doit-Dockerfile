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
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0 Wed Jun 21 13:48:38 CEST 2006 13:48:38
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_workflow_view_list_template extends isys_workflow_view_list
{

    public function get_id()
    {
        return C__WF__VIEW__LIST_TEMPLATE;
    }

    public function get_mandatory_parameters(&$l_gets)
    {
    }

    public function get_name()
    {
        return "i-manageIT::list.template";
    }

    public function get_optional_parameters(&$l_gets)
    {
        $l_gets[C__WF__GET__TEMPLATE] = true;
        $l_gets[C__WF__GET__TYPE]     = true;
    }

    public function list_init()
    {
        return true;
    }

    public function list_process()
    {
        return new isys_workflow_dao_list_template(
            $this->get_module_request()
                ->get_database()
        );
    }

    /*not used*/

    public function &get_detail_view()
    {
        return new isys_workflow_view_detail_template($this->m_modreq);
    }

    protected function get_id_field()
    {
        return "[{isys_workflow_template_parameter__id}]";
    }

    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}

?>