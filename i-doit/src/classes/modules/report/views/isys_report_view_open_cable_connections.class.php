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
 * @copyright  Copyright 2011 - synetics GmbH
 */
class isys_report_view_open_cable_connections extends isys_report_view
{

    /**
     * @var array
     */
    private $m_cable_run_arr = [];
    /**
     * @var array
     */
    private $m_map = [
        "C__CATG__UNIVERSAL_INTERFACE",
        "C__CMDB__SUBCAT__NETWORK_PORT",
        "C__CMDB__SUBCAT__NETWORK_INTERFACE_P",
        "C__CMDB__SUBCAT__NETWORK_INTERFACE_L",
        "C__CATG__CONNECTOR",
    ];

    /**
     *
     */
    public function ajax_request()
    {

    }

    /**
     * @return string
     */
    public static function description()
    {
        return "LC__REPORT__VIEW__OPEN_CABLE_CONNECTIONS__DESCRIPTION";
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
        return "LC__REPORT__VIEW__OPEN_CABLE_CONNECTIONS__TITLE";
    }

    /**
     * SHOW LIST OF FREE CABLES
     *
     */
    public function start()
    {
        global $g_comp_template, $g_comp_database, $g_dirs;

        $l_dao        = new isys_cmdb_dao($g_comp_database);
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_cable_arr  = [];

        $l_sql = "SELECT *
				FROM isys_obj AS main
				LEFT JOIN isys_catg_connector_list ON main.isys_obj__id = isys_catg_connector_list__isys_obj__id
				WHERE isys_catg_connector_list__isys_cable_connection__id IS NOT NULL
				AND isys_catg_connector_list__type = '2' AND isys_obj__isys_obj_type__id NOT IN " . "(" . C__OBJTYPE__ESC . ", " . C__OBJTYPE__EPS . ", " . C__OBJTYPE__DISTRIBUTION_BOX . ", " . C__OBJTYPE__PDU . ", " . C__OBJTYPE__UPS . ")";

        $l_res = $l_dao->retrieve($l_sql);

        while ($l_row = $l_res->get_row())
        {
            if ($l_row["isys_catg_connector_list__id"] != "")
            {
                $l_cable_arr[$l_row["isys_obj__id"]][] = $this->get_cable_run($l_row["isys_obj__id"], $l_row["isys_catg_connector_list__id"]);
            }
        }
        if (count($l_cable_arr) > 0)
        {
            foreach ($l_cable_arr AS $l_obj_id => $l_val)
            {

                $l_object_type = _L($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_obj_id)));

                foreach ($l_val AS $l_key => $l_val2)
                {
                    if (is_null($l_val2))
                    {
                        unset($l_cable_arr[$l_obj_id]);
                    }

                    $l_conn = $l_val2[1]["CONNECTION"];

                    $l_connector_id = $l_val2[1]["CONNECTOR_ID"];

                    // CHECK IF HAS NO PARENT CONNECTION
                    $l_check = $this->has_no_parent_connection($l_val2[1]["CONNECTOR_ID"]);
                    //$l_check2 = $this->sibling_is_input_connection($l_val2[1]["CONNECTOR_ID"]);

                    if ($l_check && !is_null($l_val2[1]["CABLE_ID"]))
                    {

                        while (!is_null($l_conn))
                        {

                            if ($l_conn["SIBLING"] == false)
                            {

                                unset($this->m_cable_run_arr[$l_obj_id]["connection"][$l_connector_id]);
                                break;
                            }

                            // $l_val2[1]["CONNECTOR_TYPE"] = 2 DANN AUSGANG 1 = EINGANG

                            $this->m_cable_run_arr[$l_obj_id]["connection"][$l_connector_id]["object_title"] = "<nobr>" . $l_quick_info->get_quick_info(
                                    $l_obj_id,
                                    $l_val2[1]["OBJECT_TITLE"],
                                    C__LINK__CATG,
                                    false,
                                    [
                                        C__CMDB__GET__CATG     => C__CATG__CONNECTOR,
                                        C__CMDB__GET__CATLEVEL => $l_val2[1]["CONNECTOR_ID"]
                                    ]
                                ) . "</nobr> <nobr>(" . $l_object_type . ")</nobr>";
                            $l_connector_type                                                                = ($l_val2[1]["CONNECTOR_TYPE"] == 2) ? _L(
                                "LC__CATG__CONNECTOR__OUTPUT"
                            ) : _L("LC__CATG__CONNECTOR__INPUT");
                            $l_connector_title                                                               = "<nobr>" . $l_val2[1]["CONNECTOR_TITLE"] . " (" . $l_connector_type . ")</nobr>";
                            $this->m_cable_run_arr[$l_obj_id]["connection"][$l_connector_id]["title"]        = $l_connector_title;
                            $this->m_cable_run_arr[$l_obj_id]["connection"][$l_connector_id]["type"]         = $l_val2[1]["CONNECTOR_TYPE"];
                            $this->set_cable_run(
                                $l_obj_id,
                                $l_val2[1]["OBJECT_TITLE"],
                                $l_conn["OBJECT_ID"],
                                $l_conn["CONNECTOR_ID"],
                                $l_conn["CONNECTOR_TYPE"],
                                $l_conn["OBJECT_TITLE"],
                                $l_conn["CONNECTOR_TITLE"],
                                $l_conn["LINK"],
                                $l_connector_id,
                                $l_conn["CABLE_ID"]
                            );

                            if ($l_conn["CONNECTION"])
                            {
                                $l_conn = $l_conn["CONNECTION"];
                            }
                            else
                            {
                                $l_conn = $l_conn["SIBLING"][0];
                            }
                        }
                    }
                }
            }
        }
        else
        {

        }
        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $g_comp_template->assign("dir_images", $g_dirs["images"]);
        $g_comp_template->assign("viewContent", $this->m_cable_run_arr);
    }

    /**
     * @return string
     */
    public function template()
    {
        return "view_open_cable_connections.tpl";
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
        return "LC__CMDB__CATG__CABLING_AND_PATCH";
    }

    /**
     * @param $p_connection_id
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     */
    private function has_no_parent_connection($p_connection_id)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        $l_sql = "SELECT sub.* FROM isys_catg_connector_list AS main " . "LEFT JOIN isys_catg_connector_list AS sub " . "ON sub.isys_catg_connector_list__id = main.isys_catg_connector_list__isys_catg_connector_list__id " . "AND sub.isys_catg_connector_list__isys_obj__id = main.isys_catg_connector_list__isys_obj__id " . "WHERE sub.isys_catg_connector_list__isys_catg_connector_list__id = " . $l_dao->convert_sql_id(
                $p_connection_id
            );

        $l_res = $l_dao->retrieve($l_sql);

        $l_row = $l_res->get_row();

        if ($l_row["isys_catg_connector_list__isys_cable_connection__id"] == "" || $l_res->num_rows() == 0)
        {

            $l_sql = "SELECT subsub.* FROM isys_catg_connector_list AS main
						LEFT JOIN isys_catg_connector_list AS sub ON main.isys_catg_connector_list__isys_catg_connector_list__id = sub.isys_catg_connector_list__id
						LEFT JOIN isys_catg_connector_list AS subsub ON subsub.isys_catg_connector_list__isys_cable_connection__id = sub.isys_catg_connector_list__isys_cable_connection__id AND subsub.isys_catg_connector_list__id != sub.isys_catg_connector_list__id
						WHERE main.isys_catg_connector_list__id = " . $l_dao->convert_sql_id($p_connection_id);

            $l_res = $l_dao->retrieve($l_sql);

            $l_row = $l_res->get_row();

            if ($l_row["isys_catg_connector_list__id"] == "" || $l_res->num_rows() == 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * @param $p_connection_id
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     */
    private function sibling_is_input_connection($p_connection_id)
    {
        global $g_comp_database;

        $l_dao     = new isys_cmdb_dao_cable_connection($g_comp_database);
        $l_cableID = $l_dao->get_cable_connection_id_by_connector_id($p_connection_id);

        $l_sql = "SELECT * FROM isys_catg_connector_list " . "WHERE isys_catg_connector_list__id != " . $l_dao->convert_sql_id(
                $p_connection_id
            ) . " AND isys_catg_connector_list__isys_cable_connection__id = " . $l_dao->convert_sql_id($l_cableID);

        $l_res = $l_dao->retrieve($l_sql);

        $l_row = $l_res->get_row();

        if ($l_row["isys_catg_connector_list__type"] == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param $p_obj_id
     * @param $p_connector_id
     *
     * @return array
     * @throws isys_exception_general
     */
    private function get_cable_run($p_obj_id, $p_connector_id)
    {
        global $g_comp_database;

        $l_dao_conn = new isys_cmdb_dao_category_g_connector($g_comp_database);
        $l_arr      = $l_dao_conn->resolve_cable_run($p_connector_id, true);

        return $l_arr;
    }

    /**
     * @param $p_obj_id
     * @param $p_obj_title
     * @param $p_conn_obj_id
     * @param $p_conn_connector_id
     * @param $p_conn_type
     * @param $p_conn_obj_title
     * @param $p_connector_title
     * @param $p_link
     * @param $p_key
     * @param $p_cable_id
     */
    private function set_cable_run($p_obj_id, $p_obj_title, $p_conn_obj_id, $p_conn_connector_id, $p_conn_type, $p_conn_obj_title, $p_connector_title, $p_link, $p_key, $p_cable_id)
    {
        global $g_comp_database;

        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_dao        = new isys_cmdb_dao($g_comp_database);

        if ($p_cable_id == null)
        {
            $l_cable_set = false;
        }
        else
        {
            $l_cable_set = true;
        }

        $this->m_cable_run_arr[$p_obj_id]["connection"][$p_key]["connection"][$p_conn_obj_id]["title"]                    = "<nobr>" . $l_quick_info->get_quick_info(
                $p_conn_obj_id,
                $p_conn_obj_title,
                C__LINK__CATG,
                false,
                [
                    C__CMDB__GET__CATG     => C__CATG__CONNECTOR,
                    C__CMDB__GET__CATLEVEL => $p_conn_connector_id
                ]
            ) . "</nobr> <nobr>(" . _L($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($p_conn_obj_id))) . ")</nobr>";
        $this->m_cable_run_arr[$p_obj_id]["connection"][$p_key]["connection"][$p_conn_obj_id]["connection"][$p_conn_type] = [
            "connector_id" => $p_conn_connector_id,
            "title"        => $p_connector_title,
            "cable_set"    => $l_cable_set
        ];
    }

}