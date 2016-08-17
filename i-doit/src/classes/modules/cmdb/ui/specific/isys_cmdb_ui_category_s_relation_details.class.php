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
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_relation_details extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category relation details.
     *
     * @param   isys_cmdb_dao_category_s_relation_details $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_catdata    = $p_cat->get_general_data();

        $l_dao_relation  = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_relation_type = $l_dao_relation->get_relation_type($l_catdata["isys_catg_relation_list__isys_relation_type__id"], null, true);

        // Make rules.
        $l_rules["C__CATS__RELATION_DETAILS__WEIGHTING"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_weighting__id"];
        $l_rules["C__CATS__RELATION_DETAILS__WEIGHTING"]["p_strTable"]      = "isys_weighting";
        $l_rules["C__CATS__RELATION_DETAILS__ITSERVICE"]["p_strValue"]      = $l_catdata["isys_catg_relation_list__isys_obj__id__itservice"];
        $l_rules["C__CATS__RELATION_DETAILS__RELATION_TYPE"]["p_strValue"]  = ((!empty($l_catdata["isys_catg_relation_list__isys_relation_type__id"])) ? _L(
            $l_relation_type["isys_relation_type__title"]
        ) : _L('LC__UNIVERSAL__DEPENDENCY'));

        $l_itservices = [0 => "Global"];
        $l_objects    = $p_cat->get_objects_by_type_id(C__OBJTYPE__IT_SERVICE, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_objects->get_row())
        {
            if ($l_catdata["isys_catg_relation_list__isys_obj__id__itservice"] == $l_row["isys_obj__id"])
            {
                $l_rules["C__CATS__RELATION_DETAILS__ITSERVICE"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_obj__id__itservice"];
                $l_rules["C__CATS__RELATION_DETAILS__ITSERVICE"]["p_strValue"]      = $l_row["isys_obj__title"];
            } // if

            $l_itservices[_L('LC__OBJTYPE__IT_SERVICE')][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
        } // while

        $l_rules["C__CATS__RELATION_DETAILS__ITSERVICE"]["p_arData"] = serialize($l_itservices);

        if ($l_catdata["isys_catg_relation_list__isys_obj__id__itservice"] == "")
        {
            $l_rules["C__CATS__RELATION_DETAILS__ITSERVICE"]["p_strSelectedID"] = 0;
        }
        else
        {
            $l_rules["C__CATS__RELATION_DETAILS__ITSERVICE"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_obj__id__itservice"];
        } // if

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_relation_list__description"];

        if (($l_catdata["isys_catg_relation_list__isys_relation_type__id"] == C__RELATION_TYPE__DEFAULT || empty($l_catdata["isys_catg_relation_list__isys_relation_type__id"])) && ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW))
        {

            $l_rules["C__CATS__RELATION_DETAILS__MASTER"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_obj__id__master"];
            $l_rules["C__CATS__RELATION_DETAILS__SLAVE"]["p_strSelectedID"]  = $l_catdata["isys_catg_relation_list__isys_obj__id__slave"];

            // Get directions.
            $l_direction = [
                C__RELATION_DIRECTION__DEPENDS_ON_ME => "-> " . _L("LC__CATG__RELATION__DIRECTION__DEPENDS_ON_ME"),
                C__RELATION_DIRECTION__I_DEPEND_ON   => "<- " . _L("LC__CATG__RELATION__DIRECTION__I_DEPEND_ON")
            ];

            $l_rules["C__CATS__RELATION_DETAILS__DIRECTION"]["p_arData"] = serialize($l_direction);
        }
        else
        {
            $this->get_template_component()
                ->assign("view", "relation")
                ->assign("obj_id_master", $l_catdata["isys_catg_relation_list__isys_obj__id__master"])
                ->assign("obj_id_slave", $l_catdata["isys_catg_relation_list__isys_obj__id__slave"]);
        } // if

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__PRINT)
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        $l_master = $l_slave = _L('LC_SANPOOL_POPUP__NO_OBJECT');

        if ($l_catdata["isys_catg_relation_list__isys_obj__id__master"] !== null)
        {
            $l_master = $l_quick_info->get_quick_info(
                $l_catdata["isys_catg_relation_list__isys_obj__id__master"],
                $p_cat->get_obj_name_by_id_as_string($l_catdata["isys_catg_relation_list__isys_obj__id__master"]),
                C__LINK__OBJECT,
                false
            );
        } // if

        if ($l_catdata["isys_catg_relation_list__isys_obj__id__slave"] !== null)
        {
            $l_slave = $l_quick_info->get_quick_info(
                $l_catdata["isys_catg_relation_list__isys_obj__id__slave"],
                $p_cat->get_obj_name_by_id_as_string($l_catdata["isys_catg_relation_list__isys_obj__id__slave"]),
                C__LINK__OBJECT,
                false
            );
        } // if

        // Apply rules.
        $this->get_template_component()
            ->assign("relation_type_description", _L($l_relation_type["isys_relation_type__master"]))
            ->assign("master", $l_master)
            ->assign("slave", $l_slave)
            ->assign(
                "relation_type",
                ((!empty($l_catdata["isys_catg_relation_list__isys_relation_type__id"])) ? $l_catdata["isys_catg_relation_list__isys_relation_type__id"] : C__RELATION_TYPE__DEFAULT)
            )
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class