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
 * Global category connector
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_ws_assignment extends isys_cmdb_ui_category_specific
{

    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = null, $p_strVarName = null, $p_strTemplateName = null, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = null)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                # multiselection: false is default
                isys_popup_browser_object_ng::C__TYPE_FILTER    => "C__OBJTYPE__CABLE",
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                # should isys_form gets submitted after accepting? default is no.
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                # this is the html element where the selected objects are transfered into (as JSON)
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [
                        get_class($p_cat),
                        "get_assigned_objects"
                    ],
                    $_GET[C__CMDB__GET__OBJECT]
                ]
                # this is where the browser tries to get a preselection from
            ],
            "LC__CMDB__CATS__GROUP__ADD_OBJECTS",
            "LC__CATG__OBJECT__ADD_TT"
        );

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}

?>