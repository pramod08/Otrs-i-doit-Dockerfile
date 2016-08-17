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
 * Static constant not registered by the dynamic constant manager:
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

/*******************************************************************************
 * Config constants, can be edited
 *******************************************************************************/

global $g_absdir;

// The base directory of i-doit.
define('BASE_DIR', $g_absdir . DIRECTORY_SEPARATOR);

define('PHP_VERSION_MINIMUM', '5.4.0');
define('PHP_VERSION_MINIMUM_RECOMMENDED', '5.5.0');

// This will not work with the new "isys_settings", the C__WIKI_URL has been swapped with "isys_settings::get('gui.wiki-url')".
// define("C__WIKI_URL", $g_config["wiki_url"]);

// Sysid unique? true/false possible here.
define("C__SYSID__UNIQUE", true);

// Show error message when specific javascript functions does not work due to outdated browser caches (Browser-Cache problem).
define("C__JS_BROWSER_ALERT", false);

// Define barcode type. Valid values are: qr, code39.
//define("C__BARCODE_TYPE", "qr");

// Processes all category changes, compares old to new values and saves them into the corresponding logbook entry.
// Migrated to isys_tenantsettings
//define("C__SAVE_DETAILED_CMDB_CHANGES", true);
//define("C__LIST__LOCATION__MAXLEN", 40);
//define("C__LIST__LOCATION__OBJLEN", 16);
//define("C__LIST__TITLE__MAXLEN", 55);
//define("C__DIALOG_PLUS__MAXLENGTH", 110);
//define("C__GUI_VALUE__NA", "-");

/*******************************************************************************
 * Editing constants below this marker may crash your i-doit
 *******************************************************************************/

/*******************************************************************************
 * GENERALLY USED CONSTANTS
 *******************************************************************************/
define("DS", DIRECTORY_SEPARATOR);
define("ISYS_NULL", null);
define("ISYS_EMPTY", ""); // Empty string
define("CRLF", "\r\n");

define("C__POST__POPUP_RECEIVER", "popupReceiver");

// Constant for default objecttype image
define("C__OBJTYPE_IMAGE__DEFAULT", "empty.png");

/*******************************************************************************
 * IMPORT CONSTANTS
 *******************************************************************************/
define("C__IMPORT__UI__MOUSE", 1001);
define("C__IMPORT__UI__KEYBOARD", 1002);
define("C__IMPORT__UI__PRINTER", 1003);
define("C__IMPORT__UI__MONITOR", 1004);

/*******************************************************************************
 * CMDB CONSTANTS
 *******************************************************************************/
define("C__CMDB__LOCATION_SEPARATOR", " > ");
define("C__CMDB__CONNECTOR_SEPARATOR", " > ");

// Constants for category connector.
define("C__CONNECTOR__INPUT", 1);
define("C__CONNECTOR__OUTPUT", 2);

// Cable directions.
define("C__DIRECTION__LEFT", 0);
define("C__DIRECTION__RIGHT", 1);

// Insertion options.
define("C__RACK_INSERTION__BACK", 0); // @todo Change to C__INSERTION__REAR
define("C__INSERTION__REAR", 0);
define("C__RACK_INSERTION__FRONT", 1); // @todo Change to C__INSERTION__FRONT
define("C__INSERTION__FRONT", 1);
define("C__RACK_INSERTION__BOTH", 2); // @todo Change to C__INSERTION__BOTH
define("C__INSERTION__BOTH", 2);
define("C__RACK_INSERTION__HORIZONTAL", 3);
define("C__RACK_INSERTION__VERTICAL", 4);

// Relation constants.
define("C__RELATION__IMPLICIT", 1);
define("C__RELATION__EXPLICIT", 2);

define("C__RELATION_DIRECTION__DEPENDS_ON_ME", 1);
define("C__RELATION_DIRECTION__I_DEPEND_ON", 2);
define("C__RELATION_DIRECTION__EQUAL", 3);

define("C__RELATION_OBJECT__MASTER", 0);
define("C__RELATION_OBJECT__SLAVE", 1);

