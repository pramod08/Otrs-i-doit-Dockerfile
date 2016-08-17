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
 * Import handler
 *
 * @package    i-doit
 * @subpackage Handler
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_handler_import extends isys_handler
{

    /**
     * Log
     *
     * @var isys_import_log
     */
    protected $m_log;
    /**
     * Holds the registered import types
     *
     * @var string[]
     */
    private $m_types = [];

    /**
     * Desctructs this object
     *
     */
    public function __destruct()
    {
        if (empty($_SERVER['HTTP_HOST']))
        {
            $this->logout();
        }
        $this->m_log->flush_verbosity(true, false);
    }

    /**
     * Initializes the handler.
     *
     *  A login is always needed here because controller.php?load=handler is
     *  also reachable from outside (webserver) without any permission checks.
     *  To prevent a flood attack or any other malicious attack, change the view
     *  permission of controller.php in .htaccess.
     *
     * @return bool Success?
     */
    public function init()
    {

        global $g_comp_session;

        $this->m_log->notice(C__CONSOLE_LOGO__IDOIT . ' import');
        $this->m_log->notice(C__COLOR__GREEN . 'Import handler initialized at ' . C__COLOR__NO_COLOR . date('Y-m-d H:i:s'));
        // First flush to send header (hopefully there is no footer...):
        $this->m_log->flush_verbosity();

        if ($g_comp_session->is_logged_in())
        {

            try
            {

                /* Process import */
                $this->process();

            }
            catch (Exception $e)
            {
                $this->m_log->error($e->getMessage());
            }

            return true;
        }

        return false;
    }

    /**
     * Register currently installed import handler
     *
     * @return array
     */
    public function register_import_handler()
    {
        global $g_dirs;
        $l_handler_array = [];

        $l_not_supported = [
            'isys_import_handler.class.php',
            'isys_import_handler_cabling.class.php',
            'isys_import_handler_csv.class.php'
        ];

        $l_dir_import = $g_dirs["import"] . "handler/";
        if (is_dir($l_dir_import))
        {
            $l_import_fh = opendir($l_dir_import);
            while ($l_file = readdir($l_import_fh))
            {
                if ($l_file != "." && $l_file != ".." && !in_array($l_file, $l_not_supported) && is_file($l_dir_import . "/" . $l_file))
                {
                    $l_handler                = preg_replace("/^isys_import_handler_(.*?).class.php$/", "\\1", $l_file);
                    $l_handler_array[$l_file] = $l_file;

                    $this->register($l_handler, "isys_import_handler_" . $l_handler);
                }
            }
        }

        return $l_handler_array;
    }

    /**
     * Return registered import types
     *
     * @return array
     */
    public function get_import_types()
    {
        return $this->m_types;
    }

    /**
     * Prints out the usage of he import handler
     *
     */
    public function usage()
    {
        global $g_comp_database;
        global $g_config;

        $l_error = "Wrong usage!\n\n";

        if (!empty($_SERVER['HTTP_HOST']))
        {

            $l_error .= "Example: \n";
            $l_error .= "http://" . $_SERVER['HTTP_HOST'] . $g_config["www_dir"] . "controller.php?load=import&file=client_1.xml&type=inventory&obj_type=10&force=1\n\n";

        }
        else
        {
            $l_error .= "./import inventory-export.xml import-type [object-type-id] [--force] [object-id]\n";
            $l_error .= "\nExample for importing a client with an h-inventory xml export:\n" . "./import imports/client_1.xml inventory 10 --force\n\n";
        }

        $l_error .= "--force: Force enables updating of existing objects/imports,\n" . "         but unfortunately overwrites the imported categories.\n\n";

        $l_error .= "Known import-types are:\n";

        foreach ($this->m_types as $l_k => $l_t)
        {
            $l_error .= "\t" . $l_k;
            if ($l_k == "cmdb") $l_error .= " (default)";
            $l_error .= "\n";
        }

        $l_error .= "\nObject Types:\n\n";

        $l_error .= "ID  Object-Type\n";

        $l_dao    = new isys_cmdb_dao($g_comp_database);
        $l_otypes = $l_dao->get_types();
        while ($l_row = $l_otypes->get_row())
        {

            $l_error .= $l_row["isys_obj_type__id"] . ":  " . $l_row["isys_obj_type__const"] . "\n";

        }

        $this->m_log->error($l_error);
    }

    /**
     * Registers a new import type
     *
     *
     * @param string $p_handlername
     * @param string $p_handlerclass
     */
    public function register($p_handlername, $p_handlerclass)
    {
        $this->m_types[$p_handlername] = $p_handlerclass;
    }

    /**
     * Starts the import process.
     *
     * @return bool Success?
     */
    public function process()
    {
        global $argv;
        global $g_comp_database;

        // Initialize:
        $l_file        = null;
        $l_type        = null;
        $l_object_type = null;
        $l_cmd         = null;
        if (!empty($_SERVER['HTTP_HOST']))
        {
            $l_file        = $_GET['file'];
            $l_type        = $_GET['type'];
            $l_object_type = $_GET['obj_type'];
        }
        else
        {
            if (is_array($argv))
            {
                $l_cmd  = $argv;
                $l_file = $l_cmd[0];
                $l_type = $l_cmd[1];

                $l_object_type = $l_cmd[2];
            }
            else
            {
                return false;
            }
        }

        // Handle force and object id parameter:
        $l_object_id = null;
        if (!is_numeric($l_object_type))
        {
            $l_object_type = C__OBJTYPE__CLIENT;
            (!empty($_GET['force'])) ? $l_force = true : $l_force = $l_cmd[2];

            (!empty($_GET['object_id__HIDDEN'])) ? $l_object_id = $_GET['object_id__HIDDEN'] : $l_object_id = $l_cmd[3];

        }
        else
        {
            (!empty($_GET['force'])) ? $l_force = true : $l_force = $l_cmd[3];
            if (is_numeric($l_force)) $l_force = false;

            (!empty($_GET['object_id__HIDDEN'])) ? $l_object_id = $_GET['object_id__HIDDEN'] : $l_object_id = (isset($l_cmd[4])) ? $l_cmd[4] : $l_cmd[3];
        }
        if ($l_force == '--force')
        {
            $l_force = true;
        }

        if ($l_force == true)
        {
            $this->m_log->info('Running in force mode.');
        }

        // Print usage if no type is neither given nor found or if file name is empty:
        if (empty($l_type))
        {
            $l_type = 'cmdb';
            if (!isset($this->m_types[$l_type]))
            {
                $this->usage();

                return false;
            }
        }
        if (is_null($l_file) || $l_file == '')
        {
            $this->usage();

            return false;
        }

        // Check file:
        if (!file_exists($l_file))
        {
            $this->m_log->error(sprintf('File "%s" does not exist.', $l_file));

            return false;
        }

        // Get import handler:
        $l_import = null;

        try
        {
            $l_class = $this->m_types[$l_type];
            if (class_exists($l_class))
            {
                $this->m_log->debug(sprintf('Fetch import type %s and load handler %s.', $l_type, $l_class));
                $l_import = new $l_class($this->m_log, $g_comp_database);
            }
            else
            {
                if (empty($l_class))
                {
                    $this->m_log->error(sprintf('Type %s not registered.', $l_type));

                    return false;
                }
                else
                {
                    $this->m_log->error(sprintf('Class %s not found.', $l_class));

                    return false;
                }
            }
        }
        catch (Exception $e)
        {
            $this->m_log->error(sprintf('Type %s not found.', $l_type) . $e->getMessage());
        }

        // Load file:
        $this->m_log->notice(sprintf('Load import file %s.', $l_file));

        /**
         * @var $l_import isys_import_handler
         */
        if (method_exists($l_import, 'load_import'))
        {
            $l_import->load_import($l_file);

            $this->m_log->flush_verbosity(true, false);

            try
            {
                // Fetch data:
                if ($l_import->parse() === false)
                {
                    $this->m_log->error('Unknown parse error while parsing file');

                    return false;
                }

                $this->m_log->flush_verbosity(true, false);

                // Prepare data:
                $l_import->prepare();

                $this->m_log->flush_verbosity(true, false);

                // Import data:
                // @todo Delete parameter which are not used anymore.
                if (method_exists($l_import, 'import'))
                {
                    if ($l_import->import($l_object_type, $l_force, $l_object_id) === false)
                    {
                        $this->m_log->error('Process was aborted.');

                        return false;
                    }
                }

                $this->m_log->flush_verbosity(true, false);

                /**
                 * Post processing
                 */
                $l_import->post();

                unset($l_import);

                $this->m_log->info('Import done.');

                return true;
            }
            catch (Exception $e)
            {
                $this->m_log->error($e->getMessage());
            }
        }
        else
        {
            throw new Exception('Method load_import() does not exist in ' . get_class($l_import));
        }

        return false;
    }

    /**
     * Constructs this object
     *
     */
    public function __construct()
    {
        $this->register_import_handler();

        global $g_comp_session;
        global $g_absdir;

        // Start logging:
        $this->m_log = isys_factory_log::get_instance('import_cmdb');

        if (!isset($_SERVER['HTTP_HOST']) && !$g_comp_session->is_logged_in())
        {
            if (!defined("C__HANDLER__IMPORT"))
            {
                $this->m_log->error(
                    sprintf(
                        'Import handler configuration not loaded. ' . "\nCheck the example in %s and copy it to %s.",
                        $g_absdir . '/src/handler/config/examples/isys_handler_import.inc.php',
                        $g_absdir . '/src/handler/config'
                    )
                );
            }
            else
            {
                error('Please login.');
            }
            exit(1);
        }
    }

}

?>