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
 * Migration logger.
 *
 * @package     i-doit
 * @subpackage  Log
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_log_migration extends isys_log
{
    /**
     * Contains a self representation of this class's childs.
     *
     * @var  isys_log_migration
     */
    protected static $m_instance = null;

    /**
     * Containing the migration log messages.
     *
     * @var  array
     */
    private $m_migration_log = [];

    /**
     * Method for returning all migration logs.
     *
     * @return array
     */
    public function get_migration_log()
    {
        return $this->m_migration_log;
    } // function

    /**
     * @param   string $p_type
     * @param   string $p_message
     *
     * @return  isys_log_migration
     * @throws  isys_exception_general
     */
    public function add_migration_log($p_type, $p_message)
    {
        try
        {
            if (method_exists($this, $p_type))
            {
                $this->$p_type($p_message);
                $this->set_migration_log($p_message, $p_type);
            } // if
        }
        catch (Exception $e)
        {
            throw new isys_exception_general($e->getMessage(), '');
        } // try

        return $this;
    } // function

    /**
     * Restes the log
     */
    public function reset_log()
    {
        $this->flush_log();
        $this->m_migration_log = [];

        return $this;
    } // function

    /**
     * Destructor. Flushs log and writes messages to file.
     */
    public function __destruct()
    {
        $this->flush_log();
    } // function

    /**
     * Gets the existing instance of this class. If there is no instance already created, a new one will be instantiated.
     *
     * @static
     * @return  isys_log_migration
     */
    public static function get_instance($p_instanceName = 'migration')
    {
        if (isset(self::$m_instance))
        {
            return self::$m_instance;
        } // if

        return self::new_instance();
    } // function

    /**
     * Creates a new instance of this class and returns a representation of it. This function can only be called once.
     *
     * @static
     * @return  isys_log_migration
     */
    public static function new_instance($p_instance = 'migration')
    {
        assert('!isset(self::$m_instance)');
        $l_class          = __CLASS__;
        self::$m_instance = new $l_class;

        return self::$m_instance;
    } // function

    /**
     * Set a new migration log message.
     *
     * @param   string $p_message
     * @param   string $p_type
     *
     * @return  isys_log_migration
     */
    private function set_migration_log($p_message, $p_type)
    {
        $this->m_migration_log[] = '<span class="bold ' . (($p_type == 'error') ? 'red' : (($p_type == 'warning' || $p_type == 'notice') ? 'black' : 'grey')) . ' indent">' . $p_message . '</span>';

        return $this;
    } // function
} // class
?>