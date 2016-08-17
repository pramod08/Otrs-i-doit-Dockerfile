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
 * Visualization export popup.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_popup_visualization_export extends isys_component_popup
{
    /**
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Handles SMARTY request for dialog plus lists and builds the list base on the specified table.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs;

        $l_url_params = C__CMDB__VISUALIZATION_VIEW . '=' . $_GET[C__CMDB__VISUALIZATION_VIEW] . '&' . C__CMDB__VISUALIZATION_TYPE . '=' . $_GET[C__CMDB__VISUALIZATION_TYPE];

        $l_button_options = [
            'id'                => $p_params['name'],
            'p_strClass'        => 'fr btn mr5',
            'icon'              => $g_dirs['images'] . 'icons/silk/disk.png',
            'p_bInfoIconSpacer' => 0,
            'p_onClick'         => $this->process_overlay($l_url_params, 500, 300, $p_params),
            'type'              => 'button',
            'p_strTitle'        => _L('LC__VISUALIZATION_EXPORT'),
            'p_strValue'        => _L('LC__VISUALIZATION_EXPORT'),
        ];

        return isys_factory::get_instance('isys_smarty_plugin_f_button')
            ->navigation_edit($this->m_tpl, $l_button_options);
    } // function

    /**
     * Method for handling the module request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  null
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_rules = [];

        $l_export_url = isys_helper_link::create_url(
            [
                C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                C__CMDB__VISUALIZATION_VIEW => $_GET[C__CMDB__VISUALIZATION_VIEW],
                C__CMDB__VISUALIZATION_TYPE => $_GET[C__CMDB__VISUALIZATION_TYPE],
                'export'                    => 'graphml'
            ]
        );

        $this->m_tpl->activate_editmode()
            ->assign('export_url', $l_export_url)
            ->smarty_tom_add_rules('tom.popup.visualization', $l_rules)
            ->display(dirname(__DIR__) . DS . 'assets' . DS . 'popup_export.tpl');
        die;
    } // function

    /**
     * Constructor method.
     */
    public function __construct()
    {
        global $g_comp_database, $g_comp_template;

        $this->m_db  = $g_comp_database;
        $this->m_tpl = $g_comp_template;

        parent::__construct();
    } // function
} // class