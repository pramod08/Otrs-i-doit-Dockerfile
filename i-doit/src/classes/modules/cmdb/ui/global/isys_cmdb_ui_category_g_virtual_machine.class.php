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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_virtual_machine extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_g_virtual_machine $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_comp_template;

        if ($_GET['cluster_options'])
        {
            $this->process_options($p_cat);
            die;
        } // if

        /**
         * @var $l_dao_vm_host isys_cmdb_dao_category_g_virtual_host
         */
        $l_dao_vm_host = isys_cmdb_dao_category_g_virtual_host::instance(isys_application::instance()->database);
        $l_catdata     = [];
        if ($_GET[C__CMDB__GET__OBJECT])
        {
            $l_catdata = $p_cat->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
                ->__to_array();
        } // if

        if (is_numeric($_POST['hidden_detach']) && $_POST['hidden_detach'] > 0)
        {
            $p_cat->detach_machine($_POST['hidden_detach']);
        } // if

        // Is this object a virtual machine?
        $l_vm = ($l_catdata['isys_catg_virtual_machine_list__vm']) ?: false;

        // Removed: isys_rs_system
        if (!$l_vm)
        {
            $l_vm = C__VM__NO;
            $g_comp_template->assign('editMode', 1);
        }
        else
        {
            $g_comp_template->assign('editMode', 0);
        }
        $g_comp_template->assign('vm', $l_vm);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW);
        $g_comp_template->assign('C__CATG__VM__HOST_SYSTEM', $p_cat->get_virtualization_system_as_string($l_catdata['isys_catg_virtual_machine_list__isys_obj__id']));

        // Setup ajax url.
        $g_comp_template->assign(
            'virtual_machine_ajax_url',
            '?' . http_build_query($_GET, null, '&') . '&call=category&cluster_options=1&' . C__CMDB__GET__CATLEVEL . '=' . $l_catdata['isys_catg_virtual_machine_list__id']
        );

        $l_rules['C__CATG__VM__OBJECT']['p_strSelectedID']                                                            = $l_catdata['vm_obj__object'];
        $l_rules['C__CATG__VM__CONFIG_FILE']['p_strValue']                                                            = $l_catdata['isys_catg_virtual_machine_list__config_file'];
        $l_rules['C__CATG__VM__SYSTEM']['p_strSelectedID']                                                            = $l_catdata['isys_catg_virtual_machine_list__isys_vm_type__id'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id(
        )]['p_strValue']                                                                                              = $l_catdata['isys_catg_virtual_machine_list__description'];

        // Set host
        if ($p_cat->is_cluster($l_catdata['vm_obj__object']))
        {
            $l_res             = $p_cat->get_cluster_members(null, $l_catdata['vm_obj__object'], '', null, C__RECORD_STATUS__NORMAL);
            $l_cluster_members = [];

            while ($l_row = $l_res->get_row())
            {
                if ($_GET[C__CMDB__GET__OBJECT] != $l_row['isys_obj__id'] && $l_row['isys_obj__id'] > 0)
                {
                    $l_cluster_members [$l_row ['isys_obj__id']] = $l_row ['isys_obj__title'];
                }
            }

            $l_rules['C__CMDB__CATG__VIRTUAL_MACHINE_HOST']['p_arData']        = serialize($l_cluster_members);
            $l_rules['C__CMDB__CATG__VIRTUAL_MACHINE_HOST']['p_strSelectedID'] = $l_catdata ['isys_catg_virtual_machine_list__primary'];

            if ($l_catdata['isys_catg_virtual_machine_list__primary'])
            {
                $l_adm_service_res = $l_dao_vm_host->get_data_by_property(
                    null,
                    $l_catdata['isys_catg_virtual_machine_list__primary'],
                    'administration_service'
                );
                if ($l_adm_service_res->num_rows())
                {
                    $l_rules['C__CATG__VIRTUAL_MACHINE__ADMINISTRATION_SERVICE']['p_strValue'] = $p_cat->get_obj_name_by_id_as_string(
                        $l_adm_service_res->get_row_value('administration_service')
                    );
                } // if
            } // if
        }
        else
        {
            $g_comp_template->assign('cluster_options_display', 'display: none;');
            $l_rules['C__CMDB__CATG__VIRTUAL_MACHINE_HOST']['p_arData']        = serialize([]);
            $l_rules['C__CMDB__CATG__VIRTUAL_MACHINE_HOST']['p_strSelectedID'] = null;

            if ($l_catdata['vm_obj__object'])
            {
                $l_adm_service_res = $l_dao_vm_host->get_data_by_property(
                    null,
                    $l_catdata['vm_obj__object'],
                    'administration_service'
                );
                if ($l_adm_service_res->num_rows())
                {
                    $l_adm_service                                                             = $l_adm_service_res->get_row_value('administration_service');
                    $l_rules['C__CATG__VIRTUAL_MACHINE__ADMINISTRATION_SERVICE']['p_strValue'] = $p_cat->get_obj_name_by_id_as_string($l_adm_service);
                } // if
            } // if
        } // if

        $g_comp_template->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
        $this->activate_commentary($p_cat);

        $index_includes['contentbottomcontent'] = $this->get_template();
    }

    /**
     * @deprecated This row modifier, used by get_guest_table, does not get called
     *
     * @param $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        if ($p_row['isys_cats_net_ip_addresses_list__title'] == '')
        {
            $p_row['isys_cats_net_ip_addresses_list__title'] = '-';
        }

        if ($p_row['isys_obj__isys_obj_type__id'] != C__OBJTYPE__OPERATING_SYSTEM)
        {
            $p_row['os_name'] = '-';
        }

        $l_gets = [
            C__CMDB__GET__OBJECT   => $p_row['isys_obj__id'],
            C__CMDB__GET__VIEWMODE => $_GET[C__CMDB__GET__VIEWMODE],
            C__CMDB__GET__TREEMODE => $_GET[C__CMDB__GET__TREEMODE],
            C__CMDB__GET__CATG     => C__CATG__VIRTUAL_MACHINE
        ];

        $p_row['isys_obj__title'] = '<a href="' . isys_helper_link::create_url($l_gets) . '">' . $p_row['isys_obj__title'] . '</a>';

        $p_row['detach'] = '<a href="javascript:" title="' . _L('LC__UNIVERSAL__REMOVE') . '"
			onClick="if (confirm(\'' . _L(
                'LC__CMDB__CATG__VM__REALLY_DETACH'
            ) . '\')) {document.isys_form.hidden_detach.value = \'' . $p_row['isys_catg_virtual_machine_list__id'] . '\'; document.isys_form.submit();}" >
			<img src="' . $g_dirs['images'] . 'icons/detach.gif" alt="" /></a>';
    }

    /**
     * This method does not get called anymore.
     *
     * @param   isys_component_dao_result $p_object_result
     *
     * @return  mixed
     */
    public function get_guest_table(isys_component_dao_result $p_object_result)
    {
        if ($p_object_result->num_rows() > 0)
        {
            $l_arTableHeader = [
                'isys_obj__title'                        => 'VM',
                'isys_obj_type__title'                   => _L('LC__CMDB__OBJTYPE'),
                'os_name'                                => _L('LC__OBJTYPE__OPERATING_SYSTEM'),
                'isys_cats_net_ip_addresses_list__title' => _L('LC__CATP__IP__ADDRESS'),
                'detach'                                 => ''
            ];

            $l_objList = new isys_component_list(null, $p_object_result);
            $l_objList->config($l_arTableHeader, false, '[{isys_catg_virtual_machine_list__id}]', false);
            $l_objList->createTempTable();
            $l_objList->set_row_modifier($this);

            return $l_objList->getTempTableHtml();
        }
        else
        {
            return false;
        }
    } // function

    /**
     * Handle ajax requests for the form that are used to dynamically
     * populate the host dropdown.
     *
     * @param DAO $p_cat
     */
    public function process_options($p_cat)
    {

        $l_json_response = [];
        if (!$p_cat->is_cluster($_POST['application_id']))
        {
            $l_json_response ['isCluster'] = false;
        }
        else
        {
            $l_json_response['isCluster'] = true;

            // Read list of cluster hosts.
            $l_res = $p_cat->get_cluster_members(null, $_POST['application_id'], '', null, C__RECORD_STATUS__NORMAL);

            while ($l_row = $l_res->get_row())
            {
                if ($_GET[C__CMDB__GET__OBJECT] != $l_row['isys_obj__id'] && $l_row['isys_obj__id'] > 0 && $l_row['isys_obj__status'] == C__RECORD_STATUS__NORMAL)
                {
                    $l_json_response [$l_row ['isys_obj__id']] = isys_glob_utf8_encode($l_row['isys_obj__title']);
                }
            }
        }

        echo isys_format_json::encode($l_json_response);
        die;
    }

    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template('catg__virtual_machine.tpl');
        parent::__construct($p_template);
    } // function
} // class
?>