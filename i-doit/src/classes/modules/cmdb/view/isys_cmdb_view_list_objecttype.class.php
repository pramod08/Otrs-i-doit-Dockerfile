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
 * CMDB List view for object types
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_list_objecttype extends isys_cmdb_view_list
{
    /**
     * @var  isys_cmdb_dao_list_objecttype
     */
    private $m_dao_list;

    /**
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__LIST_OBJECTTYPE;
    } // function

    /**
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * @return  string
     */
    public function get_name()
    {
        return "LC__CMDB__OBJTYPE__CONFIGURATION_MODUS";
    } // function

    /**
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * Returns name of template to show in the content "bottom" view.
     *
     * @return  string
     */
    public function get_template_bottom()
    {
        return "content/bottom/content/object_table_list.tpl";
    } // function

    /**
     * Returns name of template to show in the content "top" view.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return "";
    } // function

    /**
     * @param  integer $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        $l_posts   = $this->get_module_request()
            ->get_posts();
        $l_gets    = $this->get_module_request()
            ->get_gets();
        $l_actproc = $this->get_action_processor();

        // Determine object group for type creation.
        $l_objgroupid = C__OBJTYPE_GROUP__INFRASTRUCTURE;

        switch ($p_navmode)
        {
            case C__NAVMODE__NEW:
                isys_auth_cmdb::instance()
                    ->check(isys_auth::EDIT, 'OBJ_TYPE');

                if (isset($l_gets[C__CMDB__GET__OBJECTGROUP]))
                {
                    $l_objgroupid = $l_gets[C__CMDB__GET__OBJECTGROUP];
                } // if

                $l_actproc->insert(
                    C__CMDB__ACTION__CONFIG_OBJECTTYPE,
                    [
                        $p_navmode,
                        $l_objgroupid,
                        $l_posts
                    ]
                );

                // Process the action queue.
                $l_actproc->process();

                // Retrieve last result.
                $l_objtypeid = $l_actproc->result_pop();

                if ($l_objtypeid)
                {
                    // Set navigation parameters.
                    $l_gets[C__CMDB__GET__VIEWMODE]   = C__CMDB__VIEW__CONFIG_OBJECTTYPE;
                    $l_gets[C__CMDB__GET__EDITMODE]   = C__EDITMODE__ON;
                    $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_objtypeid;

                    // We need edit as navmode.
                    $l_posts[C__GET__NAVMODE] = C__NAVMODE__EDIT;

                    // Set new request parameters.
                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);
                    $this->get_module_request()
                        ->_internal_set_private("m_post", $l_posts);

                    // Set formular action for view jump.
                    $this->readapt_form_action();

                    // Trigger a module reload now to reset the views.
                    $this->trigger_module_reload();
                }

                break;

            case C__NAVMODE__EDIT:

                $l_objtypeid = null;
                if (is_array($l_posts["id"]))
                {
                    $l_objtypeid = @$l_posts["id"][0];
                }

                if ($l_objtypeid)
                {
                    $l_obj_type = $this->m_dao_cmdb->get_object_type($l_objtypeid);
                    isys_auth_cmdb::instance()
                        ->check(isys_auth::EDIT, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']);

                    $l_gets[C__CMDB__GET__VIEWMODE]   = C__CMDB__VIEW__CONFIG_OBJECTTYPE;
                    $l_gets[C__CMDB__GET__EDITMODE]   = C__EDITMODE__ON;
                    $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_objtypeid;

                    // Set new request parameters.
                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);

                    // Set formular action for view jump.
                    $this->readapt_form_action();

                    // Trigger a module reload now to reset the views.
                    $this->trigger_module_reload();
                }
                break;

            case C__NAVMODE__DELETE:
                if (is_array($l_posts["id"]))
                {
                    $l_objtypeid = @$l_posts["id"][0];
                } // if

                $l_obj_type = $this->m_dao_cmdb->get_object_type($l_objtypeid);

                if ($l_obj_type['isys_obj_type__status'] == C__RECORD_STATUS__NORMAL)
                {
                    try
                    {
                        isys_auth_cmdb::instance()
                            ->check(isys_auth::ARCHIVE, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']);
                    }
                    catch (Exception $e)
                    {
                        isys_auth_cmdb::instance()
                            ->check(isys_auth::DELETE, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']);
                    } // try
                }
                else
                {
                    isys_auth_cmdb::instance()
                        ->check(isys_auth::DELETE, 'OBJ_TYPE/' . $l_obj_type['isys_obj_type__const']);
                } // if

                $l_actproc->insert(
                    C__CMDB__ACTION__CONFIG_OBJECTTYPE,
                    [
                        $p_navmode,
                        $l_objgroupid,
                        $l_posts
                    ]
                );
                break;
        } // switch

        $l_edit_right   = isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::EDIT, 'OBJ_TYPE');
        $l_delete_right = isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::DELETE, 'OBJ_TYPE');

        $this->get_module_request()
            ->get_navbar()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
            ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW);
    } // function

    /**
     * List init.
     *
     * @return  boolean
     */
    public function list_init()
    {
        $this->get_module_request()
            ->get_template()
            ->assign("content_title", _L($this->get_name()));

        return true;
    } // function

    /**
     * List process method.
     *
     * @return  string
     * @throws  isys_exception_cmdb
     */
    public function list_process()
    {
        global $g_ajax_calls;

        /*
         * Grundsaetzlich gehen wir davon aus, dass alle notwendigen GET-Parameter fue½r die Anzeige gesetzt sind.
         * Das ist die Aufgabe des Request Conformers, der seine Arbeit innerhalb des CMDB-Moduls verrichtet.
         */

        // Build URL.
        $l_gets = $this->get_module_request()
            ->get_gets();

        $this->m_comp_list->set_listdao($this->m_dao_list);

        $l_gets[C__CMDB__GET__VIEWMODE]   = C__CMDB__VIEW__CONFIG_OBJECTTYPE;
        $l_gets[C__CMDB__GET__OBJECTTYPE] = "[{isys_obj_type__id}]";

        $l_url = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_gets)));

        if ($g_ajax_calls)
        {
            $l_url = "form_submit('" . $l_url . "&call=object_list', 'get');";
        }

        // Configure list component.
        $this->m_comp_list->config($this->m_dao_list->get_fields(), $l_url, "[{isys_obj_type__id}]");

        $this->m_comp_list->set_data(null, $this->m_dao_list->get_result());

        // Emit signal.
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.beforeCreateObjectTypeList", $this->m_comp_list);

        if ($this->m_comp_list->createTempTable())
        {
            return $this->m_comp_list->getTempTableHtml();
        }
        else
        {
            throw new isys_exception_cmdb("Could not create temporary table for objecttype list." . "createTempTable failed.");
        } // if
    } // function

    /**
     * Public constructor, which calls protected parent.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        $this->m_dao_list = new isys_cmdb_dao_list_objecttype($p_modreq->get_database());
    } // function
} // class