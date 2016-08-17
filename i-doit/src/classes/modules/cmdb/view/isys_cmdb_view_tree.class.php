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
 * CMDB Tree view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_tree extends isys_cmdb_view
{
    /**
     * The currently selected node.
     *
     * @var  integer (?)
     */
    protected $m_select_node;
    /**
     * Tree component.
     *
     * @var  isys_component_tree
     */
    protected $m_tree;

    abstract public function tree_build();

    abstract public function tree_process();

    /**
     * Returns the tree component
     *
     * @return isys_component_tree
     */
    public function get_tree_component()
    {
        return $this->m_tree;
    } // function

    /**
     * Method for finding out, if the tree has already been processed.
     *
     * @return  boolean
     */
    public function processed()
    {
        if (is_null($this->m_tree))
        {
            $this->m_tree = $this->get_module_request()
                ->get_menutree();
        } // if

        return ($this->m_tree->count() > 1);
    } // function

    /**
     *
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__TREEMODE] = true;
    } // function

    /**
     *
     * @param  array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        // Nothing to do here.
    } // function

    /**
     * Process view and return HTML result.
     *
     * @return  mixed
     */
    public function process()
    {
        if (is_null($this->m_tree))
        {
            $this->m_tree = $this->get_module_request()
                ->get_menutree();
        } // if

        if (is_object($this->m_tree))
        {
            $this->m_tree->reinit();
            $this->tree_build();

            return $this->tree_process();
        } // if

        return null;
    } // function

    /**
     * Method for removing ajax parameter from the GET array.
     *
     * @param   array $p_get
     *
     * @return  array
     */
    protected function remove_ajax_parameters(&$p_get)
    {
        unset($p_get[C__GET__AJAX_CALL], $p_get[C__GET__AJAX_REQUEST], $p_get[C__GET__AJAX]);

        return $p_get;
    } // function

    /**
     * @param        $p_catData
     * @param        $p_catNodeBase
     * @param        $p_catNodeParent
     * @param string $p_catGet
     * @param string $p_tbl
     */
    protected function tree_create_subcategory($p_catData, $p_catNodeBase, $p_catNodeParent, $p_catGet = C__CMDB__GET__CATG, $p_tbl = "isysgui_catg")
    {
        global $g_dirs, $g_ajax_calls;

        $l_gets    = $this->get_module_request()
            ->get_gets();
        $l_dao     = $this->get_dao_cmdb();
        $l_subcats = $l_dao->get_isysgui($p_tbl, null, null, null, $p_catData[$p_tbl . "__id"], $p_tbl . "__sort ASC");

        if ($l_subcats->num_rows() > 0)
        {
            while ($l_row = $l_subcats->get_row())
            {
                /* Skip processing when dao class does not exist */
                if (!class_exists($l_row[$p_tbl . '__class_name']))
                {
                    continue;
                }

                if ($l_row['isysgui_cats__id'] == C__CATS__BASIC_AUTH)
                {
                    if (!isys_auth_auth::instance()
                        ->is_allowed_to(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH')
                    )
                    {
                        continue;
                    } // if
                } // if

                // Define node id.
                $l_nodeid = $p_catNodeBase + ($l_row[$p_tbl . "__id"] * 100);

                // Set selected node.
                if ($_GET[$p_catGet] == $l_row[$p_tbl . "__id"])
                {
                    $this->m_select_node = $l_nodeid;
                } // if

                // Reset the Category selection parameters.
                $this->reduce_catspec_parameters($l_gets);

                // Set them new according to subcategory settings.
                $l_getsJump[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;

                // Set category ID.
                $l_gets[$p_catGet] = $l_row[$p_tbl . "__id"];

                if (!empty($l_row[$p_tbl . "__list_multi_value"]))
                {
                    $l_viewmode = C__CMDB__VIEW__LIST_CATEGORY;
                }
                else
                {
                    $l_viewmode = C__CMDB__VIEW__CATEGORY;
                } // if

                $l_strIcon = (!empty($l_row[C__CMDB__TREE_ICON])) ? $g_dirs["images"] . "dtree/special/" . $l_row[C__CMDB__TREE_ICON] : "";

                // Remove ajax parameters.
                unset($l_gets["call"], $l_gets[C__GET__AJAX]);

                if ($g_ajax_calls)
                {
                    $l_link = "javascript:get_content_by_object('" . $l_gets[C__CMDB__GET__OBJECT] . "', '" . $l_viewmode . "', '" . $l_row[$p_tbl . "__id"] . "', '" . $p_catGet . "');";
                }
                else
                {
                    $l_link = isys_glob_build_url(isys_glob_http_build_query($l_gets));
                } // if

                $l_category_tooltip = _L($l_row[$p_tbl . "__title"]);

                // Check if category has entries.
                if (empty($l_gets[C__CMDB__GET__OBJECT]))
                {
                    $l_category_title = "<span class='noentries' >" . $l_category_tooltip . "</span>";
                }
                elseif (!$l_dao->check_category($l_gets[C__CMDB__GET__OBJECT], $l_row[$p_tbl . "__class_name"], $l_row[$p_tbl . "__id"], $l_row[$p_tbl . "__source_table"]))
                {
                    $l_category_title = "<span class='noentries' >" . $l_category_tooltip . "</span>";
                }
                else
                {
                    $l_category_title = $l_category_tooltip;
                } // if

                $this->m_tree->add_node(
                    $l_nodeid,
                    $p_catNodeParent,
                    $l_category_title,
                    $l_link,
                    '',
                    $l_strIcon,
                    0,
                    '',
                    $l_category_tooltip,
                    true,
                    $l_row[$p_tbl . '__const']
                );
            } // while
        } // if
    } // function

    /**
     * Create back url.
     *
     * @param   integer $p_viewmode
     * @param   integer $p_treemode
     * @param   array   $p_newgets
     *
     * @return  string
     */
    protected function get_back_url($p_viewmode, $p_treemode, $p_newgets = null)
    {
        global $g_ajax_calls;

        $l_gets = $this->get_module_request()
            ->get_gets();

        if (is_array($p_newgets))
        {
            // Allow overriding of GET-Parameters for Back-Link.
            $l_gets = array_merge($l_gets, $p_newgets);
        } // if

        // Remove cateID.
        unset($l_gets[C__CMDB__GET__CATLEVEL]);

        if ($g_ajax_calls)
        {
            switch ($p_treemode)
            {
                case C__CMDB__VIEW__TREE_OBJECT:
                    $l_url = "javascript:get_tree_by_object({$l_gets[C__CMDB__GET__OBJECT]}, false);";
                    break;

                default:
                    $l_object = $this->m_dao_cmdb->get_object_by_id((int) $l_gets[C__CMDB__GET__OBJECT])
                        ->get_row();
                    $l_data   = $this->m_dao_cmdb->get_objtype((int) $l_object['isys_obj__isys_obj_type__id'])
                        ->get_row();

                    $l_url = "javascript:get_tree_object_type('" . $l_data["isys_obj_type__isys_obj_type_group__id"] . "', '" . $l_data['isys_obj_type__id'] . "');";
                    break;
            } // switch
        }
        else
        {
            $l_gets[C__CMDB__GET__TREEMODE] = $p_treemode;
            $l_gets[C__CMDB__GET__VIEWMODE] = $p_viewmode;

            $l_url = isys_glob_build_url(isys_glob_http_build_query($l_gets));
        } // if

        return $l_url;
    } // function

    /**
     * Creates the back button for the menutree Viewmode and treemode are passed, and the node-ID is returned.
     *
     * @param   integer $p_viewmode
     * @param   integer $p_treemode
     * @param   array   $p_newgets
     *
     * @return  integer
     */
    protected function create_back($p_viewmode, $p_treemode, $p_newgets = null)
    {
        global $g_dirs;

        return $this->m_tree->add_node(
            C__CMDB__TREE_NODE__BACK,
            C__CMDB__TREE_NODE__PARENT,
            _L('LC__UNIVERSAL__BACK'),
            $this->get_back_url($p_viewmode, $p_treemode, $p_newgets),
            "",
            $g_dirs["images"] . "dtree/special/back.gif"
        );
    } // function

    /**
     * Constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class