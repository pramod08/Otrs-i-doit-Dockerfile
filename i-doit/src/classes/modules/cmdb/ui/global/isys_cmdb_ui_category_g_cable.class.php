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
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_cable extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_comp_database;

        $l_dao = new isys_component_dao($g_comp_database);

        $l_stmt = "SELECT * " . "FROM isys_catg_connector_list " . "INNER JOIN isys_cable_connection " . "ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id " . "WHERE isys_cable_connection__isys_obj__id='" . $_GET[C__CMDB__GET__OBJECT] . "';";

        $l_res      = $l_dao->retrieve($l_stmt);
        $l_res_data = [];

        if ($l_res->num_rows() > 0)
        {
            $l_res_data = $l_res->get_row();
        } // if

        // Obj A
        $l_obj_a = $p_cat->get_object_by_id($l_res_data['isys_catg_connector_list__isys_obj__id']);

        $l_aObject_a = $l_obj_a->get_row();

        $l_connector_id         = $l_res_data['isys_catg_connector_list__id'];
        $l_dao_cable_connection = new isys_cmdb_dao_cable_connection($g_comp_database);
        $l_assigned_connector   = $l_dao_cable_connection->get_assigned_connector($l_connector_id);
        $l_connector_row        = $l_assigned_connector->get_row();
        $l_aObject_b            = [];

        // Obj B
        // echo 'isys_catg_connector_list__isys_obj__id'.$l_connector_row['isys_catg_connector_list__isys_obj__id'].'++';
        if ($l_connector_row)
        {
            $l_obj_b     = $p_cat->get_object_by_id($l_connector_row['isys_catg_connector_list__isys_obj__id']);
            $l_aObject_b = $l_obj_b->get_row();
        } // if

        // Make rules.
        $l_rules                                           = [];
        $l_rules["C__CATG__CABLE_TYPE"]["p_strTable"]      = "isys_cable_type";
        $l_rules["C__CATG__CABLE_COLOUR"]["p_strTable"]    = "isys_cable_colour";
        $l_rules["C__CATG__CABLE_OCCUPANCY"]["p_strTable"] = "isys_cable_occupancy";

        // Obj A: Title.
        $l_quick_info                                  = new isys_ajax_handler_quick_info();
        $l_rules["C__CATG__CABLE_OBJ_A"]["p_strValue"] = $l_quick_info->get_quick_info(
            $l_res_data['isys_catg_connector_list__isys_obj__id'],
            $l_aObject_a["isys_obj__title"],
            C__LINK__OBJECT,
            80
        );

        // Obj A: Link for Connection.
        $l_objID = $l_res_data['isys_catg_connector_list__isys_obj__id'];

        $l_dao = new isys_cmdb_dao_cable_connection($g_comp_database);

        $l_objInfo = $l_dao->get_type_by_object_id($l_objID)
            ->get_row();

        if ($l_res_data["isys_catg_connector_list__assigned_category"] != "" && $l_res_data["isys_catg_connector_list__assigned_category"] != null)
        {
            $l_categoryA = (is_numeric($l_res_data["isys_catg_connector_list__assigned_category"])) ? $l_res_data["isys_catg_connector_list__assigned_category"] : constant(
                $l_res_data["isys_catg_connector_list__assigned_category"]
            );
        }
        else
        {
            $l_categoryA = C__CATG__CONNECTOR;
        } // if

        $l_arrMaster = [
            C__CMDB__GET__OBJECT     => $l_objID,
            C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
            C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
            C__CMDB__GET__CATG       => $l_categoryA,
            C__CMDB__GET__TREEMODE   => C__CMDB__VIEW__TREE_OBJECT
        ];

        $l_rules["C__CATG__CABLE_CONN_A"]["p_strValue"] = '<a href="' . isys_helper_link::create_url(
                $l_arrMaster
            ) . '">' . $l_res_data["isys_catg_connector_list__title"] . '</a>';

        // Obj B: Title.
        if ($l_connector_row)
        {
            $l_rules["C__CATG__CABLE_OBJ_B"]["p_strValue"] = $l_quick_info->get_quick_info(
                $l_aObject_b['isys_obj__id'],
                $l_aObject_b["isys_obj__title"],
                C__LINK__OBJECT,
                80
            );
            if ($l_connector_row["isys_catg_connector_list__assigned_category"] != "" && $l_connector_row["isys_catg_connector_list__assigned_category"] != null)
            {
                $l_categoryB = (is_numeric(
                    $l_connector_row["isys_catg_connector_list__assigned_category"]
                )) ? $l_connector_row["isys_catg_connector_list__assigned_category"] : constant($l_connector_row["isys_catg_connector_list__assigned_category"]);
            }
            else
            {
                $l_categoryB = C__CATG__CONNECTOR;
            } // if
            // Obj B: Link for Connection.
            $l_objID                                        = $l_aObject_b['isys_obj__id'];
            $l_objInfo                                      = $l_dao->get_type_by_object_id($l_objID)
                ->get_row();
            $l_arrMaster                                    = [
                C__CMDB__GET__OBJECT     => $l_objID,
                C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => $l_categoryB,
                C__CMDB__GET__TREEMODE   => C__CMDB__VIEW__TREE_OBJECT
            ];
            $l_rules["C__CATG__CABLE_CONN_B"]["p_strValue"] = '<a href="' . isys_helper_link::create_url(
                    $l_arrMaster
                ) . '">' . $l_connector_row["isys_catg_connector_list__title"] . '</a>';
        }
        else
        {
            $l_rules["C__CATG__CABLE_OBJ_B"]["p_strValue"] = $l_rules["C__CATG__CABLE_CONN_B"]["p_strValue"] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        // If no connect_id set rules to '- -'.
        if (!$l_connector_id)
        {
            $l_rules["C__CATG__CABLE_OBJ_A"]["p_strValue"]  = '- -';
            $l_rules["C__CATG__CABLE_OBJ_B"]["p_strValue"]  = '- -';
            $l_rules["C__CATG__CABLE_CONN_A"]["p_strValue"] = '- -';
            $l_rules["C__CATG__CABLE_CONN_B"]["p_strValue"] = '- -';
        } // if

        $l_catdata                                                                                                    = $p_cat->get_general_data();
        $l_rules["C__CATG__CABLE_TYPE"]["p_strSelectedID"]                                                            = $l_catdata['isys_catg_cable_list__isys_cable_type__id'];
        $l_rules["C__CATG__CABLE_LENGTH"]["p_strValue"]                                                               = $l_catdata['isys_catg_cable_list__length'];
        $l_rules["C__CATG__CABLE_MAX_AMOUNT_OF_FIBERS_LEADS"]["p_strValue"]                                           = $l_catdata['isys_catg_cable_list__max_amount_of_fibers_leads'];
        $l_rules["C__CATG__CABLE_COLOUR"]["p_strSelectedID"]                                                          = $l_catdata['isys_catg_cable_list__isys_cable_colour__id'];
        $l_rules["C__CATG__CABLE_OCCUPANCY"]["p_strSelectedID"]                                                       = $l_catdata['isys_catg_cable_list__isys_cable_occupancy__id'];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_cable_list__description"];

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function
} // class