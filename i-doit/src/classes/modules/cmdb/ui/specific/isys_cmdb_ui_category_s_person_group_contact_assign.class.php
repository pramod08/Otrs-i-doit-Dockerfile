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
 * CMDB Person: Specific category
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_person_group_contact_assign extends isys_cmdb_ui_category_specific
{

    /**
     * @desc   define if this sub category is multivalued or not
     * @author Dennis Stücken <dstuecken@synetics.de>
     * @return boolean
     */
    public function is_multivalued()
    {
        return true;
    }

    /**
     * @global                       $index_includes
     * @global                       $g_comp_template
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @desc show the detail-template for specific category monitor
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_template;

        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_rules["C__CONTACT__ORGANISATION_TARGET_OBJECT"]["p_strSelectedID"] = $l_catdata["isys_catg_contact_list__isys_obj__id"];
        $l_rules["C__CONTACT__ORGANISATION_TARGET_OBJECT"]["groupFilter"]     = "C__OBJTYPE_GROUP__INFRASTRUCTURE;C__OBJTYPE_GROUP__OTHER;C__OBJTYPE_GROUP__SOFTWARE";

        $l_rules["C__CONTACT__ORGANISATION_ROLE"]["p_strTable"]                                                       = "isys_contact_tag";
        $l_rules["C__CONTACT__ORGANISATION_ROLE"]["p_strSelectedID"]                                                  = $l_catdata["isys_catg_contact_list__isys_contact_tag__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_contact_list__description"];

        // Apply rules
        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();

        return true;
    }

    /**
     * @desc    process_list
     * @authorn Dennis Stücken <dstuecken@synetics.de>
     *
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_dao_list_contact = new isys_cmdb_dao_list_cats_person_group_contact_assign($p_cat->get_database_component());

        $l_obj_id  = $_GET[C__CMDB__GET__OBJECT];
        $l_listres = $l_dao_list_contact->get_result(null, $l_obj_id);

        /**
         * @desc component list construction
         */
        $l_comp_list = new isys_component_list(null, $l_listres, $l_dao_list_contact, $l_dao_list_contact->get_rec_status());

        /**
         * @desc modify get parameters
         */
        $l_new_get[C__CMDB__GET__VIEWMODE]           = C__CMDB__VIEW__CATEGORY;
        $l_new_get[C__CMDB__GET__TREEMODE]           = C__CMDB__VIEW__TREE_OBJECT;
        $l_new_get[C__CMDB__GET__OBJECT]             = $_GET[C__CMDB__GET__OBJECT];
        $l_new_get[C__CMDB__GET__OBJECTTYPE]         = $_GET[C__CMDB__GET__OBJECTTYPE];
        $l_new_get[C__CMDB__GET__CATS]               = $_GET[C__CMDB__GET__CATS] ? $_GET[C__CMDB__GET__CATS] : C__CATS__PERSON_GROUP_CONTACT_ASSIGNMENT;
        $l_new_get[C__CMDB__GET__CATLEVEL]           = "[{isys_catg_contact_list__id}]";
        $l_new_get[C__CMDB__GET__CAT_MENU_SELECTION] = $_GET[C__CMDB__GET__CAT_MENU_SELECTION];

        /**
         * @desc config the fields
         */
        $l_comp_list->config($l_dao_list_contact->get_fields(), $l_dao_list_contact->make_row_link(), "[{isys_catg_contact_list__id}]", true);

        /**
         * @desc create the temporary table
         */
        $l_comp_list->createTempTable();

        $l_dao_list_contact->set_rec_status($_SESSION["cRecStatusListView"]);

        /**
         * @desc output handling
         */
        $l_table = $l_comp_list->getTempTableHtml();
        $this->m_template->assign("objectTableList", $l_table)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');

        return true;
    }

    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__contact_assign.tpl");
        parent::__construct($p_template);
    }
}

?>