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
 * UI: specific category group
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_group extends isys_cmdb_ui_category_specific
{
    /**
     * Process the list.
     *
     * @param   isys_cmdb_dao_category_s_group &$p_cat
     * @param   array                          $p_get_param_override
     * @param   string                         $p_strVarName
     * @param   string                         $p_strTemplateName
     * @param   boolean                        $p_bCheckbox
     * @param   boolean                        $p_bOrderLink
     * @param   string                         $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @see     isys_cmdb_ui_category::process_list()
     */
    public function process_list(isys_cmdb_dao_category_s_group &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $l_type = $p_cat->get_group_type($_GET[C__CMDB__GET__OBJECT]);

        if ($l_type == 0)
        {
            $this->object_browser_as_new(
                [
                    isys_popup_browser_object_ng::C__MULTISELECTION => true,
                    // multiselection: false is default
                    isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                    // should isys_form gets submitted after accepting? default is no.
                    isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                    // this is the html element where the selected objects are transfered into (as JSON)
                    isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                        [
                            get_class($p_cat),
                            "get_connected_objects"
                        ],
                        $_GET[C__CMDB__GET__OBJECT]
                        // this is where the browser tries to get a preselection from
                    ]
                ],
                "LC__CMDB__CATS__GROUP__ADD_OBJECTS",
                "LC__CATG__OBJECT__ADD_TT"
            );

            return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
        }
        else
        {
            isys_component_template_navbar::getInstance()
                ->hide_all_buttons()
                ->deactivate_all_buttons();

            parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, false, $p_bOrderLink, $p_db_field_name);

            $this->m_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=1")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=1")
                ->assign("bNavbarFilter", "0");
        } // if
        return null;
    } // function

    /**
     * Constructor.
     *
     * @param   isys_component_template $p_template
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__group.tpl");
        parent::__construct($p_template);
    } // function
} // class