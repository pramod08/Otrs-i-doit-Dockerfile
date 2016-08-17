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
 * Dashboard widget class object information
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_dashboard_widgets_properties extends isys_dashboard_widgets
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
     * Returns the js script for the table
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
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
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function init($p_config = [])
    {
        // Set the cache lifetime to 60 seconds.
        isys_core::expire(isys_convert::MINUTE);

        $this->m_tpl_file        = __DIR__ . DS . 'templates' . DS . 'properties.tpl';
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
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $this->m_config['selected_props'] = isys_format_json::encode($this->m_config['selected_props']);

        $l_ajax_url = isys_helper_link::create_url(
            [
                C__GET__AJAX_CALL => 'dashboard_widgets_properties',
                C__GET__AJAX      => 1
            ]
        );

        return $this->m_tpl->activate_editmode()
            ->assign('title', _L('LC__WIDGET__OBJECT_INFORMATION_LIST'))
            ->assign('config_data', $this->m_config)
            ->assign('provide', C__PROPERTY__PROVIDES__LIST)
            ->assign('ajax_url', $l_ajax_url)
            ->fetch($this->m_config_tpl_file);
    } // function

    /**
     * Abstract render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function render($p_unique_id)
    {
        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->assign('js_script_widget_properties', $this->get_js_table($p_unique_id))
            ->fetch($this->m_tpl_file);
    } // function

    /**
     * This Method gets the js table or other output texts
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author    Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function get_js_table($p_unique_id)
    {
        global $g_comp_database, $g_dirs;

        $l_dao = isys_cmdb_dao_list_objects::instance($g_comp_database);

        if (!empty($this->m_config['list_query']))
        {
            $l_res = $l_dao->retrieve($this->m_config['list_query']);
            if ($l_res->num_rows() > 0)
            {
                $l_data    = $l_dao->format_result($l_res, (array) $this->m_config['config']);
                $l_js_data = isys_format_json::encode($l_data);
                $l_objects = isys_format_json::decode($this->m_config['obj_id']);

                $l_ajax_pager     = false;
                $l_allow_dragging = false;

                $l_return = '<script type="text/javascript">
					// Set translations for the table view.
					idoit.Translate.set(\'LC__UNIVERSAL__TITLE_LINK\', \'' . _L('LC__UNIVERSAL__TITLE_LINK') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF') . '\');
					idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES\', \'' . _L('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES') . '\');

					// We set some variables for the list component.
					window.list_vars = {
						image_dir: \'' . $g_dirs['images'] . '\',
						tree_view: \'' . C__CMDB__VIEW__TREE_OBJECT . '\',
						view_mode: \'' . C__CMDB__VIEW__CATEGORY . '\'
					};

					// Creating a new ObjectTypeList instance for the list.
					window.object_list_' . $p_unique_id . ' = new Lists.Objects(\'prop-list-' . $p_unique_id . '\', {
						max_pages: ' . ceil(count($l_objects) / isys_glob_get_pagelimit()) . ',
						ajax_pager: ' . ($l_ajax_pager ? 'true' : 'false') . ',
						ajax_pager_url: "",
						ajax_pager_preload: "",
						classPrefix: "mainTable w100",
						data: ' . $l_js_data . ',
						filter: false,
						checkboxes: false,
						paginate: false,
						pageCount: ' . (int) isys_glob_get_pagelimit() . ',
						draggable: ' . (($l_allow_dragging) ? 'true' : 'false') . ',
						tr_click: false,
						ndo_state_url: "?' . C__GET__AJAX_CALL . '=monitoring_ndo&' . C__GET__AJAX . '=1&func=load_ndo_states",
						ndo_state_field:"' . _L('LC__MONITORING__NDO__STATUS') . '",
						livestatus_state_url: "?' . C__GET__AJAX_CALL . '=monitoring_livestatus&' . C__GET__AJAX . '=1&func=load_livestatus_states",
						livestatus_state_field:"' . _L('LC__MODULE__CHECK_MK__STATUS') . '"
					});
				</script>';
            }
            else
            {
                $l_return = '<div class="m5">' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>';
            } // if
        }
        else
        {
            $l_return = '<div class="m5">' . _L('LC__WIDGET__OBJECT_INFORMATION_LIST__WIDGET_IS_NOT_CONFIGURED') . '</div>';
        } // if

        return $l_return;
    } // function
} // class