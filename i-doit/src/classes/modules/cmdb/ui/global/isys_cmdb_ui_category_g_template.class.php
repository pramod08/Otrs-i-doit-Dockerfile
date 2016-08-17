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
 * CMDB UI: global category for templates
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      <NAME> <<EMAIL>>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_template extends isys_cmdb_ui_category_global
{

    /**
     * Processes view/edit mode.
     *
     * Overwrite or call parent method if needed, otherwise delete this method.
     *
     * @global array                 $index_includes
     *
     * @param isys_cmdb_dao_category $p_cat Category's DAO
     *
     * @note for standard categories, a process method is not needed anymore
     *
     * public function process (isys_cmdb_dao_category $p_cat)
     * {
     * global $index_includes;
     *
     * // We fetch the data of this category entry.
     * $l_catdata = $p_cat->get_result()->get_row();
     *
     * // Assign cleartext values.
     * $l_rules = array();
     * $l_rules["C__CATG__TEMPLATE__TITLE"]["p_strValue"]    = $l_catdata["isys_catg_template_list__title"];
     * $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"]    = $l_catdata["isys_catg_template_list__description"];
     *
     * // We don't use the $g_comp_database variable - Instead: use the method provided by the parent.
     * $l_template_dao = new isys_cmdb_dao_category_g_template($this->get_database_component());
     * $l_template_row = $l_template_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])->get_row();
     *
     * // Instead of the global $g_comp_template we use "get_template_component" because we already got the variable in our parent.
     * $this->get_template_component()
     * ->assig('title', $l_template_row['isys_catg_template_list__title'])
     * ->assig('template_var', array('a', 'b', 'c'))
     * ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
     *
     * // This is optional, this gets called by the "isys_cmdb_view_category" class - ONLY ON SINGLE VALUE CATEGORIES.
     * // $this->activate_commentary($p_cat);
     *
     * // This is optional, this gets called by the "isys_cmdb_view_category" class - ONLY ON SINGLE VALUE CATEGORIES.
     * // $index_includes["contentbottomcontent"] = $this->get_template();
     * } // function
     */

    /**
     * Processes category data list for multi-valued categories.
     *
     * Overwrite or call parent method if needed, otherwise delete this method.
     *
     * @param   isys_cmdb_dao_category $p_cat                Category's DAO
     * @param   array                  $p_get_param_override (optional)
     * @param   string                 $p_strVarName         (optional)
     * @param   string                 $p_strTemplateName    (optional)
     * @param   boolean                $p_bCheckbox          (optional)
     * @param   boolean                $p_bOrderLink         (optional)
     * @param   string                 $p_db_field_name      (optional)
     *
     * @return  null
     *
     * @note for standard multivalue categories, a list processing mehtod is not needed anymore
     *
     * public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = NULL, $p_strVarName = NULL, $p_strTemplateName = NULL, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = NULL) {
     * // Something to do...
     * } // function
     */

} // class