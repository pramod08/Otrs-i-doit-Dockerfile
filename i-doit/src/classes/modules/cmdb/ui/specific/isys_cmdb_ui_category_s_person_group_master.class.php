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
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_person_group_master extends isys_cmdb_ui_category_specific
{

    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_person_group_master $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_template, $g_comp_session;

        $l_rules   = [];
        $l_ldap    = false;
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        if (is_object($g_comp_session->get_ldap_module()))
        {
            $l_ldap                                          = true;
            $l_rules["C__CONTACT__GROUP_LDAP"]["p_strValue"] = $l_catdata["isys_cats_person_group_list__ldap_group"];
        } // if

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign("ldap", $l_ldap);
    } // function
} // class
?>