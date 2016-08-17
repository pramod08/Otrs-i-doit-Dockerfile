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
 * Visualization export class for "GraphML".
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_export_graphml implements isys_visualization_export
{
    /**
     * This is our DOM document, which all the nodes get appended to.
     *
     * @var  DOMDocument
     */
    protected $m_dom = null;
    /**
     * Cache for "already exported nodes" to not export them more than once.
     *
     * @var  array
     */
    protected $m_exported_nodes = [];
    /**
     * Options array.
     *
     * @var  array
     */
    protected $m_options = [];
    /**
     * This variable will hold the complete algorithm tree.
     *
     * @var  isys_tree[]
     */
    protected $m_tree = [];

    /**
     * Export method.
     *
     * @return  string
     */
    public function export()
    {
        try
        {
            $this->m_dom = new DOMDocument('1.0', 'UTF-8');

            $l_graphml = $this->m_dom->createElement('graphml');
            $l_graphml->setAttribute('xmlns', 'http://graphml.graphdrawing.org/xmlns');
            $l_graphml->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $l_graphml->setAttribute('xsi:schemaLocation', 'http://graphml.graphdrawing.org/xmlns http://www.yworks.com/xml/schema/graphml/1.1/ygraphml.xsd');
            $l_graphml->setAttribute('xmlns:y', 'http://www.yworks.com/xml/graphml');

            $l_key_0 = $this->m_dom->createElement('key');
            $l_key_0->setAttribute('id', 'd0');
            $l_key_0->setAttribute('for', 'node');
            $l_key_0->setAttribute('yfiles.type', 'nodegraphics');

            $l_key_1 = $this->m_dom->createElement('key');
            $l_key_1->setAttribute('id', 'd1');
            $l_key_1->setAttribute('for', 'edge');
            $l_key_1->setAttribute('yfiles.type', 'edgegraphics');

            $l_graph = $this->m_dom->createElement('graph');
            $l_graph->setAttribute('id', 'G');
            $l_graph->setAttribute('edgedefault', 'undirected');

            $i = 0;

            // Because some visualizations will render multiple trees, we need this.
            foreach ($this->m_tree as $l_tree)
            {
                // Now we iterate over our tree items to build the complete content.
                foreach ($l_tree->all_nodes() as $l_node)
                {
                    $l_node_data = $l_node->get_data();

                    if (!isset($this->m_exported_nodes[$l_node_data['data']['obj_id']]))
                    {
                        $l_graph->appendChild(
                            $this->render_node(
                                [
                                    'id'    => $l_node_data['data']['obj_id'],
                                    'color' => $l_node_data['data']['obj_type_color'],
                                    'label' => $l_node_data['data']['obj_type_title'] . PHP_EOL . $l_node_data['data']['obj_title']
                                ]
                            )
                        );

                        $this->m_exported_nodes[$l_node_data['data']['obj_id']] = true;
                    } // if

                    if ($l_node->has_children())
                    {
                        foreach ($l_node->get_childs() as $l_child_node)
                        {
                            $l_child_node_data = $l_child_node->get_data();

                            $l_graph->appendChild(
                                $this->render_edge(
                                    [
                                        'id'     => $i,
                                        'source' => $l_node_data['data']['obj_id'],
                                        'target' => $l_child_node_data['data']['obj_id']
                                    ]
                                )
                            );

                            $i++;
                        }
                    }
                } // foreach
            } // foreach

            $l_graphml->appendChild($l_key_0);
            $l_graphml->appendChild($l_key_1);
            $l_graphml->appendChild($l_graph);

            $this->m_dom->appendChild($l_graphml);

            return $this->m_dom->saveXML();
        }
        catch (Exception $e)
        {
            return '<pre>' . var_export($e, true) . '</pre>';
        } // try
    } // function

    /**
     * Static factory method for.
     *
     * @return  isys_visualization_export_graphml
     */
    public static function factory()
    {
        return new self;
    } // function

    /**
     * Initialization method.
     *
     * @param   array $p_options
     *
     * @throws  isys_exception_general
     * @return  isys_visualization_export
     */
    public function init(array $p_options = [])
    {
        global $g_comp_database;

        $this->m_options = $p_options;

        $l_type_class = 'isys_visualization_' . $this->m_options['type'] . '_model';

        if (!class_exists($l_type_class))
        {
            throw new isys_exception_general('Graph type "' . $this->m_options['type'] . '" does not exist!');
        } // if

        $this->m_tree[] = isys_factory::get_instance($l_type_class, $g_comp_database)
            ->recursion_run($this->m_options['object-id'], $this->m_options['service-filter-id'], $this->m_options['profile-id']);

        if ($l_type_class == 'isys_visualization_tree_model')
        {
            $this->m_tree[] = isys_factory::get_instance('isys_visualization_tree_model', $g_comp_database)
                ->recursion_run($this->m_options['object-id'], $this->m_options['service-filter-id'], $this->m_options['profile-id'], false);
        } // if

        return $this;
    } // function

    /**
     * @param   array $p_data
     *
     * @return  DOMElement
     */
    protected function render_node(array $p_data)
    {
        // Prepare the node content.
        $l_node       = $this->m_dom->createElement('node');
        $l_node_data  = $this->m_dom->createElement('data');
        $l_shape_node = $this->m_dom->createElement('y:ShapeNode');
        $l_geometry   = $this->m_dom->createElement('y:Geometry');
        $l_geometry->setAttribute('width', '120');
        $l_geometry->setAttribute('height', '40');
        $l_fill = $this->m_dom->createElement('y:Fill');
        $l_fill->setAttribute('color', $p_data['color']);
        $l_fill->setAttribute('transparent', 'false');
        $l_borderstyle = $this->m_dom->createElement('y:BorderStyle');
        $l_borderstyle->setAttribute('type', 'line');
        $l_borderstyle->setAttribute('width', '1.0');
        $l_borderstyle->setAttribute('color', '#000000');
        $l_nodelabel = $this->m_dom->createElement('y:NodeLabel', $p_data['label'] ?: null);
        $l_shape     = $this->m_dom->createElement('y:Shape');
        $l_shape->setAttribute('type', 'rectangle');

        // Add the child elements to the node.
        $l_shape_node->appendChild($l_geometry);
        $l_shape_node->appendChild($l_fill);
        $l_shape_node->appendChild($l_borderstyle);
        $l_shape_node->appendChild($l_nodelabel);
        $l_shape_node->appendChild($l_shape);

        $l_node_data->setAttribute('key', 'd0');
        $l_node_data->appendChild($l_shape_node);

        $l_node->setAttribute('id', 'n' . $p_data['id']);
        $l_node->appendChild($l_node_data);

        return $l_node;
    } // function

    /**
     * @param   array $p_data
     *
     * @return  DOMElement
     */
    protected function render_edge(array $p_data)
    {
        // Prepare the edge content.
        $l_edge       = $this->m_dom->createElement('edge');
        $l_edge_data  = $this->m_dom->createElement('data');
        $l_polyline   = $this->m_dom->createElement('y:PolyLineEdge');
        $l_line_style = $this->m_dom->createElement('y:LineStyle');
        $l_line_style->setAttribute('type', 'line');
        $l_line_style->setAttribute('width', '1.0');
        $l_line_style->setAttribute('color', '#000000');
        $l_arrows = $this->m_dom->createElement('y:Arrows');
        $l_arrows->setAttribute('source', 'none');
        $l_arrows->setAttribute('target', 'standard');
        $l_edge_label = $this->m_dom->createElement('y:EdgeLabel', $p_data['label'] ?: null);
        $l_bend_style = $this->m_dom->createElement('y:BendStyle');
        $l_bend_style->setAttribute('smoothed', 'false');

        // Add the child elements to the edge.
        $l_polyline->appendChild($l_line_style);
        $l_polyline->appendChild($l_arrows);
        $l_polyline->appendChild($l_edge_label);
        $l_polyline->appendChild($l_bend_style);

        $l_edge_data->setAttribute('key', 'd1');
        $l_edge_data->appendChild($l_polyline);

        $l_edge->setAttribute('id', 'e' . $p_data['id']);
        $l_edge->setAttribute('source', 'n' . $p_data['source']);
        $l_edge->setAttribute('target', 'n' . $p_data['target']);
        $l_edge->appendChild($l_edge_data);

        return $l_edge;
    } // function
} // class