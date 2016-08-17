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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.3
 */
class isys_ajax_handler_smartyplugin extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try
        {
            switch ($_GET['mode'])
            {
                case 'view':
                    $l_return['data'] = $this->view_mode($_POST['plugin_name'], isys_format_json::decode($_POST['parameters']));
                    break;

                case 'edit':
                    $l_return['data'] = $this->edit_mode($_POST['plugin_name'], isys_format_json::decode($_POST['parameters']));
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Method for loading an smarty plugin in VIEW mode.
     *
     * @global  isys_component_template $g_comp_template
     *
     * @param   string                  $p_plugin_name
     * @param   array                   $p_parameters
     *
     * @throws  isys_exception_template
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function view_mode($p_plugin_name, $p_parameters = [])
    {
        global $g_comp_template;

        $l_classname = 'isys_smarty_plugin_' . $p_plugin_name;

        if (!class_exists($l_classname))
        {
            throw new isys_exception_template('The requested plugin "' . $p_plugin_name . '" seems to miss its PHP-class "' . $l_classname . '".');
        } // if

        $l_class = new $l_classname;

        if (!$l_class instanceof isys_smarty_plugin_f)
        {
            throw new isys_exception_template('The requested class "' . $l_class . '" is not extending "isys_smarty_plugin_f".');
        } // if

        return $l_class->navigation_view($g_comp_template, $p_parameters);
    } // function

    /**
     * Method for loading an smarty plugin in EDIT mode.
     *
     * @global  isys_component_template $g_comp_template
     *
     * @param   string                  $p_plugin_name
     * @param   array                   $p_parameters
     *
     * @throws  isys_exception_template
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function edit_mode($p_plugin_name, $p_parameters = [])
    {
        global $g_comp_template;

        $g_comp_template->activate_editmode();

        $l_classname = 'isys_smarty_plugin_' . $p_plugin_name;

        if (!class_exists($l_classname))
        {
            throw new isys_exception_template('The requested plugin "' . $p_plugin_name . '" seems to miss its PHP-class "' . $l_classname . '".');
        } // if

        $l_class = new $l_classname;

        if (!$l_class instanceof isys_smarty_plugin_f)
        {
            throw new isys_exception_template('The requested class "' . $l_class . '" is not extending "isys_smarty_plugin_f".');
        } // if

        return $l_class->navigation_edit($g_comp_template, $p_parameters);
    } // function
} // class