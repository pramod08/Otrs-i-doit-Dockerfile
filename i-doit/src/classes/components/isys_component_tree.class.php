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
 * Tree base implementation
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @author     Leonard Fischer <lfischer@i-doit.org>
 * @version    0.9
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_component_tree extends isys_component
{
    /**
     * @var isys_component_tree[]
     */
    private static $m_instances = [];
    /**
     * This variable is used to set the selected node on the tree.
     *
     * @var Integer
     */
    protected $m_select_node = null;
    /**
     * This variable stores the tree-nodes.
     *
     * @var Array
     */
    protected $m_tree_childs = [];
    /**
     * The name of the tree, will be used as JS-variable name when rendered.
     *
     * @var String
     */
    protected $m_tree_name = '';
    /**
     * The tree and its options.
     *
     * @var Array
     */
    protected $m_tree_output = [];
    /**
     * Shall the tree be searcheable?
     */
    protected $m_tree_search = null;
    /**
     * Shall the tree be sorted?
     *
     * @var Boolean
     */
    protected $m_tree_sort = true;

    /**
     * Tree's default name is menu_tree
     *
     * @param $p_name
     *
     * @return isys_component_tree
     * @throws isys_exception_general
     */
    public static function factory($p_name = 'menu_tree')
    {
        if (!isset(self::$m_instances[$p_name]))
        {
            self::$m_instances[$p_name] = self::instance($p_name)
                ->init();
        }

        return self::$m_instances[$p_name];
    }

    /**
     * @param $p_name
     *
     * @return isys_component_tree
     */
    private static function instance($p_name)
    {
        return new self($p_name);
    }

    /**
     * @param idoit\Tree\Node $p_tree
     */
    public function payload(idoit\Tree\Node $p_tree, isys_register $p_request)
    {
        $this->add_node(
            $p_tree->id,
            -1,
            $p_tree->title,
            $p_tree->link,
            '',
            $p_tree->image,
            0,
            '',
            $p_tree->tooltip,
            $p_tree->accessRight,
            $p_tree->cssClass
        );

        $this->recurse_payload($p_tree->get_childs(), $p_request);
    }

    /**
     * Set the tree search
     *
     * @param Boolean $p_value
     */
    public function set_tree_search($p_value)
    {
        global $g_comp_template;
        $g_comp_template->assign('bMenuTreeSearcheable', (bool) $p_value);
        $this->m_tree_search = (Bool) $p_value;
    }

    /**
     * Set the tree sorting
     *
     * @param Boolean $p_value
     */
    public function set_tree_sort($p_value)
    {
        $this->m_tree_sort = (Bool) $p_value;
    } // function

    /**
     * Count all the elements.
     *
     * @return Integer
     */
    public function count()
    {
        return (count($this->m_tree_childs) + count($this->m_tree_output));
    } // function

    /**
     * Method for finding a node by it's title. The id of the first match will be returned.
     *
     * @param  String $p_title The title which shall get searched.
     *
     * @return Integer
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function find_id_by_title($p_title)
    {
        foreach ($this->m_tree_childs as $l_id => $l_node)
        {
            if (false !== strpos($l_id, $p_title))
            {
                return (int) preg_replace('~' . $this->m_tree_name . '\.add\((\d+),(.*)~', '$1', $l_node);
            } // if
        } // foreach

        return 0;
    } // function

    /**
     * This method helps you to open all nodes to a certain node in a tree and select it.
     *
     * @param Integer $p_id
     * @param Boolean $p_select
     */
    public function select_node_by_id($p_id, $p_select = true)
    {
        $this->m_select_node = $this->m_tree_name . '.openTo(' . (int) $p_id . ', ' . ((true === $p_select) ? 'true' : 'false') . ');';
    } // function

    /**
     * Initializes the tree with the given name.
     *
     * @return Boolean
     * @throws isys_exception_general
     */
    public function init()
    {
        global $g_dirs;

        $l_imagedir = $g_dirs["images"] . "dtree/";

        $this->m_tree_current_id = 1;
        $this->m_tree_childs     = [];
        $this->m_tree_output     = [
            "window['" . $this->m_tree_name . "'] = new dTree('" . $this->m_tree_name . "', " . isys_glob_js_string($l_imagedir) . ");"
        ];

        return $this;
    } // if

    /**
     * Adds a node with specified parameters.
     *
     * @param   integer $p_id        Node's identifier
     * @param   integer $p_parentid  Parent node's identifier
     * @param   string  $p_title     Title
     * @param   string  $p_url       (optional) URL. Default to an empty string.
     * @param   string  $p_target    (optional) HTML target attribute. Defaults to an empty string.
     * @param   string  $p_icon      (optional) Icon. Defaults to an empty string.
     * @param   boolean $p_select    (optional) Select this node (1). Defaults to 0.
     * @param   string  $p_backImage (optional) If set, the node will have the specified background image. Defaults to an empty string.
     * @param   string  $p_tooltip   (optional) Tooltip. Defaults to an empty string.
     *
     * @return  Integer  Returns an integer with the new node ID on success.
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function add_node($p_id, $p_parentid, $p_title, $p_url = "", $p_target = "", $p_icon = "", $p_select = false, $p_backImage = "", $p_tooltip = "", $p_has_right = true, $p_cssclass = '')
    {
        global $g_dirs;

        if (strlen($p_backImage) > 0)
        {
            $p_backImage = $g_dirs['images'] . 'dtree/background/' . $p_backImage;
        } // if

        $l_temp = $this->m_tree_name . ".add(" . (int) $p_id . ", " . (int) $p_parentid . ", " . isys_glob_js_string(
                stripslashes($p_title)
            ) . ", " . (($p_has_right) ? isys_glob_js_string($p_url) : "'javascript:;'") . ", " . "'" . $p_tooltip . "', " . isys_glob_js_string(
                $p_target
            ) . ", " . isys_glob_js_string($p_icon) . ", " . isys_glob_js_string(
                $p_icon
            ) . ", " . "''," . "''," . ((int) $p_select) . "," . "'" . $p_backImage . "'," . "'" . $p_cssclass . "');";

        if ($p_parentid > -1)
        {
            $this->m_tree_childs[isys_helper_textformat::clean_string(strip_tags($p_title)) . count($this->m_tree_childs)] = $l_temp;
        }
        else
        {
            $this->m_tree_output[] = $l_temp;
        } // if

        return $p_id;
    }

    /**
     * Removes a node ( with a dirty hack ;-( ).
     *
     * @param  Integer $p_id The ID of the node, which shall be removed.
     *
     * @return Boolean
     */
    public function remove_node($p_id)
    {
        if ($p_id > 0)
        {
            foreach ($this->m_tree_childs as $l_k => $l_c)
            {
                if (strstr($l_c, ".add($p_id,"))
                {
                    unset($this->m_tree_childs[$l_k]);

                    return true;
                } // if
            } // foreach
        } // if

        return false;
    } // function

    /**
     * Configures the tree. Possible keys are:
     *    target            (default: null)
     *    folderLinks        (default: true)
     *    useSelection    (default: true)
     *    useCookies        (default: false)
     *    useLines        (default: true)
     *    useIcons        (default: true)
     *    useStatusText    (default: false)
     *    closeSameLevel    (default: false)
     *    inOrder            (default: false)
     *
     * @param   String $p_key
     * @param   String $p_val
     *
     * @return  boolean
     */
    public function config($p_key, $p_val)
    {
        $l_posskeys = [
            "target",
            "folderLinks",
            "useSelection",
            "useCookies",
            "useLines",
            "useIcons",
            "useStatusText",
            "closeSameLevel",
            "inOrder",
        ];

        if (in_array($p_key, $l_posskeys))
        {
            $this->m_tree_output[] = $this->m_tree_name . ".config." . $p_key . "=" . $p_val . ";";

            return true;
        } // if

        return false;
    } // function

    /**
     * Processes the tree and returns it as string. Opens node specified by $p_opennode.
     *
     * @param   integer $p_opennode           Which node should be opened?
     * @param   string  $p_additional_process Optional for some extra javascript.
     *
     * @return  string
     */
    public function process($p_opennode = null, $p_additional_process = "")
    {
        if (empty($this->m_tree_search))
        {
            $this->set_tree_search(true);
        } // if

        // Sort array by keys.
        if ($this->m_tree_sort && is_array($this->m_tree_childs))
        {
            uksort($this->m_tree_childs, 'strnatcasecmp');
        } // if

        $l_tree_output = '<div id="' . $this->m_tree_name . '"></div>' . "\n";

        if (null !== $p_opennode)
        {
            $this->select_node_by_id($p_opennode);
        } // if

        if (empty($this->m_select_node))
        {
            $this->select_node_by_id(0);
        } // if

        $l_tree_output .= isys_glob_js_print(
            implode("\n", $this->m_tree_output) .
            implode("\n", $this->m_tree_childs) .
            "$('" . $this->m_tree_name . "').update(" . $this->m_tree_name . ");" . $this->m_select_node . $p_additional_process
        );

        return $l_tree_output;
    } // function

    /**
     * Clears the tree output stack.
     */
    public function reinit()
    {
        $this->init($this->m_tree_name);
    } // function

    /**
     * Defines if empty entries in the tree can be hidden or not.
     *
     * @param  boolean $p_value
     */
    public function set_tree_visibility($p_value = false)
    {
        global $g_comp_template;

        $g_comp_template->assign('bMenuTreeHideable', $p_value);
    } // function

    /**
     * @param idoit\Tree\Node[] $p_tree
     */
    private function recurse_payload($p_tree, isys_register $p_request)
    {
        foreach ($p_tree as $l_child)
        {
            $this->add_node(
                $l_child->id,
                $l_child->get_parent()->id,
                $l_child->title,
                $l_child->link,
                '',
                $l_child->image,
                $l_child->link == $p_request->{'BASE'} . ltrim($p_request->{'REQUEST'}, '/') ? 1 : 0,
                '',
                $l_child->tooltip,
                $l_child->accessRight,
                $l_child->cssClass
            );

            $this->recurse_payload($l_child->get_childs(), $p_request);
        }
    } // function

    /**
     * Initializes a tree with the given $p_name. The name has to be unique since it's used in JavaScript.
     *
     * @param  string $p_name
     */
    private function __construct($p_name)
    {
        $this->m_tree_name = $p_name;
    } // function
} // class