// View constants for CMDB.
define("C__CMDB__VIEW__LIST_OBJECT", 1001);
define("C__CMDB__VIEW__LIST_CATEGORY", 1002);
define("C__CMDB__VIEW__LIST_OBJECTTYPE", 1003);
define("C__CMDB__VIEW__CONFIG_OBJECTTYPE", 1004);
define("C__CMDB__VIEW__CONFIG_SYSTEMDATA", 1005);
define("C__CMDB__VIEW__TREE_OBJECT", 1006);
define("C__CMDB__VIEW__TREE_LOCATION", 1007);
define("C__CMDB__VIEW__TREE_OBJECTTYPE", 1008);
define("C__CMDB__VIEW__TREE_RELATION", 1009);

// View constants for the left-side location navigation.
define("C__CMDB__VIEW__TREE_LOCATION__LOCATION", 1);
define("C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS", 2);
define("C__CMDB__VIEW__TREE_LOCATION__COMBINED", 3);

// View constants for objecttype sorting.
define("C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC", 1);
define("C__CMDB__VIEW__OBJECTTYPE_SORTING__MANUAL", 2);

// All category views have the same ID. We do all the category work automatically now.
define("C__CMDB__VIEW__CATEGORY_GLOBAL", 1100);
define("C__CMDB__VIEW__CATEGORY_SPECIFIC", 1100);
define("C__CMDB__VIEW__CATEGORY", 1100);

define("C__CMDB__VIEW__MISC_WELCOME", 1014);
define("C__CMDB__VIEW__MISC_BLANK", 1015);

// Error constants, can be replaced by LC.
define("C__CMDB__ERROR__NAVIGATION", 0x8001);
define("C__CMDB__ERROR__OBJECT_OVERVIEW", 0x8002);
define("C__CMDB__ERROR__ACTION_PROCESSOR", 0x8003);
define("C__CMDB__ERROR__CATEGORY_BUILDER", 0x8004);
define("C__CMDB__ERROR__DISTRIBUTOR", 0x8005);
define("C__CMDB__ERROR__CATEGORY_PROCESSOR", 0x8006);
define("C__CMDB__ERROR__ACTION_CATEGORY_UPDATE", 0x9001);

// Constants.
define("C__CMDB__CATEGORY__TYPE_GLOBAL", 0);
define("C__CMDB__CATEGORY__TYPE_SPECIFIC", 1);
define("C__CMDB__CATEGORY__TYPE_DYNAMIC", 2);
define("C__CMDB__CATEGORY__TYPE_PORT", 3);
define("C__CMDB__CATEGORY__TYPE_CUSTOM", 4);

// Object tree increments.
define("C__CMDB__TREE_OBJECT__INC_GLOBAL", 10000);
define("C__CMDB__TREE_OBJECT__INC_SPECIFIC", 20000);
define("C__CMDB__TREE_OBJECT__INC_MODULE", 40000);
define("C__CMDB__TREE_OBJECT__INC_GLOBAL_EXT", 100000);
define("C__CMDB__TREE_OBJECT__INC_SPECIFIC_EXT", 200000);
define("C__CMDB__TREE_OBJECT__INC_MODULE_EXT", 400000);

define("C__CMDB__TREE_NODE__BACK", 500001);
define("C__CMDB__TREE_NODE__PARENT", -1);

define("C__LINK__CATS", 1081);

// Other Tree constants.
define("C__CMDB__TREE_ICON", "dtreeIcon");

