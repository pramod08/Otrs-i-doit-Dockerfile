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
 * AJAX handler for the calendar widget.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.2.0
 */
class isys_ajax_handler_dashboard_widgets_calendar extends isys_ajax_handler_dashboard
{
    /**
     * Static method for retrieving "planning" data for the given object. Gets called statically by "$this->callback()".
     *
     * @static
     *
     * @param   array $p_params
     *
     * @return  string
     * @see     $this->callback()
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_planning_data(array $p_params)
    {
        global $g_comp_database, $g_comp_template;

        /* @var  isys_cmdb_dao_category_g_planning $l_dao */
        $l_dao = isys_cmdb_dao_category_g_planning::instance($g_comp_database);

        $l_planning_row = $l_dao->get_data($p_params['cat_id'], $p_params['obj_id'])
            ->get_row();

        $l_status = $l_dao->retrieve(
            'SELECT isys_cmdb_status__title AS title, isys_cmdb_status__color AS color
			FROM isys_cmdb_status
			WHERE isys_cmdb_status__id = ' . $l_dao->convert_sql_id($l_planning_row['isys_catg_planning_list__isys_cmdb_status__id']) . ';'
        )
            ->get_row();

        return $g_comp_template->assign(
            'data',
            [
                'obj_link'        => isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_planning_row['isys_obj__id']]),
                'obj_id'          => $l_planning_row['isys_obj__id'],
                'obj_title'       => $l_planning_row['isys_obj__title'],
                'obj_type_title'  => _L($l_planning_row['isys_obj_type__title']),
                'planning_start'  => date('d.m.Y', $l_planning_row['isys_catg_planning_list__start']),
                'planning_end'    => date('d.m.Y', $l_planning_row['isys_catg_planning_list__end']),
                'planning_status' => $l_status
            ]
        )
            ->fetch(isys_dashboard_widgets_calendar::get_tpl_dir() . 'events' . DS . 'planning.tpl');
    } // function

    /**
     * Static method for retrieving "maintenance" data for the given object. Gets called statically by "$this->callback()".
     *
     * @static
     *
     * @param   array $p_params
     *
     * @return  string
     * @see     $this->callback()
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_maintenance_data(array $p_params)
    {
        global $g_comp_database, $g_comp_template;

        /* @var  isys_maintenance_dao $l_dao */
        $l_dao = isys_maintenance_dao::instance($g_comp_database);

        $l_maintenance_data = $l_dao->get_data($p_params['maintenance_id'])
            ->get_row();
        $l_object_data      = $l_dao->get_object($p_params['obj_id'])
            ->get_row();
        $l_color            = $l_dao->retrieve('SELECT isys_cmdb_status__color FROM isys_cmdb_status WHERE isys_cmdb_status__const = "C__CMDB_STATUS__UNDER_REPAIR";')
            ->get_row_value('isys_cmdb_status__color');

        return $g_comp_template->assign('color', $l_color)
            ->assign(
                'data',
                [
                    'obj_link'          => isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_params['obj_id']]),
                    'obj_id'            => $p_params['obj_id'],
                    'obj_title'         => $l_object_data['isys_obj__title'],
                    'obj_type_title'    => _L($l_object_data['isys_obj_type__title']),
                    'maintenance_start' => date('d.m.Y', strtotime($l_maintenance_data['isys_maintenance__date_from'])),
                    'maintenance_end'   => date('d.m.Y', strtotime($l_maintenance_data['isys_maintenance__date_to'])),
                    'maintenance_type'  => _L($l_maintenance_data['isys_maintenance_type__title']),
                ]
            )
            ->assign(
                'module_link',
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                        C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 2,
                        C__GET__SETTINGS_PAGE => C__MAINTENANCE__PLANNING,
                        C__GET__ID            => $p_params['maintenance_id']
                    ]
                )
            )
            ->fetch(isys_dashboard_widgets_calendar::get_tpl_dir() . 'events' . DS . 'maintenance.tpl');
    } // function

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
            switch ($_GET['func'])
            {
                case 'trigger_callback':
                    $l_return['data'] = $this->callback(
                        isys_format_json::decode($_POST['events'], true),
                        $_POST['day'],
                        $_POST['month'],
                        $_POST['year']
                    );
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = isys_glob_utf8_encode($e->getMessage());
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Callback method for handlind calendar events (even if they're not type "callback").
     *
     * @param   array   $p_events
     * @param   integer $p_day
     * @param   integer $p_month
     * @param   integer $p_year
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function callback($p_events, $p_day, $p_month, $p_year)
    {
        global $g_comp_template;

        $l_output = [];

        if (is_array($p_events) && count($p_events) > 0)
        {
            foreach ($p_events as $l_event)
            {
                if ($l_event['type'] == isys_component_calendar_event::TYPE_NOTE || $l_event['type'] == isys_component_calendar_event::TYPE_ALERT)
                {
                    $l_output[] = $l_event['name'];
                }
                else if ($l_event['type'] == isys_component_calendar_event::TYPE_CALLBACK && isset($l_event['callback']))
                {
                    if (is_callable($l_event['callback']['callback']))
                    {
                        $l_output[] = [
                            'data' => call_user_func($l_event['callback']['callback'], $l_event['callback']['params'])
                        ];
                    }
                    else
                    {
                        $l_message  = 'The given callback is not callable!';
                        $l_output[] = [
                            'data' => $g_comp_template->assign('message', $l_message)
                                ->fetch(isys_dashboard_widgets_calendar::get_tpl_dir() . 'events' . DS . 'error.tpl')
                        ];
                    } // if
                }// if
            } // foreach
        } // if

        return $l_output;
    } // function
} // class