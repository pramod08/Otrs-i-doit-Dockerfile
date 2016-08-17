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
class isys_dashboard_widgets_reports extends isys_dashboard_widgets
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
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'report.tpl';
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
        global $g_comp_database_system;

        $l_reports = [];

        $l_report_res = isys_report_dao::instance($g_comp_database_system)
            ->get_reports(
                null,
                isys_auth_report::instance()
                    ->get_allowed_reports()
            );

        if (count($l_report_res) > 0)
        {
            while ($l_row = $l_report_res->get_row())
            {
                $l_reports[$l_row['isys_report_category__title']][$l_row['isys_report__id']] = $l_row['isys_report__title'];
            } // while
            $l_reports = array_map(
                function ($l_item)
                {
                    asort($l_item);

                    return $l_item;
                },
                $l_reports
            );
        } // if

        $l_rules = [
            'report_list'     => serialize($l_reports),
            'selected_report' => $this->m_config['report_id'],
            'count'           => $this->m_config['count'],
            'limit'           => (!isset($this->m_config['limit'])) ? 5000 : $this->m_config['limit']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', 'Reports')
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
        global $g_comp_database_system;

        /* @var  isys_report_dao $l_dao */
        $l_dao = isys_report_dao::instance($g_comp_database_system);

        try
        {
            $l_report = $l_dao->get_report($this->m_config['report_id']);

            if (empty($l_report))
            {
                throw new InvalidArgumentException(_L('LC__WIDGET__REPORT__NO_REPORT'));
            } // if

            $l_limit = 5000;

            if (isset($this->m_config['limit']))
            {
                if ($this->m_config['limit'] > 0)
                {
                    $l_limit = $this->m_config['limit'];
                } // if
            } // if
            $l_report['isys_report__query'] = rtrim(trim($l_report['isys_report__query']), ';') . ' LIMIT ' . $l_limit;
            $l_report_data                  = isys_factory::get_instance('isys_module_report_pro', [])
                ->process_show_report($l_report['isys_report__query'], null, true);

            $l_report_js = isys_module_report_pro::get_tpl_dir() . DS . 'report.js';

            $this->m_tpl->assign('items_per_page', $this->m_config['count'])
                ->assign('report_id', $l_report['isys_report__id'])
                ->assign('report_title', $l_report['isys_report__title'])
                ->assign('report_description', $l_report['isys_report__description'])
                ->assign('report_js', $l_report_js)
                ->assign('report_json', isys_format_json::encode($l_report_data));
        }
        catch (InvalidArgumentException $e)
        {
            // This should only happen, when no report is selected.
            $this->m_tpl->assign('friendly_error', true)
                ->assign('error_message', $e->getMessage());
        }
        catch (Exception $e)
        {
            $this->m_tpl->assign('error_message', $e->getMessage());
        } // try

        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->fetch($this->m_tpl_file);
    } // function
} // class