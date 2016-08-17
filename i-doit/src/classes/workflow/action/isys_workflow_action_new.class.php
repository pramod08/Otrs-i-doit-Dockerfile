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
class isys_workflow_action_new extends isys_workflow_action
{
    /**
     * Return the current status of the workflow, when this action was processed.
     *
     * @return  integer
     */
    public function get_status()
    {
        return C__TASK__STATUS__INIT;
    } // function

    /**
     * Method for retrieving the path and name for the required template.
     *
     * @return  string
     * @see     isys_workflow_action::get_template()
     */
    public function get_template()
    {
        return 'workflow/detail/actions/new.tpl';
    } // function

    /**
     * Handle Method. Dummy Method.
     *
     * @see  isys_workflow_action::handle()
     */
    public function handle()
    {
        ;
    } // function

    /**
     * Save this action.
     *
     * @param   integer               $p_workflow_id
     * @param   isys_workflow_request $p_req
     * @param   integer               $p_to
     *
     * @return  boolean
     * @see     isys_workflow_action::save()
     */
    public function save($p_workflow_id, isys_workflow_request &$p_req, $p_to = null)
    {
        global $g_comp_database;

        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_workflow_type__id = $p_req->get_workflow_type();

        $l_dao_workflow = new isys_workflow_dao_action($g_comp_database);
        $l_dao_template = new isys_workflow_dao_template($g_comp_database);

        $l_workflow_id = $p_workflow_id;

        $l_request = $p_req->get_request();
        $l_to      = $p_req->get_to();

        if (is_numeric($p_to) && $p_to > 0)
        {
            $l_to = $p_to;
        } // if

        // When we are binding data to a workflow, we firstly use the action type new to store the metadata.
        $l_action_id = $l_dao_workflow->create_action(C__WORKFLOW__ACTION__TYPE__NEW, $p_req->get_from());

        // Bind action to the newly created workflow.
        if ($l_action_id && $l_workflow_id)
        {
            // Bind the action ID to the workflow.
            $l_dao_workflow->bind($l_workflow_id, $l_action_id);

            // Set the current status.
            $l_dao_workflow->set_status($l_workflow_id, $this->get_status());
        }
        else
        {
            return false;
        } // if

        // Get assign action and assign person(s) to this workflow.
        $l_assign = new isys_workflow_action_assign();

        if (is_numeric($l_to))
        {
            $l_assign->save($l_workflow_id, $l_to);
        }
        else
        {
            $l_assign->save($l_workflow_id);
        } // if

        // Iterate through the request (post data) and add the specific parameters to our new workflow.
        foreach ($l_request as $l_key => $l_value)
        {
            // @todo: cache types in an array which is assigned to the keys, before calling this method for every request parameter.
            $l_template = $l_dao_template->get_templates($l_workflow_type__id, null, $l_key);
            $l_row      = $l_template->get_row();

            $l_type               = $l_row["isys_workflow_template_parameter__type"];
            $l_template_parameter = $l_row["isys_workflow_template_parameter__id"];

            if (preg_match("/(.*?)description/i", $l_key))
            {
                $l_description = $l_value;
            } // if

            $l_dao_workflow->add_parameter($l_action_id, $l_type, $l_key, $l_value, $l_template_parameter);
        } // foreach

        // Add logbook entry.
        $l_mod_event_manager->triggerWorkflowEvent("C__LOGBOOK_ENTRY__WORKFLOW_CREATED", $l_description, $l_workflow_id);

        return true;
    } // function

    /**
     * Constructor method.
     */
    public function __construct()
    {
        ;
    } // function
} // class
?>