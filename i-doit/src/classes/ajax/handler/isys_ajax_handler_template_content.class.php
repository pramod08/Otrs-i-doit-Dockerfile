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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_template_content extends isys_ajax_handler
{
    /**
     * Initialization method.
     *
     * @global  isys_component_database $g_comp_database
     * @global  isys_component_template $g_comp_template
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_database, $g_comp_template;

        if (defined("C__MODULE__TEMPLATES") && isset($_GET["template_id"]) && $_GET["template_id"] > 0)
        {
            // Object data.
            $l_global      = new isys_cmdb_dao_category_g_global($g_comp_database);
            $l_global_data = $l_global->get_data(null, $_GET["template_id"])
                ->__to_array();

            // Object Type.
            $l_object_type      = $l_global->get_objTypeID($_GET["template_id"]);
            $l_object_type_name = $l_global->get_objtype_name_by_id_as_string($l_object_type);

            // Object image.
            $l_object_image = new isys_smarty_plugin_object_image();

            // Object info.
            $l_object_info = $l_global->get_object_by_id($_GET["template_id"])
                ->__to_array();

            // Get affected categories.
            $l_mod_template  = new isys_module_templates();
            $l_template_cats = $l_mod_template->get_affected_categories($_GET["template_id"], $l_object_type);

            // Output.
            echo "<table style=\"valign:top;background:#eee;margin-right:10px;\" class=\"p5\" cellpadding=\"2\" cellspacing=\"3\">" . "<colgroup>" . "<col width=\"55\" />" . "<col width=\"120\" />" . "<col width=\"180\" />" . "<col width=\"150\" />" . "</colgroup>" . "<tr>" . "<td rowspan=\"2\">" . $l_object_image->navigation_view(
                    $g_comp_template,
                    [
                        "objType" => $l_object_type,
                        "width"   => 45,
                        "height"  => 45
                    ]
                ) . "</td>" . "<td valign=\"top\" class=\"bold\">Name:</td>" . "<td valign=\"top\">" . $l_global_data["isys_obj__title"] . "</td>" . "<td valign=\"top\" class=\"bold\">" . _L(
                    "LC__CMDB__AFFECTED_CATEGORIES"
                ) . ":</td>" . "<td valign=\"top\">" . implode(", ", $l_template_cats) . "</td>" . "</tr>" . "<tr>" . "<td class=\"bold\">" . _L(
                    "LC__CMDB__OBJTYPE"
                ) . ":</td>" . "<td>" . _L($l_object_type_name) . "</td>" . "<td class=\"bold\">" . _L(
                    "LC__TASK__DETAIL__WORKORDER__CREATION_DATE"
                ) . ":</td>" . "<td>" . $l_object_info["isys_obj__created"] . " " . strtolower(
                    _L("LC_UNIVERSAL__FROM")
                ) . " " . $l_object_info["isys_obj__created_by"] . "</td>" . "</tr>" . "</table>";
        } // if

        $this->_die();

        return true;
    } // function
} // class