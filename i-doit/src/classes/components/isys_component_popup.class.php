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
 * Base class for popups. All popups are located in the directory "src/classes/popups".
 * The classes in it have to be inherited from this class.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_component_popup extends isys_component
{
    /**
     * Private configuration array.
     *
     * @var array
     */
    private $m_config;
    private $m_popupname;

    /**
     * Abstract method for handling module request.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return isys_component_template
     */
    abstract public function &handle_module_request(isys_module_request $p_modreq);

    /**
     * Returns a configuration parameter.
     *
     * @param   string $p_var
     *
     * @return  array
     * @return  null
     */
    public function get_config($p_var)
    {
        if (array_key_exists($p_var, $this->m_config))
        {
            return $this->m_config[$p_var];
        } // if

        return null;
    } // function

    /**
     * Formats the popup selection.
     *
     * @param   integer $p_id
     * @param   boolean $p_plain
     *
     * @return  string
     */
    public function format_selection($p_id, $p_plain = false)
    {
        return '';
    } // function

    /**
     * Gets popupname.
     *
     * @return  string
     */
    public function get_popupname()
    {
        return $this->m_popupname;
    } // function

    /**
     * Returns the whole configuration array.
     *
     * @return  array
     */
    public function get_config_array()
    {
        return $this->m_config;
    } // function

    /**
     * Sets a configuration parameters in the configuration array. Returns null if the key does not exist.
     *
     * Parameters => default value:
     *       "dependant"        => "yes"
     *       "height"            => 500
     *       "width"            => 500
     *       "menubar"        => "no"
     *       "resizable"        => "yes"
     *       "scrollbars"        => "yes"
     *       "status"            => "no"
     *       "toolbar"        => "no"
     *       "location"        => "yes"
     *
     * @param   string $p_var
     * @param   string $p_val
     *
     * @return  mixed
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function &set_config($p_var, $p_val)
    {
        if (array_key_exists($p_var, $this->m_config))
        {
            $this->m_config[$p_var] = $p_val;

            return $this->m_config;
        } // if

        return null;
    } // function

    /**
     * Process the overlay page.
     *
     * @param   string  $p_url
     * @param   integer $p_width
     * @param   integer $p_height
     * @param   array   $p_params
     * @param   string  $p_popup_receiver
     *
     * @return  string
     */
    public function process_overlay($p_url, $p_width = 950, $p_height = 550, $p_params = [], $p_popup_receiver = null)
    {
        $l_popup = str_replace("isys_popup_", "", get_class($this));

        return "get_popup('" . $l_popup . "', '" . $p_url . "', '" . $p_width . "', '" . $p_height . "', {params:'" . base64_encode(
            isys_format_json::encode($p_params)
        ) . "'}, " . ("'" . $p_popup_receiver . "'" ?: 'null') . ");";
    } // function

    /**
     * Returns a javascript function to display the object browser.
     *
     * @param   array $p_params
     *
     * @return  string
     */
    public function get_js_handler($p_params)
    {
        return $this->process_overlay('', 1100, 650, $p_params);
    } // function

    /**
     * Creates a popup-JS-block and returns it.
     *
     * @param   string  $p_uri
     * @param   boolean $p_center
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function process($p_uri, $p_center = false)
    {
        // Without a URL just return an empty string.
        if (empty($p_uri))
        {
            return '';
        } // if

        // Build JS call.
        return "isys_popup_open('" . $p_uri . "', '" . $this->m_popupname . "', " . (($p_center) ? 1 : 0) . ", " . str_replace(
            '"',
            "'",
            isys_format_json::encode($this->m_config)
        ) . ");";
    } // function

    /**
     * Handles SMARTY request for commentary popup.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    // commented for php7 compatibility
    //public function handle_smarty_include (isys_component_template &$p_tplclass, $p_params)
    //{
    //
    //} // function

    /**
     * Popup constructor.
     */
    public function __construct()
    {
        $this->m_popupname = 'isysPopup' . rand(10, 50);

        // Default popup configuration.
        $this->m_config = [
            'dependant'  => 'yes',
            'width'      => 500,
            'height'     => 500,
            'menubar'    => 'no',
            'resizable'  => 'yes',
            'scrollbars' => 'yes',
            'status'     => 'no',
            'toolbar'    => 'no',
            'location'   => 'no',
        ];
    } // function
} // class