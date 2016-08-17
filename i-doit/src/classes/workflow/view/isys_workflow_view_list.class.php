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
 * @version   Dennis Blümer <dbluemer@i-doit.org>
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
abstract class isys_workflow_view_list extends isys_workflow_view
{

    /**
     * @var isys_component_list
     */
    //protected $m_comp_list;    private $m_order_dir;
    private $m_order_field;

    abstract public function list_init();

    abstract public function list_process();

    /**
     * Gets database field which should be ordered
     *
     * @return mixed
     */
    public function get_order_field()
    {
        return $this->m_order_field;
    }

    /**
     * Gets the direction of the ordering
     *
     * @return mixed
     */
    public function get_order_dir()
    {
        return $this->m_order_dir;
    }

    /**
     * Processes the saving from the module if quick save is enabled
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_save()
    {
        return $this->process();
    }

    /**
     * @desc
     * @return string
     */
    public function get_template_bottom()
    {
        return "workflow/list.tpl";
    }

    /**
     * @desc
     * @return string
     */
    public function get_template_destination()
    {
        return "g_workflow";
    }

    /**
     * @desc
     * @return string
     */
    public function get_template_top()
    {
        return "workflow/list_top.tpl";
    }

    /**
     * @desc handles the navmode
     *
     * @param int $p_navmode
     *
     * @return boolean
     */
    public function handle_navmode($p_navmode)
    {
        $l_gets  = $this->get_module_request()
            ->get_gets();
        $l_posts = $this->get_module_request()
            ->get_posts();

        try
        {
            switch ($p_navmode)
            {
                case C__NAVMODE__NEW:

                    $l_target_class = $this->get_detail_view();

                    if ($l_target_class->get_id() == C__WF__VIEW__DETAIL__GENERIC)
                    {
                        $l_target_class = new isys_workflow_view_detail_selector($this->m_modreq);
                    }

                    $l_gets[C__CMDB__GET__VIEWMODE] = $l_target_class->get_id();
                    $l_gets[C__CMDB__GET__TREEMODE] = C__WF__VIEW__TREE;
                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                    unset($l_posts[C__GET__NAVMODE]);

                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);
                    $this->get_module_request()
                        ->_internal_set_private("m_post", $l_posts);
                    $this->readapt_form_action();

                    $this->trigger_module_reload();

                    break;
                case C__NAVMODE__EDIT:

                    break;
                case C__NAVMODE__SAVE:

                    $l_target_class = new isys_workflow_view_detail_selector($this->m_modreq);
                    $l_target_class->save();

                    break;
                case C__NAVMODE__ARCHIVE:
                case C__NAVMODE__PURGE:
                case C__NAVMODE__DELETE:
                    /* -------------------------------------------------------------------- */
                    /* Delete ------------------------------------------------------------- */
                    /* -------------------------------------------------------------------- */
                    $l_target_class = $this->get_detail_view();

                    if (method_exists($l_target_class, "delete"))
                    {
                        $l_target_class->delete($l_posts);
                    }
                    /* -------------------------------------------------------------------- */

                    unset($l_posts[C__GET__NAVMODE]);

                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);
                    $this->get_module_request()
                        ->_internal_set_private("m_post", $l_posts);
                    $this->readapt_form_action();

                    $this->trigger_module_reload();
                    break;
                case C__NAVMODE__RECYCLE:

                    $l_target_class = $this->get_detail_view();

                    if (method_exists($l_target_class, "recycle"))
                    {
                        $l_target_class->recycle($l_posts);
                    }

                    unset($l_posts[C__GET__NAVMODE]);

                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);
                    $this->get_module_request()
                        ->_internal_set_private("m_post", $l_posts);
                    $this->readapt_form_action();

                    $this->trigger_module_reload();

                    break;
                case C__NAVMODE__COMPLETE:
                    /* -------------------------------------------------------------------- */
                    /* Complete ----------------------------------------------------------- */
                    /* -------------------------------------------------------------------- */
                    $l_target_class = $this->get_detail_view();

                    if (method_exists($l_target_class, "complete"))
                    {
                        if (!isset($l_posts["id"]) || !is_array($l_posts["id"]) || count($l_posts["id"]) === 0 || !$l_target_class->complete($l_posts["id"]))
                        {
                            isys_application::instance()->container['notify']->warning(_L("LC__WORKFLOW__ACTION__COMPLETE_ERROR"), ['sticky' => true]);
                        } // if
                    } // if