// Parameter constants. Probably we need to exchange some.
define("C__GET__AJAX_CALL", "call");
define("C__GET__AJAX", "ajax");
define("C__CMDB__GET__VIEWMODE", "viewMode");
define("C__CMDB__GET__TREEMODE", "tvMode");
define("C__CMDB__GET__TREETYPE", "tvType");
define("C__CMDB__GET__OBJECTGROUP", "objGroupID");
define("C__CMDB__GET__OBJECTTYPE", "objTypeID");
define("C__CMDB__GET__OBJECT", "objID");
define("C__CMDB__GET__CATTYPE", "catTypeID");
define("C__CMDB__GET__CATG", "catgID");
define("C__CMDB__GET__CATS", "catsID");
define("C__CMDB__GET__CATG_CUSTOM", "customID");
define("C__CMDB__GET__CATD", "catdID");
define("C__CMDB__GET__POPUP", "popup");
define("C__CMDB__GET__CAT_MENU_SELECTION", "catMenuSelection");
define("C__CMDB__GET__EDITMODE", "editMode");
define("C__CMDB__GET__CAT_LIST_VIEW", "catListView");
define("C__CMDB__GET__NETPORT", "NetportID");
define("C__CMDB__GET__CATD_CHECK", "catdCheck");
define("C__CMDB__GET__SUBCAT", "subcatID");
define("C__CMDB__GET__SUBCAT_ENTRY", "subcatEntryID");
define("C__CMDB__GET__CONNECTION_TYPE", "connectionType");
define("C__CMDB__GET__LDEVSERVER", "ldevserverID");

// CMDB: Category levels while browsing IN a category.
define("C__CMDB__GET__CATLEVEL_1", "cat1ID");
define("C__CMDB__GET__CATLEVEL_2", "cat2ID");
define("C__CMDB__GET__CATLEVEL_3", "cat3ID");
define("C__CMDB__GET__CATLEVEL_4", "cat4ID");
define("C__CMDB__GET__CATLEVEL_5", "cat5ID");
define("C__CMDB__GET__CATLEVEL", "cateID");
define("C__CMDB__GET__CATLEVEL_MAX", 5);

// CMDB: Ranking levels - used in Low-Level API for deletion
define("C__CMDB__RANK__DIRECTION_DELETE", 1);
define("C__CMDB__RANK__DIRECTION_RECYCLE", 2);

// CMDB: DAO-inner constants for direction and type of network-type elements.
define("C__CMDB__DAO_NET_PORT__AHEAD", 0); // Connectionspecific
define("C__CMDB__DAO_NET_PORT__REAR", 1); // Connectionspecific
define("C__CMDB__DAO_NET_PORT__PHYSICAL", 1); // Portspecific
define("C__CMDB__DAO_NET_PORT__VIRTUAL", 2); // Portspecific
define("C__CMDB__DAO_NET_INTERFACE__PHYSICAL", 1); // Interfacespecific
define("C__CMDB__DAO_NET_INTERFACE__VIRTUAL", 2); // Interfacespecific

// CMDB: DAO-inner constants for endpoint selection of an universal interface.
define("C__CMDB__DAO_UI_ENDPOINT__AHEAD", 1);
define("C__CMDB__DAO_UI_ENDPOINT__REAR", 2);

// CMDB: DAO-inner constants for endpoint selection of a FC storage connection.
define("C__CMDB__DAO_STOR_FC__AHEAD", 1);
define("C__CMDB__DAO_STOR_FC__REAR", 2);

// CMDB ACTIONS.
define("C__CMDB__ACTION__CATEGORY_CREATE", 0x0001);
define("C__CMDB__ACTION__CATEGORY_RANK", 0x0002);
define("C__CMDB__ACTION__CATEGORY_UPDATE", 0x0003);
define("C__CMDB__ACTION__CONFIG_OBJECT", 0x0101);
define("C__CMDB__ACTION__CONFIG_OBJECTTYPE", 0x0102);
define("C__CMDB__ACTION__OBJECT_CREATE", 0x0201);
define("C__CMDB__ACTION__OBJECT_RANK", 0x0202);

/*******************************************************************************
 * CONSTANTS USED IN CONTACTS
 *******************************************************************************/
