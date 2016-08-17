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
class isys_dashboard_widgets_calendar extends isys_dashboard_widgets
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

    public static function get_tpl_dir()
    {
        return __DIR__ . DS . 'templates' . DS;
    } // function

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
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'calendar.tpl';
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
            'title'         => $this->m_config['title'],
            'object_events' => $this->m_config['object_events'],
            //			'holiday_events' => $this->m_config['holiday_events']
        ];

        $l_event_types = [
            isys_component_calendar_event::TYPE_NOTE => _L('LC__CALENDAR_TYPE__NOTE'),
            //			isys_component_calendar_event::TYPE_ALERT => _L('LC__CALENDAR_TYPE__ALERT'),
        ];

        $l_events = $this->m_config['events'];

        if (is_array($l_events) && count($l_events) > 0)
        {
            foreach ($l_events as &$l_event)
            {
                $l_event['LC_type'] = $l_event_types[$l_event['type']];
            } // foreach
        } // if

        $l_url = isys_helper_link::create_url(
            [
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'dashboard_widgets_calendar'
            ]
        );

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__CALENDAR_CONFIG'))
            ->assign('events', $l_events)
            ->assign('rules', $l_rules)
            ->assign('event_types', serialize($l_event_types))
            ->assign('calendar_ajax_url', $l_url)
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
        $l_calendar = isys_component_calendar::factory($p_unique_id);

        if (count($this->m_config['events']) > 0)
        {
            foreach ($this->m_config['events'] as $l_event)
            {
                list($l_day, $l_month, $l_year) = explode('.', $l_event['date']);

                $l_cal_event = isys_component_calendar_event::factory($l_event['name'], $l_day, $l_month, $l_year)
                    ->set_type($l_event['type']);

                if ($l_event['callback'] && is_callable($l_event['callback']))
                {
                    $l_event->set_callback($l_event['callback']);
                } // if

                $l_calendar->add_event($l_cal_event);
            } // foreach
        } // if

        if ($this->m_config['object_events'])
        {
            $this->add_object_events($l_calendar);
        } // if

