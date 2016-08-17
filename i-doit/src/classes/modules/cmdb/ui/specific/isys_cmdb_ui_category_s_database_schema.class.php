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
 * CMDB Specific category Database Schema
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_database_schema extends isys_cmdb_ui_category_specific
{

    /**
     * @param isys_cmdb_dao_category_s_database_schema $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_template;

        $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        if (empty($l_catdata["isys_cats_database_schema_list__title"]))
        {
            $l_rules["C__CMDB__CATS__DB_SCHEMA__TITLE"]["p_strValue"] = $p_cat->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);
        }
        else
        {
            $l_rules["C__CMDB__CATS__DB_SCHEMA__TITLE"]["p_strValue"] = $l_catdata["isys_cats_database_schema_list__title"];
        } // if

        $l_rules["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"]["p_strSelectedID"] = $l_catdata["isys_connection__isys_obj__id"];

        $l_dao_instance = new isys_cmdb_dao_category_s_database_instance($p_cat->get_database_component());
        $l_instances    = $l_dao_instance->get_data();
        $l_on           = strtolower(_L("LC__UNIVERSAL__ON"));

        while ($l_row = $l_instances->get_row())
        {
            $l_arInstances[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"] . " " . $l_on . " " . $p_cat->get_obj_name_by_id_as_string(
                    $l_row["isys_connection__isys_obj__id"]
                );
        } // while

        $l_rules["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"]["p_arData"] = serialize($l_arInstances);

        $l_rules["C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"]["p_strValue"]                                            = $l_catdata["isys_cats_database_schema_list__storage_engine"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
        )]["p_strValue"]                                                                                              = $l_catdata["isys_cats_database_schema_list__description"];

        /* Validation process */
        if (!$p_cat->get_validation())
        {
            $l_rules["C__CMDB__CATS__DB_SCHEMA__TITLE"]["p_strValue"]                                                     = $_POST["C__CMDB__CATS__DB_SCHEMA__TITLE"];
            $l_rules["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"]["p_strSelectedID"]                                              = $_POST["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"];
            $l_rules["C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"]["p_strValue"]                                            = $_POST["C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];
            $l_rules                                                                                                      = isys_glob_array_merge(
                $l_rules,
                $p_cat->get_additional_rules()
            );
        } // if

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__database_schema.tpl");
    } // function
} // class
?>