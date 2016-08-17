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
 * Global Functions
 *
 * This file provides a globally available function library
 *
 * @package     i-doit
 * @subpackage  General
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define("C__FUNC__AJAX__CONTENT_BY_OBJECT", 0x101);
define("C__FUNC__AJAX__OBJECT_LIST", 0x102);
define("C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP", 0x103);
define("C__FUNC__AJAX__TREE", 0x104);
define("C__FUNC__AJAX__TREE_LOCATION", 0x105);

/**
 * Function for building ajax URLs.
 *
 * @param   integer $p_function
 * @param   array   $p_gets
 * @param   string  $p_category_param
 * @param   integer $p_cateID
 *
 * @return  string
 */
function isys_glob_build_ajax_url($p_function, $p_gets, $p_category_param = C__CMDB__GET__CATG, $p_cateID = null)
{
    $l_url = "";

    unset($p_gets[C__GET__AJAX]);
    unset($p_gets[C__GET__AJAX_CALL]);

    switch ($p_function)
    {
        case C__FUNC__AJAX__TREE_LOCATION:
            unset($p_gets[C__CMDB__GET__VIEWMODE]);

            if (!is_numeric($p_gets[C__CMDB__GET__OBJECT]))
            {
                unset($p_gets[C__CMDB__GET__OBJECT]);
            }
            $l_url = "get_tree('" . isys_glob_build_url(isys_glob_http_build_query($p_gets)) . "&call=tree');";
            break;

        case C__FUNC__AJAX__TREE:
            unset($p_gets[C__CMDB__GET__VIEWMODE]);
            $l_url = "get_tree_object_type('" . $p_gets[C__CMDB__GET__OBJECTGROUP] . "', false);";
            break;

        case C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP:
            if ($p_gets[C__CMDB__GET__OBJECTGROUP])
            {
                $l_url = "javascript:get_content_by_group(" . "'" . $p_gets[C__CMDB__GET__OBJECTGROUP] . "'," . "'" . $p_gets[C__CMDB__GET__VIEWMODE] . "'" . ");";
            }
            break;

        case C__FUNC__AJAX__CONTENT_BY_OBJECT:
            $l_url = "javascript:get_content_by_object('" . $p_gets[C__CMDB__GET__OBJECT] . "', '" . $p_gets[C__CMDB__GET__VIEWMODE] . "', '" . $p_gets[C__CMDB__GET__CATG] . "','" . $p_category_param . "'";

            if (!is_null($p_cateID))
            {
                $l_url .= ", '" . $p_cateID . "'";
            }
            $l_url .= ");";

            break;

        case C__FUNC__AJAX__OBJECT_LIST:
        default:
            break;
    } // switch

    return $l_url;
} // function

/**
 * Method for recursive striptagging.
 *
 * @param   mixed  String or Array value for stripping tags.
 *
 * @return  mixed
 */
function strip_tags_deep($p_value)
{
    return is_array($p_value) ? array_map('strip_tags_deep', $p_value) : strip_tags($p_value);
} // function

/**
 * Method for recursive stripslashing.
 *
 * @param   mixed  String or Array value for stripping slashes.
 *
 * @return  mixed
 */
function stripslashes_deep($p_value)
{
    return is_array($p_value) ? array_map('stripslashes_deep', $p_value) : stripslashes($p_value);
} // function

/**
 * Method for recursive addslashing.
 *
 * @param   mixed  String or Array value for adding slashes.
 *
 * @return  mixed
 */
function addslashes_deep($p_value)
{
    return is_array($p_value) ? array_map('addslashes_deep', $p_value) : addslashes($p_value);
} // function

/**
 * Get language constant from template language manager.
 *
 * @global  isys_component_template_language_manager $g_comp_template_language_manager
 *
 * @param   string                                   $p_language_constant
 * @param   mixed                                    $p_values
 *
 * @return  string
 */
function _L($p_language_constant, $p_values = null)
{
    global $g_comp_template_language_manager;

    return is_object($g_comp_template_language_manager) ? $g_comp_template_language_manager->get($p_language_constant, $p_values) : $p_language_constant;
} // function

/**
 * Replaces Special characters like ä ue ö é á (..) to a u o e a (..)
 *
 * @param string $p_string
 *
 * @return string
 */
