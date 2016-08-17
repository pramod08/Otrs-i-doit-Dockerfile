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
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_application_assigned_obj extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_application_assigned_obj $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules    = [];
        $l_catdata  = $p_cat->get_general_data();
        $l_variants = $p_cat->get_variants($_GET[C__CMDB__GET__OBJECT]);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);
        $l_rules["C__CATG__APPLICATION_DATABASE_SCHEMATA"]["p_strSelectedID"]                                         = null;
        $l_rules["C__CATG__APPLICATION_TITLE"]["p_strValue"]                                                          = $l_catdata["isys_catg_application_list__title"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_application_list__description"];
        $l_rules["C__CATS__APPLICATION_OBJ_APPLICATION"]["multiselection"]                                            = (isys_glob_get_param(
                C__GET__NAVMODE
            ) == C__NAVMODE__NEW);
        $l_rules["C__CATS__APPLICATION_OBJ_APPLICATION"]["p_strValue"]                                                = $l_catdata["isys_catg_application_list__isys_obj__id"];
        $l_rules["C__CATG__APPLICATION_VARIANT__VARIANT"]["p_arData"]                                                 = serialize($l_variants);
        $l_rules["C__CATG__APPLICATION_VARIANT__VARIANT"]["p_strSelectedID"]                                          = $l_catdata["isys_catg_application_list__isys_cats_app_variant_list__id"];
        $l_rules["C__CATG__APPLICATION_TYPE"]["p_strSelectedID"]                                                      = (($l_catdata['isys_obj__isys_obj_type__id'] ?: $_GET[C__CMDB__GET__OBJECTTYPE]) == C__OBJTYPE__OPERATING_SYSTEM) ? C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM : C__CATG__APPLICATION_TYPE__SOFTWARE;
        $l_rules["C__CATG__APPLICATION_VERSION"]["p_strTable"]                                                        = 'isys_catg_version_list';
        $l_rules["C__CATG__APPLICATION_VERSION"]["condition"]                                                         = 'isys_catg_version_list__isys_obj__id = ' . $p_cat->convert_sql_id(
                $_GET[C__CMDB__GET__OBJECT]
            );
        $l_rules["C__CATG__APPLICATION_VERSION"]["p_strSelectedID"]                                                   = $l_catdata['isys_catg_version_list__id'];

        if ($l_catdata !== null && is_array($l_catdata))
        {
            if ($l_catdata["isys_catg_application_list__isys_cats_lic_list__id"] > 0)
            {
                $l_rules["C__CATG__LIC_ASSIGN__LICENSE"]["p_strSelectedID"] = $l_catdata["isys_catg_application_list__isys_cats_lic_list__id"];
            } // if

            if ($l_catdata["isys_catg_application_list__isys_catg_relation_list__id"] > 0)
            {
                $l_rel_data  = isys_cmdb_dao_category_g_relation::instance($p_cat->get_database_component())
                    ->get_data($l_catdata["isys_catg_application_list__isys_catg_relation_list__id"])
                    ->get_row();
                $l_condition = "AND isys_connection__isys_obj__id = " . $p_cat->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                $l_dbms_data = isys_cmdb_dao_category_s_database_access::instance($p_cat->get_database_component())
                    ->get_data(null, null, $l_condition, null, C__RECORD_STATUS__NORMAL)
                    ->get_row();
                if ($l_dbms_data['isys_obj__id'] > 0)
                {
                    $l_rules["C__CATG__APPLICATION_DATABASE_SCHEMATA"]["p_strSelectedID"] = $l_dbms_data["isys_obj__id"];
                } // if
            } // if
        } // if

        $this->get_template_component()
            ->assign("hide_priority", $l_rules["C__CATG__APPLICATION_TYPE"]["p_strSelectedID"] != C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM)
            ->assign('category', 's')
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_s_application_assigned_obj &$p_cat
     *
     * @return  boolean
     */
    public function process_list(isys_cmdb_dao_category_s_application_assigned_obj &$p_cat)
    {
        $this->list_view("isys_catg_application", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_cats_application_assigned_obj($p_cat));

        return true;
    } // function
} // class