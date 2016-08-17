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
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_shares extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__SHARES;
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
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        if (empty($p_row["isys_catg_drive_list__title"]))
        {
            $p_row["isys_catg_drive_list__title"] = "unnamed";
        } // if
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_shares_list__title"    => "LC__CMDB__CATG__SHARES__SHARE_NAME",
            "isys_catg_shares_list__unc_path" => "LC__CMDB__CATG__SHARES__UNC_PATH",
            "isys_catg_drive_list__title"     => "LC__CMDB__CATG__SHARES__VOLUME",
            "isys_catg_shares_list__path"     => "LC__CMDB__CATG__SHARES__LOCAL_PATH"
        ];
    } // function
} // class