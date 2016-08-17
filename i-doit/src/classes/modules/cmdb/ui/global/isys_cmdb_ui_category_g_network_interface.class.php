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
 * CMDB UI: Interface category for Network.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_network_interface extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                                      $index_includes
     *
     * @param   isys_cmdb_dao_category_g_network_interface &$p_cat
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        // Make rules
        $l_rules                                                                                                      = [];
        $l_rules["C__CATG__INTERFACE_P_TITLE"]["p_strValue"]                                                          = $l_catdata["isys_catg_netp_list__title"];
        $l_rules["C__CATG__INTERFACE_P_MANUFACTURER"]["p_strSelectedID"]                                              = $l_catdata["isys_catg_netp_list__isys_iface_manufacturer__id"];
        $l_rules["C__CATG__INTERFACE_P_MODEL"]["p_strSelectedID"]                                                     = $l_catdata["isys_catg_netp_list__isys_iface_model__id"];
        $l_rules["C__CATG__INTERFACE_P_SERIAL"]["p_strValue"]                                                         = $l_catdata["isys_catg_netp_list__serial"];
        $l_rules["C__CATG__INTERFACE_P_SLOTNUMBER"]["p_strValue"]                                                     = $l_catdata["isys_catg_netp_list__slotnumber"];
        $l_rules["C__CATG__INTERFACE_P_MANUFACTURER"]["p_strTable"]                                                   = "isys_iface_manufacturer";
        $l_rules["C__CATG__INTERFACE_P_MODEL"]["p_strTable"]                                                          = "isys_iface_model";
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_netp_list__description"];

        if (!$p_cat->get_validation())
        {
            $l_rules["C__CATG__INTERFACE_P_TITLE"]["p_strValue"]                                                          = $_POST["C__CATG__INTERFACE_P_TITLE"];
            $l_rules["C__CATG__INTERFACE_P_MANUFACTURER"]["p_strSelectedID"]                                              = $_POST["C__CATG__INTERFACE_P_MANUFACTURER"];
            $l_rules["C__CATG__INTERFACE_P_MODEL"]["p_strSelectedID"]                                                     = $_POST["C__CATG__INTERFACE_P_MODEL"];
            $l_rules["C__CATG__INTERFACE_P_SERIAL"]["p_strValue"]                                                         = $_POST["C__CATG__INTERFACE_P_SERIAL"];
            $l_rules["C__CATG__INTERFACE_P_SLOTNUMBER"]["p_strValue"]                                                     = $_POST["C__CATG__INTERFACE_P_SLOTNUMBER"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];
        } // if

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->get_template();
    } // function

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__interface_p.tpl");
    } // function
} // class
?>