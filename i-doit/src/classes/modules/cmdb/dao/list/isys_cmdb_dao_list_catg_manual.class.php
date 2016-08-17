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
 * DAO: ObjectType list for manuals
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_manual extends isys_cmdb_dao_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__MANUAL;
    } // function

    /**
     * Return category type constant.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * @param   array &$p_row
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row["isys_file_version__isys_file_physical__id"] !== null)
        {
            $l_file_obj = $this->m_cat_dao->get_object_by_id($p_row['isys_connection__isys_obj__id'])
                ->get_row();

            $l_dlgets                       = isys_module_request::get_instance()
                ->get_gets();
            $l_dlgets[C__GET__FILE_MANAGER] = "get";
            $l_dlgets[C__GET__FILE__ID]     = $p_row["isys_file_version__isys_file_physical__id"];
            $l_dlgets[C__GET__MODULE_ID]    = C__MODULE__CMDB;

            $l_file_name     = _L($l_file_obj['isys_obj_type__title']) . ' > ' . $l_file_obj['isys_obj__title'];
            $l_download_link = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

            $p_row["download_file_name"] = $l_file_name;
            $p_row["download"]           = '<a target="_blank" href="' . $l_download_link . '"><img src="' . $g_dirs["images"] . '/icons/silk/disk.png" class="vam" />&nbsp;' . _L(
                    'LC__UNIVERSAL__DOWNLOAD_FILE'
                ) . '</a>';
        }
        else
        {
            $p_row["download_file_name"] = $l_empty_value;
            $p_row["download"]           = $l_empty_value;
        } // if
    } // function

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_manual_list__title' => 'LC__CMDB__CATG__MANUAL_TITLE',
            'download_file_name'           => 'LC__CMDB__CATG__FILE_OBJ_FILE',
            'isys_file_version__revision'  => 'LC_UNIVERSAL__REVISION',
            'download'                     => 'LC__CMDB__CATS__FILE_DOWNLOAD'
        ];
    } // function
} // class