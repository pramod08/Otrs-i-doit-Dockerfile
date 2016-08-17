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
 * CMDB UI: Nagios Service Definition.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_nagios_service_dep extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories of application.
     *
     * @param   isys_cmdb_dao_category_g_nagios_service_dep $p_cat
     *
     * @author  Selcuk Kekec <skekec@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if ($_GET["service_hosts"])
        {
            $l_arr  = [];
            $l_data = $p_cat->get_assigned_hosts($_POST['service_id']);

            foreach ($l_data as $l_key => $l_val)
            {
                $l_arr[] = [
                    'id'  => $l_key,
                    'val' => isys_glob_utf8_encode($l_val)
                ];
            } // foreach

            header('Content-Type: application/json');
            echo isys_format_json::encode($l_arr);

            die;
        } // if

        $l_rules          = [];
        $l_catdata        = $p_cat->get_general_data();
        $l_comp_daoNagios = new isys_component_dao_nagios($this->m_database_component);

        $l_request = isys_request::factory()
            ->set_category_data_id($l_catdata['isys_catg_nagios_service_dep_list__id']);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Defining additional rules.
        $l_rules['C__CATG__NAGIOS_SERVICE_DEP__HOST']['p_arData']                = $p_cat->get_assigned_hosts($_GET[C__CMDB__GET__OBJECT]);
        $l_rules['C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD']['p_arData']          = serialize($l_comp_daoNagios->getTimeperiodsAssoc());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEP__INHERITS_PARENT']['p_arData']     = serialize(get_smarty_arr_YES_NO());
        $l_rules['C__CATG__NAGIOS_SERVICE_DEP__EXEC_FAIL_CRITERIA']['p_arData']  = serialize($p_cat->callback_property_execution_fail_criteria($l_request));
        $l_rules['C__CATG__NAGIOS_SERVICE_DEP__NOTIF_FAIL_CRITERIA']['p_arData'] = serialize($p_cat->callback_property_notification_fail_criteria($l_request));

        if ($l_catdata['isys_catg_nagios_service_dep_list__service_dep_connection'])
        {
            $l_rules['C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY']['p_arData'] = $p_cat->get_assigned_hosts(
                $l_catdata['isys_catg_nagios_service_dep_list__service_dep_connection']
            );
        } // if

        $l_local_host  = $l_rules['C__CATG__NAGIOS_SERVICE_DEP__HOST']['p_arData'][$l_rules['C__CATG__NAGIOS_SERVICE_DEP__HOST']['p_strSelectedID']];
        $l_dep_host    = $l_rules['C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY']['p_arData'][$l_rules['C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY']['p_strSelectedID']];
        $l_dep_service = $p_cat->get_obj_name_by_id_as_string($l_catdata['servicedep']);

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->assign(
                'doc_description',
                _L(
                    'LC__CATG__NAGIOS_SERVICE_DEP__DOC_DESCRIPTION',
                    [
                        $l_local_host,
                        $l_dep_service,
                        $l_dep_host
                    ]
                )
            )
            ->assign(
                "service_host_url",
                "?" . http_build_query($_GET, null, "&") . "&call=category&" . C__CMDB__GET__CATLEVEL . "=" . $l_catdata["isys_catg_application_list__id"] . '&service_hosts=1'
            );
    } // function
} // class