                    unset($l_posts[C__GET__NAVMODE]);

                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);
                    $this->get_module_request()
                        ->_internal_set_private("m_post", $l_posts);
                    $this->readapt_form_action();

                    $this->trigger_module_reload();

                    break;
                default:
                    break;
            }
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        }

        return true;
    }

    /**
     * @desc process list view
     * @return true
     */
    public function process()
    {
        global $g_comp_template_language_manager;

        $l_tlm = $g_comp_template_language_manager;

        $l_modreq = $this->get_module_request();
        $l_posts  = $l_modreq->get_posts();
        $l_gets   = $l_modreq->get_gets();

        switch ($l_gets[C__CMDB__GET__VIEWMODE])
        {
            case C__WF__VIEW__LIST:
                isys_auth_system::instance()
                    ->check(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST);
                $l_check_edit   = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW/' . C__WF__VIEW__LIST);
                $l_check_delete = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::DELETE, 'WORKFLOW/' . C__WF__VIEW__LIST);
                break;
            case C__WF__VIEW__LIST_WF_TYPE:
            case C__WF__VIEW__LIST_TEMPLATE:
                isys_auth_system::instance()
                    ->check(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE);
                $l_check_edit   = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE);
                $l_check_delete = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::DELETE, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE);
                break;
        }

        /**
         * @desc prepare displaying of the list
         */
        if ($this->list_init())
        {
            try
            {

                $this->handle_navmode(
                    $l_posts[C__GET__NAVMODE]
                );

            }
            catch (isys_exception_cmdb $e)
            {
                isys_application::instance()->container['notify']->error($e->getMessage());
            }

            try
            {
                $l_navbar = $this->get_module_request()
                    ->get_navbar();
                $l_tpl    = $this->get_module_request()
                    ->get_template();

                if (!empty($l_posts['sort']))
                {
                    $this->m_order_field = $l_posts['sort'];
                    $this->m_order_dir   = $l_posts['dir'];
                }

                $l_list_object = $this->list_process();
                if (is_object($l_list_object))
                {
                    $l_list_object->set_rec_status(null);

                    $l_tpl->assign("bNavbarFilter", "1");

                    $l_status = [
                        C__TASK__STATUS__INIT       => $l_tlm->get("LC__TASK__STATUS__INIT__SHORT"),
                        C__TASK__STATUS__ASSIGNMENT => $l_tlm->get("LC__TASK__STATUS__ASSIGNMENT__SHORT"),
                        C__TASK__STATUS__OPEN       => $l_tlm->get("LC__TASK__STATUS__OPEN__SHORT"),
                        C__TASK__STATUS__END        => $l_tlm->get("LC__TASK__STATUS__END__SHORT"),
                        C__TASK__STATUS__CLOSE      => $l_tlm->get("LC__TASK__STATUS__CLOSE__SHORT"),
                        C__TASK__STATUS__CANCEL     => $l_tlm->get("LC__TASK__STATUS__CANCEL__SHORT")
                    ];

                    if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST || $_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST_WF_TYPE)
                    {

                        $l_rec_status = $l_list_object->get_rec_status();

                        /* Activate recycle button if status = deleted */
                        if (($l_rec_status == C__RECORD_STATUS__DELETED || $l_rec_status == C__RECORD_STATUS__ARCHIVED) && $_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST_WF_TYPE && $l_check_delete)
                        {
                            $l_navbar->set_active(true, C__NAVBAR_BUTTON__RECYCLE);
                        }

                        if (!isset($_POST["filter"])) $_POST["filter"] = $_SESSION["filter"];
                        else if (isset($_POST["filter"])) $_SESSION["filter"] = $_POST["filter"];
                        else
                            unset($_SESSION["filter"]);

                        $l_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=0");
                        $l_tpl->smarty_tom_add_rule("tom.content.top.filter.p_strValue=" . $_POST["filter"]);
                        $l_tpl->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=0");
                        $l_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $l_rec_status);

                        if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST)
                        {
                            /* Assign specific workflow status */
                            $l_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($l_status));

                            /* Assign other stuff */
                            $l_navbar->set_visible(true, C__NAVBAR_BUTTON__COMPLETE);
                            $l_navbar->set_js_onclick(
                                'javascript:if(submit_workflow()){document.isys_form.navMode.value=\'10\';$(\'isys_form\').submit();}',
                                C__NAVBAR_BUTTON__SAVE
                            );

                            if ($l_rec_status == C__TASK__STATUS__ASSIGNMENT || $l_rec_status == C__TASK__STATUS__OPEN || $l_rec_status == C__TASK__STATUS__END)
                            {
                                $l_navbar->set_active(true, C__NAVBAR_BUTTON__COMPLETE);
                            }
                        }

                    }
                    else
                    {
                        $l_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");
                    }

                    /**
                     * @desc get result for the current workflow type
                     *            note: if workflow type = 0, show all workflows
                     */
                    $l_list_result     = $l_list_object->get_result($l_gets[C__WF__GET__TYPE]);
                    $this->m_comp_list = new isys_component_list(null, $l_list_result, $l_list_object);

                    $this->m_comp_list->config($l_list_object->get_fields(), $l_list_object->get_row_link(), $this->get_id_field(), true);

                    if ($this->m_comp_list->createTempTable())
                    {

                        $l_navbar->set_active($l_check_edit, C__NAVBAR_BUTTON__NEW);
                        $l_navbar->set_visible(true, C__NAVBAR_BUTTON__NEW);
                        switch ($l_rec_status)
                        {
                            case C__RECORD_STATUS__ARCHIVED:
                                $l_navbar->set_active($l_check_delete, C__NAVBAR_BUTTON__DELETE);
                                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__DELETE);
                                break;
                            case C__RECORD_STATUS__DELETED:
                                // We can not delete workflow types because we do not delete the workflows
                                break;
                            default:
                                if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST_TEMPLATE || $_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__DETAIL__TEMPLATE)
                                {
                                    $l_navbar->set_active($l_check_delete, C__NAVBAR_BUTTON__DELETE);
                                    $l_navbar->set_active($l_check_edit, C__NAVBAR_BUTTON__EDIT);
                                    $l_navbar->set_visible(true, C__NAVBAR_BUTTON__DELETE);
                                    $l_navbar->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                                }
                                else
                                {
                                    $l_navbar->set_active($l_check_delete, C__NAVBAR_BUTTON__ARCHIVE);
                                    $l_navbar->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE);
                                }
                                break;
                        }

                        return $this->m_comp_list->getTempTableHtml();
                    }
                }
            }
            catch (isys_exception_cmdb $e)
            {
                isys_application::instance()->container['notify']->error($e->getMessage());
            }
        }

        return null;
    }

    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}

?>