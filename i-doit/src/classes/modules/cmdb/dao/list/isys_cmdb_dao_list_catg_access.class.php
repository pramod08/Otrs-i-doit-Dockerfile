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
 * DAO: ObjectType list for access.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_access extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__ACCESS;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * This methode is called for every row in the methode getTempTableHtml (class isys_component_list).
     *
     * @global  array $g_dirs
     *
     * @param   array $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        if ($p_row["isys_catg_access_list__primary"])
        {
            $p_row["isys_catg_access_list__primary"] = '<span class="text-green">' .
                '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_green.png" class="vam" /> ' . _L("LC__UNIVERSAL__YES") .
                '</span>';
        }
        else
        {
            $p_row["isys_catg_access_list__primary"] = '<span class="text-red">' .
                '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" class="vam" /> ' . _L("LC__UNIVERSAL__NO") .
                '</span>';
        } // if

        if (!empty($p_row["isys_catg_access_list__url"]))
        {
            $l_aurl = isys_helper_link::handle_url_variables($p_row["isys_catg_access_list__url"], $p_row['isys_catg_access_list__isys_obj__id']);

            // ID-1344  Adding "event.stopPropagation();" stops the browser from opening the category itself.
            $p_row["isys_catg_access_list__url"] = '<a href="' . $l_aurl . '" target="_blank" onclick="event.stopPropagation();">' .
                '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam" /> ' . $l_aurl .
                '</a>';
        }
        else
        {
            $p_row["isys_catg_access_list__url"] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function

    /**
     * Method for receiving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_access_list__title"   => "LC__CMDB__CATG__ACCESS_TITLE",
            "isys_access_type__title"        => "LC__CMDB__CATG__ACCESS_TYPE",
            "isys_catg_access_list__url"     => "LC__CMDB__CATG__ACCESS_URL",
            "isys_catg_access_list__primary" => "LC__CMDB__CATG__ACCESS_PRIMARY"
        ];
    } // function
} // class