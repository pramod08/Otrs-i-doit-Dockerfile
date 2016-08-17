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
 * @author      Dennis Stuecken
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

/**
 * @param  $p_tenant_id
 */
function connect_mandator($p_tenant_id)
{
    global $g_comp_database, $l_dao_mandator, $l_licence_mandator, $g_db_system;

    $l_licence_mandator = $l_dao_mandator->get_mandator($p_tenant_id, 0);
    $l_dbdata           = $l_licence_mandator->get_row();

    // Create connection to mandator DB.
    isys_application::instance()->database = isys_component_database::get_database(
        $g_db_system["type"],
        $l_dbdata["isys_mandator__db_host"],
        $l_dbdata["isys_mandator__db_port"],
        $l_dbdata["isys_mandator__db_user"],
        $l_dbdata["isys_mandator__db_pass"],
        $l_dbdata["isys_mandator__db_name"]
    );

    $g_comp_database = isys_application::instance()->database;
} // function

/**
 * Install a module zip file.
 *
 * @param   string $p_moduleFile
 * @param   string $p_tenant Input '0' for all.
 *
 * @throws  Exception
 * @return  boolean
 */
function install_module_by_zip($p_moduleFile, $p_tenant = '0')
{
    global $g_absdir;

    // Checking for zlib and the ZipArchive class to solve #4853
    if (class_exists("ZipArchive") && extension_loaded('zlib'))
    {
        $l_files = new isys_update_files();

        if ($l_files->read_zip($p_moduleFile, $g_absdir, false, true))
        {

            if (file_exists($g_absdir . '/package.json'))
            {
                $l_package = json_decode(file_get_contents($g_absdir . '/package.json'), true);

                /**
                 * Start module installation
                 */
                $l_result = install_module($l_package, $p_tenant);

                // Move package.
                if (!file_exists($g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json'))
                {
                    rename($g_absdir . '/package.json', $g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');
                }
                else
                {
                    unlink($g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');
                    rename($g_absdir . '/package.json', $g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');
                } // if

                return $l_result;
            }
            else
            {
                throw new Exception('Error: package.json was not found.');
            } // if

        }
        else
        {
            throw new Exception('Error: Could not read zip package.');
        }
    }
    else
    {
        throw new Exception('Error: Could not extract zip file. Please check, if the zip and zlib PHP extensions are installed.');
    } // if
} // function

/**
 * Install module by it's identifier
 *
 * @param array  $p_packageJSON
 * @param string $p_tenant
 *
 * @global       $g_comp_database        isys_component_database
 * @global       $g_comp_database_system isys_component_database
 *
 * @throws Exception
 * @throws isys_exception_general
 */
function install_module(array $p_packageJSON, $p_tenant = '0')
{
    /**
     * Initialize
     */
    global $g_absdir, $g_product_info, $l_dao_mandator, $g_comp_database_system, $g_comp_database, $g_dcs;
    $l_module_manager = new isys_module_manager();
    $l_db_update      = new isys_update_xml();
    $l_files          = new isys_update_files();

    $l_tenants = [];

    if (isset($p_packageJSON['requirements']['core']))
    {
        $l_requirements = explode(' ', $p_packageJSON['requirements']['core']);

        if (!isset($l_requirements[1]))
        {
            throw new Exception('Invalid package.json format. Could not read requirements');
        } // if

        $l_current_version     = $g_product_info['version'];
        $l_version_requirement = $l_requirements[1];
        $l_operator            = $l_requirements[0];

        if (!version_compare($l_current_version, $l_version_requirement, $l_operator))
        {
            switch ($l_requirements[0])
            {
                case '>=':
                    throw new Exception(
                        sprintf(
                            'Error: i-doit Version requirement for this module does not match: Core %s. Update to version %s and try again.',
                            $p_packageJSON['requirements']['core'],
                            $l_requirements[1]
                        )
                    );
                    break;
                case '<=':
                    throw new Exception(
                        sprintf(
                            'Error: i-doit Version requirement for this module does not match: Core %s. Update to version %s and try again.',
                            $p_packageJSON['requirements']['core'],
                            $l_requirements[1]
                        )
                    );
                    break;
            } // switch
        } // if
    }
    else
    {
        throw new Exception('Invalid package.json format. Core requirement missing');
    } // if

    if (isset($p_packageJSON['dependencies']['php']) && is_array($p_packageJSON['dependencies']['php']))
    {
        foreach ($p_packageJSON['dependencies']['php'] as $l_dependency)
        {
            if (!extension_loaded($l_dependency))
            {
                throw new Exception(sprintf('Error: PHP extension %s needed for this module. Please install the extension and try again.', $l_dependency));
            } // if
        } // foreach
    } // if

    // Prepare mandator array.
    if ($p_tenant != '0')
    {
        $l_tenants = [$p_tenant];
    }
    else
    {
        $l_tenant_result = $l_dao_mandator->get_mandator();

        while ($l_row = $l_tenant_result->get_row())
        {
            $l_tenants[] = $l_row['isys_mandator__id'];
        } // while
    } // if

    // Include module installscript if available.
    if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/isys_module_' . $p_packageJSON['identifier'] . '_install.class.php'))
    {
        include_once($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/isys_module_' . $p_packageJSON['identifier'] . '_install.class.php');
    } // if

    // Delete files if necessary.
    if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_files.xml'))
    {
        $l_files->delete($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install');
    } // if

    // Iterate through prepared mandators and install module into each of them.
    foreach ($l_tenants as $l_tenant)
    {
        if ($l_tenant > 0)
        {
            // Connect mandator database $g_comp_database.
            connect_mandator($l_tenant);

            // Install module with package.
            $l_module_id = $l_module_manager->install($p_packageJSON);

            // Update Databases.
            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_data.xml'))
            {
                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_data.xml', $g_comp_database);
            } // if

            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_sys.xml'))
            {
                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_sys.xml', $g_comp_database_system);
            } // if

            // Call module installscript if available.
            $l_installclass = 'isys_module_' . $p_packageJSON['identifier'] . '_install';
            if (class_exists($l_installclass))
            {
                // When a package.json already exists, this is an update.
                if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/package.json'))
                {
                    $p_installtype = 'update';
                }
                else
                {
                    $p_installtype = 'install';
                } // if

                call_user_func_array(
                    [
                        $l_installclass,
                        'init'
                    ],
                    [
                        $g_comp_database,
                        $g_comp_database_system,
                        $l_module_id,
                        $p_installtype
                    ]
                );

                // Set installdate in system settings
                if (is_object($g_comp_database_system))
                {
                    $g_comp_database_system->query(
                        'REPLACE INTO isys_settings SET isys_settings__key = \'admin.module.' . $p_packageJSON['identifier'] . '.installed\', isys_settings__value = \'' . time(
                        ) . '\', isys_settings__isys_mandator__id = \'' . $l_tenant . '\';'
                    );
                }
            } // if
        }
    } // foreach

    // Delete cache.
    $l_deleted   = [];
    $l_undeleted = [];
    isys_glob_delete_recursive($g_absdir . '/temp/', $l_deleted, $l_undeleted);

    // Re-Create constant cache.
    $g_dcs = isys_component_constant_manager::instance()
        ->create_dcs_cache();

    return true;
}

/**
 * Replace config $p_config_location with template $p_config_template and data from $p_data (key => value)
 *
 * @param       $p_config_template
 * @param       $p_config_location
 * @param array $p_data
 */
function write_config($p_config_template, $p_config_location, $p_data = [])
{
    if (file_exists($p_config_template))
    {
        if (is_writable(dirname($p_config_location)))
        {
            return file_put_contents($p_config_location, strtr(file_get_contents($p_config_template), $p_data));
        }
        else throw new Exception('Config file ' . $p_config_location . ' is not writeable.');

    }
    else  throw new Exception('Config template ' . $p_config_template . ' dies not exist.');
}