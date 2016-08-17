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
 * "Finish" maintenance popup.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */
class isys_popup_maintenance_finish extends isys_component_popup
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
     * This will not be used, because the popup will be called directly from JS.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        return '';
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
        $l_rules = [
            'C__MAINTENANCE__POPUP__FINISH_COMMENT' => [
                'p_strClass' => 'input-small'
            ]
        ];

        $this->m_tpl->activate_editmode()
            ->assign(
                'ajax_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX      => 1,
                        C__GET__AJAX_CALL => 'maintenance'
                    ]
                )
            )
            ->smarty_tom_add_rules('tom.popup.maintenance', $l_rules)
            ->display(isys_module_maintenance::get_tpl_dir() . 'popup' . DS . 'finish_maintenance.tpl');
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