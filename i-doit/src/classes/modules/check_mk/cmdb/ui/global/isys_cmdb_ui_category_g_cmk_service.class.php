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
class isys_cmdb_ui_category_g_cmk_service extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the category check_mk
     *
     * @param   isys_cmdb_dao_category_g_cmk_service $p_cat The corresponding category DAO
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_error = false;
        $l_rules = $l_services = $l_livestatus_result = [];

        try
        {
            $l_livestatus_result = isys_monitoring_livestatus::factory($l_catdata['isys_catg_cmk_service_list__host'])
                ->query(
                    [
                        'GET services',
                        'Columns: description'
                    ]
                );

            // Preparing the service data.
            if (is_array($l_livestatus_result) && count($l_livestatus_result) > 0)
            {
                foreach ($l_livestatus_result as $l_service)
                {
                    $l_services[$l_service[0]] = $l_service[0];
                } // foreach
            } // if
        }
        catch (Exception $e)
        {
            $l_error = $e->getMessage();
        } // try

        $l_rules['C__CATG__CMK_SERVICE__CHECK_MK_SERVICES']['p_strSelectedID'] = $l_catdata['isys_catg_cmk_service_list__service'];
        $l_rules['C__CATG__CMK_SERVICE__CHECK_MK_SERVICES']['p_arData']        = serialize($l_services);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->get_template_component()
            ->assign('error', $l_error)
            ->assign('default_error', _L('LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__NO_CONFIG'))
            ->assign(
                'ajax_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX      => 1,
                        C__GET__AJAX_CALL => 'check_mk',
                        'func'            => 'query_livestatus'
                    ]
                )
            )
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = isys_module_check_mk::get_tpl_dir() . 'modules' . DS . 'cmdb' . DS . 'catg__cmk_service.tpl';
    } // function
} // class