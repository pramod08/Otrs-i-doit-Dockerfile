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
class isys_dashboard_widgets_statstable extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Init method.
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . DS . 'templates' . DS . 'statstable.tpl';

        return parent::init();
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
        global $g_comp_database, $g_comp_session;

        $l_sessions      = [];
        $l_dao           = isys_cmdb_dao::instance($g_comp_database);
        $l_dashboard_dao = isys_dashboard_dao::instance($g_comp_database);

        $l_sql = "SELECT * FROM isys_user_session
			INNER JOIN isys_cats_person_list ON isys_user_session__isys_obj__id = isys_cats_person_list__isys_obj__id
			WHERE isys_user_session__time_last_action IN (SELECT MAX(isys_user_session__time_last_action) FROM isys_user_session GROUP BY isys_user_session__isys_obj__id)
			AND ((UNIX_TIMESTAMP(isys_user_session__time_last_action) + 600)-UNIX_TIMESTAMP(NOW())) > 0
			AND isys_user_session__description NOT LIKE '%logout=1%' AND isys_user_session__description NOT LIKE '%api=jsonrpc%'
			GROUP BY isys_user_session__isys_obj__id;";

        $l_res = $l_dao->retrieve($l_sql);
        if (count($l_res) > 0)
        {
            while ($l_data = $l_res->get_row())
            {
                $l_sessions[] = $l_data["isys_cats_person_list__title"];
            } // while
        } // if

        $l_data = [
            'LC__WIDGET__STATSTABLE__TOTAL_COUNT_OBJECTS'      => $l_total_objects = $l_dao->count_objects(),
            'LC__WIDGET__STATSTABLE__TOTAL_COUNT_OBJECT_TYPES' => $l_total_object_types = $l_dao->count_object_types(),
            'LC__WIDGET__STATSTABLE__OBJECTS_IN_TYPES'         => round($l_total_objects / $l_total_object_types, 2),
            'LC__WIDGET__STATSTABLE__LAST_IDOIT_UPDATE'        => date("d.m.Y H:i:s", filemtime("index.php")),
            'LC__WIDGET__STATSTABLE__ACTIVE_SESSION_COUNT'     => count($l_sessions) . ' (' . implode(', ', $l_sessions) . ')',
            'LC__WIDGET__STATSTABLE__ACTIVE_WIDGETS'           => count($l_dashboard_dao->get_widgets_by_user($g_comp_session->get_user_id())),
        ];

        try
        {
            $l_last_login = $l_dao->retrieve(
                'SELECT isys_cats_person_list__last_login FROM isys_cats_person_list WHERE isys_cats_person_list__isys_obj__id = ' . $g_comp_session->get_user_id() . ';'
            )
                ->get_row();

            try
            {
                $l_locales = isys_locale::get_instance();
            }
            catch (Exception $e)
            {
                $l_locales = isys_locale::get($g_comp_database, $g_comp_session->get_user_id());
            } // try

            $l_data['LC__WIDGET__STATSTABLE__LAST_LOGIN'] = $l_locales->fmt_datetime($l_last_login['isys_cats_person_list__last_login'], false, false);
        }
        catch (isys_exception_database $e)
        {
            $l_data['LC__WIDGET__STATSTABLE__LAST_LOGIN'] = '<p class="exception p5">' . $e->getMessage() . '</p>';
        } // try

        return $this->m_tpl->assign('stats', $l_data)
            ->fetch($this->m_tpl_file);
    } // function
} // class