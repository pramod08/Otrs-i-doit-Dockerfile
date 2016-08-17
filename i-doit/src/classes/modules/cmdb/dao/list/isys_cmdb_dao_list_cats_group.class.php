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
 * DAO: group List
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_group extends isys_cmdb_dao_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__GROUP;
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
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        global $g_comp_database_system;

        $l_dao = isys_cmdb_dao_category_s_group_type::instance($this->get_database_component());

        $l_group_type_res = $l_dao->get_data(null, $p_objID);

        if ($l_group_type_res->num_rows() > 0)
        {
            $l_row       = $l_group_type_res->get_row();
            $l_type      = $l_row['isys_cats_group_type_list__type'];
            $l_report_id = $l_row['isys_cats_group_type_list__isys_report__id'];
        }
        else
        {
            $l_type = 0;
        } // if
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        // @todo  There should be a single method "get_group_objects" which retrieves the object IDs according to the set type!
        if ($l_type == 0)
        {
            $l_sql = "SELECT * FROM isys_cats_group_list
				INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
				INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
				INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
				WHERE isys_cats_group_list__status = " . $this->convert_sql_int($l_cRecStatus) . "
				AND isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

            return $this->retrieve($l_sql);
        }
        else
        {
            $l_report_dao  = new isys_report_dao($g_comp_database_system);
            $l_report_data = $l_report_dao->get_report($l_report_id);

            if (!empty($l_report_data['isys_report__query']))
            {
                $l_sql = 'SELECT obj_main.isys_obj__id AS __id__, obj_main.isys_obj__title, objtype.isys_obj_type__title ';
                $l_sql .= substr($l_report_data['isys_report__query'], strpos($l_report_data['isys_report__query'], 'FROM'), strlen($l_report_data['isys_report__query']));
                $l_sql = str_replace(
                    'isys_obj AS obj_main',
                    'isys_obj AS obj_main INNER JOIN isys_obj_type AS objtype ON objtype.isys_obj_type__id = obj_main.isys_obj__isys_obj_type__id ',
                    $l_sql
                );

                return $this->retrieve($l_sql);
            }
        } // if
        return $this->retrieve('SELECT * FROM isys_obj WHERE isys_obj__id IS NULL;');
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
                    C__CMDB__GET__OBJECTTYPE => $p_arrRow["isys_obj__isys_obj_type__id"],
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY,
                    C__CMDB__GET__CATG       => C__CATG__GLOBAL,
                    C__CMDB__GET__TREEMODE   => $_GET["tvMode"]
                ]
            );

            $p_arrRow["isys_obj__title"] = '<a href="' . $l_link . '"><img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" /> ' . $p_arrRow['isys_obj__title'] . '</a>';
        } // if
    } // function

    /**
     * Retrieve the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"      => "LC__CMDB__CATG__ODEP_OBJ",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE"
        ];
    } // function
} // class