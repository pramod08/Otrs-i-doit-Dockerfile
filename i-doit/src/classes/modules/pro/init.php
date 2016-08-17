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
 * i-doit PRO
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.3
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
define("C__MODULE__PRO", true);

// PRO Initialization.
define("C__ENABLE__LICENCE", true);

// URL, where idoit searches for the update XML document.
define("C__IDOIT_UPDATES_PRO", "http://www.i-doit.com/updates.xml");

// Additional main-menu constants.
define('C__MAINMENU__MYDOIT', 1);
define('C__MAINMENU__CMDB_EXPLORER', 2);

// View.
define("C__CMDB__VIEW__TREE_EXPLORER", 1010);
define("C__CMDB__VIEW__EXPLORER", 1101);
define("C__CMDB__VIEW__MULTIEDIT", 1102);

// CMDB Explorer views.
define("C__CMDB__VISUALIZATION_VIEW", 'view');
define("C__CMDB__VISUALIZATION_VIEW__OBJECT", 'object');
define("C__CMDB__VISUALIZATION_VIEW__IT_SERVICE", 'it-service');
// CMDB Explorer visualization types.
define("C__CMDB__VISUALIZATION_TYPE", 'type');
define("C__CMDB__VISUALIZATION_TYPE__TREE", 'tree');
define("C__CMDB__VISUALIZATION_TYPE__GRAPH", 'graph');

define('C__VISUALIZATION_PROFILE__OBJ_ID', 'obj-id');
define('C__VISUALIZATION_PROFILE__OBJ_SYS_ID', 'obj-sys-id');
define('C__VISUALIZATION_PROFILE__OBJ_TITLE', 'obj-title');
define('C__VISUALIZATION_PROFILE__OBJ_TITLE_CMDB_STATUS', 'obj-title-cmdb-status');
define('C__VISUALIZATION_PROFILE__OBJ_TYPE_TITLE', 'obj-type-title');
define('C__VISUALIZATION_PROFILE__OBJ_TYPE_TITLE_ICON', 'obj-type-title-icon');
define('C__VISUALIZATION_PROFILE__OBJ_TITLE_TYPE_TITLE_ICON_CMDB_STATUS', 'obj-title-type-title-icon-cmdb-status');
define('C__VISUALIZATION_PROFILE__CMDB_STATUS', 'cmdb-status');
define('C__VISUALIZATION_PROFILE__CATEGORY', 'category');
define('C__VISUALIZATION_PROFILE__PURPOSE', 'purpose');
define('C__VISUALIZATION_PROFILE__PRIMARY_CONTACT', 'primary-contact');
define('C__VISUALIZATION_PROFILE__PRIMARY_ACCESS_URL', 'primary-access-url');
define('C__VISUALIZATION_PROFILE__PRIMARY_IP', 'primary-ip');
define('C__VISUALIZATION_PROFILE__PRIMARY_HOSTNAME', 'primary-hostname');
define('C__VISUALIZATION_PROFILE__PRIMARY_HOSTNAME_FQDN', 'primary-hostname-fqdn');
define('C__VISUALIZATION_PROFILE__RELATION_TYPE', 'relation-type');

// Change product type.
global $g_product_info;
$g_product_info['type'] = 'PRO';

if (include_once('isys_module_pro_autoload.class.php'))
{
    spl_autoload_register('isys_module_pro_autoload::init');
} // if

global $g_comp_session;
if (file_exists(__DIR__ . DS . 'lang' . DS . $g_comp_session->get_language() . '.inc.php'))
{
    $l_language = include_once __DIR__ . DS . 'lang' . DS . $g_comp_session->get_language() . '.inc.php';

    if (is_array($l_language))
    {
        global $g_comp_template_language_manager;
        $g_comp_template_language_manager->append_lang($l_language);
    } // if
} // if