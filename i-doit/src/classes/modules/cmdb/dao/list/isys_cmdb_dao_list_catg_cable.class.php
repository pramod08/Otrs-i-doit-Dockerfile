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
 * DAO: AP List.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_cable extends isys_cmdb_dao_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__CABLE;
    } // function

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;
        $l_sql        = "SELECT * FROM isys_catg_connector_list
			INNER JOIN isys_cable_connection ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
			LEFT JOIN isys_connection_type ON isys_connection_type__id = isys_catg_connector_list__type
			WHERE isys_cable_connection__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_catg_connector_list__status = " . $this->convert_sql_int($l_cRecStatus) . ";";

        return $this->retrieve($l_sql);
    } // function

    /**
     * Exchange column to create individual links in columns.
     *
     * @param   array &$p_arrRow
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function modify_row(&$p_arrRow)
    {
        if (!empty($p_arrRow["isys_cable_connection__id"]))
        {
            global $g_dirs;

            $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

            $l_objID   = $p_arrRow["isys_catg_connector_list__isys_obj__id"];
            $l_objInfo = isys_cmdb_dao_cable_connection::instance($this->m_db)
                ->get_type_by_object_id($l_objID)
                ->get_row();

            $l_link = isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT     => $l_objID,
                    C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                    C__CMDB__GET__CATG       => C__CATG__UNIVERSAL_INTERFACE,
                    C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
                ]
            );

            // exchange the specified column.
            $p_arrRow["isys_obj__title"] = "<a href=\"" . $l_link . "\"> $l_strImage " . $l_objInfo["isys_obj__title"] . "</a>";
            $p_arrRow["connector_title"] = $p_arrRow["isys_catg_connector_list__title"];
        } // if
    } // function

    /**
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "connector_title" => "LC__CATG__STORAGE_CONNECTION_TYPE",
            "isys_obj__title" => "LC__CMDB__CATG__UI_ASSIGNED_UI"
        ];
    } // function

    /**
     * @return  string
     */
    public function make_row_link()
    {
        $l_objInfo = isys_cmdb_dao_cable_connection::instance($this->m_db)
            ->get_type_by_object_id(
                isys_module_request::get_instance()
                    ->get(C__CMDB__GET__OBJECT)
            )
            ->get_row();

        return isys_helper_link::create_url(
            [
                C__CMDB__GET__OBJECT     => "[{isys_catg_connector_list__isys_obj__id}]",
                C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => C__CATG__UNIVERSAL_INTERFACE,
                C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
            ]
        );
    } // function

    /**
     * Enter description here...
     *
     * @param  isys_cmdb_dao_category $p_cat
     */
    public function __construct(isys_cmdb_dao_category $p_cat)
    {
        parent::__construct($p_cat);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__RECYCLE)
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE);
    } // function
} // class