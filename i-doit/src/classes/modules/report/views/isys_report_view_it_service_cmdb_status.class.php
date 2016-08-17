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
 * i-doit Report Manager View.
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   Copyright 2011 - synetics GmbH
 */
class isys_report_view_it_service_cmdb_status extends isys_report_view
{
    /**
     * Inconsistence array.
     *
     * @var  array
     */
    private $m_inconsistence = [];
    /**
     * Object array.
     *
     * @var  array
     */
    private $m_obj_arr = [];

    /**
     * Ajax request.
     */
    public function ajax_request()
    {
        if (isys_glob_get_param('request') == 'show_relations')
        {
            echo $this->prepare_table($this->get_its_relations($_POST[C__CMDB__GET__OBJECT]));
            die;
        } // if
    } // function

    /**
     * Method for returning the views description.
     *
     * @static
     * @return  string
     */
    public static function description()
    {
        return "LC__REPORT__VIEW__CMDB_STATUS_CHECK_ON_ITS__DESCRIPTION";
    } // function

    /**
     * @return  boolean
     */
    public function init()
    {
        return true;
    } // function

    /**
     * Method for returning the views name.
     *
     * @static
     * @return  string
     */
    public static function name()
    {
        return "LC__REPORT__VIEW__CMDB_STATUS_CHECK_ON_ITS__TITLE";
    } // function

    /**
     * SHOW LIST
     */
    public function start()
    {
        global $g_comp_template, $g_comp_database, $g_dirs;

        $l_dao = new isys_cmdb_dao_category_g_relation($g_comp_database);

        $l_its_arr = [];
        $l_sql     = 'SELECT * FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id(C__OBJTYPE__IT_SERVICE) . '
			AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IN_OPERATION && $l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS && $l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE)
                {
                    $this->m_inconsistence[$l_row["isys_obj__id"]][$l_row["isys_obj__id"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                } // if

                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_status"] = $l_row["isys_obj__isys_cmdb_status__id"];
                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_color"]  = $l_row["isys_cmdb_status__color"];
                $l_its_arr[$l_row["isys_obj__id"]]["child"]       = $this->recurse_relation($l_row["isys_obj__id"]);

                if (count($this->m_inconsistence[$l_row["isys_obj__id"]]) == 0)
                {
                    unset($l_its_arr[$l_row["isys_obj__id"]]);
                } // if

                $this->m_obj_arr = [];
            } // while
        } // if

