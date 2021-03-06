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
 * DAO: ObjectType list for cluster members
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_cluster_members extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__CLUSTER_MEMBERS;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   null    $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = 'SELECT isys_catg_cluster_members_list__id, isys_obj__id, isys_obj__title, isys_obj_type__title
			FROM isys_catg_cluster_members_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($p_objID);

        $l_status = $p_cRecStatus ?: $this->get_rec_status();

        if (!empty($l_status))
        {
            $l_sql .= ' AND isys_catg_cluster_members_list__status = ' . $this->convert_sql_int($l_status);
        } // if

        return $this->retrieve($l_sql . ' AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';');
    } // function

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        if ($p_arrRow["isys_obj__id"] != null)
        {
            $l_link = isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT     => $p_arrRow["isys_obj__id"],
                    C__CMDB__GET__OBJECTTYPE => $p_arrRow["isys_obj_type__id"],
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY_GLOBAL,
                    C__CMDB__GET__CATG       => C__CATG__GLOBAL,
                    C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
                ]
            );

            $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

            $p_arrRow["isys_obj__title"] = '<a href="' . $l_link . '">' . $l_strImage . ' ' . $p_arrRow["isys_obj__title"] . '</a>';
        } // if
    } // function

    /**
     * Gets flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Method for retrieving the table field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"      => "LC_UNIVERSAL__OBJECT",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE"
        ];
    } // function
} // class