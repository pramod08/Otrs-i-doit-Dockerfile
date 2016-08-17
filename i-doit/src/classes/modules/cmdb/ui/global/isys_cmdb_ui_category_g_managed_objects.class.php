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
 * UI: global category for managed objects view
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_managed_objects extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category_g_managed_objects_view $p_cat
     *
     * @return  void
     * @throws  isys_exception_cmdb
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION      => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT         => true,
                isys_popup_browser_object_ng::C__CAT_FILTER          => 'C__CATG__VIRTUAL_HOST_ROOT',
                isys_popup_browser_object_ng::C__RETURN_ELEMENT      => C__POST__POPUP_RECEIVER,
                isys_popup_browser_object_ng::C__OBJECT_BROWSER__TAB => [
                    isys_popup_browser_object_ng::C__OBJECT_BROWSER__TAB__LOCATION => false
                ],
                isys_popup_browser_object_ng::C__DATARETRIEVAL       => [
                    [
                        get_class($p_cat),
                        "get_assigned_objects"
                    ],
                    $_GET[C__CMDB__GET__OBJECT],
                    [
                        "isys_obj__id",
                        "isys_obj__title",
                        "isys_obj__isys_obj_type__id",
                        "isys_obj__sysid"
                    ]
                ]
            ],
            "LC__CATG__OBJECT__ADD",
            "LC__CATG__CONTACT_LIST__NAVBAR_ADD"
        );

        $l_cluster_devices_res  = $p_cat->get_all_assigned_clusters($_GET[C__CMDB__GET__OBJECT]);
        $l_physical_devices_res = $p_cat->get_all_physical_devices($_GET[C__CMDB__GET__OBJECT]);
        $l_virtual_devices_res  = $p_cat->get_all_virtual_machines($_GET[C__CMDB__GET__OBJECT]);

        // Build array for cluster devices
        $l_cluster_devices = $this->get_device_data($l_cluster_devices_res);

        /**
         * @var $l_dao_cluster_mem isys_cmdb_dao_category_g_cluster_members
         */
        $l_dao_cluster_mem = isys_cmdb_dao_category_g_cluster_members::instance(isys_application::instance()->database);
        $l_cluster_members = [];

        if (count($l_cluster_devices))
        {
            $l_cluster_arr     = array_keys($l_cluster_devices);
            $l_cluster_members = [];
            foreach ($l_cluster_arr AS $l_cluster_id)
            {
                $l_cluster_members[$l_cluster_id] = $l_dao_cluster_mem->get_assigned_members_as_array($l_cluster_id);
            } // foreach
        } // if

        // Build array for physical devices
        $l_physical_devices = $this->get_device_data($l_physical_devices_res, $l_cluster_members);

        /**
         * @var $l_dao_gs isys_cmdb_dao_category_g_guest_systems
         */
        $l_dao_gs              = isys_cmdb_dao_category_g_guest_systems::instance(isys_application::instance()->database);
        $l_cluster_members_vms = [];

        if (count($l_cluster_members))
        {
            foreach ($l_cluster_members AS $l_members)
            {
                if ($l_members)
                {
                    foreach ($l_members AS $l_host_id)
                    {
                        $l_res = $l_dao_gs->get_data(null, $l_host_id);
                        if ($l_res->num_rows())
                        {
                            while ($l_row = $l_res->get_row())
                            {
                                $l_cluster_members_vms[$l_host_id][] = $l_row['isys_catg_virtual_machine_list__isys_obj__id'];
                            } // while
                        } // if
                    } // foreach
                } // if
            } // foreach
        } // if

        // Build array for virtual computers
        $l_virtual_devices = $this->get_device_data($l_virtual_devices_res, $l_cluster_members_vms);

        $this->get_template_component()
            ->assign('cluster_objects', $l_cluster_devices)
            ->assign('physical_objects', $l_physical_devices)
            ->assign('virtual_computers', $l_virtual_devices);

        $index_includes["contentbottomcontent"] = 'content/bottom/content/catg__managed_objects.tpl';
        $this->deactivate_commentary();
    } // function

    public function process_list(isys_cmdb_dao_category_g_managed_objects $p_cat)
    {
        $this->process($p_cat);
    } // function

    /**
     * @param isys_component_dao_result $p_res
     * @param array                     $p_additional_devices
     *
     * @return array
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_device_data($p_res, &$p_additional_devices = [])
    {
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao_ip    = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->database);
        $l_return    = [];

        if (is_object($p_res))
        {
            /**
             * @var $l_dao_ip    isys_cmdb_dao_category_g_ip
             * @var $l_dao_model isys_cmdb_dao_category_g_model
             */
            $l_return = [];
            while ($l_row = $p_res->get_row())
            {
                $l_os_query                       = 'SELECT isys_connection__isys_obj__id
				FROM  `isys_catg_application_list`
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
				WHERE  `isys_catg_application_list__isys_obj__id` = ' . $l_dao_ip->convert_sql_id($l_row['isys_obj__id']) . '
				AND  `isys_catg_application_list__isys_catg_application_type__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM) . '
				AND  `isys_catg_application_list__isys_catg_application_priority__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_PRIORITY__PRIMARY);
                $l_return[$l_row['isys_obj__id']] = [
                    'link'       => $l_quickinfo->get_quick_info(
                        $l_row['isys_obj__id'],
                        $l_row['isys_obj__title'],
                        C__LINK__OBJECT
                    ),
                    'type'       => _L($l_dao_ip->get_objtype_name_by_id_as_string($l_row['isys_obj__isys_obj_type__id'])),
                    'primary_ip' => $l_dao_ip->get_primary_ip($l_row['isys_obj__id'])
                        ->get_row_value('isys_cats_net_ip_addresses_list__title'),
                    'serial'     => $l_dao_ip->retrieve(
                        'SELECT isys_catg_model_list__serial FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $l_dao_ip->convert_sql_id(
                            $l_row['isys_obj__id']
                        )
                    )
                        ->get_row_value('isys_catg_model_list__serial'),
                    'os'         => $l_dao_ip->get_obj_name_by_id_as_string(
                        $l_dao_ip->retrieve($l_os_query)
                            ->get_row_value('isys_connection__isys_obj__id')
                    )
                ];
                if (isset($l_row['isys_catg_virtual_machine_list__id']))
                {
                    $l_return[$l_row['isys_obj__id']]['parent'] = $l_dao_ip->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id']);
                } // if
            } // while
        } // if

        if (count($p_additional_devices) > 0)
        {
            $l_res_objtypes        = $l_dao_ip->get_obj_type_by_catg([C__CATG__VIRTUAL_MACHINE__ROOT]);
            $l_allowed_objecttypes = [];
            while ($l_row = $l_res_objtypes->get_row())
            {
                $l_allowed_objecttypes[$l_row['isys_obj_type__id']] = true;
            } // while

            foreach ($p_additional_devices AS $l_parent_id => $l_members)
            {
                if ($l_members)
                {
                    foreach ($l_members AS $l_key => $l_id)
                    {
                        if (!isset($l_return[$l_id]))
                        {
                            $l_objtype_id = $l_dao_ip->get_objTypeID($l_id);
                            if (isset($l_allowed_objecttypes[$l_objtype_id]))
                            {
                                $l_os_query      = 'SELECT isys_connection__isys_obj__id
							FROM  `isys_catg_application_list` INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
							WHERE  `isys_catg_application_list__isys_obj__id` = ' . $l_dao_ip->convert_sql_id($l_id) . '
							AND  `isys_catg_application_list__isys_catg_application_type__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM) . '
							AND  `isys_catg_application_list__isys_catg_application_priority__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_PRIORITY__PRIMARY);
                                $l_return[$l_id] = [
                                    'link'       => $l_quickinfo->get_quick_info(
                                        $l_id,
                                        $l_dao_ip->get_obj_name_by_id_as_string($l_id),
                                        C__LINK__OBJECT
                                    ),
                                    'type'       => _L($l_dao_ip->get_objtype_name_by_id_as_string($l_objtype_id)),
                                    'primary_ip' => $l_dao_ip->get_primary_ip($l_id)
                                        ->get_row_value('isys_cats_net_ip_addresses_list__title'),
                                    'serial'     => $l_dao_ip->retrieve(
                                        'SELECT isys_catg_model_list__serial FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $l_dao_ip->convert_sql_id(
                                            $l_id
                                        )
                                    )
                                        ->get_row_value('isys_catg_model_list__serial'),
                                    'os'         => $l_dao_ip->get_obj_name_by_id_as_string(
                                        $l_dao_ip->retrieve($l_os_query)
                                            ->get_row_value('isys_connection__isys_obj__id')
                                    ),
                                    'parent'     => $l_dao_ip->get_obj_name_by_id_as_string($l_parent_id)
                                ];
                            }
                            else
                            {
                                unset($p_additional_devices[$l_parent_id][$l_key]);
                            } // if
                        }
                        else
                        {
                            unset($p_additional_devices[$l_key]);
                        } // if
                    }
                }
            }
        }

        return $l_return;
    } // function
} // class