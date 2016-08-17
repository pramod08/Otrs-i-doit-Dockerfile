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
 * Handler: Notifications
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_jdisc extends isys_handler
{
    /**
     * Constant for the "profile" parameter.
     *
     * @var  string
     */
    const C__PROFILE_PARAMETER = '-r';
    /**
     * Constant for the "group" parameter.
     *
     * @var  string
     */
    const C__GROUP_PARAMETER = '-g';
    /**
     * Constant for the "mode" parameter.
     *
     * @var  string
     */
    const C__MODE_PARAMETER = '-x';
    /**
     * Constant for the selected "JDisc Server" parameter.
     *
     * @var  string
     */
    const C__SERVER_PARAMETER = '-s';
    /**
     * Constant which indicates if overlapping host addresses should be overwritten
     *
     * @var string
     */
    const C__HOST_ADDRESS_OVERWRITE = '-o';
    const C__DETAILED_LOGGING       = '-l';
    /**
     * Constant which decides if search index should be regenerated after the import or not
     */
    const C__REGENERATE_INDEX = '-b';
    /**
     * Log instance for this handler.
     *
     * @var  isys_log
     */
    protected $m_log = null;

    /**
     * Desctructor
     *
     * @todo  Move it to parent class!?
     */
    public function __destruct()
    {
        $this->logout();
    } // function

    /**
     * Method for retrieving the handler-title.
     *
     * @return  string
     */
    protected function get_title()
    {
        return 'JDisc import';
    } // function

    /**
     * Initiates the handler.
     */
    public function init()
    {
        global $g_comp_database, $argv, $g_comp_template_language_manager, $g_idoit_language_short;

        if (!is_object($g_comp_template_language_manager))
        {
            if (empty($g_idoit_language_short))
            {
                $g_idoit_language_short = 'en';
            } // if

            $g_comp_template_language_manager = new isys_component_template_language_manager($g_idoit_language_short);
        } // if

        // Start logging.
        if (array_search(self::C__DETAILED_LOGGING, $argv))
        {
            $this->m_log = isys_factory_log::get_instance('import_jdisc');

        }
        else $this->m_log = isys_log_null::get_instance();

        $this->m_log->set_verbose_level(isys_log::C__WARNING | isys_log::C__ERROR | isys_log::C__FATAL);

        // JDisc module
        $l_jdisc = isys_module_jdisc::factory();

        $l_import_counter = 0;

        if (array_search(self::C__PROFILE_PARAMETER, $argv) === false)
        {
            $this->usage();
        } // if

        // Retrieving the group.
        $l_slice = (array_search(self::C__GROUP_PARAMETER, $argv) !== false) ? array_search(self::C__GROUP_PARAMETER, $argv) + 1 : false;
        $l_group = null;
        if ($l_slice !== false)
        {
            $l_cmd   = array_slice($argv, $l_slice);
            $l_group = $l_cmd[0];
        } // if

        // Retrieving the profile.
        $l_slice   = (array_search(self::C__PROFILE_PARAMETER, $argv) !== false) ? array_search(self::C__PROFILE_PARAMETER, $argv) + 1 : false;
        $l_profile = null;
        if ($l_slice !== false)
        {
            $l_cmd     = array_slice($argv, $l_slice);
            $l_profile = $l_cmd[0];
        } // if

        // Retrieving the jdisc server.
        $l_slice        = (array_search(self::C__SERVER_PARAMETER, $argv) !== false) ? array_search(self::C__SERVER_PARAMETER, $argv) + 1 : false;
        $l_jdisc_server = null;
        if ($l_slice !== false)
        {
            $l_cmd          = array_slice($argv, $l_slice);
            $l_jdisc_server = $l_cmd[0];
        } // if

        // Retrieving info if overlapped host addresses should be overwritten or not.
        $l_overwrite_host_addresses = (array_search(self::C__HOST_ADDRESS_OVERWRITE, $argv) !== false) ? true : false;

        if (!is_numeric($l_jdisc_server))
        {
            verbose("No jdisc server selected. Using default server for import.\n");
            $l_res_jdisc_server = $l_jdisc->get_jdisc_servers();
            while ($l_row = $l_res_jdisc_server->get_row())
            {
                if ($l_row['isys_jdisc_db__default_server'] > 0)
                {
                    $l_jdisc_server = $l_row['isys_jdisc_db__id'];
                    break;
                } // if
            } // while
            $l_jdisc->switch_database($l_jdisc_server);
        }
        else
        {
            $l_jdisc_server = (int) $l_jdisc_server;

            if (!$l_jdisc->switch_database($l_jdisc_server))
            {
                verbose("Could not connect to the selected JDisc server. Please confirm if the credentials for this server are correct.\n");
                die;
            } // if
        } // if

        if (!is_numeric($l_profile))
        {
            verbose("Profile ID has to be from type Integer.\n");
            die;
        }
        else
        {
            verbose('Checking Profile ID.');
            $l_profile = (int) $l_profile;

            if (!$l_jdisc->check_profile($l_profile))
            {
                verbose("Specified profile ID does not exist.\n", true);
                die;
            } // if

            if (is_numeric($l_jdisc_server))
            {
                if (!$l_jdisc->check_profile_in_server($l_profile, $l_jdisc_server))
                {
                    verbose(
                        "Specified profile ID is not assigned to the selected JDisc server. Please use another profile or assign the profile to the selected JDisc server.\n"
                    );
                    die;
                } // if
            } // if
        } // if

        // Retrieving the mode.
        $l_slice = array_search(self::C__MODE_PARAMETER, $argv) + 1;
        $l_cmd   = array_slice($argv, $l_slice);
        $l_mode  = $l_cmd[0];
        // Groups are optional, profiles not.
        $l_group             = ($l_group > 0) ? (int) $l_group : null;
        $l_clear_identifiers = false;

        // Retrieving indicator if search index should be regenerated
        $l_regenerate_search_index = (array_search(self::C__REGENERATE_INDEX, $argv) !== false) ? true : false;

        switch ($l_mode)
        {
            case 1:
                $l_mode = isys_import_handler_cmdb::C__APPEND;
                break;
            case 3:
                $l_jdisc->set_clear_mode(isys_import_handler_cmdb::C__OVERWRITE);
                $l_mode = isys_import_handler_cmdb::C__UPDATE;
                break;
            case 4:
                $l_clear_identifiers = true;
                $l_mode              = isys_import_handler_cmdb::C__UPDATE;
                break;
            case 2:
            default:
                $l_mode = isys_import_handler_cmdb::C__UPDATE;
                break;
        } // switch

        // Prepare the import-array.
        verbose('Begin to retrieve data and prepare the environment...');
        $l_jdisc->set_mode($l_mode)
            ->prepare_environment($l_profile);

        // Getting the PDO.
        verbose('Receiving the PDO instance... ');
        $l_pdo = null;
        try
        {
            $l_pdo = $l_jdisc->get_connection();
            verbose('Success!', false);
        }
        catch (Exception $e)
        {
            verbose('Failure: ' . $e->getMessage());
        } // try

        // Retrieve the result set for the objects to be imported.
        verbose('Receiving the JDisc data... ');
        $l_obj_res = $l_jdisc->retrieve_object_result($l_group, $l_profile);

        if ($l_obj_res)
        {
            try
            {
                $l_total_objects = $l_pdo->num_rows($l_obj_res);
                $l_start_time        = microtime(true);

                // Display the number of objects, that will be imported.
                verbose('Found ' . $l_total_objects . ' objects!', true);

                // Create an instance of the CMDB import
                $l_import = new isys_import_handler_cmdb($this->m_log, $g_comp_database);
                $l_import->set_empty_fields_mode(isys_import_handler_cmdb::C__KEEP);

                // Decide if overlapping host addresses should be overwritten or not
                $l_import->set_overwrite_ip_conflicts($l_overwrite_host_addresses);
                $l_import->set_general_header('JDisc');
                $l_import->set_logbook_source(C__LOGBOOK_SOURCE__JDISC);

                // Matching from JDisc device id to i-doit object id:
                $l_jdisc_to_idoit = [];

                // Cached object identifiers:
                $l_object_ids = [];

                // Cached devices
                $l_arr_device_ids = [];

                if ($l_total_objects > 0)
                {
                    /**
                     * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
                     * This is extremely important!
                     *
                     * An Index is done for all objects at the end of the request, if enabled via parameter.
                     */
                    idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
                        ->disconnectOnAfterCategoryEntrySave();

                    $l_not_defined_types = [];
                    $l_already_used      = new isys_array();
                    $l_group_name        = null;
                    $l_identifiers_arr   = $l_device_arr = [];
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
                                    'deviceid-' . $l_jdisc_server,
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

                    if ($l_clear_identifiers === true)
                    {
                        $l_jdisc->get_identifier_dao()
                            ->clear_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $l_jdisc_server, null, $l_group_name);
                    } // if

                    if (!empty($l_identifiers_arr))
                    {
                        $l_jdisc->get_identifier_dao()
                            ->set_mapping($l_identifiers_arr);
                    } // if

                    //while ($l_obj_row = $l_pdo->fetch_row_assoc($l_obj_res))
                    foreach ($l_device_arr AS $l_obj_row)
                    {
                        unset($l_prepared_data);

                        if (!isset($l_obj_row['idoit_obj_type']) || $l_obj_row['idoit_obj_type'] === null)
                        {
                            if (!isset($l_not_defined_types[$l_obj_row['type_name']]))
                            {
                                verbose(
                                    'JDisc type "' . $l_obj_row['type_name'] . '" is not properly defined in the profile. Skipping devices with JDisc type "' . $l_obj_row['type_name'] . '".'
                                );
                                $l_not_defined_types[$l_obj_row['type_name']] = true;
                            } // if
                            continue;
                        } // if

                        if (in_array($l_obj_row['deviceid'], $l_arr_device_ids)) continue;

                        $l_import_counter++;
                        verbose('Importing object "' . $l_obj_row['name'] . '": ' . $l_import_counter . '/' . $l_total_objects . '.', true);

                        $l_arr_device_ids[] = $l_obj_row['deviceid'];

                        $l_prepared_data = $l_jdisc->prepare_object_data(
                            $l_obj_row,
                            $l_jdisc_to_idoit,
                            $l_object_ids
                        );

                        if (!isset($l_prepared_data['object']) || !is_array($l_prepared_data['object']))
                        {
                            $l_prepared_data['object'] = [];
                        }
                        if (!isset($l_prepared_data['connections']) || !is_array($l_prepared_data['connections']))
                        {
                            $l_prepared_data['connections'] = [];
                        }
                        if (!isset($l_object_ids) || !is_array($l_object_ids))
                        {
                            $l_object_ids = [];
                        }

                        // Prepare and import the data.
                        $l_import->reset()
                            ->set_scantime()
                            ->set_prepared_data($l_prepared_data['object'])
                            ->set_connection_info($l_prepared_data['connections'])
                            ->set_mode($l_mode)
                            ->set_object_created_by_others(true)
                            ->set_object_ids($l_object_ids)
                            ->set_logbook_entries(
                                $l_jdisc->get_device_dao()
                                    ->get_logbook_entries()
                            )
                            ->import();
                        verbose('Done!', false);

                        // The last id is the prepared object:
                        $l_last_object_id                  = $l_import::get_stored_objectID();
                        $l_already_used[$l_last_object_id] = $l_obj_row['name'];

                        $l_jdisc_to_idoit[$l_obj_row['id']] = $l_last_object_id;
                        $l_jdisc->get_device_dao()
                            ->set_jdisc_to_idoit_objects($l_obj_row['id'], $l_last_object_id);
                        $l_object_ids[$l_last_object_id] = $l_last_object_id;
                    } // while
                    $l_import->set_overwrite_ip_conflicts(false);
                    unset($l_import);
                    verbose('Starting the final step of the import: Referencing the data.', true);

                    verbose('Step 1: Updating cluster assignments.', true);
                    $l_jdisc->get_cluster_dao()
                        ->assign_clusters(
                            $l_jdisc_to_idoit,
                            $l_jdisc->get_network_dao()
                                ->get_vrrp_addresses()
                        );

                    verbose('Step 2: Updating cluster members.', true);
                    $l_jdisc->get_cluster_dao()
                        ->update_cluster_members($l_jdisc_to_idoit);

                    verbose('Step 3: Creating blade connections.', true);
                    $l_jdisc->get_device_dao()
                        ->create_blade_connections($l_jdisc_to_idoit);

                    verbose('Step 4: Creating module connections.', true);
                    $l_jdisc->get_device_dao()
                        ->create_module_connections(
                            $l_jdisc_to_idoit,
                            $l_jdisc->get_network_dao()
                                ->get_import_type_interfaces()
                        );

                    // To save memory leak we iterate through all imported objects
                    $l_counter = 1;
                    foreach ($l_jdisc_to_idoit AS $l_device_id => $l_object_id)
                    {
                        verbose(
                            'Processing Object "' . $l_already_used[$l_object_id] . '" with Object-ID #' . $l_object_id . '. (' . $l_counter++ . '/' . $l_total_objects . ')'
                        );

                        $l_cache_network  = $l_jdisc->get_network_dao()
                            ->load_cache($l_object_id);
                        $l_cache_software = $l_jdisc->get_software_dao()
                            ->load_cache($l_object_id);

                        if ($l_cache_network || $l_cache_software)
                        {
                            // Create net listeners
                            $l_jdisc->get_software_dao()
                                ->create_net_listener_connections($l_object_id, $l_device_id);

                            // Create port connections
                            $l_jdisc->get_network_dao()
                                ->create_port_connections();
                            // Update ip to port assignments
                            $l_jdisc->get_network_dao()
                                ->update_ip_port_assignments();

                            // Create port map
                            $l_jdisc->get_network_dao()
                                ->create_port_map($l_object_id);
                            // Assign interfaces to the ports
                            $l_jdisc->get_network_dao()
                                ->create_network_interface_connections($l_object_id);
                            // This function takes more time than the others
                            $l_jdisc->get_network_dao()
                                ->update_vlan_assignments($l_object_id, $l_device_id);
                        } // if

                        verbose('Done!', true);
                    } // foreach

                    // Recover identifiers
                    $l_jdisc->get_identifier_dao()
                        ->recover_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $l_jdisc_server);

                    // Remove temporary table
                    $l_jdisc->get_network_dao()
                        ->drop_cache_table();

                    // Regenerate Search index
                    if ($l_regenerate_search_index)
                    {
                        $l_affected_categories = $l_jdisc->get_cached_profile()['categories'];
                        if(is_array($l_affected_categories) && count($l_affected_categories))
                        {
                            verbose('Regenerating search index..');

                            $startTimeIndexCreation = microtime(true);

                            /* Adding additional categories*/
                            $l_categories   = array_keys();
                            $l_categories[] = C__CMDB__SUBCAT__NETWORK_INTERFACE_L;
                            $l_categories[] = C__CATG__JDISC_CA;

                            \idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
                                ->onPostImport(
                                    $l_start_time,
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

                            verbose("Index creation took " . number_format(microtime(true) - $startTimeIndexCreation, 2) . " secs.");
                        } // if
                    } // if

                    verbose('Complete process took: ' . isys_glob_seconds_to_human_readable((int) (microtime(true) - $l_start_time)));
                    verbose('Memory peak usage: ' . number_format(memory_get_peak_usage() / 1024 / 1024, 2, '.', '') . ' MB');
                }
                else
                {
                    verbose('No objects found, sorry.', true);
                } // if

                verbose('Import finished.', true);
                verbose($l_import_counter . ' objects affected.', true);

                $this->m_log->info('Import finished.');
            }
            catch (Exception $e)
            {
                $l_error_msg = $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine();
                verbose('Import failed with message: ');
                verbose($l_error_msg);
                $this->m_log->error('Import failed with message: ' . $l_error_msg);
            } // try
        }
        else
        {
            verbose('Import failed with message: ');
            verbose('"There are no object types defined in the JDisc profile or are deactivated in the object type configuration."');
            $this->m_log->error('Import failed with message: "There are no object types defined in the JDisc profile or are deactivated in the object type configuration."');
        } // if

        $this->m_log->info($l_import_counter . ' objects affected.');
    } // function

    /**
     * Prints out the usage of he import handler.
     */
    public function usage()
    {
        echo "\n" . C__COLOR__LIGHT_RED . "Missing parameters!\n" . C__COLOR__NO_COLOR . "You have to use the following parameters in order for the JDisc import to work:\n\n" . "  " . self::C__PROFILE_PARAMETER . " profile-ID\n" . "  " . self::C__MODE_PARAMETER . " mode-ID (optional, default: 1)\n" . "  " . self::C__GROUP_PARAMETER . " group-ID (optional)\n\n" . "  " . self::C__SERVER_PARAMETER . " jdisc-server-ID (optional)\n\n" . "  " . self::C__HOST_ADDRESS_OVERWRITE . " Indicator for overwriting overlapped host addresses (optional)\n\n" . "  " . self::C__DETAILED_LOGGING . " Activate detailed logging (optional, memory intensive)\n\n" . "  " . self::C__REGENERATE_INDEX . " Activate regeneration of the search index (optional, memory intensive)\n\n" . "Possible modes are:\n" . "  1: Append    				- The import will only create new objects.\n" . "  2: Update    				- The import will try to update already existing objects.\n" . "  3: Overwrite 				- The import behaves like the update mode but clears all list categories of the existing object.\n" . "  4: Update (newly discovered) - The import clears all existing identification keys before the Update mode is triggered.\n" . PHP_EOL;

        die;
    } // function
} // class