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
 * Localization!
 *
 * This class has nothing to do with the language manager used in the
 * template library, but it is responsible for date and time formatting
 * based on specific settings. We do not use system- or database- locales,
 * we define all locales on our own!
 *
 * @internal
 * @package     i-doit
 * @subpackage  General
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @todo        AW-only!! Beim Durchschreiten der Hypergate wird anhand der gewählten Mandantensprache hier die Default-Sprache selektiert. Bei existierendem Eintrag wird das nicht durchgeführt.
 */

if (!defined('LC_LANG'))
{
    define('LC_LANG', 0);
} // if

class isys_locale
{
    /**
     * $_SESSION-Key for
     * storing user locales
     */
    const C__SESSION_CACHE_KEY = "user_setting";

    /**
     * Singleton instance.
     *
     * @var  isys_locale
     */
    private static $m_instance = null;
    /**
     * Locale configuration for all languages
     *
     * @var  array
     */
    private static $m_locales = [
        // Extend this in order to make a new language or localisation configuration.
        ISYS_LANGUAGE_ENGLISH => [
            // Language settings:
            LC_LANG     => [],
            // Time settings.
            LC_TIME     => [
                // Short date format
                "d_fmt_s" => "%Y-%m-%d",
                // Short date format but long year
                "d_fmt_m" => "%Y-%m-%d",
                // Long date format
                "d_fmt_l" => "%d. %M, %Y",
                // Short time format
                "t_fmt_s" => "%H:%i",
                // Long time format
                "t_fmt_l" => "%H:%i:%s",
                // Months.
                "mon"     => [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                ]
            ],
            // Monetary and currency settings.
            LC_MONETARY => [
                // EUR x,xxx,xxx,xxx
                "mon_thousands_sep" => ",",
                // EUR x,xxx.yy
                "mon_decimal_point" => ".",
                // Grouping for the value (EUR 1,000,000)
                "mon_grouping"      => 3,
                // EUR 1999,xx
                "int_frac_digits"   => 2,
                // EUR 1999,xx
                "frac_digits"       => 2,
                // 1 if int_curr_symbol stands in front of the value, 0 if behind
                "p_ics_precedes"    => 0,
            ],
            // Numeric settings.
            LC_NUMERIC  => [
                "decimal_point" => ".",
                "thousand_sep"  => ",",
                "grouping"      => 3,
                "frac_digits"   => 2
            ]
        ],
        ISYS_LANGUAGE_GERMAN  => [
            // Language settings:
            LC_LANG     => [],
            // Time settings.
            LC_TIME     => [
                // Short date format
                "d_fmt_s" => "%d.%m.%y",
                // Short date format but long year
                "d_fmt_m" => "%d.%m.%Y",
                // Long date format
                "d_fmt_l" => "%d. %M %Y",
                // Short time format
                "t_fmt_s" => "%H:%i",
                // Long time format
                "t_fmt_l" => "%H:%i:%s",
                // Months.
                "mon"     => [
                    "Januar",
                    "Februar",
                    "März",
                    "April",
                    "Mai",
                    "Juni",
                    "Juli",
                    "August",
                    "September",
                    "Oktober",
                    "November",
                    "Dezember"
                ]
            ],
            // Monetary and currency settings.
            LC_MONETARY => [
                // EUR x.xxx.xxx.xxx
                "mon_thousands_sep" => ".",
                // EUR xxxx,yy
                "mon_decimal_point" => ",",
                // Grouping for the value (EUR 1.000.000)
                "mon_grouping"      => 3,
                // EUR 1999,xx
                "int_frac_digits"   => 2,
                // EUR 1999,xx
                "frac_digits"       => 2,
                // 1 if int_curr_symbol stands in front of the value, 0 if behind
                "p_ics_precedes"    => 0,
            ],
            // Numeric settings.
            LC_NUMERIC  => [
                "decimal_point" => ",",
                "thousand_sep"  => ".",
                "grouping"      => 3,
                "frac_digits"   => 2
            ]
        ]
    ];
    /**
     * Simple cache for fetched or manipulated variables.
     *
     * @var  array  Associative array
     */
    protected $m_cache;
    /**
     * Database access object used for global mandator settings
     *
     * @var  isys_component_dao_setting
     */
    private $m_daoSetting;
    /**
     * Database access object used for internal persons and general queries.
     *
     * @var  isys_component_dao_user
     */
    private $m_daoUser;
    /**
     * Database component.
     *
     * @var  isys_component_database
     */
    private $m_db;
    /**
     * Current user settings (set after init()).
     *
     * @var  array
     */
    private $m_userSettings = [
        LC_LANG     => ISYS_LANGUAGE_ENGLISH,
        LC_TIME     => ISYS_LANGUAGE_ENGLISH,
        LC_MONETARY => ISYS_LANGUAGE_ENGLISH,
        LC_NUMERIC  => ISYS_LANGUAGE_ENGLISH
    ];

