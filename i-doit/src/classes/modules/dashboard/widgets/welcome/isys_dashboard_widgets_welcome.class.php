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
class isys_dashboard_widgets_welcome extends isys_dashboard_widgets
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
        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'welcome.tpl';
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
        global $g_comp_session;

        $l_rules = [
            'animate'    => $this->m_config['animate'],
            'salutation' => $this->m_config['salutation'],
        ];

        $l_name_data = $this->get_name_data($g_comp_session->get_user_id());

        $l_salutation_options = [
            'a' => _L('LC__WIDGET__WELCOME__GREETING_A'),
            'b' => _L('LC__WIDGET__WELCOME__GREETING_B'),
            'c' => _L('LC__WIDGET__WELCOME__GREETING_C'),
            'd' => _L('LC__WIDGET__WELCOME__GREETING_D')
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__WELCOME__CONFIG'))
            ->assign('salutation_options', array_merge_recursive($l_salutation_options, $l_name_data['options']))
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
        global $g_comp_session, $g_comp_database;

        try
        {
            $l_locales = isys_locale::get_instance();
        }
        catch (Exception $e)
        {
            $l_locales = isys_locale::get($g_comp_database, $g_comp_session->get_user_id());
        } // try

        $l_name_data = $this->get_name_data($g_comp_session->get_user_id());

        $l_date = _L(
            'LC__WIDGET__WELCOME__DATE',
            [
                date('H:i'),
                $l_locales->fmt_date(time(), false)
            ]
        );

        return $this->m_tpl->assign('animate', $this->m_config['animate'])
            ->assign('unique_id', $p_unique_id)
            ->assign('salutation', $l_name_data['options'][$this->m_config['salutation']])
            ->assign('date', $l_date)
            ->fetch($this->m_tpl_file);
    } // function

    protected function get_name_data($p_user_id)
    {
        global $g_comp_database;

        /**
         * @var  isys_cmdb_dao_category_s_person_master $l_dao
         */
        $l_dao = isys_cmdb_dao_category_s_person_master::instance($g_comp_database);

        $l_salutation = '';
        $l_person_row = $l_dao->get_data(null, $p_user_id)
            ->get_row();

        if (!empty($l_person_row['isys_cats_person_list__salutation']))
        {
            $l_salutation = $l_dao->callback_property_salutation();
            $l_salutation = $l_salutation[$l_person_row['isys_cats_person_list__salutation']];
        } // if

        $l_name_data = [
            'greeting'   => isys_helper_textformat::get_daytime(),
            'salutation' => $l_salutation,
            'title'      => $l_person_row['isys_cats_person_list__academic_degree'],
            'first_name' => $l_person_row['isys_cats_person_list__first_name'],
            'last_name'  => $l_person_row['isys_cats_person_list__last_name'],
            'options'    => []
        ];

        $l_name_data['options'] = [
            'a' => isys_helper_textformat::get_daytime() . ' ' . $l_name_data['first_name'] . ' ' . $l_name_data['last_name'],
            'b' => isys_helper_textformat::get_daytime() . ' ' . $l_name_data['salutation'] . ' ' . $l_name_data['last_name'],
            'c' => isys_helper_textformat::get_daytime() . ' ' . $l_name_data['salutation'] . ' ' . $l_name_data['title'] . ' ' . $l_name_data['last_name'],
            'd' => _L('LC_UNIVERSAL__HELLO') . ' ' . $l_name_data['first_name']
        ];

        return $l_name_data;
    } // function
} // class