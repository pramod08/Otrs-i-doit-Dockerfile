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
 * Dashboard widget class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.5.0
 */
class isys_dashboard_widgets_cmdb_statuslivecycle extends isys_dashboard_widgets
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
     * @return  isys_monitoring_widgets_not_ok_hosts
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file        = __DIR__ . '/templates/widget.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

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
        $l_objects = [];

        if (isset($this->m_config['objects']) && isys_format_json::is_json_array($this->m_config['objects']))
        {
            $l_objects = $this->m_config['objects'];
        } // if

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__MONITORING__WIDGET__NOT_OK_HOSTS__HOST_SELECTION'))
            ->assign('objects', $l_objects)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        $l_dao     = isys_cmdb_dao_status::instance(isys_application::instance()->database);
        $l_message = false;
        $l_changes = $l_matcher = $l_cmdb_status = [];
        $l_data    = [
            'objects' => [],
            'changes' => [],
            'label'   => []
        ];

        if (isset($this->m_config['objects']) && isys_format_json::is_json_array($this->m_config['objects']))
        {
            $this->m_config['objects'] = isys_format_json::decode($this->m_config['objects']);

            if (is_array($this->m_config['objects']) && count($this->m_config['objects']))
            {
                // Retrieve the CMDB status.
                $l_res = $l_dao->get_cmdb_status(null, ' ORDER BY isys_cmdb_status__id ASC ');

                if (count($l_res))
                {
                    while ($l_row = $l_res->get_row())
                    {
                        if ($l_row['isys_cmdb_status__id'] == C__CMDB_STATUS__IDOIT_STATUS || $l_row['isys_cmdb_status__id'] == C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE)
                        {
                            continue;
                        } // if

                        $l_matcher[]     = $l_row['isys_cmdb_status__id'];
                        $l_cmdb_status[] = _L($l_row['isys_cmdb_status__title']);
                    } // while
                } // if

                $l_matcher = array_flip($l_matcher);

                // Retrieve the changes of the given objects.
                $l_res = $l_dao->get_changes_by_obj_id($this->m_config['objects']);

                if (count($l_res))
                {
                    while ($l_row = $l_res->get_row())
                    {
                        if (!isset($l_data['objects'][$l_row['isys_obj__id']]))
                        {
                            $l_data['objects'][$l_row['isys_obj__id']] = [
                                'obj_title'      => $l_row['isys_obj__title'],
                                'obj_type_title' => _L($l_row['isys_obj_type__title']),
                                'obj_type_color' => $l_row['isys_obj_type__color'],
                                'status'         => $l_row['isys_obj__isys_cmdb_status__id']
                            ];
                        } // if

                        $l_changes[strtotime(
                            $l_row['isys_cmdb_status_changes__timestamp']
                        )][$l_row['isys_obj__id']][] = $l_row['isys_cmdb_status_changes__isys_cmdb_status__id'];
                    } // while
                } // if

                $l_last_timestamp = 0;

                foreach ($l_changes as $l_timestamp => $l_changedata)
                {
                    foreach ($l_data['objects'] as $l_obj_id => $l_obj_data)
                    {
                        if (isset($l_changedata[$l_obj_id]))
                        {
                            foreach ($l_changedata[$l_obj_id] as $l_single_change)
                            {
                                $l_data['changes'][$l_obj_id][] = $l_matcher[$l_single_change];
                            } // foreach
                        }
                        else
                        {
                            $l_data['changes'][$l_obj_id][] = null;
                        } // if
                    } // foreach

                    if (date('m.Y', $l_timestamp) != date('m.Y', $l_last_timestamp))
                    {
                        $l_data['label'][] = strftime('%B %Y', $l_timestamp);

                        $l_last_timestamp = $l_timestamp;
                    }
                    else
                    {
                        $l_data['label'][] = null;
                    }
                }

                // At the end we insert the current object status:
                foreach ($l_data['objects'] as $l_obj_id => $l_obj_data)
                {
                    $l_data['changes'][$l_obj_id][] = $l_matcher[$l_obj_data['status']];
                } // foreach
            }
            else
            {
                $l_message = _L('LC__MODULE__CMDB__VISUALIZATION__EMPTY_SELECTION');
            } // if
        } // if

        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->assign('title', _L('LC__WIDGET__LIVECYCLE_CMDBSTATUS'))
            ->assign('message', $l_message)
            ->assign('data', isys_format_json::encode($l_data))
            ->assign('object_count', count($l_data['objects']))
            ->assign('cmdb_status', isys_format_json::encode($l_cmdb_status))
            ->assign('cmdb_status_lowest', current(array_keys($l_cmdb_status)))
            ->assign('cmdb_status_highest', end(array_keys($l_cmdb_status)))
            ->assign('delta', count($l_data['label']))
            ->fetch($this->m_tpl_file);
    } // function
} // class