    /**
     * Returns the dummy singleton instance (used before the login!)
     *
     * @return  isys_locale
     * @static
     */
    public static function dummy()
    {
        if (!isset(self::$m_instance))
        {
            self::$m_instance = new isys_locale();
        }

        return self::$m_instance;
    } // function

    /**
     * Returns the singleton instance
     *
     * @param   isys_component_database $p_db
     * @param   integer                 $p_user_id
     *
     * @return  isys_locale
     * @static
     */
    public static function get(isys_component_database& $p_db, $p_user_id)
    {
        if (!is_object($p_db) || !is_numeric($p_user_id))
        {
            throw new Exception("Internal Error! Could not configure localization (locales.inc.php)!");
        } // if

        if (!isset(self::$m_instance) || (isset(self::$m_instance) && $p_db !== self::$m_instance->m_db))
        {
            $l_c              = __CLASS__;
            self::$m_instance = new $l_c($p_db);
            self::$m_instance->init($p_user_id);
        }
        else
        {
            self::$m_instance->init($p_user_id);
        } // if

        return self::$m_instance;
    } // function

    /**
     * Get instance
     *
     * @return isys_locale
     * @throws isys_exception_locale
     */
    public static function get_instance()
    {
        if (self::$m_instance)
        {
            return self::$m_instance;
        }
        else
        {
            if (isys_application::instance()->database)
            {
                return new isys_locale(
                    isys_application::instance()->database
                );
            }
        }

        throw new isys_exception_locale("Can not return an empty Instance of isys_locale. Please initialize one first by using isys_locale::init().");
    } // function

    /**
     * Resolves language by its constant.
     *
     * @global  isys_component_database $g_comp_database_system
     *
     * @param   mixed                   $p_constant Language constant
     *
     * @return  string  Returns language on success, otherwise null.
     */
    public function resolve_language_by_constant($p_constant)
    {
        if (is_string($p_constant))
        {
            if (is_numeric($p_constant))
            {
                $p_constant = intval($p_constant);
            }
            else
            {
                $p_constant = constant($p_constant);
            } // if
        } // if

        if (!isset($this->m_cache['languages']))
        {
            global $g_comp_database_system;

            $l_dao = new isys_component_dao($g_comp_database_system);

            $l_result = $l_dao->retrieve("SELECT isys_language__const, isys_language__short FROM isys_language WHERE isys_language__const != 'ISYS_LANGUAGE_ALL';");

            if ($l_result->num_rows() > 1)
            {
                while ($l_row = $l_result->get_row())
                {
                    $l_key                              = constant($l_row['isys_language__const']);
                    $l_value                            = $l_row['isys_language__short'];
                    $this->m_cache['languages'][$l_key] = $l_value;
                } // while
            } // if
        } // if

        if (isset($this->m_cache['languages'][$p_constant]))
        {
            return $this->m_cache['languages'][$p_constant];
        } // if

        return null;
    } // function

