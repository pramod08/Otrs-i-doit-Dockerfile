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
 * i-doit.
 *
 * DAO: Category list for passwords.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_password extends isys_cmdb_dao_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__PASSWD;
    } // function

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     *
     * @param  array $p_aRow
     */
    public function modify_row(&$p_aRow)
    {
        if (!empty($p_aRow['isys_catg_password_list__password']))
        {
            $p_aRow['isys_catg_password_list__password'] = isys_helper_crypt::decrypt($p_aRow['isys_catg_password_list__password']);
        } // if
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_password_list__id"       => "ID",
            "isys_catg_password_list__title"    => "LC__CMDB__CATG__TITLE",
            "isys_catg_password_list__username" => "LC__LOGIN__USERNAME",
            "isys_catg_password_list__password" => "LC__LOGIN__PASSWORD"
        ];
    } // function
} // class