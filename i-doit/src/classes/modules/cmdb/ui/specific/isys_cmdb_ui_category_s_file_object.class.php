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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken - <dstuecken@i-doit.org>
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class   isys_cmdb_ui_category_s_file_object extends isys_cmdb_ui_category_specific
{
    /**
     * Retrieve the category title.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        return _L("LC__CMDB__CATS__MAINTENANCE_LINKED_OBJECT_LIST");
    } // function

    /**
     * Show the list-template for subcategories of maintenance show list of assigned obj.
     *
     * @param   isys_cmdb_dao_category_s_file_object &$p_cat
     *
     * @return  null
     */
    public function process_list(isys_cmdb_dao_category_s_file_object $p_cat)
    {
        global $index_includes;

        isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons()
            ->hide_all_buttons();

        $l_listdao = new isys_cmdb_dao_list_cats_file_object($p_cat);
        $l_listdao->set_rec_status_list(false);

        $l_listres = $l_listdao->get_result(null, $_GET[C__CMDB__GET__OBJECT]);

        $l_arTableHeader = $l_listdao->get_fields();

        $l_objList = new isys_component_list(null, $l_listres, $l_listdao, $l_listdao->get_rec_status());

        $l_objList->config($l_arTableHeader, "", "", true);

        $l_objList->createTempTable();
        $l_strTempHtml = $l_objList->getTempTableHtml();

        $this->get_template_component()
            ->assign("objectTableList", $l_strTempHtml)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = $this->get_template();

        return null;
    } // function

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("object_table_list.tpl");
    } // function
} // class