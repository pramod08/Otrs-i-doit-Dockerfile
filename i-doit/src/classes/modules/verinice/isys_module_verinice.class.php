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
 * Verinice
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_verinice extends isys_module implements isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    private $m_userrequest;

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_verinice::instance();
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
     * Initialize module slots
     */
    public function initslots()
    {
        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.cmdb.afterObjectTypeSave',
                [
                    $this,
                    'slot_after_obj_type_save'
                ]
            );
        isys_component_signalcollection::get_instance()
            ->connect(
                'mod.cmdb.viewProcessed',
                [
                    $this,
                    'slot_view_proceessed'
                ]
            );
    } // function

    /**
     * @desc Starts module process
     * @throws isys_exception_general
     */
    public function start()
    {
        global $index_includes, $g_comp_database, $g_absdir;

        // Unpack request package ;-)
        $l_template = $this->m_userrequest->get_template();
        $l_tree     = $this->m_userrequest->get_menutree();

        // Don't forget to create the root entry.
        $l_tree->add_node(0, -1, "Verinice");

        // And now the other nodes.
        if (isys_auth_verinice::instance()
            ->is_allowed_to(isys_auth::VIEW, 'verinice/' . isys_auth_verinice::CL__OPERATION__MAPPER)
        )
        {
            $l_tree->add_node(
                C__MODULE__VERINICE . '1',
                0,
                'Mapper',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID],
                null,
                "images/icons/tree/dienste.gif",
                true
            );
        }

        $l_tree->add_node(
            C__MODULE__VERINICE . '2',
            0,
            _L('LC__CMDB__OBJTYPE__CONFIGURATION_MODUS'),
            '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM,
            null,
            "images/icons/tree/infrastructure.gif"
        );

        /* Get preview data */
        $l_dao  = new isys_verinice_dao($g_comp_database);
        $l_data = $l_dao->get_export_data();
        if (isset($_POST['v_export']) || isset($_GET['export']))
        {

            try
            {
                $l_xml = $l_dao->format_verinice_mapping($l_data);

                if ($l_xml)
                {
                    header("Content-Disposition: attachment; filename=\"verinice-mapping.xml\";");
                    header('Content-Type: application/octet-stream');
                    echo $l_xml;
                    die;
                }

            }
            catch (Exception $e)
            {
                isys_application::instance()->container['notify']->error($e->getMessage());
            }
        }
        else
        {

            $l_template->assign('types', $l_data->__as_array());
        }

        // Assign tree.
        $l_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));

        // Show content of this module.
        try
        {
            isys_auth_verinice::instance()
                ->check(isys_auth::VIEW, 'verinice/' . isys_auth_verinice::CL__OPERATION__MAPPER);

            $index_includes['contentbottomcontent'] = $g_absdir . '/src/classes/modules/verinice/templates/module_content.tpl';
        }
        catch (isys_exception_auth $e)
        {
            $l_template->assign("exception", $e->write_log());

            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    } // function

    /* ------------------------------------------------------------------------------------------------ */
    /* SLOTS */
    /* ------------------------------------------------------------------------------------------------ */

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
    }

    /**
     * Called after a view was processed
     *
     * @param isys_cmdb_view $p_cmdb_view
     * @param mixed          $p_process_result
     */
    public function slot_view_proceessed($p_cmdb_view, $p_process_result)
    {
        global $index_includes, $g_comp_template, $g_absdir;

        if ($p_cmdb_view->get_id() == C__CMDB__VIEW__CONFIG_OBJECTTYPE)
        {
            $l_object_type = ($_POST['id'][0] > 0) ? $_POST['id'][0] : $_GET[C__CMDB__GET__OBJECTTYPE];

            if ($l_object_type > 0)
            {
                $l_data                                             = $p_cmdb_view->get_dao_cmdb()
                    ->get_object_types($l_object_type)
                    ->get_row();
                $l_rules["C__VERINICE__MAPPING"]["p_strSelectedID"] = $l_data["isys_obj_type__isys_verinice_types__id"];

                $index_includes['contentbottomcontentaddition'][] = $g_absdir . '/src/classes/modules/verinice/templates/obj_type_config.tpl';
                $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
            }
        }
    } // function

    /**
     * Called after an object type is saved
     *
     * @param int     $p_objtype_id
     * @param array   $p_posts
     * @param boolean $p_was_saved
     */
    public function slot_after_obj_type_save($p_objtype_id, $p_posts, $p_was_saved = true)
    {
        global $g_comp_database;

        // if object type was saved correctly, go further and save the verinice stuff
        if ($p_was_saved)
        {

            /* save object type if verinice settings was found */
            if (isset($p_posts['C__VERINICE__MAPPING']))
            {
                $l_dao = new isys_verinice_dao($g_comp_database);
                $l_dao->save($p_objtype_id, $p_posts['C__VERINICE__MAPPING']);
            } // if
        } // if
    } // function
} // class