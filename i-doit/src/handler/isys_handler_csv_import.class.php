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
 * Import handler
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_csv_import extends isys_handler
{
    /**
     * Desctructs this object
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function __destruct()
    {
        if (empty($_SERVER['HTTP_HOST']))
        {
            $this->logout();
        } // if
    } // function

    /**
     * Initializes the handler.
     * A login is always needed here because controller.php?load=handler is also reachable from outside (webserver) without any permission checks.
     * To prevent a flood attack or any other malicious attack, change the view permission of controller.php in .htaccess.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session;

        verbose(C__CONSOLE_LOGO__IDOIT . ' import');
        verbose(C__COLOR__GREEN . 'csv-Import handler initialized at ' . C__COLOR__NO_COLOR . date('Y-m-d H:i:s'));

        if ($g_comp_session->is_logged_in())
        {
            try
            {
                $this->process();

                return true;
            }
            catch (Exception $e)
            {
                verbose($e->getMessage());
            } // try
        } // if

        return false;
    } // function

    /**
     * Prints out the usage of rhe import handler
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function usage()
    {
        global $g_config;

        verbose("Wrong usage!");

        if (!empty($_SERVER['HTTP_HOST']))
        {
            verbose("Example:");
            verbose("  http://" . $_SERVER['HTTP_HOST'] . $g_config["www_dir"] . "controller.php?load=csv_import&file=[relative-path-to-csv-file]&profile=[profile-id]");
        }
        else
        {
            verbose("Example:");
            verbose("  ./controller -u USERNAME -p PASSWORD -i TENANT_ID -m csv_import ABSOLUTE/PATH/TO/CSV/FILE PROFILE_ID FIELD_SEPARATOR MULTIVALUE_MODE");
        } // if

        verbose('');
        verbose('ABSOLUTE/PATH/TO/CSV/FILE: Path to csv file');
        verbose('               PROFILE_ID: ID of csv profile');
        verbose('          FIELD_SEPARATOR: Field seperator to seperate csv values, needs to be in parenthesis');
        verbose('          MULTIVALUE_MODE: Select multivalue mode. Possible modes are "row", "column" or "comma"');
        verbose('');
        verbose('Example: /var/www/controller -u admin -p admin -i 1 -m csv_import /var/www/imports/idoit-Demo-CSV-Import.csv 1 ";" column');
        verbose('');

        $l_profiles = isys_module_import_csv::get_profiles();

        if (is_array($l_profiles) && count($l_profiles))
        {
            verbose('List of profiles:');

            foreach ($l_profiles AS $l_profile)
            {
                verbose(str_pad($l_profile['id'], 5, ' ', STR_PAD_LEFT) . ': ' . $l_profile['title']);
            } // foreach
        }
        else
        {
            verbose('Attention: No profiles found! You need to provide at least one profile to import a CSV file! Please create one using the CSV import GUI.');
        } // if
    } // function

    /**
     * Starts the import process.
     *
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @return  boolean
     */
    public function process()
    {
        global $argv;

        try
        {
            // Initialize:
            $l_file                   = null;
            $l_profile_id             = null;
            $l_delimiter              = ';';
            $l_multivalue_mode        = 'row';
            $l_object_type_assignment = [];

            if (!empty($_SERVER['HTTP_HOST']))
            {
                $l_file       = $_GET['file'];
                $l_profile_id = $_GET['profileID'];
            }
            else
            {
                // Set configuration variables.
                if (is_array($argv))
                {
                    $l_cmd             = $argv;
                    $l_file            = $l_cmd[0];
                    $l_profile_id      = $l_cmd[1];
                    $l_delimiter       = $l_cmd[2];
                    $l_multivalue_mode = $l_cmd[3];
                }
                else
                {
                    $this->usage();

                    return false;
                } // if
            } // if

            // Check for unsetted but necessary configuration parameters.
            if (empty($l_file) || empty($l_profile_id) || empty($l_delimiter) || empty($l_multivalue_mode))
            {
                $this->usage();

                return false;
            } // if

            // Ensure that setted multivalue mode is valid.
            if (!in_array(
                $l_multivalue_mode,
                [
                    'row',
                    'column',
                    'comma'
                ]
            )
            )
            {
                $this->usage();

                return false;
            } // if

            // Check file:
            if (!file_exists($l_file))
            {
                verbose('[ERROR] File "' . $l_file . '" does not exist.');

                return false;
            } // if

            // Load Profile.
            if (is_numeric($l_profile_id))
            {
                $l_profiles = isys_module_import_csv::get_profiles($l_profile_id);
                verbose('Retrieve profile with id #' . $l_profile_id . '...');

                if (is_array($l_profiles) && count($l_profiles))
                {
                    // Get first profile.
                    $l_profile = $l_profiles[0];

                    // Decode data attribute into array.
                    $l_profile['data'] = isys_format_json::decode($l_profile['data']);

                    // Check for filled profile
                    if (is_array($l_profile['data']))
                    {
                        // Some transformation work
                        $l_key_data               = [];
                        $l_transformed_assignment = [];

                        foreach ($l_profile['data']['assignments'] AS $l_index => $l_data)
                        {
                            if (empty($l_data['category']))
                            {
                                continue;
                            } // if

                            // Empty property means we have object_title, category.
                            if (!defined($l_data['category']) && empty($l_data['property']))
                            {
                                $l_key_data[$l_data['category']] = $l_index;
                            }
                            else
                            {
                                // Multivalue-mode: ROW.
                                if ($l_multivalue_mode == 'row')
                                {
                                    $l_transformed_assignment[$l_data['category']][$l_data['property']] = $l_index;
                                }
                                else
                                {
                                    $l_transformed_assignment[$l_index] = [
                                        'category' => $l_data['category'],
                                        'property' => $l_data['property'],
                                    ];
                                } // if

                                if (isset($l_data['object_type']) && isset($l_data['create_object']))
                                {
                                    $l_object_type_assignment[$l_index] = [
                                        'object-type'   => $l_data['object_type'],
                                        'create-object' => (int) $l_data['create_object']
                                    ];
                                } // if
                            } // if
                        } // foreach
                    }
                    else
                    {
                        verbose('[ERROR] Profile does not have any data.');

                        return false;
                    } // if

                    verbose('Profile ' . $l_profile['title'] . ' succesfully loaded.');
                }
                else
                {
                    verbose('[ERROR] Unable to load profile with ID #' . $l_profile_id);

                    return false;
                } // if
            }
            else
            {
                verbose('[ERROR] The given profile ID is not numeric');

                return false;
            } // if

            // Collect necessary information for the import process
            verbose('Initializing csv-import...');

            if (defined($l_profile['data']['globalObjectType']))
            {
                $l_profile['data']['globalObjectType'] = constant($l_profile['data']['globalObjectType']);
            } // if

            // Initialize csv module
            $l_module_csv = new isys_module_import_csv(
                $l_file,
                $l_delimiter,
                $l_multivalue_mode,
                $l_key_data['object_title'],
                $l_profile['data']['globalObjectType'],
                $l_key_data['object_type_dynamic'],
                $l_key_data['object_purpose'],
                $l_key_data['object_category'],
                $l_key_data['object_sysid'],
                $l_key_data['object_cmdbstatus'],
                $l_key_data['object_description'],
                (bool) $l_profile['data']['headlines'],
                $l_profile['data']['additionalPropertySearch'],
                $l_profile['data']['multivalueUpdateMode'],
                \idoit\Component\Logger::INFO
            );

            if (count($l_profile['data']['identificationKeys']) > 0)
            {
                $l_csv_idents  = [];
                $l_identifiers = [];
                foreach ($l_profile['data']['identificationKeys'] AS $l_data)
                {
                    $l_csv_idents[]  = $l_data['csvIdent'];
                    $l_identifiers[] = $l_data['localIdent'];
                }

                $l_module_csv::set_update_identifiers($l_identifiers);
                $l_module_csv::set_update_csv_idents($l_csv_idents);
            } // if

            $l_module_csv->initialize($l_transformed_assignment, $l_object_type_assignment);

            // Trigger import.
            $l_module_csv->import();

            // ID-2890 - Log changes.
            $l_import_dao   = new isys_module_dao_import_log(isys_application::instance()->database);
            $l_import_entry = $l_import_dao->add_import_entry(
                $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__CSV'),
                str_replace(C__IMPORT__CSV_DIRECTORY, '', $l_file),
                null // (((bool) $_POST['profile_loaded']) ? $_POST['profile_sbox'] : null) // What is this field for?
            );

            $l_module_csv->save_log($l_import_entry);

            // Output log
            if (file_exists($l_module_csv->get_log_path()) && is_readable($l_module_csv->get_log_path()))
            {
                echo file_get_contents($l_module_csv->get_log_path());
            } // if

            verbose("Successfully imported data.");

            return true;
        }
        catch (Exception $e)
        {
            verbose("[ERROR] An error occured: " . $e->getFile() . ' in line' . $e->getLine() . ' with message \'' . $e->getMessage() . '\'');
        } // try

        return true;
    } // function

    /**
     * Constructs this object.
     */
    public function __construct()
    {
        global $g_comp_session;

        if (!isset($_SERVER['HTTP_HOST']) && !$g_comp_session->is_logged_in())
        {
            if (!defined("C__CSV_HANDLER__IMPORT"))
            {
                verbose(
                    'CSV-Import handler configuration not loaded. Check the example in "' . BASE_DIR . 'src/handler/config/examples/isys_handler_import.inc.php" and copy it to "' . BASE_DIR . 'src/handler/config".'
                );
            }
            else
            {
                error('Please login.');
            } // if

            die;
        } // if
    } // function
} // class
