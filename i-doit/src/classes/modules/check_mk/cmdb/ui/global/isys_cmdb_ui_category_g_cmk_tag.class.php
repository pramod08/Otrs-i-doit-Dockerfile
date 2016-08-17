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
 * UI: global category for Check_MK.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_ui_category_g_cmk_tag extends isys_cmdb_ui_category_global
{
    /**
     * Gets the template file.
     *
     * @return  string
     */
    public function get_template()
    {
        return isys_module_check_mk::get_tpl_dir() . 'modules' . DS . 'cmdb' . DS . 'catg__cmk_tag.tpl';
    } // function

    /**
     * Processes the UI for the category check_mk.
     *
     * @param   isys_cmdb_dao_category_g_cmk_tag $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules   = [];
        $l_obj_id  = $_GET[C__CMDB__GET__OBJECT];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_cmdb_tags = $l_dynamic_tags = false;

        if (class_exists('isys_check_mk_helper_tag'))
        {
            // This can happen, when the category was not yet filled.
            if (!is_array($l_catdata))
            {
                $l_catdata = $p_cat->get_object_by_id($l_obj_id)
                    ->get_row();
            } // if

            try
            {
                $l_cmdb_tag_rows = isys_check_mk_helper_tag::factory($l_catdata['isys_obj__isys_obj_type__id'])
                    ->get_cmdb_tags($l_obj_id);
            }
            catch (Exception $e)
            {
                $l_cmdb_tag_rows = [];
                isys_notify::error($e->getMessage(), ['sticky' => true]);
            } // try

            if (count($l_cmdb_tag_rows) > 0)
            {
                $l_tags = [];

                foreach ($l_cmdb_tag_rows as $l_tag)
                {
                    $l_tags[] = [
                        'val' => $l_tag,
                        'sel' => true
                    ];
                } // foreach

                $l_cmdb_tags = isys_factory::get_instance('isys_smarty_plugin_f_dialog_list')
                    ->navigation_view(
                        $this->get_template_component(),
                        [
                            'name'     => 'cmdb_tags',
                            'p_arData' => serialize($l_tags)
                        ]
                    );
            } // if

            $l_generic_tag_rows = isys_check_mk_helper_tag::get_dynamic_tags($l_catdata['isys_obj__id']);

            if (count($l_generic_tag_rows) > 0)
            {
                $l_tags = [];

                foreach ($l_generic_tag_rows as $l_tag)
                {
                    $l_tags[] = [
                        'val' => $l_tag,
                        'sel' => true
                    ];
                } // foreach

                $l_dynamic_tags = isys_factory::get_instance('isys_smarty_plugin_f_dialog_list')
                    ->navigation_view(
                        $this->get_template_component(),
                        [
                            'name'     => 'dynamic_tags',
                            'p_arData' => serialize($l_tags)
                        ]
                    );
            } // if
        } // if

        $this->get_template_component()
            ->assign('cmdb_tags', $l_cmdb_tags)
            ->assign('dynamic_tags', $l_dynamic_tags)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class