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
 * @package    i-doit
 * @subpackage Reports
 * @author     Van Quyen Hoang <qhoang@synetics.de>
 * @copyright  Copyright 2011 - synetics GmbH
 */
class isys_report_view_relation_it_service extends isys_report_view
{

    /**
     * @var int
     */
    private $m_counter = 0;
    /**
     * @var isys_cmdb_dao_category_g_it_service_components|null
     */
    private $m_dao_its_comp = null;
    /**
     * @var isys_cmdb_dao_category_g_relation|null
     */
    private $m_dao_rel = null;
    /**
     * @var array
     */
    private $m_ids_arr = [];
    /**
     * @var array
     */
    private $m_inconsistent_arr = [];
    /**
     * @var array
     */
    private $m_its_arr = [];
    /**
     * @var array
     */
    private $m_obj_arr = [];
    /**
     * @var array
     */
    private $m_software_obj_types;
    /**
     * @var array
     */
    private $m_unset_arr = [];

    /**
     * Gets it service list
     *
     * @param int $p_obj_id
     *
     * @return array
     */
    public function show_list($p_obj_id)
    {

        $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id__master, isys_catg_relation_list__isys_obj__id__slave, obj.isys_obj__isys_obj_type__id, obj.isys_obj__title AS title FROM isys_catg_relation_list ' . 'INNER JOIN isys_obj AS obj ON isys_catg_relation_list__isys_obj__id__slave = obj.isys_obj__id ' . 'INNER JOIN isys_obj AS relObj ON isys_catg_relation_list__isys_obj__id = relObj.isys_obj__id ' . 'WHERE isys_catg_relation_list__isys_obj__id__master = ' . $this->m_dao_rel->convert_sql_id(
                $p_obj_id
            ) . ' ' . 'AND obj.isys_obj__status = ' . $this->m_dao_rel->convert_sql_int(
                C__RECORD_STATUS__NORMAL
            ) . ' ' . 'AND isys_catg_relation_list__status = ' . $this->m_dao_rel->convert_sql_int(
                C__RECORD_STATUS__NORMAL
            ) . ' ' . 'AND relObj.isys_obj__status = ' . $this->m_dao_rel->convert_sql_int(
                C__RECORD_STATUS__NORMAL
            ) . ' ' . 'GROUP BY isys_catg_relation_list__isys_obj__id__slave ';

