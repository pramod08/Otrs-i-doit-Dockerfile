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
 * i-doit - Updates
 *
 * @package    i-doit
 * @subpackage Update
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/* Log-Type */
define("C__MESSAGE", 1 >> 1);
define("C__ERROR", 1 >> 2);
define("C__DEBUG", 1 >> 3);

/* Priority */
define("C__HIGH", 1);
define("C__MEDIUM", 2);
define("C__LOW", 3);

/* Results */
define("C__OK", "OK");
define("C__DONE", "DONE");
define("C__ERR", "ERROR");

class isys_update_log
{
    /**
     * Singleton instance.
     *
     * @var  isys_update_log
     */
    private static $m_instance;

    /**
     * Color-Map - GUI Colors mapped to priority
     *
     * @var  array
     */
    private $m_colormap = [
        C__ERR  => "#CC1111",
        C__DONE => "#11CC11",
        C__OK   => "#11CC11"
    ];

    /**
     * Debug Messages.
     *
     * @var  array
     */
    private $m_debug = [];

    /**
     * Errorcount
     *
     * @var  integer
     */
    private $m_errors = 0;

    /**
     * Log Messages. Format:
     *   array(
     *     "type"     => C__MESSAGE",
     *     "message"  => "string",
     *     "priority" => C__HIGH,
     *     "result"   => C__OK,
     *     "class"    => "bold"
     *   );
     *
     * @var array
     */
    private $m_log = [];

    /**
     * Singleton pattern.
     *
     * @return  isys_update_log
     */
    public static function get_instance()
    {
        if (!is_object(self::$m_instance))
        {
            self::$m_instance = new isys_update_log();
        } // if

        return self::$m_instance;
    } // function

    /**
     * Returns the errorcount.
     *
     * @return  integer
     */
    public function get_error_count()
    {
        return $this->m_errors;
    } // function

    /**
     * Reset the errorcount.
     */
    public function reset_error_count()
    {
        $this->m_errors = 0;
    } // function

    /**
     * Adds a new log message
     *
     * @param   string  $p_message
     * @param   integer $p_type
     * @param   string  $p_class
     * @param   integer $p_priority
     * @param   mixed   $p_result
     *
     * @return  integer
     */
    public function add($p_message, $p_type = C__MESSAGE, $p_class = null, $p_priority = C__MEDIUM, $p_result = null)
    {
        if ($p_result == C__ERR)
        {
            $this->m_errors++;
        } // if

        // Prepare log array.
        $this->m_log[] = [
            "type"     => $p_type,
            "message"  => str_replace(
                [
                    "\n",
                    "\t"
                ],
                "",
                $p_message
            ),
            "result"   => $p_result,
            "priority" => $p_priority,
            "color"    => $this->get_color($p_result),
            "class"    => $p_class
        ];

        // Adds a debug message.
        $this->debug($p_message);

        return (count($this->m_log)) - 1;
    } // function

    /**
     * Adds a debug message.
     *
     * @param  string $p_message
     */
    public function debug($p_message)
    {
        $this->m_debug[] = "[" . date("Y-m-d H:i:s") . "]: " . $p_message;
    } // function

    /**
     * Return debuglog
     *
     * @return  array
     */
    public function get_debug()
    {
        return $this->m_debug;
    } // function

    /**
     * Write debug information to i-doit-dir/time-idoit_update.log
     *
     * @return  integer
     */
    public function write_debug($p_filename = null)
    {
        global $g_absdir;

        if (!$p_filename)
        {
            $p_filename = date("Y-m-d_H-i-s") . '_idoit_update.log';
        }

        $l_str_debug = implode("\r\n", $this->get_debug());

        return file_put_contents($g_absdir . DS . "log" . DS . $p_filename, strip_tags($l_str_debug));
    } // function

    /**
     * Change result of a log entry.
     *
     * @param   integer $p_id
     * @param   string  $p_result
     * @param   integer $p_priority
     *
     * @return  boolean
     */
    public function result($p_id, $p_result, $p_priority = C__MEDIUM)
    {
        if ($p_result == C__ERR)
        {
            $this->m_errors++;
        } // if

        // Sometimes $p_id is an instance of SimpleXMLElement.
        $p_id = (string) $p_id;

        if (array_key_exists($p_id, $this->m_log))
        {
            $this->m_log[$p_id]["priority"] = $p_priority;
            $this->m_log[$p_id]["color"]    = $this->get_color($p_result);
            $this->m_log[$p_id]["result"]   = $p_result;

            return true;
        } // if

        return false;
    } // function

    /**
     * Get log message(s).
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get($p_id = null)
    {
        if ($p_id !== null)
        {
            return $this->m_log[$p_id];
        } // if

        return $this->m_log;
    } // function

    /**
     * Clear the log.
     */
    public function clear()
    {
        $this->m_log = [];
    } // function

    /**
     * Get color for priority.
     *
     * @param   integer $p_priority
     *
     * @return  string
     */
    public function get_color($p_result)
    {
        return $this->m_colormap[$p_result];
    } // function
} // class