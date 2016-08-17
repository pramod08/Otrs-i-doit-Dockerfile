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
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_tsi_service extends isys_cmdb_ui_category_global
{

    /**
     * @return void
     *
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_comp_database, $g_comp_template;

        $l_arTypeData = [];
        $l_posts      = isys_module_request::get_instance()
            ->get_posts();

        $l_rules = [];

        if ($_GET[C__CMDB__GET__OBJECT])
        {
            $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                ->__to_array();

            $l_rules["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"]["p_strValue"]                                                = $l_catdata["isys_catg_tsi_service_list__tsi_service_id"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $l_catdata["isys_catg_tsi_service_list__description"];
        }

        if (!$p_cat->get_validation())
        {
            $l_rules["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"]["p_strValue"]                                                = $l_posts["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_posts["C__CMDB__CAT__COMMENTARY"];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // Apply rules
        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__tsi_service.tpl");
    }
}

?>