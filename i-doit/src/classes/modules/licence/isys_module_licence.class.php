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
 * @package     i-doit
 * @subpackage  Licensing
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define("C__LICENCE__OBJECT_COUNT", 0x001);
define("C__LICENCE__DB_NAME", 0x002);
define("C__LICENCE__CUSTOMER_NAME", 0x003);
define("C__LICENCE__REG_DATE", 0x004);
define("C__LICENCE__RUNTIME", 0x005);
define("C__LICENCE__EMAIL", 0x006);
define("C__LICENCE__KEY", 0x007);
define("C__LICENCE__TYPE", 0x008);
define("C__LICENCE__DATA", 0x009);
define("C__LICENCE__CONTRACT", 0x010);
define("C__LICENCE__MAX_CLIENTS", 0x011);

define("LICENCE_ERROR_OBJECT_COUNT", -1);
define("LICENCE_ERROR_DB", -2);
define("LICENCE_ERROR_REG_DATE", -3);
define("LICENCE_ERROR_OVERTIME", -4);
define("LICENCE_ERROR_KEY", -5);
define("LICENCE_ERROR_EXISTS", -6);
define("LICENCE_ERROR_TYPE", -7);
define("LICENCE_ERROR_INVALID", -8);
define("LICENCE_ERROR_UNREADABLE", -9);
define("LICENCE_ERROR_INVALID_TYPE", -10);
define("LICENCE_ERROR_NO_DB", -11);
define("LICENCE_ERROR_SYSTEM", -100);

define("C__LICENCE_TYPE__SINGLE", 0);
define("C__LICENCE_TYPE__HOSTING", 1);
define("C__LICENCE_TYPE__HOSTING_SINGLE", 2);
define("C__LICENCE_TYPE__BUYERS_LICENCE", 3);
define("C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING", 4);

/**
 * Class isys_module_licence
 */
