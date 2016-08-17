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
 * Graph visualization model.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_graph_model extends isys_visualization_model
{
    /**
     * Recursion method for getting all objects.
     *
     * @param   integer $p_obj
     * @param   mixed   $p_filter
     * @param   integer $p_profile
     *
     * @return  isys_tree
     * @throws  isys_exception_general
     */
    public function recursion_run($p_obj, $p_filter = null, $p_profile = null)
    {
        $l_graph   = new isys_graph();
        $l_profile = isys_factory::get_instance('isys_visualization_profile_model', $this->m_db)
            ->get_profile_config($p_profile);

        // Get new root node for $l_object.
        $l_root = $this->clear_node_cache()
            ->format_node($p_obj);

        if (is_array($p_filter))
        {
            $l_filter = $p_filter;
        }
        else
        {
            $l_filter = $this->load_service_filter($p_filter);
        } // if

        try
        {
            // Walk through relations and build the tree (by master).
            $this->set_filter($l_filter)
                ->relation_walk($l_root, $l_filter['level'] ?: null, false);
            // Also run through the other relations (by slave).
            $this->relation_walk($l_root, $l_filter['level'] ?: null, true);
        }
        catch (Exception $e)
        {
            isys_notify::warning($e->getMessage(), ['sticky' => true]);
        } // try

        // Modify subnode count.
        $l_root->set_subnodes($l_root->count());

        $l_graph->add($l_root);

        // Filter nodes using the visitor pattern.
        $l_visitor = new isys_visualization_graph_visitor($l_filter, $l_profile);

        while ($l_graph->accept($l_visitor))
        {
            // filtering..
        } // while

        return $l_graph;
    } // function

    /**
     * Method for formatting the single object nodes.
     *
     * @param   integer $p_obj_id
     * @param   boolean $p_doubling
     * @param   array   $p_relation
     *
     * @return  isys_graph_node
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function format_node($p_obj_id, $p_doubling = false, $p_relation = [])
    {
        $l_object_type_title = null;

        // In case of a software relation, we are handling a "catalog object" and want to display the relation itself.
        if ($p_relation['isys_catg_relation_list__isys_relation_type__id'] == C__RELATION_TYPE__SOFTWARE)
        {
            $l_object_type_title = _L('LC__MODULE__CMDB__VISUALIZATION__SOFTWARE_RELATION');
        } // if

        $l_obj_data = $this->retrieve(
            'SELECT * FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';'
        )
            ->get_row();

        return new isys_graph_node(
            [
                'id'       => $p_obj_id,
                'name'     => trim($l_obj_data['isys_obj__title']),
                'children' => [],
                'data'     => [
                    'doubling'        => (bool) $p_doubling,
                    'obj_id'          => $p_obj_id,
                    'obj_title'       => trim($l_obj_data['isys_obj__title']),
                    'obj_type_id'     => $l_obj_data['isys_obj__isys_obj_type__id'],
                    'obj_type_title'  => $l_object_type_title ?: _L($l_obj_data['isys_obj_type__title']),
                    'obj_type_color'  => '#' . $l_obj_data['isys_obj_type__color'],
                    'relation_obj_id' => $p_relation['isys_catg_relation_list__isys_obj__id']
                ]
            ]
        );
    } // function
} // class