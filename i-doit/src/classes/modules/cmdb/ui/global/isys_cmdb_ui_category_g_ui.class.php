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
 * CMDB UI: Global category (category type is global)
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @version    Dennis Blümer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_ui extends isys_cmdb_ui_category_global
{

    /**
     * @global                       $index_includes
     * @global                       $g_comp_template
     *
     * @param isys_cmdb_dao_category & $p_cat
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2007-03-29
     * @desc    show the detail-template for subcategories of odep
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_template;

        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_dao = new isys_cmdb_dao_cable_connection($p_cat->get_database_component());

        $l_connectorAheadID = $l_dao->get_assigned_connector_id($l_catdata["isys_catg_ui_list__isys_catg_connector_list__id"]);
        $l_cableID          = $l_dao->get_assigned_cable($l_catdata["isys_catg_ui_list__isys_catg_connector_list__id"]);

        $l_rules["C__CATG__UI_TITLE"]["p_strValue"]                                                                   = $l_catdata["isys_catg_ui_list__title"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_ui_list__description"];
        $l_rules["C__CATG__UI_CONNECTION_TYPE"]["p_strSelectedID"]                                                    = $l_catdata["isys_catg_ui_list__isys_ui_con_type__id"];
        $l_rules["C__CATG__UI_PLUG_TYPE"]["p_strSelectedID"]                                                          = $l_catdata["isys_catg_ui_list__isys_ui_plugtype__id"];
        $l_rules["C__CATG__UI__ASSIGNED_UI"]["p_strValue"]                                                            = $l_connectorAheadID;
        $l_rules["C__CATG__UI__ASSIGNED_CABLE"]["p_strValue"]                                                         = $l_cableID;

        if (!$p_cat->get_validation())
        {
            $l_rules["C__CATG__UI_TITLE"]["p_strValue"]                                                                   = $_POST["C__CATG__UI_TITLE"];
            $l_rules["C__CATG__UI_CONNECTION_TYPE"]["p_strSelectedID"]                                                    = $_POST["C__CATG__UI_CONNECTION_TYPE"];
            $l_rules["C__CATG__UI_PLUG_TYPE"]["p_strSelectedID"]                                                          = $_POST["C__CATG__UI_PLUG_TYPE"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__ui.tpl");
    }
}

?>