// Views constants for 'Contacts & Identities'.
define("C__CONTACT__VIEW__TREE", 2001);
define("C__CONTACT__VIEW__LIST", 2002);
define("C__CONTACT__VIEW__LIST_PERSON", 2003);
define("C__CONTACT__VIEW__LIST_GROUP", 2004);
define("C__CONTACT__VIEW__LIST_ORGANISATION", 2005);
define("C__CONTACT__VIEW__DETAIL_PERSON", 2006);
define("C__CONTACT__VIEW__DETAIL_GROUP", 2007);
define("C__CONTACT__VIEW__DETAIL_ORGANISATION", 2008);
define("C__CONTACT__VIEW__DETAIL_STARTPAGE", 2009); // Startseite im Information zu Kontakten
define("C__CONTACT__VIEW__LIST_PERSON_WITHOUT_ORGANISATION", 2010); // alle personen ohne organisationszuordung
define("C__CONTACT__VIEW__NAGIOS_PERSON", 2011);
define("C__CONTACT__VIEW__LIST_LDAP", 2012);
define("C__CONTACT__VIEW__NAGIOS_GROUP", 2013);

// Menu selection IDs for 'Contacts & Identities'.
define("C__CONTACT_TREE__ORGANSIATION_MAIN", 1); // alle organisationen
define("C__CONTACT_TREE__ORGANSIATION_MASTER_DATA", 2); // eine spezifische organisation
define("C__CONTACT_TREE__ORGANSIATION_PERSON", 3); // alle personen einer organisation
define("C__CONTACT_TREE__PERSON_MAIN", 4); // alle personen
define("C__CONTACT_TREE__PERSON_MASTER_DATA", 5); // eine spezifische person
define("C__CONTACT_TREE__PERSON_GROUP", 6); // alle gruppen einer person
define("C__CONTACT_TREE__GROUP_MAIN", 7); // alle gruppen
define("C__CONTACT_TREE__GROUP_MASTER_DATA", 8); // eine spezifische gruppe
define("C__CONTACT_TREE__GROUP_PERSON", 9); // alle personen einer gruppe
define("C__CONTACT_TREE__STARTPAGE", 10); // startseite
define("C__CONTACT_TREE__PERSON_WITHOUT_ORGANISATION", 11); // alle personen ohne organisationszuordung
define("C__CONTACT_TREE__PERSON_NAGIOS", 12); // nagiosdaten einer person
define("C__CONTACT_TREE__LDAP", 13); // ldap servers
define("C__CONTACT_TREE__GROUP_NAGIOS", 14); // nagiosdaten einer gruppe

// Bit addition for value of _GET[p_iFilter], to filter the currently displayed tree.
define("C__CONTACT_BROWSER_FILTER__ORGANSATION", 1 << 0);
define("C__CONTACT_BROWSER_FILTER__PERSON", 1 << 1);
define("C__CONTACT_BROWSER_FILTER__GROUP", 1 << 2);

// GET Parameters for Contacts.
define("C__CONTACT__GET__MENU_SELECTION", "contactMenuSelection");
define("C__CONTACT_PERSON_ID", "cpID");
define("C__CONTACT_ORGANISATION_ID", "coID");
define("C__CONTACT_GROUP_ID", "cgID");

/*******************************************************************************
 * CONSTANTS USED IN TASKS
 *******************************************************************************/
define("C__TASK__VIEW__LIST_ALL", 3001);
define("C__TASK__VIEW__LIST_WORKORDER", 3002);
define("C__TASK__VIEW__LIST_CHECKLIST", 3003);
define("C__TASK__VIEW__DETAIL_WORKORDER", 3050);
define("C__TASK__VIEW__DETAIL_CHECKLIST", 3051);

define("C__TASK__VIEW__TREE", 3101);

define("C__TASK__GET__ID", "tID");
define("C__TASK__GET__STATUS", "tS");
define("C__TASK__GET__ACCEPT", "tA");
define("C__TASK__GET__COMPLETED", "tC");

define("C__TASK__OCCURRENCE__ONCE", 1);
define("C__TASK__OCCURRENCE__HOURLY", 2);
define("C__TASK__OCCURRENCE__DAILY", 3);
define("C__TASK__OCCURRENCE__WEEKLY", 4);
define("C__TASK__OCCURRENCE__EVERY_TWO_WEEKS", 5);
define("C__TASK__OCCURRENCE__MONTHLY", 6);
define("C__TASK__OCCURRENCE__YEARLY", 7);

/*******************************************************************************
 * CONSTANTS USED IN WORKFLOWS
 *******************************************************************************/