function isys_glob_replace_accent($p_string)
{
    $l_a = [
        'À',
        'Á',
        'Â',
        'Ã',
        'Ä',
        'Å',
        'Æ',
        'Ç',
        'È',
        'É',
        'Ê',
        'Ë',
        'Ì',
        'Í',
        'Î',
        'Ï',
        'Ð',
        'Ñ',
        'Ò',
        'Ó',
        'Ô',
        'Õ',
        'Ö',
        'Ø',
        'Ù',
        'Ú',
        'Û',
        'Ü',
        'Ý',
        'ß',
        'à',
        'á',
        'â',
        'ã',
        'ä',
        'å',
        'æ',
        'ç',
        'è',
        'é',
        'ê',
        'ë',
        'ì',
        'í',
        'î',
        'ï',
        'ñ',
        'ò',
        'ó',
        'ô',
        'õ',
        'ö',
        'ø',
        'ù',
        'ú',
        'û',
        'ü',
        'ÿ',
        'Ā',
        'ā',
        'Ă',
        'ă',
        'Ą',
        'ą',
        'Ć',
        'ć',
        'Ĉ',
        'ĉ',
        'Ċ',
        'ċ',
        'Č',
        'č',
        'Ď',
        'ď',
        'Đ',
        'đ',
        'Ē',
        'ē',
        'Ĕ',
        'ĕ',
        'Ė',
        'ė',
        'Ę',
        'ę',
        'Ě',
        'ě',
        'Ĝ',
        'ĝ',
        'Ğ',
        'ğ',
        'Ġ',
        'ġ',
        'Ģ',
        'ģ',
        'Ĥ',
        'ĥ',
        'Ħ',
        'ħ',
        'Ĩ',
        'ĩ',
        'Ī',
        'ī',
        'Ĭ',
        'ĭ',
        'Į',
        'į',
        'İ',
        'ı',
        'Ĳ',
        'ĳ',
        'Ĵ',
        'ĵ',
        'Ķ',
        'ķ',
        'Ĺ',
        'ĺ',
        'Ļ',
        'ļ',
        'Ľ',
        'ľ',
        'Ŀ',
        'ŀ',
        'Ł',
        'ł',
        'Ń',
        'ń',
        'Ņ',
        'ņ',
        'Ň',
        'ň',
        'ŉ',
        'Ō',
        'ō',
        'Ŏ',
        'ŏ',
        'Ő',
        'ő',
        'Œ',
        'œ',
        'Ŕ',
        'ŕ',
        'Ŗ',
        'ŗ',
        'Ř',
        'ř',
        'Ś',
        'ś',
        'Ŝ',
        'ŝ',
        'Ş',
        'ş',
        'Š',
        'š',
        'Ţ',
        'ţ',
        'Ť',
        'ť',
        'Ŧ',
        'ŧ',
        'Ũ',
        'ũ',
        'Ū',
        'ū',
        'Ŭ',
        'ŭ',
        'Ů',
        'ů',
        'Ű',
        'ű',
        'Ų',
        'ų',
        'Ŵ',
        'ŵ',
        'Ŷ',
        'ŷ',
        'Ÿ',
        'Ź',
        'ź',
        'Ż',
        'ż',
        'Ž',
        'ž',
        'ſ',
        'ƒ',
        'Ơ',
        'ơ',
        'Ư',
        'ư',
        'Ǎ',
        'ǎ',
        'Ǐ',
        'ǐ',
        'Ǒ',
        'ǒ',
        'Ǔ',
        'ǔ',
        'Ǖ',
        'ǖ',
        'Ǘ',
        'ǘ',
        'Ǚ',
        'ǚ',
        'Ǜ',
        'ǜ',
        'Ǻ',
        'ǻ',
        'Ǽ',
        'ǽ',
        'Ǿ',
        'ǿ'
    ];
    $l_b = [
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'AE',
        'C',
        'E',
        'E',
        'E',
        'E',
        'I',
        'I',
        'I',
        'I',
        'D',
        'N',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'U',
        'U',
        'U',
        'U',
        'Y',
        's',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'ae',
        'c',
        'e',
        'e',
        'e',
        'e',
        'i',
        'i',
        'i',
        'i',
        'n',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'u',
        'u',
        'u',
        'u',
        'y',
        'y',
        'A',
        'a',
        'A',
        'a',
        'A',
        'a',
        'C',
        'c',
        'C',
        'c',
        'C',
        'c',
        'C',
        'c',
        'D',
        'd',
        'D',
        'd',
        'E',
        'e',
        'E',
        'e',
        'E',
        'e',
        'E',
        'e',
        'E',
        'e',
        'G',
        'g',
        'G',
        'g',
        'G',
        'g',
        'G',
        'g',
        'H',
        'h',
        'H',
        'h',
        'I',
        'i',
        'I',
        'i',
        'I',
        'i',
        'I',
        'i',
        'I',
        'i',
        'IJ',
        'ij',
        'J',
        'j',
        'K',
        'k',
        'L',
        'l',
        'L',
        'l',
        'L',
        'l',
        'L',
        'l',
        'l',
        'l',
        'N',
        'n',
        'N',
        'n',
        'N',
        'n',
        'n',
        'O',
        'o',
        'O',
        'o',
        'O',
        'o',
        'OE',
        'oe',
        'R',
        'r',
        'R',
        'r',
        'R',
        'r',
        'S',
        's',
        'S',
        's',
        'S',
        's',
        'S',
        's',
        'T',
        't',
        'T',
        't',
        'T',
        't',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'W',
        'w',
        'Y',
        'y',
        'Y',
        'Z',
        'z',
        'Z',
        'z',
        'Z',
        'z',
        's',
        'f',
        'O',
        'o',
        'U',
        'u',
        'A',
        'a',
        'I',
        'i',
        'O',
        'o',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'U',
        'u',
        'A',
        'a',
        'AE',
        'ae',
        'O',
        'o'
    ];

    return str_replace($l_a, $l_b, $p_string);
} // function

/**
 * isys_glob_utf8_encode wrapper.
 *
 * @deprecated
 *
 * @param   $p_string
 *
 * @return  mixed
 */
function isys_glob_utf8_encode($p_string)
{
    return $p_string;
}

/**
 * isys_glob_utf8_decode wrapper.
 *
 * @deprecated
 *
 * @param   $p_string
 *
 * @return  mixed
 */
function isys_glob_utf8_decode($p_string)
{
    return $p_string;
}

/**
 * Strips all non a-z and 0-9 characters, should be combined with isys_glob_replace_accent();
 *
 * @param   string $p_string
 * @param   string $p_replace_spaces_with
 *
 * @return  mixed
 */
function isys_glob_strip_accent($p_string, $p_replace_spaces_with = "-")
{
    return preg_replace(
        [
            '/[^a-zA-Z0-9 \._-]/',
            '/[ -]+/',
            '/^-|-$/'
        ],
        [
            '',
            $p_replace_spaces_with,
            ''
        ],
        $p_string
    );
} // function

/**
 * Escapes a string.
 *
 * @param   string &$p_string
 *
 * @return  string
 */
function isys_glob_escape_string(&$p_string)
{
    global $g_comp_database;

    return str_replace("\\\\", "\\", str_replace("'", "\'", $g_comp_database->escape_string($p_string)));
} // function

/**
 * Displays a logbook message (But does NOT save it into logbook).
 *
 * @param   string  $p_message
 * @param   integer $p_alert_level
 *
 * @return  isys_component_template_infobox
 */
function isys_glob_display_message($p_message, $p_alert_level = C__LOGBOOK__ALERT_LEVEL__3)
{

    return isys_component_template_infobox::instance()
        ->set_message(_L($p_message), null, null, null, $p_alert_level);
} // function

/**
 * Returns an array with all data from $p_arrDestination append the new data from $p_arrSource and
 * override exisiting data from $p_arrSource use this function intead of array_merge.
 *
 * @param   array $p_arrDestination
 * @param   array $p_arrSource
 *
 * @return  array
 */
function isys_glob_array_merge($p_arrDestination, $p_arrSource)
{
    if (is_array($p_arrSource))
    {
        foreach ($p_arrSource as $l_key_1 => $l_value_1)
        {
            $l_arr = $l_value_1;
            foreach ($l_arr as $l_key_2 => $l_value_2)
            {
                $p_arrDestination[$l_key_1][$l_key_2] = $l_value_2;
            } // foreach
        } // foreach
    } // if

    return $p_arrDestination;
} // function

/**
 * Calculate the days in month.
 *
 * @param  integer $p_month
 * @param  integer $p_year
 *
 * @return integer
 */
function isys_glob_days_in_month($p_month, $p_year)
{
    return $p_month == 2 ? ($p_year % 4 ? 28 : ($p_year % 100 ? 29 : ($p_year % 400 ? 28 : 29))) : (($p_month - 1) % 7 % 2 ? 30 : 31);
} // function

/**
 * @param $seconds
 *
 * @return string
 */
function isys_glob_seconds_to_human_readable($p_seconds)
{
    $l_days = floor($p_seconds / 86400);
    $p_seconds -= ($l_days * 86400);

    $l_hours = floor($p_seconds / 3600);
    $p_seconds -= ($l_hours * 3600);

    $l_minutes = floor($p_seconds / 60);
    $p_seconds -= ($l_minutes * 60);

    $l_values = [
        'day'    => $l_days,
        'hour'   => $l_hours,
        'minute' => $l_minutes,
        'second' => $p_seconds
    ];

    $parts = [];

    foreach ($l_values as $l_text => $l_value)
    {
        if ($l_value > 0)
        {
            $parts[] = $l_value . ' ' . $l_text . ($l_value > 1 ? 's' : '');
        }
    }

    return implode(' ', $parts);
}

