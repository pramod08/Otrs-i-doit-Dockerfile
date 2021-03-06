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
class isys_cmdb_ui_category_s_person extends isys_cmdb_ui_category_specific
{

    /**
     * Show the detail-template for specific category monitor.
     *
     * @global  array                   $index_includes
     * @global  isys_component_template $g_comp_template
     *
     * @param   isys_cmdb_dao_category  $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        // Make rules.
        $l_rules["C__CONTACT__PERSON_ACADEMIC_DEGREE"]["p_strValue"]       = $l_catdata["isys_cats_person_list__academic_degree"];
        $l_rules["C__CONTACT__PERSON_SALUTATION"]["p_arData"]              = serialize($p_cat->callback_property_salutation());
        $l_rules["C__CONTACT__PERSON_SALUTATION"]["p_strSelectedID"]       = $l_catdata["isys_cats_person_list__salutation"];
        $l_rules["C__CONTACT__PERSON_FIRST_NAME"]["p_strValue"]            = $l_catdata["isys_cats_person_list__first_name"];
        $l_rules["C__CONTACT__PERSON_LAST_NAME"]["p_strValue"]             = $l_catdata["isys_cats_person_list__last_name"];
        $l_rules["C__CONTACT__PERSON_PERSONNEL_NUMBER"]["p_strValue"]      = $l_catdata["isys_cats_person_list__personnel_number"];
        $l_rules["C__CONTACT__PERSON_MAIL_ADDRESS"]["p_strValue"]          = $l_catdata["isys_cats_person_list__mail_address"];
        $l_rules["C__CONTACT__PERSON_PHONE_COMPANY"]["p_strValue"]         = $l_catdata["isys_cats_person_list__phone_company"];
        $l_rules["C__CONTACT__PERSON_PHONE_HOME"]["p_strValue"]            = $l_catdata["isys_cats_person_list__phone_home"];
        $l_rules["C__CONTACT__PERSON_PHONE_MOBILE"]["p_strValue"]          = $l_catdata["isys_cats_person_list__phone_mobile"];
        $l_rules["C__CONTACT__PERSON_FAX"]["p_strValue"]                   = $l_catdata["isys_cats_person_list__fax"];
        $l_rules["C__CONTACT__PERSON_PAGER"]["p_strValue"]                 = $l_catdata["isys_cats_person_list__pager"];
        $l_rules["C__CONTACT__PERSON_DEPARTMENT"]["p_strValue"]            = $l_catdata["isys_cats_person_list__department"];
        $l_rules["C__CONTACT__PERSON_ASSIGNED_ORGANISATION"]["p_strValue"] = $l_catdata["isys_cats_organization_list__isys_obj__id"];
        $l_rules["C__CONTACT__PERSON_FUNKTION"]["p_strValue"]              = $l_catdata["isys_cats_person_list__function"];
        $l_rules["C__CONTACT__PERSON_SERVICE_DESIGNATION"]["p_strValue"]   = $l_catdata["isys_cats_person_list__service_designation"];
        $l_rules["C__CONTACT__PERSON_CITY"]["p_strValue"]                  = $l_catdata["isys_cats_person_list__city"];
        $l_rules["C__CONTACT__PERSON_ZIP_CODE"]["p_strValue"]              = $l_catdata["isys_cats_person_list__zip_code"];
        $l_rules["C__CONTACT__PERSON_STREET"]["p_strValue"]                = $l_catdata["isys_cats_person_list__street"];

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_person_list__description"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . C__CATS__PERSON]["p_strValue"]           = $l_catdata["isys_cats_person_list__description"];

        // Get custom properties
        $l_custom_properties = $p_cat->get_custom_properties(true);

        foreach ($l_custom_properties AS $l_property)
        {
            $l_rules[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_strValue'] = $l_catdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
        } // if

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign('custom_properties', $l_custom_properties);

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->get_template();
    } // function

    /**
     * UI constructor.
     *
     * @param  isys_component_template &$p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__person_master.tpl");
    } // function
} // class