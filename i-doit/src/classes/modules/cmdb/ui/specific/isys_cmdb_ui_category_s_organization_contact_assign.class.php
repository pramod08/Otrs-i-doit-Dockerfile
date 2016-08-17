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
 * CMDB Person: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_organization_contact_assign extends isys_cmdb_ui_category_specific
{
    /**
     * Define if this sub category is multivalued or not.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @return  boolean
     */
    public function is_multivalued()
    {
        return true;
    } // function

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_rules["C__CONTACT__ORGANISATION_TARGET_OBJECT"]["p_strSelectedID"]                                         = $l_catdata["isys_catg_contact_list__isys_obj__id"];
        $l_rules["C__CONTACT__ORGANISATION_TARGET_OBJECT"]["groupFilter"]                                             = "C__OBJTYPE_GROUP__INFRASTRUCTURE;C__OBJTYPE_GROUP__OTHER;C__OBJTYPE_GROUP__SOFTWARE";
        $l_rules["C__CONTACT__ORGANISATION_ROLE"]["p_strTable"]                                                       = "isys_contact_tag";
        $l_rules["C__CONTACT__ORGANISATION_ROLE"]["p_strSelectedID"]                                                  = $l_catdata["isys_catg_contact_list__isys_contact_tag__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_contact_list__description"];

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_s_organization_contact_assign $p_cat
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_general
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category_s_organization_contact_assign &$p_cat)
    {
        global $g_cmdb_view, $index_includes;

        $l_gets = $g_cmdb_view->get_module_request()
            ->get_gets();

        $l_dao_list_contact = new isys_cmdb_dao_list_cats_organization_contact_assign($p_cat->get_database_component());

        $l_obj_id  = $l_gets[C__CMDB__GET__OBJECT];
        $l_listres = $l_dao_list_contact->get_result(null, $l_obj_id, $l_dao_list_contact->get_rec_status());

        // Component list construction.
        $l_comp_list = new isys_component_list(null, $l_listres, $l_dao_list_contact, $l_dao_list_contact->get_rec_status());

        // Modify get parameters.
        $l_new_get[C__CMDB__GET__VIEWMODE]           = C__CMDB__VIEW__CATEGORY;
        $l_new_get[C__CMDB__GET__TREEMODE]           = C__CMDB__VIEW__TREE_OBJECT;
        $l_new_get[C__CMDB__GET__OBJECT]             = $l_gets[C__CMDB__GET__OBJECT];
        $l_new_get[C__CMDB__GET__OBJECTTYPE]         = $l_gets[C__CMDB__GET__OBJECTTYPE];
        $l_new_get[C__CMDB__GET__CATS]               = $_GET[C__CMDB__GET__CATS] ? $_GET[C__CMDB__GET__CATS] : C__CATS__ORGANIZATION_CONTACT_ASSIGNMENT;
        $l_new_get[C__CMDB__GET__CATLEVEL]           = "[{isys_catg_contact_list__id}]";
        $l_new_get[C__CMDB__GET__CAT_MENU_SELECTION] = $l_gets[C__CMDB__GET__CAT_MENU_SELECTION];

        // Config the fields.
        $l_comp_list->config($l_dao_list_contact->get_fields(), $l_dao_list_contact->make_row_link(), "[{isys_catg_contact_list__id}]", true);

        // Create the temporary table.
        $l_comp_list->createTempTable();

        $l_dao_list_contact->set_rec_status($_SESSION["cRecStatusListView"]);

        // Output handling.
        $this->get_template_component()
            ->assign("objectTableList", $l_comp_list->getTempTableHtml())
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";

        return true;
    } // function

    /**
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__contact_assign.tpl");
        parent::__construct($p_template);
    } // function
} // class