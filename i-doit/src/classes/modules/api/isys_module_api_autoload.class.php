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
 * Class autoloader.
 *
 * @package     API
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_api_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $p_classname
     *
     * @return  boolean
     */
    public static function init($p_classname)
    {
        $l_classlist = [
            'isys_api'                                   => '/src/classes/modules/api/isys_api.class.php',
            'isys_api_controller'                        => '/src/classes/modules/api/controller/isys_api_controller.class.php',
            'isys_api_controller_jsonrpc'                => '/src/classes/modules/api/controller/isys_api_controller_jsonrpc.class.php',
            'isys_api_model_cmdb_categories'             => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_categories.class.php',
            'isys_api_model_cmdb_category'               => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_category.class.php',
            'isys_api_model_cmdb_category_info'          => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_category_info.class.php',
            'isys_api_model_cmdb_contact'                => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_contact.class.php',
            'isys_api_model_cmdb_dialog'                 => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_dialog.class.php',
            'isys_api_model_cmdb_filter'                 => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_filter.class.php',
            'isys_api_model_cmdb_impact'                 => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_impact.class.php',
            'isys_api_model_cmdb_location_tree'          => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_location_tree.class.php',
            'isys_api_model_cmdb_logbook'                => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_logbook.class.php',
            'isys_api_model_cmdb_object'                 => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_object.class.php',
            'isys_api_model_cmdb_object_type_categories' => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_object_type_categories.class.php',
            'isys_api_model_cmdb_object_type_groups'     => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_object_type_groups.class.php',
            'isys_api_model_cmdb_object_types'           => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_object_types.class.php',
            'isys_api_model_cmdb_objects'                => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_objects.class.php',
            'isys_api_model_cmdb_objects_by_relation'    => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_objects_by_relation.class.php',
            'isys_api_model_cmdb_reports'                => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_reports.class.php',
            'isys_api_model_cmdb_workflow'               => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_workflow.class.php',
            'isys_api_model_cmdb_workstation_components' => '/src/classes/modules/api/model/cmdb/isys_api_model_cmdb_workstation_components.class.php',
            'isys_api_model_idoit_constants'             => '/src/classes/modules/api/model/idoit/isys_api_model_idoit_constants.class.php',
            'isys_api_model_idoit_login'                 => '/src/classes/modules/api/model/idoit/isys_api_model_idoit_login.class.php',
            'isys_api_model_idoit_logout'                => '/src/classes/modules/api/model/idoit/isys_api_model_idoit_logout.class.php',
            'isys_api_model_idoit_version'               => '/src/classes/modules/api/model/idoit/isys_api_model_idoit_version.class.php',
            'isys_api_model_idoit_search'                => '/src/classes/modules/api/model/idoit/isys_api_model_idoit_search.class.php',
            'isys_api_model'                             => '/src/classes/modules/api/model/isys_api_model.class.php',
            'isys_api_model_interface'                   => '/src/classes/modules/api/model/isys_api_model_interface.class.php',
            'isys_api_model_cmdb'                        => '/src/classes/modules/api/model/isys_api_model_cmdb.class.php',
            'isys_api_model_idoit'                       => '/src/classes/modules/api/model/isys_api_model_idoit.class.php',
            'isys_api_view'                              => '/src/classes/modules/api/view/isys_api_view.class.php',
            'isys_api_view_json'                         => '/src/classes/modules/api/view/isys_api_view_json.class.php'
        ];

        if (isset($l_classlist[$p_classname]))
        {
            if (parent::include_file($l_classlist[$p_classname]))
            {
                return true;
            } // if
        } // if

        return false;
    } // function
} // class