        $l_res = $this->m_dao_rel->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {

            if ($l_row["isys_catg_relation_list__isys_obj__id__master"] == $p_obj_id && (is_null($this->m_obj_arr) || !in_array(
                        $l_row["isys_catg_relation_list__isys_obj__id__slave"],
                        $this->m_obj_arr
                    ))
            )
            {

                if ($l_row["isys_obj__isys_obj_type__id"] == C__OBJTYPE__IT_SERVICE)
                {
                    $this->m_its_arr[$l_row["isys_catg_relation_list__isys_obj__id__slave"]] = $l_row["title"];
                }

                if (in_array($this->m_dao_rel->get_objTypeID($l_row['isys_catg_relation_list__isys_obj__id__slave']), $this->m_software_obj_types))
                {

                    $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id ' . 'FROM isys_catg_relation_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id ' . 'WHERE isys_catg_relation_list__isys_obj__id__master = ' . $this->m_dao_rel->convert_sql_id(
                            $p_obj_id
                        ) . ' ' . 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->m_dao_rel->convert_sql_id(
                            $l_row['isys_catg_relation_list__isys_obj__id__slave']
                        ) . ' ' . 'AND isys_obj__status = ' . $this->m_dao_rel->convert_sql_int(
                            C__RECORD_STATUS__NORMAL
                        ) . ' ' . 'AND isys_catg_relation_list__status = ' . $this->m_dao_rel->convert_sql_int(
                            C__RECORD_STATUS__NORMAL
                        ) . ' ' . 'GROUP BY isys_catg_relation_list__isys_obj__id';

                    $l_res_app = $this->m_dao_rel->retrieve($l_sql);

                    while ($l_row_app = $l_res_app->get_row())
                    {
                        $l_its_data = $this->m_dao_its_comp->get_data(
                            null,
                            null,
                            "AND isys_connection__isys_obj__id = " . $this->m_dao_rel->convert_sql_id($l_row_app["isys_catg_relation_list__isys_obj__id"]),
                            null,
                            C__RECORD_STATUS__NORMAL
                        )
                            ->get_row();

                        // Check in it service components
                        if (isset($l_its_data['isys_obj__id']) && $this->m_dao_rel->get_objTypeID($l_its_data['isys_obj__id']) == C__OBJTYPE__IT_SERVICE)
                        {
                            if (!array_key_exists($l_its_data['isys_obj__id'], $this->m_its_arr))
                            {
                                $this->m_its_arr[$l_its_data['isys_obj__id']] = $l_its_data['isys_obj__title'];
                            }
                        }
                    }
                }

                $this->m_obj_arr[] = $l_row["isys_catg_relation_list__isys_obj__id__slave"];
                $this->new_recurse_relation($l_row["isys_catg_relation_list__isys_obj__id__slave"], $p_obj_id);
                unset($this->m_obj_arr);
            }
        }

        return $this;
    }

    /**
     *
     */
    public function ajax_request()
    {
        ;
    }

    /**
     * @return string
     */
    public static function description()
    {
        return "LC__REPORT__VIEW__OBJECT_RELATION_TO_ITS__DESCRIPTION";
    }

    /**
     * @return bool
     */
    public function init()
    {
        /* initialization */

        return true;
    }

    /**
     * @return string
     */
    public static function name()
    {
        return "LC__REPORT__VIEW__OBJECT_RELATION_TO_ITS__TITLE";
    }

    /**
     * SHOW LIST
     */
    public function start()
    {
        global $g_comp_template, $g_dirs;

        $l_request = isys_glob_get_param("request");

        if ($l_request == "show_list")
        {

            $l_obj_id = isys_glob_get_param(C__CMDB__GET__OBJECT);

            $this->show_list($l_obj_id);

            $l_table_content = $this->prepare_table();
            echo $l_table_content;
            die;
        }

        if ($l_request == "get_path")
        {
            $l_obj_id           = isys_glob_get_param(C__CMDB__GET__OBJECT);
            $l_obj_id_itservice = isys_glob_get_param("itservice");

            $l_table_content = $this->prepare_path($l_obj_id_itservice, $l_obj_id);

            if ($l_table_content)
            {
                echo $l_table_content;
            }
            else
            {
                echo _L('LC__REPORT__VIEW__NO_OTHER_RELATION_PATHS_FOUND');
            }
            die;
        }

        $g_comp_template->assign("dir_images", $g_dirs["images"]);

        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $g_comp_template->assign("dir_images", $g_dirs["images"]);
        $g_comp_template->assign("image_dir", $g_dirs["images"] . "dtree/");
    }

    /**
     * @return string
     */
    public function template()
    {
        return "view_relation_it_service.tpl";
    }

    /**
     * @return int
     */
    public static function type()
    {
        return self::c_php_view;
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return "LC__CMDB__CATG__IT_SERVICE";
    }

    /**
     * @return array
     */
    public function get_it_services()
    {
        return $this->m_its_arr;
    }

    /**
     * @param $p_obj_id
     * @param $p_root_obj_id
     *
     * @return $this
     * @throws Exception
     * @throws isys_exception_database
     */
    private function new_recurse_relation($p_obj_id, $p_root_obj_id)
    {

        $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id__master, isys_catg_relation_list__isys_obj__id__slave, isys_catg_relation_list__isys_obj__id, slaObj.isys_obj__title AS title ' . 'FROM isys_catg_relation_list ' . 'INNER JOIN isys_obj relObj ON relObj.isys_obj__id = isys_catg_relation_list__isys_obj__id ' . 'INNER JOIN isys_obj slaObj ON slaObj.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave ' . 'WHERE isys_catg_relation_list__isys_obj__id__master = ' . $this->m_dao_rel->convert_sql_id(
                $p_obj_id
            ) . ' ' . 'AND isys_catg_relation_list__isys_obj__id__slave != ' . $this->m_dao_rel->convert_sql_id(
                $p_root_obj_id
            ) . ' ' . 'AND relObj.isys_obj__status = ' . $this->m_dao_rel->convert_sql_int(
                C__RECORD_STATUS__NORMAL
            ) . ' ' . 'AND isys_catg_relation_list__status = ' . $this->m_dao_rel->convert_sql_int(
                C__RECORD_STATUS__NORMAL
            ) . ' ' . 'GROUP BY isys_catg_relation_list__isys_obj__id';

        $l_res = $this->m_dao_rel->retrieve($l_sql);

        if ($l_res->num_rows() > 0)
        {
            while ($l_row = $l_res->get_row())
            {

                if (!in_array($l_row['isys_catg_relation_list__isys_obj__id__slave'], $this->m_obj_arr))
                {
                    if ($this->m_dao_rel->get_objTypeID($l_row['isys_catg_relation_list__isys_obj__id__slave']) == C__OBJTYPE__IT_SERVICE)
                    {
                        if (!array_key_exists($l_row['isys_catg_relation_list__isys_obj__id__slave'], $this->m_its_arr))
                        {
                            $this->m_its_arr[$l_row['isys_catg_relation_list__isys_obj__id__slave']] = $l_row['title'];
                        }

                    }
                    else
                    {

                        if (in_array($this->m_dao_rel->get_objTypeID($l_row['isys_catg_relation_list__isys_obj__id__slave']), $this->m_software_obj_types))
                        {

                            $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id ' . 'FROM isys_catg_relation_list ' . 'INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id ' . 'WHERE isys_catg_relation_list__isys_obj__id__master = ' . $this->m_dao_rel->convert_sql_id(
                                    $p_obj_id
                                ) . ' ' . 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->m_dao_rel->convert_sql_id(
                                    $l_row['isys_catg_relation_list__isys_obj__id__slave']
                                ) . ' ' . 'AND isys_obj__status = ' . $this->m_dao_rel->convert_sql_int(
                                    C__RECORD_STATUS__NORMAL
                                ) . ' ' . 'AND isys_catg_relation_list__status = ' . $this->m_dao_rel->convert_sql_int(
                                    C__RECORD_STATUS__NORMAL
                                ) . ' ' . 'GROUP BY isys_catg_relation_list__isys_obj__id';

                            $l_res_app = $this->m_dao_rel->retrieve($l_sql);

                            while ($l_row_app = $l_res_app->get_row())
                            {
                                $l_its_data = $this->m_dao_its_comp->get_data(
                                    null,
                                    null,
                                    "AND isys_connection__isys_obj__id = " . $this->m_dao_rel->convert_sql_id($l_row_app["isys_catg_relation_list__isys_obj__id"]),
                                    null,
                                    C__RECORD_STATUS__NORMAL
                                )
                                    ->get_row();

                                // Check in it service components
                                if (isset($l_its_data['isys_obj__id']) && $this->m_dao_rel->get_objTypeID($l_its_data['isys_obj__id']) == C__OBJTYPE__IT_SERVICE)
                                {
                                    if (!array_key_exists($l_its_data['isys_obj__id'], $this->m_its_arr))
                                    {
                                        $this->m_its_arr[$l_its_data['isys_obj__id']] = $l_its_data['isys_obj__title'];
                                    }
                                }
                            }
                        }

                        $this->m_obj_arr[] = $l_row['isys_catg_relation_list__isys_obj__id__slave'];
                        $this->new_recurse_relation($l_row['isys_catg_relation_list__isys_obj__id__slave'], $p_root_obj_id);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepares it service list
     *
     * @param array $p_arr
     * @param int   $p_obj_id
     *
     * @return string
     */
    private function prepare_table()
    {
        global $g_comp_database, $g_dirs;

        $l_dao    = new isys_cmdb_dao($g_comp_database);
        $l_quicky = new isys_ajax_handler_quick_info();

        $l_table = "<table width=\"100%\" class=\"report_listing\">";

        foreach ($this->m_its_arr AS $l_obj_id => $l_title)
        {
            unset($this->m_obj_arr);
            $l_table .= "<tr style=\"\"><td onclick=\"collapse_it_service('" . $l_obj_id . "')\" id=\"" . $l_obj_id . "\" class=\"report_listing\"><img id=\"" . $l_obj_id . "_plusminus\" src=\"" . $g_dirs["images"] . "dtree/nolines_plus.gif\" class=\"vam\">";

            $l_table .= $l_quicky->get_quick_info($l_obj_id, $l_dao->get_obj_name_by_id_as_string($l_obj_id), C__LINK__OBJECT);

            $l_table .= "<img src=\"" . $g_dirs["images"] . "ajax-loading.gif\" id=\"ajax_loading_view_" . $l_obj_id . "\" style=\"display:none;\" class=\"vam\" /></td></tr>";

            $l_table .= "<tr id=\"childs_" . $l_obj_id . "\" style=\"display:none;\"><td><div id=\"childs_content_" . $l_obj_id . "\"></div>";
            $l_table .= "</td></tr>";

        }

        $l_table .= "</table>";

        return $l_table;
    }

    /**
     * Prepares relation paths
     *
     * @param int $p_obj_id__slave
     * @param int $p_obj_id
     *
     * @return string
     */
    private function prepare_path($p_obj_id__slave, $p_obj_id)
    {
        $l_res = $this->m_dao_rel->get_data(null, null, "AND isys_catg_relation_list__isys_obj__id__slave = '" . $p_obj_id__slave . "'", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_res->get_row())
        {

            if (is_null($this->m_obj_arr) || !in_array(
                    $l_row["isys_catg_relation_list__isys_obj__id__master"],
                    $this->m_obj_arr
                ) && $l_row["isys_catg_relation_list__isys_obj__id__master"] != $p_obj_id
            )
            {
                $this->m_obj_arr[] = $l_row["isys_catg_relation_list__isys_obj__id__master"];

                $l_arr[$l_row["isys_catg_relation_list__isys_obj__id__master"]] = $this->check_path($l_row["isys_catg_relation_list__isys_obj__id__master"], $p_obj_id);

                unset($this->m_obj_arr);
            }
        }

        if (!isset($l_arr))
        {
            $l_arr = [$p_obj_id => true];
        }

        $l_arr = $this->filter_array($l_arr);

        $l_table_content = $this->build_path($l_arr, $p_obj_id, 0);

        return $l_table_content;
    }

    /**
     * Checks relation path
     *
     * @param int $p_obj_id_master
     * @param int $p_obj_id
     *
     * @return array
     */
    private function check_path($p_obj_id_master, $p_obj_id)
    {
        $l_arr = [];

        $l_res = $this->m_dao_rel->get_data(null, null, "AND isys_catg_relation_list__isys_obj__id__slave = '" . $p_obj_id_master . "'", null, C__RECORD_STATUS__NORMAL);

        if (!in_array($p_obj_id_master, $this->m_obj_arr)) $this->m_obj_arr[] = $p_obj_id_master;

        while ($l_row = $l_res->get_row())
        {
            if ($l_row["isys_catg_relation_list__isys_obj__id__master"] == $p_obj_id)
            {
                $l_arr[$l_row["isys_catg_relation_list__isys_obj__id__master"]] = true;
            }
            else
            {
                if (!in_array(
                    $l_row["isys_catg_relation_list__isys_obj__id__master"],
                    $this->m_obj_arr
                )
                ) $l_arr[$l_row["isys_catg_relation_list__isys_obj__id__master"]] = $this->check_path($l_row["isys_catg_relation_list__isys_obj__id__master"], $p_obj_id);
            }
        }

        if ($this->m_dao_rel->get_objTypeID($p_obj_id_master) == C__OBJTYPE__RELATION)
        {

            $l_its_data = $this->m_dao_rel->get_data(null, null, "AND isys_catg_relation_list__isys_obj__id = '" . $p_obj_id_master . "'")
                ->get_row();

            if (!in_array($l_its_data['isys_catg_relation_list__isys_obj__id__master'], $this->m_obj_arr))
            {
                if ($l_its_data['isys_catg_relation_list__isys_obj__id__master'] == $p_obj_id) $l_arr[$l_its_data["isys_catg_relation_list__isys_obj__id__master"]] = true;
                else
                    $l_arr[$l_its_data['isys_catg_relation_list__isys_obj__id__master']] = $this->check_path(
                        $l_its_data['isys_catg_relation_list__isys_obj__id__master'],
                        $p_obj_id
                    );
            }
        }

        return $l_arr;
    }

    /**
     * Builds relation paths
     *
     * @param array $p_arr
     * @param int   $p_root_obj
     * @param int   $p_tab
     *
     * @return string
     */
    private function build_path($p_arr, $p_root_obj, $p_tab)
    {
        global $g_comp_database, $g_dirs;

        $l_dao    = new isys_cmdb_dao_status($g_comp_database);
        $l_quicky = new isys_ajax_handler_quick_info();
        $l_table  = '';

        if (is_array($p_arr))
        {
            foreach ($p_arr AS $l_root_obj => $l_value)
            {

                $l_object             = $l_dao->get_object_by_id($l_root_obj)
                    ->get_row();
                $l_object_cmdb_status = $l_dao->get_cmdb_status_as_array($l_object["isys_obj__isys_cmdb_status__id"]);
                $l_object_color       = $l_object_cmdb_status["isys_cmdb_status__color"];

                if ($p_tab == 0)
                {
                    $l_table .= "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;spacing:0px;\">";
                    $l_table .= "<tr><td>" . _L("LC__UNIVERSAL__PATHS") . "</td></tr>";
                }
                else
                {
                    $l_table .= "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;spacing:0px;\">";
                }
                $l_table .= "<tr>";
                $l_table .= "<td valign=\"top\" style=\"vertical-align:middle;border:2px solid #" . $l_object_color . ";background-color:#FFFFFF;background-color:#FFFFFF;background-image:url('" . $g_dirs["images"] . "verlauf1.png');background-repeat:repeat-x;\">";
                $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");
                $l_table .= $l_quicky->get_quick_info($l_root_obj, $l_dao->get_obj_name_by_id_as_string($l_root_obj), C__LINK__OBJECT) . "<br>";
                $l_table .= "<span style=\"padding-left:5px;padding-right:5px;\">(" . _L(
                        $l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_root_obj))
                    ) . ")</span>";

                $l_table .= "</td>";
                $p_tab++;

                if ($l_object["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IN_OPERATION && $l_object["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS && $l_object["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE)
                {

                    $this->m_inconsistent_arr[$l_object["isys_obj__id"]] = _L($l_object_cmdb_status["isys_cmdb_status__title"]);
                }

                if (is_array($l_value))
                {
                    $l_table .= "<td style=\"\">";
                    $l_table .= $this->build_path_child($l_value, $p_root_obj, $p_tab);
                    $l_table .= "</td>";
                }

                $l_table .= "</tr>";
                $l_table .= "</table><br>";

                if (!is_null($this->m_inconsistent_arr) && count($this->m_inconsistent_arr) > 0)
                {
                    $l_table .= _L("LC__REPORT__VIEW__INCONSISTENCY_IN") . ": ";
                    unset($l_inconsistence);
                    $l_inconsistence = "<p style=\"text-align:justify\">";
                    foreach ($this->m_inconsistent_arr AS $l_obj_id => $l_cmdb_status)
                    {
                        $l_inconsistence .= $l_quicky->get_quick_info(
                                $l_obj_id,
                                $l_dao->get_obj_name_by_id_as_string($l_obj_id),
                                C__LINK__OBJECT
                            ) . " [<b>" . $l_cmdb_status . "</b>], ";
                    }

                    $l_table .= substr($l_inconsistence, 0, -2) . "</p>";
                }
                $l_table .= "<br><hr><br>";

                unset($this->m_inconsistent_arr);
            }
        }

        return $l_table;
    } // function

    /**
     * Recursive function to build relation path
     *
     * @param array $p_arr
     * @param int   $p_root_obj
     * @param int   $p_tab
     *
     * @return string
     */
    private function build_path_child($p_arr, $p_root_obj, $p_tab)
    {
        global $g_comp_database;
        global $g_dirs;

        $l_dao    = new isys_cmdb_dao_status($g_comp_database);
        $l_quicky = new isys_ajax_handler_quick_info();
        $l_table  = '';

        foreach ($p_arr AS $l_root_obj => $l_value)
        {

            $l_object             = $l_dao->get_object_by_id($l_root_obj)
                ->get_row();
            $l_object_cmdb_status = $l_dao->get_cmdb_status_as_array($l_object["isys_obj__isys_cmdb_status__id"]);
            $l_object_color       = $l_object_cmdb_status["isys_cmdb_status__color"];

            $l_table .= "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;\">";

            $l_table .= "<tr>";
            $l_table .= "<td valign=\"top\" style=\"vertical-align:middle;border:2px solid #" . $l_object_color . ";background-color:#FFFFFF;background-image:url('" . $g_dirs["images"] . "verlauf1.png');background-repeat:repeat-x;\">";
            $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");
            $l_table .= "  " . $l_quicky->get_quick_info($l_root_obj, $l_dao->get_obj_name_by_id_as_string($l_root_obj), C__LINK__OBJECT) . "<br>";
            $l_table .= "<span style=\"padding-left:5px;padding-right:5px;\">(" . _L(
                    $l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_root_obj))
                ) . ")</span>";
            $l_table .= "</td>";
            $p_tab++;

            if ($l_object["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IN_OPERATION && $l_object["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS && $l_object["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE)
            {

                $this->m_inconsistent_arr[$l_object["isys_obj__id"]] = _L($l_object_cmdb_status["isys_cmdb_status__title"]);
            }

            if (is_array($l_value))
            {
                $l_table .= "<td style=\"\">";
                $l_table .= $this->build_path_child($l_value, $p_root_obj, $p_tab);
                $l_table .= "</td>";
            }

            $l_table .= "</tr>";
            $l_table .= "</table>";
        }

        return $l_table;
    } // function

    /**
     * Filters array for duplicate objects.
     *
     * @param   array $p_arr
     *
     * @return  array
     */
    private function filter_array($p_arr)
    {
        if (is_array($p_arr))
        {
            foreach ($p_arr AS $l_key => $l_value)
            {
                if (is_array($l_value))
                {
                    $p_arr[$l_key] = $this->filter_array($l_value);
                } // if

                if (is_null($p_arr[$l_key]) || !$p_arr[$l_key])
                {
                    unset($p_arr[$l_key]);
                } // if

                if (count($p_arr) == 0 || is_null($p_arr))
                {
                    unset($p_arr);
                } // if
            } // foreach
        }
        else $p_arr = [];

        return isset($p_arr) ? $p_arr : [];
    } // function

    /**
     *
     */
    public function __construct()
    {
        global $g_comp_database;
        parent::__construct();

        $this->m_dao_rel            = new isys_cmdb_dao_category_g_relation($g_comp_database);
        $this->m_dao_its_comp       = new isys_cmdb_dao_category_g_it_service_components($g_comp_database);
        $this->m_software_obj_types = $this->m_dao_its_comp->get_object_types_by_object_group(C__OBJTYPE_GROUP__SOFTWARE);
    } // function
} // class