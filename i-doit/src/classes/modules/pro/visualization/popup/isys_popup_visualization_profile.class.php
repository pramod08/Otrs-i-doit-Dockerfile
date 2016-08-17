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
 * Visualization profile popup.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_popup_visualization_profile extends isys_component_popup
{
    /**
     * @var  isys_component_database
     */
    protected $m_db = null;
    /**
     * Variable which holds the CMDB-Explorer profiles.
     *
     * @var  array
     */
    protected $m_profiles = [];
    /**
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Method for retrieving all configuration options.
     *
     * @return  array
     */
    public static function get_configuration_options()
    {
        return [
            C__VISUALIZATION_PROFILE__OBJ_ID                                => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_ID',
            C__VISUALIZATION_PROFILE__OBJ_SYS_ID                            => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_SYSID',
            C__VISUALIZATION_PROFILE__OBJ_TITLE                             => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_TITLE',
            C__VISUALIZATION_PROFILE__OBJ_TITLE_CMDB_STATUS                 => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_TITLE_WITH_CMDB_STATUS',
            C__VISUALIZATION_PROFILE__OBJ_TYPE_TITLE                        => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_TYPE_TITLE',
            C__VISUALIZATION_PROFILE__OBJ_TYPE_TITLE_ICON                   => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_TYPE_TITLE_ICON',
            C__VISUALIZATION_PROFILE__OBJ_TITLE_TYPE_TITLE_ICON_CMDB_STATUS => 'LC__VISUALIZATION_PROFILES_OPTION__OBJECT_TITLE_TYPE_TITLE_ICON_WITH_CMDB_STATUS',
            C__VISUALIZATION_PROFILE__CMDB_STATUS                           => 'LC__VISUALIZATION_PROFILES_OPTION__CMDB_STATUS',
            C__VISUALIZATION_PROFILE__PRIMARY_IP                            => 'LC__VISUALIZATION_PROFILES_OPTION__PRIMARY_IP',
            C__VISUALIZATION_PROFILE__PRIMARY_HOSTNAME                      => 'LC__VISUALIZATION_PROFILES_OPTION__PRIMARY_HOSTNAME',
            C__VISUALIZATION_PROFILE__PRIMARY_HOSTNAME_FQDN                 => 'LC__VISUALIZATION_PROFILES_OPTION__PRIMARY_HOSTNAME_FQDN',
            C__VISUALIZATION_PROFILE__CATEGORY                              => 'LC_UNIVERSAL__CATEGORY',
            C__VISUALIZATION_PROFILE__PURPOSE                               => 'LC__CMDB__CATG__PURPOSE',
            C__VISUALIZATION_PROFILE__PRIMARY_CONTACT                       => 'LC__VISUALIZATION_PROFILES_OPTION__PRIMARY_CONTACT',
            C__VISUALIZATION_PROFILE__PRIMARY_ACCESS_URL                    => 'LC__VISUALIZATION_PROFILES_OPTION__PRIMARY_ACCESS_URL',
            C__VISUALIZATION_PROFILE__RELATION_TYPE                         => 'LC__VISUALIZATION_PROFILES_OPTION__RELATION_TYPE',
        ];
    } // function

    /**
     * Handles SMARTY request for dialog plus lists and builds the list base on the specified table.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs;

        $l_dialog_data = [];

        foreach ($this->m_profiles as $l_id => $l_profile)
        {
            if (is_array($l_profile['isys_visualization_profile__type_blacklist']) && in_array(
                    $p_params[C__CMDB__VISUALIZATION_TYPE],
                    $l_profile['isys_visualization_profile__type_blacklist']
                )
            )
            {
                continue;
            } // if

            $l_dialog_data[$l_id] = _L($l_profile['isys_visualization_profile__title']);
        } // foreach

        $l_button = '';

        $l_dialog_options = [
            'name'              => $p_params['name'],
            'p_strSelectedID'   => $p_params['p_strSelectedID'],
            'p_strClass'        => 'input input-mini',
            'p_bInfoIconSpacer' => 0,
            'p_arData'          => [_L('LC__VISUALIZATION_PROFILES') => $l_dialog_data],
            'p_bSort'           => false,
            'p_bDbFieldNN'      => true,
            'p_strTitle'        => _L('LC__VISUALIZATION_PROFILES'),
            'nowiki'            => $p_params['nowiki']
        ];

        if (isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::VIEW, 'explorer_profiles')
        )
        {
            $l_button = '<a href="javascript:" class="ml5 vam" title="' . _L('LC__VISUALIZATION_PROFILES_DESCRIPTION') . '" onclick="' . $this->process_overlay(
                    '',
                    1000,
                    700,
                    $p_params
                ) . '">' . '<img class="vam" src="' . $g_dirs['images'] . 'icons/silk/pencil.png" />' . '</a>';
        } // if

        return isys_factory::get_instance('isys_smarty_plugin_f_dialog')
            ->navigation_edit($this->m_tpl, $l_dialog_options) . $l_button;
    } // function

    /**
     * Method for handling the module request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  null
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_rules = $this->load_configuration_gui(null);

        $this->m_tpl->activate_editmode()
            ->assign(
                'edit_right',
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'explorer_profiles')
            )
            ->assign(
                'delete_right',
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::DELETE, 'explorer_profiles')
            )
            ->assign(
                'ajax_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX_CALL => 'visualization',
                        C__GET__AJAX      => 1
                    ]
                )
            )
            ->assign(
                'ajax_property_url',
                isys_helper_link::create_url(
                    [
                        C__GET__AJAX_CALL => 'smartyplugin',
                        C__GET__AJAX      => 1
                    ]
                )
            )
            ->assign('profiles', $this->m_profiles)
            ->assign('default_profile', isys_usersettings::get('cmdb-explorer.default-profile', null))
            ->smarty_tom_add_rules('tom.popup.visualization', $l_rules)
            ->display(dirname(__DIR__) . DS . 'assets' . DS . 'popup_profile.tpl');
        die;
    } // function

    /**
     * This method will load all available visualization profiles to $this->m_profiles.
     *
     * @return  isys_popup_visualization_profile
     */
    protected function get_profiles()
    {
        $l_profile_res = isys_factory::get_instance('isys_visualization_profile_model', $this->m_db)
            ->get_profile();

        if (count($l_profile_res))
        {
            while ($l_row = $l_profile_res->get_row())
            {
                try
                {
                    $l_row['isys_visualization_profile__title']                 = _L($l_row['isys_visualization_profile__title']);
                    $l_row['isys_visualization_profile__defaults']              = isys_format_json::decode($l_row['isys_visualization_profile__defaults']);
                    $l_row['isys_visualization_profile__obj_info_config']       = isys_format_json::decode($l_row['isys_visualization_profile__obj_info_config']);
                    $l_row['isys_visualization_profile__config']                = isys_format_json::decode($l_row['isys_visualization_profile__config']);
                    $l_row['isys_visualization_profile__type_blacklist']        = explode(',', $l_row['isys_visualization_profile__type_blacklist']);
                    $this->m_profiles[$l_row['isys_visualization_profile__id']] = $l_row;
                }
                catch (Exception $e)
                {
                    isys_notify::error('Profile "' . _L($l_row['isys_visualization_profile__title']) . '" could not be loaded: ' . $e->getMessage(), ['sticky' => true]);
                } // try
            } // while
        } // if

        return $this;
    } // function

    /**
     * Method for loading all necessary smarty rules for the given profile.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    protected function load_configuration_gui($p_id = null)
    {
        global $g_dirs;

        $l_profile = [];

        if ($p_id !== null)
        {
            $l_profile = $this->m_profiles[$p_id];
        } // if

        $l_standard_object_type_filter = $l_service_filter = [];

        $l_object_types = isys_cmdb_dao::instance($this->m_db)
            ->get_object_type();

        if (is_array($l_object_types) && count($l_object_types))
        {
            foreach ($l_object_types as $l_object_type)
            {
                $l_standard_object_type_filter[] = [
                    'id'  => $l_object_type['isys_obj_type__id'],
                    'val' => $l_object_type['LC_isys_obj_type__title'],
                    'sel' => in_array($l_object_type['isys_obj_type__id'], $l_profile['isys_visualization_profile__defaults']['obj-type-filter'] ?: [])
                ];
            } // foreach
        } // if

        $l_service_filters = isys_itservice_dao_filter_config::instance($this->m_db)
            ->get_data();

        if (is_array($l_service_filters) && count($l_service_filters))
        {
            foreach ($l_service_filters as $l_filter)
            {
                $l_service_filter[$l_filter['isys_itservice_filter_config__id']] = $l_filter['isys_itservice_filter_config__title'];
            } // foreach
        } // if

        $l_return = [
            'C__VISUALIZATION_PROFILES__TITLE'                      => [
                'p_strClass'       => 'input input-small',
                'p_strPlaceholder' => 'LC__VISUALIZATION_PROFILES__FORM__TITLE',
                'p_strValue'       => $l_profile['isys_visualization_profile__title']
            ],
            'C__VISUALIZATION_PROFILES__WIDTH'                      => [
                'p_strValue' => $l_profile['isys_visualization_profile__config']['width']
            ],
            'C__VISUALIZATION_PROFILES__HIGHLIGHT_COLOR'            => [
                'p_strClass' => 'input input-mini js-color',
                'p_strValue' => ($l_profile['isys_visualization_profile__config']['highlight-color'] ?: '538cdd')
            ],
            'C__VISUALIZATION_PROFILES__SHOW_PATH'                  => [
                'p_strClass'      => 'input input-mini',
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_bDbFieldNN'    => true,
                'p_strSelectedID' => $l_profile['isys_visualization_profile__config']['show-cmdb-path']
            ],
            'C__VISUALIZATION_PROFILES__SHOW_TOOLTIP'               => [
                'p_strClass'      => 'input input-mini',
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_bDbFieldNN'    => true,
                'p_strSelectedID' => $l_profile['isys_visualization_profile__config']['tooltip']
            ],
            'C__VISUALIZATION_PROFILES__MASTER_TOP'                 => [
                'p_strClass'      => 'input input-mini',
                'p_arData'        => [
                    0 => _L('LC__VISUALIZATION_PROFILES__FORM__TOP_LEFT'),
                    1 => _L('LC__VISUALIZATION_PROFILES__FORM__BOTTOM_RIGHT')
                ],
                'p_bDbFieldNN'    => true,
                'p_strSelectedID' => $l_profile['isys_visualization_profile__config']['master_top']
            ],

            // Default values
            'C__VISUALIZATION_PROFILES__DEFAULT_ORIENTATION'        => [
                'p_strClass'      => 'input input-mini',
                'p_arData'        => [
                    'horizontal' => _L('LC__MODULE__CMDB__VISUALIZATION__ORIENTATION__HORIZONTAL'),
                    'vertical'   => _L('LC__MODULE__CMDB__VISUALIZATION__ORIENTATION__VERTICAL'),
                ],
                'p_bDbFieldNN'    => true,
                'p_strSelectedID' => $l_profile['isys_visualization_profile__defaults']['orientation']
            ],
            'C__VISUALIZATION_PROFILES__DEFAULT_SERVICE_FILTER'     => [
                'p_strClass'      => 'input input-mini',
                'p_arData'        => $l_service_filter,
                'p_strSelectedID' => $l_profile['isys_visualization_profile__defaults']['service-filter']
            ],
            'C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER' => [
                'p_strClass' => 'input',
                'p_arData'   => $l_standard_object_type_filter
            ]
        ];

        $l_content_options = static::get_configuration_options();

        if (!is_array($l_profile['rows']))
        {
            $l_profile['rows'] = array_fill(0, 8, []);
        } // if

        foreach ($l_profile['rows'] as $l_i => $l_row)
        {
            $l_index = ($l_i + 1);

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__ROW'] = [
                'p_bChecked'        => true,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'row-toggle',
                'p_strTitle'        => _L('LC__VISUALIZATION_PROFILES__FORM__ROW', $l_index)
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FILLCOLOR'] = [
                'p_strClass' => 'input input-mini js-color',
                'p_strValue' => ($l_row['fillcolor'] ?: 'ffffff')
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FILLCOLOR_OBJ_TYPE'] = [
                'p_strStyle' => 'margin-right:0;',
                'p_strClass' => 'btn toggle-button ml20 ' . ($l_row['fillcolor_obj_type'] ? 'btn-green' : ''),
                'p_strTitle' => 'LC__VISUALIZATION_PROFILES__FORM__FILLCOLOR_BY_OBJ_TYPE',
                'p_strValue' => 'LC__VISUALIZATION_PROFILES__FORM__FILLCOLOR_BY_OBJ_TYPE',
                'icon'       => $g_dirs['images'] . 'icons/silk/color_swatch.png'
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FONTCOLOR'] = [
                'p_strClass' => 'input input-mini js-color',
                'p_strValue' => ($l_row['fontcolor'] ?: '000000')
            ];

            // Font styles.
            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FONT_BOLD'] = [
                'p_strStyle' => 'margin-right:0;',
                'p_strClass' => 'btn toggle-button ml20 ' . ($l_row['font-bold'] ? 'btn-green' : ''),
                'p_strTitle' => 'LC_UNIVERSAL__FONT_BOLD',
                'icon'       => $g_dirs['images'] . 'icons/silk/text_bold.png'
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FONT_ITALIC'] = [
                'p_strStyle' => 'margin-right:0;',
                'p_strClass' => 'btn toggle-button ml5' . ($l_row['font-italic'] ? 'btn-green' : ''),
                'p_strTitle' => 'LC_UNIVERSAL__FONT_ITALIC',
                'icon'       => $g_dirs['images'] . 'icons/silk/text_italic.png'
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FONT_UNDERLINE'] = [
                'p_strStyle' => 'margin-right:0;',
                'p_strClass' => 'btn toggle-button ml5' . ($l_row['font-underline'] ? 'btn-green' : ''),
                'p_strTitle' => 'LC_UNIVERSAL__FONT_UNDERLINE',
                'icon'       => $g_dirs['images'] . 'icons/silk/text_underline.png'
            ];

            // Text alignments.
            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FONT_ALIGN_MIDDLE'] = [
                'p_strStyle' => 'margin-right:0;',
                'p_strClass' => 'btn toggle-button text-align ml20 ' . ($l_row['font-align-middle'] ? 'btn-green' : ''),
                'p_strTitle' => 'LC_UNIVERSAL__FONT_ALIGN_CENTER',
                'icon'       => $g_dirs['images'] . 'icons/silk/text_align_center.png'
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__FONT_ALIGN_RIGHT'] = [
                'p_strStyle' => 'margin-right:0;',
                'p_strClass' => 'btn toggle-button text-align ' . ($l_row['font-align-right'] ? 'btn-green' : ''),
                'p_strTitle' => 'LC_UNIVERSAL__FONT_ALIGN_RIGHT',
                'icon'       => $g_dirs['images'] . 'icons/silk/text_align_right.png'
            ];

            $l_return['C__VISUALIZATION_PROFILES__R' . $l_index . '__OPTION'] = [
                'p_strClass'      => 'input input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => $l_content_options,
                'p_strSelectedID' => ($l_row['option'] ?: C__VISUALIZATION_PROFILE__OBJ_TITLE)
            ];
        } // for

        return $l_return;
    } // function

    /**
     * Constructor method.
     */
    public function __construct()
    {
        global $g_comp_database, $g_comp_template;

        $this->m_db  = $g_comp_database;
        $this->m_tpl = $g_comp_template;

        parent::__construct();

        $this->get_profiles();
    } // function
} // class