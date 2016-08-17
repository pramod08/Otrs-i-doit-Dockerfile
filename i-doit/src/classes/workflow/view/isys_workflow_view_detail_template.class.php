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
class isys_workflow_view_detail_template extends isys_workflow_view_detail
{
    /**
     * Tom rules.
     *
     * @var array
     */
    private $m_rules;

    /**
     * Method for returning the detail-template ID.
     *
     * @return  integer
     * @see     isys_cmdb_view::get_id()
     */
    public function get_id()
    {
        return C__WF__VIEW__DETAIL__TEMPLATE;
    } // function

    /**
     * Method for retrieving the mandatory parameters via reference.
     *
     * @param  array $l_gets
     *
     * @see    isys_workflow_view_detail::get_mandatory_parameters()
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    } // function

    /**
     * Method for retrieving the view name.
     *
     * @return  string
     * @see     isys_cmdb_view::get_name()
     */
    public function get_name()
    {
        return "i-manageIT::template";
    } // function

    /**
     * Method for retrieving the optional parameters via reference.
     *
     * @param  array $l_gets
     *
     * @see    isys_workflow_view_detail::get_optional_parameters()
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);
    } // function

    /**
     * Method for retrieving the path and name of the bottom template.
     *
     * @return  string
     * @see     isys_workflow_view_detail::get_template_bottom()
     */
    public function get_template_bottom()
    {
        return "workflow/detail/template.tpl";
    } // function

    /**
     * Method for retrieving the path and name of the top template.
     *
     * @return  string
     * @see     isys_workflow_view_detail::get_template_top()
     */
    public function get_template_top()
    {
        return "workflow/detail/workflow.tpl";
    } // function

    /**
     * Method for handling the navmode.
     *
     * @see isys_workflow_view_detail::handle_navmode()
     */
    public function handle_navmode($p_navmode)
    {
        parent::handle_navmode($p_navmode);
    } // function

