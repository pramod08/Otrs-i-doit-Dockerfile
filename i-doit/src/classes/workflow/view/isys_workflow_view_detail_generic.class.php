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
 * @subpackage  workflow
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_workflow_view_detail_generic extends isys_workflow_view_detail
{
    /**
     * Tom rules.
     *
     * @var array
     */
    private $m_rules;

    /**
     * Method for retrieving the View-ID.
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @see     isys_cmdb_view#get_id()
     */
    public function get_id()
    {
        return C__WF__VIEW__DETAIL__GENERIC;
    } // function

    /**
     * Calls the parent method for setting mandatory parameters via reference.
     *
     * @param   array & $l_gets
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    } // function

    /**
     * Retrieve the name of this view-class.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_name()
    {
        return "i-manageIT::detail.generic";
    } // function

    /**
     * Calls the parent method for setting optional parameters via reference.
     *
     * @param   array &$l_gets
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);
    } // function

    /**
     * Retrieve the template name for the page-bottom.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_template_bottom()
    {
        return "workflow/detail/detail.tpl";
    } // function

    /**
     * Retrieve the template name for the page-top.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_template_top()
    {
        return "workflow/detail/workflow.tpl";
    } // function

    /**
     * Calls the parent method for handling navigation-mode.
     *
     * @param  integer $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        parent::handle_navmode($p_navmode);
    } // function

    /**
     * Process.
     *
     * @return  mixed  Boolean false on failure. String with $this->get_name() on success.
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function process()
    {
        global $g_comp_database, $g_comp_session, $g_comp_template;

        // The ALMIGHTY workflow component.
        $l_workflow       = new isys_workflow;
        $l_dao_wf_type    = new isys_workflow_dao_type($g_comp_database);
        $l_dao_wf_action  = new isys_workflow_dao_action($g_comp_database);
        $l_dao_wf_dynamic = new isys_workflow_dao_dynamic($g_comp_database);

        $l_navbar = $this->get_module_request()
            ->get_navbar();

        $l_navbar->set_save_mode('formsubmit');

        // Build workflow status mapping - get with eg: $l_workflow_status->get(C__TASK__STATUS__INIT);
        $l_workflow_status = new isys_workflow_status_map();
        $l_workflow_status->build_map();

        // Initialize
        $l_gets     = $this->get_module_request()
            ->get_gets();
        $l_posts    = $this->get_module_request()
            ->get_posts();
        $l_template = $this->get_module_request()
            ->get_template();

        $l_workflow__id = $l_gets[C__WF__GET__ID];

        // Process userinput.
        $l_action_id = $l_posts["C__WF__ACTION"];

        if (is_numeric($l_action_id) && empty($l_posts[C__GET__NAVMODE]))
        {
            $l_action_handle = $l_workflow->save_action($l_action_id, $l_workflow__id);
        } // if

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE)
        {
            $this->save($l_workflow__id, $l_posts);
        } // if

        // Load workflow.
        $l_workflow->load($l_workflow__id);
        if (is_null($l_workflow->get_id()))
        {
            isys_component_template_infobox::instance()
                ->set_message(
                    "Workflow with ID: <strong>{$l_workflow__id}</strong> does not exist!",
                    null,
                    null,
                    null,
                    C__LOGBOOK__ALERT_LEVEL__3
                );

            return false;
        } // if

        $l_workflow_data = $l_workflow->get_data();

        // Get Actions.
        $l_workflow_actions = $l_workflow_data->get_actions();

        // Create a dynamic task from checklist entry.
        if (isset($_GET[C__WF__GET__TYPE]) && $_GET["date"])
        {
            if ($l_dao_wf_type->is_circular($_GET[C__WF__GET__TYPE]))
            {
                // Get a DB-Conform date.
                $l_current_startdate = date("Y-m-d", strtotime($_GET["date"]));

                $l_workflow__id = null;
                $l_workflow__id = $l_dao_wf_dynamic->create_task(
                    $l_gets[C__WF__GET__ID],
                    $l_workflow,
                    $l_workflow_data,
                    $l_current_startdate
                );

                // Reload workflow data.
                if (is_numeric($l_workflow__id))
                {
                    $l_gets[C__WF__GET__ID]   = $l_workflow__id;
                    $l_gets[C__WF__GET__TYPE] = null;
                    $l_gets["date"]           = null;
                    $l_link                   = $l_workflow->create_link($l_workflow__id);

                    $g_comp_template->assign("formAdditionalAction", "action=\"" . $l_link . "\"");
                    $g_comp_template->assign("query_string", $l_link);

                    $l_workflow->unload();
                    $l_workflow->load($l_workflow__id);

                    $l_workflow_data    = $l_workflow->get_data();
                    $l_workflow_actions = $l_workflow_data->get_actions();
                } // if
            } // if
        } // if

        // Assign the whole data and action objects to smarty.
        $l_template->assign('workflow_has_parent', ($l_workflow->get_parent() > 0))
            ->assign('workflow_list', $l_dao_wf_action->get_workflow_list($l_workflow__id))
            ->assignByRef("g_workflow_pack", $l_workflow)
            ->assignByRef("g_workflow_data", $l_workflow_data)
            ->assignByRef("g_workflow_actions", $l_workflow_actions);

        $l_workflow_type = $l_workflow->get_type();

        // Get status of the last processed action.
        $l_last_action = $l_workflow_actions[count($l_workflow_actions) - 1];

        if (method_exists($l_last_action, "get_status"))
        {
            $l_status_id = $l_last_action->get_status();
        } // if

        $l_param_0 = $l_workflow_actions[0]->get_parameter(0);
        $l_param_1 = $l_workflow_data->get_parameter_by_key($l_workflow_actions[0], "task__end_date");

        if ($l_status_id == 3)
        {
            if (is_object($l_workflow_actions[0]) && method_exists($l_param_0, "get_value") && method_exists($l_param_1, "get_value"))
            {
                $l_date_start = $l_param_0->get_value();
                $l_date_end   = $l_param_1->get_value();

                $l_date_current = date("Ymd");
                $l_date_start   = str_replace("-", "", $l_date_start);
                $l_date_end     = str_replace("-", "", $l_date_end);

                //change status to: startdate reached.
                if ($l_date_start >= $l_date_current)
                {
                    $l_status_id++;
                } // if

                if ($l_date_start == "00000000")
                {
                    $l_status_id = 4;
                } // if

                if ($l_date_end < $l_date_current && !empty($l_date_end) && $l_date_end != "00000000 00:00:00")
                {
                    // Change status to: outdated (enddate reached).
                    $l_status_id += 2;

                    // Disable cancel and complete buttons.
                    $g_comp_template->assign("g_cancelled", true);
                } // if
            } // if
        } // if

        $l_template->assign("g_current_status", $l_workflow_status->get($l_status_id));

        // Assign template specifications for the current workflow type.
        $l_workflow_template  = new isys_workflow_dao_template($g_comp_database);
        $l_template_parameter = $l_workflow_template->get_template_parameter($l_workflow_type);

        $l_template->assign("g_template_parameter", $l_template_parameter);

        // Get and assign occurrence data, process exception stuff.
        $l_template->assign("g_occurrence_data", $l_dao_wf_type->get_occurrence_data());

        $l_bin_exception = $l_workflow->get_exception();
        $l_exceptions    = $l_dao_wf_type->get_exceptions();

        $l_exception = [];
        $l_exception_num = [];

        for ($i = 0;$i <= 6;$i++)
        {
            if (1 << $i & $l_bin_exception)
            {
                $l_exception[] = $l_exceptions[$i];
                $l_exception_num[$i] = 'checked="checked"';
            } // if
        } // for

        $l_template
            ->assign("g_exceptions", implode(", ", $l_exception))
            ->assign('g_exception_check', $l_exception_num);

        // Contact stuff.
        $l_dao_reference = new isys_contact_dao_reference($g_comp_database);
        $l_dao_reference->load($l_workflow->get_initiator());
        /* ----------------------------------------------------------------------------------- */

        $l_data_items = $l_dao_reference->get_data_item_array();

        if (is_array($l_data_items) && count($l_data_items))
        {
            foreach ($l_data_items as $l_key => $l_value)
            {
                if ($l_value)
                {
                    $l_userdata = $l_dao_reference->get_data_item_info($l_key, C__CONTACT__DATA_ITEM__PERSON_INTERN)
                        ->get_row();
                    break;
                } // if
            } // foreach
        } // if

        // Activate edit button, if current user is creator of the workflow.
        $l_editmode = isys_glob_get_param("editMode");
        $l_navmode  = isys_glob_get_param("navMode");

        if ($l_userdata["isys_obj__id"] == $g_comp_session->get_user_id() || isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'WORKFLOW/' . C__WF__VIEW__DETAIL__GENERIC)
        )
        {
            if ($l_navmode != C__NAVMODE__EDIT && !$l_editmode)
            {
                if ($l_status_id != C__WORKFLOW__ACTION__TYPE__COMPLETE)
                {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                }
            }
            else
            {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            } //if
        } // if

        $l_workflow_dao_type = new isys_workflow_dao_type($g_comp_database);

        $this->tom_rule("OBJECT", json_encode($l_workflow->get_object_id()));

        $l_template->assign("g_initiator_name", $l_userdata["isys_obj__title"])
            ->assign("g_workflow_type", $l_workflow_dao_type->get_title_by_id($l_workflow_type))
            ->assign("g_workflow__id", $l_gets[C__WF__GET__ID])
            ->smarty_tom_add_rules("tom.content.bottom.content.workflows", $this->m_rules);

        return $this->get_name();
    } // function

    /**
     * Method for retrieving the View-List-ID.
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_list_id()
    {
        return C__WF__VIEW__LIST;
    } // function

    /**
     * Returns an instance of isys_workflow_dao_action.
     *
     * @return  isys_workflow_dao_action
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_dao()
    {
        global $g_comp_database;

        return new isys_workflow_dao_action($g_comp_database);
    } // function

    /**
     * Add tom rules.
     *
     * @param   string $p_field
     * @param   string $p_data
     * @param   string $p_fieldname
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function tom_rule($p_field, $p_data, $p_fieldname = "p_strValue")
    {
        $this->m_rules["C__WORKFLOW__" . $p_field][$p_fieldname] = $p_data;
    } // function

    /**
     * Purge a workflow.
     *
     * @param   array $p_posts
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function delete($p_posts)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        // ..its a purge!!
        $l_delete_ids = $p_posts["id"];

        $l_dao = new isys_workflow_dao_action($g_comp_database);

        if (is_array($l_delete_ids) && count($l_delete_ids))
        {
            foreach ($l_delete_ids as $l_key => $l_value)
            {
                $l_wf_childs       = $l_dao->get_workflows_clean(null, $l_value);
                $l_wf_single       = $l_dao->get_workflows_clean($l_value);
                $l_row             = $l_wf_single->get_row();
                $l_childs_existing = false;

                if ($l_wf_childs->num_rows() > 0)
                {
                    $l_message         = $g_comp_template_language_manager->get("LC__WORKFLOW__CHECK_CANNOT_DELETE");
                    $l_childs_existing = true;
                } // if

                $l_message = str_replace("%TITLE%", "<strong>" . $l_row["isys_workflow__title"] . "</strong>", $l_message);

                if ($l_childs_existing)
                {
                    isys_application::instance()->container['notify']->error($l_message);
                }
                else
                {
                    // kill the current workflow by id.
                    if ($l_dao->kill($l_value))
                    {
                        isys_component_template_infobox::instance()
                            ->set_message("Workflow deleted.", null, null, null, C__LOGBOOK__ALERT_LEVEL__0);
                    } // if
                } // if
            } // foreach
        } // if

        return true;
    } // function

    /**
     * Complete a workflow - Workflows can be completed when they have one of these states:
     *  C__TASK__STATUS__ASSIGNMENT
     *  C__TASK__STATUS__OPEN
     *  C__TASK__STATUS__END
     * and the user requesting this action is one of the assigned contacts.
     *
     * @param   array $p_arIDs Array of the Workflow-IDs to be completed
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function complete($p_arIDs)
    {
        global $g_comp_database, $g_comp_session;

        $l_session_data    = $g_comp_session->get_session_data($g_comp_session->get_session_id());
        $l_current_user_id = $l_session_data["isys_user_session__isys_obj__id"];

        if (is_array($p_arIDs) && count($p_arIDs))
        {
            foreach ($p_arIDs as $l_id)
            {
                $l_wf = new isys_workflow();
                $l_wf->load($l_id);

                $l_wfData    = $l_wf->get_data();
                $l_wfActions = $l_wfData->get_actions();

                $l_found = false;
                foreach ($l_wfActions as $l_action)
                {
                    if ($l_action->get_actiontype() == C__WORKFLOW__ACTION__TYPE__ASSIGN)
                    {
                        $l_dao_reference = new isys_contact_dao_reference($g_comp_database);
                        $l_dao_reference->load($l_action->getAssigned());

                        $l_data_items = $l_dao_reference->get_data_item_array();

                        if (is_array($l_data_items))
                        {
                            foreach ($l_data_items as $l_key => $l_value)
                            {
                                if ($l_key == $l_current_user_id)
                                {
                                    $l_found = true;
                                    break;
                                } // if
                            } // foreach
                        } // if
                    } // if

                    if ($l_found)
                    {
                        break;
                    } // if
                } // foreach

                if (!$l_found)
                {
                    return false;
                } // if

                $l_complete = new isys_workflow_action_complete();

                if ($l_wf->get_status() == C__TASK__STATUS__ASSIGNMENT)
                {
                    $l_accept = new isys_workflow_action_accept();
                    $l_accept->save($l_id, null);
                    $l_complete->save($l_id, false);
                } // if

                if ($l_wf->get_status() == C__TASK__STATUS__OPEN || $l_wf->get_status() == C__TASK__STATUS__END)
                {
                    $l_complete->save($l_id, false);
                } // if
            } // foreach
        } // if

        return true;
    } // function

    /**
     * Method for saving a workflow.
     *
     * @param   integer $p_id
     * @param   array   $p_posts
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function save($p_id, $p_posts)
    {
        $l_dao_wf = new isys_workflow_dao_action(isys_application::instance()->database);
        $l_dao_reference = new isys_contact_dao_reference(isys_application::instance()->database);

        if (strlen($p_posts['contact_to__HIDDEN']) > 0)
        {
            try
            {
                $l_contact_id = $l_dao_reference->ref_contact(
                    $p_posts['contact_to__HIDDEN'],
                    (empty($p_posts["assign_contact_id"])) ? null : $p_posts["assign_contact_id"]
                );
            }
            catch (isys_exception_contact $e)
            {
                isys_application::instance()->template->assign("g_error", $e->getMessage());
            } // try

            // Notify.
            $l_notification = new isys_workflow_action_notification();
            $l_notification->save(
                $p_id,
                null,
                C__EMAIL_TEMPLATE__TASK__NOTIFICATION,
                $l_contact_id
            );
        }
        else
        {
            $l_contact_id = $p_posts["assign_contact_id"];
        } // if

        // Saving parameters.
        $l_dao_action_param = new isys_workflow_dao_action(isys_application::instance()->database);

        if (!empty ($p_posts["new_action_id"]))
        {
            $l_params = $l_dao_action_param->get_action_parameters($p_posts["new_action_id"]);
            while ($l_row = $l_params->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY))
            {
                if ($p_posts[$l_row["isys_workflow_action_parameter__key"] . "__HIDDEN"])
                {
                    $l_new_value = date("Y-m-d H:i:s", strtotime($p_posts[$l_row["isys_workflow_action_parameter__key"] . "__HIDDEN"]));
                }
                else
                {
                    $l_new_value = $p_posts[$l_row["isys_workflow_action_parameter__key"]];
                } // if

                $l_dao_action_param->save_parameter(
                    $l_row["isys_workflow_action_parameter__id"],
                    $l_row["isys_workflow_template_parameter__type"],
                    $l_row["isys_workflow_action_parameter__key"],
                    $l_new_value,
                    $l_row["isys_workflow_action_parameter__isys_wf_template_parameter__id"]
                );
            } // while
        } // if

        $l_dao_wf->save_action($p_posts["assign_action_id"], $l_contact_id, C__WORKFLOW__ACTION__TYPE__ASSIGN);

        // Workflow exceptions (in case of "daily" occurrence).
        $l_exception = 0;

        if ($p_posts['f_occurrence'] == C__TASK__OCCURRENCE__DAILY && is_array($p_posts["f_workflow_exception"]))
        {
            foreach ($p_posts["f_workflow_exception"] as $l_key => $l_value)
            {
                $l_exception += (1 << $l_value);
            } // foreach
        } // if

        return $l_dao_wf->modify_workflow(
            $p_id,
            $p_posts["C__WF__TITLE"],
            json_decode($p_posts["C__WORKFLOW__OBJECT__HIDDEN"]),
            $p_posts['C__WF__CATEGORY'],
            $p_posts["f_occurrence"],
            $p_posts['C__WF__PARENT_WORKFLOW'],
            $l_exception
        );
    } // function

    /**
     * Processes the saving from the module if quick save is enabled.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_save()
    {
        $this->save($_POST[C__WF__GET__ID], $_POST);

        return $this->process();
    } // function
} // class