/**
 * Gets the difference between two dates, dates need to be timestamps.
 * This is some old code (Don't know who wrote it) greatly updated by Leo (Snatched some code from the Kohana Framework).
 *
 * @param   integer $p_date_from
 * @param   integer $p_date_to
 * @param string    $p_format
 *
 * @return array
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
function isys_glob_date_diff($p_date_from = null, $p_date_to = null, $p_format = 'ymwdhis')
{
    $p_date_from = $p_date_from ?: time();
    $p_date_to   = $p_date_to ?: time();
    $l_diff      = abs($p_date_to - $p_date_from);
    $p_format    = array_unique(str_split(strtolower($p_format)));
    $l_return    = [];

    if (in_array('y', $p_format))
    {
        $l_diff -= isys_convert::YEAR * ($l_return['y'] = (int) floor($l_diff / isys_convert::YEAR));
    } // if

    if (in_array('m', $p_format))
    {
        $l_diff -= isys_convert::MONTH * ($l_return['m'] = (int) floor($l_diff / isys_convert::MONTH));
    } // if

    if (in_array('w', $p_format))
    {
        $l_diff -= isys_convert::WEEK * ($l_return['w'] = (int) floor($l_diff / isys_convert::WEEK));
    } // if

    if (in_array('d', $p_format))
    {
        $l_diff -= isys_convert::DAY * ($l_return['d'] = (int) floor($l_diff / isys_convert::DAY));
    } // if

    if (in_array('h', $p_format))
    {
        $l_diff -= isys_convert::HOUR * ($l_return['h'] = (int) floor($l_diff / isys_convert::HOUR));
    } // if

    if (in_array('i', $p_format))
    {
        $l_diff -= isys_convert::MINUTE * ($l_return['i'] = (int) floor($l_diff / isys_convert::MINUTE));
    } // if

    if (in_array('s', $p_format))
    {
        $l_return['s'] = $l_diff;
    } // if

    return $l_return;
} // function

/**
 * Unescapes a string
 *
 * @param   string $p_str
 *
 * @return  string
 * @author  Dennis Stuecken <dstuecken@i-doit.de>
 */
function isys_glob_unescape($p_str)
{
    if (is_object($p_str))
    {
        return $p_str;
    }

    return str_replace("\\", "", $p_str);
} // function

/**
 * Returns an array with 1 = yes and 0 = no.
 *
 * @return  array
 * @author  Niclas Potthast <npotthast@i-doit.org>
 */
function get_smarty_arr_YES_NO()
{
    return [
        "1" => _L("LC__UNIVERSAL__YES"),
        "0" => _L("LC__UNIVERSAL__NO")
    ];
} // function

/**
 * Validates if given hostname is allowed.
 *
 * @param   string $p_host
 *
 * @return  boolean
 */
function is_valid_hostname($p_host)
{
    $l_hostnames = explode(".", $p_host);

    if (empty($p_host)) return false;

    if (count($l_hostnames) > 1)
    {
        foreach ($l_hostnames as $l_host)
        {
            if ($l_host != "*")
            {
                if (!preg_match('/^[a-z\d\-]+$/i', $l_host))
                {
                    return false;
                } // if
            } // if
        } // foreach

        return true;
    }
    else
    {
        return match_hostname($p_host);
    } // if
} // function

/**
 * Checks if param is a valid hostname.
 *
 * @param   string $p_hostname
 *
 * @return  string
 */
function isys_glob_is_valid_hostname($p_hostname)
{
    return preg_match("/^[a-z0-9.-_]+$/i", $p_hostname);
} // function

/**
 * True, if param is a valid ip v4 address.
 *
 * @param   string $p_ip
 *
 * @return  boolean
 */
function isys_glob_is_valid_ip($p_ip)
{
    return (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $p_ip) == 0) ? false : true;
}

/**
 * Validates an ip v6 address.
 *
 * @param   string $p_ip
 *
 * @return  boolean
 */
function isys_glob_is_valid_ip6($p_ip)
{
    if (preg_match('/^[A-F0-9]{0,5}:[A-F0-9:]{1,39}$/i', $p_ip))
    {

        $l_p = explode(':::', $p_ip);
        if (count($l_p) > 1)
        {
            return false;
        }

        $l_p = explode('::', $p_ip);
        if (count($l_p) > 2)
        {
            return false;
        }

        $l_p = explode(':', $p_ip);

        if (count($l_p) > 8)
        {
            return false;
        }

        foreach ($l_p as $l_checkPart)
        {
            if (strlen($l_checkPart) > 4)
            {
                return false;
            }
        }

        return true;
    }

    return false;
}

/**
 * Default Template Handler. Called when Smarty's file: resource is unable to load a requested file.
 *
 * @param   string                  $p_res_type Resource type (e.g. "file", "string", "eval", "resource")
 * @param   string                  $p_res_name Resource name (e.g. "foo/bar.tpl")
 * @param   string                  &$p_template_source
 * @param   integer                 &$p_template_timestamp
 * @param   isys_component_template $p_smarty_obj
 *
 * @return  mixed  Path to file or boolean true if $content and $modified have been filled, boolean false if no default template could be loaded
 * @author   Dennis Stücken <dstuecken@synetics.de>
 */
function isys_glob_template_handler($p_res_type, $p_res_name, &$p_template_source, &$p_template_timestamp, isys_component_template $p_smarty_obj)
{
    if ($p_res_type == "file")
    {
        if (!is_readable($p_res_name))
        {
            $l_default_file = __DIR__ . '/themes/default/smarty/templates/' . $p_res_name;
            $l_default_file = str_replace('./', '', $l_default_file);

            if ($l_default_file && file_exists($l_default_file))
            {
                $p_template_timestamp = time();
                $p_template_source    = file_get_contents($l_default_file);

                return true;
            } // if
        } // if
    } // if

    return false;
} // function

/**
 * Override the user's settings.
 *
 * @author Dennis Stücken <dstuecken@synetics.de>
 */