    /**
     * @param   $p_short_tag
     *
     * @return  mixed
     */
    public function resolve_language_constant_by_short_tag($p_short_tag)
    {
        if (!isset($this->m_cache['language_constants_by_short_tags']))
        {
            global $g_comp_database_system;

            $l_dao = new isys_component_dao($g_comp_database_system);

            $l_result = $l_dao->retrieve("SELECT isys_language__const, isys_language__short FROM isys_language WHERE isys_language__const != 'ISYS_LANGUAGE_ALL';");

            if ($l_result->num_rows() > 1)
            {
                while ($l_row = $l_result->get_row())
                {
                    $l_key                                                     = $l_row['isys_language__short'];
                    $l_value                                                   = constant($l_row['isys_language__const']);
                    $this->m_cache['language_constants_by_short_tags'][$l_key] = $l_value;
                } // while
            } // if
        } // if

        if (isset($this->m_cache['language_constants_by_short_tags'][$p_short_tag]))
        {
            return $this->m_cache['language_constants_by_short_tags'][$p_short_tag];
        } // if

        return '';
    } // function

    /**
     * Numerical format of data.
     *
     * @param   mixed $p_data
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function fmt_numeric($p_data)
    {
        if (!is_numeric($p_data))
        {
            return $p_data;
        } // if

        $l_decSettings = self::$m_locales[$this->m_userSettings[LC_NUMERIC]][LC_NUMERIC];

        return number_format($p_data, $l_decSettings["frac_digits"], $l_decSettings["decimal_point"], $l_decSettings["thousand_sep"]);
    } // function

    /**
     * Formats a decimal number to a monetary one, dependent on the monetary setting made in locales.inc.php
     *
     * @param   float   $p_data
     * @param   boolean $p_bSymbolPrecedes
     * @param   string  $p_own_symbol
     *
     * @return  string
     */
    public function fmt_monetary($p_data, $p_bSymbolPrecedes = null, $p_own_symbol = null)
    {
        if ($p_own_symbol === null && is_object($this->m_db))
        {
            $l_symbol_data = isys_factory_cmdb_dialog_dao::get_instance('isys_currency', $this->m_db)
                ->get_data((new isys_component_dao_setting(isys_application::instance()->database))->get(null, C__MANDATORY_SETTING__CURRENCY));

            $l_symbol = $l_symbol_data['isys_currency__title'];

            if (strpos($l_symbol, ';') !== false)
            {
                $l_symbol = end(explode(';', $l_symbol));
            } // if
        }
        else
        {
            $l_symbol = $p_own_symbol;
        } // if

        $l_moneySettings = self::$m_locales[$this->m_userSettings[LC_LANG]][LC_MONETARY];

        if ($p_bSymbolPrecedes || $l_moneySettings["p_ics_precedes"] == "1")
        {
            return $l_symbol . " " . $this->fmt_numeric($p_data);
        } // if

        return $this->fmt_numeric($p_data) . " " . $l_symbol;
    } // function

    /**
     * Method to retrieve the configured currency symbol.
     */
    public function get_currency()
    {
        $l_symbol_data = isys_factory_cmdb_dialog_dao::get_instance('isys_currency', $this->m_db)
            ->get_data($this->get_setting(LC_MONETARY));

        $l_symbol = $l_symbol_data['isys_currency__title'];

        if (strpos($l_symbol, ';') !== false)
        {
            $l_symbol = end(explode(';', $l_symbol));
        } // if

        return $l_symbol;
    } // function

