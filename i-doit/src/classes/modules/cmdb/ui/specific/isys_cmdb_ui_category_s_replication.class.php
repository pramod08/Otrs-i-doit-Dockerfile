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
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_replication extends isys_cmdb_ui_category_specific
{
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_template, $index_includes;
        $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->get_row();

        $l_rules["C__CATS__REPLICATION__MECHANISM"]["p_strSelectedID"]                                                = $l_catdata["isys_replication_mechanism__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_replication_list__description"];
        if (empty($l_catdata["isys_cats_replication_list__id"]))
        {
            $l_rules["C__CATS__REPLICATION__MECHANISM"]["p_strSelectedID"]                                                = $_POST["C__CATS__REPLICATION__MECHANISM"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];
        }
        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__replication.tpl");
    }
}

?>