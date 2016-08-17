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
 * Handler for import logs
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stuecken <dstuecken@i-doitorg>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 * @deprecated  Use isys_factory_log instead.
 */
class isys_import_log
{
    /**
     * Autosave.
     *
     * @var  boolean
     */
    protected static $m_autosave = false;
    /**
     * Alarmlevel.
     *
     * @var  integer
     */
    private static $m_alarmlevel = C__LOGBOOK__ALERT_LEVEL__0;
    /**
     * Log.
     *
     * @var  array
     */
    private static $m_log = [];

    /**
     * Returns raw log.
     *
     * @return  array
     */
    public static function get_raw()
    {
        return self::$m_log;
    } // function

    /**
     * Returns import log new line separated.
     *
     * @return  string
     */
    public static function get()
    {
        return implode(CRLF, self::get_raw());
    } // function

    /**
     * Adds new message to log.
     *
     * @param  string $p_message
     */
    public static function add($p_message)
    {
        self::$m_log[] = date('Y-m-d H:i:s - ') . $p_message;
    } // function

    /**
     * Change Alarmlevel.
     *
     * @param  integer $p_val
     */
    public static function change_alarmlevel($p_val)
    {
        self::$m_alarmlevel = $p_val;
    } // function

    /**
     * Gets alarmlevel.
     *
     * @return  integer
     */
    public static function get_alarmlevel()
    {
        return self::$m_alarmlevel;
    } // function

    /**
     * Saves log to file.
     *
     * @global  array $g_absdir
     */
    public function save()
    {
        global $g_absdir;

        if (!defined('CRLF'))
        {
            define('CRLF', "\n");
        } // if

        file_put_contents($g_absdir . DS . 'temp' . DS . 'import_log_' . date('ymd_his') . '.txt', self::get());
    } // function
} // class