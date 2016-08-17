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
 * Tree visualization model.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_tree_model extends isys_visualization_model
{
    /**
     * Recursion method for getting all objects.
     *
     * @param   integer $p_obj
     * @param   mixed   $p_filter
     * @param   integer $p_profile
     * @param   boolean $p_by_master
     * @param   integer $p_start_id
     *
     * @return  isys_tree
     * @throws  isys_exception_general
     */
    public function recursion_run($p_obj, $p_filter = null, $p_profile = null, $p_by_master = true, $p_start_id = 0)
    {
        $l_tree    = new isys_tree();
        $l_profile = isys_factory::get_instance('isys_visualization_profile_model', $this->m_db)
            ->get_profile_config($p_profile);

        /* @var  isys_itservice_dao_algorithm $l_dao_algorithm */
        $l_dao_algorithm = isys_itservice_dao_algorithm::instance($this->m_db);

        // Get new root node for $l_object.
        $l_root = $l_dao_algorithm->clear_node_cache()
            ->format_node($p_obj);

        $l_root_data = $l_root->get_data()
            ->toArray();

        // Setting the root object.
        $l_root_data['data']['root-object'] = true;

        $l_root->set_data($l_root_data);

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
            // Walk through relations and build the tree.
            $l_dao_algorithm->set_filter($l_filter)
                ->relation_walk($l_root, $l_filter['level'] ?: null, $p_by_master);
        }
        catch (Exception $e)
        {
            isys_notify::warning($e->getMessage(), ['sticky' => true]);
        } // try

        // Modify subnode count.
        $l_root->set_subnodes($l_root->count());

        $l_tree->add($l_root);

        // Filter nodes using the visitor pattern.
        $l_visitor = new isys_visualization_tree_visitor($l_filter, $l_profile);

        $l_visitor->set_start_id($p_start_id);

        while ($l_tree->accept($l_visitor))
        {
            // filtering..
        } // while

        return $l_tree;
    } // function
} // class