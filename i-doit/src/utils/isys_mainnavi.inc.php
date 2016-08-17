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
 * Main navigation
 *
 * @package     i-doit
 * @subpackage  Utilities
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define('C__MAINMENU__WORKFLOWS', 3);
define('C__MAINMENU__EXTRAS', 4);

// Create the menu component.
$g_menu                        = isys_component_menu::instance();
$l_mainMenu_object_type_groups = $g_menu->get_objecttype_group_menu();
$l_mainMenu                    = $g_menu->get_mainmenu();

$l_activeMainMenuItem = 0;
if (defined('C__MAINMENU__CMDB_EXPLORER') && $_GET[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__EXPLORER)
{
    $l_activeMainMenuItem = C__MAINMENU__CMDB_EXPLORER;
}
else if ($_GET[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__MULTIEDIT || $_GET['mNavID'] == C__MAINMENU__WORKFLOWS || $_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_RELATION)
{
    $l_activeMainMenuItem = C__MAINMENU__EXTRAS;
}
else if ($_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_OBJECT || $_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_OBJECTTYPE || $_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__LIST_OBJECT || $_GET[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_OBJECT)
{
    if (isset($_GET[C__CMDB__GET__OBJECTTYPE])) $l_activeMainMenuItem = $g_menu->get_active_menu_by_objtype_as_constant($_GET[C__CMDB__GET__OBJECTTYPE]);
    else $l_activeMainMenuItem = false;

    if (!$l_activeMainMenuItem && ($_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__RELATION || $_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__PARALLEL_RELATION))
    {
        $l_activeMainMenuItem = C__MAINMENU__EXTRAS;
    }

}
else if ($_GET[C__CMDB__GET__TREEMODE] == C__WF__VIEW__TREE)
{
    $l_activeMainMenuItem = C__MAINMENU__EXTRAS;
}
else
{
    if (!isset($_GET[C__GET__MODULE_ID]) || $_GET[C__GET__MODULE_ID] == C__MODULE__CMDB)
    {
        if (isset($_GET[C__CMDB__GET__OBJECTGROUP]))
        {
            $l_activeMainMenuItem = $_GET[C__CMDB__GET__OBJECTGROUP] . '0';
        }
        else if (isset($_GET[C__CMDB__GET__OBJECTTYPE]))
        {
            $l_activeMainMenuItem = $g_menu->get_active_menu_by_objtype_as_constant($_GET[C__CMDB__GET__OBJECTTYPE]);
        }
        /*else {
            $l_activeMainMenuItem = C__OBJTYPE_GROUP__INFRASTRUCTURE . '0';
        }*/
    }
    else
    {
        $l_activeMainMenuItem = C__MAINMENU__EXTRAS;
    }
}

if (!is_int($l_activeMainMenuItem) && defined('C__MAINMENU__' . $l_activeMainMenuItem))
{
    $l_activeMainMenuItem = constant('C__MAINMENU__' . $l_activeMainMenuItem);
}
elseif (!$l_activeMainMenuItem)
{
    $l_activeMainMenuItem = $g_menu->get_default_mainmenu();
}

if ($l_activeMainMenuItem === 0 && defined('C__MAINMENU__INFRASTRUCTURE'))
{
    $l_activeMainMenuItem = C__MAINMENU__INFRASTRUCTURE;
}

// Prepare needed variables.
if (!isset($_GET['mNavID']))
{
    switch ($_GET['objGroupID'])
    {
        case C__OBJTYPE_GROUP__SOFTWARE:
            $l_gets['mNavID'] = 1;
            break;

        case C__OBJTYPE_GROUP__OTHER:
            $l_gets['mNavID'] = 3;
            break;

        default:
        case C__OBJTYPE_GROUP__INFRASTRUCTURE:
            $l_gets['mNavID'] = 2;
            break;
    } // switch

    $_GET['mNavID'] = $l_gets['mNavID'];
} // if

// .. and activate menu object. Show activ menu by Get-Parameter mNavID.
$g_menu->activate_menuobj($l_activeMainMenuItem);

global $g_comp_template;
$g_comp_template->assign(
    'g_link__user',
    isys_helper_link::create_url(
        [
            C__GET__MODULE_ID     => C__MODULE__SYSTEM,
            C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
            C__GET__TREE_NODE     => 93,
            C__GET__SETTINGS_PAGE => 'login'
        ],
        true
    )
)
    ->assign(
        'g_link__settings',
        isys_helper_link::create_url(
            [
                C__GET__MODULE_ID => C__MODULE__SYSTEM,
                'what'            => 'system_settings'
            ],
            true
        )
    )
    ->assign('mainMenu', $l_mainMenu)
    ->assign('activeMainMenuItem', $l_activeMainMenuItem);

if (defined('C__MODULE__PRO'))
{
    if (defined('ISYS_LANGUAGE_GERMAN'))
    {
        $g_comp_template->assign('flag_de', isys_glob_add_to_query('lang', 'de'));
    }

    if (defined('ISYS_LANGUAGE_ENGLISH'))
    {
        $g_comp_template->assign('flag_en', isys_glob_add_to_query('lang', 'en'));
    }
}