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
 * Helper methods
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_helper
{
    /**
     * Filters a text. Useful for filter functions.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_text($p_string)
    {
        if (is_string($p_string) && isys_strlen($p_string) <= 255)
        {
            return $p_string;
        } // if

        return false;
    } // function

    /**
     * Filters a textarea. Useful for filter functions.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_textarea($p_string)
    {
        if (is_string($p_string) && isys_strlen($p_string) <= 65534)
        {
            return $p_string;
        } // if

        return false;
    } // function

    /**
     * Filters a JSON array of IDs.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return string|bool Returns valid string, otherwise false.
     */
    public static function filter_json_array_of_ids($p_string)
    {
        try
        {
            $l_ids = isys_format_json::decode($p_string, true);

            foreach ($l_ids as $l_id)
            {
                if (!is_numeric($l_id))
                {
                    return false;
                } // if
            } // foreach
        }
        catch (Exception $e)
        {
            return false;
        } // try/catch

        return $p_string;
    } // function

    /**
     * Filters a comma separated list of IDs.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_list_of_ids($p_string)
    {
        $l_ids = array_filter(explode(',', $p_string));

        foreach ($l_ids as $l_id)
        {
            if (!is_numeric($l_id))
            {
                return false;
            } // if
        } // foreach

        return $p_string;
    } //function

    /**
     * Filters an array of integers. Note: filter_var() accepts arrays as first
     * argument but handles recursively every item, so this function accepts integers as first argument.
     *
     * @param   integer $p_value Integer that will be validated.
     *
     * @return  mixed  Returns valid integer, otherwise false.
     */
    public static function filter_array_of_ints($p_value)
    {
        if (!is_int($p_value))
        {
            return false;
        } // if

        return $p_value;
    } // function

    /**
     * Filters a date or date time.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_date($p_string)
    {
        if ($p_string == 'undefined-undefined-undefined')
        {
            $p_string = '1970-01-01';
        } // if

        $l_date = strtotime($p_string);

        if ($l_date === false)
        {
            return false;
        } // if

        return $p_string;
    } // function

    /**
     * Method for removing whitespaces from a string.
     *
     * @param   string $p_string
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function strip_whitespaces($p_string)
    {
        return preg_replace('~(\s)~', '', $p_string);
    } // function

    /**
     * Filters a combined dialog which contains the category data identifier and the category constant to resolv the referenced data set.
     *
     * @param   string $p_string Format: <id>_<constant>
     *
     * @return  mixed   Returns valid string, otherwise false.
     */
    public static function filter_combined_dialog($p_string)
    {
        // 'Empty' value:
        if ($p_string === '-1')
        {
            return $p_string;
        } // if

        $l_separator_pos = strpos($p_string, '_');

        if ($l_separator_pos === false)
        {
            return false;
        } // if

        $l_category_data_id  = substr($p_string, 0, $l_separator_pos);
        $l_category_constant = substr($p_string, ($l_separator_pos + 1));

        // Invalid category data identifier.
        if (!is_numeric($l_category_data_id) || $l_category_data_id <= 0)
        {
            return false;
        } // if

        // Invalid category constant.
        if (!defined($l_category_constant))
        {
            return false;
        } // if

        return $p_string;
    } //function

    /**
     * Filters a mac address.
     *
     * @param   string $p_string Hex or binary with ':', '.' or '-'
     *
     * @return string|bool Returns valid string, otherwise false.
     */
    public static function filter_mac_address($p_string)
    {
        $l_length = strlen(preg_replace('/[\.\:\-\s]+/', '', $p_string));

        if ($l_length == 12 && preg_match('/[a-fA-F\d\.\:\-\s]+/', $p_string) === 1)
        {
            // Hex mac adress.
            return $p_string;
        }
        else if ($l_length == 48 && preg_match('/^[01]+$/', $p_string) === 1)
        {
            // Binary mac address.
            return $p_string;
        } // if

        return false;
    } //function

    /**
     * This helper will parse a string like "1.000,95 Bla" to float "1000.95" (with up to four digits after the point).
     *
     * @param   string $p_string
     *
     * @return  float
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function filter_number($p_string)
    {
        // Check, if we got a positive or negative number.
        $l_sign = (substr(trim($p_string), 0, 1) === '-') ? '-' : '';

        // First we strip the currency ("GHZ", "Euro", "$", ...) including spaces.
        $p_string = self::strip_non_numeric($p_string);

        // If the number is null
        if (is_null($p_string) || $p_string === '')
        {
            return null;
        } // if

        // Check if someone wrote a string like "1.000.000".
        if (substr_count($p_string, '.') > 1)
        {
            $p_string = str_replace('.', '', $p_string);
        } // if

        // Check if someone wrote a string like "1,000,000".
        if (substr_count($p_string, ',') > 1)
        {
            $p_string = str_replace(',', '', $p_string);
        } // if

        // If we find a single point or a single comma, we use the last found one as decimal point.
        if (strpos($p_string, '.') !== false || strpos($p_string, ',') !== false)
        {
            if (strpos($p_string, '.') > strpos($p_string, ','))
            {
                $p_string = str_replace(',', '', $p_string);
            }
            elseif (strpos($p_string, '.') < strpos($p_string, ','))
            {
                $p_string = str_replace('.', '', $p_string);
                $p_string = str_replace(',', '.', $p_string);
            }
            elseif (strpos($p_string, '.') === false && is_int(strpos($p_string, ',')))
            {
                $p_string = str_replace(',', '.', $p_string);
            } // if
        } // if

        // Finally check if number is not numeric then return null
        if (!is_numeric($p_string)) return null;

        // Now we replace commas with dots: "1000,10" to "1000.10" and return the rounded value.
        return (float) round(str_replace(',', '.', $l_sign . $p_string), 4);
    } // function

    /**
     * Filters selection for the property selector smarty plugin.
     *
     * @param string $p_string JSON string
     *
     * @return string|bool Returns valid string, otherwise false.
     */
    public static function filter_property_selector($p_string)
    {
        try
        {
            $l_raw = isys_format_json::decode($p_string, true);

            foreach ($l_raw as $l_index => $l_sorted_entries)
            {
                if (!is_int($l_index))
                {
                    return false;
                }

                foreach ($l_sorted_entries as $l_category_type => $l_category_ids)
                {
                    switch ($l_category_type)
                    {
                        case 'g':
                        case 's':
                            break;
                        default:
                            return false;
                    } //switch

                    foreach ($l_category_ids as $l_category_const => $l_properties)
                    {
                        if (is_string($l_category_const) && !defined($l_category_const))
                        {
                            return false;
                        }
                        else if (is_numeric($l_category_const) && $l_category_const < 0)
                        {
                            return false;
                        } //if

                        foreach ($l_properties as $l_property)
                        {
                            if (!is_string($l_property) || empty($l_property))
                            {
                                return false;
                            }
                        }
                    } //foreach
                } // foreach
            } //foreach
        }
        catch (Exception $l_exception)
        {
            return false;
        } // try/catch

        return $p_string;
    } //function

    /**
     * Strips everything "not-number"-like.
     *
     * @param   string $p_data
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function strip_non_numeric($p_data)
    {
        return preg_replace('/([^,\.\d])*/i', '', $p_data);
    } // function

    /**
     * Removes all HTML tags.
     *
     * @param  string  $p_string
     *
     * @return string
     */
    public static function sanitize_text($p_string)
    {
        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1))
        {
            return strip_tags(str_replace(["\n", "\r", '&nbsp;', chr(194) . chr(160)], ['', '', ' ', ' '], $p_string));
        } // if

        return $p_string;
    } // function

    /**
     * Sanitizes float|double values
     *
     * @deprecated  Simply use "isys_helper::filter_number()".
     *
     * @param       mixed $p_value
     *
     * @return      float
     */
    public static function sanitize_number($p_value)
    {
        return self::filter_number($p_value);
    } // function

    /**
     * Public static method for retrieving an array which contains all numbers, which are included.
     * For example: 81 => array(64, 16, 1).
     *
     * @param   integer $p_number
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function split_bitwise($p_number)
    {
        $l_return = [];
        $p_number = (int) $p_number;

        for ($i = strlen(decbin($p_number));$i >= 0;$i--)
        {
            $l_current = pow(2, $i);

            if ($l_current & $p_number)
            {
                $l_return[] = $l_current;
            } // if
        } // for

        return $l_return;
    } // function

    /**
     * Returns an array of image mimetypes.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_image_mimetypes()
    {
        return [
            'bmp'  => 'image/bmp',
            'gif'  => 'image/gif',
            'ico'  => 'image/x-icon',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'svg'  => 'image/svg+xml',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
        ];
    } // function
} // class