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
 * Maintenance category UI.
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */
class isys_cmdb_ui_category_g_virtual_maintenance extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_virtual_maintenance $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_loc;

        isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons();

        $l_filter_asc      = ($_GET['filter'] == 'ASC');
        $l_maintenance_res = $p_cat->get_plannings_by_object($_GET[C__CMDB__GET__OBJECT], $l_filter_asc);
        $l_maintenances    = [];

        if (count($l_maintenance_res))
        {
            while ($l_maintenance_row = $l_maintenance_res->get_row())
            {
                $l_item = [
                    'from'               => $g_loc->fmt_date($l_maintenance_row['isys_maintenance__date_from']),
                    'to'                 => $g_loc->fmt_date($l_maintenance_row['isys_maintenance__date_to']),
                    'finished'           => !!$l_maintenance_row['isys_maintenance__finished'],
                    'finished_datetime'  => $g_loc->fmt_datetime($l_maintenance_row['isys_maintenance__finished']),
                    'mail_sent'          => !!$l_maintenance_row['isys_maintenance__mail_dispatched'],
                    'mail_sent_datetime' => $g_loc->fmt_datetime($l_maintenance_row['isys_maintenance__mail_dispatched']),
                    'url'                => isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                            C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 2,
                            C__GET__SETTINGS_PAGE => C__MAINTENANCE__PLANNING,
                            C__GET__ID            => $l_maintenance_row['isys_maintenance__id']
                        ]
                    )
                ];

                if ($l_item['finished'])
                {
                    $l_item['url'] = isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID     => C__MODULE__MAINTENANCE,
                            C__GET__TREE_NODE     => C__MODULE__MAINTENANCE . 3,
                            C__GET__SETTINGS_PAGE => C__MAINTENANCE__PLANNING_ARCHIVE,
                            C__GET__ID            => $l_maintenance_row['isys_maintenance__id']
                        ]
                    );
                } // if

                $l_maintenances[] = $l_item;
            } // while
        } // if

        $l_filter_url = [
            C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT],
            C__CMDB__GET__CATG   => C__CATG__VIRTUAL_MAINTENANCE,
            'filter'             => ($l_filter_asc ? 'DESC' : 'ASC')
        ];

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('filter_asc', $l_filter_asc)
            ->assign('filter_url', isys_helper_link::create_url($l_filter_url))
            ->assign('maintenances', $l_maintenances);

        $index_includes['contentbottomcontent'] = isys_module_maintenance::get_tpl_dir() . 'cmdb/catg__virtual_maintenance.tpl';
    } // function
} // class