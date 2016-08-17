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
 * UI: global category for Check_MK.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_ui_category_g_cmk_host_service extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the category check_mk
     *
     * @param   isys_cmdb_dao_category_g_cmk_host_service $p_cat The corresponding category DAO
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_rules  = $l_softwares = $l_services = $l_livestatus_result = [];

        try
        {
            $l_host = isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
                ->get_data(null, $l_obj_id)
                ->get_row_value('isys_catg_monitoring_list__isys_monitoring_hosts__id');

            $l_livestatus_result = isys_monitoring_livestatus::factory($l_host)
                ->query(
                    [
                        'GET hosts',
                        'Filter: host_name = ' . isys_monitoring_helper::render_export_hostname($l_obj_id),
                        'Columns: services_with_info'
                    ]
                );

            // Preparing the service data.
            if (isset($l_livestatus_result[0]) && isset($l_livestatus_result[0][0]) && count($l_livestatus_result[0][0]) > 0)
            {
                foreach ($l_livestatus_result[0][0] as $l_service)
                {
                    $l_services[$l_service[0]] = $l_service[0];
                } // foreach

                // Assign the services to the template for a raw output.
                $this->get_template_component()
                    ->assign('services', $l_livestatus_result[0][0]);
            } // if
        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage(), ['sticky' => true]);
        } // try

        $l_res = isys_cmdb_dao_category_g_application::instance($this->get_database_component())
            ->get_data(null, $l_obj_id, null, null, C__RECORD_STATUS__NORMAL);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_softwares[$l_row['isys_catg_application_list__id']] = _L($l_row['isys_obj_type__title']) . ' >> ' . $l_row['isys_obj__title'];
            } // while
        } // if

        $l_rules['C__CATG__CMK_SERVICE__CHECK_MK_SERVICES']['p_strSelectedID'] = $l_catdata['isys_catg_cmk_host_service_list__service'];
        $l_rules['C__CATG__CMK_SERVICE__CHECK_MK_SERVICES']['p_arData']        = serialize($l_services);
        $l_rules['C__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT']['p_arData']      = serialize($l_softwares);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->get_template_component()
            ->assign('states', isys_check_mk_helper::get_state_info())
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = isys_module_check_mk::get_tpl_dir() . 'modules' . DS . 'cmdb' . DS . 'catg__cmk_host_service.tpl';
    } // function

    /**
     * Processes category data list for multi-valued categories.
     *
     * @param   isys_cmdb_dao_category $p_cat                Category's DAO
     * @param   array                  $p_get_param_override (optional)
     * @param   string                 $p_strVarName         (optional)
     * @param   string                 $p_strTemplateName    (optional)
     * @param   boolean                $p_bCheckbox          (optional)
     * @param   boolean                $p_bOrderLink         (optional)
     * @param   string                 $p_db_field_name      (optional)
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_inherited_services = [];

        // At first we retrieve all assigned software objects.
        $l_app_dao         = isys_cmdb_dao_category_g_application::instance($this->m_database_component);
        $l_app_service_dao = isys_cmdb_dao_category_g_cmk_service::instance($this->m_database_component);
        $l_res             = $l_app_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, $_SESSION["cRecStatusListView"]);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                // Now we fetch all assigned services and add them to our array.
                $l_service_res = $l_app_service_dao->get_data(null, $l_row['isys_obj__id']);

                if (count($l_service_res) > 0)
                {
                    while ($l_service_row = $l_service_res->get_row())
                    {
                        $l_inherited_services[] = [
                            'application' => _L($l_row['isys_obj_type__title']) . ' >> ' . $l_row['isys_obj__title'],
                            'service'     => $l_service_row['isys_catg_cmk_service_list__service']
                        ];
                    } // while
                } // if
            } // while
        } // if

        $l_table_content = $this->get_template_component()
            ->assign('inherited_services', $l_inherited_services)
            ->fetch(isys_module_check_mk::get_tpl_dir() . 'modules' . DS . 'cmdb' . DS . 'catg__cmk_host_service_table.tpl');

        $this->get_template_component()
            ->assign('additional_object_table_data', $l_table_content);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    } // function
} // class