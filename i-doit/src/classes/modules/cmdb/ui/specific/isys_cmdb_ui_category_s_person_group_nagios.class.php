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
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_person_group_nagios extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_person_group_nagios $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW);

        $l_catdata = $p_cat->get_general_data();

        // Make rules.
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
        )]["p_strValue"]                                                              = $l_catdata["isys_cats_person_group_nagios_list__description"];
        $l_rules["C__MODULE__NAGIOS__CONTACT_GROUP_ALIAS"]["p_strValue"]              = $l_catdata["isys_cats_person_group_nagios_list__alias"];
        $l_rules["C__MODULE__NAGIOS__CONTACT_GROUP_IS_EXPORTABLE"]["p_arData"]        = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__MODULE__NAGIOS__CONTACT_GROUP_IS_EXPORTABLE"]["p_strSelectedID"] = $l_catdata["isys_cats_person_group_nagios_list__is_exportable"];

        if (!$p_cat->get_validation())
        {
            $l_rules["C__MODULE__NAGIOS__CONTACT_GROUP_ALIAS"]["p_strValue"] = $_POST["C__MODULE__NAGIOS__CONTACT_GROUP_ALIAS"];

            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"] = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()];
            $l_rules         = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        } // if

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__person_group_nagios.tpl");
    } // function
} // class
?>