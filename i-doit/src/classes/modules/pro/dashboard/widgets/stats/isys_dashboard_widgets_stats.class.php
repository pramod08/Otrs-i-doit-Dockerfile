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
class isys_dashboard_widgets_stats extends isys_dashboard_widgets
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
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'stats.tpl';
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
        global $g_comp_database;

        $l_cmdb_dao = isys_cmdb_dao::instance($g_comp_database);

        if (!is_array($this->m_config['obj_types']))
        {
            $this->m_config['obj_types'] = [];
        } // if

        $l_obj_type_data = [];
        $l_obj_types     = $l_cmdb_dao->get_object_type();

        foreach ($l_obj_types as $l_obj_type)
        {
            $l_obj_type_data[] = [
                'id'  => $l_obj_type['isys_obj_type__const'],
                'val' => $l_obj_type['LC_isys_obj_type__title'],
                'sel' => in_array($l_obj_type['isys_obj_type__const'], $this->m_config['obj_types'])
            ];
        } // foreach

        $l_rules = [
            'title'         => $this->m_config['title'],
            'legend'        => $this->m_config['legend'],
            'obj_types'     => serialize($l_obj_type_data),
            'selected_type' => $this->m_config['chart_type'],
            'chart_types'   => serialize(
                [
                    // 'AccumulatorBar' => 'AccumulatorBar', // Will not work with this sort of data.
                    // 'Area' => 'Area', // Will not work with this sort of data.
                    'Bar'            => 'LC__WIDGET__STATS__CONFIG_TYPE__BAR',
                    // 'Dot' => 'Dot', // Will not look good with this sort of data.
                    // 'Line' => 'Line', // Will not look good with this sort of data.
                    'Mini.Bar'       => 'LC__WIDGET__STATS__CONFIG_TYPE__BAR_BIG',
                    'Mini.Pie'       => 'LC__WIDGET__STATS__CONFIG_TYPE__PIE_BIG',
                    'Mini.SideBar'   => 'LC__WIDGET__STATS__CONFIG_TYPE__SIDE_BAR_BIG',
                    // 'Net' => 'Net', // Will not work with this sort of data.
                    'Pie'            => 'LC__WIDGET__STATS__CONFIG_TYPE__PIE',
                    'SideBar'        => 'LC__WIDGET__STATS__CONFIG_TYPE__SIDE_BAR',
                    'SideStackedBar' => 'LC__WIDGET__STATS__CONFIG_TYPE__SIDE_STACKED_BAR',
                    // 'Spider' => 'spider', // Will not look good with this sort of data.
                    // 'StackedArea' => 'Stacked Area', // Will not work with this sort of data.
                    'StackedBar'     => 'LC__WIDGET__STATS__CONFIG_TYPE__STACKED_BAR',
                ]
            ),
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__STATS__CONFIG'))
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
        global $g_comp_database;

        $l_dao = isys_cmdb_dao::instance($g_comp_database);

        $l_obj_type_config = [];
        $l_obj_types       = $l_dao->get_object_type(null, $this->m_config['obj_types']);

        foreach ($l_obj_types as $l_obj_type)
        {
            $l_sql = 'SELECT COUNT(*) AS cnt FROM isys_obj
				WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_obj_type['isys_obj_type__id']) . '
				AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

            $l_cnt_row = $l_dao->retrieve($l_sql)
                ->get_row();

            $l_obj_type_config[] = [
                'title'   => $l_obj_type['LC_isys_obj_type__title'],
                'color'   => $l_obj_type['isys_obj_type__color'],
                'obj_cnt' => (int) $l_cnt_row['cnt']
            ];
        } // foreach

        return $this->m_tpl->assign('legend', empty($this->m_config['legend']))
            ->assign('title', $this->m_config['title'])
            ->assign('chart_type', $this->m_config['chart_type'])
            ->assign('unique_id', $p_unique_id)
            ->assign('object_types', $l_obj_type_config)
            ->fetch($this->m_tpl_file);
    } // function
} // class