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
 * Language manager used by the template library. It is responsible for managing the language caches.
 *
 * @package     i-doit
 * @subpackage  Components_Template
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_template_language_manager extends isys_component
{
    // Constants.
    const c_tbl_language = "isys_language";
    const c_cache_holder = "[LANG_ID]";
    const c_cache_file   = "lang_cache_[LANG_ID].inc.php";

    /**
     * Properties.
     *
     * @var  array
     */
    private $m_langcache;

    /**
     * Variable which holds the loaded language.
     *
     * @var  string
     */
    private $m_language = 'en';

    /**
     * Path of a customly created language file.
     *
     * @var  string
     */
    private $m_language_custom;

    /**
     * Location of the current language file.
     *
     * @var  string
     */
    private $m_language_file;

    /**
     *
     * @global  isys_component_database $g_comp_database_system
     * @return  array
     */
    public function fetch_available_languages()
    {
        global $g_comp_database_system;

        $l_return = [];
        $l_q      = $g_comp_database_system->query("SELECT * FROM isys_language WHERE isys_language__available = 1 ORDER BY isys_language__sort ASC;");

        while ($l_lang = $g_comp_database_system->fetch_row_assoc($l_q))
        {
            $l_return[] = $l_lang;
        } // while

        return $l_return;
    } // function

    /**
     * Returns the language string specified by the language identifier ($p_ident) and an optional array for substituting parameters ($p_subst_array).
     *
     * @param   string $p_ident
     * @param   array  $p_subst_array
     *
     * @return  string
     * @author  André Wösten
     */
    public function get($p_ident, $p_subst_array = null)
    {
        if (!empty($p_ident) && isset($this->m_langcache[$p_ident]))
        {
            if (is_array($p_subst_array))
            {
                $l_retcode = vsprintf($this->m_langcache[$p_ident], $p_subst_array);
            }
            else
            {
                if ($p_subst_array !== null)
                {
                    $l_args = func_get_args();
                    unset($l_args[0]);
                    $l_retcode = vsprintf($this->m_langcache[$p_ident], $l_args);
                }
                else
                {
                    $l_retcode = $this->m_langcache[$p_ident];
                }
            } // if
        }
        else
        {
            // If the Language constant is not defined, directly output it, so you know, what is missing.
            return $p_ident;
        } // if

        /*
         * Match again for language constants in evaluated language strings and replace them. We are not using the iterative variant here (e.g. enumerating
         * through the array with language constants) - instead we're matching directly for existing language constants and replace them (using substr_replace, which
         * is faster than preg_replace in our case). If a Language constant has more than one language constants in it, we have to recalculate the substition offsets.
         */
        if ($p_subst_array !== null && strpos($l_retcode, '[{') !== false)
        {
            if (preg_match_all("/\[\{(.*?)\}\]/i", $l_retcode, $l_regex, PREG_OFFSET_CAPTURE))
            {
                $l_d_offset = 0;

                for ($l_i = 0;$l_i < count($l_regex[0]);$l_i++)
                {
                    /*
                     * If using PREG_OFFSET_CAPTURE with preg_match_all, we get a 3-dimensional array:
                     *  1. Dimension: 0 = Original data, 1 = First regex-group, 2 = Second regex-group and so on
                     *  2. Dimension: Index of search result
                     *  3. Dimension: 0 = Data, 1 = Offset
                     */
                    $l_source = $l_regex[0][$l_i][0];
                    $l_const  = $l_regex[1][$l_i][0];
                    $l_offset = $l_regex[0][$l_i][1];

                    // This is necessary since we don't want a recursive loop.
                    if ($l_const != $p_ident)
                    {
                        // Fetch data for language constant.
                        $l_newdata = $this->get($l_const);

                        if (is_array($p_subst_array))
                        {
                            if (array_key_exists($l_const, $p_subst_array))
                            {
                                $l_newdata = $this->get($p_subst_array["$l_const"]);
                            } // if
                        } // if

                        // Recalculate substition offsets.
                        $l_offset -= $l_d_offset;
                        $l_d_offset += (strlen($l_source) - strlen($l_newdata));

                        // Do substitution.
                        $l_retcode = substr_replace($l_retcode, $l_newdata, $l_offset, strlen($l_source));
                    } // if
                } // for
            } // if
        } // if

        return $l_retcode;
    } // function

    /**
     * Retrieves the currently loaded language as string (for example "de" or "en").
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_loaded_language()
    {
        return $this->m_language;
    } // function

    /**
     * Magic method wrapper for get().
     *
     * @param   string $p_ident
     *
     * @return  string
     * @uses    isys_component_template_language_manager::get()
     */
    public function __get($p_ident)
    {
        return $this->get($p_ident);
    } // function

    /**
     * Returns a reference to the language cache.
     *
     * @return  array
     */
    public function &get_cache()
    {
        return $this->m_langcache;
    } // function

    /**
     * Loads and creates, if necessary, the language cache into self::$m_langcache.
     *
     * @param   string $p_language_short
     *
     * @throws  Exception
     * @return  boolean
     */
    public function load($p_language_short = null)
    {
        global $g_absdir, $g_langcache;

        if ($p_language_short !== null)
        {
            $this->m_language = str_replace(chr(0), '', $p_language_short);
        } // if

        $this->m_language_file = $g_absdir . "/src/lang/" . $this->m_language . ".inc.php";

        if (!file_exists($this->m_language_file) || strstr($this->m_language, "/"))
        {
            $this->m_language_file = $g_absdir . "/src/lang/en.inc.php";
        } // if

        if (file_exists($this->m_language_file))
        {
            // Aufgrund von RT#27300 verwenden wir kein include_once.
            if (include($this->m_language_file))
            {
                // We need to check "is_array($g_langcache)" because when loading it a second time, the variable will be NULL.
                if (is_array($g_langcache))
                {
                    $this->m_langcache = &$g_langcache;
                    unset($g_langcache);

                    // Load custom language constants
                    $this->load_custom($p_language_short);

                    return true;
                } // if
            }
            else
            {
                throw new Exception("Could not include " . $this->m_language_file);
            } // if
        }
        else
        {
            throw new Exception("Language file " . $this->m_language_file . " not found.!");
        } // if

        return true;
    } // function

    /**
     * Loads the custom language file, if available.
     *
     * @param  string $p_language_short
     */
    public function load_custom($p_language_short = null)
    {
        global $g_absdir;

        if ($p_language_short !== null)
        {
            $this->m_language = str_replace(chr(0), '', $p_language_short);
        } // if

        $this->m_language_custom = $g_absdir . "/src/lang/" . $this->m_language . "_custom.inc.php";

        if (file_exists($this->m_language_custom))
        {
            $g_langcache = $this->m_langcache;

            // Aufgrund von RT#27300 verwenden wir kein include_once.
            include $this->m_language_custom;

            // We need to check "is_array($g_langcache)" because when loading it a second time, the variable will be NULL.
            if (is_array($g_langcache))
            {
                $this->m_langcache = &$g_langcache;
                unset($g_langcache);
            } // if
        } // if
    } // function

    /**
     * Method for generically adding new translations.
     *
     * @param   array $p_language_array
     *
     * @return  isys_component_template_language_manager
     */
    public function append_lang(array $p_language_array = [])
    {
        if (is_array($p_language_array))
        {
            if (!is_array($this->m_langcache))
            {
                $this->m_langcache = $p_language_array;
            }
            else
            {
                $this->m_langcache = array_merge($this->m_langcache, $p_language_array);
            } // if
        } // if

        return $this;
    } // function

    /**
     * Method for generically adding new translations.
     *
     * @param   string $p_language_file
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @return  isys_component_template_language_manager
     */
    public function append_lang_file($p_language_file)
    {
        if (file_exists($p_language_file))
        {
            // Aufgrund von RT#27300 verwenden wir kein include_once.
            $l_lang = include $p_language_file;

            if (is_array($l_lang))
            {
                return $this->append_lang($l_lang);
            } // if

            unset($l_lang);
        } // if

        return $this;
    } // function

    /**
     * Calls load with $p_language_id as language identifier.
     *
     * @param  string $p_language_short
     */
    public function __construct($p_language_short)
    {
        $this->m_langcache = [];

        $this->load($p_language_short);
    } // function
} // class