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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.2.0
 */
class isys_ajax_handler_dashboard_popup extends isys_ajax_handler
{
    /**
     * This variable contains information for the ajax url
     *
     * @var array
     */
    protected $m_ajax_url = [];
    /**
     * This varialbe holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        global $g_comp_template;

        $this->m_tpl = $g_comp_template;
        $l_return    = '';

        $this->set_ajax_url(
            [
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'dashboard',
                'func'            => 'update_widget'
            ]
        );

        // We set the header information because we don't accept anything than JSON.
        try
        {
            switch ($_GET['func'])
            {
                case 'load_config_popup':
                    isys_auth_dashboard::instance()
                        ->check(isys_auth::EXECUTE, 'CONFIGURE_WIDGETS');
                    $l_return = $this->load_config_popup($_POST['params']);
                    break;

                case 'load_widget_config':
                    isys_auth_dashboard::instance()
                        ->check(isys_auth::EXECUTE, 'CONFIGURE_DASHBOARD');
                    $l_return = $this->load_widget_config();
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $this->m_tpl->assign('title', _L('LC__AUTH__DASHBOARD_EXCEPTION'));
            $l_return = '<p class="p5 exception">' . $e->getMessage() . '</p>';
        } // try

        $l_url = $this->m_ajax_url;

        echo $this->m_tpl->activate_editmode()
            ->assign('config_id', end(explode('-', $_GET['popup'])))
            ->assign('unique_id', $_GET['unique_id'])
            ->assign('content', $l_return)
            ->assign('ajax_url', isys_helper_link::create_url($l_url))
            ->assign('www_data', isys_module_dashboard::get_tpl_www_dir())
            ->assign('css_path', isys_module_dashboard::get_tpl_dir() . 'popup-style.css')
            ->fetch(isys_module_dashboard::get_tpl_dir() . 'popup.tpl');

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * Sets the ajax url parameters as array
     *
     * @param $p_ajax_array
     */
    public function set_ajax_url($p_ajax_array)
    {
        $this->m_ajax_url = $p_ajax_array;
    } // function

    /**
     * Gets the ajax url parameters as array
     *
     * @return array
     */
    public function get_ajax_url()
    {
        return $this->m_ajax_url;
    } // function

    /**
     * Method for retrieving the widget config, where you can add and sort widgets.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function load_widget_config()
    {
        global $g_comp_session;

        $l_dao     = isys_dashboard_dao::instance($this->m_database_component);
        $l_widgets = [];

        $this->set_ajax_url(
            [
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'dashboard'
            ]
        );

        // At first we load all available widgets for the dialog-field.
        $l_res = $l_dao->get_data();

        while ($l_row = $l_res->get_row())
        {
            $l_classname = 'isys_dashboard_widgets_' . $l_row['isys_widgets__identifier'];

            if (class_exists($l_classname))
            {
                $l_widgets[$l_row['isys_widgets__const']] = _L($l_row['isys_widgets__title']);
            } // if

            if (!class_exists($l_classname))
            {
                // Use registered class which has been registered by the specified module
                $l_classname = isys_register::factory('widget-register')
                    ->get($l_row['isys_widgets__identifier']);
            } // if

            if (class_exists($l_classname))
            {
                $l_widgets[$l_row['isys_widgets__const']] = _L($l_row['isys_widgets__title']);
            } // if
            else
            {
                continue;
            } // if
        } // while

        // Now we load the selected widgets to display them for manipulation (removing, sorting).
        $l_res = $l_dao->get_widgets_by_user($g_comp_session->get_user_id());

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                if (class_exists('isys_dashboard_widgets_' . $l_row['isys_widgets__identifier']) || isset($l_widgets[$l_row['isys_widgets__const']]))
                {
                    // Add widget to selection if class exists or is registered by the module
                    $l_widget_list[] = [
                        'row_id' => $l_row['isys_widgets_config__id'],
                        'title'  => _L($l_row['isys_widgets__title'])
                    ];
                }
            } // while
        }
        else
        {
            // We have defined no dashboard - So we load the default.
            $l_default_res = $l_dao->get_widgets_by_default();

            if (count($l_default_res) > 0)
            {
                while ($l_row = $l_default_res->get_row())
                {
                    if (class_exists('isys_dashboard_widgets_' . $l_row['isys_widgets__identifier']))
                    {
                        $l_widget_list[] = [
                            'row_id' => $l_row['isys_widgets__const'],
                            'title'  => _L($l_row['isys_widgets__title'])
                        ];
                    }
                } // while
            } // if
        } // if

        $l_ajax_url                     = isys_helper_link::create_url($this->get_ajax_url() + ['func' => 'save_dashboard_config']);
        $l_define_default_ajax_url      = isys_helper_link::create_url($this->get_ajax_url() + ['func' => 'define_dashboard_default']);
        $l_overwrite_dashboard_ajax_url = isys_helper_link::create_url($this->get_ajax_url() + ['func' => 'overwrite_user_dashboard']);

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__MODULE__DASHBOARD__WIDGET_CONFIGURATION__TITLE'))
            ->assign('description', _L('LC__MODULE__DASHBOARD__WIDGET_CONFIGURATION__TITLE_DESCRIPTION'))
            ->assign('widget_selection', serialize($l_widgets))
            ->assign('widget_list', $l_widget_list)
            ->assign('ajax_url', $l_ajax_url)
            ->assign('define_default_ajax_url', $l_define_default_ajax_url)
            ->assign('overwrite_dashboard_ajax_url', $l_overwrite_dashboard_ajax_url)
            ->assign(
                'is_allowed_to_administrate_dashboard',
                isys_auth_dashboard::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'CONFIGURE_OTHER_DASHBOARDS')
            )
            ->fetch(isys_module_dashboard::get_tpl_dir() . 'widget-config.tpl');
    } // function

    /**
     * Method for retrieving the selected widget.
     *
     * @param   string $p_config
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_config_popup($p_config)
    {
        $l_config = isys_format_json::decode(base64_decode($p_config), true);

        $l_class = 'isys_dashboard_widgets_' . $l_config['identifier'];

        if (!class_exists($l_class))
        {
            $l_class = isys_register::factory('widget-register')
                ->get($l_config['identifier']);
        } // if

        if (class_exists($l_class))
        {
            $l_row = isys_dashboard_dao::instance($this->m_database_component)
                ->get_widgets_by_user(null, $l_config['id'])
                ->get_row();

            $l_widget_config = $l_row['isys_widgets_config__configuration'];

            if (empty($l_widget_config))
            {
                $l_widget_config = $l_row['isys_widgets__default_config'];
            } // if

            $l_class = isys_dashboard_widgets::factory($l_class)
                ->init(isys_format_json::decode(isys_glob_utf8_encode($l_widget_config), true));

            if ($l_class->has_configuration())
            {
                if ($l_class->has_ajax_handler())
                {
                    $this->set_ajax_url($l_class->get_ajax_url());
                }

                return $l_class->load_configuration($l_row, $l_config['id']);
            }
            else
            {
                return $this->display_empty_configuration_page($l_row);
            } // if
        } // if
    } // function

    /**
     * This method displays the "default" popup content, if no configuration is available.
     *
     * @param   array $p_widget_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function display_empty_configuration_page($p_widget_row)
    {
        return $this->m_tpl->assign('title', _L('LC__WIDGET__NO_CONFIG'))
            ->assign('description', _L('LC__WIDGET__NO_CONFIG_DESCRIPTION', _L($p_widget_row['isys_widgets__title'])))
            ->fetch(isys_module_dashboard::get_tpl_dir() . 'no-config.tpl');
    } // function
} // class