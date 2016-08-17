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
 * DAO: Class for overview category
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_person_contact_assign extends isys_cmdb_dao_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__PERSON_CONTACT_ASSIGNMENT;
    } // function

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     * @param string  $p_table
     * @param integer $p_id
     *
     * @return isys_component_dao_result
     * @version Dennis Blümer <dbluemer@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT isys_catg_contact_list.*, o1.*, isys_contact_tag.* FROM isys_catg_contact_list " . "INNER JOIN isys_connection  ON isys_connection__id = isys_catg_contact_list.isys_catg_contact_list__isys_connection__id " . "INNER JOIN isys_obj AS o1 ON isys_catg_contact_list.isys_catg_contact_list__isys_obj__id = o1.isys_obj__id " . "INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id " . "LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list.isys_catg_contact_list__isys_contact_tag__id " . "WHERE isys_connection__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . " ";

        $l_cRecStatus = (int) ($p_cRecStatus === null) ? $this->get_rec_status() : $p_cRecStatus;

        if ($l_cRecStatus > 0)
        {
            $l_sql .= ' AND o1.isys_obj__status = ' . $this->convert_sql_int(
                    C__RECORD_STATUS__NORMAL
                ) . ' AND isys_catg_contact_list.isys_catg_contact_list__status = ' . $this->convert_sql_int($l_cRecStatus);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    public function modify_row(&$p_arrRow)
    {
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

            $p_arrRow["isys_obj__title"] = '<a href="' . $l_link . '">' . $p_arrRow["isys_obj__title"] . '</a>';
        } // if
    } // function

    /**
     * Returns array with table headers
     *
     * @return array
     * @global $g_comp_template_language_manager
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"         => "LC__CMDB__LOGBOOK__TITLE",
            "isys_contact_tag__title" => "LC__CMDB__CONTACT_ROLE"
        ];
    } // function

    /**
     * @param   array $p_arrGetUrlOverride
     *
     * @return  string
     */
    public function make_row_link($p_arrGetUrlOverride = null)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj_type__isys_obj_type_group__id, isys_obj__isys_obj_type__id
			FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__id = ' . $_GET[C__CMDB__GET__OBJECT] . ';';

        $l_gets    = isys_module_request::get_instance()
            ->get_gets();
        $l_catdata = $this->retrieve($l_sql)
            ->get_row();

        $l_arrGetUrl = [
            C__CMDB__GET__VIEWMODE           => C__CMDB__VIEW__CATEGORY_GLOBAL,
            C__CMDB__GET__TREEMODE           => $l_gets[C__CMDB__GET__TREEMODE],
            C__CMDB__GET__OBJECT             => $l_catdata["isys_obj__id"],
            C__CMDB__GET__CATS               => C__CATS__PERSON_CONTACT_ASSIGNMENT,
            C__CMDB__GET__OBJECTGROUP        => $l_catdata["isys_obj_type__isys_obj_type_group__id"],
            C__GET__MAIN_MENU__NAVIGATION_ID => $l_gets["mNavID"],
            C__CMDB__GET__OBJECTTYPE         => $l_catdata["isys_obj__isys_obj_type__id"],
            C__CMDB__GET__CATLEVEL           => "[{isys_catg_contact_list__id}]"
        ];

        // append or override from parameter array
        if (is_array($p_arrGetUrlOverride))
        {
            while (list($l_key, $l_val) = each($p_arrGetUrlOverride))
            {
                $l_arrGetUrl[$l_key] = $p_arrGetUrlOverride[$l_key];
            } // while
        } // if

        return isys_helper_link::create_url($l_arrGetUrl);
    } // function
} // class