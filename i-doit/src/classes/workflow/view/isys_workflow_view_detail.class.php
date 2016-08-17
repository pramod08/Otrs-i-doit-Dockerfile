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
 *
 * @package    i-doit
 * @subpackage Workflow
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    0.9
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
abstract class isys_workflow_view_detail extends isys_workflow_view
{

    public function get_mandatory_parameters(&$l_gets)
    {
    }

    public function get_optional_parameters(&$l_gets)
    {
        $l_gets[C__WF__GET__ID]       = true;
        $l_gets[C__WF__GET__TEMPLATE] = true;
        $l_gets[C__WF__GET__TYPE]     = true;
    }

    public function get_template_bottom()
    {
        return "workflow/list.tpl";
    }

    public function get_template_destination()
    {
        return "g_workflow";
    }

    public function get_template_top()
    {
        return "";
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
        $l_navbar = $this->get_module_request()
            ->get_navbar();
        $l_posts  = $this->get_module_request()
            ->get_posts();
        $l_gets   = $this->get_module_request()
            ->get_gets();

        $l_navbar->set_save_mode('formsubmit');
        $l_navbar->set_active(true, C__NAVBAR_BUTTON__UP);
        //$l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
        //$l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        switch ($l_gets[C__CMDB__GET__VIEWMODE])
        {
            case C__WF__VIEW__DETAIL__GENERIC:
            case C__WF__VIEW__DETAIL__SELECTOR:
                $l_right_check = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW/' . C__WF__VIEW__LIST);
                break;
            case C__WF__VIEW__DETAIL__WF_TYPE:
            case C__WF__VIEW__DETAIL__TEMPLATE:
                $l_right_check = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE);
                break;
            case C__WF__VIEW__DETAIL__EMAIL_GUI:
                $l_right_check = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW/' . C__WF__VIEW__DETAIL__EMAIL_GUI);
                break;
            default:
                $l_right_check = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'WORKFLOW');
                break;
        }

        switch ($p_navmode)
        {
            case C__NAVMODE__NEW:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
            /* ----------------------------------------------------------------------- */
            case C__NAVMODE__EDIT:
                /* ----------------------------------------------------------------------- */

                $l_gets[C__CMDB__GET__VIEWMODE] = $this->get_id();
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            /* ----------------------------------------------------------------------- */
            case C__NAVMODE__SAVE:
                /* ----------------------------------------------------------------------- */

                /* ----------------------------------------------------------------------- */
                /* save that thing ------------------------------------------------------- */
                /* ----------------------------------------------------------------------- */
                $l_id = $this->save();

                unset($l_posts);
                unset($l_gets[C__CMDB__GET__EDITMODE]);

                if ($this->get_list_id())
                {
                    $l_gets[C__CMDB__GET__VIEWMODE] = $this->get_list_id();
                    $this->mod_reload($l_gets, []);
                }

                break;

            /* ----------------------------------------------------------------------- */
            case C__NAVMODE__UP:
                /* ----------------------------------------------------------------------- */

                /*go up*/
                unset($l_gets[C__TASK__GET__ID]);

                //$l_navbar->set_active(false, C__NAVBAR_BUTTON__UP);
                $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST;

                $this->mod_reload($l_gets, $l_posts);
                break;

            /* ----------------------------------------------------------------------- */
            default:
                /* ----------------------------------------------------------------------- */
                $l_navbar->set_active($l_right_check, C__NAVBAR_BUTTON__EDIT);
                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                //return false;
                break;

        }

        return true;
    }

    /**
     * @desc process!
     */
    public function process()
    {
        $this->handle_navmode($_POST[C__GET__NAVMODE]);
    }

    private function mod_reload($p_get, $p_post, $p_editmode = null)
    {
        unset($p_post[C__GET__NAVMODE]);

        if (!is_null($p_editmode)) $p_get[C__CMDB__GET__EDITMODE] = $p_editmode;
        else unset($p_get[C__CMDB__GET__EDITMODE]);

        $this->get_module_request()
            ->_internal_set_private("m_get", $p_get);
        $this->get_module_request()
            ->_internal_set_private("m_post", $p_post);

        $this->trigger_module_reload();
    }

    /**
     * @param isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}

?>