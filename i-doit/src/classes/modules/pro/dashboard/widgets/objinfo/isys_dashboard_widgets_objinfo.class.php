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
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_objinfo extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $m_config_tpl_file = '';
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     */
    public function has_configuration()
    {
        return true;
    } // function

    /**
     * Init method.
     *
     * @return  isys_dashboard_widgets_quicklaunch
     */
    public function init($p_config = [])
    {
        if (!$p_config)
        {
            $p_config = [];
        }

        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'objinfo.tpl';
        $this->m_config_tpl_file = __DIR__ . DS . 'templates' . DS . 'config.tpl';

        return parent::init($p_config);
    } // function

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     */
    public function load_configuration(array $p_row, $p_id)
    {
        if (!isset($this->m_config['objids']))
        {
            $this->m_config['objids'] = [
                null,
                null,
                null
            ];
        }

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__OBJINFO__CONFIG'))
            ->assign('rules', $this->m_config)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     */
    public function render($p_unique_id)
    {
        $l_data = '';

        if (isset($this->m_config['objids']) && is_array($this->m_config['objids']) && count($this->m_config['objids']) > 0)
        {
            foreach ($this->m_config['objids'] as $l_objID)
            {
                if ($l_objID > 0)
                {
                    $l_qinfo = new isys_ajax_handler_quick_info();
                    $l_data .= $l_qinfo->get_quick_info_content(
                        $l_objID,
                        [
                            C__CATG__GLOBAL,
                            C__CATG__CONTACT,
                            C__CATG__MODEL,
                            C__CATG__CPU,
                            C__CATG__NETWORK,
                            C__CATG__VERSION,
                        ],
                        [
                            C__CATS__NET,
                            C__CATS__CONTRACT
                        ],
                        [
                            'hideCloseButton'  => true,
                            'createObjectLink' => true,
                            'maxLen'           => 150
                        ]
                    );
                }
            }
        }

        return $this->m_tpl->assign('data', $l_data)
            ->fetch($this->m_tpl_file);
    } // function
} // class