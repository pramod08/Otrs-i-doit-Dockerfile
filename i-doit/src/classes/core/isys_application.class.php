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

use idoit\Component\ContainerFacade;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * i-doit main application controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_application
{
    /**
     * @var isys_application
     */
    private static $m_instance = null;

    /**
     * @var string
     */
    public $app_path = './';

    /**
     * Dependency injection container
     *
     * @var ContainerFacade
     */
    public $container = null;

    /**
     * @var isys_component_database
     */
    public $database = null;

    /**
     * @var isys_component_database
     */
    public $database_system = null;

    /**
     * Also known as $g_product_info
     *
     * @var isys_array
     */
    public $info = null;

    /**
     * @var string
     */
    public $language = 'en';

    /**
     * @var Logger
     */
    public $logger = null;

    /**
     * @var isys_module
     */
    public $module = null;

    /**
     * @var isys_component_session
     */
    public $session = null;

    /**
     * @var isys_component_template
     */
    public $template = null;

    /**
     * @var isys_tenant
     */
    public $tenant = null;

    /**
     * @var string
     */
    public $www_path = '/';

    /**
     * @return isys_application|null
     */
    final public static function instance()
    {
        if (!self::$m_instance)
        {
            self::$m_instance = new self();
        } // if

        return self::$m_instance;
    } // function

    /**
     * "The Run Loop"
     *
     * @param isys_request_controller $p_req
     *
     * @throws Exception
     */
    final public static function run(isys_request_controller $p_req)
    {
        /**
         * Parse routes
         */
        if (!$p_req->parse())
        {
            // If request controller parsing fails, this means we're not using a path URI right now
            // So, fall back to the "old" request handling

            $l_mod_id = $_GET[C__GET__MODULE_ID];

            // If no module has been selected, select the CMDB.
            if (!isset($_GET[C__GET__MODULE_ID]))
            {
                $l_mod_id = C__MODULE__CMDB;
            } // if

            // Boot load the module
            self::legacyboot($l_mod_id, isys_register::factory('request'));
        }

    }

    /**
     * 404 handler
     *
     * @param isys_register $p_request
     */
    protected static function error404(isys_register $p_request)
    {
        isys_application::instance()->container->notify->error('Error 404: Path not found.');
    }

    /**
     * Module Boot Loader
     *
     * @param int           $p_module_id
     * @param isys_register $request
     */
    final private static function legacyboot($p_module_id, $request)
    {
        global $g_modman;

        if (isset($g_modman) && is_object($g_modman))
        {
            // Check for access to the module.
            if (is_numeric($p_module_id))
            {
                try
                {
                    // Set module instance to isys_application::$module.
                    self::instance()->module = $g_modman->load($p_module_id, $request);
                }
                catch (Exception $e)
                {
                    self::instance()->container->notify->error(
                        $e->getMessage(),
                        \dstuecken\Notify\Handler\HeaderHandler::formatAttributes(null, null, 1, null, null, '400px')
                    );
                    self::instance()->logger->addError(
                        $e->getMessage() . ' (' . str_replace(isys_application::instance()->app_path . '/', '', $e->getFile()) . ':' . $e->getLine() . ')'
                    );
                } // try
            }
            else
            {
                // Doing a logout to have a clean start for the next request.
                self::instance()->session->logout();

                if (defined("C__MODULE__CMDB") && is_numeric(C__MODULE__CMDB))
                {
                    die("Error: Module ID not numeric. Check your request or constant cache.");
                }
                else
                {
                    // Deleting constant cache
                    isys_component_constant_manager::instance()
                        ->clear_dcm_cache();

                    die("Error: Module ID not numeric. Your constant cache is not loaded!");
                } // if
            } // if
        } // if
    } // function

    /**
     * Set custom warnings handler
     */
    public function overrideErrorHandler()
    {
        // Set custom warnings handler and deactivate assertions.
        set_error_handler(
            [
                'isys_core',
                'warning_handler'
            ],
            E_WARNING & E_USER_WARNING
        );

        assert_options(ASSERT_ACTIVE, 0);
    }

    /**
     * The beginning of a structured bootstrapping
     *
     * @return  $this
     * @throws  Exception
     */
    final public function bootstrap()
    {
        try
        {
            global $g_comp_database_system, $g_db_system, $g_modman;

            // Override Logger from setting system.log.type.
            $logtype = isys_settings::get('system.log.type', 'file');

            if ($logtype != 'file')
            {
                $this->initLogger($logtype);
            }

            /**
             * Request service
             *
             * @return \Symfony\Component\HttpFoundation\Request
             */
            $this->container['request'] = function ()
            {
                return \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            };

            /**
             * Database service (System-DB)
             *
             * @return isys_component_database
             * @throws Exception
             */
            $this->container['database_system'] = function () use ($g_db_system)
            {
                return isys_component_database::get_database(
                    $g_db_system["type"],
                    $g_db_system["host"],
                    $g_db_system["port"],
                    $g_db_system["user"],
                    $g_db_system["pass"],
                    $g_db_system["name"]
                );
            };

            $g_comp_database_system = $this->database_system = $this->container['database_system'];

            /**
             * Create the notify service.
             *
             * @return \dstuecken\Notify\NotificationCenter
             */
            $this->container['notify'] = function ()
            {
                return new \dstuecken\Notify\NotificationCenter(
                    [
                        new \dstuecken\Notify\Handler\HeaderHandler('Idoit'),
                        new \dstuecken\Notify\Handler\SmartyHandler(isys_component_template::instance())
                    ]
                );
            };

            /**
             * Initialize signal slot collection.
             *
             * @return isys_component_signalcollection
             */
            $this->container['signals'] = function ()
            {
                return isys_component_signalcollection::get_instance();
            };

            // Initialize settings.
            isys_settings::initialize($this->database_system);

            // Set default timezone.
            date_default_timezone_set(isys_settings::get('system.timezone', 'Europe/Berlin'));

            // Initialize system constants.
            $this->init_constant_manager();

            // Load module manager.
            $g_modman = isys_module_manager::instance();

            // Initialize directories - this needs to be done, BEFORE the template component is initialized.
            $this->init_config_directories();

            // Preserve backward compatibility
            $this->backward_compatibility();

            // Initialize session.
            $this->init_session();

            // Initialize some config variables.
            $this->init_config_variables();

            // Obtain page limit from the settings. This can only work after initializing the session.
            global $g_page_limit;
            $g_page_limit = isys_usersettings::get('gui.objectlist.rows-per-page', 50);
        }
        catch (Exception $e)
        {
            throw $e;
        } // try

        return $this;
    } // function

    /**
     * Set application's language
     *
     * @param string $language
     *
     * @return isys_application
     */
    final public function language($language)
    {
        $this->language = $language;

        return $this;
    } // function

    /**
     * Main Request handler
     *
     * @param isys_register $p_request
     *
     * @throws Exception
     */
    final public function request(isys_register $p_request)
    {
        global $g_modman;

        if (isset($p_request->module))
        {
            /**
             * Get module instance
             */
            if (($l_module_id = $g_modman->is_installed($p_request->module)))
            {

                /**
                 * Load and start the module
                 */
                $this->module = $g_modman->load($l_module_id, $p_request);

                /**
                 * Preformat action to match class naming conventions
                 */
                $p_request->action = str_replace(' ', '', ucwords(str_replace('-', ' ', $p_request->action)));

                /**
                 * @var $l_controller isys_controller
                 */
                if (isset($p_request->action) && $p_request->action !== '')
                {
                    $l_class = 'idoit\\Module\\' . str_replace(' ', '', ucfirst(str_replace('_', ' ', $p_request->module))) . '\\Controller\\' . $p_request->action;
                    if (class_exists($l_class))
                    {
                        $l_controller = new $l_class($this->module);
                    }
                }
                else
                {
                    $l_class = 'idoit\\Module\\' . str_replace(' ', '', ucfirst(str_replace('_', ' ', $p_request->module))) . '\\Controller\\Main';
                    if (class_exists($l_class))
                    {
                        $l_controller = new $l_class($this->module);
                    }
                }

                /**
                 * Redirect to new controller
                 */
                if (isset($l_controller))
                {
                    // Call controller's pre route function
                    if (method_exists($l_controller, 'pre'))
                    {
                        $l_controller->pre($p_request, $this);
                    }

                    if (isset($p_request->method) && $p_request->method !== '' && method_exists($l_controller, $p_request->method))
                    {
                        // Call controller's handler
                        $l_view = call_user_func(
                            [
                                $l_controller,
                                $p_request->method
                            ],
                            $p_request,
                            $this
                        );
                    }
                    else
                    {
                        // Call controller's main handler
                        $l_view = $l_controller->handle(
                            $p_request,
                            $this
                        );
                    }

                    // If controller is a NavbarHandable, also call onNew, onSave etc. events
                    if (is_a($l_controller, 'idoit\Controller\NavbarHandable'))
                    {
                        $l_view = $this->handleNavBarEvents($l_controller, $p_request);
                    }

                    /**
                     * Process main tree
                     */
                    $this->process_tree($l_controller, $p_request);

                    // Call controller's post route funciton
                    if (method_exists($l_controller, 'post'))
                    {
                        $l_controller->post($p_request, $this);
                    }

                    /**
                     * Process and Render the view, if view is renderable
                     */
                    if ($l_view && is_a($l_view, 'idoit\View\Renderable', true))
                    {
                        $l_view->process(
                            $this->module,
                            isys_component_template::instance(),
                            $l_controller->dao($this)
                        );

                        /* auto assign data
                        isys_component_template::instance()->assign(
                            'data', $l_view->getData()
                        );
                        */

                        $l_view->render();
                    }
                }
                /**
                 * Load module via old deprecated way
                 *
                 * @deprecated
                 */
                else
                {
                    if (($l_mod_id = $g_modman->is_installed($p_request->module)))
                    {
                        // Boot load the module in it's legacy way
                        //
                    }
                    else
                    {
                        // Call 404 handler
                        self::error404($p_request);
                    }
                }
            }
        }
        else
        {
            throw new Exception(
                'Request error for request ' . isys_request_controller::instance()
                    ->path() . ' : ' . var_export($p_request, true)
            );
        }
    } // function

    /**
     * Process the tree by calling ->tree() on $p_controller.
     *
     * @return isys_application
     */
    public function process_tree(isys_controller $p_controller, isys_register $p_request)
    {
        /**
         * Initialize the main tree
         */
        $l_tree = isys_component_tree::factory('menu_tree');

        /**
         * Load tree by the controller
         */
        $l_nodes = $p_controller->tree($p_request, $this, $l_tree);

        if (is_object($l_nodes) && $l_nodes instanceof \idoit\Tree\Node)
        {
            /**
             * Payload isys_component_tree with a \idoit\Tree\Node tree structure
             */
            $l_tree->payload($l_nodes, $p_request);

            /**
             * Process tree and assign to template
             */
            isys_component_template::instance()
                ->assign(
                    "menu_tree",
                    $l_tree->process($_GET[C__GET__TREE_NODE])
                );
        }

        return $this;
    } // function

    /**
     * @param Exception $e
     */
    public function drawException(Exception $e)
    {
        isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons()
            ->show_navbar();
        $tree = new isys_cmdb_view_tree_objecttype(isys_module_request::get_instance());

        isys_application::instance()->template->assign('menu_tree', $tree->process())
            ->assign('message', $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')')
            ->assign('backtrace', $e->getTraceAsString())
            ->display('error.tpl');

        die();
    }

    /**
     * Destructor.
     *
     * @throws  Exception
     */
    final public function __destruct()
    {
        try
        {
            $this->container['signals']->emit('system.shutdown');
        }
        catch (InvalidArgumentException $e)
        {

        }
    } // function

    /**
     * Preserve backward compatibility (e.g. for modules)
     */
    private function backward_compatibility()
    {
        global $g_comp_signals;
        $g_comp_signals = $this->container['signals'];
    }

    /**
     * @param string $logType
     *
     * @return $this
     */
    private function initLogger($logType = 'file')
    {
        // create a log channel
        $this->container['logger'] = function () use ($logType)
        {
            switch ($logType)
            {
                case "file":
                default:
                    $logHandler = new StreamHandler(isys_settings::get('system.log.path', $this->app_path . '/log/system'), Logger::WARNING);
                    break;
            }

            $log = new Logger('i-doit');
            $log->pushHandler($logHandler);

            return $log;
        };

        $this->logger = $this->container['logger'];

        return $this;
    }

    /**
     * Initialize application's session
     *
     * @global $g_comp_database
     * @global $g_comp_template_language_manager
     * @global $g_comp_template
     * @global $g_comp_session
     * @global $g_loc
     * @global $g_mandator_info
     *
     * @throws Exception
     */
    private function init_session()
    {
        global $g_comp_database, $g_comp_template_language_manager, $g_comp_template, $g_comp_session, $g_loc, $g_mandator_info;

        // Initialize global session component.
        if (class_exists('isys_module_ldap'))
        {
            $g_comp_session = isys_component_session::instance(new isys_module_ldap(), isys_settings::get('session.time', 300));
        }
        else
        {
            $g_comp_session = isys_component_session::instance(null, isys_settings::get('session.time', 300));
        } // if

        $this->session = &$g_comp_session;

        // Start session.
        if ($this->session->start_session())
        {
            // Override session language - At this point, $this->language is only set when isys_application::instance()->set_language('xyz') was called beforehand.
            if ($this->language)
            {
                $this->session->set_language($this->language);
            } // if

            // Check if mandator is set yet and instantiate $g_comp_database for current mandator.
            if (isset($_SESSION["user_mandator"]))
            {
                $g_mandator_info = $this->session->connect_mandator($_SESSION["user_mandator"]);
            }
            else
            {
                $g_mandator_info = null;
            } // if

            if ($g_comp_database)
            {
                $this->database = &$g_comp_database;
            } // if

            // Initialize template language manager if not already initialized by isys_component_session::post_init_session
            if (!is_object($g_comp_template_language_manager))
            {
                $g_comp_template_language_manager = new isys_component_template_language_manager($this->language);
            }

            /**
             * Initialize Template library with SMARTY as backend.
             *
             * @return isys_component_template
             */
            $this->container['template'] = function ()
            {
                return isys_component_template::instance();
            };
            $this->template              = $g_comp_template = $this->container['template'];

            if ($g_mandator_info && is_object($this->database))
            {
                // Save Tenant Info.
                $this->tenant = new isys_tenant(
                    $g_mandator_info['isys_mandator__title'],
                    $g_mandator_info['isys_mandator__description'],
                    $g_mandator_info['isys_mandator__id'],
                    $g_mandator_info['isys_mandator__db_name'],
                    $g_mandator_info['isys_mandator__dir_cache']
                );

                // ------------------------------------------------ OVERRIDE USER CONFIG ---
                isys_glob_override_user_settings();

                // Backward compatibility for older modules
                // @todo Should be removed in one of the next major versions
                $GLOBALS['g_active_modreq'] = $GLOBALS['g_modreq'] = isys_module_request::get_instance();
                // ---------------------------------------------------------------------------------------

                // Initialize module manager.
                isys_module_manager::instance()
                    ->init(isys_module_request::get_instance());

                // Instantiate a dummy class in case we are not logged in.
                if (!$g_loc)
                {
                    $g_loc = isys_locale::dummy();
                } // if
            }
            else
            {
                // Initialize Pro module, if existent. This case happens when there is no login.
                if (file_exists($this->app_path . '/src/classes/modules/pro/init.php'))
                {
                    include_once($this->app_path . '/src/classes/modules/pro/init.php');
                } // if
            } // if
        }
        else
        {
            $l_err = "Unable to start session!";
            if (headers_sent())
            {
                $l_err .= "\nHeaders already sent. There should not be any output before the session starts!";
            } // if

            throw new Exception($l_err);
        } // if
    }

    /**
     * Create and include system constants (temp/const_cache.inc.php)
     *
     * @global $g_dcs
     */
    private function init_constant_manager()
    {
        global $g_dcs;

        // Include Global constant cache.
        $g_dcs = isys_component_constant_manager::instance();
        $g_dcs->include_dcs();
    }

    /**
     * Initialize some config variables
     *
     * @global $g_config
     */
    private function init_config_variables()
    {
        global $g_config;

        /**
         * Sysid prefix for isys_obj__sysid
         */
        define("C__CMDB__SYSID__PREFIX", isys_tenantsettings::get('cmdb.sysid.prefix', 'SYSID_'));

        /* Attaches LDAP users automatically to these group ids (comma-separated)
            - Only one group is also possible
            - Only group IDs will work, e.g. 15 for admin. Contacts->Groups for more */
        define("C__LDAP__GROUP_IDS", isys_settings::get('ldap.default-group', '14'));

        // Activate LDAP Debugging into i-doit/log/ldap_debug.txt (boolean).
        define("C__LDAP__DEBUG", (bool) isys_settings::get('ldap.debug', true));

        // Maximum  amount of objects which are loaded into the tree of the object browser, the browser will not load at all if limit is reached.
        define("C__TREE_MAX_OBJECTS", isys_settings::get('cmdb.object-browser.max-objects', 1500)); // Numeric value

        $g_config["show_proc_time"] = isys_settings::get('system.show-proc-time', 0);
        $g_config["wiki_url"]       = isys_settings::get('gui.wiki-url', '');
        $g_config["wysiwyg"]        = isys_settings::get('gui.wysiwyg', '1');
        $g_config["use_auth"]       = isys_settings::get('auth.active', '1');
        $g_config['devmode']        = isys_settings::get('system.devmode', false);

        // Message for an outdated maintenance contract (HTML allowed).
        define(
        "C__WORKFLOW_MSG__MAINTENANCE", isys_settings::get(
            'email.template.maintenance',
            "Your maintenance contract: %s timed out.\n\n" . "<strong>Contract information</strong>:\n" . "Start: %s\n" . "End: %s\n" . "Support-Url: %s\n" .
            "Contract-Number: %s\n" . "Customer-Number: %s"
        )
        );

        // SYS-ID Readonly?
        define("C__SYSID__READONLY", !!isys_tenantsettings::get('cmdb.registry.sysid_readonly', 0));

        // How many chars should be visible in the infobox/logbook message (numeric).
        define("C__INFOBOX__LENGTH", isys_tenantsettings::get('gui.infobox.length', 150));

        // Default date format (php-dateformat: http://php.net/date).
        define("C__INFOBOX__DATEFORMAT", isys_tenantsettings::get('gui.infobox.dateformat', 'd.m.Y H:i :'));

        // Enable locking of datasets (objects)?
        define("C__LOCK__DATASETS", !!isys_tenantsettings::get('cmdb.registry.lock_dataset', 0));

        // Timeout of locked datasets in seconds.
        define("C__LOCK__TIMEOUT", isys_tenantsettings::get('cmdb.registry.lock_timeout', 120));
        define("C__TEMPLATE__COLORS", isys_tenantsettings::get('cmdb.template.colors', 1));
        define("C__TEMPLATE__COLOR_VALUE", isys_tenantsettings::get('cmdb.template.color_value', '#CC0000'));
        define("C__TEMPLATE__STATUS", isys_tenantsettings::get('cmdb.template.status', 0));
        define("C__TEMPLATE__SHOW_ASSIGNMENTS", isys_tenantsettings::get('cmdb.template.show_assignments', 1));
    }

    /**
     * Small method for specifically configurating the directories.
     */
    private function init_config_directories()
    {
        global $g_dirs;

        $g_dirs['class']   = $this->app_path . '/src/classes/';
        $g_dirs['css_abs'] = $this->app_path . '/src/themes/default/css/';
        $g_dirs['js_abs']  = $this->app_path . '/src/tools/js/';
        $g_dirs['smarty']  = $this->app_path . '/src/themes/default/smarty/';
        $g_dirs['temp']    = $this->app_path . '/temp/';
        $g_dirs['utils']   = $this->app_path . '/src/utils/';
        $g_dirs['images']  = rtrim($this->www_path, '/') . '/images/';
        $g_dirs['theme']   = rtrim($this->www_path, '/') . '/src/themes/default/';
        $g_dirs['tools']   = rtrim($this->www_path, '/') . '/src/tools/';
        $g_dirs["fileman"] = [
            "target_dir" => isys_settings::get('system.dir.file-upload', $this->app_path . '/upload/files/'),
            "temp_dir"   => $this->app_path . '/temp/',
            "image_dir"  => isys_settings::get('system.dir.image-upload', $this->app_path . '/upload/images/')
        ];
    }

    /**
     * @param isys_controller $p_controller
     *
     * @return \idoit\View\Renderable
     */
    private function handleNavBarEvents(isys_controller $p_controller, isys_register $p_request)
    {
        switch ($p_request->get('POST')
            ->get(C__GET__NAVMODE))
        {
            case C__NAVMODE__NEW:
                $eventFunction = 'onNew';
                break;
            case C__NAVMODE__PRINT:
                $eventFunction = 'onPrint';
                break;
            case C__NAVMODE__PURGE:
                $eventFunction = 'onPurge';
                break;
            case C__NAVMODE__DELETE:
                $eventFunction = 'onDelete';
                break;
            case C__NAVMODE__ARCHIVE:
                $eventFunction = 'onArchive';
                break;
            case C__NAVMODE__QUICK_PURGE:
                $eventFunction = 'onQuickPurge';
                break;
            case C__NAVMODE__RECYCLE:
                $eventFunction = 'onRecycle';
                break;
            case C__NAVMODE__DUPLICATE:
                $eventFunction = 'onDuplicate';
                break;
            case C__NAVMODE__RESET:
                $eventFunction = 'onReset';
                break;
            case C__NAVMODE__EDIT:
                $eventFunction = 'onEdit';
                break;
            case C__NAVMODE__CANCEL:
                $eventFunction = 'onCancel';
                break;
            case C__NAVMODE__SAVE:
                $eventFunction = 'onSave';
                break;
            case C__NAVMODE__UP:
                $eventFunction = 'onUp';
                break;
            default:
                $eventFunction = 'onDefault';
                break;
        }

        if ($eventFunction && method_exists($p_controller, $eventFunction))
        {
            return $p_controller->$eventFunction($p_request, $this);
        }

        return false;
    } // function

    /**
     * Private wakeup method to ensure singleton.
     */
    final private function __wakeup()
    {
        ;
    } // function

    /**
     * Private clone method to ensure singleton.
     */
    final private function __clone()
    {
        ;
    } // function

    /**
     * Private constructor
     *
     * @global $g_absdir
     * @global $g_product_info
     */
    private function __construct()
    {
        global $g_absdir, $g_product_info, $g_config;

        $this->overrideErrorHandler();

        $this->container = new ContainerFacade();

        $this->app_path = $g_absdir;
        $this->www_path = $g_config['www_dir'];

        if (!isset($g_product_info) || !is_array($g_product_info))
        {
            include_once($this->app_path . '/src/version.inc.php');
        }
        $this->info = ($g_product_info = new isys_array($g_product_info ?: []));

        $this->initLogger(isys_settings::get('system.log.type', 'file'));

        /*
        // Also retrieving system database info
        // Not needed, yet.
        $l_update = new isys_update();
        $l_info = $l_update->get_isys_info();

        $this->db_info = ($g_product_info = new isys_array(
            array(
                'version' => $l_info['version'],
                'type'    => class_exists('isys_module_pro_autoload') ? 'PRO' : 'OPEN',
                'step'    => ''
            ))
        );
        */
    } // function
} // class