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
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_tree_node_explorer extends isys_tree_node implements isys_tree_node_interface
{
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
     * @param   string $p_orn
     *
     * @return  isys_tree_node_explorer
     */
    public function set_orientation($p_orn)
    {
        $this->m_data['data']["\$orn"] = $p_orn;

        return $this;
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
} // class