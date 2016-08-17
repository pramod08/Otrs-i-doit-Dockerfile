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
 * CMDB UI: Global category cabling.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_virtual_cabling extends isys_cmdb_ui_category_g_virtual
{
    /**
     * @var  isys_component_tree
     */
    private $m_tree;

    /**
     *
     * @param   array   $p_cable_run
     * @param   string  $p_separator
     * @param   boolean $p_bold
     * @param   string  $p_chain
     *
     * @return  string
     */
    public function get_chain($p_cable_run, $p_separator = " &rarr; ", $p_bold = null, $p_chain = "")
    {
        // Print out object and connection title.
        $p_chain .= " " . $p_separator . " <a href=\"" . $p_cable_run["LINK"] . "\">" . $p_cable_run["CONNECTOR_TITLE"] . " (" . $p_cable_run["OBJECT_TITLE"] . ") " . "</a>";

        if (is_array($p_cable_run["CONNECTION"]))
        {
            // Recurse into this connection.
            $p_chain = $this->get_chain($p_cable_run["CONNECTION"], $p_separator, $p_bold, $p_chain);
        }
        else if (is_array($p_cable_run["SIBLING"]))
        {
            // Every siblings needs a new TD for indentation.
            $p_chain .= "</td><td>";

            foreach ($p_cable_run["SIBLING"] as $l_sibling)
            {
                // Recurse to get the complete chain.
                $p_chain = $this->get_chain($l_sibling, $p_separator, $p_bold, $p_chain);

                // Indent, if siblings are more than one.
                if (count($p_cable_run["SIBLING"]) > 1)
                {
                    $p_chain .= "</tr><tr rowspan=\"" . count($p_cable_run["SIBLING"]) . "\">";

                    for ($i = 1;$i <= count($p_cable_run["SIBLING"]) - 1;$i++)
                    {
                        $p_chain .= "<td></td>";
                    } // for

                    $p_chain .= "<td>";
                } // if
            } // foreach
        } // if

        return $p_chain;
    } // function

    /**
     * @param           $p_cable_run
     * @param   string  $p_separator
     * @param   array   $p_chain
     *
     * @return  array
     */
    public function get_chain_as_array($p_cable_run, $p_separator = " &larr; ", $p_chain = [])
    {
        if ($p_cable_run["OBJECT_TITLE"])
        {
            $p_chain[] = "<a href=\"" . $p_cable_run["LINK"] . "\">" . $p_cable_run["CONNECTOR_TITLE"] . " (" . $p_cable_run["OBJECT_TITLE"] . ") " . "</a>";

            if (is_array($p_cable_run["CONNECTION"]))
            {
                $p_chain = $this->get_chain_as_array($p_cable_run["CONNECTION"], $p_separator, $p_chain);
            }
            else if (is_array($p_cable_run["SIBLING"]))
            {

                $l_sibling = $p_cable_run["SIBLING"][0];
                $p_chain   = $this->get_chain_as_array($l_sibling, $p_separator, $p_chain);
            } // if
        } // if

        return $p_chain;
    } // function

    /**
     * @param        $p_cable_run
     * @param string $p_separator
     * @param array  $p_chain
     *
     * @return array
     */
    public function get_carousel_chain($p_cable_run, $p_separator = " &larr; ", $p_chain = [])
    {
        if ($p_cable_run["OBJECT_TITLE"])
        {

            $l_cable_run = $p_cable_run;
            unset($l_cable_run["CONNECTION"]);
            unset($l_cable_run["SIBLING"]);

            $p_chain[] = $l_cable_run;

            if (is_array($p_cable_run["CONNECTION"]))
            {

                $p_chain = $this->get_carousel_chain($p_cable_run["CONNECTION"], $p_separator, $p_chain);
            }
            else if (is_array($p_cable_run["SIBLING"]))
            {

                $l_sibling = $p_cable_run["SIBLING"][0];
                $p_chain   = $this->get_carousel_chain($l_sibling, $p_separator, $p_chain);
            }
        }

        return $p_chain;
    } // function

    /**
     * @param        $p_cable_run
     * @param string $p_separator
     *
     * @return string
     */
    public function get_chain_reversed($p_cable_run, $p_separator = " &larr; ")
    {
        $l_chain = array_reverse($this->get_chain_as_array($p_cable_run));

        return implode($p_separator, $l_chain);
    } // function

    /**
     *
     * @param   isys_cmdb_dao_category_g_virtual_cabling $p_cat
     *
     * @author  Dennis Stuecken
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_template;

        $l_dao_connector = new isys_cmdb_dao_category_g_connector($p_cat->get_database_component());

        // Get all Connectors.
        $l_catdata = $l_dao_connector->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL, "isys_catg_connector_list__type", "DESC");

        $l_connections = [];

        $i = 0;

        // Resolve cable run for outputs.
        while ($l_row = $l_catdata->get_row())
        {
            if ($l_row["isys_catg_connector_list__isys_catg_connector_list__id"])
            {
                $l_conlist[$l_row["isys_catg_connector_list__isys_catg_connector_list__id"]] = true;
            }

            if (isset($l_conlist[$l_row["isys_catg_connector_list__id"]]))
            {
                continue;
            }

            // RUN CABLE RUN ALGORYHTM.
            $l_cable_run = $l_dao_connector->resolve_cable_run($l_row["isys_catg_connector_list__id"]);

            // CABLE RUN TABLE.
            $l_row["cable_left"] = $this->get_chain_reversed($l_cable_run[C__DIRECTION__LEFT], " &larr; ");

            if (count($l_row["cable_left"]) > 1)
            {
                $l_row["cable_left"] .= " &larr; ";
            }

            $l_row["cable_right"] = "<table><tr><td>";
            $l_row["cable_right"] .= $this->get_chain($l_cable_run[C__DIRECTION__RIGHT], " &rarr; ", $l_row["isys_catg_connector_list__id"]);
            $l_row["cable_right"] .= "</tr></td></table>";

            $l_row["type"] = $l_dao_connector->get_assigned_category_title($l_row["isys_catg_connector_list__assigned_category"]);

            // CAROUSEL.
            $l_row["carousel"] = $this->get_carousel_chain($l_cable_run[C__DIRECTION__RIGHT]);

            global $g_dirs;

            // TREE.
            $l_parent = C__CMDB__TREE_NODE__PARENT;
            $l_icon   = $g_dirs['images'] . "dtree/special/power_f_socket.gif";

            if ($l_row["cable_left"])
            {
                $l_parent = $this->m_tree->add_node($i++, $l_parent, $l_row["cable_left"], "", "_new", $l_icon);
                $l_icon   = $g_dirs['images'] . "dtree/special/power_m_plug.gif";
            } // if

            $this->build_tree($l_cable_run[C__DIRECTION__RIGHT], $l_parent, $l_icon);

            $l_connections[] = $l_row;
        } // while

        $g_comp_template->assign("tree", $this->get_tree());
        $g_comp_template->assign("connections", $l_connections);

        $this->hide_buttons();
        $g_comp_template->assign("bShowCommentary", "0");
    } // function

    /**
     * @return  string
     */
    public function get_tree()
    {
        return $this->m_tree->process(null, "");
    } // function

    /**
     *
     * @param   array   $p_data
     * @param   integer $p_parent
     * @param   string  $p_icon
     *
     * @return  boolean
     */
    public function build_tree($p_data, $p_parent = C__CMDB__TREE_NODE__PARENT, $p_icon = "")
    {
        if ($p_data["OBJECT_TITLE"])
        {
            $l_node = $this->m_tree->add_node(
                $p_data["CONNECTOR_ID"],
                $p_parent,
                $p_data["CONNECTOR_TITLE"] . " (" . $p_data["OBJECT_TITLE"] . ") ",
                $p_data["LINK"],
                "_new",
                $p_icon
            );

            if (is_array($p_data["CONNECTION"]))
            {
                $this->build_tree($p_data["CONNECTION"], $l_node, $p_icon);
            }
            else if (is_array($p_data["SIBLING"]))
            {
                foreach ($p_data["SIBLING"] as $l_sibling)
                {
                    $this->build_tree($l_sibling, $l_node, $p_icon);
                } // foreach
            } // if
        } // if

        return true;
    } // function

    /**
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);

        $this->set_template("catg__cabling.tpl");

        $this->m_tree = isys_component_tree::factory('cable_run');
    } // function
} // class