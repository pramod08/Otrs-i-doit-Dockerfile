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
 *  __     __  ______  ______  __  __  ______ __      ______  __     __
 * /\ \  _ \ \/\  __ \/\  == \/\ \/ / /\  ___/\ \    /\  __ \/\ \  _ \ \
 * \ \ \/ ".\ \ \ \/\ \ \  __<\ \  _"-\ \  __\ \ \___\ \ \/\ \ \ \/ ".\ \
 *  \ \__/".~\_\ \_____\ \_\ \_\ \_\ \_\ \_\  \ \_____\ \_____\ \__/".~\_\
 *   \/_/   \/_/\/_____/\/_/ /_/\/_/\/_/\/_/   \/_____/\/_____/\/_/   \/_/
 *
 * Truncate workflow data:
 *
 *  TRUNCATE TABLE isys_workflow;
 *  TRUNCATE TABLE isys_workflow_action;
 *  TRUNCATE TABLE isys_workflow_action_parameter;
 *  TRUNCATE TABLE isys_workflow_2_isys_workflow_action;
 *
 * @package    i-doit
 * @subpackage Workflow
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_workflow
{

    /**
     * @var int
     */
    protected $m_id;
    /**
     * @var int
     */
    private $m_category;
    /**
     * @var array
     */
    private $m_data;
    /**
     * @var int
     */
    private $m_exception;
    /**
     * @var int
     */
    private $m_initiator;
    /**
     * isys_workflow_type__occurrence
     *
     * @var int
     */
    private $m_is_circular;
    /**
     * @var int
     */
    private $m_object;
    /**
     * @var int
     */
    private $m_occurrence;
    /**
     * isys_workflow__isys_workflow__id
     *
     * @var int
     */
    private $m_parent;
    /**
     * @var int
     */
    private $m_status;
    /**
     * @var string
     */
    private $m_title;
    /**
     * @var int
     */
    private $m_type;

    public function get_title()
    {
        return $this->m_title;
    } // function

    public function get_category()
    {
        return $this->m_category;
    } // function

    public function get_initiator()
    {
        return $this->m_initiator;
    } // function

    public function get_object_id()
    {
        return $this->m_object;
    } // function

    public function get_occurrence()
    {
        return $this->m_occurrence;
    } // function

    public function get_exception()
    {
        return $this->m_exception;
    } // function

    public function get_parent()
    {
        return $this->m_parent;
    } // function

    public function get_type()
    {
        return $this->m_type;
    } // function

    public function get_id()
    {
        return $this->m_id;
    } // function

    public function get_status()
    {
        return $this->m_status;
    } // function

    public function get_circular()
    {
        return $this->m_is_circular;
    } // function

    /**
     * Save an action.
     *
     * @param   integer               $p_action_id
     * @param   integer               $p_workflow_id
     * @param   isys_workflow_request $p_req
     *
     * @return  boolean
     */
    public function save_action($p_action_id, $p_workflow_id, &$p_req = null)
    {
        $l_object = $this->get_action_instance($p_action_id);

        if (method_exists($l_object, "save"))
        {
            return $l_object->save($p_workflow_id, $p_req);
        } // if
    } // function

    /**
     * Handles a specified action.
     *
     * @param   integer $p_action_id
     *
     * @return  boolean
     */
    public function handle_action($p_action_id)
    {
        $l_object = $this->get_action_instance($p_action_id);

        if (method_exists($l_object, "handle"))
        {
            return $l_object->handle();
        } // if
    } // function

    /**
     *
     * @param   isys_workflow_request  $p_req
     * @param                          $p_action_id
     *
     * @throws  isys_exception_cmdb
     * @return  integer
     */
    public function insert(isys_workflow_request &$p_req, $p_action_id)
    {
        global $g_comp_database;

        $l_from = $p_req->get_from();
        $l_meta = $p_req->get_meta();

        $l_dao_workflow = new isys_workflow_dao_action($g_comp_database);

        $l_exception = 0;

        if (is_array($l_meta["f_workflow_exception"]))
        {
            foreach ($l_meta["f_workflow_exception"] as $l_key => $l_value)
            {
                $l_exception += (1 << $l_value);
            } // foreach
        } // if

        $l_objects = json_decode($l_meta["f_object__HIDDEN"]);

        // Create workflows only if there are objects selected.
        if (count($l_objects) > 0)
        {
            /* Initially create that badass ------------------------------------------ */
            $l_workflow_id = $l_dao_workflow->create_workflow(
                $l_meta["C__WF__TITLE"],
                $l_from,
                $p_req->get_workflow_type(),
                $l_meta["C__WF__CATEGORY"],
                $l_objects,
                $l_meta["f_occurrence"],
                $l_exception,
                $l_meta['C__WF__PARENT_WORKFLOW']
            );

            /* handle the action ----------------------------------------------------- */
            $this->save_action($p_action_id, $l_workflow_id, $p_req);

            /* NOTIFICATION ---------------------------------------------------------------------- */
            $this->save_action(4 /*=notification*/, $l_workflow_id, $p_req);

            return $l_workflow_id;
        }
        else
        {
            throw new isys_exception_cmdb(_L('LC_WORKFLOW__EXCEPTION__NO_OBJECTS'));
        } // if
    } // function

    /**
     * @return  isys_workflow_data
     */
    public function get_data()
    {
        return $this->m_data;
    } // function

    /**
     *
     */
    public function unload()
    {
        global $g_comp_template;

        $g_comp_template->assign("g_completed", null)
            ->assign("g_cancelled", null)
            ->assign("g_accepted", null)
            ->assign("g_assign", null)
            ->assign("g_accepted_users", null)
            ->assign("g_assigned_users", null)
            ->assign("g_current_status", null)
            ->assign("g_exceptions", null)
            ->assign("g_initiator_name", null);

        $this->m_data = null;

        $this->m_id          = null;
        $this->m_title       = null;
        $this->m_category    = null;
        $this->m_initiator   = null;
        $this->m_type        = null;
        $this->m_object      = null;
        $this->m_occurrence  = null;
        $this->m_exception   = null;
        $this->m_parent      = null;
        $this->m_is_circular = null;
    } // function

    /**
     *
     * @param   integer $p_workflow__id
     * @param   boolean $p_grouped_actions
     *
     * @return  boolean
     */
    public function load($p_workflow__id, $p_grouped_actions = false)
    {
        global $g_comp_database;

        $this->m_data = new isys_workflow_data();

        $l_workflow_dao = new isys_workflow_dao_action($g_comp_database);
        $l_metadata     = $l_workflow_dao->get_workflows($p_workflow__id);
        $l_row          = $l_metadata->get_row();

        $this->m_id          = $l_row["isys_workflow__id"];
        $this->m_title       = $l_row["isys_workflow__title"];
        $this->m_category    = $l_row["isys_workflow__isys_workflow_category__id"];
        $this->m_initiator   = $l_row["isys_workflow__isys_contact__id"];
        $this->m_type        = $l_row["isys_workflow__isys_workflow_type__id"];
        $this->m_object      = $l_workflow_dao->get_linked_objects($l_row["isys_workflow__id"]);
        $this->m_occurrence  = $l_row["isys_workflow__occurrence"];
        $this->m_exception   = $l_row["isys_workflow__exception"];
        $this->m_parent      = $l_row["isys_workflow__isys_workflow__id"];
        $this->m_status      = intval($l_row["isys_workflow__status"]);
        $this->m_is_circular = $l_row["isys_workflow_type__occurrence"];

        $l_workflow_action = new isys_workflow_action($p_workflow__id);

        try
        {
            if ($p_grouped_actions)
            {
                $l_metadata->reset_pointer();
                $l_workflow_action->load($this->m_data, $l_metadata);
            }
            else
            {
                $l_workflow_action->load($this->m_data);
            } // if
        }
        catch (isys_exception_general $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        } // try

        return true;
    } // function

    /**
     * Create a link to workflow details.
     *
     * @param   integer $p_workflow__id
     *
     * @return  string
     */
    public function create_link($p_workflow__id)
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_link[C__GET__MAIN_MENU__NAVIGATION_ID] = $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID];
        $l_link[C__CMDB__GET__TREEMODE]           = C__WF__VIEW__TREE;
        $l_link[C__CMDB__GET__VIEWMODE]           = C__WF__VIEW__DETAIL__GENERIC;
        $l_link[C__WF__GET__ID]                   = $p_workflow__id;

        return "?" . urldecode(isys_glob_http_build_query($l_link));
    } // function

    /**
     * Get instance of an action by its action id.
     *
     * @param   integer $p_action_id
     *
     * @return  mixed
     */
    private function get_action_instance($p_action_id)
    {
        global $g_comp_database;

        $l_dao_workflow_actions = new isys_workflow_dao_type($g_comp_database);

        $l_data = $l_dao_workflow_actions->get_action_types($p_action_id);
        $l_row  = $l_data->get_row();

        $l_class = $l_row["isys_workflow_action_type__class"];

        /* Get object and handle it  (isys_workflow_action_*) -------------------- */
        if (class_exists($l_class))
        {
            return new $l_class();
        }
        else
        {
            return false;
        } // if
    } // function
} // class