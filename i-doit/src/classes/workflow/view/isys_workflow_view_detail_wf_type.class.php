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
 * @version   1.0 Thu Jun 22 14:38:38 CEST 2006 14:38:38
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_workflow_view_detail_wf_type extends isys_workflow_view_detail
{

    /**
     * @desc tom rules
     * @var array
     */
    private $m_rules;

    /* id */
    public function get_id()
    {
        return C__WF__VIEW__DETAIL__WF_TYPE;
    }

    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    /* name */

    public function get_name()
    {
        return "i-manageIT::workflow.type";
    }

    public function get_optional_parameters(&$l_gets)
    {
        $l_gets[C__WF__GET__TYPE] = true;

        parent::get_optional_parameters($l_gets);
    }

    public function get_template_bottom()
    {
        return "workflow/detail/wf_type.tpl";
    }

    public function get_template_top()
    {
        return "workflow/detail/workflow.tpl";
    }

    public function handle_navmode($p_navmode)
    {
        parent::handle_navmode($p_navmode);
    }

    /**
     * @desc process
     * @return string
     */
    public function process()
    {
        global $g_comp_database;
        global $g_comp_session;
        global $g_comp_template;
        global $g_comp_template_language_manager;
        global $g_dirs;

        parent::process();

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_navbar->set_save_mode('formsubmit');

        $l_lm       = $g_comp_template_language_manager;
        $l_template = $g_comp_template;

        /**
         * @desc init
         */
        $l_gets     = $this->get_module_request()
            ->get_gets();
        $l_template = $this->get_module_request()
            ->get_template();

        $l_workflow_type__id = $l_gets[C__WF__GET__TYPE];

        if (is_numeric($l_workflow_type__id))
        {
            /**
             * @desc get data of the current workflow type
             */
            $l_wf_dao = new isys_workflow_dao_type($g_comp_database);
            $l_data   = $l_wf_dao->get_workflow_types($l_workflow_type__id)
                ->get_row();
            $g_comp_template->assign("g_data", $l_data);

            /**
             * @desc get currently assigned template parameters of this workflow type
             */
            $l_comp_list = new isys_component_list();
            $l_list      = new isys_workflow_dao_list_template($g_comp_database);

            $l_listres = $l_list->get_result($l_workflow_type__id);
            $l_comp_list->set_data(
                null,
                $l_listres
            );

            $l_comp_list->set_listdao($l_list);

            $l_comp_list->config(
                $l_list->get_fields(),
                $l_list->get_row_link()
            );

            if ($l_comp_list->createTempTable())
            {
                $g_comp_template->assign("g_template_parameter", $l_comp_list->getTempTableHtml());
            }
        }

        $l_occurrence = [
            0 => $l_lm->get(LC__UNIVERSAL__NO),
            1 => $l_lm->get(LC__UNIVERSAL__YES)
        ];
        $l_template->assign("g_occurrence", $l_occurrence);

        return $this->get_name();
    }

    public function get_list_id()
    {
        return C__WF__VIEW__LIST_WF_TYPE;
    }

    /**
     * @desc returns dao for workorders
     * @return object isys_task_dao_workorder
     */
    public function get_dao()
    {
        global $g_comp_database;

        return new isys_workflow_dao_template($g_comp_database);
    }

    public function recycle($p_posts)
    {
        global $g_comp_database;

        /* -------------------------------------------------------------------------- */
        $l_delete_ids = $p_posts["id"];
        /* -------------------------------------------------------------------------- */
        $l_dao = new isys_workflow_dao_type($g_comp_database);
        /* -------------------------------------------------------------------------- */

        switch ($p_posts['cRecStatus'])
        {
            case C__RECORD_STATUS__DELETED:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;
            default:
                $l_status = C__RECORD_STATUS__NORMAL;
                break;
        }

        if (is_array($l_delete_ids) && count($l_delete_ids))
        {
            foreach ($l_delete_ids as $l_key => $l_value)
            {
                if (is_numeric($l_value))
                {
                    $l_dao->set_status($l_value, $l_status);
                    // change status of the current workflow type
                }
            }
        }

        return true;
    }

    public function delete($p_posts)
    {
        global $g_comp_database;

        /* -------------------------------------------------------------------------- */
        $l_delete_ids = $p_posts["id"];
        /* -------------------------------------------------------------------------- */
        $l_dao = new isys_workflow_dao_type($g_comp_database);
        /* -------------------------------------------------------------------------- */

        switch ($p_posts['cRecStatus'])
        {
            case C__RECORD_STATUS__ARCHIVED:
                $l_status = C__RECORD_STATUS__DELETED;
                break;
            case C__RECORD_STATUS__DELETED:
                $l_status = C__RECORD_STATUS__PURGE;
                break;
            default:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;
        }

        if (is_array($l_delete_ids) && count($l_delete_ids))
        {
            foreach ($l_delete_ids as $l_key => $l_value)
            {
                if (is_numeric($l_value))
                {
                    $l_dao->set_status($l_value, $l_status);
                    // change status of the current workflow type
                }
            }
        }

        return true;
    }

    /**
     * @desc creates a new type
     *
     * @return unknown
     */
    public function save()
    {
        global $g_comp_database;

        $l_gets  = $this->get_module_request()
            ->get_gets();
        $l_posts = $this->get_module_request()
            ->get_posts();

        $l_title      = $l_posts["f_title"];
        $l_const      = $l_posts["f_const"];
        $l_occurrence = $l_posts["f_occurrence"];

        $l_dao = new isys_workflow_dao_type($g_comp_database);

        if ($l_gets[C__WF__GET__TYPE])
        {
            $l_ret = $l_dao->save_workflow_type(
                $l_gets[C__WF__GET__TYPE],
                $l_title,
                $l_const,
                $l_occurrence
            );
        }
        else
        {
            $l_ret = $l_dao->create_workflow_type(
                $l_title,
                $l_const,
                $l_occurrence
            );
        }

        return $l_ret;
    }

    public function &get_detail_view()
    {
        return new isys_workflow_view_detail_wf_type($this->m_modreq);
    }

    /**
     * @desc constructor
     *
     * @param isys_module_request $p_request
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}

?>