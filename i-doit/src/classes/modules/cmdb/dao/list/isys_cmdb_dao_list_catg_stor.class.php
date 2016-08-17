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
 * DAO: ObjectType list for storage devices
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_stor extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CMDB__SUBCAT__STORAGE__DEVICE;
    } // function

    /**
     * Return constant of category type-
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
        if ($p_row["isys_stor_type__const"] == "C__STOR_TYPE_DEVICE_RAID_GRP")
        {
            // Compute total capacity of RAID group.
            $l_dao      = new isys_cmdb_dao_category_g_stor($this->m_db);
            $l_res      = $l_dao->get_devices(null, $_GET[C__CMDB__GET__OBJECT], $p_row["isys_catg_stor_list__id"], C__STOR_TYPE_DEVICE_HD);
            $l_numDisks = $l_res->num_rows();

            if (count($l_res) > 0)
            {
                $l_row = $l_res->get_row();
                $l_id  = $l_row["isys_catg_stor_list__id"];
                $l_lo  = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);

                while ($l_row = $l_res->get_row())
                {
                    if (isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) < $l_lo)
                    {
                        $l_lo = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    } // if

                    if ($l_row["isys_catg_stor_list__hotspare"] == "1")
                    {
                        $l_numDisks--;
                    } // if
                } // while

                // @todo  ID-2188
                $p_row["isys_catg_stor_list__capacity"] = "<div id=\"raidcapacity_" . $l_id . "\"></div>" . "<script type=\"text/javascript\">" . "raidcalc('" . $l_numDisks . "', '" . $l_lo . "', '" . $p_row["isys_stor_raid_level__title"] . "', 'raidcapacity_" . $l_id . "', null);" . "</script>";
            } // if
        }
        else
        {
            $p_row["isys_catg_stor_list__capacity"] = isys_convert::memory(
                    $p_row["isys_catg_stor_list__capacity"],
                    $p_row["isys_memory_unit__const"],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . " " . $p_row["isys_memory_unit__title"];
        } // if
    } // function

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_stor_list__title"       => "LC__CATG__STORAGE_TITLE",
            "isys_stor_type__title"            => "LC__CATG__STORAGE_TYPE",
            "isys_catg_stor_list__capacity"    => "LC__CATG__STORAGE_CAPACITY",
            "isys_catg_controller_list__title" => "LC__CATG__STORAGE_CONTROLLER"
        ];
    } // function
} // class