    /**
     * @param   mixed   $p_data
     * @param   boolean $p_shortFormat
     *
     * @return  mixed
     */
    public function fmt_date($p_data, $p_shortFormat = true)
    {
        // First index is language, second index type of locale.
        $l_dateSettings = self::$m_locales[$this->m_userSettings[LC_TIME]][LC_TIME];
        if (!$l_dateSettings)
        {
            $l_dateSettings = self::$m_locales[ISYS_LANGUAGE_ENGLISH][LC_TIME];
        }

        $l_data = trim(strval($p_data));

        if (is_numeric($l_data))
        {
            $l_data = date("Y-m-d", $l_data);
        } // if

        if (strpos($l_data, "0000-00-00") !== false || strpos($l_data, "1970-01-01") !== false)
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        if (preg_match("/^((?:\d\d)?\d\d)-(\d\d)-(\d\d)(?:.*)$/", $l_data, $l_regexData))
        {
            list(, $l_year, $l_month, $l_day) = $l_regexData;

            $l_field = ($p_shortFormat) ? "d_fmt_s" : "d_fmt_l";

            return $this->_fmt(
                $l_dateSettings[$l_field],
                [
                    "d" => sprintf("%02d", $l_day),
                    "m" => sprintf("%02d", $l_month),
                    "M" => $l_dateSettings["mon"][$l_month - 1],
                    "y" => sprintf("%02d", $l_year),
                    "Y" => sprintf("%04d", $l_year)
                ]
            );
        } // if

        return null;
    } // function

    /**
     * @param   mixed   $p_data
     * @param   boolean $p_shortFormat
     *
     * @return  mixed
     */
    public function fmt_time($p_data, $p_shortFormat = true)
    {
        $l_timeSettings = self::$m_locales[$this->m_userSettings[LC_LANG]][LC_TIME];

        if (!$l_timeSettings)
        {
            $l_timeSettings = self::$m_locales[ISYS_LANGUAGE_ENGLISH][LC_TIME];
        } // if

        $l_data = trim(strval($p_data));

        if (is_numeric($l_data))
        {
            $l_data = date('H:i:s', $l_data);
        } // if

        if (strpos($l_data, "00:00:00") !== false)
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        if (preg_match("/(\d\d)\:(\d\d)(?:\:(\d\d))?$/", $l_data, $l_regexData))
        {
            list(, $l_hour, $l_minutes, $l_seconds) = $l_regexData;

            if (!isset($l_seconds))
            {
                $l_seconds = 0;
            } // if

            $l_field = ($p_shortFormat) ? "t_fmt_s" : "t_fmt_l";

            return $this->_fmt(
                $l_timeSettings[$l_field],
                [
                    "H" => sprintf("%02d", $l_hour),
                    "i" => sprintf("%02d", $l_minutes),
                    "s" => sprintf("%02d", $l_seconds)
                ]
            );
        } // if

        return null;
    }

    /**
     * Format date (and time if it's given).
     *
     * @param   string  $p_strData
     * @param   boolean $p_bShortDate
     * @param   boolean $p_bShortTime
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function fmt_datetime($p_strData, $p_bShortDate = true, $p_bShortTime = true)
    {
        $l_strDate = $this->fmt_date($p_strData, $p_bShortDate);

        // First strip the time if it's just "0".
        if (strpos($p_strData, "00:00:00") !== false)
        {
            // Directly return just the date without time.
            return $l_strDate;
        } // if

        // Get the formatted time from the string.
        $l_strTime = $this->fmt_time($p_strData, $p_bShortTime);

        if (strlen($l_strTime) > 4)
        {
            $l_strTime = " - " . $l_strTime;
        } // if

        // Return both.
        return $l_strDate . $l_strTime;
    }

    /**
     * Sets a user's locale setting
     *
     * @param integer $p_setting
     * @param integer $p_language
     *
     * @return boolean
     */
    public function set_setting($p_setting, $p_language)
    {
        if (array_key_exists($p_language, self::$m_locales) && array_key_exists($p_setting, self::$m_locales[$p_language]))
        {
            $this->m_userSettings[$p_setting] = $p_language;

            return true;
        }

        return false;
    }

    /**
     * Get a user's locale setting
     *
     * @param integer $p_setting
     *
     * @return integer (Language-ID)
     */
    public function get_setting($p_setting)
    {
        return $this->m_userSettings[$p_setting];
    }

