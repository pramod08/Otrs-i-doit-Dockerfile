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
 * CMDB UI: specific category for enclosures.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_enclosure extends isys_cmdb_ui_category_specific
{
    /**
     * Shows the detailed template. Dies after an AJAX request.
     *
     * @global  array                              $index_includes
     *
     * @param   isys_cmdb_dao_category_s_enclosure $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_dirs;

        isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        $l_object_id = $_GET[C__CMDB__GET__OBJECT];

        $l_formfactor = $l_cmdb_status = $l_objects = $l_rules = $l_quickinfo = [];

        // Get description:
        $l_enc = $p_cat->get_general_data();

        // This will be used for the "quick info" links.
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_cmdb_res = isys_cmdb_dao_status::instance($this->m_database_component)
            ->get_cmdb_status();

        while ($l_row = $l_cmdb_res->get_row())
        {
            $l_cmdb_status[$l_row['isys_cmdb_status__id']] = [
                'color' => '#' . $l_row['isys_cmdb_status__color'],
                'title' => isys_glob_htmlentities(_L($l_row['isys_cmdb_status__title']))
            ];
        } // while

        if ($l_object_id > 0)
        {
            $l_formfactor = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
                ->get_data(null, $l_object_id)
                ->get_row();

            $l_res = isys_cmdb_dao_location::instance($this->get_database_component())
                ->get_location($l_object_id, null, C__RECORD_STATUS__NORMAL, null, false, ' AND isys_obj_type__show_in_rack = 1');

            // We prepare all objects in one array, so we can just assign them later on.
            while ($l_row = $l_res->get_row())
            {
                $l_icon = $l_row['isys_obj_type__icon'];

                if (strpos($l_icon, '/') === false)
                {
                    $l_icon = $g_dirs['images'] . 'tree/' . $l_icon;
                } // if

                $l_obj_formfactor = ['isys_catg_formfactor_type__title' => isys_tenantsettings::get('gui.empty_value', '-')];

                if ($l_row['isys_catg_formfactor_list__isys_catg_formfactor_type__id'] > 0)
                {
                    $l_obj_formfactor = isys_factory_cmdb_dialog_dao::get_instance('isys_catg_formfactor_type', $this->get_database_component())
                        ->get_data($l_row['isys_catg_formfactor_list__isys_catg_formfactor_type__id']);
                } // if

                $l_rack_units                      = $l_row['isys_catg_formfactor_list__rackunits'] ?: 1;
                $l_objects[$l_row['isys_obj__id']] = [
                    'id'          => $l_row['isys_obj__id'],
                    'title'       => isys_glob_htmlentities($l_row['isys_obj__title']),
                    'type'        => isys_glob_htmlentities(_L($l_row['isys_obj_type__title'])),
                    'formfactor'  => isys_glob_htmlentities($l_obj_formfactor['isys_catg_formfactor_type__title']),
                    'icon'        => $l_icon,
                    'color'       => '#' . $l_row['isys_obj_type__color'],
                    'cmdb_color'  => $l_cmdb_status[$l_row['isys_obj__isys_cmdb_status__id']]['color'],
                    'cmdb_status' => $l_cmdb_status[$l_row['isys_obj__isys_cmdb_status__id']]['title'],
                    'height'      => $l_rack_units,
                    'option'      => $l_row['isys_catg_location_list__option'],
                    'insertion'   => $l_row['isys_catg_location_list__insertion'],
                    'pos'         => $l_row['isys_catg_location_list__pos']
                ];

                $l_quickinfo[] = $l_quick_info->get_script('object-' . $l_row['isys_obj__id'], $l_row['isys_obj__id'], false);
            } // while
        } // if

        $l_slot_sorting = [
            'asc'  => _L('LC__CMDB__SORTING__ASC'),
            'desc' => _L('LC__CMDB__SORTING__DESC')
        ];

        // Create the drop-down data (unit sorting "asc" / "desc").
        $l_rules['C__CATS__ENCLOSURE__UNIT_SORTING']['p_strSelectedID'] = $l_enc['isys_cats_enclosure_list__slot_sorting'];
        $l_rules['C__CATS__ENCLOSURE__UNIT_SORTING']['p_arData']        = serialize($l_slot_sorting);
        $l_rules['C__CATS__ENCLOSURE__UNIT_SORTING']['p_strClass']      = 'input-block';

        // Create the drop-down data (number of vertical slots).
        $l_rules['C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT']['p_strSelectedID'] = $l_enc['isys_cats_enclosure_list__vertical_slots_front'];
        $l_rules['C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT']['p_arData']        = serialize(range(0, isys_cmdb_dao_category_s_enclosure::C__RACK__VERTICAL_SLOTS));
        $l_rules['C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT']['p_strClass']      = 'input-block';
        $l_rules['C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR']['p_strSelectedID']  = $l_enc['isys_cats_enclosure_list__vertical_slots_rear'];
        $l_rules['C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR']['p_arData']         = serialize(range(0, isys_cmdb_dao_category_s_enclosure::C__RACK__VERTICAL_SLOTS));
        $l_rules['C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR']['p_strClass']       = 'input-block';

        // Commentary field.
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_enc['isys_cats_enclosure_list__description'];

        // Preparing the link to the category for attach- and detaching objects.
        $l_link = isys_helper_link::create_url(
            [
                C__CMDB__GET__OBJECT   => $l_object_id,
                C__CMDB__GET__CATG     => C__CATG__OBJECT,
                C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT
            ]
        );

        // Assign the variables to the template.
        $this->get_template_component()
            ->assign(
                'has_edit_right',
                isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::EDIT, $l_object_id, 'C__CATS__ENCLOSURE')
            )
            ->assign('new_entry', !isset($l_enc['isys_cats_enclosure_list__slot_sorting']))
            ->assign('category_link', $l_link)
            ->assign('object_id', $l_object_id)
            ->assign('objects', $l_objects)
            ->assign('objects_json', isys_format_json::encode(array_values($l_objects)))
            ->assign('quickinfo', implode("\n\t\t", $l_quickinfo))
            ->assign('rack_slots', $l_formfactor['isys_catg_formfactor_list__rackunits'] ?: 0)
            ->assign('rack_slot_sorting', $l_enc['isys_cats_enclosure_list__slot_sorting'])
            ->assign('vertical_slots_front', $l_enc['isys_cats_enclosure_list__vertical_slots_front'])
            ->assign('vertical_slots_rear', $l_enc['isys_cats_enclosure_list__vertical_slots_rear'])
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = $this->activate_commentary($p_cat)
            ->get_template();

        return $l_rules;
    } // function
} // class