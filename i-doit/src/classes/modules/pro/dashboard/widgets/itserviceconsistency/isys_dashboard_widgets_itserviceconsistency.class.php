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
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_itserviceconsistency extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $m_config_tpl_file = '';
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return true;
    } // function

    /**
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'it_service_consistency.tpl';
        $this->m_config_tpl_file = __DIR__ . DS . 'templates' . DS . 'config.tpl';

        return parent::init($p_config);
    } // function

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_objectypes = [];
        $l_res        = isys_cmdb_dao::instance(isys_application::instance()->database)
            ->get_obj_type_by_catg([C__CATG__SERVICE]);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_objectypes[] = $l_row['isys_obj_type__const'];
            } // while
        } // if

        $l_rules = [
            'show_all'                       => $this->m_config['show_all'] ?: 0,
            'service_selection'              => isys_format_json::decode($this->m_config['service_selection']) ?: [],
            'service_selection_object_types' => $l_objectypes
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__IT_SERVICE_CONSISTENCY__CONFIG'))
            ->assign('dialog_show_all', serialize(get_smarty_arr_YES_NO()))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        global $g_comp_database, $g_dirs;

        $l_services = $l_objectypes = [];

        $l_quicky            = new isys_ajax_handler_quick_info;
        $l_itservice_dao     = isys_cmdb_dao_category_g_itservice::instance($g_comp_database);
        $l_cmdb_status_dao   = isys_cmdb_dao_status::instance($g_comp_database);
        $l_selected_services = isys_format_json::decode($this->m_config['service_selection']);

        if (is_array($l_selected_services) && count($l_selected_services))
        {
            $l_res = isys_cmdb_dao::instance(isys_application::instance()->database)
                ->get_objects(['ids' => $l_selected_services]);
        }
        else
        {
            $l_selected_services = false;
            $l_res               = [];
            //	$l_res = isys_cmdb_dao::instance(isys_application::instance()->database)->get_obj_type_by_catg([C__CATG__SERVICE]);
            //
            //	if (count($l_res))
            //	{
            //		while ($l_row = $l_res->get_row())
            //		{
            //			$l_objectypes[] = $l_row['isys_obj_type__id'];
            //		} // while
            //	} // if
            //
            //	$l_res = $l_itservice_dao->get_objects_by_type($l_objectypes);
        } // if

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_itservice_dao->get_its_relations($l_row['isys_obj__id']);

                $l_inconsistency_data = [];
                $l_inconsistencies    = $l_itservice_dao->get_inconsistence($l_row['isys_obj__id']);

                if (count($l_inconsistencies) > 0)
                {
                    foreach ($l_inconsistencies as $l_obj_id => $l_cmdb_status)
                    {
                        $l_inconsistency_data[] = [
                            'id'     => $l_obj_id,
                            'name'   => $l_quicky->get_quick_info(
                                $l_obj_id,
                                $l_itservice_dao->get_obj_name_by_id_as_string($l_obj_id) . ' (' . _L($l_itservice_dao->get_obj_type_name_by_obj_id($l_obj_id)) . ')',
                                C__LINK__OBJECT
                            ),
                            'status' => $l_cmdb_status_dao->get_cmdb_status($l_cmdb_status)
                                ->get_row()
                        ];
                    } // foreach
                } // if

                if ($this->m_config['show_all'] || count($l_inconsistency_data) > 0)
                {
                    $l_services[] = [
                        'id'              => $l_row['isys_obj__id'],
                        'name'            => $l_row['isys_obj__title'],
                        'link'            => $l_quicky->get_quick_info($l_row['isys_obj__id'], '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" />', C__LINK__OBJECT),
                        'inconsistencies' => $l_inconsistency_data
                    ];
                } // if
            } // while
        } // if

        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->assign('services', $l_services)
            ->assign('selected_services', $l_selected_services)
            ->fetch($this->m_tpl_file);
    } // function
} // class