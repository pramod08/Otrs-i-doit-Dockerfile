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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_notes extends isys_dashboard_widgets
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return true;
    } // function

    /**
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'notes.tpl';
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_rules = [
            'title'     => $this->m_config['title'],
            'color'     => $this->m_config['color'],
            'fontcolor' => $this->m_config['fontcolor'],
            'note'      => $this->m_config['note']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__NOTES__CONFIG'))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        $l_stripped_content = trim(strip_tags($this->m_config['note']));
        $l_empty            = empty($l_stripped_content);

        return $this->m_tpl->assign(
            'ajax_url',
            isys_helper_link::create_url(
                [
                    C__GET__AJAX      => 1,
                    C__GET__AJAX_CALL => 'dashboard',
                    'func'            => 'update_widget'
                ]
            )
        )
            ->assign('unique_id', $p_unique_id)
            ->assign('title', $this->m_config['title'])
            ->assign('color', $this->m_config['color'])
            ->assign('fontcolor', $this->m_config['fontcolor'])
            ->assign('note', $l_empty ? _L('LC__WIDGET__NOTES__DEFAULT') : $this->m_config['note'])
            ->assign('note_empty', $l_empty)
            ->fetch($this->m_tpl_file);
    } // function
} // class