define("C__WF__VIEW__LIST", 4001);
define("C__WF__VIEW__LIST_WF_TYPE", 4010);
define("C__WF__VIEW__LIST_TEMPLATE", 4011);
define("C__WF__VIEW__LIST_FILTER", 4012);
define("C__WF__VIEW__DETAIL__SELECTOR", 4050);
define("C__WF__VIEW__DETAIL__GENERIC", 4051);
define("C__WF__VIEW__DETAIL__WF_TYPE", 4060);
define("C__WF__VIEW__DETAIL__TEMPLATE", 4061);
define("C__WF__VIEW__DETAIL__EMAIL_GUI", 4062);
define("C__WF__VIEW__TREE", 4101);

define("C__WF__GET__ID", "wid");
define("C__WF__GET__TYPE", "wtype");
define("C__WF__GET__TEMPLATE", "wtpl");
define("C__WORKFLOW__GET__FILTER", "fltr");

define("C__WF__PARAMETER_TYPE__INT", 1);
define("C__WF__PARAMETER_TYPE__STRING", 2);
define("C__WF__PARAMETER_TYPE__TEXT", 3);
define("C__WF__PARAMETER_TYPE__DATETIME", 4);
define("C__WF__PARAMETER_TYPE__YES_NO", 5);

// E-Mail bitmasks (matched with tenant settings: isys_tenantsettings::get('workflow.notify')).
define("C__WORKFLOW__MAIL__NOTIFICATION", 1 << 0);
define("C__WORKFLOW__MAIL__ACCEPTED", 1 << 1);
define("C__WORKFLOW__MAIL__OPEN", 1 << 2);
define("C__WORKFLOW__MAIL__COMPLETED", 1 << 3);

/*******************************************************************************
 * TASK EMAIL CONSTANTS
 *******************************************************************************/
define("C__EMAIL_TEMPLATE__TASK__BEFORE_ENDDATE", 1);
define("C__EMAIL_TEMPLATE__TASK__NOTIFICATION", 2);
define("C__EMAIL_TEMPLATE__TASK__ACCEPT", 3);
define("C__EMAIL_TEMPLATE__TASK__STATUS_OPEN", 4);
define("C__EMAIL_TEMPLATE__TASK__STATUS_DUE", 5);
define("C__EMAIL_TEMPLATE__TASK__STATUS_CLOSED", 6);
define("C__EMAIL_TEMPLATE__TASK__COMPLETION_ACCEPTED", 7);

/*******************************************************************************
 * DATABASE SPECIFIC CONSTANTS
 *******************************************************************************/
define("C__DB_GENERAL__INSERT", 1);
define("C__DB_GENERAL__UPDATE", 2);
define("C__DB_GENERAL__REPLACE", 3);

/*******************************************************************************
 * GLOBALLY USED GET PARAMETER CONSTANTS
 *******************************************************************************/
define("C__GET__AJAX_REQUEST", "aj_request");
define("C__GET__FILE__ID", "f_id");
define("C__GET__FILE_MANAGER", "file_manager");
define("C__GET__FILE_NAME", "file_name");
define("C__GET__MODULE", "mod");
define("C__GET__MODULE_ID", "moduleID");
define("C__GET__PARAM", "param");
define("C__GET__MODULE_SUB_ID", "moduleSubID");
define("C__GET__MAIN_MENU__NAVIGATION_ID", "mNavID");
define("C__GET__NAVMODE", "navMode");
define("C__GET__SETTINGS_PAGE", "pID");
define("C__GET__TREE_NODE", "treeNode");
define("C__GET__ID", "id");

/*******************************************************************************
 * USER SETTINGS PAGES
 *******************************************************************************/
define("C__SETTINGS_PAGE__USER", 1);
define("C__SETTINGS_PAGE__THEME", 2);
define("C__SETTINGS_PAGE__CMDB_STATUS", 3);
define("C__SETTINGS_PAGE__SYSTEM", 4);

/*******************************************************************************
 * GLOBALLY USED SESSION PARAMETERS
 *******************************************************************************/
define("C__SESSION__REC_STATUS__LIST_VIEW", "cRecStatusListView");

