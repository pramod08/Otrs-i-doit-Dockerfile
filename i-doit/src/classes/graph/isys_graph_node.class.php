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
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_graph_node extends isys_graph implements isys_graph_node_interface
{
    /**
     * Variable that holds all exported nodes.
     *
     * @var  array
     */
    protected static $m_exported = [];
    protected static $m_iterated = [];
    /**
     * Internal tree data.
     *
     * @var  array
     */
    protected $m_data;

    /**
     *
     * @return  integer
     */
    public function get_id()
    {
        return (int) $this->m_data['id'];
    } // function

    /**
     *
     * @param   string $p_name
     *
     * @return  isys_tree_node_explorer
     */
    public function set_name($p_name)
    {
        $this->m_data['name'] = $p_name;

        return $this;
    } // function

    /**
     *
     * @param   integer $p_subnode_count
     *
     * @return  isys_tree_node_explorer
     */
    public function set_subnodes($p_subnode_count)
    {
        $this->m_data['data']['subNodes'] = $p_subnode_count;

        return $this;
    } // function

    public function get_neighbors()
    {
        $l_current   = $this;
        $l_neighbors = [];

        foreach ($this->get_parents() as $l_parent)
        {
            $l_neighbors = $l_neighbors + $l_parent->get_childs();
        } // foreach

        // Uses array_values to reset indexes after filter.
        return array_values(
            array_filter(
                $l_neighbors,
                function ($p_child) use ($l_current)
                {
                    return $p_child != $l_current;
                }
            )
        );
    } // function

    /**
     * @param array $p_data
     *
     * @return $this
     */
    public function merge_data(array $p_data)
    {
        if ($this->m_data)
        {
            $this->m_data = new isys_array(array_merge_recursive($this->m_data->toArray(), $p_data));
        }
        else
        {
            $this->m_data = $p_data;
        }

        return $this;
    } // function

    /**
     * Remove a node
     *
     * @param isys_graph_node $p_node
     *
     * @return $this
     */
    public function remove_node(isys_graph_node $p_node)
    {
        foreach ($this->m_children as $l_key => $l_child)
        {
            if ($p_node === $l_child)
            {
                unset($this->m_children[$l_key]);
            } // if
        } // foreach

        $p_node->set_parent(null);

        return $this;
    } // function

    /**
     * Return first node
     *
     * @return isys_graph_node
     */
    public function first_node()
    {
        return !empty($this->m_children) ? $this->m_children[0] : null;
    } // function

    /**
     * Return last node
     *
     * @return isys_graph_node
     */
    public function last_node()
    {
        return !empty($this->m_children) ? $this->m_children[count($this->m_children) - 1] : null;
    } // function

    /**
     * Removes a node at index $p_index and returns that node
     *
     * @param int $p_index
     *
     * @return isys_graph_node
     */
    public function remove_at($p_index)
    {
        $l_node = null;

        if (isset($this->m_children[$p_index]) && $this->m_children[$p_index] instanceof isys_graph_node)
        {
            $l_node           = $this->m_children[$p_index]->remove();
            $this->m_children = array_values($this->m_children);
        }

        return $l_node;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->m_data['name'];
    }

    /**
     * Adds a new subnode
     *
     * @param isys_graph_node $p_node
     *
     * @return isys_graph_node
     */
    public function add(isys_graph_node $p_node)
    {
        $this->m_children[] = $p_node;

        return $this;
    }

    /**
     * Returns any node above (parent, grandparent, ...) this node
     *
     * @param bool $p_include_self
     *
     * @return isys_graph_node[] array of nodes, sorted by nearest
     */
    public function anscestors($p_include_self = false)
    {
        return $p_include_self ? [$this] : [];
    }

    /**
     * @return $this
     */
    public function remove()
    {
        foreach ($this->get_parents() as $l_parent)
        {
            $l_parent->remove_node($this);
        } // foreach

        return $this;
    }

    /**
     * @return  array
     */
    public function toArray()
    {
        if (!isset(self::$m_iterated[$this->get_id()]) && $this->has_children())
        {
            self::$m_iterated[$this->get_id()] = true;

            /* @var  $l_node  isys_graph_node */
            foreach ($this->m_children as $l_node)
            {
                if (!isset(self::$m_exported[$l_node->get_id()]))
                {
                    self::$m_exported[$l_node->get_id()]             = $l_node->get_data();
                    self::$m_exported[$l_node->get_id()]['children'] = [];

                    if ($l_node->has_children())
                    {
                        $l_node->toArray();

                        foreach ($l_node->m_children as $l_child)
                        {
                            if ($l_node->get_id() != $l_child->get_id())
                            {
                                self::$m_exported[$l_node->get_id()]['children'][] = $l_child->get_id();
                            } // if
                        } // foreach
                    } // if
                } // if
            } // foreach
        } // if

        return self::$m_exported;
    }

    /**
     * Walk through children and calls function $p_funcname
     *
     * @param callable $p_funcname
     * @param mixed    $p_parameter
     */
    public function walk($p_funcname, $p_parameter = null)
    {
        foreach ($this->m_children as $l_node)
        {
            /**
             * @var $l_node isys_graph_node
             */
            call_user_func($p_funcname, $l_node, $p_parameter);

            if ($l_node->has_children())
            {
                $l_node->walk($p_funcname, $p_parameter);
            } // if
        } // foreach
    }

    /**
     * Searches for a node namy by string $p_search
     *
     * @param string  $p_search
     * @param boolean $p_strict
     *
     * @return array
     */
    public function search($p_search, $p_strict = false)
    {
        $l_results  = [];
        $l_nodeList = $this->all_nodes();

        foreach ($l_nodeList as $l_node)
        {
            $l_data = $l_node->get_name();

            if ($p_strict ? ($l_data === $p_search) : ($l_data == $p_search))
            {
                $l_results[] = $l_node;
            } // if
        } // foreach

        return $l_results;
    } // function

    /**
     * Method for setting data, after construction.
     *
     * @param   array $p_data
     *
     * @return  isys_graph_node
     */
    public function set_data(array $p_data)
    {
        $this->m_data = new isys_array($p_data);

        return $this;
    } // function

    /**
     * @param   isys_graph_node $p_node
     *
     * @return  isys_graph_node
     */
    public function copy_to(isys_graph_node $p_node)
    {
        $p_node->add(clone $this);

        return $this;
    } // function

    /**
     * Get all descendants of current node
     *
     * @param bool $p_include_self
     *
     * @return array
     */
    public function descendants($p_include_self = false)
    {
        $l_descendants = $p_include_self ? [$this] : [];

        /**
         * @var $l_childnode isys_graph_node
         */
        foreach ($this->m_children as $l_childnode)
        {
            $l_descendants[] = $l_childnode;

            if ($l_childnode->has_children())
            {
                $l_descendants = array_merge($l_descendants, $l_childnode->descendants());
            }
        }

        return $l_descendants;
    } // function

    /**
     * Get data method.
     *
     * @param   string $p_item
     * @param   mixed  $p_default
     *
     * @return  mixed
     */
    public function get_data($p_item = null, $p_default = null)
    {
        if ($p_item === null)
        {
            return $this->m_data;
        } // if

        if (isset($this->m_data[$p_item]))
        {
            return $this->m_data[$p_item];
        }
        else
        {
            return $p_default;
        } // if
    } // function

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->m_data['name'] ?: '';
    } // function

    /**
     * @return  isys_graph_node[]
     */
    public function get_parents()
    {
        // Graph nodes have no parents :(

        return [];
    } // function

    /**
     * Checks for an existing child
     *
     * @param isys_graph_node $p_node
     *
     * @return bool
     */
    public function has($p_node)
    {
        return count(
            array_filter(
                $this->m_children,
                function ($p_child) use ($p_node)
                {
                    return $p_child === $p_node;
                }
            )
        ) > 0 ? true : false;
    } // function

    /**
     * @param   isys_graph_node $p_parent
     *
     * @return  boolean
     */
    public function is_child_of(isys_graph_node $p_parent)
    {
        foreach ($this->get_parents() as $l_parent)
        {
            if ($l_parent === $p_parent)
            {
                return true;
            } // if
        } // foreach

        return false;
    } // function

    /**
     * @param   isys_graph_node $p_node
     *
     * @return  isys_graph_node
     */
    public function move_to(isys_graph_node $p_node)
    {
        $this->remove();
        $p_node->add($this);

        return $this;
    } // function

    /**
     * @param isys_graph_node $p_parent
     *
     * @return isys_graph_node
     */
    public function set_parent(isys_graph_node $p_parent = null)
    {
        // Graph nodes have no parents :(

        return $this;
    } // function

    /**
     * @param array $p_data
     */
    public function __construct(array $p_data)
    {
        $this->m_data     = new isys_array($p_data);
        $this->m_children = [];
    } // function
} // class