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
use idoit\Component\Helper\Ip;

/**
 * CMDB MultiEdit view.
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_view_multiedit extends isys_cmdb_view
{
    /**
     *
     * @var  boolean
     */
    private $m_custom_category = false;
    /**
     * @var  isys_cmdb_dao_multiedit
     */
    private $m_dao = null;
    /**
     * @var  isys_cmdb_dao_connection
     */
    private $m_dao_connection = null;
    /**
     * @var int
     */
    private $m_iterator = 0;
    /**
     * @var null
     */
    private $m_multivalued = null;
    /**
     * @var array
     */
    private $m_recorded_unallowed_objects = [];
    /**
     * Array of smarty plugins used in this multiedit.
     *
     * @var   array
     */
    private $m_smarty_plugins = [];

    /**
     * Returns the view-ID.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__MULTIEDIT;
    } // function

    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets = [];
    } // function

    /**
     * Returns the view-name.
     *
     * @return  string
     */
    public function get_name()
    {
        return "MultiEdit";
    } // function

    public function get_optional_parameters(&$l_gets)
    {
        $l_gets = [];
    }

    public function handle_navmode($p_navmode)
    {
        ;
    } // function

    public function process()
    {
        $this->init();
    } // function

    /**
     * Inititialize this view
     */
    private function init()
    {
        global $index_includes, $g_comp_template, $g_comp_database;

        isys_auth_cmdb::instance()
            ->check(isys_auth::EXECUTE, 'MULTIEDIT');

        try
        {
            // Intialize multiedit dao.
            $this->m_dao            = new isys_cmdb_dao_multiedit($g_comp_database);
            $this->m_dao_connection = new isys_cmdb_dao_connection($g_comp_database);

            // Activate editmode, disable default buttons.
            $g_comp_template->activate_editmode();
            $g_comp_template->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1');

            isys_component_template_navbar::getInstance()
                ->set_js_onclick('Multiedit.save()', C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE);

            // Ajax request detected.
            if ($_POST["request"])
            {
                try
                {
                    $this->handle_ajax_request($_POST);
                }
                catch (Exception $e)
                {
                    echo '<p class="exception m10 p5">' . $e->getMessage() . '</p><script type="text/javascript">$(\'listLoadButton\').show();</script>';
                } // try

                // Stop processing after handling the ajax request.
                die;
            } // if

            // Initialize variables
            $l_category_list = [];

            /**
             * Process ui.
             */
            $l_category_blacklist[C__CMDB__CATEGORY__TYPE_GLOBAL] = [
                C__CATG__CLUSTER_SERVICE               => true,
                C__CATG__ITS_LOGBOOK                   => true,
                C__CATG__IT_SERVICE_RELATIONS          => true,
                C__CATG__FILE                          => true,
                C__CATG__CLUSTER_SHARED_STORAGE        => true,
                C__CATG__CLUSTER_SHARED_VIRTUAL_SWITCH => true,
                C__CATG__LOGBOOK                       => true,
                C__CATG__LDEV_CLIENT                   => true,
                C__CATG__LDEV_SERVER                   => true,
                C__CATG__RELATION                      => true,
                C__CATG__SNMP                          => true,
                C__CATG__SOA_STACKS                    => true,
                C__CATG__VIRTUAL_TICKETS               => true,
                C__CATG__VIRTUAL_DEVICE                => true,
                C__CATG__VIRTUAL_SWITCH                => true,
                C__CATG__WORKFLOW                      => true,
                C__CATG__CABLING                       => true,
                C__CATG__DATABASE_ASSIGNMENT           => true,
                C__CMDB__SUBCAT__STORAGE__DEVICE       => true,
                C__CATG__GUEST_SYSTEMS                 => true,
                C__CATG__STORAGE                       => true,
                C__CATG__SANPOOL                       => true,
                C__CATG__NETWORK                       => true,
                C__CATG__VOIP_PHONE_LINE               => true,
                C__CATG__RAID                          => true,
                C__CATG__BACKUP__ASSIGNED_OBJECTS      => true,
                C__CATG__CONTRACT_ASSIGNMENT           => true,
                C__CATG__VIRTUAL_AUTH                  => true,
                C__CATG__LDAP_DN                       => true,
                C__CATG__CMK_TAG                       => true,
                C__CATG__CMK                           => true
            ];

            $l_category_blacklist[C__CMDB__CATEGORY__TYPE_SPECIFIC] = [
                //C__CATS__GROUP => true,	// Experiment
                C__CATS__PDU_OVERVIEW                              => true,
                C__CATS__FILE                                      => true,
                C__CMDB__SUBCAT__FILE_VERSIONS                     => true,
                C__CMDB__SUBCAT__FILE_ACTUAL                       => true,
                C__CMDB__SUBCAT__FILE_OBJECTS                      => true,
                C__CATS__APPLICATION_ASSIGNED_OBJ                  => true,
                C__CATS__RELATION_DETAILS                          => true,
                C__CATS__PARALLEL_RELATION                         => true,
                C__CATS__PDU_BRANCH                                => true,
                C__CATS__CHASSIS                                   => true,
                C__CATS__CHASSIS_CABLING                           => true,
                C__CATS__CHASSIS_DEVICES                           => true,
                C__CATS__CHASSIS_VIEW                              => true,
                C__CATS__NET_DHCP                                  => true,
                C__CATS__DATABASE_SCHEMA                           => true,
                C__CATS__DATABASE_ACCESS                           => true,
                C__CATS__NET_IP_ADDRESSES                          => true,
                C__CATS__LAYER2_NET                                => true,
                C__CATS__LAYER2_NET__SUBTYPE__DYNAMIC_VLAN         => true,
                C__CATS__LAYER2_NET__SUBTYPE__STATIC_VLAN          => true,
                C__CATS__LAYER2_NET_ASSIGNED_PORTS                 => true,
                C__CATS__LICENCE                                   => true,
                C__CATS__PERSON_LOGIN                              => true,
                C__CATS__PERSON_GROUP_MEMBERS                      => true,
                C__CATS__PERSON_NAGIOS                             => true,
                C__CATS__PERSON_GROUP_NAGIOS                       => true,
                C__CATS__NET                                       => true,
                C__CMDB__SUBCAT__EMERGENCY_PLAN                    => true,
                C__CATS__GROUP_TYPE                                => true,
                C__CATS__PDU                                       => true,
                C__CATS__PDU_BRANCH                                => true,
                C__CATS__PDU_OVERVIEW                              => true,
                C__CATS__ORGANIZATION_CONTACT_ASSIGNMENT           => true,
                C__CATS__ORGANIZATION_PERSONS                      => true,
                C__CATS__BASIC_AUTH                                => true,
                C__CATS__ROUTER                                    => true,
                C__CATS__SAN_ZONING                                => true,
                C__CATS__PERSON_MASTER                             => true,
                C__CATS__PERSON_GROUP_MASTER                       => true,
                C__CATS__ORGANIZATION_MASTER_DATA                  => true,
                C__CATS__CONTRACT_INFORMATION                      => true,
                C__CATS__CONTRACT_ALLOCATION                       => true,
                C__CATS__CLUSTER_SERVICE                           => true,
                C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST => true,
                C__CMDB__SUBCAT__LICENCE_OVERVIEW                  => true
            ];

            $l_allowed_categories = isys_auth_cmdb_categories::instance()
                ->get_allowed_categories();

            $l_categories             = $this->m_dao->get_all_catg(
                null,
                ' AND isysgui_catg__type IN(' . isys_cmdb_dao_category::TYPE_EDIT . ', ' . isys_cmdb_dao_category::TYPE_FOLDER . ', ' . isys_cmdb_dao_category::TYPE_ASSIGN . ', ' . isys_cmdb_dao_category::TYPE_REAR . ')'
            );
            $l_multivalued_categories = [];

            while ($l_row = $l_categories->get_row())
            {
                $l_parent_folder = '';

                if ($l_allowed_categories === true || (is_array($l_allowed_categories) && in_array($l_row['isysgui_catg__const'], $l_allowed_categories)))
                {
                    if (!isset($l_category_blacklist[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']]) && !strstr(
                            $l_row['isysgui_catg__const'],
                            '_ROOT'
                        ) && !strstr($l_row['isysgui_catg__const'], '_NAGIOS')
                    )
                    {
                        if ($l_row['isysgui_catg__parent'] > 0)
                        {
                            $l_parent_folder = _L($this->m_dao->get_catg_name_by_id_as_string($l_row['isysgui_catg__parent']));
                        } // if

                        if ($l_parent_folder != '')
                        {
                            $l_category_title = ' ' . _L($l_row['isysgui_catg__title']) . ' (' . _L('LC__MULTIEDIT__SUBCATEGORY_OF') . ' ' . $l_parent_folder . ')';
                        }
                        else
                        {
                            $l_category_title = ' ' . _L($l_row['isysgui_catg__title']);
                        } // if

                        $l_category_list[_L('LC__CMDB__GLOBAL_CATEGORIES')][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . $l_row['isysgui_catg__id']] = $l_category_title;

                        if ($l_row['isysgui_catg__list_multi_value'] > 0)
                        {
                            $l_multivalued_categories[C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . $l_row['isysgui_catg__id']] = true;

                            $l_sql            = 'SELECT isys_property_2_cat__prop_key FROM isys_property_2_cat
							WHERE isys_property_2_cat__cat_const = ' . $this->m_dao->convert_sql_text($l_row['isysgui_catg__const']) . '
								AND isys_property_2_cat__prop_type = ' . $this->m_dao->convert_sql_int(C__PROPERTY_TYPE__STATIC) . '
								AND isys_property_2_cat__prop_key != ' . $this->m_dao->convert_sql_text('description') . '
								AND isys_property_2_cat__prop_provides & ' . $this->m_dao->convert_sql_int(C__PROPERTY__PROVIDES__MULTIEDIT);
                            $l_res_check      = $this->m_dao->retrieve($l_sql);
                            $l_property_count = $l_res_check->num_rows();
                            if ($l_property_count === 1)
                            {
                                $l_prop_key   = $l_res_check->get_row_value('isys_property_2_cat__prop_key');
                                $l_cat_dao    = $l_row['isysgui_catg__class_name']::instance($this->m_dao->get_database_component());
                                $l_properties = $l_cat_dao->get_properties();
                                $l_data       = $l_properties[$l_prop_key];

                                if ($l_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER || $l_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M)
                                {
                                    if ($l_data[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] === true)
                                    {
                                        $l_multivalued_categories[C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . $l_row['isysgui_catg__id']] = false;
                                    } // if
                                    continue;
                                } // if
                            }
                            elseif ($l_property_count === 0)
                            {
                                // Remove category from list, because there are no properties which are usable for the multiedit list
                                unset($l_category_list[_L('LC__CMDB__GLOBAL_CATEGORIES')][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . $l_row['isysgui_catg__id']]);
                            } // if
                        } // if
                    } // if
                } // if
            } // while

            unset($l_categories);

            $l_categories = $this->m_dao->get_all_cats();

            while ($l_row = $l_categories->get_row())
            {
                if ($l_allowed_categories === true || (is_array($l_allowed_categories) && in_array($l_row['isysgui_cats__const'], $l_allowed_categories)))
                {
                    if (!isset($l_category_blacklist[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']]) && !strstr(
                            $l_row['isysgui_cats__const'],
                            '_ROOT'
                        ) && !strstr($l_row['isysgui_cats__const'], '_ASSIGN')
                    )
                    {
                        $l_objtypes      = $this->m_dao->get_object_types_by_category($l_row['isysgui_cats__id'], 's', false);
                        $l_objtype_names = '(';
                        foreach ($l_objtypes AS $l_objtype)
                        {
                            $l_objtype_names .= _L($this->m_dao->get_objtype_name_by_id_as_string($l_objtype)) . ', ';
                        }
                        $l_objtype_names                                                                                                           = rtrim(
                                $l_objtype_names,
                                ', '
                            ) . ')';
                        $l_category_list[_L('LC__CMDB__SPECIFIC_CATEGORIES')][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' . $l_row['isysgui_cats__id']] = ' ' . _L(
                                $l_row['isysgui_cats__title']
                            ) . ' ' . $l_objtype_names;

                        if ($l_row['isysgui_cats__list_multi_value'] > 0)
                        {
                            $l_multivalued_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' . $l_row['isysgui_cats__id']] = true;

                            $l_sql            = 'SELECT isys_property_2_cat__prop_key FROM isys_property_2_cat
							WHERE isys_property_2_cat__cat_const = ' . $this->m_dao->convert_sql_text($l_row['isysgui_cats__const']) . '
								AND isys_property_2_cat__prop_type = ' . $this->m_dao->convert_sql_int(C__PROPERTY_TYPE__STATIC) . '
								AND isys_property_2_cat__prop_key != ' . $this->m_dao->convert_sql_text('description') . '
								AND isys_property_2_cat__prop_provides & ' . $this->m_dao->convert_sql_int(C__PROPERTY__PROVIDES__MULTIEDIT);
                            $l_res_check      = $this->m_dao->retrieve($l_sql);
                            $l_property_count = $l_res_check->num_rows();
                            if ($l_property_count === 1)
                            {
                                $l_prop_key   = $l_res_check->get_row_value('isys_property_2_cat__prop_key');
                                $l_cat_dao    = $l_row['isysgui_cats__class_name']::instance($this->m_dao->get_database_component());
                                $l_properties = $l_cat_dao->get_properties();
                                $l_data       = $l_properties[$l_prop_key];

                                if ($l_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER || $l_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M)
                                {
                                    if ($l_data[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] === true)
                                    {
                                        $l_multivalued_categories[C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . $l_row['isysgui_catg__id']] = false;
                                    } // if
                                    continue;
                                } // if
                            }
                            elseif ($l_property_count === 0)
                            {
                                // Remove category from list, because there are no properties which are usable for the multiedit list
                                unset($l_category_list[_L('LC__CMDB__SPECIFIC_CATEGORIES')][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' . $l_row['isysgui_cats__id']]);
                            } // if
                        } // if
                    } // if
                } // if
            } // while

            unset($l_categories);

            $l_categories        = $this->m_dao->get_all_catg_custom();
            $l_custom_fields_dao = new isys_custom_fields_dao($this->m_dao->get_database_component());

            while ($l_row = $l_categories->get_row())
            {
                if ($l_allowed_categories === true || (is_array($l_allowed_categories) && in_array($l_row['isysgui_cats__const'], $l_allowed_categories)))
                {
                    $l_obj_types_res = $l_custom_fields_dao->get_assignments($l_row['isysgui_catg_custom__id']);
                    $l_obj_types     = '(';
                    while ($l_obj_type_row = $l_obj_types_res->get_row())
                    {
                        if ($l_obj_type_row['isys_obj_type__id'] == C__OBJTYPE__GENERIC_TEMPLATE)
                        {
                            continue;
                        } // if

                        $l_obj_types .= _L($l_obj_type_row['isys_obj_type__title']) . ', ';
                    } // while

                    $l_obj_types                                                                                                                  = rtrim(
                            $l_obj_types,
                            ', '
                        ) . ')';
                    $l_category_list[_L('LC__CMDB__CUSTOM_CATEGORIES')][C__CMDB__CATEGORY__TYPE_CUSTOM . '_' . $l_row['isysgui_catg_custom__id']] = ' ' . _L(
                            $l_row['isysgui_catg_custom__title']
                        ) . ' ' . $l_obj_types;
                    if ($l_row['isysgui_catg_custom__list_multi_value'] > 0)
                    {
                        $l_multivalued_categories[C__CMDB__CATEGORY__TYPE_CUSTOM . '_' . $l_row['isysgui_catg_custom__id']] = true;
                    } // if
                } // if
            } // while

            unset($l_categories);

            if (is_array($l_category_list[_L('LC__CMDB__SPECIFIC_CATEGORIES')]))
            {
                asort($l_category_list[_L('LC__CMDB__SPECIFIC_CATEGORIES')]);
            } // if

            if (is_array($l_category_list[_L('LC__CMDB__GLOBAL_CATEGORIES')]))
            {
                asort($l_category_list[_L('LC__CMDB__GLOBAL_CATEGORIES')]);
            } // if

            if (is_array($l_category_list[_L('LC__CMDB__CUSTOM_CATEGORIES')]))
            {
                asort($l_category_list[_L('LC__CMDB__CUSTOM_CATEGORIES')]);
            } // if

            // Assign rules.
            $l_rules = [
                'C__MULTIEDIT__CATEGORY' => [
                    'p_arData'        => serialize($l_category_list),
                    'p_bDbFieldNN'    => false,
                    'p_bSort'         => false,
                    'p_strSelectedID' => @$_GET[C__CMDB__GET__CATG]
                ],
                'C__MULTIEDIT__OBJECTS'  => [
                    isys_popup_browser_object_ng::C__MULTISELECTION => true,
                    'p_strValue'                                    => @$_GET['preselect']
                ]
            ];

            $g_comp_template->assign('multivalue_categories', isys_format_json::encode($l_multivalued_categories));

            $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
            $l_error_message = false;

            // Some php limitation checks.
            if (extension_loaded('suhosin'))
            {
                $l_error_message[] = '* You should disable the php extension suhosin in order to use multiedit properly.';
            } // if

            if (intval(ini_get('post_max_size')) <= 4)
            {
                $l_error_message[] = '* You should increase your post_max_size php.ini setting in order to use multiedit. Current value is: ' . ini_get(
                        'post_max_size'
                    ) . ', Suggested value is 10M or higher.';
            } // if

            if (intval(ini_get('max_input_vars')) < 10000)
            {
                $l_error_message[] = _L('LC__MULTIEDIT__ERROR_MAX_INPUT', ini_get('max_input_vars'));
            } // if

            if ($l_error_message)
            {
                $g_comp_template->assign('message', '<p class="m10 p5 warning">' . implode('<br />', $l_error_message) . '</p>');
            } // if
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        } // try

        $index_includes["contentbottomcontent"] = "modules/multiedit/main.tpl";
    } // function

    /**
     * Display the list after clicking on 'begin listedit' and saves the list.
     *
     * @param   array $p_data
     *
     * @throws  isys_exception_cmdb
     * @throws  Exception
     * @return  boolean
     */
    private function handle_ajax_request($p_data)
    {
        global $g_comp_database;

        try
        {

            $l_object_ids = [];

            if ($p_data['category'] < 0)
            {
                throw new isys_exception_cmdb(_L('LC__MULTIEDIT__PLEASE_SELECT_CATEGORY'));
            } // if

            // Unpack request
            if (isset($p_data['object_ids']))
            {
                $l_object_ids = isys_format_json::decode($p_data['object_ids'], true);
            } // if

            if (isset($p_data['C__MULTIEDIT__OBJECTS__HIDDEN']))
            {
                $l_object_ids = isys_format_json::decode($p_data['C__MULTIEDIT__OBJECTS__HIDDEN'], true);
            } // if

            if (isset($p_data['filter']))
            {
                $l_filter = $p_data['filter'];
            }
            else
            {
                $l_filter = '';
            }

            $l_changes_in_entry  = isys_format_json::decode($p_data['changes_in_entry']);
            $l_changes_in_object = isys_format_json::decode($p_data['changes_in_object']);

            list($l_category_type, $l_category_id) = explode('_', $p_data['category']);

            if (!is_array($l_object_ids) || count($l_object_ids) == 0)
            {
                throw new isys_exception_cmdb(_L('LC__MULTIEDIT__MINIMUM_OBJECT'));
            }

            // Switch category types.
            switch ($l_category_type)
            {
                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    $l_category       = $this->m_dao->get_all_catg($l_category_id)
                        ->__to_array();
                    $l_category_class = $l_category['isysgui_catg__class_name'];
                    $l_source_table   = $l_category['isysgui_catg__source_table'] . '_list';

                    if ($l_category['isysgui_catg__id'] == C__CATG__OBJECT)
                    {
                        $l_source_table = 'isys_catg_location_list';
                    } // if

                    // Check if selected objects own the selected category and remove them from the listedit
                    $l_check = $this->check_objects_with_global_category($l_object_ids, $l_category_id);
                    break;
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $l_category       = $this->m_dao->get_all_cats($l_category_id)
                        ->__to_array();
                    $l_category_class = $l_category['isysgui_cats__class_name'];
                    $l_source_table   = $l_category['isysgui_cats__source_table'];

                    // Check if selected objects own the selected category and remove them from the listedit
                    $l_check = $this->check_objects_with_specific_category($l_object_ids, $l_category_id);
                    break;
                case C__CMDB__CATEGORY__TYPE_CUSTOM;
                    $l_category       = $this->m_dao->get_all_catg_custom($l_category_id)
                        ->__to_array();
                    $l_category_class = $l_category['isysgui_catg_custom__class_name'];
                    $l_source_table   = $l_category['isysgui_catg_custom__source_table'];

                    $l_check = $this->check_object_with_custom_category($l_object_ids, $l_category_id);
                    break;
                default:
                    //'';
                    throw new isys_exception_cmdb('Wrong request: Could not determine type of selected category.');
                    break;
            }

            if (count($l_check) > 0)
            {
                foreach ($l_check AS $l_remove_obj_id)
                {
                    unset($l_object_ids[array_search($l_remove_obj_id, $l_object_ids)]);
                } // foreach
            } // if

            if (count($l_object_ids) == 0)
            {
                throw new isys_exception_cmdb(_L('LC__MULTIEDIT__NO_CATEGORY_ASSIGNMENT'));
            } // if

            if (class_exists($l_category_class))
            {
                /**
                 * Get instance of category dao
                 *
                 * @var $l_cat_dao isys_cmdb_dao_category_global
                 */
                $l_cat_dao = new $l_category_class($g_comp_database);
                if ($l_cat_dao->get_category_id() == C__CATG__CUSTOM_FIELDS && method_exists($l_cat_dao, 'set_catg_custom_id'))
                {
                    $l_cat_dao->set_catg_custom_id($l_category_id);
                    $this->m_custom_category = true;
                }

                unset($l_category);

                if (is_object($l_cat_dao))
                {
                    // Handle request.
                    switch ($p_data['request'])
                    {
                        case 'loadList':
                            // Render list and print it.
                            $l_list = $this->render_list($l_cat_dao, $l_object_ids, $l_source_table, $l_filter);
                            if (count($this->m_recorded_unallowed_objects) == count($l_object_ids))
                            {
                                echo '<p class="exception m10 p5">' . _L(
                                        'LC__MULTIEDIT__NO_AUTHORIZATION_ON_SELECTED_OBJECTS'
                                    ) . '</p><script type="text/javascript">$(\'listLoadButton\').show();</script>';
                            }
                            elseif ($l_list)
                            {
                                if (count($this->m_recorded_unallowed_objects) > 0)
                                {
                                    isys_notify::error(
                                        _L(
                                            'LC__MULTIEDIT__NO_AUTHORIZATION_ON_SELECTED_OBJECTS_PLACEHOLDER',
                                            '<ul class=""><li>' . ((count($this->m_recorded_unallowed_objects) > 10) ? implode(
                                                    '</li><li>',
                                                    array_slice($this->m_recorded_unallowed_objects, 0, 10)
                                                ) . '<li>...</li>' : implode('</li><li>', $this->m_recorded_unallowed_objects)) . '</li></ul>'
                                        )
                                    );
                                } // if
                                echo $l_list;
                            }
                            else
                            {
                                echo '<p class="exception m10 p5">' . _L(
                                        'LC__MULTIEDIT__NO_RESULTS'
                                    ) . '.</p><script type="text/javascript">$(\'listLoadButton\').show();</script>';
                            }
                            break;

                        case 'renderTemplateRow':
                            $this->m_iterator = $_POST['row_counter'];
                            $l_template_row   = $this->render_list($l_cat_dao, $l_object_ids, $l_source_table, $l_filter, true);

                            if ($l_template_row !== false)
                            {
                                echo $l_template_row;
                            }
                            else
                            {
                                isys_notify::warning('Neue Werte können über den Objektbrowser hinzugefügt werden.');
                            } // if

                            break;

                        case 'saveList':
                            if (!isset($l_changes_in_entry[0]) || !$l_changes_in_entry[0])
                            {
                                $l_changes_in_entry = [];
                            }

                            if (count($p_data['C__MULTIEDIT__NEW_ENTRIES']) > 0)
                            {
                                // Prepare data for new entries
                                $l_new_entries = $p_data['C__MULTIEDIT__NEW_ENTRIES'];
                                $l_changes     = count($l_new_entries);
                                $l_new_key     = key($l_new_entries) + 1;

                                $p_data['changes_in_entry'] += $l_changes;
                                $p_data['changes_in_object'] += $l_changes;

                                // Iterate through every object and add it
                                foreach ($l_new_entries AS $l_ident => $l_val)
                                {
                                    $l_remove_ident = null;
                                    if ($l_val === '-1')
                                    {
                                        foreach ($l_object_ids AS $l_obj_id)
                                        {
                                            foreach ($p_data AS $l_key => $l_data)
                                            {
                                                if (is_array($l_data))
                                                {
                                                    if (isset($l_data['new' . $l_ident]))
                                                    {
                                                        $p_data[$l_key]['new' . $l_new_key] = $l_data['new' . $l_ident];
                                                    }
                                                }
                                            }
                                            $p_data['C__MULTIEDIT__NEW_ENTRIES'][$l_new_key] = $l_obj_id;
                                            $l_new_key++;
                                        } // foreach

                                        foreach ($p_data AS $l_key => $l_data)
                                        {
                                            if (is_array($l_data))
                                            {
                                                if (isset($l_data['new' . $l_ident]))
                                                {
                                                    unset($p_data[$l_key]['new' . $l_ident]);
                                                } // if
                                            } // if
                                        } // foreach
                                        unset($p_data['C__MULTIEDIT__NEW_ENTRIES'][$l_ident]);
                                    } // if
                                } // for
                            } // if

                            // Save data.
                            $this->m_dao->save($l_cat_dao, $l_object_ids, $l_source_table, $p_data, $l_changes_in_entry, $l_changes_in_object);
                            break;

                        default:
                            throw new Exception("Invalid request! {" . $p_data . "}");
                    } // switch
                } // if
                else
                {
                    throw new isys_exception_cmdb('Wrong request: Class \'' . $l_category_class . '\' does not exist.');
                } // if

                unset($l_category_class);
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }

        return true;
    }

    /**
     * Render the multiedit list
     *
     * @param isys_cmdb_dao_category $p_cat_dao
     * @param array                  $p_objects
     * @param string                 $p_source_table
     * @param mixed                  $p_filter
     * @param boolean                $p_template
     *
     * @throws Exception
     * @throws isys_exception_database
     * @return string
     */
    private function render_list($p_cat_dao, array $p_objects, $p_source_table, $p_filter = '', $p_template = false)
    {
        if (!is_object($p_cat_dao))
        {
            throw new Exception('Error rendering list. No category DAO instantiated.');
        } // if

        if (!$p_source_table)
        {
            $p_source_table = $p_cat_dao->get_source_table();

            if (!$p_source_table)
            {
                throw new Exception('Error: source_table not found. (Category-DAO: ' . get_class($p_cat_dao) . ')');
            } // if
        } // if

        $this->m_multivalued = $p_cat_dao->is_multivalued();

        // Initialize.
        $i      = $this->m_iterator;
        $l_list = '<table class="mainTable border-bottom border-ccc" width="100%"><thead><tr>';

        if ($p_cat_dao->get_category_id() != C__CATG__GLOBAL) $l_list .= '<th>' . _L('LC_UNIVERSAL__OBJECT') . '</th>';

        // Cache smarty plugins.
        $this->m_smarty_plugins = [
            'f_link'                        => new isys_smarty_plugin_f_text(),
            C__PROPERTY__UI__TYPE__TEXT     => new isys_smarty_plugin_f_text(),
            C__PROPERTY__UI__TYPE__TEXTAREA => new isys_smarty_plugin_f_textarea(),
            C__PROPERTY__UI__TYPE__DATE     => new isys_smarty_plugin_f_popup(),
            C__PROPERTY__UI__TYPE__DATETIME => new isys_smarty_plugin_f_popup(),
            C__PROPERTY__UI__TYPE__DIALOG   => new isys_smarty_plugin_f_dialog(),
            C__PROPERTY__UI__TYPE__POPUP    => new isys_smarty_plugin_f_popup(),
        ];

        /* Object specification condition */
        $l_condition = ' AND (isys_obj__id ' . $p_cat_dao->prepare_in_condition($p_objects) . ')';

        /* ..and it's properties */
        $l_properties = $p_cat_dao->get_properties(C__PROPERTY__WITH__VALIDATION);

        /* We don´t need the description property*/
        unset($l_properties['description']);

        if ($p_cat_dao->get_category_id() == C__CATG__GLOBAL)
        {
            if (C__SYSID__READONLY === true)
            {
                unset($l_properties['sysid']);
            }
            else
            {
                unset($l_properties['sysid'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bDisabled']);
            } // if
        }
        else
        {
            // Property should only be available in global category
            unset($l_properties['sysid']);
        } // if

        $l_assignment_category = false;
        $l_check_properties    = [];

        // Check if its an assignment category.
        if (count($l_properties) == 1)
        {
            $l_check_properties = $l_properties;
            $l_check_properties = array_pop($l_check_properties);
            if ($l_check_properties[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection' || $l_check_properties[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'object')
            {
                $l_assignment_category = true;
            }
        }

        if ($this->m_custom_category)
        {
            /* Prepare header */
            foreach ($l_properties as $l_key => $l_propdata)
            {
                if ($l_propdata[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && ($l_propdata[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] != 'html' && $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] != 'hr'))
                {
                    $l_mandatory = '';

                    if (isset($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) && $l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY])
                    {
                        $l_mandatory = '<strong class="red" title="' . _L('LC__SETTINGS__CMDB__VALIDATION__BUTTON__MANDATORY') . '">*</strong>';
                    } // if

                    $l_list .= '<th>' . _L($l_propdata[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]) . $l_mandatory . '</th>';
                }
                else
                {
                    // Unset unsupported properties
                    unset($l_properties[$l_key]);
                }
            }
            $l_list .= '<th></th></tr></thead><tbody>';

            $l_smarty_params = [
                'p_onChange'                                      => 'Multiedit.overwriteAll(this);',
                isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => 'Multiedit.overwriteAll(\'%%id%%\');',
                isys_popup_browser_object_ng::C__CALLBACK__DETACH => 'Multiedit.overwriteAll(\'%%id%%\');'
            ];

            /* Render first row */
            $l_list .= $this->render_row_custom_fields(
                $l_properties,
                [
                    'isys_obj__title' => (($p_cat_dao->get_category_id() == C__CATG__GLOBAL) ? _L('LC__MULTIEDIT__ALL_OBJECTS') : '<strong>' . _L(
                            'LC__MULTIEDIT__ALL_OBJECTS'
                        ) . '</strong>')
                ],
                $p_cat_dao,
                $p_source_table,
                $i++,
                $l_smarty_params,
                'skip'
            );

            if ($p_template === true)
            {

                return $this->render_row_custom_fields(
                    $l_properties,
                    [
                        $p_source_table . '__id' => 'new' . $this->m_iterator,
                        'objects'                => $p_objects
                    ],
                    $p_cat_dao,
                    $p_source_table,
                    $this->m_iterator,
                    [],
                    null,
                    true
                );
            }

            foreach ($p_objects AS $l_object)
            {
                $l_list_data = $p_cat_dao->get_data(
                    null,
                    $l_object,
                    null,
                    $p_filter,
                    C__RECORD_STATUS__NORMAL
                );

                $l_data = [];

                if ($l_list_data->num_rows() > 0)
                {
                    while ($l_list_row = $l_list_data->get_row())
                    {
                        $l_identifier = $l_list_row['isys_catg_custom_fields_list__field_type'] . '_' . $l_list_row['isys_catg_custom_fields_list__field_key'];

                        if (!isset($l_data[$l_list_row['isys_catg_custom_fields_list__data__id']]['isys_obj__title']))
                        {
                            $l_data[$l_list_row['isys_catg_custom_fields_list__data__id']]['isys_obj__title'] = $p_cat_dao->get_obj_name_by_id_as_string(
                                $l_list_row['isys_catg_custom_fields_list__isys_obj__id']
                            );
                            $l_data[$l_list_row['isys_catg_custom_fields_list__data__id']]['isys_obj__id']    = $l_list_row['isys_catg_custom_fields_list__isys_obj__id'];
                        }

                        if ($l_properties[$l_identifier][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION] > 0)
                        {
                            $l_data[$l_list_row['isys_catg_custom_fields_list__data__id']][$l_identifier][] = $l_list_row;
                        }
                        else
                        {
                            $l_data[$l_list_row['isys_catg_custom_fields_list__data__id']][$l_identifier] = $l_list_row;
                        } // if
                    } // while
                } // if

                if (count($l_data) > 0)
                {
                    foreach ($l_data AS $l_data_id => $l_data_arr)
                    {
                        $l_list .= $this->render_row_custom_fields($l_properties, $l_data_arr, $p_cat_dao, $p_source_table, $i++, [], $l_data_id);
                    }
                }
                else
                {
                    $l_data['isys_obj__id']    = $l_object;
                    $l_data['isys_obj__title'] = $p_cat_dao->get_obj_name_by_id_as_string($l_object);
                    $l_list .= $this->render_row_custom_fields($l_properties, $l_data, $p_cat_dao, $p_source_table, $i++, [], null);
                }
            }

            return $l_list . '</tbody></table>';
        }
        else
        {
            /* Prepare header */
            foreach ($l_properties as $l_key => $l_propdata)
            {
                if (($l_propdata[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && ($l_propdata[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] || $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_ng' || $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_relation' || $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_cable_connection_ng' || $l_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location')))
                {
                    $l_mandatory = '';

                    if (isset($l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) && $l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY])
                    {
                        $l_mandatory = '<strong class="red" title="' . _L('LC__SETTINGS__CMDB__VALIDATION__BUTTON__MANDATORY') . '">*</strong>';
                    } // if

                    $l_list .= '<th>' . _L($l_propdata[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]) . $l_mandatory . '</th>';
                }
                else
                {
                    // Unset unsupported properties
                    unset($l_properties[$l_key]);
                }
            }
            $l_list .= '<th></th></tr></thead><tbody>';

            /* Render first row */
            $l_list .= $this->render_row(
                $l_properties,
                [
                    'isys_obj__title'        => (($p_cat_dao->get_category_id() == C__CATG__GLOBAL) ? _L('LC__MULTIEDIT__ALL_OBJECTS') : '<strong>' . _L(
                            'LC__MULTIEDIT__ALL_OBJECTS'
                        ) . '</strong>'),
                    $p_source_table . '__id' => 'skip'
                ],
                $p_cat_dao,
                $p_source_table,
                $i++,
                [
                    'p_onChange'                                      => 'Multiedit.overwriteAll(this);',
                    isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => 'Multiedit.overwriteAll(\'%%id%%\');',
                    isys_popup_browser_object_ng::C__CALLBACK__DETACH => 'Multiedit.overwriteAll(\'%%id%%\');'
                ]
            );

            if ($p_template === true)
            {
                if ($l_assignment_category === true) return false;

                return $this->render_row(
                    $l_properties,
                    [
                        $p_source_table . '__id' => 'new' . $this->m_iterator,
                        'objects'                => $p_objects
                    ],
                    $p_cat_dao,
                    $p_source_table,
                    $this->m_iterator,
                    [],
                    true
                );
            }
        }

        // Iterate through data.
        if ($this->m_multivalued)
        {
            //@todo solution for categories which list consists only of objects, at the moment each object connection is displayed.

            // Get category data.
            if ($p_cat_dao->get_category_id() == C__CATG__CONNECTOR)
            {
                $l_list_data = $p_cat_dao->get_data(
                    null,
                    $p_objects,
                    ' AND (isys_catg_connector_list.isys_catg_connector_list__assigned_category = ' . C__CATG__CONNECTOR . ' OR isys_catg_connector_list.isys_catg_connector_list__assigned_category = ' . $p_cat_dao->convert_sql_text(
                        'C__CATG__CONNECTOR'
                    ) . ') ',
                    $p_filter,
                    C__RECORD_STATUS__NORMAL
                );
            }
            elseif ($p_cat_dao->get_category_id() != C__CATG__APPLICATION)
            {
                $l_list_data = $p_cat_dao->get_data(
                    null,
                    $p_objects,
                    '',
                    $p_filter,
                    C__RECORD_STATUS__NORMAL
                );
            }
            else if (method_exists($p_cat_dao, 'get_data_ng'))
            {
                $l_list_data = $p_cat_dao->get_data_ng(
                    null,
                    $p_objects,
                    ' AND reference.isys_obj__title LIKE ' . $p_cat_dao->convert_sql_text('%' . addslashes($p_filter) . '%') . ' ',
                    null,
                    C__RECORD_STATUS__NORMAL
                );
            } // if

            if (isset($l_list_data) && $l_list_data instanceof isys_component_dao_result)
            {
                /* object pool of objects already processed */
                $l_objectpool = [];

                if ($l_assignment_category)
                {
                    $l_list .= $this->render_row_object_browser($l_check_properties, $p_objects, $l_list_data, $p_cat_dao, $p_source_table, $i++);
                }
                else
                {
                    /* Return false if there where no results found */
                    //if ($l_list_data->num_rows() === 0) return false;
                    if ($l_list_data->num_rows() > 0)
                    {
                        /* Iterate through category rows */
                        while ($l_row = $l_list_data->get_row())
                        {
                            /* Render this row */
                            $l_list .= $this->render_row($l_properties, $l_row, $p_cat_dao, $p_source_table, $i++);
                        }
                    }
                }

                unset($l_objectpool);
            }

        }
        else
        {
            // Separate iteration for single value, because if theres no list entry for a single value category, get_data won't return any row.
            $l_list_data = $this->m_dao->retrieve('SELECT * FROM isys_obj WHERE TRUE ' . $l_condition . ' ORDER BY isys_obj__title ASC;');

            if ($l_assignment_category)
            {
                $l_list_data = $p_cat_dao->get_data(null, $p_objects, null, null, C__RECORD_STATUS__NORMAL);
                $l_list .= $this->render_row_object_browser($l_check_properties, $p_objects, $l_list_data, $p_cat_dao, $p_source_table, $i++);
            }
            else
            {
                while ($l_row = $l_list_data->get_row())
                {
                    // Get data for current object.
                    $l_category_data = $p_cat_dao->get_data(null, $l_row['isys_obj__id'], '', $p_filter, C__RECORD_STATUS__NORMAL);

                    $l_check = true;

                    // See ID-3022
                    if (isys_strlen(trim($p_filter)) !== 0)
                    {
                        // See ID-2964
                        $l_check = !! count($l_category_data);
                    } // if

                    if ($l_check)
                    {
                        $l_category_row                    = $l_category_data->get_row();
                        $l_category_row['isys_obj__title'] = $l_row['isys_obj__title'];
                        $l_category_row['isys_obj__id']    = $l_row['isys_obj__id'];

                        $l_list .= $this->render_row($l_properties, $l_category_row, $p_cat_dao, $p_source_table, $i++);
                    } // if

                    // Free some memory.
                    $l_category_data->free_result();
                    unset($l_category_row);
                } // while
            } // if
        } // if

        return $l_list . '</tbody></table>';
    }

    /**
     * Renders a row with an object browser for assignment categories.
     *
     * @param   array                     $p_property
     * @param   array                     $p_objects
     * @param   isys_component_dao_result $p_res
     * @param   isys_cmdb_dao_category    $p_cat_dao
     * @param   string                    $p_source_table
     * @param   integer                   $p_iterator
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function render_row_object_browser($p_property, $p_objects, $p_res, $p_cat_dao, $p_source_table, $p_iterator)
    {
        global $g_comp_template;

        // Get right field.
        if (isset($p_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]))
        {
            $l_field = $p_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
        }
        else
        {
            $l_field = $p_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        } // if

        $l_arr_objects = [];
        /* Get Objects */
        while ($l_row = $p_res->get_row())
        {
            if ($p_cat_dao->get_category_id() == C__CATG__OBJECT)
            {
                // @todo find a better solution
                $l_arr_objects[$l_row['isys_catg_location_list__parentid']][] = (int) $l_row[$l_field];
            }
            elseif ($p_cat_dao->get_category_id() == C__CATG__PERSON_ASSIGNED_WORKSTATION)
            {
                // @todo find a better solution
                $l_arr_objects[$l_row[$p_source_table . '__isys_obj__id__parent']][] = (int) $l_row[$l_field];
            }
            elseif ($p_cat_dao->get_category_id() == C__CATG__ASSIGNED_LOGICAL_UNIT)
            {
                // @todo find a better solution
                $l_arr_objects[$l_row['isys_catg_logical_unit_list__isys_obj__id__parent']][] = (int) $l_row[$l_field];
            }
            elseif ($p_cat_dao->get_category_id() == C__CATG__CLUSTER_MEMBERSHIPS || $p_cat_dao->get_category_id() == C__CATG__IT_SERVICE || $p_cat_dao->get_category_id(
                ) == C__CATG__GROUP_MEMBERSHIPS
            )
            {
                // @todo find a better solution
                $l_arr_objects[$l_row['isys_connection__isys_obj__id']][] = (int) $l_row[$l_field];
            }
            elseif ($p_cat_dao->get_object_id_field() != 'isys_obj__id')
            {
                $l_arr_objects[$l_row[$p_cat_dao->get_object_id_field()]][] = (int) $l_row[$p_cat_dao->get_connected_object_id_field()];
            }
            elseif (((isset($p_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]) && $p_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection') || $p_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection') && $l_row[$l_field] > 0)
            {
                $l_arr_objects[$l_row[$p_source_table . '__isys_obj__id']][] = (int) $this->m_dao_connection->get_object_id_by_connection($l_row[$l_field]);
            }
            else
            {
                $l_arr_objects[$l_row[$p_source_table . '__isys_obj__id']][] = (int) $l_row[$l_field];
            } // if
        } // while

        $l_list        = '';
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        foreach ($p_objects as $l_object)
        {
            // Get plugin name.
            $l_name = $p_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '[object-' . $l_object . ']';

            // Smarty base param initialization.
            $l_params = array_merge(
                [
                    'name'              => $l_name,
                    'p_onChange'        => "Multiedit.changed(this);Multiedit.changesInObject('" . $l_object . "');",
                    'p_bInfoIconSpacer' => false,
                    'editMode'          => true,
                    'p_strClass'        => $p_property[C__PROPERTY__UI][C__PROPERTY__UI__ID],
                ],
                $p_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]
            );

            if (isset($l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT])) $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] = str_replace(
                '%%id%%',
                $p_property[C__PROPERTY__UI][C__PROPERTY__UI__ID],
                $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT]
            );

            if (isset($l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH])) $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] = str_replace(
                '%%id%%',
                $p_property[C__PROPERTY__UI][C__PROPERTY__UI__ID],
                $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH]
            );

            if ($p_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_ng' || $p_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_relation' || $p_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_cable_connection_ng' || $p_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location')
            {
                $l_params[isys_popup_browser_object_ng::C__EDIT_MODE] = true;

                if (isset($l_arr_objects[$l_object]))
                {
                    if ($l_params['multiselection'] === true)
                    {
                        $l_params['p_strSelectedID'] = $l_arr_objects[$l_object];
                        unset($l_params['p_strValue']);
                    }
                    else
                    {
                        $l_params['p_strValue'] = array_pop($l_arr_objects[$l_object]);
                        unset($l_params['p_strSelectedID']);
                    } // if
                }
                else
                {
                    $l_strValue = true;
                    $l_value    = null;
                    $l_obj      = null;
                    if (isset($l_params['p_strValue']) && is_object($l_params['p_strValue']) && is_a($l_params['p_strValue'], 'isys_callback'))
                    {
                        $l_obj = $l_params['p_strValue'];
                        unset($l_params['p_strSelectedID']);
                    }
                    elseif (isset($l_params['p_strSelectedID']) && is_object($l_params['p_strSelectedID']) && is_a($l_params['p_strSelectedID'], 'isys_callback'))
                    {
                        $l_obj      = $l_params['p_strSelectedID'];
                        $l_strValue = false;
                        unset($l_params['p_strValue']);
                    }

                    if (is_object($l_obj))
                    {
                        $l_value = $l_obj->execute(
                            isys_request::factory()
                                ->set_object_id($l_object)
                        );
                        if (is_object($l_value) && is_a($l_value, 'isys_component_dao_result'))
                        {
                            $l_new_value = [];
                            while ($l_vrow = $l_value->get_row())
                            {
                                $l_new_value[] = array_pop($l_vrow);
                            } // while
                            $l_value = $l_new_value;
                        }
                    }

                    if ($l_strValue)
                    {
                        $l_params['p_strValue'] = $l_value;
                    }
                    else
                    {
                        $l_params['p_strSelectedID'] = $l_value;
                    }
                }

                $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= "Multiedit.changed();Multiedit.changesInObject('" . $l_object . "');";
                $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] .= "Multiedit.changed();Multiedit.changesInObject('" . $l_object . "');";
            } // if
            $l_title = $p_cat_dao->get_obj_name_by_id_as_string($l_object);

            // Check rights
            if ($l_object !== null && !isys_auth_cmdb::instance()
                    ->check_rights_obj_and_category(isys_auth::EDIT, $l_object, $p_cat_dao->get_category_const())
            )
            {
                $this->m_recorded_unallowed_objects[$l_object] = $l_title . ' (' . _L(
                        $p_cat_dao->get_objtype_name_by_id_as_string($p_cat_dao->get_objTypeID($l_object))
                    ) . ')';
                continue;
            } // if

            $l_list .= '<tr class="' . ($p_iterator % 2 == 0 ? 'CMDBListElementsEven' : 'CMDBListElementsOdd') . '" style="cursor:default;">';

            $l_list .= '<td title="' . $l_title . '">' . '<input type="hidden" name="category_data[object-' . $l_object . ']" value="' . $l_object . '" /> ' . '<input type="hidden" name="sort-dummy" value="' . $l_title . '" /> ' . isys_glob_str_stop(
                    $l_title,
                    35
                ) . '</td>';

            if (method_exists($this->m_smarty_plugins[$p_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]], 'navigation_edit'))
            {
                $l_list .= '<td>' . $this->m_smarty_plugins[$p_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]]->navigation_edit($g_comp_template, $l_params) . '</td>';
            }
            else
            {
                $l_list .= '<td>' . $l_empty_value . '</td>';
            } // if

            $l_list .= '</tr>';
        } // foreach
        return $l_list;
    } // function

    /**
     * Renders a single row
     *
     * @param                        $p_properties
     * @param                        $p_row
     * @param isys_cmdb_dao_category $p_cat_dao
     * @param                        $p_source_table
     * @param int                    $p_iterator
     * @param array                  $p_smarty_params
     *
     * @return string
     */
    private function render_row($p_properties, $p_row, $p_cat_dao, $p_source_table, $p_iterator = 0, $p_smarty_params = [], $p_template = false)
    {
        global $g_comp_template;

        if (!isset($p_row[$p_source_table . '__id']) && isset($p_row['isys_obj__id']))
        {
            /* Mark missing category entries with "new-" prefix */
            $p_row[$p_source_table . '__id'] = 'new-' . $p_row['isys_obj__id'];
        } // if

        $l_list = '<tr class="' . ($p_iterator % 2 == 0 ? 'CMDBListElementsEven' : 'CMDBListElementsOdd') . ' ' . ($p_template === true ? 'NewEntry' : '') . '" style="cursor:default;" data-category-id="' . $p_row[$p_source_table . '__id'] . '">';

        if ($p_template === false)
        {
            if (!empty($p_row['isys_obj__title']) && isset($p_row[$p_cat_dao->get_object_id_field()]))
            {
                $l_title = $p_cat_dao->get_obj_name_by_id_as_string($p_row[$p_cat_dao->get_object_id_field()]);
            }
            else
            {
                $l_title = $p_row['isys_obj__title'];
            }
            // Check rights
            if ($p_row['isys_obj__id'] !== null && !isys_auth_cmdb::instance()
                    ->check_rights_obj_and_category(isys_auth::EDIT, $p_row['isys_obj__id'], $p_cat_dao->get_category_const())
            )
            {
                $this->m_recorded_unallowed_objects[$p_row['isys_obj__id']] = $l_title . ' (' . _L(
                        $p_cat_dao->get_objtype_name_by_id_as_string($p_cat_dao->get_objTypeID($p_row['isys_obj__id']))
                    ) . ')';

                return '';
            } // if

            /* Show object title if category is not the global one */
            if ($p_cat_dao->get_category_id(
                ) != C__CATG__GLOBAL
            ) $l_list .= '<td title="' . $l_title . '">' . '<input type="hidden" name="category_data[' . $p_row[$p_source_table . '__id'] . '-' . $p_row['isys_obj__id'] . ']" value="' . $p_row[$p_source_table . '__id'] . '" /> ' . '<input type="hidden" name="sort-dummy" value="' . $l_title . '" /> ' . isys_glob_str_stop(
                    $l_title,
                    35
                ) . '</td>';
        }
        else
        {
            // First column for the object chosen dialog
            $l_list .= '<td >' . '<input type="hidden" name="category_data[' . $p_row[$p_source_table . '__id'] . ']" value="' . $p_row[$p_source_table . '__id'] . '" /> ';

            $l_objects = $p_row['objects'];
            $l_arr     = ['-1' => 'Alle Objekte'];

            foreach ($l_objects AS $l_obj_id)
            {
                $l_arr[$l_obj_id] = $this->m_dao->get_obj_name_by_id_as_string($l_obj_id);
            } // foreach

            $l_params = [
                'chosen'            => 1,
                'p_arData'          => serialize($l_arr),
                'p_bDbFieldNN'      => true,
                'p_bSort'           => false,
                'p_strSelectedID'   => '-1',
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'normal',
                'p_bEnableMetaMap'  => 0,
                'name'              => 'C__MULTIEDIT__NEW_ENTRIES[' . $this->m_iterator . ']'
            ];

            $l_list .= $this->m_smarty_plugins[C__PROPERTY__UI__TYPE__DIALOG]->navigation_edit($g_comp_template, $l_params);

            $l_list .= '</td>';
        } // if

        /* Iterate through properties */
        foreach ($p_properties as $l_propdata)
        {
            $l_list .= $this->render_cell($l_propdata, $p_row, $p_source_table, $p_smarty_params, $p_properties);
        } // foreach
        $l_list .= '</tr>';

        return $l_list;
    } // function

    /**
     * @param array                  $p_properties
     * @param array                  $p_row
     * @param isys_cmdb_dao_category $p_cat_dao
     * @param string                 $p_source_table
     * @param int                    $p_iterator
     * @param array                  $p_smarty_params
     * @param null                   $p_data_id
     *
     * @return string
     */
    private function render_row_custom_fields($p_properties, $p_row, $p_cat_dao, $p_source_table, $p_iterator = 0, $p_smarty_params = [], $p_data_id = null, $p_template = false)
    {
        global $g_comp_template;

        if (!empty($p_row['isys_obj__title']) && isset($p_row[$p_cat_dao->get_object_id_field()])) $l_title = $p_cat_dao->get_obj_name_by_id_as_string(
            $p_row[$p_cat_dao->get_object_id_field()]
        );
        else $l_title = $p_row['isys_obj__title'];

        // Check rights
        if ($p_row['isys_obj__id'] !== null && !isys_auth_cmdb::instance()
                ->check_rights_obj_and_category(isys_auth::EDIT, $p_row['isys_obj__id'], $p_cat_dao->get_category_const())
        )
        {
            $this->m_recorded_unallowed_objects[$p_row['isys_obj__id']] = $l_title . ' (' . _L(
                    $p_cat_dao->get_objtype_name_by_id_as_string($p_cat_dao->get_objTypeID($p_row['isys_obj__id']))
                ) . ')';

            return '';
        } // if

        $l_list = '<tr class="' . ($p_iterator % 2 == 0 ? 'CMDBListElementsEven' : 'CMDBListElementsOdd') . '" data-category-id="' . $p_data_id . '" style="cursor:default;">';

        if ($p_template === false)
        {
            /* Show object title if category is not the global one */
            $l_list .= '<td title="' . $l_title . '">' . '<input type="hidden" name="category_data[' . $p_data_id . '-' . $p_row['isys_obj__id'] . ']" value="' . $p_data_id . '" /> ' . '<input type="hidden" name="sort-dummy" value="' . $l_title . '" /> ' . isys_glob_str_stop(
                    $l_title,
                    35
                ) . '</td>';
        }
        else
        {
            $l_list .= '<td >' . '<input type="hidden" name="category_data[' . $p_row[$p_source_table . '__id'] . ']" value="' . $p_row[$p_source_table . '__id'] . '" /> ';

            $l_objects = $p_row['objects'];
            $l_arr     = ['-1' => 'Alle Objekte'];

            foreach ($l_objects AS $l_obj_id)
            {
                $l_arr[$l_obj_id] = $this->m_dao->get_obj_name_by_id_as_string($l_obj_id);
            } // foreach

            $l_params = [
                'chosen'            => 1,
                'p_arData'          => serialize($l_arr),
                'p_bDbFieldNN'      => true,
                'p_bSort'           => false,
                'p_strSelectedID'   => '-1',
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'normal',
                'p_bEnableMetaMap'  => 0,
                'name'              => 'C__MULTIEDIT__NEW_ENTRIES[' . $this->m_iterator . ']'
            ];

            $l_list .= $this->m_smarty_plugins[C__PROPERTY__UI__TYPE__DIALOG]->navigation_edit($g_comp_template, $l_params);

            $l_list .= '</td>';
        } // if

        /* Mark missing category entries with "new-" prefix */
        if (!isset($p_data_id))
        {
            $p_data_id = 'new';
        }

        /* Iterate through properties */
        foreach ($p_properties as $l_prop_id => $l_propdata)
        {
            $l_list .= $this->render_cell_custom_fields($l_propdata, $p_row, $p_smarty_params, $l_prop_id, $p_data_id, $p_template);
        }
        $l_list .= '</tr>';

        return $l_list;
    } // function

    /**
     * @param   array    $p_propdata
     * @param   array    $p_row
     * @param   array    $p_smarty_params
     * @param   string   $p_prop_key
     * @param   mixed    $p_data_id
     * @param   boolean  $p_template
     *
     * @param   boolean  $p_template
     *
     * @return  string
     * @throws  \idoit\Exception\JsonException
     */
    private function render_cell_custom_fields($p_propdata = [], $p_row = [], $p_smarty_params = [], $p_prop_key = '', $p_data_id = null, $p_template = false)
    {
        $l_list = '';

        // Get plugin name.
        if ($p_data_id != 'skip' && isset($p_row['isys_obj__id']))
        {
            $l_name = $p_prop_key . '[' . $p_data_id . '-' . $p_row['isys_obj__id'] . ']';
        }
        elseif ($p_template === true)
        {
            $l_name = $p_prop_key . '[' . $p_data_id . $this->m_iterator . ']';

        }
        else
        {
            $l_name = $p_prop_key . '[skip]';
        } // if

        if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION] > 0 && $p_data_id != 'skip')
        {
            $l_strValue = [];
            if (isset($p_row[$p_prop_key]))
            {
                foreach ($p_row[$p_prop_key] AS $l_data)
                {
                    $l_strValue[] = $l_data['isys_catg_custom_fields_list__field_content'];
                } // foreach
            } // if
            $l_strValue = isys_format_json::encode($l_strValue);

            $l_plugin_params = [
                'name'              => $l_name,
                'p_onChange'        => "Multiedit.changed(this);Multiedit.changesInEntry('" . $p_data_id . "');",
                'p_bInfoIconSpacer' => false,
                'editMode'          => true,
                'p_strClass'        => $p_prop_key,
                'p_strValue'        => $l_strValue
            ];
        }
        else
        {
            $l_plugin_params = [
                'name'              => $l_name,
                'p_onChange'        => "Multiedit.changed(this);Multiedit.changesInEntry('" . $p_data_id . "');",
                'p_bInfoIconSpacer' => false,
                'editMode'          => true,
                'p_strClass'        => $p_prop_key,
                'p_strValue'        => $p_row[$p_prop_key]['isys_catg_custom_fields_list__field_content'],
                'p_strSelectedID'   => $p_row[$p_prop_key]['isys_catg_custom_fields_list__field_content'],
            ];
        }

        // Smarty base param initialization.
        $l_params = array_merge($l_plugin_params, $p_smarty_params);

        switch ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE])
        {
            case 'popup':
            case 'f_popup':
                $l_smarty_plugin            = $this->m_smarty_plugins[C__PROPERTY__UI__TYPE__POPUP];
                $l_params['p_strPopupType'] = ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup'] != 'browser_object') ? $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup'] : 'browser_object_ng';

                if (isset($l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT]))
                {
                    $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] = str_replace(
                        '%%id%%',
                        $p_prop_key,
                        $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT]
                    );
                }
                else
                {
                    $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= "Multiedit.changed();Multiedit.changesInEntry('" . $p_data_id . "');";
                } // if

                if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup'] == 'browser_object')
                {
                    if (isset($l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH]))
                    {
                        $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] = str_replace(
                            '%%id%%',
                            $p_prop_key,
                            $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH]
                        );
                    }
                    else
                    {
                        $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] .= "Multiedit.changed();Multiedit.changesInEntry('" . $p_data_id . "');";
                    } // if

                    if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION])
                    {
                        $l_params[isys_popup_browser_object_ng::C__MULTISELECTION] = 1;
                    } // if
                }
                elseif ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup'] == 'dialog_plus')
                {
                    if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'])
                    {
                        if (isys_format_json::is_json_array($l_params['p_strValue']))
                        {
                            $l_params['p_strValue'] = implode(',', isys_format_json::decode($l_params['p_strValue']));
                        } // if

                        $l_params['p_strSelectedID'] = $l_params['p_strValue'];
                        $l_params['p_multiple']      = true;
                        $l_params['chosen']          = true;
                        $l_params['p_bDbFieldNN']    = true;
                    } // if

                    $l_params['p_strTable']   = 'isys_dialog_plus_custom';
                    $l_params['p_identifier'] = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['identifier'];
                    $l_params['condition']    = 'isys_dialog_plus_custom__identifier = \'' . $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['identifier'] . '\'';
                }
                elseif ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup'] == 'calendar')
                {
                    $l_params['cellCallback'] = 'function(){$(this._relative).simulate(\'change\');}';
                } // if
                break;

            case 'dialog':
            case 'f_dialog':
                $l_smarty_plugin = $this->m_smarty_plugins[C__PROPERTY__UI__TYPE__DIALOG];
                if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['extra']))
                {
                    $l_params['p_bDbFieldNN'] = 1;
                    // Yes-No Dialog
                    $l_params['p_arData'] = [
                        'LC__UNIVERSAL__YES' => _L('LC__UNIVERSAL__YES'),
                        'LC__UNIVERSAL__NO'  => _L('LC__UNIVERSAL__NO')
                    ];
                }
                else if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
                {
                    $l_params['p_arData'] = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                } // if
                break;

            case 'text':
            case 'f_link':
            case 'f_text':
            default:
                $l_smarty_plugin = $this->m_smarty_plugins[C__PROPERTY__UI__TYPE__TEXT];
                break;
        } // switch

        // Set size, so that every form field looks same and is not too big.
        $l_params['p_strStyle'] = 'width:' . isys_tenantsettings::get('cmdb.multiedit.text-size-in-px', '140') . 'px;';

        // Set some further defaults.
        if ($p_data_id == 'skip')
        {
            if (! ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup'] == 'dialog_plus' && $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection']))
            {
                $l_params['p_strSelectedID'] = '-1';
                $l_params['p_bDbFieldNN']    = 0;
            } // if
        } // if

        if (method_exists($l_smarty_plugin, 'navigation_edit'))
        {
            $l_list .= '<td>' . $l_smarty_plugin->navigation_edit(isys_application::instance()->template, $l_params) . '</td>';
        } // if

        return $l_list;
    } // function

    /**
     * Renders a single cell
     *
     * @param array  $p_propdata
     * @param array  $p_row
     * @param string $p_source_table
     * @param array  $p_smarty_params
     * @param array  $p_properties
     *
     * @return string
     */
    private function render_cell($p_propdata = [], $p_row = [], $p_source_table = '', $p_smarty_params = [], $p_properties = [])
    {
        global $g_comp_template, $g_convert;

        $l_list        = '';
        $l_used_plugin = null;

        if (($p_propdata[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && ($p_propdata[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_ng' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_relation' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_cable_connection_ng' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location')))
        {
            $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

            /* Get plugin name */
            if ($p_row[$p_source_table . '__id'] != 'skip' && isset($p_row['isys_obj__id']))
            {
                $l_name = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '[' . $p_row[$p_source_table . '__id'] . '-' . $p_row['isys_obj__id'] . ']';
            }
            else
            {
                $l_name = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '[' . $p_row[$p_source_table . '__id'] . ']';
            }

            // Smarty base param initialization.
            $l_params = array_merge(
                [
                    'name'              => $l_name,
                    'p_onChange'        => "Multiedit.changed(this);Multiedit.changesInEntry('" . $p_row[$p_source_table . '__id'] . "');",
                    'p_bInfoIconSpacer' => false,
                    'editMode'          => true,
                    'p_strValue'        => ((empty($p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) ? $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] : $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]]),
                    'p_strSelectedID'   => ((empty($p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) ? $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] : $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]]),
                ],
                $p_smarty_params
            );

            // Also merge property params.
            if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]) && is_array($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]))
            {
                if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__CALLBACK__ACCEPT]))
                {
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] . ';';
                } // if

                if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_onChange']))
                {
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_onChange'] = 'try{ ' . $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_onChange'] . ' } catch(err) { } ';
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_onChange'] .= $l_params['p_onChange'] . ';';
                } // if

                $l_params = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] + $l_params;
            } // if

            // We set the UI ID to the string instead of appending it. See: ID-1060
            $l_params['p_strClass'] = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID];

            if (isset($p_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION]) && isset($p_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0]) && $p_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0] == 'VALIDATE_BY_TEXTFIELD' && $p_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['as-select'] === true)
            {
                // Change the input field to a dialog box.
                $l_params['force_dialog']      = true;
                $l_params['force_dialog_data'] = explode("\n", $p_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['value']);
            } // if

            /* If we use $p_source_table . '__id' as data field, there is always an helper needed to retrieve the selection,
                so we should unset the value and selected id because a browser tries to select anything wrong then */
            if ($p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] == $p_source_table . '__id' && ($p_source_table != 'isys_catg_connector_list' && $p_source_table == 'isys_catg_application_list'))
            {
                unset($l_params['p_strSelectedID'], $l_params['p_strValue']);
            } // if

            // Set some defaults.
            if ($p_row[$p_source_table . '__id'] == 'skip')
            {
                $l_params['p_strSelectedID'] = '-1';
                $l_params['p_bDbFieldNN']    = 0;

                // Disable first row for calendar.
                if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] == 'C__CATG__LOCATION_POS')
                {
                    return '<td>' . $l_empty_value . '</td>';
                } // if
            } // if

            if (isset($p_smarty_params['p_onChange']))
            {
                $l_params['p_onChange'] = str_replace('Multiedit.overwriteAll(this)', '', rtrim($l_params['p_onChange'], ';')) . ';' . $p_smarty_params['p_onChange'];
            } // if

            $l_param_id = $p_row[$p_source_table . '__id'];
            if (isset($p_row['isys_obj__id']))
            {
                $l_param_id .= '-' . $p_row['isys_obj__id'];
            } // if

            // For global category "model" we have to set the parameter "p_ajaxIdentifier".
            if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] == 'C__CATG__MODEL_MANUFACTURER')
            {
                $l_params['p_ajaxIdentifier'] = $p_row[$p_source_table . '__id'] != 'skip' ? 'C__CATG__MODEL_TITLE_ID\[' . $l_param_id . '\]' : 'C__CATG__MODEL_TITLE_ID\[skip\]';
            } // if

            // For global category "share access" we have to set the parameter "p_ajaxIdentifier".
            if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] == 'C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS')
            {
                $l_id = ($p_row[$p_source_table . '__id'] != 'skip') ? $l_param_id : 'skip';
                $l_js = "window.get_shares_from_object(\$F('C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS__HIDDEN[" . $l_id . "]'), 'C__CATG__SHARE_ACCESS__ASSIGNED_SHARE[" . $l_id . "]');";

                $l_params['p_onChange'] .= $l_js;
                $l_params['callback_accept'] .= $l_js;
                $l_params['callback_detach'] .= $l_js;
            } // if

            if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['secTableID']))
            {
                $l_params['secTableID'] = $p_row[$l_params['secTable'] . '__id'];

                // Only add score if necessary. Prevent producing [skip-].
                $l_params['p_strSecTableIdentifier'] = $l_params['p_strSecTableIdentifier'] . '[' . $p_row[$p_source_table . '__id'];
                if ($p_row[$p_source_table . '__id'] != 'skip' && isset($p_row['isys_obj__id']))
                {
                    $l_params['p_strSecTableIdentifier'] .= '-' . $p_row['isys_obj__id'];
                } // if

                $l_params['p_strSecTableIdentifier'] .= ']';
            } // if

            // Special handling for category location.
            if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] == 'C__CATG__LOCATION_PARENT')
            {
                $l_params['callback_accept'] = str_replace('C__CATG__LOCATION_POS', 'C__CATG__LOCATION_POS[' . $l_param_id . ']', $l_params['callback_accept']);
            } // if

            // Set size that every form field has the same size.
            $l_params['p_strStyle'] = 'min-width:115px;width:68%;';

            // Use callback methods to fill p_arData.
            if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) && is_object(
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']
                ) && is_a($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'], 'isys_callback')
            )
            {
                $l_arData = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute(
                    isys_request::factory()
                        ->set_object_id($p_row['isys_obj__id'])
                        ->set_row($p_row)
                        ->set_object_type_id($p_row['isys_obj_type__id'])
                        ->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL)
                        ->set_category_data_id($p_row[$p_source_table . '__id'])
                );

                $l_params['p_arData'] = (is_array($l_arData)) ? serialize($l_arData) : $l_arData;
            } // if

            // Use callback methods to fill data. This is actualy only needed by multiselect.
            if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['data']) && is_object($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['data']) && is_a(
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['data'],
                    'isys_callback'
                )
            )
            {
                $l_arData = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['data']->execute(
                    isys_request::factory()
                        ->set_object_id($p_row['isys_obj__id'])
                        ->set_row($p_row)
                        ->set_object_type_id($p_row['isys_obj_type__id'])
                        ->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL)
                        ->set_category_data_id($p_row[$p_source_table . '__id'])
                );

                $l_params['data'] = (is_array($l_arData)) ? json_encode($l_arData) : $l_arData;
            } // if

            // Use callback methods to fill p_strValue.
            if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']) && is_object(
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']
                ) && is_a($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue'], 'isys_callback')
            )
            {
                $l_tmp = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strValue']->execute(
                    isys_request::factory()
                        ->set_object_id($p_row['isys_obj__id'])
                        ->set_row($p_row)
                        ->set_object_type_id($p_row['isys_obj_type__id'])
                        ->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL)
                        ->set_category_data_id($p_row[$p_source_table . '__id'])
                );

                // Convert it to JSON if needed.
                if (is_array($l_tmp) || is_object($l_tmp))
                {
                    $l_tmp = isys_format_json::encode($l_tmp);
                } // if

                $l_params["p_strValue"] = $l_tmp;
                unset($l_params["p_strSelectedID"]);
            }

            // Use callback methods to fill p_strSelectedID.
            if (isset($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']) && is_object(
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']
                ) && is_a($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID'], 'isys_callback')
            )
            {
                $l_tmp = $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strSelectedID']->execute(
                    isys_request::factory()
                        ->set_object_id($p_row['isys_obj__id'])
                        ->set_row($p_row)
                        ->set_object_type_id($p_row['isys_obj_type__id'])
                        ->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL)
                        ->set_category_data_id($p_row[$p_source_table . '__id'])
                );

                /* Convert it to JSON if needed */
                if (is_array($l_tmp) || is_object($l_tmp))
                {
                    $l_tmp = isys_format_json::encode($l_tmp);
                }

                $l_params["p_strSelectedID"] = $l_tmp;
                unset($l_params["p_strValue"]);
            }

            if (isset($this->m_smarty_plugins[$p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]]))
            {
                $l_used_plugin = $this->m_smarty_plugins[$p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]];
            } // if

            if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__TEXT)
            {
                /* Convert content */
                if ($p_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK])
                {
                    switch ($p_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])
                    {
                        case 'convert':
                            if (class_exists($p_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) && $l_params['p_strValue'] != '')
                            {
                                if ($p_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0])
                                {
                                    $l_convert_method = $p_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0];
                                    if (method_exists($g_convert, $l_convert_method))
                                    {
                                        $l_params['p_strValue'] = $g_convert->$l_convert_method(
                                            $l_params['p_strValue'],
                                            $p_row[$p_properties[$p_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]],
                                            C__CONVERT_DIRECTION__BACKWARD
                                        );
                                    }
                                }
                            }
                            break;
                        case 'get_ip_reference':
                            switch ($p_row['isys_catg_ip_list__isys_net_type__id'])
                            {
                                case C__CATS_NET_TYPE__IPV4:
                                    if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] != 'C__CATP__IP__ADDRESS_V4')
                                    {
                                        $p_row['isys_cats_net_ip_addresses_list__title'] = null;
                                        $l_params['p_bDisabled']                         = 'disabled';
                                    } // if
                                    break;
                                case C__CATS_NET_TYPE__IPV6:
                                    if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID] != 'C__CMDB__CATG__IP__IPV6_ADDRESS')
                                    {
                                        $p_row['isys_cats_net_ip_addresses_list__title'] = null;
                                        $l_params['p_bDisabled']                         = 'disabled';
                                    } // if
                                    break;
                            } // switch

                            $l_params['p_strValue'] = $p_row['isys_cats_net_ip_addresses_list__title'];
                            break;
                    }
                }

                if (is_object($l_used_plugin) && method_exists($l_used_plugin, 'navigation_edit'))
                {
                    $l_list .= '<td>' . $l_used_plugin->navigation_edit($g_comp_template, $l_params) . '</td>';
                }
                else
                {
                    $l_list .= '<td>' . $l_empty_value . '</td>';
                } // if
            }
            else
            {
                if (isset($l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT])) $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] = str_replace(
                    '%%id%%',
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID],
                    $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT]
                );

                if (isset($l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH])) $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] = str_replace(
                    '%%id%%',
                    $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__ID],
                    $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH]
                );

                switch ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__TYPE])
                {
                    case C__PROPERTY__UI__TYPE__POPUP:

                        if (isset($p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]) && $p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection' && $l_params['p_strValue'] > 0)
                        {
                            $l_params['p_strSelectedID'] = $l_params['p_strValue'] = $this->m_dao_connection->get_object_id_by_connection(
                                (isset($p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]]) ? $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]] : $l_params['p_strValue'])
                            );
                        }
                        elseif (isset($p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]) && $p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] != 'isys_connection' && isset($p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]]) && $l_params['p_strValue'] === null && $l_params['p_strSelectedID'] === null)
                        {
                            $l_params['p_strSelectedID'] = $l_params['p_strValue'] = $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]];
                        } // if

                        if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_ng' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_object_relation' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_cable_connection_ng' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location' || $p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_file')
                        {

                            $l_params[isys_popup_browser_object_ng::C__EDIT_MODE] = true;

                            if(isset($p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && $p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] !== 'isys_connection__id')
                            {
                                $l_params[isys_popup_browser_object_ng::C__SELECTION] = (isset($p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]]) ? $p_row[$p_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]] : $l_params['p_strValue']);
                            }
                            else
                            {
                                $l_params[isys_popup_browser_object_ng::C__SELECTION] = $l_params['p_strValue'];
                            } // if

                            $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= "Multiedit.changed();Multiedit.changesInEntry('" . $p_row[$p_source_table . '__id'] . "');";
                            $l_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] .= "Multiedit.changed();Multiedit.changesInEntry('" . $p_row[$p_source_table . '__id'] . "');";
                        } // if

                        if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus')
                        {
                            if ($p_propdata[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselect'])
                            {
                                $l_params['chosen'] = true;
                            } // if
                            $l_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= "Multiedit.changed();Multiedit.changesInEntry('" . $p_row[$p_source_table . '__id'] . "');";
                        } // if
                        break;

                    case C__PROPERTY__UI__TYPE__DATE:
                        $l_params['cellCallback'] = 'function(){$(this._relative).simulate(\'change\');}';
                        break;
                }

                // Insert ui params and call navigation_edit on smarty plugin instance.
                if (is_object($l_used_plugin) && method_exists($l_used_plugin, 'navigation_edit'))
                {
                    $l_list .= '<td>' . $l_used_plugin->navigation_edit($g_comp_template, $l_params) . '</td>';
                }
                else
                {
                    $l_list .= '<td>' . $l_empty_value . '</td>';
                } // if
            } // if
        } // if

        return $l_list;
    } // function

    /**
     * This method selects which object id has no assignment to the specified specific category id
     *
     * @param $p_object_ids
     * @param $p_category_id
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function check_objects_with_specific_category($p_object_ids, $p_category_id)
    {
        $l_check_sql = 'SELECT isys_obj__id FROM isys_obj
				INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj_type__id = isys_obj__isys_obj_type__id
			AND isys_obj__id IN (' . implode(',', $p_object_ids) . ')
			AND (isys_obj_type__isysgui_cats__id NOT IN
					(SELECT isysgui_cats_2_subcategory__isysgui_cats__id__parent
						FROM isysgui_cats_2_subcategory
						WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child = ' . $p_category_id . ')
				AND isys_obj_type__isysgui_cats__id != ' . $p_category_id . ')';

        $l_res = $this->m_dao->retrieve($l_check_sql);
        $l_arr = [];
        if ($l_res->num_rows() > 0)
        {
            // Object type does not have this category
            while ($l_row = $l_res->get_row())
            {
                $l_arr[] = $l_row['isys_obj__id'];
            }
        }

        return $l_arr;
    } // function

    /**
     * This method selects which object id has no assignment to the specified global category id.
     *
     * @param   array   $p_object_ids
     * @param   integer $p_category_id
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function check_objects_with_global_category($p_object_ids, $p_category_id)
    {
        $l_check_sql = 'SELECT DISTINCT(isys_obj__id) FROM isys_obj
				INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
				INNER JOIN isys_obj_type_2_isysgui_catg ON isys_obj_type__id = isys_obj_type_2_isysgui_catg__isys_obj_type__id
			WHERE isys_obj__id IN (' . implode(',', $p_object_ids) . ')
			AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type__id
			AND (isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->m_dao->convert_sql_id($p_category_id) . '
				OR isys_obj_type_2_isysgui_catg__isysgui_catg__id = (SELECT isysgui_catg__parent FROM isysgui_catg WHERE isysgui_catg__id = ' . $this->m_dao->convert_sql_id(
                $p_category_id
            ) . '));';

        $l_res = $this->m_dao->retrieve($l_check_sql);

        if (count($l_res))
        {
            // Object type does not have this category
            while ($l_row = $l_res->get_row())
            {
                unset($p_object_ids[array_search($l_row['isys_obj__id'], $p_object_ids)]);
            } // while
        } // if

        return $p_object_ids;
    } // function

    /**
     *
     * @param   array   $p_object_ids
     * @param   integer $p_categoy_id
     *
     * @return  mixed
     */
    private function check_object_with_custom_category($p_object_ids, $p_categoy_id)
    {
        $l_check_sql = 'SELECT DISTINCT(isys_obj__id) FROM isys_obj
				INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
				INNER JOIN isys_obj_type_2_isysgui_catg_custom ON isys_obj_type__id = isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id
			WHERE isys_obj__id IN (' . implode(',', $p_object_ids) . ')
			AND isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = isys_obj_type__id
			AND isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = ' . $this->m_dao->convert_sql_id($p_categoy_id);

        $l_res = $this->m_dao->retrieve($l_check_sql);

        if (count($l_res))
        {
            // Object type does not have this category.
            while ($l_row = $l_res->get_row())
            {
                unset($p_object_ids[array_search($l_row['isys_obj__id'], $p_object_ids)]);
            } // while
        } // if

        return $p_object_ids;
    } // function

    /**
     * Constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class