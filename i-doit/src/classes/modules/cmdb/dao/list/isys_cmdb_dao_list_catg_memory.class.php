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
 * DAO: ObjectType list for CPU.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_memory extends isys_cmdb_dao_list
{
    /**
     * Return constant of category
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__MEMORY;
    } // function

    /**
     * Return constant of category type
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Modify row.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $p_arrRow["isys_catg_memory_list__capacity"] = isys_convert::memory(
                $p_arrRow["isys_catg_memory_list__capacity"],
                $p_arrRow["isys_memory_unit__const"],
                C__CONVERT_DIRECTION__BACKWARD
            ) . " " . $p_arrRow["isys_memory_unit__title"];
    } // function

    /**
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "isys_memory_title__title"        => "LC__CMDB__CATG__TITLE",
            "isys_memory_manufacturer__title" => "LC__CMDB__CATG__MANUFACTURER",
            "isys_catg_memory_list__capacity" => "LC__CMDB_CATG__MEMORY_CAPACITY"
        ];
    } // function
} // class