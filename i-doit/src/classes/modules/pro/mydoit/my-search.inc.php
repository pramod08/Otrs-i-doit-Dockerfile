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
 *
 * @package    i-doit
 * @subpackage General
 * @author     Andre Woesten <awoesten@i-doit.org> - 2006-05-06
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

$l_dao       = new isys_component_dao($g_comp_database);
$l_daoUser   = isys_component_dao_user::instance($g_comp_database);
$l_settingID = $l_daoUser->get_user_setting_id();

global $g_comp_database;
global $g_comp_template_language_manager;

define("ADD_CRITERIAS", "mysearch_addCriterion");
define("DEL_CRITERIAS", "mysearch_delCriterion");

// Request handling
if ($_GET['request'] == ADD_CRITERIAS && $l_settingID != ISYS_NULL && isset($_POST["C__SEARCH_TEXT"][0]) && !empty($_POST["C__SEARCH_TEXT"][0]))
{
    // Add Criterias to DB
    add_criterias($l_settingID);
}
else if ($_GET['request'] == DEL_CRITERIAS)
{
    // Del Criterias from DB
    del_criterium();
}

// show already bookmarked search criterias
$l_stmt = "	SELECT isys_search__id,	isys_search__isys_user_setting__id,	isys_search__title,	isys_search__link
				FROM isys_search
				WHERE isys_search__isys_user_setting__id='" . $l_settingID . "';";

$l_res        = $l_dao->retrieve($l_stmt);
$l_aBookmarks = [];
$l_nBookmarks = $l_res->num_rows();

if ($l_res && ($l_nBookmarks > 0))
{

    while ($l_row = $l_res->get_row())
    {

        $l_fieldList = '';
        $l_serLink   = $l_row['isys_search__link'];
        $l_aUnser    = unserialize($l_serLink);

        // replace white spaces WHY??
        // $l_searchText = preg_replace("/\s+/", "", $l_row['isys_search__title']);
        $l_searchText = $l_row['isys_search__title'];

        // make "#" sepparated values from search options array for javascript, see@function mysearch_change(script,element,p_elemAdvSearch){...}
        $l_fieldList = implode(
            '#',
            [
                $l_searchText,
                // search text
                $l_aUnser['C__SEARCH_OBJECTTYPES_HIDEN'],
                // object types
                $l_aUnser['C__SEARCH_CATEGORIES_HIDEN'],
                // categories
                $l_aUnser['C__SEARCH_OPTION'],
                // not option
                $l_aUnser['C__SEARCH_OPTION_LOP'],
                // operator
                $l_aUnser['worts'],
                // only words
                $l_aUnser['casesensitiv']
                // case sensitiv
            ]
        );

        // Build up SMARTY Array with bookmarks
        /*$l_aBookmarks[$l_row["isys_search__id"]] =
            array(
                "text" => $g_comp_template_language_manager->get("LC__UNIVERSAL__SEARCH_FOR").$l_row["isys_search__title"],
                "link" => "javascript:mysearch_change('index.php','ResponseContainer','".$l_fieldList."')"

            );
        */

        $l_fieldList = base64_encode($l_fieldList);

        $l_aBookmarks[$l_row["isys_search__id"]] = [
            "text" => $g_comp_template_language_manager->get("LC__UNIVERSAL__SEARCH_FOR") . $l_row["isys_search__title"],
            "link" => "javascript:mydoit_openSearchModul('" . C__GET__MODULE_ID . "','" . C__MODULE__SEARCH . "', '" . $l_fieldList . "')"
        ];
    } // while
} // if

$g_comp_template->assign(
    "mysearch_addCriterion",
    [
        "bookmarkList"  => $l_aBookmarks,
        "bookmarkCount" => $l_nBookmarks
    ]
);

/**
 * Delete criterias from DB
 */
function del_criterium()
{
    global $g_comp_database;

    // Delete selected entries
    if (isset($_POST["mysearchSelection"]))
    {
        foreach ($_POST["mysearchSelection"] as $l_selID => $l_selStatus)
        {
            $g_comp_database->query('DELETE FROM isys_search	WHERE isys_search__id = ' . (int) $l_selID . ';');
        }
    }
}

/**
 * Add Criterias to DB
 *
 * @param   integer $p_settingID
 *
 * @return  boolean
 */
function add_criterias($p_settingID)
{

    global $g_comp_database;

    // Not-checkbox for search text
    $l_searchOptionField1    = $_POST['C__SEARCH_OPTION_0'];
    $l_searchOptionField2    = $_POST['C__SEARCH_OPTION_1'];
    $l_searchOptionFieldsCsv = $l_searchOptionField1 . ',' . $l_searchOptionField2;

    $l_dao = new isys_cmdb_dao($g_comp_database);

    $l_search_arr = [];

    foreach ($_POST['C__SEARCH_TEXT'] AS $l_searchtext)
    {
        $l_search_arr[] = htmlentities(isys_helper::sanitize_text($l_searchtext), null, 'UTF-8');
    }
    $_POST['C__SEARCH_TEXT'] = $l_search_arr;

    $l_aPostsSer = serialize(
        [
            "C__SEARCH_OBJECTTYPES_HIDEN" => $_POST['C__SEARCH_OBJECTTYPES_HIDEN'],
            "C__SEARCH_CATEGORIES_HIDEN"  => $_POST['C__SEARCH_CATEGORIES_HIDEN'],
            "C__SEARCH_OPTION"            => $l_searchOptionFieldsCsv,
            "C__SEARCH_TEXT"              => $_POST['C__SEARCH_TEXT'],
            "C__SEARCH_OPTION_LOP"        => $_POST['C__SEARCH_OPTION_LOP'],
            "worts"                       => $_POST['worts'],
            "casesensitiv"                => $_POST['casesensitiv']
        ]
    );

    $l_aSeach = [];

    foreach ($_POST['C__SEARCH_TEXT'] as $l_searchValue)
    {
        $l_aSeach[] = $l_searchValue;
    }

    $l_aSeachCsv = implode(",", $l_aSeach);

    $l_aFields = [
        'isys_search__isys_user_setting__id' => $p_settingID,
        'isys_search__title'                 => $l_dao->convert_sql_text($l_aSeachCsv),
        'isys_search__link'                  => $l_dao->convert_sql_text($l_aPostsSer),
        'isys_search__date_added'            => 'now()'
    ];

    // insert into DB
    $l_result = $g_comp_database->query('INSERT INTO isys_search (' . implode(',', array_keys($l_aFields)) . ') VALUES(' . implode(',', array_values($l_aFields)) . ')');

    return ($l_result) ? true : false;
}

?>