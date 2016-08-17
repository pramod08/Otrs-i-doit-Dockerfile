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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_ajax_handler_jdisc extends isys_ajax_handler
{
    /**
     * This variable is attached to the stats message
     *
     * @var string
     */
    public static $m_additional_stats = '';

    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        switch ($_GET['func'])
        {
            case 'import':
                $this->process_import();
                break;

            case 'check_connection':
                $this->check_connection();
                break;

            case 'check_version':
                $this->check_version();
                break;
            case 'read_hostaddress_from_file':
                $this->read_hostaddress_from_file();
                break;
            case 'get_groups_and_profiles':
                $this->get_groups_and_profiles();
                break;
            case 'get_profile_data':
                $this->get_profile_data();
                break;
            case 'location_browser':
                global $g_comp_template;
                $l_obj    = new isys_popup_browser_location();
                $l_params = [
                    'p_strClass'        => 'small jdisc_location',
                    'p_strPopupType'    => 'browser_location',
                    'containers_only'   => true,
                    'name'              => $_POST['field'],
                    'p_strSelectedID'   => $_POST['selected_object'],
                    'p_bInfoIconSpacer' => 0
                ];
                echo $l_obj->handle_smarty_include($g_comp_template, $l_params);
                break;
            case 'discover_devices':
                $this->discover_devices();
                break;
            case 'discover':
                $this->discover($_GET['type']);
                break;
            case 'get_discovery_jobs':
                $this->get_discovery_jobs();
                break;
            case 'check_connection_discovery':
                $this->check_connection_discovery();
                break;
        } // switch
        // End the request.
        $this->_die();
    } // function

    /**
     * Checks which JDisc version is currently running
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function check_version()
    {
        header('Content-Type: application/json');
        $l_return = [];

        try
        {
            $l_module            = isys_module_jdisc::factory();
            $l_jdisc_server_id   = $_POST['jdisc_server'];
            $l_version_check     = $l_module->check_version($l_jdisc_server_id);
            $l_current_version   = $l_module->get_version($l_jdisc_server_id);
            $l_supported_version = $l_module::C__MODULE__JDISC__VERSION;

            if ($l_version_check === false)
            {
                $l_return['version_check'] = true;
                $l_return['message']       = '';
            }
            else
            {
                if ($l_current_version > $l_supported_version)
                {
                    $l_return['version_check'] = true;
                    $l_return['message']       = '';
                }
                else
                {
                    $l_return['version_check'] = false;
                    $l_return['message']       = _L(
                        'LC__MODULE__JDISC__VERSION_CHECK_FAILED',
                        [
                            $l_current_version,
                            $l_supported_version
                        ]
                    );
                }
            }
        }
        catch (Exception $e)
        {
            $l_return['version_check'] = false;
            $l_return['message']       = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Method for checking the connection toward JDisc.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function check_connection()
    {
        header('Content-Type: application/json');
        $l_return = [];

        try
        {
            isys_module_jdisc::factory()
                ->get_connection($_POST['jdisc_server']);

            $l_return['connection'] = true;
            $l_return['message']    = _L('LC__MODULE__JDISC__CONNECTION_SUCCESS');
        }
        catch (Exception $e)
        {
            $l_return['connection'] = false;
            $l_return['message']    = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Method for processing the JDisc import.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function process_import()
    {
        global $g_comp_database;

        $l_return            = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];
        $l_show_logging_link = false;

        /* Set memory limit */
        if (($l_memlimit = isys_settings::get('system.memory-limit.jdisc', '2G')))
        {
            ini_set('memory_limit', $l_memlimit);
        }

        // Close session to enable another request while importing.
        isys_application::instance()->session->write_close();

        // Start output buffering, because of the log.
        ob_start();

        if (isset($_POST['detailed-logging']) && $_POST['detailed-logging'] > 0)
        {
            switch ($_POST['detailed-logging'])
            {
                case '2':
                    $l_loglevel = isys_log::C__ALL;
                    break;
                default:
                case '1':
                    $l_loglevel = isys_log::C__ALL & ~isys_log::C__DEBUG;
                    break;
            }

            // Creating a log-instance
            $l_log = isys_factory_log::get_instance('import_jdisc')
                ->set_verbose_level($l_loglevel)
                ->set_log_level($l_loglevel);
            unset($l_loglevel);
            $l_show_logging_link = true;
        }
        else
        {
            $l_log = isys_log_null::get_instance();
        }

        // Groups are optional, profiles not.
        $l_group                   = ($_POST['group'] > 0) ? (int) $_POST['group'] : null;
        $l_profile                 = (int) $_POST['profile'];
        $l_category_mode           = null;
        $l_clear_identifiers       = false;
        $l_clear_single_identifier = false;
        if (isset($_POST['regenerate-index']))
        {
            $l_regenerate_search_index = (bool) $_POST['regenerate-index'];
        }
        else
        {
            $l_regenerate_search_index = true;
        }

        if (strpos($_POST['mode'], '_'))
        {
            list($l_mode, $l_category_mode) = explode('_', $_POST['mode']);
            $l_mode = (int) $l_mode;

            $l_category_mode = ($l_category_mode !== '') ? (int) $l_category_mode : null;

            if ($l_category_mode === null)
            {
                // Clear JDisc identifiers
                $l_clear_identifiers = true;
            } // if
        }
        else
        {
            $l_mode = (int) $_POST['mode'];
        } // if

        switch ($l_mode)
        {
            default:
            case 1:
                $l_mode = isys_import_handler_cmdb::C__APPEND;
                break;

            case 2:
                $l_mode = isys_import_handler_cmdb::C__MERGE;
                break;
        } // switch

        // Prepare the import-array.
        $l_jdisc = isys_module_jdisc::factory()
            ->set_mode($l_mode)
            ->set_clear_mode($l_category_mode);

        // Set database
        if (!$l_jdisc->switch_database($_POST['jdisc_server']))
        {
            $l_return['success'] = false;
            $l_return['message'] = _L('LC__MODULE__JDISC__IMPORT__JDISC_SERVER__ERROR_MSG');
        }
        else
        {
            /**
             * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
             * This is extremely important!
             *
             * An Index is done for all objects at the end of the request, if enabled via checkbox.
             */
            idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
                ->disconnectOnAfterCategoryEntrySave();

            $l_log->info('Starting jdisc import..');

            $l_jdisc->prepare_environment($l_profile);

            if (!empty($_POST['filter_type']))
            {
                $l_jdisc->prepare_filter($_POST['filter_type'], $_POST['filter_data']);
            } // if

            try
            {
                // Getting the PDO.
                $l_pdo = $l_jdisc->get_connection();

                // Retrieve the result set for the objects to be imported.
                $l_obj_res = $l_jdisc->retrieve_object_result($l_group, $l_profile);

                if ($l_obj_res)
                {
                    /* Identify import amount */
                    $l_import_count = $l_pdo->num_rows($l_obj_res);
                    if ($l_import_count > 0)
                    {
                        $l_identifiers_arr = $l_device_arr = [];

                        $l_group_name = null;
                        while ($l_obj_row = $l_pdo->fetch_row_assoc($l_obj_res))
                        {
                            if ($l_obj_row['group_name'] && $l_group_name === null)
                            {
                                $l_group_name = $l_obj_row['group_name'];
                            } // if

                            if ($l_clear_identifiers === false)
                            {
                                $l_identifier = $l_jdisc->get_identifier_dao()
                                    ->get_identifier_by_key_value(
                                        C__CATG__IDENTIFIER_TYPE__JDISC,
                                        'deviceid-' . $_POST['jdisc_server'],
                                        $l_obj_row['id']
                                    );
                                if ($l_identifier)
                                {
                                    $l_obj_row['identifierObjID']        = substr($l_identifier, 0, strpos($l_identifier, '_'));
                                    $l_obj_row['identifierID']           = substr($l_identifier, strpos($l_identifier, '_') + 1);
                                    $l_identifiers_arr[$l_obj_row['id']] = $l_obj_row['identifierObjID'];
                                } // if
                            }
                            else
                            {
                                $l_obj_row['identifierObjID'] = null;
                                $l_obj_row['identifierID']    = null;
                            } // if
                            $l_device_arr[] = $l_obj_row;
                        } // while

                        // Clear all JDisc identifiers if mode has been selected
                        if ($l_clear_identifiers === true)
                        {
                            if (empty($_POST['filter_data']))
                            {
                                $l_jdisc->get_identifier_dao()
                                    ->clear_identifiers(
                                        C__CATG__IDENTIFIER_TYPE__JDISC,
                                        'deviceid-' . $_POST['jdisc_server'],
                                        null,
                                        $l_group_name
                                    );
                            }
                            else
                            {
                                $l_clear_single_identifier = true;
                            } // if
                        } // if

                        if (!empty($l_identifiers_arr))
                        {
                            $l_jdisc->get_identifier_dao()
                                ->set_mapping($l_identifiers_arr);
                        } // if

                        $l_import_dao = new isys_module_dao_import_log($g_comp_database);
                        isys_event_manager::getInstance()
                            ->set_import_id(
                                $l_import_dao->add_import_entry(
                                    $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__JDISC'),
                                    'JDisc Import (' . $l_import_count . ' device(s))'
                                )
                            );
                        // Create an instance of the CMDB import
                        $l_import = new isys_import_handler_cmdb($l_log, $g_comp_database, isys_cmdb_dao_jdisc::factory($g_comp_database));
                        $l_import->set_empty_fields_mode(isys_import_handler_cmdb::C__KEEP);
                        $l_import->set_overwrite_ip_conflicts((bool) $_POST['overwrite_hostaddress']);
                        $l_import->set_general_header('JDisc');
                        $l_import->set_logbook_source(C__LOGBOOK_SOURCE__JDISC);
                        /**
                         * Create statistics
                         */
                        $l_stats = [
                            'max_obj'              => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_obj;')
                                ->get_row_value('cnt'),
                            'max_cluster_members'  => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_cluster_members_list;')
                                ->get_row_value('cnt'),
                            'max_blades'           => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_cats_chassis_list;')
                                ->get_row_value('cnt'),
                            'max_port_connections' => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_cable_connection;')
                                ->get_row_value('cnt'),
                            'max_ports'            => $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_port_list;')
                                    ->get_row_value('cnt') + $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_log_port_list;')
                                    ->get_row_value('cnt'),
                            'max_vlan'             => $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_cats_layer2_net_assigned_ports_list;')
                                    ->get_row_value('cnt') + $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_log_port_list_2_isys_obj;')
                                    ->get_row_value('cnt'),
                            'max_sw'               => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_application_list;')
                                ->get_row_value('cnt'),
                            'start_time'           => microtime(true)
                        ];
                        // Matching from JDisc device id to i-doit object id:
                        $l_jdisc_to_idoit = [];
                        // Cached object identifiers:
                        $l_object_ids     = [];
                        $l_arr_device_ids = [];
                        $l_log->flush_log();
                        /**
                         * Disable autocommits
                         */
                        $l_jdisc->get_network_dao()
                            ->get_database_component()
                            ->set_autocommit(false);
                        $l_not_defined_types = [];
                        foreach ($l_device_arr AS $l_obj_row)
                        {
                            if (!isset($l_obj_row['idoit_obj_type']) || $l_obj_row['idoit_obj_type'] === null)
                            {
                                if (!isset($l_not_defined_types[$l_obj_row['type_name']]))
                                {
                                    $l_log->debug(
                                        'JDisc type "' . $l_obj_row['type_name'] . '" is not properly defined in the profile. Skipping devices with JDisc type "' . $l_obj_row['type_name'] . '".'
                                    );
                                } // if
                                $l_not_defined_types[$l_obj_row['type_name']]++;
                                continue;
                            } // if
                            if (in_array($l_obj_row['deviceid'], $l_arr_device_ids)) continue;
                            $l_arr_device_ids[] = $l_obj_row['deviceid'];

                            // Clear only the identifier with the specified deviceid
                            if ($l_clear_single_identifier)
                            {
                                $l_jdisc->get_identifier_dao()
                                    ->clear_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $_POST['jdisc_server'], $l_obj_row['deviceid'], $l_group_name);
                            } // if

                            $l_prepared_data = $l_jdisc->prepare_object_data(
                                $l_obj_row,
                                $l_jdisc_to_idoit,
                                $l_object_ids
                            );
                            if ($l_prepared_data === false)
                            {
                                // Skip this device
                                $l_import_count--;
                                continue;
                            }

                            $l_log->info('Importing "' . $l_obj_row['name'] . '" (' . $l_obj_row['type_name'] . ') with ID #' . $l_jdisc_to_idoit[$l_obj_row['id']]);
                            $l_log->debug('JDisc device ID: #' . $l_obj_row['id'] . ' | i-doit object ID #' . $l_jdisc_to_idoit[$l_obj_row['id']]);
                            // Prepare and import the data.
                            $l_import->reset()
                                ->set_scantime()
                                ->set_prepared_data($l_prepared_data['object'])
                                ->set_connection_info($l_prepared_data['connections'])
                                ->set_mode($l_mode)
                                ->set_object_created_by_others(true)
                                ->set_object_ids($l_object_ids)
                                ->set_logbook_entries(
                                    isys_jdisc_dao_data::get_logbook_entries()
                                )
                                ->import();
                            // Get the object id of the created object:
                            $l_last_object_id                   = $l_import::get_stored_objectID();
                            $l_jdisc_to_idoit[$l_obj_row['id']] = $l_last_object_id;
                            $l_jdisc->get_device_dao()
                                ->set_jdisc_to_idoit_objects($l_obj_row['id'], $l_last_object_id);
                            $l_object_ids[$l_last_object_id] = $l_last_object_id;
                            $l_log->flush_log(true, false);

                            /* Update CMDB Status */
                            if (isset($l_prepared_data['object'][$l_last_object_id]['cmdb_status']) && $l_prepared_data['object'][$l_last_object_id]['cmdb_status'] > 0)
                            {
                                $l_jdisc->get_identifier_dao()
                                    ->set_object_cmdb_status($l_last_object_id, $l_prepared_data['object'][$l_last_object_id]['cmdb_status']);
                            }

                            unset($l_prepared_data);
                        } // while
                        $l_import->set_overwrite_ip_conflicts(false);
                        unset($l_import);
                        $l_log->debug('Starting the final step of the import: Referencing the data.');

                        $l_jdisc->get_cluster_dao()
                            ->assign_clusters(
                                $l_jdisc_to_idoit,
                                $l_jdisc->get_network_dao()
                                    ->get_vrrp_addresses()
                            );
                        $l_jdisc->get_cluster_dao()
                            ->update_cluster_members($l_jdisc_to_idoit);
                        $l_jdisc->get_device_dao()
                            ->create_blade_connections($l_jdisc_to_idoit);
                        $l_jdisc->get_device_dao()
                            ->create_module_connections(
                                $l_jdisc_to_idoit,
                                $l_jdisc->get_network_dao()
                                    ->get_import_type_interfaces()
                            );

                        // iterate through each device to prevent a memory leak
                        foreach ($l_jdisc_to_idoit AS $l_device_id => $l_object_id)
                        {
                            // Load cache from db
                            $l_cache_network_loaded  = $l_jdisc->get_network_dao()
                                ->load_cache($l_object_id);
                            $l_cache_software_loaded = $l_jdisc->get_software_dao()
                                ->load_cache($l_object_id);

                            if ($l_cache_network_loaded || $l_cache_software_loaded)
                            {
                                // Create net listeners
                                $l_jdisc->get_software_dao()
                                    ->create_net_listener_connections($l_object_id, $l_device_id);
                                // Create port connections
                                $l_jdisc->get_network_dao()
                                    ->create_port_connections();
                                // Update ip port assignment
                                $l_jdisc->get_network_dao()
                                    ->update_ip_port_assignments();
                                // Create port map
                                $l_jdisc->get_network_dao()
                                    ->create_port_map($l_object_id);
                                // Create network interface connections
                                $l_jdisc->get_network_dao()
                                    ->create_network_interface_connections($l_object_id);
                                // This function takes more time than the others
                                $l_jdisc->get_network_dao()
                                    ->update_vlan_assignments($l_object_id, $l_device_id);
                            } // if
                        } // foreach

                        $l_jdisc->get_identifier_dao()
                            ->recover_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $_POST['jdisc_server']);
                        // Remove temporary table
                        $l_jdisc->get_network_dao()
                            ->drop_cache_table();

                        /**
                         * Commit all queries
                         */
                        $l_jdisc->get_network_dao()
                            ->get_database_component()
                            ->commit();
                        $l_log->info('Finished!');

                        // Regenerate Search index

                        if ($l_regenerate_search_index)
                        {
                            $l_affected_categories = $l_jdisc->get_cached_profile()['categories'];
                            // Only regenerate index if there were any affected categories
                            if(is_array($l_affected_categories) && count($l_affected_categories))
                            {
                                $l_log->info('Regenerating search index..');

                                $startTimeIndexCreation = microtime(true);

                                /* Adding additional categories*/
                                $l_categories   = array_keys($l_affected_categories);
                                $l_categories[] = C__CMDB__SUBCAT__NETWORK_INTERFACE_L;
                                $l_categories[] = C__CATG__JDISC_CA;

                                \idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
                                    ->onPostImport(
                                        $l_stats['start_time'],
                                        $l_categories,
                                        [
                                            C__CATS__SERVICE,
                                            C__CATS__APPLICATION,
                                            C__CATS__DATABASE_SCHEMA,
                                            C__CATS__DBMS,
                                            C__CATS__DATABASE_INSTANCE,
                                            C__CATS__ACCESS_POINT,
                                            C__CATS__NET
                                        ]
                                    );

                                $indexCreationBenchmark = "Index creation took " . number_format(microtime(true) - $startTimeIndexCreation, 2) . " secs.\n";
                            } // if
                        } // if
                        else $indexCreationBenchmark = '';

                        /**
                         * Update statistics
                         */
                        $l_stats_update        = [
                            'max_obj'              => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_obj;')
                                ->get_row_value('cnt'),
                            'max_cluster_members'  => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_cluster_members_list;')
                                ->get_row_value('cnt'),
                            'max_port_connections' => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_cable_connection;')
                                ->get_row_value('cnt'),
                            'max_blades'           => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_cats_chassis_list;')
                                ->get_row_value('cnt'),
                            'max_ports'            => $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_port_list;')
                                    ->get_row_value('cnt') + $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_log_port_list;')
                                    ->get_row_value('cnt'),
                            'max_vlan'             => $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_cats_layer2_net_assigned_ports_list;')
                                    ->get_row_value('cnt') + $l_jdisc->get_network_dao()
                                    ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_log_port_list_2_isys_obj;')
                                    ->get_row_value('cnt'),
                            'max_sw'               => $l_jdisc->get_network_dao()
                                ->retrieve('SELECT COUNT(*) AS cnt FROM isys_catg_application_list;')
                                ->get_row_value('cnt'),
                        ];
                        $l_sum_created_objects = $l_stats_update['max_obj'] - $l_stats['max_obj'];
                        $l_vlan_stats          = $l_stats_update['max_vlan'] - $l_stats['max_vlan'];
                        $l_message             = "Stats: \n\n" . ($l_sum_created_objects) . " objects created. \n" . ($l_stats_update['max_ports'] - $l_stats['max_ports']) . " network ports created. \n" . ($l_stats_update['max_port_connections'] - $l_stats['max_port_connections']) . " network ports connected to each other. \n" . ($l_vlan_stats < 0 ? '0' : $l_vlan_stats) . " vlans assigned. \n" . ($l_vlan_stats < 0 ? ($l_vlan_stats * -1) . " vlans detached. \n" : '') . ($l_stats_update['max_cluster_members'] - $l_stats['max_cluster_members']) . " cluster members attached. \n" . ($l_stats_update['max_blades'] - $l_stats['max_blades']) . " blade servers attached. \n" . ($l_stats_update['max_sw'] - $l_stats['max_sw']) . " software objects assigned. \n\n" . (self::$m_additional_stats ? self::$m_additional_stats . "\n" : '') . $indexCreationBenchmark . "Complete process took " . isys_glob_seconds_to_human_readable(
                                (int) (microtime(true) - $l_stats['start_time'])
                            ) . ".\n" . "Memory peak usage: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2, '.', '') . ' MB';

                        $l_log->info($l_message);
                        global $g_absdir, $g_config;
                        if ($l_show_logging_link)
                        {
                            $l_log_link = (str_replace($g_absdir, $g_config['www_dir'], $l_log->get_log_file()));
                            if (strpos($l_log_link, $g_config['www_dir']) === false)
                            {
                                $l_log_link = $g_config['www_dir'] . $l_log_link;
                            } // if
                            $l_message .= "\n\n" . '<a href="' . $l_log_link . '" class="bold" target="_new">' . _L('LC__MODULE__JDISC__IMPORT__SHOW_LOG') . '</a>';
                        } // if
                        $l_not_imported_devices        = (count($l_not_defined_types)) ? ' ' . array_sum(
                                $l_not_defined_types
                            ) . ' device(s) could not be imported/updated because of their missing object type.' : '';
                        $l_return['data']['stats']     = nl2br(
                            "Done: $l_import_count device(s) imported/updated." . $l_not_imported_devices . "\n\n" . $l_message
                        ); // attach stats to log output
                        $l_return['data']['log_icons'] = isys_log::get_log_icons();
                        ob_end_clean();
                        $l_log->flush_log(true, false);
                    }
                    else
                    {
                        $l_return['success'] = false;
                        $l_return['message'] = _L('LC__UNIVERSAL__NO_OBJECTS_IMPORTED');
                    } // if
                }
                else
                {
                    $l_return['success'] = false;
                    $l_return['message'] = _L('LC__MODULE__JDISC__ERROR_OBJECTTYPES_NOT_DEFINED_OR_ACTIVATED');
                } // if
            }
            catch (Exception $e)
            {
                $l_return['success'] = false;
                $l_return['message'] = $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine();
            } // try
        } // if
        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Reads text file which contains all host addresses which will be considered for the import
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function read_hostaddress_from_file()
    {
        global $g_dirs;

        $l_uploader = new isys_library_fileupload;

        $l_filename    = $l_uploader->getName();
        $l_file_prefix = 'jdisc_';
        $l_upload_dir  = realpath($g_dirs["fileman"]["target_dir"]) . DS;

        $l_result = $l_uploader->set_prefix($l_file_prefix)
            ->handleUpload($l_upload_dir);

        if ($l_result['success'] === true)
        {
            if (file_exists($l_upload_dir . $l_file_prefix . $l_filename))
            {
                $l_data         = [];
                $l_file_handler = fopen($l_upload_dir . $l_file_prefix . $l_filename, 'r');
                while ($l_ip = fgets($l_file_handler))
                {
                    $l_ip = trim($l_ip);
                    if ($l_ip != '')
                    {
                        $l_data[] = $l_ip;
                    }
                } // while
                fclose($l_file_handler);
                unlink($l_upload_dir . $l_file_prefix . $l_filename);

                $l_return = [
                    'success' => true,
                    'message' => null,
                    'data'    => $l_data
                ];
            }
            else
            {
                $l_return = [
                    'success' => false,
                    'message' => 'Failed to open file.',
                    'data'    => null
                ];
            } // if
        }
        else
        {
            $l_return = [
                'success' => false,
                'message' => 'Failed to open file.',
                'data'    => null
            ];
        } // if
        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Retrieves all necessary info for the profile from the currently selected database
     */
    protected function get_profile_data()
    {
        header('Content-Type: application/json');
        $l_return = [
            'data'    => null,
            'success' => false
        ];

        try
        {
            /**
             * @var $l_module isys_module_jdisc
             */
            $l_module = new isys_module_jdisc();

            if ($l_module->switch_database($_POST['jdisc_server']))
            {
                // JDisc operating systems.
                $l_jdisc_operating_systems = [];

                $l_entities = $l_module->get_jdisc_operating_systems();

                foreach ($l_entities as $l_entity)
                {
                    $l_value = $l_entity['osversion'];

                    if (!empty($l_entity['osfamily']))
                    {
                        $l_value .= ' (' . $l_entity['osfamily'] . ')';
                    } // if

                    $l_jdisc_operating_systems[$l_entity['id']] = $l_value;
                } // foreach

                $l_jdisc_operating_systems = array_unique($l_jdisc_operating_systems);
                $l_options_counters        = $l_module->get_count_for_options();

                $l_return['success'] = true;
                $l_return['message'] = '';
                $l_return['data']    = [
                    'operating_systems' => $l_jdisc_operating_systems,
                    'options_counters'  => $l_options_counters
                ];

            }
            else
            {
                $l_return['message'] = _L('LC__MODULE__JDISC__IMPORT__JDISC_SERVER__ERROR_MSG');
            } // if
        }
        catch (Exception $e)
        {
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Retrieves the groups and profiles for the selected jdisc server
     */
    protected function get_groups_and_profiles()
    {
        header('Content-Type: application/json');
        $l_return = [
            'data'    => null,
            'success' => false
        ];

        try
        {
            /**
             * @var $l_module isys_module_jdisc
             */
            $l_module = new isys_module_jdisc();

            if ($l_module->switch_database($_POST['jdisc_server']))
            {
                $l_return = [
                    'success' => true,
                    'data'    => [
                        'default_profile' => null
                    ]
                ];
                if ($l_module->is_jedi())
                {
                    $l_return['data']['groups'] = false;
                }
                else
                {
                    $l_groups = $l_module->get_jdisc_groups();
                    if (count($l_groups) > 0)
                    {
                        foreach ($l_groups AS $l_group)
                        {
                            $l_return['data']['groups'][$l_group['id']] = $l_group['name'];
                        } // foreach
                        asort($l_return['data']['groups']);
                    }
                    else
                    {
                        $l_return['data']['groups'] = [];
                    } // if
                }
                $l_jdisc_server   = $l_module->get_jdisc_servers($_POST['jdisc_server'])
                    ->get_row();
                $l_default_server = ($l_jdisc_server['isys_jdisc_db__default_server'] == 1) ? true : false;
                $l_profiles       = $l_module->get_jdisc_profiles($_POST['jdisc_server'], $l_default_server);

                if ($_POST['check_web_service'])
                {
                    $l_return['data']['web_service_active'] = $l_module->web_service_active($_POST['jdisc_server']);
                } // if

                if (isset($_POST['object_type']))
                {
                    global $g_comp_database;
                    $l_return['data']['default_profile'] = isys_cmdb_dao::instance($g_comp_database)
                        ->get_object_types($_POST['object_type'])
                        ->get_row_value('isys_obj_type__isys_jdisc_profile__id');
                } // if

                if (count($l_profiles) > 0)
                {
                    foreach ($l_profiles AS $l_profile)
                    {
                        $l_return['data']['profiles'][$l_profile['id']] = $l_profile['title'];
                    } // foreach

                    asort($l_return['data']['profiles']);
                }
                else
                {
                    $l_return['data']['profiles'] = [];
                } // if
            }
            else
            {
                $l_return['message'] = _L('LC__MODULE__JDISC__IMPORT__JDISC_SERVER__ERROR_MSG');
            } // if
        }
        catch (Exception $e)
        {
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Trigger the discovery with the selected job
     */
    protected function discover_devices()
    {
        header('Content-Type: application/json');
        $l_module       = isys_module_jdisc::factory();
        $l_jdisc_server = $l_module->get_jdisc_discovery_data($_POST['host'])
            ->get_row();
        $l_jdisc_job    = isys_format_json::decode($_POST['job']);
        $l_host         = $l_jdisc_server['isys_jdisc_db__host'];
        $l_username     = $l_jdisc_server['isys_jdisc_db__discovery_username'];
        $l_password     = isys_helper_crypt::decrypt($l_jdisc_server['isys_jdisc_db__discovery_password'], 'C__MODULE__JDISC');
        $l_port         = $l_jdisc_server['isys_jdisc_db__discovery_port'];
        $l_protocol     = $l_jdisc_server['isys_jdisc_db__discovery_protocol'];

        $l_return = [
            'message' => '',
            'success' => false,
            'data'    => []
        ];

        $l_discovery_obj = isys_jdisc_dao_discovery::get_instance();
        try
        {
            $l_return['success'] = $l_discovery_obj->connect($l_host, $l_username, $l_password, $l_port, $l_protocol)
                ->set_discovery_job($l_jdisc_job)
                ->start_discovery_job();
            if ($l_return['success'])
            {
                $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__JOBS__SUCCESS');
            }
            else
            {
                $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__JOBS__FAILED');
            }
        }
        catch (Exception $e)
        {
            $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__JOBS__NO_CONNECTION');
        } // try
        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Gets all JDisc discovery jobs from the Host
     */
    protected function get_discovery_jobs()
    {
        header('Content-Type: application/json');
        $l_module = isys_module_jdisc::factory();

        $l_jdisc_server = $l_module->get_jdisc_discovery_data($_POST['host'])
            ->get_row();
        $l_host         = $l_jdisc_server['isys_jdisc_db__host'];

        $l_username = $l_jdisc_server['isys_jdisc_db__discovery_username'];
        $l_password = isys_helper_crypt::decrypt($l_jdisc_server['isys_jdisc_db__discovery_password'], 'C__MODULE__JDISC');
        $l_port     = $l_jdisc_server['isys_jdisc_db__discovery_port'];
        $l_protocol = $l_jdisc_server['isys_jdisc_db__discovery_protocol'];
        $l_return   = [
            'message' => '',
            'success' => false,
            'data'    => []
        ];

        $l_discovery_obj = isys_jdisc_dao_discovery::get_instance();
        try
        {
            $l_discovery_jobs = $l_discovery_obj->connect($l_host, $l_username, $l_password, $l_port, $l_protocol)
                ->get_discovery_jobs();
            $l_discovery_obj->disconnect();
            if (is_array($l_discovery_jobs))
            {
                if (count($l_discovery_jobs) > 0)
                {
                    $l_return['success'] = true;
                    $l_return['data']    = $l_discovery_jobs;
                }
                else
                {
                    $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__JOBS__NO_DISCOVERY_JOBS');
                } // if
            } // if
        }
        catch (Exception $e)
        {
            $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__JOBS__NO_CONNECTION');
        } // try
        echo isys_format_json::encode($l_return);
    } // function

    /**
     * Method for checking the connection toward the JDisc Web Service.
     */
    protected function check_connection_discovery()
    {
        header('Content-Type: application/json');
        $l_return = [
            'message' => _L('LC__MODULE__JDISC__DISCOVERY__CONNECTION_FAILED'),
            'success' => false,
        ];
        if (isys_module_jdisc::factory()
            ->web_service_active($_POST['jdisc_server'])
        )
        {
            $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__CONNECTION_SUCCESS');
            $l_return['success'] = true;
        } // if
        echo isys_format_json::encode($l_return);
    } // function

    protected function discover($p_type)
    {
        header('Content-Type: application/json');

        $l_target = null;
        $l_return = [
            'message' => '',
            'success' => false,
            'data'    => []
        ];

        $l_ignored_addresses = [
            '0.0.0.0',
            '0',
            '127.0.0.1'
        ];

        if ($_POST['hostname'] != '' && $_POST['hostname'] != '-')
        {
            $l_target = $_POST['hostname'];
        }
        elseif ($_POST['hostaddress'] != '' && !in_array($_POST['hostaddress'], $l_ignored_addresses))
        {
            $l_target = $_POST['hostaddress'];
        } // if

        if ($l_target === null)
        {
            $l_return['message'] = 'No hostaddress and hostname are set.';
        }
        else
        {
            $l_module       = isys_module_jdisc::factory();
            $l_jdisc_server = $l_module->get_jdisc_discovery_data($_POST['host'])
                ->get_row();
            $l_host         = $l_jdisc_server['isys_jdisc_db__host'];
            $l_username     = $l_jdisc_server['isys_jdisc_db__discovery_username'];
            $l_password     = isys_helper_crypt::decrypt($l_jdisc_server['isys_jdisc_db__discovery_password'], 'C__MODULE__JDISC');;
            $l_port     = $l_jdisc_server['isys_jdisc_db__discovery_port'];
            $l_protocol = $l_jdisc_server['isys_jdisc_db__discovery_protocol'];

            if (method_exists($this, $p_type))
            {
                try
                {
                    $l_discovery_obj = isys_jdisc_dao_discovery::get_instance()
                        ->connect($l_host, $l_username, $l_password, $l_port, $l_protocol)
                        ->set_target($l_target);

                    $l_return = $this->$p_type($l_discovery_obj);
                }
                catch (Exception $e)
                {
                    $l_return['message'] = _L('LC__MODULE__JDISC__DISCOVERY__JOBS__NO_CONNECTION');
                } // try
            } // if
        } // if
        echo isys_format_json::encode($l_return);
    }

    /**
     * Helper method which outputs the message
     *
     * @param string $p_message
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function output_message($p_message = '')
    {
        if ($p_message != '')
        {
            echo $p_message;
            ob_flush();
            flush();
            sleep(1);
        } // if
        return $p_message;
    } // function

    /**
     * Discover a specific device
     *
     * @param isys_jdisc_dao_discovery $p_discovery_obj
     *
     * @return mixed
     * @throws Exception
     */
    private function discover_device(isys_jdisc_dao_discovery $p_discovery_obj)
    {
        $l_discovery_log = '';

        isys_application::instance()->session->write_close();

        if ($p_discovery_obj->discover_device())
        {
            $l_type = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';

            $l_discovery_log .= $this->output_message("Discovery of device \"" . $p_discovery_obj->get_target() . "\" started.\n");

            $l_running_data = $p_discovery_obj->get_running_discover_status();
            $l_status       = $l_running_data['status'];
            $l_last_log     = $l_running_data['log'];
            $l_discovery_log .= $this->output_message($l_last_log . "\n");

            while ($l_status === 'Running')
            {
                if (connection_aborted())
                {
                    $this->output_message("Scan cancelled.\n");
                    $this->_die();
                } // if
                $l_running_data = $p_discovery_obj->get_running_discover_status();
                $l_status       = $l_running_data['status'];
                if ($l_last_log !== $l_running_data['log'] && $l_running_data['log'] !== '' && $l_running_data['log'] !== null)
                {
                    $l_last_log = $l_running_data['log'];
                    $l_discovery_log .= $this->output_message($l_last_log);
                } // if
            } // while
            $l_discovery_log .= $this->output_message("Finished scanning device.\n");
        }
        else
        {
            $l_discovery_log = _L(
                    'LC__CMDB__CATG__JDISC_DISCOVERY__SCAN_FAILED',
                    [$p_discovery_obj->get_target()]
                ) . "\n";
            $l_type          = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED__NOT';
        } // if

        $this->write_discovery_log($l_type, $_POST['objID'], $_POST['objTypeID'], $l_discovery_log);

        return [
            'message' => _L('LC__CMDB__CATG__JDISC_DISCOVERY__DISCOVERY_FINISHED'),
            'success' => true
        ];
    } // function

    /**
     * Write discovery log
     *
     * @param        $p_type
     * @param        $p_obj_id
     * @param        $p_obj_type_id
     * @param string $p_comment
     */
    private function write_discovery_log($p_type, $p_obj_id, $p_obj_type_id, $p_comment = '')
    {
        isys_event_manager::getInstance()
            ->triggerCMDBEvent(
                $p_type,
                null,
                $p_obj_id,
                $p_obj_type_id,
                'LC__CMDB__CATG__JDISC_DISCOVERY',
                null,
                $p_comment
            );
    } // function

} // class