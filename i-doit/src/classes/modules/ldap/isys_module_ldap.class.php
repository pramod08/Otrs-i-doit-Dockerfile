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
define("C__LDAPPAGE__CONFIG", 1);
define("C__LDAPPAGE__SERVERTYPES", 2);
define("C__LDAPPAGE__CONINFO", 3);

define("C__LDAP_MAPPING__GROUP", 0);
define("C__LDAP_MAPPING__OBJECT_CLASS", 1);
define("C__LDAP_MAPPING__FIRSTNAME", 2);
define("C__LDAP_MAPPING__LASTNAME", 3);
define("C__LDAP_MAPPING__MAIL", 4);
define("C__LDAP_MAPPING__USERNAME", 5);
define("C__LDAP_MAPPING__DESCRIPTION", 6);

define("C__DEFAULT__TIMELIMIT", 30);

/**
 * i-doit
 *
 * LDAP-Module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_ldap extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * @var \Monolog\Logger
     */
    private static $m_logger = null;
    /**
     * @var array
     */
    private $m_default_attributes = [
        "cn",
        "description"
    ];
    /**
     * @var isys_library_ldap
     */
    private $m_ldap = null;
    /**
     * @var isys_module_request
     */
    private $m_userrequest;

    /**
     * Writes a debug message
     *
     * @param string $p_message
     */
    public static function debug($p_message)
    {
        if (C__LDAP__DEBUG)
        {
            self::get_logger()
                ->addDebug($p_message);
        }
    } // function

    /**
     * @return \Monolog\Logger
     */
    public static function get_logger()
    {
        if (self::$m_logger === null)
        {
            self::$m_logger = new \Monolog\Logger('ldap');
            self::$m_logger->pushHandler(
                new \Monolog\Handler\StreamHandler(isys_application::instance()->app_path . DS . "log" . DS . "ldap", \Monolog\Logger::DEBUG)
            );
        }

        return self::$m_logger;
    } // function

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
        global $g_dirs;
        $l_root      = -1;
        $l_submodule = '';

        if ($p_system_module)
        {
            $l_root      = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LDAP;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            $l_root = $p_tree->add_node(
                C__MODULE__LDAP . '0',
                $l_root,
                'LDAP'
            );
        } // if

        if (!isset($_GET[C__GET__SETTINGS_PAGE]))
        {
            $_GET[C__GET__SETTINGS_PAGE] = C__LDAPPAGE__CONFIG;
        } // if

        $p_tree->add_node(
            C__MODULE__LDAP . C__LDAPPAGE__CONFIG,
            $l_root,
            'Server',
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG . '&' . C__GET__SETTINGS_PAGE . '=' . C__LDAPPAGE__CONFIG . '&' . C__CMDB__GET__EDITMODE . "=" . C__EDITMODE__ON,
            null,
            $g_dirs['images'] . "icons/tree/stammdaten.gif",
            ($_GET[C__GET__SETTINGS_PAGE] == C__LDAPPAGE__CONFIG && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__LDAP) ? 1 : 0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG)
        );

        $p_tree->add_node(
            C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES,
            $l_root,
            'Directories',
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES . '&' . C__GET__SETTINGS_PAGE . "=" . C__LDAPPAGE__SERVERTYPES,
            null,
            $g_dirs['images'] . "icons/tree/folderopen.gif",
            ($_GET[C__GET__SETTINGS_PAGE] == C__LDAPPAGE__SERVERTYPES && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__LDAP) ? 1 : 0,
            '',
            '',
            isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES)
        );
    } // function

    /**
     * Returns dao instance.
     *
     * @param isys_component_database $p_database
     *
     * @return isys_ldap_dao
     */
    public function get_dao(&$p_database = null)
    {
        global $g_comp_database;

        if (is_null($p_database))
        {
            $l_database = $g_comp_database;
        }
        else
        {
            $l_database = $p_database;
        } // if

        if (!class_exists('isys_ldap_dao'))
        {
            include_once('init.php');
        }

        return new isys_ldap_dao($l_database);
    } // function

    /**
     * Starts module process
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        // Unpack request package.
        $l_gets = $this->m_userrequest->get_gets();

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_template = $this->m_userrequest->get_template();
            $l_tree     = $this->m_userrequest->get_menutree();

            $this->build_tree($l_tree, false);

            $l_template->assign("menu_tree", $l_tree->process(0));
        } // if

        $this->process($l_gets);
    }

    /**
     * Return key of serialized mapping
     *
     * @param   string $p_key
     * @param   string $p_mapping
     *
     * @return  string
     */
    public function get_mapping($p_key, $p_mapping)
    {
        $l_mapping = unserialize($p_mapping);

        if (isset($l_mapping[$p_key]))
        {
            return $l_mapping[$p_key];
        }
        else
        {
            return false;
        } // if
    }

    /**
     * Initializes the module
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_ldap
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;

        return $this;
    }

    /**
     * i-doit Session login. Returns a valid i-doit user id by giving ldap userlogin data.
     *
     * @param   string                                 $p_username
     * @param   string                                 $p_password
     * @param   null                                   $p_userdn
     * @param   isys_cmdb_dao_category_s_person_master $p_user_dao
     *
     * @throws  Exception
     * @return  boolean
     */
    public function session_login($p_username, $p_password, $p_userdn = null, isys_cmdb_dao_category_s_person_master $p_user_dao = null)
    {
        // User-DAO.
        if (is_null($p_user_dao))
        {
            $l_user_dao = isys_cmdb_dao_category_s_person_master::instance(isys_application::instance()->database);
        }
        else
        {
            $l_user_dao = &$p_user_dao;
        } // if

        try
        {
            // Try the ldap-login (bind).
            $l_found_user = $this->ldap_login(
                $l_user_dao->get_database_component(),
                $p_username,
                $p_password,
                $p_userdn,
                null,
                $l_user_dao->get_person_id_by_username($p_username)
            );

            // User found and Ldap-Bind was OK.
            if (is_array($l_found_user))
            {
                // Get UserDN.
                $l_dn = $l_found_user["dn"];

                // Checks if the user exists in i-doit.
                if (($l_user_id = $l_user_dao->exists($p_username)))
                {
                    /* Ldap bind was successfull and user was found in i-doit. Nice. */
                    // Reassign person group
                    $this->attach_groups_to_user(
                        $l_user_id,
                        $this->ldap_get_groups($l_found_user),
                        $l_user_dao
                    );

                    return $l_user_id;
                }
                else
                    /* User was not found in i-doit. So create it. */
                {
                    $this->debug(
                        'Creating User: ' . $l_found_user[C__LDAP_MAPPING__FIRSTNAME] . ' ' . $l_found_user[C__LDAP_MAPPING__LASTNAME] . ' with username ' . $p_username
                    );

                    /**
                     *  Auth was OK, now we create the user as an internal contact
                     *  with an ldap dn reference
                     */
                    $l_user_id = $l_user_dao->create(
                        null,
                        $p_username,
                        $l_found_user[C__LDAP_MAPPING__FIRSTNAME],
                        $l_found_user[C__LDAP_MAPPING__LASTNAME],
                        $l_found_user[C__LDAP_MAPPING__MAIL],
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        $l_found_user[C__LDAP_MAPPING__DESCRIPTION],
                        $l_found_user["ldap_data"]["isys_ldap__id"],
                        $l_dn
                    );

                    if (is_numeric($l_user_id) && $l_user_id > 0)
                    {
                        $this->debug("User account created. User-ID: " . $l_user_id);

                        /* Now, also attach the user into ldap enabled i-doit groups.
                            Note: 	Attaching does only work, if the ldap group has got the same name
                                    as the i-doit GROUP LDAP MAPPING.
                                    This mapping is defined in the detail view of the corresponding group
                                    under contacts -> groups -> groupname */

                        $this->attach_groups_to_user(
                            $l_user_id,
                            $this->ldap_get_groups($l_found_user),
                            $l_user_dao,
                            true
                        );

                    }
                    else
                    {
                        $this->debug("Unknown error while creating the LDAP-User - Received an empty user-id.");
                    }

                    return $l_user_id;
                }

            }
            else if ($l_found_user == true)
            {

                $this->debug("--- LDAP-Login succeeded. Granting access..");

                return true;

            }
            else
            {
                $this->debug("*** LDAP Auth failed. (" . var_export($l_found_user, true) . ")");
                $l_user_id = 0;
            }

        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $l_user_id;
    }

    /**
     * Attaches the user into found ldap groups
     *
     * @param int                                    $p_user_id
     * @param array                                  $p_groups
     * @param isys_cmdb_dao_category_s_person_master $p_user_dao
     *
     * @return bool
     */
    public function attach_groups_to_user($p_user_id, $p_groups, isys_cmdb_dao_category_s_person_master $p_user_dao, $p_user_created = false)
    {

        if (is_array($p_groups))
        {

            $this->debug("Syncing groups..");

            /**
             * Detach all groups first
             */
            $p_user_dao->detach_groups($p_user_id, null, ' AND isys_person_2_group__ldap = 1', $p_user_created);

            /**
             * Then attach the ldap group pendents to user
             */
            foreach ($p_groups as $l_group_data)
            {

                if (is_numeric($l_group_data["isys_cats_person_group_list__isys_obj__id"]))
                {
                    //$this->debug(" Trying to attach user({$p_user_id}) to group: " . $l_group_data["isys_cats_person_group_list__title"]);
                    if ($p_user_dao->attach_group($p_user_id, trim($l_group_data["isys_cats_person_group_list__isys_obj__id"]), '1'))
                    {
                        $this->debug(" User ({$p_user_id}) successfully attached to group: " . $l_group_data["isys_cats_person_group_list__title"]);
                    }
                    else
                    {
                        $this->debug(" Failed attaching user({$p_user_id}) to group: " . $l_group_data["isys_cats_person_group_list__title"]);
                    }
                }

            }

            return true;
        }

        return false;
    }

    /**
     * Trys to login into all configured ldap-servers for $p_database.
     * If a login succeeds, the ldap attributes of the corresponding user are returned.
     *
     * @param isys_component_database $p_database
     * @param string                  $p_username
     * @param string                  $p_password
     * @param null                    $p_dn
     * @param null                    $p_ldap_server
     * @param null                    $p_user_id
     *
     * @throws Exception
     * @return array|boolean
     */
    public function ldap_login(&$p_database, $p_username, $p_password, $p_dn = null, $p_ldap_server = null, $p_user_id = null)
    {

        if (is_object($p_database))
        {

            /* Get Servers and configured admin-authentication */
            $l_ldap_dao = $this->get_dao($p_database);
            $l_servers  = $l_ldap_dao->get_active_servers($p_ldap_server);
            $i          = 1;

            $this->debug('----------------------------------------------------------------------------------------------');
            $this->debug("LDAP Module launched for mandator: " . $p_database->get_db_name());

            /* Iterate through configured servers */
            if (is_object($l_servers))
            {

                $this->debug("Found " . $l_servers->num_rows() . " configured LDAP Servers.");

                while ($l_ldap = $l_servers->get_row())
                {

                    try
                    {

                        $this->debug($i++ . ": " . $l_ldap["isys_ldap__hostname"] . " (" . $l_ldap["isys_ldap__user_search"] . ")");
                        $this->debug("----------------------------------------------------------------------------------------------");

                        /* Connect to LDAP-Server and get the internal ldap library */
                        $this->m_ldap = $this->get_library(
                            $l_ldap["isys_ldap__hostname"],
                            $l_ldap["isys_ldap__dn"],
                            $l_ldap["isys_ldap__password"],
                            $l_ldap["isys_ldap__port"],
                            $l_ldap["isys_ldap__version"],
                            $l_ldap["isys_ldap__tls"]
                        );

                        if ($this->m_ldap->is_connected())
                        {
                            /* OpenLDAP Fix */
                            if ($l_ldap["isys_ldap_directory__const"] == "C__LDAP__OPENLDAP")
                            {
                                $l_user_mapping = $this->get_mapping(
                                    C__LDAP_MAPPING__USERNAME,
                                    $l_ldap["isys_ldap_directory__mapping"]
                                );
                                if ($l_user_mapping)
                                {
                                    $this->m_ldap->set_idattribute($l_user_mapping);
                                }
                                else
                                {
                                    $this->m_ldap->set_idattribute("uid");
                                }
                            }

                            if (empty($p_dn))
                            {
                                $this->debug("Searching for username: {$p_username}");

                                if (($l_found_user = $this->m_ldap->get_user($p_username, $l_ldap)))
                                {

                                    $this->debug("Found DN: " . $l_found_user["dn"] . ". Trying to login with it.");

                                    $l_found_user["ldap_data"] = &$l_ldap;
                                    $l_found_user["ldapi"]     = &$this->m_ldap;

                                    /* Try to authenticate with entered username and password */
                                    if (!empty($l_found_user["dn"]) && $this->m_ldap->try_auth($l_found_user["dn"], $p_password))
                                    {

                                        $this->debug("Auth successfull (" . $l_found_user["dn"] . ").");

                                        return $l_found_user;

                                    }
                                    else
                                    {
                                        if ($this->m_ldap->get_ldap_error() != "Success")
                                        {
                                            $l_ldap_result = " LDAP-Result: " . $this->m_ldap->get_ldap_error();
                                        }
                                        else $l_ldap_result = "";

                                        $this->debug("** Auth failed." . $l_ldap_result);
                                    }

                                }
                                else
                                {
                                    $this->debug(
                                        "User not found. Check if {$p_username} " . "exist in your configured search-path: " . $l_ldap["isys_ldap__user_search"]
                                    );
                                }
                            }
                            else
                            {

                                $this->debug("Trying to auth with DN: " . $p_dn);
                                if ($this->m_ldap->try_auth($p_dn, $p_password))
                                {
                                    $this->debug(" + " . $p_dn . " / " . $p_username . " authenticated.");
                                    $_SESSION["username"] = $p_username;

                                    // Sync groups
                                    if ($p_user_id > 0)
                                    {
                                        if (($l_found_user = $this->m_ldap->get_user($p_username, $l_ldap)))
                                        {

                                            $l_found_user["ldap_data"] = &$l_ldap;
                                            $l_found_user["ldapi"]     = &$this->m_ldap;

                                            // do not instantiate the object via factory. Because the object uses
                                            // the wrong mandator database
                                            $this->attach_groups_to_user(
                                                $p_user_id,
                                                $this->ldap_get_groups($l_found_user),
                                                new isys_cmdb_dao_category_s_person_master($p_database)
                                            );
                                        }
                                    }

                                    /* AUTH SUCCEEDED */
                                    $this->debug("----------------------------------------------------------------------------------------------");

                                    return true;

                                }
                                else
                                {
                                    if ($this->m_ldap->get_ldap_error() != "Success")
                                    {
                                        $l_ldap_result = " LDAP-Result: " . $this->m_ldap->get_ldap_error();
                                    }
                                    else $l_ldap_result = "";

                                    $this->debug($p_dn . " / " . $p_username . " auth failed. " . $l_ldap_result);

                                    /* AUTH FAILED */
                                }

                            }

                            $this->debug("----------------------------------------------------------------------------------------------");

                        }
                        else throw new Exception("LDAP-Connection Error");
                    }
                    catch (Exception $e)
                    {
                        $this->debug($e->getMessage());
                    }

                }

            }
            else
            {
                throw new Exception("No active LDAP servers found.");
            }
        }

        return false;
    }

    /**
     * Resolves groups from ldap memberof array
     *
     * @param array $p_found_user
     *
     * @return array
     */
    public function ldap_get_groups($p_found_user)
    {
        $l_return = [];

        $this->debug("Getting groups of {$p_found_user["dn"]} (Servertype: " . $p_found_user["ldap_data"]["isys_ldap_directory__title"] . ")");

        /**
         * @var ldapi_acc
         */
        $l_ldapi = $p_found_user["ldapi"] ? $p_found_user["ldapi"] : new ldapi_acc();

        $l_mapping    = unserialize($p_found_user["ldap_data"]["isys_ldap_directory__mapping"]);
        $l_group_attr = $l_mapping[C__LDAP_MAPPING__USERNAME];

        if (is_object($l_ldapi))
        {
            switch ($p_found_user["ldap_data"]["isys_ldap_directory__const"])
            {

                case "C__LDAP__OPENLDAP":

                    $this->debug(" Attention: OpenLDAP connections are experimental!");

                    /**
                     * Determine search path for groups
                     */
                    $l_group_search = $p_found_user["ldap_data"]["isys_ldap__group_search"];
                    if (!$l_group_search) $l_group_search = $l_ldapi->get_search_path();
                    $l_ldapi->set_search_path($l_group_search);

                    if ($p_found_user[C__LDAP_MAPPING__USERNAME] || $p_found_user[$l_mapping[C__LDAP_MAPPING__USERNAME]])
                    {
                        $l_group_data = $l_ldapi->search(
                            $l_ldapi->get_search_path(),
                            "(" . $l_mapping[C__LDAP_MAPPING__GROUP] . "=" . $p_found_user["dn"] . ")",
                            array_merge(
                                [$l_group_attr],
                                $this->m_default_attributes
                            ),
                            0,
                            null,
                            null,
                            C__LDAP_SCOPE__RECURSIVE
                        );

                        $this->debug(
                            " search() " . $l_ldapi->get_search_path(
                            ) . " (Filter: " . "(" . $l_mapping[C__LDAP_MAPPING__GROUP] . "=" . $p_found_user["dn"] . ")" . "): " . $l_ldapi->count($l_group_data)
                        );

                        if ($l_ldapi->count($l_group_data) > 0)
                        {
                            $l_group_entries = $l_ldapi->get_entries($l_group_data);

                            foreach ($l_group_entries as $l_single_group)
                            {
                                $l_group_name = $l_single_group[$l_group_attr][0];

                                if (!$l_group_name)
                                {
                                    if ($l_single_group["cn"][0])
                                    {
                                        $l_group_name = $l_single_group["cn"][0];
                                    }
                                    else if ($l_single_group["cn"])
                                    {
                                        $l_group_name = $l_single_group["cn"];
                                    }
                                }

                                if ($l_group_name)
                                {
                                    /* Search for i-doit pendant */
                                    $l_idoit_group = $this->get_idoit_group($l_group_name);
                                    if ($l_idoit_group) $l_return[] = $l_idoit_group;
                                }

                            }
                        }
                    }
                    else
                    {
                        $this->debug("No group found.");
                    }

                    break;

                default:
                    $l_ldap_groups = (isset($p_found_user[$l_mapping[C__LDAP_MAPPING__GROUP]]) ? $p_found_user[$l_mapping[C__LDAP_MAPPING__GROUP]] : (isset($p_found_user[strtolower(
                            $l_mapping[C__LDAP_MAPPING__GROUP]
                        )]) ? $p_found_user[strtolower($l_mapping[C__LDAP_MAPPING__GROUP])] : $p_found_user[C__LDAP_MAPPING__GROUP]));

                    if (is_array($l_ldap_groups))
                    {
                        unset($l_ldap_groups['count']);

                        foreach ($l_ldap_groups as $l_group)
                        {
                            $l_group_data = $l_ldapi->search(
                                $l_group,
                                "(objectclass=*)",
                                array_merge(
                                    [$l_group_attr],
                                    $this->m_default_attributes
                                ),
                                0,
                                null,
                                null,
                                C__LDAP_SCOPE__RECURSIVE
                            );

                            $this->debug(" Found " . $l_group . " (Filter: (objectclass=*)): " . $l_ldapi->count($l_group_data));

                            if ($l_ldapi->count($l_group_data) > 0)
                            {
                                $l_group_entries = $l_ldapi->get_entries($l_group_data);

                                if ($l_group_entries)
                                {
                                    $l_group_name = (isset($l_group_entries[0][$l_group_attr][0])) ? $l_group_entries[0][$l_group_attr][0] : $l_group_entries[0][strtolower(
                                        $l_group_attr
                                    )][0];

                                    if (empty($l_group_name))
                                    {
                                        $l_group_name = $l_group_entries[0]["cn"][0];
                                    }

                                    /* Search for i-doit pendant */
                                    $l_idoit_group = $this->get_idoit_group($l_group_name);
                                    if ($l_idoit_group) $l_return[] = $l_idoit_group;
                                }
                            }
                        }

                    }
                    break;
            }
        }
        else
        {
            $this->debug("ERROR: LDAPi-Library not available :: " . __FILE__ . ":" . __LINE__);
        }

        return $l_return;
    }

    /**
     * Returns a html list of ldap server types or fales, if no type was found.
     *
     * @return string / false
     */
    public function get_server_types()
    {
        $l_ldap = new isys_ldap_dao(isys_application::instance()->database);

        $l_types = $l_ldap->get_ldap_types();

        if ($l_types->num_rows() > 0)
        {
            $l_objList = new isys_component_list(ISYS_NULL, $l_types);

            $l_objList->config(
                [
                    "isys_ldap_directory__title" => "Title",
                    "isys_ldap_directory__const" => "Const"
                ],
                '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LDAP . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . '&' . C__GET__SETTINGS_PAGE . '=' . C__LDAPPAGE__SERVERTYPES . '&id=[{isys_ldap_directory__id}]',
                "[{isys_ldap_directory__id}]",
                true
            );

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        }
        else return false;
    }

    /**
     * Returns a html list of configured ldap servers, or false if nothing was configured, yet.
     *
     * @return string / false
     */
    public function get_server_list()
    {
        $l_ldap = new isys_ldap_dao(isys_application::instance()->database);

        $l_data = $l_ldap->get_data();

        if ($l_data->num_rows() > 0)
        {
            $l_objList = new isys_component_list(ISYS_NULL, $l_data);

            $l_objList->config(
                [
                    "isys_ldap__id"              => "ID",
                    "isys_ldap_directory__title" => "Directory",
                    "isys_ldap__hostname"        => "Host",
                    "isys_ldap__port"            => "Port",
                    "isys_ldap__dn"              => "Login",
                    "isys_ldap__user_search"     => "Search",
                    "isys_ldap__filter"          => "Filter",
                    "isys_ldap__active"          => "Active",
                ],
                '?' . C__GET__MODULE_ID . '=' . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LDAP . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . '&' . C__GET__SETTINGS_PAGE . '=' . C__LDAPPAGE__CONFIG . '&id=[{isys_ldap__id}]',
                "[{isys_ldap__id}]",
                true
            );

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        }
        else return false;
    } // function

    /**
     * Process ldap module
     *
     * @param array $p_get
     */
    public function process($p_get)
    {
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_ldap = new isys_ldap_dao(isys_application::instance()->database);

        // Enable save mode via AJAX:
        $l_navbar->set_save_mode('ajax')
            ->set_ajax_return('ajax_return');

        switch ($p_get[C__GET__SETTINGS_PAGE])
        {
            case C__LDAPPAGE__SERVERTYPES:
                isys_auth_system::instance()
                    ->check(isys_auth::VIEW, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES);
                $l_id = (isset($_GET["id"]) || !empty($_POST['id'])) ? ((is_array(isys_glob_get_param("id"))) ? array_pop(isys_glob_get_param("id")) : isys_glob_get_param(
                    "id"
                )) : null;

                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                {

                    $l_mapping = [
                        C__LDAP_MAPPING__GROUP        => $_POST["LDAP_MAP__GROUP"],
                        C__LDAP_MAPPING__OBJECT_CLASS => $_POST["LDAP_MAP__OBJECTCLASS"],
                        C__LDAP_MAPPING__FIRSTNAME    => $_POST["LDAP_MAP__GIVENNAME"],
                        C__LDAP_MAPPING__LASTNAME     => $_POST["LDAP_MAP__SURNAME"],
                        C__LDAP_MAPPING__MAIL         => $_POST["LDAP_MAP__MAIL"],
                        C__LDAP_MAPPING__USERNAME     => $_POST["LDAP_MAP__USERNAME"]
                    ];

                    if (isset($l_id))
                    {
                        $l_ret = $l_ldap->save_ldap_directory(
                            $_POST["C__MODULE__LDAP_TYPE__TITLE"],
                            $_POST["C__MODULE__LDAP_TYPE__CONST"],
                            $l_mapping,
                            $l_id
                        );

                    }
                    else
                    {
                        $l_ret = $l_ldap->create_ldap_directory(
                            $_POST["C__MODULE__LDAP_TYPE__TITLE"],
                            $_POST["C__MODULE__LDAP_TYPE__CONST"],
                            $l_mapping
                        );
                    }

                    if ($l_ret)
                    {
                        isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));
                    }
                    else
                    {
                        isys_notify::error(
                            _L('LC__INFOBOX__DATA_WAS_NOT_SAVED') . ' : ' . $l_ldap->get_database_component()
                                ->get_last_error_as_string()
                        );
                    }

                    // Die, because this is an ajax request.
                    die;
                }

                $l_edit_right = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES);

                if ($l_id || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW)
                {

                    $l_rules = [];

                    // Display single-view, if id is set.
                    if ($l_id > 0)
                    {
                        $l_types = $l_ldap->get_ldap_types($l_id)
                            ->__to_array();

                        $l_rules["C__MODULE__LDAP_TYPE__TITLE"]["p_strValue"] = $l_types["isys_ldap_directory__title"];

                        if ($l_types["isys_ldap_directory__const"] == "C__LDAP__AD" || $l_types["isys_ldap_directory__const"] == "C__LDAP__NDS" || $l_types["isys_ldap_directory__const"] == "C__LDAP__OPENLDAP")
                        {
                            $l_rules["C__MODULE__LDAP_TYPE__CONST"]["p_bDisabled"] = true;
                        } // if

                        $l_rules["C__MODULE__LDAP_TYPE__CONST"]["p_strValue"] = $l_types["isys_ldap_directory__const"];

                        $l_mapping                                      = unserialize($l_types["isys_ldap_directory__mapping"]);
                        $l_rules["LDAP_MAP__GROUP"]["p_strValue"]       = $l_mapping[C__LDAP_MAPPING__GROUP];
                        $l_rules["LDAP_MAP__OBJECTCLASS"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__OBJECT_CLASS];
                        $l_rules["LDAP_MAP__GIVENNAME"]["p_strValue"]   = $l_mapping[C__LDAP_MAPPING__FIRSTNAME];
                        $l_rules["LDAP_MAP__SURNAME"]["p_strValue"]     = $l_mapping[C__LDAP_MAPPING__LASTNAME];
                        $l_rules["LDAP_MAP__MAIL"]["p_strValue"]        = $l_mapping[C__LDAP_MAPPING__MAIL];
                        $l_rules["LDAP_MAP__USERNAME"]["p_strValue"]    = $l_mapping[C__LDAP_MAPPING__USERNAME];
                    } // if

                    switch ($_POST[C__GET__NAVMODE])
                    {
                        case C__NAVMODE__NEW:
                        case C__NAVMODE__EDIT:
                            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                            break;
                        default:
                            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                            break;
                    }

                    isys_application::instance()->template->assign('dirID', $l_id)
                        ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

                }
                else
                {
                    $l_list = $this->get_server_types();

                    $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

                    isys_application::instance()->template->assign("g_list", $l_list)
                        ->assign('content_title', _L('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__DIRECTORIES'));
                } // if

                isys_component_template::instance()
                    ->include_template(
                        'contentbottomcontent',
                        isys_application::instance()->app_path . "/src/themes/default/smarty/templates/content/bottom/content/module__ldap_server_types.tpl"
                    );

                break;
            default:
            case C__LDAPPAGE__CONFIG:

                /* Connection test */
                if ($_GET["connection_test"] == "1")
                {

                    $l_debug_level = 0;
                    if (isset($_GET['debug']))
                    {
                        if ($_GET['debug'] == 6 || $_GET['debug'] == 7)
                        {
                            $l_debug_level = $_GET['debug'];
                        }
                    }

                    try
                    {
                        $this->debug(
                            "Testing connection to " . $_POST["C__MODULE__LDAP__HOST"] . ":" . $_POST["C__MODULE__LDAP__PORT"] . " (" . $_POST["C__MODULE__LDAP__DN"] . ")"
                        );

                        $l_ldapi = isys_library_ldap::factory(
                            $_POST["C__MODULE__LDAP__HOST"],
                            $_POST["C__MODULE__LDAP__DN"],
                            $_POST["C__MODULE__LDAP__PASS"],
                            $_POST["C__MODULE__LDAP__PORT"],
                            $_POST["C__MODULE__LDAP__VERSION"],
                            !!$_POST["C__MODULE__LDAP__TLS"],
                            $l_debug_level
                        )
                            ->set_logger(self::get_logger());

                        if (!$l_ldapi->connected())
                        {
                            $this->debug("Connection failed: " . $l_ldapi->get_last_error());

                            die("<p class=\"exception bold p5\">Error! Could not connect to " . "{$_POST["C__MODULE__LDAP__HOST"]}:{$_POST["C__MODULE__LDAP__PORT"]} using " . "{$_POST["C__MODULE__LDAP__DN"]}!<br />" . "{$l_ldapi->get_last_error()}<br />" . "LDAP-Error: {$l_ldapi->get_ldap_error()}</p>");
                        }
                        else
                        {
                            $this->debug("Connection successfull.");

                            echo "<p class=\"note bold p5\">" . "Connection OK!<br />";

                            if ($_POST["C__MODULE__LDAP__RECURSIVE"] > 0) $l_scope = C__LDAP_SCOPE__RECURSIVE;
                            else $l_scope = C__LDAP_SCOPE__SINGLE;

                            if (count($_POST['field_value']) > 0)
                            {

                                foreach ($_POST['field_value'] AS $l_key => $l_val)
                                {
                                    if ($l_val === '') die("<p class=\"exception bold p5\">Error!<br />" . _L(
                                            'LC__INFOBOX__LDAP__ERROR__VALUE_IN_FILTER_MUST_NOT_BE_EMPTY'
                                        ) . "</p>");

                                    $l_arr[$l_key] = $l_val;
                                }
                            }

                            // New Filter
                            $l_filter_arr = [
                                "attributes"      => $_POST['field_title'],
                                "values"          => $_POST['field_value'],
                                "field_type"      => $_POST['field_type'],
                                "field_link_type" => $_POST['field_link_type'],
                                "field_operator"  => $_POST['field_operator']
                            ];

                            $l_filter = $this->create_filter_string($l_filter_arr);

                            if (!$l_filter)
                            {
                                $l_filter = $_POST["C__MODULE__LDAP__FILTER"];
                            } // if

                            $l_res = $l_ldapi->search(
                                $_POST["C__MODULE__LDAP__SEARCH"],
                                $l_filter,
                                [],
                                0,
                                0,
                                (!empty($_POST["C__MODULE__LDAP__TIMELIMIT"]) ? intval($_POST["C__MODULE__LDAP__TIMELIMIT"]) : C__DEFAULT__TIMELIMIT),
                                null,
                                $l_scope
                            );

                            if (($l_count = $l_ldapi->count($l_res)))
                            {
                                if ($l_count >= 50)
                                {
                                    $this->debug("50 or more objects found in " . $_POST["C__MODULE__LDAP__SEARCH"]);
                                    echo "<strong>50</strong> or more objects found in {$_POST["C__MODULE__LDAP__SEARCH"]}.";
                                }
                                else
                                {
                                    $this->debug("Found {$l_count} object(s) in {$_POST["C__MODULE__LDAP__SEARCH"]}.");
                                    echo "<strong>{$l_count}</strong> object(s) found in {$_POST["C__MODULE__LDAP__SEARCH"]}.";
                                }
                            }
                            else
                            {
                                $this->debug("No objects found in your configurated OU. No one will be able to login into i-doit. Check filter and search-dn.");
                                echo "No object found. That means that no one will be able to login with the current setup. " . "Check your filter and search-dn.";
                            }

                            echo "</p>";

                            die();
                        }
                    }
                    catch (Exception $e)
                    {
                        die("<p class=\"exception bold p5\">Error!<br /><br />" . $e->getMessage() . "</p>");
                    }
                }

                /**
                 * init
                 */
                $l_filter_arr = null;
                $l_filter     = '';
                $l_arr        = [];
                isys_auth_system::instance()
                    ->check(isys_auth::VIEW, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG);

                /* Delete */
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__PURGE)
                {
                    foreach ($_POST["id"] as $l_id)
                    {
                        if ($l_ldap->delete_server($l_id))
                        {
                            // Server $l_id deleted successfully
                        }
                    }
                }
                // Entry id
                $l_id = (isset($_GET["id"]) || !empty($_POST['id'])) ? ((is_array(isys_glob_get_param("id"))) ? array_pop(isys_glob_get_param("id")) : isys_glob_get_param(
                    "id"
                )) : null;

                /* Save*/
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                {

                    if (empty($_POST["C__MODULE__LDAP__ACTIVE"]))
                    {
                        $_POST["C__MODULE__LDAP__ACTIVE"] = "0";
                    }

                    if (is_array($_POST['field_value']) && count($_POST['field_value']) > 0)
                    {
                        $l_message = _L('LC__INFOBOX__LDAP__ERROR__VALUE_IN_FILTER_MUST_NOT_BE_EMPTY');

                        foreach ($_POST['field_value'] AS $l_key => $l_val)
                        {
                            if ($l_val === '') die("<p class=\"exception bold p5\">Error! " . _L(
                                    'LC__INFOBOX__LDAP__ERROR__COULD_NOT_SAVE'
                                ) . " <br />" . $l_message . "</p>");

                            $l_arr[$l_key] = $l_val;
                        }
                    }

                    $l_filter_arr = [
                        "attributes"      => $_POST['field_title'],
                        "values"          => $l_arr,
                        "field_type"      => $_POST['field_type'],
                        "field_link_type" => $_POST['field_link_type'],
                        "field_operator"  => $_POST['field_operator']
                    ];

                    $l_filter = $this->create_filter_string($l_filter_arr);

                    if (!$l_filter)
                    {
                        $l_filter = $_POST["C__MODULE__LDAP__FILTER"];
                    } // if

                    try
                    {
                        if (isset($l_id))
                        {
                            $l_ldap->save_server(
                                $_POST["C__MODULE__LDAP__DIRECTORY"],
                                $_POST["C__MODULE__LDAP__HOST"],
                                $_POST["C__MODULE__LDAP__PORT"],
                                $_POST["C__MODULE__LDAP__DN"],
                                $_POST["C__MODULE__LDAP__PASS"],
                                $_POST["C__MODULE__LDAP__SEARCH"],
                                $_POST["C__MODULE__LDAP__SEARCH_GROUP"],
                                $l_filter,
                                $_POST["C__MODULE__LDAP__ACTIVE"],
                                $_POST["C__MODULE__LDAP__TIMELIMIT"],
                                $_POST["C__MODULE__LDAP__RECURSIVE"],
                                $_POST["C__MODULE__LDAP__TLS"],
                                $_POST["C__MODULE__LDAP__VERSION"],
                                $l_id,
                                $l_filter_arr
                            );
                        }
                        else
                        {
                            $l_ldap->create_server(
                                $_POST["C__MODULE__LDAP__DIRECTORY"],
                                $_POST["C__MODULE__LDAP__HOST"],
                                $_POST["C__MODULE__LDAP__PORT"],
                                $_POST["C__MODULE__LDAP__DN"],
                                $_POST["C__MODULE__LDAP__PASS"],
                                $_POST["C__MODULE__LDAP__SEARCH"],
                                $_POST["C__MODULE__LDAP__SEARCH_GROUP"],
                                $l_filter,
                                $_POST["C__MODULE__LDAP__ACTIVE"],
                                $_POST["C__MODULE__LDAP__TIMELIMIT"],
                                $_POST["C__MODULE__LDAP__RECURSIVE"],
                                $_POST["C__MODULE__LDAP__TLS"],
                                $_POST["C__MODULE__LDAP__VERSION"],
                                $l_filter_arr
                            );
                        } // if

                        isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));
                    }
                    catch (Exception $e)
                    {
                        $l_message = $e->getMessage();
                        isys_notify::error($l_message, ['sticky' => true]);
                        //die("<p class=\"exception bold p5\">Error! " . _L('LC__INFOBOX__LDAP__ERROR__COULD_NOT_SAVE') . "<br />" . $l_message . "</p>");
                    } // try

                    // Die, because this is an ajax request.
                    die();
                } // if

                $l_edit_right   = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG);
                $l_delete_right = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::DELETE, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG);

                // New or Edit.
                if ((isset($l_id) || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) && $_POST[C__GET__NAVMODE] != C__NAVMODE__PURGE)
                {
                    $l_data = $l_filter_data = [];

                    // Single-View Mode.

                    if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW || $_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
                    {
                        $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                        $l_edit_mode = 1;
                    }
                    else
                    {
                        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                            ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                        $l_edit_mode = 0;
                    } //if

                    $l_rules["C__MODULE__LDAP__ACTIVE"]["p_arData"]         = serialize(get_smarty_arr_YES_NO());
                    $l_rules["C__MODULE__LDAP__PORT"]["p_strValue"]         = "389";
                    $l_rules["C__MODULE__LDAP__TIMELIMIT"]["p_strValue"]    = "30";
                    $l_rules["C__MODULE__LDAP__FILTER"]["p_strValue"]       = "(objectClass=user)";
                    $l_rules["C__MODULE__LDAP__FILTER"]["p_bReadonly"]      = true;
                    $l_versions                                             = [
                        "1" => "1",
                        "2" => "2",
                        "3" => "3"
                    ];
                    $l_rules["C__MODULE__LDAP__VERSION"]["p_arData"]        = serialize($l_versions);
                    $l_rules["C__MODULE__LDAP__VERSION"]["p_strSelectedID"] = "3";
                    $l_rules["C__MODULE__LDAP__TLS"]["p_arData"]            = serialize(get_smarty_arr_YES_NO());
                    $l_rules["C__MODULE__LDAP__TLS"]["p_bDbFieldNN"]        = 1;
                    $l_rules["C__MODULE__LDAP__TLS"]["p_strSelectedID"]     = 0;

                    if ($l_id > 0)
                    {
                        $l_data                = $l_ldap->get_data($l_id)
                            ->__to_array();
                        $l_filter_exists       = false;
                        $l_filter_array_exists = false;
                        if (strlen($l_data["isys_ldap__filter_array"]) > 0)
                        {
                            $l_filter_data = unserialize($l_data["isys_ldap__filter_array"]);

                            if (is_array($l_filter_data))
                            {
                                $l_filter_array_exists = true;
                                if (!empty($l_filter_data["attributes"][0]) && !empty($l_filter_data["values"][0]))
                                {
                                    $l_filter        = $this->create_filter_string(unserialize($l_data["isys_ldap__filter_array"]));
                                    $l_filter_exists = true;
                                } // if
                            }
                        } // if

                        if (!$l_filter_exists && !$l_filter_array_exists)
                        {
                            $l_filter_data = [
                                'attributes'      => ['objectClass'],
                                'values'          => ['user'],
                                'field_type'      => ['3'],
                                'field_link_type' => ['&'],
                                'field_operator'  => ['=']
                            ];
                            $l_filter      = "(objectClass=user)";
                        }

                        isys_application::instance()->template->assign("g_recursive", $l_data["isys_ldap__recursive"]);

                        $l_rules["C__MODULE__LDAP__TLS"]["p_strSelectedID"]       = $l_data["isys_ldap__tls"];
                        $l_rules["C__MODULE__LDAP__VERSION"]["p_strSelectedID"]   = $l_data["isys_ldap__version"];
                        $l_rules["C__MODULE__LDAP__ACTIVE"]["p_strSelectedID"]    = $l_data["isys_ldap__active"];
                        $l_rules["C__MODULE__LDAP__DIRECTORY"]["p_strSelectedID"] = $l_data["isys_ldap__isys_ldap_directory__id"];
                        $l_rules["C__MODULE__LDAP__TITLE"]["p_strValue"]          = $l_data["isys_ldap__title"];
                        $l_rules["C__MODULE__LDAP__TIMELIMIT"]["p_strValue"]      = $l_data["isys_ldap__timelimit"];
                        $l_rules["C__MODULE__LDAP__HOST"]["p_strValue"]           = $l_data["isys_ldap__hostname"];
                        $l_rules["C__MODULE__LDAP__PORT"]["p_strValue"]           = $l_data["isys_ldap__port"];
                        $l_rules["C__MODULE__LDAP__DN"]["p_strValue"]             = $l_data["isys_ldap__dn"];
                        $l_rules["C__MODULE__LDAP__PASS"]["p_strValue"]           = isys_helper_crypt::decrypt($l_data["isys_ldap__password"]);
                        $l_rules["C__MODULE__LDAP__SEARCH"]["p_strValue"]         = $l_data["isys_ldap__user_search"];
                        $l_rules["C__MODULE__LDAP__SEARCH_GROUP"]["p_strValue"]   = $l_data["isys_ldap__group_search"];
                        $l_rules["C__MODULE__LDAP__FILTER"]["p_strValue"]         = $l_filter;

                        if ($l_data['isys_ldap_directory__const'] == 'C__LDAP__OPENLDAP')
                        {
                            isys_application::instance()->template->assign('simulate_openldap_search', true)
                                ->assign('ldap_directory_value', $l_data["isys_ldap__isys_ldap_directory__id"]);
                        }

                        if (!$l_filter_exists && !$l_filter_array_exists)
                        {
                            isys_application::instance()->template->assign("filter_message", _L("LC__LDAP__FILTER__MESSAGE"));
                        } // if
                    }
                    else
                    {
                        $l_filter_data = [
                            'attributes'      => ['objectClass'],
                            'values'          => ['user'],
                            'field_type'      => ['3'],
                            'field_link_type' => ['&'],
                            'field_operator'  => ['=']
                        ];
                    } // if

                    isys_application::instance()->template->assign("filter_arr", $l_filter_data)
                        ->assign("entryID", isset($l_data['isys_ldap__id']) ? $l_data['isys_ldap__id'] : null)
                        ->assign("isEditMode", $l_edit_mode)
                        ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                }
                else
                {
                    // List-Mode.
                    $l_navbar->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
                        ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
                        ->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW)
                        ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

                    $l_list = $this->get_server_list();

                    isys_application::instance()->template->assign("content_title", _L('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__SERVER'))
                        ->assign("g_list", $l_list);
                } // if

                isys_application::instance()->template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                    ->include_template('contentbottomcontent', 'content/bottom/content/module__ldap.tpl');

                break;
        } // switch
    }

    /**
     * Creates an ldap filter
     *
     * @param $p_filter_arr
     *
     * @return bool|null|string
     */
    public function create_filter_string($p_filter_arr)
    {
        if (isset($p_filter_arr["attributes"]) && is_array($p_filter_arr["attributes"]))
        {
            $l_attributes = $p_filter_arr["attributes"];
            $l_values     = $p_filter_arr["values"];
            $l_types      = $p_filter_arr["field_type"];
            $l_link_types = $p_filter_arr["field_link_type"];
            $l_operators  = $p_filter_arr["field_operator"];

            $l_keys = array_keys($l_attributes);

            $l_arr = [];

            if (count($l_keys) > 0)
            {

                if (count($l_attributes) == 1)
                {
                    if ((empty($l_attributes[0]) && empty($l_values[0])) || (!empty($l_attributes[0]) && empty($l_values[0]))) return null;
                }

                foreach ($l_keys AS $l_key)
                {
                    $l_string = "";

                    if ($l_operators[$l_key] == "!=")
                    {
                        $l_prefix = "!";
                        $l_string = "(";
                    }
                    else $l_prefix = "";

                    switch ($l_operators[$l_key])
                    {
                        case ">=":
                            $l_operator = ">=";
                            break;
                        case "<=":
                            $l_operator = "<=";
                            break;
                        default:
                            $l_operator = "=";
                            break;
                    }

                    if ($l_attributes[$l_key] && $l_values[$l_key])
                    {
                        $l_string .= $l_prefix . "(" . $l_attributes[$l_key] . $l_operator . ($l_values[$l_key]) . ")";

                        if (strlen($l_prefix) > 0) $l_string .= ")";

                        $l_arr[$l_key] = $l_string;
                    }
                }

                return $this->build_filter($l_arr, $l_types, $l_link_types);
            }
        }

        return false;
    }

    /**
     * Returns ldap library by isys_ldap__id.
     *
     * @param   integer $p_ldap_id
     * @param   null    &$p_connection_info This parameter will get overwritten anyways.
     *
     * @throws  Exception
     * @return isys_library_ldap
     */
    public function get_library_by_id($p_ldap_id, &$p_connection_info)
    {
        // get isys_ldap_dao.
        $l_dao = $this->get_dao();

        // retrieve server configuration data.
        $l_ldap = $l_dao->get_active_servers($p_ldap_id)
            ->__to_array();

        // fill reference with ldap connection information.
        $p_connection_info = $l_ldap;

        try
        {
            // return library.
            return $this->get_library(
                $l_ldap["isys_ldap__hostname"],
                $l_ldap["isys_ldap__dn"],
                $l_ldap["isys_ldap__password"],
                $l_ldap["isys_ldap__port"],
                $l_ldap["isys_ldap__version"],
                $l_ldap["isys_ldap__tls"]
            );
        }
        catch (Exception $e)
        {
            throw $e;
        } // try
    } // function

    /**
     * Returns internal ldap library.
     *
     * @param   string  $p_host
     * @param   string  $p_user_dn
     * @param   string  $p_pass
     * @param   integer $p_port
     * @param   integer $p_protocol_version
     * @param   boolean $p_tls
     *
     * @return isys_library_ldap
     * @throws Exception
     */
    public function get_library($p_host, $p_user_dn, $p_pass, $p_port = 389, $p_protocol_version = 3, $p_tls = false)
    {
        if ($p_port < 0 || empty($p_port))
        {
            $p_port = 389;
        } // if

        if ($p_protocol_version < 1 || empty($p_protocol_version))
        {
            $p_protocol_version = 3;
        } // if

        if (empty($p_tls))
        {
            $p_tls = false;
        } // if

        try
        {
            $this->debug("Creating new ldap-library connection to: " . $p_host . ":" . $p_port . ", user: " . $p_user_dn);

            return isys_library_ldap::factory(
                $p_host,
                $p_user_dn,
                $p_pass,
                $p_port,
                $p_protocol_version,
                $p_tls
            )
                ->set_logger(
                    self::get_logger()
                );
        }
        catch (Exception $e)
        {
            throw $e;
        } // try
    } // function

    /**
     * Get the corresponding i-doit ldap group by the ldap name
     *
     * @param string $p_group_name
     *
     * @return string
     */
    private function get_idoit_group($p_group_name)
    {
        $l_return = '';

        if ($p_group_name != "")
        {
            $l_dao_groups = isys_cmdb_dao_category_s_person_group_master::instance($GLOBALS['g_comp_database']);

            //$this->debug("Querying LDAP group: ". $p_group_name);
            $l_idoit_group = $l_dao_groups->get_data(null, null, " AND (isys_cats_person_group_list__ldap_group = '" . $p_group_name . "')");

            if ($l_idoit_group->num_rows() > 0)
            {

                $l_idoit_group_data = $l_idoit_group->__to_array();
                $l_return           = $l_idoit_group_data;

                $this->debug(" ** i-doit group pendant for \"{$p_group_name}\" found: " . $l_return["isys_cats_person_group_list__title"]);

            }
            else
            {
                $this->debug(
                    " -- Group pendant for \"{$p_group_name}\" not found. Set an LDAP-Mapping in your corresponding person group if you want to use this as a right group."
                );
            }
        }
        else
        {
            $this->debug("Group name empty in get_idoit_group()");

            return false;
        }

        return $l_return;
    } // function

    /**
     * Builds an LDAP-Filter as String
     *
     * @param array $p_arr
     * @param array $p_types
     * @param array $p_link_types
     *
     * @return string
     */
    private function build_filter($p_arr, $p_types, $p_link_types)
    {
        $l_counter = 0;

        $l_string   = '';
        $l_last_key = -1;

        if (count($p_arr) > 1)
        {
            foreach ($p_arr AS $l_key => $l_val)
            {

                if (isset($p_arr[$l_last_key]) && $p_arr[$l_last_key])
                {
                    switch ($p_types[$l_key])
                    {
                        case 1:
                            if ($l_counter == 0) $l_counter = 1;

                            $l_string = $l_string . $l_val;
                            break;
                        case 2:
                            if ($l_counter == 0) $l_counter = 2;
                            else $l_counter++;

                            $l_string = $l_string . "(" . $p_link_types[$l_key] . $l_val;
                            break;
                        case 3:

                            if ($l_counter > 0)
                            {
                                $l_string = substr($l_string, 0, strlen($l_string) - 1);
                            }
                            for ($i = 0;$i <= $l_counter;$i++)
                            {
                                $l_string .= ")";
                            }

                            $l_counter = 1;
                            $l_string  = "(" . $p_link_types[$l_key] . $l_string . $l_val;

                            break;
                        default:
                            break;
                    }
                }
                else
                {
                    $l_string = "(" . $p_link_types[$l_key] . $l_val;
                }
                $l_last_key = $l_key;
            }

            for ($i = 0;$i < $l_counter;$i++)
            {
                $l_string .= ")";
            }

            if ($l_counter == 0) $l_string .= ")";
        }
        else
        {
            return $p_arr[0];
        }

        return $l_string;
    } // function
} // class