    /**
     * Process.
     *
     * @return  string
     */
    public function process()
    {
        global $g_comp_database, $g_comp_session, $g_comp_template, $g_comp_template_language_manager, $g_dirs;

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_navbar->set_save_mode('formsubmit');

        $l_lm       = $g_comp_template_language_manager;
        $l_template = $g_comp_template;

        // Initialize.
        $l_gets     = $this->get_module_request()
            ->get_gets();
        $l_posts    = $this->get_module_request()
            ->get_posts();
        $l_template = $this->get_module_request()
            ->get_template();

        $this->handle_navmode($l_posts[C__GET__NAVMODE]);

        $l_template_parameter__id = $l_gets[C__WF__GET__TEMPLATE];

        // Existing parameters and their user friendly title.
        $l_parameters                                  = [];
        $l_parameters[C__WF__PARAMETER_TYPE__STRING]   = "Text";
        $l_parameters[C__WF__PARAMETER_TYPE__INT]      = "Numeric";
        $l_parameters[C__WF__PARAMETER_TYPE__DATETIME] = "Date (Calendar)";
        $l_parameters[C__WF__PARAMETER_TYPE__TEXT]     = "Fulltext (Multiline)";
        $l_parameters[C__WF__PARAMETER_TYPE__YES_NO]   = $l_lm->get(LC__UNIVERSAL__YES) . " / " . $l_lm->get(LC__UNIVERSAL__NO);

        // Get parameter data if the id is numeric.
        if (is_numeric($l_template_parameter__id))
        {
            $l_tpl_dao = new isys_workflow_dao_template($g_comp_database);
            $l_data    = $l_tpl_dao->get_template_parameter_by_id($l_template_parameter__id)
                ->get_row();

            $g_comp_template->assign("g_data", $l_data);
        } // if

        // Get template parameters to output them as a dialog.
        $l_parameter_type = '<select name="f_type" class="input input-small">';

        foreach ($l_parameters as $l_key => $l_value)
        {
            $l_parameter_type .= "<option value=\"" . $l_key . "\"";

            if ($l_data["isys_workflow_template_parameter__type"] == $l_key)
            {
                $l_parameter_type .= " selected=\"selected\"";
                $l_current = $l_value;
            } // if

            $l_parameter_type .= ">" . $l_value . "</option>";
        } // foreach
        $l_parameter_type .= "</select>";

        // Display parameter as text or selectable combobox, depending on editmode.
        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT || $l_posts[C__GET__NAVMODE] == C__NAVMODE__NEW || $l_gets[C__CMDB__GET__EDITMODE] == true)
        {
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
            //$l_navbar->set_active(false, C__NAVBAR_BUTTON__CANCEL);
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__NEW);
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT);
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__DELETE);

            $g_comp_template->assign("g_parameter_types", $l_parameter_type);
        }
        else
        {
            $g_comp_template->assign("g_parameter_types", "<strong>" . $l_current . "</strong>");
        } // if

        return $this->get_name();
    } // function

    /**
     * Method for returning the list-template ID.
     *
     * @return  integer
     */
    public function get_list_id()
    {
        return C__WF__VIEW__LIST_TEMPLATE;
    } // function

    /**
     * Delete a template parameter.
     *
     * @param   array $p_posts
     *
     * @return  boolean
     */
    public function delete($p_posts)
    {
        global $g_comp_database;

        $l_delete_ids = $p_posts["id"];

        $l_dao = new isys_workflow_dao_template($g_comp_database);

        if (is_array($l_delete_ids) && count($l_delete_ids))
        {
            foreach ($l_delete_ids as $l_key => $l_value)
            {
                if (is_numeric($l_value))
                {
                    $l_return = $l_dao->delete_template_parameter($l_value);

                    if ($l_return == -1)
                    {
                        isys_component_template_infobox::instance()
                            ->set_message(
                                "[Workflows] <b>Could not delete this mandatory template parameter!</b>",
                                null,
                                null,
                                null,
                                C__LOGBOOK__ALERT_LEVEL__3
                            );
                    } // if
                } // if
            } // foreach
        } // if

        return true;
    } // function

    /**
     * Returns dao for workorders.
     *
     * @return  object isys_task_dao_workorder
     */
    public function get_dao()
    {
        global $g_comp_database;

        return new isys_workflow_dao_template($g_comp_database);
    } // function

    /**
     * Save method.
     *
     * @return  boolean
     */
    public function save()
    {
        global $g_comp_database;

        $l_gets  = $this->get_module_request()
            ->get_gets();
        $l_posts = $this->get_module_request()
            ->get_posts();

        $l_parameter_id = $l_gets[C__WF__GET__TEMPLATE];

        // Get parameters.
        $l_title         = $l_posts["f_title"];
        $l_workflow_type = $l_posts["f_workflow_type"];
        $l_key           = $l_posts["f_key"];
        $l_sort          = $l_posts["f_sort"];
        $l_type          = $l_posts["f_type"];
        $l_check         = ($l_posts["f_check"] == "1") ? 1 : 0;

        $l_dao = new isys_workflow_dao_template($g_comp_database);

        // Save or create the edited template parameter.
        if (is_numeric($l_parameter_id))
        {
            $l_ret = $l_dao->save_template_parameter(
                $l_parameter_id,
                $l_title,
                $l_workflow_type,
                $l_type,
                $l_key,
                $l_sort,
                $l_check
            );
        }
        else
        {
            $l_ret = $l_dao->create_template_parameter(
                $l_title,
                $l_workflow_type,
                $l_type,
                $l_key,
                $l_sort,
                $l_check
            );

            $l_dao->bind(
                $l_workflow_type,
                $l_ret
            );
        } // if

        return $l_ret;
    } // function

    /**
     * Method for retrieving a new instance of the view-detail template.
     *
     * @return  isys_task_view_detail_template
     */
    public function &get_detail_view()
    {
        return new isys_task_view_detail_template($this->m_modreq);
    } // function

    /**
     * Constructor, calls parent constructor.
     *
     * @param  isys_module_request $p_request
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class
?>