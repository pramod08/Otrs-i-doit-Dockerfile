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
 * Dashboard widget class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_dashboard_widgets
{
    /**
     * Array for external widgets.
     *
     * @var  array
     */
    protected static $m_external = [];
    /**
     * Array for all our instances
     *
     * @var  array
     */
    protected static $m_instances = [];
    /**
     * Ajax url information
     *
     * @var array
     */
    protected $m_ajax_url = [];
    /**
     * Configuration array.
     *
     * @var  array
     */
    protected $m_config = [];
    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Abstract render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    abstract public function render($p_unique_id); // function

    /**
     * Factory method for instant method chaining.
     *
     * @param   string $p_class
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_class)
    {
        return isys_factory::get_instance($p_class);
    } // function

    /**
     * Method for adding an external widget.
     *
     * @static
     *
     * @param   string $p_identifier
     * @param   string $p_class
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_register
     */
    public static function add_external_widget($p_identifier, $p_class)
    {
        isys_register::factory('widget-register')
            ->set($p_identifier, $p_class);
    } // function

    /**
     * Method for retrieving the class name of an external widget.
     *
     * @static
     *
     * @param   string $p_identifier
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_register
     */
    public static function get_external_widget_class($p_identifier)
    {
        return isys_register::factory('widget-register')
            ->get($p_identifier);
    }

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return false;
    }

    /**
     * Returns a boolean value, if the current widget has an own ajax handler
     *
     * @return bool
     */
    public function has_ajax_handler()
    {
        return false;
    } // function

    /**
     * Gets ajax url information
     *
     * @return array
     */
    public function get_ajax_url()
    {
        return $this->m_ajax_url;
    } // function

    /**
     * Sets ajax parameters
     *
     * @param $p_array
     */
    public function set_ajax_url($p_array)
    {
        $this->m_ajax_url = $p_array;
    } // function

    /**
     * Method for loading the widget configuration.
     * This method should return a rendered template with forms for the configuration - Use like "return $this->m_tpl->fetch('config.tpl');".
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        return '';
    } // function

    /**
     * Dummy init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        global $g_comp_template;

        $this->m_tpl    = $g_comp_template;
        $this->m_config = $p_config;

        return $this;
    }
} // class