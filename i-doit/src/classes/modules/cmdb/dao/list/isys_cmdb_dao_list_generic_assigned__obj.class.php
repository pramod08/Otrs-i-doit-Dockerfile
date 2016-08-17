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
 * DAO: ObjectType list for CPU
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_generic_assigned__obj extends isys_cmdb_dao_list
{
    /**
     * @var  string
     */
    protected $m_strTableName;

    /**
     * Set the name of the sourcetable as string without "__list".
     *
     * @param   string $p_strTableName
     */
    public function set_source_table($p_strTableName)
    {
        $this->m_strTableName = $p_strTableName;
    } // function

    /**
     * @return  string
     */
    public function get_source_table()
    {
        return $this->m_strTableName;
    } // function

    /**
     *
     * @param   null    $p_str
     * @param   integer $p_fk_Id
     * @param   null    $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_fk_Id, $p_cRecStatus = null)
    {
        $l_tb = $this->get_source_table();

        $l_strSQL = "SELECT isys_obj__id, isys_obj_type__id, isys_obj_type__title, isys_obj_type__isys_obj_type_group__id, isys_obj__title
			FROM " . $l_tb . "_list
			INNER JOIN isys_connection ON " . $l_tb . "_list__isys_connection__id = isys_connection__id
			INNER JOIN isys_obj ON " . $l_tb . "_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_connection__isys_obj__id= " . $this->convert_sql_id($p_fk_Id) . " ";

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (!empty($l_cRecStatus))
        {
            $l_strSQL .= " AND " . $l_tb . "_list__status = " . $this->convert_sql_id($l_cRecStatus) . " AND isys_obj__status = " . $this->convert_sql_id($l_cRecStatus);
        } // if

        return $this->retrieve($l_strSQL);
    } // function

    /**
     * Modify row.
     *
     * @param   array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $l_gets     = isys_module_request::get_instance()
            ->get_gets();
        $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

        if ($p_arrRow["isys_obj__id"] != null)
        {
            // Link to connected object.
            $l_arrConnectedObj = [
                C__CMDB__GET__OBJECT     => $p_arrRow["isys_obj__id"],
                C__CMDB__GET__OBJECTTYPE => $p_arrRow["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY_GLOBAL,
                C__CMDB__GET__CATG       => C__CATG__GLOBAL,
                C__CMDB__GET__TREEMODE   => $l_gets[C__CMDB__GET__TREEMODE]
            ];

            // Link to connected objType (list of objects).
            $l_arrConnectedObjType = [
                C__CMDB__GET__VIEWMODE    => C__CMDB__VIEW__LIST_OBJECT,
                C__CMDB__GET__TREEMODE    => C__CMDB__VIEW__TREE_OBJECTTYPE,
                C__CMDB__GET__OBJECTTYPE  => $p_arrRow["isys_obj_type__id"],
                C__CMDB__GET__OBJECTGROUP => $p_arrRow["isys_obj_type__isys_obj_type_group__id"]
            ];

            $p_arrRow["isys_obj__title"]      = '<a href="' . isys_helper_link::create_url(
                    $l_arrConnectedObj
                ) . '">' . $l_strImage . ' ' . $p_arrRow["isys_obj__title"] . '</a>';
            $p_arrRow["isys_obj_type__title"] = '<a href="' . isys_helper_link::create_url($l_arrConnectedObjType) . '">' . $l_strImage . ' ' . _L(
                    $p_arrRow["isys_obj_type__title"]
                ) . '</a>';
        } // if
    } // function

    /**
     * Method for retrieving the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__id"         => "ID",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE",
            "isys_obj__title"      => "LC__CMDB__CATG__GLOBAL_TITLE"
        ];
    } // function

    /**
     * Make row link.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    } // function
} // class