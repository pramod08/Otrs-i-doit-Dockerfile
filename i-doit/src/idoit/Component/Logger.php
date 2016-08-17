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
namespace idoit\Component;

/**
 * i-doit Logger - extends the brilliant Monolog class with a few own methods (mostly used for the GUI).
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Logger extends \Monolog\Logger
{
    /**
     * Method for retrieving a fitting icon for every log level.
     *
     * @static
     *
     * @param   integer $level
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelIcons($level = null)
    {
        global $g_dirs;

        $icons = [
            self::DEBUG     => $g_dirs["images"] . 'icons/silk/bug.png',
            self::INFO      => $g_dirs["images"] . 'icons/silk/information.png',
            self::NOTICE    => $g_dirs["images"] . 'icons/silk/lightbulb.png',
            self::WARNING   => $g_dirs["images"] . 'icons/silk/error.png',
            self::ERROR     => $g_dirs["images"] . 'icons/alert-icon.png',
            self::CRITICAL  => $g_dirs["images"] . 'icons/alert-icon.png',
            self::ALERT     => $g_dirs["images"] . 'icons/silk/delete.png',
            self::EMERGENCY => $g_dirs["images"] . 'icons/silk/cross.png'
        ];

        if ($level !== null)
        {
            return $icons[$level];
        } // if

        return $icons;
    } // function

    /**
     * Method for retrieving a fitting text-color (via CSS class) for every log level.
     *
     * @static
     *
     * @param   integer $level
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelColors($level = null)
    {
        $colors = [
            self::DEBUG     => 'green',
            self::INFO      => 'blue',
            self::NOTICE    => '',
            self::WARNING   => 'yellow',
            self::ERROR     => 'red',
            self::CRITICAL  => 'red',
            self::ALERT     => 'red',
            self::EMERGENCY => 'red'
        ];

        if ($level !== null)
        {
            return $colors[$level];
        } // if

        return $colors;
    } // function

    /**
     * Gets log level as string.
     *
     * @static
     *
     * @param   integer $level
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelNames($level = null)
    {
        $names = [
            self::DEBUG     => _L('LC_UNIVERSAL__LOG_LEVEL__DEBUG'),
            self::INFO      => _L('LC_UNIVERSAL__LOG_LEVEL__INFO'),
            self::NOTICE    => _L('LC_UNIVERSAL__LOG_LEVEL__NOTICE'),
            self::WARNING   => _L('LC_UNIVERSAL__LOG_LEVEL__WARNING'),
            self::ERROR     => _L('LC_UNIVERSAL__LOG_LEVEL__ERROR'),
            self::CRITICAL  => _L('LC_UNIVERSAL__LOG_LEVEL__CRITICAL'),
            self::ALERT     => _L('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR'),
            self::EMERGENCY => _L('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR'),
        ];

        if ($level !== null)
        {
            return $names[$level];
        } // if

        return $names;
    } // function
} // class