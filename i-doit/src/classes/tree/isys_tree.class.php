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
 * i-doit tree structure.
 * Object structure:
 *
 *  isys_tree
 *    +- array(isys_tree_collection)
 *          +- isys_tree_node
 *                +- array(isys_tree_collection)
 *                      +- isys_tree_node
 *                      +- isys_tree_node
 *                +- array(isys_tree_collection)
 *                      +- isys_tree_node
 *          +- isys_tree_node
 *                +- array(isys_tree_collection)
 *                +- array(isys_tree_collection)
 *                +- array(isys_tree_collection)
 *    +- array(isys_tree_collection)
 *          +- isys_tree_node
 *          +- isys_tree_node
 *    +- array(isys_tree_collection)
 *          +- isys_tree_node
 *
 * Data format:
 *
 * array
 *   'id'    => int 0
 *   'name'  => string 'Root-Lokation'
 *   'data'  =>
 * array
 *   'id'        => int 1
 *   'data'      => array
 *   'children'  =>
 * array
 *   0 =>
 *   array
 *     'id'        => string '13'
 *     'name'      => string 'Gebäude A'
 *     'data'      => array
 *     'children'  =>
 * array
 *   0 =>
 *   array
 *     'id'    => string '14'
 *     'name'  => string 'Raum 101'
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_tree extends RecursiveArrayIterator implements JsonSerializable
{
    /**
     * Tree children.
     *
     * @var  isys_tree_node[]
     */
    protected $m_children;

    /**
     *
     * @param   isys_tree_node $l_node
     *
     * @return  integer
     */
    public function indexOf(isys_tree_node $l_node)
    {
        foreach ($this->m_children as $p_index => $_node)
        {
            if ($l_node === $_node)
            {
                return $p_index;
            }  // if
        } // foreach

        return null;
    } // function

    /**
     * Implementation of IteratorAggregate::getIterator()
     *
     * @return  array  iterator object for looping
     */
    public function getIterator()
    {
        return new RecursiveArrayIterator($this->m_children);
    } // function

    /**
     * Adds a new node to the tree. Data can contain any type of additional data for the node.
     *
     * @param   isys_tree_node $p_root_node
     *
     * @return  isys_tree_node
     */
    public function add(isys_tree_node $p_root_node)
    {
        return $this->m_children[] = $p_root_node;
    } // function

    /**
     * Visitor pattern
     *
     * @param   isys_tree_visitor_interface $p_visitor
     *
     * @return  mixed
     */
    public function accept(isys_tree_visitor_interface $p_visitor)
    {
        return $p_visitor->visit($this);
    } // function

    /**
     * Return all child nodes.
     *
     * @return  isys_tree_node[]
     */
    public function get_childs()
    {
        return $this->m_children;
    } // function

    /**
     * Removes a node by isys_tree_node.
     *
     * @param   isys_tree_node $p_node
     * @param   boolean        $p_recursive
     *
     * @return  boolean
     */
    public function remove_node(isys_tree_node $p_node, $p_recursive = false)
    {
        /** $l_mynodes isys_tree_node[] */
        $l_mynodes = [];

        foreach ($this->m_children as $l_index => $l_node)
        {
            if ($l_node === $p_node)
            {
                $p_node->set_parent(null);

                unset($this->m_children[$l_index]);

                return true;
            }
            else if ($p_recursive && $l_node->has_children())
            {
                $l_mynodes[] = $l_node;
            } // if
        } // foreach

        // Go thru searching those nodes that have children.
        if (count($l_mynodes) > 0)
        {
            /** @var $l_node isys_tree_node */
            foreach ($l_mynodes as $l_node)
            {
                $l_node->remove($p_node, true);
            } // foreach
        } // if

        $p_node->set_parent(null);

        return $this;
    } // function

    /**
     * Checks if tree has children.
     *
     * @return  boolean
     */
    public function has_children()
    {
        return count($this->m_children) > 0;
    } // function

    /**
     * Convert tree structure into JSON.
     *
     * @return  string
     */
    public function toJSON()
    {
        return isys_format_json::encode($this->toArray());
    } // function

    /**
     * Convert tree structure into Array.
     *
     * @return  array
     */
    public function toArray()
    {
        $l_return = [];

        if (is_array($this->m_children))
        {
            foreach ($this->m_children as $l_child)
            {
                $l_return             = $l_child->get_data();
                $l_return['children'] = $l_child->toArray();
            } // foreach
        }

        return $l_return;
    } // function

    /**
     * @return  isys_tree_node[]
     */
    public function all_nodes()
    {
        $nodes = [];

        foreach ($this->m_children as $subnode)
        {
            $nodes[] = $subnode;

            foreach ($subnode->descendants() as $subsubnode)
            {
                $nodes[] = $subsubnode;
            } // foreach
        } // foreach

        return $nodes;
    } // function

    /**
     * Called from isys_tree_node children.
     *
     * @return  array
     */
    public function anscestors()
    {
        return [];
    } // function

    /**
     *
     * @return  string
     */
    public function __toString()
    {
        $str = [];

        foreach ($this->all_nodes() as $node)
        {
            $indent1st = str_repeat('  ', $node->level() - 1) . ($node->has_children() ? '+-' : '|-') . ' ';
            $indent    = str_repeat('  ', ($node->level() - 1) + 2);
            $node      = (string) $node;
            $str[]     = "$indent1st" . str_replace("\n", "$indent\n  ", $node);
        } // foreach

        return join("\n", $str);
    } // function

    /**
     *
     * @return  array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    } // function

    /**
     * Countable::count() interface (PHP5.1+).
     *
     * @param   boolean $p_include_childs
     *
     * @return  integer
     */
    public function count($p_include_childs = false)
    {
        if ($p_include_childs)
        {
            $l_count = count($this->m_children);

            foreach ($this->m_children as $l_node)
            {
                $l_count += $l_node->count(true);
            } // foreach

            return $l_count;
        }
        else
        {
            return count($this->m_children);
        } // if
    } // function

    /**
     * Implementation of ArrayAccess:offsetExists()
     * isset(isys_tree_collection);
     *
     * @param   mixed $p_key
     *
     * @return  boolean
     */
    public function offsetExists($p_key)
    {
        return isset($this->m_children[$p_key]);
    } // function

    /**
     * Implementation of ArrayAccess:offsetGet()
     * isys_tree_collection[$p_key];
     *
     * @param   mixed $p_key
     *
     * @return  mixed
     */
    public function offsetGet($p_key)
    {
        return $this->m_children[$p_key];
    } // function

    /**
     * Implementation of ArrayAccess:offsetSet()
     * isys_tree_collection[$p_key] = "foobar";
     *
     * @param  mixed $p_key
     * @param  mixed $value
     */
    public function offsetSet($p_key, $value)
    {
        $this->m_children[$p_key] = $value;
    } // function

    /**
     * Implementation of ArrayAccess:offsetUnset()
     * unset(isys_tree_collection);
     *
     * @param  mixed $p_key
     */
    public function offsetUnset($p_key)
    {
        unset($this->m_children[$p_key]);
    } // function

    /**
     * Constructor.
     *
     * @param  [isys_tree_node $p_root_node]
     */
    public function __construct($p_root_node = null)
    {
        if ($p_root_node instanceof isys_tree_node)
        {
            $this->add($p_root_node);
        } // if
    } // function
} // class