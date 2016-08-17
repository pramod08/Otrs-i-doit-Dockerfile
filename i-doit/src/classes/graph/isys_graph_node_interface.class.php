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
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
interface isys_graph_node_interface
{
    /**
     * Visitor pattern
     *
     * @param  isys_tree_visitor_interface $p_visitor
     *
     * @return  mixed
     */
    public function accept(isys_graph_visitor_interface $p_visitor);

    /**
     * Adds a new node to the tree. Data can contain any type of additional data for the node.
     *
     * @param   isys_graph_node $p_root_node
     *
     * @return  isys_graph_node
     */
    public function add(isys_graph_node $p_root_node);

    /**
     * Returns any node above (parent, grandparent, ...) this node
     *
     * @param bool $p_include_self
     *
     * @return isys_graph_node[] array of nodes, sorted by nearest
     */
    public function anscestors($p_include_self = false);

    /**
     * @param isys_graph_node $p_node
     *
     * @return isys_graph_node
     */
    public function copy_to(isys_graph_node $p_node);

    /**
     * Get all descendants of current node.
     *
     * @param   boolean $p_include_self
     *
     * @return  array
     */
    public function descendants($p_include_self = false);

    /**
     * Additional Data of the current node
     *
     * @return mixed
     */
    public function get_data();

    /**
     * Name of the current node
     *
     * @return string
     */
    public function get_name();

    /**
     * @return isys_graph_node
     */
    public function get_parents();

    /**
     * Checks for an existing child
     *
     * @return bool
     */
    public function has($p_node);

    /**
     * @return bool
     */
    public function has_children();

    /**
     * @param   isys_graph_node $p_parent
     *
     * @return  boolean
     */
    public function is_child_of(isys_graph_node $p_parent);

    /**
     * @param   isys_graph_node $p_node
     *
     * @return  mixed
     */
    public function move_to(isys_graph_node $p_node);

    /**
     * Remove node
     *
     * @return mixed
     */
    public function remove();

    /**
     * Set parent (isys_graph_node)
     *
     * @param isys_graph_node $parent
     *
     * @return isys_graph_node
     */
    public function set_parent(isys_graph_node $parent = null);
} // class