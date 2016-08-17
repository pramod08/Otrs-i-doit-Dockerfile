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
 * Trouble-Ticket-System Module
 *
 * @package    i-doit
 * @subpackage Modules
 *
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
define("C__GET__TTS__PAGE", "npID");

class isys_module_tts extends isys_module implements isys_module_interface
{

    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * @var bool
     */
    protected static $m_licenced = true;

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
        $l_parent    = -1;
        $l_submodule = '';

        if ($p_system_module)
        {
            $l_parent    = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__TTS;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(
                C__MODULE__TTS . '0',
                $l_parent,
                _L('LC__MODULE__TTS')
            );
        } // if

        $p_tree->add_node(
            C__MODULE__TTS . '1',
            $l_root,
            _L('LC__TTS__CONFIGURATION'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TTS . '1' . '&' . C__GET__TTS__PAGE . '=' . 1,
            '',
            'images/icons/silk/comments.png',
            0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'TTS/CONFIG')
        );

        if (!$p_system_module)
        {

        } // if
    } // function

    /**
     * Start module
     */
    public function start()
    {
        global $g_comp_template, $index_includes, $g_comp_database;
        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'TTS/CONFIG');

        $l_dao_tts = new isys_tts_dao($g_comp_database);

        $l_gets  = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        if (empty($_GET[C__GET__TTS__PAGE]))
        {
            $_GET[C__GET__TTS__PAGE] = 1;
        } // if

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();
            $this->build_tree($l_tree, false);
            $l_tree->select_node_by_id($_GET[C__GET__TREE_NODE]);
            $g_comp_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        switch ($l_posts[C__GET__NAVMODE])
        {
            case C__NAVMODE__SAVE:
                $l_dao_tts = new isys_tts_dao($g_comp_database);

                if (strpos($_POST["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"], "://") !== false)
                {
                    $l_dao_tts->save(
                        $_POST["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"],
                        $_POST["C__TTS__TYPE"],
                        $_POST["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"],
                        $_POST["C__MODULE__REQUEST_TRACKER_CONFIG__USER"],
                        $_POST["C__MODULE__REQUEST_TRACKER_CONFIG__PASS"]
                    );
                }
                else
                {
                    $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"]["p_strInfoIconError"] = _L("LC__UNIVERSAL__FIELD_VALUE_IS_INVALID");
                }
                break;
        } // switch

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'TTS/CONFIG');

        switch ($l_gets[C__GET__TTS__PAGE])
        {
            case 1:
                $l_navbar = isys_component_template_navbar::getInstance();
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                    ->set_active(false, C__NAVBAR_BUTTON__NEW)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

                if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT)
                {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
                } //if

                $l_settings = $l_dao_tts->get_data()
                    ->get_row();

                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"]["p_arData"]        = serialize(get_smarty_arr_YES_NO());
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"]["p_strSelectedID"] = $l_settings["isys_tts_config__active"];
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"]["p_strClass"]      = 'input input-mini';
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"]["p_strValue"]           = $l_settings["isys_tts_config__service_url"];
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__USER"]["p_strValue"]           = $l_settings["isys_tts_config__user"];
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__PASS"]["p_strValue"]           = $l_settings["isys_tts_config__pass"];
                $l_rules['C__TTS__TYPE']['p_strSelectedID']                                 = $l_settings['isys_tts_config__isys_tts_type__id'];
                $l_rules['C__TTS__TYPE']['p_strClass']                                      = 'input input-mini';

                $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

                $index_includes['contentbottomcontent'] = "modules/ticketing/tts_config.tpl";
                break;
        } // switch
    } // function

    /**
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_tts
     */
    public function init(isys_module_request $p_req)
    {
        return $this;
    } // function

    /**
     * Method for adding links to the "sticky" category bar.
     *
     * @param  isys_component_template $p_tpl
     * @param  string                  $p_tpl_var
     * @param  integer                 $p_obj_id
     * @param  integer                 $p_obj_type_id
     */
    public function process_menu_tree_links($p_tpl, $p_tpl_var, $p_obj_id, $p_obj_type_id)
    {
        global $g_dirs, $g_comp_database;

        if (isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::VIEW, $p_obj_id, 'C__CATG__VIRTUAL_TICKETS')
        )
        {
            $l_tts = new isys_tts_dao($g_comp_database);

            // Seems like we need this to prevent all the exceptions, when no connector is defined.
            if (count($l_tts->get_data()) > 0)
            {
                try
                {
                    if ($l_tts->get_config())
                    {
                        $l_link_data = [
                            'title' => _L('LC__CMDB__CATG__VIRTUAL_TICKETS'),
                            'icon'  => $g_dirs['images'] . 'icons/silk/comments.png',
                            'link'  => "javascript:get_content_by_object('" . $p_obj_id . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . C__CATG__VIRTUAL_TICKETS . "', '" . C__CMDB__GET__CATG . "');"
                        ];

                        $p_tpl->append($p_tpl_var, ['ticket' => $l_link_data], true);

                    } // if
                }
                catch (isys_exception_general $e)
                {
                    ;
                } // try
            } // if
        } // if
    } // function
} // class