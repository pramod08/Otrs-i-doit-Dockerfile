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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_tree extends isys_ajax_handler
{
    /**
     * Initialization for this AJAX request.
     *
     * @global  isys_component_template $g_comp_template
     * @global  isys_component_database $g_comp_database
     */
    public function init()
    {
        global $g_comp_template, $g_comp_database;

        $l_dao = isys_component_dao_user::instance($g_comp_database);

        if ($_GET[C__CMDB__GET__TREEMODE] != C__WF__VIEW__TREE)
        {
            if ($_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_LOCATION)
            {
                isys_auth_cmdb::instance()
                    ->check(isys_auth::VIEW, 'LOCATION_VIEW');
            } // if

            $l_dao->save_settings(
                C__SETTINGS_PAGE__SYSTEM,
                ['C__CATG__OVERVIEW__DEFAULT_TREEVIEW' => $_GET[C__CMDB__GET__TREEMODE]]
            );
        } // if

        // At this point we need to select the previously saved option to assign it to the template.
        $l_settings = $l_dao->get_user_settings();

        $g_comp_template->assign('treeType', $l_settings['isys_user_locale__default_tree_type'])
            ->display("file:" . $this->m_smarty_dir . "templates/content/leftContent.tpl");

        $this->_die();
    } // function

    /**
     * Method which defines, if the hypergate needs to be run.
     *
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function
} // class