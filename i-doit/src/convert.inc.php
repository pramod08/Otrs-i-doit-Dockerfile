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

define('C__CONVERT_DIRECTION__FORMWARD', 1);
define('C__CONVERT_DIRECTION__BACKWARD', 2);

/**
 * i-doit
 *
 * Convert helper.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_convert
{
    /**
     * Unit for inches.
     *
     * @var  float
     */
    const INCH = 25.4;

    /**
     * Unit for foot.
     *
     * @var  float
     */
    const FOOT = 304.8;

    /**
     * Unit for bytes.
     *
     * @var  integer
     */
    const BYTE = 1024;

    /**
     * Unit for hertz.
     *
     * @var  integer
     */
    const HERTZ = 1000;

    /**
     * The amount of seconds in one "common" year, as defined: http://en.wikipedia.org/wiki/Year#Symbol_a
     *
     * @var  integer
     */
    const YEAR = 31556926;

    /**
     * The amount of seconds in one "common" month, rounded result of 31556926 / 12.
     *
     * @var  integer
     */
    const MONTH = 2629744;

    /**
     * The amount of seconds in one week.
     *
     * @var  integer
     */
    const WEEK = 604800;

    /**
     * The amount of seconds in one day.
     *
     * @var  integer
     */
    const DAY = 86400;

    /**
     * The amount of seconds in one hour.
     *
     * @var  integer
     */
    const HOUR = 3600;

    /**
     * The amount of seconds in one minute.
     *
     * @var  integer
     */
    const MINUTE = 60;

    /**
     * Converts seconds to a $p_unit conform period.
     *
     * @param   integer $p_seconds
     * @param   mixed   $p_unit
     *
     * @return  integer
     * @todo    Merge with period_to_seconds - Maybe even replace with isys_convert::time();
     */
    public static function seconds_to_period($p_seconds, $p_unit)
    {
        if (is_null($p_seconds) || !is_numeric($p_seconds)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_guarantee_period_unit__const'];
        } // if

        switch ($p_unit)
        {
            case "C__GUARANTEE_PERIOD_UNIT_DAYS":
                return round($p_seconds / self::DAY);

            case "C__GUARANTEE_PERIOD_UNIT_WEEKS":
                return round($p_seconds / self::WEEK);

            case "C__GUARANTEE_PERIOD_UNIT_MONTH":
                return round($p_seconds / self::MONTH);

            case "C__GUARANTEE_PERIOD_UNIT_YEARS":
                return round($p_seconds / self::YEAR);
        } // switch

        return $p_seconds;
    } // function

    /**
     * Converts a period beginning at $p_from_date to seconds.
     *
     * @param   integer $p_period
     * @param   integer $p_unit
     *
     * @return  integer
     * @todo    Merge with seconds_to_period - Maybe even replace with isys_convert::time();
     */
    public static function period_to_seconds($p_period, $p_unit)
    {
        if (is_null($p_period) || !is_numeric($p_period)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_guarantee_period_unit__const'];
        } // if

        switch ($p_unit)
        {
            case "C__GUARANTEE_PERIOD_UNIT_DAYS":
                return $p_period * self::DAY;

            case "C__GUARANTEE_PERIOD_UNIT_WEEKS":
                return $p_period * self::WEEK;

            case "C__GUARANTEE_PERIOD_UNIT_MONTH":
                return $p_period * self::MONTH;

            case "C__GUARANTEE_PERIOD_UNIT_YEARS":
                return $p_period * self::YEAR;
        } // switch

        return $p_period;
    } // function

    /**
     * Converts KHz, MHz, GHz, THz.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function frequency($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_frequency_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_frequency_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__FREQUENCY_UNIT__KHZ":
                        return $p_value * self::HERTZ;
                    case "C__FREQUENCY_UNIT__MHZ":
                        return $p_value * self::HERTZ * self::HERTZ;
                    case "C__FREQUENCY_UNIT__GHZ":
                        return $p_value * self::HERTZ * self::HERTZ * self::HERTZ;
                    case "C__FREQUENCY_UNIT__THZ":
                        return $p_value * self::HERTZ * self::HERTZ * self::HERTZ * self::HERTZ;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__FREQUENCY_UNIT__KHZ":
                        return $p_value / self::HERTZ;
                    case "C__FREQUENCY_UNIT__MHZ":
                        return $p_value / (self::HERTZ * self::HERTZ);
                    case "C__FREQUENCY_UNIT__GHZ":
                        return $p_value / (self::HERTZ * self::HERTZ * self::HERTZ);
                    case "C__FREQUENCY_UNIT__THZ":
                        return $p_value / (self::HERTZ * self::HERTZ * self::HERTZ * self::HERTZ);
                } // switch
        } // switch

        return $p_value;
    } // function

    /**
     * Converts B, KB, MB, GB, TB.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function memory($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if ($p_direction === C__CONVERT_DIRECTION__FORMWARD) $p_value = isys_helper::filter_number($p_value);

        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_memory_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__MEMORY_UNIT__KB":
                        return $p_value * self::BYTE;
                    case "C__MEMORY_UNIT__MB":
                        return $p_value * self::BYTE * self::BYTE;
                    case "C__MEMORY_UNIT__GB":
                        return $p_value * self::BYTE * self::BYTE * self::BYTE;
                    case "C__MEMORY_UNIT__TB":
                        return $p_value * self::BYTE * self::BYTE * self::BYTE * self::BYTE;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__MEMORY_UNIT__KB":
                        $p_value = $p_value / self::BYTE;
                        break;
                    case "C__MEMORY_UNIT__MB":
                        $p_value = $p_value / (self::BYTE * self::BYTE);
                        break;
                    case "C__MEMORY_UNIT__GB":
                        $p_value = $p_value / (self::BYTE * self::BYTE * self::BYTE);
                        break;
                    case "C__MEMORY_UNIT__TB":
                        $p_value = $p_value / (self::BYTE * self::BYTE * self::BYTE * self::BYTE);
                        break;
                } // switch

                try
                {
                    return isys_locale::get_instance()
                        ->fmt_numeric($p_value);
                }
                catch (isys_exception_locale $e)
                {
                    return number_format($p_value, 2, '.', '');
                }
        } // switch

        return $p_value;
    } // function

    /**
     * Converts mm, cm and inch.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function measure($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_depth_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_depth_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case 'C__DEPTH_UNIT__CM':
                        return $p_value * 10;
                    case 'C__DEPTH_UNIT__INCH':
                        return $p_value * self::INCH;
                    case 'C__DEPTH_UNIT__FOOT':
                        return $p_value * self::FOOT;
                    case 'C__DEPTH_UNIT__METER':
                        return $p_value * 1000;
                    case 'C__DEPTH_UNIT__KILOMETER':
                        return $p_value * 1000000;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case 'C__DEPTH_UNIT__CM':
                        return $p_value / 10;
                    case 'C__DEPTH_UNIT__INCH':
                        return $p_value / self::INCH;
                    case 'C__DEPTH_UNIT__FOOT':
                        return $p_value / self::FOOT;
                    case 'C__DEPTH_UNIT__METER':
                        return $p_value / 1000;
                    case 'C__DEPTH_UNIT__KILOMETER':
                        return $p_value / 1000000;
                } // switch
        } // switch

        return $p_value;
    } // function

    /**
     * Converts Bit/s, KBit/s, MBit/s and GBit/s.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function speed($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_port_speed', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_port_speed__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__PORT_SPEED__KBIT_S":
                        return $p_value * 1000;
                    case "C__PORT_SPEED__MBIT_S":
                        return $p_value * 1000000;
                    case "C__PORT_SPEED__GBIT_S":
                        return $p_value * 1000000000;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__PORT_SPEED__KBIT_S":
                        return $p_value / 1000;
                    case "C__PORT_SPEED__MBIT_S":
                        return $p_value / 1000000;
                    case "C__PORT_SPEED__GBIT_S":
                        return $p_value / 1000000000;
                } // switch
        } // switch

        return $p_value;
    } // function

    /**
     * Converts Bit/s, KBit/s, MBit/s and GBit/s.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant as string.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Merge this to the existing speed method (+ migration).
     */
    public static function speed_wan($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_wan_capacity_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_wan_capacity_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__WAN_CAPACITY_UNIT__BITS":
                        return $p_value;
                    case "C__WAN_CAPACITY_UNIT__KBITS":
                        return $p_value * 1000;
                    case "C__WAN_CAPACITY_UNIT__MBITS":
                        return $p_value * 1000000;
                    case "C__WAN_CAPACITY_UNIT__GBITS":
                        return $p_value * 1000000000;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__WAN_CAPACITY_UNIT__BITS":
                        return $p_value;
                    case "C__WAN_CAPACITY_UNIT__KBITS":
                        return $p_value / 1000;
                    case "C__WAN_CAPACITY_UNIT__MBITS":
                        return $p_value / 1000000;
                    case "C__WAN_CAPACITY_UNIT__GBITS":
                        return $p_value / 1000000000;
                } // switch
        } // switch

        return $p_value;
    } // function

    /**
     * Converts seconds, minutes, hours, days, months and years.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function time($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_unit_of_time', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_unit_of_time__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__CMDB__UNIT_OF_TIME__SECOND":
                        return $p_value;
                    case "C__CMDB__UNIT_OF_TIME__MINUTE":
                        return $p_value * self::MINUTE;
                    case "C__CMDB__UNIT_OF_TIME__HOUR":
                        return $p_value * self::HOUR;
                    case "C__CMDB__UNIT_OF_TIME__DAY":
                        return $p_value * self::DAY;
                    case "C__CMDB__UNIT_OF_TIME__MONTH":
                        return $p_value * self::MONTH;
                    case "C__CMDB__UNIT_OF_TIME__YEAR":
                        return $p_value * self::YEAR;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__CMDB__UNIT_OF_TIME__SECOND":
                        return $p_value;
                    case "C__CMDB__UNIT_OF_TIME__MINUTE":
                        return $p_value / self::MINUTE;
                    case "C__CMDB__UNIT_OF_TIME__HOUR":
                        return $p_value / self::HOUR;
                    case "C__CMDB__UNIT_OF_TIME__DAY":
                        return $p_value / self::DAY;
                    case "C__CMDB__UNIT_OF_TIME__MONTH":
                        return $p_value / self::MONTH;
                    case "C__CMDB__UNIT_OF_TIME__YEAR":
                        return $p_value / self::YEAR;
                } // switch
                break;
        } // switch

        return $p_value;
    } // function

    /**
     * Converts a ini-value to bytes (128M or 1G, ...).
     *
     * @param   string $p_value
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function to_bytes($p_value)
    {
        if (is_null($p_value) || !is_numeric(substr($p_value, 0, -1)))
        {
            return null;
        } // if

        $l_return = trim($p_value);
        $l_unit   = strtolower($p_value[strlen($p_value) - 1]);

        switch ($l_unit)
        {
            case 'g':
                $l_return *= self::BYTE;
            case 'm':
                $l_return *= self::BYTE;
            case 'k':
                $l_return *= self::BYTE;
        } // switch

        return $l_return;
    } // function

    /**
     * Converts ml and liter.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function volume($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_volume_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_volume_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    default:
                    case "C__VOLUME_UNIT__ML":
                        return $p_value;
                    case "C__VOLUME_UNIT__L":
                        return $p_value * 100;
                } // switch

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    default:
                    case "C__VOLUME_UNIT__ML":
                        return $p_value;
                    case "C__VOLUME_UNIT__L":
                        return $p_value / 100;
                } // switch
        } // switch

        return $p_value;
    } // function

    /**
     * Converts Watt and BTU.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function watt($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_ac_refrigerating_capacity_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_ac_refrigerating_capacity_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__REF_CAPACITY_UNIT__KWATT":
                        return $p_value * 1000;
                    case "C__REF_CAPACITY_UNIT__MWATT":
                        return $p_value * 1000000;
                    case "C__REF_CAPACITY_UNIT__GWATT":
                        return $p_value * 1000000000;
                    case "C__REF_CAPACITY_UNIT__BTU":
                        return $p_value * 3.414;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__REF_CAPACITY_UNIT__KWATT":
                        return $p_value / 1000;
                    case "C__REF_CAPACITY_UNIT__MWATT":
                        return $p_value / 1000000;
                    case "C__REF_CAPACITY_UNIT__GWATT":
                        return $p_value / 1000000000;
                    case "C__REF_CAPACITY_UNIT__BTU":
                        return $p_value / 3.414;
                } // switch
                break;
        } // switch

        return $p_value;
    } // function

    /**
     * Converts g, kg and t.
     *
     * @param   mixed   $p_value May be an integer or an float.
     * @param   mixed   $p_unit  May be an integer or the unit-constant.
     * @param   integer $p_direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function weight($p_value, $p_unit, $p_direction = C__CONVERT_DIRECTION__FORMWARD)
    {
        if (is_null($p_value) || !is_numeric($p_value)) return null;

        if (is_numeric($p_unit))
        {
            global $g_comp_database;

            $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_weight_unit', $g_comp_database)
                ->get_data($p_unit);

            $p_unit = $l_unit['isys_weight_unit__const'];
        } // if

        switch ($p_direction)
        {
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($p_unit)
                {
                    case "C__WEIGHT_UNIT__KG":
                        return $p_value * 1000;
                    case "C__WEIGHT_UNIT__T":
                        return $p_value * 1000000;
                } // switch
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($p_unit)
                {
                    case "C__WEIGHT_UNIT__KG":
                        return $p_value / 1000;
                    case "C__WEIGHT_UNIT__T":
                        return $p_value / 1000000;
                } // switch
                break;
        } // switch

        return $p_value;
    } // function
} // class