    /**
     * Overloading method for getting a property
     *
     * Wraps to this->get
     *
     * @param string $p_setting
     *
     * @return integer
     */
    public function __get($p_setting)
    {
        return $this->get_setting(constant($p_setting));
    } // function

    /**
     * Overloading method for setting a property
     *
     * Wraps to this->set
     *
     * @param string  $p_setting
     * @param integer $p_language
     */
    public function __set($p_setting, $p_language)
    {
        $this->set_setting(constant($p_setting), $p_language);
    } // function

    /**
     * Saves the user's locale settings
     *
     * @param   integer $p_user_id
     *
     * @return  boolean
     */
    public function save_settings($p_user_id)
    {
        if (is_numeric($p_user_id))
        {
            $l_currency_id = 0;
            $l_row         = $this->get_settings_by_user_id($p_user_id)
                ->get_row();

            if (empty($this->m_userSettings[LC_MONETARY]))
            {
                if (defined('C__CMDB__CURRENCY__EURO'))
                {
                    $l_currency_id = constant('C__CMDB__CURRENCY__EURO');
                } // if
            }
            else
            {
                $l_currency_id = (int) $this->m_userSettings[LC_MONETARY];
            } // if

            $l_q = "UPDATE isys_user_locale
				SET isys_user_locale__language = '" . $this->m_userSettings[LC_LANG] . "',
				isys_user_locale__language_time = '" . $this->m_userSettings[LC_TIME] . "',
				isys_user_locale__isys_currency__id = " . $this->m_daoUser->convert_sql_id($l_currency_id) . ",
				isys_user_locale__language_numeric = '" . $this->m_userSettings[LC_NUMERIC] . "'
				WHERE isys_user_locale__isys_user_setting__id = '" . $l_row["isys_user_setting__id"] . "';";

            return ($this->m_daoUser->update($l_q) && $this->m_daoUser->apply_update());
        } // if

        return null;
    } // function

    /**
     * Initialize the per user's locale settings.
     *
     * @param  integer $p_user_id
     */
    public function init($p_user_id)
    {
        // Are the locales already cached?
        if (isset($_SESSION[self::C__SESSION_CACHE_KEY]) && !empty($_SESSION[self::C__SESSION_CACHE_KEY]) && is_array($_SESSION[self::C__SESSION_CACHE_KEY]) && count(
                $_SESSION[self::C__SESSION_CACHE_KEY]
            )
        )
        {
            $this->m_userSettings = $_SESSION[self::C__SESSION_CACHE_KEY];
        }
        else
        {
            $l_res = $this->get_settings_by_user_id($p_user_id);

            if ($l_res !== null)
            {
                $l_langrow = $l_res->get_row();

                if (!empty($l_langrow["isys_user_locale__language"]))
                {
                    $this->m_userSettings[LC_LANG] = $l_langrow["isys_user_locale__language"];
                } // if

                if ($l_langrow["isys_user_locale__language_time"] > 0)
                {
                    $this->m_userSettings[LC_TIME] = $l_langrow["isys_user_locale__language_time"];
                } // if

                try
                {
                    $this->m_userSettings[LC_MONETARY] = $this->m_daoSetting->get(null, null, "C__MANDATORY_SETTING__CURRENCY");
                }
                catch (isys_exception_database $e)
                {
                    $this->m_userSettings[LC_MONETARY] = C__CMDB__CURRENCY__EURO;
                } // try

                if ($l_langrow["isys_user_locale__language_numeric"] > 0)
                {
                    $this->m_userSettings[LC_NUMERIC] = $l_langrow["isys_user_locale__language_numeric"];
                } // if

                if ($l_langrow["isys_user_locale__default_tree_type"] > 0)
                {
                    $this->m_userSettings['tree_type'] = $l_langrow["isys_user_locale__default_tree_type"];
                } // if
            } // if
            $_SESSION[self::C__SESSION_CACHE_KEY] = $this->m_userSettings;
        }
    } // function

