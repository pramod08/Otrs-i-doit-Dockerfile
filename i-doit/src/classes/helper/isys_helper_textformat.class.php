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
 * Helper methods for text formatting.
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 */
class isys_helper_textformat
{
    /**
     * This method will link all URLs (like "http://example.com" or "www.example.com").
     *
     * @param   string $p_text
     * @param   string $p_quotation
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function link_urls_in_string($p_text, $p_quotation = '"')
    {
        $p_text = preg_replace('~\b(?<!href="|">)(?<!src="|">)(?:ht|f)tps?://[^<\s]+(?:/|\b)~i', '<a href=' . $p_quotation . '$0' . $p_quotation . '>$0</a>', $p_text);

        return preg_replace('~\b(?<!://|">)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}[^<\s]*\b~i', '<a href=' . $p_quotation . 'http://$0' . $p_quotation . '>$0</a>', $p_text);
    } // function

    /**
     * This method will link all email-addresses (like "lfischer@i-doit.com").
     *
     * @param   string $p_text
     * @param   string $p_quotation
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function link_mailtos_in_string($p_text, $p_quotation = '"')
    {
        return preg_replace('~\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}\b~i', '<a href=' . $p_quotation . 'mailto:$0' . $p_quotation . '>$0</a>', $p_text);
    } // function

    /**
     * Method for stripping HTML attributes out of the given string.
     *
     * @param   string $p_string String to be filtered.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function strip_html_attributes($p_string)
    {
        return preg_replace('~<([a-z][a-z0-9]*)[^>]*?(\/?)>~i', '<$1$2>', $p_string);
    } // function

    /**
     * Strips script-tags from a (HTML) string.
     *
     * @param   string  $p_string
     * @param   boolean $p_allow_html
     *
     * @return  string
     */
    public static function strip_scripts_tags($p_string, $p_allow_html = false)
    {
        if (!$p_allow_html)
        {
            return strip_tags($p_string);
        }
        else
        {
            return preg_replace("~<script[^>]*>([\\S\\s]*?)</script>~", "\\1", $p_string);
        } // if
    } // function

    /**
     * Strips script-tags from a (HTML) string.
     *
     * @param   string $p_string
     *
     * @return  string
     */
    public static function remove_scripts($p_string)
    {
        return preg_replace("~<script[^>]*>(.*?)</script>~", "", $p_string);
    } // function

    /**
     * Method for cleaning a string from all "non-word-characters": All special characters.
     *
     * @param   string $p_string
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function clean_string($p_string)
    {
        return preg_replace('~\W~i', '', $p_string);
    } // function

    /**
     * Method for retrieving a string like "Good morning", depending on the time of the day.
     *
     * @param   integer $p_hour
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_daytime($p_hour = null)
    {
        if ($p_hour === null)
        {
            $p_hour = date('H');
        } // if

        switch ($p_hour)
        {
            case ($p_hour < 6 || $p_hour >= 22):
                return _L('LC_UNIVERSAL__DATE__GOOD_NIGHT');

            case ($p_hour < 12):
                return _L('LC_UNIVERSAL__DATE__GOOD_MORNING');

            case ($p_hour < 18):
                return _L('LC_UNIVERSAL__DATE__GOOD_DAY');

            case ($p_hour < 22):
                return _L('LC_UNIVERSAL__DATE__GOOD_EVENING');
        } // switch

        return _L('LC_UNIVERSAL__HELLO');
    } // function

    /**
     * This method returns a string like "A, B, C and D".
     *
     * @param   array $p_parts
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function this_this_and_that(array $p_parts)
    {
        if (count($p_parts) > 1)
        {
            return implode(', ', array_slice($p_parts, 0, -1)) . ' ' . _L('LC__UNIVERSAL__AND') . ' ' . end($p_parts);
        }
        else
        {
            return current($p_parts);
        } // if
    } // function
} // class