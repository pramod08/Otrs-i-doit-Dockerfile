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
 * CMDB Explorer view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_view_explorer extends isys_cmdb_view
{
    /**
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__EXPLORER;
    } // function

    /**
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets = [];
    } // function

    /**
     * @return  string
     */
    public function get_name()
    {
        return _L('LC__MODULE__CMDB__VISUALIZATION');
    } // function

    /**
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        $l_gets = [];
    } // function

    /**
     * @param  integer $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        ;
    } // function

    /**
     *
     */
    public function process()
    {
        isys_auth_cmdb::instance()
            ->check(isys_auth::VIEW, 'EXPLORER');

        $this->init();
    } // function

    /**
     * Inititialize this view
     */
    private function init()
    {
        $l_view = $_GET[C__CMDB__VISUALIZATION_VIEW] ?: C__CMDB__VISUALIZATION_VIEW__OBJECT;
        $l_type = $_GET[C__CMDB__VISUALIZATION_TYPE] ?: C__CMDB__VISUALIZATION_TYPE__TREE;

        // If the given type does not exist, simply use the "tree".
        if (!class_exists('isys_visualization_' . $l_type))
        {
            $l_type = C__CMDB__VISUALIZATION_TYPE__TREE;
        } // if

        isys_factory::get_instance('isys_visualization_' . $l_type, isys_module_request::get_instance())
            ->init([C__CMDB__VISUALIZATION_VIEW => $l_view])
            ->start();
    } // function
} // class