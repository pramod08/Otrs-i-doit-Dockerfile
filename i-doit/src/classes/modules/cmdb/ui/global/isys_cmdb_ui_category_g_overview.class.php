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
 * CMDB UI: Overview category with content of configured categories.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_overview extends isys_cmdb_ui_category_global
{
    /**
     * In this specific case this variable will not work - because we call a lot of other UI classes which have this set to true. This is just for completeness ;)
     *
     * @var   boolean
     */
    protected $m_csv_export = false;

    /**
     * Process the category.
     *
     * @param  isys_cmdb_dao_category_g_overview &$p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs, $index_includes;

        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_auth_edit = isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::EDIT, 'CATEGORY/C__CATG__OVERVIEW');

        // Get visible categories.
        $l_categories        = $p_cat->get_categories_as_array($l_gets[C__CMDB__GET__OBJECTTYPE], $l_gets[C__CMDB__GET__OBJECT]);
        $l_specific_category = $p_cat->get_category_specific($l_gets[C__CMDB__GET__OBJECTTYPE], $l_gets[C__CMDB__GET__OBJECT]);
        $l_custom_categories = $p_cat->get_custom_categories_as_array($l_gets[C__CMDB__GET__OBJECTTYPE], $l_gets[C__CMDB__GET__OBJECT]);

        if (is_array($l_custom_categories) && count($l_custom_categories))
        {
            foreach ($l_custom_categories AS $l_value)
            {
                $l_categories["custom_" . $l_value['id']] = $l_value;
            } // foreach
        } // if

        if (count($l_categories))
        {
            isys_glob_sort_array_by_column($l_categories, 'sort');
        } // if

        if (is_array($l_specific_category) && count($l_specific_category))
        {
            foreach ($l_specific_category as $l_value)
            {
                $l_categories["specific"] = $l_value;
            } // foreach
        } // if

        if (is_array($l_categories))
        {
            foreach ($l_categories as $l_key => $l_cat)
            {
                if (isset($l_cat['dao']) && is_object($l_cat['dao']))
                {
                    /** @var isys_cmdb_ui_category $l_ui */
                    $l_ui = $l_cat['dao']->get_ui();
                    isys_component_signalcollection::get_instance()
                        ->emit(
                            "mod.cmdb.beforeProcess",
                            $l_cat['dao'],
                            $index_includes["contentbottomcontent"]
                        );

                    $l_categories[$l_key]['template']        = $l_ui->get_template();
                    $l_categories[$l_key]['template_before'] = $l_ui->get_additional_template_before();
                    $l_categories[$l_key]['template_after']  = $l_ui->get_additional_template_after();

                    if (method_exists($l_cat['dao'], 'get_config') && method_exists($l_cat['dao'], 'get_catg_custom_id'))
                    {
                        $l_categories[$l_key]['fields'] = $l_cat['dao']->get_config($l_cat['dao']->get_catg_custom_id());
                    } // if

                    if ($_POST[C__GET__NAVMODE] != C__NAVBAR_BUTTON__NEW && $l_cat['multivalued'] && $l_cat['const'] != 'C__CATG__IP')
                    {
                        $l_ui->process_list($l_cat['dao'], null, $l_categories[$l_key]['const'], null, false);
                    }
                    else
                    {
                        if ($l_cat['const'] == 'C__CATG__IP' && method_exists($l_ui, 'show_primary_ip'))
                        {
                            $l_ui->show_primary_ip();
                        } // if

                        $l_rules = $l_ui->process($l_cat['dao']);
                        $l_ui->process_ui_validation_rules($l_cat['dao'], (is_array($l_rules) && count($l_rules) ? $l_rules : []));
                    } // if
                } // if
            } // foreach

            switch ($_POST[C__GET__NAVMODE])
            {
                case C__NAVMODE__NEW:
                    isys_component_template_navbar::getInstance()
                        ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_visible(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                        ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
                    break;
                case C__NAVMODE__EDIT:
                    isys_component_template_navbar::getInstance()
                        ->set_visible(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                        ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE);
                    break;
                default:
                    isys_component_template_navbar::getInstance()
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                        ->set_active($l_auth_edit, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__PRINT)
                        ->set_active(true, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                    break;
            } // switch
        } // if

        // Assign stuff to the template.
        $this->get_template_component()
            ->assign("g_navmode", isys_glob_get_param(C__GET__NAVMODE))
            ->assign("g_categories", $l_categories)
            ->assign('img_dir', $g_dirs["images"])
            ->assign('auth', isys_auth_cmdb::instance())
            ->assign('auth_view_id', isys_auth::VIEW)
            ->assign('auth_edit_id', isys_auth::EDIT)
            ->assign('obj_id', $l_gets[C__CMDB__GET__OBJECT])
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=0");

        $this->deactivate_commentary();

        isys_component_template_navbar::getInstance()
            ->set_active($l_auth_edit && $_POST[C__GET__NAVMODE] != C__NAVMODE__EDIT && $_POST[C__GET__NAVMODE] != C__NAVMODE__NEW, C__NAVBAR_BUTTON__EDIT)
            ->set_active($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
            ->set_active(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
        {
            isys_component_template_navbar::getInstance()
                ->set_active(true, C__NAVBAR_BUTTON__SAVE);
        }

        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    /**
     * This is no multivalue category, so we use the process method here.
     *
     * @todo    Is this method even necessary?
     *
     * @param   isys_cmdb_dao_category_g_overview $p_cat
     *
     * @return  mixed
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $this->process($p_cat);
    } // function
} // class