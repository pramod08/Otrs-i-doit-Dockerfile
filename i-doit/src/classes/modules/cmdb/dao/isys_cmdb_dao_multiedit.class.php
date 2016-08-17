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
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * Multiedit DAO
 *
 * @package    i-doit
 * @subpackage CMDB_Low-Level_API
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_multiedit extends isys_cmdb_dao
{
    /**
     * Disable or enable logging to log/multiedit.dbg
     */
    const C__LOGGING = false;

    /**
     * containts all ids which shall be created
     *
     * @var array
     */
    private $m_create_entries = [];

    /**
     * Save multiedit data.
     *
     * @param   isys_cmdb_dao_category  $p_cat_dao
     * @param   array                   $p_object_ids
     * @param   string                  $p_source_table
     * @param   array                   $p_data
     * @param   array                   $p_changes_in_entry
     * @param   array                   $p_changes_in_object
     *
     * @throws  Exception
     */
    public final function save($p_cat_dao, $p_object_ids, $p_source_table, $p_data, $p_changes_in_entry = [], $p_changes_in_object = [])
    {
        global $g_comp_template_language_manager, $g_loc;

        /*
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the save request. (via mod.cmdb.multiEditSaved)
         */
        \idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        if (!is_object($p_cat_dao))
        {
            throw new Exception('Error while saving. No category DAO instantiated.');
        } // if

        if (!$p_source_table || $p_source_table == 'isys_catg_virtual_list')
        {
            $p_source_table = $p_cat_dao->get_source_table();

            if (!$p_source_table)
            {
                $p_source_table = $p_cat_dao->get_table();
            } // if

            if (!$p_source_table)
            {
                throw new Exception('Error: source_table not found. (Category-DAO: ' . get_class($p_cat_dao) . ')');
            } // if
        } // if

        // Initialize changes array.
        $l_changed = [];

        // Initialize row cache.
        $l_cache = [];

        // Get category title by id.
        $l_category_title = $p_cat_dao->get_catg_name_by_id_as_string($p_cat_dao->get_category_id());

        // Helper methods which won´t be needed to call.
        $l_do_not_call = [
            'dialog',
            'dialog_plus',
            'get_reference_value',
            'object_image'
        ];

        // Types which will be ignored while merging the old data with the new data.
        $l_ignore_types = [
            'dialog',
            'dialog_plus',
        ];

        // Indicator if property keys should be used for retrieving the post data. Only for custom categories.
        $l_use_prop_key = false;

        if ($p_cat_dao->get_category_id() == C__CATG__CUSTOM_FIELDS)
        {
            $l_use_prop_key = true;
            list($l_category_type, $l_category_custom_id) = explode('_', $p_data['category']);
            $p_cat_dao->set_catg_custom_id($l_category_custom_id);
        } // if

        // Check if theres an array of object ids given.
        if (is_array($p_object_ids) && count($p_object_ids) > 0)
        {
            if (self::C__LOGGING)
            {
                global $g_absdir;

                $l_log = isys_log::get_instance()
                    ->set_verbose_level(isys_log::C__ALL)
                    ->set_log_file($g_absdir . '/log/multiedit.dbg');

                $l_log->debug('Registered changes: ' . var_export($p_changes_in_object, true));
            } // if

            /* If sync does not exist, this doesn't make sense at all */
            if (method_exists($p_cat_dao, 'sync'))
            {
                try
                {
                    // Get properties.
                    $l_properties       = $p_cat_dao->get_properties();
                    $l_check_properties = $l_unsupported_properties = [];

                    // Initialize $l_data.
                    $l_data = [];

                    // Remove unsupported properties.
                    foreach ($l_properties AS $l_key => $l_property)
                    {
                        if (!$l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT])
                        {
                            $l_unsupported_properties[$l_key] = $l_properties[$l_key];
                            unset($l_properties[$l_key]);
                        } // if
                    } // foreach

                    $l_assignment_category = false;

                    // Check if its an assignment category.
                    if (count($l_properties) == 1)
                    {
                        $l_check_properties = $l_properties;
                        $l_property_key     = key($l_check_properties);
                        $l_check_properties = array_pop($l_check_properties);
                        if ($l_check_properties[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection' || $l_check_properties[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'object')
                        {
                            $l_assignment_category = true;
                        } // if
                    } // if

                    if ($l_assignment_category)
                    {
                        // Sync only real changes.
                        if (isset($p_changes_in_object) && count($p_changes_in_object) > 0)
                        {
                            // Get field for the local assigned object.
                            if ($l_check_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection' || $l_check_properties[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection')
                            {
                                $l_field = 'isys_connection__isys_obj__id';
                            }
                            else
                            {
                                if (isset($l_check_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]))
                                {
                                    $l_field = $l_check_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                                }
                                else
                                {
                                    $l_field = $l_check_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                } // if
                            } // if

                            if (self::C__LOGGING && isset($l_log))
                            {
                                $l_log->debug(var_export($l_data, true));
                                $l_log->debug('################################');
                                $l_log->debug('Starting synchronization process.. ');
                                $l_log->debug('################################');
                            } // if

                            // Iterate through the objects.
                            foreach ($p_changes_in_object as $l_object_id)
                            {
                                $l_res = $p_cat_dao->get_data(null, $l_object_id);

                                if ($l_res)
                                {
                                    // Local data.
                                    $l_arr_objects = [];
                                    if ($l_res->num_rows() > 0)
                                    {
                                        while ($l_row = $l_res->get_row())
                                        {
                                            $l_arr_objects[$l_row[$p_source_table . '__id']] = $l_row[$l_field];
                                        } // while
                                    } // if

                                    // Post data.
                                    $l_data = $p_data[$l_check_properties[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__HIDDEN']['object-' . $l_object_id];
                                    if ($l_data != '')
                                    {
                                        $l_data = isys_format_json::decode($l_data);
                                        if (!is_array($l_data))
                                        {
                                            $l_data = [$l_data];
                                        } // if
                                    } // if

                                    // Create assignments.
                                    if (is_array($l_data) && count($l_data) > 0)
                                    {
                                        foreach ($l_data AS $l_post_object_id)
                                        {
                                            if (count($l_arr_objects) > 0 && $l_key = array_search(
                                                    $l_post_object_id,
                                                    $l_arr_objects
                                                )
                                            )
                                            {
                                                unset($l_arr_objects[$l_key]);
                                                continue;
                                            } // if
                                            $l_sync_prop = [
                                                'properties' => [
                                                    $l_property_key => [
                                                        C__DATA__VALUE => $l_post_object_id
                                                    ]
                                                ]
                                            ];

                                            if (method_exists($p_cat_dao, 'sync'))
                                            {
                                                try
                                                {
                                                    $l_sync_value = $p_cat_dao->sync($l_sync_prop, $l_object_id, isys_import_handler_cmdb::C__CREATE);
                                                }
                                                catch (isys_exception_validation $l_validation)
                                                {
                                                    $l_return['success'] = false;
                                                    $l_properties        = $p_cat_dao->get_properties();

                                                    foreach ($l_validation->get_validation_errors() as $l_key => $l_message)
                                                    {
                                                        if (isset($l_properties[$l_key]))
                                                        {
                                                            $l_return['data'][] = [
                                                                'obj_id'       => $l_object_id,
                                                                'prop_ui_id'   => $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__ID],
                                                                'message'      => $l_message,
                                                                'cat_entry_id' => $l_validation->get_cat_entry_id()
                                                            ];
                                                        } // if
                                                    } // foreach

                                                    $l_return['message'] = $l_validation->getMessage();
                                                } // try

                                                if ($l_sync_value)
                                                {
                                                    if (self::C__LOGGING)
                                                    {
                                                        $l_log->debug('Object ' . $l_object_id . '/' . $l_post_object_id . ' synced.<br />');
                                                    } // if
                                                }
                                                else
                                                {
                                                    if (self::C__LOGGING)
                                                    {
                                                        $l_log->debug('Sync failed for ' . $l_object_id . '/' . $l_post_object_id);
                                                    } // if
                                                } // if
                                            } // if
                                        } // foreach
                                    } // if

                                    // Delete assignments.
                                    if (count($l_arr_objects) > 0)
                                    {
                                        foreach ($l_arr_objects AS $l_cat_id => $l_local_object_id)
                                        {
                                            $p_cat_dao->delete_entry($l_cat_id, $p_source_table);
                                        } // foreach
                                    } // if
                                } // if
                            } // foreach
                        } // if
                    }
                    else
                    {
                        // Iterate through properties.
                        if (is_array($l_properties))
                        {
                            $l_obj_browser_class = new isys_popup_browser_object_ng();

                            foreach ($l_properties as $l_propkey => $l_propdata)
                            {
                                $l_is_ip_field = false;
                                $l_iterator    = 1;

                                // For custom categories.
                                if ($l_use_prop_key)
                                {
                                    $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] = $l_propkey;
                                } // if

                                // For IPv4 fields.
                                if (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'get_ip_reference')
                                {
                                    $l_is_ip_field = true;
                                } // if

                                $l_formtag = (($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP || $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DATETIME) && $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] != 'dialog_plus' && $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] != 'browser_file') ? $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__HIDDEN' : $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID];

                                if ($p_cat_dao->get_category_id() !== C__CATG__CUSTOM_FIELDS)
                                {
                                    $l_db_field = $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                }
                                else
                                {
                                    $l_db_field = $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                                } // if

                                // Check if the formtag of this properties can be found in our incoming data.
                                if (isset($p_data[$l_formtag]))
                                {
                                    // Remove first row (all objects), which is marked with a 'skip'.
                                    unset($p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['skip']);

                                    $l_sanitize_data = false;
                                    $l_options       = null;
                                    $l_filter        = null;

                                    if (isset($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION]))
                                    {
                                        if (is_array($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION]))
                                        {
                                            if (isset($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0]))
                                            {
                                                if ($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0] > 0)
                                                {
                                                    $l_filter = $l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0];
                                                }
                                                else
                                                {
                                                    $l_filter = constant($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0]);
                                                } // if

                                                if (isset($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][1]))
                                                {
                                                    $l_options = $l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][1];
                                                } // if
                                                $l_sanitize_data = true;
                                            } // if
                                        } // if
                                    }

                                    // Iterate through incoming data.
                                    foreach ($p_data[$l_formtag] as $l_key => $l_content)
                                    {
                                        if ($l_content !== null)
                                        {
                                            if (is_array($l_content) && count($l_content) === 1 && isys_format_json::is_json_array($l_content[0]))
                                            {
                                                $l_content = isys_format_json::decode($l_content[0]);
                                            } // if

                                            $l_new_entry   = false;
                                            $l_object_id   = -1;
                                            $l_category_id = null;

                                            if (strpos($l_key, '-') !== false)
                                            {
                                                list($l_category_id, $l_object_id) = explode('-', $l_key);

                                                if ($l_category_id === 'new')
                                                {
                                                    $l_category_id                          = $p_cat_dao->get_last_id_from_table($p_source_table) + $l_iterator;
                                                    $this->m_create_entries[$l_category_id] = true;
                                                    $l_new_entry                            = true;
                                                    $l_iterator++;
                                                } // if
                                            }
                                            elseif (strpos($l_key, 'new') !== false)
                                            {
                                                $l_category_id                          = $p_cat_dao->get_last_id_from_table($p_source_table) + $l_iterator;
                                                $this->m_create_entries[$l_category_id] = true;
                                                $l_object_id                            = $p_data['C__MULTIEDIT__NEW_ENTRIES'][str_replace(
                                                    [
                                                        'new',
                                                        '-'
                                                    ],
                                                    [
                                                        '',
                                                        ''
                                                    ],
                                                    $l_key
                                                )];
                                                $l_new_entry                            = true;
                                                if ($l_object_id < 0) continue;

                                                $l_iterator++;
                                            } // if

                                            // Check for json content.
                                            if (is_string($l_content) && isys_format_json::is_json_array($l_content))
                                            {
                                                $l_content = isys_format_json::decode($l_content);
                                            }

                                            if ($l_is_ip_field)
                                            {
                                                if (!Ip::validate_ipv6($l_content))
                                                {
                                                    $l_content = current($p_cat_dao->merge_posted_ip_data(C__CATS_NET_TYPE__IPV4, $l_key, $p_data[$l_formtag]));
                                                } // if
                                            } // if

                                            if ($l_sanitize_data && !is_null($l_filter))
                                            {
                                                $l_content = @filter_var($l_content, $l_filter, $l_options);
                                            } // if

                                            // Continue if entry is marked for skipping.
                                            if ($l_category_id == 'skip' || $l_object_id < 0) continue;

                                            // Set category data id.
                                            $l_data[$l_object_id][$l_category_id]['data_id'] = $l_category_id;

                                            // Set value.
                                            $l_data_value = $this->replace_object_placeholder($l_content, $l_object_id);

                                            $l_data[$l_object_id][$l_category_id]['properties'][$l_propkey][C__DATA__VALUE] = $l_data_value;

                                            // Check if theres a change made for this property.
                                            if ($l_new_entry === true)
                                            {
                                                $l_new_content = $l_content;

                                                if ($l_propdata[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER || $l_propdata[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M)
                                                {
                                                    $l_obj = null;

                                                    if (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']) && is_object(
                                                            $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']
                                                        ) && is_a($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'], 'isys_callback')
                                                    )
                                                    {
                                                        $l_obj = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'];
                                                    }
                                                    elseif (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']) && is_object(
                                                            $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']
                                                        ) && is_a($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID'], 'isys_callback')
                                                    )
                                                    {
                                                        $l_obj = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID'];
                                                    }

                                                    if (is_object($l_obj) && is_array($l_new_content))
                                                    {
                                                        $l_new_content_logbook = '';
                                                        foreach ($l_new_content AS $l_obj_id)
                                                        {
                                                            $l_new_content_logbook .= $l_obj_browser_class->format_selection($l_obj_id) . ', ';
                                                        }
                                                        $l_new_content_logbook = rtrim($l_new_content_logbook, ', ');
                                                    }
                                                    elseif (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['secondListFormat']))
                                                    {
                                                        list($l_class, $l_method) = explode('::', $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['secondListFormat']);
                                                        $l_obj_class = new $l_class($this->m_db);
                                                        if (method_exists($l_obj_class, $l_method))
                                                        {
                                                            $l_new_content_logbook = $l_obj_class->$l_method($l_new_content);
                                                        } // if
                                                    } // if
                                                }
                                                else
                                                {
                                                    if (isset($l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && strpos(
                                                            $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                                            '_2_'
                                                        ) === false
                                                    )
                                                    {
                                                        // Retrieve content of updated reference
                                                        // @todo  It is possible that "$l_content" is an array - this case can not be processed right now.
                                                        $l_dialog_data         = isys_factory_cmdb_dialog_dao::get_instance(
                                                            $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                                            $this->m_db
                                                        )
                                                            ->get_data($l_content);
                                                        $l_new_content_logbook = $g_comp_template_language_manager->get(
                                                            $l_dialog_data[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title']
                                                        );
                                                        unset($l_dialog_data);
                                                    }
                                                    elseif (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                                                    {
                                                        if (is_array($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                                                        {
                                                            // If we simply get an array.
                                                            $l_dialog_data = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                                                        }
                                                        else if (is_object($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) && get_class(
                                                                $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']
                                                            ) == 'isys_callback'
                                                        )
                                                        {
                                                            // If we get an instance of "isys_callback"
                                                            $l_dialog_data = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
                                                        }
                                                        else if (is_string($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                                                        {
                                                            // Or if we get a string (we assume it's serialized).
                                                            $l_dialog_data = unserialize($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']);
                                                        } // if
                                                        if (isset($l_dialog_data))
                                                        {
                                                            if (is_numeric($l_content) && $l_content < 0)
                                                            {
                                                                $l_new_content_logbook = $l_data[$l_object_id][$l_category_id]['properties'][$l_propkey][C__DATA__VALUE] = null;
                                                            }
                                                            else
                                                            {
                                                                $l_new_content_logbook = (isset($l_dialog_data[$l_content])) ? $l_dialog_data[$l_content] : null;
                                                            }
                                                        }
                                                    }
                                                }

                                                /* Retrieve logbook change info from post */
                                                if (empty($l_new_content_logbook))
                                                {
                                                    $l_new_content_logbook = isset($p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_key]) ? $p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_key] : $p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__VIEW'][$l_key];
                                                } // if

                                                $l_changed[$l_object_id][$l_category_id][get_class($p_cat_dao) . '::' . $l_propkey] = [
                                                    'from' => '',
                                                    'to'   => $l_new_content_logbook
                                                ];

                                                unset($l_new_content_logbook);
                                            }
                                            else
                                            {
                                                // Retrieve current dataset.
                                                if (!isset($l_cache[$l_category_id]))
                                                {
                                                    if ($p_cat_dao->get_category_id() === C__CATG__CUSTOM_FIELDS)
                                                    {
                                                        $l_res = $p_cat_dao->get_data($l_category_id);
                                                        while ($l_row_custom = $l_res->get_row())
                                                        {
                                                            $l_custom_fields_index                           = $l_row_custom['isys_catg_custom_fields_list__field_type'] . '_' . $l_row_custom['isys_catg_custom_fields_list__field_key'];
                                                            $l_cache[$l_category_id][$l_custom_fields_index] = $l_row_custom['isys_catg_custom_fields_list__field_content'];
                                                        }
                                                    }
                                                    else if ($p_cat_dao->get_category_id() !== C__CATG__APPLICATION)
                                                    {
                                                        $l_cache[$l_category_id] = $p_cat_dao->get_data($l_category_id)
                                                            ->get_row();
                                                    }
                                                    else if (method_exists($p_cat_dao, 'get_data_ng'))
                                                    {
                                                        $l_cache[$l_category_id] = $p_cat_dao->get_data_ng($l_category_id)
                                                            ->get_row();
                                                    } // if
                                                } // if

                                                $l_row = $l_cache[$l_category_id];

                                                $l_old_content = '';
                                                $l_new_content = $l_content;

                                                if ($l_propdata[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER || $l_propdata[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M)
                                                {
                                                    $l_obj = null;

                                                    if (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']) && is_object(
                                                            $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']
                                                        ) && is_a($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'], 'isys_callback')
                                                    )
                                                    {
                                                        $l_obj = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'];
                                                    }
                                                    elseif (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']) && is_object(
                                                            $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']
                                                        ) && is_a($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID'], 'isys_callback')
                                                    )
                                                    {
                                                        $l_obj = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID'];
                                                    }

                                                    if (is_object($l_obj))
                                                    {
                                                        $l_old_content = $l_obj->execute(
                                                            isys_request::factory()
                                                                ->set_object_id($l_row['isys_obj__id'])
                                                                ->set_row($l_row)
                                                                ->set_object_type_id($l_row['isys_obj_type__id'])
                                                                ->set_category_type($p_cat_dao->get_category_type())
                                                                ->set_category_data_id($l_row[$p_source_table . '__id'])
                                                        );

                                                        if ($l_old_content != $l_new_content)
                                                        {
                                                            if (is_array($l_old_content))
                                                            {
                                                                $l_cache_old_content = $l_old_content;
                                                                unset($l_old_content);
                                                                $l_old_content = '';
                                                                foreach ($l_cache_old_content AS $l_obj_id)
                                                                {
                                                                    $l_old_content .= $l_obj_browser_class->format_selection($l_obj_id) . ', ';
                                                                }
                                                                $l_old_content = rtrim($l_old_content, ', ');
                                                            }
                                                            if (is_array($l_new_content))
                                                            {
                                                                $l_new_content_logbook = '';
                                                                foreach ($l_new_content AS $l_obj_id)
                                                                {
                                                                    $l_new_content_logbook .= $l_obj_browser_class->format_selection($l_obj_id) . ', ';
                                                                }
                                                                $l_new_content_logbook = rtrim($l_new_content_logbook, ', ');
                                                            }
                                                        }
                                                    }
                                                    else
                                                    {
                                                        if ($l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection')
                                                        {
                                                            $l_old_content = $l_obj_browser_class->format_selection($l_row['isys_connection__isys_obj__id']);
                                                        }
                                                        else
                                                        {
                                                            $l_old_content = ($l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]) ? $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]] : $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];

                                                            if (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['secondListFormat']))
                                                            {
                                                                list($l_class, $l_method) = explode(
                                                                    '::',
                                                                    $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['secondListFormat']
                                                                );
                                                                $l_obj_class = new $l_class($this->m_db);
                                                                if (method_exists($l_obj_class, $l_method))
                                                                {
                                                                    $l_old_content = $l_obj_class->$l_method($l_old_content);
                                                                    $l_new_content = $l_obj_class->$l_method($l_new_content);
                                                                }
                                                            }
                                                            else
                                                            {
                                                                $l_old_content = $l_obj_browser_class->format_selection($l_old_content);
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if (isset($l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && strpos(
                                                            $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                                            '_2_'
                                                        ) === false
                                                    )
                                                    {
                                                        if ($l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection')
                                                        {
                                                            $l_old_content = $p_cat_dao->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id']);
                                                        }
                                                        else
                                                        {
                                                            $l_old_content = $g_comp_template_language_manager->get(
                                                                $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title']
                                                            );
                                                            // Retrieve content of updated reference.
                                                            // @todo  It is possible that "$l_content" is an array - this case can not be processed right now.
                                                            $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance(
                                                                $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                                                $this->m_db
                                                            )
                                                                ->get_data($l_content);
                                                            $l_new_content = $l_new_content_logbook = $g_comp_template_language_manager->get(
                                                                $l_dialog_data[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title']
                                                            );
                                                            unset($l_dialog_data);
                                                        } // if
                                                    }
                                                    elseif (isset($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                                                    {
                                                        if (is_array($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                                                        {
                                                            // If we simply get an array.
                                                            $l_dialog_data = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                                                        }
                                                        else if (is_object($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) && get_class(
                                                                $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']
                                                            ) == 'isys_callback'
                                                        )
                                                        {
                                                            // If we get an instance of "isys_callback"
                                                            $l_dialog_data = $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
                                                        }
                                                        else if (is_string($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                                                        {
                                                            // Or if we get a string (we assume it's serialized).
                                                            $l_dialog_data = unserialize($l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']);
                                                        } // if
                                                        if (isset($l_dialog_data))
                                                        {
                                                            $l_old_content = $l_dialog_data[$l_row[$l_db_field]];

                                                            if (is_numeric($l_old_content) && $l_old_content < 0)
                                                            {
                                                                $l_old_content = null;
                                                            } // if

                                                            if (is_numeric($l_content) && $l_content < 0)
                                                            {
                                                                $l_new_content = $l_new_content_logbook = $l_data[$l_object_id][$l_category_id]['properties'][$l_propkey][C__DATA__VALUE] = null;
                                                            }
                                                            else
                                                            {
                                                                $l_new_content = $l_new_content_logbook = (isset($l_dialog_data[$l_content])) ? $l_dialog_data[$l_content] : null;
                                                            }
                                                        }
                                                    }
                                                    elseif (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]))
                                                    {
                                                        if ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'object')
                                                        {
                                                            $l_old_content = $p_cat_dao->get_obj_name_by_id_as_string($l_row[$l_db_field]);
                                                        }
                                                        elseif ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'date')
                                                        {
                                                            $l_old_content = $g_loc->fmt_date($l_row[$l_db_field], true);
                                                        }
                                                        elseif ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])
                                                        {
                                                            $l_helper_class = $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                                                            if (!isset($l_helper_obj[$l_helper_class]))
                                                            {
                                                                $l_helper_obj[$l_helper_class] = new $l_helper_class(
                                                                    $l_row,
                                                                    $p_cat_dao->get_database_component(),
                                                                    $l_propdata[C__PROPERTY__DATA],
                                                                    $l_propdata[C__PROPERTY__FORMAT],
                                                                    $l_propdata[C__PROPERTY__UI]
                                                                );
                                                            }
                                                            else
                                                            {
                                                                $l_helper_obj[$l_helper_class]->set_row($l_row);
                                                                $l_helper_obj[$l_helper_class]->set_reference_info($l_propdata[C__PROPERTY__DATA]);
                                                                $l_helper_obj[$l_helper_class]->set_format_info($l_propdata[C__PROPERTY__FORMAT]);
                                                                $l_helper_obj[$l_helper_class]->set_ui_info($l_propdata[C__PROPERTY__UI]);
                                                            } // if

                                                            if (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]))
                                                            {
                                                                $l_unit_property = $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];
                                                                $l_unit_field    = $l_properties[$l_unit_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                                                $l_helper_obj[$l_helper_class]->set_unit_const($l_row[$l_unit_field]);
                                                            } // if

                                                            $l_old_value = call_user_func(
                                                                [
                                                                    $l_helper_obj[$l_helper_class],
                                                                    $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                                                ],
                                                                $l_row[$l_db_field]
                                                            );

                                                            if (is_array($l_old_value))
                                                            {
                                                                if (isset($l_old_value['ref_title']))
                                                                {
                                                                    $l_old_content = $l_old_value['ref_title'];
                                                                }
                                                                else
                                                                {
                                                                    $l_old_content = $l_old_value[C__DATA__TITLE];
                                                                } // if
                                                            }
                                                            else
                                                            {
                                                                $l_old_content = $l_old_value;
                                                            } // if
                                                        } // if
                                                    }
                                                    else
                                                    {
                                                        $l_old_content = $l_row[$l_db_field];
                                                    } // if
                                                } // if

                                                /* Check if data has changed */
                                                if ($l_old_content != $l_new_content)
                                                {
                                                    /* Retrieve logbook change info from post */
                                                    if (empty($l_new_content_logbook))
                                                    {
                                                        $l_new_content_logbook = isset($p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_key]) ? $p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID]][$l_key] : $p_data[$l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__VIEW'][$l_key];
                                                    } // if

                                                    $l_changed[$l_object_id][$l_category_id][get_class($p_cat_dao) . '::' . $l_propkey] = [
                                                        'from' => $l_old_content,
                                                        'to'   => $l_new_content_logbook
                                                    ];

                                                    unset($l_content_field, $l_new_content_logbook, $l_old_content);
                                                } // if

                                            } // else

                                        } // if

                                    } // foreach
                                } // if
                            } // foreach

                            // Iterate through incoming data.
                            if (is_array($p_data['category_data']))
                            {
                                foreach ($p_data['category_data'] as $l_key => $l_value)
                                {
                                    list($l_category_id, $l_object_id) = explode('-', $l_key);

                                    // Retrieve current dataset.
                                    if (!isset($l_cache[$l_category_id])) $l_cache[$l_category_id] = $p_cat_dao->get_data($l_category_id)
                                        ->get_row();
                                    $l_row = $l_cache[$l_category_id];

                                    if ($l_row[$l_properties['description'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])
                                    {
                                        /* Set old value, this works for basic text fields or references only */
                                        $l_data[$l_object_id][$l_category_id]['properties']['description'][C__DATA__VALUE] = $l_row[$l_properties['description'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                                    } // if
                                } // foreach
                            }
                            elseif ($p_cat_dao->get_category_type() == C__CMDB__CATEGORY__TYPE_GLOBAL && $p_cat_dao->get_category_id() == C__CATG__GLOBAL)
                            {
                                foreach ($l_cache AS $l_cache_category_id => $l_cache_category_data)
                                {
                                    $l_data[$l_cache_category_data['isys_obj__id']][$l_cache_category_id]['properties']['description'][C__DATA__VALUE] = $l_cache[$l_cache_category_id][$l_properties['description'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                                } // foreach
                            } // if
                        } // if

                        if (self::C__LOGGING)
                        {
                            $l_log->debug(var_export($l_data, true));
                            $l_log->debug('################################');
                            $l_log->debug('Starting synchronization process.. ');
                            $l_log->debug('################################');
                        } // if

                        $l_changed_objects = [];

                        /* Iterate through data and sync it */
                        foreach ($l_data as $l_object_id => $l_object_data)
                        {
                            if (!in_array($l_object_id, $p_object_ids)) continue;

                            $p_cat_dao->set_object_id($l_object_id)
                                ->set_object_type_id($p_cat_dao->get_objTypeID($l_object_id));

                            foreach ($l_object_data as $l_category_data)
                            {
                                /* Check if category is marked for creating */
                                if ($l_category_data['data_id'] === 'new' || isset($this->m_create_entries[$l_category_data['data_id']]))
                                {
                                    if ($l_use_prop_key)
                                    {
                                        $l_check_sql = 'SELECT ' . $p_source_table . '__id as `id` FROM ' . $p_source_table . ' WHERE ' . $p_source_table . '__isys_obj__id = ' . $p_cat_dao->convert_sql_id(
                                                $l_object_id
                                            ) . ' ' . ' AND ' . $p_source_table . '__isysgui_catg_custom__id = ' . $p_cat_dao->convert_sql_id(
                                                $p_cat_dao->get_catg_custom_id()
                                            );
                                    }
                                    else
                                    {
                                        $l_check_sql = 'SELECT ' . $p_source_table . '__id as `id` FROM ' . $p_source_table . ' WHERE ' . $p_source_table . '__isys_obj__id = ' . $p_cat_dao->convert_sql_id(
                                                $l_object_id
                                            );
                                    } // if

                                    $l_check = $this->retrieve($l_check_sql);

                                    /* Create new single value entry if it does not exist */
                                    if (($l_category_data['data_id'] === 'new' || isset($this->m_create_entries[$l_category_data['data_id']])) && ($l_check->num_rows(
                                            ) === 0 || $p_cat_dao->is_multivalued())
                                    )
                                    {
                                        $l_sync_type = isys_import_handler_cmdb::C__CREATE;
                                    }
                                    else
                                    {
                                        /* Or retrieve the correct id of it right now */
                                        $l_row                      = $l_check->get_row();
                                        $l_category_data['data_id'] = $l_row['id'];
                                        $l_sync_type                = isys_import_handler_cmdb::C__UPDATE;
                                    } // if
                                }
                                elseif (count($p_changes_in_entry) > 0 && !in_array($l_category_data['data_id'], $p_changes_in_entry))
                                {
                                    continue;
                                }
                                else
                                {
                                    $l_sync_type = isys_import_handler_cmdb::C__UPDATE;
                                } // if

                                /* .. and sync! */
                                if ($l_object_id > 0)
                                {
                                    if (count($l_unsupported_properties) > 0 && $l_sync_type == isys_import_handler_cmdb::C__UPDATE)
                                    {
                                        foreach ($l_unsupported_properties AS $l_propkey => $l_upropdata)
                                        {
                                            // have to skip description field for custom categories otherwise the description will be replaced
                                            if (($l_propkey === 'description' && $p_cat_dao->get_category_id(
                                                    ) === C__CATG__CUSTOM_FIELDS) || ($l_propkey === 'contact' && $p_cat_dao->get_category_id() === C__CATG__CONTACT)
                                            )
                                            {
                                                continue;
                                            } // if

                                            // add missing unsupported properties
                                            if (isset($l_upropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && !in_array(
                                                    $l_upropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1],
                                                    $l_do_not_call
                                                ) && !in_array($l_upropdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE], $l_ignore_types)
                                            )
                                            {

                                                $l_helper_class = $l_upropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                                                if (!isset($l_helper_obj[$l_helper_class]))
                                                {
                                                    $l_helper_obj[$l_helper_class] = new $l_helper_class(
                                                        $l_cache[$l_category_data['data_id']],
                                                        $p_cat_dao->get_database_component(),
                                                        $l_upropdata[C__PROPERTY__DATA],
                                                        $l_upropdata[C__PROPERTY__FORMAT],
                                                        $l_upropdata[C__PROPERTY__UI]
                                                    );
                                                }
                                                else
                                                {
                                                    $l_helper_obj[$l_helper_class]->set_row($l_cache[$l_category_data['data_id']]);
                                                    $l_helper_obj[$l_helper_class]->set_reference_info($l_upropdata[C__PROPERTY__DATA]);
                                                    $l_helper_obj[$l_helper_class]->set_format_info($l_upropdata[C__PROPERTY__FORMAT]);
                                                    $l_helper_obj[$l_helper_class]->set_ui_info($l_upropdata[C__PROPERTY__UI]);
                                                } // if

                                                if (isset($l_upropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]))
                                                {
                                                    $l_unit_property = $l_upropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];
                                                    if (isset($l_unsupported_properties[$l_unit_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]))
                                                    {
                                                        $l_unit_field = $l_unsupported_properties[$l_unit_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                                        $l_helper_obj[$l_helper_class]->set_unit_const($l_cache[$l_category_data['data_id']][$l_unit_field]);
                                                    } // if
                                                } // if

                                                $l_export_method = $l_upropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];

                                                $l_export_value = $l_helper_obj[$l_helper_class]->$l_export_method(
                                                    $l_cache[$l_category_data['data_id']][$l_upropdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]
                                                );

                                                if (is_object($l_export_value))
                                                {
                                                    $l_export_value = ['value' => $l_export_value->get_data()];
                                                }
                                                else
                                                {
                                                    $l_export_value = ['value' => $l_export_value];
                                                } // if

                                                $l_import_method = $l_export_method . '_import';
                                                if (method_exists($l_helper_obj, $l_import_method))
                                                {
                                                    $l_import_value = $l_helper_obj[$l_helper_class]->$l_import_method($l_export_value);
                                                }
                                                else
                                                {
                                                    $l_import_value = $l_cache[$l_category_data['data_id']][$l_upropdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                                                } // if

                                                $l_category_data['properties'][$l_propkey] = [
                                                    'value' => $l_import_value
                                                ];
                                            }
                                            elseif (isset($l_upropdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                                            {
                                                $l_category_data['properties'][$l_propkey] = [
                                                    'value' => $l_cache[$l_category_data['data_id']][$l_upropdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]]
                                                ];
                                            }
                                            else
                                            {
                                                $l_category_data['properties'][$l_propkey] = [
                                                    'value' => $l_cache[$l_category_data['data_id']][$l_upropdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]
                                                ];
                                            } // if
                                        } // foreach
                                    } // if

                                    $l_sync_value = null;
                                    try
                                    {
                                        $l_sync_value = $p_cat_dao->sync($l_category_data, $l_object_id, $l_sync_type);
                                    }
                                    catch (isys_exception_validation $l_validation)
                                    {
                                        $l_properties = $p_cat_dao->get_properties();

                                        $l_return['success'] = false;

                                        foreach ($l_validation->get_validation_errors() as $l_key => $l_message)
                                        {
                                            if (isset($l_properties[$l_key]))
                                            {
                                                $l_return['data'][] = [
                                                    'obj_id'       => $l_object_id,
                                                    'prop_ui_id'   => $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__ID],
                                                    'message'      => $l_message,
                                                    'cat_entry_id' => $l_validation->get_cat_entry_id()
                                                ];
                                            } // if
                                        } // foreach

                                        $l_return['message'] = $l_validation->getMessage();
                                    } // try

                                    if ($l_sync_type == isys_import_handler_cmdb::C__CREATE)
                                    {
                                        $l_category_data['data_id'] = $l_sync_value;
                                    } // if

                                    if ($l_sync_value)
                                    {
                                        if (self::C__LOGGING)
                                        {
                                            $l_log->debug(
                                                'Object ' . $l_object_id . '/' . $l_category_data['data_id'] . ' synced.<br />'
                                            );
                                        } // if
                                    }
                                    else
                                    {
                                        if (self::C__LOGGING)
                                        {
                                            $l_log->debug('Sync failed for ' . $l_object_id . '/' . $l_category_data['data_id']);
                                        } // if
                                    } // if
                                }
                                else
                                {
                                    if (self::C__LOGGING)
                                    {
                                        $l_log->debug('Object id missing for data: ' . $l_category_data);
                                    } // if
                                } // if
                            } // foreach

                            /* Store changes in database */
                            if (is_array($l_changed) && count($l_changed) > 0)
                            {
                                $l_strConstEvent     = "C__LOGBOOK_EVENT__CATEGORY_CHANGED";
                                $l_mod_event_manager = isys_event_manager::getInstance();

                                if (is_array($l_changed[$l_object_id]))
                                {
                                    foreach ($l_changed[$l_object_id] as $l_change)
                                    {
                                        if ((bool) isys_tenantsettings::get('logbook.changes', '1'))
                                        {
                                            $l_changed_compressed = serialize($l_change);
                                        }
                                        else $l_changed_compressed = '';
                                        /* ----------------------------------------------------------------------------------- */

                                        /* Create the logbook entry after object change */
                                        $l_mod_event_manager->triggerCMDBEvent(
                                            $l_strConstEvent,
                                            '',
                                            $l_object_id,
                                            $p_cat_dao->get_objTypeID($l_object_id),
                                            $l_category_title,
                                            $l_changed_compressed,
                                            ''
                                        );
                                    } // foreach
                                } // if
                            } // if

                        } // foreach

                        // Update isys_obj__updated.
                        $p_cat_dao->object_changed(array_keys($l_changed));

                        isys_component_signalcollection::get_instance()
                            ->emit('mod.cmdb.multiEditSaved', $p_cat_dao, $l_data, $l_changed);

                    } // if
                }
                catch (isys_exception_dao_cmdb $e)
                {
                    throw new Exception($e->getMessage());
                }
                catch (isys_exception_cmdb $e)
                {
                    throw new Exception($e);
                }
                catch (Exception $e)
                {
                    throw $e;
                } // try
            }
            else
            {
                throw new Exception('Sync method not found. This category is not multi-editable.');
            } // if
        }
        else
        {
            throw new Exception('Error: No objects selected for multi edit.');
        } // if

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);
        die;
    } // function

    /**
     * Constructor.
     *
     * @param  isys_component_database $p_database
     */
    public function __construct(isys_component_database $p_database)
    {
        parent::__construct($p_database);
    } // function
} // class