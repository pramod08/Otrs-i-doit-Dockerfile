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
 * Abstract visualization view class.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
abstract class isys_visualization
{
    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * Variable which holds the visualization model class.
     *
     * @var  isys_visualization_model
     */
    protected $m_model = null;
    /**
     * This array will hold the (filtered) object types for the frontend.
     *
     * @var  array
     */
    protected $m_object_types = [];
    /**
     * Options array which shall hold all necessary configuration.
     *
     * @var  array
     */
    protected $m_options = [];
    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * This method will be called, if the current request is a AJAX request.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    abstract public function process(); // function

    /**
     * This method will be called, if the current request is a AJAX request.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    abstract public function process_ajax(); // function

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @global  array $g_dirs
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_www_dir()
    {
        global $g_config;

        return $g_config['www_dir'] . 'src/classes/modules/pro/visualization/';
    } // function

    /**
     * Initializes the module.
     *
     * @param   array $p_options
     *
     * @return  isys_visualization_graph
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(array $p_options = [])
    {
        return $this->set_options($p_options);
    } // function

    /**
     * Sets the options.
     *
     * @param   array $p_options
     *
     * @return  isys_visualization
     */
    public function set_options(array $p_options)
    {
        $this->m_options = $p_options;

        return $this;
    } // function

    /**
     * Sets a single option.
     *
     * @param   string $p_key
     * @param   mixed  $p_value
     *
     * @return  isys_visualization
     */
    public function set_option($p_key, $p_value)
    {
        $this->m_options[$p_key] = $p_value;

        return $this;
    } // function

    /**
     * Start method.
     *
     * @return  isys_visualization
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function start()
    {
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && substr($_GET['request'], 0, 7) != 'mydoit_')
        {
            $this->process_ajax();
        }
        else
        {
            if ($_GET['export'] == 'graphml')
            {
                $l_export = isys_visualization_export_graphml::factory()
                    ->init(
                        [
                            'object-id'         => $_GET['object'],
                            'type'              => $_GET[C__CMDB__VISUALIZATION_TYPE],
                            'profile-id'        => $_GET['profile'],
                            'service-filter-id' => $_GET['service-filter']
                        ]
                    )
                    ->export();

                set_time_limit(0);
                ob_end_clean();
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Expires: 0");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Content-Type: application/xml; charset=utf-8");
                //header("Content-length: " . (function_exists('mb_strlen') ? mb_strlen($l_export) : strlen($l_export)));
                header("Content-Disposition: attachement; filename=cmdb-export_" . date('Y-m-d') . ".graphml");
                header("Content-transfer-encoding: binary");

                echo $l_export;

                die;
            } // if

            global $index_includes;

            // This is the default fallback for "visualization".
            $l_filters = [];

            foreach (isys_cmdb_dao::instance($this->m_db)
                         ->get_object_type() as $l_id => $l_data)
            {
                $l_icon = $l_data['isys_obj_type__icon'] ?: 'images/icons/silk/page_white.png';

                if (strpos($l_icon, '/') === false)
                {
                    $l_icon = 'images/tree/' . $l_icon;
                } // if

                $this->m_object_types[$l_id] = [
                    'title'    => $l_data['LC_isys_obj_type__title'],
                    'color'    => '#' . $l_data['isys_obj_type__color'],
                    'icon'     => isys_core::request_url() . $l_icon,
                    'filtered' => false
                ];
            } // foreach

            // Sorting by title, keeping the keys.
            uasort(
                $this->m_object_types,
                function ($l_a, $l_b)
                {
                    return strcasecmp($l_a['title'], $l_b['title']);
                }
            );

            $l_service_filter = isys_itservice_dao_filter_config::instance($this->m_db)
                ->get_data();

            $l_filters[-1] = '-';

            // Collect the available it-service filters in a "dialog friendly" way.
            if (is_array($l_service_filter) && count($l_service_filter))
            {
                foreach ($l_service_filter as $l_filter)
                {
                    $l_filters[$l_filter['isys_itservice_filter_config__id']] = $l_filter['isys_itservice_filter_config__title'];
                } // foreach
            } // if

            $l_rules = [
                'C_VISUALIZATION_SERVICE_FILTER' => [
                    'p_strClass'        => 'input input-mini',
                    'p_bInfoIconSpacer' => 0,
                    'p_arData'          => [_L('LC__ITSERVICE__CONFIG') => $l_filters],
                    'p_bDbFieldNN'      => true,
                    'p_strTitle'        => _L('LC__ITSERVICE__CONFIG'),
                    'p_strSelectedID'   => $_GET['service'],
                    'nowiki'            => true
                ]
            ];

            $this->m_tpl->activate_editmode()
                ->assign(
                    'ajax_url_visualization',
                    isys_helper_link::create_url(
                        [
                            C__GET__AJAX      => 1,
                            C__GET__AJAX_CALL => 'visualization'
                        ]
                    )
                )
                ->assign(
                    'ajax_url',
                    isys_helper_link::create_url(
                        [
                            C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                            C__CMDB__VISUALIZATION_TYPE => $_GET[C__CMDB__VISUALIZATION_TYPE],
                            C__CMDB__VISUALIZATION_VIEW => $_GET[C__CMDB__VISUALIZATION_VIEW]
                        ]
                    )
                )
                ->assign(
                    'service_filter_url',
                    isys_helper_link::create_url(
                        [
                            C__GET__MODULE_ID     => C__MODULE__ITSERVICE,
                            C__GET__SETTINGS_PAGE => 'filter-config'
                        ]
                    )
                )
                ->assign('visualization_www_dir', $this->get_www_dir())
                ->assign('visualization_dir', __DIR__)
                ->smarty_tom_add_rules('tom.content.top', $l_rules);

            $index_includes["leftcontent"] = __DIR__ . DS . 'assets' . DS . 'visualization_left.tpl';
            $index_includes["contenttop"]  = __DIR__ . DS . 'assets' . DS . 'visualization_top.tpl';

            $this->process();
        } // if

        return $this;
    }

    /**
     * Visualization constructor.
     *
     * @param  isys_module_request $p_req
     */
    public function __construct(isys_module_request &$p_req)
    {
        $this->m_db    = $p_req->get_database();
        $this->m_tpl   = $p_req->get_template();
        $this->m_model = isys_factory::get_instance(get_class($this) . '_model', $this->m_db);
    }
} // class