/*******************************************************************************
 * JOB CONTROL SYSTEM - SPECIFIC
 *******************************************************************************/
define("C__JCS__OS_UNIX", 1);
define("C__JCS__OS_WINDOWS", 2);

/*******************************************************************************
 * CONSTANTS FOR SEARCH  MODULE
 *******************************************************************************/
define("C__SEARCH__GET__WHAT", "s");
define("C__SEARCH__GET__HIGHLIGHT", "highlight");

// Virtual machine.
define("C__VM__GUEST", 2);
define("C__VM__NO", 3);

/*******************************************************************************
 * CATEGORY PROPERTIES
 *******************************************************************************/
define('C__PROPERTY_TYPE__STATIC', 1);
define('C__PROPERTY_TYPE__DYNAMIC', 2);

define('C__PROPERTY__INFO', 'info');
define('C__PROPERTY__INFO__TITLE', 'title');
define('C__PROPERTY__INFO__DESCRIPTION', 'description');
define('C__PROPERTY__INFO__PRIMARY', 'primary_field');
define('C__PROPERTY__INFO__TYPE', 'type');
define('C__PROPERTY__INFO__BACKWARD', 'backward');

define('C__PROPERTY__INFO__TYPE__TEXT', 'text');
define('C__PROPERTY__INFO__TYPE__TEXTAREA', 'textarea');
define('C__PROPERTY__INFO__TYPE__DOUBLE', 'double');
define('C__PROPERTY__INFO__TYPE__FLOAT', 'float');
define('C__PROPERTY__INFO__TYPE__INT', 'int');
define('C__PROPERTY__INFO__TYPE__N2M', 'n2m');
define('C__PROPERTY__INFO__TYPE__DIALOG', 'dialog');
define('C__PROPERTY__INFO__TYPE__DIALOG_PLUS', 'dialog_plus');
define('C__PROPERTY__INFO__TYPE__DIALOG_LIST', 'dialog_list');
define('C__PROPERTY__INFO__TYPE__DATE', 'date');
define('C__PROPERTY__INFO__TYPE__DATETIME', 'datetime');
define('C__PROPERTY__INFO__TYPE__OBJECT_BROWSER', 'object_browser');
define('C__PROPERTY__INFO__TYPE__MULTISELECT', 'multiselect');
define('C__PROPERTY__INFO__TYPE__MONEY', 'money');
define('C__PROPERTY__INFO__TYPE__AUTOTEXT', 'autotext');
define('C__PROPERTY__INFO__TYPE__UPLOAD', 'upload');
define('C__PROPERTY__INFO__TYPE__COMMENTARY', 'commentary');

define('C__PROPERTY__DATA', 'data');
define('C__PROPERTY__DATA__TYPE', 'type');
define('C__PROPERTY__DATA__FIELD', 'field');
define('C__PROPERTY__DATA__RELATION_TYPE', 'relation_type');
define('C__PROPERTY__DATA__RELATION_HANDLER', 'relation_handler');
define('C__PROPERTY__DATA__FIELD_ALIAS', 'field_alias');
define('C__PROPERTY__DATA__TABLE_ALIAS', 'table_alias');
define('C__PROPERTY__DATA__REFERENCES', 'references');
define('C__PROPERTY__DATA__READONLY', 'readonly');
define('C__PROPERTY__DATA__JOIN_CONDITION', 'join_condition');

define('C__PROPERTY__UI', 'ui');
define('C__PROPERTY__UI__ID', 'id');
define('C__PROPERTY__UI__TYPE', 'type');
define('C__PROPERTY__UI__PARAMS', 'params');
define('C__PROPERTY__UI__DEFAULT', 'default');
define('C__PROPERTY__UI__PLACEHOLDER', 'placeholder');
define('C__PROPERTY__UI__EMPTYMESSAGE', 'emptyMessage');

