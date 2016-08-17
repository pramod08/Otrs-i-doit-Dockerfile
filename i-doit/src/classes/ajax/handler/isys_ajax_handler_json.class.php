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
 * JSON Data Interface
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_json extends isys_ajax_handler
{
    public function init()
    {
        global $g_comp_database;

        $_GET             = $this->m_get;
        $_POST            = $this->m_post;
        $l_condition      = '';
        $l_use_auth = (isset($_GET['useAuth']) && $_GET['useAuth']);
        $l_auth_condition = $l_use_auth ? ' ' . isys_auth_cmdb_objects::instance()->get_allowed_objects_condition() . ' ' : '';

        // Changing the memory limit, if possible/necessary. See ID-793
        $l_current_memory_limit = isys_convert::to_bytes(ini_get('memory_limit'));
        $l_desired_memory_limit = isys_convert::to_bytes(isys_settings::get('system.memory-limit.object-lists', '1024M'));

        if ($l_current_memory_limit < $l_desired_memory_limit)
        {
            ini_set('memory_limit', isys_settings::get('system.memory-limit.object-lists', '1024M'));
        } // if

        header("Content-Type: application/json");
        $l_return = [];

        // With a select structure, you can define which fields you would like to return.
        if (isset($_GET["select"]))
        {
            $l_select = isys_format_json::decode($_GET["select"]);
        }
        else
        {
            $l_select = false;
        } // if

        if (isset($_GET[isys_popup_browser_object_ng::C__CMDB_FILTER]))
        {
            $l_status       = explode(';', $_GET[isys_popup_browser_object_ng::C__CMDB_FILTER]);
            $l_status_array = [];

            foreach ($l_status as $l_cmdb_status)
            {
                if (defined($l_cmdb_status))
                {
                    $l_status_array[] = (int) constant($l_cmdb_status);
                } // if
            } // foreach

            if (count($l_status_array) > 0)
            {
                $l_condition = ' AND isys_obj__isys_cmdb_status__id IN (' . implode(',', $l_status_array) . ') ';
            } // if
        } // if

        switch ($_GET['action'])
        {
            case 'getByGroup':
                if (strlen($_GET[C__CMDB__GET__OBJECT]) > 0)
                {
                    // first retrieve group type
                    $l_group_type_data = isys_cmdb_dao_category_s_group_type::instance(isys_application::instance()->database)
                        ->get_data(null, $_GET[C__CMDB__GET__OBJECT])->get_row();

                    if($l_group_type_data['isys_cats_group_type_list__type'] && $l_group_type_data['isys_cats_group_type_list__isys_report__id'])
                    {
                        $l_report_query = isys_report_dao::instance(isys_application::instance()->database_system)
                            ->get_report($l_group_type_data['isys_cats_group_type_list__isys_report__id'])['isys_report__query'];

                        if ($l_report_query)
                        {
                            $l_sql = 'SELECT obj_main.isys_obj__id AS \'connected_id\',
                                obj_main.isys_obj__title AS \'connected_title\',
                                objtype.isys_obj_type__title AS \'connected_type\',
                                obj_main.isys_obj__sysid AS \'connected_sysid\'';
                            $l_sql .= substr($l_report_query, strpos($l_report_query, 'FROM'), strlen($l_report_query));
                            $l_sql = str_replace(
                                'isys_obj AS obj_main',
                                'isys_obj AS obj_main INNER JOIN isys_obj_type AS objtype ON objtype.isys_obj_type__id = obj_main.isys_obj__isys_obj_type__id ',
                                $l_sql
                            );

                            $l_return = isys_cmdb_dao_category_s_group_type::instance(isys_application::instance()->database)->retrieve($l_sql);
                        } // if
                    }
                    else
                    {
                        $l_return = isys_cmdb_dao_category_s_group::instance(isys_application::instance()->database)
                            ->get_data(null, $_GET[C__CMDB__GET__OBJECT], $l_condition);
                    } // if
                } // if

                break;
            case 'getByPersonGroup':
                if (strlen($_GET[C__CMDB__GET__OBJECT]) > 0)
                {
                    $l_ar_return = [];
                    $l_return = isys_cmdb_dao_category_s_person_group_members::instance(isys_application::instance()->database)
                        ->get_data(null, $_GET[C__CMDB__GET__OBJECT], $l_condition);

                    // Format data.
                    while ($l_row = $l_return->get_row())
                    {
                        $l_rowdata = [];

                        if (is_array($l_select))
                        {
                            foreach ($l_select as $l_key => $l_value)
                            {
                                if ($l_key == "person_type")
                                {
                                    $l_rowdata[$l_value] = _L("LC__CONTACT__TREE__PERSON");
                                }
                                else
                                {
                                    $l_rowdata[_L($l_value)] = _L($l_row[$l_key]);
                                } // if
                            } // foreach
                        } // if

                        $l_ar_return[$l_row['person_id']] = $l_rowdata;
                    } // while

                    $l_return = array_values($l_ar_return);
                    unset($l_ar_return);
                } // if

                break;
            case 'getByTimeCondition':
                $l_dao = new isys_cmdb_dao_category_g_global($g_comp_database);
                switch ($_GET["condition"])
                {
                    case "latest-updated":
                        $l_return = $l_dao->search_objects(
                            "",
                            $_GET["typeFilter"],
                            $_GET["groupFilter"],
                            $l_auth_condition . $l_condition,
                            false,
                            false,
                            "isys_obj__updated DESC",
                            50,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $_GET['catFilter']
                        );
                        break;

                    case "latest-created":
                        $l_return = $l_dao->search_objects(
                            "",
                            $_GET["typeFilter"],
                            $_GET["groupFilter"],
                            $l_auth_condition . $l_condition,
                            false,
                            false,
                            "isys_obj__created DESC",
                            50,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $_GET['catFilter']
                        );
                        break;

                    case "this-month":
                        $l_condition = $l_auth_condition . $l_condition . ' AND (isys_obj__created > \'' . date('Y-m-') . '01\')';
                        $l_return    = $l_dao->search_objects(
                            "",
                            $_GET["typeFilter"],
                            $_GET["groupFilter"],
                            $l_condition,
                            false,
                            false,
                            "isys_obj__created DESC",
                            50,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $_GET['catFilter']
                        );
                        break;

                    case "last-month":
                        $l_condition = $l_auth_condition . $l_condition . ' AND (isys_obj__created < \'' . date('Y-m-') . '01\' AND isys_obj__created > \'' . date(
                                'Y-m-',
                                strtotime('-1 month')
                            ) . '01\')';
                        $l_return    = $l_dao->search_objects(
                            "",
                            $_GET["typeFilter"],
                            $_GET["groupFilter"],
                            $l_condition,
                            false,
                            false,
                            "isys_obj__created DESC",
                            50,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $_GET['catFilter']
                        );
                        break;
                } // switch

                break;
            case 'getByRelationType':
                if ($_GET["type"] > 0)
                {
                    $l_dao = isys_cmdb_dao_category_g_relation::instance($g_comp_database);

                    $l_return = $l_dao->get_data_for_obj_browser_ng(
                        str_replace('isys_obj__id IN', 'relobj.isys_obj__id IN', $l_auth_condition) . $l_condition . " AND isys_relation_type__id = " . $l_dao->convert_sql_id($_GET["type"])
                    );
                } // if

                break;

            case 'getObjectsByReport':

                global $g_comp_database_system;

                $l_id_list   = [];
                $l_report_id = (int) isys_format_json::decode($_GET['request']);

                if ($l_report_id == 0)
                {
                    $l_return = false;
                    break;
                } // if

                $l_report_dao = new isys_report_dao($g_comp_database_system);
                $l_report     = $l_report_dao->get_report($l_report_id);

                $l_sql = $l_report['isys_report__query'];

                if ($l_report_dao->validate_query($l_sql))
                {
                    $l_dao = new isys_cmdb_dao_category_g_global($g_comp_database);
                    $l_res = $l_dao->retrieve($l_sql);

                    if ($l_res->num_rows() > 0)
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            if (isset($l_row['__id__']))
                            {
                                $l_id_list[] = $l_row['__id__'];
                            }
                            else if (isset($l_row['isys_obj__id']))
                            {
                                $l_id_list[] = $l_row['isys_obj__id'];
                            } // if
                        } // while

                        $l_id_list = array_unique($l_id_list);

                        if (count($l_id_list) > 0)
                        {
                            $l_return = $l_dao->get_data(null, null, $l_auth_condition . $l_condition . " AND isys_obj__id IN (" . implode(',', $l_id_list) . ")", null, C__RECORD_STATUS__NORMAL);
                        } // if
                    } // if
                } // if

                break;

            case 'getObjectsByCustomBrowserRequest':

                if (isset($_GET["request"]))
                {
                    $l_request = isys_format_json::decode($_GET["request"]);

                    if (isset($l_request["callFunction"]))
                    {
                        $l_filter = explode("::", $l_request["callFunction"]);

                        if (count($l_filter) > 1)
                        {
                            if (class_exists($l_filter[0]))
                            {
                                $l_filterObject = new $l_filter[0]($g_comp_database);

                                if (method_exists($l_filterObject, $l_filter[1]))
                                {
                                    $l_return = call_user_func(
                                        [
                                            $l_filterObject,
                                            $l_filter[1]
                                        ],
                                        isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST,
                                        $l_request
                                    );
                                }
                            }
                        }
                    }
                }
                break;

            case 'filter':
                if (strlen($_GET["filter"]) > 0)
                {
                    $l_return = isys_cmdb_dao_category_g_global::instance($g_comp_database)->search_objects(urldecode($_GET["filter"]), $_GET["typeFilter"], $_GET["groupFilter"], $l_auth_condition);
                } // if

                break;
            case 'createObject':

                if ($_POST["objectTitle"] && $_POST["objectTypeID"])
                {
                    echo isys_cmdb_dao::instance($g_comp_database)
                        ->create_object($_POST["objectTitle"], $_POST["objectTypeID"]);
                }
                else
                {
                    if (!$_POST["objectTitle"])
                    {
                        isys_notify::error(_L('LC__CMDB__OBJECT_BROWSER__NOTIFY__NO_OBJECT_TITLE'), ['life' => 10]);
                    } // if

                    if (!$_POST["objectTypeID"])
                    {
                        isys_notify::error(_L('LC__CMDB__OBJECT_BROWSER__NOTIFY__NO_OBJECT_TYPE'), ['life' => 10]);
                    } // if

                    echo -1;
                } // if

                die();

                break;
            case 'createObjectGroup':

                if (isset($_POST["objects"]))
                {
                    $l_objects = json_decode($_POST["objects"]);

                    if (isset($_POST["objectTitle"]) && $_POST["objectTitle"])
                    {

                        if (is_array($l_objects) && count($l_objects) > 0)
                        {
                            $l_dao = new isys_cmdb_dao($g_comp_database);

                            if ($_POST["forceOverwrite"] || !$l_dao->get_obj_id_by_title($_POST["objectTitle"], C__OBJECT_TYPE__GROUP))
                            {
                                $l_group_id = $l_dao->create_object($_POST["objectTitle"], C__OBJECT_TYPE__GROUP);

                                $l_dao_group = new isys_cmdb_dao_category_s_group($g_comp_database);

                                foreach ($l_objects as $l_obj)
                                {
                                    $l_dao_group->create($l_group_id, C__RECORD_STATUS__NORMAL, $l_obj, '');
                                }

                                echo $l_group_id;
                            }
                            else
                            {
                                echo json_encode(
                                    [
                                        'exists' => true
                                    ]
                                );
                            }
                        }
                        else
                        {
                            isys_notify::warning(_L('LC__CMDB__OBJECT_BROWSER__OBJECT_GROUP_NO_OBJECTS'));
                        }

                    }
                    else
                    {
                        isys_notify::warning(_L('LC__TEMPLATES__NO_TITLE_GIVEN'));
                    }
                }
                else isys_notify::error('Request error');

                die();

                break;
            case 'insertNewEntry':

                if (!empty($_POST['entryTitle']) && trim($_POST['entryTitle']) != '')
                {
                    $l_dao = new isys_cmdb_dao($g_comp_database);

                    $l_title = str_replace(';', '', $g_comp_database->escape_string($_POST['entryTitle']));
                    $l_table = str_replace(';', '', $g_comp_database->escape_string($_POST['entryTable']));

                    // Extend cases to your dialog tables...
                    switch ($l_table)
                    {
                        default:
                        case 'isys_net_dns_domain':
                        case 'isys_fiber_wave_length':
                            $l_sql = "SELECT * FROM " . $l_table . " WHERE " . $l_table . "__title = " . $l_dao->convert_sql_text($l_title);

                            try
                            {
                                $l_res = $l_dao->retrieve($l_sql);

                                if (count($l_res) == 0)
                                {
                                    $l_sort = $l_dao->retrieve('SELECT MAX(' . $l_table . '__sort) AS sort FROM ' . $l_table . ';')
                                        ->get_row();
                                    $l_sort = $l_sort['sort'] + 1;

                                    $l_insert = "INSERT INTO " . $l_table . " SET
								        " . $l_table . "__title = " . $l_dao->convert_sql_text($l_title) . ",
										" . $l_table . "__sort = " . ($l_dao->convert_sql_int($l_sort)) . ",
										" . $l_table . "__status = " . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL);

                                    if ($l_dao->update($l_insert) && $l_dao->apply_update())
                                    {
                                        echo $l_dao->get_last_insert_id();
                                    }
                                    else
                                    {
                                        echo '0';
                                    } // if
                                } // if
                            }
                            catch (isys_exception_database $e)
                            {
                                echo '0';
                            } // try

                            break;
                    } // switch
                } // if

                die();
                break;

            case 'getRelationsByObjectId':

                $l_result  = [];
                $l_objects = explode(';', $_GET['request']);

                foreach ($l_objects as $l_key => $l_object)
                {
                    $l_relation_dao = new isys_cmdb_dao_category_g_relation($g_comp_database);
                    $l_relation_res = $l_relation_dao->get_data(null, $l_object, $l_condition);

                    $l_obj_name = $l_relation_dao->get_obj_name_by_id_as_string($l_object);

                    // If the object has no name, we need something to display.
                    if (empty($l_obj_name))
                    {
                        $l_obj_name = '(' . _L('LC__UNIVERSAL__NO_TITLE') . ' - ID ' . $l_object . ')';
                    } // if

                    while ($l_relation_row = $l_relation_res->get_row())
                    {
                        $l_return[$l_obj_name][$l_relation_row['isys_relation_type__id']] = '- ' . _L($l_relation_row['isys_relation_type__title']);
                    } // while

                    if (count($l_return[$l_obj_name]) > 0)
                    {
                        sort($l_return[$l_obj_name]);
                    } // if
                } // foreach

                // Because JSON has some problems with utf8 encoded strings as key, we have to return everything as plain array.
                foreach ($l_return as $l_object => $l_categories)
                {
                    $l_result[] = $l_object;

                    foreach ($l_categories as $l_category)
                    {
                        $l_result[] = $l_category;
                    } // foreach

                    // We use this for a nice blank line after each category-list.
                    $l_result[] = '';
                } // foreach

                echo isys_format_json::encode($l_result);
                $this->_die();
                break;

            case 'hasEditRightsByObjectType':
                // Checks if user has edit rights for the selected object type.
                $l_id = $l_constant = null;

                if (is_numeric($_POST['objTypeID']))
                {
                    $l_id = $_POST['objTypeID'];
                }
                else if (is_string($_POST['objTypeID']))
                {
                    $l_constant = $_POST['objTypeID'];
                } // if

                if (empty($_POST['right']))
                {
                    $l_right = 'isys_auth::EDIT';
                }
                else
                {
                    $l_right = $_POST['right'];
                }

                $l_blindly_allow = false;
                $l_objtype       = isys_cmdb_dao::instance($g_comp_database)
                    ->get_object_type($l_id, $l_constant);

                if (($l_id === null && $l_constant === null) || !is_array($l_objtype))
                {
                    // Somehow we did not receive an ID, a constant or a object-type result...
                    $l_blindly_allow = true;
                } // if

                if ($l_blindly_allow || isys_auth_cmdb::instance()
                        ->is_allowed_to(constant($l_right), 'OBJ_IN_TYPE/' . $l_objtype['isys_obj_type__const'])
                )
                {
                    $l_result = [
                        'success' => true,
                        'message' => null
                    ];
                }
                else
                {
                    $l_result = [
                        'success' => false,
                        'message' => _L(
                            'LC__AUTH__EXCEPTION__MISSING_RIGHTS_TO_CREATE_OBJECTTYPE',
                            [
                                _L(isys_auth::get_right_name(constant($l_right))),
                                _L($l_objtype['isys_obj_type__title'])
                            ]
                        )
                    ];
                } // if

                echo isys_format_json::encode($l_result);
                die;

            case 'load_object_data':
                try
                {
                    $l_data    = $l_objects_sort = [];
                    $l_objects = isys_format_json::decode($_POST['objects']);

                    if (count($l_objects) > 0)
                    {
                        $l_res = isys_cmdb_dao_category_g_global::instance($g_comp_database)
                            ->get_data(null, $l_objects);

                        if (count($l_res) > 0)
                        {
                            while ($l_row = $l_res->get_row())
                            {
                                $l_data[$l_row['isys_obj__id']] = [
                                    'id'         => $l_row['isys_obj__id'],
                                    'title'      => $l_row['isys_obj__title'],
                                    'type_title' => _L($l_row['isys_obj_type__title'])
                                ];
                            } // while
                        } // if

                        $l_objects_sort = array_flip($l_objects);
                    } // if

                    // Awesome PHP 5.3 code for sorting the resultset.
                    uksort(
                        $l_data,
                        function ($l_a, $l_b) use ($l_objects_sort)
                        {
                            return $l_objects_sort[$l_a] > $l_objects_sort[$l_b];
                        }
                    );

                    $l_return = [
                        'success' => true,
                        'message' => null,
                        'data'    => array_values($l_data)
                    ];
                }
                catch (Exception $e)
                {
                    $l_return = [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data'    => null
                    ];
                }

                break;

            default:
                /* Process Parameters */
                if ((isset($_GET[C__CMDB__GET__OBJECT]) || isset($_GET["condition"])) && ($_GET[C__CMDB__GET__CATS] || $_GET[C__CMDB__GET__CATG]))
                {
                    // default
                    $l_get_param  = C__CMDB__GET__CATG;
                    $l_cat_suffix = 'g';

                    if ($_GET[C__CMDB__GET__CATS])
                    {
                        $l_get_param  = C__CMDB__GET__CATS;
                        $l_cat_suffix = "s";
                    }
                    else if ($_GET[C__CMDB__GET__CATG])
                    {
                        $l_get_param  = C__CMDB__GET__CATG;
                        $l_cat_suffix = "g";
                    }

                    $l_dao     = new isys_cmdb_dao($g_comp_database);
                    $l_isysgui = $l_dao->get_isysgui("isysgui_cat" . $l_cat_suffix, $g_comp_database->escape_string($_GET[$l_get_param]))
                        ->__to_array();

                    /* Check class and instantiate it */
                    if (class_exists($l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]))
                    {

                        /* Process data */
                        if (($l_cat = new $l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]($g_comp_database)))
                        {

                            if (isset($_GET["method"])) $l_method = "get_" . $_GET["method"];
                            else $l_method = "get_data";

                            if (method_exists($l_cat, $l_method))
                            {
                                $l_condition = $l_auth_condition . ' ' . $l_condition;

                                if (isset($_GET["condition"]))
                                {
                                    $l_return = $l_cat->$l_method(null, null, $l_condition . urldecode($_GET["condition"]));
                                }
                                else
                                {
                                    $l_return = $l_cat->$l_method(null, $g_comp_database->escape_string($_GET[C__CMDB__GET__OBJECT]), $l_condition);
                                }

                            }
                            else
                            {
                                $l_return[] = "Method does not exist";
                            }

                        }
                    }

                }
                else if ($_GET[C__CMDB__GET__OBJECT])
                {
                    // @todo Where is this ever used? Please remove if possible.
                    $l_quicky = new isys_ajax_handler_quick_info();

                    $l_catg = [
                        C__CATG__GLOBAL,
                        C__CATG__CONTACT,
                        C__CATG__MODEL,
                        C__CATG__CPU,
                        C__CATG__NETWORK
                    ];

                    $l_quicky->get_quick_info_content($_GET[C__CMDB__GET__OBJECT], $l_catg);
                    $l_qc = $l_quicky->get_info_array();

                    $l_dao = isys_cmdb_dao_category_g_global::instance(isys_application::instance()->database);

                    $l_return["title"]      = $l_qc['g' . C__CATG__GLOBAL]["Name"];
                    $l_return["objtype"]    = _L($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($_GET[C__CMDB__GET__OBJECT])));
                    $l_return["sysid"]      = $l_qc['g' . C__CATG__GLOBAL]["SYS-ID"];
                    $l_return["model"]      = $l_qc['g' . C__CATG__MODEL]["LC__CMDB__CATG__MODEL_TITLE"];
                    $l_return["cpu_title"]  = $l_qc['g' . C__CATG__CPU]["LC__CMDB__CATG__CPU_TITLE"];
                    $l_return["cpu_type"]   = $l_qc['g' . C__CATG__CPU]["LC__CMDB__CATG__CPU_TYPE"];
                    $l_return["ip_address"] = $l_qc['g' . C__CATG__NETWORK]["LC__CATP__IP__ADDRESS"][0];
                    $l_return["interface"]  = $l_qc['g' . C__CATG__NETWORK]["Interface"][0];
                    $l_return["netmask"]    = $l_qc['g' . C__CATG__NETWORK]["LC__CMDB__CATS__NET__MASK"][0];

                    echo "[" . isys_format_json::encode($l_return) . "]";
                    $this->_die();
                }
                else
                {
                    $l_condition = $l_auth_condition . ' ' . $l_condition;

                    $l_dao  = new isys_cmdb_dao($g_comp_database);
                    $l_data = $l_dao->get_objects_by_type_id($_GET[C__CMDB__GET__OBJECTTYPE], C__RECORD_STATUS__NORMAL, null, '', null, $l_condition);

                    while ($l_row = $l_data->get_row())
                    {
                        if ($l_row["isys_obj__title"] && $l_row["isys_obj__id"])
                        {
                            // Check for a predefined select.
                            if (is_object($l_select) || is_array($l_select))
                            {
                                $l_rowdata = [];

                                foreach ($l_select as $l_key => $l_value)
                                {
                                    $l_rowdata[_L($l_value)] = _L($l_row[$l_key]);
                                } // foreach

                                $l_return[] = $l_rowdata;
                            }
                            else if ($_GET["raw"])
                            {
                                $l_return[] = [
                                    "isys_obj__id"         => $l_row["isys_obj__id"],
                                    "isys_obj__title"      => $l_row["isys_obj__title"],
                                    "isys_obj__sysid"      => $l_row["isys_obj__sysid"],
                                    "isys_obj_type__title" => _L($l_row["isys_obj_type__title"]),
                                ];
                            }
                            else
                            {
                                $l_return[] = [
                                    "id"    => $l_row["isys_obj__id"],
                                    "title" => $l_row["isys_obj__title"],
                                    "sysid" => $l_row["isys_obj__sysid"],
                                    "type"  => $l_row["isys_obj__isys_obj_type__id"]
                                ];
                            } // if
                        } // if
                    } // while
                } // if
                break;
        } // switch

        // Check if the response variable is a dao result to handle the output generically.
        if (is_object($l_return) && is_a($l_return, "isys_component_dao_result"))
        {
            // Format data.
            while ($l_row = $l_return->get_row())
            {
                if (is_object($l_select) || is_array($l_select))
                {
                    $l_rowdata = [];
                    foreach ($l_select as $l_key => $l_value)
                    {
                        $l_rowdata[_L($l_value)] = _L($l_row[$l_key]);
                    } // foreach

                    $l_ar_return[] = $l_rowdata;
                }
                else
                {
                    $l_ar_return[] = array_map("isys_glob_utf8_encode", $l_row);
                } // if
            } // while

            $l_return = &$l_ar_return;
        } // if

        // Return an empty json array if there are no results.
        if (empty($l_return))
        {
            $l_return = [];
        } // if

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function
} // class