        $g_comp_template->assign("image_dir", $g_dirs["images"] . "dtree/")
            ->assign("viewContent", $this->prepare_root($l_its_arr))
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
    } // function

    /**
     * Method for returning the views template name.
     *
     * @static
     * @return  string
     */
    public function template()
    {
        return "view_it_service_cmdb_status.tpl";
    } // function

    /**
     * Method for returning the views type.
     *
     * @static
     * @return  integer
     */
    public static function type()
    {
        return self::c_php_view;
    } // function

    /**
     * Method for returning the views viewtype.
     *
     * @static
     * @return  string
     */
    public static function viewtype()
    {
        return "LC__CMDB__CATG__IT_SERVICE";
    } // function

    /**
     * Gets all relations of the it service object
     *
     * @param int $p_its_obj_id
     *
     * @return array
     */
    private function get_its_relations($p_its_obj_id)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        $l_its_arr = [];
        $l_sql     = 'SELECT * FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id(C__OBJTYPE__IT_SERVICE) . '
			AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__id = ' . $l_dao->convert_sql_id($p_its_obj_id) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IN_OPERATION && $l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS && $l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE)
                {
                    $this->m_inconsistence[$l_row["isys_obj__id"]][$l_row["isys_obj__id"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                } // if

                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_status"] = $l_row["isys_obj__isys_cmdb_status__id"];
                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_color"]  = $l_row["isys_cmdb_status__color"];
                $l_its_arr[$l_row["isys_obj__id"]]["child"]       = $this->recurse_relation($l_row["isys_obj__id"]);

                $this->m_obj_arr = [];
            } // while
        } // if

        return $l_its_arr;
    } // function

    /**
     * Recursive function to iterate through relations
     *
     * @param int $p_obj_id
     * @param int $p_it_service
     *
     * @return array
     */
    private function recurse_relation($p_obj_id, $p_it_service = null)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_g_relation($g_comp_database);

        if ($p_it_service === null)
        {
            $p_it_service = $p_obj_id;
        } // if

        $l_arr = [];
        $l_sql = "SELECT * FROM isys_catg_relation_list
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__master
			LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__status = " . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . " AND isys_catg_relation_list__isys_obj__id__slave = " . $l_dao->convert_sql_id(
                $p_obj_id
            ) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                if (is_null($this->m_obj_arr) || !in_array($l_row["isys_catg_relation_list__isys_obj__id__master"], $this->m_obj_arr))
                {
                    $this->m_obj_arr[] = $p_obj_id;

                    if ($l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IN_OPERATION && $l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS && $l_row["isys_obj__isys_cmdb_status__id"] != C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE)
                    {
                        if (is_null($this->m_inconsistence[$p_it_service]) || !in_array(
                                $l_row["isys_catg_relation_list__isys_obj__id__master"],
                                $this->m_inconsistence[$p_it_service]
                            )
                        )
                        {
                            $this->m_inconsistence[$p_it_service][$l_row["isys_catg_relation_list__isys_obj__id__master"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                        } // if
                    } // if

                    $l_arr[$l_row["isys_obj__id"]]["cmdb_status"] = $l_row["isys_obj__isys_cmdb_status__id"];
                    $l_arr[$l_row["isys_obj__id"]]["cmdb_color"]  = $l_row["isys_cmdb_status__color"];
                    $l_arr[$l_row["isys_obj__id"]]["child"]       = $this->recurse_relation($l_row["isys_catg_relation_list__isys_obj__id__master"], $p_it_service);
                } // if
            } // while
        } // if

        return $l_arr;
    } // function

    /**
     * Prepares it service list as table
     *
     * @param array $p_its_arr
     *
     * @return string
     */
    private function prepare_root($p_its_arr)
    {
        global $g_comp_database, $g_dirs;

        $l_dao    = new isys_cmdb_dao($g_comp_database);
        $l_quicky = new isys_ajax_handler_quick_info();

        $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");

        if (count($p_its_arr))
        {
            $l_return = "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;spacing:0px;\" width=\"100%\" class=\"report_listing\">";

            foreach ($p_its_arr AS $l_obj_id => $l_value)
            {
                $l_return .= "<tr><td onclick=\"collapse_it_service('" . $l_obj_id . "');show_relations('" . $l_obj_id . "');\" class=\"report_listing\" id=\"it_service_" . $l_obj_id . "\">";
                $l_return .= "<img id=\"" . $l_obj_id . "_plusminus\" src=\"" . $g_dirs["images"] . "dtree/nolines_plus.gif\" class=\"vam\"> " . $l_quicky->get_quick_info(
                        $l_obj_id,
                        $l_dao->get_obj_name_by_id_as_string($l_obj_id),
                        C__LINK__OBJECT
                    ) . " <img src=\"" . $g_dirs["images"] . "ajax-loading.gif\" id=\"ajax_loading_view_" . $l_obj_id . "\" style=\"display:none;\" class=\"vam\" />";
                $l_return .= "<br>";
                $l_return .= "</td></tr><tr><td><span id=\"row_" . $l_obj_id . "\"></span></td></tr>";
            } // foreach

            $l_return .= "</table>";
        }
        else
        {
            $l_return = '<div class="p5 m5 info"><img src="' . $g_dirs['images'] . 'icons/silk/information.png" class="vam mr5" /><span class="vam">' . _L(
                    'LC__REPORT__VIEW__NO_INCONSISTENCY'
                ) . '</span></div>';
        } // if

        return $l_return;
    } // function

    /**
     * Prepares relation paths of the it service
     *
     * @param array $p_its_arr
     *
     * @return string
     */
    private function prepare_table($p_its_arr)
    {
        global $g_comp_database;

        $l_dao          = new isys_cmdb_dao_status($g_comp_database);
        $l_quicky       = new isys_ajax_handler_quick_info();
        $l_inco_objects = '';

        $l_table = '<table padding="0" cellspacing="0" style="position:relative;"><tr><td style="padding-left:5px;">';

        if (count($this->m_inconsistence) > 0)
        {
            $l_table .= _L('LC__REPORT__VIEW__INCONSISTENCY_IN') . ': <p style="text-align:justify">';
            $l_counter = 900;

            foreach ($this->m_inconsistence as $l_inco_val)
            {
                $l_counter_arr = 1;
                $l_count_arr   = count($l_inco_val);
                foreach ($l_inco_val as $l_inco_obj_id => $l_inco_status)
                {
                    $l_inco_status = $l_dao->get_cmdb_status($l_inco_status)
                        ->get_row();
                    $l_inco_objects .= $l_quicky->get_quick_info($l_inco_obj_id, $l_dao->get_obj_name_by_id_as_string($l_inco_obj_id), C__LINK__OBJECT) . " [<b>" . _L(
                            $l_inco_status["isys_cmdb_status__title"]
                        ) . "</b>], ";
                    if (strlen($l_inco_objects) - $l_counter > 0)
                    {
                        if ($l_counter_arr == $l_count_arr)
                        {
                            $l_inco_objects = substr($l_inco_objects, 0, -2);
                        }
                        $l_inco_objects .= "<br>";
                        $l_counter = $l_counter + 900;
                    }
                    $l_counter_arr++;
                }
            }
            $l_table .= substr($l_inco_objects, 0, -2);
            $l_table .= "</p><br>";
        }

        $l_table .= "</td></tr><tr><td>";

        $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");

        foreach ($p_its_arr AS $l_key => $l_value)
        {
            $l_object_title      = $l_dao->get_obj_name_by_id_as_string($l_key);
            $l_object_type_title = _L($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_key)));

            $l_table .= "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;spacing:0px;\"><tr><td align=\"center\" title=\"" . $l_object_title . " (" . $l_object_type_title . ")\" class=\"vam\" style=\"cursor:pointer;border:2px solid #" . $l_value["cmdb_color"] . ";background-color:#EFEFEF;\">";
            $l_table .= "  " . $l_quicky->get_quick_info($l_key, $l_object_title, C__LINK__OBJECT) . "<br>";
            $l_table .= "  (" . $l_object_type_title . ")<br>";
            $l_table .= "</td>";

            $l_table .= "<td>";
            $l_table .= $this->prepare_childs($l_value["child"], $l_key);
            $l_table .= "</td>";

            $l_table .= "</tr></table>";
        }

        $l_table .= "</td></tr></table><br />";

        return $l_table;
    } // function

    /**
     * Recursive function to build the relation paths.
     *
     * @param   array   $p_arr
     * @param   integer $p_root_obj
     *
     * @return  string
     */
    private function prepare_childs($p_arr, $p_root_obj)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        $l_dao    = new isys_cmdb_dao($g_comp_database);
        $l_quicky = new isys_ajax_handler_quick_info();
        $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");

        if (is_array($p_arr))
        {
            $l_table = '';

            foreach ($p_arr AS $l_root_obj => $l_value)
            {
                $l_object_title      = $l_dao->get_obj_name_by_id_as_string($l_root_obj);
                $l_object_type_title = $g_comp_template_language_manager->get($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_root_obj)));

                $l_table .= '<table padding="0" cellspacing="2" style="position:relative;"><tr>' . '<td valign="top" align="center" title="' . $l_object_title . ' (' . $l_object_type_title . ')" class="child" style="border-color: #' . $l_value['cmdb_color'] . ';">' . $l_quicky->get_quick_info(
                        $l_root_obj,
                        $l_object_title,
                        C__LINK__OBJECT
                    ) . '<br />(' . $l_object_type_title . ')' . '</td>';

                if (is_array($l_value))
                {
                    $l_table .= '<td>' . $this->prepare_childs($l_value["child"], $p_root_obj) . '</td>';
                } // if

                $l_table .= '</tr></table>';
            } // foreach

            return $l_table;
        } // if
    } // function
} // class