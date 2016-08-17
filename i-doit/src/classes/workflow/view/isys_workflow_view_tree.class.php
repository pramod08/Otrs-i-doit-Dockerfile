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
 * Updates at the bottom of the file for easy SVN diff.
 *
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @author    Leonard Fischer <lfischer@synetics.de>
 * @version   1.0 Wed Jun 21 15:39:47 CEST 2006 15:39:47
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_workflow_view_tree extends isys_cmdb_view
{
    /**
     * @var Integer
     */
    protected $m_c = 0;
    /**
     * @var Integer
     */
    protected $m_root = 0;
    /**
     * @var isys_component_tree
     */
    protected $m_tree;
    /**
     * @var Array
     */
    private $m_menu_tree;
    /**
     * @var Integer
     */
    private $m_selected;

    /**
     * Returns the ID of this view.
     *
     * @return Integer
     * @see isys_cmdb_view::get_id()
     */
    public function get_id()
    {
        return C__WF__VIEW__TREE;
    } // function

    /**
     * Set tree-mode to true.
     *
     * @see isys_cmdb_view::get_mandatory_parameters()
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__TREEMODE] = true;
    } // function

    /**
     * Returns the name of this view.
     *
     * @return String
     * @see isys_cmdb_view::get_name()
     */
    public function get_name()
    {
        return "Workflows";
    } // function

    /**
     * Empty method from parent.
     *
     * @see isys_cmdb_view::get_optional_parameters()
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * Method from parent class, used for processing the request.
     *
     * @return Boolean|Null
     * @see isys_cmdb_view::process()
     */
    public function process()
    {
        $this->m_tree = $this->get_module_request()
            ->get_menutree();
        if (is_object($this->m_tree))
        {
            $this->m_tree->reinit();
            $this->tree_build();

            return $this->tree_process();
        } // if

        return null;
    } // function

    /**
     * Adds a new additional tree node.
     *
     * @param Integer $p_id
     * @param String  $p_text
     */
    public function add_tree_entry($p_id, $p_text)
    {
        global $g_comp_template_language_manager;
        $this->m_menu_tree[$p_id] = [
            "text" => $g_comp_template_language_manager->get($p_text),
            "mode" => C__WF__VIEW__LIST
        ];
    } // function

    /**
     * Return the additional tree items.
     *
     * @return Array
     */
    public function get_tree()
    {
        return $this->m_menu_tree;
    } // function

    /**
     * Returns the tree component.
     *
     * @return isys_component_tree
     */
    public function get_tree_component()
    {
        return $this->m_tree;
    } // function

    /**
     * Adds the root-node.
     *
     * @param String $p_name
     */
    public function add_root($p_name)
    {
        $this->m_tree->add_node(0, -1, $p_name);
    } // function

    /** @desc build the tree */
    public function tree_build()
    {
        if ($_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM)
        {
            return true;
        } // if

        global $g_comp_template_language_manager;
        global $g_comp_database;

        /**
         * @desc get workflow dao
         */
        $l_workflow_dao = new isys_workflow_dao_type($g_comp_database);

        /* set root name of tree */
        $this->add_root($this->get_name());

        $l_c                                      = 0;
        $l_gets[C__CMDB__GET__TREEMODE]           = $this->get_id();
        $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID] = $_GET[C__GET__MAIN_MENU__NAVIGATION_ID];

        /**
         * @desc add a default link to the tree
         *            this link is for viewing all types grouped into one list
         */
        $this->add_tree_entry($l_c++, $g_comp_template_language_manager->get("LC__WORKFLOWS__ALL"));

        /**
         * @desc get known workflow types and add them to the tree
         */
        $l_wf_types = $l_workflow_dao->get_workflow_types();

        while ($l_row = $l_wf_types->get_row())
        {
            $this->add_tree_entry($l_row["isys_workflow_type__id"], $l_row["isys_workflow_type__title"]);
        }

        $l_thetree = $this->get_tree();

        /* -------------------------------------------------------------------------------------- */
        /* - Overview : ------------------------------------------------------------------------- */
        /* -------------------------------------------------------------------------------------- */
        $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST_FILTER;
        unset($l_gets[C__WF__GET__TYPE]);
        /* -------------------------------------------------------------------------------------- */
        $this->m_tree->add_node(
            $l_c++,
            0,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__OVERVIEW"),
            isys_helper_link::create_url($l_gets),
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_FILTER)
        );
        if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST_FILTER)
        {
            $this->m_selected = $l_c - 1;
        }
        /* -------------------------------------------------------------------------------------- */
        /* - Workflows Group : ------------------------------------------------------------------ */
        /* -------------------------------------------------------------------------------------- */
        $l_workflows__id = $this->m_tree->add_node(
            $l_c++,
            0,
            "Workflows",
            '',
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST)
        );
        /* -------------------------------------------------------------------------------------- */
        foreach ($l_thetree as $l_key => $l_value)
        {
            $l_gets[C__WF__GET__TYPE]       = $l_key;
            $l_gets[C__CMDB__GET__VIEWMODE] = $l_value["mode"];

            $this->m_tree->add_node(
                $l_c++,
                $l_workflows__id,
                $l_value["text"],
                isys_helper_link::create_url($l_gets),
                '',
                '',
                0,
                '',
                '',
                isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST)
            );
            if (isset($_GET[C__WF__GET__TYPE]) && $_GET[C__WF__GET__TYPE] == $l_key)
            {
                $this->m_selected = $l_c - 1;
            }
        }
        /* -------------------------------------------------------------------------------------- */
        /* - My Workflows : --------------------------------------------------------------------- */
        /* -------------------------------------------------------------------------------------- */
        $l_settings__id = $this->m_tree->add_node(
            $l_c++,
            0,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__MY"),
            '',
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_FILTER)
        );

        /* -------------------------------------------------------------------------------------- */
        $l_gets[C__CMDB__GET__VIEWMODE]   = C__WF__VIEW__LIST_FILTER;
        $l_gets[C__WF__GET__TYPE]         = C__WORKFLOW_TYPE__CHECKLIST;
        $l_gets["uid"]                    = $_SESSION["session_data"]["isys_user_session__isys_obj__id"];
        $l_gets[C__WORKFLOW__GET__FILTER] = "d";

        /* -------------------------------------------------------------------------------------- */
        $this->m_tree->add_node(
            $l_c++,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__CURRENT_DAY"),
            isys_helper_link::create_url($l_gets),
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_FILTER)
        );
        if ($_GET[C__WORKFLOW__GET__FILTER] == "d") $this->m_selected = $l_c - 1;
        /* -------------------------------------------------------------------------------------- */
        $l_gets[C__WORKFLOW__GET__FILTER] = "m";
        $this->m_tree->add_node(
            $l_c++,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__CURRENT_MONTH"),
            isys_helper_link::create_url($l_gets),
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_FILTER)
        );
        if ($_GET[C__WORKFLOW__GET__FILTER] == "m") $this->m_selected = $l_c - 1;
        /* -------------------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------------------- */
        /* - Settings : ------------------------------------------------------------------------- */
        /* -------------------------------------------------------------------------------------- */
        $l_settings__id = $this->m_tree->add_node(
            $l_c++,
            0,
            $g_comp_template_language_manager->get("LC__WORKFLOW__TEMPLATES"),
            '',
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE)
        );
        /* -------------------------------------------------------------------------------------- */
        $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST_WF_TYPE;
        unset($l_gets[C__WF__GET__TYPE]);
        $this->m_tree->add_node(
            $l_c++,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOW__TYPES"),
            isys_helper_link::create_url($l_gets),
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE)
        );
        if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST_WF_TYPE || $_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__DETAIL__WF_TYPE) $this->m_selected = $l_c - 1;
        /* -------------------------------------------------------------------------------------- */
        $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST_TEMPLATE;
        $this->m_tree->add_node(
            $l_c++,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOW__TEMPLATE_PARAMETERS"),
            isys_helper_link::create_url($l_gets),
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__LIST_TEMPLATE)
        );
        if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__LIST_TEMPLATE || $_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__DETAIL__TEMPLATE) $this->m_selected = $l_c - 1;
        /* -------------------------------------------------------------------------------------- */

        /* -------------------------------------------------------------------------------------- */
        /* - E-mail GUI : ----------------------------------------------------------------------- */
        /* -------------------------------------------------------------------------------------- */

        unset($l_gets);
        $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID] = $_GET[C__GET__MAIN_MENU__NAVIGATION_ID];
        $l_gets[C__CMDB__GET__VIEWMODE]           = C__WF__VIEW__DETAIL__EMAIL_GUI;
        $l_gets[C__CMDB__GET__TREEMODE]           = $this->get_id();
        $l_emailNode                              = $this->m_tree->add_node(
            $l_c++,
            0,
            $g_comp_template_language_manager->{LC_WORKFLOW_TREE__EMAIL},
            isys_helper_link::create_url($l_gets),
            '',
            '',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::VIEW, 'WORKFLOW/' . C__WF__VIEW__DETAIL__EMAIL_GUI)
        );

        if ($_GET[C__CMDB__GET__VIEWMODE] == C__WF__VIEW__DETAIL__EMAIL_GUI)
        {
            $this->m_selected = $l_c - 1;
        }

        $l_gets['tplID']                                     = C__EMAIL_TEMPLATE__TASK__NOTIFICATION;
        $l_treeNodeID[C__EMAIL_TEMPLATE__TASK__NOTIFICATION] = $this->m_tree->add_node(
            $l_c++,
            $l_emailNode,
            _L('LC__WORKFLOW__ACTION__TYPE__ASSIGN'),
            "?" . isys_glob_http_build_query($l_gets),
            ''
        );

        $l_gets['tplID']                               = C__EMAIL_TEMPLATE__TASK__ACCEPT;
        $l_treeNodeID[C__EMAIL_TEMPLATE__TASK__ACCEPT] = $this->m_tree->add_node(
            $l_c++,
            $l_emailNode,
            _L('LC__WORKFLOW__ACTION__TYPE__ACCEPTED'),
            "?" . isys_glob_http_build_query($l_gets)
        );

        $l_gets['tplID']                                            = C__EMAIL_TEMPLATE__TASK__COMPLETION_ACCEPTED;
        $l_treeNodeID[C__EMAIL_TEMPLATE__TASK__COMPLETION_ACCEPTED] = $this->m_tree->add_node(
            $l_c++,
            $l_emailNode,
            _L('LC__WORKFLOW__ACTION__TYPE__COMPLETE'),
            "?" . isys_glob_http_build_query($l_gets)
        );

        $l_gets['tplID']                                      = C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED;
        $l_treeNodeID[C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED] = $this->m_tree->add_node(
            $l_c++,
            $l_emailNode,
            _L('LC__WORKFLOW__ACTION__TYPE__CANCEL'),
            "?" . isys_glob_http_build_query($l_gets)
        );

        if (isset($_GET['tplID']))
        {
            $this->m_selected = $l_treeNodeID[$_GET['tplID']];
        }

        return true;
    } // function

    /**
     * Return the processed tree.
     *
     * @return String
     */
    public function tree_process()
    {
        return $this->m_tree->process($this->m_selected);
    } // function

    /**
     * Returns true or false if the tree has been processed.
     *
     * @return Boolean
     */
    public function processed()
    {
        if (is_null($this->m_tree))
        {
            $this->m_tree = $this->get_module_request()
                ->get_menutree();
        } // if

        if ($this->m_tree->count() > 1)
        {
            return true;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Build the tree for the modules-menu.
     *
     * @param isys_component_tree $p_tree
     *
     * @return Boolean
     * @author Leonard Fischer <lfischer@synetics.de>
     * @see    isys_workflow_view_tree::tree_build()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent)
    {
        global $g_comp_template_language_manager, $g_comp_database;

        // Set the tree.
        $this->m_tree = $p_tree;

        // Get workflow dao.
        $l_workflow_dao = new isys_workflow_dao_type($g_comp_database);

        // Set root name of tree.
        $this->m_root = $this->m_tree->add_node(
            C__WF__VIEW__TREE . (++$this->m_c),
            $this->m_tree->find_id_by_title('Modules'),
            $this->get_name()
        );

        $l_gets                                   = [];
        $l_gets[C__GET__MODULE_ID]                = $_GET[C__GET__MODULE_ID];
        $l_gets[C__CMDB__GET__TREEMODE]           = $this->get_id();
        $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID] = $_GET[C__GET__MAIN_MENU__NAVIGATION_ID];

        /*
                // Overview.
                $l_gets[C__GET__TREE_NODE] = C__WF__VIEW__TREE . (++ $this->m_c);
                $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST_FILTER;
                $this->m_tree->add_node(
                    C__WF__VIEW__TREE . $this->m_c,
                    $this->m_root,
                    $g_comp_template_language_manager->get("LC__WORKFLOWS__OVERVIEW"),
                    isys_helper_link::create_url($l_gets));
        */

        // Workflows Group.
        $l_gets[C__GET__TREE_NODE] = C__WF__VIEW__TREE . (++$this->m_c);
        $l_workflows__id           = $this->m_tree->add_node(
            C__WF__VIEW__TREE . $this->m_c,
            $this->m_root,
            "Workflows"
        );

        $this->add_tree_entry(
            C__WF__VIEW__TREE . (++$this->m_c),
            $g_comp_template_language_manager->get("LC__WORKFLOWS__ALL")
        );

        // Get known workflow types and add them to the tree.
        $l_wf_types = $l_workflow_dao->get_workflow_types();

        while ($l_row = $l_wf_types->get_row())
        {
            $this->add_tree_entry(
                $l_row["isys_workflow_type__id"],
                $l_row["isys_workflow_type__title"]
            );
        } // while

        $l_thetree = $this->get_tree();

        foreach ($l_thetree as $l_key => $l_value)
        {
            $l_gets[C__WF__GET__TYPE]       = $l_key;
            $l_gets[C__CMDB__GET__VIEWMODE] = $l_value["mode"];
            $l_gets[C__GET__TREE_NODE]      = C__WF__VIEW__TREE . (++$this->m_c);

            $this->m_tree->add_node(
                C__WF__VIEW__TREE . $this->m_c,
                $l_workflows__id,
                $l_value["text"],
                isys_helper_link::create_url($l_gets)
            );
        } // foreach

        // Set the counter for debugging sake.
        C__WF__VIEW__TREE . (++$this->m_c);

        // My Workflows
        $l_settings__id = $this->m_tree->add_node(
            C__WF__VIEW__TREE . (++$this->m_c),
            $this->m_root,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__MY")
        );

        $l_gets[C__CMDB__GET__VIEWMODE]   = C__WF__VIEW__LIST_FILTER;
        $l_gets[C__WF__GET__TYPE]         = C__WORKFLOW_TYPE__CHECKLIST;
        $l_gets["uid"]                    = $_SESSION["session_data"]["isys_user_session__isys_obj__id"];
        $l_gets[C__WORKFLOW__GET__FILTER] = "d";
        $l_gets[C__GET__TREE_NODE]        = C__WF__VIEW__TREE . (++$this->m_c);
        $this->m_tree->add_node(
            C__WF__VIEW__TREE . $this->m_c,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__CURRENT_DAY"),
            isys_helper_link::create_url($l_gets)
        );

        $l_gets[C__GET__TREE_NODE]        = C__WF__VIEW__TREE . (++$this->m_c);
        $l_gets[C__WORKFLOW__GET__FILTER] = "m";
        $this->m_tree->add_node(
            C__WF__VIEW__TREE . $this->m_c,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOWS__CURRENT_MONTH"),
            isys_helper_link::create_url($l_gets)
        );

        // Settings
        $l_settings__id = $this->m_tree->add_node(
            C__WF__VIEW__TREE . (++$this->m_c),
            $this->m_root,
            $g_comp_template_language_manager->get("LC__WORKFLOW__TEMPLATES")
        );

        $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST_WF_TYPE;
        $l_gets[C__GET__TREE_NODE]      = C__WF__VIEW__TREE . (++$this->m_c);
        unset($l_gets[C__WF__GET__TYPE]);
        $this->m_tree->add_node(
            C__WF__VIEW__TREE . $this->m_c,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOW__TYPES"),
            isys_helper_link::create_url($l_gets)
        );

        $l_gets[C__GET__MODULE_ID]      = $_GET[C__GET__MODULE_ID];
        $l_gets[C__CMDB__GET__VIEWMODE] = C__WF__VIEW__LIST_TEMPLATE;
        $l_gets[C__GET__TREE_NODE]      = C__WF__VIEW__TREE . (++$this->m_c);
        $this->m_tree->add_node(
            C__WF__VIEW__TREE . $this->m_c,
            $l_settings__id,
            $g_comp_template_language_manager->get("LC__WORKFLOW__TEMPLATE_PARAMETERS"),
            isys_helper_link::create_url($l_gets)
        );

        // E-mail GUI
        $l_gets                                   = [];
        $l_gets[C__GET__MODULE_ID]                = $_GET[C__GET__MODULE_ID];
        $l_gets[C__GET__MAIN_MENU__NAVIGATION_ID] = $_GET[C__GET__MAIN_MENU__NAVIGATION_ID];
        $l_gets[C__CMDB__GET__VIEWMODE]           = C__WF__VIEW__DETAIL__EMAIL_GUI;
        $l_gets[C__CMDB__GET__TREEMODE]           = $this->get_id();
        $l_gets[C__GET__TREE_NODE]                = C__WF__VIEW__TREE . (++$this->m_c);
        $this->m_tree->add_node(
            C__WF__VIEW__TREE . $this->m_c,
            $this->m_root,
            $g_comp_template_language_manager->{LC_WORKFLOW_TREE__EMAIL},
            isys_helper_link::create_url($l_gets)
        );

        return true;
    } // function

    /**
     * Constructor.
     *
     * @param isys_module_request $p_modreq
     *
     * @see isys_cmdb_view::__construct()
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class