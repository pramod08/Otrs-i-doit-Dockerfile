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
 * Barcode-Module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_barcode extends isys_module implements isys_module_interface
{

    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Initializes the module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_barcode
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;

        return $this;
    } // function

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
     * Starts module process.
     *
     * @throws  isys_exception_general
     */
    public function start()
    {
        // Unpack request package.
        $l_gets     = $this->m_userrequest->get_gets();
        $l_posts    = $this->m_userrequest->get_posts();
        $l_template = $this->m_userrequest->get_template();
        $l_tree     = $this->m_userrequest->get_menutree();

        // Don't forget to create the root entry.
        $l_tree->add_node(0, -1, "Barcode");

        // Assign tree.
        $l_template->assign("menu_tree", $l_tree->process(0));

        $this->process($l_gets, $l_posts);
    } // function

    /**
     * Process method.
     *
     * @param  array $p_get
     * @param  array $p_post
     */
    public function process($p_get, $p_post)
    {
        global $g_comp_database;

        if (isset($p_get["get"]))
        {
            $l_barcode   = str_replace(C__CMDB__SYSID__PREFIX, "", ($p_get["get"]));
            $l_object_id = $p_get[C__CMDB__GET__OBJECT];

            $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);
            $l_name     = $l_dao_cmdb->get_obj_name_by_id_as_string($l_object_id);

            header("Content-Type: text/idoit-barcode");
            header("Content-Disposition: inline; filename=" . $l_object_id . ".ib");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Content-transfer-encoding: binary");

            $l_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n" . "<barcode>\r\n" . "\t<info>\r\n" . "\t\t<version>1.0</version>\r\n" . "\t\t<date>%s</date>\r\n" . "\t\t<timestamp>%s</timestamp>\r\n" . "\t</info>\r\n" . "\t<codes>\r\n" . "\t\t<code object=\"%s\">\r\n" . "\t\t\t<id>%s</id>\r\n" . "\t\t\t<name>%s</name>\r\n" . "\t\t</code>\r\n" . "\t</codes>\r\n" . "</barcode>";

            printf($l_xml, date("Y-m-d h:i:s"), time(), $l_object_id, $l_barcode, $l_name);

            die();
        } // if
    } // function
} // class