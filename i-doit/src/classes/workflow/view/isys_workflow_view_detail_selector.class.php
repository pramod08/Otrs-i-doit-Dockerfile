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
class isys_workflow_view_detail_selector extends isys_workflow_view_detail
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
        return C__WF__VIEW__DETAIL__SELECTOR;
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
        return "i-manageIT::selector";
    } // function

    /**
     * Calls the parent method for setting optional parameters via reference.
     *
     * @param   array & $l_gets
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
        return "workflow/detail/selector.tpl";
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
     * @param unknown_type $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        parent::handle_navmode($p_navmode);
    } // function

    /**
     * Process method.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function process()
    {
        parent::process();

        // Initialize.
        $l_workflow_list = $l_types = [];
        $l_gets          = $this->get_module_request()
            ->get_gets();
        $l_template      = $this->get_module_request()
            ->get_template();

        // Get current user id.
        $l_current_user_id = isys_application::instance()->session->get_user_id();

        // Assign current user data.
        $l_user_row = (
        new isys_cmdb_dao_category_s_person_master(
            $this->get_module_request()
                ->get_database()
        )
        )->get_data(null, $l_current_user_id)
            ->get_row();

        // Get DAOs.
        $l_dao_workflow = new isys_workflow_dao_type(
            $this->get_module_request()
                ->get_database()
        );

        // Get and assign the given workflow types.

        $l_workflow_types = $l_dao_workflow->get_workflow_types();
        while ($l_row = $l_workflow_types->get_row())
        {
            $l_types[$l_row["isys_workflow_type__id"]] = $l_row["isys_workflow_type__title"];
        } // while

        $l_workflows = $l_dao_workflow->get_workflows();

        if (count($l_workflows))
        {
            while ($l_row = $l_workflows->get_row())
            {
                $l_workflow_list[$l_row["isys_workflow__id"]] = $l_row["isys_workflow__title"];
            } // while
        } // if

        $l_template->assign("g_user_name", $l_user_row["isys_cats_person_list__first_name"] . " " . $l_user_row["isys_cats_person_list__last_name"])
            ->assign("g_current_user__id", $l_current_user_id)
            ->assign("workflow_types", serialize($l_types))
            ->assign("g_url", (new isys_popup_workflow)->handle_smarty_include($l_template, null, null, true))
            ->assign("g_workflow_type", $l_gets[C__WF__GET__TYPE])
            ->assign("workflow_list", $l_workflow_list)
            ->assign("formAdditionalAction", "onSubmit=\"return submit_workflow();\" action=\"?{$_SERVER['QUERY_STRING']}\"")
            ->assign("query_string", $_SERVER['QUERY_STRING']);

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
     * Save method.
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function save()
    {
        global $g_comp_database, $g_comp_template_language_manager;

        $l_posts = $this->get_module_request()
            ->get_posts();

        $l_contact_dao = new isys_contact_dao_reference($g_comp_database);

        try
        {
            $l_user_id       = $l_posts["C__WF__AUTHOR"];
            $l_action_id     = $l_posts["C__WF__ACTION"];
            $l_workflow_type = $l_posts["g_workflow_type"];
            $l_object_arr    = isys_format_json::decode($l_posts["f_object__HIDDEN"]);

            if (empty($l_workflow_type)) throw new isys_exception_cmdb(_L('LC_WORKFLOW__NO_WORKFLOW_TYPE_SELECTED'));

            $l_dao      = $this->get_dao();
            $l_workflow = new isys_workflow();

            // Handle contacts.
            $l_contact_id = $l_contact_dao->ref_contact($l_posts["contact_to__HIDDEN"]);

            // Handle request.
            $l_request = new isys_workflow_request($l_posts, $l_user_id, $l_contact_id);
            $l_request->set_workflow_type($l_workflow_type);

            if ($l_request->format_request())
            {
                // Insert workflow and handle posted action id.
                $l_workflow_id = $l_workflow->insert($l_request, $l_action_id);
            }
            else $l_workflow_id = null;

            $l_workflow_data = $l_dao->get_workflows($l_workflow_id)
                ->get_row();

            // Logbook Entry for selected object.
            if (is_array($l_object_arr))
            {
                $l_default_dao = isys_cmdb_dao::instance($g_comp_database);
                $l_logbook     = isys_component_dao_logbook::instance($g_comp_database);
                foreach ($l_object_arr AS $l_object_id)
                {

                    $l_row           = $l_default_dao->get_type_by_object_id($l_object_id)
                        ->get_row();
                    $l_strObjectType = $g_comp_template_language_manager->get($l_row['isys_obj_type__title']);

                    $l_row         = $l_default_dao->get_catg_by_table_name('isys_catg_workflow_list')
                        ->get_row();
                    $l_strCategory = $g_comp_template_language_manager->get($l_row['isysgui_catg__title']);

                    $l_logbook->set_entry(
                        'C__LOGBOOK_ENTRY__WORKFLOW_CREATED',
                        null,
                        null,
                        C__LOGBOOK__ALERT_LEVEL__0,
                        $l_object_id,
                        $l_workflow_data['isys_workflow__title'],
                        $l_workflow_data['isys_workflow_type__title'],
                        null,
                        null,
                        null,
                        $_POST['LogbookCommentary']
                    );
                }
            }

            return $l_workflow_id;
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        }
    } // function

    /**
     * Method for returning a new workorder-view instance.
     *
     * @return  isys_task_view_detail_workorder
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function &get_detail_view()
    {
        return new isys_task_view_detail_workorder($this->m_modreq);
    } // function

    /**
     * Constructor.
     *
     * @param   isys_module_request $p_request
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class
?>