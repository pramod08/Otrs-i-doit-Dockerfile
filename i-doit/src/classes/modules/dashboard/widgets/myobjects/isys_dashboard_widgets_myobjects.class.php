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
class isys_dashboard_widgets_myobjects extends isys_dashboard_widgets
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
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'myobjects.tpl';
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
        $l_rules = [
            'count' => $this->m_config['objects'],
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__MYOBJECTS__CONFIG'))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Abstract render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        global $g_comp_database, $g_comp_session;

        try
        {
            $l_locales = isys_locale::get_instance();
        }
        catch (Exception $e)
        {
            $l_locales = isys_locale::get($g_comp_database, $g_comp_session->get_user_id());
        } // try

        $l_quicky                  = new isys_ajax_handler_quick_info();
        $this->m_config['objects'] = (int) $this->m_config['objects'];

        $l_dao = isys_cmdb_dao::instance($g_comp_database);

        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj__updated, isys_obj__created, isys_cmdb_status__color, isys_cmdb_status__title
			FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_obj__isys_cmdb_status__id = isys_cmdb_status__id
			WHERE isys_obj__updated_by = ' . $l_dao->convert_sql_text($g_comp_session->get_current_username()) . '
			AND isys_obj__title != ""
			AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			ORDER BY isys_obj__updated DESC LIMIT ' . $l_dao->convert_sql_int($this->m_config['objects']) . ';';

        $l_data = [];
        $l_res  = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_data[] = [
                    'cmdb_color' => '#' . $l_row['isys_cmdb_status__color'],
                    'cmdb_title' => _L($l_row['isys_cmdb_status__title']),
                    'title'      => $l_row['isys_obj__title'],
                    'title_link' => $l_quicky->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT),
                    'created'    => $l_locales->fmt_date($l_row['isys_obj__created']),
                    'updated'    => $l_locales->fmt_date($l_row['isys_obj__updated'])
                ];
            } // while
        } // if

        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->assign('tabledata', $l_data)
            ->fetch($this->m_tpl_file);
    } // function
} // class