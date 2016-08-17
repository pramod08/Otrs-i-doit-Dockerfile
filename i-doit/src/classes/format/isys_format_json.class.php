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
 * JSON Data Interface
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   Copyright 2010 - synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-7
 */
class isys_format_json
{
    /**
     * Wrapper method for json_decode, takes care of magic quotes and strip slashes.
     *
     * @param   string  $p_str
     * @param   boolean $p_as_assoc
     *
     * @return  mixed  If second parameter is set to FALSE the method will return a stdClass.
     *
     * @throws  Exception
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function decode($p_str, $p_as_assoc = true)
    {
        try
        {
            if (is_scalar($p_str) && $p_str)
            {
                $l_result = json_decode($p_str, $p_as_assoc);

                if (($l_err = self::last_error()))
                {
                    throw new \idoit\Exception\JsonException($l_err);
                }
                else
                {
                    return $l_result;
                } // if
            }

            return $p_str;
        }
        catch (ErrorException $e)
        {
            return null;
        }

    } // function

    /**
     * Wrapper method for json_encode.
     *
     * @param   mixed $p_val
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function encode($p_val)
    {
        return json_encode($p_val);
    } // function

    /**
     * Method to assure the given string really IS a JSON string.
     *
     * @param   string $p_val
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_json($p_val)
    {
        try
        {
            if (is_scalar($p_val) && json_decode($p_val, false, 1024) !== null)
            {
                return true;
            } // if

            return false;
        }
        catch (Exception $e)
        {
            return false;
        } // try
    } // function

    /**
     * Method to assure the given string really IS a JSON array.
     *
     * @param   string $p_val
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_json_array($p_val)
    {
        if (self::is_json($p_val) && is_array(self::decode($p_val)))
        {
            return true;
        } // if

        return false;
    } // function

    /**
     * Returns the last error (if any) occurred by last JSON parsing.
     *
     * @return  mixed  String with error message when an error occured, boolean false if eveything is okay.
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function last_error()
    {
        if (function_exists("json_last_error"))
        {
            switch (json_last_error())
            {
                case JSON_ERROR_DEPTH:
                    return 'Maximum stack depth exceeded';
                    break;

                case JSON_ERROR_CTRL_CHAR:
                    return 'Unexpected control character found';
                    break;

                case JSON_ERROR_SYNTAX:
                    return 'Syntax error, malformed JSON';
                    break;

                case JSON_ERROR_NONE:
                    return false;
                    break;
            } // switch
        } // if

        return false;
    } // function
} // class