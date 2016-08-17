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
global $g_comp_database, $g_config, $g_absdir, $g_comp_database_system, $g_comp_template, $g_product_info, $l_dao_mandator;

try
{
    $l_module_manager = new isys_module_manager();
    $l_dao_mandator   = new isys_component_dao_mandator($g_comp_database_system);

    switch ($_REQUEST['action'])
    {
        case 'lazyinstall':

            try
            {
                if (isset($_POST['module']) && isset($_POST['tenant']))
                {
                    // Include cmdb autoloader
                    if (include_once($g_absdir . '/src/classes/modules/cmdb/isys_module_cmdb_autoload.class.php'))
                    {
                        spl_autoload_register('isys_module_cmdb_autoload::init');
                    } // if

                    $l_package = json_decode(file_get_contents($g_absdir . '/src/classes/modules/' . $_POST['module'] . '/package.json'), true);

                    $l_response = [
                        'success' => install_module(
                            $l_package,
                            $_POST['tenant'] ?: '0'
                        ),
                        'message' => 'Module installed/updated successfully'
                    ];

                }
                else throw new Exception('Request error: module & tenant not received.');

            }
            catch (Exception $e)
            {
                $l_response = [
                    'error'   => $e->getMessage(),
                    'success' => false
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($l_response);

            die;

            break;
        case 'add':
            try
            {
                if (isset($_POST['mandator']))
                {
                    if (isset($_FILES['module_file']))
                    {
                        if ($_FILES['module_file']['tmp_name'])
                        {
                            // Include cmdb autoloader
                            if (include_once($g_absdir . '/src/classes/modules/cmdb/isys_module_cmdb_autoload.class.php'))
                            {
                                spl_autoload_register('isys_module_cmdb_autoload::init');
                            } // if

                            if (install_module_by_zip($_FILES['module_file']['tmp_name'], $_POST['mandator']))
                            {
                                $g_comp_template->assign("message", 'Module successfully installed.');
                            } // if
                        }
                        else
                        {
                            switch ($_FILES['module_file']['error'])
                            {
                                case UPLOAD_ERR_OK:
                                    break;
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $message = 'file too large - limit of ' . ini_get(
                                            'upload_max_filesize'
                                        ) . ' reached, check upload_max_filesize and post_max_size in php.ini';
                                    break;
                                case UPLOAD_ERR_PARTIAL:
                                    $message = 'file upload was not completed';
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    $message = 'zero-length file uploaded';
                                    break;
                                default:
                                    $message = 'internal error #' . $_FILES['newfile']['error'];
                                    break;
                            } // switch

                            throw new Exception('Error: There has been an error while uploading the file (' . $message . ')');
                        } // if
                    }
                    else
                    {
                        throw new Exception(
                            "Error: Package upload failed. Doublecheck the following php.ini settings against your uploaded package: \nfile_uploads, post_max_size, upload_max_filesize, upload_tmp_dir"
                        );
                    } // if
                }
                else
                {
                    throw new Exception('Error: Select a module');
                } // if
            }
            catch (Exception $e)
            {
                $g_comp_template->assign("error", nl2br($e->getMessage()));
            } // try

            break;
        case 'deactivate':

            if (isset($_REQUEST['module']) && is_array($_REQUEST['module']))
            {

                foreach ($_REQUEST['module'] as $l_tenant => $l_modules)
                {
                    if ($l_tenant)
                    {
                        connect_mandator($l_tenant);

                        foreach ($l_modules as $l_module)
                        {
                            if ($l_module)
                            {
                                $l_module_manager->deactivate($l_module);
                            }
                        }
                    }
                }

            }

            break;
        case 'uninstall':

            try
            {
                $l_moduleList  = [];
                $l_mandatorDBs = [];

                if (isset($_REQUEST['module']) && is_array($_REQUEST['module']))
                {
                    foreach ($_REQUEST['module'] as $l_tenant => $l_modules)
                    {
                        if ($l_tenant)
                        {
                            foreach ($l_modules as $l_module)
                            {
                                if ($l_module)
                                {
                                    $l_moduleList[] = $l_module;

                                }
                            }
                        }
                    }

                    if (is_array($l_moduleList) && count($l_moduleList) > 0)
                    {
                        $l_tenants = $l_dao_mandator->get_mandator();
                        while ($l_row = $l_tenants->get_row())
                        {
                            connect_mandator($l_row['isys_mandator__id']);
                            $l_mandatorDBs[] = $g_comp_database;
                        }

                        if (count($l_mandatorDBs) > 0)
                        {
                            // Include cmdb autoloader
                            if (include_once($g_absdir . '/src/classes/modules/cmdb/isys_module_cmdb_autoload.class.php'))
                            {
                                spl_autoload_register('isys_module_cmdb_autoload::init');
                            } // if

                            foreach ($l_moduleList as $l_moduleToDelete)
                            {
                                $l_module_manager->uninstall($l_moduleToDelete, $l_mandatorDBs);
                            }
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                $g_comp_template->assign("error", nl2br($e->getMessage()));
            } // try
            break;
        case 'activate':

            $l_db_update = new isys_update_xml();
            if (isset($_REQUEST['module']) && is_array($_REQUEST['module']))
            {
                foreach ($_REQUEST['module'] as $l_tenant => $l_modules)
                {
                    connect_mandator($l_tenant);

                    foreach ($l_modules as $l_module)
                    {
                        if ($l_module)
                        {
                            $l_module_manager->activate($l_module);

                            if (file_exists($g_absdir . '/src/classes/modules/' . $l_module . '/install/update_data.xml'))
                            {
                                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $l_module . '/install/update_data.xml', $g_comp_database);
                            }

                            if (file_exists($g_absdir . '/src/classes/modules/' . $l_module . '/install/update_sys.xml'))
                            {
                                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $l_module . '/install/update_sys.xml', $g_comp_database_system);
                            }
                        }
                    }
                }

            }

            break;
        default:

            break;
    }

    /**
     * Get mandators
     */
    $l_tenants = $l_dao_mandator->get_mandator();

    /**
     * Initialize modules
     */
    $l_directory = $g_absdir . '/src/classes/modules/';
    $l_modules   = [];
    $i           = 0;
    while ($l_tenant = $l_tenants->get_row())
    {
        connect_mandator($l_tenant['isys_mandator__id']);

        $l_dirhandle   = opendir($l_directory);
        $l_modules[$i] = [
            'id'       => $l_tenant['isys_mandator__id'],
            'title'    => $l_tenant['isys_mandator__title'],
            'active'   => $l_tenant['isys_mandator__active'],
            'licenced' => !!@$l_tenant['isys_licence__id'],
            'expires'  => $l_tenant['isys_licence__expires'],
            'host'     => $l_tenant['isys_mandator__db_host'],
            'db'       => $l_tenant['isys_mandator__db_name']
        ];

        $l_licence_serialized_info = utf8_decode($l_tenant['isys_licence__data']);
        $l_licence_info            = unserialize($l_licence_serialized_info);

        if (!$l_licence_info)
        {
            $l_licence_info = unserialize($l_tenant['isys_licence__data']);
        } // if

        if (isset($l_licence_info[9]) && is_array($l_licence_info[9]))
        {
            $l_module_licence = array_map('utf8_encode', $l_licence_info[9]);
        }
        else
        {
            $l_module_licence = [];
        } // if

        while (($l_file = readdir($l_dirhandle)) !== false)
        {
            if (is_dir($l_directory . $l_file) && strpos($l_file, '.') !== 0)
            {
                if (file_exists($l_directory . $l_file . '/package.json'))
                {
                    $l_package   = json_decode(file_get_contents($l_directory . $l_file . '/package.json'), true);
                    $l_module_id = $l_module_manager->is_installed($l_package['identifier']);

                    if ($l_package && $l_module_id)
                    {
                        $l_package['active'] = $l_module_manager->is_active($l_package['identifier']);
                        $l_package['id']     = $l_module_id;
                        $l_package['data']   = $l_module_manager->get_modules($l_package['id'])
                            ->get_row();
                        $l_package['update'] = ($l_package['type'] != 'core' && filemtime($l_directory . $l_file . '/package.json') > strtotime(
                                $l_package['data']['isys_module__date_install']
                            ));

                        if ($l_package['licence'])
                        {

                            if ($l_tenant['isys_licence__id'])
                            {
                                if ($l_package['licence'])
                                {
                                    $l_package['licenced'] = isset($l_module_licence[$l_package['identifier']]) ? true : false;
                                }
                            }
                            else
                            {
                                $l_package['licenced'] = false;
                            }

                        }
                        else $l_package['licenced'] = null;

                        $l_package['installed'] = true;

                        $l_modules[$i]['modules'][] = $l_package;
                    }
                    else
                    {
                        if ($l_package['identifier'] != 'pro')
                        {
                            $l_package['installed']     = false;
                            $l_modules[$i]['modules'][] = $l_package;
                        }
                    }

                }
            }
        }
        $i++;
        closedir($l_dirhandle);
        unset($l_dirhandle);
        $l_tenants_array[] = $l_tenant;
    }

    $g_comp_template->assign('modules', $l_modules);
    if (isset($l_tenants_array))
    {
        $g_comp_template->assign('mandators', $l_tenants_array);
    }

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