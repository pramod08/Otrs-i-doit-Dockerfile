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
 * DAO: AP List
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_access_point extends isys_cmdb_dao_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__ACCESS_POINT;
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
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT * FROM isys_cats_access_point_list
			LEFT OUTER JOIN isys_wlan_channel ON isys_cats_access_point_list__isys_wlan_channel__id = isys_wlan_channel__id
			LEFT OUTER JOIN isys_wlan_auth ON isys_cats_access_point_list__isys_wlan_auth__id = isys_wlan_auth__id
			LEFT OUTER JOIN isys_wlan_function ON isys_cats_access_point_list__isys_wlan_function__id = isys_wlan_function__id
			LEFT OUTER JOIN isys_wlan_encryption ON isys_cats_access_point_list__encryption = isys_wlan_encryption__id
			WHERE isys_cats_access_point_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_cats_access_point_list__status = " . $this->convert_sql_int(empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus) . ";";

        return $this->retrieve($l_sql);
    } // function

    /**
     *
     * @param  array $p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow["isys_cats_access_point_list__broadcast_ssid"] == 1)
        {
            $p_arrRow["isys_cats_access_point_list__broadcast_ssid"] = _L("LC__UNIVERSAL__YES");
        }
        else
        {
            $p_arrRow["isys_cats_access_point_list__broadcast_ssid"] = _L("LC__UNIVERSAL__NO");
        } // if

        if ($p_arrRow["isys_cats_access_point_list__mac_filter"] == 1)
        {
            $p_arrRow["isys_cats_access_point_list__mac_filter"] = _L("LC__UNIVERSAL__YES");
        }
        else
        {
            $p_arrRow["isys_cats_access_point_list__mac_filter"] = _L("LC__UNIVERSAL__NO");
        } // if
    } // function

    /**
     * Build header for the list.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_access_point_list__title"          => "LC__UNIVERSAL__TITLE",
            "isys_wlan_function__title"                   => "LC__CMDB__CATS__ACCESS_POINT_FUNCTION",
            "isys_cats_access_point_list__ssid"           => "LC__CMDB__CATS__ACCESS_POINT_SSID",
            "isys_wlan_channel__title"                    => "LC__CMDB__CATS__ACCESS_POINT_CHANNEL",
            "isys_wlan_auth__title"                       => "LC__CMDB__CATS__ACCESS_POINT_AUTH",
            "isys_wlan_encryption__title"                 => "LC__CMDB__CATS__ACCESS_POINT_ENCRYPTION",
            "isys_cats_access_point_list__broadcast_ssid" => "LC__CMDB__CATS__ACCESS_POINT_BRODCAST_SSID",
            "isys_cats_access_point_list__mac_filter"     => "LC__CMDB__CATS__ACCESS_POINT_MAC_FILTER"

        ];
    } // function
} // class