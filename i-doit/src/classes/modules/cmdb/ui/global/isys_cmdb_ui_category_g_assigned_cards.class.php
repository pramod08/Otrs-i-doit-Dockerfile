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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     0.9.9.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_assigned_cards extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category_g_assigned_cards $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    } // function

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_g_assigned_cards $p_cat
     *
     * @return  null
     */
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                // multiselection: false is default
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                // should isys_form gets submitted after accepting? default is no.
                isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATG__SIM_CARD",
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                // this is the html element where the selected objects are transfered into (as JSON)
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [
                        get_class($p_cat),
                        "get_assigned_object"
                    ],
                    $_GET[C__CMDB__GET__OBJECT],
                ]
                // this is where the browser tries to get a preselection from
            ],
            "LC__CATG__OBJECT__ADD",
            "LC__CATG__OBJECT__ADD"
        );

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    } // function

    /**
     * Constructor.
     *
     * @todo   Is this a reversed-category or can the constructor be removed?
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__assigned_cards.tpl");
        parent::__construct($p_template);
    } // function
} // class
?>