function isys_glob_override_user_settings()
{
    global $g_comp_database, $g_config, $g_dirs, $g_reference_colors, $g_current_theme;

    if (is_object($g_comp_database))
    {
        $g_comp_user        = isys_component_dao_user::instance($g_comp_database);
        $g_current_theme    = $g_comp_user->get_user_theme_as_string();
        $g_reference_colors = $g_comp_user->get_reference_coloration();
    }
    else
    {
        $g_reference_colors = [];
    } // if

    // If no current theme is selected (if the session is expired), use default one.
    if (empty($g_current_theme))
    {
        $g_current_theme = $g_config["theme"];
    } // if

    // The GET-Parameter 'theme' has the highest priority - it should be used from the cache handlers!
    /* @see  ID-1220
     * if (isset($_GET["theme"]))
     * {
     * $g_current_theme = $_GET["theme"];
     * } // if
     */

    $g_dirs["css_abs"] = preg_replace("/themes\/(.+?)\//i", "themes/" . $g_current_theme . "/", $g_dirs["css_abs"]);

    // Replace "theme_images" with the current theme directory.
    if (!empty($g_dirs["theme_images"]))
    {
        $g_dirs["theme_images"] = preg_replace("/themes\/(.+?)\//i", "themes/" . $g_current_theme . "/", $g_dirs["theme_images"]);
    }
    else
    {
        $g_dirs["theme_images"] = $g_config["www_dir"] . "src/themes/" . $g_current_theme . "/images/";
    } // if

    $g_dirs["smarty"] = preg_replace("/themes\/(.+?)\//i", "themes/" . $g_current_theme . "/", $g_dirs["smarty"]);
    $g_dirs["theme"]  = preg_replace("/themes\/(.+?)\//i", "themes/" . $g_current_theme . "/", $g_dirs["theme"]);
    $g_dirs["images"] = $g_config["www_dir"] . "images/";

    $g_config["theme"] = $g_current_theme;

    return true;
} // function

/**
 * Deletes a directory recursively.
 *
 * @param   string $p_startdir
 * @param   string &$p_deleted
 * @param   string &$p_undeleted
 *
 * @return  boolean
 */
function isys_glob_delete_recursive($p_startdir, &$p_deleted, &$p_undeleted)
{
    if (empty($p_startdir) || file_exists($p_startdir) === false)
    {
        return false;
    } // if

    if (is_file($p_startdir) || is_link($p_startdir))
    {
        $p_deleted++;

        return unlink($p_startdir);
    } // if

    $l_files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p_startdir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($l_files as $l_fileinfo)
    {
        /* @var  SplFileInfo $l_fileinfo */
        if ($l_fileinfo->isDir())
        {
            isys_glob_delete_recursive($l_fileinfo->getRealPath(), $p_deleted, $p_undeleted);

            if (rmdir($l_fileinfo->getRealPath()) === false)
            {
                $p_undeleted++;
            }
            else
            {
                $p_deleted++;
            } // if
        }
        else
        {
            /*
            // do not remove .gitkeep files if devmode is active
            if (strstr($l_fileinfo->getRealPath(), '.gitkeep'))
            {
                if (isys_settings::get('system.devmode', false))
                {
                    continue;
                }
            }
            */

            if (unlink($l_fileinfo->getRealPath()) === false)
            {
                $p_undeleted++;
            }
            else
            {
                $p_deleted++;
            } // if
        } // if
    } // foreach

    return true;
} // function

/**
 * This functions dies with a message defined by the specified parameters. $p_file should be __FILE__ and $p_line __LINE__.
 *
 * @param  string  $p_file
 * @param  integer $p_line
 * @param  string  $p_message
 */
function isys_glob_die($p_file, $p_line, $p_message)
{
    die("In " . $p_file . "/" . $p_line . ": " . $p_message);
} // function

/**
 * Builds a TCP Address.
 *
 * @param   string  $p_host
 * @param   integer $p_port
 *
 * @return  string
 */
function isys_glob_create_tcp_address($p_host, $p_port)
{
    return $p_host . ":" . $p_port;
} // function

/**
 * Returns the temporary directory.
 *
 * @return  string
 */
function isys_glob_get_temp_dir()
{
    global $g_dirs;

    return $g_dirs["temp"];
} // function

/**
 * Escapes a string.
 *
 * @param   string $p_string
 *
 * @return  string
 */
function &isys_glob_prepare_string(&$p_string)
{
    $p_string = str_replace("\\", "\\\\", $p_string);
    $p_string = str_replace("\"", "\\\"", $p_string);

    return $p_string;
} // function

/**
 * Returns a javascript block with $p_string in it.
 *
 * @param   string $p_string
 *
 * @return  string
 */
function isys_glob_js_print($p_string)
{
    return '<script language="javascript" type="text/javascript">
		try {
		' . $p_string . '
		} catch (e) {
			if (typeof idoit.Notify === "object") {
				idoit.Notify.error(e.message, {sticky:true});
			} else {
				alert(e.message);
			}
		}</script>';
} // function

/**
 * Returns a variable preformatted.
 *
 * @param   mixed $p_var
 *
 * @return  string
 */
function isys_glob_var_export($p_var)
{
    return '<pre>' . var_export($p_var, true) . '</pre>';
} // function

/**
 * Returns a parameter which was send via get or post or false if no parameter was found.
 *
 * @param   string $p_key
 *
 * @return  mixed   Mixed value if the key is found - otherwise boolean false.
 */
function isys_glob_get_param($p_key)
{
    if (isset($_GET[$p_key]))
    {
        return $_GET[$p_key];
    }
    else if (isset($_POST[$p_key]))
    {
        return $_POST[$p_key];
    }
    else
    {
        return false;
    } // if
} // function

/**
 * give post params higher priority than get
 *
 * @param   string $p_param
 *
 * @return  mixed  post or get param
 */
function isys_glob_get_param_invert($p_param)
{
    return (isset($_POST[$p_param])) ? $_POST[$p_param] : $_GET[$p_param];
} // function

/**
 * Returns mandator string from session variable or from the db
 *
 * @param   integer $p_id
 *
 * @return  string
 */
function isys_glob_get_mandant_name_as_string($p_id)
{
    global $g_comp_session;
    global $g_comp_database;
    global $g_db;
    global $g_config;

    $l_strMandatorName = $g_comp_session->get_mandator_name();

    if (isys_strlen($l_strMandatorName) > 0)
    {
        return $l_strMandatorName;
    }

    $l_table_mandator = "isys_mandator";

    $l_mandant_dao = $g_comp_session->get_mandator_dao($p_id);

    try
    {
        if (!is_null($g_comp_database) && $g_comp_database->num_rows($l_mandant_dao) > 0)
        {
            $l_row = $g_comp_database->fetch_array($l_mandant_dao);
            $g_comp_session->set_mandator_name($l_row['isys_mandator__title']);
        }
        else
        {

            if ($g_comp_session->logout())
            {
                $l_logoutmsg = "I resetted your session now. You may just need to refresh your browser and login again.";
            }
            else
            {
                $l_logoutmsg = "You may need to restart your browser to reset your current session.";
            }

            $l_message = "Error: Could not retrieve the mandators name from System-DB. (Table: {$l_table_mandator})\n" . "Used ID: \"{$p_id}\"\n\n" . "If you don't see any ID, your session might be broken. " . $l_logoutmsg;

            throw new isys_exception_database ($l_message, $g_db);

        }
    }
    catch (isys_exception_database $e)
    {
        // Don't make any output, but log the error.
        $e->write_log();
    } // try

    return $g_comp_session->get_mandator_name();
}

