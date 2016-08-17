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
 * Tree visitor class.
 *
 * @package     modules
 * @subpackage  analytics
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_tree_visitor implements isys_tree_visitor_interface
{
    /**
     * This counter will be used to reset the node IDs.
     *
     * @var  integer
     */
    private static $i = 0;

    /**
     * Filter array.
     *
     * @var  array
     */
    private $m_filter = [];

    /**
     * Array which holds the profile configuration.
     *
     * @var  array
     */
    private $m_profile = [];

    /**
     * Method for retrieving the "last inserted ID".
     *
     * @return  integer
     */
    public static function get_last_id()
    {
        return static::$i;
    } // function

    /**
     * Setting the start ID for the data.
     *
     * @param   integer $p_id
     *
     * @return  isys_visualization_tree_visitor
     */
    public function set_start_id($p_id)
    {
        static::$i = (int) $p_id;

        return $this;
    } // function

    /**
     * @param   isys_tree|isys_tree_node $p_node
     *
     * @return  mixed
     */
    public function visit(isys_tree $p_node)
    {
        global $g_comp_database;

        $l_return = [];

        if ($p_node instanceof isys_tree_node)
        {
            $l_data = $p_node->get_data();

            // Level cut off.
            if (isset($this->m_filter['level']) && $this->m_filter['level'] > 0)
            {
                if ($p_node->level() > $this->m_filter['level'] + 1)
                {
                    $l_return[] = $this->remove($p_node);
                } // if
            } // if

            // Filter by relation type.
            if (isset($this->m_filter['relation-type']) && is_array($this->m_filter['relation-type']) && isset($l_data['data']['relation']['type']))
            {
                foreach ($this->m_filter['relation-type'] as $l_hideRelation)
                {
                    if (defined($l_hideRelation))
                    {
                        if (constant($l_hideRelation) == $l_data['data']['relation']['type'])
                        {
                            $this->remove($p_node);
                        } // if
                    } // if
                } // foreach
            } // if

            // Object type cut off.
            if (isset($this->m_filter['object-type']) && is_array($this->m_filter['object-type']) && count($this->m_filter['object-type']) > 0)
            {
                if (in_array($l_data['data']['objTypeID'], $this->m_filter['object-type']))
                {
                    $l_return[] = $this->remove($p_node);
                } // if
            } // if

            // Here we append the "profile" specific data to the node.
            if (is_array($this->m_profile['rows']))
            {
                foreach ($this->m_profile['rows'] as $l_profile)
                {
                    $l_data['content'][$l_profile['option']] = isys_factory::get_instance('isys_visualization_profile_model', $g_comp_database)
                        ->get_profile_options_content($p_node, $l_profile['option']);
                } // foreach
            } // if

            // We need to set the ID to something new, because object-IDs confuse D3 :(
            $l_data['id'] = ++static::$i;

            $p_node->set_data($l_data->toArray());
        } // if

        $l_child_cache = [];

        // Iterate through childs and call accept as well.
        foreach ($p_node->get_childs() as $l_child)
        {
            /*
            if ($p_node instanceof isys_tree_node && $l_child instanceof isys_tree_node)
            {
                if (isset($l_child_cache[$p_node->get_data('id') . '-' . $l_child->get_data('id')]))
                {
                    $l_child->remove();
                } // if

                $l_child_cache[$p_node->get_data('id') . '-' . $l_child->get_data('id')] = true;
            } // if
            */

            $l_return = array_merge($l_return, $l_child->accept($this));
        } // foreach

        // @todo  Try to avoid this, because the "show only it-service" option will not work
        if ($p_node->count() === 0)
        {
            if ($p_node->get_name() == 'Root')
            {
                return [];
            }

            $l_parent = $p_node->get_parent();

            if (method_exists($l_parent, 'get_data'))
            {
                $l_data = $p_node->get_parent()
                    ->get_data();

                if ($l_data['id'] == -1)
                {
                    return [];
                } // if
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * @param   isys_tree_node $p_node
     *
     * @return  isys_tree_node
     */
    private function remove(isys_tree_node $p_node)
    {
        $l_descendants = $p_node->descendants(true);

        array_walk(
            $l_descendants,
            function (isys_tree_node $p_desc)
            {
                // Remove node.
                $p_desc->remove();
            }
        );

        return $p_node;
    } // function

    /**
     * Construct visitor with special filter. Filter structure:
     * array(
     *   'level' => int,
     *   'relation-type' array of relation-types as string constants
     * )
     *
     * @param  array $p_filter
     * @param  array $p_profile
     */
    public function __construct(array $p_filter, array $p_profile = [])
    {
        $this->m_filter  = $p_filter;
        $this->m_profile = $p_profile;
    } // function
} // class