define('C__PROPERTY__CHECK', 'check');
define('C__PROPERTY__CHECK__MANDATORY', 'mandatory');
define('C__PROPERTY__CHECK__VALIDATION', 'validation');
define('C__PROPERTY__CHECK__SANITIZATION', 'sanitization');
define('C__PROPERTY__CHECK__UNIQUE_OBJ', 'unique_obj');
define('C__PROPERTY__CHECK__UNIQUE_OBJTYPE', 'unique_objtype');
define('C__PROPERTY__CHECK__UNIQUE_GLOBAL', 'unique_global');

define('C__PROPERTY__PROVIDES', 'provides');
define('C__PROPERTY__PROVIDES__SEARCH', 1);
define('C__PROPERTY__PROVIDES__IMPORT', 2);
define('C__PROPERTY__PROVIDES__EXPORT', 4);
define('C__PROPERTY__PROVIDES__REPORT', 8);
define('C__PROPERTY__PROVIDES__LIST', 16);
define('C__PROPERTY__PROVIDES__MULTIEDIT', 32);
define('C__PROPERTY__PROVIDES__VALIDATION', 64);
define('C__PROPERTY__PROVIDES__VIRTUAL', 128);

define('C__PROPERTY__FORMAT', 'format');
define('C__PROPERTY__FORMAT__CALLBACK', 'callback');
define('C__PROPERTY__FORMAT__REQUIRES', 'requires');
define('C__PROPERTY__FORMAT__UNIT', 'unit');

define('C__PROPERTY__UI__TYPE__POPUP', 'popup');
define('C__PROPERTY__UI__TYPE__MULTISELECT', 'multiselect');
define('C__PROPERTY__UI__TYPE__TEXT', 'text');
define('C__PROPERTY__UI__TYPE__TEXTAREA', 'textarea');
define('C__PROPERTY__UI__TYPE__DIALOG', 'dialog');
define('C__PROPERTY__UI__TYPE__DIALOG_LIST', 'f_dialog_list');
define('C__PROPERTY__UI__TYPE__DATE', 'date');
define('C__PROPERTY__UI__TYPE__DATETIME', 'datetime');
define('C__PROPERTY__UI__TYPE__CHECKBOX', 'checkbox');
define('C__PROPERTY__UI__TYPE__PROPERTY_SELECTOR', 'f_property_selector');
define('C__PROPERTY__UI__TYPE__AUTOTEXT', 'autotext');
define('C__PROPERTY__UI__TYPE__UPLOAD', 'upload');

// We use these constants for the "get_properties()" method.
define('C__PROPERTY__WITH__VALIDATION', 1);
define('C__PROPERTY__WITH__DEFAULTS', 2);
// define('C__PROPERTY__WITH__', 4); // We use these constants "bitwise"!

// Defining a global "wildcard" symbol.
define('C__WILDCARD', '*');

// We define some "day" and "month" constants.
define('C__DAY__MONDAY', 'monday');
define('C__DAY__TUESDAY', 'tuesday');
define('C__DAY__WEDNESDAY', 'wednesday');
define('C__DAY__THURSDAY', 'thursday');
define('C__DAY__FRIDAY', 'friday');
define('C__DAY__SATURDAY', 'saturday');
define('C__DAY__SUNDAY', 'sunday');

define('C__MONTH__JANUARY', 'january');
define('C__MONTH__FEBRUARY', 'february');
define('C__MONTH__MARCH', 'march');
define('C__MONTH__APRIL', 'april');
define('C__MONTH__MAY', 'may');
define('C__MONTH__JUNE', 'june');
define('C__MONTH__JULY', 'july');
define('C__MONTH__AUGUST', 'august');
define('C__MONTH__SEPTEMBER', 'september');
define('C__MONTH__OCTOBER', 'october');
define('C__MONTH__NOVEMBER', 'november');
define('C__MONTH__DECEMBER', 'december');

/*******************************************************************************
 * Categories' properties (deprecated)
 *******************************************************************************/

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__TAG', 'tag');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__TITLE', 'title');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__FORMTAG', 'formtag');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__HELPER', 'helper');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__EXPORT_HELPER', 'helper');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__IMPORT_HELPER', 'helper');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__PARAM', 'param');

/**
 * Parameter(s) for the export helper class' constructor.
 *
 * @deprecated
 * @todo  Refactor to 'export_param'.
 */
