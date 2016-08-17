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
 * Export helper for Nagios
 *
 * @package    i-doit
 * @subpackage Export
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_export_helper_nagios extends isys_export_helper
{
    public function assigned_ip_address($p_id)
    {
        $l_table = 'isys_catg_ip_list';
        $l_arr   = [];
        $l_dao   = new isys_cmdb_dao($this->m_database);
        if (empty($p_id))
        {
            $l_sql = 'SELECT * FROM ' . $l_table . ' INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = ' . $l_table . '__isys_cats_net_ip_addresses_list__id ' . ' WHERE ' . $l_table . '__isys_obj__id = ' . $l_dao->convert_sql_id(
                    $this->m_row['isys_obj__id']
                ) . ' AND ' . $l_table . '__primary = 1';
        }
        else
        {
            $l_sql = 'SELECT * FROM ' . $l_table . ' INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = ' . $l_table . '__isys_cats_net_ip_addresses_list__id ' . ' WHERE ' . $l_table . '__id = ' . $l_dao->convert_sql_id(
                    $p_id
                );
        }

        $l_res = $l_dao->retrieve($l_sql);
        if ($l_res->num_rows() > 0)
        {
            $l_row = $l_res->get_row();
            $l_arr = [
                'id'    => $l_row[$l_table . '__id'],
                'type'  => 'C__CATG__IP',
                'title' => $l_row['isys_cats_net_ip_addresses_list__title'],
            ];
        }

        return $l_arr;
    }

    public function assigned_ip_address_import($p_value)
    {
        if (is_array($p_value))
        {
            if (isset($p_value[C__DATA__VALUE]) && $p_value['id'] > 0)
            {
                if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP]) && array_key_exists(
                        $p_value['id'],
                        $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP]
                    )
                )
                {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP][$p_value['id']];
                }
            }
        }

        return false;
    }

    public function check_period($p_id)
    {
        $l_dao_nagios_comp = new isys_component_dao_nagios($this->m_database);
        $l_timeperiod      = $l_dao_nagios_comp->getTimeperiodsAssoc();

        $l_arr = [
            'id'    => $p_id,
            'title' => $l_timeperiod[$p_id]
        ];

        return $l_arr;
    }

    public function check_period_import($p_value)
    {
        $l_table      = 'isys_nagios_timeperiods';
        $l_property   = 'check_period';
        $l_attributes = [
            'id',
            'name' => 'value'
        ];

        return $this->import($l_table, $l_property, $l_attributes);
    }

    public function notification_period($p_id)
    {
        $l_dao_nagios_comp = new isys_component_dao_nagios($this->m_database);
        $l_timeperiod      = $l_dao_nagios_comp->getTimeperiodsAssoc();
        $l_arr             = [
            'id'    => $p_id,
            'title' => $l_timeperiod[$p_id]
        ];

        return $l_arr;
    }

    public function notification_period_import($p_value)
    {
        $l_table      = 'isys_nagios_timeperiods';
        $l_property   = 'notification_period';
        $l_attributes = [
            'id',
            'name' => 'value'
        ];

        return $this->import($l_table, $l_property, $l_attributes);
    }

    /**
     * Export method for check commands.
     *
     * @param   integer $p_id
     *
     * @return  mixed
     */
    public function check_command($p_id)
    {
        $l_return = false;

        if ($p_id > 0)
        {
            $l_commands = isys_component_dao_nagios::instance($this->m_database)
                ->getCommandsAssoc();

            if (isset($l_commands[$p_id]))
            {
                $l_return = [
                    'id'    => $p_id,
                    'title' => $l_commands[$p_id]
                ];
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Import method for check commands.
     *
     * @param   array $p_data
     *
     * @return  mixed
     */
    public function check_command_import($p_data = [])
    {
        if (!isset($p_data['id']) || empty($p_data['id']))
        {
            return null;
        } // if

        return $this->import(
            'isys_nagios_commands',
            'check_command',
            [
                'id',
                'name' => 'value'
            ]
        );
    } // function

    /**
     * Export method for event handler.
     *
     * @param   integer $p_id
     *
     * @return  mixed
     */
    public function event_handler($p_id)
    {
        // This does the same as the "check_command" helper.
        return $this->check_command($p_id);
    } // function

    /**
     * Import method for event handler.
     *
     * @param   array $p_data
     *
     * @return  mixed
     */
    public function event_handler_import($p_data = [])
    {
        if (!isset($p_data['id']) || empty($p_data['id']))
        {
            return null;
        } // if

        return $this->import(
            'isys_nagios_commands',
            'event_handler',
            [
                'id',
                'name' => 'value'
            ]
        );
    } // function

    public function host_initial_state($p_id)
    {
        $l_dao_nagios_comp = new isys_component_dao_nagios($this->m_database);
        $l_state           = $l_dao_nagios_comp->getHostFlapDetectionOptionsAssoc();
        $l_arr             = [
            'id'    => $p_id,
            'title' => $l_state[$p_id]
        ];

        return $l_arr;
    }

    public function service_initial_state($p_id)
    {
        $l_dao_nagios_comp = new isys_component_dao_nagios($this->m_database);
        $l_state           = $l_dao_nagios_comp->getServiceFlapDetectionOptionsAssoc();
        $l_arr             = [
            'id'    => $p_id,
            'title' => $l_state[$p_id]
        ];

        return $l_arr;
    }

    public function host_initial_state_import()
    {
        $l_property = 'initial_state';
        if (!isset($this->m_property_data[$l_property]['id']))
        {
            return false;
        } //if
        $l_id      = $this->m_property_data[$l_property]['id'];
        $l_dao     = new isys_component_dao_nagios($this->m_database);
        $l_options = $l_dao->getHostFlapDetectionOptionsAssoc();
        if (!array_key_exists($l_id, $l_options))
        {
            return false;
        }

        return $l_id;
    }

    public function service_initial_state_import()
    {
        $l_property = 'initial_state';
        if (!isset($this->m_property_data[$l_property]['id']))
        {
            return false;
        } //if
        $l_id      = $this->m_property_data[$l_property]['id'];
        $l_dao     = new isys_component_dao_nagios($this->m_database);
        $l_options = $l_dao->getServiceFlapDetectionOptionsAssoc();
        if (!array_key_exists($l_id, $l_options))
        {
            return false;
        }

        return $l_id;
    }

    public function host_flap_detection_options($p_id)
    {
        $l_property = 'flap_detection_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getHostFlapDetectionOptionsAssoc();

        return $this->export_list($p_id, $l_property, $l_source);
    }

    public function host_flap_detection_options_import()
    {
        $l_property = 'flap_detection_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getHostFlapDetectionOptionsAssoc();

        return $this->import_list($l_property, $l_source);
    }

    public function service_flap_detection_options($p_id)
    {
        $l_property = 'flap_detection_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getServiceFlapDetectionOptionsAssoc();

        return $this->export_list($p_id, $l_property, $l_source);
    }

    public function service_flap_detection_options_import()
    {
        $l_property = 'flap_detection_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getServiceFlapDetectionOptionsAssoc();

        return $this->import_list($l_property, $l_source);
    }

    public function host_stalking_options($p_id)
    {
        $l_property = 'stalking_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getHostFlapDetectionOptionsAssoc();

        return $this->export_list($p_id, $l_property, $l_source);
    }

    public function host_stalking_options_import()
    {
        $l_property = 'stalking_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getHostFlapDetectionOptionsAssoc();

        return $this->import_list($l_property, $l_source);
    }

    public function service_stalking_options($p_id)
    {
        $l_property = 'stalking_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getServiceFlapDetectionOptionsAssoc();

        return $this->export_list($p_id, $l_property, $l_source);
    }

    public function service_stalking_options_import()
    {
        $l_property = 'stalking_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getServiceFlapDetectionOptionsAssoc();

        return $this->import_list($l_property, $l_source);
    }

    public function host_notification_options($p_id)
    {
        $l_property = 'host_notification_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getHostNotificationOptionsAssoc();

        return $this->export_list($p_id, $l_property, $l_source);
    }

    public function host_notification_options_import()
    {
        $l_property = 'host_notification_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getHostNotificationOptionsAssoc();

        return $this->import_list($l_property, $l_source);
    }

    public function service_notification_options($p_id)
    {
        $l_property = 'service_notification_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getServiceNotificationOptionsAssoc();

        return $this->export_list($p_id, $l_property, $l_source);
    }

    public function service_notification_options_import()
    {
        $l_property = 'service_notification_options';
        $l_dao      = new isys_component_dao_nagios($this->m_database);
        $l_source   = $l_dao->getServiceNotificationOptionsAssoc();

        return $this->import_list($l_property, $l_source);
    }

    public function host_escalations($p_id)
    {
        $p_id = trim($p_id);
        if (empty($p_id)) return true;

        return $this->getEscalations('host', $p_id);
    }

    public function host_escalations_import()
    {
        if (!is_array($this->m_property_data['escalations'][C__DATA__VALUE])) return $this->m_property_data['escalations'][C__DATA__VALUE];

        return $this->getEscalations_import('host');
    }

    public function service_escalations($p_id)
    {
        $p_id = trim($p_id);
        if (empty($p_id)) return true;

        return $this->getEscalations('service', $p_id);
    }

    public function service_escalations_import()
    {
        if (!is_array($this->m_property_data['escalations'][C__DATA__VALUE])) return $this->m_property_data['escalations'][C__DATA__VALUE];

        return $this->getEscalations_import('service');
    }

    public function getEscalations($p_type, $p_ids)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        if ($p_type == 'host')
        {
            $l_table = 'isys_nagios_host_escalations';
        }
        else
        {
            $l_table = 'isys_nagios_service_escalations';
        }

        $l_sql = 'SELECT * FROM ' . $l_table . ' WHERE ' . $l_table . '__id IN (' . $p_ids . ')';
        $l_res = $l_dao->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {
            $l_contact_id = $l_row[$l_table . '__isys_contact__id'];
            $l_value      = [
                'escalation_contacts'    => $this->contact($l_contact_id),
                'first_notification'     => [C__PROPERTY__INFO__TITLE => $l_row[$l_table . '__first_notification']],
                'last_notification'      => [C__PROPERTY__INFO__TITLE => $l_row[$l_table . '__last_notification']],
                'notification_interval'  => [C__PROPERTY__INFO__TITLE => $l_row[$l_table . '__notification_interval']],
                'escalation_period'      => $this->dialog_plus($l_row[$l_table . '__escalation_period'], 'isys_nagios_timeperiods'),
                'escalation_period_plus' => $this->dialog_plus($l_row[$l_table . '__escalation_period_plus'], 'isys_nagios_timeperiods_plus'),
                'escalation_options'     => [C__PROPERTY__INFO__TITLE => $l_row[$l_table . '__escalation_options']]
            ];

            if ($l_row[$l_table . '__escalation_period'] > 0)
            {
                unset($l_value['escalation_period_plus']);
            }
            if ($l_row[$l_table . '__escalation_period_plus'] > 0)
            {
                unset($l_value['escalation_period']);
            }

            $l_return[] = [
                'id'    => $l_row[$l_table . '__id'],
                'title' => $l_row[$l_table . '__title'],
                'value' => new isys_export_data($l_value)
            ];
        }

        return new isys_export_data($l_return);
    }

    public function getEscalations_import($p_type)
    {
        $l_dao         = new isys_component_dao_nagios($this->m_database);
        $l_dao_contact = new isys_contact_dao_reference($this->m_database);

        $l_data = $this->m_property_data['escalations'][C__DATA__VALUE];
        if (count($l_data) > 0)
        {

            if ($p_type == 'host')
            {
                $l_method = 'getHostEscalation';
                $l_table  = 'isys_nagios_host_escalations';
            }
            else
            {
                $l_method = 'getServiceEscalation';
                $l_table  = 'isys_nagios_service_escalations';
            }

            foreach ($l_data AS $l_content)
            {
                $l_id              = $this->dialog_plus_import($l_content['title'], $l_table);
                $l_attributes      = $l_content['value'];
                $l_escalation_data = $l_dao->$l_method($l_id);
                if (is_array($l_attributes))
                {
                    $l_update = 'UPDATE ' . $l_table . ' SET ';
                    foreach ($l_attributes AS $l_attribute_property)
                    {
                        $l_tag = $l_attribute_property['tag'];
                        switch ($l_tag)
                        {
                            case 'escalation_contacts':
                                if (empty($l_escalation_data[$l_table . '__isys_contact__id']))
                                {
                                    $l_dao_contact->save();
                                    $l_escalation_data[$l_table . '__isys_contact__id'] = $l_dao_contact->get_id();
                                }
                                $l_dao_contact->load($l_escalation_data[$l_table . '__isys_contact__id']);
                                $l_contacts = $l_attribute_property['value'];
                                if (count($l_contacts) > 0)
                                {
                                    foreach ($l_contacts AS $l_contact_import_data)
                                    {
                                        if (array_key_exists($l_contact_import_data['id'], $this->m_object_ids))
                                        {
                                            $l_dao_contact->insert_data_item($this->m_object_ids[$l_contact_import_data['id']]);
                                        }
                                    }
                                    $l_dao_contact->save($l_escalation_data[$l_table . '__isys_contact__id']);
                                }
                                $l_update .= $l_table . '__isys_contact__id = ' . $l_dao->convert_sql_id($l_escalation_data[$l_table . '__isys_contact__id']) . ', ';
                                break;
                            case 'sub_escalation_period_plus':
                                $l_period_id = $this->dialog_plus_import($l_attribute_property['title'], 'isys_nagios_timeperiods_plus');
                                $l_update .= $l_table . '__escalation_period_plus = ' . $l_dao->convert_sql_id($l_period_id) . ', ';
                                break;
                            case 'sub_escalation_period':
                                $l_period_id = $this->dialog_plus_import($l_attribute_property['title'], 'isys_nagios_timeperiods');
                                $l_update .= $l_table . '__escalation_period = ' . $l_dao->convert_sql_id($l_period_id) . ', ';
                                break;
                            default:
                                $l_field = $l_table . '__' . substr($l_tag, strpos($l_tag, '_') + 1, strlen($l_tag));
                                $l_update .= $l_field . ' = \'' . $l_attribute_property['value'] . '\', ';
                                break;
                        }
                    }
                    $l_update = rtrim($l_update, ', ') . ' WHERE ' . $l_table . '__id = ' . $l_dao->convert_sql_id($l_id);
                    if (!$l_dao->update($l_update) || !$l_dao->apply_update())
                    {
                        return false;
                    }
                }
                $l_ids[] = $l_id;
            }
        }

        return implode(',', $l_ids);
    } // function

    /**
     * This method exports the property nagios host template from global category nagios host.
     *
     * @param   string $p_value
     *
     * @return  mixed  May be an array or isys_export_data
     */
    public function host_template($p_value)
    {
        $l_dao    = new isys_cmdb_dao($this->m_database);
        $l_return = [];

        if (isys_format_json::is_json_array($p_value))
        {
            $p_value = isys_format_json::decode($p_value);

            foreach ($p_value as $l_obj_id)
            {
                $l_data = $l_dao->get_object_by_id($l_obj_id)
                    ->get_row();

                if (is_array($l_data))
                {
                    $l_return[] = [
                        'id'    => $l_data['isys_obj__id'],
                        'type'  => $l_data['isys_obj_type__const'],
                        'sysid' => $l_data['isys_obj__sysid'],
                        'title' => $l_data['isys_obj__title']
                    ];
                } // if
            } // foreach

            $l_return = new isys_export_data($l_return);
        } // if

        return $l_return;
    } // function

    /**
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function host_template_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE]) && $p_value['id'] > 0)
        {
            if (array_key_exists($p_value['id'], $this->m_object_ids))
            {
                return $this->m_object_ids[$p_value['id']];
            } // if
        } // if
        return null;
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  isys_export_data
     */
    public function host_notification_commands($p_id)
    {
        return $this->export_list(
            $p_id,
            'host_notification_commands',
            isys_component_dao_nagios::instance($this->m_database)
                ->getCommandsAssoc()
        );
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  isys_export_data
     */
    public function service_notification_commands($p_id)
    {
        return $this->export_list(
            $p_id,
            'service_notification_commands',
            isys_component_dao_nagios::instance($this->m_database)
                ->getCommandsAssoc()
        );
    } // function

    /**
     *
     * @return  mixed
     */
    public function host_notification_commands_import()
    {
        return $this->import_list(
            'host_notification_commands',
            isys_component_dao_nagios::instance($this->m_database)
                ->getCommandsAssoc()
        );
    } // function

    /**
     *
     * @return  mixed
     */
    public function service_notification_commands_import()
    {
        return $this->import_list(
            'service_notification_commands',
            isys_component_dao_nagios::instance($this->m_database)
                ->getCommandsAssoc()
        );
    } // function
} // class