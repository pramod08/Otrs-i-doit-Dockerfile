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
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    0.9
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_relation extends isys_cmdb_ui_category_global
{
    /**
     *
     * @param   isys_cmdb_dao_category_g_relation $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category_g_relation $p_cat)
    {
        $l_object_id            = $p_cat->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT];
        $l_relation_type_dialog = $l_catdata = [];
        $l_id                   = $_GET[C__CMDB__GET__CATLEVEL] ?: (is_array($_POST[C__GET__ID]) ? $_POST[C__GET__ID][0] : null);

        if ($l_id)
        {
            $l_catdata = $p_cat->get_data($l_id)
                ->get_row();
        } // if

        // Relation relevant data is disabled or readonly if relation type is implicit.
        if ($l_catdata["isys_relation_type__type"] == C__RELATION__IMPLICIT)
        {
            $l_rules["C__CATG__RELATION__RELATION_TYPE"]["p_editMode"]  = "0";
            $l_rules["C__CATG__RELATION__RELATION_TYPE"]["p_bDisabled"] = "1";
            $l_rules["C__CATG__RELATION_SLAVE"]["readOnly"]             = "1";
            $l_rules["C__CATG__RELATION_MASTER"]["p_editMode"]          = "0";
            $l_rules["C__CATG__RELATION_SLAVE"]["edit_mode"]            = "0";
            $l_rules["C__CATG__RELATION__ITSERVICE"]["p_bDisabled"]     = true;
            $l_rules["C__CATG__RELATION__DIRECTION"]["p_editMode"]      = ($l_catdata["isys_relation_type__editable"] > 0) ? "1" : "0";
            $this->get_template_component()
                ->assign(
                    'hidden_relation_slave',
                    '<input type="hidden" name="C__CATG__RELATION_SLAVE" value="' . $l_catdata["isys_catg_relation_list__isys_obj__id__slave"] . '">'
                )
                ->assign(
                    'hidden_relation_master',
                    '<input type="hidden" name="C__CATG__RELATION_MASTER" value="' . $l_catdata["isys_catg_relation_list__isys_obj__id__master"] . '">'
                );
        } // if

        if (!isset($l_catdata["isys_relation_type__id"]))
        {
            $l_relation_types = $p_cat->get_relation_types_as_array(null, C__RELATION__EXPLICIT);
            foreach ($l_relation_types AS $l_relation_type_id => $l_rel_type)
            {
                $l_relation_type_dialog[$l_relation_type_id] = $l_rel_type['title'];
            } // foreach
            $l_rules["C__CATG__RELATION__RELATION_TYPE"]["p_arData"] = $l_relation_type_dialog;
        }
        else
        {
            $l_relation_types = $p_cat->get_relation_types_as_array();
            foreach ($l_relation_types AS $l_relation_type_id => $l_rel_type)
            {
                $l_relation_type_dialog[$l_relation_type_id] = $l_rel_type['title'];
            } // foreach
            $l_rules["C__CATG__RELATION__RELATION_TYPE"]["p_arData"] = $l_relation_type_dialog;
        } // if

        // Assign non-complex values.
        $l_rules["C__CATG__RELATION_MASTER"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_obj__id__master"];
        $l_rules["C__CATG__RELATION_SLAVE"]["p_strSelectedID"]  = $l_catdata["isys_catg_relation_list__isys_obj__id__slave"];

        /*
         * In Relation objects we want to show graphical relation information when not in editmode
         *     e.g. | Object1 |   depends on   | Object2 |
         */
        if ($_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__RELATION)
        {
            if (!$this->get_template_component()
                    ->editmode() && ($l_catdata["isys_catg_relation_list__isys_obj__id__master"] && $l_catdata["isys_catg_relation_list__isys_obj__id__slave"])
            )
            {
                // Get Relation type information.
                $l_quickinfo = new isys_ajax_handler_quick_info();

                $l_master = $p_cat->get_obj_name_by_id_as_string($l_catdata["isys_catg_relation_list__isys_obj__id__master"]);
                $l_slave  = $p_cat->get_obj_name_by_id_as_string($l_catdata["isys_catg_relation_list__isys_obj__id__slave"]);

                $l_master_ot = _L($p_cat->get_objtype_name_by_id_as_string($p_cat->get_objTypeID($l_catdata["isys_catg_relation_list__isys_obj__id__master"])));
                $l_slave_ot  = _L($p_cat->get_objtype_name_by_id_as_string($p_cat->get_objTypeID($l_catdata["isys_catg_relation_list__isys_obj__id__slave"])));

                $this->get_template_component()
                    ->assign(
                        "master",
                        $l_quickinfo->get_quick_info($l_catdata["isys_catg_relation_list__isys_obj__id__master"], $l_master_ot . ": " . $l_master, C__LINK__OBJECT)
                    )
                    ->assign("slave", $l_quickinfo->get_quick_info($l_catdata["isys_catg_relation_list__isys_obj__id__slave"], $l_slave_ot . ": " . $l_slave, C__LINK__OBJECT))
                    ->assign("view", "relation");

                unset($l_quickinfo, $l_master, $l_slave, $l_master_ot, $l_slave_ot);
            } // if

            $this->get_template_component()
                ->assign("relation_type_description", _L($l_catdata["isys_relation_type__master"]));
        } // if

        if ($_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__IT_SERVICE && ($_GET[C__CMDB__GET__CATG] == C__CATG__IT_SERVICE_RELATIONS || $_GET[C__CMDB__GET__CATG] == C__CATG__RELATION_ROOT))
        {
            // In IT Service objects (especially in the it service relation category), we want to enabled the selection of it service components only.
            $l_scomponents = [];

            // Get iT Service components of curremt it service.
            $l_its_components     = new isys_cmdb_dao_category_g_it_service_components($p_cat->get_database_component());
            $l_service_components = $l_its_components->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, $_SESSION["cRecStatusListView"]);

            while ($l_row = $l_service_components->get_row())
            {
                $l_scomponents[$l_row["isys_connection__isys_obj__id"]] = $l_row["itsc_title"];
            } // while

            // Pack array.
            $l_packed_scomponents = serialize($l_scomponents);

            // Assign it service components to object 1 and 2.
            $l_rules["C__CATG__RELATION_MASTER"]["p_arData"]               = $l_packed_scomponents;
            $l_rules["C__CATG__RELATION_SLAVE__HIDDEN"]["p_arData"]        = $l_packed_scomponents;
            $l_rules["C__CATG__RELATION__DIRECTION"]["p_strSelectedID"]    = C__RELATION_DIRECTION__DEPENDS_ON_ME;
            $l_rules["C__CATG__RELATION_MASTER"]["p_strSelectedID"]        = $l_catdata["isys_catg_relation_list__isys_obj__id__master"];
            $l_rules["C__CATG__RELATION_SLAVE__HIDDEN"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_obj__id__slave"];

            // Preselect current it-service.
            $l_catdata["isys_catg_relation_list__isys_obj__id__itservice"] = $_GET[C__CMDB__GET__OBJECT];
            $l_rules["C__CATG__RELATION__ITSERVICE"]["p_bDisabled"]        = true;

            unset($l_packed_scomponents, $l_scomponents);

            $this->get_template_component()
                ->assign("it_service", $l_catdata["isys_catg_relation_list__isys_obj__id__itservice"])
                ->assign("view", "it_service");
        }
        else
        {
            // In every other object type, we just show the relation description and allow an object selection of every object type.
            $this->get_template_component()
                ->assign("relation_type_description", _L($l_catdata["isys_relation_type__slave"]));

            // Master selection if relation type is explicit.
            $l_object_title = $p_cat->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);

            if ($l_object_title === '')
            {
                $l_object_title = _L('LC__CMDB__UNIVERSAL__UNNAMED');
            } // if

            $l_arData[$_GET[C__CMDB__GET__OBJECT]] = $l_object_title;

            $l_members_res = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, null);

            while ($l_row = $l_members_res->get_row())
            {
                if ($l_row["isys_catg_relation_list__isys_obj__id"] != $_GET[C__CMDB__GET__OBJECT])
                {
                    $l_arData[_L("LC__CMDB__CATG__RELATION")][$l_row["isys_catg_relation_list__isys_obj__id"]] = $p_cat->format_relation_name(
                        $l_row["master_title"],
                        $l_row["slave_title"],
                        $l_row["isys_relation_type__master"]
                    );
                } // if
            } // while

            $l_rules["C__CATG__RELATION_MASTER"]["p_arData"] = serialize($l_arData);

            if ($l_catdata["isys_catg_relation_list__isys_obj__id__master"] == $_GET[C__CMDB__GET__OBJECT] || $p_cat->object_belongs_to_relation(
                    $_GET[C__CMDB__GET__OBJECT],
                    $l_catdata["isys_catg_relation_list__isys_obj__id__master"]
                )
            )
            {
                $l_rules["C__CATG__RELATION__DIRECTION"]["p_strSelectedID"] = C__RELATION_DIRECTION__DEPENDS_ON_ME;
                $l_rules["C__CATG__RELATION_MASTER"]["p_strSelectedID"]     = $l_catdata["isys_catg_relation_list__isys_obj__id__master"];
                $l_rules["C__CATG__RELATION_MASTER"]["p_strValue"]          = $p_cat->get_obj_name_by_id_as_string(
                    $l_catdata["isys_catg_relation_list__isys_obj__id__master"]
                );
            }
            else
            {
                $l_rules["C__CATG__RELATION__DIRECTION"]["p_strSelectedID"] = C__RELATION_DIRECTION__I_DEPEND_ON;
                $l_rules["C__CATG__RELATION_MASTER"]["p_strSelectedID"]     = $l_catdata["isys_catg_relation_list__isys_obj__id__slave"];
                $l_rules["C__CATG__RELATION_MASTER"]["p_strValue"]          = $p_cat->get_obj_name_by_id_as_string($l_catdata["isys_catg_relation_list__isys_obj__id__slave"]);
                $l_rules["C__CATG__RELATION_SLAVE"]["p_strSelectedID"]      = $l_catdata["isys_catg_relation_list__isys_obj__id__master"];
            } // if
        } // if

        if ($l_catdata["isys_relation_type__type"] == C__RELATION__IMPLICIT)
        {
            $this->get_template_component()
                ->assign("relation_type", $l_catdata["isys_catg_relation_list__isys_relation_type__id"]);
        } // if

        // Retrieve IT Services.
        $l_itservices = [0 => "Global"];
        $l_objects    = $p_cat->get_objects_by_type_id(C__OBJTYPE__IT_SERVICE, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_objects->get_row())
        {
            if ($l_catdata["isys_catg_relation_list__isys_obj__id__itservice"] == $l_row["isys_obj__id"])
            {
                $l_rules["C__CATG__RELATION__ITSERVICE"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_obj__id__itservice"];
                $l_rules["C__CATG__RELATION__ITSERVICE"]["p_strValue"]      = $l_row["isys_obj__title"];
            } // if

            $l_itservices[_L('LC__OBJTYPE__IT_SERVICE')][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
        } // while

        if (empty($l_catdata["isys_catg_relation_list__isys_obj__id__itservice"]))
        {
            $l_rules["C__CATG__RELATION__ITSERVICE"]["p_strSelectedID"] = 0;
        } // if

        $l_rules["C__CATG__RELATION__ITSERVICE"]["p_arData"]            = serialize($l_itservices);
        $l_rules["C__CATG__RELATION__WEIGHTING"]["p_strSelectedID"]     = $l_catdata["isys_catg_relation_list__isys_weighting__id"];
        $l_rules["C__CATG__RELATION__RELATION_TYPE"]["p_strSelectedID"] = $l_catdata["isys_catg_relation_list__isys_relation_type__id"];

        if (empty($l_rules["C__CATG__RELATION__WEIGHTING"]["p_strSelectedID"]))
        {
            $l_rules["C__CATG__RELATION__WEIGHTING"]["p_strSelectedID"] = C__WEIGHTING__5;
        } // if

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_relation_list__description"];

        // Get directions.
        $l_direction = [
            C__RELATION_DIRECTION__DEPENDS_ON_ME => "-> " . _L("LC__CATG__RELATION__DIRECTION__DEPENDS_ON_ME"),
            C__RELATION_DIRECTION__I_DEPEND_ON   => "<- " . _L("LC__CATG__RELATION__DIRECTION__I_DEPEND_ON"),
        ];

        $l_rules["C__CATG__RELATION__DIRECTION"]["p_arData"] = serialize($l_direction);

        // Get parallelly aligned siblings.
        $l_dao       = new isys_cmdb_dao_category_s_parallel_relation($p_cat->get_database_component());
        $l_siblibgs  = $l_dao->get_pool_siblings_as_array($l_catdata["isys_obj__id"]);
        $l_quickinfo = new isys_ajax_handler_quick_info();

        if (is_array($l_siblibgs) && count($l_siblibgs) > 0)
        {
            $l_sibling_list = [];

            foreach ($l_siblibgs as $l_sib)
            {
                $l_sibling_list[] = '<li class="mb5">' . $l_quickinfo->get_quick_info($l_sib, $l_dao->get_obj_name_by_id_as_string($l_sib), C__LINK__OBJECT) . '</li>';
            } // foreach

            $this->get_template_component()
                ->assign("sibling_list", '<ul class="list-style-none m0">' . implode('', $l_sibling_list) . '</ul>');
        } // if

        // ID-2845 In case of a "new" relation, we simply set the master object to the current object itself (usability).
        if (empty($l_rules["C__CATG__RELATION_MASTER"]["p_strSelectedID"]) || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW)
        {
            $l_rules["C__CATG__RELATION_MASTER"]["p_strSelectedID"] = $l_object_id;
        } // if

        $this->get_template_component()
            ->assign(
                "relation_object",
                [
                    "link"  => "?objID=" . $l_catdata["isys_obj__id"],
                    "title" => $l_catdata["isys_obj__title"]
                ]
            )
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class