//		if ($this->m_config['holiday_events'])
//		{
//			$l_calendar->add_holidays(date('Y'));
//		} // if

        $l_ajax_url = isys_helper_link::create_url(
            [
                C__GET__AJAX_CALL => 'dashboard_widgets_calendar',
                C__GET__AJAX      => '1',
                'func'            => 'trigger_callback'
            ]
        );

        // refs #4964 - The last month of the previous year is beeing displayed wrong.
        $l_prev_options = [
            'month' => date('n') - 1,
            'year'  => date('Y')
        ];

        if ($l_prev_options['month'] == 0)
        {
            $l_prev_options = [
                'month' => 12,
                'year'  => date('Y') - 1
            ];
        } // if

        // refs #4964 - The first month of the next year is beeing displayed wrong.
        $l_next_options = [
            'month' => date('n') + 1,
            'year'  => date('Y')
        ];

        if ($l_next_options['month'] == 13)
        {
            $l_next_options = [
                'month' => 1,
                'year'  => date('Y') + 1
            ];
        } // if

        return $this->m_tpl->assign('ajax_url', $l_ajax_url)
            ->assign('unique_id', $p_unique_id)
            ->assign('title', $this->m_config['title'])
            ->assign('data', $l_calendar->render(false))
            ->assign(
                'data_prev',
                $l_calendar->merge_options($l_prev_options)
                    ->render(false)
            )
            ->assign(
                'data_next',
                $l_calendar->merge_options($l_next_options)
                    ->render(false)
            )
            ->fetch($this->m_tpl_file);
    } // function

    /**
     * Method for adding object specific events to the calendar.
     *
     * @param   isys_component_calendar $p_calendar
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function add_object_events(isys_component_calendar $p_calendar)
    {
        global $g_comp_session, $g_comp_database;

        /**
         * @var  isys_cmdb_dao_category_g_planning $l_dao
         * @var  isys_maintenance_dao              $l_maintenance_dao
         */
        $l_dao             = isys_cmdb_dao_category_g_planning::instance($g_comp_database);
        $l_maintenance_dao = isys_maintenance_dao::instance($g_comp_database);
        $l_cmdb            = [];

        $l_res = $l_dao->retrieve('SELECT isys_cmdb_status__id, isys_cmdb_status__title FROM isys_cmdb_status;');

        while ($l_row = $l_res->get_row())
        {
            $l_cmdb[$l_row['isys_cmdb_status__id']] = _L($l_row['isys_cmdb_status__title']);
        } // while

        $l_res = isys_cmdb_dao_category_s_person_contact_assign::instance($g_comp_database)
            ->get_data(null, $g_comp_session->get_user_id(), 'AND isys_catg_contact_list__isys_contact_tag__id = ' . $l_dao->convert_sql_id(C__CONTACT_TYPE__ADMIN));

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_planning_res = $l_dao->get_data(null, $l_row['isys_obj__id']);

                if (count($l_planning_res) > 0)
                {
                    while ($l_planning_row = $l_planning_res->get_row())
                    {
                        list($l_day, $l_month, $l_year) = explode('-', date('j-n-Y', $l_planning_row['isys_catg_planning_list__start']));

                        $p_calendar->add_event(
                            isys_component_calendar_event::factory(
                                _L('LC__UNIVERSAL__START') . ': ' . $l_cmdb[$l_planning_row['isys_catg_planning_list__isys_cmdb_status__id']],
                                $l_day,
                                $l_month,
                                $l_year
                            )
                                ->set_callback(
                                    [
                                        'isys_ajax_handler_dashboard_widgets_calendar',
                                        'get_planning_data'
                                    ],
                                    [
                                        'obj_id' => $l_row['isys_obj__id'],
                                        'cat_id' => $l_planning_row['isys_catg_planning_list__id']
                                    ]
                                )
                        );

                        list($l_day, $l_month, $l_year) = explode('-', date('j-n-Y', $l_planning_row['isys_catg_planning_list__end']));

                        $p_calendar->add_event(
                            isys_component_calendar_event::factory(
                                _L('LC__UNIVERSAL__STOP') . ': ' . $l_cmdb[$l_planning_row['isys_catg_planning_list__isys_cmdb_status__id']],
                                $l_day,
                                $l_month,
                                $l_year
                            )
                                ->set_callback(
                                    [
                                        'isys_ajax_handler_dashboard_widgets_calendar',
                                        'get_planning_data'
                                    ],
                                    [
                                        'obj_id' => $l_row['isys_obj__id'],
                                        'cat_id' => $l_planning_row['isys_catg_planning_list__id']
                                    ]
                                )
                        );
                    } // while
                } // if

                // Look for maintenance events, if the module is active.
                if (isys_module_manager::instance()
                        ->is_active('maintenance') && method_exists($l_maintenance_dao, 'get_data_by_maintenance_object')
                )
                {
                    try
                    {
                        $l_maintenance_res = $l_maintenance_dao->get_data_by_maintenance_object(
                            $l_row['isys_obj__id'],
                            mktime(0, 0, 0, (date('m') - 1), 1, date('Y')),
                            mktime(0, 0, 0, (date('m') + 2), 0, date('Y'))
                        );

                        if (count($l_maintenance_res))
                        {
                            while ($l_maintenance_row = $l_maintenance_res->get_row())
                            {
                                list($l_year, $l_month, $l_day) = explode('-', $l_maintenance_row['isys_maintenance__date_from']);

                                $p_calendar->add_event(
                                    isys_component_calendar_event::factory(_L('LC__UNIVERSAL__START') . ': ' . _L('LC__MODULE__MAINTENANCE'), $l_day, $l_month, $l_year)
                                        ->set_callback(
                                            [
                                                'isys_ajax_handler_dashboard_widgets_calendar',
                                                'get_maintenance_data'
                                            ],
                                            [
                                                'obj_id'         => $l_row['isys_obj__id'],
                                                'maintenance_id' => $l_maintenance_row['isys_maintenance__id']
                                            ]
                                        )
                                );

                                list($l_year, $l_month, $l_day) = explode('-', $l_maintenance_row['isys_maintenance__date_to']);

                                $p_calendar->add_event(
                                    isys_component_calendar_event::factory(_L('LC__UNIVERSAL__STOP') . ': ' . _L('LC__MODULE__MAINTENANCE'), $l_day, $l_month, $l_year)
                                        ->set_callback(
                                            [
                                                'isys_ajax_handler_dashboard_widgets_calendar',
                                                'get_maintenance_data'
                                            ],
                                            [
                                                'obj_id'         => $l_row['isys_obj__id'],
                                                'maintenance_id' => $l_maintenance_row['isys_maintenance__id']
                                            ]
                                        )
                                );
                            } // while
                        } // if
                    }
                    catch (Exception $e)
                    {
                        // Silently fail...
                    } // try
                } // if
            } // while
        } // if
    } // function
} // class