define('C__CATEGORY_DATA__EXPORT_PARAM', 'param');

/**
 * Parameter(s) for the export helper class' constructor.
 *
 * @deprecated
 * @todo  Refactor to 'import_param'.
 */
define('C__CATEGORY_DATA__IMPORT_PARAM', 'param');

/**
 * Parameter(s) for the helper's method.
 *
 * @deprecated  Never used.
 */
define('C__CATEGORY_DATA__ARG', 'arg');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__METHOD', 'method');
/**
 * @deprecated
 */
define('C__CATEGORY_DATA__FIELD', 'field');
/**
 * @deprecated
 */
define('C__CATEGORY_DATA__REF', 'ref');
/**
 * @deprecated
 */
define('C__CATEGORY_DATA__TABLE', 'table');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__FILTER', 'filter');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__IMPORT', 'import');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__EXPORT', 'export');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__OPTIONAL', 'optional');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__DEFAULT', 'default');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__VALUE', 'value');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__REPORT', 'report');

/**
 * A constant for the "value" string.
 * Will be used quite often - especially inside the import and export classes and helper.
 */
define('C__DATA__VALUE', 'value');

/**
 * A constant for the "title" string.
 * Will be used quite often - especially inside the import and export classes and helper.
 */
define('C__DATA__TITLE', 'title');

/**
 * A constant for the "tag" string.
 * Will be used quite often - especially inside the import and export classes and helper.
 */
define('C__DATA__TAG', 'tag');

/**
 * Validate property's value. Based on filter_var. Can be just a filter or an
 * associative array with the filter and options. Callbacks are also supported.
 * Used by category DAO's validate_user_data() method. Defaults to null (no validation).
 *
 * Examples:
 *
 * Email:
 * C__CATEGORY_DATA__VALIDATE => FILTER_VALIDATE_EMAIL
 *
 * URL (scheme required):
 * C__CATEGORY_DATA__VALIDATE => array(
 *     'filter' => FILTER_VALIDATE_URL,
 *     'options' => FILTER_FLAG_SCHEME_REQUIRED
 * )
 *
 * Integer with range between 0 and 3:
 * C__CATEGORY_DATA__VALIDATE => array(
 *     'filter' => FILTER_VALIDATE_INT,
 *     'options' => array(
 *         'options' => array(
 *             'min_range' => 0,
 *             'max_range' => 3
 *         )
 *     )
 * )
 *
 * Callback for validating textareas:
 * C__CATEGORY_DATA__VALIDATE => array(
 *     'filter' => FILTER_CALLBACK,
 *     'options' => array(
 *         'options' => array('isys_helper', 'filter_textarea')
 *     )
 * )
 */
/**
 * @deprecated
 */
define('C__CATEGORY_DATA__VALIDATE', 'validate');

/**
 * @deprecated
 */
define('C__CATEGORY_DATA__TYPE', 'type');

// Property type 'text' means type VARCHAR(255) in SQL.
define('C__TYPE__TEXT', 'text');
// Property type 'text_area', 'json' and 'image' means type TEXT in SQL.
define('C__TYPE__TEXT_AREA', 'text_area');
define('C__TYPE__JSON', 'json');
// Property type 'int' means type INT(10) in SQL.
define('C__TYPE__INT', 'int');
define('C__TYPE__FLOAT', 'float');
define('C__TYPE__DOUBLE', 'double');
define('C__TYPE__DATE', 'date');
define('C__TYPE__DATE_TIME', 'date_time');

// Category property's value type. Defaults to 'text'.
define('C__CATEGORY_DATA__FORMAT', 'format');

// Property formats:

// Defines whether migration is active or inactive. Defaults to true.
define('C__UPDATE_MIGRATION', true);

/**
 * Warning: If this constant is set than you have to save all information like ldap server in the ldap module
 * or nagios ndo mysql server in the nagios module again.
 */
define('C__CRYPT_KEY', '');

// Defines, if all exceptions shall be written to a log. !!Warning!! The auth-exceptions can quickly reach several Megabytes.
define('C__WRITE_EXCEPTION_LOGS', true);