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
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_workflow_data extends isys_workflow
{
    /**
     * @var  array
     */
    private $m_workflow_actions;

    /**
     * Add a new action.
     *
     * @param  isys_workflow_action $p_workflow_action
     */
    public function add_action($p_workflow_action)
    {
        $this->m_workflow_actions[] = $p_workflow_action;
    } // function

    /**
     * Get actions for this datapack.
     *
     * @return  array
     */
    public function get_actions()
    {
        return $this->m_workflow_actions;
    } // function

    /**
     * Get a parameter by id.
     *
     * @param   isys_workflow_action $p_action
     * @param   int                  $p_id
     *
     * @return  isys_workflow_action_parameter
     */
    public function get_parameter_by_id(isys_workflow_action $p_action, $p_id)
    {
        return $this->get_parameter($p_action, $p_id, null);
    } // function

    /**
     * Get a parameter by key.
     *
     * @param   isys_workflow_action $p_action
     * @param   string               $p_key
     *
     * @return  isys_workflow_action_parameter
     */
    public function get_parameter_by_key(isys_workflow_action $p_action, $p_key)
    {
        return $this->get_parameter($p_action, null, $p_key);
    } // function

    /**
     * Return a parameter by id or key.
     *
     * @param   object  $p_action
     * @param   integer $p_id
     * @param   string  $p_key
     *
     * @return  isys_workflow_action_parameter
     */
    private function get_parameter($p_action, $p_id, $p_key)
    {
        $l_parameters = $p_action->get_parameters();

        if (is_array($l_parameters))
        {
            foreach ($l_parameters as $l_value)
            {
                if (is_object($l_value))
                {
                    if (!is_null($p_key) && $l_value->get_key() == $p_key)
                    {
                        return $l_value;
                    } // if

                    if (!is_null($p_id) && $l_value->get_id() == $p_id)
                    {
                        return $l_value;
                    } // if
                } // if
            } // foreach
        } // if
    } // function
} // class