    /**
     * @param   integer $p_nSetting (i.e. LC_NUMERIC)
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_user_settings($p_nSetting)
    {
        return self::$m_locales[$this->m_userSettings[$p_nSetting]][$p_nSetting];
    }

    /**
     * Reset (and Rebuild) the user locale cache
     *
     * @param bool|integer $p_rebuild
     */
    public function reset_cache($p_rebuild = false)
    {
        /* Reset the Cache */
        $_SESSION[self::C__SESSION_CACHE_KEY] = null;

        /* Rebuild the cache */
        if ($p_rebuild)
        {
            $l_user_id = (is_numeric($p_rebuild)) ? $p_rebuild : isys_application::instance()->session->get_user_id();

            $this->init($l_user_id);
        }
    } // function

    /**
     * @param bool|true $p_short
     *
     * @return mixed
     */
    public function get_date_format($p_short = true)
    {

        $l_date_settings = $this->get_user_settings(LC_TIME);

        if ($p_short) $l_settings = $l_date_settings['d_fmt_m'];
        else $l_settings = $l_date_settings['d_fmt_l'];

        return str_replace('%', '', $l_settings);
    } // function

    /**
     * @return mixed
     */
    public function get_month_names()
    {
        $l_key = $this->resolve_language_constant_by_short_tag(isys_application::instance()->session->get_language());

        if (!$l_key)
        {
            $l_key = ISYS_LANGUAGE_ENGLISH;
        } // if

        return self::$m_locales[$l_key][LC_TIME]['mon'];
    }

    /**
     * _fmt method.
     *
     * @param   string $p_string
     * @param   array  $p_repArray
     *
     * @return  mixed
     */
    private function _fmt($p_string, $p_repArray)
    {
        foreach ($p_repArray as $l_key => $l_value)
        {
            $p_string = str_replace("%" . $l_key, $l_value, $p_string);
        } // foreach

        return $p_string;
    }

    /**
     * Returns the user settings by the user-ID
     *
     * @param   integer $p_user_id
     *
     * @return  isys_component_dao_result
     */
    private function get_settings_by_user_id($p_user_id)
    {
        global $g_comp_session;

        if ($p_user_id > 0)
        {
            $l_q = "SELECT * FROM isys_user_locale
				INNER JOIN isys_user_setting ON isys_user_locale__isys_user_setting__id = isys_user_setting__id
				LEFT JOIN isys_user_ui ON isys_user_ui__isys_user_setting__id = isys_user_setting__id
				WHERE isys_user_setting__isys_obj__id = " . (int) $p_user_id . ";";

            $l_res = $this->m_daoUser->retrieve($l_q);

            if ($l_res->num_rows() > 0)
            {
                return $l_res;
            }
            else
            {
                $l_language = $g_comp_session->get_language();

                if ($this->m_daoUser->get_user_setting_id($p_user_id))
                {
                    // Otherwise create record and try again.
                    $l_q = "REPLACE INTO isys_user_locale (
						isys_user_locale__isys_user_setting__id,
						isys_user_locale__language,
						isys_user_locale__language_time,
						isys_user_locale__language_numeric
						) VALUES (
						'" . $this->m_daoUser->get_user_setting_id() . "',
						'" . $l_language . "',
						'" . $l_language . "',
						'" . $l_language . "');";

                    $this->m_daoUser->begin_update();
                    if ($this->m_daoUser->update($l_q))
                    {
                        if ($this->m_daoUser->apply_update())
                        {
                            return $this->get_settings_by_user_id($p_user_id);
                        } // if
                    } // if
                } // if
            } // if
        } // if

        return null;
    } // function

    /**
     * Constructor
     *
     * @param  isys_component_database & $p_db
     */
    public function __construct(isys_component_database& $p_db = null)
    {
        if ($p_db)
        {
            $this->m_db         = $p_db;
            $this->m_daoUser    = isys_component_dao_user::instance($p_db);
            $this->m_daoSetting = new isys_component_dao_setting($p_db);
        }
    } // function
} // class
