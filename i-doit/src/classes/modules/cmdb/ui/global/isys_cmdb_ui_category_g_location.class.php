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
 * CMDB UI: Global category location.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_location extends isys_cmdb_ui_category_global
{
    /**
     * Processing method.
     *
     * @param   isys_cmdb_dao_category  &$p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata    = $p_cat->get_general_data();
        $l_rules      = [];
        $l_commentary = "C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id();

        // We will need this request-object for several callbacks.
        if ($l_catdata)
        {
            $l_request = isys_request::factory()
                ->set_category_data_id($l_catdata["isys_catg_location_list__id"])
                ->set_object_id($_GET[C__CMDB__GET__OBJECT])
                ->set_object_type_id($_GET[C__CMDB__GET__OBJECTTYPE])
                ->set_row($l_catdata);

            $l_parent_obj_id = $l_catdata["isys_catg_location_list__parentid"];
            $l_obj_type      = $_GET[C__CMDB__GET__OBJECTTYPE];

            $l_parent_obj = $p_cat->get_type_by_object_id($l_parent_obj_id)
                ->get_row();

            $l_rack_object = false;

            // Is the current object allowed inside a rack?
            if ($p_cat->is_obj_type_in_rack($l_obj_type) && $l_parent_obj['isys_obj_type__isysgui_cats__id'] == C__CATS__ENCLOSURE)
            {
                $l_rack_object = $l_parent_obj_id;

                // Prepare the assembly-options for the rack.
                $l_rules["C__CATG__LOCATION_OPTION"]["p_arData"]        = serialize($p_cat->callback_property_assembly_options($l_request));
                $l_rules["C__CATG__LOCATION_OPTION"]["p_strSelectedID"] = $l_catdata["isys_catg_location_list__option"];

                // Apply the insertion of this object.
                $l_nSelectedSide = -1;
                if ($l_catdata["isys_catg_location_list__insertion"] >= 0)
                {
                    $l_nSelectedSide = $l_catdata["isys_catg_location_list__insertion"];
                } // if

                $l_rules["C__CATG__LOCATION_INSERTION"]["p_arData"]        = serialize($p_cat->callback_property_insertion($l_request));
                $l_rules["C__CATG__LOCATION_INSERTION"]["p_strSelectedID"] = $l_nSelectedSide;

                // Preparing the available positions for this rack.
                if (class_exists('isys_cmdb_dao_category_s_enclosure'))
                {
                    $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                        ->get_data(null, $l_catdata['isys_catg_location_list__parentid'])
                        ->get_row();
                } // if

                // We need this for the height units of the rack.
                $l_rack_form = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
                    ->get_data(null, $l_catdata['isys_catg_location_list__parentid'])
                    ->get_row();

                $l_rack_height = $l_rack_form['isys_catg_formfactor_list__rackunits'];

                $l_free_slots          = $p_cat->get_free_rackslots(
                    $l_parent_obj_id,
                    $l_catdata['isys_catg_location_list__insertion'],
                    $l_catdata['isys_obj__id'],
                    $l_catdata['isys_catg_location_list__option']
                );
                $l_available_positions = [];

                foreach ($l_free_slots as $l_key => $l_slot)
                {
                    // We need to alter the value a bit, depending on the rack slot-sorting.
                    if ($l_catdata['isys_catg_location_list__option'] == C__RACK_INSERTION__HORIZONTAL && $l_rack['isys_cats_enclosure_list__slot_sorting'] == 'desc')
                    {
                        $l_slot_key = $l_rack_height - current(explode(';', $l_key)) + 1;
                    }
                    else
                    {
                        $l_slot_key = current(explode(';', $l_key));
                    } // if

                    $l_available_positions[$l_slot_key] = str_replace('&rarr;', '-', $l_slot);
                } // foreach

                if ($l_catdata["isys_catg_location_list__pos"] == 0)
                {
                    $l_catdata["isys_catg_location_list__pos"] = -1;
                } // if

                $l_rules["C__CATG__LOCATION_POS"]["p_arData"]        = serialize($l_available_positions);
                $l_rules["C__CATG__LOCATION_POS"]["p_strSelectedID"] = $l_catdata["isys_catg_location_list__pos"];
            } // if

            // Handle gps data:
            $l_rules["C__CATG__LOCATION_LATITUDE"]['p_strValue']  = $l_catdata["latitude"];
            $l_rules["C__CATG__LOCATION_LONGITUDE"]['p_strValue'] = $l_catdata["longitude"];
            $this->m_template->assign('lat', $l_catdata["latitude"]);
            $this->m_template->assign('lng', $l_catdata["longitude"]);

            $l_rules["C__CATG__LOCATION_PARENT"]["p_strValue"] = $l_parent_obj_id;
            $l_rules[$l_commentary]["p_strValue"]              = $l_catdata["isys_catg_location_list__description"];
            $l_show_in_rack                                    = !!$l_catdata['isys_obj_type__show_in_rack'];
        }
        else
        {
            $l_show_in_rack = !!$p_cat->get_type_by_object_id($p_cat->get_object_id())
                ->get_row_value('isys_obj_type__show_in_rack');
        } // if

        $this->get_template_component()
            ->assign('option', $l_catdata['isys_catg_location_list__option'] ?: C__RACK_INSERTION__HORIZONTAL)
            ->assign('obj_id', $l_catdata['isys_obj__id'])
            ->assign('rack_object', $l_rack_object)
            ->assign('show_in_rack', $l_show_in_rack)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    } // function
} // class