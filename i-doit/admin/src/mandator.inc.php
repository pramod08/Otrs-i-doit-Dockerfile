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
 * @author     Dennis Stuecken
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
if (!C__ENABLE__LICENCE)
{
    throw new Exception(
        "Tenant pages are not available in this i-doit version! " . "You need to buy a subscription licence at <a href=\"http://www.i-doit.com\">http://www.i-doit.com</a>."
    );
}

global $g_absdir, $g_comp_template, $g_db_system;
define("DUMPFILE", $g_absdir . "/setup/sql/idoit_data.sql");

global $g_comp_database_system;
$l_dao_mandator = new isys_component_dao_mandator($g_comp_database_system);

if (file_exists($g_absdir . "/setup/functions.inc.php"))
{
    include_once $g_absdir . "/setup/functions.inc.php";
} // if

try
{
    $l_error = false;

    switch ($_GET["action"])
    {
        case "edit":
            error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

            if ($_POST["mandator_title"])
            {
                $l_message = '';

                try
                {
                    if (!isset($_POST["id"]))
                    {
                        throw new Exception("Unknown error. Dataset not found! Try reloading this page!");
                    } // if

                    if ($_POST["change_pass"])
                    {
                        if ($_POST["mandator_password"] != $_POST["mandator_password2"])
                        {
                            throw new Exception("Error: Passwords not equal.");
                        } // if
                    }
                    else
                    {
                        $_POST["mandator_password"] = $l_dao_mandator->get_mandator($_POST["id"])
                            ->get_row_value('isys_mandator__db_pass');
                    } // if

                    $l_bIP = preg_match("/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]/", $_POST["mandator_db_host"]);
                    if (($l_bIP && !gethostbyaddr($_POST["mandator_db_host"])) || (!$l_bIP && !gethostbyname($_POST["mandator_db_host"])))
                    {
                        throw new Exception("Connection failed. Host not reachable! Check your MySQL Host setting.");
                    } // if

                    // Check if Database is already in use by another tenant
                    if (($l_in_use = $l_dao_mandator->get_mandator_id_by_db_name($_POST["mandator_database"])))
                    {
                        if ($l_in_use != $_POST["id"])
                        {
                            throw new Exception("Tenant data can not be saved. Database Name is already in use.");
                        } // if
                    }

                    // Close session so that the request can be aborted when the next check waits for a timeout.
                    session_write_close();

                    try
                    {
                        $l_db_check = isys_component_database::get_database(
                            $g_db_system['type'],
                            $_POST["mandator_db_host"],
                            $_POST["mandator_db_port"],
                            $_POST["mandator_username"],
                            $_POST["mandator_password"],
                            $_POST["mandator_database"]
                        );
                    }
                    catch (Exception $e)
                    {
                        $error = mysqli_connect_error();
                        if (!$error)
                        {
                            $error = 'Unknown error. Check your database access rights for user ' . $_POST["mandator_username"];
                        }

                        throw new Exception("Could not connect to database (" . $error . "). Check the database name and connection parameters.");
                    } // try

                    $l_sql = "UPDATE isys_mandator SET
						isys_mandator__title = " . $l_dao_mandator->convert_sql_text($_POST["mandator_title"]) . ",
						isys_mandator__description = " . $l_dao_mandator->convert_sql_text($_POST["mandator_description"]) . ",
						isys_mandator__db_host = " . $l_dao_mandator->convert_sql_text($_POST["mandator_db_host"]) . ",
						isys_mandator__db_port = " . $l_dao_mandator->convert_sql_int($_POST["mandator_db_port"]) . ",
						isys_mandator__db_name = " . $l_dao_mandator->convert_sql_text($_POST["mandator_database"]) . ",
						isys_mandator__dir_cache = " . $l_dao_mandator->convert_sql_text('cache_' . filter_directory_name($_POST["mandator_cache_dir"])) . ",
						isys_mandator__sort = " . $l_dao_mandator->convert_sql_int($_POST["mandator_sort"]) . ",
						isys_mandator__db_user = " . $l_dao_mandator->convert_sql_text($_POST["mandator_username"]);

                    if ($_POST["change_pass"])
                    {
                        $l_sql .= ", isys_mandator__db_pass = " . $l_dao_mandator->convert_sql_text($_POST["mandator_password"]);
                    } // if

                    $l_sql .= "WHERE isys_mandator__id = " . $l_dao_mandator->convert_sql_id($_POST["id"]) . ";";

                    if ($g_comp_database_system->query($l_sql))
                    {
                        $l_message = "Successfully updated.";
                    } // if
                }
                catch (Exception $e)
                {
                    $l_error   = true;
                    $l_message = $e->getMessage();
                } // try

                $l_response = [
                    "error"   => $l_error,
                    "message" => $l_message
                ];

                header("Content-Type: application/json");
                echo json_encode($l_response);

                die;
            } // if

            $l_tenant        = $l_dao_mandator->get_mandator($_POST["id"], 0);
            $l_data_mandator = $l_tenant->get_row();

            $g_comp_template->assign("mandator_data", $l_data_mandator);
            $g_comp_template->display($g_absdir . "/admin/templates/pages/mandator_edit.tpl");
            die;
            break;
        case "activate":
        case "deactivate":
        case "delete":

            $l_ids = json_decode(stripslashes($_POST["ids"]));

            /* Delete database(s) */
            if (is_array($l_ids) && count($l_ids) > 0)
            {
                foreach ($l_ids as $l_id)
                {
                    if ($_GET["action"] == "delete")
                    {

                        $l_res_mandator  = $l_dao_mandator->get_mandator($l_id, 0);
                        $l_data_mandator = $l_res_mandator->get_row();

                        if ($l_data_mandator["isys_mandator__db_name"])
                        {
                            $g_comp_database_system->query("DROP DATABASE IF EXISTS `" . $l_data_mandator["isys_mandator__db_name"] . "`;");

                            if ($l_dao_mandator->delete($l_id))
                            {
                                $l_message = "Tenant(s) successfully deleted.";
                                $l_error   = false;
                            }
                        }
                        else
                        {
                            $l_message = "Tenant with id '" . $l_id . "' not found.";
                            $l_error   = false;
                        }

                    }
                    else if ($_GET["action"] == "deactivate")
                    {
                        $l_res_mandator  = $l_dao_mandator->get_mandator();
                        $l_data_mandator = $l_dao_mandator->get_mandator($l_id, 0)
                            ->get_row();

                        if ($l_data_mandator["isys_mandator__active"] == 1)
                        {
                            if ($l_res_mandator->num_rows() == 1)
                            {
                                $l_message = "At least one mandator has to be active.";
                                $l_error   = true;
                            }
                            else
                            {
                                if ($l_dao_mandator->deactivate_mandator($l_id))
                                {
                                    $l_message = "Tenant(s) successfully deactivated.";
                                    $l_error   = false;
                                }
                            }
                        }
                        elseif (!$l_error)
                        {
                            $l_message = "Tenant(s) already deactivated.";
                            $l_error   = true;
                        }

                    }
                    else if ($_GET["action"] == "activate")
                    {
                        if ($l_dao_mandator->activate_mandator($l_id))
                        {
                            $l_message = "Tenant(s) successfully activated.";
                            $l_error   = false;
                        }
                    }
                }

            }
            else
            {
                $l_message = "No tenants(s) selected. Nothing done.";
                $l_error   = true;
            }

            $l_response = [
                "error"   => $l_error,
                "message" => $l_message
            ];

            header("Content-Type: application/json");
            echo json_encode($l_response);

            die;

            break;
        case "list":
            $l_tenants = $l_dao_mandator->get_mandator(null, 0);
            $g_comp_template->assign("mandators", $l_tenants);
            $g_comp_template->display($g_absdir . "/admin/templates/pages/mandator_list.tpl");
            die;
            break;
        case "add":

            if ($_POST["mandator_username"])
            {

                /* Get highest sort value */
                $l_mtmp      = $l_dao_mandator->retrieve("SELECT MAX(isys_mandator__sort) AS sort FROM isys_mandator;");
                $l_sort_data = $l_mtmp->get_row();
                $l_sort      = $l_sort_data["sort"];

                $l_tenant_username = $_POST["mandator_username"];

                if ($_POST["mandator_password"] == $_POST["mandator_password2"])
                {
                    $l_tenant_pass = $_POST["mandator_password"];
                }
                else throw new Exception("Passwords not equal");

                $l_tenant_title      = $_POST["mandator_title"];
                $l_data_mandatorbase = $_POST["mandator_database"];
                $l_tenant_autoinc    = $_POST['mandator_autoinc'];

                global $g_config, $g_dbLink;

                if (isset($_POST["root_pw"]))
                {
                    $g_db_system["user"] = "root";
                    $g_db_system["pass"] = $_POST["root_pw"];
                }

                try
                {
                    /* Connection to system database */
                    $g_dbLink = new mysqli(
                        $g_db_system["host"], $g_db_system["user"], $g_db_system["pass"], $g_db_system["name"], $g_db_system["port"]
                    );
                }
                catch (Exception $e)
                {
                    throw new Exception("Could not connect to mysql database. Check your root password. " . $e->getMessage());
                }

                if ($g_dbLink)
                {
                    $g_dbLink->query("SET sql_mode='';");

                    if ($_POST["addNewDatabase"] == "1")
                    {

                        if (!$g_dbLink->query("CREATE DATABASE `" . $l_data_mandatorbase . "` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;"))
                        {
                            throw new Exception("Error creating database: " . $g_dbLink->error);
                        } // if

                        $l_output = "";

                        if (!mysql_import($l_data_mandatorbase, DUMPFILE, $l_output, $g_dbLink))
                        {
                            throw new Exception("Error while importing database: " . $g_dbLink->error . "<br />" . $l_output);
                        }
                        else
                        {
                            if (is_numeric($l_tenant_autoinc) && (int) $l_tenant_autoinc > 0)
                            {
                                if (!$g_dbLink->query("ALTER TABLE $l_data_mandatorbase.isys_obj AUTO_INCREMENT = " . (int) $l_tenant_autoinc . ";")) throw new Exception(
                                    "Unable to set Auto-Increment start value"
                                );
                            }

                            $l_message = "Database \"<strong>" . $l_data_mandatorbase . "</strong>\" and mandator \"<strong>" . $l_tenant_title . "</strong>\" successfully created.";
                        } // if
                    }
                    else
                    {
                        $l_message = "Tenant \"<strong>" . $l_tenant_title . "</strong>\" successfully created.";
                    } // if

                    // Adding mandator.
                    $l_result = add_mandator(
                        $l_tenant_title,
                        "",
                        $l_tenant_title,
                        "default",
                        $g_db_system["host"],
                        $g_db_system["port"],
                        $l_data_mandatorbase,
                        $l_tenant_username,
                        $l_tenant_pass,
                        $l_sort + 1,
                        $g_db_system["name"],
                        $g_dbLink
                    );

                    if (!$l_result)
                    {
                        throw new Exception("Error while creating new tenant: " . $g_dbLink->error);
                    } // if

                    // Granting permissions to *.
                    $l_grant = "GRANT ALL " . "ON " . $l_data_mandatorbase . ".* " . "TO '" . $l_tenant_username . "'@'%'";

                    if ($l_tenant_pass != "")
                    {
                        $l_grant .= " IDENTIFIED BY '" . $l_tenant_pass . "'";
                    } // if

                    $l_grant .= ";";

                    if (!$g_dbLink->query($l_grant))
                    {
                        throw new Exception("Error granting permissions: " . $g_dbLink->error);
                    } // if

                    // Granting permissions to localhost.
                    $l_grant = "GRANT ALL " . "ON " . $l_data_mandatorbase . ".* " . "TO '" . $l_tenant_username . "'@'localhost'";

                    if ($l_tenant_pass != "")
                    {
                        $l_grant .= " IDENTIFIED BY '" . $l_tenant_pass . "'";
                    } // if

                    $l_grant .= ";";

                    if (!$g_dbLink->query($l_grant))
                    {
                        throw new Exception("Error granting permissions: " . $g_dbLink->error);
                    } // if

                    // All done.
                    $l_response = [
                        "error"   => false,
                        "message" => $l_message
                    ];
                    header("Content-Type: application/json");
                    echo json_encode($l_response);
                    die;
                }
                else
                {
                    throw new Exception('Could not connect. Please verify your MySQL credentials.');
                }

            } // if
            break;
        default:

            break;
    } // switch
}
catch (Exception $e)
{
    $l_response = [
        "error"   => true,
        "message" => $e->getMessage()
    ];

    header("Content-Type: application/json");
    echo json_encode($l_response);
    die;
} // try

$l_tenants = $l_dao_mandator->get_mandator(null, 0);

$g_comp_template->assign("mandators", $l_tenants);
$g_comp_template->assign("db_conf", $g_db_system);