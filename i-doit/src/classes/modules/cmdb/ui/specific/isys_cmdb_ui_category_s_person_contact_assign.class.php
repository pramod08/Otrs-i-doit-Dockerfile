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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_person_contact_assign extends isys_cmdb_ui_category_specific
{
    /**
     * Define if this category is multivalued or not.
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
     * @return  void
     * @throws  isys_exception_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Process-list method.
     *
     * @param   isys_cmdb_dao_category_s_person_contact_assign $p_cat
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category_s_person_contact_assign $p_cat)
    {
        global $g_cmdb_view;

        $l_gets = $g_cmdb_view->get_module_request()
            ->get_gets();

        $l_dao_list_contact = new isys_cmdb_dao_list_cats_person_contact_assign($p_cat->get_database_component());

        $l_listres = $l_dao_list_contact->get_result(null, $l_gets[C__CMDB__GET__OBJECT]);

        $l_comp_list = new isys_component_list(null, $l_listres, $l_dao_list_contact, $l_dao_list_contact->get_rec_status());

        $l_comp_list->config(
            $l_dao_list_contact->get_fields(),
            $l_dao_list_contact->make_row_link(),
            "[{isys_catg_contact_list__id}]",
            true
        );

        $l_comp_list->createTempTable();

        $l_dao_list_contact->set_rec_status($_SESSION["cRecStatusListView"]);

        $l_table = $l_comp_list->getTempTableHtml();

        $this->get_template_component()
            ->assign("objectTableList", $l_table)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__QUICK_PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE);

        return true;
    } // function
} // class