/**
 * Get the directory name for mandator-cache.
 *
 * @param   integer $p_id The (optional) mandator-ID.
 *
 * @return  string
 * @author  Leonard Fischer <lfischer@synetics.de>
 */
function isys_glob_get_mandator_cache_dir($p_id = null)
{
    if (null === $p_id)
    {
        $p_id = $_SESSION['user_mandator'];
    } // if

    return 'cache_' . isys_glob_get_mandant_name_as_string($p_id);
} // function

/**
 * Get the direction of the tabledata-order and append sql-syntax to the given parameter.
 *
 * @param   string $p_strSQL
 *
 * @return  string
 */
function isys_glob_sql_append_order($p_strSQL)
{
    if (isys_glob_get_param("sort") != false)
    {
        $l_sort      = isys_glob_get_param("sort");
        $l_direction = isys_glob_get_param("dir");
        $p_strSQL .= " ORDER BY $l_sort $l_direction";
    } // if

    return $p_strSQL;
} // function

/**
 * Returns ASC or DESC, depending on the value in the url.
 *
 * @return  string
 */
function isys_glob_get_order()
{
    if (isys_glob_get_param("dir") == "DESC")
    {
        return "ASC";
    } // if

    return "DESC";
} // function

/**
 * Removes a GET parameter from an URL.
 *
 * @param   string &$p_url
 * @param   string $p_parameter
 *
 * @return  string
 * @todo    Use isys_helper_link::remove_params_from_url();
 */
function isys_glob_url_remove($p_url, $p_parameter)
{
    $p_url = preg_replace("/(\?)" . $p_parameter . "=(.+?)(&|$)/", "\\1", $p_url);
    $p_url = preg_replace("/(&)" . $p_parameter . "=(.+?)(&|$)/", "\\3", $p_url);

    return $p_url;
} // function

/**
 * Returns a string javascript-formatted
 *
 * @param string $p_string
 *
 * @return string
 */
function isys_glob_js_string($p_string)
{
    return "'" . str_replace(
        [
            "\\\\",
            "\n",
            "'"
        ],
        [
            "\\",
            "\\n",
            "\\'"
        ],
        $p_string
    ) . "'";
} //function

/**
 * Returns a string that is either $p_url (if you pass it) or the current URI, appended with "$p_key"="$p_value".
 *
 * @param   string $p_key
 * @param   string $p_value
 * @param   string $p_url
 *
 * @return  string
 * @author  Dennis Stuecken <dstuecken@i-doit.org>
 * @version Selcuk Kekec    <skekec@i-doit.org>
 * @todo    Use isys_helper_link::add_params_to_url();
 */
function isys_glob_add_to_query($p_key, $p_value, $p_url = null)
{
    /* Get default get-params */
    if (empty($p_url))
    {
        $p_url = $_GET;
    }
    else
    {
        /* Remove '?' from the beginning of the delivered query */
        if (is_string($p_url) && isys_strlen($p_url) && $p_url[0] == "?") $p_url = substr($p_url, 1);

        /* Explode it to an array */
        $p_url = explode("&", $p_url);
    }

    /* Set/Replace the given KEY in our params-array */
    $p_url[$p_key] = $p_value;

    return "?" . http_build_query($p_url);
} // function

/**
 * Generates a URL-encoded query string (This is a wrapper for the function http_build_query).
 * Formdata may be an array or object containing properties. A formdata array may be a simple one-dimensional structure, or an array of arrays (who in turn may contain other arrays).
 * If numeric indices are used in the base array and a numeric_prefix is provided, it will be prepended to the numeric index for elements in the base array only.
 * This is to allow for legal variable names when the data is decoded by PHP or another CGI application later on.
 *
 * @param   array $p_arData
 *
 * @return  string
 */
function isys_glob_http_build_query($p_arData)
{
    return http_build_query(((count($p_arData) > 0) ? $p_arData : []), null, '&');
} // function

/**
 * Stops the string at a given position.
 *
 * @param   string  $p_string
 * @param   integer $p_length
 * @param   string  $p_etc
 *
 * @return  string
 */
function isys_glob_cut_string($p_string, $p_length = 100, $p_etc = "..")
{
    global $g_config;
    if (isys_strlen($p_string) > $p_length)
    {
        $p_length -= isys_strlen($p_etc);

        if (function_exists('mb_substr'))
        {
            $l_string = mb_substr($p_string, 0, $p_length, $g_config['html-encoding']);
        }
        else
        {
            $l_string = substr($p_string, 0, $p_length);
        } // if

        return $l_string . $p_etc;
    }
    else
    {
        return $p_string;
    } // if
} // function

/**
 * Stops a string and appends.
 *
 * @param   string  $p_str
 * @param   integer $p_maxlen
 * @param   string  $p_appending
 *
 * @return  string
 */
function isys_glob_str_stop($p_str, $p_maxlen, $p_appending = "..")
{
    return isys_glob_cut_string($p_str, $p_maxlen, $p_appending);
} // function

/**
 * Returns the current date and time in datetime syntax: "YYYY-MM-DD HH:MM".
 *
 * @return  string
 * @author  Niclas Potthast <npotthast@i-doit.org>
 */
function isys_glob_datetime()
{
    return date("Y-m-d H:i:s");
} // function

/**
 * Formats a datetime string.
 *
 * @param   string  $p_strDatetime
 * @param   boolean $p_bTime
 *
 * @return  string
 * @author  Niclas Potthast <npotthast@i-doit.org>
 * @todo    Format has to be user specific (from user preferences)
 */
function isys_glob_format_datetime($p_strDatetime, $p_bTime = false)
{
    if (strlen($p_strDatetime) >= 10)
    {
        if ($p_bTime)
        {
            return $p_strDatetime;
        }
        else
        {
            $p_strDatetime = substr($p_strDatetime, 0, 10);

            if (substr_count($p_strDatetime, "0000") > 0)
            {
                return '';
            } // if

            return $p_strDatetime;
        } // if
    } // if

    return $p_strDatetime;
} // function

/**
 * Builds temporary table name for object lists.
 *
 * @param   string $p_tblName
 * @param   string $p_sesID
 *
 * @return  string
 * @author  Niclas Potthast <npotthast@i-doit.org>
 */
function isys_glob_get_obj_list_table_name($p_tblName = null, $p_sesID = null)
{
    global $g_comp_session;

    if ($p_sesID)
    {
        $l_sesID = $p_sesID;
    }
    else
    {
        $l_sesID = $g_comp_session->get_session_id();
    } // if

    if (!$p_tblName)
    {
        $l_tblName = "tempObjList_";
    }
    else
    {
        $l_tblName = $p_tblName;
    } // if

    return $l_tblName . md5($l_sesID);
} // function

