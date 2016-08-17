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
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_cmdbexplorer extends isys_dashboard_widgets
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
     */
    public function init($p_config = [])
    {
        if (!$p_config)
        {
            $p_config = [];
        }

        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'cmdb-explorer.tpl';
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
     */
    public function load_configuration(array $p_row, $p_id)
    {
        global $g_comp_database;

        $l_profiles = $l_filters = [];

        $l_service_filter = isys_itservice_dao_filter_config::instance($g_comp_database)
            ->get_data();
        $l_profile_res    = isys_factory::get_instance('isys_visualization_profile_model', $g_comp_database)
            ->get_profile();

        // Collect the available it-service filters in a "dialog friendly" way.
        if (is_array($l_service_filter) && count($l_service_filter))
        {
            foreach ($l_service_filter as $l_filter)
            {
                $l_filters[$l_filter['isys_itservice_filter_config__id']] = $l_filter['isys_itservice_filter_config__title'];
            } // foreach
        } // if

        // Collect the available CMDB-Explorer profiles.
        if (count($l_profile_res))
        {
            while ($l_row = $l_profile_res->get_row())
            {
                // Currently used for the "net" profile. This might be updated, if more view types become available.
                if (strpos($l_row['isys_visualization_profile__type_blacklist'], 'tree') !== false)
                {
                    continue;
                } // if

                $l_profiles[$l_row['isys_visualization_profile__id']] = _L($l_row['isys_visualization_profile__title']);
            } // while
        } // if

        $l_orientation = [
            'horizontal' => _L('LC__MODULE__CMDB__VISUALIZATION__ORIENTATION__HORIZONTAL'),
            'vertical'   => _L('LC__MODULE__CMDB__VISUALIZATION__ORIENTATION__VERTICAL')
        ];

        $l_rules = [
            'C_VISUALIZATION_OBJ_SELECTION'  => [
                'p_strValue' => ($this->m_config['objid'] ?: null),
                'p_strClass' => 'input input-small',
                'nowiki'     => true
            ],
            'C_VISUALIZATION_SERVICE_FILTER' => [
                'p_strClass'      => 'input input-mini',
                'p_strSelectedID' => ($this->m_config['servicefilter_id'] ?: null),
                'p_arData'        => $l_filters,
                'p_bSort'         => false,
                'nowiki'          => true
            ],
            'C_VISUALIZATION_PROFILE'        => [
                'p_strClass'      => 'input input-mini',
                'p_strSelectedID' => ($this->m_config['profile_id'] ?: null),
                'p_arData'        => $l_profiles,
                'p_bSort'         => false,
                'p_bDbFieldNN'    => true,
                'nowiki'          => true
            ],
            'C_VISUALIZATION_ORIENTATION'    => [
                'p_strClass'      => 'input input-mini',
                'p_strSelectedID' => ($this->m_config['orientation'] ?: 'horizontal'),
                'p_arData'        => $l_orientation,
                'p_bDbFieldNN'    => true,
                'nowiki'          => true
            ]
        ];

        $this->m_tpl->activate_editmode()
            ->assign('title', 'CMDB-Explorer')
            ->smarty_tom_add_rules('tom.popup.visualization', $l_rules);

        return $this->m_tpl->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @throws  isys_exception_cmdb
     * @throws  Exception
     * @return  string
     */
    public function render($p_unique_id)
    {
        global $g_dirs, $g_comp_database;

        if (isset($this->m_config['objid']) && $this->m_config['objid'] > 0)
        {
            $l_object_types = [];

            foreach (isys_cmdb_dao::instance($g_comp_database)
                         ->get_object_type() as $l_id => $l_data)
            {
                $l_icon = $l_data['isys_obj_type__icon'] ?: $g_dirs['images'] . 'icons/silk/page_white.png';

                if (strpos($l_icon, '/') === false)
                {
                    $l_icon = $g_dirs['images'] . 'tree/' . $l_icon;
                } // if

                $l_object_types[$l_id] = [
                    'title' => $l_data['LC_isys_obj_type__title'],
                    'color' => '#' . $l_data['isys_obj_type__color'],
                    'icon'  => $l_icon
                ];
            } // foreach

            $l_ajax_url_params = [
                C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                C__CMDB__VISUALIZATION_TYPE => C__CMDB__VISUALIZATION_TYPE__TREE,
                C__CMDB__VISUALIZATION_VIEW => C__CMDB__VISUALIZATION_VIEW__OBJECT
            ];

            $this->m_tpl->assign('ajax_url', isys_helper_link::create_url($l_ajax_url_params))
                ->assign('object_types', isys_format_json::encode($l_object_types))
                ->assign('d3_js', file_get_contents(BASE_DIR . 'src' . DS . 'tools' . DS . 'js' . DS . 'd3' . DS . 'd3-v3.5.5-min.js'))
                ->assign(
                    'objtitle',
                    isys_cmdb_dao::instance($g_comp_database)
                        ->get_obj_name_by_id_as_string($this->m_config['objid'])
                )
                ->assign('objid', $this->m_config['objid'])
                ->assign('filter', $this->m_config['servicefilter_id'])
                ->assign('profile', $this->m_config['profile_id'])
                ->assign('orientation', $this->m_config['orientation']);
        }
        else
        {
            $this->m_tpl->assign('error', _L('LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE'));
        } // if

        return $this->m_tpl->assign('uniqueid', $p_unique_id)
            ->fetch($this->m_tpl_file);
    } // function
} // class