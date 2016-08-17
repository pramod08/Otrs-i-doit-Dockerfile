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
 * CMDB - Reversed category for "nagios service" to "nagios service template".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_nagios_service_refs_tpl_backwards extends isys_cmdb_ui_category_global
{
    /**
     * Process method - Usually, we won't need this.
     *
     * @param   isys_cmdb_dao_category_g_nagios_service_refs_tpl_backwards $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    } // function

    /**
     * Method for processing the list-view.
     *
     * @param   isys_cmdb_dao_category_g_nagios_service_refs_tpl_backwards $p_cat
     *
     * @return  boolean
     */
    public function process_list(isys_cmdb_dao_category_g_nagios_service_refs_tpl_backwards $p_cat)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__NAGIOS_SERVICE_FOLDER',
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [
                        get_class($p_cat),
                        "get_selected_objects"
                    ],
                    $_GET[C__CMDB__GET__OBJECT],
                    [
                        "isys_obj__id",
                        "isys_obj__title",
                        "isys_obj__isys_obj_type__id",
                        "isys_obj__sysid"
                    ]
                ]
            ],
            'LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS__ASSIGN_BUTTON',
            'LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS__ASSIGN_BUTTON'
        );

        isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE);

        $this->list_view("isys_obj__id", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_catg_nagios_service_refs_tpl_backwards($this->get_database_component()));

        return true;
    } // function
} // class
?>
