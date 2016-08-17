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
 * Module template
 *
 * This source file describes the structure of a module. A module
 * has to implement the methods derived from isys_module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 * @todo        Nicht vergessen, dieses Template nach Änderung des Modulinterfaces mit anzupassen!!
 */
class isys_module_template extends isys_module
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;

    private $m_userrequest;

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        ;
    } // function

    /**
     * Initialize module slots
     */
    public function initslots()
    {
        isys_component_signalcollection::get_instance()
            ->connect(
                "mod.cmdb.extendObjectTree",
                [
                    $this,
                    "slot_tree_extend"
                ]
            );
    } // function

    /**
     * @desc Starts module process
     * @throws isys_exception_general
     */
    public function start()
    {
        // @todo Bleibt $index_includes auch innerhalb der Module so wie geplant erhalten?
        global $index_includes;

        // Unpack request package ;-)
        $l_template = $this->m_userrequest->get_template();
        $l_tree     = $this->m_userrequest->get_menutree();

        // Don't forget to create the root entry.
        $l_tree->add_node(0, -1, "Tree");

        // And now the other nodes.
        $l_tree->add_node(1, 0, "Eintrag 1");
        $l_tree->add_node(2, 0, "Eintrag 2");
        $l_tree->add_node(3, 0, "Eintrag 3");

        // Assign tree.
        $l_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));

        // Show content of this module.
        $index_includes['contentarea'] = "content/bottom/content/main_template.tpl";
    } // function

    /**
     * Initializes the module.
     *
     * @param isys_module_request & $p_req
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req))
        {
            $this->m_userrequest = &$p_req;

            return true;
        } // if

        return false;
    } // function

    /**
     * Example slot for extending a tree
     *
     * @param isys_component_tree $p_tree
     */
    public function slot_tree_extend(isys_component_tree $p_tree)
    {
        $p_tree->add_node($p_tree->count(), 0, "Test", "javascript:;");
    } // function

} // class