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
 * Session manager. Providers basic session management
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_session extends isys_component
{
    /**
     * @var  isys_component_session
     */
    private static $m_instance = null;

    /**
     * @var  array
     */
    protected $m_mandator_data = [];

    /**
     * @var  integer
     */
    protected $m_mandator_id;

    /**
     * @var  string
     */
    protected $m_mandator_name;

    /**
     * Session specific error messages.
     *
     * @var  array
     */
    private $m_err;

    /**
     * @var  string
     */
    private $m_language;

    /**
     * @var  boolean
     */
    private $m_logged_in = false;

    /**
     * @var  isys_module_ldap
     */
    private $m_mod_ldap = null;

    /**
     * @var  array
     */
    private $m_session_data;

    /**
     * @var  string
     */
    private $m_session_id;

    /**
     * @var  integer
     */
    private $m_session_time = 300;

    /**
     * @var  integer
     */
    private $m_user_id;

    /**
     * @var  array
     */
    private $m_userdata = [];

    /**
     * @var  string
     */
    private $m_username;

    /**
     * Get singleton instance.
     *
     * @param   isys_module_ldap $p_ldap_module
     * @param   integer          $p_session_time
     *
     * @return  \isys_component_session
     */
    final public static function instance(isys_module_ldap $p_ldap_module = null, $p_session_time = 300)
    {
        if (!self::$m_instance)
        {
            self::$m_instance = new self($p_ldap_module, $p_session_time);
        } // if

        return self::$m_instance;
    } // function

    /**
     * @param  integer $p_session_time
     */
    public function set_session_time($p_session_time)
    {
        if ($p_session_time > 0)
        {
            $this->m_session_time = $p_session_time;
        } // if
    } // function

    /**
     * @return  integer
     */
    public function get_session_time()
    {
        return $this->m_session_time;
    } // function

    /**
     * @return  string
     */
    public function get_language()
    {
        return $this->m_language;
    } // function

    /**
     * @param   string $p_language
     *
     * @return  $this
     */
    public function set_language($p_language)
    {
        global $g_idoit_language_short;

        if (!$p_language)
        {
            // Initialize a default language (since $g_idoit_language_short is used by the init.php scripts to include the language file).
            $p_language = 'en';
        } // if

        $g_idoit_language_short = $_SESSION["lang"] = $this->m_language = $p_language;

        isys_application::instance()
            ->language($p_language);

        return $this;
    } // function

    /**
     * @return array
     */
    public function get_userdata()
    {
        return $this->m_userdata;
    }

    /**
     * @return mixed
     */
    public function get_current_username()
    {
        return $this->m_username;
    }

    /**
     * @return array
     */
    public function get_mandator_data()
    {
        return $this->m_mandator_data;
    }

    /**
     * @return isys_module_ldap
     */
    public function get_ldap_module()
    {
        return $this->m_mod_ldap;
    }

    /**
     * @return mixed
     */
    public function get_user_id()
    {
        if (!$this->m_user_id)
        {
            $l_sessdata = $this->get_session_data();

            $this->m_user_id = $l_sessdata["isys_user_session__isys_obj__id"];
        }

        return $this->m_user_id;
    }

    /**
     * @param      $p_msg
     * @param null $p_key
     */
    public function add_error($p_msg, $p_key = null)
    {
        if (!is_null($p_key))
        {
            $this->m_err[$p_key] = $p_msg;
        }
        else $this->m_err[] = $p_msg;
    }

    /**
     * @return array
     */
    public function get_errors()
    {
        return $this->m_err;
    }

    /**
     * @param $p_mname
     *
     * @return isys_component_session
     */
    public function set_mandator_name($p_mname)
    {
        $this->m_mandator_name = $p_mname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function get_mandator_name()
    {
        return $this->m_mandator_name;
    }

    /**
     * @return mixed
     */
    public function get_mandator_id()
    {
        return $this->m_mandator_id;
    }

    /**
     * @return mixed
     */
    public function get_user_session_id()
    {
        $l_sessdata = $this->get_session_data();

        return $l_sessdata["isys_user_session__id"];
    }

    /**
     * Is the user logged in? TRUE, if yes, FALSE, if error or no.
     *
     * @return  boolean
     */
    public function is_logged_in()
    {
        $l_nSessID = $this->get_session_id();

        if (is_array($this->m_session_data) && isset($this->m_session_data["isys_user_session__isys_obj__id"]))
        {
            // Returns true, if session is _not_ binded to a guest user.
            return true;
        }
        else
        {
            if (!count($_SESSION))
            {
                return false;
            }

            $this->m_session_data = $this->get_session_data($l_nSessID);

            if ($this->m_session_data)
            {
                return true;
            } // if
        } // if

        return false;
    } // function

    /**
     * Change current mandator.
     * Works only if username and password are same for the new mandator. Uses current user to double check.
     *
     * @param   integer $p_mandator_id
     *
     * @return  boolean
     * @throws  Exception
     */
    public function change_mandator($p_mandator_id)
    {
        global $g_comp_database;

        if (is_object($g_comp_database) && $p_mandator_id > 0)
        {
            $l_person_dao = new isys_cmdb_dao_category_s_person_master($g_comp_database);
            $l_data       = $l_person_dao->get_person_by_username($this->get_current_username())
                ->__to_array();

            if ($l_data)
            {
                try
                {
                    $this->connect_mandator($p_mandator_id);
                    $this->delete_current_session();
                    $this->start_dbsession();

                    if ($this->login($GLOBALS['g_comp_database'], $l_data['isys_cats_person_list__title'], $l_data['isys_cats_person_list__user_pass'], true, true))
                    {
                        if ((defined('C__MODULE__PRO') && C__MODULE__PRO === true) && (defined('C__ENABLE__LICENCE') && C__ENABLE__LICENCE === true) && class_exists(
                                'isys_module_licence'
                            )
                        )
                        {
                            // Overwrite Licence related keys in session from previous mandator
                            $l_licence = new isys_module_licence();
                            $l_licence->verify();
                        } // if

                        return true;
                    } // if
                }
                catch (Exception $e)
                {
                    throw $e;
                } // try
            } // if
        } // if

        return false;
    } // function

    /**
     * Returns dao for mandator.
     *
     * @param    integer $p_mandator_id
     *
     * @return   resource
     * @version  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function get_mandator_dao($p_mandator_id)
    {
        return (new isys_component_dao_mandator)->get_mandator_query($p_mandator_id);
    }

    /**
     * Does a complete weblogin, including session storage,
     * session initialization and mandator selection
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function weblogin($p_user, $p_pass, $p_mandator, $p_md5 = false)
    {
        global $g_mandator_info;

        if (!$this->is_logged_in() && $p_user && $p_pass)
        {
            if (is_numeric($p_mandator))
            {
                $g_mandator_info = $this->connect_mandator($p_mandator);

                if ($g_mandator_info)
                {
                    if (is_object($GLOBALS['g_comp_database']))
                    {
                        $this->delete_current_session();
                        $this->start_dbsession();

                        return $this->login($GLOBALS['g_comp_database'], $p_user, $p_pass, true, $p_md5);
                    } // if
                }
                else
                {
                    throw new Exception('Could not connect to tenant with id ' . $p_mandator);
                } // if
            }
            else
            {
                throw new Exception("Login failed. Either username (" . $p_user . "), password or tenant (" . $p_mandator . ") information is not correct.");
            } // if
        } // if

        return $this->is_logged_in();
    } // function

    /**
     * Does a mandator login based on its apikey
     *
     * @param       $p_apikey
     * @param array $p_userdata
     * @param int   $p_session_id
     *
     * @throws \Exception
     * @throws \isys_exception_api
     * @return bool
     */
    public function apikey_login($p_apikey, $p_userdata = null, $p_session_id = null)
    {
        global $g_comp_database_system, $g_mandator_info;

        if (!$this->is_logged_in() && !empty($p_apikey))
        {
            $l_mandator      = $this->query_mandator($p_apikey);
            $l_mandator_data = $g_comp_database_system->fetch_row_assoc($l_mandator);

            if (is_array($l_mandator_data) && count($l_mandator_data) > 0)
            {
                $g_mandator_info = $this->connect_mandator($l_mandator_data['isys_mandator__id']);

                if (is_object($GLOBALS['g_comp_database']))
                {
                    // Check for an existing session
                    if ($p_session_id && strlen($p_session_id) > 2)
                    {
                        // Try to connect to an existing session
                        $l_session_data = $this->get_session_data($p_session_id);

                        if ($this->is_logged_in())
                        {
                            // Post init session and write session data to $_SESSION
                            $this->post_init_session($l_session_data);

                            return true;
                        } // if
                    } // if

                    $this->delete_current_session();
                    $this->start_dbsession();

                    if ($p_userdata)
                    {
                        return $this->login($GLOBALS['g_comp_database'], $p_userdata['username'], $p_userdata['password'], true);
                    }
                    else
                    {
                        // API option 'api.authenticated-users-only' is activated and no user is specified
                        if (isys_settings::get('api.authenticated-users-only', 0))
                        {
                            throw new isys_exception_api(
                                'Please specify a user by RPC Session header or HTTP Basic Authentication.', isys_api_controller_jsonrpc::ERR_Auth
                            );
                        } // if

                        $l_object = $GLOBALS['g_comp_database']->query(
                            'SELECT isys_obj__id, isys_cats_person_list__isys_obj__id, isys_cats_person_list__title, isys_cats_person_list__first_name, isys_cats_person_list__last_name, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address ' .
                            'FROM isys_obj INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id ' .
                            'LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1 ' .
                            'WHERE isys_obj__const = \'C__OBJ__PERSON_API_SYSTEM\';'
                        );

                        if ($l_object)
                        {
                            $l_object_data = $GLOBALS['g_comp_database']->fetch_row_assoc($l_object);

                            if ($l_object_data && isset($l_object_data['isys_cats_person_list__isys_obj__id']))
                            {
                                $this->post_init_session($l_object_data);

                                return true;
                            } // if
                        } // if
                    } // if
                } // if
                throw new Exception('Could not connect tenant database.');
            }
            else
            {
                return false;
            } // if
        } // if

        return $this->is_logged_in();
    } // function

    /**
     * @param array $p_cats_person_data
     *
     * @return void
     */
    public function write_userdata($p_cats_person_data)
    {
        if (isset($p_cats_person_data["isys_cats_person_list__isys_obj__id"]))
        {
            $this->m_logged_in = true;

            $this->write_userid($p_cats_person_data["isys_cats_person_list__isys_obj__id"]);

            $_SESSION["username"] = $this->m_username = $p_cats_person_data['isys_cats_person_list__title'];
            $this->m_userdata     = [
                'name'  => $p_cats_person_data["isys_cats_person_list__first_name"] . ' ' . $p_cats_person_data["isys_cats_person_list__last_name"],
                'email' => isset($p_cats_person_data["isys_cats_person_list__mail_address"]) ? $p_cats_person_data["isys_cats_person_list__mail_address"] : '',
                'id'    => $p_cats_person_data["isys_cats_person_list__isys_obj__id"]
            ];
        }
    } // function

    /**
     * @return array|NULL
     *
     * @param integer $p_mandator_id
     *
     * @desc Connects to a mandator specified by $p_mandator_id, writes session info
     *       and saves the database access object on $g_comp_database
     */
    public function connect_mandator($p_mandator_id)
    {
        global $g_comp_database, $g_comp_database_system, $g_db_system, $g_mandator_info;

        $g_comp_database_system->select_database($g_comp_database_system->get_db_name());

        $l_res = $this->get_mandator_dao($p_mandator_id);

        if ($g_comp_database_system->num_rows($l_res))
        {
            $l_dbdata = $g_comp_database_system->fetch_row_assoc($l_res);

            if ($l_dbdata)
            {
                // Destruct old database object
                if (isset($g_comp_database) && is_object($g_comp_database) && method_exists($g_comp_database, 'close'))
                {
                    $g_comp_database->close();
                    unset($g_comp_database);
                }

                try
                {
                    /**
                     * Create tenant database
                     *
                     * @return isys_component_database
                     * @throws Exception
                     */
                    unset(isys_application::instance()->container['database']);
                    isys_application::instance()->container['database'] = function () use ($l_dbdata, $g_db_system)
                    {
                        return isys_component_database::get_database(
                            $g_db_system["type"],
                            $l_dbdata["isys_mandator__db_host"],
                            $l_dbdata["isys_mandator__db_port"],
                            $l_dbdata["isys_mandator__db_user"],
                            $l_dbdata["isys_mandator__db_pass"],
                            $l_dbdata["isys_mandator__db_name"]
                        );
                    };

                    // Create connection to mandator DB
                    isys_application::instance()->database = $GLOBALS['g_comp_database'] = isys_application::instance()->container['database'];

                    $GLOBALS['g_mandator_info'] = $g_mandator_info = $l_dbdata;

                    if ($GLOBALS['g_comp_database'])
                    {
                        $this->set_mandator_name($l_dbdata["isys_mandator__title"]);

                        $this->m_mandator_data     = $l_dbdata;
                        $this->m_mandator_id       = $p_mandator_id;
                        $_SESSION["user_mandator"] = $p_mandator_id;
                        $this->m_logged_in         = $this->is_logged_in();

                        return $l_dbdata;
                    }
                }
                catch (isys_exception_database $e)
                {
                    isys_application::instance()->container['notify']->error($e->getMessage());
                } // try
            } // if
        } // if

        return null;
    }

    /**
     * @return array
     *
     * @param string $p_username
     * @param string $p_password
     * @param bool   $p_md5_pass
     *
     * @desc On login, we need to know the mandators, to which
     *       a user is allowed to connect. This function returns
     *       an array with an option array for Smarty in this
     *       format:
     *
     *       <code>
     *        array(
     *         idoit_system.isys_mandator.isys_mandator__id =>
     *          idoit_system.isys_mandator.isys_mandator__title
     *        );
     *       </code>
     *
     *       There can't be real failure, but if there is one,
     *       the array length is also 0. Take a look at the debugger
     *       to see occured errors/warnings.
     */
    public function fetch_mandators($p_username, $p_password, $p_md5_pass = false)
    {
        global $g_comp_database;
        global $g_comp_database_system;
        global $g_db_system;

        $l_mandants = [];

        if (!$p_md5_pass)
        {
            $l_md5_pass = md5($p_password);
        }
        else $l_md5_pass = $p_password;

        $l_res = $this->query_mandator();

        if ($g_comp_database_system->num_rows($l_res))
        {
            while ($l_dbdata = $g_comp_database_system->fetch_array($l_res))
            {

                // Destruct old database object
                if (isset($g_comp_database)) $g_comp_database = null;

                // Create connection to mandator DB
                $g_comp_database = isys_component_database::get_database(
                    $g_db_system["type"],
                    $l_dbdata["isys_mandator__db_host"],
                    $l_dbdata["isys_mandator__db_port"],
                    $l_dbdata["isys_mandator__db_user"],
                    $l_dbdata["isys_mandator__db_pass"],
                    $l_dbdata["isys_mandator__db_name"]
                );

                if ($g_comp_database->is_connected())
                {
                    // Have to reset it before because some methods are accessing this variable later on (isys_module_ldap -> ldap_get_groups)
                    unset($GLOBALS['g_comp_database']);
                    $GLOBALS['g_comp_database'] = $g_comp_database;

                    /* Get user dao and user data */
                    $l_user_dao = new isys_cmdb_dao_category_s_person_master($g_comp_database);
                    $l_userdata = $l_user_dao->get_person_by_username($p_username, C__RECORD_STATUS__NORMAL)
                        ->get_row();
                    $l_user_id  = -1;

                    /* Try other auths, if user does not exist in database. */
                    if ($l_userdata)
                    {
                        // Set user id if password was accepted.
                        if ($l_userdata['isys_cats_person_list__user_pass'] === $l_md5_pass)
                        {
                            $l_user_id = $l_userdata["isys_obj__id"];
                        }
                    }

                    if ($l_user_id === -1)
                    {
                        if (!isset($l_ldap_done[$l_dbdata["isys_mandator__db_name"]]))
                        {

                            $l_user_id = $this->ldap_login(
                                $p_username,
                                $p_password,
                                null,
                                $l_user_dao
                            );
                        }
                    }

                    // Check if user_id is allowed to login.
                    if ($l_user_id > 0)
                    {
                        // Removed: isys_rs_system, check if the given user owns at least one group / role.
                        $l_mandants[$l_dbdata["isys_mandator__id"]] = [
                            'id'      => $l_dbdata["isys_mandator__id"],
                            'user_id' => $l_user_id,
                            'title'   => $l_dbdata["isys_mandator__title"]
                        ];

                        /*
                        if ($p_with_language)
                        {
                            $l_locale             = isys_locale::get($g_comp_database, $l_user_id);
                            $l_language_constant  = $l_locale->get_setting(LC_LANG);
                            $l_preferred_language = $l_locale->resolve_language_by_constant($l_language_constant);
                            $l_locale->reset_cache();
                            $l_mandants[$l_dbdata["isys_mandator__id"]]['preferred_language'] = $l_preferred_language;
                        } // if
                        */
                    } // if

                    $l_ldap_done[$l_dbdata["isys_mandator__db_name"]] = true;
                } // if
            } // while

            if (count($l_mandants) === 0)
            {
                // Log failed login:
                isys_application::instance()->logger->addWarning(
                    'Login failed for user ' . $p_username,
                    [
                        'username'   => $p_username,
                        'ip-address',
                        $_SERVER['REMOTE_ADDR'],
                        'user-agent' => $_SERVER['HTTP_USER_AGENT']
                    ]
                );
            }

            return $l_mandants;
        } // if

        return null;
    }

    /**
     * @param string                                 $p_username
     * @param string                                 $p_password
     * @param string                                 $p_userdn
     * @param isys_cmdb_dao_category_s_person_master $p_user_dao
     */
    public function ldap_login($p_username, $p_password, $p_userdn, $p_user_dao)
    {
        /* Check LDAP Auth, if module is installed and an LDAP-Server is registered for the current mandator */
        if (is_object($this->m_mod_ldap))
        {
            try
            {
                /* Call session login in ldap module */
                if ($this->m_mod_ldap->session_login($p_username, $p_password, $p_userdn, $p_user_dao))
                {
                    $l_obj_id = $p_user_dao->get_person_id_by_username($p_username, C__RECORD_STATUS__NORMAL);
                    if ($l_obj_id)
                    {
                        return $l_obj_id;
                    }
                    else
                    {
                        throw new Exception('Error: User was improperly created.');
                    }
                }
            }
            catch (Exception $e)
            {
                $this->m_mod_ldap->debug($e->getMessage());
            }
        }

        return false;
    } // function

    /**
     * Logs a user with $p_username and $p_password in to the database specified by $p_db.
     *
     *
     * @param   isys_component_database $p_db
     * @param   string                  $p_username
     * @param   string                  $p_password
     * @param   boolean                 $p_retbool
     * @param   boolean                 $p_md5pass
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function login(isys_component_database &$p_db, $p_username, $p_password, $p_retbool = false, $p_md5pass = false)
    {
        $l_password = ($p_md5pass == false) ? md5($p_password) : $p_password;

        // Search the user.
        $l_res = $this->query_session($p_db, $p_username);

        if ($p_db->num_rows($l_res) >= 1)
        {
            $this->m_logged_in = false;

            $l_row = $p_db->fetch_row_assoc($l_res);

            if ($l_row["isys_cats_person_list__user_pass"] == $l_password)
            {
                $this->m_logged_in = true;
            }
            else
            {
                if (is_object($this->m_mod_ldap))
                {
                    if ($this->m_mod_ldap->ldap_login($p_db, $p_username, $p_password, null, null, $l_row["isys_obj__id"]))
                    {
                        $this->m_logged_in = true;
                    } // if
                }
            } // if

            if ($this->m_logged_in)
            {
                /**
                 * User is logged in, so post init session
                 */
                $this->post_init_session($l_row);

                $p_db->query(
                    'UPDATE isys_cats_person_list SET isys_cats_person_list__last_login = NOW() WHERE isys_cats_person_list__isys_obj__id = ' . (int) $l_row["isys_obj__id"] .
                    ';'
                );

                return ($p_retbool) ? true : $l_row;
            } // if
        }

        return false;
    }

    /**
     * Destroys the current session.
     *
     * @return boolean
     */
    public function destroy()
    {
        try
        {
            if (session_id()) return @session_destroy();

            return false;
        }
        catch (ErrorException $e)
        {
            // session already closed
        }
    } // function

    /**
     * session_write_close wrapper
     */
    public function write_close()
    {
        @session_write_close();

        return $this;
    }

    /**
     * @return mixed
     */
    public function include_mandator_cache()
    {
        if ($this->m_mandator_data["isys_mandator__dir_cache"])
        {
            return isys_component_constant_manager::instance()
                ->include_dcm($this->m_mandator_data["isys_mandator__dir_cache"]);
        }
        else throw new Exception('Error: Tenant cache directory in system database (dir_cache) is not set');
    } // function

    /**
     * @return boolean
     * @desc Perform user logout, returns a boolean result:
     *       true for success and false for failure.
     */
    public function logout()
    {
        global $g_comp_database;

        /* Cache Session ID before calling session_destroy() */
        $l_SesID = $this->get_session_id();

        /* Drop current session from database */
        $this->delete_current_session();

        if ($this->destroy())
        {
            $this->m_username      = null;
            $this->m_logged_in     = false;
            $this->m_mandator_id   = null;
            $this->m_mandator_name = null;
            $this->m_mandator_data = null;
            $this->m_session_id    = null;
            $this->m_user_id       = null;
            unset($this->m_session_data);
        }

        //delete temporary tables which are not used currently
        if (is_object($g_comp_database))
        {
            $l_objDAOTable = new isys_component_dao_table($g_comp_database);
            $l_objDAOTable->clean_temp_tables_at_logout($l_SesID);
        }

        return true;
    }

    /**
     * Starts a session.
     *
     * @return  boolean
     */
    public function start_session()
    {
        if (!headers_sent())
        {
            if (isset($_COOKIE['PHPSESSID']) && $_COOKIE['PHPSESSID'] == '') unset($_COOKIE['PHPSESSID']);

            $l_ret            = session_start();
            $this->m_username = $_SESSION["username"];
        }
        else
        {
            $l_ret = false;
        } // if

        return $l_ret;
    }

    /**
     * Initialize a session in the database.
     *
     * @return  integer
     */
    public function start_dbsession()
    {
        global $g_comp_database, $_SERVER;

        if (is_object($g_comp_database))
        {
            $l_res = $this->query_session($g_comp_database, null, $this->get_session_id());

            // Is a user session existent ..?
            if ($g_comp_database->num_rows($l_res) == 0)
            {
                // NO - so add the session.
                $l_query = "INSERT INTO isys_user_session SET
				    isys_user_session__isys_obj__id = 1,
				    isys_user_session__php_sid = '" . $this->get_session_id() . "',
				    isys_user_session__time_login = CURRENT_TIMESTAMP,
				    isys_user_session__time_last_action = CURRENT_TIMESTAMP,
				    isys_user_session__description = '" . $g_comp_database->escape_string($_SERVER['REQUEST_URI']) . "',
				    isys_user_session__ip = '" . $g_comp_database->escape_string($_SERVER['REMOTE_ADDR']) . "';";

                if ($g_comp_database->query($l_query))
                {
                    return $g_comp_database->get_last_insert_id();
                } // if
            }
            else
            {
                // YES - update and use existing session.
                $l_query = "UPDATE isys_user_session SET
					isys_user_session__time_last_action = CURRENT_TIMESTAMP,
					isys_user_session__description = '" . $g_comp_database->escape_string($_SERVER['REQUEST_URI']) . "'
					WHERE isys_user_session__php_sid = '" . $this->get_session_id() . "'";

                $l_query = $g_comp_database->limit_update($l_query, 1);

                if ($g_comp_database->query($l_query))
                {
                    return $this->get_session_id();
                } // if
            } // if
        } // if

        return null;
    }

    /**
     * Returns the session record as associative array.
     *
     * @param   string                 $p_sessionid
     *
     * @return  array
     * @global isys_component_database $g_comp_database
     */
    public function get_session_data($p_sessionid = null)
    {
        if (!$this->m_session_data)
        {
            global $g_comp_database;

            if ($p_sessionid === null)
            {
                $p_sessionid = $this->get_session_id();
            } // if

            if (strlen($p_sessionid) > 0)
            {
                if ($g_comp_database && $g_comp_database instanceof isys_component_database)
                {
                    if ($g_comp_database->is_connected())
                    {
                        $l_res = $this->query_session($g_comp_database, null, $p_sessionid);

                        if ($g_comp_database->num_rows($l_res))
                        {
                            // User is logged in, so post initialize session.
                            $this->post_init_session(($this->m_session_data = $g_comp_database->fetch_array($l_res)));
                        } // if
                    } // if
                } // if
            } // if
        } // if

        return $this->m_session_data;
    } // function

    /**
     * Sets the cookie parameters.
     *
     * @param   integer $p_lifetime
     *
     * @return  isys_component_session
     */
    public function set_cookie_params($p_lifetime)
    {
        session_set_cookie_params($p_lifetime);

        return $this;
    } // function

    /**
     * @param $p_session_id
     *
     * @return $this
     */
    public function set_session_id($p_session_id)
    {
        $this->m_session_id = session_id($p_session_id);

        return $this;
    } // function

    /**
     * Returns the current session id.
     *
     * @return  string
     */
    public function get_session_id()
    {
        if (!$this->m_session_id) $this->m_session_id = session_id();

        return $this->m_session_id;
    }

    /**
     * Delete the expired sessions.
     *
     * @return  void
     * @global isys_component_database $g_comp_database
     */
    public function delete_expired_sessions()
    {
        global $g_comp_database;

        if (is_object($g_comp_database) && $g_comp_database->is_connected())
        {
            $l_query = "DELETE FROM isys_user_session WHERE " . $g_comp_database->date_sub(
                    "SECOND",
                    $this->m_session_time,
                    "NOW()"
                ) . " > isys_user_session__time_last_action";

            $g_comp_database->query($l_query);
        } // if
    } // function

    /**
     * Delete the current session from database.
     *
     * @return  void
     */
    public function delete_current_session()
    {
        global $g_comp_database;

        $_SESSION['groups'] = null;

        if (is_object($g_comp_database) && $g_comp_database->is_connected())
        {
            $g_comp_database->query('DELETE FROM isys_user_session WHERE (isys_user_session__php_sid = "' . $g_comp_database->escape_string($this->get_session_id()) . '");');
        } // if
    } // function

    /**
     * Gets the current mandator as record-ID.
     *
     * @return  integer
     */
    public function get_current_mandator_as_id()
    {
        global $g_comp_database_system, $g_db_system;

        $l_res = $this->query_mandator();

        if ($g_comp_database_system->num_rows($l_res))
        {
            while ($l_dbdata = $g_comp_database_system->fetch_array($l_res))
            {
                // Destruct old database object.
                if (isset($l_db))
                {
                    unset($l_db);
                } // if

                // Create connection to mandator DB.
                $l_db = isys_component_database::get_database(
                    $g_db_system["type"],
                    $l_dbdata["isys_mandator__db_host"],
                    $l_dbdata["isys_mandator__db_port"],
                    $l_dbdata["isys_mandator__db_user"],
                    $l_dbdata["isys_mandator__db_pass"],
                    $l_dbdata["isys_mandator__db_name"]
                );

                if ($l_db->is_connected())
                {
                    $l_sessres = $this->query_session($l_db, null, $this->get_session_id());

                    if ($l_db->num_rows($l_sessres))
                    {
                        // If we have more language for _one_ database, the session is in all mandators. This check avoids that a wrong language is used as current mandator.
                        if ($l_dbdata["isys_mandator__id"] == $_SESSION["user_mandator"])
                        {
                            return (int) $l_dbdata["isys_mandator__id"];
                        } // if
                    } // if
                } // if
            } // while
        } // if

        return null;
    }

    /**
     * Initialization, which is only possible after user has logged in
     *
     * @param $p_session_data
     */
    protected function post_init_session($p_session_data)
    {

        /**
         * Store session data
         */
        $this->m_session_data = $p_session_data;

        $this->write_userdata($p_session_data);

        // Include and write mandator cache
        $this->include_mandator_cache();

        // Delete expired sessions
        $this->delete_expired_sessions();

        /**
         * Initialize user settings
         */
        try
        {
            isys_usersettings::initialize(isys_application::instance()->database);
            isys_tenantsettings::initialize(isys_application::instance()->database_system);
        }
        catch (Exception $e)
        {
            isys_glob_display_error($e->getMessage());
            die();
        } // try

        /**
         * Initialize locales
         */
        isys_core::init_locales(
            isset($p_session_data["isys_cats_person_list__isys_obj__id"]) ? $p_session_data["isys_cats_person_list__isys_obj__id"] : $p_session_data["isys_obj__id"]
        );

        // Re-set the language (if necessary).
        $l_lang = $this->get_language();

        if (!$l_lang)
        {
            if (isset($_SESSION['lang']) && $_SESSION['lang'])
            {
                $l_lang = $_SESSION['lang'];
            } // if
            else
            {
                $l_lang = isys_locale::get_instance()
                    ->resolve_language_by_constant(
                        isys_locale::get_instance()
                            ->get_setting(LC_LANG)
                    ) ?: isys_tenantsettings::get('system.default-language', 'en');
            }

        } // if

        /**
         * @var $g_comp_template_language_manager isys_component_template_language_manager
         */
        global $g_comp_template_language_manager;
        if (!is_object($g_comp_template_language_manager))
        {
            $g_comp_template_language_manager = new isys_component_template_language_manager($l_lang);
        }
        else
        {
            if (isys_application::instance()->language != $l_lang || $g_comp_template_language_manager->get_loaded_language() != $l_lang)
            {
                $g_comp_template_language_manager->load($l_lang);
            } // if
        }
        $this->set_language($l_lang);
    } // function

    /**
     * @return boolean
     *
     * @param integer $p_userid
     *
     * @desc Write a User-ID to a session. It is private and used by the
     *       method 'login'.
     */
    private function write_userid($p_userid)
    {
        global $g_comp_database;

        $this->m_user_id = $p_userid;

        if ($p_userid > 0)
        {
            $l_query = "UPDATE isys_user_session SET isys_user_session__isys_obj__id ='" . $g_comp_database->escape_string(
                    $p_userid
                ) . "' " . "WHERE isys_user_session__php_sid='" . $this->get_session_id() . "';";

            return !!($g_comp_database->query($l_query));
        }

        throw new Exception('There was a problem writing your user id to session.');
    } // function

    /**
     * Query isys_mandator in system database.
     *
     * @param   string  $p_apikey
     * @param   boolean $p_onlyactive
     *
     * @return  mixed
     */
    private function query_mandator($p_apikey = null, $p_onlyactive = true)
    {
        global $g_comp_database_system;

        $l_condition = '';

        if ($p_apikey)
        {
            $l_condition .= ' AND (isys_mandator__apikey = \'' . $g_comp_database_system->escape_string($p_apikey) . '\')';
        } // if

        if ($p_onlyactive)
        {
            $l_condition .= ' AND (isys_mandator__active = 1)';
        } // if

        if (!($l_sortby = isys_settings::get('login.tenantlist.sortby')))
        {
            $l_sortby = 'isys_mandator__title';
        } // if

        return $g_comp_database_system->query('SELECT * FROM isys_mandator WHERE TRUE ' . $l_condition . ' ORDER BY ' . $l_sortby . ' ASC');
    } // function

    /**
     * Query isys_user_session in mandator database
     *
     * @param $p_session_id
     *
     * @return mixed
     */
    private function query_session(isys_component_database &$p_db, $p_username = null, $p_session_id = null, $p_condition = '')
    {
        $l_condition = $p_condition;

        if ($p_session_id)
        {
            $l_condition .= ' AND isys_user_session__php_sid = "' . $p_db->escape_string($p_session_id) . '";';
        }

        if ($p_username)
        {
            $l_condition .= ' AND (isys_cats_person_list__title = \'' . $p_db->escape_string($p_username) . '\')';
        }

        return $p_db->query(
            'SELECT isys_obj__id, isys_obj__title, isys_cats_person_list__isys_obj__id, isys_cats_person_list__title, isys_cats_person_list__first_name, ' .
            'isys_cats_person_list__user_pass, isys_cats_person_list__last_name, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, isys_user_session.* ' .
            'FROM isys_cats_person_list ' . 'INNER JOIN isys_obj ON isys_cats_person_list__isys_obj__id = isys_obj__id ' .
            'LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1 ' .
            'LEFT JOIN isys_user_session ON isys_user_session__isys_obj__id = isys_obj__id ' . 'WHERE (isys_obj__status = \'' . C__RECORD_STATUS__NORMAL . '\')' . $l_condition
        );
    } // function

    /**
     * Private wakeup method to prevent multiple instances via unserialization.
     */
    final private function __wakeup()
    {
        ;
    } // function

    /**
     * Private clone method to prevent multiple instances.
     */
    final private function __clone()
    {
        ;
    } // function

    /**
     * Constructing session management.
     *
     * @param  isys_module_ldap $p_ldap_module
     * @param  integer          $p_session_time
     */
    private function __construct(isys_module_ldap $p_ldap_module = null, $p_session_time = 300)
    {
        // LDAP Module dependency injection.
        $this->m_mod_ldap = $p_ldap_module;

        $this->m_session_time = $p_session_time;
    } // function
} // class