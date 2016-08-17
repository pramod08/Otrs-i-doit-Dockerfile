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
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   Copyright 2012 - synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_report_view_devices_in_location_detailed extends isys_report_view
{
    /**
     * Class cache
     *
     * @var  array
     */
    private $m_class_cache = [];

    /**
     * File directory
     *
     * @var  string
     */
    private $m_directory = '';

    /**
     * File directory
     *
     * @var  string
     */
    private $m_filename = '';

    /**
     * All helpers for dynamic fields
     *
     * @var  array
     */
    private $m_dynamic_headers = [];

    /**
     * Encoding type.
     *
     * @var  string
     */
    private $m_encoding = 'UTF-8';

    /**
     * Table header
     *
     * @var  array
     */
    private $m_headers = [];

    /**
     * All helpers from properties
     *
     * @var  array
     */
    private $m_helpers = [];

    /**
     * Main SQL
     *
     * @var  string
     */
    private $m_sql = '';

    /**
     * Properties.
     *
     * @var  array
     */
    private $m_view_properties = [];

    /**
     * Helper method for location path.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_location_path($p_obj_id)
    {
        $l_loc_popup = new isys_popup_browser_location();

        return $l_loc_popup->format_selection($p_obj_id);
    } // function

    /**
     * Helper method for translations.
     *
     * @param   $p_lc
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function translate_value($p_lc)
    {
        global $g_comp_template_language_manager;

        return $g_comp_template_language_manager->get($p_lc);
    } // function

    /**
     * Helper method for dialog fields
     *
     * @param $p_params
     * @param $p_id
     *
     * @return string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function dialog($p_params, $p_id)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        $l_return = '';

        if ($p_id > 0)
        {
            $l_table  = $p_params[0][0];
            $l_query  = 'SELECT ' . $l_table . '__title FROM ' . $l_table . ' WHERE ' . $l_table . '__id = ' . $p_id;
            $l_result = $g_comp_database->query($l_query);
            $l_return = $g_comp_template_language_manager->get(array_shift($g_comp_database->fetch_row_assoc($l_result)));
        } // if

        return $l_return;
    } // function

    /**
     * Method for ajax-requests.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ajax_request()
    {
        global $g_comp_database, $g_dirs;

        $l_return = [];

        $this->build_properties();

        $this->m_directory = $g_dirs['temp'];

        $l_extended_properties = isys_component_signalcollection::get_instance()
            ->emit(
                "mod.report.views.view_devices_in_location_detailed.extend_report_properties"
            );

        $l_type_filter = isys_format_json::decode($_POST['objTypeFilter']);

        if (count($l_extended_properties) > 0)
        {
            foreach ($l_extended_properties AS $l_property_type_item)
            {
                foreach ($l_property_type_item AS $l_category_type_id => $l_categories)
                {
                    foreach ($l_categories AS $l_category_id => $l_properties)
                    {
                        if ($l_category_type_id == C__CMDB__CATEGORY__TYPE_GLOBAL)
                        {
                            $this->m_view_properties[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_category_id] = $l_properties;
                        }
                        else if ($l_category_type_id == C__CMDB__CATEGORY__TYPE_SPECIFIC)
                        {
                            $this->m_view_properties[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_category_id] = $l_properties;
                        } // if
                    } // foreach
                } // foreach
            } // foreach
        } // if

        $this->build_query_by_properties();

        if (is_null($_POST['obj_id']))
        {
            $l_obj_id = C__OBJ__ROOT_LOCATION;
        }
        else
        {
            $l_obj_id = $_POST['obj_id'];
        } // if

        $l_objects = isys_cmdb_dao_location::instance($g_comp_database)
            ->get_child_locations_recursive($l_obj_id);

        $l_in = '';

        foreach ($l_objects as $l_object)
        {
            // We want to go sure we get no corrupted data.
            if ($l_object['isys_obj__id'] > 0)
            {
                $l_in .= $l_object['isys_obj__id'] . ',';
            } // if
        } // while

        if ($l_in != '')
        {
            $l_in = rtrim($l_in, ',');
            $this->m_sql .= ' WHERE main.isys_obj__id IN (' . $l_in . ') ';

            if (count($l_type_filter) > 0)
            {
                $this->m_sql .= ' AND main.isys_obj__isys_obj_type__id IN (' . implode(',', $l_type_filter) . ') ';
            }

            $this->m_sql .= 'ORDER BY isys_catg_location_list__parentid';
        }
        else
        {
            header('Content-Type: application/json');

            echo isys_format_json::encode(false);
            die();
        } // if

        foreach (array_keys($this->m_headers) AS $l_val)
        {
            $l_return['headLine'][$l_val] = _L($l_val);
        } // foreach

        $l_result = $g_comp_database->query($this->m_sql);
        if ($g_comp_database->num_rows($l_result) > 0)
        {
            while ($l_row = $g_comp_database->fetch_row_assoc($l_result))
            {
                foreach ($l_row AS $l_lc => $l_value)
                {
                    if (array_key_exists($l_lc, $this->m_helpers))
                    {
                        $l_callback = $this->m_helpers[$l_lc];
                        $l_helper   = explode('::', array_shift($l_callback));
                        $l_class    = $l_helper[0];
                        $l_method   = $l_helper[1];
                        if (class_exists($l_class))
                        {
                            if (isset($this->m_class_cache[$l_class]))
                            {
                                $l_class_obj = $this->m_class_cache[$l_class];
                            }
                            else
                            {
                                $this->m_class_cache[$l_class] = new $l_class($g_comp_database);
                                $l_class_obj                   = $this->m_class_cache[$l_class];
                            }

                            if (method_exists($l_class_obj, $l_method))
                            {
                                if (count($l_callback) > 0)
                                {
                                    $l_row[$l_lc] = isys_glob_utf8_encode(
                                        call_user_func_array(
                                            [
                                                $l_class_obj,
                                                $l_method
                                            ],
                                            [
                                                $l_callback,
                                                $l_row[$l_lc]
                                            ]
                                        )
                                    );
                                }
                                else
                                {
                                    $l_row[$l_lc] = isys_glob_utf8_encode(
                                        call_user_func_array(
                                            [
                                                $l_class_obj,
                                                $l_method
                                            ],
                                            [$l_row[$l_lc]]
                                        )
                                    );
                                }
                            }
                        }
                    }
                    else
                    {

                        if ($l_value == '')
                        {
                            $l_row[$l_lc] = '';
                        }
                        else
                        {
                            $l_row[$l_lc] = isys_glob_utf8_encode($l_value);
                        }
                    }
                }

                if (count($this->m_dynamic_headers) > 0)
                {
                    foreach ($this->m_dynamic_headers AS $l_lc => $l_callback)
                    {
                        $l_callback_arr = explode('::', $l_callback[0]);
                        $l_class        = $l_callback_arr[0];
                        $l_method       = $l_callback_arr[1];

                        if (isset($l_callback[1]))
                        {
                            $l_parameters = [];
                            foreach ($l_callback[1] AS $l_row_field)
                            {
                                $l_parameters[] = $l_row[$l_row_field];
                            }
                        }
                        else
                        {
                            $l_parameters = null;
                        }

                        if (class_exists($l_class))
                        {
                            $l_class_obj = new $l_class($g_comp_database);
                            if (method_exists($l_class_obj, $l_method))
                            {
                                $l_row[$l_lc] = isys_glob_utf8_encode(
                                    call_user_func_array(
                                        [
                                            $l_class_obj,
                                            $l_method
                                        ],
                                        $l_parameters
                                    )
                                );
                            }
                        }
                    }
                }

                $l_return['lineValues'][] = $l_row;
            }
        }
        else
        {
            header('Content-Type: application/json');

            echo isys_format_json::encode(false);
            die();
        }

        $this->generate_csv_file($l_return['headLine'], $l_return['lineValues']);

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);
        die();
    } // function

    /**
     * Method for retrieving the language constant of the report-description.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION_DESCRIPTION__DETAILLED';
    } // function

    /**
     * Initialize method.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        return true;
    } // function

    /**
     * Method for retrieving the language constant of the report-name.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION_AS_LIST';
    }

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @global  isys_component_template $g_comp_template
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function start()
    {
        global $g_comp_template, $g_comp_database;

        // Preparing some variables.
        $l_objtypes = [];

        $l_dao         = isys_cmdb_dao::instance($g_comp_database);
        $l_objtype_res = $l_dao->get_object_types_by_properties();

        if ($l_objtype_res->num_rows() > 0)
        {
            while ($l_objtype_row = $l_objtype_res->get_row())
            {
                $l_objtypes[$l_objtype_row['isys_obj_type__id']] = _L($l_objtype_row['isys_obj_type__title']);
            } // while
        } // if

        asort($l_objtypes);

        $l_rules                                = [];
        $l_rules['C__OBJECT_TYPES']['p_arData'] = serialize($l_objtypes);

        $l_obj_types = $l_dao->get_object_types();
        while ($l_row = $l_obj_types->get_row())
        {
            $l_arr[] = [
                'id'  => $l_row['isys_obj_type__id'],
                'val' => _L($l_row['isys_obj_type__title'])
            ];
        }

        $l_rules['C__VIEW__OBJTYPE__DIALOG_LIST']['p_arData'] = serialize($l_arr);

        // Finally assign the data to the template.
        $g_comp_template->activate_editmode()
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign('download_link', 'temp/' . $this->m_filename);
    } // function

    /**
     * Method for retrieving the template-name of this report.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public function template()
    {
        return 'view_devices_in_location_detailed.tpl';
    } // function

    /**
     * Method for declaring the type of this report.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function type()
    {
        return self::c_php_view;
    } // function

    /**
     * Method for declaring the view-type.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    } // function

    /**
     * Property builder
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function build_properties()
    {
        $this->m_view_properties = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [
                C__CATG__GLOBAL   => [
                    'table'      => 'isys_catg_global_list',
                    'obj_id'     => 'isys_catg_global_list__isys_obj__id',
                    'properties' => [
                        'LC__CMDB__OBJTYPE'            => 'isys_obj_type__title',
                        'LC_UNIVERSAL__OBJECT'         => 'main.isys_obj__title',
                        'LC__CMDB__CATG__GLOBAL_SYSID' => 'main.isys_obj__sysid'
                    ],
                    'helper'     => [
                        'LC__CMDB__OBJTYPE' => ['isys_report_view_devices_in_location_detailed::translate_value']
                    ]
                ],
                C__CATG__LOCATION => [
                    'table'      => 'isys_catg_location_list',
                    'obj_id'     => 'isys_catg_location_list__isys_obj__id',
                    'join'       => [
                        'join_type'  => 'INNER',
                        'join_field' => 'isys_catg_location_list__parentid',
                        'table'      => 'isys_obj',
                        'field'      => 'isys_obj__id',
                        'alias'      => 'loc'
                    ],
                    'properties' => [
                        'LC__CMDB__OBJTYPE__LOCATION' => 'main.isys_obj__id'
                    ],
                    'helper'     => [
                        'LC__CMDB__OBJTYPE__LOCATION' => ['isys_report_view_devices_in_location_detailed::get_location_path']
                    ]
                ]
            ],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => []
        ];
    } // function

    /**
     * Method which builds main query by properties
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function build_query_by_properties()
    {
        $l_sql = '';

        foreach ($this->m_view_properties AS $l_category_type_id => $l_categories)
        {
            foreach ($l_categories AS $l_category_id => $l_properties)
            {
                if (isset($l_properties['table']))
                {
                    $l_table     = $l_properties['table'];
                    $l_obj_field = $l_properties['obj_id'];

                    $l_sql .= 'LEFT JOIN ' . $l_table . ' ON ' . $l_obj_field . ' = main.isys_obj__id ';

                    if (isset($l_properties['join']))
                    {
                        $l_sql .= $l_properties['join']['join_type'] . ' JOIN ' . $l_properties['join']['table'] . ' AS ' . $l_properties['join']['alias'] . ' ON ';
                        $l_sql .= $l_properties['join']['join_field'] . ' = ' . $l_properties['join']['alias'] . '.' . $l_properties['join']['field'] . ' ';
                    } // if

                    foreach ($l_properties['properties'] AS $l_property_key => $l_property_info)
                    {
                        $this->m_headers[$l_property_key] = $l_property_info;
                    } // foreach

                    if (isset($l_properties['helper']))
                    {
                        foreach ($l_properties['helper'] AS $l_field => $l_helper)
                        {
                            $this->m_helpers[$l_field] = $l_helper;
                        } // foreach
                    } // if
                }
                else
                {
                    // Dynamic property.
                    foreach ($l_properties['properties'] AS $l_property_key => $l_property_info)
                    {
                        $this->m_dynamic_headers[$l_property_key] = $l_property_info;
                    } // foreach

                    if (isset($l_properties['helper']))
                    {
                        foreach ($l_properties['helper'] AS $l_field => $l_helper)
                        {
                            $this->m_helpers[$l_field] = $l_helper;
                        } // foreach
                    } // if
                } // if
            } // foreach
        } // foreach

        $l_selection = '*';

        if (count($this->m_headers) > 0)
        {
            $l_selection = '';
            foreach ($this->m_headers AS $l_field_alias => $l_field)
            {
                $l_selection .= $l_field . ' AS ' . $l_field_alias . ',';
            } // foreach

            $l_selection = rtrim($l_selection, ',');
        } // if

        $this->m_headers = array_merge_recursive($this->m_headers, $this->m_dynamic_headers);

        $l_sql_main = 'SELECT ' . $l_selection . ', main.isys_obj__id AS __objid__ FROM isys_obj AS main ' . 'INNER JOIN isys_obj_type ON main.isys_obj__isys_obj_type__id = isys_obj_type__id ' . $l_sql;

        $this->m_sql = $l_sql_main;
    } // function

    /**
     * Method which generates a csv file
     *
     * @param $p_header
     * @param $p_content
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function generate_csv_file($p_header, $p_content)
    {
        $l_encoding = array_shift(
            isys_component_signalcollection::get_instance()
                ->emit(
                    "mod.report.views.view_devices_in_location_detailed.set_encoding_type"
                )
        );
        if ($l_encoding != '' && $l_encoding != $this->m_encoding)
        {
            $this->m_encoding = $l_encoding;
        }

        $this->m_filename = isys_application::instance()->session->get_user_id() . "-csv_export_report_view_devices_in_location.csv";
        $l_filename       = $this->m_directory . $this->m_filename;
        $l_csv            = implode(';', $p_header) . "\n";
        $l_handler        = fopen($l_filename, 'w');
        foreach ($p_content AS $l_row)
        {
            foreach ($l_row AS $l_key => $l_value)
            {
                if (strstr($l_key, 'LC_'))
                {
                    $l_csv .= trim($l_value) . ';';
                }
            }
            $l_csv = substr($l_csv, 0, strlen($l_csv) - 1) . "\n";
        }

        if ($this->m_encoding != 'UTF-8')
        {
            $l_csv = iconv('UTF-8', $this->m_encoding, $l_csv);
        }

        fwrite($l_handler, $l_csv);
        fclose($l_handler);
    } // function
} // class