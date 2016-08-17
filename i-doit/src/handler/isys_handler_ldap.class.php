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
 * LDAP handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_handler_ldap extends isys_handler
{
    /**
     * @var  integer
     */
    private $m_default_company = null;

    /**
     * @var  array
     */
    private $m_room = [];

    /**
     * Usage notice.
     */
    public function usage()
    {
        error(
            "Usage: ./controller parameter\n" . "Parameters: sync [ldap server id]\n" . "sync       - syncs all configured active ldap servers into i-doit (persons/users only)\n" . "sync n     - syncs ldap server 'n' only\n" . "fixstatus  - (re)-sets status of every ldap contact to 'normal'\n"
        );
    } // function

    /**
     * @return  boolean
     */
    public function parse_params()
    {
        global $argv;

        if (is_array($argv))
        {
            $l_cmd    = $argv[1];
            $l_method = $argv[0];
        }
        else
        {
            $l_method = null;
            $l_cmd    = null;
        } // if

        if (empty($l_method))
        {
            verbose("Wrong usage. I need at least one parameter");
            $this->usage();

            return false;
        } // if

        if (method_exists($this, $l_method))
        {
            return $this->$l_method($l_cmd);
        }
        else
        {
            verbose("Option '" . $l_method . "' does not exist\n");
            $this->usage();

            return false;
        } // if
    } // function

    /**
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session, $g_ldapconf;

        if (is_array($g_ldapconf["rooms"]))
        {
            $this->m_room = $g_ldapconf["rooms"];
        } // if

        if ($g_comp_session->is_logged_in())
        {
            verbose("LDAP-Handler initialized (" . date("Y-m-d H:i:s") . ")");

            return $this->parse_params();
        }
        else
        {
            verbose("Login failed.\n");
        } // if
    } // function

    /**
     * Person is syncronized here. $p_attributes are ldap_search data attributes.
     *
     * @param   array                                  $p_attributes
     * @param   isys_cmdb_dao_category_s_person_master $p_person_dao
     * @param   array                                  $p_mapping
     * @param   integer                                $p_ldap_server_id
     * @param   array                                  $p_serverdata
     * @param   isys_module_ldap                       $p_ldap_module
     *
     * @return  boolean
     */
    private function sync_user($p_attributes, $p_person_dao, $p_mapping, $p_ldap_server_id, $p_ldap_module, $p_serverdata, $p_forceStatus = C__RECORD_STATUS__NORMAL)
    {
        global $g_ldapconf;

        if ($g_ldapconf['defaultCompany'])
        {
            $this->m_default_company = $g_ldapconf['defaultCompany'];
        }

        if (empty($p_mapping[C__LDAP_MAPPING__USERNAME]) && empty($p_mapping[C__LDAP_MAPPING__FIRSTNAME]))
        {
            echo "LDAP Mappings empty! Configure your LDAP-Mappings in System -> LDAP -> Directories";
            die;
        } // if

        $l_username  = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__USERNAME])][0];
        $l_firstname = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__FIRSTNAME])][0];
        $l_lastname  = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__LASTNAME])][0];
        $l_mail      = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__MAIL])][0];

        if (isset($g_ldapconf['ignoreUsersWithAttributes']) && is_array($g_ldapconf['ignoreUsersWithAttributes']))
        {
            if (isset($g_ldapconf['ignoreFunction']) && is_callable($g_ldapconf['ignoreFunction']))
            {
                $l_syncUser = true;

                foreach ($g_ldapconf['ignoreUsersWithAttributes'] as $l_checkAttr)
                {
                    if (call_user_func($g_ldapconf['ignoreFunction'], $p_attributes[$l_checkAttr][0], $p_attributes))
                    {
                        $l_syncUser = false;
                    }
                    else
                    {
                        $l_syncUser = true;
                    }

                }

                if (!$l_syncUser)
                {
                    $p_ldap_module->debug('ignoreFunction prohibited syncing user "' . ($p_attributes['distinguishedname'] ?: $p_attributes['cn']) . '"');

                    throw new isys_exception_validation('ignoreFunction prohibited syncing user.', $g_ldapconf['ignoreUsersWithAttributes']);
                }
            }

        }

        if ($l_username)
        {
            $l_userdata = $p_person_dao->get_person_by_username($l_username);
            $l_user_created = false;

            if ($l_userdata->num_rows() <= 0)
            {
                $p_ldap_module->debug('User with username "' . $l_username . '" was not found. Creating..');
                $l_object_id = $p_person_dao->create(
                    null,
                    $l_username,
                    $l_firstname,
                    $l_lastname,
                    $l_mail,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $p_ldap_server_id,
                    $p_attributes["dn"],
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                );

                $l_userdata = $p_person_dao->get_person_by_username($l_username)
                    ->get_row();
                $l_user_id  = $l_userdata['isys_cats_person_list__isys_obj__id'];
                $l_user_created = true;
            }
            else
            {
                $p_ldap_module->debug('User with username "' . $l_username . '" found. Syncing..');

                $l_userdata  = $l_userdata->get_row();
                $l_user_id   = $l_userdata["isys_cats_person_list__id"];
                $l_object_id = $l_userdata['isys_cats_person_list__isys_obj__id'];

                // Fixing object status (in case an object was re-activated in ldap again, or accidentally archived in i-doit)
                // Do only in Active Directory or when explicitly activated
                if ((isset($p_serverdata["isys_ldap_directory__const"]) && $p_serverdata["isys_ldap_directory__const"] == "C__LDAP__AD") || $g_ldapconf['autoReactivateUsers'])
                {
                    $p_person_dao->set_object_status($l_object_id, $p_forceStatus);
                }

                // Setting login
                $p_person_dao->save_login($l_user_id, $l_username, null, null, $p_forceStatus, false);

            } // if

            if ($l_user_id > 0)
            {

                $p_ldap_module->debug(
                    'Available attributes for this user: ' . implode(
                        ',',
                        array_filter(
                            array_keys($p_attributes),
                            function ($p_val)
                            {
                                return !is_numeric($p_val) && $p_val != 'count' && $p_val != 'ldapi' && $p_val != 'ldap_data';
                            }
                        )
                    )
                );

                /**
                 * Initialize category data array
                 */
                $l_category_data = [
                    'data_id'    => $l_user_id,
                    'properties' => []
                ];

                /**
                 * Prepare current values
                 */
                foreach ($p_person_dao->get_properties() as $l_key => $l_property)
                {
                    if (isset($l_userdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]))
                    {
                        if ($l_key == 'organization')
                        {
                            $l_category_data['properties'][$l_key][C__DATA__VALUE] = $l_userdata['isys_connection__isys_obj__id'];
                        }
                        else
                        {
                            $l_category_data['properties'][$l_key][C__DATA__VALUE] = $l_userdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                        }
                    }
                }

                // Custom properties
                $l_custom_properties = $p_person_dao->get_custom_properties(true);

                foreach ($l_custom_properties as $l_key => $l_property)
                {
                    if (isset($l_userdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]))
                    {
                        $l_category_data['properties'][$l_key][C__DATA__VALUE] = $l_userdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                    }
                } // foreach

                /* Override default properties coming from ldap */
                $l_category_data['properties']['id']         = [C__DATA__VALUE => $l_user_id];
                $l_category_data['properties']['first_name'] = [C__DATA__VALUE => $l_firstname];
                $l_category_data['properties']['last_name']  = [C__DATA__VALUE => $l_lastname];
                $l_category_data['properties']['ldap_dn']    = [C__DATA__VALUE => $p_attributes["dn"]];
                $l_category_data['properties']['ldap_id']    = [C__DATA__VALUE => $p_ldap_server_id];
                $l_category_data['properties']['mail']       = [C__DATA__VALUE => $l_mail];

                // Prepare 'syncable' attributes.
                if (isset($g_ldapconf['attributes']) && is_array($g_ldapconf['attributes']))
                {
                    foreach ($g_ldapconf['attributes'] as $l_idoitAttribute => $l_ldapAttribute)
                    {
                        $l_ldapAttribute = strtolower($l_ldapAttribute);

                        if (!isset($l_category_data[$l_idoitAttribute]))
                        {
                            if (isset($p_attributes[$l_ldapAttribute][0]))
                            {
                                $l_category_data['properties'][$l_idoitAttribute][C__DATA__VALUE] = $p_attributes[$l_ldapAttribute][0];
                            }
                            else
                            {
                                $p_ldap_module->debug('  Warning: LDAP Attribute "' . $l_ldapAttribute . '" was not found for user ' . $p_attributes["dn"]);
                            } // if
                        } // if
                    } // foreach
                } // if

                // Prepare organization assignment.
                if (isset($g_ldapconf['attributes']['organization']) && isset($p_attributes[$g_ldapconf['attributes']['organization']][0]))
                {
                    $l_company = $p_attributes[$g_ldapconf['attributes']['organization']][0];
                }
                else if ($this->m_default_company)
                {
                    $l_company = $this->m_default_company;
                } // if

                // Check if company is defined
                if (isset($l_company) && $l_company)
                {
                    if (!is_numeric($l_company))
                    {
                        $l_orga_obj_types = $p_person_dao->get_objtype_ids_by_cats_id_as_array(C__CATS__ORGANIZATION)?: C__OBJTYPE__ORGANIZATION;
                        $l_category_data['properties']['organization'][C__DATA__VALUE] = $p_person_dao->get_obj_id_by_title($l_company, $l_orga_obj_types);

                        if (!$l_category_data['properties']['organization'][C__DATA__VALUE])
                        {
                            $l_category_data['properties']['organization'][C__DATA__VALUE] = $p_person_dao->insert_new_obj(
                                C__OBJTYPE__ORGANIZATION,
                                false,
                                $l_company,
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        } // if
                    }
                    else if (is_numeric($l_company))
                    {
                        if ($p_person_dao->obj_exists($l_company))
                        {
                            $l_category_data['properties']['organization'][C__DATA__VALUE] = $l_company;
                        } // if
                    } // if
                } // if

                unset($g_ldapconf['attributes']['company']);

                // Synchronize.
                $l_success = $p_person_dao->sync(
                    $l_category_data,
                    $l_object_id,
                    isys_import_handler_cmdb::C__UPDATE
                );

                // Emit category signal (afterCategoryEntrySave).
                isys_component_signalcollection::get_instance()
                    ->emit(
                        "mod.cmdb.afterCategoryEntrySave",
                        $p_person_dao,
                        $l_user_id,
                        $l_success,
                        $l_object_id,
                        $l_category_data,
                        []
                    );

                /**
                 * Also sync room
                 */
                if (isset($p_attributes[$g_ldapconf['attributes']['office']][0]))
                {
                    $l_room_title = $p_attributes[$g_ldapconf['attributes']['office']][0];

                    if ($g_ldapconf["import_rooms"] && $l_room_title)
                    {
                        $this->add_to_room($l_room_title, $l_user_id);
                    } // if
                } // if

                /**
                 * And corresponding groups
                 */
                if ($l_userdata && is_array($l_userdata))
                {
                    if (isset($l_userdata["isys_obj__id"]) && $l_userdata["isys_obj__id"] > 0)
                    {
                        $p_ldap_module->attach_groups_to_user(
                            $l_userdata["isys_obj__id"],
                            $p_ldap_module->ldap_get_groups($p_attributes, $p_person_dao->get_database_component()),
                            $p_person_dao,
                            $l_user_created
                        );
                    }
                    else
                    {
                        $p_ldap_module->debug('Could not attach user to groups. User ID was not found.');
                    } // if
                } // if

                $p_ldap_module->debug('Done: User ID is "' . $l_userdata["isys_obj__id"] . '" (Category ID: ' . $l_user_id . ')');

                return $l_user_id;
            }
            else
            {
                $p_ldap_module->debug('Could not add user.');
            } // if
        }
        else
        {
            verbose("Username for DN: " . C__COLOR__RED . $p_attributes["dn"] . C__COLOR__NO_COLOR . " is not defined!");
        }

        return false;
    } // function

    /**
     *
     * @param $p_room_key
     * @param $p_user_id
     */
    private function add_to_room($p_room_key, $p_user_id)
    {
        $this->m_room[$p_room_key][] = $p_user_id;
    } // function

    /**
     *
     * @param $l_user_string
     * @param $p_room_title
     */
    private function connect_room($l_user_string, $p_room_title)
    {
        $l_dao       = new isys_cmdb_dao(isys_application::instance()->database);
        $l_object_id = $l_dao->get_obj_id_by_title($p_room_title, C__OBJTYPE__ROOM);

        if (empty($l_object_id))
        {
            $l_object_id = $l_dao->insert_new_obj(C__OBJTYPE__ROOM, false, $p_room_title, null, C__RECORD_STATUS__NORMAL);
        } // if

        $l_dist = new isys_cmdb_dao_distributor(
            $l_dao->get_database_component(), $l_object_id, C__CMDB__CATEGORY__TYPE_GLOBAL, ISYS_NULL, [C__CATG__CONTACT => true]
        );

        if ($l_dist && $l_dist->count() > 0)
        {
            $l_cat = $l_dist->get_category(C__CATG__CONTACT);

            if (is_object($l_cat))
            {
                $_POST[C__POST__POPUP_RECEIVER] = $l_user_string;

                return $l_cat->create_connector($l_cat->get_table(), $l_object_id);
            } // if
        } // if

        return null;
    } // function

    /**
     *
     * @param  array $p_params
     */
    private function fixstatus($p_params)
    {
        $this->sync($p_params, C__RECORD_STATUS__NORMAL);
    } // function

    /**
     * Start the sync job.
     *
     * @param  array $p_params
     */
    private function sync($p_params, $p_forceStatus = C__RECORD_STATUS__NORMAL)
    {
        global $g_comp_database, $g_ldapconf;

        $l_server_id = null;
        if (is_numeric($p_params))
        {
            $l_server_id = $p_params;
        }

        if (class_exists("isys_module_ldap"))
        {
            $l_ldap_module = new isys_module_ldap();
            $l_ldap_dao    = new isys_ldap_dao($g_comp_database);
            $l_person_dao  = new isys_cmdb_dao_category_s_person_master($g_comp_database);

            $l_servers = $l_ldap_dao->get_active_servers($l_server_id);

            if ($l_servers->num_rows() > 0)
            {
                while ($l_row = $l_servers->get_row())
                {
                    $l_hostname  = $l_row["isys_ldap__hostname"];
                    $l_port      = $l_row["isys_ldap__port"];
                    $l_dn        = $l_row["isys_ldap__dn"];
                    $l_filter    = $l_row["isys_ldap__filter"];
                    $l_password  = isys_helper_crypt::decrypt($l_row["isys_ldap__password"]);
                    $l_mapping   = unserialize($l_row["isys_ldap_directory__mapping"]);
                    $l_recursive = (int) $l_row['isys_ldap__recursive'];
                    $l_tls       = (bool) $l_row['isys_ldap__tls'];
                    $l_version   = (int) $l_row['isys_ldap__version'] ?: 3;
                    verbose("Syncing LDAP-Server " . $l_hostname . " (" . $l_row["isys_ldap_directory__title"] . ")");

                    try
                    {
                        $l_coninfo  = null;
                        $l_ldap_lib = $l_ldap_module->get_library($l_hostname, $l_dn, $l_password, $l_port, $l_version, $l_tls);

                        if (!empty($l_mapping[C__LDAP_MAPPING__LASTNAME]))
                        {
                            /**
                             * Remove all ldap_dn entries for this ldap server
                             *  - This is used to identify deleted users later on
                             */
                            $l_sql = "UPDATE isys_cats_person_list SET isys_cats_person_list__ldap_dn = '' WHERE isys_cats_person_list__isys_ldap__id = '" . $l_row["isys_ldap__id"] . "'";
                            $l_person_dao->update($l_sql);

                            $l_search = $l_ldap_lib->search($l_row["isys_ldap__user_search"], $l_filter, [], 0, null, null, null, $l_recursive);

                            if ($l_search)
                            {
                                $l_attributes = $l_ldap_lib->get_entries($l_search);

                                for ($l_i = 0;$l_i <= $l_attributes["count"];$l_i++)
                                {
                                    if (isset($l_attributes[$l_i]["dn"]))
                                    {

                                        $l_attributes[$l_i]["ldapi"]     = &$l_ldap_lib;
                                        $l_attributes[$l_i]["ldap_data"] = &$l_row;

                                        try
                                        {
                                            if ($this->sync_user(
                                                $l_attributes[$l_i],
                                                $l_person_dao,
                                                $l_mapping,
                                                $l_row["isys_ldap__id"],
                                                $l_ldap_module,
                                                $l_row,
                                                $p_forceStatus
                                            )
                                            )
                                            {
                                                verbose("User " . C__COLOR__LIGHT_GREEN . $l_attributes[$l_i]["dn"] . C__COLOR__NO_COLOR . " synchronized.");
                                            }
                                            else
                                            {
                                                verbose("Failed synchronizing: " . C__COLOR__RED . $l_attributes[$l_i]["dn"] . C__COLOR__NO_COLOR);
                                            } // if
                                        }
                                        catch (isys_exception_validation $e)
                                        {
                                            verbose("Validation for " . C__COLOR__RED . $l_attributes[$l_i]["dn"] . C__COLOR__NO_COLOR . ' failed: ' . $e->getMessage());
                                        }
                                    } // if
                                } // for
                            } // if

                            /**
                             * Archive or delete all deleted users where ldap_dn = '', this means this user was not synced and should therefore not exist anymore
                             */
                            if (!isset($g_ldapconf['deletedUsersBehaviour']))
                            {
                                $g_ldapconf['deletedUsersBehaviour'] = 'archive';
                            }

                            if ($g_ldapconf['deletedUsersBehaviour'] == 'delete')
                            {
                                $l_deletedUserStatus = C__RECORD_STATUS__DELETED;
                            }
                            else
                            {
                                $l_deletedUserStatus = C__RECORD_STATUS__ARCHIVED;
                            }

                            $l_sql               = "SELECT isys_obj__title FROM isys_obj INNER JOIN isys_cats_person_list ON isys_obj__id = isys_cats_person_list__isys_obj__id WHERE isys_cats_person_list__isys_ldap__id = '" . $l_row["isys_ldap__id"] . "' AND isys_cats_person_list__ldap_dn = '';";
                            $l_deletedUsers      = $l_person_dao->retrieve($l_sql);
                            $l_deletedUsersArray = [];
                            while ($l_delRow = $l_deletedUsers->get_row())
                            {
                                $l_deletedUsersArray[$l_delRow['isys_obj__id']] = $l_delRow['isys_obj__title'];
                            }

                            if (count($l_deletedUsersArray) > 0 && $l_deletedUserStatus > 0)
                            {
                                $l_sql = "UPDATE isys_obj SET isys_obj__status = " . (int) $l_deletedUserStatus . " WHERE isys_obj__id IN('" . implode(
                                        ',',
                                        array_keys($l_deletedUsersArray)
                                    ) . "')";
                                $l_person_dao->update($l_sql);
                                $l_ldap_module->debug(
                                    'NOTICE: The following users were ' . $g_ldapconf['deletedUsersBehaviour'] . 'd: ' . implode(', ', $l_deletedUsersArray)
                                );
                                verbose(
                                    'Found ' . count(
                                        $l_deletedUsersArray
                                    ) . ' orphaned users which are ' . $g_ldapconf['deletedUsersBehaviour'] . 'd now (deleted users in your directory)'
                                );
                            }
                            else
                            {
                                verbose('No deleted users found.');
                            }
                            unset($l_deletedUsersArray);

                        } // if

                        if ($l_row["isys_ldap_directory__const"] == "C__LDAP__AD")
                        {
                            // Disabled users in Active Directory.
                            $l_search = $l_ldap_lib->search(
                                $l_row["isys_ldap__user_search"],
                                "(&(userAccountControl:1.2.840.113556.1.4.803:=2)(objectclass=user))",
                                [],
                                0,
                                null,
                                null,
                                null,
                                $l_recursive
                            );

                            if ($l_search)
                            {
                                $l_attributes = $l_ldap_lib->get_entries($l_search);

                                if (is_null($l_attributes["count"]))
                                {
                                    $l_disabled = 0;
                                }
                                else
                                {
                                    $l_disabled = $l_attributes["count"];
                                } // if

                                verbose("Found " . $l_disabled . " disabled object(s) inside " . $l_row["isys_ldap__user_search"] . ".");

                                for ($l_i = 0;$l_i <= $l_attributes["count"];$l_i++)
                                {
                                    if (isset($l_attributes[$l_i]["dn"]))
                                    {
                                        $l_username = $l_attributes[$l_i][strtolower($l_mapping[C__LDAP_MAPPING__USERNAME])][0];

                                        if ($l_username)
                                        {
                                            $l_user_id = $l_person_dao->get_person_by_username($l_username)
                                                ->get_row_value('isys_obj__id');

                                            if ($l_user_id > 0 && $l_person_dao->set_object_status($l_user_id, C__RECORD_STATUS__ARCHIVED))
                                            {
                                                verbose("User " . C__COLOR__CYAN . "'" . $l_username . "'" . C__COLOR__NO_COLOR . " archived.");
                                            }
                                        } // if
                                    } // if
                                } // for
                                //$l_person_dao->apply_update(); // apply unfortunately already done by set_object_status

                                verbose("");
                            } // if
                        } // if
                    }
                    catch (Exception $e)
                    {
                        error($e->getMessage());
                    } // try
                } // while
            }
            else
            {
                error("Error: No LDAP server configured.");
            } // if

            // Attach users to rooms.
            if (count($this->m_room) > 0)
            {
                foreach ($this->m_room as $l_room_title => $l_room_users)
                {
                    printf("Adding " . count($l_room_users) . " users to room: " . $l_room_title . "\n");

                    $l_contact_string = "";
                    foreach ($l_room_users as $l_user)
                    {
                        if (!is_numeric($l_user))
                        {
                            $l_userdata = $l_person_dao->get_person_by_username($l_user)
                                ->__to_array();
                            $l_user     = $l_userdata["isys_cats_person_list__id"];
                        } // if

                        $l_contact_string .= "," . $l_user;
                    } // foreach

                    $l_contact_string = ltrim($l_contact_string, ",");

                    $this->connect_room($l_contact_string, $l_room_title);
                } // foreach
            } // if
        }
        else
        {
            error("LDAP Module not installed! Please (re-)install via the update manager and latest i-doit update.");
        } // if

        // Clear all found "auth-*" cache-files.
        try
        {
            $l_cache_files = isys_caching::find('auth-*');
            array_map(
                function ($l_cache)
                {
                    $l_cache->clear();
                },
                $l_cache_files
            );
        }
        catch (Exception $e)
        {
            error('An error occurred while clearing the cache files: ' . $e->getMessage());
        }
    } // function
} // class