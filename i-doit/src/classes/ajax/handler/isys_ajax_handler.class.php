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

abstract class isys_ajax_handler implements isys_ajax_handler_interface
{
    /**
     * @var  isys_component_database
     */
    protected $m_database_component;
    protected $m_get;
    protected $m_post;
    protected $m_smarty_dir;

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return false;
    } // function

    /**
     * Default initializer
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function init()
    {
        global $g_comp_template;

        $g_comp_template->display("file:" . $this->m_smarty_dir . "templates/content/main_groups.tpl");
        $this->_die();
    } // function

    /**
     * Send data to browser.
     *
     * @param   string $string
     *
     * @return  $this
     * @throws  Exception
     */
    protected function send($string)
    {
        // LF: Carefull when "sending" JSON - this might be mistaken for smarty syntax and trigger an error!
        isys_application::instance()->template->display('string:' . $string);

        $this->_die();

        return $this;
    } // function

    /**
     * Method for writing javascript inside a javascrip tag.
     *
     * @deprecated
     *
     * @param   string $p_javascript
     *
     * @return  string
     */
    protected function script($p_javascript)
    {
        return "<script type=\"text/javascript\">" . $p_javascript . "</script>";
    } // function

    /**
     * Returns current script processing time.
     *
     * @global  float $g_start_time
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    protected function get_processing_time()
    {
        global $g_start_time;

        return (microtime(true) - $g_start_time) . "ms";
    } // function

    /**
     * A wrapper for PHP's "die()".
     *
     * @param   string $p_str
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    protected function _die($p_str = '')
    {
        die($p_str);
    } // function

    /**
     * Don't forget to add parent::__construct() if you overwrite the constructor!!
     *
     * @param   array $p_get
     * @param   array $p_post
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_get, $p_post)
    {
        global $g_absdir, $g_comp_database;

        // @todo Update the smarty dir - This should be generic?
        $this->m_smarty_dir         = $g_absdir . "/src/themes/default/smarty/";
        $this->m_database_component = $g_comp_database;
        $this->m_get                = $p_get;
        $this->m_post               = $p_post;
    } // function
} // interface

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface isys_ajax_handler_interface
{
    public function init();
} // class