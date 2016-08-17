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
 * Visualization it-service selection popup.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_popup_visualization_itservice_selection extends isys_component_popup
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

        return '<a href="javascript:' . $this->process_overlay('', 800, 500, $p_params) . '" class="ml5 vam" title="' . _L(
            'LC__MODULE__CMDB__VISUALIZATION__IT_SERVICE_SELECTION'
        ) . '">' . '<img src="' . $g_dirs['images'] . 'icons/silk/chart_pie.png" class="vam" alt="' . _L(
            'LC__MODULE__CMDB__VISUALIZATION__IT_SERVICE_SELECTION'
        ) . '" />' . '</a>';
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
        global $g_config;

        $l_rules = [];

        // Here we load the available service types and IT-services without any type.
        $l_itservice_types = [];
        $l_itservice_data  = isys_factory_cmdb_dialog_dao::get_instance('isys_its_type', $this->m_db)
            ->get_data();

        $l_itservice_types[-1] = _L('LC__UNIVERSAL__ALL');

        if (is_array($l_itservice_data))
        {
            foreach ($l_itservice_data as $l_itservice_type_id => $l_itservice_type)
            {
                $l_itservice_types[$l_itservice_type_id] = $l_itservice_type['title'];
            } // foreach
        } // if

        asort($l_itservice_types);

        $this->m_tpl->activate_editmode()
            ->smarty_tom_add_rules('tom.popup.visualization', $l_rules)
            ->assign('visualization_assets', $g_config['www_dir'] . 'src/classes/modules/pro/visualization/assets/')
            ->assign('it_service_types', $l_itservice_types)
            ->assign(
                'ajax_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX_CALL => 'visualization',
                        C__GET__AJAX      => 1,
                        'func'            => 'load-it-services-by-type'
                    ]
                )
            )
            ->display(dirname(__DIR__) . DS . 'assets' . DS . 'popup_itservice_selection.tpl');
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