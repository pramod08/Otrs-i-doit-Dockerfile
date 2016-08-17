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
 * Smarty plugin for text input fields.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_cabling extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     * @author  André Wösten <awoesten@i-doit.org>
     */
    public static function get_meta_map()
    {
        return [
            "p_endpointID",
            "p_cableConID",
            "p_strType"
        ];
    } // function

    /**
     * Returns the content value.
     *
     * @global  array                   $g_dirs
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if ($p_param["p_bInvisible"] == true)
        {
            return '';
        } // if

        global $g_dirs, $g_comp_database;

        $l_connectorID = $p_param["p_connectorID"];
        $l_cableConID  = $p_param["p_cableConID"];

        if ($l_cableConID == null)
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        $l_dao = new isys_cmdb_dao_cable_connection($g_comp_database);

        $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

        $l_objID = $l_dao->get_assigned_object($l_cableConID, $l_connectorID);

        $l_objInfo = $l_dao->get_type_by_object_id($l_objID)
            ->get_row();

        $l_arrMaster = [
            C__CMDB__GET__OBJECT     => $l_objID,
            C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
            C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY,
            C__CMDB__GET__CATG       => C__CATG__CABLING,
            C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
        ];

        // Exchange the specified column.
        return '<a href="' . isys_helper_link::create_url($l_arrMaster) . '">' . $l_strImage . ' ' . $l_objInfo["isys_obj__title"] . '</a>';
    } // function

    /**
     * Navigation edit.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "f_cabling";
        $this->m_strPluginName  = $p_param["name"];

        return '';
    } // function
} // class