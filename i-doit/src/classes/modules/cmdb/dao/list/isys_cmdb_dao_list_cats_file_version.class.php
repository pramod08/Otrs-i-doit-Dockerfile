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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_file_version extends isys_cmdb_dao_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CMDB__SUBCAT__FILE_VERSIONS;
    } // function

    /**
     * Return category type constant.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     * Modify row method.
     *
     * @param   array &$p_arrRow
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_loc, $g_dirs;

        $p_arrRow['isys_file_size'] = isys_tenantsettings::get('gui.empty_value', '-');
        $l_filepath                 = $g_dirs["fileman"]["target_dir"] . DS . $p_arrRow["isys_file_physical__filename"];

        if (file_exists($l_filepath))
        {
            $l_filesize = filesize($l_filepath);

            if ($l_filesize > 0)
            {
                $l_dlgets                       = isys_module_request::get_instance()
                    ->get_gets();
                $l_dlgets[C__GET__FILE_MANAGER] = "get";
                $l_dlgets[C__GET__FILE__ID]     = $p_arrRow["isys_file_version__isys_file_physical__id"];
                $l_dlgets[C__GET__MODULE_ID]    = C__MODULE__CMDB;

                $p_arrRow['isys_download']  = '<a target="_blank" href="' . isys_glob_build_url(
                        urldecode(isys_glob_http_build_query($l_dlgets))
                    ) . '"><img src="' . $g_dirs["images"] . '/icons/silk/disk.png" class="vam" /><span class="ml5 vam">' . _L('LC__UNIVERSAL__DOWNLOAD_FILE') . '</span></a>';
                $p_arrRow['isys_file_size'] = isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD) . ' ' . _L('LC__CMDB__MEMORY_UNIT__MB');
            } // if
        } // if

        // Formatting the upload-date.
        $p_arrRow["isys_file_physical__date_uploaded"] = $g_loc->fmt_date($p_arrRow["isys_file_physical__date_uploaded"]);
    } // function

    /**
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            "isys_file_physical__filename_original" => "LC__CMDB__CATS__FILE_NAME",
            "isys_file_version__title"              => "LC__CMDB__CATS__FILE_TITLE",
            "isys_file_version__revision"           => "LC__CMDB__CATS__FILE_REVISION",
            "isys_file_physical__date_uploaded"     => "LC__CMDB__CATS__FILE_UPLOAD_DATE",
            "isys_file_size"                        => "LC__CMDB__CATS__FILE__SIZE",
            "isys_download"                         => "LC__CMDB__CATS__FILE_DOWNLOAD"
        ];
    } // function

    /**
     * The isys_component_dao_object_table_list constructor differentiates if $p_cat is an instance of isys_cmdb_dao_category or isys_component database.
     *
     * @param  isys_cmdb_dao_category &$p_cat
     */
    public function __construct(isys_cmdb_dao_category &$p_cat)
    {
        $this->set_rec_status_list(false);
        parent::__construct($p_cat);
    } // function
} // class