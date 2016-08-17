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
 * CMDB Active Directory: Specific category
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_emergency_plan_assigned_obj extends isys_cmdb_ui_category_s_emergency_plan
{
    /**
     * Returns the title of the specific category
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        global $g_comp_template_language_manager;

        return $g_comp_template_language_manager->get("LC__CMDB__CATS__EMERGENCY_PLAN_LINKED_OBJECT_LIST");
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_s_emergency_plan_assigned_obj $p_cat
     *
     * @return  null
     */
    public function process_list(isys_cmdb_dao_category_s_emergency_plan_assigned_obj $p_cat)
    {
        global $index_includes;

        $l_listdao = new isys_cmdb_dao_list_generic_assigned__obj($this->get_database_component(), $p_cat);
        $l_listdao->set_rec_status_list(false);

        // set sourcetable (cause of using generic list...)
        $l_listdao->set_source_table("isys_catg_emergency_plan");

        $l_listres = $l_listdao->get_result(null, $_GET[C__CMDB__GET__OBJECT]);

        $l_arTableHeader = $l_listdao->get_fields();

        //1. step: construct list
        $l_objList = new isys_component_list(null, $l_listres, $l_listdao, $l_listdao->get_rec_status());

        //2. step: config list
        $l_objList->config($l_arTableHeader, $l_listdao->make_row_link(), "", true);

        //5. step: createTempTable() (optional)
        $l_objList->createTempTable();

        //6. step: getTempTableHtml()
        $l_strTempHtml = $l_objList->getTempTableHtml();

        //7. step: assign html to smarty
        $this->get_template_component()
            ->assign("objectTableList", $l_strTempHtml)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons()
            ->hide_all_buttons();

        $index_includes['contentbottomcontent'] = $this->get_template();

        return null;
    } // function

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("object_table_list.tpl");
    } // function
} // class
?>