/**
 * Returns a DAO result object with the table entries.
 *
 * @param   string                  $p_tbl
 * @param   isys_component_database $p_dbo
 * @param   integer                 $p_status
 * @param   string                  $p_order
 * @param   string                  $p_condition
 *
 * @return  isys_component_dao_result
 */
function isys_glob_get_data_by_table($p_tbl, $p_dbo = null, $p_status = C__RECORD_STATUS__NORMAL, $p_order = null, $p_condition = null)
{
    global $g_comp_database;

    // Determine database object to user
    $l_dbo = $p_dbo;

    if ($p_dbo == null)
    {
        $l_dbo = $g_comp_database;
    } // if

    if (is_object($l_dbo))
    {
        // Return DAO result with table entries
        $l_sql = 'SELECT * FROM ' . $p_tbl . ' WHERE TRUE';

        if (!empty($p_condition))
        {
            $l_sql .= " AND (" . $p_condition . ")";
        }

        if (!empty($p_status))
        {
            $l_sql .= " AND (" . $p_tbl . "__status = " . $p_status . ") ";
        }

        if (!strpos($p_tbl, '_catg_') && !strpos($p_tbl, '_cats_'))
        {
            if (is_null($p_order))
            {
                $l_sql .= " ORDER BY " . $p_tbl . "__title ASC;";
            }
            else if ($p_order)
            {
                $l_sql .= " ORDER BY " . $p_order . " ASC;";
            }
        }

        return new isys_component_dao_result($l_dbo, $l_dbo->query($l_sql));
    } // if

    return null;
} // function

/**
 * Array_merge that preserves keys, truly accepts an arbitrary number of arguments, and saves space on the stack (non recursive).
 *
 * @return  array
 */
function isys_array_merge_keys()
{
    $l_result = [];
    $l_args   = func_get_args();

    foreach ($l_args as $l_array)
    {
        foreach ($l_array as $l_key => $l_value)
        {
            $l_result[$l_key] = $l_value;
        } // foreach
    } // foreach

    return $l_result;
} // function

/**
 * Replace entries in $p_arr in $p_str. [KEY] is substituted by value in array.
 *
 * @param   string $p_str
 * @param   array  $p_arr
 *
 * @return  string
 */
function isys_glob_str_replace($p_str, $p_arr)
{
    if (is_array($p_arr))
    {
        foreach ($p_arr as $l_subst => $l_val)
        {
            $p_str = str_replace("[" . $l_subst . "]", $l_val, $p_str);
        } // foreach

        return $p_str;
    } // if

    return null;
} // function

/**
 * Resets a variable type. Also detects booleans.
 *
 * @param  mixed &$p_var
 */
function isys_glob_reset_type(&$p_var)
{
    $l_vartype = gettype($p_var);

    if ($l_vartype == 'string')
    {
        if ($p_var == "true" || $p_var == "false")
        {
            $l_vartype = "boolean";
        } // if
    } // switch

    settype($p_var, $l_vartype);
} // function

/**
 * Returns the browser type and version.
 *
 * @return  array
 * @author  Niclas Potthast <npotthast@i-doit.org>
 */
function _get_browser()
{
    $l_arBrowser = [
        "OPERA",
        "MSIE",
        "NETSCAPE",
        "FIREFOX",
        "SAFARI",
        "KONQUEROR",
        "MOZILLA"
    ];

    $l_info['type'] = "OTHER";

    foreach ($l_arBrowser as $l_parent)
    {
        if (($l_s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $l_parent)) !== false)
        {
            $l_f               = $l_s + strlen($l_parent);
            $l_version         = substr($_SERVER['HTTP_USER_AGENT'], $l_f, 5);
            $l_version         = preg_replace('/[^0-9,.]/', '', $l_version);
            $l_info['type']    = $l_parent;
            $l_info['version'] = $l_version;
            break; // first match wins
        } // if
    } // foreach

    return $l_info;
} // function

/**
 * Gets the current URL and adds a query to it. For example:
 * key=value&key2=value2 -> http://www.example.com/index.php?key=value&key2=value2
 *
 * @param   string $p_query
 *
 * @return  string
 */
function isys_glob_build_url($p_query)
{
    global $g_config;

    return $g_config["startpage"] . "?" . $p_query;
} // function

/**
 * @param  isys_module_request $p_modreq
 */
function isys_glob_merge_globals_by_modreq(isys_module_request &$p_modreq)
{
    global $GLOBALS;

    $GLOBALS["_GET"]  = array_merge($GLOBALS["_GET"], $p_modreq->get_gets());
    $GLOBALS["_POST"] = array_merge($GLOBALS["_POST"], $p_modreq->get_posts());
} // function

/**
 * If given several parameters, this function will return the first one, which is set (not null, false, empty, ...).
 *
 * @return  mixed
 */
function isys_glob_which_isset()
{
    $l_aargs = func_get_args();

    foreach ($l_aargs as $l_arg)
    {
        if (@isset($l_arg))
        {
            return $l_arg;
        } // if
    } // foreach

    return null;
} // function

/**
 * Makes a formatted date from p_datestring using strtotime.
 *
 * @param   string $p_datestring
 * @param   string $p_format
 *
 * @return  string
 */
function isys_glob_mkdate($p_datestring, $p_format)
{
    return date($p_format, strtotime($p_datestring));
} // function

/**
 * Returns array with all language constant-strings from isys_language (system database), excluding 'ISYS_LANGUAGE_ALL'.
 *
 * @global  $g_comp_database
 * @return  array
 */
function isys_glob_get_language_constants()
{
    $l_return = [];

    $l_res = isys_component_dao::factory(isys_application::instance()->database_system)
        ->retrieve('SELECT isys_language__title, isys_language__const FROM isys_language WHERE isys_language__const != "ISYS_LANGUAGE_ALL";');

    if (count($l_res) > 0)
    {
        while ($l_row = $l_res->get_row())
        {
            $l_return[constant($l_row['isys_language__const'])] = $l_row['isys_language__title'];
        } // while
    } // if

    return $l_return;
} // function

/**
 * Displays a html formatted error
 *
 * @param  string $p_message
 */
function isys_glob_display_error($p_message)
{
    ob_end_clean();

    echo '<style>body {background-color:transparent;} .error {background-color:#ffdddd; border:1px solid #ff4343; color: #701719; overflow:auto; padding:10px;}</style>' . '<div><img style="float:right; margin-left: 15px; margin-right:5px;" width="100" src="images/logo.png" /><p class="error">' . $p_message . '</p></div>';
} // function

/**
 * This method originally comes from http://de2.php.net/ip2long by a guy named "anjo2".
 *
 * @param   string $p_ip The IP to be converted
 *
 * @return  mixed  String if everything went well, null if function "inet_pton" is not available.
 * @author  Leonard Fischer <lfischer@synetics.de>
 */
