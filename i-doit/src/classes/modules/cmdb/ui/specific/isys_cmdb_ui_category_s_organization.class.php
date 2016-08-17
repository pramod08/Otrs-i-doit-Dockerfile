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
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_organization extends isys_cmdb_ui_category_specific
{
    /**
     * Organisation process method.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_org = [];
        $l_res = $p_cat->get_objects_by_cats_id(C__CATS__ORGANIZATION, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_res->get_row())
        {
            $l_org[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
        } // while

        // Make rules
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_organization_list__description"];
        $l_rules["C__CONTACT__ORGANISATION_TITLE"]["p_strValue"]                                                      = $l_catdata["isys_cats_organization_list__title"];
        $l_rules["C__CONTACT__ORGANISATION_PHONE"]["p_strValue"]                                                      = $l_catdata["isys_cats_organization_list__telephone"];
        $l_rules["C__CONTACT__ORGANISATION_FAX"]["p_strValue"]                                                        = $l_catdata["isys_cats_organization_list__fax"];
        $l_rules["C__CONTACT__ORGANISATION_WEBSITE"]["p_strValue"]                                                    = $l_catdata["isys_cats_organization_list__website"];
        $l_rules["C__CONTACT__ORGANISATION_ASSIGNMENT"]["p_arData"]                                                   = $l_org;
        $l_rules["C__CONTACT__ORGANISATION_ASSIGNMENT"]["p_strSelectedID"]                                            = $l_catdata["isys_connection__isys_obj__id"];
        $l_rules["C__CONTACT__ORGANISATION_ASSIGNMENT"]["p_arDisabled"]                                               = [$l_catdata["isys_cats_organization_list__id"] => true];

        $this->get_template_component()->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__organization.tpl");
    } // function
} // class