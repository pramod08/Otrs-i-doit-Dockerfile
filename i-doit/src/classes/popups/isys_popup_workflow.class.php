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
 * Workflow Popup for creating workflows with an ajax request or popup
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_popup_workflow extends isys_popup_browser
{
    /**
     * @desc tom rules
     * @var array
     */
    private $m_rules;

    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params, $p_display_magnifier = null, $p_url_only = false)
    {
        global $g_dirs;
        global $g_config;
        global $g_comp_database;
        global $g_comp_template_language_manager;

        $l_url            = "";
        $l_strOut         = "";
        $l_strHiddenField = "";

        $l_url = $g_config["startpage"] . "?mod=cmdb" . "&popup=workflow";

        $this->set_config("width", 950);
        $this->set_config("height", 720);

        $p_params["p_bReadonly"] = "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_get_param("editMode") == C__EDITMODE__ON)
        {
            $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $p_params);
            $l_strOut .= "<a " . "href=\"javascript:void(0);\" " . "title=\"" . $g_comp_template_language_manager->{LC_WORKFLOW__CREATION} . "\"  " . "style=\"margin-left:5px;\" " . "onClick=\"" . $this->process(
                    $l_url,
                    true
                ) . ";\"" . ">";

            $l_strOut .= "</a>";

        }
        else
        {
            $l_strOut .= $l_objPlugin->navigation_view($p_tplclass, $p_params);
        }

        /*return onclick, or full html*/
        if ($p_url_only)
        {
            return $l_url;
        }
        else
        {
            return $l_strOut;
        }
    }

    /**
     * @desc
     *
     * @param isys_module_request $p_modreq
     * @param unknown_type        $p_module_request
     */
    public function &handle_ajax_request(isys_module_request $p_modreq, $p_module_request = false)
    {
        $this->handle($p_modreq, $p_module_request);
    }

    /**
     * @desc ...
     *
     * @return isys_component_template&
     *
     * @param isys_module_request $p_modreq
     *
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $this->handle($p_modreq, false);
    }

    public function handle(isys_module_request $p_modreq, $p_module_request)
    {
        global $g_comp_database;
        global $g_dirs;
        global $g_config;
        global $g_comp_template_language_manager;
        global $g_comp_session;
        $l_tlm = $g_comp_template_language_manager;

        /* Unpack module request */
        $l_gets     = $p_modreq->get_gets();
        $l_posts    = $p_modreq->get_posts();
        $l_tplpopup = $p_modreq->get_template();

        /*get dao for template handler*/
        $l_workflow_template = new isys_workflow_dao_template($g_comp_database);
        $l_workflow_type_dao = new isys_workflow_dao_type($g_comp_database);

        switch ($l_gets[C__GET__AJAX_REQUEST])
        {
            default:
                break;
        }

        /**
         * @desc get data for possible yes / no field
         */
        $l_ar_yes_no = [
            1 => $l_tlm->get("LC__UNIVERSAL__YES"),
            0 => $l_tlm->get("LC__UNIVERSAL__NO")
        ];
        $l_tplpopup->assign("g_ar_yes_no", $l_ar_yes_no);

        /**
         * @desc get and assign data for occurence dialog if needed
         */
        $l_wf_tmp = $l_workflow_type_dao->get_workflow_types($l_gets[C__WF__GET__TYPE]);

        if ($l_wf_tmp->num_rows() > 0)
        {
            $l_workflow_type = $l_wf_tmp->get_row();

            $l_tplpopup->assign("g_occurrence", $l_workflow_type["isys_workflow_type__occurrence"]);

            $l_dao_wf = new isys_workflow_dao($g_comp_database);
            $l_tplpopup->assign("g_occurrence_data", $l_dao_wf->get_occurrence_data());
        }
        /**
         * @desc assign template parameter
         */
        $l_template_parameter = $l_workflow_template->get_template_parameter($l_gets[C__WF__GET__TYPE]);
        $l_tplpopup->assign("g_template_parameter", $l_template_parameter);

        $l_tplpopup->assign("g_workflow_type", $l_gets[C__WF__GET__TYPE]);

        if ($p_module_request)
        {
            return $l_tplpopup;
        }
        else
        {
            return $l_tplpopup->display("workflow/detail/generic.tpl");;
        }
    }
}

?>