function isys_glob_ip2bin($p_ip)
{
    if (function_exists('inet_pton') && function_exists('inet_pton'))
    {
        if (filter_var($p_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
        {
            return base_convert(ip2long($p_ip), 10, 2);
        } // if

        if (filter_var($p_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
        {
            return false;
        } // if

        if (defined('AF_INET6'))
        {
            $l_ipbin = '';

            // inet_pton is only available for UNIX (PHP5) and Windows (PHP 5.3).
            if (($l_ip_n = inet_pton($p_ip)) === false)
            {
                return false;
            } // if

            // 16 x 8 bit = 128bit (ipv6).
            $l_bits = 15;

            while ($l_bits >= 0)
            {
                $l_bin   = sprintf("%08b", (ord($l_ip_n[$l_bits])));
                $l_ipbin = $l_bin . $l_ipbin;
                $l_bits--;
            } // while

            return $l_ipbin;
        } // if
    } // if

    return null;
} // function

/**
 * This method originally comes from http://de2.php.net/ip2long by a guy named "anjo2".
 *
 * @param   string $p_bin The binary
 *
 * @return  mixed  String if everything went well, null if "inet_pton" function does not exist.
 * @author  Leonard Fischer <lfischer@synetics.de>
 */
function isys_glob_bin2ip($p_bin)
{
    if (function_exists('inet_pton') && function_exists('inet_pton'))
    {
        // 32bits (ipv4).
        if (strlen($p_bin) <= 32)
        {
            return long2ip(base_convert($p_bin, 2, 10));
        } // if

        if (strlen($p_bin) != 128)
        {
            return false;
        } // if

        if (defined('AF_INET6'))
        {
            $l_pad = 128 - strlen($p_bin);

            for ($i = 1;$i <= $l_pad;$i++)
            {
                $p_bin = "0" . $p_bin;
            } // for

            $l_bits = 0;
            $l_ipv6 = '';
            while ($l_bits <= 7)
            {
                $l_bin_part = substr($p_bin, ($l_bits * 16), 16);
                $l_ipv6 .= dechex(bindec($l_bin_part)) . ":";
                $l_bits++;
            } // while

            return inet_ntop(inet_pton(substr($l_ipv6, 0, -1)));
        } // if
    } // if

    return null;
} // function

/**
 * With this function we can be sure that we're in "edit mode".
 *
 * @author  Leonard Fischer <lfischer@synetics.de>
 * @return  boolean
 * @since   0.9.9-8
 */
function isys_glob_is_edit_mode()
{
    return (bool) (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW || isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT || isys_glob_get_param(
            C__CMDB__GET__EDITMODE
        ) == C__EDITMODE__ON);
} // function

/**
 * Function for putting the given backtrace in a nice readable form into a file - helpful for debugging!
 *
 * @param   array   $p_backtrace
 * @param   boolean $p_append
 * @param   boolean $p_show_args
 * @param   integer $p_limit
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
function print_backtrace_file($p_backtrace = null, $p_append = false, $p_show_args = false, $p_limit = 0)
{
    global $g_dirs;

    $l_content = [];

    if ($p_backtrace === null)
    {
        $p_backtrace = _Backtrace($p_limit, $p_show_args);
    }

    if (!is_array($p_backtrace))
    {
        $l_content[] = 'Given backtrace is no array... It\'s a "' . gettype($p_backtrace) . '".';
    }
    else
    {
        foreach ($p_backtrace as $l_trace)
        {
            $l_content[] = $l_trace['file'] . ' (' . $l_trace['line'] . ")\n   " . $l_trace['class'] . ' -> ' . $l_trace['function'] . '()';
        } // foreach
    } // if

    file_put_contents($g_dirs['temp'] . 'backtrace_output.txt', implode("\n", $l_content) . "\n\n", ($p_append ? FILE_APPEND : 0));
} // function

/**
 * Function for putting the value into a file - helpful for debugging!
 *
 * @param   mixed   $p_value
 * @param   boolean $p_append
 *
 * @author  Van Quyen Hoang <qhoang@i-doit.org>
 */
function print_ar_file($p_value, $p_append = false)
{
    global $g_dirs;

    file_put_contents($g_dirs['temp'] . 'debug_output.txt', var_export($p_value, true) . "\n", ($p_append ? FILE_APPEND : 0));
} // function

/**
 * function for dumping a formatted output on screen (helpful for debugging).
 *
 * @param   mixed $p_value
 *
 * @author  Van Quyen Hoang <qhoang@i-doit.org>
 */
function print_ar($p_value)
{
    if (!empty($p_value))
    {
        echo '<pre>' . var_export($p_value, true) . '</pre>';
    }
    else
    {
        echo "Content is empty!";
    } // if
} // function

/**
 * html_entities Wrapper.
 *
 * @param   string  $p_val
 * @param   integer $p_flags
 * @param   string  $p_encoding
 * @param   boolean $p_double_enc
 *
 * @return  string
 */
function isys_glob_htmlentities($p_val, $p_flags = ENT_QUOTES, $p_encoding = null, $p_double_enc = false)
{
    $p_encoding = $p_encoding ?: $GLOBALS['g_config']['html-encoding'];

    if (is_string($p_val))
    {
        return htmlentities($p_val, $p_flags, $p_encoding, $p_double_enc);
    } // if

    else return '';
} // function

/**
 * html_specialchars Wrapper.
 *
 * @param   string  $p_val
 * @param   integer $p_flags
 * @param   string  $p_encoding
 * @param   boolean $p_double_enc
 *
 * @return  string
 */
function isys_glob_htmlspecialchars($p_val, $p_flags = ENT_QUOTES, $p_encoding = null, $p_double_enc = false)
{
    $p_encoding = (empty($p_encoding)) ? $GLOBALS['g_config']['html-encoding'] : $p_encoding;

    return htmlspecialchars($p_val, $p_flags, $p_encoding, $p_double_enc);
} // function

/**
 * Compare two arrays by array key 'title'
 *
 * @param   mixed $p_x
 * @param   mixed $p_y
 *
 * @return  mixed
 */
function isys_glob_array_compare_title($p_x, $p_y)
{
    if (is_array($p_x) && isset($p_x['title']) && isset($p_y['title']))
    {
        return strcmp($p_x['title'], $p_y['title']);
    }
    else if (is_string($p_x))
    {
        return strcmp($p_x, $p_y);
    }
    else
    {
        return false;
    } // if
} // function

/**
 * Returns the defined page-limit.
 *
 * @global  integer $g_page_limit
 * @return  integer
 */
function isys_glob_get_pagelimit()
{
    global $g_page_limit;

    if (!is_numeric($g_page_limit))
    {
        $g_page_limit = isys_usersettings::get('gui.objectlist.rows-per-page', 50);
    } // if

    return $g_page_limit;
} // function

/**
 * Sorting mechanism for multidimensional arrays
 *
 * @param array     $p_array
 * @param   string  $p_field
 * @param   integer $p_direction
 *
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 */
function isys_glob_sort_array_by_column(array &$p_array, $p_field, $p_direction = SORT_ASC)
{
    $l_sort_array = [];

    foreach ($p_array AS $l_key => $l_value)
    {
        $l_sort_array[$l_key] = $l_value[$p_field];
    } // foreach

    array_multisort($l_sort_array, $p_direction, $p_array);
} // function

/**
 * Wrapper function for function debug_backtrace. Good for debugging.
 *
 * @param   integer $p_limit
 * @param   boolean $p_show_args
 *
 * @return  array
 * @author  Van Quyen Hoang <qhoang@i-doit.org>
 */
function _Backtrace($p_limit = 0, $p_show_args = false)
{
    $l_option    = ($p_show_args) ? DEBUG_BACKTRACE_PROVIDE_OBJECT : DEBUG_BACKTRACE_IGNORE_ARGS;
    $l_backtrace = debug_backtrace($l_option, (($p_limit > 0) ? $p_limit + 1 : $p_limit));

    unset($l_backtrace[0]);

    return $l_backtrace;
} // function

/**
 * Function to find out if a given array is associative.
 *
 * @param   array $p_arr
 *
 * @return  boolean
 * @author  Leonard Fischer <lfischer@i-doit.com>
 * @see     http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
 */
function is_assoc($p_arr)
{
    return (bool) count(array_filter(array_keys($p_arr), 'is_string'));
} // function

/**
 * This function accepts "string parts" for searching a array (other than "array_search").
 *
 * @param   string $needle
 * @param   array  $haystack
 *
 * @return  mixed
 * @author  Leonard Fischer <lfischer@i-doit.com>
 * @see     http://php.net/manual/de/function.array-search.php#90711
 */
function array_find($needle, array $haystack)
{
    foreach ($haystack as $item)
    {
        if (strpos($item, $needle) !== false)
        {
            return $item;
        } // if
    } // foreach

    return null;
} // function

/**
 * Function which will return the string length. Will use mb_strlen if available.
 *
 * @param   string $p_string
 *
 * @return  integer
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
function isys_strlen($p_string)
{
    global $g_config;

    return (function_exists('mb_strlen') ? mb_strlen($p_string, $g_config['html-encoding']) : strlen($p_string));
} // function

/**
 * Assert callback function.
 *
 * @param   string  $p_file    The file, at which the assertion happened.
 * @param   integer $p_line    The line, at which the assertion is placed.
 * @param   string  $p_message The assertion-code.
 *
 * @author  Leonard Fischer <lfischer@i-doit.org>
 */
function isys_glob_assert_callback($p_file, $p_line, $p_message)
{
    // End the output buffering.
    ob_end_clean();

    // Configuration
    $l_prev_lines = 3;
    $l_next_lines = 3;

    // Read the file in a Array for display purpose.
    $l_file_data = file($p_file);

    // Define the start point of viewing the code.
    $l_start = $p_line - $l_prev_lines;
    if ($l_start < 0)
    {
        $l_start = 0;
    } // if

    // Define the end point of viewing the code.
    $l_end = $p_line + $l_next_lines;
    if ($l_end > count($l_file_data))
    {
        $l_file_data[] = '- End of file';
        $l_end         = count($l_file_data);
    } // if

    $l_error = '';

    // Preparing the error style.
    $l_error .= '<style type="text/css">pre.error {background-color:#ddd; border:1px solid #aaa; color:#444; overflow:auto; padding:10px;} pre.error span {display:block;}</style>';

    // Start echo'ing the formatted error-message.
    $l_error .= '<pre class="error"><b>Assertion Error in file "' . $p_file . '" (l.' . $p_line . '): "' . $p_message . '"</b>' . PHP_EOL;

    for ($i = $l_start;$i <= $l_end;$i++)
    {
        $l_error .= '<span ' . (($i == $p_line) ? 'style="background:#ffff00;"' : '') . '>' . (($i != count($l_file_data)) ? $i . ': ' : '') . isys_glob_htmlentities(
                str_replace(PHP_EOL, '', $l_file_data[($i - 1)])
            ) . '</span>';
    } // for

    $l_bt = debug_backtrace();

    $l_class    = $l_bt[3]['class'];
    $l_function = $l_bt[3]['function'];
    $l_file     = $l_bt[2]['file'];
    $l_line     = $l_bt[2]['line'];

    $l_error .= PHP_EOL . "Called in: {$l_class}::{$l_function} in {$l_file} at {$l_line}</pre>";

    isys_application::instance() ->container["notify"] ->error($l_error);
} // function

/**
 * The "which" command (show the full path of a command).
 *
 * @param      string $p_program  The command to search for
 * @param      mixed  $p_fallback Value to return if $program is not found
 *
 * @return     mixed  A string with the full path or false if not found
 *
 * @category   pear
 * @package    System
 * @author     Tomas V.V.Cox <cox@idecnet.com>
 * @author     Stig Bakken <ssb@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: System.php 313024 2011-07-06 19:51:24Z dufuz $
 * @link       http://pear.php.net/package/PEAR
 */
function system_which($p_program, $p_fallback = false)
{
    // enforce API.
    if (!is_string($p_program) || '' == $p_program)
    {
        return $p_fallback;
    } // if

    // full path given.
    if (basename($p_program) != $p_program)
    {
        $l_path_elements[] = dirname($p_program);
        $p_program         = basename($p_program);
    }
    else
    {
        // Honor safe mode.
        if (!ini_get('safe_mode') || !$l_path = ini_get('safe_mode_exec_dir'))
        {
            $l_path = getenv('PATH');
            if (!$l_path)
            {
                // Some OSes do this.
                $l_path = getenv('Path');
            } // if
        } // if

        $l_path_elements = explode(PATH_SEPARATOR, $l_path);
    } // if

    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
    {
        $l_exe_suffixes = getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : [
            '.exe',
            '.bat',
            '.cmd',
            '.com'
        ];

        // Allow passing a command.exe param.
        if (strpos($p_program, '.') !== false)
        {
            array_unshift($l_exe_suffixes, '');
        } // if

        // is_executable() is not available on windows for PHP4
        $l_pear_is_executable = (function_exists('is_executable')) ? 'is_executable' : 'is_file';
    }
    else
    {
        $l_exe_suffixes       = [''];
        $l_pear_is_executable = 'is_executable';
    } // if

    foreach ($l_exe_suffixes as $l_suff)
    {
        foreach ($l_path_elements as $l_dir)
        {
            $l_file = $l_dir . DS . $p_program . $l_suff;

            if (@$l_pear_is_executable($l_file))
            {
                return $l_file;
            } // if
        } // foreach
    } // foreach

    return $p_fallback;
} // function