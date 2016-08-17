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
 * DAO: Group memberships list for persons.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_person_assigned_groups extends isys_cmdb_dao_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__PERSON_ASSIGNED_GROUPS;
    } // function

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param   null    $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_unused = null)
    {
        $l_query = "SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address
			FROM isys_person_2_group
			INNER JOIN isys_obj ON isys_obj__id = isys_person_2_group__isys_obj__id__group
			INNER JOIN isys_cats_person_group_list ON isys_obj__id = isys_cats_person_group_list__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($p_objID) . "
			GROUP BY isys_cats_person_group_list__isys_obj__id;";

        return $this->retrieve($l_query);
    } // function

    /**
     * Flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Build header for the list.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_person_group_list__title"         => "LC__CMDB__CATG__ODEP_OBJ",
            "isys_cats_person_group_list__email_address" => "LC__CONTACT__GROUP_EMAIL_ADDRESS",
            "isys_cats_person_group_list__phone"         => "LC__CONTACT__GROUP_PHONE",
            "isys_cats_person_group_list__ldap_group"    => "LC__CONTACT__GROUP_LDAP_GROUP"
        ];
    } // function

    /**
     * @return  string
     */
    public function make_row_link()
    {
        return isys_helper_link::create_url(
            [
                C__GET__MODULE_ID    => C__MODULE__CMDB,
                C__CMDB__GET__OBJECT => "[{isys_person_2_group__isys_obj__id__group}]",
                C__CMDB__GET__CATS   => C__CATS__PERSON_GROUP
            ]
        );
    } // function
} // class