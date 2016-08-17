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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 */
class isys_ajax_handler_auth extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'create_new_path_by_category':
                    $l_return['data'] = $this->create_new_path_by_category($_POST['person_id'], $_POST['module_id'], $_POST['method'], $_POST['parameter'], $_POST['rights']);
                    break;

                case 'load_all_module_paths':
                    $l_return['data'] = $this->load_all_module_paths($_POST['module_id']);
                    break;

                case 'load_all_object_paths':
                    $l_return['data'] = $this->load_all_object_paths($_POST['obj_id']);
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Method for saving a new auth-path out of the "auth-category".
     *
     * @param   integer $p_person_id
     * @param   integer $p_module_id
     * @param   string  $p_method
     * @param   string  $p_parameter
     * @param   string  $p_rights May contain several rights, divided by ";".
     *
     * @throws  isys_exception_general
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function create_new_path_by_category($p_person_id, $p_module_id, $p_method, $p_parameter, $p_rights)
    {
        if (empty($p_person_id) || empty($p_module_id) || empty($p_method))
        {
            throw new isys_exception_general(_L('LC__CMDB__CATG__AUTH_EXCEPTION_MISSING_PARAMETERS'));
        } // if

        $l_rights = explode(';', $p_rights);

        if (in_array(isys_auth::SUPERVISOR, $l_rights))
        {
            // If the supervisor was selected, no other rights have to be assigned.
            $l_rights = [isys_auth::SUPERVISOR];
        } // if

        // Prepare the array syntax for isys_auth_dao->create_paths().
        $l_path_data = [$p_method => [$p_parameter => $l_rights]];

        isys_auth_dao::instance($this->m_database_component)
            ->create_paths($p_person_id, $p_module_id, $l_path_data);

        $l_object_paths = isys_cmdb_dao_category_g_virtual_auth::instance($this->m_database_component)
            ->get_object_paths($p_parameter);

        try
        {
            array_map(
                function (isys_caching $l_cache)
                {
                    $l_cache->clear();
                },
                isys_caching::find('auth-*')
            );
        }
        catch (Exception $e)
        {
            isys_notify::warning('Could not clear cache files for /temp/auth-* with message: ' . $e->getMessage());
        } // try

        // Return the new paths for the given object.
        return isys_auth_dao::instance($this->m_database_component)
            ->build_paths_by_array($l_object_paths);
    } // function

    /**
     * Method for loading all paths by a given module.
     *
     * @param   integer $p_module_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_all_module_paths($p_module_id)
    {
        $l_paths = [];

        $l_dao      = isys_auth_dao::instance($this->m_database_component);
        $l_cmdb_dao = isys_cmdb_dao::instance($this->m_database_component);

        $l_res = $l_dao->get_paths(null, $p_module_id);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                // This needs to be done, for the reference to work.
                if ($l_paths[$l_row['isys_auth__isys_obj__id']] === null)
                {
                    if ($l_row['isys_auth__isys_obj__id'] > 0)
                    {
                        $l_person = $l_cmdb_dao->get_object_by_id($l_row['isys_auth__isys_obj__id'])
                            ->get_row();

                        $l_paths[$l_row['isys_auth__isys_obj__id']] = [
                            'paths'  => [],
                            'person' => _L($l_person['isys_obj_type__title']) . ' &raquo; ' . $l_person['isys_obj__title']
                        ];
                    }
                } // if

                $l_dao->build_path($l_paths[$l_row['isys_auth__isys_obj__id']]['paths'], $l_row);
            } // while
        } // if

        $l_methods       = [];
        $l_auth_instance = isys_module_manager::instance()
            ->get_module_auth($p_module_id);

        if ($l_auth_instance)
        {
            $l_methods = $l_auth_instance->get_auth_methods();
        } // if

        // Retrieve the rights and make sure, the titles are UTF8.
        $l_rights = isys_auth::get_rights();

        return [
            'method'       => 'module-id',
            'auth_rights'  => $l_rights,
            'auth_methods' => $l_methods,
            'auth_paths'   => $l_paths
        ];
    } // function

    /**
     * Method for loading all paths by a given object (person / persongroup).
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_all_object_paths($p_obj_id)
    {
        $l_paths = [];

        $l_dao = isys_auth_dao::instance($this->m_database_component);

        $l_res = $l_dao->get_paths($p_obj_id);

        if (is_object($l_res) && $l_res->num_rows() > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                // This needs to be done, for the reference to work.
                if ($l_paths[$l_row['isys_auth__isys_module__id']] === null)
                {
                    // Loading the module data.
                    $l_module                       = isys_module_manager::instance()
                        ->get_modules($l_row['isys_auth__isys_module__id'])
                        ->get_row();
                    $l_module['isys_module__title'] = _L($l_module['isys_module__title']);

                    // Prepare the module specific methods.
                    $l_methods       = [];
                    $l_auth_instance = isys_module_manager::instance()
                        ->get_module_auth($l_row['isys_auth__isys_module__id']);

                    if ($l_auth_instance)
                    {
                        $l_methods = $l_auth_instance->get_auth_methods();
                    } // if

                    foreach ($l_methods as &$l_method)
                    {
                        $l_method['title'] = $l_method['title'];
                    } // foreach

                    $l_paths[$l_row['isys_auth__isys_module__id']] = [
                        'paths'       => [],
                        'group_paths' => [],
                        'info'        => [
                            'data'    => $l_module,
                            'methods' => $l_methods
                        ]
                    ];
                } // if

                // Add the user specific paths.
                $l_dao->build_path($l_paths[$l_row['isys_auth__isys_module__id']]['paths'], $l_row);
            } // while
        } // if

        // Check, if the given obj-id is a person, so we can load the inherited rights.
        $l_obj_type = isys_cmdb_dao::instance($this->m_database_component)
            ->get_objTypeID($p_obj_id);

        if ($l_obj_type != C__OBJTYPE__PERSON_GROUP)
        {
            $l_res = $l_dao->get_group_paths_by_person($p_obj_id);

            if (is_object($l_res) && $l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    // This needs to be done, for the reference to work.
                    if ($l_paths[$l_row['isys_auth__isys_module__id']] === null)
                    {
                        // Loading the module data.
                        $l_module                       = isys_module_manager::instance()
                            ->get_modules($l_row['isys_auth__isys_module__id'])
                            ->get_row();
                        $l_module['isys_module__title'] = _L($l_module['isys_module__title']);

                        // Prepare the module specific methods.
                        $l_methods       = [];
                        $l_auth_instance = isys_module_manager::instance()
                            ->get_module_auth($l_row['isys_auth__isys_module__id']);

                        if ($l_auth_instance)
                        {
                            $l_methods = $l_auth_instance->get_auth_methods();
                        } // if

                        foreach ($l_methods as &$l_method)
                        {
                            $l_method['title'] = $l_method['title'];
                        } // foreach

                        $l_paths[$l_row['isys_auth__isys_module__id']] = [
                            'paths'       => [],
                            'group_paths' => [],
                            'info'        => [
                                'data'    => $l_module,
                                'methods' => $l_methods
                            ]
                        ];
                    } // if

                    // Add the user specific paths.
                    $l_dao->build_path($l_paths[$l_row['isys_auth__isys_module__id']]['group_paths'], $l_row);
                } // while
            }
        } // if

        // Retrieve the rights and make sure, the titles are UTF8.
        $l_rights = isys_auth::get_rights();

        foreach ($l_rights as &$l_right)
        {
            $l_right['title'] = $l_right['title'];
        } // foreach

        return [
            'method'      => 'obj-id',
            'auth_rights' => $l_rights,
            'modules'     => $l_paths
        ];
    } // function
} // class