class isys_module_licence extends isys_module implements isys_module_interface
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;

    private $m_userrequest = null;

    /**
     * @param $bool
     */
    public static function session_licenced($bool)
    {
        $_SESSION["licenced"] = $bool;
    } // function

    /**
     * Show nag screen if user is not licenced.
     */
    public static function show_nag_screen()
    {
        if (C__ENABLE__LICENCE)
        {
            if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM && $_GET['handle'] != 'licence_overview')
            {
                if (isset($_SESSION['licenced']) && $_SESSION['licenced'] === false)
                {
                    isys_component_signalcollection::get_instance()
                        ->connect(
                            'system.gui.beforeRender',
                            function ()
                            {
                                register_shutdown_function(
                                    function ()
                                    {
                                        echo '<script>openFullscreenPopup("license-warning");</script>';
                                    }
                                );
                            }
                        );
                } // if
            } // if
        } // if
    } // function

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req))
        {
            $this->m_userrequest = &$p_req;

            return true;
        } // if

        return false;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        ;
    }

    /**
     * Starts module process.
     */
    public function start()
    {
        ;
    } // function

    /**
     * Verifys licence status and saves it in session variable licenced and licence_data
     */
    public function verify()
    {
        global $g_comp_database_system, $g_comp_session, $g_comp_database;

        try
        {
            self::session_licenced(false);

            $l_licences = $this->get_installed_licences(
                $g_comp_database_system,
                $g_comp_session->get_current_mandator_as_id(),
                null,
                "AND " . "(isys_licence__type != '" . C__LICENCE_TYPE__HOSTING . "')"
            );

            if (!is_null($l_licences))
            {
                if (count($l_licences) > 0)
                {
                    $_SESSION["licence_data"] = [];
                    foreach ($l_licences as $l_lic)
                    {
                        $l_licence = $this->check_licence($l_lic["licence_data"], $g_comp_database);

                        if (isset($l_licence[C__LICENCE__DATA]))
                        {
                            $_SESSION["licence_data"] = array_merge($_SESSION["licence_data"], $l_licence[C__LICENCE__DATA]);
                        }
                    }
                    self::session_licenced(true);
                }
            }
            else
            {
                throw new isys_exception_licence(_L("LC__LICENCE__NO_LICENCE"), 1);
            }
        }
        catch (isys_exception_licence $e)
        {
            self::session_licenced(false);
        }
    } // function

    /**
     * Checks a licence file.
     *
     * @param   string                  $p_file
     * @param   isys_component_database $p_database
     * @param   array                   $p_options We can give some options to the parser
     *
     * @return  array
     * @throws  isys_exception_licence
     */
    public function check_licence_file($p_file, $p_database, array $p_options = [])
    {
        if (file_exists($p_file))
        {
            return $this->parse(file_get_contents($p_file), $p_database, $p_options);
        }
        else
        {
            throw new isys_exception_licence("File {$p_file} does not exist.", 0);
        } // if
    } // function

    /**
     *
     * @param  string                  $p_string
     * @param  isys_component_database $p_database
     * @param  array                   $p_options We can give some options to the parser
     *
     * @return mixed
     */
    public function check_licence($p_string, $p_database, array $p_options = [])
    {
        return $this->parse($p_string, $p_database, $p_options);
    } // function

    /**
     * @param isys_component_database $p_database
     * @param null                    $p_licence_id
     *
     * @return isys_component_dao_result
     */
    public function get_licence(isys_component_database $p_database, $p_licence_id = null)
    {
        $l_sql = "SELECT * FROM isys_licence WHERE TRUE";

        if (!is_null($p_licence_id))
        {
            $l_sql .= " AND isys_licence__id = '" . $p_licence_id . "'";
        }

        $l_dao_mandator = new isys_component_dao_mandator($p_database);

        return $l_dao_mandator->retrieve($l_sql . ";");
    } // function

    /**
     * @param   isys_component_database $p_database
     * @param   integer                 $p_mandator_id
     * @param   integer                 $p_licence_type
     * @param   string                  $p_condition
     *
     * @return  array
     */
    public function get_installed_licences(isys_component_database $p_database, $p_mandator_id = null, $p_licence_type = null, $p_condition = "")
    {
        global $g_db_system;

        $i     = 0;
        $l_lic = [];

        $l_sql = "SELECT * FROM isys_licence WHERE TRUE";
        if (!is_null($p_mandator_id)) $l_sql .= " AND (isys_licence__isys_mandator__id = '" . $p_mandator_id . "')";
        if (!is_null($p_licence_type)) $l_sql .= " AND (isys_licence__type = '" . $p_licence_type . "')";
        $l_sql .= " " . $p_condition;

        $l_dao_mandator = new isys_component_dao_mandator($p_database);
        $l_licdata      = $l_dao_mandator->retrieve($l_sql . ";");

        while ($l_row = $l_licdata->get_row())
        {
            $i++;

            if (!is_null($l_row["isys_licence__isys_mandator__id"]))
            {
                $l_mandators = $l_dao_mandator->get_mandator($l_row["isys_licence__isys_mandator__id"], 1);
                if ($l_mandators->num_rows() > 0)
                {
                    $l_dbdata = $l_mandators->get_row();

                    // Create connection to mandator DB
                    $l_database = isys_component_database::get_database(
                        $g_db_system["type"],
                        $l_dbdata["isys_mandator__db_host"],
                        $l_dbdata["isys_mandator__db_port"],
                        $l_dbdata["isys_mandator__db_user"],
                        $l_dbdata["isys_mandator__db_pass"],
                        $l_dbdata["isys_mandator__db_name"]
                    );

                    $l_stats_dao         = isys_module_statistics::get_statistics_dao($l_database);
                    $l_lic[$i]["in_use"] = $l_stats_dao->count_objects();
                    $l_lic[$i]["database_instance"] &= $l_database;
                    $l_lic[$i]["mandator"] = $l_dbdata["isys_mandator__id"];
                }

            }

            // We need to decode the serialized data.
            $l_serialized_data = $l_row["isys_licence__data"];
            $l_data            = unserialize($l_serialized_data);

            if ($l_data === null)
            {
                $l_data = unserialize(isys_glob_replace_accent($l_serialized_data));
            } // if

            // And now we encode it again...
            $l_lic[$i]["parent_licence"] = $l_row["isys_licence__isys_licence__id"];
            $l_lic[$i]["licence_data"]   = $l_row["isys_licence__data"];
            $l_lic[$i]["id"]             = $l_row["isys_licence__id"];
            $l_lic[$i]["organisation"]   = $l_data[C__LICENCE__CUSTOMER_NAME];
            $l_lic[$i]["objcount"]       = $l_data[C__LICENCE__OBJECT_COUNT];
            $l_lic[$i]["database"]       = $l_data[C__LICENCE__DB_NAME];
            $l_lic[$i]["email"]          = $l_data[C__LICENCE__EMAIL];
            $l_lic[$i]["reg_date"]       = $l_data[C__LICENCE__REG_DATE];
            $l_lic[$i]["expires"]        = $l_row["isys_licence__expires"];
            $l_lic[$i]["uploaded"]       = $l_row["isys_licence__datetime"];
            $l_lic[$i]["type"]           = $l_data[C__LICENCE__TYPE];
            $l_lic[$i]["data"]           = [];

            if (isset($l_data[C__LICENCE__DATA]) && is_array($l_data[C__LICENCE__DATA]))
            {
                $l_lic[$i]["data"] = array_map('utf8_encode', $l_data[C__LICENCE__DATA]);
            } // if

            if ($l_data[C__LICENCE__OBJECT_COUNT] == 0) $l_lic[$i]["unlimited"] = true;
            else $l_lic[$i]["unlimited"] = false;

            if ($l_data[C__LICENCE__TYPE] == C__LICENCE_TYPE__SINGLE)
            {
                $l_lic[$i]["licencetype"] = "Subscription";
            }
            elseif ($l_data[C__LICENCE__TYPE] == C__LICENCE_TYPE__HOSTING_SINGLE)
            {
                $l_lic[$i]["licencetype"] = "Client";
            }
            elseif ($l_data[C__LICENCE__TYPE] == C__LICENCE_TYPE__HOSTING)
            {
                $l_lic[$i]["licencetype"] = "Multi-tenant";
            }
            elseif ($l_data[C__LICENCE__TYPE] == C__LICENCE_TYPE__BUYERS_LICENCE)
            {
                $l_lic[$i]["licencetype"] = "Buyers-Licence";
                unset($l_lic[$i]["expires"]);
            }
            elseif ($l_data[C__LICENCE__TYPE] == C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING)
            {
                $l_lic[$i]["licencetype"] = "Multi-tenant buyers-Licence";
                unset($l_lic[$i]["expires"]);

            }
            else $l_lic[$i]["licencetype"] = "Unknown";

        }

        return $l_lic;
    } // function

    /**
     * @param isys_component_database $p_database
     * @param                         $p_id
     *
     * @return bool
     */
    public function delete_licence(isys_component_database $p_database, $p_id)
    {
        $l_dao = new isys_component_dao($p_database);
        if ($l_dao->update("DELETE FROM isys_licence WHERE isys_licence__id = '" . $p_database->escape_string($p_id) . "';")) $l_dao->apply_update();

        return true;
    } // function

    /**
     * Deletes all licences of given type
     *
     * @param isys_component_database (system) $p_database
     * @param int|string                       $p_type
     * @param int                              $p_mandator_id
     *
     * @return bool
     */
    public function delete_licence_by_type(isys_component_database $p_database, $p_type, $p_mandator_id = null)
    {
        $l_dao = new isys_component_dao($p_database);

        $l_sql = "DELETE FROM isys_licence WHERE isys_licence__type = '" . $p_database->escape_string($p_type) . "'";

        if (!is_null($p_mandator_id))
        {
            $l_sql .= " AND isys_licence__isys_mandator__id = '" . $p_mandator_id . "'";
        }

        if (($l_update = $l_dao->update($l_sql)))
        {
            $l_rows = $l_dao->affected_after_update($l_update);

            return $l_dao->apply_update() ? $l_rows : false;
        }

        return false;
    } // function

    /**
     * Deletes all licences of given mandator.
     *
     * @param   isys_component_database $p_database
     * @param   integer                 $p_mandator_id
     *
     * @return  integer
     */
    public function delete_licence_by_mandator(isys_component_database $p_database, $p_mandator_id)
    {
        $l_dao = isys_component_dao::factory($p_database);

        $l_sql = "DELETE FROM isys_licence WHERE isys_licence__isys_mandator__id = " . $l_dao->convert_sql_id($p_mandator_id) . ";";

        if ($l_dao->update($l_sql))
        {
            $l_data = $l_dao->retrieve(
                "SELECT COUNT(isys_licence__id) AS count FROM isys_licence WHERE isys_licence__isys_mandator__id = " . $l_dao->convert_sql_id($p_mandator_id) . ";"
            )
                ->get_row();

            return (int) $l_data['count'];
        } // if

        return 0;
    } // function

    /**
     * Method to update an existing i-doit licence.
     *
     * @param   isys_component_database $p_database
     * @param   integer                 $p_licence_id
     * @param   mixed                   $p_data
     *
     * @return  boolean
     */
    public function update_licence(isys_component_database $p_database, $p_licence_id, $p_data)
    {
        $l_dao = new isys_component_dao($p_database);

        if (is_array($p_data)) $p_data = serialize($p_data);

        $l_sql = "UPDATE isys_licence SET " . "isys_licence__data = '" . $p_data . "' " . "WHERE isys_licence__id = '" . $p_licence_id . "';";

        return $l_dao->update($l_sql) && $l_dao->apply_update();
    }

    /**
     * Installs a new licence for a specific mandator.
     *
     * @param   isys_component_database $p_database
     * @param   array                   $p_data
     * @param   integer                 $p_mandator_id
     *
     * @throws isys_exception_licence
     * @return  boolean
     */
    public function install(isys_component_database $p_database, $p_data, $p_mandator_id = null, $p_parent_licence_id = null)
    {
        $l_dao = new isys_component_dao($p_database);

        // Licence prerequisites
        $l_days = round(abs((($p_data[C__LICENCE__RUNTIME] / 60 / 60 / 24))));
        if ($p_data[C__LICENCE__TYPE] == C__LICENCE_TYPE__BUYERS_LICENCE)
        {
            $l_expires = null;
        }
        else
        {
            $l_expires = strtotime("+{$l_days} days", $p_data[C__LICENCE__REG_DATE]);
        }
        $l_contract = isset($p_data[C__LICENCE__CONTRACT]) ? $p_data[C__LICENCE__CONTRACT] : '';

        // Install subscription licences
        $l_sql             = "SELECT * FROM isys_licence WHERE isys_licence__key = '" . $p_database->escape_string($p_data[C__LICENCE__KEY]) . "'";
        $l_existence_check = $l_dao->retrieve($l_sql);

        // Throw error, since licence is already installed.
        if ($l_existence_check->num_rows() > 0)
        {
            $l_error = _L("LC__LICENCE__INSTALL__FAIL_EXISTS");

            if (strpos($l_error, 'LC__') === 0)
            {
                $l_error = 'This license already exists';
            } // if

            throw new isys_exception_licence($l_error, LICENCE_ERROR_EXISTS);
        }
        else
        {

            if ($p_mandator_id === -1 || !$p_mandator_id)
            {
                // Install mandator licence
                $l_sql             = "SELECT * FROM isys_licence WHERE isys_licence__type = '%s';";
                $l_existence_check = $l_dao->retrieve(sprintf($l_sql, $p_data[C__LICENCE__TYPE]));

                if ($l_existence_check->num_rows() > 0)
                {
                    // Hosting licence exists
                    $l_hosting_licence_id = $l_existence_check->get_row_value('isys_licence__id');

                    // If object count is limited, check if this licence still fits with the installed ones.
                    if ($p_data[C__LICENCE__OBJECT_COUNT] > 0)
                    {
                        $l_object_count = $this->count_licenced_objects(null, [C__LICENCE_TYPE__HOSTING_SINGLE]);

                        if ($l_object_count > $p_data[C__LICENCE__OBJECT_COUNT])
                        {
                            throw new isys_exception_licence(
                                sprintf(
                                    'The total object limit of %s CMDB objects for your hosting licence is reached by an amount of %s. Please adjust your sublicenced tenants to fit the total amount of your tenant licence.',
                                    $p_data[C__LICENCE__OBJECT_COUNT],
                                    $l_object_count
                                ), LICENCE_ERROR_OBJECT_COUNT
                            );
                        }
                    }

                    // Update hosting licence..
                    $l_sql = "UPDATE isys_licence " . "SET " . "isys_licence__isys_mandator__id = %s, " . "isys_licence__isys_licence__id = %s, " . "isys_licence__expires = %s, " . "isys_licence__data = '%s', " . "isys_licence__type = '%s', " . "isys_licence__datetime = NOW(), " . "isys_licence__key = '%s', " . "isys_licence__contract = '%s' " . "WHERE isys_licence__id = '" . $l_hosting_licence_id . "';";

                    if ($l_dao->update(
                        sprintf(
                            $l_sql,
                            $l_dao->convert_sql_id($p_mandator_id),
                            $l_dao->convert_sql_id($p_parent_licence_id),
                            $l_dao->convert_sql_datetime($l_expires),
                            $p_database->escape_string(serialize($p_data)),
                            $p_data[C__LICENCE__TYPE],
                            $p_data[C__LICENCE__KEY],
                            $p_database->escape_string($l_contract)
                        )
                    )
                    )
                    {
                        // existing licence was updated
                        return $l_dao->apply_update();
                    }
                    else
                    {
                        // error installing licence
                        return false;
                    }

                }
                else
                {
                    // go further and install the licence (default behaviour)
                }
            }

            // Default behaviour: Licence does not exist. So install it:
            $l_sql = "INSERT INTO isys_licence " . "SET " . "isys_licence__isys_mandator__id = %s, " . "isys_licence__isys_licence__id = %s, " . "isys_licence__expires = '%s', " . "isys_licence__data = '%s', " . "isys_licence__type = '%s', " . "isys_licence__datetime = NOW(), " . "isys_licence__key = '%s', " . "isys_licence__contract = '%s' ";

            if ($l_dao->update(
                sprintf(
                    $l_sql,
                    $l_dao->convert_sql_id($p_mandator_id),
                    $l_dao->convert_sql_id($p_parent_licence_id),
                    $l_expires,
                    $p_database->escape_string(serialize($p_data)),
                    $p_data[C__LICENCE__TYPE],
                    $p_data[C__LICENCE__KEY],
                    $p_database->escape_string($l_contract)
                )
            )
            )
            {
                return $l_dao->apply_update();
            } // if

        }

        return false;
    } // function

    /**
     * Method for parsing the licence-file.
     *
     * @param  string                  $p_str
     * @param  isys_component_database $p_database
     * @param  array                   $p_options We can give some options to the parser
     *
     * @throws isys_exception_licence
     * @return mixed
     */
    private function parse($p_str, $p_database, array $p_options = [])
    {
        try
        {
            if (!strstr($p_str, 'i:') || !strstr($p_str, 'a:') || !strstr($p_str, 's:'))
            {
                if (($l_unzip = @gzuncompress($p_str)))
                {
                    $p_str = $l_unzip;
                } // if
            }
        }
        catch (ErrorException $e)
        {
            // $p_str may be already unpacked!
        }

        try
        {
            // We need to decode the licence data
            $l_licence = unserialize($p_str);
        }
        catch (ErrorException $e)
        {
            throw new isys_exception_licence($e->getMessage(), LICENCE_ERROR_SYSTEM);
        }

        if (is_array($l_licence))
        {
            if (isset($l_licence[C__LICENCE__TYPE]))
            {
                switch ($l_licence[C__LICENCE__TYPE])
                {
                    case C__LICENCE_TYPE__HOSTING_SINGLE:
                    case C__LICENCE_TYPE__SINGLE:

                        /* DB Name check */
                        if ($p_database)
                        {
                            if (isset($l_licence[C__LICENCE__DB_NAME]))
                            {
                                if ($l_licence[C__LICENCE__TYPE] == C__LICENCE_TYPE__HOSTING_SINGLE || $l_licence[C__LICENCE__TYPE] == C__LICENCE_TYPE__SINGLE)
                                {
                                    if ($p_database->get_db_name() != $l_licence[C__LICENCE__DB_NAME])
                                    {
                                        $l_message = _L("LC__LICENCE__ERROR_DB");

                                        if ($l_message == "LC__LICENCE__ERROR_DB")
                                        {
                                            $l_message = 'Your database name "%s" does not match the one in your licensing profile: "%s"';
                                        } // if

                                        $l_err = sprintf($l_message, $p_database->get_db_name(), $l_licence[C__LICENCE__DB_NAME]);
                                        throw new isys_exception_licence($l_err, LICENCE_ERROR_DB);
                                    } // if
                                } // if

                                if (isset($l_licence[C__LICENCE__OBJECT_COUNT]))
                                {
                                    if (is_numeric($l_licence[C__LICENCE__OBJECT_COUNT]) && $l_licence[C__LICENCE__OBJECT_COUNT] >= 0)
                                    {
                                        if ($l_licence[C__LICENCE__OBJECT_COUNT] > 0)
                                        {
                                            if (defined("C__OBJTYPE__RELATION") && defined("C__OBJTYPE__PARALLEL_RELATION"))
                                            {
                                                $l_condition = "isys_obj__isys_obj_type__id NOT IN ('" . C__OBJTYPE__RELATION . "', '" . C__OBJTYPE__PARALLEL_RELATION . "'";

                                                if (defined("C__OBJTYPE__NAGIOS_SERVICE") && defined("C__OBJTYPE__NAGIOS_SERVICE_TPL") && defined(
                                                        "C__OBJTYPE__NAGIOS_HOST_TPL"
                                                    )
                                                )
                                                {
                                                    $l_condition .= ", '" . C__OBJTYPE__NAGIOS_SERVICE . "', '" . C__OBJTYPE__NAGIOS_SERVICE_TPL . "', '" . C__OBJTYPE__NAGIOS_HOST_TPL . "'";
                                                } // if

                                                $l_condition .= ") ";
                                            }
                                            else
                                            {
                                                $l_condition = "isys_obj__isys_obj_type__id NOT IN (" . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__RELATION'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__PARALLEL_RELATION'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__NAGIOS_SERVICE'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__NAGIOS_HOST_TPL'), " . "(SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = 'C__OBJTYPE__NAGIOS_SERVICE_TPL') " . ")";
                                            } // if

                                            $l_q = '';

                                            foreach (explode(
                                                         "-",
                                                         "83-69-76-69-67-84-32-42-32-70-82-79-77-32-105-115-121-115-95-111-98-106-32-87-72-69-82-69-32-105-115-121-115-95-111-98-106-95-95-115-116-97-116-117-115-32-61-32-39-50-39"
                                                     ) as $l_c)
                                            {
                                                $l_q .= chr($l_c);
                                            }

                                            if (($l_count = $p_database->num_rows(
                                                    $p_database->query($l_q . " AND " . $l_condition . ";")
                                                )) > $l_licence[C__LICENCE__OBJECT_COUNT]
                                            )
                                            {
                                                $l_message = _L("LC__LICENCE__OBJECT_COUNT_REACHED");

                                                if ($l_message == "LC__LICENCE__OBJECT_COUNT_REACHED")
                                                {
                                                    $l_message = 'The object limit of %s CMDB objects is reached by an amount of %s.';
                                                } // if

                                                throw new isys_exception_licence(
                                                    sprintf($l_message, $l_licence[C__LICENCE__OBJECT_COUNT], $l_count), LICENCE_ERROR_OBJECT_COUNT
                                                );
                                            } // if
                                        } // if
                                    }
                                    else
                                    {
                                        throw new isys_exception_licence("Error while checking licence count", LICENCE_ERROR_OBJECT_COUNT);
                                    } // if
                                } // if
                            }
                            else
                            {
                                throw new isys_exception_licence("Could not read database name from licence file.", LICENCE_ERROR_NO_DB);
                            } // if
                        }
                        else
                        {
                            throw new isys_exception_licence("Database connection error.", LICENCE_ERROR_SYSTEM);
                        }

                        break;
                    case C__LICENCE_TYPE__BUYERS_LICENCE:
                    case C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING:

                        // We don't check for databases in buyers licences.

                        break;
                    case C__LICENCE_TYPE__HOSTING:
                        if (is_object($p_database))
                        {
                            throw new isys_exception_licence("Cannot install hosting licence inside mandator.", LICENCE_ERROR_DB);
                        }
                        break;
                    default:

                        throw new isys_exception_licence("Invalid licence type", LICENCE_ERROR_INVALID_TYPE);

                        break;
                }

                // We check if the licence start-time is already reached.
                if (time() < $l_licence[C__LICENCE__REG_DATE])
                {
                    if (isset($p_options['check_start_date']) && $p_options['check_start_date'] === false)
                    {
                        // We don't have to throw this exception.
                    }
                    else
                    {
                        $l_message = _L("LC__LICENCE__ERROR_REG_DATE");

                        if ($l_message == 'LC__LICENCE__ERROR_REG_DATE')
                        {
                            $l_message = 'The registration date of your license is higher than the current date on your server!';
                        } // if

                        throw new isys_exception_licence($l_message, LICENCE_ERROR_REG_DATE);
                    } // if
                } // if

                // Key check.
                $l_tmp = $l_licence;
                unset($l_tmp[C__LICENCE__KEY]);
                $l_sha1 = sha1(serialize($l_tmp));

                if ($l_sha1 != $l_licence[C__LICENCE__KEY])
                {
                    throw new isys_exception_licence(_L("Licence key invalid."), LICENCE_ERROR_KEY);
                } // if

                // Runtime check.
                if (isset($l_licence[C__LICENCE__RUNTIME]))
                {
                    if (($l_licence[C__LICENCE__REG_DATE] + $l_licence[C__LICENCE__RUNTIME]) < time())
                    {
                        $l_message = _L("LC__LICENCE__ERROR_OVERTIME");

                        if ($l_message == 'LC__LICENCE__ERROR_OVERTIME')
                        {
                            $l_message = 'Your license has expired. Please update your license on <a href="http://login.i-doit.com">login.i-doit.com</a> or contact <a href="mailto:sales@i-doit.com?subject=i-doit license renewal">sales@i-doit.com</a>.';
                        } // if

                        throw new isys_exception_licence($l_message, LICENCE_ERROR_OVERTIME);
                    } // if
                }

                return $l_licence;
            }
            else
            {
                throw new isys_exception_licence('Your provided an invalid licence.', LICENCE_ERROR_INVALID);
            }
        }
        else
        {
            throw new isys_exception_licence('You provided an unreadable licence.', LICENCE_ERROR_UNREADABLE);
        }
    } // function

    /**
     * @param null $p_mandator_id
     *
     * @return int
     * @throws Exception
     * @throws isys_exception_database
     */
    private function count_licenced_objects($p_by_mandator_id = null, array $p_with_licence_types = [])
    {
        $l_dao = new isys_component_dao(isys_application::instance()->database_system);

        $l_condition = '';
        $l_condition .= $p_by_mandator_id ? ' AND isys_licence__isys_mandator__id = ' . $l_dao->convert_sql_id($p_by_mandator_id) : '';
        $l_condition .= count($p_with_licence_types) ? ' AND isys_licence__type IN(' . implode(',', $p_with_licence_types) . ')' : '';

        $l_object_count = 0;
        $l_sub_licences = $l_dao->retrieve('SELECT * FROM isys_licence WHERE TRUE' . $l_condition . ';')
            ->__as_array();
        foreach ($l_sub_licences as $l_sub)
        {
            $l_data = unserialize($l_sub['isys_licence__data']);
            if (is_array($l_data))
            {
                $l_object_count += $l_data[C__LICENCE__OBJECT_COUNT];
            }
        }

        return $l_object_count;
    } // function
} // class