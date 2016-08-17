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
 * Cable-connection Browser
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_cable_connection_ng extends isys_popup_browser_object_ng
{
    /**
     * Name of the parameter, which inherits the second-selection ID.
     */
    const C__SECOND_SELECTION_ID = 'second_selection_id';

    const C__ONLY_LOGICAL_PORTS = 'only_log_ports';

    private $m_only_logical_ports = false;

    /**
     * Returns a formatted string for the selected SAN-Pool.
     *
     * @param   integer $p_connector_list_id
     * @param   boolean $p_create_link
     *
     * @return  string
     */
    public function format_selection($p_connector_list_id, $p_create_link = true)
    {
        global $g_comp_database, $g_dirs, $g_comp_template_language_manager;

        if (empty($p_connector_list_id))
        {
            return '-';
        } // if

        $l_quick_info = new isys_ajax_handler_quick_info();

        // If we connect logical ports, we don't need the rest.
        if ($this->m_only_logical_ports === true)
        {
            $l_row = isys_cmdb_dao_category_g_network_ifacel::instance($g_comp_database)
                ->get_data($p_connector_list_id)
                ->get_row();

            $l_title = $l_row['isys_obj__title'] . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_row['isys_catg_log_port_list__title'];

            if ($p_create_link)
            {
                return '<img class="vam" height="15" title="' . $g_comp_template_language_manager->get(
                    'LC__CATG__STORAGE_CONNECTION_TYPE'
                ) . '" src="' . $g_dirs['images'] . '/dtree/special/power_f_socket.gif"> ' . $l_quick_info->get_quick_info(
                    $l_row['isys_obj__id'],
                    $l_title,
                    C__LINK__CATG,
                    false,
                    [
                        C__CMDB__GET__CATG     => C__CMDB__SUBCAT__NETWORK_INTERFACE_L,
                        C__CMDB__GET__CATLEVEL => $p_connector_list_id,
                    ]
                );
            }
            else
            {
                return $l_title;
            } // if
        } // if

        $l_dao = new isys_cmdb_dao_category_g_connector($g_comp_database);

        if (is_null($p_connector_list_id))
        {
            $p_connector_list_id = -1;
        } // if

        $l_data  = $l_dao->get_data($p_connector_list_id)
            ->__to_array();
        $l_title = $l_data['isys_obj__title'] . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_data['isys_catg_connector_list__title'];

        if ($p_create_link)
        {
            return '<img class="vam" height="15" title="' . $g_comp_template_language_manager->get(
                'LC__CATG__STORAGE_CONNECTION_TYPE'
            ) . '" src="' . $g_dirs['images'] . '/dtree/special/power_f_socket.gif"> ' . $l_quick_info->get_quick_info(
                $l_data['isys_obj__id'],
                $l_title,
                C__LINK__CATG,
                false,
                [
                    C__CMDB__GET__CATG     => C__CATG__CONNECTOR,
                    C__CMDB__GET__CATLEVEL => $p_connector_list_id,
                ]
            );
        }
        else
        {
            return $l_title;
        } // if
    } // function

    /**
     * Handle ajax request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  json
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function &handle_ajax_request(isys_module_request $p_modreq)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        $l_dao_distributor = new isys_cmdb_dao_distributor(
            $g_comp_database, $_GET[C__CMDB__GET__OBJECT], C__CMDB__CATEGORY__TYPE_GLOBAL, null, [C__CATG__CONNECTOR => true]
        );

        $l_guidata = $l_dao_distributor->get_guidata(C__CATG__CONNECTOR);
        $l_cat     = $l_dao_distributor->get_category(C__CATG__CONNECTOR);

        if (is_object($l_cat))
        {
            if ($_GET[self::C__ONLY_LOGICAL_PORTS])
            {
                /**
                 * @var  $l_logport_dao  isys_cmdb_dao_category_g_network_ifacel
                 */
                $l_logport_dao = isys_cmdb_dao_category_g_network_ifacel::instance($g_comp_database);
                $l_logport_res = $l_logport_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, '', C__RECORD_STATUS__NORMAL);

                $l_json = [];

                if ($l_logport_res->num_rows() > 0)
                {
                    while ($l_logport_row = $l_logport_res->get_row())
                    {
                        $l_connected_to = '-';

                        if ($l_logport_row['isys_catg_log_port_list__isys_catg_log_port_list__id'] !== null)
                        {
                            $l_row          = $l_logport_dao->get_data($l_logport_row['isys_catg_log_port_list__isys_catg_log_port_list__id'])
                                ->get_row();
                            $l_connected_to = $l_row['isys_obj__title'] . isys_tenantsettings::get(
                                    'gui.separator.connector',
                                    ' > '
                                ) . $l_row['isys_catg_log_port_list__title'];
                        } // if

                        $l_json[] = [
                            '__checkbox__'                                                                                     => isys_glob_utf8_encode(
                                $l_logport_row['isys_catg_log_port_list__id']
                            ),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__CONNECTORS'))        => isys_glob_utf8_encode(
                                $l_logport_row['isys_catg_log_port_list__title']
                            ),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__UI_ASSIGNED_UI'))    => isys_glob_utf8_encode($l_connected_to),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__INTERFACE_L__TYPE')) => isys_glob_utf8_encode(
                                $l_logport_row['isys_netx_ifacel_type__title']
                            )
                        ];
                    } // while

                    // Set header-information.
                    header('Content-type: application/json');

                    return isys_format_json::encode($l_json);
                } // if
            }
            else
            {
                $l_json           = [];
                $l_data           = $l_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, '', C__RECORD_STATUS__NORMAL);
                $l_dao_connection = new isys_cmdb_dao_cable_connection($g_comp_database);

                if ($l_data->num_rows() > 0)
                {

                    while ($l_row = $l_data->get_row())
                    {
                        $l_category       = $l_cat->get_assigned_category_title($l_row['isys_catg_connector_list__assigned_category']);
                        $l_connector_data = $l_dao_connection->get_assigned_connector($l_row['isys_catg_connector_list__id']);

                        if ($l_connector_data->num_rows() > 0)
                        {
                            $l_connector_data = $l_connector_data->__to_array();
                            $l_connected_to   = $l_dao_connection->get_obj_name_by_id_as_string(
                                    $l_connector_data['isys_catg_connector_list__isys_obj__id']
                                ) . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_connector_data['isys_catg_connector_list__title'];
                        }
                        else
                        {
                            $l_connected_to = '-';
                        } // if

                        // Set in- or output.
                        $l_inout = $g_comp_template_language_manager->get('LC__CATG__CONNECTOR__OUTPUT');
                        if ($l_row['isys_catg_connector_list__type'] == C__CONNECTOR__INPUT)
                        {
                            $l_inout = $g_comp_template_language_manager->get('LC__CATG__CONNECTOR__INPUT');
                        } // if

                        $l_json[] = [
                            '__checkbox__'                                                                                  => isys_glob_utf8_encode(
                                $l_row[$l_guidata['isysgui_catg__source_table'] . '_list__id']
                            ),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__CONNECTORS'))     => isys_glob_utf8_encode(
                                $l_row[$l_guidata['isysgui_catg__source_table'] . '_list__title']
                            ),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__UI_ASSIGNED_UI')) => isys_glob_utf8_encode($l_connected_to),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATG__CATEGORY'))       => isys_glob_utf8_encode($l_category),
                            isys_glob_utf8_encode($g_comp_template_language_manager->get('LC__CMDB__CATS__PRT_TYPE'))       => isys_glob_utf8_encode($l_inout)
                        ];
                    } // while

                    // Set header-information.
                    header('Content-type: application/json');

                    return isys_format_json::encode($l_json);
                } // if
            } // if
        }
        else
        {
            $l_json = [];
        } // if

        header('Content-type: application/json');

        return isys_format_json::encode($l_json);
    } // function

    /**
     * Handle the popup request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @throws  Exception
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_template_language_manager, $g_comp_database;

        $l_params = isys_format_json::decode(base64_decode($_POST['params']));

        if (is_array($l_params) || is_object($l_params))
        {
            // Convert the parameter-object to an associative array.
            $l_params = (array) $l_params;

            // Unpack module request.
            $l_gets           = $p_modreq->get_gets();
            $this->m_template = $p_modreq->get_template();

            // The categories we need.
            $l_catg = [
                C__CATG__NETWORK,
                C__CATG__CONTROLLER_FC_PORT,
                C__CATG__CABLING
            ];

            // Getting a few object types.
            $l_dao_cmdb      = new isys_cmdb_dao($g_comp_database);
            $l_obj_types_res = $l_dao_cmdb->get_obj_type_by_catg($l_catg);
            $l_arObjectTypes = [];

            // Write the object types in the a array.
            while ($l_row = $l_obj_types_res->get_row())
            {
                $l_arObjectTypes[$l_row['isys_obj_type__id']] = $g_comp_template_language_manager->get($l_row['isys_obj_type__title']);
            } // while

            // Create Ajax URL.
            $l_ajaxgets = [
                C__CMDB__GET__POPUP           => $l_gets[C__CMDB__GET__POPUP],
                C__GET__MODULE_ID             => C__MODULE__CMDB,
                C__CMDB__GET__CONNECTION_TYPE => $l_gets[C__CMDB__GET__CONNECTION_TYPE],
                C__CMDB__GET__CATG            => $l_gets[C__CMDB__GET__CATG],
                C__GET__AJAX_REQUEST          => 'handle_ajax_request',
                self::C__ONLY_LOGICAL_PORTS   => $l_params[self::C__ONLY_LOGICAL_PORTS]
            ];

            $this->m_only_logical_ports = $l_params[self::C__ONLY_LOGICAL_PORTS];

            // Set a nice browser-name
            if (!isset($l_params[self::C__TITLE]))
            {
                $this->m_template->assign('browser_title', $g_comp_template_language_manager->{'LC__POPUP__BROWSER__OBJECT_BROWSER'});
            }
            else
            {
                $this->m_template->assign('browser_title', $g_comp_template_language_manager->{$l_params[self::C__TITLE]});
            } // if

            // Assign the Ajax URL for calling from the template.
            $this->m_template->assign('ajax_url', isys_glob_build_url(isys_glob_http_build_query($l_ajaxgets)))// Assign our object-types to the template.
            ->assign('arObjectTypes', $l_arObjectTypes)// Assign tab configuration.
            ->assign('tabs', $this->m_tabconfig)// Set the parameters for submitting the popup
            ->assign('return_element', $l_params['hidden'])
                ->assign('return_view', $l_params['view'])
                ->assign('return_cable_name', $l_params['cable_name'])
                ->assign(self::C__TYPE_FILTER, $l_params[self::C__TYPE_FILTER])
                ->assign(self::C__GROUP_FILTER, $l_params[self::C__GROUP_FILTER])
                ->assign(self::C__CALLBACK__ACCEPT, $l_params[self::C__CALLBACK__ACCEPT])
                ->assign(self::C__CALLBACK__ABORT, $l_params[self::C__CALLBACK__ABORT])
                ->assign(self::C__CALLBACK__DETACH, $l_params[self::C__CALLBACK__DETACH])
                ->assign('usageWarning', $l_params['usageWarning'])
                ->assign('arAllObjectTypes', $this->get_object_types_by_filter($l_params))// Javascript initialization.
                ->assign('js_init', 'popup/cable_connection_ng.js');

            // This code will preselect the objects, we selected since the last request (Open browser, select and close. Open browser again).
            if (isset($_GET['live_preselection']))
            {
                if ($_GET['live_preselection'] > 0)
                {
                    $l_params[self::C__SECOND_SELECTION_ID] = $_GET['live_preselection'];
                }
                else
                {
                    $l_params[self::C__SECOND_SELECTION_ID] = null;
                }
            } // if

            // Handle the preselection.
            $this->handle_preselection($l_params[self::C__SECOND_SELECTION_ID]);

            if ($this->m_tabconfig['report']['disabled'] == false)
            {
                $this->m_template->assign('reports', $this->handle_report_request());
            } // if

            // Enable second selection.
            if (isset($l_params[self::C__SECOND_SELECTION]) && $l_params[self::C__SECOND_SELECTION] == true)
            {
                $this->m_template->assign('secondSelection', true);
            }
            else
            {
                $this->m_template->assign('secondSelection', false);
            } // if

            // Show popup content and die.
            $this->m_template->display('popup/object_ng.tpl');

            die();
        }
        else
        {
            throw new Exception('Parameter error.');
        } // if
    } // function

    /**
     * Handles the preselection and assigns these to smarty.
     *
     * @param   integer $l_port_preselection
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function handle_preselection($l_port_preselection)
    {
        global $g_comp_database;

        // Create a DAO object for the connection.
        $l_dao = new isys_cmdb_dao_category_g_connector($g_comp_database);

        if ($this->m_only_logical_ports === true && $l_port_preselection > 0)
        {
            $l_row = isys_cmdb_dao_category_g_network_ifacel::instance($g_comp_database)
                ->get_data($l_port_preselection)
                ->get_row();

            $this->m_template->assign(
                'preselection',
                isys_format_json::encode(
                    [
                        $l_row['isys_obj__id'],
                        $l_row['isys_obj__title'],
                        _L($l_dao->get_objtype_name_by_id_as_string($l_row['isys_obj__isys_obj_type__id'])),
                        $l_row['isys_obj__sysid']
                    ]
                )
            );

            $this->m_template->assign(
                'second_preselection',
                isys_format_json::encode(
                    [
                        $l_port_preselection,
                        $l_row['isys_catg_log_port_list__title'],
                        null
                    ]
                )
            );
            $this->m_template->assign('category_preselection', (int) $l_row['isys_obj__isys_obj_type__id']);

            return;
        } // if

        // If we have no given preselection, set to something which has definitly no relations.
        if ($l_port_preselection === null)
        {
            $l_port_preselection = -1;
        } // if

        // Get the data as array.
        $l_data = $l_dao->get_data($l_port_preselection)
            ->__to_array();

        // Populate preselection in smarty object.
        if (null !== $l_data['isys_obj__id'])
        {
            $this->m_template->assign(
                'preselection',
                isys_format_json::encode(
                    [
                        $l_data['isys_obj__id'],
                        $l_data['isys_obj__title'],
                        _L($l_dao->get_objtype_name_by_id_as_string($l_data['isys_obj__isys_obj_type__id'])),
                        $l_data['isys_obj__sysid']
                    ]
                )
            );
        }
        else
        {
            $this->m_template->assign('preselection', "[]");
        } // if

        $this->m_template->assign(
            'second_preselection',
            isys_format_json::encode(
                [
                    $l_port_preselection,
                    $l_data['connector_name'],
                    null
                ]
            )
        )
            ->assign('category_preselection', (int) $l_data['isys_obj__isys_obj_type__id']);
    } // function

    /**
     * Handle the smarty include for displaying the form-element.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_comp_database;

        $l_strOut = '';

        // If no name is given, we can skip the rest.
        if (!empty($p_params['name']))
        {
            global $g_dirs, $g_comp_template_language_manager;

            $l_strOut = '';

            if (strstr($p_params["name"], '[') && strstr($p_params["name"], ']'))
            {
                $l_tmp    = explode('[', $p_params["name"]);
                $l_view   = $l_tmp[0] . '__VIEW[' . $l_tmp[1];
                $l_hidden = $l_tmp[0] . '__HIDDEN[' . $l_tmp[1];

                $l_attr = [
                    'hidden'     => $l_hidden,
                    'cable_name' => $p_params['name'] . '__CABLE_NAME',
                    'view'       => $l_view,
                ];

                unset($l_tmp);
            }
            else
            {
                $l_attr = [
                    'hidden'     => $p_params['name'] . '__HIDDEN',
                    'cable_name' => $p_params['name'] . '__CABLE_NAME',
                    'view'       => $p_params['name'] . '__VIEW',
                ];
            }

            // f_text parameters + parameters.
            $l_objPlugin                    = new isys_smarty_plugin_f_text();
            $p_params['id']                 = $l_attr['view'];
            $p_params[C__CMDB__GET__OBJECT] = $_GET[C__CMDB__GET__OBJECT];
            $p_params['hidden']             = $l_attr['hidden'];
            $p_params['view']               = $l_attr['view'];
            $p_params['cable_name']         = $l_attr['cable_name'];
            $p_params['p_bReadonly']        = 1;

            if ($p_params["p_strValue"])
            {
                $l_port_id = $p_params["p_strValue"];
            }
            else if ($p_params["p_strSelectedID"])
            {
                $l_port_id = $p_params["p_strSelectedID"];
            }
            else
            {
                $l_port_id = null;
            }

            if (strstr($l_port_id, '"'))
            {
                $l_port_id = (int) isys_format_json::decode($l_port_id);
            }

            if ($l_port_id == 'null')
            {
                $l_port_id = 0;
            }

            $l_obj_id = 0;
            if (isset($p_params["p_objValue"]) && is_numeric($p_params["p_objValue"]) && $p_params["p_objValue"] > 0)
            {
                $l_obj_id = $p_params["p_objValue"];
            }
            else
            {
                if (is_numeric($l_port_id) && $l_port_id > 0)
                {
                    $l_dao_connector = new isys_cmdb_dao_category_g_connector($g_comp_database);
                    $l_data          = $l_dao_connector->get_data($l_port_id)
                        ->get_row();
                    $l_obj_id        = $l_data['isys_catg_connector_list__isys_obj__id'];
                }
            }

            $p_params[self::C__SELECTION]           = $l_obj_id;
            $p_params[self::C__SECOND_SELECTION_ID] = $l_port_id;

            $l_strHiddenField = '<input id="' . $l_attr['hidden'] . '" name="' . $l_attr['hidden'] . '" class="' . $p_params['hidden_class'] . '" type="hidden" value="' . $l_port_id . '" />' . '<input id="' . $l_attr['cable_name'] . '" name="' . $l_attr['cable_name'] . '" type="hidden" value="" />';

            $l_detach_callback = isset($p_params[self::C__CALLBACK__DETACH]) ? $p_params[self::C__CALLBACK__DETACH] : "";

            $l_onclick_detach = "var e_view = $('" . $l_attr['view'] . "'), " . "e_hidden = $('" . $l_attr['hidden'] . "');" .

                "if(e_view && e_hidden) {" . "e_view.value = '" . $g_comp_template_language_manager->get(
                    'LC__UNIVERSAL__CONNECTION_DETACHED'
                ) . "!'; " . "e_hidden.value = '';" . "}" . $l_detach_callback;

            $this->m_only_logical_ports = $p_params[self::C__ONLY_LOGICAL_PORTS];
            if (isys_glob_is_edit_mode() || $p_params[self::C__EDIT_MODE])
            {
                $p_params["p_strValue"] = $this->format_selection($l_port_id, false);

                // Textfield.
                $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $p_params);

                // Opener.
                $l_strOut .= '<a href="javascript:" title="' . _L('LC__UNIVERSAL__ATTACH') . '" class="ml5" onClick="' . $this->process_overlay(
                        "live_preselection=' + $('" . $l_attr['hidden'] . "').value + '",
                        1100,
                        650,
                        $p_params
                    ) . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt="Open the browser" />' . '</a>';

                // Detacher.
                $l_strOut .= '<a href="javascript:" title="' . _L(
                        'LC__UNIVERSAL__DETACH'
                    ) . '" class="ml5" onClick="' . $l_onclick_detach . ';" >' . '<img src="' . $g_dirs["images"] . 'icons/silk/detach.png" alt="Detach" />' . '</a>' . $l_strHiddenField;
            }
            else
            {
                $p_params["p_strValue"] = $this->format_selection($l_port_id, true);

                $l_strOut .= $l_objPlugin->navigation_view($p_tplclass, $p_params) . $l_strHiddenField;
            } // if
        } // if

        return $l_strOut;
    } // function

    /**
     * Public constructor, which calls the parent constructor.
     *
     * @see isys_popup_browser::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_tabconfig['location']['disabled'] = true;
        $this->m_tabconfig['search']['disabled']   = true;
        $this->m_tabconfig['report']['disabled']   = true;
    } // function
} // class